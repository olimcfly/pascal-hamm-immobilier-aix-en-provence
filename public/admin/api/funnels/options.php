<?php
/**
 * API — Options pour les selects du wizard funnels
 * Retourne les séquences CRM et les ressources disponibles.
 * GET /admin/api/funnels/options.php
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__, 4));
require_once ROOT_PATH . '/core/bootstrap.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$db = db();

// ── Séquences CRM ─────────────────────────────────────────────
$sequences = [];
try {
    $stmt = $db->query("SELECT id, name, status FROM crm_sequences WHERE status = 'active' ORDER BY name");
    $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable) {}

// ── Ressources (guides PDF) ───────────────────────────────────
$ressources = [];
try {
    $stmt = $db->query("SELECT id, title FROM ressources WHERE status = 'published' ORDER BY title");
    $ressources = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable) {}

echo json_encode([
    'ok'         => true,
    'sequences'  => $sequences,
    'ressources' => $ressources,
]);
