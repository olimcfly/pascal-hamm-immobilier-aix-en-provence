<?php
$currentUri = $currentUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [
    ['path' => '/',           'href' => url('/'),           'label' => 'Accueil'],
    ['path' => '/biens',      'href' => url('/biens'),      'label' => 'Biens à vendre'],
    ['path' => '/secteurs',   'href' => url('/secteurs'),   'label' => 'Secteurs'],
    [
        'path' => '/vendre',
        'href' => url('/vendre'),
        'label' => 'Vendre',
        'sub' => [
            ['path' => '/vendre',              'href' => url('/vendre'),              'label' => 'Vente classique'],
            ['path' => '/estimation-gratuite', 'href' => url('/estimation-gratuite'), 'label' => 'Estimation immobilière'],
            ['path' => '/services',            'href' => url('/services'),            'label' => 'Accompagnement vendeur'],
            ['path' => '/viager',              'href' => url('/viager'),              'label' => 'Viager éthique'],
        ],
    ],
    ['path' => '/acheter',    'href' => url('/acheter'),    'label' => 'Acheter'],
    ['path' => '/a-propos',   'href' => url('/a-propos'),   'label' => 'À propos'],
    ['path' => '/contact',    'href' => url('/contact'),    'label' => 'Contact'],
    ['path' => '/estimation-gratuite', 'href' => url('/estimation-gratuite'), 'label' => 'Estimation gratuite'],
];
?>
<nav class="site-nav" id="site-nav" role="navigation" aria-label="Navigation principale">
    <ul class="nav__list">
        <?php foreach ($navItems as $item): ?>
        <?php $active = isActive($item['path'], $currentUri); ?>
        <li class="nav__item <?= !empty($item['sub']) ? 'has-dropdown' : '' ?> <?= $active ?>">
            <a href="<?= e($item['href']) ?>" class="nav__link <?= $active ?>"
               <?= !empty($item['sub']) ? 'aria-haspopup="true" aria-expanded="false"' : '' ?>>
                <?= e($item['label']) ?>
                <?php if (!empty($item['sub'])): ?><span class="nav__arrow" aria-hidden="true">▾</span><?php endif; ?>
            </a>
            <?php if (!empty($item['sub'])): ?>
            <ul class="nav__dropdown" role="menu">
                <?php foreach ($item['sub'] as $sub): ?>
                <li role="none">
                    <a href="<?= e($sub['href']) ?>" class="dropdown__link" role="menuitem">
                        <?= e($sub['label']) ?>
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
    <ul>
        <?php foreach ($navItems as $item): ?>
        <li>
            <a href="<?= e($item['href']) ?>" class="<?= isActive($item['path'], $currentUri) ?>">
                <?= e($item['label']) ?>
            </a>
            <?php if (!empty($item['sub'])): ?>
            <ul class="mobile-sub">
                <?php foreach ($item['sub'] as $sub): ?>
                <li><a href="<?= e($sub['href']) ?>"><?= e($sub['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php if (APP_PHONE): ?>
        <li style="margin-bottom:.5rem"><a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--full">📞 <?= e(APP_PHONE) ?></a></li>
        <?php endif; ?>
    </ul>
</div>
<div class="nav-overlay" id="nav-overlay"></div>
