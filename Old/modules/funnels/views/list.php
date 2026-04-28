<?php // modules/funnels/views/list.php ?>
<style>
.funnels-page { display:grid; gap:1.25rem; }

.funnels-info-wrap { position:relative; display:inline-block; margin-bottom:.5rem; }
.funnels-info-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:.4rem .85rem; font-size:.85rem; color:#64748b; cursor:pointer; display:inline-flex; align-items:center; gap:.45rem; transition:background .15s,color .15s; }
.funnels-info-btn:hover { background:#f1f5f9; color:#334155; }
.funnels-info-tooltip { display:none; position:absolute; top:calc(100% + 8px); left:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); padding:1rem 1.1rem; width:400px; max-width:90vw; }
.funnels-info-tooltip.is-open { display:block; }
.funnels-info-row { display:flex; gap:.75rem; align-items:flex-start; padding:.55rem 0; font-size:.84rem; line-height:1.45; color:#374151; }
.funnels-info-row + .funnels-info-row { border-top:1px solid #f1f5f9; }
.funnels-info-row > i { margin-top:2px; flex-shrink:0; width:16px; text-align:center; }

.funnels-toolbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
.funnels-filters { display:flex; gap:.5rem; flex-wrap:wrap; }
.funnels-filter-select { border:1px solid #e2e8f0; border-radius:8px; padding:.38rem .65rem; font-size:.84rem; color:#374151; background:#fff; cursor:pointer; }
.funnels-filter-input { border:1px solid #e2e8f0; border-radius:8px; padding:.38rem .65rem; font-size:.84rem; color:#374151; width:160px; }
.funnels-filter-btn { border:1px solid #e2e8f0; border-radius:8px; padding:.38rem .85rem; font-size:.84rem; color:#64748b; background:#f8fafc; cursor:pointer; }

.funnels-new-btn { display:inline-flex; align-items:center; gap:.45rem; background:#0f2237; color:#fff; text-decoration:none; border-radius:10px; padding:.52rem .92rem; font-size:.84rem; font-weight:700; }
.funnels-new-btn:hover { background:#1a3a5c; color:#fff; }

.funnels-empty { text-align:center; padding:3rem 1.5rem; background:#fff; border:1px solid #e2e8f0; border-radius:14px; }
.funnels-empty-icon { font-size:3rem; color:#cbd5e1; margin-bottom:1rem; }
.funnels-empty h3 { margin:0 0 .5rem; color:#334155; }
.funnels-empty p { margin:0 0 1.5rem; color:#64748b; font-size:.9rem; }

.funnels-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; }
.funnels-table { width:100%; border-collapse:collapse; font-size:.875rem; }
.funnels-table th { background:#f8fafc; padding:.75rem 1rem; text-align:left; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; border-bottom:1px solid #e2e8f0; }
.funnels-table td { padding:.85rem 1rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; color:#374151; }
.funnels-table tr:last-child td { border-bottom:none; }
.funnels-table tr:hover td { background:#fafafa; }

.funnel-name { font-weight:600; color:#0f2237; margin:0 0 .15rem; }
.funnel-slug { font-size:.78rem; color:#94a3b8; }

.funnel-canal-badge { display:inline-flex; align-items:center; padding:.22rem .55rem; border-radius:999px; font-size:.75rem; font-weight:700; }
.funnel-status-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.22rem .6rem; border-radius:999px; font-size:.75rem; font-weight:700; }
.funnel-status-badge.published { background:#d1fae5; color:#065f46; }
.funnel-status-badge.draft { background:#f1f5f9; color:#64748b; }

.funnels-actions { display:flex; gap:.35rem; }
.funnels-action-btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:7px; border:1px solid #e2e8f0; background:#fff; color:#64748b; text-decoration:none; font-size:.8rem; cursor:pointer; transition:background .15s, color .15s; }
.funnels-action-btn:hover { background:#f1f5f9; color:#0f2237; }
.funnels-action-btn.danger:hover { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
</style>

<div class="funnels-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fas fa-filter"></i> Funnels</div>
        <h1>Funnels & Landing Pages</h1>
        <p>Créez des tunnels de conversion guidés par canal, optimisés pour chaque source de trafic.</p>
    </header>

    <div class="funnels-info-wrap">
        <button class="funnels-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
            <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
        </button>
        <div class="funnels-info-tooltip" role="tooltip">
            <div class="funnels-info-row">
                <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
                <div><strong>Problème</strong><br>Une page d'accueil générique convertit mal les visiteurs venant d'une campagne précise.</div>
            </div>
            <div class="funnels-info-row">
                <i class="fas fa-diagram-project" style="color:#3b82f6"></i>
                <div><strong>Logique</strong><br>Un funnel = une source de trafic + une promesse ciblée + un formulaire adapté.</div>
            </div>
            <div class="funnels-info-row">
                <i class="fas fa-chart-line" style="color:#10b981"></i>
                <div><strong>Bénéfice</strong><br>Meilleur Quality Score, coût par lead réduit, taux de conversion en hausse.</div>
            </div>
            <div class="funnels-info-row">
                <i class="fas fa-rocket" style="color:#8b5cf6"></i>
                <div><strong>Action</strong><br>Cliquez sur "Nouveau funnel" et suivez l'assistant en 4 étapes.</div>
            </div>
        </div>
    </div>

    <div class="funnels-toolbar">
        <form method="GET" class="funnels-filters">
            <input type="hidden" name="module" value="funnels">
            <select name="canal" class="funnels-filter-select" onchange="this.form.submit()">
                <option value="">Tous les canaux</option>
                <?php foreach ($canaux as $key => $canal): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= ($filters['canal'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($canal['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="funnels-filter-select" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publiés</option>
                <option value="draft"     <?= ($filters['status'] ?? '') === 'draft'     ? 'selected' : '' ?>>Brouillons</option>
            </select>
            <input type="text" name="ville" class="funnels-filter-input" placeholder="Filtrer par ville"
                   value="<?= htmlspecialchars($filters['ville'] ?? '') ?>">
            <button type="submit" class="funnels-filter-btn"><i class="fas fa-filter"></i></button>
        </form>
        <a href="?module=funnels&action=wizard&step=1" class="funnels-new-btn">
            <i class="fas fa-plus"></i> Nouveau funnel
        </a>
    </div>

    <?php if (empty($funnels)): ?>
    <div class="funnels-empty">
        <div class="funnels-empty-icon"><i class="fas fa-filter"></i></div>
        <h3>Aucun funnel pour l'instant</h3>
        <p>Créez votre premier funnel de conversion en moins de 5 minutes.</p>
        <a href="?module=funnels&action=wizard&step=1" class="funnels-new-btn">
            <i class="fas fa-rocket"></i> Créer mon premier funnel
        </a>
    </div>
    <?php else: ?>

    <div class="funnels-table-wrap">
        <table class="funnels-table">
            <thead>
                <tr>
                    <th>Funnel</th>
                    <th>Canal</th>
                    <th>Ville</th>
                    <th>Statut</th>
                    <th style="text-align:center">Vues</th>
                    <th style="text-align:center">Leads</th>
                    <th style="text-align:center">Conv.</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($funnels as $funnel): ?>
                <?php
                    $canalInfo  = $canaux[$funnel['canal']] ?? ['label' => $funnel['canal'], 'color' => '#999'];
                    $isPublished = $funnel['status'] === 'published';
                ?>
                <tr>
                    <td>
                        <p class="funnel-name"><?= htmlspecialchars($funnel['name']) ?></p>
                        <span class="funnel-slug">/lp/<?= htmlspecialchars($funnel['slug']) ?></span>
                    </td>
                    <td>
                        <span class="funnel-canal-badge" style="background:<?= htmlspecialchars($canalInfo['color']) ?>22;color:<?= htmlspecialchars($canalInfo['color']) ?>">
                            <?= htmlspecialchars($canalInfo['label']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($funnel['ville'] ?? '—') ?></td>
                    <td>
                        <span class="funnel-status-badge <?= $isPublished ? 'published' : 'draft' ?>">
                            <?php if ($isPublished): ?><i class="fas fa-circle" style="font-size:7px"></i><?php endif; ?>
                            <?= $isPublished ? 'Publié' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td style="text-align:center;color:#94a3b8">—</td>
                    <td style="text-align:center;color:#94a3b8">—</td>
                    <td style="text-align:center;color:#94a3b8">—</td>
                    <td>
                        <div class="funnels-actions">
                            <a href="?module=funnels&action=edit&id=<?= (int)$funnel['id'] ?>"
                               class="funnels-action-btn" title="Modifier">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php if ($isPublished): ?>
                                <a href="<?= rtrim(APP_URL, '/') ?>/lp/<?= htmlspecialchars($funnel['slug']) ?>"
                                   target="_blank" class="funnels-action-btn" title="Voir la LP">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="?module=funnels&action=stats&id=<?= (int)$funnel['id'] ?>"
                                   class="funnels-action-btn" title="Statistiques">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                            <?php endif; ?>
                            <button class="funnels-action-btn" title="Dupliquer"
                                    onclick="duplicateFunnel(<?= (int)$funnel['id'] ?>)">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="funnels-action-btn danger" title="Supprimer"
                                    onclick="deleteFunnel(<?= (int)$funnel['id'] ?>, '<?= htmlspecialchars($funnel['name'], ENT_QUOTES) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>
</div>

<script>
(function () {
    var btn = document.querySelector('.funnels-info-btn');
    var tip = document.querySelector('.funnels-info-tooltip');
    if (!btn || !tip) return;
    btn.addEventListener('click', function (e) { e.stopPropagation(); tip.classList.toggle('is-open'); });
    document.addEventListener('click', function () { tip.classList.remove('is-open'); });
})();

function duplicateFunnel(id) {
    if (!confirm('Dupliquer ce funnel ?')) return;
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'duplicate', id})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.error || 'Erreur'); });
}

function deleteFunnel(id, name) {
    if (!confirm('Supprimer le funnel "' + name + '" ? Cette action est irréversible.')) return;
    fetch('/public/admin/api/funnels/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id})
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.error || 'Erreur'); });
}
</script>
