<?php
$pageTitle = 'Optimiser';
$pageDescription = 'Analysez et améliorez en continu vos performances';

$allowedViews = ['index', 'analytics'];
$view = isset($_GET['view']) ? strtolower((string) $_GET['view']) : 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

function renderContent(): void
{
    global $view;

    $viewFile = __DIR__ . '/' . $view . '.php';
    if (!is_file($viewFile)) {
        $viewFile = __DIR__ . '/index.php';
    }

    require $viewFile;
}
