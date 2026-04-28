<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ProspectSequenceRepository.php';

class SequenceEngineService
{
    public function __construct(private ProspectSequenceRepository $sequences)
    {
    }

    public function createSequence(int $campaignId, string $name, array $steps): array
    {
        if ($steps === []) {
            return ['success' => false, 'error' => 'Une séquence doit contenir au moins une étape.'];
        }

        $sequenceId = $this->sequences->create($campaignId, $name, true);

        foreach ($steps as $index => $step) {
            $this->sequences->createStep($sequenceId, [
                'step_order' => $index + 1,
                'subject_template' => $step['subject_template'],
                'body_template' => $step['body_template'],
                'delay_days' => (int) ($step['delay_days'] ?? 0),
            ]);
        }

        return ['success' => true, 'sequence_id' => $sequenceId, 'steps_count' => count($steps)];
    }

    public function renderVariables(string $template, array $contact): string
    {
        $replacements = [
            '{{first_name}}' => $contact['first_name'] ?? '',
            '{{last_name}}' => $contact['last_name'] ?? '',
            '{{company_network}}' => $contact['company_network'] ?? '',
            '{{city}}' => $contact['city'] ?? '',
        ];

        return strtr($template, $replacements);
    }
}
