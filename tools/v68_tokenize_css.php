<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · tools/v68_tokenize_css.php – převod pevných barev v CSS na tokeny palety (var(--kN)).
 *
 *   php tools/v68_tokenize_css.php [--dry] <soubor.css> [<soubor.css> …]
 *
 * Každá barva (#hex, rgb(a)(), white, black; mimo url()) v deklaraci se nahradí var(--kN). Paleta se drží ve dvou generovaných souborech:
 *   assets/tokens-palette-v68.css       – světlé hodnoty (přesně původní barvy → vzhled ve světlém režimu se nemění),
 *   assets/tokens-palette-dark-v68.css  – tmavé hodnoty (v68c_dark: transformace zachovávající poměr kontrastu každé dvojice barev).
 * Token se vytvoří jednou pro každou jedinečnou barvu a stabilně se znovu používá (čísla se nepřidělují znovu). Soubor se přepisuje na místě
 * (zálohu si vyrob předem), --dry jen vypíše počty. Gradienty, stíny i barvy v custom properties se převedou stejně.
 * Konec: V68_TOKENIZE_OK files=N replaced=M tokens=K
 */

require_once __DIR__ . '/lib/v68_color.php';

/** Sdílené soubory CSS (načítá je i žákovská část) → cockpit dostane vlastní tokenizovanou kopii assets/cx-<název> (originál zůstává beze změny). */
const TK68_DERIVED = [
    'app.css', 'mastery.css', 'hands-on-v50.css', 'cognitive-v43.css', 'learning-studio-v44.css', 'visual-simulation-v45.css', 'visual-practical-v48.css',
    'visual-labs-3a-v48-1.css', 'ui-v51.css', 'tutorial-v52.css', 'session-v53.css', 'brand-v54.css', 'linux-v57.css', 'arena-v57.css',
    'arena-events-v58.css', 'marketplace-v60.css', 'projects-v60.css', 'feedback-v60.css', 'competency-v62.css', 'paths-v63.css', 'projects-v65.css',
    'assessment-v66.css', 'arena-v64.css', 'motivation-v61.css', 'robots-v58.css', 'teamgames-v58.css',
];
const TK68_LIGHT = 'assets/tokens-palette-v68.css';
const TK68_DARK = 'assets/tokens-palette-dark-v68.css';

function tk68_key(array $c): string
{
    return v68c_css($c);
}

/** @return array<string,int> světlá barva (klíč) → číslo tokenu */
function tk68_load_palette(string $root): array
{
    $out = [];
    $text = is_file($root . '/' . TK68_LIGHT) ? (string)file_get_contents($root . '/' . TK68_LIGHT) : '';
    if (preg_match_all('/--k(\d+)\s*:\s*([^;]+);/', $text, $m, PREG_SET_ORDER) > 0) {
        foreach ($m as $row) {
            $parsed = v68c_parse(trim($row[2]));
            if ($parsed !== null) $out[tk68_key($parsed)] = (int)$row[1];
        }
    }
    return $out;
}

function tk68_write_palette(string $root, array $palette): void
{
    asort($palette);
    $light = "/* EDUCANET v68 · paleta (světlé hodnoty) – GENEROVÁNO tools/v68_tokenize_css.php, ručně neupravovat. */\n:root {\n";
    $dark = "/* EDUCANET v68 · paleta (tmavé hodnoty, poměr kontrastu zachován) – GENEROVÁNO tools/v68_tokenize_css.php. */\n:root:not([data-theme=\"light\"]) {\n";
    foreach ($palette as $key => $n) {
        $c = v68c_parse($key);
        if ($c === null) continue;
        $light .= '  --k' . $n . ': ' . $key . ";\n";
        $dark .= '  --k' . $n . ': ' . v68c_css(v68c_dark($c)) . ";\n";
    }
    file_put_contents($root . '/' . TK68_LIGHT, $light . "}\n");
    file_put_contents($root . '/' . TK68_DARK, $dark . "}\n");
}

/** @param array<string,int> $palette (předáno odkazem, doplňuje se) */
function tk68_convert(string $css, array &$palette, int &$replaced): string
{
    $colorRe = '~url\([^)]*\)|#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)|(?<![-\w.#%])(?:white|black)(?![-\w(])~';
    return (string)preg_replace_callback('~(--[\w-]+|[a-zA-Z-]+)(\s*:\s*)([^;{}]*)(?=[;}])~', static function (array $m) use (&$palette, &$replaced, $colorRe): string {
        $value = (string)preg_replace_callback($colorRe, static function (array $c) use (&$palette, &$replaced): string {
            if (str_starts_with($c[0], 'url(')) return $c[0];
            $parsed = v68c_parse($c[0]);
            if ($parsed === null) return $c[0];
            $key = tk68_key($parsed);
            if (!isset($palette[$key])) $palette[$key] = ($palette === [] ? 0 : max($palette)) + 1;
            $replaced++;
            return 'var(--k' . $palette[$key] . ')';
        }, $m[3]);
        return $m[1] . $m[2] . $value;
    }, $css);
}

/** Obsah odvozené kopie: hlavička se zdrojem a SHA-256 originálu + tokenizovaný text. */
function tk68_derived_text(string $root, string $name, array &$palette, int &$replaced): string
{
    $src = (string)file_get_contents($root . '/assets/' . $name);
    $head = '/* GENEROVÁNO tools/v68_tokenize_css.php --derive ze souboru assets/' . $name . ' (sha256 ' . substr(hash('sha256', $src), 0, 16) . ") – ručně neupravovat. */\n";
    return $head . tk68_convert($src, $palette, $replaced);
}

/** --derive: vytvoří (nebo s --check jen ověří, že jsou aktuální) kopie assets/cx-*.css. */
function tk68_derive(string $root, bool $check): int
{
    $palette = tk68_load_palette($root);
    $stale = [];
    $total = 0;
    foreach (TK68_DERIVED as $name) {
        $n = 0;
        $text = tk68_derived_text($root, $name, $palette, $n);
        $total += $n;
        $target = $root . '/assets/cx-' . $name;
        $same = is_file($target) && hash_equals(hash('sha256', $text), hash_file('sha256', $target));
        if (!$same) $stale[] = $name;
        if (!$check) file_put_contents($target, $text);
    }
    if ($check) {
        echo $stale === [] ? "V68_DERIVE_CURRENT files=" . count(TK68_DERIVED) . "\n" : 'V68_DERIVE_STALE ' . implode(',', $stale) . "\n";
        return $stale === [] ? 0 : 1;
    }
    tk68_write_palette($root, $palette);
    echo 'V68_DERIVE_OK files=' . count(TK68_DERIVED) . ' replaced=' . $total . ' tokens=' . count($palette) . "\n";
    return 0;
}

function tk68_main(array $argv): int
{
    $root = str_replace(chr(92), '/', dirname(__DIR__));
    $dry = in_array('--dry', $argv, true);
    $files = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '--')));
    if (in_array('--derive', $argv, true)) return tk68_derive($root, in_array('--check', $argv, true));
    if (in_array('--rebuild', $argv, true)) { tk68_write_palette($root, tk68_load_palette($root)); echo "V68_TOKENIZE_REBUILT
"; return 0; }
    if ($files === []) { fwrite(STDERR, "Použití: php tools/v68_tokenize_css.php [--dry] soubor.css …\n"); return 2; }
    $palette = tk68_load_palette($root);
    $total = 0;
    foreach ($files as $rel) {
        if (!str_starts_with($rel, 'assets/') || !str_ends_with($rel, '.css') || str_contains($rel, '..') || !is_file($root . '/' . $rel)) { fwrite(STDERR, "Odmítnuto: $rel\n"); return 2; }
        $before = (string)file_get_contents($root . '/' . $rel);
        $n = 0;
        $after = tk68_convert($before, $palette, $n);
        $total += $n;
        printf("%-40s %6d -> %6d B, nahrazeno %d\n", $rel, strlen($before), strlen($after), $n);
        if (!$dry) file_put_contents($root . '/' . $rel, $after);
    }
    if (!$dry) tk68_write_palette($root, $palette);
    echo 'V68_TOKENIZE_OK files=' . count($files) . ' replaced=' . $total . ' tokens=' . count($palette) . "\n";
    return 0;
}

exit(tk68_main($argv));
