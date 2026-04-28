<?php
/**
 * @var array<string, mixed>|null $post
 */
$__q = static function (array $base): string {
    return function_exists('admin_url') ? admin_url($base) : ('/admin/?' . http_build_query($base, '', '&', PHP_QUERY_RFC3986));
};
?>
<?php if ($post === null): ?>
    <article class="post-v2-empty">
        <p class="post-v2-empty-msg">Publication introuvable.</p>
        <a class="post-v2-btn post-v2-btn--outline" href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'journal']), ENT_QUOTES, 'UTF-8') ?>">
            <i class="fas fa-newspaper"></i> Journal
        </a>
    </article>
<?php else:
    $postId      = (int) ($post['id'] ?? 0);
    $sequenceId  = (int) ($post['sequence_id'] ?? 0);
    $reseaux     = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [];
    $statut      = (string) ($post['statut'] ?? 'brouillon');
    $niveau      = (string) ($post['niveau'] ?? '');
    $planifieRaw = (string) ($post['planifie_at'] ?? '');
    $imageSvg    = social_sanitize_svg((string) ($post['image_svg'] ?? ''));

    $netLabels = [
        'facebook'           => ['Facebook', 'fab fa-facebook-f', '#1877F2'],
        'instagram'          => ['Instagram', 'fab fa-instagram', '#E1306C'],
        'linkedin'           => ['LinkedIn', 'fab fa-linkedin-in', '#0A66C2'],
        'google_my_business' => ['Google Business', 'fab fa-google', '#34A853'],
    ];

    $niveauHuman = [
        'n1' => 'N1 — Prise de conscience',
        'n2' => 'N2 — Problème identifié',
        'n3' => 'N3 — Solutions envisagées',
        'n4' => 'N4 — Comparaison / décision',
        'n5' => 'N5 — Passage à l’action',
    ];

    $statutLabel = [
        'brouillon' => ['Brouillon', 'post-v2-pill--draft'],
        'planifie'  => ['Planifié', 'post-v2-pill--plan'],
        'publie'    => ['Publié', 'post-v2-pill--ok'],
        'erreur'    => ['Erreur', 'post-v2-pill--err'],
    ];
    $stInfo = $statutLabel[$statut] ?? ['—', 'post-v2-pill--draft'];

    $scheduleLabel = 'Non planifié';
    if ($planifieRaw !== '') {
        $ts = strtotime($planifieRaw);
        $scheduleLabel = $ts ? date('d/m/Y \à H:i', $ts) : htmlspecialchars($planifieRaw, ENT_QUOTES, 'UTF-8');
    }
    ?>

