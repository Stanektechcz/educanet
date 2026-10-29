<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v70 · Přehled správy pro administrátora (logika, jen čtení).
 * Na jednom místě: účty učitelů a role (v59), pokrytí tříd učiteli, poslední události účtů (bez IP, hesel a OTP),
 * zdraví provozu (v58 úložiště a zálohy, v61 týdenní kontrola), kvalita dat (v46.2) a registr identit žáků (v58),
 * k tomu doporučení „co udělat“ s odkazem na záložku nebo příkaz. Nic se nezapisuje a nic se nespouští.
 * Záložka je jen pro administrátora (modul 'admin' => true + TEACHER59_ADMIN_TABS).
 */

const AD70_BACKUP_WARN_DAYS = 7;
const AD70_HEALTH_WARN_DAYS = 8;
const AD70_EVENTS = 8;

/** Lidský popis události účtu (neznámý kód se vypíše tak, jak je). */
function ad70_event_label(string $event): string
{
    return [
        'created' => 'Založen účet', 'otp_issued' => 'Vydáno jednorázové heslo', 'pw_changed' => 'Změna hesla', 'login_ok' => 'Přihlášení',
        'login_fail' => 'Neúspěšné přihlášení', 'logout' => 'Odhlášení', 'locked' => 'Účet dočasně zamčen', 'unlocked' => 'Účet odemčen',
        'expired' => 'Jednorázové heslo vypršelo', 'reassigned' => 'Změna rolí nebo tříd', 'sessions_revoked' => 'Odhlášena všechna zařízení',
        'scope_denied' => 'Zamítnutý přístup mimo rozsah', 'throttled' => 'Zpomalení po pokusech', 'reclaimed' => 'Převzetí starých dat',
        'reclaim_refused' => 'Převzetí dat odmítnuto', 'legacy_key_used' => 'Použit sdílený klíč', 'legacy_session_cleared' => 'Ukončena stará relace',
    ][$event] ?? $event;
}

/** Účty učitelů a pokrytí tříd. */
function ad70_accounts(): array
{
    $mode = function_exists('teacher59_mode') ? teacher59_mode() : 'legacy';
    $out = ['mode' => $mode, 'total' => 0, 'roles' => ['admin' => 0, 'teacher' => 0, 'assistant' => 0], 'active' => 0, 'disabled' => 0,
        'otp' => 0, 'otp_expired' => 0, 'locked' => 0, 'never' => 0, 'coverage' => [], 'events' => []];
    $classes = function_exists('teacher59_all_class_ids') ? teacher59_all_class_ids() : ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    foreach ($classes as $classId) $out['coverage'][$classId] = ['teacher' => 0, 'assistant' => 0];
    if ($mode !== 'accounts') return $out;
    $logins = [];
    foreach (teacher59_accounts() as $account) {
        if (!is_array($account)) continue;
        $out['total']++;
        $role = (string)($account['role'] ?? 'teacher');
        if (isset($out['roles'][$role])) $out['roles'][$role]++;
        $active = (string)($account['status'] ?? '') === 'active';
        $out[$active ? 'active' : 'disabled']++;
        $logins[(string)($account['id'] ?? '')] = (string)($account['login'] ?? '');
        if (!$active) continue;
        $otp = teacher59_otp_status($account);
        $out['otp'] += $otp === 'otp' ? 1 : 0;
        $out['otp_expired'] += $otp === 'expired' ? 1 : 0;
        $out['never'] += empty($account['last_login_at']) ? 1 : 0;
        $out['locked'] += function_exists('teacher59_is_locked') && teacher59_is_locked((string)($account['login'] ?? '')) ? 1 : 0;
        if ($role === 'admin') continue;
        $seen = [];
        foreach ((array)($account['assignments'] ?? []) as $a) {
            $cid = is_array($a) ? (string)($a['class_id'] ?? '') : '';
            if (!isset($out['coverage'][$cid]) || isset($seen[$cid])) continue;
            $seen[$cid] = true;
            $out['coverage'][$cid][$role === 'assistant' ? 'assistant' : 'teacher']++;
        }
    }
    foreach (teacher59_log_rows(AD70_EVENTS) as $row) {
        if (!is_array($row)) continue;
        $out['events'][] = ['at' => (string)($row['at'] ?? ''), 'event' => ad70_event_label((string)($row['event'] ?? '')), 'target' => $logins[(string)($row['target_id'] ?? '')] ?? ''];
    }
    return $out;
}

