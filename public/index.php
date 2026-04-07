<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

ob_start();
require ROOT_PATH . '/public/pages/core/home.php';
$pageContent = ob_get_clean();

require ROOT_PATH . '/public/templates/layout.php';
