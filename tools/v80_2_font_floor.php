<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · tools/v80_2_font_floor.php – spodní hranice písma 14 px ve starších vrstvách CSS (zadání: „nikdy < 14 px“).
 *
 *   php tools/v80_2_font_floor.php            přepíše každé `font-size: V` menší než 14 px na `font-size: max(.875rem, V)` (hodnota se zachová,
 *                                              jen nikdy neklesne pod 14 px; em/% dál závisí na rodiči); idempotentní
 *   php tools/v80_2_font_floor.php --check    jen ověří, že žádná taková deklarace nezbyla (V80_2_FONT_FLOOR_OK / _STALE, exit 1)
 *
 * Bez změny: @media print (tisk A4 beze změny funkce), svg/text (grafy), tiskové soubory, odvozené kopie cx-* a tmavé vrstvy dark/*
 * (generují je tools/v68_tokenize_css.php --derive a tools/v69_dark_overlay.php ze zdrojů), tokeny a vrstvy v78+ (písmo jen přes --fs-*),
 * a zamčené soubory Linux Labu (linux-v57.css, arena-v57.css – SHA v tools/lib/v67_lab_hashes.php); lab má přepisy v lab-a11y-v58.css.
 * Mění se jen prezentace, ne chování ani data.
 */

const FF802_SKIP = ['linux-v57.css', 'arena-v57.css', 'print-v75.css', 'print-v76.css', 'lesson-print-v73.css', 'assessment-v66-print.css',
    'tokens-v61.css', 'tokens-v78.css', 'tokens-dark-v67.css', 'tokens-palette-v68.css', 'tokens-palette-dark-v68.css', 'lab-floor-v80-2.css'];

/** Hodnota v px (null = nečíselná / clamp / už s max()). */
function ff802_px(string $v): ?float
{
    if (preg_match('~^\s*([\d.]+)(px|rem|em|%)\s*$~', $v, $m) !== 1) return null;
    return match ($m[2]) { 'px' => (float)$m[1], 'rem', 'em' => (float)$m[1] * 16, '%' => (float)$m[1] * 0.16 };
}

/**
 * Nová hodnota: absolutní jednotky (px, rem) pod 14 px rovnou .875rem (max(.875rem, V) by dalo totéž, jen delší zápis – rozpočet CSS v61),
 * relativní (em, %) max(.875rem, V), aby zůstala vazba na rodiče.
 */
function ff802_value(string $v): string
{
    return preg_match('~(px|rem)\s*$~', $v) === 1 ? '.875rem' : 'max(.875rem, ' . $v . ')';
}

/** Přepíše deklarace v těle pravidla; $n = počet změn. */
function ff802_body(string $body, int &$n): string
{
    return (string)preg_replace_callback('~(?<![-\w])font-size(\s*:\s*)([^;}!]+?)(\s*(?:!important)?\s*(?:;|$))~i', static function (array $m) use (&$n): string {
        $px = ff802_px($m[2]);
        if ($px === null || $px >= 13.95) return $m[0];
        $n++;
        return 'font-size' . $m[1] . ff802_value(trim($m[2])) . $m[3];
    }, $body);
}

/** Projde CSS (vnořené @media/@supports/@container), přeskočí @media print a svg; vrátí nový text. */
function ff802_rewrite(string $css, int &$n): string
{
    $out = '';
    $len = strlen($css);
    $i = 0;
    $stack = [];   // true = uvnitř tiskového bloku
    $start = 0;
    while ($i < $len) {
        $ch = $css[$i];
        if ($ch === '/' && ($css[$i + 1] ?? '') === '*') { $end = strpos($css, '*/', $i + 2); $i = $end === false ? $len : $end + 2; continue; }
        if ($ch === '"' || $ch === "'") { $end = strpos($css, $ch, $i + 1); $i = $end === false ? $len : $end + 1; continue; }
        if ($ch === '{') {
            $prelude = trim((string)preg_replace('~/\*.*?\*/~s', '', substr($css, $start, $i - $start)));
            $prelude = trim((string)substr($prelude, (int)max(strrpos($prelude, ';') === false ? -1 : strrpos($prelude, ';'), -1) + 1));
            if (str_starts_with($prelude, '@')) {
                $print = str_starts_with(strtolower($prelude), '@media') && str_contains(strtolower($prelude), 'print') && !str_contains(strtolower($prelude), 'screen');
                $stack[] = $print || in_array(true, $stack, true);
                $out .= substr($css, $start, $i + 1 - $start);
                $i++;
                $start = $i;
                continue;
            }
            $close = strpos($css, '}', $i);
            if ($close === false) break;
            $body = substr($css, $i + 1, $close - $i - 1);
            // svg: text v souřadnicích viewBoxu (i třídy s fill/stroke) se neupravuje – měřítko určuje obrázek, ne písmo stránky
            $skip = in_array(true, $stack, true) || preg_match('#(^|[\s>+~,(])(svg|text|tspan|html-scene)\b#i', $prelude) === 1
                || preg_match('#(^|[;{\s])(fill|stroke)\s*:#i', $body) === 1;
            $out .= substr($css, $start, $i + 1 - $start) . ($skip ? $body : ff802_body($body, $n)) . '}';
            $i = $close + 1;
            $start = $i;
            continue;
        }
        if ($ch === '}') { array_pop($stack); $out .= substr($css, $start, $i + 1 - $start); $i++; $start = $i; continue; }
        $i++;
    }
    return $out . substr($css, $start);
}

