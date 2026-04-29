<?php
// ============================================================
// ROUTES — Pascal Hamm Immobilier
// Chaque route appelle le helper page() défini dans public/index.php
// ============================================================

declare(strict_types=1);

/**
 * Harmonise les clés admin (profil_*, zone_*) avec ce qu'attendent les pages publiques (advisor_name, city…).
 * Valeurs de repli : Pascal Hamm / Aix-en-Provence si BDD et .env sont vides.
 */
function enrichPublicSiteData(array $raw): array
{
    $prenom = trim((string) ($raw['profil_prenom'] ?? ''));
    $nom = trim((string) ($raw['profil_nom'] ?? ''));
    $full = trim($prenom . ' ' . $nom);
    if ($full !== '') {
        $raw['advisor_name'] = preg_replace('/\s+/u', ' ', $full);
    }

    $ville = trim((string) ($raw['profil_ville'] ?? ''));
    if ($ville === '') {
        $ville = trim((string) ($raw['zone_ville'] ?? ''));
    }
    if ($ville !== '') {
        $raw['city'] = $ville;
    }

    if (!empty($raw['profil_telephone'])) {
        $raw['phone'] = $raw['profil_telephone'];
    }
    if (!empty($raw['profil_email'])) {
        $raw['email'] = $raw['profil_email'];
    }
    if (empty($raw['territory_name']) && $ville !== '') {
        $raw['territory_name'] = $ville;
    }

    if (empty($raw['advisor_name'])) {
        $an = (string) (defined('ADVISOR_NAME') ? ADVISOR_NAME : '');
        if ($an === '' && !empty($_ENV['ADVISOR_NAME'])) {
            $an = (string) $_ENV['ADVISOR_NAME'];
        }
        if ($an === '' && defined('APP_NAME')) {
            $an = preg_replace('/\s+Immobilier.*/iu', '', (string) APP_NAME);
        }
        $raw['advisor_name'] = trim($an) !== '' ? trim($an) : 'Pascal Hamm';
    }
    if (empty($raw['city'])) {
        $c = (string) (defined('APP_CITY') ? APP_CITY : '');
        if ($c === '' && !empty($_ENV['APP_CITY'])) {
            $c = (string) $_ENV['APP_CITY'];
        }
        $raw['city'] = trim($c) !== '' ? trim($c) : 'Aix-en-Provence';
    }
    if (empty($raw['phone']) && defined('APP_PHONE') && (string) APP_PHONE !== '') {
        $raw['phone'] = (string) APP_PHONE;
    }
    if (empty($raw['email']) && defined('APP_EMAIL') && (string) APP_EMAIL !== '') {
        $raw['email'] = (string) APP_EMAIL;
    }
    if (empty($raw['territory_name'])) {
        $raw['territory_name'] = $raw['city'];
    }

    if (empty($raw['address']) && defined('APP_ADDRESS') && (string) APP_ADDRESS !== '') {
        $raw['address'] = (string) APP_ADDRESS;
    }
    if (empty($raw['advisor_tagline']) && defined('APP_ADVISOR_TAGLINE') && (string) APP_ADVISOR_TAGLINE !== '') {
        $raw['advisor_tagline'] = (string) APP_ADVISOR_TAGLINE;
    }
    if (empty($raw['advisor_rsac']) && defined('ADVISOR_RSAC') && (string) ADVISOR_RSAC !== '') {
        $raw['advisor_rsac'] = (string) ADVISOR_RSAC;
    }

    return $raw;
}

/**
 * Charge tous les settings de l'utilisateur courant en un seul appel SQL.
 * Utilisé pour passer $siteSettings aux templates (home, a-propos, contact…)
 */
