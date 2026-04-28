-- Référence stable + lien article blog pour les séquences générées depuis la rédaction
ALTER TABLE social_sequences ADD COLUMN ref_code VARCHAR(48) DEFAULT NULL;
ALTER TABLE social_sequences ADD COLUMN source_article_id INT UNSIGNED DEFAULT NULL;
CREATE INDEX idx_social_sequences_user_ref ON social_sequences (user_id, ref_code);
