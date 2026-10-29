<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v78.1 · audit navigace (fáze B z docs/PROMPT_V78_RESPONSIVE.md):
 *   1) soubory, rozpočty (CSS ≤ 15 kB gzip, JS ≤ 5 kB gzip), jen tokeny a breakpointy v78, bez innerHTML, žádná nová POST akce / GET politika,
 *   2) informační architektura: 4 sekce (Dnes · Výuka · Žáci · Správa), každá skupina v68 a záložka právě v jedné sekci,
 *   3) dosažitelnost podle role: každý modul teacher58_modules (a záložka mapy) je v menu právě tehdy, když ho role smí; ≤ 3 klepnutí,
 *   4) HTTP (lib/v78_1_nav_http.php): menu, spodní lišta, drobečky + Zpět na každé záložce, aria-current, odznaky (T-14, neschválené,
 *      Komu pomoct, nepřečtené) jednotně ve všech navigacích, staré odkazy rozcestníku, žák Domů · Dnes · Úlohy · Profil (cs/en/uk),
 *   5) prohlížeč (lib/v78_1_nav_browser.cjs, Chrome + playwright-core, je-li k dispozici): 11 šířek × světlý/tmavý – bez vodorovného
 *      posunu (měření DOM), cíle ≥ 44 px, písmo ≥ 14 px; klávesnice: drawer drží fokus, Esc zavře a vrátí fokus, aria-current.
 *   php tools/v78_1_nav_audit.php     Dočasné úložiště, fiktivní data. Konec: V78_1_NAV_AUDIT_OK checks=N failed=0.
 *   Bez prohlížeče: část 5 se vypíše jako SKIP (ne FAIL); vynutit: EDUCANET_NAV_BROWSER=require.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v78-1-nav')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/ui/ui_v67.php', 'src/teacher/cockpit_v70.php', 'src/ui/nav_v78_1.php', 'src/ui/nav_v61.php'] as $lib) require_once $ROOT . '/' . $lib;

const V781_KEY = 'v78-1-nav-audit-key';
const V781_CSS_BUDGET = 15 * 1024;
const V781_JS_BUDGET = 5 * 1024;

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with($tmp, $ROOT . '/storage'));
$check('bez souboru účtů je režim legacy (role z EDUCANET_TEACHER_ROLE)', teacher59_mode() === 'legacy');

