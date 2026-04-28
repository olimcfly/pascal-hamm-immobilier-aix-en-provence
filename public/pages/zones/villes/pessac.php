<?php
declare(strict_types=1);
/**
 * Ancienne fiche (héritage d’URL) — le territoire couvert par le site est Aix & Pays d’Aix.
 * En navigation normale, le routeur envoie déjà une 301 vers /immobilier/aix-en-provence.
 */
header('Location: /immobilier/aix-en-provence', true, 301);
exit;
