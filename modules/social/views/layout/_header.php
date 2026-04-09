<?php
<<<<<<< HEAD
<<<<<<< Updated upstream
$activeAction = (string) ($_GET['action'] ?? 'sequences');
=======
$activeAction = isset($_GET['action']) ? (string) $_GET['action'] : 'sequences';
>>>>>>> 75fa36ef774fcc8396c746e9683cf6fab941b202
$isJournal    = $activeAction === 'journal';

$advisorCity = setting('zone_city', 'Aix-en-Provence');

/* Personas disponibles pour le filtre côté client */
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
?>
<div class="social-wrap">

<!-- ── EN-TÊTE MODULE ── -->
<div class="social-header">
    <div class="social-header-left">
        <div>
            <h1 class="social-header-title"><?= $isJournal ? 'Journal des publications' : 'Séquences de posts' ?></h1>
            <div class="social-header-sub"><?= htmlspecialchars($advisorCity) ?> Métropole</div>
        </div>

        <!-- Toggle Séquences / Journal -->
        <div class="social-view-toggle">
            <a href="/admin?module=social&action=sequences"
               class="svt-btn<?= !$isJournal ? ' is-active' : '' ?>">
                <i class="fas fa-th"></i> Séquences
            </a>
            <a href="/admin?module=social&action=journal"
               class="svt-btn<?= $isJournal ? ' is-active' : '' ?>">
                <i class="fas fa-list-ul"></i> Journal
            </a>
        </div>
=======
$activeView = (string) ($_GET['action'] ?? 'sequences');
$isJournal = $activeView === 'journal';

// KPIs sociaux
try {
    $_spdo = db();
    $_pub_fb    = (int) $_spdo->query("SELECT COUNT(*) FROM blog_publications WHERE reseau='facebook'")->fetchColumn();
    $_pub_ig    = (int) $_spdo->query("SELECT COUNT(*) FROM blog_publications WHERE reseau='instagram'")->fetchColumn();
    $_pub_li    = (int) $_spdo->query("SELECT COUNT(*) FROM blog_publications WHERE reseau='linkedin'")->fetchColumn();
    $_pub_mois  = (int) $_spdo->query("SELECT COUNT(*) FROM blog_publications WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')")->fetchColumn();
    $_pub_total = (int) $_spdo->query("SELECT COUNT(*) FROM blog_publications")->fetchColumn();
    $_seq_total = (int) $_spdo->query("SELECT COUNT(*) FROM crm_sequences")->fetchColumn();
    $_seq_active= (int) $_spdo->query("SELECT COUNT(*) FROM crm_sequences WHERE is_active=1")->fetchColumn();
} catch (Exception $e) {
    $_pub_fb = $_pub_ig = $_pub_li = $_pub_mois = $_pub_total = $_seq_total = $_seq_active = 0;
}
?>

<div class="db-kpi-grid" style="margin-bottom:20px">
    <div class="db-kpi" style="border-left-color:#1877f2">
        <div class="db-kpi-icon">📘</div>
        <div class="db-kpi-val"><?= $_pub_fb ?></div>
        <div class="db-kpi-label">Posts Facebook</div>
        <div class="db-kpi-sub">Au total</div>
    </div>
    <div class="db-kpi" style="border-left-color:#e1306c">
        <div class="db-kpi-icon">📸</div>
        <div class="db-kpi-val"><?= $_pub_ig ?></div>
        <div class="db-kpi-label">Posts Instagram</div>
        <div class="db-kpi-sub">Au total</div>
    </div>
    <div class="db-kpi" style="border-left-color:#0a66c2">
        <div class="db-kpi-icon">💼</div>
        <div class="db-kpi-val"><?= $_pub_li ?></div>
        <div class="db-kpi-label">Posts LinkedIn</div>
        <div class="db-kpi-sub">Au total</div>
    </div>
    <div class="db-kpi accent-gold">
        <div class="db-kpi-icon">📅</div>
        <div class="db-kpi-val"><?= $_pub_mois ?></div>
        <div class="db-kpi-label">Ce mois-ci</div>
        <div class="db-kpi-sub"><?= $_pub_total ?> publications au total</div>
    </div>
    <div class="db-kpi accent-green">
        <div class="db-kpi-icon">⚡</div>
        <div class="db-kpi-val"><?= $_seq_active ?></div>
        <div class="db-kpi-label">Séquences actives</div>
        <div class="db-kpi-sub"><?= $_seq_total ?> séquences au total</div>
    </div>
</div>

<section class="social-module-header">
    <div>
        <h1>Séquences de posts</h1>
        <p><?= $isJournal ? 'Vue chronologique des publications.' : 'Gestion visuelle des séquences automatiques.' ?></p>
>>>>>>> Stashed changes
    </div>

    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <?php if (!$isJournal): ?>
        <!-- Légende niveaux N1-N5 -->
        <div class="social-legend">
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n1)"></div><span class="s-leg-lbl">N1 Inconscient</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n2)"></div><span class="s-leg-lbl">N2 Problème</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n3)"></div><span class="s-leg-lbl">N3 Solutions</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n4)"></div><span class="s-leg-lbl">N4 Évaluation</span></div>
            <div class="s-leg-item"><div class="s-leg-dot" style="background:var(--n5)"></div><span class="s-leg-lbl">N5 Action</span></div>
        </div>
        <?php endif; ?>

        <a href="/admin?module=social&action=post-form" class="s-btn-new">
            <i class="fas fa-plus"></i>
            <?= $isJournal ? 'Nouvelle publication' : 'Nouvelle séquence' ?>
        </a>
        <a href="/admin?module=social&action=kit" class="s-btn-new" style="background:#1e293b;">
            <i class="fas fa-wand-magic-sparkles"></i> Kit publications
        </a>
    </div>
</div>

<!-- ── FILTRES (vue séquences uniquement) ── -->
<?php if (!$isJournal): ?>
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
