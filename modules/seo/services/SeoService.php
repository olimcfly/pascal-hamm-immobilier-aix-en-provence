<?php

declare(strict_types=1);

require_once __DIR__ . '/KeywordTracker.php';
require_once __DIR__ . '/SitemapGenerator.php';
require_once __DIR__ . '/PerformanceAudit.php';
require_once __DIR__ . '/SeoTechnicalPerformanceService.php';

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
        if ($sql === false) {
            return;
        }
        // Exécute chaque statement séparément
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt !== '') {
                try {
                    $this->pdo->exec($stmt);
                } catch (PDOException) {
                    // Ignore les erreurs non bloquantes (ex: FK déjà existante)
                }
            }
        }
    }

    public function getHubStats(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seo_keywords WHERE user_id = ? AND (status IS NULL OR status <> 'archived')");
        $stmt->execute([$userId]);
        $keywordsCount = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM seo_city_pages WHERE user_id = ?');
        $stmt->execute([$userId]);
        $villesCount = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seo_city_pages WHERE user_id = ? AND status = 'published'");
        $stmt->execute([$userId]);
        $villesPublished = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT generated_at FROM seo_sitemap_logs WHERE user_id = ? ORDER BY generated_at DESC LIMIT 1');
        $stmt->execute([$userId]);
        $sitemapLastGenerated = $stmt->fetchColumn() ?: null;

        $performanceSummary = ['score' => null, 'status' => 'non_audite'];
        try {
            $performanceSummary = (new SeoTechnicalPerformanceService($this->pdo, $userId))->getHubPerformanceSummary();
        } catch (Throwable) {
            // fallback sur ancien module d'audit si nécessaire
            $stmt = $this->pdo->prepare('SELECT perf_score FROM seo_performance_audits WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$userId]);
            $legacyScore = $stmt->fetchColumn();
            if ($legacyScore !== false) {
                $performanceSummary['score'] = (int)$legacyScore;
                $performanceSummary['status'] = (int)$legacyScore >= 80 ? 'bon' : ((int)$legacyScore >= 60 ? 'moyen' : 'a_corriger');
            }
        }

        return [
            'keywords_count' => $keywordsCount,
            'top10_count' => $this->countTop10($userId),
            'villes_count' => $villesCount,
            'villes_published' => $villesPublished,
            'sitemap_last_generated' => $sitemapLastGenerated,
            'last_audit_score' => $performanceSummary['score'] !== null ? (int)$performanceSummary['score'] : null,
            'last_audit_status' => (string)$performanceSummary['status'],
        ];
    }

    private function countTop10(int $userId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM seo_keywords WHERE user_id = ? AND current_position BETWEEN 1 AND 10 AND (status IS NULL OR status <> 'archived')");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
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
