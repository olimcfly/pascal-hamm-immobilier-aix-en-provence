<?php
$pageTitle = 'Google My Business';
$pageDescription = 'Pilotez votre fiche, vos avis et vos performances locales.';

$allowedViews = ['index', 'fiche', 'avis', 'demande-avis', 'statistiques'];
$view = $_GET['view'] ?? 'index';
if (!in_array($view, $allowedViews, true)) {
    $view = 'index';
}

function gmbAssetVersion(string $absolutePath): int
{
    return is_file($absolutePath) ? (int) filemtime($absolutePath) : 1;
}

function gmbViewMeta(string $view): array
{
    return match ($view) {
        'avis'       => ['icon' => 'fa-star', 'badge' => 'Google My Business', 'title' => 'Avis clients', 'sub' => 'Gérez et répondez aux avis Google de votre fiche GMB.'],
        'demande-avis' => ['icon' => 'fa-envelope-open-text', 'badge' => 'Google My Business', 'title' => 'Demande d\'avis', 'sub' => 'Envoyez des demandes d\'avis à vos clients par email ou SMS.'],
        'statistiques' => ['icon' => 'fa-chart-bar', 'badge' => 'Google My Business', 'title' => 'Statistiques GMB', 'sub' => 'Suivez impressions, clics et appels de votre fiche Google.'],
        'fiche'      => ['icon' => 'fa-store', 'badge' => 'Google My Business', 'title' => 'Ma fiche GMB', 'sub' => 'Mettez à jour les informations de votre fiche Google My Business.'],
        default      => ['icon' => 'fa-map-location-dot', 'badge' => 'Google My Business', 'title' => 'Google My Business', 'sub' => 'Pilotez votre fiche, vos avis et vos performances locales.'],
    };
}

function renderContent(): void
{
    global $view;
    $viewFile = ($view === 'index') ? __DIR__ . '/_hub.php' : __DIR__ . '/' . $view . '.php';

    $publicCssPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/gmb.css';
    $publicJsPath  = $_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/gmb.js';

    echo '<link rel="stylesheet" href="/admin/assets/css/gmb.css?v=' . gmbAssetVersion($publicCssPath) . '">';

    $meta = gmbViewMeta($view);
    ?>
    <style>
    .gmb-hub-nav{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.2rem}
    .gmb-hub-nav a{padding:.38rem .85rem;border-radius:999px;text-decoration:none;font-size:.84rem;font-weight:600;border:1.5px solid var(--hub-border,#e2e8f0);color:#475569;background:#fff;transition:.15s}
    .gmb-hub-nav a.active,.gmb-hub-nav a:hover{background:var(--hub-navy,#0f2237);color:#fff;border-color:var(--hub-navy,#0f2237)}
    </style>
    <div class="hub-page">
        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas <?= $meta['icon'] ?>"></i> <?= $meta['badge'] ?></div>
            <h1><?= $meta['title'] ?></h1>
            <p><?= $meta['sub'] ?></p>
        </header>

        <nav class="gmb-hub-nav">
            <a href="/admin?module=gmb" <?= $view === 'index' ? 'class="active"' : '' ?>>Vue d'ensemble</a>
            <a href="/admin?module=gmb&view=fiche" <?= $view === 'fiche' ? 'class="active"' : '' ?>>Ma fiche</a>
            <a href="/admin?module=gmb&view=avis" <?= $view === 'avis' ? 'class="active"' : '' ?>>Avis</a>
            <a href="/admin?module=gmb&view=demande-avis" <?= $view === 'demande-avis' ? 'class="active"' : '' ?>>Demande d'avis</a>
            <a href="/admin?module=gmb&view=statistiques" <?= $view === 'statistiques' ? 'class="active"' : '' ?>>Statistiques</a>
        </nav>

        <?php
        if (is_file($viewFile)) {
            require $viewFile;
        } else {
            require __DIR__ . '/_hub.php';
        }
        ?>
    </div><!-- /.hub-page -->
    <?php

    echo '<script src="/admin/assets/js/gmb.js?v=' . gmbAssetVersion($publicJsPath) . '"></script>';
}
