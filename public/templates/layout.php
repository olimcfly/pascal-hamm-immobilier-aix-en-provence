<?php
$zoneCity            = 'Aix-en-Provence';
$siteMetaDescription = 'Expert immobilier 360° indépendant dans le Pays d\'Aix — achat, vente, estimation, viager.';
$advisorName         = 'Pascal Hamm';
$advisorPhone        = '+33667198366';
$advisorPhoneDisplay = '06 67 19 83 66';
$advisorEmail        = defined('APP_EMAIL') ? APP_EMAIL : '';
$appUrl              = defined('APP_URL')   ? APP_URL   : 'https://pascalhamm.fr';
$appName             = 'Pascal Hamm | Expert Immobilier 360°';
$contactAddress      = trim((string) setting('contact_address', defined('APP_ADDRESS') ? APP_ADDRESS : ''));
$contactPhone        = trim((string) setting('contact_phone', defined('APP_PHONE') ? APP_PHONE : ''));
$requestUri          = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
$gaMeasurementId     = trim((string) setting('google_analytics_id', ''));

$noindexPaths = [
    '/merci',
    '/merci-estimation',
    '/tag',
    '/author',
    '/category',
    '/wp-content',
    '/wp-admin',
    '/compte',
];

foreach ($noindexPaths as $pathPattern) {
    if (str_starts_with($requestUri, $pathPattern)) {
        $metaRobots = 'noindex, nofollow';
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $appName) ?></title>
    <meta name="description" content="<?= e($metaDesc ?? $siteMetaDescription) ?>">
    <meta name="robots"      content="<?= e($metaRobots ?? 'index, follow') ?>">
    <link rel="canonical"    href="<?= e($canonical ?? $appUrl . $requestUri) ?>">
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">

    <!-- Open Graph -->
    <meta property="og:title"       content="<?= e($pageTitle ?? $appName) ?>">
    <meta property="og:description" content="<?= e($metaDesc ?? ('Expert immobilier à ' . $zoneCity)) ?>">
    <meta property="og:type"        content="<?= e($ogType ?? 'website') ?>">
    <meta property="og:url"         content="<?= e($appUrl . $requestUri) ?>">
    <meta property="og:locale"      content="fr_FR">
    <meta property="og:site_name"   content="<?= e($appName) ?>">
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image"     content="<?= e($ogImage) ?>">
    <meta property="og:image:alt" content="<?= e($pageTitle ?? $appName) ?>">
    <?php endif; ?>

    <?php if ($gaMeasurementId !== ''): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaMeasurementId) ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= e($gaMeasurementId) ?>');
    </script>
    <?php endif; ?>

    <!-- Pagination SEO -->
    <?php if (!empty($prevPage)): ?><link rel="prev" href="<?= e($prevPage) ?>"><?php endif; ?>
    <?php if (!empty($nextPage)): ?><link rel="next" href="<?= e($nextPage) ?>"><?php endif; ?>

    <!-- JSON-LD : RealEstateAgent -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateAgent",
        "name": "<?= e($advisorName) ?>",
        "description": "<?= e($siteMetaDescription) ?>",
        "url": "<?= e($appUrl) ?>",
        "telephone": "<?= e($advisorPhone) ?>",
        "email": "<?= e($advisorEmail) ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "<?= e($zoneCity) ?>",
            "addressCountry": "FR"
        },
        "areaServed": {
            "@type": "City",
            "name": "<?= e($zoneCity) ?>"
        }
        <?php if (!empty($jsonLd)): ?>,<?= $jsonLd ?><?php endif; ?>
    }
    </script>

    <!-- JSON-LD : LocalBusiness -->
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $advisorName,
        'description' => $siteMetaDescription,
        'url' => $appUrl,
        'telephone' => $contactPhone !== '' ? $contactPhone : $advisorPhone,
        'email' => $advisorEmail,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $contactAddress !== '' ? $contactAddress : 'Aix-en-Provence, France',
            'addressLocality' => $zoneCity,
            'postalCode' => '13100',
            'addressRegion' => 'Provence-Alpes-Côte d’Azur',
            'addressCountry' => 'FR',
        ],
        'openingHoursSpecification' => [
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'opens' => '09:00',
                'closes' => '19:00',
            ],
            [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => 'Saturday',
                'opens' => '10:00',
                'closes' => '17:00',
            ],
        ],
        'areaServed' => [
            '@type' => 'AdministrativeArea',
            'name' => 'Aix-en-Provence',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= e(asset_url('/assets/css/main.css')) ?>">
    <?php foreach ($extraCss ?? [] as $css): ?>
    <link rel="stylesheet" href="<?= e(asset_url($css)) ?>">
    <?php endforeach; ?>

    <style>
        .role-topbar { position: fixed; top: 0; left: 0; width: 100%; z-index: 9999; padding: .5rem 1.25rem; font-weight: 700; font-family: Inter, Arial, sans-serif; font-size: .875rem; }
        .topbar-admin { background: #1a73e8; color: #fff; }
        .topbar-superadmin { background: linear-gradient(90deg, #b8860b, #ffd700); color: #1a1a1a; }
        .role-topbar a { color: inherit; text-decoration: underline; }
        body.has-role-topbar { padding-top: 2.625rem; }

        .session-access-modal { position: fixed; inset: 0; z-index: 10000; background: rgba(15,23,42,.45); display: flex; align-items: center; justify-content: center; padding: 1.25rem; }
        .session-access-modal__card { background: #fff; border-radius: .75rem; padding: 1.125rem; max-width: 26.25rem; width: 100%; box-shadow: 0 .9375rem 2.8125rem rgba(0,0,0,.25); }
        .session-access-modal__card h3 { margin: 0 0 .5rem; }
        .session-access-modal__card p  { margin: 0 0 .875rem; color: #334155; }
        .session-access-modal__actions { display: flex; gap: .625rem; }
        .session-access-modal__actions button { border: 0; border-radius: .5rem; padding: .625rem .875rem; cursor: pointer; font-weight: 600; background: #2563eb; color: #fff; min-height: 3rem; }
        .session-access-modal__actions button.danger { background: #dc2626; }
    </style>

</head>
<body class="<?= e($bodyClass ?? '') ?>">

<?php require ROOT_PATH . '/includes/layout/header.php'; ?>

<main id="main-content">
    <?php $flash = Session::getFlash(); if ($flash): ?>
    <div class="flash flash--<?= e($flash['type']) ?>"
         role="alert" aria-live="assertive" aria-atomic="true">
        <span><?= e($flash['message']) ?></span>
        <button class="flash__close"
                onclick="this.parentElement.remove()"
                aria-label="Fermer">×</button>
    </div>
    <?php endif; ?>

    <?= $pageContent ?? '' ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<script>
(function(){
    if (document.querySelector('.role-topbar'))
        document.body.classList.add('has-role-topbar');
})();
</script>

<script>
window.__APP_SETTINGS__ = {
    advisorName : <?= json_encode($advisorName, JSON_UNESCAPED_UNICODE) ?>,
    advisorPhone: <?= json_encode($advisorPhoneDisplay, JSON_UNESCAPED_UNICODE) ?>,
    zoneCity    : <?= json_encode($zoneCity, JSON_UNESCAPED_UNICODE) ?>
};
</script>

<!-- JS -->
<script src="<?= e(asset_url('/assets/js/main.js')) ?>" defer></script>
<?php foreach ($extraJs ?? [] as $js): ?>
<script src="<?= e(asset_url($js)) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>
