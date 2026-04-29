<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/MessageRepository.php';
require_once __DIR__ . '/repositories/TemplateRepository.php';
require_once __DIR__ . '/services/ImapService.php';
require_once __DIR__ . '/../../core/services/MailService.php';

$pdo    = db();
$user   = Auth::user();
$userId = (int) (($user['id'] ?? null) ?: ($_SESSION['user_id'] ?? 0));
if ($userId <= 0) {
    $userId = 1;
}

$repo     = new MessageRepository($pdo);
$tplRepo  = new TemplateRepository($pdo);
$repo->ensureSchema();
$imap     = new ImapService($repo, $userId);
$tplRepo->seedDefaults($userId);

$view = isset($_GET['view']) ? preg_replace('/[^a-z_]/', '', (string)$_GET['view']) : 'inbox';
$view = in_array($view, ['inbox', 'templates', 'settings'], true) ? $view : 'inbox';

$pageTitle       = 'Messagerie';
$pageDescription = 'Emails, templates et IA pour communiquer avec vos contacts.';

// ════════════════════════════════════════════════════════════════
// AJAX / API
// ════════════════════════════════════════════════════════════════
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = (string)$_GET['action'];

    // ── Sync IMAP ────────────────────────────────────────────────
    if ($action === 'sync') {
        if (!ImapService::imapExtensionAvailable()) {
            echo json_encode(['ok' => false, 'error' => 'Extension PHP imap absente sur le serveur.']);
            exit;
        }
        if (!$imap->isConfigured()) {
            echo json_encode(['ok' => false, 'error' => 'IMAP non configuré (hôte, identifiant et mot de passe requis).']);
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

    // ── Envoyer un email ─────────────────────────────────────────
    if ($action === 'send') {
        $to      = trim((string)($_POST['to'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body    = trim((string)($_POST['body'] ?? ''));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
            echo json_encode(['ok' => false, 'error' => 'Destinataire, objet et message obligatoires.']);
            exit;
        }
        echo json_encode($imap->send($to, $subject, nl2br(htmlspecialchars($body))));
        exit;
    }

    // ── Marquer comme lu ────────────────────────────────────────
    if ($action === 'mark_read') {
        $tid = (int)($_GET['thread_id'] ?? 0);
        if ($tid > 0) $repo->markThreadRead($userId, $tid);
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── Test connexion IMAP ──────────────────────────────────────
    if ($action === 'test_imap') {
        if (!ImapService::imapExtensionAvailable()) {
            echo json_encode(['ok' => false, 'error' => 'Extension PHP imap absente sur le serveur.']);
            exit;
        }
        try {
            $count = $imap->testConnection();
            echo json_encode(['ok' => true, 'message' => "Connexion réussie — {$count} message(s) dans la boîte."]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // ── Test envoi SMTP ─────────────────────────────────────────
    if ($action === 'test_smtp') {
        $to = filter_var((string)($_POST['to'] ?? $imap->getAdvisorEmail()), FILTER_VALIDATE_EMAIL);
        if (!$to) {
            echo json_encode(['ok' => false, 'error' => 'Adresse de test invalide.']);
            exit;
        }
        try {
            $sent = MailService::send(
                $to,
                'Test SMTP - Pascal Hamm Immobilier',
                "Bonjour,\n\nCet email confirme que l'envoi SMTP fonctionne depuis l'admin.\n\nPascal Hamm Immobilier",
                '<p>Bonjour,</p><p>Cet email confirme que l&apos;envoi SMTP fonctionne depuis l&apos;admin.</p><p>Pascal Hamm Immobilier</p>'
            );

            echo json_encode($sent
                ? ['ok' => true, 'message' => 'Email de test envoyé. Vérifiez la boîte de réception.']
                : ['ok' => false, 'error' => 'Envoi SMTP échoué. Vérifiez hôte, port, sécurité, identifiant et mot de passe.']);
        } catch (Throwable $e) {
            error_log('messagerie test_smtp error: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Erreur SMTP serveur : ' . $e->getMessage()]);
        }
        exit;
    }

    // ── Sauvegarder config IMAP ──────────────────────────────────
    if ($action === 'save_imap') {
        $defaultMailHost = 'mail.pascal-hamm-immobilier-aix-en-provence.fr';
        $defaultMailUser = 'superuser@pascal-hamm-immobilier-aix-en-provence.fr';

        $host   = trim((string)($_POST['host'] ?? ''));
        $port   = (int)($_POST['port'] ?? 993);
        $user_  = trim((string)($_POST['user'] ?? ''));
        $pass   = (string)($_POST['pass'] ?? '');
        $secure = in_array($_POST['secure'] ?? '', ['ssl','tls','none']) ? $_POST['secure'] : 'ssl';
        $smtpHost   = trim((string)($_POST['smtp_host'] ?? $host));
        $smtpPort   = (int)($_POST['smtp_port'] ?? 465);
        $smtpSecure = in_array($_POST['smtp_secure'] ?? '', ['ssl','tls','none'], true) ? (string)$_POST['smtp_secure'] : 'ssl';
        $smtpPass   = (string)($_POST['smtp_pass'] ?? '');
        $fromName   = trim((string)($_POST['from_name'] ?? setting('smtp_from_name', 'Pascal Hamm Immobilier', $userId)));

        $host = $host !== '' ? $host : (string) setting('imap_host', $defaultMailHost, $userId);
        $user_ = $user_ !== '' ? $user_ : (string) setting('imap_user', $defaultMailUser, $userId);
        $smtpHost = $smtpHost !== '' ? $smtpHost : (string) setting('smtp_host', $host ?: $defaultMailHost, $userId);

        if ($host === '') $host = $defaultMailHost;
        if ($user_ === '') $user_ = $defaultMailUser;
        if ($smtpHost === '') $smtpHost = $host;

        $writes = [
            setting_set('imap_host',   $host,   $userId),
            setting_set('imap_port',   (string)$port, $userId),
            setting_set('imap_user',   $user_,  $userId),
            setting_set('imap_secure', $secure, $userId),
        ];
        if ($pass !== '') {
            $writes[] = setting_set('imap_pass', $pass, $userId);
        }

        if ($smtpHost !== '') {
            $writes[] = setting_set('smtp_host', $smtpHost, $userId);
            $writes[] = setting_set('smtp_port', (string)($smtpPort > 0 ? $smtpPort : 465), $userId);
            $writes[] = setting_set('smtp_user', $user_, $userId);
            $writes[] = setting_set('smtp_secure', $smtpSecure, $userId);
            $writes[] = setting_set('smtp_from', $user_, $userId);
            $writes[] = setting_set('smtp_from_name', $fromName !== '' ? $fromName : 'Pascal Hamm Immobilier', $userId);
            if ($smtpPass !== '' || $pass !== '') {
                $writes[] = setting_set('smtp_pass', $smtpPass !== '' ? $smtpPass : $pass, $userId);
            }
        }

        if (in_array(false, $writes, true)) {
            echo json_encode(['ok' => false, 'error' => 'Impossible d’enregistrer la configuration email. Vérifiez les droits base de données.']);
            exit;
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    // ── Déconnecter IMAP ────────────────────────────────────────
    if ($action === 'disconnect_imap') {
        foreach (['imap_host','imap_port','imap_user','imap_pass','imap_secure'] as $k) {
            setting_delete($k, $userId);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

    // ── IA — Rédiger un email ────────────────────────────────────
    if ($action === 'ai_draft') {
        $apiKey = (string) setting('tech_openai_key', '', $userId);
        if ($apiKey === '') {
            echo json_encode(['ok' => false, 'error' => 'Clé OpenAI non configurée dans Paramètres → API.']);
            exit;
        }
        $contactName = trim((string)($_POST['contact_name'] ?? ''));
        $intent      = trim((string)($_POST['intent'] ?? 'suivi'));
        $context     = trim((string)($_POST['context'] ?? ''));
        $tone        = in_array($_POST['tone'] ?? '', ['professionnel','amical','urgent']) ? $_POST['tone'] : 'professionnel';
        $advisorName = (string) setting('profil_nom', APP_NAME, $userId);

        $systemPrompt = "Tu es un assistant pour {$advisorName}, conseiller immobilier expert. Rédige des emails professionnels en français, concis et efficaces. Réponds UNIQUEMENT en JSON avec les clés \"subject\" et \"body\" (HTML simple avec <p> et <br>).";

        $userPrompt = "Rédige un email {$tone} pour : {$intent}.\n";
        if ($contactName) $userPrompt .= "Contact : {$contactName}.\n";
        if ($context)     $userPrompt .= "Contexte : {$context}.\n";
        $userPrompt .= "Signature : {$advisorName}.\nRéponds en JSON {\"subject\":\"...\",\"body\":\"...\"}";

        $payload = json_encode([
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.7,
            'max_tokens'  => 600,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data    = json_decode($resp ?: '{}', true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        $parsed  = json_decode($content, true);

        if (isset($parsed['subject'], $parsed['body'])) {
            echo json_encode(['ok' => true, 'subject' => $parsed['subject'], 'body' => $parsed['body']]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Réponse IA invalide. Réessayez.', 'raw' => $content]);
        }
        exit;
    }

    // ── IA — Générer un template ─────────────────────────────────
    if ($action === 'ai_template') {
        $apiKey = (string) setting('tech_openai_key', '', $userId);
        if ($apiKey === '') {
            echo json_encode(['ok' => false, 'error' => 'Clé OpenAI non configurée dans Paramètres → API.']);
            exit;
        }

        $goal        = trim((string)($_POST['goal'] ?? ''));
        $context     = trim((string)($_POST['context'] ?? ''));
        $tone        = in_array($_POST['tone'] ?? '', ['professionnel', 'amical', 'urgent', 'premium'], true) ? (string)$_POST['tone'] : 'professionnel';
        $category    = in_array($_POST['category'] ?? '', array_keys($tplRepo->categories()), true) ? (string)$_POST['category'] : 'general';
        $advisorName = (string) setting('profil_nom', APP_NAME, $userId);

        if ($goal === '') {
            echo json_encode(['ok' => false, 'error' => 'Objectif obligatoire pour générer un template.']);
            exit;
        }

        $systemPrompt = "Tu es un assistant pour {$advisorName}, conseiller immobilier expert. "
            . "Génère un template email réutilisable en français avec placeholders. "
            . "Utilise, si pertinent, {{contact_prenom}}, {{conseiller_nom}}, {{bien_titre}}, {{date_rdv}}. "
            . "Retourne UNIQUEMENT du JSON valide avec les clés : name, subject, body_html. "
            . "body_html doit contenir du HTML simple avec uniquement <p> et <br>.";

        $userPrompt = "Objectif du template : {$goal}.\n"
            . "Ton : {$tone}.\n"
            . "Catégorie : {$category}.\n";

        if ($context !== '') {
            $userPrompt .= "Contexte métier : {$context}.\n";
        }

        $userPrompt .= "Signature à inclure naturellement avec {{conseiller_nom}}.\n"
            . "Réponds strictement au format JSON.";

        $payload = json_encode([
            'model'       => 'gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.65,
            'max_tokens'  => 900,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $data    = json_decode($resp ?: '{}', true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        $parsed  = json_decode($content, true);

        if (is_array($parsed) && isset($parsed['name'], $parsed['subject'], $parsed['body_html'])) {
            echo json_encode([
                'ok'       => true,
                'name'     => trim((string)$parsed['name']),
                'subject'  => trim((string)$parsed['subject']),
                'body_html'=> trim((string)$parsed['body_html']),
            ]);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Réponse IA invalide. Réessayez.', 'raw' => $content]);
        }
        exit;
    }

    // ── Templates CRUD ───────────────────────────────────────────
    if ($action === 'template_save') {
        $id      = (int)($_POST['id'] ?? 0);
        $data    = [
            'name'      => trim((string)($_POST['name'] ?? '')),
            'category'  => (string)($_POST['category'] ?? 'general'),
            'subject'   => trim((string)($_POST['subject'] ?? '')),
            'body_html' => trim((string)($_POST['body_html'] ?? '')),
        ];
        if ($data['name'] === '') {
            echo json_encode(['ok' => false, 'error' => 'Nom obligatoire.']);
            exit;
        }
        if ($id > 0) {
            $ok = $tplRepo->update($userId, $id, $data);
            echo json_encode(['ok' => $ok]);
        } else {
            $newId = $tplRepo->insert($userId, $data);
            echo json_encode(['ok' => $newId > 0, 'id' => $newId]);
        }
        exit;
    }

    if ($action === 'template_delete') {
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(['ok' => $tplRepo->delete($userId, $id)]);
        exit;
    }

    if ($action === 'template_use') {
        $id  = (int)($_GET['id'] ?? 0);
        $tpl = $tplRepo->getById($userId, $id);
        if ($tpl) {
            $tplRepo->incrementUsage($id);
            echo json_encode(['ok' => true, 'template' => $tpl]);
        } else {
            echo json_encode(['ok' => false]);
        }
        exit;
    }

    if ($action === 'templates_list') {
        echo json_encode(['ok' => true, 'templates' => $tplRepo->getAll($userId)]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Action inconnue.']);
    exit;
}

function renderContent(): void
{
    global $view, $imap, $repo, $tplRepo, $userId;
    require __DIR__ . '/views/' . $view . '.php';
}
