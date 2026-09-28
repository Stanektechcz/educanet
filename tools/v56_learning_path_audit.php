<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v56 audit: jedna cesta učení (teorie → test → projekt → odevzdání), menu, materiály, výsledky, profil.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
// v58: audit běží v dočasném úložišti (nikdy ne v ostrých datech žáků) a hodiny s kódem ověřuje
// na pevném vyučovacím dni s kontrolovaným vstupem – výsledek nezávisí na tom, jaký je dnes den.
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v56');

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require $root . '/runtime_content.php';
require_once $root . '/tutorial_v52.php';
require_once $root . '/tutorial_v52_views.php';
require_once $root . '/intake_v51.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/points_v53.php';
require_once $root . '/session_v53.php';
require_once $root . '/student_v55.php';
require_once $root . '/student_v55_views.php';
require_once $root . '/learning_v56.php';
require_once $root . '/learning_v56_views.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);
$css = $read('assets/learning-v56.css');
$js = $read('assets/learning-v56.js');
$views = $read('learning_v56_views.php');
$schoolYear = require $root . '/school_year.php';
$runtime = runtime_content_load_classes(array_keys($modules));
$nextLessons = $runtime['nextLessons'];
$extendedLessons = $runtime['extendedLessons'];

// --- Menu: pět položek a dropdown (přímé volání se skutečnými daty) ---
$nav = v55_primary_nav('class_2a', 'dashboard');
$labels = array_map(static fn(array $i): string => (string)$i['label'], $nav);
// v57 přidává šestou položku Linux Lab (terminál a třídní závody); učební jádro zůstává pět položek.
$check('menu má pět učebních položek + Linux Lab (' . implode(', ', $labels) . ')', count($nav) === 6 && in_array($labels[5] ?? '', ['Linux Lab', 'Závod!'], true));
$check('menu obsahuje dnešní hodinu, materiály, kalendář, výsledky a profil',
    array_slice($labels, 0, 5) === ['Dnešní hodina', 'Materiály', 'Kalendář', 'Výsledky', 'Profil']);
$check('dropdown je ovládaný klávesnicí (JS, bez běhového prostředí prohlížeče)', str_contains($js, 'ArrowDown') && str_contains($js, 'Escape') && str_contains($js, 'aria-expanded'), false);
$check('dropdown má moderní vzhled a animaci', str_contains($css, '.v56-menu-panel') && str_contains($css, 'transition') && str_contains($css, 'box-shadow'), false);
$check('dropdown se zavře klikem mimo (JS)', str_contains($js, "document.addEventListener('click'"), false);

// --- Fáze a jejich pořadí (přímá volání) ---
$meta = v56_phase_meta();
$check('fáze jsou teorie → test → projekt → odevzdání', array_keys($meta) === ['theory', 'test', 'project', 'submit']);
$check('na test stačí 60 %', V56_TEST_PASS_PERCENT === 60);
$check('další fáze se odemyká až po předchozí (zdrojová kontrola šablony)', str_contains($views, "\$phase['state'] !== 'locked'"), false);

