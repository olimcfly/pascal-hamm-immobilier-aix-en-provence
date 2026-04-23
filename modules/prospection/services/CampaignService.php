<?php

declare(strict_types=1);

require_once MODULES_PATH . '/prospection/repositories/CampaignRepository.php';
require_once MODULES_PATH . '/prospection/repositories/SequenceRepository.php';

class CampaignService
{
    public function __construct(
        private readonly CampaignRepository $campaignRepo,
        private readonly SequenceRepository $seqRepo,
        private readonly int $userId
    ) {}

    public function getAll(array $filters = []): array
    {
        return $this->campaignRepo->findAll($this->userId, $filters);
    }

    public function getById(int $id): ?array
    {
        return $this->campaignRepo->findById($id, $this->userId);
    }

    public function create(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $id = $this->campaignRepo->create([
            'user_id'     => $this->userId,
            'name'        => htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars(trim($input['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'objective'   => htmlspecialchars(trim($input['objective']   ?? ''), ENT_QUOTES, 'UTF-8') ?: null,
            'status'      => 'draft',
        ]);

        $this->seqRepo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $id,
            'event'      => 'campaign_created',
            'detail'     => 'Campagne créée : ' . $input['name'],
        ]);

        return ['ok' => true, 'id' => $id];
    }

    public function update(int $id, array $input): array
    {
        $campaign = $this->campaignRepo->findById($id, $this->userId);
        if (!$campaign) {
            return ['ok' => false, 'errors' => ['global' => 'Campagne introuvable.']];
        }

        $errors = $this->validate($input, isUpdate: true);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $data = [];
        foreach (['name','description','objective','status'] as $f) {
            if (array_key_exists($f, $input)) {
                $data[$f] = htmlspecialchars(trim((string) $input[$f]), ENT_QUOTES, 'UTF-8') ?: null;
            }
        }

        $this->campaignRepo->update($id, $this->userId, $data);

        return ['ok' => true];
    }

    public function delete(int $id): bool
    {
        $ok = $this->campaignRepo->softDelete($id, $this->userId);
        if ($ok) {
            $this->seqRepo->logActivity([
                'user_id'    => $this->userId,
                'campaign_id'=> $id,
                'event'      => 'campaign_deleted',
            ]);
        }
        return $ok;
    }

    public function getDetailData(int $id): ?array
    {
        $campaign = $this->campaignRepo->findById($id, $this->userId);
        if (!$campaign) {
            return null;
        }

        $campaign['contacts']  = $this->campaignRepo->getEnrolledContacts($id);
        $campaign['steps']     = $this->seqRepo->getStepsByCampaign($id);
        $campaign['send_stats']= $this->seqRepo->getSendStats($id);

        return $campaign;
    }

    // ------------------------------------------------------------------
    // GESTION DES CONTACTS
    // ------------------------------------------------------------------

    public function enrollContacts(int $campaignId, array $contactIds): array
    {
        $campaign = $this->campaignRepo->findById($campaignId, $this->userId);
        if (!$campaign) {
            return ['ok' => false, 'message' => 'Campagne introuvable.'];
        }

        $steps   = $this->seqRepo->getStepsByCampaign($campaignId);
        $firstStep = !empty($steps) ? $steps[0] : null;

        $enrolled = 0;
        foreach ($contactIds as $contactId) {
            $contactId = (int) $contactId;
            if ($contactId <= 0) {
                continue;
            }

            // Calcule la date du premier envoi
            $nextSendAt = null;
            if ($firstStep) {
                $delay      = (int) ($firstStep['delay_days'] ?? 0);
                $nextSendAt = date('Y-m-d H:i:s', strtotime("+{$delay} days"));
            }

            $this->campaignRepo->enrollContact($campaignId, $contactId, $nextSendAt);

            $this->seqRepo->logActivity([
                'user_id'    => $this->userId,
                'campaign_id'=> $campaignId,
                'contact_id' => $contactId,
                'event'      => 'contact_enrolled',
            ]);

            $enrolled++;
        }

        return ['ok' => true, 'enrolled' => $enrolled];
    }

    public function unenrollContact(int $campaignId, int $contactId): bool
    {
        $ok = $this->campaignRepo->unenrollContact($campaignId, $contactId);
        if ($ok) {
            $this->seqRepo->logActivity([
                'user_id'    => $this->userId,
                'campaign_id'=> $campaignId,
                'contact_id' => $contactId,
                'event'      => 'contact_removed',
            ]);
        }
        return $ok;
    }

    public function markReplied(int $campaignId, int $contactId): bool
    {
        $stmt = db()->prepare(
            'UPDATE campaign_contacts SET status = "replied", replied_at = NOW(), next_send_at = NULL
             WHERE campaign_id = :campaign_id AND contact_id = :contact_id'
        );
        $stmt->execute([':campaign_id' => $campaignId, ':contact_id' => $contactId]);

        $this->seqRepo->logActivity([
            'user_id'    => $this->userId,
            'campaign_id'=> $campaignId,
            'contact_id' => $contactId,
            'event'      => 'contact_replied',
            'detail'     => 'Séquence arrêtée suite à réponse.',
        ]);

        return $stmt->rowCount() > 0;
    }

    // ------------------------------------------------------------------
    // GLOBAL STATS (pour le dashboard)
    // ------------------------------------------------------------------

    public function getDashboardStats(): array
    {
        return $this->campaignRepo->getStats($this->userId);
    }

    // ------------------------------------------------------------------
    // VALIDATION
    // ------------------------------------------------------------------

    private function validate(array $input, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate || array_key_exists('name', $input)) {
            $name = trim($input['name'] ?? '');
            if ($name === '') {
                $errors['name'] = 'Le nom de la campagne est obligatoire.';
            } elseif (mb_strlen($name) > 200) {
                $errors['name'] = 'Nom trop long (200 caractères max).';
            }
        }

        if (array_key_exists('status', $input)) {
            $allowed = ['draft','active','paused','completed'];
            if (!in_array($input['status'], $allowed, true)) {
                $errors['status'] = 'Statut invalide.';
            }
        }

        return $errors;
    }
}
