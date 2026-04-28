<?php

declare(strict_types=1);

class EmailSequenceService
{
    /** Slugs utilisés par les formulaires du site (voir LeadService::resolveEmailSequenceFormTrigger). */
    public const TRIGGER_CONTACT = 'contact';
    public const TRIGGER_ESTIMATION_GRATUITE = 'estimation-gratuite';
    public const TRIGGER_ESTIMATION_RESULTAT = 'estimation-resultat';
    public const TRIGGER_ESTIMATION_RAPPORT = 'estimation-rapport';
    public const TRIGGER_ESTIMATION_TUNNEL_CONTACT = 'estimation-tunnel-contact';
    public const TRIGGER_ESTIMATION_TUNNEL_RDV = 'estimation-tunnel-rdv';
    public const TRIGGER_PRENDRE_RDV = 'prendre-rendez-vous';
    public const TRIGGER_GUIDE_OFFERT = 'guide-offert';
    public const TRIGGER_BIEN_DETAIL = 'bien-detail';
    public const TRIGGER_FINANCEMENT = 'financement';
    public const TRIGGER_AVIS_VALEUR = 'avis-valeur';

    private const EMAIL_TEMPLATES = [
        1 => [
            'subject_template' => 'Votre opportunité immobilière à {city}',
            'body_template' => 'Bonjour {first_name},\n\nNous avons identifié une opportunité {objective} à {city} qui pourrait vous intéresser.',
        ],
        2 => [
            'subject_template' => 'Comment maximiser votre {objective}',
            'body_template' => 'Cher(e) {first_name},\n\nVous êtes un(e) {persona} cherchant à {objective}. Voici nos conseils pour réussir.',
        ],
        3 => [
            'subject_template' => 'Les 3 erreurs à éviter pour votre {objective}',
            'body_template' => 'Bonjour {first_name},\n\nNous avons analysé 100+ transactions à {city}. Voici ce qui fonctionne et ce à éviter.',
        ],
        4 => [
            'subject_template' => 'Témoignage: Comment {persona} a réussi à {objective}',
            'body_template' => 'Cher(e) {first_name},\n\nDécouvrez comment l\'un de nos clients {persona} a atteint son objectif {objective}.',
        ],
        5 => [
            'subject_template' => 'Votre consultation gratuite à {city}',
            'body_template' => 'Bonjour {first_name},\n\nUn dernier message: reservez votre consultation gratuite pour discuter de votre {objective}.',
        ],
    ];

