<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 1</p>
    <h1>Installer et fiabiliser les analytics</h1>
    <p class="opt-article__lead">Sans mesure cohérente, impossible d’optimiser. Cette étape pose les bases techniques et sémantiques.</p>
    <ul class="opt-article__list">
        <li><strong>Google Analytics 4</strong> : propriété dédiée au site, flux web + (si besoin) flux app. Vérifiez que les événements <code>generate_lead</code> ou équivalents remontent bien lors d’une prise de contact.</li>
        <li><strong>Pixel Meta</strong> (si campagnes social) : même définition d’événement « lead » pour comparer les coûts aux conversions CRM.</li>
        <li><strong>UTM systématiques</strong> sur les liens partagés (emailing, réseaux, partenaires) pour attribuer les leads aux bons canaux.</li>
    </ul>
    <p class="opt-article__next"><a href="<?= htmlspecialchars($base . '&view=etape-kpis', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : définir les KPIs →</a></p>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Ouvrir le tableau de bord Analytics</a> · <a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Retour au parcours</a></p>
</div>
<style>
.opt-article{width:100%;font-family:system-ui,-apple-system,sans-serif;color:#0f172a;line-height:1.6;display:grid;gap:1rem}
.opt-article__crumb{font-size:.88rem;color:#64748b;margin:0}
.opt-article__crumb a{color:#1a3a5c;text-decoration:none;font-weight:700}
.opt-article__crumb a:hover{text-decoration:underline}
.opt-article h1{margin:0;padding:36px 40px 12px;border-radius:16px 16px 0 0;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:#fff;font-size:28px;font-weight:700;line-height:1.25}
.opt-article__lead{margin:-1rem 0 0;padding:0 40px 28px;border-radius:0 0 16px 16px;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:rgba(255,255,255,.72);font-size:15px}
.opt-article__list{margin:0;padding:1.25rem 1.25rem 1.25rem 2.35rem;background:#fff;border:1px solid #e8edf4;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.07)}
.opt-article__list li{margin-bottom:.65rem}
.opt-article__next,.opt-article__tools{margin:0;padding:1rem 1.15rem;background:#fff;border:1px solid #e8edf4;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06)}
.opt-article__next a{font-weight:800;color:#1a3a5c;text-decoration:none}
.opt-article__next a:hover{text-decoration:underline}
.opt-article__tools{font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#1a3a5c;font-weight:700;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
@media(max-width:700px){.opt-article h1{padding:24px 20px 12px}.opt-article__lead{padding:0 20px 22px}}
</style>
