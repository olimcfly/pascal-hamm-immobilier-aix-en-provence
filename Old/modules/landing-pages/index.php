<?php
$pageTitle = 'Landing Pages Ads';
$pageDescription = 'Créez des LP conformes Google Ads pour estimation et financement.';

$pdo = db();
$pdo->exec('CREATE TABLE IF NOT EXISTS landing_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    website_id INT UNSIGNED NOT NULL DEFAULT 1,
    slug VARCHAR(160) NOT NULL,
    type ENUM("estimation", "financement") NOT NULL DEFAULT "estimation",
    headline VARCHAR(255) NOT NULL,
    sous_titre VARCHAR(255) NOT NULL,
    ville VARCHAR(120) NOT NULL,
    advisor_name VARCHAR(180) NOT NULL DEFAULT "",
    advisor_phone VARCHAR(40) NOT NULL DEFAULT "",
    advisor_zone VARCHAR(255) NOT NULL DEFAULT "",
    advisor_photo_webp VARCHAR(255) NOT NULL DEFAULT "",
    advisor_bio TEXT NULL,
    company_name VARCHAR(180) NOT NULL DEFAULT "",
    legal_url VARCHAR(255) NOT NULL DEFAULT "/mentions-legales",
    privacy_url VARCHAR(255) NOT NULL DEFAULT "/politique-confidentialite",
    review_1_firstname VARCHAR(80) NULL,
    review_1_city VARCHAR(120) NULL,
    review_1_text VARCHAR(255) NULL,
    review_2_firstname VARCHAR(80) NULL,
    review_2_city VARCHAR(120) NULL,
    review_2_text VARCHAR(255) NULL,
    utm_source_default VARCHAR(40) NOT NULL DEFAULT "google",
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_landing_page_slug (website_id, slug),
    KEY idx_landing_page_active (website_id, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

$websiteId = 1;
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $editId > 0;
$errors = [];

$defaults = [
    'slug' => '', 'type' => 'estimation', 'headline' => '', 'sous_titre' => '', 'ville' => '',
    'advisor_name' => trim((string)(setting('advisor_firstname', '') . ' ' . setting('advisor_lastname', ''))),
    'advisor_phone' => (string)setting('advisor_phone', ''),
    'advisor_zone' => (string)setting('zone_city', ''),
    'advisor_photo_webp' => '', 'advisor_bio' => (string)setting('advisor_bio', ''),
    'company_name' => (string)setting('agency_name', ''),
    'legal_url' => '/mentions-legales', 'privacy_url' => '/politique-confidentialite',
    'review_1_firstname' => '', 'review_1_city' => '', 'review_1_text' => '',
    'review_2_firstname' => '', 'review_2_city' => '', 'review_2_text' => '',
    'utm_source_default' => 'google', 'active' => 1,
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM landing_pages WHERE id = ? AND website_id = ? LIMIT 1');
    $stmt->execute([$editId, $websiteId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $defaults = array_merge($defaults, $row);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $defaults;
    foreach (array_keys($defaults) as $key) {
        if ($key === 'active') {
            $data[$key] = !empty($_POST['active']) ? 1 : 0;
            continue;
        }
        if (!isset($_POST[$key])) {
            continue;
        }
        $data[$key] = trim((string)$_POST[$key]);
    }

    $data['slug'] = strtolower((string)preg_replace('/[^a-z0-9-]+/', '-', (string)$data['slug']));
    $data['slug'] = trim((string)$data['slug'], '-');

    if ($data['slug'] === '') {
        $errors[] = 'Le slug est obligatoire.';
    }
    if (!in_array($data['type'], ['estimation', 'financement'], true)) {
        $errors[] = 'Le type doit être estimation ou financement.';
    }
    if ($data['headline'] === '' || $data['sous_titre'] === '') {
        $errors[] = 'Headline et sous-titre sont obligatoires.';
    }
    if (!in_array($data['utm_source_default'], ['google', 'facebook'], true)) {
        $errors[] = 'UTM source par défaut invalide.';
    }

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE landing_pages SET
                slug=:slug, type=:type, headline=:headline, sous_titre=:sous_titre, ville=:ville,
                advisor_name=:advisor_name, advisor_phone=:advisor_phone, advisor_zone=:advisor_zone, advisor_photo_webp=:advisor_photo_webp,
                advisor_bio=:advisor_bio, company_name=:company_name, legal_url=:legal_url, privacy_url=:privacy_url,
                review_1_firstname=:review_1_firstname, review_1_city=:review_1_city, review_1_text=:review_1_text,
                review_2_firstname=:review_2_firstname, review_2_city=:review_2_city, review_2_text=:review_2_text,
                utm_source_default=:utm_source_default, active=:active
                WHERE id=:id AND website_id=:website_id';
            $data['id'] = $editId;
        } else {
            $sql = 'INSERT INTO landing_pages
                (website_id, slug, type, headline, sous_titre, ville, advisor_name, advisor_phone, advisor_zone, advisor_photo_webp, advisor_bio, company_name, legal_url, privacy_url,
                 review_1_firstname, review_1_city, review_1_text, review_2_firstname, review_2_city, review_2_text, utm_source_default, active)
                VALUES
                (:website_id, :slug, :type, :headline, :sous_titre, :ville, :advisor_name, :advisor_phone, :advisor_zone, :advisor_photo_webp, :advisor_bio, :company_name, :legal_url, :privacy_url,
                 :review_1_firstname, :review_1_city, :review_1_text, :review_2_firstname, :review_2_city, :review_2_text, :utm_source_default, :active)';
        }

        $data['website_id'] = $websiteId;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        redirect('/admin?module=landing-pages&saved=1');
    }

    $defaults = $data;
}

$listStmt = $pdo->prepare('SELECT id, slug, type, headline, ville, active, updated_at FROM landing_pages WHERE website_id = ? ORDER BY updated_at DESC');
$listStmt->execute([$websiteId]);
$landingPages = $listStmt->fetchAll(PDO::FETCH_ASSOC);

function renderContent(): void
{
    global $landingPages, $defaults, $errors, $isEdit;
    ?>
    <style>
    .lp-layout{display:grid;gap:1.2rem;grid-template-columns:1fr 1.1fr;align-items:start}
    .lp-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.2rem 1.4rem;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
    .lp-card h3{margin:0 0 1rem;font-size:1rem;color:#0f172a;display:flex;align-items:center;gap:.4rem}
    .lp-table{width:100%;border-collapse:collapse}
    .lp-table th,.lp-table td{padding:.6rem .7rem;border-bottom:1px solid #f1f5f9;text-align:left;font-size:.86rem}
    .lp-table th{font-size:.73rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:700;background:#fafbfc}
    .lp-table tr:last-child td{border-bottom:none}
    .lp-form{display:grid;gap:.6rem}
    .lp-form-field{display:grid;gap:.25rem}
    .lp-form-field label{font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em}
    .lp-form-field input,.lp-form-field select,.lp-form-field textarea{border:1px solid #cbd5e1;border-radius:10px;padding:.55rem .7rem;font-size:.88rem;width:100%}
    .lp-form-2col{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem}
    .lp-form-actions{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-top:.4rem}
    .lp-badge-active{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .5rem;border-radius:999px;font-size:.73rem;font-weight:700;background:#dcfce7;color:#166534}
    .lp-badge-inactive{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .5rem;border-radius:999px;font-size:.73rem;font-weight:700;background:#f1f5f9;color:#64748b}
    .lp-link{color:#1d4ed8;text-decoration:none;font-size:.84rem;font-weight:600}
    .lp-link:hover{text-decoration:underline}
    .lp-error{color:#b91c1c;font-size:.86rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.5rem .7rem;margin-bottom:.5rem}
    .lp-empty{padding:2.5rem 1rem;text-align:center;color:#94a3b8}
    @media(max-width:1000px){.lp-layout{grid-template-columns:1fr}.lp-form-2col{grid-template-columns:1fr}}
    </style>

    <div class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-file-lines"></i> Pages & formulaires</div>
            <h1>Landing Pages Google Ads</h1>
            <p>Créez des pages de capture conformes RGPD, optimisées pour le Quality Score et prêtes pour vos campagnes publicitaires.</p>
        </header>

        <div class="lp-info-wrap">
            <button class="lp-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
                <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
            </button>
            <div class="lp-info-tooltip" role="tooltip">
                <div class="lp-info-row">
                    <i class="fas fa-bullseye" style="color:#3b82f6"></i>
                    <div><strong>Pourquoi une LP dédiée</strong><br>Une landing page cohérente avec votre annonce améliore le Quality Score Google Ads, réduit votre coût par clic et augmente les conversions.</div>
                </div>
                <div class="lp-info-row">
                    <i class="fas fa-check-circle" style="color:#10b981"></i>
                    <div><strong>Ce que vous créez</strong><br>Chaque page est autonome, avec votre identité, vos avis clients, votre formulaire et vos mentions légales — prête en quelques minutes.</div>
                </div>
                <div class="lp-info-row">
                    <i class="fas fa-link" style="color:#f59e0b"></i>
                    <div><strong>Comment l'utiliser</strong><br>Copiez l'URL <code>/lp/votre-slug</code> dans vos campagnes Google Ads ou Meta Ads comme URL de destination.</div>
                </div>
            </div>
        </div>
        <style>
        .lp-info-wrap { position:relative; display:inline-block; margin-bottom:1.25rem; }
        .lp-info-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:.4rem .85rem; font-size:.85rem; color:#64748b; cursor:pointer; display:inline-flex; align-items:center; gap:.45rem; transition:background .15s,color .15s; }
        .lp-info-btn:hover { background:#f1f5f9; color:#334155; }
        .lp-info-tooltip { display:none; position:absolute; top:calc(100% + 8px); left:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); padding:1rem 1.1rem; width:420px; max-width:90vw; }
        .lp-info-tooltip.is-open { display:block; }
        .lp-info-row { display:flex; gap:.75rem; align-items:flex-start; padding:.55rem 0; font-size:.84rem; line-height:1.45; color:#374151; }
        .lp-info-row + .lp-info-row { border-top:1px solid #f1f5f9; }
        .lp-info-row > i { margin-top:2px; flex-shrink:0; width:16px; text-align:center; }
        </style>
        <script>
        (function () {
            var btn = document.querySelector('.lp-info-btn');
            var tip = document.querySelector('.lp-info-tooltip');
            if (!btn || !tip) return;
            btn.addEventListener('click', function (e) { e.stopPropagation(); tip.classList.toggle('is-open'); });
            document.addEventListener('click', function () { tip.classList.remove('is-open'); });
        })();
        </script>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="lp-error"><i class="fas fa-circle-exclamation me-2"></i><?= e((string) $error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="lp-layout">

            <!-- Liste des LP existantes -->
            <div class="lp-card">
                <h3><i class="fas fa-list" style="color:#3b82f6"></i> Pages existantes (<?= count($landingPages) ?>)</h3>
                <?php if (!$landingPages): ?>
                    <div class="lp-empty">
                        <i class="fas fa-file-lines fa-2x" style="opacity:.2;display:block;margin-bottom:.5rem"></i>
                        <div style="font-size:.88rem">Aucune landing page créée.</div>
                        <div style="font-size:.82rem;margin-top:.3rem">Utilisez le formulaire ci-contre pour en créer une.</div>
                    </div>
                <?php else: ?>
                    <table class="lp-table">
                        <thead><tr><th>Slug / URL</th><th>Type</th><th>Ville</th><th>Statut</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($landingPages as $lp): ?>
                            <tr>
                                <td><code style="font-size:.78rem;background:#f1f5f9;padding:.1rem .4rem;border-radius:4px">/lp/<?= e((string) $lp['slug']) ?></code></td>
                                <td><?= e((string) $lp['type']) ?></td>
                                <td><?= e((string) $lp['ville']) ?></td>
                                <td><?= ((int) $lp['active'] === 1) ? '<span class="lp-badge-active">Active</span>' : '<span class="lp-badge-inactive">Inactive</span>' ?></td>
                                <td><a href="/admin?module=landing-pages&id=<?= (int) $lp['id'] ?>" class="lp-link">Éditer</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php if ($isEdit): ?>
                    <div style="margin-top:.8rem">
                        <a href="/admin?module=landing-pages" class="hub-btn hub-btn--sm" style="background:#f1f5f9;color:#334155;">
                            <i class="fas fa-plus"></i> Nouvelle LP
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulaire -->
            <div class="lp-card">
                <h3>
                    <i class="fas fa-<?= $isEdit ? 'pen' : 'plus-circle' ?>" style="color:#f59e0b"></i>
                    <?= $isEdit ? 'Modifier la landing page' : 'Créer une landing page' ?>
                </h3>
                <form class="lp-form" method="post">
                    <div class="lp-form-2col">
                        <div class="lp-form-field">
                            <label>Slug *</label>
                            <input name="slug" required value="<?= e((string) $defaults['slug']) ?>" placeholder="estimation-aix">
                        </div>
                        <div class="lp-form-field">
                            <label>Type *</label>
                            <select name="type" required>
                                <option value="estimation"  <?= $defaults['type'] === 'estimation'  ? 'selected' : '' ?>>Estimation</option>
                                <option value="financement" <?= $defaults['type'] === 'financement' ? 'selected' : '' ?>>Financement</option>
                            </select>
                        </div>
                    </div>
                    <div class="lp-form-field"><label>Headline H1 *</label><input name="headline" required value="<?= e((string) $defaults['headline']) ?>"></div>
                    <div class="lp-form-field"><label>Sous-titre *</label><input name="sous_titre" required value="<?= e((string) $defaults['sous_titre']) ?>"></div>
                    <div class="lp-form-field"><label>Ville</label><input name="ville" value="<?= e((string) $defaults['ville']) ?>"></div>
                    <div class="lp-form-2col">
                        <div class="lp-form-field"><label>Nom conseiller</label><input name="advisor_name" value="<?= e((string) $defaults['advisor_name']) ?>"></div>
                        <div class="lp-form-field"><label>Téléphone</label><input name="advisor_phone" value="<?= e((string) $defaults['advisor_phone']) ?>"></div>
                    </div>
                    <div class="lp-form-field"><label>Zone d'intervention</label><input name="advisor_zone" value="<?= e((string) $defaults['advisor_zone']) ?>"></div>
                    <div class="lp-form-field"><label>Photo conseiller (URL WebP)</label><input name="advisor_photo_webp" value="<?= e((string) $defaults['advisor_photo_webp']) ?>"></div>
                    <div class="lp-form-field"><label>Bio courte</label><textarea rows="2" name="advisor_bio"><?= e((string) $defaults['advisor_bio']) ?></textarea></div>
                    <div class="lp-form-2col">
                        <div class="lp-form-field"><label>Avis 1 — Prénom</label><input name="review_1_firstname" value="<?= e((string) $defaults['review_1_firstname']) ?>"></div>
                        <div class="lp-form-field"><label>Avis 1 — Ville</label><input name="review_1_city" value="<?= e((string) $defaults['review_1_city']) ?>"></div>
                    </div>
                    <div class="lp-form-field"><label>Avis 1 — Texte</label><textarea rows="2" name="review_1_text"><?= e((string) $defaults['review_1_text']) ?></textarea></div>
                    <div class="lp-form-2col">
                        <div class="lp-form-field"><label>Avis 2 — Prénom</label><input name="review_2_firstname" value="<?= e((string) $defaults['review_2_firstname']) ?>"></div>
                        <div class="lp-form-field"><label>Avis 2 — Ville</label><input name="review_2_city" value="<?= e((string) $defaults['review_2_city']) ?>"></div>
                    </div>
                    <div class="lp-form-field"><label>Avis 2 — Texte</label><textarea rows="2" name="review_2_text"><?= e((string) $defaults['review_2_text']) ?></textarea></div>
                    <div class="lp-form-2col">
                        <div class="lp-form-field">
                            <label>UTM source par défaut</label>
                            <select name="utm_source_default">
                                <option value="google"   <?= $defaults['utm_source_default'] === 'google'   ? 'selected' : '' ?>>google</option>
                                <option value="facebook" <?= $defaults['utm_source_default'] === 'facebook' ? 'selected' : '' ?>>facebook</option>
                            </select>
                        </div>
                        <div class="lp-form-field" style="justify-content:flex-end;align-items:flex-end">
                            <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.88rem;text-transform:none;letter-spacing:0">
                                <input type="checkbox" name="active" value="1" <?= !empty($defaults['active']) ? 'checked' : '' ?>>
                                Page active (visible)
                            </label>
                        </div>
                    </div>
                    <div class="lp-form-actions">
                        <button class="hub-btn hub-btn--gold" type="submit">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <?php if ($isEdit): ?>
                            <a href="/admin?module=landing-pages" class="hub-btn" style="background:#f1f5f9;color:#334155;">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>

    </div>
    <?php
}
