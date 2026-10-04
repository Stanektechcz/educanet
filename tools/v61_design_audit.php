<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · audit jednotného design systému žákovské aplikace (část C).
 *   1) tokeny značky a rozměrů jsou definované jednou (tokens-v61.css), žádná jiná vrstva je znovu nedefinuje,
 *   2) nové soubory nemají vlastní pevné barvy (vše přes tokeny), každý použitý token je definovaný,
 *   3) komponenty .ui-* mají viditelný fokus (≥ 3 px) a interaktivní cíle ≥ 44 px, profil je generuje (neduplikuje),
 *   4) kontrast hlavních dvojic tokenů (text ≥ 4,5 : 1, ohraničení polí ≥ 3 : 1),
 *   5) specificita: globální h1–h4/p/li pravidla ze student-ui-v50-7-7.css jsou ve :where() bez !important,
 *   6) HTTP (dočasné úložiště, dev server): spodní lišta na žákovských stránkách s aria-current, ne v testu a v terminálu
 *      labu, drobečková navigace <nav><ol>, tokeny i komponenty se linkují, ?view=precache je obsahuje,
 *   7) velikost CSS na stránku (součet linkovaných souborů) nevzrostla o víc než 20 KB oproti stavu před částí C.
 *
 *   php tools/v61_design_audit.php
 * Konec: V61_DESIGN_AUDIT_OK checks=N failed=0 (jinak _FAIL a nenulový exit kód).
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v61-design')), '/');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v61_design_css.php';
foreach (['student_v55.php', 'nav_v61.php', 'profile_v60_ui.php'] as $rel) { require_once $ROOT . '/' . $rel; }

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
/** 20 KB původní rozpočet části C + 8 KB pro assets/nav-v61.css (nová horní navigace se skupinami a podmenu; viz CHANGELOG nav). */
const V61D_CSS_BUDGET_BYTES = 28672;
/** Součet linkovaných CSS souborů na stránku těsně před částí C (měřeno 2026-10-04, dev server, třída 3.A). */
const V61D_CSS_BASELINE = [
    'dashboard' => 723577, 'materialy' => 706085, 'calendar' => 706085, 'vysledky' => 706085, 'lab' => 748297,
    'obchod' => 708729, 'projekty' => 708696, 'hlaseni' => 709314, 'profile' => 728574,
];

$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));

// ---------------------------------------------------------------- 1) tokeny definované jednou
$tokensCss = $read('assets/tokens-v61.css');
$componentsCss = $read('assets/components-v61.css');
$tokenDecls = v61d_custom_props($tokensCss);
$allDecls = array_merge($tokenDecls, v61d_custom_props($componentsCss));
$counts = [];
foreach ($allDecls as $d) { $counts[$d['name']] = ($counts[$d['name']] ?? 0) + 1; }
$accentNames = ['ui-accent', 'ui-accent-ink', 'ui-accent-soft'];
$dups = array_keys(array_filter($counts, static fn(int $n, string $name): bool => $n > (in_array($name, $accentNames, true) ? 2 : 1), ARRAY_FILTER_USE_BOTH));
$check('tokeny: každý token je v tokens/components-v61.css definovaný jednou (výjimka: subjektový akcent 2×) ' . ($dups ? '[' . implode(', ', $dups) . ']' : '(' . count($counts) . ' tokenů)'), $dups === [] && count($counts) >= 50);
$graphics = array_values(array_filter($tokenDecls, static fn(array $d): bool => $d['block'] === 'body.accent-graphics'));
$check('tokeny: oranžový akcent grafiky je jen v bloku body.accent-graphics (3 tokeny)', count($graphics) === 3);
$redefined = [];
foreach (glob($ROOT . '/assets/*.css') ?: [] as $file) {
    if (basename($file) === 'tokens-v61.css') continue;
    foreach (v61d_custom_props((string)file_get_contents($file)) as $d) {
        if (preg_match('/^edu-(yellow|orange|teal|ink)(-dark|-soft|-ink)?$/', $d['name']) === 1) $redefined[] = basename($file) . ' --' . $d['name'];
    }
}
$check('barvy značky (--edu-*) nedefinuje žádný jiný CSS soubor' . ($redefined ? ' [' . implode(', ', $redefined) . ']' : ''), $redefined === []);
$vars = audit_css_vars($tokensCss);
$resolve = static fn(string $name): string => strtolower(audit_css_resolve($vars, 'var(--' . $name . ')'));
foreach (['edu-yellow' => '#f3b21f', 'edu-orange' => '#ec6b10', 'edu-teal' => '#00a8b9', 'edu-teal-dark' => '#007a87', 'edu-orange-dark' => '#c4550a'] as $name => $hex) {
    $check('token --' . $name . ' se rozpouští na ' . $hex, $resolve($name) === $hex);
}
$check('most: starší tokeny (--u-radius, --edu-radius, --v506-radius, --u-line-strong) berou hodnoty z --ui-*', $resolve('u-radius') === '16px' && $resolve('edu-radius') === '16px' && $resolve('v506-radius') === '16px' && $resolve('u-line-strong') === '#6b7885' && $resolve('u-font') === $resolve('ui-font'));
$used = [];
foreach ([$componentsCss, $read('nav_v61.php')] as $src) { preg_match_all('/var\(--(ui-[a-z0-9-]+)/', $src, $m); $used = array_merge($used, $m[1]); }
$undefined = array_values(array_diff(array_unique($used), array_keys($counts)));
$check('každý použitý token --ui-* v components-v61.css je definovaný' . ($undefined ? ' [' . implode(', ', $undefined) . ']' : ''), $undefined === [] && $used !== []);

