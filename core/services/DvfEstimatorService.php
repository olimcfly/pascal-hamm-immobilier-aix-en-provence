<?php

class DvfEstimatorService
{
    private static bool $tableReady = false;

    public static function sourceMode(): string
    {
        if (!function_exists('setting')) {
            return 'file';
        }

        $mode = strtolower(trim((string) setting('estimation_dvf_source_mode', 'file')));
        return in_array($mode, ['file', 'api'], true) ? $mode : 'file';
    }

    public static function sourceConfiguration(): array
    {
        $get = static function (string $key, mixed $default = ''): mixed {
            if (!function_exists('setting')) {
                return $default;
            }

            return setting($key, $default);
        };

        return [
            'source_mode' => self::sourceMode(),
            'api_provider' => trim((string) $get('estimation_dvf_api_provider', 'DVF API')),
            'api_endpoint' => trim((string) $get('estimation_dvf_api_endpoint', '')),
            'api_key' => trim((string) $get('estimation_dvf_api_key', '')),
            'api_trial_days' => max(0, (int) $get('estimation_dvf_api_trial_days', 30)),
            'api_trial_note' => trim((string) $get('estimation_dvf_api_trial_note', '')),
            'home_title' => trim((string) $get('estimation_home_title', 'Estimation gratuite de votre bien immobilier')),
            'home_subtitle' => trim((string) $get('estimation_home_subtitle', 'Basée sur les ventes réelles DVF (13) et le Pays d’Aix · Instantané · Sans inscription')),
            'home_disclaimer' => trim((string) $get('estimation_home_disclaimer', 'Cette estimation est indicative et calculée à partir des ventes réelles DVF disponibles.')),
            'home_cta_label' => trim((string) $get('estimation_home_cta_label', 'Obtenir mon estimation gratuite')),
            'home_hints' => trim((string) $get('estimation_home_hints', 'Résultat instantané · Sans inscription')),
            'result_title' => trim((string) $get('estimation_result_title', 'Votre estimation indicative')),
            'result_intro' => trim((string) $get('estimation_result_intro', 'Cette estimation est indicative et non contractuelle.')),
            'result_disclaimer' => trim((string) $get('estimation_result_disclaimer', 'Elle est calculée à partir des ventes DVF les plus proches disponibles et doit être confirmée par une analyse locale.')),
            'result_heading' => trim((string) $get('estimation_result_heading', 'Obtenir une estimation précise')),
            'result_primary_cta_label' => trim((string) $get('estimation_result_primary_cta_label', 'Prendre rendez-vous avec Pascal Hamm')),
            'result_primary_cta_url' => trim((string) $get('estimation_result_primary_cta_url', '/prendre-rendez-vous')),
            'result_secondary_cta_label' => trim((string) $get('estimation_result_secondary_cta_label', 'Demander une estimation précise')),
            'result_secondary_cta_url' => trim((string) $get('estimation_result_secondary_cta_url', '/estimation-gratuite')),
        ];
    }

    public static function sourceInfo(): array
    {
        $csvPath = self::dvfCsvPath();
        return [
            'mode' => self::sourceMode(),
            'csv_path' => $csvPath,
            'csv_exists' => is_file($csvPath),
            'csv_readable' => is_readable($csvPath),
            'csv_size' => is_file($csvPath) ? (int) filesize($csvPath) : 0,
            'csv_mtime' => is_file($csvPath) ? date('Y-m-d H:i', (int) filemtime($csvPath)) : null,
            'cache_files' => self::countCacheFiles(),
        ];
    }

    public static function clearCsvCache(): int
    {
        $dir = self::dvfCacheDir();
        if (!is_dir($dir)) {
            return 0;
        }

        $deleted = 0;
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            if (@unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function replaceActiveCsvUpload(array $file): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'Fichier CSV invalide.', 'code' => 'upload_error'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return ['ok' => false, 'message' => 'Upload non reconnu.', 'code' => 'invalid_upload'];
        }

        $original = strtolower((string) ($file['name'] ?? 'dvf.csv'));
        if (!str_ends_with($original, '.csv')) {
            return ['ok' => false, 'message' => 'Le fichier doit être un CSV.', 'code' => 'invalid_format'];
        }

        $target = self::dvfCsvPath();
        $targetDir = dirname($target);
        if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return ['ok' => false, 'message' => 'Impossible de créer le dossier DVF.', 'code' => 'mkdir_failed'];
        }

