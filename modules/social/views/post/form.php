<?php
/**
 * @var array<string, mixed>|null $post
 * @var array<int, array<string, mixed>> $sequences
 */
$post = $post ?? null;
$sequences = $sequences ?? [];

$__q = static function (array $base): string {
    return function_exists('admin_url') ? admin_url($base) : ('/admin/?' . http_build_query($base, '', '&', PHP_QUERY_RFC3986));
};

$cancelUrl = $__q(['module' => 'social', 'action' => 'sequences-list']);
if ($post !== null) {
    $cancelUrl = $__q(['module' => 'social', 'action' => 'post', 'id' => (int) ($post['id'] ?? 0)]);
}

$niveauVal = (string) ($post['niveau'] ?? '');
$status = (string) ($post['statut'] ?? 'brouillon');
$networks = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: ['facebook'];
$imgFmt = (string) ($post['image_format'] ?? 'feed');
if (! in_array($imgFmt, ['feed', 'story'], true)) {
    $imgFmt = 'feed';
}
$imageSvgStored = social_sanitize_svg((string) ($post['image_svg'] ?? ''));
$seqPref = (int) ($post['sequence_id'] ?? $_GET['sequence_id'] ?? 0);
?>

<style>
.post-form-v2 { max-width:920px; margin:0 auto 48px; padding-bottom:12px; }
.post-form-v2-hero {
    background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);
    border-radius:16px;
    padding:24px 26px;
    color:#fff;
    margin-bottom:18px;
    box-shadow:0 4px 20px rgba(15,34,55,.15);
}
.post-form-v2-hero-badge { font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#c9a84c; margin-bottom:8px; display:flex; align-items:center; gap:8px; }
.post-form-v2-hero h1 { margin:0; font-size:clamp(1.2rem,2.5vw,1.65rem); font-weight:700; color:#fff; font-family:inherit; }

.post-form-v2-grid {
    display:grid;
    gap:16px;
}
.post-form-v2-panel {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:20px 22px;
}
.post-form-v2-panel h2 {
    margin:0 0 14px;
    font-size:.72rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:#94a3b8;
}
.post-form-v2 label.lbl-block {
    display:grid;
    gap:6px;
    font-size:.82rem;
    font-weight:600;
    color:#334155;
}
.post-form-v2 input[type=text],
.post-form-v2 textarea,
.post-form-v2 select {
    border:1px solid #e2e8f0;
    border-radius:10px;
    padding:11px 14px;
    font:inherit;
    font-size:.95rem;
    color:#0f172a;
    background:#fff;
}
.post-form-v2 textarea { min-height:160px; resize:vertical; line-height:1.55; }
.post-form-v2 input:focus,
.post-form-v2 textarea:focus,
.post-form-v2 select:focus {
    outline:none;
    border-color:#1a3c5e;
    box-shadow:0 0 0 3px rgba(26,60,94,.12);
}
.post-form-v2 fieldset.net-fieldset {
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px 16px;
    margin:0;
}
.post-form-v2 fieldset.net-fieldset legend {
    font-size:.72rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#64748b;
    padding:0 8px;
}
.post-form-v2 .net-checks {
    display:flex;
    flex-wrap:wrap;
    gap:12px 18px;
}
.post-form-v2 .net-checks label {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-weight:500;
    font-size:.88rem;
    cursor:pointer;
    color:#334155;
}

.post-form-v2-studio {
    display:grid;
    gap:14px;
}
.post-form-v2-format {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}
.post-form-v2-format label.fmt-opt {
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:12px 16px;
    border-radius:12px;
    border:2px solid #e5e7eb;
    cursor:pointer;
    font-weight:600;
    font-size:.85rem;
    color:#475569;
    background:#fafafa;
    transition:border-color .15s, background .15s;
}
.post-form-v2-format label.fmt-opt:has(input:checked) {
    border-color:#c9a84c;
    background:#fffbeb;
    color:#92400e;
}
.post-form-v2-format input { accent-color:#c9a84c; }

.post-form-v2-svg-preview-wrap {
    border-radius:12px;
    overflow:auto;
    border:1px dashed #cbd5e1;
    background:#f8fafc;
    min-height:180px;
    max-height:min(55vh,520px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:12px;
}
.post-form-v2-svg-preview-wrap svg { max-width:100%; height:auto; display:block; }
.post-form-v2-svg-placeholder {
    color:#94a3b8;
    font-size:.9rem;
    text-align:center;
    padding:24px;
}
.post-form-v2-studio-actions {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
}

.post-form-v2-actions {
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    align-items:center;
    margin-top:8px;
}
.post-form-v2 .pf-btn {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:.85rem;
    font-weight:600;
    padding:11px 18px;
    border-radius:10px;
    border:1px solid #cbd5e1;
    background:#fff;
    color:#1a3c5e;
    cursor:pointer;
    text-decoration:none;
    font-family:inherit;
}
.post-form-v2 .pf-btn:hover { border-color:#c9a84c; color:#b45309; }
.post-form-v2 .pf-btn--gold {
    background:#c9a84c;
    border-color:#c9a84c;
    color:#10253c;
    font-weight:700;
}
.post-form-v2 .pf-btn--gold:hover { background:#b8962d; color:#10253c; }
</style>

<section class="post-form-v2">
    <header class="post-form-v2-hero">
        <div class="post-form-v2-hero-badge"><i class="fas fa-pen-to-square"></i> <?= $post ? 'Édition' : 'Création' ?></div>
        <h1><?= $post ? 'Modifier la publication' : 'Nouvelle publication' ?></h1>
    </header>

    <form method="post" action="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'save-post']), ENT_QUOTES, 'UTF-8') ?>" class="post-form-v2-grid" id="social-post-form">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) ($post['id'] ?? 0) ?>">
        <input type="hidden" name="ordre_sequence" value="<?= $post ? (int) ($post['ordre_sequence'] ?? 0) : 0 ?>">
        <input type="hidden" name="image_svg" id="social-image-svg-field" value="<?= htmlspecialchars($imageSvgStored, ENT_QUOTES, 'UTF-8') ?>">

        <div class="post-form-v2-panel">
            <h2>Contenu</h2>
            <div style="display:grid;gap:14px;">
                <label class="lbl-block">Titre interne
                    <input type="text" name="titre" required maxlength="300"
                           value="<?= htmlspecialchars((string) ($post['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Repère pour cette publication">
                </label>

                <label class="lbl-block">Texte du post
                    <textarea name="contenu" required placeholder="Texte qui sera publié ou adapté sur les réseaux"><?= htmlspecialchars((string) ($post['contenu'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label class="lbl-block">Séquence
                    <select name="sequence_id">
                        <option value="0">Aucune séquence</option>
                        <?php foreach ($sequences as $sequence): ?>
                            <option value="<?= (int) $sequence['id'] ?>"
                                <?= $seqPref === (int) $sequence['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $sequence['nom'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="lbl-block">Niveau de conscience (parcours N1→N5)
                    <select name="niveau">
                        <option value="">— Non défini —</option>
                        <option value="n1" <?= $niveauVal === 'n1' ? 'selected' : '' ?>>N1 — Prise de conscience</option>
                        <option value="n2" <?= $niveauVal === 'n2' ? 'selected' : '' ?>>N2 — Problème identifié</option>
                        <option value="n3" <?= $niveauVal === 'n3' ? 'selected' : '' ?>>N3 — Solutions envisagées</option>
                        <option value="n4" <?= $niveauVal === 'n4' ? 'selected' : '' ?>>N4 — Comparaison / décision</option>
                        <option value="n5" <?= $niveauVal === 'n5' ? 'selected' : '' ?>>N5 — Passage à l’action</option>
                    </select>
                </label>

                <label class="lbl-block">Planifier le
                    <input type="datetime-local" name="planifie_at"
                           value="<?= htmlspecialchars(isset($post['planifie_at']) && $post['planifie_at'] ? date('Y-m-d\TH:i', strtotime((string) $post['planifie_at'])) : '', ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <fieldset class="net-fieldset">
                    <legend>Réseaux</legend>
                    <div class="net-checks">
                        <label><input type="checkbox" name="reseaux[]" value="facebook"  <?= in_array('facebook', $networks, true) ? 'checked' : '' ?>> Facebook</label>
                        <label><input type="checkbox" name="reseaux[]" value="instagram" <?= in_array('instagram', $networks, true) ? 'checked' : '' ?>> Instagram</label>
                        <label><input type="checkbox" name="reseaux[]" value="linkedin"  <?= in_array('linkedin', $networks, true) ? 'checked' : '' ?>> LinkedIn</label>
                        <label><input type="checkbox" name="reseaux[]" value="google_my_business" <?= in_array('google_my_business', $networks, true) ? 'checked' : '' ?>> Google Business</label>
                    </div>
                </fieldset>

                <label class="lbl-block">Statut
                    <select name="statut">
                        <option value="brouillon" <?= $status === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="planifie"  <?= $status === 'planifie'  ? 'selected' : '' ?>>Planifié</option>
                        <option value="publie"    <?= $status === 'publie'    ? 'selected' : '' ?>>Publié</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="post-form-v2-panel">
            <h2>Visuel social (SVG)</h2>
            <p style="margin:-6px 0 14px;font-size:.88rem;color:#64748b;line-height:1.5;">
                Choisissez un format (flux carré ou story vertical), puis générez une image vectorielle à partir du titre et du texte — idéal comme base pour vos publications.
            </p>

            <div class="post-form-v2-studio">
                <div class="post-form-v2-format" role="radiogroup" aria-label="Format visuel">
                    <label class="fmt-opt">
                        <input type="radio" name="image_format" value="feed" <?= $imgFmt === 'feed' ? 'checked' : '' ?>>
                        Feed <span style="font-weight:400;color:#64748b;">· 1200×628</span>
                    </label>
                    <label class="fmt-opt">
                        <input type="radio" name="image_format" value="story" <?= $imgFmt === 'story' ? 'checked' : '' ?>>
                        Story <span style="font-weight:400;color:#64748b;">· 1080×1920</span>
                    </label>
                </div>

                <div class="post-form-v2-studio-actions">
                    <button type="button" class="pf-btn pf-btn--gold" id="social-svg-generate">
                        <i class="fas fa-wand-magic-sparkles"></i> Générer le visuel SVG
                    </button>
                    <button type="button" class="pf-btn" id="social-svg-download" title="Télécharger le fichier .svg">
                        <i class="fas fa-download"></i> Télécharger
                    </button>
                    <button type="button" class="pf-btn" id="social-svg-clear">
                        <i class="fas fa-eraser"></i> Effacer le visuel
                    </button>
                </div>

                <div class="post-form-v2-svg-preview-wrap" id="social-svg-preview-wrap">
                    <?php if ($imageSvgStored !== ''): ?>
                        <?= $imageSvgStored ?>
                    <?php else: ?>
                        <div class="post-form-v2-svg-placeholder" id="social-svg-placeholder">
                            Aucun visuel — utilisez « Générer » après avoir saisi titre et texte.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="post-form-v2-actions">
            <button type="submit" class="pf-btn pf-btn--gold">
                <i class="fas fa-check"></i> Enregistrer
            </button>
            <a class="pf-btn" href="<?= htmlspecialchars($cancelUrl, ENT_QUOTES, 'UTF-8') ?>">Annuler</a>
        </div>
    </form>
</section>

<script>
(function(){
    function escXml(s) {
        return String(s ?? '')
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&apos;');
    }
    function excerpt(text, max) {
        var t = String(text ?? '').replace(/\s+/g,' ').trim();
        if (t.length <= max) return t;
        return t.slice(0, max - 1) + '…';
    }
    window.socialBuildSvg = function(format, title, body) {
        var isStory = format === 'story';
        var W = isStory ? 1080 : 1200;
        var H = isStory ? 1920 : 628;
        var titleEsc = escXml(title || 'Votre titre');
        var subEsc = escXml(excerpt(body, isStory ? 160 : 110));
        var fsTitle = isStory ? 52 : 40;
        var fsSub = isStory ? 28 : 20;
        var yTitle = isStory ? 700 : 218;
        var ySub = isStory ? 860 : 292;
        var badgeY = isStory ? 520 : 118;
        return (
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + W + ' ' + H + '" width="' + W + '" height="' + H + '">' +
            '<defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">' +
            '<stop offset="0%" style="stop-color:#0f2237"/><stop offset="100%" style="stop-color:#1a3a5c"/></linearGradient></defs>' +
            '<rect width="100%" height="100%" fill="url(#bg)"/>' +
            '<rect x="' + (isStory ? 72 : 88) + '" y="' + (isStory ? 420 : 72) + '" width="' + (isStory ? 936 : 1024) + '" height="' + (isStory ? 8 : 6) + '" fill="#c9a84c" rx="2"/>' +
            '<text x="' + (W/2) + '" y="' + badgeY + '" text-anchor="middle" fill="#c9a84c" font-family="system-ui,sans-serif" font-size="' + (isStory ? 24 : 17) + '" font-weight="700" letter-spacing="0.14em">SOCIAL</text>' +
            '<text x="' + (W/2) + '" y="' + yTitle + '" text-anchor="middle" fill="#ffffff" font-family="system-ui,sans-serif" font-size="' + fsTitle + '" font-weight="700">' + titleEsc + '</text>' +
            '<text x="' + (W/2) + '" y="' + ySub + '" text-anchor="middle" fill="rgba(255,255,255,.82)" font-family="system-ui,sans-serif" font-size="' + fsSub + '">' + subEsc + '</text>' +
            '</svg>'
        );
    };

    var wrap = document.getElementById('social-svg-preview-wrap');
    var field = document.getElementById('social-image-svg-field');
    var form = document.getElementById('social-post-form');
    if (!wrap || !field || !form) return;

    function currentFormat() {
        var r = form.querySelector('input[name="image_format"]:checked');
        return r && r.value === 'story' ? 'story' : 'feed';
    }
    function renderSvg(svgStr) {
        wrap.innerHTML = svgStr || '';
        field.value = svgStr || '';
        if (!svgStr) {
            wrap.innerHTML = '<div class="post-form-v2-svg-placeholder" id="social-svg-placeholder">Aucun visuel — utilisez « Générer » après avoir saisi titre et texte.</div>';
        }
    }
    document.getElementById('social-svg-generate').addEventListener('click', function() {
        var titre = form.querySelector('input[name="titre"]');
        var contenu = form.querySelector('textarea[name="contenu"]');
        var svg = window.socialBuildSvg(currentFormat(), titre && titre.value, contenu && contenu.value);
        renderSvg(svg);
    });
    document.getElementById('social-svg-clear').addEventListener('click', function() {
        renderSvg('');
    });
    document.getElementById('social-svg-download').addEventListener('click', function() {
        var svg = field.value;
        if (!svg) return;
        var blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'visuel-social-' + currentFormat() + '.svg';
        a.click();
        URL.revokeObjectURL(url);
    });
    form.querySelectorAll('input[name="image_format"]').forEach(function(r) {
        r.addEventListener('change', function() {
            if (field.value) document.getElementById('social-svg-generate').click();
        });
    });
})();
</script>
