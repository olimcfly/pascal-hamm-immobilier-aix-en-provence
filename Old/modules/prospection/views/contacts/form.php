<?php
// modules/prospection/views/contacts/form.php
$isEdit    = !empty($contact);
$pageTitle = $isEdit ? 'Modifier le contact' : 'Nouveau contact';
$flash     = Session::getFlash();
$c         = $contact ?? [];
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- En-tête -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="?module=prospection&action=contacts" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold">
            <i class="fas fa-<?= $isEdit ? 'pen' : 'user-plus' ?> text-primary me-2"></i><?= $pageTitle ?>
        </h1>
        <?php if ($isEdit): ?>
        <p class="text-muted mb-0 small">Modifiez les informations du contact.</p>
        <?php endif; ?>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-12 col-lg-8">

<form method="POST" action="?module=prospection" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="contact_save">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
    <?php endif; ?>

    <!-- Identité -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="fw-semibold"><i class="fas fa-user me-2 text-primary"></i>Identité</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label fw-semibold small">Prénom</label>
                    <input type="text" name="first_name" class="form-control"
                           value="<?= e($c['first_name'] ?? '') ?>" maxlength="100" placeholder="Jean">
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold small">Nom</label>
                    <input type="text" name="last_name" class="form-control"
                           value="<?= e($c['last_name'] ?? '') ?>" maxlength="100" placeholder="Dupont">
                </div>
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= e($c['email'] ?? '') ?>" maxlength="180" placeholder="jean@societe.fr">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Téléphone</label>
                    <input type="tel" name="phone" class="form-control"
                           value="<?= e($c['phone'] ?? '') ?>" maxlength="40" placeholder="06 00 00 00 00">
                </div>
            </div>
        </div>
    </div>

    <!-- Entreprise -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="fw-semibold"><i class="fas fa-building me-2 text-secondary"></i>Entreprise</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label fw-semibold small">Société</label>
                    <input type="text" name="company" class="form-control"
                           value="<?= e($c['company'] ?? '') ?>" maxlength="180" placeholder="Nom de la société">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Ville</label>
                    <input type="text" name="city" class="form-control"
                           value="<?= e($c['city'] ?? '') ?>" maxlength="120" placeholder="Aix-en-Provence">
                </div>
            </div>
        </div>
    </div>

    <!-- Qualification -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="fw-semibold"><i class="fas fa-tag me-2 text-success"></i>Qualification</div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Source</label>
                    <select name="source" class="form-select">
                        <?php foreach (['manual'=>'Manuel','csv'=>'Import CSV','scraping'=>'Scraping','referral'=>'Recommandation','autre'=>'Autre'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($c['source'] ?? 'manual') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Statut contact</label>
                    <select name="status" class="form-select">
                        <?php foreach (['active'=>'Actif','paused'=>'En pause','bounced'=>'Bounced','replied'=>'A répondu','unsubscribed'=>'Désabonné'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($c['status'] ?? 'active') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small">Statut email</label>
                    <select name="email_status" class="form-select">
                        <?php foreach (['unknown'=>'Inconnu','valid'=>'Valide','risky'=>'Risqué','invalid'=>'Invalide'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= ($c['email_status'] ?? 'unknown') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">
                        Tags
                        <span class="text-muted fw-normal">(séparés par des virgules)</span>
                    </label>
                    <?php
                    $tagsValue = '';
                    if (!empty($c['tags'])) {
                        $decoded = is_string($c['tags']) ? json_decode($c['tags'], true) : $c['tags'];
                        $tagsValue = is_array($decoded) ? implode(', ', $decoded) : $c['tags'];
                    }
                    ?>
                    <input type="text" name="tags" class="form-control"
                           value="<?= e($tagsValue) ?>" placeholder="immobilier, aix, prospect-chaud">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold small">Notes internes</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Contexte, remarques, points d'attention…"><?= e($c['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex justify-content-between">
        <a href="?module=prospection&action=contacts" class="btn btn-outline-secondary">
            <i class="fas fa-xmark me-2"></i>Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-<?= $isEdit ? 'floppy-disk' : 'plus' ?> me-2"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le contact' ?>
        </button>
    </div>

</form>
</div>
</div>
