<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/services/LocalPartnerService.php';

$slug = (string) ($GLOBALS['guideLocalSlug'] ?? ($slug ?? ''));
$service = new LocalPartnerService();
$service->ensureSchema();
$partner = $service->findBySlug($slug);

if (!$partner) {
    try {
        $stmt = db()->prepare('SELECT nom, description, image, prix_m2, tendance FROM guide_local WHERE slug = :slug AND statut = "publie" LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $legacy = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable) {
        $legacy = null;
    }

    if ($legacy) {
        $pageTitle = 'Guide local — ' . (string) $legacy['nom'];
        $metaDesc = truncate(strip_tags((string) ($legacy['description'] ?? '')), 150);
        ?>
        <section class="section"><div class="container">
            <nav class="breadcrumb"><a href="/">Accueil</a><a href="/guide-local">Guide local</a><span><?= e((string) $legacy['nom']) ?></span></nav>
            <h1><?= e((string) $legacy['nom']) ?></h1>
            <?php if (!empty($legacy['image'])): ?><img src="<?= e((string) $legacy['image']) ?>" alt="<?= e((string) $legacy['nom']) ?>" style="max-width:100%;border-radius:12px;margin:14px 0;"><?php endif; ?>
            <p><?= nl2br(e((string) ($legacy['description'] ?? ''))) ?></p>
        </div></section>
        <?php
        return;
    }

    http_response_code(404);
    $pageTitle = 'Partenaire introuvable';
    ?>
    <section class="section"><div class="container"><h1>404</h1><p>Ce partenaire est introuvable.</p></div></section>
    <?php
    return;
}

$pageTitle = (string) $partner['nom'] . ' — Partenaire local';
$metaDesc = truncate((string) ($partner['description_courte'] ?? ''), 150);
$googleMapsApiKey = trim((string) setting('api_google_maps', ''));
?>

<section class="section">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><a href="/guide-local">Guide local</a><span><?= e((string) $partner['nom']) ?></span></nav>
        <h1><?= e((string) $partner['nom']) ?></h1>
        <p><strong><?= e((string) ($partner['categorie'] ?? 'Partenaire local')) ?></strong></p>
        <p><?= e((string) ($partner['description_longue'] ?: $partner['description_courte'])) ?></p>

        <ul>
            <li>Adresse : <?= e(trim((string) (($partner['adresse'] ?? '') . ', ' . ($partner['code_postal'] ?? '') . ' ' . ($partner['ville'] ?? '')))) ?></li>
            <?php if (!empty($partner['telephone'])): ?><li>Téléphone : <a href="tel:<?= e((string) $partner['telephone']) ?>"><?= e((string) $partner['telephone']) ?></a></li><?php endif; ?>
            <?php if (!empty($partner['site_web'])): ?><li>Site : <a href="<?= e((string) $partner['site_web']) ?>" target="_blank" rel="noopener"><?= e((string) $partner['site_web']) ?></a></li><?php endif; ?>
            <?php if (!empty($partner['google_maps_url'])): ?><li><a href="<?= e((string) $partner['google_maps_url']) ?>" target="_blank" rel="noopener">Ouvrir dans Google Maps</a></li><?php endif; ?>
        </ul>

        <?php if ($googleMapsApiKey !== '' && !empty($partner['latitude']) && !empty($partner['longitude'])): ?>
            <div id="partner-map" style="height:320px;border-radius:12px;border:1px solid #e2e8f0;"></div>
            <script>
            window.initPartnerMap = function(){
                const pos = {lat: <?= (float) $partner['latitude'] ?>, lng: <?= (float) $partner['longitude'] ?>};
                const map = new google.maps.Map(document.getElementById('partner-map'), {center: pos, zoom: 14, mapTypeControl: false});
                new google.maps.Marker({position: pos, map});
            };
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleMapsApiKey) ?>&callback=initPartnerMap" async defer></script>
        <?php endif; ?>
    </div>
</section>
