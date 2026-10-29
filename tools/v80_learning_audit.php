<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80 · audit podpory učení (Můj pokrok, odstupňované nápovědy, vysvětlení chyb, opakování 1/3/7 dní, interaktivní cvičení, cíle a odznaky):
 *   1) soubory, guardy, rozpočty NA STRÁNKU (nové CSS fáze ≤ 15 kB gzip vč. tmavé vrstvy pohledu, nové JS ≤ 5 kB gzip), jen tokeny, JS bez innerHTML a sítě,
 *      invariant simulátoru (žádné spouštění ani síť v nových souborech), registr rout, záložka učitele, tmavý režim, i18n,
 *   2) čisté funkce (lib/v80_learning_part2.php): nápovědy a vysvětlení chyb pro všechny generátory, specificita, opakování, cvičení, cíle, odznaky,
 *      úložiště (jen počty a id, žádné odpovědi ani jména), retence po odchodu žáka i po roce nečinnosti, souhrn pro učitele,
 *   3) HTTP (lib/v80_learning_http.php): stránky žáka, nápověda POST + CSRF, chyba → vysvětlení + opakování, splatné opakování, cvičení kontrolovaná serverem
 *      bez JS, cíle, záložka pokrok80 a rozsah tříd, soukromí, en/uk,
 *   4) prohlížeč (lib/v80_learning_browser.cjs): 11 šířek × světlý/tmavý – bez vodorovného posunu, cíle ≥ 44 px, písmo ≥ 14 px, tmavé pozadí podle zařízení;
 *      klávesnice a živé pomůcky (aria-live) u všech 4 cvičení, nápověda klávesnicí.
 *   php tools/v80_learning_audit.php      Dočasné úložiště, fiktivní data. Konec: V80_LEARNING_AUDIT_OK checks=N failed=0.
 *   Prohlížeč: EDUCANET_V80_BROWSER=require|skip; snímky: EDUCANET_V80_SHOTS=<adresář> (JPEG ≤ 150 kB).
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v80-learning')), '/');
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/ops/runtime_content.php', 'app/lib.php', 'src/ui/ui_v67.php', 'src/ui/nav_v61.php',
    'src/assessment/taskbank_v75_views.php', 'src/lessons/learning_v80_views.php', 'src/ui/widgets_v80_views.php', 'src/lessons/learning_v80_post.php', 'src/student/progress_v80_views.php', 'src/student/retention_v76.php', 'src/assessment/evidence_v62.php', 'src/projects/projects_v65.php',
    'src/student/identity_v58.php', 'src/linux/linux_v57_lab.php', 'src/student/student_v55.php'] as $lib) require_once $ROOT . '/' . $lib;
app_require_libs(['core', 'layout', 'paths', 'projects65']);

const V80_KEY = 'v80-learning-audit-key';
const V80_PAGE_CSS_BUDGET = 15 * 1024;
const V80_JS_BUDGET = 5 * 1024;
const V80_VIEWS = ['pokrok', 'cviceni', 'opakovat'];

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$gz = static fn(string $rel): int => strlen((string)gzencode($read($rel), 9));
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with($tmp, $ROOT . '/storage'));

// ---------------------------------------------------------------- 1) soubory, rozpočty, pravidla kódu
$php = ['src/lessons/hints_v80.php', 'src/lessons/hints_v80_gfx.php', 'src/lessons/learning_v80.php', 'src/ui/widgets_v80.php', 'src/student/progress_v80.php', 'src/lessons/learning_v80_views.php', 'src/student/progress_v80_views.php', 'src/ui/widgets_v80_views.php',
    'src/lessons/learning_v80_post.php', 'src/lessons/learning_v80_teacher_views.php', 'app/views/learning_v80.php', 'app/views/pokrok_v80.php', 'app/actions/learning_v80.php'];
$guards = array_filter($php, static fn(string $f): bool => str_contains($read($f), 'declare(strict_types=1);') && str_contains($read($f), "=== basename(__FILE__)) { http_response_code(403); exit; }"));
$check('PHP vrstvy v80 (' . count($php) . '): strict_types a 403 guard', count($guards) === count($php));
$all = array_merge($php, ['assets/learning-v80.css', 'assets/practice-v80.js', 'lang/en/ui/learning_v80.php', 'lang/uk/ui/learning_v80.php', 'tools/v80_learning_audit.php', 'tools/lib/v80_learning_part2.php',
    'tools/lib/v80_learning_http.php', 'tools/lib/v80_learning_browser.cjs', 'src/assessment/taskbank_v75.php', 'src/assessment/taskbank_v75_views.php', 'src/ui/stepper_v79_student.php', 'src/student/retention_v76.php', 'src/ui/nav_v61.php', 'tools/lib/http_harness.php', 'tools/run_audits.php']);
