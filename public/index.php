<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = '/' . ltrim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}


if ($uri === '/api/estimation-instantanee' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_once ROOT_PATH . '/core/services/InstantEstimationService.php';

    $raw = file_get_contents('php://input');
    $payload = json_decode((string) $raw, true);
    if (!is_array($payload)) {
        $payload = [];
    }

    header('Content-Type: application/json; charset=utf-8');

    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) ($payload['csrf_token'] ?? ''))) {
        http_response_code(419);
        echo json_encode(['ok' => false, 'message' => 'Session expirée, rechargez la page.']);
        exit;
    }

    $result = InstantEstimationService::estimate($payload);

    InstantEstimationService::saveRequest([
        'address_input' => trim((string) ($payload['location'] ?? '')),
        'address_normalized' => trim((string) ($payload['location_normalized'] ?? '')),
        'place_id' => trim((string) ($payload['place_id'] ?? '')),
        'lat' => (float) ($payload['lat'] ?? 0),
        'lng' => (float) ($payload['lng'] ?? 0),
        'property_type' => trim((string) ($payload['property_type'] ?? '')),
        'surface' => (float) ($payload['surface'] ?? 0),
        'result_low' => $result['low'] ?? null,
        'result_med' => $result['median'] ?? null,
        'result_high' => $result['high'] ?? null,
        'comparables_count' => $result['comparables_count'] ?? 0,
        'reliability_score' => $result['reliability_score'] ?? 0,
        'status' => $result['status'] ?? 'error',
        'source' => 'instant_page',
        'metadata' => ['step' => $result['step'] ?? null],
    ]);

    if (empty($result['ok'])) {
        http_response_code(422);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
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
    '/estimation-instantanee' => 'estimation/instantanee',
    '/prendre-rendez-vous' => 'conversion/prendre-rendez-vous',
    '/mentions-legales' => 'legal/mentions-legales',
    '/politique-confidentialite' => 'legal/politique-confidentialite',
    '/politique-cookies' => 'legal/politique-cookies',
    '/cgv' => 'legal/cgv',
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
