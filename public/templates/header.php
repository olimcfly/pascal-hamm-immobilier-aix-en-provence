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
    $advisorName = ADVISOR_NAME ?: APP_NAME;
}
?>

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
