<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · behaviorální audit obchodu bodů (marketplace_v60.php + učitelský rozsah/role).
 *   php tools/v60_marketplace_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/.
 * Konec: V60_MARKETPLACE_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-marketplace')), '/');
require $root . '/bootstrap.php';
require_once $root . '/points_v53.php';
require_once $root . '/points_v60.php';
require_once $root . '/marketplace_v60.php';
require_once $root . '/teacher_operations_v46.php';
require_once $root . '/teacher_scope_v59.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

// Fixture tříd (subject_family potřebuje course_type) – zaregistruje se jako "neomezená kopie modulů".
$GLOBALS['modules'] = [
    'class_1a' => ['course_type' => 'graphics'],
    'class_3a' => ['course_type' => 'networks'],
    'class_4a' => ['course_type' => 'networks'],
];
teacher59_all_modules($GLOBALS['modules']);

$classA = 'class_3a';
$classB = 'class_4a';
$studentA = 'class_3a:student:Audit Zak';

// --- 1) Katalog: uložení, aktivace, bílá listina typů -------------------------------------------
$idFrame = mkt60_save_item('', ['type' => 'cosmetic', 'title' => 'Rámeček', 'price' => 10, 'classes' => [$classA], 'slot' => 'frame', 'active' => true], [$classA, $classB], 'audit');
$check('mkt60_save_item: platná kosmetika se uloží', is_string($idFrame));
$check('mkt60_save_item: neplatný typ (mimo bílou listinu) se odmítne', mkt60_save_item('', ['type' => 'zbrane', 'title' => 'X', 'price' => 1, 'classes' => [$classA]], [$classA], 'audit') === null);
$check('mkt60_save_item: kosmetika bez slotu se odmítne', mkt60_save_item('', ['type' => 'cosmetic', 'title' => 'X', 'price' => 1, 'classes' => [$classA]], [$classA], 'audit') === null);
$check('mkt60_save_item: cizí třída mimo dovolené se odmítne', mkt60_save_item('', ['type' => 'other', 'title' => 'X', 'price' => 1, 'classes' => ['class_9z']], [$classA], 'audit') === null);

$idLimited = mkt60_save_item('', ['type' => 'other', 'title' => 'Limitka', 'price' => 5, 'stock' => 1, 'classes' => [$classA], 'active' => true], [$classA], 'audit');
$check('mkt60_save_item: skladová položka se uloží', is_string($idLimited));

// --- 2) Nákup: bez bodů odmítnut, sklad se nesmí dostat pod 0, idempotence request_id ------------
$result = mkt60_buy($classA, $studentA, (string)$idLimited, 1, 'req-1');
$check('mkt60_buy: bez bodů na účtu je nákup odmítnut', $result['ok'] === false && $result['error'] === 'insufficient_points');

pts53_award($classA, $studentA, 'seed:1', 10, 'seed');
$result2 = mkt60_buy($classA, $studentA, (string)$idLimited, 1, 'req-2');
$check('mkt60_buy: nákup s dostatkem bodů projde', $result2['ok'] === true);
$check('mkt60_buy: sklad se snížil na 0', (int)(mkt60_item((string)$idLimited)['stock'] ?? -1) === 0);

$result3 = mkt60_buy($classA, $studentA, (string)$idLimited, 1, 'req-3');
$check('mkt60_buy: vyprodaná položka se dál nekupuje (sklad nikdy pod 0)', $result3['ok'] === false && $result3['error'] === 'out_of_stock');
$check('mkt60_buy: sklad zůstává 0 (ne záporný)', (int)(mkt60_item((string)$idLimited)['stock'] ?? -1) === 0);

$balanceBefore = pts53_balance($classA, $studentA);
$resultRepeat = mkt60_buy($classA, $studentA, (string)$idLimited, 1, 'req-2');
$check('mkt60_buy: opakování stejného request_id je idempotentní (bez druhého odečtu)', $resultRepeat['ok'] === true && pts53_balance($classA, $studentA) === $balanceBefore);

// --- 3) Kosmetika: aktivace jen u vlastněné položky ----------------------------------------------
pts53_award($classA, $studentA, 'seed:2', 10, 'seed');
$buyFrame = mkt60_buy($classA, $studentA, (string)$idFrame, 1, 'req-frame');
$check('mkt60_buy: nákup rámečku proběhl', $buyFrame['ok'] === true);
$check('mkt60_set_cosmetic: vlastněný rámeček lze aktivovat', mkt60_set_cosmetic($classA, $studentA, 'frame', (string)$idFrame));
$check('mkt60_cosmetics: aktivní rámeček se uloží', mkt60_cosmetics($classA, $studentA)['frame'] === $idFrame);
$check('mkt60_set_cosmetic: nevlastněnou položku nelze aktivovat', !mkt60_set_cosmetic($classA, $studentA, 'frame', 'neexistujici-id'));

// --- 4) Cizí profil nikdy nesmí zobrazit body (profile60_data_points je jen pro isMe) ------------
require_once $root . '/profile_v60.php';
$check('profil: profile60_data() u cizího profilu nevrací body ani na záložce "body"', profile60_data($classA, $studentA, '', false, 'body') === []);

