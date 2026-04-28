<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/services/LocalPartnerService.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $service = new LocalPartnerService();
    $service->ensureSchema();

    $centerLat = isset($_GET['lat']) ? (float) $_GET['lat'] : (float) setting('zone_lat', 43.529742);
    $centerLng = isset($_GET['lng']) ? (float) $_GET['lng'] : (float) setting('zone_lng', 5.447427);
    $radiusKm = isset($_GET['rayon']) ? (float) $_GET['rayon'] : (float) setting('zone_rayon_km', 10);
    $categoryId = isset($_GET['categorie_id']) && $_GET['categorie_id'] !== '' ? (int) $_GET['categorie_id'] : null;

    $items = $service->getPublicList($centerLat, $centerLng, $radiusKm, $categoryId);

    echo json_encode([
        'ok' => true,
        'center' => ['lat' => $centerLat, 'lng' => $centerLng],
        'rayon_km' => $radiusKm,
        'count' => count($items),
        'items' => $items,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Erreur lors du chargement des partenaires.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
