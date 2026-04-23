<?php

$userId = (int) (Auth::user()['id'] ?? 0);
$service = new SitemapService(db());
$service->ensureSchema();
$data = $service->getDashboard($userId);
$sitemap = $data['sitemap'] ?? null;
?>
<div class="hub-page">

<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-sitemap"></i> SEO</div>
    <h1>Sitemap XML</h1>
    <p>Générez, vérifiez et soumettez votre sitemap pour maximiser l'indexation de votre site par Google.</p>
</header>

<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--explanation">
        <h3><i class="fas fa-map" style="color:#3b82f6"></i> Rôle du sitemap</h3>
        <p>Le sitemap indique à Google toutes vos pages importantes — sans lui, certaines pages peuvent ne jamais être indexées.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Après génération</h3>
        <p>Soumettez votre sitemap dans Google Search Console pour accélérer l'indexation de votre contenu frais.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Fréquence recommandée</h3>
        <p>Regénérez votre sitemap après chaque publication majeure : nouvelle fiche ville, article de blog, page de service.</p>
    </article>
</div>

<section class="seo-section">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> › SEO › Sitemap</div>

    <div class="kpi-grid">
        <div class="kpi"><span>Statut</span><strong><?= htmlspecialchars((string) ($sitemap['status'] ?? 'idle')) ?></strong></div>
        <div class="kpi"><span>Dernière génération</span><strong><?= htmlspecialchars((string) ($sitemap['last_generated_at'] ?? 'Jamais')) ?></strong></div>
        <div class="kpi"><span>URLs totales</span><strong><?= (int) ($sitemap['total_urls'] ?? 0) ?></strong></div>
        <div class="kpi"><span>Erreurs détectées</span><strong><?= (int) ($sitemap['issues_count'] ?? count($data['issues'] ?? [])) ?></strong></div>
    </div>

    <p><strong>URL sitemap :</strong> <?= htmlspecialchars((string) ($sitemap['sitemap_url'] ?? 'Non générée')) ?></p>

    <div class="actions">
        <button type="button" onclick="runSitemapAction('generate')">Générer</button>
        <button type="button" onclick="runSitemapAction('verify')">Vérifier</button>
        <button type="button" onclick="runSitemapAction('submit')">Soumettre à Google</button>
    </div>

    <?php if (!empty($data['coverage'])): ?>
        <h3>Couverture par type</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Type</th><th>Nombre d'URLs</th></tr></thead>
                <tbody>
                <?php foreach ($data['coverage'] as $type => $count): ?>
                    <tr><td><?= htmlspecialchars((string) $type) ?></td><td><?= (int) $count ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h3>Contrôles de cohérence</h3>
    <?php if (empty($data['issues'])): ?>
        <p>Aucune anomalie détectée. Les contenus publiés indexables ont été intégrés.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($data['issues'] as $issue): ?>
                <li><?= htmlspecialchars((string) $issue) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3>Historique</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Action</th><th>Statut</th><th>Message</th><th>URLs</th></tr></thead>
            <tbody id="sitemap-logs-body">
            <?php foreach (($data['logs'] ?? []) as $log): ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($log['created_at'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string) ($log['action_type'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string) ($log['status'] ?? '')) ?></td>
                    <td><?= htmlspecialchars((string) ($log['message'] ?? '')) ?></td>
                    <td><?= (int) ($log['urls_count'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <h3>Prévisualisation XML</h3>
    <pre id="sitemap-xml-preview">Cliquez sur « Générer » pour produire le XML.</pre>
</section>
</div><!-- /.hub-page -->
