-- ============================================================
-- Table cms_pages (contenu éditable des pages, dont l’accueil)
-- À exécuter si la base n’a pas encore cette table (ex. hébergement
-- sans les migrations historiques qui supposaient cms_pages existant).
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `cms_pages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `slug` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `template` VARCHAR(255) NOT NULL DEFAULT '',
    `page_type` VARCHAR(50) NOT NULL DEFAULT 'page',
    `kind` VARCHAR(50) DEFAULT NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` TEXT,
    `data_json` LONGTEXT,
    `show_in_menu` TINYINT(1) NOT NULL DEFAULT 0,
    `show_in_footer` TINYINT(1) NOT NULL DEFAULT 0,
    `show_in_sitemap` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_cms_pages_site_slug` (`site_id`, `slug`),
    KEY `idx_cms_pages_slug` (`slug`),
    KEY `idx_cms_pages_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
