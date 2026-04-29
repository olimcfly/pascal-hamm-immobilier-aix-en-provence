<?php

declare(strict_types=1);

$current = 'hub';
require __DIR__ . '/views/_subnav.php';
?>
<style>
.module-start-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:36px 40px;color:#fff;margin-bottom:28px;box-shadow:0 4px 20px rgba(15,34,55,.18)}
.module-start-badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;border:1px solid rgba(201,168,76,.35)}
.module-start-hero h1{font-size:28px;font-weight:700;color:#fff;margin:0 0 12px;line-height:1.25}.module-start-hero p{font-size:15px;color:rgba(255,255,255,.72);line-height:1.65;max-width:680px;margin:0}
.module-start-title{font-size:12px;font-weight:800;color:#8a95a3;text-transform:uppercase;letter-spacing:.07em;margin-bottom:16px}.module-start-steps{display:flex;flex-direction:column;gap:14px}
.module-start-step{display:flex;align-items:flex-start;gap:18px;background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 1px 6px rgba(0,0,0,.07);text-decoration:none;color:inherit;border-left:4px solid var(--accent,#c9a84c);transition:transform .15s,box-shadow .15s}.module-start-step:hover{transform:translateX(4px);box-shadow:0 4px 16px rgba(0,0,0,.1);color:inherit}
.module-start-num{flex-shrink:0;width:36px;height:36px;border-radius:50%;background:var(--icon-bg,#f1f5f9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--accent,#64748b)}.module-start-body{flex:1}.module-start-label{font-size:15px;font-weight:700;color:#1e293b;margin-bottom:3px}.module-start-desc{font-size:13px;color:#64748b;line-height:1.5}.module-start-arrow{flex-shrink:0;color:#c9a84c;font-size:16px;margin-top:8px}
@media(max-width:600px){.module-start-hero{padding:24px 20px}.module-start-step{flex-wrap:wrap}}
</style>
<div class="module-start-hero"><div class="module-start-badge">Optimisation</div><h1>Mesurer et améliorer vos performances</h1><p>Suivez vos KPIs, analysez vos leviers et priorisez les actions qui augmentent réellement vos conversions.</p></div>
<div class="module-start-title">Menu Optimiser</div>
<div class="module-start-steps">
    <a class="module-start-step" href="/admin?module=optimiser&view=parcours" style="--accent:#c9a84c;--icon-bg:#fef9e7;"><div class="module-start-num">1</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-route" style="color:#c9a84c;margin-right:6px;"></i>Parcours d’optimisation</div><div class="module-start-desc">Le guide en 5 étapes pour installer, lire et améliorer votre système d’acquisition.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div></a>
    <a class="module-start-step" href="/admin?module=optimiser&view=analytics" style="--accent:#3498db;--icon-bg:#e3f2fd;"><div class="module-start-num">2</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-chart-bar" style="color:#3498db;margin-right:6px;"></i>Tableau de bord Analytics</div><div class="module-start-desc">Vue consolidée de vos KPIs : leads, estimations et trafic pages si disponible.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div></a>
    <a class="module-start-step" href="/admin?module=optimiser&view=ab-testing" style="--accent:#f39c12;--icon-bg:#fef9e7;"><div class="module-start-num">3</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-vials" style="color:#f39c12;margin-right:6px;"></i>A/B Testing</div><div class="module-start-desc">Testez vos pages, emails et messages pour maximiser les taux de conversion.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div></a>
    <a class="module-start-step" href="/admin?module=optimiser&view=recommandations" style="--accent:#27ae60;--icon-bg:#eafaf1;"><div class="module-start-num">4</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-lightbulb" style="color:#27ae60;margin-right:6px;"></i>Recommandations IA</div><div class="module-start-desc">Pistes d’action et liens vers les outils déjà disponibles.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div></a>
    <a class="module-start-step" href="/admin?module=optimiser&view=rapport-mensuel" style="--accent:#e74c3c;--icon-bg:#fdedec;"><div class="module-start-num">5</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-file-lines" style="color:#e74c3c;margin-right:6px;"></i>Rapport mensuel</div><div class="module-start-desc">Générez votre rapport de performance mensuel en un clic.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div></a>
</div>
