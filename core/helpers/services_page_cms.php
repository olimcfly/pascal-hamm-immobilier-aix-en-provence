<?php
declare(strict_types=1);

/**
 * Pilote CMS — /services (slug cms_pages: services)
 */

function cms_services_page_slug(): string
{
    return 'services';
}

/**
 * 4 offres (source unique ; converties en tuples pour le front).
 *
 * @return list<array{icon: string, title: string, description: string, benefits: list<string>, button_label: string, button_url: string}>
 */
function cms_services_default_services_blocks_assoc(): array
{
    return [
        [
            'icon' => '🏠',
            'title' => 'Vente de votre bien',
            'description' => "Je vous accompagne de l'estimation à la signature chez le notaire.",
            'benefits' => [
                'Estimation précise au prix du marché',
                'Photos professionnelles & home staging',
                'Diffusion sur +30 portails immobiliers',
                'Sélection et accompagnement des acquéreurs',
                "Négociation et suivi jusqu'à la signature",
            ],
            'button_label' => 'Estimer mon bien',
            'button_url' => '/estimation-gratuite',
        ],
        [
            'icon' => '🔑',
            'title' => "Recherche d'un bien à acheter",
            'description' => 'Je trouve le bien qui correspond exactement à vos critères.',
            'benefits' => [
                'Définition précise de votre projet',
                'Accès à des biens hors-marché',
                'Visites accompagnées et conseillées',
                'Analyse de la valeur avant offre',
                "Accompagnement jusqu'à la remise des clés",
            ],
            'button_label' => 'Démarrer ma recherche',
            'button_url' => '/contact',
        ],
        [
            'icon' => '📊',
            'title' => 'Estimation gratuite',
            'description' => 'Connaissez la vraie valeur de votre bien en moins de 48h.',
            'benefits' => [
                'Analyse comparative du marché',
                'Visite du bien et évaluation terrain',
                "Rapport d'estimation détaillé",
                'Conseils pour valoriser votre bien',
                'Sans engagement, 100% gratuit',
            ],
            'button_label' => 'Demander une estimation',
            'button_url' => '/estimation-gratuite',
        ],
        [
            'icon' => '💼',
            'title' => 'Investissement locatif',
            'description' => 'Maximisez votre rendement locatif avec une stratégie adaptée.',
            'benefits' => [
                'Analyse des zones à fort potentiel',
                'Calcul de rentabilité détaillé',
                'Sélection de biens adaptés à votre profil',
                'Conseils fiscaux (dispositifs Pinel, LMNP…)',
                'Gestion locative optionnelle',
            ],
            'button_label' => 'Étudier mon investissement',
            'button_url' => '/contact',
        ],
    ];
}

/**
 * @param array{0: string, 1: string, 2: string, 3: list<string>, 4: string, 5: string} $def
 * @return array{0: string, 1: string, 2: string, 3: list<string>, 4: string, 5: string}
 */
function services_block_assoc_to_tuple(array $b, array $def): array
{
    $ben = $b['benefits'] ?? [];
    if (is_string($ben)) {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $ben) ?: [])));
    } else {
        $lines = array_values(array_map(static fn($x) => trim((string) $x), (array) $ben));
    }
    if ($lines === []) {
        $lines = $def[3];
    }
    $icon = trim((string) ($b['icon'] ?? ''));
    $title = trim((string) ($b['title'] ?? ''));
    $desc = trim((string) ($b['description'] ?? ''));
    $btn = trim((string) ($b['button_label'] ?? ''));
    $url = trim((string) ($b['button_url'] ?? ''));

    return [
        $icon !== '' ? $icon : $def[0],
        $title !== '' ? $title : $def[1],
        $desc !== '' ? $desc : $def[2],
        $lines,
        $url !== '' ? $url : $def[4],
        $btn !== '' ? $btn : $def[5],
    ];
}

/**
 * @return list<array{0: string, 1: string, 2: string, 3: list<string>, 4: string, 5: string}>
 */
function services_page_resolved_service_tuples(array $sc): array
{
    $defaults = cms_services_default_service_blocks();
    $raw = $sc['services_blocks'] ?? null;
    if (!is_array($raw) || $raw === []) {
        return $defaults;
    }
    $out = [];
    for ($i = 0; $i < 4; $i++) {
        $b = $raw[$i] ?? null;
        if (!is_array($b) || trim((string) ($b['title'] ?? '')) === '') {
            $out[] = $defaults[$i];
            continue;
        }
        $out[] = services_block_assoc_to_tuple($b, $defaults[$i]);
    }

    return $out;
}

/**
 * @return array<string, mixed>
 */
