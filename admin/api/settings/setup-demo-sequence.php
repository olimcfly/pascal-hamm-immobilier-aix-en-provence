<?php
/**
 * Script de setup — Séquence email de démonstration (3 emails)
 * À exécuter une seule fois depuis l'admin pour créer la séquence de test.
 *
 * Accès : /admin/api/settings/setup-demo-sequence.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../core/bootstrap.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Non authentifié.']);
    exit;
}

$pdo = db();

// ── Créer les tables si elles n'existent pas ──────────────────────────────────

$pdo->exec("CREATE TABLE IF NOT EXISTS crm_sequences (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(180) NOT NULL,
    description  TEXT NULL,
    status       ENUM('active','draft','paused') NOT NULL DEFAULT 'active',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS crm_sequence_steps (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id    INT UNSIGNED NOT NULL,
    step_order     INT UNSIGNED NOT NULL,
    delay_days     INT UNSIGNED NOT NULL DEFAULT 0,
    email_subject  VARCHAR(255) NOT NULL,
    email_body_html TEXT NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_seq_step (sequence_id, step_order),
    KEY idx_seq (sequence_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS crm_sequence_enrollments (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id   INT UNSIGNED NOT NULL,
    lead_id       INT UNSIGNED NOT NULL,
    current_step  INT UNSIGNED NOT NULL DEFAULT 0,
    status        ENUM('active','completed','unsubscribed','paused') NOT NULL DEFAULT 'active',
    next_send_at  DATETIME NULL,
    enrolled_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_enrollment (sequence_id, lead_id),
    KEY idx_due (status, next_send_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ── Vérifier si la séquence demo existe déjà ─────────────────────────────────

$existing = $pdo->query("SELECT id FROM crm_sequences WHERE name = 'Séquence Campagne — Estimation & Contact' LIMIT 1")->fetch();
if ($existing) {
    echo json_encode(['ok' => true, 'message' => 'Séquence démo déjà présente.', 'sequence_id' => (int)$existing['id']]);
    exit;
}

// ── Insérer la séquence ───────────────────────────────────────────────────────

$pdo->prepare("INSERT INTO crm_sequences (name, description, status) VALUES (?, ?, 'active')")
    ->execute([
        'Séquence Campagne — Estimation & Contact',
        'Séquence automatique 3 emails déclenchée après soumission d\'un formulaire landing page. J0 confirmation, J+2 valeur, J+4 relance.',
    ]);

$seqId = (int) $pdo->lastInsertId();

// ── Insérer les 3 étapes ──────────────────────────────────────────────────────

$steps = [
    [
        'step_order'     => 1,
        'delay_days'     => 0,
        'email_subject'  => 'Votre demande a bien été reçue, [PRENOM] 👋',
        'email_body_html' => <<<HTML
<div style="font-family:sans-serif;max-width:580px;margin:0 auto;color:#0f172a">
  <div style="background:linear-gradient(135deg,#0f2d5a,#1e4d8c);padding:28px 24px;border-radius:10px 10px 0 0">
    <h1 style="color:#fff;font-size:1.3rem;margin:0">Bonjour [PRENOM] 👋</h1>
    <p style="color:#bfdbfe;margin:6px 0 0;font-size:.9rem">Votre demande a bien été reçue</p>
  </div>
  <div style="background:#f8fafc;padding:24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px">
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:16px">
      Merci pour votre demande ! Je vais l'étudier avec soin et vous recontacter
      <strong>sous 48h</strong> pour vous apporter une réponse personnalisée.
    </p>
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:16px">
      En attendant, voici ce que je vais préparer pour vous :
    </p>
    <ul style="font-size:.9rem;line-height:1.7;color:#475569;padding-left:20px">
      <li>Une analyse du marché local dans votre secteur</li>
      <li>Une estimation réaliste basée sur les dernières transactions</li>
      <li>Des conseils pour optimiser votre projet</li>
    </ul>
    <div style="background:#eff6ff;border-left:3px solid #3b82f6;padding:12px 16px;border-radius:0 8px 8px 0;margin:20px 0">
      <strong style="font-size:.88rem;color:#1e40af">💡 Bon à savoir :</strong>
      <p style="font-size:.85rem;color:#475569;margin:4px 0 0">Mon service est gratuit et sans engagement. Vous n'avez aucune obligation.</p>
    </div>
    <p style="font-size:.9rem;color:#64748b">À très bientôt,<br><strong>[ADVISOR_NAME]</strong><br>Conseiller immobilier</p>
  </div>
</div>
HTML,
    ],
    [
        'step_order'     => 2,
        'delay_days'     => 2,
        'email_subject'  => '[PRENOM], 3 choses que la plupart des vendeurs ignorent',
        'email_body_html' => <<<HTML
<div style="font-family:sans-serif;max-width:580px;margin:0 auto;color:#0f172a">
  <div style="background:linear-gradient(135deg,#0f2d5a,#1e4d8c);padding:28px 24px;border-radius:10px 10px 0 0">
    <h1 style="color:#fff;font-size:1.2rem;margin:0">3 erreurs qui coûtent cher aux vendeurs</h1>
    <p style="color:#bfdbfe;margin:6px 0 0;font-size:.88rem">Ce que j'observe sur le marché local</p>
  </div>
  <div style="background:#f8fafc;padding:24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px">
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:20px">
      Bonjour [PRENOM], après avoir accompagné plus de 200 vendeurs sur notre marché local,
      voici les 3 erreurs les plus courantes que je vois régulièrement :
    </p>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:14px">
      <div style="font-weight:700;color:#dc2626;margin-bottom:6px">❌ Erreur n°1 — Surestimer son bien</div>
      <p style="font-size:.88rem;color:#475569;line-height:1.5;margin:0">
        Un prix trop élevé au départ rallonge le délai de vente de 3 à 6 mois et force souvent
        une négociation à la baisse. Les acheteurs voient l'historique de prix.
      </p>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:14px">
      <div style="font-weight:700;color:#dc2626;margin-bottom:6px">❌ Erreur n°2 — Mauvaises photos</div>
      <p style="font-size:.88rem;color:#475569;line-height:1.5;margin:0">
        90% des acheteurs commencent leur recherche en ligne. Des photos de qualité
        peuvent multiplier par 3 le nombre de visites.
      </p>
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px">
      <div style="font-weight:700;color:#dc2626;margin-bottom:6px">❌ Erreur n°3 — Négliger les diagnostics</div>
      <p style="font-size:.88rem;color:#475569;line-height:1.5;margin:0">
        Des diagnostics incomplets ou périmés peuvent bloquer la vente au dernier moment
        et vous coûter plusieurs milliers d'euros.
      </p>
    </div>

    <p style="font-size:.9rem;color:#475569;margin-bottom:16px">
      Je serai à votre côté pour éviter tous ces pièges. Avez-vous des questions entre-temps ?
    </p>

    <p style="font-size:.9rem;color:#64748b">Cordialement,<br><strong>[ADVISOR_NAME]</strong></p>
  </div>
</div>
HTML,
    ],
    [
        'step_order'     => 3,
        'delay_days'     => 4,
        'email_subject'  => '[PRENOM], passons à l\'étape suivante ?',
        'email_body_html' => <<<HTML
<div style="font-family:sans-serif;max-width:580px;margin:0 auto;color:#0f172a">
  <div style="background:linear-gradient(135deg,#0f2d5a,#1e4d8c);padding:28px 24px;border-radius:10px 10px 0 0">
    <h1 style="color:#fff;font-size:1.2rem;margin:0">Vous avez toujours un projet immobilier, [PRENOM] ?</h1>
    <p style="color:#bfdbfe;margin:6px 0 0;font-size:.88rem">Je suis disponible pour en discuter</p>
  </div>
  <div style="background:#f8fafc;padding:24px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 10px 10px">
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:16px">
      Bonjour [PRENOM],
    </p>
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:16px">
      Il y a quelques jours, vous avez sollicité mes services. Je voulais m'assurer
      que tout est clair et que vous disposez de toutes les informations pour avancer.
    </p>
    <p style="font-size:.95rem;line-height:1.6;margin-bottom:24px">
      <strong>Voici ce que je vous propose :</strong> un rendez-vous de 30 minutes (en présentiel ou par téléphone)
      pour faire le point sur votre projet, sans engagement de votre part.
    </p>

    <div style="text-align:center;margin-bottom:24px">
      <a href="[RDV_URL]" style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:700;padding:14px 28px;border-radius:8px;text-decoration:none;font-size:.95rem">
        📅 Prendre rendez-vous gratuitement
      </a>
    </div>

    <div style="background:#eff6ff;border-radius:8px;padding:14px 16px;margin-bottom:20px">
      <p style="font-size:.85rem;color:#1e40af;margin:0">
        <strong>Rappel :</strong> Mon service est 100% gratuit.
        Je ne perçois des honoraires qu'à la signature de la vente.
      </p>
    </div>

    <p style="font-size:.88rem;color:#64748b;margin-bottom:4px">
      Ou répondez simplement à cet email — je vous recontacte dans les 24h.
    </p>
    <p style="font-size:.9rem;color:#64748b">À bientôt,<br><strong>[ADVISOR_NAME]</strong></p>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:20px 0">
    <p style="font-size:.72rem;color:#94a3b8;text-align:center">
      Vous ne souhaitez plus recevoir ces emails ?
      <a href="#" style="color:#94a3b8">Se désabonner</a>
    </p>
  </div>
</div>
HTML,
    ],
];

$insertStep = $pdo->prepare(
    "INSERT INTO crm_sequence_steps (sequence_id, step_order, delay_days, email_subject, email_body_html)
     VALUES (:seq_id, :step_order, :delay_days, :subject, :body)"
);

foreach ($steps as $step) {
    $insertStep->execute([
        ':seq_id'     => $seqId,
        ':step_order' => $step['step_order'],
        ':delay_days' => $step['delay_days'],
        ':subject'    => $step['email_subject'],
        ':body'       => $step['email_body_html'],
    ]);
}

echo json_encode([
    'ok'          => true,
    'message'     => 'Séquence démo créée avec succès.',
    'sequence_id' => $seqId,
    'steps'       => count($steps),
    'hint'        => "Liez cette séquence à un funnel via le champ sequence_id = {$seqId} dans la table funnels.",
]);