/** Provoz: úložiště, zálohy, týdenní kontrola, PHP rozšíření. */
function ad70_ops(bool $full): array
{
    $out = ['size' => null, 'files' => null, 'free' => null, 'backup_days' => null, 'backup_at' => '', 'missing_ext' => [], 'health' => 'none', 'health_days' => null, 'school_year' => ''];
    if (!function_exists('ops58_health')) require_once __DIR__ . '/ops_v58.php';
    $backup = ops58_last_backup();
    if (is_array($backup)) {
        $ts = strtotime((string)($backup['created_at'] ?? '')) ?: 0;
        if ($ts === 0 && is_string($backup['path'] ?? null) && file_exists($backup['path'])) $ts = (int)filemtime($backup['path']);
        $out['backup_days'] = $ts > 0 ? intdiv(max(0, time() - $ts), 86400) : null;
        $out['backup_at'] = $ts > 0 ? date('j. n. Y', $ts) : '';
    }
    foreach (['sodium', 'openssl', 'zip', 'intl'] as $ext) if (!extension_loaded($ext)) $out['missing_ext'][] = $ext;
    $out['school_year'] = ops58_school_year();
    if ($full) {
        $health = ops58_health();
        $out['size'] = (int)$health['total_size_bytes'];
        $out['files'] = (int)$health['file_count'];
        $out['free'] = $health['free_disk_bytes'];
    }
    if (function_exists('ops61_health_runs')) {
        $runs = ops61_health_runs();
        $last = $runs === [] ? null : end($runs);
        if (is_array($last)) {
            $out['health'] = ops61_health_overall($last);
            $at = strtotime((string)($last['at'] ?? '')) ?: 0;
            $out['health_days'] = $at > 0 ? intdiv(max(0, time() - $at), 86400) : null;
        }
    }
    return $out;
}

/** Kvalita dat (v46.2) a registr identit (v58). */
function ad70_data(): array
{
    $out = ['quality' => null, 'identity' => null];
    if (function_exists('teacher_ops_data_quality')) {
        $q = teacher_ops_data_quality();
        $out['quality'] = ['high' => (int)($q['counts']['high'] ?? 0), 'medium' => (int)($q['counts']['medium'] ?? 0), 'low' => (int)($q['counts']['low'] ?? 0)];
    }
    if (function_exists('identity58_registry')) {
        $reg = identity58_registry();
        $students = (array)($reg['students'] ?? []);
        $active = count(array_filter($students, static fn($s): bool => is_array($s) && (string)($s['status'] ?? 'active') === 'active'));
        $dups = function_exists('identity58_duplicates') ? count(identity58_duplicates($reg)) : 0;
        $out['identity'] = ['total' => count($students), 'active' => $active, 'duplicates' => $dups];
    }
    return $out;
}

/** Doporučení „co udělat“ (seřazeno podle závažnosti). @return list<array{level:string,text:string,tab:string,cmd:string}> */
function ad70_recommendations(array $o): array
{
    $acc = $o['accounts'];
    $ops = $o['ops'];
    $r = [];
    if ($acc['mode'] === 'legacy') $r[] = ['level' => 'warn', 'text' => 'Cockpit běží se sdíleným učitelským klíčem (bez účtů). Každý učitel pak vidí všechny třídy.', 'tab' => '', 'cmd' => 'php tools/v59_teacher_accounts.php'];
    if ($acc['mode'] === 'broken') $r[] = ['level' => 'bad', 'text' => 'Úložiště učitelských účtů je nečitelné – přihlášení je zablokované (fail closed).', 'tab' => '', 'cmd' => 'php tools/preflight.php'];
    if ($ops['backup_days'] === null) $r[] = ['level' => 'bad', 'text' => 'Chybí záloha úložiště.', 'tab' => 'provoz', 'cmd' => 'php tools/backup_storage.php'];
    elseif ($ops['backup_days'] > AD70_BACKUP_WARN_DAYS) $r[] = ['level' => 'warn', 'text' => 'Poslední záloha je ' . $ops['backup_days'] . ' dní stará.', 'tab' => 'provoz', 'cmd' => 'php tools/backup_storage.php'];
    if ($ops['health'] === 'none') $r[] = ['level' => 'warn', 'text' => 'Týdenní kontrola provozu zatím nikdy neproběhla (nastavte ji v plánovači úloh).', 'tab' => 'provoz', 'cmd' => 'php tools/v61_weekly_health.php'];
    elseif ($ops['health'] !== 'PASS') $r[] = ['level' => $ops['health'] === 'FAIL' ? 'bad' : 'warn', 'text' => 'Týdenní kontrola provozu hlásí ' . ($ops['health'] === 'FAIL' ? 'chybu' : 'upozornění') . '.', 'tab' => 'provoz', 'cmd' => ''];
    elseif ($ops['health_days'] !== null && $ops['health_days'] > AD70_HEALTH_WARN_DAYS) $r[] = ['level' => 'warn', 'text' => 'Týdenní kontrola neproběhla ' . $ops['health_days'] . ' dní.', 'tab' => 'provoz', 'cmd' => 'php tools/v61_weekly_health.php'];
    if ($ops['missing_ext'] !== []) $r[] = ['level' => 'warn', 'text' => 'Chybí rozšíření PHP: ' . implode(', ', $ops['missing_ext']) . '.', 'tab' => 'provoz', 'cmd' => 'php tools/preflight.php'];
    if ($acc['otp_expired'] > 0) $r[] = ['level' => 'warn', 'text' => $acc['otp_expired'] . '× vypršelé jednorázové heslo – vydejte nové.', 'tab' => 'ucitele', 'cmd' => ''];
    if ($acc['locked'] > 0) $r[] = ['level' => 'warn', 'text' => $acc['locked'] . ' účtů je dočasně zamčeno po neúspěšných pokusech.', 'tab' => 'ucitele', 'cmd' => ''];
    if ($acc['mode'] === 'accounts') {
        foreach ($acc['coverage'] as $classId => $c) {
            if ($c['teacher'] === 0) $r[] = ['level' => 'warn', 'text' => 'Třída ' . ad70_class_label($classId) . ' nemá přiřazeného učitele (vidí ji jen administrátor).', 'tab' => 'ucitele', 'cmd' => ''];
        }
    }
    $q = $o['data']['quality'];
    if (is_array($q) && $q['high'] + $q['medium'] > 0) $r[] = ['level' => $q['high'] > 0 ? 'bad' : 'warn', 'text' => 'Kvalita dat: ' . $q['high'] . ' vážných a ' . $q['medium'] . ' středních nálezů.', 'tab' => 'quality', 'cmd' => ''];
    $id = $o['data']['identity'];
    if (is_array($id) && $id['duplicates'] > 0) $r[] = ['level' => 'warn', 'text' => 'Registr identit má ' . $id['duplicates'] . ' kolizí jmen.', 'tab' => 'identita', 'cmd' => ''];
    $rank = ['bad' => 0, 'warn' => 1];
    usort($r, static fn(array $a, array $b): int => ($rank[$a['level']] ?? 2) <=> ($rank[$b['level']] ?? 2));
    return $r;
}

