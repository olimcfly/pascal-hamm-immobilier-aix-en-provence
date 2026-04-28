<?php
declare(strict_types=1);

/**
 * Métriques transverses liste admin CMS (mots, scores SEO / sémantique / SERP).
 * Déterministe, sans appel API — pas d’effet sur le front public.
 */

if (!class_exists('CmsPageDiscovery', false)) {
    require_once dirname(__DIR__) . '/services/CmsPageDiscovery.php';
}
if (!function_exists('services_page_cms_merged_state')) {
    require_once __DIR__ . '/services_page_cms.php';
}

/**
 * Remplace {{city}} et {{advisor}} (accueil) pour comparaisons de texte.
 */
function cms_page_scores_replace_tokens(string $s): string
{
    $out = $s;
    if (str_contains($out, '{{city}}') && defined('APP_CITY')) {
        $out = str_replace('{{city}}', (string) APP_CITY, $out);
    }
    if (str_contains($out, '{{advisor}}') && defined('ADVISOR_NAME')) {
        $out = str_replace('{{advisor}}', (string) ADVISOR_NAME, $out);
    }

    return $out;
}

/**
 * Fiches « secteurs » (liste admin) : pages géo (villes, quartiers) + entrées guide local par secteur.
 * N’inclut pas l’index /secteurs ni les pages core du site.
 *
 * @param array<string, mixed> $row Ligne cms_pages
 */
function cms_page_row_is_secteur_fiche(array $row): bool
{
    $t = str_replace('\\', '/', trim((string) ($row['template'] ?? ''), '/'));
    if (preg_match('#^pages/zones/(?:villes|quartiers)/#', $t) === 1) {
        return true;
    }
    if (preg_match('/^guide-local-ville-/', (string) ($row['slug'] ?? '')) === 1) {
        return true;
    }

    return false;
}

/**
 * Chemin public canonique (relatif) pour liens « Voir en ligne ».
 *
 * @param array<string, mixed> $row Ligne cms_pages
 */
function cms_page_public_path(array $row): string
{
    $tpl = str_replace('\\', '/', trim((string) ($row['template'] ?? ''), '/'));
    $slug = (string) ($row['slug'] ?? '');

    if ($slug === 'home' || $tpl === 'pages/core/home') {
        return '/';
    }
    if ($slug === 'services' || $tpl === 'pages/services/services') {
        return '/services';
    }

    if (preg_match('#^pages/zones/villes/([^/]+)$#', $tpl, $m)) {
        $file = (string) $m[1];

        return '/immobilier/' . rawurlencode($file);
    }
    if (preg_match('#^pages/zones/quartiers/([^/]+)$#', $tpl, $m)) {
        $file = (string) $m[1];

        return '/quartier/' . rawurlencode($file);
    }
    if (preg_match('#^guide-local-ville-(.+)$#', $slug, $m)) {
        $sect = (string) $m[1];
        if ($sect !== '' && $sect !== 'default') {
            return '/guide-local/' . rawurlencode(str_replace('--', '', $sect));
        }
    }

    // Sous-dossier biens : routes explicites /biens, /biens/appartements, etc. (pas seulement le dernier segment du template)
    if (preg_match('#^pages/biens/([a-z0-9_-]+)$#i', $tpl, $m)) {
        $file = (string) $m[1];
        if ($file === 'index') {
            return '/biens';
        }
        if (in_array($file, ['bien-detail', 'bien-merci'], true)) {
            return '/biens';
        }

        return '/biens/' . $file;
    }

    if (preg_match('#^pages/(.+)$#', $tpl, $m)) {
        $rest = (string) $m[1];
        if ($rest === 'core/estimation-gratuite' || $rest === 'estimation/estimation-gratuite') {
            return '/estimation-gratuite';
        }
        if ($rest === 'core/contact' || $rest === 'contact/contact') {
            return '/contact';
        }
        if ($rest === 'core/a-propos' || $rest === 'core/about' || $rest === 'a-propos/a-propos') {
            return '/a-propos';
        }
        $parts = explode('/', $rest);
        $last = (string) (end($parts) ?: '');

        return $last !== '' ? ('/' . $last) : ('/' . $slug);
    }

    // Slugs cms historiques (listing biens) : URL publique toujours sous /biens/…
    $biensSlugToPath = [
        'biens' => '/biens',
        'appartements' => '/biens/appartements',
        'maisons' => '/biens/maisons',
        'prestige' => '/biens/prestige',
        'biens-vendus' => '/biens/vendus',
    ];
    if (isset($biensSlugToPath[$slug])) {
        return $biensSlugToPath[$slug];
    }

    return $slug !== '' ? ('/' . $slug) : '/';
}

