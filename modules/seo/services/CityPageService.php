<?php

declare(strict_types=1);

class CityPageService
{
    private static bool $schemaEnsured = false;

    public function __construct(private readonly PDO $pdo)
    {
        $this->ensureSchema();
    }

    public function listForUser(int $userId, string $status = '', string $search = ''): array
    {
        $where = ['user_id = :user_id'];
        $params = ['user_id' => $userId];

        if (in_array($status, ['draft', 'ready', 'published'], true)) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(city_name LIKE :search OR slug LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $sql = 'SELECT * FROM seo_city_pages WHERE ' . implode(' AND ', $where) . ' ORDER BY updated_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(fn(array $row): array => $this->hydrateWithScores($row), $rows);
    }

    public function getStats(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) AS published,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
                AVG(seo_score) AS avg_seo_score
             FROM seo_city_pages
             WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'published' => (int)($row['published'] ?? 0),
            'draft' => (int)($row['draft'] ?? 0),
            'avg_seo_score' => (int)round((float)($row['avg_seo_score'] ?? 0)),
        ];
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM seo_city_pages WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrateWithScores($row) : null;
    }

    public function save(int $userId, array $payload, ?int $id = null): int
    {
        $record = [
            'city_name' => trim((string)($payload['city_name'] ?? '')),
            'slug' => trim((string)($payload['slug'] ?? '')),
            'status' => (string)($payload['status'] ?? 'draft'),
            'seo_title' => trim((string)($payload['seo_title'] ?? '')),
            'meta_description' => trim((string)($payload['meta_description'] ?? '')),
            'h1' => trim((string)($payload['h1'] ?? '')),
            'intro' => trim((string)($payload['intro'] ?? '')),
            'market_block' => trim((string)($payload['market_block'] ?? '')),
            'faq_json' => $this->normalizeJsonField($payload['faq_json'] ?? ''),
            'internal_links_json' => $this->normalizeJsonField($payload['internal_links_json'] ?? ''),
            'canonical_url' => trim((string)($payload['canonical_url'] ?? '')),
        ];

        if ($record['slug'] === '' && $record['city_name'] !== '') {
            $record['slug'] = slugify($record['city_name']);
        }

        if (!in_array($record['status'], ['draft', 'ready', 'published'], true)) {
            $record['status'] = 'draft';
        }

        $record['seo_score'] = $this->calculateSeoScore($record);
        $record['content_score'] = $this->calculateContentScore($record);

        if ($record['status'] === 'published' && !$this->isPublishable($record)) {
            throw new InvalidArgumentException('La fiche doit être complète avant publication.');
        }

        $publishedAt = null;
        if ($record['status'] === 'published') {
            $publishedAt = date('Y-m-d H:i:s');
        }

        if ($id !== null && $id > 0) {
            $stmt = $this->pdo->prepare(
                "UPDATE seo_city_pages
                 SET city_name = :city_name,
                     slug = :slug,
                     status = :status,
                     seo_title = :seo_title,
                     meta_description = :meta_description,
                     h1 = :h1,
                     intro = :intro,
                     market_block = :market_block,
                     faq_json = :faq_json,
                     internal_links_json = :internal_links_json,
                     canonical_url = :canonical_url,
                     seo_score = :seo_score,
                     content_score = :content_score,
                     published_at = CASE WHEN :status = 'published' THEN COALESCE(published_at, :published_at) ELSE NULL END,
                     updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id"
            );
            $stmt->execute(array_merge($record, [
                'published_at' => $publishedAt,
                'id' => $id,
                'user_id' => $userId,
            ]));

            return $id;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO seo_city_pages
             (user_id, city_name, slug, status, seo_title, meta_description, h1, intro, market_block, faq_json, internal_links_json, canonical_url, seo_score, content_score, published_at, created_at, updated_at)
             VALUES
             (:user_id, :city_name, :slug, :status, :seo_title, :meta_description, :h1, :intro, :market_block, :faq_json, :internal_links_json, :canonical_url, :seo_score, :content_score, :published_at, NOW(), NOW())"
        );
        $stmt->execute(array_merge($record, [
            'user_id' => $userId,
            'published_at' => $publishedAt,
        ]));

        return (int)$this->pdo->lastInsertId();
    }

    public function togglePublication(int $id, int $userId): void
    {
        $page = $this->findForUser($id, $userId);
        if ($page === null) {
            throw new InvalidArgumentException('Fiche introuvable.');
        }

        if ($page['status'] === 'published') {
            $stmt = $this->pdo->prepare("UPDATE seo_city_pages SET status = 'ready', published_at = NULL, updated_at = NOW() WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            return;
        }

        if (!$this->isPublishable($page)) {
            throw new InvalidArgumentException('Impossible de publier : les champs minimum ne sont pas complétés.');
        }

        $stmt = $this->pdo->prepare("UPDATE seo_city_pages SET status = 'published', published_at = NOW(), updated_at = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
    }

    public function isPublishable(array $record): bool
    {
        return $this->calculateSeoScore($record) >= 80;
    }

    public function calculateSeoScore(array $record): int
    {
        $checks = [
            !empty($record['seo_title']),
            !empty($record['meta_description']),
            !empty($record['h1']),
            !empty($record['intro']),
            !empty($record['market_block']),
            !empty($this->decodeJson($record['faq_json'] ?? '')),
            !empty($record['canonical_url']),
            $this->isCleanSlug((string)($record['slug'] ?? '')),
        ];

        return (int)round((array_sum(array_map(static fn(bool $ok): int => $ok ? 1 : 0, $checks)) / count($checks)) * 100);
    }

    public function calculateContentScore(array $record): int
    {
        $introWords = str_word_count(strip_tags((string)($record['intro'] ?? '')));
        $marketWords = str_word_count(strip_tags((string)($record['market_block'] ?? '')));
        $faqCount = count($this->decodeJson($record['faq_json'] ?? ''));
        $linkCount = count($this->decodeJson($record['internal_links_json'] ?? ''));

        $score = 0;
        $score += $introWords >= 80 ? 30 : ($introWords >= 30 ? 15 : 0);
        $score += $marketWords >= 120 ? 35 : ($marketWords >= 50 ? 15 : 0);
        $score += $faqCount >= 3 ? 20 : ($faqCount > 0 ? 10 : 0);
        $score += $linkCount >= 3 ? 15 : ($linkCount > 0 ? 7 : 0);

        return min(100, $score);
    }

    private function hydrateWithScores(array $row): array
    {
        $row['seo_score'] = (int)($row['seo_score'] ?? $this->calculateSeoScore($row));
        $row['content_score'] = (int)($row['content_score'] ?? $this->calculateContentScore($row));
        $row['faq_list'] = $this->decodeJson($row['faq_json'] ?? '[]');
        $row['internal_links_list'] = $this->decodeJson($row['internal_links_json'] ?? '[]');

        return $row;
    }

    private function normalizeJsonField(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        $value = trim((string)$value);
        if ($value === '') {
            return '[]';
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return '[]';
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    private function decodeJson(string $value): array
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function isCleanSlug(string $slug): bool
    {
        return $slug !== '' && (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
    }

    private function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }

        self::$schemaEnsured = true;

        try {
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS seo_city_pages (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    city_name VARCHAR(160) NOT NULL,
                    slug VARCHAR(190) NOT NULL,
                    status ENUM('draft', 'ready', 'published') NOT NULL DEFAULT 'draft',
                    seo_title VARCHAR(70) DEFAULT NULL,
                    meta_description VARCHAR(170) DEFAULT NULL,
                    h1 VARCHAR(190) DEFAULT NULL,
                    intro TEXT NULL,
                    market_block MEDIUMTEXT NULL,
                    faq_json JSON NULL,
                    internal_links_json JSON NULL,
                    canonical_url VARCHAR(255) DEFAULT NULL,
                    seo_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    content_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    published_at DATETIME NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_city_slug (user_id, slug),
                    KEY idx_city_status (user_id, status),
                    KEY idx_city_name (user_id, city_name),
                    KEY idx_city_score (user_id, seo_score)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );

            $this->addColumnIfMissing('city_name', 'VARCHAR(160) NULL AFTER user_id');
            $this->addColumnIfMissing('intro', 'TEXT NULL AFTER h1');
            $this->addColumnIfMissing('market_block', 'MEDIUMTEXT NULL AFTER intro');
            $this->addColumnIfMissing('faq_json', 'JSON NULL AFTER market_block');
            $this->addColumnIfMissing('internal_links_json', 'JSON NULL AFTER faq_json');
            $this->addColumnIfMissing('canonical_url', 'VARCHAR(255) NULL AFTER internal_links_json');
            $this->addColumnIfMissing('seo_score', 'TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER canonical_url');
            $this->addColumnIfMissing('content_score', 'TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER seo_score');

            if ($this->columnExists('city')) {
                $this->tryExec('UPDATE seo_city_pages SET city_name = COALESCE(NULLIF(city_name, \'\'), NULLIF(city, \'\'), \'\') WHERE city_name IS NULL OR city_name = \'\'');
                $this->tryExec('ALTER TABLE seo_city_pages MODIFY COLUMN city VARCHAR(160) NULL');
            }

            if ($this->columnExists('postal_code')) {
                $this->tryExec('ALTER TABLE seo_city_pages MODIFY COLUMN postal_code VARCHAR(12) NULL');
            }

            if ($this->columnExists('content')) {
                $this->tryExec('ALTER TABLE seo_city_pages MODIFY COLUMN content MEDIUMTEXT NULL');
            }

            $this->tryExec("ALTER TABLE seo_city_pages MODIFY COLUMN city_name VARCHAR(160) NOT NULL");
            $this->tryExec("ALTER TABLE seo_city_pages MODIFY COLUMN status ENUM('draft', 'ready', 'published') NOT NULL DEFAULT 'draft'");
            $this->addIndexIfMissing('idx_city_name', 'ADD INDEX idx_city_name (user_id, city_name)');
            $this->addIndexIfMissing('idx_city_score', 'ADD INDEX idx_city_score (user_id, seo_score)');
        } catch (Throwable $e) {
            error_log('CityPageService::ensureSchema ' . $e->getMessage());
        }
    }

    private function addColumnIfMissing(string $column, string $definition): void
    {
        if (!$this->columnExists($column)) {
            $this->tryExec("ALTER TABLE seo_city_pages ADD COLUMN {$column} {$definition}");
        }
    }

    private function addIndexIfMissing(string $index, string $definition): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1'
            );
            $stmt->execute(['seo_city_pages', $index]);

            if (!$stmt->fetchColumn()) {
                $this->tryExec("ALTER TABLE seo_city_pages {$definition}");
            }
        } catch (Throwable $e) {
            error_log('CityPageService::addIndexIfMissing ' . $e->getMessage());
        }
    }

    private function columnExists(string $column): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1'
            );
            $stmt->execute(['seo_city_pages', $column]);

            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            error_log('CityPageService::columnExists ' . $e->getMessage());
            return false;
        }
    }

    private function tryExec(string $sql): void
    {
        try {
            $this->pdo->exec($sql);
        } catch (Throwable $e) {
            error_log('CityPageService::tryExec ' . $e->getMessage());
        }
    }
}
