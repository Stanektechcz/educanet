<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – procesy, služby, logy, balíčky a disky.
 * Služby i procesy jsou jen záznamy v poli světa; „spuštění“ znamená změnu stavu v paměti.
 */

function lab57_cmds_sys(): array
{
    return [
        'ps' => 'lab57_cmd_ps', 'top' => 'lab57_cmd_top', 'kill' => 'lab57_cmd_kill', 'pkill' => 'lab57_cmd_pkill',
        'free' => 'lab57_cmd_free', 'systemctl' => 'lab57_cmd_systemctl', 'service' => 'lab57_cmd_service',
        'journalctl' => 'lab57_cmd_journalctl', 'nginx' => 'lab57_cmd_nginx', 'apt' => 'lab57_cmd_apt', 'apt-get' => 'lab57_cmd_apt',
        'df' => 'lab57_cmd_df', 'du' => 'lab57_cmd_du',
    ];
}

// ---------------------------------------------------------------------------
// Procesy
// ---------------------------------------------------------------------------

function lab57_proc_name(string $cmd): string
{
    $first = (string)strtok(ltrim($cmd, '-'), ' ');
    if (str_starts_with($first, '[')) return trim($first, '[]');
    $base = basename(rtrim($first, ':'));
    return $base === '' ? $cmd : $base;
}

function lab57_ps_rows(Lab57World $w, string $self): array
{
    $rows = $w->procs;
    $rows[$w->pidCounter + 1] = ['user' => $w->effectiveUser(), 'cmd' => $self, 'cpu' => 0.0, 'mem' => 0.1, 'vsz' => 11200, 'rss' => 4300, 'tty' => 'pts/0', 'stat' => 'R+', 'start' => $w->now, 'service' => null];
    ksort($rows);
    return $rows;
}

function lab57_cpu_time(array $proc, int $now): int
{
    $elapsed = max(0, $now - (int)($proc['start'] ?? $now));
    return (int)($elapsed * ((float)($proc['cpu'] ?? 0) / 100));
}

function lab57_cmd_ps(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $style = 'tty';
    $sort = null;
    $filterUser = null;
    $filterPid = null;
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if (in_array($a, ['aux', '-aux', 'axu', 'ax', 'au'], true)) { $style = 'aux'; continue; }
        if (in_array($a, ['-ef', '-e', '-A', '-eF', '-f'], true)) { $style = 'ef'; continue; }
        if (str_starts_with($a, '--sort=')) { $sort = substr($a, 7); continue; }
        if ($a === '--sort') { $sort = (string)($args[++$i] ?? ''); continue; }
        if ($a === '-u' || $a === '-U') { $filterUser = (string)($args[++$i] ?? ''); $style = $style === 'tty' ? 'aux' : $style; continue; }
        if ($a === '-p') { $filterPid = (int)($args[++$i] ?? 0); $style = $style === 'tty' ? 'aux' : $style; continue; }
        $p->err("error: unsupported option (BSD syntax)\n\nUsage:\n ps [options]\n\n Try 'ps --help <simple|list|output|threads|misc|all>'\n");
        $w->tip(tr('Nejčastěji se používá ps aux (všechny procesy) nebo ps -ef.'));
        return 1;
    }
    $rows = lab57_ps_rows($w, $style === 'aux' ? 'ps ' . implode(' ', $args) : 'ps');
    if ($filterUser !== null) $rows = array_filter($rows, static fn(array $r): bool => (string)$r['user'] === $filterUser);
    if ($filterPid !== null) $rows = array_filter($rows, static fn(int|string $pid): bool => (int)$pid === $filterPid, ARRAY_FILTER_USE_KEY);
    if ($sort !== null) {
        $desc = str_starts_with($sort, '-');
        $key = ltrim($sort, '-+');
        uksort($rows, static function (int|string $a, int|string $b) use ($rows, $key, $desc): int {
            $va = match ($key) { '%cpu', 'pcpu' => (float)$rows[$a]['cpu'], '%mem', 'pmem' => (float)$rows[$a]['mem'], 'rss' => (float)$rows[$a]['rss'], default => (float)$a };
            $vb = match ($key) { '%cpu', 'pcpu' => (float)$rows[$b]['cpu'], '%mem', 'pmem' => (float)$rows[$b]['mem'], 'rss' => (float)$rows[$b]['rss'], default => (float)$b };
            return $desc ? $vb <=> $va : $va <=> $vb;
        });
    }
    if ($style === 'tty') {
        $p->line('    PID TTY          TIME CMD');
        foreach ($rows as $pid => $r) {
            if ((string)$r['tty'] !== 'pts/0') continue;
            $p->line(str_pad((string)$pid, 7, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$r['tty'], 8) . ' ' . gmdate('H:i:s', lab57_cpu_time($r, $w->now)) . ' ' . lab57_proc_name((string)$r['cmd']));
        }
        return 0;
    }
    if ($style === 'ef') {
        $p->line('UID          PID    PPID  C STIME TTY          TIME CMD');
        foreach ($rows as $pid => $r) {
            $ppid = $pid <= 2 ? 0 : ((string)$r['tty'] === 'pts/0' ? 1487 : 1);
            if ((int)$pid === 1487) $ppid = 1480;
            $p->line(str_pad((string)$r['user'], 8) . ' ' . str_pad((string)$pid, 7, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$ppid, 7, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)min(99, (int)$r['cpu']), 2, ' ', STR_PAD_LEFT) . ' ' . date('H:i', (int)$r['start']) . ' ' . str_pad((string)$r['tty'], 8) . ' ' . gmdate('H:i:s', lab57_cpu_time($r, $w->now)) . ' ' . $r['cmd']);
        }
        return 0;
    }
    $p->line('USER         PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND');
    foreach ($rows as $pid => $r) {
        $cpuTime = lab57_cpu_time($r, $w->now);
        $p->line(str_pad(mb_substr((string)$r['user'], 0, 8), 8) . ' ' . str_pad((string)$pid, 7, ' ', STR_PAD_LEFT) . ' ' . str_pad(number_format((float)$r['cpu'], 1, '.', ''), 4, ' ', STR_PAD_LEFT) . ' ' . str_pad(number_format((float)$r['mem'], 1, '.', ''), 4, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$r['vsz'], 6, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$r['rss'], 5, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$r['tty'], 8) . ' ' . str_pad((string)$r['stat'], 4) . ' ' . date('H:i', (int)$r['start']) . ' ' . str_pad(intdiv($cpuTime, 60) . ':' . str_pad((string)($cpuTime % 60), 2, '0', STR_PAD_LEFT), 6, ' ', STR_PAD_LEFT) . ' ' . $r['cmd']);
    }
    return 0;
}

