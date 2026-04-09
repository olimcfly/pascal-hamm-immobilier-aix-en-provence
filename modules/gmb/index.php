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
