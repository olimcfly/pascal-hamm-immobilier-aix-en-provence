<?php
// public/templates/header.php

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$advisorName = trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''));
if ($advisorName === '') {
    $advisorName = ADVISOR_NAME ?: APP_NAME;
}

function isActive(string $path, string $current): string {
    return ($path !== '/' ? str_starts_with($current, $path) : $current === '/') ? 'active' : '';
}

// Définir les styles par défaut et spécifiques à la page
$defaultStyles = [
    '/assets/css/style.css',
    '/assets/css/main.css',
    '/assets/css/biens.css',
    '/assets/css/secteurs.css',
    '/assets/css/home.css',
    '/assets/css/contact.css',
    '/assets/css/estimation.css',
    '/assets/css/guide.css'
];

// Styles spécifiques à certaines pages
$pageSpecificStyles = [
    '/biens' => '/assets/css/biens-specific.css',
    '/contact' => '/assets/css/contact-specific.css',
    '/estimation-gratuite' => '/assets/css/estimation-specific.css',
    '/guide' => '/assets/css/guide-specific.css',
    '/secteurs' => '/assets/css/secteurs-specific.css',
    '/biens/vendus' => '/assets/css/biens-vendus-specific.css', // Ajoutez cette ligne
];


// Déterminer les styles à inclure
$stylesToInclude = array_merge(
    $defaultStyles,
    isset($pageSpecificStyles[$currentUri]) ? [$pageSpecificStyles[$currentUri]] : []
);

// Fusionner avec les styles supplémentaires si nécessaire
$stylesToInclude = array_merge(
    $stylesToInclude,
    !empty($extraCss) ? $extraCss : []
);
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

    <!-- Contenu principal de la page -->
    <main>
        <?php
        // Vérifier si $template est défini et si le fichier existe
        if (isset($template) && !empty($template)) {
            $templatePath = __DIR__ . '/../../templates/' . $template . '.php';

            if (file_exists($templatePath)) {
                require $templatePath;
            } else {
                // Afficher un message d'erreur ou un contenu par défaut si le template n'existe pas
                echo '<div class="container"><p>Le template spécifié n\'a pas été trouvé.</p></div>';
            }
        } else {
            // Afficher un contenu par défaut si aucun template n'est spécifié
            echo '<div class="container"><p>Contenu par défaut de la page.</p></div>';
        }
        ?>
    </main>

    <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
