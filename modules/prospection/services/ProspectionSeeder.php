<?php

declare(strict_types=1);

/**
 * ProspectionSeeder
 *
 * Insère une campagne de démonstration complète :
 * - 1 campagne "Démo Prospection — Découverte"
 * - 5 étapes de séquence (séquence officielle)
 * - 8 contacts tests avec scénarios variés
 * - Inscription des contacts dans la campagne
 * - Simulation des états métier
 *
 * Convention scénarios (via notes du contact) :
 *   sim:no_open        → reçoit tout, n'ouvre rien
 *   sim:bounce         → email bounced dès le premier envoi
 *   sim:replied_step1  → répond après l'email 1
 *   sim:replied_step3  → répond après l'email 3
 *   sim:opened_no_reply→ ouvre mais ne répond pas
 *   sim:clicked        → clique sur un lien
 *   sim:paused         → mis en pause manuellement
 *   sim:unsub          → se désabonne
 */
class ProspectionSeeder
{
    private PDO $db;
    private int $userId;

    public function __construct(PDO $db, int $userId)
    {
        $this->db     = $db;
        $this->userId = $userId;
    }

    /**
     * Exécute la seed complète.
     * Si une campagne démo existe déjà pour cet utilisateur, retourne son ID sans recréer.
     *
     * @return array{campaign_id:int, contacts_created:int, already_existed:bool}
     */
    public function run(): array
    {
        // Vérifie si la démo existe déjà
        $existing = $this->db->prepare(
            "SELECT id FROM email_campaigns WHERE user_id = :uid AND name LIKE '%Démo Prospection%' AND deleted_at IS NULL LIMIT 1"
        );
        $existing->execute([':uid' => $this->userId]);
        $existingId = $existing->fetchColumn();

        if ($existingId) {
            return ['campaign_id' => (int)$existingId, 'contacts_created' => 0, 'already_existed' => true];
        }

        $campaignId     = $this->createCampaign();
        $this->createSequenceSteps($campaignId);
        $contactIds     = $this->createContacts();
        $this->enrollContacts($campaignId, $contactIds);

        return [
            'campaign_id'     => $campaignId,
            'contacts_created'=> count($contactIds),
            'already_existed' => false,
        ];
    }

    // ------------------------------------------------------------------

