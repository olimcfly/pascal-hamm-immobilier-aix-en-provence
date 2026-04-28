<?php
// modules/prospection/views/campaigns/detail.php
$flash = Session::getFlash();

if (!$campaign) {
    echo '<div class="alert alert-danger">Campagne introuvable.</div>';
    return;
}

$pageTitle  = e($campaign['name']);
$contacts   = $campaign['contacts']   ?? [];
$steps      = $campaign['steps']      ?? [];
$sendStats  = $campaign['send_stats'] ?? [];
$cid        = (int) $campaign['id'];

$mailMode   = \ProspectionMailer::currentMode();
$isTestMode = \ProspectionMailer::isTestMode();

// ---- Helpers badges ----
$campaignBadge = static function (string $s): string {
    return match($s) {
        'active'    => '<span class="badge bg-success">Active</span>',
        'draft'     => '<span class="badge bg-secondary">Brouillon</span>',
        'paused'    => '<span class="badge bg-warning text-dark">En pause</span>',
        'completed' => '<span class="badge bg-primary">Terminée</span>',
        default     => '<span class="badge bg-light text-dark">' . e($s) . '</span>',
    };
};

$enrollBadge = static function (string $s): string {
    return match($s) {
        'enrolled'      => '<span class="badge bg-secondary">Inscrit</span>',
        'active'        => '<span class="badge bg-success">Actif</span>',
        'pending'       => '<span class="badge bg-light text-dark border">En attente</span>',
        'queued'        => '<span class="badge" style="background:#e0f2fe;color:#0369a1;">En file</span>',
        'delivered'     => '<span class="badge" style="background:#dcfce7;color:#166534;">Délivré</span>',
        'opened'        => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Ouvert</span>',
        'clicked'       => '<span class="badge" style="background:#ede9fe;color:#6d28d9;">Cliqué</span>',
        'paused'        => '<span class="badge bg-warning text-dark">En pause</span>',
        'completed'     => '<span class="badge bg-primary">Terminé</span>',
        'replied'       => '<span class="badge" style="background:#d1fae5;color:#065f46;">Répondu</span>',
        'bounced'       => '<span class="badge bg-danger">Bounced</span>',
        'unsubscribed'  => '<span class="badge bg-light text-muted">Désabonné</span>',
        default         => '<span class="badge bg-light text-dark">' . e($s) . '</span>',
    };
};

// ---- Calcul stats détaillées ----
$statsByStatus = [];
foreach ($contacts as $cc) {
    $st = $cc['enroll_status'] ?? 'enrolled';
    $statsByStatus[$st] = ($statsByStatus[$st] ?? 0) + 1;
}

$repliedCount  = $statsByStatus['replied']   ?? 0;
$bouncedCount  = $statsByStatus['bounced']   ?? 0;
$completedCount= $statsByStatus['completed'] ?? 0;
$activeCount   = $statsByStatus['active']    ?? 0;
$pausedCount   = $statsByStatus['paused']    ?? 0;
$unsubCount    = $statsByStatus['unsubscribed'] ?? 0;

$sentTotal     = (int)($sendStats['sent']    ?? 0);
$failedTotal   = (int)($sendStats['failed']  ?? 0);
$openedTotal   = (int)($sendStats['opened']  ?? 0);

// Contacts disponibles pour inscription
$enrolledIds = array_column($contacts, 'id');
$allContacts = $prospectService->getList(['status' => 'active'], 1, 500)['contacts'];
$available   = array_filter($allContacts, fn($c) => !in_array($c['id'], $enrolledIds));

