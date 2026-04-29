<?php
/**
 * Module CRM - Gestion des leads
 */

require_once __DIR__ . '/../../session-helper.php';
startAdminSession();

if (!isAdminLoggedIn()) {
    redirectAdmin('/admin/login');
}

// Filtre par source
$filter = $_GET['source'] ?? '';
$validSources = ['estimation', 'contact', 'telechargement', 'financement', 'avis_valeur', 'autre'];

$filters = [];
if (!empty($filter) && in_array($filter, $validSources, true)) {
    $filters['source_type'] = $filter;
}

// Récupérer les leads
$leads = LeadService::list($filters);
$totalLeads = count($leads);

// Statistiques
$statsEstimation = count(LeadService::list(['source_type' => 'estimation']));
$statsContact = count(LeadService::list(['source_type' => 'contact']));
$statsTelechargement = count(LeadService::list(['source_type' => 'telechargement']));
$statsAll = count(LeadService::list());

$pageTitle = 'CRM - Leads';
$currentModule = 'crm';
?>

<div class="legacy-admin-page">
    <header class="legacy-hero">
        <h1>CRM - Gestion des leads</h1>
        <p>Tableau de bord des leads enregistrées depuis vos formulaires.</p>
    </header>

    <section class="legacy-stats-grid" aria-label="Statistiques CRM">
        <div class="legacy-stat-card">
            <div class="legacy-stat-value"><?= $statsAll ?></div>
            <div class="legacy-stat-label">Leads totales</div>
        </div>
        <div class="legacy-stat-card">
            <div class="legacy-stat-value" style="color:#1d4ed8;"><?= $statsEstimation ?></div>
            <div class="legacy-stat-label" style="color:#1d4ed8;">Estimations</div>
        </div>
        <div class="legacy-stat-card">
            <div class="legacy-stat-value" style="color:#7c3aed;"><?= $statsContact ?></div>
            <div class="legacy-stat-label" style="color:#7c3aed;">Contacts</div>
        </div>
        <div class="legacy-stat-card">
            <div class="legacy-stat-value" style="color:#c2410c;"><?= $statsTelechargement ?></div>
            <div class="legacy-stat-label" style="color:#c2410c;">Téléchargements</div>
        </div>
    </section>

    <section class="legacy-card">
        <div class="legacy-filter-bar">
            <a href="/admin?module=crm" class="legacy-btn <?= empty($filter) ? 'legacy-btn--primary' : 'legacy-btn--secondary' ?>">
                Tous (<?= $statsAll ?>)
            </a>
            <a href="/admin?module=crm&source=estimation" class="legacy-btn <?= $filter === 'estimation' ? 'legacy-btn--primary' : 'legacy-btn--secondary' ?>">
                Estimations (<?= $statsEstimation ?>)
            </a>
            <a href="/admin?module=crm&source=contact" class="legacy-btn <?= $filter === 'contact' ? 'legacy-btn--primary' : 'legacy-btn--secondary' ?>">
                Contacts (<?= $statsContact ?>)
            </a>
            <a href="/admin?module=crm&source=telechargement" class="legacy-btn <?= $filter === 'telechargement' ? 'legacy-btn--primary' : 'legacy-btn--secondary' ?>">
                Téléchargements (<?= $statsTelechargement ?>)
            </a>
        </div>
    </section>

    <section class="legacy-card legacy-card--tight">
        <div class="legacy-card-head">
            <h2>Leads récentes</h2>
            <span class="legacy-badge legacy-badge--gray"><?= $statsAll ?> total</span>
        </div>

        <div class="legacy-table-wrap">
            <?php if (empty($leads)): ?>
                <div class="legacy-empty">
                    <p style="font-size:1.05rem;margin-bottom:.35rem;">Aucune lead pour le moment.</p>
                    <p>Les formulaires remplis s'afficheront ici.</p>
                </div>
            <?php else: ?>
                <table class="legacy-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Stage</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td><?= htmlspecialchars($lead['id']) ?></td>
                            <td style="font-weight:600;color:#0f172a;">
                                <?= htmlspecialchars($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($lead['email']) ?>">
                                    <?= htmlspecialchars($lead['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($lead['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>">
                                        <?= htmlspecialchars($lead['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="legacy-badge legacy-badge--gray">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="legacy-badge legacy-badge--gray">
                                    <?= htmlspecialchars($lead['property_type'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <span class="legacy-badge legacy-badge--blue">
                                    <?= htmlspecialchars($lead['source_type']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="legacy-badge legacy-badge--rose">
                                    <?= htmlspecialchars($lead['stage'] ?? 'non défini') ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="/admin?module=crm&action=view&id=<?= $lead['id'] ?>" class="legacy-btn legacy-btn--ghost">
                                    Détails
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <div class="legacy-note">
        <strong>Info :</strong> Les leads sont enregistrées automatiquement depuis les formulaires d'estimation, contact et autres conversions.
    </div>
</div>
