<?php
$currentModule = $module ?? 'construire';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$moduleExists = static function (string $moduleName): bool {
    $modulePath = ROOT_PATH . '/modules/' . $moduleName . '/accueil.php';
    return is_file($modulePath);
};

$menuGroups = [
    'Pilotage' => [
        ['module' => 'dashboard',   'label' => 'Tableau de bord',   'hint' => 'Vue d\'ensemble',            'icon' => 'fas fa-gauge-high'],
        ['module' => 'commencer',   'label' => 'Commencer ici',     'hint' => 'Par où démarrer',            'icon' => 'fas fa-rocket'],
        ['module' => 'construire',  'label' => 'Construire',        'hint' => 'Poser les bases',            'icon' => 'fas fa-layer-group'],
        ['module' => 'onboarding',  'label' => 'Onboarding',        'hint' => 'Activation guidée',          'icon' => 'fas fa-route'],
        ['module' => 'attirer',     'label' => 'Attirer',           'hint' => 'Générer des vendeurs',       'icon' => 'fas fa-bullseye'],
        ['module' => 'capture',     'label' => 'Capture',           'hint' => 'Transformer en contacts',    'icon' => 'fas fa-magnet', 'aliases' => ['capturer']],
        ['module' => 'convertir',   'label' => 'Convertir',         'hint' => 'Transformer en clients',     'icon' => 'fas fa-arrow-trend-up'],
        ['module' => 'optimiser',   'label' => 'Optimiser',         'hint' => 'Améliorer les résultats',    'icon' => 'fas fa-chart-line'],
    ],
    'Attirer' => [
        ['module' => 'seo',         'label' => 'SEO',               'hint' => 'Positionnement Google',      'icon' => 'fas fa-magnifying-glass-chart'],
        ['module' => 'gmb',         'label' => 'Google My Business','hint' => 'Avis et visibilité',         'icon' => 'fab fa-google'],
        ['module' => 'social',      'label' => 'Social',            'hint' => 'Publications & réseaux',     'icon' => 'fas fa-share-nodes'],
        ['module' => 'blog',        'label' => 'Rédaction',         'hint' => 'Articles, campagnes, posts', 'icon' => 'fas fa-pen-to-square'],
    ],
    'Capture' => [
        ['module' => 'messagerie',  'label' => 'Messagerie',        'hint' => 'Emails & contacts',          'icon' => 'fas fa-envelope'],
        ['module' => 'financement', 'label' => 'Demandes de financement', 'hint' => 'Leads financement',  'icon' => 'fas fa-hand-holding-dollar'],
        ['module' => 'biens',       'label' => 'Biens',             'hint' => 'Gestion du portefeuille',    'icon' => 'fas fa-house'],
    ],
    'Outils' => [
        ['module' => 'assistant',   'label' => 'Assistant IA',      'hint' => 'IA à votre service',         'icon' => 'fas fa-robot'],
    ],
    'Compte' => [
        ['module' => 'parametres',  'label' => 'Paramètres',        'hint' => 'Compte et préférences',      'icon' => 'fas fa-gear'],
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
            ?>
            <li class="nav-section-label"><?= htmlspecialchars($sectionLabel) ?></li>
            <?php foreach ($visibleItems as $item):
                $targetUrl = $item['url'] ?? ('/admin?module=' . rawurlencode($item['module']));
                $aliases = $item['aliases'] ?? [];
                $isActive = isset($item['url'])
                    ? (rtrim($currentPath, '/') === rtrim((string) $item['url'], '/'))
                    : ($currentModule === $item['module'] || in_array($currentModule, $aliases, true));
            ?>
                <li>
                    <a href="<?= htmlspecialchars($targetUrl) ?>"
                       class="menu-item<?= $isActive ? ' active' : '' ?>"
                       data-module="<?= htmlspecialchars($item['module']) ?>"
                       data-tooltip="<?= htmlspecialchars($item['label']) ?>">
                        <span class="menu-icon"><i class="<?= htmlspecialchars($item['icon']) ?>"></i></span>
                        <span class="menu-label"><?= htmlspecialchars($item['label']) ?>
                            <?php if ($item['module'] === 'messagerie'): ?>
                                <?php
                                try {
                                    static $msgUnread = null;
                                    if ($msgUnread === null) {
                                        require_once ROOT_PATH . '/modules/messagerie/repositories/MessageRepository.php';
                                        $msgRepo = new MessageRepository(db());
                                        $msgUnread = $msgRepo->getTotalUnread((int)(Auth::user()['id'] ?? 1));
                                    }
                                    if ($msgUnread > 0): ?>
                                        <span style="background:#ef4444;color:#fff;border-radius:999px;font-size:.6rem;padding:1px 5px;margin-left:4px;font-weight:700;"><?= (int)$msgUnread ?></span>
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
