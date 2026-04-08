<?php
$pageTitle = 'Financement immobilier à Aix-en-Provence — Pascal Hamm';
$metaDesc  = 'Accompagnement personnalisé pour votre financement immobilier à Aix-en-Provence. Conseil humain, optimisation du dossier et suivi jusqu\'à l\'offre de prêt.';
$extraCss  = [];

// CSRF token
if (session_status() === PHP_SESSION_ACTIVE || session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) @session_start();
}
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!-- HERO -->
<section class="hero hero--premium" aria-labelledby="financement-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Financement immobilier — Pays d'Aix</span>
            <h1 id="financement-hero-title">Financez votre projet immobilier à Aix-en-Provence sereinement.</h1>
            <p class="hero__subtitle">
                Pas de simulateur automatique. Un conseil humain, une étude de dossier sur-mesure et
                un accompagnement concret jusqu'à l'offre de prêt.
            </p>
            <div class="hero__actions">
                <a href="/prendre-rendez-vous" class="btn btn--accent btn--lg">Prendre rendez-vous</a>
                <a href="/contact" class="btn btn--outline-white btn--lg">Poser une question</a>
            </div>
        </div>
    </div>
</section>

<!-- POURQUOI NOUS FAIRE CONFIANCE -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Pourquoi nous confier votre financement</span>
            <h2 class="section-title">Un accompagnement humain, pas un comparateur automatique.</h2>
            <p class="section-subtitle">
                Obtenir un prêt immobilier, ce n'est pas remplir un formulaire en ligne. C'est constituer un dossier solide,
                négocier les bonnes conditions et coordonner les parties jusqu'à la signature.
            </p>
        </div>
        <div class="grid-3">
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">Conseil personnalisé</h3>
                    <p class="card__text">
                        Chaque dossier est analysé individuellement : revenus, apport, charges existantes,
                        type de projet et calendrier. Nous identifions les montages adaptés à votre situation réelle,
                        pas à un profil type.
                    </p>
                </div>
            </article>
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">Optimisation des conditions</h3>
                    <p class="card__text">
                        Taux, durée, assurance emprunteur, garanties bancaires : chaque levier est examiné.
                        Nous négocions directement avec nos partenaires pour obtenir les meilleures conditions
                        disponibles pour votre profil.
                    </p>
                </div>
            </article>
            <article class="card" data-animate>
                <div class="card__body">
                    <h3 class="card__title">Suivi jusqu'à l'offre</h3>
                    <p class="card__text">
                        Nous centralisons les demandes, suivons les retours des banques, relisons les propositions
                        et coordonnons les démarches jusqu'à la signature de l'offre de prêt — sans vous laisser
                        gérer seul les échanges administratifs.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ACHETER AVANT DE VENDRE -->
<section class="section" id="acheter-avant-vendre">
    <div class="container grid-2 about-split">
        <div data-animate>
            <span class="section-label">Situation spécifique</span>
            <h2 class="section-title">Acheter avant de vendre votre bien actuel ?</h2>
            <p>
                Vous avez trouvé le bien idéal mais votre propriété actuelle n'est pas encore vendue.
                C'est une situation fréquente sur le marché immobilier d'Aix-en-Provence, et elle mérite
                une analyse précise avant de s'engager.
            </p>
            <ul class="benefits-list">
                <li><strong>Prêt relais</strong> : financement temporaire adossé à la valeur de votre bien à vendre.</li>
                <li><strong>Double financement</strong> : deux crédits distincts gérés simultanément, si la situation le permet.</li>
                <li><strong>Bridge bancaire</strong> : solution intermédiaire selon votre banque et votre calendrier de vente.</li>
            </ul>
            <p style="margin-top:1rem">
                Chaque option a des implications différentes sur votre budget mensuel, votre capacité d'emprunt
                et votre exposition au risque. Nous vous présentons des scénarios clairs pour choisir en connaissance de cause.
            </p>
            <a href="/prendre-rendez-vous" class="btn btn--outline" style="margin-top:1.5rem;display:inline-flex">
                En parler avec Pascal
            </a>
        </div>
        <div class="grid-2" style="display:flex;flex-direction:column;gap:1rem" data-animate>
            <article class="card">
                <div class="card__body">
                    <h3 class="card__title">Avant de s'engager</h3>
                    <p class="card__text">
                        Nous évaluons la valeur de marché de votre bien actuel et la faisabilité du double projet
                        avant de contacter les banques.
                    </p>
                </div>
            </article>
            <article class="card">
                <div class="card__body">
                    <h3 class="card__title">Simulation des scénarios</h3>
                    <p class="card__text">
                        Prêt relais court ou long, financement en cascade, délai entre les deux transactions :
                        chaque hypothèse est chiffrée clairement.
                    </p>
                </div>
            </article>
            <article class="card">
                <div class="card__body">
                    <h3 class="card__title">Coordination vente + achat</h3>
                    <p class="card__text">
                        En travaillant avec Pascal sur les deux côtés de la transaction, vous bénéficiez
                        d'une coordination fluide entre la vente de votre bien et l'achat du suivant.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- PROCESSUS -->
