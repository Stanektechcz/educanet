<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v79.1 · audit přehlednosti obsahu (fáze D z docs/PROMPT_V78_RESPONSIVE.md):
 *   1) soubory, rozpočty fáze (CSS ≤ 15 kB gzip, JS ≤ 5 kB gzip), jen tokeny, JS bez innerHTML/sítě a s localStorage jen v try/catch, registr, politiky GET,
 *   2) čisté funkce: tabulka dt791 (whitelist GET, filtr, řazení, stránkování po 50, URL, aria-sort, karty přes data-label), prázdný stav, ukazatel s tabulkou,
 *   3) Dnešní hodina: hero karta (třída, čas, cíl, stav schválení, primární akce), pořadí Plán → Materiály → Komu pomoct → Exit ticket → Poznámky,
 *      kontextový panel (Komu pomoct + T-14), sbalitelné sekce (data-lt791-sec), staré značky pro audity v70/v71,
 *   4) prázdné stavy se zdůvodněním a tlačítkem (seznam stránek níže), formuláře (popisky, inputmode, autocomplete), ukazatele s textem,
 *   5) HTTP (lib/v79_1_content_http.php): stránky 200 bez chyb, GET řazení/filtr/strana, neplatné hodnoty ignorované, žák Úlohy/Portfolio tmavě,
 *   6) prohlížeč (lib/v79_1_content_browser.cjs): 11 šířek × světlý/tmavý – bez vodorovného posunu, cíle ≥ 44 px, písmo ≥ 14 px, kontextový panel na xl,
 *      sbalitelné sekce s pamětí (i bez úložiště), tabulka → karty pod 768 px a lepicí hlavička/první sloupec od 768 px, tmavé Úlohy/Portfolio.
 *   php tools/v79_1_content_audit.php     Dočasné úložiště, fiktivní data. Konec: V79_1_CONTENT_AUDIT_OK checks=N failed=0.
 *   Prohlížeč: EDUCANET_V791_BROWSER=require|skip; snímky do adresáře: EDUCANET_V791_SHOTS=<adresář>.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v79-1-content')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v79_1_forms.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php', 'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php',
    'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php', 'app/lib.php', 'src/ui/ui_v67.php', 'src/teacher/teacher_shell_v68.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (['hodina', 'prehled', 'schvalovani'] as $m) foreach ((array)$mods[$m]['files'] as $f) require_once $ROOT . '/' . $f;
require_once $ROOT . '/src/teacher/t14_v77.php';
require_once $ROOT . '/src/teacher/t14_v77_views.php';

const V791_KEY = 'v79-1-content-audit-key';
const V791_CSS_BUDGET = 15 * 1024;
const V791_JS_BUDGET = 5 * 1024;

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with($tmp, $ROOT . '/storage'));

// ---------------------------------------------------------------- 1) soubory, rozpočty, registr, politiky
$php = ['src/ui/ux_v79_1.php', 'src/lessons/lesson_today_v79_1_views.php'];
$guards = array_filter($php, static fn(string $f): bool => str_contains($read($f), 'declare(strict_types=1);') && str_contains($read($f), "=== basename(__FILE__)) { http_response_code(403); exit; }"));
$check('PHP vrstvy v79.1: strict_types a 403 guard', count($guards) === count($php));
$touched = array_merge($php, ['assets/content-v79-1.css', 'assets/today-v79-1.js', 'tools/v79_1_content_audit.php', 'tools/lib/v79_1_content_http.php', 'tools/lib/v79_1_content_browser.cjs', 'tools/lib/v79_1_forms.php',
    'src/lessons/lesson_today_v71_views.php', 'src/teacher/teacher_overview_v61_views.php', 'src/lessons/lesson_approval_v72_views.php', 'src/teacher/teacher_class_dashboard.php', 'src/teacher/cockpit_v70.php', 'src/teacher/insights_v74_views.php', 'src/ops/materials_v74_views.php',
    'src/student/feedback_v60_teacher_views.php', 'src/teacher/t14_v77_views.php', 'src/assessment/taskbank_v75_views.php', 'src/student/portfolio_v75_views.php', 'src/student/portfolio_v65_views.php', 'src/ui/ui_v67.php']);
