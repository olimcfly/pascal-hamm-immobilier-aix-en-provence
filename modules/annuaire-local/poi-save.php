<?php
declare(strict_types=1);

/**
 * Traitement POST création / édition POI (inclus depuis accueil.php).
 */
$annRedir = static function (array $query): never {
    $url = function_exists('admin_url')
        ? admin_url($query)
        : ('/admin/?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    header('Location: ' . $url);
    exit;
};

if (!function_exists('csrfToken')) {
    $annRedir(['module' => 'annuaire-local', 'error' => 'Session / helpers indisponibles']);
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (!hash_equals(csrfToken(), $token)) {
    $annRedir(['module' => 'annuaire-local', 'error' => 'Jeton CSRF invalide']);
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim((string) ($_POST['name'] ?? ''));
$slugInput = trim((string) ($_POST['slug'] ?? ''));
$categoryId = (int) ($_POST['category_id'] ?? 0);
$villeId = (int) ($_POST['ville_id'] ?? 0) ?: null;
$quartierId = (int) ($_POST['quartier_id'] ?? 0) ?: null;
$description = trim((string) ($_POST['description'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$website = trim((string) ($_POST['website'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$facebook = trim((string) ($_POST['facebook'] ?? ''));
$instagram = trim((string) ($_POST['instagram'] ?? ''));
$postalCode = trim((string) ($_POST['postal_code'] ?? ''));
$seoKeywords = trim((string) ($_POST['seo_keywords'] ?? ''));
$openingHours = trim((string) ($_POST['opening_hours'] ?? ''));
$isActive = isset($_POST['is_active']) ? 1 : 0;
$isVerified = isset($_POST['is_verified']) ? 1 : 0;
$lat = trim((string) ($_POST['latitude'] ?? ''));
$lng = trim((string) ($_POST['longitude'] ?? ''));
$ratingIn = trim((string) ($_POST['rating'] ?? ''));
$rating = $ratingIn !== '' && is_numeric($ratingIn) ? (float) $ratingIn : null;
if ($rating !== null) {
    $rating = min(5, max(0, $rating));
}

if ($name === '' || $categoryId <= 0) {
    $q = ['module' => 'annuaire-local', 'error' => 'Nom et catégorie sont obligatoires.'];
    if ($id > 0) {
        $q['action'] = 'poi-edit';
        $q['id'] = $id;
    } else {
        $q['action'] = 'poi-new';
    }
    $annRedir($q);
}

if ($villeId === null && $quartierId === null) {
    $q = ['module' => 'annuaire-local', 'error' => 'Choisissez une ville ou un quartier.'];
    if ($id > 0) {
        $q['action'] = 'poi-edit';
        $q['id'] = $id;
    } else {
        $q['action'] = 'poi-new';
    }
    $annRedir($q);
}

$pdo = db();

if ($quartierId !== null) {
    $qv = $pdo->prepare('SELECT ville_id FROM quartiers WHERE id = ? LIMIT 1');
    $qv->execute([$quartierId]);
    $rowV = $qv->fetch(PDO::FETCH_ASSOC);
    if (!$rowV) {
        $annRedir(['module' => 'annuaire-local', 'action' => 'poi-new', 'error' => 'Quartier invalide.']);
    }
    $villeId = (int) $rowV['ville_id'];
}

$slug = $slugInput !== '' ? annuaire_local_slugify($slugInput) : annuaire_local_slugify($name);
$slug = substr($slug, 0, 190) ?: 'poi';

$uniq = $pdo->prepare('SELECT id FROM guide_pois WHERE slug = ? AND id <> ? LIMIT 1');
$uniq->execute([$slug, $id]);
if ($uniq->fetch()) {
    $slug .= '-' . bin2hex(random_bytes(3));
}

$latitude = $lat !== '' && is_numeric($lat) ? (float) $lat : null;
$longitude = $lng !== '' && is_numeric($lng) ? (float) $lng : null;

$featuredImage = null;
if ($id > 0) {
    $ex = $pdo->prepare('SELECT featured_image FROM guide_pois WHERE id = ? LIMIT 1');
    $ex->execute([$id]);
    $featuredImage = $ex->fetchColumn() ?: null;
}

if (!empty($_FILES['featured_image']['tmp_name']) && is_uploaded_file((string) $_FILES['featured_image']['tmp_name'])) {
    $err = (int) ($_FILES['featured_image']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_OK) {
        $orig = (string) ($_FILES['featured_image']['name'] ?? '');
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_IMG, true)) {
            $q = ['module' => 'annuaire-local', 'error' => 'Image : formats jpg, png, webp uniquement.'];
            if ($id > 0) {
                $q['action'] = 'poi-edit';
                $q['id'] = $id;
            } else {
                $q['action'] = 'poi-new';
            }
            $annRedir($q);
        }
        if ((int) ($_FILES['featured_image']['size'] ?? 0) > (defined('MAX_FILE_SIZE') ? (int) MAX_FILE_SIZE : 5242880)) {
            $q = ['module' => 'annuaire-local', 'error' => 'Fichier trop volumineux.'];
            if ($id > 0) {
                $q['action'] = 'poi-edit';
                $q['id'] = $id;
            } else {
                $q['action'] = 'poi-new';
            }
            $annRedir($q);
        }
        $dir = rtrim(UPLOAD_PATH, '/') . '/guide-poi';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $annRedir(['module' => 'annuaire-local', 'error' => 'Impossible de créer le dossier upload.']);
        }
        $fileName = 'poi_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $fileName;
        if (move_uploaded_file((string) $_FILES['featured_image']['tmp_name'], $dest)) {
            $featuredImage = '/storage/uploads/guide-poi/' . $fileName;
        }
    }
}

$quartierIdSave = (int) ($_POST['quartier_id'] ?? 0) ?: null;

try {
    if ($id <= 0) {
        $ins = $pdo->prepare(
            'INSERT INTO guide_pois (
                ville_id, quartier_id, category_id, name, slug, description, seo_keywords, address, postal_code,
                latitude, longitude, phone, website, email, facebook, instagram, opening_hours, featured_image,
                is_active, is_verified, rating, reviews_count
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $ins->execute([
            $villeId,
            $quartierIdSave,
            $categoryId,
            $name,
            $slug,
            $description !== '' ? $description : null,
            $seoKeywords !== '' ? $seoKeywords : null,
            $address !== '' ? $address : null,
            $postalCode !== '' ? $postalCode : null,
            $latitude,
            $longitude,
            $phone !== '' ? $phone : null,
            $website !== '' ? $website : null,
            $email !== '' ? $email : null,
            $facebook !== '' ? $facebook : null,
            $instagram !== '' ? $instagram : null,
            $openingHours !== '' ? $openingHours : null,
            $featuredImage,
            $isActive,
            $isVerified,
            $rating,
            0,
        ]);
        $newId = (int) $pdo->lastInsertId();
        $annRedir(['module' => 'annuaire-local', 'saved' => '1', 'highlight' => $newId]);
    }

    $upd = $pdo->prepare(
        'UPDATE guide_pois SET ville_id = ?, quartier_id = ?, category_id = ?, name = ?, slug = ?, description = ?,
         seo_keywords = ?, address = ?, postal_code = ?, latitude = ?, longitude = ?, phone = ?, website = ?,
         email = ?, facebook = ?, instagram = ?, opening_hours = ?, featured_image = COALESCE(?, featured_image), is_active = ?,
         is_verified = ?, rating = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
    );
    $upd->execute([
        $villeId,
        $quartierIdSave,
        $categoryId,
        $name,
        $slug,
        $description !== '' ? $description : null,
        $seoKeywords !== '' ? $seoKeywords : null,
        $address !== '' ? $address : null,
        $postalCode !== '' ? $postalCode : null,
        $latitude,
        $longitude,
        $phone !== '' ? $phone : null,
        $website !== '' ? $website : null,
        $email !== '' ? $email : null,
        $facebook !== '' ? $facebook : null,
        $instagram !== '' ? $instagram : null,
        $openingHours !== '' ? $openingHours : null,
        $featuredImage,
        $isActive,
        $isVerified,
        $rating,
        $id,
    ]);
    $annRedir(['module' => 'annuaire-local', 'saved' => '1', 'highlight' => $id]);
} catch (Throwable $e) {
    error_log('[annuaire-local poi-save] ' . $e->getMessage());
    $q = ['module' => 'annuaire-local', 'error' => 'Erreur enregistrement (slug dupliqué ou données invalides).'];
    if ($id > 0) {
        $q['action'] = 'poi-edit';
        $q['id'] = $id;
    } else {
        $q['action'] = 'poi-new';
    }
    $annRedir($q);
}
