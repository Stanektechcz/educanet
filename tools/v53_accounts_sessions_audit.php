<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';

// v53 audit: školní účty, hodina s kódem, body a nápovědy.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
// v58: audit běží v dočasném úložišti (nikdy ne v ostrých datech žáků) a hodiny s kódem ověřuje
// na pevném vyučovacím dni s kontrolovaným vstupem – výsledek nezávisí na tom, jaký je dnes den.
require_once __DIR__ . '/lib/audit_storage.php';
$storageDir = edu_audit_temp_storage('v53');

$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require $root . '/runtime_content.php';
require_once $root . '/tutorial_v52.php';
require_once $root . '/intake_v51.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/points_v53.php';
require_once $root . '/session_v53.php';
require_once $root . '/session_v53_teacher.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($root . '/' . $rel);
$index = edu_app_source();
$teacher = $read('teacher.php');

foreach (['points_v53.php', 'tools/v53_provision_accounts.php'] as $f) {
    $check('exists ' . $f, is_file($root . '/' . $f), false);
}

// --- Účty podle jmen ---
$check('e-mail podle jména', acc53_email_local('Jan Novák') === 'jan.novak' && acc53_email_local('Vítězslav Čápek') === 'vitezslav.capek');
$check('víceslovné jméno', acc53_email_local('Marie Anna Dvořáková') === 'marie-anna.dvorakova');
$check('doména školy', str_ends_with(acc53_email('Jan Novák'), '@' . google_workspace_domain()));
$check('kolize e-mailů se řeší číslem', acc53_email('Jan Novák', ['jan.novak@' . google_workspace_domain() => true]) === 'jan.novak2@' . google_workspace_domain());
$check('sdílené heslo demo001 nefunguje (v58 jednorázová hesla)', acc58_login_gate([], null, ACC53_DEFAULT_PASSWORD) !== null && local_password_validate(ACC53_DEFAULT_PASSWORD) !== null);
$check('změna hesla je vynucená po prvním přihlášení', str_contains($index, "acc53_must_change_password()") && str_contains($index, "\$view === 'change_password'") && str_contains($index, "acc53_change_password"), false);
$check('nové heslo prochází validací', (bool)preg_match('/local_password_validate\(\$password\s*[,)]/', $index), false);
$check('účty se zakládají automaticky (zapojení v obou vstupních bodech)', str_contains($index, 'acc53_provision_all($modules)') && str_contains($teacher, 'acc53_provision_all($modules)'), false);

acc53_provision_all($modules); // kontrolovaný vstup: stejné zakládání účtů, jaké spouští index.php
$accounts = local_accounts();
$withClass = array_filter($accounts, static fn($a) => is_array($a) && !empty($a['class_id']));
$check('účty existují pro žáky z adresáře (' . count($withClass) . ')', count($withClass) >= count(student_directory()));
$bad = array_filter($withClass, static fn($a) => !str_ends_with((string)$a['email'], '@' . google_workspace_domain()));
$check('všechny účty mají školní doménu', !$bad);
$plain = array_filter($accounts, static fn($a) => is_array($a) && isset($a['password']));
$check('hesla se ukládají jen jako hash', !$plain && !array_filter($accounts, static fn($a) => is_array($a) && empty($a['password_hash'])));

// --- Body ---
$check('známka 1 = 3 body, 2 = 2 body, 3 = 1 bod', pts53_points_for_grade(1) === 3 && pts53_points_for_grade(2) === 2 && pts53_points_for_grade(3) === 1 && pts53_points_for_grade(4) === 0);
$check('nápověda stojí 2 body', PTS53_HINT_COST === 2);
$check('české skloňování bodů', pts53_points_label(1) === '1 bod' && pts53_points_label(3) === '3 body' && pts53_points_label(5) === '5 bodů');
$hints = pts53_hints('dns');
$check('každý úkol má 3 nápovědy: první zdarma, další za body', count($hints) === 3 && (int)$hints[0]['cost'] === 0 && (int)$hints[1]['cost'] === PTS53_HINT_COST && (int)$hints[2]['cost'] === PTS53_HINT_COST);
$check('text nápovědy nechodí do HTML předem', !str_contains($read('tutorial_v52_views.php'), 'pts53_hints(') && str_contains($read('assets/tutorial-v52.js'), "action: 'pts53_hint'"), false);
$walletBefore = pts53_balance('class_3a', 'audit-wallet-key');
pts53_award('class_3a', 'audit-wallet-key', 'audit:test-event', 5, 'Audit test');
$check('body z úkolů/známky se opravdu připíší do peněženky', pts53_balance('class_3a', 'audit-wallet-key') === $walletBefore + 5);
$check('body z úkolů volají pts53_award (napojení tutoriálu)', str_contains($read('tutorial_v52.php'), 'pts53_award($classId, $studentKey'), false);
$check('známka posílá body žákovi (napojení hodnocení)', str_contains($read('session_v53.php'), 'pts53_award($session[\'class_id\']'), false);

