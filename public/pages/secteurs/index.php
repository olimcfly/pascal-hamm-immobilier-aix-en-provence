<?php
$pageTitle    = 'Nos secteurs immobiliers — ' . ADVISOR_NAME;
$metaDesc     = 'Découvrez nos secteurs d’intervention sur Aix-en-Provence et le Pays d’Aix. Expertise locale par ' . ADVISOR_NAME . ', conseiller immobilier indépendant.';
$metaKeywords = 'secteurs immobiliers Aix-en-Provence, Pays d\'Aix, immobilier 13, expert immobilier ' . APP_CITY;
$extraCss     = ['/assets/css/villes.css'];
?>

<section class="hero hero--primary" aria-labelledby="secteurs-hero-title" style="padding-block: 1.5rem;">
    <div class="container">
        <div class="hero__content" style="max-width:540px">
            <span class="section-label" style="color:rgba(255,255,255,0.8);font-size:0.7rem;">Secteurs</span>
            <h1 id="secteurs-hero-title" style="color:white;font-size:1.75rem;margin-bottom:0.5rem;">Pays d’Aix &amp; Bouches-du-Rhône (13)</h1>
            <p class="hero__subtitle" style="color:rgba(255,255,255,0.9);font-size:0.95rem;">
                <?= htmlspecialchars((string) (ADVISOR_NAME ?: 'Pascal Hamm'), ENT_QUOTES, 'UTF-8') ?> : expertise locale, estimation et accompagnement sur
                <strong>Aix-en-Provence</strong> et le <strong>Pays d’Aix</strong> (Bouches-du-Rhône).
            </p>
            <p style="color:rgba(255,255,255,0.85);font-size:0.9rem;margin-top:0.75rem;">
                Intervention sur Aix-en-Provence et les communes environnantes (rayon habituel ~30&nbsp;km selon projet).
            </p>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="ville-ref-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Ville de référence</span>
            <h2 id="ville-ref-title" class="section-title">Aix-en-Provence</h2>
            <p class="section-subtitle">Fiche principale : estimation, vente et achat avec une lecture fine du centre historique, des quartiers résidentiels et de la périphérie immédiate.</p>
        </div>
        <div class="cities-grid" style="max-width:420px">
            <a href="<?= url('/immobilier/aix-en-provence') ?>" class="city-card">
                <img src="/assets/images/hero-bg.jpg" alt="Aix-en-Provence" loading="lazy">
                <div class="city-card__body">
                    <h3>Aix-en-Provence</h3>
                    <p class="city-card__desc">Cœur du Pays d’Aix : Cours Mirabeau, Mazarin, villages (Puyricard, Milles, Luynes) et déplacements vers l’emploi, les axes et l’aéroport de Marseille-Provence.</p>
                    <span class="city-card__cta">Découvrir la fiche →</span>
                </div>
            </a>
        </div>
        <p class="section-subtitle" style="margin-top:1.5rem">
            <strong>Communes souvent couvertes</strong> (non exhaustif) : Gardanne, Vitrolles, Les Pennes-Mirabeau, Bouc-Bel-Air, Berre-l’Étang, Saint-Cannat, Châteauneuf-le-Rouge, Éguilles,
            Rognac, La Fare-les-Oliviers, Cabriès, Simiane-Collongue, Meyreuil, etc. — indiquez votre adresse : le projet prime sur la liste.
        </p>
    </div>
</section>

