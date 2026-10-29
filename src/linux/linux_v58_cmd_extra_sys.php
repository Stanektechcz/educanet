<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – drobné systémové příkazy (CNT-02): whereis, pgrep, lsblk, lscpu, mount
 * (jen výpis), watch (1 průchod), time, expr, factor. Údaje o stroji odpovídají světu v57
 * (/proc/cpuinfo, df, /etc/fstab). Nic se nespouští, žádná síť.
 */

// ---------------------------------------------------------------------------
// whereis, pgrep
// ---------------------------------------------------------------------------

function lab58_cmd_whereis(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $want = ['b' => true, 'm' => true];
    $names = [];
    foreach ($args as $a) {
        if (in_array($a, ['-b', '-m', '-s'], true)) { $want = ['b' => $a === '-b', 'm' => $a === '-m']; continue; }
        if ($a === '-l') { $p->line("bin: /usr/bin\nbin: /usr/sbin\nbin: /usr/local/bin\nbin: /bin\nbin: /sbin\nman: /usr/share/man/man1\nman: /usr/share/man/man8\nsrc: /usr/src"); return 0; }
        if (str_starts_with($a, '-')) {
            $p->err("whereis: bad usage\nTry 'whereis --help' for more information.\n");
            $w->tip(tr('whereis zná -b (jen programy), -m (jen manuálové stránky). → man whereis'));
            return 1;
        }
        $names[] = $a;
    }
    if ($names === []) { $p->err("whereis: not enough arguments\nTry 'whereis --help' for more information.\n"); return 1; }
    $manual = function_exists('v57_manual') ? (array)(v57_manual()['commands'] ?? []) : [];
    foreach ($names as $name) {
        $found = [];
        if ($want['b']) {
            foreach (['/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin', '/bin', '/sbin', '/usr/lib', '/etc', '/usr/share'] as $dir) {
                if ($w->fs->exists($dir . '/' . $name) && !str_contains($name, '/')) $found[] = $dir . '/' . $name;
            }
        }
        if ($want['m'] && isset($manual[$name]) && !in_array($name, lab57_builtins(), true)) $found[] = '/usr/share/man/man' . (str_starts_with(lab57_command_bin($name), '/usr/sbin') ? '8' : '1') . '/' . $name . '.' . (str_starts_with(lab57_command_bin($name), '/usr/sbin') ? '8' : '1') . '.gz';
        $p->line($name . ':' . ($found === [] ? '' : ' ' . implode(' ', $found)));
    }
    return 0;
}

function lab58_cmd_pgrep(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'lafxicnovu:d:', ['list-name' => false, 'list-full' => false, 'full' => false, 'exact' => false, 'ignore-case' => false, 'count' => false, 'newest' => false, 'oldest' => false, 'inverse' => false, 'euid' => true, 'delimiter' => true]);
    if ($error !== null) {
        $p->err("pgrep: $error\n\nUsage:\n pgrep [options] <pattern>\n\nFor more details see pgrep(1).\n");
        $w->tip(tr('pgrep najde čísla procesů podle jména: pgrep ssh, s názvy pgrep -l ssh. → man pgrep'));
        return 2;
    }
    $pattern = $ops[0] ?? null;
    $user = $o['u'] ?? $o['--euid'] ?? null;
    if ($pattern === null && $user === null) {
        $p->err("pgrep: no matching criteria specified\nTry `pgrep --help' for more information.\n");
        $w->tip(tr('Napiš, co hledáš: pgrep nginx (čísla procesů), pgrep -a nginx (i s příkazem). → man pgrep'));
        return 2;
    }
    if (count($ops) > 1) { $p->err("pgrep: only one pattern can be provided\nTry `pgrep --help' for more information.\n"); return 2; }
    $body = $pattern === null ? '' : lab57_regex_body((string)$pattern, 'ere');
    $re = '~' . (isset($o['x']) ? '^(?:' . $body . ')$' : $body) . '~' . (isset($o['i']) ? 'i' : '');
    if ($pattern !== null && @preg_match($re, '') === false) { $p->err("pgrep: cannot compile regular expression '$pattern'\n"); return 2; }
    $hits = [];
    foreach ($w->procs as $pid => $proc) {
        $cmd = (string)$proc['cmd'];
        $subject = isset($o['f']) || isset($o['--full']) ? $cmd : substr(lab57_proc_name($cmd), 0, 15);
        $ok = ($pattern === null || preg_match($re, $subject) === 1) && ($user === null || in_array((string)$proc['user'], explode(',', (string)$user), true));
        if ($ok xor (isset($o['v']) || isset($o['--inverse']))) $hits[(int)$pid] = $proc;
    }
    ksort($hits);
    if (($o['n'] ?? $o['o'] ?? null) !== null && $hits !== []) {
        uasort($hits, static fn(array $a, array $b): int => (int)$a['start'] <=> (int)$b['start']);
        $hits = isset($o['n']) ? array_slice($hits, -1, 1, true) : array_slice($hits, 0, 1, true);
    }
    if (isset($o['c']) || isset($o['--count'])) { $p->line((string)count($hits)); return $hits === [] ? 1 : 0; }
    $out = [];
    foreach ($hits as $pid => $proc) {
        $out[] = $pid . (isset($o['a']) || isset($o['--list-full']) ? ' ' . $proc['cmd'] : (isset($o['l']) || isset($o['--list-name']) ? ' ' . lab57_proc_name((string)$proc['cmd']) : ''));
    }
    if ($out !== []) $p->out(implode((string)($o['d'] ?? $o['--delimiter'] ?? "\n"), $out) . "\n");
    return $hits === [] ? 1 : 0;
}

