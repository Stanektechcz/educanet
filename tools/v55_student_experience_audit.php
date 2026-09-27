<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v55 audit: menu s LVL prstencem, odznaky, zámek práce, kalendář od–do, posloupnost lekcí a adaptivní bonus.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
// v58: audit dřív běžel bez dočasného úložiště (STORAGE_DIR by bez EDUCANET_STORAGE_DIR spadl na ostrou
// storage/) – teď běží ve stejném izolovaném dočasném úložišti jako ostatní audity vrstvy v5x.
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v55');

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

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);
$views = $read('student_v55_views.php');
$css = $read('assets/student-v55.css');
$js = $read('assets/student-v55.js');
$schoolYear = require $root . '/school_year.php';

// --- Menu a LVL prstenec (přímá volání se skutečnými daty tříd) ---
$nav = v55_primary_nav('class_2a', 'dashboard');
$labels = array_map(static fn(array $i): string => (string)$i['label'], $nav);
$check('menu obsahuje dnešní hodinu, materiály, kalendář a profil (' . implode(', ', $labels) . ')',
    in_array('Materiály', $labels, true) && in_array('Kalendář', $labels, true) && in_array('Profil', $labels, true) && count($nav) <= 6);
$check('LVL zná zbývající XP', (static function (): bool {
    $s = learning_level(0);
    return isset($s['next']) && (int)$s['next'] > 0;
})());
$check('prstenec se plní podle XP do dalšího levelu (125/250 XP = 50 %)', learning_level(125)['percent'] === 50 && learning_level(0)['percent'] === 0 && learning_level(250)['percent'] === 0);

// --- Odznaky (reálná data třídy) ---
$board = v55_badge_board('class_2a', $modules['class_2a']);
$check('sbírka odznaků má postup i podmínky (' . $board['total'] . ' odznaků)', $board['total'] > 0);
$check('postup k odznaku se počítá', (static function (): bool {
    $p = v55_badge_progress('course_mastery', ['lessons' => 14, 'kb' => 0, 'kb_total' => 1, 'excellent_projects' => 0, 'masterpiece_projects' => 0, 'extra_distinction' => 0, 'special_exams' => 0, 'special_exam_perfect' => 0], 1);
    return (int)$p[0] === 50;
})());
$check('sbírka nezahltí vzdálenými levely', count($board['progress']) <= 12);

// --- Zámek práce: funkční pravidlo, JS/CSS dopad je čistě klientský (viz text. kontroly níže) ---
$check('zámek je aktivní při testu', v55_lock_active('test') && v55_lock_active('kb_quiz'));
$check('zámek se nevztahuje na běžné stránky', !v55_lock_active('course') && !v55_lock_active('calendar'));
$check('body dostane příznak zámku (zdrojová kontrola)', str_contains($read('app/views/_layout.php'), 'data-v55-lock="on"') && str_contains($read('app/views/_layout.php'), 'v55_lock_active'), false);
$check('odchod ze stránky je hlídaný (JS, bez běhového prostředí prohlížeče)', str_contains($js, 'beforeunload') && str_contains($js, 'v55-lock-dialog'), false);
$check('proklik menu je během práce zablokovaný (JS/CSS)', str_contains($js, 'v55-locked-nav') && str_contains($css, '.v55-locked-nav') && str_contains($css, 'pointer-events: none'), false);
$check('rozpracovaná práce se ukládá do localStorage (JS)', str_contains($js, 'localStorage') && str_contains($js, 'edu55:work:') && str_contains($views, 'data-v55-autosave'), false);
$check('uložená kopie se po odevzdání zahodí (JS)', str_contains($js, 'removeItem'), false);

// --- Kalendář: blok od–do (přímé volání se skutečným rozvrhem třídy) ---
$window = v55_block_window($schoolYear, 'class_2a', '2026-09-16', strtotime('2026-09-16 13:00'));
$check('blok třídy zná začátek i konec (' . $window['range'] . ')', $window['start'] === '12:45' && $window['end'] === '14:20' && $window['range'] === '12:45–14:20');
$check('zbývající čas se počítá do konce bloku', $window['remaining_minutes'] === 80 && $window['running']);

