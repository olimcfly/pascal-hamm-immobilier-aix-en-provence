<?php

declare(strict_types=1);

class ConversationService
{
    public function __construct(private PDO $db)
    {
    }

    public function registerInboundReply(array $payload): void
    {
        $this->db->beginTransaction();

        try {
            $contactId = (int) $payload['contact_id'];
            $campaignId = (int) $payload['campaign_id'];
            $threadKey = (string) $payload['thread_key'];

            $this->stopSequence($campaignId, $contactId);
            $conversationId = $this->upsertConversation($campaignId, $contactId, $threadKey);
            $this->storeInboundMessage($campaignId, $contactId, $payload);
            $this->markContactAsReplied($contactId);
            $this->logInfo('conversation', sprintf('Reply tracked on conversation #%d', $conversationId), $payload);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            $this->logInfo('conversation', 'Inbound processing failed', ['error' => $exception->getMessage()]);
        }
    }

    private function stopSequence(int $campaignId, int $contactId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE prospect_campaign_contacts
             SET enrollment_status = "replied", stopped_at = NOW()
             WHERE campaign_id = :campaign_id AND contact_id = :contact_id'
        );
        $stmt->execute([':campaign_id' => $campaignId, ':contact_id' => $contactId]);
    }

    private function upsertConversation(int $campaignId, int $contactId, string $threadKey): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_conversations (contact_id, campaign_id, thread_key, status, last_message_at)
             VALUES (:contact_id, :campaign_id, :thread_key, "open", NOW())
             ON DUPLICATE KEY UPDATE status = "open", last_message_at = NOW(), id = LAST_INSERT_ID(id)'
        );

        $stmt->execute([
            ':contact_id' => $contactId,
            ':campaign_id' => $campaignId,
            ':thread_key' => $threadKey,
        ]);

        return (int) $this->db->lastInsertId();
    }

    private function storeInboundMessage(int $campaignId, int $contactId, array $payload): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_messages (campaign_id, contact_id, direction, subject, body, provider_message_id, received_at)
             VALUES (:campaign_id, :contact_id, "inbound", :subject, :body, :provider_message_id, NOW())'
        );

        $stmt->execute([
            ':campaign_id' => $campaignId,
            ':contact_id' => $contactId,
            ':subject' => (string) ($payload['subject'] ?? '(sans objet)'),
            ':body' => (string) ($payload['body'] ?? ''),
            ':provider_message_id' => (string) ($payload['provider_message_id'] ?? ''),
        ]);
    }

    private function markContactAsReplied(int $contactId): void
    {
        $stmt = $this->db->prepare('UPDATE prospect_contacts SET contact_status = "replied" WHERE id = :id');
        $stmt->execute([':id' => $contactId]);
    }

    private function logInfo(string $context, string $message, array $payload): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO prospect_logs (level, context, message, payload_json)
             VALUES ("info", :context, :message, :payload_json)'
        );
        $stmt->execute([
            ':context' => $context,
            ':message' => $message,
            ':payload_json' => json_encode($payload),
        ]);
    }
}
