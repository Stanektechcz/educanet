<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v59 · SECFIX – limity učitelských účtů (načítá teacher_accounts_v59.php; konstanty a cesty jsou tam):
 *   SEC59-03 zámek přihlášení: login + IP klienta (TEACHER59_LOCK_LIMIT / 15 min) = dočasný zámek jen té IP;
 *            login napříč IP (TEACHER59_THROTTLE_LIMIT / 15 min) jen zpomalí (progresivní zpoždění), nikdy nezamkne.
 *            Úložiště teacher_accounts_v59_locks.json.php – solené hashe loginu a IP (žádný login ani IP v čitelné podobě).
 *   SEC59-04 hlučný log (neúspěšná přihlášení, zámek, sdílený klíč, zamítnutí rozsahu) zvlášť od bezpečnostních
 *            událostí: teacher_accounts_v59_noise.json.php, strop TEACHER59_NOISE_LIMIT, max TEACHER59_NOISE_PER_MINUTE
 *            záznamů na (událost, aktér/IP) za minutu – záplava nevytlačí záznamy o založení účtu, resetu apod.
 */

/** Klíč limitu hlučného logu: událost + aktér (id účtu), bez aktéra IP – solený, IP se neukládá. */
function teacher59_noise_key(string $event, ?string $actorId, string $salt): string
{
    $who = ($actorId !== null && $actorId !== '') ? 'a:' . $actorId : 'ip:' . (string)($_SERVER['REMOTE_ADDR'] ?? 'cli');
    return substr(hash('sha256', $salt . '|' . $event . '|' . $who), 0, 24);
}

/** Hlučný log: max TEACHER59_NOISE_PER_MINUTE záznamů na (událost, aktér/IP) za minutu, strop TEACHER59_NOISE_LIMIT řádků. */
function teacher59_noise_log(array $row, ?string $actorId): void
{
    $path = teacher59_noise_path();
    $minute = intdiv(time(), 60);
    $event = (string)$row['event'];
    try {
        $store = storage_read($path, false);
        $salt = (string)($store['salt'] ?? '');
        $rl = is_array($store['rl'] ?? null) ? $store['rl'] : [];
        if ($salt !== '') {
            $seen = $rl[teacher59_noise_key($event, $actorId, $salt)] ?? null;
            if (is_array($seen) && (int)($seen[0] ?? 0) === $minute && (int)($seen[1] ?? 0) >= TEACHER59_NOISE_PER_MINUTE) return; // bez zápisu
        }
        storage_update($path, static function (array $store) use ($row, $actorId, $minute, $event): array {
            if (empty($store['salt'])) $store['salt'] = bin2hex(random_bytes(16));
            $key = teacher59_noise_key($event, $actorId, (string)$store['salt']);
            $rl = array_filter(is_array($store['rl'] ?? null) ? $store['rl'] : [], static fn($v): bool => is_array($v) && (int)($v[0] ?? 0) === $minute);
            $seen = (int)($rl[$key][1] ?? 0);
            $store['rl'] = $rl;
            if ($seen >= TEACHER59_NOISE_PER_MINUTE) return $store;
            $store['rl'][$key] = [$minute, $seen + 1];
            $rows = array_values(array_filter(is_array($store['rows'] ?? null) ? $store['rows'] : [], 'is_array'));
            $rows[] = $row;
            $store['rows'] = count($rows) > TEACHER59_NOISE_LIMIT ? array_slice($rows, -TEACHER59_NOISE_LIMIT) : $rows;
            return $store;
        });
    } catch (Throwable $e) {
        error_log('EDUCANET v59 hlučný log účtů: ' . $e->getMessage());
    }
}

// ---------------------------------------------------------------------------
// Zámek přihlášení (SEC59-03)
// ---------------------------------------------------------------------------

/** Solené klíče zámku [login, IP klienta] – v úložišti není login ani IP v čitelné podobě. */
function teacher59_lock_keys(string $login, string $salt): array
{
    return [
        substr(hash('sha256', $salt . '|login|' . teacher59_normalize_login($login)), 0, 32),
        substr(hash('sha256', $salt . '|ip|' . (string)($_SERVER['REMOTE_ADDR'] ?? 'cli')), 0, 24),
    ];
}

