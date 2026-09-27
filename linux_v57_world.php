<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – simulovaný stroj.
 *
 * Svět = souborový systém + uživatelé + služby + procesy + síť. Výchozí svět úrovně se
 * staví deterministicky ze semínka; ukládají se jen změny, které žák udělal.
 * Veřejné adresy v simulaci jsou z dokumentačních rozsahů (RFC 5737) nebo známé veřejné
 * DNS resolvery – nic se však nikam neposílá, vše jsou jen data v paměti.
 */

final class Lab57World
{
    public Lab57Vfs $fs;
    public string $user = 'student';
    public string $cwd = '/home/student';
    public string $oldCwd = '/home/student';
    /** @var array<string,string> proměnné nastavené žákem */
    public array $env = [];
    public string $hostname = 'lab-pc';
    public array $users = [];
    public array $groups = [];
    public array $services = [];
    public array $procs = [];
    public array $net = [];
    public array $packages = [];
    public array $history = [];
    public array $journal = [];
    public array $aliases = [];
    public array $level = [];
    /** @var array<string,string> kódy úrovně (zástupný symbol → hodnota) */
    public array $codes = [];
    public string $seed = '';
    public int $now = 0;
    public int $bootTime = 0;
    public int $lastExit = 0;
    public bool $sudoAllowed = true;
    public bool $root = false;
    public array $effects = [];
    public array $tips = [];
    public int $pidCounter = 3100;
    /** @var array<string,mixed> pomocný stav úrovně (serializuje se) */
    public array $mem = [];
    public int $depth = 0;
    public int $steps = 0;
    /** @var array<string,callable> simulované programy (neserializují se) */
    public array $programs = [];
    /** @var array<string,callable> simulované síťové služby "ip:port" → handler(vstup): odpověď */
    public array $netServices = [];
    /** @var list<string> poziční argumenty běžícího skriptu ($1, $2 …) */
    public array $args = [];
    /** @var list<array{0:int,1:string}> chybový výstup z vnořených $(…), který patří do terminálu */
    public array $stray = [];
    /** @var array<string,mixed> v58: rozšiřující stav (ukládá se, např. clock_offset) */
    public array $ext = [];
    /** @var array<string,string> v58: fakta z generátorů deklarativní úrovně (neukládají se, staví se znovu) */
    public array $facts = [];
    /** @var array<string,mixed> v58: údaje požadavku – context, prefix, role, player, shared (neukládají se) */
    public array $session = [];
    /** v58: reálný čas požadavku; simulovaný čas = lab58_now() */
    public int $clockBase = 0;
    /** v58: otisk výchozích uživatelů a skupin (ukládají se jen při změně) */
    public string $baseAccounts = '';

    public function home(?string $user = null): string
    {
        $user ??= $this->user;
        return (string)($this->users[$user]['home'] ?? '/home/' . $user);
    }

    public function effectiveUser(): string
    {
        return $this->root ? 'root' : $this->user;
    }

    public function uid(string $user): int
    {
        return (int)($this->users[$user]['uid'] ?? 65534);
    }

    public function gid(string $group): int
    {
        return (int)($this->groups[$group] ?? 65534);
    }

    /** @return list<string> */
    public function userGroups(string $user): array
    {
        return array_values(array_unique(array_map('strval', (array)($this->users[$user]['groups'] ?? []))));
    }

    public function primaryGroup(string $user): string
    {
        $groups = $this->userGroups($user);
        return $groups[0] ?? $user;
    }

    public function abs(string $path): string
    {
        return Lab57Vfs::normalize($path, $this->cwd);
    }

    /** Oprávnění r/w/x pro efektivního uživatele. */
    public function can(array $node, string $perm): bool
    {
        $mode = (int)($node['m'] ?? 0);
        $bit = ['r' => 4, 'w' => 2, 'x' => 1][$perm] ?? 0;
        if ($this->root) {
            if ($perm !== 'x' || ($node['t'] ?? '') === 'd') return true;
            return ($mode & 0111) !== 0;
        }
        $user = $this->user;
        if ((string)($node['u'] ?? '') === $user) return (($mode >> 6) & $bit) !== 0;
        if (in_array((string)($node['g'] ?? ''), $this->userGroups($user), true)) return (($mode >> 3) & $bit) !== 0;
        return ($mode & $bit) !== 0;
    }

    /** Lze projít všemi nadřazenými složkami (právo x)? */
    public function canTraverse(string $abs): bool
    {
        if ($this->root) return true;
        $dir = Lab57Vfs::dirname($abs);
        $parts = $dir === '/' ? [] : explode('/', trim($dir, '/'));
        $path = '';
        foreach ($parts as $part) {
            $path .= '/' . $part;
            $node = $this->fs->get($path);
            if ($node === null) return true;
            if (!$this->can($node, 'x')) return false;
        }
        return true;
    }

    /** Přečte soubor s kontrolou práv. Při chybě vrací null a do $err dá text chyby jako Linux. */
    public function readFile(string $path, ?string &$err = null): ?string
    {
        $abs = $this->abs($path);
        if (!$this->canTraverse($abs)) { $err = 'Permission denied'; return null; }
        $node = $this->fs->get($abs);
        if ($node === null) { $err = 'No such file or directory'; return null; }
        if (($node['t'] ?? '') === 'd') { $err = 'Is a directory'; return null; }
        if (!$this->can($node, 'r')) { $err = 'Permission denied'; return null; }
        if ($abs === '/dev/null') return '';
        return (string)($node['c'] ?? '');
    }

