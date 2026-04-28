<?php

declare(strict_types=1);

require_once ROOT_PATH . '/core/helpers/services_page_cms.php';

$sv = services_page_cms_merged_state(services_page_cms_load_row());
$pageTitle = $sv['pageTitle'];
$metaDesc = $sv['metaDesc'];
$sc = $sv['sc'];
$heroEyebrow = $sc['hero']['eyebrow'] ?? 'Services';
$heroH1 = $sc['hero']['h1'] ?? 'Mes services';
$heroSubtitle = $sc['hero']['subtitle'] ?? '';
$introTitle = trim((string) ($sc['intro']['title'] ?? ''));
$introText = trim((string) ($sc['intro']['text'] ?? ''));
$ctaTitle = $sc['cta']['title'] ?? 'Commençons votre projet';
$ctaText = $sc['cta']['text'] ?? '';
$ctaBtn = $sc['cta']['button_label'] ?? 'Prendre rendez-vous';
$ctaUrl = $sc['cta']['button_url'] ?? '/prendre-rendez-vous';
$pr = $sc['pricing'] ?? [];
$plabel = trim((string) ($pr['section_label'] ?? '')) !== '' ? (string) $pr['section_label'] : 'Transparence';
$ptitle = trim((string) ($pr['section_title'] ?? '')) !== '' ? (string) $pr['section_title'] : 'Des honoraires clairs';
$psub = trim((string) ($pr['section_subtitle'] ?? '')) !== '' ? (string) $pr['section_subtitle'] : (cms_services_default_content()['pricing']['section_subtitle'] ?? '');
$prows = (isset($pr['rows']) && is_array($pr['rows'])) ? $pr['rows'] : cms_services_default_content()['pricing']['rows'];
$prows = array_filter(
    $prows,
    static function ($r): bool {
        if (!is_array($r)) {
            return false;
        }

        return trim((string) ($r['name'] ?? '')) !== '' || trim((string) ($r['price'] ?? '')) !== '' || trim((string) ($r['desc'] ?? '')) !== '';
    }
);
if ($prows === []) {
    $prows = cms_services_default_content()['pricing']['rows'];
}

$services = services_page_resolved_service_tuples($sc);
?>
<div class="page-header">
    <div class="container">
        <nav class="breadcrumb"><a href="/">Accueil</a><span><?= e($heroEyebrow) ?></span></nav>
        <h1><?= e($heroH1) ?></h1>
        <p><?= e($heroSubtitle) ?></p>
    </div>
</div>
<?php if ($introTitle !== '' || $introText !== ''): ?>
<section class="section" style="padding-top:0;padding-bottom:0">
    <div class="container" style="max-width:48rem">
        <?php if ($introTitle !== ''): ?><p style="font-weight:700;margin:0 0 .75rem;color:var(--clr-text)"><?= e($introTitle) ?></p><?php endif; ?>
        <?php if ($introText !== ''): ?><p style="color:var(--clr-text-muted);margin:0;line-height:1.6"><?= nl2br(e($introText), false) ?></p><?php endif; ?>
    </div>
</section>
<?php endif; ?>
<section class="section">
    <div class="container">
        <?php
        foreach ($services as $i => [$icon, $titre, $desc, $items, $href, $cta]): ?>
        <div class="grid-2 <?= $i % 2 !== 0 ? 'grid-2--reverse' : '' ?>" style="gap:4rem;align-items:center;margin-bottom:5rem" data-animate>
            <div>
                <span class="section-label">Service <?= $i + 1 ?></span>
                <h2 class="section-title"><?= $icon ?> <?= e($titre) ?></h2>
                <p style="color:var(--clr-text-muted);margin-bottom:1.5rem"><?= e($desc) ?></p>
                <ul style="list-style:none;margin-bottom:2rem">
                    <?php foreach ($items as $item): ?>
                    <li style="display:flex;gap:.75rem;margin-bottom:.75rem;font-size:.95rem">
                        <span style="color:var(--clr-success);font-weight:700;flex-shrink:0">✓</span>
                        <?= e($item) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= e($href) ?>" class="btn btn--primary"><?= e($cta) ?></a>
            </div>
            <div>
                <div style="background:linear-gradient(135deg,var(--clr-primary),#0f2644);border-radius:var(--radius-xl);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:5rem">
                    <?= $icon ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Tarifs -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center">
            <span class="section-label"><?= e($plabel) ?></span>
            <h2 class="section-title"><?= e($ptitle) ?></h2>
            <p class="section-subtitle"><?= e($psub) ?></p>
        </div>
        <div class="grid-3" data-animate>
            <?php foreach ($prows as $row):
                if (!is_array($row)) {
                    continue;
                }
                $service = (string) ($row['name'] ?? '');
                $tarif = (string) ($row['price'] ?? '');
                $d = (string) ($row['desc'] ?? '');
                if ($service === '' && $tarif === '' && $d === '') {
                    continue;
                }
                ?>
            <div style="background:var(--clr-white);border-radius:var(--radius-lg);border:1px solid var(--clr-border);padding:2rem;text-align:center">
                <h3 style="margin-bottom:.5rem"><?= e($service) ?></h3>
                <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:var(--clr-accent);margin:.75rem 0"><?= e($tarif) ?></div>
                <p style="font-size:.875rem;color:var(--clr-text-muted);margin:0"><?= e($d) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="cta-banner">
    <div class="container">
        <h2><?= e($ctaTitle) ?></h2>
        <p><?= e($ctaText) ?></p>
        <div class="cta-banner__actions">
            <a href="<?= e($ctaUrl) ?>" class="btn btn--accent btn--lg"><?= e($ctaBtn) ?></a>
        </div>
    </div>
</section>
