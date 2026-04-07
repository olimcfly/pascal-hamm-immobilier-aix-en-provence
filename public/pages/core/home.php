<?php
$pageTitle = 'Immobilier Aix-en-Provence & Pays d\'Aix — Pascal Hamm | Vente, Achat, Estimation';
$metaDesc = 'Expert immobilier indépendant à Aix-en-Provence : vente, achat, estimation immobilière et accompagnement 360° dans le Pays d\'Aix.';
$metaKeywords = 'immobilier Aix-en-Provence, expert immobilier Aix-en-Provence, estimation immobilière Aix-en-Provence, achat immobilier Pays d\'Aix, vente immobilière Pays d\'Aix, conseiller immobilier indépendant Pays d\'Aix';
$extraCss = ['/assets/css/home.css'];

$featuredProperties = [
    [
        'title' => 'Villa familiale avec jardin arboré',
        'city' => 'Aix-en-Provence',
        'price' => '895 000 €',
        'surface' => '165 m²',
        'rooms' => '5 pièces',
        'badge' => 'Exclusivité',
        'image' => '/assets/images/featured1.jpg',
    ],
    [
        'title' => 'Appartement terrasse centre historique',
        'city' => 'Aix-en-Provence',
        'price' => '545 000 €',
        'surface' => '88 m²',
        'rooms' => '4 pièces',
        'badge' => 'Nouveau',
        'image' => '/assets/images/featured2.jpg',
    ],
    [
        'title' => 'Maison provençale proche nature',
        'city' => 'Le Tholonet',
        'price' => '1 240 000 €',
        'surface' => '210 m²',
        'rooms' => '6 pièces',
        'badge' => 'Coup de cœur',
        'image' => '/assets/images/property2.jpg',
    ],
];
?>

<section class="hero hero--premium" aria-labelledby="home-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Aix-en-Provence · Pays d'Aix</span>
            <h1 id="home-hero-title">Vendre, acheter et estimer sereinement dans le Pays d'Aix, avec un conseiller local unique.</h1>
            <p class="hero__subtitle">Pascal Hamm vous accompagne de la stratégie jusqu'à la signature : estimation immobilière Aix-en-Provence, vente immobilière Pays d'Aix et recherche ciblée d'opportunités.</p>
            <div class="hero__actions">
                <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Demander une estimation gratuite</a>
                <a href="/biens" class="btn btn--outline-white btn--lg">Voir les biens à vendre</a>
            </div>

            <div class="hero__pillars" role="list" aria-label="Piliers de confiance">
                <span role="listitem">Vente</span>
                <span role="listitem">Achat</span>
                <span role="listitem">Estimation</span>
                <span role="listitem">Accompagnement 360°</span>
            </div>

            <div class="hero__trust">
                <div class="trust-item"><span class="value">4.9/5</span><span class="label">Avis clients</span></div>
                <div class="trust-item"><span class="value">Pays d'Aix</span><span class="label">Expertise terrain</span></div>
                <div class="trust-item"><span class="value">24h</span><span class="label">Délai de réponse</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section section--alt" id="realite-prospect">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Votre réalité immobilière</span>
            <h2 class="section-title">Vous avez un projet important, mais aussi des doutes très concrets.</h2>
            <p class="section-subtitle">Vendre sans brader, acheter au bon prix, éviter les erreurs administratives, négocier correctement : ce sont les vraies préoccupations des propriétaires et acquéreurs à Aix-en-Provence.</p>
        </div>
        <div class="grid-3">
            <article class="card"><div class="card__body"><h3 class="card__title">Vendre au bon prix</h3><p class="card__text">Vous voulez une estimation fiable, pas un prix vitrine. L'objectif : vendre dans les bonnes conditions et dans un délai cohérent.</p></div></article>
            <article class="card"><div class="card__body"><h3 class="card__title">Trouver les bonnes opportunités</h3><p class="card__text">Le marché bouge vite. Les biens qualitatifs partent vite. Vous avez besoin d'un tri pertinent et d'un accès à des opportunités locales.</p></div></article>
            <article class="card"><div class="card__body"><h3 class="card__title">Réduire la charge mentale</h3><p class="card__text">Visites, négociation, compromis, suivi notaire : vous ne voulez pas gérer seul chaque détail ni prendre de risques inutiles.</p></div></article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid-2 about-split">
        <div>
            <span class="section-label">Votre conseiller</span>
            <h2 class="section-title">Pascal Hamm, conseiller immobilier indépendant à Aix-en-Provence.</h2>
            <p>Interlocuteur unique, Pascal accompagne les projets d'achat, de vente et d'estimation immobilière à Aix-en-Provence et dans le Pays d'Aix avec une approche humaine, structurée et rigoureuse.</p>
            <ul class="benefits-list">
                <li>Expert local : Aix-en-Provence, Le Tholonet, Ventabren, Luynes, Puyricard.</li>
                <li>Accompagnement 360° : stratégie, commercialisation, négociation, sécurisation.</li>
                <li>Suivi personnalisé du premier échange jusqu'à la signature.</li>
            </ul>
            <a href="/a-propos" class="btn btn--outline">Découvrir son parcours</a>
        </div>
        <figure class="about-photo">
            <img src="/assets/images/about.jpg" alt="Pascal Hamm, conseiller immobilier au Pays d'Aix" loading="lazy">
        </figure>
    </div>
