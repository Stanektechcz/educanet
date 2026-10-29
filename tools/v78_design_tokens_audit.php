<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v78 · audit design systému (fáze A):
 *   1) tokeny: povinné sémantické barvy, mezery po 4 px, radius, stíny, typografie, breakpointy xs–xxl, cíl dotyku 44 px;
 *      světlá sada a dvě shodné tmavé sady (volba „tmavý“ a „podle systému“) se stejnými barevnými klíči,
 *   2) kontrast všech dvojic tokenů v obou režimech (text ≥ 4,5 : 1, prvky UI ≥ 3 : 1) – dvojice z design_v78.php,
 *   3) nové soubory v78 bez barev mimo tokeny (hex, rgb/hsl/…, pojmenované barvy, inline styl jen s var()),
 *   4) písmo nikdy pod 14 px, interaktivní komponenty ≥ 44 px, @media/@container jen na šířkách tokenů, container queries u karty a tabulky,
 *      fokus, reduced-motion, rozpočty (CSS ≤ 15 kB gzip, JS ≤ 5 kB gzip), JS bez innerHTML/eval/sítě,
 *   5) registrace záložky design78 (jen admin, bez POST a GET parametru, tmavý režim, menu Správa),
 *   6) HTTP (dočasné úložiště): admin 200 bez zápisu do úložiště, učitel a asistent 403, menu jen adminovi, motiv tmavý.
 * Konec: V78_DESIGN_TOKENS_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v78-design')), '/');
const V78D_KEY = 'v78d-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V78D_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/ui/design_v78.php'] as $lib) require_once $ROOT . '/' . $lib;

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-'));
$read = static fn(string $f): string => is_file($ROOT . '/' . $f) ? (string)file_get_contents($ROOT . '/' . $f) : '';
$stripCss = static fn(string $css): string => (string)preg_replace('~/\*.*?\*/~s', '', $css);

// ---------------------------------------------------------------- 0) soubory
$newCss = [DS78_COMPONENTS_CSS, DS78_DEMO_CSS];
$files = [DS78_TOKENS_CSS, DS78_COMPONENTS_CSS, DS78_DEMO_CSS, DS78_JS, 'src/ui/design_v78.php', 'src/ui/design_v78_views.php', 'tools/v78_design_tokens_audit.php', 'tools/lib/v78_design_http.php', 'tools/v78_css_inventory.php'];
$missing = array_values(array_filter($files, static fn(string $f): bool => !is_file($ROOT . '/' . $f)));
$check('soubory v78 existují (' . count($files) . ')' . ($missing ? ': chybí ' . implode(', ', $missing) : ''), $missing === []);
$long = [];
foreach ($files as $f) if (is_file($ROOT . '/' . $f) && count(file($ROOT . '/' . $f)) > 800) $long[] = $f;
$check('soubory v78 do 800 řádků' . ($long ? ': ' . implode(', ', $long) : ''), $long === []);
$guards = true;
foreach (['src/ui/design_v78.php', 'src/ui/design_v78_views.php', 'tools/lib/v78_design_http.php'] as $f) {
    $src = $read($f);
    $guards = $guards && str_contains($src, 'declare(strict_types=1);') && str_contains($src, "basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)");
}
$guards = $guards && str_contains($read('tools/v78_css_inventory.php'), "if (PHP_SAPI !== 'cli')");
$check('PHP v78: strict_types, guard přímého přístupu u knihoven, CLI guard u nástroje', $guards);

// ---------------------------------------------------------------- 1) tokeny
$sets = ds78_token_sets();
$light = $sets['light'];
$colorKeys = array_keys(array_filter($light, static fn(string $v): bool => preg_match('/^#[0-9a-f]{6}$/i', $v) === 1));
$check('tmavé sady „tmavý“ a „podle systému“ jsou shodné a neprázdné', $sets['dark'] !== [] && $sets['dark'] === $sets['dark_system']);
$noDark = array_values(array_filter($colorKeys, static fn(string $k): bool => !isset($sets['dark'][$k])));
$check('každá barva světlého režimu má tmavou variantu (' . count($colorKeys) . ' barev)' . ($noDark ? ': chybí ' . implode(', ', $noDark) : ''), $noDark === [] && count($colorKeys) >= 20);
$required = ['bg', 'surface', 'text', 'muted', 'accent', 'ok', 'warn', 'danger', 'info'];
$check('povinné sémantické barvy: ' . implode(', ', $required), array_diff($required, $colorKeys) === []);
$spaces = [];
for ($i = 1; $i <= 7; $i++) $spaces[] = (string)($light['space-' . $i] ?? '');
$check('mezery po 4 px: 4/8/12/16/24/32/48', $spaces === ['4px', '8px', '12px', '16px', '24px', '32px', '48px']);
$check('radius, stíny, řádkování 1,5 a systémová písma (žádný webfont)', isset($light['radius-sm'], $light['radius'], $light['radius-lg'], $light['shadow-1'], $light['shadow-2'])
    && ($light['lh'] ?? '') === '1.5' && str_contains((string)($light['font-sans'] ?? ''), 'system-ui') && !preg_match('~@import|@font-face|url\(~i', $stripCss($read(DS78_TOKENS_CSS))));