// --- Funkční průchod jednou lekcí (kontrolovaný vstup, ověřeno na každém kroku) ---
$classId = 'class_2a';
$key = '__v56audit__';
$no = v56_current_lesson_number($classId, $schoolYear);
$bundle = v56_lesson_bundle($classId, $modules[$classId], $no, $nextLessons, $extendedLessons);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('nová lekce začíná teorií', (string)$lessonState['current'] === 'theory' && (int)$lessonState['done'] === 0);
foreach (array_keys((array)$bundle['topics']) as $t) v56_mark_theory($classId, $key, $no, (string)$t);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('po teorii následuje test', (string)$lessonState['current'] === 'test');
$wrong = [];
foreach ((array)$bundle['questions'] as $i => $q) $wrong[(string)$i] = '__';
$res = v56_submit_test($classId, $key, $no, (array)$bundle['questions'], $wrong);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('neúspěšný test nepustí dál', empty($res['passed']) && (string)$lessonState['current'] === 'test');
$right = [];
foreach ((array)$bundle['questions'] as $i => $q) $right[(string)$i] = (string)$q['correct'];
$res = v56_submit_test($classId, $key, $no, (array)$bundle['questions'], $right);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('úspěšný test odemkne projekt', !empty($res['passed']) && (string)$lessonState['current'] === 'project');
foreach (array_keys((array)$bundle['steps']) as $i) v56_toggle_project_step($classId, $key, $no, (int)$i, true);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('po projektu následuje odevzdání', (string)$lessonState['current'] === 'submit');
v56_save_submit($classId, $key, $no, 'test', '', true);
$lessonState = v56_lesson_state($classId, $key, $bundle);
$check('po odevzdání je lekce hotová', $lessonState['complete'] && (int)$lessonState['percent'] === 100);
$check('XP se přičítá za každou fázi', V56_XP['theory'] > 0 && V56_XP['test'] > 0 && V56_XP['project'] > 0 && V56_XP['submit'] > 0);
$check('dnešní hodina i lekce používají stejnou stránku (zdrojová kontrola)', substr_count(edu_app_source(), 'v56_render_lesson(') === 2, false);
$check('jediné Pokračovat vychází z v56 (zdrojová kontrola)', str_contains($read('student_v55_views.php'), 'v56_next_step('), false);

// --- Materiály (přímá volání) ---
$idx = v56_materials_index($classId, $modules[$classId], $nextLessons, $extendedLessons, $schoolYear);
$check('materiály obsahují všech 28 lekcí', count($idx) === 28);
$check('budoucí lekce jsou zamčené', empty($idx[28]['available']) && !empty($idx[1]['available']));
$check('materiály umí hledat (JS)', str_contains($js, 'data-v56-filter') && str_contains($views, 'data-v56-item'), false);
$check('lekce z materiálů vede na stejnou stránku (zdrojová kontrola)', str_contains($views, 'v56_lesson_url('), false);

