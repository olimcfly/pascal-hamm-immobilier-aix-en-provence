-- ============================================================
-- 043 — Alignement schéma SaaS (organizations, memberships,
--        tenant_settings, tenant_features, usage_counters)
-- À exécuter APRÈS 041 si déjà appliquée. Idempotent partielle
-- (vérifie colonnes / tables avant ALTER).
-- ============================================================

SET NAMES utf8mb4;
SET @db := DATABASE();

-- ── organizations : colonnes métier + suppression is_default ─
SET @has_is_default := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'organizations' AND COLUMN_NAME = 'is_default'
);
SET @sql := IF(@has_is_default > 0,
  'ALTER TABLE `organizations` DROP COLUMN `is_default`',
  'SELECT 1'
);
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

ALTER TABLE `organizations`
  MODIFY `name` VARCHAR(255) NOT NULL,
  MODIFY `slug` VARCHAR(120) NOT NULL,
  MODIFY `status` ENUM('active','trial','suspended','cancelled') NOT NULL DEFAULT 'trial';

SET @has_plan := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'organizations' AND COLUMN_NAME = 'plan_code'
);
SET @sql2 := IF(@has_plan = 0,
  'ALTER TABLE `organizations` ADD COLUMN `plan_code` VARCHAR(80) NOT NULL DEFAULT \'essential\' AFTER `status`',
  'SELECT 1'
);
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;

SET @has_owner := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'organizations' AND COLUMN_NAME = 'owner_user_id'
);
SET @sql3 := IF(@has_owner = 0,
  'ALTER TABLE `organizations` ADD COLUMN `owner_user_id` INT UNSIGNED NULL AFTER `plan_code`',
  'SELECT 1'
);
PREPARE s3 FROM @sql3; EXECUTE s3; DEALLOCATE PREPARE s3;

SET @fk_owner := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'organizations' AND CONSTRAINT_NAME = 'fk_organizations_owner_user'
);
SET @sql4 := IF(@fk_owner = 0,
  'ALTER TABLE `organizations` ADD CONSTRAINT `fk_organizations_owner_user` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE s4 FROM @sql4; EXECUTE s4; DEALLOCATE PREPARE s4;

UPDATE `organizations` SET `status` = 'active' WHERE `slug` = 'legacy-default';

-- ── memberships : statut + rôles editor/viewer ──────────────
SET @has_mstat := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'memberships' AND COLUMN_NAME = 'status'
);
SET @sql5 := IF(@has_mstat = 0,
  'ALTER TABLE `memberships` ADD COLUMN `status` ENUM(\'active\',\'invited\',\'disabled\') NOT NULL DEFAULT \'active\' AFTER `role`',
  'SELECT 1'
);
PREPARE s5 FROM @sql5; EXECUTE s5; DEALLOCATE PREPARE s5;

UPDATE `memberships` SET `role` = 'editor' WHERE `role` = 'member';

ALTER TABLE `memberships`
  MODIFY `role` ENUM('owner','admin','editor','viewer') NOT NULL DEFAULT 'owner';

-- ── tenant_settings : PK surrogate + LONGTEXT ───────────────
SET @ts_old_pk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = @db AND TABLE_NAME = 'tenant_settings'
    AND CONSTRAINT_TYPE = 'PRIMARY KEY' AND CONSTRAINT_NAME = 'PRIMARY'
);
SET @ts_has_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tenant_settings' AND COLUMN_NAME = 'id'
);

SET @need_ts_migrate := (@ts_has_id = 0 AND EXISTS (
  SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tenant_settings'
));

SET FOREIGN_KEY_CHECKS = 0;

SET @sql_ts := IF(@need_ts_migrate > 0,
  'CREATE TABLE `tenant_settings_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `setting_key` VARCHAR(120) NOT NULL,
    `setting_value` LONGTEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_org_setting` (`organization_id`, `setting_key`),
    CONSTRAINT `fk_tenant_settings_new_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
  'SELECT 1'
);
PREPARE sts FROM @sql_ts; EXECUTE sts; DEALLOCATE PREPARE sts;

SET @sql_ts_ins := IF(@need_ts_migrate > 0,
  'INSERT INTO `tenant_settings_new` (`organization_id`, `setting_key`, `setting_value`, `created_at`, `updated_at`)
   SELECT `organization_id`, `setting_key`, `setting_value`, COALESCE(`updated_at`, NOW()), COALESCE(`updated_at`, NOW()) FROM `tenant_settings`',
  'SELECT 1'
);
PREPARE stsi FROM @sql_ts_ins; EXECUTE stsi; DEALLOCATE PREPARE stsi;

SET @sql_ts_drop := IF(@need_ts_migrate > 0, 'DROP TABLE `tenant_settings`', 'SELECT 1');
PREPARE stsd FROM @sql_ts_drop; EXECUTE stsd; DEALLOCATE PREPARE stsd;

