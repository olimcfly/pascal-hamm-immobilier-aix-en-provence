<?php
$currentModule = $module ?? 'dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$moduleExists = static function (string $moduleName): bool {
    $modulePath = ROOT_PATH . '/modules/' . $moduleName . '/index.php';
    return is_file($modulePath);
};

$menuGroups = [

    'Tableau de bord' => [
        ['module' => 'dashboard',      'label' => 'Tableau de bord',    'hint' => 'Vue d\'ensemble',             'icon' => 'fas fa-gauge-high'],
    ],

    'Assistant IA' => [
        ['module' => 'assistant',      'label' => 'Assistant IA',       'hint' => 'IA à votre service',          'icon' => 'fas fa-robot', 'featured' => true],
    ],

    'Démarrer' => [
        ['module' => 'commencer',      'label' => 'Commencer ici',      'hint' => 'Guide de démarrage',          'icon' => 'fas fa-rocket'],
       
    ],

    'Stratégie' => [
        ['module' => 'construire',     'label' => 'Construire',         'hint' => 'Positionnement & message',    'icon' => 'fas fa-layer-group'],
    ],

    'Visibilité' => [
        ['module' => 'seo',            'label' => 'SEO',                'hint' => 'Apparaître sur Google',       'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'gmb',            'label' => 'Google My Business', 'hint' => 'Avis et visibilité locale',   'icon' => 'fab fa-google'],
        ['module' => 'social',         'label' => 'Social',             'hint' => 'Publications & réseaux',      'icon' => 'fas fa-share-nodes'],
        ['module' => 'blog',           'label' => 'Rédaction',          'hint' => 'Articles & contenus',         'icon' => 'fas fa-pen-to-square'],
        ['module' => 'attirer',        'label' => 'Attirer',            'hint' => 'Stratégie de visibilité',     'icon' => 'fas fa-bullseye'],
    ],

    'Acquisition' => [
        ['module' => 'capture',        'label' => 'Capture',            'hint' => 'Formulaires & pop-ups',       'icon' => 'fas fa-magnet',           'aliases' => ['capturer']],
        ['module' => 'landing-pages',  'label' => 'Pages & formulaires','hint' => 'Pages de capture',            'icon' => 'fas fa-file-lines'],
        ['module' => 'funnels',        'label' => 'Funnels',            'hint' => 'Tunnels de conversion',       'icon' => 'fas fa-filter'],
    ],

    'Conversion' => [
        ['module' => 'convertir',      'label' => 'Convertir',          'hint' => 'Transformer en clients',      'icon' => 'fas fa-arrow-trend-up'],
    ],

    'Prospection & Email' => [
        ['module' => 'prospection',    'label' => 'Campagnes email',    'hint' => 'Séquences & automatisation',  'icon' => 'fas fa-paper-plane'],
        ['module' => 'messagerie',     'label' => 'Messagerie',         'hint' => 'Emails & conversations',      'icon' => 'fas fa-envelope'],
    ],

    'Performance' => [
        ['module' => 'optimiser',      'label' => 'Optimiser',          'hint' => 'Analytics & résultats',       'icon' => 'fas fa-chart-line'],
    ],

    'Outils' => [
        ['module' => 'financement',    'label' => 'Financement',        'hint' => 'Demandes de financement',     'icon' => 'fas fa-hand-holding-dollar'],
        ['module' => 'biens',          'label' => 'Biens',              'hint' => 'Catalogue & mandats',         'icon' => 'fas fa-house'],
        ['module' => 'partenaires',    'label' => 'Partenaires',        'hint' => 'Réseau local',                'icon' => 'fas fa-handshake'],
    ],

    'Aide' => [
        ['module' => 'aide',           'label' => 'Centre d\'aide',     'hint' => 'Guides par module',           'icon' => 'fas fa-circle-question'],
        ['module' => 'ai-help-chat',   'label' => 'Chat d\'aide IA',    'hint' => 'Assistant contextuel',        'icon' => 'fas fa-comments'],
    ],

    'Compte' => [
        ['module' => 'parametres',     'label' => 'Paramètres',         'hint' => 'Compte et préférences',       'icon' => 'fas fa-gear'],
        ['module' => 'integrations',   'label' => 'Intégrations',       'hint' => 'Santé des APIs',              'icon' => 'fas fa-plug'],
    ],

];

$authUser = Auth::user();
if (($authUser['role'] ?? '') === 'superadmin') {
    $menuGroups['Compte'][] = ['module' => 'superadmin', 'label' => 'Superadmin', 'hint' => 'Modules & accès live', 'icon' => 'fas fa-crown'];
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
            $isFeaturedSection = ($sectionLabel === 'Assistant IA');
            ?>
            <li class="nav-section-label<?= $isFeaturedSection ? ' nav-section-label--ai' : '' ?>"><?= htmlspecialchars($sectionLabel) ?></li>
            <?php foreach ($visibleItems as $item):
                $targetUrl   = $item['url'] ?? ('/admin?module=' . rawurlencode($item['module']));
                $aliases     = $item['aliases'] ?? [];
                $isFeatured  = !empty($item['featured']);
                $isActive    = isset($item['url'])
                    ? (rtrim($currentPath, '/') === rtrim((string) $item['url'], '/'))
                    : ($currentModule === $item['module'] || in_array($currentModule, $aliases, true));

                $itemClasses = 'menu-item';
                if ($isActive)   $itemClasses .= ' active';
                if ($isFeatured) $itemClasses .= ' menu-item--ai';
            ?>
                <li>
                    <a href="<?= htmlspecialchars($targetUrl) ?>"
                       class="<?= $itemClasses ?>"
                       data-module="<?= htmlspecialchars($item['module']) ?>"
                       data-tooltip="<?= htmlspecialchars($item['label']) ?>">
                        <span class="menu-icon"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                        <span class="menu-label">
                            <?= htmlspecialchars($item['label']) ?>
                            <?php if ($item['module'] === 'messagerie'): ?>
                                <?php
                                try {
                                    static $msgUnread = null;
                                    if ($msgUnread === null) {
                                        require_once ROOT_PATH . '/modules/messagerie/repositories/MessageRepository.php';
                                        $msgRepo   = new MessageRepository(db());
                                        $msgUnread = $msgRepo->getTotalUnread((int)(Auth::user()['id'] ?? 1));
                                    }
                                    if ($msgUnread > 0): ?>
                                        <span class="menu-badge"><?= (int)$msgUnread ?></span>
                                <?php endif;
                                } catch (Throwable) {}
                                ?>
                            <?php endif; ?>
                            <small class="menu-hint"><?= htmlspecialchars($item['hint']) ?></small>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </ul>
</nav>
