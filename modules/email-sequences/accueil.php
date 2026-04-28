<?php

declare(strict_types=1);

$action = preg_replace('/[^a-z0-9_-]/i', '', (string)($_GET['action'] ?? 'index'));

if ($action === 'sync_priority' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && defined('ROOT_PATH')) {
    if (is_file(ROOT_PATH . '/public/admin/includes/auth_functions.php')) {
        require_once ROOT_PATH . '/public/admin/includes/auth_functions.php';
    }
    if (!function_exists('verifyCsrfToken') || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) {
        $_SESSION['error'] = 'Session expirée ou jeton de sécurité invalide. Réessayez.';
    } else {
        require_once ROOT_PATH . '/core/services/EmailSequencePriorityService.php';
        $rep = EmailSequencePriorityService::syncPriorityAutomaticSequences();
        $nDup = is_array($rep['duplicates'] ?? null) ? count($rep['duplicates']) : 0;
        $msg = 'Synchronisation prioritaire : '
            . count($rep['created'] ?? []) . ' créée(s), '
            . count($rep['updated'] ?? []) . ' mise(s) à jour, '
            . count($rep['already_ok'] ?? []) . ' déjà conforme(s).';
        if ($nDup > 0) {
            $msg .= ' Doublon(s) actif(s) sur un même déclencheur : ' . $nDup . ' (non modifié(s), corrigez manuellement).';
        }
        if (!empty($rep['errors'])) {
            $msg .= ' Erreur(s) : ' . implode(' ; ', $rep['errors']);
        }
        $_SESSION['success'] = $msg;
    }
    $redirF = (string)($_POST['return_filter'] ?? 'automatic');
    if (!in_array($redirF, ['all', 'automatic', 'manual'], true)) {
        $redirF = 'automatic';
    }
    header('Location: /admin?module=email-sequences&filter=' . rawurlencode($redirF));
    exit;
}

if ($action === 'set_view') {
    $viewType = sanitizeString($_POST['view_type'] ?? 'list');
    if (in_array($viewType, ['card', 'list'])) {
        $_SESSION['sequences_view_type'] = $viewType;
    }
    $f = sanitizeString($_POST['filter'] ?? 'all');
    if (!in_array($f, ['all', 'automatic', 'manual'], true)) {
        $f = 'all';
    }
    header('Location: /admin?module=email-sequences&filter=' . rawurlencode($f));
    exit;
}

if ($action === 'new' || $action === 'create') {
    require_once __DIR__ . '/new.php';
    return;
}

if ($action === 'edit') {
    require_once __DIR__ . '/edit.php';
    return;
}

