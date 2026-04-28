<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/core/services/CmsPageDiscovery.php';

function cms_ensure_extended_columns(\PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    try {
        $pdo->exec('ALTER TABLE cms_pages ADD COLUMN page_level TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT \'1=public, 2=utilitaire\' AFTER kind');
    } catch (Throwable) {
    }
    try {
        $pdo->exec('ALTER TABLE cms_pages ADD COLUMN og_image_url VARCHAR(512) NULL DEFAULT NULL AFTER meta_description');
    } catch (Throwable) {
    }
    $done = true;
}

function cms_sync_pages_from_disk(): int
{
    $pdo = db();
    cmsEnsureCmsPagesTable($pdo);
    cms_ensure_extended_columns($pdo);

    $inserted = 0;
    foreach (CmsPageDiscovery::scan(ROOT_PATH) as $disc) {
        $slug = $disc['slug'];
        $st = $pdo->prepare('SELECT id, data_json FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
        $st->execute([$slug]);
        $existing = $st->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $cur = CmsPageDiscovery::sectionsFromRow($existing);
            foreach ($disc['sections'] as $k => $v) {
                if (!array_key_exists($k, $cur) || trim((string) $cur[$k]) === '') {
                    $cur[$k] = $v;
                }
            }
            $dataJson = CmsPageDiscovery::mergeDataJson((string) ($existing['data_json'] ?? ''), $cur);
            $up = $pdo->prepare('UPDATE cms_pages SET template = :tpl, page_level = :lvl, data_json = :dj, updated_at = NOW() WHERE id = :id');
            $up->execute([
                ':tpl' => $disc['template'],
                ':lvl' => $disc['page_level'],
                ':dj' => $dataJson,
                ':id' => (int) $existing['id'],
            ]);

            continue;
        }

        $dataJson = CmsPageDiscovery::mergeDataJson(null, $disc['sections']);
        $ins = $pdo->prepare(
            'INSERT INTO cms_pages (site_id, slug, title, template, page_type, page_level, status, meta_title, meta_description, og_image_url, data_json, created_at, updated_at)
             VALUES (1, :slug, :title, :template, \'page\', :pl, \'draft\', NULL, NULL, NULL, :dj, NOW(), NOW())'
        );
        $ins->execute([
            ':slug' => $slug,
            ':title' => $disc['label'],
            ':template' => $disc['template'],
            ':pl' => $disc['page_level'],
            ':dj' => $dataJson,
        ]);
        $inserted++;
    }

    try {
        $pdo->prepare("DELETE FROM cms_pages WHERE site_id = 1 AND slug = 'guide-local-ville' LIMIT 1")->execute();
    } catch (Throwable) {
    }

    return $inserted;
}

/**
 * @return array<string, mixed>|null
 */
function cms_load_page_row(string $slug): ?array
{
    $pdo = db();
    $st = $pdo->prepare('SELECT * FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
    $st->execute([$slug]);

    $row = $st->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function cms_update_generic_page(string $slug, array $post): void
{
    $pdo = db();
    cms_ensure_extended_columns($pdo);
    $row = cms_load_page_row($slug);
    if (!$row) {
        throw new RuntimeException('Page CMS inconnue: ' . $slug);
    }

    $status = (string) ($post['status'] ?? 'draft');
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }

    $title = trim((string) ($post['title'] ?? ''));
    $metaTitle = trim((string) ($post['meta_title'] ?? ''));
    $metaDesc = trim((string) ($post['meta_description'] ?? ''));
    $og = trim((string) ($post['og_image_url'] ?? ''));

    $sections = CmsPageDiscovery::sectionsFromRow($row);
    foreach ($sections as $key => $_) {
        $field = 'section_' . $key;
        if (array_key_exists($field, $post)) {
            $sections[$key] = (string) $post[$field];
        }
    }

    $dataJson = CmsPageDiscovery::mergeDataJson((string) ($row['data_json'] ?? ''), $sections);
    $pageLevel = (int) ($post['page_level'] ?? $row['page_level'] ?? 1);
    if (!in_array($pageLevel, [1, 2], true)) {
        $pageLevel = 1;
    }

    $upd = $pdo->prepare(
        'UPDATE cms_pages SET title = :title, status = :status, meta_title = :mt, meta_description = :md,
         og_image_url = :og, page_level = :pl, data_json = :dj, updated_at = NOW() WHERE id = :id'
    );
    $upd->execute([
        ':title' => $title !== '' ? $title : (string) $row['title'],
        ':status' => $status,
        ':mt' => $metaTitle !== '' ? $metaTitle : null,
        ':md' => $metaDesc !== '' ? $metaDesc : null,
        ':og' => $og !== '' ? $og : null,
        ':pl' => $pageLevel,
        ':dj' => $dataJson,
        ':id' => (int) $row['id'],
    ]);
}

/**
 * Checklist SEO (sans JavaScript).
 *
 * @return array{score:int, items:list<array{ok:bool, text:string}>}
 */
function cms_seo_checklist_generic(string $metaTitle, string $metaDesc, string $bodySample): array
{
    $mt = $metaTitle;
    $md = $metaDesc;
    $body = $bodySample;
    $items = [];
    $score = 0;

    $mtLen = mb_strlen($mt);
    $mtOk = $mtLen >= 45 && $mtLen <= 65;
    $items[] = ['ok' => $mtOk, 'text' => 'Meta title entre 45 et 65 caractères (' . $mtLen . ')'];
    if ($mtOk) {
        $score += 25;
    }

    $mdLen = mb_strlen($md);
    $mdOk = $mdLen >= 110 && $mdLen <= 170;
    $items[] = ['ok' => $mdOk, 'text' => 'Meta description entre 110 et 170 caractères (' . $mdLen . ')'];
    if ($mdOk) {
        $score += 25;
    }

    $words = $body !== '' ? preg_split('/\s+/u', trim($body)) ?: [] : [];
    $wOk = count($words) >= 12;
    $items[] = ['ok' => $wOk, 'text' => 'Contenu principal : au moins ~12 mots dans les champs édités'];
    if ($wOk) {
        $score += 25;
    }

    $items[] = ['ok' => $mt !== '' && $md !== '', 'text' => 'Meta title et description renseignés'];
    if ($mt !== '' && $md !== '') {
        $score += 25;
    }

    return ['score' => min(100, $score), 'items' => $items];
}

/**
 * Panneau SEO accueil (calcul serveur, sans JavaScript).
 *
 * @param array<string, mixed> $page
 * @param array<string, mixed> $data
 * @return array{score:int, items:list<array{ok:bool, text:string}>, mt_len:int, md_len:int, hero_words:int}
 */
function cms_home_seo_panel_state(array $page, array $data): array
{
    $mt = (string) ($page['meta_title'] ?? '');
    $md = (string) ($page['meta_description'] ?? '');
    $h1 = (string) ($data['home_hero_title'] ?? '');
    $hs = (string) ($data['home_hero_subtitle'] ?? '');
    $focus = mb_strtolower(trim((string) ($data['home_seo_focus_keyword'] ?? '')));
    $bodyText = $h1 . ' ' . $hs . ' ' . $md;
    $items = [];
    $score = 0;

    $mtLen = mb_strlen($mt);
    $mtOk = $mtLen >= 50 && $mtLen <= 60;
    $items[] = ['ok' => $mtOk, 'text' => 'Meta title 50–60 caractères (' . $mtLen . ')'];
    if ($mtOk) {
        $score += 20;
    }

    $mdLen = mb_strlen($md);
    $mdOk = $mdLen >= 120 && $mdLen <= 160;
    $items[] = ['ok' => $mdOk, 'text' => 'Meta description 120–160 caractères (' . $mdLen . ')'];
    if ($mdOk) {
        $score += 20;
    }

    $hOk = $h1 !== '';
    $items[] = ['ok' => $hOk, 'text' => 'Titre hero (H1) renseigné'];
    if ($hOk) {
        $score += 15;
    }

    $focusOk = $focus === '' || mb_strpos(mb_strtolower($h1), $focus) !== false;
    $items[] = ['ok' => $focusOk, 'text' => 'Mot-clé principal présent dans le H1'];
    if ($focusOk) {
        $score += 15;
    }

    $fmtOk = $focus === '' || mb_strpos(mb_strtolower($mt), $focus) !== false;
    $items[] = ['ok' => $fmtOk, 'text' => 'Mot-clé principal dans le meta title'];
    if ($fmtOk) {
        $score += 15;
    }

    $fmdOk = $focus === '' || mb_strpos(mb_strtolower($md), $focus) !== false;
    $items[] = ['ok' => $fmdOk, 'text' => 'Mot-clé principal dans la meta description'];
    if ($fmdOk) {
        $score += 15;
    }

    $words = $h1 !== '' || $hs !== '' ? preg_split('/\s+/u', trim($h1 . ' ' . $hs)) ?: [] : [];
    $heroWords = count(array_filter($words));

    return [
        'score' => min(100, $score),
        'items' => $items,
        'mt_len' => $mtLen,
        'md_len' => $mdLen,
        'hero_words' => $heroWords,
    ];
}