function siteData(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        $pdo = db();
        $user = Auth::user();
        if (!$user) {
            // Pages publiques : charger les settings du propriétaire du site (rôle user)
            $row = $pdo->query("SELECT id FROM users WHERE role = 'user' ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return $cache = enrichPublicSiteData([]);
            }
            $userId = (int) $row['id'];
        } else {
            $userId = (int) $user['id'];
        }
        $stmt = $pdo->prepare(
            'SELECT setting_key, setting_value FROM settings WHERE user_id = ? ORDER BY setting_key'
        );
        $stmt->execute([$userId]);
        $raw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        $cache = enrichPublicSiteData($raw);
    } catch (\Throwable $e) {
        $cache = enrichPublicSiteData([]);
    }
    return $cache;
}

/**
 * Slug canonique pour les fiches villes (fichiers en minuscules) + fautes de frappe fréquentes.
 */
function normalizeVilleSlug(string $slug): string
{
    $lower = strtolower($slug);

    return $lower;
}

/**
 * Slug canonique fiches quartiers (fusion / anciennes URLs).
 */
function normalizeQuartierSlug(string $slug): string
{
    // Fiche centre-ville = Aix (centre-ville.php).
    static $aliases = [];
    $lower = strtolower($slug);

    return $aliases[$lower] ?? $lower;
}

/** Anciennes fiches (slugs 33) : 301 vers la fiche Pays d'Aix. */
function redirectLegacyZoneToAix(string $canonical, string $zoneType): void
{
}

// ── Page 404 ────────────────────────────────────────────────
$router->set404(function () {
    page('pages/404');
});

// ════════════════════════════════════════════════════════════
// FICHIERS TECHNIQUES (URL canoniques, hors CMS slug)
// ════════════════════════════════════════════════════════════

$router->get('/robots.txt', function (): void {
    require ROOT_PATH . '/public/robots.php';
    exit;
});

$router->get('/sitemap.xml', function (): void {
    require ROOT_PATH . '/public/sitemap.php';
    exit;
});

// ════════════════════════════════════════════════════════════
// PAGES PRINCIPALES
// ════════════════════════════════════════════════════════════

$router->get('/', function () {
    page('pages/core/home', ['siteSettings' => siteData()]);
});

$router->get('/a-propos', function () {
    page('pages/core/a-propos', [
        'siteSettings' => siteData(),
        'bodyClass' => 'page-a-propos-light',
    ]);
});

$router->get('/contact', function () {
    page('pages/core/contact', ['siteSettings' => siteData()]);
});
$router->post('/contact', function () {
    page('pages/core/contact', ['siteSettings' => siteData()]);
});

$router->get('/plan-du-site', function () {
    page('pages/core/plan-du-site', ['siteSettings' => siteData()]);
});

$router->get('/services', function () {
    page('pages/services/services');
});

// ════════════════════════════════════════════════════════════
// BIENS IMMOBILIERS
// (routes spécifiques AVANT la route slug générique)
// ════════════════════════════════════════════════════════════

$router->get('/biens/appartements', function () {
    page('pages/biens/appartements');
});
$router->get('/biens/maisons', function () {
    page('pages/biens/maisons');
});
$router->get('/biens/prestige', function () {
    page('pages/biens/prestige');
});
$router->get('/biens/vendus', function () {
    page('pages/biens/vendus');
});
$router->get('/biens/{slug}/merci', function (string $slug) {
    page('pages/biens/bien-merci', ['slug' => $slug]);
});
$bienDetailPage = function (string $slug) {
    page('pages/biens/bien-detail', ['slug' => $slug]);
};
$router->get('/biens/{slug}', $bienDetailPage);
$router->post('/biens/{slug}', $bienDetailPage);
$router->get('/biens', function () {
    page('pages/biens/index');
});
$router->get('/bien/{slug}', function (string $slug) {
    // Rétro-compatibilité URL courte
    header('Location: /biens/' . rawurlencode($slug), true, 301);
    exit;
});

// ════════════════════════════════════════════════════════════
// BLOG
// ════════════════════════════════════════════════════════════

$router->get('/blog/{slug}', function (string $slug) {
    page('pages/blog/article', ['slug' => $slug]);
});
$router->get('/blog', function () {
    page('pages/blog/index');
});

// ════════════════════════════════════════════════════════════
// ACTUALITÉS
// ════════════════════════════════════════════════════════════

