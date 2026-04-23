<?php
require_once __DIR__ . '/../../core/bootstrap.php';
?>

<section class="hub-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fab fa-google"></i> Google My Business</div>
        <h1>Recevez plus d'appels depuis Google</h1>
        <p>Activez votre présence locale pour convertir les recherches en rendez-vous.</p>
    </header>

    <div class="gmb-info-wrap">
        <button class="gmb-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
            <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
        </button>
        <div class="gmb-info-tooltip" role="tooltip">
            <div class="gmb-info-row">
                <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
                <div><strong>Problème</strong><br>Votre fiche locale ne transforme pas assez de vues en contacts.</div>
            </div>
            <div class="gmb-info-row">
                <i class="fas fa-diagram-project" style="color:#3b82f6"></i>
                <div><strong>Logique</strong><br>Profil complet, avis réguliers, publications utiles.</div>
            </div>
            <div class="gmb-info-row">
                <i class="fas fa-chart-line" style="color:#10b981"></i>
                <div><strong>Bénéfice</strong><br>Vous gagnez en confiance locale et en demandes entrantes.</div>
            </div>
            <div class="gmb-info-row">
                <i class="fas fa-play-circle" style="color:#f59e0b"></i>
                <div><strong>Action</strong><br>Commencez par compléter votre fiche dès aujourd'hui.</div>
            </div>
        </div>
    </div>
    <style>
    .gmb-info-wrap { position:relative; display:inline-block; margin-bottom:1.25rem; }
    .gmb-info-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:.4rem .85rem; font-size:.85rem; color:#64748b; cursor:pointer; display:inline-flex; align-items:center; gap:.45rem; transition:background .15s,color .15s; }
    .gmb-info-btn:hover { background:#f1f5f9; color:#334155; }
    .gmb-info-tooltip { display:none; position:absolute; top:calc(100% + 8px); left:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); padding:1rem 1.1rem; width:380px; max-width:90vw; }
    .gmb-info-tooltip.is-open { display:block; }
    .gmb-info-row { display:flex; gap:.75rem; align-items:flex-start; padding:.55rem 0; font-size:.84rem; line-height:1.45; color:#374151; }
    .gmb-info-row + .gmb-info-row { border-top:1px solid #f1f5f9; }
    .gmb-info-row > i { margin-top:2px; flex-shrink:0; width:16px; text-align:center; }
    </style>
    <script>
    (function () {
        var btn = document.querySelector('.gmb-info-btn');
        var tip = document.querySelector('.gmb-info-tooltip');
        if (!btn || !tip) return;
        btn.addEventListener('click', function (e) { e.stopPropagation(); tip.classList.toggle('is-open'); });
        document.addEventListener('click', function () { tip.classList.remove('is-open'); });
    })();
    </script>

    <div class="hub-modules-grid">
        <a class="hub-module-card" href="/admin?module=gmb&view=fiche">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-id-card"></i></div>
                <h3>Compléter la fiche</h3>
            </div>
            <p>Renseignez les infos essentielles de votre établissement.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-star"></i></div>
                <h3>Répondre aux avis</h3>
            </div>
            <p>Montrez votre réactivité et renforcez votre image.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=demande-avis">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-envelope-open-text"></i></div>
                <h3>Demander des avis</h3>
            </div>
            <p>Augmentez les retours clients après chaque transaction.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=redaction&action=pool_gmb">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-pen-nib"></i></div>
                <h3>Publier chaque semaine</h3>
            </div>
            <p>Restez visible localement avec des posts réguliers.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>

        <a class="hub-module-card" href="/admin?module=gmb&view=statistiques">
            <div class="hub-module-card-head">
                <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-chart-bar"></i></div>
                <h3>Suivre les résultats</h3>
            </div>
            <p>Mesurez les appels, clics et vues générés par votre fiche.</p>
            <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
        </a>
    </div>

    <section class="hub-final-cta" aria-label="Progression GMB">
        <div>
            <h2>Progression : Fiche → Avis → Demandes → Publications → Résultats</h2>
            <p>Commencez par un levier, puis activez les suivants.</p>
        </div>
        <a class="hub-btn hub-btn--gold" href="/admin?module=gmb&view=fiche"><i class="fas fa-arrow-trend-up"></i> Lancer la première étape</a>
    </section>

</section>