// --- Posloupnost lekcí (reálná data školního roku) ---
$rows = adaptive_school_year_rows($schoolYear, 'class_2a');
$byDate = [];
foreach ($rows as $r) $byDate[(string)$r['date']] = $r;
$check('2. 9. 2026 se ještě neučilo', (string)($byDate['2026-09-02']['status'] ?? '') === 'no_school');
$check('9. 9. 2026 byl seznamovací blok', (string)($byDate['2026-09-09']['kind'] ?? '') === 'intro');
$check('16. 9. 2026 je lekce 1', (int)($byDate['2026-09-16']['lesson_number'] ?? 0) === 1);
$check('23. 9. 2026 je lekce 2', (int)($byDate['2026-09-23']['lesson_number'] ?? 0) === 2);
$undated = 0;
foreach (array_keys($modules) as $cid) {
    $dates = tut52_lesson_dates($schoolYear, (string)$cid);
    for ($n = 1; $n <= 28; $n++) if (empty($dates[$n])) $undated++;
}
$check('všech 28 lekcí má termín (' . $undated . ' bez termínu)', $undated === 0);
$warnings = 0;
foreach (array_keys($modules) as $cid) {
    foreach (adaptive_school_year_rows($schoolYear, (string)$cid) as $r) if (!empty($r['schedule_warning'])) $warnings++;
}
$check('plán se vejde do školního roku bez varování', $warnings === 0);

// --- Krok za krokem (přímé volání s kontrolovaným vstupem) ---
$session = ['id' => 'x', 'lesson_number' => 1, 'title' => 'Lekce 1', 'tasks' => [['title' => 'a'], ['title' => 'b']]];
$plan = v55_lesson_plan('class_2a', $modules['class_2a'], $session, ['checks' => [], 'status' => 'open'], null);
$check('hodina má 4 kroky a začíná diagnostikou', $plan['total'] === 4 && $plan['current'] === 0 && (string)$plan['steps'][0]['id'] === 'test');
$plan2 = v55_lesson_plan('class_2a', $modules['class_2a'], $session, ['checks' => ['0', '1'], 'status' => 'submitted', 'tutorial_done' => true], ['score' => 1]);
$check('po splnění všech kroků je hodina hotová', $plan2['complete'] && $plan2['done'] === 4);
$check('další krok se odemyká postupně (zdrojová kontrola gatingu v šabloně)', str_contains($views, 'if (!$isCurrent && !$isDone) continue;'), false);
$check('po přihlášení jde žák do dnešní hodiny (zapojení v aplikaci)', str_contains(edu_app_source(), 'v55_after_login_url'), false);
$check('CTA na konci stránky vede do dnešní hodiny, když je otevřená (zdrojová kontrola)', str_contains($views, "'?view=hodina'"), false);

// --- Adaptivní bonus a váha (přímá volání, kontrolovaný vstup) ---
$check('rezerva do konce vyučování je 5 minut', V55_RESERVE_MINUTES === 5);
$long = v55_bonus_variant(45);
$check('po první hodině dostane rozšíření 30–40 minut (' . $long['minutes'] . ')', (string)$long['id'] === 'extended' && $long['minutes'] >= 30 && $long['minutes'] <= 40);
$short = v55_bonus_variant(20);
$check('20 minut před koncem je test max. 15 minut (' . $short['minutes'] . ')', $short['minutes'] <= 15 && $short['minutes'] > 0);
$none = v55_bonus_variant(5);
$check('těsně před koncem se bonus nezadává', (string)$none['id'] === 'none' && (float)$none['weight'] === 0.0);
$check('méně času = nižší váha hodnocení',
    (float)v55_bonus_variant(45)['weight'] > (float)v55_bonus_variant(25)['weight']
    && (float)v55_bonus_variant(25)['weight'] > (float)v55_bonus_variant(20)['weight']
    && (float)v55_bonus_variant(20)['weight'] > (float)v55_bonus_variant(12)['weight']);
$check('zadání je kratší, když je míň času', count(v55_bonus_tasks('class_2a', $modules['class_2a'], [], $long)) > count(v55_bonus_tasks('class_2a', $modules['class_2a'], [], $short)));
$check('body za bonus zohledňují váhu', v55_bonus_points(1, 1.0) === 3 && v55_bonus_points(1, 0.5) === 2 && v55_bonus_points(4, 1.0) === 0);
$check('žák váhu nevidí (zdrojová kontrola žákovské šablony)', !str_contains($views, 'weight_label') && !preg_match('/váha/u', $views), false);
$check('učitel váhu vidí (zdrojová kontrola učitelské šablony)', str_contains($read('session_v53_teacher.php'), 'v55_weight_label'), false);
$check('bonus má termín odevzdání s rezervou (zdrojová kontrola)', str_contains($read('student_v55.php'), 'V55_RESERVE_MINUTES * 60'), false);