function lab57_cmd_top(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $rows = lab57_ps_rows($w, 'top');
    uasort($rows, static fn(array $a, array $b): int => ((float)$b['cpu'] <=> (float)$a['cpu']) ?: ((float)$b['mem'] <=> (float)$a['mem']));
    [$l1, $l5, $l15] = lab57_load_average($w);
    $up = max(60, $w->now - $w->bootTime);
    $cpuUsed = min(100.0, array_sum(array_map(static fn(array $r): float => (float)$r['cpu'], $rows)) / 2);
    $running = count(array_filter($rows, static fn(array $r): bool => str_starts_with((string)$r['stat'], 'R')));
    $p->line('top - ' . date('H:i:s', $w->now) . ' up ' . sprintf('%2d:%02d', intdiv($up, 3600), intdiv($up % 3600, 60)) . ',  1 user,  load average: ' . number_format($l1, 2) . ', ' . number_format($l5, 2) . ', ' . number_format($l15, 2));
    $p->line('Tasks: ' . str_pad((string)count($rows), 3, ' ', STR_PAD_LEFT) . ' total,   ' . $running . ' running, ' . str_pad((string)(count($rows) - $running), 3, ' ', STR_PAD_LEFT) . ' sleeping,   0 stopped,   0 zombie');
    $p->line('%Cpu(s): ' . str_pad(number_format($cpuUsed * 0.8, 1), 4, ' ', STR_PAD_LEFT) . ' us,  ' . number_format($cpuUsed * 0.2, 1) . ' sy,  0.0 ni, ' . str_pad(number_format(100 - $cpuUsed, 1), 4, ' ', STR_PAD_LEFT) . ' id,  0.2 wa,  0.0 hi,  0.0 si,  0.0 st');
    $p->line('MiB Mem :   3934.1 total,   2103.8 free,    810.2 used,   1020.1 buff/cache');
    $p->line('MiB Swap:   1024.0 total,   1024.0 free,      0.0 used.   3035.0 avail Mem');
    $p->line('');
    $p->line('    PID USER      PR  NI    VIRT    RES    SHR S  %CPU  %MEM     TIME+ COMMAND');
    $shown = 0;
    foreach ($rows as $pid => $r) {
        if (++$shown > 15) break;
        $t = lab57_cpu_time($r, $w->now);
        $p->line(str_pad((string)$pid, 7, ' ', STR_PAD_LEFT) . ' ' . str_pad(mb_substr((string)$r['user'], 0, 8), 8) . '  20   0 ' . str_pad((string)$r['vsz'], 7, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)$r['rss'], 6, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)(int)($r['rss'] * 0.6), 6, ' ', STR_PAD_LEFT) . ' ' . substr((string)$r['stat'], 0, 1) . ' ' . str_pad(number_format((float)$r['cpu'], 1), 5, ' ', STR_PAD_LEFT) . ' ' . str_pad(number_format((float)$r['mem'], 1), 5, ' ', STR_PAD_LEFT) . ' ' . str_pad(intdiv($t, 60) . ':' . str_pad((string)($t % 60), 2, '0', STR_PAD_LEFT) . '.00', 9, ' ', STR_PAD_LEFT) . ' ' . lab57_proc_name((string)$r['cmd']));
    }
    $w->tip(tr('top v simulaci ukáže jeden snímek. Ve skutečném terminálu se obnovuje a ukončíš ho klávesou q.'));
    return 0;
}

/** Ukončí proces (s ohledem na práva a signál). Vrací chybovou zprávu nebo null. */
function lab57_kill_pid(Lab57World $w, int $pid, int $signal): ?string
{
    if (!isset($w->procs[$pid])) return 'No such process';
    $proc = $w->procs[$pid];
    if ($pid <= 2) return 'Operation not permitted';
    if (!$w->root && (string)$proc['user'] !== $w->user) return 'Operation not permitted';
    if ($signal === 0) return null;
    if ($signal === 15 && !empty($proc['ignore_term'])) {
        $w->journalAdd('kernel', 'process ' . $pid . ' (' . lab57_proc_name((string)$proc['cmd']) . ') ignored SIGTERM', 'warning');
        return null;
    }
    unset($w->procs[$pid]);
    $service = (string)($proc['service'] ?? '');
    if ($service !== '' && isset($w->services[$service])) {
        $remaining = array_filter($w->procs, static fn(array $r): bool => (string)($r['service'] ?? '') === $service);
        if ($remaining === [] || str_contains((string)$proc['cmd'], 'master')) {
            foreach (array_keys($remaining) as $other) unset($w->procs[$other]);
            $w->services[$service]['active'] = false;
            $w->services[$service]['failed'] = $signal === 9;
            $w->services[$service]['result'] = 'signal';
            $w->services[$service]['since'] = $w->now;
            $w->journalAdd($service, $service . '.service: Main process exited, code=killed, status=' . $signal . '/' . ($signal === 9 ? 'KILL' : 'TERM'), 'warning');
        }
    }
    return null;
}

function lab57_signal_number(string $raw): ?int
{
    $raw = strtoupper(ltrim($raw, '-'));
    if (str_starts_with($raw, 'SIG')) $raw = substr($raw, 3);
    if (ctype_digit($raw)) return (int)$raw;
    return ['HUP' => 1, 'INT' => 2, 'QUIT' => 3, 'KILL' => 9, 'TERM' => 15, 'STOP' => 19, 'CONT' => 18, 'USR1' => 10][$raw] ?? null;
}

function lab57_cmd_kill(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    if ($args === []) { $p->err("kill: usage: kill [-s sigspec | -n signum | -sigspec] pid | jobspec ... or kill -l [sigspec]\n"); return 2; }
    if ($args[0] === '-l' || $args[0] === '-L') {
        $p->line(' 1) SIGHUP	 2) SIGINT	 3) SIGQUIT	 9) SIGKILL	10) SIGUSR1	15) SIGTERM	18) SIGCONT	19) SIGSTOP');
        return 0;
    }
    $signal = 15;
    if ($args[0] === '-s' || $args[0] === '-n') { array_shift($args); $signal = lab57_signal_number((string)array_shift($args)) ?? -1; }
    elseif (str_starts_with($args[0], '-')) { $signal = lab57_signal_number((string)array_shift($args)) ?? -1; }
    if ($signal < 0) { $p->err("bash: kill: invalid signal specification\n"); return 1; }
    $status = 0;
    foreach ($args as $arg) {
        if (!ctype_digit($arg)) { $p->err("bash: kill: $arg: arguments must be process or job IDs\n"); $status = 1; continue; }
        $error = lab57_kill_pid($w, (int)$arg, $signal);
        if ($error !== null) {
            $p->err("bash: kill: ($arg) - $error\n");
            if ($error === 'Operation not permitted') $w->tip(tr('Cizí proces (jiného uživatele) může ukončit jen správce: sudo kill {pid}', ['pid' => $arg]));
            if ($error === 'No such process') $w->tip(tr('Proces s tímto PID neexistuje. Aktuální čísla procesů ukáže ps aux.'));
            $status = 1;
        }
    }
    return $status;
}

