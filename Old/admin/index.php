<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

Auth::requireAuth('/admin/login');

$module = preg_replace('/[^a-z0-9_-]/', '', (string) ($_GET['module'] ?? 'dashboard'));
if ($module === '') {
    $module = 'dashboard';
}

$moduleAliases = [
    'capturer' => 'capture',
    'assistant_ia' => 'assistant',
];

if (isset($moduleAliases[$module])) {
    $target = '/admin?module=' . rawurlencode($moduleAliases[$module]);
    if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str((string) $_SERVER['QUERY_STRING'], $query);
        unset($query['module']);
        if ($query !== []) {
            $target .= '&' . http_build_query($query);
        }
    }
    header('Location: ' . $target, true, 302);
    exit;
}

$contentFile = __DIR__ . '/modules/' . $module . '/index.php';
if (!is_file($contentFile)) {
    http_response_code(404);
    $module = 'dashboard';
    $pageTitle = 'Module introuvable';
    $content = '<div class="card"><h2>Module introuvable</h2><p>Le module demandé n\'existe pas.</p></div>';
    require __DIR__ . '/views/layout.php';
    exit;
}

require __DIR__ . '/views/layout.php';
