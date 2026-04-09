<?php
$pageTitle = 'Convertir';
$pageDescription = 'Transformez vos contacts en clients signés';

const CONVERTIR_CRM_STATUSES = [
    'nouveau' => 'Nouveau',
    'contacte' => 'Contacté',
    'rdv_planifie' => 'RDV planifié',
    'mandat_signe' => 'Mandat signé',
    'perdu' => 'Perdu',
];

ensureConvertirCrmTables();
handleConvertirCrmPost();

$action = strtolower((string)($_GET['action'] ?? ''));
$crmContactsView = $action === 'crm-contacts';

$crmLeads = [];
$selectedLead = null;
$selectedLeadHistory = [];

if ($crmContactsView) {
    $crmLeads = listConvertirCrmLeads();

    $leadId = (int)($_GET['lead_id'] ?? 0);
    if ($leadId <= 0 && $crmLeads) {
        $leadId = (int)$crmLeads[0]['id'];
    }

    if ($leadId > 0) {
        $selectedLead = findConvertirCrmLeadById($leadId);
        if ($selectedLead !== null) {
            $selectedLeadHistory = listConvertirCrmHistory($leadId);
        }
    }
}

function handleConvertirCrmPost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $intent = (string)($_POST['crm_intent'] ?? '');
    if (!in_array($intent, ['update_status', 'add_note'], true)) {
        return;
    }

    verifyCsrf();

    $leadId = (int)($_POST['lead_id'] ?? 0);
    if ($leadId <= 0 || !convertirLeadExists($leadId)) {
        Session::flash('error', 'Lead introuvable.');
        redirect('/admin?module=convertir&action=crm-contacts');
    }

    if ($intent === 'update_status') {
        $status = normalizeConvertirStatus((string)($_POST['status'] ?? 'nouveau'));
        $note = trim((string)($_POST['note'] ?? ''));
        updateConvertirLeadStatus($leadId, $status, $note);
        Session::flash('success', 'Statut du lead mis à jour.');
    }

    if ($intent === 'add_note') {
        $note = trim((string)($_POST['note'] ?? ''));
        if ($note === '') {
            Session::flash('error', 'Ajoutez une note avant de valider.');
        } else {
            addConvertirLeadNote($leadId, $note);
            Session::flash('success', 'Note ajoutée à l\'historique.');
        }
    }

    redirect('/admin?module=convertir&action=crm-contacts&lead_id=' . $leadId);
}