    /** Zapíše soubor (vytvoří nebo přepíše), respektuje práva složky i souboru. */
    public function writeFile(string $path, string $content, bool $append = false, ?string &$err = null): bool
    {
        $abs = $this->abs($path);
        if ($abs === '/dev/null') return true;
        $existing = $append ? strlen((string)($this->fs->get($abs)['c'] ?? '')) : 0;
        if ($existing + strlen($content) > 1048576) {
            $err = 'No space left on device';
            $this->tip(tr('Soubor by byl větší než 1 MB – na tvém cvičném disku pro něj už není místo.'));
            return false;
        }
        if (!$this->canTraverse($abs)) { $err = 'Permission denied'; return false; }
        $node = $this->fs->get($abs);
        if ($node !== null) {
            if (($node['t'] ?? '') === 'd') { $err = 'Is a directory'; return false; }
            if (!$this->can($node, 'w')) { $err = 'Permission denied'; return false; }
            $node['c'] = $append ? (string)($node['c'] ?? '') . $content : $content;
            unset($node['s']);
            $node['mt'] = $this->now;
            $this->fs->set($abs, $node);
            return true;
        }
        $parent = $this->fs->get(Lab57Vfs::dirname($abs));
        if ($parent === null) { $err = 'No such file or directory'; return false; }
        if (($parent['t'] ?? '') !== 'd') { $err = 'Not a directory'; return false; }
        if (!$this->can($parent, 'w') || !$this->can($parent, 'x')) { $err = 'Permission denied'; return false; }
        $owner = $this->effectiveUser();
        // v58: nový soubor (přesměrování, tee, cp …) respektuje umask a dědí skupinu ze složky se setgid.
        $node = ['t' => 'f', 'm' => 0666 & ~(function_exists('lab57_umask') ? lab57_umask($this) : ($owner === 'root' ? 022 : 002)), 'u' => $owner, 'g' => $this->primaryGroup($owner), 'mt' => $this->now, 'c' => $content];
        $this->fs->set($abs, function_exists('lab57_inherit_setgid') ? lab57_inherit_setgid($this, $abs, $node) : $node);
        return true;
    }

    /** Smí efektivní uživatel vytvářet/mazat položky ve složce? */
    public function canModifyDir(string $dirAbs): bool
    {
        $dir = $this->fs->get($dirAbs);
        return $dir !== null && ($dir['t'] ?? '') === 'd' && $this->can($dir, 'w') && $this->can($dir, 'x');
    }

    public function mkfile(string $path, string $content, int $mode = 0644, string $owner = 'root', ?string $group = null, ?int $mt = null, array $extra = []): void
    {
        $abs = Lab57Vfs::normalize($path);
        $this->mkdirp(Lab57Vfs::dirname($abs), 0755, $owner === 'root' ? 'root' : $owner);
        $this->fs->set($abs, array_merge(['t' => 'f', 'm' => $mode, 'u' => $owner, 'g' => $group ?? $this->primaryGroup($owner), 'mt' => $mt ?? ($this->now - 86400 * 3), 'c' => $content], $extra));
    }

    public function mkdirp(string $path, int $mode = 0755, string $owner = 'root', ?string $group = null, ?int $mt = null): void
    {
        $abs = Lab57Vfs::normalize($path);
        if ($abs === '/') return;
        $parts = explode('/', trim($abs, '/'));
        $cur = '';
        foreach ($parts as $part) {
            $cur .= '/' . $part;
            if ($this->fs->exists($cur)) continue;
            $inHome = str_starts_with($cur, $this->home($owner) . '/') && $owner !== 'root';
            $this->fs->set($cur, ['t' => 'd', 'm' => $mode, 'u' => $inHome ? $owner : ($cur === $abs ? $owner : 'root'), 'g' => $inHome || $cur === $abs ? ($group ?? $this->primaryGroup($owner)) : 'root', 'mt' => $mt ?? ($this->now - 86400 * 20)]);
        }
    }

    /** @return array<string,string> */
    public function envAll(): array
    {
        $user = $this->user;
        return array_merge([
            'HOME' => $this->home(),
            'USER' => $user,
            'LOGNAME' => $user,
            'SHELL' => '/bin/bash',
            'PATH' => '/usr/local/bin:/usr/bin:/bin:/usr/local/sbin:/usr/sbin:/sbin',
            'PWD' => $this->cwd,
            'OLDPWD' => $this->oldCwd,
            'HOSTNAME' => $this->hostname,
            'TERM' => 'xterm-256color',
            'LANG' => 'C.UTF-8',
            'EDITOR' => 'nano',
        ], $this->env);
    }

    public function newPid(): int
    {
        $this->pidCounter += 1 + ($this->pidCounter % 3);
        return $this->pidCounter;
    }

    public function tip(string $text): void
    {
        if (!in_array($text, $this->tips, true) && count($this->tips) < 3) $this->tips[] = $text;
    }

    public function journalAdd(string $unit, string $text, string $priority = 'info'): void
    {
        $this->journal[] = ['t' => $this->now, 'unit' => $unit, 'p' => $priority, 'msg' => $text];
        if (count($this->journal) > 400) $this->journal = array_slice($this->journal, -400);
    }

    public function code(string $name = 'CODE'): string
    {
        return (string)($this->codes[$name] ?? 'EDU-TEST-0000');
    }
}

// ---------------------------------------------------------------------------
// Výchozí obraz systému (Debian 12, učebna)
// ---------------------------------------------------------------------------

