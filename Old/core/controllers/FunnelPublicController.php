<?php

require_once MODULES_PATH . '/funnels/repositories/FunnelRepository.php';
require_once MODULES_PATH . '/funnels/services/FunnelService.php';
require_once MODULES_PATH . '/funnels/services/SequenceCrmService.php';
require_once ROOT_PATH . '/core/services/LeadService.php';

class FunnelPublicController
{
    private PDO $db;
    private FunnelRepository $repo;
    private FunnelService $service;

    public function __construct()
    {
        $this->db      = \Database::getInstance();
        $this->repo    = new FunnelRepository($this->db);
        $this->service = new FunnelService($this->db);
    }

    /**
     * Affiche la landing page publique.
     * GET /lp/{slug}
     */
    public function show(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            include PUBLIC_PATH . '/pages/core/404.php';
            return;
        }

        // Tracking vue
        $this->trackEvent($funnel['id'], 'view');

        // Décoder JSON
        $funnel['thankyou_config'] = json_decode($funnel['thankyou_config'] ?? '{}', true) ?? [];
        $funnel['faq_json']        = json_decode($funnel['faq_json'] ?? '[]', true) ?? [];

        // Données settings conseiller
        $advisorName = defined('ADVISOR_NAME') ? ADVISOR_NAME : setting('advisor_name', '');
        $appUrl      = defined('APP_URL') ? APP_URL : '';

        // Template LP (sans menu)
        $templateFile = PUBLIC_PATH . '/templates/lp/' . $funnel['template_id'] . '.php';
        if (!file_exists($templateFile)) {
            $templateFile = PUBLIC_PATH . '/templates/lp/guide_vendeur_v1.php';
        }