// --- HTTP: vstup kódem, CSRF, přehled, export přístupů, přímý přístup ke knihovnám ---
audit_prewarm_accounts($modules);
$schoolYear = require $root . '/school_year.php';
$today = EDU_AUDIT_TEACHING_DATE;
edu_audit_open_sessions($modules, $schoolYear, $today);
$todaySession = sess53_for_class_date('class_3a', $today);
$harness = Harness::start([
    'EDUCANET_STORAGE_DIR' => $storageDir,
    'EDUCANET_DEV_BYPASS' => '1',
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
]);
try {
    $join = $harness->request('GET', '/?view=join');
    $check('vstup kódem hodiny je veřejný (bez přihlášení)', $join['status'] === 200 && str_contains((string)$join['body'], 'name="code"'));

    $csrf = $harness->csrfToken((string)$join['body']);
    $badCode = $harness->request('POST', '/', ['action' => 'sess53_code', 'code' => 'NEPLATNY', 'csrf' => (string)$csrf]);
    $check('neplatný kód hodiny se odmítne se srozumitelnou hláškou', str_contains((string)$badCode['body'], 'Tento kód hodiny neplatí'));
    if (is_array($todaySession)) {
        $goodCode = $harness->request('POST', '/', ['action' => 'sess53_code', 'code' => (string)$todaySession['code'], 'csrf' => (string)$csrf]);
        $check('platný kód dnešní hodiny se přijme (bez chybové hlášky)', !str_contains((string)$goodCode['body'], 'Tento kód hodiny neplatí'));
    } else {
        $check('platný kód dnešní hodiny se přijme (bez chybové hlášky)', false);
    }
    $noCsrf = $harness->request('POST', '/', ['action' => 'sess53_code', 'code' => 'NEPLATNY']);
    $check('POST sess53_code bez CSRF je odmítnut (419)', $noCsrf['status'] === 419);

    foreach (['accounts_v53.php', 'session_v53.php', 'session_v53_views.php', 'session_v53_teacher.php'] as $lib) {
        $resp = $harness->request('GET', '/' . $lib);
        $check('přímý přístup k ' . $lib . ' je zablokovaný (403)', $resp['status'] === 403);
    }
    foreach (['assets/session-v53.js', 'assets/session-v53.css', 'assets/session-v53-teacher.js'] as $asset) {
        $resp = $harness->request('GET', '/' . $asset);
        $check('asset ' . $asset . ' je dostupný (HTTP 200)', $resp['status'] === 200);
    }

    $login = audit_login_student($harness, 'class_3a', 'Audit Tester 53');
    $check('dnešní hodina je první krok na přehledu', str_contains((string)$login['response']['body'], 'href="?view=hodina"'));
} finally {
    $harness->stop();
}

$teacherTabHtml = audit_capture(static function (): void { sess53_render_teacher_tab($GLOBALS['modules'], 'class_3a'); });
$check('učitelská záložka Hodina se vykreslí', $teacherTabHtml !== '' && str_contains($teacherTabHtml, 'Hodina'));
$check("\$tab==='session' je zapojená v teacher.php", str_contains($teacher, "\$tab==='session'"), false);

// --- Vyučovací den (pevné datum, hodiny otevřené jako v učitelské záložce) ---
$open = 0; $codes = [];
foreach ($modules as $classId => $module) {
    $row = sess53_for_class_date((string)$classId, $today);
    if (!is_array($row)) continue;
    $open++;
    $codes[(string)$row['code']] = ($codes[(string)$row['code']] ?? 0) + 1;
    if ((string)$row['kind'] === 'work') {
        $check((string)$module['name'] . ': samostatná práce má zadání', count((array)$row['tasks']) >= 3);
    } else {
        $check((string)$module['name'] . ': hodina je seznamovací dotazník', (string)$row['kind'] === 'intake');
    }
}
$check('kódy hodin jsou unikátní', !array_filter($codes, static fn(int $n): bool => $n > 1));
$check('na vyučovací den ' . $today . ' jsou otevřené hodiny (' . $open . ')', $open > 0);

exit(audit_summary($state, 'V53_ACCOUNTS_SESSIONS'));
