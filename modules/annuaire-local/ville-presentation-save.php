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

$slug = trim((string) ($_POST['ville_slug'] ?? ''));
$slug = preg_replace('/[^a-z0-9-]/', '', $slug) ?? '';
$description = trim((string) ($_POST['description'] ?? ''));
$imageUrl    = trim((string) ($_POST['image_url'] ?? ''));
$codePostal  = trim((string) ($_POST['code_postal'] ?? ''));
$codePostal  = preg_replace('/\D/', '', $codePostal);
$codePostal  = $codePostal !== '' ? substr($codePostal, 0, 5) : null;

if ($slug === '') {
    $annRedir(['module' => 'annuaire-local', 'action' => 'edit-ville', 'error' => 'Slug manquant.']);
}

try {
    $pdo = db();
    $st  = $pdo->prepare('UPDATE villes SET description = ?, image_url = ?, code_postal = COALESCE(?, code_postal), updated_at = CURRENT_TIMESTAMP WHERE slug = ? AND actif = 1');
    $st->execute([
        $description !== '' ? $description : null,
        $imageUrl !== '' ? $imageUrl : null,
        $codePostal,
        $slug,
    ]);
    if ($st->rowCount() === 0) {
        $annRedir(['module' => 'annuaire-local', 'action' => 'edit-ville', 'slug' => $slug, 'error' => 'Aucune ligne mise à jour.']);
    }
} catch (Throwable $e) {
    error_log('[ville-presentation-save] ' . $e->getMessage());
    $annRedir(['module' => 'annuaire-local', 'action' => 'edit-ville', 'slug' => $slug, 'error' => 'Erreur enregistrement.']);
}

$annRedir(['module' => 'annuaire-local', 'action' => 'edit-ville', 'slug' => $slug, 'ville_saved' => '1']);
