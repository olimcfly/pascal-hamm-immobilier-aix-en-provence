<?php

require_once __DIR__ . '/services/MonthlyReportService.php';

$allowedActions = ['index', 'rapport-mensuel'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z-]/', '', (string) $_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

$pageTitle = $action === 'rapport-mensuel' ? 'Optimiser — Rapport mensuel' : 'Optimiser';
$pageDescription = 'Analysez et améliorez en continu vos performances';

function renderContent(): void
{
    global $action;

    if ($action === 'rapport-mensuel') {
        require __DIR__ . '/views/rapport-mensuel.php';
        return;
    }
    ?>
    <div class="page-header">
        <h1><i class="fas fa-chart-line page-icon"></i> HUB <span class="page-title-accent">Optimiser</span></h1>
        <p>Piloter et améliorer vos résultats</p>
        <p class="optimiser-hero-subtitle">Analysez vos performances et améliorez votre système en continu.</p>
    </div>

    <section class="optimiser-mere-card" aria-label="Cycle d'amélioration continue">
        <div class="optimiser-mere-header">
            <span class="optimiser-mere-kicker">MOTIVATION</span>
            <h2>Travailler sans données = perte</h2>
        </div>
        <p class="optimiser-mere-explanation">
            Pour progresser, il faut piloter un cycle court et régulier : analyser, corriger, tester.
        </p>
        <div class="optimiser-cycle" role="list" aria-label="Cycle analyser corriger tester">
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">1</span>
                <strong>Analyser</strong>
            </div>
            <div class="optimiser-cycle-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">2</span>
                <strong>Corriger</strong>
            </div>
            <div class="optimiser-cycle-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>
            <div class="optimiser-cycle-step" role="listitem">
                <span class="optimiser-cycle-badge">3</span>
                <strong>Tester</strong>
            </div>
            <div class="optimiser-cycle-loop" aria-hidden="true"><i class="fas fa-rotate-right"></i></div>
        </div>
        <div class="optimiser-mere-footer">
            <p><strong>Résultat :</strong> progression continue.</p>
            <p><strong>Action :</strong> faire 1 amélioration maintenant.</p>
        </div>
    </section>

    <div class="cards-container">

        <a class="card" href="?module=optimiser&view=analytics" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd; text-decoration:none; color:inherit; display:block;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                <h3 class="card-title">Comprendre vos performances</h3>
            </div>
            <p class="card-description">Module Analytics pour visualiser vos KPIs : leads, estimations et trafic pages.</p>
            <div class="card-tags"><span class="tag">Analytics</span><span class="tag">KPIs</span><span class="tag">30 / 90 jours</span></div>
            <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir Analytics</span>
        </a>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                <h3 class="card-title">Identifier les problèmes</h3>
            </div>
            <p class="card-description">Module IA / insights pour détecter les frictions et prioriser les corrections.</p>
            <div class="card-tags"><span class="tag">IA</span><span class="tag">Insights</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-vials"></i></div>
                <h3 class="card-title">Tester des améliorations</h3>
            </div>
            <p class="card-description">Module A/B testing pour comparer vos variantes de pages, emails et messages.</p>
            <div class="card-tags"><span class="tag">A/B testing</span><span class="tag">Expérimentation</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-chart-line"></i></div>
                <h3 class="card-title">Suivre & ajuster</h3>
            </div>
            <p class="card-description">Module rapports pour suivre les résultats, ajuster vos actions et automatiser le suivi mensuel.</p>
            <div class="card-tags"><span class="tag">Rapports</span><span class="tag">Export PDF/HTML</span><span class="tag">Email auto</span></div>
            <a class="card-action" href="/admin?module=optimiser&action=rapport-mensuel"><i class="fas fa-arrow-right"></i> Ouvrir les rapports</a>
        </div>

    </div>

    <section class="optimiser-final-cta" aria-label="Appel à l'action final">
        <h2>Améliorez vos résultats</h2>
        <a href="?module=optimiser&view=analytics" class="btn btn-primary">
            <i class="fas fa-rocket"></i> Lancer une optimisation
        </a>
    </section>
    <?php
}