function lab57_cmd_pkill(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $signal = 15;
    if ($args !== [] && str_starts_with($args[0], '-')) $signal = lab57_signal_number((string)array_shift($args)) ?? 15;
    $pattern = (string)($args[0] ?? '');
    if ($pattern === '') { $p->err("pkill: no matching criteria specified\nTry `pkill --help' for more information.\n"); return 2; }
    $matched = false;
    foreach ($w->procs as $pid => $proc) {
        if (!str_contains(lab57_proc_name((string)$proc['cmd']), $pattern)) continue;
        $matched = true;
        $error = lab57_kill_pid($w, (int)$pid, $signal);
        if ($error !== null) $p->err("pkill: killing pid $pid failed: $error\n");
    }
    return $matched ? 0 : 1;
}

function lab57_cmd_free(Lab57Proc $p, array $argv): int
{
    $flag = $argv[1] ?? '';
    $vals = [[4028500, 810200, 2154320, 18844, 1064000, 3107756], [1048572, 0, 1048572]];
    $fmt = static function (int $kb) use ($flag): string {
        return match ($flag) {
            '-h' => $kb >= 1048576 ? number_format($kb / 1048576, 1, '.', '') . 'Gi' : number_format($kb / 1024, 0, '.', '') . 'Mi',
            '-m' => (string)intdiv($kb, 1024),
            '-g' => (string)intdiv($kb, 1048576),
            default => (string)$kb,
        };
    };
    $p->line('               total        used        free      shared  buff/cache   available');
    $p->line('Mem:    ' . implode('', array_map(static fn(int $v): string => str_pad($fmt($v), 12, ' ', STR_PAD_LEFT), $vals[0])));
    $p->line('Swap:   ' . implode('', array_map(static fn(int $v): string => str_pad($fmt($v), 12, ' ', STR_PAD_LEFT), $vals[1])));
    return 0;
}

// ---------------------------------------------------------------------------
// Služby
// ---------------------------------------------------------------------------

function lab57_unit_name(string $raw): string
{
    return (string)preg_replace('/\.service$/', '', $raw);
}

/** Kontrola konfigurace služby před spuštěním. Vrací seznam chybových řádků (prázdný = OK). */
function lab57_service_validate(Lab57World $w, string $unit): array
{
    $svc = $w->services[$unit] ?? [];
    $validator = (string)($svc['validate'] ?? '');
    if ($validator === 'nginx') {
        $errors = lab57_nginx_check($w);
        return $errors;
    }
    if ($validator !== '' && isset($w->programs['validate:' . $validator])) {
        return (array)($w->programs['validate:' . $validator])($w, $unit);
    }
    foreach ((array)($svc['requires'] ?? []) as $path) {
        if (!$w->fs->exists((string)$path)) return [$unit . ': chybí soubor ' . $path];
    }
    return [];
}

function lab57_service_start(Lab57World $w, string $unit): bool
{
    $svc = $w->services[$unit];
    foreach ($w->procs as $pid => $proc) if ((string)($proc['service'] ?? '') === $unit) unset($w->procs[$pid]);
    $errors = lab57_service_validate($w, $unit);
    $w->journalAdd('systemd', 'Starting ' . $unit . '.service - ' . $svc['desc'] . '...');
    if ($errors !== []) {
        foreach ($errors as $line) $w->journalAdd($unit, $line, 'err');
        $w->journalAdd('systemd', $unit . '.service: Control process exited, code=exited, status=1/FAILURE', 'err');
        $w->journalAdd('systemd', 'Failed to start ' . $unit . '.service - ' . $svc['desc'] . '.', 'err');
        $w->services[$unit]['active'] = false;
        $w->services[$unit]['failed'] = true;
        $w->services[$unit]['result'] = 'exit-code';
        $w->services[$unit]['since'] = $w->now;
        return false;
    }
    foreach ((array)$svc['procs'] as [$owner, $cmd]) {
        $pid = $w->newPid();
        $w->procs[$pid] = ['user' => $owner, 'cmd' => $cmd, 'cpu' => 0.0, 'mem' => 0.4, 'vsz' => 55000, 'rss' => 5600, 'tty' => '?', 'stat' => 'Ss', 'start' => $w->now, 'service' => $unit];
    }
    $w->services[$unit]['active'] = true;
    $w->services[$unit]['failed'] = false;
    $w->services[$unit]['result'] = 'success';
    $w->services[$unit]['since'] = $w->now;
    $w->journalAdd('systemd', 'Started ' . $unit . '.service - ' . $svc['desc'] . '.');
    return true;
}

function lab57_service_stop(Lab57World $w, string $unit): void
{
    foreach ($w->procs as $pid => $proc) if ((string)($proc['service'] ?? '') === $unit) unset($w->procs[$pid]);
    $w->services[$unit]['active'] = false;
    $w->services[$unit]['failed'] = false;
    $w->services[$unit]['since'] = $w->now;
    $w->journalAdd('systemd', 'Stopping ' . $unit . '.service - ' . $w->services[$unit]['desc'] . '...');
    $w->journalAdd('systemd', 'Stopped ' . $unit . '.service - ' . $w->services[$unit]['desc'] . '.');
}

function lab57_service_state(array $svc): string
{
    if (!empty($svc['active'])) return 'active';
    return !empty($svc['failed']) ? 'failed' : 'inactive';
}

function lab57_journal_line(Lab57World $w, array $entry): string
{
    $unit = (string)$entry['unit'];
    $pid = $unit === 'systemd' ? 1 : (abs(crc32($unit)) % 900 + 300);
    return date('M d H:i:s', (int)$entry['t']) . ' ' . $w->hostname . ' ' . ($unit === 'kernel' ? 'kernel' : $unit . '[' . $pid . ']') . ': ' . $entry['msg'];
}