$router->get('/actualites/{slug}', function (string $slug) {
    page('pages/actualites/article', ['slug' => $slug]);
});
$router->get('/actualites', function () {
    page('pages/actualites/index');
});

// ════════════════════════════════════════════════════════════
// ESTIMATION
// ════════════════════════════════════════════════════════════

$router->get('/estimation-gratuite', function () {
    page('pages/estimation/estimation-gratuite');
});
$router->post('/estimation-gratuite', function () {
    page('pages/estimation/estimation-gratuite');
});
$router->get('/estimation-instantanee', function () {
    page('pages/estimation-instantanee');
});
$router->get('/estimation/instantanee', function () {
    page('pages/estimation/instantanee');
});
$router->get('/estimation/tunnel', function () {
    page('pages/estimation/tunnel');
});
$router->post('/estimation/tunnel', function () {
    page('pages/estimation/tunnel');
});
$router->get('/estimation/resultat', function () {
    page('pages/estimation/resultat');
});
$router->get('/estimation-gratuite/resultat', function () {
    page('pages/estimation/resultat');
});
$router->post('/estimation-gratuite/resultat', function () {
    page('pages/estimation/resultat');
});
$router->get('/merci-estimation', function () {
    page('pages/estimation/merc-estimation');
});

// ════════════════════════════════════════════════════════════
// FINANCEMENT
// ════════════════════════════════════════════════════════════

$router->get('/financement', function () {
    page('pages/financement/financement');
});
$router->post('/financement', function () {
    page('pages/financement/financement');
});

// ════════════════════════════════════════════════════════════
// GUIDE LOCAL (secteurs / villes)
// ════════════════════════════════════════════════════════════
// Fiche annuaire par ville (commerces + présentation BDD) — AVANT le slug générique
$router->get('/guide-local/annuaire/{ville_slug}', function (string $ville_slug) {
    $v = normalizeVilleSlug($ville_slug);
    if (strcasecmp($v, $ville_slug) !== 0) {
        header('Location: ' . url('/guide-local/annuaire/' . rawurlencode($v)), true, 301);
        exit;
    }
    page('pages/guide-local/annuaire-ville', ['ville_slug' => $v]);
});
$router->get('/guide-local/{slug}', function (string $slug) {
    page('pages/guide-local/ville', ['slug' => $slug]);
});
$router->get('/guide-local', function () {
    page('pages/guide-local/index');
});

$router->get('/commerces/{ville_slug}/{poi_slug}', function (string $ville_slug, string $poi_slug) {
    $ville = normalizeVilleSlug($ville_slug);
    if (strcasecmp($ville, $ville_slug) !== 0) {
        header('Location: ' . url('/commerces/' . rawurlencode($ville) . '/' . rawurlencode($poi_slug)), true, 301);
        exit;
    }
    page('pages/annuaire/fiche', ['ville_slug' => $ville, 'poi_slug' => $poi_slug]);
});

// ════════════════════════════════════════════════════════════
// GUIDES & RESSOURCES
// ════════════════════════════════════════════════════════════

$router->get('/guides/guide-vendeur', function () {
    page('pages/guides/guide-vendeur');
});
$router->get('/guides/guide-acheteur', function () {
    page('pages/guides/guide-acheteur');
});
$router->get('/ressources/guide-vendeur', function () {
    page('pages/ressources/guide-vendeur');
});
$router->get('/ressources/guide-acheteur', function () {
    page('pages/ressources/guide-acheteur');
});
$router->get('/ressources', function () {
    page('pages/ressources/index');
});

// Alias raccourcis
$router->get('/acheter', function () {
    page('pages/ressources/guide-acheteur');
});
$router->get('/vendre', function () {
    page('pages/ressources/guide-vendeur');
});

// ════════════════════════════════════════════════════════════
// CAPTURE / CONVERSION
// ════════════════════════════════════════════════════════════

