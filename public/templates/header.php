<?php
// public/templates/header.php

// Définir la variable $stylesToInclude si elle n'existe pas
$stylesToInclude = $stylesToInclude ?? [];
$extraJs = $extraJs ?? [];

// Définir la fonction isActive()
if (!function_exists('isActive')) {
    function isActive($path) {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $path ||
               strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $path) === 0;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$advisorName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
if ($advisorName === '') {
    $advisorName = defined('ADVISOR_NAME') ? ADVISOR_NAME : (defined('APP_NAME') ? APP_NAME : 'Pascal Hamm');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($metaKeywords ?? 'mots-clés, par, défaut') ?>">

    <!-- Balises Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Titre par défaut') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDesc ?? 'Description par défaut') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">

    <!-- Inclure les styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php foreach ($stylesToInclude as $cssFile): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($cssFile) ?>">
    <?php endforeach; ?>

    <!-- Inclure les scripts JavaScript -->
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $jsFile): ?>
            <script src="<?= htmlspecialchars($jsFile) ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="site-header" id="site-header">
        <div class="container header__inner">

            <!-- Logo -->
            <a href="<?= htmlspecialchars(url('/')) ?>" class="header__logo" aria-label="<?= htmlspecialchars($advisorName) ?> — Accueil">
                <span class="logo__icon">🏡</span>
                <span class="logo__text">
                    <strong><?= htmlspecialchars($advisorName) ?></strong>
                    <em>Immobilier</em>
                </span>
            </a>

            <!-- Navigation principale -->
            <?php require __DIR__ . '/nav.php'; ?>

            <!-- CTA header -->
            <div class="header__actions">
                <a href="<?= htmlspecialchars(url('/estimation-gratuite')) ?>" class="btn btn--primary btn--header-cta">Estimation gratuite</a>
            </div>

            <!-- Burger mobile -->
            <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-mobile">
                <span></span><span></span><span></span>
            </button>

        </div>
    </header>

    <!-- Début du contenu principal -->
    <main>