function ad70_class_label(string $classId): string
{
    return preg_match('/^class_(\d)([a-z])$/', $classId, $m) === 1 ? $m[1] . '.' . strtoupper($m[2]) : $classId;
}

/** Celý přehled; $full = i rekurzivní velikost úložiště a kvalita dat (dražší, jen na záložce Přehled správy). */
function ad70_overview(bool $full = true): array
{
    $o = ['accounts' => ad70_accounts(), 'ops' => ad70_ops($full), 'data' => $full ? ad70_data() : ['quality' => null, 'identity' => null]];
    $o['recommendations'] = ad70_recommendations($o);
    return $o;
}

/** Ukazatele pro pruh KPI (rozcestník Správa i záložka). */
function ad70_kpi_items(array $o): array
{
    $acc = $o['accounts'];
    $ops = $o['ops'];
    $mode = ['accounts' => 'Účty učitelů', 'legacy' => 'Sdílený klíč', 'broken' => 'Chyba úložiště'][$acc['mode']] ?? $acc['mode'];
    $backup = $ops['backup_days'] === null ? 'Chybí' : ($ops['backup_days'] === 0 ? 'Dnes' : 'Před ' . $ops['backup_days'] . ' d');
    $health = ['PASS' => 'V pořádku', 'WARN' => 'Upozornění', 'FAIL' => 'Chyba', 'none' => 'Neproběhla'][$ops['health']] ?? $ops['health'];
    $bad = count(array_filter($o['recommendations'], static fn(array $r): bool => $r['level'] === 'bad'));
    return [
        ['Přihlašování', $mode, $acc['mode'] === 'accounts' ? $acc['active'] . ' aktivních účtů' : 'bez rolí a rozsahu tříd', $acc['mode'] === 'accounts' ? 'ok' : 'warn'],
        $acc['mode'] === 'accounts' ? ['Čeká na 1. přihlášení', (string)$acc['otp'], $acc['otp_expired'] . ' s vypršelým heslem', $acc['otp_expired'] > 0 ? 'warn' : '']
            : ['Účty učitelů', 'Nezapnuté', 'tools/v59_teacher_accounts.php', 'warn'],
        ['Poslední záloha', $backup, $ops['backup_at'] !== '' ? $ops['backup_at'] : 'tools/backup_storage.php', $ops['backup_days'] === null || $ops['backup_days'] > AD70_BACKUP_WARN_DAYS ? 'warn' : 'ok'],
        ['Týdenní kontrola', $health, $ops['health_days'] !== null ? 'před ' . $ops['health_days'] . ' d' : 'cron tools/v61_weekly_health.php', $ops['health'] === 'PASS' ? 'ok' : 'warn'],
        ['K řešení', (string)count($o['recommendations']), 'z toho vážné: ' . $bad, $bad > 0 ? 'warn' : 'ok'],
    ];
}
