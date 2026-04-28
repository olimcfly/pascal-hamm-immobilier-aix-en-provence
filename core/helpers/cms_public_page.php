<?php
declare(strict_types=1);

/**
 * Fusionne le contenu CMS publié avec des clés de template (noms de variables PHP).
 *
 * @param array<string, string> $overlay valeurs déjà calculées côté template (ignorées si le CMS fournit la clé)
 * @return array<string, string>|null null si page absente ou non publiée
 */
function cms_public_merge(string $slug, array $overlay = []): ?array
{
    if (!function_exists('db')) {
        return null;
    }

    try {
        $st = db()->prepare(
            'SELECT status, meta_title, meta_description, og_image_url, data_json FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1'
        );
        $st->execute([$slug]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return null;
    }

    if (!$row || ($row['status'] ?? '') !== 'published') {
        return null;
    }

    $out = $overlay;
    $data = json_decode((string) ($row['data_json'] ?? ''), true);
    $sections = is_array($data) ? ($data['sections'] ?? []) : [];
    if (!is_array($sections)) {
        $sections = [];
    }

    if (!empty($row['meta_title'])) {
        $out['pageTitle'] = (string) $row['meta_title'];
    }
    if (!empty($row['meta_description'])) {
        $out['metaDesc'] = (string) $row['meta_description'];
    }
    if (!empty($row['og_image_url'])) {
        $out['ogImage'] = (string) $row['og_image_url'];
    }

    foreach ($sections as $k => $v) {
        if (!is_string($k) || $k === '') {
            continue;
        }
        $out[$k] = (string) $v;
    }

    return $out;
}

/**
 * Applique les clés CMS sur des variables existantes (noms dans $keys).
 *
 * @param list<string> $keys
 */
function cms_public_apply(string $slug, array &$ref, array $keys): void
{
    $pick = [];
    foreach ($keys as $k) {
        if (array_key_exists($k, $ref)) {
            $pick[$k] = (string) $ref[$k];
        }
    }
    $merged = cms_public_merge($slug, $pick);
    if ($merged === null) {
        return;
    }
    foreach ($keys as $k) {
        if (isset($merged[$k])) {
            $ref[$k] = $merged[$k];
        }
    }
}

/**
 * Slug cms_pages pour le rendu public (guide local = une entrée par secteur).
 */
function cms_public_resolve_slug(string $template, array $templateVars): string
{
    if ($template === 'pages/services/services') {
        return 'services';
    }
    $slug = CmsPageDiscovery::templateToSlug($template);
    if ($template !== 'pages/guide-local/ville') {
        return $slug;
    }
    $s = $templateVars['slug'] ?? null;
    if (is_string($s) && $s !== '') {
        return CmsPageDiscovery::guideLocalCmsSlug($s);
    }

    return $slug;
}

/**
 * Applique les sections publiées sur un tableau (ex. $ville, $s du guide local).
 *
 * @param array<string, mixed> $assoc
 * @param list<string> $whitelist
 */
function cms_public_apply_publish_sections_to_assoc(string $slug, array &$assoc, array $whitelist): void
{
    if (!function_exists('db')) {
        return;
    }

    try {
        $st = db()->prepare('SELECT status, data_json FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
        $st->execute([$slug]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable) {
        return;
    }

    if (!$row || ($row['status'] ?? '') !== 'published') {
        return;
    }

    $data = json_decode((string) ($row['data_json'] ?? ''), true);
    $sections = is_array($data) && isset($data['sections']) && is_array($data['sections'])
        ? $data['sections']
        : [];

    foreach ($whitelist as $key) {
        if (!array_key_exists($key, $sections)) {
            continue;
        }
        $assoc[$key] = (string) $sections[$key];
    }
}

/**
 * Détecte le slug CMS d'une fiche zones/villes/*.php ou zones/quartiers/*.php depuis la pile d'appels.
 */
function cms_public_detect_zone_cms_slug(): ?string
{
    foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 16) as $frame) {
        $file = (string) ($frame['file'] ?? '');
        if (str_ends_with($file, '/_ville-secteur.php')) {
            continue;
        }
        if (preg_match('#/public/pages/zones/(villes|quartiers)/([^/]+)\.php$#', $file, $m)) {
            return CmsPageDiscovery::templateToSlug('pages/zones/' . $m[1] . '/' . $m[2]);
        }
    }

    return null;
}