/**
 * URL absolue publique (si url() / APP_URL dispo).
 */
function cms_page_public_url(array $row): string
{
    $path = cms_page_public_path($row);
    if (function_exists('url')) {
        return url($path);
    }
    if (defined('APP_URL')) {
        return rtrim((string) APP_URL, '/') . $path;
    }

    return $path;
}

/**
 * @param mixed $node
 * @return list<string>
 */
function cms_page_scores_flatten_text_nodes($node, int $depth = 0): array
{
    if ($depth > 40) {
        return [];
    }
    if (is_string($node)) {
        $t = trim($node);
        if ($t === '' || self_cms_is_urlish_string($t)) {
            return [];
        }

        return [$t];
    }
    if (!is_array($node)) {
        return [];
    }
    $out = [];
    foreach ($node as $k => $v) {
        if (is_string($k) && (preg_match('/_url$|_link$|image$/i', (string) $k) && is_string($v) && self_cms_is_urlish_string($v))) {
            continue;
        }
        foreach (cms_page_scores_flatten_text_nodes($v, $depth + 1) as $s) {
            $out[] = $s;
        }
    }

    return $out;
}

function self_cms_is_urlish_string(string $s): bool
{
    $t = trim($s);
    if ($t === '') {
        return true;
    }
    if (preg_match('#^https?://#i', $t)) {
        return true;
    }
    if (preg_match('#^/[\w./\-\#?=&;%+]+$#', $t) && !preg_match('/\s/', $t) && mb_strlen($t) < 200) {
        return (bool) preg_match('#^/(?:contact|services|estimation|biens|blog|a-propos|merci|prendre|prise-rdv|guide)#', $t);
    }

    return false;
}

/**
 * Bloc texte unifié pour comptage / analyse (hors JSON brut, URLs nettoyées côté comptage).
 *
 * @param array<string, mixed> $row cms_pages
 */
function cms_page_text_blob_for_metrics(array $row): string
{
    $slug = (string) ($row['slug'] ?? '');
    $title = (string) ($row['title'] ?? '');
    $mt = (string) ($row['meta_title'] ?? '');
    $md = (string) ($row['meta_description'] ?? '');

    if ($slug === 'services') {
        $st = services_page_cms_merged_state($row);
        $sc = $st['sc'];
        $tuples = services_page_resolved_service_tuples($sc);
        $body = cms_services_build_seo_indexable_text($sc, $tuples, true);
        $parts = [$title, (string) $st['pageTitle'], (string) $st['metaDesc'], $body];

        return trim(implode("\n\n", array_filter($parts, static fn (string $p): bool => $p !== '')));
    }

    $raw = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($raw)) {
        $raw = [];
    }

    if ($slug === 'home' || (string) ($row['template'] ?? '') === 'pages/core/home') {
        $fromJson = cms_page_scores_flatten_text_nodes($raw);
        $parts = array_merge([$title, $mt, $md], $fromJson);

        return trim(implode("\n\n", array_filter($parts, static fn (string $p): bool => $p !== '')));
    }

    $sections = CmsPageDiscovery::sectionsFromRow($row);
    $textSections = $sections === [] ? '' : trim(implode("\n\n", $sections));
    $rest = $raw;
    unset($rest['sections']);
    $fromRest = $rest === [] ? [] : cms_page_scores_flatten_text_nodes($rest);
    $parts = array_merge([$title, $mt, $md, $textSections], $fromRest);

    return trim(implode("\n\n", array_filter($parts, static fn (string $p): bool => $p !== '')));
}

/**
 * @param array<string, mixed> $row cms_pages
 */
function cms_page_word_count(array $row): int
{
    $blob = cms_page_text_blob_for_metrics($row);
    $plain = self_cms_text_to_words_string($blob);

    if ($plain === '') {
        return 0;
    }
    $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

    return count($words);
}