SET @sql_ts_ren := IF(@need_ts_migrate > 0, 'RENAME TABLE `tenant_settings_new` TO `tenant_settings`', 'SELECT 1');
PREPARE stsr FROM @sql_ts_ren; EXECUTE stsr; DEALLOCATE PREPARE stsr;

-- ── tenant_features : id + limit_value ──────────────────────
SET @tf_has_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tenant_features' AND COLUMN_NAME = 'id'
);
SET @need_tf := (@tf_has_id = 0 AND EXISTS (
  SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tenant_features'
));

SET @sql_tf := IF(@need_tf > 0,
  'CREATE TABLE `tenant_features_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `feature_key` VARCHAR(120) NOT NULL,
    `enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `limit_value` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_org_feature` (`organization_id`, `feature_key`),
    CONSTRAINT `fk_tenant_features_new_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
  'SELECT 1'
);
PREPARE stf FROM @sql_tf; EXECUTE stf; DEALLOCATE PREPARE stf;

SET @sql_tf_ins := IF(@need_tf > 0,
  'INSERT INTO `tenant_features_new` (`organization_id`, `feature_key`, `enabled`, `limit_value`, `created_at`, `updated_at`)
   SELECT `organization_id`, `feature_key`, `enabled`, NULL, NOW(), COALESCE(`updated_at`, NOW()) FROM `tenant_features`',
  'SELECT 1'
);
PREPARE stfi FROM @sql_tf_ins; EXECUTE stfi; DEALLOCATE PREPARE stfi;

SET @sql_tf_drop := IF(@need_tf > 0, 'DROP TABLE `tenant_features`', 'SELECT 1');
PREPARE stfd FROM @sql_tf_drop; EXECUTE stfd; DEALLOCATE PREPARE stfd;

SET @sql_tf_ren := IF(@need_tf > 0, 'RENAME TABLE `tenant_features_new` TO `tenant_features`', 'SELECT 1');
PREPARE stfr FROM @sql_tf_ren; EXECUTE stfr; DEALLOCATE PREPARE stfr;

-- ── usage_counters : périodes date + usage_key ───────────────
SET @uc_has_period := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'usage_counters' AND COLUMN_NAME = 'period_start'
);
SET @need_uc := (@uc_has_period = 0 AND EXISTS (
  SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'usage_counters'
));

SET @sql_uc := IF(@need_uc > 0,
  'CREATE TABLE `usage_counters_new` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` INT UNSIGNED NOT NULL,
    `usage_key` VARCHAR(120) NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `current_value` INT NOT NULL DEFAULT 0,
    `limit_value` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_org_usage_period` (`organization_id`, `usage_key`, `period_start`, `period_end`),
    CONSTRAINT `fk_usage_counters_new_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
  'SELECT 1'
);
PREPARE suc FROM @sql_uc; EXECUTE suc; DEALLOCATE PREPARE suc;

SET @sql_uc_ins := IF(@need_uc > 0,
  'INSERT INTO `usage_counters_new` (`organization_id`, `usage_key`, `period_start`, `period_end`, `current_value`, `limit_value`, `created_at`, `updated_at`)
   SELECT
     `organization_id`,
     `counter_key` AS `usage_key`,
     CASE
       WHEN `period_key` = \'all\' OR `period_key` = \'\' THEN DATE(\'1970-01-01\')
       WHEN `period_key` REGEXP \'^[0-9]{4}-[0-9]{2}$\'
         THEN STR_TO_DATE(CONCAT(`period_key`, \'-01\'), \'%Y-%m-%d\')
       ELSE DATE(\'1970-01-01\')
     END AS `period_start`,
     CASE
       WHEN `period_key` = \'all\' OR `period_key` = \'\' THEN DATE(\'2099-12-31\')
       WHEN `period_key` REGEXP \'^[0-9]{4}-[0-9]{2}$\'
         THEN LAST_DAY(STR_TO_DATE(CONCAT(`period_key`, \'-01\'), \'%Y-%m-%d\'))
       ELSE DATE(\'2099-12-31\')
     END AS `period_end`,
     `value` AS `current_value`,
     NULL AS `limit_value`,
     NOW(),
     COALESCE(`updated_at`, NOW())
   FROM `usage_counters`',
  'SELECT 1'
);
PREPARE suci FROM @sql_uc_ins; EXECUTE suci; DEALLOCATE PREPARE suci;

SET @sql_uc_drop := IF(@need_uc > 0, 'DROP TABLE `usage_counters`', 'SELECT 1');
PREPARE sucd FROM @sql_uc_drop; EXECUTE sucd; DEALLOCATE PREPARE sucd;

SET @sql_uc_ren := IF(@need_uc > 0, 'RENAME TABLE `usage_counters_new` TO `usage_counters`', 'SELECT 1');
PREPARE sucr FROM @sql_uc_ren; EXECUTE sucr; DEALLOCATE PREPARE sucr;

SET FOREIGN_KEY_CHECKS = 1;
