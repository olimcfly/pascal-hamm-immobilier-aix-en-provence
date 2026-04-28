<?php

require_once dirname(__DIR__) . '/TenantContext.php';

class LeadService
{
    public const SOURCE_ESTIMATION = 'estimation';
    public const SOURCE_RESSOURCE = 'telechargement';
    public const SOURCE_CONTACT = 'contact';
    public const SOURCE_FINANCEMENT = 'financement';
    /** Page /avis-de-valeur (distinct du tunnel estimation) */
    public const SOURCE_AVIS_VALEUR = 'avis_valeur';
    public const SOURCE_AUTRE = 'autre';

    private static bool $tableReady = false;
    private static bool $interactionTableReady = false;
    private static bool $orgColumnChecked = false;

    public static function capture(array $payload): int
    {
        self::ensureTable();

        $source = self::sanitizeSource((string)($payload['source_type'] ?? self::SOURCE_AUTRE));
        $pipeline = self::sanitizePipeline((string)($payload['pipeline'] ?? $source));
        $stage = self::sanitizeStage((string)($payload['stage'] ?? self::defaultStage($source)));
        $orgId = (int) ($payload['organization_id'] ?? TenantContext::leadCaptureOrganizationId());
        if ($orgId <= 0) {
            error_log('LeadService::capture: organization_id manquant (migrations SaaS 041+ / table organizations requise).');
        }

        $stmt = db()->prepare('INSERT INTO crm_leads
            (organization_id, source_type, pipeline, stage, priority, first_name, last_name, email, phone, intent, property_type, property_address, metadata_json, notes, consent, created_at, updated_at)
            VALUES
            (:organization_id, :source_type, :pipeline, :stage, :priority, :first_name, :last_name, :email, :phone, :intent, :property_type, :property_address, :metadata_json, :notes, :consent, NOW(), NOW())');

        $stmt->execute([
            ':organization_id' => $orgId > 0 ? $orgId : null,
            ':source_type' => $source,
            ':pipeline' => $pipeline,
            ':stage' => $stage,
            ':priority' => self::sanitizePriority((string)($payload['priority'] ?? 'normal')),
            ':first_name' => trim((string)($payload['first_name'] ?? '')),
            ':last_name' => trim((string)($payload['last_name'] ?? '')),
            ':email' => trim((string)($payload['email'] ?? '')),
            ':phone' => trim((string)($payload['phone'] ?? '')),
            ':intent' => trim((string)($payload['intent'] ?? '')),
            ':property_type' => trim((string)($payload['property_type'] ?? '')),
            ':property_address' => trim((string)($payload['property_address'] ?? '')),
            ':metadata_json' => json_encode($payload['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
            ':notes' => trim((string)($payload['notes'] ?? '')),
            ':consent' => !empty($payload['consent']) ? 1 : 0,
        ]);

        $leadId = (int)db()->lastInsertId();

        // Créer un thread + message dans la messagerie CRM
        if ($source === self::SOURCE_CONTACT) {
            self::createMessageThread($leadId, $payload);
        }

        self::maybeEnrollEmailSequence($source, $payload);

        return $leadId;
    }

    /**
     * Inscrit le lead aux séquences email automatiques actives (point d’entrée du site).
     */
    private static function maybeEnrollEmailSequence(string $source, array $payload): void
    {
        if (empty($payload['consent']) || empty($payload['email'])) {
            return;
        }
        $email = trim((string) $payload['email']);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        if (!class_exists('EmailSequenceService')) {
            return;
        }

        $trigger = self::resolveEmailSequenceFormTrigger($source, $payload);
        if ($trigger === null || $trigger === '') {
            return;
        }

        $seqMeta = null;
        if (!empty($payload['email_sequence_metadata']) && is_array($payload['email_sequence_metadata'])) {
            $seqMeta = $payload['email_sequence_metadata'];
        }

        try {
            EmailSequenceService::triggerSequenceFromForm(
                $trigger,
                $email,
                trim((string) ($payload['first_name'] ?? '')),
                $seqMeta
            );
        } catch (Throwable $e) {
            error_log('LeadService::maybeEnrollEmailSequence — ' . $e->getMessage());
        }
    }

    private static function resolveEmailSequenceFormTrigger(string $source, array $payload): ?string
    {
        if (!empty($payload['email_sequence_trigger'])) {
            $t = strtolower(preg_replace('/[^a-z0-9_-]/', '', (string) $payload['email_sequence_trigger']));

            return $t !== '' ? $t : null;
        }

        switch ($source) {
            case self::SOURCE_CONTACT:
                return EmailSequenceService::TRIGGER_CONTACT;
            case self::SOURCE_RESSOURCE:
                return EmailSequenceService::TRIGGER_GUIDE_OFFERT;
            case self::SOURCE_FINANCEMENT:
                return EmailSequenceService::TRIGGER_FINANCEMENT;
            case self::SOURCE_AVIS_VALEUR:
                return EmailSequenceService::TRIGGER_AVIS_VALEUR;
            case self::SOURCE_ESTIMATION:
                return self::resolveEstimationEmailSequenceTrigger((string) ($payload['intent'] ?? ''));
            default:
                return null;
        }
    }

    private static function resolveEstimationEmailSequenceTrigger(string $intent): string
    {
        $i = strtolower($intent);
        if (in_array($i, ['email_report', 'contact_request', 'rdv_request'], true)) {
            return match ($i) {
                'email_report' => EmailSequenceService::TRIGGER_ESTIMATION_RAPPORT,
                'contact_request' => EmailSequenceService::TRIGGER_ESTIMATION_TUNNEL_CONTACT,
                'rdv_request' => EmailSequenceService::TRIGGER_ESTIMATION_TUNNEL_RDV,
                default => EmailSequenceService::TRIGGER_ESTIMATION_GRATUITE,
            };
        }
        if (str_contains($i, 'estimation + rdv')) {
            return EmailSequenceService::TRIGGER_ESTIMATION_RESULTAT;
        }
        if ((str_contains($i, 'rendez-vous') || str_contains($i, 'rdv')) && str_contains($i, 'affin')) {
            return EmailSequenceService::TRIGGER_PRENDRE_RDV;
        }
        if (str_contains($i, 'demande de rendez-vous') || str_contains($i, 'demande rendez-vous')) {
            return EmailSequenceService::TRIGGER_PRENDRE_RDV;
        }

        return EmailSequenceService::TRIGGER_ESTIMATION_GRATUITE;
    }

    public static function list(array $filters = []): array
    {
        self::ensureTable();

        $params = [];
        $where = [];

        if (!empty($filters['source_type'])) {
            $where[] = 'source_type = :source';
            $params[':source'] = self::sanitizeSource((string)$filters['source_type']);
        }

        if (!empty($filters['pipeline'])) {
            $where[] = 'pipeline = :pipeline';
            $params[':pipeline'] = self::sanitizePipeline((string)$filters['pipeline']);
        }

        if (!empty($filters['stage_like'])) {
            $where[] = 'stage LIKE :stage_like';
            $params[':stage_like'] = '%' . self::sanitizeStageLike((string)$filters['stage_like']) . '%';
        }

        $tenant = TenantContext::crmLeadsTenantPredicate();
        if ($tenant['sql'] !== '1=1') {
            $where[] = '(' . $tenant['sql'] . ')';
            foreach ($tenant['params'] as $k => $v) {
                $params[$k] = $v;
            }
        }

        $sql = 'SELECT * FROM crm_leads';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 300';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['metadata'] = json_decode((string)($row['metadata_json'] ?? '{}'), true) ?: [];
        }

        return $rows;
    }

    public static function updateRdvStatus(int $leadId, string $action, ?string $scheduledAt = null, string $comment = ''): bool
    {
        self::ensureTable();

        $leadId = max(0, $leadId);
        if ($leadId <= 0) {
            return false;
        }

        $action = strtolower(trim($action));
        if (!in_array($action, ['confirm', 'cancel', 'reschedule'], true)) {
            return false;
        }

        $targetStage = match ($action) {
            'confirm' => 'rdv_planifie',
            'cancel' => 'perdu',
            'reschedule' => 'rdv_a_planifier',
        };

        $pred = TenantContext::crmLeadsTenantPredicate();
        $sqlSel = 'SELECT metadata_json, notes FROM crm_leads WHERE id = :id AND (' . $pred['sql'] . ') LIMIT 1';
        $paramsSel = array_merge([':id' => $leadId], $pred['params']);
        $stmt = db()->prepare($sqlSel);
        $stmt->execute($paramsSel);
        $lead = $stmt->fetch();
        if (!$lead) {
            return false;
        }

        $metadata = json_decode((string)($lead['metadata_json'] ?? '{}'), true) ?: [];
        $existingNotes = trim((string)($lead['notes'] ?? ''));

        if ($scheduledAt !== null && $scheduledAt !== '') {
            $metadata['appointment_at'] = $scheduledAt;
        }
        $metadata['appointment_status'] = $action;
        $metadata['appointment_updated_at'] = date('c');

        $comment = trim($comment);
        $nextNotes = $existingNotes;
        if ($comment !== '') {
            $prefix = '[' . date('d/m/Y H:i') . '] ';
            $nextNotes = trim($existingNotes . PHP_EOL . $prefix . $comment);
        }

        $pred = TenantContext::crmLeadsTenantPredicate();
        $sqlUp = 'UPDATE crm_leads
            SET stage = :stage,
                metadata_json = :metadata_json,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id AND (' . $pred['sql'] . ')
            LIMIT 1';
        $paramsUp = array_merge([
            ':stage' => $targetStage,
            ':metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
            ':notes' => $nextNotes,
            ':id' => $leadId,
        ], $pred['params']);

        return db()->prepare($sqlUp)->execute($paramsUp);
    }

    public static function stageMatrix(): array
    {
        return [
            self::SOURCE_ESTIMATION => ['nouveau', 'a_qualifier', 'rdv_a_planifier', 'rdv_planifie', 'converti', 'perdu'],
            self::SOURCE_RESSOURCE => ['nouveau', 'nurturing', 'a_relancer', 'rdv_propose', 'converti', 'inactif'],
            self::SOURCE_CONTACT => ['nouveau', 'a_traiter', 'en_discussion', 'rdv_planifie', 'converti', 'archive'],
            self::SOURCE_FINANCEMENT => ['nouveau', 'en_cours', 'traite'],
            self::SOURCE_AVIS_VALEUR => ['nouveau', 'a_qualifier', 'rdv_a_planifier', 'rdv_planifie', 'converti', 'perdu'],
            self::SOURCE_AUTRE => ['nouveau', 'a_qualifier', 'en_cours', 'converti', 'archive'],
        ];
    }

    public static function stageLabel(string $stage): string
    {
        $labels = [
            'nouveau' => 'Nouveau',
            'a_qualifier' => 'À qualifier',
            'rdv_a_planifier' => 'RDV à planifier',
            'rdv_planifie' => 'RDV planifié',
            'converti' => 'Converti',
            'perdu' => 'Perdu',
            'nurturing' => 'Nurturing',
            'a_relancer' => 'À relancer',
            'rdv_propose' => 'RDV proposé',
            'inactif' => 'Inactif',
            'a_traiter' => 'À traiter',
            'en_discussion' => 'En discussion',
            'archive' => 'Archivé',
            'en_cours' => 'En cours',
            'traite' => 'Traité',
        ];

        return $labels[$stage] ?? ucfirst(str_replace('_', ' ', $stage));
    }

    public static function sourceLabel(string $source): string
    {
        return [
            self::SOURCE_ESTIMATION => 'Estimation',
            self::SOURCE_RESSOURCE => 'Téléchargement',
            self::SOURCE_CONTACT => 'Contact',
            self::SOURCE_FINANCEMENT => 'Financement',
            self::SOURCE_AVIS_VALEUR => 'Avis de valeur',
            self::SOURCE_AUTRE => 'Autre',
        ][$source] ?? ucfirst($source);
    }

    private static function ensureTable(): void
    {
        self::ensureOrganizationColumn();

        if (self::$tableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS crm_leads (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            organization_id INT UNSIGNED NULL,
            source_type VARCHAR(40) NOT NULL DEFAULT "autre",
            pipeline VARCHAR(60) NOT NULL DEFAULT "autre",
            stage VARCHAR(60) NOT NULL DEFAULT "nouveau",
            priority VARCHAR(16) NOT NULL DEFAULT "normal",
            first_name VARCHAR(80) NOT NULL,
            last_name VARCHAR(80) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NULL,
            intent VARCHAR(120) NULL,
            property_type VARCHAR(60) NULL,
            property_address VARCHAR(255) NULL,
            metadata_json JSON NULL,
            notes TEXT NULL,
            consent TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX idx_source_stage (source_type, stage),
            INDEX idx_pipeline (pipeline),
            INDEX idx_email (email),
            INDEX idx_created_at (created_at),
            INDEX idx_crm_leads_organization (organization_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$tableReady = true;
    }

    /**
     * Table créée avant migration 041 : ajoute organization_id si absent.
     */
    private static function ensureOrganizationColumn(): void
    {
        if (self::$orgColumnChecked) {
            return;
        }
        self::$orgColumnChecked = true;
        try {
            $db = db();
            $tbl = $db->query("SHOW TABLES LIKE 'crm_leads'");
            if (!$tbl || $tbl->rowCount() === 0) {
                return;
            }
            $col = $db->query("SHOW COLUMNS FROM crm_leads LIKE 'organization_id'");
            if ($col && $col->rowCount() > 0) {
                return;
            }
            $db->exec('ALTER TABLE crm_leads ADD COLUMN organization_id INT UNSIGNED NULL AFTER id');
            $db->exec(
                "UPDATE crm_leads SET organization_id = (SELECT id FROM organizations WHERE slug = 'legacy-default' LIMIT 1) WHERE organization_id IS NULL"
            );
            $db->exec('ALTER TABLE crm_leads ADD INDEX idx_crm_leads_organization (organization_id)');
        } catch (Throwable $e) {
            error_log('LeadService::ensureOrganizationColumn: ' . $e->getMessage());
        }
    }

    public static function logInteraction(int $leadId, string $type, string $note = '', array $meta = []): bool
    {
        self::ensureTable();
        self::ensureInteractionTable();

        if ($leadId <= 0) {
            return false;
        }
        if (!self::assertCrmLeadAccess($leadId)) {
            return false;
        }

        $type = self::sanitizeInteractionType($type);
        $note = trim($note);
        $oldValue = isset($meta['old']) ? (string)$meta['old'] : null;
        $newValue = isset($meta['new']) ? (string)$meta['new'] : null;

        $stmt = db()->prepare('INSERT INTO crm_lead_interactions
            (lead_id, interaction_type, old_value, new_value, note, created_at)
            VALUES (:lead_id, :interaction_type, :old_value, :new_value, :note, NOW())');

        return $stmt->execute([
            ':lead_id' => $leadId,
            ':interaction_type' => $type,
            ':old_value' => $oldValue,
            ':new_value' => $newValue,
            ':note' => $note !== '' ? $note : null,
        ]);
    }

    public static function latestInteractionsByLead(array $leadIds, string $type = 'appel'): array
    {
        self::ensureTable();
        self::ensureInteractionTable();

        $leadIds = array_values(array_filter(array_map('intval', $leadIds), static fn(int $id): bool => $id > 0));
        if ($leadIds === []) {
            return [];
        }

        $pred = TenantContext::crmLeadsTenantPredicate();
        $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
        $tenantClause = $pred['sql'] === '1=1' ? '1=1' : $pred['sql'];
        $tenantParams = $pred['params'];
        $sqlAllowed = 'SELECT id FROM crm_leads WHERE id IN (' . $placeholders . ') AND (' . $tenantClause . ')';
        $stmtAllow = db()->prepare($sqlAllowed);
        $stmtAllow->execute([...$leadIds, ...array_values($tenantParams)]);
        $allowed = [];
        while ($r = $stmtAllow->fetch(PDO::FETCH_ASSOC)) {
            $allowed[] = (int) ($r['id'] ?? 0);
        }
        if ($allowed === []) {
            return [];
        }

        $type = self::sanitizeInteractionType($type);
        $placeholders2 = implode(',', array_fill(0, count($allowed), '?'));

        $sql = 'SELECT i.*
                FROM crm_lead_interactions i
                INNER JOIN (
                    SELECT lead_id, MAX(id) AS max_id
                    FROM crm_lead_interactions
                    WHERE lead_id IN (' . $placeholders2 . ') AND interaction_type = ?
                    GROUP BY lead_id
                ) latest ON latest.max_id = i.id';

        $stmt = db()->prepare($sql);
        $stmt->execute([...$allowed, $type]);
        $rows = $stmt->fetchAll();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['lead_id']] = $row;
        }

        return $indexed;
    }

    private static function ensureInteractionTable(): void
    {
        if (self::$interactionTableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS crm_lead_interactions (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lead_id INT UNSIGNED NOT NULL,
            interaction_type VARCHAR(20) NOT NULL,
            old_value VARCHAR(80) NULL,
            new_value VARCHAR(80) NULL,
            note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lead_created (lead_id, created_at),
            INDEX idx_lead_type (lead_id, interaction_type, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$interactionTableReady = true;
    }

    private static function sanitizeInteractionType(string $type): string
    {
        $type = strtolower(trim($type));
        $allowed = ['status', 'note', 'email', 'appel', 'sms', 'rdv', 'autre'];
        return in_array($type, $allowed, true) ? $type : 'autre';
    }

    private static function sanitizeSource(string $source): string
    {
        $source = strtolower(trim($source));
        $allowed = [self::SOURCE_ESTIMATION, self::SOURCE_RESSOURCE, self::SOURCE_CONTACT, self::SOURCE_FINANCEMENT, self::SOURCE_AVIS_VALEUR, self::SOURCE_AUTRE];
        return in_array($source, $allowed, true) ? $source : self::SOURCE_AUTRE;
    }

    private static function sanitizePipeline(string $pipeline): string
    {
        $pipeline = strtolower(trim($pipeline));
        return preg_replace('/[^a-z0-9_\-]/', '', $pipeline) ?: self::SOURCE_AUTRE;
    }

    private static function sanitizeStage(string $stage): string
    {
        $stage = strtolower(trim($stage));
        return preg_replace('/[^a-z0-9_\-]/', '', $stage) ?: 'nouveau';
    }

    private static function sanitizeStageLike(string $stage): string
    {
        $stage = strtolower(trim($stage));
        return preg_replace('/[^a-z0-9_\-]/', '', $stage) ?: 'rdv';
    }

    private static function sanitizePriority(string $priority): string
    {
        $allowed = ['basse', 'normal', 'haute'];
        return in_array($priority, $allowed, true) ? $priority : 'normal';
    }

    private static function assertCrmLeadAccess(int $leadId): bool
    {
        $pred = TenantContext::crmLeadsTenantPredicate();
        $sql = 'SELECT id FROM crm_leads WHERE id = :id AND (' . $pred['sql'] . ') LIMIT 1';
        $params = array_merge([':id' => $leadId], $pred['params']);
        $st = db()->prepare($sql);
        $st->execute($params);

        return (bool) $st->fetch(PDO::FETCH_ASSOC);
    }

    private static function defaultStage(string $source): string
    {
        $matrix = self::stageMatrix();
        return $matrix[$source][0] ?? 'nouveau';
    }

    private static function createMessageThread(int $leadId, array $payload): void
    {
        try {
            $repoFile = ROOT_PATH . '/modules/messagerie/repositories/MessageRepository.php';
            if (!is_file($repoFile)) return;
            require_once $repoFile;

            // Récupère le user_id du premier admin actif
            $adminUser = db()->query(
                "SELECT id FROM users WHERE role IN ('admin','superadmin') AND status='active' ORDER BY id ASC LIMIT 1"
            )->fetch(PDO::FETCH_ASSOC);
            $userId = (int)($adminUser['id'] ?? 1);

            $repo        = new MessageRepository(db());
            $email       = strtolower(trim((string)($payload['email'] ?? '')));
            $firstName   = trim((string)($payload['first_name'] ?? ''));
            $lastName    = trim((string)($payload['last_name'] ?? ''));
            $name        = trim("$firstName $lastName") ?: $email;
            $intent      = trim((string)($payload['intent'] ?? 'Contact général'));
            $notes       = trim((string)($payload['notes'] ?? ''));
            $phone       = trim((string)($payload['phone'] ?? ''));

            $subject = $intent ?: 'Nouveau message de contact';
            $snippet = mb_substr($notes, 0, 120);

            $threadId = $repo->upsertThread($userId, $email, $name, $subject, $snippet);

            $bodyParts = [];
            if ($notes !== '')  $bodyParts[] = nl2br(htmlspecialchars($notes));
            if ($phone !== '')  $bodyParts[] = '<p><strong>Téléphone :</strong> ' . htmlspecialchars($phone) . '</p>';

            $bodyHtml = implode("\n", $bodyParts);
            $bodyText = $notes . ($phone ? "\nTéléphone : $phone" : '');

            $repo->insertMessage([
                'thread_id'       => $threadId,
                'user_id'         => $userId,
                'gmail_message_id'=> 'contact_form_lead_' . $leadId,
                'direction'       => 'inbound',
                'from_email'      => $email,
                'from_name'       => $name,
                'to_email'        => (string)(setting('smtp_user', '') ?: APP_EMAIL ?? ''),
                'subject'         => $subject,
                'body_html'       => $bodyHtml,
                'body_text'       => $bodyText,
                'status'          => 'received',
                'is_read'         => 0,
                'sent_at'         => date('Y-m-d H:i:s'),
            ]);

            $repo->incrementUnread($threadId);
        } catch (Throwable) {
            // Ne jamais bloquer la soumission du formulaire
        }
    }
}
