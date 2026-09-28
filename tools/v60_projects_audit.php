<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · behaviorální audit projektů podle levelu (projects_v60.php + rozsah/role učitele).
 *   php tools/v60_projects_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/.
 * Konec: V60_PROJECTS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-projects')), '/');
require $root . '/bootstrap.php';
require_once $root . '/projects_v60.php';
require_once $root . '/teacher_operations_v46.php';
require_once $root . '/teacher_scope_v59.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

$GLOBALS['modules'] = [
    'class_1a' => ['course_type' => 'graphics'],
    'class_3a' => ['course_type' => 'networks'],
    'class_4a' => ['course_type' => 'networks'],
];
teacher59_all_modules($GLOBALS['modules']);

$classA = 'class_3a';
$classB = 'class_4a';
$studentA = 'class_3a:student:Audit Zak';
$studentB = 'class_3a:student:Druhy Zak';

// --- 1) Katalog: uložení a validace vstupu ---------------------------------------------------
$idOpen = proj60_save('', ['title' => 'Web pro kavárnu', 'reward_type' => 'portfolio', 'min_level' => 3, 'capacity' => 1, 'classes' => [$classA], 'status' => 'open'], [$classA, $classB], 'audit');
$check('proj60_save: platný projekt se uloží', is_string($idOpen));
$check('proj60_save: neplatný typ odměny se odmítne', proj60_save('', ['title' => 'X', 'reward_type' => 'zlato', 'min_level' => 1, 'classes' => [$classA]], [$classA], 'audit') === null);
$check('proj60_save: min_level < 1 se odmítne', proj60_save('', ['title' => 'X', 'reward_type' => 'other', 'min_level' => 0, 'classes' => [$classA]], [$classA], 'audit') === null);
$check('proj60_save: cizí třída mimo dovolené se odmítne', proj60_save('', ['title' => 'X', 'reward_type' => 'other', 'min_level' => 1, 'classes' => ['class_9z']], [$classA], 'audit') === null);

$idKc = proj60_save('', ['title' => 'Placený web', 'reward_type' => 'kc', 'reward_note' => 'dohodou', 'min_level' => 1, 'capacity' => 1, 'classes' => [$classA], 'status' => 'open', 'requires_guardian_consent' => false], [$classA], 'audit');
$check('proj60_save: kc projekt se uloží', is_string($idKc));
$check('proj60_save: u odměny kc je requires_guardian_consent VŽDY vynuceno true (i když formulář poslal false)', (bool)(proj60_item((string)$idKc)['requires_guardian_consent'] ?? false) === true);

$item = proj60_item((string)$idOpen);
$check('proj60_public_view: bez levelu nevrací detail_private', !array_key_exists('detail_private', proj60_public_view($item, false)));
$check('proj60_public_view: s levelem vrací detail_private', array_key_exists('detail_private', proj60_public_view($item, true)));

// --- 2) Přihláška: level, třída, kapacita, termín, duplicita -----------------------------------
$resultLow = proj60_apply($classA, $studentA, (string)$idOpen, 'Chci to zkusit', 1);
$check('proj60_apply: nízký level je odmítnut', $resultLow['ok'] === false && $resultLow['error'] === 'level_too_low');

$resultWrongClass = proj60_apply($classB, $studentA, (string)$idOpen, 'x', 5);
$check('proj60_apply: cizí třída (projekt tam není nabízen) je odmítnuta', $resultWrongClass['ok'] === false && $resultWrongClass['error'] === 'wrong_class');

$resultOk = proj60_apply($classA, $studentA, (string)$idOpen, 'Mám zkušenosti s HTML/CSS.', 5);
$check('proj60_apply: dostatečný level a vlastní třída – přihláška projde', $resultOk['ok'] === true);
$appId = (string)($resultOk['application_id'] ?? '');

$resultDup = proj60_apply($classA, $studentA, (string)$idOpen, 'znovu', 5);
$check('proj60_apply: duplicitní přihláška (stále aktivní) je odmítnuta', $resultDup['ok'] === false && $resultDup['error'] === 'already_applied');

$resultCapacity = proj60_apply($classA, $studentB, (string)$idOpen, 'taky chci', 5);
$check('proj60_apply: druhý žák se přihlásí (kapacita ještě volná, nikdo není approved)', $resultCapacity['ok'] === true);