</section>

<section class="section section--alt" id="methode">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">La méthode Pascal Hamm</span>
            <h2 class="section-title">Une méthode claire en 5 étapes, pour sécuriser votre projet.</h2>
        </div>
        <div class="grid-5-steps">
            <?php foreach ([
                ['01', 'Comprendre votre projet', 'Objectifs, contraintes, timing et contexte familial ou patrimonial.'],
                ['02', 'Définir la stratégie', 'Positionnement prix, plan de commercialisation ou cahier de recherche acheteur.'],
                ['03', 'Valoriser ou cibler', 'Mise en valeur du bien vendeur ou sélection affinée des biens acheteur.'],
                ['04', 'Négocier et sécuriser', 'Négociation argumentée, vérifications et cadre juridique sécurisé.'],
                ['05', 'Accompagner jusqu\'à la signature', 'Suivi notarial, coordination des parties et pilotage jusqu\'à l\'acte.'],
            ] as $step): ?>
            <article class="step-card">
                <span class="step-card__num"><?= e($step[0]) ?></span>
                <h3><?= e($step[1]) ?></h3>
                <p><?= e($step[2]) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-32">
            <a href="/contact" class="btn btn--primary">Réserver un rendez-vous</a>
            <a href="/secteurs" class="btn btn--outline">Consulter les secteurs</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Action vs inaction</span>
            <h2 class="section-title">Ce que vous gagnez en étant accompagné, et ce que vous évitez.</h2>
        </div>
        <div class="grid-2 compare-grid">
            <article class="compare-card compare-card--positive">
                <h3>En passant à l'action</h3>
                <ul>
                    <li>Gain de temps et plan d'action clair.</li>
                    <li>Négociation plus solide et décisions mieux cadrées.</li>
                    <li>Projet sécurisé, moins de stress et plus de visibilité.</li>
                </ul>
            </article>
            <article class="compare-card compare-card--risk">
                <h3>En restant seul</h3>
                <ul>
                    <li>Risque de mauvaise estimation ou de mauvais ciblage.</li>
                    <li>Négociations subies et délais plus longs.</li>
                    <li>Charge mentale élevée et opportunités manquées.</li>
                </ul>
            </article>
        </div>
    </div>
</section>