function self_cms_text_to_words_string(string $htmlOrText): string
{
    $s = $htmlOrText;
    $s = preg_replace('#<script\b[^>]*>.*?</script>#is', ' ', (string) $s) ?? $s;
    $s = preg_replace('#<style\b[^>]*>.*?</style>#is', ' ', $s) ?? $s;
    $s = strip_tags($s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('#https?://\S+#iu', ' ', $s) ?? $s;
    $s = preg_replace('#\b(?:/[\w\-.%/?\#&=+~]+\.(?:fr|com|org|net)(?:/[^\s]*)?)\b#iu', ' ', $s) ?? $s;
    $s = preg_replace('#\s+#u', ' ', $s) ?? $s;

    return trim($s);
}

/**
 * Nombre de mots du début (approx.) pour mot-clé dans intro.
 */
function self_cms_first_n_words_string(string $plain, int $n): string
{
    $w = preg_split('/\s+/u', trim($plain), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if ($w === []) {
        return '';
    }

    return mb_strtolower(implode(' ', array_slice($w, 0, $n)));
}

function self_cms_h2ish_count_for_row(array $row, string $plainBig): int
{
    $n = 0;
    $n += max(0, (int) preg_match_all('/<h2[\s>]/i', (string) ($row['data_json'] ?? ''), $x));
    $sections = CmsPageDiscovery::sectionsFromRow($row);
    $concat = $plainBig;
    foreach ($sections as $k => $v) {
        $concat .= "\n" . (string) $k . ' ' . (string) $v;
    }
    $n += max(0, (int) preg_match_all('/<h2[\s>]/i', $concat, $x));
    if ($n === 0) {
        $n = max(0, (int) preg_match_all('/\n#{2,3}\s.+/u', $concat, $x));
    }

    return $n;
}

/**
 * @return array{score:int, ok:list<string>, improve:list<string>}
 */
function cms_page_seo_score(array $row): array
{
    $slug = (string) ($row['slug'] ?? '');

    if ($slug === 'services') {
        $st = services_page_cms_merged_state($row);
        $sc = $st['sc'];
        $h1a = trim((string) ($sc['hero']['h1'] ?? '')) !== '' ? trim((string) $sc['hero']['h1']) : 'Mes services';
        $tuples = services_page_resolved_service_tuples($sc);
        $bodySeo = cms_services_build_seo_indexable_text($sc, $tuples, true);
        $res = cms_services_seo_score($sc, $row, $h1a, $bodySeo);
        $ok = [];
        $improve = [];
        foreach ($res['items'] as $it) {
            if (($it['ok'] ?? false) === true) {
                $ok[] = (string) ($it['text'] ?? '');
            } else {
                $improve[] = (string) ($it['text'] ?? '');
            }
        }

        return [
            'score' => (int) ($res['score'] ?? 0),
            'ok' => $ok,
            'improve' => $improve,
        ];
    }

    if ($slug === 'home' || (string) ($row['template'] ?? '') === 'pages/core/home') {
        return self_cms_seo_score_home($row);
    }

    return self_cms_seo_score_generic($row);
}

/**
 * @return array{score:int, ok:list<string>, improve:list<string>}
 */
function self_cms_seo_score_home(array $row): array
{
    $ok = [];
    $improve = [];
    $score = 0;
    $add = static function (bool $pass, string $label) use (&$ok, &$improve, &$score): void {
        if ($pass) {
            $ok[] = $label;
            $score += 10;
        } else {
            $improve[] = $label;
        }
    };

    $data = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($data)) {
        $data = [];
    }

    $focusRaw = trim(cms_page_scores_replace_tokens((string) ($data['home_seo_focus_keyword'] ?? '')));
    $focus = mb_strtolower($focusRaw);
    $h1 = trim((string) ($data['home_hero_title'] ?? ''));
    $h1l = mb_strtolower($h1);
    $mt = mb_strtolower(trim((string) ($row['meta_title'] ?? '')));
    $md = mb_strtolower(trim((string) ($row['meta_description'] ?? '')));
    $blob = cms_page_text_blob_for_metrics($row);
    $plain = self_cms_text_to_words_string($blob);
    $first100 = self_cms_first_n_words_string($plain, 100);

    $add($focus !== '', 'Mot-clé principal renseigné');
    $add($focus !== '' && $mt !== '' && str_contains($mt, $focus), 'Mot-clé dans le meta title');
    $add($focus !== '' && $md !== '' && str_contains($md, $focus), 'Mot-clé dans la meta description');
    $add($focus !== '' && $h1l !== '' && str_contains($h1l, $focus), 'Mot-clé dans le H1');
    $add($focus !== '' && $first100 !== '' && str_contains($first100, $focus), 'Mot-clé dans les 100 premiers mots');

    $wcount = cms_page_word_count($row);
    $add($wcount >= 800, 'Contenu textuel supérieur à 800 mots');

    $h2n = self_cms_h2ish_count_for_row($row, $plain);
    if ($h2n < 2) {
        $h2n = 0;
        $blocks = 0;
        foreach (['home_steps', 'home_faq', 'home_hero_pillars', 'home_sell_guide', 'home_reality_cards', 'home_market_cards', 'home_services'] as $bk) {
            if (!empty($data[$bk]) && is_array($data[$bk]) && count($data[$bk]) >= 1) {
                $blocks++;
            }
        }
        $h2n = $blocks >= 2 ? 2 : $h2n;
    }
    $add($h2n >= 2, 'Au moins 2 H2 ou blocs de section');

    $ogU = trim((string) ($row['og_image_url'] ?? ''));
    $ogAlt = trim((string) ($data['og_image_alt'] ?? $data['home_og_image_alt'] ?? ''));
    $add($ogU !== '' && $ogAlt !== '', 'Image principale ou Open Graph avec alt');

    $hasInternal = (bool) preg_match(
        '#/(?:contact|estimation|biens|a-propos|services|prendre-rendez-vous|prise-rdv|blog)(?:/|\b|$)#i',
        $plain
    );
    $add($hasInternal, 'Lien interne présent');

    $hasCta = (trim((string) ($data['home_hero_primary_label'] ?? '')) !== '' && trim((string) ($data['home_hero_primary_url'] ?? '')) !== '')
        || (trim((string) ($data['home_final_cta_title'] ?? '')) !== '');
    $add($hasCta, 'CTA présent');

    return ['score' => min(100, $score), 'ok' => $ok, 'improve' => $improve];
}

/**
 * @return array{score:int, ok:list<string>, improve:list<string>}
 */
function self_cms_seo_score_generic(array $row): array
{
    $ok = [];
    $improve = [];
    $score = 0;
    $add = static function (bool $pass, string $label) use (&$ok, &$improve, &$score): void {
        if ($pass) {
            $ok[] = $label;
            $score += 10;
        } else {
            $improve[] = $label;
        }
    };

    $data = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($data)) {
        $data = [];
    }
    $sections = CmsPageDiscovery::sectionsFromRow($row);
    $focus = '';
    if (isset($sections['seo_focus_keyword']) && trim((string) $sections['seo_focus_keyword']) !== '') {
        $focus = mb_strtolower(trim((string) $sections['seo_focus_keyword']));
    } elseif (isset($data['seo_focus_keyword']) && is_string($data['seo_focus_keyword']) && trim($data['seo_focus_keyword']) !== '') {
        $focus = mb_strtolower(trim($data['seo_focus_keyword']));
    }

    if ($focus === '') {
        $mt0 = trim((string) ($row['meta_title'] ?? ''));
        if ($mt0 !== '') {
            $bits = array_values(array_filter(preg_split('/\s+/u', $mt0) ?: []));
            $focus = mb_strtolower(implode(' ', array_slice($bits, 0, min(3, count($bits)))));
        }
    }

    $h1 = '';
    foreach (['pageH1', 'page_h1', 'h1', 'pageTitle', 'titre', 'title'] as $k) {
        if (isset($sections[$k]) && trim((string) $sections[$k]) !== '') {
            $h1 = trim((string) $sections[$k]);
            break;
        }
    }
    if ($h1 === '' && $focus !== '' && (isset($sections['pageContent']) || isset($sections['body']))) {
        $pc = (string) ($sections['pageContent'] ?? $sections['body'] ?? '');
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $pc, $hm)) {
            $h1 = trim(strip_tags((string) $hm[1]));
        }
    }

    $h1l = mb_strtolower($h1);
    $mt = mb_strtolower(trim((string) ($row['meta_title'] ?? '')));
    $md = mb_strtolower(trim((string) ($row['meta_description'] ?? '')));
    $blob = cms_page_text_blob_for_metrics($row);
    $plain = self_cms_text_to_words_string($blob);
    $first100 = self_cms_first_n_words_string($plain, 100);

    $add($focus !== '', 'Mot-clé principal renseigné (meta ou section)');
    $add($focus !== '' && $mt !== '' && str_contains($mt, $focus), 'Mot-clé dans le meta title');
    $add($focus !== '' && $md !== '' && str_contains($md, $focus), 'Mot-clé dans la meta description');
    $add($focus !== '' && $h1l !== '' && str_contains($h1l, $focus), 'Mot-clé dans le H1 ou titre de page');
    $add($focus !== '' && $first100 !== '' && str_contains($first100, $focus), 'Mot-clé dans les 100 premiers mots');

    $wcount = cms_page_word_count($row);
    $add($wcount >= 800, 'Contenu textuel supérieur à 800 mots');

    $h2n = self_cms_h2ish_count_for_row($row, $plain);
    $add($h2n >= 2, 'Au moins 2 H2 ou titres de section');

    $ogU = trim((string) ($row['og_image_url'] ?? ''));
    $ogAlt = '';
    foreach (['og_image_alt', 'ogImageAlt', 'image_alt', 'imageAlt'] as $k) {
        if (isset($sections[$k]) && trim((string) $sections[$k]) !== '') {
            $ogAlt = trim((string) $sections[$k]);
            break;
        }
    }
    $add($ogU !== '' && $ogAlt !== '', 'Image principale ou Open Graph avec alt');

    $hasInternal = (bool) preg_match(
        '#/(?:contact|estimation|biens|a-propos|services|prendre-rendez-vous|prise-rdv|blog|secteurs|immobilier|quartier)(?:/|\b|$)#i',
        $plain
    );
    $add($hasInternal, 'Lien interne présent');

    $hasCta = (bool) preg_match(
        '/\b(?:contact|rendez|estimation|en savoir|découvrir|téléphoner|appeler)\b/ui',
        $plain
    ) || (bool) preg_match('#/contact#i', $plain);
    $add($hasCta, 'CTA ou intention d’action présente');

    return ['score' => min(100, $score), 'ok' => $ok, 'improve' => $improve];
}