$bpOk = true;
foreach (ds78_breakpoints() as $t => $px) $bpOk = $bpOk && ($light['bp-' . $t] ?? '') === $px . 'px';
$check('breakpointy xs 320 · sm 480 · md 768 · lg 1024 · xl 1366 · xxl 1920 a obsah max. 1600 px', $bpOk && ($light['content-max'] ?? '') === '1600px');
$check('cíl dotyku --target-min = 44 px, spodní lišta ≥ 44 px', ($light['target-min'] ?? '') === DS78_TARGET_MIN_PX . 'px' && (int)($light['bottomnav-h'] ?? 0) >= DS78_TARGET_MIN_PX);
$check('jediný akcent ke změně (--accent, --accent-hover, --on-accent, --accent-soft)', isset($light['accent'], $light['accent-hover'], $light['on-accent'], $light['accent-soft']) && isset($sets['dark']['accent-soft']));

// ---------------------------------------------------------------- 2) kontrast
$rows = ds78_contrast_rows($sets);
$bad = array_values(array_filter($rows, static fn(array $r): bool => !$r['ok']));
$minText = min(array_map(static fn(array $r): float => min((float)$r['light'], (float)$r['dark']), array_filter($rows, static fn(array $r): bool => $r['kind'] === 'text')));
$minUi = min(array_map(static fn(array $r): float => min((float)$r['light'], (float)$r['dark']), array_filter($rows, static fn(array $r): bool => $r['kind'] === 'ui')));
$check(sprintf('kontrast %d dvojic v obou režimech: text min %.2f : 1 (≥ 4,5), UI min %.2f : 1 (≥ 3)', count($rows), $minText, $minUi)
    . ($bad ? ' – nesplňuje: ' . implode(', ', array_map(static fn(array $r): string => $r['fg'] . '/' . $r['bg'], $bad)) : ''), $bad === [] && count($rows) >= 50);
$used = [];
foreach ([DS78_COMPONENTS_CSS, DS78_DEMO_CSS] as $f) {
    preg_match_all('/var\(--([a-z0-9-]+)\)/', $read($f), $m);
    $used = array_merge($used, $m[1]);
}
$undefined = array_values(array_unique(array_filter($used, static fn(string $v): bool => !isset($light[$v]) && !in_array($v, ['sw', 'tone', 'tone-soft'], true))));
$check('komponenty používají jen definované tokeny' . ($undefined ? ': neznámé ' . implode(', ', $undefined) : ''), $undefined === []);

// ---------------------------------------------------------------- 3) žádné barvy mimo tokeny
$named = 'white|black|red|green|blue|yellow|orange|purple|gray|grey|silver|navy|teal|maroon|olive|lime|aqua|fuchsia|pink|brown|gold|cyan|magenta|beige|ivory|coral|salmon|crimson|indigo|violet|turquoise|tomato';
$colorRe = '~#(?:[0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})\b|\b(?:rgba?|hsla?|hwb|lab|lch|oklab|oklch|color)\(~i';
$raw = [];
foreach ($newCss as $f) {
    $css = $stripCss($read($f));
    if (preg_match_all($colorRe, $css, $m)) $raw[] = $f . ': ' . implode(' ', array_unique($m[0]));
    if (preg_match_all('~:\s*[^;{}]*(?<![\w-])(' . $named . ')(?![\w-])[^;{}]*;~i', $css, $m)) $raw[] = $f . ': ' . implode(' ', array_unique($m[1]));
}
$js = $read(DS78_JS);
$jsCode = (string)preg_replace(['~/\*.*?\*/~s', '~^\s*//.*$~m'], '', $js);
if (preg_match_all($colorRe, $js, $m)) $raw[] = DS78_JS . ': ' . implode(' ', $m[0]);
foreach (['src/ui/design_v78.php', 'src/ui/design_v78_views.php'] as $f) {
    $src = $read($f);
    if (preg_match_all('~#[0-9a-f]{6}\b|\b(?:rgba?|hsla?)\(~i', $src, $m)) $raw[] = $f . ': ' . implode(' ', array_unique($m[0]));
    preg_match_all('~style="([^"]*)"~', $src, $m);
    foreach ($m[1] as $style) {
        $plain = (string)preg_replace("~'\\s*\\.\\s*[^.]+?\\s*\\.\\s*'~", 'x', $style);   // ' . e($name) . ' → x
        if (preg_replace('~(?:--[a-z0-9-]+|font-size|width|border-radius|box-shadow)\s*:\s*var\(--[a-z0-9-]+\)\s*;?\s*~i', '', $plain) !== '') $raw[] = $f . ': inline styl „' . $style . '“';
    }
}
$check('nové soubory v78 (komponenty, ukázka, JS, PHP) bez barev mimo tokeny' . ($raw ? ': ' . implode(' | ', $raw) : ''), $raw === []);

