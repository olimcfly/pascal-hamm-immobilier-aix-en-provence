-- ============================================================
-- 036 — Compte superuser (authentification via table users)
-- Email : superuser@pascal-hamm-immobilier-aix-en-provence.fr
-- Exécution : mysql -u… -p… nom_bdd < database/migrations/036_superuser_account.sql
-- ============================================================

SET NAMES utf8mb4;

-- Autoriser le rôle superadmin (erreur possible si déjà présent : ignorer)
ALTER TABLE `users`
  MODIFY COLUMN `role` ENUM('admin', 'editor', 'superadmin') NOT NULL DEFAULT 'editor';

-- Colonne is_active si absente (Auth::attempt, espace superadmin)
SET @db := DATABASE();
SET @has_ia := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_active'
);
SET @sql_ia := IF(@has_ia = 0,
  'ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`',
  'SELECT 1 AS skip_is_active'
);
PREPARE stmt_ia FROM @sql_ia;
EXECUTE stmt_ia;
DEALLOCATE PREPARE stmt_ia;

INSERT INTO `users` (`email`, `password`, `name`, `role`, `is_active`, `created_at`, `updated_at`)
VALUES (
  'superuser@pascal-hamm-immobilier-aix-en-provence.fr',
  '$2y$12$OY433LCqq3/qYzCCDIByUejcKQWEWU5x1aQ4yDt15bzs6PkBVoSQe',
  'Super administrateur',
  'superadmin',
  1,
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `password`   = VALUES(`password`),
  `role`       = 'superadmin',
  `name`       = VALUES(`name`),
  `is_active`  = 1,
  `updated_at` = NOW();
