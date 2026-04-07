<?php
// Métadonnées SEO
$pageTitle = 'Nos secteurs immobiliers en Provence - Expertise locale | Pascal Hamm Immobilier';
$pageDescription = 'Découvrez nos secteurs d\'intervention autour d\'Aix-en-Provence. Expertise locale pour Aix-en-Provence, Bouc-Bel-Air, Eguilles et leurs alentours.';
$pageKeywords = 'secteurs immobiliers Provence, expert immobilier Aix-en-Provence, immobilier Bouc-Bel-Air, immobilier Eguilles, immobilier Simiane-Collongue, immobilier Beaurecueil';
$canonicalUrl = 'https://votresite.com/secteurs-immobiliers';

// Schema.org
$schemaMarkup = '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Pascal Hamm - Expert immobilier",
  "description": "Expertise immobilière locale dans les secteurs d\'Aix-en-Provence et ses alentours.",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Aix-en-Provence",
    "addressRegion": "Provence-Alpes-Côte d\'Azur",
    "postalCode": "13090",
    "addressCountry": "FR"
  },
  "telephone": "+33412345678",
  "url": "https://votresite.com",
  "openingHours": "Mo-Sa 09:00-19:00",
  "hasMap": "https://www.google.com/maps/place/Aix-en-Provence",
  "areaServed": {
    "@type": "GeoCircle",
    "geoMidpoint": {
      "@type": "GeoCoordinates",
      "latitude": 43.5297,
      "longitude": 5.4442
    },
    "geoRadius": "20"
  },
  "sameAs": [
    "https://www.facebook.com/votrepage",
    "https://www.linkedin.com/company/votresociete"
  ]
}
</script>
';

// CSS
$cssFiles = ['/assets/css/secteurs.css', '/assets/css/animations.css'];

