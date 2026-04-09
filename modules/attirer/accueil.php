<?php

declare(strict_types=1);

$pageTitle = 'Attirer';
$pageDescription = 'Générez des vendeurs qualifiés sur votre territoire';

/**
 * @return array<int, array<string, mixed>>
 */
function attirerPublishedArticlesWithPerformance(): array
{
    $pdo = db();
    $user = Auth::user();
    $websiteId = (int) ($user['website_id'] ?? 1);

    try {
        $columnsStmt = $pdo->query('SHOW COLUMNS FROM blog_articles');
        $columns = $columnsStmt ? $columnsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable) {
        return [];
    }

    if (!$columns) {
        return [];
    }

    $viewsColumn = in_array('views_count', $columns, true)
        ? 'views_count'
        : (in_array('vues', $columns, true) ? 'vues' : null);

    $sharesColumn = in_array('shares_count', $columns, true)
        ? 'shares_count'
        : (in_array('partages', $columns, true) ? 'partages' : null);

    $selectViews = $viewsColumn ? "COALESCE(a.{$viewsColumn}, 0)" : '0';
    $selectShares = $sharesColumn ? "COALESCE(a.{$sharesColumn}, 0)" : '0';

    $sql = "
        SELECT
            a.id,
            a.titre,
            a.slug,
            a.date_publication,
            a.updated_at,
            {$selectViews} AS vues,
            {$selectShares} AS partages
        FROM blog_articles a
        WHERE a.website_id = :website_id
          AND a.statut = 'publié'
        ORDER BY COALESCE(a.date_publication, a.updated_at) DESC
        LIMIT 20
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['website_id' => $websiteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable) {
        return [];
    }
}

/**
 * @return array<int, string>
 */
function attirerNoahTopicSuggestions(): array
{
    $zoneCity = trim((string) setting('zone_city', setting('zone_ville', 'votre secteur')));
    $zoneDepartment = trim((string) setting('zone_departement', ''));
    $zoneRegion = trim((string) setting('zone_region', ''));
    $communesRaw = trim((string) setting('zone_communes', ''));

    $communes = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $communesRaw ?: '') ?: [])));
    $communeFocus = $communes[0] ?? $zoneCity;

    $zoneLabel = $zoneCity;
    if ($zoneDepartment !== '') {
        $zoneLabel .= ' (' . $zoneDepartment . ')';
    }

    $contextRegion = $zoneRegion !== '' ? " en {$zoneRegion}" : '';

    return [
        "Prix immobilier 2026 à {$zoneCity} : tendances, délais et marges de négociation",
        "Vendre rapidement à {$zoneCity}{$contextRegion} : 7 actions qui font la différence",
        "Acheter dans {$communeFocus} : quartiers à potentiel et budget moyen par type de bien",
        "Maison ou appartement à {$zoneCity} : que recherchent les acquéreurs actuellement ?",
        "Estimer son bien à {$zoneLabel} : erreurs fréquentes qui font perdre des offres",
        "Investir autour de {$zoneCity} : communes voisines à suivre en priorité",
    ];
}

function renderAttirerHub(): void
{
    ?>
    <div class="page-header">
        <h1><i class="fas fa-bullseye page-icon"></i> HUB <span class="page-title-accent">Attirer</span></h1>
        <p>Générez des vendeurs qualifiés sur votre territoire</p>
    </div>

    <div class="cards-container">

        <div class="card" style="--card-accent:#1abc9c; --card-icon-bg:#e8f8f5;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-magnifying-glass"></i></div>
                <h3 class="card-title">SEO local</h3>
            </div>
            <p class="card-description">Gagnez en visibilité organique sur votre territoire grâce aux fiches villes et aux mots-clés ciblés.</p>
            <div class="card-tags">
                <span class="tag">GMB</span>
                <span class="tag">Mots-clés</span>
                <span class="tag">Fiche ville</span>
            </div>
            <a href="?module=seo" class="card-action"><i class="fas fa-arrow-right"></i> Accéder</a>
        </div>

        <div class="card" style="--card-accent:#e74c3c; --card-icon-bg:#fdedec;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-google"></i></div>
                <h3 class="card-title">Google Ads</h3>
            </div>
            <p class="card-description">Créez des campagnes payantes ciblées avec le wizard 5 étapes assisté par IA.</p>
            <div class="card-tags">
                <span class="tag">Wizard 5 étapes</span>
                <span class="tag">Perplexity IA</span>
            </div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#3b5998; --card-icon-bg:#eaf0fb;">
            <div class="card-header">
                <div class="card-icon"><i class="fab fa-facebook-f"></i></div>
                <h3 class="card-title">Facebook Ads</h3>
            </div>
            <p class="card-description">Diffusez des publicités sociales ciblées sur votre zone de chalandise.</p>
            <div class="card-tags">
                <span class="tag">Ciblage local</span>
                <span class="tag">Lookalike</span>
            </div>
            <span class="card-soon"><i class="fas fa-clock"></i> Arrivée bientôt</span>
        </div>

        <div class="card" style="--card-accent:#f39c12; --card-icon-bg:#fef9e7;">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-pen-nib"></i></div>
                <h3 class="card-title">Contenu & articles</h3>
            </div>
            <p class="card-description">Développez votre autorité locale avec du contenu de blog assisté par IA.</p>
            <div class="card-tags">
                <span class="tag">Noah IA</span>
                <span class="tag">Blog</span>
                <span class="tag">Performance</span>
            </div>
            <a href="?module=attirer&action=contenu-articles" class="card-action"><i class="fas fa-arrow-right"></i> Ouvrir</a>
        </div>

    </div>
    <?php
}

