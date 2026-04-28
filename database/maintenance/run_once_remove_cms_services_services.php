<?php
/**
 * Maintenance one-shot : sauvegarde puis suppression de la ligne cms_pages
 * slug=services-services (orphelin). Ne supprime pas `services`.
 *
 * Usage (CLI) : php database/maintenance/run_once_remove_cms_services_services.php
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/core/bootstrap.php';

$pdo = db();
$siteId = 1;

echo "=== 1) Lignes services / services-services ===\n";
$st = $pdo->prepare(
    'SELECT id, slug, title, status, updated_at FROM cms_pages WHERE slug IN (?, ?) AND site_id = ? ORDER BY slug'
);
$st->execute(['services', 'services-services', $siteId]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
$hasOrphan = false;
foreach ($rows as $r) {
    if (($r['slug'] ?? '') === 'services-services') {
        $hasOrphan = true;
    }
}
if (!$hasOrphan) {
    echo "\nAucune ligne services-services pour site_id={$siteId} — rien à supprimer.\n";
    echo "Vérification finale slugs contenant 'services' :\n";
    $v = $pdo->query("SELECT id, slug, title, status, updated_at FROM cms_pages WHERE site_id = {$siteId} AND slug LIKE '%services%'");
    while ($row = $v->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    exit(0);
}

echo "\n=== 2) Sauvegarde (CREATE TABLE … AS SELECT, une ligne orpheline) ===\n";
$sqlBackup = 'CREATE TABLE IF NOT EXISTS cms_pages_backup_services_services AS
    SELECT * FROM cms_pages
    WHERE site_id = ' . (int) $siteId . " AND slug = 'services-services'";
$pdo->exec($sqlBackup);
$n = (int) $pdo->query('SELECT COUNT(*) FROM cms_pages_backup_services_services')->fetchColumn();
echo "Lignes dans cms_pages_backup_services_services : {$n}\n";

echo "\n=== 3) DELETE ligne orpheline uniquement ===\n";
$del = $pdo->prepare('DELETE FROM cms_pages WHERE site_id = ? AND slug = ? LIMIT 1');
$del->execute([$siteId, 'services-services']);
echo "Lignes supprimées : " . $del->rowCount() . "\n";

echo "\n=== 4) Vérification finale (slug LIKE %services%) ===\n";
$v = $pdo->prepare('SELECT id, slug, title, status, updated_at FROM cms_pages WHERE site_id = ? AND slug LIKE ?');
$v->execute([$siteId, '%services%']);
while ($row = $v->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nTerminé. La ligne `services` doit apparaître seule (pour ce filtre).\n";