    private function createCampaign(): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO email_campaigns (user_id, name, description, objective, status)
             VALUES (:uid, :name, :desc, :obj, 'active')"
        );
        $stmt->execute([
            ':uid'  => $this->userId,
            ':name' => 'Démo Prospection — Découverte',
            ':desc' => 'Campagne de démonstration avec 8 scénarios contacts simulés. Séquence de 5 emails axée sur la découverte et la qualification.',
            ':obj'  => 'Simuler un cycle complet de prospection et tester tous les états métier du module.',
        ]);

        return (int)$this->db->lastInsertId();
    }

    private function createSequenceSteps(int $campaignId): void
    {
        $steps = [
            [
                'step_order' => 1, 'delay_days' => 0,
                'subject'    => 'Question rapide',
                'body_text'  => "Salut {{first_name}},\n\nJe me permets de te contacter rapidement car je travaille en ce moment avec plusieurs professionnels.\n\nJe fais un petit sondage terrain :\nAujourd'hui, tu développes ton activité plutôt via :\n1) recommandation\n2) contenu / réseaux\n3) prospection directe\n4) autre ?\n\nCurieux d'avoir ton retour.",
            ],
            [
                'step_order' => 2, 'delay_days' => 2,
                'subject'    => 'Tu fais partie des rares ?',
                'body_text'  => "Salut {{first_name}},\n\nJe reviens vers toi car plusieurs retours me montrent une chose :\nbeaucoup travaillent beaucoup, mais peu ont un vrai système régulier d'opportunités.\n\nTu arrives aujourd'hui à avoir une visibilité stable sur tes prochaines opportunités ou c'est encore irrégulier ?",
            ],
            [
                'step_order' => 3, 'delay_days' => 5,
                'subject'    => "Ce que j'observe",
                'body_text'  => "Salut {{first_name}},\n\nCe que j'observe souvent :\nbeaucoup dépendent encore trop du bouche-à-oreille ou des plateformes.\n\nCeux qui passent un cap ont généralement un système simple mais structuré.\n\nTu serais ouvert à découvrir une autre approche ?",
            ],
            [
                'step_order' => 4, 'delay_days' => 9,
                'subject'    => 'Je peux te montrer',
                'body_text'  => "Salut {{first_name}},\n\nJe mets actuellement en place une mécanique simple pour aider à structurer l'acquisition et le suivi.\n\nSi tu veux, je peux te montrer la logique.",
            ],
            [
                'step_order' => 5, 'delay_days' => 14,
                'subject'    => 'Je clôture',
                'body_text'  => "Salut {{first_name}},\n\nJe ne vais pas t'embêter plus longtemps.\n\nSi le sujet t'intéresse, je peux te montrer comment je structure cela.\nSinon aucun souci.",
            ],
        ];

        $stmt = $this->db->prepare(
            'INSERT INTO email_sequence_steps (campaign_id, step_order, delay_days, subject, body_text, is_active)
             VALUES (:campaign_id, :step_order, :delay_days, :subject, :body_text, 1)'
        );

        foreach ($steps as $s) {
            $stmt->execute([
                ':campaign_id' => $campaignId,
                ':step_order'  => $s['step_order'],
                ':delay_days'  => $s['delay_days'],
                ':subject'     => $s['subject'],
                ':body_text'   => $s['body_text'],
            ]);
        }
    }

    private function createContacts(): array
    {
        $contacts = [
            [
                'first_name' => 'Sophie',   'last_name' => 'Martin',
                'email'      => 'demo.no-open@test-prospection.invalid',
                'company'    => 'Agence Étoile',   'city' => 'Aix-en-Provence',
                'notes'      => 'sim:no_open — reçoit tout, n\'ouvre rien',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Lucas',    'last_name' => 'Bernard',
                'email'      => 'demo.opened-no-reply@test-prospection.invalid',
                'company'    => 'Conseil RH Sud',  'city' => 'Marseille',
                'notes'      => 'sim:opened_no_reply — ouvre mais ne répond pas',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Emma',     'last_name' => 'Dubois',
                'email'      => 'demo.clicked@test-prospection.invalid',
                'company'    => 'Studio Web 13',   'city' => 'Aix-en-Provence',
                'notes'      => 'sim:clicked — clique sur un lien',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Thomas',   'last_name' => 'Leroy',
                'email'      => 'demo.replied-step1@test-prospection.invalid',
                'company'    => 'Immobilier Provence', 'city' => 'Aix-en-Provence',
                'notes'      => 'sim:replied_step1 — répond dès le premier email',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Claire',   'last_name' => 'Petit',
                'email'      => 'demo.replied-step3@test-prospection.invalid',
                'company'    => 'Architecte 13',   'city' => 'Toulon',
                'notes'      => 'sim:replied_step3 — répond après le 3e email',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Maxime',   'last_name' => 'Moreau',
                'email'      => 'demo.bounce@test-prospection.invalid',
                'company'    => 'Startup Tech Aix', 'city' => 'Aix-en-Provence',
                'notes'      => 'sim:bounce — email invalide / bounced',
                'email_status'=> 'invalid',
                'status'     => 'bounced',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Juliette', 'last_name' => 'Simon',
                'email'      => 'demo.paused@test-prospection.invalid',
                'company'    => 'Conseil Jurisoft', 'city' => 'Nice',
                'notes'      => 'sim:paused — mis en pause manuellement',
                'source'     => 'demo',
            ],
            [
                'first_name' => 'Antoine',  'last_name' => 'Laurent',
                'email'      => 'demo.unsub@test-prospection.invalid',
                'company'    => 'Commerce Laurent', 'city' => 'Arles',
                'notes'      => 'sim:unsub — s\'est désabonné',
                'source'     => 'demo',
            ],
        ];

        $stmt = $this->db->prepare(
            'INSERT INTO prospect_contacts
                (user_id, first_name, last_name, email, company, city, notes, source, email_status, status)
             VALUES
                (:user_id, :first_name, :last_name, :email, :company, :city, :notes, :source, :email_status, :status)
             ON DUPLICATE KEY UPDATE notes = VALUES(notes)'
        );

        $ids = [];
        foreach ($contacts as $c) {
            $stmt->execute([
                ':user_id'     => $this->userId,
                ':first_name'  => $c['first_name'],
                ':last_name'   => $c['last_name'],
                ':email'       => $c['email'],
                ':company'     => $c['company']      ?? null,
                ':city'        => $c['city']         ?? null,
                ':notes'       => $c['notes']        ?? null,
                ':source'      => $c['source']       ?? 'demo',
                ':email_status'=> $c['email_status'] ?? 'unknown',
                ':status'      => $c['status']       ?? 'active',
            ]);

            // Récupère l'ID (insert ou existant)
            $id = (int)$this->db->lastInsertId();
            if ($id === 0) {
                $s2 = $this->db->prepare('SELECT id FROM prospect_contacts WHERE email = :e AND user_id = :u LIMIT 1');
                $s2->execute([':e' => $c['email'], ':u' => $this->userId]);
                $id = (int)$s2->fetchColumn();
            }
            if ($id > 0) {
                $ids[] = ['id' => $id, 'scenario' => $c['notes']];
            }
        }

        return $ids;
    }

    private function enrollContacts(int $campaignId, array $contactData): void
    {
        // Récupère les étapes de la campagne
        $stmt  = $this->db->prepare(
            'SELECT * FROM email_sequence_steps WHERE campaign_id = :cid ORDER BY step_order ASC'
        );
        $stmt->execute([':cid' => $campaignId]);
        $steps = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $firstDelay = (int)($steps[0]['delay_days'] ?? 0);
        $nextSendAt = date('Y-m-d H:i:s', strtotime("+{$firstDelay} days"));

        $ins = $this->db->prepare(
            'INSERT IGNORE INTO campaign_contacts
                (campaign_id, contact_id, status, current_step, next_send_at)
             VALUES (:cid, :ctid, "enrolled", 0, :next)'
        );

        foreach ($contactData as $cd) {
            $ins->execute([
                ':cid'  => $campaignId,
                ':ctid' => $cd['id'],
                ':next' => $nextSendAt,
            ]);
        }
    }
}