// ---------------------------------------------------------------------------
// lsblk, lscpu, mount (údaje shodné s df a /proc/cpuinfo ve světě v57)
// ---------------------------------------------------------------------------

/** Velikost jako util-linux (mocniny 1024, jedno desetinné místo, bez „.0“). */
function lab58_sys_size(int $bytes): string
{
    $units = ['B', 'K', 'M', 'G', 'T'];
    $i = 0;
    $v = (float)$bytes;
    while ($v >= 1024 && $i < 4) { $v /= 1024; $i++; }
    $text = rtrim(rtrim(number_format(round($v, 1), 1, '.', ''), '0'), '.');
    return $text . $units[$i];
}

/** @return list<array<string,string>> řádky lsblk */
function lab58_sys_blockdevs(Lab57World $w): array
{
    $disk = lab57_disk_usage($w);
    $avail = max(0, $disk['size'] - $disk['used']);
    $pct = $disk['size'] > 0 ? (int)ceil($disk['used'] / $disk['size'] * 100) . '%' : '';
    $rows = [
        ['NAME' => 'sda', 'TREE' => '', 'MAJ:MIN' => '8:0', 'RM' => '0', 'SIZE' => $disk['size'] + 1073741824, 'RO' => '0', 'TYPE' => 'disk', 'MOUNTPOINTS' => '', 'FSTYPE' => '', 'FSVER' => '', 'LABEL' => '', 'UUID' => '', 'FSAVAIL' => '', 'FSUSE%' => '', 'MODEL' => 'QEMU HARDDISK'],
        ['NAME' => 'sda1', 'TREE' => '├─', 'MAJ:MIN' => '8:1', 'RM' => '0', 'SIZE' => $disk['size'], 'RO' => '0', 'TYPE' => 'part', 'MOUNTPOINTS' => '/', 'FSTYPE' => 'ext4', 'FSVER' => '1.0', 'LABEL' => '', 'UUID' => '3f6c2a1e-7b1d-4c55-9a0e-5d7c1e2b8f10', 'FSAVAIL' => lab58_sys_size($avail), 'FSUSE%' => $pct, 'MODEL' => ''],
        ['NAME' => 'sda2', 'TREE' => '├─', 'MAJ:MIN' => '8:2', 'RM' => '0', 'SIZE' => 1024, 'RO' => '0', 'TYPE' => 'part', 'MOUNTPOINTS' => '', 'FSTYPE' => '', 'FSVER' => '', 'LABEL' => '', 'UUID' => '', 'FSAVAIL' => '', 'FSUSE%' => '', 'MODEL' => ''],
        ['NAME' => 'sda5', 'TREE' => '└─', 'MAJ:MIN' => '8:5', 'RM' => '0', 'SIZE' => 1073741824, 'RO' => '0', 'TYPE' => 'part', 'MOUNTPOINTS' => '[SWAP]', 'FSTYPE' => 'swap', 'FSVER' => '1', 'LABEL' => '', 'UUID' => '0d1f3c4e-5a6b-4c7d-8e9f-a0b1c2d3e4f5', 'FSAVAIL' => '', 'FSUSE%' => '', 'MODEL' => ''],
        ['NAME' => 'sr0', 'TREE' => '', 'MAJ:MIN' => '11:0', 'RM' => '1', 'SIZE' => 1073741312, 'RO' => '0', 'TYPE' => 'rom', 'MOUNTPOINTS' => '', 'FSTYPE' => '', 'FSVER' => '', 'LABEL' => '', 'UUID' => '', 'FSAVAIL' => '', 'FSUSE%' => '', 'MODEL' => 'QEMU DVD-ROM'],
    ];
    return $rows;
}