/** Zamčené soubory Linux Labu: přepisy se generují do samostatné vrstvy (načítá ji app/views/_layout.php hned po nich). */
const FF802_LOCKED = ['linux-v57.css', 'arena-v57.css'];
const FF802_LAB_OUT = 'assets/lab-floor-v80-2.css';

/** Přepisy písma pro zamčený soubor: stejný selektor (a stejné @media), jen `font-size: max(.875rem, V)`. */
function ff802_overrides(string $css, string $name): string
{
    $out = '';
    $len = strlen($css);
    $i = 0;
    $start = 0;
    $at = [];
    while ($i < $len) {
        $ch = $css[$i];
        if ($ch === '/' && ($css[$i + 1] ?? '') === '*') { $end = strpos($css, '*/', $i + 2); $i = $end === false ? $len : $end + 2; $start = $start > $i ? $start : $start; continue; }
        if ($ch === '{') {
            $prelude = trim((string)preg_replace('~/\*.*?\*/~s', '', substr($css, $start, $i - $start)));
            if (str_starts_with($prelude, '@')) { $at[] = $prelude; $i++; $start = $i; continue; }
            $close = strpos($css, '}', $i);
            if ($close === false) break;
            $body = substr($css, $i + 1, $close - $i - 1);
            $print = array_filter($at, static fn(string $a): bool => str_contains(strtolower($a), 'print') && !str_contains(strtolower($a), 'screen')) !== [];
            $svg = preg_match('#(^|[\s>+~,(])(svg|text|tspan)\b#i', $prelude) === 1 || preg_match('#(^|[;{\s])(fill|stroke)\s*:#i', $body) === 1;
            if (!$print && !$svg && preg_match_all('~(?<![-\w])font-size\s*:\s*([^;}!]+?)\s*(!important)?\s*(?:;|$)~i', $body, $mm, PREG_SET_ORDER)) {
                foreach ($mm as $m) {
                    $px = ff802_px($m[1]);
                    if ($px === null || $px >= 13.95) continue;
                    $rule = $prelude . '{font-size:' . ff802_value(trim($m[1])) . (($m[2] ?? '') !== '' ? ' !important' : '') . '}';
                    foreach (array_reverse($at) as $a) $rule = $a . '{' . $rule . '}';
                    $out .= $rule . "\n";
                }
            }
            $i = $close + 1;
            $start = $i;
            continue;
        }
        if ($ch === '}') { array_pop($at); $i++; $start = $i; continue; }
        $i++;
    }
    return $out === '' ? '' : '/* ' . $name . ' */' . "\n" . $out;
}

/** Text vrstvy přepisů pro zamčené soubory labu. */
function ff802_lab_text(string $root): string
{
    $body = '';
    $src = '';
    foreach (FF802_LOCKED as $b) {
        $css = (string)file_get_contents($root . '/assets/' . $b);
        $src .= $b . ' ' . substr(hash('sha256', $css), 0, 16) . ' ';
        $body .= ff802_overrides($css, $b);
    }
    return '/* GENEROVÁNO tools/v80_2_font_floor.php – ručně neupravovat. Spodní hranice písma 14 px pro zamčené soubory Linux Labu (' . trim($src) . ").\n"
        . '   Stejné selektory jako zdroj, načítá se hned po nich (app/views/_layout.php), takže vyhrají jen u písma; zdroje se nemění (SHA v tools/lib/v67_lab_hashes.php). */' . "\n" . $body;
}

/** @return list<string> soubory k úpravě (relativně k assets/) */
function ff802_files(string $root): array
{
    $files = [];
    foreach (glob($root . '/assets/*.css') ?: [] as $f) {
        $b = basename($f);
        if (str_starts_with($b, 'cx-') || in_array($b, FF802_SKIP, true)) continue;
        $files[] = $b;
    }
    sort($files);
    return $files;
}

function ff802_main(array $argv): int
{
    $root = str_replace(chr(92), '/', dirname(__DIR__));
    $check = in_array('--check', $argv, true);
    $stale = [];
    $total = 0;
    foreach (ff802_files($root) as $b) {
        $path = $root . '/assets/' . $b;
        $css = (string)file_get_contents($path);
        $n = 0;
        $new = ff802_rewrite($css, $n);
        if ($n === 0) continue;
        $total += $n;
        if ($check) { $stale[] = $b . ' (' . $n . ')'; continue; }
        file_put_contents($path, $new);
        echo 'upraveno ' . $b . ': ' . $n . "\n";
    }
    $lab = ff802_lab_text($root);
    $labPath = $root . '/' . FF802_LAB_OUT;
    if ($check && (!is_file($labPath) || (string)file_get_contents($labPath) !== $lab)) $stale[] = FF802_LAB_OUT . ' (zastaralé)';
    if (!$check && (!is_file($labPath) || (string)file_get_contents($labPath) !== $lab)) { file_put_contents($labPath, $lab); echo 'vygenerováno ' . FF802_LAB_OUT . ': ' . substr_count($lab, 'max(.875rem') . " přepisů\n"; }
    if ($check) { echo $stale === [] ? "V80_2_FONT_FLOOR_OK\n" : 'V80_2_FONT_FLOOR_STALE ' . implode(', ', $stale) . "\n"; return $stale === [] ? 0 : 1; }
    echo 'V80_2_FONT_FLOOR_DONE declarations=' . $total . "\n";
    return 0;
}

if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === realpath(__FILE__)) exit(ff802_main($argv));
