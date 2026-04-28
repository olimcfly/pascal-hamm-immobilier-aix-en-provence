<?php
declare(strict_types=1);

/**
 * Hydrate active_organization_id / organization_role après login.
 * À appeler avec un PDO déjà connecté (même base que l’admin).
 */
function saas_hydrate_organization_session(PDO $pdo): void
{
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        return;
    }

    $platformRole = (string) ($_SESSION['user_role'] ?? '');

    try {
        $stmt = $pdo->prepare(
            'SELECT m.organization_id, m.role AS org_role
             FROM memberships m
             INNER JOIN organizations o ON o.id = m.organization_id AND o.status IN (\'active\', \'trial\')
             WHERE m.user_id = ? AND m.status = \'active\'
             ORDER BY m.organization_id ASC'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('saas_hydrate_organization_session: ' . $e->getMessage());
        $_SESSION['active_organization_id'] = null;
        $_SESSION['organization_role'] = null;

        return;
    }

    if ($rows === []) {
        $_SESSION['active_organization_id'] = null;
        $_SESSION['organization_role'] = null;

        return;
    }

    $current = isset($_SESSION['active_organization_id']) ? (int) $_SESSION['active_organization_id'] : 0;
    $matched = false;
    foreach ($rows as $r) {
        if ((int) ($r['organization_id'] ?? 0) === $current) {
            $_SESSION['organization_role'] = (string) ($r['org_role'] ?? 'editor');
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        $_SESSION['active_organization_id'] = (int) ($rows[0]['organization_id'] ?? 0);
        $_SESSION['organization_role'] = (string) ($rows[0]['org_role'] ?? 'editor');
    } else {
        $_SESSION['active_organization_id'] = $current;
    }

    if ($platformRole === 'superadmin') {
        if (!array_key_exists('tenant_view_all', $_SESSION)) {
            $_SESSION['tenant_view_all'] = false;
        }
    } else {
        $_SESSION['tenant_view_all'] = false;
    }
}

/**
 * Si la session admin n’a pas encore d’organisation active, tente un hydrate (ex. après déploiement migration).
 */
function saas_ensure_tenant_session(PDO $pdo): void
{
    if (empty($_SESSION['user_id'])) {
        return;
    }
    $aid = $_SESSION['active_organization_id'] ?? null;
    if ($aid !== null && $aid !== '' && (int) $aid > 0) {
        return;
    }
    saas_hydrate_organization_session($pdo);
}
