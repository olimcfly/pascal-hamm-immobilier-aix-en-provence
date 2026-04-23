<?php

declare(strict_types=1);

require_once __DIR__ . '/../services/SeoTechnicalPerformanceService.php';

$userId = (int)(Auth::user()['id'] ?? 0);
$service = new SeoTechnicalPerformanceService(db(), $userId);
$data = $service->getDashboardData();
$flash = getFlash();

$statusLabels = [
    'bon' => 'Bon',
    'moyen' => 'Moyen',
    'a_corriger' => 'À corriger',
];
?>
<div class="hub-page">

<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-gauge-high"></i> SEO</div>
    <h1>Performance technique</h1>
    <p>Un tableau de bord clair pour savoir si votre site est sain, quoi corriger et dans quel ordre de priorité.</p>
</header>

<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Pourquoi c'est crucial</h3>
        <p>Un site lent ou avec des erreurs techniques perd des positions Google — indépendamment de la qualité du contenu.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Ce que l'audit détecte</h3>
        <p>Vitesse, balises manquantes, liens brisés, images non optimisées — triés par ordre de priorité d'impact SEO.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Par où commencer</h3>
        <p>Traitez d'abord les problèmes critiques, puis les importants. Les mineurs peuvent attendre le prochain sprint.</p>
    </article>
</div>

<section class="seo-section seo-performance-dashboard">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> › SEO › Performance technique</div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
        <p style="margin:0;font-size:.88rem;color:#64748b">Lancez un audit pour analyser votre site et obtenir un score de santé technique.</p>
        <form method="post" action="/admin?module=seo&action=performance_run">
            <?= csrfField() ?>
            <button type="submit" class="hub-btn hub-btn--gold"><i class="fas fa-magnifying-glass-chart"></i> Lancer un audit global</button>
        </form>
    </div>

    <?php if ($flash): ?>
        <div class="seo-flash seo-flash-<?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="kpi-grid seo-kpi-grid-5">
        <div class="kpi"><span>Score global</span><strong><?= (int)$data['global_score'] ?>/100</strong></div>
        <div class="kpi"><span>État</span><strong><?= e($statusLabels[$data['status']] ?? 'À corriger') ?></strong></div>
        <div class="kpi"><span>Critiques</span><strong><?= (int)$data['issue_counts']['critical'] ?></strong></div>
        <div class="kpi"><span>Importants</span><strong><?= (int)$data['issue_counts']['important'] ?></strong></div>
        <div class="kpi"><span>Mineurs</span><strong><?= (int)$data['issue_counts']['minor'] ?></strong></div>
    </div>

    <div class="grid-two">
        <div class="chart-card">
            <h3>Pages prévues dans l’audit</h3>
            <ul>
                <?php foreach ($data['target_pages'] as $target): ?>
                    <li><?= e((string)$target['url']) ?> · <span class="pill"><?= e((string)$target['type']) ?></span></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chart-card">
            <h3>Décision rapide</h3>
            <ul>
                <li><strong>Mon site est-il sain ?</strong> <?= (int)$data['global_score'] >= 80 ? 'Oui, globalement.' : 'Des correctifs sont recommandés.' ?></li>
                <li><strong>Quelles pages ont un problème ?</strong> Voir la liste des pages auditées.</li>
                <li><strong>Quoi corriger en priorité ?</strong> Commencer par les problèmes critiques puis importants.</li>
            </ul>
            <a class="seo-secondary" href="/admin?module=seo&action=performance_history">Voir toutes les pages auditées</a>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Page</th><th>Type</th><th>Score</th><th>Date audit</th><th>Problèmes</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($data['latest_audits'], 0, 12) as $audit): ?>
                <tr>
                    <td><?= e((string)$audit['page_url']) ?></td>
                    <td><span class="pill"><?= e((string)$audit['page_type']) ?></span></td>
                    <td><?= (int)$audit['global_score'] ?>/100</td>
                    <td><?= e((string)$audit['audited_at']) ?></td>
                    <td><?= (int)$audit['broken_links_count'] + (int)$audit['image_issues_count'] ?></td>
                    <td><a href="/admin?module=seo&action=performance_history&audit_id=<?= (int)$audit['id'] ?>">Voir détail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
</div><!-- /.hub-page -->
