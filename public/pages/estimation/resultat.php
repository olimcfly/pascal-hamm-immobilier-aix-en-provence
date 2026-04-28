<?php
/**
 * Résultat — estimation gratuite (DVF).
 *
 * Routes : GET|POST /estimation-gratuite/resultat et /estimation/resultat (config/routes.php).
 *
 * Données : le calcul est effectué sur POST dans estimation-gratuite.php, puis
 * $_SESSION['estimation'] est remplie (fourchette, comparables, saisie utilisateur).
 * Un accès direct à cette URL sans session redirige vers /estimation-gratuite : comportement
 * voulu (pas de calcul ni de fourchette sans formulaire). Pas de reprise par GET.
 */
require_once __DIR__ . '/../../../core/bootstrap.php';

// ── Récupération session ──────────────────────────────────────────────────────
$est = $_SESSION['estimation'] ?? null;
if (!$est) {
    redirect('/estimation-gratuite');
}

$fourchette  = $est['fourchette']  ?? null;
$comparables = $est['comparables'] ?? [];
$typeBien    = $est['type_bien']   ?? '';
$surface     = $est['surface']     ?? '';
$localite    = $est['localite']    ?? '';
$budget      = $est['budget']      ?? '';
$projet      = $est['projet']      ?? '';
$insufficientData = $fourchette === null;
$cityForCopy = (defined('APP_CITY') && (string) APP_CITY !== '') ? (string) APP_CITY : 'Aix-en-Provence';
$advisorShort = defined('ADVISOR_NAME') ? (string) ADVISOR_NAME : 'Pascal Hamm';

// ── Labels ────────────────────────────────────────────────────────────────────
$typeLabels = [
    'appartement' => 'Appartement',
    'maison'      => 'Maison',
    'villa'       => 'Villa',
    'terrain'     => 'Terrain',
    'local'       => 'Local commercial',
    'immeuble'    => 'Immeuble',
];

// ── Formulaire qualification (POST) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email     = trim($_POST['email']     ?? '');
    $prenom    = trim($_POST['prenom']    ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    if ($email && $prenom && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_ESTIMATION,
            'pipeline'    => LeadService::SOURCE_ESTIMATION,
            'stage'       => 'qualifie',
            'first_name'  => $prenom,
            'last_name'   => trim($_POST['nom']   ?? ''),
            'email'       => $email,
            'phone'       => $telephone,
            'intent'      => 'Estimation + RDV',
            'consent'     => !empty($_POST['rgpd']),
            'metadata'    => [
                'zone_id'           => $est['zone_id'] ?? null,
                'type_bien'         => $typeBien,
                'surface'           => $surface,
                'localite'          => $localite,
                'budget_client'     => $budget,
                'projet'            => $projet,
                'estimation_min'    => $fourchette['min'] ?? null,
                'estimation_max'    => $fourchette['max'] ?? null,
                // Qualification
                'seul_decideur'     => trim($_POST['seul_decideur']   ?? ''),
                'urgence'           => trim($_POST['urgence']         ?? ''),
                'delai'             => trim($_POST['delai']           ?? ''),
                'raison'            => trim($_POST['raison']          ?? ''),
                'situation'         => trim($_POST['situation']       ?? ''),
                'creneau_prefere'   => trim($_POST['creneau_prefere'] ?? ''),
            ],
        ]);

        // ── Nettoyage session ──────────────────────────────────────
        unset($_SESSION['estimation']);

        redirect('/merci-estimation');
    }
}

if ($insufficientData) {
    $pageTitle = 'Estimation indicative limitée — ' . ($typeLabels[$typeBien] ?? 'Bien') . ' · ' . e($localite);
    $metaDesc  = 'Données de ventes comparables insuffisantes pour une fourchette automatique fiable. '
        . 'Avis de valeur local à ' . e($localite) . ' — ' . e($advisorShort) . '.';
} else {
    $pageTitle = 'Votre estimation — ' . ($typeLabels[$typeBien] ?? 'Bien') . ' · ' . e($localite);
    $metaDesc  = 'Résultat de votre estimation immobilière gratuite pour votre '
        . ($typeLabels[$typeBien] ?? 'bien') . ' à ' . $localite
        . '. ' . e($advisorShort) . ', conseiller immobilier à ' . e($cityForCopy) . ' (13).';
}
$extraCss  = ['/assets/css/estimation.css', '/assets/css/estimation-resultat.css'];
$extraJs   = ['/assets/js/estimation-resultat.js'];

