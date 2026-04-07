<?php
// Configuration de la page
$pageTitle = 'Nos biens immobiliers à Aix-en-Provence — Pascal Hamm | Vente & Location';
$metaDesc = 'Découvrez notre sélection exclusive de biens immobiliers à Aix-en-Provence et dans le Pays d\'Aix : appartements, maisons, terrains et locaux commerciaux.';
$metaKeywords = 'biens immobiliers Aix-en-Provence, appartements à vendre Aix-en-Provence, maisons Aix-en-Provence, immobilier Pays d\'Aix, acheter Aix-en-Provence, location Aix-en-Provence';
$extraCss = ['/public/assets/css/style.css'];


// ============================================
// CONNEXION DB - Inclure seulement le nouveau Database.php
// ============================================
require_once __DIR__ . '/../../../core/Database.php';

try {
    // Utilisation directe de la classe Database
    $db = Database::getInstance();
} catch (Exception $e) {
    error_log('DB Connection Error: ' . $e->getMessage());
    die("Service temporairement indisponible.");
}

// ============================================
// RÉCUPÉRATION DES BIENS
// ============================================
$biens = [];

try {
    $stmt = $db->query("
        SELECT id, slug, titre, type_bien, prix, surface, pieces, chambres,
               ville, secteur, quartier, description_courte, photo_principale,
               transaction_type, statut, created_at
        FROM biens
        WHERE statut IN ('Disponible', 'Sous offre')
        ORDER BY
            CASE WHEN transaction_type = 'Vente' THEN 1 ELSE 2 END,
            created_at DESC
        LIMIT 50
    ");

    $biens = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur récupération biens: ' . $e->getMessage());
    $biens = [];
}

$nbBiensVente = count(array_filter($biens, fn($b) => $b['transaction_type'] === 'Vente'));
$nbBiensLocation = count(array_filter($biens, fn($b) => $b['transaction_type'] === 'Location'));
$nbBiensTotal = count($biens);

include __DIR__ . '/../../templates/header.php';
?>

<main class="main-content">
    <!-- En-tête de page -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">Nos biens immobiliers</h1>
            <p class="page-subtitle">À Aix-en-Provence et dans le Pays d'Aix</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $nbBiensTotal ?></div>
                    <div class="stat-label">Biens au total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $nbBiensVente ?></div>
                    <div class="stat-label">À vendre</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $nbBiensLocation ?></div>
                    <div class="stat-label">À louer</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    <div class="filtres-section">
        <div class="container">
            <div class="filtres">
                <button class="filtre-btn active" data-type="tous">Tous les biens</button>
                <button class="filtre-btn" data-type="Vente">À vendre</button>
                <button class="filtre-btn" data-type="Location">À louer</button>
            </div>
            <div class="results-count">
                Affichage de <strong><?= $nbBiensTotal ?></strong> biens
            </div>
        </div>
    </div>

    <!-- Grille des biens -->
    <section class="biens-section">
        <div class="container">
            <div class="biens-grid">
                <?php if (!empty($biens)): ?>
                    <?php foreach ($biens as $bien): ?>
                        <article class="bien-card" data-type="<?= htmlspecialchars($bien['transaction_type']) ?>" data-category="<?= htmlspecialchars($bien['type_bien']) ?>">
                            <div class="bien-card__image">
                                <?php if (!empty($bien['photo_principale'])): ?>
                                    <img src="/uploads/biens/<?= htmlspecialchars($bien['photo_principale']) ?>" alt="<?= htmlspecialchars($bien['titre']) ?>" loading="lazy">
                                <?php else: ?>
                                    <img src="/assets/images/default-property.jpg" alt="Bien immobilier" loading="lazy">
                                <?php endif; ?>
                                <span class="bien-badge"><?= htmlspecialchars($bien['transaction_type']) ?></span>
                            </div>
                            <div class="bien-card__content">
                                <h3 class="bien-card__title">
                                    <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>">
                                        <?= htmlspecialchars($bien['titre']) ?>
                                    </a>
                                </h3>
                                <div class="bien-card__location">
                                    <i class="icon-pin"></i>
                                    <?= htmlspecialchars($bien['ville']) ?>
                                    <?php if (!empty($bien['quartier'])): ?>
                                        - <?= htmlspecialchars($bien['quartier']) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="bien-card__features">
                                    <span><i class="icon-area"></i> <?= number_format($bien['surface'], 0) ?> m²</span>
                                    <span><i class="icon-bed"></i> <?= $bien['pieces'] ?> pièces</span>
                                    <span><i class="icon-bed"></i> <?= $bien['chambres'] ?> ch.</span>
                                </div>
                                <div class="bien-card__price">
                                    <strong><?= number_format($bien['prix'], 0, ',', ' ') ?> €</strong>
                                    <?php if ($bien['transaction_type'] === 'Location'): ?>
                                        <span>/mois</span>
                                    <?php endif; ?>
                                </div>
                                <div class="bien-card__footer">
                                    <div class="bien-card__meta">
                                        <?php if (!empty($bien['type_bien'])): ?>
                                            <span class="tag"><?= htmlspecialchars($bien['type_bien']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>" class="btn btn--primary btn--block">
                                        Voir ce bien
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>Aucun bien disponible pour le moment.</p>
                        <p>Contactez-nous pour une recherche personnalisée.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h3>Vous ne trouvez pas votre bonheur ?</h3>
                <p>Nos conseillers peuvent vous proposer des biens non encore publiés.</p>
                <div class="cta-buttons">
                    <a href="/contact" class="btn btn--primary btn--lg">Demander une recherche</a>
                    <a href="tel:+33667198366" class="btn btn--outline-white btn--lg">Appeler Pascal</a>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Script de filtrage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtres = document.querySelectorAll('.filtre-btn');
    const biens = document.querySelectorAll('.bien-card');
    const compteBiens = document.querySelector('.results-count strong');

    function filterBiens(type) {
        let visibleCount = 0;

        biens.forEach(bien => {
            const shouldShow = type === 'tous' ||
                              bien.dataset.type === type ||
                              bien.dataset.category === type;

            bien.style.display = shouldShow ? 'block' : 'none';
            if (shouldShow) visibleCount++;
        });

        if (compteBiens) {
            compteBiens.textContent = visibleCount;
        }
    }

    filtres.forEach(filtre => {
        filtre.addEventListener('click', function() {
            filtres.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterBiens(this.dataset.type);
        });
    });

    // Initial filter
    filterBiens('tous');
});
</script>

<!-- Schema.org Markup -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "RealEstateListing",
  "name": "<?= htmlspecialchars($pageTitle) ?>",
  "description": "<?= htmlspecialchars($metaDesc) ?>",
  "url": "<?= 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>",
  "image": "/assets/images/og-biens.jpg",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Aix-en-Provence",
    "addressRegion": "Provence-Alpes-Côte d'Azur",
    "addressCountry": "FR"
  },
  "offers": {
    "@type": "Offer",
    "priceSpecification": {
      "@type": "UnitPriceSpecification",
      "price": "<?= $bien['prix'] ?>",
      "priceCurrency": "EUR",
      "referenceQuantity": {
        "@type": "QuantitativeValue",
        "unitText": "MONTH"<?php if ($bien['transaction_type'] === 'Vente'): ?>, "value": 1<?php endif; ?>
      }
    }
  }
}
</script>

<?php include __DIR__ . '/../../templates/footer.php'; ?>
