<?php
$pageTitle = 'Demandes de financement';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="demandes-financement-' . date('Ymd-His') . '.csv"');

    $out = fopen('php://output', 'wb');
    fputcsv($out, ['Date', 'Statut', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Type de projet', 'Secteur', 'Budget', 'Apport', 'Situation pro', 'Délai', 'Message']);
    foreach ($rows as $lead) {
        $meta = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
        fputcsv($out, [
            (string) ($lead['created_at'] ?? ''),
            LeadService::stageLabel((string) ($lead['stage'] ?? 'nouveau')),
            (string) ($lead['first_name'] ?? ''), (string) ($lead['last_name'] ?? ''),
            (string) ($lead['email'] ?? ''), (string) ($lead['phone'] ?? ''),
            (string) ($meta['type_projet'] ?? ''), (string) ($meta['secteur_recherche'] ?? ''),
            (string) ($meta['budget_estime'] ?? ''), (string) ($meta['apport_personnel'] ?? ''),
            (string) ($meta['situation_professionnelle'] ?? ''), (string) ($meta['delai_projet'] ?? ''),
            (string) ($lead['notes'] ?? ''),
        ]);
    }
    fclose($out);
    exit;
}

$leads = LeadService::list(['pipeline' => LeadService::SOURCE_FINANCEMENT]);
$stats = ['total' => count($leads), 'nouveau' => 0, 'en_cours' => 0, 'traite' => 0];
foreach ($leads as $lead) {
    $stage = (string) ($lead['stage'] ?? 'nouveau');
    if (isset($stats[$stage])) {
        $stats[$stage]++;
    }
}

function renderContent(): void
{
    global $leads, $stats;
    ?>
    <style>
    .fin-kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem}
    .fin-kpi{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
    .fin-kpi-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700;margin-bottom:.2rem}
    .fin-kpi-value{font-size:2rem;font-weight:800;color:#0f172a;line-height:1}
    .fin-kpi-sub{font-size:.78rem;color:#64748b;margin-top:.15rem}
    .fin-table-wrap{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);overflow:hidden;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
    .fin-table-header{padding:.85rem 1rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:.5rem}
    .fin-table-header-title{font-weight:700;font-size:.95rem;color:#0f172a;display:flex;align-items:center;gap:.4rem}
    .fin-table-wrap table{width:100%;border-collapse:collapse;min-width:900px}
    .fin-table-wrap th,.fin-table-wrap td{padding:.7rem .85rem;border-bottom:1px solid #f8fafc;text-align:left;font-size:.88rem}
    .fin-table-wrap th{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:700;background:#fafbfc}
    .fin-table-wrap tr:last-child td{border-bottom:none}
    .fin-meta{font-size:.78rem;color:#64748b;margin-top:.15rem}
    .fin-stage{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:700}
    .fin-stage--nouveau{background:#eff6ff;color:#1d4ed8}
    .fin-stage--en_cours{background:#fef3c7;color:#92400e}
    .fin-stage--traite{background:#dcfce7;color:#166534}
    .fin-empty{padding:3rem 1rem;text-align:center;color:#94a3b8}
    .fin-empty i{font-size:2rem;opacity:.25;display:block;margin-bottom:.6rem}
    @media(min-width:680px){.fin-kpis{grid-template-columns:repeat(4,1fr)}}
    </style>

    <div class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-hand-holding-dollar"></i> Financement</div>
            <h1>Demandes de financement</h1>
            <p>Toutes les demandes reçues depuis votre page Financement, avec leur statut de traitement.</p>
        </header>

        <div class="hub-narrative">
            <article class="hub-narrative-card hub-narrative-card--motivation">
                <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Ce que vous recevez</h3>
                <p>Chaque visiteur qui remplit votre formulaire de financement apparaît ici automatiquement, avec toutes ses informations et son projet.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--resultat">
                <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Comment traiter</h3>
                <p>Qualifiez chaque demande, notez l'avancement et exportez vos contacts en CSV pour un suivi externalisé ou CRM.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--action">
                <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> À ne pas oublier</h3>
                <p>Répondez rapidement : un lead financement contacté dans l'heure a 7x plus de chances de se convertir.</p>
            </article>
        </div>

        <!-- KPIs -->
        <div class="fin-kpis">
            <div class="fin-kpi">
                <div class="fin-kpi-label">Total reçues</div>
                <div class="fin-kpi-value"><?= $stats['total'] ?></div>
                <div class="fin-kpi-sub">demandes</div>
            </div>
            <div class="fin-kpi">
                <div class="fin-kpi-label">Nouvelles</div>
                <div class="fin-kpi-value" style="color:#1d4ed8"><?= $stats['nouveau'] ?></div>
                <div class="fin-kpi-sub">à traiter</div>
            </div>
            <div class="fin-kpi">
                <div class="fin-kpi-label">En cours</div>
                <div class="fin-kpi-value" style="color:#f59e0b"><?= $stats['en_cours'] ?></div>
                <div class="fin-kpi-sub">en traitement</div>
            </div>
            <div class="fin-kpi">
                <div class="fin-kpi-label">Traitées</div>
                <div class="fin-kpi-value" style="color:#16a34a"><?= $stats['traite'] ?></div>
                <div class="fin-kpi-sub">terminées</div>
            </div>
        </div>

        <!-- Table -->
        <div class="fin-table-wrap">
            <div class="fin-table-header">
                <div class="fin-table-header-title">
                    <i class="fas fa-list" style="color:#64748b"></i> Liste des demandes
                </div>
                <a href="?module=financement&export=csv" class="hub-btn hub-btn--sm">
                    <i class="fas fa-download"></i> Exporter CSV
                </a>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th><th>Statut</th><th>Contact</th><th>Projet</th>
                        <th>Secteur</th><th>Budget / Apport</th><th>Situation / Délai</th><th>Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$leads): ?>
                        <tr><td colspan="8">
                            <div class="fin-empty">
                                <i class="fas fa-inbox"></i>
                                <div style="font-size:.88rem">Aucune demande de financement pour le moment.</div>
                                <div style="font-size:.82rem;margin-top:.3rem;color:#94a3b8">Les demandes de votre page financement apparaîtront ici automatiquement.</div>
                            </div>
                        </td></tr>
                    <?php endif; ?>
                    <?php foreach ($leads as $lead):
                        $meta  = is_array($lead['metadata'] ?? null) ? $lead['metadata'] : [];
                        $stage = (string) ($lead['stage'] ?? 'nouveau');
                        ?>
                        <tr>
                            <td style="white-space:nowrap"><?= e(date('d/m/Y', strtotime((string) $lead['created_at']))) ?><div class="fin-meta"><?= e(date('H:i', strtotime((string) $lead['created_at']))) ?></div></td>
                            <td><span class="fin-stage fin-stage--<?= e($stage) ?>"><?= e(LeadService::stageLabel($stage)) ?></span></td>
                            <td>
                                <strong><?= e(trim((string) ($lead['first_name'] ?? '') . ' ' . (string) ($lead['last_name'] ?? ''))) ?></strong>
                                <div class="fin-meta"><?= e((string) ($lead['email'] ?? '')) ?></div>
                                <div class="fin-meta"><?= e((string) ($lead['phone'] ?? '')) ?></div>
                            </td>
                            <td><?= e((string) ($meta['type_projet'] ?? ($lead['intent'] ?? '—'))) ?></td>
                            <td><?= e((string) ($meta['secteur_recherche'] ?? '—')) ?></td>
                            <td>
                                <div><?= e((string) ($meta['budget_estime'] ?? '—')) ?></div>
                                <div class="fin-meta">Apport : <?= e((string) ($meta['apport_personnel'] ?? '—')) ?></div>
                            </td>
                            <td>
                                <div><?= e((string) ($meta['situation_professionnelle'] ?? '—')) ?></div>
                                <div class="fin-meta">Délai : <?= e((string) ($meta['delai_projet'] ?? '—')) ?></div>
                            </td>
                            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e((string) ($lead['notes'] ?? '')) ?>"><?= e((string) ($lead['notes'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <?php
}