if ($action === 'get-email') {
    header('Content-Type: application/json');
    $emailId = (int)($_GET['id'] ?? 0);

    try {
        $stmt = db()->prepare('SELECT id, email_number, subject, body_html, preview_text FROM email_sequence_emails WHERE id = ?');
        $stmt->execute([$emailId]);
        $email = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($email) {
            echo json_encode($email);
        } else {
            echo json_encode(['error' => 'Email not found']);
        }
    } catch (Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update-email') {
    header('Content-Type: application/json');
    $emailId = (int)($_POST['email_id'] ?? 0);
    $subject = sanitizeString($_POST['subject'] ?? '');
    $body = sanitizeString($_POST['body_html'] ?? '');
    $preview = sanitizeString($_POST['preview'] ?? '');

    try {
        $success = EmailSequenceService::updateSequenceEmail($emailId, $subject, $body, $preview);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'update-destination') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_POST['sequence_id'] ?? 0);
    $destinationType = sanitizeString($_POST['destination_type'] ?? '');
    $destinationUrl = sanitizeString($_POST['destination_url'] ?? '');
    $destinationLabel = sanitizeString($_POST['destination_label'] ?? '');
    $destinationContactType = sanitizeString($_POST['destination_contact_type'] ?? '');

    try {
        $success = EmailSequenceService::updateSequenceDestination(
            $sequenceId,
            $destinationType ?: null,
            $destinationUrl ?: null,
            $destinationLabel ?: null,
            $destinationContactType ?: null
        );
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'activate') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_GET['id'] ?? 0);

    try {
        $success = EmailSequenceService::activateSequence($sequenceId);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'deactivate') {
    header('Content-Type: application/json');
    $sequenceId = (int)($_GET['id'] ?? 0);

    try {
        $success = EmailSequenceService::deactivateSequence($sequenceId);
        echo json_encode(['success' => $success]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// N'afficher la liste que si action est 'index' (pas new, edit, create, etc)
if ($action === 'index' || $action === '') {
    $rawFilter = strtolower((string) ($_GET['filter'] ?? 'all'));
    if (!in_array($rawFilter, ['all', 'automatic', 'manual'], true)) {
        $rawFilter = 'all';
    }
    $seqFilter = $rawFilter;

    $pageTitle = 'Séquences e-mail';
    $pageDescription = 'Créer et gérer des campagnes et automations d’e-mail';

    if ($seqFilter === 'automatic') {
        $pageTitle = 'Séquences e-mail automatiques';
        $pageDescription = 'Déclenchées quand un visiteur envoie un formulaire (consentement RGPD).';
    } elseif ($seqFilter === 'manual') {
        $pageTitle = 'Séquences e-mail manuelles';
        $pageDescription = 'Relances et scénarios lancés sans déclencheur formulaire du site.';
    }

    $seqFilterWhere = '';
    $seqFilterParams = [];
    if ($seqFilter === 'automatic') {
        $seqFilterWhere = ' WHERE trigger_type = ? ';
        $seqFilterParams = ['automatic'];
    } elseif ($seqFilter === 'manual') {
        $seqFilterWhere = ' WHERE trigger_type = ? ';
        $seqFilterParams = ['manual'];
    }

    $bootstrapMsg = '';
    try {
        $seededCount = EmailSequenceService::ensureDefaultEntrySequences();
        if ($seededCount > 0) {
            $bootstrapMsg = ($seededCount === 1)
                ? 'Une séquence automatique par défaut a été créée (points d’entrée du site).'
                : $seededCount . ' séquences automatiques par défaut ont été créées (une par type de formulaire).';
        }
    } catch (Throwable $e) {
        error_log('email-sequences ensureDefaultEntrySequences: ' . $e->getMessage());
    }

    // Initialiser la préférence de vue (par défaut: list)
    if (!isset($_SESSION['sequences_view_type'])) {
        $_SESSION['sequences_view_type'] = 'list';
    }
    $viewType = $_SESSION['sequences_view_type'];

    $priorityHealth = [];
    if (defined('ROOT_PATH') && is_file(ROOT_PATH . '/core/services/EmailSequencePriorityService.php')) {
        require_once ROOT_PATH . '/core/services/EmailSequencePriorityService.php';
        if ($seqFilter === 'automatic') {
            try {
                $syncRep = EmailSequencePriorityService::syncPriorityAutomaticSequences();
                $nC = count($syncRep['created'] ?? []);
                $nU = count($syncRep['updated'] ?? []);
                if ($nC + $nU > 0) {
                    $bootstrapMsg .= ($bootstrapMsg !== '' ? ' ' : '')
                        . 'Scénarios prioritaires (e-mails détaillés) : ' . $nC . ' créée(s), ' . $nU . ' mise(s) à jour.';
                }
            } catch (Throwable $e) {
                error_log('email-sequences syncPriorityAutomaticSequences: ' . $e->getMessage());
            }
        }
        $priorityHealth = EmailSequencePriorityService::getHealthSummary();
    }

    if ($bootstrapMsg !== '') {
        $_SESSION['success'] = trim($bootstrapMsg);
    }

    function renderContent(): void {
        global $viewType, $seqFilter, $seqFilterWhere, $seqFilterParams, $pageTitle, $pageDescription, $priorityHealth;
        $baseQ = '/admin?module=email-sequences';
        $filterTabs = [
            'all' => ['label' => 'Toutes', 'q' => ''],
            'automatic' => ['label' => 'Automatiques', 'q' => '&filter=automatic'],
            'manual' => ['label' => 'Manuelles', 'q' => '&filter=manual'],
        ];

        if ($seqFilter === 'automatic') {
            if (!class_exists(EmailSequencePriorityService::class, false) && defined('ROOT_PATH') && is_file(ROOT_PATH . '/core/services/EmailSequencePriorityService.php')) {
                require_once ROOT_PATH . '/core/services/EmailSequencePriorityService.php';
            }
            if (defined('ROOT_PATH') && is_file(ROOT_PATH . '/public/admin/includes/auth_functions.php')) {
                require_once ROOT_PATH . '/public/admin/includes/auth_functions.php';
            }
            $csrfSync = function_exists('generateCsrfToken') ? generateCsrfToken() : '';
            $presets = class_exists(EmailSequencePriorityService::class, false)
                ? EmailSequencePriorityService::loadPresets()
                : [];
            $autoSequences = [];
            try {
                $stmt = db()->prepare(
                    'SELECT es.id, es.name, es.description, es.status, es.form_trigger, es.trigger_type, es.created_at,
                    (SELECT COUNT(*) FROM email_sequence_emails e WHERE e.sequence_id = es.id) AS email_steps
                    FROM email_sequences es
                    WHERE es.trigger_type = ?
                    ORDER BY es.form_trigger ASC, es.status DESC, es.created_at DESC
                    LIMIT 120'
                );
                $stmt->execute(['automatic']);
                $autoSequences = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e) {
                error_log('email-sequences automatic list: ' . $e->getMessage());
            }
            require __DIR__ . '/views/automatic_layout.php';

            return;
        }

        ?>
    <style>
        /* Détails : hub-hero = hub-page.css (évite double définition + incohérences) */
        .email-sequences-admin { min-width: 0; }
        .email-sequences-admin .hub-hero { margin-bottom: 24px; }
        .seq-filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .seq-filter-tabs a { display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: 1px solid rgba(255,255,255,.25); color: rgba(255,255,255,.88); background: rgba(0,0,0,.12); transition: background .15s, border-color .15s; }
        .seq-filter-tabs a:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.4); color: #fff; text-decoration: none; }
        .seq-filter-tabs a.is-active { background: rgba(201,168,76,.25); border-color: #c9a84c; color: #fff; }
        .hub-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 16px; align-items: center; }
        .hub-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all .2s; }
        .hub-btn-primary { background: #c9a84c; color: #10253c; }
        .hub-btn-primary:hover { background: #b8962d; }
        .view-toggle { display: flex; gap: 4px; border: 1px solid rgba(255,255,255,.3); border-radius: 6px; padding: 2px; flex-shrink: 0; }
        .view-btn { padding: 8px 12px; border: none; background: transparent; color: rgba(255,255,255,.7); font-weight: 600; cursor: pointer; border-radius: 4px; transition: all .2s; font-size: 12px; font-family: inherit; }
        .view-btn.active { background: rgba(255,255,255,.2); color: #fff; }
        .view-btn:hover { color: #fff; }
        .sequences-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .sequences-table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 12px; }
        .sequences-table { width: 100%; min-width: 880px; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .sequences-table th { background: #f3f4f6; padding: 12px 16px; text-align: left; font-weight: 600; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; }
        .sequences-table td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
        .sequences-table tbody tr:hover { background: #f9fafb; }
        .sequences-table tbody tr:last-child td { border-bottom: none; }
        .sequence-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; transition: all .2s; }
        .sequence-card:hover { border-color: #c9a84c; box-shadow: 0 4px 12px rgba(201,168,76,.15); }
        .sequence-card h3 { margin: 0 0 8px; font-size: 15px; font-weight: 600; color: #0f172a; }
        .sequence-card p { margin: 0 0 12px; font-size: 13px; color: #64748b; }
        .sequence-badge { display: inline-block; padding: 4px 8px; background: #dbeafe; color: #1d4ed8; border-radius: 4px; font-size: 11px; font-weight: 600; margin-bottom: 12px; }
        .sequence-actions { display: flex; gap: 8px; }
        .sequence-btn { padding: 6px 12px; border-radius: 6px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; }
        .sequence-btn-primary { background: #3498db; color: #fff; }
        .sequence-btn-primary:hover { background: #2980b9; }
        .empty-state { text-align: center; padding: 40px 20px; color: #9ca3af; }
        .sequences-flash { margin: 0 0 20px; padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600; }
        .sequences-flash--ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .sequences-flash--err { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .priority-strip { display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 20px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 12px; min-width: 0; }
        .priority-strip h3 { width: 100%; margin: 0 0 6px; font-size: 13px; color: #0f172a; font-weight: 700; }
        .priority-strip p { width: 100%; margin: 0 0 8px; color: #64748b; line-height: 1.4; }
        .ph-pill { display: inline-flex; align-items: center; max-width: 100%; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 11px; word-break: break-word; }
        .ph-ok { background: #dcfce7; color: #14532d; }
        .ph-miss, .ph-miss a { color: #7f1d1d; background: #fee2e2; }
        .ph-dup { background: #ffedd5; color: #9a3412; }
        .ph-inc { background: #fef9c3; color: #854d0e; }
        .ph-dim, .ph-neutral { background: #e2e8f0; color: #475569; }
        .ph-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; }
    </style>

    <div class="email-sequences-admin">
        <header class="hub-hero">
            <h1><?= htmlspecialchars((string) ($pageTitle ?? 'Séquences e-mail'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars((string) ($pageDescription ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
            <p style="margin-top:10px;font-size:13px;color:rgba(255,255,255,.72);max-width:52rem;line-height:1.45;">
                Les séquences <strong>automatiques</strong> se déclenchent quand un visiteur envoie un formulaire (contact, estimation, RDV, guide, etc.), sous réserve du consentement RGPD.
                Planifiez le cron <code style="background:rgba(0,0,0,.2);padding:2px 6px;border-radius:4px;">cron/email_sequences.php</code> toutes les 15–60&nbsp;minutes pour l’envoi des emails espacés (J+0, J+3, …). Les séquences <strong>manuelles</strong> s’adressent à d’autres relances (hors point d’entrée formulaire).
            </p>
            <nav class="seq-filter-tabs" aria-label="Filtrer par type de séquence">
                <?php foreach ($filterTabs as $key => $ft): ?>
                    <a href="<?= $baseQ . $ft['q'] ?>"
                       class="<?= $seqFilter === $key ? 'is-active' : '' ?>"><?= htmlspecialchars($ft['label'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="hub-actions">
                <?php
                if (defined('ROOT_PATH') && is_file(ROOT_PATH . '/public/admin/includes/auth_functions.php')) {
                    require_once ROOT_PATH . '/public/admin/includes/auth_functions.php';
                }
                $csrfSync = function_exists('generateCsrfToken') ? generateCsrfToken() : '';
                ?>
                <?php if ((isset($priorityHealth['triggers']) && is_array($priorityHealth['triggers'])) && $csrfSync !== ''): ?>
                <form method="post" action="/admin?module=email-sequences&action=sync_priority" class="seq-sync-priority" style="display:inline-flex;margin:0;padding:0;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfSync, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_filter" value="<?= htmlspecialchars($seqFilter, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="hub-btn" style="background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.4);" title="Crée ou met à jour les 7 scénarios prioritaires (1 déclencheur = 1 active)">
                        <i class="fas fa-magic"></i> Sync séquences prioritaires
                    </button>
                </form>
                <?php endif; ?>
                <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle séquence
                </a>
                <form method="POST" style="display: none;" id="viewToggleForm">
                    <input type="hidden" name="view_type" id="viewTypeInput">
                    <input type="hidden" name="filter" id="sequencesFilterField" value="<?= htmlspecialchars($seqFilter, ENT_QUOTES, 'UTF-8') ?>">
                </form>
                <div class="view-toggle">
                    <button type="button" class="view-btn <?= $viewType === 'list' ? 'active' : '' ?>" onclick="setView('list')" title="Vue liste">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" class="view-btn <?= $viewType === 'card' ? 'active' : '' ?>" onclick="setView('card')" title="Vue cartes">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>
        </header>

        <?php
        if (!empty($_SESSION['success'])) {
            $sf = (string) $_SESSION['success'];
            unset($_SESSION['success']);
            echo '<div class="sequences-flash sequences-flash--ok" role="status">' . htmlspecialchars($sf) . '</div>';
        }
        if (!empty($_SESSION['error'])) {
            $ef = (string) $_SESSION['error'];
            unset($_SESSION['error']);
            echo '<div class="sequences-flash sequences-flash--err" role="alert">' . htmlspecialchars($ef) . '</div>';
        }
        ?>

        <?php if (defined('ROOT_PATH') && is_file(ROOT_PATH . '/core/services/EmailSequencePriorityService.php')): ?>
        <?php
        if (!class_exists(EmailSequencePriorityService::class, false)) {
            require_once ROOT_PATH . '/core/services/EmailSequencePriorityService.php';
        }
        $phShow = (isset($priorityHealth['triggers']) && is_array($priorityHealth['triggers'])) ? $priorityHealth['triggers'] : [];
        ?>
        <div class="priority-strip" role="region" aria-label="Santé des séquences prioritaires">
            <h3>Scénarios automatiques prioritaires (1 déclencheur = 1 active)</h3>
            <p>OK = une active complète &nbsp;·&nbsp; Manquante = aucune active &nbsp;·&nbsp; Doublon = plusieurs actives le même déclencheur &nbsp;·&nbsp; Incomplète = active mais e-mails manquants ou insuffisants. La première étape d’estimation sans e-mail n’est pas modifiée ici.</p>
            <?php foreach (EmailSequencePriorityService::PRIORITY_FORM_TRIGGERS as $pft):
                $pt = $phShow[$pft] ?? null;
                $st = (string)($pt['status'] ?? '—');
                $cls = match ($st) {
                    'ok' => 'ph-ok',
                    'manquante' => 'ph-miss',
                    'doublon' => 'ph-dup',
                    'incomplète' => 'ph-inc',
                    default => 'ph-neutral',
                };
                $label = EmailSequencePriorityService::triggerSummaryLabel($pft, $priorityHealth);
                ?>
            <span class="ph-pill <?= htmlspecialchars($cls, ENT_QUOTES, 'UTF-8') ?>"><code style="font-size:10px;opacity:.9;"><?= htmlspecialchars($pft, ENT_QUOTES, 'UTF-8') ?></code> &nbsp; <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($viewType === 'list'): ?>
        <div class="sequences-table-wrap">
        <table class="sequences-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Déclencheur</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th style="min-width: 100px;">Santé</th>
                    <th style="width: 150px;">Créée le</th>
                    <th style="width: 100px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            try {
                $stmt = db()->prepare(
                    'SELECT id, name, description, status, form_trigger, trigger_type, created_at FROM email_sequences'
                    . $seqFilterWhere
                    . ' ORDER BY created_at DESC LIMIT 100'
                );
                $stmt->execute($seqFilterParams);
                $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($sequences)):
            ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="fas fa-envelope" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                        <p style="margin: 0;">Aucune séquence dans ce filtre</p>
                        <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                            <i class="fas fa-plus"></i> Créer la première séquence
                        </a>
                    </td>
                </tr>
            <?php else: foreach ($sequences as $seq):
                $statusClass = $seq['status'] === 'active' ? 'dbeafe' : 'fee2e2';
                $statusColor = $seq['status'] === 'active' ? '#1d4ed8' : '#dc2626';
                $statusLabel = $seq['status'] === 'active' ? '🟢 Actif' : '🔴 Inactif';
                $hb = class_exists(EmailSequencePriorityService::class, false)
                    ? EmailSequencePriorityService::badgeForRow($seq, $priorityHealth)
                    : ['label' => '—', 'class' => 'ph-neutral'];
            ?>
                <tr>
                    <td><strong><?= htmlspecialchars($seq['name']) ?></strong></td>
                    <td><small style="color:#64748b;font-family:monospace;"><?= !empty($seq['form_trigger']) ? htmlspecialchars((string)$seq['form_trigger']) : '—' ?></small><br><small style="color:#94a3b8;"><?= htmlspecialchars((string)($seq['trigger_type'] ?? '')) ?></small></td>
                    <td><?= htmlspecialchars(substr((string)($seq['description'] ?? ''), 0, 60)) ?></td>
                    <td><span class="sequence-badge" style="background: #<?= $statusClass ?>; color: <?= $statusColor ?>;"><?= htmlspecialchars($statusLabel) ?></span></td>
                    <td><span class="ph-badge <?= htmlspecialchars($hb['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($hb['label'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    <td><small style="color: #9ca3af;"><?= date('d/m/Y', strtotime($seq['created_at'])) ?></small></td>
                    <td style="text-align: right;">
                        <a href="/admin?module=email-sequences&action=edit&id=<?= $seq['id'] ?>" class="sequence-btn sequence-btn-primary">✏️ Éditer</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            <?php } catch (Throwable $e) {
                error_log('Email Sequences Error: ' . $e->getMessage());
            ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #9ca3af;">
                        Erreur lors du chargement des séquences
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="sequences-grid">
            <?php
            try {
                $stmt = db()->prepare(
                    'SELECT id, name, description, status, form_trigger, trigger_type, created_at FROM email_sequences'
                    . $seqFilterWhere
                    . ' ORDER BY created_at DESC LIMIT 100'
                );
                $stmt->execute($seqFilterParams);
                $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($sequences)):
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-envelope" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
                    <p>Aucune séquence dans ce filtre</p>
                    <a href="/admin?module=email-sequences&action=new" class="hub-btn hub-btn-primary" style="margin-top: 16px;">
                        <i class="fas fa-plus"></i> Créer la première séquence
                    </a>
                </div>
            <?php else: foreach ($sequences as $seq):
                $statusClass = $seq['status'] === 'active' ? 'dbeafe' : 'fee2e2';
                $statusColor = $seq['status'] === 'active' ? '#1d4ed8' : '#dc2626';
                $hbCard = class_exists(EmailSequencePriorityService::class, false)
                    ? EmailSequencePriorityService::badgeForRow($seq, $priorityHealth)
                    : ['label' => '—', 'class' => 'ph-neutral'];
            ?>
                <div class="sequence-card">
                    <span class="sequence-badge" style="background: #<?= $statusClass ?>; color: <?= $statusColor ?>;">
                        <?= ucfirst($seq['status']) ?>
                    </span>
                    <span class="ph-badge <?= htmlspecialchars($hbCard['class'], ENT_QUOTES, 'UTF-8') ?>" style="margin-left:8px;"><?= htmlspecialchars($hbCard['label'], ENT_QUOTES, 'UTF-8') ?></span>
                    <h3><?= htmlspecialchars($seq['name']) ?></h3>
                    <?php if (!empty($seq['form_trigger'])): ?>
                    <p style="font-size:12px;color:#64748b;margin:0 0 8px;font-family:monospace;"><?= htmlspecialchars((string)$seq['form_trigger']) ?> · <?= htmlspecialchars((string)($seq['trigger_type'] ?? '')) ?></p>
                    <?php endif; ?>
                    <p><?= htmlspecialchars(substr((string)($seq['description'] ?? ''), 0, 60)) ?></p>
                    <small style="color: #9ca3af; display: block; margin-bottom: 12px;">Créée le <?= date('d/m/Y', strtotime($seq['created_at'])) ?></small>
                    <div class="sequence-actions">
                        <a href="/admin?module=email-sequences&action=edit&id=<?= $seq['id'] ?>" class="sequence-btn sequence-btn-primary">Éditer</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
            <?php
            } catch (Throwable $e) {
                error_log('Email Sequences Error: ' . $e->getMessage());
            ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <p>Erreur lors du chargement des séquences</p>
                </div>
            <?php } ?>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function setView(viewType) {
        document.getElementById('viewTypeInput').value = viewType;
        const form = document.getElementById('viewToggleForm');
        form.action = '/admin?module=email-sequences&action=set_view';
        form.submit();
    }
    </script>
    <?php
    }
}
