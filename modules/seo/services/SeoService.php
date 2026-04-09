<?php

declare(strict_types=1);

require_once __DIR__ . '/KeywordTracker.php';
require_once __DIR__ . '/SitemapGenerator.php';
require_once __DIR__ . '/PerformanceAudit.php';
require_once __DIR__ . '/SitemapService.php';

class SeoService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $sql = file_get_contents(__DIR__ . '/../sql/seo.sql');
        if ($sql !== false) {
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt === '') {
                    continue;
                }
                try {
                    $this->pdo->exec($stmt);
                } catch (PDOException) {
                }
            }
        }

        (new SitemapService($this->pdo))->ensureSchema();
    }

    public function getHubStats(int $userId): array
    {
        $tracker = new KeywordTracker($this->pdo, $userId);

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM seo_keywords WHERE user_id = ? AND is_active = 1');
        $stmt->execute([$userId]);
        $keywordsCount = (int) $stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM seo_city_pages WHERE user_id = ?');
        $stmt->execute([$userId]);
        $villesCount = (int) $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seo_city_pages WHERE user_id = ? AND status = 'published'");
        $stmt->execute([$userId]);
        $villesPublished = (int) $stmt->fetchColumn();

        $sitemapService = new SitemapService($this->pdo);
        $sitemapData = $sitemapService->getDashboard($userId);
        $sitemap = $sitemapData['sitemap'] ?? [];

        $stmt = $this->pdo->prepare('SELECT perf_score FROM seo_performance_audits WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        $lastAuditScore = $stmt->fetchColumn();

        return [
            'keywords_count' => $keywordsCount,
            'top10_count' => count($tracker->getTop10($userId)),
            'villes_count' => $villesCount,
            'villes_published' => $villesPublished,
            'sitemap_last_generated' => $sitemap['last_generated_at'] ?? null,
            'sitemap_status' => $sitemap['status'] ?? 'idle',
            'sitemap_issues_count' => (int) ($sitemap['issues_count'] ?? 0),
            'last_audit_score' => $lastAuditScore !== false ? (int) $lastAuditScore : null,
        ];
    }

    public function searchModules(string $query): array
    {
        $query = mb_strtolower(trim($query));
        $modules = [
            ['id' => 'keywords', 'title' => 'Mots-clés'],
            ['id' => 'cities', 'title' => 'Fiches villes'],
            ['id' => 'sitemap', 'title' => 'Sitemap'],
            ['id' => 'performance', 'title' => 'Performance technique'],
        ];

        if ($query === '') {
            return $modules;
        }

        return array_values(array_filter($modules, static function (array $module) use ($query): bool {
            return str_contains(mb_strtolower($module['title']), $query)
                || str_contains(mb_strtolower($module['id']), $query);
        }));
    }
}
