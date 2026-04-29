-- ============================================================
-- MIGRATION 044 — Messagerie IMAP/SMTP password settings
-- ============================================================

SET NAMES utf8mb4;

INSERT INTO settings_templates
(setting_key, default_value, label, description, setting_type, setting_group, is_required, validation_rules, sort_order)
VALUES
('imap_pass','','Mot de passe IMAP','Mot de passe de la boite email pour la reception IMAP','password','messagerie',0,'',10),
('smtp_pass','','Mot de passe SMTP','Mot de passe de la boite email pour l envoi SMTP','password','messagerie',0,'',20)
ON DUPLICATE KEY UPDATE
  default_value = VALUES(default_value),
  label = VALUES(label),
  description = VALUES(description),
  setting_type = VALUES(setting_type),
  setting_group = VALUES(setting_group),
  is_required = VALUES(is_required),
  validation_rules = VALUES(validation_rules),
  sort_order = VALUES(sort_order);

UPDATE settings
SET setting_type = 'password',
    is_encrypted = 1
WHERE setting_key IN ('imap_pass', 'smtp_pass')
  AND COALESCE(setting_value, '') = '';
