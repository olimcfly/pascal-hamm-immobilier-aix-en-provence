<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ProspectContactRepository.php';

class ContactIngestionService
{
    public function __construct(private ProspectContactRepository $contacts)
    {
    }

    public function addManual(array $payload): array
    {
        $normalized = $this->normalizeContact($payload, 'manual', $payload['source_label'] ?? 'crm-manual');
        return $this->storeContact($normalized);
    }

    public function ingestCsvRow(array $row, array $mapping, string $importLabel): array
    {
        $contact = [];
        foreach ($mapping as $field => $columnName) {
            $contact[$field] = $row[$columnName] ?? null;
        }

        $normalized = $this->normalizeContact($contact, 'csv', $importLabel);
        return $this->storeContact($normalized);
    }

    public function pushScrapeBuffer(array $scrapedRows, string $scrapeLabel): array
    {
        $buffer = [];
        foreach ($scrapedRows as $row) {
            $buffer[] = $this->normalizeContact($row, 'scraping', $scrapeLabel);
        }

        return $buffer;
    }

    private function storeContact(array $contact): array
    {
        $existing = $this->contacts->findByEmail($contact['email']);
        if ($existing) {
            return ['status' => 'duplicate', 'contact' => $existing];
        }

        $id = $this->contacts->create($contact);
        return ['status' => 'created', 'contact_id' => $id];
    }

    private function normalizeContact(array $payload, string $sourceType, string $sourceLabel): array
    {
        return [
            'first_name' => trim((string) ($payload['first_name'] ?? '')),
            'last_name' => trim((string) ($payload['last_name'] ?? '')),
            'email' => mb_strtolower(trim((string) ($payload['email'] ?? ''))),
            'phone' => trim((string) ($payload['phone'] ?? '')) ?: null,
            'company_network' => trim((string) ($payload['company_network'] ?? '')),
            'city' => trim((string) ($payload['city'] ?? '')),
            'source_type' => $sourceType,
            'source_label' => $sourceLabel,
            'validation_status' => 'pending_review',
            'blacklist_status' => 0,
            'notes' => trim((string) ($payload['notes'] ?? '')) ?: null,
        ];
    }
}
