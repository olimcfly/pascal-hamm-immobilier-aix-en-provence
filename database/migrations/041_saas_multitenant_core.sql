-- ============================================================
-- 041 — Socle SaaS multi-tenant + CRM (schéma aligné produit)
-- N’efface aucune donnée existante sur les autres tables.
-- Après exécution : rattacher les crm_leads à l’organisation « legacy ».
-- Si vous aviez une ancienne 041 avec is_default : exécuter aussi 043.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `organizations` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(255) NOT NULL,
  `slug`            VARCHAR(120) NOT NULL,
  `status`          ENUM('active','trial','suspended','cancelled') NOT NULL DEFAULT 'trial',
  `plan_code`       VARCHAR(80) NOT NULL DEFAULT 'essential',
  `owner_user_id`   INT UNSIGNED NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_organizations_slug` (`slug`),
  CONSTRAINT `fk_organizations_owner_user` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `memberships` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` INT UNSIGNED NOT NULL,
  `user_id`          INT UNSIGNED NOT NULL,
  `role`             ENUM('owner','admin','editor','viewer') NOT NULL DEFAULT 'owner',
  `status`           ENUM('active','invited','disabled') NOT NULL DEFAULT 'active',
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_user` (`organization_id`, `user_id`),
  KEY `idx_memberships_user` (`user_id`),
  CONSTRAINT `fk_memberships_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_memberships_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_settings` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` INT UNSIGNED NOT NULL,
  `setting_key`     VARCHAR(120) NOT NULL,
  `setting_value`   LONGTEXT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_setting` (`organization_id`, `setting_key`),
  CONSTRAINT `fk_tenant_settings_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_features` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` INT UNSIGNED NOT NULL,
  `feature_key`     VARCHAR(120) NOT NULL,
  `enabled`         TINYINT(1) NOT NULL DEFAULT 0,
  `limit_value`     INT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_feature` (`organization_id`, `feature_key`),
  CONSTRAINT `fk_tenant_features_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usage_counters` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` INT UNSIGNED NOT NULL,
  `usage_key`       VARCHAR(120) NOT NULL,
  `period_start`    DATE NOT NULL,
  `period_end`      DATE NOT NULL,
  `current_value`   INT NOT NULL DEFAULT 0,
  `limit_value`     INT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_org_usage_period` (`organization_id`, `usage_key`, `period_start`, `period_end`),
  CONSTRAINT `fk_usage_counters_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `organizations` (`name`, `slug`, `status`, `plan_code`, `owner_user_id`, `created_at`, `updated_at`)
VALUES (
  'Organisation principale (legacy)',
  'legacy-default',
  'active',
  'essential',
  NULL,
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `status` = 'active',
  `plan_code` = VALUES(`plan_code`),
  `updated_at` = NOW();

SET @default_org_id := (SELECT `id` FROM `organizations` WHERE `slug` = 'legacy-default' LIMIT 1);

INSERT INTO `memberships` (`organization_id`, `user_id`, `role`, `status`, `created_at`, `updated_at`)
SELECT
  @default_org_id,
  u.`id`,
  CASE
    WHEN u.`role` = 'superadmin' THEN 'owner'
    WHEN u.`role` = 'admin' THEN 'admin'
    ELSE 'editor'
  END,
  'active',
  NOW(),
  NOW()
FROM `users` u
WHERE @default_org_id IS NOT NULL
ON DUPLICATE KEY UPDATE
  `updated_at` = NOW();

-- ── CRM : colonne organization_id sur crm_leads ─────────────
SET @db := DATABASE();
SET @has_org_col := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'crm_leads' AND COLUMN_NAME = 'organization_id'
);
SET @sql_org_col := IF(@has_org_col = 0,
  'ALTER TABLE `crm_leads` ADD COLUMN `organization_id` INT UNSIGNED NULL AFTER `id`',
  'SELECT 1 AS skip_crm_org_col'
);
PREPARE stmt_org_col FROM @sql_org_col;
EXECUTE stmt_org_col;
DEALLOCATE PREPARE stmt_org_col;

UPDATE `crm_leads`
SET `organization_id` = @default_org_id
WHERE (`organization_id` IS NULL OR `organization_id` = 0)
  AND @default_org_id IS NOT NULL;

SET @has_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'crm_leads' AND INDEX_NAME = 'idx_crm_leads_organization'
);
SET @sql_idx := IF(@has_idx = 0,
  'ALTER TABLE `crm_leads` ADD INDEX `idx_crm_leads_organization` (`organization_id`)',
  'SELECT 1 AS skip_idx'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'crm_leads'
    AND CONSTRAINT_NAME = 'fk_crm_leads_organization'
);
SET @sql_fk := IF(@fk_exists = 0 AND @default_org_id IS NOT NULL,
  'ALTER TABLE `crm_leads` ADD CONSTRAINT `fk_crm_leads_organization` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT',
  'SELECT 1 AS skip_fk_crm_leads'
);
PREPARE stmt_fk FROM @sql_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;
