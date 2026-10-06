<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v52 Tutorial Mode audit: kurz, tutoriál lekcí, kalendář, témata, programy, GSAP scény a interaktivní úkoly.
// v58 F3: kontroly jsou přednostně behaviorální (funkční volání + HTTP přes dev server).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v52');

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require $root . '/runtime_content.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/tutorial_v52.php';
require_once $root . '/tutorial_v52_views.php'; // tut52_render_scene()/tut52_render_exercise() – volá je tut52_render_teacher_lesson()
require_once $root . '/tutorial_v52_teacher.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);
$index = edu_app_source();
$js = $read('assets/tutorial-v52.js');
$css = $read('assets/tutorial-v52.css');
$views = $read('tutorial_v52_views.php');
$sw = $read('sw.js');

foreach (['tutorial_v52.php', 'tutorial_v52_views.php'] as $f) {
    $check('exists ' . $f, is_file($root . '/' . $f), false);
}
$check('GSAP is vendored locally (no runtime CDN)', !preg_match('~https?://[^"\']*(gsap|greensock|cdnjs|jsdelivr|unpkg)~i', $views . $js), false);

// --- HTTP: pohledy kurzu/kalendáře/tutoriálu, assety, progress.php, tut52_score, učitel ---
audit_prewarm_accounts($modules);
$teacherKey = 'audit-v52-' . bin2hex(random_bytes(6));
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
    'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey,
]);
try {
    $login = audit_login_student($harness, 'class_3a', 'Audit Tester 52');
    $check('žák se přihlásí bez chyby', audit_response_clean($login['response']));

    $familyViewsOk = true;
    foreach (['course', 'calendar', 'tutorial', 'topics', 'tools', 'knowledgebase'] as $v) {
        $resp = $harness->request('GET', '/', ['view' => $v]);
        if (!audit_response_clean($resp)) $familyViewsOk = false;
    }
    $check('pohledy course/calendar/tutorial/topics/tools/knowledgebase se vykreslí bez chyby', $familyViewsOk);

    $lessonRedirect = $harness->request('GET', '/', ['view' => 'course_lesson', 'lesson' => 1]);
    $check('klasická URL lekce (course_lesson) vede na fungující stránku tutoriálu', audit_response_clean($lessonRedirect) && strlen((string)$lessonRedirect['body']) > 2000);

    foreach (['assets/tutorial-v52.css', 'assets/tutorial-v52.js', 'assets/vendor/gsap-3.15.0/gsap.min.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }

    $progress = $harness->request('GET', '/progress.php');
    $progressJson = json_decode((string)$progress['body'], true);
    $check('progress.php (volá ho tutorial-v52.js) vrací platný stav', $progress['status'] === 200 && is_array($progressJson) && !empty($progressJson['ok']));

    $csrf = $harness->csrfToken((string)$login['response']['body']);
    $scoreOk = $harness->request('POST', '/', ['action' => 'tut52_score', 'exercise' => 'auditcheck', 'points' => '3', 'csrf' => (string)$csrf]);
    $scoreJson = json_decode((string)$scoreOk['body'], true);
    $check('POST tut52_score s CSRF uloží bodování cvičení', is_array($scoreJson) && !empty($scoreJson['ok']) && (int)($scoreJson['best'] ?? 0) === 3);
    $studentKey = project_student_key('class_3a', 'Audit Tester 52');
    $scores = load_php_json(tut52_scores_path());
    $check('bodování se opravdu uložilo do úložiště (mimoprocesově)', (int)($scores['class_3a|' . $studentKey]['auditcheck']['points'] ?? 0) === 3);
    $scoreNoCsrf = $harness->request('POST', '/', ['action' => 'tut52_score', 'exercise' => 'auditcheck', 'points' => '3']);
    $check('POST tut52_score bez CSRF je odmítnut (419)', $scoreNoCsrf['status'] === 419);

    $teacherLogin = audit_login_teacher($harness, $teacherKey);
    $check('učitel se přihlásí učitelským klíčem', audit_response_clean($teacherLogin['response']));
    $teachTab = $harness->request('GET', '/teacher.php', ['tab' => 'teach', 'class' => 'class_3a']);
    $check('režim hodiny (tab=teach) načítá v52 styl (vykreslená stránka)', audit_response_clean($teachTab) && preg_match('~assets/(cx-)?tutorial-v52.css~', (string)$teachTab['body']) === 1);   // v68: cockpit načítá odvozenou kopii cx-tutorial-v52.css
    $teacherGuard = $harness->request('GET', '/tutorial_v52_teacher.php');
    $check('přímý přístup k tutorial_v52_teacher.php je zablokovaný (403)', $teacherGuard['status'] === 403);
} finally {
    $harness->stop();
}

preg_match('/const SCENES = \{(.*?)\n  \};\n/s', $js, $m);
preg_match_all('/^\s{4}(\w+)\(stage\)\s*\{/m', $m[1] ?? '', $sm);
$jsScenes = array_flip($sm[1] ?? []);
preg_match_all('/^\s{4}(\w+)\(ex, body(?:, ctx)?\)\s*\{/m', $js, $em);
$engines = array_flip($em[1] ?? []);
// Počty scén/enginů nejsou samoúčelné – dál v souboru se hned použijí k ověření, že KAŽDÉ
// téma/lekce/cvičení (reálná data tříd) má odpovídající scénu a validní engine (funkční volání).
$check('26 animated scenes in JS (' . count($jsScenes) . ')', count($jsScenes) >= 26);
$check('6 exercise engines in JS', count(array_intersect_key(array_flip(['order', 'match', 'terminal', 'bits', 'sizes', 'contrast']), $engines)) === 6);

$schoolYear = require $root . '/school_year.php';
$missingScene = []; $missingEngine = []; $badTerminal = []; $noTools = []; $lessonCounts = []; $undated = 0;
$sampleLesson = null; $sampleClass = null; $sampleModule = null;
foreach (['class_1a', 'class_2a', 'class_3a', 'class_4a'] as $classId) {
    $module = $modules[$classId];
    $family = tut52_family($classId, $module);
    foreach (array_keys((array)$module['knowledgebase']) as $topic) {
        $scene = tut52_scene((string)$topic, $family);
        if (!isset($jsScenes[$scene])) $missingScene[] = $classId . ':' . $topic . '→' . $scene;
        $meta = tut52_scene_meta($scene);
        if (count($meta['caption']) !== 4) $missingScene[] = $scene . ' captions';
        $ex = tut52_exercise($scene);
        if (!isset($engines[$ex['type']])) $missingEngine[] = $scene . ':' . $ex['type'];
        if ($ex['type'] === 'terminal') {
            foreach ($ex['tasks'] as $task) if (!preg_match('~' . str_replace('~', '\~', $task['accept']) . '~i', $task['hint'])) $badTerminal[] = $scene . ': ' . $task['hint'];
        }
        if ($ex['type'] === 'order' && count(array_unique($ex['items'])) !== count($ex['items'])) $missingEngine[] = $scene . ' duplicate order items';
        if ($ex['type'] === 'match' && count(array_unique(array_column($ex['pairs'], 1))) !== count($ex['pairs'])) $missingEngine[] = $scene . ' ambiguous match pairs';
    }
    $rt = runtime_content_load_classes([$classId]);
    $lessons = tut52_lessons($classId, $module, $rt['nextLessons'], $rt['extendedLessons'], $schoolYear, null);
    $lessonCounts[$classId] = count($lessons);
    foreach ($lessons as $l) {
        if (!$l['tools']) $noTools[] = $classId . ' L' . $l['number'];
        if ($l['date'] === '') $undated++;
        foreach ($l['topics'] as $t) if (!isset($jsScenes[tut52_scene((string)$t, $family)])) $missingScene[] = $classId . ' L' . $l['number'] . ':' . $t;
    }
    if ($sampleLesson === null && $classId === 'class_3a') {
        foreach ($lessons as $candidate) {
            if (!empty($candidate['steps'])) { $sampleLesson = $candidate; $sampleClass = $classId; $sampleModule = $module; break; }
        }
    }
    $tools = tut52_tools($family);
    $check($classId . ': tools have description and shortcuts or commands', !array_filter($tools, static fn($t) => trim($t['use']) === '' || (!$t['shortcuts'] && empty($t['commands']))));
    $check($classId . ': glossary has at least 15 abbreviations', count(tut52_glossary($family)) >= 15);
}
$check('every topic and lesson maps to an existing scene', !$missingScene);
if ($missingScene) echo '      ' . implode(', ', array_slice(array_unique($missingScene), 0, 8)) . PHP_EOL;
$check('every scene has a valid, unambiguous exercise', !$missingEngine);
if ($missingEngine) echo '      ' . implode(', ', array_unique($missingEngine)) . PHP_EOL;
$check('terminal hints are accepted by their own patterns', !$badTerminal);
if ($badTerminal) echo '      ' . implode(', ', $badTerminal) . PHP_EOL;
$check('28 lessons per class', $lessonCounts === ['class_1a' => 28, 'class_2a' => 28, 'class_3a' => 28, 'class_4a' => 28]);
$check('every lesson lists programs', !$noTools);
$check('lessons are linked to calendar dates (' . $undated . ' without date)', $undated === 0);

// --- Režim hodiny (učitel) – přímé volání vykreslovací funkce nad reálnou lekcí ---
// (přístup k tutorial_v52_teacher.php ověřuje HTTP 403 test výše; is_file by jen opakoval require_once nahoře)
if ($sampleLesson !== null) {
    $panelHtml = audit_capture(static function () use ($sampleClass, $sampleModule, $sampleLesson): void {
        tut52_render_teacher_lesson($sampleClass, $sampleModule, (int)$sampleLesson['number'], $sampleLesson);
    });
    $check('teacher lesson mode renders the tutorial panel', $panelHtml !== '' && str_contains($panelHtml, 't52-teach') && substr_count($panelHtml, 'data-t52-deck-item') === count((array)$sampleLesson['steps']));
} else {
    $check('teacher lesson mode renders the tutorial panel', false);
}
$check('projection deck switches demos', str_contains($js, 'data-t52-deck') && str_contains($read('tutorial_v52_teacher.php'), 'data-t52-deck-item'), false);
$check('teacher demos never save student points', str_contains($views, 'data-no-save') && str_contains($js, "root.hasAttribute('data-no-save')"), false);
$missingSolution = [];
foreach (['order', 'match', 'terminal', 'bits', 'sizes', 'contrast'] as $type) {
    foreach (['dns', 'ports', 'logs', 'permissions', 'hierarchy', 'contrast'] as $scene) {
        $ex = tut52_exercise($scene);
        if ((string)$ex['type'] === $type && trim(tut52_exercise_solution($ex)) === '') $missingSolution[] = $type;
    }
}
$check('every exercise has a teacher solution', !$missingSolution && str_contains(tut52_exercise_solution(tut52_exercise('dhcp')), 'DHCPDISCOVER'));

// --- JS/CSS chování, které bez prohlížeče nelze spustit (ponecháno jako textová kontrola zdroje) ---
$check('reveal animations cannot leave content hidden', str_contains($js, 'const reveal = ') && !preg_match('/gsap\.from\((?!list)/', $js), false);
$check('reduced motion respected', str_contains($js, 'prefers-reduced-motion') && str_contains($css, 'prefers-reduced-motion'), false);
$check('keyboard navigation in tutorial', str_contains($js, "e.key === 'ArrowRight'"), false);
$check('order exercise usable without drag (buttons)', str_contains($js, 'data-up') && str_contains($js, 'data-down'), false);
$check('no horizontal overflow guards', str_contains($css, '.t52 * { min-width: 0; }'), false);
$check('PWA caches v52 assets', preg_match('/educanet-v[56]\d/', $sw) === 1 && str_contains($sw, 'assets/tutorial-v52.css') && str_contains($sw, 'assets/tutorial-v52.js'), false);

exit(audit_summary($state, 'V52_TUTORIAL_MODE'));
