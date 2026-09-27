<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v59 · AUTHZ58-07 – účty učitelů: úložiště, přihlášení, relace, jednorázová hesla, log, převzetí starých dat.
 *
 * Úložiště storage/teacher_accounts_v59.json.php (zápis jen přes storage_update):
 *   { "mode":"accounts", "version":1, "dummy_hash":"…", "accounts": { "t_<16hex>": { id, login, display_name,
 *     role admin|teacher|assistant, assignments [{class_id, subject_id|"*"}], password_hash, must_change_password,
 *     otp {issued_at, expires_at, issued_by}, status active|disabled, session_version, created_at, created_by,
 *     updated_at, updated_by, last_login_at, password_changed_at, disabled_at } } }
 * Režim: soubor neexistuje = legacy (sdílený klíč jako dosud); existuje a jde přečíst = accounts;
 * existuje, ale nejde přečíst / nemá mode=accounts = broken (fail closed, 503 – nikdy automaticky legacy).
 * SEC59-05: pojistka mimo úložiště EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1 (nebo tajemství teacher_accounts_required) –
 * chybějící soubor účtů je pak také broken (obnova staré zálohy / jiný EDUCANET_STORAGE_DIR nevrátí sdílený klíč).
 * Otevřené heslo ani jednorázové heslo se nikdy neukládá ani neloguje; log je bez IP adres.
 * Log: bezpečnostní události v teacher_accounts_v59_log.json.php; hlučné (neúspěšné přihlášení, zámek, sdílený klíč,
 * zamítnutí rozsahu) zvlášť v teacher_accounts_v59_noise.json.php s limitem na událost a aktéra/IP za minutu (SEC59-04).
 * Zámek přihlášení (SEC59-03): login + IP klienta (10 chyb / 15 min) = dočasný zámek jen té IP; login napříč IP
 * (200 / 15 min) jen zpomaluje. Úložiště teacher_accounts_v59_locks.json.php (solené hashe, žádný login ani IP).
 * Obnova přístupu jen přes CLI: php tools/v59_teacher_accounts.php reset --login=…
 */

const TEACHER59_STORE_VERSION = 1;
const TEACHER59_ROLES = ['admin', 'teacher', 'assistant'];
const TEACHER59_LOGIN_RE = '/^[a-z0-9._-]{3,40}$/D';
const TEACHER59_OTP_TTL = 259200;          // 72 h
const TEACHER59_IDLE_TIMEOUT = 3600;       // 60 min nečinnosti
const TEACHER59_ABSOLUTE_TIMEOUT = 36000;  // 10 h od přihlášení
const TEACHER59_PASSWORD_MIN = 12;
const TEACHER59_LOCK_LIMIT = 10;           // chyb z jedné IP na jeden login → dočasný zámek této IP
const TEACHER59_LOCK_WINDOW = 900;
const TEACHER59_THROTTLE_LIMIT = 200;      // chyb napříč IP na jeden login → jen zpomalení (nikdy tvrdý zámek)
const TEACHER59_THROTTLE_STEP_MS = 250;
const TEACHER59_THROTTLE_MAX_MS = 3000;
const TEACHER59_LOCK_MAX_LOGINS = 5000;
const TEACHER59_LOG_LIMIT = 5000;
const TEACHER59_NOISE_LIMIT = 2000;
const TEACHER59_NOISE_PER_MINUTE = 5;
const TEACHER59_NOISE_EVENTS = ['login_fail', 'locked', 'throttled', 'legacy_key_used', 'scope_denied'];
const TEACHER59_CARD_TTL = 600;
const TEACHER59_OTP_WORD_COUNT = 4;
const TEACHER59_REQUIRED_ENV = 'EDUCANET_TEACHER_ACCOUNTS_REQUIRED';
const TEACHER59_SESSION_KEYS = ['teacher59', 'teacher_export_authenticated', 'teacher_display_name', 'teacher59_card', 'teacher59_notice', 'acc58_reveal', 'acc58_once_cards',
    'teacher_saved_filter_actor_token', 'teacher_demo_credentials', 'teacher_export_flash'];
const TEACHER59_MSG_LOGIN = 'Přihlášení se nepodařilo. Zkontrolujte přihlašovací jméno a heslo.';
const TEACHER59_MSG_LIMIT = 'Příliš mnoho pokusů, zkuste to za pár minut.';
const TEACHER59_MSG_OTP_EXPIRED = 'Jednorázové heslo vypršelo – požádejte správce o nové.';
const TEACHER59_MSG_EXPIRED = 'Relace vypršela. Přihlaste se prosím znovu.';
const TEACHER59_MSG_RECLAIM_NAME = 'Stará data jsou vedená pod jiným jménem, než má váš účet. Sami je převzít nejde – o převedení požádejte administrátora školy.';

function teacher59_accounts_path(): string { return STORAGE_DIR . '/teacher_accounts_v59.json.php'; }
function teacher59_log_path(): string { return STORAGE_DIR . '/teacher_accounts_v59_log.json.php'; }
function teacher59_noise_path(): string { return STORAGE_DIR . '/teacher_accounts_v59_noise.json.php'; }
function teacher59_locks_path(): string { return STORAGE_DIR . '/teacher_accounts_v59_locks.json.php'; }

