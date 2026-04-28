<?php
declare(strict_types=1);

/**
 * Publication catalogue public /biens (colonne migr. 039 publier_vitrine).
 */

function biens_sql_publier_vitrine_ok(string $alias = 'b'): string
{
    $p = trim($alias) === '' ? '' : ($alias . '.');
    return '(' . $p . 'publier_vitrine IS NULL OR ' . $p . 'publier_vitrine = 1)';
}