function lab58_cmd_lsblk(Lab57Proc $p, array $argv): int
{
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'fdlpbo:mn', ['fs' => false, 'nodeps' => false, 'list' => false, 'paths' => false, 'bytes' => false, 'output' => true, 'noheadings' => false]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    $cols = isset($o['f']) || isset($o['--fs']) ? ['NAME', 'FSTYPE', 'FSVER', 'LABEL', 'UUID', 'FSAVAIL', 'FSUSE%', 'MOUNTPOINTS'] : ['NAME', 'MAJ:MIN', 'RM', 'SIZE', 'RO', 'TYPE', 'MOUNTPOINTS'];
    $spec = $o['o'] ?? $o['--output'] ?? null;
    if ($spec !== null) {
        $cols = [];
        foreach (explode(',', strtoupper((string)$spec)) as $c) {
            $c = $c === 'MOUNTPOINT' ? 'MOUNTPOINTS' : $c;
            if (!in_array($c, ['NAME', 'MAJ:MIN', 'RM', 'SIZE', 'RO', 'TYPE', 'MOUNTPOINTS', 'FSTYPE', 'FSVER', 'LABEL', 'UUID', 'FSAVAIL', 'FSUSE%', 'MODEL'], true)) { $p->err("lsblk: unknown column: $c\n"); return 1; }
            $cols[] = $c;
        }
    }
    $rows = lab58_sys_blockdevs($p->w);
    if (isset($o['d']) || isset($o['--nodeps'])) $rows = array_values(array_filter($rows, static fn(array $r): bool => $r['TREE'] === ''));
    $flat = isset($o['l']) || isset($o['--list']) || isset($o['d']);
    $table = [];
    foreach ($rows as $r) {
        if ($ops !== [] && !in_array('/dev/' . $r['NAME'], $ops, true) && !in_array('/dev/' . preg_replace('/\d+$/', '', $r['NAME']), $ops, true)) continue;
        $line = [];
        foreach ($cols as $c) {
            $v = $c === 'SIZE' ? (isset($o['b']) || isset($o['--bytes']) ? (string)$r['SIZE'] : lab58_sys_size((int)$r['SIZE'])) : (string)$r[$c];
            if ($c === 'NAME') $v = ($flat ? '' : $r['TREE']) . (isset($o['p']) || isset($o['--paths']) ? '/dev/' : '') . $v;
            if ($c === 'MAJ:MIN') { [$maj, $min] = explode(':', $v); $v = str_pad($maj, 3, ' ', STR_PAD_LEFT) . ':' . str_pad($min, 3); }
            $line[$c] = $v;
        }
        $table[] = $line;
    }
    $right = ['RM', 'SIZE', 'RO', 'FSAVAIL', 'FSUSE%'];
    $width = [];
    foreach ($cols as $c) {
        $width[$c] = isset($o['n']) ? 0 : mb_strlen($c);
        foreach ($table as $line) $width[$c] = max($width[$c], mb_strlen($line[$c]));
    }
    $render = static function (array $line) use ($cols, $width, $right): string {
        $parts = [];
        foreach ($cols as $i => $c) {
            $pad = str_repeat(' ', max(0, $width[$c] - mb_strlen($line[$c])));
            $parts[] = in_array($c, $right, true) ? $pad . $line[$c] : ($i === count($cols) - 1 ? $line[$c] : $line[$c] . $pad);
        }
        return implode(' ', $parts);
    };
    if (!isset($o['n']) && !isset($o['--noheadings'])) $p->line($render(array_combine($cols, $cols)));
    foreach ($table as $line) $p->line($render($line));
    return 0;
}

