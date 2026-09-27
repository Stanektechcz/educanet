<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · audit jednorázových hesel a zabezpečení účtů (SEC-01, SEC-12, SEC-16, SEC-17, Z3, Z4).
 * Běží v izolovaném dočasném úložišti (EDUCANET_STORAGE_DIR), ostrou storage/ nikdy nečte ani nemění.
 *   php tools/v58_accounts_audit.php [--teacher-root=<adresář>]
 * --teacher-root: odkud číst teacher.php a handlery pro kontrolu SEC-12 (výchozí kořen projektu).
 * Konec: V58_ACCOUNTS_AUDIT_OK checks=N failed=0 (jinak ..._FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$opts = getopt('', ['teacher-root:']);
$teacherRoot = rtrim(str_replace('\\', '/', (string)($opts['teacher-root'] ?? $root)), '/');

$tmp = str_replace('\\', '/', sys_get_temp_dir()) . '/educanet-v58-audit-' . bin2hex(random_bytes(6));
mkdir($tmp, 0770, true);
putenv('EDUCANET_STORAGE_DIR=' . $tmp);
putenv('EDUCANET_TEACHER_ROLE=teacher');
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/accounts_v58_views.php';
require_once $root . '/teacher_operations_v46.php';
unset($GLOBALS['educanet_secrets']['otp_card_key']);

$checks = 0; $failed = 0;
$check = static function (string $label, bool $ok) use (&$checks, &$failed): void {
    $checks++; if (!$ok) $failed++;
    echo ($ok ? 'PASS  ' : 'FAIL  ') . $label . PHP_EOL;
};
$throws = static function (callable $fn): bool { try { $fn(); return false; } catch (Throwable $e) { return true; } };
$read = static fn(string $path): string => (string)@file_get_contents($path);
$domain = '@' . google_workspace_domain();

$check('úložiště auditu je dočasné, ne ostrá storage/', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

// --- 1. Generátor OTP -------------------------------------------------------
$words = acc58_words();
$check('seznam slov ≥ 256, jen a–z, bez duplicit (' . count($words) . ')', count($words) >= 256 && count($words) === count(array_unique($words)) && !array_filter($words, static fn(string $w): bool => !preg_match('/^[a-z]{2,12}$/', $w)));
$check('žádné nevhodné slovo v seznamu', !array_intersect($words, ['prdel', 'kokot', 'pica', 'kurva', 'koza', 'osel', 'prase', 'debil', 'blbec', 'sex', 'drog', 'smrt', 'zabit']));
$check('entropie ≥ 36 bitů (' . round(acc58_entropy_bits(), 1) . ')', acc58_entropy_bits() >= ACC58_MIN_ENTROPY_BITS);
$check('číslice bez zaměnitelných 0/1', !str_contains(ACC58_OTP_DIGITS, '0') && !str_contains(ACC58_OTP_DIGITS, '1'));
$samples = [];
for ($i = 0; $i < 500; $i++) $samples[] = acc58_generate_otp();
$check('500 vzorků má formát slovo-číslice-slovo-slovo', !array_filter($samples, static fn(string $o): bool => !preg_match(acc58_otp_pattern(), $o)));
$check('500 vzorků je unikátních', count(array_unique($samples)) === 500);
$check('každý vzorek projde validátorem hesel', !array_filter(array_slice($samples, 0, 200), static fn(string $o): bool => local_password_validate($o) !== null));
$check('generátor používá random_int', str_contains($read($root . '/accounts_v58.php'), 'random_int('));

// --- 2. SEC-16 validátor ----------------------------------------------------
$check('SEC-16: kratší než 10 znaků neprojde', local_password_validate('abc12345') !== null);
$check('SEC-16: písmeno i číslice povinné', local_password_validate('abcdefghijk') !== null && local_password_validate('12345678901') !== null);
$check('SEC-16: demo001 je blokované (i uvnitř)', local_password_validate('demo001') !== null && local_password_validate('Xdemo001abc9') !== null);
$check('SEC-16: běžná hesla neprojdou', local_password_validate('heslo123456') !== null && local_password_validate('Password2024') !== null && local_password_validate('qwertyuiop1') !== null && local_password_validate('minecraft2010') !== null);
$check('SEC-16: jméno a e-mail v hesle neprojde', local_password_validate('novak-2024-ok', ['name' => 'Jan Novák']) !== null && local_password_validate('xjannovak99', ['email' => 'jan.novak' . $domain]) !== null);
$check('SEC-16: shoda s OTP neprojde', local_password_validate('sova-2345-mrak-kolo', ['otp' => 'sova-2345-mrak-kolo']) !== null);
$check('SEC-16: silné vlastní heslo projde (i s diakritikou)', local_password_validate('Žlutý-Kůň-2468') === null && local_password_validate('ModraVelryba77', ['name' => 'Jan Novák']) === null);
$check('SEC-16: zpětná kompatibilita bez kontextu', local_password_validate('ZelenyKopec42') === null);

// --- 3. Šifrovaná kopie ------------------------------------------------------
$backend = acc58_crypto_backend();
$check('backend šifrování zjištěn (' . $backend . ')', in_array($backend, ['sodium', 'openssl', 'none'], true));
if ($backend !== 'none') {
    $enc = acc58_encrypt('sova-2345-mrak-kolo', 'a' . $domain);
    $check('šifrovaná kopie jde dešifrovat', is_array($enc) && acc58_decrypt($enc, 'a' . $domain) === 'sova-2345-mrak-kolo');
    $check('kopie je vázaná na e-mail účtu', is_array($enc) && acc58_decrypt($enc, 'b' . $domain) === null);
    $bad = is_array($enc) ? array_replace($enc, ['c' => base64_encode(strrev((string)base64_decode($enc['c'])))]) : [];
    $check('poškozená kopie se nedešifruje', acc58_decrypt($bad, 'a' . $domain) === null);
    $check('klíč je v úložišti s ochranným řádkem', str_starts_with($read(acc58_key_path()), STORAGE_GUARD_LINE));
}

// --- 4. Staré účty (stav v57) a migrace --------------------------------------
$legacyHash = local_password_hash(ACC53_DEFAULT_PASSWORD);
$ownHash = local_password_hash('ZelenyKopec42');
$base = static fn(string $email, string $label, array $extra): array => array_replace([
    'id' => bin2hex(random_bytes(16)), 'email' => $email, 'name' => $label, 'password_hash' => $legacyHash, 'created_at' => date(DATE_ATOM),
    'verified_at' => date(DATE_ATOM), 'verified_by' => 'school_provisioning', 'verification_token_hash' => null, 'verification_expires_at' => null,
    'reset_token_hash' => null, 'reset_expires_at' => null, 'must_change_password' => true, 'initial_password' => ACC53_DEFAULT_PASSWORD,
    'class_id' => 'class_3a', 'student_label' => $label, 'demo_account' => false,
], $extra);
$eA = 'tereza.pokusna' . $domain; $eB = 'ondrej.zkusebni' . $domain; $eDemo = 'demo.3a' . $domain; $eOwn = 'marek.vlastni' . $domain;
save_php_json_map(local_accounts_path(), [
    $eA => $base($eA, 'Tereza Pokusná', []),
    $eB => $base($eB, 'Ondřej Zkušební', []),
    $eDemo => $base($eDemo, 'Demo 3.A', ['must_change_password' => false, 'demo_account' => true]),
    $eOwn => $base($eOwn, 'Marek Vlastní', ['password_hash' => $ownHash, 'must_change_password' => false, 'initial_password' => null, 'password_changed_at' => date(DATE_ATOM)]),
]);
$before = $read(local_accounts_path());
$check('před migrací: stav legacy u sdíleného hesla', acc58_otp_status(local_accounts()[$eA]) === 'legacy' && acc58_otp_status(local_accounts()[$eDemo]) === 'legacy' && acc58_otp_status(local_accounts()[$eOwn]) === 'own');
$check('login gate blokuje legacy účet', acc58_login_gate(local_accounts()[$eA]) === ACC58_MSG_LEGACY);
$dry = acc58_migrate(true);
$check('migrace --dry-run nic nemění a jen počítá', $read(local_accounts_path()) === $before && $dry['issued'] === 3 && $dry['kept_own'] === 1);
$stats = acc58_migrate(false);
$after = local_accounts();
$check('migrace vydala OTP 3 účtům (vč. demo) a vrací jen počty', $stats['issued'] === 3 && !array_filter($stats, static fn($v): bool => is_string($v)));
$check('po migraci nikde initial_password (plaintext)', !str_contains($read(local_accounts_path()), 'initial_password'));
$check('účet s vlastním heslem se nezměnil', $after[$eOwn]['password_hash'] === $ownHash && empty($after[$eOwn]['must_change_password']) && !isset($after[$eOwn]['otp']));
$check('demo001 po migraci nefunguje u žádného účtu', !array_filter($after, static fn(array $a): bool => password_verify(ACC53_DEFAULT_PASSWORD, (string)$a['password_hash'])));
$check('login gate odmítne heslo demo001 vždy', acc58_login_gate($after[$eOwn], null, ACC53_DEFAULT_PASSWORD) !== null);
$check('migrované účty jsou pending s platností 14 dní', acc58_otp_status($after[$eA]) === 'pending' && abs((int)$after[$eA]['otp']['expires_at'] - time() - 14 * 86400) < 120 && $after[$eA]['otp']['issued_by'] === 'migration');
$snapshot = $read(local_accounts_path());
$again = acc58_migrate(false);
$check('migrace je idempotentní', $again['issued'] === 0 && $read(local_accounts_path()) === $snapshot);

// --- 5. Reveal, vypršení, změna hesla ------------------------------------------
$otpA = acc58_reveal_otp($eA);
if ($backend !== 'none') {
    $check('reveal vrátí OTP jen pro pending a to OTP funguje', is_string($otpA) && preg_match(acc58_otp_pattern(), $otpA) === 1 && password_verify($otpA, (string)local_accounts()[$eA]['password_hash']));
    $check('otevřené OTP není v souboru účtů ani v logu', !str_contains($read(local_accounts_path()), (string)$otpA) && !str_contains($read(acc58_log_path()), (string)$otpA));
}
$check('reveal pro vlastní heslo vrací null', acc58_reveal_otp($eOwn) === null);
$check('login gate pustí pending účet', acc58_login_gate(local_accounts()[$eA]) === null);
acc58_account_update($eB, static function (array $a): array { $a['otp']['expires_at'] = time() - 10; return $a; });
$check('vypršelé OTP: stav expired, gate hláška, reveal null', acc58_otp_status(local_accounts()[$eB]) === 'expired' && acc58_login_gate(local_accounts()[$eB]) === ACC58_MSG_EXPIRED && acc58_reveal_otp($eB) === null);
$check('změna hesla: slabé heslo odmítnuto', acc58_change_password($eA, (string)$otpA, 'heslo12345', 'heslo12345') !== null);
if ($backend !== 'none') {
    $check('změna hesla: nové = OTP odmítnuto', acc58_change_password($eA, (string)$otpA, (string)$otpA, (string)$otpA) !== null);
    $check('změna hesla: špatné stávající heslo odmítnuto', acc58_change_password($eA, 'spatne-2345-heslo-xx', 'ModraVelryba77', 'ModraVelryba77') === 'Stávající heslo nesouhlasí.');
    $check('změna hesla s OTP projde', acc58_change_password($eA, (string)$otpA, 'ModraVelryba77', 'ModraVelryba77') === null);
    $rowA = local_accounts()[$eA];
    $check('po změně hesla zmizí otp i šifrovaná kopie, stav own', !isset($rowA['otp']) && empty($rowA['must_change_password']) && acc58_otp_status($rowA) === 'own' && acc58_reveal_otp($eA) === null);
}
acc58_mark_session_proven($eB);
$check('session s prokázaným OTP nemusí zadávat stávající heslo', acc58_change_password($eB, '', 'Kopretina-4829', 'Kopretina-4829') === null && acc58_otp_status(local_accounts()[$eB]) === 'own');

// --- 6. Nové účty, vydání pro třídu, bez šifrování ----------------------------
$new = acc53_ensure_account('Klára Nováčková', 'class_3a', ['by' => 'cli']);
$check('nový účet dostane OTP (ne demo001) a heslo se vrátí jen jednou', $new['created'] && is_string($new['password']) && password_verify($new['password'], (string)local_accounts()[$new['email']]['password_hash'])
    && !password_verify(ACC53_DEFAULT_PASSWORD, (string)local_accounts()[$new['email']]['password_hash']) && acc58_otp_status(local_accounts()[$new['email']]) === 'pending');
$check('opakované založení účet nepřepíše', acc53_ensure_account('Klára Nováčková', 'class_3a')['created'] === false);
$classRows = acc58_issue_for_class('class_3a', false, 'cli');
$check('vydání pro třídu přeskočí žáky s vlastním heslem', !isset($classRows[$eOwn]) && !isset($classRows[$eA]) && isset($classRows[$new['email']]) && isset($classRows[$eDemo]));
$check('staré heslo kartičky po novém vydání neplatí', !password_verify((string)$new['password'], (string)local_accounts()[$new['email']]['password_hash']));
acc58_crypto_force('none', true);
$plainNone = acc58_issue_otp($eDemo, 'teacher');
$check('bez sodium/openssl se šifrovaná kopie neukládá', !isset(local_accounts()[$eDemo]['otp']['enc']) && acc58_reveal_otp($eDemo) === null && password_verify($plainNone, (string)local_accounts()[$eDemo]['password_hash']));
acc58_crypto_force(null, true);
$check('acc53_reset_password vydá OTP', preg_match(acc58_otp_pattern(), acc53_reset_password($eDemo, 'cli')) === 1);

// --- 7. Log a automatická migrace ---------------------------------------------
$logRaw = $read(acc58_log_path());
$check('log vydání existuje, má ochranný řádek a neobsahuje hesla', str_starts_with($logRaw, STORAGE_GUARD_LINE) && !str_contains($logRaw, (string)$plainNone) && !str_contains($logRaw, '"password"'));
acc58_log_many(array_fill(0, 2100, acc58_log_entry('issue', 'x' . $domain, 'class_3a', 'cli')));
$check('log drží nejvýš 2000 záznamů', count(load_php_json(acc58_log_path())) === ACC58_LOG_LIMIT);
acc58_auto_migrate();
$check('auto-migrace zapíše verzi a běží jen jednou', (int)(load_php_json(acc58_state_path())['migration_version'] ?? 0) === ACC58_MIGRATION_VERSION);

// --- 8. Z4 atomické úpravy, Z3 propojení ---------------------------------------
$check('acc58_account_update na neexistující účet nic nezapíše', acc58_account_update('nikdo' . $domain, static fn(array $a): array => $a) === null && !isset(local_accounts()['nikdo' . $domain]));
$check('acc58_account_insert nepřepíše existující účet', acc58_account_insert($eOwn, ['email' => $eOwn]) === false && local_accounts()[$eOwn]['password_hash'] === $ownHash);
$before = (string)(local_accounts()[$eOwn]['last_login_at'] ?? '');
acc53_touch_login($eOwn); $first = (string)local_accounts()[$eOwn]['last_login_at'];
acc58_account_update($eOwn, static fn(array $a): array => array_replace($a, ['last_login_at' => '2020-01-01T00:00:00+00:00']));
acc53_touch_login($eOwn);
$check('touch_login zapisuje nejvýš jednou za hodinu', $first !== '' && local_accounts()[$eOwn]['last_login_at'] !== '2020-01-01T00:00:00+00:00');
acc58_account_update($eOwn, static fn(array $a): array => array_replace($a, ['last_login_at' => date(DATE_ATOM, time() - 60)]));
$stamp = (string)local_accounts()[$eOwn]['last_login_at']; acc53_touch_login($eOwn);
$check('touch_login do hodiny nezapisuje', (string)local_accounts()[$eOwn]['last_login_at'] === $stamp);
$ownId = (string)local_accounts()[$eOwn]['id'];
save_php_json_map(STORAGE_DIR . '/student_accounts.json.php', ['local:' . $ownId => ['provider' => 'local', 'class_id' => 'class_3a', 'student_label' => 'Marek Vlastní'], 'google-cizi' => ['provider' => 'google', 'class_id' => 'class_3a', 'student_label' => 'Petra Obsazená']]);
$check('Z3: jméno navázané na jiný (Google) účet nejde převzít', acc58_link_allowed('class_3a', 'Petra Obsazená', 'google-novy') !== null);
$check('Z3: jméno školního účtu vyžaduje heslo', acc58_link_allowed('class_3a', 'Marek Vlastní', 'google-novy') !== null && acc58_link_allowed('class_3a', 'Marek Vlastní', 'google-novy', 'spatne-heslo-22') !== null);
$check('Z3: správné heslo školního účtu propojení povolí', acc58_link_allowed('class_3a', 'Marek Vlastní', 'google-novy', 'ZelenyKopec42') === null);
$check('Z3: volné jméno bez účtu projde, vlastní účet projde', acc58_link_allowed('class_3a', 'Nikdo Neobsazený', 'google-novy') === null && acc58_link_allowed('class_3a', 'Marek Vlastní', 'local:' . $ownId) === null);
$check('Z3: demo001 propojení nepotvrdí', acc58_link_allowed('class_3a', 'Demo 3.A', 'google-novy', ACC53_DEFAULT_PASSWORD) !== null);

// --- 9. Učitelské akce: CSRF a oprávnění --------------------------------------
$_SESSION['csrf'] = 'audit-csrf-' . bin2hex(random_bytes(8));
$_SESSION['teacher_export_authenticated'] = true;
$mods = ['class_3a' => ['name' => '3.A', 'subject' => 'Sítě'], 'class_2a' => ['name' => '2.A', 'subject' => 'Grafika']];
ob_start(); acc58_render_teacher_accounts('class_3a', $_SESSION['csrf'], $mods); $html = (string)ob_get_clean();
$check('tabulka přístupů obsahuje CSRF token a obě akce', str_contains($html, 'name="csrf" value="' . $_SESSION['csrf'] . '"') && str_contains($html, 'value="acc58_issue_one"') && str_contains($html, 'value="acc58_issue_class"'));
$check('tabulka přístupů neukazuje hesla ani šifrovanou kopii', !str_contains($html, '"alg"') && !str_contains($html, (string)$plainNone) && str_contains($html, 'Tisk kartiček'));
$_POST = ['action' => 'acc58_issue_one', 'class_id' => 'class_3a', 'email' => $eOwn];
$check('handler bez CSRF tokenu odmítne', $throws(static fn() => acc58_teacher_handle_post('acc58_issue_one', $mods)));
$_POST['csrf'] = 'spatny-token';
$check('handler se špatným tokenem odmítne', $throws(static fn() => acc58_teacher_handle_post('acc58_issue_one', $mods)));
$_SESSION['teacher_export_authenticated'] = false;
$check('nepřihlášený učitel nic nevydá', $throws(static fn() => acc58_teacher_apply('acc58_issue_one', ['class_id' => 'class_3a', 'email' => $eOwn], $mods)));
$_SESSION['teacher_export_authenticated'] = true;
putenv('EDUCANET_TEACHER_ROLE=assistant');
$check('role asistent (bez students.manage) nic nevydá', $throws(static fn() => acc58_teacher_apply('acc58_issue_one', ['class_id' => 'class_3a', 'email' => $eOwn], $mods)));
putenv('EDUCANET_TEACHER_ROLE=teacher');
$check('žák z jiné třídy / neznámá třída odmítnuta', $throws(static fn() => acc58_teacher_apply('acc58_issue_one', ['class_id' => 'class_2a', 'email' => $eOwn], $mods)) && $throws(static fn() => acc58_teacher_apply('acc58_issue_class', ['class_id' => 'class_9x'], $mods)));
$res = acc58_teacher_apply('acc58_issue_one', ['class_id' => 'class_3a', 'email' => $eOwn], $mods);
$check('učitel vydá heslo a oznámení ho ukáže jen jednou', $res['reveal']['email'] === $eOwn && acc58_otp_status(local_accounts()[$eOwn]) === 'pending');
if ($backend !== 'none') {
    $_SESSION['acc58_reveal'] = $res['reveal'];
    ob_start(); acc58_render_teacher_accounts('class_3a', $_SESSION['csrf'], $mods); $html1 = (string)ob_get_clean();
    ob_start(); acc58_render_teacher_accounts('class_3a', $_SESSION['csrf'], $mods); $html2 = (string)ob_get_clean();
    $otpOwn = (string)acc58_reveal_otp($eOwn);
    $check('nové heslo je v oznámení jen při prvním zobrazení', $otpOwn !== '' && str_contains($html1, $otpOwn) && !str_contains($html2, $otpOwn));
    ob_start(); acc58_render_cards_print('class_3a', $mods); $print = (string)ob_get_clean();
    $check('tisk kartiček: heslo pending žáka, e-mail, platnost, návod, @media print v CSS', str_contains($print, $otpOwn) && str_contains($print, e($eOwn)) && str_contains($print, 'Platí do') && substr_count($print, '<li>') >= 3
        && str_contains($read($root . '/assets/accounts-v58.css'), '@media print'));
}
$check('výsledek vydání třídy nevrací hesla do oznámení', !isset(acc58_teacher_apply('acc58_issue_class', ['class_id' => 'class_3a'], $mods)['reveal']));

// --- 10. SEC-17 obnova hesla --------------------------------------------------
$token = local_password_reset_request($eOwn, false);
$stored = local_accounts()[$eOwn];
$check('SEC-17: token 64 hex, uložen jen hash, platnost ≤ 30 min', is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token) === 1 && $stored['reset_token_hash'] === hash('sha256', $token) && (int)$stored['reset_expires_at'] <= time() + 1800);
$check('SEC-17: neexistující e-mail nic nevydá', local_password_reset_request('nikdo' . $domain, false) === null);
$check('SEC-17: výměna tokenu za session (bez tokenu v session)', local_password_reset_exchange((string)$token) && !str_contains((string)json_encode($_SESSION), (string)$token) && local_password_reset_session_email() === $eOwn);
$check('SEC-17: slabé heslo odmítnuto, token zůstává', local_password_reset_complete('abc', 'abc') !== null && local_password_reset_session_email() === $eOwn);
$check('SEC-17: reset projde a zruší OTP i vynucenou změnu', local_password_reset_complete('HnedaVeverka58', 'HnedaVeverka58') === null && acc58_otp_status(local_accounts()[$eOwn]) === 'own' && password_verify('HnedaVeverka58', (string)local_accounts()[$eOwn]['password_hash']));
$check('SEC-17: token je jednorázový', !local_password_reset_exchange((string)$token) && local_password_reset_complete('JinaVeverka59', 'JinaVeverka59') !== null);
$token2 = (string)local_password_reset_request($eOwn, false);
acc58_account_update($eOwn, static fn(array $a): array => array_replace($a, ['reset_expires_at' => time() - 1]));
$check('SEC-17: vypršelý token neprojde', !local_password_reset_exchange($token2));
$bootSrc = $read($root . '/bootstrap.php');
$check('SEC-17: odkaz v e-mailu nenese e-mail, stránka má Referrer-Policy no-referrer', !str_contains((string)strstr($bootSrc, 'function send_local_password_reset'), "reset_password&email=") && str_contains($bootSrc, "header('Referrer-Policy: no-referrer')"));

// --- 11. Texty a guardy ----------------------------------------------------------
$check('texty žáka nezmiňují demo001', !str_contains($read($root . '/session_v53_views.php'), 'ACC53_DEFAULT_PASSWORD') && !str_contains($read($root . '/session_v53_views.php'), 'demo001'));
$check('registrace kódem hodiny nepustí do existujícího účtu', !str_contains($read($root . '/session_v53.php'), 'password_verify(ACC53_DEFAULT_PASSWORD'));
foreach (['accounts_v53.php', 'accounts_v58.php', 'accounts_v58_views.php', 'session_v53.php', 'session_v53_views.php'] as $f) {
    $check('guard knihovny ' . $f, str_contains($read($root . '/' . $f), "=== basename(__FILE__)) { http_response_code(403); exit; }"));
}
foreach (['tools/v58_issue_passwords.php', 'tools/v58_accounts_audit.php', 'tools/v53_provision_accounts.php'] as $f) {
    $check('CLI guard ' . $f, str_contains($read($root . '/' . $f), "PHP_SAPI !== 'cli'"));
}

// --- 12. SEC-12 deny-by-default: každá učitelská POST akce má mapování -----------
$src = static fn(string $rel): string => $read($teacherRoot . '/' . $rel);
$actions = [];
$teacherSrc = $src('teacher.php');
$collect = static function (string $code) use (&$actions): void {
    preg_match_all('/\$action\s*===\s*\'([a-z0-9_]+)\'/', $code, $m); foreach ($m[1] as $a) $actions[$a] = true;
    preg_match_all('/in_array\(\$action\s*,\s*\[([^\]]*)\]/', $code, $m);
    foreach ($m[1] as $list) { preg_match_all('/\'([a-z0-9_]+)\'/', $list, $mm); foreach ($mm[1] as $a) $actions[$a] = true; }
    preg_match_all('/case\s+\'([a-z0-9_]+)\'\s*:/', $code, $m); foreach ($m[1] as $a) $actions[$a] = true;
};
$postBlock = (string)strstr($teacherSrc, "if (\$action === 'teacher_login')");
$collect(substr($postBlock, 0, max(0, (int)strpos($postBlock, '$rawFlash'))));
foreach (['intake_v51_teacher.php' => 'intake_v51_teacher_handle_post', 'session_v53_teacher.php' => 'sess53_teacher_handle_post', 'arena_v57.php' => 'arena57_teacher_handle_post', 'accounts_v58_views.php' => 'acc58_teacher_apply'] as $file => $fn) {
    $code = $src($file);
    $body = (string)strstr($code, 'function ' . $fn . '(');
    $next = strpos($body, "\nfunction ", 10);
    $collect($next === false ? $body : substr($body, 0, $next));
}
unset($actions['teacher_login'], $actions['teacher_logout']);
$unmapped = array_keys(array_filter($actions, static fn(bool $v, string $a): bool => teacher_action_permission($a) === null || teacher_action_permission($a) === TEACHER_PERMISSION_DENY, ARRAY_FILTER_USE_BOTH));
$check('SEC-12: nalezeno ' . count($actions) . ' učitelských akcí, všechny mají oprávnění' . ($unmapped ? ' – CHYBÍ: ' . implode(', ', $unmapped) : ''), count($actions) >= 60 && !$unmapped);
$check('SEC-12: neznámá akce je zamítnuta všem (i adminovi)', teacher_action_permission('neznama_akce_x') === TEACHER_PERMISSION_DENY && !teacher_permission(TEACHER_PERMISSION_DENY) && $throws(static fn() => teacher_require_permission(TEACHER_PERMISSION_DENY)));
$check('SEC-12: teacher.php ověřuje oprávnění před handlery', (bool)preg_match('/teacher_action_permission\(\$action\).{0,120}teacher_require_permission\(\$requiredPermission\).{0,80}intake_v51_teacher_handle_post/s', $teacherSrc));
$check('SEC-12: acc58 akce jsou volané z teacher.php [čeká na patch INTEGRATION.md#sec-01-teacher]', str_contains($teacherSrc, 'acc58_teacher_handle_post($action,$modules)') || str_contains($teacherSrc, 'acc58_teacher_handle_post($action, $modules)'));

// --- 13. Revize v58 · skupina A (přihlášení, účty) -------------------------------
require_once $root . '/intake_v51.php';
require_once $root . '/session_v53.php';
$resetLimits = static function (): void { unset($_SESSION['auth_rate_limits']); @unlink(auth_rate_limit_path()); php_json_cache_forget(auth_rate_limit_path()); };
$as = static function (string $ip, callable $fn) { $prev = $_SERVER['REMOTE_ADDR'] ?? null; $_SERVER['REMOTE_ADDR'] = $ip; unset($_SESSION['auth_rate_limits']); try { return $fn(); } finally { $_SERVER['REMOTE_ADDR'] = $prev; } };
$regClass = intake_v51_classes($modules)['class_1a'] ?? [];
$regSeat = '';
foreach ((array)($regClass['seats'] ?? []) as $seatRow) { if (!empty($seatRow['active'])) { $regSeat = (string)$seatRow['id']; break; } }
$intakeSession = ['id' => 'audit-intake', 'class_id' => 'class_1a', 'kind' => 'intake', 'open' => true, 'code' => 'AUD123', 'title' => 'Audit'];
$regPost = static fn(string $first, string $last): array => ['first_name' => $first, 'last_name' => $last, 'email' => '', 'seat_id' => $regSeat];
unset($_SESSION['local_user'], $_SESSION['google_user']);
$regErr = static function (array $session, array $post) use ($modules): string {
    try { @sess53_join_register($session, $modules, $post); return ''; } catch (Throwable $e) { return $e->getMessage(); }
};
$check('SEC58-01: kód pracovní hodiny účet nezaloží', $regErr(array_replace($intakeSession, ['kind' => 'work']), $regPost('Pracovní', 'Hodina')) === SESS53_REGISTER_CLOSED && !array_filter(local_accounts(), static fn($a): bool => ($a['name'] ?? '') === 'Pracovní Hodina'));
$check('SEC58-01: zavřená seznamovací hodina účet nezaloží', $regErr(array_replace($intakeSession, ['open' => false]), $regPost('Zavřená', 'Hodina')) === SESS53_REGISTER_CLOSED);
$_SESSION['local_user'] = ['email' => $eOwn, 'provider' => 'local', 'id' => 'x', 'name' => 'Marek Vlastní'];
$check('SEC58-01: přihlášený uživatel přes kód hodiny nezaloží další účet', $regErr($intakeSession, $regPost('Druhý', 'Účet')) !== '' && !array_filter(local_accounts(), static fn($a): bool => ($a['name'] ?? '') === 'Druhý Účet'));
unset($_SESSION['local_user']);
if ($regSeat !== '') {
    $firstReg = $regErr($intakeSession, $regPost('Nový', 'Prvák'));
    $newPrvak = array_values(array_filter(local_accounts(), static fn($a): bool => ($a['name'] ?? '') === 'Nový Prvák'));
    $check('SEC58-01: nové jméno v seznamovací hodině účet založí (' . $firstReg . ')', $firstReg === '' && count($newPrvak) === 1);
    unset($_SESSION['local_user'], $_SESSION['google_user']);
    $check('SEC58-01: stejné jméno ve třídě podruhé odmítnuto (jednotná hláška)', $regErr($intakeSession, $regPost('Nový', 'Prvák')) === SESS53_REGISTER_EXISTS
        && count(array_filter(local_accounts(), static fn($a): bool => ($a['name'] ?? '') === 'Nový Prvák')) === 1);
    unset($_SESSION['local_user'], $_SESSION['google_user']);
    storage_update(STORAGE_DIR . '/student_accounts.json.php', static fn(array $m): array => array_replace($m, ['google-prvak' => ['provider' => 'google', 'class_id' => 'class_1a', 'student_label' => 'Jana Googlová']]));
    $check('SEC58-01: jméno propojené s Google účtem odmítnuto', $regErr($intakeSession, $regPost('Jana', 'Googlová')) === SESS53_REGISTER_EXISTS
        && !array_filter(local_accounts(), static fn($a): bool => ($a['name'] ?? '') === 'Jana Googlová'));
} else {
    $check('SEC58-01: třída 1.A má aktivní místo pro test registrace', false);
}
unset($_SESSION['local_user'], $_SESSION['google_user']);
$joinSrc = $read($root . '/app/actions/session_join.php');
$check('SEC58-01: registrace má limit pokusů na IP (40/15 min)', str_contains($joinSrc, "auth_rate_limit_check('sess53-register', SESS53_REGISTER_LIMIT, SESS53_REGISTER_WINDOW)") && SESS53_REGISTER_LIMIT === 40 && SESS53_REGISTER_WINDOW === 900);
$check('SEC58-01: registrace volá acc58_link_allowed', str_contains((string)strstr($read($root . '/session_v53.php'), 'function sess53_join_register'), 'acc58_link_allowed('));

// SEC58-02: odkazy do e-mailu jen z EDUCANET_APP_URL, bez e-mailu v URL, limit 3/h
$prevAppUrl = getenv('EDUCANET_APP_URL'); $prevBypass = getenv('EDUCANET_DEV_BYPASS');
$prevErrorLog = (string)ini_get('error_log'); ini_set('error_log', $tmp . '/audit-error.log'); // varování o chybějící adrese jdou do logu
putenv('EDUCANET_APP_URL'); putenv('EDUCANET_DEV_BYPASS');
$_SERVER['HTTP_HOST'] = 'utocnik.example';
$check('SEC58-02: bez EDUCANET_APP_URL se odkaz do e-mailu nesestaví (Host se nepoužije)', @auth_mail_base_url() === null && @send_local_password_reset(['email' => $eOwn], str_repeat('a', 64)) === false && @send_local_email_verification(['email' => $eOwn], str_repeat('a', 64)) === false);
putenv('EDUCANET_APP_URL=javascript:alert(1)');
$check('SEC58-02: neplatné EDUCANET_APP_URL se nepoužije', @auth_mail_base_url() === null);
putenv('EDUCANET_APP_URL=https://lab.skola.example/');
$check('SEC58-02: odkaz se skládá z EDUCANET_APP_URL', auth_mail_base_url() === 'https://lab.skola.example');
putenv('EDUCANET_APP_URL');
putenv('EDUCANET_DEV_BYPASS=1');
$check('SEC58-02: lokální vývoj (DEV_BYPASS) smí použít adresu požadavku', is_string(auth_mail_base_url()));
putenv('EDUCANET_DEV_BYPASS');
$verifySrc = (string)strstr($bootSrc, 'function send_local_email_verification');
$verifySrc = substr($verifySrc, 0, (int)strpos($verifySrc, "\nfunction ", 10));
$check('SEC58-02: ověřovací odkaz nenese e-mail', !str_contains($verifySrc, 'email=') && str_contains($verifySrc, 'verify_email&token='));
$check('SEC58-02: stránka ověření hledá účet jen podle tokenu', str_contains($read($root . '/app/views/auth_links.php'), 'local_email_verification_consume(') && !str_contains($read($root . '/app/views/auth_links.php'), "\$_GET['email']"));
$verAccount = ['id' => bin2hex(random_bytes(16)), 'email' => 'overeni' . $domain, 'name' => 'Ověřovací Žák', 'password_hash' => $ownHash, 'verified_at' => null];
$verToken = issue_local_email_verification($verAccount);
acc58_account_insert('overeni' . $domain, $verAccount);
$check('SEC58-02: token bez e-mailu ověří správný účet a je jednorázový', (local_email_verification_consume($verToken)['email'] ?? '') === 'overeni' . $domain
    && !empty(local_accounts()['overeni' . $domain]['verified_at']) && local_email_verification_consume($verToken) === null && local_email_verification_consume('nesmysl') === null);
$verAccount2 = array_replace($verAccount, ['email' => 'overeni2' . $domain, 'id' => bin2hex(random_bytes(16))]);
$verToken2 = issue_local_email_verification($verAccount2);
$verAccount2['verification_expires_at'] = time() - 5;
acc58_account_insert('overeni2' . $domain, $verAccount2);
$check('SEC58-02: vypršelý ověřovací token neprojde', local_email_verification_consume($verToken2) === null && empty(local_accounts()['overeni2' . $domain]['verified_at']));
$resetLimits();
$mailHits = $as('198.51.100.10', static fn(): array => [auth_mail_rate_limit_hit($eOwn), auth_mail_rate_limit_hit($eOwn)]);
$mailHits[] = $as('198.51.100.11', static fn(): bool => auth_mail_rate_limit_hit($eOwn));
$check('SEC58-02: 3 e-maily/h na adresu projdou, 4. z jiné IP ne', $mailHits === [true, true, true] && $as('198.51.100.12', static fn(): bool => auth_mail_rate_limit_hit($eOwn)) === false);
$check('SEC58-02: obnova hesla po vyčerpání limitu nic nevydá', $as('198.51.100.13', static fn() => @local_password_reset_request($eOwn, true)) === null);
$check('SEC58-02: opětovné ověření používá limit e-mailů', str_contains($read($root . '/app/actions/auth.php'), 'auth_mail_rate_limit_hit($email) && $account'));
if ($prevAppUrl !== false) putenv('EDUCANET_APP_URL=' . $prevAppUrl);
if ($prevBypass !== false) putenv('EDUCANET_DEV_BYPASS=' . $prevBypass);
$check('SEC58-02: chybějící EDUCANET_APP_URL se zaloguje jako varování', str_contains($read($tmp . '/audit-error.log'), 'chybí EDUCANET_APP_URL'));
ini_set('error_log', $prevErrorLog);

// SEC58-12: limity přihlášení (IP napříč účty, účet napříč IP, učitel)
$resetLimits();
$check('SEC58-12: bucket global: nezávisí na IP', $as('198.51.100.1', static fn(): string => auth_rate_limit_key('global:x')) === $as('198.51.100.2', static fn(): string => auth_rate_limit_key('global:x'))
    && $as('198.51.100.1', static fn(): string => auth_rate_limit_key('x')) !== $as('198.51.100.2', static fn(): string => auth_rate_limit_key('x')));
for ($i = 0; $i < AUTH_LOGIN_LIMIT_ACCOUNT; $i++) $as('198.51.100.' . (20 + intdiv($i, 5)), static fn() => auth_rate_limit_fail_all(auth_login_buckets($eOwn)));
$check('SEC58-12: účet se po ' . AUTH_LOGIN_LIMIT_ACCOUNT . ' neúspěších z více IP zamkne i pro novou IP', $as('198.51.100.99', static fn(): bool => auth_rate_limit_check_all(auth_login_buckets($eOwn), AUTH_LOGIN_WINDOW)) === false
    && $as('198.51.100.99', static fn(): bool => auth_rate_limit_check_all(auth_login_buckets('jiny' . $domain), AUTH_LOGIN_WINDOW)) === true);
$resetLimits();
for ($i = 0; $i < AUTH_LOGIN_LIMIT_IP; $i++) $as('203.0.113.5', static fn() => auth_rate_limit_fail_all(auth_login_buckets('zak' . $i . $domain)));
$check('SEC58-12: IP se po ' . AUTH_LOGIN_LIMIT_IP . ' neúspěších napříč účty zablokuje, jiná IP ne', $as('203.0.113.5', static fn(): bool => auth_rate_limit_check_all(auth_login_buckets('dalsi' . $domain), AUTH_LOGIN_WINDOW)) === false
    && $as('203.0.113.6', static fn(): bool => auth_rate_limit_check_all(auth_login_buckets('dalsi' . $domain), AUTH_LOGIN_WINDOW)) === true);
$resetLimits();
$_SERVER['HTTP_USER_AGENT'] = 'StudentBrowser/1.0';
for ($i = 0; $i < TEACHER_LOGIN_LIMIT_CLIENT; $i++) $as('203.0.113.7', static fn() => auth_rate_limit_fail_all(teacher_login_buckets()));
$studentBlocked = $as('203.0.113.7', static fn(): bool => auth_rate_limit_check_all(teacher_login_buckets(), TEACHER_LOGIN_WINDOW)) === false;
$_SERVER['HTTP_USER_AGENT'] = 'TeacherBrowser/2.0';
$check('SEC58-12: žák za školní IP učitele nezamkne (limit IP + prohlížeč), sám zablokován je', $studentBlocked && $as('203.0.113.7', static fn(): bool => auth_rate_limit_check_all(teacher_login_buckets(), TEACHER_LOGIN_WINDOW)) === true);
unset($_SERVER['HTTP_USER_AGENT']);
$resetLimits();
$authSrc = $read($root . '/app/actions/auth.php');
$check('SEC58-12: přihlášení žáka používá všechny tři limity', str_contains($authSrc, 'auth_rate_limit_check_all($loginBuckets, AUTH_LOGIN_WINDOW)') && str_contains($authSrc, 'auth_rate_limit_fail_all($loginBuckets)'));
$check('SEC58-12: registrace s ověřením neprozradí existující účet', substr_count($authSrc, '$_SESSION[\'flash\'] = $registerGeneric;') >= 3);
$check('SEC58-12: učitelské přihlášení používá limit IP + prohlížeč', str_contains($teacherSrc, 'teacher_login_buckets()') && str_contains($teacherSrc, 'auth_rate_limit_fail_all($teacherLoginBuckets)'));

// SEC58-08, SEC58-15, PRIV58-13
$check('SEC58-08: progress.php odmítne účet s vynucenou změnou hesla', (bool)preg_match('/acc53_must_change_password\(\).{0,120}http_response_code\(403\)/s', $read($root . '/progress.php')));
$check('SEC58-15: odhlášení učitele mění ID session', (bool)preg_match("/'teacher_logout'.{0,260}session_regenerate_id\\(true\\)/s", $teacherSrc));
$linkView = $read($root . '/app/views/link_account.php');
$check('PRIV58-13: stránka propojení nevypisuje adresář žáků', !str_contains($linkView, 'student_directory(') && !str_contains($linkView, '<option value="<?= e($key)') && str_contains($linkView, 'name="student_name"'));
$check('PRIV58-13: jméno se ověřuje na serveru jen proti vybrané třídě', str_contains($authSrc, "\$_POST['student_name']") && str_contains($authSrc, "!== \$targetClass) continue;"));

// Úklid dočasného úložiště
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($it as $file) { $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname()); }
@rmdir($tmp);

echo ($failed === 0 ? 'V58_ACCOUNTS_AUDIT_OK' : 'V58_ACCOUNTS_AUDIT_FAIL') . " checks={$checks} failed={$failed}" . PHP_EOL;
exit($failed === 0 ? 0 : 1);
