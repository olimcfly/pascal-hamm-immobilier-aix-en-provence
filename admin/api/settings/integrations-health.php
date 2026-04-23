<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non authentifié.']);
    exit;
}

$userId = (int) (Auth::user()['id'] ?? 0);

/**
 * Vérifie si une clé est non vide et non placeholder.
 */
function isKeyValid(string $value): bool
{
    $placeholders = ['', 'changeme', 'change-me', 'votre', 'xxx', 'xxxxxxx', 'remplacez', 'base64:VotreCl'];
    if (trim($value) === '') {
        return false;
    }
    foreach ($placeholders as $p) {
        if (stripos($value, $p) !== false) {
            return false;
        }
    }
    return true;
}

/**
 * Teste une URL d'API avec une clé HTTP basique (ping).
 */
function quickPing(string $url, array $headers = [], int $timeout = 5): int
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_NOBODY         => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}

$integrations = [];

// ── 1. Anthropic Claude ─────────────────────────────────────────────────────
$anthropicKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';
if (!isKeyValid($anthropicKey)) {
    // Fallback 1: settings table (géré depuis l'UI Paramètres)
    $anthropicKey = (string) setting('api_anthropic', '', $userId);
}
if (!isKeyValid($anthropicKey)) {
    // Fallback 2: ia_configurations table (ancienne méthode)
    try {
        $stmt = db()->prepare(
            "SELECT api_key FROM ia_configurations
             WHERE user_id = :uid AND provider = 'anthropic' AND is_active = 1
             ORDER BY updated_at DESC LIMIT 1"
        );
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $anthropicKey = (string) ($row['api_key'] ?? '');
    } catch (Throwable) {
        $anthropicKey = '';
    }
}
$anthropicSource = isKeyValid($_ENV['ANTHROPIC_API_KEY'] ?? '')
    ? 'ENV'
    : (isKeyValid(setting('api_anthropic', '', $userId)) ? 'Settings DB' : 'DB ia_config');
$integrations['anthropic'] = [
    'name'    => 'Anthropic Claude (IA)',
    'icon'    => 'fa-robot',
    'color'   => '#f59e0b',
    'source'  => $anthropicSource,
    'status'  => isKeyValid($anthropicKey) ? 'configured' : 'missing',
    'detail'  => isKeyValid($anthropicKey) ? 'Clé présente — IA active' : 'Clé manquante dans .env, Paramètres et DB',
    'model'   => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-haiku-4-5-20251001',
];

// ── 2. OpenAI ───────────────────────────────────────────────────────────────
$openaiKey = $_ENV['OPENAI_API_KEY'] ?? '';
if (!isKeyValid($openaiKey)) {
    $openaiKey = (string) setting('tech_openai_key', '', $userId);
}
$integrations['openai'] = [
    'name'   => 'OpenAI (GPT)',
    'icon'   => 'fa-brain',
    'color'  => '#10b981',
    'source' => isKeyValid($_ENV['OPENAI_API_KEY'] ?? '') ? 'ENV' : 'Settings DB',
    'status' => isKeyValid($openaiKey) ? 'configured' : 'missing',
    'detail' => isKeyValid($openaiKey) ? 'Clé présente' : 'Clé manquante — fonctionnalités Social/Messagerie IA désactivées',
];

// ── 3. SMTP ─────────────────────────────────────────────────────────────────
$smtpHost = setting('smtp_host', '', $userId) ?: ($_ENV['SMTP_HOST'] ?? '');
$smtpUser = setting('smtp_user', '', $userId) ?: ($_ENV['SMTP_USER'] ?? '');
$smtpPass = setting('smtp_pass', '', $userId) ?: ($_ENV['SMTP_PASS'] ?? '');
$integrations['smtp'] = [
    'name'   => 'SMTP (email sortant)',
    'icon'   => 'fa-paper-plane',
    'color'  => '#3b82f6',
    'source' => 'ENV + DB',
    'status' => (isKeyValid($smtpHost) && isKeyValid($smtpUser) && isKeyValid($smtpPass)) ? 'configured' : 'partial',
    'detail' => isKeyValid($smtpHost) ? "Hôte : {$smtpHost}" : 'Hôte SMTP non configuré',
];

// ── 4. IMAP ─────────────────────────────────────────────────────────────────
$imapHost = setting('imap_host', '', $userId) ?: ($_ENV['IMAP_HOST'] ?? $smtpHost);
$integrations['imap'] = [
    'name'   => 'IMAP (email entrant)',
    'icon'   => 'fa-inbox',
    'color'  => '#8b5cf6',
    'source' => 'ENV + DB',
    'status' => isKeyValid($imapHost) ? 'configured' : 'missing',
    'detail' => isKeyValid($imapHost) ? "Hôte : {$imapHost}" : 'Non configuré — messagerie désactivée',
];

// ── 5. GMB (Google My Business) ─────────────────────────────────────────────
$gmbClientId = (string) setting('api_gmb_client_id', '', $userId);
$gmbToken    = (string) setting('api_gmb_access_token', '', $userId);
$integrations['gmb'] = [
    'name'   => 'Google My Business',
    'icon'   => 'fa-map-location-dot',
    'color'  => '#4285f4',
    'source' => 'Settings DB',
    'status' => isKeyValid($gmbToken) ? 'connected' : (isKeyValid($gmbClientId) ? 'configured' : 'missing'),
    'detail' => isKeyValid($gmbToken) ? 'Token OAuth actif' : (isKeyValid($gmbClientId) ? 'App configurée — pas encore connectée' : 'Non configuré'),
];

