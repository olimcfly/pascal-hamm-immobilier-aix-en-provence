<?php
$currentUri = $currentUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navItems = [
    [
        'path' => '/biens',
        'href' => url('/biens'),
        'label' => 'Nos biens',
        'sub' => [
            ['path' => '/biens', 'href' => url('/biens'), 'label' => 'Tous les biens'],
            ['path' => '/biens', 'href' => url('/biens?type=maison'), 'label' => 'Maisons'],
            ['path' => '/biens', 'href' => url('/biens?type=appartement'), 'label' => 'Appartements'],
            ['path' => '/biens', 'href' => url('/biens?gamme=prestige'), 'label' => 'Prestige'],
            ['path' => '/biens', 'href' => url('/biens?statut=vendu'), 'label' => 'Biens vendus'],
        ],
    ],
    [
        'path' => '/guide-local',
        'href' => url('/guide-local'),
        'label' => 'Secteurs',
        'sub' => [
            ['path' => '/guide-local', 'href' => url('/immobilier/aix-en-provence'), 'label' => 'Aix-en-Provence'],
            ['path' => '/guide-local', 'href' => url('/guide-local'), 'label' => 'Pays d’Aix'],
            ['path' => '/guide-local', 'href' => url('/immobilier/luynes'), 'label' => 'Luynes'],
            ['path' => '/guide-local', 'href' => url('/immobilier/puyricard'), 'label' => 'Puyricard'],
            ['path' => '/guide-local', 'href' => url('/immobilier/venelles'), 'label' => 'Venelles'],
            ['path' => '/guide-local', 'href' => url('/guide-local'), 'label' => 'Tous les secteurs'],
        ],
    ],
    [
        'path' => '/vendre',
        'href' => url('/vendre'),
        'label' => 'Vendre',
        'sub' => [
            ['path' => '/vendre', 'href' => url('/vendre'), 'label' => 'Vendre mon bien'],
            ['path' => '/estimation-gratuite', 'href' => url('/estimation-gratuite'), 'label' => 'Estimation gratuite'],
            ['path' => '/services', 'href' => url('/ressources/guide-vendeur'), 'label' => 'Méthode de vente'],
            ['path' => '/services', 'href' => url('/services'), 'label' => 'Mise en valeur du bien'],
            ['path' => '/services', 'href' => url('/services#faq-vendeur'), 'label' => 'FAQ vendeur'],
        ],
    ],
    [
        'path' => '/acheter',
        'href' => url('/acheter'),
        'label' => 'Acheter',
        'sub' => [
            ['path' => '/acheter', 'href' => url('/acheter'), 'label' => 'Acheter un bien'],
            ['path' => '/biens', 'href' => url('/biens'), 'label' => 'Nos biens disponibles'],
            ['path' => '/acheter', 'href' => url('/ressources/guide-acheteur'), 'label' => 'Accompagnement acquéreur'],
            ['path' => '/acheter', 'href' => url('/contact?sujet=Recherche+personnalisee'), 'label' => 'Recherche personnalisée'],
            ['path' => '/acheter', 'href' => url('/acheter#faq-acheteur'), 'label' => 'FAQ acheteur'],
        ],
    ],
    [
        'path' => '/financement',
        'href' => url('/financement'),
        'label' => 'Financement',
        'sub' => [
            ['path' => '/financement', 'href' => url('/financement'), 'label' => 'Financer mon projet'],
            ['path' => '/financement', 'href' => url('/financement#simulateur'), 'label' => 'Simuler mon budget'],
            ['path' => '/financement', 'href' => url('/financement#acheter-avant-vendre'), 'label' => 'Acheter avant de vendre'],
            ['path' => '/financement', 'href' => url('/financement#faq-financement'), 'label' => 'FAQ financement'],
        ],
    ],
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
    <ul class="nav-mobile__list">
        <?php foreach ($navItems as $item): ?>
        <li class="nav-mobile__item <?= !empty($item['sub']) ? 'has-submenu' : '' ?>">
            <?php if (!empty($item['sub'])): ?>
            <button class="nav-mobile__toggle <?= isActive($item['path'], $currentUri) ?>"
                    type="button"
                    aria-expanded="false">
                <span><?= e($item['label']) ?></span>
                <span class="nav-mobile__caret" aria-hidden="true">▾</span>
            </button>
            <?php if (!empty($item['sub'])): ?>
            <ul class="mobile-sub" hidden>
                <li><a href="<?= e($item['href']) ?>"><?= e($item['label']) ?></a></li>
                <?php foreach ($item['sub'] as $sub): ?>
                <li><a href="<?= e($sub['href']) ?>"><?= e($sub['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php else: ?>
            <a href="<?= e($item['href']) ?>" class="<?= isActive($item['path'], $currentUri) ?>">
                <?= e($item['label']) ?>
            </a>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li class="nav-mobile__cta-wrap">
            <a href="<?= e(url('/estimation-gratuite')) ?>" class="btn btn--primary btn--full">Estimation gratuite</a>
        </li>
        <?php if (APP_PHONE): ?>
        <li style="margin-bottom:.5rem"><a href="tel:<?= e(preg_replace('/\s+/', '', APP_PHONE)) ?>" class="btn btn--outline btn--full">📞 <?= e(APP_PHONE) ?></a></li>
        <?php endif; ?>
    </ul>
</div>
<div class="nav-overlay" id="nav-overlay"></div>
