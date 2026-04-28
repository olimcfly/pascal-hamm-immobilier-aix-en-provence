<?php
$pageTitle    = 'Immobilier centre-ville Aix-en-Provence - Expert | ' . ADVISOR_NAME;
$metaDesc     = 'Expert immobilier au cœur d’Aix-en-Provence : Cours Mirabeau, ruelles, immeubles anciens, biens de standing — ' . (ADVISOR_NAME ?: 'Pascal Hamm') . ' accompagne vente, achat et estimation.';
$metaKeywords = 'immobilier Aix centre, appartement Cours Mirabeau, Mazarin, vente Aix, hyper-centre 13, expert Aix';
$extraCss     = ['/assets/css/villes.css'];

$pageContent = '
<section class="hero hero--premium" aria-labelledby="centre-ville-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/centre-ville-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Centre historique — Aix-en-Provence</span>
            <h1 id="centre-ville-hero-title">L’immobilier au cœur d’Aix</h1>
            <p class="hero__subtitle">Le cœur d’Aix concentre les biens les plus recherchés : immeubles haussmanniens, appartements rénovés, ruelles piétonnes, Cours et quartier Mazarin. Un marché exigeant, où l’analyse de micro-secteur et d’état des biens prime.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--primary">Demander une estimation gratuite</a>
                <a href="/contact" class="btn btn--outline">Nous contacter</a>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="centre-ville-intro">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Hyper-centre</span>
            <h2 class="section-title">Patrimoine, vie étudiante et commerces</h2>
            <p class="section-subtitle">Entre hôtels particuliers, bâtis anciens et réhabilitations, le centre d’Aix offre un spectre de prix large selon la rue, l’étage, la prestation (ascenseur, extérieur, place de stationnement). La demande est soutenue par les résidents, les investisseurs et l’attractivité universitaire.</p>
        </div>
        <div class="grid-2">
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg></div>
                <h3 class="card__title">Immeubles de caractère</h3>
                <p class="card__text">Parquet, moulures, hauteurs, volumes : les critères d’estimation se jouent sur l’intérieur autant que sur la carte.</p>
            </div>
            <div class="card card--alt" data-animate>
                <div class="card__icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></div>
                <h3 class="card__title">Nervosité de marché</h3>
                <p class="card__text">Biens soignés, bien exposés et correctement côtés : délais de vente souvent compacts pour les offres fortes.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="centre-ville-market">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Marché immobilier</span>
            <h2 class="section-title">Indicateurs (ordre de grandeur, Aix centre)</h2>
        </div>
        <div class="grid-3">
            <div class="stat-card" data-animate>
                <div class="stat-card__value">4 800–6 200 €</div>
                <div class="stat-card__label">Fourchettes fréquentes au m²</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">+1,5 %</div>
                <div class="stat-card__label">Tendance illustr. annuelle</div>
            </div>
            <div class="stat-card" data-animate>
                <div class="stat-card__value">~35 jours</div>
                <div class="stat-card__label">Cible de délai (biens prêts)</div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="centre-ville-services">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Nos services</span>
            <h2 class="section-title">Nos services en hyper-centre</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Estimation</h3>
                <p class="card__text">Cartographie des rues, état, charges, extérieur : aucune moyenne web ne remplace l’analyse in situ.</p>
                <a href="/estimation-gratuite" class="btn btn--outline">Demander une estimation</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Vente</h3>
                <p class="card__text">Argumentaire, diffusion ciblée, qualification des offres dans un centre où la négociation est la norme.</p>
                <a href="/contact" class="btn btn--outline">Vendre</a>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Achat</h3>
                <p class="card__text">Piste hors marché, visites ciblées, arbitrage ruelle / bruit / stationnement (critères d’Aix centre).</p>
                <a href="/biens" class="btn btn--outline">Voir les biens</a>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner" id="centre-ville-cta">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Votre projet au cœur d’Aix</h2>
            <p class="cta-banner__text">Discutons de votre bien ou de votre recherche en hyper-centre.</p>
            <div class="cta-banner__actions">
                <a href="/contact" class="btn btn--primary">Contact</a>
                <a href="/estimation-gratuite" class="btn btn--outline">Estimation gratuite</a>
            </div>
        </div>
    </div>
</section>
';
?>
