<?php
declare(strict_types=1);

/**
 * Découverte des gabarits dans public/pages et extraction des chaînes éditables.
 */
final class CmsPageDiscovery
{
    private const PAGES_ROOT = 'public/pages';

    private const MAX_LEN_SHORT = 120_000;

    private const MAX_LEN_PAGE_BODY = 500_000;

    /** Gabarits exclus (dynamiques, includes, données brutes). */
    private const SKIP_PATH_CONTAINS = [
        '/blog/article.php',
        '/page.php',
        '/zones/_ville-secteur.php',
        '/ressources/guides-data.php',
    ];

    /** Segments de chemin → niveau 2 (non index SEO principal). */
    private const LEVEL2_MARKERS = [
        'merci', '404', 'maintenance', 'error', 'tunnel', 'resultat', 'instantanee',
        'bien-merci', 'plan-du-site',
    ];

    /** Variables pour lesquelles on accepte de très longues chaînes (HTML). */
    private const LONG_BODY_VARS = ['pageContent', 'page_intro', 'body', 'html', 'content'];

    /** Clés de texte pour les fiches guide-local (une entrée CMS par slug URL). */
    private const GUIDE_SECTEUR_TEXT_KEYS = [
        'nom', 'desc', 'marche', 'transports', 'commerces', 'prix', 'tendance', 'delai', 'img', 'img_credit', 'biens',
    ];

    /** Clés $ville injectées dans _ville-secteur. */
    private const VILLE_TEXT_KEYS = [
        'nom', 'type', 'prix', 'tendance', 'delai', 'description', 'marche', 'metaDesc', 'image',
    ];

    /**
     * Slug CMS pour une route guide-local (une ligne par secteur).
     */
    public static function guideLocalCmsSlug(string $secteurSlug): string
    {
        $s = strtolower(preg_replace('/[^a-z0-9-]/', '', $secteurSlug));

        return 'guide-local-ville-' . ($s !== '' ? $s : 'default');
    }

    /**
     * @return list<string>
     */
    public static function villeOverlayKeys(): array
    {
        return self::VILLE_TEXT_KEYS;
    }

    /**
     * @return list<string>
     */
    public static function guideSecteurOverlayKeys(): array
    {
        return self::GUIDE_SECTEUR_TEXT_KEYS;
    }

    /**
     * @return list<array{template:string,slug:string,label:string,page_level:int,sections:array<string,string>}>
     */
    public static function scan(string $rootPath): array
    {
        $pagesDir = rtrim($rootPath, '/') . '/' . self::PAGES_ROOT;
        if (!is_dir($pagesDir)) {
            return [];
        }

        $out = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pagesDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }
            $full = $file->getPathname();
            $rel = substr($full, strlen($pagesDir) + 1);
            if (self::shouldSkip($rel)) {
                continue;
            }

            $relPosix = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
            if ($relPosix === 'guide-local/ville.php') {
                foreach (self::scanGuideLocalVilleFragments($full, $relPosix) as $disc) {
                    $out[] = $disc;
                }

                continue;
            }

            $template = 'pages/' . preg_replace('#\.php$#i', '', $relPosix);
            $slug = self::templateToSlug($template);
            $sections = self::extractAllEditableSections($full);

            if ($sections === []) {
                $sections = ['page_intro' => ''];
            }

