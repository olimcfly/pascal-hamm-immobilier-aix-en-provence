<?php

declare(strict_types=1);

require_once MODULES_PATH . '/prospection/repositories/SequenceRepository.php';
require_once MODULES_PATH . '/prospection/repositories/CampaignRepository.php';
require_once MODULES_PATH . '/prospection/services/ProspectionMailer.php';

class SequenceService
{
    public function __construct(
        private readonly SequenceRepository $repo,
        private readonly CampaignRepository $campaignRepo,
        private readonly int $userId
    ) {}

    // ------------------------------------------------------------------
    // GESTION DES ÉTAPES
    // ------------------------------------------------------------------

    public function getSteps(int $campaignId): array
    {
        if (!$this->campaignRepo->findById($campaignId, $this->userId)) {
            return [];
        }
        return $this->repo->getStepsByCampaign($campaignId);
    }

    public function addStep(int $campaignId, array $input): array
    {
        $campaign = $this->campaignRepo->findById($campaignId, $this->userId);
        if (!$campaign) {
            return ['ok' => false, 'errors' => ['global' => 'Campagne introuvable.']];
        }

        $errors = $this->validateStep($input);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->repo->createStep([
            'campaign_id' => $campaignId,
            'delay_days'  => (int) ($input['delay_days'] ?? 0),
            'subject'     => trim($input['subject']),
            'body_text'   => trim($input['body_text']),
            'is_active'   => 1,
        ]);

        $this->repo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $campaignId,
            'event'      => 'step_added',
            'detail'     => 'Étape ajoutée : ' . trim($input['subject']),
        ]);

        return ['ok' => true, 'id' => $id];
    }

    public function updateStep(int $campaignId, int $stepId, array $input): array
    {
        if (!$this->campaignRepo->findById($campaignId, $this->userId)) {
            return ['ok' => false, 'errors' => ['global' => 'Campagne introuvable.']];
        }

        $step = $this->repo->findStepById($stepId);
        if (!$step || (int) $step['campaign_id'] !== $campaignId) {
            return ['ok' => false, 'errors' => ['global' => 'Étape introuvable.']];
        }

        $errors = $this->validateStep($input, isUpdate: true);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [];
        foreach (['delay_days','subject','body_text','is_active','step_order'] as $f) {
            if (array_key_exists($f, $input)) {
                $data[$f] = in_array($f, ['delay_days','is_active','step_order'], true)
                    ? (int) $input[$f]
                    : trim((string) $input[$f]);
            }
        }

        $this->repo->updateStep($stepId, $data);

        return ['ok' => true];
    }

    public function deleteStep(int $campaignId, int $stepId): bool
    {
        if (!$this->campaignRepo->findById($campaignId, $this->userId)) {
            return false;
        }

        $step = $this->repo->findStepById($stepId);
        if (!$step || (int) $step['campaign_id'] !== $campaignId) {
            return false;
        }

        $ok = $this->repo->deleteStep($stepId);
        if ($ok) {
            $this->repo->reorderSteps($campaignId);
        }

        return $ok;
    }

    // ------------------------------------------------------------------
    // APERÇU AVEC VARIABLES RÉSOLUES
    // ------------------------------------------------------------------

    public function preview(string $text, array $contact = []): string
    {
        $vars = [
            '{{first_name}}' => $contact['first_name'] ?? 'Prénom',
            '{{last_name}}'  => $contact['last_name']  ?? 'Nom',
            '{{email}}'      => $contact['email']      ?? 'email@exemple.com',
            '{{company}}'    => $contact['company']    ?? 'Votre entreprise',
            '{{city}}'       => $contact['city']       ?? 'Votre ville',
            '{{phone}}'      => $contact['phone']      ?? '',
        ];

        return str_replace(array_keys($vars), array_values($vars), $text);
    }

    // ------------------------------------------------------------------
    // ENVOI TEST MANUEL (depuis l'interface)
    // ------------------------------------------------------------------

    /**
     * Envoie un email test pour une étape donnée.
     * N'est jamais envoyé au prospect réel — toujours au mode transport actif.
     *
     * @return array{ok:bool, mode:string, sent_to:string, subject:string, body:string, log_id:int, error:string|null}
     */
    public function sendTestEmail(int $campaignId, int $stepId, array $previewContact = []): array
    {
        $campaign = $this->campaignRepo->findById($campaignId, $this->userId);
        if (!$campaign) {
            return ['ok' => false, 'error' => 'Campagne introuvable.'];
        }

        $step = $this->repo->findStepById($stepId);
        if (!$step || (int)$step['campaign_id'] !== $campaignId) {
            return ['ok' => false, 'error' => 'Étape introuvable.'];
        }

        // Contact d'aperçu par défaut si non fourni
        $contact = array_merge([
            'first_name' => 'Jean',
            'last_name'  => 'Dupont',
            'email'      => ProspectionMailer::testRecipient() ?: 'test@exemple.fr',
            'company'    => 'Société Test',
            'city'       => 'Aix-en-Provence',
        ], $previewContact);

        $subject = $this->preview($step['subject'],   $contact);
        $body    = $this->preview($step['body_text'], $contact);

        $transport = ProspectionMailer::transport();
        $result    = $transport->deliver($contact['email'], $subject, $body);

        // Log l'envoi test
        $logId = $this->repo->logSend([
            'campaign_id'       => $campaignId,
            'contact_id'        => 0,
            'step_id'           => (int)$step['id'],
            'to_email'          => $result['is_test'] ? (ProspectionMailer::testRecipient() ?: $contact['email']) : $contact['email'],
            'subject'           => $subject,
            'body_text'         => $body,
            'status'            => $result['sent'] ? 'sent' : 'failed',
            'sent_at'           => $result['sent'] ? date('Y-m-d H:i:s') : null,
            'is_test'           => 1,
            'intended_recipient'=> $contact['email'],
            'error_message'     => $result['error'],
        ]);

        $this->repo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $campaignId,
            'contact_id' => null,
            'event'      => $result['sent'] ? 'test_email_sent' : 'test_email_failed',
            'detail'     => sprintf('Test [%s] étape %d → %s | %s',
                strtoupper($transport->mode()),
                (int)$step['step_order'],
                $contact['email'],
                $subject
            ),
        ]);

        return [
            'ok'      => $result['sent'],
            'mode'    => $transport->mode(),
            'sent_to' => $result['is_test'] ? (ProspectionMailer::testRecipient() ?: $contact['email']) : $contact['email'],
            'subject' => $subject,
            'body'    => $body,
            'log_id'  => $logId,
            'error'   => $result['error'],
        ];
    }

    // ------------------------------------------------------------------
    // MOTEUR D'ENVOI RÉEL / TEST (appel cron ou manuel)
    // ------------------------------------------------------------------

    /**
     * Traite tous les envois dus.
     * Respecte MAIL_MODE : test ou smtp.
     *
     * @return array{sent:int, failed:int, skipped:int, mode:string}
     */
    public function processDueEmails(): array
    {
        $due       = $this->repo->getDueEnrollments();
        $transport = ProspectionMailer::transport();
        $sent      = 0;
        $failed    = 0;
        $skipped   = 0;

        foreach ($due as $enrollment) {
            $campaignId = (int)$enrollment['campaign_id'];
            $contactId  = (int)$enrollment['contact_id'];
            $stepIndex  = (int)$enrollment['current_step'];

            $activeSteps = array_values(
                array_filter(
                    $this->repo->getStepsByCampaign($campaignId),
                    fn($s) => (int)$s['is_active'] === 1
                )
            );

            if (!isset($activeSteps[$stepIndex])) {
                $this->repo->advanceContactStep($campaignId, $contactId, $stepIndex, null);
                $skipped++;
                continue;
            }

            $step    = $activeSteps[$stepIndex];
            $subject = $this->preview($step['subject'],   $enrollment);
            $body    = $this->preview($step['body_text'], $enrollment);

            $result   = $transport->deliver($enrollment['email'], $subject, $body);
            $isTest   = $result['is_test'];
            $sentTo   = $isTest ? ProspectionMailer::testRecipient() : $enrollment['email'];

            // Log envoi
            $this->repo->logSend([
                'campaign_id'       => $campaignId,
                'contact_id'        => $contactId,
                'step_id'           => (int)$step['id'],
                'to_email'          => $sentTo,
                'subject'           => $subject,
                'body_text'         => $body,
                'status'            => $result['sent'] ? 'sent' : 'failed',
                'sent_at'           => $result['sent'] ? date('Y-m-d H:i:s') : null,
                'is_test'           => $isTest ? 1 : 0,
                'intended_recipient'=> $isTest ? $enrollment['email'] : null,
                'error_message'     => $result['error'],
            ]);

            if ($result['sent']) {
                $nextIndex = $stepIndex + 1;
                $nextSendAt = null;
                if (isset($activeSteps[$nextIndex])) {
                    $delay = (int)($activeSteps[$nextIndex]['delay_days'] ?? 1);
                    $nextSendAt = date('Y-m-d H:i:s', strtotime("+{$delay} days"));
                }

                $this->repo->advanceContactStep($campaignId, $contactId, $nextIndex, $nextSendAt);

                $this->repo->logActivity([
                    'user_id'    => $this->getOwnerOfCampaign($campaignId),
                    'campaign_id'=> $campaignId,
                    'contact_id' => $contactId,
                    'event'      => 'email_sent',
                    'detail'     => sprintf('[%s] Étape %d : %s',
                        strtoupper($transport->mode()),
                        $stepIndex + 1,
                        $subject
                    ),
                ]);

                $sent++;
            } else {
                $this->repo->logActivity([
                    'user_id'    => $this->getOwnerOfCampaign($campaignId),
                    'campaign_id'=> $campaignId,
                    'contact_id' => $contactId,
                    'event'      => 'email_failed',
                    'detail'     => $result['error'] ?? 'Erreur inconnue',
                ]);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'mode' => $transport->mode()];
    }

    // ------------------------------------------------------------------
    // SIMULATION COMPLÈTE DU CYCLE
    // ------------------------------------------------------------------

    /**
     * Simule le déroulement complet d'une campagne sur tous ses contacts.
     * N'envoie PAS d'emails réels si mode log/test.
     * Met à jour les statuts dans la base selon les scénarios de chaque contact.
     *
     * @return array{total:int, scenarios:array}
     */
    public function simulateFullCycle(int $campaignId): array
    {
        $campaign = $this->campaignRepo->findById($campaignId, $this->userId);
        if (!$campaign) {
            return ['total' => 0, 'scenarios' => []];
        }

        $contacts = $this->campaignRepo->getEnrolledContacts($campaignId);
        $steps    = array_values(
            array_filter(
                $this->repo->getStepsByCampaign($campaignId),
                fn($s) => (int)$s['is_active'] === 1
            )
        );

        $results = [];

        foreach ($contacts as $cc) {
            $contactId = (int)$cc['id'];
            $scenario  = $this->detectScenario($cc);
            $outcome   = $this->applyScenario($campaignId, $contactId, $cc, $steps, $scenario);
            $results[] = $outcome;
        }

        $this->repo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $campaignId,
            'event'      => 'simulation_run',
            'detail'     => count($results) . ' contacts simulés',
        ]);

        return ['total' => count($results), 'scenarios' => $results];
    }

    /**
     * Détecte le scénario de simulation selon les notes/tags du contact.
     * Convention : tag "sim:xxx" dans les notes ou le champ tags.
     */
    private function detectScenario(array $contact): string
    {
        $notes = strtolower($contact['notes'] ?? '');
        $tags  = strtolower(is_string($contact['tags']) ? $contact['tags'] : json_encode($contact['tags'] ?? []));

        foreach (['no_open','bounce','replied_step1','replied_step3','opened_no_reply','clicked','paused','unsub'] as $s) {
            if (str_contains($notes, "sim:{$s}") || str_contains($tags, "sim:{$s}")) {
                return $s;
            }
        }

        return 'no_open'; // défaut
    }

    /**
     * Applique un scénario sur un contact inscrit dans la campagne.
     */
    private function applyScenario(
        int   $campaignId,
        int   $contactId,
        array $contact,
        array $steps,
        string $scenario
    ): array {

        $baseDate = strtotime($contact['enrolled_at'] ?? 'now');
        $stepCount = count($steps);

        switch ($scenario) {

            case 'no_open':
                // Tous les emails envoyés, aucun ouvert, séquence terminée
                $this->simulateSentAll($campaignId, $contactId, $steps, $baseDate);
                $this->updateEnrollFull($campaignId, $contactId, 'completed', []);
                $this->logSimEvent($campaignId, $contactId, 'simulation_no_open', 'Aucune ouverture, séquence terminée');
                break;

            case 'bounce':
                // Email bounced au premier envoi
                if (!empty($steps)) {
                    $s       = $steps[0];
                    $subject = $this->preview($s['subject'],   $contact);
                    $body    = $this->preview($s['body_text'], $contact);
                    $this->repo->logSend([
                        'campaign_id' => $campaignId, 'contact_id' => $contactId,
                        'step_id' => (int)$s['id'], 'to_email' => $contact['email'],
                        'subject' => $subject, 'body_text' => $body,
                        'status' => 'bounced', 'sent_at' => date('Y-m-d H:i:s', $baseDate),
                        'is_test' => 1, 'intended_recipient' => $contact['email'],
                    ]);
                }
                $this->updateEnrollFull($campaignId, $contactId, 'bounced', ['bounced_at' => date('Y-m-d H:i:s', $baseDate)]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_bounce', 'Email bounced');
                break;

            case 'replied_step1':
                // Répond après l'email 1
                if (!empty($steps)) {
                    $this->simulateSent($campaignId, $contactId, $steps[0], $baseDate, 'sent');
                }
                $replyDate = date('Y-m-d H:i:s', $baseDate + 3600);
                $this->updateEnrollFull($campaignId, $contactId, 'replied', [
                    'replied_at' => $replyDate,
                    'next_send_at' => null,
                    'current_step' => 1,
                ]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_replied_step1', 'A répondu dès le premier email');
                break;

            case 'replied_step3':
                // Reçoit les 3 premiers emails, répond après le 3e
                foreach (array_slice($steps, 0, 3) as $i => $s) {
                    $offset = $s['delay_days'] * 86400;
                    $this->simulateSent($campaignId, $contactId, $s, $baseDate + $offset, 'sent');
                }
                $replyDate = date('Y-m-d H:i:s', $baseDate + (($steps[2]['delay_days'] ?? 5) * 86400) + 7200);
                $this->updateEnrollFull($campaignId, $contactId, 'replied', [
                    'replied_at'  => $replyDate,
                    'next_send_at'=> null,
                    'current_step'=> 3,
                ]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_replied_step3', 'A répondu après le 3e email');
                break;

            case 'opened_no_reply':
                // Reçoit tout, "ouvre" les emails (simulation), ne répond pas
                $this->simulateSentAll($campaignId, $contactId, $steps, $baseDate, 'opened');
                $openDate = date('Y-m-d H:i:s', $baseDate + 1800);
                $this->updateEnrollFull($campaignId, $contactId, 'completed', ['opened_at' => $openDate]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_opened_no_reply', 'Ouvertures sans réponse');
                break;

            case 'clicked':
                // Ouvre et clique (simulation)
                $this->simulateSentAll($campaignId, $contactId, $steps, $baseDate, 'clicked');
                $clickDate = date('Y-m-d H:i:s', $baseDate + 3600);
                $this->updateEnrollFull($campaignId, $contactId, 'completed', [
                    'opened_at'  => $clickDate,
                    'clicked_at' => date('Y-m-d H:i:s', $baseDate + 3700),
                ]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_clicked', 'A cliqué sur un lien');
                break;

            case 'paused':
                // Contact mis en pause manuellement
                $this->updateEnrollFull($campaignId, $contactId, 'paused', []);
                $this->logSimEvent($campaignId, $contactId, 'simulation_paused', 'Séquence mise en pause');
                break;

            case 'unsub':
                // Contact désabonné
                $unsubDate = date('Y-m-d H:i:s', $baseDate + 7200);
                $this->updateEnrollFull($campaignId, $contactId, 'unsubscribed', ['unsub_at' => $unsubDate]);
                $this->logSimEvent($campaignId, $contactId, 'simulation_unsub', 'Désabonnement simulé');
                break;

            default:
                $this->logSimEvent($campaignId, $contactId, 'simulation_skip', 'Scénario inconnu : ' . $scenario);
        }

        return [
            'contact_id' => $contactId,
            'email'      => $contact['email'],
            'scenario'   => $scenario,
            'steps'      => $stepCount,
        ];
    }

    // ------------------------------------------------------------------
    // HELPERS SIMULATION
    // ------------------------------------------------------------------

    private function simulateSentAll(
        int $campaignId, int $contactId, array $steps, int $baseDate, string $status = 'sent'
    ): void {
        foreach ($steps as $s) {
            $offset = ($s['delay_days'] ?? 0) * 86400;
            $this->simulateSent($campaignId, $contactId, $s, $baseDate + $offset, $status);
        }
    }

    private function simulateSent(
        int $campaignId, int $contactId, array $step, int $ts, string $status
    ): void {
        // Récupère les infos du contact pour résoudre les variables
        $stmt = db()->prepare('SELECT * FROM prospect_contacts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $contactId]);
        $contact = $stmt->fetch() ?: [];

        $subject = $this->preview($step['subject'],   $contact);
        $body    = $this->preview($step['body_text'], $contact);

        $this->repo->logSend([
            'campaign_id'       => $campaignId,
            'contact_id'        => $contactId,
            'step_id'           => (int)$step['id'],
            'to_email'          => $contact['email'] ?? '',
            'subject'           => $subject,
            'body_text'         => $body,
            'status'            => $status,
            'sent_at'           => date('Y-m-d H:i:s', $ts),
            'is_test'           => 1,
            'intended_recipient'=> $contact['email'] ?? null,
        ]);

        // Avance l'étape du contact
        $stmt2 = db()->prepare(
            'UPDATE campaign_contacts SET current_step = current_step + 1, last_sent_at = :ts
             WHERE campaign_id = :cid AND contact_id = :ctid'
        );
        $stmt2->execute([':ts' => date('Y-m-d H:i:s', $ts), ':cid' => $campaignId, ':ctid' => $contactId]);
    }

    private function updateEnrollFull(int $campaignId, int $contactId, string $status, array $extra): void
    {
        $fields = ['status = :status'];
        $params = [':status' => $status, ':cid' => $campaignId, ':ctid' => $contactId];

        foreach (['replied_at','opened_at','clicked_at','bounced_at','unsub_at','next_send_at','current_step'] as $col) {
            if (array_key_exists($col, $extra)) {
                $fields[]           = "{$col} = :{$col}";
                $params[":{$col}"]  = $extra[$col];
            }
        }

        $sql  = 'UPDATE campaign_contacts SET ' . implode(', ', $fields)
              . ' WHERE campaign_id = :cid AND contact_id = :ctid';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
    }

    private function updateContactStatus(int $campaignId, int $contactId, string $status): void
    {
        $this->updateEnrollFull($campaignId, $contactId, $status, []);
    }

    private function logSimEvent(int $campaignId, int $contactId, string $event, string $detail = ''): void
    {
        $this->repo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $campaignId,
            'contact_id' => $contactId,
            'event'      => $event,
            'detail'     => $detail,
        ]);
    }

    // ------------------------------------------------------------------
    // JOURNAL / ACTIVITÉ
    // ------------------------------------------------------------------

    public function getRecentActivity(int $limit = 50): array
    {
        return $this->repo->getRecentActivity($this->userId, $limit);
    }

    public function getSendLogs(int $campaignId, int $limit = 100): array
    {
        return $this->repo->getSendLogsByCampaign($campaignId, $limit);
    }

    // ------------------------------------------------------------------
    // SÉQUENCE EXEMPLE
    // ------------------------------------------------------------------

    public function seedDemoSequence(int $campaignId): void
    {
        $steps = [
            ['delay_days' => 0,  'subject' => 'Question rapide',
             'body_text'  => "Salut {{first_name}},\n\nJe me permets de te contacter rapidement car je travaille en ce moment avec plusieurs professionnels.\n\nJe fais un petit sondage terrain :\nAujourd'hui, tu développes ton activité plutôt via :\n1) recommandation\n2) contenu / réseaux\n3) prospection directe\n4) autre ?\n\nCurieux d'avoir ton retour."],
            ['delay_days' => 2,  'subject' => 'Tu fais partie des rares ?',
             'body_text'  => "Salut {{first_name}},\n\nJe reviens vers toi car plusieurs retours me montrent une chose :\nbeaucoup travaillent beaucoup, mais peu ont un vrai système régulier d'opportunités.\n\nTu arrives aujourd'hui à avoir une visibilité stable sur tes prochaines opportunités ou c'est encore irrégulier ?"],
            ['delay_days' => 5,  'subject' => "Ce que j'observe",
             'body_text'  => "Salut {{first_name}},\n\nCe que j'observe souvent :\nbeaucoup dépendent encore trop du bouche-à-oreille ou des plateformes.\n\nCeux qui passent un cap ont généralement un système simple mais structuré.\n\nTu serais ouvert à découvrir une autre approche ?"],
            ['delay_days' => 9,  'subject' => 'Je peux te montrer',
             'body_text'  => "Salut {{first_name}},\n\nJe mets actuellement en place une mécanique simple pour aider à structurer l'acquisition et le suivi.\n\nSi tu veux, je peux te montrer la logique."],
            ['delay_days' => 14, 'subject' => 'Je clôture',
             'body_text'  => "Salut {{first_name}},\n\nJe ne vais pas t'embêter plus longtemps.\n\nSi le sujet t'intéresse, je peux te montrer comment je structure cela.\nSinon aucun souci."],
        ];

        foreach ($steps as $i => $step) {
            $this->repo->createStep([
                'campaign_id' => $campaignId,
                'step_order'  => $i + 1,
                'delay_days'  => $step['delay_days'],
                'subject'     => $step['subject'],
                'body_text'   => $step['body_text'],
                'is_active'   => 1,
            ]);
        }
    }

    // ------------------------------------------------------------------
    // HELPERS INTERNES
    // ------------------------------------------------------------------

    private function getOwnerOfCampaign(int $campaignId): int
    {
        $stmt = db()->prepare('SELECT user_id FROM email_campaigns WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $campaignId]);
        return (int)$stmt->fetchColumn();
    }

    private function validateStep(array $input, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate || array_key_exists('subject', $input)) {
            if (empty(trim($input['subject'] ?? ''))) {
                $errors['subject'] = "L'objet de l'email est obligatoire.";
            }
        }

        if (!$isUpdate || array_key_exists('body_text', $input)) {
            if (empty(trim($input['body_text'] ?? ''))) {
                $errors['body_text'] = "Le corps de l'email est obligatoire.";
            }
        }

        if (array_key_exists('delay_days', $input)) {
            if (!is_numeric($input['delay_days']) || (int)$input['delay_days'] < 0) {
                $errors['delay_days'] = 'Le délai doit être un nombre positif.';
            }
        }

        return $errors;
    }
}
