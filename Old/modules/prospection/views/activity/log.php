<?php
// modules/prospection/views/activity/log.php
$pageTitle = 'Journal d\'activité — Prospection';
$flash     = Session::getFlash();

$eventMeta = [
    'campaign_created' => ['label'=>'Campagne créée',       'icon'=>'fas fa-plus-circle',        'color'=>'#3b82f6'],
    'campaign_deleted' => ['label'=>'Campagne supprimée',    'icon'=>'fas fa-trash',              'color'=>'#ef4444'],
    'contact_enrolled' => ['label'=>'Contact inscrit',       'icon'=>'fas fa-user-plus',          'color'=>'#10b981'],
    'contact_removed'  => ['label'=>'Contact retiré',        'icon'=>'fas fa-user-minus',         'color'=>'#f59e0b'],
    'contact_replied'  => ['label'=>'Réponse reçue',         'icon'=>'fas fa-reply',              'color'=>'#10b981'],
    'email_sent'       => ['label'=>'Email envoyé',          'icon'=>'fas fa-paper-plane',        'color'=>'#3b82f6'],
    'email_failed'     => ['label'=>'Échec d\'envoi',        'icon'=>'fas fa-circle-exclamation', 'color'=>'#ef4444'],
    'step_added'       => ['label'=>'Étape ajoutée',         'icon'=>'fas fa-list-check',         'color'=>'#3b82f6'],
    'sequence_stopped' => ['label'=>'Séquence arrêtée',      'icon'=>'fas fa-stop-circle',        'color'=>'#6b7280'],
    'campaign_paused'  => ['label'=>'Campagne mise en pause','icon'=>'fas fa-pause-circle',       'color'=>'#f59e0b'],
];
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="?module=prospection" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Dashboard
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold"><i class="fas fa-clock-rotate-left text-secondary me-2"></i>Journal d'activité</h1>
        <p class="text-muted mb-0 small"><?= count($activity) ?> événement<?= count($activity) > 1 ? 's' : '' ?> — tout ce qui s'est passé</p>
    </div>
</div>

<?php if (empty($activity)): ?>
<div class="text-center py-5">
    <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-25"></i>
    <h5 class="text-muted">Aucune activité enregistrée</h5>
    <p class="text-muted small">Les actions sur vos campagnes et contacts apparaîtront ici.</p>
</div>
<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php
            $prevDate = '';
            foreach ($activity as $event):
                $meta    = $eventMeta[$event['event']] ?? ['label' => $event['event'], 'icon' => 'fas fa-circle', 'color' => '#9ca3af'];
                $evDate  = date('d/m/Y', strtotime($event['created_at']));
            ?>
            <?php if ($evDate !== $prevDate): ?>
                <?php $prevDate = $evDate; ?>
                <div class="px-3 py-2 bg-light border-bottom">
                    <div class="text-muted fw-semibold" style="font-size:.75rem;">
                        <i class="fas fa-calendar me-1"></i><?= $evDate ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="list-group-item border-0 px-3 py-2 d-flex align-items-start gap-3">
                <div class="flex-shrink-0 mt-1" style="width:20px;text-align:center;">
                    <i class="<?= $meta['icon'] ?> small" style="color:<?= $meta['color'] ?>;"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-semibold small"><?= e($meta['label']) ?></span>
                        <?php if ($event['campaign_name']): ?>
                        <a href="?module=prospection&action=campaign-detail&campaign_id=<?= (int)$event['campaign_id'] ?>"
                           class="text-decoration-none">
                            <span class="badge bg-light text-primary border" style="font-size:.68rem;">
                                <i class="fas fa-bullhorn me-1"></i><?= e($event['campaign_name']) ?>
                            </span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size:.78rem;">
                        <?php if ($event['contact_name'] || $event['contact_email']): ?>
                        <i class="fas fa-user me-1"></i>
                        <?= e($event['contact_name'] ?: $event['contact_email']) ?>
                        <?= $event['contact_email'] && $event['contact_name'] ? ' · ' . e($event['contact_email']) : '' ?>
                        <?php endif; ?>
                        <?php if ($event['detail']): ?>
                        <?= ($event['contact_name'] || $event['contact_email']) ? ' · ' : '' ?>
                        <span class="text-muted"><?= e($event['detail']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex-shrink-0 text-muted" style="font-size:.72rem;white-space:nowrap;">
                    <?= date('H:i', strtotime($event['created_at'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php endif; ?>