// Données des secteurs (à remplacer par une requête à votre base de données)
$sectors = [
    [
        'name' => 'Aix-en-Provence',
        'type' => 'Centre-ville',
        'description' => 'Notre zone d\'intervention principale avec une connaissance approfondie des quartiers : Mazarin, Sextius, Jas de Bouffan, etc.',
        'stats' => [
            'Prix moyen au m² : 3 200 €',
            'Temps de vente moyen : 45 jours'
        ],
        'url' => '/villes/aix-en-provence-immobilier'
    ],
    [
        'name' => 'Bouc-Bel-Air',
        'type' => 'Zone résidentielle',
        'description' => 'Commune rurale avec des propriétés spacieuses et un cadre de vie préservé.',
        'stats' => [
            'Prix moyen au m² : 2 100 €',
            'Temps de vente moyen : 50 jours'
        ],
        'url' => '/villes/bouc-bel-air-immobilier'
    ],
    [
        'name' => 'Simiane-Collongue',
        'type' => 'Campagne',
        'description' => 'Commune rurale avec des propriétés spacieuses et un cadre de vie préservé.',
        'stats' => [
            'Prix moyen au m² : 2 100 €',
            'Temps de vente moyen : 50 jours'
        ],
        'url' => '/villes/simiane-collongue-immobilier'
    ],
    [
        'name' => 'Beaurecueil',
        'type' => 'Village',
        'description' => 'Petit village résidentiel très prisé pour sa proximité avec Aix-en-Provence.',
        'stats' => [
            'Prix moyen au m² : 2 800 €',
            'Temps de vente moyen : 30 jours'
        ],
        'url' => '/villes/beaurecueil-immobilier'
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="robots" content="index, follow">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <?php foreach ($cssFiles as $cssFile): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($cssFile); ?>" onerror="console.error('Failed to load CSS: <?php echo htmlspecialchars($cssFile); ?>')">
    <?php endforeach; ?>
    <?php echo $schemaMarkup; ?>
</head>
<body>
    <header>
        <!-- Votre header ici -->
    </header>

    <main>
        <section class="section hero-section" aria-labelledby="secteurs-hero">
            <div class="container">
                <h1 id="secteurs-hero">Nos secteurs immobiliers en Provence</h1>
                <p class="lead">Expertise locale pour une transaction immobilière sereine dans toute la région d'Aix-en-Provence</p>
            </div>
        </section>

        <section class="section introduction" aria-labelledby="secteurs-intro">
            <div class="container">
                <div class="sectors-intro">
                    <p>Nous intervenons sur l'ensemble des communes et quartiers autour d'Aix-en-Provence, avec une connaissance approfondie des spécificités locales de chaque secteur.</p>
                    <p>Notre couverture géographique s'étend des centres-villes aux zones résidentielles, en passant par les villages environnants.</p>
                </div>
            </div>
        </section>

        <section class="section map-section" aria-labelledby="secteurs-map">
            <div class="container">
                <div class="sectors-map">
                    <img src="/assets/images/secteurs-carte.jpg" alt="Carte des secteurs immobiliers couverts par Pascal Hamm Immobilier" class="map-image" loading="lazy">
                    <div class="map-legend">
                        <h3>Légende</h3>
                        <ul>
                            <li><span class="legend-color color-1" aria-label="Centre-ville"></span> Centre-ville</li>
                            <li><span class="legend-color color-2" aria-label="Zones résidentielles"></span> Zones résidentielles</li>
                            <li><span class="legend-color color-3" aria-label="Villages et campagnes"></span> Villages et campagnes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section class="section sectors-grid-section" aria-labelledby="secteurs-grid">
            <div class="container">
                <h2 id="secteurs-grid">Nos secteurs d'intervention</h2>

                <?php if (!empty($sectors)): ?>
                    <div class="sectors-grid">
                        <?php foreach ($sectors as $sector): ?>
                            <article class="sector-card" itemscope itemtype="https://schema.org/Place">
                                <div class="sector-header" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                                    <h2 itemprop="addressLocality"><?php echo htmlspecialchars($sector['name']); ?></h2>
                                    <span class="sector-type" itemprop="addressRegion"><?php echo htmlspecialchars($sector['type']); ?></span>
                                </div>
                                <div class="sector-content" itemprop="description">
                                    <p><?php echo htmlspecialchars($sector['description']); ?></p>
                                    <ul class="sector-stats">
                                        <?php foreach ($sector['stats'] as $stat): ?>
                                            <li><?php echo htmlspecialchars($stat); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <a href="<?php echo htmlspecialchars($sector['url']); ?>" class="btn btn-primary" itemprop="url">Découvrir <?php echo htmlspecialchars($sector['name']); ?></a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Aucun secteur disponible pour le moment. Contactez-nous pour plus d'informations.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section expertise-section" aria-labelledby="expertise-section">
            <div class="container">
                <h2 id="expertise-section">Pourquoi choisir un expert local ?</h2>
                <p>Chaque secteur a ses particularités : prix, demande, types de biens, etc. Notre connaissance approfondie de chaque zone nous permet de vous conseiller au mieux pour votre projet immobilier.</p>

                <div class="expertise-points">
                    <div class="point">
                        <h3>Analyse précise</h3>
                        <p>Nous connaissons les spécificités de chaque quartier et leur évolution.</p>
                    </div>
                    <div class="point">
                        <h3>Conseils adaptés</h3>
                        <p>Notre expertise locale vous garantit des conseils pertinents et personnalisés.</p>
                    </div>
                    <div class="point">
                        <h3>Réseau local</h3>
                        <p>Nous avons un réseau solide d'acteurs locaux pour faciliter vos transactions.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section cta-section" aria-labelledby="secteurs-cta">
            <div class="container">
                <div class="cta-section">
                    <h2 id="secteurs-cta">Vous avez un projet immobilier dans l'un de ces secteurs ?</h2>
                    <p>Contactez-nous pour une expertise locale et des conseils personnalisés.</p>
                    <a href="/contact" class="btn btn-primary btn-lg">Contactez-nous</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <!-- Votre footer ici -->
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Vérification du chargement des secteurs
        const sectors = document.querySelectorAll('.sector-card');
        if (sectors.length === 0) {
            console.warn('Aucun secteur trouvé. Vérifiez votre base de données ou le chargement des données.');
        } else {
            console.log(`Nombre de secteurs chargés: ${sectors.length}`);
        }

        // Vérification du chargement des fichiers CSS
        <?php foreach ($cssFiles as $cssFile): ?>
            const cssLoaded = document.querySelector(`link[href="<?php echo htmlspecialchars($cssFile); ?>"]`);
            if (!cssLoaded || !cssLoaded.rel === 'stylesheet') {
                console.error('CSS non chargé: <?php echo htmlspecialchars($cssFile); ?>');
            }
        <?php endforeach; ?>
    });
    </script>
</body>
</html>
