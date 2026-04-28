<?php
declare(strict_types=1);

/**
 * INSERT « scraping eXp » adapté au schéma réel de la table biens (colonnes optionnelles selon migrations).
 */

/** @return array<string, true> */
function scraping_import_biens_column_set(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $st = $pdo->prepare(
        'SELECT COLUMN_NAME FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
    );
    $st->execute(['biens']);
    $cache = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $n = (string) ($r['COLUMN_NAME'] ?? '');
        if ($n !== '') {
            $cache[$n] = true;
        }
    }

    return $cache;
}

/**
 * @param array<string, mixed> $row colonne SQL => valeur
 * @return array{0: string, 1: array<string, mixed>} SQL + exécuteur PDO
 */
function scraping_import_build_insert(PDO $pdo, array $row): array
{
    $set = scraping_import_biens_column_set($pdo);

    $allowed = [
        'slug' => true, 'titre' => true, 'description' => true,
        'type_transaction' => true, 'transaction' => true, 'transaction_type' => true,
        'type_bien' => true, 'prix' => true, 'surface' => true, 'pieces' => true, 'chambres' => true,
        'sdb' => true, 'etage' => true, 'adresse' => true, 'ville' => true, 'code_postal' => true,
        'secteur' => true, 'latitude' => true, 'longitude' => true, 'caracteristiques' => true,
        'dpe_classe' => true, 'ges_classe' => true, 'mode_chauffage' => true, 'visite_virtuelle_url' => true,
        'annee_construction' => true, 'statut' => true, 'etat_bien' => true, 'exclusif' => true,
        'a_parking' => true, 'a_jardin' => true, 'a_piscine' => true, 'a_terrasse' => true,
        'a_balcon' => true, 'a_ascenseur' => true, 'photo_principale' => true, 'reference' => true,
        'agent_id' => true, 'source' => true, 'source_externe_id' => true, 'source_agent_nom' => true,
        'publier_vitrine' => true,
    ];

    $defaults = [
        'etat_bien' => 'good',
        'exclusif' => 0,
        'a_parking' => 0,
        'a_jardin' => 0,
        'a_piscine' => 0,
        'a_ascenseur' => 0,
        'caracteristiques' => null,
        'ges_classe' => null,
        'mode_chauffage' => null,
        'visite_virtuelle_url' => null,
        'secteur' => null,
        'etage' => null,
    ];

    foreach ($defaults as $k => $v) {
        if (isset($set[$k]) && !array_key_exists($k, $row)) {
            $row[$k] = $v;
        }
    }

    // Schémas hétérogènes : vente/location peut être stocké sous type_transaction, transaction ou transaction_type.
    if (array_key_exists('type_transaction', $row)) {
        $txVal = $row['type_transaction'];
        unset($row['type_transaction']);
        foreach (['type_transaction', 'transaction', 'transaction_type'] as $txCol) {
            if (isset($set[$txCol])) {
                $row[$txCol] = $txVal;
                break;
            }
        }
    }

    $cols = [];
    $ph = [];
    $params = [];
    foreach ($row as $col => $val) {
        if (!isset($allowed[$col]) || !isset($set[$col])) {
            continue;
        }
        if (!preg_match('/^[a-z0-9_]+$/i', $col)) {
            continue;
        }
        $cols[] = '`' . $col . '`';
        $ph[] = ':' . $col;
        $params[':' . $col] = $val;
    }

    if ($cols === []) {
        throw new RuntimeException(
            'Table biens sans colonnes reconnues pour l’import (slug, titre, prix…). Vérifiez la base.'
        );
    }

    $sql = 'INSERT INTO biens (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $ph) . ')';

    return [$sql, $params];
}
