<?php

declare(strict_types=1);

class CampaignRepository
{
    public function __construct(private readonly PDO $db) {}

    // ------------------------------------------------------------------
    // READ
    // ------------------------------------------------------------------

    public function findAll(int $userId, array $filters = []): array
    {
        $where  = ['c.deleted_at IS NULL', 'c.user_id = :user_id'];
        $params = [':user_id' => $userId];

        if (!empty($filters['status'])) {
            $where[]          = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[]           = 'c.name LIKE :search';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT c.*,
                    COUNT(DISTINCT cc.contact_id) AS contact_count,
                    SUM(CASE WHEN cc.status = "replied" THEN 1 ELSE 0 END) AS reply_count,
                    COUNT(DISTINCT ss.id) AS step_count
                FROM email_campaigns c
                LEFT JOIN campaign_contacts cc ON cc.campaign_id = c.id
                LEFT JOIN email_sequence_steps ss ON ss.campaign_id = c.id AND ss.is_active = 1
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY c.id
                ORDER BY c.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                COUNT(DISTINCT cc.contact_id) AS contact_count,
                SUM(CASE WHEN cc.status = "replied" THEN 1 ELSE 0 END) AS reply_count,
                COUNT(DISTINCT ss.id) AS step_count
             FROM email_campaigns c
             LEFT JOIN campaign_contacts cc ON cc.campaign_id = c.id
             LEFT JOIN email_sequence_steps ss ON ss.campaign_id = c.id AND ss.is_active = 1
             WHERE c.id = :id AND c.user_id = :user_id AND c.deleted_at IS NULL
             GROUP BY c.id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    // ------------------------------------------------------------------
    // WRITE
    // ------------------------------------------------------------------

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO email_campaigns (user_id, name, description, objective, status)
             VALUES (:user_id, :name, :description, :objective, :status)'
        );
        $stmt->execute([
            ':user_id'     => $data['user_id'],
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':objective'   => $data['objective']   ?? null,
            ':status'      => $data['status']       ?? 'draft',
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id, ':user_id' => $userId];

        foreach (['name','description','objective','status'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]            = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if ($fields === []) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE email_campaigns SET ' . implode(', ', $fields)
            . ' WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );

        return $stmt->execute($params);
    }

    public function softDelete(int $id, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE email_campaigns SET deleted_at = NOW()
             WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id, ':user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    // ------------------------------------------------------------------
    // CONTACTS DANS UNE CAMPAGNE
    // ------------------------------------------------------------------

    public function getEnrolledContacts(int $campaignId, array $filters = []): array
    {
        $where  = ['cc.campaign_id = :campaign_id'];
        $params = [':campaign_id' => $campaignId];

        if (!empty($filters['status'])) {
            $where[]          = 'cc.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql = 'SELECT p.*, cc.status AS enroll_status, cc.current_step, cc.next_send_at, cc.last_sent_at, cc.replied_at, cc.enrolled_at
                FROM campaign_contacts cc
                JOIN prospect_contacts p ON p.id = cc.contact_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY cc.enrolled_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function enrollContact(int $campaignId, int $contactId, ?string $nextSendAt = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO campaign_contacts (campaign_id, contact_id, status, current_step, next_send_at)
             VALUES (:campaign_id, :contact_id, "enrolled", 0, :next_send_at)
             ON DUPLICATE KEY UPDATE status = "enrolled", current_step = 0, next_send_at = :next_send_at2, replied_at = NULL'
        );
        $stmt->execute([
            ':campaign_id'   => $campaignId,
            ':contact_id'    => $contactId,
            ':next_send_at'  => $nextSendAt,
            ':next_send_at2' => $nextSendAt,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function unenrollContact(int $campaignId, int $contactId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM campaign_contacts WHERE campaign_id = :campaign_id AND contact_id = :contact_id'
        );
        $stmt->execute([':campaign_id' => $campaignId, ':contact_id' => $contactId]);

        return $stmt->rowCount() > 0;
    }

    public function updateContactEnrollStatus(int $campaignId, int $contactId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE campaign_contacts SET status = :status WHERE campaign_id = :campaign_id AND contact_id = :contact_id'
        );
        return $stmt->execute([':status' => $status, ':campaign_id' => $campaignId, ':contact_id' => $contactId]);
    }

    // ------------------------------------------------------------------
    // STATS
    // ------------------------------------------------------------------

    public function getStats(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "active") AS active,
                SUM(status = "draft") AS draft,
                SUM(status = "paused") AS paused,
                SUM(status = "completed") AS completed
             FROM email_campaigns
             WHERE user_id = :user_id AND deleted_at IS NULL'
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