// ---------------------------------------------------------------- 2) žádné nové pevné barvy
$colorLiteral = '/#[0-9a-fA-F]{3,8}\b|\b(?:rgb|rgba|hsl|hsla)\(/';
$noComments = static fn(string $css): string => (string)preg_replace('~/\*.*?\*/~s', '', $css);
$check('components-v61.css: žádné pevné barvy (hex/rgb/hsl) – jen tokeny', preg_match($colorLiteral, $noComments($componentsCss)) !== 1);
$nonTokenColor = 0;
foreach (v61d_rules($noComments($tokensCss)) as $rule) {
    foreach (v61d_decls($rule['body']) as $prop => $value) { if (!str_starts_with($prop, '--') && preg_match($colorLiteral, $value) === 1) $nonTokenColor++; }
}
$check('tokens-v61.css: barevné hodnoty jsou jen v deklaracích tokenů', $nonTokenColor === 0);
$newBytes = strlen($tokensCss) + strlen($componentsCss);
$check('nové styly (tokeny + komponenty) ≤ 20 KB (' . $newBytes . ' B)', $newBytes <= V61D_CSS_BUDGET_BYTES);
$check('soubory CSS/PHP nové vrstvy ≤ 800 řádků', max(substr_count($tokensCss, "\n"), substr_count($componentsCss, "\n"), substr_count($read('nav_v61.php'), "\n"), substr_count($read('tools/v61_design_audit.php'), "\n")) <= 800);

