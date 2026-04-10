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

$places        = $payload['places'] ?? [];
$typePartenaire = trim((string) ($payload['type_partenaire'] ?? ''));

if (!is_array($places) || empty($places)) {
    echo json_encode(['success' => false, 'message' => 'Aucun partenaire sélectionné.']);
    exit;
}

$user      = Auth::user();
$websiteId = (int) ($user['website_id'] ?? 1);

// Créer la table si elle n'existe pas
db()->exec('CREATE TABLE IF NOT EXISTS partenaires (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id      INT UNSIGNED NOT NULL,
    place_id        VARCHAR(255) NOT NULL,
    nom             VARCHAR(255) NOT NULL,
    type_partenaire VARCHAR(100) NOT NULL DEFAULT "",
    adresse         TEXT NULL,
    telephone       VARCHAR(50) NULL,
    site_web        VARCHAR(255) NULL,
    rating          DECIMAL(2,1) NULL,
    nb_avis         INT UNSIGNED NOT NULL DEFAULT 0,
    lat             DECIMAL(10,7) NULL,
    lng             DECIMAL(10,7) NULL,
    notes           TEXT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_website_place (website_id, place_id),
    INDEX idx_website (website_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$apiKey = trim((string) (settings_group('api')['api_google_maps'] ?? ''));
$saved  = 0;
$skipped = 0;

foreach ($places as $p) {
    $placeId = trim((string) ($p['place_id'] ?? ''));
    if ($placeId === '') {
        continue;
    }

    // Vérifier si déjà sauvegardé
    $check = db()->prepare('SELECT id FROM partenaires WHERE website_id = :wid AND place_id = :pid');
    $check->execute([':wid' => $websiteId, ':pid' => $placeId]);
    if ($check->fetchColumn()) {
        $skipped++;
        continue;
    }

    // Récupérer les détails (téléphone + site web) via Place Details si clé dispo
    $telephone = null;
    $siteWeb   = null;
    if ($apiKey !== '') {
        $detailUrl = 'https://maps.googleapis.com/maps/api/place/details/json'
                   . '?place_id=' . urlencode($placeId)
                   . '&fields=formatted_phone_number,website'
                   . '&key=' . urlencode($apiKey)
                   . '&language=fr';
        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $raw = @file_get_contents($detailUrl, false, $ctx);
        if ($raw !== false) {
            $detail = json_decode($raw, true);
            if (($detail['status'] ?? '') === 'OK') {
                $telephone = $detail['result']['formatted_phone_number'] ?? null;
                $siteWeb   = $detail['result']['website'] ?? null;
            }
        }
    }

    $stmt = db()->prepare('INSERT INTO partenaires
        (website_id, place_id, nom, type_partenaire, adresse, telephone, site_web, rating, nb_avis, lat, lng)
        VALUES (:wid, :pid, :nom, :type, :adresse, :tel, :web, :rating, :avis, :lat, :lng)');

    $stmt->execute([
        ':wid'    => $websiteId,
        ':pid'    => $placeId,
        ':nom'    => substr(trim((string) ($p['nom'] ?? '')), 0, 255),
        ':type'   => substr($typePartenaire, 0, 100),
        ':adresse'=> (string) ($p['adresse'] ?? ''),
        ':tel'    => $telephone,
        ':web'    => $siteWeb,
        ':rating' => isset($p['rating']) ? (float) $p['rating'] : null,
        ':avis'   => (int) ($p['nb_avis'] ?? 0),
        ':lat'    => !empty($p['lat']) ? (float) $p['lat'] : null,
        ':lng'    => !empty($p['lng']) ? (float) $p['lng'] : null,
    ]);

    $saved++;
}

echo json_encode([
    'success' => true,
    'saved'   => $saved,
    'skipped' => $skipped,
    'message' => $saved > 0
        ? $saved . ' partenaire(s) sauvegardé(s).' . ($skipped > 0 ? ' ' . $skipped . ' déjà présent(s).' : '')
        : 'Tous les partenaires sélectionnés sont déjà dans votre liste.',
]);
