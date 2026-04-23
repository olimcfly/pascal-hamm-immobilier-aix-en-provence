<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/services/LocalPartnerService.php';

$pageTitle = 'Partenaires locaux';
$pageDescription = 'Gestion des partenaires du guide local et de la carte Google Maps.';

function renderContent(): void
{
    $service = new LocalPartnerService();
    $service->ensureSchema();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $action = (string) ($_POST['action'] ?? 'save');

        try {
            if ($action === 'delete') {
                $service->delete((int) ($_POST['id'] ?? 0));
                flash('success', 'Partenaire supprimé.');
            } else {
                $service->save($_POST);
                flash('success', 'Partenaire enregistré.');
            }
        } catch (Throwable $e) {
            flash('error', 'Enregistrement impossible : ' . $e->getMessage());
        }

        redirect('/admin?module=partenaires');
    }

    $partners = $service->getAllForAdmin();
    $categories = $service->getCategories();
    $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    $editing = null;
    foreach ($partners as $partner) {
        if ((int) $partner['id'] === $editId) {
            $editing = $partner;
            break;
        }
    }
    ?>
    <div class="page-header">
        <h1><i class="fas fa-handshake page-icon"></i> Partenaires locaux</h1>
        <p>Gérez les fiches affichées dans le guide local et sur la carte.</p>
    </div>

    <div class="card" style="padding:16px;margin-bottom:16px;">
        <h3><?= $editing ? 'Modifier le partenaire' : 'Ajouter un partenaire' ?></h3>
        <form method="post" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
            <input name="nom" placeholder="Nom" value="<?= e((string) ($editing['nom'] ?? '')) ?>" required>
            <input name="slug" placeholder="Slug" value="<?= e((string) ($editing['slug'] ?? '')) ?>">
            <select name="categorie_id">
                <option value="">Catégorie</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= (int) ($editing['categorie_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                        <?= e((string) $cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input name="adresse" placeholder="Adresse" value="<?= e((string) ($editing['adresse'] ?? '')) ?>">
            <input name="ville" placeholder="Ville" value="<?= e((string) ($editing['ville'] ?? '')) ?>">
            <input name="code_postal" placeholder="Code postal" value="<?= e((string) ($editing['code_postal'] ?? '')) ?>">
            <input name="telephone" placeholder="Téléphone" value="<?= e((string) ($editing['telephone'] ?? '')) ?>">
            <input name="site_web" placeholder="Site web" value="<?= e((string) ($editing['site_web'] ?? '')) ?>">
            <input name="logo" placeholder="Logo/image URL" value="<?= e((string) ($editing['logo'] ?? '')) ?>">
            <input name="latitude" placeholder="Latitude" value="<?= e((string) ($editing['latitude'] ?? '')) ?>">
            <input name="longitude" placeholder="Longitude" value="<?= e((string) ($editing['longitude'] ?? '')) ?>">
            <input name="google_maps_url" placeholder="URL Google Maps" value="<?= e((string) ($editing['google_maps_url'] ?? '')) ?>">
            <textarea name="description_courte" placeholder="Description courte" style="grid-column:1/-1;"><?= e((string) ($editing['description_courte'] ?? '')) ?></textarea>
            <textarea name="description_longue" placeholder="Description longue" style="grid-column:1/-1;min-height:100px;"><?= e((string) ($editing['description_longue'] ?? '')) ?></textarea>
            <label><input type="checkbox" name="statut_actif" value="1" <?= !isset($editing['statut_actif']) || (int) $editing['statut_actif'] === 1 ? 'checked' : '' ?>> Actif</label>
            <div style="grid-column:1/-1;display:flex;gap:10px;">
                <button class="btn btn--primary" type="submit">Enregistrer</button>
                <?php if ($editing): ?><a class="btn btn--outline" href="/admin?module=partenaires">Annuler</a><?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card" style="padding:16px;">
        <h3>Partenaires enregistrés</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr><th align="left">Nom</th><th align="left">Catégorie</th><th align="left">Ville</th><th align="left">Statut</th><th align="right">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($partners as $partner): ?>
                <tr>
                    <td><?= e((string) $partner['nom']) ?></td>
                    <td><?= e((string) ($partner['categorie'] ?? '—')) ?></td>
                    <td><?= e(trim((string) (($partner['code_postal'] ?? '') . ' ' . ($partner['ville'] ?? '')))) ?></td>
                    <td><?= (int) ($partner['statut_actif'] ?? 0) === 1 ? 'Actif' : 'Inactif' ?></td>
                    <td align="right">
                        <a href="/admin?module=partenaires&edit=<?= (int) $partner['id'] ?>">Modifier</a>
                        <form method="post" style="display:inline-block" onsubmit="return confirm('Supprimer ce partenaire ?')">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $partner['id'] ?>">
                            <button type="submit" style="border:none;background:none;color:#b91c1c;cursor:pointer;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
