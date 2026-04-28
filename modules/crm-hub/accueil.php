<?php

declare(strict_types=1);

$__crmHubAction = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($_GET['action'] ?? '')));
if ($__crmHubAction === 'conversions' && is_file(__DIR__ . '/conversions.php')) {
    require __DIR__ . '/conversions.php';
    return;
}

$pageTitle = 'Contacts & suivi';
$pageDescription = 'Vue CRM : leads, suivi et conversions';

function renderContent(): void {
    $u = static function (array $query): string {
        return function_exists('admin_url')
            ? admin_url($query)
            : ('/admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    };

    $conversionStats = ConversionTrackingService::getTotalsByType();
    $totalConversions = (int) array_sum(array_column($conversionStats, 'total_count'));

    $leadBySource = [];
    $leadTotal = 0;
    try {
        $pred = TenantContext::crmLeadsTenantPredicate();
        $stLeads = db()->prepare(
            'SELECT source_type, COUNT(*) AS n FROM crm_leads WHERE (' . $pred['sql'] . ') GROUP BY source_type'
        );
        $stLeads->execute($pred['params']);
        if ($stLeads) {
            foreach ($stLeads->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $src = (string) ($row['source_type'] ?? 'autre');
                $leadBySource[$src] = (int) ($row['n'] ?? 0);
                $leadTotal += (int) ($row['n'] ?? 0);
            }
        }
    } catch (Throwable $e) {
        error_log('[crm-hub] crm_leads count: ' . $e->getMessage());
    }

    $leadSourceLabels = [
        'financement' => ['label' => 'Financement', 'color' => '#b45309', 'bg' => '#fffbeb'],
        'avis_valeur' => ['label' => 'Avis de valeur', 'color' => '#0e7490', 'bg' => '#ecfeff'],
        'estimation' => ['label' => 'Estimation', 'color' => '#1d4ed8', 'bg' => '#eff6ff'],
        'contact' => ['label' => 'Contact', 'color' => '#6d28d9', 'bg' => '#f5f3ff'],
        'telechargement' => ['label' => 'Téléchargement', 'color' => '#047857', 'bg' => '#ecfdf5'],
        'autre' => ['label' => 'Autre', 'color' => '#475569', 'bg' => '#f8fafc'],
    ];

    $convUrl = htmlspecialchars($u(['module' => 'crm-hub', 'action' => 'conversions']), ENT_QUOTES, 'UTF-8');
    $contactsUrl = htmlspecialchars($u(['module' => 'contacts']), ENT_QUOTES, 'UTF-8');
    ?>
    <style>
        .start-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 28px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }
        .start-hero h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin: 0 0 12px;
            line-height: 1.25;
        }
        .start-hero p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
            max-width: 640px;
            margin: 0;
        }
        .crm-hub-panel {
            background: #fff;
            border: 1px solid #e8eef7;
            border-radius: 14px;
            padding: 1.35rem 1.35rem 1.15rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
            margin-bottom: 1.75rem;
        }
        .crm-hub-panel__title {
            margin: 0 0 .4rem;
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
        }
        .crm-hub-panel__intro {
            margin: 0 0 1.15rem;
            font-size: 13px;
            color: #64748b;
            line-height: 1.55;
            max-width: 52rem;
        }
        .crm-hub-panel__intro a { color: #1a3a5c; font-weight: 600; }
        .crm-hub-lead-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
        }
        .crm-lead-total-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            border-left: 4px solid #c9a84c;
        }
        .crm-lead-total-card .crm-lead-total-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 6px;
        }
        .crm-lead-total-card .crm-lead-total-num {
            font-size: 32px;
            font-weight: 700;
            color: #0f2237;
        }
        .crm-lead-total-card .crm-lead-total-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
        }
        .crm-hub-track-foot {
            margin: 1.25rem 0 0;
            padding-top: 1rem;
            border-top: 1px solid #eef2f7;
            font-size: 13px;
            color: #64748b;
            line-height: 1.55;
        }
        .crm-hub-track-foot a {
            font-weight: 600;
            color: #1a3a5c;
            white-space: nowrap;
        }
        .crm-hub-tools-title {
            font-size: 12px;
            font-weight: 700;
            color: #8a95a3;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin: 0 0 12px;
        }
        @media (max-width: 600px) {
            .start-hero { padding: 24px 20px; }
        }
    </style>

    <div class="hub-page">
        <div class="start-hero">
            <h1>Gérez vos contacts et clients</h1>
            <p>
                Accédez à vos leads par source, puis ouvrez les outils messages, tracking ou prospection.
            </p>
        </div>

        <section class="crm-hub-panel" aria-labelledby="crm-hub-leads-title">
            <h2 id="crm-hub-leads-title" class="crm-hub-panel__title">Leads issus des formulaires</h2>
            <p class="crm-hub-panel__intro">
                Comptages <strong>crm_leads</strong> (tenant courant). Chaque carte ouvre
                <a href="<?= $contactsUrl ?>">Contacts</a> avec le filtre correspondant.
            </p>
            <div class="crm-hub-lead-grid">
                <a href="<?= $contactsUrl ?>" style="text-decoration: none; color: inherit;">
                    <div class="crm-lead-total-card">
                        <div class="crm-lead-total-label">Total leads</div>
                        <div class="crm-lead-total-num"><?= (int) $leadTotal ?></div>
                        <div class="crm-lead-total-hint">Liste complète</div>
                    </div>
                </a>
                <?php foreach ($leadSourceLabels as $srcKey => $cfg):
                    $n = (int) ($leadBySource[$srcKey] ?? 0);
                    $href = htmlspecialchars($u(['module' => 'contacts', 'source' => $srcKey]), ENT_QUOTES, 'UTF-8');
                    ?>
                <a href="<?= $href ?>" style="text-decoration: none; color: inherit;">
                    <div style="background: <?= htmlspecialchars($cfg['bg'], ENT_QUOTES, 'UTF-8') ?>; border-radius: 12px; padding: 16px 14px; border: 1px solid #e5e7eb; box-shadow: 0 1px 4px rgba(0,0,0,.04);">
                        <div style="font-size: 22px; font-weight: 700; color: <?= htmlspecialchars($cfg['color'], ENT_QUOTES, 'UTF-8') ?>;"><?= $n ?></div>
                        <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <p class="crm-hub-track-foot">
                <strong>Tracking marketing</strong> (table <code>conversion_tracking</code>, pixels / événements)&nbsp;:
                <strong><?= $totalConversions ?></strong> enregistrement<?= $totalConversions !== 1 ? 's' : '' ?> cumulé<?= $totalConversions !== 1 ? 's' : '' ?>.
                <a href="<?= $convUrl ?>">Répartition par type</a>
            </p>
        </section>

        <p class="crm-hub-tools-title">Outils complémentaires</p>
        <div class="hub-modules-grid">
            <a class="hub-module-card" href="<?= htmlspecialchars($u(['module' => 'messagerie']), ENT_QUOTES, 'UTF-8') ?>">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-envelope" aria-hidden="true"></i></div>
                    <h3>Messagerie</h3>
                </div>
                <p>Conversations et historique des échanges.</p>
                <span class="hub-module-card-action">Ouvrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
            </a>
            <a class="hub-module-card" href="<?= $convUrl ?>">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fff7ed;color:#c2410c"><i class="fas fa-chart-line" aria-hidden="true"></i></div>
                    <h3>Suivi des conversions</h3>
                </div>
                <p>Détail par type d’événement et filtres.</p>
                <span class="hub-module-card-action">Ouvrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
            </a>
            <a class="hub-module-card" href="<?= htmlspecialchars($u(['module' => 'scraper']), ENT_QUOTES, 'UTF-8') ?>">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-spider" aria-hidden="true"></i></div>
                    <h3>Scraper web</h3>
                </div>
                <p>Import automatisé de prospects.</p>
                <span class="hub-module-card-action">Ouvrir <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
            </a>
        </div>
    </div>
    <?php
}
