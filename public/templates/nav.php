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

// Fonction url() pour générer les URLs
if (!function_exists('url')) {
    function url($path) {
        return $path;
    }
}

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Pages non encore disponibles (affichées en grisé, non cliquables)
$disabledPages = [
    '/vendre',
    '/viager',
    '/acheter',
    '/faq-acheteurs',
    '/services',
    '/secteurs/pays-d-aix',
];

$navItems = [
    [
        'path'  => '/biens',
        'href'  => url('/biens'),
        'label' => 'Nos biens',
        'sub'   => [
            ['path' => '/biens',               'href' => url('/biens'),              'label' => 'Tous les biens'],
            ['path' => '/biens/maisons',       'href' => url('/biens/maisons'),      'label' => 'Maisons'],
            ['path' => '/biens/appartements',  'href' => url('/biens/appartements'), 'label' => 'Appartements'],
            ['path' => '/biens/prestige',      'href' => url('/biens/prestige'),     'label' => 'Prestige'],
            ['path' => '/biens/vendus',        'href' => url('/biens/vendus'),       'label' => 'Biens vendus'],
        ],
    ],
    [
        'path'  => '/secteurs',
        'href'  => url('/secteurs'),
        'label' => 'Secteurs',
        'sub'   => [
            ['path' => '/secteurs/villes/aix-en-provence', 'href' => url('/secteurs/villes/aix-en-provence'), 'label' => 'Aix-en-Provence'],
            ['path' => '/secteurs/pays-d-aix',             'href' => null,                                   'label' => "Pays d'Aix"],
            ['path' => '/secteurs/quartiers/luynes',       'href' => url('/secteurs/quartiers/luynes'),      'label' => 'Luynes'],
            ['path' => '/secteurs/quartiers/puyricard',    'href' => url('/secteurs/quartiers/puyricard'),   'label' => 'Puyricard'],
            ['path' => '/secteurs/villes/venelles',        'href' => url('/secteurs/villes/venelles'),       'label' => 'Venelles'],
            ['path' => '/secteurs',                        'href' => url('/secteurs'),                       'label' => 'Tous les secteurs'],
        ],
    ],
    [
        'path'  => '/vendre',
        'href'  => null,
        'label' => 'Vendre',
        'sub'   => [
            ['path' => '/vendre',              'href' => null,                          'label' => 'Vente classique'],
            ['path' => '/estimation-gratuite',  'href' => url('/estimation-gratuite'),   'label' => 'Estimation gratuite'],
            ['path' => '/avis-de-valeur',      'href' => url('/avis-de-valeur'),        'label' => 'Avis de valeur'],
            ['path' => '/viager',              'href' => null,                          'label' => 'Vente en viager'],
        ],
    ],
    [
        'path'  => '/acheter',
        'href'  => null,
        'label' => 'Acheter',
        'sub'   => [
            ['path' => '/acheter',    'href' => null,                   'label' => 'Acheter un bien'],
            ['path' => '/financement','href' => url('/financement'),    'label' => 'Financement'],
            ['path' => '/faq-acheteurs', 'href' => null,               'label' => 'FAQ acheteurs'],
        ],
    ],
    [
        'path'  => '/a-propos',
        'href'  => url('/a-propos'),
        'label' => 'À propos',
    ],
    [
        'path'  => '/contact',
        'href'  => url('/contact'),
        'label' => 'Contact',
    ],
];
?>

<nav class="nav" id="nav" aria-label="Menu principal">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <li class="nav__item <?= !empty($item['sub']) ? 'has-submenu' : '' ?>">
            <?php if (!empty($item['sub'])): ?>

            <?php if ($item['href']): ?>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="nav__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <span class="nav__caret" aria-hidden="true">▾</span>
                </a>
            <?php else: ?>
                <button class="nav__toggle <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>"
                        type="button">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <span class="nav__caret" aria-hidden="true">▾</span>
                </button>
            <?php endif; ?>

            <ul class="submenu">
                <?php foreach ($item['sub'] as $sub): ?>
                <li>
                    <?php if ($sub['href'] === null): ?>
                        <span class="disabled-link"><?= htmlspecialchars($sub['label']) ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($sub['href']) ?>"
                           class="<?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>">
                            <?= htmlspecialchars($sub['label']) ?>
                        </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php else: ?>
                <?php if ($item['href'] === null): ?>
                    <span class="nav__link disabled-link <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="nav__link <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>

<!-- Navigation mobile -->
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
                <?php foreach ($item['sub'] as $sub): ?>
                <li>
                    <?php if ($sub['href'] === null): ?>
                        <span class="disabled-link"><?= htmlspecialchars($sub['label']) ?></span>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($sub['href']) ?>"
                           class="<?= isActive($sub['path'], $currentUri) ? 'active' : '' ?>">
                            <?= htmlspecialchars($sub['label']) ?>
                        </a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php else: ?>
                <?php if ($item['href'] === null): ?>
                    <span class="nav-mobile__link disabled-link">
                        <?= htmlspecialchars($item['label']) ?>
                    </span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       class="nav-mobile__link <?= isActive($item['path'], $currentUri) ? 'active' : '' ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>

        <li class="nav-mobile__cta-wrap">
            <a href="<?= htmlspecialchars(url('/avis-de-valeur')) ?>" class="btn btn--outline btn--full" style="margin-bottom:.5rem">
                Avis de valeur
            </a>
            <a href="<?= htmlspecialchars(url('/prendre-rendez-vous')) ?>" class="btn btn--primary btn--full">
                Prendre RDV
            </a>
        </li>

        <?php if (defined('APP_PHONE') && APP_PHONE): ?>
        <li style="margin-bottom:.5rem; padding: 0 1rem;">
            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--full">
                📞 <?= htmlspecialchars(APP_PHONE) ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<div class="nav-overlay" id="nav-overlay"></div>