/** @return array{ip:int, all:int, max_ip:int} chyby loginu v okně: z této IP, napříč IP, nejvíc z jedné IP */
function teacher59_lock_counts(string $login, ?int $now = null): array
{
    $now ??= time();
    $zero = ['ip' => 0, 'all' => 0, 'max_ip' => 0];
    try {
        $store = storage_read(teacher59_locks_path(), false);
    } catch (Throwable $e) {
        error_log('EDUCANET v59 zámek přihlášení: ' . $e->getMessage());
        return $zero;
    }
    if ((string)($store['salt'] ?? '') === '') return $zero;
    [$loginKey, $ipKey] = teacher59_lock_keys($login, (string)$store['salt']);
    $entry = is_array($store['logins'][$loginKey] ?? null) ? $store['logins'][$loginKey] : [];
    $recent = static fn($times): array => auth_rate_limit_recent(is_array($times) ? $times : [], TEACHER59_LOCK_WINDOW, $now);
    $maxIp = 0;
    foreach ((array)($entry['ip'] ?? []) as $times) $maxIp = max($maxIp, count($recent($times)));
    return ['ip' => count($recent($entry['ip'][$ipKey] ?? [])), 'all' => count($recent($entry['all'] ?? [])), 'max_ip' => $maxIp];
}

/** Zaznamená chybu loginu z této IP; vrací nové počty {ip, all}. Úklid starých záznamů při každém zápisu. */
function teacher59_lock_fail(string $login, ?int $now = null): array
{
    $now ??= time();
    $counts = ['ip' => 0, 'all' => 0];
    try {
        storage_update(teacher59_locks_path(), static function (array $store) use ($login, $now, &$counts): array {
            if (empty($store['salt'])) $store['salt'] = bin2hex(random_bytes(16));
            [$loginKey, $ipKey] = teacher59_lock_keys($login, (string)$store['salt']);
            $logins = [];
            foreach ((array)($store['logins'] ?? []) as $key => $entry) {
                $ips = [];
                foreach ((array)($entry['ip'] ?? []) as $ip => $times) {
                    $kept = auth_rate_limit_recent((array)$times, TEACHER59_LOCK_WINDOW, $now);
                    if ($kept !== []) $ips[(string)$ip] = $kept;
                }
                $all = auth_rate_limit_recent((array)($entry['all'] ?? []), TEACHER59_LOCK_WINDOW, $now);
                if ($ips !== [] || $all !== []) $logins[(string)$key] = ['ip' => $ips, 'all' => $all];
            }
            $entry = $logins[$loginKey] ?? ['ip' => [], 'all' => []];
            unset($logins[$loginKey]);
            $entry['ip'][$ipKey] = array_slice(array_merge($entry['ip'][$ipKey] ?? [], [$now]), -2 * TEACHER59_LOCK_LIMIT);
            $entry['all'] = array_slice(array_merge($entry['all'], [$now]), -2 * TEACHER59_THROTTLE_LIMIT);
            $logins[$loginKey] = $entry; // naposledy aktivní na konec (strop odřízne nejstarší)
            $counts = ['ip' => count($entry['ip'][$ipKey]), 'all' => count($entry['all'])];
            $store['logins'] = array_slice($logins, -TEACHER59_LOCK_MAX_LOGINS, null, true);
            return $store;
        });
    } catch (Throwable $e) {
        error_log('EDUCANET v59 zámek přihlášení: ' . $e->getMessage());
    }
    return $counts;
}

/** Smaže chyby loginu z této IP ($allIps = false, po úspěchu), nebo ze všech IP (odemknutí adminem/CLI). */
function teacher59_lock_clear(string $login, bool $allIps): void
{
    if (!is_file(teacher59_locks_path())) return;
    try {
        storage_update(teacher59_locks_path(), static function (array $store) use ($login, $allIps): array {
            if ((string)($store['salt'] ?? '') === '') return $store;
            [$loginKey, $ipKey] = teacher59_lock_keys($login, (string)$store['salt']);
            if ($allIps) unset($store['logins'][$loginKey]);
            elseif (isset($store['logins'][$loginKey]['ip'])) unset($store['logins'][$loginKey]['ip'][$ipKey]);
            return $store;
        });
    } catch (Throwable $e) {
        error_log('EDUCANET v59 zámek přihlášení: ' . $e->getMessage());
    }
}

/** Zpoždění pokusu (ms) podle chyb loginu napříč IP – jen zpomalení, nikdy odmítnutí správného hesla. */
function teacher59_throttle_delay_ms(int $allFails): int
{
    if ($allFails < TEACHER59_THROTTLE_LIMIT) return 0;
    return min(TEACHER59_THROTTLE_MAX_MS, TEACHER59_THROTTLE_STEP_MS * (1 + intdiv($allFails - TEACHER59_THROTTLE_LIMIT, 20)));
}

/** Pro správu účtů: zamčeno z některé IP, nebo zpomaleno napříč IP. */
function teacher59_is_locked(string $login): bool
{
    $counts = teacher59_lock_counts($login);
    return $counts['max_ip'] >= TEACHER59_LOCK_LIMIT || $counts['all'] >= TEACHER59_THROTTLE_LIMIT;
}

function teacher59_unlock_login(string $login): void
{
    teacher59_lock_clear($login, true);
}