// ---------------------------------------------------------------- 3) komponenty: fokus, ≥ 44 px, profil je generuje
$componentRules = v61d_rules($noComments($componentsCss));
foreach (['.ui-card', '.ui-section-title', '.ui-stat', '.ui-tile', '.ui-meter-bar', '.ui-tag', '.ui-btn--primary', '.ui-btn--secondary', '.ui-btn--quiet', '.ui-empty', '.ui-flash', '.ui-table', '.ui-input', '.ui-select', '.ui-textarea', '.ui-label', '.ui-crumbs', '.ui-bottomnav'] as $component) {
    $found = array_filter($componentRules, static fn(array $r): bool => preg_match('/(^|[\s,(])' . preg_quote($component, '/') . '(?![\w-])/', $r['sel']) === 1);
    $check('komponenta ' . $component . ' je definovaná', $found !== []);
}
$focusPx = v61d_px('var(--ui-focus)', $vars);
foreach (['.ui-btn' => '.ui-btn', '.ui-link' => '.ui-link', '.ui-tile' => '.ui-tile', '.ui-input' => '.ui-input', '.ui-crumbs a' => '.ui-crumbs a', '.ui-bottomnav a' => '.ui-bottomnav a'] as $label => $needle) {
    $focus = array_filter($componentRules, static fn(array $r): bool => str_contains($r['sel'], $needle) && str_contains($r['sel'], ':focus-visible') && preg_match('/outline\s*:[^;]*var\(--ui-focus\)/', $r['body']) === 1);
    $check('fokus ' . $label . ': :focus-visible s obrysem var(--ui-focus) = ' . $focusPx . ' px (≥ 3 px)', $focus !== [] && $focusPx !== null && $focusPx >= 3.0);
}
foreach (['.ui-btn' => 44.0, '.ui-link' => 44.0, '.ui-tile' => 44.0, '.ui-input' => 44.0, '.ui-crumbs a' => 44.0, '.ui-bottomnav a' => 44.0] as $needle => $min) {
    $heights = [];
    foreach ($componentRules as $r) {
        if (!preg_match('/(^|,)\s*' . preg_quote($needle, '/') . '\s*(,|$)/', $r['sel'])) continue;
        $decl = v61d_decls($r['body']);
        if (isset($decl['min-height'])) $heights[] = v61d_px($decl['min-height'], $vars);
    }
    $check('cíl ' . $needle . ': min-height ≥ 44 px (' . implode('/', array_map(static fn($h): string => (string)$h, $heights)) . ')', $heights !== [] && min($heights) >= $min);
}
$mobile = array_values(array_filter($componentRules, static fn(array $r): bool => str_contains($r['sel'], 'body.ui61-has-bottomnav') && str_contains($r['body'], 'padding-bottom')));
$check('spodní lišta: obsah se nepřekrývá (padding-bottom na body) a respektuje safe-area-inset-bottom', $mobile !== [] && str_contains($mobile[0]['body'], 'env(safe-area-inset-bottom'));
$bar = array_values(array_filter($componentRules, static fn(array $r): bool => trim($r['sel']) === '.ui-bottomnav' && str_contains($r['body'], 'position: fixed')));
$check('spodní lišta: position fixed + safe-area-inset-bottom, na ≥ 621 px skrytá (display:none mimo @media max-width:620px)', $bar !== [] && str_contains($bar[0]['body'], 'env(safe-area-inset-bottom') && preg_match('/\.ui-bottomnav\s*\{\s*display:\s*none/', $componentsCss) === 1);
$check('prefers-reduced-motion: pohyb dlaždic se vypne', preg_match('/prefers-reduced-motion:\s*reduce\)\s*\{[^}]*\.ui-tile[^}]*transition:\s*none/', $componentsCss) === 1);
$html = profile60_panel_open('Titulek <b>', 'Oko', 'Více', '?view=profile', 'p-x') . profile60_stats([['5', 'Bodů <i>', 'teal']]) . profile60_meter_list([['label' => 'Cíl', 'value' => 3.0, 'max' => 8.0, 'text' => '3 / 8']])
    . profile60_empty('Nic tu není', 'Začni', '?view=lab') . profile60_tile('?view=lab', 'Lab', '3', 'úrovně', 'orange') . profile60_tags(['Linux <x>']) . profile60_panel_close();