function lab57_systemctl_status(Lab57Proc $p, string $unit): int
{
    $w = $p->w;
    $svc = $w->services[$unit];
    $state = lab57_service_state($svc);
    $dot = match ($state) { 'active' => "\e[01;32m●\e[0m", 'failed' => "\e[01;31m×\e[0m", default => '○' };
    if (!$p->tty) $dot = $state === 'active' ? '●' : ($state === 'failed' ? '×' : '○');
    $since = (int)($svc['since'] ?? $w->bootTime + 4);
    $ago = max(1, intdiv($w->now - $since, 60));
    $p->line($dot . ' ' . $unit . '.service - ' . $svc['desc']);
    $p->line('     Loaded: loaded (/lib/systemd/system/' . $unit . '.service; ' . (!empty($svc['enabled']) ? 'enabled' : 'disabled') . '; preset: enabled)');
    $active = match ($state) {
        'active' => 'active (running) since ' . date('D Y-m-d H:i:s T', $since) . '; ' . ($ago >= 60 ? intdiv($ago, 60) . 'h ' . ($ago % 60) . 'min' : $ago . 'min') . ' ago',
        'failed' => 'failed (Result: ' . ($svc['result'] ?? 'exit-code') . ') since ' . date('D Y-m-d H:i:s T', $since) . '; ' . $ago . 'min ago',
        default => 'inactive (dead)' . (isset($svc['since']) ? ' since ' . date('D Y-m-d H:i:s T', $since) . '; ' . $ago . 'min ago' : ''),
    };
    if ($p->tty && $state !== 'inactive') $active = ($state === 'active' ? "\e[01;32m" : "\e[01;31m") . strtok($active, ' ') . "\e[0m" . substr($active, strlen((string)strtok($active, ' ')));
    $p->line('     Active: ' . $active);
    $pids = [];
    foreach ($w->procs as $pid => $proc) if ((string)($proc['service'] ?? '') === $unit) $pids[$pid] = $proc;
    if ($pids !== []) {
        $main = (int)array_key_first($pids);
        $p->line('   Main PID: ' . $main . ' (' . lab57_proc_name((string)$pids[$main]['cmd']) . ')');
        $p->line('      Tasks: ' . count($pids) . ' (limit: 4637)');
        $p->line('     Memory: ' . number_format(count($pids) * 2.9, 1) . 'M');
        $p->line('     CGroup: /system.slice/' . $unit . '.service');
        $last = array_key_last($pids);
        foreach ($pids as $pid => $proc) $p->line('             ' . ($pid === $last ? '└─' : '├─') . $pid . ' "' . $proc['cmd'] . '"');
    }
    $entries = array_values(array_filter($w->journal, static fn(array $e): bool => (string)$e['unit'] === $unit || ((string)$e['unit'] === 'systemd' && str_contains((string)$e['msg'], $unit . '.service'))));
    $entries = array_slice($entries, -8);
    if ($entries !== []) {
        $p->line('');
        foreach ($entries as $e) $p->line(lab57_journal_line($w, $e));
    }
    return $state === 'active' ? 0 : 3;
}

function lab57_cmd_systemctl(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !in_array($a, ['--no-pager', '-l', '--full', '-q', '--quiet'], true)));
    $now = in_array('--now', $args, true);
    $args = array_values(array_filter($args, static fn(string $a): bool => $a !== '--now'));
    $failedOnly = in_array('--failed', $args, true) || in_array('--state=failed', $args, true);
    $args = array_values(array_filter($args, static fn(string $a): bool => !in_array($a, ['--failed', '--state=failed', '--type=service', '--all', '-a', '-t', 'service'], true)));
    $action = (string)($args[0] ?? 'list-units');
    $units = array_map('lab57_unit_name', array_slice($args, 1));
    if ($action === 'list-units' || $action === 'list-unit-files' || $failedOnly && $units === []) {
        $p->line('  UNIT' . str_repeat(' ', 26) . 'LOAD   ACTIVE   SUB     DESCRIPTION');
        $count = 0;
        ksort($w->services);
        foreach ($w->services as $name => $svc) {
            $state = lab57_service_state($svc);
            if ($failedOnly && $state !== 'failed') continue;
            if ($state === 'inactive' && !$failedOnly && $action === 'list-units') continue;
            $count++;
            $sub = $state === 'active' ? 'running' : ($state === 'failed' ? 'failed' : 'dead');
            $p->line(($state === 'failed' ? '● ' : '  ') . str_pad($name . '.service', 30) . 'loaded ' . str_pad($state, 8) . ' ' . str_pad($sub, 7) . ' ' . $svc['desc']);
        }
        $p->line('');
        $p->line('LOAD   = Reflects whether the unit definition was properly loaded.');
        $p->line('ACTIVE = The high-level unit activation state, i.e. generalization of SUB.');
        $p->line('SUB    = The low-level unit activation state, values depend on unit type.');
        $p->line($count . ' loaded units listed.');
        return 0;
    }
    if ($action === 'daemon-reload') {
        if (!$w->root) { $p->err("Failed to reload daemon: Access denied\n"); return 1; }
        return 0;
    }
    if ($units === []) { $p->err("Too few arguments.\n"); return 1; }
    $status = 0;
    foreach ($units as $unit) {
        if (!isset($w->services[$unit])) {
            $p->err('Unit ' . $unit . ".service could not be found.\n");
            $w->tip(tr('Seznam služeb ukáže systemctl list-units --type=service.'));
            $status = in_array($action, ['status'], true) ? 4 : 5;
            continue;
        }
        $svc = $w->services[$unit];
        switch ($action) {
            case 'status':
                $status = max($status, lab57_systemctl_status($p, $unit));
                break;
            case 'is-active':
                $state = lab57_service_state($svc);
                $p->line($state);
                if ($state !== 'active') $status = 3;
                break;
            case 'is-enabled':
                $p->line(!empty($svc['enabled']) ? 'enabled' : 'disabled');
                if (empty($svc['enabled'])) $status = 1;
                break;
            case 'is-failed':
                $state = lab57_service_state($svc);
                $p->line($state);
                if ($state !== 'failed') $status = 1;
                break;
            case 'start': case 'stop': case 'restart': case 'reload': case 'enable': case 'disable':
                if (!$w->root) {
                    $verb = $action === 'enable' ? 'enable unit' : ($action === 'disable' ? 'disable unit' : $action . ' ' . $unit . '.service');
                    $p->err('Failed to ' . $verb . ": Access denied\n");
                    $w->tip(tr('Službu spouští a zastavuje správce systému: sudo systemctl {akce} {jednotka}', ['akce' => $action, 'jednotka' => $unit]));
                    $status = 1;
                    break;
                }
                if (!empty($svc['static']) && in_array($action, ['stop', 'disable'], true)) {
                    $p->err('Operation refused, unit ' . $unit . ".service may be requested by dependency only.\n");
                    $status = 1;
                    break;
                }
                if ($action === 'enable' || $action === 'disable') {
                    $w->services[$unit]['enabled'] = $action === 'enable';
                    if ($action === 'enable') $p->err('Created symlink /etc/systemd/system/multi-user.target.wants/' . $unit . '.service → /lib/systemd/system/' . $unit . ".service.\n");
                    else $p->err('Removed "/etc/systemd/system/multi-user.target.wants/' . $unit . ".service\".\n");
                    if (!$now) break;
                    $action = $action === 'enable' ? 'start' : 'stop';
                }
                if ($action === 'stop') { lab57_service_stop($w, $unit); break; }
                if ($action === 'reload' && empty($svc['active'])) { $p->err('Job for ' . $unit . ".service invalid.\n"); $status = 1; break; }
                if ($action === 'start' && !empty($svc['active'])) break;
                if (!lab57_service_start($w, $unit)) {
                    $p->err('Job for ' . $unit . '.service failed because the control process exited with error code.' . "\n" . 'See "systemctl status ' . $unit . '.service" and "journalctl -xeu ' . $unit . '.service" for details.' . "\n");
                    $extra = $unit === 'nginx' ? tr(' – nebo zkontroluj konfiguraci: sudo nginx -t') : '';
                    $w->tip(tr('Služba nenaběhla. Důvod najdeš v logu: journalctl -u {jednotka} -n 20{extra}', ['jednotka' => $unit, 'extra' => $extra]));
                    $status = 1;
                }
                break;
            default:
                $p->err("Unknown command verb '$action'.\n");
                return 1;
        }
    }
    return $status;
}

