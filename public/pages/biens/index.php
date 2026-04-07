<?php

declare(strict_types=1);

$pageTitle = 'Biens immobiliers à Aix-en-Provence — Pascal Hamm';
$metaDesc  = 'Découvrez les biens immobiliers disponibles à Aix-en-Provence et ses environs avec Pascal Hamm Immobilier.';
$extraCss  = ['/assets/css/biens.css'];
$extraJs   = ['/assets/js/biens.js'];

$biens = [];

try {
    $stmt = $db->query(
        "SELECT id, slug, titre, type_bien, prix, surface, pieces, chambres, ville, secteur, photo_principale, statut
         FROM biens
         WHERE statut <> 'archive'
         ORDER BY created_at DESC
         LIMIT 50"
    );

    if ($stmt !== false) {
        $biens = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    $biens = [];
}
?>

<section class="section">
    <div class="container">
        <h1>Nos biens immobiliers</h1>
        <p class="biens-count"><?= count($biens) ?> bien(s) disponible(s)</p>

        <?php if (empty($biens)): ?>
            <p>Aucun bien n'est disponible pour le moment.</p>
        <?php else: ?>
            <div class="biens-grid">
                <?php foreach ($biens as $bien): ?>
                    <article class="bien-card">
                        <div class="bien-card__img">
                            <img
                                src="<?= e((string) ($bien['photo_principale'] ?: '/assets/images/hero-bg.jpg')) ?>"
                                alt="<?= e((string) $bien['titre']) ?>"
                                loading="lazy">
                        </div>
                        <div class="bien-card__body">
                            <p class="bien-card__prix"><?= number_format((int) $bien['prix'], 0, ',', ' ') ?> €</p>
                            <h2 class="bien-card__titre"><?= e((string) $bien['titre']) ?></h2>
                            <p class="bien-card__loc"><?= e((string) ($bien['secteur'] ?: $bien['ville'] ?: 'Aix-en-Provence')) ?></p>
                            <div class="bien-card__specs">
                                <?php if (!empty($bien['surface'])): ?>
                                    <span><?= (int) $bien['surface'] ?> m²</span>
                                <?php endif; ?>
                                <?php if (!empty($bien['pieces'])): ?>
                                    <span><?= (int) $bien['pieces'] ?> pièces</span>
                                <?php endif; ?>
                                <?php if (!empty($bien['chambres'])): ?>
                                    <span><?= (int) $bien['chambres'] ?> ch.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