function cms_services_default_content(): array
{
    return [
        'hero' => [
            'eyebrow' => 'Services',
            'h1' => 'Mes services',
            'subtitle' => '',
        ],
        'intro' => [
            'title' => '',
            'text' => '',
        ],
        'services_blocks' => cms_services_default_services_blocks_assoc(),
        'cta' => [
            'title' => 'Commençons votre projet',
            'text' => "Discutons de vos besoins lors d'un premier échange gratuit et sans engagement.",
            'button_label' => 'Prendre rendez-vous',
            'button_url' => '/prendre-rendez-vous',
        ],
        'pricing' => [
            'section_label' => 'Transparence',
            'section_title' => 'Des honoraires clairs',
            'section_subtitle' => "Pas de surprise : mes honoraires sont fixés à l'avance et intégrés dans le prix de vente.",
            'rows' => [
                ['name' => 'Vente', 'price' => '4 à 6%', 'desc' => 'du prix de vente TTC, honoraires vendeur. Négociable selon la valeur du bien.'],
                ['name' => 'Achat', 'price' => '2 à 3%', 'desc' => "du prix d'achat TTC, honoraires acquéreur. Service de recherche clé en main."],
                ['name' => 'Estimation', 'price' => 'Gratuit', 'desc' => 'Estimation et rapport écrit. Sans engagement et sans condition.'],
            ],
        ],
        'seo' => [
            'focus_keyword' => 'services immobiliers Aix-en-Provence',
            'og_image_alt' => '',
        ],
    ];
}

/**
 * Repli contenu (chaînes vides ou blocs partiels) sur les valeurs par défaut — aligné front / admin.
 *
 * @param array<string, mixed> $sc
 * @param array<string, mixed> $base
 * @return array<string, mixed>
 */
function cms_services_coalesce_with_defaults(array $sc, array $base, string $defaultSubtitleFallback): array
{
    if (!is_array($sc['hero'] ?? null)) {
        $sc['hero'] = $base['hero'];
    } else {
        if (trim((string) ($sc['hero']['subtitle'] ?? '')) === '') {
            $sc['hero']['subtitle'] = trim((string) ($base['hero']['subtitle'] ?? '')) !== ''
                ? (string) $base['hero']['subtitle']
                : $defaultSubtitleFallback;
        }
        if (trim((string) ($sc['hero']['h1'] ?? '')) === '') {
            $sc['hero']['h1'] = (string) $base['hero']['h1'];
        }
        if (trim((string) ($sc['hero']['eyebrow'] ?? '')) === '') {
            $sc['hero']['eyebrow'] = (string) $base['hero']['eyebrow'];
        }
    }
    if (!is_array($sc['intro'] ?? null)) {
        $sc['intro'] = $base['intro'];
    } else {
        foreach (['title', 'text'] as $k) {
            if (trim((string) ($sc['intro'][$k] ?? '')) === '') {
                $sc['intro'][$k] = (string) ($base['intro'][$k] ?? '');
            }
        }
    }
    if (!is_array($sc['cta'] ?? null)) {
        $sc['cta'] = $base['cta'];
    } else {
        foreach ($base['cta'] as $k => $v) {
            if (trim((string) ($sc['cta'][$k] ?? '')) === '') {
                $sc['cta'][$k] = $v;
            }
        }
    }
    if (!is_array($sc['pricing'] ?? null) || !is_array($sc['pricing']['rows'] ?? null)) {
        $sc['pricing'] = $base['pricing'];
    } else {
        if (trim((string) ($sc['pricing']['section_label'] ?? '')) === '') {
            $sc['pricing']['section_label'] = (string) $base['pricing']['section_label'];
        }
        if (trim((string) ($sc['pricing']['section_title'] ?? '')) === '') {
            $sc['pricing']['section_title'] = (string) $base['pricing']['section_title'];
        }
        if (trim((string) ($sc['pricing']['section_subtitle'] ?? '')) === '') {
            $sc['pricing']['section_subtitle'] = (string) $base['pricing']['section_subtitle'];
        }
        $bRows = $base['pricing']['rows'];
        $outR = [];
        for ($i = 0; $i < 3; $i++) {
            $r = is_array($sc['pricing']['rows'][$i] ?? null) ? $sc['pricing']['rows'][$i] : [];
            $d = is_array($bRows[$i] ?? null) ? $bRows[$i] : ['name' => '', 'price' => '', 'desc' => ''];
            $outR[] = [
                'name' => trim((string) ($r['name'] ?? '')) !== '' ? trim((string) $r['name']) : (string) ($d['name'] ?? ''),
                'price' => trim((string) ($r['price'] ?? '')) !== '' ? trim((string) $r['price']) : (string) ($d['price'] ?? ''),
                'desc' => trim((string) ($r['desc'] ?? '')) !== '' ? trim((string) $r['desc']) : (string) ($d['desc'] ?? ''),
            ];
        }
        $sc['pricing']['rows'] = $outR;
    }
    if (!is_array($sc['seo'] ?? null)) {
        $sc['seo'] = $base['seo'];
    } else {
        if (trim((string) ($sc['seo']['focus_keyword'] ?? '')) === '') {
            $sc['seo']['focus_keyword'] = (string) ($base['seo']['focus_keyword'] ?? '');
        }
        if (trim((string) ($sc['seo']['og_image_alt'] ?? '')) === '') {
            $sc['seo']['og_image_alt'] = (string) ($base['seo']['og_image_alt'] ?? '');
        }
    }
    $defBlocks = $base['services_blocks'];
    $outB = [];
    for ($i = 0; $i < 4; $i++) {
        $b = is_array($sc['services_blocks'][$i] ?? null) ? $sc['services_blocks'][$i] : [];
        $d = is_array($defBlocks[$i] ?? null) ? $defBlocks[$i] : [];
        $ben = $b['benefits'] ?? [];
        if (is_string($ben)) {
            $blines = array_values(
                array_filter(
                    array_map('trim', preg_split('/\R/u', $ben) ?: []),
                    static fn (string $s): bool => $s !== ''
                )
            );
        } else {
            $blines = array_values(array_map(static fn ($x) => trim((string) $x), (array) $ben));
        }
        $dben = is_array($d['benefits'] ?? null) ? $d['benefits'] : [];
        if ($blines === [] && is_array($dben)) {
            $blines = array_values(
                array_map(static fn ($x) => (string) $x, $dben)
            );
        }
        $outB[] = [
            'icon' => trim((string) ($b['icon'] ?? '')) !== '' ? trim((string) $b['icon']) : (string) ($d['icon'] ?? ''),
            'title' => trim((string) ($b['title'] ?? '')) !== '' ? trim((string) $b['title']) : (string) ($d['title'] ?? ''),
            'description' => trim((string) ($b['description'] ?? '')) !== '' ? trim((string) $b['description']) : (string) ($d['description'] ?? ''),
            'benefits' => $blines,
            'button_label' => trim((string) ($b['button_label'] ?? '')) !== '' ? trim((string) $b['button_label']) : (string) ($d['button_label'] ?? ''),
            'button_url' => trim((string) ($b['button_url'] ?? '')) !== '' ? trim((string) $b['button_url']) : (string) ($d['button_url'] ?? ''),
        ];
    }
    $sc['services_blocks'] = $outB;

    return $sc;
}

