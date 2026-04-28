<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/ProspectionEmailController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new ProspectionEmailController($GLOBALS['db']);
$action = $_GET['action'] ?? 'manual_add';

if ($action === 'manual_add') {
    $result = $controller->getIngestionService()->addManual($_POST);
    echo json_encode($result);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Action non supportée']);