function lab57_cmd_service(Lab57Proc $p, array $argv): int
{
    if (count($argv) < 3) { $p->err("Usage: service < option > | --status-all | [ service_name [ command | --full-restart ] ]\n"); return 1; }
    return lab57_cmd_systemctl($p, ['systemctl', (string)$argv[2], (string)$argv[1]]);
}

function lab57_cmd_journalctl(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, , $error] = lab57_getopt(array_slice($argv, 1), 'u:n:p:xefr', ['unit' => true, 'lines' => true, 'priority' => true, 'no-pager' => false, 'since' => true, 'follow' => false, 'reverse' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $unit = isset($o['u']) || isset($o['--unit']) ? lab57_unit_name((string)($o['u'] ?? $o['--unit'])) : null;
    $limit = isset($o['n']) || isset($o['--lines']) ? max(0, (int)($o['n'] ?? $o['--lines'])) : (isset($o['e']) ? 1000 : null);
    $priority = (string)($o['p'] ?? $o['--priority'] ?? '');
    $levels = ['emerg' => 0, 'alert' => 1, 'crit' => 2, 'err' => 3, 'warning' => 4, 'notice' => 5, 'info' => 6, 'debug' => 7];
    $maxLevel = $priority === '' ? 7 : ($levels[$priority] ?? (ctype_digit($priority) ? (int)$priority : 7));
    $entries = array_values(array_filter($w->journal, static function (array $e) use ($unit, $maxLevel, $levels): bool {
        if ($unit !== null && (string)$e['unit'] !== $unit && !((string)$e['unit'] === 'systemd' && str_contains((string)$e['msg'], $unit . '.service'))) return false;
        return ($levels[(string)($e['p'] ?? 'info')] ?? 6) <= $maxLevel;
    }));
    if ($limit !== null) $entries = array_slice($entries, -$limit);
    if (isset($o['r']) || isset($o['--reverse'])) $entries = array_reverse($entries);
    if ($entries === []) { $p->line('-- No entries --'); return 0; }
    foreach ($entries as $e) {
        $line = lab57_journal_line($w, $e);
        if ($p->tty && in_array((string)($e['p'] ?? ''), ['err', 'crit', 'alert', 'emerg'], true)) $line = "\e[01;31m" . $line . "\e[0m";
        elseif ($p->tty && (string)($e['p'] ?? '') === 'warning') $line = "\e[01;33m" . $line . "\e[0m";
        $p->line($line);
    }
    if (isset($o['f']) || isset($o['--follow'])) $w->tip(tr('journalctl -f by čekal na nové záznamy; simulace vypíše poslední a skončí.'));
    return 0;
}

// ---------------------------------------------------------------------------
// nginx (kontrola konfigurace a obsluha souborů pro curl)
// ---------------------------------------------------------------------------

function lab57_nginx_directives(): array
{
    return ['user', 'worker_processes', 'pid', 'events', 'worker_connections', 'http', 'sendfile', 'include', 'access_log', 'error_log', 'server', 'listen', 'root', 'index', 'server_name', 'location', 'try_files', 'return', 'error_page', 'types', 'default_type', 'charset', 'gzip', 'autoindex', 'add_header', 'proxy_pass', 'keepalive_timeout', 'client_max_body_size', 'alias', 'rewrite', 'deny', 'allow', 'expires'];
}

/** Zkontroluje syntaxi jednoho konfiguračního souboru. Vrací chybu ve formátu nginx nebo null. */
function lab57_nginx_check_file(string $path, string $content, bool $typesBlock = false): ?string
{
    $depth = 0;
    $lines = explode("\n", $content);
    $known = lab57_nginx_directives();
    $pending = null;
    foreach ($lines as $idx => $raw) {
        $no = $idx + 1;
        $line = trim((string)preg_replace('/#.*$/', '', $raw));
        if ($line === '') continue;
        $tokens = preg_split('/\s+/', $line) ?: [];
        $directive = (string)$tokens[0];
        if ($pending !== null && $directive !== '{') {
            return 'nginx: [emerg] directive "' . $pending[0] . '" is not terminated by ";" in ' . $path . ':' . $pending[1];
        }
        $pending = null;
        if ($line === '}') {
            $depth--;
            if ($depth < 0) return 'nginx: [emerg] unexpected "}" in ' . $path . ':' . $no;
            $typesBlock = false;
            continue;
        }
        if (!$typesBlock && !in_array($directive, $known, true) && $directive !== '{') {
            return 'nginx: [emerg] unknown directive "' . $directive . '" in ' . $path . ':' . $no;
        }
        if ($directive === 'listen' && isset($tokens[1])) {
            $port = (string)preg_replace('/^\[::\]:/', '', rtrim((string)$tokens[1], ';'));
            if (preg_match('/^(\d{1,5}|[\d.]+:\d{1,5})$/', $port) !== 1 || (int)substr($port, (int)strrpos(':' . $port, ':')) > 65535) {
                return 'nginx: [emerg] invalid port in "' . rtrim((string)$tokens[1], ';') . '" of the "listen" directive in ' . $path . ':' . $no;
            }
        }
        if (str_ends_with($line, '{')) {
            $depth++;
            if ($directive === 'types') $typesBlock = true;
            continue;
        }
        if (str_ends_with($line, ';')) continue;
        $pending = [$directive, $no];
    }
    if ($pending !== null) return 'nginx: [emerg] directive "' . $pending[0] . '" is not terminated by ";" in ' . $path . ':' . $pending[1];
    if ($depth > 0) return 'nginx: [emerg] unexpected end of file, expecting "}" in ' . $path . ':' . count($lines);
    return null;
}

/** @return list<string> chyby konfigurace (prázdné = v pořádku) */
function lab57_nginx_check(Lab57World $w): array
{
    $files = ['/etc/nginx/nginx.conf' => false, '/etc/nginx/mime.types' => true];
    foreach ($w->fs->children('/etc/nginx/sites-enabled') as $name) $files['/etc/nginx/sites-enabled/' . $name] = false;
    foreach ($files as $path => $types) {
        $node = $w->fs->get($path);
        if ($node === null) {
            if ($path === '/etc/nginx/nginx.conf') return ['nginx: [emerg] open() "/etc/nginx/nginx.conf" failed (2: No such file or directory)'];
            continue;
        }
        $error = lab57_nginx_check_file($path, (string)($node['c'] ?? ''), $types);
        if ($error !== null) return [$error, 'nginx: configuration file /etc/nginx/nginx.conf test failed'];
    }
    return [];
}

/** Konfigurace serverů nginx: port → [root, index]. */
function lab57_nginx_sites(Lab57World $w): array
{
    $sites = [];
    foreach ($w->fs->children('/etc/nginx/sites-enabled') as $name) {
        $content = (string)($w->fs->get('/etc/nginx/sites-enabled/' . $name)['c'] ?? '');
        preg_match_all('/^\s*listen\s+(?:\[::\]:)?(\d+)/m', $content, $ports);
        preg_match('/^\s*root\s+([^;\s]+)\s*;/m', $content, $root);
        preg_match('/^\s*index\s+([^;]+);/m', $content, $index);
        foreach ($ports[1] as $port) {
            $sites[(int)$port] ??= ['root' => (string)($root[1] ?? '/var/www/html'), 'index' => preg_split('/\s+/', trim((string)($index[1] ?? 'index.html'))) ?: ['index.html']];
        }
    }
    return $sites;
}

/** Odpověď místního nginx na HTTP požadavek. @return array{0:int,1:string,2:string} */
function lab57_nginx_serve(Lab57World $w, int $port, string $path): array
{
    $sites = lab57_nginx_sites($w);
    $site = $sites[$port] ?? null;
    $page = static fn(int $code, string $text): string => "<html>\r\n<head><title>$code $text</title></head>\r\n<body>\r\n<center><h1>$code $text</h1></center>\r\n<hr><center>nginx/1.22.1</center>\r\n</body>\r\n</html>\r\n";
    if ($site === null) return [404, 'text/html', $page(404, 'Not Found')];
    $www = new Lab57World();
    $www->fs = $w->fs;
    $www->users = $w->users;
    $www->groups = $w->groups;
    $www->user = 'www-data';
    $www->cwd = '/';
    $file = Lab57Vfs::normalize(rawurldecode((string)parse_url($path, PHP_URL_PATH)), '/');
    $abs = Lab57Vfs::normalize(ltrim($file, '/'), $site['root']);
    if (!str_starts_with($abs . '/', rtrim($site['root'], '/') . '/')) return [403, 'text/html', $page(403, 'Forbidden')];
    $node = $w->fs->get($abs);
    if ($node !== null && ($node['t'] ?? '') === 'd') {
        $found = null;
        foreach ((array)$site['index'] as $idx) {
            $candidate = ($abs === '/' ? '' : $abs) . '/' . $idx;
            if ($w->fs->isFile($candidate)) { $found = $candidate; break; }
        }
        if ($found === null) return [403, 'text/html', $page(403, 'Forbidden')];
        $abs = $found;
        $node = $w->fs->get($abs);
    }
    if ($node === null) return [404, 'text/html', $page(404, 'Not Found')];
    if (!$www->canTraverse($abs) || !$www->can($node, 'r')) return [403, 'text/html', $page(403, 'Forbidden')];
    $type = match (strtolower(pathinfo($abs, PATHINFO_EXTENSION))) { 'css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png', 'txt' => 'text/plain', default => 'text/html' };
    return [200, $type, (string)($node['c'] ?? '')];
}

function lab57_cmd_nginx(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $arg = $argv[1] ?? '';
    if ($arg === '-v' || $arg === '-V') { $p->err("nginx version: nginx/1.22.1\n"); return 0; }
    if ($arg === '-t' || $arg === '-T') {
        $errors = lab57_nginx_check($w);
        if ($errors !== []) {
            foreach ($errors as $line) $p->err($line . "\n");
            $w->tip(tr('nginx ukazuje soubor a číslo řádku s chybou. Otevři ho (sudo nano …) a oprav přesně ten řádek.'));
            return 1;
        }
        $p->err("nginx: the configuration file /etc/nginx/nginx.conf syntax is ok\nnginx: configuration file /etc/nginx/nginx.conf test is successful\n");
        if ($arg === '-T') foreach (['/etc/nginx/nginx.conf'] as $path) $p->out("# configuration file $path:\n" . (string)($w->fs->get($path)['c'] ?? ''));
        return 0;
    }
    if ($arg === '-s') {
        if (!$w->root) { $p->err("nginx: [alert] could not open error log file: open() \"/var/log/nginx/error.log\" failed (13: Permission denied)\n"); return 1; }
        return lab57_cmd_systemctl($p, ['systemctl', ($argv[2] ?? 'reload') === 'stop' ? 'stop' : 'reload', 'nginx']);
    }
    $p->err("nginx: [emerg] bind() to 0.0.0.0:80 failed (98: Address already in use)\n");
    $w->tip(tr('Webový server běží jako služba. Ovládej ho přes sudo systemctl start|stop|restart nginx.'));
    return 1;
}

// ---------------------------------------------------------------------------
// Balíčky
// ---------------------------------------------------------------------------

function lab57_apt_catalog(): array
{
    return [
        'cowsay' => ['3.03+dfsg2-8', 'configurable talking cow', '18.6 kB', '93.2 kB', ['cowsay']],
        'net-tools' => ['2.10-0.1', 'NET-3 networking toolkit (ifconfig, netstat)', '243 kB', '1,015 kB', ['ifconfig', 'netstat']],
        'tree' => ['2.1.0-1', 'displays an indented directory tree, in color', '52.4 kB', '124 kB', ['tree']],
        'curl' => ['7.88.1-10+deb12u5', 'command line tool for transferring data with URL syntax', '316 kB', '500 kB', ['curl']],
        'nginx' => ['1.22.1-9', 'small, powerful, scalable web/proxy server', '526 kB', '1,389 kB', ['nginx']],
        'nano' => ['7.2-1', 'small, friendly text editor inspired by Pico', '690 kB', '2,824 kB', ['nano']],
    ] + lab57_apt_catalog_v58();
}

/** Balíčky registrované v v58 (lab58_register_apt_package / meta package příkazů) ve formátu katalogu v57. */
function lab57_apt_catalog_v58(): array
{
    if (!function_exists('lab58_apt_packages')) return [];
    $out = [];
    foreach (lab58_apt_packages() as $name => $info) $out[$name] = [$info['version'], $info['description'], $info['size'], $info['installed_size'], $info['bins'], $info['removable']];
    return $out;
}

function lab57_cmd_apt(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !in_array($a, ['-y', '--yes', '-q'], true)));
    $action = (string)($args[0] ?? '');
    $packages = array_slice($args, 1);
    $catalog = lab57_apt_catalog();
    $needsRoot = in_array($action, ['update', 'install', 'remove', 'purge', 'upgrade', 'autoremove'], true);
    if ($action === '') { $p->line('apt 2.6.1 (amd64)'); $p->line('Usage: apt [options] command'); return 1; }
    if ($needsRoot && !$w->root) {
        $p->line('Reading package lists... Done');
        $p->err("E: Could not open lock file /var/lib/dpkg/lock-frontend - open (13: Permission denied)\nE: Unable to acquire the dpkg frontend lock (/var/lib/dpkg/lock-frontend), are you root?\n");
        $w->tip(tr('Instalaci softwaru dělá správce systému: sudo apt {argumenty}', ['argumenty' => implode(' ', $args)]));
        return 100;
    }
    if (in_array($action, ['update', 'install', 'upgrade'], true) && function_exists('lab57_net_resolve')) {
        $resolved = lab57_net_resolve($w, 'deb.debian.org');
        $reach = $resolved['ip'] !== null ? lab57_net_path($w, (string)$resolved['ip']) : null;
        if ($resolved['ip'] === null || $reach === null || !$reach['ok']) {
            $why = $resolved['ip'] === null ? "Temporary failure resolving 'deb.debian.org'" : 'Could not connect to deb.debian.org:80 (198.51.100.80), connection timed out';
            $p->line('Ign:1 http://deb.debian.org/debian bookworm InRelease');
            $p->line('Err:1 http://deb.debian.org/debian bookworm InRelease');
            $p->line('  ' . $why);
            $p->err("W: Failed to fetch http://deb.debian.org/debian/dists/bookworm/InRelease  $why\nW: Some index files failed to download. They have been ignored, or old ones used instead.\n");
            $w->tip(tr('Balíčky se stahují z internetu. Nejdřív oprav připojení (ping, ip route, /etc/resolv.conf).'));
            return 100;
        }
    }
    switch ($action) {
        case 'update':
            $p->line('Hit:1 http://deb.debian.org/debian bookworm InRelease');
            $p->line('Hit:2 http://deb.debian.org/debian-security bookworm-security InRelease');
            $p->line('Reading package lists... Done');
            $p->line('Building dependency tree... Done');
            $p->line('All packages are up to date.');
            $w->mem['apt_updated'] = true;
            return 0;
        case 'upgrade':
            $p->line('Reading package lists... Done');
            $p->line('Calculating upgrade... Done');
            $p->line('0 upgraded, 0 newly installed, 0 to remove and 0 not upgraded.');
            return 0;
        case 'install':
            if ($packages === []) { $p->line('Reading package lists... Done'); $p->line('0 upgraded, 0 newly installed, 0 to remove and 0 not upgraded.'); return 0; }
            $p->line('Reading package lists... Done');
            $p->line('Building dependency tree... Done');
            $p->line('Reading state information... Done');
            foreach ($packages as $pkg) {
                if (!isset($catalog[$pkg])) { $p->err("E: Unable to locate package $pkg\n"); $w->tip(tr('Balíček s tímto jménem neexistuje. Hledat můžeš: apt search {balicek}', ['balicek' => $pkg])); return 100; }
            }
            $new = array_values(array_filter($packages, static fn(string $pkg): bool => !isset($w->packages[$pkg])));
            foreach (array_diff($packages, $new) as $pkg) $p->line($pkg . ' is already the newest version (' . $catalog[$pkg][0] . ').');
            if ($new !== []) {
                $p->line('The following NEW packages will be installed:');
                $p->line('  ' . implode(' ', $new));
            }
            $p->line('0 upgraded, ' . count($new) . ' newly installed, 0 to remove and 0 not upgraded.');
            foreach ($new as $k => $pkg) {
                [$version, , $size, $installed, $bins] = $catalog[$pkg];
                $p->line('Get:' . ($k + 1) . ' http://deb.debian.org/debian bookworm/main amd64 ' . $pkg . ' amd64 ' . $version . ' [' . $size . ']');
                $p->line('Selecting previously unselected package ' . $pkg . '.');
                $p->line('Unpacking ' . $pkg . ' (' . $version . ') ...');
                $p->line('Setting up ' . $pkg . ' (' . $version . ') ...');
                $w->packages[$pkg] = $version;
                foreach ($bins as $bin) {
                    $w->fs->set(function_exists('lab57_command_bin') ? lab57_command_bin((string)$bin) : '/usr/bin/' . $bin, ['t' => 'f', 'm' => 0755, 'u' => 'root', 'g' => 'root', 'mt' => $w->now, 'c' => "\x7fELF\x02\x01\x01\x00" . $bin, 'x' => 'bin:' . $bin]);
                }
                $dpkg = $w->fs->get('/var/log/dpkg.log');
                if ($dpkg !== null) { $dpkg['c'] = (string)$dpkg['c'] . date('Y-m-d H:i:s', $w->now) . ' status installed ' . $pkg . ':amd64 ' . $version . "\n"; $w->fs->set('/var/log/dpkg.log', $dpkg); }
            }
            return 0;
        case 'remove':
        case 'purge':
            foreach ($packages as $pkg) {
                if (!isset($w->packages[$pkg])) { $p->line("Package '$pkg' is not installed, so not removed"); continue; }
                if (!in_array($pkg, ['cowsay', 'net-tools', 'tree'], true) && empty($catalog[$pkg][5])) { $p->err("E: simulace nedovolí odebrat základní balíček $pkg\n"); return 100; }
                unset($w->packages[$pkg]);
                foreach ($catalog[$pkg][4] ?? [] as $bin) $w->fs->delete(function_exists('lab57_command_bin') ? lab57_command_bin((string)$bin) : '/usr/bin/' . $bin);
                $p->line('Removing ' . $pkg . ' (' . ($catalog[$pkg][0] ?? '') . ') ...');
            }
            return 0;
        case 'list':
            $p->line('Listing... Done');
            $installedOnly = in_array('--installed', $packages, true);
            $names = $installedOnly ? array_keys($w->packages) : array_unique(array_merge(array_keys($w->packages), array_keys($catalog)));
            sort($names);
            foreach ($names as $name) {
                $version = $w->packages[$name] ?? ($catalog[$name][0] ?? '');
                $p->line($name . '/stable,now ' . $version . ' amd64' . (isset($w->packages[$name]) ? ' [installed]' : ''));
            }
            return 0;
        case 'search':
            $term = mb_strtolower((string)($packages[0] ?? ''));
            $p->line('Sorting... Done');
            $p->line('Full Text Search... Done');
            foreach ($catalog as $name => $info) {
                if ($term !== '' && !str_contains(mb_strtolower($name . ' ' . $info[1]), $term)) continue;
                $p->line($name . '/stable ' . $info[0] . ' amd64' . (isset($w->packages[$name]) ? ' [installed]' : ''));
                $p->line('  ' . $info[1]);
                $p->line('');
            }
            return 0;
        case 'show':
            $pkg = (string)($packages[0] ?? '');
            if (!isset($catalog[$pkg])) { $p->err("E: No packages found\n"); return 100; }
            $p->line('Package: ' . $pkg);
            $p->line('Version: ' . $catalog[$pkg][0]);
            $p->line('Description: ' . $catalog[$pkg][1]);
            return 0;
    }
    $p->err("E: Invalid operation $action\n");
    return 100;
}