// --- HTTP: skutečné odpovědi serveru (menu, přesměrování, materiály, výsledky, profil, CSRF) ---
audit_prewarm_accounts($modules);
$teacherKey = getenv('EDUCANET_TEACHER_EXPORT_KEY') ?: 'audit-v56-teacher-key';
$directoryName = null;
foreach (student_directory() as $row) {
    if (is_array($row) && (string)($row['class_id'] ?? '') === 'class_2a') {
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        if ($label !== '') { $directoryName = $label; break; }
    }
}
// Učitelská záložka Hodina počítá „Kde je“/„Teď na teorii“ z otevřené hodiny druhu work na
// SKUTEČNÉ dnešní datum (session_v53_teacher.php volá date('Y-m-d') napevno, bez parametru) –
// aby kontrola nezávisela na tom, kdy se audit spustí, otevřeme hodinu na dnešek sami (dočasné úložiště).
edu_audit_open_sessions($modules, $schoolYear, date('Y-m-d'));
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey,
]);
try {
    foreach (['assets/learning-v56.css', 'assets/learning-v56.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }
    $sw = $harness->request('GET', '/sw.js');
    $check('PWA předcachuje v56', $sw['status'] === 200 && str_contains((string)$sw['body'], 'learning-v56.css') && str_contains((string)$sw['body'], 'learning-v56.js'));

    $login = audit_login_student($harness, 'class_2a', 'Audit Student 56');
    $dashBody = (string)$login['response']['body'];
    $check('přehled se vykreslí bez chyb', audit_response_clean($login['response']));

    $redirectMap = ['course' => 'lekce', 'topics' => 'temata', 'tools' => 'programy', 'knowledgebase' => 'temata'];
    foreach ($redirectMap as $legacy => $section) {
        $resp = $harness->request('GET', '/?view=' . $legacy, [], ['follow_redirects' => false]);
        $loc = (string)($resp['headers']['Location'] ?? '');
        $check('starý pohled ' . $legacy . ' přesměruje do materiálů (sekce ' . $section . ')',
            in_array($resp['status'], [301, 302, 303, 307, 308], true) && str_contains($loc, '?view=materialy') && str_contains($loc, 'sekce=' . $section));
    }

    $check('v56 assety jsou připojené na přehledu', str_contains($dashBody, 'assets/learning-v56.css') && str_contains($dashBody, 'assets/learning-v56.js'));
    $check('odhlášení je v účtovém menu', str_contains($dashBody, 'data-v56-menu-panel') && str_contains($dashBody, 'logout_class'));

    $materials = $harness->request('GET', '/?view=materialy');
    $check('materiály se vykreslí bez chyb', audit_response_clean($materials));
    $check('materiály mají lekce, témata i programy', str_contains((string)$materials['body'], 'Témata') && str_contains((string)$materials['body'], 'Programy a zkratky'));

    $vysledky = $harness->request('GET', '/?view=vysledky');
    $check('výsledky se vykreslí bez chyb', audit_response_clean($vysledky));
    $check('výsledky ukazují známky, body i postup', str_contains((string)$vysledky['body'], 'Hodnocení od učitele') && str_contains((string)$vysledky['body'], 'Postup v lekcích'));

    if ($directoryName !== null) {
        $profileLogin = audit_login_student($harness, 'class_2a', $directoryName);
        // v60: profil má nové záložky (profile_v60_views.php) – XP graf/statistiky jsou na
        // výchozí záložce Přehled, sbírka odznaků na záložce Odznaky (dřív vše na jedné stránce).
        $profile = $harness->request('GET', '/?view=profile');
        $profileBody = (string)$profile['body'];
        $check('profil se vykreslí bez chyb', audit_response_clean($profile));
        $check('profil má XP graf', str_contains($profileBody, 'p60-chart') && str_contains($profileBody, '<svg'));
        $check('profil má levely, body i achievementy', str_contains($profileBody, 'p60-stat-grid') && str_contains($profileBody, 'achievement'));
        $profileBadges = $harness->request('GET', '/?view=profile&tab=odznaky');
        $profileBadgesBody = (string)$profileBadges['body'];
        $check('profil má odznaky', str_contains($profileBadgesBody, 'id="odznaky"'));
        $check('XP graf kreslí sloupce jako SVG obdélníky, ne procenty', (bool)preg_match('/<rect[^>]+height="\d+"/', $profileBody));
        $check('prázdný XP graf má vysvětlení (nový žák bez XP)', str_contains($profileBody, 'p60-chart-empty') && str_contains($profileBody, 'Zatím nemáš žádné XP'));
        // Znovu přihlásíme syntetického žáka, aby zbylé kontroly (CSRF) neběžely nad rolí z adresáře.
        audit_login_student($harness, 'class_2a', 'Audit Student 56');
    } else {
        foreach (['profil se vykreslí bez chyb', 'profil má XP graf', 'profil má levely, body i achievementy', 'profil má odznaky', 'XP graf kreslí sloupce v pixelech, ne procenty', 'prázdný XP graf má vysvětlení (nový žák bez XP)'] as $skipped) {
            $check($skipped . ' (přeskočeno – adresář 2.A je prázdný)', false);
        }
    }

    $teacherLogin = audit_login_teacher($harness, $teacherKey);
    $check('učitel se přihlásí učitelským klíčem', audit_response_clean($teacherLogin['response']));
    $sessionTab = $harness->request('GET', '/teacher.php', ['tab' => 'session', 'class' => 'class_2a']);
    $check('učitel vidí, v jaké fázi žák je, a souhrn fází třídy', audit_response_clean($sessionTab) && str_contains((string)$sessionTab['body'], 'Kde je') && str_contains((string)$sessionTab['body'], 'Teď na teorii'));
    $teachTab = $harness->request('GET', '/teacher.php', ['tab' => 'teach', 'class' => 'class_2a']);
    $check('režim hodiny používá název lekce z v56 a ukazuje čtyři fáze', audit_response_clean($teachTab) && str_contains((string)$teachTab['body'], 'v56-teach-path') && str_contains((string)$teachTab['body'], 'Teorie → test → projekt → odevzdání'));

    $noCsrf = $harness->request('POST', '/', ['action' => 'v56_theory_done']);
    $check('POST v56_theory_done bez CSRF je odmítnut (419)', $noCsrf['status'] === 419);
} finally {
    $harness->stop();
}

// --- Výsledky (zdrojová kontrola napojení na body – bez reálného HTTP kontextu učitelského hodnocení) ---
$check('výsledky čerpají body z peněženky (zdrojová kontrola)', str_contains($views, 'pts53_balance'), false);

// --- Profil: dlaždice a responzivita (čistě CSS, bez běhového layoutu prohlížeče) ---
$check('dlaždice profilu se sbalí na úzký displej', str_contains($css, '.v56-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px') && str_contains($css, '.v56-achievements { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px'), false);
$check('horní lišta zůstane v řádku i na mobilu', str_contains($css, '.topbar .topbar-right.v55-right { flex-direction: row;') && str_contains($css, 'flex-direction: row !important'), false);
$check('na mobilu zůstane značka školy', str_contains($css, '.topbar .brand .brand-mark'), false);

// --- Učitelské rozhraní: zbylé implementační detaily (zdrojová kontrola) ---
$teacherFile = $read('session_v53_teacher.php');
$check('třídy jsou seřazené podle rozvrhu dne (zdrojová kontrola)', str_contains($teacherFile, 'uksort($byTime') && str_contains($teacherFile, 'foreach ($byTime as $cid'), false);
$check('učitel má obsah lekcí ze stejného zdroje jako žák (zdrojová kontrola)', str_contains($read('teacher.php'), 'runtime_content_load_classes(array_keys($modules))') && str_contains($read('teacher.php'), '$teacherRuntime[\'extendedLessons\']'), false);

// --- Obsah pro dvouhodinové bloky vyučovacího dne (pevné datum, reálná data tříd) ---
$today = EDU_AUDIT_TEACHING_DATE;
edu_audit_open_sessions($modules, $schoolYear, $today);
$ready = 0;
foreach ($modules as $cid => $module) {
    $cid = (string)$cid;
    $row = sess53_for_class_date($cid, $today);
    if (!is_array($row)) continue;
    $ready++;
    if ((string)$row['kind'] === 'intake') { $check((string)$module['name'] . ': seznamovací dotazník je připravený', trim((string)$row['code']) !== ''); continue; }
    $n = max(1, (int)$row['lesson_number']);
    $b = v56_lesson_bundle($cid, $module, $n, $nextLessons, $extendedLessons);
    $minutes = 0;
    foreach ((array)$b['steps'] as $st) if (preg_match('/(\d+)/', (string)$st['time'], $m)) $minutes += (int)$m[1];
    $ok = count((array)$b['topics']) >= 3 && count((array)$b['questions']) >= 5 && count((array)$b['steps']) >= 3 && $minutes >= 30 && $minutes <= 90 && count((array)$b['tools']) >= 2;
    $check((string)$module['name'] . ': materiály na dvouhodinovku (' . count((array)$b['topics']) . ' témat, ' . count((array)$b['questions']) . ' otázek, ' . count((array)$b['steps']) . ' kroků / ' . $minutes . ' min)', $ok);
    $check((string)$module['name'] . ': zadání učitele = kroky projektu', count((array)$row['tasks']) === count((array)$b['steps']));
}
$check('na vyučovací den ' . $today . ' jsou hodiny pro všechny třídy (' . $ready . ')', $ready === count($modules));

// --- Responzivita (čistě CSS) ---
$check('rozvržení je responzivní', substr_count($css, '@media') >= 4 && str_contains($css, 'max-width: 900px') && str_contains($css, 'max-width: 720px'), false);
$check('lišta se nepřekrývá', str_contains($css, '.topbar .main-menu.v55-nav') && str_contains($css, 'flex: 0 0 100%'), false);
$check('žádná duplicitní drobečková lišta', str_contains($css, 'body.view-lekce .v506-compass'), false);

// --- Úklid testovacích dat (dočasné úložiště, ne ostrá storage/) ---
$all = load_php_json(v56_progress_path());
unset($all[v56_progress_key($classId, $key)]);
save_php_json_map(v56_progress_path(), $all);

exit(audit_summary($state, 'V56_LEARNING_PATH'));
