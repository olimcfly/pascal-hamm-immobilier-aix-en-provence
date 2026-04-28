<?php
// modules/prospection/views/contacts/list.php
$pageTitle = 'Contacts — Prospection';
?>
<div class="hub-page">
<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-address-book"></i> Prospection</div>
    <h1>Contacts de prospection</h1>
    <p>Gérez votre base de contacts, suivez leur statut d'email et pilotez leur inscription aux campagnes.</p>
</header>
<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> La base de tout</h3>
        <p>Chaque prospect qualifié ajouté ici peut recevoir une séquence email automatisée — sans intervention manuelle.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Suivi en temps réel</h3>
        <p>Statut email, engagement, réponses — tout est centralisé pour ne laisser passer aucune opportunité.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Qualité > quantité</h3>
        <p>100 contacts qualifiés bien ciblés valent mieux que 1000 adresses sans contexte. Importez avec intention.</p>
    </article>
</div>
</div><!-- /.hub-page -->
<?php

$filters = [
    'search'       => trim($_GET['search']       ?? ''),
    'status'       => $_GET['status']       ?? '',
    'email_status' => $_GET['email_status'] ?? '',
    'source'       => $_GET['source']       ?? '',
];

$page    = max(1, (int) ($_GET['page'] ?? 1));
$result  = $prospectService->getList($filters, $page, 50);
$contacts     = $result['contacts'];
$totalPages   = $result['total_pages'];
$total        = $result['total'];
$stats        = $prospectService->getStats();

$flash = Session::getFlash();

$statusBadge = static function (string $s): string {
    return match($s) {
        'active'       => '<span class="badge bg-success">Actif</span>',
        'paused'       => '<span class="badge bg-warning text-dark">En pause</span>',
        'bounced'      => '<span class="badge bg-danger">Bounced</span>',
        'replied'      => '<span class="badge bg-primary">A répondu</span>',
        'unsubscribed' => '<span class="badge bg-secondary">Désabonné</span>',
        default        => '<span class="badge bg-light text-dark">' . e($s) . '</span>',
    };
};

$emailStatusBadge = static function (string $s): string {
    return match($s) {
        'valid'   => '<span class="badge" style="background:#d1fae5;color:#065f46;font-size:.65rem;">Valide</span>',
        'risky'   => '<span class="badge" style="background:#fef3c7;color:#92400e;font-size:.65rem;">Risqué</span>',
        'invalid' => '<span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.65rem;">Invalide</span>',
        default   => '<span class="badge" style="background:#f3f4f6;color:#6b7280;font-size:.65rem;">Inconnu</span>',
    };
};
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- En-tête -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-users text-primary me-2"></i>Contacts de prospection</h1>
        <p class="text-muted mb-0"><?= number_format($total) ?> contact<?= $total > 1 ? 's' : '' ?> — base à jour</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="?module=prospection&action=contact-import" class="btn btn-outline-secondary">
            <i class="fas fa-file-import me-2"></i>Importer CSV
        </a>
        <a href="?module=prospection&action=contact-new" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau contact
        </a>
    </div>
</div>

<!-- Badges statuts -->
<div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach ([
        'active' => ['Actifs', 'success'],
        'paused' => ['En pause', 'warning'],
        'replied'=> ['Ont répondu', 'primary'],
        'bounced'=> ['Bounced', 'danger'],
        'unsubscribed' => ['Désabonnés', 'secondary'],
    ] as $k => [$label, $color]): ?>
    <?php if (isset($stats[$k])): ?>
    <a href="?module=prospection&action=contacts&status=<?= $k ?>" class="text-decoration-none">
        <span class="badge bg-<?= $color ?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?> border-opacity-25 py-2 px-3" style="font-size:.78rem;">
            <?= $label ?> <strong><?= $stats[$k] ?></strong>
        </span>
    </a>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($filters['status'] || $filters['search']): ?>
    <a href="?module=prospection&action=contacts" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-xmark me-1"></i>Réinitialiser
    </a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="module" value="prospection">
    <input type="hidden" name="action" value="contacts">
    <div class="col-12 col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
            <input type="text" name="search" class="form-control" placeholder="Nom, email, société…" value="<?= e($filters['search']) ?>">
        </div>
    </div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <?php foreach (['active'=>'Actif','paused'=>'En pause','bounced'=>'Bounced','replied'=>'A répondu','unsubscribed'=>'Désabonné'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $filters['status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select name="email_status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Email statut</option>
            <?php foreach (['unknown'=>'Inconnu','valid'=>'Valide','risky'=>'Risqué','invalid'=>'Invalide'] as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $filters['email_status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter me-1"></i>Filtrer</button>
    </div>
</form>

<!-- Tableau -->
<?php if (empty($contacts)): ?>
<div class="text-center py-5">
    <i class="fas fa-user-slash fa-3x text-muted mb-3 opacity-25"></i>
    <h5 class="text-muted">Aucun contact trouvé</h5>
    <p class="text-muted small">Ajoutez votre premier contact ou importez un fichier CSV.</p>
    <a href="?module=prospection&action=contact-new" class="btn btn-primary me-2">
        <i class="fas fa-plus me-2"></i>Ajouter un contact
    </a>
    <a href="?module=prospection&action=contact-import" class="btn btn-outline-secondary">
        <i class="fas fa-file-import me-2"></i>Importer CSV
    </a>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.88rem;">
            <thead class="bg-light">
                <tr>
                    <th>Contact</th>
                    <th>Email</th>
                    <th class="d-none d-md-table-cell">Société</th>
                    <th class="d-none d-md-table-cell">Source</th>
                    <th>Statut</th>
                    <th class="d-none d-lg-table-cell">Email</th>
                    <th class="d-none d-lg-table-cell">Ajouté</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= e(trim($c['first_name'] . ' ' . $c['last_name'])) ?: '<em class="text-muted">—</em>' ?></div>
                        <?php if ($c['city']): ?><div class="text-muted small"><?= e($c['city']) ?></div><?php endif; ?>
                    </td>
                    <td class="text-muted"><?= e($c['email']) ?></td>
                    <td class="d-none d-md-table-cell text-muted"><?= e($c['company'] ?? '—') ?></td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge bg-light text-dark"><?= e($c['source']) ?></span>
                    </td>
                    <td><?= $statusBadge($c['status']) ?></td>
                    <td class="d-none d-lg-table-cell"><?= $emailStatusBadge($c['email_status']) ?></td>
                    <td class="d-none d-lg-table-cell text-muted small"><?= formatDate($c['created_at'], 'd/m/Y') ?></td>
                    <td class="text-end">
                        <a href="?module=prospection&action=contact-edit&contact_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                            onclick="confirmDelete(<?= $c['id'] ?>, '<?= e(addslashes(trim($c['first_name'] . ' ' . $c['last_name']) ?: $c['email'])) ?>')"
                            title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="d-flex justify-content-center mt-4">
    <ul class="pagination pagination-sm">
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?module=prospection&action=contacts&page=<?= $page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
        <?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?module=prospection&action=contacts&page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="?module=prospection&action=contacts&page=<?= $page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<!-- Formulaire suppression (caché) -->
<form id="delete-form" method="POST" action="?module=prospection">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="contact_delete">
    <input type="hidden" name="id" id="delete-id">
</form>

<script>
function confirmDelete(id, name) {
    if (!confirm('Supprimer le contact "' + name + '" ?\n\nCette action est réversible.')) return;
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-form').submit();
}
</script>