// ── Comparaison budget client vs marché ───────────────────────────────────────
$diffPct = null;
if ($budget && $fourchette) {
    $budgetVal = (int)str_replace([' ', '€', ','], '', $budget);
    $moyVal    = (int)str_replace([' ', '€', ','], '', $fourchette['moy']);
    if ($moyVal > 0) {
        $diffPct = round((($budgetVal - $moyVal) / $moyVal) * 100);
    }
}
?>

<!-- ══ PAGE HEADER ══════════════════════════════════════════════════════════ -->
<div class="page-header page-header--resultat">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a>
            <a href="/estimation-gratuite">Estimation</a>
            <span>Résultat</span>
        </nav>
        <h1>Votre estimation<br>
            <span>
                <?= e($typeLabels[$typeBien] ?? $typeBien) ?> ·
                <?= e($surface) ?> m² ·
                <?= e($localite) ?>
            </span>
        </h1>
    </div>
</div>

<!-- ══ DISCLAIMER ═══════════════════════════════════════════════════════════ -->
<div class="estimation-banner-disclaimer">
    <div class="container">
        <div class="disclaimer-inner">
            <span class="disclaimer-badge" aria-hidden="true">⚠️</span>
            <div>
                <strong>Cette estimation est indicative et non contractuelle.</strong>
                Elle est calculée à partir des statistiques de ventes enregistrées
                sur les moteurs de recherche et la base DVF (Demandes de Valeurs Foncières).
                <strong>La seule vraie estimation est celle négociée entre un acheteur et un vendeur.</strong>
                Seul un expert immobilier agréé peut établir une estimation certifiée
                (divorce, succession, prêt bancaire, fiscalité).
            </div>
        </div>
    </div>
</div>

