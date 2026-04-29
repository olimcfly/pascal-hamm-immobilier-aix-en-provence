<?php

declare(strict_types=1);

$current = 'hub';
require __DIR__ . '/_subnav.php';
?>
<style>
.module-start-hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:16px;padding:36px 40px;color:#fff;margin-bottom:28px;box-shadow:0 4px 20px rgba(15,34,55,.18)}
.module-start-badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;border:1px solid rgba(201,168,76,.35)}
.module-start-hero h1{font-size:28px;font-weight:700;color:#fff;margin:0 0 12px;line-height:1.25}
.module-start-hero p{font-size:15px;color:rgba(255,255,255,.72);line-height:1.65;max-width:680px;margin:0}
.module-start-title{font-size:12px;font-weight:800;color:#8a95a3;text-transform:uppercase;letter-spacing:.07em;margin-bottom:16px}
.module-start-steps{display:flex;flex-direction:column;gap:14px}
.module-start-step{display:flex;align-items:flex-start;gap:18px;background:#fff;border-radius:12px;padding:20px 22px;box-shadow:0 1px 6px rgba(0,0,0,.07);text-decoration:none;color:inherit;border-left:4px solid var(--accent,#c9a84c);transition:transform .15s,box-shadow .15s}
.module-start-step:hover{transform:translateX(4px);box-shadow:0 4px 16px rgba(0,0,0,.1);color:inherit}
.module-start-num{flex-shrink:0;width:36px;height:36px;border-radius:50%;background:var(--icon-bg,#f1f5f9);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:var(--accent,#64748b)}
.module-start-body{flex:1}.module-start-label{font-size:15px;font-weight:700;color:#1e293b;margin-bottom:3px}.module-start-desc{font-size:13px;color:#64748b;line-height:1.5}.module-start-arrow{flex-shrink:0;color:#c9a84c;font-size:16px;margin-top:8px}
@media(max-width:600px){.module-start-hero{padding:24px 20px}.module-start-step{flex-wrap:wrap}}
</style>

<div class="module-start-hero">
    <div class="module-start-badge">Conversion</div>
    <h1>Transformer vos leads en mandats signés</h1>
    <p>Un parcours simple pour qualifier, appeler, traiter les objections, fixer les rendez-vous et relancer au bon moment.</p>
</div>

<div class="module-start-title">Les actions prioritaires</div>
<div class="module-start-steps">
    <a class="module-start-step" href="/admin?module=convertir&action=parcours" style="--accent:#c9a84c;--icon-bg:#fef9e7;">
        <div class="module-start-num">1</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-route" style="color:#c9a84c;margin-right:6px;"></i>Parcours de conversion</div><div class="module-start-desc">Méthode en 5 étapes : qualification, script, objections, RDV et signature.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div>
    </a>
    <a class="module-start-step" href="/admin?module=convertir&action=rdv" style="--accent:#3b82f6;--icon-bg:#e3f2fd;">
        <div class="module-start-num">2</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-calendar-days" style="color:#3b82f6;margin-right:6px;"></i>Prise de RDV</div><div class="module-start-desc">Vue agenda des leads en phase RDV avec actions de confirmation et replanification.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div>
    </a>
    <a class="module-start-step" href="/admin?module=convertir&action=suivi-post-rdv" style="--accent:#10b981;--icon-bg:#ecfdf5;">
        <div class="module-start-num">3</div><div class="module-start-body"><div class="module-start-label"><i class="fas fa-reply" style="color:#10b981;margin-right:6px;"></i>Suivi post-RDV</div><div class="module-start-desc">Séquence de relance structurée après un rendez-vous pour augmenter la signature.</div></div><div class="module-start-arrow"><i class="fas fa-chevron-right"></i></div>
    </a>
</div>