function renderAttirerContentArticles(): void
{
    $articles = attirerPublishedArticlesWithPerformance();
    $topics = attirerNoahTopicSuggestions();
    $zoneCity = trim((string) setting('zone_city', setting('zone_ville', 'votre secteur')));
    $zoneRegion = trim((string) setting('zone_region', ''));
    ?>
    <style>
        .attirer-content-wrap { display:grid; gap:1.25rem; }
        .attirer-toolbar {
            background:#fff; border:1px solid #eef2f7; border-radius:16px;
            padding:1rem 1.2rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;
        }
        .attirer-toolbar .zone { color:#6b7280; font-size:.95rem; }
        .attirer-grid { display:grid; grid-template-columns:1.35fr 1fr; gap:1.25rem; }
        .attirer-card {
            background:#fff; border:1px solid #eef2f7; border-radius:16px; padding:1rem 1.2rem;
            box-shadow:0 6px 18px rgba(15,23,42,.04);
        }
        .attirer-card h2 { margin:0 0 .7rem; font-size:1.08rem; }
        .attirer-table { width:100%; border-collapse:collapse; }
        .attirer-table th, .attirer-table td { padding:.62rem .4rem; border-bottom:1px solid #f1f5f9; text-align:left; font-size:.92rem; }
        .attirer-table th { color:#6b7280; font-weight:600; }
        .metric-pill {
            display:inline-flex; align-items:center; gap:.35rem; padding:.24rem .52rem;
            border-radius:999px; background:#f8fafc; color:#111827; font-weight:600;
        }
        .topic-list { display:grid; gap:.65rem; margin:0; padding:0; list-style:none; }
        .topic-item {
            border:1px solid #f1f5f9; background:#fcfdff; border-radius:12px;
            padding:.72rem .78rem; display:flex; gap:.55rem;
        }
        .topic-item i { color:#f39c12; margin-top:.14rem; }
        .empty { color:#6b7280; font-style:italic; }
        @media (max-width: 1100px) { .attirer-grid { grid-template-columns:1fr; } }
    </style>

    <div class="attirer-content-wrap">
        <div class="page-header" style="margin-bottom:0;">
            <h1><i class="fas fa-pen-nib page-icon"></i> Contenu & <span class="page-title-accent">Articles</span></h1>
            <p>Suivez les performances de vos contenus publiés et planifiez vos prochains sujets avec Noah IA.</p>
        </div>

        <div class="attirer-toolbar">
            <a href="?module=attirer" class="card-action" style="width:auto;"><i class="fas fa-arrow-left"></i> Retour au hub Attirer</a>
            <div class="zone">
                Zone configurée :
                <strong><?= htmlspecialchars($zoneCity !== '' ? $zoneCity : 'Non définie') ?></strong>
                <?= $zoneRegion !== '' ? ' — ' . htmlspecialchars($zoneRegion) : '' ?>
            </div>
        </div>

        <div class="attirer-grid">
            <section class="attirer-card">
                <h2><i class="fas fa-chart-line"></i> Articles publiés & performances</h2>
                <?php if (empty($articles)): ?>
                    <p class="empty">Aucun article publié trouvé pour le moment.</p>
                <?php else: ?>
                    <table class="attirer-table" aria-label="Articles publiés">
                        <thead>
                        <tr>
                            <th>Article</th>
                            <th>Date</th>
                            <th>Vues</th>
                            <th>Partages</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>
                                    <a href="/blog/<?= urlencode((string) $article['slug']) ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars((string) $article['titre']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $dateRef = (string) ($article['date_publication'] ?: $article['updated_at']);
                                    echo $dateRef !== '' ? htmlspecialchars(date('d/m/Y', strtotime($dateRef))) : '—';
                                    ?>
                                </td>
                                <td><span class="metric-pill"><i class="fas fa-eye"></i><?= (int) $article['vues'] ?></span></td>
                                <td><span class="metric-pill"><i class="fas fa-share-nodes"></i><?= (int) $article['partages'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <aside class="attirer-card">
                <h2><i class="fas fa-wand-magic-sparkles"></i> Sujets recommandés par Noah IA</h2>
                <p style="margin-top:0;color:#6b7280;font-size:.92rem;">Suggestions contextualisées selon votre zone géographique configurée.</p>
                <ul class="topic-list">
                    <?php foreach ($topics as $topic): ?>
                        <li class="topic-item">
                            <i class="fas fa-lightbulb"></i>
                            <span><?= htmlspecialchars($topic) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </div>
    <?php
}

function renderContent(): void
{
    $action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? ''));

    if ($action === 'contenu-articles') {
        renderAttirerContentArticles();
        return;
    }

    renderAttirerHub();
}
