<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v69 · tools/lib/v69_css_usage.php – které třídy a id může pohled použít (podklad pro dělení tmavé vrstvy).
 *
 * Statický rozbor PHP (bez spuštění kódu): soubor pohledu a _layout.php se berou celé, z ostatních souborů jen ty funkce,
 * na které se z už načteného kódu odkazuje jménem (volání, řetězec s názvem funkce, callback). Soubory zmíněné v řetězcích
 * (částečné šablony *.php) se berou celé. Slova = identifikátory z řetězců a HTML šablon (možné názvy tříd) + řetězce v JS stránky.
 * Cílem je nadmnožina skutečně vykreslených tříd; úplnost ověřuje tools/v69_dark_audit.php na vykreslených stránkách.
 */

/** Projektové PHP soubory s možnými šablonami (bez tools, tests, V1, storage, retired, lang, docs). @return list<string> */
function u69_php_files(string $root): array
{
    $out = [];
    $skip = '~^(tools|tests|V1|storage|retired|lang|docs|database|cache|lab-runtime|uploads|\.claude)/~';
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        $rel = substr(str_replace(chr(92), '/', $file->getPathname()), strlen($root) + 1);
        if (str_ends_with($rel, '.php') && preg_match($skip, $rel) !== 1) $out[] = $rel;
    }
    sort($out);
    return $out;
}

/** @return array{words:array<string,true>,idents:array<string,true>,includes:array<string,true>,markup:bool} */
function u69_empty_bag(): array
{
    return ['words' => [], 'idents' => [], 'includes' => [], 'markup' => false];
}

