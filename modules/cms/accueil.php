<?php
declare(strict_types=1);

$pageTitle = 'Pages de votre site';
$pageDescription = 'Modifier vos textes, titres SEO et mise en ligne';

/**
 * MVP CMS: on commence par la page Accueil.
 */
$managedPages = [
    'home' => [
        'label' => 'Accueil',
        'template' => 'pages/core/home',
    ],
    'services' => [
        'label' => 'Services (/services)',
        'template' => 'pages/services/services',
    ],
];

/**
 * Crée la table cms_pages si absente (évite fatal en admin / routes CMS).
 * Voir aussi database/migrations/037_cms_pages.sql
 */
function cmsEnsureCmsPagesTable(\PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS cms_pages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    site_id INT UNSIGNED NOT NULL DEFAULT 1,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL DEFAULT '',
    template VARCHAR(255) NOT NULL DEFAULT '',
    page_type VARCHAR(50) NOT NULL DEFAULT 'page',
    kind VARCHAR(50) DEFAULT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description TEXT,
    data_json LONGTEXT,
    show_in_menu TINYINT(1) NOT NULL DEFAULT 0,
    show_in_footer TINYINT(1) NOT NULL DEFAULT 0,
    show_in_sitemap TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_cms_pages_site_slug (site_id, slug),
    KEY idx_cms_pages_slug (slug),
    KEY idx_cms_pages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
    $pdo->exec($sql);
    $done = true;
}

function cmsEnsureHomePageExists(): void
{
    $pdo = db();
    cmsEnsureCmsPagesTable($pdo);
    $slug = 'home';

    $stmt = $pdo->prepare('SELECT id FROM cms_pages WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($exists) {
        return;
    }

    $defaultData = [
        'home_hero_label' => '',
        'home_hero_title' => '',
        'home_hero_subtitle' => '',
        'home_hero_primary_label' => '',
        'home_hero_primary_url' => '',
        'home_hero_secondary_label' => '',
        'home_hero_secondary_url' => '',
        'home_hero_pillars' => [],
        'home_services' => [],
        'home_stats' => [],
        'home_reality_cards' => [],
        'home_comparison' => [
            'with' => ['tag' => 'Avec accompagnement', 'title' => '', 'items' => []],
            'without' => ['tag' => 'Sans accompagnement', 'title' => '', 'items' => []],
        ],
        'home_about_title' => '',
        'home_about_text' => '',
        'home_about_benefits' => [],
        'home_steps' => [],
        'home_testimonials' => [],
        'featured_properties' => [],
        'home_market_cards' => [],
        'home_sell_guide' => [],
        'home_faq' => [],
        'home_final_cta_title' => '',
        'home_final_cta_text' => '',
    ];

    $insert = $pdo->prepare(
        'INSERT INTO cms_pages (slug, title, template, status, data_json, created_at, updated_at)
         VALUES (:slug, :title, :template, :status, :data_json, NOW(), NOW())'
    );
    $insert->execute([
        ':slug' => $slug,
        ':title' => 'Accueil',
        ':template' => 'pages/core/home',
        ':status' => 'published',
        ':data_json' => json_encode($defaultData, JSON_UNESCAPED_UNICODE),
    ]);
}

function cmsLoadPage(string $slug): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM cms_pages WHERE slug = ? LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function cmsHomeViewsPath(): string
{
    return __DIR__ . '/../../storage/cms-home-views.txt';
}

function cmsHomeViewsCount(): int
{
    $path = cmsHomeViewsPath();
    if (!is_file($path)) {
        return 0;
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return 0;
    }
    return max(0, (int)trim($raw));
}