$router->get('/capture/estimation-gratuite', function () {
    page('pages/capture/estimation-gratuite');
});
$router->post('/capture/estimation-gratuite', function () {
    page('pages/capture/estimation-gratuite');
});
$router->get('/capture/guide-offert', function () {
    page('pages/capture/guide-offert');
});
$router->post('/capture/guide-offert', function () {
    page('pages/capture/guide-offert');
});
$router->get('/capture/merci', function () {
    page('pages/capture/merci');
});

$router->get('/avis-valeur', function () {
    page('pages/conversion/avis-valeur');
});
$router->post('/avis-valeur', function () {
    page('pages/conversion/avis-valeur');
});
$router->get('/avis-de-valeur', function () {
    page('pages/conversion/avis-valeur');
});
$router->post('/avis-de-valeur', function () {
    page('pages/conversion/avis-valeur');
});
$router->get('/prendre-rendez-vous', function () {
    page('pages/conversion/prendre-rendez-vous');
});
$router->post('/prendre-rendez-vous', function () {
    page('pages/conversion/prendre-rendez-vous');
});
$router->get('/prise-rdv', function () {
    page('pages/conversion/prise-rdv');
});
$router->post('/prise-rdv', function () {
    page('pages/conversion/prise-rdv');
});
$router->get('/conversion/merci', function () {
    page('pages/conversion/merci');
});
$router->get('/merci', function () {
    page('pages/conversion/merci');
});
$router->get('/estimation-internationale', function () {
    page('pages/conversion/international-valuation');
});

// ════════════════════════════════════════════════════════════
// PREUVE SOCIALE
// ════════════════════════════════════════════════════════════

$router->get('/avis', function () {
    page('pages/social-proof/avis');
});

// ════════════════════════════════════════════════════════════
// SECTEURS / ZONES
// ════════════════════════════════════════════════════════════

$router->get('/secteurs', function () {
    page('pages/secteurs/index');
});

// Alias secteurs/villes/{slug} → même logique que /immobilier/{slug}
$router->get('/secteurs/villes/{slug}', function (string $slug) {
    $canonical = normalizeVilleSlug($slug);
    if ($slug !== $canonical) {
        header('Location: ' . url('/secteurs/villes/' . rawurlencode($canonical)), true, 301);
        exit;
    }
    redirectLegacyZoneToAix($canonical, 'ville');
    $tplFile = ROOT_PATH . '/public/pages/zones/villes/' . $canonical . '.php';
    if (file_exists($tplFile)) {
        page('pages/zones/villes/' . $canonical, ['slug' => $canonical]);
    } else {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT * FROM seo_city_pages WHERE slug = ? AND status = 'published' LIMIT 1"
            );
            $stmt->execute([$canonical]);
            $cityPage = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cityPage) {
                page('pages/page', [
                    'page'      => $cityPage + ['template' => 'pages/city-price'],
                    'pageTitle' => $cityPage['seo_title'] ?? $cityPage['city_name'],
                    'metaDesc'  => $cityPage['meta_description'] ?? '',
                ]);
                return;
            }
        } catch (\Throwable $e) {
            // rien
        }
        http_response_code(404);
        page('pages/404');
    }
});

// Alias secteurs/quartiers/{slug} → même logique que /quartier/{slug}
$router->get('/secteurs/quartiers/{slug}', function (string $slug) {
    $canonical = normalizeQuartierSlug($slug);
    if ($slug !== $canonical) {
        header('Location: ' . url('/secteurs/quartiers/' . rawurlencode($canonical)), true, 301);
        exit;
    }
    redirectLegacyZoneToAix($canonical, 'quartier');
    $tplFile = ROOT_PATH . '/public/pages/zones/quartiers/' . $canonical . '.php';
    if (file_exists($tplFile)) {
        page('pages/zones/quartiers/' . $canonical, ['slug' => $canonical]);
    } else {
        http_response_code(404);
        page('pages/404');
    }
});