        $backup = $target . '.bak.' . date('Ymd_His');
        if (is_file($target)) {
            @copy($target, $backup);
        }

        if (!@move_uploaded_file($tmpPath, $target)) {
            return ['ok' => false, 'message' => 'Impossible de remplacer le fichier DVF.', 'code' => 'move_failed'];
        }

        $deleted = self::clearCsvCache();

        return [
            'ok' => true,
            'message' => 'Fichier DVF remplacé avec succès.',
            'backup' => is_file($backup) ? $backup : null,
            'cache_cleared' => $deleted,
            'path' => $target,
        ];
    }

    private static function storageRoot(): string
    {
        if (defined('STORAGE_PATH') && STORAGE_PATH !== '') {
            return rtrim((string) STORAGE_PATH, '/');
        }

        return dirname(__DIR__, 2) . '/storage';
    }

    private static function dvfCsvPath(): string
    {
        return self::storageRoot() . '/dvf/dvf.csv';
    }

    private static function dvfCacheDir(): string
    {
        return self::storageRoot() . '/cache/dvf';
    }

    private static function ensureCacheDir(): void
    {
        $dir = self::dvfCacheDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    private static function countCacheFiles(): int
    {
        $dir = self::dvfCacheDir();
        if (!is_dir($dir)) {
            return 0;
        }

        $files = glob($dir . '/*.json') ?: [];
        return count($files);
    }

    private static function normalizeKey(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii === false || $ascii === '') {
            $ascii = $value;
        }

        $ascii = strtolower((string) $ascii);
        $ascii = preg_replace('/[^a-z0-9]+/', '-', $ascii) ?: '';
        $ascii = trim($ascii, '-');

        return $ascii !== '' ? $ascii : sha1($value);
    }

    private static function parseLocalite(string $localite): array
    {
        $localite = trim(preg_replace('/\s+/u', ' ', $localite) ?: '');
        $postal = '';
        if (preg_match('/\b(\d{5})\b/', $localite, $m)) {
            $postal = $m[1];
        }

        $city = trim((string) preg_replace('/\b\d{5}\b/u', '', $localite));
        $city = trim($city, " \t\n\r\0\x0B,;-");

        return [
            'city' => $city,
            'postal_code' => $postal,
        ];
    }

    private static function hasDatabaseRows(): bool
    {
        try {
            return (int) (db()->query('SELECT COUNT(*) FROM dvf_transactions')->fetchColumn() ?: 0) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function buildEstimateFromRows(array $rows, float $surface, string $source): array
    {
        $prices = [];
        foreach ($rows as $row) {
            $pm2 = (float) ($row['price_m2'] ?? 0);
            if ($pm2 > 100 && $pm2 < 50000) {
                $prices[] = $pm2;
            }
        }

        if (count($prices) < 8) {
            return [
                'ok' => false,
                'reason' => 'not_enough_comparables',
                'status' => 'insufficient_data',
                'comparables_count' => count($prices),
                'message' => 'Pas assez de ventes comparables récentes pour une estimation fiable.',
                'source' => $source,
                'comparables' => [],
            ];
        }

        sort($prices);
        $q1 = self::percentile($prices, 25);
        $q2 = self::percentile($prices, 50);
        $q3 = self::percentile($prices, 75);
        $iqr = max(0.0, $q3 - $q1);

        $lowFence = max(100.0, $q1 - (1.5 * $iqr));
        $highFence = $q3 + (1.5 * $iqr);

        $clean = array_values(array_filter($rows, static function (array $row) use ($lowFence, $highFence): bool {
            $v = (float) ($row['price_m2'] ?? 0);
            return $v >= $lowFence && $v <= $highFence;
        }));

        $cleanPrices = [];
        foreach ($clean as $row) {
            $pm2 = (float) ($row['price_m2'] ?? 0);
            if ($pm2 > 100 && $pm2 < 50000) {
                $cleanPrices[] = $pm2;
            }
        }

        if (count($cleanPrices) < 8) {
            return [
                'ok' => false,
                'reason' => 'dispersion_too_high',
                'status' => 'insufficient_data',
                'comparables_count' => count($cleanPrices),
                'message' => 'Dispersion trop élevée, estimation instantanée bloquée.',
                'source' => $source,
                'comparables' => [],
            ];
        }

        sort($cleanPrices);
        $p35 = self::percentile($cleanPrices, 35);
        $p50 = self::percentile($cleanPrices, 50);
        $p65 = self::percentile($cleanPrices, 65);

        $confidenceScore = self::computeConfidenceScore(count($cleanPrices), $iqr, $p50);
        $confidenceLevel = $confidenceScore >= 80 ? 'élevée' : ($confidenceScore >= 60 ? 'moyenne' : 'faible');

        usort($clean, static function (array $a, array $b) use ($surface): int {
            $sa = (float) ($a['surface'] ?? 0);
            $sb = (float) ($b['surface'] ?? 0);
            $da = abs($sa - $surface);
            $db = abs($sb - $surface);
            if ($da === $db) {
                return strcmp((string) ($b['mutation_date'] ?? ''), (string) ($a['mutation_date'] ?? ''));
            }

            return $da <=> $db;
        });

        $comparables = array_slice(array_map(static function (array $row): array {
            return [
                'address_label' => (string) ($row['address_label'] ?? ''),
                'surface' => (float) ($row['surface'] ?? 0),
                'value_amount' => (float) ($row['value_amount'] ?? 0),
                'price_m2' => (float) ($row['price_m2'] ?? 0),
                'mutation_date' => (string) ($row['mutation_date'] ?? ''),
                'city' => (string) ($row['city'] ?? ''),
                'postal_code' => (string) ($row['postal_code'] ?? ''),
            ];
        }, $clean), 0, 5);

        return [
            'ok' => true,
            'status' => 'ok',
            'estimate_low' => round($p35 * $surface),
            'estimate_median' => round($p50 * $surface),
            'estimate_high' => round($p65 * $surface),
            'price_m2_median' => round($p50, 2),
            'comparables_count' => count($cleanPrices),
            'confidence_score' => $confidenceScore,
            'confidence_level' => $confidenceLevel,
            'message' => 'Estimation indicative, à confirmer lors d’un rendez-vous conseiller.',
            'source' => $source,
            'comparables' => $comparables,
        ];
    }

    private static function cacheKey(array $input, string $type): string
    {
        $location = trim((string) ($input['city'] ?? '')) . '|' . trim((string) ($input['postal_code'] ?? ''));
        return sha1($location . '|' . $type);
    }

    private static function csvCachePath(array $input, string $type): string
    {
        self::ensureCacheDir();
        return self::dvfCacheDir() . '/' . self::cacheKey($input, $type) . '.json';
    }

    private static function loadCsvCache(array $input, string $type): ?array
    {
        $path = self::csvCachePath($input, $type);
        if (!is_file($path)) {
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false || $json === '') {
            return null;
        }

        $payload = json_decode($json, true);
        if (!is_array($payload) || !isset($payload['rows']) || !is_array($payload['rows'])) {
            return null;
        }

        return $payload;
    }

    private static function saveCsvCache(array $input, string $type, array $rows): void
    {
        self::ensureCacheDir();
        $path = self::csvCachePath($input, $type);
        $payload = [
            'generated_at' => date('c'),
            'input' => $input,
            'type' => $type,
            'rows' => $rows,
            'truncated' => false,
        ];
        @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private static function csvMatchesLocation(array $row, array $input): bool
    {
        $cityFilter = self::normalizeText((string) ($input['city'] ?? ''));
        $postalFilter = trim((string) ($input['postal_code'] ?? ''));
        $rowCity = self::normalizeText((string) ($row['nom_commune'] ?? ''));
        $rowPostal = trim((string) ($row['code_postal'] ?? ''));

        if ($cityFilter !== '' && $rowCity !== $cityFilter) {
            return false;
        }

        if ($postalFilter !== '') {
            if (!str_starts_with($rowPostal, $postalFilter)) {
                return false;
            }
        }

        return $cityFilter !== '' || $postalFilter !== '';
    }

    private static function normalizeText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?: '';

        return trim(preg_replace('/\s+/', ' ', $value) ?: '');
    }

    private static function buildCsvCache(array $input, string $type): array
    {
        $path = self::dvfCsvPath();
        if (!is_file($path) || !is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle, 0, ',', '"', '');
        if (!$header) {
            fclose($handle);
            return [];
        }

        $map = [];
        foreach ($header as $idx => $name) {
            $map[strtolower(trim((string) $name))] = $idx;
        }

        $get = static function (array $row, string $key) use ($map): string {
            $idx = $map[strtolower($key)] ?? null;
            if ($idx === null) {
                return '';
            }

            return trim((string) ($row[$idx] ?? ''));
        };

        $rows = [];
        $wantedType = self::normalizeType((string) ($input['property_type'] ?? ''));
        $targetType = match ($wantedType) {
            'villa', 'immeuble' => 'maison',
            default => $wantedType,
        };

        $monthsCutoff = new DateTimeImmutable('-48 months');
        $startedAt = microtime(true);
        $timeBudgetSeconds = 8.0;
        $targetRows = 120;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count($rows) >= $targetRows) {
                break;
            }

            if ((microtime(true) - $startedAt) >= $timeBudgetSeconds && count($rows) >= 20) {
                break;
            }

            $typeLocal = strtolower($get($row, 'type_local'));
            if ($typeLocal === '') {
                continue;
            }

            if ($targetType !== '' && $typeLocal !== $targetType) {
                continue;
            }

            if (!self::csvMatchesLocation([
                'nom_commune' => $get($row, 'nom_commune'),
                'code_postal' => $get($row, 'code_postal'),
            ], $input)) {
                continue;
            }

            $surface = (float) str_replace(',', '.', $get($row, 'surface_reelle_bati'));
            $value = (float) str_replace(',', '.', $get($row, 'valeur_fonciere'));
            $dateRaw = $get($row, 'date_mutation');
            if ($surface <= 0 || $value <= 0 || $dateRaw === '') {
                continue;
            }

            $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateRaw)
                ?: DateTimeImmutable::createFromFormat('d/m/Y', $dateRaw);
            if (!$date || $date < $monthsCutoff) {
                continue;
            }

            $priceM2 = $value / $surface;
            if ($priceM2 < 100 || $priceM2 > 50000) {
                continue;
            }

            $rows[] = [
                'mutation_date' => $date->format('Y-m-d'),
                'property_type' => $typeLocal,
                'surface' => $surface,
                'value_amount' => $value,
                'price_m2' => $priceM2,
                'city' => $get($row, 'nom_commune'),
                'postal_code' => $get($row, 'code_postal'),
                'address_label' => $get($row, 'adresse_nom_voie'),
            ];
        }

        fclose($handle);

        return $rows;
    }

    private static function estimateFromCsv(array $input): array
    {
        $type = self::normalizeType((string) ($input['property_type'] ?? ''));
        $surface = (float) ($input['surface'] ?? 0);
        $city = trim((string) ($input['city'] ?? ''));
        $postal = trim((string) ($input['postal_code'] ?? ''));

        if ($type === '' || $surface <= 0 || ($city === '' && $postal === '')) {
            return [
                'ok' => false,
                'reason' => 'invalid_input',
                'status' => 'invalid_input',
                'message' => 'Entrées insuffisantes.',
                'source' => 'csv',
                'comparables' => [],
            ];
        }

        $cache = self::loadCsvCache($input, $type);
        if ($cache === null) {
            $rows = self::buildCsvCache($input, $type);
            if ($rows !== []) {
                self::ensureCacheDir();
                $path = self::csvCachePath($input, $type);
                $payload = [
                    'generated_at' => date('c'),
                    'input' => $input,
                    'type' => $type,
                    'rows' => $rows,
                    'truncated' => count($rows) < 120,
                ];
                @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));
            }
        } else {
            $rows = $cache['rows'];
        }

        if (!is_array($rows) || $rows === []) {
            return [
                'ok' => false,
                'reason' => 'not_enough_comparables',
                'status' => 'insufficient_data',
                'comparables_count' => 0,
                'message' => 'Pas assez de ventes comparables récentes pour une estimation fiable.',
                'source' => 'csv',
                'comparables' => [],
            ];
        }

        $surfaceBands = [0.15, 0.25, 0.35];
        $dateBandsMonths = [24, 36, 48];
        $minComparables = 8;
        $best = [];

        foreach ($surfaceBands as $sBand) {
            foreach ($dateBandsMonths as $months) {
                $cutoff = new DateTimeImmutable('-' . $months . ' months');
                $filtered = array_values(array_filter($rows, static function (array $row) use ($surface, $sBand, $cutoff): bool {
                    $rowSurface = (float) ($row['surface'] ?? 0);
                    $rowDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) ($row['mutation_date'] ?? ''));
                    if (!$rowDate) {
                        return false;
                    }

                    return $rowSurface > 0
                        && $rowSurface >= max(9.0, $surface * (1 - $sBand))
                        && $rowSurface <= $surface * (1 + $sBand)
                        && $rowDate >= $cutoff;
                }));

                if (count($filtered) >= $minComparables) {
                    $best = $filtered;
                    break 2;
                }

                if (count($filtered) > count($best)) {
                    $best = $filtered;
                }
            }
        }

        if (count($best) < $minComparables) {
            return [
                'ok' => false,
                'reason' => 'not_enough_comparables',
                'status' => 'insufficient_data',
                'comparables_count' => count($best),
                'message' => 'Pas assez de ventes comparables récentes pour une estimation fiable.',
                'source' => 'csv',
                'comparables' => [],
            ];
        }

        return self::buildEstimateFromRows($best, $surface, 'csv');
    }

    public static function ensureTables(): void
    {
        if (self::$tableReady) {
            return;
        }

        db()->exec('CREATE TABLE IF NOT EXISTS dvf_transactions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            mutation_id VARCHAR(64) NOT NULL,
            mutation_date DATE NOT NULL,
            property_type VARCHAR(40) NOT NULL,
            surface DECIMAL(10,2) NOT NULL,
            rooms TINYINT UNSIGNED NULL,
            land_surface DECIMAL(10,2) NULL,
            value_amount DECIMAL(14,2) NOT NULL,
            price_m2 DECIMAL(12,2) NOT NULL,
            city VARCHAR(120) NULL,
            postal_code VARCHAR(12) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            address_label VARCHAR(255) NULL,
            source_file VARCHAR(255) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_mutation_line (mutation_id, property_type, surface, value_amount),
            INDEX idx_type_date (property_type, mutation_date),
            INDEX idx_geo (latitude, longitude),
            INDEX idx_city (city),
            INDEX idx_postal_code (postal_code)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS dvf_import_runs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            source_file VARCHAR(255) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT "running",
            rows_read INT UNSIGNED NOT NULL DEFAULT 0,
            rows_inserted INT UNSIGNED NOT NULL DEFAULT 0,
            rows_updated INT UNSIGNED NOT NULL DEFAULT 0,
            rows_rejected INT UNSIGNED NOT NULL DEFAULT 0,
            started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            finished_at DATETIME NULL,
            error_log TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS estimation_zones (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type_bien VARCHAR(40) NOT NULL,
            surface VARCHAR(32) NOT NULL,
            localite VARCHAR(255) NOT NULL,
            budget VARCHAR(32) NULL,
            projet VARCHAR(40) NOT NULL,
            lat VARCHAR(32) NULL,
            lng VARCHAR(32) NULL,
            ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_est_zones_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        db()->exec('CREATE TABLE IF NOT EXISTS estimation_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            request_type VARCHAR(20) NOT NULL,
            full_name VARCHAR(160) NOT NULL DEFAULT "",
            email VARCHAR(190) NOT NULL DEFAULT "",
            phone VARCHAR(40) NULL,
            property_type VARCHAR(40) NOT NULL,
            surface DECIMAL(10,2) NOT NULL,
            rooms TINYINT UNSIGNED NULL,
            address_raw VARCHAR(255) NOT NULL,
            address_norm VARCHAR(255) NULL,
            city VARCHAR(120) NULL,
            postal_code VARCHAR(12) NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            estimated_low DECIMAL(14,2) NULL,
            estimated_median DECIMAL(14,2) NULL,
            estimated_high DECIMAL(14,2) NULL,
            comparables_count INT UNSIGNED NOT NULL DEFAULT 0,
            confidence_score DECIMAL(5,2) NULL,
            confidence_level VARCHAR(20) NULL,
            status VARCHAR(20) NOT NULL DEFAULT "new",
            metadata_json JSON NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_created (created_at),
            INDEX idx_status (status),
            INDEX idx_city_date (city, created_at),
            INDEX idx_property_type (property_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        self::$tableReady = true;
    }

    public static function estimate(array $input): array
    {
        self::ensureTables();

        if (self::sourceMode() === 'api') {
            $apiResult = self::estimateFromApi($input);
            if (!empty($apiResult['ok'])) {
                return $apiResult;
            }
        }

        $dbResult = null;
        if (self::hasDatabaseRows()) {
            try {
                $dbResult = self::estimateFromDatabase($input);
                if (!empty($dbResult['ok'])) {
                    return $dbResult;
                }
            } catch (Throwable $e) {
                error_log('DvfEstimatorService::estimate database fallback — ' . $e->getMessage());
            }
        }

        $csvResult = self::estimateFromCsv([
            'property_type' => (string) ($input['property_type'] ?? ''),
            'surface' => (float) ($input['surface'] ?? 0),
            'city' => (string) ($input['city'] ?? ''),
            'postal_code' => (string) ($input['postal_code'] ?? ''),
        ]);

        if (!empty($csvResult['ok']) || $dbResult === null) {
            return $csvResult;
        }

        return $dbResult;
    }

    private static function estimateFromApi(array $input): array
    {
        $endpoint = '';
        $apiKey = '';
        $provider = 'DVF API';
        if (function_exists('setting')) {
            $endpoint = trim((string) setting('estimation_dvf_api_endpoint', ''));
            $apiKey = trim((string) setting('estimation_dvf_api_key', ''));
            $provider = trim((string) setting('estimation_dvf_api_provider', 'DVF API'));
        }

        if ($endpoint === '') {
            return [
                'ok' => false,
                'reason' => 'api_not_configured',
                'status' => 'config_error',
                'message' => 'Mode API sélectionné mais point d’accès non configuré.',
                'source' => 'api',
                'comparables' => [],
            ];
        }

        $payload = [
            'property_type' => (string) ($input['property_type'] ?? ''),
            'surface' => (float) ($input['surface'] ?? 0),
            'city' => (string) ($input['city'] ?? ''),
            'postal_code' => (string) ($input['postal_code'] ?? ''),
            'lat' => isset($input['lat']) ? (float) $input['lat'] : null,
            'lng' => isset($input['lng']) ? (float) $input['lng'] : null,
        ];

        $response = null;
        $headers = ['Accept: application/json'];
        if ($apiKey !== '') {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }
        $headers[] = 'Content-Type: application/json';

        try {
            if (function_exists('curl_init')) {
                $ch = curl_init($endpoint);
                if ($ch !== false) {
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => $headers,
                        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
                        CURLOPT_CONNECTTIMEOUT => 10,
                        CURLOPT_TIMEOUT => 20,
                    ]);
                    $raw = curl_exec($ch);
                    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if (is_string($raw) && $raw !== '' && $status >= 200 && $status < 300) {
                        $response = json_decode($raw, true);
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('DvfEstimatorService::estimateFromApi curl error — ' . $e->getMessage());
        }

        $hasFields = is_array($response)
            && (
                isset($response['estimate_low'])
                || isset($response['low'])
                || isset($response['median'])
                || isset($response['estimate_median'])
            );

        if (!is_array($response) || (empty($response['ok']) && !$hasFields)) {
            return [
                'ok' => false,
                'reason' => 'api_failed',
                'status' => 'config_error',
                'message' => 'Impossible de récupérer l’estimation depuis l’API DVF configurée.',
                'source' => 'api',
                'provider' => $provider,
                'comparables' => [],
            ];
        }

        return [
            'ok' => true,
            'status' => 'ok',
            'estimate_low' => (int) ($response['estimate_low'] ?? $response['low'] ?? 0),
            'estimate_median' => (int) ($response['estimate_median'] ?? $response['median'] ?? 0),
            'estimate_high' => (int) ($response['estimate_high'] ?? $response['high'] ?? 0),
            'price_m2_median' => (float) ($response['price_m2_median'] ?? $response['price_m2'] ?? 0),
            'comparables_count' => (int) ($response['comparables_count'] ?? 0),
            'confidence_score' => (float) ($response['confidence_score'] ?? 0),
            'confidence_level' => (string) ($response['confidence_level'] ?? 'moyenne'),
            'message' => (string) ($response['message'] ?? 'Estimation calculée via l’API DVF.'),
            'source' => 'api',
            'provider' => $provider,
            'comparables' => isset($response['comparables']) && is_array($response['comparables']) ? $response['comparables'] : [],
        ];
    }

    private static function estimateFromDatabase(array $input): array
    {
        $type = self::normalizeType((string) ($input['property_type'] ?? ''));
        $surface = (float) ($input['surface'] ?? 0);
        $lat = isset($input['lat']) ? (float) $input['lat'] : null;
        $lng = isset($input['lng']) ? (float) $input['lng'] : null;
        $city = trim((string) ($input['city'] ?? ''));

        if ($type === '' || $surface <= 0) {
            return ['ok' => false, 'reason' => 'invalid_input', 'status' => 'invalid_input', 'message' => 'Entrées insuffisantes.'];
        }

        $surfaceBands = [0.15, 0.25, 0.35];
        $dateBandsMonths = [24, 36, 48];
        $radiusKmBands = [1.0, 2.0, 5.0];
        $minComparables = 8;

        $best = [];
        foreach ($surfaceBands as $sBand) {
            foreach ($dateBandsMonths as $months) {
                foreach ($radiusKmBands as $radiusKm) {
                    $rows = self::findComparables($type, $surface, $sBand, $months, $radiusKm, $lat, $lng, $city);
                    if (count($rows) >= $minComparables) {
                        $best = $rows;
                        break 3;
                    }
                    if (count($rows) > count($best)) {
                        $best = $rows;
                    }
                }
            }
        }

        if (count($best) < $minComparables) {
            return [
                'ok' => false,
                'reason' => 'not_enough_comparables',
                'status' => 'insufficient_data',
                'comparables_count' => count($best),
                'message' => 'Pas assez de ventes comparables récentes pour une estimation fiable.',
                'comparables' => [],
                'source' => 'db',
            ];
        }

        return self::buildEstimateFromRows($best, $surface, 'db');
    }

    public static function saveRequest(array $request, array $estimate): int
    {
        self::ensureTables();

        $stmt = db()->prepare('INSERT INTO estimation_requests
            (request_type, full_name, email, phone, property_type, surface, rooms, address_raw, address_norm, city, postal_code, latitude, longitude,
             estimated_low, estimated_median, estimated_high, comparables_count, confidence_score, confidence_level, status, metadata_json, created_at, updated_at)
            VALUES
            (:request_type, :full_name, :email, :phone, :property_type, :surface, :rooms, :address_raw, :address_norm, :city, :postal_code, :latitude, :longitude,
             :estimated_low, :estimated_median, :estimated_high, :comparables_count, :confidence_score, :confidence_level, :status, :metadata_json, NOW(), NOW())');

        $stmt->execute([
            ':request_type' => (string) ($request['request_type'] ?? 'instant'),
            ':full_name' => trim((string) ($request['full_name'] ?? '')),
            ':email' => trim((string) ($request['email'] ?? '')),
            ':phone' => trim((string) ($request['phone'] ?? '')),
            ':property_type' => self::normalizeType((string) ($request['property_type'] ?? '')),
            ':surface' => (float) ($request['surface'] ?? 0),
            ':rooms' => isset($request['rooms']) && $request['rooms'] !== '' ? (int) $request['rooms'] : null,
            ':address_raw' => trim((string) ($request['address_raw'] ?? '')),
            ':address_norm' => trim((string) ($request['address_norm'] ?? '')),
            ':city' => trim((string) ($request['city'] ?? '')),
            ':postal_code' => trim((string) ($request['postal_code'] ?? '')),
            ':latitude' => isset($request['lat']) ? (float) $request['lat'] : null,
            ':longitude' => isset($request['lng']) ? (float) $request['lng'] : null,
            ':estimated_low' => $estimate['estimate_low'] ?? null,
            ':estimated_median' => $estimate['estimate_median'] ?? null,
            ':estimated_high' => $estimate['estimate_high'] ?? null,
            ':comparables_count' => (int) ($estimate['comparables_count'] ?? 0),
            ':confidence_score' => $estimate['confidence_score'] ?? null,
            ':confidence_level' => $estimate['confidence_level'] ?? null,
            ':status' => 'new',
            ':metadata_json' => json_encode($request['metadata'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        return (int) db()->lastInsertId();
    }

    public static function recentRequests(array $filters = []): array
    {
        self::ensureTables();
        $where = [];
        $params = [];

        if (!empty($filters['city'])) {
            $where[] = 'city = :city';
            $params[':city'] = $filters['city'];
        }
        if (!empty($filters['property_type'])) {
            $where[] = 'property_type = :property_type';
            $params[':property_type'] = self::normalizeType((string) $filters['property_type']);
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql = 'SELECT * FROM estimation_requests';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 500';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function importStats(): array
    {
        self::ensureTables();
        $runs = db()->query('SELECT * FROM dvf_import_runs ORDER BY started_at DESC LIMIT 20')->fetchAll() ?: [];
        $totalRows = (int) (db()->query('SELECT COUNT(*) FROM dvf_transactions')->fetchColumn() ?: 0);

        return [
            'runs' => $runs,
            'total_rows' => $totalRows,
        ];
    }

    private static function findComparables(string $type, float $surface, float $surfaceBand, int $months, float $radiusKm, ?float $lat, ?float $lng, string $city): array
    {
        $minSurface = max(9.0, $surface * (1 - $surfaceBand));
        $maxSurface = $surface * (1 + $surfaceBand);

        // NOTE: PDO::ATTR_EMULATE_PREPARES est false → pas de répétition de params nommés.
        // On utilise des paramètres positionnels (?) pour la formule de distance.
        $params = [];

        if ($lat !== null && $lng !== null) {
            // Formule haversine avec paramètres positionnels pour éviter HY093
            $distanceExpr = '(6371 * ACOS(LEAST(1.0, COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))))';
            $params[] = $lat;  // premier ?
            $params[] = $lng;  // deuxième ?
            $params[] = $lat;  // troisième ?
        } else {
            $distanceExpr = '9999';
        }

        $sql = "SELECT price_m2, surface, value_amount, mutation_date, city, address_label, postal_code, {$distanceExpr} AS distance_km
                FROM dvf_transactions
                WHERE property_type = ?
                  AND surface BETWEEN ? AND ?
                  AND mutation_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                  AND price_m2 > 100";

        // Paramètres WHERE principaux (après les params lat/lng du SELECT)
        $params[] = $type;
        $params[] = $minSurface;
        $params[] = $maxSurface;
        $params[] = $months;

        if ($lat !== null && $lng !== null) {
            $sql .= ' AND latitude IS NOT NULL AND longitude IS NOT NULL HAVING distance_km <= ? ORDER BY mutation_date DESC LIMIT 250';
            $params[] = $radiusKm;
        } elseif ($city !== '') {
            $sql .= ' AND city = ? ORDER BY mutation_date DESC LIMIT 250';
            $params[] = $city;
        } else {
            $sql .= ' ORDER BY mutation_date DESC LIMIT 250';
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    private static function normalizeType(string $type): string
    {
        $map = [
            'appartement' => 'appartement',
            'maison' => 'maison',
            'local' => 'local',
            'terrain' => 'terrain',
            'immeuble' => 'immeuble',
        ];
        $type = strtolower(trim($type));
        return $map[$type] ?? $type;
    }

    private static function percentile(array $sortedValues, float $percent): float
    {
        $n = count($sortedValues);
        if ($n === 0) {
            return 0.0;
        }
        if ($n === 1) {
            return (float) $sortedValues[0];
        }
        $rank = ($percent / 100) * ($n - 1);
        $low = (int) floor($rank);
        $high = (int) ceil($rank);
        if ($low === $high) {
            return (float) $sortedValues[$low];
        }
        $weight = $rank - $low;
        return ((1 - $weight) * $sortedValues[$low]) + ($weight * $sortedValues[$high]);
    }

    private static function computeConfidenceScore(int $count, float $iqr, float $p50): float
    {
        $countScore = min(100, $count * 6);
        $dispersionRatio = $p50 > 0 ? $iqr / $p50 : 1;
        $dispersionScore = max(0, 100 - ($dispersionRatio * 180));
        return round(($countScore * 0.6) + ($dispersionScore * 0.4), 1);
    }
}