// ---------------------------------------------------------------- 1) soubory a rozpočty
$php = ['src/ui/nav_v78_1.php', 'src/ui/nav_v78_1_views.php'];
$guards = array_filter($php, static fn(string $f): bool => str_contains($read($f), "declare(strict_types=1);") && str_contains($read($f), "=== basename(__FILE__)) { http_response_code(403); exit; }"));
$check('PHP vrstvy: strict_types a 403 guard knihovny (' . implode(', ', $php) . ')', count($guards) === count($php));
$lines = [];
foreach (array_merge($php, ['assets/nav-v78-1.css', 'assets/nav-student-v78-1.css', 'assets/nav-v78-1.js', 'tools/v78_1_nav_audit.php', 'tools/lib/v78_1_nav_http.php', 'tools/lib/v78_1_nav_browser.cjs']) as $f) $lines[$f] = substr_count($read($f), "\n");
$check('soubory do 800 řádků (' . implode(', ', array_map(static fn($f, $n) => basename($f) . ' ' . $n, array_keys($lines), $lines)) . ')', max($lines) < 800 && min($lines) > 10);
$cssNew = $read('assets/nav-v78-1.css') . $read('assets/nav-student-v78-1.css');
$js = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/nav-v78-1.js'));   // bez komentářů (text „bez innerHTML“)
$cssGz = strlen((string)gzencode($read('assets/nav-v78-1.css'), 9)) + strlen((string)gzencode($read('assets/nav-student-v78-1.css'), 9));
$jsGz = strlen((string)gzencode($read('assets/nav-v78-1.js'), 9));
$check('rozpočet fáze: nové CSS ' . round($cssGz / 1024, 1) . ' kB gzip (≤ 15), JS ' . round($jsGz / 1024, 1) . ' kB gzip (≤ 5)', $cssGz <= V781_CSS_BUDGET && $jsGz <= V781_JS_BUDGET);
$noComments = (string)preg_replace('~/\*.*?\*/~s', '', $cssNew);
$check('CSS v78.1: žádné pevné barvy (hex, rgb, hsl) – jen tokeny v78 / --ui-*', preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', $noComments) !== 1);
preg_match_all('~@media([^{]*)\{~', $noComments, $mm);
$badMedia = array_values(array_filter(array_map('trim', $mm[1]), static function (string $q): bool {
    preg_match_all('~(\d+)px~', $q, $px);
    return array_diff($px[1], ['768', '1024']) !== [];
}));
$check('CSS v78.1: @media jen s breakpointy tokenů (768, 1024 px) + reduced-motion / print / forced-colors' . ($badMedia ? ' [' . implode('; ', $badMedia) . ']' : ''), $badMedia === [] && count($mm[1]) >= 5);
$check('CSS v78.1: cíle dotyku přes --target-min / min-height (navlink, Zpět, hlavička draweru, lišta žáka 60 px), reduced-motion a focus-visible',
    substr_count($cssNew, 'var(--target-min)') >= 5 && str_contains($cssNew, 'min-height: 60px') && str_contains($cssNew, 'prefers-reduced-motion') && str_contains($cssNew, ':focus-visible'));
$check('JS v78.1: bez innerHTML/eval/sítě, drawer: inert, aria-modal, Esc, Tab uvnitř, návrat fokusu', !preg_match('~innerHTML|outerHTML|eval\(|fetch\(|XMLHttpRequest|document\.write~', $js)
    && str_contains($js, '.inert = on') && str_contains($js, "'aria-modal'") && str_contains($js, "e.key === 'Escape'") && str_contains($js, "e.key !== 'Tab'") && str_contains($js, 'opener.focus()'));
$policies = json_encode([array_keys(teacher59_action_policies()), teacher59_get_policies()]);
$check('bez nové POST akce a GET politiky: žádná politika ani modul s prefixem nav781', !str_contains((string)$policies, 'nav781') && !array_filter(array_keys(teacher58_modules()), static fn(string $k): bool => str_starts_with($k, 'nav781')));

// ---------------------------------------------------------------- 2) informační architektura
$sections = nav781_sections();
$groups = teacher68_sections();
$map = teacher68_tab_map();
$check('4 hlavní sekce v pořadí Dnes · Výuka · Žáci · Správa', array_column($sections, 'label') === ['Dnes', 'Výuka', 'Žáci', 'Správa'] && array_keys($sections) === ['dnes', 'vyuka', 'zaci', 'sprava']);
$assigned = array_merge(...array_values(array_column($sections, 'groups')));
$check('každá skupina v68 (' . count($groups) . ') je právě v jedné sekci', count($assigned) === count(array_unique($assigned)) && array_diff(array_keys($groups), $assigned) === [] && array_diff($assigned, array_keys($groups)) === []);
$noSection = array_keys(array_filter($map, static fn(array $d, string $t): bool => nav781_section_of($t) === '', ARRAY_FILTER_USE_BOTH));
$check('každá záložka mapy (' . count($map) . ') má sekci; pořadí mapy = pořadí menu (Dnes, Výuka+Hodnocení, Žáci, Správa)' . ($noSection ? ' [' . implode(', ', $noSection) . ']' : ''),
    $noSection === [] && array_values(array_unique(array_map(static fn(string $t): string => nav781_section_of($t), array_keys($map)))) === array_keys($sections));
$check('staré id rozcestníku: trida → zaci, hodnoceni → vyuka, hry → zaci; neznámé → prázdné', nav781_section_param('trida') === 'zaci' && nav781_section_param('hodnoceni') === 'vyuka'
    && nav781_section_param('hry') === 'zaci' && nav781_section_param('dnes') === 'dnes' && nav781_section_param('xyz') === '' && nav781_section_param('<b>') === '');
$crumbs = nav781_breadcrumb('kompetence', 'class_3a');
$check('drobečky: Cockpit › Výuka › Kompetence, odkaz sekce nese třídu; Zpět bez JS = sekce s třídou; neplatná třída se nepřenáší',
    array_column($crumbs, 0) === ['Cockpit', 'Výuka', 'Kompetence'] && $crumbs[1][1] === 'teacher.php?tab=sekce&sekce=vyuka&class=class_3a' && $crumbs[2][1] === null
    && nav781_back_url('kompetence', 'class_3a') === 'teacher.php?tab=sekce&sekce=vyuka&class=class_3a' && nav781_back_url('kompetence', 'class_3a&x=1') === 'teacher.php?tab=sekce&sekce=vyuka'
    && teacher68_breadcrumb('kompetence', 'class_3a') === $crumbs && array_column(nav781_breadcrumb('sekce', '', 'hry'), 0) === ['Cockpit', 'Žáci']);

// ---------------------------------------------------------------- 3) dosažitelnost podle role a ≤ 3 klepnutí
$navModules = teacher58_modules();   // ne $modules – to je globální katalog tříd (teacher59_allowed_class_ids)
$avail = teacher58_tabs();
$roles = [['admin', true], ['teacher', false], ['assistant', false], ['teacher', true]];
$reach = [];
foreach ($roles as [$role, $isAdmin]) {
    $visible = teacher68_visible_tabs($role, $isAdmin, $avail);
    $inNav = array_merge(...array_values(array_map(static fn(array $g): array => array_merge(...array_values($g)), nav781_tree($visible))));
    $missing = $extra = [];
    foreach ($map as $tab => $def) {
        $mod = $navModules[$tab] ?? null;
        $can = (!$def['admin'] || $isAdmin) && teacher_permission_for_role($role, $def['perm']) && ($mod === null || (in_array($tab, $avail, true) && (empty($mod['admin']) || $isAdmin)));
        if ($can && !in_array($tab, $inNav, true)) $missing[] = $tab;
        if (!$can && in_array($tab, $inNav, true)) $extra[] = $tab;
    }
    $modsOk = array_filter(array_keys($navModules), static fn(string $t): bool => isset($map[$t]) && in_array($t, $inNav, true) !== in_array($t, $visible, true));
    $check('role ' . $role . ($isAdmin ? ' (admin)' : '') . ': menu = přesně záložky, které role smí (' . count($inNav) . '), moduly v58 (' . count($navModules) . ') podle oprávnění'
        . ($missing ? ' chybí: ' . implode(',', $missing) : '') . ($extra ? ' navíc: ' . implode(',', $extra) : ''),
        $missing === [] && $extra === [] && $modsOk === [] && count($inNav) === count(array_unique($inNav)) && count($inNav) === count($visible));
    $reach[$role . ($isAdmin ? '+admin' : '')] = $visible;
}
$unmapped = array_values(array_diff(array_keys($navModules), array_keys($map)));
$check('každý modul teacher58_modules má záložku v mapě menu (žádný osiřelý modul)' . ($unmapped ? ' [' . implode(', ', $unmapped) . ']' : ''), $unmapped === []);
$adminOnly = array_keys(array_filter($map, static fn(array $d): bool => $d['admin']));
$check('záložky jen pro administrátora (' . implode(', ', $adminOnly) . ') učitel ani asistent v menu nemá', array_intersect($adminOnly, $reach['teacher']) === [] && array_intersect($adminOnly, $reach['assistant']) === []);
$worst = 0;
$clickFail = [];
foreach ($reach as $who => $visible) {
    foreach (nav781_main_tasks() as [$label, $tab]) {
        $c = nav781_clicks($tab, $visible);
        if ($c === null) continue;
        $worst = max($worst, max($c));
        if (max($c) > 3) $clickFail[] = $who . ':' . $tab;
    }
}
$check('≤ 3 klepnutí z úvodu: ' . count(nav781_main_tasks()) . ' hlavních úloh × role, nejhorší ' . $worst . ' (lg panel / md drawer / xs lišta + rozcestník)' . ($clickFail ? ' [' . implode(', ', $clickFail) . ']' : ''),
    $clickFail === [] && $worst <= 3 && count(array_filter(nav781_main_tasks(), static fn(array $t): bool => !isset($map[$t[1]]))) === 0);

// ---------------------------------------------------------------- odznaky (čisté funkce)
$check('odznaky: české tvary (1 lekce, 3 lekce, 5 lekcí; 1 žák, 2 žáci, 7 žáků)', nav781_plural(1, 'a', 'b', 'c') === 'a' && nav781_plural(3, 'a', 'b', 'c') === 'b' && nav781_plural(5, 'a', 'b', 'c') === 'c' && nav781_plural(0, 'a', 'b', 'c') === 'c');
$_SESSION['nav781_help'] = ['class' => 'class_3a', 'n' => 3, 't' => time()];
$h1 = nav781_help_badge('class_3a', true);
$h2 = nav781_help_badge('class_1a', true);
$h3 = nav781_help_badge('class_1a', false);
$_SESSION['nav781_help']['t'] = time() - NAV781_BADGE_TTL - 5;
$h4 = nav781_help_badge('class_3a', true);
unset($_SESSION['nav781_help']);
$check('Komu pomoct: počet platí pro stejnou třídu (nebo stránku bez třídy) a nejvýš 5 minut; jen číslo a třída v relaci', $h1['n'] === 3 && $h2['n'] === 0 && $h3['n'] === 3 && $h4['n'] === 0);
$check('odznak sekce: priorita T-14 > neschválené > Komu pomoct > nepřečtené', nav781_section_badge(['attention', 'hodina', 'streda77'], ['attention' => ['n' => 1, 'label' => 'a'], 'streda77' => ['n' => 2, 'label' => 't']])['label'] === 't'
    && nav781_section_badge(['attention'], []) === null && NAV781_BADGE_PRIORITY === ['streda77', 'schvalovani', 'hodina', 'attention']);

// ---------------------------------------------------------------- 4) HTTP a 5) prohlížeč
require __DIR__ . '/lib/v78_1_nav_http.php';

exit(audit_summary($state, 'V78_1_NAV'));
