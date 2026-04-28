CREATE TABLE IF NOT EXISTS prospect_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(50) NULL,
    company_network VARCHAR(190) NOT NULL,
    city VARCHAR(120) NOT NULL,
    source_type ENUM('manual','csv','scraping') NOT NULL,
    source_label VARCHAR(190) NOT NULL,
    validation_status ENUM('missing','invalid_format','duplicate','pending_review','valid','blacklisted') NOT NULL DEFAULT 'pending_review',
    blacklist_status TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    contact_status ENUM('new','queued','in_sequence','replied','qualified','unsubscribed') NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_prospect_contact_email (email),
    KEY idx_prospect_contacts_validation (validation_status),
    KEY idx_prospect_contacts_source (source_type, source_label)
);

CREATE TABLE IF NOT EXISTS prospect_contact_imports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    import_name VARCHAR(190) NOT NULL,
    file_name VARCHAR(190) NOT NULL,
    mapping_json JSON NOT NULL,
    total_rows INT UNSIGNED NOT NULL DEFAULT 0,
    accepted_rows INT UNSIGNED NOT NULL DEFAULT 0,
    rejected_rows INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft','processed','failed') NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prospect_contact_scrapes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scrape_batch VARCHAR(190) NOT NULL,
    payload_json JSON NOT NULL,
    dedupe_signature VARCHAR(255) NOT NULL,
    status ENUM('buffered','approved','rejected','imported') NOT NULL DEFAULT 'buffered',
    preview_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_prospect_contact_scrapes_status (status),
    KEY idx_prospect_contact_scrapes_signature (dedupe_signature)
);

CREATE TABLE IF NOT EXISTS prospect_contact_validations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    old_status ENUM('missing','invalid_format','duplicate','pending_review','valid','blacklisted') NOT NULL,
    new_status ENUM('missing','invalid_format','duplicate','pending_review','valid','blacklisted') NOT NULL,
    action_type ENUM('validate','reject','correct','merge','blacklist') NOT NULL,
    action_notes TEXT NULL,
    actor_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_validation_contact FOREIGN KEY (contact_id) REFERENCES prospect_contacts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prospect_mailboxes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(190) NOT NULL,
    smtp_host VARCHAR(190) NOT NULL,
    smtp_port INT UNSIGNED NOT NULL,
    smtp_encryption ENUM('none','ssl','tls') NOT NULL DEFAULT 'tls',
    smtp_username VARCHAR(190) NOT NULL,
    smtp_password VARCHAR(190) NOT NULL,
    from_email VARCHAR(190) NOT NULL,
    from_name VARCHAR(190) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS prospect_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(190) NOT NULL,
    objective VARCHAR(255) NOT NULL,
    status ENUM('draft','ready','running','paused','completed') NOT NULL DEFAULT 'draft',
    segment_filter_json JSON NOT NULL,
    mailbox_id BIGINT UNSIGNED NOT NULL,
    daily_limit INT UNSIGNED NOT NULL DEFAULT 50,
    launch_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_campaign_mailbox FOREIGN KEY (mailbox_id) REFERENCES prospect_mailboxes(id)
);

CREATE TABLE IF NOT EXISTS prospect_campaign_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    enrollment_status ENUM('queued','active','replied','stopped','completed') NOT NULL DEFAULT 'queued',
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    stopped_at DATETIME NULL,
    UNIQUE KEY uniq_campaign_contact (campaign_id, contact_id),
    CONSTRAINT fk_campaign_contacts_campaign FOREIGN KEY (campaign_id) REFERENCES prospect_campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_campaign_contacts_contact FOREIGN KEY (contact_id) REFERENCES prospect_contacts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prospect_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(190) NOT NULL,
    auto_stop_on_reply TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sequences_campaign FOREIGN KEY (campaign_id) REFERENCES prospect_campaigns(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prospect_sequence_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sequence_id BIGINT UNSIGNED NOT NULL,
    step_order SMALLINT UNSIGNED NOT NULL,
    subject_template VARCHAR(255) NOT NULL,
    body_template MEDIUMTEXT NOT NULL,
    delay_days SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sequence_steps_sequence FOREIGN KEY (sequence_id) REFERENCES prospect_sequences(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_sequence_step (sequence_id, step_order)
);

CREATE TABLE IF NOT EXISTS prospect_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NOT NULL,
    sequence_step_id BIGINT UNSIGNED NULL,
    direction ENUM('outbound','inbound') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body MEDIUMTEXT NOT NULL,
    provider_message_id VARCHAR(255) NULL,
    sent_at DATETIME NULL,
    received_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_campaign FOREIGN KEY (campaign_id) REFERENCES prospect_campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_contact FOREIGN KEY (contact_id) REFERENCES prospect_contacts(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_step FOREIGN KEY (sequence_step_id) REFERENCES prospect_sequence_steps(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS prospect_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED NOT NULL,
    campaign_id BIGINT UNSIGNED NULL,
    thread_key VARCHAR(190) NOT NULL,
    status ENUM('open','waiting_internal_reply','closed') NOT NULL DEFAULT 'open',
    last_message_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_thread_key (thread_key),
    CONSTRAINT fk_conversation_contact FOREIGN KEY (contact_id) REFERENCES prospect_contacts(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversation_campaign FOREIGN KEY (campaign_id) REFERENCES prospect_campaigns(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS prospect_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level ENUM('info','warning','error') NOT NULL,
    context VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    payload_json JSON NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_prospect_logs_context (context)
);
