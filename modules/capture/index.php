<?php
$pageTitle = "Capture";
$pageDescription = "Transformez vos visites en prises de contact";

function renderContent() {
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-magnet"></i> Conversion</div>
            <h1>Recevez plus de demandes qualifiées</h1>
            <p>Créez un parcours clair pour convertir chaque visite en contact.</p>
        </header>

        <div class="capture-info-wrap">
            <button class="capture-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
                <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
            </button>
            <div class="capture-info-tooltip" role="tooltip">
                <div class="capture-info-row">
                    <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
                    <div><strong>Problème</strong><br>Des visiteurs partent sans laisser leurs coordonnées.</div>
                </div>
                <div class="capture-info-row">
                    <i class="fas fa-diagram-project" style="color:#3b82f6"></i>
                    <div><strong>Logique</strong><br>Une promesse claire, un formulaire court, puis une confirmation immédiate.</div>
                </div>
                <div class="capture-info-row">
                    <i class="fas fa-chart-line" style="color:#10b981"></i>
                    <div><strong>Bénéfice</strong><br>Vous obtenez plus de contacts sans effort supplémentaire.</div>
                </div>
                <div class="capture-info-row">
                    <i class="fas fa-play-circle" style="color:#f59e0b"></i>
                    <div><strong>Action</strong><br>Lancez votre premier parcours de capture maintenant.</div>
                </div>
            </div>
        </div>
        <style>
        .capture-info-wrap { position:relative; display:inline-block; margin-bottom:1.25rem; }
        .capture-info-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:.4rem .85rem; font-size:.85rem; color:#64748b; cursor:pointer; display:inline-flex; align-items:center; gap:.45rem; transition:background .15s,color .15s; }
        .capture-info-btn:hover { background:#f1f5f9; color:#334155; }
        .capture-info-tooltip { display:none; position:absolute; top:calc(100% + 8px); left:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); padding:1rem 1.1rem; width:380px; max-width:90vw; }
        .capture-info-tooltip.is-open { display:block; }
        .capture-info-row { display:flex; gap:.75rem; align-items:flex-start; padding:.55rem 0; font-size:.84rem; line-height:1.45; color:#374151; }
        .capture-info-row + .capture-info-row { border-top:1px solid #f1f5f9; }
        .capture-info-row > i { margin-top:2px; flex-shrink:0; width:16px; text-align:center; }
        </style>
        <script>
        (function () {
            var btn = document.querySelector('.capture-info-btn');
            var tip = document.querySelector('.capture-info-tooltip');
            if (!btn || !tip) return;
            btn.addEventListener('click', function (e) { e.stopPropagation(); tip.classList.toggle('is-open'); });
            document.addEventListener('click', function () { tip.classList.remove('is-open'); });
        })();
        </script>

        <div class="hub-modules-grid" aria-label="Actions clés de conversion">
            <a href="/capture/" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-flag-checkered"></i></div>
                    <h3>Page d'entrée</h3>
                </div>
                <p>Présentez votre offre en quelques secondes.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/capture/form.php" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-list-check"></i></div>
                    <h3>Formulaire</h3>
                </div>
                <p>Gardez seulement les champs essentiels.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="/capture/merci.php" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fdedec;color:#dc2626;"><i class="fas fa-circle-check"></i></div>
                    <h3>Confirmation</h3>
                </div>
                <p>Confirmez la demande et proposez la suite.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=optimiser&view=analytics" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-chart-line"></i></div>
                    <h3>Mesurer</h3>
                </div>
                <p>Repérez rapidement ce qui fait gagner des contacts.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Voir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression conversion">
            <div>
                <h2>Progression : Entrée → Formulaire → Confirmation → Mesure</h2>
                <p>Commencez par un levier, puis développez votre parcours.</p>
            </div>
            <a href="?module=funnels" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Démarrer le parcours</a>
        </section>

    </section>
    <?php
}