function lab57_default_users(): array
{
    return [
        'root' => ['uid' => 0, 'gid' => 0, 'home' => '/root', 'shell' => '/bin/bash', 'groups' => ['root'], 'gecos' => 'root'],
        'daemon' => ['uid' => 1, 'gid' => 1, 'home' => '/usr/sbin', 'shell' => '/usr/sbin/nologin', 'groups' => ['daemon'], 'gecos' => 'daemon'],
        'www-data' => ['uid' => 33, 'gid' => 33, 'home' => '/var/www', 'shell' => '/usr/sbin/nologin', 'groups' => ['www-data'], 'gecos' => 'www-data'],
        'student' => ['uid' => 1000, 'gid' => 1000, 'home' => '/home/student', 'shell' => '/bin/bash', 'groups' => ['student', 'sudo', 'lab'], 'gecos' => 'Student,,,'],
        'nobody' => ['uid' => 65534, 'gid' => 65534, 'home' => '/nonexistent', 'shell' => '/usr/sbin/nologin', 'groups' => ['nogroup'], 'gecos' => 'nobody'],
    ];
}

function lab57_default_groups(): array
{
    return ['root' => 0, 'daemon' => 1, 'adm' => 4, 'sudo' => 27, 'www-data' => 33, 'student' => 1000, 'lab' => 1001, 'nogroup' => 65534];
}

/** Síť učebny: počítač → switch → školní router → poskytovatel → internet. */
function lab57_default_net(): array
{
    return [
        'ifaces' => [
            'lo' => ['up' => true, 'ip' => '127.0.0.1', 'prefix' => 8, 'mac' => '00:00:00:00:00:00', 'mtu' => 65536, 'ip6' => '::1/128', 'idx' => 1],
            'eth0' => ['up' => true, 'ip' => '10.0.0.23', 'prefix' => 24, 'mac' => '52:54:00:3a:7c:17', 'mtu' => 1500, 'ip6' => 'fe80::5054:ff:fe3a:7c17/64', 'idx' => 2],
        ],
        'routes' => [
            ['dst' => 'default', 'via' => '10.0.0.1', 'dev' => 'eth0', 'proto' => 'dhcp', 'metric' => 100],
            ['dst' => '10.0.0.0/24', 'dev' => 'eth0', 'proto' => 'kernel', 'scope' => 'link', 'src' => '10.0.0.23'],
        ],
        'nodes' => [
            'pc' => ['ip' => '10.0.0.23', 'name' => 'lab-pc', 'kind' => 'pc', 'label' => 'Tvůj počítač', 'zone' => 'self', 'x' => 60, 'y' => 150],
            'switch' => ['ip' => '', 'name' => 'switch', 'kind' => 'switch', 'label' => 'Switch', 'zone' => 'l2', 'x' => 170, 'y' => 150],
            'router' => ['ip' => '10.0.0.1', 'name' => 'router.skola.test', 'kind' => 'router', 'label' => 'Školní router', 'zone' => 'lan', 'ttl' => 64, 'lat' => 0.6, 'ports' => [53 => 'domain'], 'x' => 290, 'y' => 150],
            'intranet' => ['ip' => '10.0.0.10', 'name' => 'intranet.skola.test', 'kind' => 'server', 'label' => 'Intranet', 'zone' => 'lan', 'ttl' => 64, 'lat' => 0.4, 'ports' => [22 => 'ssh', 80 => 'http'], 'x' => 170, 'y' => 45],
            'printer' => ['ip' => '10.0.0.50', 'name' => 'tiskarna.skola.test', 'kind' => 'printer', 'label' => 'Tiskárna', 'zone' => 'lan', 'ttl' => 255, 'lat' => 1.1, 'ports' => [631 => 'ipp', 9100 => 'jetdirect'], 'x' => 170, 'y' => 255],
            'isp' => ['ip' => '100.64.0.1', 'name' => 'gw.poskytovatel.test', 'kind' => 'router', 'label' => 'Poskytovatel', 'zone' => 'wan', 'ttl' => 255, 'lat' => 6.2, 'x' => 410, 'y' => 150],
            'ix' => ['ip' => '198.51.100.1', 'name' => 'core.ix.test', 'kind' => 'router', 'label' => 'Páteř internetu', 'zone' => 'wan', 'ttl' => 255, 'lat' => 10.8, 'x' => 525, 'y' => 150],
            'dns1' => ['ip' => '1.1.1.1', 'name' => 'one.one.one.one', 'kind' => 'dns', 'label' => 'DNS 1.1.1.1', 'zone' => 'inet', 'ttl' => 64, 'lat' => 12.4, 'ports' => [53 => 'domain', 443 => 'https'], 'dns' => true, 'x' => 660, 'y' => 40],
            'dns2' => ['ip' => '8.8.8.8', 'name' => 'dns.google', 'kind' => 'dns', 'label' => 'DNS 8.8.8.8', 'zone' => 'inet', 'ttl' => 128, 'lat' => 13.1, 'ports' => [53 => 'domain', 443 => 'https'], 'dns' => true, 'x' => 660, 'y' => 110],
            'www' => ['ip' => '203.0.113.10', 'name' => 'www.example.com', 'kind' => 'server', 'label' => 'Web example.com', 'zone' => 'inet', 'ttl' => 64, 'lat' => 17.6, 'ports' => [80 => 'http', 443 => 'https'], 'x' => 660, 'y' => 185],
            'mail' => ['ip' => '203.0.113.25', 'name' => 'mail.example.com', 'kind' => 'server', 'label' => 'Pošta', 'zone' => 'inet', 'ttl' => 64, 'lat' => 18.3, 'ports' => [25 => 'smtp'], 'x' => 660, 'y' => 255],
            'mirror' => ['ip' => '198.51.100.80', 'name' => 'deb.debian.org', 'kind' => 'server', 'label' => 'Zrcadlo balíčků', 'zone' => 'inet', 'ttl' => 64, 'lat' => 15.9, 'ports' => [80 => 'http', 443 => 'https'], 'hidden' => true, 'x' => 600, 'y' => 280],
        ],
        'links' => [['pc', 'switch'], ['switch', 'router'], ['switch', 'intranet'], ['switch', 'printer'], ['router', 'isp'], ['isp', 'ix'], ['ix', 'dns1'], ['ix', 'dns2'], ['ix', 'www'], ['ix', 'mail']],
        'wan_path' => ['router', 'isp', 'ix'],
        'dns' => [
            'router.skola.test' => ['A' => ['10.0.0.1']],
            'intranet.skola.test' => ['A' => ['10.0.0.10']],
            'wiki.skola.test' => ['CNAME' => ['intranet.skola.test']],
            'tiskarna.skola.test' => ['A' => ['10.0.0.50']],
            'skola.test' => ['A' => ['10.0.0.10'], 'NS' => ['router.skola.test'], 'MX' => [[10, 'intranet.skola.test']], 'TXT' => ['"Skolni sit - jen pro vyuku"']],
            'example.com' => ['A' => ['203.0.113.10'], 'AAAA' => ['2001:db8::10'], 'MX' => [[10, 'mail.example.com']], 'NS' => ['ns1.example.com', 'ns2.example.com'], 'TXT' => ['"v=spf1 mx -all"']],
            'www.example.com' => ['A' => ['203.0.113.10'], 'AAAA' => ['2001:db8::10']],
            'mail.example.com' => ['A' => ['203.0.113.25']],
            'ns1.example.com' => ['A' => ['203.0.113.53']],
            'ns2.example.com' => ['A' => ['198.51.100.53']],
            'dns.google' => ['A' => ['8.8.8.8', '8.8.4.4']],
            'one.one.one.one' => ['A' => ['1.1.1.1', '1.0.0.1']],
            'gw.poskytovatel.test' => ['A' => ['100.64.0.1']],
            'core.ix.test' => ['A' => ['198.51.100.1']],
            'deb.debian.org' => ['A' => ['198.51.100.80']],
        ],
        'http' => [
            '10.0.0.10:80' => ['/' => [200, 'text/html', "<!doctype html>\n<html lang=\"cs\"><head><title>Školní intranet</title></head>\n<body><h1>Školní intranet</h1><p>Rozvrh, jídelna a novinky.</p></body></html>\n"]],
            '203.0.113.10:80' => ['/' => [301, 'text/html', "<html><body>Moved Permanently</body></html>\n", ['Location' => 'https://www.example.com/']]],
            '203.0.113.10:443' => ['/' => [200, 'text/html', "<!doctype html>\n<html><head><title>Example Domain</title></head>\n<body><h1>Example Domain</h1><p>Tato doména slouží pro ukázky v dokumentaci.</p></body></html>\n"]],
        ],
        'faults' => [],
        'arp' => ['10.0.0.1' => '00:1a:2b:3c:4d:01'],
    ];
}

