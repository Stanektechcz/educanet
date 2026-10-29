<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – plánovač cron (LAB-04).
 *
 * crontab (-l/-e/-r/<soubor>), čtení /etc/crontab, /etc/cron.d/*, /var/spool/cron/crontabs/<user>,
 * validace syntaxe a lab příkaz „timewarp“, který posune simulované hodiny (lab58_clock_advance)
 * a spustí úlohy, které měly během posunu proběhnout. Výstup jde do /var/log/syslog a do pošty
 * uživatele. Vše je simulace – nic se nespouští, žádná síť; čas jen z $w->now / lab58_now().
 */

const LAB58_CRON_MAX_JOBS = 200;
const LAB58_CRON_MAX_WINDOW = 40320; // minut (28 dní)

// ---------------------------------------------------------------------------
// Validace a vyhodnocení plánu
// ---------------------------------------------------------------------------

/** @return list<int>|null povolené hodnoty pole, nebo null při chybě */
function lab58_cron_field(string $field, int $min, int $max, array $names = []): ?array
{
    $field = strtolower(trim($field));
    if ($field === '') return null;
    if ($names !== []) $field = strtr($field, $names);
    $out = [];
    foreach (explode(',', $field) as $part) {
        $step = 1;
        if (str_contains($part, '/')) {
            [$part, $s] = explode('/', $part, 2);
            if (!ctype_digit($s) || (int)$s < 1) return null;
            $step = (int)$s;
        }
        if ($part === '*') { $lo = $min; $hi = $max; }
        elseif (preg_match('/^(\d+)-(\d+)$/', $part, $m) === 1) { $lo = (int)$m[1]; $hi = (int)$m[2]; }
        elseif (ctype_digit($part)) { $lo = $hi = (int)$part; }
        else return null;
        if ($lo < $min || $hi > $max || $lo > $hi) return null;
        for ($i = $lo; $i <= $hi; $i += $step) $out[] = $i;
    }
    return array_values(array_unique($out));
}

const LAB58_CRON_MONTHS = ['jan' => '1', 'feb' => '2', 'mar' => '3', 'apr' => '4', 'may' => '5', 'jun' => '6', 'jul' => '7', 'aug' => '8', 'sep' => '9', 'oct' => '10', 'nov' => '11', 'dec' => '12'];
const LAB58_CRON_DOWS = ['sun' => '0', 'mon' => '1', 'tue' => '2', 'wed' => '3', 'thu' => '4', 'fri' => '5', 'sat' => '6'];

/** Rozbalí zkratku @daily apod. na 5 polí, nebo vrátí null. */
function lab58_cron_shortcut(string $token): ?array
{
    return match (strtolower($token)) {
        '@yearly', '@annually' => ['0', '0', '1', '1', '*'],
        '@monthly' => ['0', '0', '1', '*', '*'],
        '@weekly' => ['0', '0', '*', '*', '0'],
        '@daily', '@midnight' => ['0', '0', '*', '*', '*'],
        '@hourly' => ['0', '*', '*', '*', '*'],
        default => null,
    };
}

/** Ověří 5 polí plánu. @return ?string česká chyba, nebo null když je vše v pořádku */
function lab58_cron_validate_fields(array $fields): ?string
{
    if (lab58_cron_field($fields[0], 0, 59) === null) return 'neplatné pole „minuta" (' . $fields[0] . '); povoleno 0–59, * , - /';
    if (lab58_cron_field($fields[1], 0, 23) === null) return 'neplatné pole „hodina" (' . $fields[1] . '); povoleno 0–23';
    if (lab58_cron_field($fields[2], 1, 31) === null) return 'neplatné pole „den v měsíci" (' . $fields[2] . '); povoleno 1–31';
    if (lab58_cron_field($fields[3], 1, 12, LAB58_CRON_MONTHS) === null) return 'neplatné pole „měsíc" (' . $fields[3] . '); povoleno 1–12 nebo jan–dec';
    if (lab58_cron_field($fields[4], 0, 7, LAB58_CRON_DOWS) === null) return 'neplatné pole „den v týdnu" (' . $fields[4] . '); povoleno 0–7 (0 i 7 = neděle) nebo sun–sat';
    return null;
}

