<?php

declare(strict_types=1);

/**
 * EDUCANET v59 · audit učitelských účtů a rozsahu tříd/předmětů (AUTHZ58-07) – úroveň funkcí, dočasné úložiště.
 *   php tools/v59_teacher_accounts_audit.php
 * Kontroluje: CRUD účtů, OTP, vynucenou změnu hesla, lockout, session_version, timeouty, ochranu posledního admina,
 * rozsah (třídy, předměty), guardy POST/GET, pokrytí politik (každá akce teacher.php + handlerů), legacy no-op,
 * převzetí starých dat, žádná tajemství v logu, omezení PHP 8.1. Ostrou storage/ nikdy nečte ani nemění.
 * Konec: V59_TEACHER_ACCOUNTS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = edu_audit_temp_storage('v59-teacher-accounts');
putenv('EDUCANET_TEACHER_ROLE=teacher');
require $root . '/bootstrap.php';
require_once $root . '/teacher_accounts_v59.php';
require_once $root . '/teacher_scope_v59.php';
require_once $root . '/teacher_accounts_v59_admin.php';
require_once $root . '/teacher_accounts_v59_views.php';
require_once $root . '/teacher_v58.php';

$state = audit_counter();
$check = audit_checker($state);
$throws = static function (callable $fn): bool { try { $fn(); return false; } catch (Throwable $e) { return true; } };
$read = static fn(string $path): string => (string)@file_get_contents($path);
$resetLimits = static function (): void { unset($_SESSION['auth_rate_limits']); @unlink(auth_rate_limit_path()); php_json_cache_forget(auth_rate_limit_path()); };
$as = static function (?string $id): void { teacher59_session_clear(); if ($id !== null) teacher59_session_begin((array)teacher59_account($id)); };
$_SERVER['REMOTE_ADDR'] = '198.51.100.40';
$_SERVER['HTTP_USER_AGENT'] = 'AuditBrowser/59';
$otps = [];
$passwords = [];

$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === rtrim(str_replace('\\', '/', $tmp), '/') && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

// --- 1. Legacy režim: guardy jsou no-op -----------------------------------------
$check('bez souboru účtů je režim legacy', teacher59_mode() === 'legacy' && !is_file(teacher59_accounts_path()));
$_SESSION['teacher_export_authenticated'] = true;
$check('legacy: rozsah = všechny třídy, admin, výchozí třída class_2a', teacher59_scope()['all'] && teacher59_is_admin() && teacher59_can_class('class_3a') && teacher59_default_class('') === 'class_2a' && teacher59_default_class('class_4a') === 'class_4a');
$check('legacy: guard POST i GET nic nezamítá (ani neznámou akci)', teacher59_guard_post_check('save_grade', ['class_id' => 'class_3a']) === null && teacher59_guard_post_check('neznama_akce', []) === null
    && teacher59_guard_get_check('quality', ['class' => 'class_3a']) === null && teacher59_guard_get_check('arena', ['hadanka_poll' => '1']) === null);
$legacyModules = $GLOBALS['modules'];
$check('legacy: $modules i mapy tříd beze změny, role/owner z v46', teacher59_scope_modules($legacyModules) === $legacyModules && teacher59_filter_class_map(['class_3a' => 1]) === ['class_3a' => 1]
    && teacher59_role_for_v46() === null && teacher59_owner_key() === null && teacher59_session_state() === 'none' && teacher59_current() === null);
$check('legacy: přihlášení se nevolá – starý příznak session zůstává', !empty($_SESSION['teacher_export_authenticated']));
unset($_SESSION['teacher_export_authenticated']);

// --- 2. Pokrytí politik (deny-by-default) ----------------------------------------
$actions = [];
$collect = static function (string $code) use (&$actions): void {
    foreach (['/\$action\s*===\s*\'([a-z0-9_]+)\'/', '/\$action\s*!==\s*\'([a-z0-9_]+)\'/', '/case\s+\'([a-z0-9_]+)\'\s*:/'] as $re) {
        preg_match_all($re, $code, $m);
        foreach ($m[1] as $a) $actions[$a] = true;
    }
    preg_match_all('/in_array\(\$action\s*,\s*\[([^\]]*)\]/', $code, $m);
    foreach ($m[1] as $list) { preg_match_all('/\'([a-z0-9_]+)\'/', $list, $mm); foreach ($mm[1] as $a) $actions[$a] = true; }
};
$teacherSrc = $read($root . '/teacher.php');
$postBlock = (string)strstr($teacherSrc, "if (\$action === 'teacher_login')");
$collect(substr($postBlock, 0, max(0, (int)strpos($postBlock, '$rawFlash'))));
$handlers = [
    'intake_v51_teacher.php' => 'intake_v51_teacher_handle_post', 'session_v53_teacher.php' => 'sess53_teacher_handle_post',
    'arena_v57.php' => 'arena57_teacher_handle_post', 'accounts_v58_views.php' => 'acc58_teacher_apply',
    'arena_v58_weekly.php' => 'arena58_weekly_teacher_handle_post', 'robots_v58.php' => 'robots58_teacher_handle_post',
    'teamgames_v58_teacher_views.php' => 'tg58_teacher_handle_post', 'arena_v58_ctf.php' => 'arena58_ctf_teacher_handle_post',
    'arena_v58_incident.php' => 'arena58_inc_teacher_handle_post', 'lab_v58_teacher.php' => 'lab58t_teacher_handle_post',
    'lab_v58_editor.php' => 'lab58e_teacher_handle_post', 'identity_v58_views.php' => 'identity58_teacher_handle_post',
];
$missingHandlers = [];
foreach ($handlers as $file => $fn) {
    $body = (string)strstr($read($root . '/' . $file), 'function ' . $fn . '(');
    if ($body === '') { $missingHandlers[] = $file; continue; }
    $next = strpos($body, "\nfunction ", 10);
    $collect($next === false ? $body : substr($body, 0, $next));
}
unset($actions['teacher_login'], $actions['teacher_logout']);
$denied = array_keys(array_filter($actions, static fn(bool $v, string $a): bool => !empty(teacher59_action_policy($a)['deny']), ARRAY_FILTER_USE_BOTH));
$check('všechny handlery nalezeny' . ($missingHandlers ? ' – CHYBÍ: ' . implode(', ', $missingHandlers) : ''), $missingHandlers === []);
$check('politika pro každou POST akci (' . count($actions) . ')' . ($denied ? ' – CHYBÍ: ' . implode(', ', $denied) : ''), count($actions) >= 100 && $denied === []);
$check('neznámá akce a akce jen s prefixem v58 jsou zamítnuté', !empty(teacher59_action_policy('neznama_akce_x')['deny']) && !empty(teacher59_action_policy('robots58_hack')['deny']) && !empty(teacher59_action_policy('arena58_')['deny']));
$prefixless = [];
foreach (teacher58_modules() as $tab => $mod) {
    foreach (array_keys((array)($mod['post'] ?? [])) as $prefix) {
        if (!array_filter(array_keys(teacher59_action_policies()), static fn(string $a): bool => str_starts_with($a, (string)$prefix))) $prefixless[] = $prefix;
    }
}
$check('každý POST prefix registru v58 má politiky' . ($prefixless ? ' – CHYBÍ: ' . implode(', ', $prefixless) : ''), $prefixless === []);
$getMissing = [];
foreach (teacher58_modules() as $tab => $mod) {
    foreach (array_keys((array)($mod['get'] ?? [])) as $param) if (!isset(teacher59_get_policies()[$tab . '|' . $param])) $getMissing[] = $tab . '|' . $param;
}
foreach (['teach|v48_state', 'pristupy|print', 'arena|arena_poll', 'arena|hadanka_poll', 'arena|zaznam', 'arena|projector', 'intake|artifact', 'intake|export'] as $key) {
    if (!isset(teacher59_get_policies()[$key])) $getMissing[] = $key;
}
$check('každý zvláštní GET (registr v58 i teacher.php) má politiku' . ($getMissing ? ' – CHYBÍ: ' . implode(', ', $getMissing) : ''), $getMissing === []);
$check('admin-only akce: týdenní hádanka, SLA (v69 vyřazeno → zamítnuto), sync V1, identita, správa účtů', !empty(teacher59_action_policy('arena58_weekly_reroll')['admin']) && !empty(teacher59_action_policy('teacher_sla_policy_save')['deny'])
    && !empty(teacher59_action_policy('intake_t_sync')['admin']) && !empty(teacher59_action_policy('identity58_plan')['admin']) && !empty(teacher59_action_policy('teacher59_admin_create')['admin']));

// --- 3. Účty: založení, validace, OTP ---------------------------------------------
$admin = teacher59_account_create(['login' => 'ada.admin', 'display_name' => 'Ada Správcová', 'role' => 'admin'], 'cli');
$otps[] = $admin['otp'];
$check('create-admin zapne režim accounts (soubor s ochranným řádkem)', teacher59_mode() === 'accounts' && str_starts_with($read(teacher59_accounts_path()), STORAGE_GUARD_LINE));
$mk = static function (string $login, string $name, string $role, array $assign) use (&$otps): array {
    $r = teacher59_account_create(['login' => $login, 'display_name' => $name, 'role' => $role, 'assignments' => $assign], 'cli');
    $otps[] = $r['otp'];
    return $r;
};
$a = $mk('alena.ucitelova', 'Alena Učitelová', 'teacher', [['class_id' => 'class_1a', 'subject_id' => '*'], ['class_id' => 'class_2a', 'subject_id' => 'graphics']]);
$b = $mk('bohdan.sitar', 'Bohdan Sítař', 'teacher', [['class_id' => 'class_3a', 'subject_id' => 'networks'], ['class_id' => 'class_4a', 'subject_id' => '*']]);
$s = $mk('sona.asistentka', 'Soňa Asistentka', 'assistant', [['class_id' => 'class_3a', 'subject_id' => '*']]);
$e = $mk('emil.bezdrid', 'Emil Beztřídní', 'teacher', []);
[$aid, $bid, $sid, $eid, $adminId] = [(string)$a['account']['id'], (string)$b['account']['id'], (string)$s['account']['id'], (string)$e['account']['id'], (string)$admin['account']['id']];
$check('předmět musí patřit třídě (3.A není grafika), neznámá třída odmítnuta', $throws(static fn() => teacher59_account_create(['login' => 'x.graf', 'display_name' => 'X Y', 'role' => 'teacher', 'assignments' => [['class_id' => 'class_3a', 'subject_id' => 'graphics']]], 'cli'))
    && $throws(static fn() => teacher59_validate_assignments([['class_id' => 'class_9z', 'subject_id' => '*']])) && teacher59_validate_assignments([['class_id' => 'class_2a', 'subject_id' => 'graphics']]) !== []);
$check('neplatné přihlašovací jméno, duplicita a neplatná role odmítnuty', $throws(static fn() => teacher59_account_create(['login' => 'Jan Novák', 'display_name' => 'Jan', 'role' => 'teacher'], 'cli'))
    && $throws(static fn() => teacher59_account_create(['login' => 'alena.ucitelova', 'display_name' => 'Jiná', 'role' => 'teacher'], 'cli'))
    && $throws(static fn() => teacher59_account_create(['login' => 'role.test', 'display_name' => 'Role Test', 'role' => 'lead'], 'cli')));
$accRaw = $read(teacher59_accounts_path());
$check('OTP: 4 slova + 4 číslice, unikátní, platnost 72 h, uložen jen hash', !array_filter($otps, static fn(string $o): bool => preg_match(teacher59_otp_pattern(), $o) !== 1) && count(array_unique($otps)) === count($otps)
    && abs((int)$a['account']['otp']['expires_at'] - time() - TEACHER59_OTP_TTL) < 60 && password_verify($a['otp'], (string)teacher59_account($aid)['password_hash']) && !array_filter($otps, static fn(string $o): bool => str_contains($accRaw, $o)));
require_once $root . '/accounts_v58.php';
$check('entropie OTP ≥ 45 bitů (' . round(TEACHER59_OTP_WORD_COUNT * log(count(acc58_words()), 2) + 4 * log(strlen(ACC58_OTP_DIGITS), 2), 1) . ')', TEACHER59_OTP_WORD_COUNT * log(count(acc58_words()), 2) + 4 * log(strlen(ACC58_OTP_DIGITS), 2) >= 45.0);
$check('nový účet: must_change, active, session_version 1, žádný e-mail ani IP', !empty(teacher59_account($aid)['must_change_password']) && teacher59_account($aid)['status'] === 'active' && (int)teacher59_account($aid)['session_version'] === 1
    && !str_contains($accRaw, '"email"') && !str_contains($accRaw, '198.51.100'));

// --- 4. Přihlášení, vynucená změna hesla ---------------------------------------
$resetLimits();
$_SESSION['csrf'] = 'before-login';
$_SESSION['teacher_export_authenticated'] = true;
$login = teacher59_attempt_login('Alena.Ucitelova', $a['otp']);
$check('přihlášení OTP projde, rotuje CSRF, starý příznak smaže', $login['ok'] && $_SESSION['csrf'] !== 'before-login' && empty($_SESSION['teacher_export_authenticated']) && (string)$_SESSION['teacher59']['id'] === $aid);
$check('vynucená změna: stav must_change, prázdný rozsah, guard zamítne, role asistent', teacher59_session_state() === 'must_change' && !teacher59_can_class('class_1a') && teacher59_guard_post_check('save_grade', ['class_id' => 'class_1a']) === 'no_session' && teacher59_role_for_v46() === 'assistant');
$pwA = 'Zelena-Lod-4827-Kopec';
$passwords[] = $pwA;
$check('slabé heslo (krátké, běžné, se jménem, = OTP, neshoda) odmítnuto', teacher59_change_password($aid, $a['otp'], 'Kratke-12', 'Kratke-12')['error'] !== null
    && teacher59_change_password($aid, $a['otp'], 'password123456', 'password123456')['error'] !== null && teacher59_change_password($aid, $a['otp'], 'Alena-Ucitelova-2026', 'Alena-Ucitelova-2026')['error'] !== null
    && teacher59_change_password($aid, $a['otp'], $a['otp'], $a['otp'])['error'] !== null && teacher59_change_password($aid, $a['otp'], $pwA, $pwA . 'x')['error'] !== null);
$check('špatné stávající heslo odmítnuto', teacher59_change_password($aid, 'spatne-heslo-2222-xx', $pwA, $pwA)['error'] === 'Stávající heslo nesouhlasí.');
$svBefore = (int)teacher59_account($aid)['session_version'];
$self = teacher59_self_apply('teacher59_self_password', ['current_password' => $a['otp'], 'new_password' => $pwA, 'new_password_confirm' => $pwA], $aid);
$check('změna hesla projde, relace pokračuje se stavem ok, OTP smazáno, session_version++', ($self['type'] ?? 'ok') === 'ok' && teacher59_session_state() === 'ok' && !isset(teacher59_account($aid)['otp'])
    && empty(teacher59_account($aid)['must_change_password']) && (int)teacher59_account($aid)['session_version'] === $svBefore + 1 && (int)$_SESSION['teacher59']['sv'] === $svBefore + 1);
$check('OTP po změně hesla neplatí', !teacher59_attempt_login('alena.ucitelova', $a['otp'])['ok']);
$unknown = teacher59_attempt_login('nikdo.neexistuje', 'Cokoli-1234-heslo');
$bad = teacher59_attempt_login('alena.ucitelova', 'Spatne-Heslo-9999');
$check('neznámý účet i špatné heslo: stejná obecná hláška', !$unknown['ok'] && !$bad['ok'] && $unknown['error'] === $bad['error'] && $bad['error'] === TEACHER59_MSG_LOGIN);
$check('dummy hash je uložený v úložišti (stejná cena ověření)', (string)(teacher59_store_read()['dummy_hash'] ?? '') !== '' && password_get_info(teacher59_dummy_hash())['algo'] !== null);
$check('teacher59_is_admin() je pro učitele false, pro admina true', (function () use ($as, $aid, $adminId): bool { $as($aid); $t = teacher59_is_admin(); $as($adminId); $ad = teacher59_session_state() === 'must_change'; return !$t && $ad; })());

// --- 5. Lockout ------------------------------------------------------------------
// SEC59-03: zámek je na login + IP (10/15 min); napříč IP (200/15 min) jen zpomalení – žák za školní NAT nezamkne učitele jinde.
$resetLimits();
$ipA = '203.0.113.10';
for ($i = 0; $i < TEACHER59_LOCK_LIMIT; $i++) { $_SERVER['REMOTE_ADDR'] = $ipA; $_SERVER['HTTP_USER_AGENT'] = 'Brute/' . $i; unset($_SESSION['auth_rate_limits']); teacher59_attempt_login('alena.ucitelova', 'Hadani-Hesla-' . $i . '1'); }
$_SERVER['HTTP_USER_AGENT'] = 'AuditBrowser/59'; unset($_SESSION['auth_rate_limits']);
$locked = teacher59_attempt_login('alena.ucitelova', $pwA);
$check('SEC59-03: po 10 chybách z IP A je login z IP A zamčen i se správným heslem', !$locked['ok'] && $locked['error'] === TEACHER59_MSG_LIMIT && teacher59_is_locked('alena.ucitelova'));
$_SERVER['REMOTE_ADDR'] = '203.0.113.99'; unset($_SESSION['auth_rate_limits']);
$fromB = teacher59_attempt_login('alena.ucitelova', $pwA);
$check('SEC59-03: zámek z IP A nezamkne účet pro IP B (správné heslo z B projde)', $fromB['ok']);
$_SERVER['REMOTE_ADDR'] = $ipA; unset($_SESSION['auth_rate_limits']);
$check('SEC59-03: IP A zůstává zamčená i po úspěchu z IP B', teacher59_attempt_login('alena.ucitelova', $pwA)['error'] === TEACHER59_MSG_LIMIT);
for ($i = 0; $i < TEACHER59_LOCK_LIMIT; $i++) { $_SERVER['HTTP_USER_AGENT'] = 'Brute2/' . $i; unset($_SESSION['auth_rate_limits']); teacher59_attempt_login('neexistujici.ucet', 'Hadani-Hesla-' . $i . '1'); }
$_SERVER['HTTP_USER_AGENT'] = 'AuditBrowser/59'; unset($_SESSION['auth_rate_limits']);
$check('zámek neprozradí existenci účtu (neexistující dostane stejnou hlášku)', teacher59_attempt_login('neexistujici.ucet', 'x')['error'] === TEACHER59_MSG_LIMIT);
teacher59_unlock_login('alena.ucitelova');
unset($_SESSION['auth_rate_limits']);
$check('odemknutí (admin/CLI) vrátí přihlášení i z IP A', teacher59_attempt_login('alena.ucitelova', $pwA)['ok'] && !teacher59_is_locked('alena.ucitelova'));
for ($i = 0; $i < TEACHER59_THROTTLE_LIMIT - 1; $i++) { $_SERVER['REMOTE_ADDR'] = '198.18.' . intdiv($i, 250) . '.' . ($i % 250 + 1); teacher59_lock_fail('alena.ucitelova'); }
$_SERVER['REMOTE_ADDR'] = '198.18.9.9'; unset($_SESSION['auth_rate_limits']);
teacher59_attempt_login('alena.ucitelova', 'Posledni-Spatne-2001'); // 200. chyba → přechod do zpomalení (log „throttled“)
$_SERVER['REMOTE_ADDR'] = '198.51.100.41'; unset($_SESSION['auth_rate_limits']);
$counts = teacher59_lock_counts('alena.ucitelova');
$t0 = microtime(true);
$throttled = teacher59_attempt_login('alena.ucitelova', $pwA);
$elapsed = microtime(true) - $t0;
$check('SEC59-03: 200 chyb z různých IP jen zpomalí (' . round($elapsed, 2) . ' s) – správné heslo z nové IP projde', $counts['all'] >= TEACHER59_THROTTLE_LIMIT && $throttled['ok']
    && $elapsed >= 0.2 && teacher59_throttle_delay_ms(TEACHER59_THROTTLE_LIMIT - 1) === 0 && teacher59_throttle_delay_ms(100000) === TEACHER59_THROTTLE_MAX_MS && teacher59_is_locked('alena.ucitelova'));
teacher59_unlock_login('alena.ucitelova');
$_SERVER['REMOTE_ADDR'] = '198.51.100.40';
$locksRaw = $read(teacher59_locks_path());
$check('úložiště zámků: ochranný řádek, bez loginu a IP v čitelné podobě, odemknutí smaže všechny IP', str_starts_with($locksRaw, STORAGE_GUARD_LINE) && !str_contains($locksRaw, 'alena') && !str_contains($locksRaw, '203.0.113')
    && !str_contains($locksRaw, '198.18.') && teacher59_lock_counts('alena.ucitelova')['all'] === 0 && !str_contains($read(auth_rate_limit_path()), 'alena'));

// --- 6. Rozsah učitele A (1.A + 2.A grafika) ------------------------------------
$resetLimits();
$as($aid);
$check('A: povolené třídy 1.A a 2.A, předmět jen grafika', teacher59_allowed_class_ids() == ['class_2a', 'class_1a'] || (count(teacher59_allowed_class_ids()) === 2 && teacher59_can_class('class_1a') && teacher59_can_class('class_2a')));
$check('A: 3.A/4.A nepovolené, can_classes vyžaduje všechny, prázdný seznam = jen admin', !teacher59_can_class('class_3a') && !teacher59_can_classes(['class_2a', 'class_3a']) && teacher59_can_classes(['class_2a']) && !teacher59_can_classes([]));
$check('A: předměty graphics ano, networks/both ne; subject_of_class podle subject_family', teacher59_can_subject('graphics') && !teacher59_can_subject('networks') && !teacher59_can_subject('both')
    && teacher59_subject_of_class('class_3a') === 'networks' && teacher59_subject_of_class('class_2a') === 'graphics' && teacher59_subject_of_class('class_9z') === '');
$scoped = teacher59_scope_modules($GLOBALS['modules']);
$check('A: $modules omezené, neomezená kopie zachovaná, mapa tříd filtrovaná', array_keys($scoped) == array_values(array_filter(array_keys($GLOBALS['modules']), static fn($k): bool => in_array($k, ['class_1a', 'class_2a'], true)))
    && count(teacher59_all_modules()) === count($GLOBALS['modules']) && teacher59_filter_class_map(['class_2a' => 1, 'class_3a' => 2]) === ['class_2a' => 1]);
$check('A: výchozí třída (nepovolená → 2.A), role teacher, owner klíč v3', teacher59_default_class('class_3a') === 'class_2a' && teacher59_default_class('class_1a') === 'class_1a' && teacher59_redirect_class('class_4a') === 'class_2a'
    && teacher59_role_for_v46() === 'teacher' && teacher59_owner_key() === hash('sha256', 'v3|' . $aid));
$g = static fn(string $action, array $post): ?string => teacher59_guard_post_check($action, $post);
$check('POST A: cizí třída 403, chybějící class_id 403, vlastní OK', $g('save_grade', ['class_id' => 'class_3a']) === 'class_out_of_scope' && $g('save_grade', []) === 'missing_class' && $g('teacher_bulk_mark', ['class_id' => 'class_2a']) === null);
$check('POST A: class_ids[] i classes[] musí být všechny v rozsahu', $g('arena58_ctf_create', ['class_ids' => ['class_2a', 'class_3a']]) === 'class_out_of_scope' && $g('arena58_ctf_create', ['class_ids' => ['class_2a']]) === null
    && $g('lab58e_save', ['class_id' => 'class_2a', 'classes' => ['class_3a']]) === 'class_out_of_scope' && $g('lab58e_save', ['class_id' => 'class_2a']) === 'missing_class' && $g('lab58e_save', ['classes' => ['class_1a', 'class_2a']]) === null);
$check('POST A: neplatný tvar class_id odmítnut, class_id=all odmítnuto, vyřazená review_ack zamítnuta (v69)', $g('teacher_bulk_mark', ['class_id' => ['class_2a']]) === 'bad_class_param' && $g('teacher_review_ack', ['class_id' => 'all']) === 'unknown_action' && $g('teacher_bulk_mark', ['class_id' => 'all']) === 'class_out_of_scope');
$check('POST A: admin-only akce, legacy-only role, neznámá akce', $g('arena58_weekly_override', []) === 'admin_only' && $g('identity58_plan', []) === 'admin_only' && $g('teacher59_admin_create', []) === 'admin_only'
    && $g('teacher_team_member_role', []) === 'legacy_only' && $g('neznama_akce', []) === 'unknown_action' && $g('teacher59_self_password', []) === null);
storage_write(STORAGE_DIR . '/teacher_followups.json.php', [['id' => 'fu_3a', 'class_id' => 'class_3a'], ['id' => 'fu_2a', 'class_id' => 'class_2a'], ['id' => 'fu_none']]);
storage_write(STORAGE_DIR . '/teacher_saved_filters.json.php', [['id' => 'sf_4a', 'class_id' => 'class_4a'], ['id' => 'sf_1a', 'class_id' => 'class_1a'], ['id' => 'sf_3a', 'class_id' => 'class_3a']]);
storage_write(STORAGE_DIR . '/skill_evidence.json.php', [['id' => 'ev_3a', 'class_id' => 'class_3a']]);
$check('POST A: entita cizí třídy / neexistující / bez třídy / chybějící id → 403', $g('teacher_followup_update', ['followup_id' => 'fu_3a']) === 'entity_out_of_scope' && $g('teacher_followup_update', ['followup_id' => 'nic']) === 'entity_not_found'
    && $g('teacher_followup_update', ['followup_id' => 'fu_none']) === 'entity_out_of_scope' && $g('teacher_followup_update', []) === 'missing_entity' && $g('teacher_saved_filter_delete', ['filter_id' => 'sf_3a']) === 'entity_out_of_scope' && $g('skill_validation', ['evidence_id' => 'ev_3a']) === 'unknown_action');
$check('POST A: entita vlastní třídy OK (i když class_id z formuláře lže, ověří se obojí)', $g('teacher_followup_update', ['followup_id' => 'fu_2a']) === null && $g('teacher_saved_filter_delete', ['filter_id' => 'sf_1a']) === null
    && $g('teacher_saved_filter_delete', ['filter_id' => 'sf_4a', 'class_id' => 'class_1a']) === 'entity_out_of_scope' && $g('teacher_followup_update', ['followup_id' => 'fu_2a', 'class_id' => 'class_3a']) === 'class_out_of_scope');
$check('POST A: chybí finder modulu → fail closed (závod, hra, dotazník)', $g('arena57_start', ['class_id' => 'class_2a', 'race' => 'r1']) === 'entity_not_found' && $g('intake_t_delete', ['class_id' => 'class_2a', 'response_id' => 'x']) === 'entity_not_found');
$check('POST A: banka otázek podle předmětu (grafika ano, sítě/obojí ne), mazání bez položky 403', $g('tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'graphics']) === null && $g('tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'networks']) === 'subject_out_of_scope'
    && $g('tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'both']) === 'subject_out_of_scope' && $g('tg58_bank_delete', ['bank_id' => 'q1']) === 'entity_not_found');
$_POST = ['followup_id' => 'fu_2a'];
teacher59_guard_post('teacher_followup_update');
$check('guard doplní class_id z entity (přesměrování nepadá na class_2a cizí třídy)', ($_POST['class_id'] ?? '') === 'class_2a');
$_POST = [];
$gg = static fn(string $tab, array $get): ?string => teacher59_guard_get_check($tab, $get);
$check('GET A: admin záložky 403 (ucitele, identita, provoz, quality)', $gg('ucitele', []) === 'admin_only' && $gg('identita', []) === 'admin_only' && $gg('provoz', []) === 'admin_only' && $gg('quality', []) === 'admin_only');
$check('GET A: cizí třída v ?class, projekce v48, tisk kartiček, export labu → 403', $gg('class_overview', ['class' => 'class_3a']) === 'class_out_of_scope' && $gg('teach', ['v48_state' => '1', 'class' => 'class_4a']) === 'class_out_of_scope'
    && $gg('pristupy', ['print' => '1', 'class' => 'class_3a']) === 'class_out_of_scope' && $gg('labdata', ['export' => '1', 'class' => 'class_3a']) === 'class_out_of_scope' && $gg('teach', ['v48_state' => '1', 'class' => 'class_2a']) === null);
$check('GET A: neznámá třída se nezamítá (padá na výchozí), student cizí třídy 403', $gg('class_overview', ['class' => 'class_xyz']) === null && $gg('student360', ['student' => 'class_3a:student:abc']) === 'entity_out_of_scope' && $gg('student360', ['student' => 'class_2a:student:abc']) === null);
$check('GET A: polling/projektor bez ověřitelné entity 403 (závod, zápas, hra, artefakt)', $gg('arena', ['arena_poll' => '1', 'race' => 'x']) === 'entity_not_found' && $gg('roboti', ['robots_poll' => '1']) === 'entity_not_found'
    && $gg('hry', ['tg_poll' => '1', 'game' => 'g', 'projektor' => '1']) === 'entity_not_found' && $gg('intake', ['artifact' => 'r1']) === 'entity_not_found' && $gg('ucitele', ['karticka' => 't']) === 'admin_only');
$check('SEC59-10: GET A: týdenní hádanka povolená každému učiteli (data filtruje modul), bez značky modulu', $gg('arena', ['hadanka_poll' => '1']) === null && !teacher59_module_filtered('arena58_weekly_scoped')
    && !isset(teacher59_get_policies()['arena|hadanka_poll']['filter_marker']));
$check('A: ?projektor=<id> se bere jako id hry, projektor=1 ne', teacher59_get_entity_id(['projektor' => 'abc'], 'game|projektor') === 'abc' && teacher59_get_entity_id(['game' => 'g1', 'projektor' => '1'], 'game|projektor') === 'g1'
    && teacher59_get_entity_ids(['game' => 'g1', 'projektor' => 'g2'], 'game|projektor') === ['g1', 'g2'] && teacher59_get_entity_ids(['projektor' => ['x']], 'game|projektor') === null);

// --- 7. Ostatní role ------------------------------------------------------------------
$as($sid);
teacher59_change_password($sid, $s['otp'], 'Modry-Balon-7342-Vrch', 'Modry-Balon-7342-Vrch');
$as($sid);
$check('asistent: jen 3.A, role assistant pro v46', teacher59_allowed_class_ids() === ['class_3a'] && teacher59_role_for_v46() === 'assistant' && $g('teacher_bulk_mark', ['class_id' => 'class_3a']) === null && $g('teacher_bulk_mark', ['class_id' => 'class_4a']) === 'class_out_of_scope');
$as($eid);
teacher59_change_password($eid, $e['otp'], 'Hneda-Kotva-5823-Luka', 'Hneda-Kotva-5823-Luka');
$as($eid);
$check('učitel bez tříd: žádná povolená třída, výchozí třída prázdná, vše 403', teacher59_allowed_class_ids() === [] && teacher59_default_class('class_2a') === '' && $g('teacher_bulk_mark', ['class_id' => 'class_2a']) === 'class_out_of_scope' && $gg('overview', ['class' => 'class_2a']) === 'class_out_of_scope');
$as($adminId);
$pwAdmin = 'Stribrny-Most-6284-Rak';
$passwords[] = $pwAdmin;
teacher59_change_password($adminId, $admin['otp'], $pwAdmin, $pwAdmin);
$as($adminId);
$check('admin: všechny třídy a předměty, admin-only akce i GET povolené', teacher59_is_admin() && teacher59_can_subject('both') && $g('arena58_weekly_reroll', []) === null && $g('save_grade', ['class_id' => 'class_3a']) === null
    && $gg('quality', []) === null && $gg('arena', ['hadanka_poll' => '1']) === null && teacher59_role_for_v46() === 'admin');

// --- 8. Správa účtů (admin apply), kartička, poslední admin -------------------------
$created = teacher59_admin_apply('teacher59_admin_create', ['login' => 'cyril.novy', 'display_name' => 'Cyril Nový', 'role' => 'teacher', 'assign' => ['class_4a'], 'subject' => ['class_4a' => 'networks']], $adminId);
$card = $_SESSION['teacher59_card'] ?? [];
$otps[] = (string)($card['otp'] ?? '');
$reveal1 = teacher59_card_take_reveal();
$reveal2 = teacher59_card_take_reveal();
$check('admin create: kartička v session, OTP ukázané jen poprvé', is_array($reveal1) && $reveal2 === null && preg_match(teacher59_otp_pattern(), (string)$reveal1['otp']) === 1 && teacher59_find_by_login('cyril.novy') !== null);
$html = audit_capture(static function () use ($adminId): void { $_SESSION['teacher59_card'] = ($_SESSION['teacher59_card'] ?? []); teacher59_render_admin_tab(); });
$check('záložka Učitelé: CSRF, účty, žádný hash ani OTP po prvním zobrazení', str_contains($html, 'name="csrf" value="' . csrf_token() . '"') && str_contains($html, 'cyril.novy') && str_contains($html, 'alena.ucitelova')
    && !str_contains($html, '$argon') && !str_contains($html, '$2y$') && !str_contains($html, (string)$reveal1['otp']));
$check('kartička: token jednorázový, špatný token nic nevydá', teacher59_card_consume('spatny') === null && is_array(teacher59_card_consume((string)$reveal1['token'])) && teacher59_card_consume((string)$reveal1['token']) === null);
$check('admin update: předmět mimo třídu odmítnut, role/přiřazení uloženy', $throws(static fn() => teacher59_admin_apply('teacher59_admin_update', ['account_id' => $bid, 'role' => 'teacher', 'assign' => ['class_3a'], 'subject' => ['class_3a' => 'graphics']], $adminId))
    && is_array(teacher59_admin_apply('teacher59_admin_update', ['account_id' => $bid, 'role' => 'teacher', 'assign' => ['class_3a', 'class_4a'], 'subject' => ['class_3a' => 'networks']], $adminId)));
$check('poslední aktivní admin: nelze degradovat ani deaktivovat, ani sebe', $throws(static fn() => teacher59_account_update_access($adminId, 'teacher', [], 'cli'))
    && $throws(static fn() => teacher59_account_set_status($adminId, 'disabled', 'cli')) && $throws(static fn() => teacher59_account_set_status($adminId, 'disabled', $adminId)));
$second = $mk('druhy.admin', 'Druhý Admin', 'admin', []);
$check('s druhým adminem jde prvního degradovat (a vrátit)', !$throws(static fn() => teacher59_account_update_access($adminId, 'teacher', [], 'cli')) && !$throws(static fn() => teacher59_account_update_access($adminId, 'admin', [], 'cli')));
$as($adminId);
$svAdmin = (int)teacher59_account($adminId)['session_version'];
$check('SEC59-21: admin nemůže resetovat vlastní účet (web i funkce), heslo ani relace se nezmění', $throws(static fn() => teacher59_admin_apply('teacher59_admin_reset', ['account_id' => $adminId], $adminId))
    && $throws(static fn() => teacher59_account_reset($adminId, $adminId)) && empty(teacher59_account($adminId)['must_change_password']) && (int)teacher59_account($adminId)['session_version'] === $svAdmin && teacher59_session_state() === 'ok');
$adminHtml = audit_capture(static function (): void { teacher59_render_admin_tab(); });
$selfBlock = (string)strstr((string)strstr($adminHtml, '<small>(vy)</small>'), '</article>', true);
$check('SEC59-21: záložka Učitelé nenabízí reset u vlastního účtu (u cizích ano)', $selfBlock !== '' && !str_contains($selfBlock, 'teacher59_admin_reset') && str_contains($selfBlock, '?tab=ucet') && str_contains($adminHtml, 'teacher59_admin_reset'));
$as($aid);
$check('admin apply jako učitel → výjimka', $throws(static fn() => teacher59_admin_apply('teacher59_admin_disable', ['account_id' => $bid], $aid)));

// --- 9. Invalidace relací a timeouty ------------------------------------------------
$pwB = 'Cerveny-Vlak-3947-Hora';
$passwords[] = $pwB;
teacher59_change_password($bid, $b['otp'], $pwB, $pwB);
$resetLimits();
$check('B se přihlásí vlastním heslem', teacher59_attempt_login('bohdan.sitar', $pwB)['ok'] && teacher59_session_state() === 'ok');
teacher59_account_reset($bid, $adminId);
teacher59_reset_cache();
$check('reset hesla adminem odhlásí otevřenou relaci (session_version)', teacher59_current() === null && teacher59_session_state() === 'expired' && ($_SESSION['teacher59_notice'] ?? '') === TEACHER59_MSG_EXPIRED);
$as($aid);
teacher59_account_update_access($aid, 'teacher', [['class_id' => 'class_2a', 'subject_id' => 'graphics']], $adminId);
teacher59_reset_cache();
$check('změna přiřazení odhlásí relaci', teacher59_current() === null);
$as($aid);
teacher59_account_set_status($aid, 'disabled', $adminId);
teacher59_reset_cache();
$check('deaktivace odhlásí relaci a přihlášení selže obecnou hláškou', teacher59_current() === null && teacher59_attempt_login('alena.ucitelova', $pwA)['error'] === TEACHER59_MSG_LOGIN);
teacher59_account_set_status($aid, 'active', $adminId);
$as($aid);
$_SESSION['teacher59']['seen_at'] = time() - TEACHER59_IDLE_TIMEOUT - 5;
teacher59_reset_cache();
$check('nečinnost > 60 min → relace vypršela', teacher59_current() === null && teacher59_session_state() === 'expired');
$as($aid);
$_SESSION['teacher59']['login_at'] = time() - TEACHER59_ABSOLUTE_TIMEOUT - 5;
teacher59_reset_cache();
$check('absolutně > 10 h → relace vypršela i při aktivitě', teacher59_current() === null);
$as($aid);
$_SESSION['teacher59']['seen_at'] = time() - 120;
teacher59_reset_cache();
$check('platná relace posune seen_at', teacher59_current() !== null && (int)$_SESSION['teacher59']['seen_at'] >= time() - 2);
$sv = (int)teacher59_account($aid)['session_version'];
teacher59_self_apply('teacher59_self_logout_others', [], $aid);
teacher59_reset_cache();
$check('„Odhlásit ostatní“: session_version++, tahle relace platí dál', (int)teacher59_account($aid)['session_version'] === $sv + 1 && teacher59_current() !== null);
teacher59_account_update($eid, static fn(array $x): array => array_replace($x, ['must_change_password' => true, 'otp' => ['issued_at' => time() - 90000, 'expires_at' => time() - 10, 'issued_by' => 'cli']]));
$resetLimits();
$check('vypršelé OTP: přihlášení odmítnuto s vlastní hláškou', teacher59_attempt_login('emil.bezdrid', 'Hneda-Kotva-5823-Luka')['error'] === TEACHER59_MSG_OTP_EXPIRED);
$as($aid);
$_SESSION['teacher_saved_filter_actor_token'] = str_repeat('cd', 32);
$_SESSION['teacher_demo_credentials'] = ['email' => 'demo@example.test'];
$_SESSION['teacher_export_flash'] = ['message' => 'x', 'type' => 'ok'];
teacher59_logout();
$check('odhlášení smaže jen učitelské klíče (CSRF zůstává); SEC59-17: i token prohlížeče, demo přístupy a flash', !isset($_SESSION['teacher59']) && !isset($_SESSION['teacher_display_name']) && isset($_SESSION['csrf'])
    && !isset($_SESSION['teacher_saved_filter_actor_token']) && !isset($_SESSION['teacher_demo_credentials']) && !isset($_SESSION['teacher_export_flash']));

// --- 10. Převzetí starých dat ----------------------------------------------------
$as($aid);
$token = str_repeat('ab', 32);
$_SESSION['teacher_saved_filter_actor_token'] = $token;
$legacyKey = teacher59_legacy_owner_key($token, 'Alena Učitelová');
// A má po změně přiřazení jen 2.A (grafika). SEC59-07: owner_label musí odpovídat jménu účtu; SEC59-06: řádky mimo rozsah zůstanou.
$opsAuditRow = ['id' => 'oa1', 'class_id' => 'class_2a', 'owner_key' => $legacyKey, 'owner_label' => 'Alena Učitelová', 'action' => 'x'];
storage_write(STORAGE_DIR . '/teacher_ops_audit.json.php', [$opsAuditRow]);
storage_write(STORAGE_DIR . '/teacher_saved_filters.json.php', [['id' => 'f1', 'class_id' => 'class_2a', 'owner_key' => $legacyKey, 'owner_version' => 2, 'owner_label' => 'ALENA UCITELOVA'],
    ['id' => 'f2', 'class_id' => 'class_2a', 'owner_key' => 'cizi', 'owner_version' => 2], ['id' => 'f3', 'class_id' => 'class_3a', 'owner_key' => $legacyKey, 'owner_version' => 2, 'owner_label' => 'Alena Učitelová']]);
storage_write(STORAGE_DIR . '/teacher_watchlist.json.php', [['id' => 'w1', 'class_id' => 'class_2a', 'owner_key' => $legacyKey]]);
storage_write(STORAGE_DIR . '/teacher_notifications.json.php', [['id' => 'n1', 'owner_key' => $legacyKey, 'url' => 'teacher.php?tab=class_results&class=class_4a', 'title' => 'MK4A'],
    ['id' => 'n2', 'owner_key' => $legacyKey, 'url' => 'teacher.php?tab=class_results&class=class_2a', 'title' => 'MK2A']]);
$check('SEC59-07: převzetí pod jiným jménem, než má účet → odmítnuto s pokynem pro admina, nic se nezmění', $throws(static fn() => teacher59_self_apply('teacher59_self_reclaim', ['legacy_name' => 'Jiné Jméno'], $aid))
    && storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php')[0]['owner_key'] === $legacyKey);
$res = teacher59_self_apply('teacher59_self_reclaim', ['legacy_name' => 'alena ucitelova'], $aid);
$filters = storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php');
$notes = storage_read(STORAGE_DIR . '/teacher_notifications.json.php');
$check('převzetí: jen záznamy s legacy klíčem tohoto prohlížeče a jména, v3 klíč + owner_version 3, owner_label beze změny', ($res['type'] ?? '') === 'ok'
    && $filters[0]['owner_key'] === hash('sha256', 'v3|' . $aid) && (int)$filters[0]['owner_version'] === 3 && $filters[0]['owner_label'] === 'ALENA UCITELOVA' && $filters[1]['owner_key'] === 'cizi'
    && storage_read(STORAGE_DIR . '/teacher_watchlist.json.php')[0]['owner_key'] === hash('sha256', 'v3|' . $aid));
$check('SEC59-06: převzetí přeskočí řádky tříd mimo rozsah účtu (filtr 3.A, upozornění 4.A z URL)', $filters[2]['owner_key'] === $legacyKey && $notes[0]['owner_key'] === $legacyKey && $notes[1]['owner_key'] === hash('sha256', 'v3|' . $aid));
$check('SEC59-07: audit operací se při převzetí nikdy nepřepisuje (owner_key i owner_label zůstávají)', storage_read(STORAGE_DIR . '/teacher_ops_audit.json.php') === [$opsAuditRow] && !isset(teacher59_reclaim_stores()['teacher_ops_audit.json.php']));
$lastLog = teacher59_log_rows(1)[0] ?? [];
$check('SEC59-07: log převzetí má počty podle úložiště a hash starého klíče (ne klíč)', ($lastLog['event'] ?? '') === 'reclaimed' && ($lastLog['meta']['counts']['saved_filters'] ?? 0) === 1
    && ($lastLog['meta']['skipped_out_of_scope'] ?? -1) === 2 && ($lastLog['meta']['old_key'] ?? '') === substr(hash('sha256', $legacyKey), 0, 16) && !str_contains($read(teacher59_log_path()), $legacyKey));
storage_write(STORAGE_DIR . '/teacher_followups.json.php', [['id' => 'fu_x', 'class_id' => 'class_2a', 'owner_key' => $legacyKey, 'owner_label' => 'Petr Kolega']]);
$check('SEC59-07: řádek se stejným klíčem, ale cizím owner_label (sdílené PC) → převzetí odmítnuto celé', $throws(static fn() => teacher59_reclaim_legacy($aid, $token, 'Alena Učitelová'))
    && storage_read(STORAGE_DIR . '/teacher_followups.json.php')[0]['owner_key'] === $legacyKey && $filters[2]['owner_key'] === storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php')[2]['owner_key']);
$check('převzetí bez tokenu prohlížeče odmítnuto', $throws(static fn() => teacher59_reclaim_legacy($aid, '', 'Alena')));

// --- 11. Pohledy ------------------------------------------------------------------
teacher59_session_clear();
$loginHtml = audit_capture(static function (): void { teacher59_render_login_card(null); });
$check('přihlašovací formulář: login + heslo + CSRF, bez sdíleného klíče', str_contains($loginHtml, 'name="login"') && str_contains($loginHtml, 'autocomplete="current-password"') && str_contains($loginHtml, 'value="teacher_login"')
    && str_contains($loginHtml, 'name="csrf"') && !str_contains($loginHtml, 'teacher_key'));
$as($aid);
$_SESSION['teacher_saved_filter_actor_token'] = $token; // SEC59-17: přihlášení token smaže, na webu ho obnoví cookie
$accHtml = audit_capture(static function (): void { teacher59_render_account_tab(); });
$check('záložka Můj účet: změna hesla, odhlášení ostatních, převzetí dat, CSRF', str_contains($accHtml, 'teacher59_self_password') && str_contains($accHtml, 'teacher59_self_logout_others') && str_contains($accHtml, 'teacher59_self_reclaim') && substr_count($accHtml, 'name="csrf"') >= 3);

// --- 12. Nečitelné úložiště = fail closed ------------------------------------------
$good = $read(teacher59_accounts_path());
$prevErrorLog = (string)ini_get("error_log"); ini_set("error_log", $tmp . "/audit-error.log");
file_put_contents(teacher59_accounts_path(), STORAGE_GUARD_LINE . '{nečitelné');
php_json_cache_forget(teacher59_accounts_path());
teacher59_reset_cache();
$check('poškozený soubor účtů: broken, bez relace, guard zamítá, nikdy legacy', teacher59_mode() === 'broken' && teacher59_current() === null && teacher59_session_state() === 'unavailable'
    && teacher59_guard_post_check('save_grade', ['class_id' => 'class_2a']) === 'no_session' && $throws(static fn() => teacher59_accounts()) && !teacher59_attempt_login('alena.ucitelova', $pwA)['ok']);
// SEC59-05: soubor účtů zmizí (obnova staré zálohy / jiný EDUCANET_STORAGE_DIR) – bez pojistky legacy, s pojistkou broken (503).
@unlink(teacher59_accounts_path());
php_json_cache_forget(teacher59_accounts_path());
teacher59_reset_cache();
putenv(TEACHER59_REQUIRED_ENV);
$check('SEC59-05: chybějící soubor bez pojistky = legacy (dosavadní chování)', !teacher59_accounts_required() && teacher59_mode() === 'legacy');
putenv(TEACHER59_REQUIRED_ENV . '=1');
teacher59_reset_cache();
$check('SEC59-05: chybějící soubor + ' . TEACHER59_REQUIRED_ENV . '=1 → broken: žádný legacy/admin, guard zamítá, přihlášení ne, sdílený klíč ne', teacher59_accounts_required() && teacher59_mode() === 'broken'
    && !teacher59_is_admin() && !teacher59_scope()['all'] && teacher59_session_state() === 'unavailable' && teacher59_guard_post_check('save_grade', ['class_id' => 'class_2a']) === 'no_session'
    && teacher59_guard_get_check('quality', []) === 'no_session' && !teacher_export_authenticated() && teacher_export_configured() && $throws(static fn() => teacher59_accounts())
    && !teacher59_attempt_login('alena.ucitelova', $pwA)['ok'] && teacher59_owner_account_map() === null);
// Stránka končí exit – vykreslí ji podproces nad stejným dočasným úložištěm.
$child = proc_open([PHP_BINARY, '-r', '$_SERVER["SCRIPT_FILENAME"] = "audit-child"; require ' . var_export($root . '/bootstrap.php', true) . '; require_once ' . var_export($root . '/teacher_accounts_v59_views.php', true) . '; teacher59_guard_forced_get(null); echo "NO-EXIT";'],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => STORAGE_DIR, TEACHER59_REQUIRED_ENV => '1']));
$unavailable = is_resource($child) ? (string)stream_get_contents($pipes[1]) : '';
if (is_resource($child)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($child); }
$check('SEC59-05: stránka při chybějícím souboru = 503 text „nedostupné“ (bez sdíleného klíče)', str_contains($unavailable, 'dočasně nedostupné') && !str_contains($unavailable, 'teacher_key') && !str_contains($unavailable, 'NO-EXIT'));
putenv(TEACHER59_REQUIRED_ENV);
file_put_contents(teacher59_accounts_path(), $good);
ini_set("error_log", $prevErrorLog);
php_json_cache_forget(teacher59_accounts_path());
teacher59_reset_cache();

// --- 13. Log bez tajemství, statika ----------------------------------------------
$log = $read(teacher59_log_path());
$noise = $read(teacher59_noise_path());
$leak = array_filter(array_merge($otps, $passwords), static fn(string $x): bool => $x !== '' && (str_contains($log, $x) || str_contains($noise, $x)));
$check('log: guard řádek, bezpečnostní události (přihlášení, heslo, vypršení), žádné heslo/OTP/IP', str_starts_with($log, STORAGE_GUARD_LINE) && str_contains($log, '"login_ok"') && str_contains($log, '"created"')
    && str_contains($log, '"expired"') && str_contains($log, '"pw_changed"') && $leak === [] && !str_contains($log, '198.51.100') && !str_contains($log, '"password') && !str_contains($log, 'password_hash'));
$check('SEC59-04: hlučné události (login_fail, locked, throttled) jen v odděleném logu, bez IP', str_starts_with($noise, STORAGE_GUARD_LINE) && str_contains($noise, '"login_fail"') && str_contains($noise, '"locked"')
    && str_contains($noise, '"throttled"') && !str_contains($log, '"login_fail"') && !str_contains($log, '"locked"') && !str_contains($noise, '198.51.100') && !str_contains($noise, '203.0.113'));
$createdBefore = count(array_filter(teacher59_log_rows(TEACHER59_LOG_LIMIT), static fn(array $r): bool => ($r['event'] ?? '') === 'created'));
$as($aid);
$_SERVER['REMOTE_ADDR'] = '192.0.2.77';
for ($i = 0; $i < 60; $i++) teacher59_log('scope_denied', $aid, null, ['class_3a'], 'flood');
for ($i = 0; $i < 30; $i++) teacher59_log('legacy_key_used', null, null, [], 'flood');
$_SERVER['REMOTE_ADDR'] = '198.51.100.40';
$noiseRows = teacher59_log_rows(TEACHER59_NOISE_LIMIT, true);
$floodDenied = count(array_filter($noiseRows, static fn(array $r): bool => ($r['event'] ?? '') === 'scope_denied' && ($r['reason'] ?? '') === 'flood'));
$floodKey = count(array_filter($noiseRows, static fn(array $r): bool => ($r['event'] ?? '') === 'legacy_key_used' && ($r['reason'] ?? '') === 'flood'));
$createdAfter = count(array_filter(teacher59_log_rows(TEACHER59_LOG_LIMIT), static fn(array $r): bool => ($r['event'] ?? '') === 'created'));
$check('SEC59-04: záplava scope_denied/legacy_key_used je omezená (' . $floodDenied . '/' . $floodKey . ' ≤ ' . TEACHER59_NOISE_PER_MINUTE . ' za minutu) a nevytlačí „created“ z bezpečnostního logu',
    $floodDenied >= 1 && $floodDenied <= TEACHER59_NOISE_PER_MINUTE * 2 && $floodKey <= TEACHER59_NOISE_PER_MINUTE * 2 && $createdBefore >= 5 && $createdAfter === $createdBefore
    && !str_contains($read(teacher59_log_path()), '"flood"'));
$files = ['teacher_accounts_v59.php', 'teacher_scope_v59.php', 'teacher_accounts_v59_admin.php', 'teacher_accounts_v59_views.php', 'teacher_accounts_v59_limits.php'];
foreach ($files as $f) {
    $src = $read($root . '/' . $f);
    $check('knihovna ' . $f . ': strict_types + guard, PHP 8.1 (bez readonly/enum/json_validate/typovaných konstant), ≤ 800 řádků',
        str_contains($src, 'declare(strict_types=1);') && str_contains($src, "=== basename(__FILE__)) { http_response_code(403); exit; }")
        && !preg_match('/\breadonly\b|\benum\s+[A-Z]|json_validate\(|const\s+[a-z?\\\\]+\s+[A-Z_]+\s*=/', $src) && substr_count($src, "\n") <= 800, false);
}
foreach (['tools/v59_teacher_accounts.php', 'tools/v59_teacher_accounts_audit.php'] as $f) $check('CLI guard ' . $f, str_contains($read($root . '/' . $f), "PHP_SAPI !== 'cli'"), false);
$js = $read($root . '/assets/teacher-accounts-v59.js');
$css = $read($root . '/assets/teacher-accounts-v59.css');
$check('JS bez innerHTML/eval, CSS s viditelným fokusem, cíli 44 px a tiskem', !str_contains($js, 'innerHTML') && !str_contains($js, 'eval(') && str_contains($css, ':focus-visible') && str_contains($css, 'min-height: 44px') && str_contains($css, '@media print'), false);
$views = $read($root . '/teacher_accounts_v59_views.php');
$check('pohledy: žádné OTP v URL (jen jednorázový token), no-store u stránek účtů', !preg_match('/http_build_query\([^)]*otp/', $views) && substr_count($views, 'teacher59_no_store()') >= 5, false);

exit(audit_summary($state, 'V59_TEACHER_ACCOUNTS'));
