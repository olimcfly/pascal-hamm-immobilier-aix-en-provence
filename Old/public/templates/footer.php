<?php
// Ces vars sont déjà définies par layout.php si le footer est inclus via lui.
// Si inclus indépendamment, on les reconstruit depuis les settings.
if (!isset($advisorName) || $advisorName === '') {
    $_fn = trim((string) setting('advisor_firstname', ''));
    $_ln = trim((string) setting('advisor_lastname',  ''));
    $advisorName = trim($_fn . ' ' . $_ln);
    if ($advisorName === '') {
        $advisorName = defined('APP_NAME')
            ? (string) preg_replace('/\s+Immobilier\b.*/iu', '', APP_NAME)
            : 'Votre Conseiller';
    }
}
$advisorTitle   = trim((string) setting('advisor_title',   'Expert Immobilier'));
$_taglineFallback = isset($zoneCity) && $zoneCity !== ''
    ? "Expert immobilier indépendant à {$zoneCity}. Accompagnement en vente, achat et financement."
    : "Expert immobilier indépendant. Accompagnement en vente, achat et financement.";
$advisorTagline = trim((string) setting('advisor_tagline', $_taglineFallback));

$socialLinks = [
    'Facebook'  => '',  // ex: https://facebook.com/pascalhamm
    'LinkedIn'  => '',  // ex: https://linkedin.com/in/pascalhamm
    'Instagram' => '',  // ex: https://instagram.com/pascalhamm
];

$socialIcons = [
    'Facebook' => '<svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
    'LinkedIn' => '<svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>',
    'Instagram' => '<svg viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
];

$navColumns = [
    'Découvrir' => [
        ['label' => 'Biens à vendre',  'href' => '/biens'],
        ['label' => 'Secteurs',   'href' => '/guide-local'],
        ['label' => 'Acheter',    'href' => '/biens'],
    ],
    'Vendre' => [
        ['label' => 'Vendre un bien',      'href' => '/estimation-gratuite'],
        ['label' => 'Estimation gratuite', 'href' => '/estimation-gratuite'],
        ['label' => 'Méthode de vente',    'href' => '/ressources/guide-vendeur'],
    ],
    'Acheter' => [
        ['label' => 'Acheter un bien',        'href' => '/biens'],
        ['label' => 'Financement',            'href' => '/financement'],
        ['label' => 'Recherche personnalisée','href' => '/contact?sujet=Recherche+personnalisee'],
    ],
    'Entreprise' => [
        ['label' => 'À propos',     'href' => '/a-propos'],
        ['label' => 'Avis clients', 'href' => '/avis-clients'],
        ['label' => 'Contact',      'href' => '/contact'],
    ],
];

$legalLinks = [
    'Mentions légales'  => '/mentions-legales',
    'Confidentialité'   => '/politique-confidentialite',
    'Cookies'           => '/politique-cookies',
    'CGV'               => '/cgv',
    'Plan du site'      => '/plan-du-site',
];
?>

<footer class="site-footer">
    <div class="container footer__grid">

        <!-- Identité -->
        <div class="footer__col footer__brand">
            <a href="/" class="footer__logo">
                <span aria-hidden="true">🏡</span>
                <span>
                    <strong><?= e($advisorName) ?></strong><br>
                    <em><?= e($advisorTitle) ?></em>
                </span>
            </a>
            <p class="footer__tagline"><!-- Coordonnées de contact -->
<div class="footer__contact">
    <p>
        <a href="tel:+33667198366" class="footer__phone">
            <svg viewBox="0 0 24 24" width="16" fill="currentColor" aria-hidden="true">
                <path d="M6.62 10.79c1.44 2.36 3.56 4.32 6.38 6.38l-1.56-1.56a1 1 0 0 1-.12-.24c.36-.6.54-1.26.54-1.98 0-.72-.18-1.38-.54-1.98a1 1 0 0 1-.12-.24l1.56-1.56c2.06-2.82 3.96-5.94 6.38-6.38l-1.56-1.56a1 1 0 0 1-.12-.24C17.3 3.18 16.64 3 15.92 3c-.72 0-1.38.18-1.98.54a1 1 0 0 1-.24-.12L13.4 1.82c-2.46.42-4.32 2.28-6.38 6.38l1.56 1.56a1 1 0 0 1 .12.24c.36.6.54 1.26.54 1.98 0 .72-.18 1.38-.54 1.98a1 1 0 0 1 .12.24l-1.56 1.56z"/>
            </svg>
            +33 6 67 19 83 66
        </a>
    </p>
    <p>
        <a href="mailto:contact@pascal-hamm-immobilier-aix-en-provence.fr" class="footer__email">
            <svg viewBox="0 0 24 24" width="16" fill="currentColor" aria-hidden="true">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
            contact@pascal-hamm-immobilier-aix-en-provence.fr
        </a>
    </p>
    <p class="footer__rsac">
        <span>RSAC : 441887536</span>
    </p>
</div>
<?= e($advisorTagline) ?></p>

            <!-- Réseaux sociaux — n'affiche que ceux renseignés -->
            <?php $filledSocials = array_filter($socialLinks); ?>
            <?php if (!empty($filledSocials)): ?>
                <div class="footer__social">
                    <?php foreach ($filledSocials as $name => $url): ?>
                        <a href="<?= e($url) ?>"
                           class="social-link"
                           aria-label="<?= e($name) ?>"
                           target="_blank"
                           rel="noopener noreferrer">
                            <?= $socialIcons[$name] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Colonnes de navigation -->
        <?php foreach ($navColumns as $title => $links): ?>
            <div class="footer__col">
                <h3 class="footer__title"><?= e($title) ?></h3>
                <ul class="footer__links">
                    <?php foreach ($links as $link): ?>
                        <li>
                            <a href="<?= e($link['href']) ?>">
                                <?= e($link['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Bas de footer -->
    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <p>
                &copy; <?= date('Y') ?> <?= e($advisorName) ?> — Tous droits réservés
                <?= defined('APP_SIRET') && APP_SIRET ? ' — SIRET&nbsp;: ' . e(APP_SIRET) : '' ?>.
            </p>
            <nav aria-label="Liens légaux">
                <?php foreach ($legalLinks as $label => $href): ?>
                    <a href="<?= e($href) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
                <a href="/admin" style="opacity:.2;font-size:.7rem;color:inherit;margin-left:.5rem" tabindex="-1" aria-hidden="true">·</a>
            </nav>
        </div>
    </div>
</footer>