function lab58_cmd_lscpu(Lab57Proc $p, array $argv): int
{
    $args = array_slice($argv, 1);
    if ($args !== [] && !in_array($args[0], ['-e', '--extended'], true)) return lab57_opt_error($p, str_starts_with($args[0], '--') ? "unrecognized option '{$args[0]}'" : "invalid option -- '" . substr($args[0], 1, 1) . "'", 1);
    if ($args !== []) {
        $p->out("CPU NODE SOCKET CORE L1d:L1i:L2:L3 ONLINE\n  0    0      0    0 0:0:0:0          yes\n  1    0      0    1 1:1:1:0          yes\n");
        return 0;
    }
    $rows = [
        ['Architecture:', 'x86_64'], ['  CPU op-mode(s):', '32-bit, 64-bit'], ['  Address sizes:', '39 bits physical, 48 bits virtual'], ['  Byte Order:', 'Little Endian'],
        ['CPU(s):', '2'], ['  On-line CPU(s) list:', '0,1'], ['Vendor ID:', 'GenuineIntel'], ['  Model name:', 'Intel(R) Core(TM) i5-10400 CPU @ 2.90GHz'],
        ['    CPU family:', '6'], ['    Model:', '165'], ['    Thread(s) per core:', '1'], ['    Core(s) per socket:', '2'], ['    Socket(s):', '1'], ['    Stepping:', '3'],
        ['    BogoMIPS:', '5808.00'], ['    Flags:', 'fpu vme de pse tsc msr pae mce cx8 apic sep mtrr pge mca cmov pat pse36 clflush mmx fxsr sse sse2 ht syscall nx rdtscp lm constant_tsc rep_good nopl xtopology cpuid tsc_known_freq pni pclmulqdq ssse3 fma cx16 pcid sse4_1 sse4_2 x2apic movbe popcnt aes xsave avx f16c rdrand hypervisor lahf_lm abm 3dnowprefetch fsgsbase bmi1 avx2 smep bmi2 erms invpcid rdseed adx smap clflushopt xsaveopt xsavec xgetbv1 xsaves arat md_clear flush_l1d arch_capabilities'],
        ['Virtualization features:', ''], ['  Hypervisor vendor:', 'KVM'], ['  Virtualization type:', 'full'],
        ['Caches (sum of all):', ''], ['  L1d:', '64 KiB (2 instances)'], ['  L1i:', '64 KiB (2 instances)'], ['  L2:', '512 KiB (2 instances)'], ['  L3:', '12 MiB (1 instance)'],
        ['NUMA:', ''], ['  NUMA node(s):', '1'], ['  NUMA node0 CPU(s):', '0,1'],
        ['Vulnerabilities:', ''], ['  Meltdown:', 'Not affected'], ['  Spectre v1:', 'Mitigation; usercopy/swapgs barriers and __user pointer sanitization'], ['  Spectre v2:', 'Mitigation; Enhanced IBRS, IBPB conditional, RSB filling, PBRSB-eIBRS SW sequence'],
    ];
    foreach ($rows as [$k, $v]) $p->line(str_pad($k, 25) . $v);
    return 0;
}

function lab58_cmd_mount(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'lt:avo:r', ['types' => true, 'all' => false, 'show-labels' => false, 'read-only' => false, 'options' => true]);
    if ($error !== null) return lab57_opt_error($p, $error, 1);
    if (isset($o['a']) || isset($o['--all']) || $ops !== []) {
        if (!$w->root) { $p->err('mount: ' . ($ops[1] ?? $ops[0] ?? '/') . ": must be superuser to use mount.\n"); $w->tip(tr('Připojování disků smí jen správce (sudo mount …). Seznam připojených svazků vypíše samotné mount nebo df -h.')); return 32; }
        if ($ops === []) return 0;
        $target = (string)($ops[1] ?? $ops[0]);
        $source = (string)$ops[0];
        if (count($ops) === 1) { $p->err("mount: $target: can't find in /etc/fstab.\n"); return 1; }
        if (!$w->fs->isDir($w->abs($target))) { $p->err("mount: $target: mount point does not exist.\n"); return 32; }
        $p->err("mount: $target: " . ($source === '/dev/sda1' ? "/dev/sda1 already mounted on /.\n" : "special device $source does not exist.\n"));
        $w->tip(tr('V laboratoři není žádný další disk ani flash disk k připojení – co existuje, ukáže lsblk.'));
        return 32;
    }
    $disk = lab57_disk_usage($w);
    $list = [
        ['sysfs', '/sys', 'sysfs', 'rw,nosuid,nodev,noexec,relatime'], ['proc', '/proc', 'proc', 'rw,nosuid,nodev,noexec,relatime'],
        ['udev', '/dev', 'devtmpfs', 'rw,nosuid,relatime,size=1987440k,nr_inodes=496860,mode=755,inode64'], ['devpts', '/dev/pts', 'devpts', 'rw,nosuid,noexec,relatime,gid=5,mode=620,ptmxmode=000'],
        ['tmpfs', '/run', 'tmpfs', 'rw,nosuid,nodev,noexec,relatime,size=402852k,mode=755,inode64'], ['/dev/sda1', '/', 'ext4', 'rw,relatime,errors=remount-ro'],
        ['tmpfs', '/dev/shm', 'tmpfs', 'rw,nosuid,nodev,inode64'], ['tmpfs', '/run/lock', 'tmpfs', 'rw,nosuid,nodev,noexec,relatime,size=5120k,inode64'],
        ['cgroup2', '/sys/fs/cgroup', 'cgroup2', 'rw,nosuid,nodev,noexec,relatime,nsdelegate,memory_recursiveprot'],
        ['tmpfs', '/run/user/1000', 'tmpfs', 'rw,nosuid,nodev,relatime,size=402848k,nr_inodes=100712,mode=700,uid=1000,gid=1000,inode64'],
    ];
    $types = isset($o['t']) ? explode(',', (string)$o['t']) : null;
    foreach ($list as [$src, $dst, $type, $opts]) {
        if ($types !== null && !in_array($type, $types, true)) continue;
        $p->line("$src on $dst type $type ($opts)");
    }
    return 0;
}

