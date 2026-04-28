<?php
declare(strict_types=1);

$annRedir = static function (array $query): never {
    $url = function_exists('admin_url')
        ? admin_url($query)
        : ('/admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    header('Location: ' . $url);
    exit;
};

if (!function_exists('csrfToken')) {
    $annRedir(['module' => 'annuaire-local', 'error' => 'Session indisponible']);
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $token)) {
    $annRedir(['module' => 'annuaire-local', 'error' => 'Jeton CSRF invalide']);
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    $annRedir(['module' => 'annuaire-local', 'error' => 'POI invalide']);
}

try {
    $pdo = db();
    $pdo->prepare('DELETE FROM guide_pois WHERE id = ? LIMIT 1')->execute([$id]);
} catch (Throwable $e) {
    error_log('[annuaire-local poi-delete] ' . $e->getMessage());
    $annRedir(['module' => 'annuaire-local', 'error' => 'Suppression impossible.']);
}

$annRedir(['module' => 'annuaire-local', 'deleted' => '1']);
