<?php
// modules/prospection/views/campaigns/list.php
$pageTitle = 'Campagnes — Prospection';
?>
<div class="hub-page">
<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-bullhorn"></i> Prospection</div>
    <h1>Campagnes email</h1>
    <p>Créez et gérez vos séquences de prospection automatisées — chaque campagne envoie les bons messages au bon moment.</p>
</header>
<div class="prosp-camp-info-wrap">
    <button class="prosp-camp-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
        <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
    </button>
    <div class="prosp-camp-info-tooltip" role="tooltip">
        <div class="prosp-camp-info-row"><i class="fas fa-bolt" style="color:#f59e0b"></i><div><strong>Pourquoi automatiser</strong><br>Un prospect nécessite en moyenne 5 à 7 points de contact avant de répondre. L'automatisation rend ça scalable.</div></div>
        <div class="prosp-camp-info-row"><i class="fas fa-check-circle" style="color:#10b981"></i><div><strong>Ce que vous pilotez</strong><br>Nombre d'inscrits, taux d'ouverture, réponses, étapes — toute la performance en un coup d'œil.</div></div>
        <div class="prosp-camp-info-row"><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i><div><strong>À éviter</strong><br>Ne lancez pas une campagne sans avoir testé la séquence en mode simulation. Vérifiez les textes avant d'envoyer.</div></div>
    </div>
