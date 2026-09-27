<?php

declare(strict_types=1);

/**
 * POST přihlášení a účty: google_login, local_register, local_login, acc53_change_password,
 * local_resend_verification, local_forgot_password, local_reset_password, link_account, enter_class,
 * logout_class. Chování beze změny (bezpečnostní úpravy dělá integrátor).
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($action === 'google_login') {
    if (!google_auth_configured()) {
        $_SESSION['flash'] = tr('Google přihlášení zatím není na serveru nakonfigurované.');
        redirect_to('?view=home');
    }
    try {
        $credential = is_string($_POST['credential'] ?? null) ? $_POST['credential'] : '';
        $user = verify_google_id_token($credential);
        session_regenerate_id(true);
        $_SESSION['google_user'] = $user;
        unset($_SESSION['local_user']);
        unset($_SESSION['next_class_id'], $_SESSION['student_label'], $_SESSION['next_test'], $_SESSION['next_result'], $_SESSION['next_practice'], $_SESSION['next_practice_result']);
        if (try_restore_auth_assignment($modules)) redirect_to('?view=dashboard');
        redirect_to('?view=link_account');
    } catch (Throwable $e) {
        $_SESSION['flash'] = $e->getMessage();
        redirect_to('?view=home');
    }
}

if ($action === 'local_register') {
    if (!local_auth_enabled()) { $_SESSION['flash'] = tr('Registrace e-mailem je na serveru vypnutá.'); redirect_to('?view=home'); }
    $email = local_email_normalize((string)($_POST['email'] ?? ''));
    $name = trim(u_substr((string)($_POST['name'] ?? ''), 0, 120));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['password_confirm'] ?? '');
    if (!local_email_is_allowed($email)) { $_SESSION['flash'] = tr('Použij platný školní e-mail @{domain}.', ['domain' => google_workspace_domain()]); redirect_to('?view=home#local-register'); }
    if ($name === '' || u_strlen($name) < 3) { $_SESSION['flash'] = tr('Zadej jméno a příjmení.'); redirect_to('?view=home#local-register'); }
    if ($password !== $confirm) { $_SESSION['flash'] = tr('Hesla se neshodují.'); redirect_to('?view=home#local-register'); }
    if (($passwordError = local_password_validate($password, ['email' => $email, 'name' => $name])) !== null) { $_SESSION['flash'] = $passwordError; redirect_to('?view=home#local-register'); }
    // v58 · SEC58-12: s ověřováním e-mailu má registrace jednotnou odpověď – neprozradí, jestli účet existuje.
    $registerGeneric = tr('Pokud je e-mail volný, poslali jsme na něj ověřovací odkaz (platí 24 hodin). Jestli už účet máš, přihlas se nebo si obnov heslo.');
    $account = [
        'id' => bin2hex(random_bytes(16)), 'email' => $email, 'name' => $name,
        'password_hash' => local_password_hash($password), 'created_at' => date(DATE_ATOM),
        'verified_at' => null, 'verification_token_hash' => null, 'verification_expires_at' => null,
        'reset_token_hash' => null, 'reset_expires_at' => null,
    ];
    if (local_auth_require_email_verification()) {
        if (isset(local_accounts()[$email])) { $_SESSION['flash'] = $registerGeneric; redirect_to('?view=home#local-login'); }
        $token = issue_local_email_verification($account);
        if (!acc58_account_insert($email, $account)) { $_SESSION['flash'] = $registerGeneric; redirect_to('?view=home#local-login'); }
        if (!auth_mail_rate_limit_hit($email) || !send_local_email_verification($account, $token)) {
            $_SESSION['flash'] = tr('Účet byl vytvořen, ale server nedokázal odeslat ověřovací e-mail. Kontaktuj učitele/správce nebo použij Google přihlášení.');
            redirect_to('?view=home#local-login');
        }
        $_SESSION['flash'] = $registerGeneric;
        redirect_to('?view=home#local-login');
    }
    if (isset(local_accounts()[$email])) { $_SESSION['flash'] = tr('Účet pro tento e-mail už existuje. Přihlas se nebo obnov heslo.'); redirect_to('?view=home#local-login'); }
    $account['verified_at'] = date(DATE_ATOM);
    if (!acc58_account_insert($email, $account)) { $_SESSION['flash'] = tr('Účet pro tento e-mail už existuje. Přihlas se nebo obnov heslo.'); redirect_to('?view=home#local-login'); }
    session_regenerate_id(true);
    $_SESSION['local_user'] = local_account_public($account);
    unset($_SESSION['google_user']);
    if (try_restore_auth_assignment($modules)) redirect_to('?view=dashboard');
    redirect_to('?view=link_account');
}

if ($action === 'local_login') {
    if (!local_auth_enabled()) redirect_to('?view=home');
    $email = local_email_normalize((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    // v58 · SEC58-12: limit e-mail+IP, IP napříč účty a účet napříč IP (viz auth_login_buckets v bootstrap.php).
    $loginBuckets = auth_login_buckets($email);
    $bucket = (string)array_key_first($loginBuckets);
    if (!auth_rate_limit_check_all($loginBuckets, AUTH_LOGIN_WINDOW)) { $_SESSION['flash'] = tr('Příliš mnoho neúspěšných pokusů. Zkus to za několik minut.'); redirect_to('?view=home#local-login'); }
    $accounts = local_accounts();
    $account = is_array($accounts[$email] ?? null) ? $accounts[$email] : null;
    $valid = is_array($account) && !empty($account['password_hash']) && password_verify($password, (string)$account['password_hash']);
    if (!$valid) { auth_rate_limit_fail_all($loginBuckets); $_SESSION['flash'] = tr('E-mail nebo heslo není správně.'); redirect_to('?view=home#local-login'); }
    if (local_auth_require_email_verification() && empty($account['verified_at'])) {
        $_SESSION['flash'] = tr('Nejdřív ověř školní e-mail. Pokud odkaz nemáš, pošli si nový.');
        redirect_to('?view=home#local-login');
    }
    // v58: vypršelé jednorázové heslo nebo účet pořád na sdíleném hesle se nepřihlásí.
    if (($acc58Gate = acc58_login_gate($account, null, $password)) !== null) { $_SESSION['flash'] = $acc58Gate; redirect_to('?view=home#local-login'); }
    auth_rate_limit_clear($bucket);
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    if (password_needs_rehash((string)$account['password_hash'], $algo)) {
        $newHash = local_password_hash($password);
        $account = acc58_account_update($email, static fn(array $a): array => array_replace($a, ['password_hash' => $newHash])) ?? $account;
    }
    session_regenerate_id(true);
    $_SESSION['local_user'] = local_account_public($account);
    unset($_SESSION['google_user']);
    unset($_SESSION['next_class_id'], $_SESSION['student_label'], $_SESSION['next_test'], $_SESSION['next_result'], $_SESSION['next_practice'], $_SESSION['next_practice_result']);
    acc53_touch_login($email);
    // v53: účet založený školou má společné první heslo – hned si nastav vlastní.
    if (!empty($account['must_change_password'])) {
        acc58_mark_session_proven($email);
        try_restore_auth_assignment($modules);
        redirect_to('?view=change_password');
    }
    if (try_restore_auth_assignment($modules)) redirect_to(v55_after_login_url($modules));
    redirect_to('?view=link_account');
}

if ($action === 'acc53_change_password') {
    $user = auth_user();
    if (!$user || ($user['provider'] ?? '') !== 'local') redirect_to('?view=home');
    $email = local_email_normalize((string)($user['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (!is_array(local_accounts()[$email] ?? null)) redirect_to('?view=home');
    $acc58Error = acc58_change_password($email, (string)($_POST['current_password'] ?? ''), $password, (string)($_POST['password_confirm'] ?? ''));
    if ($acc58Error !== null) { $_SESSION['flash'] = $acc58Error; redirect_to('?view=change_password'); }
    $_SESSION['flash'] = tr('Heslo je nastavené. Vítej v EDUCANETu!');
    redirect_to('?view=dashboard');
}

if ($action === 'local_resend_verification') {
    $email = local_email_normalize((string)($_POST['email'] ?? ''));
    $accounts = local_accounts();
    $account = is_array($accounts[$email] ?? null) ? $accounts[$email] : null;
    // v58 · SEC58-02: limit 3 e-maily/hod na adresu (počítá se i neexistující účet – neprozradí existenci).
    if (local_email_is_allowed($email) && auth_mail_rate_limit_hit($email) && $account && empty($account['verified_at'])) {
        $token = issue_local_email_verification($account);
        $saved = acc58_account_update($email, static fn(array $a): array => array_replace($a, ['verification_token_hash' => $account['verification_token_hash'], 'verification_expires_at' => $account['verification_expires_at']]));
        if ($saved !== null) send_local_email_verification($account, $token);
    }
    $_SESSION['flash'] = tr('Pokud existuje neověřený účet pro tento e-mail, poslali jsme nový ověřovací odkaz.');
    redirect_to('?view=home#local-login');
}

if ($action === 'local_forgot_password') {
    local_password_reset_request((string)($_POST['email'] ?? ''));
    $_SESSION['flash'] = tr('Pokud účet existuje, poslali jsme na školní e-mail odkaz pro nastavení nového hesla.');
    redirect_to('?view=home#local-login');
}

if ($action === 'local_reset_password') {
    local_password_reset_headers();
    $resetError = local_password_reset_complete((string)($_POST['password'] ?? ''), (string)($_POST['password_confirm'] ?? ''));
    if ($resetError !== null) { $_SESSION['flash'] = $resetError; redirect_to(local_password_reset_session_email() !== null ? '?view=reset_password' : '?view=home#local-login'); }
    $_SESSION['flash'] = tr('Heslo bylo změněno. Teď se můžeš přihlásit.');
    redirect_to('?view=home#local-login');
}

if ($action === 'link_google_account' || $action === 'link_account') {
    $user = auth_user();
    if (!$user) redirect_to('?view=home');
    $targetClass = is_string($_POST['class_id'] ?? null) ? $_POST['class_id'] : '';
    $providedCode = is_string($_POST['class_code'] ?? null) ? trim($_POST['class_code']) : '';
    if (!isset($modules[$targetClass])) {
        $_SESSION['flash'] = tr('Vyber platnou třídu.');
        redirect_to('?view=link_account');
    }
    // v58 F6: po přechodu roku platí nové kódy tříd z registru identity.
    $expectedCode = function_exists('identity58_class_code') ? identity58_class_code($targetClass, (string)($modules[$targetClass]['code'] ?? '')) : (string)($modules[$targetClass]['code'] ?? '');
    if (strtoupper($providedCode) !== strtoupper($expectedCode)) { $_SESSION['flash'] = tr('Kód neodpovídá vybrané třídě.'); redirect_to('?view=link_account'); }
    $linkProof = is_string($_POST['link_password'] ?? null) ? (string)$_POST['link_password'] : '';
    $linkBucket = 'link-proof:' . substr(hash('sha256', auth_assignment_key($user) . '|' . (string)($_SERVER['REMOTE_ADDR'] ?? '')), 0, 24);
    if (!auth_rate_limit_check($linkBucket, 10, 900)) { $_SESSION['flash'] = tr('Příliš mnoho pokusů. Zkus to za několik minut.'); redirect_to('?view=link_account'); }
    $label = trim((string)($user['name'] ?? $user['email']));
    if ($targetClass !== 'class_1a') {
        // v58 · PRIV58-13: jméno se píše do pole (adresář se nevypisuje) a ověří se jen proti vybrané třídě.
        // Starý formulář posílal student_key „třída|jméno“ – pořád funguje.
        $studentKey = is_string($_POST['student_key'] ?? null) ? $_POST['student_key'] : '';
        $typedName = is_string($_POST['student_name'] ?? null) ? u_substr(trim($_POST['student_name']), 0, 120) : '';
        $needle = $typedName !== '' ? normalized_person_name($typedName) : (str_starts_with($studentKey, $targetClass . '|') ? substr($studentKey, strlen($targetClass) + 1) : '');
        $match = null;
        foreach ($needle !== '' ? student_directory() : [] as $row) {
            if ((string)($row['class_id'] ?? '') !== $targetClass) continue;
            if (normalized_person_name(trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''))) === $needle) { $match = $row; break; }
        }
        if (!is_array($match)) {
            auth_rate_limit_fail($linkBucket);
            $_SESSION['flash'] = tr('Tohle jméno v seznamu vybrané třídy nenacházíme. Napiš jméno a příjmení tak, jak je má učitel, nebo se obrať na učitele.');
            redirect_to('?view=link_account');
        }
        $label = trim((string)$match['first_name'] . ' ' . (string)$match['last_name']);
    }
    if (($linkError = acc58_link_allowed($targetClass, $label, auth_assignment_key($user), $linkProof)) !== null) {
        if ($linkProof !== '') auth_rate_limit_fail($linkBucket);
        $_SESSION['flash'] = $linkError;
        redirect_to('?view=link_account');
    }
    bind_auth_account($user, $targetClass, $label);
    redirect_to('?view=dashboard');
}

if ($action === 'enter_class') {
    if (!educanet_dev_bypass_enabled() && getenv('EDUCANET_ALLOW_FREE_CLASS_ENTRY') !== '1') { $_SESSION['flash'] = tr('Do třídy se vstupuje školním účtem nebo kódem hodiny.'); redirect_to('?view=home'); }
    $classId = class_from_code($modules, (string)($_POST['class_code'] ?? ''));
    $student = trim((string)($_POST['student_label'] ?? ''));
    if ($classId === null) {
        $_SESSION['flash'] = tr('Kód třídy se nepodařilo najít.');
        redirect_to('?view=home');
    }
    $_SESSION['next_class_id'] = $classId;
    $_SESSION['student_label'] = u_substr($student !== '' ? $student : 'Anonymní student', 0, 120);
    unset($_SESSION['next_test'], $_SESSION['next_result'], $_SESSION['next_practice'], $_SESSION['next_practice_result'], $_SESSION['next_extra_submission']);
    redirect_to('?view=dashboard');
}

if ($action === 'logout_class') {
    unset($_SESSION['next_class_id'], $_SESSION['student_label'], $_SESSION['next_test'], $_SESSION['next_result'], $_SESSION['next_practice'], $_SESSION['next_practice_result'], $_SESSION['next_extra_submission'], $_SESSION['google_user'], $_SESSION['local_user']);
    // Sdílené školní počítače: celá session zanikne a další uživatel dostane nové ID; jazyk se vrací na češtinu (v59).
    edu_clear_locale();
    $_SESSION = [];
    session_destroy();
    session_start();
    session_regenerate_id(true);
    redirect_to('?view=home');
}
