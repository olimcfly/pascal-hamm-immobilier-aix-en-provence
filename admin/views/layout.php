<?php
if (!defined('IMMO_LOCAL')) { die('Accès direct interdit'); }

// --- Contexte utilisateur ---
$user = Auth::user() ?? [];
$advisorFirst = (string) setting('advisor_firstname', '');
$advisorLast  = (string) setting('advisor_lastname', '');
$advisorDisplayName = trim($advisorFirst . ' ' . $advisorLast)
    ?: (defined('ADVISOR_NAME') ? ADVISOR_NAME : (defined('APP_NAME') ? APP_NAME : 'IMMO LOCAL+'));

// --- Contexte module / aide ---
$module = $module ?? ($_GET['module'] ?? 'dashboard');
$helpContext = preg_replace('/[^a-z0-9_-]/', '', (string) $module);
$helpLink = '/admin?module=aide&context=' . rawurlencode($helpContext);

// --- Titre de page ---
$pageTitle = $pageTitle ?? ucfirst($module);
$appName = defined('APP_NAME') ? APP_NAME : 'IMMO LOCAL+';

// --- CSS spécifique au module ---
$moduleCssMap = [
    'dashboard' => '/admin/assets/css/dashboard.css',
    'seo'       => '/admin/assets/css/seo.css',
    'gmb'       => '/admin/assets/css/gmb.css',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars($appName) ?></title>

    <link rel="icon" type="image/png" href="/favicon.png">

    <!-- Font Awesome (icônes topbar/sidebar) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Feuilles de style admin (chemins corrigés : /admin/assets/css/) -->
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link rel="stylesheet" href="/admin/assets/css/hub-unified.css">
    <link rel="stylesheet" href="/admin/assets/css/settings.css">

    <?php if (isset($moduleCssMap[$module])): ?>
        <link rel="stylesheet" href="<?= $moduleCssMap[$module] ?>">
    <?php endif; ?>
</head>
<body class="admin-body module-<?= htmlspecialchars($module) ?>">
<div class="layout">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>

    <div class="layout-body">
        <?php require_once __DIR__ . '/partials/header.php'; ?>

        <main class="main-content">
            <?php
            if (!empty($content)) {
                echo $content;
            } elseif (!empty($contentFile) && is_file($contentFile)) {
                require $contentFile;
            } elseif (function_exists('renderContent')) {
                renderContent();
            } else {
                echo '<div class="alert alert-warning">Aucun contenu pour le module <strong>'
                    . htmlspecialchars($module) . '</strong>.</div>';
            }
            ?>
        </main>

        <?php require_once __DIR__ . '/partials/footer.php'; ?>
    </div>
</div>

<!-- Scripts -->
<script src="/admin/assets/js/app.js" defer></script>
</body>
</html>
