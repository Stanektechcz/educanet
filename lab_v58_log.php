<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – log příkazů a klasifikace chyb (substrát pro EDU-02/03, TCH-02/03).
 *
 * Úložiště: storage/linux_v58/log/<třída>__<sha1(žák)>/<úroveň>.jsonl.php
 *   1. řádek = ochrana <?php http_response_code(403); exit; ?>, pak jeden JSON záznam na řádek.
 *   meta.json.php ve stejné složce drží klíč žáka (pro učitelské přehledy v rámci třídy).
 * Zápis je jen připojení řádku pod zámkem (rychlé); když soubor přeroste LAB58_LOG_COMPACT_BYTES,
 * nebo u každého LAB58_LOG_RETENTION_EVERY. příkazu instance úrovně, se soubor pod zámkem zhutní na
 * posledních LAB58_LOG_MAX záznamů mladších 30 dní (a nejvýš na polovinu limitu velikosti).
 * Čtení vrací vždy nejvýš LAB58_LOG_MAX záznamů za posledních 30 dní.
 * Záznam (zkrácené klíče na disku): t=ts, c=ctx, k=druh (cmd|open|complete|reset), l=řádek (≤200),
 * x=exit, e=error_class, o=výstup (≤300), h=číslo použité nápovědy, p=body (complete), r=role.
 */

const LAB58_LOG_MAX = 150;
const LAB58_LOG_RETENTION = 2592000; // 30 dní
const LAB58_LOG_COMPACT_BYTES = 163840; // zhutnění nechá nejvýš polovinu → zhutňuje se jen občas
const LAB58_LOG_RETENTION_EVERY = 50;   // každý 50. příkaz instance úrovně uplatní retenci při zápisu
const LAB58_LOG_GUARD = "<?php http_response_code(403); exit; ?>\n";
const LAB58_ERROR_CLASSES = ['ok', 'not_found', 'no_such_file', 'permission', 'bad_option', 'syntax', 'usage', 'wrong_answer', 'other'];

function lab58_log_root(): string
{
    return lab57_storage_dir() . '/linux_v58/log';
}

function lab58_log_dir(string $classId, string $studentKey): string
{
    return lab58_log_root() . '/' . preg_replace('/[^a-z0-9_]/i', '', $classId) . '__' . sha1($studentKey);
}

function lab58_log_file(string $classId, string $studentKey, string $levelId): string
{
    return lab58_log_dir($classId, $studentKey) . '/' . preg_replace('/[^a-z0-9-]/', '', $levelId) . '.jsonl.php';
}

/** Třída chyby podle stderr, návratového kódu a příkazu. */
function lab58_classify_error(string $stderr, int $exit, string $cmd): string
{
    if ($exit === 0) return 'ok';
    $first = strtolower((string)strtok(ltrim($cmd), " \t;|&"));
    // exit 2 = chybějící/špatný argument příkazu (usage), viz lab57_cmd_submit()/lab57_cmd_answer();
    // exit 1 u submit/answer/check = špatná odpověď. Strukturovaný kód místo srovnávání s textem hlášky.
    if (in_array($first, ['submit', 'answer', 'check'], true) && $exit === 1) return 'wrong_answer';
    $e = strtolower($stderr);
    if ($exit === 127 && str_contains($e, 'command not found')) return 'not_found';
    if (str_contains($e, 'no such file or directory') || str_contains($e, 'cannot access')) return 'no_such_file';
    if (str_contains($e, 'permission denied') || str_contains($e, 'operation not permitted') || str_contains($e, 'are you root')) return 'permission';
    if (preg_match('/(invalid|unrecognized|unknown|illegal) (option|argument)|invalid (number|mode)/', $e) === 1) return 'bad_option';
    if (str_contains($e, 'syntax error') || str_contains($e, 'unexpected eof') || str_contains($e, 'bad substitution')) return 'syntax';
    if (str_contains($e, 'usage:') || str_contains($e, 'missing') || str_contains($e, 'requires an argument') || str_contains($e, 'what manual page')) return 'usage';
    if ($exit === 127) return 'not_found';
    return 'other';
}