// ---------------------------------------------------------------- 4) písmo, cíle dotyku, breakpointy, rozpočty
$fontBad = [];
foreach (['fs-sm', 'fs-base', 'fs-lg', 'fs-xl', 'fs-2xl'] as $t) {
    $v = (string)($light[$t] ?? '');
    $min = preg_match('/^clamp\(\s*([\d.]+)rem/', $v, $mm) === 1 ? (float)$mm[1] * 16 : 0.0;
    if ($min < DS78_MIN_FONT_PX) $fontBad[] = $t . '=' . $v;
}
preg_match_all('/--fs-[a-z0-9]+\s*:\s*([\d.]+)rem/', $stripCss($read(DS78_TOKENS_CSS)), $mm);
foreach ($mm[1] as $rem) if ((float)$rem * 16 < DS78_MIN_FONT_PX) $fontBad[] = 'xxl ' . $rem . 'rem';
foreach ($newCss as $f) {
    preg_match_all('/font-size\s*:\s*([^;}]+)/', $stripCss($read($f)), $m);
    foreach ($m[1] as $v) if (preg_match('/^var\(--fs-(sm|base|lg|xl|2xl)\)$/', trim($v)) !== 1) $fontBad[] = $f . ': ' . trim($v);
}
$check('písmo nikdy pod 14 px: tokeny clamp() od 0,875 rem, komponenty jen var(--fs-*)' . ($fontBad ? ': ' . implode(', ', $fontBad) : ''), $fontBad === []);
$comp = $stripCss($read(DS78_COMPONENTS_CSS));
$targets = ['.ds78 .ds-btn' => 'min-height', '.ds78 .ds-btn--icon' => 'width', '.ds78 .ds-tab' => 'min-height', '.ds78 .ds-acc > summary' => 'min-height', '.ds78 .ds-input' => 'min-height',
    '.ds78 .ds-check' => 'min-height', '.ds78 .ds-breadcrumb a' => 'min-height', '.ds78 .ds-navlink' => 'min-height', '.ds78 .ds-bottomnav a' => 'min-height'];
$tBad = [];
foreach ($targets as $sel => $prop) {
    $ok = preg_match('~(?:^|\})\s*' . preg_quote($sel, '~') . '\s*\{([^}]*)\}~', $comp, $m) === 1
        && preg_match('~(?:^|;)\s*' . $prop . '\s*:\s*var\(--(target-min|bottomnav-h)\)~', $m[1], $v) === 1 && (int)($light[$v[1]] ?? 0) >= DS78_TARGET_MIN_PX;
    if (!$ok) $tBad[] = $sel;
}
$check('interaktivní komponenty ≥ 44 px (' . count($targets) . ' pravidel: tlačítka, ikonové tlačítko, záložka, akordeon, pole, zaškrtávátko, drobečky, menu, spodní lišta)' . ($tBad ? ': ' . implode(', ', $tBad) : ''), $tBad === []);
$bpBad = [];
foreach ([DS78_TOKENS_CSS, DS78_COMPONENTS_CSS, DS78_DEMO_CSS] as $f) {
    preg_match_all('~@(?:media|container)[^{]*~', $stripCss($read($f)), $m);
    foreach ($m[0] as $q) {
        preg_match_all('/(\d+(?:\.\d+)?)px/', $q, $px);
        foreach ($px[1] as $w) if (!in_array((int)$w, ds78_breakpoints(), true)) $bpBad[] = $f . ': ' . trim($q);
    }
}
$check('@media a @container jen na šířkách tokenů (320/480/768/1024/1366/1920)' . ($bpBad ? ': ' . implode(' | ', array_unique($bpBad)) : ''), $bpBad === []);
$check('container queries: karta a tabulka mají container-type, @container pro kartu (480), tabulku → karty (768) a stepper',
    preg_match('~\.ds78 \.ds-card \{[^}]*container-type:\s*inline-size~', $comp) === 1 && preg_match('~\.ds78 \.ds-table-wrap \{[^}]*container-type:\s*inline-size~', $comp) === 1
    && str_contains($comp, '@container (min-width: 480px)') && str_contains($comp, '@container (width < 768px)') && str_contains($comp, '@container (width < 480px)'));
