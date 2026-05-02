<?php
$pageTitle = 'Estimation DVF';
$pageDescription = 'Pilotez la source DVF, les textes publics et les demandes d’estimation.';

$settings = DvfEstimatorService::sourceConfiguration();
$sourceInfo = DvfEstimatorService::sourceInfo();

$feedback = null;
$feedbackType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['estimation_admin_action'])) {
    if (!empty($_POST['csrf_token']) && hash_equals(csrfToken(), (string) $_POST['csrf_token'])) {
        $action = (string) $_POST['estimation_admin_action'];

        if ($action === 'replace_csv' && isset($_FILES['dvf_csv'])) {
            $feedback = DvfEstimatorService::replaceActiveCsvUpload($_FILES['dvf_csv']);
            $feedbackType = !empty($feedback['ok']) ? 'success' : 'danger';
            $sourceInfo = DvfEstimatorService::sourceInfo();
        } elseif ($action === 'legacy_import_csv' && isset($_FILES['dvf_csv'])) {
            $feedback = DvfImportService::importCsv($_FILES['dvf_csv']);
            $feedbackType = !empty($feedback['ok']) ? 'success' : 'danger';
            $importStats = DvfEstimatorService::importStats();
        } elseif ($action === 'save_settings') {
            $keys = [
                'estimation_dvf_source_mode',
                'estimation_dvf_api_provider',
                'estimation_dvf_api_endpoint',
                'estimation_dvf_api_key',
                'estimation_dvf_api_trial_days',
                'estimation_dvf_api_trial_note',
                'estimation_home_title',
                'estimation_home_subtitle',
                'estimation_home_disclaimer',
                'estimation_home_cta_label',
                'estimation_home_hints',
                'estimation_result_title',
                'estimation_result_intro',
                'estimation_result_disclaimer',
                'estimation_result_heading',
                'estimation_result_primary_cta_label',
                'estimation_result_primary_cta_url',
                'estimation_result_secondary_cta_label',
                'estimation_result_secondary_cta_url',
            ];

            $payload = [];
            foreach ($keys as $key) {
                $payload[$key] = trim((string) ($_POST[$key] ?? ''));
            }

            if ($payload['estimation_dvf_source_mode'] === '') {
                $payload['estimation_dvf_source_mode'] = 'file';
            }
            if ($payload['estimation_dvf_api_trial_days'] === '') {
                $payload['estimation_dvf_api_trial_days'] = '30';
            }
            if ($payload['estimation_dvf_api_provider'] === '') {
                $payload['estimation_dvf_api_provider'] = 'DVF API';
            }
            if ($payload['estimation_result_primary_cta_url'] === '') {
                $payload['estimation_result_primary_cta_url'] = '/prendre-rendez-vous';
            }
            if ($payload['estimation_result_secondary_cta_url'] === '') {
                $payload['estimation_result_secondary_cta_url'] = '/estimation-gratuite';
            }
            if ($payload['estimation_result_heading'] === '') {
                $payload['estimation_result_heading'] = 'Obtenir une estimation précise';
            }

            $ok = saveSettingsBatch($payload);
            if ($ok && (string) $payload['estimation_dvf_source_mode'] === 'api' && trim((string) setting('estimation_dvf_api_trial_started_at', '')) === '') {
                saveSetting('estimation_dvf_api_trial_started_at', date('Y-m-d H:i:s'));
            }

            $feedback = [
                'ok' => $ok,
                'message' => $ok ? 'Réglages estimation enregistrés.' : 'Impossible d’enregistrer les réglages.',
            ];
            $feedbackType = $ok ? 'success' : 'danger';
            $settings = DvfEstimatorService::sourceConfiguration();
        } elseif ($action === 'clear_cache') {
            $deleted = DvfEstimatorService::clearCsvCache();
            $feedback = [
                'ok' => true,
                'message' => $deleted > 0
                    ? $deleted . ' fichier(s) de cache supprimé(s).'
                    : 'Aucun cache à supprimer.',
            ];
            $feedbackType = 'success';
            $sourceInfo = DvfEstimatorService::sourceInfo();
        } else {
            $feedback = ['ok' => false, 'message' => 'Action inconnue.'];
            $feedbackType = 'danger';
        }
    } else {
        $feedback = ['ok' => false, 'message' => 'Token CSRF invalide.'];
        $feedbackType = 'danger';
    }
}

