<?php
/**
 * @var array<int, array<string, mixed>> $sequences
 * @var array<int, array<int, array<string, mixed>>> $postBySequence
 */
$sequences = $sequences ?? [];
$postBySequence = $postBySequence ?? [];

$currentPersona = (string) ($_GET['persona'] ?? 'all');
$currentStatus = (string) ($_GET['status'] ?? 'all');

$__q = static function (array $base): string {
    return function_exists('admin_url') ? admin_url($base) : ('/admin/?' . http_build_query($base, '', '&', PHP_QUERY_RFC3986));
};

$__seqPostsUrl = static function (int $seqId) use ($__q, $currentPersona, $currentStatus): string {
    $p = ['module' => 'social', 'action' => 'sequences', 'seq' => $seqId];
    if ($currentPersona !== 'all') {
        $p['persona'] = $currentPersona;
    }
    if ($currentStatus !== 'all') {
        $p['status'] = $currentStatus;
    }

    return $__q($p);
};

$__filterUrl = static function (array $overrides) use ($__q, $currentPersona, $currentStatus): string {
    $persona = array_key_exists('persona', $overrides) ? (string) $overrides['persona'] : $currentPersona;
    $status = array_key_exists('status', $overrides) ? (string) $overrides['status'] : $currentStatus;
    $p = ['module' => 'social', 'action' => 'sequences-list'];
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
.seq-list-page { display:grid; gap:22px; padding-bottom:32px; }
.seq-list-hero {
    background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);
    border-radius:16px;
    padding:28px 26px;
    color:#fff;
    box-shadow:0 4px 20px rgba(15,34,55,.18);
}
.seq-list-hero-badge {
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
.seq-list-hero h1 { margin:0 0 10px; font-size:clamp(22px,3vw,28px); font-weight:700; color:#fff; line-height:1.25; }
.seq-list-hero p { margin:0; font-size:15px; color:rgba(255,255,255,.72); line-height:1.65; max-width:720px; }

.seq-list-toolbar {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:12px;
    justify-content:space-between;
}
.seq-list-filters {
    display:flex;
    flex-wrap:wrap;
    gap:6px;
}
.seq-list-filters a {
    font-size:.76rem;
    font-weight:600;
    padding:6px 12px;
    border-radius:999px;
    text-decoration:none;
    color:#64748b;
    background:#fff;
    border:1px solid #e2e8f0;
}
.seq-list-filters a.is-active { background:#1a3c5e; color:#fff; border-color:#1a3c5e; }

.seq-list-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:14px;
}
.seq-list-card {
    display:block;
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px 18px 16px;
    text-decoration:none;
    color:inherit;
    transition:border-color .15s, box-shadow .15s;
}
.seq-list-card:hover {
    border-color:#c9a84c;
    box-shadow:0 4px 14px rgba(15,34,55,.08);
}
.seq-list-card-ref { font-size:.72rem; font-weight:800; color:#c9a84c; letter-spacing:.04em; margin-bottom:6px; }
.seq-list-card-title { font-size:1rem; font-weight:700; color:#0f172a; line-height:1.35; margin-bottom:8px; }
.seq-list-card-meta { font-size:.82rem; color:#64748b; line-height:1.45; margin-bottom:12px; }
.seq-list-card-meta strong { color:#334155; font-weight:600; }
.seq-list-card-footer {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:center;
    justify-content:space-between;
    padding-top:12px;
    border-top:1px solid #f1f5f9;
    font-size:.78rem;
    font-weight:600;
    color:#1a3c5e;
}
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

.seq-list-empty {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    text-align:center;
    padding:48px 20px;
    color:#94a3b8;
    font-size:.95rem;
}
.seq-list-cta {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:.88rem;
    font-weight:700;
    background:#c9a84c;
    color:#10253c;
    padding:10px 18px;
    border-radius:8px;
    text-decoration:none;
}
.seq-list-cta:hover { background:#b8962d; }
</style>

<section class="seq-list-page">
    <header class="seq-list-hero">
        <span class="seq-list-hero-badge"><i class="fas fa-list-ul"></i> Liste des séquences</span>
        <h1>Vos séquences social</h1>
        <p>Ouvrez une séquence pour voir et modifier les posts (N1→N5), ou créez-en une depuis la rédaction d’un article.</p>
    </header>

    <?php if ($sequences === []): ?>
        <div class="seq-list-empty">
            <p style="margin:0 0 16px;font-size:1rem;color:#64748b;">Aucune séquence pour l’instant.</p>
            <p style="margin:0 0 20px;font-size:.9rem;color:#94a3b8;">Créez une séquence depuis un article : <strong>Rédaction</strong> → ouvrir l’article → <strong>Créer une séquence social</strong>.</p>
            <a href="<?= htmlspecialchars($__q(['module' => 'redaction']), ENT_QUOTES, 'UTF-8') ?>" class="seq-list-cta">
                <i class="fas fa-pen-to-square"></i> Aller à la rédaction
            </a>
        </div>
    <?php else: ?>
        <div class="seq-list-toolbar">
            <div class="seq-list-filters">
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

        <div class="seq-list-grid">
            <?php foreach ($sequences as $seq):
                $sid = (int) ($seq['id'] ?? 0);
                $posts = $postBySequence[$sid] ?? [];
                $st = (string) ($seq['statut'] ?? 'active');
                $pill = $statutLabels[$st] ?? ['Actif', 'seq-pill--ok'];
                ?>
                <a class="seq-list-card" href="<?= htmlspecialchars($__seqPostsUrl($sid), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="seq-list-card-ref"><?= htmlspecialchars($seq_format_ref($seq), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="seq-list-card-title"><?= htmlspecialchars((string) ($seq['nom'] ?? 'Séquence'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="seq-list-card-meta">
                        <strong>Persona :</strong> <?= htmlspecialchars((string) ($seq['persona'] ?? '—'), ENT_QUOTES, 'UTF-8') ?>
                        <?php if (! empty($seq['objectif'])): ?>
                            <br><strong>Objectif :</strong> <?= htmlspecialchars((string) $seq['objectif'], ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                        <br><?= count($posts) ?> post<?= count($posts) > 1 ? 's' : '' ?>
                    </div>
                    <div class="seq-list-card-footer">
                        <span class="seq-pill <?= htmlspecialchars($pill[1], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($pill[0], ENT_QUOTES, 'UTF-8') ?></span>
                        <span>Ouvrir les posts <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

</div><!-- /.social-wrap — ouvert dans _header.php -->
