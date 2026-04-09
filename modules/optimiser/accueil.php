<?php

require_once __DIR__ . '/services/MonthlyReportService.php';

$allowedActions = ['index', 'rapport-mensuel'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

$pageTitle = $action === 'rapport-mensuel' ? 'Optimiser — Rapport mensuel' : 'Optimiser';
$pageDescription = 'Améliorez vos résultats avec des décisions simples';

function renderContent(): void
{
    global $action;

    if ($action === 'rapport-mensuel') {
        require __DIR__ . '/views/rapport-mensuel.php';
        return;
    }
    ?>
    <div class="page-header">
        <h1><i class="fas fa-chart-line page-icon"></i> Gagnez en résultats chaque semaine</h1>
        <p>Voyez ce qui marche, corrigez vite, avancez en continu.</p>
    </div>

    <section class="optimiser-mere-card" aria-label="Méthode d'amélioration">
        <div class="optimiser-mere-header">
            <h2>Méthode simple en 4 points</h2>
        </div>
        <div class="optimiser-cycle" role="list" aria-label="Méthode">
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">1</span>
                <strong>Problème utilisateur</strong>
                <small>Vous agissez sans repère clair.</small>
            </div>
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">2</span>
                <strong>Logique simple</strong>
                <small>Mesurer, ajuster, vérifier.</small>
            </div>
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">3</span>
                <strong>Bénéfice clair</strong>
                <small>Chaque action devient plus rentable.</small>
            </div>
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">4</span>
                <strong>Action</strong>
                <small>Commencez par un seul levier.</small>
            </div>
        </div>
        <div class="optimiser-mere-footer">
            <a href="?module=optimiser&view=analytics" class="btn btn-primary"><i class="fas fa-bolt"></i> Commencer</a>
        </div>
    </section>

    <div class="cards-container">
        <a class="card" href="?module=optimiser&view=analytics" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd; text-decoration:none; color:inherit; display:block;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                <h3 class="card-title">Lire les résultats</h3>
            </div>
            <p class="card-description">Comprenez en un coup d'œil ce qui monte ou baisse.</p>
            <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                <h3 class="card-title">Trouver le frein principal</h3>
            </div>
            <p class="card-description">Identifiez le point qui bloque votre progression.</p>
            <span class="card-soon"><i class="fas fa-clock"></i> Bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-vials"></i></div>
                <h3 class="card-title">Tester une amélioration</h3>
            </div>
            <p class="card-description">Comparez deux versions et gardez la meilleure.</p>
            <span class="card-soon"><i class="fas fa-clock"></i> Bientôt</span>
        </div>

        <a class="card" href="/admin?module=optimiser&action=rapport-mensuel" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec; text-decoration:none; color:inherit; display:block;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-chart-line"></i></div>
                <h3 class="card-title">Partager le bilan mensuel</h3>
            </div>
            <p class="card-description">Gardez une vision claire et prenez vos décisions plus vite.</p>
            <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>
    </div>

    <section class="optimiser-final-cta" aria-label="Progression optimiser">
        <h2>Progression : Mesurer → Corriger → Vérifier → Répéter</h2>
        <a href="?module=optimiser&view=analytics" class="btn btn-primary">
            <i class="fas fa-rocket"></i> Lancer ma première amélioration
        </a>
    </section>
    <?php
}
