<?php

declare(strict_types=1);

$__crmHubAction = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) ($_GET['action'] ?? '')));
if ($__crmHubAction === 'conversions' && is_file(__DIR__ . '/conversions.php')) {
    require __DIR__ . '/conversions.php';
    return;
}

$pageTitle = 'CRM & Contacts';
$pageDescription = 'Gestion complète des contacts';

function renderContent(): void {
    $u = static function (array $query): string {
        return function_exists('admin_url')
            ? admin_url($query)
            : ('/admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    };
    ?>
    <style>
        .start-hero {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }
        .start-hero-badge {
            display: inline-block;
            background: rgba(201,168,76,.2);
            color: #c9a84c;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 14px;
            border: 1px solid rgba(201,168,76,.35);
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
        .start-steps-title {
            font-size: 12px;
            font-weight: 700;
            color: #8a95a3;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }
        .start-steps {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 32px;
        }
        .start-step {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            background: #fff;
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
            text-decoration: none;
            color: inherit;
            border-left: 4px solid #e8ecf0;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }
        .start-step:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
            border-color: #c9a84c;
        }
        .start-step-num {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
        }
        .start-step-body { flex: 1; }
        .start-step-label {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .start-step-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }
        .start-step-arrow {
            flex-shrink: 0;
            color: #c9a84c;
            font-size: 16px;
            margin-top: 8px;
        }
        @media (max-width: 600px) {
            .start-hero { padding: 24px 20px; }
            .start-step { flex-wrap: wrap; }
        }
    </style>

    <div class="start-hero">
        <div class="start-hero-badge">CRM & Contacts</div>
        <h1>Gérez vos contacts et clients</h1>
        <p>
            Centralisez vos contacts, gérez vos conversations et importez des prospects
            depuis le web avec notre outil de scraping intégré.
        </p>
    </div>

    <!-- Tableau de bord des conversions -->
    <div style="margin-bottom: 40px;">
        <div class="start-steps-title">Conversions & Suivi en temps réel</div>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 16px; max-width: 720px; line-height: 1.55">
            Ces chiffres viennent de la table <strong>conversion_tracking</strong> (suivi marketing / pixels).
            Les formulaires <strong>financement</strong>, <strong>avis de valeur</strong>, contact, estimation enregistrent surtout des <strong>leads CRM</strong> (table <code>crm_leads</code>) : voir la section suivante et la page
            <a href="/admin?module=contacts" style="color: #1a3a5c; font-weight: 600">Contacts</a>.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <?php
            // Récupérer les stats de conversions
            $conversionStats = ConversionTrackingService::getTotalsByType();
            $totalConversions = array_sum(array_column($conversionStats, 'total_count'));

            // Définir les labels et couleurs pour chaque type
            $conversionLabels = [
                'estimation_gratuite_simple' => ['label' => 'Estimations Simples', 'icon' => '📊', 'color' => '#3b82f6'],
                'rapport_telechargement' => ['label' => 'Rapports DL', 'icon' => '📄', 'color' => '#10b981'],
                'rdv_demande' => ['label' => 'Demandes RDV', 'icon' => '📅', 'color' => '#f59e0b'],
                'contact_formulaire' => ['label' => 'Contacts Form', 'icon' => '💬', 'color' => '#8b5cf6'],
                'guide_gratuit_telechargement' => ['label' => 'Guides Gratuits', 'icon' => '📚', 'color' => '#ec4899'],
                'guide_payant_telechargement' => ['label' => 'Guides Payants', 'icon' => '💳', 'color' => '#ef4444'],
            ];

            // Afficher une carte pour chaque type de conversion
            foreach ($conversionLabels as $type => $config) {
                $stat = array_values(array_filter($conversionStats, fn($s) => $s['conversion_type'] === $type))[0] ?? ['total_count' => 0, 'with_email_count' => 0];
                ?>
                <div style="background: white; border-radius: 12px; padding: 20px; border-left: 4px solid <?= $config['color'] ?>; box-shadow: 0 1px 6px rgba(0,0,0,.07);">
                    <div style="font-size: 24px; margin-bottom: 8px;"><?= $config['icon'] ?></div>
                    <div style="font-size: 24px; font-weight: 700; color: <?= $config['color'] ?>; margin-bottom: 4px;">
                        <?= $stat['total_count'] ?? 0 ?>
                    </div>
                    <div style="font-size: 13px; color: #64748b;">
                        <?= $config['label'] ?>
                    </div>
                    <div style="font-size: 12px; color: #cbd5e1; margin-top: 8px;">
                        Avec contact: <?= $stat['with_email_count'] ?? 0 ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div style="background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 12px; padding: 20px; color: white;">
            <div style="font-size: 14px; font-weight: 700; color: #c9a84c; margin-bottom: 8px;">📈 TOTAL CONVERSIONS</div>
            <div style="font-size: 36px; font-weight: 700;"><?= $totalConversions ?></div>
            <div style="font-size: 13px; color: rgba(255,255,255,.7); margin-top: 8px;">
                Toutes conversions confondues depuis le début
            </div>
        </div>
    </div>

    <?php
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
    ?>
    <div style="margin-bottom: 40px;">
        <div class="start-steps-title">Leads CRM (formulaires → crm_leads)</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 16px;">
            <a href="/admin?module=contacts" style="text-decoration: none; color: inherit;">
                <div style="background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%); border-radius: 12px; padding: 18px 16px; color: #fff; box-shadow: 0 1px 8px rgba(0,0,0,.08);">
                    <div style="font-size: 11px; font-weight: 700; color: #c9a84c; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px;">Total leads</div>
                    <div style="font-size: 32px; font-weight: 700;"><?= (int) $leadTotal ?></div>
                    <div style="font-size: 12px; color: rgba(255,255,255,.75); margin-top: 6px;">Voir la liste complète</div>
                </div>
            </a>
            <?php foreach ($leadSourceLabels as $srcKey => $cfg):
                $n = (int) ($leadBySource[$srcKey] ?? 0);
                ?>
            <a href="/admin?module=contacts&amp;source=<?= htmlspecialchars($srcKey, ENT_QUOTES, 'UTF-8') ?>" style="text-decoration: none; color: inherit;">
                <div style="background: <?= htmlspecialchars($cfg['bg'], ENT_QUOTES, 'UTF-8') ?>; border-radius: 12px; padding: 16px 14px; border: 1px solid #e5e7eb; box-shadow: 0 1px 4px rgba(0,0,0,.04);">
                    <div style="font-size: 22px; font-weight: 700; color: <?= htmlspecialchars($cfg['color'], ENT_QUOTES, 'UTF-8') ?>;"><?= $n ?></div>
                    <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($cfg['label'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="start-steps-title">Les modules de cette section</div>
    <div class="start-steps">

        <a href="/admin/?module=contacts" class="start-step">
            <div class="start-step-num">1</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-address-book" style="color:#3b82f6;margin-right:6px;"></i>Contacts</div>
                <div class="start-step-desc">Centralisez tous vos contacts et informations client en un seul endroit. Importez, exportez et gérez facilement votre base de données.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=messagerie" class="start-step">
            <div class="start-step-num">2</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-envelope" style="color:#10b981;margin-right:6px;"></i>Messagerie</div>
                <div class="start-step-desc">Maintenez une communication directe avec vos clients. Gérez vos conversations email et gardez l'historique de tous les échanges.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=crm-hub&action=conversions" class="start-step">
            <div class="start-step-num">3</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-chart-line" style="color:#f59e0b;margin-right:6px;"></i>Suivi des Conversions</div>
                <div class="start-step-desc">Trackez toutes les conversions: estimations, téléchargements, RDV, contacts et guides. Visualisez les statistiques en temps réel.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

        <a href="/admin/?module=scraper" class="start-step">
            <div class="start-step-num">4</div>
            <div class="start-step-body">
                <div class="start-step-label"><i class="fas fa-spider" style="color:#10b981;margin-right:6px;"></i>Web Scraper</div>
                <div class="start-step-desc">Crawler le web pour trouver et importer automatiquement des prospects. Gagnez du temps en automatisant la prospection.</div>
            </div>
            <div class="start-step-arrow"><i class="fas fa-chevron-right"></i></div>
        </a>

    </div>
    <?php
}