/** Sedí plán na daný čas? */
function lab58_cron_match(array $fields, int $ts): bool
{
    $mins = lab58_cron_field($fields[0], 0, 59) ?? [];
    $hours = lab58_cron_field($fields[1], 0, 23) ?? [];
    $doms = lab58_cron_field($fields[2], 1, 31) ?? [];
    $mons = lab58_cron_field($fields[3], 1, 12, LAB58_CRON_MONTHS) ?? [];
    $dows = array_map(static fn(int $d): int => $d === 7 ? 0 : $d, lab58_cron_field($fields[4], 0, 7, LAB58_CRON_DOWS) ?? []);
    if (!in_array((int)date('i', $ts), $mins, true)) return false;
    if (!in_array((int)date('G', $ts), $hours, true)) return false;
    if (!in_array((int)date('n', $ts), $mons, true)) return false;
    $domMatch = in_array((int)date('j', $ts), $doms, true);
    $dowMatch = in_array((int)date('w', $ts), $dows, true);
    $domRestricted = trim($fields[2]) !== '*';
    $dowRestricted = trim($fields[4]) !== '*';
    if ($domRestricted && $dowRestricted) return $domMatch || $dowMatch;
    return $domMatch && $dowMatch;
}

/** Rozparsuje řádek crontabu. $withUser = systémové taby (/etc/crontab, cron.d) mají pole user. */
function lab58_cron_parse_line(string $line, bool $withUser): array|string|null
{
    $line = rtrim($line);
    $trim = ltrim($line);
    if ($trim === '' || str_starts_with($trim, '#')) return null;
    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*\s*=/', $trim) === 1) return null; // proměnná prostředí
    if (str_starts_with($trim, '@')) {
        $parts = preg_split('/\s+/', $trim, $withUser ? 3 : 2) ?: [];
        $sc = lab58_cron_shortcut($parts[0]);
        if (strtolower($parts[0]) === '@reboot') {
            $rest = $withUser ? array_slice($parts, 1) : array_slice($parts, 1);
            $user = $withUser ? (string)($rest[0] ?? '') : '';
            $cmd = $withUser ? (string)($rest[1] ?? '') : (string)($rest[0] ?? '');
            if ($cmd === '') return 'chybí příkaz za @reboot';
            return ['fields' => null, 'reboot' => true, 'user' => $user, 'command' => $cmd];
        }
        if ($sc === null) return 'neznámá zkratka „' . $parts[0] . '" (použij @daily, @hourly, @weekly, @monthly, @yearly nebo @reboot)';
        $user = $withUser ? (string)($parts[1] ?? '') : '';
        $cmd = $withUser ? (string)($parts[2] ?? '') : (string)($parts[1] ?? '');
        if ($cmd === '') return 'chybí příkaz po ' . $parts[0];
        return ['fields' => $sc, 'reboot' => false, 'user' => $user, 'command' => $cmd];
    }
    $need = $withUser ? 7 : 6;
    $parts = preg_split('/\s+/', $trim, $need) ?: [];
    if (count($parts) < $need) return 'málo polí – plán potřebuje 5 časových polí' . ($withUser ? ', uživatele' : '') . ' a příkaz';
    $fields = array_slice($parts, 0, 5);
    $err = lab58_cron_validate_fields($fields);
    if ($err !== null) return $err;
    $user = $withUser ? (string)$parts[5] : '';
    $cmd = (string)$parts[$withUser ? 6 : 5];
    if (trim($cmd) === '') return 'chybí příkaz';
    return ['fields' => $fields, 'reboot' => false, 'user' => $user, 'command' => $cmd];
}

/** Ověří celý obsah crontabu uživatele. @return ?string chyba „řádek N: …", nebo null */
function lab58_cron_validate(string $content, bool $withUser): ?string
{
    $no = 0;
    foreach (explode("\n", $content) as $line) {
        $no++;
        $parsed = lab58_cron_parse_line($line, $withUser);
        if (is_string($parsed)) return 'řádek ' . $no . ': ' . $parsed;
    }
    return null;
}

