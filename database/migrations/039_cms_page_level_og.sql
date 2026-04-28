-- Niveau de page (1 = public, 2 = remerciements / erreurs / utilitaires) + image Open Graph
SET NAMES utf8mb4;

ALTER TABLE `cms_pages`
    ADD COLUMN `page_level` TINYINT UNSIGNED NOT NULL DEFAULT 1
        COMMENT '1=public, 2=utilitaire (merci, erreur, maintenance…)' AFTER `kind`,
    ADD COLUMN `og_image_url` VARCHAR(512) NULL DEFAULT NULL
        COMMENT 'Image partage réseaux (Open Graph)' AFTER `meta_description`;
