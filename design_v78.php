<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v78 · design systém – čistá logika bez výstupu (sdílí ukázková záložka design78 a tools/v78_design_tokens_audit.php).
 *   - ds78_token_sets(): proměnné z assets/tokens-v78.css pro světlý režim, tmavý režim (volba „tmavý“) a tmavý režim podle systému,
 *   - ds78_text_pairs() / ds78_ui_pairs(): dvojice popředí × pozadí, které komponenty skutečně používají (text ≥ 4,5 : 1, UI ≥ 3 : 1),
 *   - ds78_contrast_rows(): kontrast všech dvojic v obou režimech (WCAG 2.x),
 *   - ds78_breakpoints(): jediné povolené šířky pro @media / @container v souborech v78.
 * Nic nečte z úložiště a nic nezapisuje.
 */

const DS78_TOKENS_CSS = 'assets/tokens-v78.css';
const DS78_COMPONENTS_CSS = 'assets/components-v78.css';
const DS78_DEMO_CSS = 'assets/design-v78.css';
const DS78_JS = 'assets/components-v78.js';
const DS78_MIN_TEXT = 4.5;
const DS78_MIN_UI = 3.0;
const DS78_TARGET_MIN_PX = 44;
const DS78_MIN_FONT_PX = 14;

/** @return array<string, int> token → šířka v px (xs … xxl) */
function ds78_breakpoints(): array
{
    return ['xs' => 320, 'sm' => 480, 'md' => 768, 'lg' => 1024, 'xl' => 1366, 'xxl' => 1920];
}

function ds78_tokens_css(?string $root = null): string
{
    $file = ($root ?? __DIR__) . '/' . DS78_TOKENS_CSS;
    return is_file($file) ? (string)file_get_contents($file) : '';
}

/** Deklarace „--jméno: hodnota;“ jednoho bloku. @return array<string, string> */
function ds78_vars(string $block): array
{
    $out = [];
    if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $block, $m, PREG_SET_ORDER) === false) return [];
    foreach ($m as $row) $out[$row[1]] = trim($row[2]);
    return $out;
}

/**
 * Tři sady tokenů: light (.ds78), dark (:root[data-theme="dark"] .ds78), dark_system (v @media prefers-color-scheme: dark).
 * @return array{light: array<string,string>, dark: array<string,string>, dark_system: array<string,string>}
 */
function ds78_token_sets(?string $css = null): array
{
    $css = preg_replace('~/\*.*?\*/~s', '', $css ?? ds78_tokens_css()) ?? '';
    $grab = static function (string $selectorRe) use ($css): array {
        return preg_match('~' . $selectorRe . '\s*\{([^{}]*)\}~', $css, $m) === 1 ? ds78_vars($m[1]) : [];
    };
    return [
        'light' => $grab('(?:^|\})\s*\.ds78'),
        'dark' => $grab(':root\[data-theme="dark"\]\s+\.ds78'),
        'dark_system' => $grab(':root:not\(\[data-theme="light"\]\):not\(\[data-theme="dark"\]\)\s+\.ds78'),
    ];
}

/** Dvojice text × pozadí (≥ 4,5 : 1). @return list<array{0:string,1:string}> */
function ds78_text_pairs(): array
{
    $pairs = [];
    foreach (['text', 'muted', 'accent', 'accent-hover', 'ok', 'warn', 'danger', 'info', 'brand-2'] as $fg) {
        foreach (['bg', 'surface', 'surface-2'] as $bg) $pairs[] = [$fg, $bg];
    }
    foreach ([['on-accent', 'accent'], ['on-accent', 'accent-hover'], ['on-brand', 'brand'], ['on-highlight', 'highlight'], ['on-danger', 'danger'], ['on-danger', 'danger-hover'],
        ['accent', 'accent-soft'], ['ok', 'ok-soft'], ['warn', 'warn-soft'], ['danger', 'danger-soft'], ['info', 'info-soft'],
        ['text', 'accent-soft'], ['text', 'ok-soft'], ['text', 'warn-soft'], ['text', 'danger-soft'], ['text', 'info-soft'], ['surface', 'ok']] as $p) $pairs[] = $p;
    return $pairs;
}

/** Dvojice prvek UI × pozadí (≥ 3 : 1): rámeček polí, fokus, indikátory a stavové proužky. @return list<array{0:string,1:string}> */
function ds78_ui_pairs(): array
{
    $pairs = [];
    foreach (['border', 'focus', 'accent'] as $fg) {
        foreach (['bg', 'surface', 'surface-2'] as $bg) $pairs[] = [$fg, $bg];
    }
    foreach (['ok', 'warn', 'danger', 'info'] as $fg) $pairs[] = [$fg, 'surface'];
    return $pairs;
}

/** Kontrastní poměr dvou hex barev (WCAG 2.x), nebo null pro neplatnou hodnotu. */
function ds78_contrast(string $a, string $b): ?float
{
    $lum = static function (string $hex): ?float {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        if (preg_match('/^[0-9a-f]{6}$/i', $hex) !== 1) return null;
        $sum = 0.0;
        foreach ([[0, 0.2126], [2, 0.7152], [4, 0.0722]] as [$i, $w]) {
            $v = hexdec(substr($hex, $i, 2)) / 255;
            $sum += $w * ($v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4);
        }
        return $sum;
    };
    $la = $lum($a);
    $lb = $lum($b);
    if ($la === null || $lb === null) return null;
    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

/**
 * Kontrast všech dvojic v obou režimech.
 * @return list<array{fg:string, bg:string, kind:string, min:float, light:?float, dark:?float, ok:bool}>
 */
function ds78_contrast_rows(?array $sets = null): array
{
    $sets ??= ds78_token_sets();
    $rows = [];
    foreach ([['text', DS78_MIN_TEXT, ds78_text_pairs()], ['ui', DS78_MIN_UI, ds78_ui_pairs()]] as [$kind, $min, $pairs]) {
        foreach ($pairs as [$fg, $bg]) {
            $light = ds78_contrast((string)($sets['light'][$fg] ?? ''), (string)($sets['light'][$bg] ?? ''));
            $dark = ds78_contrast((string)($sets['dark'][$fg] ?? ''), (string)($sets['dark'][$bg] ?? ''));
            $rows[] = ['fg' => $fg, 'bg' => $bg, 'kind' => $kind, 'min' => $min, 'light' => $light, 'dark' => $dark,
                'ok' => $light !== null && $dark !== null && $light >= $min && $dark >= $min];
        }
    }
    return $rows;
}

/** Barevné tokeny pro ukázku (pořadí = pořadí v tabulce barev). @return list<array{0:string,1:string}> */
function ds78_color_tokens(): array
{
    return [
        ['bg', 'Pozadí stránky'], ['surface', 'Plocha karty'], ['surface-2', 'Druhá plocha, hlavička tabulky'], ['text', 'Text'], ['muted', 'Doplňkový text'],
        ['line', 'Dělicí čára (dekorace)'], ['border', 'Rámeček pole a tlačítka'], ['accent', 'Akcent: primární akce, odkazy'], ['accent-soft', 'Jemný akcent'],
        ['brand', 'Barva školy (tmavě modrá)'], ['highlight', 'Zvýraznění školy (žlutá)'], ['ok', 'Hotovo / v pořádku'], ['warn', 'Pozor'], ['danger', 'Chyba / nevratná akce'], ['info', 'Informace'],
    ];
}
