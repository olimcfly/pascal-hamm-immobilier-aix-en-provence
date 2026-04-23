<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/services/LocalPartnerService.php';

$pageTitle = 'Partenaires locaux';

// ── POST handling au niveau fichier (avant tout rendu HTML) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $service = new LocalPartnerService();
    $service->ensureSchema();
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

function renderContent(): void
{
    $service    = new LocalPartnerService();
    $service->ensureSchema();
    $partners   = $service->getAllForAdmin();
    $categories = $service->getCategories();
    $editId     = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
    $editing    = null;
    foreach ($partners as $partner) {
        if ((int) $partner['id'] === $editId) {
            $editing = $partner;
            break;
        }
    }

    $flashMsg  = Session::getFlash();
    ?>
    <style>
    .part-grid{display:grid;gap:1.2rem}
    .part-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.2rem 1.4rem;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
    .part-card h3{margin:0 0 1rem;font-size:1rem;color:#0f172a;display:flex;align-items:center;gap:.4rem}
    .part-form{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}
    .part-form input,.part-form select,.part-form textarea{border:1px solid #cbd5e1;border-radius:10px;padding:.55rem .7rem;font-size:.88rem;width:100%}
    .part-form textarea{grid-column:1/-1;min-height:80px;resize:vertical}
    .part-form-actions{grid-column:1/-1;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-top:.2rem}
    .part-table-wrap{overflow-x:auto}
    .part-table{width:100%;border-collapse:collapse;min-width:600px}
    .part-table th,.part-table td{padding:.65rem .8rem;border-bottom:1px solid #f1f5f9;text-align:left;font-size:.88rem;vertical-align:middle}
    .part-table th{font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:700;background:#fafbfc}
    .part-table tr:last-child td{border-bottom:none}
    .part-badge-active{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .5rem;border-radius:999px;font-size:.73rem;font-weight:700;background:#dcfce7;color:#166534}
    .part-badge-inactive{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .5rem;border-radius:999px;font-size:.73rem;font-weight:700;background:#f1f5f9;color:#64748b}
    .part-empty{padding:2.5rem 1rem;text-align:center;color:#94a3b8}
    .part-link{color:#1d4ed8;text-decoration:none;font-size:.84rem;font-weight:600}
    .part-link:hover{text-decoration:underline}
    .part-delete-btn{background:none;border:none;color:#b91c1c;cursor:pointer;font-size:.84rem;font-weight:600;padding:0}
    @media(max-width:700px){.part-form{grid-template-columns:1fr}}
    </style>

    <div class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-handshake"></i> Réseau local</div>
            <h1>Partenaires locaux</h1>
            <p>Gérez les fiches affichées dans votre guide local et sur la carte Google Maps de votre site.</p>
        </header>

        <div class="part-info-wrap">
            <button class="part-info-btn" type="button"><i class="fas fa-circle-info"></i> À quoi sert ce module ?</button>
            <div class="part-info-tooltip" role="tooltip">
                <div class="part-info-row"><i class="fas fa-map-pin" style="color:#3b82f6"></i><div><strong>À quoi ça sert</strong><br>Chaque partenaire ajouté ici apparaît dans le guide local de votre site : plombiers, notaires, architectes, artisans de confiance.</div></div>
                <div class="part-info-row"><i class="fas fa-check-circle" style="color:#10b981"></i><div><strong>L'avantage</strong><br>Un guide local étoffé renforce votre expertise territoriale et fidélise vos visiteurs — c'est aussi un bon signal SEO local.</div></div>
                <div class="part-info-row"><i class="fas fa-bolt" style="color:#f59e0b"></i><div><strong>Conseil</strong><br>Commencez par 5 à 10 partenaires clés bien documentés plutôt qu'une longue liste vide de descriptions.</div></div>
            </div>
        </div>
        <style>.part-info-wrap{position:relative;display:inline-block;margin-bottom:1.25rem;}.part-info-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;padding:.4rem .85rem;font-size:.85rem;color:#64748b;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:background .15s,color .15s;}.part-info-btn:hover{background:#f1f5f9;color:#334155;}.part-info-tooltip{display:none;position:absolute;top:calc(100% + 8px);left:0;z-index:200;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:1rem 1.1rem;width:400px;max-width:90vw;}.part-info-tooltip.is-open{display:block;}.part-info-row{display:flex;gap:.75rem;align-items:flex-start;padding:.55rem 0;font-size:.84rem;line-height:1.45;color:#374151;}.part-info-row+.part-info-row{border-top:1px solid #f1f5f9;}.part-info-row>i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}</style>
        <script>(function(){var b=document.querySelector('.part-info-btn'),t=document.querySelector('.part-info-tooltip');if(!b||!t)return;b.addEventListener('click',function(e){e.stopPropagation();t.classList.toggle('is-open');});document.addEventListener('click',function(){t.classList.remove('is-open');});})();</script>

        <?php if ($flashMsg): ?>
        <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible" role="alert">
            <?= e($flashMsg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="part-grid">

            <!-- Formulaire -->
            <div class="part-card">
                <h3>
                    <i class="fas fa-<?= $editing ? 'pen' : 'plus-circle' ?>" style="color:#f59e0b"></i>
                    <?= $editing ? 'Modifier le partenaire' : 'Ajouter un partenaire' ?>
                </h3>
                <form method="post">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
                    <div class="part-form">
                        <input name="nom"          placeholder="Nom *"           value="<?= e((string) ($editing['nom']          ?? '')) ?>" required>
                        <input name="slug"         placeholder="Slug (URL)"      value="<?= e((string) ($editing['slug']         ?? '')) ?>">
                        <select name="categorie_id">
                            <option value="">Catégorie</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>" <?= (int) ($editing['categorie_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= e((string) $cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input name="adresse"      placeholder="Adresse"         value="<?= e((string) ($editing['adresse']      ?? '')) ?>">
                        <input name="ville"        placeholder="Ville"           value="<?= e((string) ($editing['ville']        ?? '')) ?>">
                        <input name="code_postal"  placeholder="Code postal"     value="<?= e((string) ($editing['code_postal']  ?? '')) ?>">
                        <input name="telephone"    placeholder="Téléphone"       value="<?= e((string) ($editing['telephone']    ?? '')) ?>">
                        <input name="site_web"     placeholder="Site web"        value="<?= e((string) ($editing['site_web']     ?? '')) ?>">
                        <input name="logo"         placeholder="Logo (URL)"      value="<?= e((string) ($editing['logo']         ?? '')) ?>">
                        <input name="latitude"     placeholder="Latitude"        value="<?= e((string) ($editing['latitude']     ?? '')) ?>">
                        <input name="longitude"    placeholder="Longitude"       value="<?= e((string) ($editing['longitude']    ?? '')) ?>">
                        <input name="google_maps_url" placeholder="URL Google Maps" value="<?= e((string) ($editing['google_maps_url'] ?? '')) ?>">
                        <textarea name="description_courte" placeholder="Description courte"><?= e((string) ($editing['description_courte'] ?? '')) ?></textarea>
                        <textarea name="description_longue" placeholder="Description longue" style="min-height:100px;"><?= e((string) ($editing['description_longue'] ?? '')) ?></textarea>
                        <div style="grid-column:1/-1">
                            <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;cursor:pointer">
                                <input type="checkbox" name="statut_actif" value="1" <?= !isset($editing['statut_actif']) || (int) $editing['statut_actif'] === 1 ? 'checked' : '' ?>>
                                Partenaire actif (visible sur le site)
                            </label>
                        </div>
                        <div class="part-form-actions">
                            <button class="hub-btn hub-btn--gold" type="submit">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                            <?php if ($editing): ?>
                                <a class="hub-btn" style="background:#f1f5f9;color:#334155;" href="/admin?module=partenaires">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Liste -->
            <div class="part-card">
                <h3><i class="fas fa-list" style="color:#3b82f6"></i> Partenaires enregistrés (<?= count($partners) ?>)</h3>
                <?php if (!$partners): ?>
                    <div class="part-empty">
                        <i class="fas fa-handshake fa-2x" style="opacity:.2;display:block;margin-bottom:.5rem"></i>
                        <div style="font-size:.88rem">Aucun partenaire pour le moment.</div>
                        <div style="font-size:.82rem;margin-top:.3rem">Utilisez le formulaire ci-dessus pour en ajouter un.</div>
                    </div>
                <?php else: ?>
                    <div class="part-table-wrap">
                        <table class="part-table">
                            <thead>
                            <tr>
                                <th>Nom</th><th>Catégorie</th><th>Ville</th><th>Statut</th><th style="text-align:right">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($partners as $partner): ?>
                                <tr>
                                    <td><strong><?= e((string) $partner['nom']) ?></strong></td>
                                    <td><?= e((string) ($partner['categorie'] ?? '—')) ?></td>
                                    <td><?= e(trim((string) (($partner['code_postal'] ?? '') . ' ' . ($partner['ville'] ?? '')))) ?></td>
                                    <td>
                                        <?php if ((int) ($partner['statut_actif'] ?? 0) === 1): ?>
                                            <span class="part-badge-active"><i class="fas fa-circle" style="font-size:.5rem"></i> Actif</span>
                                        <?php else: ?>
                                            <span class="part-badge-inactive">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;white-space:nowrap">
                                        <a href="/admin?module=partenaires&edit=<?= (int) $partner['id'] ?>" class="part-link">Modifier</a>
                                        <form method="post" style="display:inline-block;margin-left:.6rem" onsubmit="return confirm('Supprimer ce partenaire ?')">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int) $partner['id'] ?>">
                                            <button type="submit" class="part-delete-btn">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
    <?php
}