/**
 * @return list<array{0: string, 1: string, 2: string, 3: list<string>, 4: string, 5: string}>
 */
function cms_services_default_service_blocks(): array
{
    $assoc = cms_services_default_services_blocks_assoc();
    $out = [];
    foreach ($assoc as $a) {
        $out[] = [
            (string) $a['icon'],
            (string) $a['title'],
            (string) $a['description'],
            array_values($a['benefits'] ?? []),
            (string) $a['button_url'],
            (string) $a['button_label'],
        ];
    }

    return $out;
}

/**
 * L’ancien scan disposait d’une ligne séparée « services-services » — ne pas supprimer en automatique.
 */
function cms_services_legacy_duplicate_row_exists(): bool
{
    if (!function_exists('db')) {
        return false;
    }
    try {
        $st = db()->prepare('SELECT 1 FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
        $st->execute(['services-services']);

        return (bool) $st->fetchColumn();
    } catch (Throwable) {
        return false;
    }
}

function cms_ensure_services_page(): void
{
    if (!function_exists('db')) {
        return;
    }
    $pdo = db();
    if (function_exists('cmsEnsureCmsPagesTable')) {
        cmsEnsureCmsPagesTable($pdo);
    }
    if (function_exists('cms_ensure_extended_columns')) {
        cms_ensure_extended_columns($pdo);
    }
    $advisor = defined('ADVISOR_NAME') ? (string) ADVISOR_NAME : 'Pascal Hamm';
    $city = defined('APP_CITY') ? (string) APP_CITY : 'Aix-en-Provence';
    $seedTitle = 'Services immobiliers à ' . $city . ' | ' . $advisor;
    $seedDesc = 'Découvrez les services immobiliers de ' . $advisor . ' à ' . $city
        . ' : vente, achat, estimation gratuite et investissement locatif.';

    $slug = cms_services_page_slug();
    $st = $pdo->prepare('SELECT id, data_json, meta_title, meta_description FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
    $st->execute([$slug]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    $defaultContent = cms_services_default_content();
    $defaultContent['hero']['subtitle'] = 'Un accompagnement complet pour tous vos projets immobiliers à ' . $city . ' et ses environs.';
    $defaultContent['seo']['focus_keyword'] = 'services immobiliers ' . $city;
    if ($row) {
        $dj = json_decode((string) ($row['data_json'] ?? ''), true);
        if (!is_array($dj) || !isset($dj['services_content'])) {
            $dj = is_array($dj) ? $dj : [];
            $dj['services_content'] = $defaultContent;
            if (!isset($dj['sections']) || !is_array($dj['sections'])) {
                $dj['sections'] = [
                    'pageTitle' => $seedTitle,
                    'metaDesc' => $seedDesc,
                ];
            }
            $newMt = trim((string) ($row['meta_title'] ?? '')) === '' ? $seedTitle : trim((string) $row['meta_title']);
            $newMd = trim((string) ($row['meta_description'] ?? '')) === '' ? $seedDesc : trim((string) $row['meta_description']);
            $up = $pdo->prepare('UPDATE cms_pages SET data_json = :dj, template = :tpl, title = :title, meta_title = :mt, meta_description = :md, updated_at = NOW() WHERE id = :id');
            $up->execute([
                ':dj' => json_encode($dj, JSON_UNESCAPED_UNICODE),
                ':tpl' => 'pages/services/services',
                ':title' => 'Page Services',
                ':mt' => $newMt,
                ':md' => $newMd,
                ':id' => (int) $row['id'],
            ]);
        } elseif (empty($dj['services_content']['services_blocks']) || !is_array($dj['services_content']['services_blocks'])) {
            $dj['services_content'] = array_replace_recursive(
                $defaultContent,
                is_array($dj['services_content'] ?? null) ? $dj['services_content'] : []
            );
            if (!is_array($dj['sections'] ?? null)) {
                $dj['sections'] = ['pageTitle' => $seedTitle, 'metaDesc' => $seedDesc];
            }
            $up = $pdo->prepare('UPDATE cms_pages SET data_json = :dj, updated_at = NOW() WHERE id = :id');
            $up->execute([
                ':dj' => json_encode($dj, JSON_UNESCAPED_UNICODE),
                ':id' => (int) $row['id'],
            ]);
        }

        return;
    }

    $data = [
        'services_content' => $defaultContent,
        'sections' => [
            'pageTitle' => $seedTitle,
            'metaDesc' => $seedDesc,
        ],
    ];
    $ins = $pdo->prepare(
        'INSERT INTO cms_pages (site_id, slug, title, template, page_type, page_level, status, meta_title, meta_description, og_image_url, data_json, created_at, updated_at)
         VALUES (1, :slug, :title, :template, \'page\', 1, \'published\', :mt, :md, NULL, :dj, NOW(), NOW())'
    );
    $ins->execute([
        ':slug' => $slug,
        ':title' => 'Page Services',
        ':template' => 'pages/services/services',
        ':mt' => $seedTitle,
        ':md' => $seedDesc,
        ':dj' => json_encode($data, JSON_UNESCAPED_UNICODE),
    ]);
}

/**
 * Texte indexable pour mots / mot-clé (admin SEO + score).
 */
function cms_services_build_seo_indexable_text(array $sc, array $serviceBlocks, bool $includeStaticBlocks): string
{
    $parts = [];
    $h = $sc['hero'] ?? [];
    $parts[] = (string) ($h['h1'] ?? '');
    $parts[] = (string) ($h['subtitle'] ?? '');
    $i = $sc['intro'] ?? [];
    $parts[] = (string) ($i['title'] ?? '');
    $parts[] = (string) ($i['text'] ?? '');

    if ($includeStaticBlocks) {
        foreach ($serviceBlocks as $b) {
            if (!is_array($b) || count($b) < 6) {
                continue;
            }
            $parts[] = (string) $b[1];
            $parts[] = (string) $b[2];
            $parts[] = (string) $b[4];
            foreach ((array) $b[3] as $li) {
                $parts[] = (string) $li;
            }
        }
    }
    $p = $sc['pricing'] ?? [];
    $parts[] = (string) ($p['section_title'] ?? '');
    $parts[] = (string) ($p['section_subtitle'] ?? '');
    foreach ((array) ($p['rows'] ?? []) as $r) {
        if (!is_array($r)) {
            continue;
        }
        $parts[] = (string) ($r['name'] ?? '');
        $parts[] = (string) ($r['price'] ?? '');
        $parts[] = (string) ($r['desc'] ?? '');
    }
    $c = $sc['cta'] ?? [];
    $parts[] = (string) ($c['title'] ?? '');
    $parts[] = (string) ($c['text'] ?? '');
    $parts[] = (string) ($c['button_url'] ?? '');

    return trim(implode(' ', $parts));
}

/**
 * H1 public avec contexte (APP_CITY / ADVISOR_NAME) pour repli.
 *
 * @param array<string, mixed> $row cms_pages|empty
 * @return array{pageTitle:string, metaDesc:string, sc: array<string, mixed>, published: bool}
 */
function services_page_cms_merged_state(?array $row = null): array
{
    $advisor = defined('ADVISOR_NAME') ? (string) ADVISOR_NAME : 'Pascal Hamm';
    $city = defined('APP_CITY') ? (string) APP_CITY : 'Aix-en-Provence';
    $defaultTitle = 'Services immobiliers à ' . $city . ' | ' . $advisor;
    $defaultDesc = 'Découvrez les services immobiliers de ' . $advisor . ' à ' . $city
        . ' : vente, achat, estimation gratuite et investissement locatif.';
    $defaultSub = 'Un accompagnement complet pour tous vos projets immobiliers à ' . $city . ' et ses environs.';

    $base = cms_services_default_content();
    if ($row === null) {
        $scNoRow = $base;
        $scNoRow['hero']['subtitle'] = $defaultSub;
        $sc0 = cms_services_coalesce_with_defaults($scNoRow, $base, $defaultSub);

        return [
            'pageTitle' => $defaultTitle,
            'metaDesc' => $defaultDesc,
            'sc' => $sc0,
            'published' => false,
        ];
    }

    $dj = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($dj)) {
        $dj = [];
    }
    $sc = array_replace_recursive(
        $base,
        is_array($dj['services_content'] ?? null) ? $dj['services_content'] : []
    );
    $sc = cms_services_coalesce_with_defaults($sc, $base, $defaultSub);
    if (trim((string) ($sc['seo']['focus_keyword'] ?? '')) === '') {
        $sc['seo']['focus_keyword'] = 'services immobiliers ' . $city;
    }

    $mt = trim((string) ($row['meta_title'] ?? ''));
    $md = trim((string) ($row['meta_description'] ?? ''));
    $sPage = is_array($dj['sections'] ?? null) ? $dj['sections'] : [];
    $pt = $mt !== '' ? $mt : (trim((string) ($sPage['pageTitle'] ?? '')) !== '' ? (string) $sPage['pageTitle'] : $defaultTitle);
    $mdf = $md !== '' ? $md : (trim((string) ($sPage['metaDesc'] ?? '')) !== '' ? (string) $sPage['metaDesc'] : $defaultDesc);

    $published = (string) ($row['status'] ?? '') === 'published';

    return [
        'pageTitle' => $pt,
        'metaDesc' => $mdf,
        'sc' => $sc,
        'published' => $published,
    ];
}

/**
 * @return array<string, mixed>|null
 */
function services_page_cms_load_row(): ?array
{
    if (!function_exists('db')) {
        return null;
    }
    try {
        $st = db()->prepare('SELECT * FROM cms_pages WHERE site_id = 1 AND slug = ? LIMIT 1');
        $st->execute([cms_services_page_slug()]);

        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    } catch (Throwable) {
        return null;
    }
}

/**
 * @param array<string, mixed> $sc
 * @param array<string, mixed> $pageRow
 * @return array{score:int, items:list<array{ok:bool, text:string}>, serp:array{title:string,url:string,desc:string}}
 */
function cms_services_seo_score(array $sc, array $pageRow, string $h1ForPage, string $bodyPlainForWords): array
{
    $focus = mb_strtolower(trim((string) ($sc['seo']['focus_keyword'] ?? '')));
    $mt = mb_strtolower(trim((string) ($pageRow['meta_title'] ?? '')));
    $md = mb_strtolower(trim((string) ($pageRow['meta_description'] ?? '')));
    $h1l = mb_strtolower($h1ForPage);
    $ogUrl = trim((string) ($pageRow['og_image_url'] ?? ''));
    $ogAlt = trim((string) ($sc['seo']['og_image_alt'] ?? ''));
    $items = [];
    $score = 0;
    $add = static function (bool $ok, string $text) use (&$items, &$score): void {
        $items[] = ['ok' => $ok, 'text' => $text];
        if ($ok) {
            $score += 10;
        }
    };

    $add($focus !== '', 'Mot-clé principal renseigné');
    $add($focus !== '' && $mt !== '' && str_contains($mt, $focus), 'Mot-clé dans le meta title');
    $add($focus !== '' && $md !== '' && str_contains($md, $focus), 'Mot-clé dans la meta description');
    $add($focus !== '' && $h1l !== '' && str_contains($h1l, $focus), 'Mot-clé dans le H1');

    $wlist = preg_split('/\s+/u', trim(strip_tags($bodyPlainForWords))) ?: [];
    $first100 = implode(' ', array_slice($wlist, 0, 100));
    $add($focus !== '' && str_contains(mb_strtolower($first100), $focus), 'Mot-clé dans les 100 premiers mots du contenu textuel');

    $wcount = count($wlist);
    $add($wcount >= 800, 'Contenu textuel supérieur à 800 mots (hero, intro, blocs, tarifs, CTA)');

    $h2n = 5;
    $add($h2n >= 2, 'Au moins 2 H2 (titres de section) : le gabarit comporte 4 services + 1 section honoraires (5 H2) tant que le hero reste H1 seul');

    $add($ogUrl !== '' && $ogAlt !== '', 'Image Open Graph renseignée (URL) avec texte alternatif');

    $hasInternal = (bool) preg_match('#/(?:contact|estimation-gratuite|estimation|biens|a-propos|services)(?:[^a-z]|$)#i', $bodyPlainForWords);
    $add($hasInternal, 'Lien interne présent (URL relative type /contact, /estimation-gratuite…)');

    $ctaOk = trim((string) ($sc['cta']['title'] ?? '')) !== '' && trim((string) ($sc['cta']['button_label'] ?? '')) !== '';
    $add($ctaOk, 'CTA final : titre + libellé de bouton renseignés');

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    if ($host === '' && !empty($_ENV['PUBLIC_BASE_HOST'])) {
        $host = (string) $_ENV['PUBLIC_BASE_HOST'];
    }
    if ($host === '') {
        $host = 'votre-site.fr';
    }
    $serp = [
        'title' => trim((string) ($pageRow['meta_title'] ?? '')) !== '' ? trim((string) $pageRow['meta_title']) : (string) ($pageRow['title'] ?? 'Services'),
        'url' => 'https://' . $host . '/services',
        'desc' => trim((string) ($pageRow['meta_description'] ?? '')),
    ];

    return ['score' => min(100, $score), 'items' => $items, 'serp' => $serp];
}

/**
 * Propositions assistées (éditeur admin Services uniquement) — ne modifie pas la base, ne génère pas d’affichage front.
 * Les libellés « Aix / Pascal » suivent les constantes du site quand c’est cohérent.
 *
 * @param array<string, mixed> $pageRow cms_pages
 * @param array<string, mixed> $sc       services_content (fusionné)
 * @return array<string, mixed>
 */
function cms_services_seo_assist_proposals(array $pageRow, array $sc, string $h1ForPage, string $bodyPlainForWords): array
{
    $city = defined('APP_CITY') ? (string) APP_CITY : 'Aix-en-Provence';
    $advisor = defined('ADVISOR_NAME') ? (string) ADVISOR_NAME : 'Pascal Hamm';
    $focus = mb_strtolower(trim((string) ($sc['seo']['focus_keyword'] ?? '')));

    $metaT = trim((string) ($pageRow['meta_title'] ?? ''));
    $metaD = trim((string) ($pageRow['meta_description'] ?? ''));
    $h1 = trim($h1ForPage);
    $sub = trim((string) ($sc['hero']['subtitle'] ?? ''));
    $ititle = trim((string) ($sc['intro']['title'] ?? ''));
    $itext = trim((string) ($sc['intro']['text'] ?? ''));
    $ogU = trim((string) ($pageRow['og_image_url'] ?? ''));
    $ogA = trim((string) ($sc['seo']['og_image_alt'] ?? ''));

    $wlist = preg_split('/\s+/u', trim(strip_tags($bodyPlainForWords))) ?: [];
    $wordCount = count($wlist);
    $first100 = mb_strtolower(implode(' ', array_slice($wlist, 0, 100)));
    $first100Has = $focus !== '' && str_contains($first100, $focus);
    $h1l = mb_strtolower($h1);
    $mtL = mb_strtolower($metaT);
    $mdL = mb_strtolower($metaD);

    $pMetaTitle = 'Services immobiliers à ' . $city . ' | ' . $advisor;
    $pMetaDesc = 'Découvrez les services immobiliers de ' . $advisor . ' à ' . $city
        . ' : vente, achat, estimation gratuite et accompagnement pour votre projet immobilier.';
    $pH1 = 'Services immobiliers à ' . $city;
    $pSubAppend = 'Pascal Hamm propose des services immobiliers à ' . $city
        . ' pour accompagner les vendeurs, acheteurs et investisseurs avec une approche locale, claire et personnalisée.';
    $pOgAlt = 'Services immobiliers à ' . $city . ' avec ' . $advisor;
    $ogNoImageNote = 'Ajouter une image Open Graph représentative de ' . $advisor . ' ou d’' . $city . '.';

    $eTitle = 'Pourquoi choisir un accompagnement immobilier local à ' . $city . ' ?';
    $eText = 'Faire estimer, vendre, acheter ou investir à ' . $city
        . ' demande plus qu’une simple annonce immobilière. Chaque quartier, chaque rue et chaque type de bien peut avoir une dynamique différente. Un accompagnement local permet de mieux comprendre le marché, d’éviter les erreurs de prix, de préparer les bonnes actions et de sécuriser chaque étape du projet.'
        . "\n\n" . 'Avec ' . $advisor . ', l’objectif est de vous apporter une lecture claire de votre situation : valeur réelle du bien, attentes des acheteurs, potentiel de mise en valeur, stratégie de diffusion, calendrier de vente ou d’achat et points de vigilance. Cette approche permet d’avancer avec méthode, sans précipitation et avec des décisions plus solides.'
        . "\n\n" . 'Que vous souhaitiez vendre votre maison, acheter un appartement, demander une estimation gratuite ou étudier un investissement locatif, l’accompagnement repose sur une méthode simple : écouter votre projet, analyser le marché local, définir une stratégie adaptée et vous guider jusqu’à la réalisation concrète.';

    $rows = [];
    if ($focus === '') {
        return [
            'focus'        => '',
            'city'         => $city,
            'advisor'      => $advisor,
            'word_count'   => $wordCount,
            'first100_has' => false,
            'rows'         => [],
            'og_note'      => $ogU === '' ? $ogNoImageNote : null,
            'editorial'    => ['title' => $eTitle, 'text' => $eText, 'suggest' => $wordCount < 800],
        ];
    }

    if ($metaT === '' || !str_contains($mtL, $focus)) {
        $rows[] = [
            'id' => 'meta_title', 'label' => 'Meta title', 'name' => 'meta_title', 'type' => 'text',
            'current' => $metaT, 'proposed' => $pMetaTitle, 'empty_ok' => $metaT === '',
        ];
    }
    if ($metaD === '' || !str_contains($mdL, $focus)) {
        $rows[] = [
            'id' => 'meta_description', 'label' => 'Meta description', 'name' => 'meta_description', 'type' => 'textarea',
            'current' => $metaD, 'proposed' => $pMetaDesc, 'empty_ok' => $metaD === '',
        ];
    }
    if ($h1 === '' || !str_contains($h1l, $focus)) {
        $rows[] = [
            'id' => 'hero_h1', 'label' => 'H1 public', 'name' => 'hero_h1', 'type' => 'text',
            'current' => $h1, 'proposed' => $pH1, 'empty_ok' => $h1 === '',
        ];
    }
    if (!$first100Has) {
        $proposedSub = $sub === ''
            ? $pSubAppend
            : rtrim($sub, " \n\r\t") . "\n\n" . $pSubAppend;
        $rows[] = [
            'id' => 'hero_subtitle', 'label' => 'Sous-titre (hero) — phrase pour le mot-clé dans les 100 premiers mots', 'name' => 'hero_subtitle', 'type' => 'textarea',
            'current' => $sub, 'proposed' => $proposedSub, 'empty_ok' => $sub === '',
        ];
    }
    if ($ogU !== '' && $ogA === '') {
        $rows[] = [
            'id' => 'og_image_alt', 'label' => 'Texte alternatif Open Graph', 'name' => 'og_image_alt', 'type' => 'text',
            'current' => $ogA, 'proposed' => $pOgAlt, 'empty_ok' => true,
        ];
    }

    $ogNote = $ogU === '' ? $ogNoImageNote : null;

    return [
        'focus'        => $sc['seo']['focus_keyword'] ?? $focus,
        'city'         => $city,
        'advisor'      => $advisor,
        'word_count'   => $wordCount,
        'first100_has' => $first100Has,
        'rows'         => $rows,
        'og_note'      => $ogNote,
        'editorial'    => [
            'title'   => $eTitle,
            'text'    => $eText,
            'suggest' => $wordCount < 800,
        ],
    ];
}

/**
 * @param array<string, mixed> $post
 */
function cms_save_services_page(array $post): void
{
    if (!function_exists('db')) {
        throw new RuntimeException('Base indisponible');
    }
    if (!function_exists('cms_ensure_extended_columns') || !function_exists('cms_load_page_row')) {
        require_once dirname(__DIR__, 2) . '/modules/cms/cms-generic.inc.php';
    }
    $pdo = db();
    cms_ensure_extended_columns($pdo);
    cms_ensure_services_page();
    $row = cms_load_page_row(cms_services_page_slug());
    if (!$row) {
        throw new RuntimeException('Page services introuvable');
    }
    $status = (string) ($post['status'] ?? 'draft');
    if (!in_array($status, ['draft', 'published'], true)) {
        $status = 'draft';
    }
    $title = trim((string) ($post['title'] ?? 'Page Services'));
    $metaTitle = trim((string) ($post['meta_title'] ?? ''));
    $metaDesc = trim((string) ($post['meta_description'] ?? ''));
    $og = trim((string) ($post['og_image_url'] ?? ''));
    $pl = (int) ($post['page_level'] ?? 1);
    if (!in_array($pl, [1, 2], true)) {
        $pl = 1;
    }

    $sc = [
        'hero' => [
            'eyebrow' => trim((string) ($post['hero_eyebrow'] ?? '')),
            'h1' => trim((string) ($post['hero_h1'] ?? '')),
            'subtitle' => trim((string) ($post['hero_subtitle'] ?? '')),
        ],
        'intro' => [
            'title' => trim((string) ($post['intro_title'] ?? '')),
            'text' => trim((string) ($post['intro_text'] ?? '')),
        ],
        'cta' => [
            'title' => trim((string) ($post['cta_title'] ?? '')),
            'text' => trim((string) ($post['cta_text'] ?? '')),
            'button_label' => trim((string) ($post['cta_button_label'] ?? '')),
            'button_url' => trim((string) ($post['cta_button_url'] ?? '')),
        ],
        'pricing' => [
            'section_label' => trim((string) ($post['pricing_section_label'] ?? '')),
            'section_title' => trim((string) ($post['pricing_section_title'] ?? '')),
            'section_subtitle' => trim((string) ($post['pricing_section_subtitle'] ?? '')),
            'rows' => [],
        ],
        'seo' => [
            'focus_keyword' => trim((string) ($post['focus_keyword'] ?? '')),
            'og_image_alt' => trim((string) ($post['og_image_alt'] ?? '')),
        ],
        'services_blocks' => [],
    ];
    for ($b = 0; $b < 4; $b++) {
        $bLines = (string) ($post['block_benefits_' . $b] ?? '');
        $benefits = array_values(
            array_filter(
                array_map('trim', preg_split('/\R/u', $bLines) ?: []),
                static fn (string $s): bool => $s !== ''
            )
        );
        $sc['services_blocks'][] = [
            'icon' => trim((string) ($post['block_icon_' . $b] ?? '')),
            'title' => trim((string) ($post['block_title_' . $b] ?? '')),
            'description' => trim((string) ($post['block_desc_' . $b] ?? '')),
            'benefits' => $benefits,
            'button_label' => trim((string) ($post['block_btn_label_' . $b] ?? '')),
            'button_url' => trim((string) ($post['block_btn_url_' . $b] ?? '')),
        ];
    }
    for ($i = 0; $i < 3; $i++) {
        $sc['pricing']['rows'][] = [
            'name' => trim((string) ($post['price_name_' . $i] ?? '')),
            'price' => trim((string) ($post['price_val_' . $i] ?? '')),
            'desc' => trim((string) ($post['price_desc_' . $i] ?? '')),
        ];
    }

    $old = json_decode((string) ($row['data_json'] ?? ''), true);
    if (!is_array($old)) {
        $old = [];
    }
    $old['services_content'] = array_replace_recursive(cms_services_default_content(), $sc);
    $old['services_content']['services_blocks'] = $sc['services_blocks'];
    if (!isset($old['sections']) || !is_array($old['sections'])) {
        $old['sections'] = [];
    }
    $old['sections']['pageTitle'] = $metaTitle;
    $old['sections']['metaDesc'] = $metaDesc;

    $dj = json_encode($old, JSON_UNESCAPED_UNICODE);
    if ($dj === false) {
        throw new RuntimeException('Encodage JSON services impossible');
    }

    $up = $pdo->prepare(
        'UPDATE cms_pages SET title = :title, status = :status, meta_title = :mt, meta_description = :md,
         og_image_url = :og, page_level = :pl, data_json = :dj, updated_at = NOW() WHERE id = :id'
    );
    $up->execute([
        ':title' => $title,
        ':status' => $status,
        ':mt' => $metaTitle !== '' ? $metaTitle : null,
        ':md' => $metaDesc !== '' ? $metaDesc : null,
        ':og' => $og !== '' ? $og : null,
        ':pl' => $pl,
        ':dj' => $dj,
        ':id' => (int) $row['id'],
    ]);
}
