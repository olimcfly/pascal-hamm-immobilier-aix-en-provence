<?php
$metaDesc  = 'Aix-en-Provence et Pays d’Aix (13) : commerces de proximité, annuaire par ville, carte des adresses et fiches pratiques. ' . ADVISOR_NAME . ', même exigence de clarté que sur l’immobilier.';
$extraCss  = [
    '/assets/css/financement.css',
    '/assets/css/guide.css',
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css',
];
$extraJs   = [
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js',
    '/assets/js/guide-local-map.js',
];
$pageTitle = 'Guide local — Aix & Pays d’Aix (13) | ' . APP_NAME;

$communesRayon10 = [
    ['nom' => 'Gardanne', 'cp' => '13120', 'note' => 'Pôle résidentiel et tertiaire au nord, bien relié à Aix.'],
    ['nom' => 'Vitrolles', 'cp' => '13127', 'note' => 'Desserte vers l’aéroport Marseille-Provence et l’emploi local.'],
    ['nom' => 'Les Pennes-Mirabeau', 'cp' => '13170', 'note' => 'Commune résidentielle entre Aix et l’étang de Berre.'],
    ['nom' => 'Bouc-Bel-Air', 'cp' => '13320', 'note' => 'Maisons et lotissements, cadre familial à l’ouest d’Aix.'],
    ['nom' => 'Cabriès', 'cp' => '13440', 'note' => 'Calme, villas et commerces de centre village.'],
    ['nom' => 'Éguilles', 'cp' => '13510', 'note' => 'Secteur prisé, chemin des petites routes vers Aix.'],
    ['nom' => 'Meyreuil', 'cp' => '13590', 'note' => 'Pied de la Sainte-Victoire, nature et familles.'],
    ['nom' => 'Rousset', 'cp' => '13790', 'note' => 'Sous le massif, maisons et vue, proximité Aix.'],
    ['nom' => 'Berre-l’Étang', 'cp' => '13130', 'note' => 'Nord de l’étang, prix souvent accessibles, desserte vers Aix.'],
];

$secteursAix = [
    'Centre-ville & Cours',
    'Mazarin',
    'Puyricard',
    'Jas-de-Bouffan',
    'Les Milles',
    'Luynes',
];

$communesProches = [
    ['nom' => 'Salon-de-Provence', 'cp' => '13300', 'note' => 'Grande ville du nord des Bouches-du-Rhône, praticité et services.'],
    ['nom' => 'Arles', 'cp' => '13200', 'note' => 'Projet de vie patrimoine / Camargue, trajet domicile—travail à anticiper.'],
    ['nom' => 'Martigues', 'cp' => '13500', 'note' => 'Côté étang, liaison autoroutière et cadre de vie du littoral.'],
    ['nom' => 'Istres', 'cp' => '13800', 'note' => 'Emploi et logements, ouverture vers la Crau et l’arrière-pays.'],
];

