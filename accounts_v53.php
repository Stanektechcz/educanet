<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v53 · Školní účty podle jmen.
 * - e-mail vždy jmeno.prijmeni@educanet.cz (bez diakritiky),
 * - v58: první přihlášení jednorázovým heslem z kartičky od učitele (accounts_v58.php),
 * - hned po prvním přihlášení si žák musí nastavit vlastní heslo.
 */

require_once __DIR__ . '/accounts_v58.php';

/**
 * Bývalé společné heslo (do v57). Od v58 ho nedostane žádný účet – konstanta slouží jen k rozpoznání
 * a zablokování starých účtů (migrace, acc58_login_gate) a k zákazu ve validátoru hesel.
 */
const ACC53_DEFAULT_PASSWORD = 'demo001';

function acc53_state_path(): string
{
    return STORAGE_DIR . '/accounts_v53_state.json.php';
}

/** „Jan Novák“ → jan.novak ; „Marie Anna Dvořáková“ → marie-anna.dvorakova */
function acc53_email_local(string $label): string
{
    $parts = preg_split('/\s+/u', trim($label)) ?: [];
    $parts = array_values(array_filter(array_map(static function (string $p): string {
        if (class_exists('Transliterator')) {
            $t = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC; Lower()');
            if ($t) $p = (string)$t->transliterate($p);
        } else {
            $p = strtolower((string)(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $p) ?: $p));
        }
        return (string)preg_replace('/[^a-z0-9]+/', '', strtolower($p));
    }, $parts), static fn(string $p): bool => $p !== ''));
    if (!$parts) return '';
    $last = array_pop($parts);
    return ($parts ? implode('-', $parts) . '.' : '') . $last;
}

function acc53_email(string $label, array $taken = []): string
{
    $local = acc53_email_local($label);
    if ($local === '') return '';
    $domain = '@' . google_workspace_domain();
    $email = $local . $domain;
    $i = 1;
    while (isset($taken[$email])) { $i++; $email = $local . $i . $domain; }
    return $email;
}

function acc53_is_demo_account(array $account): bool
{
    return !empty($account['demo_account']) || str_starts_with((string)($account['email'] ?? ''), 'demo.');
}

/**
 * Vytvoří nebo doplní účet a jeho propojení s třídou. Existující účty nikdy nepřepisuje.
 * v58: nový účet dostane jednorázové heslo (vždy s vynucenou změnou). Otevřené heslo se neukládá;
 * vrací se jednou v klíči 'password' (jen u nově založeného účtu) pro okamžitý výpis v CLI.
 * $options: email, demo, by ('system'|'cli'|'teacher'), ttl_days.
 */
function acc53_ensure_account(string $label, string $classId, array $options = []): array
{
    $accounts = local_accounts();
    $wanted = local_email_normalize((string)($options['email'] ?? ''));
    if ($wanted === '') {
        foreach ($accounts as $email => $row) {
            if (is_array($row) && (string)($row['student_label'] ?? '') === $label && (string)($row['class_id'] ?? '') === $classId) {
                return ['email' => (string)$email, 'created' => false, 'account' => $row, 'password' => null];
            }
        }
        $wanted = acc53_email($label, $accounts);
    }
    if ($wanted === '' || isset($accounts[$wanted])) {
        $existing = is_array($accounts[$wanted] ?? null) ? $accounts[$wanted] : [];
        return ['email' => $wanted, 'created' => false, 'account' => $existing, 'password' => null];
    }
    $by = (string)($options['by'] ?? 'system');
    $plain = acc58_generate_valid_otp(['email' => $wanted, 'name' => $label]);
    $now = time();
    $account = acc58_with_otp([
        'id' => bin2hex(random_bytes(16)),
        'email' => $wanted,
        'name' => $label,
        'created_at' => date(DATE_ATOM, $now),
        // Účet zakládá škola, proto se e-mail neověřuje odkazem.
        'verified_at' => date(DATE_ATOM, $now),
        'verified_by' => 'school_provisioning',
        'verification_token_hash' => null, 'verification_expires_at' => null,
        'reset_token_hash' => null, 'reset_expires_at' => null,
        'class_id' => $classId,
        'student_label' => $label,
        'demo_account' => !empty($options['demo']),
    ], acc58_otp_fields($plain, $wanted, $by, (int)($options['ttl_days'] ?? ACC58_OTP_TTL_DAYS), $now));

    $created = false;
    storage_update(local_accounts_path(), static function (array $rows) use ($wanted, $account, &$created): array {
        if (isset($rows[$wanted])) return $rows; // souběžně založený jinde – nepřepisovat
        $rows[$wanted] = $account;
        $created = true;
        return $rows;
    });
    if (!$created) {
        $existing = local_accounts()[$wanted] ?? [];
        return ['email' => $wanted, 'created' => false, 'account' => is_array($existing) ? $existing : [], 'password' => null];
    }

    // Propojení účtu s třídou, aby žák po přihlášení rovnou viděl svá data.
    $key = 'local:' . $account['id'];
    storage_update(STORAGE_DIR . '/student_accounts.json.php', static function (array $map) use ($key, $wanted, $classId, $label): array {
        $map[$key] = ['provider' => 'local', 'email' => $wanted, 'class_id' => $classId, 'student_label' => $label, 'linked_at' => date(DATE_ATOM)];
        return $map;
    });
    acc58_log_many([acc58_log_entry('create', $wanted, $classId, $by)]);
    return ['email' => $wanted, 'created' => true, 'account' => $account, 'password' => $plain];
}

