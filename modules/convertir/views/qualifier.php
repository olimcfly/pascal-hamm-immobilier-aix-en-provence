<?php

declare(strict_types=1);

$base = '/admin?module=convertir';
$current = 'parcours';
require __DIR__ . '/_subnav.php';
?>
<div class="conv-article conv-article--qualifier">
    <p class="conv-article__crumb"><a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>">Convertir</a> › <a href="<?= htmlspecialchars($base . '&action=parcours', ENT_QUOTES, 'UTF-8') ?>">Parcours</a> › Étape 1</p>
    <h1>Qualifier rapidement</h1>
    <p class="conv-article__lead">Le premier échange doit confirmer le besoin, le timing et la faisabilité du projet.</p>
    <ul class="conv-article__list">
        <li><strong>Objectif du contact</strong> : vente, achat, estimation, information simple.</li>
        <li><strong>Échéance</strong> : quand la personne veut avancer (immédiat, 1-3 mois, +6 mois).</li>
        <li><strong>Contexte</strong> : décisionnaire unique ou couple, contraintes de disponibilité, historique avec une agence.</li>
    </ul>
    <p class="conv-article__next"><a href="<?= htmlspecialchars($base . '&action=script-appel', ENT_QUOTES, 'UTF-8') ?>">Étape suivante : script d'appel →</a></p>
</div>
<style>
.conv-article{width:100%;font-family:system-ui,-apple-system,sans-serif;color:#0f2237;line-height:1.6;display:grid;gap:1rem}
.conv-article--qualifier{padding-bottom:1.5rem}
.conv-article__crumb{font-size:.88rem;color:#64748b;margin:0}
.conv-article__crumb a{color:#1a3a5c;text-decoration:none;font-weight:700}
.conv-article__crumb a:hover{text-decoration:underline}
.conv-article h1{margin:0;padding:38px 42px 12px;border-radius:18px 18px 0 0;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:#fff;font-size:30px;font-weight:800;line-height:1.2;box-shadow:0 8px 24px rgba(15,34,55,.16)}
.conv-article__lead{margin:-1rem 0 0;padding:0 42px 30px;border-radius:0 0 18px 18px;background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);color:rgba(255,255,255,.76);font-size:15px;box-shadow:0 8px 24px rgba(15,34,55,.16)}
.conv-article__list{margin:0;padding:1.35rem 1.35rem 1.35rem 2.45rem;background:#fff;border:1px solid #e8edf4;border-radius:14px;box-shadow:0 8px 22px rgba(15,34,55,.07)}
.conv-article__list li{margin-bottom:.65rem}
.conv-article__list strong{color:#1a3a5c}
.conv-article__next{margin:0;padding:1.05rem 1.2rem;background:#fff;border:1px solid #e8edf4;border-radius:14px;box-shadow:0 8px 22px rgba(15,34,55,.06)}
.conv-article__next a{font-weight:800;color:#1a3a5c;text-decoration:none}
.conv-article__next a:hover{text-decoration:underline}
@media(max-width:700px){.conv-article h1{padding:26px 22px 12px;font-size:24px}.conv-article__lead{padding:0 22px 24px}}
</style>
