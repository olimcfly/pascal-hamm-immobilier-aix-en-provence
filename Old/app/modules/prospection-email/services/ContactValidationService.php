<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ProspectContactRepository.php';

class ContactValidationService
{
    private const VALIDATION_STATUSES = [
        'missing',
        'invalid_format',
        'duplicate',
        'pending_review',
        'valid',
        'blacklisted',
    ];

    public function __construct(private ProspectContactRepository $contacts, private PDO $db)
    {
    }

    public function validateContact(int $contactId, string $newStatus, string $actionType, ?string $notes = null): bool
    {
        if (!in_array($newStatus, self::VALIDATION_STATUSES, true)) {
            return false;
        }

        $current = $this->fetchContact($contactId);
        if (!$current) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $isBlacklisted = $newStatus === 'blacklisted';
            $this->contacts->updateStatus($contactId, $newStatus, $isBlacklisted);

            $stmt = $this->db->prepare(
                'INSERT INTO prospect_contact_validations
                (contact_id, old_status, new_status, action_type, action_notes)
                VALUES (:contact_id, :old_status, :new_status, :action_type, :action_notes)'
            );
            $stmt->execute([
                ':contact_id' => $contactId,
                ':old_status' => $current['validation_status'],
                ':new_status' => $newStatus,
                ':action_type' => $actionType,
                ':action_notes' => $notes,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            return false;
        }
    }

    private function fetchContact(int $contactId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM prospect_contacts WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $contactId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
