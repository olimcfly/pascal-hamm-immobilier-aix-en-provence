<?php
$currentModule = $module ?? 'dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$moduleExists = static function (string $moduleName): bool {
    $modulePath = ROOT_PATH . '/modules/' . $moduleName . '/accueil.php';

    return is_file($modulePath);
};

/**
 * Titres de section = ton conversationnel (« ce que vous faites »).
 * Libellés d’entrée = courts pour éviter la coupure en sidebar étroite ; le title reprend la formulation complète.
 */
$menuGroups = [
    'Par ici, c’est clair' => [
        ['module' => 'dashboard', 'label' => 'Vue d’ensemble', 'title' => 'Tableau de bord — votre activité en un coup d’œil', 'icon' => 'fas fa-gauge-high'],
    ],
    'Vous avancez, étape par étape' => [
        ['module' => 'commencer', 'label' => 'Par où commencer', 'title' => 'Démarrer : premiers réglages et repères', 'icon' => 'fas fa-rocket'],
        ['module' => 'positionnement', 'label' => 'Votre position', 'title' => 'Se positionner sur votre marché', 'icon' => 'fas fa-layer-group'],
        ['module' => 'offre', 'label' => 'Ce que vous proposez', 'title' => 'Votre offre et votre promesse', 'icon' => 'fas fa-gift'],
        ['module' => 'attirer', 'label' => 'Être trouvé', 'title' => 'Être visible : SEO et visibilité', 'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'capture', 'label' => 'Attirer des demandes', 'title' => 'Capter des contacts et leads', 'icon' => 'fas fa-magnet', 'aliases' => ['capturer']],
        ['module' => 'convertir', 'label' => 'Conclure', 'title' => 'Signer des clients, conversion', 'icon' => 'fas fa-arrow-trend-up'],
        ['module' => 'optimiser', 'label' => 'Faire mieux', 'title' => 'Améliorer vos résultats', 'icon' => 'fas fa-chart-line'],
    ],
    'Votre site, vos textes' => [
        ['module' => 'cms-hub', 'label' => 'Pages & contenus', 'title' => 'Hub contenu — accès aux modules Blog, CMS…', 'icon' => 'fas fa-file-lines'],
        [
            'url'         => ($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=cms&st=all',
            'label'       => 'Éditer les pages du site',
            'title'       => 'CMS : liste et édition des pages publiées ou en travail',
            'icon'        => 'fas fa-pen-to-square',
            'active_when' => ['module' => 'cms', 'get' => ['st' => 'all']],
        ],
        [
            'url'         => ($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=cms&st=draft',
            'label'       => 'Pages en cours (brouillons)',
            'title'       => 'CMS : pages encore en brouillon, non publiées sur le site',
            'icon'        => 'fas fa-hourglass-half',
            'active_when' => ['module' => 'cms', 'get' => ['st' => 'draft']],
        ],
    ],
    'Vos contacts & suivi' => [
        ['module' => 'crm-hub', 'label' => 'Contacts & suivi', 'title' => 'CRM et contacts', 'icon' => 'fas fa-address-book'],
    ],
    'Relances & visibilité' => [
        ['module' => 'marketing-hub', 'label' => 'Marketing & relances', 'title' => 'Marketing et automatisations', 'icon' => 'fas fa-bolt'],
        [
            'url'        => ($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=email-sequences&filter=automatic',
            'label'      => 'E-mails auto (séquences)',
            'title'      => 'Séquences e-mail déclenchées par les formulaires du site',
            'icon'       => 'fas fa-robot',
            'active_when' => ['module' => 'email-sequences', 'get' => ['filter' => 'automatic']],
        ],
        [
            'url'        => ($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=email-sequences&filter=manual',
            'label'      => 'E-mails manuels (séquences)',
            'title'      => 'Séquences lancées à la main ou hors déclencheur formulaire',
            'icon'       => 'fas fa-hand-pointer',
            'active_when' => ['module' => 'email-sequences', 'get' => ['filter' => 'manual']],
        ],
    ],
    'Biens, secteurs, local' => [
        ['module' => 'properties-hub', 'label' => 'Annonces & secteurs', 'title' => 'Propriétés et secteurs', 'icon' => 'fas fa-house'],
        ['module' => 'scraping', 'label' => 'Import eXp', 'title' => 'Scraping / import de biens (flux eXp France)', 'icon' => 'fas fa-satellite-dish'],
        ['module' => 'scraper', 'label' => 'Scraper web', 'title' => 'Scraper web — crawl et fiches sources', 'icon' => 'fas fa-spider'],
        ['module' => 'annuaire-local', 'label' => 'Annuaire local', 'title' => 'Fiches et annuaire local', 'icon' => 'fas fa-store'],
    ],
    'Boîte à outils' => [
        ['module' => 'outils-hub', 'label' => 'Outils & exports', 'title' => 'Boîte à outils : téléchargements, scripts, ressources', 'icon' => 'fas fa-toolbox'],
        ['module' => 'checklist', 'label' => 'Checklist client', 'title' => 'Checklist de verification partageable avec le client', 'icon' => 'fas fa-list-check'],
    ],
    'Votre compte' => [
        ['module' => 'profil', 'label' => 'Mon profil', 'title' => 'Profil et identité', 'icon' => 'fas fa-user-gear'],
        ['module' => 'site', 'label' => 'Site public', 'title' => 'Aperçu et réglages du site public', 'icon' => 'fas fa-globe'],
        ['module' => 'parametres', 'label' => 'Paramètres', 'title' => 'Paramètres techniques et messagerie', 'icon' => 'fas fa-gear'],
    ],
];

$authUser = Auth::user();
if (($authUser['role'] ?? '') === 'superadmin') {
    $menuGroups['Votre compte'][] = ['module' => 'superadmin', 'label' => 'Superadmin', 'title' => 'Superadmin — modules et accès en direct', 'icon' => 'fas fa-crown'];
}
?>
<nav class="sidebar-nav">
    <ul class="sidebar-menu">
        <?php foreach ($menuGroups as $sectionLabel => $items): ?>
            <?php
            $visibleItems = array_values(array_filter($items, static function (array $item) use ($moduleExists): bool {
                if (isset($item['url'])) {
                    return true;
                }

                return $moduleExists((string) ($item['module'] ?? ''));
            }));

            if ($visibleItems === []) {
                continue;
            }
            ?>
            <li class="sidebar-section-head">
                <span class="nav-section-label"><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></span>
            </li>
            <?php foreach ($visibleItems as $item):
                $targetUrl = $item['url'] ?? (function_exists('admin_url')
                    ? admin_url(['module' => (string) $item['module']])
                    : (($GLOBALS['admin_query_base'] ?? '/admin/?') . 'module=' . rawurlencode((string) $item['module'])));
                $aliases = $item['aliases'] ?? [];
                $isActive = false;
                if (isset($item['active_when']) && is_array($item['active_when'])) {
                    $aw = $item['active_when'];
                    if (($aw['module'] ?? '') === $currentModule) {
                        $isActive = true;
                        if (!empty($aw['get']) && is_array($aw['get'])) {
                            foreach ($aw['get'] as $gKey => $gVal) {
                                $want = (string) $gVal;
                                $got  = isset($_GET[$gKey]) ? (string) $_GET[$gKey] : '';
                                /** @see modules/cms/accueil.php : liste « toutes les pages » = st absent ou all */
                                if ($gKey === 'st' && $want === 'all') {
                                    if ($got !== '' && $got !== 'all') {
                                        $isActive = false;
                                        break;
                                    }
                                } elseif ($got !== $want) {
                                    $isActive = false;
                                    break;
                                }
                            }
                        }
                    }
                } elseif (isset($item['url'])) {
                    $isActive = (rtrim($currentPath, '/') === rtrim((string) $item['url'], '/'));
                } else {
                    $isActive = ($currentModule === $item['module'] || in_array($currentModule, $aliases, true));
                }
                $tooltip = (string) ($item['title'] ?? $item['label'] ?? '');
                ?>
                <li>
                    <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>"
                       class="menu-item<?= $isActive ? ' active' : '' ?>"
                       data-module="<?= htmlspecialchars((string) ($item['module'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       data-tooltip="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>"
                       title="<?= htmlspecialchars($tooltip, ENT_QUOTES, 'UTF-8') ?>"
                       style="position: relative;">
                        <span class="menu-icon"><i class="<?= htmlspecialchars((string) $item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span class="menu-label"><?= htmlspecialchars((string) ($item['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</nav>
