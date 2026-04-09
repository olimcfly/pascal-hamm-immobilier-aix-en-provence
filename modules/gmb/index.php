<?php
require_once __DIR__ . '/../../core/bootstrap.php';
?>

<div class="gmb-dashboard gmb-hub">
    <header class="gmb-hero">
        <p class="gmb-hero-kicker">Hub Google</p>
        <h1>Générer des contacts avec Google</h1>
        <p class="gmb-hero-subtitle">Optimisez votre fiche Google pour recevoir des appels et des demandes automatiquement.</p>
    </header>

    <section class="gmb-panel gmb-mere-block" aria-labelledby="mere-title">
        <h2 id="mere-title">MERE</h2>
        <div class="gmb-mere-grid">
            <article class="gmb-mere-item">
                <h3>Motivation</h3>
                <p>Visibilité locale = leads</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Explication</h3>
                <p>3 piliers : fiche, avis, contenu</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Résultat</h3>
                <p>Appels + leads</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Action</h3>
                <p>Optimiser fiche</p>
            </article>
        </div>
    </section>

    <section aria-labelledby="gmb-modules-title">
        <h2 id="gmb-modules-title" class="gmb-section-title">Les 5 leviers à activer</h2>
        <div class="gmb-cards-grid">
            <a class="gmb-card" href="/admin?module=gmb&view=fiche">
                <p class="gmb-card-index">1</p>
                <h3>Optimiser votre fiche</h3>
                <p class="gmb-card-module">Module : fiche GMB</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=avis">
                <p class="gmb-card-index">2</p>
                <h3>Gérer vos avis</h3>
                <p class="gmb-card-module">Module : avis clients</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=demande-avis">
                <p class="gmb-card-index">3</p>
                <h3>Demander des avis</h3>
                <p class="gmb-card-module">Module : automatisation</p>
            </a>
            <a class="gmb-card" href="/admin?module=redaction&action=pool_gmb">
                <p class="gmb-card-index">4</p>
                <h3>Publier du contenu</h3>
                <p class="gmb-card-module">Module : posts GMB</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=statistiques">
                <p class="gmb-card-index">5</p>
                <h3>Suivre vos performances</h3>
                <p class="gmb-card-module">Module : stats</p>
            </a>
        </div>
    </section>

    <section class="gmb-panel gmb-final-cta" aria-labelledby="gmb-cta-title">
        <h2 id="gmb-cta-title">Améliorez votre visibilité locale</h2>
        <a class="btn-gmb" href="/admin?module=gmb&view=fiche">Optimiser ma fiche Google</a>
    </section>
</div>
