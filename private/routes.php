<?php
// ============================================================
// ROUTES FRONT-END — Pascal Hamm Immobilier
// ============================================================

/** @var Router $router */

require_once ROOT_PATH . '/admin/modules/cms/services/CmsService.php';
require_once ROOT_PATH . '/admin/modules/cms/controllers/PageController.php';

// Fonction utilitaire pour les guides
if (!function_exists('resourceGuidesData')) {
    function resourceGuidesData(): array
    {
        static $guides = null;
        if ($guides === null) {
            $guides = [
                'acheteur' => [
                    'slug' => 'acheteur',
                    'title' => 'Guide de l\'acheteur immobilier à Aix-en-Provence',
                    'description' => 'Tout savoir pour acheter sereinement dans le Pays d\'Aix avec Pascal Hamm',
                    'pages' => [
                        'preparer-projet' => 'Préparer son projet',
                        'trouver-bien' => 'Trouver le bien idéal',
                        'negocier' => 'Négocier comme un pro'
                    ]
                ],
                'vendeur' => [
                    'slug' => 'vendeur',
                    'title' => 'Guide du vendeur immobilier',
                    'description' => 'Vendre au meilleur prix avec l\'expertise de Pascal Hamm',
                    'pages' => [
                        'estimer-bien' => 'Estimer son bien',
                        'preparer-vente' => 'Préparer la vente',
                        'choisir-agence' => 'Choisir son expert'
                    ]
                ]
            ];
        }
        return $guides;
    }
}

// Fonction pour trouver un guide
function findResourceGuide(string $persona, string $slug): ?array
{
    $guides = resourceGuidesData();
    if (!isset($guides[$persona]['pages'][$slug])) {
        return null;
    }
    return [
        'persona' => $persona,
        'slug' => $slug,
        'title' => $guides[$persona]['pages'][$slug],
        'guideTitle' => $guides[$persona]['title'],
        'guideDescription' => $guides[$persona]['description']
    ];
}

// Fonction pour capturer les leads
function captureGuideLead(array $guideContext): void
{
    $leadData = [
        'type' => 'guide_' . $guideContext['persona'],
        'source' => 'guide_' . $guideContext['slug'],
        'data' => json_encode([
            'guide' => $guideContext['title'],
            'persona' => $guideContext['persona']
        ])
    ];
    LeadService::capture($leadData);
    header('Location: /merci');
    exit;
}

// Routes principales
$router->get('/', fn() => page('pages/home'), 'home');
$router->get('/a-propos', fn() => page('pages/about'), 'about');
$router->get('/services', fn() => page('pages/services'), 'services');
$router->get('/contact', fn() => page('pages/contact'), 'contact');
$router->post('/contact', fn() => page('pages/contact'), 'contact.post');
$router->get('/estimation', fn() => page('pages/estimation'), 'estimation');
$router->post('/estimation', fn() => page('pages/estimation'), 'estimation.post');
$router->get('/avis', fn() => page('pages/avis'), 'avis');

// Biens immobiliers
$router->get('/biens', fn() => page('pages/biens'), 'biens');
$router->get('/biens/{slug}', fn($slug) => page('pages/biens', ['slug' => $slug]), 'bien.detail');

// Blog
$router->get('/blog', fn() => page('blog/index'), 'blog');
$router->get('/blog/{slug}', fn($slug) => page('blog/article', ['slug' => $slug]), 'blog.article');

// Actualités
$router->get('/actualites', fn() => page('actualites/index'), 'actualites');
$router->get('/actualites/{slug}', fn($slug) => page('actualites/article', ['slug' => $slug]), 'actualite.article');

// Zones géographiques (villes et quartiers)
$router->get('/immobilier/{ville}', function($ville) {
    $file = ROOT_PATH . '/public/pages/zones/villes/' . sanitizeFilename($ville) . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        page('pages/404');
        return;
    }
    page('pages/zones/villes/' . sanitizeFilename($ville));
}, 'zone.ville');

$router->get('/quartier/{quartier}', function($quartier) {
    $file = ROOT_PATH . '/public/pages/zones/quartiers/' . sanitizeFilename($quartier) . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        page('pages/404');
        return;
    }
    page('pages/zones/quartiers/' . sanitizeFilename($quartier));
}, 'zone.quartier');

// Financement
$router->get('/financement', fn() => page('financement/financement'), 'financement');
$router->get('/financement/acheter-avant-vendre', fn() => page('financement/financement'), 'financement.acheter-avant-vendre');
$router->get('/viager', fn() => page('pages/services/viager'), 'viager');

// Ressources
$router->get('/ressources', fn() => page('ressources/index'), 'ressources');
$router->get('/ressources/guide-vendeur', fn() => page('ressources/guide-vendeur'), 'guide-vendeur');
$router->get('/ressources/guide-acheteur', fn() => page('ressources/guide-acheteur'), 'guide-acheteur');
$router->get('/ressources/guides/{persona}/{slug}', function ($persona, $slug) {
    $guideContext = findResourceGuide((string) $persona, (string) $slug);
    if (!$guideContext) {
        http_response_code(404);
        echo '<h1>404 — Guide introuvable</h1>';
        return;
    }
    page('ressources/guide', ['guideContext' => $guideContext]);
}, 'ressources.guide');
$router->post('/ressources/guides/{persona}/{slug}', function ($persona, $slug) {
    $guideContext = findResourceGuide((string) $persona, (string) $slug);
    if (!$guideContext) {
        http_response_code(404);
        echo '<h1>404 — Guide introuvable</h1>';
        return;
    }
    captureGuideLead($guideContext);
}, 'ressources.guide.post');

// Capture de leads
$router->get('/estimation-gratuite', fn() => page('capture/estimation-gratuite'), 'capture.estimation');
$router->post('/estimation-gratuite', fn() => page('capture/estimation-gratuite'), 'capture.estimation.post');
$router->get('/guide-offert', fn() => page('capture/guide-offert'), 'capture.guide');
$router->post('/guide-offert', fn() => page('capture/guide-offert'), 'capture.guide.post');
$router->get('/merci', fn() => page('capture/merci'), 'merci');

// Pages légales
$router->get('/mentions-legales', fn() => page('legal/mentions-legales'), 'mentions-legales');
$router->get('/politique-confidentialite', fn() => page('legal/politique-confidentialite'), 'politique-confidentialite');
$router->get('/politique-cookies', fn() => page('legal/politique-cookies'), 'politique-cookies');
$router->get('/plan-du-site', fn() => page('legal/plan-du-site'), 'plan-du-site');
$router->get('/cgv', fn() => page('legal/cgv'), 'cgv');

// CMS Admin
$router->get('/admin/cms/edit/{page_slug}', [\Admin\Modules\Cms\Controllers\PageController::class, 'edit'], 'admin.cms.edit');
$router->post('/admin/cms/save', [\Admin\Modules\Cms\Controllers\PageController::class, 'save'], 'admin.cms.save');