/** Jméno příkazu z řádku (bez sudo a přiřazení proměnných) pro agregace; nic jiného z řádku se nepoužije. */
function lab58_log_command_name(string $line): string
{
    foreach (preg_split('/\s+/', trim($line)) ?: [] as $word) {
        if ($word === '' || $word === 'sudo' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*=/', $word) === 1) continue;
        return preg_match('/^[A-Za-z0-9._+\[-]{1,32}$/', $word) === 1 ? $word : '?';
    }
    return '?';
}

/**
 * Připojí záznam do logu úrovně žáka. Běžný zápis = jedno připojení řádku pod zámkem (LOCK_EX).
 * Zhutnění (≤ LAB58_LOG_MAX záznamů, ≤ polovina limitu velikosti, jen mladší 30 dní) proběhne,
 * když soubor přeroste LAB58_LOG_COMPACT_BYTES, nebo když $retention = true (jádro posílá každý
 * LAB58_LOG_RETENTION_EVERY. příkaz instance úrovně) – retence 30 dní se tak uplatní při zápisu.
 */
function lab58_log_append(string $classId, string $studentKey, string $levelId, array $entry, bool $retention = false): void
{
    if (preg_match('/^[a-z0-9-]{2,32}$/', $levelId) !== 1 || $studentKey === '') return;
    $file = lab58_log_file($classId, $studentKey, $levelId);
    $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
    clearstatcache(true, $file);
    if (!is_file($file)) {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0770, true) && !is_dir($dir)) throw new RuntimeException(tr('Nelze vytvořit složku logu laboratoře.'));
            lab57_store_update($dir . '/meta.json.php', static fn(array $d): array => ['class' => $classId, 'student' => $studentKey] + $d);
        }
        // Vytvoří soubor výhradně (x): ochranný řádek je vždy první. Kdo závod o vytvoření prohraje, jen připojí.
        $fp = @fopen($file, 'x');
        if ($fp !== false) {
            fwrite($fp, LAB58_LOG_GUARD . $line);
            fclose($fp);
            return;
        }
    } elseif ($retention || (int)filesize($file) > LAB58_LOG_COMPACT_BYTES) {
        lab58_log_compact($file, (int)($entry['t'] ?? time()));
    }
    if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) throw new RuntimeException(tr('Nelze zapsat log laboratoře.'));
}

/** Zhutní soubor logu pod zámkem: jen záznamy mladší 30 dní, nejvýš LAB58_LOG_MAX a nejvýš polovina limitu velikosti. */
function lab58_log_compact(string $file, int $now): void
{
    $fp = fopen($file, 'c+');
    if ($fp === false) return;
    try {
        if (!flock($fp, LOCK_EX)) return;
        $lines = array_map(static fn(array $r): string => json_encode($r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . "\n", lab58_log_parse((string)stream_get_contents($fp), $now));
        $bytes = array_sum(array_map('strlen', $lines));
        while ($lines !== [] && $bytes > intdiv(LAB58_LOG_COMPACT_BYTES, 2)) $bytes -= strlen((string)array_shift($lines));
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, LAB58_LOG_GUARD . implode('', $lines));
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }
}

/** @return list<array> surové záznamy (zkrácené klíče), nejvýš LAB58_LOG_MAX, mladší 30 dní */
function lab58_log_parse(string $raw, int $now): array
{
    $rows = [];
    foreach (explode("\n", $raw) as $line) {
        if ($line === '' || $line[0] !== '{') continue;
        $row = json_decode($line, true);
        if (is_array($row) && (int)($row['t'] ?? 0) >= $now - LAB58_LOG_RETENTION) $rows[] = $row;
    }
    return array_slice($rows, -LAB58_LOG_MAX);
}

function lab58_log_expand(array $r, string $levelId): array
{
    return ['ts' => (int)($r['t'] ?? 0), 'ctx' => (string)($r['c'] ?? 'practice'), 'level' => $levelId, 'kind' => (string)($r['k'] ?? 'cmd'), 'line' => (string)($r['l'] ?? ''),
        'exit' => (int)($r['x'] ?? 0), 'error_class' => (string)($r['e'] ?? 'ok'), 'out' => (string)($r['o'] ?? ''), 'hint' => (int)($r['h'] ?? 0), 'points' => (int)($r['p'] ?? 0), 'role' => $r['r'] ?? null];
}

/** @return list<array> záznamy žáka (jedna úroveň, nebo všechny seřazené podle času) */
function lab58_log_read(string $classId, string $studentKey, ?string $levelId = null, ?int $now = null): array
{
    $now ??= time();
    $files = $levelId !== null ? [lab58_log_file($classId, $studentKey, $levelId)] : (glob(lab58_log_dir($classId, $studentKey) . '/*.jsonl.php') ?: []);
    $out = [];
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        $level = basename($file, '.jsonl.php');
        foreach (lab58_log_parse((string)file_get_contents($file), $now) as $row) $out[] = lab58_log_expand($row, $level);
    }
    usort($out, static fn(array $a, array $b): int => $a['ts'] <=> $b['ts']);
    return $out;
}

