<?php
/**
 * Test d'envoi d'email — GET /admin/api/test-email.php
 * Envoie un email de test vers NOTIF_LEAD_EMAIL pour vérifier le SMTP.
 */
declare(strict_types=1);

ini_set('display_errors', '0');

define('ROOT_PATH', dirname(__DIR__, 3));
require_once ROOT_PATH . '/core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non authentifié']);
    exit;
}

require_once ROOT_PATH . '/core/services/MailService.php';

$to      = $_ENV['NOTIF_LEAD_EMAIL'] ?? $_ENV['APP_EMAIL'] ?? '';
$subject = 'Test email — ' . ($_ENV['APP_NAME'] ?? 'Site') . ' — ' . date('d/m/Y H:i');

$html = '
<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;background:#f9f9f9;border-radius:8px">
  <h2 style="color:#1f3a5f;border-bottom:2px solid #1f3a5f;padding-bottom:8px">✅ Email de test reçu !</h2>
  <p>Bonjour,</p>
  <p>Cet email confirme que votre configuration SMTP fonctionne correctement.</p>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr style="background:#e8f0fe"><td style="padding:8px;font-weight:bold">SMTP Host</td><td style="padding:8px">' . htmlspecialchars($_ENV['SMTP_HOST'] ?? 'non configuré') . '</td></tr>
    <tr><td style="padding:8px;font-weight:bold">Port</td><td style="padding:8px">' . htmlspecialchars($_ENV['SMTP_PORT'] ?? '465') . '</td></tr>
    <tr style="background:#e8f0fe"><td style="padding:8px;font-weight:bold">Sécurité</td><td style="padding:8px">' . strtoupper(htmlspecialchars($_ENV['SMTP_SECURE'] ?? 'ssl')) . '</td></tr>
    <tr><td style="padding:8px;font-weight:bold">Expéditeur</td><td style="padding:8px">' . htmlspecialchars($_ENV['SMTP_FROM_EMAIL'] ?? $_ENV['APP_EMAIL'] ?? '') . '</td></tr>
    <tr style="background:#e8f0fe"><td style="padding:8px;font-weight:bold">Destinataire</td><td style="padding:8px">' . htmlspecialchars($to) . '</td></tr>
    <tr><td style="padding:8px;font-weight:bold">Envoyé le</td><td style="padding:8px">' . date('d/m/Y à H:i:s') . '</td></tr>
  </table>
  <p style="color:#666;font-size:0.85em">Email généré depuis ' . htmlspecialchars($_ENV['APP_URL'] ?? '') . '</p>
</div>';

$text = "Email de test - " . ($_ENV['APP_NAME'] ?? 'Site') . "\n\n"
      . "Envoyé le : " . date('d/m/Y à H:i:s') . "\n"
      . "SMTP : " . ($_ENV['SMTP_HOST'] ?? 'non configuré') . ":" . ($_ENV['SMTP_PORT'] ?? '465') . "\n"
      . "Cet email confirme que votre configuration SMTP fonctionne.";

$result = MailService::send($to, $subject, $text, $html);

echo json_encode([
    'ok'         => $result,
    'message'    => $result
        ? "Email envoyé avec succès à {$to}"
        : "Échec d'envoi. Vérifiez les logs PHP et la config SMTP.",
    'to'         => $to,
    'smtp_host'  => $_ENV['SMTP_HOST'] ?? '',
    'smtp_port'  => $_ENV['SMTP_PORT'] ?? '',
    'smtp_secure'=> $_ENV['SMTP_SECURE'] ?? '',
], JSON_UNESCAPED_UNICODE);
