<?php

declare(strict_types=1);

$base = '/admin?module=optimiser';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="opt-guide">
    <div class="opt-guide__hero">
        <div class="opt-guide__badge">Parcours en 5 étapes</div>
        <h1 class="opt-guide__title">Optimisez votre acquisition immobilière</h1>
        <p class="opt-guide__lead">Un parcours clair pour mesurer, prioriser et améliorer vos canaux (SEO, réseaux, portails, partenaires).</p>
    </div>
    <div class="opt-guide__grid">
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-analytics', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">1</div>
            <h2 class="opt-card__title">Installer les analytics</h2>
            <p class="opt-card__text">GA4, pixel Meta, événements clés (lead, visite, estimation).</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-kpis', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">2</div>
            <h2 class="opt-card__title">Définir 3–5 KPIs</h2>
            <p class="opt-card__text">Coût par lead, taux de qualification, délai de réponse, part canal.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-dashboard', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">3</div>
            <h2 class="opt-card__title">Tableau de bord hebdo</h2>
            <p class="opt-card__text">Google Sheets ou Looker Studio, mis à jour automatiquement si possible.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-tests', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">4</div>
            <h2 class="opt-card__title">Tests A/B ciblés</h2>
            <p class="opt-card__text">Titres d’annonces, CTA, pages ville, formulaires courts vs longs.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
        <a class="opt-card" href="<?= htmlspecialchars($base . '&view=etape-analyse', ENT_QUOTES, 'UTF-8') ?>">
            <div class="opt-card__num">5</div>
            <h2 class="opt-card__title">Revue mensuelle</h2>
            <p class="opt-card__text">Synthèse des gains, décisions budget, backlog d’actions.</p>
            <span class="opt-card__link">Ouvrir l’étape →</span>
        </a>
    </div>
    <p class="opt-guide__footer">
        <a href="<?= htmlspecialchars($base . '&view=analytics', ENT_QUOTES, 'UTF-8') ?>">Voir le tableau de bord Analytics</a>
        · <a href="<?= htmlspecialchars($base . '&view=rapport-mensuel', ENT_QUOTES, 'UTF-8') ?>">Générer un rapport mensuel</a>
    </p>
</div>
<style>
.opt-guide{font-family:system-ui,-apple-system,sans-serif;color:#0f172a;display:grid;gap:1.2rem}
.opt-guide__hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:#fff;border-radius:16px;padding:36px 40px;box-shadow:0 4px 20px rgba(15,34,55,.18)}
.opt-guide__badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;border:1px solid rgba(201,168,76,.35);padding:4px 12px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;margin-bottom:14px}
.opt-guide__title{margin:0 0 12px;color:#fff;font-size:28px;font-weight:700;line-height:1.25}
.opt-guide__lead{margin:0;color:rgba(255,255,255,.72);font-size:15px;line-height:1.65;max-width:680px}
.opt-guide__grid{display:flex;flex-direction:column;gap:14px}
.opt-card{display:flex;align-items:flex-start;gap:18px;background:#fff;border:1px solid #e8edf4;border-left:4px solid #c9a84c;border-radius:12px;padding:20px 22px;text-decoration:none;color:inherit;box-shadow:0 1px 6px rgba(0,0,0,.07);transition:transform .15s,box-shadow .15s}
.opt-card:hover{transform:translateX(4px);box-shadow:0 4px 16px rgba(0,0,0,.1);color:inherit}
.opt-card__num{flex-shrink:0;width:36px;height:36px;border-radius:50%;background:#fef9e7;color:#c9a84c;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:.95rem}
.opt-card__title{margin:0 0 3px;font-size:15px;font-weight:700;color:#1e293b}
.opt-card__text{margin:0;font-size:13px;color:#64748b;line-height:1.5}
.opt-card__link{margin-left:auto;align-self:center;font-size:.82rem;font-weight:700;color:#c9a84c;white-space:nowrap}
.opt-guide__footer{margin:0;padding:20px 22px;border-radius:12px;background:#fff;border:1px solid #e8edf4;box-shadow:0 1px 6px rgba(0,0,0,.06);font-size:.9rem;color:#64748b}
.opt-guide__footer a{color:#1a3a5c;font-weight:700;text-decoration:none}
.opt-guide__footer a:hover{text-decoration:underline}
@media(max-width:700px){.opt-guide__hero{padding:24px 20px}.opt-card{flex-wrap:wrap}.opt-card__link{margin-left:0;width:100%}}
</style>
