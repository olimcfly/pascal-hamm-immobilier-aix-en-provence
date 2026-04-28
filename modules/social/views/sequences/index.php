<?php
/**
 * @var array<int, array<string, mixed>> $sequences
 * @var array<int, array<int, array<string, mixed>>> $postBySequence
 * @var int $selectedSeqId
 * @var array<string, mixed>|null $selectedSequence
 * @var array<int, array<string, mixed>> $selectedPosts
 */
$sequences = $sequences ?? [];
$postBySequence = $postBySequence ?? [];
$selectedSeqId = (int) ($selectedSeqId ?? 0);
$selectedSequence = $selectedSequence ?? null;
$selectedPosts = $selectedPosts ?? [];

$currentPersona = (string) ($_GET['persona'] ?? 'all');
$currentStatus = (string) ($_GET['status'] ?? 'all');

$__q = static function (array $base): string {
    return function_exists('admin_url') ? admin_url($base) : ('/admin/?' . http_build_query($base, '', '&', PHP_QUERY_RFC3986));
};

$__seqUrl = static function (int $seqId) use ($__q, $currentPersona, $currentStatus): string {
    $p = ['module' => 'social', 'action' => 'sequences', 'seq' => $seqId];
    if ($currentPersona !== 'all') {
        $p['persona'] = $currentPersona;
    }
    if ($currentStatus !== 'all') {
        $p['status'] = $currentStatus;
    }

    return $__q($p);
};

$__listUrl = static function () use ($__q, $currentPersona, $currentStatus): string {
    $p = ['module' => 'social', 'action' => 'sequences-list'];
    if ($currentPersona !== 'all') {
        $p['persona'] = $currentPersona;
    }
    if ($currentStatus !== 'all') {
        $p['status'] = $currentStatus;
    }

    return $__q($p);
};

$__filterUrl = static function (array $overrides) use ($__q, $selectedSeqId, $currentPersona, $currentStatus): string {
    $persona = array_key_exists('persona', $overrides) ? (string) $overrides['persona'] : $currentPersona;
    $status = array_key_exists('status', $overrides) ? (string) $overrides['status'] : $currentStatus;
    $p = ['module' => 'social', 'action' => 'sequences'];
    if ($selectedSeqId > 0) {
        $p['seq'] = $selectedSeqId;
    }
    if ($persona !== 'all') {
        $p['persona'] = $persona;
    }
    if ($status !== 'all') {
        $p['status'] = $status;
    }

    return $__q($p);
};

$seq_format_ref = static function (array $row): string {
    $r = trim((string) ($row['ref_code'] ?? ''));
    if ($r !== '') {
        return $r;
    }

    return 'SEQ-' . (int) ($row['id'] ?? 0);
};

$statutLabels = [
    'active' => ['Actif', 'seq-pill--ok'],
    'pause' => ['Pause', 'seq-pill--pause'],
    'brouillon' => ['Brouillon', 'seq-pill--draft'],
];
?>

