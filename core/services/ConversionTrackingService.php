<?php

/**
 * Service de tracking des conversions
 * Enregistre tous les types de conversions sans besoin d'email/contact
 */
class ConversionTrackingService
{
    private static bool $tablesReady = false;
    private static bool $tablesFailed = false;

    public const TYPE_ESTIMATION_SIMPLE = 'estimation_gratuite_simple';
    public const TYPE_RAPPORT_DOWNLOAD = 'rapport_telechargement';
    public const TYPE_RDV_DEMANDE = 'rdv_demande';
    public const TYPE_CONTACT_FORM = 'contact_formulaire';
    public const TYPE_GUIDE_GRATUIT = 'guide_gratuit_telechargement';
    public const TYPE_GUIDE_PAYANT = 'guide_payant_telechargement';

    /**
     * Crée les tables si absentes (migration 027 non appliquée sur l’hébergement).
     */
    private static function tableExists(\PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
        );
        $stmt->execute([$table]);

        return (bool) $stmt->fetch();
    }

    private static function ensureTables(): bool
    {
        if (self::$tablesFailed) {
            return false;
        }
        if (self::$tablesReady) {
            return true;
        }

        try {
            $pdo = db();
            // LONGTEXT + contrôle JSON en appli : meilleure compatibilité que type SQL JSON
            $sqlMain = 'CREATE TABLE IF NOT EXISTS conversion_tracking (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                conversion_type VARCHAR(60) NOT NULL,
                description VARCHAR(255) NULL,
                email VARCHAR(255) NULL,
                phone VARCHAR(40) NULL,
                first_name VARCHAR(100) NULL,
                metadata_json LONGTEXT NULL,
                source_page VARCHAR(255) NULL,
                user_agent VARCHAR(500) NULL,
                ip_address VARCHAR(45) NULL,
                session_id VARCHAR(100) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_conversion_type (conversion_type),
                KEY idx_created_at (created_at),
                KEY idx_email (email),
                KEY idx_source_page (source_page)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

            if ($pdo->exec($sqlMain) === false) {
                throw new \RuntimeException('CREATE conversion_tracking a échoué (exec false).');
            }

            $sqlDaily = 'CREATE TABLE IF NOT EXISTS conversion_stats_daily (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                conversion_type VARCHAR(60) NOT NULL,
                date_day DATE NOT NULL,
                total_count INT UNSIGNED DEFAULT 0,
                with_email_count INT UNSIGNED DEFAULT 0,
                with_phone_count INT UNSIGNED DEFAULT 0,
                UNIQUE KEY uniq_type_day (conversion_type, date_day),
                KEY idx_date (date_day)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

            if ($pdo->exec($sqlDaily) === false) {
                throw new \RuntimeException('CREATE conversion_stats_daily a échoué (exec false).');
            }

            if (!self::tableExists($pdo, 'conversion_tracking')) {
                throw new \RuntimeException('Table conversion_tracking absente après CREATE.');
            }

            self::$tablesReady = true;

            return true;
        } catch (Throwable $e) {
            self::$tablesFailed = true;
            self::$tablesReady = false;
            error_log('ConversionTrackingService::ensureTables — ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Enregistre une conversion
     */
    public static function track(
        string $conversionType,
        ?string $email = null,
        ?string $phone = null,
        ?string $firstName = null,
        ?array $metadata = null,
        ?string $description = null
    ): int {
        if (!self::ensureTables()) {
            return 0;
        }

        try {
            $stmt = db()->prepare('INSERT INTO conversion_tracking
            (conversion_type, email, phone, first_name, metadata_json, description, source_page, user_agent, ip_address, session_id, created_at)
            VALUES
            (:conversion_type, :email, :phone, :first_name, :metadata_json, :description, :source_page, :user_agent, :ip_address, :session_id, NOW())');

            $stmt->execute([
                ':conversion_type' => $conversionType,
                ':email' => !empty($email) ? trim($email) : null,
                ':phone' => !empty($phone) ? trim($phone) : null,
                ':first_name' => !empty($firstName) ? trim($firstName) : null,
                ':metadata_json' => !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
                ':description' => !empty($description) ? trim($description) : null,
                ':source_page' => $_SERVER['REQUEST_URI'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                ':ip_address' => self::getClientIp(),
                ':session_id' => session_id() ?? null,
            ]);

            return (int)db()->lastInsertId();
        } catch (PDOException $e) {
            error_log('ConversionTrackingService::track — ' . $e->getMessage());
            self::$tablesReady = false;
            if (str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), "doesn't exist")) {
                self::$tablesFailed = false;
            }

            return 0;
        }
    }

    /**
     * Récupère les statistiques des conversions
     */
    public static function getStats(
        ?string $conversionType = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        if (!self::ensureTables()) {
            return [];
        }

        $where = [];
        $params = [];

        if (!empty($conversionType)) {
            $where[] = 'conversion_type = :conversion_type';
            $params[':conversion_type'] = $conversionType;
        }

        if (!empty($startDate)) {
            $where[] = 'DATE(created_at) >= :start_date';
            $params[':start_date'] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = 'DATE(created_at) <= :end_date';
            $params[':end_date'] = $endDate;
        }

        $sql = 'SELECT
            conversion_type,
            COUNT(*) as total_count,
            COUNT(CASE WHEN email IS NOT NULL THEN 1 END) as with_email_count,
            COUNT(CASE WHEN phone IS NOT NULL THEN 1 END) as with_phone_count,
            DATE(created_at) as date_day
            FROM conversion_tracking';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY conversion_type, DATE(created_at)
                 ORDER BY date_day DESC';

        try {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ConversionTrackingService::getStats — ' . $e->getMessage());
            self::$tablesReady = false;
            if (str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), "doesn't exist")) {
                self::$tablesFailed = false;
            }

            return [];
        }
    }

    /**
     * Récupère les totaux par type de conversion
     */
    public static function getTotalsByType(): array
    {
        try {
            if (!self::ensureTables()) {
                return [];
            }

            $stmt = db()->prepare('SELECT
                conversion_type,
                COUNT(*) as total_count,
                COUNT(CASE WHEN email IS NOT NULL THEN 1 END) as with_email_count,
                MAX(created_at) as last_conversion
                FROM conversion_tracking
                GROUP BY conversion_type
                ORDER BY total_count DESC');

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ConversionTrackingService::getTotalsByType — ' . $e->getMessage());
            self::$tablesReady = false;
            if (str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), "doesn't exist")) {
                self::$tablesFailed = false;
            }

            return [];
        }
    }

    /**
     * Récupère l'IP du client
     */
    private static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return (string)$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return (string)(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return (string)$_SERVER['REMOTE_ADDR'];
        }
        return '0.0.0.0';
    }

    /**
     * Récupère les conversions récentes
     */
    public static function getRecent(?string $conversionType = null, int $limit = 50): array
    {
        try {
            if (!self::ensureTables()) {
                return [];
            }

            $sql = 'SELECT * FROM conversion_tracking';

            if (!empty($conversionType)) {
                $sql .= ' WHERE conversion_type = ?';
                $stmt = db()->prepare($sql . ' ORDER BY created_at DESC LIMIT ?');
                $stmt->execute([$conversionType, $limit]);
            } else {
                $stmt = db()->prepare($sql . ' ORDER BY created_at DESC LIMIT ?');
                $stmt->execute([$limit]);
            }

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('ConversionTrackingService::getRecent — ' . $e->getMessage());
            self::$tablesReady = false;
            if (str_contains($e->getMessage(), '1146') || str_contains($e->getMessage(), "doesn't exist")) {
                self::$tablesFailed = false;
            }

            return [];
        }
    }
}
