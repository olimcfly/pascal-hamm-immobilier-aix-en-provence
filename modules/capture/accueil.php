<?php
$pageTitle = "Capture";
$pageDescription = "Hub conversion pour transformer vos visiteurs en contacts";

function renderContent() {
    ?>
    <div class="page-header capture-hub-hero">
        <h1><i class="fas fa-magnet page-icon"></i> Transformer vos visiteurs en contacts</h1>
        <p>Mettez en place un système simple pour capter des prospects automatiquement.</p>
    </div>

    <section class="capture-hub-mere" aria-label="Pourquoi structurer votre capture">
        <article class="capture-mere-card capture-mere-card--motivation">
            <h2>Ce qui se passe aujourd’hui</h2>
            <p>Sans système clair, une partie de vos visiteurs repart sans laisser ses coordonnées. Vous perdez des opportunités chaque semaine.</p>
        </article>
        <article class="capture-mere-card capture-mere-card--explanation">
            <h2>Le parcours à suivre</h2>
            <p>Un tunnel simple guide le prospect en 3 étapes&nbsp;: <strong>landing page</strong>, <strong>formulaire</strong>, puis <strong>confirmation</strong>.</p>
        </article>
        <article class="capture-mere-card capture-mere-card--resultat">
            <h2>Ce que vous obtenez</h2>
            <p>Vos leads sont capturés automatiquement, avec un parcours lisible et mesurable de bout en bout.</p>
        </article>
        <a href="?module=funnels" class="btn btn-primary capture-mere-cta">
            <i class="fas fa-bolt"></i>
            Créer un tunnel
        </a>
    </section>

    <section class="capture-hub-block" aria-label="Créer un tunnel de capture">
        <div class="capture-hub-block__header">
            <h2>BLOC 1 — Créer un tunnel de capture</h2>
            <p>Suivez les étapes dans l'ordre pour construire votre parcours de conversion.</p>
        </div>

        <div class="capture-step-flow" role="list" aria-label="Étapes du tunnel">
            <a role="listitem" href="/capture/" class="card capture-step-card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-flag-checkered"></i></div>
                    <h3 class="card-title">1. Landing page</h3>
                </div>
                <p class="card-description">Présentez votre promesse avec un message clair et un CTA visible.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir l'étape</span>
            </a>

            <a role="listitem" href="/capture/form.php" class="card capture-step-card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-list-check"></i></div>
                    <h3 class="card-title">2. Formulaire</h3>
                </div>
                <p class="card-description">Collectez les informations essentielles sans friction inutile.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir l'étape</span>
            </a>

            <a role="listitem" href="/capture/merci.php" class="card capture-step-card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-circle-check"></i></div>
                    <h3 class="card-title">3. Page de confirmation</h3>
                </div>
                <p class="card-description">Validez la demande et proposez la prochaine action à forte valeur.</p>
                <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir l'étape</span>
            </a>
        </div>
    </section>

    <div class="cards-container capture-secondary-grid">
        <section class="card capture-module-card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;" aria-label="Suivre la performance">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                <h3 class="card-title">BLOC 2 — Suivre la performance</h3>
            </div>
            <p class="card-description">Mesurez ce qui fonctionne pour améliorer chaque étape du tunnel.</p>
            <div class="card-tags">
                <span class="tag">Statistiques</span>
                <span class="tag">Taux de conversion</span>
            </div>
            <a href="?module=optimiser&view=analytics" class="card-action"><i class="fas fa-arrow-right"></i> Voir les performances</a>
        </section>

        <section class="card capture-module-card" style="--card-accent:#8e44ad; --card-icon-bg:#f4ecf7;" aria-label="Automatiser en option">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-robot"></i></div>
                <h3 class="card-title">BLOC 3 — Automatiser (optionnel)</h3>
            </div>
            <p class="card-description">Ajoutez des automatisations simples pour accélérer votre suivi sans surcharge manuelle.</p>
            <div class="card-tags">
                <span class="tag">Emails</span>
                <span class="tag">CRM</span>
            </div>
            <div class="capture-module-actions">
                <a href="?module=messagerie" class="card-action"><i class="fas fa-envelope"></i> Emails</a>
                <a href="?module=capturer" class="card-action"><i class="fas fa-address-book"></i> CRM</a>
            </div>
        </section>
    </div>

    <section class="capture-final-cta" aria-label="Créer le premier tunnel">
        <h2>Créez votre premier tunnel</h2>
        <a href="?module=funnels" class="btn btn-primary">
            <i class="fas fa-rocket"></i>
            Lancer mon tunnel
        </a>
    </section>
    <?php
}