// ---------------------------------------------------------------------------
// crontab
// ---------------------------------------------------------------------------

function lab58_cron_spool_path(string $user): string
{
    return '/var/spool/cron/crontabs/' . $user;
}

function lab58_cmd_crontab(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $user = $w->effectiveUser();
    $action = null;
    $file = null;
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-u') { $target = (string)($args[++$i] ?? ''); if (!$w->root) { $p->err("crontab: must be privileged to use -u\n"); return 1; } $user = $target; continue; }
        if (in_array($a, ['-l', '-e', '-r', '-i'], true)) { $action = $a; continue; }
        if (!str_starts_with($a, '-')) { $file = $a; continue; }
        $p->err("crontab: invalid option -- '" . ltrim($a, '-') . "'\ncrontab: usage error: unrecognized option\n"); return 1;
    }
    if (!isset($w->users[$user])) { $p->err("crontab: user '$user' unknown\n"); return 1; }
    $spool = lab58_cron_spool_path($user);
    if ($action === '-l') {
        $node = $w->fs->get($spool);
        if ($node === null) { $p->err("no crontab for $user\n"); return 1; }
        $p->out((string)($node['c'] ?? ''));
        return 0;
    }
    if ($action === '-r') {
        if (!$w->fs->exists($spool)) { $p->err("no crontab for $user\n"); return 1; }
        $w->fs->delete($spool);
        return 0;
    }
    if ($action === '-e') {
        $content = (string)($w->fs->get($spool)['c'] ?? "# m h  dom mon dow   command\n");
        if (!$w->fs->isDir('/var/spool/cron/crontabs')) $w->mkdirp('/var/spool/cron/crontabs', 01730, 'root', 'crontab');
        if (!$w->fs->exists($spool)) $w->mkfile($spool, $content, 0600, $user, 'crontab', $w->now);
        $w->effects['editor'] = ['path' => $w->abs($spool), 'name' => 'crontab', 'content' => $content, 'root' => $w->root, 'new' => false, 'writable' => true];
        $p->line('[ Otevírám crontab v editoru… ]');
        $w->tip(tr('crontab -e normálně otevře plán v $EDITOR a po uložení ho zkontroluje. V laboratoři uprav plán a ulož ho; nebo naplánuj z připraveného souboru: crontab muj-plan.txt.'));
        return 0;
    }
    if ($file !== null) {
        $err = null;
        $content = $w->readFile($file, $err);
        if ($content === null) { $p->err("crontab: $file: $err\n"); lab57_error_tip($w, (string)$err, $file); return 1; }
        $verr = lab58_cron_validate($content, false);
        if ($verr !== null) {
            $p->err("crontab: installing new crontab\n\"$file\":" . preg_replace('/^řádek (\d+): /', '$1: ', $verr) . "\nerrors in crontab file, can't install.\n");
            $w->tip(tr('Chyba v plánu: {chyba}. Formát řádku: „minuta hodina den měsíc den_v_týdnu  příkaz".', ['chyba' => $verr]));
            return 1;
        }
        if (!$w->fs->isDir('/var/spool/cron/crontabs')) $w->mkdirp('/var/spool/cron/crontabs', 01730, 'root', 'crontab');
        $w->mkfile($spool, $content, 0600, $user, 'crontab', $w->now);
        return 0;
    }
    $p->err("usage:  crontab [-u user] file\n        crontab [-u user] [-i] { -e | -l | -r }\n");
    $w->tip(tr('crontab -l vypíše plán, crontab -e ho upraví, crontab soubor.txt ho nastaví z připraveného souboru.'));
    return 1;
}

// ---------------------------------------------------------------------------
// timewarp – posun hodin a spuštění naplánovaných úloh
// ---------------------------------------------------------------------------

