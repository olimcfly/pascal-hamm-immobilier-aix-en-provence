<?php
declare(strict_types=1);
/**
 * Ancienne fiche (héritage d’URL) — le site couvre Aix-en-Provence et le Pays d’Aix.
 * Le routeur envoie normalement une 301 vers la fiche territoire unifiée.
 */
header('Location: /immobilier/aix-en-provence', true, 301);
exit;
