<?php
$pageTitle = "Capture";
$pageDescription = "Transformez vos visites en prises de contact";

function renderContent() {
    ?>
    <div class="page-header capture-hub-hero">
        <h1><i class="fas fa-magnet page-icon"></i> Recevez plus de demandes qualifiées</h1>
        <p>Créez un parcours clair pour convertir chaque visite en contact.</p>
    </div>

    <section class="capture-hub-mere" aria-label="Méthode de conversion">
        <article class="capture-mere-card capture-mere-card--motivation">
            <h2>Problème utilisateur</h2>
            <p>Des visiteurs partent sans laisser leurs coordonnées.</p>
        </article>
        <article class="capture-mere-card capture-mere-card--explanation">
            <h2>Logique simple</h2>
            <p>Une promesse claire, un formulaire court, puis une confirmation immédiate.</p>
        </article>
        <article class="capture-mere-card capture-mere-card--resultat">
            <h2>Bénéfice clair</h2>
            <p>Vous obtenez plus de contacts sans effort supplémentaire.</p>
        </article>
        <article class="capture-mere-card capture-mere-card--action">
            <h2>Action</h2>
            <a href="?module=funnels" class="btn btn-primary capture-mere-cta">
                <i class="fas fa-bolt"></i>
                Lancer maintenant
            </a>
        </article>
    </section>

    <section class="capture-hub-block" aria-label="Actions clés de conversion">
        <div class="capture-hub-block__header">
            <h2>Zone action</h2>
            <p>Choisissez une action et avancez sans hésiter.</p>
        </div>

        <div class="capture-step-flow" role="list" aria-label="Actions principales">
            <a role="listitem" href="/capture/" class="card capture-step-card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-flag-checkered"></i></div>
                    <h3 class="card-title">Créer la page d'entrée</h3>
                </div>
                <p class="card-description">Présentez votre offre en quelques secondes.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a role="listitem" href="/capture/form.php" class="card capture-step-card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-list-check"></i></div>
                    <h3 class="card-title">Simplifier le formulaire</h3>
                </div>
                <p class="card-description">Gardez seulement les champs essentiels.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a role="listitem" href="/capture/merci.php" class="card capture-step-card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-circle-check"></i></div>
                    <h3 class="card-title">Finaliser la confirmation</h3>
                </div>
                <p class="card-description">Confirmez la demande et proposez la suite.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a role="listitem" href="?module=optimiser&view=analytics" class="card capture-step-card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                    <h3 class="card-title">Mesurer les résultats</h3>
                </div>
                <p class="card-description">Repérez rapidement ce qui fait gagner des contacts.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Voir</span>
            </a>
        </div>
    </section>

    <section class="capture-final-cta" aria-label="Progression conversion">
        <h2>Progression recommandée : Entrée → Formulaire → Confirmation → Mesure</h2>
        <a href="?module=funnels" class="btn btn-primary">
            <i class="fas fa-rocket"></i>
            Démarrer le parcours
        </a>
    </section>
    <?php
}