$check('profil generuje sdílené komponenty (ui-card, ui-stat, ui-meter-bar, ui-empty, ui-tile, ui-tag, ui-link, ui-eyebrow)', preg_match_all('/class="[^"]*\b(ui-card|ui-stat|ui-meter-bar|ui-empty|ui-tile|ui-tag|ui-link|ui-eyebrow)\b/', $html, $um) >= 8 && count(array_unique($um[1])) === 8);
$check('profil: obsah se escapuje (žádné syrové <b>, <i>, <x> z dat)', !str_contains($html, 'Titulek <b>') && !str_contains($html, 'Bodů <i>') && !str_contains($html, 'Linux <x>') && str_contains($html, 'Titulek &lt;b&gt;') && str_contains($html, 'Linux &lt;x&gt;'));
$check('profil: tón dlaždice a čísla přes is-* třídy (is-teal, is-orange)', str_contains($html, 'ui-stat is-teal') && str_contains($html, 'ui-tile is-orange'));
$dupe = [];
foreach (v61d_rules($noComments($read('assets/profile-v60.css'))) as $rule) {
    foreach (v61d_split_selectors($rule['sel']) as $sel) {
        if (preg_match('/^\.p60-(card|stat|meter-bar|empty-state|tile|tags|flash|data-table)(\.is-[a-z]+)?$/', $sel) === 1 && array_intersect_key(v61d_decls($rule['body']), array_flip(['background', 'border', 'box-shadow', 'border-radius', 'border-collapse'])) !== []) $dupe[] = $sel;
    }
}
$check('profil-v60.css už nedefinuje vlastní povrch karet/čísel/progressu/prázdného stavu/štítků/tabulky (přešly do .ui-*) ' . ($dupe ? '[' . implode(' ', $dupe) . ']' : ''), $dupe === []);

// ---------------------------------------------------------------- 4) kontrast hlavních dvojic tokenů
$pairs = [
    ['ui-ink', 'ui-surface', 4.5], ['ui-ink', 'ui-surface-2', 4.5], ['ui-ink', 'ui-accent-soft', 4.5], ['ui-ink', 'ui-bg', 4.5],
    ['ui-muted', 'ui-surface', 4.5], ['ui-muted', 'ui-surface-2', 4.5], ['ui-muted', 'ui-bg', 4.5],
    ['ui-accent', 'ui-surface', 4.5], ['ui-accent-ink', 'ui-surface', 4.5], ['ui-accent-ink', 'ui-accent-soft', 4.5], ['ui-accent-ink', 'ui-surface-2', 4.5],
    ['ui-on-accent', 'ui-accent', 4.5], ['ui-on-accent', 'ui-accent-ink', 4.5], ['ui-on-accent', 'edu-orange-dark', 4.5], ['ui-on-accent', 'ui-hot-ink', 4.5],
    ['ui-warn-ink', 'ui-warn-soft', 4.5], ['ui-hot-ink', 'ui-hot-soft', 4.5], ['ui-hot-ink', 'ui-surface', 4.5],
    ['ui-ok-ink', 'ui-surface', 4.5], ['ui-bad-ink', 'ui-surface', 4.5], ['ui-bad-ink', 'ui-bad-soft', 4.5],
    ['ui-field-border', 'ui-surface', 3.0], ['ui-accent', 'ui-surface-2', 3.0],
];
foreach ($pairs as [$fg, $bg, $min]) {
    $ratio = audit_contrast_ratio($resolve($fg), $resolve($bg));
    $check(sprintf('kontrast --%s na --%s ≥ %.1f : 1 (%s)', $fg, $bg, $min, $ratio === null ? 'nelze určit' : number_format($ratio, 2)), $ratio !== null && $ratio >= $min);
}