$filters = [
    'city' => trim((string) ($_GET['city'] ?? '')),
    'property_type' => trim((string) ($_GET['property_type'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
];

$requests = DvfEstimatorService::recentRequests($filters);
$requests = is_array($requests) ? $requests : [];
$importStats = DvfEstimatorService::importStats();
$importStats = is_array($importStats) ? $importStats : ['runs' => [], 'total_rows' => 0];
$sourceInfo = is_array($sourceInfo) ? $sourceInfo : [];
$settings = is_array($settings) ? $settings : [];

function renderContent() {
    global $feedback, $feedbackType, $requests, $importStats, $filters, $settings, $sourceInfo;
    $requests = is_array($requests) ? $requests : [];
    $importStats = is_array($importStats) ? $importStats : ['runs' => [], 'total_rows' => 0];
    $sourceInfo = is_array($sourceInfo) ? $sourceInfo : [];
    $settings = is_array($settings) ? $settings : [];
    ?>
    <style>
    .dvf-module .page-header {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 36px 40px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .dvf-module .page-header h1 {
        color: #fff;
        margin: 0 0 12px;
        line-height: 1.25;
        font-size: 28px;
    }
    .dvf-module .page-header p {
        color: rgba(255,255,255,.72);
        max-width: 760px;
        line-height: 1.65;
        margin: 0;
        font-size: 15px;
    }
    .dvf-module .page-header .page-title-accent { color: #c9a84c; }
    .dvf-module .card,
    .dvf-module .cards-container .card {
        border-radius: 12px;
        border: 1px solid #e8ecf0;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
    }
    .dvf-module .cards-container {
        display: grid;
        gap: 14px;
    }
    .dvf-module .cards-container > .card {
        background: #fff;
    }
    .dvf-module .card h3 {
        color: #1e293b;
        margin-top: 0;
    }
    .dvf-module .card p,
    .dvf-module .card label,
    .dvf-module .card small {
        color: #64748b;
    }
    .dvf-module .card strong {
        color: #0f2237;
    }
    .dvf-module .section-title {
        font-size: 12px;
        font-weight: 700;
        color: #8a95a3;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin: 22px 0 12px;
    }
    .dvf-module .dvf-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: space-between;
        align-items: center;
    }
    .dvf-module .dvf-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .dvf-module .dvf-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,.15);
        font-size: 12px;
        font-weight: 600;
    }
    .dvf-module .dvf-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #f8fafc;
        border: 1px solid #e8ecf0;
        border-left: 4px solid #c9a84c;
        border-radius: 12px;
        padding: 14px 16px;
    }
    .dvf-module .dvf-note strong {
        display: block;
        margin-bottom: 4px;
    }
    .dvf-module .dvf-mini-cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 18px;
        border-radius: 10px;
        background: #c9a84c;
        color: #0f2237;
        font-weight: 700;
        text-decoration: none;
    }
    .dvf-module .dvf-mini-cta:hover { background: #b8943d; }
    .dvf-module .dvf-section-label {
        font-size: 12px;
        font-weight: 700;
        color: #8a95a3;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin: 18px 0 12px;
    }
    .dvf-module .dvf-text-panel {
        background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        border: 1px solid #e6edf5;
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .dvf-module .dvf-text-panel + .dvf-text-panel {
        margin-top: 14px;
    }
    .dvf-module .dvf-text-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .dvf-module .dvf-text-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #f1f5f9;
        color: #1a3a5c;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    .dvf-module .dvf-text-head h4 {
        margin: 0;
        font-size: 15px;
        color: #0f2237;
        line-height: 1.25;
    }
    .dvf-module .dvf-text-head p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }
    .dvf-module .dvf-text-panel .form-group {
        margin-bottom: 12px;
    }
    .dvf-module .dvf-text-panel .form-group label {
        color: #334155;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .dvf-module .dvf-text-panel input[type="text"],
    .dvf-module .dvf-text-panel input[type="url"],
    .dvf-module .dvf-text-panel input[type="password"],
    .dvf-module .dvf-text-panel input[type="number"],
    .dvf-module .dvf-text-panel textarea,
    .dvf-module .dvf-text-panel select {
        border: 1px solid #dbe4ee;
        background: #fff;
        border-radius: 10px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
    }
    .dvf-module .dvf-text-panel textarea::placeholder,
    .dvf-module .dvf-text-panel input::placeholder {
        color: #94a3b8;
        opacity: 1;
    }
    .dvf-module .dvf-text-panel .field-hint {
        color: #94a3b8;
    }
    @media (max-width: 900px) {
        .dvf-module .page-header { padding: 24px 20px; }
        .dvf-module .page-header h1 { font-size: 24px; }
        .dvf-module .cards-container { grid-template-columns: 1fr !important; }
    }
    </style>

    <div class="dvf-module">
    <div class="page-header">
        <h1><i class="fas fa-chart-area page-icon"></i> Estimation <span class="page-title-accent">DVF</span></h1>
        <p>Module de paramétrage, import DVF, textes publics et suivi des demandes.</p>
    </div>

    <div class="dvf-pill-row" style="margin-bottom:16px;">
        <span class="dvf-pill">CSV actif</span>
        <span class="dvf-pill">API optionnelle</span>
        <span class="dvf-pill">Textes public</span>
        <span class="dvf-pill">Demandes</span>
    </div>

    <?php if ($feedback): ?>
        <div class="card" style="padding:1rem;border-left:4px solid <?= $feedbackType === 'success' ? '#10b981' : '#ef4444' ?>;margin-bottom:1rem;">
            <?= e((string) $feedback['message']) ?>
        </div>
    <?php endif; ?>

    <div class="cards-container" style="grid-template-columns:repeat(4,minmax(0,1fr));margin-bottom:1.25rem;">
        <div class="card"><h3>Données DVF</h3><p><strong><?= number_format((int) ($importStats['total_rows'] ?? 0), 0, ',', ' ') ?></strong> lignes exploitées</p></div>
        <div class="card"><h3>Demandes</h3><p><strong><?= number_format(count($requests), 0, ',', ' ') ?></strong> demandes filtrées</p></div>
        <div class="card"><h3>Imports récents</h3><p><strong><?= number_format(count($importStats['runs'] ?? []), 0, ',', ' ') ?></strong> runs SQL</p></div>
        <div class="card"><h3>Cache CSV</h3><p><strong><?= number_format((int) ($sourceInfo['cache_files'] ?? 0), 0, ',', ' ') ?></strong> fichier(s)</p></div>
    </div>

    <div class="card dvf-text-panel" id="dvf-source" style="padding:1rem;margin-bottom:1rem;">
        <h3>Source DVF</h3>
        <p style="margin:.25rem 0 1rem;color:#6b7280">
            Source active : <strong><?= e((string) $sourceInfo['mode']) ?></strong> ·
            CSV : <?= !empty($sourceInfo['csv_exists']) ? 'présent' : 'absent' ?> ·
            Dernière modification : <?= e((string) ($sourceInfo['csv_mtime'] ?? '—')) ?>
        </p>
        <form method="POST" enctype="multipart/form-data" style="display:grid;gap:1rem;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="replace_csv">
            <div class="form-group">
                <label>Remplacer le fichier DVF actif</label>
                <input type="file" name="dvf_csv" accept=".csv,text/csv" required>
                <small class="field-hint">Le fichier sera copié dans `storage/dvf/dvf.csv` et le cache local sera vidé.</small>
            </div>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <button type="submit" class="btn btn--primary">Remplacer le CSV actif</button>
                <span style="color:#6b7280;font-size:.9rem;">L’API peut être présentée comme gratuite au début puis payante par requête selon votre contrat.</span>
            </div>
        </form>
        <form method="POST" style="margin-top:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="clear_cache">
            <button type="submit" class="btn btn--outline">Vider le cache CSV</button>
            <small class="field-hint">Utile après remplacement du fichier ou modification des paramètres.</small>
        </form>
    </div>

    <div class="card dvf-text-panel" style="padding:1rem;margin-bottom:1rem;" id="dvf-settings">
        <h3>Paramétrage du moteur</h3>
        <form method="POST" style="display:grid;gap:1rem;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="save_settings">

            <div class="form-row">
                <div class="form-group">
                    <label>Source DVF</label>
                    <select name="estimation_dvf_source_mode" class="form-control">
                        <option value="file" <?= ($settings['source_mode'] ?? 'file') === 'file' ? 'selected' : '' ?>>Fichier CSV local</option>
                        <option value="api" <?= ($settings['source_mode'] ?? 'file') === 'api' ? 'selected' : '' ?>>API DVF externe</option>
                    </select>
                    <small class="field-hint">Le mode API est prévu pour une intégration future ou un fournisseur tiers.</small>
                </div>
                <div class="form-group">
                    <label>Nom du fournisseur API</label>
                    <input type="text" name="estimation_dvf_api_provider" class="form-control" value="<?= e((string) ($settings['api_provider'] ?? 'DVF API')) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>URL de l’API DVF</label>
                    <input type="url" name="estimation_dvf_api_endpoint" class="form-control" value="<?= e((string) ($settings['api_endpoint'] ?? '')) ?>" placeholder="https://…">
                </div>
                <div class="form-group">
                    <label>Clé API DVF</label>
                    <input type="password" name="estimation_dvf_api_key" class="form-control" value="<?= e((string) ($settings['api_key'] ?? '')) ?>" placeholder="Clé API…">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Durée de l’essai gratuit (jours)</label>
                    <input type="number" name="estimation_dvf_api_trial_days" class="form-control" min="0" value="<?= e((string) ($settings['api_trial_days'] ?? 30)) ?>">
                </div>
                <div class="form-group">
                    <label>Note commerciale</label>
                    <input type="text" name="estimation_dvf_api_trial_note" class="form-control" value="<?= e((string) ($settings['api_trial_note'] ?? '')) ?>" placeholder="Gratuit 30 jours puis payant par requête">
                </div>
            </div>

            <div class="card" style="padding:1rem;background:#f8fafc;border:1px solid #e5e7eb;">
                <strong>Note métier</strong>
                <p style="margin:.35rem 0 0;color:#6b7280;">
                    Si vous activez une API externe, prévoyez un essai gratuit limité puis une facturation par requête.
                    Le moteur garde le CSV local comme filet de sécurité tant que le flux API n’est pas prêt.
                </p>
            </div>

            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn--primary">Enregistrer le moteur</button>
            </div>
        </form>
    </div>

    <div class="card dvf-text-panel" style="padding:1rem;margin-bottom:1rem;" id="dvf-texts">
        <div class="dvf-text-head">
            <div class="dvf-text-icon" aria-hidden="true"><i class="fas fa-pen-nib"></i></div>
            <div>
                <h4>Personnalisation de la page d’entrée</h4>
                <p>Les champs ci-dessous pilotent le texte affiché au propriétaire sur la page publique.</p>
            </div>
        </div>
        <form method="POST" style="display:grid;gap:1rem;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="save_settings">
            <div class="form-group">
                <label>Titre principal</label>
                <input type="text" name="estimation_home_title" class="form-control" value="<?= e((string) ($settings['home_title'] ?? '')) ?>" placeholder="Ex : Estimation gratuite de votre bien immobilier">
            </div>
            <div class="form-group">
                <label>Sous-titre</label>
                <textarea name="estimation_home_subtitle" class="form-control" rows="3" placeholder="Ex : Basée sur les ventes réelles DVF, instantané, sans inscription"><?= e((string) ($settings['home_subtitle'] ?? '')) ?></textarea>
            </div>
            <div class="form-group">
                <label>Disclaimer</label>
                <textarea name="estimation_home_disclaimer" class="form-control" rows="3" placeholder="Ex : Cette estimation est indicative et non contractuelle."><?= e((string) ($settings['home_disclaimer'] ?? '')) ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Texte du bouton</label>
                    <input type="text" name="estimation_home_cta_label" class="form-control" value="<?= e((string) ($settings['home_cta_label'] ?? '')) ?>" placeholder="Ex : Obtenir mon estimation gratuite">
                </div>
                <div class="form-group">
                    <label>Micro-texte de réassurance</label>
                    <input type="text" name="estimation_home_hints" class="form-control" value="<?= e((string) ($settings['home_hints'] ?? '')) ?>" placeholder="Ex : Résultat instantané · Sans inscription">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn--primary">Enregistrer la page d’entrée</button>
            </div>
        </form>
    </div>

    <div class="card dvf-text-panel" style="padding:1rem;margin-bottom:1rem;">
        <div class="dvf-text-head">
            <div class="dvf-text-icon" aria-hidden="true"><i class="fas fa-comments-dollar"></i></div>
            <div>
                <h4>Personnalisation de la page résultat</h4>
                <p>Ces champs rendent la page de résultat plus commerciale, plus claire et plus rassurante.</p>
            </div>
        </div>
        <form method="POST" style="display:grid;gap:1rem;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="save_settings">
            <div class="form-group">
                <label>Titre du résultat</label>
                <input type="text" name="estimation_result_title" class="form-control" value="<?= e((string) ($settings['result_title'] ?? '')) ?>" placeholder="Ex : Votre estimation indicative">
            </div>
            <div class="form-group">
                <label>Intro du résultat</label>
                <textarea name="estimation_result_intro" class="form-control" rows="3" placeholder="Ex : Cette estimation est indicative et non contractuelle."><?= e((string) ($settings['result_intro'] ?? '')) ?></textarea>
            </div>
            <div class="form-group">
                <label>Disclaimer du résultat</label>
                <textarea name="estimation_result_disclaimer" class="form-control" rows="3" placeholder="Ex : Elle est calculée à partir des ventes DVF les plus proches disponibles."><?= e((string) ($settings['result_disclaimer'] ?? '')) ?></textarea>
            </div>
            <div class="form-group">
                <label>Accroche de conversion</label>
                <input type="text" name="estimation_result_heading" class="form-control" value="<?= e((string) ($settings['result_heading'] ?? '')) ?>" placeholder="Ex : Obtenir une estimation précise">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>CTA principal</label>
                    <input type="text" name="estimation_result_primary_cta_label" class="form-control" value="<?= e((string) ($settings['result_primary_cta_label'] ?? '')) ?>" placeholder="Ex : Prendre rendez-vous avec Pascal Hamm">
                </div>
                <div class="form-group">
                    <label>URL CTA principal</label>
                    <input type="text" name="estimation_result_primary_cta_url" class="form-control" value="<?= e((string) ($settings['result_primary_cta_url'] ?? '')) ?>" placeholder="/prendre-rendez-vous">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>CTA secondaire</label>
                    <input type="text" name="estimation_result_secondary_cta_label" class="form-control" value="<?= e((string) ($settings['result_secondary_cta_label'] ?? '')) ?>" placeholder="Ex : Demander une estimation précise">
                </div>
                <div class="form-group">
                    <label>URL CTA secondaire</label>
                    <input type="text" name="estimation_result_secondary_cta_url" class="form-control" value="<?= e((string) ($settings['result_secondary_cta_url'] ?? '')) ?>" placeholder="/estimation-gratuite">
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn--primary">Enregistrer la page résultat</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;">
        <h3>Import SQL hérité</h3>
        <form method="POST" enctype="multipart/form-data" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
            <?= csrfField() ?>
            <input type="hidden" name="estimation_admin_action" value="legacy_import_csv">
            <input type="file" name="dvf_csv" accept=".csv" required>
            <button type="submit" class="btn btn--primary">Lancer import SQL</button>
            <small class="field-hint">Conserve l’import en base pour les modules qui l’utilisent encore.</small>
        </form>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;">
        <h3>Filtres demandes</h3>
        <form method="GET" action="/admin/index.php" style="display:grid;gap:.75rem;grid-template-columns:repeat(5,minmax(0,1fr));">
            <input type="hidden" name="module" value="estimation_dvf">
            <input type="text" name="city" class="form-control" placeholder="Ville" value="<?= e($filters['city']) ?>">
            <select name="property_type" class="form-control">
                <option value="">Type</option>
                <option value="appartement" <?= $filters['property_type']==='appartement'?'selected':'' ?>>Appartement</option>
                <option value="maison" <?= $filters['property_type']==='maison'?'selected':'' ?>>Maison</option>
                <option value="local" <?= $filters['property_type']==='local'?'selected':'' ?>>Local</option>
                <option value="terrain" <?= $filters['property_type']==='terrain'?'selected':'' ?>>Terrain</option>
            </select>
            <select name="status" class="form-control">
                <option value="">Statut</option>
                <option value="new" <?= $filters['status']==='new'?'selected':'' ?>>Nouveau</option>
                <option value="contacted" <?= $filters['status']==='contacted'?'selected':'' ?>>Contacté</option>
                <option value="qualified" <?= $filters['status']==='qualified'?'selected':'' ?>>Qualifié</option>
            </select>
            <button class="btn btn--primary" type="submit">Filtrer</button>
        </form>
    </div>

    <div class="card" style="padding:1rem;margin-bottom:1rem;overflow:auto;">
        <h3>Demandes d’estimation</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Date</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Type</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Ville</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Surface</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Comparables</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Fiabilité</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Statut</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['created_at']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['property_type']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['city']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['surface']) ?> m²
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['comparables_count']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['confidence_level']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $r['status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding:1rem;">
        <h3>Carte des demandes (Google Maps)</h3>
        <p style="margin-bottom:.75rem;color:#6b7280">Connectez votre clé Google Maps pour afficher la carte interactive des demandes.</p>
        <div style="height:280px;border:1px dashed #d1d5db;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#6b7280;">
            Carte des demandes (placeholder)
        </div>
    </div>

    <div class="card" style="padding:1rem;margin-top:1rem;overflow:auto;">
        <h3>Historique imports SQL</h3>
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
            <tr>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Date</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Fichier</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Statut</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Lues</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Insérées</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">MAJ</th>
                <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:.4rem;">Rejetées</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($importStats['runs'] as $run): ?>
                <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['started_at']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['source_file']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['status']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['rows_read']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['rows_inserted']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['rows_updated']) ?>
                    </td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                        <?= e((string) $run['rows_rejected']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
    <?php
}