/** Založí účty všem žákům ze seznamu tříd (idempotentně). Hesla nevrací – kartičky tiskne učitel. */
function acc53_provision_all(array $modules, bool $force = false, string $by = 'system'): array
{
    $directory = student_directory();
    $signature = count($directory) . ':' . substr(hash('sha256', (string)json_encode(array_map(
        static fn(array $r): string => (string)($r['class_id'] ?? '') . '|' . (string)($r['first_name'] ?? '') . (string)($r['last_name'] ?? ''), $directory
    ))), 0, 16);
    $state = load_php_json(acc53_state_path());
    if (!$force && (string)($state['signature'] ?? '') === $signature) return ['created' => 0, 'ran' => false];

    $created = 0;
    foreach ($directory as $row) {
        if (!is_array($row)) continue;
        $classId = (string)($row['class_id'] ?? '');
        $label = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        if ($classId === '' || $label === '' || !isset($modules[$classId])) continue;
        $res = acc53_ensure_account($label, $classId, ['by' => $by]);
        if ($res['created']) $created++;
    }
    storage_update(acc53_state_path(), static fn(array $s): array => ['signature' => $signature, 'last_run_at' => date(DATE_ATOM), 'created' => $created]);
    return ['created' => $created, 'ran' => true];
}

/** Přehled přístupů pro učitele (bez hesel; stav podle v58). */
function acc53_class_accounts(string $classId): array
{
    return array_map(static fn(array $r): array => [
        'email' => $r['email'],
        'label' => $r['label'],
        'demo' => $r['demo'],
        'must_change' => $r['status'] !== 'own',
        'status' => $r['status'],
        'password_hint' => acc58_status_label($r['status']),
        'expires_at' => $r['expires_at'],
        'last_login_at' => $r['last_login_at'],
    ], acc58_class_rows($classId));
}

/** v58: místo společného hesla vydá nové jednorázové heslo (žák si ho po přihlášení musí změnit). Vrací ho. */
function acc53_reset_password(string $email, string $by = 'cli'): string
{
    return acc58_issue_otp($email, $by);
}

/** Vlastní heslo žáka: zruší vynucenou změnu i jednorázové heslo včetně šifrované kopie. */
function acc53_set_password(string $email, string $password): void
{
    $hash = local_password_hash($password);
    $saved = acc58_account_update($email, static function (array $row) use ($hash): array {
        unset($row['otp'], $row['initial_password']);
        return array_replace($row, ['password_hash' => $hash, 'must_change_password' => false, 'password_changed_at' => date(DATE_ATOM)]);
    });
    if ($saved === null) throw new RuntimeException('Účet neexistuje.');
}

/** Poslední přihlášení – zapisuje se nejvýš jednou za hodinu na účet (celá třída se přihlašuje naráz). */
const ACC53_TOUCH_INTERVAL = 3600;

function acc53_touch_login(string $email): void
{
    $email = local_email_normalize($email);
    $row = local_accounts()[$email] ?? null;
    if (!is_array($row)) return;
    $last = strtotime((string)($row['last_login_at'] ?? '')) ?: 0;
    if ($last > time() - ACC53_TOUCH_INTERVAL) return;
    acc58_account_update($email, static fn(array $a): array => array_replace($a, ['last_login_at' => date(DATE_ATOM)]));
}

function acc53_must_change_password(): bool
{
    $user = auth_user();
    if (!$user || ($user['provider'] ?? '') !== 'local') return false;
    $account = local_accounts()[local_email_normalize((string)($user['email'] ?? ''))] ?? null;
    return is_array($account) && !empty($account['must_change_password']);
}