<!-- ══ RÉSULTAT PRINCIPAL ═══════════════════════════════════════════════════ -->
<section class="section section--resultat">
    <div class="container">
        <div class="resultat-layout">

            <!-- ── Colonne principale ──────────────────────────────────────── -->
            <div class="resultat-main">

                <?php if ($fourchette): ?>
                <!-- Fourchette -->
                <div class="fourchette-card">
                    <div class="fourchette-header">
                        <h2>Fourchette estimée</h2>
                        <span class="fourchette-badge">
                            Basée sur <?= e($fourchette['nb']) ?>
                            vente<?= $fourchette['nb'] > 1 ? 's' : '' ?> sur 12 mois
                        </span>
                    </div>
                    <div class="fourchette-range">
                        <div class="fourchette-bound fourchette-bound--min">
                            <span class="bound-label">Estimation basse</span>
                            <span class="bound-value"><?= e($fourchette['min']) ?> €</span>
                        </div>
                        <div class="fourchette-middle">
                            <span class="middle-label">Valeur médiane</span>
                            <span class="middle-value"><?= e($fourchette['moy']) ?> €</span>
                            <span class="middle-pm2"><?= e($fourchette['pm2']) ?> €/m²</span>
                        </div>
                        <div class="fourchette-bound fourchette-bound--max">
                            <span class="bound-label">Estimation haute</span>
                            <span class="bound-value"><?= e($fourchette['max']) ?> €</span>
                        </div>
                    </div>
                    <!-- Barre visuelle -->
                    <div class="fourchette-bar" aria-hidden="true">
                        <div class="fourchette-bar__fill"></div>
                        <div class="fourchette-bar__cursor"></div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Données DVF insuffisantes : pas de fourchette, conversion honnête -->
                <div class="estimation-result" data-estimation-state="insufficient">
                    <div class="estimation-result__card">
                        <div class="estimation-result__status estimation-result__status--warning" role="status">
                            <span class="estimation-result__status-icon" aria-hidden="true">◆</span>
                            <span class="estimation-result__badges">
                                <span class="estimation-result__badge estimation-result__badge--muted">Données insuffisantes</span>
                                <span class="estimation-result__badge">Analyse nécessaire</span>
                            </span>
                        </div>
                        <div class="estimation-result__hero">
                            <h2 class="estimation-result__title">Estimation automatique limitée pour votre bien</h2>
                            <p class="estimation-result__lead">
                                Nous n'avons pas trouvé assez de ventes comparables réellement exploitables
                                dans votre secteur pour afficher une fourchette fiable.
                            </p>
                        </div>
                        <div class="estimation-result__body">
                            <p>
                                C'est justement dans ce type de situation qu'un avis de valeur local est utile.
                                Certains biens, certaines rues ou certains secteurs de <?= e($cityForCopy) ?>
                                nécessitent une lecture humaine du marché : état du bien, emplacement précis, étage,
                                extérieur, rareté, demande locale et concurrence actuelle.
                            </p>
                            <p class="estimation-result__emphasis">
                                <strong>Seul un expert local peut estimer précisément votre bien.</strong>
                            </p>
                        </div>
                        <div class="estimation-result__reassurance" aria-label="Pourquoi un avis local">
                            <p class="estimation-result__reassurance-title">
                                Votre bien mérite une analyse locale, pas une estimation approximative.
                            </p>
                            <ul class="estimation-result__reason-list">
                                <li>Analyse des ventes comparables réellement pertinentes</li>
                                <li>Lecture du secteur et de la demande actuelle</li>
                                <li>Prise en compte de l'état, des prestations et du potentiel du bien</li>
                                <li>Recommandation de prix claire et argumentée</li>
                            </ul>
                        </div>
                        <div class="estimation-result__cta">
                            <div class="estimation-result__actions">
                                <a class="btn btn--accent btn--lg" href="/estimation-gratuite">Demander un avis de valeur précis</a>
                                <a class="btn btn--outline btn--lg" href="/prendre-rendez-vous">Prendre rendez-vous</a>
                            </div>
                            <p class="estimation-result__note" role="note">
                                Estimation indicative non contractuelle. Un avis de valeur professionnel permet d'obtenir
                                une lecture plus fiable de votre situation.
                            </p>
                        </div>
                    </div>
                    <div class="estimation-result__summary" aria-label="Votre saisie">
                        <h3 class="estimation-result__summary-title">Résumé de votre demande</h3>
                        <div class="estimation-result__grid">
                            <div class="estimation-result__summary-item">
                                <span class="estimation-result__summary-label">Localité</span>
                                <span class="estimation-result__summary-value"><?= e($localite !== '' ? $localite : '—') ?></span>
                            </div>
                            <div class="estimation-result__summary-item">
                                <span class="estimation-result__summary-label">Type de bien</span>
                                <span class="estimation-result__summary-value"><?= e($typeLabels[$typeBien] ?? $typeBien ?: '—') ?></span>
                            </div>
                            <div class="estimation-result__summary-item">
                                <span class="estimation-result__summary-label">Surface</span>
                                <span class="estimation-result__summary-value"><?= e($surface !== '' ? $surface . ' m²' : '—') ?></span>
                            </div>
                            <div class="estimation-result__summary-item">
                                <span class="estimation-result__summary-label">Projet</span>
                                <span class="estimation-result__summary-value"><?= e($projet !== '' ? ucfirst($projet) : '—') ?></span>
                            </div>
                            <?php if ($budget !== ''): ?>
                            <div class="estimation-result__summary-item estimation-result__summary-item--wide">
                                <span class="estimation-result__summary-label">Votre estimation personnelle</span>
                                <span class="estimation-result__summary-value"><?= e(number_format((int) str_replace([' ', '€', ','], '', $budget), 0, ',', ' ')) ?> €</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($budget && $diffPct !== null): ?>
                <!-- Comparaison budget client -->
                <div class="budget-comparison
                    <?= $diffPct > 10
                        ? 'budget-comparison--high'
                        : ($diffPct < -10 ? 'budget-comparison--low' : 'budget-comparison--ok')
                    ?>">
                    <h3>📊 Votre estimation vs le marché local (DVF)</h3>
                    <p>
                        Vous estimez votre bien à
                        <strong><?= number_format((int)$budget, 0, ',', ' ') ?> €</strong>.
                        <?php if ($diffPct > 10): ?>
                            C'est <strong><?= abs($diffPct) ?>% au-dessus</strong> de la médiane
                            du marché. Un prix trop élevé peut allonger significativement
                            les délais de vente.
                        <?php elseif ($diffPct < -10): ?>
                            C'est <strong><?= abs($diffPct) ?>% en dessous</strong> de la médiane
                            du marché. Votre bien pourrait se vendre plus rapidement,
                            mais vous laissez peut-être de la valeur.
                        <?php else: ?>
                            C'est <strong>cohérent</strong> avec la médiane du marché.
                            Votre estimation est bien positionnée.
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>

                <?php if (!empty($comparables) && $fourchette): ?>
                <!-- Comparables DVF (affichés seulement si la fourchette a pu être calculée) -->
                <div class="comparables-section">
                    <h3>🏠 Ventes comparables récentes</h3>
                    <p class="comparables-intro">
                        Biens similaires vendus sur le même territoire (code postal)
                        ces 12 derniers mois (source : base DVF officielle).
                    </p>
                    <div class="comparables-table-wrap">
                        <table class="comparables-table">
                            <thead>
                                <tr>
                                    <th>Localisation</th>
                                    <th>Surface</th>
                                    <th>Prix vendu</th>
                                    <th>Prix/m²</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comparables as $comp): ?>
                                <tr>
                                    <td><?= e($comp['address_label'] ?? '—') ?></td>
                                    <td><?= e($comp['surface']) ?> m²</td>
                                    <td><?= number_format((int)$comp['value_amount'], 0, ',', ' ') ?> €</td>
                                    <td><?= number_format((int)$comp['price_m2'], 0, ',', ' ') ?> €/m²</td>
                                    <td><?= date('m/Y', strtotime($comp['mutation_date'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bloc pédagogique -->
                <div class="education-section">
                    <h3>💡 Comprendre votre estimation</h3>
                    <div class="education-grid">
                        <div class="education-card">
                            <span class="education-icon" aria-hidden="true">⚖️</span>
                            <h4>Quand une expertise officielle est obligatoire ?</h4>
                            <p>
                                Divorce, succession, donation, prêt bancaire, déclaration ISF/IFI :
                                un <strong>rapport d'expertise certifié</strong> par un professionnel
                                agréé est requis.
                            </p>
                        </div>
                        <div class="education-card">
                            <span class="education-icon" aria-hidden="true">🧭</span>
                            <h4>Les moteurs de recherche ne suffisent pas</h4>
                            <p>
                                Les prix affichés en ligne incluent des biens en vente,
                                pas encore vendus. Les prix <strong>réellement négociés</strong>
                                sont souvent 5 à 15% inférieurs.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ── Double CTA ──────────────────────────────────────────── -->
                <div class="resultat-cta-section">
                    <h3>Quelle est la prochaine étape ?</h3>
                    <div class="resultat-cta-grid">

                        <!-- CTA RDV (principal) -->
                        <div class="cta-card cta-card--primary">
                            <span class="cta-card__icon" aria-hidden="true">📅</span>
                            <h4>Obtenir une estimation précise</h4>
                            <p>
                                Prenez rendez-vous avec Pascal Hamm.
                                Visite, analyse et rapport personnalisé gratuit.
                            </p>
                            <button type="button"
                                    class="btn btn--accent btn--lg btn--full"
                                    id="openQualifForm">
                                Prendre rendez-vous gratuitement
                            </button>
                            <small>Sans engagement · Réponse sous 24h</small>
                        </div>

                    </div>
                </div>

                <!-- ── Guides téléchargeables ──────────────────────────────── -->
                <div class="ressources-section" id="ressources-section">
                    <h3>📥 Guides & ressources gratuits</h3>
                    <div class="ressources-grid">
                        <?php
                        $guides = [
                            [
                                'icon'  => '🏷️',
                                'title' => 'Guide complet vendeur',
                                'desc'  => '28 étapes pour réussir votre vente au meilleur prix.',
                                'link'  => '/guide-vendeur',
                                'cta'   => 'Lire le guide',
                            ],
                            [
                                'icon'  => '🔑',
                                'title' => 'Guide complet acheteur',
                                'desc'  => 'De la recherche à la signature, tout ce qu\'il faut savoir.',
                                'link'  => '/guide-acheteur',
                                'cta'   => 'Lire le guide',
                            ],
                            [
                                'icon'  => '💡',
                                'title' => 'Comment bien négocier ?',
                                'desc'  => 'Techniques et stratégies pour négocier le meilleur prix.',
                                'link'  => '/blog/negociation-immobiliere',
                                'cta'   => 'Lire l\'article',
                            ],
                        ];
                        foreach ($guides as $g): ?>
                        <div class="ressource-card">
                            <span class="ressource-icon" aria-hidden="true"><?= $g['icon'] ?></span>
                            <h4><?= e($g['title']) ?></h4>
                            <p><?= e($g['desc']) ?></p>
                            <a href="<?= e($g['link']) ?>"
                               class="btn btn--outline btn--sm">
                                <?= e($g['cta']) ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /resultat-main -->

            <!-- ── Sidebar résultat ────────────────────────────────────────── -->
            <aside class="resultat-sidebar" aria-label="Récapitulatif">

                <!-- Récap bien -->
                <div class="sidebar-card sidebar-card--recap">
                    <h3>📋 Votre bien</h3>
                    <ul class="recap-list">
                        <li>
                            <span class="recap-label">Type</span>
                            <span class="recap-value"><?= e($typeLabels[$typeBien] ?? $typeBien) ?></span>
                        </li>
                        <li>
                            <span class="recap-label">Surface</span>
                            <span class="recap-value"><?= e($surface) ?> m²</span>
                        </li>
                        <li>
                            <span class="recap-label">Localité</span>
                            <span class="recap-value"><?= e($localite) ?></span>
                        </li>
                        <li>
                            <span class="recap-label">Projet</span>
                            <span class="recap-value"><?= ucfirst(e($projet)) ?></span>
                        </li>
                        <?php if ($budget): ?>
                        <li>
                            <span class="recap-label">Votre estimation</span>
                            <span class="recap-value">
                                <?= number_format((int)$budget, 0, ',', ' ') ?> €
                            </span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <a href="/estimation-gratuite" class="btn btn--ghost btn--sm btn--full">
                        ← Recommencer
                    </a>
                </div>

                <!-- Conseiller -->
                <div class="sidebar-card sidebar-card--advisor">
                    <div class="advisor-mini">
                        <div class="advisor-mini__avatar" aria-hidden="true">👤</div>
                        <div class="advisor-mini__info">
                            <strong>
                                <?= e(defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Pascal Hamm') ?>
                            </strong>
                            <span>Conseiller immobilier — Aix-en-Provence & environs (13)</span>
                        </div>
                    </div>
                    <p>
                        Je connais le marché local et ses spécificités.
                        Discutons de votre projet pour une estimation précise et personnalisée.
                    </p>
                    <button type="button"
                            class="btn btn--accent btn--sm btn--full"
                            id="openQualifFormSidebar">
                        Prendre rendez-vous
                    </button>
                    <?php if (defined('APP_PHONE') && APP_PHONE): ?>
                    <a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>"
                       class="btn btn--outline btn--sm btn--full">
                        📞 <?= e(APP_PHONE) ?>
                    </a>
                    <?php endif; ?>
                </div>

            </aside>
        </div>
    </div>
</section>

<!-- ══ MODAL QUALIFICATION ══════════════════════════════════════════════════ -->
<div id="qualifModal"
     class="modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="qualifModalTitle"
     hidden>
    <div class="modal__backdrop" id="qualifModalBackdrop"></div>
    <div class="modal__dialog modal__dialog--lg">
        <div class="modal__header">
            <h2 id="qualifModalTitle">📅 Prendre rendez-vous</h2>
            <button type="button" class="modal__close" aria-label="Fermer">×</button>
        </div>
        <div class="modal__body">

            <p class="modal__intro">
                Pour préparer au mieux notre rendez-vous sur votre bien à
                <strong><?= e($localite) ?></strong>,
                quelques questions rapides sur votre projet.
            </p>

            <form id="form-qualification"
                  action="/estimation-gratuite/resultat"
                  method="POST"
                  novalidate>
                <?= csrfField() ?>

                <!-- Confirmation surface -->
                <fieldset class="fieldset">
                    <legend>Confirmation du bien</legend>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="q-surface-conf">Surface (m²)</label>
                            <input type="number"
                                   id="q-surface-conf"
                                   name="surface_confirm"
                                   class="form-control"
                                   value="<?= e($surface) ?>">
                        </div>
                    </div>
                </fieldset>

                <!-- Qualification projet -->
                <fieldset class="fieldset">
                    <legend>Votre projet</legend>

                    <div class="form-group">
                        <label class="form-label" for="q-decideur">
                            Êtes-vous le seul décisionnaire ?
                        </label>
                        <select id="q-decideur" name="seul_decideur" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="oui">Oui</option>
                            <option value="non">Non (en couple / indivision…)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-urgence">Niveau d'urgence</label>
                        <select id="q-urgence" name="urgence" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="urgent">Urgent (- de 3 mois)</option>
                            <option value="normal">Normal (3 à 6 mois)</option>
                            <option value="flexible">Flexible (+ de 6 mois)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-delai">Délai souhaité</label>
                        <select id="q-delai" name="delai" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="moins_3_mois">Moins de 3 mois</option>
                            <option value="3_6_mois">3 à 6 mois</option>
                            <option value="plus_6_mois">Plus de 6 mois</option>
                            <option value="indéfini">Pas encore défini</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-raison">
                            Raison principale de ce projet
                        </label>
                        <select id="q-raison" name="raison" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="investissement">Investissement</option>
                            <option value="résidence_principale">Résidence principale</option>
                            <option value="succession">Succession / héritage</option>
                            <option value="divorce">Divorce / séparation</option>
                            <option value="déménagement">Déménagement</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-situation">Votre situation actuelle</label>
                        <select id="q-situation" name="situation" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="proprietaire">Propriétaire</option>
                            <option value="locataire">Locataire</option>
                            <option value="hebergé">Hébergé</option>
                            <option value="deja_vendu">J'ai déjà vendu</option>
                            <option value="en_cours">Vente en cours</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-creneau">Créneau préféré</label>
                        <select id="q-creneau" name="creneau_prefere" class="form-control">
                            <option value="">— Sélectionner —</option>
                            <option value="matin">Matin (9h – 12h)</option>
                            <option value="midi">Midi (12h – 14h)</option>
                            <option value="apres-midi">Après-midi (14h – 18h)</option>
                            <option value="soir">Soir (18h – 20h)</option>
                        </select>
                    </div>

                </fieldset>

                <!-- Contact -->
                <fieldset class="fieldset">
                    <legend>Vos coordonnées</legend>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="q-prenom">
                                Prénom <span class="required-star">*</span>
                            </label>
                            <input type="text"
                                   id="q-prenom"
                                   name="prenom"
                                   class="form-control"
                                   required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="q-nom">Nom</label>
                            <input type="text"
                                   id="q-nom"
                                   name="nom"
                                   class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-email">
                            Email <span class="required-star">*</span>
                        </label>
                        <input type="email"
                               id="q-email"
                               name="email"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="q-telephone">Téléphone</label>
                        <input type="tel"
                               id="q-telephone"
                               name="telephone"
                               class="form-control"
                               placeholder="06 …">
                    </div>

                </fieldset>

                <!-- RGPD -->
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="rgpd" required>
                        <span>
                            J'accepte la
                            <a href="/politique-confidentialite" target="_blank">
                                politique de confidentialité
                            </a>.
                            <span class="required-star" aria-hidden="true">*</span>
                        </span>
                    </label>
                </div>

                <div class="form-submit-wrap">
                    <button type="submit" class="btn btn--accent btn--lg btn--full">
                        Confirmer mon rendez-vous →
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
