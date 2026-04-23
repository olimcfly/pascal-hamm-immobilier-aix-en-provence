-- =============================================================
-- MODULE PROSPECTION EMAIL — Schema V1
-- =============================================================

-- -------------------------------------------------------------
-- 1. CONTACTS DE PROSPECTION
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS prospect_contacts (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED     NOT NULL,                          -- propriétaire (multi-tenant)
    first_name    VARCHAR(100)     NOT NULL DEFAULT '',
    last_name     VARCHAR(100)     NOT NULL DEFAULT '',
    email         VARCHAR(180)     NOT NULL,
    phone         VARCHAR(40)      DEFAULT NULL,
    company       VARCHAR(180)     DEFAULT NULL,
    city          VARCHAR(120)     DEFAULT NULL,
    source        VARCHAR(80)      NOT NULL DEFAULT 'manual',         -- manual, csv, import, autre
    tags          JSON             DEFAULT NULL,                      -- tableau de chaînes
    email_status  ENUM('unknown','valid','risky','invalid') NOT NULL DEFAULT 'unknown',
    status        ENUM('active','paused','bounced','replied','unsubscribed') NOT NULL DEFAULT 'active',
    notes         TEXT             DEFAULT NULL,
    deleted_at    DATETIME         DEFAULT NULL,                      -- soft delete
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_prospect_email_user (email, user_id),
    KEY idx_prospect_user    (user_id),
    KEY idx_prospect_status  (status),
    KEY idx_prospect_deleted (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. CAMPAGNES
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_campaigns (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED     NOT NULL,
    name          VARCHAR(200)     NOT NULL,
    description   TEXT             DEFAULT NULL,
    objective     VARCHAR(255)     DEFAULT NULL,
    status        ENUM('draft','active','paused','completed') NOT NULL DEFAULT 'draft',
    deleted_at    DATETIME         DEFAULT NULL,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_campaign_user   (user_id),
    KEY idx_campaign_status (status),
    KEY idx_campaign_deleted(deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. ÉTAPES DE SÉQUENCE
--    Une campagne → plusieurs étapes ordonnées
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_sequence_steps (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    campaign_id   INT UNSIGNED     NOT NULL,
    step_order    TINYINT UNSIGNED NOT NULL DEFAULT 1,               -- ordre dans la séquence
    delay_days    SMALLINT UNSIGNED NOT NULL DEFAULT 0,              -- délai depuis le step précédent (0 = J0)
    subject       VARCHAR(255)     NOT NULL,
    body_text     MEDIUMTEXT       NOT NULL,                         -- corps brut avec {{variables}}
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_step_campaign (campaign_id),
    CONSTRAINT fk_step_campaign FOREIGN KEY (campaign_id)
        REFERENCES email_campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. INSCRIPTION CONTACTS / CAMPAGNE
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS campaign_contacts (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    campaign_id        INT UNSIGNED  NOT NULL,
    contact_id         INT UNSIGNED  NOT NULL,
    status             ENUM('enrolled','active','paused','completed','replied','bounced','unsubscribed') NOT NULL DEFAULT 'enrolled',
    current_step       TINYINT UNSIGNED NOT NULL DEFAULT 0,          -- index de la prochaine étape (0 = pas encore envoyé)
    next_send_at       DATETIME      DEFAULT NULL,                   -- prochaine date d'envoi planifiée
    last_sent_at       DATETIME      DEFAULT NULL,
    replied_at         DATETIME      DEFAULT NULL,
    enrolled_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_enroll (campaign_id, contact_id),
    KEY idx_cc_campaign (campaign_id),
    KEY idx_cc_contact  (contact_id),
    KEY idx_cc_status   (status),
    KEY idx_cc_next     (next_send_at),
    CONSTRAINT fk_cc_campaign FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_cc_contact  FOREIGN KEY (contact_id)  REFERENCES prospect_contacts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. JOURNAL D'ENVOI (un enregistrement par email généré/envoyé)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_send_log (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    campaign_id   INT UNSIGNED  NOT NULL,
    contact_id    INT UNSIGNED  NOT NULL,
    step_id       INT UNSIGNED  NOT NULL,
    to_email      VARCHAR(180)  NOT NULL,
    subject       VARCHAR(255)  NOT NULL,
    body_text     MEDIUMTEXT    DEFAULT NULL,                        -- corps avec variables résolues
    status        ENUM('scheduled','sent','failed','opened','clicked') NOT NULL DEFAULT 'scheduled',
    sent_at       DATETIME      DEFAULT NULL,
    error_message TEXT          DEFAULT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_campaign (campaign_id),
    KEY idx_log_contact  (contact_id),
    KEY idx_log_step     (step_id),
    KEY idx_log_status   (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. JOURNAL D'ACTIVITÉ (événements métier traçables)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_activity_log (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED  NOT NULL,
    campaign_id   INT UNSIGNED  DEFAULT NULL,
    contact_id    INT UNSIGNED  DEFAULT NULL,
    event         VARCHAR(80)   NOT NULL,                            -- enrolled, sent, replied, bounced, paused, ...
    detail        TEXT          DEFAULT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_act_user     (user_id),
    KEY idx_act_campaign (campaign_id),
    KEY idx_act_event    (event),
    KEY idx_act_created  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
