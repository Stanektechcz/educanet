<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · pomocné funkce týdenní kontroly (tools/v61_weekly_health.php): spuštění nástrojů jako
 * podprocesů (bez shellu), převod jejich výstupu na PASS/WARN/FAIL + počty a očištění textů.
 * Do výsledku nejde nic než stavy, počty a krátké názvy kontrol (osobní údaje ani tajné hodnoty nikdy).
 */

const H61_TIMEOUT_SECONDS = 600;
const H61_MAX_ISSUES = 6;
const H61_ISSUE_LEN = 140;

/** Spustí `php <skript> <argumenty>` bez shellu s časovým limitem. @return array{exit:int,output:string,timed_out:bool} */
function h61_run_tool(array $phpArgs, int $timeout = H61_TIMEOUT_SECONDS): array
{
    $cmd = array_merge([PHP_BINARY], $phpArgs);
    $proc = @proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($proc)) return ['exit' => 127, 'output' => '', 'timed_out' => false];
    foreach ($pipes as $p) stream_set_blocking($p, false);
    $out = '';
    $timedOut = false;
    $start = time();
    while (true) {
        foreach ($pipes as $p) $out .= (string)stream_get_contents($p);
        $status = proc_get_status($proc);
        if (!$status['running']) break;
        if (time() - $start > $timeout) { $timedOut = true; proc_terminate($proc); break; }
        usleep(100000);
    }
    foreach ($pipes as $p) { $out .= (string)stream_get_contents($p); fclose($p); }
    $exit = proc_close($proc);
    if (!$timedOut && isset($status['exitcode']) && $status['exitcode'] >= 0) $exit = (int)$status['exitcode'];
    return ['exit' => $exit, 'output' => $out, 'timed_out' => $timedOut];
}

/** Zkrátí a očistí text kontroly: dlouhé „tokeny“ a cesty ke klíčům se nahradí, délka je omezená. */
function h61_scrub(string $line): string
{
    $line = preg_replace('~[A-Za-z0-9+/_=-]{24,}~', '…', $line) ?? '';
    $line = preg_replace('~(?i)(key|secret|token|password|heslo|klíč)\s*[=:]\s*\S+~u', '$1=…', $line) ?? '';
    $line = trim(preg_replace('~\s+~u', ' ', $line) ?? '');
    return mb_strlen($line) > H61_ISSUE_LEN ? mb_substr($line, 0, H61_ISSUE_LEN - 1) . '…' : $line;
}

/**
 * Převede výstup nástroje na výsledek kontroly.
 * Závěrečný řádek má tvar PREFIX_(OK|WARN|FAIL) k=v … (STORAGE_SELFTEST, PREFLIGHT, PERF_REPORT).
 *
 * @return array{status:string,summary:string,counts:array<string,int>,issues:list<string>}
 */
function h61_parse_result(string $prefix, string $output, int $exit, bool $timedOut = false): array
{
    $res = ['status' => 'FAIL', 'summary' => '', 'counts' => [], 'issues' => []];
    if ($timedOut) { $res['summary'] = 'překročen čas'; return $res; }
    $final = null;
    $issues = [];
    foreach (preg_split('~\R~', $output) ?: [] as $line) {
        if (preg_match('~^' . preg_quote($prefix, '~') . '_(OK|WARN|FAIL)\b(.*)$~', $line, $m) === 1) $final = [$m[1], $m[2]];
        elseif (preg_match('~^(FAIL|WARN)\s+(.+)$~', $line, $m) === 1 && count($issues) < H61_MAX_ISSUES * 3) $issues[] = [$m[1], h61_scrub($m[2])];
    }
    if ($final === null) {
        $res['summary'] = 'bez závěrečného řádku (exit ' . $exit . ')';
        $res['issues'] = array_map(static fn(array $i): string => $i[0] . ' ' . $i[1], array_slice($issues, 0, H61_MAX_ISSUES));
        return $res;
    }
    preg_match_all('~(\w+)=(\d+)~', $final[1], $kv, PREG_SET_ORDER);
    foreach ($kv as $pair) $res['counts'][$pair[1]] = (int)$pair[2];
    $warn = ($res['counts']['warn'] ?? $res['counts']['warned'] ?? $res['counts']['pages_over'] ?? 0);
    $res['status'] = $final[0] === 'FAIL' ? 'FAIL' : (($final[0] === 'WARN' || $warn > 0) ? 'WARN' : 'PASS');
    if ($exit === 1 && $res['status'] !== 'FAIL') $res['status'] = 'FAIL';
    $res['summary'] = h61_summary($res['counts']);
    // Nejdřív FAIL, pak WARN.
    usort($issues, static fn(array $a, array $b): int => ($a[0] === 'FAIL' ? 0 : 1) <=> ($b[0] === 'FAIL' ? 0 : 1));
    $res['issues'] = array_map(static fn(array $i): string => $i[0] . ' ' . $i[1], array_slice($issues, 0, H61_MAX_ISSUES));
    return $res;
}

/** Krátký souhrn počtů, např. „checks=50 failed=0 warn=2“. */
function h61_summary(array $counts): string
{
    $parts = [];
    foreach ($counts as $k => $v) $parts[] = $k . '=' . $v;
    return implode(' ', $parts);
}

/** Celkový stav: FAIL > WARN > PASS; SKIP se nepočítá. */
function h61_overall(array $checks): string
{
    $overall = 'PASS';
    foreach ($checks as $c) {
        $s = (string)($c['status'] ?? 'FAIL');
        if ($s === 'FAIL') return 'FAIL';
        if ($s === 'WARN') $overall = 'WARN';
    }
    return $overall;
}

/** Připojí jednořádkový záznam do logu (rotace nad 512 KB). Chyba zápisu logu se jen oznámí na STDERR. */
function h61_append_log(string $path, string $line): bool
{
    if (is_file($path) && (int)filesize($path) > 524288) @rename($path, $path . '.1');
    return @file_put_contents($path, $line . "\n", FILE_APPEND | LOCK_EX) !== false;
}