// SEC59-03/04: zámek přihlášení (login + IP, zpomalení napříč IP) a hlučný log s limitem.
require_once __DIR__ . '/teacher_accounts_v59_limits.php';

/** SEC59-05: pojistka mimo úložiště – účty jsou povinné (chybějící soubor = broken, nikdy legacy). */
function teacher59_accounts_required(): bool
{
    $truthy = static fn($v): bool => is_scalar($v) && in_array(strtolower(trim((string)$v)), ['1', 'true', 'yes', 'on'], true);
    $env = getenv(TEACHER59_REQUIRED_ENV);
    if ($truthy($env) || $truthy($_SERVER[TEACHER59_REQUIRED_ENV] ?? null) || $truthy($_ENV[TEACHER59_REQUIRED_ENV] ?? null)) return true;
    return function_exists('educanet_secret') && $truthy(educanet_secret('teacher_accounts_required'));
}

/** Mezipaměť v rámci jednoho požadavku (aktuální účet, stav relace). */
function &teacher59_cache(): array
{
    static $cache = [];
    return $cache;
}

function teacher59_reset_cache(): void
{
    $cache = &teacher59_cache();
    $cache = [];
}

/** legacy | accounts | broken */
function teacher59_mode(): string
{
    $path = teacher59_accounts_path();
    if (!is_file($path)) return teacher59_accounts_required() ? 'broken' : 'legacy';
    try {
        $store = storage_read($path, true);
    } catch (Throwable $e) {
        return 'broken';
    }
    return ((string)($store['mode'] ?? '') === 'accounts' && is_array($store['accounts'] ?? null)) ? 'accounts' : 'broken';
}

function teacher59_is_legacy(): bool
{
    return teacher59_mode() === 'legacy';
}

/** Celé úložiště účtů; legacy = []; nečitelné úložiště = výjimka (fail closed). */
function teacher59_store_read(): array
{
    $mode = teacher59_mode();
    if ($mode === 'legacy') return [];
    if ($mode === 'broken') {
        throw new RuntimeException(is_file(teacher59_accounts_path()) ? 'Úložiště učitelských účtů nejde přečíst. Obnovte ho ze zálohy.'
            : 'Soubor učitelských účtů chybí, ale účty jsou povinné (' . TEACHER59_REQUIRED_ENV . '). Obnovte ho ze zálohy.');
    }
    return storage_read(teacher59_accounts_path(), true);
}

/** @return array<string, array<string, mixed>> */
function teacher59_accounts(): array
{
    $accounts = teacher59_store_read()['accounts'] ?? [];
    return is_array($accounts) ? array_filter($accounts, 'is_array') : [];
}

function teacher59_account(string $id): ?array
{
    if ($id === '') return null;
    $row = teacher59_accounts()[$id] ?? null;
    return is_array($row) ? $row : null;
}

function teacher59_normalize_login(string $login): string
{
    return strtolower(trim($login));
}

function teacher59_valid_login(string $login): bool
{
    return preg_match(TEACHER59_LOGIN_RE, $login) === 1;
}

function teacher59_find_by_login(string $login): ?array
{
    $login = teacher59_normalize_login($login);
    if (!teacher59_valid_login($login)) return null;
    foreach (teacher59_accounts() as $row) {
        if ((string)($row['login'] ?? '') === $login) return $row;
    }
    return null;
}

/** RMW úložiště účtů pod jedním zámkem; první zápis založí režim accounts. */
function teacher59_store_update(callable $fn): array
{
    $new = storage_update(teacher59_accounts_path(), static function (array $store) use ($fn): array {
        if ($store === []) {
            $store = ['mode' => 'accounts', 'version' => TEACHER59_STORE_VERSION, 'accounts' => []];
        } elseif ((string)($store['mode'] ?? '') !== 'accounts' || !is_array($store['accounts'] ?? null)) {
            throw new RuntimeException('Úložiště učitelských účtů je poškozené, zápis byl zastaven.');
        }
        if (empty($store['dummy_hash'])) $store['dummy_hash'] = local_password_hash(bin2hex(random_bytes(16)));
        $out = $fn($store);
        if (!is_array($out) || !is_array($out['accounts'] ?? null)) throw new RuntimeException('Úprava účtů nevrátila platná data.');
        return $out;
    });
    teacher59_reset_cache();
    return $new;
}

/** Úprava jednoho účtu; $fn(array $account, array $accounts): array. */
function teacher59_account_update(string $id, callable $fn): array
{
    $result = [];
    teacher59_store_update(static function (array $store) use ($id, $fn, &$result): array {
        $row = $store['accounts'][$id] ?? null;
        if (!is_array($row)) throw new RuntimeException('Účet nebyl nalezen.');
        $new = $fn($row, $store['accounts']);
        if (!is_array($new)) throw new RuntimeException('Úprava účtu nevrátila platná data.');
        $store['accounts'][$id] = $new;
        $result = $new;
        return $store;
    });
    return $result;
}

