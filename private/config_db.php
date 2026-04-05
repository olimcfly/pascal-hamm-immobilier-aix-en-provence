<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'cool1019_sitevirine');
define('DB_USER', 'cool1019_userdb');
define('DB_PASS', 'xf6jqhckqhmfobim5');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3306');

// Configuration de l'application
define('APP_NAME', 'CRM Immobilier');
define('APP_URL', 'http://pascal-hamm-immobilier-aix-en-provence.fr');
define('APP_EMAIL', 'contact@pascal-hamm-immobilier-aix-en-provence.fr');
define('APP_DEBUG', true);
define('APP_ENV', 'production');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CORE_PATH', ROOT_PATH . '/core');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
define('UPLOAD_URL', APP_URL . '/assets/images/uploads');