/** Pokusy, chyby podle tříd, neúspěšná odevzdání, nápovědy, čas od otevření, poslední aktivita. */
function lab58_level_stats(string $classId, string $studentKey, string $levelId, ?int $now = null): array
{
    $now ??= time();
    $stats = ['level' => $levelId, 'attempts' => 0, 'errors' => [], 'failed_submits' => 0, 'hints' => 0, 'opened_at' => null, 'last_ts' => null, 'completed_at' => null, 'points' => null, 'secs_open' => 0];
    foreach (lab58_log_read($classId, $studentKey, $levelId, $now) as $e) {
        $stats['opened_at'] ??= $e['ts'];
        $stats['last_ts'] = $e['ts'];
        if ($e['kind'] === 'complete') { $stats['completed_at'] ??= $e['ts']; $stats['points'] ??= $e['points']; continue; }
        if ($e['kind'] !== 'cmd') continue;
        $stats['attempts']++;
        if ($e['hint'] > 0) $stats['hints'] = max($stats['hints'], $e['hint']);
        if ($e['error_class'] === 'wrong_answer') $stats['failed_submits']++;
        if ($e['error_class'] !== 'ok') $stats['errors'][$e['error_class']] = ($stats['errors'][$e['error_class']] ?? 0) + 1;
    }
    if ($stats['opened_at'] !== null) $stats['secs_open'] = max(0, (int)($stats['completed_at'] ?? $now) - (int)$stats['opened_at']);
    return $stats;
}

/** @return array<string,string> složka logu → klíč žáka (jen žáci dané třídy) */
function lab58_log_class_dirs(string $classId): array
{
    $out = [];
    foreach (glob(lab58_log_root() . '/' . preg_replace('/[^a-z0-9_]/i', '', $classId) . '__*', GLOB_ONLYDIR) ?: [] as $dir) {
        $meta = lab57_store_read($dir . '/meta.json.php');
        if (($meta['class'] ?? '') === $classId && is_string($meta['student'] ?? null)) $out[$dir] = $meta['student'];
    }
    return $out;
}

/** Heatmapa třídy: příkaz × třída chyby × počet žáků (bez jmen). */
function lab58_class_log_summary(string $classId, int $sinceTs, ?int $now = null): array
{
    $now ??= time();
    $commands = [];
    $errors = [];
    $active = 0;
    $total = 0;
    foreach (lab58_log_class_dirs($classId) as $dir => $student) {
        $seen = false;
        foreach (glob($dir . '/*.jsonl.php') ?: [] as $file) {
            if (filemtime($file) < $sinceTs) continue;
            foreach (lab58_log_parse((string)file_get_contents($file), $now) as $r) {
                if ((int)($r['t'] ?? 0) < $sinceTs || ($r['k'] ?? 'cmd') !== 'cmd') continue;
                $seen = true;
                $total++;
                $cmd = lab58_log_command_name((string)($r['l'] ?? ''));
                $class = (string)($r['e'] ?? 'ok');
                $commands[$cmd]['total'] = ($commands[$cmd]['total'] ?? 0) + 1;
                $commands[$cmd]['who'][$student] = true;
                $commands[$cmd]['errors'][$class]['count'] = ($commands[$cmd]['errors'][$class]['count'] ?? 0) + 1;
                $commands[$cmd]['errors'][$class]['who'][$student] = true;
                $errors[$class]['count'] = ($errors[$class]['count'] ?? 0) + 1;
                $errors[$class]['who'][$student] = true;
            }
        }
        if ($seen) $active++;
    }
    $count = static function (array $cell): array {
        $cell['students'] = count($cell['who'] ?? []);
        unset($cell['who']);
        return $cell;
    };
    foreach ($commands as $cmd => $cell) {
        $cell = $count($cell);
        foreach ($cell['errors'] as $class => $err) $cell['errors'][$class] = $count($err);
        $commands[$cmd] = $cell;
    }
    uasort($commands, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);
    return ['class' => $classId, 'since' => $sinceTs, 'students' => $active, 'total' => $total, 'commands' => $commands, 'errors' => array_map($count, $errors)];
}