function lab57_default_services(bool $nginx): array
{
    $services = [
        'ssh' => ['desc' => 'OpenBSD Secure Shell server', 'active' => true, 'enabled' => true, 'failed' => false, 'ports' => [22], 'bin' => '/usr/sbin/sshd', 'procs' => [['root', 'sshd: /usr/sbin/sshd -D [listener] 0 of 10-100 startups']]],
        'cron' => ['desc' => 'Regular background program processing daemon', 'active' => true, 'enabled' => true, 'failed' => false, 'ports' => [], 'bin' => '/usr/sbin/cron', 'procs' => [['root', '/usr/sbin/cron -f']]],
        'systemd-journald' => ['desc' => 'Journal Service', 'active' => true, 'enabled' => true, 'failed' => false, 'ports' => [], 'static' => true, 'bin' => '/lib/systemd/systemd-journald', 'procs' => [['root', '/lib/systemd/systemd-journald']]],
    ];
    if ($nginx) {
        $services['nginx'] = ['desc' => 'A high performance web server and a reverse proxy server', 'active' => true, 'enabled' => true, 'failed' => false, 'ports' => [80], 'bin' => '/usr/sbin/nginx', 'validate' => 'nginx', 'procs' => [['root', 'nginx: master process /usr/sbin/nginx -g daemon on; master_process on;'], ['www-data', 'nginx: worker process']]];
    }
    return $services;
}

function lab57_nginx_site(string $root = '/var/www/html', int $port = 80): string
{
    return "server {\n    listen {$port} default_server;\n    listen [::]:{$port} default_server;\n\n    root {$root};\n    index index.html;\n\n    server_name _;\n\n    location / {\n        try_files \$uri \$uri/ =404;\n    }\n}\n";
}