<style>
.seq-v2-page { display:grid; gap:22px; padding-bottom:32px; }
.seq-v2-hero {
    background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);
    border-radius:16px;
    padding:28px 26px;
    color:#fff;
    box-shadow:0 4px 20px rgba(15,34,55,.18);
}
.seq-v2-hero-badge {
    display:inline-block;
    background:rgba(201,168,76,.2);
    color:#c9a84c;
    font-size:11px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
    padding:4px 12px;
    border-radius:20px;
    margin-bottom:12px;
    border:1px solid rgba(201,168,76,.35);
}
.seq-v2-hero h1 { margin:0 0 10px; font-size:clamp(22px,3vw,28px); font-weight:700; color:#fff; line-height:1.25; }
.seq-v2-hero p { margin:0; font-size:15px; color:rgba(255,255,255,.72); line-height:1.65; max-width:720px; }

.seq-v2-toolbar {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:14px;
    padding:14px 16px;
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
}
.seq-v2-toolbar-back {
    font-size:.85rem;
    font-weight:600;
    color:#1a3c5e;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    white-space:nowrap;
}
.seq-v2-toolbar-back:hover { color:#c9a84c; }
.seq-v2-seq-wrap {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:10px;
    flex:1;
    min-width:min(100%,240px);
}
.seq-v2-seq-label {
    font-size:.72rem;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.seq-v2-seq-select {
    flex:1;
    min-width:200px;
    max-width:440px;
    font-size:.88rem;
    font-weight:600;
    padding:8px 12px;
    border-radius:8px;
    border:1px solid #cbd5e1;
    color:#0f172a;
    background:#fff;
}
.seq-v2-filters {
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    flex:1;
    justify-content:flex-end;
    min-width:min(100%,220px);
}
.seq-v2-filters a {
    font-size:.76rem;
    font-weight:600;
    padding:5px 10px;
    border-radius:999px;
    text-decoration:none;
    color:#64748b;
    background:#fafbfc;
    border:1px solid #e2e8f0;
}
.seq-v2-filters a.is-active { background:#1a3c5e; color:#fff; border-color:#1a3c5e; }

.seq-v2-main {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:22px 22px 26px;
    min-height:320px;
}
.seq-v2-empty {
    text-align:center;
    padding:48px 20px;
    color:#94a3b8;
    font-size:.95rem;
}
.seq-v2-detail-head {
    display:flex;
    flex-wrap:wrap;
    justify-content:space-between;
    gap:14px;
    margin-bottom:20px;
    padding-bottom:18px;
    border-bottom:1px solid #f1f5f9;
}
.seq-v2-detail-title { margin:0; font-size:1.25rem; font-weight:800; color:#0f172a; }
.seq-v2-detail-sub { margin:6px 0 0; font-size:.88rem; color:#64748b; }
.seq-v2-pills { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
.seq-pill {
    font-size:.72rem;
    font-weight:700;
    padding:4px 10px;
    border-radius:999px;
    background:#f1f5f9;
    color:#475569;
}
.seq-pill--ok { background:#dcfce7; color:#166534; }
.seq-pill--pause { background:#fef3c7; color:#92400e; }
.seq-pill--draft { background:#e2e8f0; color:#475569; }

.seq-v2-actions { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
.seq-v2-actions form { display:inline; }
.seq-v2-btn {
    display:inline-flex;
    align-items:center;
    gap:6px;
    font-size:.82rem;
    font-weight:600;
    padding:8px 14px;
    border-radius:8px;
    border:1px solid #cbd5e1;
    background:#fff;
    color:#1a3c5e;
    cursor:pointer;
    text-decoration:none;
}
.seq-v2-btn:hover { border-color:#c9a84c; color:#b45309; }
.seq-v2-btn--primary { background:#1a3c5e; color:#fff; border-color:#1a3c5e; }
.seq-v2-btn--primary:hover { background:#0f2237; color:#fff; }
.seq-v2-btn--gold { background:#c9a84c; color:#10253c; border-color:#c9a84c; font-weight:700; }
.seq-v2-btn--gold:hover { background:#b8962d; }

.seq-v2-posts-title { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#8a95a3; margin:8px 0 14px; }
.seq-v2-posts-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:18px;
}
.seq-v2-add {
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:120px;
    border:2px dashed #cbd5e1;
    border-radius:12px;
    color:#64748b;
    font-weight:600;
    font-size:.88rem;
    text-decoration:none;
    transition:border-color .15s, color .15s;
}
.seq-v2-add:hover { border-color:#c9a84c; color:#b45309; }
</style>

<section class="seq-v2-page">
    <header class="seq-v2-hero">
        <span class="seq-v2-hero-badge"><i class="fas fa-align-left"></i> Posts de la séquence</span>
        <h1>N1 → N5 : relisez, modifiez et publiez</h1>
        <p>
            Retrouvez la <a href="<?= htmlspecialchars($__listUrl(), ENT_QUOTES, 'UTF-8') ?>" style="color:#c9a84c;font-weight:600;">liste des séquences</a>
            pour changer de campagne. Ici, concentrez-vous sur les posts de la ligne sélectionnée — retestez le message sur le réseau ciblé avant publication.
        </p>
    </header>

    <?php if ($sequences === []): ?>
        <div class="seq-v2-main">
            <div class="seq-v2-empty">
                <p style="margin:0 0 16px;font-size:1rem;color:#64748b;">Aucune séquence pour l’instant.</p>
                <p style="margin:0 0 20px;font-size:.9rem;color:#94a3b8;">Créez une séquence depuis un article : <strong>Rédaction</strong> → ouvrir l’article → <strong>Créer une séquence social</strong>.</p>
                <a href="<?= htmlspecialchars($__q(['module' => 'redaction']), ENT_QUOTES, 'UTF-8') ?>" class="seq-v2-btn seq-v2-btn--gold">
                    <i class="fas fa-pen-to-square"></i> Aller à la rédaction
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="seq-v2-toolbar">
            <a class="seq-v2-toolbar-back" href="<?= htmlspecialchars($__listUrl(), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-arrow-left"></i> Liste des séquences
            </a>
            <div class="seq-v2-seq-wrap">
                <label class="seq-v2-seq-label" for="seq-v2-seq-switch">Séquence</label>
                <select id="seq-v2-seq-switch" class="seq-v2-seq-select" aria-label="Changer de séquence"
                        onchange="if (this.value) { window.location.href = this.value; }">
                    <?php foreach ($sequences as $seq):
                        $sid = (int) ($seq['id'] ?? 0);
                        ?>
                        <option value="<?= htmlspecialchars($__seqUrl($sid), ENT_QUOTES, 'UTF-8') ?>"
                            <?= $sid === $selectedSeqId ? ' selected' : '' ?>>
                            <?= htmlspecialchars($seq_format_ref($seq) . ' — ' . (string) ($seq['nom'] ?? 'Séquence'), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="seq-v2-filters">
                <a href="<?= htmlspecialchars($__filterUrl(['persona' => 'all', 'status' => 'all']), ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= ($currentPersona === 'all' && $currentStatus === 'all') ? 'is-active' : '' ?>">Tout</a>
                <a href="<?= htmlspecialchars($__filterUrl(['status' => 'active']), ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= $currentStatus === 'active' ? 'is-active' : '' ?>">Actives</a>
                <a href="<?= htmlspecialchars($__filterUrl(['status' => 'pause']), ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= $currentStatus === 'pause' ? 'is-active' : '' ?>">Pause</a>
                <a href="<?= htmlspecialchars($__filterUrl(['status' => 'brouillon']), ENT_QUOTES, 'UTF-8') ?>"
                   class="<?= $currentStatus === 'brouillon' ? 'is-active' : '' ?>">Brouillon</a>
            </div>
        </div>

        <div class="seq-v2-main">
            <?php if ($selectedSequence === null): ?>
                <div class="seq-v2-empty">Séquence introuvable.</div>
            <?php else:
                $st = (string) ($selectedSequence['statut'] ?? 'active');
                $pill = $statutLabels[$st] ?? ['Actif', 'seq-pill--ok'];
                $srcArticle = (int) ($selectedSequence['source_article_id'] ?? 0);
                ?>
                <div class="seq-v2-detail-head">
                    <div>
                        <p style="margin:0 0 4px;font-size:.75rem;font-weight:800;color:#c9a84c;letter-spacing:.06em;">
                            <?= htmlspecialchars($seq_format_ref($selectedSequence), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <h2 class="seq-v2-detail-title"><?= htmlspecialchars((string) ($selectedSequence['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="seq-v2-detail-sub">
                            Persona : <strong><?= htmlspecialchars((string) ($selectedSequence['persona'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            · Objectif : <?= htmlspecialchars((string) ($selectedSequence['objectif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                            <?php if (! empty($selectedSequence['zone'])): ?>
                                · Zone <?= htmlspecialchars((string) $selectedSequence['zone'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="seq-v2-pills">
                        <span class="seq-pill <?= htmlspecialchars($pill[1], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pill[0], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <div class="seq-v2-actions" style="margin-bottom:22px;">
                    <?php if ($srcArticle > 0): ?>
                        <a class="seq-v2-btn seq-v2-btn--primary"
                           href="<?= htmlspecialchars($__q(['module' => 'redaction', 'action' => 'article_edit', 'id' => $srcArticle]), ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-file-alt"></i> Article source (#<?= (int) $srcArticle ?>)
                        </a>
                    <?php endif; ?>
                    <a class="seq-v2-btn seq-v2-btn--gold"
                       href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'post-form', 'sequence_id' => $selectedSeqId]), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-plus"></i> Ajouter un post
                    </a>
                    <form method="post" action="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'duplicate-sequence']), ENT_QUOTES, 'UTF-8') ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $selectedSeqId ?>">
                        <button type="submit" class="seq-v2-btn"><i class="fas fa-copy"></i> Dupliquer</button>
                    </form>
                    <form method="post" action="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'toggle-sequence']), ENT_QUOTES, 'UTF-8') ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $selectedSeqId ?>">
                        <button type="submit" class="seq-v2-btn">
                            <?= $st === 'pause' ? '<i class="fas fa-play"></i> Reprendre' : '<i class="fas fa-pause"></i> Pause' ?>
                        </button>
                    </form>
                    <a class="seq-v2-btn" href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'journal']), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-newspaper"></i> Journal
                    </a>
                </div>

                <h3 class="seq-v2-posts-title">Posts de cette séquence (ordre N1 → N5)</h3>
                <?php if ($selectedPosts === []): ?>
                    <p style="color:#94a3b8;font-size:.9rem;">Aucun post — ajoutez-en un ou régénérez depuis l’article.</p>
                <?php else: ?>
                <div class="seq-v2-posts-grid">
                    <?php
                    $GLOBALS['social_post_card_compact'] = false;
                    foreach ($selectedPosts as $i => $post):
                        ?>
                        <?php include __DIR__ . '/_post_card.php'; ?>
                    <?php endforeach; ?>
                    <a class="seq-v2-add" href="<?= htmlspecialchars($__q(['module' => 'social', 'action' => 'post-form', 'sequence_id' => $selectedSeqId]), ENT_QUOTES, 'UTF-8') ?>">
                        + Post
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

</div><!-- /.social-wrap — ouvert dans _header.php -->