<section class="section section--alt" aria-labelledby="quartiers-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Quartiers &amp; faubourgs d’Aix</span>
            <h2 id="quartiers-title" class="section-title">Secteurs d’Aix</h2>
            <p class="section-subtitle">Fiches quartier : <code>/secteurs/quartiers/[slug]</code> ou <code>/quartier/[slug]</code>. Les anciennes URL (site précédent) se réorientent en 301 vers la fiche territoire Pays d’Aix.</p>
        </div>
        <div class="cities-grid">
            <a href="<?= url('/quartier/centre-ville') ?>" class="city-card">
                <img src="/assets/images/centre-ville-hero.jpg" alt="Centre-ville d'Aix" loading="lazy">
                <div class="city-card__body">
                    <h3>Centre-ville &amp; Cours</h3>
                    <p class="city-card__desc">Hyper-centre, ruelles, immeubles anciens, commerces, vie étudiante et culturelle.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/mazarin') ?>" class="city-card">
                <img src="/assets/images/hero-bg.jpg" alt="Quartier Mazarin" loading="lazy">
                <div class="city-card__body">
                    <h3>Quartier Mazarin</h3>
                    <p class="city-card__desc">Hôtels particuliers, rues calmes, standing et proximité des cours.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/puyricard') ?>" class="city-card">
                <img src="/assets/images/puyricard-hero.jpg" alt="Puyricard" loading="lazy">
                <div class="city-card__body">
                    <h3>Puyricard</h3>
                    <p class="city-card__desc">Villas et jardins au nord, cadre résidentiel très recherché.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/jas-de-bouffan') ?>" class="city-card">
                <img src="/assets/images/hero-bg.jpg" alt="Jas-de-Bouffan" loading="lazy">
                <div class="city-card__body">
                    <h3>Jas-de-Bouffan</h3>
                    <p class="city-card__desc">Résidentiel ouest, praticité, familles, accès rocade.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/les-milles') ?>" class="city-card">
                <img src="/assets/images/centre-ville-hero.jpg" alt="Les Milles" loading="lazy">
                <div class="city-card__body">
                    <h3>Les Milles</h3>
                    <p class="city-card__desc">Activités, pôle tertiaire, logements neufs, desserte vers l’aéroport.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
            <a href="<?= url('/quartier/luynes') ?>" class="city-card">
                <img src="/assets/images/puyricard-hero.jpg" alt="Luynes" loading="lazy">
                <div class="city-card__body">
                    <h3>Luynes</h3>
                    <p class="city-card__desc">Village associé, maisons, vue, ambiance provençale.</p>
                    <span class="city-card__cta">Découvrir →</span>
                </div>
            </a>
        </div>
    </div>
</section>

<section class="section" id="reference-secteurs" aria-labelledby="ref-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Référence</span>
            <h2 id="ref-title" class="section-title">URL utiles (copier)</h2>
            <p class="section-subtitle">Ville : <code>/secteurs/villes/aix-en-provence</code> ou <code>/immobilier/aix-en-provence</code> — les anciennes adresses d’archives se réorientent en 301 vers cette fiche unique Pays d’Aix.</p>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="expertise-title">
    <div class="container">
        <div class="section__header">
            <span class="section-label">Notre expertise</span>
            <h2 id="expertise-title" class="section-title">Pourquoi un expert sur le Pays d’Aix ?</h2>
        </div>
        <div class="grid-3">
            <div class="card" data-animate>
                <h3 class="card__title">Lecture de marché locale</h3>
                <p class="card__text">Prix, délais et typologies diffèrent du centre à la périphérie : l’estimation se fait avec des comparables cohérents.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Réseau de partenaires</h3>
                <p class="card__text">Notaires, courtiers, travaux : un écosystème local pour sécuriser la vente ou l’achat.</p>
            </div>
            <div class="card" data-animate>
                <h3 class="card__title">Un interlocuteur sur le terrain</h3>
                <p class="card__text">Quartier par quartier, le marché varie : une lecture locale évite les erreurs de prix et de calendrier.</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <div class="cta-banner__content">
            <h2 class="cta-banner__title">Un projet sur Aix ou les alentours ?</h2>
            <p class="cta-banner__text">Contactez <?= htmlspecialchars((string) (ADVISOR_NAME ?: 'Pascal Hamm'), ENT_QUOTES, 'UTF-8') ?> pour une expertise locale et des conseils personnalisés.</p>
            <div class="cta-banner__actions">
                <a href="<?= url('/estimation-gratuite') ?>" class="btn btn--accent">Estimation gratuite</a>
                <a href="<?= url('/contact') ?>" class="btn btn--outline-white">Nous contacter</a>
            </div>
        </div>
    </div>
</section>
