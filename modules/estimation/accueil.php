<?php
require_once ROOT_PATH . '/core/services/InstantEstimationService.php';

$pageTitle = 'Estimation DVF';
$pageDescription = 'Pilotage du système d’estimation instantanée et des demandes RDV.';

function renderContent() {
    $stats = [
        'requests' => 0,
        'ok' => 0,
        'blocked' => 0,
        'rdv' => 0,
    ];

    try {
        $stats['requests'] = (int) db()->query('SELECT COUNT(*) FROM estimation_requests')->fetchColumn();
        $stats['ok'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status = 'ok'")->fetchColumn();
        $stats['blocked'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status IN ('insufficient_data','low_reliability')")->fetchColumn();
        $stats['rdv'] = (int) db()->query("SELECT COUNT(*) FROM estimation_requests WHERE status = 'rdv_requested'")->fetchColumn();
    } catch (Throwable $e) {
        // tableau vide si tables non encore remplies
    }

    $latestImports = [];
    $latestRequests = [];

    try {
        $latestImports = db()->query('SELECT * FROM dvf_import_jobs ORDER BY created_at DESC LIMIT 10')->fetchAll();
    } catch (Throwable $e) {}

    try {
        $latestRequests = db()->query('SELECT * FROM estimation_requests ORDER BY created_at DESC LIMIT 20')->fetchAll();
    } catch (Throwable $e) {}
    ?>
    <div class="page-header">
        <h1><i class="fas fa-calculator page-icon"></i> HUB <span class="page-title-accent">Estimation DVF</span></h1>
        <p>Imports DVF, historique, demandes d’estimation et indicateurs de fiabilité.</p>
    </div>

    <div class="dashboard-grid" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;">
        <div class="card"><div class="card-title">Demandes totales</div><div class="card-value"><?= (int) $stats['requests'] ?></div></div>
        <div class="card"><div class="card-title">Estimations fiables</div><div class="card-value"><?= (int) $stats['ok'] ?></div></div>
        <div class="card"><div class="card-title">Estimations bloquées</div><div class="card-value"><?= (int) $stats['blocked'] ?></div></div>
        <div class="card"><div class="card-title">RDV demandés</div><div class="card-value"><?= (int) $stats['rdv'] ?></div></div>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Historique imports DVF</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead><tr><th align="left">Date</th><th align="left">Fichier</th><th align="left">Statut</th><th align="right">Valides</th><th align="right">Rejetées</th></tr></thead>
            <tbody>
            <?php if (!$latestImports): ?>
                <tr><td colspan="5" style="padding:8px 0;color:#6b7280;">Aucun import DVF enregistré.</td></tr>
            <?php else: foreach ($latestImports as $row): ?>
                <tr>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($row['source_file'] ?? '')) ?></td>
                    <td><?= e((string) ($row['status'] ?? '')) ?></td>
                    <td align="right"><?= (int) ($row['rows_valid'] ?? 0) ?></td>
                    <td align="right"><?= (int) ($row['rows_rejected'] ?? 0) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="margin-top:16px;padding:16px;">
        <h3>Demandes d’estimation (20 dernières)</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr><th align="left">Date</th><th align="left">Adresse</th><th align="left">Type</th><th align="right">Surface</th><th align="right">Comp.</th><th align="left">Statut</th></tr>
            </thead>
            <tbody>
            <?php if (!$latestRequests): ?>
                <tr><td colspan="6" style="padding:8px 0;color:#6b7280;">Aucune demande.</td></tr>
            <?php else: foreach ($latestRequests as $row): ?>
                <tr>
                    <td><?= e((string) ($row['created_at'] ?? '')) ?></td>
                    <td><?= e((string) ($row['address_normalized'] ?: $row['address_input'] ?? '')) ?></td>
                    <td><?= e((string) ($row['property_type'] ?? '')) ?></td>
                    <td align="right"><?= (int) ($row['surface'] ?? 0) ?> m²</td>
                    <td align="right"><?= (int) ($row['comparables_count'] ?? 0) ?></td>
                    <td><?= e((string) ($row['status'] ?? '')) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        <p style="margin-top:12px;color:#6b7280;">Carte Google Maps des demandes : branchez la clé JS et affichez les points lat/lng avec clustering.</p>
    </div>
    <?php
}
