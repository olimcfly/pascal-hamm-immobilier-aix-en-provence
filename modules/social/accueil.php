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

$action = preg_replace('/[^a-z-]/', '', (string) ($_GET['action'] ?? 'sequences'));
$allowedActions = ['index', 'sequences', 'journal', 'post', 'post-form', 'kit', 'save-sequence', 'save-post', 'delete-post', 'toggle-sequence', 'duplicate-sequence'];
if (!in_array($action, $allowedActions, true)) {
    $action = 'sequences';
}

if ($action === 'index') {
    $action = 'sequences';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (in_array($action, ['save-sequence', 'toggle-sequence', 'duplicate-sequence'], true)) {
        $sequenceController->handle($action);
    }
    if (in_array($action, ['save-post', 'delete-post'], true)) {
        $postController->handle($action);
    }
}

require_once __DIR__ . '/../../admin/views/layout.php';

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
        default:
            $socialController->sequences();
            break;
    }

    $jsPath = __DIR__ . '/assets/social.js';
    if (is_file($jsPath)) {
        echo '<script>' . file_get_contents($jsPath) . '</script>';
    }
}