// Pages villes SEO : /immobilier/{slug-ville}
$router->get('/immobilier/{slug}', function (string $slug) {
    $canonical = normalizeVilleSlug($slug);
    if ($slug !== $canonical) {
        header('Location: ' . url('/immobilier/' . rawurlencode($canonical)), true, 301);
        exit;
    }
    redirectLegacyZoneToAix($canonical, 'ville');
    $tplFile = ROOT_PATH . '/public/pages/zones/villes/' . $canonical . '.php';
    if (file_exists($tplFile)) {
        page('pages/zones/villes/' . $canonical, ['slug' => $canonical]);
    } else {
        // Fallback : tenter via seo_city_pages en DB
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT * FROM seo_city_pages WHERE slug = ? AND status = 'published' LIMIT 1"
            );
            $stmt->execute([$canonical]);
            $cityPage = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cityPage) {
                page('pages/page', [
                    'page'      => $cityPage + ['template' => 'pages/city-price'],
                    'pageTitle' => $cityPage['seo_title'] ?? $cityPage['city_name'],
                    'metaDesc'  => $cityPage['meta_description'] ?? '',
                ]);
                return;
            }
        } catch (\Throwable $e) {
            // rien
        }
        http_response_code(404);
        page('pages/404');
    }
});

// Pages quartiers : /quartier/{slug}
$router->get('/quartier/{slug}', function (string $slug) {
    $canonical = normalizeQuartierSlug($slug);
    if ($slug !== $canonical) {
        header('Location: ' . url('/quartier/' . rawurlencode($canonical)), true, 301);
        exit;
    }
    redirectLegacyZoneToAix($canonical, 'quartier');
    $tplFile = ROOT_PATH . '/public/pages/zones/quartiers/' . $canonical . '.php';
    if (file_exists($tplFile)) {
        page('pages/zones/quartiers/' . $canonical, ['slug' => $canonical]);
    } else {
        http_response_code(404);
        page('pages/404');
    }
});

// ════════════════════════════════════════════════════════════
// PAGES LÉGALES
// ════════════════════════════════════════════════════════════

$router->get('/mentions-legales', function () {
    page('pages/legal/mentions-legales');
});
$router->get('/politique-confidentialite', function () {
    page('pages/legal/politique-confidentialite');
});
$router->get('/politique-cookies', function () {
    page('pages/legal/politique-cookies');
});
$router->get('/cgv', function () {
    page('pages/legal/cgv');
});

// ════════════════════════════════════════════════════════════
// Anciennes URL (un seul segment) — canonique /biens/…
// ════════════════════════════════════════════════════════════

$router->get('/appartements', function (): void {
    header('Location: /biens/appartements', true, 301);
    exit;
});
$router->get('/maisons', function (): void {
    header('Location: /biens/maisons', true, 301);
    exit;
});
$router->get('/prestige', function (): void {
    header('Location: /biens/prestige', true, 301);
    exit;
});
$router->get('/vendus', function (): void {
    header('Location: /biens/vendus', true, 301);
    exit;
});
$router->get('/biens-vendus', function (): void {
    header('Location: /biens/vendus', true, 301);
    exit;
});

// ════════════════════════════════════════════════════════════
// PAGES CMS (base de données) — fallback générique
// Lit depuis cms_pages (table principale du CMS)
// ════════════════════════════════════════════════════════════

$router->get('/{slug}', function (string $slug) {
    // Sécurité : éviter les traversals
    if (str_contains($slug, '..') || str_contains($slug, '/')) {
        http_response_code(404);
        page('pages/404');
        return;
    }

    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT * FROM cms_pages WHERE slug = ? AND status = 'published' LIMIT 1"
        );
        $stmt->execute([$slug]);
        $dbPage = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbPage) {
            page('pages/page', [
                'page'      => $dbPage,
                'pageTitle' => $dbPage['meta_title'] ?: $dbPage['title'],
                'metaDesc'  => $dbPage['meta_description'] ?? '',
            ]);
            return;
        }
    } catch (\Throwable $e) {
        error_log('[Router] DB error for slug "' . $slug . '": ' . $e->getMessage());
    }

    http_response_code(404);
    page('pages/404');
});