<section class="section section--alt" id="preuves-sociales">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Preuves sociales</span>
            <h2 class="section-title">Des résultats concrets et des avis clients qui rassurent.</h2>
        </div>
        <div class="grid-3">
            <article class="testimonial">
                <div class="testimonial__stars">★★★★★</div>
                <p class="testimonial__text">"Accompagnement clair du début à la fin. Notre vente à Aix-en-Provence a été structurée, fluide et sécurisée."</p>
                <p class="testimonial__author">Sophie & Marc — Vente</p>
            </article>
            <article class="testimonial">
                <div class="testimonial__stars">★★★★★</div>
                <p class="testimonial__text">"Nous avons acheté dans le Pays d'Aix avec une vraie stratégie. Pascal a filtré efficacement les biens."</p>
                <p class="testimonial__author">Julie R. — Achat</p>
            </article>
            <article class="testimonial">
                <div class="testimonial__stars">★★★★★</div>
                <p class="testimonial__text">"Estimation immobilière très précise, communication excellente, et un interlocuteur vraiment disponible."</p>
                <p class="testimonial__author">Nicolas T. — Estimation</p>
            </article>
        </div>
        <div class="text-center mt-32">
            <a href="/avis-clients" class="btn btn--outline">Voir tous les avis clients</a>
        </div>
    </div>
</section>

<section class="section" id="biens-en-vente">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Biens en vente en cours</span>
            <h2 class="section-title">Des opportunités sélectionnées à Aix-en-Provence et dans le Pays d'Aix.</h2>
            <p class="section-subtitle">Chaque bien est présenté avec ses informations clés pour vous permettre une décision rapide et éclairée.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($featuredProperties as $property): ?>
            <article class="card property-card-premium">
                <img class="card__img" src="<?= e($property['image']) ?>" alt="<?= e($property['title']) ?> à <?= e($property['city']) ?>" loading="lazy">
                <div class="card__body">
                    <span class="property-badge"><?= e($property['badge']) ?></span>
                    <h3 class="card__title"><?= e($property['title']) ?></h3>
                    <p class="card__text property-meta"><?= e($property['city']) ?> · <?= e($property['surface']) ?> · <?= e($property['rooms']) ?></p>
                    <p class="property-price"><?= e($property['price']) ?></p>
                    <a href="/biens" class="btn btn--primary btn--sm">Voir le bien</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-32">
            <a href="/biens" class="btn btn--outline btn--lg">Voir tous les biens</a>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label">Vos objectifs</span>
            <h2 class="section-title">Des services organisés selon votre motivation réelle.</h2>
        </div>
        <div class="grid-4">
            <article class="service-card"><h3 class="service-card__title">Je veux vendre dans les meilleures conditions</h3><p class="service-card__text">Positionnement prix, stratégie de diffusion et qualification acquéreurs.</p></article>
            <article class="service-card"><h3 class="service-card__title">Je veux acheter sans perdre de temps</h3><p class="service-card__text">Sélection ciblée, accompagnement visites et négociation structurée.</p></article>
            <article class="service-card"><h3 class="service-card__title">Je veux un accompagnement de A à Z</h3><p class="service-card__text">Un interlocuteur unique pour coordonner toutes les étapes.</p></article>
            <article class="service-card"><h3 class="service-card__title">Je veux une estimation fiable</h3><p class="service-card__text">Analyse locale précise basée sur les transactions comparables.</p></article>
        </div>
    </div>
</section>

<section class="cta-banner" id="cta-final">
    <div class="container">
        <h2>Parlons de votre projet immobilier à Aix-en-Provence.</h2>
        <p>Choisissez votre prochain pas : direct pour avancer vite, ou indirect pour vous informer avant décision.</p>
        <div class="cta-banner__actions">
            <a href="/estimation-gratuite" class="btn btn--accent btn--lg">Demander une estimation gratuite</a>
            <a href="/contact" class="btn btn--outline-white btn--lg">Prendre contact</a>
            <a href="tel:+33667198366" class="btn btn--outline-white btn--lg">Appeler Pascal</a>
        </div>
        <div class="cta-banner__actions cta-banner__actions--secondary">
            <a href="/biens" class="btn btn--outline-white">Voir les biens</a>
            <a href="#methode" class="btn btn--outline-white">Découvrir la méthode</a>
            <a href="/secteurs" class="btn btn--outline-white">Consulter les secteurs</a>
            <a href="/blog" class="btn btn--outline-white">Lire les conseils immobiliers</a>
            <a href="/avis-clients" class="btn btn--outline-white">Voir les avis clients</a>
        </div>
    </div>
</section>
