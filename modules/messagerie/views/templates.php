<?php
/** @var TemplateRepository $tplRepo */
/** @var int $userId */

$templates  = $tplRepo->getAll($userId);
$categories = $tplRepo->categories();
$grouped    = [];
foreach ($templates as $t) {
    $grouped[$t['category']][] = $t;
}

$catIcons = [
    'prospection'    => ['icon' => 'fa-paper-plane', 'color' => '#3b82f6'],
    'rdv'            => ['icon' => 'fa-calendar-check', 'color' => '#10b981'],
    'suivi_vendeur'  => ['icon' => 'fa-house-chimney', 'color' => '#f59e0b'],
    'suivi_acheteur' => ['icon' => 'fa-key', 'color' => '#8b5cf6'],
    'offre'          => ['icon' => 'fa-file-signature', 'color' => '#ef4444'],
    'financement'    => ['icon' => 'fa-coins', 'color' => '#0e7490'],
    'signature'      => ['icon' => 'fa-pen-fancy', 'color' => '#c9a84c'],
    'apres_vente'    => ['icon' => 'fa-handshake', 'color' => '#64748b'],
    'general'        => ['icon' => 'fa-envelope', 'color' => '#2563eb'],
];
?>
<style>
/* ── Page (réf. « Commencer » : héros + colonne + cartes) ── */
.tpl-page { margin-bottom: 2rem; }
.tpl-hero {
    background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
    border-radius: 16px;
    padding: 28px 32px 32px;
    color: #fff;
    margin-bottom: 24px;
    box-shadow: 0 4px 20px rgba(15,34,55,.18);
}
.tpl-hero-badge {
    display: inline-block;
    background: rgba(201,168,76,.2);
    color: #c9a84c;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
    border: 1px solid rgba(201,168,76,.35);
}
.tpl-hero h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 10px;
    line-height: 1.25;
}
.tpl-hero p {
    font-size: .92rem;
    color: rgba(255,255,255,.72);
    line-height: 1.6;
    max-width: 42rem;
    margin: 0;
}
.tpl-hero-meta {
    margin-top: 14px;
    font-size: .78rem;
    color: rgba(255,255,255,.55);
}
.tpl-hero-meta code {
    background: rgba(255,255,255,.12);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: .72rem;
    color: #e2e8f0;
}

.tpl-layout {
    display: grid;
    grid-template-columns: minmax(240px, 270px) 1fr;
    gap: 22px;
    align-items: start;
}

