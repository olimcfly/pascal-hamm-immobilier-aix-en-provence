<?php

declare(strict_types=1);

class ProspectSequenceRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function create(int $campaignId, string $name, bool $autoStopOnReply = true): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_sequences (campaign_id, name, auto_stop_on_reply)
             VALUES (:campaign_id, :name, :auto_stop_on_reply)'
        );

        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':name' => $name,
            ':auto_stop_on_reply' => (int) $autoStopOnReply,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function createStep(int $sequenceId, array $step): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_sequence_steps (sequence_id, step_order, subject_template, body_template, delay_days)
             VALUES (:sequence_id, :step_order, :subject_template, :body_template, :delay_days)'
        );

        $stmt->execute([
            ':sequence_id' => $sequenceId,
            ':step_order' => $step['step_order'],
            ':subject_template' => $step['subject_template'],
            ':body_template' => $step['body_template'],
            ':delay_days' => $step['delay_days'],
        ]);
    }
}
