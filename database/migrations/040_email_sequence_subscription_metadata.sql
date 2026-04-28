-- Contexte formulaire (ex. fiche bien) pour personnaliser les emails de séquence
ALTER TABLE email_sequence_subscriptions
ADD COLUMN metadata_json TEXT NULL COMMENT 'JSON: bien_titre, bien_url, bien_reference, etc.' AFTER last_name;
