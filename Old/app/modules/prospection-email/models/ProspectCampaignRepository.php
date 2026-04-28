<?php

declare(strict_types=1);

class ProspectCampaignRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_campaigns (name, objective, status, segment_filter_json, mailbox_id, daily_limit, launch_at, created_by)
             VALUES (:name, :objective, :status, :segment_filter_json, :mailbox_id, :daily_limit, :launch_at, :created_by)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':objective' => $data['objective'],
            ':status' => $data['status'] ?? 'draft',
            ':segment_filter_json' => json_encode($data['segment_filter'] ?? [], JSON_THROW_ON_ERROR),
            ':mailbox_id' => $data['mailbox_id'],
            ':daily_limit' => $data['daily_limit'],
            ':launch_at' => $data['launch_at'],
            ':created_by' => $data['created_by'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function assignContacts(int $campaignId, array $contacts): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO prospect_campaign_contacts (campaign_id, contact_id, enrollment_status)
             VALUES (:campaign_id, :contact_id, :enrollment_status)'
        );

        foreach ($contacts as $contact) {
            $stmt->execute([
                ':campaign_id' => $campaignId,
                ':contact_id' => $contact['id'],
                ':enrollment_status' => 'queued',
            ]);
        }
    }
}
