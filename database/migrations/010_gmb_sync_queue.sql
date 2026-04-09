-- Queue + logs de synchronisation Google Business Profile (admin hub)

CREATE TABLE IF NOT EXISTS gmb_sync_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_type VARCHAR(64) NOT NULL,
    payload_json JSON DEFAULT NULL,
    status ENUM('queued','running','done','failed') NOT NULL DEFAULT 'queued',
    attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    available_at DATETIME NOT NULL,
    started_at DATETIME DEFAULT NULL,
    finished_at DATETIME DEFAULT NULL,
    last_error VARCHAR(1000) DEFAULT NULL,
    result_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_available (status, available_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gmb_sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_type VARCHAR(64) NOT NULL,
    status ENUM('done','failed') NOT NULL,
    message VARCHAR(1000) DEFAULT NULL,
    crawl_score TINYINT UNSIGNED DEFAULT NULL,
    reviews_synced INT UNSIGNED DEFAULT NULL,
    synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_synced (user_id, synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
