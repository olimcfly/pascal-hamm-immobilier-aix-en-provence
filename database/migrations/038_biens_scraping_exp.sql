-- Colonnes pour l’import scraping eXp (admin/api/scraping/import.php)
-- Exécuter sur les bases où ces colonnes n’existent pas encore.

SET NAMES utf8mb4;

ALTER TABLE `biens`
  ADD COLUMN `agent_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Utilisateur admin ayant importé' AFTER `photo_principale`,
  ADD COLUMN `source` VARCHAR(32) NULL DEFAULT NULL COMMENT 'own|partage' AFTER `agent_id`,
  ADD COLUMN `source_externe_id` VARCHAR(80) NULL DEFAULT NULL COMMENT 'ID listing côté eXp/Supabase' AFTER `source`,
  ADD COLUMN `source_agent_nom` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Nom agent sur la fiche source' AFTER `source_externe_id`;

ALTER TABLE `biens`
  ADD KEY `idx_source_externe` (`source_externe_id`);