/** Všechny naplánované úlohy (spool uživatelů, /etc/crontab, /etc/cron.d/*). */
function lab58_cron_all_jobs(Lab57World $w): array
{
    $jobs = [];
    foreach ($w->fs->children('/var/spool/cron/crontabs') as $name) {
        $node = $w->fs->get('/var/spool/cron/crontabs/' . $name);
        if ($node === null || ($node['t'] ?? '') !== 'f') continue;
        foreach (explode("\n", (string)($node['c'] ?? '')) as $line) {
            $parsed = lab58_cron_parse_line($line, false);
            if (is_array($parsed)) $jobs[] = array_merge($parsed, ['user' => $name, 'source' => 'crontab:' . $name]);
        }
    }
    $files = ['/etc/crontab'];
    if ($w->fs->isDir('/etc/cron.d')) foreach ($w->fs->children('/etc/cron.d') as $name) $files[] = '/etc/cron.d/' . $name;
    foreach ($files as $file) {
        $node = $w->fs->get($file);
        if ($node === null || ($node['t'] ?? '') !== 'f') continue;
        foreach (explode("\n", (string)($node['c'] ?? '')) as $line) {
            $parsed = lab58_cron_parse_line($line, true);
            if (is_array($parsed)) $jobs[] = $parsed + ['source' => $file];
        }
    }
    return $jobs;
}

/** Spustí jednu úlohu v čase $ts jako její uživatel; výstup do syslogu a pošty. */
function lab58_cron_run_job(Lab57World $w, array $job, int $ts): void
{
    $user = (string)($job['user'] ?? 'root');
    if ($user === '' || !isset($w->users[$user])) $user = 'root';
    $saved = ['user' => $w->user, 'root' => $w->root, 'cwd' => $w->cwd, 'now' => $w->now, 'steps' => $w->steps, 'depth' => $w->depth, 'args' => $w->args, 'stray' => $w->stray, 'effects' => $w->effects];
    $w->user = $user;
    $w->root = $user === 'root';
    $w->cwd = $w->home($user);
    $w->now = $ts;
    $w->steps = 0;
    $w->depth = 1;
    $w->args = [];
    $w->stray = [];
    $term = [];
    try {
        lab57_exec_source($w, (string)$job['command'], $term);
    } catch (Throwable) {
        // chyba v úloze cron nesmí shodit posun času
    }
    foreach ($w->stray as $chunk) $term[] = $chunk;
    $out = '';
    foreach ($term as [, $text]) $out .= $text;
    $w->user = $saved['user'];
    $w->root = $saved['root'];
    $w->cwd = $saved['cwd'];
    $w->now = $saved['now'];
    $w->steps = $saved['steps'];
    $w->depth = $saved['depth'];
    $w->args = $saved['args'];
    $w->stray = $saved['stray'];
    $w->effects = $saved['effects'];
    lab58_cron_syslog($w, $user, (string)$job['command'], $ts);
    $w->journalAdd('cron', '(' . $user . ') CMD (' . $job['command'] . ')');
    if (trim($out) !== '') lab58_cron_mail($w, $user, (string)$job['command'], $out, $ts);
}

function lab58_cron_syslog(Lab57World $w, string $user, string $command, int $ts): void
{
    $pid = 30000 + (abs(crc32($command . $ts)) % 9000);
    $line = date('M j H:i:s', $ts) . ' ' . $w->hostname . ' CRON[' . $pid . ']: (' . $user . ') CMD (' . $command . ")\n";
    $node = $w->fs->get('/var/log/syslog');
    if ($node === null) { $w->mkfile('/var/log/syslog', $line, 0640, 'root', 'adm', $ts); return; }
    $node['c'] = (string)($node['c'] ?? '') . $line;
    $node['mt'] = $ts;
    $w->fs->set('/var/log/syslog', $node);
}

function lab58_cron_mail(Lab57World $w, string $user, string $command, string $output, int $ts): void
{
    $path = '/var/mail/' . $user;
    $msg = 'From root@' . $w->hostname . '  ' . date('D M j H:i:s Y', $ts) . "\n"
        . 'From: root@' . $w->hostname . " (Cron Daemon)\n"
        . 'To: ' . $user . '@' . $w->hostname . "\n"
        . 'Subject: Cron <' . $user . '@' . $w->hostname . '> ' . $command . "\n\n"
        . $output . (str_ends_with($output, "\n") ? '' : "\n") . "\n";
    $node = $w->fs->get($path);
    if ($node === null) { $w->mkfile($path, $msg, 0660, $user, 'mail', $ts); return; }
    $node['c'] = (string)($node['c'] ?? '') . $msg;
    $node['mt'] = $ts;
    $w->fs->set($path, $node);
}

