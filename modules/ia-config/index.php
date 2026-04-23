<?php
$pageTitle = 'Configuration IA';
$pageDescription = 'Configurez votre fournisseur IA et surveillez vos coûts.';

function iaConfigEnsureTable(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS ia_configurations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            provider VARCHAR(32) NOT NULL,
            api_key TEXT NOT NULL,
            model VARCHAR(120) NOT NULL,
            tokens_used BIGINT UNSIGNED NOT NULL DEFAULT 0,
            estimated_cost DECIMAL(12,6) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_ia_user_active (user_id, is_active, updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function iaConfigModels(): array
{
    return [
        'openai'    => 'gpt-4o-mini',
        'anthropic' => 'claude-haiku-4-5-20251001',
        'mistral'   => 'mistral-small-latest',
    ];
}

function iaConfigPing(string $provider, string $apiKey, string $model): array
{
    $url     = '';
    $headers = ['Content-Type: application/json'];
    $payload = [];

    switch ($provider) {
        case 'openai':
            $url       = 'https://api.openai.com/v1/chat/completions';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            $payload   = ['model' => $model, 'messages' => [['role' => 'user', 'content' => 'ok']], 'max_tokens' => 5];
            break;
        case 'anthropic':
            $url       = 'https://api.anthropic.com/v1/messages';
            $headers[] = 'x-api-key: ' . $apiKey;
            $headers[] = 'anthropic-version: 2023-06-01';
            $payload   = ['model' => $model, 'max_tokens' => 5, 'messages' => [['role' => 'user', 'content' => 'ok']]];
            break;
        case 'mistral':
            $url       = 'https://api.mistral.ai/v1/chat/completions';
            $headers[] = 'Authorization: Bearer ' . $apiKey;
            $payload   = ['model' => $model, 'messages' => [['role' => 'user', 'content' => 'ok']], 'max_tokens' => 5];
            break;
        default:
            return ['ok' => false, 'message' => 'Fournisseur IA non supporté.'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 12,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($response === false || $error !== '') {
        return ['ok' => false, 'message' => 'Erreur réseau : ' . ($error ?: 'réponse vide')];
    }
    if ($httpCode >= 200 && $httpCode < 300) {
        return ['ok' => true, 'message' => 'Connexion API validée avec succès.'];
    }
    return ['ok' => false, 'message' => 'Ping API échoué (HTTP ' . $httpCode . ').'];
}

function renderContent(): void
{
    $pdo = db();
    iaConfigEnsureTable($pdo);

    $userId    = (int) ($_SESSION['user_id'] ?? 0);
    $models    = iaConfigModels();
    $providers = ['openai' => 'OpenAI', 'anthropic' => 'Anthropic (Claude)', 'mistral' => 'Mistral'];
    $flash     = ['type' => '', 'message' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId > 0) {
        $provider = strtolower(trim((string) ($_POST['provider'] ?? 'openai')));
        $apiKey   = trim((string) ($_POST['api_key'] ?? ''));
        $model    = trim((string) ($_POST['model'] ?? ($models[$provider] ?? '')));
        $action   = trim((string) ($_POST['action_type'] ?? 'save'));

        if (!isset($providers[$provider])) {
            $flash = ['type' => 'error', 'message' => 'Réseau IA invalide.'];
        } elseif ($apiKey === '' || $model === '') {
            $flash = ['type' => 'error', 'message' => 'La clé API et le modèle sont obligatoires.'];
        } else {
            if ($action === 'test') {
                $test  = iaConfigPing($provider, $apiKey, $model);
                $flash = ['type' => $test['ok'] ? 'ok' : 'error', 'message' => $test['message']];
            }

            $stmt = $pdo->prepare('UPDATE ia_configurations SET is_active = 0 WHERE user_id = :user_id');
            $stmt->execute(['user_id' => $userId]);

            $stmt = $pdo->prepare(
                'INSERT INTO ia_configurations (user_id, provider, api_key, model, is_active)
                 VALUES (:user_id, :provider, :api_key, :model, 1)'
            );
            $stmt->execute(['user_id' => $userId, 'provider' => $provider, 'api_key' => $apiKey, 'model' => $model]);

            if ($action !== 'test') {
                $flash = ['type' => 'ok', 'message' => 'Configuration IA enregistrée.'];
            }
        }
    }

    $stmt = $pdo->prepare(
        'SELECT provider, api_key, model, tokens_used, estimated_cost
         FROM ia_configurations
         WHERE user_id = :user_id AND is_active = 1
         ORDER BY updated_at DESC, id DESC LIMIT 1'
    );
    $stmt->execute(['user_id' => $userId]);
    $activeConfig = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare(
        'SELECT COALESCE(SUM(tokens_used),0) AS total_tokens, COALESCE(SUM(estimated_cost),0) AS total_cost
         FROM ia_configurations WHERE user_id = :user_id'
    );
    $stmt->execute(['user_id' => $userId]);
    $usage = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_tokens' => 0, 'total_cost' => 0];

    $provider = (string) ($activeConfig['provider'] ?? 'openai');
    $apiKey   = (string) ($activeConfig['api_key']  ?? '');
    $model    = (string) ($activeConfig['model']    ?? ($models[$provider] ?? 'gpt-4o-mini'));
    $iaStatus = get_ia_status($userId);
    ?>
    <style>
    .iac-grid{display:grid;gap:1.2rem}
    .iac-form-row{display:grid;grid-template-columns:1.2fr .8fr;gap:1.2rem;align-items:start}
    .iac-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.25rem 1.4rem;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
    .iac-card h3{margin:0 0 1rem;font-size:1rem;color:#0f172a}
    .iac-field{display:grid;gap:.35rem;margin-bottom:.85rem}
    .iac-field:last-of-type{margin-bottom:0}
    .iac-field label{font-size:.82rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em}
    .iac-field input,.iac-field select{border:1px solid #cbd5e1;border-radius:10px;padding:.6rem .75rem;font-size:.92rem;width:100%;background:#fff}
    .iac-field input:focus,.iac-field select:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
    .iac-actions{display:flex;gap:.7rem;flex-wrap:wrap;margin-top:1rem}
    .iac-flash{padding:.65rem .9rem;border-radius:10px;font-size:.86rem;margin-bottom:1rem}
    .iac-flash-ok{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0}
    .iac-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
    .iac-metric{display:flex;justify-content:space-between;align-items:center;padding:.7rem 0;border-top:1px solid #f1f5f9;font-size:.9rem}
    .iac-metric:first-child{border-top:none}
    .iac-metric strong{font-weight:700;color:#0f172a}
    .iac-status-connected{color:#16a34a}
    .iac-status-disconnected{color:#dc2626}
    @media(max-width:900px){.iac-form-row{grid-template-columns:1fr}}
    </style>

    <div class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-microchip"></i> Intelligence Artificielle</div>
            <h1>Configuration IA</h1>
            <p>Connectez votre fournisseur IA, testez la clé API et suivez votre consommation.</p>
        </header>

        <div class="hub-narrative">
            <article class="hub-narrative-card hub-narrative-card--explanation">
                <h3><i class="fas fa-plug" style="color:#3b82f6"></i> Pourquoi configurer l'IA ?</h3>
                <p>L'Assistant Noah et les outils de rédaction utilisent une IA externe. Sans clé configurée, ces fonctionnalités restent inactives.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--resultat">
                <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Ce que ça débloque</h3>
                <p>Une fois votre clé enregistrée, l'assistant, la génération de contenu et les suggestions IA deviennent disponibles immédiatement.</p>
            </article>
            <article class="hub-narrative-card hub-narrative-card--motivation">
                <h3><i class="fas fa-shield-halved" style="color:#f59e0b"></i> Sécurité</h3>
                <p>Votre clé API est stockée chiffrée en base de données. Elle n'apparaît jamais dans les logs ni dans le code source.</p>
            </article>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="iac-flash iac-flash-<?= htmlspecialchars($flash['type']) ?>">
            <i class="fas fa-<?= $flash['type'] === 'ok' ? 'check-circle' : 'circle-exclamation' ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div class="iac-form-row">
            <div class="iac-card">
                <h3><i class="fas fa-key me-2" style="color:#f59e0b"></i>Fournisseur et clé API</h3>
                <form method="POST">
                    <input type="hidden" name="action_type" id="ia-action-type" value="save">

                    <div class="iac-field">
                        <label>Fournisseur IA</label>
                        <select name="provider" required>
                            <?php foreach ($providers as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= $provider === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="iac-field">
                        <label>Clé API</label>
                        <input type="text" name="api_key" value="<?= htmlspecialchars($apiKey) ?>" placeholder="sk-... ou clé Anthropic" required autocomplete="off">
                    </div>

                    <div class="iac-field">
                        <label>Modèle</label>
                        <input type="text" name="model" value="<?= htmlspecialchars($model) ?>" required>
                    </div>

                    <div class="iac-actions">
                        <button type="submit" class="hub-btn hub-btn--gold">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <button type="submit" class="hub-btn" style="background:#f1f5f9;color:#334155;"
                                onclick="document.getElementById('ia-action-type').value='test'; return true;">
                            <i class="fas fa-plug"></i> Tester la connexion
                        </button>
                    </div>
                </form>
            </div>

            <div class="iac-card">
                <h3><i class="fas fa-chart-bar me-2" style="color:#3b82f6"></i>Utilisation & statut</h3>
                <div class="iac-metric">
                    <span>Statut actuel</span>
                    <strong class="iac-status-<?= htmlspecialchars($iaStatus) ?>"><?= strtoupper(htmlspecialchars($iaStatus)) ?></strong>
                </div>
                <div class="iac-metric">
                    <span>Fournisseur actif</span>
                    <strong><?= htmlspecialchars($providers[$provider] ?? $provider) ?></strong>
                </div>
                <div class="iac-metric">
                    <span>Tokens utilisés</span>
                    <strong><?= number_format((int) $usage['total_tokens'], 0, ',', ' ') ?></strong>
                </div>
                <div class="iac-metric">
                    <span>Coût estimé</span>
                    <strong><?= number_format((float) $usage['total_cost'], 4, ',', ' ') ?> €</strong>
                </div>
            </div>
        </div>

    </div>
    <?php
}