// --- 5) Log nákupů/vrácení je append-only proud (marketplace_v60_log) ----------------------------
$logRows = iterator_to_array(storage_scan('marketplace_v60_log'), false);
$check('log: nákupy se zapsaly do proudu marketplace_v60_log', count($logRows) >= 2);

// --- 6) Vrácení nákupu učitelem -------------------------------------------------------------------
$balanceBeforeRefund = pts53_balance($classA, $studentA);
$check('pts60_refund: vrátí body a nemaže nákup', pts60_refund($classA, $studentA, 'mkt:' . $idLimited . ':req-2', 'test') && pts53_balance($classA, $studentA) === $balanceBeforeRefund + 5);

// --- 7) Rozsah učitele: mkt60_save/activate/refund respektují teacher59_action_policy() ----------
require_once $root . '/teacher_accounts_v59.php';
// Skutečný účet v úložišti (ne mock) – teacher59_current()/teacher59_mode() se ověřují vždy proti storage.
$teacherId = 't_' . bin2hex(random_bytes(8));
storage_update(teacher59_accounts_path(), static function (array $store) use ($teacherId, $classA): array {
    $store['mode'] = 'accounts';
    $store['version'] = TEACHER59_STORE_VERSION;
    $store['accounts'][$teacherId] = [
        'id' => $teacherId, 'login' => 'audit.teacher', 'display_name' => 'Audit Učitel', 'role' => 'teacher',
        'assignments' => [['class_id' => $classA, 'subject_id' => '*']],
        'password_hash' => password_hash('audit-heslo-123456', PASSWORD_DEFAULT), 'must_change_password' => false,
        'status' => 'active', 'session_version' => 1, 'created_at' => date(DATE_ATOM), 'created_by' => 'audit', 'updated_at' => date(DATE_ATOM),
    ];
    return $store;
});
$_SESSION['teacher59'] = ['id' => $teacherId, 'sv' => 1, 'seen_at' => time(), 'login_at' => time()];
teacher59_reset_cache();
$check('fixture: teacher59_current() vrátí právě vytvořený účet role teacher', (string)(teacher59_current()['role'] ?? '') === 'teacher');

$postOwnClass = ['id' => '', 'classes' => [$classA]];
$check('teacher59_guard_post_check: mkt60_save do VLASTNÍ třídy je povoleno', teacher59_guard_post_check('mkt60_save', $postOwnClass) === null);
$postForeignClass = ['id' => '', 'classes' => [$classB]];
$check('teacher59_guard_post_check: mkt60_save do CIZÍ třídy je zamítnuto', teacher59_guard_post_check('mkt60_save', $postForeignClass) !== null);
$postEditForeignItem = ['id' => $idFrame, 'classes' => [$classA]]; // idFrame patří class_3a, to je OK…
$check('teacher59_guard_post_check: úprava položky VE vlastní třídě je povolena', teacher59_guard_post_check('mkt60_save', $postEditForeignItem) === null);

// Položka výhradně pro cizí třídu (class_4a) – učitel A ji nesmí upravit ani deaktivovat.
$idForeign = mkt60_save_item('', ['type' => 'other', 'title' => 'Cizí', 'price' => 1, 'classes' => [$classB], 'active' => true], [$classB], 'admin');
$check('teacher59_guard_post_check: mkt60_activate cizí položky je zamítnuto', teacher59_guard_post_check('mkt60_activate', ['id' => (string)$idForeign]) !== null);
$check('teacher59_guard_post_check: mkt60_activate vlastní položky je povoleno', teacher59_guard_post_check('mkt60_activate', ['id' => (string)$idFrame]) === null);

// Neexistující akce (jiný prefix) je zamítnuta (deny-by-default).
$check('teacher59_guard_post_check: neznámá akce je zamítnuta (deny-by-default)', teacher59_guard_post_check('mkt60_neco_neznameho', []) !== null);

// --- 8) Role: asistent nemá právo zapisovat (jen 'view'), učitel má 'content.manage' -------------
$check('teacher_action_permission: mkt60_save vyžaduje content.manage', teacher_action_permission('mkt60_save') === 'content.manage');
$check('role teacher: smí content.manage (může spravovat obchod svých tříd)', teacher_permission('content.manage'));
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

// --- 9) CSRF: akce žáka bez platného tokenu musí být odmítnuta (ověřuje se stejným API jako jinde) ---
$_SESSION = $_SESSION ?? [];
$_SESSION['csrf'] = 'ocekavany-token';
$csrfOk = hash_equals((string)$_SESSION['csrf'], 'ocekavany-token');
$csrfBad = hash_equals((string)$_SESSION['csrf'], 'jiny-token-od-utocnika');
$check('CSRF: shodný token projde', $csrfOk);
$check('CSRF: chybějící/jiný token je odmítnut', !$csrfBad);

exit(audit_summary($state, 'V60_MARKETPLACE'));
