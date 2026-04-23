<?php
/**
 * Setup : insère un funnel de démo campagne_v1 + séquence 3 emails
 * Accès : /admin/api/funnels/setup-demo.php
 * À exécuter une seule fois.
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__, 4));
require_once ROOT_PATH . '/core/bootstrap.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non authentifié']);
    exit;
}

$db = db();

// ── Créer les tables si manquantes ────────────────────────────
require_once ROOT_PATH . '/modules/funnels/repositories/FunnelRepository.php';
new FunnelRepository($db); // ensureTables() appelé dans __construct

// ── Séquence 3 emails ─────────────────────────────────────────
$db->exec("CREATE TABLE IF NOT EXISTS crm_sequences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    status ENUM('active','draft','paused') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->exec("CREATE TABLE IF NOT EXISTS crm_sequence_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT UNSIGNED NOT NULL,
    step_order INT UNSIGNED NOT NULL,
    delay_days INT UNSIGNED NOT NULL DEFAULT 0,
    email_subject VARCHAR(255) NOT NULL,
    email_body_html TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_seq_step (sequence_id, step_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->exec("CREATE TABLE IF NOT EXISTS crm_sequence_enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id INT UNSIGNED NOT NULL,
    lead_id INT UNSIGNED NOT NULL,
    current_step INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','completed','unsubscribed','paused') NOT NULL DEFAULT 'active',
    next_send_at DATETIME NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_enrollment (sequence_id, lead_id),
    KEY idx_due (status, next_send_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$seqId = 0;
$seqRow = $db->query("SELECT id FROM crm_sequences WHERE name = 'Séquence Campagne Estimation' LIMIT 1")->fetch();
if ($seqRow) {
    $seqId = (int) $seqRow['id'];
} else {
    $db->prepare("INSERT INTO crm_sequences (name, description, status) VALUES (?, ?, 'active')")
       ->execute(['Séquence Campagne Estimation', 'Séquence 3 emails auto : J0 confirmation, J+2 valeur, J+4 relance RDV']);
    $seqId = (int) $db->lastInsertId();

    $steps = [
        [1, 0, 'Votre demande a bien été reçue [PRENOM] 👋',
         '<p>Bonjour [PRENOM],</p><p>Merci pour votre demande ! Je vous recontacte sous 48h avec une analyse personnalisée de votre bien.</p><p>Cordialement,<br>[ADVISOR_NAME]</p>'],
        [2, 2, '[PRENOM] — 3 erreurs qui font perdre 10 % sur la vente',
         '<p>Bonjour [PRENOM],</p><p>Les 3 erreurs les plus fréquentes : surestimer le prix, mauvaises photos, diagnostics tardifs. Je vous aide à les éviter.</p><p>[ADVISOR_NAME]</p>'],
        [3, 4, 'Passons à l\'étape suivante, [PRENOM] ?',
         '<p>Bonjour [PRENOM],</p><p>Je suis disponible pour un RDV de 30 min sans engagement. <a href="[RDV_URL]">Choisissez votre créneau ici</a>.</p><p>[ADVISOR_NAME]</p>'],
    ];
    $ins = $db->prepare("INSERT INTO crm_sequence_steps (sequence_id,step_order,delay_days,email_subject,email_body_html) VALUES (?,?,?,?,?)");
    foreach ($steps as $s) {
        $ins->execute([$seqId, ...$s]);
    }
}

// ── Funnel de démo ────────────────────────────────────────────
$funnelSlug = 'campagne-estimation-aix';
$existing = $db->prepare("SELECT id,slug FROM funnels WHERE slug = ? LIMIT 1");
$existing->execute([$funnelSlug]);
$existingFunnel = $existing->fetch(PDO::FETCH_ASSOC);

if ($existingFunnel) {
    echo json_encode([
        'ok'          => true,
        'message'     => 'Funnel déjà existant.',
        'funnel_id'   => (int) $existingFunnel['id'],
        'sequence_id' => $seqId,
        'preview_url' => '/lp/' . $funnelSlug,
    ]);
    exit;
}

$db->prepare("INSERT INTO funnels
    (canal, template_id, name, ville, keyword, persona, slug,
     seo_title, h1, promise, cta_label, sequence_id,
     thankyou_type, indexable, form_type, status, published_at)
    VALUES
    ('google_ads', 'campagne_v1', 'Campagne Estimation Aix — Demo',
     'Aix-en-Provence', 'vendre maison aix', 'vendeur', :slug,
     'Estimer votre bien à Aix-en-Provence — Gratuit & Sans engagement',
     'Vendez votre bien à Aix-en-Provence au meilleur prix',
     'Obtenez une estimation gratuite en 48h — sans engagement',
     'Recevoir mon estimation gratuite',
     :seq_id, 'estimation_recue', 0, 'contact', 'published', NOW())
")->execute([':slug' => $funnelSlug, ':seq_id' => $seqId]);

$funnelId = (int) $db->lastInsertId();

echo json_encode([
    'ok'          => true,
    'message'     => 'Funnel de démo créé avec succès !',
    'funnel_id'   => $funnelId,
    'sequence_id' => $seqId,
    'preview_url' => '/lp/' . $funnelSlug,
    'admin_url'   => '/admin?module=funnels&action=edit&id=' . $funnelId,
]);
