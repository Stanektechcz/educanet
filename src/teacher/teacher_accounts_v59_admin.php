<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v59 · AUTHZ58-07 – POST akce učitelských účtů.
 *   teacher59_admin_* (jen admin; oprávnění accounts.manage): create, update, reset, disable, enable, unlock, revoke.
 *   teacher59_self_*  (každý přihlášený účet): password, logout_others, reclaim.
 * CSRF ověřuje teacher.php (verify_csrf) před dispatchem; rozsah/roli ověřuje teacher59_guard_post a znovu zde.
 * *_apply() jsou bez přesměrování (testovatelné), teacher59_handle_post() flashuje a přesměruje.
 */

const TEACHER59_PWCHANGE_LIMIT = 10;
const TEACHER59_PWCHANGE_WINDOW = 900;

/** Přiřazení z formuláře: assign[] = class_id, subject[<class_id>] = * | graphics | networks. */
function teacher59_assignments_from_post(array $post): array
{
    $out = [];
    $subjects = is_array($post['subject'] ?? null) ? $post['subject'] : [];
    foreach ((array)($post['assign'] ?? []) as $classId) {
        if (!is_string($classId) || $classId === '') continue;
        $subject = $subjects[$classId] ?? '*';
        $out[] = ['class_id' => $classId, 'subject_id' => is_string($subject) && $subject !== '' ? $subject : '*'];
    }
    return $out;
}

function teacher59_post_string(array $post, string $key, int $max = 200): string
{
    $value = $post[$key] ?? '';
    return is_string($value) ? substr($value, 0, $max) : '';
}

// ---------------------------------------------------------------------------
// Jednorázová kartička (OTP jen v session, token jen jednou, nikdy v URL ani logu)
// ---------------------------------------------------------------------------

function teacher59_card_store(array $account, string $otp): void
{
    $_SESSION['teacher59_card'] = [
        'token' => bin2hex(random_bytes(16)), 'account_id' => (string)$account['id'], 'login' => (string)$account['login'],
        'name' => (string)$account['display_name'], 'otp' => $otp, 'expires_at' => (int)($account['otp']['expires_at'] ?? 0),
        'created' => time(), 'shown' => false,
    ];
}

/** Kartička k zobrazení v záložce Učitelé – jen poprvé (pak zůstane jen pro jeden tisk). */
function teacher59_card_take_reveal(): ?array
{
    $card = $_SESSION['teacher59_card'] ?? null;
    if (!is_array($card) || (int)($card['created'] ?? 0) < time() - TEACHER59_CARD_TTL) { unset($_SESSION['teacher59_card']); return null; }
    if (!empty($card['shown'])) return null;
    $_SESSION['teacher59_card']['shown'] = true;
    return $card;
}

/** Tisk kartičky: token se spotřebuje (druhý tisk už heslo neukáže). */
function teacher59_card_consume(string $token): ?array
{
    $card = $_SESSION['teacher59_card'] ?? null;
    if (!is_array($card) || $token === '' || !hash_equals((string)($card['token'] ?? ''), $token)) return null;
    unset($_SESSION['teacher59_card']);
    if ((int)($card['created'] ?? 0) < time() - TEACHER59_CARD_TTL) return null;
    return $card;
}

// ---------------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------------

/** @return array{flash:string, params:array<string,string>} */
function teacher59_admin_apply(string $action, array $post, string $actorId): array
{
    if (!teacher59_is_admin()) throw new RuntimeException('Správa účtů je jen pro administrátora.');
    $params = ['tab' => 'ucitele'];
    if ($action === 'teacher59_admin_create') {
        $result = teacher59_account_create([
            'login' => teacher59_post_string($post, 'login', 60), 'display_name' => teacher59_post_string($post, 'display_name', 400),
            'role' => teacher59_post_string($post, 'role', 20), 'assignments' => teacher59_assignments_from_post($post),
        ], $actorId);
        teacher59_card_store($result['account'], $result['otp']);
        return ['flash' => 'Účet „' . $result['account']['login'] . '“ je založený. Jednorázové heslo se ukáže jen jednou.', 'params' => $params];
    }
    $id = teacher59_post_string($post, 'account_id', 40);
    $account = teacher59_account($id);
    if ($account === null) throw new RuntimeException('Účet nebyl nalezen.');
    $label = '„' . (string)$account['display_name'] . '“';
    switch ($action) {
        case 'teacher59_admin_update':
            teacher59_account_update_access($id, teacher59_post_string($post, 'role', 20), teacher59_assignments_from_post($post), $actorId);
            return ['flash' => 'Přístup účtu ' . $label . ' je uložený. Jeho otevřené relace se odhlásily.', 'params' => $params];
        case 'teacher59_admin_reset':
            $result = teacher59_account_reset($id, $actorId);
            teacher59_card_store($result['account'], $result['otp']);
            return ['flash' => 'Nové jednorázové heslo pro ' . $label . ' je připravené. Ukáže se jen jednou.', 'params' => $params];
        case 'teacher59_admin_disable':
            teacher59_account_set_status($id, 'disabled', $actorId);
            return ['flash' => 'Účet ' . $label . ' je deaktivovaný a odhlášený.', 'params' => $params];
        case 'teacher59_admin_enable':
            teacher59_account_set_status($id, 'active', $actorId);
            return ['flash' => 'Účet ' . $label . ' je znovu aktivní.', 'params' => $params];
        case 'teacher59_admin_unlock':
            teacher59_unlock_login((string)$account['login']);
            teacher59_log('unlocked', $actorId, $id);
            return ['flash' => 'Dočasný zámek přihlášení účtu ' . $label . ' je zrušený.', 'params' => $params];
        case 'teacher59_admin_revoke':
            teacher59_account_bump_session($id, $actorId);
            if ($id === $actorId) teacher59_session_refresh((array)teacher59_account($id));
            return ['flash' => 'Všechny relace účtu ' . $label . ' jsou odhlášené.', 'params' => $params];
    }
    throw new RuntimeException('Neznámá akce správy účtů.');
}