</div>
<style>
.prosp-camp-info-wrap{position:relative;display:inline-block;margin-bottom:1.25rem;}
.prosp-camp-info-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;padding:.4rem .85rem;font-size:.85rem;color:#64748b;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:background .15s,color .15s;}
.prosp-camp-info-btn:hover{background:#f1f5f9;color:#334155;}
.prosp-camp-info-tooltip{display:none;position:absolute;top:calc(100% + 8px);left:0;z-index:200;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:1rem 1.1rem;width:400px;max-width:90vw;}
.prosp-camp-info-tooltip.is-open{display:block;}
.prosp-camp-info-row{display:flex;gap:.75rem;align-items:flex-start;padding:.55rem 0;font-size:.84rem;line-height:1.45;color:#374151;}
.prosp-camp-info-row+.prosp-camp-info-row{border-top:1px solid #f1f5f9;}
.prosp-camp-info-row>i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}
</style>
<script>
(function(){var b=document.querySelector('.prosp-camp-info-btn'),t=document.querySelector('.prosp-camp-info-tooltip');if(!b||!t)return;b.addEventListener('click',function(e){e.stopPropagation();t.classList.toggle('is-open');});document.addEventListener('click',function(){t.classList.remove('is-open');});})();
</script>
</div><!-- /.hub-page -->
<?php

$filters   = [
    'status' => $_GET['status'] ?? '',
    'search' => trim($_GET['search'] ?? ''),
];
$campaigns = $campaignService->getAll($filters);
$stats     = $campaignService->getDashboardStats();
$flash     = Session::getFlash();

$statusBadge = static function (string $s): string {
    return match($s) {
        'active'    => '<span class="badge bg-success">Active</span>',
        'draft'     => '<span class="badge bg-secondary">Brouillon</span>',
        'paused'    => '<span class="badge bg-warning text-dark">En pause</span>',
        'completed' => '<span class="badge bg-primary">Terminée</span>',
        default     => '<span class="badge bg-light text-dark">' . e($s) . '</span>',
    };
};
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- En-tête -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-bullhorn text-warning me-2"></i>Campagnes email</h1>
        <p class="text-muted mb-0"><?= count($campaigns) ?> campagne<?= count($campaigns) > 1 ? 's' : '' ?> — gérez vos séquences de prospection</p>
    </div>
    <a href="?module=prospection&action=campaign-new" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle campagne
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach ([
        'total'     => ['Total',      '#1a3c5e'],
        'active'    => ['Actives',    '#10b981'],
        'draft'     => ['Brouillons', '#6b7280'],
        'paused'    => ['En pause',   '#f59e0b'],
        'completed' => ['Terminées',  '#3b82f6'],
    ] as $key => [$label, $color]): ?>
    <div class="col-6 col-md-auto flex-md-fill">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold" style="font-size:1.6rem;color:<?= $color ?>;"><?= (int)($stats[$key] ?? 0) ?></div>
            <div class="text-muted small"><?= $label ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtres -->
<form method="GET" class="d-flex gap-2 mb-3 flex-wrap">
    <input type="hidden" name="module" value="prospection">
    <input type="hidden" name="action" value="campaigns">
    <div class="input-group input-group-sm" style="max-width:300px;">
        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Rechercher une campagne…" value="<?= e($filters['search']) ?>">
    </div>
    <select name="status" class="form-select form-select-sm" style="max-width:180px;" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <?php foreach (['active'=>'Active','draft'=>'Brouillon','paused'=>'En pause','completed'=>'Terminée'] as $v => $l): ?>
        <option value="<?= $v ?>" <?= $filters['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter me-1"></i>Filtrer</button>
    <?php if ($filters['status'] || $filters['search']): ?>
    <a href="?module=prospection&action=campaigns" class="btn btn-outline-secondary btn-sm"><i class="fas fa-xmark"></i></a>
    <?php endif; ?>
</form>

<!-- Liste des campagnes -->
<?php if (empty($campaigns)): ?>
<div class="text-center py-5">
    <i class="fas fa-bullhorn fa-3x text-muted mb-3 opacity-25"></i>
    <h5 class="text-muted">Aucune campagne</h5>
    <p class="text-muted small">Créez votre première campagne et définissez une séquence d'emails.</p>
    <a href="?module=prospection&action=campaign-new" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Créer une campagne
    </a>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($campaigns as $camp): ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="fw-semibold text-truncate me-2"><?= e($camp['name']) ?></div>
                    <?= $statusBadge($camp['status']) ?>
                </div>
                <?php if ($camp['description']): ?>
                <div class="text-muted small mb-3 line-clamp-2"><?= e($camp['description']) ?></div>
                <?php else: ?>
                <div class="text-muted small mb-3"><em>Pas de description</em></div>
                <?php endif; ?>

                <!-- Métriques -->
                <div class="d-flex gap-3 text-center border-top pt-3 mb-3">
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1.1rem;"><?= (int)$camp['contact_count'] ?></div>
                        <div class="text-muted" style="font-size:.7rem;">Contacts</div>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1.1rem;"><?= (int)$camp['step_count'] ?></div>
                        <div class="text-muted" style="font-size:.7rem;">Étapes</div>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1.1rem;color:#10b981;"><?= (int)$camp['reply_count'] ?></div>
                        <div class="text-muted" style="font-size:.7rem;">Réponses</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <a href="?module=prospection&action=campaign-detail&campaign_id=<?= $camp['id'] ?>" class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-eye me-1"></i>Ouvrir
                    </a>
                    <a href="?module=prospection&action=sequence&campaign_id=<?= $camp['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Séquence">
                        <i class="fas fa-list-ol"></i>
                    </a>
                    <a href="?module=prospection&action=campaign-edit&campaign_id=<?= $camp['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="deleteCampaign(<?= $camp['id'] ?>, '<?= e(addslashes($camp['name'])) ?>')"
                        title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 pt-0 pb-2">
                <div class="text-muted" style="font-size:.72rem;">
                    <i class="fas fa-clock me-1"></i>Créée le <?= formatDate($camp['created_at'], 'd/m/Y') ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<form id="delete-campaign-form" method="POST" action="?module=prospection">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="campaign_delete">
    <input type="hidden" name="id" id="delete-campaign-id">
</form>

<script>
function deleteCampaign(id, name) {
    if (!confirm('Supprimer la campagne "' + name + '" ?\n\nLes contacts inscrits ne seront pas supprimés.')) return;
    document.getElementById('delete-campaign-id').value = id;
    document.getElementById('delete-campaign-form').submit();
}
</script>

<style>
.line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
</style>