/**
 * @return array{score:int, ok:list<string>, improve:list<string>}
 */
function cms_page_semantic_score(array $row): array
{
    $blob = cms_page_text_blob_for_metrics($row);
    $plain = self_cms_text_to_words_string($blob);
    $low = mb_strtolower($plain);
    $ok = [];
    $improve = [];
    $score = 0;

    $add = static function (int $pts, bool $pass, string $label) use (&$ok, &$improve, &$score): void {
        if ($pass) {
            $ok[] = $label;
            $score += $pts;
        } else {
            $improve[] = $label;
        }
    };

    $focus = '';
    $slug = (string) ($row['slug'] ?? '');
    if ($slug === 'services') {
        $st = services_page_cms_merged_state($row);
        $focus = mb_strtolower(trim((string) ($st['sc']['seo']['focus_keyword'] ?? '')));
    } else {
        $d = json_decode((string) ($row['data_json'] ?? ''), true);
        if (is_array($d)) {
            $focus = mb_strtolower(trim((string) ($d['home_seo_focus_keyword'] ?? $d['seo']['focus_keyword'] ?? '')));
            $focus = mb_strtolower(cms_page_scores_replace_tokens($focus));
        }
    }
    if ($focus === '') {
        $focus = mb_strtolower(trim((string) ($row['meta_title'] ?? '')));
        if (mb_strlen($focus) > 40) {
            $focus = (string) mb_substr($focus, 0, 40);
        }
    }
    $add(15, $focus !== '' && str_contains($low, $focus), 'Mot-clé / thème central dans le texte');

    $city = defined('APP_CITY') ? mb_strtolower((string) APP_CITY) : 'aix-en-provence';
    $add(15, $city !== '' && str_contains($low, $city), 'Ville ou zone locale (APP_CITY)');

    $lex = [
        'immobilier', 'bien', 'vente', 'achat', 'estimation', 'maison', 'appartement', 'acquéreur', 'vendeur',
        'marché', 'prix', 'secteur', 'quartier', 'investissement', 'location', 'projet immobilier', 'vendre', 'acheter',
    ];
    $hits = 0;
    foreach ($lex as $w) {
        if (str_contains($low, $w)) {
            $hits++;
        }
    }
    $add(15, $hits >= 4, 'Champ lexical immobilier (au moins 4 thèmes)');

    $ben = ['accompagn', 'bénéfic', 'rapide', 'sérén', 'conseil', 'garant', 'réactiv', 'proximit', 'sécuris', 'optimis', 'accompagner', 'rassur'];
    $bhit = 0;
    foreach ($ben as $w) {
        if (str_contains($low, $w)) {
            $bhit++;
        }
    }
    $add(10, $bhit >= 2, 'Bénéfices / valeur pour le client');

    $act = ['vendre', 'acheter', 'estimer', 'investir', 'contacter', 'recherch', 'échang', 'prendr', 'découvrir', 'négoci', 'accompagner', 'sélectionn'];
    $ahit = 0;
    foreach ($act as $w) {
        if (str_contains($low, $w)) {
            $ahit++;
        }
    }
    $add(10, $ahit >= 2, 'Verbes d’action liés au projet immobilier');

    $h2c = self_cms_h2ish_count_for_row($row, $plain);
    if ($slug === 'home') {
        $d = json_decode((string) ($row['data_json'] ?? ''), true);
        $c = 0;
        if (is_array($d)) {
            foreach (['home_steps', 'home_faq', 'home_hero_pillars', 'home_sell_guide'] as $bk) {
                if (!empty($d[$bk]) && is_array($d[$bk]) && count($d[$bk]) >= 1) {
                    $c++;
                }
            }
        }
        $h2c = max($h2c, $c);
    }
    if ($slug === 'services') {
        $h2c = 5;
    }
    $add(10, $h2c >= 2, 'Plusieurs blocs de contenu (titres / sections)');

    $words = $plain !== '' ? (preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: []) : [];
    $wlen = count($words);
    $add(10, $wlen >= 400, 'Texte assez développé (≥ 400 mots)');

    $repOk = self_cms_repetition_ok($words);
    $add(10, $repOk, 'Répétition modérée (pas de mot > 4 % du texte)');

    $add(5, (bool) preg_match('/\b(?:contact|rendez|estimation|découvrir|offre|gratuit|cliquez)\b/iu', $plain) || (bool) preg_match('#/contact#i', $plain), 'CTA contextualisé (contact, RDV, etc.)');

    return ['score' => min(100, $score), 'ok' => $ok, 'improve' => $improve];
}