// ---------------------------------------------------------------------------
// Vlastní účet
// ---------------------------------------------------------------------------

function teacher59_actor_token(): string
{
    foreach ([$_SESSION['teacher_saved_filter_actor_token'] ?? null, $_COOKIE['educanet_teacher_actor'] ?? null] as $token) {
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/D', $token)) return $token;
    }
    return '';
}

/** @return array{flash:string, params:array<string,string>, type?:string} */
function teacher59_self_apply(string $action, array $post, string $actorId): array
{
    $account = teacher59_account($actorId);
    if ($account === null) throw new RuntimeException('Účet nebyl nalezen.');
    $params = ['tab' => 'ucet'];
    if ($action === 'teacher59_self_password') {
        $bucket = 'global:teacher59-pwchange:' . $actorId;
        if (!auth_rate_limit_check($bucket, TEACHER59_PWCHANGE_LIMIT, TEACHER59_PWCHANGE_WINDOW)) {
            return ['flash' => TEACHER59_MSG_LIMIT, 'params' => $params, 'type' => 'error'];
        }
        $forced = !empty($account['must_change_password']);
        $result = teacher59_change_password($actorId, teacher59_post_string($post, 'current_password'), teacher59_post_string($post, 'new_password'), teacher59_post_string($post, 'new_password_confirm'));
        if ($result['error'] !== null) {
            if ($result['error'] === 'Stávající heslo nesouhlasí.') auth_rate_limit_fail($bucket);
            return ['flash' => (string)$result['error'], 'params' => $forced ? [] : $params, 'type' => 'error'];
        }
        auth_rate_limit_clear($bucket);
        teacher59_session_refresh($result['account']);
        return ['flash' => $forced ? 'Heslo je nastavené. Vítejte v učitelském rozhraní.' : 'Heslo je změněné. Ostatní přihlášení byla odhlášena.', 'params' => $forced ? ['tab' => 'overview'] : $params];
    }
    if ($action === 'teacher59_self_logout_others') {
        $fresh = teacher59_account_bump_session($actorId, $actorId);
        teacher59_session_refresh($fresh);
        return ['flash' => 'Ostatní přihlášení tohoto účtu jsou odhlášená. Tohle zůstává aktivní.', 'params' => $params];
    }
    if ($action === 'teacher59_self_reclaim') {
        $counts = teacher59_reclaim_legacy($actorId, teacher59_actor_token(), teacher59_post_string($post, 'legacy_name', 200));
        $total = array_sum($counts);
        return ['flash' => $total > 0 ? 'Převzato záznamů: ' . $total . '. Filtry, šablony a další vaše data teď patří k tomuto účtu.' : 'Pod tímto jménem v tomto prohlížeči nebyla nalezena žádná stará data.', 'params' => $params, 'type' => $total > 0 ? 'ok' : 'error'];
    }
    throw new RuntimeException('Neznámá akce účtu.');
}

/** Dispatch z teacher.php (uvnitř try – výjimky se ukážou jako flash). */
function teacher59_handle_post(string $action): void
{
    if (!str_starts_with($action, 'teacher59_')) return;
    if (teacher59_mode() !== 'accounts') throw new RuntimeException('Učitelské účty nejsou zapnuté. Správce je zapne příkazem tools/v59_teacher_accounts.php create-admin.');
    teacher59_no_store();
    $actorId = teacher59_current_id();
    if ($actorId === null) throw new RuntimeException('Nejdřív se přihlaste.');
    if (str_starts_with($action, 'teacher59_admin_')) $result = teacher59_admin_apply($action, $_POST, $actorId);
    elseif (str_starts_with($action, 'teacher59_self_')) $result = teacher59_self_apply($action, $_POST, $actorId);
    else throw new RuntimeException('Neznámá akce účtu.');
    teacher_flash($result['flash'], (string)($result['type'] ?? 'ok'));
    teacher_redirect($result['params']);
}

/** POST v relaci s vynucenou změnou hesla: jen změna hesla a odhlášení (volá teacher.php před kontrolou přihlášení). */
function teacher59_handle_forced_post(string $action): void
{
    if (teacher59_mode() === 'legacy' || teacher59_session_state() !== 'must_change') return;
    if ($action === 'teacher_logout') return;
    teacher59_no_store();
    if ($action === 'teacher59_self_password') {
        $result = teacher59_self_apply($action, $_POST, (string)teacher59_current_id());
        teacher_flash($result['flash'], (string)($result['type'] ?? 'ok'));
        teacher_redirect($result['params']);
    }
    teacher_flash('Nejdřív si nastavte vlastní heslo.', 'error');
    teacher_redirect();
}
