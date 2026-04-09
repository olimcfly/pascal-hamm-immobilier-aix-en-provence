<?php
$pageTitle = 'Nos biens immobiliers à Aix-en-Provence — Pascal Hamm';
$metaDesc = 'Découvrez notre sélection exclusive de biens immobiliers à Aix-en-Provence et dans le Pays d\'Aix.';
$extraCss = ['/assets/css/style.css', '/assets/css/biens.css'];

require_once __DIR__ . '/../../../core/Database.php';

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Service indisponible.");
}

// ✅ RÉCUPÉRATION DES BIENS
$biens = [];

try {
    $stmt = $db->query("
        SELECT *
        FROM biens
        WHERE statut IN ('Disponible', 'Sous offre')
        ORDER BY
            CASE WHEN transaction_type = 'Vente' THEN 1 ELSE 2 END,
            created_at DESC
    ");

    $biens = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$nbBiensTotal = count($biens);
?>

<!-- HERO -->
<section class="hero hero--premium">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92), rgba(15,38,68,.86)), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Biens immobiliers</span>
            <h1>Maisons et appartements à Aix-en-Provence</h1>
            <p class="hero__subtitle">
                Découvrez notre sélection de biens disponibles à la vente et à la location dans le Pays d’Aix.
            </p>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="section page-biens-header">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $nbBiensTotal ?></div>
                <div class="stat-label">Biens disponibles</div>
            </div>
        </div>
    </div>
</section>

<!-- GRID -->
<section class="section">
    <div class="container">

        <div class="biens-grid">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>

                    <article class="bien-card">

                        <div class="bien-card__header">

                            <?php
                                $photoSrc = (!empty($bien['photo_principale']) && strpos($bien['photo_principale'], 'default.jpg') === false)
                                    ? htmlspecialchars($bien['photo_principale'])
                                    : '/assets/images/placeholder.php?type=' . urlencode($bien['type_bien'] ?? 'bien') . '&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            ?>
                            <?php
                                $placeholderFallback = '/assets/images/placeholder.php?type=' . urlencode($bien['type_bien'] ?? 'bien') . '&surface=' . (int)($bien['surface'] ?? 0) . '&pieces=' . (int)($bien['pieces'] ?? 0);
                            ?>
                            <img class="bien-card__img"
                                 src="<?= $photoSrc ?>"
                                 alt="<?= htmlspecialchars($bien['titre'] ?? '') ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= $placeholderFallback ?>'">

                            <span class="bien-badge <?= strtolower($bien['transaction_type']) === 'vente' ? 'vente' : 'location' ?>">
                                <?= htmlspecialchars($bien['transaction_type']) ?>
                            </span>

                        </div>

                        <div class="bien-card__body">

                            <h3 class="bien-card__title">
                                <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>">
                                    <?= htmlspecialchars($bien['titre']) ?>
                                </a>
                            </h3>

                            <div class="bien-card__location">
                                📍 <?= htmlspecialchars($bien['ville']) ?>
                            </div>

                            <div class="bien-card__specs">
                                <div class="spec-item">📐 <?= number_format($bien['surface'], 0) ?> m²</div>
                                <div class="spec-item">🛏️ <?= $bien['pieces'] ?> pièces</div>
                            </div>

                            <div class="bien-card__price">
                                <?= number_format($bien['prix'], 0, ',', ' ') ?> €
                                <?php if ($bien['transaction_type'] === 'Location'): ?>
                                    <span>/mois</span>
                                <?php endif; ?>
                            </div>

                            <a href="/bien/<?= htmlspecialchars($bien['slug']) ?>" class="btn btn--primary" style="width:100%;">
                                Voir le bien
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:3rem;">
                    <p>Aucun bien disponible actuellement.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- CTA -->
<div class="container">
    <section class="cta-section">
        <h3>Vous ne trouvez pas votre bonheur ?</h3>
        <p>Nos conseillers peuvent vous proposer des biens exclusifs.</p>
        <div class="cta-buttons">
            <a href="/contact" class="btn btn--primary btn--lg">Nous contacter</a>
            <a href="tel:+33600000000" class="btn btn--outline-white btn--lg">Appeler</a>
        </div>
    </section>
</div>

<?php
?>
