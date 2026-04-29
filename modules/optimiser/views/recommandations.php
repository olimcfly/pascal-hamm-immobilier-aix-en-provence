<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'recommandations';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-article">
    <p class="opt-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Optimiser</a> › Recommandations IA</p>
    <h1>Recommandations IA</h1>
    <p class="opt-article__lead">Les suggestions automatiques « tout-en-un » arriveront ici. En attendant, concentrez-vous sur les leviers à fort impact déjà disponibles dans l’admin.</p>
    <h2 class="opt-article__h2">Par où commencer</h2>
    <ul class="opt-article__list">
        <li><a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Tableau de bord Analytics</a> : repérez la source de leads la plus productive sur 90 jours.</li>
        <li><a href="/admin?module=seo">Module SEO</a> : pages villes, contenus, et suivi des performances organiques.</li>
        <li><a href="<?= htmlspecialchars($base . '&view=rapport-mensuel', ENT_QUOTES, 'UTF-8') ?>">Rapport mensuel</a> : synthèse partageable avec votre équipe ou vos partenaires.</li>
    </ul>
    <p class="opt-article__tools"><a href="<?= htmlspecialchars($base . '&view=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours d’optimisation (5 étapes)</a></p>
</div>
<style>
.opt-article{width:100%;font-family:system-ui,-apple-system,sans-serif;color:#0f172a;line-height:1.6;display:grid;gap:1rem}
.opt-article__crumb{font-size:.88rem;color:#64748b;margin:0}
.opt-article__crumb a{color:#1a3a5c;text-decoration:none;font-weight:700}
.opt-article__crumb a:hover{text-decoration:underline}
.opt-article h1{margin:0;padding:36px 40px 12px;border-radius:16px 16px 0 0;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:#fff;font-size:28px;font-weight:700;line-height:1.25}
.opt-article__lead{margin:-1rem 0 0;padding:0 40px 28px;border-radius:0 0 16px 16px;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:rgba(255,255,255,.72);font-size:15px}
.opt-article__h2{margin:0;padding:1.1rem 1.15rem .2rem;background:#fff;border:1px solid #e8edf4;border-bottom:0;border-radius:12px 12px 0 0;font-size:1.05rem;font-weight:800;color:#0f2237}
.opt-article__list{margin:-1rem 0 0;padding:1.1rem 1.25rem 1.25rem 2.35rem;background:#fff;border:1px solid #e8edf4;border-top:0;border-radius:0 0 12px 12px;box-shadow:0 1px 6px rgba(0,0,0,.07)}
.opt-article__list li{margin-bottom:.65rem}
.opt-article__list a{color:#1a3a5c;font-weight:700;text-decoration:none}
.opt-article__list a:hover{text-decoration:underline}
.opt-article__tools{margin:0;padding:1rem 1.15rem;background:#fff;border:1px solid #e8edf4;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06);font-size:.9rem;color:#64748b}
.opt-article__tools a{color:#1a3a5c;font-weight:700;text-decoration:none}
.opt-article__tools a:hover{text-decoration:underline}
@media(max-width:700px){.opt-article h1{padding:24px 20px 12px}.opt-article__lead{padding:0 20px 22px}}
</style>
