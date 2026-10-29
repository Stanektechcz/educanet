<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.1 · audit podpory učitele (Mapa třídy, rychlá zpětná vazba, přidělení procvičování, menší HTML stránek žáka):
 *   1) soubory, guardy, ≤ 800 řádků, rozpočty NA STRÁNKU (nové CSS ≤ 15 kB gzip, nové JS ≤ 5 kB gzip), jen tokeny v78, JS bez innerHTML/sítě/úložiště,
 *      invariant simulátoru v nových souborech, registr modulu mapa801, politiky teacher59 (POST exact, GET informativní, jiné ts801_* zakázané),
 *      oprávnění (asistent nic neodešle), navigace a tmavý režim, i18n, retence, sdílený drawer a menu žáka (cvičení + opakování),
 *   2) čisté funkce a úložiště (lib/v80_1_teacher_part2.php): mapa na fixture (každá buňka proti vzoru), řazení, filtry (oblast, lekce, neplatné hodnoty),
 *      zpětná vazba (čištění, limity, stažení), přidělení (4 druhy, splnění, limit otevřených), retence po 180 dnech i po odchodu žáka, soukromí dat,
 *   3) HTTP (lib/v80_1_teacher_http.php): mapa, filtry, skrytá jména, POST s/bez CSRF, výběr z Komu pomoct, žák vidí „Od učitele“ jako Další krok,
 *      splnění cvičení, soukromí mezi žáky, en/uk, stránka Linux příkazy (rozpočet v61 s rezervou ≥ 3 kB), rozsah tříd a asistent (účty v59),
 *   4) prohlížeč (lib/v80_1_teacher_browser.cjs): mapa, tabulka, Můj pokrok a Linux příkazy × 11 šířek × světlý/tmavý (posun, 44 px, 14 px),
 *      lepicí hlavička a první sloupec, klik na buňku = detail žáka, výběr a fráze (JS), drawer a jazyk v menu účtu (klon v JS).
 *   php tools/v80_1_teacher_audit.php   Dočasné úložiště, fiktivní data. Konec: V80_1_TEACHER_AUDIT_OK checks=N failed=0.
 *   Prohlížeč: EDUCANET_V801_BROWSER=require|skip; snímky: EDUCANET_V801_SHOTS=<adresář> (JPEG ≤ 150 kB).
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v80-1-teacher')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/ops/runtime_content.php', 'app/lib.php', 'src/ui/ui_v67.php', 'src/ui/nav_v61.php', 'src/student/identity_v58.php',
    'src/teacher/cockpit_v70.php', 'src/teacher/teacher_prefs_v72_1.php', 'src/assessment/competencies_v62.php', 'src/assessment/evidence_v62.php', 'src/assessment/mastery_v62.php', 'src/lessons/lesson_model_v71_sources.php', 'src/lessons/lesson_model_v71.php', 'src/assessment/competencies_v75_views.php',
    'src/assessment/taskbank_v75_views.php', 'src/assessment/taskbank_v76.php', 'src/lessons/learning_v80_views.php', 'src/ui/widgets_v80_views.php', 'src/student/progress_v80_views.php', 'src/student/retention_v76.php', 'src/projects/projects_v65.php',
    'src/teacher/classmap_v80_1.php', 'src/student/support_v80_1.php', 'src/student/support_v80_1_teacher.php', 'src/student/support_v80_1_views.php', 'src/teacher/classmap_v80_1_views.php', 'src/teacher/insights_v74_views.php', 'src/student/student_v55.php'] as $lib) require_once $ROOT . '/' . $lib;
app_require_libs(['core', 'layout', 'paths', 'projects65']);
require_once __DIR__ . '/lib/v80_1_fixture.php';

const V801_KEY = 'v80-1-teacher-audit-key';
const V801_CSS_BUDGET = 15 * 1024;
const V801_JS_BUDGET = 5 * 1024;
const V801_PRIKAZY_MAX = (62 - 3) * 1024;   // rozpočet v61 62 kB s rezervou ≥ 3 kB
const V801_NEW_PHP = ['src/teacher/classmap_v80_1.php', 'src/teacher/classmap_v80_1_views.php', 'src/student/support_v80_1.php', 'src/student/support_v80_1_teacher.php', 'src/student/support_v80_1_views.php'];

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-') && STORAGE_DIR === $tmp);

