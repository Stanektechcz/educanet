<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · tools/v68_dark_overlay.php – tmavá vrstva žákovské části generovaná ze stávajících CSS (originály zůstávají beze změny).
 *
 *   php tools/v68_dark_overlay.php            vygeneruje assets/cockpit-dark-v68.css (v69: žákovská část se generuje po pohledech nástrojem tools/v69_dark_overlay.php)
 *   php tools/v68_dark_overlay.php --check    ověří, že soubor odpovídá aktuálním zdrojům (V68_OVERLAY_CURRENT / V68_OVERLAY_STALE, exit 1)
 *
 * Proč overlay místo úpravy zdrojů: žákovské stránky mají rozpočet CSS (V61D_CSS_BUDGET_BYTES = 28 672 B, nezvyšovat) a světlý vzhled
 * se nesmí změnit. Pro každé pravidlo zdrojových CSS, které obsahuje pevnou barvu (#hex, rgb(a), white, black), se vypíše pravidlo
 * `html:not([data-theme="light"]) <selektor> { jen deklarace s barvami v tmavé variantě }` (v68c_dark: transformace zachovávající a zesilující
 * kontrast). Soubor se linkuje jen při tmavém motivu (ui67_dark_link_html), takže světlé stránky nic nenavíc nestahují.
 * Předpona zvyšuje specificitu všech pravidel stejně o (0,1,1), takže overlay vyhrává nezávisle na pořadí načtení (i nad CSS linkovaným v těle stránky).
 * Aby se nezměnily vztahy mezi pravidly (stavy .is-current, :hover … s proměnnými místo pevných barev), jsou v overlayi VŠECHNY barevné deklarace
 * (color, background*, border*, outline*, box-shadow, fill, stroke …): pevné barvy v tmavé variantě, ostatní (var(), klíčová slova) beze změny.
 * Deklarace s !important si !important zachovají.
 */

require_once __DIR__ . '/lib/v68_color.php';

/** Cockpit: CSS linkované přímo v šablonách Linux Labu (SHA-256 hlídá tools/lib/v67_lab_hashes.php → šablonu neměnit), proto overlay místo kopie. */
const DO68_COCKPIT_SOURCES = ['lab-editor-v58.css'];
const DO68_COCKPIT_OUT = 'assets/cockpit-dark-v68.css';
// celé rodiny border*, outline*, background* (zkratky a podvlastnosti), aby se zachovalo pořadí zkratky a podvlastností (border: solid …; border-width: …)
const DO68_COLOR_PROP_RE = '~^(color|background[a-z-]*|border[a-z-]*|outline[a-z-]*|box-shadow|text-shadow|fill|stroke|caret-color|accent-color|text-decoration-color|column-rule-color)$~';
/** Sytě tmavé plochy s bílým textem (hero profilu a přihlášení): celý podstrom zůstává v původním vzhledu, v tmavém režimu se neinvertuje. */
const DO68_KEEP_ORIGINAL = ['.p60-hero', '.p60-preview', '.auth54-hero'];
const DO68_PREFIX = 'html:not([data-theme="light"])';
const DO68_COLOR_RE = '~url\([^)]*\)|#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)|(?<![-\w.#%])(?:white|black)(?![-\w(])~';

/** Rozdělí text podle znaku mimo závorky a uvozovky. @return list<string> */
function do68_split(string $text, string $sep): array
{
    $parts = [];
    $depth = 0;
    $quote = '';
    $cur = '';
    for ($i = 0, $n = strlen($text); $i < $n; $i++) {
        $c = $text[$i];
        if ($quote !== '') { if ($c === $quote && $text[$i - 1] !== chr(92)) $quote = ''; }
        elseif ($c === '"' || $c === "'") $quote = $c;
        elseif ($c === '(' || $c === '[') $depth++;
        elseif ($c === ')' || $c === ']') $depth = max(0, $depth - 1);
        elseif ($c === $sep && $depth === 0) { $parts[] = $cur; $cur = ''; continue; }
        $cur .= $c;
    }
    $parts[] = $cur;
    return $parts;
}

/** Selektor → selektor s předponou tmavého režimu (každá část seznamu zvlášť). */
function do68_selector(string $selector): string
{
    $out = [];
    foreach (do68_split($selector, ',') as $part) {
        $part = trim($part);
        if ($part === '') continue;
        foreach (DO68_KEEP_ORIGINAL as $keep) if (str_contains($part, $keep)) continue 2;
        if (preg_match('~^(:root|html)(?![\w-])(.*)$~s', $part, $m) === 1) $out[] = DO68_PREFIX . $m[2];
        else $out[] = DO68_PREFIX . ' ' . $part;
    }
    return implode(', ', $out);
}

/** Deklarace s barvami → tmavé hodnoty. @return string  např. "background:#171717;color:#fdfeff" */
function do68_decls(string $body): string
{
    $out = [];
    foreach (do68_split($body, ';') as $decl) {
        if (preg_match('~^\s*([a-zA-Z-]+|--[\w-]+)\s*:\s*(.+?)\s*$~s', $decl, $m) !== 1) continue;
        $n = 0;
        $value = (string)preg_replace_callback(DO68_COLOR_RE, static function (array $c) use (&$n): string {
            if (str_starts_with($c[0], 'url(')) return $c[0];
            $p = v68c_parse($c[0]);
            if ($p === null) return $c[0];
            $n++;
            return v68c_css(v68c_dark($p));
        }, $m[2]);
        if ($n > 0 || preg_match(DO68_COLOR_PROP_RE, $m[1]) === 1) $out[] = $m[1] . ':' . preg_replace('~\s+~', ' ', $value);
    }
    return implode(';', $out);
}

/** Najde konec bloku ({ … }) od pozice za otevírací závorkou; vrací pozici uzavírací závorky. */
function do68_block_end(string $css, int $from): int
{
    $depth = 1;
    for ($i = $from, $n = strlen($css); $i < $n; $i++) {
        if ($css[$i] === '{') $depth++;
        elseif ($css[$i] === '}' && --$depth === 0) return $i;
    }
    return $n;
}

/** Rekurzivně projde CSS a vrátí overlay pravidla (zachová obálky @media/@supports). */
function do68_walk(string $css): string
{
    $css = (string)preg_replace('~/\*.*?\*/~s', '', $css);
    $out = '';
    $pos = 0;
    $len = strlen($css);
    while ($pos < $len) {
        $open = strpos($css, '{', $pos);
        if ($open === false) break;
        $prelude = trim(substr($css, $pos, $open - $pos));
        $end = do68_block_end($css, $open + 1);
        $inner = substr($css, $open + 1, $end - $open - 1);
        $pos = $end + 1;
        if ($prelude === '') continue;
        if ($prelude[0] === '@') {
            if (preg_match('~^@(media|supports|layer)\b~', $prelude) === 1) {
                $sub = do68_walk($inner);
                if ($sub !== '') $out .= $prelude . '{' . $sub . '}';
            }
            continue;
        }
        $decls = do68_decls($inner);
        if ($decls !== '') $out .= do68_selector($prelude) . '{' . $decls . "}\n";
    }
    return $out;
}

/** Ručně psaný závěr overlaye (proměnné → tokeny). */
const DO68_TAIL = <<<'CSS'
/* --- ručně psaný závěr overlaye: proměnné starších vrstev vedou na tokeny --ui-* z tokens-dark-v67.css (barvy bez překladu přes var()) --- */
html:not([data-theme="light"]):not(.x) { --u-bg: var(--ui-bg); --u-surface: var(--ui-surface); --u-soft: var(--ui-surface-2); --u-line: var(--ui-line); --u-line-strong: var(--ui-dashed); --u-text: var(--ui-ink); --u-muted: var(--ui-muted); --u-faint: var(--ui-muted); --u-accent: var(--ui-accent); --u-accent-soft: var(--ui-accent-soft); --u-ok: var(--ui-ok-ink); --u-ok-soft: color-mix(in srgb, var(--ui-ok-ink) 14%, var(--ui-surface)); --u-warn: var(--ui-warn-ink); --u-warn-soft: var(--ui-warn-soft); --u-bad: var(--ui-bad-ink); --u-bad-soft: var(--ui-bad-soft); --edu-ink: #e9eef2; --edu-teal: #4cc9d8; --edu-teal-dark: #4cc9d8; --edu-teal-ink: #86e3ed; --edu-teal-soft: #123a41; --edu-orange: #ffa066; --edu-orange-dark: #ffa066; --edu-orange-soft: #3a2415; --edu-yellow: #f4cd74; --edu-yellow-soft: #382e12; color-scheme: dark; }
html:not([data-theme="light"]) body, html:not([data-theme="light"]) body.accent-graphics, html:not([data-theme="light"]) body.accent-network, html:not([data-theme="light"]) body.accent-advanced {
  --bg: var(--ui-bg); --surface: var(--ui-surface); --surface-2: var(--ui-surface-2); --text: var(--ui-ink); --muted: var(--ui-muted); --line: var(--ui-line);
  --accent: var(--ui-accent); --accent-soft: var(--ui-accent-soft); --ok: var(--ui-ok-ink); --ok-soft: color-mix(in srgb, var(--ui-ok-ink) 14%, var(--ui-surface)); --bad: var(--ui-bad-ink); --bad-soft: var(--ui-bad-soft);
  --u-accent: var(--ui-accent); --u-accent-soft: var(--ui-accent-soft); --t52-accent: var(--ui-accent); --t52-accent-soft: var(--ui-accent-soft); --panel: var(--ui-surface);
}
html:not([data-theme="light"]) body:not(.x) { background: var(--ui-bg) !important; color: var(--ui-ink); }
html:not([data-theme="light"]) .p60-lvl { background: var(--ui-warn-ink); color: var(--ui-on-accent); }
/* hero plochy zůstávají v původním (tmavě tyrkysovém) vzhledu s bílým textem: podstrom dostane zpět původní barvy značky */
html:not([data-theme="light"]) .auth54-hero, html:not([data-theme="light"]) .p60-hero, html:not([data-theme="light"]) .p60-preview { --edu-teal: #00a8b9; --edu-teal-dark: #007a87; --edu-teal-ink: #05616c; --edu-teal-soft: #e2f6f8; --edu-orange: #ec6b10; --edu-orange-dark: #c4550a; --edu-orange-soft: #fdeee3; --edu-yellow: #f3b21f; --edu-yellow-soft: #fef5e0; --edu-ink: #12212b; --ui-ink: #12212b; --ui-muted: #4d5b68; --ui-accent: #007a87; --ui-accent-ink: #05616c; --ui-accent-soft: #e2f6f8; --ink: #fff; --muted: #e2f6f8; --accent: #007a87; --accent-soft: #e2f6f8; }
CSS;

/** Obsah jednoho výstupního souboru: hlavička, overlay pravidla ze zdrojů a ručně psaný závěr. */
function do68_build(string $root, array $sources, string $title, ?string $tail = null): string
{
    $text = '/* EDUCANET v68 · tmavá vrstva ' . $title . ' – GENEROVÁNO tools/v68_dark_overlay.php ze souborů assets/*.css, ručně neupravovat. Načítá se jen při tmavém motivu. */' . "\n";
    foreach ($sources as $name) {
        $src = (string)file_get_contents($root . '/assets/' . $name);
        $text .= '/* ' . $name . ' (sha256 ' . substr(hash('sha256', $src), 0, 12) . ") */\n" . do68_walk($src);
    }
    return $text . ($tail ?? DO68_TAIL) . "\n";
}

function do68_main(array $argv): int
{
    $root = str_replace(chr(92), '/', dirname(__DIR__));
    $check = in_array('--check', $argv, true);
    $targets = [DO68_COCKPIT_OUT => do68_build($root, DO68_COCKPIT_SOURCES, 'cockpitu (soubory Labu)', '')];
    $stale = [];
    foreach ($targets as $rel => $text) {
        $target = $root . '/' . $rel;
        if (!is_file($target) || !hash_equals(hash('sha256', $text), (string)hash_file('sha256', $target))) $stale[] = $rel;
        if (!$check) file_put_contents($target, $text);
    }
    if ($check) {
        echo $stale === [] ? "V68_OVERLAY_CURRENT\n" : 'V68_OVERLAY_STALE ' . implode(',', $stale) . "\n";
        return $stale === [] ? 0 : 1;
    }
    echo 'V68_OVERLAY_OK bytes=' . strlen($targets[DO68_COCKPIT_OUT]) . ' sources=' . count(DO68_COCKPIT_SOURCES) . "\n";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(do68_main($argv));   // v69: soubor lze načíst jako knihovnu (tools/v69_dark_overlay.php)
