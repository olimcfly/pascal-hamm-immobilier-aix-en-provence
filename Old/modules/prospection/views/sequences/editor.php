<?php
// modules/prospection/views/sequences/editor.php
$pageTitle  = 'Éditeur de séquence';
$flash      = Session::getFlash();
$mailMode   = \ProspectionMailer::currentMode();
$isTestMode = \ProspectionMailer::isTestMode();

// Charger la campagne
$campaign = $campaignService->getById($campaign_id);
if (!$campaign) {
    echo '<div class="alert alert-danger">Campagne introuvable.</div>';
    return;
}

// Étape en cours d'édition ?
$editStepId = isset($_GET['edit_step']) ? (int)$_GET['edit_step'] : 0;
$editStep   = null;
foreach ($steps as $s) {
    if ((int)$s['id'] === $editStepId) {
        $editStep = $s;
        break;
    }
}

$cid = (int)$campaign['id'];

// Variables disponibles
$vars = ['{{first_name}}','{{last_name}}','{{email}}','{{company}}','{{city}}'];
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- En-tête -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="?module=prospection&action=campaign-detail&campaign_id=<?= $cid ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Campagne
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold"><i class="fas fa-list-ol text-secondary me-2"></i>Séquence — <?= e($campaign['name']) ?></h1>
        <p class="text-muted mb-0 small"><?= count($steps) ?> étape<?= count($steps) > 1 ? 's' : '' ?> — définissez l'enchaînement des emails</p>
    </div>
</div>

<!-- Bloc pédagogique -->
<div class="alert border-0 mb-4 py-2 px-3 small" style="background:#fffbeb;color:#92400e;border-left:3px solid #f59e0b !important;">
    <i class="fas fa-lightbulb me-2"></i>
    <strong>Bonne pratique :</strong> commencez par un email court et simple (J0), puis espacez progressivement.
    Utilisez <code>{{first_name}}</code> pour personnaliser. La séquence s'arrête automatiquement si le contact répond.
</div>