function ensureConvertirCrmTables(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_tracking (
        lead_id INT UNSIGNED NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT "nouveau",
        note TEXT NULL,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (lead_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_interactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lead_id INT UNSIGNED NOT NULL,
        interaction_type VARCHAR(20) NOT NULL,
        old_value VARCHAR(80) NULL,
        new_value VARCHAR(80) NULL,
        note TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_lead_created (lead_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

function normalizeConvertirStatus(string $status): string
{
    $status = strtolower(trim($status));
    return array_key_exists($status, CONVERTIR_CRM_STATUSES) ? $status : 'nouveau';
}

function convertirLeadExists(int $leadId): bool
{
    $stmt = db()->prepare('SELECT id FROM leads WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $leadId]);
    return (bool)$stmt->fetchColumn();
}

function updateConvertirLeadStatus(int $leadId, string $newStatus, string $note = ''): void
{
    $stmt = db()->prepare('SELECT status FROM crm_lead_tracking WHERE lead_id = :lead_id LIMIT 1');
    $stmt->execute([':lead_id' => $leadId]);
    $oldStatus = (string)($stmt->fetchColumn() ?: 'nouveau');

    $upsert = db()->prepare('INSERT INTO crm_lead_tracking (lead_id, status, note, updated_at)
        VALUES (:lead_id, :status, :note, NOW())
        ON DUPLICATE KEY UPDATE status = VALUES(status), note = VALUES(note), updated_at = NOW()');
    $upsert->execute([
        ':lead_id' => $leadId,
        ':status' => $newStatus,
        ':note' => $note,
    ]);

    if ($oldStatus !== $newStatus || $note !== '') {
        $log = db()->prepare('INSERT INTO crm_lead_interactions (lead_id, interaction_type, old_value, new_value, note, created_at)
            VALUES (:lead_id, "status", :old_value, :new_value, :note, NOW())');
        $log->execute([
            ':lead_id' => $leadId,
            ':old_value' => $oldStatus,
            ':new_value' => $newStatus,
            ':note' => $note,
        ]);
    }
}

function addConvertirLeadNote(int $leadId, string $note): void
{
    $upsert = db()->prepare('INSERT INTO crm_lead_tracking (lead_id, status, note, updated_at)
        VALUES (:lead_id, "nouveau", :note, NOW())
        ON DUPLICATE KEY UPDATE note = VALUES(note), updated_at = NOW()');
    $upsert->execute([
        ':lead_id' => $leadId,
        ':note' => $note,
    ]);

    $log = db()->prepare('INSERT INTO crm_lead_interactions (lead_id, interaction_type, note, created_at)
        VALUES (:lead_id, "note", :note, NOW())');
    $log->execute([
        ':lead_id' => $leadId,
        ':note' => $note,
    ]);
}

function listConvertirCrmLeads(): array
{
    $sql = 'SELECT l.id, l.nom, l.email, l.telephone, l.message, l.source, l.persona, l.guide_titre, l.created_at,
                   COALESCE(t.status, "nouveau") AS crm_status,
                   t.note AS crm_note,
                   t.updated_at AS crm_updated_at
            FROM leads l
            LEFT JOIN crm_lead_tracking t ON t.lead_id = l.id
            ORDER BY l.created_at DESC
            LIMIT 500';

    return db()->query($sql)->fetchAll();
}

function findConvertirCrmLeadById(int $leadId): ?array
{
    $stmt = db()->prepare('SELECT l.id, l.nom, l.email, l.telephone, l.message, l.source, l.persona, l.guide_titre, l.created_at,
                                  COALESCE(t.status, "nouveau") AS crm_status,
                                  t.note AS crm_note,
                                  t.updated_at AS crm_updated_at
                           FROM leads l
                           LEFT JOIN crm_lead_tracking t ON t.lead_id = l.id
                           WHERE l.id = :id
                           LIMIT 1');
    $stmt->execute([':id' => $leadId]);
    $lead = $stmt->fetch();

    return $lead ?: null;
}

function listConvertirCrmHistory(int $leadId): array
{
    $stmt = db()->prepare('SELECT interaction_type, old_value, new_value, note, created_at
                           FROM crm_lead_interactions
                           WHERE lead_id = :lead_id
                           ORDER BY created_at DESC, id DESC
                           LIMIT 100');
    $stmt->execute([':lead_id' => $leadId]);
    return $stmt->fetchAll();
}

function renderConvertirHubCards(): void
{
    ?>
    <div class="cards-container">

        <div class="card" style="--card-accent:#3498db; --card-icon-bg:#e3f2fd;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-address-book"></i></div>
                <h3 class="card-title">CRM Contacts</h3>
            </div>
            <p class="card-description">Gérez, qualifiez et suivez vos leads capturés avec historique complet.</p>
            <div class="card-tags"><span class="tag">Pipeline</span><span class="tag">Historique</span></div>
            <a href="/admin?module=convertir&action=crm-contacts" class="crm-link-btn">Ouvrir le module</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                <h3 class="card-title">Prise de RDV</h3>
            </div>
            <p class="card-description">Automatisez la prise de rendez-vous vendeurs avec un agenda en ligne.</p>
            <div class="card-tags"><span class="tag">Agenda</span><span class="tag">Automation</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#27ae60; --card-icon-bg:#eafaf1;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-file-signature"></i></div>
                <h3 class="card-title">Argumentaire mandat</h3>
            </div>
            <p class="card-description">Scripts et supports pour transformer un RDV en mandat exclusif.</p>
            <div class="card-tags"><span class="tag">Script</span><span class="tag">Exclusivité</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#8e44ad; --card-icon-bg:#f5eef8;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-handshake"></i></div>
                <h3 class="card-title">Suivi post-RDV</h3>
            </div>
            <p class="card-description">Relances automatiques et séquences de nurturing après le premier contact.</p>
            <div class="card-tags"><span class="tag">Relance</span><span class="tag">Nurturing</span></div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

    </div>
    <?php
}

function renderConvertirCrmContacts(array $crmLeads, ?array $selectedLead, array $selectedLeadHistory): void
{
    $flash = Session::getFlash();
    ?>
    <style>
        .crm-link-btn{display:inline-flex;margin-top:.85rem;background:#0f172a;color:#fff;text-decoration:none;padding:.55rem .85rem;border-radius:10px;font-weight:700;}
        .convertir-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin:1rem 0;flex-wrap:wrap;}
        .convertir-count{color:#64748b;font-size:.92rem;}
        .crm-layout{display:grid;grid-template-columns:minmax(520px,1.5fr) minmax(340px,1fr);gap:1rem;align-items:start;}
        .crm-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 25px rgba(15,23,42,.05);overflow:hidden;}
        .crm-panel-head{padding:1rem 1.1rem;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center;gap:.6rem;}
        .crm-panel-body{padding:1rem 1.1rem;}
        .crm-table-wrap{overflow:auto;max-height:72vh;}
        .crm-table{width:100%;border-collapse:collapse;min-width:700px;}
        .crm-table th,.crm-table td{padding:.72rem .65rem;border-bottom:1px solid #f1f5f9;text-align:left;vertical-align:top;font-size:.9rem;}
        .crm-table tr.is-selected{background:#eff6ff;}
        .crm-status{display:inline-block;padding:.2rem .55rem;border-radius:999px;background:#e2e8f0;font-size:.78rem;font-weight:700;}
        .crm-status.nouveau{background:#dbeafe;color:#1d4ed8;}
        .crm-status.contacte{background:#fde68a;color:#92400e;}
        .crm-status.rdv_planifie{background:#ddd6fe;color:#5b21b6;}
        .crm-status.mandat_signe{background:#bbf7d0;color:#166534;}
        .crm-status.perdu{background:#fecaca;color:#991b1b;}
        .crm-side-grid{display:grid;gap:1rem;}
        .crm-lead-meta{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-top:.85rem;}
        .crm-lead-meta > div{font-size:.85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:.55rem .65rem;}
        .crm-form{display:grid;gap:.7rem;margin-top:1rem;}
        .crm-form select,.crm-form textarea,.crm-form button{width:100%;padding:.62rem .7rem;border-radius:10px;border:1px solid #cbd5e1;font:inherit;}
        .crm-form button{background:#0f172a;color:#fff;font-weight:700;border-color:#0f172a;cursor:pointer;}
        .crm-history{display:grid;gap:.6rem;max-height:320px;overflow:auto;padding-right:.2rem;}
        .crm-history-item{border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;padding:.65rem .7rem;}
        .crm-muted{color:#64748b;font-size:.84rem;}
        .crm-flash{padding:.75rem .9rem;border-radius:10px;margin-bottom:1rem;font-weight:600;}
        .crm-flash.success{background:#dcfce7;color:#166534;}
        .crm-flash.error{background:#fee2e2;color:#991b1b;}
        @media (max-width: 1100px) {.crm-layout{grid-template-columns:1fr;}}
    </style>

    <?php if ($flash): ?>
        <div class="crm-flash <?= e((string)$flash['type']) ?>"><?= e((string)$flash['message']) ?></div>
    <?php endif; ?>

    <div class="convertir-toolbar">
        <a href="/admin?module=convertir" class="crm-link-btn" style="margin:0;background:#475569;">← Retour au hub</a>
        <div class="convertir-count"><?= count($crmLeads) ?> leads capturés depuis la table <code>leads</code>.</div>
    </div>

    <div class="crm-layout">
        <section class="crm-panel">
            <div class="crm-panel-head">
                <strong>Leads capturés</strong>
            </div>
            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Contact</th>
                        <th>Source</th>
                        <th>Statut CRM</th>
                        <th>Créé le</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$crmLeads): ?>
                        <tr><td colspan="5">Aucun lead dans la table <code>leads</code>.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($crmLeads as $lead): ?>
                        <?php $isSelected = $selectedLead && (int)$selectedLead['id'] === (int)$lead['id']; ?>
                        <tr class="<?= $isSelected ? 'is-selected' : '' ?>">
                            <td>
                                <a href="/admin?module=convertir&action=crm-contacts&lead_id=<?= (int)$lead['id'] ?>"><strong><?= e((string)($lead['nom'] ?: 'Lead #' . $lead['id'])) ?></strong></a>
                                <div class="crm-muted"><?= e((string)($lead['guide_titre'] ?: ($lead['persona'] ?: '—'))) ?></div>
                            </td>
                            <td>
                                <div><?= e((string)$lead['email']) ?></div>
                                <div class="crm-muted"><?= e((string)($lead['telephone'] ?: 'Pas de téléphone')) ?></div>
                            </td>
                            <td><?= e((string)($lead['source'] ?: '—')) ?></td>
                            <td><span class="crm-status <?= e((string)$lead['crm_status']) ?>"><?= e(CONVERTIR_CRM_STATUSES[(string)$lead['crm_status']] ?? 'Nouveau') ?></span></td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$lead['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="crm-side-grid">
            <section class="crm-panel">
                <div class="crm-panel-head"><strong>Fiche lead</strong></div>
                <div class="crm-panel-body">
                    <?php if (!$selectedLead): ?>
                        <div class="crm-muted">Sélectionnez un lead pour modifier son statut et ses notes.</div>
                    <?php else: ?>
                        <h3 style="margin:0"><?= e((string)($selectedLead['nom'] ?: ('Lead #' . $selectedLead['id']))) ?></h3>
                        <div class="crm-muted" style="margin-top:.3rem;"><?= e((string)$selectedLead['email']) ?></div>
                        <div class="crm-lead-meta">
                            <div><strong>Téléphone</strong><br><?= e((string)($selectedLead['telephone'] ?: '—')) ?></div>
                            <div><strong>Source</strong><br><?= e((string)($selectedLead['source'] ?: '—')) ?></div>
                            <div><strong>Persona</strong><br><?= e((string)($selectedLead['persona'] ?: '—')) ?></div>
                            <div><strong>Capture</strong><br><?= e(date('d/m/Y H:i', strtotime((string)$selectedLead['created_at']))) ?></div>
                        </div>
                        <div style="margin-top:.75rem;"><strong>Message initial</strong><div class="crm-muted" style="margin-top:.2rem;"><?= nl2br(e((string)($selectedLead['message'] ?: 'Aucun message.'))) ?></div></div>

                        <form method="post" class="crm-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="crm_intent" value="update_status">
                            <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                            <label>
                                <strong>Statut</strong>
                                <select name="status" required>
                                    <?php foreach (CONVERTIR_CRM_STATUSES as $statusKey => $statusLabel): ?>
                                        <option value="<?= e($statusKey) ?>" <?= (string)$selectedLead['crm_status'] === $statusKey ? 'selected' : '' ?>><?= e($statusLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <strong>Note de suivi (optionnel)</strong>
                                <textarea name="note" rows="3" placeholder="Ex: appel effectué, client souhaite rappeler lundi..."><?= e((string)($selectedLead['crm_note'] ?? '')) ?></textarea>
                            </label>
                            <button type="submit">Enregistrer le statut</button>
                        </form>

                        <form method="post" class="crm-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="crm_intent" value="add_note">
                            <input type="hidden" name="lead_id" value="<?= (int)$selectedLead['id'] ?>">
                            <label>
                                <strong>Ajouter une interaction</strong>
                                <textarea name="note" rows="3" placeholder="Ex: relance SMS envoyée, rappel prévu vendredi" required></textarea>
                            </label>
                            <button type="submit">Ajouter à l'historique</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>

            <section class="crm-panel">
                <div class="crm-panel-head"><strong>Historique des interactions</strong></div>
                <div class="crm-panel-body">
                    <?php if (!$selectedLead): ?>
                        <div class="crm-muted">Aucun lead sélectionné.</div>
                    <?php elseif (!$selectedLeadHistory): ?>
                        <div class="crm-muted">Aucune interaction enregistrée pour ce lead.</div>
                    <?php else: ?>
                        <div class="crm-history">
                            <?php foreach ($selectedLeadHistory as $event): ?>
                                <article class="crm-history-item">
                                    <div>
                                        <?php if ((string)$event['interaction_type'] === 'status'): ?>
                                            <strong>Statut :</strong>
                                            <?= e(CONVERTIR_CRM_STATUSES[(string)($event['old_value'] ?? 'nouveau')] ?? 'Nouveau') ?>
                                            →
                                            <?= e(CONVERTIR_CRM_STATUSES[(string)($event['new_value'] ?? 'nouveau')] ?? 'Nouveau') ?>
                                        <?php else: ?>
                                            <strong>Note ajoutée</strong>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($event['note'])): ?>
                                        <div class="crm-muted" style="margin-top:.35rem;"><?= nl2br(e((string)$event['note'])) ?></div>
                                    <?php endif; ?>
                                    <div class="crm-muted" style="margin-top:.35rem;"><?= e(date('d/m/Y H:i', strtotime((string)$event['created_at']))) ?></div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>
    <?php
}

function renderContent(): void
{
    global $crmContactsView, $crmLeads, $selectedLead, $selectedLeadHistory;
    ?>
    <div class="page-header">
        <h1><i class="fas fa-arrow-trend-up page-icon"></i> HUB <span class="page-title-accent">Convertir</span></h1>
        <p>Transformez vos contacts en clients signés</p>
    </div>

    <?php if ($crmContactsView): ?>
        <?php renderConvertirCrmContacts($crmLeads, $selectedLead, $selectedLeadHistory); ?>
    <?php else: ?>
        <?php renderConvertirHubCards(); ?>
    <?php endif; ?>
    <?php
}