<style>
.post-v2-page { max-width:920px; margin:0 auto 40px; padding-bottom:8px; }
.post-v2-hero {
    background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);
    border-radius:16px;
    padding:26px 24px;
    color:#fff;
    margin-bottom:18px;
    box-shadow:0 4px 20px rgba(15,34,55,.15);
}
.post-v2-hero-badge { font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#c9a84c; margin-bottom:10px; display:inline-flex; align-items:center; gap:8px; }
.post-v2-hero h1 { margin:0 0 8px; font-size:clamp(1.25rem,2.5vw,1.75rem); font-weight:700; line-height:1.25; color:#fff; font-family:inherit; }
.post-v2-hero-meta { margin:0; font-size:.88rem; color:rgba(255,255,255,.72); }

.post-v2-panel {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:20px 22px;
    margin-bottom:14px;
}
.post-v2-row-meta {
    display:flex;
    flex-wrap:wrap;
    gap:14px 18px;
    align-items:flex-start;
}
.post-v2-meta-block { flex:1; min-width:160px; }
.post-v2-meta-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:6px; }
.post-v2-nlevel {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-weight:700;
    font-size:.95rem;
    color:#0f172a;
}
.post-v2-nlevel-dot {
    width:10px; height:10px; border-radius:50%;
}
.nd-n1{background:#94a3b8}.nd-n2{background:#3b82f6}.nd-n3{background:#f59e0b}.nd-n4{background:#f97316}.nd-n5{background:#22c55e}.nd-none{background:#cbd5e1}

.post-v2-nets { display:flex; flex-wrap:wrap; gap:8px; }
.post-v2-net-pill {
    display:inline-flex; align-items:center; gap:8px;
    font-size:.82rem; font-weight:600;
    padding:8px 12px;
    border-radius:10px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    color:#0f172a;
}
.post-v2-net-pill i { font-size:14px; }

.post-v2-schedule {
    display:flex;
    align-items:flex-start;
    gap:12px;
    padding:14px 16px;
    border-radius:12px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    margin-bottom:14px;
}
.post-v2-schedule i { color:#c9a84c; margin-top:2px; }
.post-v2-schedule strong { display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin-bottom:4px; }
.post-v2-schedule span { font-size:.95rem; font-weight:600; color:#1e293b; }

.post-v2-svg-stage {
    border-radius:12px;
    overflow:hidden;
    border:1px solid #e5e7eb;
    background:#f1f5f9;
    margin-bottom:14px;
    max-height:min(70vh,520px);
    display:flex;
    align-items:center;
    justify-content:center;
}
.post-v2-svg-stage svg { width:100%; height:auto; display:block; vertical-align:middle; }

.post-v2-body-title { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin:0 0 10px; }
.post-v2-body-text {
    font-size:1rem;
    line-height:1.65;
    color:#334155;
    white-space:pre-wrap;
}

.post-v2-actions-bar {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    align-items:center;
    justify-content:space-between;
    margin-top:18px;
    padding-top:18px;
    border-top:1px solid #f1f5f9;
}
.post-v2-actions-left, .post-v2-actions-right { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }

.post-v2-btn {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:.85rem;
    font-weight:600;
    padding:10px 16px;
    border-radius:10px;
    border:1px solid #cbd5e1;
    background:#fff;
    color:#1a3c5e;
    cursor:pointer;
    text-decoration:none;
    font-family:inherit;
}
.post-v2-btn:hover { border-color:#c9a84c; color:#b45309; }
.post-v2-btn--gold {
    background:#c9a84c;
    border-color:#c9a84c;
    color:#10253c;
    font-weight:700;
}
.post-v2-btn--gold:hover { background:#b8962d; color:#10253c; }
.post-v2-btn--outline { background:transparent; }

.post-v2-pill {
    font-size:.78rem;
    font-weight:700;
    padding:5px 11px;
    border-radius:999px;
}
.post-v2-pill--draft { background:#e2e8f0; color:#475569; }
.post-v2-pill--plan { background:#dbeafe; color:#1e40af; }
.post-v2-pill--ok { background:#dcfce7; color:#166534; }
.post-v2-pill--err { background:#fee2e2; color:#991b1b; }

.post-v2-empty { text-align:center; padding:48px 20px; max-width:420px; margin:0 auto; }
.post-v2-empty-msg { color:#64748b; margin-bottom:16px; font-size:1rem; }
</style>

<section class="post-v2-page">
    <header class="post-v2-hero">
        <div class="post-v2-hero-badge"><i class="fas fa-eye"></i> Lecture · Publication</div>
        <h1><?= htmlspecialchars((string) ($post['titre'] ?? 'Publication'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="post-v2-hero-meta">
            <?= htmlspecialchars((string) ($post['sequence_nom'] ?? 'Sans séquence'), ENT_QUOTES, 'UTF-8') ?>
            <span class="post-v2-pill <?= htmlspecialchars($stInfo[1], ENT_QUOTES, 'UTF-8') ?>" style="margin-left:10px;"><?= htmlspecialchars($stInfo[0], ENT_QUOTES, 'UTF-8') ?></span>
        </p>
    </header>

    <div class="post-v2-panel">
        <div class="post-v2-row-meta">
            <div class="post-v2-meta-block">
                <div class="post-v2-meta-label">Niveau de conscience</div>
                <?php if ($niveau !== ''): ?>
                    <div class="post-v2-nlevel">
                        <?php $nDot = preg_match('/^n[1-5]$/', $niveau) ? $niveau : 'none'; ?>
                        <span class="post-v2-nlevel-dot nd-<?= htmlspecialchars($nDot, ENT_QUOTES, 'UTF-8') ?>"></span>
                        <?= htmlspecialchars($niveauHuman[$niveau] ?? strtoupper($niveau), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php else: ?>
                    <span style="color:#94a3b8;font-size:.9rem;">Non défini</span>
                <?php endif; ?>
            </div>
            <div class="post-v2-meta-block" style="flex:2;min-width:220px;">
                <div class="post-v2-meta-label">Réseaux ciblés</div>
                <div class="post-v2-nets">
                    <?php foreach ($reseaux as $r):
                        $info = $netLabels[$r] ?? [ucfirst((string) $r), 'fas fa-share-alt', '#64748b'];
                        ?>
                        <span class="post-v2-net-pill">
                            <i class="<?= htmlspecialchars($info[1], ENT_QUOTES, 'UTF-8') ?>" style="color:<?= htmlspecialchars($info[2], ENT_QUOTES, 'UTF-8') ?>;"></i>
                            <?= htmlspecialchars($info[0], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if ($reseaux === []): ?>
                        <span style="color:#94a3b8;font-size:.9rem;">Aucun réseau sélectionné</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="post-v2-schedule">
        <i class="far fa-clock fa-lg"></i>
        <div>
            <strong>Publication prévue</strong>
            <span><?= $scheduleLabel ?></span>
        </div>
    </div>

    <?php if ($imageSvg !== ''): ?>
        <div class="post-v2-svg-stage" aria-label="Visuel généré"><?= $imageSvg ?></div>
    <?php endif; ?>

    <div class="post-v2-panel">
        <h2 class="post-v2-body-title">Texte du post</h2>
        <div class="post-v2-body-text"><?= nl2br(htmlspecialchars((string) ($post['contenu'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>

        <div class="post-v2-actions-bar">
            <div class="post-v2-actions-left">
                <a class="post-v2-btn post-v2-btn--gold" href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'post-edit', 'id' => $postId]), ENT_QUOTES, 'UTF-8') ?>">
                    <i class="fas fa-pen-to-square"></i> Modifier la publication
                </a>
                <?php if ($sequenceId > 0): ?>
                    <a class="post-v2-btn post-v2-btn--outline" href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'sequences', 'seq' => $sequenceId]), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-layer-group"></i> Retour à la séquence
                    </a>
                <?php endif; ?>
            </div>
            <div class="post-v2-actions-right">
                <form method="post" action="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'delete-post']), ENT_QUOTES, 'UTF-8') ?>" onsubmit="return confirm('Supprimer cette publication ?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $postId ?>">
                    <button type="submit" class="post-v2-btn" style="border-color:#fecaca;color:#b91c1c;">
                        <i class="fas fa-trash-alt"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
