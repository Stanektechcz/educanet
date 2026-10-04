<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · malý parser CSS pro tools/v61_design_audit.php (pravidla, deklarace, tokeny, px, specificita).
 * Stačí na vlastní soubory projektu (jedno pravidlo = selektor { deklarace }, @media se rozbalí); není to obecný parser.
 */

/**
 * Pravidla CSS bez komentářů; @media/@supports se rozbalí (obsah se vrací jako běžná pravidla), @keyframes/@font-face se přeskočí.
 *
 * @return list<array{sel:string,body:string}>
 */
function v61d_rules(string $css): array
{
    $rules = [];
    $length = strlen($css);
    $pos = 0;
    while ($pos < $length) {
        $open = strpos($css, '{', $pos);
        if ($open === false) break;
        $prelude = trim(substr($css, $pos, $open - $pos));
        $depth = 1;
        $i = $open + 1;
        while ($i < $length && $depth > 0) {
            $ch = $css[$i];
            if ($ch === '{') $depth++;
            elseif ($ch === '}') $depth--;
            $i++;
        }
        $inner = substr($css, $open + 1, $i - $open - 2);
        $pos = $i;
        if (str_starts_with($prelude, '@media') || str_starts_with($prelude, '@supports')) {
            array_push($rules, ...v61d_rules($inner));
        } elseif (!str_starts_with($prelude, '@')) {
            $rules[] = ['sel' => $prelude, 'body' => $inner];
        }
    }
    return $rules;
}

/** @return array<string,string> vlastnost => hodnota (poslední výskyt vyhrává) */
function v61d_decls(string $body): array
{
    $out = [];
    if (preg_match_all('/(--[a-zA-Z0-9_-]+|[a-z-]+)\s*:\s*([^;]+)(?:;|$)/', $body, $m, PREG_SET_ORDER)) {
        foreach ($m as $row) { $out[$row[1]] = trim($row[2]); }
    }
    return $out;
}

/**
 * Deklarace vlastních vlastností (--název) s názvem bloku, ve kterém jsou.
 *
 * @return list<array{name:string,value:string,block:string}>
 */
function v61d_custom_props(string $css): array
{
    $css = (string)preg_replace('~/\*.*?\*/~s', '', $css);
    $out = [];
    foreach (v61d_rules($css) as $rule) {
        foreach (v61d_decls($rule['body']) as $prop => $value) {
            if (str_starts_with($prop, '--')) $out[] = ['name' => substr($prop, 2), 'value' => $value, 'block' => trim($rule['sel'])];
        }
    }
    return $out;
}

/** Hodnota v px: „44px“, „var(--ui-tap)“, „calc(var(--ui-bottomnav-h) - 4px)“ (vlastní vlastnosti z $vars). */
function v61d_px(string $value, array $vars): ?float
{
    $value = trim($value);
    $value = (string)preg_replace_callback('/var\(\s*--([a-zA-Z0-9_-]+)\s*\)/', static fn(array $m): string => audit_css_resolve($vars, 'var(--' . $m[1] . ')'), $value);
    if (preg_match('/^calc\(\s*([\d.]+)px\s*([+-])\s*([\d.]+)px\s*\)$/', $value, $m) === 1) {
        return $m[2] === '+' ? (float)$m[1] + (float)$m[3] : (float)$m[1] - (float)$m[3];
    }
    return preg_match('/^([\d.]+)px$/', $value, $m) === 1 ? (float)$m[1] : null;
}

/** Rozdělí seznam selektorů podle čárek mimo závorky. @return list<string> */
function v61d_split_selectors(string $list): array
{
    $parts = [];
    $depth = 0;
    $current = '';
    foreach (str_split($list) as $ch) {
        if ($ch === '(' || $ch === '[') $depth++;
        if ($ch === ')' || $ch === ']') $depth--;
        if ($ch === ',' && $depth === 0) { $parts[] = trim($current); $current = ''; continue; }
        $current .= $ch;
    }
    if (trim($current) !== '') $parts[] = trim($current);
    return $parts;
}

/**
 * Specificita jednoho selektoru [id, třídy/atributy/pseudotřídy, elementy]; :where() = 0, :is()/:not() = nejsilnější argument.
 *
 * @return array{0:int,1:int,2:int}
 */
function v61d_specificity(string $selector): array
{
    $spec = [0, 0, 0];
    $balanced = '(\((?:[^()]++|(?1))*\))';
    $selector = (string)preg_replace('/:where' . $balanced . '/', '', $selector);
    while (preg_match('/:(is|not|has)' . $balanced . '/', $selector, $m, PREG_OFFSET_CAPTURE) === 1) {
        $args = substr($m[2][0], 1, -1);
        $best = [0, 0, 0];
        foreach (v61d_split_selectors($args) as $arg) {
            $s = v61d_specificity($arg);
            if ($s > $best) $best = $s;
        }
        foreach ($best as $k => $v) { $spec[$k] += $v; }
        $selector = substr($selector, 0, $m[0][1]) . ' ' . substr($selector, $m[0][1] + strlen($m[0][0]));
    }
    $spec[0] += preg_match_all('/#[\w-]+/', $selector);
    $spec[1] += preg_match_all('/\.[\w-]+|\[[^\]]*\]|(?<!:):(?!:)[\w-]+/', $selector);
    $stripped = (string)preg_replace('/\[[^\]]*\]|[.#:][\w-]+/', ' ', $selector);
    $spec[2] += preg_match_all('/(?<![\w-])[a-zA-Z][\w-]*/', $stripped) + preg_match_all('/::[\w-]+/', $selector);
    return $spec;
}
