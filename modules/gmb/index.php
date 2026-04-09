<?php
require_once __DIR__ . '/../../core/bootstrap.php';
?>

<div class="gmb-dashboard gmb-hub">
    <header class="gmb-hero">
        <h1>Recevez plus d'appels depuis Google</h1>
        <p class="gmb-hero-subtitle">Activez votre présence locale pour convertir les recherches en rendez-vous.</p>
    </header>

    <section class="gmb-panel gmb-mere-block" aria-labelledby="mere-title">
        <h2 id="mere-title">Méthode</h2>
        <div class="gmb-mere-grid">
            <article class="gmb-mere-item">
                <h3>Problème utilisateur</h3>
                <p>Votre fiche locale ne transforme pas assez de vues en contacts.</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Logique simple</h3>
                <p>Profil complet, avis réguliers, publications utiles.</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Bénéfice clair</h3>
                <p>Vous gagnez en confiance locale et en demandes entrantes.</p>
            </article>
            <article class="gmb-mere-item">
                <h3>Action</h3>
                <p><a class="btn-gmb" href="/admin?module=gmb&view=fiche">Commencer</a></p>
            </article>
        </div>
    </section>

    <section aria-labelledby="gmb-modules-title">
        <h2 id="gmb-modules-title" class="gmb-section-title">Zone action</h2>
        <div class="gmb-cards-grid">
            <a class="gmb-card" href="/admin?module=gmb&view=fiche">
                <p class="gmb-card-index">1</p>
                <h3>Compléter la fiche</h3>
                <p class="gmb-card-module">Renseignez les infos essentielles</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=avis">
                <p class="gmb-card-index">2</p>
                <h3>Répondre aux avis</h3>
                <p class="gmb-card-module">Montrez votre réactivité</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=demande-avis">
                <p class="gmb-card-index">3</p>
                <h3>Demander des avis</h3>
                <p class="gmb-card-module">Augmentez les retours clients</p>
            </a>
            <a class="gmb-card" href="/admin?module=redaction&action=pool_gmb">
                <p class="gmb-card-index">4</p>
                <h3>Publier chaque semaine</h3>
                <p class="gmb-card-module">Restez visible localement</p>
            </a>
            <a class="gmb-card" href="/admin?module=gmb&view=statistiques">
                <p class="gmb-card-index">5</p>
                <h3>Suivre les résultats</h3>
                <p class="gmb-card-module">Mesurez les appels et clics</p>
            </a>
        </div>
    </section>

    <section class="gmb-panel gmb-final-cta" aria-labelledby="gmb-cta-title">
        <h2 id="gmb-cta-title">Progression : Fiche → Avis → Demandes → Publications → Résultats</h2>
        <a class="btn-gmb" href="/admin?module=gmb&view=fiche">Lancer la première étape</a>
    </section>
</div>