/** Živý dohled: žák → aktuální úroveň, poslední aktivita, chyby za posledních $recentMin minut. */
function lab58_class_activity(string $classId, int $sinceTs, int $recentMin = 5, ?int $now = null): array
{
    $now ??= time();
    $out = [];
    foreach (lab58_log_class_dirs($classId) as $dir => $student) {
        $latest = null;
        $latestMt = 0;
        foreach (glob($dir . '/*.jsonl.php') ?: [] as $file) {
            $mt = (int)filemtime($file);
            if ($mt >= $latestMt) { $latestMt = $mt; $latest = $file; }
        }
        if ($latest === null || $latestMt < $sinceTs) continue;
        $level = basename($latest, '.jsonl.php');
        $rows = lab58_log_parse((string)file_get_contents($latest), $now);
        if ($rows === []) continue;
        $last = end($rows);
        $recent = array_filter($rows, static fn(array $r): bool => (int)($r['t'] ?? 0) >= $now - $recentMin * 60 && ($r['k'] ?? 'cmd') === 'cmd');
        $out[$student] = [
            'student' => $student, 'level' => $level, 'ctx' => (string)($last['c'] ?? 'practice'), 'last_ts' => (int)($last['t'] ?? 0), 'idle_secs' => max(0, $now - (int)($last['t'] ?? $now)),
            'cmds_recent' => count($recent), 'errors_recent' => count(array_filter($recent, static fn(array $r): bool => ($r['e'] ?? 'ok') !== 'ok')),
            'solved' => array_filter($rows, static fn(array $r): bool => ($r['k'] ?? '') === 'complete') !== [],
        ];
    }
    return $out;
}

/** Smaže logy, které se 30 dní nezměnily (OPS-03). @return int počet smazaných souborů */
function lab58_log_purge(?int $now = null): int
{
    $now ??= time();
    $removed = 0;
    foreach (glob(lab58_log_root() . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
        $left = 0;
        foreach (glob($dir . '/*.jsonl.php') ?: [] as $file) {
            if ((int)filemtime($file) < $now - LAB58_LOG_RETENTION && @unlink($file)) $removed++;
            else $left++;
        }
        if ($left === 0) { @unlink($dir . '/meta.json.php'); @rmdir($dir); }
    }
    return $removed;
}

/** Zapíše záznamy odložené přes $GLOBALS['lab58_log_defer'] (API je zapisuje až po odeslání odpovědi). @return int počet zapsaných */
function lab58_log_flush(): int
{
    $queue = (array)($GLOBALS['lab58_log_queue'] ?? []);
    $GLOBALS['lab58_log_queue'] = [];
    $written = 0;
    foreach ($queue as $args) {
        try {
            lab58_log_append(...$args);
            $written++;
        } catch (Throwable $e) {
            error_log('EDUCANET v58 lab log: ' . $e->getMessage());
        }
    }
    return $written;
}

/** Shutdown funkce API: nejdřív doručí odpověď klientovi, pak zapíše odložený log. */
function lab58_log_flush_after_response(): void
{
    if (($GLOBALS['lab58_log_queue'] ?? []) === []) return;
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } elseif (function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
    } else {
        while (ob_get_level() > 0) @ob_end_flush();
        flush();
    }
    lab58_log_flush();
}

/** Posluchači jádra: události command/open/complete/reset → log (v API odloženě po odpovědi). Volá linux_v58_ext.php. */
function lab58_log_register_listeners(): void
{
    $write = static function (array $ev, array $entry): void {
        if (!empty($GLOBALS['lab58_log_disabled'])) return;
        $entry = ['t' => (int)$ev['ts'], 'c' => (string)$ev['ctx']] + $entry;
        if (($ev['role'] ?? null) !== null) $entry['r'] = (string)$ev['role'];
        $seq = (int)($ev['seq'] ?? 0);
        $args = [(string)$ev['class'], (string)$ev['student'], (string)$ev['level'], $entry, $seq > 0 && $seq % LAB58_LOG_RETENTION_EVERY === 0];
        if (!empty($GLOBALS['lab58_log_defer'])) { $GLOBALS['lab58_log_queue'][] = $args; return; }
        lab58_log_append(...$args);
    };
    lab58_on('command', static function (array $ev) use ($write): void {
        $entry = ['k' => 'cmd', 'l' => mb_substr((string)$ev['line'], 0, 200), 'x' => (int)$ev['exit'], 'e' => (string)$ev['error_class']];
        if ((string)($ev['out'] ?? '') !== '') $entry['o'] = mb_substr((string)$ev['out'], 0, 300);
        if (!empty($ev['hint'])) $entry['h'] = (int)$ev['hint'];
        $write($ev, $entry);
    });
    lab58_on('open', static fn(array $ev) => $write($ev, ['k' => 'open']));
    lab58_on('reset', static fn(array $ev) => $write($ev, ['k' => 'reset']));
    lab58_on('complete', static fn(array $ev) => $write($ev, ['k' => 'complete', 'p' => (int)($ev['points'] ?? 0), 'h' => (int)($ev['hints'] ?? 0)]));
}