$long = array_filter($all, static fn(string $f): bool => substr_count($read($f), "\n") >= 800 || $read($f) === '');
$check('soubory existují a mají do 800 řádků (' . count($all) . ')' . ($long ? ' [' . implode(', ', $long) . ']' : ''), $long === []);
$cssGz = $gz('assets/learning-v80.css');
$jsGz = $gz('assets/practice-v80.js');
$pages = [];
foreach (V80_VIEWS as $v) $pages[$v] = $cssGz + $gz(ui69_student_dark_css($v));
$check('rozpočet NA STRÁNKU: nové CSS fáze ' . implode(', ', array_map(static fn(string $v, int $b): string => $v . ' ' . round($b / 1024, 1) . ' kB', array_keys($pages), $pages))
    . ' gzip (learning-v80 ' . round($cssGz / 1024, 1) . ' kB + tmavá vrstva pohledu; ≤ 15), Úlohy +' . round($cssGz / 1024, 1) . ' kB; JS practice-v80 ' . round($jsGz / 1024, 2) . ' kB gzip (≤ 5)',
    max($pages) <= V80_PAGE_CSS_BUDGET && $cssGz <= V80_PAGE_CSS_BUDGET && $jsGz <= V80_JS_BUDGET);
$css = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/learning-v80.css'));
preg_match_all('~@media \((?:min|max)-width:\s*(\d+)px\)~', $css, $mq);
preg_match_all('~font-size:\s*([^;}]+)~', $css, $fs);
$check('CSS v80: barvy jen tokeny (žádné hex/rgb/hsl/white/black), @media jen 768 px, písmo jen var(--fs-*), cíle var(--target-min)',
    preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(|(?<![-\w])(white|black)(?![-\w])~', $css) !== 1 && array_diff(array_unique($mq[1]), ['768']) === []
    && array_filter($fs[1], static fn(string $v): bool => !str_starts_with(trim($v), 'var(--fs-')) === [] && substr_count($css, 'var(--target-min)') >= 5 && substr_count($css, 'var(--') >= 120);
$js = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/practice-v80.js'));
$check('JS v80: bez innerHTML/eval/sítě/úložiště, texty jen z data-t (žádná čeština v JS), aria-live výstupy plní textContent',
    preg_match('~innerHTML|outerHTML|insertAdjacentHTML|eval\(|new Function|fetch\(|XMLHttpRequest|sendBeacon|WebSocket|EventSource|document\.write|localStorage|sessionStorage|import\(~', $js) !== 1
    && preg_match('~[áčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ]~u', $js) !== 1 && str_contains($js, 'textContent') && str_contains($js, "getAttribute('data-t')"));
$forbidden = '~\b(exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|eval|assert|create_function|fsockopen|stream_socket_client|socket_[a-z_]+|curl_[a-z_]+|gethostbyname|dns_get_record|mail)\s*\(|`[^`]*`|file_get_contents\s*\(\s*[\'"]https?:~i';
$bad = array_filter(['src/lessons/hints_v80.php', 'src/lessons/hints_v80_gfx.php', 'src/lessons/learning_v80.php', 'src/ui/widgets_v80.php', 'src/student/progress_v80.php', 'src/lessons/learning_v80_views.php', 'src/student/progress_v80_views.php', 'src/ui/widgets_v80_views.php', 'src/lessons/learning_v80_post.php', 'src/lessons/learning_v80_teacher_views.php'],
    static fn(string $f): bool => preg_match($forbidden, (string)preg_replace(['~/\*.*?\*/~s', '~//[^\n]*~'], '', $read($f))) === 1);
$check('invariant simulátoru: nové soubory nic nespouštějí, nepřipojují se a neposílají poštu (chmod/podsítě = čistý výpočet)' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$routes = require $ROOT . '/app/routes.php';
$find = static function (string $stage, string $name) use ($routes): ?array { foreach ($routes[$stage] as $r) if (in_array($name, $r['match'], true)) return $r; return null; };
$check('router: pohledy pokrok / cviceni / opakovat (views_student) se skupinami learning80 (+ progress80); akce lr80_* jen v actions_class (třída povinná, CSRF ověřil index.php)',
    ($find('views_student', 'pokrok')['file'] ?? '') === 'views/pokrok_v80.php' && in_array('progress80', $find('views_student', 'pokrok')['libs'], true)
    && ($find('views_student', 'cviceni')['file'] ?? '') === 'views/learning_v80.php' && ($find('views_student', 'opakovat')['file'] ?? '') === 'views/learning_v80.php'
    && array_diff(LR80_ACTIONS, $find('actions_class', 'lr80_hint')['match'] ?? []) === [] && $find('actions_pre', 'lr80_hint') === null
    && in_array('learning80', $find('views_student', 'ulohy')['libs'], true) && in_array('learning80', $find('actions_class', 'tb75_answer')['libs'], true));
$mods = teacher58_modules();
$m80 = $mods['pokrok80'] ?? [];
$map = teacher68_tab_map();
$check('učitel: záložka pokrok80 (Třída a žáci) jen čtení – bez POST, bez GET registru, tmavý režim; žádná politika lr80_* pro učitele (akce žáka nejdou přes teacher.php)',
    ($m80['label'] ?? '') === 'Pokrok a podpora žáků' && !isset($m80['post']) && !isset($m80['get']) && in_array('src/lessons/learning_v80_teacher_views.php', (array)($m80['files'] ?? []), true)
    && ($map['pokrok80']['section'] ?? '') === 'trida' && in_array('pokrok80', UI68_TEACHER_DARK_TABS, true)
    && array_filter(array_keys(teacher59_action_policies()), static fn($k): bool => str_starts_with((string)$k, 'lr80_')) === [] && !empty(teacher59_action_policy('lr80_hint')['deny'])
    && array_filter(array_keys(teacher59_get_policies()), static fn($k): bool => str_starts_with((string)$k, 'pokrok80|')) === []);
require_once __DIR__ . '/v69_dark_overlay.php';
$stale = array_filter(V80_VIEWS, static fn(string $v): bool => !isset(DO69_VIEWS[$v]) || $read(DO69_VIEWS[$v]['out']) !== do69_build($ROOT, $v));
$check('tmavý režim podle zařízení: pokrok, cviceni, opakovat v UI67_DARK_VIEWS (výchozí „podle systému“), tmavá vrstva v69 vygenerovaná a aktuální' . ($stale ? ' [' . implode(', ', $stale) . ']' : ''),
    array_diff(V80_VIEWS, ui67_dark_views()) === [] && $stale === [] && ui67_effective_theme('pokrok', 'system') === 'system');
$domains = require $ROOT . '/lang/domains_v59.php';
$en = require $ROOT . '/lang/en/ui/learning_v80.php';
$uk = require $ROOT . '/lang/uk/ui/learning_v80.php';
$check('i18n: doména learning_v80 (5 souborů UI + pohledy) v manifestu, katalogy en/uk se stejnými klíči; navigace má en/uk pro Můj pokrok',
    array_diff(['src/lessons/learning_v80_views.php', 'src/student/progress_v80_views.php', 'src/ui/widgets_v80_views.php', 'src/lessons/learning_v80_post.php', 'app/views/learning_v80.php', 'app/views/pokrok_v80.php'], $domains['domains']['learning_v80'] ?? []) === []
    && array_keys($en) === array_keys($uk) && count($en) >= 150 && isset((require $ROOT . '/lang/en/ui/nav_v61.php')['Můj pokrok'], (require $ROOT . '/lang/uk/ui/nav_v61.php')['Můj pokrok']));
$GLOBALS['ui61_primary_nav'] = null;
$hrefs = array_merge(...array_map(static fn(array $g): array => array_column($g['items'], 'href'), nav61_groups('class_3a', 'pokrok')));
// v80.1 (změna limitu auditu, BUILD_MANIFEST_V80_1.md): po zmenšení HTML hlavičky (sdílený drawer) jsou cvičení a opakování chyb i v menu Učení.
$check('navigace žáka: Můj pokrok (Dnes); cvičení a opakování v rozcestníku Úloh a Mého pokroku a od v80.1 i v menu Učení; spodní lišta dál 4 položky, „Úlohy“ aktivní i na cvičení a opakování',
    in_array('?view=pokrok', $hrefs, true) && in_array('?view=cviceni', $hrefs, true) && in_array('?view=opakovat', $hrefs, true) && str_contains(lr80_links_html(), '?view=cviceni') && str_contains(lr80_links_html(), '?view=opakovat') && count(nav61_bottom_items('class_3a', 'cviceni')) === 4
    && nav61_bottom_items('class_3a', 'opakovat')[2]['active'] && nav61_bottom_items('class_3a', 'cviceni')[2]['active']);

require __DIR__ . '/lib/v80_learning_part2.php';
require __DIR__ . '/lib/v80_learning_http.php';

exit(audit_summary($state, 'V80_LEARNING'));
