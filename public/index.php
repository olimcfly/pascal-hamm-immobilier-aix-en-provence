<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

$target = '/admin/login.php';

if (!headers_sent()) {
    header('Location: ' . $target, true, 302);
    exit;
}

echo '<!doctype html><html lang="fr"><head><meta charset="utf-8"><title>Redirection</title></head><body>';
echo '<p>Redirection vers l\'espace d\'administration… <a href="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '">Continuer</a></p>';
echo '</body></html>';
