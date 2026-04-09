<?php
/** @var ImapService $imap */
/** @var MessageRepository $repo */
/** @var int $userId */

$isConfigured = $imap->isConfigured();
$advisorEmail = $imap->getAdvisorEmail();
$threadId     = isset($_GET['thread_id']) ? (int)$_GET['thread_id'] : 0;
$threads      = $isConfigured ? $repo->getThreads($userId, 80) : [];
$totalUnread  = $isConfigured ? $repo->getTotalUnread($userId) : 0;

$activeThread   = null;
$activeMessages = [];
if ($threadId > 0 && $isConfigured) {
    $activeThread = $repo->getThread($userId, $threadId);
    if ($activeThread) {
        $activeMessages = $repo->getMessages($threadId);
        $repo->markThreadRead($userId, $threadId);
    }
}
?>

<style>
.msg-layout{display:grid;grid-template-columns:320px 1fr;height:calc(100vh - 130px);border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;background:#fff;}
.msg-sidebar{border-right:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden;}
.msg-sidebar-header{padding:12px 14px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;background:#fafafa;flex-shrink:0;}
.msg-sidebar-header h2{font-size:.9rem;font-weight:700;color:#0f172a;flex:1;margin:0;}
.msg-search{padding:8px 12px;border-bottom:1px solid #e5e7eb;flex-shrink:0;}
.msg-search input{width:100%;padding:6px 10px 6px 30px;border:1px solid #e2e8f0;border-radius:8px;font-size:.82rem;background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 9px center;}
.msg-threads{overflow-y:auto;flex:1;}
.msg-thread-item{padding:11px 13px;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .12s;}
.msg-thread-item:hover{background:#f8fafc;}
.msg-thread-item.active{background:#eff6ff;border-left:3px solid #2563eb;}
.msg-thread-item.unread .msg-ti-name{font-weight:700;}
.msg-ti-top{display:flex;justify-content:space-between;align-items:baseline;gap:4px;}
.msg-ti-name{font-size:.85rem;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.msg-ti-date{font-size:.7rem;color:#94a3b8;flex-shrink:0;}
.msg-ti-subject{font-size:.76rem;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;}
.msg-ti-snippet{font-size:.73rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px;}
.msg-ti-badges{display:flex;align-items:center;gap:4px;margin-top:3px;}
.msg-unread-dot{width:7px;height:7px;border-radius:50%;background:#2563eb;flex-shrink:0;}
.msg-badge-crm{font-size:.62rem;padding:1px 5px;border-radius:999px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;}
.msg-main{display:flex;flex-direction:column;overflow:hidden;}
.msg-main-header{padding:12px 18px;border-bottom:1px solid #e5e7eb;background:#fafafa;flex-shrink:0;display:flex;align-items:center;gap:10px;}
.msg-main-header h3{font-size:.9rem;font-weight:700;color:#0f172a;flex:1;margin:0;}
.msg-messages{flex:1;overflow-y:auto;padding:14px 18px;display:flex;flex-direction:column;gap:10px;}
.msg-bw{display:flex;flex-direction:column;}
.msg-bw.outbound{align-items:flex-end;}
.msg-bw.inbound{align-items:flex-start;}
.msg-bw-meta{font-size:.7rem;color:#94a3b8;margin-bottom:2px;}
.msg-bubble{max-width:70%;padding:9px 13px;border-radius:13px;font-size:.84rem;line-height:1.55;word-break:break-word;}
.msg-bubble.outbound{background:#2563eb;color:#fff;border-bottom-right-radius:3px;}
.msg-bubble.inbound{background:#f1f5f9;color:#0f172a;border-bottom-left-radius:3px;}
.msg-bubble a{color:inherit;text-decoration:underline;}
.msg-reply{padding:10px 14px;border-top:1px solid #e5e7eb;flex-shrink:0;background:#fafafa;}
.msg-reply textarea{width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:8px 11px;font:inherit;font-size:.84rem;resize:none;min-height:65px;}
.msg-reply-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:7px;align-items:center;}
.msg-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:8px;color:#94a3b8;}
.msg-empty i{font-size:2.2rem;opacity:.35;}
.btn-send{background:#2563eb;color:#fff;border:0;padding:7px 16px;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer;}
.btn-send:disabled{opacity:.5;cursor:not-allowed;}
.btn-icon{background:#f1f5f9;color:#475569;border:0;padding:6px 10px;border-radius:8px;font-size:.78rem;cursor:pointer;}
.btn-icon:hover{background:#e2e8f0;}
.msg-no-config{padding:36px;text-align:center;}
.msg-no-config i{font-size:2rem;color:#94a3b8;margin-bottom:12px;display:block;}
</style>

<div class="page-header">
    <h1>
        <i class="fas fa-envelope page-icon"></i> Messagerie
        <?php if ($totalUnread > 0): ?>
            <span class="badge badge-warning" style="font-size:.62rem;vertical-align:middle;margin-left:6px;"><?= $totalUnread ?> non lu<?= $totalUnread > 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </h1>
    <p><?= $isConfigured ? htmlspecialchars($advisorEmail) : 'SMTP non configuré — rendez-vous dans Paramètres.' ?></p>
</div>

<?php if (!$isConfigured): ?>
<div class="card">
    <div class="msg-no-config">
        <i class="fas fa-envelope-open"></i>
        <h3 style="margin:0 0 8px;font-size:1rem;">Aucun serveur mail configuré</h3>
        <p style="color:#64748b;font-size:.88rem;margin:0 0 16px;">Configurez votre SMTP dans Paramètres pour activer la messagerie.</p>
        <a href="/admin?module=parametres&section=smtp" class="btn-send" style="text-decoration:none;">
            <i class="fas fa-gear"></i> Configurer le SMTP
        </a>
    </div>
</div>
<?php else: ?>

<div class="msg-layout">

    <!-- LISTE DES CONVERSATIONS -->
    <div class="msg-sidebar">
        <div class="msg-sidebar-header">
            <h2><i class="fas fa-inbox" style="color:#2563eb;margin-right:4px;font-size:.85rem;"></i> Boîte de réception</h2>
            <button class="btn-icon" onclick="openCompose()" title="Nouveau message"><i class="fas fa-pen"></i></button>
            <button class="btn-icon" id="syncBtn" onclick="syncInbox()" title="Synchroniser">
                <i class="fas fa-rotate-right" id="syncIcon"></i>
            </button>
        </div>
        <div class="msg-search">
            <input type="text" placeholder="Rechercher..." id="threadSearch" oninput="filterThreads(this.value)">
        </div>
        <div class="msg-threads" id="threadList">
            <?php if (empty($threads)): ?>
                <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.82rem;">
                    Aucun message.<br>Cliquez sur <i class="fas fa-rotate-right"></i> pour synchroniser.
                </div>
            <?php else: ?>
                <?php foreach ($threads as $t): ?>
                <?php
                    $isActive  = (int)$t['id'] === $threadId;
                    $hasUnread = (int)$t['unread_count'] > 0;
                    $hasCrm    = !empty($t['contact_id']);
                    $dateStr   = '';
                    if (!empty($t['last_message_at'])) {
                        try {
                            $d = new DateTime($t['last_message_at']);
                            $dateStr = $d->format('d/m') === date('d/m') ? $d->format('H:i') : $d->format('d/m');
                        } catch (Throwable) {}
                    }
                ?>
                <div class="msg-thread-item <?= $isActive ? 'active' : '' ?> <?= $hasUnread ? 'unread' : '' ?>"
                     data-name="<?= htmlspecialchars(strtolower($t['contact_name'] . ' ' . $t['contact_email'])) ?>"
                     onclick="openThread(<?= (int)$t['id'] ?>)">
                    <div class="msg-ti-top">
                        <span class="msg-ti-name"><?= htmlspecialchars($t['contact_name'] ?: $t['contact_email']) ?></span>
                        <span class="msg-ti-date"><?= $dateStr ?></span>
                    </div>
                    <div class="msg-ti-subject"><?= htmlspecialchars($t['subject']) ?></div>
                    <div class="msg-ti-snippet"><?= htmlspecialchars($t['snippet'] ?? '') ?></div>
                    <div class="msg-ti-badges">
                        <?php if ($hasUnread): ?><span class="msg-unread-dot"></span><?php endif; ?>
                        <?php if ($hasCrm): ?><span class="msg-badge-crm"><i class="fas fa-user"></i> CRM</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ZONE MESSAGES -->
    <div class="msg-main">
        <?php if ($activeThread): ?>
            <?php
            $contactUrl = '';
            if (!empty($activeThread['contact_id'])) {
                $contactUrl = match($activeThread['contact_type'] ?? '') {
                    'crm'     => '/admin?module=convertir&lead_id='  . (int)$activeThread['contact_id'],
                    'contact' => '/admin?module=capturer&contact_id=' . (int)$activeThread['contact_id'],
                    default   => '',
                };
            }
            ?>
            <div class="msg-main-header">
                <div style="width:32px;height:32px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;color:#2563eb;font-weight:700;font-size:.85rem;flex-shrink:0;">
                    <?= htmlspecialchars(strtoupper(substr($activeThread['contact_name'] ?: $activeThread['contact_email'], 0, 1))) ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <h3><?= htmlspecialchars($activeThread['contact_name'] ?: $activeThread['contact_email']) ?></h3>
                    <div style="font-size:.72rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($activeThread['contact_email']) ?></div>
                </div>
                <?php if ($contactUrl): ?>
                    <a href="<?= htmlspecialchars($contactUrl) ?>" style="font-size:.76rem;color:#2563eb;text-decoration:none;white-space:nowrap;">
                        <i class="fas fa-user"></i> Fiche contact
                    </a>
                <?php endif; ?>
            </div>

            <div class="msg-messages" id="msgMessages">
                <?php foreach ($activeMessages as $m): ?>
                <?php
                    $dir  = $m['direction'];
                    $ts   = $m['sent_at'] ?? $m['created_at'];
                    $when = '';
                    if ($ts) { try { $when = (new DateTime($ts))->format('d/m/Y H:i'); } catch(Throwable) {} }
                    $body = $m['body_html'] ?: nl2br(htmlspecialchars((string)($m['body_text'] ?? '')));
                ?>
                <div class="msg-bw <?= $dir ?>">
                    <div class="msg-bw-meta">
                        <?= htmlspecialchars($m['from_name'] ?: $m['from_email']) ?> · <?= $when ?>
                    </div>
                    <div class="msg-bubble <?= $dir ?>">
                        <?= $body ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="msg-reply">
                <input type="hidden" id="replyTo"      value="<?= htmlspecialchars($activeThread['contact_email']) ?>">
                <input type="hidden" id="replySubject" value="Re: <?= htmlspecialchars($activeThread['subject']) ?>">
                <textarea id="replyBody" placeholder="Répondre à <?= htmlspecialchars($activeThread['contact_name'] ?: $activeThread['contact_email']) ?>..." rows="3"></textarea>
                <div class="msg-reply-actions">
                    <span id="replyFeedback" style="font-size:.76rem;color:#64748b;flex:1;"></span>
                    <button class="btn-send" id="replyBtn" onclick="sendReply()">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </div>

        <?php else: ?>
            <div class="msg-empty">
                <i class="fas fa-envelope-open"></i>
                <span>Sélectionnez une conversation</span>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- MODALE COMPOSER -->
<div id="composeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:14px;width:500px;max-width:94vw;padding:22px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="margin:0;font-size:.95rem;">Nouveau message</h3>
            <button onclick="closeCompose()" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <div style="display:flex;flex-direction:column;gap:9px;">
            <input type="email" id="composeTo"      placeholder="À (adresse email)" style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 11px;font:inherit;font-size:.86rem;">
            <input type="text"  id="composeSubject" placeholder="Objet"             style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 11px;font:inherit;font-size:.86rem;">
            <textarea id="composeBody" placeholder="Votre message..." rows="6" style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 11px;font:inherit;font-size:.86rem;resize:vertical;"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;align-items:center;">
            <span id="composeFeedback" style="font-size:.76rem;color:#64748b;flex:1;"></span>
            <button onclick="closeCompose()" style="border:1px solid #d1d5db;background:#fff;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:.83rem;">Annuler</button>
            <button class="btn-send" id="composeBtn" onclick="sendCompose()"><i class="fas fa-paper-plane"></i> Envoyer</button>
        </div>
    </div>
</div>

<script>
function openThread(id) {
    window.location.href = '/admin?module=messagerie&thread_id=' + id;
}

function filterThreads(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.msg-thread-item').forEach(el => {
        el.style.display = (el.dataset.name || '').includes(q) ? '' : 'none';
    });
}

async function syncInbox() {
    const btn  = document.getElementById('syncBtn');
    const icon = document.getElementById('syncIcon');
    btn.disabled = true;
    icon.classList.add('fa-spin');
    try {
        const r = await fetch('/admin?module=messagerie&action=sync');
        const d = await r.json();
        if (d.ok) {
            showToast(d.imported + ' nouveau(x) message(s) importé(s)');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(d.error || 'Erreur de synchronisation', true);
        }
    } catch(e) {
        showToast('Erreur réseau', true);
    } finally {
        btn.disabled = false;
        icon.classList.remove('fa-spin');
    }
}

async function sendReply() {
    const to      = document.getElementById('replyTo').value;
    const subject = document.getElementById('replySubject').value;
    const body    = document.getElementById('replyBody').value.trim();
    const btn     = document.getElementById('replyBtn');
    const fb      = document.getElementById('replyFeedback');

    if (!body) { fb.textContent = 'Message vide.'; return; }
    btn.disabled = true; fb.textContent = 'Envoi...';

    const fd = new FormData();
    fd.append('to', to); fd.append('subject', subject); fd.append('body', body);

    try {
        const r = await fetch('/admin?module=messagerie&action=send', { method: 'POST', body: fd });
        const d = await r.json();
        if (d.ok) {
            document.getElementById('replyBody').value = '';
            fb.textContent = '';
            showToast('Message envoyé !');
            setTimeout(() => location.reload(), 700);
        } else {
            fb.textContent = d.error || 'Erreur envoi.';
            btn.disabled = false;
        }
    } catch(e) {
        fb.textContent = 'Erreur réseau.';
        btn.disabled = false;
    }
}

function openCompose() {
    document.getElementById('composeModal').style.display = 'flex';
    document.getElementById('composeTo').focus();
}
function closeCompose() {
    document.getElementById('composeModal').style.display = 'none';
}

async function sendCompose() {
    const to      = document.getElementById('composeTo').value.trim();
    const subject = document.getElementById('composeSubject').value.trim();
    const body    = document.getElementById('composeBody').value.trim();
    const btn     = document.getElementById('composeBtn');
    const fb      = document.getElementById('composeFeedback');

    if (!to || !subject || !body) { fb.textContent = 'Tous les champs sont requis.'; return; }
    btn.disabled = true; fb.textContent = 'Envoi...';

    const fd = new FormData();
    fd.append('to', to); fd.append('subject', subject); fd.append('body', body);

    try {
        const r = await fetch('/admin?module=messagerie&action=send', { method: 'POST', body: fd });
        const d = await r.json();
        if (d.ok) {
            closeCompose();
            showToast('Message envoyé !');
            setTimeout(() => location.reload(), 700);
        } else {
            fb.textContent = d.error || 'Erreur.';
            btn.disabled = false;
        }
    } catch(e) {
        fb.textContent = 'Erreur réseau.';
        btn.disabled = false;
    }
}

function showToast(msg, isError = false) {
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = `position:fixed;bottom:20px;right:20px;background:${isError?'#ef4444':'#22c55e'};color:#fff;padding:9px 16px;border-radius:9px;font-size:.83rem;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,.18);`;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
}

// Auto-scroll bas
const msgs = document.getElementById('msgMessages');
if (msgs) msgs.scrollTop = msgs.scrollHeight;
</script>

<?php endif; ?>
