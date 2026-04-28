-- =============================================================
-- MIGRATION V2 — Statuts étendus + mode test
-- =============================================================

-- -------------------------------------------------------------
-- campaign_contacts — statuts étendus + colonnes tracking
-- -------------------------------------------------------------
ALTER TABLE campaign_contacts
    MODIFY COLUMN status ENUM(
        'enrolled',
        'active',
        'paused',
        'completed',
        'replied',
        'bounced',
        'unsubscribed',
        'pending',
        'queued',
        'delivered',
        'opened',
        'clicked'
    ) NOT NULL DEFAULT 'enrolled',
    ADD COLUMN IF NOT EXISTS opened_at    DATETIME DEFAULT NULL AFTER replied_at,
    ADD COLUMN IF NOT EXISTS clicked_at   DATETIME DEFAULT NULL AFTER opened_at,
    ADD COLUMN IF NOT EXISTS bounced_at   DATETIME DEFAULT NULL AFTER clicked_at,
    ADD COLUMN IF NOT EXISTS unsub_at     DATETIME DEFAULT NULL AFTER bounced_at,
    ADD COLUMN IF NOT EXISTS step_history JSON     DEFAULT NULL COMMENT 'Historique des étapes parcourues' AFTER unsub_at;

-- -------------------------------------------------------------
-- email_send_log — statuts étendus + flag is_test
-- -------------------------------------------------------------
ALTER TABLE email_send_log
    MODIFY COLUMN status ENUM(
        'scheduled',
        'rendered',
        'sent',
        'delivered',
        'failed',
        'opened',
        'clicked',
        'skipped',
        'stopped_on_reply',
        'unsubscribed',
        'bounced'
    ) NOT NULL DEFAULT 'scheduled',
    ADD COLUMN IF NOT EXISTS is_test              TINYINT(1)   NOT NULL DEFAULT 0    COMMENT '1 = envoi en mode test',
    ADD COLUMN IF NOT EXISTS intended_recipient   VARCHAR(180) DEFAULT NULL          COMMENT 'Destinataire réel si redirigé en test',
    ADD COLUMN IF NOT EXISTS delivered_at         DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS opened_at            DATETIME     DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS clicked_at           DATETIME     DEFAULT NULL;