/** Hash pro ověření u neexistujícího účtu (stejná cena jako skutečný hash – bez výčtu uživatelů podle času). */
function teacher59_dummy_hash(): string
{
    static $computed = null;
    try {
        $stored = (string)(teacher59_store_read()['dummy_hash'] ?? '');
        if ($stored !== '') return $stored;
    } catch (Throwable $e) {
        // nečitelné úložiště – přihlášení stejně skončí 503
    }
    return $computed ??= local_password_hash(bin2hex(random_bytes(16)));
}

function teacher59_role_label(string $role): string
{
    return match ($role) { 'admin' => 'Administrátor', 'assistant' => 'Asistent', default => 'Učitel' };
}

// ---------------------------------------------------------------------------
// Jednorázová hesla (4 slova + 4 číslice ≈ 45,6 bitu, platnost 72 h, ukládá se jen hash)
// ---------------------------------------------------------------------------

function teacher59_otp_pattern(): string
{
    return '/^[a-z]{2,12}-[a-z]{2,12}-[2-9]{4}-[a-z]{2,12}-[a-z]{2,12}$/';
}

/** Kontext validátoru hesel: jméno + části přihlašovacího jména (heslo je nesmí obsahovat). */
function teacher59_password_context(array $account): array
{
    $login = str_replace(['.', '_', '-'], ' ', (string)($account['login'] ?? ''));
    return ['name' => trim((string)($account['display_name'] ?? '') . ' ' . $login)];
}

function teacher59_generate_otp(array $context = []): string
{
    require_once __DIR__ . '/accounts_v58.php';
    $words = acc58_words();
    $last = count($words) - 1;
    $pick = static fn(): string => (string)$words[random_int(0, $last)];
    for ($try = 0; $try < 50; $try++) {
        $digits = '';
        for ($i = 0; $i < ACC58_OTP_DIGIT_COUNT; $i++) $digits .= ACC58_OTP_DIGITS[random_int(0, strlen(ACC58_OTP_DIGITS) - 1)];
        $otp = $pick() . '-' . $pick() . '-' . $digits . '-' . $pick() . '-' . $pick();
        if (local_password_validate($otp, $context) === null) return $otp;
    }
    throw new RuntimeException('Jednorázové heslo se nepodařilo vygenerovat.');
}

/** Stav účtu: otp (čeká na změnu), expired (OTP vypršelo), own (vlastní heslo). */
function teacher59_otp_status(array $account, ?int $now = null): string
{
    if (empty($account['must_change_password'])) return 'own';
    return (int)($account['otp']['expires_at'] ?? 0) < ($now ?? time()) ? 'expired' : 'otp';
}

// ---------------------------------------------------------------------------
// Log (bez IP, hesel a OTP)
// ---------------------------------------------------------------------------

/** Bezpečnostní událost → log účtů; hlučná událost (TEACHER59_NOISE_EVENTS) → oddělený log s limitem (SEC59-04). */
function teacher59_log(string $event, ?string $actorId, ?string $targetId, array $classIds = [], string $reason = '', array $meta = []): void
{
    $row = [
        'at' => date(DATE_ATOM), 'event' => substr((string)preg_replace('/[^a-z0-9_]/', '', $event), 0, 40),
        'actor_id' => $actorId, 'target_id' => $targetId,
        'class_ids' => array_values(array_map('strval', $classIds)), 'reason' => u_substr($reason, 0, 160),
    ];
    if ($meta !== []) $row['meta'] = $meta;
    if (in_array($row['event'], TEACHER59_NOISE_EVENTS, true)) {
        teacher59_noise_log($row, $actorId);
        return;
    }
    try {
        storage_list_push(teacher59_log_path(), $row, TEACHER59_LOG_LIMIT);
    } catch (Throwable $e) {
        error_log('EDUCANET v59 log účtů: ' . $e->getMessage());
    }
}

/** @return list<array<string, mixed>> nejnovější záznamy bezpečnostního logu ($noise = true: hlučného logu) */
function teacher59_log_rows(int $limit = 200, bool $noise = false): array
{
    $raw = storage_read($noise ? teacher59_noise_path() : teacher59_log_path(), false);
    $rows = array_values(array_filter($noise ? (is_array($raw['rows'] ?? null) ? $raw['rows'] : []) : $raw, 'is_array'));
    return array_slice(array_reverse($rows), 0, max(1, $limit));
}

// ---------------------------------------------------------------------------
// Relace
// ---------------------------------------------------------------------------

function teacher59_session_clear(): void
{
    foreach (TEACHER59_SESSION_KEYS as $key) unset($_SESSION[$key]);
    teacher59_reset_cache();
}

function teacher59_regenerate_session(): void
{
    if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE && !headers_sent()) session_regenerate_id(true);
    $_SESSION['csrf'] = bin2hex(random_bytes(24));
}

/** Po úspěšném přihlášení: nové ID session, nový CSRF token, učitelský stav jen z účtu. */
function teacher59_session_begin(array $account, ?int $now = null): void
{
    $now ??= time();
    teacher59_session_clear();
    teacher59_regenerate_session();
    $_SESSION['teacher59'] = ['id' => (string)$account['id'], 'sv' => (int)($account['session_version'] ?? 1), 'login_at' => $now, 'seen_at' => $now];
    $_SESSION['teacher_display_name'] = (string)($account['display_name'] ?? '');
    teacher59_reset_cache();
}

