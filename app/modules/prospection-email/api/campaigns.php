<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/ProspectionEmailController.php';

header('Content-Type: application/json; charset=utf-8');

$controller = new ProspectionEmailController($GLOBALS['db']);
$result = $controller->createCampaign($_POST);

echo json_encode($result);