<div class="row g-4">

    <!-- Liste des étapes -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div class="fw-semibold"><i class="fas fa-list-check me-2"></i>Étapes (<?= count($steps) ?>)</div>
                <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>" class="btn btn-outline-primary btn-sm"
                   title="Ajouter une étape" onclick="document.getElementById('step-editor').classList.remove('d-none');return false;"
                   id="add-step-btn">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </a>
            </div>
            <?php if (empty($steps)): ?>
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-inbox fa-2x mb-2 opacity-25"></i>
                <div class="small">Aucune étape.</div>
                <div class="small mt-1">Commencez par ajouter le premier email (J0).</div>
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($steps as $i => $step): ?>
                <div class="list-group-item border-0 px-3 py-2 <?= !$step['is_active'] ? 'opacity-50' : '' ?>">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                             style="width:28px;height:28px;font-size:.75rem;background:#3b82f6;">
                            <?= (int)$step['step_order'] ?>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold small text-truncate"><?= e($step['subject']) ?></div>
                            <div class="text-muted" style="font-size:.72rem;">
                                <i class="fas fa-clock me-1"></i>
                                <?= $step['delay_days'] == 0 ? 'J0 — immédiatement' : 'J+' . (int)$step['delay_days'] . ' jour' . ((int)$step['delay_days'] > 1 ? 's' : '') ?>
                                <?php if (!(bool)$step['is_active']): ?>
                                · <span class="text-warning">désactivé</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button type="button"
                                    class="btn btn-xs btn-outline-warning" title="Envoyer un test"
                                    style="padding:2px 7px;font-size:.72rem;"
                                    onclick="openTestModal(<?= $cid ?>, <?= (int)$step['id'] ?>)">
                                <i class="fas fa-flask"></i>
                            </button>
                            <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>&edit_step=<?= $step['id'] ?>"
                               class="btn btn-xs btn-outline-secondary" title="Modifier" style="padding:2px 7px;font-size:.72rem;">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="?module=prospection" class="d-inline"
                                  onsubmit="return confirm('Supprimer cette étape ?')">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="step_delete">
                                <input type="hidden" name="campaign_id" value="<?= $cid ?>">
                                <input type="hidden" name="step_id" value="<?= $step['id'] ?>">
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Supprimer"
                                        style="padding:2px 7px;font-size:.72rem;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulaire d'ajout / édition d'étape -->
    <div class="col-12 col-lg-7">
        <div id="step-editor" class="<?= $editStep ? '' : 'd-none' ?>">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="fw-semibold">
                        <i class="fas fa-<?= $editStep ? 'pen' : 'plus' ?> me-2 text-primary"></i>
                        <?= $editStep ? 'Modifier l\'étape' : 'Nouvelle étape' ?>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="?module=prospection" novalidate>
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="step_save">
                        <input type="hidden" name="campaign_id" value="<?= $cid ?>">
                        <input type="hidden" name="step_id" value="<?= $editStep ? $editStep['id'] : 0 ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Délai (jours depuis le début)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <input type="number" name="delay_days" class="form-control" min="0" max="365"
                                           value="<?= $editStep ? (int)$editStep['delay_days'] : '' ?>"
                                           placeholder="0 = immédiat">
                                </div>
                                <div class="form-text">0 = J0, 2 = J+2, 5 = J+5…</div>
                            </div>
                            <div class="col-6 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="step-active"
                                           <?= !$editStep || $editStep['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="step-active">Étape active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Objet de l'email <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required
                                   value="<?= e($editStep['subject'] ?? '') ?>"
                                   placeholder="Ex : Question rapide, {{first_name}} ?" maxlength="255" id="subject-input">
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold small mb-0">Corps de l'email <span class="text-danger">*</span></label>
                                <div class="d-flex gap-1 flex-wrap">
                                    <?php foreach ($vars as $v): ?>
                                    <button type="button" class="btn btn-xs btn-outline-secondary"
                                            onclick="insertVar('<?= $v ?>')"
                                            style="font-size:.65rem;padding:1px 6px;">
                                        <?= e($v) ?>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <textarea name="body_text" class="form-control" rows="10" required
                                      id="body-input"
                                      placeholder="Rédigez votre email ici. Utilisez les variables ci-dessus pour personnaliser."><?= e($editStep['body_text'] ?? '') ?></textarea>
                            <div class="form-text">Texte brut, pas de HTML. Utilisez des retours à la ligne.</div>
                        </div>

                        <!-- Aperçu -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div class="fw-semibold small">Aperçu</div>
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="togglePreview()" style="font-size:.72rem;">
                                    <i class="fas fa-eye me-1"></i>Voir le rendu
                                </button>
                            </div>
                            <div id="preview-box" class="d-none rounded border p-3 bg-light small"
                                 style="white-space:pre-wrap;font-family:inherit;min-height:80px;max-height:200px;overflow-y:auto;"></div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="?module=prospection&action=sequence&campaign_id=<?= $cid ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-xmark me-2"></i>Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-floppy-disk me-2"></i>
                                <?= $editStep ? 'Enregistrer les modifications' : 'Ajouter l\'étape' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!$editStep && empty($steps)): ?>
        <!-- État vide pédagogique -->
        <div class="card border-0 shadow-sm <?= $editStep ? 'd-none' : '' ?>" id="empty-hint">
            <div class="card-body text-center py-5">
                <i class="fas fa-paper-plane fa-3x text-muted mb-3 opacity-25"></i>
                <h5 class="text-muted">Construisez votre séquence</h5>
                <p class="text-muted small mb-4">
                    Une séquence efficace commence par un email simple (J0), suivi de relances espacées.
                    Chaque email doit apporter une valeur ou une question différente.
                </p>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('step-editor').classList.remove('d-none');document.getElementById('empty-hint').classList.add('d-none')">
                    <i class="fas fa-plus me-2"></i>Ajouter le premier email
                </button>
            </div>
        </div>
        <?php elseif (!$editStep): ?>
        <div class="card border-0 shadow-sm" id="empty-hint">
            <div class="card-body text-center py-5">
                <i class="fas fa-pen-to-square fa-2x text-muted mb-3 opacity-25"></i>
                <h6 class="text-muted">Sélectionnez une étape à modifier ou ajoutez-en une nouvelle.</h6>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                    onclick="document.getElementById('step-editor').classList.remove('d-none');document.getElementById('empty-hint').classList.add('d-none')">
                    <i class="fas fa-plus me-1"></i>Nouvelle étape
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
document.getElementById('add-step-btn').addEventListener('click', function(e) {
    e.preventDefault();
    var editor = document.getElementById('step-editor');
    var hint   = document.getElementById('empty-hint');
    editor.classList.remove('d-none');
    if (hint) hint.classList.add('d-none');
    editor.scrollIntoView({behavior:'smooth', block:'start'});
});