/** Důvod neplatnosti relace, nebo '' (relace platí). */
function teacher59_session_invalid_reason(?array $account, array $session, int $now): string
{
    if ($account === null) return 'missing';
    if ((string)($account['status'] ?? '') !== 'active') return 'disabled';
    if ((int)($account['session_version'] ?? 0) !== (int)($session['sv'] ?? -1)) return 'session_version';
    if ($now - (int)($session['seen_at'] ?? 0) > TEACHER59_IDLE_TIMEOUT) return 'idle';
    if ($now - (int)($session['login_at'] ?? 0) > TEACHER59_ABSOLUTE_TIMEOUT) return 'absolute';
    return '';
}

/** Aktuální učitelský účet (každý požadavek znovu ověřen proti úložišti), nebo null. */
function teacher59_current(): ?array
{
    $cache = &teacher59_cache();
    if (array_key_exists('current', $cache)) return $cache['current'];
    $cache['current'] = null;
    $cache['state'] = 'none';
    $mode = teacher59_mode();
    if ($mode === 'legacy') return null;
    if ($mode === 'broken') { $cache['state'] = 'unavailable'; return null; }
    if (!empty($_SESSION['teacher_export_authenticated'])) {
        // Stará session jen s příznakem sdíleného klíče v režimu účtů neplatí.
        unset($_SESSION['teacher_export_authenticated']);
        if (empty($_SESSION['teacher59'])) teacher59_log('legacy_session_cleared', null, null, [], 'shared_key_session');
    }
    $session = $_SESSION['teacher59'] ?? null;
    if (!is_array($session)) return null;
    $now = time();
    $account = teacher59_account((string)($session['id'] ?? ''));
    $reason = teacher59_session_invalid_reason($account, $session, $now);
    if ($reason !== '') {
        $id = (string)($session['id'] ?? '');
        foreach (TEACHER59_SESSION_KEYS as $key) unset($_SESSION[$key]);
        $_SESSION['teacher59_notice'] = TEACHER59_MSG_EXPIRED;
        teacher59_log('expired', $id !== '' ? $id : null, $id !== '' ? $id : null, [], $reason);
        $cache['current'] = null;
        $cache['state'] = 'expired';
        return null;
    }
    $_SESSION['teacher59']['seen_at'] = $now;
    $cache['current'] = $account;
    $cache['state'] = !empty($account['must_change_password']) ? 'must_change' : 'ok';
    return $account;
}

/** none | ok | must_change | expired | unavailable (v režimu legacy vždy none – rozhoduje starý příznak). */
function teacher59_session_state(): string
{
    teacher59_current();
    $cache = &teacher59_cache();
    return (string)($cache['state'] ?? 'none');
}

function teacher59_current_id(): ?string
{
    $account = teacher59_current();
    return $account !== null ? (string)$account['id'] : null;
}

/** Odhlášení: smaže jen učitelské klíče (ID session regeneruje teacher.php). */
function teacher59_logout(): void
{
    $id = is_array($_SESSION['teacher59'] ?? null) ? (string)($_SESSION['teacher59']['id'] ?? '') : '';
    if ($id !== '') teacher59_log('logout', $id, $id);
    teacher59_session_clear();
}

// ---------------------------------------------------------------------------
// Přihlášení (zámek a zpomalení: teacher_accounts_v59_limits.php)
// ---------------------------------------------------------------------------

/**
 * Jádro přihlášení (bez přesměrování – testovatelné). Vrací ['ok'=>bool, 'error'=>?string, 'account'=>?array].
 * Pořadí: limity klienta a login+IP (i se správným heslem) → zpomalení podle chyb loginu napříč IP → ověření hesla
 * (neznámý účet proti dummy hashi, stejné limity – bez výčtu účtů) → stav účtu → relace.
 */
