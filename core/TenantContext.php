<?php
declare(strict_types=1);

/**
 * Contexte multi-tenant : organisation active, rôles, prédicats SQL pour le CRM.
 * Le superadmin peut basculer « toutes organisations » via ?tenant_scope=all (session).
 */
final class TenantContext
{
    private static ?int $cachedDefaultOrgId = null;

    public static function isSuperadmin(): bool
    {
        return (string) ($_SESSION['user_role'] ?? '') === 'superadmin';
    }

    /**
     * Voir toutes les organisations (leads non filtrés par organization_id).
     * Réservé au superadmin ; forcé à false pour les autres rôles.
     */
    public static function viewingAllOrganizations(): bool
    {
        return self::isSuperadmin() && !empty($_SESSION['tenant_view_all']);
    }

    /**
     * ID d’organisation actif en session (null si non hydraté).
     */
    public static function activeOrganizationId(): ?int
    {
        $v = $_SESSION['active_organization_id'] ?? null;
        if ($v === null || $v === '') {
            return null;
        }

        return (int) $v;
    }

    public static function organizationRole(): ?string
    {
        $r = $_SESSION['organization_role'] ?? null;

        return $r !== null && $r !== '' ? (string) $r : null;
    }

    /**
     * Organisation utilisée pour les soumissions publiques (formulaires → crm_leads).
     * = organisation marquée is_default, sinon la première par id.
     */
    public static function leadCaptureOrganizationId(): int
    {
        if (self::$cachedDefaultOrgId !== null) {
            return self::$cachedDefaultOrgId;
        }

        try {
            $pdo = db();
            $stmt = $pdo->query(
                "SELECT id FROM organizations WHERE slug = 'legacy-default' AND status IN ('active','trial') ORDER BY id ASC LIMIT 1"
            );
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            if ($row && isset($row['id'])) {
                self::$cachedDefaultOrgId = (int) $row['id'];

                return self::$cachedDefaultOrgId;
            }
            $stmt = $pdo->query(
                "SELECT id FROM organizations WHERE status IN ('active','trial') ORDER BY id ASC LIMIT 1"
            );
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
            if ($row && isset($row['id'])) {
                self::$cachedDefaultOrgId = (int) $row['id'];

                return self::$cachedDefaultOrgId;
            }
        } catch (Throwable $e) {
            error_log('TenantContext::leadCaptureOrganizationId: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Prédicat SQL + paramètres pour filtrer crm_leads selon le tenant.
     *
     * @return array{sql: string, params: array<string, int>}
     */
    public static function crmLeadsTenantPredicate(string $tableAlias = ''): array
    {
        if (self::viewingAllOrganizations()) {
            return ['sql' => '1=1', 'params' => []];
        }

        $col = ($tableAlias === '' ? '' : $tableAlias . '.') . 'organization_id';
        $orgId = self::activeOrganizationId();
        if ($orgId === null || $orgId <= 0) {
            return ['sql' => '1=0', 'params' => []];
        }

        return ['sql' => $col . ' = :__tenant_org_id', 'params' => [':__tenant_org_id' => $orgId]];
    }

    /**
     * Applique le filtre tenant à une requête existante (AND …).
     *
     * @param array<string, mixed> $params
     */
    public static function appendCrmLeadTenantFilter(string &$sql, array &$params, string $tableAlias = ''): void
    {
        $p = self::crmLeadsTenantPredicate($tableAlias);
        if ($p['sql'] === '1=1') {
            return;
        }
        $sql .= ' AND (' . $p['sql'] . ')';
        foreach ($p['params'] as $k => $v) {
            $params[$k] = $v;
        }
    }

    public static function canAccessCrmLeadRow(?array $lead): bool
    {
        if ($lead === null) {
            return false;
        }
        if (self::viewingAllOrganizations()) {
            return true;
        }
        $orgId = self::activeOrganizationId();
        if ($orgId === null || $orgId <= 0) {
            return false;
        }
        $rowOrg = isset($lead['organization_id']) ? (int) $lead['organization_id'] : null;
        if ($rowOrg === null || $rowOrg === 0) {
            return false;
        }

        return $rowOrg === $orgId;
    }

    public static function setViewAllOrganizations(bool $on): void
    {
        if (!self::isSuperadmin()) {
            $_SESSION['tenant_view_all'] = false;

            return;
        }
        $_SESSION['tenant_view_all'] = $on;
    }
}
