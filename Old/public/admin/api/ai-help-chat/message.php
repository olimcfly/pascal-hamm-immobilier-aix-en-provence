<?php
/**
 * Endpoint dédié AI Help Chat — POST /admin/api/ai-help-chat/message.php
 * Contourne le routing admin pour éviter que display_errors corrompe le JSON.
 */
declare(strict_types=1);

// Désactiver IMMÉDIATEMENT l'affichage des erreurs pour ne pas corrompre le JSON
ini_set('display_errors', '0');
error_reporting(0);

define('ROOT_PATH', dirname(__DIR__, 4));
require_once ROOT_PATH . '/core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Re-désactiver après bootstrap (qui peut le réactiver si APP_DEBUG)
ini_set('display_errors', '0');

try {
    if (!Auth::check()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Non autorisé']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }

    if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
        exit;
    }

    require_once ROOT_PATH . '/modules/aide/service.php';
    require_once ROOT_PATH . '/modules/ai-help-chat/service.php';

    $user = Auth::user() ?? ['id' => 0, 'role' => 'guest'];
    $role = (string) ($user['role'] ?? 'guest');

    $service = new AiHelpChatService(db());

    if (!$service->canUserUseChat($role)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Chat non autorisé pour ce rôle']);
        exit;
    }

    $message = trim((string) ($_POST['message'] ?? ''));
    if ($message === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Message vide']);
        exit;
    }

    $context = [
        'module'   => preg_replace('/[^a-z0-9_-]/', '', (string) ($_POST['context_module'] ?? 'dashboard')),
        'page'     => mb_substr(trim((string) ($_POST['context_page'] ?? '')), 0, 120),
        'category' => mb_substr(trim((string) ($_POST['context_category'] ?? '')), 0, 80),
        'step'     => mb_substr(trim((string) ($_POST['context_step'] ?? '')), 0, 80),
    ];

    $conversationId = (int) ($_POST['conversation_id'] ?? 0);
    if ($conversationId <= 0) {
        $conversationId = $service->createConversation((int) ($user['id'] ?? 0), $role, $context);
    }

    $service->saveMessage($conversationId, 'user', $message, ['context' => $context]);
    $result = $service->buildAssistantResponse($message, $context);
    $service->saveMessage($conversationId, 'assistant', (string) ($result['answer'] ?? ''), $result);

    echo json_encode([
        'success'         => true,
        'conversation_id' => $conversationId,
        'assistant'       => $result,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('[AiHelpChat endpoint] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur interne']);
}