    public static function createSequence(
        string $name,
        string $objective,
        string $persona,
        string $city,
        string $description = '',
        string $triggerType = 'manual',
        ?string $formTrigger = null,
        ?string $destinationType = null,
        ?string $destinationUrl = null,
        ?string $destinationLabel = null,
        ?string $destinationContactType = null
    ): int {
        try {
            $db = db();

            $stmt = $db->prepare('
                INSERT INTO email_sequences
                (name, objective, persona, city, description, trigger_type, form_trigger, destination_type, destination_url, destination_label, destination_contact_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $status = $triggerType === 'automatic' ? 'draft' : 'draft';

            $stmt->execute([
                $name,
                $objective,
                $persona,
                $city,
                $description,
                $triggerType,
                $formTrigger,
                $destinationType,
                $destinationUrl,
                $destinationLabel,
                $destinationContactType,
                $status,
            ]);

            $sequenceId = (int) $db->lastInsertId();

            self::generateSequenceEmails($sequenceId, $objective, $persona, $city);

            return $sequenceId;
        } catch (Throwable $e) {
            error_log('Error creating sequence: ' . $e->getMessage());
            throw $e;
        }
    }

    private static function generateSequenceEmails(
        int $sequenceId,
        string $objective,
        string $persona,
        string $city
    ): void {
        try {
            $db = db();

            foreach (self::EMAIL_TEMPLATES as $emailNumber => $template) {
                $subject = self::replacePlaceholders($template['subject_template'], [
                    '{objective}' => $objective,
                    '{persona}' => $persona,
                    '{city}' => $city,
                ]);

                $body = self::replacePlaceholders($template['body_template'], [
                    '{objective}' => $objective,
                    '{persona}' => $persona,
                    '{city}' => $city,
                    '{first_name}' => '{first_name}',
                ]);

                $delay = ($emailNumber - 1) * 3;

                $stmt = $db->prepare('
                    INSERT INTO email_sequence_emails
                    (sequence_id, email_number, subject, body_html, delay_days)
                    VALUES (?, ?, ?, ?, ?)
                ');

                $stmt->execute([
                    $sequenceId,
                    $emailNumber,
                    $subject,
                    $body,
                    $delay,
                ]);
            }
        } catch (Throwable $e) {
            error_log('Error generating sequence emails: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getSequence(int $sequenceId): ?array
    {
        try {
            $stmt = db()->prepare('SELECT * FROM email_sequences WHERE id = ?');
            $stmt->execute([$sequenceId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            error_log('Error fetching sequence: ' . $e->getMessage());
            return null;
        }
    }

    public static function getSequenceEmails(int $sequenceId): array
    {
        try {
            $stmt = db()->prepare('
                SELECT * FROM email_sequence_emails
                WHERE sequence_id = ?
                ORDER BY email_number ASC
            ');
            $stmt->execute([$sequenceId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('Error fetching sequence emails: ' . $e->getMessage());
            return [];
        }
    }

    public static function updateSequenceEmail(
        int $emailId,
        string $subject,
        string $body,
        string $preview = ''
    ): bool {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequence_emails
                SET subject = ?, body_html = ?, preview_text = ?
                WHERE id = ?
            ');

            return $stmt->execute([
                $subject,
                $body,
                $preview,
                $emailId,
            ]);
        } catch (Throwable $e) {
            error_log('Error updating sequence email: ' . $e->getMessage());
            return false;
        }
    }

    public static function activateSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET status = ?
                WHERE id = ?
            ');

            return $stmt->execute(['active', $sequenceId]);
        } catch (Throwable $e) {
            error_log('Error activating sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function deactivateSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET status = ?
                WHERE id = ?
            ');

            return $stmt->execute(['inactive', $sequenceId]);
        } catch (Throwable $e) {
            error_log('Error deactivating sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function subscribeToSequence(
        int $sequenceId,
        string $email,
        string $firstName = '',
        string $lastName = '',
        ?array $metadata = null
    ): int {
        try {
            $email = strtolower(trim($email));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return 0;
            }

            $db = db();

            $stmt = $db->prepare('
                SELECT id, status FROM email_sequence_subscriptions
                WHERE sequence_id = ? AND email = ? LIMIT 1
            ');
            $stmt->execute([$sequenceId, $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $st = (string) ($existing['status'] ?? '');
                if ($st === 'pending' || $st === 'active') {
                    return (int) $existing['id'];
                }

                return 0;
            }

            $metaJson = null;
            if ($metadata !== null && $metadata !== []) {
                $metaJson = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            if ($metaJson !== null && self::subscriptionTableHasMetadataJson($db)) {
                $stmt = $db->prepare('
                    INSERT INTO email_sequence_subscriptions
                    (sequence_id, email, first_name, last_name, metadata_json, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $sequenceId,
                    $email,
                    $firstName,
                    $lastName,
                    $metaJson,
                    'pending',
                ]);
            } else {
                $stmt = $db->prepare('
                    INSERT INTO email_sequence_subscriptions
                    (sequence_id, email, first_name, last_name, status)
                    VALUES (?, ?, ?, ?, ?)
                ');

                $stmt->execute([
                    $sequenceId,
                    $email,
                    $firstName,
                    $lastName,
                    'pending',
                ]);
            }

            return (int) $db->lastInsertId();
        } catch (Throwable $e) {
            error_log('Error subscribing to sequence: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getSequenceStats(int $sequenceId): array
    {
        try {
            $db = db();

            $stats = [
                'total_subscribers' => 0,
                'active_subscribers' => 0,
                'completed_subscribers' => 0,
                'total_sent' => 0,
                'total_opened' => 0,
                'total_clicked' => 0,
            ];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ?
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ? AND status = ?
            ');
            $stmt->execute([$sequenceId, 'active']);
            $stats['active_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_subscriptions
                WHERE sequence_id = ? AND status = ?
            ');
            $stmt->execute([$sequenceId, 'completed']);
            $stats['completed_subscribers'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ?
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_sent'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ? AND ess.opened_at IS NOT NULL
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_opened'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $stmt = $db->prepare('
                SELECT COUNT(*) as count FROM email_sequence_sends ess
                JOIN email_sequence_subscriptions esub ON ess.subscription_id = esub.id
                WHERE esub.sequence_id = ? AND ess.clicked_at IS NOT NULL
            ');
            $stmt->execute([$sequenceId]);
            $stats['total_clicked'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (Throwable $e) {
            error_log('Error getting sequence stats: ' . $e->getMessage());
            return [];
        }
    }

    private static function replacePlaceholders(string $text, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace($placeholder, (string) $value, $text);
        }

        return $text;
    }

    public static function deleteSequence(int $sequenceId): bool
    {
        try {
            $stmt = db()->prepare('DELETE FROM email_sequences WHERE id = ?');
            return $stmt->execute([$sequenceId]);
        } catch (Throwable $e) {
            error_log('Error deleting sequence: ' . $e->getMessage());
            return false;
        }
    }

    public static function getSequencesByCity(string $city): array
    {
        try {
            $stmt = db()->prepare('
                SELECT * FROM email_sequences
                WHERE city = ? AND status = ?
                ORDER BY created_at DESC
            ');
            $stmt->execute([$city, 'active']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (Throwable $e) {
            error_log('Error fetching sequences by city: ' . $e->getMessage());
            return [];
        }
    }

    public static function triggerSequenceFromForm(string $formName, string $email, string $firstName = '', ?array $metadata = null): bool
    {
        try {
            $stmt = db()->prepare('
                SELECT id FROM email_sequences
                WHERE form_trigger = ? AND trigger_type = ? AND status = ?
            ');
            $stmt->execute([$formName, 'automatic', 'active']);
            $sequences = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sequences as $sequence) {
                self::subscribeToSequence(
                    (int) $sequence['id'],
                    $email,
                    $firstName,
                    '',
                    $metadata
                );
            }

            return true;
        } catch (Throwable $e) {
            error_log('Error triggering sequence from form: ' . $e->getMessage());
            return false;
        }
    }

    public static function updateSequenceDestination(
        int $sequenceId,
        ?string $destinationType = null,
        ?string $destinationUrl = null,
        ?string $destinationLabel = null,
        ?string $destinationContactType = null
    ): bool {
        try {
            $stmt = db()->prepare('
                UPDATE email_sequences
                SET destination_type = ?, destination_url = ?, destination_label = ?, destination_contact_type = ?
                WHERE id = ?
            ');
            return $stmt->execute([
                $destinationType,
                $destinationUrl,
                $destinationLabel,
                $destinationContactType,
                $sequenceId,
            ]);
        } catch (Throwable $e) {
            error_log('Error updating sequence destination: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée les séquences automatiques par point d’entrée (idempotent : une par form_trigger).
     */
    public static function ensureDefaultEntrySequences(): int
    {
        $city = self::resolveDefaultCity();
        $created = 0;

        foreach (self::entrySequencePresets($city) as $formTrigger => $preset) {
            if (self::sequenceExistsForFormTrigger($formTrigger)) {
                continue;
            }
            try {
                self::insertEntrySequenceRow($preset, $formTrigger, $city);
                ++$created;
            } catch (Throwable $e) {
                error_log('EmailSequenceService::ensureDefaultEntrySequences — ' . $e->getMessage());
            }
        }

        return $created;
    }

    /**
     * Envoie les emails dus (à appeler via cron toutes les 15–60 min).
     */
    public static function processDueSends(int $maxSubscriptions = 40): int
    {
        if (!class_exists(MailService::class)) {
            return 0;
        }

        $sent = 0;
        try {
            $db = db();
            $hasMeta = self::subscriptionTableHasMetadataJson($db);
            $metaCol = $hasMeta ? 'esub.metadata_json' : 'NULL AS metadata_json';
            $stmt = $db->prepare('
                SELECT esub.id AS sub_id, esub.sequence_id, esub.email, esub.first_name, esub.last_name, esub.status AS sub_status,
                       ' . $metaCol . ',
                       seq.city AS seq_city, seq.name AS seq_name
                FROM email_sequence_subscriptions esub
                INNER JOIN email_sequences seq ON seq.id = esub.sequence_id AND seq.status = ?
                WHERE esub.status IN (\'pending\', \'active\')
                ORDER BY esub.id ASC
                LIMIT ' . max(1, min(200, $maxSubscriptions)) . '
            ');
            $stmt->execute(['active']);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as $row) {
                if (self::sendNextEmailForSubscription($db, $row)) {
                    ++$sent;
                }
            }
        } catch (Throwable $e) {
            error_log('EmailSequenceService::processDueSends — ' . $e->getMessage());
        }

        return $sent;
    }

    private static function resolveDefaultCity(): string
    {
        if (function_exists('setting')) {
            $c = trim((string) setting('zone_city', ''));
            if ($c !== '') {
                return $c;
            }
        }
        if (defined('APP_CITY')) {
            return (string) APP_CITY;
        }

        return 'votre secteur';
    }

    private static function advisorDisplayName(): string
    {
        if (function_exists('setting')) {
            $fn = trim((string) setting('advisor_firstname', ''));
            $ln = trim((string) setting('advisor_lastname', ''));
            $n = trim($fn . ' ' . $ln);
            if ($n !== '') {
                return $n;
            }
        }
        if (defined('ADVISOR_NAME') && ADVISOR_NAME !== '') {
            return (string) ADVISOR_NAME;
        }

        return 'Votre conseiller';
    }

    private static function personalizationMap(string $firstName, string $city): array
    {
        $fn = trim($firstName);

        return [
            '{first_name}' => $fn !== '' ? $fn : 'Bonjour',
            '{city}' => $city,
            '{advisor_name}' => self::advisorDisplayName(),
        ];
    }

    /**
     * Placeholders pour les séquences déclenchées avec contexte bien (ex. fiche annonce).
     * Clés JSON : bien_titre, bien_url, bien_reference, bien_ville, bien_type, bien_prix
     */
    private static function metadataBienPlaceholdersFromJson(?string $json): array
    {
        $defaults = [
            '{bien_titre}' => 'ce bien',
            '{bien_url}' => '',
            '{bien_reference}' => '',
            '{bien_ville}' => '',
            '{bien_type}' => '',
            '{bien_prix}' => '',
        ];
        if ($json === null || $json === '') {
            return $defaults;
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return $defaults;
        }
        $map = $defaults;
        foreach (['bien_titre', 'bien_url', 'bien_reference', 'bien_ville', 'bien_type', 'bien_prix'] as $k) {
            if (isset($data[$k]) && (string) $data[$k] !== '') {
                $map['{' . $k . '}'] = (string) $data[$k];
            }
        }

        return $map;
    }

    private static function applyPersonalization(string $text, array $subRow, ?array $seqRow = null): string
    {
        $city = trim((string) ($seqRow['city'] ?? $subRow['seq_city'] ?? ''));
        if ($city === '') {
            $city = self::resolveDefaultCity();
        }
        $bienMap = self::metadataBienPlaceholdersFromJson(isset($subRow['metadata_json']) ? (string) $subRow['metadata_json'] : null);
        $map = array_merge($bienMap, self::personalizationMap((string) ($subRow['first_name'] ?? ''), $city));

        return self::replacePlaceholders($text, $map);
    }

    private static ?bool $subscriptionMetadataColumn = null;

    private static function subscriptionTableHasMetadataJson(PDO $db): bool
    {
        if (self::$subscriptionMetadataColumn === true) {
            return true;
        }
        try {
            $st = $db->query("SHOW COLUMNS FROM email_sequence_subscriptions LIKE 'metadata_json'");
            $ok = (bool) $st->fetch(PDO::FETCH_ASSOC);
            if ($ok) {
                self::$subscriptionMetadataColumn = true;
            }

            return $ok;
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function sequenceExistsForFormTrigger(string $formTrigger): bool
    {
        try {
            $stmt = db()->prepare('
                SELECT id FROM email_sequences
                WHERE form_trigger = ? AND trigger_type = ? LIMIT 1
            ');
            $stmt->execute([$formTrigger, 'automatic']);

            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param array{name:string,description:string,objective:string,persona:string,emails:array<int,array{subject:string,body:string}>} $preset
     */
    private static function insertEntrySequenceRow(array $preset, string $formTrigger, string $city): void
    {
        $db = db();
        $stmt = $db->prepare('
            INSERT INTO email_sequences
            (name, objective, persona, city, description, status, trigger_type, form_trigger)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $preset['name'],
            $preset['objective'],
            $preset['persona'],
            $city,
            $preset['description'],
            'active',
            'automatic',
            $formTrigger,
        ]);
        $sequenceId = (int) $db->lastInsertId();

        foreach ($preset['emails'] as $emailNumber => $tpl) {
            $delay = ((int) $emailNumber - 1) * 3;
            $ins = $db->prepare('
                INSERT INTO email_sequence_emails
                (sequence_id, email_number, subject, body_html, delay_days)
                VALUES (?, ?, ?, ?, ?)
            ');
            $ins->execute([
                $sequenceId,
                (int) $emailNumber,
                self::replacePlaceholders($tpl['subject'], ['{city}' => $city]),
                self::replacePlaceholders($tpl['body'], ['{city}' => $city]),
                $delay,
            ]);
        }
    }

    private static function sendNextEmailForSubscription(PDO $db, array $subRow): bool
    {
        $subId = (int) $subRow['sub_id'];
        $sequenceId = (int) $subRow['sequence_id'];

        $lastStmt = $db->prepare('
            SELECT email_number, sent_at FROM email_sequence_sends
            WHERE subscription_id = ?
            ORDER BY email_number DESC LIMIT 1
        ');
        $lastStmt->execute([$subId]);
        $last = $lastStmt->fetch(PDO::FETCH_ASSOC);

        $nextNum = $last ? ((int) $last['email_number'] + 1) : 1;

        $tplStmt = $db->prepare('
            SELECT subject, body_html, delay_days FROM email_sequence_emails
            WHERE sequence_id = ? AND email_number = ? LIMIT 1
        ');
        $tplStmt->execute([$sequenceId, $nextNum]);
        $tpl = $tplStmt->fetch(PDO::FETCH_ASSOC);
        if (!$tpl) {
            $db->prepare('
                UPDATE email_sequence_subscriptions
                SET status = ?, completed_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ')->execute(['completed', $subId]);

            return false;
        }

        if ($nextNum > 1 && $last) {
            $delay = (int) $tpl['delay_days'];
            $lastSent = new DateTimeImmutable((string) $last['sent_at']);
            $due = $lastSent->modify('+' . $delay . ' days');
            if (new DateTimeImmutable() < $due) {
                return false;
            }
        }

        $seqStmt = $db->prepare('SELECT city, name FROM email_sequences WHERE id = ? LIMIT 1');
        $seqStmt->execute([$sequenceId]);
        $seq = $seqStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $subject = self::applyPersonalization((string) $tpl['subject'], $subRow, $seq);
        $body = self::applyPersonalization((string) $tpl['body_html'], $subRow, $seq);
        $html = '<p style="font-family:Georgia,serif;font-size:16px;line-height:1.6;color:#1a1a2e;">'
            . nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
            . '</p>';

        $to = (string) $subRow['email'];
        if (!MailService::send($to, $subject, $body, $html)) {
            error_log('EmailSequenceService: échec envoi séquence ' . $sequenceId . ' → ' . $to);

            return false;
        }

        $db->prepare('
            INSERT INTO email_sequence_sends (subscription_id, email_number) VALUES (?, ?)
        ')->execute([$subId, $nextNum]);

        $maxStmt = $db->prepare('SELECT MAX(email_number) FROM email_sequence_emails WHERE sequence_id = ?');
        $maxStmt->execute([$sequenceId]);
        $maxStep = (int) $maxStmt->fetchColumn();

        if ($nextNum >= $maxStep) {
            $db->prepare('
                UPDATE email_sequence_subscriptions
                SET status = ?, current_email_number = ?, completed_at = NOW(), started_at = COALESCE(started_at, NOW()), updated_at = NOW()
                WHERE id = ?
            ')->execute(['completed', $nextNum, $subId]);
        } else {
            $db->prepare('
                UPDATE email_sequence_subscriptions
                SET status = ?, current_email_number = ?, started_at = COALESCE(started_at, NOW()), updated_at = NOW()
                WHERE id = ?
            ')->execute(['active', $nextNum + 1, $subId]);
        }

        return true;
    }

    /**
     * @return array<string, array{name:string,description:string,objective:string,persona:string,emails:array<int,array{subject:string,body:string}>}>
     */
    private static function entrySequencePresets(string $city): array
    {
        $sig = "\n\nCordialement,\n{advisor_name}\nConseiller immobilier — {city}";

        $e = static function (string $intro, string $mid = '', string $subjectLine = '') use ($sig, $city): array {
            $body = $intro . ($mid !== '' ? "\n\n" . $mid : '') . str_replace('{city}', $city, $sig);

            return ['subject' => $subjectLine, 'body' => $body];
        };

        $five = static function (array $steps) use ($e): array {
            $out = [];
            foreach ([1, 2, 3, 4, 5] as $i) {
                $out[$i] = $e(...$steps[$i - 1]);
            }

            return $out;
        };

        return [
            self::TRIGGER_CONTACT => [
                'name' => 'Suite — Formulaire contact',
                'description' => 'Déclenchée après envoi du formulaire Contact.',
                'objective' => 'Échanger et proposer un rendez-vous',
                'persona' => 'Visiteur du site',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre message. Je le lis personnellement et vous réponds très vite, en général sous 24h ouvrées.', 'Merci pour votre message — ' . $city],
                    ['Bonjour {first_name},', 'Souhaitez-vous qu’on échange 15 minutes par téléphone pour cadrer votre projet ? Répondez simplement à cet email avec vos disponibilités.', 'Faisons le point sur votre projet'],
                    ['Bonjour {first_name},', 'Voici ce qui aide souvent nos clients à avancer : liste des pièces utiles pour une estimation, et 3 questions à se poser avant de vendre ou d’acheter.', '3 repères pour avancer sereinement'],
                    ['Bonjour {first_name},', 'Les projets immobiliers se jouent souvent sur le bon calendrier. Si vous le souhaitez, je peux vous proposer un créneau de visite ou d’appel.', 'Trouver le bon moment'],
                    ['Bonjour {first_name},', 'Dernier message de cette mini-série : je reste disponible pour un conseil personnalisé à ' . $city . '. Répondez « RDV » et je vous envoie des propositions de créneaux.', 'Restons en contact'],
                ]),
            ],
            self::TRIGGER_ESTIMATION_GRATUITE => [
                'name' => 'Suite — Estimation gratuite (étape 1)',
                'description' => 'Après la demande d’estimation en ligne (première étape).',
                'objective' => 'Qualifier le bien et proposer la suite',
                'persona' => 'Propriétaire ou acquéreur',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre demande d’estimation. Pour affiner le chiffrage, la prochaine étape utile est souvent une courte conversation ou une visite.', 'Votre demande d’estimation à ' . $city],
                    ['Bonjour {first_name},', 'Une estimation fiable croise données de marché et état réel du bien. Si vous le souhaitez, je vous explique notre méthode en quelques minutes.', 'Affiner votre estimation'],
                    ['Bonjour {first_name},', 'Les vendeurs qui réussissent fixent souvent un prix cohérent dès le départ. Je peux vous aider à le valider avec des comparables récents.', 'Prix et stratégie de mise en vente'],
                    ['Bonjour {first_name},', 'Souhaitez-vous recevoir une fourchette détaillée ou un avis après visite ? Dites-moi ce qui vous arrange.', 'Quelle suite pour votre estimation ?'],
                    ['Bonjour {first_name},', 'Je clôture ce fil d’emails ici — répondez quand vous voulez, je vous accompagne sur ' . $city . '.', 'Besoin d’un avis concret ?'],
                ]),
            ],
            self::TRIGGER_ESTIMATION_RESULTAT => [
                'name' => 'Suite — Estimation détaillée / qualification',
                'description' => 'Après la page résultat + formulaire de qualification.',
                'objective' => 'Transformer l’estimation en action (vente / achat)',
                'persona' => 'Propriétaire engagé',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour ces précisions sur votre projet. Votre estimation et votre situation m’aident à cibler la bonne stratégie.', 'Votre projet après l’estimation'],
                    ['Bonjour {first_name},', 'Souvent, la prochaine étape est de confirmer la fourchette avec des ventes réelles comparables sur votre secteur.', 'Comparables et marché réel'],
                    ['Bonjour {first_name},', 'Si vous vendez : préparer le dossier (diagnostics, charges, copropriété le cas échéant) accélère la suite.', 'Préparer une vente réussie'],
                    ['Bonjour {first_name},', 'Si vous achetez : définir budget, financement et secteur en priorité évite les pertes de temps.', 'Cadrer un achat à ' . $city],
                    ['Bonjour {first_name},', 'Je reste votre interlocuteur pour passer du chiffrage à la décision. Un créneau vous convient cette semaine ?', 'Passer à l’action'],
                ]),
            ],
            self::TRIGGER_ESTIMATION_RAPPORT => [
                'name' => 'Suite — Rapport d’estimation (email)',
                'description' => 'Après demande d’envoi du rapport depuis le tunnel estimation.',
                'objective' => 'Exploiter le rapport et proposer un RDV',
                'persona' => 'Prospect estimation',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Vous avez reçu votre rapport : gardez-le comme base, le marché évolue — une mise à jour après visite reste la référence.', 'À propos de votre rapport d’estimation'],
                    ['Bonjour {first_name},', 'Les points à vérifier ensuite : état du bien, travaux récents, étage/exposition, charges — tout impacte le prix réel.', 'Affiner après le rapport'],
                    ['Bonjour {first_name},', 'Si vous vendez, la mise en valeur (photos, mise en scène) influence fortement les visites.', 'Valoriser votre bien'],
                    ['Bonjour {first_name},', 'Un entretien de 20 minutes permet souvent de trancher sur une fourchette réaliste.', '20 minutes pour valider votre fourchette'],
                    ['Bonjour {first_name},', 'Besoin d’aide pour la suite ? Répondez à cet email, je vous propose des créneaux.', 'Je suis disponible'],
                ]),
            ],
            self::TRIGGER_ESTIMATION_TUNNEL_CONTACT => [
                'name' => 'Suite — Tunnel estimation (contact)',
                'description' => 'Demande de contact depuis le tunnel estimation détaillé.',
                'objective' => 'Répondre et qualifier le besoin',
                'persona' => 'Prospect chaud',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre demande de contact. Je reviens vers vous personnellement très vite.', 'J’ai bien reçu votre demande'],
                    ['Bonjour {first_name},', 'Pour gagner du temps, indiquez-moi si votre sujet est plutôt vente, achat ou les deux.', 'Votre priorité : vente ou achat ?'],
                    ['Bonjour {first_name},', 'Les délais utiles : à quelle échéance pensez-vous (3 mois, 6 mois, plus) ?', 'Votre calendrier'],
                    ['Bonjour {first_name},', 'Je peux vous proposer un échange téléphonique ou un rendez-vous sur place selon ce qui vous convient.', 'Choisissez le format d’échange'],
                    ['Bonjour {first_name},', 'Sans nouvelle de votre part, je reste joignable — votre projet à ' . $city . ' mérite un vrai échange humain.', 'Toujours disponible'],
                ]),
            ],
            self::TRIGGER_ESTIMATION_TUNNEL_RDV => [
                'name' => 'Suite — Tunnel estimation (RDV)',
                'description' => 'Demande de rendez-vous depuis le tunnel estimation.',
                'objective' => 'Confirmer le RDV',
                'persona' => 'Prospect très engagé',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre demande de rendez-vous. Je vous confirme les créneaux par retour de message.', 'Votre rendez-vous — étape suivante'],
                    ['Bonjour {first_name},', 'Pour préparer notre rencontre : adresse du bien, type et surface, et vos questions principales.', 'Préparer notre échange'],
                    ['Bonjour {first_name},', 'Si vous préférez visio ou téléphone, dites-le : je m’adapte.', 'Format du rendez-vous'],
                    ['Bonjour {first_name},', 'Pensez à prévoir documents utiles (taxe foncière, plans, PV d’AG récents si copropriété).', 'Documents utiles'],
                    ['Bonjour {first_name},', 'Au plaisir de nous rencontrer — n’hésitez pas à me répondre pour ajuster l’horaire.', 'À très bientôt'],
                ]),
            ],
            self::TRIGGER_PRENDRE_RDV => [
                'name' => 'Suite — Page Prendre rendez-vous',
                'description' => 'Après formulaire « Prendre rendez-vous » (pages dédiées).',
                'objective' => 'Honorer la demande de RDV',
                'persona' => 'Prospect RDV',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre demande de rendez-vous. Je vous propose des créneaux en réponse à ce message.', 'Votre RDV à ' . $city],
                    ['Bonjour {first_name},', 'Pour cadrer notre entretien : vendez-vous, achetez-vous ou les deux ?', 'Cadrage rapide'],
                    ['Bonjour {first_name},', 'Indiquez vos disponibilités (matin / après-midi / semaine type) pour accélérer la planification.', 'Vos disponibilités'],
                    ['Bonjour {first_name},', 'Un entretien de 30 à 45 minutes suffit souvent pour une première stratégie claire.', 'Durée et déroulé'],
                    ['Bonjour {first_name},', 'Je reste à votre écoute pour confirmer la date et le lieu (bureau / visite / visio).', 'Confirmation'],
                ]),
            ],
            self::TRIGGER_GUIDE_OFFERT => [
                'name' => 'Suite — Téléchargement guide',
                'description' => 'Après opt-in sur le guide offert.',
                'objective' => 'Nurturing valeur + prise de contact',
                'persona' => 'Lead contenu',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre intérêt pour le guide. Gardez-le sous la main : les chapitres sur le calendrier et la fixation du prix sont souvent les plus utiles.', 'Votre guide immobilier'],
                    ['Bonjour {first_name},', 'Une question fréquente : faut-il vendre avant d’acheter ? La réponse dépend de votre financement et du marché local.', 'Vendre avant d’acheter ?'],
                    ['Bonjour {first_name},', 'Si un projet concret se précise à ' . $city . ', un échange personnalisé vaut toutes les généralités.', 'Passer du guide à l’action'],
                    ['Bonjour {first_name},', 'Je partage parfois des opportunités ou analyses de quartier — répondez si vous souhaitez les recevoir.', 'Rester informé'],
                    ['Bonjour {first_name},', 'Besoin d’un avis sur votre bien ou votre recherche ? Il suffit de répondre à cet email.', 'Une question précise ?'],
                ]),
            ],
            self::TRIGGER_BIEN_DETAIL => [
                'name' => 'Suite — Fiche bien (contact)',
                'description' => 'Après message depuis une annonce (contenu personnalisé par bien : titres, lien, ref., prix, ville).',
                'objective' => 'Organiser visite ou complément d’info',
                'persona' => 'Acquéreur',
                'emails' => $five([
                    [
                        'Bonjour {first_name},',
                        'Suite à notre accusé de réception, un rappel utile : votre demande porte sur « {bien_titre} » (réf. {bien_reference}) — {bien_type} à {bien_ville}, affiché à {bien_prix}.\n\nLien direct : {bien_url}\n\nJe suis sur ce dossier et reviens vers vous en priorité.',
                        '« {bien_titre} » — suite à votre demande',
                    ],
                    [
                        'Bonjour {first_name},',
                        'Pour l’annonce « {bien_titre} » : souhaitez-vous planifier une visite ou recevoir des précisions (copropriété, charges, travaux) ? Il suffit de répondre à ce message.',
                        'Visite ou détails sur le bien',
                    ],
                    [
                        'Bonjour {first_name},',
                        'Vous visez un achat à {bien_ville} : un accord de principe bancaire aidera souvent à se positionner sur « {bien_titre} ». Si vous voulez un point d’étape, répondez ici.',
                        'Financement et projet sur {bien_ville}',
                    ],
                    [
                        'Bonjour {first_name},',
                        'Si « {bien_titre} » ne colle pas à 100 % à votre recherche, je peux vous proposer d’autres biens proches, sur le même secteur ou le même type de budget.',
                        'D’autres options près de {bien_ville}',
                    ],
                    [
                        'Bonjour {first_name},',
                        'Dernier message de cette mini-série : l’annonce reste ici {bien_url} — à {bien_ville}, je reste joignable pour avancer. Merci pour votre confiance.',
                        'Votre recherche — {bien_ville}',
                    ],
                ]),
            ],
            self::TRIGGER_FINANCEMENT => [
                'name' => 'Suite — Demande financement',
                'description' => 'Après formulaire financement / projet de prêt.',
                'objective' => 'Accompagner le montage du projet',
                'persona' => 'Emprunteur',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour les éléments sur votre projet de financement. Je peux vous orienter sur la cohérence budget / apport / secteur.', 'Votre projet de financement'],
                    ['Bonjour {first_name},', 'Les banques regardent la stabilité des revenus, l’endettement et le reste à vivre — je peux vous aider à préparer le dossier.', 'Préparer un dossier solide'],
                    ['Bonjour {first_name},', 'Comparer plusieurs offres (taux, assurance, frais) fait souvent économiser sur la durée.', 'Comparer les offres'],
                    ['Bonjour {first_name},', 'Une fois le financement cadré, la recherche de bien à ' . $city . ' devient beaucoup plus sereine.', 'Enchaîner avec le bien'],
                    ['Bonjour {first_name},', 'Dites-moi où vous en êtes : je vous aide à prioriser les prochaines étapes.', 'Prochaines étapes'],
                ]),
            ],
            self::TRIGGER_AVIS_VALEUR => [
                'name' => 'Suite — Avis de valeur',
                'description' => 'Après demande d’avis de valeur formalisé.',
                'objective' => 'Livrer une analyse sérieuse et proposer la suite',
                'persona' => 'Propriétaire exigeant',
                'emails' => $five([
                    ['Bonjour {first_name},', 'Merci pour votre demande d’avis de valeur. Je traite chaque dossier avec des comparables récents et le contexte local.', 'Réception de votre demande'],
                    ['Bonjour {first_name},', 'Pour affiner : l’état intérieur, les travaux récents et la luminosité comptent autant que la surface.', 'Les critères qui font varier la valeur'],
                    ['Bonjour {first_name},', 'Si vous envisagez une vente, anticiper les diagnostics et documents de copropriété évite les surprises.', 'Anticiper la vente'],
                    ['Bonjour {first_name},', 'Un entretien ou une visite permet de passer d’une fourchette « marché » à une fourchette « votre bien ».', 'Précision sur votre bien'],
                    ['Bonjour {first_name},', 'Je reste à votre disposition pour la suite : stratégie de prix, calendrier, mise en marché.', 'Stratégie et calendrier'],
                ]),
            ],
        ];
    }
}
