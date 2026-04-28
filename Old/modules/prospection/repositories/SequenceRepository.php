<?php

declare(strict_types=1);

class SequenceRepository
{
    public function __construct(private readonly PDO $db) {}

    public function getStepsByCampaign(int $campaignId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM email_sequence_steps WHERE campaign_id = :campaign_id ORDER BY step_order ASC'
        );
        $stmt->execute([':campaign_id' => $campaignId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findStepById(int $stepId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM email_sequence_steps WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $stepId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getStepByOrder(int $campaignId, int $order): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM email_sequence_steps WHERE campaign_id = :campaign_id AND step_order = :order AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([':campaign_id' => $campaignId, ':order' => $order]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function createStep(array $data): int
    {
        // Auto-incrément de l'ordre si non précisé
        if (!isset($data['step_order'])) {
            $stmt = $this->db->prepare(
                'SELECT COALESCE(MAX(step_order), 0) + 1 FROM email_sequence_steps WHERE campaign_id = :campaign_id'
            );
            $stmt->execute([':campaign_id' => $data['campaign_id']]);
            $data['step_order'] = (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare(
            'INSERT INTO email_sequence_steps (campaign_id, step_order, delay_days, subject, body_text, is_active)
             VALUES (:campaign_id, :step_order, :delay_days, :subject, :body_text, :is_active)'
        );
        $stmt->execute([
            ':campaign_id' => $data['campaign_id'],
            ':step_order'  => $data['step_order'],
            ':delay_days'  => $data['delay_days']  ?? 0,
            ':subject'     => $data['subject'],
            ':body_text'   => $data['body_text'],
            ':is_active'   => $data['is_active']   ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateStep(int $stepId, array $data): bool
    {
        $fields = [];
        $params = [':id' => $stepId];

        foreach (['step_order','delay_days','subject','body_text','is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[]            = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if ($fields === []) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE email_sequence_steps SET ' . implode(', ', $fields) . ' WHERE id = :id'
        );

        return $stmt->execute($params);
    }

    public function deleteStep(int $stepId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM email_sequence_steps WHERE id = :id');
        $stmt->execute([':id' => $stepId]);

        return $stmt->rowCount() > 0;
    }

    public function reorderSteps(int $campaignId): void
    {
        // Remet des order consécutifs après suppression
        $stmt = $this->db->prepare(
            'SELECT id FROM email_sequence_steps WHERE campaign_id = :campaign_id ORDER BY step_order ASC, id ASC'
        );
        $stmt->execute([':campaign_id' => $campaignId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $upd = $this->db->prepare('UPDATE email_sequence_steps SET step_order = :order WHERE id = :id');
        foreach ($ids as $i => $id) {
            $upd->execute([':order' => $i + 1, ':id' => $id]);
        }
    }

    // ------------------------------------------------------------------
    // MOTEUR D'ENVOI — contacts éligibles au prochain envoi
    // ------------------------------------------------------------------

    public function getDueEnrollments(): array
    {
        $stmt = $this->db->prepare(
            'SELECT cc.*, p.email, p.first_name, p.last_name, p.company, p.city
             FROM campaign_contacts cc
             JOIN prospect_contacts p ON p.id = cc.contact_id
             WHERE cc.status = "active"
               AND cc.next_send_at IS NOT NULL
               AND cc.next_send_at <= NOW()
               AND p.status = "active"
               AND p.deleted_at IS NULL'
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function advanceContactStep(int $campaignId, int $contactId, int $nextStep, ?string $nextSendAt): void
    {
        $stmt = $this->db->prepare(
            'UPDATE campaign_contacts
             SET current_step = :step, next_send_at = :next, last_sent_at = NOW(), status = :status
             WHERE campaign_id = :campaign_id AND contact_id = :contact_id'
        );
        $status = $nextSendAt ? 'active' : 'completed';
        $stmt->execute([
            ':step'        => $nextStep,
            ':next'        => $nextSendAt,
            ':status'      => $status,
            ':campaign_id' => $campaignId,
            ':contact_id'  => $contactId,
        ]);
    }

    // ------------------------------------------------------------------
    // LOGS
    // ------------------------------------------------------------------

    public function getSendLogsByCampaign(int $campaignId, int $limit = 100): array
    {
        $stmt = $this->db->prepare(
            'SELECT sl.*, CONCAT(p.first_name, " ", p.last_name) AS contact_name, p.email AS contact_email,
                    ss.step_order, ss.subject AS step_subject
             FROM email_send_log sl
             LEFT JOIN prospect_contacts p  ON p.id  = sl.contact_id
             LEFT JOIN email_sequence_steps ss ON ss.id = sl.step_id
             WHERE sl.campaign_id = :campaign_id
             ORDER BY sl.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logSend(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO email_send_log
                (campaign_id, contact_id, step_id, to_email, subject, body_text, status,
                 sent_at, is_test, intended_recipient, error_message)
             VALUES
                (:campaign_id, :contact_id, :step_id, :to_email, :subject, :body_text, :status,
                 :sent_at, :is_test, :intended_recipient, :error_message)'
        );
        $stmt->execute([
            ':campaign_id'       => $data['campaign_id'],
            ':contact_id'        => $data['contact_id'],
            ':step_id'           => $data['step_id'],
            ':to_email'          => $data['to_email'],
            ':subject'           => $data['subject'],
            ':body_text'         => $data['body_text']          ?? null,
            ':status'            => $data['status']              ?? 'sent',
            ':sent_at'           => $data['sent_at']             ?? null,
            ':is_test'           => $data['is_test']             ?? 0,
            ':intended_recipient'=> $data['intended_recipient']  ?? null,
            ':error_message'     => $data['error_message']       ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function logActivity(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO email_activity_log (user_id, campaign_id, contact_id, event, detail)
             VALUES (:user_id, :campaign_id, :contact_id, :event, :detail)'
        );
        $stmt->execute([
            ':user_id'    => $data['user_id'],
            ':campaign_id'=> $data['campaign_id'] ?? null,
            ':contact_id' => $data['contact_id']  ?? null,
            ':event'      => $data['event'],
            ':detail'     => $data['detail']       ?? null,
        ]);
    }

    public function getRecentActivity(int $userId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT al.*, c.name AS campaign_name,
                    CONCAT(p.first_name, " ", p.last_name) AS contact_name, p.email AS contact_email
             FROM email_activity_log al
             LEFT JOIN email_campaigns c ON c.id = al.campaign_id
             LEFT JOIN prospect_contacts p ON p.id = al.contact_id
             WHERE al.user_id = :user_id
             ORDER BY al.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,   PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSendStats(int $campaignId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(status = "sent")   AS sent,
                SUM(status = "failed") AS failed,
                SUM(status = "opened") AS opened,
                SUM(status = "clicked")AS clicked
             FROM email_send_log WHERE campaign_id = :campaign_id'
        );
        $stmt->execute([':campaign_id' => $campaignId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