$baseUrl  = rtrim((string) (defined('APP_URL') ? APP_URL : ''), '/');
$countPoi = 0;
$countVil = 0;
$poisVedette = [];
if (function_exists('db')) {
    try {
        $pdo   = db();
        $countVil = (int) $pdo->query('SELECT COUNT(*) FROM villes WHERE actif = 1')->fetchColumn();
    } catch (Throwable $e) {
        $countVil = 0;
    }
    try {
        $pdoC = db();
        $pdoC->query('SELECT 1 FROM guide_pois LIMIT 1');
        $countPoi = (int) $pdoC->query('SELECT COUNT(*) FROM guide_pois WHERE is_active = 1')->fetchColumn();
        $stP = $pdoC->query(
            'SELECT p.name, p.slug, p.description, p.featured_image, c.name AS cat_name,
                    COALESCE(v.slug, vq.slug) AS ville_slug, COALESCE(v.nom, vq.nom) AS ville_nom
             FROM guide_pois p
             INNER JOIN guide_poi_categories c ON c.id = p.category_id AND c.is_active = 1
             LEFT JOIN villes v ON v.id = p.ville_id
             LEFT JOIN quartiers q ON q.id = p.quartier_id
             LEFT JOIN villes vq ON vq.id = q.ville_id
             WHERE p.is_active = 1 AND COALESCE(v.slug, vq.slug) IS NOT NULL AND COALESCE(v.slug, vq.slug) != \'\'
             ORDER BY p.updated_at DESC
             LIMIT 9'
        );
        if ($stP) {
            $poisVedette = $stP->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Throwable $e) {
        $countPoi    = 0;
        $poisVedette = [];
    }
}

$guidesParVille = [];
try {
    if (function_exists('db')) {
        $st = db()->query(
            'SELECT id, nom, slug, code_postal, description, image_url FROM villes WHERE actif = 1 ORDER BY ordre ASC, nom ASC'
        );
        if ($st) {
            $guidesParVille = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    }
} catch (Throwable $e) {
    $guidesParVille = [];
}

$excerptPoi = static function (?string $html, int $len = 130): string {
    $s = trim(strip_tags((string) $html));
    if ($s === '') {
        return '';
    }
    if (mb_strlen($s, 'UTF-8') > $len) {
        return mb_substr($s, 0, $len, 'UTF-8') . '…';
    }

    return $s;
};
?>
<div class="guide-local-index">
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane"><a href="/">Accueil</a><span>Guide local</span></nav>
        <h1>Annuaire local, commerces et services de proximité</h1>
        <p>Repérez des adresses utiles sur Aix-en-Provence, le Pays d’Aix et les communes alentour : par ville, sur la carte,
            et en fiche détaillée pour chaque commerçant enregistré. Une présentation sobre, dans la continuité de <?= e(APP_NAME) ?>.</p>
        <div class="fin-hero-actions">
            <?php if ($poisVedette !== []): ?>
            <a href="#commercants-vedette" class="btn btn--accent btn--lg">Voir la sélection</a>
            <?php else: ?>
            <a href="#guides-par-ville" class="btn btn--accent btn--lg">Explorer par ville</a>
            <?php endif; ?>
            <a href="#map-section" class="btn btn--outline-white btn--lg">Carte interactive</a>
        </div>
        <p class="fin-hero-trust">Fiches mises à jour &middot; Carte par ville, quartier et activité &middot; Même identité visuelle que le reste du site</p>
    </div>
</div>

<section class="section section--guide-tiles" aria-label="Fonctionnalités du guide">
    <div class="container">
        <div class="grid-4 fin-reassurance guide-local-tiles" data-animate>
            <article class="fin-card">
                <h2>Par commune</h2>
                <p>Choisissez une municipalité pour lire l’intro et accéder aux fiches du secteur.</p>
            </article>
            <article class="fin-card">
                <h2>Par besoin</h2>
                <p>Filtrez alimentation, services, commerces de proximité selon l’annuaire.</p>
            </article>
            <article class="fin-card">
                <h2>Sur la carte</h2>
                <p>Visualisez les points, zoomez, puis ouvrez la fiche liée.</p>
            </article>
            <article class="fin-card">
                <h2>Pour s’installer</h2>
                <p>En appui de votre recherche : anticiper le quotidien du quartier avant de vous y installer.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container fin-columns" data-animate>
        <div>
            <span class="section-label">À quoi sert ce guide</span>
            <h2 class="section-title">Commerces de proximité, présentation unifiée</h2>
            <p class="section-subtitle">Cette page n’est pas un comparatif de prix : c’est un répertoire pratique. Les
                fiches s’inscrivent dans le même cadrage graphique que vos lectures sur le site (estimation, financement,
                secteurs), pour ne pas mélanger styles et publicités.</p>
        </div>
        <ul class="fin-list">
            <li>Recherche guidée : ville, quartier, type d’activité.</li>
            <li>Contenu : adresse, catégorie, description courte, lien vers la fiche complète.</li>
            <li>Évolution : les fiches s’enrichissent ; la carte et les listes se synchronisent.</li>
        </ul>
    </div>
</section>

<?php if ($poisVedette !== []): ?>
<section class="section" id="commercants-vedette" style="padding-top:0">
    <div class="container">
        <div class="section__header text-center" style="margin-bottom:1.5rem">
            <span class="section-label">Sélection</span>
            <h2 class="section-title">Dernières fiches publiées</h2>
            <p class="section-subtitle" style="max-width:40rem;margin-left:auto;margin-right:auto">Extraits issus de l’annuaire. Ouvrez une fiche pour l’adresse, le contact et le détail.</p>
        </div>
        <div class="blog-grid guide-local-merchants" data-animate>
            <?php foreach ($poisVedette as $pv):
                $pSlug  = (string) ($pv['slug'] ?? '');
                $vSlug  = (string) ($pv['ville_slug'] ?? '');
                if ($pSlug === '' || $vSlug === '') {
                    continue;
                }
                $ficheUrl  = '/commerces/' . rawurlencode($vSlug) . '/' . rawurlencode($pSlug);
                $img       = trim((string) ($pv['featured_image'] ?? ''));
                if ($img !== '' && $img[0] === '/') {
                    $img = $baseUrl . $img;
                }
                $blurb = $excerptPoi($pv['description'] ?? null, 125);
                if ($blurb === '') {
                    $blurb = 'Fiche commerçant : horaires, contact et itinéraire sur la page détaillée.';
                }
                ?>
            <article class="article-card">
                <?php if ($img !== ''): ?>
                <a href="<?= e($ficheUrl) ?>" class="article-card__img" aria-label="<?= e((string) $pv['name']) ?> — voir la fiche">
                    <img src="<?= e($img) ?>" alt="" loading="lazy" width="400" height="225">
                </a>
                <?php else: ?>
                <a href="<?= e($ficheUrl) ?>" class="article-card__img article-card__img--placeholder" aria-label="<?= e((string) $pv['name']) ?>">Image à venir</a>
                <?php endif; ?>
                <div class="article-card__body">
                    <span class="article-card__cat"><?= e((string) ($pv['cat_name'] ?? 'Commerçant')) ?> · <?= e((string) ($pv['ville_nom'] ?? $vSlug)) ?></span>
                    <h2 class="article-card__title"><a href="<?= e($ficheUrl) ?>"><?= e((string) $pv['name']) ?></a></h2>
                    <p class="article-card__excerpt"><?= e($blurb) ?></p>
                    <div class="article-card__meta">
                        <span class="sr-only"><?= e((string) $pv['ville_nom']) ?></span>
                        <a href="<?= e($ficheUrl) ?>" class="article-card__readmore">Voir la fiche</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="text-center" style="margin:1.5rem 0 0;font-size:.9rem">
            <a href="#map-section" class="btn btn--primary">Afficher la carte de l’annuaire</a>
        </p>
    </div>
</section>
<?php endif; ?>

<?php if ($guidesParVille !== []): ?>
<section class="section" id="guides-par-ville" style="padding-top:0">
    <div class="container">
        <div class="section__header text-center" style="margin-bottom:1.5rem">
            <span class="section-label">Par commune</span>
            <h2 class="section-title">Guides par ville</h2>
            <p class="section-subtitle" style="max-width:42rem;margin-left:auto;margin-right:auto">Chaque fiche comporte un texte d’introduction, une visuelle de couverture le cas échéant, puis
                l’annuaire des fiches (commerces et services) pour cette municipalité.</p>
        </div>
        <div class="comparatif-cards comparatif-cards--visible" data-animate style="justify-content:center">
            <?php
            foreach ($guidesParVille as $gv):
                $gImg = trim((string) ($gv['image_url'] ?? ''));
                if ($gImg !== '' && $gImg[0] === '/') {
                    $gImg = $baseUrl . $gImg;
                }
                $gIntro = trim(strip_tags((string) ($gv['description'] ?? '')));
                if (mb_strlen($gIntro, 'UTF-8') > 120) {
                    $gIntro = mb_substr($gIntro, 0, 117, 'UTF-8') . '…';
                }
                $gSlug = (string) ($gv['slug'] ?? '');
                if ($gSlug === '') {
                    continue;
                }
                $gUrl = '/guide-local/annuaire/' . rawurlencode($gSlug);
                ?>
                <a href="<?= e($gUrl) ?>" class="comparatif-card" style="text-decoration:none;color:inherit;display:flex;flex-direction:column;cursor:pointer;padding:0;overflow:hidden;min-height:100%">
                    <div class="comparatif-card__media" style="height:11rem;background:#e2e8f0;position:relative;flex-shrink:0">
                        <?php if ($gImg !== ''): ?>
                            <img src="<?= e($gImg) ?>" alt="" width="360" height="176" style="width:100%;height:100%;object-fit:cover" loading="lazy" decoding="async">
                        <?php else:
                            $ini = mb_strtoupper(mb_substr((string) ($gv['nom'] ?? ''), 0, 1, 'UTF-8'), 'UTF-8');
                            ?>
                            <div class="comparatif-card__media--empty" style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--clr-bg)" aria-hidden="true">
                                <span class="guide-local-ville-ph"><?= e($ini !== '' ? $ini : '?') ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="comparatif-card__body" style="padding:1.1rem 1.1rem 1.25rem;flex:1;display:flex;flex-direction:column">
                        <div class="comparatif-card__nom" style="margin-top:0"><?= e((string) ($gv['nom'] ?? '')) ?><?php if (!empty($gv['code_postal'])): ?> <span style="font-weight:500;color:#64748b">(<?= e((string) $gv['code_postal']) ?>)</span><?php endif; ?></div>
                        <p class="comparatif-card__lede" style="margin:0.35rem 0 0;font-size:0.875rem;line-height:1.5;color:var(--clr-text-muted)"><?= $gIntro !== '' ? e($gIntro) : 'Ouvrez le guide : commerçants, artisans et services de la commune.' ?></p>
                        <span class="section-label" style="margin-top:10px;display:inline-block">Ouvrir le guide</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section guide-local-map-section" id="map-section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Carte interactive</span>
            <h2 class="section-title">Points d’intérêt autour de vous</h2>
            <p class="section-subtitle">Filtrez par ville, quartier et catégorie : les données viennent du même annuaire que les fiches ci-dessus.</p>
        </div>

        <div class="guide-local-map-block">
            <div class="guide-local-filters-card" role="region" aria-label="Filtres de la carte">
                <p class="guide-local-filters-card__kicker">Affiner l’annuaire</p>
                <div class="guide-local-filters">
                    <div class="guide-local-filters__field">
                        <label for="gl-city">Ville</label>
                        <div class="guide-local-filters__select-wrap">
                            <select id="gl-city" class="guide-local-filters__select"></select>
                        </div>
                    </div>
                    <div class="guide-local-filters__field">
                        <label for="gl-district">Quartier</label>
                        <div class="guide-local-filters__select-wrap">
                            <select id="gl-district" class="guide-local-filters__select" disabled>
                                <option value="">Tous les quartiers</option>
                            </select>
                        </div>
                    </div>
                    <div class="guide-local-filters__field">
                        <label for="gl-category">Catégorie</label>
                        <div class="guide-local-filters__select-wrap">
                            <select id="gl-category" class="guide-local-filters__select">
                                <option value="">Toutes les catégories</option>
                            </select>
                        </div>
                    </div>
                    <div class="guide-local-filters__actions">
                        <button type="button" class="btn btn--primary guide-local-filters__btn" id="gl-apply">
                            <span>Actualiser</span>
                        </button>
                    </div>
                </div>
            </div>

            <p class="guide-local-map__status" id="gl-status" role="status" aria-live="polite"></p>

            <div class="guide-local-map-layout">
                <div class="guide-local-map__col guide-local-map__col--map">
                    <div id="guideLocalMap" class="guide-local-map__canvas" aria-label="Carte des points d’intérêt"></div>
                </div>
                <aside class="guide-local-sidebar" aria-label="Liste des lieux">
                    <div class="guide-local-sidebar__head">
                        <h3 class="guide-local-sidebar__title">Lieux</h3>
                        <p class="guide-local-sidebar__hint">Clic sur la carte pour le détail</p>
                    </div>
                    <ul class="guide-local-sidebar__list" id="gl-list"></ul>
                </aside>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Sélection locale</span>
            <h2 class="section-title">Communes autour d’Aix-en-Provence</h2>
            <p class="section-subtitle">Exemples de communes fréquemment recherchées sur le Pays d’Aix et le nord des Bouches-du-Rhône (hors exhaustif).</p>
        </div>

        <div class="comparatif-cards comparatif-cards--visible" data-animate>
            <?php foreach ($communesRayon10 as $commune): ?>
                <article class="comparatif-card" style="cursor:default">
                    <div class="comparatif-card__nom"><?= e($commune['nom']) ?> (<?= e($commune['cp']) ?>)</div>
                    <div class="comparatif-card__row"><strong><?= e($commune['note']) ?></strong></div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="comparatif-section" data-animate>
            <h2 class="section-title">Quartiers &amp; faubourgs d’Aix</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">
                <strong>Centre, Mazarin, Puyricard, Jas-de-Bouffan, Milles, Luynes</strong> : autant de typologies de marché — du centre historique au village associé, en passant par les pôles tertiaires.
            </p>
            <div class="comparatif-cards comparatif-cards--visible">
                <?php foreach ($secteursAix as $secteur): ?>
                    <article class="comparatif-card" style="cursor:default">
                        <div class="comparatif-card__nom"><?= e($secteur) ?></div>
                        <div class="comparatif-card__row"><strong>Secteur d’Aix (repère fréquent)</strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="comparatif-section" data-animate>
            <h2 class="section-title">Élargir le projet (au-delà du Pays d’Aix)</h2>
            <p class="section-subtitle" style="margin-bottom:1rem">D’autres villes des Bouches-du-Rhône ou limitrophes entrent en jeu selon l’emploi, la famille ou le budget : utile pour cadrer les temps de trajet.</p>
            <div class="comparatif-cards comparatif-cards--visible">
                <?php foreach ($communesProches as $commune): ?>
                    <article class="comparatif-card" style="cursor:default">
                        <div class="comparatif-card__nom"><?= e($commune['nom']) ?> (<?= e($commune['cp']) ?>)</div>
                        <div class="comparatif-card__row"><strong><?= e($commune['note']) ?></strong></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="blog-cta" data-animate>
            <div>
                <h3>Vous cherchez dans une commune précise ?</h3>
                <p>Parlez de votre projet avec <?= e(ADVISOR_NAME) ?> et obtenez une orientation personnalisée selon votre budget, votre style de vie et vos délais.</p>
            </div>
            <a href="/contact" class="btn btn--accent">Prendre contact</a>
        </div>
    </div>
</section>
</div>
