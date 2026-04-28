<?php

declare(strict_types=1);

class LocalPartnerService
{
    public function ensureSchema(): void
    {
        db()->exec(
            "CREATE TABLE IF NOT EXISTS local_partner_categories (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nom VARCHAR(120) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        db()->exec(
            "CREATE TABLE IF NOT EXISTS local_partners (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nom VARCHAR(180) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                categorie_id INT UNSIGNED DEFAULT NULL,
                description_courte VARCHAR(320) DEFAULT NULL,
                description_longue MEDIUMTEXT DEFAULT NULL,
                adresse VARCHAR(255) DEFAULT NULL,
                ville VARCHAR(120) DEFAULT NULL,
                code_postal VARCHAR(20) DEFAULT NULL,
                telephone VARCHAR(40) DEFAULT NULL,
                email VARCHAR(160) DEFAULT NULL,
                site_web VARCHAR(255) DEFAULT NULL,
                logo VARCHAR(255) DEFAULT NULL,
                latitude DECIMAL(10,8) DEFAULT NULL,
                longitude DECIMAL(11,8) DEFAULT NULL,
                place_id VARCHAR(200) DEFAULT NULL,
                google_maps_url VARCHAR(255) DEFAULT NULL,
                horaires JSON DEFAULT NULL,
                reseaux_sociaux JSON DEFAULT NULL,
                statut_actif TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_local_partners_status (statut_actif),
                KEY idx_local_partners_geo (latitude, longitude),
                KEY idx_local_partners_category (categorie_id),
                CONSTRAINT fk_local_partners_category FOREIGN KEY (categorie_id) REFERENCES local_partner_categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public function getPublicList(float $centerLat, float $centerLng, float $radiusKm, ?int $categoryId = null): array
    {
        $radiusKm = max(0.5, $radiusKm);
        [$minLat, $maxLat, $minLng, $maxLng] = $this->boundingBox($centerLat, $centerLng, $radiusKm);

        $params = [
            ':center_lat'  => $centerLat,
            ':center_lat2' => $centerLat,
            ':center_lng'  => $centerLng,
            ':radius_km'   => $radiusKm,
            ':min_lat'     => $minLat,
            ':max_lat'     => $maxLat,
            ':min_lng'     => $minLng,
            ':max_lng'     => $maxLng,
        ];

        $categorySql = '';
        if ($categoryId !== null && $categoryId > 0) {
            $categorySql = ' AND p.categorie_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $sql = "SELECT
                    p.id,
                    p.nom,
                    p.slug,
                    p.description_courte,
                    p.adresse,
                    p.ville,
                    p.code_postal,
                    p.telephone,
                    p.site_web,
                    p.logo,
                    p.latitude,
                    p.longitude,
                    p.google_maps_url,
                    c.nom AS categorie,
                    (
                        6371 * ACOS(
                            COS(RADIANS(:center_lat)) * COS(RADIANS(p.latitude))
                            * COS(RADIANS(p.longitude) - RADIANS(:center_lng))
                            + SIN(RADIANS(:center_lat2)) * SIN(RADIANS(p.latitude))
                        )
                    ) AS distance_km
                FROM local_partners p
                LEFT JOIN local_partner_categories c ON c.id = p.categorie_id
                WHERE p.statut_actif = 1
                  AND p.latitude IS NOT NULL
                  AND p.longitude IS NOT NULL
                  AND p.latitude BETWEEN :min_lat AND :max_lat
                  AND p.longitude BETWEEN :min_lng AND :max_lng
                  {$categorySql}
                HAVING distance_km <= :radius_km
                ORDER BY distance_km ASC, p.nom ASC";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, c.nom AS categorie
             FROM local_partners p
             LEFT JOIN local_partner_categories c ON c.id = p.categorie_id
             WHERE p.slug = :slug
             LIMIT 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getCategories(): array
    {
        $stmt = db()->query('SELECT id, nom, slug FROM local_partner_categories ORDER BY nom ASC');
        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    public function getAllForAdmin(): array
    {
        $stmt = db()->query(
            'SELECT p.*, c.nom AS categorie
             FROM local_partners p
             LEFT JOIN local_partner_categories c ON c.id = p.categorie_id
             ORDER BY p.updated_at DESC, p.id DESC'
        );

        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    public function save(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = slugify((string) ($data['nom'] ?? ''));
        }

        $payload = [
            ':nom' => trim((string) ($data['nom'] ?? '')),
            ':slug' => $slug,
            ':categorie_id' => !empty($data['categorie_id']) ? (int) $data['categorie_id'] : null,
            ':description_courte' => trim((string) ($data['description_courte'] ?? '')),
            ':description_longue' => trim((string) ($data['description_longue'] ?? '')),
            ':adresse' => trim((string) ($data['adresse'] ?? '')),
            ':ville' => trim((string) ($data['ville'] ?? '')),
            ':code_postal' => trim((string) ($data['code_postal'] ?? '')),
            ':telephone' => trim((string) ($data['telephone'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':site_web' => trim((string) ($data['site_web'] ?? '')),
            ':logo' => trim((string) ($data['logo'] ?? '')),
            ':latitude' => $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            ':longitude' => $data['longitude'] !== '' ? (float) $data['longitude'] : null,
            ':place_id' => trim((string) ($data['place_id'] ?? '')),
            ':google_maps_url' => trim((string) ($data['google_maps_url'] ?? '')),
            ':statut_actif' => !empty($data['statut_actif']) ? 1 : 0,
        ];

        if ($id > 0) {
            $payload[':id'] = $id;
            $stmt = db()->prepare(
                'UPDATE local_partners SET
                    nom = :nom,
                    slug = :slug,
                    categorie_id = :categorie_id,
                    description_courte = :description_courte,
                    description_longue = :description_longue,
                    adresse = :adresse,
                    ville = :ville,
                    code_postal = :code_postal,
                    telephone = :telephone,
                    email = :email,
                    site_web = :site_web,
                    logo = :logo,
                    latitude = :latitude,
                    longitude = :longitude,
                    place_id = :place_id,
                    google_maps_url = :google_maps_url,
                    statut_actif = :statut_actif,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute($payload);
            return $id;
        }

        $stmt = db()->prepare(
            'INSERT INTO local_partners (
                nom, slug, categorie_id, description_courte, description_longue, adresse, ville, code_postal,
                telephone, email, site_web, logo, latitude, longitude, place_id, google_maps_url, statut_actif
            ) VALUES (
                :nom, :slug, :categorie_id, :description_courte, :description_longue, :adresse, :ville, :code_postal,
                :telephone, :email, :site_web, :logo, :latitude, :longitude, :place_id, :google_maps_url, :statut_actif
            )'
        );
        $stmt->execute($payload);

        return (int) db()->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM local_partners WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    private function boundingBox(float $lat, float $lng, float $radiusKm): array
    {
        $earthRadiusKm = 6371;
        $latDelta = rad2deg($radiusKm / $earthRadiusKm);
        $lngDelta = rad2deg($radiusKm / ($earthRadiusKm * cos(deg2rad($lat))));

        return [$lat - $latDelta, $lat + $latDelta, $lng - $lngDelta, $lng + $lngDelta];
    }
}