function insertVar(v) {
    var ta = document.getElementById('body-input');
    var start = ta.selectionStart;
    var end   = ta.selectionEnd;
    ta.value = ta.value.substring(0, start) + v + ta.value.substring(end);
    ta.selectionStart = ta.selectionEnd = start + v.length;
    ta.focus();
}

function togglePreview() {
    var box     = document.getElementById('preview-box');
    var subject = document.getElementById('subject-input').value;
    var body    = document.getElementById('body-input').value;

    // Remplacement de variables avec des valeurs exemple
    function replaceVars(text) {
        return text
            .replace(/\{\{first_name\}\}/g, 'Jean')
            .replace(/\{\{last_name\}\}/g,  'Dupont')
            .replace(/\{\{email\}\}/g,      'jean@exemple.fr')
            .replace(/\{\{company\}\}/g,    'SARL Exemple')
            .replace(/\{\{city\}\}/g,       'Aix-en-Provence')
            .replace(/\{\{phone\}\}/g,      '0600000000');
    }

    if (box.classList.contains('d-none')) {
        box.innerHTML = '<strong>Objet :</strong> ' + escHtml(replaceVars(subject)) + '\n\n' + escHtml(replaceVars(body));
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Test email
var _tCid = 0, _tSid = 0;
var csrf = <?= json_encode($_SESSION['csrf_token'] ?? '') ?>;

function openTestModal(cid, sid) {
    _tCid = cid; _tSid = sid;
    document.getElementById('seq-test-result').classList.add('d-none');
    document.getElementById('seq-test-modal').classList.remove('d-none');
}
function closeSeqTestModal() {
    document.getElementById('seq-test-modal').classList.add('d-none');
}
function submitSeqTest() {
    var btn = document.getElementById('seq-test-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi…';
    var fd = new FormData();
    fd.append('csrf_token', csrf);
    fd.append('campaign_id', _tCid);
    fd.append('step_id', _tSid);
    fd.append('preview_first_name', document.getElementById('seq-t-fname').value);
    fd.append('preview_email', document.getElementById('seq-t-email').value);
    fetch('?module=prospection&ajax=send_test', {method:'POST', body:fd})
        .then(r => r.json())
        .then(function(data) {
            var box = document.getElementById('seq-test-result');
            box.classList.remove('d-none');
            if (data.ok) {
                box.className = 'alert alert-success py-2 px-3 small mt-3';
                box.innerHTML = '<i class="fas fa-check me-1"></i>Envoyé (' + escHtml(data.mode) + ') → <strong>' + escHtml(data.sent_to) + '</strong>';
            } else {
                box.className = 'alert alert-danger py-2 px-3 small mt-3';
                box.innerHTML = 'Échec : ' + escHtml(data.error || '?');
            }
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Envoyer';
        });
}
document.getElementById('seq-test-modal').addEventListener('click', function(e) {
    if (e.target === this) closeSeqTestModal();
});
</script>

<!-- Modal test email séquence -->
<div id="seq-test-modal" class="d-none" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.4);display:flex!important;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:1rem;max-width:420px;width:100%;box-shadow:0 24px 64px rgba(0,0,0,.2);">
        <div class="p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-flask me-2 text-warning"></i>Email test</h6>
                <button type="button" onclick="closeSeqTestModal()" class="btn-close"></button>
            </div>
            <div class="alert py-2 px-3 small mb-3" style="background:#fef3c7;color:#92400e;">
                Mode <strong><?= strtoupper(e($mailMode)) ?></strong>
                <?php if ($isTestMode && \ProspectionMailer::testRecipient()): ?>
                — envoi vers <strong><?= e(\ProspectionMailer::testRecipient()) ?></strong>
                <?php elseif ($mailMode === 'log'): ?>
                — journalisation seule, aucun envoi réel
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Prénom test</label>
                <input type="text" id="seq-t-fname" class="form-control form-control-sm" value="Jean">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email destinataire</label>
                <input type="email" id="seq-t-email" class="form-control form-control-sm"
                       value="<?= e(\ProspectionMailer::testRecipient()) ?>"
                       placeholder="test@exemple.fr">
            </div>
            <div id="seq-test-result" class="d-none"></div>
            <div class="d-flex gap-2 justify-content-end mt-3">
                <button type="button" onclick="closeSeqTestModal()" class="btn btn-sm btn-outline-secondary">Annuler</button>
                <button type="button" onclick="submitSeqTest()" class="btn btn-sm btn-warning" id="seq-test-btn">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer
                </button>
            </div>
        </div>
    </div>
</div>
