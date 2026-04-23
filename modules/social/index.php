<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/_bootstrap.php';
require_once __DIR__ . '/repositories/SequenceRepository.php';
require_once __DIR__ . '/repositories/PostRepository.php';
require_once __DIR__ . '/services/StrategyService.php';
require_once __DIR__ . '/services/PublishService.php';
require_once __DIR__ . '/services/SchedulerService.php';
require_once __DIR__ . '/services/SequenceService.php';
require_once __DIR__ . '/services/MediaService.php';
require_once __DIR__ . '/controllers/SequenceController.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/controllers/SocialController.php';

$pageTitle = 'Social';
$pageDescription = 'Séquences, publication et journal social';

$sequenceRepository = new SequenceRepository(db());

$postRepository = new PostRepository(db());
$strategyService = new StrategyService();
$sequenceController = new SequenceController($sequenceRepository, $postRepository);
$postController = new PostController($postRepository, $strategyService);
$socialController = new SocialController($sequenceRepository, $postRepository, $strategyService);

$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'index'));
$allowedActions = ['index', 'sequences', 'journal', 'post', 'post-form', 'kit', 'save-sequence', 'save-post', 'delete-post', 'toggle-sequence', 'duplicate-sequence'];
if (!in_array($action, $allowedActions, true)) {
    $action = 'index';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (in_array($action, ['save-sequence', 'toggle-sequence', 'duplicate-sequence'], true)) {
        $sequenceController->handle($action);
    }
    if (in_array($action, ['save-post', 'delete-post'], true)) {
        $postController->handle($action);
    }
}

function renderSocialHub(): void
{
    ?>
    <section class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-share-nodes"></i> Réseaux sociaux</div>
            <h1>Publiez régulièrement pour rester visible</h1>
            <p>Planifiez vos contenus, automatisez vos séquences et entretenez la relation avec votre audience locale.</p>
        </header>

        <div class="social-info-wrap">
            <button class="social-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
                <i class="fas fa-circle-info"></i> Comment ça fonctionne ?
            </button>
            <div class="social-info-tooltip" role="tooltip">
                <div class="social-info-row">
                    <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
                    <div><strong>Problème</strong><br>Publier manuellement prend du temps et manque de régularité.</div>
                </div>
                <div class="social-info-row">
                    <i class="fas fa-diagram-project" style="color:#3b82f6"></i>
                    <div><strong>Logique</strong><br>Des séquences planifiées, un journal de bord, un kit de contenus réutilisables.</div>
                </div>
                <div class="social-info-row">
                    <i class="fas fa-chart-line" style="color:#10b981"></i>
                    <div><strong>Bénéfice</strong><br>Vous restez visible chaque semaine sans effort supplémentaire.</div>
                </div>
                <div class="social-info-row">
                    <i class="fas fa-play-circle" style="color:#f59e0b"></i>
                    <div><strong>Action</strong><br>Créez votre première séquence ou rédigez un post maintenant.</div>
                </div>
            </div>
        </div>

        <div class="hub-modules-grid">
            <a href="?module=social&action=sequences" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#eafaf1;color:#16a34a;"><i class="fas fa-layer-group"></i></div>
                    <h3>Séquences</h3>
                </div>
                <p>Planifiez des séries de posts automatiques sur vos réseaux.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=social&action=journal" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#dbeafe;color:#2563eb;"><i class="fas fa-newspaper"></i></div>
                    <h3>Journal</h3>
                </div>
                <p>Suivez tous vos posts publiés et leur historique.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>

            <a href="?module=social&action=post-form" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-pen-to-square"></i></div>
                    <h3>Nouveau post</h3>
                </div>
                <p>Rédigez et planifiez un post en quelques secondes.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Créer</span>
            </a>

            <a href="?module=social&action=kit" class="hub-module-card">
                <div class="hub-module-card-head">
                    <div class="hub-module-card-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-toolbox"></i></div>
                    <h3>Kit de contenus</h3>
                </div>
                <p>Accédez à des modèles et inspirations pour vos publications.</p>
                <span class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Ouvrir</span>
            </a>
        </div>

        <section class="hub-final-cta" aria-label="Progression social">
            <div>
                <h2>Progression : Séquences → Posts → Journal → Amplifier</h2>
                <p>Commencez par une séquence, puis publiez régulièrement.</p>
            </div>
            <a href="?module=social&action=sequences" class="hub-btn hub-btn--gold"><i class="fas fa-rocket"></i> Créer ma première séquence</a>
        </section>

    </section>
    <?php
}

function renderContent(): void
{
    global $socialController, $action;

    $cssPath = __DIR__ . '/assets/social.css';
    if (is_file($cssPath)) {
        echo '<style data-social-inline="1">' . file_get_contents($cssPath) . '</style>';
    }

    switch ($action) {
        case 'journal':
            $socialController->journal();
            break;
        case 'post':
            $socialController->postDetail((int) ($_GET['id'] ?? 0));
            break;
        case 'post-form':
            $socialController->postForm((int) ($_GET['id'] ?? 0));
            break;
        case 'kit':
            require __DIR__ . '/views/kit.php';
            break;
        case 'sequences':
            $socialController->sequences();
            break;
        default:
            renderSocialHub();
            break;
    }

    $jsPath = __DIR__ . '/assets/social.js';
    if (is_file($jsPath)) {
        echo '<script>' . file_get_contents($jsPath) . '</script>';
    }
    ?>
    <style>
    .social-info-wrap { position:relative; display:inline-block; margin-bottom:1.25rem; }
    .social-info-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:.4rem .85rem; font-size:.85rem; color:#64748b; cursor:pointer; display:inline-flex; align-items:center; gap:.45rem; transition:background .15s,color .15s; }
    .social-info-btn:hover { background:#f1f5f9; color:#334155; }
    .social-info-tooltip { display:none; position:absolute; top:calc(100% + 8px); left:0; z-index:200; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.1); padding:1rem 1.1rem; width:380px; max-width:90vw; }
    .social-info-tooltip.is-open { display:block; }
    .social-info-row { display:flex; gap:.75rem; align-items:flex-start; padding:.55rem 0; font-size:.84rem; line-height:1.45; color:#374151; }
    .social-info-row + .social-info-row { border-top:1px solid #f1f5f9; }
    .social-info-row > i { margin-top:2px; flex-shrink:0; width:16px; text-align:center; }
    </style>
    <script>
    (function () {
        var btn = document.querySelector('.social-info-btn');
        var tip = document.querySelector('.social-info-tooltip');
        if (!btn || !tip) return;
        btn.addEventListener('click', function (e) { e.stopPropagation(); tip.classList.toggle('is-open'); });
        document.addEventListener('click', function () { tip.classList.remove('is-open'); });
    })();
    </script>
    <?php
}