/** @return int|null delta v sekundách, nebo null při chybě */
function lab58_timewarp_delta(string $spec, int $now): ?int
{
    if (preg_match('/^\+?(\d+)([smhd])$/', $spec, $m) === 1) {
        $unit = ['s' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400][$m[2]];
        return (int)$m[1] * $unit;
    }
    if (preg_match('/^(\d{1,2}):(\d{2})$/', $spec, $m) === 1) {
        $h = (int)$m[1];
        $min = (int)$m[2];
        if ($h > 23 || $min > 59) return null;
        $target = mktime($h, $min, 0, (int)date('n', $now), (int)date('j', $now), (int)date('Y', $now));
        if ($target === false) return null;
        if ($target <= $now) $target += 86400;
        return $target - $now;
    }
    return null;
}

function lab58_cmd_timewarp(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    if (!function_exists('lab58_clock_advance')) { $p->err("timewarp: simulovaný čas není v tomto režimu dostupný\n"); return 1; }
    if (!empty($GLOBALS['lab58_cron_running'])) { $p->err("timewarp: nelze volat uvnitř úlohy cron\n"); return 1; }
    $spec = (string)($argv[1] ?? '');
    if ($spec === '') {
        $p->err("timewarp: chybí čas\n");
        $w->tip(tr('Použij timewarp +30m, +2h, +1d nebo přesný čas timewarp 06:25 – posune simulované hodiny a spustí naplánované úlohy cronu.'));
        return 1;
    }
    $delta = lab58_timewarp_delta($spec, $w->now);
    if ($delta === null || $delta <= 0) {
        $p->err('timewarp: nerozumím času „' . $spec . '"' . "\n");
        $w->tip(tr('Zadej posun jako +30m, +2h, +90s, +1d, nebo cílový čas HH:MM (např. 06:25).'));
        return 1;
    }
    $oldNow = $w->now;
    $newNow = $oldNow + $delta;
    $jobs = lab58_cron_all_jobs($w);
    $ran = [];
    $count = 0;
    $GLOBALS['lab58_cron_running'] = true;
    $minutes = min(LAB58_CRON_MAX_WINDOW, (int)ceil($delta / 60));
    $start = intdiv($oldNow, 60) * 60 + 60;
    for ($step = 0, $ts = $start; $ts <= $newNow && $step < $minutes + 1; $ts += 60, $step++) {
        foreach ($jobs as $job) {
            if (($job['reboot'] ?? false) || !is_array($job['fields'] ?? null)) continue;
            if (!lab58_cron_match($job['fields'], $ts)) continue;
            if ($count >= LAB58_CRON_MAX_JOBS) break 2;
            lab58_cron_run_job($w, $job, $ts);
            $ran[] = ['user' => (string)($job['user'] ?: 'root'), 'command' => (string)$job['command'], 'ts' => $ts];
            $count++;
        }
    }
    $GLOBALS['lab58_cron_running'] = false;
    lab58_clock_advance($w, $delta);
    $p->line('Simulovaný čas posunut na ' . date('D Y-m-d H:i:s', $w->now) . ' (o ' . lab58_human_duration($delta) . ').');
    if ($ran === []) {
        $p->line('Během posunu neproběhla žádná naplánovaná úloha.');
        return 0;
    }
    $p->line('Proběhlo naplánovaných úloh: ' . count($ran));
    foreach (array_slice($ran, 0, 20) as $r) {
        $p->line('  ' . date('H:i', $r['ts']) . '  (' . $r['user'] . ')  ' . $r['command']);
    }
    if (count($ran) > 20) $p->line('  … a další (' . (count($ran) - 20) . ')');
    $p->line('Výstup úloh najdeš v /var/log/syslog (grep CRON) a v poště uživatele (/var/mail/<uživatel>).');
    return 0;
}

