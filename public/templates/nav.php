<?php
// Définir la fonction isActive() si elle n'est pas déjà définie
if (!function_exists('isActive')) {
    function isActive($path, $currentUri = null) {
        if ($currentUri === null) {
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        return $currentUri === $path || strpos($currentUri, $path) === 0;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [
    [
        'path' => '/biens',
        'href' => url('/biens'),
        'label' => 'Nos biens',
        'sub' => [
            ['path' => '/biens',               'href' => url('/biens'),               'label' => 'Tous les biens'],
            ['path' => '/biens/maisons',       'href' => url('/biens/maisons'),       'label' => 'Maisons'],
            ['path' => '/biens/appartements',  'href' => url('/biens/appartements'),  'label' => 'Appartements'],
            ['path' => '/biens/prestige',      'href' => url('/biens/prestige'),      'label' => 'Prestige'],
            ['path' => '/biens/vendus',        'href' => url('/biens/vendus'),        'label' => 'Biens vendus'],
        ],
    ],
    [
        'path' => '/secteurs',
        'href' => url('/secteurs'),
        'label' => 'Secteurs',
        'sub' => [
            ['path' => '/secteurs/aix-en-provence', 'href' => url('/secteurs/aix-en-provence'), 'label' => 'Aix-en-Provence'],
            ['path' => '/secteurs/pays-d-aix',      'href' => url('/secteurs/pays-d-aix'),      'label' => 'Pays d’Aix'],
            ['path' => '/secteurs/luynes',          'href' => url('/secteurs/luynes'),          'label' => 'Luynes'],
            ['path' => '/secteurs/puyricard',       'href' => url('/secteurs/puyricard'),       'label' => 'Puyricard'],
            ['path' => '/secteurs/venelles',        'href' => url('/secteurs/venelles'),        'label' => 'Venelles'],
            ['path' => '/secteurs',                 'href' => url('/secteurs'),                 'label' => 'Tous les secteurs'],
        ],
    ],
    [
        'path' => '/vendre',
        'href' => url('/vendre'),
        'label' => 'Vendre',
        'sub' => [
            ['path' => '/vendre',               'href' => url('/vendre'),               'label' => 'Vente classique'],
            ['path' => '/estimation-gratuite',  'href' => url('/estimation-gratuite'),  'label' => 'Estimation immobilière'],
            ['path' => '/services',             'href' => url('/services'),             'label' => 'Accompagnement vendeur'],
            ['path' => '/viager',               'href' => url('/viager'),               'label' => 'Viager éthique'],
        ],
    ],
    [
        'path' => '/acheter',
        'href' => url('/acheter'),
        'label' => 'Acheter',
        'sub' => [
            ['path' => '/acheter',                           'href' => url('/acheter'),                           'label' => 'Acheter un bien'],
            ['path' => '/financement',                       'href' => url('/financement'),                       'label' => 'Financement'],
            ['path' => '/financement#acheter-avant-vendre', 'href' => url('/financement#acheter-avant-vendre'), 'label' => 'Acheter avant vendre'],
            ['path' => '/faq-acheteurs',                     'href' => url('/faq-acheteurs'),                     'label' => 'FAQ acheteurs'],
        ],
    ],
    [
        'path' => '/financement',
        'href' => url('/financement'),
        'label' => 'Financement',
        'sub' => [
            ['path' => '/financement',                      'href' => url('/financement'),                      'label' => 'Vue d’ensemble'],
            ['path' => '/financement#acheter-avant-vendre', 'href' => url('/financement#acheter-avant-vendre'), 'label' => 'Acheter avant vendre'],
            ['path' => '/financement#faq-financement',      'href' => url('/financement#faq-financement'),      'label' => 'FAQ financement'],
        ],
    ],
];
?>

<nav class="site-nav" id="site-nav" role="navigation" aria-label="Navigation principale">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <?php $active = isActive($item['path'], $currentUri); ?>
        <li class="nav__item <?= !empty($item['sub']) ? 'has-dropdown' : '' ?> <?= $active ? 'active' : '' ?>">
            <a href="<?= htmlspecialchars($item['href']) ?>" class="nav__link <?= $active ? 'active' : '' ?>"
               <?= !empty($item['sub']) ? 'aria-haspopup="true" aria-expanded="false"' : '' ?>>
                <?= htmlspecialchars($item['label']) ?>
                <?php if (!empty($item['sub'])): ?>
                    <span class="nav__arrow" aria-hidden="true">▾</span>
                <?php endif; ?>
            </a>

            <?php if (!empty($item['sub'])): ?>
            <ul class="nav__dropdown" role="menu">
                <?php foreach ($item['sub'] as $sub): ?>
                <li role="none">
                    <a href="<?= htmlspecialchars($sub['href']) ?>" class="dropdown__link <?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>" role="menuitem">
                        <?= htmlspecialchars($sub['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<div class="nav-mobile" id="nav-mobile" aria-hidden="true">
    <button class="nav-mobile__close" id="nav-close" aria-label="Fermer le menu">×</button>

    <ul class="nav-mobile__list">
        <?php foreach ($navItems as $item): ?>
        <li class="nav-mobile__item <?= !empty($item['sub']) ? 'has-submenu' : '' ?>">
            <?php if (!empty($item['sub'])): ?>
            <button class="nav-mobile__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>"
                    type="button"
                    aria-expanded="false">
                <span><?= htmlspecialchars($item['label']) ?></span>
                <span class="nav-mobile__caret" aria-hidden="true">▾</span>
            </button>

            <ul class="mobile-sub" hidden>
                <li>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </li>
                <?php foreach ($item['sub'] as $sub): ?>
                <li>
                    <a href="<?= htmlspecialchars($sub['href']) ?>" class="<?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>">
                        <?= htmlspecialchars($sub['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <a href="<?= htmlspecialchars($item['href']) ?>" class="nav-mobile__link <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                <?= htmlspecialchars($item['label']) ?>
            </a>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>

        <li class="nav-mobile__cta-wrap">
            <a href="<?= htmlspecialchars(url('/estimation-gratuite')) ?>" class="btn btn--primary btn--full">
                Estimation gratuite
            </a>
        </li>

        <?php if (APP_PHONE): ?>
        <li style="margin-bottom:.5rem">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--full">
                📞 <?= htmlspecialchars(APP_PHONE) ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="nav-overlay" id="nav-overlay"></div>