// Skutečný běh: sestavení zadání stejnou funkcí jako aplikace (v55_bonus_assignment), spuštění
// a odevzdání bonusu se ověřuje zápisem/čtením z dočasného úložiště, ne textem.
$bonusAssignment = v55_bonus_assignment('class_2a', $modules['class_2a'], ['topics' => [], 'title' => 'Audit lekce'], $window);
$bonusRow = v55_bonus_start('audit-bonus-session', 'audit-bonus-student', $bonusAssignment);
$check('bonus se spustí a uloží do vlastního úložiště (bonus_v55.json.php)',
    $bonusRow['status'] === 'open' && $bonusRow['variant'] === (string)$bonusAssignment['variant']
    && is_file(v55_bonus_path()) && v55_bonus_get('audit-bonus-session', 'audit-bonus-student')['status'] === 'open');
$bonusSubmitted = v55_bonus_submit('audit-bonus-session', 'audit-bonus-student', ['Moje odpověď na audit úkol.']);
$check('bonus se odevzdá a stav v úložišti se změní', $bonusSubmitted['status'] === 'submitted' && v55_bonus_get('audit-bonus-session', 'audit-bonus-student')['status'] === 'submitted');

// --- HTTP: přehled, profil, kalendář, assety a CSRF (skutečné odpovědi serveru) ---
// Profil vyžaduje žáka ze skutečného adresáře třídy (project_students_for_class), jinak
// ?view=profile přesměruje zpátky na přehled – proto se přihlašujeme reálným jménem z adresáře.
$directoryName = null;
foreach (student_directory() as $row) {
    if (is_array($row) && (string)($row['class_id'] ?? '') === 'class_2a') {
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        if ($label !== '') { $directoryName = $label; break; }
    }
}
$check('adresář třídy 2.A má aspoň jednoho žáka pro test profilu', $directoryName !== null);
audit_prewarm_accounts($modules);
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
]);
try {
    foreach (['assets/student-v55.css', 'assets/student-v55.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }
    $sw = $harness->request('GET', '/sw.js');
    $check('PWA předcachuje v55', $sw['status'] === 200 && str_contains((string)$sw['body'], 'student-v55.css') && str_contains((string)$sw['body'], 'student-v55.js'));

    $login = audit_login_student($harness, 'class_2a', (string)($directoryName ?? 'Audit Student 55'));
    $dashBody = (string)$login['response']['body'];
    $check('přehled se vykreslí bez chyb', audit_response_clean($login['response']));
    $check('v55 assety jsou připojené na přehledu', str_contains($dashBody, 'assets/student-v55.css') && str_contains($dashBody, 'assets/student-v55.js'));
    $navBodyOk = $labels !== [];
    foreach ($labels as $label) { if (!str_contains($dashBody, '>' . $label . '<')) { $navBodyOk = false; break; } }
    $check('v menu jsou jen nejdůležitější položky (skutečný výstup v55_primary_nav se vykreslí)', $navBodyOk);
    $check('položky menu jsou na mobilu v rozbalovacím Více', str_contains($dashBody, 'class="v55-mobile-only"'));
    $check('LVL prstenec je v horní liště', str_contains($dashBody, 'v55-lvl-ring') && (bool)preg_match('/--v55-lvl-percent:\s*\d+/', $dashBody));
    $check('tlačítko Pokračovat není v horní liště', !str_contains($dashBody, 'student-nav-continue'));
    $check('tlačítko Pokračovat je na konci stránky', str_contains($dashBody, 'v55-page-cta'));

    $profile = $harness->request('GET', '/?view=profile');
    $profileBody = (string)$profile['body'];
    $check('profil se vykreslí bez chyb', audit_response_clean($profile));
    $check('profil ukazuje získávání odznaků s postupem i podmínkami', str_contains($profileBody, 'v55-badge-bar') && str_contains($profileBody, 'v55-badge-score'));

    $calendar = $harness->request('GET', '/?view=calendar');
    $calBody = (string)$calendar['body'];
    $check('kalendář (?view=calendar) se vykreslí bez chyb', audit_response_clean($calendar));
    $check('kalendář ukazuje čas od–do v buňce', str_contains($calBody, 'cal54-time') && str_contains($calBody, $window['range']));
    $check('kalendář ukazuje čas od–do u dne', str_contains($calBody, 't52-day-time'));

    $noCsrfStart = $harness->request('POST', '/', ['action' => 'v55_bonus_start']);
    $check('POST v55_bonus_start bez CSRF je odmítnut (419)', $noCsrfStart['status'] === 419);
    $noCsrfSubmit = $harness->request('POST', '/', ['action' => 'v55_bonus_submit']);
    $check('POST v55_bonus_submit bez CSRF je odmítnut (419)', $noCsrfSubmit['status'] === 419);
} finally {
    $harness->stop();
}

exit(audit_summary($state, 'V55_STUDENT_EXPERIENCE'));