function lab58_human_duration(int $s): string
{
    if ($s % 86400 === 0) return ($s / 86400) . ' d';
    if ($s % 3600 === 0) return ($s / 3600) . ' h';
    if ($s % 60 === 0) return ($s / 60) . ' min';
    return $s . ' s';
}

// ---------------------------------------------------------------------------
// run-parts – spustí spustitelné skripty ve složce (používá ho výchozí /etc/crontab)
// ---------------------------------------------------------------------------

function lab58_cmd_run_parts(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $report = false;
    $test = false;
    $dir = null;
    foreach (array_slice($argv, 1) as $a) {
        if ($a === '--report') { $report = true; continue; }
        if ($a === '--test') { $test = true; continue; }
        if ($a === '--list' || str_starts_with($a, '--')) continue;
        if (!str_starts_with($a, '-')) $dir = $a;
    }
    if ($dir === null) { $p->err("Usage: run-parts [OPTION]... DIRECTORY\n"); return 1; }
    $abs = $w->abs($dir);
    $node = $w->fs->get($abs);
    if ($node === null || ($node['t'] ?? '') !== 'd') { $p->err("run-parts: failed to open directory $dir: No such file or directory\n"); return 1; }
    $status = 0;
    foreach ($w->fs->children($abs) as $name) {
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $name) !== 1) continue; // run-parts přeskočí soubory s tečkou apod.
        $childAbs = ($abs === '/' ? '' : $abs) . '/' . $name;
        $child = $w->fs->get($childAbs);
        if ($child === null || ($child['t'] ?? '') !== 'f' || ((int)($child['m'] ?? 0) & 0111) === 0) continue;
        if ($test) { $p->line($childAbs); continue; }
        if ($report) $p->err($childAbs . ":\n");
        $status = max($status, lab57_subrun($p, [$childAbs]));
    }
    return $status;
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

lab58_register_command('crontab', 'lab58_cmd_crontab', ['bin' => '/usr/bin/crontab']);
lab58_register_command('timewarp', 'lab58_cmd_timewarp', ['bin' => '/usr/local/bin/timewarp']);
lab58_register_command('run-parts', 'lab58_cmd_run_parts', ['bin' => '/usr/bin/run-parts', 'help' => false]);

lab58_register_manual(['commands' => [
    'crontab' => ['extend' => true, 'tldr' => [['crontab -l', 'vypíše naplánované úlohy'], ['crontab muj-plan.txt', 'nastaví plán ze souboru'], ['crontab -r', 'smaže celý plán']], 'see_also' => ['timewarp', 'systemctl', 'journalctl']],
    'timewarp' => ['cat' => 'procesy', 'summary' => 'Posune simulovaný čas a spustí úlohy cronu, které měly proběhnout.', 'synopsis' => 'timewarp +30m | +2h | +1d | HH:MM', 'about' => 'Cvičný příkaz laboratoře: v běžném Linuxu neexistuje. Umožní „přeskočit" čas dopředu, aby sis nemusel(a) hodinu čekat, než se spustí naplánovaná úloha. Posune hodiny a spustí všechny úlohy z crontabů, jejichž čas během posunu nastal. Výstup úloh uvidíš v /var/log/syslog a v poště uživatele.', 'options' => [['+30m', 'posun o 30 minut (s, m, h, d)'], ['+2h', 'posun o 2 hodiny'], ['+1d', 'posun o jeden den'], ['HH:MM', 'posun na nejbližší tento čas']], 'examples' => [['timewarp +1h', 'posune čas o hodinu a spustí, co mělo proběhnout'], ['timewarp 06:25', 'posune čas na 06:25'], ['timewarp +1d', 'posune čas o den (spustí denní úlohy)']], 'tldr' => [['timewarp +1h', 'skok o hodinu a spuštění úloh'], ['timewarp 06:25', 'skok na konkrétní čas']], 'see_also' => ['crontab', 'journalctl', 'grep'], 'related' => ['crontab', 'date'], 'level' => 3, 'in_lab' => true, 'tip' => 'Po timewarpu si výsledek ověř: grep CRON /var/log/syslog a cat /var/mail/student.'],
]]);
