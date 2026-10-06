<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v68 · barvy pro generátor palety a audit kontrastu (tools/v68_tokenize_css.php, tools/v68_theme_audit.php).
 *
 * v68c_parse()    – "#rgb", "#rrggbb", "#rrggbbaa", "rgb(a)(r,g,b,a)", "white|black" → [r,g,b,a] (0–255, a 0–1) nebo null.
 * v68c_lum()      – relativní jas podle WCAG; v68c_ratio() – poměr kontrastu dvou barev.
 * v68c_dark()     – tmavá varianta barvy transformací, která ZACHOVÁVÁ poměr kontrastu mezi libovolnou dvojicí barev:
 *                   f(Y) = c / (Y + 0,05)^γ − 0,05 (γ = V68C_DARK_GAMMA): poměr kontrastu libovolné dvojice se umocní na γ = 1,4,
 *                   takže dvojice, které splňovaly kontrast ve světlém režimu, ho splní i v tmavém (světlá plocha → tmavá, tmavý text → světlý).
 *                   Odstín zůstává (kanály se škálují), při přetečení se sytost snižuje tak, aby jas zůstal přesný.
 *                   Stíny (téměř černé, průhledné) zůstávají tmavé.
 */

const V68C_DARK_BG_Y = 0.0085;   // jas nejsvětlejší plochy (bílá) v tmavém režimu ≈ #14181d
const V68C_DARK_GAMMA = 1.4;     // poměr kontrastu v tmavém režimu = (poměr ve světlém)^1,4 → dvojice, které byly na hraně AA, ji v tmavém splní

function v68c_parse(string $raw): ?array
{
    $raw = strtolower(trim($raw));
    if ($raw === 'white') return [255, 255, 255, 1.0];
    if ($raw === 'black') return [0, 0, 0, 1.0];
    if (preg_match('/^#([0-9a-f]{3,8})$/', $raw, $m) === 1) {
        $h = $m[1];
        if (strlen($h) === 3 || strlen($h) === 4) $h = implode('', array_map(static fn(string $c): string => $c . $c, str_split($h)));
        if (strlen($h) !== 6 && strlen($h) !== 8) return null;
        $a = strlen($h) === 8 ? round(hexdec(substr($h, 6, 2)) / 255, 3) : 1.0;
        return [(int)hexdec(substr($h, 0, 2)), (int)hexdec(substr($h, 2, 2)), (int)hexdec(substr($h, 4, 2)), $a];
    }
    if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*([0-9.]+)\s*)?\)$/', $raw, $m) === 1) {
        return [min(255, (int)$m[1]), min(255, (int)$m[2]), min(255, (int)$m[3]), isset($m[4]) && $m[4] !== '' ? min(1.0, (float)$m[4]) : 1.0];
    }
    return null;
}

function v68c_lin(int|float $c): float
{
    $c /= 255;
    return $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
}

function v68c_gamma(float $l): int
{
    $l = max(0.0, min(1.0, $l));
    $c = $l <= 0.0031308 ? $l * 12.92 : 1.055 * ($l ** (1 / 2.4)) - 0.055;
    return (int)round(max(0.0, min(1.0, $c)) * 255);
}

function v68c_lum(array $rgb): float
{
    return 0.2126 * v68c_lin($rgb[0]) + 0.7152 * v68c_lin($rgb[1]) + 0.0722 * v68c_lin($rgb[2]);
}

/** Poměr kontrastu; průhledná barva se předem smíchá s $under. */
function v68c_ratio(array $a, array $b, ?array $under = null): float
{
    $a = v68c_flatten($a, $under ?? $b);
    $b = v68c_flatten($b, $under ?? [255, 255, 255, 1.0]);
    $la = v68c_lum($a);
    $lb = v68c_lum($b);
    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

function v68c_flatten(array $c, array $under): array
{
    $a = (float)($c[3] ?? 1.0);
    if ($a >= 1.0) return $c;
    return [(int)round($c[0] * $a + $under[0] * (1 - $a)), (int)round($c[1] * $a + $under[1] * (1 - $a)), (int)round($c[2] * $a + $under[2] * (1 - $a)), 1.0];
}

/** Tmavá varianta ([r,g,b,a] → [r,g,b,a]). */
function v68c_dark(array $c): array
{
    [$r, $g, $b, $a] = [$c[0], $c[1], $c[2], (float)($c[3] ?? 1.0)];
    $y = v68c_lum([$r, $g, $b]);
    if ($a < 1.0 && $y < 0.02) return [0, 0, 0, round(min(0.85, $a * 1.6), 3)];   // stín zůstává tmavý (a o něco výraznější)
    $k = (V68C_DARK_BG_Y + 0.05) * (1.05 ** V68C_DARK_GAMMA);
    $target = min(1.0, $k / (($y + 0.05) ** V68C_DARK_GAMMA) - 0.05);
    $lin = [v68c_lin($r), v68c_lin($g), v68c_lin($b)];
    $scaled = $y > 1e-5 ? array_map(static fn(float $v): float => $v * $target / $y, $lin) : [$target, $target, $target];
    $s = 1.0;
    foreach ($scaled as $v) if ($v > 1.0) $s = min($s, ($target < 1.0 ? (1.0 - $target) / ($v - $target) : 0.0));
    $out = array_map(static fn(float $v): int => v68c_gamma($target + $s * ($v - $target)), $scaled);
    return [$out[0], $out[1], $out[2], $a];
}

/** Zápis pro CSS: #rrggbb nebo rgba(r,g,b,a). */
function v68c_css(array $c): string
{
    $a = (float)($c[3] ?? 1.0);
    if ($a >= 1.0) return sprintf('#%02x%02x%02x', $c[0], $c[1], $c[2]);
    $alpha = rtrim(rtrim(number_format($a, 3, '.', ''), '0'), '.');
    return 'rgba(' . $c[0] . ',' . $c[1] . ',' . $c[2] . ',' . ($alpha === '' ? '0' : $alpha) . ')';
}
