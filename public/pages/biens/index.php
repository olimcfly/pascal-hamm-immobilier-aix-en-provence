<?php

declare(strict_types=1);

$pageTitle = 'Biens immobiliers à Aix-en-Provence — Pascal Hamm';
$metaDesc  = 'Découvrez les biens immobiliers disponibles à Aix-en-Provence et ses environs avec Pascal Hamm Immobilier.';
$extraCss  = ['/assets/css/biens.css'];
$extraJs   = ['/assets/js/biens.js'];

$biens = [];
$searchQuery = trim((string) ($_GET['query'] ?? ''));
$searchType = strtolower(trim((string) ($_GET['type'] ?? '')));
$searchBudget = trim((string) ($_GET['budget'] ?? ''));
$filtersApplied = $searchQuery !== '' || $searchType !== '' || $searchBudget !== '';

$allowedTypes = [
    'bastide' => 'bastide',
    'villa' => 'villa',
    'viager' => 'viager',
];
$normalizedType = $allowedTypes[$searchType] ?? '';

$minBudget = null;
$maxBudget = null;
if (preg_match('/^\d+\-\d+$/', $searchBudget) === 1) {
    [$minBudget, $maxBudget] = array_map('intval', explode('-', $searchBudget, 2));
    if ($minBudget > $maxBudget) {
        [$minBudget, $maxBudget] = [$maxBudget, $minBudget];
    }
}

try {
    $sql = "SELECT id, slug, titre, type_bien, prix, surface, pieces, chambres, ville, secteur, photo_principale, statut
            FROM biens
            WHERE statut <> 'archive'";

    $params = [];
    if ($searchQuery !== '') {
        $sql .= " AND (
            titre LIKE :query
            OR ville LIKE :query
            OR secteur LIKE :query
            OR description LIKE :query
        )";
        $params[':query'] = '%' . $searchQuery . '%';
    }

    if ($normalizedType !== '') {
        $sql .= " AND LOWER(type_bien) LIKE :type";
        $params[':type'] = '%' . $normalizedType . '%';
    }

    if ($minBudget !== null && $maxBudget !== null) {
        $sql .= " AND prix BETWEEN :min_budget AND :max_budget";
        $params[':min_budget'] = $minBudget;
        $params[':max_budget'] = $maxBudget;
    }

    $sql .= " ORDER BY created_at DESC LIMIT 50";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

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
        <?php if ($filtersApplied): ?>
            <p>Résultats de votre recherche.</p>
        <?php endif; ?>
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