$check('viditelný fokus (--focus-width) a omezený pohyb (tokeny i komponenty)', str_contains($comp, ':focus-visible { outline: var(--focus-width) solid var(--focus)')
    && str_contains($stripCss($read(DS78_TOKENS_CSS)), 'prefers-reduced-motion: reduce') && str_contains($comp, 'prefers-reduced-motion: reduce'));
$cssGz = strlen((string)gzencode($read(DS78_TOKENS_CSS) . $read(DS78_COMPONENTS_CSS) . $read(DS78_DEMO_CSS), 9));
$jsGz = strlen((string)gzencode($js, 9));
$check('rozpočet fáze: CSS ' . $cssGz . ' B gzip (≤ 15 kB), JS ' . $jsGz . ' B gzip (≤ 5 kB)', $cssGz <= 15360 && $jsGz <= 5120);
$check('JS bez innerHTML/outerHTML/insertAdjacentHTML/eval/document.write a bez síťových volání', preg_match('~innerHTML|outerHTML|insertAdjacentHTML|\beval\(|document\.write|fetch\(|XMLHttpRequest|https?://~', $jsCode) !== 1);
$check('žádné externí zdroje v CSS v78 (@import, url(), webfont)', preg_match('~@import|url\(|@font-face~i', $stripCss($read(DS78_COMPONENTS_CSS) . $read(DS78_DEMO_CSS))) !== 1);

// ---------------------------------------------------------------- 5) registrace záložky
$mods = teacher58_modules();
$m = $mods['design78'] ?? [];
$check('modul design78: jen admin, bez POST a GET parametru, soubory a CSS/JS v78', !empty($m['admin']) && !isset($m['post']) && !isset($m['get'])
    && ($m['files'] ?? []) === ['src/ui/design_v78.php', 'src/ui/design_v78_views.php'] && ($m['css'] ?? []) === [DS78_TOKENS_CSS, DS78_COMPONENTS_CSS, DS78_DEMO_CSS] && ($m['js'] ?? []) === [DS78_JS]);
$map = teacher68_tab_map();
$check('menu: Správa › Design systém (admin, bez ?class=), tmavý režim, TEACHER59_ADMIN_TABS', ($map['design78']['section'] ?? '') === 'sprava' && ($map['design78']['admin'] ?? false) === true
    && ($map['design78']['class'] ?? true) === false && in_array('design78', UI68_TEACHER_DARK_TABS, true) && in_array('design78', TEACHER59_ADMIN_TABS, true));
$check('viditelnost: admin záložku vidí, učitel a asistent ne', in_array('design78', teacher68_visible_tabs('admin', true, teacher58_tabs()), true)
    && !in_array('design78', teacher68_visible_tabs('teacher', false, teacher58_tabs()), true) && !in_array('design78', teacher68_visible_tabs('assistant', false, teacher58_tabs()), true));
$check('žádná nová POST akce ani GET politika v78 (deny-by-default: ds78_* zakázané)', teacher59_action_policy('ds78_x')['deny'] === true
    && array_filter(array_keys(teacher59_get_policies()), static fn(string $k): bool => str_starts_with($k, 'design78|')) === []);
$views = $read('src/ui/design_v78_views.php');
$check('ukázka nic neodesílá: bez <form>, všechna tlačítka type="button", fiktivní jména', !str_contains($views, '<form') && substr_count($views, '<button') === substr_count($views, '<button type="button"')
    && str_contains($views, 'Adam Ukázkový'));

// ---------------------------------------------------------------- 6) HTTP
require __DIR__ . '/lib/v78_design_http.php';

exit(audit_summary($state, 'V78_DESIGN_TOKENS'));