<section class="section section--alt" id="processus-financement">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Comment ça se passe</span>
            <h2 class="section-title">De la première discussion à l'offre de prêt.</h2>
        </div>
        <div class="grid-5-steps">
            <?php foreach ([
                ['01', 'Premier échange', 'Nous faisons le point sur votre projet, votre situation personnelle et votre capacité d\'emprunt estimée.'],
                ['02', 'Constitution du dossier', 'Nous listons les documents nécessaires et vous guidons dans leur préparation pour présenter un dossier solide.'],
                ['03', 'Sollicitation bancaire', 'Nous contactons nos partenaires et négocions directement les conditions : taux, durée, assurance, garanties.'],
                ['04', 'Analyse des propositions', 'Nous relisons chaque offre avec vous, comparons les options et recommandons la plus adaptée à votre situation.'],
                ['05', 'Signature de l\'offre', 'Vous signez l\'offre de prêt en toute clarté. Nous restons disponibles pour la suite jusqu\'à l\'acte notarié.'],
            ] as $step): ?>
            <article class="step-card">
                <span class="step-card__num"><?= $step[0] ?></span>
                <h3><?= $step[1] ?></h3>
                <p><?= $step[2] ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- TÉMOIGNAGE -->
<section class="section">
    <div class="container" style="max-width:720px;text-align:center">
        <article class="testimonial">
            <div class="testimonial__stars">★★★★★</div>
            <p class="testimonial__text">
                « Accompagnement sérieux et efficace pour notre financement à Aix-en-Provence. Dossier monté rapidement,
                conditions négociées au mieux. On recommande sans hésitation. »
            </p>
            <p class="testimonial__author">L. &amp; M. — Achat à Aix-en-Provence</p>
        </article>
    </div>
</section>

<!-- FAQ FINANCEMENT -->
<section class="section section--alt" id="faq-financement">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Questions fréquentes</span>
            <h2 class="section-title">FAQ — Financement immobilier</h2>
        </div>
        <div style="max-width:780px;margin:0 auto;display:flex;flex-direction:column;gap:1.5rem">

            <?php foreach ([
                [
                    'q' => 'Combien de temps pour obtenir une étude de financement ?',
                    'a' => 'Après réception des éléments principaux (revenus, apport, détails du projet), nous fournissons un premier état des lieux et des propositions sous 24 à 48h ouvrées.'
                ],
                [
                    'q' => 'Proposez-vous des prêts relais ?',
                    'a' => 'Oui. Nous analysons la pertinence d\'un prêt relais selon votre projet, la valeur de votre bien actuel et votre calendrier de vente, avant de soumettre le dossier à nos partenaires.'
                ],
                [
                    'q' => 'Faut-il un apport pour emprunter ?',
                    'a' => 'L\'apport facilite l\'obtention du prêt et améliore les conditions proposées par les banques. Cependant, nous travaillons aussi des dossiers avec peu ou pas d\'apport selon les profils, les garanties et la nature du projet.'
                ],
                [
                    'q' => 'Faites-vous appel à des comparateurs automatiques ?',
                    'a' => 'Non. Nous privilégions le contact direct et la négociation humaine avec nos partenaires bancaires pour obtenir des conditions sur-mesure, pas un résultat standardisé.'
                ],
                [
                    'q' => 'Quels documents préparer pour lancer une étude ?',
                    'a' => 'Les 3 dernières fiches de paie, le dernier avis d\'imposition, un justificatif d\'identité et les détails du projet (type de bien, montant estimé, localisation). Nous vous précisons la liste complète lors du premier échange.'
                ],
            ] as $faq): ?>
            <div style="background:var(--clr-white);border:1px solid var(--clr-border);border-radius:var(--radius);padding:1.25rem 1.5rem">
                <h3 style="font-size:1rem;font-weight:600;color:var(--clr-primary);margin-bottom:.5rem"><?= htmlspecialchars($faq['q']) ?></h3>
                <p style="color:var(--clr-text-muted);line-height:1.7;margin:0"><?= htmlspecialchars($faq['a']) ?></p>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="cta-banner">
    <div class="container">
        <h2>Prêt à lancer votre projet immobilier à Aix-en-Provence ?</h2>
        <p>Discutons de votre situation et des solutions de financement adaptées à votre projet.</p>
        <div class="cta-banner__actions">
            <a href="/prendre-rendez-vous" class="btn btn--accent btn--lg">Prendre rendez-vous</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Nous contacter</a>
        </div>
    </div>
</section>

<!-- JSON-LD FAQ SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type":"Question","name":"Combien de temps pour obtenir une étude de financement ?","acceptedAnswer":{"@type":"Answer","text":"Après réception des éléments principaux, nous fournissons un premier état des lieux sous 24 à 48h ouvrées."}},
    {"@type":"Question","name":"Proposez-vous des prêts relais ?","acceptedAnswer":{"@type":"Answer","text":"Oui, nous analysons la pertinence d'un prêt relais selon votre projet et votre calendrier de vente."}},
    {"@type":"Question","name":"Faut-il un apport pour emprunter ?","acceptedAnswer":{"@type":"Answer","text":"L'apport améliore les conditions, mais nous travaillons aussi des dossiers avec peu ou pas d'apport selon les profils et garanties."}},
    {"@type":"Question","name":"Quels documents préparer pour lancer une étude ?","acceptedAnswer":{"@type":"Answer","text":"Les 3 dernières fiches de paie, le dernier avis d'imposition, un justificatif d'identité et les détails du projet."}}
  ]
}
</script>