// ---------------------------------------------------------------------------
// watch (jeden průchod), time (deterministické časy)
// ---------------------------------------------------------------------------

function lab58_cmd_watch(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_slice($argv, 1));
    if (($args[0] ?? '') === '--help') return lab57_manual_help($p, 'watch') ? 0 : 1;
    $interval = 2.0;
    $title = true;
    $direct = false;
    while ($args !== [] && str_starts_with((string)$args[0], '-')) {
        $a = (string)array_shift($args);
        if ($a === '--') break;
        if (preg_match('/^(-n|--interval=?)(.*)$/', $a, $m) === 1) {
            $v = $m[2] !== '' ? $m[2] : array_shift($args);
            if ($v === null || !is_numeric(str_replace(',', '.', (string)$v))) { $p->err("watch: failed to parse argument: '" . ($v ?? '') . "'\n"); return 1; }
            $interval = max(0.1, (float)str_replace(',', '.', (string)$v));
            continue;
        }
        if ($a === '--no-title' || $a === '--exec' || preg_match('/^-[dbecgptwx]+$/', $a) === 1 || in_array($a, ['--differences', '--beep', '--errexit', '--color', '--chgexit', '--precise', '--no-wrap'], true)) {
            if ($a === '--no-title' || (!str_starts_with($a, '--') && str_contains($a, 't'))) $title = false;
            if ($a === '--exec' || (!str_starts_with($a, '--') && str_contains($a, 'x'))) $direct = true;
            continue;
        }
        $p->err('watch: ' . (str_starts_with($a, '--') ? "unrecognized option '$a'" : "invalid option -- '" . $a[1] . "'") . "\n\nUsage:\n watch [options] command\n");
        $w->tip(tr('watch -n 5 příkaz spouští příkaz každých 5 s. → man watch'));
        return 1;
    }
    if ($args === []) { $p->err("\nUsage:\n watch [options] command\n\nOptions:\n  -n, --interval <secs>  seconds to wait between updates\n  -t, --no-title         turn off header\n"); return 1; }
    if ($w->depth >= 4) { $p->err("watch: příliš hluboké vnoření příkazů\n"); return 1; }
    $cmd = implode(' ', $args);
    if ($title) {
        $right = $w->hostname . ': ' . date('D M ', $w->now) . str_pad(date('j', $w->now), 2, ' ', STR_PAD_LEFT) . date(' H:i:s Y', $w->now);
        $left = mb_substr(sprintf('Every %.1fs: %s', $interval, $cmd), 0, max(10, 79 - mb_strlen($right)));
        $p->line($left . str_repeat(' ', max(1, 80 - mb_strlen($left) - mb_strlen($right))) . $right);
        $p->line('');
    }
    $w->depth++;
    try {
        if ($direct) {
            lab57_subrun($p, $args);
        } else {
            $term = [];
            try {
                lab57_exec_source($w, $cmd, $term);
            } catch (Lab57SyntaxError $e) {
                $term[] = [2, 'sh: 1: ' . $e->getMessage() . "\n"];
            }
            foreach ($term as [$fd, $text]) { if ($fd === 1) $p->out($text); else $p->err($text); }
        }
    } finally {
        $w->depth--;
    }
    $w->tip(tr('watch v simulaci proběhne jen jednou. Skutečný watch by příkaz spouštěl každých {sec} s a obnovoval obrazovku, dokud ho neukončíš Ctrl+C.', ['sec' => rtrim(rtrim(number_format($interval, 1, '.', ''), '0'), '.')]));
    return 0;
}

