<?php
$pageTitle = 'Nos biens immobiliers à ' . APP_CITY . ' — ' . ADVISOR_NAME;
$metaDesc = 'Découvrez notre sélection exclusive de biens immobiliers à Aix-en-Provence et sur le Pays d’Aix (13).';
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

$biens = [];
try {
    $where  = ["b.statut <> 'archive'"];
    $params = [];

    if ($searchQuery !== '') {
        $where[] = '(b.titre LIKE :query OR b.ville LIKE :query OR b.secteur LIKE :query OR b.description LIKE :query)';
        $params[':query'] = '%' . $searchQuery . '%';
    }

    if ($normalizedType !== '') {
        $where[] = 'LOWER(b.type_bien) LIKE :type';
        $params[':type'] = '%' . $normalizedType . '%';
    }

    if ($minBudget !== null && $maxBudget !== null) {
        $where[] = 'b.prix BETWEEN :min_budget AND :max_budget';
        $params[':min_budget'] = $minBudget;
        $params[':max_budget'] = $maxBudget;
    }

    $whereSql   = implode(' AND ', $where);
    $orderLimit = ' ORDER BY b.created_at DESC LIMIT 50';
    $gallerySub = '(SELECT bp.chemin FROM bien_photos bp WHERE bp.bien_id = b.id ORDER BY bp.position ASC, bp.id ASC LIMIT 1) AS photo_galerie';
    $cols       = 'b.id, b.slug, b.titre, b.type_bien, b.prix, b.surface, b.pieces, b.chambres, b.ville, b.secteur, b.photo_principale, b.statut';

    // Requêtes de repli selon le schéma (type_transaction vs transaction, colonne photos, table bien_photos).
    $tries = [
        "SELECT $cols, b.type_transaction, b.photos, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.type_transaction, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.transaction AS type_transaction, b.photos, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.transaction AS type_transaction, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.photos, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, $gallerySub FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.type_transaction, b.photos FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.transaction AS type_transaction, b.photos FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols, b.type_transaction FROM biens b WHERE $whereSql $orderLimit",
        "SELECT $cols FROM biens b WHERE $whereSql $orderLimit",
    ];

    $lastErr = null;
    foreach ($tries as $sqlTry) {
        try {
            $stmt = $db->prepare($sqlTry);
            $stmt->execute($params);
            $biens = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            break;
        } catch (Throwable $e) {
            $lastErr = $e;
            continue;
        }
    }
    if ($biens === [] && $lastErr) {
        error_log('biens/index listing SQL: ' . $lastErr->getMessage());
    }
} catch (Throwable $e) {
    $biens = [];
    error_log('biens/index: ' . $e->getMessage());
}

$nbBiensTotal = count($biens);
?>

<!-- HERO -->
<section class="hero hero--premium hero--biens">
    <div class="hero__bg" style="background-image:linear-gradient(110deg, rgba(26,60,94,.92), rgba(15,38,68,.86)), url('/assets/images/hero-bg.jpg');"></div>
    <div class="container">
        <div class="hero__content">
            <span class="section-label">Biens immobiliers</span>
            <h1>Maisons et appartements sur Aix-en-Provence &amp; le Pays d’Aix</h1>
            <p class="hero__subtitle">
                Découvrez notre sélection de biens disponibles à la vente et à la location autour d’Aix-en-Provence.
            </p>
            <div class="hero__stats">
                <div class="stat-item">
                    <span class="stat-value"><?= $nbBiensTotal ?></span>
                    <span class="stat-label">Biens disponibles</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- GRID -->
<section class="section">
    <div class="container">
        <h1>Nos biens immobiliers</h1>
        <?php if ($filtersApplied): ?>
            <p>Résultats de votre recherche.</p>
        <?php endif; ?>
        <p class="biens-count"><?= count($biens) ?> bien(s) disponible(s)</p>

        <div class="biens-grid">
            <?php if (!empty($biens)): ?>
                <?php foreach ($biens as $bien): ?>

                    <article class="bien-card">

                        <div class="bien-card__header">

                            <?php
                                $placeholderFallback = '/assets/images/placeholder.php?type=' . urlencode($bien['type_bien'] ?? 'bien') . '&surface=' . (int) ($bien['surface'] ?? 0) . '&pieces=' . (int) ($bien['pieces'] ?? 0);
                                $coverUrl = function_exists('bien_resolve_cover_url')
                                    ? bien_resolve_cover_url($bien)
                                    : '';
                                $photoSrc = htmlspecialchars(
                                    $coverUrl !== '' ? $coverUrl : $placeholderFallback,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>
                            <img class="bien-card__img"
                                 src="<?= $photoSrc ?>"
                                 alt="<?= htmlspecialchars($bien['titre'] ?? '') ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='<?= htmlspecialchars($placeholderFallback, ENT_QUOTES, 'UTF-8') ?>'">

                            <?php
                                $txRaw = strtolower((string) ($bien['type_transaction'] ?? ''));
                                if ($txRaw === '') {
                                    $txRaw = 'vente';
                                }
                                $isLocation = $txRaw === 'location';
                            ?>
                            <span class="bien-badge <?= $isLocation ? 'location' : 'vente' ?>">
                                <?= $isLocation ? 'Location' : 'Vente' ?>
                            </span>

                        </div>

                        <div class="bien-card__body">

                            <h3 class="bien-card__title">
                                <a href="/biens/<?= htmlspecialchars($bien['slug']) ?>">
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
                                <?php if ($isLocation): ?>
                                    <span>/mois</span>
                                <?php endif; ?>
                            </div>

                            <a href="/biens/<?= htmlspecialchars($bien['slug']) ?>" class="btn btn--primary" style="width:100%;">
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
