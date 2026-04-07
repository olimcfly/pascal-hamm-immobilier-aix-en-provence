<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

if (!class_exists('Router')) {
    die('La classe Router n\'est pas définie.');
}

$router = new Router();

if (!class_exists('ZoneController')) {
    die('La classe ZoneController n\'est pas définie.');
}

// Helper pour charger une page PHP
function loadPage(string $file): string {
    ob_start();
    require $file;
    return ob_get_clean();
}

// Accueil
$router->get('/', function() {
    $pageFile = ROOT_PATH . '/public/pages/core/home.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});
// Biens
$router->get('/biens', function() {
    $pageFile = ROOT_PATH . '/public/pages/biens/index.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Bien individuel
$router->get('/biens/{slug}', function(string $slug) {
    $pageFile = ROOT_PATH . '/public/pages/biens/' . $slug . '.php';
    if (!is_file($pageFile)) {
        http_response_code(404);
        $pageTitle   = 'Bien introuvable';
        $pageContent = '<section class="section"><div class="container"><h1>404</h1><p>Ce bien est introuvable.</p></div></section>';
    } else {
        ob_start();
        require $pageFile;
        $pageContent = ob_get_clean();
    }
    require ROOT_PATH . '/public/templates/layout.php';
});

// À propos
$router->get('/a-propos', function() {
    $pageFile = ROOT_PATH . '/public/pages/core/a-propos.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Contact
$router->get('/contact', function() {
    $pageFile = ROOT_PATH . '/public/pages/core/contact.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Mentions légales
$router->get('/mentions-legales', function() {
    $pageFile = ROOT_PATH . '/public/pages/legal/mentions-legales.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Politique de confidentialité
$router->get('/politique-confidentialite', function() {
    $pageFile = ROOT_PATH . '/public/pages/legal/politique-confidentialite.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// CGV
$router->get('/cgv', function() {
    $pageFile = ROOT_PATH . '/public/pages/legal/cgv.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Plan du site
$router->get('/plan-du-site', function() {
    $pageFile = ROOT_PATH . '/public/pages/legal/plan-du-site.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Estimation
$router->get('/estimation', function() {
    $pageFile = ROOT_PATH . '/public/pages/estimation/estimation-gratuite.php';
    ob_start();
    require $pageFile;
    $pageContent = ob_get_clean();
    require ROOT_PATH . '/public/templates/layout.php';
});

// Services
$router->get('/services/{slug}', function(string $slug) {
    $pageFile = ROOT_PATH . '/public/pages/services/' . $slug . '.php';
    if (!is_file($pageFile)) {
        http_response_code(404);
        $pageTitle   = 'Page introuvable';
        $pageContent = '<section class="section"><div class="container"><h1>404</h1><p>Service introuvable.</p></div></section>';
    } else {
        ob_start();
        require $pageFile;
        $pageContent = ob_get_clean();
    }
    require ROOT_PATH . '/public/templates/layout.php';
});

// Secteurs
$router->get('/secteurs/{type}/{slug}', 'ZoneController@show');

// 404
$router->set404(function() {
    http_response_code(404);
    $pageTitle   = 'Page introuvable';
    $pageContent = '<section class="section"><div class="container"><h1>404</h1><p>La page demandée est introuvable.</p></div></section>';
    require ROOT_PATH . '/public/templates/layout.php';
});

$router->dispatch();
