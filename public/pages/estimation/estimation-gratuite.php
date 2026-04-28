<?php
// Bootstrap déjà chargé par le router (index.php)
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../../core/bootstrap.php';
}

if (!class_exists(DvfEstimatorService::class, false)) {
    require_once __DIR__ . '/../../../core/services/DvfEstimatorService.php';
}

$db = Database::getInstance();
DvfEstimatorService::ensureTables();

/**
 * Extrait le préfixe département (ex. 13%) pour filtrer la base DVF à partir
 * du libellé commune + code postal (autocomplete adresse.data.gouv).
 */
function estimationPostalPrefixForDvf(string $localite): string
{
    if (preg_match('/\b((?:13|84|83|04|05|06)[0-9]{3})\b/', $localite, $m)) {
        return substr($m[1], 0, 2) . '%';
    }

    return '13%';
}

/**
 * Filtre SQL sur property_type DVF (type_local en base, souvent en minuscules).
 * Villa / immeuble : regroupement sur « maison ». Local : préfixe DGFIP long.
 */
function estimationDvfPropertyTypeFilter(string $typeBien): array
{
    $typeBien = strtolower(trim($typeBien));
    if ($typeBien === 'villa' || $typeBien === 'immeuble') {
        return [
            'clause' => 'property_type = :dvf_pt',
            'params' => [':dvf_pt' => 'maison'],
        ];
    }
    if ($typeBien === 'local') {
        return [
            'clause' => '(property_type LIKE :dvf_pt_like OR property_type = :dvf_pt_eq)',
            'params' => [
                ':dvf_pt_like' => 'local%',
                ':dvf_pt_eq'   => 'local',
            ],
        ];
    }
    if ($typeBien === 'terrain') {
        return [
            'clause' => '(property_type LIKE :dvf_pt_like OR property_type = :dvf_pt_eq)',
            'params' => [
                ':dvf_pt_like' => 'terrain%',
                ':dvf_pt_eq'   => 'terrain',
            ],
        ];
    }

    return [
        'clause' => 'property_type = :dvf_pt',
        'params' => [':dvf_pt' => $typeBien],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $typeBien  = trim($_POST['type_bien']  ?? '');
    $surface   = trim($_POST['surface']    ?? '');
    $localite  = trim($_POST['localite']   ?? '');
    $budget    = trim($_POST['budget']     ?? '');
    $projet    = trim($_POST['projet']     ?? '');
    $lat       = trim($_POST['lat']        ?? '');
    $lng       = trim($_POST['lng']        ?? '');

    if ($typeBien && $surface && $localite && $projet) {

        // ── Capture anonyme pour géolocalisation (aucun email/tel) ──
        $db->prepare("
            INSERT INTO estimation_zones
                (type_bien, surface, localite, budget, projet, lat, lng, ip, created_at)
            VALUES
                (:type_bien, :surface, :localite, :budget, :projet, :lat, :lng, :ip, NOW())
        ")->execute([
            ':type_bien' => $typeBien,
            ':surface'   => $surface,
            ':localite'  => $localite,
            ':budget'    => $budget,
            ':projet'    => $projet,
            ':lat'       => $lat ?: null,
            ':lng'       => $lng ?: null,
            ':ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $zoneId = $db->lastInsertId();

        $cpPrefix = estimationPostalPrefixForDvf($localite);
        $typeFilter = estimationDvfPropertyTypeFilter($typeBien);

        // ── Calcul fourchette DVF ──────────────────────────────────
        $dvf = $db->prepare("
            SELECT
                ROUND(AVG(price_m2))   AS prix_moyen,
                ROUND(MIN(price_m2))   AS prix_min,
                ROUND(MAX(price_m2))   AS prix_max,
                COUNT(*)              AS nb_transactions
            FROM dvf_transactions
            WHERE " . $typeFilter['clause'] . "
            AND   postal_code LIKE :cp
            AND   mutation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            AND   surface BETWEEN :surf_min AND :surf_max
        ");
        $dvf->execute(array_merge($typeFilter['params'], [
            ':cp'       => $cpPrefix,
            ':surf_min' => max(0, (int) $surface - 20),
            ':surf_max' => (int) $surface + 20,
        ]));
        $dvfData = $dvf->fetch(PDO::FETCH_ASSOC);

        // ── Calcul fourchette ──────────────────────────────────────
        $fourchette = null;
        if ($dvfData && $dvfData['prix_moyen']) {
            $surf = (int) $surface;
            $fourchette = [
                'min'  => number_format((int) $dvfData['prix_min'] * $surf, 0, ',', ' '),
                'moy'  => number_format((int) $dvfData['prix_moyen'] * $surf, 0, ',', ' '),
                'max'  => number_format((int) $dvfData['prix_max'] * $surf, 0, ',', ' '),
                'pm2'  => number_format((int) $dvfData['prix_moyen'], 0, ',', ' '),
                'nb'   => $dvfData['nb_transactions'],
            ];
        }

        // ── Comparables récents ────────────────────────────────────
        $comparables = $db->prepare("
            SELECT address_label, surface, value_amount, price_m2, mutation_date
            FROM   dvf_transactions
            WHERE  " . $typeFilter['clause'] . "
            AND    postal_code LIKE :cp
            AND    mutation_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            ORDER  BY ABS(surface - :surface) ASC
            LIMIT  5
        ");
        $comparables->execute(array_merge($typeFilter['params'], [
            ':cp'      => $cpPrefix,
            ':surface' => (int) $surface,
        ]));
        $comps = $comparables->fetchAll(PDO::FETCH_ASSOC);

        // ── Stockage session pour page résultat ───────────────────
        $_SESSION['estimation'] = [
            'zone_id'     => $zoneId,
            'type_bien'   => $typeBien,
            'surface'     => $surface,
            'localite'    => $localite,
            'budget'      => $budget,
            'projet'      => $projet,
            'fourchette'  => $fourchette,
            'comparables' => $comps,
        ];

        redirect('/estimation-gratuite/resultat');
    }
}

$pageTitle = 'Estimation gratuite — Pascal Hamm | Conseiller immobilier Aix-en-Provence';
$metaDesc  = 'Obtenez une fourchette d\'estimation basée sur les ventes réelles DVF (Bouches-du-Rhône, Pays d\'Aix). Gratuit, instantané, sans inscription.';
$extraCss  = ['/assets/css/estimation.css'];
$extraJs   = ['/assets/js/estimation.js'];

$advisorPhoto = trim((string) setting('advisor_photo', ''));
if ($advisorPhoto === '') {
    $advisorPhoto = DEFAULT_ADVISOR_PHOTO_URL;
}
?>

<!-- ══ PAGE HEADER ══════════════════════════════════════════════════════════ -->
<div class="page-header page-header--estimation">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a>
            <span>Estimation gratuite</span>
        </nav>
        <h1>Estimation gratuite<br><span>de votre bien immobilier</span></h1>
        <p>Basée sur les ventes réelles DVF (13) et le Pays d’Aix · Instantané · Sans inscription</p>
    </div>
</div>

<!-- ══ SECTION PRINCIPALE ══════════════════════════════════════════════════ -->
<section class="section section--estimation">
    <div class="container">
        <div class="estimation-layout">

            <!-- ── Formulaire ─────────────────────────────────────────────── -->
            <div class="estimation-form-wrap">

                <!-- Disclaimer pédagogique -->
                <div class="estimation-disclaimer" role="alert">
                    <span class="disclaimer-icon" aria-hidden="true">ℹ️</span>
                    <div>
                        <strong>Information importante</strong>
                        <p>
                            Cette estimation est basée sur des statistiques de marché issues des données
                            DVF (Demandes de Valeurs Foncières). Elle donne une <strong>fourchette indicative</strong>
                            et ne constitue pas une expertise officielle.
                            Seul un professionnel agréé peut établir une estimation certifiée
                            (divorce, succession, etc.).
                        </p>
                    </div>
                </div>

                <form id="form-estimation"
                      action="/estimation-gratuite"
                      method="POST">
                    <?= csrfField() ?>

                    <!-- Champs cachés géolocalisation -->
                    <input type="hidden" name="lat" id="geo-lat">
                    <input type="hidden" name="lng" id="geo-lng">

                    <!-- ── Ligne 1 : Type de bien ──────────────────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num">1</span>
                            Quel type de bien ?
                        </h3>
                        <div class="type-grid" role="group" aria-label="Type de bien">
                            <?php
                            $types = [
                                'appartement' => ['🏢', 'Appartement'],
                                'maison'      => ['🏠', 'Maison'],
                                'villa'       => ['🏡', 'Villa'],
                                'terrain'     => ['🌿', 'Terrain'],
                                'local'       => ['🏪', 'Local commercial'],
                                'immeuble'    => ['🏬', 'Immeuble'],
                            ];
                            foreach ($types as $val => [$icon, $label]): ?>
                            <label class="type-card" data-type="<?= $val ?>">
                                <input type="radio"
                                       name="type_bien"
                                       value="<?= $val ?>"
                                       required
                                       class="sr-only">
                                <span class="type-card__icon" aria-hidden="true"><?= $icon ?></span>
                                <span class="type-card__label"><?= $label ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <p id="err-type" class="form-error" hidden>
                            Veuillez choisir un type de bien.
                        </p>
                    </div>

                    <!-- ── Ligne 2 : Surface + Localité ───────────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num">2</span>
                            Caractéristiques & localisation
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-surface">
                                    Surface habitable <span class="required-star">*</span>
                                </label>
                                <div class="input-with-unit">
                                    <input type="number"
                                           id="est-surface"
                                           name="surface"
                                           class="form-control"
                                           placeholder="Ex : 85"
                                           min="5"
                                           max="2000"
                                           required>
                                    <span class="input-unit">m²</span>
                                </div>
                            </div>
                            <div class="form-group form-group--autocomplete">
                                <label class="form-label" for="est-localite">
                                    Ville / Code postal <span class="required-star">*</span>
                                </label>
                                <div class="input-with-icon">
                                    <input type="text"
                                           id="est-localite"
                                           name="localite"
                                           class="form-control"
                                           placeholder="Ex : Aix-en-Provence, 13100…"
                                           autocomplete="off"
                                           required>
                                    <span class="input-icon" aria-hidden="true">📍</span>
                                </div>
                                <!-- Suggestions autocomplete -->
                                <ul id="localite-suggestions" class="autocomplete-list" hidden></ul>
                            </div>
                        </div>
                    </div>

                    <!-- ── Ligne 3 : Budget + Projet ──────────────────── -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="form-step-num">3</span>
                            Votre projet
                        </h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="est-budget">
                                    Votre estimation personnelle
                                </label>
                                <div class="input-with-unit">
                                    <input type="number"
                                           id="est-budget"
                                           name="budget"
                                           class="form-control"
                                           placeholder="Ex : 350 000"
                                           min="0">
                                    <span class="input-unit">€</span>
                                </div>
                                <small class="form-hint">Optionnel — Pour comparer avec le marché</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    Votre projet <span class="required-star">*</span>
                                </label>
                                <div class="projet-toggle" role="group" aria-label="Type de projet">
                                    <label class="projet-btn">
                                        <input type="radio"
                                               name="projet"
                                               value="vendre"
                                               class="sr-only"
                                               required>
                                        <span>🏷️ Vendre</span>
                                    </label>
                                    <label class="projet-btn">
                                        <input type="radio"
                                               name="projet"
                                               value="acheter"
                                               class="sr-only">
                                        <span>🔑 Acheter</span>
                                    </label>
                                    <label class="projet-btn">
                                        <input type="radio"
                                               name="projet"
                                               value="les_deux"
                                               class="sr-only">
                                        <span>🔄 Les deux</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Submit ──────────────────────────────────────── -->
                    <div class="form-submit-wrap">
                        <button type="submit"
                                id="btn-submit-estimation"
                                class="btn btn--accent btn--lg btn--full btn--submit-estimation">
                            <span class="btn-text">Obtenir mon estimation gratuite</span>
                            <span class="btn-loader" hidden aria-hidden="true">Calcul en cours…</span>
                            <span class="btn-icon" aria-hidden="true">→</span>
                        </button>
                        <p class="form-submit-hint">
                            🔒 Aucun email ni téléphone requis · Résultat instantané
                        </p>
                    </div>

                </form>
            </div>

            <!-- ── Sidebar ─────────────────────────────────────────────────── -->
            <aside class="estimation-sidebar" aria-label="Informations complémentaires">

                <!-- Ce que vous obtiendrez -->
                <div class="sidebar-card sidebar-card--what">
                    <h3>📊 Ce que vous obtiendrez</h3>
                    <ul class="what-list">
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Fourchette de prix basée sur les <strong>ventes réelles DVF</strong></span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Prix au m² moyen <strong>dans votre secteur</strong></span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Comparables des <strong>5 biens similaires</strong> vendus récemment</span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Comparaison avec <strong>votre estimation</strong></span>
                        </li>
                        <li>
                            <span class="what-icon" aria-hidden="true">✅</span>
                            <span>Accès à des <strong>guides gratuits</strong> téléchargeables</span>
                        </li>
                    </ul>
                </div>

                <!-- Avertissement officiel -->
                <div class="sidebar-card sidebar-card--warning">
                    <h3>⚖️ Estimation officielle</h3>
                    <p>
                        Dans certains cas (divorce, succession, prêt, fiscalité),
                        une <strong>expertise immobilière certifiée</strong> est obligatoire.
                    </p>
                    <p>Seul un professionnel agréé peut délivrer ce document officiel.</p>
                    <a href="/contact" class="btn btn--outline btn--sm btn--full">
                        Demander une expertise officielle
                    </a>
                </div>

                <!-- Indicateurs indicatifs Pays d’Aix (DVF) -->
                <div class="sidebar-card sidebar-card--market">
                    <h3>📈 Marché Pays d’Aix (13)</h3>
                    <div class="market-stats">
                        <div class="market-stat">
                            <span class="market-stat__value">≈ 5 100 €</span>
                            <span class="market-stat__label">Prix médian/m² appartement</span>
                        </div>
                        <div class="market-stat">
                            <span class="market-stat__value">≈ 4 400 €</span>
                            <span class="market-stat__label">Prix médian/m² maison</span>
                        </div>
                        <div class="market-stat">
                            <span class="market-stat__value">—</span>
                            <span class="market-stat__label">Lecture affinée sur fiche</span>
                        </div>
                    </div>
                    <small class="market-source">Ordres de grandeur DVF 12 mois · non contractuel</small>
                </div>

                <!-- Conseiller -->
                <div class="sidebar-card sidebar-card--advisor">
                    <div class="advisor-mini">
                        <div class="advisor-mini__avatar" aria-hidden="true">
                            <img src="<?= e($advisorPhoto) ?>" alt="" width="44" height="44" loading="lazy" decoding="async">
                        </div>
                        <div class="advisor-mini__info">
                            <strong><?= e(defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Pascal Hamm') ?></strong>
                            <span>Conseiller immobilier — Aix-en-Provence & Pays d’Aix (13)</span>
                        </div>
                    </div>
                    <p class="advisor-mini__quote">
                        « Chaque bien est unique. Mon rôle est de vous donner une vision
                        claire du marché aixois pour que vous preniez la meilleure
                        décision, en toute confiance. »
                    </p>
                </div>

            </aside>
        </div>
    </div>
</section>
