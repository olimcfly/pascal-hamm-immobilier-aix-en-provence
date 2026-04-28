<?php

declare(strict_types=1);

/**
 * Synchronise les 7 scénarios d’e-mails automatiques prioritaires (1 form_trigger = 1 séquence active).
 * Les délais (delay_days) sur chaque e-mail n > 1 = jours après l’envoi de l’e-mail n-1
 * (voir EmailSequenceService::sendNextEmailForSubscription).
 */
class EmailSequencePriorityService
{
    public const PRIORITY_FORM_TRIGGERS = [
        'estimation-rapport',
        'avis-valeur',
        'estimation-resultat',
        'guide-offert',
        'prendre-rendez-vous',
        'contact',
        'financement',
    ];

    private const LOG_RELATIVE = '/logs/email_sequence_priority_last_sync.json';

    public static function loadPresets(): array
    {
        $path = dirname(__DIR__) . '/config/email_sequence_priority_presets.php';
        if (!is_file($path)) {
            return [];
        }
        $data = require $path;

        return is_array($data) ? $data : [];
    }

    public static function getHealthSummary(): array
    {
        $presets = self::loadPresets();
        $out = [
            'triggers' => [],
            'by_sequence_id' => [],
        ];

        if ($presets === [] || !function_exists('db')) {
            return $out;
        }

        $db = db();
        $placeholders = implode(',', array_fill(0, count(self::PRIORITY_FORM_TRIGGERS), '?'));

        $st = $db->prepare(
            'SELECT id, form_trigger, status, trigger_type, name
             FROM email_sequences
             WHERE trigger_type = ? AND form_trigger IN (' . $placeholders . ')'
        );
        $st->execute(array_merge(['automatic'], self::PRIORITY_FORM_TRIGGERS));
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $byTrigger = [];
        foreach ($rows as $r) {
            $ft = (string) ($r['form_trigger'] ?? '');
            if ($ft === '') {
                continue;
            }
            $byTrigger[$ft] ??= [];
            $byTrigger[$ft][] = $r;
        }

        foreach (self::PRIORITY_FORM_TRIGGERS as $ft) {
            $list = $byTrigger[$ft] ?? [];
            $actives = array_values(array_filter($list, static fn (array $x): bool => ($x['status'] ?? '') === 'active'));
            $expected = isset($presets[$ft]['emails']) ? count($presets[$ft]['emails']) : 0;

            if (count($actives) > 1) {
                $status = 'doublon';
            } elseif (count($actives) === 0) {
                $status = 'manquante';
            } else {
                $id = (int) $actives[0]['id'];
                $countStmt = $db->prepare('SELECT COUNT(*) FROM email_sequence_emails WHERE sequence_id = ?');
                $countStmt->execute([$id]);
                $nEmails = (int) $countStmt->fetchColumn();
                $incomplete = $nEmails < $expected || $nEmails < 1
                    || !self::emailsSeemComplete($db, $id, $expected);
                $status = $incomplete ? 'incomplète' : 'ok';
            }

            $ids = array_map(static fn (array $x): int => (int) $x['id'], $actives);
            $out['triggers'][$ft] = [
                'status' => $status,
                'expected_emails' => $expected,
                'active_count' => count($actives),
                'active_ids' => $ids,
            ];

            foreach ($actives as $a) {
                $sid = (int) $a['id'];
                if ($status === 'doublon') {
                    $out['by_sequence_id'][$sid] = 'doublon';
                } else {
                    $out['by_sequence_id'][$sid] = $status === 'incomplète' ? 'incomplète' : 'ok';
                }
            }
            foreach ($list as $r) {
                $sid = (int) ($r['id'] ?? 0);
                if (isset($out['by_sequence_id'][$sid])) {
                    continue;
                }
                if (($r['status'] ?? '') !== 'active') {
                    $out['by_sequence_id'][$sid] = 'brouillon';
                }
            }
        }

        return $out;
    }