// Logs d'envoi (50 derniers)
$sendLogs = $sequenceService->getSendLogs($cid, 50);
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- En-tête -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <a href="?module=prospection&action=campaigns" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Campagnes
            </a>
            <?= $campaignBadge($campaign['status']) ?>
            <?php if ($isTestMode): ?>
            <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fcd34d;">
                <i class="fas fa-flask me-1"></i>Mode <?= strtoupper(e($mailMode)) ?>
            </span>
            <?php endif; ?>
        </div>
        <h1 class="h3 mb-0 fw-bold"><?= e($campaign['name']) ?></h1>
        <?php if ($campaign['description']): ?>
        <p class="text-muted mb-0 small"><?= e($campaign['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <!-- Bouton Simuler -->
        <form method="POST" action="?module=prospection" id="simulate-form">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="run_simulation">
            <input type="hidden" name="campaign_id" value="<?= $cid ?>">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('Lancer la simulation pour tous les contacts de cette campagne ?\n\nCela va mettre à jour leurs statuts selon leurs scénarios.')">
                <i class="fas fa-play me-2"></i>Lancer simulation
            </button>
        </form>
        <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>" class="btn btn-outline-secondary">
            <i class="fas fa-list-ol me-2"></i>Séquence
        </a>
        <a href="?module=prospection&action=campaign-edit&campaign_id=<?= $cid ?>" class="btn btn-outline-secondary">
            <i class="fas fa-pen me-2"></i>Modifier
        </a>
    </div>
</div>

<!-- Stats KPIs -->
<div class="row g-2 mb-4">
    <?php foreach ([
        ['label'=>'Contacts',  'value'=>count($contacts),  'color'=>'#1a3c5e', 'icon'=>'fas fa-users'],
        ['label'=>'Étapes',    'value'=>count($steps),     'color'=>'#1a3c5e', 'icon'=>'fas fa-list-ol'],
        ['label'=>'Envoyés',   'value'=>$sentTotal,         'color'=>'#3b82f6', 'icon'=>'fas fa-paper-plane'],
        ['label'=>'Réponses',  'value'=>$repliedCount,      'color'=>'#10b981', 'icon'=>'fas fa-reply'],
        ['label'=>'Terminés',  'value'=>$completedCount,    'color'=>'#6366f1', 'icon'=>'fas fa-check-circle'],
        ['label'=>'Bouncés',   'value'=>$bouncedCount,      'color'=>'#ef4444', 'icon'=>'fas fa-circle-xmark'],
        ['label'=>'En pause',  'value'=>$pausedCount,       'color'=>'#f59e0b', 'icon'=>'fas fa-pause'],
        ['label'=>'Désabonnés','value'=>$unsubCount,        'color'=>'#9ca3af', 'icon'=>'fas fa-ban'],
    ] as $kpi): ?>
    <div class="col-6 col-md-3 col-xl-auto flex-xl-fill">
        <div class="card border-0 shadow-sm h-100 text-center py-2 px-1">
            <div class="fw-bold" style="font-size:1.5rem;color:<?= $kpi['color'] ?>;"><?= $kpi['value'] ?></div>
            <div class="text-muted" style="font-size:.7rem;"><i class="<?= $kpi['icon'] ?> me-1"></i><?= $kpi['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Onglets -->
<ul class="nav nav-tabs mb-0" id="detail-tabs">
    <li class="nav-item">
        <button class="nav-link active" data-tab="contacts"><i class="fas fa-users me-1"></i>Contacts (<?= count($contacts) ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-tab="logs"><i class="fas fa-envelope me-1"></i>Envois (<?= count($sendLogs) ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-tab="sequence"><i class="fas fa-list-ol me-1"></i>Séquence</button>
    </li>
</ul>

<!-- TAB : Contacts -->
<div class="tab-content" id="tab-contacts">
<div class="card border-0 shadow-sm border-top-0 rounded-top-0">

    <!-- Ajouter contacts -->
    <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
        <div class="fw-semibold small"><i class="fas fa-users me-2 text-primary"></i>Contacts inscrits</div>
        <?php if (!empty($available)): ?>
        <button type="button" class="btn btn-primary btn-sm" onclick="toggleEnrollPanel()">
            <i class="fas fa-plus me-1"></i>Ajouter
        </button>
        <?php endif; ?>
    </div>

    <!-- Panneau enroll -->
    <div id="enroll-panel" class="d-none border-bottom p-3" style="background:#f8fafc;">
        <form method="POST" action="?module=prospection">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="campaign_enroll">
            <input type="hidden" name="campaign_id" value="<?= $cid ?>">
            <div class="fw-semibold small mb-2">Sélectionnez les contacts à inscrire :</div>
            <div class="mb-2">
                <input type="text" id="enroll-search" class="form-control form-control-sm" placeholder="Filtrer…" oninput="filterEnroll(this.value)">
            </div>
            <div id="enroll-list" style="max-height:180px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:.5rem;background:#fff;">
                <?php foreach ($available as $ac): ?>
                <label class="d-flex align-items-center gap-2 px-3 py-2 border-bottom cursor-pointer hover-bg enroll-item">
                    <input type="checkbox" name="contact_ids[]" value="<?= $ac['id'] ?>">
                    <span class="flex-grow-1 small enroll-name">
                        <strong><?= e(trim($ac['first_name'] . ' ' . $ac['last_name'])) ?: e($ac['email']) ?></strong>
                        <span class="text-muted"> — <?= e($ac['email']) ?></span>
                    </span>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between gap-2 mt-2">
                <div>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="checkAll(true)">Tout sélect.</button>
                    <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2" onclick="checkAll(false)">Tout désélect.</button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleEnrollPanel()">Annuler</button>
                    <button type="submit" class="btn btn-sm btn-primary">Inscrire la sélection</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tableau contacts -->
    <?php if (empty($contacts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-user-plus fa-2x mb-2 opacity-25"></i>
        <div class="small">Aucun contact inscrit.</div>
        <?php if (!empty($available)): ?>
        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="toggleEnrollPanel()">
            <i class="fas fa-plus me-1"></i>Ajouter des contacts
        </button>
        <?php else: ?>
        <a href="?module=prospection&action=contacts" class="btn btn-outline-secondary btn-sm mt-2">
            Gérer les contacts
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.83rem;">
            <thead class="bg-light">
                <tr>
                    <th>Contact</th>
                    <th>Statut</th>
                    <th class="d-none d-md-table-cell">Étape</th>
                    <th class="d-none d-md-table-cell">Prochain envoi</th>
                    <th class="d-none d-lg-table-cell">Dernier envoi</th>
                    <th class="d-none d-lg-table-cell">Répondu</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $cc): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e(trim($cc['first_name'] . ' ' . $cc['last_name'])) ?: e($cc['email']) ?></div>
                        <div class="text-muted small"><?= e($cc['email']) ?></div>
                        <?php if ($cc['company']): ?><div class="text-muted" style="font-size:.72rem;"><?= e($cc['company']) ?></div><?php endif; ?>
                    </td>
                    <td><?= $enrollBadge($cc['enroll_status']) ?></td>
                    <td class="d-none d-md-table-cell text-muted small">
                        <?php if ($cc['enroll_status'] === 'completed'): ?>
                            <span class="text-success"><i class="fas fa-check me-1"></i>Terminé</span>
                        <?php elseif ($cc['enroll_status'] === 'replied'): ?>
                            <span class="text-primary"><i class="fas fa-reply me-1"></i>Répondu</span>
                        <?php elseif ($cc['enroll_status'] === 'bounced'): ?>
                            <span class="text-danger"><i class="fas fa-circle-xmark me-1"></i>Bounced</span>
                        <?php elseif ($cc['enroll_status'] === 'unsubscribed'): ?>
                            <span class="text-muted"><i class="fas fa-ban me-1"></i>Désabonné</span>
                        <?php else: ?>
                            <?php $nextStep = (int)$cc['current_step'] + 1; ?>
                            Étape <?= min($nextStep, count($steps)) ?>/<?= count($steps) ?>
                        <?php endif; ?>
                    </td>
                    <td class="d-none d-md-table-cell text-muted small">
                        <?= $cc['next_send_at'] ? formatDate($cc['next_send_at'], 'd/m/Y') : '—' ?>
                    </td>
                    <td class="d-none d-lg-table-cell text-muted small">
                        <?= $cc['last_sent_at'] ? formatDate($cc['last_sent_at'], 'd/m/Y H:i') : '—' ?>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <?php if ($cc['replied_at']): ?>
                            <span class="text-success small"><i class="fas fa-check me-1"></i><?= formatDate($cc['replied_at'], 'd/m') ?></span>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end" style="white-space:nowrap;">
                        <?php if (!in_array($cc['enroll_status'], ['replied','completed','bounced','unsubscribed'])): ?>
                        <form method="POST" action="?module=prospection" class="d-inline"
                              onsubmit="return confirm('Marquer comme répondu ? La séquence sera arrêtée.')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="campaign_mark_replied">
                            <input type="hidden" name="campaign_id" value="<?= $cid ?>">
                            <input type="hidden" name="contact_id" value="<?= $cc['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-outline-success" title="Répondu" style="font-size:.72rem;padding:2px 7px;">
                                <i class="fas fa-reply"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                        <form method="POST" action="?module=prospection" class="d-inline"
                              onsubmit="return confirm('Retirer ce contact de la campagne ?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="campaign_unenroll">
                            <input type="hidden" name="campaign_id" value="<?= $cid ?>">
                            <input type="hidden" name="contact_id" value="<?= $cc['id'] ?>">
                            <button type="submit" class="btn btn-xs btn-outline-danger ms-1" title="Retirer" style="font-size:.72rem;padding:2px 7px;">
                                <i class="fas fa-xmark"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- TAB : Logs d'envoi -->
<div class="tab-content d-none" id="tab-logs">
<div class="card border-0 shadow-sm border-top-0 rounded-top-0">
    <div class="card-header bg-white border-bottom py-2">
        <div class="fw-semibold small"><i class="fas fa-envelope me-2 text-secondary"></i>Journal des envois</div>
    </div>
    <?php if (empty($sendLogs)): ?>
    <div class="text-center py-4 text-muted small">
        <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i><br>Aucun envoi journalisé.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" style="font-size:.78rem;">
            <thead class="bg-light">
                <tr>
                    <th>Date</th>
                    <th>Contact</th>
                    <th>Étape</th>
                    <th>Objet</th>
                    <th>Statut</th>
                    <th>Test ?</th>
                    <th class="d-none d-lg-table-cell">Destinataire réel</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sendLogs as $log):
                    $logBadge = match($log['status']) {
                        'sent','delivered' => '<span class="badge bg-success">' . $log['status'] . '</span>',
                        'failed'           => '<span class="badge bg-danger">failed</span>',
                        'opened'           => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">opened</span>',
                        'clicked'          => '<span class="badge" style="background:#ede9fe;color:#6d28d9;">clicked</span>',
                        'bounced'          => '<span class="badge bg-danger">bounced</span>',
                        'stopped_on_reply' => '<span class="badge bg-primary">stopped</span>',
                        'skipped'          => '<span class="badge bg-secondary">skipped</span>',
                        default            => '<span class="badge bg-light text-dark">' . e($log['status']) . '</span>',
                    };
                ?>
                <tr>
                    <td class="text-muted"><?= $log['sent_at'] ? formatDate($log['sent_at'], 'd/m H:i') : formatDate($log['created_at'], 'd/m H:i') ?></td>
                    <td>
                        <?php if ($log['contact_name']): ?>
                            <div class="fw-semibold"><?= e(trim($log['contact_name'])) ?></div>
                            <div class="text-muted"><?= e($log['to_email']) ?></div>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Test manuel</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= $log['step_order'] ? 'E' . (int)$log['step_order'] : '—' ?></td>
                    <td class="text-truncate" style="max-width:180px;" title="<?= e($log['subject']) ?>"><?= e($log['subject']) ?></td>
                    <td><?= $logBadge ?></td>
                    <td><?= $log['is_test'] ? '<span class="badge" style="background:#fef3c7;color:#92400e;">test</span>' : '<span class="text-muted">—</span>' ?></td>
                    <td class="d-none d-lg-table-cell text-muted"><?= e($log['intended_recipient'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- TAB : Séquence -->
<div class="tab-content d-none" id="tab-sequence">
<div class="card border-0 shadow-sm border-top-0 rounded-top-0">
    <div class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
        <div class="fw-semibold small"><i class="fas fa-list-ol me-2 text-secondary"></i>Aperçu de la séquence</div>
        <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-pen me-1"></i>Éditer
        </a>
    </div>
    <?php if (empty($steps)): ?>
    <div class="text-center py-4 text-muted small">
        <i class="fas fa-list-check fa-2x mb-2 opacity-25"></i><br>Aucune étape.
        <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>" class="btn btn-outline-primary btn-sm d-block mx-auto mt-2" style="max-width:200px;">
            <i class="fas fa-plus me-1"></i>Créer la séquence
        </a>
    </div>
    <?php else: ?>
    <div class="p-3">
        <?php foreach ($steps as $i => $step): ?>
        <div class="d-flex align-items-start gap-3 mb-3">
            <div class="d-flex flex-column align-items-center flex-shrink-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:32px;height:32px;font-size:.8rem;background:#3b82f6;flex-shrink:0;">
                    <?= (int)$step['step_order'] ?>
                </div>
                <?php if ($i < count($steps) - 1): ?>
                <div style="width:2px;height:20px;background:#e5e7eb;margin:3px 0;"></div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 card border-0 shadow-sm p-3">
                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                    <div>
                        <div class="fw-semibold small"><?= e($step['subject']) ?></div>
                        <div class="text-muted" style="font-size:.72rem;">
                            <i class="fas fa-clock me-1"></i>
                            <?= $step['delay_days'] == 0 ? 'J0 — immédiatement' : 'J+' . (int)$step['delay_days'] . ' jours' ?>
                        </div>
                    </div>
                    <!-- Bouton send test intégré -->
                    <button type="button" class="btn btn-xs btn-outline-warning flex-shrink-0"
                            style="font-size:.72rem;padding:2px 8px;"
                            onclick="sendTest(<?= $cid ?>, <?= (int)$step['id'] ?>)"
                            title="Envoyer un email test pour cette étape">
                        <i class="fas fa-flask me-1"></i>Tester
                    </button>
                </div>
                <div class="text-muted small mt-2 border-top pt-2" style="white-space:pre-wrap;font-size:.78rem;max-height:80px;overflow:hidden;" id="preview-<?= $step['id'] ?>">
                    <?= e(mb_substr($step['body_text'], 0, 150)) ?>…
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</div>

<!-- Modal test email -->
<div id="test-modal" class="d-none" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.4);display:flex!important;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:1rem;max-width:500px;width:100%;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div class="p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2 text-warning"></i>Envoyer un email test</h6>
                <button type="button" onclick="closeTestModal()" class="btn-close"></button>
            </div>
            <div class="alert py-2 px-3 small mb-3" style="background:#fef3c7;color:#92400e;">
                <i class="fas fa-info-circle me-1"></i>
                Mode <strong id="test-mode-label"></strong> — l'email sera envoyé à l'adresse ci-dessous.
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Prénom du contact test</label>
                <input type="text" id="t-firstname" class="form-control form-control-sm" value="Jean" placeholder="Jean">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email de test</label>
                <input type="email" id="t-email" class="form-control form-control-sm"
                       value="<?= e(\ProspectionMailer::testRecipient()) ?>"
                       placeholder="votre-test@exemple.fr">
            </div>
            <div id="test-result" class="d-none mb-3"></div>
            <div class="d-flex gap-2 justify-content-end">
                <button type="button" onclick="closeTestModal()" class="btn btn-sm btn-outline-secondary">Annuler</button>
                <button type="button" onclick="submitTest()" class="btn btn-sm btn-warning" id="test-submit-btn">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer le test
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.hover-bg:hover { background:#f8fafc; }
.cursor-pointer  { cursor:pointer; }
.nav-tabs .nav-link { cursor:pointer;border:none;color:#6b7280;font-size:.85rem;padding:.5rem 1rem; }
.nav-tabs .nav-link:hover { color:#1a3c5e; }
.nav-tabs .nav-link.active { color:#1a3c5e;font-weight:600;border-bottom:2px solid #3b82f6; }
.rounded-top-0 { border-top-left-radius:0!important;border-top-right-radius:0!important; }
</style>

<script>
// Onglets
document.querySelectorAll('[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.add('d-none'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('d-none');
    });
});

// Panneau enroll
function toggleEnrollPanel() {
    document.getElementById('enroll-panel').classList.toggle('d-none');
}
function filterEnroll(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.enroll-item').forEach(function(item) {
        item.style.display = item.querySelector('.enroll-name').textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function checkAll(v) {
    document.querySelectorAll('#enroll-list input[type=checkbox]').forEach(cb => cb.checked = v);
}

// Test email
var _testCampaignId = 0, _testStepId = 0;
var csrf = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;

function sendTest(cid, sid) {
    _testCampaignId = cid;
    _testStepId     = sid;
    document.getElementById('test-mode-label').textContent = <?= json_encode(strtoupper(\ProspectionMailer::currentMode())) ?>;
    document.getElementById('test-result').classList.add('d-none');
    document.getElementById('test-modal').classList.remove('d-none');
}
function closeTestModal() {
    document.getElementById('test-modal').classList.add('d-none');
}
function submitTest() {
    var btn = document.getElementById('test-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi…';

    var fd = new FormData();
    fd.append('csrf_token',       csrf);
    fd.append('campaign_id',      _testCampaignId);
    fd.append('step_id',          _testStepId);
    fd.append('preview_first_name', document.getElementById('t-firstname').value);
    fd.append('preview_email',    document.getElementById('t-email').value);

    fetch('?module=prospection&ajax=send_test', {method:'POST', body:fd})
        .then(r => r.json())
        .then(function(data) {
            var box = document.getElementById('test-result');
            box.classList.remove('d-none');
            if (data.ok) {
                box.className = 'alert alert-success py-2 px-3 small mb-3';
                box.innerHTML = '<i class="fas fa-check me-2"></i>Email envoyé ('
                    + escHtml(data.mode) + ') vers <strong>' + escHtml(data.sent_to) + '</strong>'
                    + '<br>Objet : <em>' + escHtml(data.subject) + '</em>';
            } else {
                box.className = 'alert alert-danger py-2 px-3 small mb-3';
                box.innerHTML = '<i class="fas fa-circle-xmark me-2"></i>Échec : ' + escHtml(data.error || 'Erreur inconnue');
            }
        })
        .catch(function() {
            var box = document.getElementById('test-result');
            box.className = 'alert alert-danger py-2 px-3 small mb-3';
            box.innerHTML = 'Erreur réseau.';
            box.classList.remove('d-none');
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Envoyer le test';
        });
}
function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
document.getElementById('test-modal').addEventListener('click', function(e) {
    if (e.target === this) closeTestModal();
});
</script>