// ── 6. Google Search Console ────────────────────────────────────────────────
$gscClientId    = (string) setting('api_gsc_client_id', '', $userId);
$gscRefreshToken = (string) setting('api_gsc_refresh_token', '', $userId);
$integrations['gsc'] = [
    'name'   => 'Google Search Console',
    'icon'   => 'fa-magnifying-glass-chart',
    'color'  => '#ea4335',
    'source' => 'Settings DB',
    'status' => isKeyValid($gscRefreshToken) ? 'connected' : (isKeyValid($gscClientId) ? 'configured' : 'missing'),
    'detail' => isKeyValid($gscRefreshToken) ? 'OAuth connecté' : (isKeyValid($gscClientId) ? 'App configurée — pas encore connectée' : 'Non configuré'),
];

// ── 7. Facebook ─────────────────────────────────────────────────────────────
$fbToken = (string) setting('api_fb_access_token', '', $userId);
$integrations['facebook'] = [
    'name'   => 'Facebook',
    'icon'   => 'fa-facebook',
    'color'  => '#1877f2',
    'source' => 'Settings DB',
    'status' => isKeyValid($fbToken) ? 'configured' : 'missing',
    'detail' => isKeyValid($fbToken) ? 'Token actif' : 'Token Facebook non configuré',
];

// ── 8. Instagram ────────────────────────────────────────────────────────────
$igToken = (string) setting('ig_access_token', '', $userId);
$integrations['instagram'] = [
    'name'   => 'Instagram',
    'icon'   => 'fa-instagram',
    'color'  => '#e1306c',
    'source' => 'Settings DB',
    'status' => isKeyValid($igToken) ? 'configured' : 'missing',
    'detail' => isKeyValid($igToken) ? 'Token actif' : 'Token Instagram non configuré',
];

// ── 9. LinkedIn ─────────────────────────────────────────────────────────────
$liToken = (string) setting('li_access_token', '', $userId);
$integrations['linkedin'] = [
    'name'   => 'LinkedIn',
    'icon'   => 'fa-linkedin',
    'color'  => '#0a66c2',
    'source' => 'Settings DB',
    'status' => isKeyValid($liToken) ? 'configured' : 'missing',
    'detail' => isKeyValid($liToken) ? 'Token actif' : 'Token LinkedIn non configuré',
];

// ── 10. DataForSEO ──────────────────────────────────────────────────────────
$dfsLogin = setting('api_dataforseo_login', '', $userId) ?: ($_ENV['DATAFORSEO_LOGIN'] ?? '');
$integrations['dataforseo'] = [
    'name'   => 'DataForSEO',
    'icon'   => 'fa-chart-line',
    'color'  => '#6366f1',
    'source' => isKeyValid($_ENV['DATAFORSEO_LOGIN'] ?? '') ? 'ENV' : 'Settings DB',
    'status' => isKeyValid($dfsLogin) ? 'configured' : 'missing',
    'detail' => isKeyValid($dfsLogin) ? "Login : {$dfsLogin}" : 'Non configuré — tracking SEO désactivé',
];

// ── 11. Supabase ────────────────────────────────────────────────────────────
$supabaseUrl = $_ENV['SUPABASE_URL'] ?? '';
$supabaseKey = $_ENV['SUPABASE_ANON_KEY'] ?? '';
$integrations['supabase'] = [
    'name'   => 'Supabase (import biens)',
    'icon'   => 'fa-database',
    'color'  => '#3ecf8e',
    'source' => 'ENV',
    'status' => (isKeyValid($supabaseUrl) && isKeyValid($supabaseKey)) ? 'configured' : 'missing',
    'detail' => isKeyValid($supabaseKey) ? 'Clé présente' : 'Clé SUPABASE_ANON_KEY manquante dans .env',
];

// ── 12. APP_ENCRYPT_KEY ─────────────────────────────────────────────────────
$encryptKey = $_ENV['APP_ENCRYPT_KEY'] ?? '';
$integrations['encryption'] = [
    'name'   => 'Chiffrement DB (APP_ENCRYPT_KEY)',
    'icon'   => 'fa-lock',
    'color'  => '#64748b',
    'source' => 'ENV',
    'status' => isKeyValid($encryptKey) ? 'configured' : 'warning',
    'detail' => isKeyValid($encryptKey) ? 'Clé définie — données chiffrées sécurisées' : 'Clé manquante — chiffrement utilise une valeur par défaut insécurisée',
];

// ── Résumé ──────────────────────────────────────────────────────────────────
$counts = ['configured' => 0, 'connected' => 0, 'missing' => 0, 'warning' => 0, 'partial' => 0];
foreach ($integrations as $i) {
    $s = $i['status'];
    if (isset($counts[$s])) {
        $counts[$s]++;
    }
}
$healthScore = (int) round(
    (($counts['configured'] + $counts['connected']) / count($integrations)) * 100
);

echo json_encode([
    'ok'           => true,
    'score'        => $healthScore,
    'integrations' => $integrations,
    'counts'       => $counts,
    'checked_at'   => date('d/m/Y H:i'),
]);
