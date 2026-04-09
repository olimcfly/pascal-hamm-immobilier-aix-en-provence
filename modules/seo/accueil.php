<?php

declare(strict_types=1);

require_once __DIR__ . '/services/SeoService.php';
require_once __DIR__ . '/services/SitemapService.php';

$pageTitle = 'SEO';
$pageDescription = 'Attirez plus de vendeurs depuis Google';

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
        <h1>Prenez plus de mandats grâce à Google</h1>
        <p class="seo-subtitle">Structurez votre présence locale pour générer des demandes régulières.</p>

        <section class="seo-mother-block" aria-label="Méthode visibilité locale">
            <div class="seo-mother-item">
                <span class="mother-label">Problème utilisateur</span>
                <p>Vos futurs clients vous trouvent trop peu sur votre zone.</p>
            </div>
            <div class="seo-mother-item">
                <span class="mother-label">Logique simple</span>
                <p>Des pages locales, des expressions ciblées, puis un site propre.</p>
            </div>
            <div class="seo-mother-item">
                <span class="mother-label">Bénéfice clair</span>
                <p>Vous augmentez vos demandes locales mois après mois.</p>
            </div>
            <div class="seo-mother-item">
                <span class="mother-label">Action</span>
                <a href="/admin?module=seo&action=ville-edit" class="btn btn-sm">Créer ma première page</a>
            </div>
        </section>

        <div class="seo-grid" id="seo-modules-grid">
            <article class="seo-card seo-card-priority" style="--accent:#10b981;--icon-bg:#d1fae5;">
                <div class="seo-card-head"><span class="icon">📍</span><h3>Créer les pages locales</h3></div>
                <p>Couvrez vos villes clés avec des pages utiles et claires.</p>
                <a href="/admin?module=seo&action=villes" class="btn btn-sm">Ouvrir</a>
                <small><?= (int) $stats['villes_count'] ?> pages / <?= (int) $stats['villes_published'] ?> publiées</small>
            </article>

            <article class="seo-card" style="--accent:#3b82f6;--icon-bg:#dbeafe;">
                <div class="seo-card-head"><span class="icon">🔑</span><h3>Suivre les recherches clés</h3></div>
                <p>Concentrez-vous sur les recherches qui amènent des vendeurs.</p>
                <a href="/admin?module=seo&action=keywords" class="btn btn-sm">Ouvrir</a>
                <small><?= (int) $stats['keywords_count'] ?> expressions suivies</small>
            </article>

            <article class="seo-card" style="--accent:#ef4444;--icon-bg:#fee2e2;">
                <div class="seo-card-head"><span class="icon">🗺️</span><h3>Vérifier la présence sur Google</h3></div>
                <p>Gardez vos pages accessibles pour être trouvable plus vite.</p>
                <a href="/admin?module=seo&action=sitemap" class="btn btn-sm">Ouvrir</a>
                <small><?= (int) ($stats['sitemap_issues_count'] ?? 0) ?> point(s) à corriger</small>
            </article>

            <article class="seo-card" style="--accent:#f59e0b;--icon-bg:#fef3c7;">
                <div class="seo-card-head"><span class="icon">🎯</span><h3>Accélérer le site</h3></div>
                <p>Un site fluide aide vos pages à mieux performer.</p>
                <a href="/admin?module=seo&action=performance" class="btn btn-sm">Ouvrir</a>
                <small>Dernier score : <?= $stats['last_audit_score'] !== null ? (int) $stats['last_audit_score'] . '/100' : 'N/A' ?></small>
            </article>
        </div>

        <section class="seo-cta-final" aria-label="Progression SEO">
            <h2>Progression : Pages locales → Recherches clés → Présence Google → Vitesse</h2>
            <a href="/admin?module=seo&action=ville-edit" class="btn btn-sm">Commencer maintenant</a>
        </section>
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