/** Přidá do tašky obsah jednoho tokenu (řetězec, šablona nebo identifikátor). */
function u69_bag_add(array &$bag, int|null $id, string $text, bool $inInclude = false): void
{
    if ($id === T_STRING) { $bag['idents'][$text] = true; return; }
    if (!in_array($id, [T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) return;
    preg_match_all('~[A-Za-z_][A-Za-z0-9_-]*~', $text, $m);
    foreach ($m[0] as $word) $bag['words'][$word] = true;
    if ($id === T_INLINE_HTML ? trim($text) !== '' : preg_match('~<[A-Za-z/]~', $text) === 1) $bag['markup'] = true;
    // řetězec tvořený jen jedním identifikátorem může být název funkce (callback); volné slovo ve větě nikoli
    if ($id === T_CONSTANT_ENCAPSED_STRING && preg_match('~^["\x27][A-Za-z_][A-Za-z0-9_]*["\x27]$~', $text) === 1) $bag['idents'][trim($text, '"\x27')] = true;
    if ($inInclude && preg_match_all('~[A-Za-z0-9_./-]+\.php~', $text, $inc) > 0) foreach ($inc[0] as $path) $bag['includes'][$path] = true;
}

/** Název funkce, která začíná tokenem T_FUNCTION na pozici $i (null = anonymní funkce). */
function u69_function_name(array $tokens, int $i): ?string
{
    for ($j = $i + 1; isset($tokens[$j]); $j++) {
        $t = $tokens[$j];
        if (is_array($t) && $t[0] === T_WHITESPACE) continue;
        if ($t === '&') continue;
        return is_array($t) && $t[0] === T_STRING ? $t[1] : null;
    }
    return null;
}

/**
 * Rozloží PHP kód na „top“ (mimo pojmenované funkce) a pojmenované funkce.
 * @return array{top:array,funcs:array<string,array>}
 */
function u69_parse_php(string $src): array
{
    $tokens = token_get_all($src);
    $bags = ['' => u69_empty_bag()];
    $depth = 0;
    $pending = null;
    $current = null;   // [název, hloubka těla]
    $inInclude = false; // uvnitř příkazu require/include (jen tam se .php řetězce berou jako vložené soubory)
    foreach ($tokens as $i => $t) {
        $id = is_array($t) ? $t[0] : null;
        $text = is_array($t) ? $t[1] : $t;
        if ($id === T_FUNCTION && $current === null) $pending = u69_function_name($tokens, $i);
        if ($text === ';') { $inInclude = false; if ($current === null) $pending = null; }
        if (in_array($id, [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE], true)) $inInclude = true;
        if ($text === '{' || $text === '${') {
            $depth++;
            if ($pending !== null && $current === null) {
                $current = [$pending, $depth];
                $bags[$pending] ??= u69_empty_bag();
                $pending = null;
            }
            continue;
        }
        if ($text === '}') {
            if ($current !== null && $current[1] === $depth) $current = null;
            $depth--;
            continue;
        }
        $key = $current === null ? '' : $current[0];
        u69_bag_add($bags[$key], $id, $text, $inInclude);
    }
    $top = $bags[''];
    unset($bags['']);
    return ['top' => $top, 'funcs' => $bags];
}

/** Index projektu: soubory (top + jména funkcí) a funkce (jméno → tašky). */
function u69_index(string $root): array
{
    $index = ['files' => [], 'funcs' => []];
    foreach (u69_php_files($root) as $rel) {
        $parsed = u69_parse_php((string)file_get_contents($root . '/' . $rel));
        $index['files'][$rel] = ['top' => $parsed['top'], 'funcs' => array_keys($parsed['funcs'])];
        foreach ($parsed['funcs'] as $name => $bag) $index['funcs'][$name][] = $bag;
    }
    return $index;
}

/** Soubor z řetězce (cesta nebo jen název) – shoda na konci cesty. */
function u69_resolve_include(array $index, string $path): ?string
{
    $path = ltrim($path, './');
    foreach (array_keys($index['files']) as $rel) {
        if ($rel === $path || str_ends_with($rel, '/' . $path)) return $rel;
    }
    return null;
}

/** Slova z řetězců JS (bez komentářů). @return array<string,true> */
function u69_js_words(string $src): array
{
    $src = (string)preg_replace('~/\*.*?\*/~s', '', $src);
    preg_match_all('~"(?:[^"\\\\\n]|\\\\.)*"|\'(?:[^\'\\\\\n]|\\\\.)*\'|`(?:[^`\\\\]|\\\\.)*`~s', $src, $m);
    $set = [];
    foreach ($m[0] as $chunk) {
        preg_match_all('~[A-Za-z_][A-Za-z0-9_-]*~', $chunk, $w);
        foreach ($w[0] as $word) $set[$word] = true;
    }
    return $set;
}

/**
 * Slova pohledu: celé vstupní soubory + dosažitelné funkce + částečné šablony + řetězce JS.
 * @param list<string> $entries vstupní soubory (relativně) @param list<string> $jsFiles cesty JS (relativně)
 * @return array<string,true>
 */
function u69_view_words(string $root, array $index, array $entries, array $jsFiles): array
{
    $words = [];
    $entrySet = array_flip($entries);
    $seenFn = [];
    $seenFile = [];
    $stack = [];
    $takeBag = static function (array $bag, bool $entry = false) use (&$words, &$stack, &$seenFn, &$seenFile, $index): void {
        if ($bag['markup'] || $entry) foreach ($bag['words'] as $w => $_) $words[$w] = true;
        foreach (array_keys($bag['idents']) as $name) {
            if (isset($index['funcs'][$name]) && !isset($seenFn[$name])) { $seenFn[$name] = true; $stack[] = ['fn', $name]; }
        }
        foreach (array_keys($bag['includes']) as $path) {
            $rel = u69_resolve_include($index, $path);
            if ($rel !== null && !isset($seenFile[$rel])) { $seenFile[$rel] = true; $stack[] = ['file', $rel]; }
        }
    };
    foreach ($entries as $rel) if (isset($index['files'][$rel])) { $seenFile[$rel] = true; $stack[] = ['file', $rel]; }
    while ($stack !== []) {
        [$kind, $name] = array_pop($stack);
        if ($kind === 'fn') { foreach ($index['funcs'][$name] as $bag) $takeBag($bag); continue; }
        $takeBag($index['files'][$name]['top'], isset($entrySet[$name]));
        if (!isset($entrySet[$name])) continue;   // zahrnutý soubor: jen jeho kód mimo funkce, funkce až podle volání
        foreach ($index['files'][$name]['funcs'] as $fn) if (!isset($seenFn[$fn])) { $seenFn[$fn] = true; $stack[] = ['fn', $fn]; }
    }
    foreach ($jsFiles as $rel) {
        if (is_file($root . '/' . $rel)) $words += u69_js_words((string)file_get_contents($root . '/' . $rel));
    }
    return $words;
}

/** Název (třída/id) je použitelný: přesná shoda, nebo předpona skládaného názvu (slovo končící „-“ / „_“). */
function u69_name_used(string $name, array $words, array $prefixes): bool
{
    if (isset($words[$name])) return true;
    foreach ($prefixes as $prefix) if (str_starts_with($name, $prefix)) return true;
    return false;
}

/** Předpony skládaných názvů ze slov. @return list<string> */
function u69_prefixes(array $words): array
{
    return array_values(array_filter(array_keys($words), static fn(string $w): bool => strlen($w) > 2 && (str_ends_with($w, '-') || str_ends_with($w, '_'))));
}

/** Třídy a id použité v části selektoru. @return list<string> */
function u69_selector_names(string $part): array
{
    $plain = (string)preg_replace('~"[^"]*"|\'[^\']*\'~', '', $part);
    $plain = (string)preg_replace('~\[[^\]]*\]~', '', $plain);
    preg_match_all('~[.#]([A-Za-z_-][A-Za-z0-9_-]*)~', $plain, $m);
    return $m[1];
}

/**
 * Část selektoru se zachová, když všechny její třídy/id jsou použitelné a aspoň jedna vychází z kódu stránky ($words: PHP šablony a JS stránky).
 * Třídy, které zná jen obecný JS ($widget: app.js, simulace – vytvářejí DOM jen tam, kde šablona stránky nese jejich kořen), samy pravidlo nezachrání.
 * Část bez tříd a id (značky, :root, atributy) se zachová vždy.
 */
function u69_part_used(string $part, array $words, array $prefixes, array $widget = []): bool
{
    $names = u69_selector_names($part);
    if ($names === []) return true;
    $anchored = false;
    foreach ($names as $name) {
        if (u69_name_used($name, $words, $prefixes)) { $anchored = true; continue; }
        if (!isset($widget[$name])) return false;
    }
    return $anchored;
}