/** Vytvoří nový svět se základním obrazem systému. */
function lab57_world_new(string $seed, int $now, array $opts = []): Lab57World
{
    $w = new Lab57World();
    $w->seed = $seed;
    $w->now = $now;
    $w->clockBase = $now;
    $w->bootTime = $now - 3600 * 5 - 1260;
    $w->fs = new Lab57Vfs([]);
    $w->users = lab57_default_users();
    $w->groups = lab57_default_groups();
    $w->hostname = (string)($opts['hostname'] ?? 'lab-pc');
    $w->sudoAllowed = (bool)($opts['sudo'] ?? true);
    if (!$w->sudoAllowed) $w->users['student']['groups'] = ['student', 'lab'];
    $nginx = (bool)($opts['nginx'] ?? true);
    $w->packages = ['bash' => '5.2.15-2+b7', 'coreutils' => '9.1-1', 'grep' => '3.8-5', 'sed' => '4.9-1', 'iproute2' => '6.1.0-3', 'iputils-ping' => '3:20221126-1', 'dnsutils' => '1:9.18.24-1', 'curl' => '7.88.1-10+deb12u5', 'openssh-server' => '1:9.2p1-2+deb12u2', 'cron' => '3.0pl1-162', 'nano' => '7.2-1', 'tree' => '2.1.0-1'];
    if ($nginx) $w->packages['nginx'] = '1.22.1-9';
    if (function_exists('lab58_default_packages')) $w->packages += lab58_default_packages();
    $w->aliases = ['ll' => 'ls -alF', 'la' => 'ls -A', 'l' => 'ls -CF'];

    foreach (['/', '/bin', '/boot', '/dev', '/etc', '/home', '/lib', '/media', '/mnt', '/opt', '/run', '/sbin', '/srv', '/usr', '/usr/bin', '/usr/sbin', '/usr/lib', '/usr/local', '/usr/local/bin', '/usr/share', '/usr/share/doc', '/var', '/var/lib', '/var/log', '/var/www', '/var/cache', '/var/backups'] as $dir) {
        $w->fs->set($dir, ['t' => 'd', 'm' => 0755, 'u' => 'root', 'g' => 'root', 'mt' => $now - 86400 * 40]);
    }
    $w->fs->set('/proc', ['t' => 'd', 'm' => 0555, 'u' => 'root', 'g' => 'root', 'mt' => $w->bootTime]);
    $w->fs->set('/sys', ['t' => 'd', 'm' => 0555, 'u' => 'root', 'g' => 'root', 'mt' => $w->bootTime]);
    $w->fs->set('/tmp', ['t' => 'd', 'm' => 01777, 'u' => 'root', 'g' => 'root', 'mt' => $now - 600]);
    $w->fs->set('/var/tmp', ['t' => 'd', 'm' => 01777, 'u' => 'root', 'g' => 'root', 'mt' => $now - 86400]);
    $w->fs->set('/root', ['t' => 'd', 'm' => 0700, 'u' => 'root', 'g' => 'root', 'mt' => $now - 86400 * 9]);
    $w->mkfile('/root/.bashrc', "# ~/.bashrc správce systému\nexport PS1='\\u@\\h:\\w# '\n", 0644, 'root');
    $w->mkfile('/dev/null', '', 0666, 'root', 'root', $w->bootTime);
    $w->mkfile('/proc/cpuinfo', "processor\t: 0\nvendor_id\t: GenuineIntel\nmodel name\t: Intel(R) Core(TM) i5-10400 CPU @ 2.90GHz\ncpu MHz\t\t: 2904.000\ncache size\t: 12288 KB\ncpu cores\t: 2\n\nprocessor\t: 1\nvendor_id\t: GenuineIntel\nmodel name\t: Intel(R) Core(TM) i5-10400 CPU @ 2.90GHz\ncpu MHz\t\t: 2904.000\ncache size\t: 12288 KB\ncpu cores\t: 2\n", 0444, 'root', 'root', $w->bootTime);
    $w->mkfile('/proc/version', "Linux version 6.1.0-25-amd64 (debian-kernel@lists.debian.org) (gcc-12 (Debian 12.2.0-14) 12.2.0) #1 SMP PREEMPT_DYNAMIC Debian 6.1.106-3 (2024-08-26)\n", 0444, 'root', 'root', $w->bootTime);
    $w->mkfile('/proc/meminfo', "MemTotal:        4028500 kB\nMemFree:         2154320 kB\nMemAvailable:    3107756 kB\nBuffers:           84212 kB\nCached:           918332 kB\nSwapTotal:       1048572 kB\nSwapFree:        1048572 kB\n", 0444, 'root', 'root', $w->bootTime);

    $home = '/home/student';
    $w->fs->set($home, ['t' => 'd', 'm' => 0750, 'u' => 'student', 'g' => 'student', 'mt' => $now - 3600]);
    $w->mkfile($home . '/.bashrc', "# ~/.bashrc: spouští se pro každý interaktivní bash\nHISTSIZE=1000\nalias ll='ls -alF'\nalias la='ls -A'\nalias l='ls -CF'\n", 0644, 'student', 'student', $now - 86400 * 30);
    $w->mkfile($home . '/.profile', "# ~/.profile: spouští se při přihlášení\nif [ -d \"\$HOME/bin\" ] ; then\n    PATH=\"\$HOME/bin:\$PATH\"\nfi\n", 0644, 'student', 'student', $now - 86400 * 30);

    lab57_world_etc($w, $nginx);
    lab57_world_bins($w);
    lab57_world_logs($w, $nginx);
    if ($nginx) {
        $w->mkfile('/var/www/html/index.html', "<!doctype html>\n<html lang=\"cs\">\n<head><meta charset=\"utf-8\"><title>lab-pc</title></head>\n<body>\n  <h1>Vítej na lab-pc</h1>\n  <p>Tuhle stránku posílá webový server nginx.</p>\n</body>\n</html>\n", 0644, 'root', 'root', $now - 86400 * 6);
    }

    $w->services = lab57_default_services($nginx);
    $w->net = lab57_default_net();
    $w->net['nodes']['pc']['name'] = $w->hostname;
    $w->procs = [];
    $w->procs[1] = ['user' => 'root', 'cmd' => '/sbin/init', 'cpu' => 0.0, 'mem' => 0.3, 'vsz' => 167748, 'rss' => 12880, 'tty' => '?', 'stat' => 'Ss', 'start' => $w->bootTime, 'service' => null];
    $w->procs[2] = ['user' => 'root', 'cmd' => '[kthreadd]', 'cpu' => 0.0, 'mem' => 0.0, 'vsz' => 0, 'rss' => 0, 'tty' => '?', 'stat' => 'S', 'start' => $w->bootTime, 'service' => null];
    $pid = 180;
    foreach ($w->services as $name => $svc) {
        if (empty($svc['active'])) continue;
        foreach ((array)$svc['procs'] as [$owner, $cmd]) {
            $w->procs[$pid] = ['user' => $owner, 'cmd' => $cmd, 'cpu' => 0.0, 'mem' => 0.2 + (($pid % 7) / 10), 'vsz' => 15000 + $pid * 13, 'rss' => 4000 + $pid * 3, 'tty' => '?', 'stat' => 'Ss', 'start' => $w->bootTime + 4, 'service' => $name];
            $pid += 137;
        }
    }
    $w->procs[1487] = ['user' => 'student', 'cmd' => '-bash', 'cpu' => 0.0, 'mem' => 0.1, 'vsz' => 8200, 'rss' => 5100, 'tty' => 'pts/0', 'stat' => 'Ss', 'start' => $now - 1800, 'service' => null];

    $w->journalAdd('systemd', 'Started systemd-journald.service - Journal Service.');
    $w->journalAdd('ssh', 'Server listening on 0.0.0.0 port 22.');
    $w->journalAdd('cron', 'INFO (Running @reboot jobs)');
    if ($nginx) $w->journalAdd('nginx', 'Started nginx.service - A high performance web server and a reverse proxy server.');
    return $w;
}

