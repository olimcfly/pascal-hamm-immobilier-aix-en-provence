<?php
// modules/prospection/views/dashboard.php
$pageTitle = 'Prospection Email';

$campaignStats  = $campaignService->getDashboardStats();
$prospectStats  = $prospectService->getStats();
$recentActivity = $sequenceService->getRecentActivity(8);

$totalContacts  = array_sum($prospectStats);
$activeContacts = $prospectStats['active'] ?? 0;

$totalCampaigns  = (int) ($campaignStats['total']  ?? 0);
$activeCampaigns = (int) ($campaignStats['active'] ?? 0);

$mailMode     = \ProspectionMailer::currentMode();
$isTestMode   = \ProspectionMailer::isTestMode();
$testRecipient= \ProspectionMailer::testRecipient();

$flash = Session::getFlash();
?>
<style>
.prosp-page { display:grid; gap:22px; }

.prosp-hero {
    background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 65%, #22507d 100%);
    border-radius: 16px;
    padding: 24px 20px;
    color: #fff;
    box-shadow: 0 4px 20px rgba(15,34,55,.18);
}
.prosp-hero-badge {
    display:inline-flex; align-items:center; gap:.45rem;
    font-size:.7rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
    color:#c9a84c; border:1px solid rgba(201,168,76,.35);
    background:rgba(201,168,76,.17); border-radius:999px;
    padding:.28rem .68rem; margin-bottom:.7rem;
}
.prosp-hero h1 { margin:0 0 .5rem; color:#fff; font-size:clamp(22px,4vw,28px); line-height:1.25; }
.prosp-hero p  { margin:0 0 1.1rem; color:rgba(255,255,255,.76); line-height:1.6; max-width:760px; font-size:15px; }
.prosp-hero-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
.prosp-btn-primary {
    display:inline-flex; align-items:center; gap:.45rem;
    background:#c9a84c; color:#10253c; font-weight:700; font-size:.84rem;
    border-radius:10px; padding:.52rem .92rem; text-decoration:none;
    transition:background .15s, transform .15s;
}
.prosp-btn-primary:hover { background:#b8943f; transform:translateY(-1px); color:#10253c; }
.prosp-btn-ghost {
    display:inline-flex; align-items:center; gap:.45rem;
    background:rgba(255,255,255,.12); color:#fff; font-weight:600; font-size:.84rem;
    border-radius:10px; padding:.52rem .92rem; text-decoration:none; border:1px solid rgba(255,255,255,.22);
    transition:background .15s;
}
.prosp-btn-ghost:hover { background:rgba(255,255,255,.2); color:#fff; }

.prosp-test-banner {
    background:#fef3c7; border:1px solid #fcd34d; border-radius:12px;
    padding:.7rem 1rem; display:flex; align-items:center; gap:.6rem;
    font-size:.86rem; color:#92400e;
}

.prosp-kpis {
    display:grid; grid-template-columns:repeat(2,1fr); gap:12px;
}
.prosp-kpi {
    background:#fff; border:1px solid #e2e8f0; border-radius:14px;
    padding:16px; box-shadow:0 1px 8px rgba(15,23,42,.06);
    display:flex; flex-direction:column; gap:.2rem;
}
.prosp-kpi-label { font-size:.75rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; font-weight:700; }
.prosp-kpi-value { font-size:2rem; font-weight:800; color:#0f172a; line-height:1; }
.prosp-kpi-sub   { font-size:.78rem; color:#64748b; }
.prosp-kpi-sub--green { color:#16a34a; }

.prosp-grid { display:grid; gap:12px; }

.prosp-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:16px;
    box-shadow:0 1px 8px rgba(15,23,42,.06); overflow:hidden;
}
.prosp-card-header {
    padding:.85rem 1rem; border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; justify-content:space-between; gap:.5rem;
}
.prosp-card-header-title { font-weight:700; font-size:.95rem; color:#0f172a; display:flex; align-items:center; gap:.4rem; }

.prosp-nav-link {
    display:flex; align-items:center; gap:.75rem;
    padding:.78rem 1rem; text-decoration:none; color:#0f172a;
    border-bottom:1px solid #f1f5f9; transition:background .12s;
}
.prosp-nav-link:last-child { border-bottom:none; }
.prosp-nav-link:hover { background:#f8fafc; }
.prosp-nav-icon {
    width:36px; height:36px; border-radius:50%; background:#f1f5f9;
    display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.9rem;
}
.prosp-nav-text strong { display:block; font-size:.88rem; font-weight:700; color:#0f172a; }
.prosp-nav-text span   { display:block; font-size:.74rem; color:#64748b; }
.prosp-nav-chevron { margin-left:auto; font-size:.7rem; color:#94a3b8; }

.prosp-activity-row {
    display:flex; align-items:center; gap:.75rem;
    padding:.58rem 1rem; border-bottom:1px solid #f8fafc;
}
.prosp-activity-row:last-child { border-bottom:none; }
.prosp-activity-icon { width:20px; text-align:center; flex-shrink:0; font-size:.82rem; }
.prosp-activity-text { flex-grow:1; min-width:0; }
.prosp-activity-text strong { display:block; font-size:.84rem; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.prosp-activity-text small  { font-size:.73rem; color:#64748b; }
.prosp-activity-time { font-size:.72rem; color:#94a3b8; flex-shrink:0; }

.prosp-mere { display:grid; grid-template-columns:1fr; gap:12px; }
.prosp-mere-card {
    background:#fff; border:1px solid #e2e8f0; border-radius:14px;
    padding:14px 16px; box-shadow:0 1px 8px rgba(15,23,42,.06);
}
.prosp-mere-head { display:flex; align-items:center; gap:.45rem; font-weight:700; color:#1e293b; font-size:.88rem; margin-bottom:.4rem; }
.prosp-mere-card p { margin:0; font-size:.86rem; line-height:1.58; color:#475569; }
.prosp-mere-card.motivation { border-left:4px solid #f59e0b; }
.prosp-mere-card.explanation { border-left:4px solid #3b82f6; }
.prosp-mere-card.risque      { border-left:4px solid #ef4444; }

.prosp-seeder-form { display:inline; }

@media (min-width:680px) {
    .prosp-kpis { grid-template-columns:repeat(4,1fr); }
    .prosp-mere { grid-template-columns:repeat(3,1fr); }
}
@media (min-width:900px) {
    .prosp-hero { padding:34px 36px; }
    .prosp-grid { grid-template-columns:300px 1fr; }
}
</style>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="prosp-page">

    <!-- Hero -->
    <header class="prosp-hero">
        <div class="prosp-hero-badge"><i class="fas fa-paper-plane"></i> Prospection Email</div>
        <h1>Gérez vos campagnes et séquences email</h1>
        <p>Automatisez vos relances, suivez chaque contact et ne laissez plus passer aucune opportunité.</p>
        <div class="prosp-hero-actions">
            <a href="?module=prospection&action=campaign-new" class="prosp-btn-primary">
                <i class="fas fa-plus"></i> Nouvelle campagne
            </a>
            <a href="?module=prospection&action=contacts" class="prosp-btn-ghost">
                <i class="fas fa-users"></i> Contacts
            </a>
            <form method="POST" action="?module=prospection" class="prosp-seeder-form">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="run_seeder">
                <button type="submit" class="prosp-btn-ghost" style="border:none;cursor:pointer;">
                    <i class="fas fa-flask"></i> Charger démo
                </button>
            </form>
        </div>
    </header>

    <?php if ($isTestMode): ?>
    <div class="prosp-test-banner">
        <i class="fas fa-flask"></i>
        <span>
            <strong>Mode <?= strtoupper(e($mailMode)) ?> actif</strong> —
            <?php if ($mailMode === 'log'): ?>
                Aucun email envoyé. Tous les envois sont journalisés uniquement.
            <?php else: ?>
                Emails redirigés vers <strong><?= e($testRecipient ?: 'adresse non configurée') ?></strong>.
                Pour envoyer en production : <code>MAIL_MODE=smtp</code> et <code>EMAIL_SANDBOX=false</code> dans <code>.env</code>.
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="prosp-kpis">
        <div class="prosp-kpi">
            <div class="prosp-kpi-label">Contacts</div>
            <div class="prosp-kpi-value"><?= number_format(array_sum($prospectStats)) ?></div>
            <div class="prosp-kpi-sub prosp-kpi-sub--green"><?= number_format($activeContacts) ?> actifs</div>
        </div>
        <div class="prosp-kpi">
            <div class="prosp-kpi-label">Campagnes</div>
            <div class="prosp-kpi-value"><?= $totalCampaigns ?></div>
            <div class="prosp-kpi-sub prosp-kpi-sub--green"><?= $activeCampaigns ?> actives</div>
        </div>
        <div class="prosp-kpi">
            <div class="prosp-kpi-label">En pause</div>
            <div class="prosp-kpi-value" style="color:#f59e0b;"><?= (int)($prospectStats['paused'] ?? 0) ?></div>
            <div class="prosp-kpi-sub">contacts</div>
        </div>
        <div class="prosp-kpi">
            <div class="prosp-kpi-label">Ont répondu</div>
            <div class="prosp-kpi-value" style="color:#10b981;"><?= (int)($prospectStats['replied'] ?? 0) ?></div>
            <div class="prosp-kpi-sub">contacts</div>
        </div>
    </div>

    <!-- Navigation + Activité -->
    <div class="prosp-grid">

        <!-- Navigation rapide -->
        <div class="prosp-card">
            <div class="prosp-card-header">
                <div class="prosp-card-header-title"><i class="fas fa-compass" style="color:#3b82f6;"></i> Navigation rapide</div>
            </div>
            <nav>
                <a href="?module=prospection&action=contacts" class="prosp-nav-link">
                    <div class="prosp-nav-icon"><i class="fas fa-users" style="color:#3b82f6;"></i></div>
                    <div class="prosp-nav-text">
                        <strong>Contacts</strong>
                        <span>Gérer la base de prospection</span>
                    </div>
                    <i class="fas fa-chevron-right prosp-nav-chevron"></i>
                </a>
                <a href="?module=prospection&action=campaigns" class="prosp-nav-link">
                    <div class="prosp-nav-icon"><i class="fas fa-bullhorn" style="color:#f59e0b;"></i></div>
                    <div class="prosp-nav-text">
                        <strong>Campagnes</strong>
                        <span>Créer et suivre les campagnes</span>
                    </div>
                    <i class="fas fa-chevron-right prosp-nav-chevron"></i>
                </a>
                <a href="?module=prospection&action=contact-import" class="prosp-nav-link">
                    <div class="prosp-nav-icon"><i class="fas fa-file-import" style="color:#10b981;"></i></div>
                    <div class="prosp-nav-text">
                        <strong>Importer</strong>
                        <span>Charger un fichier CSV</span>
                    </div>
                    <i class="fas fa-chevron-right prosp-nav-chevron"></i>
                </a>
                <a href="?module=prospection&action=activity" class="prosp-nav-link">
                    <div class="prosp-nav-icon"><i class="fas fa-clock-rotate-left" style="color:#64748b;"></i></div>
                    <div class="prosp-nav-text">
                        <strong>Activité</strong>
                        <span>Journal complet des événements</span>
                    </div>
                    <i class="fas fa-chevron-right prosp-nav-chevron"></i>
                </a>
            </nav>
        </div>

        <!-- Activité récente -->
        <div class="prosp-card">
            <div class="prosp-card-header">
                <div class="prosp-card-header-title"><i class="fas fa-clock-rotate-left" style="color:#64748b;"></i> Activité récente</div>
                <a href="?module=prospection&action=activity" style="font-size:.8rem;color:#64748b;text-decoration:none;">Tout voir →</a>
            </div>
            <?php if (empty($recentActivity)): ?>
            <div style="padding:2.5rem 1rem; text-align:center; color:#94a3b8;">
                <i class="fas fa-inbox fa-2x" style="opacity:.25; display:block; margin-bottom:.6rem;"></i>
                <div style="font-size:.85rem;">Aucune activité pour l'instant.</div>
                <div style="font-size:.82rem; margin-top:.3rem;">Créez une campagne pour démarrer.</div>
            </div>
            <?php else: ?>
            <?php
            $eventLabels = [
                'campaign_created' => ['label'=>'Campagne créée',    'icon'=>'fas fa-plus-circle',        'color'=>'#3b82f6'],
                'campaign_deleted' => ['label'=>'Campagne supprimée','icon'=>'fas fa-trash',              'color'=>'#ef4444'],
                'contact_enrolled' => ['label'=>'Contact inscrit',   'icon'=>'fas fa-user-plus',          'color'=>'#16a34a'],
                'contact_removed'  => ['label'=>'Contact retiré',    'icon'=>'fas fa-user-minus',         'color'=>'#f59e0b'],
                'contact_replied'  => ['label'=>'Réponse reçue',     'icon'=>'fas fa-reply',              'color'=>'#16a34a'],
                'email_sent'       => ['label'=>'Email envoyé',      'icon'=>'fas fa-paper-plane',        'color'=>'#0ea5e9'],
                'email_failed'     => ['label'=>'Échec d\'envoi',    'icon'=>'fas fa-circle-exclamation', 'color'=>'#ef4444'],
                'step_added'       => ['label'=>'Étape ajoutée',     'icon'=>'fas fa-list-check',         'color'=>'#3b82f6'],
            ];
            foreach ($recentActivity as $event):
                $ev = $eventLabels[$event['event']] ?? ['label'=>$event['event'],'icon'=>'fas fa-circle','color'=>'#94a3b8'];
            ?>
            <div class="prosp-activity-row">
                <div class="prosp-activity-icon" style="color:<?= $ev['color'] ?>;"><i class="<?= $ev['icon'] ?>"></i></div>
                <div class="prosp-activity-text">
                    <strong><?= e($ev['label']) ?></strong>
                    <small>
                        <?= $event['campaign_name'] ? e($event['campaign_name']) : '' ?>
                        <?= ($event['campaign_name'] && $event['contact_name']) ? ' · ' : '' ?>
                        <?= $event['contact_name'] ? e($event['contact_name']) : '' ?>
                    </small>
                </div>
                <div class="prosp-activity-time"><?= timeAgo($event['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>