// ---------------------------------------------------------------------------
// Disky
// ---------------------------------------------------------------------------

/** @return array{size:int,used:int} obsazení kořenového oddílu v bajtech */
function lab57_disk_usage(Lab57World $w): array
{
    $size = (int)($w->mem['disk_size'] ?? 21474836480);
    $used = (int)($w->mem['disk_base'] ?? 6442450944);
    foreach ($w->fs->all() as $path => $node) {
        if (($node['t'] ?? '') !== 'f') continue;
        if (preg_match('#^/(proc|sys|dev|run)(/|$)#', (string)$path) === 1) continue;
        $used += lab57_blocks($node) * 1024;
    }
    return ['size' => $size, 'used' => min($used, $size)];
}

function lab57_df_human(int $bytes): string
{
    if ($bytes === 0) return '0';
    $units = ['K', 'M', 'G', 'T'];
    $value = $bytes / 1024;
    $unit = 'K';
    foreach ($units as $u) {
        $unit = $u;
        if ($value < 1024) break;
        $value /= 1024;
    }
    return ($value < 10 ? number_format(ceil($value * 10) / 10, 1, '.', '') : (string)(int)ceil($value)) . $unit;
}

function lab57_cmd_df(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $human = in_array('-h', $argv, true) || in_array('-H', $argv, true);
    $disk = lab57_disk_usage($w);
    $rows = [
        ['udev', 1987440 * 1024, 0, '/dev'],
        ['tmpfs', 402852 * 1024, 1060 * 1024, '/run'],
        ['/dev/sda1', $disk['size'], $disk['used'], '/'],
        ['tmpfs', 2014260 * 1024, 0, '/dev/shm'],
        ['tmpfs', 402852 * 1024, 48 * 1024, '/run/user/1000'],
    ];
    $p->line($human ? 'Filesystem      Size  Used Avail Use% Mounted on' : 'Filesystem     1K-blocks     Used Available Use% Mounted on');
    foreach ($rows as [$fs, $size, $used, $mount]) {
        $avail = max(0, $size - $used);
        $pct = $size > 0 ? (int)ceil($used / $size * 100) : 0;
        if ($human) {
            $p->line(str_pad($fs, 14) . ' ' . str_pad(lab57_df_human($size), 5, ' ', STR_PAD_LEFT) . ' ' . str_pad(lab57_df_human($used), 5, ' ', STR_PAD_LEFT) . ' ' . str_pad(lab57_df_human($avail), 5, ' ', STR_PAD_LEFT) . ' ' . str_pad($pct . '%', 4, ' ', STR_PAD_LEFT) . ' ' . $mount);
        } else {
            $p->line(str_pad($fs, 14) . ' ' . str_pad((string)intdiv($size, 1024), 9, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)intdiv($used, 1024), 8, ' ', STR_PAD_LEFT) . ' ' . str_pad((string)intdiv($avail, 1024), 9, ' ', STR_PAD_LEFT) . ' ' . str_pad($pct . '%', 4, ' ', STR_PAD_LEFT) . ' ' . $mount);
        }
    }
    return 0;
}

