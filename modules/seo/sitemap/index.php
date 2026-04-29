<?php

$userId = (int) (Auth::user()['id'] ?? 0);
$service = new SitemapService(db());
$service->ensureSchema();
$data = $service->getDashboard($userId);
$sitemap = $data['sitemap'] ?? null;
?>
<section class="seo-section seo-sitemap-dashboard">
    <div class="seo-breadcrumb"><a href="/admin?module=seo">Accueil</a> &gt; SEO &gt; Sitemap</div>

    <div class="seo-headline">
        <div>
            <span class="seo-eyebrow">Indexation Google</span>
            <h2>Sitemap XML</h2>
            <p>Générez, vérifiez et préparez la soumission de votre sitemap pour aider Google à découvrir les pages importantes.</p>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi"><span>Statut</span><strong><?= htmlspecialchars((string) ($sitemap['status'] ?? 'idle')) ?></strong></div>
        <div class="kpi"><span>Dernière génération</span><strong><?= htmlspecialchars((string) ($sitemap['last_generated_at'] ?? 'Jamais')) ?></strong></div>
        <div class="kpi"><span>URLs totales</span><strong><?= (int) ($sitemap['total_urls'] ?? 0) ?></strong></div>
        <div class="kpi"><span>Erreurs détectées</span><strong><?= (int) ($sitemap['issues_count'] ?? count($data['issues'] ?? [])) ?></strong></div>
    </div>

    <div class="seo-info-strip">
        <strong>URL sitemap</strong>
        <span><?= htmlspecialchars((string) ($sitemap['sitemap_url'] ?? 'Non générée')) ?></span>
    </div>

    <input type="hidden" id="seo-sitemap-csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
    <div class="actions seo-action-row">
        <button type="button" class="seo-primary-action" onclick="runSitemapAction('generate', this)">
            <i class="fas fa-rotate"></i> Générer
        </button>
        <button type="button" class="seo-secondary-action" onclick="runSitemapAction('verify', this)">
            <i class="fas fa-check"></i> Vérifier
        </button>
        <button type="button" class="seo-secondary-action" onclick="runSitemapAction('submit', this)">
            <i class="fas fa-paper-plane"></i> Soumettre à Google
        </button>
    </div>
    <div id="sitemap-action-result" class="seo-action-result" role="status" aria-live="polite" hidden></div>

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
        <p class="seo-empty-state">Aucune anomalie détectée. Les contenus publiés indexables ont été intégrés.</p>
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
