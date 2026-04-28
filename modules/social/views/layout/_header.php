<?php
$activeAction = isset($_GET['action']) ? (string) $_GET['action'] : 'sequences';
$isJournal         = $activeAction === 'journal';
$isSeqListing      = $activeAction === 'sequences-list';
$isSeqPosts        = $activeAction === 'sequences';
$useCommencerNav = ! empty($GLOBALS['social_use_commencer_nav']);

$advisorCity = setting('zone_city', 'Bordeaux');

$personaFilters = [
    ''             => 'Tous',
    'vendeur'      => '👴 Vendeur Senior',
    'famille'      => '👨‍👩‍👧 Famille',
    'primo'        => '🚀 Primo-accédant',
    'investisseur' => '📈 Investisseur',
    'expatrie'     => '✈️ Expatrié',
];

$currentPersona = isset($_GET['persona']) ? (string) $_GET['persona'] : '';
$currentStatus  = isset($_GET['status']) ? (string) $_GET['status'] : '';

$__admin = static function (array $q): string {
    return function_exists('admin_url') ? admin_url($q) : ('/admin/?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986));
};
?>
<div class="social-wrap<?= $useCommencerNav ? ' social-wrap--seq-v2' : '' ?>">

<?php if ($useCommencerNav): ?>
<header class="seq-v2-topbar">
    <div class="seq-v2-topbar-inner">
        <a href="<?= htmlspecialchars($__admin(['module' => 'social']), ENT_QUOTES, 'UTF-8') ?>" class="seq-v2-topbar-back">
            <i class="fas fa-arrow-left"></i> Social
        </a>
        <div class="seq-v2-topbar-toggle">
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'sequences-list']), ENT_QUOTES, 'UTF-8') ?>" class="<?= $isSeqListing ? 'is-active' : '' ?>">
                <i class="fas fa-list-ul"></i> Liste
            </a>
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'sequences']), ENT_QUOTES, 'UTF-8') ?>" class="<?= $isSeqPosts ? 'is-active' : '' ?>">
                <i class="fas fa-align-left"></i> Posts
            </a>
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'journal']), ENT_QUOTES, 'UTF-8') ?>" class="<?= $isJournal ? 'is-active' : '' ?>">
                <i class="fas fa-newspaper"></i> Journal
            </a>
        </div>
        <div class="seq-v2-topbar-actions">
            <a href="<?= htmlspecialchars($__admin(['module' => 'redaction']), ENT_QUOTES, 'UTF-8') ?>" class="seq-v2-btn-outline">
                <i class="fas fa-pen-to-square"></i> Rédaction
            </a>
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'post-form']), ENT_QUOTES, 'UTF-8') ?>" class="seq-v2-btn-gold">
                <i class="fas fa-plus"></i> Nouveau post
            </a>
        </div>
    </div>
</header>
<?php else: ?>
<div class="social-header">
    <div class="social-header-left">
        <div>
            <h1 class="social-header-title"><?= $isJournal ? 'Suivez vos publications en temps réel' : 'Obtenez plus de prises de contact via vos réseaux' ?></h1>
            <div class="social-header-sub">Plan simple, action rapide · <?= htmlspecialchars($advisorCity) ?></div>
        </div>

        <div class="social-view-toggle">
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'sequences-list']), ENT_QUOTES, 'UTF-8') ?>"
               class="svt-btn<?= $isSeqListing ? ' is-active' : '' ?>">
                <i class="fas fa-list-ul"></i> Liste
            </a>
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'sequences']), ENT_QUOTES, 'UTF-8') ?>"
               class="svt-btn<?= $isSeqPosts ? ' is-active' : '' ?>">
                <i class="fas fa-align-left"></i> Posts
            </a>
            <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'journal']), ENT_QUOTES, 'UTF-8') ?>"
               class="svt-btn<?= $isJournal ? ' is-active' : '' ?>">
                <i class="fas fa-newspaper"></i> Journal
            </a>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <?php if (!$isJournal): ?>
        <div class="social-legend">
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n1)"></div><span class="s-leg-lbl">N1 Inconscient</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n2)"></div><span class="s-leg-lbl">N2 Problème</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n3)"></div><span class="s-leg-lbl">N3 Solutions</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n4)"></div><span class="s-leg-lbl">N4 Évaluation</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n5)"></div><span class="s-leg-lbl">N5 Action</span></div>
        </div>
        <?php endif; ?>

        <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'post-form']), ENT_QUOTES, 'UTF-8') ?>" class="s-btn-new">
            <i class="fas fa-plus"></i>
            <?= $isJournal ? 'Nouvelle publication' : 'Nouvelle séquence' ?>
        </a>
        <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'kit']), ENT_QUOTES, 'UTF-8') ?>" class="s-btn-new" style="background:#1e293b;">
            <i class="fas fa-wand-magic-sparkles"></i> Kit publications
        </a>
    </div>