function teacher59_attempt_login(string $login, string $password, ?int $now = null): array
{
    $now ??= time();
    if (teacher59_mode() !== 'accounts') return ['ok' => false, 'error' => TEACHER59_MSG_LOGIN, 'account' => null];
    $login = teacher59_normalize_login($login);
    $clientBuckets = teacher_login_buckets();
    $account = teacher59_valid_login($login) ? teacher59_find_by_login($login) : null;
    $targetId = $account !== null ? (string)$account['id'] : null;
    $counts = teacher59_lock_counts($login, $now);
    if (!auth_rate_limit_check_all($clientBuckets, TEACHER_LOGIN_WINDOW) || $counts['ip'] >= TEACHER59_LOCK_LIMIT) {
        return ['ok' => false, 'error' => TEACHER59_MSG_LIMIT, 'account' => null]; // „locked“ se loguje jen při přechodu do zámku
    }
    $delay = teacher59_throttle_delay_ms($counts['all']);
    if ($delay > 0) usleep($delay * 1000);
    $hash = $account !== null ? (string)($account['password_hash'] ?? '') : teacher59_dummy_hash();
    $valid = password_verify($password, $hash !== '' ? $hash : teacher59_dummy_hash());
    $valid = $valid && $account !== null && strlen($password) <= LOCAL_PASSWORD_MAX_BYTES;
    $reason = $account === null ? 'unknown' : ($valid ? '' : 'bad_password');
    if ($valid && (string)($account['status'] ?? '') !== 'active') { $valid = false; $reason = 'disabled'; }
    if (!$valid) {
        auth_rate_limit_fail_all($clientBuckets);
        $after = teacher59_lock_fail($login, $now);
        teacher59_log('login_fail', null, $targetId, [], $reason);
        if ($after['ip'] === TEACHER59_LOCK_LIMIT) teacher59_log('locked', null, $targetId, [], 'login_ip_limit');
        if ($after['all'] === TEACHER59_THROTTLE_LIMIT) teacher59_log('throttled', null, $targetId, [], 'login_global_limit');
        return ['ok' => false, 'error' => TEACHER59_MSG_LOGIN, 'account' => null];
    }
    if (teacher59_otp_status($account, $now) === 'expired') {
        teacher59_log('login_fail', null, $targetId, [], 'otp_expired');
        return ['ok' => false, 'error' => TEACHER59_MSG_OTP_EXPIRED, 'account' => null];
    }
    auth_rate_limit_clear((string)array_key_first($clientBuckets));
    if ($counts['ip'] > 0) teacher59_lock_clear($login, false);
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $newHash = password_needs_rehash($hash, $algo) ? local_password_hash($password) : null;
    $fresh = teacher59_account_update((string)$account['id'], static function (array $a) use ($newHash, $now): array {
        if ($newHash !== null) $a['password_hash'] = $newHash;
        $a['last_login_at'] = date(DATE_ATOM, $now);
        return $a;
    });
    teacher59_session_begin($fresh, $now);
    teacher59_log('login_ok', (string)$fresh['id'], (string)$fresh['id'], array_column((array)($fresh['assignments'] ?? []), 'class_id'));
    return ['ok' => true, 'error' => null, 'account' => $fresh];
}

/** Obsluha akce teacher_login v režimu účtů (volá teacher.php; v režimu legacy se nevolá). */
function teacher59_handle_login(): never
{
    teacher59_no_store();
    if (teacher59_mode() === 'broken') teacher59_render_unavailable();
    if (isset($_POST['teacher_key'])) {
        teacher59_log('legacy_key_used', null, null, [], 'shared_key_rejected');
        teacher_flash('Sdílený učitelský klíč už neplatí. Přihlaste se vlastním účtem.', 'error');
        teacher_redirect();
    }
    $login = is_string($_POST['login'] ?? null) ? $_POST['login'] : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $result = teacher59_attempt_login($login, $password);
    if (!$result['ok']) { teacher_flash((string)$result['error'], 'error'); teacher_redirect(); }
    if (function_exists('teacher_saved_filter_actor_token')) teacher_saved_filter_actor_token();
    if (function_exists('teacher_ops_team_member_touch')) {
        try { teacher_ops_team_member_touch(); } catch (Throwable $e) { error_log('EDUCANET v59 tým: ' . $e->getMessage()); }
    }
    teacher_redirect(!empty($result['account']['must_change_password']) ? [] : ['tab' => 'overview']);
}

/** Hlavičky pro stránky s účty a jednorázovými hesly. */
function teacher59_no_store(): void
{
    if (PHP_SAPI === 'cli' || headers_sent()) return;
    header('Cache-Control: no-store, max-age=0');
    header('Pragma: no-cache');
}

// ---------------------------------------------------------------------------
// Změna hesla, správa účtů (volají admin handlery i CLI)
// ---------------------------------------------------------------------------

/** Chyba nového hesla, nebo null. */
function teacher59_new_password_error(array $account, string $new, string $confirm): ?string
{
    if (!hash_equals($new, $confirm)) return 'Nové heslo a jeho potvrzení se neshodují.';
    if (u_strlen($new) < TEACHER59_PASSWORD_MIN) return 'Heslo musí mít alespoň ' . TEACHER59_PASSWORD_MIN . ' znaků.';
    $context = teacher59_password_context($account) + ['current_hash' => (string)($account['password_hash'] ?? '')];
    $error = local_password_validate($new, $context);
    if ($error !== null) return str_replace(['tvoje jméno ani část e-mailu', 'Vymysli si vlastní', 'Zkus', 'z kartičky'], ['vaše jméno ani přihlašovací jméno', 'Zvolte vlastní', 'Zkuste', ''], $error);
    return null;
}

