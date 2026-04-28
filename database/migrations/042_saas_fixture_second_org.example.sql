-- Exemple : 2e organisation + lead de test pour valider l’isolation CRM.
-- À adapter : remplacer @user_b par l’id d’un utilisateur réel.

SET @user_b := 2;
INSERT INTO `organizations` (`name`, `slug`, `status`, `plan_code`, `owner_user_id`, `created_at`, `updated_at`)
VALUES ('Organisation test B', 'fixture-tenant-b', 'trial', 'essential', NULL, NOW(), NOW());
SET @org_b := LAST_INSERT_ID();

INSERT INTO `memberships` (`organization_id`, `user_id`, `role`, `status`, `created_at`, `updated_at`)
VALUES (@org_b, @user_b, 'owner', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

INSERT INTO `crm_leads` (
  `organization_id`, `source_type`, `pipeline`, `stage`, `priority`,
  `first_name`, `last_name`, `email`, `phone`, `consent`, `created_at`, `updated_at`
) VALUES (
  @org_b, 'contact', 'contact', 'nouveau', 'normal',
  'Test', 'Iso', 'iso-test-b@example.test', NULL, 0, NOW(), NOW()
);
