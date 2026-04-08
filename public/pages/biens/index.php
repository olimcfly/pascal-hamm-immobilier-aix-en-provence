<?php
// Configuration de la page
$pageTitle = 'Nos biens immobiliers à Aix-en-Provence — Pascal Hamm | Vente & Location';
$metaDesc = 'Découvrez notre sélection exclusive de biens immobiliers à Aix-en-Provence et dans le Pays d\'Aix : appartements, maisons, terrains et locaux commerciaux.';
$metaKeywords = 'biens immobiliers Aix-en-Provence, appartements à vendre Aix-en-Provence, maisons Aix-en-Provence, immobilier Pays d\'Aix, acheter Aix-en-Provence, location Aix-en-Provence';
$extraCss = ['/assets/css/style.css'];

// ============================================
// CONNEXION DB
// ============================================
require_once __DIR__ . '/../../../core/Database.php';

try {
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

<style>
/* Styles spécifiques à la page Biens */
.page-biens-header { padding: 4rem 0 2rem; text-align: center; }
.stats-grid { display: flex; justify-content: center; gap: 2rem; margin-top: 2rem; flex-wrap: wrap; }
.stat-card { background: #f8f9fa; padding: 1.5rem 2rem; border-radius: 8px; text-align: center; min-width: 150px; }
.stat-number { font-size: 2rem; font-weight: 700; color: var(--color-primary, #003366); }
.stat-label { font-size: 0.9rem; color: #666; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 1px; }

.filtres-section { padding: 1rem 0 2rem; border-bottom: 1px solid #eaeaea; margin-bottom: 3rem; }
.filtres-container { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
.filtres { display: flex; gap: 0.5rem; }
.filtre-btn { background: #f1f1f1; border: none; padding: 0.5rem 1.2rem; border-radius: 20px; cursor: pointer; font-weight: 500; transition: 0.3s; }
.filtre-btn:hover { background: #e2e2e2; }
.filtre-btn.active { background: var(--color-primary, #003366); color: white; }

.biens-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem; }
.bien-card__image-wrapper { position: relative; height: 240px; overflow: hidden; border-radius: 8px 8px 0 0; }
.bien-card__image-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
.card:hover .bien-card__image-wrapper img { transform: scale(1.05); }
.bien-badge { position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.7); color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }

.cta-section { background: var(--color-primary, #003366); color: white; padding: 4rem 0; text-align: center; border-radius: 12px; margin: 4rem 0; }
.cta-section h3 { color: white; margin-bottom: 1rem; font-size: 2rem; }
.cta-buttons { display: flex; justify-content: center; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
.btn--outline-white { border: 2px solid white; color: white; background: transparent; }
.btn--outline-white:hover { background: white; color: var(--color-primary, #003366); }
</style>

<!-- En-tête de page -->
<section class="section page-biens-header">
    <div class="container">
        <div class="section__header">
            <span class="section-label">À Aix-en-Provence et dans le Pays d'Aix</span>
            <h1 class="section-title">Nos biens immobiliers</h1>
        </div>

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
    <div class="container filtres-container">
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
<section class="section">
    <div class="container">
        <div class="biens-grid" id="biens-grid">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>
                    <article class="card bien-item" data-type="<?= htmlspecialchars($bien['transaction_type']) ?>" data-category="<?= htmlspecialchars($bien['type_bien']) ?>">
                        
                        <div class="bien-card__image-wrapper">
                            <?php if (!empty($bien['photo_principale'])): ?>
                                <img src="/uploads/biens/<?= htmlspecialchars($bien['photo_principale']) ?>" alt="<?= htmlspecialchars($bien['titre']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="/assets/images/featured1.jpg" alt="Bien immobilier par défaut" loading="lazy">
                            <?php endif; ?>
                            <span class="bien-badge"><?= htmlspecialchars($bien['transaction_type']) ?></span>
                        </div>
                        
                        <div class="card__body">
                            <h3 class="card__title">
                                <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>" style="text-decoration:none; color:inherit;">
                                    <?= htmlspecialchars($bien['titre']) ?>
                                </a>
                            </h3>
                            
                            <p class="card__text property-meta">
                                <?= htmlspecialchars($bien['ville']) ?> 
                                <?php if(!empty($bien['quartier'])) echo ' - ' . htmlspecialchars($bien['quartier']); ?> 
                                <br>
                                <?= number_format($bien['surface'], 0) ?> m² · <?= $bien['pieces'] ?> pièces
                            </p>
                            
                            <p class="property-price">
                                <?= number_format($bien['prix'], 0, ',', ' ') ?> €
                                <?php if ($bien['transaction_type'] === 'Location'): ?><span>/mois</span><?php endif; ?>
                            </p>
                            
                            <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>" class="btn btn--primary btn--sm" style="width:100%; text-align:center; margin-top:1rem;">Voir le bien</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #f9f9f9; border-radius: 8px;">
                    <p style="font-size: 1.2rem; color: #555;">Aucun bien disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<div class="container">
    <section class="cta-section">
        <h3>Vous ne trouvez pas votre bonheur ?</h3>
        <p>Nos conseillers peuvent vous proposer des biens non encore publiés.</p>
        <div class="cta-buttons">
            <a href="/contact" class="btn btn--primary btn--lg" style="background: white; color: var(--color-primary, #003366);">Demander une recherche</a>
            <a href="tel:+33600000000" class="btn btn--outline-white btn--lg">Appeler Pascal</a>
        </div>
    </section>
</div>

<!-- Script de filtrage -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtres = document.querySelectorAll('.filtre-btn');
    const biens = document.querySelectorAll('.bien-item');
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
});
</script>

<?php include __DIR__ . '/../../templates/footer.php'; ?>