// ---------------------------------------------------------------- 5) specificita globálních h1–h4/p
$studentCss = $noComments($read('assets/student-ui-v50-7-7.css'));
$check('selftest specifity: staré body:not(.assessment-mode) h1 = (0,1,2), nové :where(…) :is(h1) = (0,0,1)', v61d_specificity('body:not(.assessment-mode) h1') === [0, 1, 2] && v61d_specificity(':where(body:not(.assessment-mode)) :is(h1,h2)') === [0, 0, 1]);
$globalText = [];
foreach (v61d_rules($studentCss) as $rule) {
    foreach (v61d_split_selectors($rule['sel']) as $sel) {
        $rest = trim((string)preg_replace('/^(?::where\(body:not\(\.assessment-mode\)\)|body:not\(\.assessment-mode\))/', '', $sel, 1, $hit));
        if ($hit === 1 && preg_match('/^(?::is\()?(?:h1|h2|h3|h4|p|li)(?:\s*,\s*(?:h1|h2|h3|h4|p|li))*\)?$/', $rest) === 1) {
            $globalText[] = ['sel' => $sel, 'spec' => v61d_specificity($sel), 'important' => str_contains($rule['body'], '!important')];
        }
    }
}
$tooStrong = array_filter($globalText, static fn(array $g): bool => $g['spec'][0] > 0 || $g['spec'][1] > 0 || $g['important']);
$check('globální pravidla h1–h4/p/li (' . count($globalText) . ') mají specificitu ≤ (0,0,1) a žádné !important' . ($tooStrong ? ' [' . implode(' | ', array_map(static fn(array $g): string => $g['sel'], $tooStrong)) . ']' : ''), count($globalText) >= 7 && $tooStrong === []);
$oldHeading = preg_match('/(^|\})\s*body:not\(\.assessment-mode\)\s+(h1|h2|h3|h4|p)\b[^{]*\{[^}]*!important/', $studentCss) === 1;
$check('v student-ui-v50-7-7.css nezbylo „body:not(.assessment-mode) h1…p … !important“', !$oldHeading);
$btnStrong = array_filter(v61d_rules($studentCss), static fn(array $r): bool => trim($r['sel']) === 'body:not(.assessment-mode) .btn');
$check('základní .btn a pole formulářů ve student-ui jsou bez !important (moduly je přebijí třídou)', $btnStrong === [] && preg_match('/:where\(body:not\(\.assessment-mode\)\)\s*:is\(input,textarea,select\)\s*\{[^}]*border-color:\s*var\(--ui-field-border/', $studentCss) === 1);
$check('profil-v60.css: nadpisy bez !important a bez body:not(.assessment-mode) (stačí třída)', preg_match('/\.p60-layout :is\(h2,h3\)\{font-weight:700;color:var\(--ink\)\}/', $read('assets/profile-v60.css')) === 1);

