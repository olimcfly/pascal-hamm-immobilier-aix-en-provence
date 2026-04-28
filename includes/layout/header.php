<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/topbar.php';

// Mode fragment : pas de second `<html>` / `<body>` (le gabarit layout.php fournit la coquille complète)
$siteHeaderEmbedOnly = true;
require_once ROOT_PATH . '/public/templates/header.php';