/** Změna vlastního hesla. Vrací ['error'=>?string, 'account'=>?array]; zvýší session_version (odhlásí ostatní relace). */
function teacher59_change_password(string $id, string $current, string $new, string $confirm): array
{
    $account = teacher59_account($id);
    if ($account === null) return ['error' => 'Účet nebyl nalezen.', 'account' => null];
    if (!password_verify($current, (string)($account['password_hash'] ?? ''))) return ['error' => 'Stávající heslo nesouhlasí.', 'account' => null];
    $error = teacher59_new_password_error($account, $new, $confirm);
    if ($error !== null) return ['error' => $error, 'account' => null];
    $hash = local_password_hash($new);
    $fresh = teacher59_account_update($id, static function (array $a) use ($hash): array {
        $a['password_hash'] = $hash;
        $a['must_change_password'] = false;
        unset($a['otp']);
        $a['password_changed_at'] = date(DATE_ATOM);
        $a['session_version'] = (int)($a['session_version'] ?? 1) + 1;
        $a['updated_at'] = date(DATE_ATOM);
        return $a;
    });
    teacher59_log('pw_changed', $id, $id);
    return ['error' => null, 'account' => $fresh];
}

/** Po změně hesla / odhlášení ostatních: tahle relace pokračuje s novou verzí (nové ID + CSRF). */
function teacher59_session_refresh(array $account): void
{
    if (!is_array($_SESSION['teacher59'] ?? null)) return;
    teacher59_regenerate_session();
    $_SESSION['teacher59']['sv'] = (int)($account['session_version'] ?? 1);
    $_SESSION['teacher59']['seen_at'] = time();
    $_SESSION['teacher_display_name'] = (string)($account['display_name'] ?? '');
    teacher59_reset_cache();
}

function teacher59_active_admin_ids(array $accounts): array
{
    $ids = [];
    foreach ($accounts as $id => $row) {
        if (is_array($row) && (string)($row['role'] ?? '') === 'admin' && (string)($row['status'] ?? '') === 'active') $ids[] = (string)$id;
    }
    return $ids;
}

function teacher59_validate_display_name(string $name): string
{
    $name = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $name));
    if (u_strlen($name) < 2 || u_strlen($name) > 80) throw new RuntimeException('Jméno musí mít 2 až 80 znaků.');
    return $name;
}

/** @return list<array{class_id:string, subject_id:string}> ověřená přiřazení (předmět musí patřit třídě). */
function teacher59_validate_assignments(array $raw): array
{
    $out = [];
    $universe = teacher59_all_class_ids();
    foreach ($raw as $row) {
        if (!is_array($row)) throw new RuntimeException('Neplatné přiřazení třídy.');
        $classId = (string)($row['class_id'] ?? '');
        $subjectId = (string)($row['subject_id'] ?? '*');
        if (!in_array($classId, $universe, true)) throw new RuntimeException('Neznámá třída „' . $classId . '“.');
        $subject = teacher59_subject_of_class($classId);
        if ($subjectId !== '*' && ($subject === '' || $subjectId !== $subject)) throw new RuntimeException('Předmět „' . $subjectId . '“ do třídy ' . $classId . ' nepatří.');
        $out[$classId] = ['class_id' => $classId, 'subject_id' => $subjectId];
    }
    return array_values($out);
}

/** Nový účet. Vrací ['account'=>array, 'otp'=>string] – OTP jen v paměti, uloží se hash. */
function teacher59_account_create(array $in, string $actorId): array
{
    $login = teacher59_normalize_login((string)($in['login'] ?? ''));
    if (!teacher59_valid_login($login)) throw new RuntimeException('Přihlašovací jméno: 3–40 znaků, jen malá písmena bez diakritiky, číslice, tečka, pomlčka, podtržítko.');
    $name = teacher59_validate_display_name((string)($in['display_name'] ?? ''));
    $role = (string)($in['role'] ?? 'teacher');
    if (!in_array($role, TEACHER59_ROLES, true)) throw new RuntimeException('Neplatná role.');
    $assignments = teacher59_validate_assignments((array)($in['assignments'] ?? []));
    $otp = teacher59_generate_otp(teacher59_password_context(['login' => $login, 'display_name' => $name]));
    $hash = local_password_hash($otp);
    $now = time();
    $account = [];
    teacher59_store_update(static function (array $store) use ($login, $name, $role, $assignments, $hash, $now, $actorId, &$account): array {
        foreach ($store['accounts'] as $row) {
            if (is_array($row) && (string)($row['login'] ?? '') === $login) throw new RuntimeException('Přihlašovací jméno už existuje.');
        }
        do { $id = 't_' . bin2hex(random_bytes(8)); } while (isset($store['accounts'][$id]));
        $account = [
            'id' => $id, 'login' => $login, 'display_name' => $name, 'role' => $role, 'assignments' => $assignments,
            'password_hash' => $hash, 'must_change_password' => true,
            'otp' => ['issued_at' => $now, 'expires_at' => $now + TEACHER59_OTP_TTL, 'issued_by' => $actorId],
            'status' => 'active', 'session_version' => 1,
            'created_at' => date(DATE_ATOM, $now), 'created_by' => $actorId, 'updated_at' => date(DATE_ATOM, $now), 'updated_by' => $actorId,
            'last_login_at' => null, 'password_changed_at' => null, 'disabled_at' => null,
        ];
        $store['accounts'][$id] = $account;
        return $store;
    });
    teacher59_log('created', $actorId, (string)$account['id'], array_column($assignments, 'class_id'), 'role:' . $role);
    teacher59_log('otp_issued', $actorId, (string)$account['id'], [], 'create');
    return ['account' => $account, 'otp' => $otp];
}