</div>
<?php endif; ?>

<?php if (! $isJournal && ! $useCommencerNav): ?>
<div class="social-filters">
    <span class="s-filter-label">Persona</span>
    <div class="s-filter-chips" data-filter-group="persona">
        <?php foreach ($personaFilters as $value => $label): ?>
            <span class="s-chip<?= $currentPersona === $value ? ' is-active' : '' ?>"
                  data-filter-value="<?= htmlspecialchars($value) ?>">
                <?= htmlspecialchars($label) ?>
            </span>
        <?php endforeach; ?>
    </div>

    <div class="s-filter-sep"></div>

    <span class="s-filter-label">Statut</span>
    <div class="s-filter-chips" data-filter-group="status">
        <span class="s-chip<?= $currentStatus === '' ? ' is-active' : '' ?>" data-filter-value="all">Toutes</span>
        <span class="s-chip<?= $currentStatus === 'active' ? ' is-active' : '' ?>" data-filter-value="active">
            <span class="sdot sdot-publie"></span> Active
        </span>
        <span class="s-chip<?= $currentStatus === 'pause' ? ' is-active' : '' ?>" data-filter-value="pause">⏸ En pause</span>
        <span class="s-chip<?= $currentStatus === 'brouillon' ? ' is-active' : '' ?>" data-filter-value="brouillon">✏️ Brouillon</span>
    </div>
</div>
<?php endif; ?>

<?php if ($useCommencerNav): ?>
<style>
.seq-v2-topbar { background:#fff; border-bottom:1px solid #e2e8f0; margin:-8px -12px 0; padding:0 12px 12px; }
.seq-v2-topbar-inner { max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; padding-top:12px; }
.seq-v2-topbar-back { font-size:.88rem; font-weight:600; color:#1a3c5e; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
.seq-v2-topbar-back:hover { color:#c9a84c; }
.seq-v2-topbar-toggle { display:flex; gap:6px; background:#f1f5f9; padding:4px; border-radius:10px; }
.seq-v2-topbar-toggle a { font-size:.82rem; font-weight:600; color:#64748b; text-decoration:none; padding:8px 14px; border-radius:8px; display:inline-flex; align-items:center; gap:6px; }
.seq-v2-topbar-toggle a.is-active { background:#fff; color:#1a3c5e; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.seq-v2-topbar-actions { display:flex; gap:10px; flex-wrap:wrap; }
.seq-v2-btn-outline { font-size:.82rem; font-weight:600; color:#1a3c5e; border:1.5px solid #cbd5e1; padding:8px 14px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.seq-v2-btn-outline:hover { border-color:#c9a84c; color:#c9a84c; }
.seq-v2-btn-gold { font-size:.82rem; font-weight:700; background:#c9a84c; color:#10253c; padding:8px 14px; border-radius:8px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.seq-v2-btn-gold:hover { background:#b8962d; }
.social-wrap--seq-v2 { max-width:1200px; margin:0 auto; }
</style>
<?php endif; ?>
