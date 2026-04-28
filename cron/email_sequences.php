<?php
/**
 * Traitement des séquences email automatiques (relances différées).
 * Planifier en cron toutes les 15 à 60 minutes (ex. toutes les 20 min) :
 *   php .../cron/email_sequences.php >> .../logs/cron-email-sequences.log 2>&1
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . '/core/bootstrap.php';

$n = EmailSequenceService::processDueSends(60);
echo gmdate('c') . " email_sequences processDueSends sent={$n}\n";