/**
 * @param list<string> $words
 */
function self_cms_repetition_ok(array $words): bool
{
    if ($words === [] || count($words) < 20) {
        return true;
    }
    $freq = [];
    foreach ($words as $w) {
        $k = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', $w) ?? $w);
        if (mb_strlen($k) < 3) {
            continue;
        }
        $freq[$k] = ($freq[$k] ?? 0) + 1;
    }
    if ($freq === []) {
        return true;
    }
    $max = max($freq);
    $ratio = $max / count($words);

    return $ratio < 0.04;
}

/**
 * @return array{score:int, ok:list<string>, improve:list<string>}
 */
function cms_page_serp_score(array $row): array
{
    $ok = [];
    $improve = [];
    $score = 0;
    $add = static function (int $pts, bool $pass, string $label) use (&$ok, &$improve, &$score): void {
        if ($pass) {
            $ok[] = $label;
            $score += $pts;
        } else {
            $improve[] = $label;
        }
    };

    $mt = trim((string) ($row['meta_title'] ?? ''));
    $md = trim((string) ($row['meta_description'] ?? ''));
    $mtL = mb_strtolower($mt);
    $mdL = mb_strtolower($md);
    $focus = '';
    $slug = (string) ($row['slug'] ?? '');

    $d = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($d)) {
        $d = [];
    }

    if ($slug === 'services') {
        $st = services_page_cms_merged_state($row);
        $focus = mb_strtolower(trim((string) ($st['sc']['seo']['focus_keyword'] ?? '')));
    } else {
        if (isset($d['home_seo_focus_keyword']) && trim((string) $d['home_seo_focus_keyword']) !== '') {
            $focus = mb_strtolower(cms_page_scores_replace_tokens(trim((string) $d['home_seo_focus_keyword'])));
        } elseif (isset($d['seo']['focus_keyword']) && is_string($d['seo']['focus_keyword'])) {
            $focus = mb_strtolower(trim($d['seo']['focus_keyword']));
        }
        if ($focus === '' && isset($d['services_content']['seo']['focus_keyword']) && is_string($d['services_content']['seo']['focus_keyword'])) {
            $focus = mb_strtolower(trim((string) $d['services_content']['seo']['focus_keyword']));
        }
    }
    if ($focus === '') {
        $bits = array_values(array_filter(preg_split('/\s+/u', $mt) ?: []));
        $focus = mb_strtolower(implode(' ', array_slice($bits, 0, min(3, count($bits)))));
    }

    $stSlug = (string) ($row['slug'] ?? '');
    $slugOk = $stSlug !== '' && (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $stSlug) && !str_contains($stSlug, '--') && strlen($stSlug) <= 80;

    $city = defined('APP_CITY') ? mb_strtolower((string) APP_CITY) : '';
    $cityToken = 'aix';
    if ($city !== '') {
        $norm = str_replace("'", '’', $city);
        $first = preg_split('~[-\s,]+~u', $norm, 2);
        $cityToken = mb_strtolower((string) ($first[0] ?? $norm));
    }
    if ($cityToken === '' || $cityToken === '0') {
        $cityToken = 'aix';
    }

    $mdBenefit = (bool) preg_match(
        '/\b(pour|afin|sans engagement|bénéfici|découvr|gagnez|amélior|garant|accompagn|expert|local|cliquez|estimation|prix|rapide|contact)\b/iu',
        $md
    );

    $cityInTitle = $mtL !== '' && $city !== '' && (str_contains($mtL, $city) || ($cityToken !== '' && str_contains($mtL, (string) $cityToken)));

    $add(10, $mt !== '', 'Meta title présent');
    $lmt = mb_strlen($mt);
    $add(15, $lmt >= 45 && $lmt <= 65, 'Meta title entre 45 et 65 caractères');
    $add(10, $md !== '', 'Meta description présente');
    $lmd = mb_strlen($md);
    $add(15, $lmd >= 120 && $lmd <= 160, 'Meta description entre 120 et 160 caractères');
    $add(10, $slugOk, 'Slug propre (lettres, tirets, pas de doublon --)');
    $add(10, $focus !== '' && $mtL !== '' && str_contains($mtL, $focus), 'Mot-clé principal dans le meta title');
    $add(10, $focus !== '' && $mdL !== '' && str_contains($mdL, $focus), 'Mot-clé principal dans la meta description');
    $add(10, $cityInTitle, 'Ville / zone évoquée dans le title');
    $add(5, $md !== '' && $mdBenefit, 'Meta description orientée bénéfice (formulation explicite)');
    $add(5, $mt !== '' && $md !== '', 'Aperçu SERP possible (titre + description renseignés)');

    return ['score' => min(100, $score), 'ok' => $ok, 'improve' => $improve];
}