.tpl-aside {
    position: sticky;
    top: 12px;
}
.tpl-aside-title {
    font-size: 12px;
    font-weight: 700;
    color: #8a95a3;
    text-transform: uppercase;
    letter-spacing: .07em;
    margin: 0 0 12px;
    padding-left: 4px;
}
.tpl-cat-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tpl-cat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    text-align: left;
    padding: 14px 14px 14px 12px;
    border: 0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    border-left: 4px solid #e8ecf0;
    cursor: pointer;
    color: inherit;
    transition: transform .15s, box-shadow .15s, border-color .15s, background .15s;
    font: inherit;
}
.tpl-cat-item:hover {
    transform: translateX(3px);
    box-shadow: 0 4px 14px rgba(0,0,0,.08);
    border-left-color: #c9a84c;
}
.tpl-cat-item.active {
    border-left-color: #c9a84c;
    background: linear-gradient(90deg, rgba(201,168,76,.08) 0%, #fff 40%);
    box-shadow: 0 2px 12px rgba(15,34,55,.08);
}
.tpl-cat-ico {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    flex-shrink: 0;
    background: #f1f5f9;
    color: #64748b;
}
.tpl-cat-item.active .tpl-cat-ico { background: #fffbeb; color: #b45309; }
.tpl-cat-body { flex: 1; min-width: 0; }
.tpl-cat-label {
    font-size: .88rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.25;
}
.tpl-cat-desc {
    font-size: .72rem;
    color: #94a3b8;
    margin-top: 2px;
}
.tpl-cat-count {
    font-size: .72rem;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    padding: 3px 8px;
    border-radius: 999px;
    flex-shrink: 0;
}
.tpl-cat-item.active .tpl-cat-count {
    background: rgba(201,168,76,.25);
    color: #0f2237;
}

.tpl-main-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.tpl-main-head h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}
.tpl-main-head p {
    margin: 4px 0 0;
    font-size: .8rem;
    color: #64748b;
}
.btn-new {
    background: #c9a84c;
    color: #0f2237;
    border: 0;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: .83rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background .15s, transform .12s;
    white-space: nowrap;
}
.btn-new:hover { background: #b8943d; transform: translateY(-1px); }

.tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.tpl-card {
    background: #fff;
    border: 1px solid #e8eef7;
    border-radius: 14px;
    padding: 0;
    overflow: hidden;
    transition: box-shadow .18s, border-color .18s, transform .15s;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
}
.tpl-card:hover {
    box-shadow: 0 10px 28px rgba(15, 34, 55, .1);
    border-color: #dce5f0;
    transform: translateY(-2px);
}
.tpl-card-top {
    height: 4px;
    background: linear-gradient(90deg, #1a3a5c, #c9a84c);
}
.tpl-card-inner { padding: 16px 16px 14px; }
.tpl-card-cat {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    margin-bottom: 8px;
}
.tpl-card-name {
    font-size: .95rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
    line-height: 1.3;
}
.tpl-card-sub {
    font-size: .8rem;
    color: #64748b;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.4em;
    margin-bottom: 12px;
}
.tpl-card-foot {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}
.tpl-usage {
    font-size: .68rem;
    color: #94a3b8;
    margin-left: auto;
}
.btn-tpl-action {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
    border: 0;
}
.btn-edit { background: #f1f5f9; color: #475569; }
.btn-edit:hover { background: #e2e8f0; }
.btn-use-tpl { background: #eff6ff; color: #1d4ed8; }
.btn-use-tpl:hover { background: #dbeafe; }
.btn-del { background: #fee2e2; color: #991b1b; }
.btn-del:hover { background: #fecaca; }
.tpl-default-badge {
    font-size: .6rem;
    padding: 2px 7px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
    margin-left: 6px;
    vertical-align: middle;
    font-weight: 700;
}
.tpl-modal .sf textarea { min-height: 180px; }
.tpl-ai-box {
    margin: 8px 0 14px;
    padding: 10px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    background: #f8fafc;
}
.tpl-ai-grid { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 8px; }
.tpl-ai-btn {
    background: #0f172a;
    color: #fff;
    border: 0;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
}
.tpl-ai-btn:hover { background: #1e293b; }
.tpl-editor-wrap { border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; background: #fff; }
.tpl-editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 8px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
}
.tpl-editor-toolbar button {
    border: 1px solid #cbd5e1;
    background: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: .75rem;
    cursor: pointer;
    color: #334155;
}
.tpl-editor-toolbar button:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8;
}
.tpl-editor {
    min-height: 190px;
    padding: 12px;
    font-size: .86rem;
    line-height: 1.6;
    color: #334155;
    outline: none;
    white-space: pre-wrap;
}

.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-box {
    background: #fff;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 20px 60px rgba(0,0,0,.18);
    max-width: 94vw;
    max-height: 90vh;
    overflow-y: auto;
}
.sf { display: flex; flex-direction: column; gap: 9px; }
.sf label { font-size: .76rem; font-weight: 600; color: #374151; margin-bottom: 2px; }
.sf input, .sf select, .sf textarea {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 7px 10px;
    font: inherit;
    font-size: .83rem;
    width: 100%;
}

@media (max-width: 900px) {
    .tpl-layout { grid-template-columns: 1fr; }
    .tpl-aside { position: static; }
    .tpl-cat-list {
        flex-direction: row;
        flex-wrap: wrap;
    }
    .tpl-cat-item {
        flex: 1 1 calc(50% - 6px);
        min-width: 140px;
    }
}
@media (max-width: 600px) {
    .tpl-hero { padding: 22px 20px; }
    .tpl-ai-grid { grid-template-columns: 1fr; }
}
</style>

<div class="tpl-page">
    <div class="tpl-hero">
        <div class="tpl-hero-badge">Messagerie</div>
        <h1>Banque de templates email</h1>
        <p>
            Filtrez par type de scénario à gauche, puis ouvrez ou modifiez une carte. Les mêmes modèles sont utilisables depuis la boîte de réception.
        </p>
        <div class="tpl-hero-meta">
            Placeholders&nbsp;:
            <code>{{contact_prenom}}</code>
            <code>{{conseiller_nom}}</code>
            <code>{{bien_titre}}</code>
            · <strong><?= count($templates) ?></strong> template<?= count($templates) > 1 ? 's' : '' ?>
        </div>
    </div>

    <div class="tpl-layout">
        <aside class="tpl-aside" aria-label="Filtrer par catégorie">
            <div class="tpl-aside-title">Parcourir par catégorie</div>
            <nav class="tpl-cat-list">
                <button type="button" class="tpl-cat-item active" data-cat="all" onclick="filterCat('all', this)">
                    <span class="tpl-cat-ico"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                    <span class="tpl-cat-body">
                        <span class="tpl-cat-label">Tous les templates</span>
                        <span class="tpl-cat-desc">Vue complète</span>
                    </span>
                    <span class="tpl-cat-count"><?= count($templates) ?></span>
                </button>
                <?php foreach ($categories as $key => $label):
                    $count = count($grouped[$key] ?? []);
                    $ico = $catIcons[$key] ?? ['icon' => 'fa-folder', 'color' => '#64748b'];
                ?>
                <button type="button" class="tpl-cat-item" data-cat="<?= htmlspecialchars($key) ?>" onclick="filterCat('<?= htmlspecialchars($key) ?>', this)">
                    <span class="tpl-cat-ico" style="color:<?= htmlspecialchars($ico['color']) ?>;">
                        <i class="fas <?= htmlspecialchars($ico['icon']) ?>" aria-hidden="true"></i>
                    </span>
                    <span class="tpl-cat-body">
                        <span class="tpl-cat-label"><?= htmlspecialchars($label) ?></span>
                        <span class="tpl-cat-desc"><?= $count ?> modèle<?= $count !== 1 ? 's' : '' ?></span>
                    </span>
                    <span class="tpl-cat-count"><?= $count ?></span>
                </button>
                <?php endforeach; ?>
            </nav>
        </aside>

        <div class="tpl-main">
            <div class="tpl-main-head">
                <div>
                    <h2 id="tplMainHeading">Tous les templates</h2>
                    <p id="tplMainSub">Cliquez sur une carte pour prévisualiser, modifier ou dupliquer.</p>
                </div>
                <button type="button" class="btn-new" onclick="openTplForm()">
                    <i class="fas fa-plus"></i> Nouveau template
                </button>
            </div>

            <div class="tpl-grid" id="tplGrid">
                <?php foreach ($grouped as $cat => $tpls): ?>
                    <?php foreach ($tpls as $tpl): ?>
                <div class="tpl-card" data-cat="<?= htmlspecialchars($tpl['category']) ?>">
                    <div class="tpl-card-top" style="background:linear-gradient(90deg, <?= htmlspecialchars(($catIcons[$tpl['category']] ?? $catIcons['general'])['color']) ?>, #1a3a5c);"></div>
                    <div class="tpl-card-inner">
                        <div class="tpl-card-cat"><?= htmlspecialchars($categories[$tpl['category']] ?? $tpl['category']) ?></div>
                        <div class="tpl-card-name">
                            <?= htmlspecialchars($tpl['name']) ?>
                            <?php if ($tpl['is_default']): ?><span class="tpl-default-badge">défaut</span><?php endif; ?>
                        </div>
                        <div class="tpl-card-sub"><?= htmlspecialchars($tpl['subject']) ?></div>
                        <div class="tpl-card-foot">
                            <button type="button" class="btn-tpl-action btn-use-tpl" onclick="previewTemplate(<?= (int)$tpl['id'] ?>)">
                                <i class="fas fa-eye"></i> Aperçu
                            </button>
                            <?php if (!$tpl['is_default']): ?>
                            <button type="button" class="btn-tpl-action btn-edit" onclick='openTplForm(<?= htmlspecialchars(json_encode($tpl), ENT_QUOTES) ?>)'>
                                <i class="fas fa-pen"></i> Modifier
                            </button>
                            <button type="button" class="btn-tpl-action btn-del" onclick="deleteTpl(<?= (int)$tpl['id'] ?>, this)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn-tpl-action btn-edit" onclick='duplicateTpl(<?= htmlspecialchars(json_encode($tpl), ENT_QUOTES) ?>)'>
                                <i class="fas fa-copy"></i> Dupliquer
                            </button>
                            <?php endif; ?>
                            <span class="tpl-usage"><?= (int)$tpl['usage_count'] ?> util.</span>
                        </div>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL FORM ══ -->
<div class="modal-overlay" id="tplFormModal">
    <div class="modal-box tpl-modal" style="width:560px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 id="tplFormTitle" style="margin:0;font-size:.95rem;font-weight:700;">Nouveau template</h3>
            <button type="button" onclick="closeModal('tplFormModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <input type="hidden" id="tplId" value="">
        <div class="sf">
            <div class="tpl-ai-box">
                <div style="font-size:.76rem;font-weight:700;color:#334155;margin-bottom:8px;"><i class="fas fa-wand-magic-sparkles" style="color:#2563eb;"></i> Générer avec IA</div>
                <div class="tpl-ai-grid">
                    <input type="text" id="tplAiGoal" placeholder="Objectif (ex: relance après visite)">
                    <select id="tplAiTone">
                        <option value="professionnel">Ton professionnel</option>
                        <option value="amical">Ton amical</option>
                        <option value="urgent">Ton urgent</option>
                        <option value="premium">Ton premium</option>
                    </select>
                    <button type="button" class="tpl-ai-btn" onclick="generateTplWithAI()"><i class="fas fa-sparkles"></i> Générer</button>
                </div>
                <input type="text" id="tplAiContext" placeholder="Contexte optionnel (type de bien, situation client...)" style="margin-top:8px;">
                <div id="tplAiFeedback" style="font-size:.72rem;color:#64748b;margin-top:6px;"></div>
            </div>
            <div>
                <label>Nom du template *</label>
                <input type="text" id="tplName" placeholder="Ex: Relance après visite">
            </div>
            <div>
                <label>Catégorie</label>
                <select id="tplCategory">
                    <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Objet de l'email</label>
                <input type="text" id="tplSubject" placeholder="Ex: Suite à votre visite — {{bien_titre}}">
            </div>
            <div>
                <label>Corps du message <small style="font-weight:400;color:#94a3b8;">Placeholders : {{contact_prenom}} {{conseiller_nom}} {{bien_titre}} etc.</small></label>
                <div class="tpl-editor-wrap">
                    <div class="tpl-editor-toolbar">
                        <button type="button" onclick="editorCmd('bold')"><b>B</b></button>
                        <button type="button" onclick="editorCmd('italic')"><i>I</i></button>
                        <button type="button" onclick="editorCmd('insertUnorderedList')">• Liste</button>
                        <button type="button" onclick="editorCmd('insertParagraph')">¶ Paragraphe</button>
                        <button type="button" onclick="insertPlaceholder('{{contact_prenom}}')">{{contact_prenom}}</button>
                        <button type="button" onclick="insertPlaceholder('{{conseiller_nom}}')">{{conseiller_nom}}</button>
                        <button type="button" onclick="insertPlaceholder('{{bien_titre}}')">{{bien_titre}}</button>
                    </div>
                    <div id="tplBodyEditor" class="tpl-editor" contenteditable="true"></div>
                </div>
                <textarea id="tplBody" style="display:none;" placeholder="Bonjour {{contact_prenom}},&#10;&#10;...&#10;&#10;Cordialement,&#10;{{conseiller_nom}}"></textarea>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px;">
            <span id="tplFormFeedback" style="font-size:.76rem;color:#64748b;align-self:center;flex:1;"></span>
            <button type="button" onclick="closeModal('tplFormModal')" style="border:1px solid #d1d5db;background:#fff;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:.82rem;">Annuler</button>
            <button type="button" onclick="saveTpl()" style="background:#2563eb;color:#fff;border:0;padding:7px 16px;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:700;">Enregistrer</button>
        </div>
    </div>
</div>

<!-- ══ MODAL APERÇU ══ -->
<div class="modal-overlay" id="tplPreviewModal">
    <div class="modal-box" style="width:580px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 id="previewTitle" style="margin:0;font-size:.95rem;font-weight:700;"></h3>
            <button type="button" onclick="closeModal('tplPreviewModal')" style="border:0;background:none;font-size:1.1rem;cursor:pointer;color:#6b7280;">✕</button>
        </div>
        <div style="background:#f8fafc;border-radius:9px;padding:14px;margin-bottom:10px;">
            <div style="font-size:.75rem;color:#94a3b8;margin-bottom:4px;">OBJET</div>
            <div id="previewSubject" style="font-size:.88rem;font-weight:600;color:#0f172a;"></div>
        </div>
        <div style="border:1px solid #e5e7eb;border-radius:9px;padding:14px;min-height:120px;font-size:.85rem;line-height:1.65;color:#334155;" id="previewBody"></div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px;">
            <button type="button" onclick="closeModal('tplPreviewModal')" style="border:1px solid #d1d5db;background:#fff;padding:7px 14px;border-radius:8px;cursor:pointer;font-size:.82rem;">Fermer</button>
        </div>
    </div>
</div>

<script>
const allTpls = <?= json_encode($templates, JSON_UNESCAPED_UNICODE) ?>;
const catLabels = <?= json_encode($categories, JSON_UNESCAPED_UNICODE) ?>;

function filterCat(cat, btn) {
    document.querySelectorAll('.tpl-cat-item').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tpl-card').forEach(c => {
        c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none';
    });
    const h = document.getElementById('tplMainHeading');
    const s = document.getElementById('tplMainSub');
    if (cat === 'all') {
        h.textContent = 'Tous les templates';
        s.textContent = 'Cliquez sur une carte pour prévisualiser, modifier ou dupliquer.';
    } else {
        h.textContent = catLabels[cat] || cat;
        s.textContent = 'Modèles pour ce type de message.';
    }
}

function openTplForm(tpl = null) {
    const htmlBody = tpl ? (tpl.body_html || '') : '';
    document.getElementById('tplId').value      = tpl ? tpl.id : '';
    document.getElementById('tplName').value    = tpl ? tpl.name : '';
    document.getElementById('tplSubject').value = tpl ? tpl.subject : '';
    document.getElementById('tplCategory').value = tpl ? tpl.category : 'general';
    document.getElementById('tplFormTitle').textContent = tpl ? 'Modifier le template' : 'Nouveau template';
    document.getElementById('tplFormFeedback').textContent = '';
    document.getElementById('tplAiFeedback').textContent = '';
    document.getElementById('tplAiGoal').value = '';
    document.getElementById('tplAiContext').value = '';
    setEditorHtml(htmlBody || '<p>Bonjour {{contact_prenom}},</p><p></p><p>Cordialement,<br>{{conseiller_nom}}</p>');
    openModal('tplFormModal');
    setTimeout(() => document.getElementById('tplName').focus(), 100);
}

function duplicateTpl(tpl) {
    openTplForm({...tpl, id: 0, name: 'Copie — ' + tpl.name, is_default: 0});
}

async function saveTpl() {
    const name = document.getElementById('tplName').value.trim();
    const fb   = document.getElementById('tplFormFeedback');
    if (!name) { fb.textContent = 'Nom obligatoire.'; return; }
    syncEditorToTextarea();
    const htmlBody = document.getElementById('tplBody').value.trim();
    const fd = new FormData();
    fd.append('id',       document.getElementById('tplId').value);
    fd.append('name',     name);
    fd.append('category', document.getElementById('tplCategory').value);
    fd.append('subject',  document.getElementById('tplSubject').value.trim());
    fd.append('body_html', htmlBody);
    fb.textContent = 'Enregistrement...';
    const d = await (await fetch('/admin?module=messagerie&action=template_save', {method:'POST',body:fd})).json();
    if (d.ok) { closeModal('tplFormModal'); location.reload(); }
    else { fb.textContent = d.error||'Erreur.'; }
}

async function deleteTpl(id, btn) {
    if (!confirm('Supprimer ce template ?')) return;
    const fd = new FormData(); fd.append('id', id);
    const d = await (await fetch('/admin?module=messagerie&action=template_delete', {method:'POST',body:fd})).json();
    if (d.ok) { btn.closest('.tpl-card').remove(); }
}

function previewTemplate(id) {
    const tpl = allTpls.find(t => t.id == id);
    if (!tpl) return;
    document.getElementById('previewTitle').textContent   = tpl.name;
    document.getElementById('previewSubject').textContent = tpl.subject;
    document.getElementById('previewBody').innerHTML      = tpl.body_html;
    openModal('tplPreviewModal');
}

function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function editorCmd(cmd) {
    document.execCommand(cmd, false, null);
    syncEditorToTextarea();
}

function insertPlaceholder(text) {
    document.execCommand('insertText', false, text);
    syncEditorToTextarea();
}

function normalizeEditorHtml(html) {
    const div = document.createElement('div');
    div.innerHTML = (html || '').trim();
    if (!div.innerHTML) return '';
    div.querySelectorAll('script,style').forEach(node => node.remove());
    return div.innerHTML;
}

function setEditorHtml(html) {
    const clean = normalizeEditorHtml(html);
    document.getElementById('tplBodyEditor').innerHTML = clean;
    syncEditorToTextarea();
}

function syncEditorToTextarea() {
    const html = normalizeEditorHtml(document.getElementById('tplBodyEditor').innerHTML);
    document.getElementById('tplBody').value = html;
}

document.getElementById('tplBodyEditor').addEventListener('input', syncEditorToTextarea);

async function generateTplWithAI() {
    const feedback = document.getElementById('tplAiFeedback');
    const goal = document.getElementById('tplAiGoal').value.trim();
    if (!goal) {
        feedback.textContent = "Ajoutez un objectif pour lancer la génération IA.";
        return;
    }

    feedback.textContent = 'Génération en cours...';
    const fd = new FormData();
    fd.append('goal', goal);
    fd.append('tone', document.getElementById('tplAiTone').value);
    fd.append('context', document.getElementById('tplAiContext').value.trim());
    fd.append('category', document.getElementById('tplCategory').value);

    try {
        const res = await fetch('/admin?module=messagerie&action=ai_template', { method:'POST', body:fd });
        const d = await res.json();
        if (!d.ok) {
            feedback.textContent = d.error || "Impossible de générer le template.";
            return;
        }
        if (!document.getElementById('tplName').value.trim()) {
            document.getElementById('tplName').value = d.name || '';
        }
        document.getElementById('tplSubject').value = d.subject || '';
        setEditorHtml(d.body_html || '');
        feedback.textContent = 'Template généré. Vous pouvez le modifier manuellement avant enregistrement.';
    } catch (e) {
        feedback.textContent = "Erreur réseau pendant la génération IA.";
    }
}
</script>