/** Změna role a přiřazení (chrání posledního aktivního admina). */
function teacher59_account_update_access(string $id, string $role, array $assignments, string $actorId): array
{
    if (!in_array($role, TEACHER59_ROLES, true)) throw new RuntimeException('Neplatná role.');
    $assignments = teacher59_validate_assignments($assignments);
    $events = [];
    $fresh = teacher59_account_update($id, static function (array $a, array $all) use ($role, $assignments, $actorId, $id, &$events): array {
        $oldRole = (string)($a['role'] ?? 'teacher');
        if ($oldRole === 'admin' && $role !== 'admin' && (string)($a['status'] ?? '') === 'active'
            && array_diff(teacher59_active_admin_ids($all), [$id]) === []) {
            throw new RuntimeException('Poslední aktivní administrátor nemůže přijít o roli administrátora.');
        }
        if ($oldRole !== $role) $events[] = 'role_changed';
        if ((array)($a['assignments'] ?? []) !== $assignments) $events[] = 'assign_changed';
        if ($events === []) return $a;
        $a['role'] = $role;
        $a['assignments'] = $assignments;
        $a['session_version'] = (int)($a['session_version'] ?? 1) + 1;
        $a['updated_at'] = date(DATE_ATOM);
        $a['updated_by'] = $actorId;
        return $a;
    });
    foreach ($events as $event) teacher59_log($event, $actorId, $id, array_column($assignments, 'class_id'), $event === 'role_changed' ? 'role:' . $role : '');
    return $fresh;
}

/** Nové jednorázové heslo (reset). Vrací ['account'=>array, 'otp'=>string]. */
function teacher59_account_reset(string $id, string $actorId): array
{
    $account = teacher59_account($id);
    if ($account === null) throw new RuntimeException('Účet nebyl nalezen.');
    // SEC59-21: reset vlastního účtu by adminovi vzal přístup (OTP by se ukázalo jen v odhlášené relaci).
    if ($id === $actorId) throw new RuntimeException('Vlastní heslo změníte v záložce Můj účet. Nové jednorázové heslo pro sebe vydat nelze.');
    $otp = teacher59_generate_otp(teacher59_password_context($account));
    $hash = local_password_hash($otp);
    $now = time();
    $fresh = teacher59_account_update($id, static function (array $a) use ($hash, $now, $actorId): array {
        $a['password_hash'] = $hash;
        $a['must_change_password'] = true;
        $a['otp'] = ['issued_at' => $now, 'expires_at' => $now + TEACHER59_OTP_TTL, 'issued_by' => $actorId];
        $a['session_version'] = (int)($a['session_version'] ?? 1) + 1;
        $a['updated_at'] = date(DATE_ATOM, $now);
        $a['updated_by'] = $actorId;
        return $a;
    });
    teacher59_unlock_login((string)$fresh['login']);
    teacher59_log('otp_issued', $actorId, $id, [], 'reset');
    return ['account' => $fresh, 'otp' => $otp];
}

/** Aktivace / deaktivace (nelze deaktivovat sebe ani posledního aktivního admina). */
function teacher59_account_set_status(string $id, string $status, string $actorId): array
{
    if (!in_array($status, ['active', 'disabled'], true)) throw new RuntimeException('Neplatný stav účtu.');
    if ($status === 'disabled' && $id === $actorId) throw new RuntimeException('Vlastní účet deaktivovat nelze.');
    $fresh = teacher59_account_update($id, static function (array $a, array $all) use ($status, $actorId, $id): array {
        if ((string)($a['status'] ?? '') === $status) return $a;
        if ($status === 'disabled' && (string)($a['role'] ?? '') === 'admin' && array_diff(teacher59_active_admin_ids($all), [$id]) === []) {
            throw new RuntimeException('Posledního aktivního administrátora nelze deaktivovat.');
        }
        $a['status'] = $status;
        $a['disabled_at'] = $status === 'disabled' ? date(DATE_ATOM) : null;
        $a['session_version'] = (int)($a['session_version'] ?? 1) + 1;
        $a['updated_at'] = date(DATE_ATOM);
        $a['updated_by'] = $actorId;
        return $a;
    });
    teacher59_log($status === 'disabled' ? 'disabled' : 'enabled', $actorId, $id);
    return $fresh;
}

/** Odhlásí všechny relace účtu (session_version++). */
function teacher59_account_bump_session(string $id, string $actorId): array
{
    $fresh = teacher59_account_update($id, static function (array $a): array {
        $a['session_version'] = (int)($a['session_version'] ?? 1) + 1;
        return $a;
    });
    teacher59_log('sessions_revoked', $actorId, $id);
    return $fresh;
}

// ---------------------------------------------------------------------------
// Převzetí starých dat (owner_key v2 z cookie tokenu + jména → v3 z id účtu)
// ---------------------------------------------------------------------------