function lab58_time_text(int $ms, bool $posix): string
{
    return $posix ? sprintf('%d.%02d', intdiv($ms, 1000), intdiv($ms % 1000, 10)) : sprintf('%dm%d.%03ds', intdiv($ms, 60000), intdiv($ms % 60000, 1000), $ms % 1000);
}

/** time (klíčové slovo bashe): spustí příkaz a na stderr vypíše real/user/sys. Časy jsou deterministické (podle objemu práce). */
function lab58_cmd_time(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_slice($argv, 1));
    $posix = ($args[0] ?? '') === '-p';
    if ($posix) array_shift($args);
    $status = 0;
    $sleep = 0.0;
    $mark = count($p->chunks);
    if ($args !== []) {
        if ($w->depth >= 4) { $p->err("bash: time: příliš hluboké vnoření příkazů\n"); return 1; }
        if ($args[0] === 'sleep') {
            foreach (array_slice($args, 1) as $a) {
                if (preg_match('/^(\d+(?:\.\d+)?)([smhd]?)$/', $a, $m) === 1) $sleep += (float)$m[1] * ['' => 1, 's' => 1, 'm' => 60, 'h' => 3600, 'd' => 86400][$m[2]];
            }
        }
        $w->depth++;
        try {
            $status = lab57_subrun($p, $args);
        } finally {
            $w->depth--;
        }
    }
    $bytes = 0;
    foreach (array_slice($p->chunks, $mark) as [, $text]) $bytes += strlen($text);
    $work = $args === [] ? 0 : 1 + intdiv($bytes, 4096) + count($args);
    $real = $work + (int)round($sleep * 1000);
    $user = intdiv($work, 3);
    $sys = $work - $user - ($work > 1 ? 1 : 0);
    $p->err($posix
        ? 'real ' . lab58_time_text($real, true) . "\nuser " . lab58_time_text($user, true) . "\nsys " . lab58_time_text(max(0, $sys), true) . "\n"
        : "\nreal\t" . lab58_time_text($real, false) . "\nuser\t" . lab58_time_text($user, false) . "\nsys\t" . lab58_time_text(max(0, $sys), false) . "\n");
    return $status;
}

// ---------------------------------------------------------------------------
// expr (coreutils 9.1): priority | & relace +- */% : a výrazy match/substr/index/length
// ---------------------------------------------------------------------------

final class Lab58ExprError extends RuntimeException
{
}

function lab58_expr_null(string $v): bool
{
    return $v === '' || preg_match('/^-?0+$/', $v) === 1;
}

function lab58_expr_int(string $v): int
{
    if (preg_match('/^-?\d{1,18}$/', $v) !== 1) throw new Lab58ExprError('non-integer argument', 2);
    return (int)$v;
}

/** @param list<string> $a */
function lab58_expr_eval(array $a, int &$i, int $level): string
{
    $ops = [0 => ['|'], 1 => ['&'], 2 => ['<', '<=', '=', '==', '!=', '>=', '>'], 3 => ['+', '-'], 4 => ['*', '/', '%'], 5 => [':']];
    if ($level > 5) return lab58_expr_primary($a, $i);
    $l = lab58_expr_eval($a, $i, $level + 1);
    while (isset($a[$i]) && in_array($a[$i], $ops[$level], true)) {
        $op = $a[$i++];
        if (!isset($a[$i])) throw new Lab58ExprError('syntax error: missing argument after ‘' . $op . '’', 2);
        $r = lab58_expr_eval($a, $i, $level + 1);
        $l = lab58_expr_apply($op, $l, $r);
    }
    return $l;
}

