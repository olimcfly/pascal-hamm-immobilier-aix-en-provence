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
        <p>Analysez et améliorez en continu vos performances</p>
    </div>

    <div class="cards-container">

        <a class="card" href="?module=optimiser&view=analytics" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd; text-decoration:none; color:inherit; display:block;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-chart-bar"></i></div>
                <h3 class="card-title">Tableau de bord Analytics</h3>
            </div>
            <p class="card-description">Vue consolidée de vos KPIs : leads, estimations et trafic pages (si disponible).</p>
            <div class="card-tags"><span class="tag">KPIs</span><span class="tag">Reporting</span><span class="tag">30 / 90 jours</span></div>
            <span class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir le tableau de bord</span>
        </a>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-vials"></i></div>
                <h3 class="card-title">A/B Testing</h3>
            </div>
            <p class="card-description">Testez vos pages, emails et messages pour maximiser les taux de conversion.</p>
            <div class="card-tags"><span class="tag">Tests</span><span class="tag">Conversion</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-lightbulb"></i></div>
                <h3 class="card-title">Recommandations IA</h3>
            </div>
            <p class="card-description">Suggestions personnalisées pour améliorer vos actions chaque semaine.</p>
            <div class="card-tags"><span class="tag">IA</span><span class="tag">Insights</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-chart-line"></i></div>
                <h3 class="card-title">Rapport mensuel</h3>
            </div>
            <p class="card-description">Générez un rapport PDF ou HTML (leads, sources, conversions, blog, social) et envoyez-le automatiquement en fin de mois.</p>
            <div class="card-tags"><span class="tag">Rapport</span><span class="tag">Export PDF/HTML</span><span class="tag">Email auto</span></div>
            <a class="card-action" href="/admin?module=optimiser&action=rapport-mensuel"><i class="fas fa-arrow-right"></i> Ouvrir</a>
        </div>

    </div>
    <?php
}
