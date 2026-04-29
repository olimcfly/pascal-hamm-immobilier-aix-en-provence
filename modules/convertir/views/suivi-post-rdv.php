<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'suivi-post-rdv';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-followup">
    <div class="conv-followup__hero">
        <div class="conv-followup__badge">Relance commerciale</div>
        <h1><i class="fas fa-reply"></i> Suivi post-RDV</h1>
        <p>Séquence courte de relance pour transformer un rendez-vous en décision.</p>
    </div>

    <section class="conv-followup__panel">
        <h2>Cadence recommandée</h2>
        <ol>
            <li><strong>Sous 2h</strong> : message récapitulatif + bénéfice principal évoqué en rendez-vous.</li>
            <li><strong>J+2</strong> : relance orientée décision avec un choix simple ("on avance" / "on ajuste").</li>
            <li><strong>J+7</strong> : dernier contact courtois + date de validité de votre proposition.</li>
        </ol>
    </section>

    <section class="conv-followup__panel">
        <h2>Modèle de message (J+2)</h2>
        <div class="conv-followup__template">Bonjour {{prenom}}, suite à notre rendez-vous, je vous propose de valider le plan de commercialisation cette semaine afin de publier dans les meilleures conditions. Préférez-vous jeudi 14h ou vendredi 10h pour finaliser ?</div>
    </section>

    <p class="conv-followup__links">
        <a href="<?= htmlspecialchars($base . '&action=rdv', ENT_QUOTES, 'UTF-8') ?>">Voir les RDV en cours</a>
        · <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Retour au parcours</a>
    </p>
</div>
<style>
.conv-followup{width:100%;font-family:system-ui,-apple-system,sans-serif;color:#0f2237;display:grid;gap:1rem;padding-bottom:1.5rem}
.conv-followup__hero{background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);border-radius:18px;padding:38px 42px;color:#fff;box-shadow:0 8px 24px rgba(15,34,55,.16)}
.conv-followup__badge{display:inline-block;background:rgba(201,168,76,.2);color:#c9a84c;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:4px 12px;border-radius:20px;margin-bottom:14px;border:1px solid rgba(201,168,76,.35)}
.conv-followup__hero h1{font-size:30px;font-weight:800;color:#fff;margin:0 0 12px;line-height:1.2}
.conv-followup__hero p{font-size:15px;color:rgba(255,255,255,.72);line-height:1.65;max-width:680px;margin:0}
.conv-followup__panel{background:#fff;border:1px solid #e8edf4;border-radius:14px;padding:1.2rem 1.3rem;box-shadow:0 8px 22px rgba(15,34,55,.06)}
.conv-followup__panel h2{margin:0 0 .6rem;font-size:1.05rem;color:#0f2237}
.conv-followup__panel ol{margin:0;padding-left:1.2rem}
.conv-followup__panel li{margin:.45rem 0}
.conv-followup__panel strong{color:#1a3a5c}
.conv-followup__template{padding:1rem 1.05rem;border-radius:12px;background:#f8fafc;border:1px solid #e8edf4;font-size:.92rem;line-height:1.65;color:#0f2237}
.conv-followup__links{margin:0;padding:1.05rem 1.2rem;background:#fff;border:1px solid #e8edf4;border-radius:14px;box-shadow:0 8px 22px rgba(15,34,55,.06);font-size:.9rem;color:#64748b}
.conv-followup__links a{color:#1a3a5c;font-weight:800;text-decoration:none}
.conv-followup__links a:hover{text-decoration:underline}
@media(max-width:700px){.conv-followup__hero{padding:26px 22px}.conv-followup__hero h1{font-size:24px}}
</style>