// ---------------------------------------------------------------- 1) soubory a pravidla
$bad = [];
foreach (V801_NEW_PHP as $f) {
    $src = $read($f);
    if ($src === '' || !str_contains($src, 'declare(strict_types=1);') || !str_contains($src, "basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)") || substr_count($src, "\n") > 800) $bad[] = $f;
}
$check('nové PHP soubory: strict_types, guard přímého přístupu, ≤ 800 řádků' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$forbidden = '/\b(exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|eval|assert|create_function|fsockopen|stream_socket_client|curl_[a-z_]+|socket_[a-z_]+|mail|gethostbyname|dns_get_record)\s*\(/i';
$hits = array_filter(V801_NEW_PHP, static fn(string $f): bool => preg_match($forbidden, (string)preg_replace('~/\*.*?\*/|//[^\n]*~s', '', $read($f))) === 1 || str_contains((string)preg_replace('~/\*.*?\*/|//[^
]*~s', '', $read($f)), '`'));
$check('invariant: nové soubory nic nespouštějí a nikam se nepřipojují (žádné exec/síť/eval/zpětné apostrofy)', $hits === []);
$css = $read('assets/classmap-v80-1.css');
$js = $read('assets/classmap-v80-1.js');
$navJs = $read('assets/nav-v61.js');
$cssClean = (string)preg_replace('~/\*.*?\*/~s', '', $css);
preg_match_all('~@media[^{]+~', $cssClean, $mq);
preg_match_all('~font-size:\s*([^;}]+)~', $cssClean, $fs);
$check('CSS mapy: jen tokeny (žádné #hex/rgb), @media jen 768 px, písmo jen var(--fs-*), cíle var(--target-min)',
    preg_match('~#[0-9a-f]{3,8}\b|rgba?\(|hsl~i', $cssClean) !== 1 && array_filter($mq[0], static fn(string $m): bool => trim($m) !== '@media (min-width: 768px)') === []
    && array_filter($fs[1], static fn(string $v): bool => !str_starts_with(trim($v), 'var(--fs-')) === [] && str_contains($css, 'var(--target-min)'));
$cssGz = strlen((string)gzencode($css, 9));
$jsGz = strlen((string)gzencode($js, 9));
$check('rozpočet NA STRÁNKU: nové CSS záložky ' . round($cssGz / 1024, 1) . ' kB gzip (≤ 15 kB), nové JS ' . round($jsGz / 1024, 1) . ' kB gzip (≤ 5 kB); žák: žádné nové CSS/JS', $cssGz <= V801_CSS_BUDGET && $jsGz <= V801_JS_BUDGET);
$noNet = static fn(string $s): bool => !preg_match('~innerHTML|outerHTML|insertAdjacentHTML|eval\(|new Function|fetch\(|XMLHttpRequest|localStorage|sessionStorage|document\.write~', (string)preg_replace('~/\*.*?\*/~s', '', $s));
$check('JS: mapa a sdílený drawer bez innerHTML, sítě a úložiště (cloneNode/textContent)', $noNet($js) && $noNet($navJs) && str_contains($navJs, 'cloneNode(true)') && str_contains($navJs, 'textContent'));

$mods = teacher58_modules();
$m = $mods['mapa801'] ?? [];
$check('registr: modul mapa801 (Mapa třídy) se soubory, CSS/JS a POST prefixem ts801_; všechny soubory existují',
    ($m['label'] ?? '') === 'Mapa třídy' && array_keys((array)($m['post'] ?? [])) === ['ts801_'] && ($m['post']['ts801_'] ?? '') === 'ts801_teacher_handle_post' && in_array('assets/classmap-v80-1.js', (array)($m['js'] ?? []), true)
    && in_array('assets/classmap-v80-1.css', (array)($m['css'] ?? []), true) && teacher58_available('mapa801') && !isset($m['get']));
$map = teacher68_tab_map();
$check('navigace a vzhled: Mapa třídy v sekci Třída a žáci, tmavý režim cockpitu', ($map['mapa801']['section'] ?? '') === 'trida' && in_array('mapa801', UI68_TEACHER_DARK_TABS, true));
$req = ['ts801_feedback_send', 'ts801_feedback_withdraw', 'ts801_assign', 'ts801_assign_cancel', 'ts801_select'];
$pol = teacher59_action_policies();
$check('politiky teacher59: 5 akcí se třídou povinnou a v rozsahu, vlastní fráze bez třídy; neznámá ts801_* akce zakázaná (deny-by-default)',
    array_filter($req, static fn(string $a): bool => ($pol[$a]['class'] ?? '') !== 'required') === [] && isset($pol['ts801_phrase_add'], $pol['ts801_phrase_delete'])
    && ($pol['ts801_phrase_add']['class'] ?? '') !== 'required' && !empty(teacher59_action_policy('ts801_neco')['deny']) && !empty(teacher59_action_policy('ts801_')['deny']));
$check('oprávnění: prefix ts801_ = students.manage → učitel ano, asistent ne (nic neodešle), neznámá akce mimo prefix zamítnutá',
    teacher_action_permission('ts801_assign') === 'students.manage' && teacher_permission_for_role('teacher', 'students.manage') && !teacher_permission_for_role('assistant', 'students.manage')
    && teacher_action_permission('ts802_x') === TEACHER_PERMISSION_DENY);
$get = array_values(array_filter(array_keys(teacher59_get_policies()), static fn(string $k): bool => str_starts_with($k, 'mapa801|')));
sort($get);
$check('GET: filtry mapy jako informativní politiky (zdroj, oblast, od, do, razeni, smer, zobrazeni); výběr žáků jen POSTem', $get === ['mapa801|do', 'mapa801|oblast', 'mapa801|od', 'mapa801|razeni', 'mapa801|smer', 'mapa801|zdroj', 'mapa801|zobrazeni']);
$snap = array_filter(['tools/v68_cockpit_audit.php', 'tools/v70_cockpit_audit.php', 'tools/v71_lesson_model_audit.php'], static fn(string $f): bool => !str_contains($read($f), "'ts801_feedback_send'") || !str_contains($read($f), "unset(\$get['mapa801|'"));
$check('snímky politik v68/v70/v71: nové akce a GET filtry výslovně vyjmuté (změna limitu auditu zaznamenaná)', $snap === []);
$domains = require $ROOT . '/lang/domains_v59.php';
$en = require $ROOT . '/lang/en/ui/support_v80_1.php';
$uk = require $ROOT . '/lang/uk/ui/support_v80_1.php';
$check('i18n: doména support_v80_1 v manifestu, katalogy en/uk se stejnými klíči; menu žáka (cvičení, opakování) a Další krok „Od učitele“ v en/uk',
    in_array('src/student/support_v80_1_views.php', (array)($domains['domains']['support_v80_1'] ?? []), true) && array_keys($en) === array_keys($uk) && count($en) >= 20
    && isset((require $ROOT . '/lang/en/ui/nav_v61.php')['Interaktivní cvičení'], (require $ROOT . '/lang/uk/ui/nav_v61.php')['Opakování chyb'], (require $ROOT . '/lang/uk/ui/learning_v80.php')['Od učitele']));
$check('retence: retention_v76 maže i zpětnou vazbu a přidělení (ts801_retention_purge), tools/v58_retention.php vypisuje v80_1_data_purged=',
    str_contains($read('src/student/retention_v76.php'), "\$res['support'] = ts801_retention_purge(") && str_contains($read('tools/v58_retention.php'), 'v80_1_data_purged='));

// ---------------------------------------------------------------- 1b) menší HTML žáka a menu
$GLOBALS['ui61_primary_nav'] = null;
$hrefs = array_merge(...array_map(static fn(array $g): array => array_column($g['items'], 'href'), nav61_groups('class_3a', 'dashboard')));
$drawer = nav61_drawer_html('class_3a', 'dashboard');
$main = nav61_main_html('class_3a', 'dashboard');
$groups = nav61_drawer_groups('class_3a', 'dashboard');
$missing = array_filter(array_merge(...array_column($groups, 'hrefs')), static fn(string $h): bool => !str_contains($main, 'href="' . e($h) . '"'));
$check('menu žáka: Učení obsahuje Interaktivní cvičení a Opakování chyb; drawer menu účtu je v HTML prázdný (sdílené značky), JS ho naklonuje z horní lišty – každý odkaz draweru v horní liště je',
    in_array('?view=cviceni', $hrefs, true) && in_array('?view=opakovat', $hrefs, true) && preg_match('~^<div class="nav61-drawer" data-nav61-skip="[^"]*" data-nav61-head="v56-menu-label"></div>$~', $drawer) === 1
    && count($groups) >= 4 && $missing === [] && str_contains($read('app/views/_layout.php'), '<div class="v56-menu-lang" data-nav61-lang></div>'));
if (!function_exists('edu801_collapse_ws')) { $view = 'audit'; include $ROOT . '/app/views/linux_lab.php'; }   // jen definice funkce (jiný pohled nic nevykreslí)
$sample = "<div>\n      <p>a</p>\n  <pre>x\n   y</pre>\n\n <span>b</span> <span>c</span></div>";
$check('Linux příkazy: zkrácení odsazení šablon mění jen mezery se zalomením mezi značkami, <pre> beze změny, mezera mezi inline prvky zůstává',
    edu801_collapse_ws($sample) === "<div>\n<p>a</p>\n<pre>x\n   y</pre>\n<span>b</span> <span>c</span></div>");

require __DIR__ . '/lib/v80_1_teacher_part2.php';
require __DIR__ . '/lib/v80_1_teacher_http.php';

exit(audit_summary($state, 'V80_1_TEACHER'));