function cmsUpdateHome(array $post): string
{
    $pdo = db();
    $slug = 'home';
    $row = cmsLoadPage($slug);
    if (!$row) {
        throw new RuntimeException('Page CMS introuvable: home');
    }

    $status = (string)($post['status'] ?? 'published');
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $title = trim((string)($post['title'] ?? 'Accueil'));
    $metaTitle = trim((string)($post['meta_title'] ?? ''));
    $metaDescription = trim((string)($post['meta_description'] ?? ''));
    $linesToList = static function (string $text): array {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $items = [];
        foreach ($lines as $line) {
            $clean = trim((string)$line);
            if ($clean !== '') {
                $items[] = $clean;
            }
        }
        return $items;
    };
    $parseBlocks = static function (string $text): array {
        $rawBlocks = preg_split("/\R{2,}/u", trim($text)) ?: [];
        $blocks = [];
        foreach ($rawBlocks as $rawBlock) {
            $lines = preg_split('/\r\n|\r|\n/', trim($rawBlock)) ?: [];
            $cleanLines = [];
            foreach ($lines as $line) {
                $line = trim((string)$line);
                if ($line !== '') {
                    $cleanLines[] = $line;
                }
            }
            if ($cleanLines !== []) {
                $blocks[] = $cleanLines;
            }
        }
        return $blocks;
    };
    $parseTitleTextBlocks = static function (string $text) use ($parseBlocks): array {
        $result = [];
        foreach ($parseBlocks($text) as $blockLines) {
            $first = (string)($blockLines[0] ?? '');
            $second = implode("\n", array_slice($blockLines, 1));
            if (strpos($first, '::') !== false) {
                [$title, $desc] = array_map('trim', explode('::', $first, 2));
                if ($desc !== '') {
                    $second = $desc . ($second !== '' ? "\n" . $second : '');
                }
                $first = $title;
            }
            if ($first === '' && $second === '') {
                continue;
            }
            $result[] = ['title' => $first, 'text' => trim($second)];
        }
        return $result;
    };
    $toTitleTextEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $title = trim((string)($item['title'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            $rows[] = $title . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $toFaqEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $q = trim((string)($item['question'] ?? ''));
            $a = trim((string)($item['answer'] ?? ''));
            if ($q === '' && $a === '') {
                continue;
            }
            $rows[] = $q . ($a !== '' ? "\n" . $a : '');
        }
        return implode("\n\n", $rows);
    };
    $toTestimonialsEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $stars = trim((string)($item['stars'] ?? '★★★★★'));
            $author = trim((string)($item['author'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($text === '' && $author === '') {
                continue;
            }
            $rows[] = $stars . ($author !== '' ? ' | ' . $author : '') . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $normalizeKeywordInput = static function (string $input, int $maxItems = 10): string {
        $raw = preg_split('/[\r\n,;|]+/', $input) ?: [];
        $items = [];
        foreach ($raw as $part) {
            $clean = trim(preg_replace('/\s+/', ' ', (string)$part));
            if ($clean === '') {
                continue;
            }
            if (mb_strlen($clean) > 50) {
                // Ignore suspiciously long chunks (often pasted content blocks)
                continue;
            }
            $items[] = $clean;
            if (count($items) >= $maxItems) {
                break;
            }
        }
        $items = array_values(array_unique($items));
        return implode(', ', $items);
    };
    $sanitizeFocusKeyword = static function (string $input): string {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $input)));
        return mb_substr($clean, 0, 70);
    };
    $focusKeyword = $sanitizeFocusKeyword((string)($post['home_seo_focus_keyword'] ?? ''));
    $secondaryKeywords = $normalizeKeywordInput((string)($post['home_seo_secondary_keywords'] ?? ''), 8);
    $semanticTerms = $normalizeKeywordInput((string)($post['home_seo_semantic_terms'] ?? ''), 10);
    if ($focusKeyword === '') {
        $focusKeyword = 'immobilier {{city}}';
    }
    if ($secondaryKeywords === '') {
        $secondaryKeywords = 'estimation immobiliere {{city}}, vente immobiliere {{city}}, achat immobilier {{city}}';
    }
    if ($semanticTerms === '') {
        $semanticTerms = 'notaire, mandat, compromis, acquereur, vendeur';
    }

    if (($post['empty_template'] ?? '') === '1') {
        $homeData = [
            'home_hero_label' => '',
            'home_hero_title' => '',
            'home_hero_subtitle' => '',
            'home_hero_primary_label' => '',
            'home_hero_primary_url' => '',
            'home_hero_secondary_label' => '',
            'home_hero_secondary_url' => '',
            'home_hero_pillars' => [],
            'home_services' => [],
            'home_stats' => [],
            'home_reality_cards' => [],
            'home_comparison' => ['with' => ['tag' => 'Avec accompagnement', 'title' => '', 'items' => []], 'without' => ['tag' => 'Sans accompagnement', 'title' => '', 'items' => []]],
            'home_about_title' => '',
            'home_about_text' => '',
            'home_about_benefits' => [],
            'home_steps' => [],
            'home_testimonials' => [],
            'featured_properties' => [],
            'home_market_cards' => [],
            'home_sell_guide' => [],
            'home_faq' => [],
            'home_final_cta_title' => '',
            'home_final_cta_text' => '',
            'home_services_section_label' => '',
            'home_services_section_title' => '',
            'home_reality_section_label' => '',
            'home_reality_section_title' => '',
            'home_reality_section_subtitle' => '',
            'home_comparison_section_label' => '',
            'home_comparison_section_title' => '',
            'home_comparison_section_subtitle' => '',
            'home_about_section_label' => '',
            'home_about_cta_label' => '',
            'home_about_cta_url' => '',
            'home_method_section_label' => '',
            'home_method_section_title' => '',
            'home_method_section_subtitle' => '',
            'home_method_primary_cta_label' => '',
            'home_method_primary_cta_url' => '',
            'home_method_secondary_cta_label' => '',
            'home_method_secondary_cta_url' => '',
            'home_testimonials_section_label' => '',
            'home_testimonials_section_title' => '',
            'home_testimonials_cta_label' => '',
            'home_testimonials_cta_url' => '',
            'home_featured_section_label' => '',
            'home_featured_section_title' => '',
            'home_featured_section_subtitle' => '',
            'home_featured_item_cta_label' => '',
            'home_featured_item_cta_url' => '',
            'home_featured_section_cta_label' => '',
            'home_featured_section_cta_url' => '',
            'home_market_section_label' => '',
            'home_market_section_title' => '',
            'home_market_section_subtitle' => '',
            'home_market_cta_label' => '',
            'home_market_cta_url' => '',
            'home_sell_section_label' => '',
            'home_sell_section_title' => '',
            'home_sell_section_subtitle' => '',
            'home_sell_cta_label' => '',
            'home_sell_cta_url' => '',
            'home_faq_section_label' => '',
            'home_faq_section_title' => '',
            'home_faq_section_subtitle' => '',
            'home_final_primary_cta_label' => '',
            'home_final_primary_cta_url' => '',
            'home_final_secondary_cta_label' => '',
            'home_final_secondary_cta_url' => '',
            'home_final_third_cta_label' => '',
            'home_final_third_cta_url' => '',
            'home_final_fourth_cta_label' => '',
            'home_final_fourth_cta_url' => '',
            'home_final_fifth_cta_label' => '',
            'home_final_fifth_cta_url' => '',
            'home_seo_focus_keyword' => '',
            'home_seo_secondary_keywords' => '',
            'home_seo_semantic_terms' => '',
        ];
    } else {
        $services = $parseTitleTextBlocks((string)($post['home_services_text'] ?? ''));
        $statsText = $parseTitleTextBlocks((string)($post['home_stats_text'] ?? ''));
        $stats = [];
        foreach ($statsText as $item) {
            $stats[] = ['value' => (string)$item['title'], 'label' => (string)$item['text']];
        }
        $stepsText = $parseTitleTextBlocks((string)($post['home_steps_text'] ?? ''));
        $steps = [];
        foreach ($stepsText as $idx => $item) {
            $num = str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT);
            $steps[] = ['num' => $num, 'title' => (string)$item['title'], 'text' => (string)$item['text']];
        }
        $faqBlocks = $parseBlocks((string)($post['home_faq_text'] ?? ''));
        $faq = [];
        foreach ($faqBlocks as $block) {
            $faq[] = [
                'question' => (string)($block[0] ?? ''),
                'answer' => trim(implode("\n", array_slice($block, 1))),
            ];
        }
        $testBlocks = $parseBlocks((string)($post['home_testimonials_text'] ?? ''));
        $testimonials = [];
        foreach ($testBlocks as $block) {
            $header = (string)($block[0] ?? '★★★★★');
            $stars = '★★★★★';
            $author = '';
            if (strpos($header, '|') !== false) {
                [$starsParsed, $authorParsed] = array_map('trim', explode('|', $header, 2));
                $stars = $starsParsed !== '' ? $starsParsed : '★★★★★';
                $author = $authorParsed;
            } else {
                $stars = $header !== '' ? $header : '★★★★★';
                $author = (string)($block[2] ?? '');
            }
            $text = trim((string)($block[1] ?? ''));
            if ($text === '' && isset($block[2]) && strpos($header, '|') !== false) {
                $text = trim(implode("\n", array_slice($block, 1, -1)));
            }
            $testimonials[] = ['stars' => $stars, 'text' => $text, 'author' => $author];
        }
        $comparisonWithItems = $linesToList((string)($post['home_comparison_with_items_text'] ?? ''));
        $comparisonWithoutItems = $linesToList((string)($post['home_comparison_without_items_text'] ?? ''));
        $properties = $parseTitleTextBlocks((string)($post['featured_properties_text'] ?? ''));
        $featuredProperties = [];
        foreach ($properties as $property) {
            $featuredProperties[] = [
                'title' => (string)$property['title'],
                'city' => '{{city}}',
                'price' => (string)$property['text'],
                'surface' => '',
                'rooms' => '',
                'badge' => 'Sélection',
                'image' => '[URL_IMAGE]',
            ];
        }

        $homeData = [
            'home_hero_label' => trim((string)($post['home_hero_label'] ?? '')),
            'home_hero_title' => trim((string)($post['home_hero_title'] ?? '')),
            'home_hero_subtitle' => trim((string)($post['home_hero_subtitle'] ?? '')),
            'home_hero_primary_label' => trim((string)($post['home_hero_primary_label'] ?? '')),
            'home_hero_primary_url' => trim((string)($post['home_hero_primary_url'] ?? '')),
            'home_hero_secondary_label' => trim((string)($post['home_hero_secondary_label'] ?? '')),
            'home_hero_secondary_url' => trim((string)($post['home_hero_secondary_url'] ?? '')),
            'home_hero_pillars' => $linesToList((string)($post['home_hero_pillars_text'] ?? '')),
            'home_services' => $services,
            'home_stats' => $stats,
            'home_reality_cards' => $parseTitleTextBlocks((string)($post['home_reality_cards_text'] ?? '')),
            'home_comparison' => [
                'with' => [
                    'tag' => trim((string)($post['home_comparison_with_tag'] ?? 'Avec accompagnement')),
                    'title' => trim((string)($post['home_comparison_with_title'] ?? '')),
                    'items' => $comparisonWithItems,
                ],
                'without' => [
                    'tag' => trim((string)($post['home_comparison_without_tag'] ?? 'Sans accompagnement')),
                    'title' => trim((string)($post['home_comparison_without_title'] ?? '')),
                    'items' => $comparisonWithoutItems,
                ],
            ],
            'home_about_title' => trim((string)($post['home_about_title'] ?? '')),
            'home_about_text' => trim((string)($post['home_about_text'] ?? '')),
            'home_about_benefits' => $linesToList((string)($post['home_about_benefits_text'] ?? '')),
            'home_steps' => $steps,
            'home_testimonials' => $testimonials,
            'featured_properties' => $featuredProperties,
            'home_market_cards' => $parseTitleTextBlocks((string)($post['home_market_cards_text'] ?? '')),
            'home_sell_guide' => $parseTitleTextBlocks((string)($post['home_sell_guide_text'] ?? '')),
            'home_faq' => $faq,
            'home_final_cta_title' => trim((string)($post['home_final_cta_title'] ?? '')),
            'home_final_cta_text' => trim((string)($post['home_final_cta_text'] ?? '')),
            'home_services_section_label' => trim((string)($post['home_services_section_label'] ?? '')),
            'home_services_section_title' => trim((string)($post['home_services_section_title'] ?? '')),
            'home_reality_section_label' => trim((string)($post['home_reality_section_label'] ?? '')),
            'home_reality_section_title' => trim((string)($post['home_reality_section_title'] ?? '')),
            'home_reality_section_subtitle' => trim((string)($post['home_reality_section_subtitle'] ?? '')),
            'home_comparison_section_label' => trim((string)($post['home_comparison_section_label'] ?? '')),
            'home_comparison_section_title' => trim((string)($post['home_comparison_section_title'] ?? '')),
            'home_comparison_section_subtitle' => trim((string)($post['home_comparison_section_subtitle'] ?? '')),
            'home_about_section_label' => trim((string)($post['home_about_section_label'] ?? '')),
            'home_about_cta_label' => trim((string)($post['home_about_cta_label'] ?? '')),
            'home_about_cta_url' => trim((string)($post['home_about_cta_url'] ?? '')),
            'home_method_section_label' => trim((string)($post['home_method_section_label'] ?? '')),
            'home_method_section_title' => trim((string)($post['home_method_section_title'] ?? '')),
            'home_method_section_subtitle' => trim((string)($post['home_method_section_subtitle'] ?? '')),
            'home_method_primary_cta_label' => trim((string)($post['home_method_primary_cta_label'] ?? '')),
            'home_method_primary_cta_url' => trim((string)($post['home_method_primary_cta_url'] ?? '')),
            'home_method_secondary_cta_label' => trim((string)($post['home_method_secondary_cta_label'] ?? '')),
            'home_method_secondary_cta_url' => trim((string)($post['home_method_secondary_cta_url'] ?? '')),
            'home_testimonials_section_label' => trim((string)($post['home_testimonials_section_label'] ?? '')),
            'home_testimonials_section_title' => trim((string)($post['home_testimonials_section_title'] ?? '')),
            'home_testimonials_cta_label' => trim((string)($post['home_testimonials_cta_label'] ?? '')),
            'home_testimonials_cta_url' => trim((string)($post['home_testimonials_cta_url'] ?? '')),
            'home_featured_section_label' => trim((string)($post['home_featured_section_label'] ?? '')),
            'home_featured_section_title' => trim((string)($post['home_featured_section_title'] ?? '')),
            'home_featured_section_subtitle' => trim((string)($post['home_featured_section_subtitle'] ?? '')),
            'home_featured_item_cta_label' => trim((string)($post['home_featured_item_cta_label'] ?? '')),
            'home_featured_item_cta_url' => trim((string)($post['home_featured_item_cta_url'] ?? '')),
            'home_featured_section_cta_label' => trim((string)($post['home_featured_section_cta_label'] ?? '')),
            'home_featured_section_cta_url' => trim((string)($post['home_featured_section_cta_url'] ?? '')),
            'home_market_section_label' => trim((string)($post['home_market_section_label'] ?? '')),
            'home_market_section_title' => trim((string)($post['home_market_section_title'] ?? '')),
            'home_market_section_subtitle' => trim((string)($post['home_market_section_subtitle'] ?? '')),
            'home_market_cta_label' => trim((string)($post['home_market_cta_label'] ?? '')),
            'home_market_cta_url' => trim((string)($post['home_market_cta_url'] ?? '')),
            'home_sell_section_label' => trim((string)($post['home_sell_section_label'] ?? '')),
            'home_sell_section_title' => trim((string)($post['home_sell_section_title'] ?? '')),
            'home_sell_section_subtitle' => trim((string)($post['home_sell_section_subtitle'] ?? '')),
            'home_sell_cta_label' => trim((string)($post['home_sell_cta_label'] ?? '')),
            'home_sell_cta_url' => trim((string)($post['home_sell_cta_url'] ?? '')),
            'home_faq_section_label' => trim((string)($post['home_faq_section_label'] ?? '')),
            'home_faq_section_title' => trim((string)($post['home_faq_section_title'] ?? '')),
            'home_faq_section_subtitle' => trim((string)($post['home_faq_section_subtitle'] ?? '')),
            'home_final_primary_cta_label' => trim((string)($post['home_final_primary_cta_label'] ?? '')),
            'home_final_primary_cta_url' => trim((string)($post['home_final_primary_cta_url'] ?? '')),
            'home_final_secondary_cta_label' => trim((string)($post['home_final_secondary_cta_label'] ?? '')),
            'home_final_secondary_cta_url' => trim((string)($post['home_final_secondary_cta_url'] ?? '')),
            'home_final_third_cta_label' => trim((string)($post['home_final_third_cta_label'] ?? '')),
            'home_final_third_cta_url' => trim((string)($post['home_final_third_cta_url'] ?? '')),
            'home_final_fourth_cta_label' => trim((string)($post['home_final_fourth_cta_label'] ?? '')),
            'home_final_fourth_cta_url' => trim((string)($post['home_final_fourth_cta_url'] ?? '')),
            'home_final_fifth_cta_label' => trim((string)($post['home_final_fifth_cta_label'] ?? '')),
            'home_final_fifth_cta_url' => trim((string)($post['home_final_fifth_cta_url'] ?? '')),
            'home_seo_focus_keyword' => $focusKeyword,
            'home_seo_secondary_keywords' => $secondaryKeywords,
            'home_seo_semantic_terms' => $semanticTerms,
        ];
    }

    if ($metaTitle === '') {
        $metaTitle = trim((string)($homeData['home_hero_title'] ?? ''));
        if ($metaTitle === '') {
            $metaTitle = 'Immobilier {{city}} | Vente, achat, estimation';
        }
        $metaTitle = mb_substr($metaTitle, 0, 60);
    }
    if ($metaDescription === '') {
        $metaDescription = trim((string)($homeData['home_hero_subtitle'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = 'Conseiller immobilier local : estimation, vente et achat avec accompagnement complet.';
        }
        $metaDescription = mb_substr($metaDescription, 0, 160);
    }

    $stmt = $pdo->prepare(
        'UPDATE cms_pages
         SET title = :title,
             template = :template,
             status = :status,
             meta_title = :meta_title,
             meta_description = :meta_description,
             data_json = :data_json,
             updated_at = NOW()
         WHERE id = :id'
    );
    $stmt->execute([
        ':title' => $title !== '' ? $title : 'Accueil',
        ':template' => 'pages/core/home',
        ':status' => $status,
        ':meta_title' => $metaTitle,
        ':meta_description' => $metaDescription,
        ':data_json' => json_encode($homeData, JSON_UNESCAPED_UNICODE),
        ':id' => (int)$row['id'],
    ]);

    return ($post['empty_template'] ?? '') === '1'
        ? 'Page Accueil vidée (template prêt à dupliquer).'
        : 'Page Accueil enregistrée.';
}

require_once __DIR__ . '/cms-generic.inc.php';
require_once dirname(__DIR__, 2) . '/core/helpers/services_page_cms.php';

function renderContent(): void
{
    require_once dirname(__DIR__, 2) . '/core/helpers/cms_page_scores.php';
    global $managedPages;
    cmsEnsureHomePageExists();
    cms_ensure_services_page();
    $cmsServicesLegacyDuplicate = cms_services_legacy_duplicate_row_exists();

    $pdo = db();
    cms_ensure_extended_columns($pdo);

    $action = preg_replace('/[^a-z_-]/', '', (string)($_GET['action'] ?? 'list'));
    $slugRaw = preg_replace('/[^a-z0-9-]/', '', (string)($_GET['slug'] ?? 'home'));
    if ($slugRaw === 'services-services' && $_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'edit' && !headers_sent()) {
        $to = function_exists('admin_url')
            ? admin_url(['module' => 'cms', 'action' => 'edit', 'slug' => 'services'])
            : '/admin/?module=cms&action=edit&slug=services';
        header('Location: ' . $to, true, 302);
        exit;
    }
    $slug = $slugRaw === 'services-services' ? 'services' : $slugRaw;

    $notice = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $slug === 'services' && isset($_POST['cms_services_save'])) {
        try {
            cms_save_services_page($_POST);
            $notice = 'Page Services enregistrée.';
        } catch (Throwable $e) {
            $error = 'Erreur sauvegarde: ' . $e->getMessage();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $slug !== 'home' && $slug !== 'services' && isset($_POST['cms_generic_save'])) {
        try {
            cms_update_generic_page($slug, $_POST);
            $notice = 'Page enregistrée.';
        } catch (Throwable $e) {
            $error = 'Erreur sauvegarde: ' . $e->getMessage();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $slug === 'home') {
        try {
            $notice = cmsUpdateHome($_POST);
        } catch (Throwable $e) {
            $error = 'Erreur sauvegarde: ' . $e->getMessage();
        }
    }

    $allCmsRows = [];
    try {
        $allCmsRows = $pdo->query('SELECT * FROM cms_pages WHERE site_id = 1 ORDER BY page_level ASC, slug ASC')->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable) {
        $allCmsRows = [];
    }
    $allCmsRows = array_values(array_filter(
        $allCmsRows,
        static function (array $r): bool {
            return (string) ($r['slug'] ?? '') !== 'services-services';
        }
    ));

    $cmsListSt = 'all';
    $cmsListSc = 'all';
    $cmsListSort = 'slug';
    $cmsListOrd = 'asc';
    $cmsListQ = '';
    $cmsListEnriched = [];
    $cmsListUrl = static function (array $over = []): string {
        $p = array_merge(['module' => 'cms'], $over);

        return function_exists('admin_url')
            ? admin_url($p)
            : ('/admin/?' . http_build_query($p, '', '&', PHP_QUERY_RFC3986));
    };
    if ($action !== 'edit') {
    $gSt = (string) ($_GET['st'] ?? 'all');
    $cmsListSt = in_array($gSt, ['all', 'published', 'draft'], true) ? $gSt : 'all';
    $gSc = (string) ($_GET['sc'] ?? 'all');
    $cmsListSc = in_array($gSc, ['all', 'low', 'medium', 'good'], true) ? $gSc : 'all';
    $gSort = (string) ($_GET['sort'] ?? '');
    $cmsListSort = in_array($gSort, ['', 'title', 'slug', 'word', 'seo', 'serp', 'sem', 'updated'], true) ? $gSort : 'slug';
    if ($cmsListSort === '') {
        $cmsListSort = 'slug';
    }
    $cmsListOrd = strtolower((string) ($_GET['ord'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
    $cmsListQ = trim((string) ($_GET['q'] ?? ''));
    $cmsListEnriched = [];
    foreach ($allCmsRows as $r) {
        $w = cms_page_word_count($r);
        $seoR = cms_page_seo_score($r);
        $semR = cms_page_semantic_score($r);
        $serpR = cms_page_serp_score($r);
        $scBand = cms_page_score_band(
            (int) min($seoR['score'], $semR['score'], $serpR['score'])
        );
        $cmsListEnriched[] = [
            'row' => $r,
            'words' => $w,
            'seo' => $seoR['score'],
            'sem' => $semR['score'],
            'serp' => $serpR['score'],
            'priority' => cms_page_priority_label($w, $seoR['score'], $semR['score'], $serpR['score']),
            'sc_band' => $scBand,
        ];
    }
    if ($cmsListSt === 'published') {
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e): bool {
                return (string) ($e['row']['status'] ?? '') === 'published';
            }
        ));
    } elseif ($cmsListSt === 'draft') {
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e): bool {
                return (string) ($e['row']['status'] ?? '') === 'draft';
            }
        ));
    }
    if ($cmsListQ !== '') {
        $qLower = mb_strtolower($cmsListQ);
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e) use ($qLower): bool {
                $t = mb_strtolower((string) ($e['row']['title'] ?? ''));
                $s = mb_strtolower((string) ($e['row']['slug'] ?? ''));

                return str_contains($t, $qLower) || str_contains($s, $qLower);
            }
        ));
    }
    if ($cmsListSc === 'low') {
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e): bool {
                $m = min((int) $e['seo'], (int) $e['sem'], (int) $e['serp']);

                return $m < 50;
            }
        ));
    } elseif ($cmsListSc === 'medium') {
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e): bool {
                $m = min((int) $e['seo'], (int) $e['sem'], (int) $e['serp']);

                return $m >= 50 && $m < 75;
            }
        ));
    } elseif ($cmsListSc === 'good') {
        $cmsListEnriched = array_values(array_filter(
            $cmsListEnriched,
            static function (array $e): bool {
                $m = min((int) $e['seo'], (int) $e['sem'], (int) $e['serp']);

                return $m >= 75;
            }
        ));
    }
    $sortFn = static function (array $a, array $b) use ($cmsListSort, $cmsListOrd): int {
        $get = static function (array $e, string $k): int|string {
            if ($k === 'word') {
                return (int) $e['words'];
            }
            if ($k === 'seo') {
                return (int) $e['seo'];
            }
            if ($k === 'serp') {
                return (int) $e['serp'];
            }
            if ($k === 'sem') {
                return (int) $e['sem'];
            }
            if ($k === 'updated') {
                return (string) ($e['row']['updated_at'] ?? '1970-01-01 00:00:00');
            }
            if ($k === 'title') {
                return mb_strtolower((string) ($e['row']['title'] ?? ''));
            }

            return mb_strtolower((string) ($e['row']['slug'] ?? ''));
        };
        $va = $get($a, $cmsListSort);
        $vb = $get($b, $cmsListSort);
        $c = is_int($va) && is_int($vb) ? $va <=> $vb : strcmp((string) $va, (string) $vb);
        if ($c === 0) {
            return strcmp(
                (string) ($a['row']['slug'] ?? ''),
                (string) ($b['row']['slug'] ?? '')
            );
        }

        return $cmsListOrd === 'desc' ? -$c : $c;
    };
    usort($cmsListEnriched, $sortFn);
    $cmsListGroupCore = [];
    $cmsListGroupSecteur = [];
    $cmsListGroupUtil = [];
    foreach ($cmsListEnriched as $e) {
        $r = $e['row'];
        if ((int) ($r['page_level'] ?? 1) === 2) {
            $cmsListGroupUtil[] = $e;
            continue;
        }
        if (cms_page_row_is_secteur_fiche($r)) {
            $cmsListGroupSecteur[] = $e;
        } else {
            $cmsListGroupCore[] = $e;
        }
    }
    $cmsListUrl = static function (array $over = []) use ($cmsListSt, $cmsListSc, $cmsListSort, $cmsListOrd, $cmsListQ): string {
        $p = array_merge(
            [
                'module' => 'cms',
                'st' => $cmsListSt,
                'sc' => $cmsListSc,
                'sort' => $cmsListSort,
                'ord' => $cmsListOrd,
                'q' => $cmsListQ,
            ],
            $over
        );
        if (($p['st'] ?? '') === 'all') {
            unset($p['st']);
        }
        if (($p['sc'] ?? '') === 'all') {
            unset($p['sc']);
        }
        if (trim((string) ($p['q'] ?? '')) === '') {
            unset($p['q']);
        }

        return function_exists('admin_url')
            ? admin_url($p)
            : ('/admin/?' . http_build_query($p, '', '&', PHP_QUERY_RFC3986));
    };
    }

    $page = null;
    if ($action === 'edit') {
        if ($slug === 'home') {
            $page = cmsLoadPage('home');
        } else {
            $page = cms_load_page_row($slug);
        }
        if (!$page) {
            $action = 'list';
            if ($error === '') {
                $error = 'Page introuvable. Vérifiez l’adresse ou revenez à la liste des pages.';
            }
        }
    }

    $homeViewsCount = cmsHomeViewsCount();
    $toTitleTextEditor = static function (array $items): string {
        $rows = [];
        foreach ($items as $item) {
            $title = trim((string)($item['title'] ?? ''));
            $text = trim((string)($item['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            $rows[] = $title . ($text !== '' ? "\n" . $text : '');
        }
        return implode("\n\n", $rows);
    };
    $data = [];
    if (is_array($page) && !empty($page['data_json'])) {
        $decoded = json_decode((string)$page['data_json'], true);
        if (is_array($decoded)) {
            $data = $decoded;
        }
    }
    if ($slug === 'home') {
        if (empty($data['home_seo_focus_keyword'])) {
            $data['home_seo_focus_keyword'] = 'immobilier {{city}}';
        }
        if (empty($data['home_seo_secondary_keywords'])) {
            $data['home_seo_secondary_keywords'] = 'estimation immobiliere {{city}}, vente immobiliere {{city}}, achat immobilier {{city}}';
        }
        if (empty($data['home_seo_semantic_terms'])) {
            $data['home_seo_semantic_terms'] = 'notaire, mandat, compromis, acquereur, vendeur';
        }
    }
    if ($slug === 'services') {
        $data['services_content'] = services_page_cms_merged_state(is_array($page) ? $page : null)['sc'];
    }

    $adminCmsList = function_exists('admin_url') ? admin_url(['module' => 'cms']) : '/admin/?module=cms';
    $sectionsGeneric = ($page && $slug !== 'home' && $slug !== 'services')
        ? CmsPageDiscovery::sectionsFromRow($page)
        : [];
    $bodySampleGen = implode(' ', $sectionsGeneric);
    $seoCheckGeneric = ($page && $slug !== 'home' && $slug !== 'services')
        ? cms_seo_checklist_generic(
            (string) ($page['meta_title'] ?? ''),
            (string) ($page['meta_description'] ?? ''),
            $bodySampleGen
        )
        : ['score' => 0, 'items' => []];
    $seoHome = ($slug === 'home' && is_array($page)) ? cms_home_seo_panel_state($page, $data) : null;
    $seoServicesPanel = null;
    $seoServicesAssist = null;
    if ($slug === 'services' && is_array($page)) {
        $sd = is_array($data['services_content'] ?? null) ? $data['services_content'] : cms_services_default_content();
        $h1a = trim((string) ($sd['hero']['h1'] ?? '')) !== '' ? trim((string) $sd['hero']['h1']) : 'Mes services';
        $bodySeo = cms_services_build_seo_indexable_text($sd, services_page_resolved_service_tuples($sd), true);
        $seoServicesPanel = cms_services_seo_score($sd, $page, $h1a, $bodySeo);
        $seoServicesAssist = cms_services_seo_assist_proposals($page, $sd, $h1a, $bodySeo);
    }
    ?>
    <style>
        .cms-wrap { display:grid; gap:20px; max-width:1440px; margin:0 auto; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color:#18181b; }
        .cms-hero { background:linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #0f172a 100%); border-radius:16px; padding:24px 28px; color:#fff; box-shadow: 0 20px 50px -20px rgba(15,23,42,.45); border:1px solid rgba(255,255,255,.08); }
        .cms-hero h1 { margin:0 0 6px; font-size:26px; font-weight:700; letter-spacing:-.02em; }
        .cms-hero p { margin:0; color:rgba(248,250,252,.72); font-size:14px; line-height:1.5; }
        .cms-card { background:#fff; border:1px solid #e4e4e7; border-radius:16px; padding:0; box-shadow: 0 4px 24px -8px rgba(15,23,42,.08); overflow:hidden; }
        .cms-card > h2 { margin:0; padding:20px 24px; font-size:18px; font-weight:700; letter-spacing:-.02em; border-bottom:1px solid #f4f4f5; background:linear-gradient(180deg, #fafafa 0%, #fff 100%); }
        .cms-card .cms-edit-card-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; padding:20px 24px; border-bottom:1px solid #f4f4f5; background:linear-gradient(180deg, #fafafa 0%, #fff 100%); }
        .cms-card .cms-edit-card-head h2 { margin:0; padding:0; border:0; font-size:18px; font-weight:700; letter-spacing:-.02em; background:transparent; }
        .cms-edit-view-link { display:inline-flex; align-items:center; gap:8px; font-size:14px; font-weight:650; text-decoration:none; color:#0f172a; border:1px solid #e4e4e7; border-radius:10px; padding:8px 14px; background:#fff; flex-shrink:0; }
        .cms-edit-view-link:hover { background:#f4f4f5; border-color:#d4d4d8; color:#0f172a; }
        .cms-edit-view-ico { display:block; flex-shrink:0; }
        .cms-card .notice, .cms-card .error { margin:16px 24px 0; }
        .cms-list { display:grid; gap:10px; padding:20px 24px 24px; }
        .cms-list a { display:flex; justify-content:space-between; align-items:center; border:1px solid #e4e4e7; border-radius:12px; padding:14px 16px; text-decoration:none; color:#18181b; background:#fafafa; transition: border-color .15s, box-shadow .15s, background .15s; }
        .cms-list a:hover { border-color:#d4d4d8; background:#fff; box-shadow: 0 4px 12px -4px rgba(0,0,0,.06); }
        .cms-form { padding:20px 24px 28px; display:grid; gap:0; }
        .cms-editor-layout { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:24px; align-items:start; }
        .cms-editor-main { min-width:0; display:grid; gap:16px; }
        .cms-editor-rail { min-width:0; }
        .cms-section { background:#fff; border:1px solid #e4e4e7; border-radius:14px; padding:20px 22px; box-shadow: 0 1px 0 rgba(0,0,0,.03); }
        .cms-section h3 { margin:0 0 16px; color:#09090b; font-size:15px; font-weight:700; letter-spacing:-.01em; padding-bottom:12px; border-bottom:1px solid #f4f4f5; }
        .cms-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 16px; }
        .cms-form .row { display:grid; gap:14px; grid-template-columns:1fr 1fr; }
        .cms-form label { display:block; font-size:11px; font-weight:650; color:#71717a; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px; }
        .cms-form input, .cms-form textarea, .cms-form select { width:100%; border:1px solid #e4e4e7; border-radius:10px; padding:11px 14px; font-size:14px; color:#18181b; background:#fafafa; transition: border-color .15s, box-shadow .15s, background .15s; }
        .cms-form input:hover, .cms-form textarea:hover, .cms-form select:hover { border-color:#d4d4d8; background:#fff; }
        .cms-form input:focus, .cms-form textarea:focus, .cms-form select:focus { outline:none; border-color:#a1a1aa; background:#fff; box-shadow: 0 0 0 3px rgba(24,24,27,.06); }
        .cms-form textarea { min-height:96px; resize:vertical; line-height:1.55; }
        .cms-form hr { border:0; border-top:1px solid #f4f4f5; margin:18px 0; }
        .cms-form h3:not(.cms-section h3) { font-size:13px; font-weight:700; color:#52525b; margin:20px 0 8px; letter-spacing:-.01em; }
        .cms-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:8px; padding-top:20px; border-top:1px solid #f4f4f5; }
        .btn { border:0; border-radius:10px; padding:11px 18px; cursor:pointer; font-weight:650; font-size:14px; transition: transform .12s, box-shadow .12s, background .12s; }
        .btn-primary { background:linear-gradient(180deg, #18181b 0%, #09090b 100%); color:#fafafa; box-shadow: 0 2px 8px -2px rgba(0,0,0,.25); }
        .btn-primary:hover { box-shadow: 0 6px 20px -4px rgba(0,0,0,.3); transform: translateY(-1px); }
        .btn-light { background:#f4f4f5; color:#18181b; text-decoration:none; display:inline-flex; align-items:center; border:1px solid #e4e4e7; }
        .btn-light:hover { background:#e4e4e7; }
        .notice { background:#ecfdf5; color:#14532d; border:1px solid #86efac; padding:12px 14px; border-radius:10px; font-size:14px; }
        .error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:12px 14px; border-radius:10px; font-size:14px; }
        .cms-seo-panel { position:sticky; top:16px; z-index:4; background:linear-gradient(165deg, #1f2937 0%, #334155 55%, #1f2937 100%); border:1px solid rgba(255,255,255,.14); border-radius:16px; padding:20px 20px 22px; box-shadow: 0 20px 42px -20px rgba(15,23,42,.42), inset 0 1px 0 rgba(255,255,255,.08); }
        .cms-seo-panel::before { content:''; position:absolute; left:0; top:18px; bottom:18px; width:3px; border-radius:0 3px 3px 0; background:linear-gradient(180deg, #d4af37, #b8860b); }
        .cms-editor-rail .cms-seo-panel { position:sticky; top:16px; }
        .cms-seo-panel__head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:18px; padding-left:8px; }
        .cms-seo-panel__head h3 { margin:0; font-size:14px; font-weight:700; color:#f8fafc; letter-spacing:-.02em; }
        .cms-seo-panel__badge { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#0f172a; background:linear-gradient(135deg, #fde68a, #d4af37); padding:4px 8px; border-radius:999px; }
        .cms-seo-score { display:flex; align-items:center; gap:14px; margin-bottom:18px; padding:14px 14px 14px 18px; background:rgba(15,23,42,.25); border-radius:12px; border:1px solid rgba(255,255,255,.12); }
        .cms-seo-score-badge { width:56px; height:56px; min-width:56px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; color:#fff; background:linear-gradient(145deg, #64748b, #475569); box-shadow: inset 0 1px 0 rgba(255,255,255,.15); transition: background .2s; }
        .cms-seo-score-badge.is-good { background:linear-gradient(145deg, #22c55e, #15803d); }
        .cms-seo-score-badge.is-warn { background:linear-gradient(145deg, #f59e0b, #d97706); }
        .cms-seo-score-badge.is-bad { background:linear-gradient(145deg, #ef4444, #b91c1c); }
        .cms-seo-score-text { display:flex; flex-direction:column; gap:2px; }
        .cms-seo-score-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:#cbd5e1; }
        .cms-seo-score-text strong { font-size:15px; font-weight:700; color:#ffffff; letter-spacing:-.02em; }
        .cms-seo-field { margin-bottom:14px; padding-left:8px; }
        .cms-seo-field label { color:#e2e8f0 !important; margin-bottom:7px !important; }
        .cms-seo-panel input { background:rgba(15,23,42,.34) !important; border-color:rgba(148,163,184,.45) !important; color:#f8fafc !important; }
        .cms-seo-panel input:focus { border-color:rgba(212,175,55,.55) !important; box-shadow: 0 0 0 3px rgba(212,175,55,.12) !important; }
        .cms-field-hint { display:block; font-size:11px; color:#cbd5e1; margin-top:5px; }
        .cms-seo-checklist { list-style:none; margin:16px 0 0; padding:8px 0 0 8px; border-top:1px solid rgba(255,255,255,.12); display:grid; gap:8px; }
        .cms-seo-checklist li { font-size:12px; padding:9px 11px; border-radius:10px; border:1px solid rgba(148,163,184,.24); background:rgba(15,23,42,.28); color:#f1f5f9; line-height:1.45; }
        .cms-seo-checklist li.ok { border-color:rgba(34,197,94,.35); color:#bbf7d0; background:rgba(22,163,74,.12); }
        .cms-seo-checklist li.bad { border-color:rgba(248,113,113,.3); color:#fecaca; background:rgba(220,38,38,.12); }
        .cms-seo-help { font-size:11px; color:#e2e8f0; margin:14px 0 0 8px; line-height:1.55; }
        .cms-seo-metrics { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin:0 0 14px 8px; }
        .cms-seo-metric { background:rgba(15,23,42,.35); border:1px solid rgba(148,163,184,.2); border-radius:10px; padding:8px 10px; display:grid; gap:2px; }
        .cms-seo-metric span { font-size:10px; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; }
        .cms-seo-metric strong { font-size:13px; color:#f8fafc; }
        .cms-rail-stack { display:grid; gap:16px; }
        .cms-widget { background:#fff; border:1px solid #e4e4e7; border-radius:14px; padding:16px 18px; font-size:13px; color:#334155; }
        .cms-widget h4 { margin:0 0 10px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
        .cms-widget code { font-size:11px; word-break:break-all; background:#f4f4f5; padding:2px 6px; border-radius:4px; }
        .cms-share-preview { border:1px dashed #cbd5e1; border-radius:10px; padding:12px; background:#f8fafc; }
        .cms-share-preview img { max-width:100%; border-radius:8px; margin-top:8px; max-height:140px; object-fit:cover; }
        .cms-share-preview .t { font-weight:700; color:#0f172a; font-size:14px; margin-bottom:4px; }
        .cms-share-preview .d { font-size:12px; color:#64748b; line-height:1.45; }
        .cms-pages-dashboard { overflow:hidden; }
        .cms-pages-toolbar { display:flex; flex-wrap:wrap; gap:12px 16px; align-items:flex-end; padding:16px 24px; border-bottom:1px solid #f4f4f5; background:linear-gradient(180deg, #fafafa 0%, #fff 100%); }
        .cms-pages-toolbar .cms-pages-field label { display:block; font-size:10px; font-weight:650; text-transform:uppercase; letter-spacing:.06em; color:#71717a; margin-bottom:4px; }
        .cms-pages-toolbar input[type="search"], .cms-pages-toolbar select { min-width:160px; border:1px solid #e4e4e7; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff; }
        .cms-pages-toolbar button[type="submit"] { margin-top:18px; }
        .cms-pages-table-wrap { padding:0; overflow-x:auto; display:block; }
        .cms-pages-table { width:100%; border-collapse:collapse; font-size:13px; }
        .cms-pages-table th { text-align:left; padding:10px 12px; border-bottom:1px solid #e4e4e7; color:#52525b; font-size:11px; text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; background:#fafafa; }
        .cms-pages-table th a { color:#0f172a; text-decoration:none; font-weight:700; }
        .cms-pages-table th a:hover { text-decoration:underline; }
        .cms-pages-table td { padding:10px 12px; border-bottom:1px solid #f4f4f5; vertical-align:middle; }
        .cms-page-row:hover td { background:#fafafa; }
        .cms-page-title strong { display:block; font-weight:700; color:#0f172a; }
        .cms-page-slug { display:block; font-size:11px; color:#64748b; margin-top:2px; font-family:ui-monospace,monospace; }
        .cms-page-template { display:block; font-size:10px; color:#a1a1aa; }
        .cms-page-url .cms-page-view-btn {
            display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:650;
            padding:6px 12px; border-radius:8px; border:1px solid #e4e4e7; background:#fff; color:#0f172a;
            text-decoration:none; white-space:nowrap; cursor:pointer;
        }
        .cms-page-url .cms-page-view-btn:hover { background:#f4f4f5; border-color:#d4d4d8; color:#0f172a; }
        .cms-page-status { display:inline-flex; font-size:11px; font-weight:650; padding:3px 8px; border-radius:6px; }
        .cms-page-status--published { background:#dcfce7; color:#166534; }
        .cms-page-status--draft { background:#f4f4f5; color:#52525b; }
        .cms-page-word-count { font-variant-numeric:tabular-nums; text-align:right; }
        .cms-score { display:inline-flex; min-width:2.2rem; justify-content:center; font-size:12px; font-weight:800; padding:3px 8px; border-radius:6px; font-variant-numeric:tabular-nums; }
        .cms-score--low { background:#fee2e2; color:#991b1b; }
        .cms-score--medium { background:#fef3c7; color:#92400e; }
        .cms-score--good { background:#d1fae5; color:#065f46; }
        .cms-page-priority { font-size:12px; line-height:1.35; max-width:12rem; }
        .cms-page-priority--priority { color:#b91c1c; font-weight:650; }
        .cms-page-priority--enrich { color:#b45309; }
        .cms-page-priority--ok { color:#166534; }
        .cms-page-priority--excellent { color:#0f766e; font-weight:700; }
        .cms-page-priority--neutral { color:#64748b; }
        .cms-page-updated { font-size:12px; color:#52525b; white-space:nowrap; }
        .cms-page-level { font-size:10px; color:#a1a1aa; }
        .cms-page-actions { display:flex; flex-wrap:wrap; gap:6px; }
        .cms-page-actions a, .cms-page-actions .btn-linky { font-size:12px; font-weight:650; text-decoration:none; border-radius:8px; padding:6px 10px; border:1px solid #e4e4e7; color:#0f172a; background:#fff; }
        .cms-page-actions a:hover, .cms-page-actions .btn-linky:hover { background:#f4f4f5; }
        .cms-page-actions .is-primary { background:#18181b; color:#fafafa; border-color:#18181b; }
        .cms-page-actions .is-primary:hover { background:#27272a; }
        .cms-pages-group { border-top:1px solid #f4f4f5; }
        .cms-pages-group:first-of-type { border-top:0; }
        .cms-pages-group__title { margin:0; padding:18px 24px 6px; font-size:16px; font-weight:700; color:#0f172a; letter-spacing:-.02em; }
        .cms-pages-group__hint { margin:0; padding:0 24px 14px; font-size:13px; color:#64748b; line-height:1.5; max-width:72ch; }
        .cms-pages-group--core .cms-pages-group__title { color:#0f172a; }
        .cms-pages-group--secteur .cms-pages-group__title { color:#1e40af; }
        .cms-pages-group--util .cms-pages-group__title { color:#52525b; }
        .cms-pages-group__empty { margin:0 24px 16px; padding:12px 14px; font-size:13px; color:#64748b; background:#fafafa; border:1px dashed #e4e4e7; border-radius:10px; }
        .cms-pages-cards { display:none; }
        .cms-page-card { border:1px solid #e4e4e7; border-radius:12px; padding:14px 16px; background:#fff; }
        @media (max-width: 900px) {
            .cms-pages-group .cms-pages-table-wrap { display:none; }
            .cms-pages-group .cms-pages-cards { display:grid; gap:10px; padding:0 16px 16px; }
        }
        @media (min-width: 901px) {
            .cms-pages-group .cms-pages-cards { display:none; }
        }
        @media (max-width: 1180px) {
            .cms-editor-layout { grid-template-columns:1fr; }
            .cms-editor-rail .cms-seo-panel { position:static; }
            .cms-editor-main { order: 2; }
            .cms-editor-rail { order: 1; }
            .cms-meta-grid { grid-template-columns:1fr; }
        }
    </style>

    <section class="cms-wrap">
        <header class="cms-hero">
            <h1>Contenu du site vitrine</h1>
            <p>Ici vous modifiez les textes qui s’affichent sur votre site&nbsp;: accueil, pages d’information, villes ou quartiers suivis dans votre stratégie locale, ainsi que les écrans de confirmation ou messages techniques (peu ou pas affichés sur Google).</p>
        </header>

        <?php if ($action !== 'edit'): ?>
            <?php
            $editUrl = static function (string $s): string {
                $q = ['module' => 'cms', 'action' => 'edit', 'slug' => $s];

                return function_exists('admin_url') ? admin_url($q) : ('/admin/?' . http_build_query($q));
            };
            $sortLink = static function (string $key, string $label) use ($cmsListUrl, $cmsListSort, $cmsListOrd): string {
                $is = $cmsListSort === $key;
                $next = $is && $cmsListOrd === 'asc' ? 'desc' : 'asc';
                $u = $cmsListUrl($is ? ['sort' => $key, 'ord' => $next] : ['sort' => $key, 'ord' => 'asc']);
                $arrow = $is ? ($cmsListOrd === 'asc' ? ' ↑' : ' ↓') : '';

                return '<a href="' . htmlspecialchars($u, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . $arrow . '</a>';
            };
            $viewOnline = static function (array $r): string {
                if (!function_exists('url')) {
                    $p = cms_page_public_path($r);
                    if (defined('APP_URL')) {
                        return rtrim((string) APP_URL, '/') . $p;
                    }

                    return $p;
                }

                return url(cms_page_public_path($r));
            };
            ?>
            <div class="cms-card cms-pages-dashboard" id="cms-global-page-list">
                <h2>Liste de vos pages</h2>
                <p style="margin:0;padding:4px 24px 16px;font-size:14px;color:#64748b;line-height:1.5;border-bottom:1px solid #f4f4f5"><strong>Pages principales</strong> : accueil, services, contact, textes centraux de votre présentation.<br><strong>Villes / secteurs</strong> : fiches géographiques et contenus pensés pour le référencement local.<br><strong>Écrans secondaires</strong> : remerciements après formulaires, messages d’erreur — rarement indexés ; priorité rédactionnelle plus faible.</p>
                <form class="cms-pages-toolbar" method="get" action="<?= htmlspecialchars(function_exists('admin_url') ? admin_url(['module' => 'cms']) : '/admin/?module=cms', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="module" value="cms">
                    <div class="cms-pages-field">
                        <label for="cms-q">Recherche</label>
                        <input id="cms-q" type="search" name="q" value="<?= htmlspecialchars($cmsListQ, ENT_QUOTES, 'UTF-8') ?>" placeholder="Titre de page ou mot-clé dans l’adresse…" autocomplete="off">
                    </div>
                    <div class="cms-pages-field">
                        <label for="cms-st">Statut</label>
                        <select id="cms-st" name="st">
                            <option value="all"<?= $cmsListSt === 'all' ? ' selected' : '' ?>>Tous</option>
                            <option value="published"<?= $cmsListSt === 'published' ? ' selected' : '' ?>>Publié</option>
                            <option value="draft"<?= $cmsListSt === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                    <div class="cms-pages-field">
                        <label for="cms-sc">Qualité rédaction SEO (global)</label>
                        <select id="cms-sc" name="sc">
                            <option value="all"<?= $cmsListSc === 'all' ? ' selected' : '' ?>>Tous</option>
                            <option value="low"<?= $cmsListSc === 'low' ? ' selected' : '' ?>>Faible (0–49)</option>
                            <option value="medium"<?= $cmsListSc === 'medium' ? ' selected' : '' ?>>Moyen (50–74)</option>
                            <option value="good"<?= $cmsListSc === 'good' ? ' selected' : '' ?>>Bon (75–100)</option>
                        </select>
                    </div>
                    <div class="cms-pages-field" style="display:none" aria-hidden="true">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($cmsListSort, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="ord" value="<?= htmlspecialchars($cmsListOrd, ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Appliquer</button>
                </form>
                <?php if ($cmsListEnriched === []): ?>
                    <p class="cms-pages-group__empty" style="margin:16px 24px 20px">Aucune page ne correspond à votre recherche. Essayez un autre mot ou réinitialisez les filtres ci-dessus.</p>
                <?php else: ?>
                <?php
                $cmsDashboardGroups = [
                    [
                        'id' => 'core',
                        'title' => 'Pages principales du site',
                        'hint' => 'À renseigner en priorité : ce sont vos textes vitrine (valeur ajoutée, offre, coordonnées, prise de contact).',
                        'rows' => $cmsListGroupCore,
                    ],
                    [
                        'id' => 'secteur',
                        'title' => 'Villes et secteurs',
                        'hint' => 'Pour chaque zone que vous prospectez ou couvrez : des contenus localement pertinents aident vos pages à être trouvées sur votre secteur.',
                        'rows' => $cmsListGroupSecteur,
                    ],
                    [
                        'id' => 'util',
                        'title' => 'Pages utilitaires (secondaires)',
                        'hint' => 'Merci après envoi de formulaire, messages techniques — utiles au parcours visiteur, sans impact majeur sur votre visibilité Google.',
                        'rows' => $cmsListGroupUtil,
                    ],
                ];
                foreach ($cmsDashboardGroups as $dg):
                    $gRows = $dg['rows'];
                ?>
                <section class="cms-pages-group cms-pages-group--<?= htmlspecialchars((string) $dg['id'], ENT_QUOTES, 'UTF-8') ?>" aria-labelledby="cms-group-h-<?= htmlspecialchars((string) $dg['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <h3 class="cms-pages-group__title" id="cms-group-h-<?= htmlspecialchars((string) $dg['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $dg['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="cms-pages-group__hint"><?= htmlspecialchars((string) $dg['hint'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($gRows === []): ?>
                    <p class="cms-pages-group__empty">Pas de page dans cette catégorie pour l’instant, ou filtres trop restrictifs.</p>
                    <?php else: ?>
                <div class="cms-pages-table-wrap" role="region" aria-label="<?= htmlspecialchars((string) $dg['title'], ENT_QUOTES, 'UTF-8') ?>">
                    <table class="cms-pages-table">
                        <thead>
                        <tr>
                            <th><?= $sortLink('title', 'Page') ?></th>
                            <th>Voir</th>
                            <th><span>Réf.&nbsp;URL</span></th>
                            <th>Statut</th>
                            <th><?= $sortLink('word', 'Mots') ?></th>
                            <th><?= $sortLink('seo', 'SEO') ?></th>
                            <th><?= $sortLink('sem', 'Sémantique') ?></th>
                            <th><?= $sortLink('serp', 'SERP') ?></th>
                            <th>Priorité</th>
                            <th><?= $sortLink('updated', 'Modif.') ?></th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($gRows as $e):
                            $r = $e['row'];
                            $publicUrl = $viewOnline($r);
                            $s = (string) ($r['slug'] ?? '');
                            $publ = (string) ($r['status'] ?? '') === 'published';
                            $pCls = $e['priority']['kind'] ?? 'neutral';
                            $pLabel = (string) ($e['priority']['label'] ?? '—');
                            $eso = (int) $e['seo'];
                            $esm = (int) $e['sem'];
                            $esr = (int) $e['serp'];
                            $up = (string) ($r['updated_at'] ?? $r['created_at'] ?? '');
                            $upTs = $up !== '' ? strtotime($up) : false;
                            $upDisp = $upTs !== false ? date('d/m/Y H:i', (int) $upTs) : '—';
                            $lvl = (int) ($r['page_level'] ?? 1);
                        ?>
                        <tr class="cms-page-row">
                            <td class="cms-page-title">
                                <span class="cms-page-level">Priorité <?= (int) $lvl ?></span>
                                <strong><?= htmlspecialchars((string) ($r['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="cms-page-template"><?= htmlspecialchars((string) ($r['template'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="cms-page-url">
                                <a class="cms-page-view-btn" href="<?= htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8') ?>">Voir la page</a>
                            </td>
                            <td><span class="cms-page-slug"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><span class="cms-page-status <?= $publ ? 'cms-page-status--published' : 'cms-page-status--draft' ?>"><?= $publ ? 'Publié' : 'Brouillon' ?></span></td>
                            <td class="cms-page-word-count"><?= (int) $e['words'] ?></td>
                            <td><span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($eso), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $eso ?></span></td>
                            <td><span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($esm), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $esm ?></span></td>
                            <td><span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($esr), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $esr ?></span></td>
                            <td class="cms-page-priority cms-page-priority--<?= htmlspecialchars($pCls, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="cms-page-updated"><?= htmlspecialchars($upDisp, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="cms-page-actions">
                                <a class="is-primary" href="<?= htmlspecialchars($editUrl($s) . '#cms-seo-panel', ENT_QUOTES, 'UTF-8') ?>">Éditer</a>
                                <a href="<?= htmlspecialchars($editUrl($s) . '#cms-seo-panel', ENT_QUOTES, 'UTF-8') ?>">Conseils SEO</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="cms-pages-cards" role="list">
                    <?php foreach ($gRows as $e):
                        $r = $e['row'];
                        $publicUrl = $viewOnline($r);
                        $s = (string) ($r['slug'] ?? '');
                        $publ = (string) ($r['status'] ?? '') === 'published';
                        $pCls = $e['priority']['kind'] ?? 'neutral';
                        $pLabel = (string) ($e['priority']['label'] ?? '—');
                        $eso = (int) $e['seo'];
                        $esm = (int) $e['sem'];
                        $esr = (int) $e['serp'];
                        $up = (string) ($r['updated_at'] ?? $r['created_at'] ?? '');
                        $upTs2 = $up !== '' ? strtotime($up) : false;
                        $upDisp = $upTs2 !== false ? date('d/m/Y H:i', (int) $upTs2) : '—';
                        $lvl = (int) ($r['page_level'] ?? 1);
                    ?>
                    <div class="cms-page-card" role="listitem">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap">
                            <div>
                                <div style="font-size:10px;color:#a1a1aa">Priorité <?= (int) $lvl ?></div>
                                <strong style="font-size:15px"><?= htmlspecialchars((string) ($r['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="cms-page-slug" style="margin-top:4px"><?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                            <span class="cms-page-status <?= $publ ? 'cms-page-status--published' : 'cms-page-status--draft' ?>"><?= $publ ? 'Publié' : 'Brouillon' ?></span>
                        </div>
                        <p class="cms-page-url" style="margin:10px 0 4px">
                            <a class="cms-page-view-btn" href="<?= htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8') ?>">Voir la page</a>
                        </p>
                        <p style="margin:0 0 8px;font-size:12px;color:#52525b">Mots <strong><?= (int) $e['words'] ?></strong> · modif. <?= htmlspecialchars($upDisp, ENT_QUOTES, 'UTF-8') ?></p>
                        <p style="margin:0 0 6px">SEO <span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($eso), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $eso ?></span>
                            Sém. <span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($esm), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $esm ?></span>
                            SERP <span class="cms-score <?= htmlspecialchars(cms_page_score_badge_class($esr), ENT_QUOTES, 'UTF-8') ?>"><?= (int) $esr ?></span></p>
                        <p class="cms-page-priority cms-page-priority--<?= htmlspecialchars($pCls, ENT_QUOTES, 'UTF-8') ?>" style="margin:0 0 10px"><?= htmlspecialchars($pLabel, ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="cms-page-actions">
                            <a class="is-primary" href="<?= htmlspecialchars($editUrl($s) . '#cms-seo-panel', ENT_QUOTES, 'UTF-8') ?>">Éditer</a>
                            <a href="<?= htmlspecialchars($editUrl($s) . '#cms-seo-panel', ENT_QUOTES, 'UTF-8') ?>">Conseils SEO</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                    <?php endif; ?>
                </section>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($slug === 'home'): ?>
            <?php $cmsEditPublicViewUrl = is_array($page) ? cms_page_public_url($page) : (function_exists('url') ? url('/') : '/'); ?>
            <div class="cms-card">
                <div class="cms-edit-card-head">
                    <h2>Édition — Accueil</h2>
                    <a class="cms-edit-view-link" href="<?= htmlspecialchars($cmsEditPublicViewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Ouvrir l’accueil du site">
                        <svg class="cms-edit-view-ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <span>Voir la page</span>
                    </a>
                </div>
                <?php if ($notice !== ''): ?><div class="notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form method="post" class="cms-form">
                    <div class="cms-editor-layout">
                    <div class="cms-editor-main">
                    <section class="cms-section">
                    <h3>Paramètres de page</h3>
                    <div class="cms-meta-grid">
                    <div>
                        <label>Titre page</label>
                        <input type="text" name="title" value="<?= htmlspecialchars((string)($page['title'] ?? 'Accueil'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Statut</label>
                        <select name="status">
                            <?php $status = (string)($page['status'] ?? 'published'); ?>
                            <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Publié</option>
                            <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                    </div>
                    <div>
                        <label>Meta title</label>
                        <input type="text" id="meta-title" name="meta_title" value="<?= htmlspecialchars((string)($page['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Meta description</label>
                        <textarea id="meta-description" name="meta_description"><?= htmlspecialchars((string)($page['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    </section>
                    <section class="cms-section">
                    <h3>Contenu de la page</h3>
                    <div>
                        <label>Hero label</label>
                        <input type="text" name="home_hero_label" value="<?= htmlspecialchars((string)($data['home_hero_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Hero title</label>
                        <input type="text" id="home-hero-title" name="home_hero_title" value="<?= htmlspecialchars((string)($data['home_hero_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Hero subtitle</label>
                        <textarea id="home-hero-subtitle" name="home_hero_subtitle"><?= htmlspecialchars((string)($data['home_hero_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA principal - Label</label>
                            <input type="text" name="home_hero_primary_label" value="<?= htmlspecialchars((string)($data['home_hero_primary_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA principal - URL</label>
                            <input type="text" name="home_hero_primary_url" value="<?= htmlspecialchars((string)($data['home_hero_primary_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA secondaire - Label</label>
                            <input type="text" name="home_hero_secondary_label" value="<?= htmlspecialchars((string)($data['home_hero_secondary_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA secondaire - URL</label>
                            <input type="text" name="home_hero_secondary_url" value="<?= htmlspecialchars((string)($data['home_hero_secondary_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <hr>
                    <h3>Piliers Hero (1 ligne = 1 élément)</h3>
                    <textarea name="home_hero_pillars_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_hero_pillars'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>En-tête Services</h3>
                    <div class="row">
                        <div>
                            <label>Label section</label>
                            <input type="text" name="home_services_section_label" value="<?= htmlspecialchars((string)($data['home_services_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>Titre section</label>
                            <input type="text" name="home_services_section_title" value="<?= htmlspecialchars((string)($data['home_services_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>Services (bloc par service: titre puis texte)</h3>
                    <textarea name="home_services_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_services'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>KPI (bloc: valeur puis libellé)</h3>
                    <textarea name="home_stats_text"><?php
                        $statsRows = [];
                        foreach ((array)($data['home_stats'] ?? []) as $stat) {
                            $statsRows[] = trim((string)($stat['value'] ?? '')) . "\n" . trim((string)($stat['label'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($statsRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <h3>Problématiques clients (bloc: titre puis texte)</h3>
                    <textarea name="home_reality_cards_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_reality_cards'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <h3>En-tête Réalité</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_reality_section_label" value="<?= htmlspecialchars((string)($data['home_reality_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <textarea name="home_reality_section_title"><?= htmlspecialchars((string)($data['home_reality_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_reality_section_subtitle"><?= htmlspecialchars((string)($data['home_reality_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Comparaison - Avec accompagnement</h3>
                    <div>
                        <label>Label section comparaison</label>
                        <input type="text" name="home_comparison_section_label" value="<?= htmlspecialchars((string)($data['home_comparison_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section comparaison</label>
                        <input type="text" name="home_comparison_section_title" value="<?= htmlspecialchars((string)($data['home_comparison_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section comparaison</label>
                        <textarea name="home_comparison_section_subtitle"><?= htmlspecialchars((string)($data['home_comparison_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Tag</label>
                        <input type="text" name="home_comparison_with_tag" value="<?= htmlspecialchars((string)($data['home_comparison']['with']['tag'] ?? 'Avec accompagnement'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre</label>
                        <input type="text" name="home_comparison_with_title" value="<?= htmlspecialchars((string)($data['home_comparison']['with']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Points (1 ligne = 1 point)</label>
                        <textarea name="home_comparison_with_items_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_comparison']['with']['items'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Comparaison - Sans accompagnement</h3>
                    <div>
                        <label>Tag</label>
                        <input type="text" name="home_comparison_without_tag" value="<?= htmlspecialchars((string)($data['home_comparison']['without']['tag'] ?? 'Sans accompagnement'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre</label>
                        <input type="text" name="home_comparison_without_title" value="<?= htmlspecialchars((string)($data['home_comparison']['without']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Points (1 ligne = 1 point)</label>
                        <textarea name="home_comparison_without_items_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_comparison']['without']['items'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Conseiller</h3>
                    <div>
                        <label>Label section conseiller</label>
                        <input type="text" name="home_about_section_label" value="<?= htmlspecialchars((string)($data['home_about_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section conseiller</label>
                        <input type="text" name="home_about_title" value="<?= htmlspecialchars((string)($data['home_about_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Texte section conseiller</label>
                        <textarea name="home_about_text"><?= htmlspecialchars((string)($data['home_about_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div>
                        <label>Bénéfices conseiller (1 ligne = 1 bénéfice)</label>
                        <textarea name="home_about_benefits_text"><?= htmlspecialchars(implode("\n", array_map('strval', (array)($data['home_about_benefits'] ?? []))), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>Bouton conseiller - Label</label>
                            <input type="text" name="home_about_cta_label" value="<?= htmlspecialchars((string)($data['home_about_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>Bouton conseiller - URL</label>
                            <input type="text" name="home_about_cta_url" value="<?= htmlspecialchars((string)($data['home_about_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Méthode</h3>
                    <div>
                        <label>Label section méthode</label>
                        <input type="text" name="home_method_section_label" value="<?= htmlspecialchars((string)($data['home_method_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section méthode</label>
                        <input type="text" name="home_method_section_title" value="<?= htmlspecialchars((string)($data['home_method_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section méthode</label>
                        <textarea name="home_method_section_subtitle"><?= htmlspecialchars((string)($data['home_method_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Méthode (bloc: titre puis texte, numérotation automatique)</h3>
                    <textarea name="home_steps_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_steps'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA méthode principal - Label</label>
                            <input type="text" name="home_method_primary_cta_label" value="<?= htmlspecialchars((string)($data['home_method_primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA méthode principal - URL</label>
                            <input type="text" name="home_method_primary_cta_url" value="<?= htmlspecialchars((string)($data['home_method_primary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA méthode secondaire - Label</label>
                            <input type="text" name="home_method_secondary_cta_label" value="<?= htmlspecialchars((string)($data['home_method_secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA méthode secondaire - URL</label>
                            <input type="text" name="home_method_secondary_cta_url" value="<?= htmlspecialchars((string)($data['home_method_secondary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Témoignages</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_testimonials_section_label" value="<?= htmlspecialchars((string)($data['home_testimonials_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_testimonials_section_title" value="<?= htmlspecialchars((string)($data['home_testimonials_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <h3>Témoignages (bloc: "★★★★★ | Auteur" puis texte)</h3>
                    <textarea name="home_testimonials_text"><?php
                        $testRows = [];
                        foreach ((array)($data['home_testimonials'] ?? []) as $t) {
                            $header = trim((string)($t['stars'] ?? '★★★★★')) . ' | ' . trim((string)($t['author'] ?? ''));
                            $text = trim((string)($t['text'] ?? ''));
                            $testRows[] = trim($header) . ($text !== '' ? "\n" . $text : '');
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($testRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA témoignages - Label</label>
                            <input type="text" name="home_testimonials_cta_label" value="<?= htmlspecialchars((string)($data['home_testimonials_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA témoignages - URL</label>
                            <input type="text" name="home_testimonials_cta_url" value="<?= htmlspecialchars((string)($data['home_testimonials_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Biens</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_featured_section_label" value="<?= htmlspecialchars((string)($data['home_featured_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_featured_section_title" value="<?= htmlspecialchars((string)($data['home_featured_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_featured_section_subtitle"><?= htmlspecialchars((string)($data['home_featured_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Biens sélectionnés (bloc: titre puis prix/texte)</h3>
                    <textarea name="featured_properties_text"><?php
                        $propertyRows = [];
                        foreach ((array)($data['featured_properties'] ?? []) as $property) {
                            $propertyRows[] = trim((string)($property['title'] ?? '')) . "\n" . trim((string)($property['price'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($propertyRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA carte bien - Label</label>
                            <input type="text" name="home_featured_item_cta_label" value="<?= htmlspecialchars((string)($data['home_featured_item_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA carte bien - URL</label>
                            <input type="text" name="home_featured_item_cta_url" value="<?= htmlspecialchars((string)($data['home_featured_item_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA section biens - Label</label>
                            <input type="text" name="home_featured_section_cta_label" value="<?= htmlspecialchars((string)($data['home_featured_section_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA section biens - URL</label>
                            <input type="text" name="home_featured_section_cta_url" value="<?= htmlspecialchars((string)($data['home_featured_section_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Marché local</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_market_section_label" value="<?= htmlspecialchars((string)($data['home_market_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_market_section_title" value="<?= htmlspecialchars((string)($data['home_market_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_market_section_subtitle"><?= htmlspecialchars((string)($data['home_market_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Marché local (bloc: titre puis texte)</h3>
                    <textarea name="home_market_cards_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_market_cards'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA marché - Label</label>
                            <input type="text" name="home_market_cta_label" value="<?= htmlspecialchars((string)($data['home_market_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA marché - URL</label>
                            <input type="text" name="home_market_cta_url" value="<?= htmlspecialchars((string)($data['home_market_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête Guide vendre</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_sell_section_label" value="<?= htmlspecialchars((string)($data['home_sell_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_sell_section_title" value="<?= htmlspecialchars((string)($data['home_sell_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_sell_section_subtitle"><?= htmlspecialchars((string)($data['home_sell_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>Guide vendre (bloc: titre puis texte)</h3>
                    <textarea name="home_sell_guide_text"><?= htmlspecialchars($toTitleTextEditor((array)($data['home_sell_guide'] ?? [])), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="row">
                        <div>
                            <label>CTA guide - Label</label>
                            <input type="text" name="home_sell_cta_label" value="<?= htmlspecialchars((string)($data['home_sell_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA guide - URL</label>
                            <input type="text" name="home_sell_cta_url" value="<?= htmlspecialchars((string)($data['home_sell_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <h3>En-tête FAQ</h3>
                    <div>
                        <label>Label section</label>
                        <input type="text" name="home_faq_section_label" value="<?= htmlspecialchars((string)($data['home_faq_section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Titre section</label>
                        <input type="text" name="home_faq_section_title" value="<?= htmlspecialchars((string)($data['home_faq_section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Sous-titre section</label>
                        <textarea name="home_faq_section_subtitle"><?= htmlspecialchars((string)($data['home_faq_section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <h3>FAQ (bloc: question puis réponse)</h3>
                    <textarea name="home_faq_text"><?php
                        $faqRows = [];
                        foreach ((array)($data['home_faq'] ?? []) as $faq) {
                            $faqRows[] = trim((string)($faq['question'] ?? '')) . "\n" . trim((string)($faq['answer'] ?? ''));
                        }
                        echo htmlspecialchars(implode("\n\n", array_filter($faqRows)), ENT_QUOTES, 'UTF-8');
                    ?></textarea>
                    <h3>CTA final</h3>
                    <div>
                        <label>Titre CTA final</label>
                        <input type="text" name="home_final_cta_title" value="<?= htmlspecialchars((string)($data['home_final_cta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label>Texte CTA final</label>
                        <textarea name="home_final_cta_text"><?= htmlspecialchars((string)($data['home_final_cta_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 1 - Label</label>
                            <input type="text" name="home_final_primary_cta_label" value="<?= htmlspecialchars((string)($data['home_final_primary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 1 - URL</label>
                            <input type="text" name="home_final_primary_cta_url" value="<?= htmlspecialchars((string)($data['home_final_primary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 2 - Label</label>
                            <input type="text" name="home_final_secondary_cta_label" value="<?= htmlspecialchars((string)($data['home_final_secondary_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 2 - URL</label>
                            <input type="text" name="home_final_secondary_cta_url" value="<?= htmlspecialchars((string)($data['home_final_secondary_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 3 - Label</label>
                            <input type="text" name="home_final_third_cta_label" value="<?= htmlspecialchars((string)($data['home_final_third_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 3 - URL</label>
                            <input type="text" name="home_final_third_cta_url" value="<?= htmlspecialchars((string)($data['home_final_third_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 4 - Label</label>
                            <input type="text" name="home_final_fourth_cta_label" value="<?= htmlspecialchars((string)($data['home_final_fourth_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 4 - URL</label>
                            <input type="text" name="home_final_fourth_cta_url" value="<?= htmlspecialchars((string)($data['home_final_fourth_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>CTA final 5 - Label</label>
                            <input type="text" name="home_final_fifth_cta_label" value="<?= htmlspecialchars((string)($data['home_final_fifth_cta_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label>CTA final 5 - URL</label>
                            <input type="text" name="home_final_fifth_cta_url" value="<?= htmlspecialchars((string)($data['home_final_fifth_cta_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="cms-actions">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="submit" name="empty_template" value="1" class="btn btn-light" onclick="return confirm('Réinitialiser toute cette page aux textes par défaut ?')">Réinitialiser (modèle vide)</button>
                        <a class="btn btn-light" href="<?= htmlspecialchars($cmsEditPublicViewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Voir la page</a>
                        <a class="btn btn-light" href="<?= htmlspecialchars($adminCmsList, ENT_QUOTES, 'UTF-8') ?>">Retour liste</a>
                    </div>
                    </section>
                    </div>
                    <div class="cms-editor-rail">
                    <aside class="cms-seo-panel" id="cms-seo-panel" aria-labelledby="cms-seo-heading">
                            <div class="cms-seo-panel__head">
                                <h3 id="cms-seo-heading">Assistant SEO</h3>
                                <span class="cms-seo-panel__badge">Automatique</span>
                            </div>
                            <?php
                            $sh = $seoHome ?? ['score' => 0, 'items' => [], 'mt_len' => 0, 'md_len' => 0, 'hero_words' => 0];
                            $sc = (int) ($sh['score'] ?? 0);
                            $badgeCls = $sc >= 80 ? 'is-good' : ($sc >= 60 ? 'is-warn' : 'is-bad');
                            $lbl = $sc >= 80 ? 'Excellent' : ($sc >= 60 ? 'Bon' : 'À optimiser');
                            ?>
                            <div class="cms-seo-score">
                                <div class="cms-seo-score-badge <?= $badgeCls ?>"><?= $sc ?></div>
                                <div class="cms-seo-score-text">
                                    <span class="cms-seo-score-label">Score</span>
                                    <strong><?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                            </div>
                            <div class="cms-seo-metrics">
                                <div class="cms-seo-metric"><span>Mots (hero)</span><strong><?= (int) ($sh['hero_words'] ?? 0) ?></strong></div>
                                <div class="cms-seo-metric"><span>Meta title</span><strong><?= (int) ($sh['mt_len'] ?? 0) ?></strong></div>
                                <div class="cms-seo-metric"><span>Meta description</span><strong><?= (int) ($sh['md_len'] ?? 0) ?></strong></div>
                                <div class="cms-seo-metric"><span>Visites page</span><strong><?= (int)$homeViewsCount ?></strong></div>
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-focus-keyword">Mot-clé principal</label>
                                <input type="text" id="home-seo-focus-keyword" name="home_seo_focus_keyword" maxlength="70" value="<?= htmlspecialchars((string)($data['home_seo_focus_keyword'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex. immobilier {{city}}">
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-secondary-keywords">Mots-clés secondaires</label>
                                <input type="text" id="home-seo-secondary-keywords" name="home_seo_secondary_keywords" maxlength="350" value="<?= htmlspecialchars((string)($data['home_seo_secondary_keywords'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="estimation, achat, vente">
                                <span class="cms-field-hint">Séparés par des virgules</span>
                            </div>
                            <div class="cms-seo-field">
                                <label for="home-seo-semantic-terms">Champ sémantique</label>
                                <input type="text" id="home-seo-semantic-terms" name="home_seo_semantic_terms" maxlength="350" value="<?= htmlspecialchars((string)($data['home_seo_semantic_terms'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="notaire, mandat, compromis">
                                <span class="cms-field-hint">Termes à faire apparaître dans le corps</span>
                            </div>
                            <ul class="cms-seo-checklist" role="list">
                                <?php foreach (($sh['items'] ?? []) as $it): ?>
                                    <li class="<?= !empty($it['ok']) ? 'ok' : 'bad' ?>"><?= !empty($it['ok']) ? 'OK — ' : 'À corriger — ' ?><?= htmlspecialchars((string) ($it['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="cms-seo-help">L’analyse et les scores se mettent à jour quand vous enregistrez la page.</p>
                        </aside>
                    </div>
                    </div>
                </form>
                <datalist id="cms-link-suggestions">
                    <option value="/"></option>
                    <option value="/contact"></option>
                    <option value="/biens"></option>
                    <option value="/estimation-gratuite"></option>
                    <option value="/avis-de-valeur"></option>
                    <option value="/avis-clients"></option>
                    <option value="/a-propos"></option>
                    <option value="/secteurs"></option>
                    <option value="/guide-offert"></option>
                    <option value="/prendre-rendez-vous"></option>
                </datalist>
            </div>
            <?php elseif ($slug === 'services'): ?>
            <?php
            $pg = $page;
            $svc = $data['services_content'] ?? cms_services_default_content();
            $ss = $seoServicesPanel ?? ['score' => 0, 'items' => [], 'serp' => ['title' => '', 'url' => '/services', 'desc' => '']];
            $scS = (int) ($ss['score'] ?? 0);
            $scSCls = $scS >= 80 ? 'is-good' : ($scS >= 60 ? 'is-warn' : 'is-bad');
            $scSLbl = $scS >= 80 ? 'Très bon' : ($scS >= 60 ? 'Correct' : 'À renforcer');
            $serp = $ss['serp'] ?? [];
            $viewServices = is_array($pg) ? cms_page_public_url($pg) : (function_exists('url') ? url('/services') : '/services');
            ?>
            <div class="cms-card">
                <div class="cms-edit-card-head">
                    <h2>Édition — Page Services <span style="font-weight:500;font-size:14px;color:#64748b">(URL publique : /services)</span></h2>
                    <a class="cms-edit-view-link" href="<?= htmlspecialchars($viewServices, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Ouvrir /services sur le site">
                        <svg class="cms-edit-view-ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <span>Voir la page</span>
                    </a>
                </div>
                <?php if (!empty($cmsServicesLegacyDuplicate)): ?>
                <div class="error" role="status" style="margin:12px 24px 0">Une ancienne entrée en double (« services-services ») existe encore dans les données. Modifiez toujours le contenu ici — c’est la seule page <strong>/services</strong> valide. Demandez au support technique la suppression du doublon si besoin.</div>
                <?php endif; ?>
                <?php if ($notice !== ''): ?><div class="notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form id="form-cms-services-edit" method="post" class="cms-form" action="<?= htmlspecialchars(function_exists('admin_url') ? admin_url(['module' => 'cms', 'action' => 'edit', 'slug' => 'services']) : '/admin/?module=cms&action=edit&slug=services', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="cms_services_save" value="1">
                    <?= function_exists('csrfField') ? csrfField() : '' ?>
                    <div class="cms-editor-layout">
                        <div class="cms-editor-main">
                            <section class="cms-section">
                                <h3>Paramètres &amp; hero</h3>
                                <div class="cms-meta-grid">
                                    <div>
                                        <label>Titre interne (admin)</label>
                                        <input type="text" name="title" value="<?= htmlspecialchars((string) ($pg['title'] ?? 'Page Services'), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div>
                                        <label>Statut</label>
                                        <select name="status">
                                            <?php $stS = (string) ($pg['status'] ?? 'draft'); ?>
                                            <option value="draft"<?= $stS === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                                            <option value="published"<?= $stS === 'published' ? ' selected' : '' ?>>Publié</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label>H1 public</label>
                                    <input type="text" id="cms-svc-hero-h1" name="hero_h1" value="<?= htmlspecialchars((string) ($svc['hero']['h1'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Mes services">
                                </div>
                                <div>
                                    <label>Fil d’Ariane (du 2ᵉ segment)</label>
                                    <input type="text" name="hero_eyebrow" value="<?= htmlspecialchars((string) ($svc['hero']['eyebrow'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Services">
                                </div>
                                <div>
                                    <label>Sous-titre sous le H1</label>
                                    <textarea id="cms-svc-hero-sub" name="hero_subtitle" rows="3" placeholder="Laisser vide pour le texte par défaut (ville + environs)"><?= htmlspecialchars((string) ($svc['hero']['subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </section>
                            <section class="cms-section">
                                <h3>Introduction (optionnel)</h3>
                                <div>
                                    <label>Titre court</label>
                                    <input type="text" id="cms-svc-intro-title" name="intro_title" value="<?= htmlspecialchars((string) ($svc['intro']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Texte</label>
                                    <textarea id="cms-svc-intro-text" name="intro_text" rows="4"><?= htmlspecialchars((string) ($svc['intro']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </section>
                            <section class="cms-section">
                                <h3>Quatre offres (blocs services)</h3>
                                <p style="margin:0 0 12px;font-size:13px;color:#64748b">Même rendu qu’en front (alternance gauche/droite). Une ligne = un bénéfice.</p>
                                <?php
                                $sBlocks = is_array($svc['services_blocks'] ?? null) ? $svc['services_blocks'] : [];
                                for ($bi = 0; $bi < 4; $bi++):
                                    $blk = is_array($sBlocks[$bi] ?? null) ? $sBlocks[$bi] : [];
                                    $benRaw = isset($blk['benefits']) && is_array($blk['benefits'])
                                        ? implode("\n", array_map('strval', $blk['benefits']))
                                        : (string) ($blk['benefits'] ?? '');
                                    ?>
                                <div style="margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #f4f4f5">
                                    <h4 style="margin:0 0 12px;font-size:13px;color:#52525b">Offre <?= $bi + 1 ?></h4>
                                    <div class="row">
                                        <div>
                                            <label>Icône (emoji)</label>
                                            <input type="text" name="block_icon_<?= $bi ?>" value="<?= htmlspecialchars((string) ($blk['icon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="🏠">
                                        </div>
                                        <div>
                                            <label>Titre (H2)</label>
                                            <input type="text" name="block_title_<?= $bi ?>" value="<?= htmlspecialchars((string) ($blk['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div>
                                        <label>Description</label>
                                        <textarea name="block_desc_<?= $bi ?>" rows="3"><?= htmlspecialchars((string) ($blk['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                    <div>
                                        <label>Bénéfices (1 ligne = 1 puce)</label>
                                        <textarea name="block_benefits_<?= $bi ?>" rows="5"><?= htmlspecialchars($benRaw, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div>
                                            <label>Libellé bouton</label>
                                            <input type="text" name="block_btn_label_<?= $bi ?>" value="<?= htmlspecialchars((string) ($blk['button_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                        <div>
                                            <label>URL bouton</label>
                                            <input type="text" name="block_btn_url_<?= $bi ?>" value="<?= htmlspecialchars((string) ($blk['button_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/contact">
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </section>
                            <section class="cms-section">
                                <h3>Meta &amp; image</h3>
                                <div>
                                    <label>Meta title</label>
                                    <input type="text" id="cms-svc-meta-title" name="meta_title" value="<?= htmlspecialchars((string) ($pg['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Meta description</label>
                                    <textarea id="cms-svc-meta-desc" name="meta_description" rows="4"><?= htmlspecialchars((string) ($pg['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div>
                                    <label>Image Open Graph (URL)</label>
                                    <input type="text" id="cms-svc-og-url" name="og_image_url" value="<?= htmlspecialchars((string) ($pg['og_image_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="https://… ou /assets/…">
                                </div>
                                <div>
                                    <label>Texte alternatif (image / partage)</label>
                                    <input type="text" id="cms-svc-og-alt" name="og_image_alt" value="<?= htmlspecialchars((string) ($svc['seo']['og_image_alt'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Description courte pour l’accessibilité et le score SEO">
                                </div>
                            </section>
                            <section class="cms-section">
                                <h3>Section honoraires (3 cartes)</h3>
                                <p style="margin:0 0 12px;font-size:13px;color:#64748b">Cartes alignées sur le design actuel (grille 3 colonnes).</p>
                                <?php
                                $rows = is_array($svc['pricing']['rows'] ?? null) ? $svc['pricing']['rows'] : [];
                                for ($ri = 0; $ri < 3; $ri++):
                                    $rw = is_array($rows[$ri] ?? null) ? $rows[$ri] : [];
                                    ?>
                                <div class="row" style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #f4f4f5">
                                    <div>
                                        <label>Nom <?= $ri + 1 ?></label>
                                        <input type="text" name="price_name_<?= $ri ?>" value="<?= htmlspecialchars((string) ($rw['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div>
                                        <label>Tarif affiché <?= $ri + 1 ?></label>
                                        <input type="text" name="price_val_<?= $ri ?>" value="<?= htmlspecialchars((string) ($rw['price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div>
                                    <label>Description <?= $ri + 1 ?></label>
                                    <textarea name="price_desc_<?= $ri ?>" rows="2"><?= htmlspecialchars((string) ($rw['desc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <?php endfor; ?>
                                <div>
                                    <label>Label de section (petit)</label>
                                    <input type="text" name="pricing_section_label" value="<?= htmlspecialchars((string) ($svc['pricing']['section_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Transparence">
                                </div>
                                <div>
                                    <label>Titre de section (H2)</label>
                                    <input type="text" name="pricing_section_title" value="<?= htmlspecialchars((string) ($svc['pricing']['section_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Sous-titre de section</label>
                                    <textarea name="pricing_section_subtitle" rows="2"><?= htmlspecialchars((string) ($svc['pricing']['section_subtitle'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </section>
                            <section class="cms-section">
                                <h3>CTA final (bandeau)</h3>
                                <div>
                                    <label>Titre</label>
                                    <input type="text" name="cta_title" value="<?= htmlspecialchars((string) ($svc['cta']['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Texte</label>
                                    <textarea name="cta_text" rows="3"><?= htmlspecialchars((string) ($svc['cta']['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div class="row">
                                    <div>
                                        <label>Libellé du bouton</label>
                                        <input type="text" name="cta_button_label" value="<?= htmlspecialchars((string) ($svc['cta']['button_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div>
                                        <label>URL du bouton</label>
                                        <input type="text" name="cta_button_url" value="<?= htmlspecialchars((string) ($svc['cta']['button_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="/contact">
                                    </div>
                                </div>
                            </section>
                            <div class="cms-actions">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                <a class="btn btn-light" href="<?= htmlspecialchars($viewServices, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Voir la page</a>
                                <a class="btn btn-light" href="<?= htmlspecialchars($adminCmsList, ENT_QUOTES, 'UTF-8') ?>">Retour liste</a>
                            </div>
                        </div>
                        <div class="cms-editor-rail">
                            <div class="cms-rail-stack">
                                <aside class="cms-seo-panel" id="cms-seo-panel" aria-labelledby="cms-seo-svc">
                                    <div class="cms-seo-panel__head">
                                        <h3 id="cms-seo-svc">Score SEO (0–100)</h3>
                                        <span class="cms-seo-panel__badge">Automatique</span>
                                    </div>
                                    <div class="cms-seo-score">
                                        <div class="cms-seo-score-badge <?= $scSCls ?>"><?= $scS ?></div>
                                        <div class="cms-seo-score-text">
                                            <span class="cms-seo-score-label">Score</span>
                                            <strong><?= htmlspecialchars($scSLbl, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                    </div>
                                    <div class="cms-seo-field">
                                        <label for="svc-focus-kw">Mot-clé principal (focus)</label>
                                        <input type="text" id="svc-focus-kw" name="focus_keyword" maxlength="80" value="<?= htmlspecialchars((string) ($svc['seo']['focus_keyword'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="ex. agence immobilière Aix-en-Provence">
                                    </div>
                                    <ul class="cms-seo-checklist" role="list">
                                        <?php foreach (($ss['items'] ?? []) as $it): ?>
                                            <li class="<?= !empty($it['ok']) ? 'ok' : 'bad' ?>"><?= !empty($it['ok']) ? 'OK — ' : 'À améliorer — ' ?><?= htmlspecialchars((string) ($it['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="cms-seo-help">Score sur 100 (dix critères). Aperçu proche du résultat Google (titre, lien, extrait)&nbsp;:</p>
                                    <div class="cms-share-preview" style="margin-top:8px">
                                        <div class="t" style="color:#1a0dab"><?= htmlspecialchars((string) ($serp['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div style="font-size:12px;color:#006621;margin:2px 0"><?= htmlspecialchars((string) ($serp['url'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="d"><?= htmlspecialchars((string) ($serp['desc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                    <p class="cms-seo-help">Enregistrez la page pour mettre à jour le score et l’aperçu.</p>
                                </aside>
                                <?php
                                $assist = $seoServicesAssist ?? null;
                                $asRows = is_array($assist) ? ($assist['rows'] ?? []) : [];
                                $asEd = is_array($assist) ? ($assist['editorial'] ?? []) : [];
                                $asWordCount = is_array($assist) ? (int) ($assist['word_count'] ?? 0) : 0;
                                $asOgNote = is_array($assist) ? ($assist['og_note'] ?? null) : null;
                                $asFocus = is_array($assist) ? trim((string) ($assist['focus'] ?? '')) : '';
                                $edSug = !empty($asEd['suggest']);
                                $assistJson = json_encode(
                                    [
                                        'fieldIds'  => [
                                            'meta_title'       => 'cms-svc-meta-title',
                                            'meta_description' => 'cms-svc-meta-desc',
                                            'hero_h1'          => 'cms-svc-hero-h1',
                                            'hero_subtitle'    => 'cms-svc-hero-sub',
                                            'og_image_alt'     => 'cms-svc-og-alt',
                                        ],
                                        'rows'         => $asRows,
                                        'ogUrlElement' => 'cms-svc-og-url',
                                        'editorial'    => $edSug
                                            ? ['title' => (string) ($asEd['title'] ?? ''), 'text' => (string) ($asEd['text'] ?? '')]
                                            : null,
                                    ],
                                    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
                                );
                                $assistJson = $assistJson === false ? '{}' : $assistJson;
                                $assistJsonB64 = base64_encode((string) $assistJson);
                                ?>
                                <div class="cms-widget cms-seo-assist" id="cms-seo-assist" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;box-shadow:none">
                                    <h4 style="margin:0 0 8px;color:#334155;font-size:15px">Correction SEO assistée</h4>
                                    <p style="margin:0 0 8px;font-size:12px;color:#64748b">
                                        Les boutons ci-dessous pré-remplissent les champs du formulaire uniquement ; rien n’est publié tant que vous n’avez pas cliqué sur <strong>Enregistrer</strong>.
                                    </p>
                                    <?php if ($asFocus === ''): ?>
                                    <p style="margin:0 0 10px;font-size:12px;color:#a16207">Renseignez d’abord le <strong>mot-clé principal</strong> (focus) pour activer les propositions ciblées.</p>
                                    <?php endif; ?>
                                    <div class="cms-seo-assist__actions" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                                        <button type="button" class="btn btn-primary" id="cms-seo-btn-auto" style="font-size:13px;padding:8px 12px"<?= $asFocus === '' ? ' disabled' : '' ?>>Corriger automatiquement le SEO</button>
                                        <button type="button" class="btn btn-light" id="cms-seo-btn-toggle" style="font-size:13px;padding:8px 12px"<?= $asFocus === '' && count($asRows) === 0 && !$asOgNote && empty($asEd['suggest']) ? ' disabled' : '' ?>>Voir les corrections proposées</button>
                                    </div>
                                    <div id="cms-seo-proposals-body" class="cms-seo-proposals-body" style="display:none">
                                        <p style="margin:0 0 6px;font-size:11px;color:#64748b">Nombre de mots sur cette page : <strong><?= (int) $asWordCount ?></strong><?= $asWordCount < 800 ? ' — en dessous du repère 800 mots (bon pour le référencement)' : '' ?></p>
                                        <?php if ($asOgNote): ?>
                                        <div style="font-size:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:10px">
                                            <strong>Image Open Graph</strong> — recommandation : <?= htmlspecialchars($asOgNote, ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (count($asRows) > 0): ?>
                                        <div style="display:grid;gap:10px;max-height:32rem;overflow:auto">
                                            <?php foreach ($asRows as $ri => $r): ?>
                                            <div class="cms-seo-row" style="border:1px solid #e4e4e7;border-radius:10px;padding:10px 12px;background:#fafafa">
                                                <div style="font-size:12px;font-weight:650;margin-bottom:6px"><?= htmlspecialchars((string) ($r['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Valeur actuelle</div>
                                                <div style="font-size:12px;white-space:pre-wrap;word-break:break-word;margin-bottom:6px;max-height:4rem;overflow:auto"><?= $r['current'] === '' || $r['current'] === null ? '—' : htmlspecialchars((string) $r['current'], ENT_QUOTES, 'UTF-8') ?></div>
                                                <div style="font-size:11px;color:#14532d;margin-bottom:4px">Proposition</div>
                                                <div style="font-size:12px;white-space:pre-wrap;word-break:break-word;margin-bottom:8px;max-height:5rem;overflow:auto"><?= htmlspecialchars((string) ($r['proposed'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                <button type="button" class="btn btn-light cms-seo-apply-one" data-field="<?= htmlspecialchars((string) ($r['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-row-idx="<?= (int) $ri ?>" style="font-size:12px">Appliquer</button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php elseif ($asFocus !== ''): ?>
                                        <p style="font-size:12px;color:#15803d">Rien à corriger de façon prioritaire pour ce mot-clé : les champs importants sont déjà remplis ou aucune suggestion supplémentaire n’est nécessaire.</p>
                                        <?php endif; ?>
                                        <?php
                                        if ($edSug) :
                                            $eTi = (string) ($asEd['title'] ?? '');
                                            $eTx = (string) ($asEd['text'] ?? '');
                                            ?>
                                        <div style="margin-top:12px;border-top:1px solid #e4e4e7;padding-top:10px">
                                            <div style="font-size:12px;font-weight:700;margin-bottom:4px">Texte suggéré pour enrichir la page (800+ mots — à relire avant publication)</div>
                                            <p style="font-size:11px;color:#64748b;margin:0 0 6px">S’insère dans <strong>Introduction (optionnel)</strong>. Aucun ajout sans clic.</p>
                                            <p style="font-size:12px;font-weight:600;margin:0 0 4px"><?= htmlspecialchars($eTi, ENT_QUOTES, 'UTF-8') ?></p>
                                            <p style="font-size:12px;white-space:pre-wrap;max-height:8rem;overflow:auto;margin:0 0 8px"><?= htmlspecialchars($eTx, ENT_QUOTES, 'UTF-8') ?></p>
                                            <button type="button" class="btn btn-light" id="cms-seo-btn-editorial" style="font-size:12px">Ajouter ce bloc (intro)</button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <textarea id="cms-seo-assist-data" hidden aria-hidden="true" readonly class="visually-hidden"><?= htmlspecialchars($assistJsonB64, ENT_QUOTES, 'UTF-8') ?></textarea>
                                <script>
                                (function () {
                                    var form = document.getElementById('form-cms-services-edit');
                                    var elData = document.getElementById('cms-seo-assist-data');
                                    if (!form || !elData) return;
                                    var data;
                                    try {
                                        var raw = (elData.value || '').trim();
                                        var json = typeof atob === 'function' ? atob(raw) : raw;
                                        data = JSON.parse(json);
                                    } catch (e) { return; }
                                    if (!data || typeof data !== 'object') return;
                                    function fid(name) { return (data.fieldIds && data.fieldIds[name]) || ''; }
                                    function eln(name) { var id = fid(name); return id ? document.getElementById(id) : null; }
                                    function applyValue(el, val) {
                                        if (!el) return;
                                        if (el.dataset.seoBackup === undefined) el.dataset.seoBackup = el.value;
                                        el.value = val;
                                        el.dispatchEvent(new Event('input', { bubbles: true }));
                                        el.dispatchEvent(new Event('change', { bubbles: true }));
                                        el.focus({ preventScroll: true });
                                        try { el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); } catch (e2) { el.scrollIntoView(true); }
                                        el.classList.add('cms-seo-field-flash');
                                        setTimeout(function () { el.classList.remove('cms-seo-field-flash'); }, 1200);
                                    }
                                    var body = document.getElementById('cms-seo-proposals-body');
                                    var btnT = document.getElementById('cms-seo-btn-toggle');
                                    if (btnT && body) {
                                        btnT.addEventListener('click', function (e) { e.preventDefault(); body.style.display = body.style.display === 'none' ? 'block' : 'none'; });
                                    }
                                    var btnA = document.getElementById('cms-seo-btn-auto');
                                    if (btnA) {
                                        btnA.addEventListener('click', function (e) {
                                            e.preventDefault();
                                            (data.rows || []).forEach(function (row) {
                                                if (!row || !row.name || !row.empty_ok) return;
                                                if (row.name === 'og_image_alt') {
                                                    var ogU = document.getElementById(data.ogUrlElement || 'cms-svc-og-url');
                                                    if (!ogU || !String(ogU.value || '').trim()) return;
                                                }
                                                var el = eln(row.name);
                                                if (!el) return;
                                                if (String(el.value || '').replace(/\s/g, '') !== '') return;
                                                if (row.proposed === undefined) return;
                                                applyValue(el, String(row.proposed));
                                            });
                                        });
                                    }
                                    form.addEventListener('click', function (e) {
                                        var b = e.target && e.target.closest && e.target.closest('.cms-seo-apply-one');
                                        if (!b) return;
                                        e.preventDefault();
                                        e.stopPropagation();
                                        var name = b.getAttribute('data-field');
                                        var idx = parseInt(b.getAttribute('data-row-idx') || '-1', 10);
                                        var row = (data.rows || [])[idx];
                                        if (!row || !name) return;
                                        if (row.proposed === undefined) return;
                                        var el = eln(name);
                                        if (!el) return;
                                        applyValue(el, String(row.proposed));
                                    });
                                    var btnEd = document.getElementById('cms-seo-btn-editorial');
                                    if (btnEd && data.editorial) {
                                        btnEd.addEventListener('click', function (e) {
                                            e.preventDefault();
                                            var t = document.getElementById('cms-svc-intro-title');
                                            var x = document.getElementById('cms-svc-intro-text');
                                            if (!t || !x) return;
                                            var title = data.editorial.title || '';
                                            var text = data.editorial.text || '';
                                            if (String(x.value || '').trim() && !window.confirm('Remplacer le texte d’introduction existant ?')) return;
                                            if (t.dataset.seoBackup === undefined) t.dataset.seoBackup = t.value;
                                            if (x.dataset.seoBackup === undefined) x.dataset.seoBackup = x.value;
                                            t.value = title; x.value = text;
                                            t.dispatchEvent(new Event('input', { bubbles: true }));
                                            x.dispatchEvent(new Event('input', { bubbles: true }));
                                        });
                                    }
                                })();
                                </script>
                                <style>
                                .cms-seo-field-flash { outline: 2px solid #fbbf24 !important; transition: outline .2s; }
                                #form-cms-services-edit .cms-seo-assist .btn-primary { background: #334155; color: #f8fafc; border: 0; }
                                #form-cms-services-edit .cms-seo-assist .btn-primary:hover { background: #1e293b; }
                                #form-cms-services-edit .cms-seo-assist .btn-light { background: #fff; color: #334155; border: 1px solid #e2e8f0; }
                                </style>
                                <div class="cms-widget">
                                    <h4>Page &amp; adresse web</h4>
                                    <p style="margin:0;font-size:12px;color:#64748b">Mise en page du site&nbsp;: <code>pages/services/services</code></p>
                                    <p style="margin:8px 0 0;font-size:12px;color:#64748b">Référence enregistrée&nbsp;: <strong><code><?= htmlspecialchars((string) ($pg['slug'] ?? 'services'), ENT_QUOTES, 'UTF-8') ?></code></strong> (c’est la page qui alimente <strong>/services</strong>).</p>
                                    <p style="margin:8px 0 0;font-size:11px;color:#94a3b8">Si une entrée « services-services » apparaît encore ailleurs, c’est un doublon technique masqué dans la liste — signaler au support pour nettoyage.</p>
                                </div>
                                <div class="cms-widget">
                                    <h4>Publication</h4>
                                    <label for="cms-svc-level" style="text-transform:none;font-size:13px;color:#334155">Niveau de page</label>
                                    <select name="page_level" id="cms-svc-level" style="margin-top:6px">
                                        <?php $plS = (int) ($pg['page_level'] ?? 1); ?>
                                        <option value="1"<?= $plS === 1 ? ' selected' : '' ?>>1 — Public</option>
                                        <option value="2"<?= $plS === 2 ? ' selected' : '' ?>>2 — Utilitaire</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php else: /* édition page générique */ ?>
            <?php
            $pg = $page;
            $shareTitle = trim((string) ($pg['meta_title'] ?? '')) !== '' ? trim((string) $pg['meta_title']) : trim((string) ($pg['title'] ?? ''));
            $shareDesc = trim((string) ($pg['meta_description'] ?? ''));
            $ogU = trim((string) ($pg['og_image_url'] ?? ''));
            $scG = $seoCheckGeneric;
            $scVal = (int) ($scG['score'] ?? 0);
            $scCls = $scVal >= 75 ? 'is-good' : ($scVal >= 50 ? 'is-warn' : 'is-bad');
            $scLbl = $scVal >= 75 ? 'Bon équilibre' : ($scVal >= 50 ? 'À peaufiner' : 'À compléter');
            $cmsGenViewUrl = is_array($pg) ? cms_page_public_url($pg) : '#';
            ?>
            <div class="cms-card">
                <div class="cms-edit-card-head">
                    <h2>Édition — <?= htmlspecialchars((string) ($pg['title'] ?? $slug), ENT_QUOTES, 'UTF-8') ?></h2>
                    <a class="cms-edit-view-link" href="<?= htmlspecialchars($cmsGenViewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" title="Ouvrir la page sur le site public">
                        <svg class="cms-edit-view-ico" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <span>Voir la page</span>
                    </a>
                </div>
                <?php if ($notice !== ''): ?><div class="notice"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form method="post" class="cms-form" action="<?= htmlspecialchars(function_exists('admin_url') ? admin_url(['module' => 'cms', 'action' => 'edit', 'slug' => $slug]) : ('/admin/?module=cms&action=edit&slug=' . rawurlencode($slug)), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="cms_generic_save" value="1">
                    <?= function_exists('csrfField') ? csrfField() : '' ?>
                    <div class="cms-editor-layout">
                        <div class="cms-editor-main">
                            <section class="cms-section">
                                <h3>Contenu éditable</h3>
                                <div>
                                    <label>Titre interne (admin)</label>
                                    <input type="text" name="title" value="<?= htmlspecialchars((string) ($pg['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Meta title</label>
                                    <input type="text" name="meta_title" value="<?= htmlspecialchars((string) ($pg['meta_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div>
                                    <label>Meta description</label>
                                    <textarea name="meta_description" rows="4"><?= htmlspecialchars((string) ($pg['meta_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div>
                                    <label>Image Open Graph (URL absolue ou chemin /…)</label>
                                    <input type="text" name="og_image_url" value="<?= htmlspecialchars($ogU, ENT_QUOTES, 'UTF-8') ?>" placeholder="https://… ou /assets/…">
                                </div>
                                <hr>
                                <p style="margin:0 0 10px;font-size:13px;color:#64748b">Ces zones correspondent au contenu prévu dans la mise en page de la page. Elles s’affichent sur le site dès que les textes sont enregistrés ici.</p>
                                <?php foreach ($sectionsGeneric as $field => $val): ?>
                                    <div>
                                        <label><code><?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?></code></label>
                                        <textarea name="section_<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" rows="<?= mb_strlen((string) $val) > 120 ? 5 : 2 ?>"><?= htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($sectionsGeneric === []): ?>
                                    <p class="error">Aucun bloc de texte n’est disponible pour cette page. Contactez le support technique si le problème persiste.</p>
                                <?php endif; ?>
                                <div class="cms-actions">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    <a class="btn btn-light" href="<?= htmlspecialchars($cmsGenViewUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Voir la page</a>
                                    <a class="btn btn-light" href="<?= htmlspecialchars($adminCmsList, ENT_QUOTES, 'UTF-8') ?>">Retour liste</a>
                                </div>
                            </section>
                        </div>
                        <div class="cms-editor-rail">
                            <div class="cms-rail-stack">
                                <aside class="cms-seo-panel" id="cms-seo-panel" aria-labelledby="cms-seo-gen">
                                    <div class="cms-seo-panel__head">
                                        <h3 id="cms-seo-gen">Aide SEO</h3>
                                        <span class="cms-seo-panel__badge">Automatique</span>
                                    </div>
                                    <div class="cms-seo-score">
                                        <div class="cms-seo-score-badge <?= $scCls ?>"><?= $scVal ?></div>
                                        <div class="cms-seo-score-text">
                                            <span class="cms-seo-score-label">Score</span>
                                            <strong><?= htmlspecialchars($scLbl, ENT_QUOTES, 'UTF-8') ?></strong>
                                        </div>
                                    </div>
                                    <ul class="cms-seo-checklist" role="list">
                                        <?php foreach (($scG['items'] ?? []) as $it): ?>
                                            <li class="<?= !empty($it['ok']) ? 'ok' : 'bad' ?>"><?= !empty($it['ok']) ? 'OK — ' : 'À corriger — ' ?><?= htmlspecialchars((string) ($it['text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="cms-seo-help">Suggestions basées sur le titre, la description et les textes des blocs ci-contre.</p>
                                </aside>
                                <div class="cms-widget">
                                    <h4>Mise en page &amp; URL</h4>
                                    <p style="margin:0;font-size:12px;color:#64748b">Fichier gabarit&nbsp;: <code><?= htmlspecialchars((string) ($pg['template'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></p>
                                    <p style="margin:8px 0 0;font-size:12px;color:#64748b">Fin d’adresse de la page&nbsp;: <strong><?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?></strong></p>
                                </div>
                                <div class="cms-widget">
                                    <h4>Publication</h4>
                                    <label for="cms-gen-status" style="text-transform:none;font-size:13px;color:#334155">Statut</label>
                                    <select name="status" id="cms-gen-status" style="margin-top:6px">
                                        <?php $stGen = (string) ($pg['status'] ?? 'draft'); ?>
                                        <option value="draft"<?= $stGen === 'draft' ? ' selected' : '' ?>>Brouillon</option>
                                        <option value="published"<?= $stGen === 'published' ? ' selected' : '' ?>>Publié</option>
                                    </select>
                                    <label for="cms-gen-level" style="text-transform:none;font-size:13px;color:#334155;margin-top:12px;display:block">Niveau</label>
                                    <select name="page_level" id="cms-gen-level" style="margin-top:6px">
                                        <?php $plGen = (int) ($pg['page_level'] ?? 1); ?>
                                        <option value="1"<?= $plGen === 1 ? ' selected' : '' ?>>1 — Public</option>
                                        <option value="2"<?= $plGen === 2 ? ' selected' : '' ?>>2 — Utilitaire / merci / erreur</option>
                                    </select>
                                </div>
                                <div class="cms-widget">
                                    <h4>Aperçu partage (Open Graph)</h4>
                                    <div class="cms-share-preview">
                                        <div class="t"><?= htmlspecialchars($shareTitle !== '' ? $shareTitle : '— Titre —', ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="d"><?= htmlspecialchars($shareDesc !== '' ? $shareDesc : '— Description —', ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php if ($ogU !== ''): ?>
                                            <img src="<?= htmlspecialchars($ogU, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                        <?php endif; ?>
                                    </div>
                                    <p style="margin:8px 0 0;font-size:11px;color:#94a3b8">Aperçu statique depuis les valeurs enregistrées (rechargez après sauvegarde).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
}