        require PUBLIC_PATH . '/templates/lp/layout_lp.php';
    }

    /**
     * Traite la soumission du formulaire LP.
     * POST /lp/{slug}/submit
     */
    public function submit(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Page introuvable']);
            return;
        }

        // Validation basique
        $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $firstName= trim(strip_tags($_POST['first_name'] ?? ''));
        $lastName = trim(strip_tags($_POST['last_name'] ?? ''));
        $phone    = trim(strip_tags($_POST['phone'] ?? ''));
        $message  = trim(strip_tags($_POST['message'] ?? ''));
        $consent  = !empty($_POST['consent']);

        if (!$email || !$firstName) {
            $this->redirectWithError($slug, 'Prénom et email requis.');
            return;
        }

        if (!$consent) {
            $this->redirectWithError($slug, 'Vous devez accepter la politique de confidentialité.');
            return;
        }

        // Honeypot anti-bot
        if (!empty($_POST['website'])) {
            $this->redirectToThankyou($slug);
            return;
        }

        // Récupérer UTMs depuis POST ou cookie
        $utmSource   = $this->getUtm('utm_source',   $funnel['utm_source'] ?? '');
        $utmMedium   = $this->getUtm('utm_medium',   $funnel['utm_medium'] ?? '');
        $utmCampaign = $this->getUtm('utm_campaign', $funnel['utm_campaign'] ?? '');
        $utmContent  = $this->getUtm('utm_content',  $funnel['utm_content'] ?? '');
        $utmKeyword  = $this->getUtm('utm_term',     $funnel['keyword'] ?? '');

        // Créer le lead
        $leadId = LeadService::capture([
            'source_type'  => LeadService::SOURCE_RESSOURCE,
            'funnel_id'    => $funnel['id'],
            'first_name'   => $firstName,
            'last_name'    => $lastName,
            'email'        => $email,
            'phone'        => $phone,
            'notes'        => $message,
            'intent'       => $funnel['persona'] ?? 'vendeur',
            'consent'      => 1,
            'pipeline'     => 'new',
            'stage'        => 'lead',
            'utm_source'   => $utmSource,
            'utm_medium'   => $utmMedium,
            'utm_campaign' => $utmCampaign,
            'utm_content'  => $utmContent,
            'utm_keyword'  => $utmKeyword,
        ]);

        // Tracking soumission
        $this->trackEvent($funnel['id'], 'submit');

        // Notifier l'agent immobilier par email
        $this->sendInternalNotification($funnel, $firstName, $lastName, $email, $phone, $message);

        // Inscrire dans la séquence si configurée
        if (!empty($funnel['sequence_id']) && $leadId) {
            $seqService = new SequenceCrmService($this->db);

            // Construire l'URL de téléchargement si ressource liée
            $ressourceUrl = '';
            if (!empty($funnel['ressource_id'])) {
                $ressourceUrl = $this->generateDownloadToken($leadId, (int) $funnel['ressource_id']);
            }

            $seqService->enroll($leadId, (int) $funnel['sequence_id'], [
                '[RESSOURCE_URL]' => $ressourceUrl,
                '[CTA_LABEL]'     => $funnel['cta_label'] ?? 'Télécharger',
            ]);
        }

        $this->redirectToThankyou($slug);
    }

    /**
     * Affiche la thank you page.
     * GET /lp/{slug}/merci
     */
    public function thankyou(array $params): void
    {
        $slug   = $params['slug'] ?? '';
        $funnel = $this->repo->findBySlug($slug);

        if (!$funnel) {
            http_response_code(404);
            return;
        }

        $funnel['thankyou_config'] = json_decode($funnel['thankyou_config'] ?? '{}', true) ?? [];
        $type = $funnel['thankyou_type'] ?? 'telechargement';

        $tplFile = PUBLIC_PATH . '/templates/lp/thankyou/' . $type . '.php';
        if (!file_exists($tplFile)) {
            $tplFile = PUBLIC_PATH . '/templates/lp/thankyou/telechargement.php';
        }

        require PUBLIC_PATH . '/templates/lp/layout_lp.php';
    }

    /**
     * Téléchargement sécurisé d'une ressource.
     * GET /ressource/{token}
     */
    public function download(array $params): void
    {
        $token = $params['token'] ?? '';

        [$leadId, $ressourceId] = $this->decodeDownloadToken($token);

        if (!$leadId || !$ressourceId) {
            http_response_code(403);
            echo 'Lien invalide ou expiré.';
            return;
        }

        $stmt = $this->db->prepare('SELECT * FROM ressources WHERE id = :id AND status = "published"');
        $stmt->execute([':id' => $ressourceId]);
        $ressource = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ressource || empty($ressource['file_path'])) {
            http_response_code(404);
            echo 'Ressource introuvable.';
            return;
        }

        $filePath = ROOT_PATH . '/' . ltrim($ressource['file_path'], '/');
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'Fichier introuvable.';
            return;
        }

        // Incrémenter compteur téléchargements
        $this->db->prepare('UPDATE ressources SET downloads = downloads + 1 WHERE id = :id')
                 ->execute([':id' => $ressourceId]);

        $this->trackEventById($ressourceId, 'download');

        $filename = basename($filePath);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }

    // ---- Privé ----

    private function trackEvent(int $funnelId, string $type): void
    {
        try {
            $sessionId = session_id() ?: bin2hex(random_bytes(16));
            $ipHash    = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

            $stmt = $this->db->prepare('
                INSERT INTO funnel_events
                    (funnel_id, event_type, session_id, ip_hash, utm_source, utm_medium, utm_campaign, referrer, user_agent)
                VALUES
                    (:fid, :type, :sid, :ip, :src, :med, :cmp, :ref, :ua)
            ');
            $stmt->execute([
                ':fid'  => $funnelId,
                ':type' => $type,
                ':sid'  => $sessionId,
                ':ip'   => $ipHash,
                ':src'  => $this->getUtm('utm_source', ''),
                ':med'  => $this->getUtm('utm_medium', ''),
                ':cmp'  => $this->getUtm('utm_campaign', ''),
                ':ref'  => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
                ':ua'   => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
            ]);
        } catch (\Exception $e) {
            error_log('[FunnelTracking] ' . $e->getMessage());
        }
    }

    private function trackEventById(int $ressourceId, string $type): void
    {
        // Retrouver le funnel lié à la ressource
        $stmt = $this->db->prepare('SELECT id FROM funnels WHERE ressource_id = :rid LIMIT 1');
        $stmt->execute([':rid' => $ressourceId]);
        $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($funnel) {
            $this->trackEvent((int) $funnel['id'], $type);
        }
    }

    private function getUtm(string $key, string $fallback = ''): string
    {
        return trim(
            $_GET[$key]
            ?? $_COOKIE['utm_' . str_replace('utm_', '', $key)]
            ?? $_POST[$key]
            ?? $fallback
        );
    }

    private function generateDownloadToken(int $leadId, int $ressourceId): string
    {
        $expiry  = time() + (72 * 3600);
        $payload = base64_encode("$leadId:$ressourceId:$expiry");
        $sig     = hash_hmac('sha256', $payload, defined('APP_KEY') ? APP_KEY : 'secret');
        return $payload . '.' . substr($sig, 0, 16);
    }

    private function decodeDownloadToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) return [0, 0];

        [$payload, $sig] = $parts;
        $expectedSig = substr(hash_hmac('sha256', $payload, defined('APP_KEY') ? APP_KEY : 'secret'), 0, 16);

        if (!hash_equals($expectedSig, $sig)) return [0, 0];

        $decoded = base64_decode($payload);
        [$leadId, $ressourceId, $expiry] = explode(':', $decoded) + [0, 0, 0];

        if (time() > (int) $expiry) return [0, 0];

        return [(int) $leadId, (int) $ressourceId];
    }

    private function redirectToThankyou(string $slug): void
    {
        header('Location: ' . rtrim(APP_URL, '/') . '/lp/' . $slug . '/merci');
        exit;
    }

    private function redirectWithError(string $slug, string $message): void
    {
        $_SESSION['funnel_error'] = $message;
        header('Location: ' . rtrim(APP_URL, '/') . '/lp/' . $slug . '?error=1');
        exit;
    }

    /**
     * Envoie une notification email interne au conseiller lors d'un nouveau lead.
     */
    private function sendInternalNotification(array $funnel, string $firstName, string $lastName, string $email, string $phone, string $message): void
    {
        if (!class_exists('MailService')) {
            require_once ROOT_PATH . '/core/services/MailService.php';
        }

        $notifEmail = $_ENV['NOTIF_LEAD_EMAIL'] ?? $_ENV['NOTIF_EMAIL'] ?? '';
        if (!$notifEmail || !filter_var($notifEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $fullName    = trim("$firstName $lastName") ?: $email;
        $landingName = $funnel['name'] ?? ($funnel['h1'] ?? $funnel['slug']);
        $date        = date('d/m/Y à H:i');
        $appUrl      = rtrim(defined('APP_URL') ? APP_URL : '', '/');
        $adminUrl    = $appUrl . '/admin?module=funnels';

        $subject = "🏠 Nouveau lead — {$fullName} via « {$landingName} »";

        $msgRow = $message
            ? "<tr><td style='padding:6px 0;color:#64748b;font-size:.88rem;vertical-align:top'>Message</td><td style='padding:6px 0'>" . htmlspecialchars($message) . "</td></tr>"
            : '';

        $html = "
        <div style='font-family:sans-serif;max-width:580px;margin:0 auto;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden'>
          <div style='background:#1e3a5f;padding:20px 24px'>
            <h2 style='color:#fff;margin:0;font-size:1.15rem'>🏠 Nouveau lead immobilier</h2>
            <p style='color:#93c5fd;margin:4px 0 0;font-size:.85rem'>{$date}</p>
          </div>
          <div style='padding:24px;background:#f8fafc'>
            <table style='width:100%;border-collapse:collapse'>
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem;width:120px'>Prénom</td><td style='padding:6px 0;font-weight:600'>" . htmlspecialchars($firstName) . "</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem'>Nom</td><td style='padding:6px 0;font-weight:600'>" . htmlspecialchars($lastName) . "</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem'>Email</td><td style='padding:6px 0'><a href='mailto:" . htmlspecialchars($email) . "' style='color:#1d4ed8'>" . htmlspecialchars($email) . "</a></td></tr>
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem'>Téléphone</td><td style='padding:6px 0'>" . (htmlspecialchars($phone) ?: '<em style=\"color:#94a3b8\">non renseigné</em>') . "</td></tr>
              {$msgRow}
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem'>Page</td><td style='padding:6px 0'>" . htmlspecialchars($landingName) . "</td></tr>
              <tr><td style='padding:6px 0;color:#64748b;font-size:.88rem'>Slug</td><td style='padding:6px 0'><code style='background:#f1f5f9;padding:2px 6px;border-radius:4px'>/lp/" . htmlspecialchars($funnel['slug']) . "</code></td></tr>
            </table>
            <div style='margin-top:20px'>
              <a href='{$adminUrl}' style='display:inline-block;background:#1e3a5f;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-size:.9rem;font-weight:600'>Voir dans l'admin →</a>
            </div>
          </div>
        </div>";

        $text = "Nouveau lead reçu le {$date}\n\n"
              . "Prénom  : {$firstName}\n"
              . "Nom     : {$lastName}\n"
              . "Email   : {$email}\n"
              . "Tél     : " . ($phone ?: 'non renseigné') . "\n"
              . ($message ? "Message : {$message}\n" : '')
              . "\nPage    : {$landingName}\n"
              . "Slug    : /lp/{$funnel['slug']}\n"
              . "Admin   : {$adminUrl}";

        try {
            MailService::send($notifEmail, $subject, $text, $html);
        } catch (\Throwable $e) {
            error_log('[FunnelNotif] Notification email failed: ' . $e->getMessage());
        }
    }
}