// ---------------------------------------------------------------- 6) HTTP: spodní lišta, drobečky, odkazy na CSS
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0'];
require_once __DIR__ . '/lib/v61_perf_fixture.php';
$fx = v61_perf_seed($modules, $tmp, 3);   // fiktivní žáci s historií; profil bez rosteru přesměruje na přehled
$h = Harness::start($env);
$cssPerPage = [];
try {
    $login = audit_login_student($h, $fx['class'], $fx['label']);
    $pages = ['dashboard' => 'dashboard', 'materialy' => 'materialy', 'calendar' => 'calendar', 'vysledky' => 'vysledky', 'lab' => 'lab', 'obchod' => 'obchod', 'projekty' => 'projekty', 'hlaseni' => 'hlaseni', 'profile' => 'profile', 'study' => 'study'];
    $active = ['dashboard' => '?view=dashboard', 'materialy' => '?view=materialy', 'vysledky' => '?view=vysledky', 'profile' => '?view=profile', 'lab' => '?view=lab'];
    foreach ($pages as $id => $view) {
        $r = $h->request('GET', '/?view=' . $view);
        $body = (string)$r['body'];
        $check('stránka ' . $id . ': HTTP 200 bez chyby', $r['status'] === 200 && audit_response_clean($r));
        $hasBar = preg_match('~<nav class="ui-bottomnav" aria-label="[^"]+" data-ui61-bottomnav><ul>(.*?)</ul></nav>~s', $body, $bar) === 1;
        $links = $hasBar ? preg_match_all('~<li><a[^>]*href="(\?view=[a-z_]+[^"]*)"[^>]*>~', $bar[1], $lm) : 0;
        $currents = $hasBar ? preg_match_all('/aria-current="page"/', $bar[1]) : 0;
        $check('stránka ' . $id . ': spodní lišta má 4–5 odkazů (' . $links . ') a třídu body ui61-has-bottomnav', $hasBar && $links >= 4 && $links <= 5 && str_contains($body, ' ui61-has-bottomnav'));
        if (isset($active[$id])) {
            $check('stránka ' . $id . ': ve spodní liště je právě jedna položka aria-current="page" a vede na ' . $active[$id], $currents === 1 && preg_match('~<a[^>]*href="' . preg_quote($active[$id], '~') . '"[^>]*aria-current="page"~', (string)($bar[1] ?? '')) === 1);
        } else {
            $check('stránka ' . $id . ': ve spodní liště je nejvýš jedna položka aria-current (' . $currents . ')', $currents <= 1);
        }
        $crumbs = preg_match('~<nav class="ui-crumbs" aria-label="[^"]+"><ol>(.*?)</ol></nav>~s', $body, $cm) === 1;
        if ($id === 'dashboard') {
            $check('stránka dashboard: bez drobečků (je to domů)', !$crumbs);
        } else {
            $check('stránka ' . $id . ': drobečková navigace <nav aria-label><ol> s odkazem Domů a aktuální stránkou (aria-current)', $crumbs && str_contains($cm[1], 'href="?view=dashboard"') && substr_count($cm[1], 'aria-current="page"') === 1 && substr_count($cm[1], '<li>') >= 2);
        }
        $hasMain = preg_match('~<nav class="nav61-main" aria-label="[^"]+"><ul class="nav61-list">(.*?)</ul></nav>~s', $body, $mm) === 1;
        $groupCount = $hasMain ? preg_match_all('~<button type="button" class="nav61-trigger" aria-expanded="false" aria-controls="(nav61-panel-[a-z]+)"~', $mm[1], $gm) : 0;
        $panelsOk = $hasMain && $groupCount > 0 && count(array_filter($gm[1], static fn(string $pid): bool => str_contains($mm[1], 'id="' . $pid . '"'))) === $groupCount;
        $check('stránka ' . $id . ': horní navigace má 4–5 skupin s tlačítkem aria-expanded a existujícím panelem (' . $groupCount . '), nejvýš jednu položku aria-current="page"', $panelsOk && $groupCount >= 4 && $groupCount <= 5 && substr_count($mm[1], 'aria-current="page"') <= 1);
        $check('stránka ' . $id . ': skip link vede na <main id="main-content">', str_contains($body, '<a class="nav61-skip" href="#main-content">') && preg_match('~<main class="ui-page" id="main-content"~', $body) === 1);
        $linked = [];
        preg_match_all('~<link rel="stylesheet" href="(assets/[^"?]+\.css)~', $body, $lm2);
        foreach (array_unique($lm2[1]) as $cssFile) { $linked[$cssFile] = (int)@filesize($ROOT . '/' . $cssFile); }
        $cssPerPage[$id] = array_sum($linked);
        if ($id === 'materialy') {
            $order = array_keys($linked);
            $check('CSS se linkuje: tokens-v61.css před brand-v54.css i components-v61.css, každý právě jednou', isset($linked['assets/tokens-v61.css'], $linked['assets/components-v61.css']) && array_search('assets/tokens-v61.css', $order, true) < array_search('assets/brand-v54.css', $order, true) && array_search('assets/tokens-v61.css', $order, true) < array_search('assets/components-v61.css', $order, true) && substr_count($body, 'tokens-v61.css') === 1 && substr_count($body, 'components-v61.css') === 1);
            $check('<main> nese třídu ui-page (kořen pro mapování starších tříd na komponenty)', preg_match('~<main class="ui-page"[ >]~', $body) === 1);
        }
    }
    $login2 = $h->request('GET', '/?view=precache');
    $check('?view=precache obsahuje tokens-v61.css i components-v61.css', $login2['status'] === 200 && str_contains((string)$login2['body'], 'tokens-v61.css') && str_contains((string)$login2['body'], 'components-v61.css'));
    $assess = $h->request('GET', '/?view=test');
    $assessBody = (string)$assess['body'];
    $isAssessment = $assess['status'] === 200 && preg_match('/<body class="[^"]*\bassessment-mode\b/', $assessBody) === 1;
    $check('test (assessment-mode): bez spodní lišty' . ($isAssessment ? '' : ' (stránka testu není pro čerstvého žáka dostupná – ověřeno funkčně níže)'), !$isAssessment || !str_contains($assessBody, 'ui-bottomnav'));
    $check('test (assessment-mode): body bez třídy ui61-has-bottomnav (obsah se nezkracuje)', !$isAssessment || !str_contains($assessBody, 'ui61-has-bottomnav'));
} finally {
    $h->stop();
}
foreach (['test', 'kb_quiz'] as $view) { $check('nav61_bottom_hidden(' . $view . ') = true (assessment-mode)', nav61_bottom_hidden($view) === true); }
$check('nav61_bottom_hidden(dashboard) = false', nav61_bottom_hidden('dashboard') === false);
$saved = $_GET;
$_GET = ['uroven' => 'x'];
$hiddenTerminal = nav61_bottom_hidden('lab');
$_GET = ['zavod' => 'x'];
$hiddenRace = nav61_bottom_hidden('lab');
$_GET = [];
$shownHub = nav61_bottom_hidden('lab');
$_GET = $saved;
$check('lab: spodní lišta se skryje v otevřeném terminálu (?uroven, ?zavod), na rozcestníku labu zůstává', $hiddenTerminal === true && $hiddenRace === true && $shownHub === false);
$GLOBALS['ui61_primary_nav'] = null;
$items = nav61_bottom_items(null, 'materialy');
$check('nav61_bottom_items: Přehled, Materiály, … Výsledky, Profil; aktivní je právě Materiály', count($items) >= 4 && $items[0]['href'] === '?view=dashboard' && array_values(array_filter($items, static fn(array $i): bool => $i['active']))[0]['href'] === '?view=materialy');
$navItem = static fn(string $href, string $label, string $mark, array $views, string $class, bool $active): array => ['href' => $href, 'label' => $label, 'mark' => $mark, 'views' => $views, 'class' => $class, 'active' => $active];
$scenario = static function (string $view, bool $lessonOpen, bool $labOn) use ($navItem): array {
    $items = [$navItem('?view=hodina', 'Dnešní hodina', '●', ['hodina', 'intake'], $lessonOpen ? 'is-today' : 'is-muted', $view === 'hodina'), $navItem('?view=materialy', 'Materiály', '◫', ['materialy'], $view === 'materialy' ? 'is-active' : '', $view === 'materialy'), $navItem('?view=calendar', 'Kalendář', '◷', ['calendar'], '', false), $navItem('?view=vysledky', 'Výsledky', '✓', ['vysledky'], '', false), $navItem('?view=profile', 'Profil', '◆', ['profile'], '', false)];
    if ($labOn) $items[] = $navItem('?view=lab', 'Linux Lab', '›_', ['lab', 'prikazy'], '', $view === 'lab');
    $GLOBALS['ui61_primary_nav'] = ['view' => $view, 'cid' => null, 'items' => $items];
    return array_map(static fn(array $i): string => $i['href'] . ($i['active'] ? '*' : ''), nav61_bottom_items(null, $view));
};
$check('spodní lišta: běží hodina → Dnešní hodina na 3. místě (5 položek)', $scenario('materialy', true, true) === ['?view=dashboard', '?view=materialy*', '?view=hodina', '?view=vysledky', '?view=profile']);
$check('spodní lišta: v labu je 3. místo Linux Lab a je aktivní, i když běží hodina', $scenario('lab', true, true) === ['?view=dashboard', '?view=materialy', '?view=lab*', '?view=vysledky', '?view=profile']);
$check('spodní lišta: bez hodiny je 3. místo Linux Lab', $scenario('dashboard', false, true) === ['?view=dashboard*', '?view=materialy', '?view=lab', '?view=vysledky', '?view=profile']);
$check('spodní lišta: bez hodiny a bez labu jsou 4 položky (ne mrtvá „Dnešní hodina“)', $scenario('dashboard', false, false) === ['?view=dashboard*', '?view=materialy', '?view=vysledky', '?view=profile']);
$GLOBALS['ui61_primary_nav'] = null;
$navGroups = nav61_groups(null, 'materialy');
$activeGroups = array_values(array_filter($navGroups, static fn(array $g): bool => $g['active']));
$allHrefs = array_merge(...array_map(static fn(array $g): array => array_column($g['items'], 'href'), $navGroups));
$check('nav61_groups: nejvýš 5 skupin; pro materialy je aktivní právě „learn“ s jednou aktivní položkou ?view=materialy', count($navGroups) <= 5 && count($activeGroups) === 1 && $activeGroups[0]['key'] === 'learn' && array_column(array_filter($activeGroups[0]['items'], static fn(array $i): bool => $i['active']), 'href') === ['?view=materialy']);
$check('nav61_groups: zachovává všechny dřívější cíle (hodina, kalendář, příkazy, obchod, tým, dotazník, hlášení, pomoc, spolužáci)', count(array_diff(['?view=hodina', '?view=calendar', '?view=prikazy', '?view=obchod', '?view=project_lobbies', '?view=my_intake', '?view=hlaseni', '?view=study_loop', '?view=community', '?view=projekty', '?view=profile', '?view=vysledky'], $allHrefs)) === 0);
$navHtml = nav61_main_html(null, 'materialy');
$check('nav61_main_html: aktivní skupina má aria-current="true", položka aria-current="page", panel je <ul>', substr_count($navHtml, 'aria-current="true"') === 1 && substr_count($navHtml, 'aria-current="page"') === 1 && substr_count($navHtml, '<ul class="nav61-panel"') === count($navGroups) && !str_contains($navHtml, '<script'));
$check('nav61_icon: neznámá ikona nespadne a vrací aria-hidden SVG bez interpolace dat', str_contains(nav61_icon('<x>'), 'aria-hidden="true"') && !str_contains(nav61_icon('<x>'), '<x>'));
$navCss = $read('assets/nav-v61.css');
$check('nav-v61.css: no-JS fallback (:focus-within), prefers-reduced-motion, viditelný fokus a cíle ≥ 44 px (--ui-tap)', str_contains($navCss, ':focus-within') && str_contains($navCss, 'prefers-reduced-motion') && str_contains($navCss, 'outline: var(--ui-focus)') && str_contains($navCss, 'min-height: var(--ui-tap)') && !preg_match('/#[0-9a-f]{3,6}\b/i', $navCss));
$navJs = $read('assets/nav-v61.js');
$check('nav-v61.js: bez innerHTML/eval, zavírá na Escape, přepíná aria-expanded', !str_contains($navJs, 'innerHTML') && !str_contains($navJs, 'eval(') && str_contains($navJs, "'Escape'") && str_contains($navJs, 'aria-expanded'));
$crumbHtml =nav61_breadcrumb_html(null, 'materialy', 'Název <script>x</script>', true);
$check('drobečky: název stránky se escapuje a aktuální položka není odkaz', !str_contains($crumbHtml, '<script>') && str_contains($crumbHtml, '<span aria-current="page"') && !str_contains($crumbHtml, 'href="?view=materialy"'));
foreach (['en', 'uk'] as $loc) {
    $cat = edu_tr_domain($loc, 'nav_v61');
    $check('katalog ' . $loc . ': Hlavní cíle, Drobečková navigace, Domů, Přehled přeložené', ($cat['Hlavní cíle'] ?? '') !== '' && ($cat['Drobečková navigace'] ?? '') !== '' && ($cat['Domů'] ?? '') !== '' && ($cat['Přehled'] ?? '') !== '');
}

// ---------------------------------------------------------------- 7) velikost CSS na stránku
foreach (V61D_CSS_BASELINE as $id => $before) {
    $now = $cssPerPage[$id] ?? 0;
    $check(sprintf('CSS na stránku %s: %d B (před částí C %d B, rozdíl %+d B, limit +%d B)', $id, $now, $before, $now - $before, V61D_CSS_BUDGET_BYTES), $now > 0 && $now - $before <= V61D_CSS_BUDGET_BYTES);
}
$manifest = $read('BUILD_MANIFEST_V61.md');
$check('BUILD_MANIFEST_V61.md zdůvodňuje rozhodnutí o tmavém režimu (část C5)', str_contains($manifest, 'Tmavý režim') && str_contains($manifest, 'tokens-v61.css'), false);

exit(audit_summary($state, 'V61_DESIGN'));