$check('proj60_decide: schválení bez consentu u projektu bez povinného souhlasu funguje', proj60_decide($appId, 'approved', false, 'ucitel'));
$resultFull = proj60_apply($classA, 'class_3a:student:Treti Zak', (string)$idOpen, 'chci', 9);
$check('proj60_apply: naplněná kapacita (1 approved z 1) odmítne dalšího zájemce', $resultFull['ok'] === false && $resultFull['error'] === 'capacity_full');

$idClosed = proj60_save('', ['title' => 'Uzavřený', 'reward_type' => 'other', 'min_level' => 1, 'classes' => [$classA], 'status' => 'closed'], [$classA], 'audit');
$resultClosed = proj60_apply($classA, $studentA, (string)$idClosed, 'x', 9);
$check('proj60_apply: uzavřený projekt nelze přihlásit', $resultClosed['ok'] === false && $resultClosed['error'] === 'project_unavailable');

$idPast = proj60_save('', ['title' => 'Po termínu', 'reward_type' => 'other', 'min_level' => 1, 'classes' => [$classA], 'status' => 'open', 'deadline' => '2000-01-01'], [$classA], 'audit');
$resultPast = proj60_apply($classA, $studentA, (string)$idPast, 'x', 9);
$check('proj60_apply: projekt po termínu nelze přihlásit', $resultPast['ok'] === false && $resultPast['error'] === 'deadline_passed');

// --- 3) Vrácená přihláška lze poslat znovu -----------------------------------------------------
$idAgain = proj60_save('', ['title' => 'Znovu', 'reward_type' => 'other', 'min_level' => 1, 'capacity' => 2, 'classes' => [$classA], 'status' => 'open'], [$classA], 'audit');
$firstApp = proj60_apply($classA, $studentA, (string)$idAgain, 'x', 9);
$check('proj60_withdraw: cizí žák nemůže stáhnout přihlášku jiného žáka', !proj60_withdraw($classA, $studentB, (string)$firstApp['application_id']));
$check('proj60_withdraw: vlastník přihlášku stáhne', proj60_withdraw($classA, $studentA, (string)$firstApp['application_id']));
$secondApp = proj60_apply($classA, $studentA, (string)$idAgain, 'znovu po stažení', 9);
$check('proj60_apply: po stažení lze podat novou přihlášku na stejný projekt', $secondApp['ok'] === true);

// --- 4) Souhlas zákonného zástupce u placené odměny (kc) ----------------------------------------
$appKc = proj60_apply($classA, $studentA, (string)$idKc, 'chci vydělat', 9);
$check('proj60_apply: přihláška na kc projekt projde (souhlas se řeší až při schválení)', $appKc['ok'] === true);
$check('proj60_decide: schválení kc BEZ potvrzeného souhlasu je odmítnuto', !proj60_decide((string)$appKc['application_id'], 'approved', false, 'ucitel'));
$check('proj60_decide: přihláška zůstává interested (neschválená)', (string)proj60_application((string)$appKc['application_id'])['status'] === 'interested');
$check('proj60_decide: schválení kc S potvrzeným souhlasem projde', proj60_decide((string)$appKc['application_id'], 'approved', true, 'ucitel'));
$approvedRow = proj60_application((string)$appKc['application_id']);
$check('proj60_decide: po schválení je consent_confirmed_by_teacher true', (bool)$approvedRow['consent_confirmed_by_teacher'] === true);
$idKc2 = proj60_save('', ['title' => 'Placený web 2', 'reward_type' => 'kc', 'min_level' => 1, 'capacity' => 1, 'classes' => [$classA], 'status' => 'open'], [$classA], 'audit');
$appReject = proj60_apply($classA, $studentB, (string)$idKc2, 'x', 9);
$check('proj60_decide: zamítnutí funguje i bez consentu', proj60_decide((string)($appReject['application_id'] ?? ''), 'rejected', false, 'ucitel'));

// --- 5) Rozsah učitele: proj60_save/status/decide respektují teacher59_action_policy() -----------
require_once $root . '/teacher_accounts_v59.php';
$teacherId = 't_' . bin2hex(random_bytes(8));
storage_update(teacher59_accounts_path(), static function (array $store) use ($teacherId, $classA): array {
    $store['mode'] = 'accounts';
    $store['version'] = TEACHER59_STORE_VERSION;
    $store['accounts'][$teacherId] = [
        'id' => $teacherId, 'login' => 'audit.teacher.proj', 'display_name' => 'Audit Učitel', 'role' => 'teacher',
        'assignments' => [['class_id' => $classA, 'subject_id' => '*']],
        'password_hash' => password_hash('audit-heslo-123456', PASSWORD_DEFAULT), 'must_change_password' => false,
        'status' => 'active', 'session_version' => 1, 'created_at' => date(DATE_ATOM), 'created_by' => 'audit', 'updated_at' => date(DATE_ATOM),
    ];
    return $store;
});
$_SESSION['teacher59'] = ['id' => $teacherId, 'sv' => 1, 'seen_at' => time(), 'login_at' => time()];
teacher59_reset_cache();
$check('fixture: teacher59_current() vrátí právě vytvořený účet role teacher', (string)(teacher59_current()['role'] ?? '') === 'teacher');

