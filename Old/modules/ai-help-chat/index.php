<?php

declare(strict_types=1);

require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/service.php';
require_once __DIR__ . '/api.php';
require_once __DIR__ . '/widget.php';
require_once __DIR__ . '/admin-settings.php';
require_once __DIR__ . '/_view.php';

$pageTitle = ‘Chat d’aide IA’;
$pageDescription = ‘Assistant contextuel interne connecté aux ressources CRM.’;

// ── Réponses API (JSON) — sortie avant le layout ─────────────
$_aiHelpAction = preg_replace(‘/[^a-z_]/’, ‘’, (string) ($_GET[‘action’] ?? ‘index’));
if ($_aiHelpAction === ‘api’) {
    $service = new AiHelpChatService(db());
    handleAiHelpChatApi($service);
    exit;
}

function renderContent(): void
{
    global $_aiHelpAction;
    $service = new AiHelpChatService(db());

    if ($_aiHelpAction === ‘settings’) {
        renderAiHelpChatAdminSettings($service);
        return;
    }

    renderAiHelpChatIndex($service);
}
