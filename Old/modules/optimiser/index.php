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
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-chart-line"></i> Amélioration continue</div>
            <h1>Gagnez en résultats chaque semaine</h1>
            <p>Voyez ce qui marche, corrigez vite, avancez en continu.</p>
        </header>

        <div class="opt-info-wrap">
            <button class="opt-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
                <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
            </button>
            <div class="opt-info-tooltip" role="tooltip">
                <div class="opt-info-row"><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i><div><strong>Problème</strong><br>Vous agissez sans repère clair.</div></div>
                <div class="opt-info-row"><i class="fas fa-diagram-project" style="color:#3b82f6"></i><div><strong>Logique</strong><br>Mesurer, ajuster, vérifier.</div></div>
                <div class="opt-info-row"><i class="fas fa-chart-line" style="color:#10b981"></i><div><strong>Bénéfice</strong><br>Chaque action devient plus rentable.</div></div>
                <div class="opt-info-row"><i class="fas fa-play-circle" style="color:#f59e0b"></i><div><strong>Action</strong><br>Commencez par un seul levier.</div></div>
            </div>
        </div>
        <style>
        .opt-info-wrap{position:relative;display:inline-block;margin-bottom:1.25rem;}
        .opt-info-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;padding:.4rem .85rem;font-size:.85rem;color:#64748b;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:background .15s,color .15s;}
        .opt-info-btn:hover{background:#f1f5f9;color:#334155;}
        .opt-info-tooltip{display:none;position:absolute;top:calc(100% + 8px);left:0;z-index:200;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:1rem 1.1rem;width:340px;max-width:90vw;}
        .opt-info-tooltip.is-open{display:block;}
        .opt-info-row{display:flex;gap:.75rem;align-items:flex-start;padding:.55rem 0;font-size:.84rem;line-height:1.45;color:#374151;}
        .opt-info-row+.opt-info-row{border-top:1px solid #f1f5f9;}
        .opt-info-row>i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}
        </style>
        <script>
        (function(){var b=document.querySelector('.opt-info-btn'),t=document.querySelector('.opt-info-tooltip');if(!b||!t)return;b.addEventListener('click',function(e){e.stopPropagation();t.classList.toggle('is-open');});document.addEventListener('click',function(){t.classList.remove('is-open');});})();
        </script>

        <div class="hub-modules-grid">
            <a href="?module=optimiser&view=analytics" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-chart-bar"></i></div>
                    <h3>Lire les résultats</h3>
                </div>
                <p>Comprenez en un coup d'œil ce qui monte ou baisse.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <div class="hub-module-card hub-module-card--soon">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-lightbulb"></i></div>
                    <h3>Trouver le frein</h3>
                </div>
                <p>Identifiez le point qui bloque votre progression.</p>
                <span class="hub-state hub-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
            </div>

            <div class="hub-module-card hub-module-card--soon">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-vials"></i></div>
                    <h3>Tester une amélioration</h3>
                </div>
                <p>Comparez deux versions et gardez la meilleure.</p>
                <span class="hub-state hub-state--soon"><i class="fas fa-clock"></i> Bientôt</span>
            </div>

            <a href="/admin?module=optimiser&action=rapport-mensuel" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-file-chart-line"></i></div>
                    <h3>Bilan mensuel</h3>
                </div>
                <p>Gardez une vision claire et prenez vos décisions plus vite.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression optimiser">
            <div>
                <h2>Progression : Mesurer → Corriger → Vérifier → Répéter</h2>
                <p>Commencez par un levier, puis développez votre système.</p>
            </div>
            <a href="?module=optimiser&view=analytics" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Lancer ma première amélioration</a>
        </section>

    </section>
    <?php
}
