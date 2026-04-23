<?php
// modules/prospection/views/campaigns/form.php
$isEdit    = !empty($campaign);
$pageTitle = $isEdit ? 'Modifier la campagne' : 'Nouvelle campagne';
$flash     = Session::getFlash();
$c         = $campaign ?? [];
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible mb-3" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="?module=prospection&action=campaigns" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
    <div>
        <h1 class="h3 mb-0 fw-bold">
            <i class="fas fa-<?= $isEdit ? 'pen' : 'plus-circle' ?> text-warning me-2"></i><?= $pageTitle ?>
        </h1>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-12 col-lg-8">

<form method="POST" action="?module=prospection" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="campaign_save">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="fw-semibold"><i class="fas fa-bullhorn me-2 text-warning"></i>Informations de la campagne</div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Nom de la campagne <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required maxlength="200"
                       value="<?= e($c['name'] ?? '') ?>" placeholder="Ex : Prospection agents immobiliers Aix">
                <div class="form-text">Choisissez un nom clair qui identifie la cible ou l'objectif.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Décrivez le contexte de cette campagne, la cible, l'approche…"><?= e($c['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Objectif</label>
                <input type="text" name="objective" class="form-control" maxlength="255"
                       value="<?= e($c['objective'] ?? '') ?>" placeholder="Ex : Prendre 5 RDV qualifiés en 30 jours">
            </div>
            <?php if ($isEdit): ?>
            <div class="mb-0">
                <label class="form-label fw-semibold small">Statut</label>
                <select name="status" class="form-select">
                    <?php foreach (['draft'=>'Brouillon','active'=>'Active','paused'=>'En pause','completed'=>'Terminée'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= ($c['status'] ?? 'draft') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$isEdit): ?>
    <!-- Option séquence démo -->
    <div class="card border-0 shadow-sm mb-4" style="border-left:4px solid #f59e0b !important;">
        <div class="card-body py-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="seed_demo" value="1" id="seed-demo" checked>
                <label class="form-check-label fw-semibold" for="seed-demo">
                    <i class="fas fa-magic me-2 text-warning"></i>Ajouter la séquence exemple (5 emails)
                </label>
            </div>
            <div class="text-muted small mt-1 ms-4">
                Une séquence de 5 emails pré-rédigés sera créée automatiquement. Vous pourrez la modifier ensuite.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between">
        <a href="?module=prospection&action=campaigns" class="btn btn-outline-secondary">
            <i class="fas fa-xmark me-2"></i>Annuler
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-<?= $isEdit ? 'floppy-disk' : 'plus' ?> me-2"></i>
            <?= $isEdit ? 'Enregistrer' : 'Créer la campagne' ?>
        </button>
    </div>

</form>
</div>
</div>