function lab57_world_etc(Lab57World $w, bool $nginx): void
{
    $t = $w->now - 86400 * 12;
    $w->mkfile('/etc/hostname', $w->hostname . "\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/hosts', "127.0.0.1\tlocalhost\n127.0.1.1\t{$w->hostname}\n\n# Školní síť\n10.0.0.10\tintranet.skola.test intranet\n\n::1\tlocalhost ip6-localhost ip6-loopback\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/resolv.conf', "# Vygeneroval DHCP klient\nnameserver 1.1.1.1\nnameserver 8.8.8.8\nsearch skola.test\n", 0644, 'root', 'root', $w->now - 3600);
    lab57_world_write_accounts($w, $t);
    $w->mkfile('/etc/os-release', "PRETTY_NAME=\"Debian GNU/Linux 12 (bookworm)\"\nNAME=\"Debian GNU/Linux\"\nVERSION_ID=\"12\"\nVERSION=\"12 (bookworm)\"\nVERSION_CODENAME=bookworm\nID=debian\nHOME_URL=\"https://www.debian.org/\"\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/issue', "Debian GNU/Linux 12 \\n \\l\n\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/motd', "Vítej v Linux Labu EDUCANET.\nVšechno tady je simulace – klidně zkoušej, nic se doopravdy nerozbije.\nNápověda: help · příručka: man <příkaz>\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/fstab', "# <file system>  <mount point>  <type>  <options>  <dump>  <pass>\nUUID=3f6c2a1e-7b1d-4c55-9a0e-5d7c1e2b8f10  /      ext4  errors=remount-ro  0  1\nUUID=8d2e61b0-11aa-4f0e-b3c2-0a9f7e6d5c43  /home  ext4  defaults           0  2\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/crontab', "SHELL=/bin/sh\nPATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin\n\n# m h dom mon dow user  command\n17 *\t* * *\troot\tcd / && run-parts --report /etc/cron.hourly\n25 6\t* * *\troot\ttest -x /usr/sbin/anacron || run-parts --report /etc/cron.daily\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/apt/sources.list', "deb http://deb.debian.org/debian bookworm main\ndeb http://deb.debian.org/debian-security bookworm-security main\n", 0644, 'root', 'root', $t);
    $w->mkfile('/etc/ssh/sshd_config', "# Konfigurace SSH serveru (zkrácená)\nPort 22\nPermitRootLogin no\nX11Forwarding no\nPrintMotd no\nSubsystem sftp /usr/lib/openssh/sftp-server\n", 0644, 'root', 'root', $t);
    if ($nginx) {
        $w->mkfile('/etc/nginx/nginx.conf', "user www-data;\nworker_processes auto;\npid /run/nginx.pid;\n\nevents {\n    worker_connections 768;\n}\n\nhttp {\n    sendfile on;\n    include /etc/nginx/mime.types;\n    access_log /var/log/nginx/access.log;\n    error_log /var/log/nginx/error.log;\n    include /etc/nginx/sites-enabled/*;\n}\n", 0644, 'root', 'root', $t);
        $w->mkfile('/etc/nginx/mime.types', "types {\n    text/html html htm;\n    text/css css;\n    application/javascript js;\n    image/png png;\n}\n", 0644, 'root', 'root', $t);
        $w->mkfile('/etc/nginx/sites-enabled/default', lab57_nginx_site(), 0644, 'root', 'root', $t);
    }
}

/** /etc/passwd a /etc/group podle uživatelů světa (volá se znovu, když úroveň přidá uživatele). */
function lab57_world_write_accounts(Lab57World $w, ?int $t = null): void
{
    $t ??= $w->now - 86400 * 12;
    $passwd = '';
    foreach ($w->users as $name => $u) {
        $passwd .= $name . ':x:' . $u['uid'] . ':' . $u['gid'] . ':' . $u['gecos'] . ':' . $u['home'] . ':' . $u['shell'] . "\n";
    }
    $w->mkfile('/etc/passwd', $passwd, 0644, 'root', 'root', $t);
    $group = '';
    foreach ($w->groups as $name => $gid) {
        $members = [];
        foreach ($w->users as $uname => $u) {
            if ((int)$u['gid'] !== $gid && in_array($name, (array)$u['groups'], true)) $members[] = $uname;
        }
        $group .= $name . ':x:' . $gid . ':' . implode(',', $members) . "\n";
    }
    $w->mkfile('/etc/group', $group, 0644, 'root', 'root', $t);
}

/** Každý podporovaný příkaz má „binárku“ v /usr/bin, aby fungovalo which, ls /usr/bin i file. */
function lab57_world_bins(Lab57World $w): void
{
    $stub = "\x7fELF\x02\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x03\x00\x3e\x00\x01\x00\x00\x00/lib64/ld-linux-x86-64.so.2\x00GLIBC_2.34\x00.text\x00.data\x00";
    $builtins = ['cd', 'export', 'history', 'help', 'exit', 'mise', 'hint', 'submit', 'check', 'answer', 'reset', 'alias', 'type', 'unset', 'source'];
    $names = function_exists('lab57_command_registry') ? array_keys(lab57_command_registry()) : [];
    $v58 = function_exists('lab58_registry') ? lab58_registry()['commands'] : [];
    foreach ($names as $name) {
        $name = (string)$name;
        $meta = $v58[$name] ?? null;
        if (in_array($name, $builtins, true) || !empty($meta['builtin'])) continue;
        $package = $meta !== null ? $meta['package'] : (function_exists('lab57_command_package') ? lab57_command_package($name) : null);
        if ($package !== null && !isset($w->packages[$package])) continue;
        if ($name === 'nginx' && !isset($w->packages['nginx'])) continue;
        $path = $meta !== null ? (string)$meta['bin'] : ($name === 'nginx' ? '/usr/sbin/' : '/usr/bin/') . $name;
        if ($meta !== null && !$w->fs->isDir(Lab57Vfs::dirname($path))) $w->mkdirp(Lab57Vfs::dirname($path));
        $w->fs->set($path, ['t' => 'f', 'm' => $name === 'sudo' ? 04755 : 0755, 'u' => 'root', 'g' => 'root', 'mt' => $w->now - 86400 * 120, 'c' => $stub . $name . "\x00", 'x' => 'bin:' . $name]);
    }
    foreach (['sshd', 'cron'] as $name) {
        $w->fs->set('/usr/sbin/' . $name, ['t' => 'f', 'm' => 0755, 'u' => 'root', 'g' => 'root', 'mt' => $w->now - 86400 * 120, 'c' => $stub . $name . "\x00"]);
    }
}

function lab57_world_logs(Lab57World $w, bool $nginx): void
{
    $rng = new Lab57Rng($w->seed . '|logs');
    $syslog = [];
    $t = $w->now - 7200;
    $msgs = [
        'systemd[1]: Started apt-daily.service - Daily apt download activities.',
        'CRON[%d]: (root) CMD (cd / && run-parts --report /etc/cron.hourly)',
        'systemd[1]: Starting systemd-tmpfiles-clean.service - Cleanup of Temporary Directories...',
        'systemd[1]: Finished systemd-tmpfiles-clean.service - Cleanup of Temporary Directories.',
        'kernel: [ 1523.114208] e1000: eth0 NIC Link is Up 1000 Mbps Full Duplex',
        'dhclient[512]: bound to 10.0.0.23 -- renewal in 3417 seconds.',
        'systemd[1]: Started session-3.scope - Session 3 of User student.',
    ];
    for ($i = 0; $i < 24; $i++) {
        $t += $rng->int(60, 400);
        $syslog[] = date('M j H:i:s', $t) . ' ' . $w->hostname . ' ' . sprintf((string)$rng->pick($msgs), $rng->int(2000, 9000));
    }
    $w->mkfile('/var/log/syslog', lab57_join($syslog), 0640, 'root', 'adm', $w->now - 60);
    $auth = [];
    $t = $w->now - 5400;
    foreach (['sshd[610]: Server listening on 0.0.0.0 port 22.', 'systemd-logind[498]: New session 3 of user student.', 'sudo:  student : TTY=pts/0 ; PWD=/home/student ; USER=root ; COMMAND=/usr/bin/apt update', 'sudo: pam_unix(sudo:session): session closed for user root'] as $line) {
        $t += 300;
        $auth[] = date('M j H:i:s', $t) . ' ' . $w->hostname . ' ' . $line;
    }
    $w->mkfile('/var/log/auth.log', lab57_join($auth), 0640, 'root', 'adm', $w->now - 900);
    $w->mkfile('/var/log/dpkg.log', date('Y-m-d H:i:s', $w->now - 86400 * 6) . " status installed nginx:amd64 1.22.1-9\n" . date('Y-m-d H:i:s', $w->now - 86400 * 6) . " status installed tree:amd64 2.1.0-1\n", 0644, 'root', 'root', $w->now - 86400 * 6);
    if ($nginx) {
        $w->mkfile('/var/log/nginx/access.log', lab57_gen_access_log($rng, 30, $w->now - 5000), 0640, 'www-data', 'adm', $w->now - 120);
        $w->mkfile('/var/log/nginx/error.log', date('Y/m/d H:i:s', $w->now - 4000) . " [notice] 700#700: signal process started\n", 0640, 'www-data', 'adm', $w->now - 4000);
        $w->fs->set('/var/log/nginx', ['t' => 'd', 'm' => 0755, 'u' => 'root', 'g' => 'adm', 'mt' => $w->now - 120]);
    }
}

// ---------------------------------------------------------------------------
// Generátory dat (deterministické)
// ---------------------------------------------------------------------------

function lab57_gen_access_log(Lab57Rng $r, int $lines, int $start, array $ips = []): string
{
    $ips = $ips !== [] ? $ips : ['10.0.0.31', '10.0.0.44', '10.0.0.23', '10.0.0.57', '10.0.0.31', '10.0.0.62', '10.0.0.31', '10.0.0.44'];
    $paths = [['/index.html', 200, 5120], ['/styl.css', 200, 1874], ['/rozvrh.html', 200, 8410], ['/obrazky/logo.png', 200, 20480], ['/jidelna.html', 200, 3310], ['/stara-stranka.html', 404, 162], ['/admin', 403, 153]];
    $out = [];
    $t = $start;
    for ($i = 0; $i < $lines; $i++) {
        $t += $r->int(5, 140);
        [$path, $status, $size] = $r->pick($paths);
        $method = $r->int(0, 9) === 0 ? 'POST' : 'GET';
        $out[] = sprintf('%s - - [%s] "%s %s HTTP/1.1" %d %d', (string)$r->pick($ips), date('d/M/Y:H:i:s O', $t), $method, $path, $status, $size);
    }
    return lab57_join($out);
}

function lab57_first_names(): array
{
    return ['Adam', 'Bára', 'Cyril', 'Dana', 'Ema', 'Filip', 'Gábina', 'Hynek', 'Ivana', 'Jakub', 'Klára', 'Lukáš', 'Marek', 'Nela', 'Ondra', 'Petra', 'Radek', 'Sára', 'Tomáš', 'Veronika', 'Vojta', 'Zuzana'];
}

function lab57_it_words(): array
{
    return ['server', 'síť', 'paket', 'router', 'kabel', 'switch', 'linux', 'terminál', 'soubor', 'složka', 'procesor', 'paměť', 'disk', 'port', 'adresa', 'brána', 'protokol', 'klávesnice', 'monitor', 'skript'];
}

function lab57_gen_students_csv(Lab57Rng $r, int $rows): string
{
    $names = $r->shuffle(lab57_first_names());
    $out = ['id,jmeno,trida,body'];
    for ($i = 1; $i <= $rows; $i++) {
        $out[] = $i . ',' . $names[($i - 1) % count($names)] . ',' . $r->pick(['3.A', '4.A']) . ',' . $r->int(12, 98);
    }
    return lab57_join($out);
}

function lab57_gen_words(Lab57Rng $r, int $count): string
{
    $words = array_slice(lab57_it_words(), 0, 9);
    $out = [];
    for ($i = 0; $i < $count; $i++) $out[] = (string)$r->pick($words);
    return lab57_join($out);
}

/** Pískoviště: bohatá domovská složka pro volné zkoušení. */
function lab57_world_sandbox_home(Lab57World $w, Lab57Rng $r): void
{
    $h = '/home/student';
    $t = $w->now - 86400 * 2;
    $w->mkfile($h . '/vitej.txt', "Ahoj! Tohle je tvůj cvičný linuxový počítač.\nVšechno je simulace, takže se nemusíš ničeho bát.\n\nZkus třeba:\n  ls -la        vypíše obsah složky i se skrytými soubory\n  cd data       přejde do složky data\n  cat vitej.txt vypíše tento soubor\n  man ls        otevře nápovědu k příkazu ls\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/poznamky.txt', "Linux je operační systém.\nV terminálu píšu příkazy.\nTODO: naučit se příkaz grep\nSíť spojuje počítače.\nTODO: zjistit svoji IP adresu\nLinux běží na serverech i v telefonech.\nSložky oddělujeme lomítkem /.\nTODO: vyzkoušet rouru |\nSíť má router a switch.\nKonec poznámek.\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/.skryty_tip.txt', "Skryté soubory začínají tečkou. Uvidíš je příkazem ls -a.\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/projekty/web/index.html', "<!doctype html>\n<html lang=\"cs\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Můj web</title>\n  <link rel=\"stylesheet\" href=\"styl.css\">\n</head>\n<body>\n  <h1>Můj první web</h1>\n</body>\n</html>\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/projekty/web/styl.css', "body {\n  font-family: sans-serif;\n  color: #1d2733;\n}\nh1 {\n  color: #007a87;\n}\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/projekty/skripty/zaloha.sh', "#!/bin/bash\n# Zkopíruje web do zálohy\nmkdir -p /tmp/zaloha\ncp -r ~/projekty/web /tmp/zaloha/\necho \"Záloha hotová: /tmp/zaloha/web\"\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/projekty/skripty/pozdrav.sh', "#!/bin/bash\necho \"Ahoj, $(whoami)! Dnes je $(date +%d.%m.%Y).\"\n", 0755, 'student', 'student', $t);
    $w->mkfile($h . '/data/zaci.csv', lab57_gen_students_csv($r, 12), 0644, 'student', 'student', $t);
    $w->mkfile($h . '/data/access.log', lab57_gen_access_log($r, 40, $w->now - 9000), 0644, 'student', 'student', $t);
    $numbers = [];
    for ($i = 0; $i < 20; $i++) $numbers[] = (string)$r->int(1, 250);
    $w->mkfile($h . '/data/cisla.txt', lab57_join($numbers), 0644, 'student', 'student', $t);
    $w->mkfile($h . '/data/slova.txt', lab57_gen_words($r, 30), 0644, 'student', 'student', $t);
    $w->mkfile($h . '/data/zprava.b64', base64_encode("Gratuluju, dekódoval(a) jsi zprávu v base64!\n") . "\n", 0644, 'student', 'student', $t);
    $w->mkfile($h . '/obrazky/logo.png', "\x89PNG\r\n\x1a\n\x00\x00\x00\rIHDR\x00\x00\x00\x40\x00\x00\x00\x40\x08\x06\x00\x00\x00" . $r->bytes(180) . "tEXtAutor\x00EDUCANET\x00" . $r->bytes(120) . "IEND\xaeB`\x82", 0644, 'student', 'student', $t);
}
