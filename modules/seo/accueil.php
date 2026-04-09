<?php

declare(strict_types=1);

require_once __DIR__ . '/services/SeoService.php';
require_once __DIR__ . '/services/SitemapService.php';

$pageTitle = 'SEO';
$pageDescription = 'Pilotez votre référencement local';

$user = Auth::user();
$userId = (int) ($user['id'] ?? 0);
$seoService = new SeoService(db());
$stats = $seoService->getHubStats($userId);
$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'index'));

function renderSeoHub(array $stats): void
{
    ?>
    <div class="seo-hub">
        <div class="seo-breadcrumb">Accueil › SEO</div>
        <h1>🔍 HUB SEO</h1>
        <p class="seo-subtitle">Pilotez vos mots-clés, vos fiches villes, votre sitemap et la performance technique.</p>

        <div class="seo-search-wrap">
            <input type="text" id="seo-module-search" placeholder="Rechercher un module SEO…" oninput="filterModules(this.value)">
        </div>

        <div class="seo-grid" id="seo-modules-grid">
            <article class="seo-card" data-module="mots-clés top 10 positions" style="--accent:#3b82f6;--icon-bg:#dbeafe;">
                <div class="seo-card-head"><span class="icon">🔑</span><h3>Mots-clés</h3></div>
                <p>Suivez le positionnement de vos mots-clés cibles sur Google.</p>
                <div class="badges"><span>Top 10</span><span>Positions</span></div>
                <a href="/admin?module=seo&action=keywords" class="btn btn-sm">Consulter</a>
                <small><?= (int) $stats['keywords_count'] ?> mots-clés suivis</small>
            </article>

            <article class="seo-card" data-module="fiches villes local seo communes" style="--accent:#10b981;--icon-bg:#d1fae5;">
                <div class="seo-card-head"><span class="icon">📍</span><h3>Fiches villes</h3></div>
                <p>Pages optimisées pour chaque commune de votre territoire.</p>
                <div class="badges"><span>Local SEO</span><span>Communes</span></div>
                <a href="/admin?module=seo&action=villes" class="btn btn-sm">Gérer</a>
                <small><?= (int) $stats['villes_count'] ?> fiches / <?= (int) $stats['villes_published'] ?> publiées</small>
            </article>

            <article class="seo-card" data-module="sitemap indexation gsc" style="--accent:#ef4444;--icon-bg:#fee2e2;">
                <div class="seo-card-head"><span class="icon">🗺️</span><h3>Sitemap</h3></div>
                <p>Générez et contrôlez votre sitemap XML avant envoi à Google.</p>
                <div class="badges"><span>Indexation</span><span><?= htmlspecialchars((string) ($stats['sitemap_status'] ?? 'idle')) ?></span></div>
                <a href="/admin?module=seo&action=sitemap" class="btn btn-sm">Gérer</a>
                <small>Dernière génération : <?= $stats['sitemap_last_generated'] ? htmlspecialchars((string) $stats['sitemap_last_generated']) : 'Jamais' ?> · <?= (int) ($stats['sitemap_issues_count'] ?? 0) ?> alerte(s)</small>
            </article>

            <article class="seo-card" data-module="performance technique core web vitals vitesse" style="--accent:#f59e0b;--icon-bg:#fef3c7;">
                <div class="seo-card-head"><span class="icon">🎯</span><h3>Performance technique</h3></div>
                <p>Vitesse, Core Web Vitals et audit technique de votre site.</p>
                <div class="badges"><span>Core Web Vitals</span><span>Vitesse</span></div>
                <a href="/admin?module=seo&action=performance" class="btn btn-sm">Auditer</a>
                <small>Dernier score : <?= $stats['last_audit_score'] !== null ? (int) $stats['last_audit_score'] . '/100' : 'N/A' ?></small>
            </article>
        </div>
    </div>
    <?php
}

function renderContent(): void
{
    global $action, $stats;

    echo '<link rel="stylesheet" href="/admin/assets/css/seo.css?v=' . (int) @filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/css/seo.css') . '">';

    if ($action === 'sitemap') {
        require __DIR__ . '/sitemap/index.php';
    } elseif ($action === 'villes') {
        require __DIR__ . '/fiches-villes.php';
    } elseif ($action === 'performance') {
        require __DIR__ . '/performance.php';
    } else {
        renderSeoHub($stats);
    }

    echo '<script src="/admin/assets/js/seo.js?v=' . (int) @filemtime($_SERVER['DOCUMENT_ROOT'] . '/admin/assets/js/seo.js') . '"></script>';
}