$check('teacher59_guard_post_check: proj60_save do VLASTNÍ třídy je povoleno', teacher59_guard_post_check('proj60_save', ['id' => '', 'classes' => [$classA]]) === null);
$check('teacher59_guard_post_check: proj60_save do CIZÍ třídy je zamítnuto', teacher59_guard_post_check('proj60_save', ['id' => '', 'classes' => [$classB]]) !== null);

$idForeign = proj60_save('', ['title' => 'Cizí', 'reward_type' => 'other', 'min_level' => 1, 'classes' => [$classB], 'status' => 'open'], [$classB], 'admin');
$check('teacher59_guard_post_check: proj60_status cizí položky je zamítnuto', teacher59_guard_post_check('proj60_status', ['id' => (string)$idForeign]) !== null);
$check('teacher59_guard_post_check: proj60_status vlastní položky je povoleno', teacher59_guard_post_check('proj60_status', ['id' => (string)$idOpen]) === null);

$foreignApp = proj60_apply($classB, 'class_4a:student:Cizi Zak', (string)$idForeign, 'x', 9);
$check('teacher59_guard_post_check: proj60_decide o přihlášce k CIZÍMU projektu je zamítnuto', teacher59_guard_post_check('proj60_decide', ['application_id' => (string)$foreignApp['application_id']]) !== null);
$check('teacher59_guard_post_check: proj60_decide o přihlášce k VLASTNÍMU projektu je povoleno', teacher59_guard_post_check('proj60_decide', ['application_id' => $appId]) === null);
$check('teacher59_guard_post_check: neznámá akce je zamítnuta (deny-by-default)', teacher59_guard_post_check('proj60_neco_neznameho', []) !== null);

// --- 6) Role: asistent nemá právo zapisovat (jen 'view'), učitel má 'content.manage' -------------
$check('teacher_action_permission: proj60_save vyžaduje content.manage', teacher_action_permission('proj60_save') === 'content.manage');
$check('role teacher: smí content.manage', teacher_permission('content.manage'));
storage_map_update(teacher59_accounts_path(), 'accounts', static function (?array $accounts) use ($teacherId): array {
    $accounts = is_array($accounts) ? $accounts : [];
    $accounts[$teacherId]['role'] = 'assistant';
    return $accounts;
});
teacher59_reset_cache();
$check('role assistant: nesmí content.manage (jen čte)', !teacher_permission('content.manage'));
$check('role assistant: smí aspoň view (čtení zůstává)', teacher_permission('view'));
storage_map_update(teacher59_accounts_path(), 'accounts', static function (?array $accounts) use ($teacherId): array {
    $accounts = is_array($accounts) ? $accounts : [];
    $accounts[$teacherId]['role'] = 'teacher';
    return $accounts;
});
teacher59_reset_cache();

// --- 7) Log/přehled aplikací nepřenáší žádné osobní údaje navíc (jen class_id+student_key+text) --
$row = proj60_application($appId);
$check('přihláška ukládá jen class_id/student_key/motivaci – žádné jméno klienta ani částku jako číslo', array_keys($row) === ['id', 'project_id', 'class_id', 'student_key', 'motivation', 'status', 'level_at_apply', 'consent_confirmed_by_teacher', 'consent_confirmed_by', 'decided_by', 'at', 'updated_at']);
$check('reward_note je text, žádné číselné pole s částkou v datech projektu', !array_key_exists('amount', $item) && !array_key_exists('reward_kc', $item));

// --- 8) CSRF (stejné API jako v ostatních v60 modulech) ------------------------------------------
$_SESSION['csrf'] = 'ocekavany-token';
$check('CSRF: shodný token projde', hash_equals((string)$_SESSION['csrf'], 'ocekavany-token'));
$check('CSRF: chybějící/jiný token je odmítnut', !hash_equals((string)$_SESSION['csrf'], 'jiny-token'));

exit(audit_summary($state, 'V60_PROJECTS'));
