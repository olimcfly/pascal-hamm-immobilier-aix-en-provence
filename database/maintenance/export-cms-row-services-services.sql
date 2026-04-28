-- =============================================================================
-- (Historique) Ligne orpheline services-services : sauvegarde + suppression
-- ciblée effectuée via database/maintenance/run_once_remove_cms_services_services.php
-- Table de sauvegarde : cms_pages_backup_services_services
--
-- Sauvegarde manuelle équivalente :
--   CREATE TABLE IF NOT EXISTS cms_pages_backup_services_services AS
--   SELECT * FROM cms_pages WHERE site_id = 1 AND slug = 'services-services';
-- =============================================================================

-- 1) Inspection
SELECT *
FROM cms_pages
WHERE site_id = 1 AND slug = 'services-services';

-- 2) Export minimal (copie JSON + méta, utile pour archivage)
SELECT
    id,
    site_id,
    slug,
    title,
    template,
    status,
    meta_title,
    meta_description,
    data_json,
    created_at,
    updated_at
FROM cms_pages
WHERE site_id = 1 AND slug = 'services-services';

-- 3) Suppression MANUELLE — ne l’exécuter qu’après sauvegarde (mysqldump ou copie)
--    et seulement si la ligne est bien un doublon inutile.
-- DELETE FROM cms_pages WHERE site_id = 1 AND slug = 'services-services' LIMIT 1;
