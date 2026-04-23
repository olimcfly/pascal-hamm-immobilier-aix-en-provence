<?php

declare(strict_types=1);

/**
 * AJAX : Envoi d'un email test pour une étape de séquence.
 * POST /admin?module=prospection&ajax=send_test
 *
 * Paramètres POST :
 *   csrf_token    — obligatoire
 *   campaign_id   — int
 *   step_id       — int
 *   preview_first_name, preview_last_name, preview_email (optionnels)
 */

require_once ROOT_PATH . '/core/bootstrap.php';
require_once ROOT_PATH . '/core/services/ModuleService.php';

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
$stepId     = (int)($_POST['step_id']     ?? 0);

if ($campaignId <= 0 || $stepId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Paramètres manquants.']);
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

$previewContact = [];
if (!empty($_POST['preview_email'])) {
    $previewContact['email']      = filter_var(trim($_POST['preview_email']), FILTER_SANITIZE_EMAIL);
    $previewContact['first_name'] = htmlspecialchars(trim($_POST['preview_first_name'] ?? 'Jean'),  ENT_QUOTES);
    $previewContact['last_name']  = htmlspecialchars(trim($_POST['preview_last_name']  ?? 'Dupont'), ENT_QUOTES);
}

$result = $seqSvc->sendTestEmail($campaignId, $stepId, $previewContact);

echo json_encode([
    'ok'      => $result['ok'],
    'mode'    => $result['mode']    ?? 'unknown',
    'sent_to' => $result['sent_to'] ?? '',
    'subject' => $result['subject'] ?? '',
    'body'    => $result['body']    ?? '',
    'log_id'  => $result['log_id']  ?? 0,
    'error'   => $result['error']   ?? null,
]);