function lab58_expr_apply(string $op, string $l, string $r): string
{
    switch ($op) {
        case '|': return !lab58_expr_null($l) ? $l : (!lab58_expr_null($r) ? $r : '0');
        case '&': return !lab58_expr_null($l) && !lab58_expr_null($r) ? $l : '0';
        case ':': return lab58_expr_match($l, $r);
    }
    if (in_array($op, ['<', '<=', '=', '==', '!=', '>=', '>'], true)) {
        $num = preg_match('/^-?\d+$/', $l) === 1 && preg_match('/^-?\d+$/', $r) === 1;
        $c = $num ? ((int)$l <=> (int)$r) : (strcmp($l, $r) <=> 0);
        return match ($op) { '<' => $c < 0, '<=' => $c <= 0, '=', '==' => $c === 0, '!=' => $c !== 0, '>=' => $c >= 0, default => $c > 0 } ? '1' : '0';
    }
    $x = lab58_expr_int($l);
    $y = lab58_expr_int($r);
    if (($op === '/' || $op === '%') && $y === 0) throw new Lab58ExprError('division by zero', 2);
    $res = match ($op) { '+' => $x + $y, '-' => $x - $y, '*' => $x * $y, '/' => intdiv($x, $y), default => $x % $y };
    if (!is_int($res)) throw new Lab58ExprError('integer result too large', 3);
    return (string)$res;
}

function lab58_expr_match(string $s, string $re): string
{
    $body = lab57_regex_body(ltrim($re, '^'), 'bre');
    $pcre = '~^(?:' . $body . ')~u';
    if (@preg_match($pcre, '') === false) throw new Lab58ExprError('Invalid regular expression', 2);
    if (preg_match($pcre, $s, $m) !== 1) return str_contains($body, '(') && !str_contains($body, '\\(') ? '' : '0';
    return isset($m[1]) ? $m[1] : (string)mb_strlen($m[0]);
}

function lab58_expr_primary(array $a, int &$i): string
{
    if (!isset($a[$i])) throw new Lab58ExprError('syntax error: missing argument after ‘' . ($a[$i - 1] ?? '') . '’', 2);
    $t = $a[$i++];
    $arg = static function () use ($a, &$i): string { return lab58_expr_primary($a, $i); };
    switch ($t) {
        case '(':
            $v = lab58_expr_eval($a, $i, 0);
            if (($a[$i] ?? null) !== ')') throw new Lab58ExprError('syntax error: expecting \')\' ' . (isset($a[$i]) ? 'instead of ‘' . $a[$i] . '’' : 'after ‘' . $a[$i - 1] . '’'), 2);
            $i++;
            return $v;
        case '+': return (string)($a[$i++] ?? throw new Lab58ExprError('syntax error: missing argument after ‘+’', 2));
        case 'length': return (string)mb_strlen($arg());
        case 'match': $s = $arg(); return lab58_expr_match($s, $arg());
        case 'index':
            $s = $arg();
            $chars = $arg();
            foreach (mb_str_split($s) as $k => $ch) if (str_contains($chars, $ch)) return (string)($k + 1);
            return '0';
        case 'substr':
            $s = $arg();
            $pos = $arg();
            $len = $arg();
            if (preg_match('/^\d+$/', $pos) !== 1 || preg_match('/^\d+$/', $len) !== 1 || (int)$pos < 1) return '';
            return mb_substr($s, (int)$pos - 1, (int)$len);
    }
    return $t;
}

function lab58_cmd_expr(Lab57Proc $p, array $argv): int
{
    $a = array_values(array_slice($argv, 1));
    if (($a[0] ?? '') === '--') array_shift($a);
    if ($a === []) { $p->err("expr: missing operand\nTry 'expr --help' for more information.\n"); return 2; }
    $i = 0;
    try {
        $v = lab58_expr_eval($a, $i, 0);
        if ($i < count($a)) throw new Lab58ExprError('syntax error: unexpected argument ‘' . $a[$i] . '’', 2);
    } catch (Lab58ExprError $e) {
        $p->err('expr: ' . $e->getMessage() . "\n");
        $p->w->tip(str_contains($e->getMessage(), 'syntax') ? tr('Mezi čísly a operátory musí být mezery (expr 3 + 4). Hvězdičku a závorky chraň před shellem: expr 3 \\* 4, expr \\( 1 + 2 \\) \\* 3. → man expr') : tr('expr počítá jen s celými čísly; na desetinná čísla použij bc. → man expr'));
        return $e->getCode();
    }
    $p->line($v);
    return lab58_expr_null($v) ? 1 : 0;
}

// ---------------------------------------------------------------------------
// factor (64bitová čísla: pokusné dělení + Pollard rho s Miller–Rabinem)
// ---------------------------------------------------------------------------

