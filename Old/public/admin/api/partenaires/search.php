<?php
declare(strict_types=1);
ob_start();
require_once __DIR__ . '/../../../../core/bootstrap.php';
ob_clean();

set_exception_handler(function (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    exit;
});

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Payload invalide.']);
    exit;
}

if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), (string) ($payload['csrf_token'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expirée, rechargez la page.']);
    exit;
}

$ville      = trim((string) ($payload['ville']      ?? ''));
$partenaire = trim((string) ($payload['partenaire'] ?? ''));
$rayon      = max(1, min(50, (int) ($payload['rayon'] ?? 10)));

if ($ville === '' || $partenaire === '') {
    echo json_encode(['success' => false, 'message' => 'Ville et type de partenaire requis.']);
    exit;
}

// Clé API Google Maps (Places)
$apiKey = trim((string) (settings_group('api')['api_google_maps'] ?? ''));
if ($apiKey === '') {
    echo json_encode(['success' => false, 'message' => 'Clé API Google Maps non configurée. Allez dans Paramètres → API.']);
    exit;
}

// Récupérer les partenaires déjà sauvegardés (pour badge "Déjà sauvegardé")
$user      = Auth::user();
$websiteId = (int) ($user['website_id'] ?? 1);
$savedIds  = [];
try {
    $stmt = db()->prepare('SELECT place_id FROM partenaires WHERE website_id = :wid');
    $stmt->execute([':wid' => $websiteId]);
    $savedIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'place_id');
} catch (Throwable) {
    // Table pas encore créée — ignoré
}

// Appel Google Places Text Search (max 20 résultats)
$query = urlencode($partenaire . ' ' . $ville);
$url   = 'https://maps.googleapis.com/maps/api/place/textsearch/json'
       . '?query=' . $query
       . '&key=' . urlencode($apiKey)
       . '&language=fr'
       . '&region=fr';

$ctx  = stream_context_create(['http' => ['timeout' => 10]]);
$raw  = @file_get_contents($url, false, $ctx);

if ($raw === false) {
    echo json_encode(['success' => false, 'message' => 'Impossible de contacter l\'API Google Places.']);
    exit;
}

$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Réponse Google invalide.']);
    exit;
}

$status = $data['status'] ?? 'UNKNOWN';
if (!in_array($status, ['OK', 'ZERO_RESULTS'], true)) {
    $errMsg = $data['error_message'] ?? $status;
    echo json_encode(['success' => false, 'message' => 'Google Places : ' . $errMsg]);
    exit;
}

$results = array_slice($data['results'] ?? [], 0, 20);

$places = [];
foreach ($results as $r) {
    $placeId = (string) ($r['place_id'] ?? '');
    $places[] = [
        'place_id'       => $placeId,
        'nom'            => (string) ($r['name'] ?? ''),
        'adresse'        => (string) ($r['formatted_address'] ?? ''),
        'rating'         => isset($r['rating']) ? (float) $r['rating'] : null,
        'nb_avis'        => (int) ($r['user_ratings_total'] ?? 0),
        'ouvert'         => $r['opening_hours']['open_now'] ?? null,
        'photo_ref'      => $r['photos'][0]['photo_reference'] ?? null,
        'types'          => $r['types'] ?? [],
        'lat'            => (float) ($r['geometry']['location']['lat'] ?? 0),
        'lng'            => (float) ($r['geometry']['location']['lng'] ?? 0),
        'already_saved'  => in_array($placeId, $savedIds, true),
    ];
}

echo json_encode([
    'success' => true,
    'count'   => count($places),
    'places'  => $places,
    'api_key' => substr($apiKey, 0, 8) . '…',
]);