$long = array_filter($touched, static fn(string $f): bool => substr_count($read($f), "\n") >= 800 || $read($f) === '');
$check('soubory existují a mají do 800 řádků (' . count($touched) . ')' . ($long ? ' [' . implode(', ', $long) . ']' : ''), $long === []);
$css = $read('assets/content-v79-1.css');
$js = $read('assets/today-v79-1.js');
$cssGz = strlen((string)gzencode($css, 9));
$jsGz = strlen((string)gzencode($js, 9));
$check('rozpočet fáze: CSS ' . round($cssGz / 1024, 2) . ' kB gzip (≤ 15), JS ' . round($jsGz / 1024, 2) . ' kB gzip (≤ 5)', $cssGz <= V791_CSS_BUDGET && $jsGz <= V791_JS_BUDGET);
$noComments = (string)preg_replace('~/\*.*?\*/~s', '', $css);
preg_match_all('~@media \((?:min|max)-width:\s*(\d+)px\)~', $noComments, $mq);
preg_match_all('~@container \((?:width [<>]=? |min-width:\s*)(\d+)px\)~', $noComments, $cq);
$check('CSS v79.1: žádné pevné barvy (hex, rgb, hsl, white/black), barvy jen tokeny; @media jen breakpointy v78 (767/768/1024/1366), @container jen 480 (úzký kontejner)',
    preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(|(?<![-\w])(white|black)(?![-\w])~', $noComments) !== 1 && array_diff(array_unique($mq[1]), ['767', '768', '1024', '1366']) === [] && array_diff(array_unique($cq[1]), ['480']) === []
    && preg_match_all('~(?<![-\w])(color|background|border(?:-[a-z]+)?|box-shadow)\s*:[^;]*var\(--~', $noComments) >= 20);
$check('CSS v79.1: oprava .btn.small (0,6rem = 9,6 px → .875rem, min. 44 px), písmo tabulky Výsledků ≥ .875rem, cíle --target-min/44px',
    str_contains($noComments, '.teacher-app .btn.small, .t68 .btn.small { font-size: .875rem; min-height: 44px;') && preg_match_all('~font-size:\s*\.(\d+)rem~', $noComments, $fs) > 0 && min(array_map(static fn(string $v): float => (float)('0.' . $v), $fs[1])) >= 0.875
    && substr_count($noComments, 'var(--target-min)') >= 4 && substr_count($noComments, 'min-height: 44px') >= 3);
$jsNo = (string)preg_replace('~/\*.*?\*/~s', '', $js);
$check('JS v79.1: bez innerHTML/eval/sítě; localStorage jen v try/catch (bez úložiště vše rozbalené), aria-expanded + aria-controls, jen pod 768 px',
    !preg_match('~innerHTML|outerHTML|insertAdjacentHTML|eval\(|fetch\(|XMLHttpRequest|sendBeacon|document\.write|sessionStorage~', $jsNo)
    && preg_match_all('~try \{[^}]*localStorage~', $jsNo) === 2 && substr_count($jsNo, 'localStorage') === 2 && str_contains($jsNo, "setAttribute('aria-expanded'") && str_contains($jsNo, "setAttribute('aria-controls'")
    && str_contains($jsNo, "matchMedia('(max-width: 767px)')"));
$h = $mods['hodina'];
$check('registr: Dnešní hodina dál jen čtení (bez post/get), CSS v71 beze změny, JS today-v79-1.js, soubory v79.1; lt71_render_tab deleguje na lt791_render_tab',
    !isset($h['post']) && !isset($h['get']) && ($h['css'] ?? []) === ['assets/cockpit-v71.css'] && ($h['js'] ?? []) === ['assets/today-v79-1.js']
    && in_array('src/lessons/lesson_today_v79_1_views.php', $h['files'], true) && in_array('src/ui/ux_v79_1.php', $h['files'], true) && str_contains($read('src/lessons/lesson_today_v71_views.php'), "if (function_exists('lt791_render_tab')) { lt791_render_tab(\$modules, \$classId, \$now); return; }"));
$check('registr: Přehled třídy a Schvalování načítají ux_v79_1.php; Výsledky třídy (teacher_class_dashboard.php) ho připojí; CSS v79.1 na každé záložce cockpitu (nav781_css_html)',
    in_array('src/ui/ux_v79_1.php', $mods['prehled']['files'], true) && in_array('src/ui/ux_v79_1.php', $mods['schvalovani']['files'], true) && str_contains($read('src/teacher/teacher_class_dashboard.php'), "require_once dirname(__DIR__, 2) . '/src/ui/ux_v79_1.php'")
    && str_contains(nav781_css_html('hodina'), 'assets/content-v79-1.css') && str_contains(nav781_css_html('design78'), 'assets/content-v79-1.css'));
$get = teacher59_get_policies();
$want = [];
foreach (DT791_TABS as $t) foreach (DT791_PARAMS as $p) $want[] = $t . '|' . $p;
$check('politiky GET: ?razeni/?smer/?filtr/?strana registrované pro ' . implode(', ', DT791_TABS) . ' (informativní, bez omezení navíc; třídu hlídá obecná kontrola)',
    array_filter($want, static fn(string $k): bool => ($get[$k] ?? null) !== []) === [] && DT791_PARAMS === ['razeni', 'smer', 'filtr', 'strana'] && DT791_TABS === ['prehled', 'schvalovani', 'class_results']);
$check('politiky: modul prehled má dál jen GET export, schvalovani bez GET registru (tabulka nepřidává POST ani GET registr modulu)',
    array_keys((array)($mods['prehled']['get'] ?? [])) === ['export'] && !isset($mods['schvalovani']['get']) && array_keys((array)$mods['prehled']['post']) === ['ov61_'] && array_keys((array)$mods['schvalovani']['post']) === ['lc72_']);

// ---------------------------------------------------------------- 2) čisté funkce
$st = dt791_state(['a', 'b'], 'a', 'asc', ['razeni' => '<script>', 'smer' => 'nahoru', 'filtr' => "x\x01y" . str_repeat('z', 100), 'strana' => '12345']);
$st2 = dt791_state(['a', 'b'], 'a', 'asc', ['razeni' => 'b', 'smer' => 'desc', 'filtr' => '  Žák ', 'strana' => '3']);
$check('dt791_state: neznámý sloupec → výchozí, směr jen asc|desc, filtr bez řídicích znaků a ≤ 80 znaků, strana jen 1–4 číslice; platné hodnoty projdou',
    $st['sort'] === 'a' && $st['dir'] === 'asc' && $st['q'] === 'x y' . str_repeat('z', 77) && mb_strlen($st['q']) === 80 && $st['page'] === 1
    && $st2 === ['sort' => 'b', 'dir' => 'desc', 'q' => 'Žák', 'page' => 3, 'default_sort' => 'a', 'default_dir' => 'asc']);
$rows = [];
for ($i = 1; $i <= 101; $i++) $rows[] = ['sort' => ['n' => $i, 'jmeno' => 'Žák ' . $i, 'm' => $i % 7 === 0 ? null : $i % 10], 'text' => 'Žák ' . $i . ($i === 55 ? ' Čtvrťák' : ''), 'cells' => [(string)$i]];
$p1 = dt791_apply($rows, dt791_state(['n', 'jmeno', 'm'], 'n'));
$p3 = dt791_apply($rows, dt791_state(['n', 'jmeno', 'm'], 'n', 'asc', ['strana' => '3']));
$p9 = dt791_apply($rows, dt791_state(['n', 'jmeno', 'm'], 'n', 'asc', ['strana' => '9']));
$desc = dt791_apply($rows, dt791_state(['n', 'jmeno', 'm'], 'n', 'asc', ['razeni' => 'm', 'smer' => 'desc']));
$q = dt791_apply($rows, dt791_state(['n'], 'n', 'asc', ['filtr' => 'ctvrtak']));
$check('dt791_apply: 101 řádků = 3 strany po 50 (50/50/1), strana mimo rozsah = poslední, filtr bez diakritiky a velikosti písmen, řazení sestupně s null na konci a stabilní',
    count($p1['rows']) === 50 && $p1['pages'] === 3 && $p1['from'] === 1 && $p1['to'] === 50 && count($p3['rows']) === 1 && $p3['from'] === 101 && $p9['page'] === 3
    && $q['matched'] === 1 && $q['rows'][0]['sort']['n'] === 55 && $desc['rows'][0]['sort']['m'] === 9 && $desc['rows'][0]['sort']['n'] === 9
    && end($desc['rows'])['sort']['m'] !== null && dt791_apply($rows, dt791_state(['n', 'm'], 'n', 'asc', ['razeni' => 'm', 'strana' => '3']))['rows'][0]['sort']['m'] === null);
$base = ['tab' => 'prehled', 'class' => 'class_3a'];
$s0 = dt791_state(['zak'], 'poradi');
$check('dt791_url: výchozí hodnoty se do URL nepíšou, změna řazení/strany/filtru ano (+ kotva tabulky)',
    dt791_url($base, $s0, []) === '?tab=prehled&class=class_3a' && dt791_url($base, $s0, ['sort' => 'zak', 'dir' => 'desc', 'page' => 2, 'q' => 'Á b'], 'x') === '?tab=prehled&class=class_3a&razeni=zak&smer=desc&filtr=%C3%81+b&strana=2#x');
$th = dt791_th(['key' => 'zak', 'label' => 'Žák', 'sort' => true], $base, dt791_state(['zak'], 'poradi', 'asc', ['razeni' => 'zak']), 't');
$check('dt791_th: aktivní sloupec má aria-sort a odkaz na opačný směr, šipka jen dekorace, směr textem pro čtečky; neřaditelný sloupec bez odkazu',
    str_contains($th, 'aria-sort="ascending"') && str_contains($th, 'smer=desc') && str_contains($th, '<span aria-hidden="true">▲</span>') && str_contains($th, 'class="ds-sr"')
    && dt791_th(['key' => 'x', 'label' => 'Důvod'], $base, $s0, 't') === '<th scope="col">Důvod</th>');
$tbl = dt791_table_html('t1', 'Tabulka <x>', [['key' => 'n', 'label' => 'Číslo', 'sort' => true], ['key' => 'jmeno', 'label' => 'Jméno']], array_map(static fn(array $r): array => array_replace($r, ['cells' => [(string)$r['sort']['n'], e($r['text'])]]), $rows), $base, dt791_state(['n'], 'n'));
$check('dt791_table_html: filtr (GET, role=search, skrytá záložka a třída, type=search s inputmode), počet „Zobrazeno 1–50 z 101“ (role=status), oblast s posuvem (region, tabindex, popisek), caption, th scope=row, td data-label, stránkování (nav, aria-current)',
    str_contains($tbl, 'role="search"') && str_contains($tbl, '<input type="hidden" name="tab" value="prehled">') && str_contains($tbl, 'type="search" name="filtr" inputmode="search"') && str_contains($tbl, 'Zobrazeno 1–50 z 101')
    && str_contains($tbl, 'role="region" tabindex="0" aria-labelledby="t1-cap"') && str_contains($tbl, 'Tabulka &lt;x&gt;') && substr_count($tbl, '<th scope="row">') === 50 && substr_count($tbl, 'data-label="Jméno"') === 50
    && str_contains($tbl, '<nav class="dt791-pager" aria-label="Stránkování">') && str_contains($tbl, 'aria-current="page">1<') && str_contains($tbl, 'strana=2'));
$none = dt791_table_html('t2', 'T', [['key' => 'n', 'label' => 'N', 'sort' => true]], $rows, $base, dt791_state(['n'], 'n', 'asc', ['filtr' => 'nic-takoveho']));
$empty = ux791_empty_html('Titulek <b>', 'Proč', [['?tab=hodina', 'Akce'], ['javascript:alert(1)', 'Zlá'], ['?tab=x', '']]);
$check('prázdný stav: role=status, nadpis + proč (escapované), primární tlačítko; nebezpečné URL a prázdný popisek se vynechají; filtr bez výsledku → prázdný stav se „Zrušit filtr“',
    str_contains($empty, 'role="status"') && str_contains($empty, '<h2 class="ux791-empty__title">Titulek &lt;b&gt;</h2><p>Proč</p>') && str_contains($empty, '<a class="ds-btn ds-btn--primary" href="?tab=hodina">Akce</a>')
    && !str_contains($empty, 'javascript') && !str_contains($empty, 'Zlá') && str_contains($none, 'Filtru nic neodpovídá') && str_contains($none, '>Zrušit filtr</a>') && !str_contains($none, '<table'));
$bars = ind791_bars_html('Známky', [['Známka 1', 3, '3 žáci'], ['Známka 2', 0, '0 žáků']], 'Počet žáků');
$check('ukazatel: pruhy jen dekorace (aria-hidden), každý řádek má popisek i hodnotu textem, tabulka pro čtečky se stejnými čísly (caption, th scope)',
    str_contains($bars, '<ul class="ind791-bars" aria-hidden="true">') && substr_count($bars, 'class="ind791-value"') === 2 && str_contains($bars, '<table class="ds-sr"><caption>Známky</caption>')
    && str_contains($bars, '<th scope="row">Známka 1</th><td>3 žáci</td>') && str_contains($bars, 'style="width:100%"') && str_contains($bars, 'style="width:0%"'));
$kpi = c70_kpis_html([['A', '1', 'x', 'warn'], ['B', '2', 'y', 'ok'], ['C', '3', 'z', '']], 'Souhrn');
$check('ukazatele KPI: tón i textem („Pozor“ / „V pořádku“), neutrální bez textu tónu', str_contains($kpi, '<em class="c791-tone">Pozor</em>') && str_contains($kpi, '<em class="c791-tone">V pořádku</em>') && substr_count($kpi, 'c791-tone') === 2);

require __DIR__ . '/lib/v79_1_content_http.php';

exit(audit_summary($state, 'V79_1_CONTENT'));
