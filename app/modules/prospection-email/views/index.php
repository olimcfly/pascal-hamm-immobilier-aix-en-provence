<?php /** @var string $pageTitle */ ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Prospection Email B2B', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/app/modules/prospection-email/views/prospection-email.css">
</head>
<body>
<main class="prospection-layout">
    <header class="hero-card">
        <h1>CRM Prospection Email B2B</h1>
        <p>Transformez des contacts multi-sources en campagnes structurées, pilotées et conversationnelles.</p>
    </header>

    <section class="mere-grid">
        <article class="mere-card">
            <h2>Motivation</h2>
            <p>Centraliser les leads manuels, CSV et scraping pour éviter les envois dispersés et non traçables.</p>
        </article>
        <article class="mere-card">
            <h2>Explication</h2>
            <p>Le module sépare collecte, validation, segmentation, séquences et conversations pour un process propre.</p>
        </article>
        <article class="mere-card">
            <h2>Méthode</h2>
            <p>1) Zone tampon scraping → 2) Validation → 3) Campagne segmentée → 4) Séquence multi-étapes.</p>
        </article>
        <article class="mere-card">
            <h2>Exercice</h2>
            <p>Créez une campagne test de 20 contacts validés, séquence en 3 étapes, arrêt auto sur réponse.</p>
        </article>
    </section>

    <section class="panel">
        <h2>Pipeline produit</h2>
        <ol>
            <li>Base contacts unifiée (manuel / CSV / scraping)</li>
            <li>Zone tampon scraping + aperçu + anti-doublons</li>
            <li>Validation manuelle avec statuts métier</li>
            <li>Campagnes segmentées sur contacts validés</li>
            <li>Séquences email multi-étapes avec variables dynamiques</li>
            <li>Conversations centralisées avec arrêt automatique</li>
        </ol>
    </section>
</main>
</body>
</html>
