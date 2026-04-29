<?php
/** @var ImapService $imap */
/** @var int $userId */

$isConfigured = $imap->isConfigured();
$advisorEmail = $imap->getAdvisorEmail();
$imapExtOk    = ImapService::imapExtensionAvailable();
$imapHost     = (string) setting('imap_host',   $_ENV['IMAP_HOST']   ?? setting('smtp_host',   $_ENV['SMTP_HOST']   ?? ''), $userId);
$imapPort     = (string) setting('imap_port',   $_ENV['IMAP_PORT']   ?? '993', $userId);
$imapUser     = (string) setting('imap_user',   $_ENV['IMAP_USER']   ?? $advisorEmail, $userId);
$imapSecure   = (string) setting('imap_secure', $_ENV['IMAP_SECURE'] ?? 'ssl', $userId);
$smtpHost     = (string) setting('smtp_host',   $_ENV['SMTP_HOST']   ?? ($imapHost ?: 'mail.pascal-hamm-immobilier-aix-en-provence.fr'), $userId);
$smtpPort     = (string) setting('smtp_port',   $_ENV['SMTP_PORT']   ?? '465', $userId);
$smtpSecure   = (string) setting('smtp_secure', $_ENV['SMTP_SECURE'] ?? 'ssl', $userId);
$fromName     = (string) setting('smtp_from_name', $_ENV['SMTP_FROM_NAME'] ?? 'Pascal Hamm Immobilier', $userId);
$imapPassSaved = trim((string) setting('imap_pass', '', $userId));
$smtpPassSaved = trim((string) setting('smtp_pass', '', $userId));
$default02SwitchHost = 'mail.pascal-hamm-immobilier-aix-en-provence.fr';
$default02SwitchUser = 'superuser@pascal-hamm-immobilier-aix-en-provence.fr';
?>
<style>
.conn-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:24px;max-width:760px;}
.conn-status{display:flex;align-items:center;gap:10px;padding:14px;border-radius:10px;margin-bottom:20px;}
.conn-status.ok{background:#dcfce7;border:1px solid #bbf7d0;}
.conn-status.nok{background:#fef3c7;border:1px solid #fde68a;}
.conn-status i{font-size:1.1rem;}
.conn-status.ok i{color:#16a34a;}
.conn-status.nok i{color:#d97706;}
.conn-status-text h4{margin:0;font-size:.9rem;font-weight:700;}
.conn-status-text p{margin:2px 0 0;font-size:.78rem;color:#475569;}
.conn-alert{margin-bottom:14px;padding:12px 14px;border-radius:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:.8rem;line-height:1.5;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.form-group{display:flex;flex-direction:column;gap:4px;}
.form-group.full{grid-column:1/-1;}
.form-group label{font-size:.76rem;font-weight:600;color:#374151;}
.form-group input,.form-group select{border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font:inherit;font-size:.84rem;}
.password-row{display:flex;gap:8px;}
.password-row input{flex:1;min-width:0;}
.btn-password{border:1px solid #e2e8f0;background:#fff;color:#0f2237;border-radius:8px;padding:0 10px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;}
.btn-password:hover{background:#f8fafc;}
.form-section{grid-column:1/-1;margin:8px 0 2px;padding-top:10px;border-top:1px solid #e5e7eb;font-weight:800;color:#0f2237;font-size:.84rem;}
.form-actions{display:flex;gap:8px;margin-top:16px;align-items:center;}
.btn-test{background:#f1f5f9;color:#475569;border:0;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-test:hover{background:#e2e8f0;}
.btn-save{background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-save:hover{background:#1d4ed8;}
.btn-smtp{background:#0f2237;color:#fff;border:0;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-smtp:hover{background:#1a3a5c;}
.btn-disconnect{background:#fee2e2;color:#991b1b;border:0;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
#formFeedback{font-size:.78rem;color:#64748b;}
.help-box{margin-top:20px;padding:12px;background:#f8fafc;border-radius:9px;border:1px solid #e5e7eb;}
.help-box p{font-size:.76rem;color:#64748b;margin:0;line-height:1.6;}
.help-box code{color:#0f2237;}
</style>

<div class="page-header" style="margin-bottom:16px;">
    <h1><i class="fas fa-plug page-icon"></i> Connexion email</h1>
    <p>Configurez votre compte IMAP pour recevoir et envoyer des emails depuis l'application.</p>
</div>

<?php if (!$imapExtOk): ?>
<div class="conn-status nok" style="max-width:520px;margin-bottom:14px;border:1px solid #fecaca;background:#fef2f2;">
    <i class="fas fa-triangle-exclamation" style="color:#dc2626;"></i>
    <div class="conn-status-text">
        <h4 style="color:#991b1b;">Extension PHP « imap » inactive</h4>
        <p style="color:#7f1d1d;">Sans elle, la connexion à la boîte ne peut pas fonctionner. Activez <code>imap</code> pour la version PHP du site (hébergeur / cPanel « Extensions »).</p>
    </div>
</div>
<?php endif; ?>

<div class="conn-card">

    <!-- Statut -->
    <div class="conn-status <?= ($isConfigured && $imapExtOk) ? 'ok' : 'nok' ?>">
        <i class="fas <?= ($isConfigured && $imapExtOk) ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
        <div class="conn-status-text">
            <h4><?= ($isConfigured && $imapExtOk) ? 'Identifiants enregistrés' : ($imapExtOk ? 'Connexion incomplète ou absente' : 'Configuration impossible (serveur)') ?></h4>
            <p><?= ($isConfigured && $imapExtOk) ? htmlspecialchars($advisorEmail ?: '—') : 'Renseignez hôte, email et mot de passe IMAP ci-dessous, puis testez.' ?></p>
        </div>
        <?php if ($isConfigured && $imapExtOk): ?>
            <button class="btn-disconnect" style="margin-left:auto;" onclick="disconnectAccount()">
                <i class="fas fa-xmark"></i> Déconnecter
            </button>
        <?php endif; ?>
    </div>

    <?php if ($imapPassSaved === ''): ?>
        <div class="conn-alert">
            Le mot de passe IMAP n’est pas enregistré pour ce compte. Saisis-le avant de tester la connexion ou l’envoi SMTP.
        </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <form onsubmit="saveConfig(event)">
        <div class="form-grid">
            <div class="form-section">Boîte 02switch recommandée</div>
            <div class="form-group">
                <label>Utilisateur conseillé</label>
                <input type="email" value="<?= htmlspecialchars($default02SwitchUser) ?>" readonly onclick="this.select()">
            </div>
            <div class="form-group">
                <label>Serveur conseillé</label>
                <input type="text" value="<?= htmlspecialchars($default02SwitchHost) ?>" readonly onclick="this.select()">
            </div>

            <div class="form-section">Réception IMAP</div>
            <div class="form-group full">
                <label>Adresse email (nom d'utilisateur IMAP)</label>
                <input type="email" id="cfgUser" value="<?= htmlspecialchars($imapUser ?: $default02SwitchUser) ?>" placeholder="<?= htmlspecialchars($default02SwitchUser) ?>" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <div class="password-row">
                    <input type="password" id="cfgPass" placeholder="<?= $isConfigured ? 'Laisse vide pour conserver le mot de passe enregistré' : 'Mot de passe' ?>" autocomplete="new-password" spellcheck="false" autocapitalize="off">
                    <button type="button" class="btn-password" id="togglePassBtn" onclick="togglePassword('cfgPass', 'togglePassBtn')">Voir</button>
                </div>
            </div>
            <div class="form-group">
                <label>Sécurité</label>
                <select id="cfgSecure">
                    <option value="ssl"  <?= $imapSecure === 'ssl'  ? 'selected' : '' ?>>SSL (port 993)</option>
                    <option value="tls"  <?= $imapSecure === 'tls'  ? 'selected' : '' ?>>TLS (port 143)</option>
                    <option value="none" <?= $imapSecure === 'none' ? 'selected' : '' ?>>Aucune (port 143)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hôte IMAP <small style="font-weight:400;color:#94a3b8;">(auto si même que SMTP)</small></label>
                <input type="text" id="cfgHost" value="<?= htmlspecialchars($imapHost ?: $default02SwitchHost) ?>" placeholder="<?= htmlspecialchars($default02SwitchHost) ?>">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="number" id="cfgPort" value="<?= htmlspecialchars($imapPort ?: '993') ?>" min="1" max="65535">
            </div>

            <div class="form-section">Envoi SMTP</div>
            <div class="form-group">
                <label>Hôte SMTP</label>
                <input type="text" id="cfgSmtpHost" value="<?= htmlspecialchars($smtpHost ?: $default02SwitchHost) ?>" placeholder="<?= htmlspecialchars($default02SwitchHost) ?>">
            </div>
            <div class="form-group">
                <label>Mot de passe SMTP</label>
                <div class="password-row">
                    <input type="password" id="cfgSmtpPass" placeholder="<?= $smtpPassSaved !== '' ? 'Laisse vide pour conserver le mot de passe SMTP enregistré' : 'Mot de passe SMTP' ?>" autocomplete="new-password" spellcheck="false" autocapitalize="off">
                    <button type="button" class="btn-password" id="toggleSmtpPassBtn" onclick="togglePassword('cfgSmtpPass', 'toggleSmtpPassBtn')">Voir</button>
                </div>
            </div>
            <div class="form-group">
                <label>Port SMTP</label>
                <input type="number" id="cfgSmtpPort" value="<?= htmlspecialchars($smtpPort ?: '465') ?>" min="1" max="65535">
            </div>
            <div class="form-group">
                <label>Sécurité SMTP</label>
                <select id="cfgSmtpSecure">
                    <option value="ssl"  <?= $smtpSecure === 'ssl'  ? 'selected' : '' ?>>SSL (port 465)</option>
                    <option value="tls"  <?= $smtpSecure === 'tls'  ? 'selected' : '' ?>>TLS (port 587)</option>
                    <option value="none" <?= $smtpSecure === 'none' ? 'selected' : '' ?>>Aucune</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nom expéditeur</label>
                <input type="text" id="cfgFromName" value="<?= htmlspecialchars($fromName ?: 'Pascal Hamm Immobilier') ?>" placeholder="Pascal Hamm Immobilier">
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-test" id="testBtn" onclick="testConnection()" <?= !$imapExtOk ? 'disabled' : '' ?>>
                <i class="fas fa-plug"></i> Tester IMAP
            </button>
            <button type="button" class="btn-smtp" id="smtpBtn" onclick="testSmtp()">
                <i class="fas fa-paper-plane"></i> Tester SMTP
            </button>
            <span id="formFeedback"></span>
            <button type="submit" class="btn-save" style="margin-left:auto;">
                <i class="fas fa-floppy-disk"></i> Enregistrer
            </button>
        </div>
    </form>

    <!-- Aide -->
    <div class="help-box">
        <p>
            <strong>02switch :</strong> utilisateur = <code><?= htmlspecialchars($default02SwitchUser) ?></code>, serveur = <code><?= htmlspecialchars($default02SwitchHost) ?></code>, IMAP <strong>993 SSL</strong>, SMTP <strong>465 SSL</strong>.<br>
            <strong>OVH / cPanel :</strong> hôte = <code>mail.votredomaine.fr</code>, port SSL = 993<br>
            <strong>Infomaniak :</strong> hôte = <code>mail.infomaniak.com</code>, port SSL = 993<br>
            <strong>Google / Gmail :</strong> <code>imap.gmail.com</code>, port <strong>993</strong> SSL — avec 2FA, créez un <strong>mot de passe d’application</strong> (compte Google → Sécurité).<br>
            <strong>Microsoft 365 :</strong> <code>outlook.office365.com</code>, port <strong>993</strong> SSL (IMAP activé par l’admin Microsoft si besoin).<br>
            Sinon le mot de passe est en général celui de la boîte mail.
        </p>
    </div>
</div>

<script>
async function saveConfig(e) {
    e.preventDefault();
    return persistConfig({reload: true});
}
function ensureDefaultMailValues() {
    const host = document.getElementById('cfgHost');
    const smtpHost = document.getElementById('cfgSmtpHost');
    const user = document.getElementById('cfgUser');
    if (host && host.value.trim() === '') host.value = '<?= htmlspecialchars($default02SwitchHost, ENT_QUOTES, 'UTF-8') ?>';
    if (smtpHost && smtpHost.value.trim() === '') smtpHost.value = host ? host.value.trim() : '<?= htmlspecialchars($default02SwitchHost, ENT_QUOTES, 'UTF-8') ?>';
    if (user && user.value.trim() === '') user.value = '<?= htmlspecialchars($default02SwitchUser, ENT_QUOTES, 'UTF-8') ?>';
}
function togglePassword(inputId, buttonId) {
    const pass = document.getElementById(inputId);
    const btn = document.getElementById(buttonId);
    if (!pass || !btn) return;
    const visible = pass.type === 'text';
    pass.type = visible ? 'password' : 'text';
    btn.textContent = visible ? 'Voir' : 'Masquer';
}
async function persistConfig(options = {}) {
    const reload = options.reload === true;
    ensureDefaultMailValues();
    const fb = document.getElementById('formFeedback');
    fb.textContent = 'Enregistrement...';
    const fd = new FormData();
    fd.append('host',   document.getElementById('cfgHost').value.trim());
    fd.append('port',   document.getElementById('cfgPort').value);
    fd.append('user',   document.getElementById('cfgUser').value.trim());
    fd.append('pass',   document.getElementById('cfgPass').value);
    fd.append('secure', document.getElementById('cfgSecure').value);
    fd.append('smtp_host', document.getElementById('cfgSmtpHost').value.trim());
    fd.append('smtp_port', document.getElementById('cfgSmtpPort').value);
    fd.append('smtp_secure', document.getElementById('cfgSmtpSecure').value);
    fd.append('smtp_pass', document.getElementById('cfgSmtpPass').value);
    fd.append('from_name', document.getElementById('cfgFromName').value.trim());
    const d = await (await fetch('/admin?module=messagerie&action=save_imap', {method:'POST',body:fd})).json();
    if (d.ok) {
        fb.style.color='#16a34a';
        fb.textContent = '✓ Enregistré.';
        if (reload) setTimeout(() => location.reload(), 800);
    }
    else { fb.style.color='#ef4444'; fb.textContent = d.error||'Erreur.'; }
    return d;
}
async function fetchJson(url, options = {}) {
    const response = await fetch(url, options);
    const text = await response.text();
    let data = null;
    try {
        data = JSON.parse(text);
    } catch (e) {
        const preview = text.trim().replace(/\s+/g, ' ').slice(0, 200);
        throw new Error(preview || 'Réponse non JSON du serveur.');
    }
    if (!response.ok) {
        throw new Error(data.error || data.message || `HTTP ${response.status}`);
    }
    return data;
}
async function testConnection() {
    const btn = document.getElementById('testBtn');
    const fb  = document.getElementById('formFeedback');
    const saved = await persistConfig({reload: false});
    if (!saved.ok) return;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Test...';
    fb.style.color = '#64748b'; fb.textContent = '';
    try {
        const d = await fetchJson('/admin?module=messagerie&action=test_imap');
        if (d.ok) { fb.style.color='#16a34a'; fb.textContent = '✓ ' + d.message; }
        else { fb.style.color='#ef4444'; fb.textContent = '✗ ' + (d.error||'Échec.'); }
    } catch(e) { fb.style.color='#ef4444'; fb.textContent = '✗ ' + (e.message || 'Erreur réseau.'); }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-plug"></i> Tester IMAP'; }
}
async function testSmtp() {
    const btn = document.getElementById('smtpBtn');
    const fb  = document.getElementById('formFeedback');
    const saved = await persistConfig({reload: false});
    if (!saved.ok) return;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
    fb.style.color = '#64748b'; fb.textContent = '';
    try {
        const fd = new FormData();
        fd.append('to', document.getElementById('cfgUser').value.trim());
        const d = await fetchJson('/admin?module=messagerie&action=test_smtp', {method:'POST',body:fd});
        if (d.ok) { fb.style.color='#16a34a'; fb.textContent = '✓ ' + d.message; }
        else { fb.style.color='#ef4444'; fb.textContent = '✗ ' + (d.error||'Échec SMTP.'); }
    } catch(e) { fb.style.color='#ef4444'; fb.textContent = '✗ ' + (e.message || 'Erreur réseau.'); }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Tester SMTP'; }
}
async function disconnectAccount() {
    if (!confirm('Déconnecter ce compte ? Les messages synchronisés sont conservés.')) return;
    const d = await (await fetch('/admin?module=messagerie&action=disconnect_imap')).json();
    if (d.ok) { location.reload(); }
}
</script>