function lab58_factor_mulmod(int $a, int $b, int $m): int
{
    if ($a < 3037000499 && $b < 3037000499) return ($a * $b) % $m;
    $r = 0;
    $a %= $m;
    while ($b > 0) {
        if (($b & 1) === 1) $r = $r >= $m - $a ? $r - ($m - $a) : $r + $a;
        $a = $a >= $m - $a ? $a - ($m - $a) : $a + $a;
        $b >>= 1;
    }
    return $r;
}

function lab58_factor_powmod(int $b, int $e, int $m): int
{
    $r = 1;
    $b %= $m;
    for (; $e > 0; $e >>= 1) {
        if (($e & 1) === 1) $r = lab58_factor_mulmod($r, $b, $m);
        $b = lab58_factor_mulmod($b, $b, $m);
    }
    return $r;
}

function lab58_factor_prime(int $n): bool
{
    if ($n < 2) return false;
    foreach ([2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37] as $q) if ($n % $q === 0) return $n === $q;
    $d = $n - 1;
    $s = 0;
    while (($d & 1) === 0) { $d >>= 1; $s++; }
    foreach ([2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37] as $a) {
        $x = lab58_factor_powmod($a, $d, $n);
        if ($x === 1 || $x === $n - 1) continue;
        for ($r = 1; $r < $s; $r++) { $x = lab58_factor_mulmod($x, $x, $n); if ($x === $n - 1) continue 2; }
        return false;
    }
    return true;
}

/** @return list<int> prvočinitele vzestupně */
function lab58_factor(int $n): array
{
    $out = [];
    foreach ([2, 3, 5] as $q) while ($n % $q === 0) { $out[] = $q; $n = intdiv($n, $q); }
    for ($q = 7; $q <= 10000 && $q * $q <= $n; $q += 2) while ($n % $q === 0) { $out[] = $q; $n = intdiv($n, $q); }
    $stack = $n > 1 ? [$n] : [];
    while ($stack !== []) {
        $m = array_pop($stack);
        if (lab58_factor_prime($m)) { $out[] = $m; continue; }
        $d = $m;
        for ($c = 1; $d === $m && $c < 20; $c++) {
            $x = 2;
            $y = 2;
            $d = 1;
            for ($guard = 0; $d === 1 && $guard < 200000; $guard++) {
                $x = (lab58_factor_mulmod($x, $x, $m) + $c) % $m;
                $y = (lab58_factor_mulmod($y, $y, $m) + $c) % $m;
                $y = (lab58_factor_mulmod($y, $y, $m) + $c) % $m;
                $a = abs($x - $y);
                for ($b = $m; $b !== 0;) [$a, $b] = [$b, $a % $b];
                $d = $a;
            }
        }
        if ($d === $m || $d <= 1) { $out[] = $m; continue; }
        $stack[] = $d;
        $stack[] = intdiv($m, $d);
    }
    sort($out);
    return $out;
}

function lab58_cmd_factor(Lab57Proc $p, array $argv): int
{
    $nums = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => $a !== '--'));
    if ($nums === []) {
        if (!$p->hasStdin) { lab57_needs_input($p, []); return 0; }
        $nums = preg_split('/\s+/', trim($p->stdin), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
    $status = 0;
    foreach (array_slice($nums, 0, 1000) as $raw) {
        $digits = ltrim(ltrim($raw, '+'), '0');
        if (preg_match('/^\+?\d+$/', $raw) !== 1) { $p->err("factor: ‘{$raw}’ is not a valid positive integer\n"); $status = 1; continue; }
        if (strlen($digits) > 19 || (strlen($digits) === 19 && strcmp($digits, (string)PHP_INT_MAX) > 0)) { $p->err("factor: ‘{$raw}’ is too large\n"); $status = 1; continue; }
        $n = (int)$digits;
        $p->line($n . ':' . ($n < 2 ? '' : ' ' . implode(' ', lab58_factor($n))));
    }
    return $status;
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

foreach (['whereis', 'pgrep', 'lsblk', 'lscpu', 'mount', 'expr', 'factor'] as $lab58SysName) lab58_register_command($lab58SysName, 'lab58_cmd_' . $lab58SysName);
unset($lab58SysName);
lab58_register_command('watch', 'lab58_cmd_watch', ['help' => false]);
lab58_register_command('time', 'lab58_cmd_time', ['builtin' => true, 'help' => false]);
