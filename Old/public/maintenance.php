<?php
// ── Chargement .env ─────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim(trim($v), '"\'');
        $env[$k] = $v;
    }
}

$appName  = $env['APP_NAME']  ?? 'Notre agence';
$appEmail = $env['APP_EMAIL'] ?? '';
$appPhone = $env['APP_PHONE'] ?? '';

// ── Connexion DB ─────────────────────────────────────────────
$dbOk = false;
try {
    $pdo = new PDO(
        'mysql:host=' . ($env['DB_HOST'] ?? 'localhost') . ';dbname=' . ($env['DB_NAME'] ?? '') . ';charset=utf8mb4',
        $env['DB_USER'] ?? '',
        $env['DB_PASS'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
    );
    $dbOk = true;
} catch (PDOException $e) {
    error_log('Maintenance DB error: ' . $e->getMessage());
}

// ── Accès admin bypass (ex: ?bypass=VOTRE_CLE) ──────────────
$bypassKey = $env['APP_SECRET'] ?? '';
if ($bypassKey && isset($_GET['bypass']) && hash_equals($bypassKey, $_GET['bypass'])) {
    header('Location: /');
    exit;
}

http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Maintenance — <?= htmlspecialchars($appName) ?></title>
    <link rel="icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8f6f2;
            color: #2c2c2c;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 40px rgba(0,0,0,.08);
        }

        .icon {
            width: 72px;
            height: 72px;
            background: #fef3e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: .75rem;
            color: #1a1a1a;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 1.5rem 0;
        }

        .contact-label {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #999;
            margin-bottom: .75rem;
        }

        .contact-links {
            display: flex;
            flex-direction: column;
            gap: .5rem;
        }

        .contact-links a {
            color: #b8860b;
            text-decoration: none;
            font-weight: 500;
            font-size: .95rem;
        }

        .contact-links a:hover { text-decoration: underline; }

        .db-status {
            margin-top: 1.5rem;
            font-size: .75rem;
            color: #bbb;
        }

        .dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }
        .dot.ok  { background: #22c55e; }
        .dot.err { background: #ef4444; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔧</div>
        <h1>Site en maintenance</h1>
        <p>
            Nous effectuons une mise à jour de notre site.<br>
            Nous serons de retour très prochainement.
        </p>

        <?php if ($appEmail || $appPhone): ?>
        <hr class="divider">
        <p class="contact-label">Nous contacter pendant ce temps</p>
        <div class="contact-links">
            <?php if ($appPhone): ?>
                <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $appPhone)) ?>">
                    📞 <?= htmlspecialchars($appPhone) ?>
                </a>
            <?php endif; ?>
            <?php if ($appEmail): ?>
                <a href="mailto:<?= htmlspecialchars($appEmail) ?>">
                    ✉️ <?= htmlspecialchars($appEmail) ?>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="db-status">
            <span class="dot <?= $dbOk ? 'ok' : 'err' ?>"></span>
            Système <?= $dbOk ? 'opérationnel' : 'en cours de restauration' ?>
        </p>
    </div>
</body>
</html>
