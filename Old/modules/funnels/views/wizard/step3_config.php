<?php // modules/funnels/views/wizard/step3_config.php
$canal      = $_GET['canal'] ?? '';
$templateId = $_GET['template'] ?? '';
$canalInfo  = $canaux[$canal] ?? ['label' => $canal, 'color' => '#666'];
$tplInfo    = $templates[$canal][$templateId] ?? ['label' => $templateId];
$isGoogleAds = $canal === 'google_ads';
$isSeo      = $canal === 'seo';
?>
<style>
/* ── Wizard layout ───────────────────────────────────────────── */
.wiz-wrap        { max-width:820px }
.wiz-progress    { margin-bottom:1.5rem }
.wiz-steps       { display:flex;gap:0;margin-top:.5rem }
.wiz-step        { flex:1;text-align:center;font-size:.72rem;font-weight:600;color:#94a3b8;padding-top:.4rem;border-top:3px solid #e2e8f0;transition:color .2s,border-color .2s }
.wiz-step.done   { color:#10b981;border-color:#10b981 }
.wiz-step.active { color:#1e3a5f;border-color:#1e3a5f }
.wiz-bar         { height:4px;background:#e2e8f0;border-radius:999px;overflow:hidden }
.wiz-bar-fill    { height:100%;background:linear-gradient(90deg,#1e3a5f,#3b82f6);border-radius:999px;transition:width .4s }

/* ── Cards ───────────────────────────────────────────────────── */
.wiz-card        { background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06));margin-bottom:1rem;overflow:hidden }
.wiz-card-head   { display:flex;align-items:center;gap:.5rem;padding:.85rem 1.1rem;border-bottom:1px solid #f1f5f9;font-size:.88rem;font-weight:700;color:#0f172a }
.wiz-card-head i { color:#3b82f6 }
.wiz-card-head .wiz-badge { margin-left:auto;font-size:.7rem;font-weight:700;background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:999px }
.wiz-card-body   { padding:1.1rem }

/* ── Form grid ───────────────────────────────────────────────── */
.wiz-grid-2  { display:grid;grid-template-columns:1fr 1fr;gap:.75rem }
.wiz-grid-3  { display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem }
.wiz-grid-4  { display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:.75rem }
.wiz-field   { display:flex;flex-direction:column;gap:.3rem }
.wiz-field label { font-size:.74rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em }
.wiz-field label .req { color:#ef4444 }
.wiz-field label .tip { font-weight:400;text-transform:none;letter-spacing:0;font-size:.72rem;color:#94a3b8;margin-left:.3rem }
.wiz-field input,
.wiz-field select,
.wiz-field textarea {
    border:1px solid #cbd5e1;border-radius:8px;padding:.5rem .7rem;font-size:.88rem;
    color:#0f172a;background:#fff;width:100%;font-family:inherit;transition:border-color .15s,box-shadow .15s
}
.wiz-field input:focus,
.wiz-field select:focus,
.wiz-field textarea:focus { outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12) }
.wiz-field textarea { resize:vertical;min-height:64px }
.wiz-field .hint     { font-size:.74rem;color:#94a3b8;margin-top:.1rem }
.wiz-field .counter  { font-size:.72rem;color:#94a3b8;text-align:right;margin-top:.1rem }

/* ── Radio cards (thank you type) ────────────────────────────── */
.wiz-radio-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-top:.4rem }
.wiz-radio-card { position:relative }
.wiz-radio-card input[type=radio] { position:absolute;opacity:0;width:0;height:0 }
.wiz-radio-card label {
    display:flex;flex-direction:column;align-items:center;gap:.3rem;padding:.75rem .5rem;
    border:2px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.78rem;font-weight:600;
    color:#475569;text-align:center;transition:border-color .15s,background .15s
}
.wiz-radio-card label .wiz-radio-icon { font-size:1.4rem;line-height:1 }
.wiz-radio-card input[type=radio]:checked + label { border-color:#1e3a5f;background:#eff6ff;color:#1e3a5f }

/* ── Alert inline ────────────────────────────────────────────── */
.wiz-alert-warning {
    display:none;background:#fefce8;border:1px solid #fde047;border-radius:8px;
    padding:.5rem .8rem;font-size:.8rem;color:#854d0e;margin-top:.4rem
}
.wiz-alert-warning.show { display:flex;align-items:center;gap:.4rem }

/* ── Banner info ─────────────────────────────────────────────── */
.wiz-info-banner {
    background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;
    padding:.7rem .9rem;font-size:.8rem;color:#1e40af;
    display:flex;align-items:flex-start;gap:.5rem;margin-bottom:.75rem
}
.wiz-info-banner i { margin-top:2px;flex-shrink:0 }

/* ── Google Ads card accent ──────────────────────────────────── */
.wiz-card--ads .wiz-card-head { background:#fffbeb }
.wiz-card--ads .wiz-card-head i { color:#d97706 }

/* ── Divider ─────────────────────────────────────────────────── */
.wiz-divider { height:1px;background:#f1f5f9;margin:.75rem 0 }

/* ── Actions ─────────────────────────────────────────────────── */
.wiz-actions { display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem }

/* ── Back link ───────────────────────────────────────────────── */
.wiz-back { display:inline-flex;align-items:center;gap:.4rem;font-size:.83rem;color:#64748b;text-decoration:none;margin-bottom:.9rem }
.wiz-back:hover { color:#0f172a }

@media(max-width:640px) {
    .wiz-grid-2,.wiz-grid-3,.wiz-grid-4 { grid-template-columns:1fr }
    .wiz-radio-grid { grid-template-columns:1fr 1fr }
}
</style>

<div class="hub-page wiz-wrap">

    <!-- Back -->
    <a href="?module=funnels&action=wizard&step=2&canal=<?= htmlspecialchars($canal) ?>" class="wiz-back">
        <i class="fas fa-arrow-left"></i> Retour au choix du template
    </a>

    <!-- Progress -->
    <div class="wiz-progress">
        <div class="wiz-bar"><div class="wiz-bar-fill" style="width:75%"></div></div>
        <div class="wiz-steps">
            <div class="wiz-step done">1. Canal</div>
            <div class="wiz-step done">2. Template</div>
            <div class="wiz-step active">3. Configuration</div>
            <div class="wiz-step">4. Publication</div>
        </div>
    </div>

    <!-- Hero -->
    <header class="hub-hero" style="margin-bottom:1.25rem">
        <div class="hub-hero-badge" style="background:<?= htmlspecialchars($canalInfo['color'] ?? '#3b82f6') ?>1a;color:<?= htmlspecialchars($canalInfo['color'] ?? '#3b82f6') ?>">
            <i class="fas fa-sliders"></i> Configuration
        </div>
        <h1 style="font-size:1.5rem">Configurez votre landing page</h1>
        <p>
            Template <strong><?= htmlspecialchars($tplInfo['label']) ?></strong> —
            Canal <strong style="color:<?= htmlspecialchars($canalInfo['color'] ?? '#3b82f6') ?>"><?= htmlspecialchars($canalInfo['label']) ?></strong>
        </p>
    </header>

    <form id="funnel-config-form">
        <input type="hidden" name="action" value="create">
        <input type="hidden" name="canal" value="<?= htmlspecialchars($canal) ?>">
        <input type="hidden" name="template_id" value="<?= htmlspecialchars($templateId) ?>">
        <?= csrfField() ?>

        <!-- ── Bloc 1 : Ciblage ─────────────────────────────── -->
        <div class="wiz-card">
            <div class="wiz-card-head">
                <i class="fas fa-map-marker-alt"></i> Ciblage géographique & persona
            </div>
            <div class="wiz-card-body">
                <div class="wiz-grid-3">
                    <div class="wiz-field">
                        <label>Ville <span class="req">*</span></label>
                        <input type="text" name="ville" placeholder="ex: Aix-en-Provence" required>
                    </div>
                    <div class="wiz-field">
                        <label>Quartier <span class="tip">optionnel</span></label>
                        <input type="text" name="quartier" placeholder="ex: Mazarin">
                    </div>
                    <div class="wiz-field">
                        <label>
                            Mot-clé principal
                            <?php if ($isGoogleAds): ?>
                            <span class="tip" title="Doit figurer dans le H1 pour le Quality Score">⚡ Quality Score</span>
                            <?php endif; ?>
                            <?= $isGoogleAds ? '<span class="req">*</span>' : '' ?>
                        </label>
                        <input type="text" name="keyword" id="keyword"
                               placeholder="ex: vendre maison" <?= $isGoogleAds ? 'required' : '' ?>>
                    </div>
                </div>
                <div class="wiz-divider"></div>
                <div class="wiz-grid-2">
                    <div class="wiz-field">
                        <label>Persona cible</label>
                        <select name="persona">
                            <option value="vendeur">🏠 Vendeur</option>
                            <option value="acheteur">🔑 Acheteur</option>
                            <option value="investisseur">📈 Investisseur</option>
                            <option value="primo_accedant">🌱 Primo-accédant</option>
                            <option value="senior">👤 Senior</option>
                        </select>
                    </div>
                    <div class="wiz-field">
                        <label>Niveau de conscience</label>
                        <select name="awareness_level">
                            <option value="problem_aware">Problem Aware — connaît le problème</option>
                            <option value="solution_aware">Solution Aware — cherche une solution</option>
                            <option value="product_aware">Product Aware — compare des offres</option>
                            <option value="most_aware">Most Aware — prêt à agir</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Bloc 2 : Contenu LP ──────────────────────────── -->
        <div class="wiz-card">
            <div class="wiz-card-head">
                <i class="fas fa-pen-nib"></i> Contenu de la landing page
            </div>
            <div class="wiz-card-body">
                <div class="wiz-field" style="margin-bottom:.75rem">
                    <label>Nom interne (admin) <span class="req">*</span></label>
                    <input type="text" name="name"
                           placeholder="ex: Campagne Vente Aix — Google Ads — Mai 2025" required>
                    <div class="hint">Visible uniquement dans l'admin — soyez précis pour retrouver facilement</div>
                </div>
                <div class="wiz-field" style="margin-bottom:.4rem">
                    <label>H1 — Titre principal <span class="req">*</span></label>
                    <input type="text" name="h1" id="h1"
                           placeholder="ex: Vendez votre bien à Aix-en-Provence au meilleur prix"
                           maxlength="120" required>
                    <div class="counter" id="h1-count">0 / 120</div>
                    <?php if ($isGoogleAds): ?>
                    <div class="wiz-alert-warning" id="kw-warning">
                        <i class="fas fa-triangle-exclamation"></i>
                        Le mot-clé doit apparaître dans le H1 pour un bon Quality Score Google Ads.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="wiz-grid-2">
                    <div class="wiz-field">
                        <label>Promesse / Sous-titre</label>
                        <input type="text" name="promise"
                               placeholder="ex: Estimation gratuite en 48h, sans engagement">
                    </div>
                    <div class="wiz-field">
                        <label>Label du bouton CTA</label>
                        <input type="text" name="cta_label"
                               value="Recevoir mon estimation gratuite"
                               placeholder="ex: Télécharger le guide gratuit">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Bloc 3 : SEO ─────────────────────────────────── -->
        <div class="wiz-card">
            <div class="wiz-card-head">
                <i class="fas fa-magnifying-glass"></i> SEO
                <span class="wiz-badge">Auto-généré si vide</span>
            </div>
            <div class="wiz-card-body">
                <div class="wiz-grid-2">
                    <div class="wiz-field">
                        <label>SEO Title</label>
                        <input type="text" name="seo_title" id="seo-title"
                               placeholder="Laissez vide pour auto-génération" maxlength="70">
                        <div class="counter" id="title-count">0 / 70</div>
                    </div>
                    <div class="wiz-field">
                        <label>Meta Description</label>
                        <textarea name="meta_description" id="meta-desc" rows="2"
                                  placeholder="Laissez vide pour auto-génération" maxlength="160"></textarea>
                        <div class="counter" id="meta-count">0 / 160</div>
                    </div>
                </div>
                <?php if ($isSeo): ?>
                <div class="wiz-divider"></div>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.88rem;color:#475569;font-weight:600">
                    <input type="checkbox" name="indexable" value="1" id="indexable" style="width:auto">
                    Page indexable par Google — laissez décoché pour les LP Ads (noindex)
                </label>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Bloc 4 : Google Ads (conditionnel) ──────────── -->
        <?php if ($isGoogleAds): ?>
        <div class="wiz-card wiz-card--ads">
            <div class="wiz-card-head">
                <i class="fab fa-google"></i> Paramètres Google Ads
                <span class="wiz-badge" style="background:#fef3c7;color:#92400e">Ads</span>
            </div>
            <div class="wiz-card-body">
                <div class="wiz-grid-2" style="margin-bottom:.75rem">
                    <div class="wiz-field">
                        <label>Nom de la campagne</label>
                        <input type="text" name="campaign_name" placeholder="ex: Vente Aix 2025">
                    </div>
                    <div class="wiz-field">
                        <label>Ad Group</label>
                        <input type="text" name="ad_group" placeholder="ex: Aix-Mazarin Maison">
                    </div>
                </div>
                <div class="wiz-grid-4">
                    <div class="wiz-field">
                        <label>utm_source</label>
                        <input type="text" name="utm_source" value="google">
                    </div>
                    <div class="wiz-field">
                        <label>utm_medium</label>
                        <input type="text" name="utm_medium" value="cpc">
                    </div>
                    <div class="wiz-field" style="grid-column:span 2">
                        <label>utm_campaign</label>
                        <input type="text" name="utm_campaign" placeholder="ex: vente-aix-2025">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Bloc 5 : Ressource & Séquence ────────────────── -->
        <div class="wiz-card">
            <div class="wiz-card-head">
                <i class="fas fa-link"></i> Automatisation post-soumission
            </div>
            <div class="wiz-card-body">
                <div class="wiz-info-banner">
                    <i class="fas fa-circle-info"></i>
                    <span>Ces éléments se déclenchent <strong>automatiquement</strong> quand un prospect soumet le formulaire.</span>
                </div>
                <div class="wiz-grid-2" style="margin-bottom:.75rem">
                    <div class="wiz-field">
                        <label>Ressource PDF associée</label>
                        <select name="ressource_id" id="sel-ressource">
                            <option value="">— Aucune ressource —</option>
                        </select>
                        <div class="hint">Guide envoyé automatiquement par email après soumission</div>
                    </div>
                    <div class="wiz-field">
                        <label>Séquence email automatique</label>
                        <select name="sequence_id" id="sel-sequence">
                            <option value="">— Aucune séquence —</option>
                        </select>
                        <div class="hint">Relances automatiques J0 / J+2 / J+4</div>
                    </div>
                </div>

                <div class="wiz-divider"></div>

                <div class="wiz-field">
                    <label>Type de page de confirmation (Thank You)</label>
                    <div class="wiz-radio-grid">
                        <?php foreach ([
                            'telechargement'   => ['📥', 'Téléchargement'],
                            'estimation_recue' => ['🏠', 'Estimation reçue'],
                            'rdv_confirme'     => ['📅', 'RDV confirmé'],
                            'contact_recu'     => ['✉️', 'Contact reçu'],
                        ] as $val => [$icon, $label]): ?>
                        <div class="wiz-radio-card">
                            <input type="radio" name="thankyou_type" id="ty_<?= $val ?>"
                                   value="<?= $val ?>" <?= $val === 'telechargement' ? 'checked' : '' ?>>
                            <label for="ty_<?= $val ?>">
                                <span class="wiz-radio-icon"><?= $icon ?></span>
                                <?= htmlspecialchars($label) ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Actions ──────────────────────────────────────── -->
        <div class="wiz-actions">
            <button type="button" class="hub-btn" onclick="submitFunnel('draft')"
                    style="background:#f1f5f9;color:#334155">
                <i class="fas fa-save"></i> Sauver brouillon
            </button>
            <button type="button" class="hub-btn hub-btn--gold" onclick="submitFunnel('published')">
                Continuer <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </form>
</div>

<script>
// ── Compteurs de caractères ───────────────────────────────────
function bindCounter(inputId, counterId, max) {
    const el = document.getElementById(inputId);
    const ct = document.getElementById(counterId);
    if (!el || !ct) return;
    const update = () => {
        const n = el.value.length;
        ct.textContent = n + ' / ' + max;
        ct.style.color = n > max * 0.9 ? '#f59e0b' : '#94a3b8';
    };
    el.addEventListener('input', update);
    update();
}
bindCounter('h1', 'h1-count', 120);
bindCounter('seo-title', 'title-count', 70);
bindCounter('meta-desc', 'meta-count', 160);

<?php if ($isGoogleAds): ?>
// ── Quality Score check ───────────────────────────────────────
function checkQS() {
    const kw   = (document.getElementById('keyword')?.value || '').toLowerCase().trim();
    const h1   = (document.getElementById('h1')?.value || '').toLowerCase().trim();
    const warn = document.getElementById('kw-warning');
    if (!warn) return;
    warn.classList.toggle('show', kw.length > 0 && h1.length > 0 && !h1.includes(kw));
}
document.getElementById('keyword')?.addEventListener('input', checkQS);
document.getElementById('h1')?.addEventListener('input', checkQS);
<?php endif; ?>

// ── Charger ressources & séquences via API ────────────────────
(function loadSelectOptions() {
    fetch('/admin/api/funnels/options.php')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            const selR = document.getElementById('sel-ressource');
            const selS = document.getElementById('sel-sequence');
            (data.ressources || []).forEach(r => {
                const o = new Option(r.title || r.name, r.id);
                selR.add(o);
            });
            (data.sequences || []).forEach(s => {
                const o = new Option(s.name, s.id);
                selS.add(o);
            });
        })
        .catch(() => {}); // silently ignore if endpoint missing
})();

// ── Soumission ────────────────────────────────────────────────
function submitFunnel(status) {
    const form = document.getElementById('funnel-config-form');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    const data = {};
    new FormData(form).forEach((v, k) => { data[k] = v; });
    data.status = status;

    const btn = event.currentTarget;
    btn.disabled = true;
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement…';

    fetch('/admin/api/funnels/ajax.php', {
        method:  'POST',
        headers: {'Content-Type': 'application/json'},
        body:    JSON.stringify(data),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success || d.ok) {
            window.location.href = '?module=funnels&action=wizard&step=4&id=' + (d.id || d.funnel_id);
        } else {
            alert((d.errors || [d.error || 'Erreur inconnue']).join('\n'));
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    })
    .catch(() => {
        alert('Erreur réseau — vérifiez la console.');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    });
}
</script>