            $out[] = [
                'template' => $template,
                'slug' => $slug,
                'label' => self::humanLabel($slug, $rel),
                'page_level' => self::guessLevel($rel),
                'sections' => $sections,
            ];
        }

        usort($out, static fn (array $a, array $b): int => [$a['page_level'], $a['slug']] <=> [$b['page_level'], $b['slug']]);

        return $out;
    }

    public static function templateToSlug(string $template): string
    {
        $t = preg_replace('#^pages/#', '', trim($template, '/')) ?? trim($template, '/');
        if ($t === 'core/home') {
            return 'home';
        }
        // Source de vérité unique : slug « services » (pas services-services)
        if ($t === 'services/services') {
            return 'services';
        }

        return str_replace('/', '-', $t);
    }

    /**
     * @return list<string>
     */
    public static function editableKeysForFile(string $absolutePhpPath): array
    {
        return array_keys(self::extractAllEditableSections($absolutePhpPath));
    }

    /**
     * @param array<string, mixed> $row cms_pages row
     * @return array<string, string> sections éditables
     */
    public static function sectionsFromRow(array $row): array
    {
        $raw = json_decode((string) ($row['data_json'] ?? ''), true);
        if (!is_array($raw)) {
            return [];
        }
        $s = $raw['sections'] ?? null;

        return is_array($s) ? array_map('strval', $s) : [];
    }

    /**
     * @param array<string, string> $sections
     */
    public static function mergeDataJson(?string $existingJson, array $sections): string
    {
        $base = [];
        if ($existingJson !== null && $existingJson !== '') {
            $d = json_decode($existingJson, true);
            if (is_array($d)) {
                $base = $d;
            }
        }
        $base['sections'] = $sections;

        $enc = json_encode($base, JSON_UNESCAPED_UNICODE);
        if ($enc === false) {
            throw new RuntimeException('JSON encode sections CMS impossible');
        }

        return $enc;
    }

    /**
     * @return array<string, string>
     */
    public static function extractAllEditableSections(string $absolutePhpPath): array
    {
        $merged = [];
        foreach (self::extractStringAssignments($absolutePhpPath) as $k => $v) {
            $merged[$k] = $v;
        }
        foreach (self::extractNullCoalesceDefaults($absolutePhpPath) as $k => $v) {
            if (!array_key_exists($k, $merged) || trim((string) $merged[$k]) === '') {
                $merged[$k] = $v;
            }
        }
        foreach (self::extractTokenStringAssignments($absolutePhpPath) as $k => $v) {
            if (!array_key_exists($k, $merged) || (in_array($k, self::LONG_BODY_VARS, true) && mb_strlen($v) > mb_strlen((string) ($merged[$k] ?? '')))) {
                $merged[$k] = $v;
            }
        }
        foreach (self::extractVilleArrayStrings($absolutePhpPath) as $k => $v) {
            if (!array_key_exists($k, $merged) || trim((string) $merged[$k]) === '') {
                $merged[$k] = $v;
            }
        }

        return $merged;
    }

    private static function shouldSkip(string $rel): bool
    {
        $n = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
        foreach (self::SKIP_PATH_CONTAINS as $frag) {
            if (str_contains($n, trim($frag, '/'))) {
                return true;
            }
        }

        return false;
    }

    private static function guessLevel(string $rel): int
    {
        $n = strtolower(str_replace(DIRECTORY_SEPARATOR, '/', $rel));
        foreach (self::LEVEL2_MARKERS as $m) {
            if (str_contains($n, $m)) {
                return 2;
            }
        }
        if (str_contains($n, '404.php')) {
            return 2;
        }

        return 1;
    }

    private static function humanLabel(string $slug, string $rel): string
    {
        $base = basename(str_replace(DIRECTORY_SEPARATOR, '/', $rel), '.php');

        return $base === 'index' ? dirname(str_replace(DIRECTORY_SEPARATOR, '/', $rel)) . ' (index)' : $base;
    }

    /**
     * @return list<array{template:string,slug:string,label:string,page_level:int,sections:array<string,string>}>
     */
    private static function scanGuideLocalVilleFragments(string $abs, string $relPosix): array
    {
        $src = @file_get_contents($abs);
        if ($src === false || !str_contains($src, '$secteurs')) {
            return [[
                'template' => 'pages/guide-local/ville',
                'slug' => 'guide-local-ville',
                'label' => 'guide-local/ville',
                'page_level' => self::guessLevel($relPosix),
                'sections' => ['page_intro' => ''],
            ]];
        }

        $slugs = [];
        foreach (preg_split('/\R/', $src) as $line) {
            if (preg_match("/^\\s*'([a-z][a-z0-9-]*)'\\s*=>\\s*\\[\\s*$/", $line, $m)) {
                $slugs[] = $m[1];
            }
        }
        $slugs = array_values(array_unique($slugs));
        if ($slugs === []) {
            return [[
                'template' => 'pages/guide-local/ville',
                'slug' => 'guide-local-ville',
                'label' => 'guide-local/ville',
                'page_level' => 1,
                'sections' => ['page_intro' => ''],
            ]];
        }

        $out = [];
        foreach ($slugs as $secteurSlug) {
            $block = self::extractAssocArrayBlock($src, 'secteurs', $secteurSlug);
            if ($block === null) {
                continue;
            }
            $pairs = self::parseFlatStringPairsInArrayBody($block);
            $sections = ['page_intro' => ''];
            foreach (self::GUIDE_SECTEUR_TEXT_KEYS as $gk) {
                if (isset($pairs[$gk])) {
                    $sections[$gk] = $pairs[$gk];
                }
            }
            $nom = $pairs['nom'] ?? $secteurSlug;
            $out[] = [
                'template' => 'pages/guide-local/ville',
                'slug' => self::guideLocalCmsSlug($secteurSlug),
                'label' => 'Guide local — ' . $nom,
                'page_level' => 1,
                'sections' => $sections,
            ];
        }

        return $out !== [] ? $out : [[
            'template' => 'pages/guide-local/ville',
            'slug' => 'guide-local-ville',
            'label' => 'guide-local/ville',
            'page_level' => 1,
            'sections' => ['page_intro' => ''],
        ]];
    }

    /**
     * Extrait le corps d'un tableau associatif `'key' => [ ... ]` pour $varName.
     */
    private static function extractAssocArrayBlock(string $src, string $varName, string $arrayKey): ?string
    {
        $needle = preg_quote('$' . $varName, '/') . '\s*=\s*\[';
        if (!preg_match('/' . $needle . '/s', $src, $mm, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $openBracketPos = (int) $mm[0][1] + strlen($mm[0][0]) - 1;
        $inner = self::innerArrayAfterOpenBracket($src, $openBracketPos);
        if ($inner === null) {
            return null;
        }

        $keyPat = "'" . preg_quote($arrayKey, '/') . "'\\s*=>\\s*\\[";
        if (!preg_match('/' . $keyPat . '/s', $inner, $km, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $subOpen = (int) $km[0][1] + strlen($km[0][0]) - 1;

        return self::innerArrayAfterOpenBracket($inner, $subOpen);
    }

    private static function innerArrayAfterOpenBracket(string $src, int $openBracketPos): ?string
    {
        if (($src[$openBracketPos] ?? '') !== '[') {
            return null;
        }
        $depth = 0;
        $len = strlen($src);
        for ($i = $openBracketPos; $i < $len; $i++) {
            $c = $src[$i];
            if ($c === '[') {
                $depth++;
            } elseif ($c === ']') {
                $depth--;
                if ($depth === 0) {
                    return substr($src, $openBracketPos + 1, $i - $openBracketPos - 1);
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private static function parseFlatStringPairsInArrayBody(string $inner): array
    {
        $out = [];
        $nest = 0;
        foreach (preg_split('/\R/', $inner) as $line) {
            $nest += substr_count($line, '[') - substr_count($line, ']');
            if ($nest > 0) {
                continue;
            }
            if (preg_match("/^\\s*'([a-zA-Z_][a-zA-Z0-9_]*)'\\s*=>\\s*'((?:[^'\\\\]|\\\\.)*)'\\s*,?\\s*$/u", $line, $m)) {
                $out[$m[1]] = self::decodeSingleQuoted($m[2]);

                continue;
            }
            if (preg_match('/^\\s*\'([a-zA-Z_][a-zA-Z0-9_]*)\'\\s*=>\\s*"((?:[^"\\\\]|\\\\.)*)"\\s*,?\\s*$/u', $line, $m)) {
                $out[$m[1]] = stripcslashes($m[2]);

                continue;
            }
            if (preg_match("/^\\s*'biens'\\s*=>\\s*(\\d+)\\s*,?\\s*$/", $line, $m)) {
                $out['biens'] = $m[1];
            }
        }

        return $out;
    }

    private static function decodeSingleQuoted(string $inner): string
    {
        return str_replace(["\\\\", "\\'"], ['\\', "'"], $inner);
    }

    /**
     * @return array<string, string>
     */
    private static function extractVilleArrayStrings(string $file): array
    {
        $src = @file_get_contents($file);
        if ($src === false || !preg_match('/\$ville\s*=\s*\[/', $src)) {
            return [];
        }
        if (!preg_match('/\$ville\s*=\s*\[/', $src, $mm, PREG_OFFSET_CAPTURE)) {
            return [];
        }
        $open = (int) $mm[0][1] + strlen($mm[0][0]) - 1;
        $inner = self::innerArrayAfterOpenBracket($src, $open);
        if ($inner === null) {
            return [];
        }
        $pairs = self::parseFlatStringPairsInArrayBody($inner);
        $out = [];
        foreach (self::VILLE_TEXT_KEYS as $k) {
            if (isset($pairs[$k]) && self::isEditableVilleKey($k)) {
                $out[$k] = $pairs[$k];
            }
        }

        return $out;
    }

    private static function isEditableVilleKey(string $k): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    private static function extractStringAssignments(string $file): array
    {
        $src = @file_get_contents($file);
        if ($src === false) {
            return [];
        }
        $lines = preg_split('/\R/', $src) ?: [];
        $vars = [];
        $pattern = '/^\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*([\'"])(.*)\2\s*;\s*$/u';

        foreach ($lines as $line) {
            if (!preg_match($pattern, $line, $m)) {
                continue;
            }
            $name = $m[1];
            $value = $m[3];
            if (!self::isEditableVar($name)) {
                continue;
            }
            if (str_contains($value, '<?') || str_contains($value, '$')) {
                continue;
            }
            $max = in_array($name, self::LONG_BODY_VARS, true) ? self::MAX_LEN_PAGE_BODY : self::MAX_LEN_SHORT;
            if (mb_strlen($value) > $max) {
                continue;
            }
            $vars[$name] = $value;
        }

        return $vars;
    }

    /**
     * $var = $siteSettings['k'] ?? 'texte';
     *
     * @return array<string, string>
     */
    private static function extractNullCoalesceDefaults(string $file): array
    {
        $src = @file_get_contents($file);
        if ($src === false) {
            return [];
        }
        $vars = [];
        $pattern = '/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*(?:\$siteSettings\[[^\]]+\]|\$_ENV\[[^\]]+\]|\$[a-zA-Z_][a-zA-Z0-9_]*)\s*\?\?\s*(?:(\')((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")\s*;/u';
        if (preg_match_all($pattern, $src, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $name = $m[1];
                if (!self::isEditableVar($name)) {
                    continue;
                }
                $value = $m[2] === "'" ? self::decodeSingleQuoted((string) $m[3]) : stripcslashes((string) $m[4]);
                if (str_contains($value, '$') || str_contains($value, '<?')) {
                    continue;
                }
                $max = in_array($name, self::LONG_BODY_VARS, true) ? self::MAX_LEN_PAGE_BODY : self::MAX_LEN_SHORT;
                if (mb_strlen($value) > $max) {
                    continue;
                }
                $vars[$name] = $value;
            }
        }

        return $vars;
    }

    /**
     * Chaînes multi-lignes (pageContent, etc.) via tokenizer PHP.
     *
     * @return array<string, string>
     */
    private static function extractTokenStringAssignments(string $file): array
    {
        $src = @file_get_contents($file);
        if ($src === false) {
            return [];
        }
        $tokens = @token_get_all($src);
        if (!is_array($tokens)) {
            return [];
        }

        $tokenNames = [
            'pageContent', 'pageTitle', 'metaDesc', 'meta_description', 'metaKeywords',
            'bodyClass', 'intro', 'subtitle', 'heroTitle', 'lead',
        ];

        $out = [];
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $t = $tokens[$i];
            if (!is_array($t) || $t[0] !== T_VARIABLE) {
                continue;
            }
            $varName = substr($t[1], 1);
            if (!in_array($varName, $tokenNames, true) && !self::isEditableVar($varName)) {
                continue;
            }
            $j = $i + 1;
            $j = self::skipWs($tokens, $j, $n);
            if ($j >= $n) {
                continue;
            }
            if (!self::isTokenEq($tokens[$j])) {
                continue;
            }
            $j++;
            $j = self::skipWs($tokens, $j, $n);
            $built = self::readConcatStringsFromTokens($tokens, $j, $n);
            if ($built === null) {
                continue;
            }
            [$str, $endIdx] = $built;
            if ($str === '') {
                continue;
            }
            $max = in_array($varName, self::LONG_BODY_VARS, true) ? self::MAX_LEN_PAGE_BODY : self::MAX_LEN_SHORT;
            if (mb_strlen($str) > $max) {
                continue;
            }
            $out[$varName] = $str;
        }

        return $out;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private static function skipWs(array $tokens, int $j, int $n): int
    {
        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }

        return $j;
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     */
    private static function isTokenEq(mixed $tok): bool
    {
        return is_string($tok) && $tok === '=';
    }

    /**
     * @param array<int, array{0:int,1:string,2:int}|string> $tokens
     * @return ?array{0:string,1:int}
     */
    private static function readConcatStringsFromTokens(array $tokens, int $j, int $n): ?array
    {
        $acc = '';
        $pos = $j;
        while ($pos < $n) {
            $t = $tokens[$pos];
            if (is_array($t) && $t[0] === T_CONSTANT_ENCAPSED_STRING) {
                $acc .= self::decodeEncapsedStringToken($t[1]);
                $pos++;
                $pos = self::skipWs($tokens, $pos, $n);
                if ($pos < $n && is_string($tokens[$pos]) && $tokens[$pos] === '.') {
                    $pos++;
                    $pos = self::skipWs($tokens, $pos, $n);
                    if ($pos < $n && is_array($tokens[$pos]) && $tokens[$pos][0] === T_STRING) {
                        $cn = $tokens[$pos][1];
                        if ($cn === 'ADVISOR_NAME' && defined('ADVISOR_NAME')) {
                            $acc .= (string) constant('ADVISOR_NAME');
                        } elseif ($cn === 'APP_NAME' && defined('APP_NAME')) {
                            $acc .= (string) constant('APP_NAME');
                        }
                        $pos++;
                        $pos = self::skipWs($tokens, $pos, $n);
                        if ($pos < $n && is_string($tokens[$pos]) && $tokens[$pos] === '.') {
                            continue;
                        }
                    }
                    continue;
                }

                if ($pos < $n && is_string($tokens[$pos]) && $tokens[$pos] === ';') {
                    return [$acc, $pos];
                }
                if ($pos < $n && is_string($tokens[$pos]) && $tokens[$pos] === ',') {
                    return [$acc, $pos];
                }

                return [$acc, $pos];
            }
            if (is_string($t) && $t === ';') {
                return $acc !== '' ? [$acc, $pos] : null;
            }
            if (is_array($t) && $t[0] === T_STRING && ($t[1] === 'ob_get_clean' || $t[1] === 'file_get_contents')) {
                return null;
            }

            return null;
        }

        return null;
    }

    private static function decodeEncapsedStringToken(string $raw): string
    {
        if ($raw === '') {
            return '';
        }
        $q = $raw[0];
        if (($q === "'" || $q === '"') && substr($raw, -1) === $q) {
            $inner = substr($raw, 1, -1);

            return $q === "'" ? self::decodeSingleQuoted($inner) : stripcslashes($inner);
        }

        return '';
    }

    private static function isEditableVar(string $name): bool
    {
        if (preg_match('/(Settings|settings|Error|error|Path|path|Url|url|Href|href|Token|token|Time|time|Count|count|Id|ID|Phone|Email|Image)$/i', $name)) {
            return false;
        }

        return (bool) preg_match('/^(pageTitle|metaDesc|meta_description|metaKeywords|pageContent|intro|subtitle|title|hero|lead|description|heading|label|text|body|cta|section)/i', $name)
            || (bool) preg_match('/^(contact|about|service|legal|faq|merci|error|territory|speciality|years|approach|icon|value|card|stat|pillar)/i', $name);
    }
}
