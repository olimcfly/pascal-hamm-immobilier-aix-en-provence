<?php

declare(strict_types=1);

/**
 * AJAX : Lance la simulation complète du cycle d'une campagne.
 * POST /admin?module=prospection&ajax=simulate
 *
 * Paramètres POST :
 *   csrf_token   — obligatoire
 *   campaign_id  — int
 */

require_once ROOT_PATH . '/core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non authentifié.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Token CSRF invalide.']);
    exit;
}

$campaignId = (int)($_POST['campaign_id'] ?? 0);
if ($campaignId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'campaign_id manquant.']);
    exit;
}

require_once MODULES_PATH . '/prospection/repositories/ProspectRepository.php';
require_once MODULES_PATH . '/prospection/repositories/CampaignRepository.php';
require_once MODULES_PATH . '/prospection/repositories/SequenceRepository.php';
require_once MODULES_PATH . '/prospection/services/ProspectionMailer.php';
require_once MODULES_PATH . '/prospection/services/SequenceService.php';

$userId  = (int)(Auth::user()['id'] ?? 0);
$db      = \Database::getInstance();
$seqRepo = new SequenceRepository($db);
$camRepo = new CampaignRepository($db);
$seqSvc  = new SequenceService($seqRepo, $camRepo, $userId);

$result = $seqSvc->simulateFullCycle($campaignId);

echo json_encode([
    'ok'       => true,
    'total'    => $result['total'],
    'scenarios'=> $result['scenarios'],
    'mode'     => ProspectionMailer::currentMode(),
]);