    private static function emailsSeemComplete(PDO $db, int $sequenceId, int $expected): bool
    {
        $st = $db->prepare(
            'SELECT subject, body_html FROM email_sequence_emails
             WHERE sequence_id = ? ORDER BY email_number ASC'
        );
        $st->execute([$sequenceId]);
        $emails = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (count($emails) !== $expected) {
            return false;
        }
        foreach ($emails as $e) {
            if (mb_strlen(trim((string) ($e['subject'] ?? ''))) < 3) {
                return false;
            }
            if (mb_strlen(trim((string) ($e['body_html'] ?? ''))) < 40) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{label:string, class:string}
     */
    public static function badgeForRow(array $seq, array $health): array
    {
        if (($seq['trigger_type'] ?? '') !== 'automatic') {
            return ['label' => '—', 'class' => 'ph-neutral'];
        }
        $ft = (string) ($seq['form_trigger'] ?? '');
        if (!in_array($ft, self::PRIORITY_FORM_TRIGGERS, true)) {
            return ['label' => 'Hors lot', 'class' => 'ph-neutral'];
        }
        $id = (int) ($seq['id'] ?? 0);
        if (isset($health['by_sequence_id'][$id])) {
            $k = $health['by_sequence_id'][$id];

            return self::mapKeyToBadge($k);
        }
        if (($seq['status'] ?? '') === 'active') {
            $t = $health['triggers'][$ft] ?? null;
            if ($t && (int) ($t['active_count'] ?? 0) > 1) {
                return self::mapKeyToBadge('doublon');
            }
        }

        return self::mapKeyToBadge('brouillon');
    }

    /**
     * @return array{label:string, class:string}
     */
    private static function mapKeyToBadge(string $k): array
    {
        return match ($k) {
            'ok' => ['label' => 'OK', 'class' => 'ph-ok'],
            'incomplète' => ['label' => 'Incomplète', 'class' => 'ph-inc'],
            'doublon' => ['label' => 'Doublon', 'class' => 'ph-dup'],
            'manquante' => ['label' => 'Manquante', 'class' => 'ph-miss'],
            'brouillon' => ['label' => 'Inactif', 'class' => 'ph-dim'],
            default => ['label' => '—', 'class' => 'ph-neutral'],
        };
    }

    public static function triggerSummaryLabel(string $formTrigger, array $health): string
    {
        $t = $health['triggers'][$formTrigger] ?? null;
        if ($t === null) {
            return '—';
        }
        $st = (string) ($t['status'] ?? '');
        $map = match ($st) {
            'ok' => 'OK',
            'incomplète' => 'Incomplète',
            'doublon' => 'Doublon',
            'manquante' => 'Manquante',
            default => $st,
        };

        $n = (int) ($t['active_count'] ?? 0);
        $ex = (int) ($t['expected_emails'] ?? 0);

        return $map . ' · ' . $n . ' active(s) / ' . $ex . ' e-mails prévus';
    }

    /**
     * @return array<string, mixed>
     */
    public static function syncPriorityAutomaticSequences(): array
    {
        $report = [
            'at' => date('Y-m-d H:i:s'),
            'automatic_count_before' => 0,
            'created' => [],
            'updated' => [],
            'unchanged' => [],
            'already_ok' => [],
            'duplicates' => [],
            'skipped' => [],
            'errors' => [],
        ];

        if (!function_exists('db')) {
            $report['errors'][] = 'db() indisponible';

            return $report;
        }

        $db = db();
        $stCount = $db->query("SELECT COUNT(*) FROM email_sequences WHERE trigger_type = 'automatic'");
        $report['automatic_count_before'] = (int) $stCount->fetchColumn();

        $presets = self::loadPresets();
        $city = self::resolveDefaultCity();

        foreach (self::PRIORITY_FORM_TRIGGERS as $formTrigger) {
            if (!isset($presets[$formTrigger])) {
                $report['errors'][] = 'Preset manquant : ' . $formTrigger;
                continue;
            }
            $def = $presets[$formTrigger];

            try {
                $st = $db->prepare(
                    'SELECT id, status FROM email_sequences
                     WHERE form_trigger = ? AND trigger_type = ? ORDER BY id ASC'
                );
                $st->execute([$formTrigger, 'automatic']);
                $all = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $actives = array_values(array_filter($all, static fn (array $x): bool => ($x['status'] ?? '') === 'active'));
                $nActive = count($actives);

                if ($nActive > 1) {
                    $ids = array_map(static fn (array $x): int => (int) $x['id'], $actives);
                    $report['duplicates'][] = [
                        'form_trigger' => $formTrigger,
                        'active_ids' => $ids,
                    ];
                    $report['skipped'][] = 'Doublon actif non modifié : ' . $formTrigger;
                    continue;
                }

                if ($nActive === 1) {
                    $id = (int) $actives[0]['id'];
                    if (self::sequenceMatchesPreset($db, $id, $def, $city)) {
                        $report['already_ok'][] = ['form_trigger' => $formTrigger, 'sequence_id' => $id];
                        continue;
                    }
                    self::applyPresetToSequence($db, $id, $def, $city, true);
                    $report['updated'][] = ['form_trigger' => $formTrigger, 'sequence_id' => $id];
                    continue;
                }

                if ($nActive === 0) {
                    $inact = array_values(array_filter($all, static fn (array $x): bool => ($x['status'] ?? '') !== 'active'));
                    if (count($inact) >= 1) {
                        $id = (int) $inact[0]['id'];
                        if (count($inact) > 1) {
                            foreach (array_slice($inact, 1) as $x) {
                                $db->prepare('UPDATE email_sequences SET status = ? WHERE id = ?')
                                    ->execute(['draft', (int) $x['id']]);
                            }
                        }
                        $db->prepare('UPDATE email_sequences SET status = ? WHERE id = ?')
                            ->execute(['active', $id]);
                        self::applyPresetToSequence($db, $id, $def, $city, true);
                        $report['updated'][] = [
                            'form_trigger' => $formTrigger,
                            'sequence_id' => $id,
                            'note' => 'séquence inactive réactivée',
                        ];
                    } else {
                        $id = self::insertNewSequence($db, $formTrigger, $def, $city);
                        $report['created'][] = ['form_trigger' => $formTrigger, 'sequence_id' => $id];
                    }
                }
            } catch (Throwable $e) {
                $report['errors'][] = $formTrigger . ' : ' . $e->getMessage();
            }
        }

        self::writeLastLog($report);

        return $report;
    }

    private static function writeLastLog(array $report): void
    {
        if (!defined('ROOT_PATH') || !is_string(ROOT_PATH)) {
            return;
        }
        $path = rtrim(ROOT_PATH, '/') . self::LOG_RELATIVE;
        $dir = dirname($path);
        if (is_dir($dir) && is_writable($dir)) {
            @file_put_contents(
                $path,
                (string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            );
        }
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

    private static function insertNewSequence(PDO $db, string $formTrigger, array $def, string $city): int
    {
        $st = $db->prepare('
            INSERT INTO email_sequences
            (name, objective, persona, city, description, status, trigger_type, form_trigger)
            VALUES (?, ?, ?, ?, ?, \'active\', \'automatic\', ?)
        ');
        $st->execute([
            (string) $def['name'],
            (string) $def['objective'],
            (string) $def['persona'],
            $city,
            (string) ($def['description'] ?? ''),
            $formTrigger,
        ]);
        $id = (int) $db->lastInsertId();
        self::insertEmails($db, $id, $def, $city);

        return $id;
    }

    private static function applyPresetToSequence(PDO $db, int $sequenceId, array $def, string $city, bool $updateMeta): void
    {
        if ($updateMeta) {
            $st = $db->prepare('
                UPDATE email_sequences
                SET name = ?, objective = ?, persona = ?, city = ?, description = ?
                WHERE id = ?
            ');
            $st->execute([
                (string) $def['name'],
                (string) $def['objective'],
                (string) $def['persona'],
                $city,
                (string) ($def['description'] ?? ''),
                $sequenceId,
            ]);
        }
        $db->prepare('DELETE FROM email_sequence_emails WHERE sequence_id = ?')->execute([$sequenceId]);
        self::insertEmails($db, $sequenceId, $def, $city);
    }

    private static function insertEmails(PDO $db, int $sequenceId, array $def, string $city): void
    {
        $emails = $def['emails'] ?? [];
        if (!is_array($emails)) {
            return;
        }
        $ins = $db->prepare('
            INSERT INTO email_sequence_emails
            (sequence_id, email_number, subject, body_html, delay_days)
            VALUES (?, ?, ?, ?, ?)
        ');
        $num = 0;
        foreach ($emails as $tpl) {
            ++$num;
            if (!is_array($tpl)) {
                continue;
            }
            $subject = self::placeholders((string) ($tpl['subject'] ?? ''), $city);
            $body = self::placeholders((string) ($tpl['body'] ?? ''), $city);
            $delay = $num === 1 ? 0 : (int) ($tpl['delay_after_previous'] ?? 0);
            if ($num > 1 && $delay < 0) {
                $delay = 0;
            }
            $ins->execute([$sequenceId, $num, $subject, $body, $delay]);
        }
    }

    private static function placeholders(string $text, string $city): string
    {
        $text = str_replace('{city}', $city, $text);
        if (class_exists(EmailSequenceService::class)) {
            $advisor = 'Votre conseiller';
            if (function_exists('setting')) {
                $fn = trim((string) setting('advisor_firstname', ''));
                $ln = trim((string) setting('advisor_lastname', ''));
                $n = trim($fn . ' ' . $ln);
                if ($n !== '') {
                    $advisor = $n;
                }
            }
            $text = str_replace('{advisor_name}', $advisor, $text);
        } else {
            $text = str_replace('{advisor_name}', 'Votre conseiller', $text);
        }

        return $text;
    }

    private static function sequenceMatchesPreset(PDO $db, int $sequenceId, array $def, string $city): bool
    {
        $emails = $def['emails'] ?? [];
        if (!is_array($emails)) {
            return false;
        }
        $expectedN = count($emails);
        $st = $db->prepare(
            'SELECT email_number, subject, body_html, delay_days FROM email_sequence_emails
             WHERE sequence_id = ? ORDER BY email_number ASC'
        );
        $st->execute([$sequenceId]);
        $cur = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if (count($cur) !== $expectedN) {
            return false;
        }
        $seq = $db->prepare('SELECT name, objective, persona, description, city FROM email_sequences WHERE id = ?');
        $seq->execute([$sequenceId]);
        $meta = $seq->fetch(PDO::FETCH_ASSOC) ?: [];
        if (trim((string) ($meta['name'] ?? '')) !== trim((string) $def['name'])
            || trim((string) ($meta['objective'] ?? '')) !== trim((string) $def['objective'])
        ) {
            return false;
        }

        foreach ($emails as $i => $tpl) {
            if (!is_array($tpl)) {
                return false;
            }
            $row = $cur[$i] ?? null;
            if (!$row) {
                return false;
            }
            $expSubj = self::placeholders((string) ($tpl['subject'] ?? ''), $city);
            $expBody = self::placeholders((string) ($tpl['body'] ?? ''), $city);
            $num = $i + 1;
            $expDelay = $num === 1 ? 0 : (int) ($tpl['delay_after_previous'] ?? 0);
            if (trim((string) ($row['subject'] ?? '')) !== trim($expSubj)) {
                return false;
            }
            if (trim((string) ($row['body_html'] ?? '')) !== trim($expBody)) {
                return false;
            }
            if ((int) ($row['delay_days'] ?? -1) !== $expDelay) {
                return false;
            }
        }

        return true;
    }
}
