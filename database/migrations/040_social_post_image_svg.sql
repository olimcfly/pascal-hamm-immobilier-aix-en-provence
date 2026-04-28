-- Visuel social généré (SVG) + format cible feed / story
ALTER TABLE social_posts ADD COLUMN image_svg LONGTEXT NULL;
ALTER TABLE social_posts ADD COLUMN image_format VARCHAR(16) NOT NULL DEFAULT 'feed';
