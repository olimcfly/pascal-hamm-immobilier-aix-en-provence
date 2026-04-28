<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/services/LocalPartnerService.php';

$pageTitle = 'Guide local & partenaires à Aix-en-Provence';
$metaDesc = 'Découvrez les partenaires locaux recommandés autour d’Aix-en-Provence, filtrez par rayon et visualisez la zone sur Google Maps.';

$service = new LocalPartnerService();
$service->ensureSchema();

$availableRadii = [1, 2, 3, 5, 10, 15];
$selectedRadius = isset($_GET['rayon']) ? (float) $_GET['rayon'] : 5.0;
if (!in_array((int) $selectedRadius, $availableRadii, true)) {
    $selectedRadius = 5.0;
}

$centerLat = isset($_GET['lat']) ? (float) $_GET['lat'] : (float) setting('zone_lat', 43.529742);
$centerLng = isset($_GET['lng']) ? (float) $_GET['lng'] : (float) setting('zone_lng', 5.447427);

$partners = $service->getPublicList($centerLat, $centerLng, $selectedRadius);
$googleMapsApiKey = trim((string) setting('api_google_maps', ''));
?>

<section class="section">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span>Guide local</span></nav>
        <div class="section__header">
            <span class="section-label">Partenaires locaux</span>
            <h1 class="section-title">Guide local autour d’Aix-en-Provence</h1>
            <p class="section-subtitle">Sélectionnez un rayon et visualisez immédiatement les partenaires disponibles dans la zone.</p>
        </div>

        <form method="get" class="filter-bar" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
            <label>Rayon
                <select name="rayon" onchange="this.form.submit()">
                    <?php foreach ($availableRadii as $radius): ?>
                        <option value="<?= $radius ?>" <?= (int) $selectedRadius === $radius ? 'selected' : '' ?>><?= $radius ?> km</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <input type="hidden" name="lat" value="<?= e((string) $centerLat) ?>">
            <input type="hidden" name="lng" value="<?= e((string) $centerLng) ?>">
            <span style="color:#64748b;"><?= count($partners) ?> partenaire(s) trouvé(s)</span>
        </form>

        <div id="guide-local-map" style="height:420px;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#f8fafc;">
            <?php if ($googleMapsApiKey === ''): ?>
                <div style="height:100%;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center;color:#b45309;">Google Maps n’est pas configuré (clé API manquante).</div>
            <?php endif; ?>
        </div>

        <div class="comparatif-cards" style="margin-top:20px;">
            <?php if ($partners === []): ?>
                <article class="comparatif-card"><div class="comparatif-card__nom">Aucun partenaire dans ce rayon</div><div class="comparatif-card__row">Essayez un rayon plus large.</div></article>
            <?php endif; ?>
            <?php foreach ($partners as $partner): ?>
                <article class="comparatif-card">
                    <div class="comparatif-card__nom"><?= e((string) $partner['nom']) ?></div>
                    <div class="comparatif-card__row"><strong><?= e((string) ($partner['categorie'] ?? 'Partenaire local')) ?></strong></div>
                    <div class="comparatif-card__row"><?= e(trim((string) (($partner['adresse'] ?? '') . ', ' . ($partner['code_postal'] ?? '') . ' ' . ($partner['ville'] ?? '')))) ?></div>
                    <div class="comparatif-card__row">Distance : <?= number_format((float) ($partner['distance_km'] ?? 0), 1, ',', ' ') ?> km</div>
                    <div class="comparatif-card__row" style="margin-top:8px;"><a href="/guide-local/<?= e((string) $partner['slug']) ?>" class="btn btn--outline">Voir la fiche</a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($googleMapsApiKey !== ''): ?>
<script>
(function(){
    const center = { lat: <?= json_encode($centerLat) ?>, lng: <?= json_encode($centerLng) ?> };
    const radiusKm = <?= json_encode((float) $selectedRadius) ?>;
    const points = <?= json_encode($partners, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    window.initLocalGuideMap = function () {
        const container = document.getElementById('guide-local-map');
        if (!container || !window.google || !google.maps) return;

        const map = new google.maps.Map(container, {
            center,
            zoom: 12,
            mapTypeControl: false,
            streetViewControl: false,
        });

        const circle = new google.maps.Circle({
            map,
            center,
            radius: radiusKm * 1000,
            fillColor: '#2563eb',
            fillOpacity: 0.12,
            strokeColor: '#2563eb',
            strokeOpacity: 0.65,
            strokeWeight: 2,
        });

        const bounds = new google.maps.LatLngBounds();
        bounds.extend(center);

        new google.maps.Marker({
            position: center,
            map,
            title: 'Point central',
            icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        });

        const info = new google.maps.InfoWindow();
        points.forEach((partner) => {
            const marker = new google.maps.Marker({
                position: { lat: Number(partner.latitude), lng: Number(partner.longitude) },
                map,
                title: partner.nom,
            });

            marker.addListener('click', () => {
                info.setContent(`<strong>${partner.nom}</strong><br>${partner.categorie || ''}<br><a href="/guide-local/${partner.slug}">Voir la fiche</a>`);
                info.open({ map, anchor: marker });
            });

            bounds.extend(marker.getPosition());
        });

        if (points.length > 0) {
            map.fitBounds(bounds, 50);
        }
    };
})();
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= e($googleMapsApiKey) ?>&callback=initLocalGuideMap" async defer></script>
<?php endif; ?>