/** Úložiště učitelských dat s owner_key (teacher_tasks.php, teacher_operations_*_v46*.php). Audit operací se nikdy nepřepisuje (historie). */
function teacher59_reclaim_stores(): array
{
    return [
        'teacher_saved_filters.json.php' => 'Uložené filtry', 'teacher_watchlist.json.php' => 'Watchlist',
        'teacher_followups.json.php' => 'Follow-upy', 'teacher_intervention_templates.json.php' => 'Šablony intervencí',
        'teacher_message_templates.json.php' => 'Komunikační šablony', 'teacher_automations.json.php' => 'Automatizace',
        'teacher_notifications.json.php' => 'Upozornění', 'teacher_review_snapshots.json.php' => 'Kontrolní přehledy',
        'teacher_bulk_undo.json.php' => 'Vrácení hromadných akcí', 'teacher_student_notes.json.php' => 'Poznámky ke studentům',
        'teacher_interventions.json.php' => 'Intervence',
    ];
}

function teacher59_legacy_owner_key(string $actorToken, string $name): string
{
    return hash('sha256', 'v2|' . $actorToken . '|' . normalized_person_name($name));
}

function teacher59_owner_key_for(string $accountId): string
{
    return hash('sha256', 'v3|' . $accountId);
}

/** Mapa owner_key v3 → účet (cron automatizací); [] = legacy, null = nečitelné úložiště (fail closed). */
function teacher59_owner_account_map(): ?array
{
    $mode = teacher59_mode();
    if ($mode === 'legacy') return [];
    if ($mode === 'broken') return null;
    $map = [];
    foreach (teacher59_accounts() as $id => $row) $map[teacher59_owner_key_for((string)$id)] = $row;
    return $map;
}

/**
 * Přepíše owner_key starých záznamů (vázaných na token prohlížeče + jméno) na klíč účtu (SEC59-07):
 * jméno i owner_label všech nalezených řádků musí odpovídat jménu účtu (bez diakritiky a velikosti písmen), jinak
 * nic nepřevezme (sdílené PC ve sborovně); řádky tříd mimo rozsah účtu zůstanou (SEC59-06); owner_label se nemění.
 * @return array<string, int> počet převzatých záznamů podle úložiště
 */
function teacher59_reclaim_legacy(string $accountId, string $actorToken, string $legacyName): array
{
    $account = teacher59_account($accountId);
    if ($account === null) throw new RuntimeException('Účet nebyl nalezen.');
    if (!preg_match('/^[a-f0-9]{64}$/D', $actorToken)) throw new RuntimeException('V tomto prohlížeči nejsou žádná stará data učitele.');
    if (trim($legacyName) === '') throw new RuntimeException('Doplňte jméno, pod kterým jste se dříve přihlašoval(a).');
    $wanted = normalized_person_name((string)$account['display_name']);
    if (normalized_person_name($legacyName) !== $wanted) throw new RuntimeException(TEACHER59_MSG_RECLAIM_NAME);
    $old = teacher59_legacy_owner_key($actorToken, $legacyName);
    $new = teacher59_owner_key_for($accountId);
    $paths = [];
    foreach (array_keys(teacher59_reclaim_stores()) as $file) {
        if (is_file(STORAGE_DIR . '/' . $file)) $paths[$file] = STORAGE_DIR . '/' . $file;
    }
    $counts = array_fill_keys(array_keys($paths), 0);
    if ($paths === []) return $counts;
    $mismatch = false;
    $skipped = 0;
    storage_update_many(array_values($paths), static function (array $data) use ($paths, $old, $new, $wanted, $account, &$counts, &$mismatch, &$skipped): array {
        $mine = static fn($row): bool => is_array($row) && hash_equals((string)($row['owner_key'] ?? ''), $old);
        foreach ($paths as $path) {
            foreach ((array)($data[$path] ?? []) as $row) {
                if ($mine($row) && array_key_exists('owner_label', $row) && normalized_person_name((string)$row['owner_label']) !== $wanted) { $mismatch = true; return []; }
            }
        }
        $out = [];
        foreach ($paths as $file => $path) {
            $rows = $data[$path] ?? [];
            foreach ($rows as $k => $row) {
                if (!$mine($row)) continue;
                $classId = teacher59_row_class($row);
                if ($classId !== '' && !teacher59_account_can_class($account, $classId)) { $skipped++; continue; }
                $row['owner_key'] = $new;
                if ($file === 'teacher_saved_filters.json.php') $row['owner_version'] = 3;
                $rows[$k] = $row;
                $counts[$file]++;
            }
            if ($counts[$file] > 0) $out[$path] = $rows;
        }
        return $out;
    });
    if ($mismatch) {
        teacher59_log('reclaim_refused', $accountId, $accountId, [], 'owner_label_mismatch', ['old_key' => substr(hash('sha256', $old), 0, 16)]);
        throw new RuntimeException(TEACHER59_MSG_RECLAIM_NAME);
    }
    $perStore = [];
    foreach ($counts as $file => $n) if ($n > 0) $perStore[str_replace(['teacher_', '.json.php'], '', $file)] = $n;
    teacher59_log('reclaimed', $accountId, $accountId, [], 'rows:' . array_sum($counts) . ' skipped:' . $skipped,
        ['old_key' => substr(hash('sha256', $old), 0, 16), 'counts' => $perStore, 'skipped_out_of_scope' => $skipped]);
    return $counts;
}
