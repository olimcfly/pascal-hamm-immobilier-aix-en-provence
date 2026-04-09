<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/includes/GmbService.php';

$user = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$syncJob = $service->getLatestSyncJob();

$statusLabelMap = [
    'pending' => 'En attente',
    'running' => 'En cours',
    'done' => 'Terminé',
    'error' => 'Erreur',
];

$currentStatus = (string) ($syncJob['status'] ?? 'pending');
$currentStatusLabel = $statusLabelMap[$currentStatus] ?? ucfirst($currentStatus);
$jobId = isset($syncJob['id']) && $syncJob['id'] !== null ? (int) $syncJob['id'] : null;
$updatedAt = $syncJob['updated_at'] ?? null;
?>
<div class="gmb-dashboard">
    <div class="gmb-page-header">
        <div>
            <h1><span class="gmb-google-icon" aria-hidden="true">📍</span> Hub Google My Business</h1>
            <p>Pilotez votre présence locale : fiche, avis, demandes d'avis et statistiques.</p>
            <p class="gmb-version-note">
                Interface GMB v2 — si l'affichage paraît ancien, forcez un rechargement du navigateur (Ctrl/Cmd + Shift + R).
            </p>
        </div>
    </div>

    <section class="gmb-panel gmb-sync-status-panel">
        <div class="gmb-panel-head">
            <h2>État de synchronisation GMB</h2>
            <button class="btn-gmb" data-action="sync-fiche">Lancer une synchronisation</button>
        </div>
        <p>
            Statut :
            <span class="gmb-sync-badge gmb-sync-<?= htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8') ?>" data-gmb-sync-status>
                <?= htmlspecialchars($currentStatusLabel, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </p>
        <p>Job #<span data-gmb-sync-job><?= $jobId !== null ? (int) $jobId : '-' ?></span></p>
        <p>Dernière mise à jour : <span data-gmb-sync-updated><?= htmlspecialchars((string) ($updatedAt ?? 'Jamais'), ENT_QUOTES, 'UTF-8') ?></span></p>
        <p class="gmb-sync-error" data-gmb-sync-error></p>
    </section>

    <div class="gmb-cards-grid">
        <a class="gmb-card" href="/admin?module=gmb&view=fiche">
            <h3>Ma fiche GMB</h3>
            <p>Synchronisez et mettez à jour vos informations locales.</p>
        </a>
        <a class="gmb-card" href="/admin?module=gmb&view=avis">
            <h3>Avis clients</h3>
            <p>Consultez et répondez rapidement à vos avis Google.</p>
        </a>
        <a class="gmb-card" href="/admin?module=gmb&view=demande-avis">
            <h3>Demande d'avis automatique</h3>
            <p>Envoyez des demandes post-transaction par email/SMS.</p>
        </a>
        <a class="gmb-card" href="/admin?module=gmb&view=statistiques">
            <h3>Statistiques GMB</h3>
            <p>Suivez impressions, clics site, appels et itinéraires.</p>
        </a>
    </div>
</div>
<<<<<<< HEAD

<section class="gmb-panel gmb-sync-status-panel">
    <div class="gmb-panel-head">
        <h2>État de synchronisation GMB</h2>
        <button class="btn-gmb" data-action="sync-fiche">Lancer une synchronisation</button>
    </div>
    <p>
        Statut :
        <span class="gmb-sync-badge gmb-sync-<?= htmlspecialchars($currentStatus, ENT_QUOTES, 'UTF-8') ?>" data-gmb-sync-status>
            <?= htmlspecialchars($currentStatusLabel, ENT_QUOTES, 'UTF-8') ?>
        </span>
    </p>
    <p>Job #<span data-gmb-sync-job><?= $jobId !== null ? (int) $jobId : '-' ?></span></p>
    <p>Dernière mise à jour : <span data-gmb-sync-updated><?= htmlspecialchars((string) ($updatedAt ?? 'Jamais'), ENT_QUOTES, 'UTF-8') ?></span></p>
    <p class="gmb-sync-error" data-gmb-sync-error></p>
</section>

<?php
try {
    $_gpdo   = db();
    $_avis_total = (int) $_gpdo->query("SELECT COUNT(*) FROM courtier_reviews")->fetchColumn();
    $_avis_mois  = (int) $_gpdo->query("SELECT COUNT(*) FROM courtier_reviews WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();
    $_avis_note  = $_gpdo->query("SELECT ROUND(AVG(rating),1) FROM courtier_reviews WHERE rating > 0")->fetchColumn();
    $_pub_gmb    = (int) $_gpdo->query("SELECT COUNT(*) FROM blog_publications WHERE reseau='gmb'")->fetchColumn();
    $_pub_gmb_m  = (int) $_gpdo->query("SELECT COUNT(*) FROM blog_publications WHERE reseau='gmb' AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();
} catch (Exception $e) {
    $_avis_total = $_avis_mois = $_pub_gmb = $_pub_gmb_m = 0;
    $_avis_note = null;
}
?>
<div class="db-kpi-grid" style="margin-bottom:24px">

    <a href="/admin?module=gmb&view=avis" class="db-kpi accent-gold">
        <div class="db-kpi-icon">⭐</div>
        <div class="db-kpi-val"><?= $_avis_total ?></div>
        <div class="db-kpi-label">Avis clients</div>
        <div class="db-kpi-sub"><?= $_avis_note ? 'Note moyenne : ' . $_avis_note . '/5' : 'Aucune note' ?></div>
    </a>

    <a href="/admin?module=gmb&view=avis" class="db-kpi accent-green">
        <div class="db-kpi-icon">🆕</div>
        <div class="db-kpi-val"><?= $_avis_mois ?></div>
        <div class="db-kpi-label">Nouveaux avis</div>
        <div class="db-kpi-sub">Ce mois-ci</div>
    </a>

    <a href="/admin?module=redaction&action=pool_gmb" class="db-kpi" style="border-left-color:#ea4335">
        <div class="db-kpi-icon">📝</div>
        <div class="db-kpi-val"><?= $_pub_gmb ?></div>
        <div class="db-kpi-label">Posts GMB rédigés</div>
        <div class="db-kpi-sub"><?= $_pub_gmb_m ?> ce mois · min. 1/semaine recommandé</div>
    </a>

    <div class="db-kpi <?= $currentStatus === 'done' ? 'accent-green' : ($currentStatus === 'error' ? 'accent-red' : 'accent-blue') ?>">
        <div class="db-kpi-icon"><?= $currentStatus === 'done' ? '✅' : ($currentStatus === 'error' ? '❌' : '🔄') ?></div>
        <div class="db-kpi-val" style="font-size:1.1rem;font-weight:700"><?= htmlspecialchars($currentStatusLabel) ?></div>
        <div class="db-kpi-label">Synchro GMB</div>
        <div class="db-kpi-sub"><?= $updatedAt ? 'Màj : ' . date('d/m H:i', strtotime($updatedAt)) : 'Jamais synchronisé' ?></div>
    </div>

</div>

<div class="gmb-cards-grid">
    <a class="gmb-card" href="/admin?module=gmb&view=fiche">
        <h3>Ma fiche GMB</h3>
        <p>Synchronisez et mettez à jour vos informations locales.</p>
    </a>
    <a class="gmb-card" href="/admin?module=gmb&view=avis">
        <h3>Avis clients</h3>
        <p>Consultez et répondez rapidement à vos avis Google.</p>
    </a>
    <a class="gmb-card" href="/admin?module=gmb&view=demande-avis">
        <h3>Demande d'avis automatique</h3>
        <p>Envoyez des demandes post-transaction par email/SMS.</p>
    </a>
    <a class="gmb-card" href="/admin?module=gmb&view=statistiques">
        <h3>Statistiques GMB</h3>
        <p>Suivez impressions, clics site, appels et itinéraires.</p>
    </a>
</div>
=======
>>>>>>> 75fa36ef774fcc8396c746e9683cf6fab941b202
