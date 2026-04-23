<?php

declare(strict_types=1);

// ============================================================
// PROSPECTION MAILER
// Couche d'abstraction entre le module de prospection
// et le système d'envoi réel.
//
// MAIL_MODE (dans .env) :
//   "test"  → tous les envois redirigés vers EMAIL_TEST_RECIPIENT
//   "log"   → simulation pure, aucun envoi réel, tout est journalisé
//   "smtp"  → envoi réel via MailService (SMTP configuré)
//
// La couche permet de switcher de mode sans toucher au code métier.
// ============================================================

interface EmailTransportInterface
{
    /**
     * @return array{sent:bool, is_test:bool, intended_recipient:string|null, error:string|null}
     */
    public function deliver(string $to, string $subject, string $body): array;

    public function mode(): string;
}

// ----------------------------------------------------------------
// Transport SMTP réel — wrapping MailService
// ----------------------------------------------------------------
class SmtpEmailTransport implements EmailTransportInterface
{
    public function deliver(string $to, string $subject, string $body): array
    {
        // MailService existe déjà dans le projet
        $ok    = false;
        $error = null;
        try {
            require_once ROOT_PATH . '/core/services/MailService.php';
            $ok = \MailService::send($to, $subject, $body);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'sent'               => $ok,
            'is_test'            => false,
            'intended_recipient' => null,
            'error'              => $error,
        ];
    }

    public function mode(): string { return 'smtp'; }
}

// ----------------------------------------------------------------
// Transport TEST — redirige vers adresse de test
// ----------------------------------------------------------------
class TestEmailTransport implements EmailTransportInterface
{
    private string $testRecipient;

    public function __construct(string $testRecipient)
    {
        $this->testRecipient = $testRecipient;
    }

    public function deliver(string $to, string $subject, string $body): array
    {
        $testSubject = '[TEST → ' . $to . '] ' . $subject;

        $ok    = false;
        $error = null;
        try {
            require_once ROOT_PATH . '/core/services/MailService.php';
            $ok = \MailService::send($this->testRecipient, $testSubject, $body);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return [
            'sent'               => $ok,
            'is_test'            => true,
            'intended_recipient' => $to,
            'error'              => $error,
        ];
    }

    public function mode(): string { return 'test'; }
}

// ----------------------------------------------------------------
// Transport LOG — simulation pure, zéro envoi
// ----------------------------------------------------------------
class LogOnlyEmailTransport implements EmailTransportInterface
{
    public function deliver(string $to, string $subject, string $body): array
    {
        // Journalise dans error_log pour trace
        error_log(sprintf('[ProspectionMailer][LOG] To=%s | Subject=%s', $to, $subject));

        return [
            'sent'               => true,    // "réussi" du point de vue de la simulation
            'is_test'            => true,
            'intended_recipient' => $to,
            'error'              => null,
        ];
    }

    public function mode(): string { return 'log'; }
}

// ----------------------------------------------------------------
// Factory — lit MAIL_MODE dans .env et retourne le bon transport
// ----------------------------------------------------------------
class ProspectionMailer
{
    private static ?EmailTransportInterface $instance = null;

    public static function transport(): EmailTransportInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $mode      = strtolower(trim($_ENV['MAIL_MODE'] ?? 'log'));
        $recipient = trim($_ENV['EMAIL_TEST_RECIPIENT'] ?? '');
        $sandbox   = strtolower(trim($_ENV['EMAIL_SANDBOX'] ?? 'true')) !== 'false';

        // Sécurité : en mode smtp, si EMAIL_SANDBOX=true on force le mode test
        if ($mode === 'smtp' && $sandbox) {
            $mode = 'test';
        }

        self::$instance = match ($mode) {
            'smtp' => new SmtpEmailTransport(),
            'test' => new TestEmailTransport($recipient ?: 'test@localhost'),
            default => new LogOnlyEmailTransport(),
        };

        return self::$instance;
    }

    /**
     * Réinitialise l'instance (utile pour les tests unitaires ou la config dynamique)
     */
    public static function reset(?EmailTransportInterface $transport = null): void
    {
        self::$instance = $transport;
    }

    /**
     * Retourne le mode actuel sans instancier
     */
    public static function currentMode(): string
    {
        $mode    = strtolower(trim($_ENV['MAIL_MODE'] ?? 'log'));
        $sandbox = strtolower(trim($_ENV['EMAIL_SANDBOX'] ?? 'true')) !== 'false';
        if ($mode === 'smtp' && $sandbox) {
            return 'test';
        }
        return in_array($mode, ['smtp','test','log'], true) ? $mode : 'log';
    }

    public static function isTestMode(): bool
    {
        return self::currentMode() !== 'smtp';
    }

    public static function testRecipient(): string
    {
        return trim($_ENV['EMAIL_TEST_RECIPIENT'] ?? '');
    }
}
