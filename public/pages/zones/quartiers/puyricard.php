<?php
$pageTitle    = 'Immobilier Puyricard Aix-en-Provence - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier à Puyricard, village rattaché au nord d\'Aix-en-Provence. Villas, maisons avec jardin, cadre résidentiel calme et verdoyant.';
$metaKeywords = 'immobilier Puyricard, maison Puyricard Aix, villa Puyricard, pays d\'Aix, résidentiel Puyricard 13';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="puyricard-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/puyricard-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Puyricard — Aix-en-Provence</span>
            <h1 id="puyricard-hero-title">L\'immobilier résidentiel à Puyricard</h1>
            <p class="hero__subtitle">Au nord d\'Aix-en-Provence, Puyricard est recherché pour ses propriétés avec jardin, son calme et sa proximité du centre (quelques minutes en voiture). Un secteur exigeant où l\'accompagnement local fait la différence sur le prix et le délai de vente.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="puyricard-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Puyricard</span>
            <h2 class="section-title">Un village au caractère résidentiel marqué</h2>
            <p class="section-subtitle">Puyricard combine l\'atmosphère d\'un village provençal, des vues sur la campagne aixoise et l\'accès rapide à l\'emploi, aux scolaires et aux grands axes (rocade, A7, aéroport Marseille-Provence). Les biens recherchés : maisons d\'architecte, villas avec piscine, bastides et demeures avec terrain arboré.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Maisons &amp; villas</h3>
                <p class="card__text">Forte demande pour les biens de plain-pied, les maisons T5-T7 avec jardin et piscine, parfois vues sur la campagne.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Calme &amp; accessibilité</h3>
                <p class="card__text">Un cadre résidentiel tout en restant intégré à l\'agglomération aixoise et aux accès péri-urbains.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="puyricard-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Le marché immobilier à Puyricard</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 200–5 200 €</div>
                <div class="stat-card__label">Fourchette indicative au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+2 à +4 %</div>
                <div class="stat-card__label">Tendance (ordre de grandeur)</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">40 jours</div>
                <div class="stat-card__label">Délai moyen cible (biens prêts)</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="puyricard-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services immobiliers à Puyricard</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation gratuite</h3>
                <p class="card__text">Analyse de votre maison, terrain et environnement proche, avec comparaison au marché du nord d’Aix.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente</h3>
                <p class="card__text">Mise en valeur, diffusion, qualification des acheteurs : la liquidité se joue sur le positionnement de prix juste.</p>
                <a href="/contact" class="btn btn--outline">Vendre</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat</h3>
                <p class="card__text">Accès à l’exclusif / hors marché, arbitrage des secteurs périphériques proches (Éguilles, Saint-Cannat, etc.).</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="puyricard-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet à Puyricard</h2>
            <p class="cta-banner__text">Discutons de votre bien ou de votre recherche, sans engagement.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contact</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>
';
?>
