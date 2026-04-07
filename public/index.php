<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . ltrim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

// Permet au serveur PHP intégré de servir les fichiers statiques (CSS/JS/images).
if (PHP_SAPI === 'cli-server') {
    $staticPath = realpath(__DIR__ . $uri);
    $publicRoot = realpath(__DIR__);
    if ($staticPath && $publicRoot && str_starts_with($staticPath, $publicRoot) && is_file($staticPath)) {
        return false;
    }
}

$routeToPage = [
    '/' => 'core/home',
    '/a-propos' => 'core/a-propos',
    '/contact' => 'core/contact',
    '/services' => 'services/services',
    '/viager' => 'services/services',
    '/biens' => 'biens/index',
    '/acheter' => 'ressources/guide-acheteur',
    '/vendre' => 'ressources/guide-vendeur',
    '/secteurs' => 'guide-local/index',
    '/guide-local' => 'guide-local/index',
    '/ressources' => 'ressources/index',
    '/ressources/guide-acheteur' => 'ressources/guide-acheteur',
    '/ressources/guide-vendeur' => 'ressources/guide-vendeur',
    '/blog' => 'blog/index',
    '/actualites' => 'actualites/index',
    '/avis' => 'social-proof/avis',
    '/avis-clients' => 'social-proof/avis',
    '/estimation-gratuite' => 'capture/estimation-gratuite',
    '/merci' => 'conversion/merci',
    '/merci-estimation' => 'capture/merci',
    '/mentions-legales' => 'legal/mentions-legales',
    '/politique-confidentialite' => 'legal/politique-confidentialite',
    '/politique-cookies' => 'legal/politique-cookies',
    '/cgv' => 'legal/cgv',
    '/plan-du-site' => 'core/plan-du-site',
];

$pageKey = $routeToPage[$uri] ?? null;
if ($pageKey === null) {
    http_response_code(404);
    $pageTitle = 'Page introuvable';
    $pageContent = '<section class="section"><div class="container"><h1>404</h1><p>La page demandée est introuvable.</p></div></section>';
    require ROOT_PATH . '/public/templates/layout.php';
    exit;
}

$pageFile = ROOT_PATH . '/public/pages/' . $pageKey . '.php';
if (!is_file($pageFile)) {
    http_response_code(500);
    $pageTitle = 'Erreur interne';
    $pageContent = '<section class="section"><div class="container"><h1>500</h1><p>Le fichier de page est introuvable.</p></div></section>';
    require ROOT_PATH . '/public/templates/layout.php';
    exit;
}

ob_start();
require $pageFile;
$pageContent = ob_get_clean();

require ROOT_PATH . '/public/templates/layout.php';
