<?php
$allowedActions = ['index', 'ancre', 'profils', 'offre', 'zone', 'synthese', 'actions'];
$action = isset($_GET['action']) ? preg_replace('/[^a-z_-]/', '', (string)$_GET['action']) : 'index';
if (!in_array($action, $allowedActions, true)) $action = 'index';

$actionTitles = [
    'ancre'    => 'Méthode ANCRE+ — Positionnement',
    'profils'  => 'NeuroPersona — Profils Clients',
    'offre'    => 'Offre Conseiller — Formulation',
    'zone'     => 'Zone de Prospection',
    'synthese' => 'Synthèse Stratégique',
    'actions'  => 'Actions du Jour',
];

$pageTitle       = $action === 'index' ? 'Construire' : ($actionTitles[$action] ?? 'Construire');
$pageDescription = 'Posez les bases solides de votre activité';

function renderContent()
{
    global $action;

    if ($action !== 'index') {
        $file = __DIR__ . '/' . $action . '.php';
        if (is_file($file)) {
            include $file;
            return;
        }
    }
    ?>
    <style>
    .build-page {
        display: grid;
        gap: 22px;
    }

    .build-hero {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 24px 20px;
        color: #fff;
        box-shadow: 0 4px 20px rgba(15, 34, 55, .18);
    }
    .build-hero h1 {
        margin: 0 0 10px;
        font-size: clamp(24px, 4vw, 30px);
        line-height: 1.24;
        color: #fff;
    }
    .build-hero p {
        margin: 0;
        color: rgba(255,255,255,.78);
        font-size: 15px;
        line-height: 1.65;
        max-width: 860px;
    }

    .build-mere {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .mere-card {
        background: #fff;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 1px 8px rgba(15,23,42,.08);
        border: 1px solid #e2e8f0;
    }
    .mere-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
        font-weight: 700;
        color: #1e293b;
        font-size: 14px;
        letter-spacing: .01em;
    }
    .mere-card p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #475569;
        white-space: pre-line;
    }
    .mere-card.motivation { border-left: 5px solid #f59e0b; background: #fffaf0; }
    .mere-card.explanation { border-left: 5px solid #3b82f6; background: #f8fbff; }
    .mere-card.resultat { border-left: 5px solid #10b981; background: #f2fdf8; }
    .mere-card.exercice { border-left: 5px solid #ef4444; background: #fff6f6; }

    .build-progress {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 1px 8px rgba(15,23,42,.08);
    }
    .build-progress-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    .build-progress-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .07em;
        font-weight: 700;
        color: #64748b;
    }
    .build-progress-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
    .build-progress-track {
        width: 100%;
        height: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .build-progress-bar {
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
        transition: width .3s ease;
    }

    .build-modules {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .build-module {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 1px 8px rgba(15,23,42,.08);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .build-module:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15,23,42,.10);
    }
    .build-module-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 8px;
    }
    .build-module-title-wrap {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .build-module-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .build-module h3 {
        margin: 0;
        font-size: 16px;
        color: #0f172a;
    }
    .build-module h3 span {
        display: block;
        margin-top: 2px;
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }
    .build-module p {
        margin: 0 0 12px;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }

    .module-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .module-status[data-status="not_started"] { background: #f1f5f9; color: #64748b; }
    .module-status[data-status="in_progress"] { background: #ffedd5; color: #c2410c; }
    .module-status[data-status="completed"] { background: #dcfce7; color: #166534; }

    .build-module-footer {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .build-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: background .15s ease, color .15s ease;
    }
    .build-btn-primary {
        background: #0f2237;
        color: #fff;
    }
    .build-btn-primary:hover { background: #193757; }

    .build-btn-ghost {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #dbe2ea;
    }
    .build-btn-ghost:hover { background: #eef2f7; }

    .build-next {
        background: #fff;
        border-radius: 16px;
        padding: 22px 20px;
        box-shadow: 0 1px 8px rgba(15,23,42,.08);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .build-next h2 {
        margin: 0 0 6px;
        font-size: 20px;
        color: #0f172a;
    }
    .build-next p {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.55;
        max-width: 760px;
    }

    html { scroll-behavior: smooth; }

    @media (min-width: 900px) {
        .build-page { gap: 26px; }
        .build-hero { padding: 34px 36px; }
        .build-mere { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .build-modules { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .build-module { padding: 20px; }
        .build-next { padding: 24px 26px; }
    }
    .construire-info-wrap {
        position: relative;
        display: inline-block;
        margin-bottom: 1.25rem;
    }
    .construire-info-btn {
        background: none;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: .4rem .85rem;
        font-size: .85rem;
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        transition: background .15s, color .15s;
    }
    .construire-info-btn:hover { background: #f1f5f9; color: #334155; }
    .construire-info-tooltip {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 200;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.1);
        padding: 1rem 1.1rem;
        width: 400px;
        max-width: 90vw;
    }
    .construire-info-tooltip.is-open { display: block; }
    .construire-info-row {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .55rem 0;
        font-size: .84rem;
        line-height: 1.45;
        color: #374151;
    }
    .construire-info-row + .construire-info-row { border-top: 1px solid #f1f5f9; }
    .construire-info-row > i { margin-top: 2px; flex-shrink: 0; width: 16px; text-align: center; }
    </style>

    <div class="build-page">
        <section class="build-hero">
            <h1>Construire les fondations de votre système</h1>
            <p>Ici, vous définissez votre positionnement, votre cible et votre offre. C’est ce qui va faire toute la différence entre un conseiller invisible… et un conseiller choisi.</p>
        </section>

        <div class="construire-info-wrap">
            <button class="construire-info-btn" type="button" aria-label="Pourquoi cette étape ?">
                <i class="fas fa-circle-info"></i> Pourquoi cette étape ?
            </button>
            <div class="construire-info-tooltip" role="tooltip">
                <div class="construire-info-row">
                    <i class="fas fa-bolt" style="color:#f59e0b"></i>
                    <div><strong>Le constat</strong><br>La plupart des conseillers parlent à tout le monde, proposent la même chose et copient ce qu’ils voient. Au final, personne ne les remarque.</div>
                </div>
                <div class="construire-info-row">
                    <i class="fas fa-lightbulb" style="color:#3b82f6"></i>
                    <div><strong>La base à poser</strong><br>Avant de chercher des leads, définissez à qui vous parlez, ce que vous proposez, où vous intervenez et comment vous vous différenciez.</div>
                </div>
                <div class="construire-info-row">
                    <i class="fas fa-chart-line" style="color:#10b981"></i>
                    <div><strong>Vos bénéfices</strong><br>Un message clair, les bons prospects, une activité plus simple et plus efficace.</div>
                </div>
                <div class="construire-info-row">
                    <i class="fas fa-list-check" style="color:#8b5cf6"></i>
                    <div><strong>Passez à l’action</strong><br>Complétez les modules ci-dessous dans l’ordre pour construire votre système.</div>
                </div>
            </div>
        </div>

        <section class="build-progress" aria-live="polite">
            <div class="build-progress-top">
                <div class="build-progress-label">Progression</div>
                <div class="build-progress-value" id="build-progress-value">Progression : 0%</div>
            </div>
            <div class="build-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="build-progress-track">
                <div class="build-progress-bar" id="build-progress-bar"></div>
            </div>
        </section>

        <section class="build-modules" id="build-modules-grid">
            <article class="build-module" data-module="ancre">
                <div class="build-module-head">
                    <div class="build-module-title-wrap">
                        <span class="build-module-num">1</span>
                        <h3>Méthode ANCRE+<span>Clarifier votre positionnement</span></h3>
                    </div>
                    <span class="module-status" data-status="not_started">Non commencé</span>
                </div>
                <p>Générez un message clair et différenciant en quelques minutes.</p>
                <div class="build-module-footer">
                    <a href="/admin?module=construire&amp;action=ancre" class="build-btn build-btn-primary">Commencer</a>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="in_progress">En cours</button>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="completed">Terminé</button>
                </div>
            </article>

            <article class="build-module" data-module="profils">
                <div class="build-module-head">
                    <div class="build-module-title-wrap">
                        <span class="build-module-num">2</span>
                        <h3>NeuroPersona<span>Identifier vos clients idéaux</span></h3>
                    </div>
                    <span class="module-status" data-status="not_started">Non commencé</span>
                </div>
                <p>Définissez les profils à cibler pour maximiser votre impact.</p>
                <div class="build-module-footer">
                    <a href="/admin?module=construire&amp;action=profils" class="build-btn build-btn-primary">Commencer</a>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="in_progress">En cours</button>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="completed">Terminé</button>
                </div>
            </article>

            <article class="build-module" data-module="offre">
                <div class="build-module-head">
                    <div class="build-module-title-wrap">
                        <span class="build-module-num">3</span>
                        <h3>Offre Conseiller<span>Construire votre offre</span></h3>
                    </div>
                    <span class="module-status" data-status="not_started">Non commencé</span>
                </div>
                <p>Créez une proposition claire qui donne envie de vous contacter.</p>
                <div class="build-module-footer">
                    <a href="/admin?module=construire&amp;action=offre" class="build-btn build-btn-primary">Commencer</a>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="in_progress">En cours</button>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="completed">Terminé</button>
                </div>
            </article>

            <article class="build-module" data-module="zone">
                <div class="build-module-head">
                    <div class="build-module-title-wrap">
                        <span class="build-module-num">4</span>
                        <h3>Zone de Prospection<span>Définir votre terrain</span></h3>
                    </div>
                    <span class="module-status" data-status="not_started">Non commencé</span>
                </div>
                <p>Concentrez vos efforts sur les zones les plus rentables.</p>
                <div class="build-module-footer">
                    <a href="/admin?module=construire&amp;action=zone" class="build-btn build-btn-primary">Commencer</a>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="in_progress">En cours</button>
                    <button type="button" class="build-btn build-btn-ghost" data-set-status="completed">Terminé</button>
                </div>
            </article>
        </section>

        <section class="build-next">
            <div>
                <h2>Prêt à passer à l’étape suivante ?</h2>
                <p>Une fois ces fondations posées, vous pourrez commencer à attirer des vendeurs qualifiés automatiquement.</p>
            </div>
            <a href="/admin?module=attirer" class="build-btn build-btn-primary">Passer à l’étape Attirer <i class="fas fa-arrow-right"></i></a>
        </section>
    </div>

    <script>
    (function () {
        var STORAGE_KEY = 'immo_local_construire_statuses';
        var defaultStatuses = {
            ancre: 'not_started',
            profils: 'not_started',
            offre: 'not_started',
            zone: 'not_started'
        };

        function loadStatuses() {
            try {
                var raw = window.localStorage.getItem(STORAGE_KEY);
                if (!raw) return Object.assign({}, defaultStatuses);
                var parsed = JSON.parse(raw);
                return Object.assign({}, defaultStatuses, parsed || {});
            } catch (e) {
                return Object.assign({}, defaultStatuses);
            }
        }

        function saveStatuses(statuses) {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(statuses));
        }

        function statusLabel(status) {
            if (status === 'completed') return 'Terminé';
            if (status === 'in_progress') return 'En cours';
            return 'Non commencé';
        }

        function computeProgress(statuses) {
            var modules = Object.keys(defaultStatuses);
            var completed = modules.reduce(function (sum, key) {
                return sum + (statuses[key] === 'completed' ? 1 : 0);
            }, 0);
            return Math.round((completed / modules.length) * 100);
        }

        function render(statuses) {
            var cards = document.querySelectorAll('.build-module');
            cards.forEach(function (card) {
                var module = card.getAttribute('data-module');
                var status = statuses[module] || 'not_started';
                var badge = card.querySelector('.module-status');
                if (badge) {
                    badge.setAttribute('data-status', status);
                    badge.textContent = statusLabel(status);
                }
            });

            var progress = computeProgress(statuses);
            var progressLabel = document.getElementById('build-progress-value');
            var progressBar = document.getElementById('build-progress-bar');
            var progressTrack = document.getElementById('build-progress-track');

            if (progressLabel) progressLabel.textContent = 'Progression : ' + progress + '%';
            if (progressBar) progressBar.style.width = progress + '%';
            if (progressTrack) progressTrack.setAttribute('aria-valuenow', String(progress));
        }

        var statuses = loadStatuses();

        document.querySelectorAll('.build-module').forEach(function (card) {
            var module = card.getAttribute('data-module');
            var primary = card.querySelector('.build-btn-primary');
            if (primary) {
                primary.addEventListener('click', function () {
                    if (statuses[module] === 'not_started') {
                        statuses[module] = 'in_progress';
                        saveStatuses(statuses);
                        render(statuses);
                    }
                });
            }

            card.querySelectorAll('[data-set-status]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var next = btn.getAttribute('data-set-status') || 'not_started';
                    statuses[module] = next;
                    saveStatuses(statuses);
                    render(statuses);
                });
            });
        });

        render(statuses);

        // Infobulle
        var infoBtn = document.querySelector('.construire-info-btn');
        var infoTip = document.querySelector('.construire-info-tooltip');
        if (infoBtn && infoTip) {
            infoBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                infoTip.classList.toggle('is-open');
            });
            document.addEventListener('click', function () {
                infoTip.classList.remove('is-open');
            });
        }
    })();
    </script>
    <?php
}