/**
 * @return 'faible'|'moyen'|'bon'
 */
function cms_page_score_band(int $score): string
{
    if ($score < 50) {
        return 'faible';
    }
    if ($score < 75) {
        return 'moyen';
    }

    return 'bon';
}

function cms_page_score_badge_class(int $score): string
{
    if ($score < 50) {
        return 'cms-score--low';
    }
    if ($score < 75) {
        return 'cms-score--medium';
    }

    return 'cms-score--good';
}

/**
 * @return array{label:string, kind:string}
 * kind: priority|enrich|ok|excellent|neutral
 */
function cms_page_priority_label(int $words, int $seo, int $sem, int $serp): array
{
    $sum3 = $seo + $sem + $serp;
    if ($sum3 >= 240) {
        return ['label' => 'Très bon', 'kind' => 'excellent'];
    }
    if ($seo >= 75 && $sem >= 75 && $serp >= 75) {
        return ['label' => 'Correct', 'kind' => 'ok'];
    }
    if ($seo < 50 || $serp < 50) {
        return ['label' => 'À corriger en priorité', 'kind' => 'priority'];
    }
    if ($words < 500) {
        return ['label' => 'À enrichir', 'kind' => 'enrich'];
    }

    return ['label' => 'À suivre', 'kind' => 'neutral'];
}