function lab57_cmd_du(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $paths, $error] = lab57_getopt(array_slice($argv, 1), 'shacd:', ['max-depth' => true, 'summarize' => false, 'human-readable' => false, 'all' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($paths === []) $paths = ['.'];
    $opt = [
        'summary' => isset($o['s']) || isset($o['--summarize']),
        'human' => isset($o['h']) || isset($o['--human-readable']),
        'all' => isset($o['a']) || isset($o['--all']),
        'max' => isset($o['d']) || isset($o['--max-depth']) ? (int)($o['d'] ?? $o['--max-depth']) : PHP_INT_MAX,
    ];
    $grand = 0;
    $status = 0;
    foreach ($paths as $path) {
        $abs = $w->abs($path);
        if (!$w->canTraverse($abs) || !$w->fs->exists($abs)) { $p->err("du: cannot access '$path': No such file or directory\n"); $status = 1; continue; }
        $grand += lab57_du_walk($p, $abs, rtrim($path, '/') === '' ? '/' : rtrim($path, '/'), 0, $opt, $status);
    }
    if (isset($o['c'])) $p->line(($opt['human'] ? lab57_df_human($grand * 1024) : (string)$grand) . "\ttotal");
    return $status;
}

function lab57_du_walk(Lab57Proc $p, string $abs, string $disp, int $depth, array $opt, int &$status): int
{
    $w = $p->w;
    $node = $w->fs->get($abs);
    if ($node === null) return 0;
    $fmt = static fn(int $kb): string => $opt['human'] ? lab57_df_human($kb * 1024) : (string)$kb;
    if (($node['t'] ?? '') !== 'd') {
        $kb = lab57_blocks($node);
        if ($depth === 0 || ($opt['all'] && !$opt['summary'] && $depth <= $opt['max'])) $p->line($fmt($kb) . "\t" . $disp);
        return $kb;
    }
    $total = 4;
    if (!$w->can($node, 'r') || !$w->can($node, 'x')) {
        $p->err("du: cannot read directory '$disp': Permission denied\n");
        $status = 1;
    } else {
        foreach ($w->fs->children($abs) as $name) {
            $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
            $total += lab57_du_walk($p, $childAbs, ($disp === '/' ? '' : $disp) . '/' . $name, $depth + 1, $opt, $status);
        }
    }
    if ((!$opt['summary'] && $depth <= $opt['max']) || $depth === 0) $p->line($fmt($total) . "\t" . $disp);
    return $total;
}
