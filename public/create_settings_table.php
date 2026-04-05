<?php
require_once __DIR__ . '/../private/config.php';

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT.";charset=".DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() === 0) {
        // Créer la table
        $pdo->exec("
            CREATE TABLE `settings` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `setting_key` varchar(255) NOT NULL,
              `setting_value` text,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "La table settings a été créée avec succès.";
    } else {
        echo "La table settings existe déjà.";
    }

    // Vérifier la structure de la table
    $stmt = $pdo->query("DESCRIBE settings");
    echo "<h3>Structure de la table settings :</h3>";
    echo "<pre>";
    print_r($stmt->fetchAll());
    echo "</pre>";

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
