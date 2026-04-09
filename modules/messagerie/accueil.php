<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/MessageRepository.php';
require_once __DIR__ . '/services/ImapService.php';

$pdo    = db();
$user   = Auth::user();
$userId = (int)($user['id'] ?? 1);

$repo  = new MessageRepository($pdo);
$repo->ensureSchema();
$imap  = new ImapService($repo, $userId);

$view  = isset($_GET['view']) ? preg_replace('/[^a-z_]/', '', (string)$_GET['view']) : 'inbox';
$view  = in_array($view, ['inbox'], true) ? $view : 'inbox';

$pageTitle       = 'Messagerie';
$pageDescription = 'Tous vos emails centralisés et liés à vos contacts.';

// ── Actions AJAX ────────────────────────────────────────────────
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = (string)$_GET['action'];

    if ($action === 'sync') {
        if (!$imap->isConfigured()) {
            echo json_encode(['ok' => false, 'error' => 'IMAP non configuré. Vérifiez Paramètres → SMTP.']);
            exit;
        }
        try {
            $count = $imap->syncInbox(100);
            echo json_encode(['ok' => true, 'imported' => $count]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'send') {
        $to      = trim((string)($_POST['to'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body    = trim((string)($_POST['body'] ?? ''));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
            echo json_encode(['ok' => false, 'error' => 'Destinataire, objet et message obligatoires.']);
            exit;
        }
        $result = $imap->send($to, $subject, nl2br(htmlspecialchars($body)));
        echo json_encode($result);
        exit;
    }

    if ($action === 'mark_read') {
        $threadId = (int)($_GET['thread_id'] ?? 0);
        if ($threadId > 0) $repo->markThreadRead($userId, $threadId);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Action inconnue.']);
    exit;
}

function renderContent(): void
{
    global $imap, $repo, $userId;
    require __DIR__ . '/views/inbox.php';
}
