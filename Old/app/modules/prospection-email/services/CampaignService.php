<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ProspectCampaignRepository.php';
require_once __DIR__ . '/../models/ProspectContactRepository.php';

class CampaignService
{
    public function __construct(
        private ProspectCampaignRepository $campaigns,
        private ProspectContactRepository $contacts
    ) {
    }

    public function createCampaign(array $input): array
    {
        $errors = $this->validate($input);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $campaignId = $this->campaigns->create($input);
        $targetContacts = $this->contacts->findValidBySegment($input['segment_filter'] ?? []);
        $this->campaigns->assignContacts($campaignId, $targetContacts);

        return [
            'success' => true,
            'campaign_id' => $campaignId,
            'contacts_enrolled' => count($targetContacts),
        ];
    }

    private function validate(array $input): array
    {
        $errors = [];

        foreach (['name', 'objective', 'mailbox_id', 'daily_limit'] as $required) {
            if (empty($input[$required])) {
                $errors[] = sprintf('Le champ %s est requis.', $required);
            }
        }

        if (!empty($input['daily_limit']) && (int) $input['daily_limit'] <= 0) {
            $errors[] = 'Le volume journalier doit être supérieur à 0.';
        }

        return $errors;
    }
}
