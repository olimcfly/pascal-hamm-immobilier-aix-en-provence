<?php
// Métadonnées SEO optimisées
$pageTitle = "Immobilier Aix-en-Provence - Achat/Vente & Estimation Gratuite";
$pageDescription = "Expert immobilier indépendant à Aix-en-Provence. Estimation gratuite, vente et achat d'appartements et maisons. Prix moyens, tendances du marché et conseils personnalisés.";
$metaKeywords = 'immobilier Aix-en-Provence, expert immobilier Aix-en-Provence, estimation immobilière Aix-en-Provence, achat maison Aix-en-Provence, vente appartement Aix-en-Provence, conseiller immobilier indépendant Aix-en-Provence';

// CSS supplémentaire
$extraCss = ['/assets/css/villes.css'];

// Schema.org (LocalBusiness)
$schemaMarkup = '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Pascal Hamm - Expert immobilier Aix-en-Provence",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Aix-en-Provence",
    "addressRegion": "Bouches-du-Rhône",
    "postalCode": "13090",
    "streetAddress": "Votre adresse"
  },
  "telephone": "+33412345678",
  "url": "https://votresite.com/villes/aix-en-provence-immobilier",
  "description": "Expert immobilier indépendant à Aix-en-Provence, spécialisé dans l\'achat, la vente et l\'estimation de biens immobiliers.",
  "openingHours": "Mo-Sa 09:00-19:00",
  "image": "https://votresite.com/assets/images/aix-en-provence-hero.jpg"
}
</script>
';

// Contenu de la page
$pageContent = '
<!-- Hero Section -->
<section class="hero hero--premium" aria-labelledby="aix-hero-title">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92) 0%, rgba(15,38,68,.86) 58%, rgba(26,60,94,.92) 100%), url(\'/assets/images/aix-en-provence-hero.jpg\');"></div>
    <div class="container">
        <div class="hero__content" data-animate>
            <span class="section-label hero__label">Immobilier Aix-en-Provence</span>
            <h1 id="aix-hero-title">Vendre, acheter et estimer sereinement à Aix-en-Provence</h1>
            <p class="hero__subtitle" data-animate>Expert immobilier indépendant depuis 20 ans. Découvrez nos services sur mesure pour votre projet immobilier à Aix-en-Provence.</p>
            <a href="/contact" class="btn btn--primary" data-animate>Demander une estimation gratuite</a>
        </div>
    </div>
</section>

<!-- Marché Immobilier -->
<section class="market-data" aria-labelledby="market-data-title">
    <div class="container">
        <h2 id="market-data-title">Le marché immobilier à Aix-en-Provence en 2024</h2>
        <div class="market-stats">
            <div class="stat-card">
                <h3>Prix moyen au m²</h3>
                <p>3 200 €</p>
            </div>
            <div class="stat-card">
                <h3>Temps de vente moyen</h3>
                <p>35 jours</p>
            </div>
            <div class="stat-card">
                <h3>Demande locative</h3>
                <p>Très élevée</p>
            </div>
        </div>
        <p>Les prix à Aix-en-Provence ont augmenté de 7% en 2023, avec une forte demande pour les appartements en centre-ville et les maisons avec jardin. Le marché reste dynamique grâce à l\'attractivité de la ville.</p>
    </div>
</section>

<!-- FAQ -->
<section class="faq" aria-labelledby="faq-title">
    <div class="container">
        <h2 id="faq-title">Foire aux questions sur l\'immobilier à Aix-en-Provence</h2>
        <div class="accordion">
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Quel est le prix moyen d\'un appartement à Aix-en-Provence ?</span>
                    <svg class="accordion__icon" viewBox="0 0 24 24" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>Le prix moyen d\'un appartement à Aix-en-Provence est de 350 000 €, avec des variations selon la surface et l\'emplacement. Les biens en centre-ville sont particulièrement recherchés.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button class="accordion__button" aria-expanded="false">
                    <span class="accordion__title">Combien de temps faut-il pour vendre une maison à Aix-en-Provence ?</span>
                    <svg class="accordion__icon" viewBox="0 0 24 24" aria-hidden="true">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>
                <div class="accordion__content">
                    <p>En moyenne, une maison se vend en 35 jours à Aix-en-Provence, mais ce délai peut varier selon le prix et la qualité de la présentation du bien.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Liens vers d\'autres villes -->
<section class="related-cities" aria-labelledby="related-cities-title">
    <div class="container">
        <h2 id="related-cities-title">Autres villes proches</h2>
        <div class="cities-grid">
            <a href="/villes/eguilles-immobilier" class="city-card">
                <img src="/assets/images/eguilles-thumb.jpg" alt="Immobilier Eguilles" loading="lazy">
                <h3>Eguilles</h3>
            </a>
            <a href="/villes/simiane-collongue-immobilier" class="city-card">
                <img src="/assets/images/simiane-collongue-thumb.jpg" alt="Immobilier Simiane-Collongue" loading="lazy">
                <h3>Simiane-Collongue</h3>
            </a>
            <a href="/villes/velaux-immobilier" class="city-card">
                <img src="/assets/images/velaux-thumb.jpg" alt="Immobilier Vélaux" loading="lazy">
                <h3>Vélaux</h3>
            </a>
        </div>
    </div>
</section>
';

// Balise canonique
$canonicalUrl = 'https://votresite.com/villes/aix-en-provence-immobilier';

include(__DIR__ . '/../../../templates/page.php');
?>
