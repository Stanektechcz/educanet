<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – SSH klíče a vzdálený přístup (LAB-02).
 *
 * ssh-keygen, ssh, scp, ssh-copy-id. Klíče i otisky hostitelů jsou FIKTIVNÍ deterministická
 * data – žádná skutečná kryptografie, nic se nespouští a nic nejde do sítě. Vzdálené hosty
 * popisuje $w->ext['ssh'] (malý souborový strom, uživatelé, authorized_keys, otisk hostitele).
 * „Spojení“ jen projde simulovaný síťový graf (jako ping/traceroute) kvůli animaci topologie.
 */

const LAB58_SSH_MAXHOP = 2;

// ---------------------------------------------------------------------------
// Fiktivní klíče a otisky
// ---------------------------------------------------------------------------

function lab58_ssh_len(string $s): string
{
    return pack('N', strlen($s)) . $s;
}

/** Deterministický „klíč“ (fiktivní bajty) ze semínka. @return array{algo:string,bits:int,priv:string,pub:string,field:string,blob:string,fp:string} */
function lab58_ssh_keypair(string $seed, string $type = 'ed25519', int $bits = 0, string $comment = ''): array
{
    $type = strtolower($type);
    if (!in_array($type, ['ed25519', 'rsa', 'ecdsa'], true)) $type = 'ed25519';
    $algo = $type === 'ed25519' ? 'ssh-ed25519' : ($type === 'ecdsa' ? 'ecdsa-sha2-nistp256' : 'ssh-rsa');
    $keyBytes = $type === 'ed25519' ? 32 : ($type === 'ecdsa' ? 65 : max(256, (int)ceil(($bits ?: 3072) / 8)));
    $raw = '';
    for ($i = 0; strlen($raw) < $keyBytes; $i++) $raw .= hash('sha256', $seed . '|k|' . $i, true);
    $raw = substr($raw, 0, $keyBytes);
    $blob = lab58_ssh_len($algo) . lab58_ssh_len($raw);
    $field = base64_encode($blob);
    $bitsOut = $type === 'ed25519' ? 256 : ($type === 'ecdsa' ? 256 : ($bits ?: 3072));
    $pub = $algo . ' ' . $field . ($comment !== '' ? ' ' . $comment : '');
    $body = chunk_split(base64_encode('openssh-key-v1' . "\x00" . $blob . hash('sha256', $seed . '|priv', true)), 70, "\n");
    $priv = "-----BEGIN OPENSSH PRIVATE KEY-----\n" . $body . "-----END OPENSSH PRIVATE KEY-----\n";
    return ['algo' => $algo, 'bits' => $bitsOut, 'priv' => $priv, 'pub' => $pub . "\n", 'field' => $field, 'blob' => $blob, 'fp' => lab58_ssh_fp_of_blob($blob)];
}

function lab58_ssh_fp_of_blob(string $blob): string
{
    return 'SHA256:' . rtrim(base64_encode(hash('sha256', $blob, true)), '=');
}

/** Otisk z řádku veřejného klíče „algo base64 [komentář]“. */
function lab58_ssh_fp_of_pub(string $pubLine): ?string
{
    $parts = preg_split('/\s+/', trim($pubLine)) ?: [];
    if (count($parts) < 2) return null;
    $blob = base64_decode($parts[1], true);
    if ($blob === false) return null;
    return lab58_ssh_fp_of_blob($blob);
}

function lab58_ssh_key_type_label(string $algo): string
{
    return match ($algo) { 'ssh-ed25519' => 'ED25519', 'ecdsa-sha2-nistp256' => 'ECDSA', 'ssh-rsa' => 'RSA', default => strtoupper($algo) };
}

/** Deterministický randomart (drunken bishop). */
function lab58_ssh_randomart(string $blob, string $algo, int $bits): string
{
    $w = 17;
    $h = 9;
    $grid = array_fill(0, $h, array_fill(0, $w, 0));
    $x = intdiv($w, 2);
    $y = intdiv($h, 2);
    $digest = hash('sha256', $blob, true);
    for ($i = 0, $n = strlen($digest); $i < $n; $i++) {
        $b = ord($digest[$i]);
        for ($s = 0; $s < 4; $s++) {
            $dir = ($b >> ($s * 2)) & 3;
            $x = max(0, min($w - 1, $x + (($dir & 1) ? 1 : -1)));
            $y = max(0, min($h - 1, $y + (($dir & 2) ? 1 : -1)));
            $grid[$y][$x]++;
        }
    }
    $chars = ' .o+=*BOX@%&#/^';
    $grid[intdiv($h, 2)][intdiv($w, 2)] = -1; // S
    $grid[$y][$x] = -2; // E
    $title = '[' . lab58_ssh_key_type_label($algo) . ' ' . $bits . ']';
    $top = '+' . str_pad($title, $w, '-', STR_PAD_BOTH) . '+';
    $out = [$top];
    foreach ($grid as $row) {
        $line = '|';
        foreach ($row as $v) $line .= $v === -1 ? 'S' : ($v === -2 ? 'E' : $chars[min($v, strlen($chars) - 1)]);
        $out[] = $line . '|';
    }
    $out[] = '+' . str_pad('[SHA256]', $w, '-', STR_PAD_BOTH) . '+';
    return implode("\n", $out);
}

// ---------------------------------------------------------------------------
// Lokální identita, ~/.ssh/config, known_hosts
// ---------------------------------------------------------------------------

/** @return array{path:string,pub:string,fp:?string,exists:bool,perms_ok:bool,mode:int} */
function lab58_ssh_identity(Lab57World $w, ?string $explicit): array
{
    $home = $w->home();
    // ~ v IdentityFile (z ~/.ssh/config) shell nerozbalí – udělá to ssh sám.
    if ($explicit !== null && ($explicit === '~' || str_starts_with($explicit, '~/'))) $explicit = $home . substr($explicit, 1);
    $candidates = $explicit !== null ? [$explicit] : [$home . '/.ssh/id_ed25519', $home . '/.ssh/id_ecdsa', $home . '/.ssh/id_rsa'];
    foreach ($candidates as $path) {
        $abs = $w->abs($path);
        $node = $w->fs->get($abs);
        if ($node === null) continue;
        $mode = (int)($node['m'] ?? 0);
        $pubNode = $w->fs->get($abs . '.pub');
        $pub = $pubNode !== null ? (string)($pubNode['c'] ?? '') : '';
        return ['path' => $abs, 'pub' => $pub, 'fp' => $pub !== '' ? lab58_ssh_fp_of_pub($pub) : lab58_ssh_fp_of_blob((string)($node['c'] ?? '')), 'exists' => true, 'perms_ok' => ($mode & 0077) === 0, 'mode' => $mode];
    }
    return ['path' => $w->abs($candidates[0]), 'pub' => '', 'fp' => null, 'exists' => false, 'perms_ok' => true, 'mode' => 0];
}

/** @return array<string,array<string,string>> alias => nastavení (hostname,user,port,identityfile) */
function lab58_ssh_config(Lab57World $w): array
{
    $content = (string)($w->fs->get($w->home() . '/.ssh/config')['c'] ?? '');
    if ($content === '') return [];
    $hosts = [];
    $current = null;
    foreach (explode("\n", $content) as $line) {
        $line = trim((string)preg_replace('/#.*/', '', $line));
        if ($line === '') continue;
        if (preg_match('/^Host\s+(.+)$/i', $line, $m) === 1) { $current = trim($m[1]); $hosts[$current] = []; continue; }
        if ($current === null) continue;
        if (preg_match('/^(\S+)\s+(.+)$/', $line, $m) === 1) $hosts[$current][strtolower($m[1])] = trim($m[2]);
    }
    return $hosts;
}

/** @return array{host:string,user:?string,port:?int,identity:?string} přepis aliasem z ~/.ssh/config */
function lab58_ssh_apply_config(Lab57World $w, string $host, ?string $user, ?int $port): array
{
    $cfg = lab58_ssh_config($w);
    $out = ['host' => $host, 'user' => $user, 'port' => $port, 'identity' => null];
    foreach ([$host, '*'] as $key) {
        if (!isset($cfg[$key])) continue;
        $c = $cfg[$key];
        if ($key === $host && isset($c['hostname'])) $out['host'] = $c['hostname'];
        if ($out['user'] === null && isset($c['user'])) $out['user'] = $c['user'];
        if ($out['port'] === null && isset($c['port'])) $out['port'] = (int)$c['port'];
        if ($out['identity'] === null && isset($c['identityfile'])) $out['identity'] = $c['identityfile'];
    }
    return $out;
}

function lab58_ssh_known_hosts_path(Lab57World $w): string
{
    return $w->home() . '/.ssh/known_hosts';
}

/** @return array<string,string> host => base64 pole klíče v known_hosts */
function lab58_ssh_known_hosts(Lab57World $w): array
{
    $out = [];
    foreach (explode("\n", (string)($w->fs->get(lab58_ssh_known_hosts_path($w))['c'] ?? '')) as $line) {
        $parts = preg_split('/\s+/', trim($line)) ?: [];
        if (count($parts) < 3) continue;
        foreach (explode(',', $parts[0]) as $name) $out[strtolower($name)] = $parts[2];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Hosté v $w->ext['ssh']
// ---------------------------------------------------------------------------

function lab58_ssh_host_spec(Lab57World $w, string $host): ?array
{
    $host = strtolower($host);
    $hosts = (array)($w->ext['ssh']['hosts'] ?? []);
    if (isset($hosts[$host])) return $hosts[$host] + ['name' => $host];
    foreach ($hosts as $name => $spec) {
        if ((string)($spec['ip'] ?? '') === $host) return $spec + ['name' => (string)$name];
    }
    return null;
}

/** Aktuální otisk hostitelského klíče (fiktivní, deterministický ze semínka hosta). */
function lab58_ssh_hostkey(array $spec): array
{
    $seed = (string)($spec['host_seed'] ?? ('hostkey|' . ($spec['name'] ?? 'host')));
    return lab58_ssh_keypair($seed, (string)($spec['host_type'] ?? 'ed25519'), 0, '');
}

/** Autorizované otisky pro uživatele na hostu (spec + nahrané přes scp/ssh-copy-id). */
function lab58_ssh_authorized(Lab57World $w, array $spec, string $user): array
{
    $auth = array_values((array)($spec['users'][$user]['authorized'] ?? []));
    $host = (string)($spec['name'] ?? '');
    foreach ((array)($w->ext['ssh']['authorized'][$host][$user] ?? []) as $fp) $auth[] = (string)$fp;
    return array_values(array_unique($auth));
}

// ---------------------------------------------------------------------------
// Vzdálený svět (malý strom hosta) + spuštění příkazu
// ---------------------------------------------------------------------------

function lab58_ssh_remote_world(Lab57World $w, array $spec, string $user, int $now): Lab57World
{
    $host = (string)($spec['name'] ?? 'server');
    $seed = (string)($spec['seed'] ?? ($w->seed . '|sshremote|' . $host));
    $rw = lab57_world_new($seed, $now, ['hostname' => (string)($spec['hostname'] ?? explode('.', $host)[0]), 'nginx' => false, 'sudo' => false]);
    if (!isset($rw->users[$user])) {
        $uid = 1000 + (abs(crc32($user)) % 500);
        $rw->groups[$user] ??= $uid;
        $rw->users[$user] = ['uid' => $uid, 'gid' => $uid, 'home' => (string)($spec['users'][$user]['home'] ?? '/home/' . $user), 'shell' => '/bin/bash', 'groups' => [$user], 'gecos' => $user];
        lab57_world_write_accounts($rw);
    }
    $rw->user = $user;
    $home = $rw->home($user);
    $rw->cwd = $home;
    if (!$rw->fs->isDir($home)) $rw->mkdirp($home, 0750, $user, $user);
    foreach ((array)($spec['files'] ?? []) as $path => $file) {
        $content = is_array($file) ? (string)($file['content'] ?? '') : (string)$file;
        $mode = is_array($file) ? (int)($file['mode'] ?? 0644) : 0644;
        $owner = is_array($file) ? (string)($file['owner'] ?? $user) : $user;
        $abs = str_starts_with($path, '~') ? $home . substr($path, 1) : $path;
        $rw->mkfile(Lab57Vfs::normalize($abs), $content, $mode, isset($rw->users[$owner]) ? $owner : 'root', null, $now - 3600);
    }
    foreach ((array)($w->ext['ssh']['uploads'][$host][$user] ?? []) as $path => $content) {
        $abs = str_starts_with((string)$path, '~') ? $home . substr((string)$path, 1) : (string)$path;
        $rw->mkfile(Lab57Vfs::normalize($abs), (string)$content, 0644, $user, null, $now);
    }
    $rw->fs->seal();
    return $rw;
}

// ---------------------------------------------------------------------------
// Reachability (síťový graf) + net effect pro animaci
// ---------------------------------------------------------------------------

function lab58_ssh_reach(Lab57World $w, string $host, int $port): array
{
    $conn = lab57_net_connect($w, $host, $port);
    lab57_net_effect($w, 'ssh', ($conn['path'] ?? []) + ['ok' => $conn['ok']], 'ssh ' . $host);
    return $conn;
}

// ---------------------------------------------------------------------------
// ssh-keygen
// ---------------------------------------------------------------------------

function lab58_cmd_ssh_keygen(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, , $error] = lab57_getopt(array_slice($argv, 1), 't:b:f:N:C:lR:yq', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    // -R host: odstraní hosta z known_hosts
    if (isset($o['R'])) {
        $host = strtolower((string)$o['R']);
        $khPath = isset($o['f']) ? (string)$o['f'] : lab58_ssh_known_hosts_path($w);
        $content = (string)($w->fs->get($w->abs($khPath))['c'] ?? '');
        $khAbs = $w->abs($khPath);
        $kept = [];
        $found = [];
        foreach (explode("\n", $content) as $no => $line) {
            if (trim($line) === '') continue;
            $names = array_map('strtolower', explode(',', (string)(preg_split('/\s+/', trim($line))[0] ?? '')));
            if (in_array($host, $names, true)) { $found[] = $no + 1; continue; }
            $kept[] = $line;
        }
        if ($found === []) { $p->err('Host ' . $host . ' not found in ' . $khAbs . "\n"); return 0; }
        $err = null;
        $w->writeFile($khAbs . '.old', $content, false, $err);
        $w->writeFile($khAbs, lab57_join($kept), false, $err);
        foreach ($found as $no) $p->line('# Host ' . $host . ' found: line ' . $no);
        $p->line($khAbs . ' updated.');
        $p->line('Original contents retained as ' . $khAbs . '.old');
        return 0;
    }
    // -l -f: otisk existujícího klíče
    if (isset($o['l'])) {
        $file = (string)($o['f'] ?? ($w->home() . '/.ssh/id_ed25519.pub'));
        $err = null;
        $data = $w->readFile($file, $err);
        if ($data === null) { $p->err($file . ': ' . $err . "\n"); return 255; }
        $fp = lab58_ssh_fp_of_pub($data) ?? lab58_ssh_fp_of_blob($data);
        $parts = preg_split('/\s+/', trim($data)) ?: [];
        $algo = str_contains($data, ' ') ? (string)($parts[0] ?? 'ssh-ed25519') : 'ssh-ed25519';
        $comment = count($parts) >= 3 ? implode(' ', array_slice($parts, 2)) : ($w->user . '@' . $w->hostname);
        $bits = $algo === 'ssh-rsa' ? 3072 : 256;
        $p->line($bits . ' ' . $fp . ' ' . $comment . ' (' . lab58_ssh_key_type_label($algo) . ')');
        return 0;
    }
    $type = strtolower((string)($o['t'] ?? 'rsa'));
    if (!in_array($type, ['ed25519', 'rsa', 'ecdsa'], true)) { $p->err("unknown key type $type\n"); $w->tip(tr('Podporované typy klíčů: ed25519 (doporučeno), rsa, ecdsa. Např. ssh-keygen -t ed25519.')); return 1; }
    $bits = isset($o['b']) ? (int)$o['b'] : ($type === 'rsa' ? 3072 : 0);
    $comment = (string)($o['C'] ?? ($w->user . '@' . $w->hostname));
    $file = (string)($o['f'] ?? ($w->home() . '/.ssh/id_' . $type));
    $abs = $w->abs($file);
    if ($w->fs->exists($abs)) {
        $p->line($abs . ' already exists.');
        $p->err("Overwrite (y/n)? \n");
        $p->err("Key generation aborted.\n");
        $w->tip(tr('Klíč už existuje. Zvol jiný název volbou -f, nebo starý nejdřív smaž (rm {file} {file}.pub).', ['file' => $file]));
        return 1;
    }
    $dir = Lab57Vfs::dirname($abs);
    if (!$w->fs->isDir($dir)) $w->mkdirp($dir, 0700, $w->effectiveUser());
    $seed = $w->seed . '|sshkeygen|' . $abs . '|' . $type . '|' . $comment;
    $kp = lab58_ssh_keypair($seed, $type, $bits, $comment);
    $err = null;
    $p->line('Generating public/private ' . $type . ' key pair.');
    if (!$w->writeFile($abs, $kp['priv'], false, $err)) { $p->err("Saving key \"$abs\" failed: $err\n"); return 1; }
    $priv = $w->fs->get($abs);
    if ($priv !== null) { $priv['m'] = 0600; $w->fs->set($abs, $priv); }
    $w->writeFile($abs . '.pub', $kp['pub'], false, $err);
    $pub = $w->fs->get($abs . '.pub');
    if ($pub !== null) { $pub['m'] = 0644; $w->fs->set($abs . '.pub', $pub); }
    if ((string)($o['N'] ?? '') !== '') $w->tip(tr('Přístupová fráze (-N) chrání soukromý klíč, kdyby ho někdo získal. V laboratoři ji nepotřebuješ – klíč je jen fiktivní.'));
    $p->line('Your identification has been saved in ' . $abs);
    $p->line('Your public key has been saved in ' . $abs . '.pub');
    $p->line('The key fingerprint is:');
    $p->line($kp['fp'] . ' ' . $comment);
    $p->line("The key's randomart image is:");
    $p->out(lab58_ssh_randomart($kp['blob'], $kp['algo'], $kp['bits']) . "\n");
    return 0;
}

// ---------------------------------------------------------------------------
// ssh
// ---------------------------------------------------------------------------

/** @return array{user:?string,host:string,port:?int,identity:?string,command:string}|null */
function lab58_ssh_parse(array $args): ?array
{
    $user = null;
    $host = null;
    $port = null;
    $identity = null;
    $command = [];
    $n = count($args);
    for ($i = 0; $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($host === null && str_starts_with($a, '-')) {
            if ($a === '-p') { $port = (int)($args[++$i] ?? 0); continue; }
            if (str_starts_with($a, '-p')) { $port = (int)substr($a, 2); continue; }
            if ($a === '-i') { $identity = (string)($args[++$i] ?? ''); continue; }
            if (str_starts_with($a, '-i')) { $identity = substr($a, 2); continue; }
            if ($a === '-o' || $a === '-F' || $a === '-l') { $i++; continue; }
            continue; // ostatní přepínače (-v, -t, -T…) ignoruj
        }
        if ($host === null) {
            if (str_contains($a, '@')) [$user, $host] = explode('@', $a, 2);
            else $host = $a;
            continue;
        }
        $command[] = $a;
    }
    if ($host === null || $host === '') return null;
    return ['user' => $user, 'host' => $host, 'port' => $port, 'identity' => $identity, 'command' => implode(' ', $command)];
}

function lab58_cmd_ssh(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $parsed = lab58_ssh_parse(array_slice($argv, 1));
    if ($parsed === null) { $p->err("usage: ssh [-p port] [-i identity_file] [user@]hostname [command]\n"); return 255; }
    $cfg = lab58_ssh_apply_config($w, $parsed['host'], $parsed['user'], $parsed['port']);
    $host = $cfg['host'];
    $user = $cfg['user'] ?? $w->user;
    $port = $cfg['port'] ?? ($parsed['port'] ?? 22);
    $identity = $parsed['identity'] ?? $cfg['identity'];

    if (($GLOBALS['lab58_ssh_depth'] ?? 0) >= LAB58_SSH_MAXHOP) { $p->err("ssh: too many nested connections\n"); return 255; }

    $conn = lab58_ssh_reach($w, $host, $port);
    if (!$conn['ok']) return lab58_ssh_conn_error($p, $conn, $host, $port);
    $spec = lab58_ssh_host_spec($w, $host);
    if ($spec === null) {
        // Dosažitelný, ale bez účtu v laboratoři – přihlášení neprojde.
        $p->err("$user@$host: Permission denied (publickey,password).\n");
        $w->tip(tr('Na tenhle počítač se v laboratoři nedá přihlásit (nemá cvičný účet). Použij hosta ze zadání úlohy.'));
        return 255;
    }

    $hk = lab58_ssh_hostkey($spec);
    $verdict = lab58_ssh_hostkey_check($p, $host, $hk, (string)($conn['ip'] ?? $host));
    if ($verdict !== 0) return $verdict;

    $id = lab58_ssh_identity($w, $identity);
    $authorized = lab58_ssh_authorized($w, $spec, $user);
    $keyOk = false;
    if ($id['exists']) {
        if (!$id['perms_ok']) {
            lab58_ssh_bad_perms($p, $id);
        } elseif ($id['fp'] !== null && in_array($id['fp'], $authorized, true)) {
            $keyOk = true;
        }
    }
    if (!$keyOk) return lab58_ssh_auth_fail($p, $spec, $user, $host, $id);

    // Autentizace klíčem prošla.
    $command = trim($parsed['command']);
    if ($command === '') {
        $p->err("PTY allocation request failed – interaktivní shell v laboratoři není.\n");
        $w->tip(tr("Terminál je neinteraktivní. Spusť příkaz rovnou: ssh {user}@{host} 'ls -la'  (nebo 'cat kod.txt').", ['user' => $user, 'host' => $host]));
        return 0;
    }
    $GLOBALS['lab58_ssh_depth'] = ($GLOBALS['lab58_ssh_depth'] ?? 0) + 1;
    try {
        $rw = lab58_ssh_remote_world($w, $spec, $user, $w->now);
        $run = lab57_run_line($rw, $command);
    } finally {
        $GLOBALS['lab58_ssh_depth'] = ($GLOBALS['lab58_ssh_depth'] ?? 1) - 1;
    }
    foreach ($run['chunks'] as [$fd, $text]) { $fd === 1 ? $p->out($text) : $p->err($text); }
    return (int)$run['exit'];
}

function lab58_ssh_conn_error(Lab57Proc $p, array $conn, string $host, int $port): int
{
    $w = $p->w;
    switch ((string)$conn['error']) {
        case 'resolve':
        case 'nxdomain':
            $p->err("ssh: Could not resolve hostname $host: Name or service not known\n");
            $w->tip(tr('Jméno „{host}“ nešlo přeložit na IP. Zkontroluj překlep nebo /etc/hosts.', ['host' => $host]));
            return 255;
        case 'refused':
            $p->err("ssh: connect to host $host port $port: Connection refused\n");
            $w->tip(tr('Na portu {port} nikdo neposlouchá. Běží na cíli SSH server? Zkus nc -zv {host} {port}.', ['port' => $port, 'host' => $host]));
            return 255;
        case 'unreachable':
            $p->err("ssh: connect to host $host port $port: Network is unreachable\n");
            return 255;
        default:
            $p->err("ssh: connect to host $host port $port: Connection timed out\n");
            $w->tip(tr('Spojení vypršelo. Ověř dostupnost: ping {host} a traceroute {host}.', ['host' => $host]));
            return 255;
    }
}

function lab58_ssh_hostkey_check(Lab57Proc $p, string $host, array $hk, string $ip = ''): int
{
    $w = $p->w;
    $known = lab58_ssh_known_hosts($w);
    $key = strtolower($host);
    if (isset($known[$key])) {
        $knownFp = lab58_ssh_fp_of_blob(base64_decode($known[$key], true) ?: $known[$key]);
        if ($knownFp !== $hk['fp']) {
            $p->err("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n");
            $p->err("@    WARNING: REMOTE HOST IDENTIFICATION HAS CHANGED!     @\n");
            $p->err("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n");
            $p->err("IT IS POSSIBLE THAT SOMEONE IS DOING SOMETHING NASTY!\n");
            $p->err("Someone could be eavesdropping on you right now (man-in-the-middle attack)!\n");
            $p->err("It is also possible that a host key has just been changed.\n");
            $p->err('The fingerprint for the ' . lab58_ssh_key_type_label($hk['algo']) . " key sent by the remote host is\n" . $hk['fp'] . ".\n");
            $khPath = lab58_ssh_known_hosts_path($w);
            $lineNo = 1;
            foreach (explode("\n", (string)($w->fs->get($khPath)['c'] ?? '')) as $i => $row) {
                $names = array_map('strtolower', explode(',', (string)(preg_split('/\s+/', trim($row))[0] ?? '')));
                if (in_array($key, $names, true)) { $lineNo = $i + 1; break; }
            }
            $p->err('Please contact your system administrator.' . "\n");
            $p->err('Add correct host key in ' . $khPath . " to get rid of this message.\n");
            $p->err('Offending ' . lab58_ssh_key_type_label($hk['algo']) . ' key in ' . $khPath . ':' . $lineNo . "\n");
            $p->err("  remove with:\n  ssh-keygen -f \"" . $khPath . '" -R "' . $host . "\"\n");
            $p->err('Host key for ' . $host . " has changed and you have requested strict checking.\n");
            $p->err("Host key verification failed.\n");
            $w->tip(tr('Otisk serveru se změnil oproti tomu v known_hosts. V laboratoři to znamená, že úroveň server přeinstalovala. Když je to očekávané, starý záznam smaž: ssh-keygen -R {host} a přihlas se znovu.', ['host' => $host]));
            return 255;
        }
        return 0;
    }
    // TOFU: první připojení – v laboratoři automaticky přijmi a zapiš.
    $p->err("The authenticity of host '$host (" . $ip . ")' can't be established.\n");
    $p->err(lab58_ssh_key_type_label($hk['algo']) . ' key fingerprint is ' . $hk['fp'] . ".\n");
    $p->err("This key is not known by any other names.\n");
    $p->err("Warning: Permanently added '$host' (" . lab58_ssh_key_type_label($hk['algo']) . ") to the list of known hosts.\n");
    $line = $host . ' ' . $hk['algo'] . ' ' . $hk['field'] . "\n";
    $err = null;
    $w->writeFile(lab58_ssh_known_hosts_path($w), $line, true, $err);
    $kh = $w->fs->get(lab58_ssh_known_hosts_path($w));
    if ($kh !== null) { $kh['m'] = 0644; $w->fs->set(lab58_ssh_known_hosts_path($w), $kh); }
    return 0;
}

function lab58_ssh_bad_perms(Lab57Proc $p, array $id): void
{
    $w = $p->w;
    $p->err("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n");
    $p->err("@         WARNING: UNPROTECTED PRIVATE KEY FILE!          @\n");
    $p->err("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@\n");
    $p->err('Permissions ' . sprintf('%04o', $id['mode'] & 07777) . " for '" . $id['path'] . "' are too open.\n");
    $p->err("It is required that your private key files are NOT accessible by others.\n");
    $p->err("This private key will be ignored.\n");
    $p->err('Load key "' . $id['path'] . "\": bad permissions\n");
    $w->tip(tr('Soukromý klíč smí číst jen ty. Oprav práva: chmod 600 {path} (a chmod 700 {dir}).', ['path' => $id['path'], 'dir' => Lab57Vfs::dirname($id['path'])]));
}

function lab58_ssh_auth_fail(Lab57Proc $p, array $spec, string $user, string $host, array $id): int
{
    $w = $p->w;
    $passwordAuth = !empty($spec['users'][$user]['password']);
    $methods = $passwordAuth ? 'publickey,password' : 'publickey';
    $p->err("$user@$host: Permission denied ($methods).\n");
    if (!$id['exists']) {
        $w->tip(tr('Nemáš klíč, kterým by tě server přijal. Vygeneruj si ho (ssh-keygen -t ed25519) a nahraj na server (ssh-copy-id {user}@{host}).', ['user' => $user, 'host' => $host]));
    } elseif ($passwordAuth) {
        $w->tip(tr('Server tvůj klíč nezná a heslo v neinteraktivním terminálu zadat nejde. Nahraj svůj veřejný klíč: ssh-copy-id {user}@{host}.', ['user' => $user, 'host' => $host]));
    } else {
        $w->tip(tr('Server přijímá jen klíče. Ověř, že tvůj veřejný klíč je v ~/.ssh/authorized_keys na serveru (ssh-copy-id {user}@{host}).', ['user' => $user, 'host' => $host]));
    }
    return 255;
}

// ---------------------------------------------------------------------------
// scp
// ---------------------------------------------------------------------------

/** @return array{remote:bool,user:?string,host:?string,path:string} */
function lab58_scp_endpoint(string $arg): array
{
    if (preg_match('/^([^@\/]+@)?([a-zA-Z0-9._-]+):(.*)$/', $arg, $m) === 1 && !str_starts_with($arg, '.') && !str_starts_with($arg, '/')) {
        return ['remote' => true, 'user' => $m[1] !== '' ? rtrim($m[1], '@') : null, 'host' => $m[2], 'path' => $m[3] === '' ? '.' : $m[3]];
    }
    return ['remote' => false, 'user' => null, 'host' => null, 'path' => $arg];
}

function lab58_cmd_scp(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'rP:i:pqv', ['recursive' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (count($ops) < 2) { $p->err("usage: scp [-r] [-P port] [-i identity] source ... target\n"); return 1; }
    $port = isset($o['P']) ? (int)$o['P'] : 22;
    $identity = isset($o['i']) ? (string)$o['i'] : null;
    $dst = lab58_scp_endpoint((string)array_pop($ops));
    $status = 0;
    foreach ($ops as $srcArg) {
        $src = lab58_scp_endpoint((string)$srcArg);
        if ($src['remote'] && $dst['remote']) { $p->err("scp: kopírování mezi dvěma vzdálenými hosty simulace nepodporuje\n"); return 1; }
        if (!$src['remote'] && !$dst['remote']) { $p->err("scp: alespoň jeden konec musí být vzdálený (user@host:cesta)\n"); $w->tip(tr('Na lokální kopírování použij cp. scp přenáší mezi počítači: scp soubor user@host:~/')); $status = 1; continue; }
        $status = max($status, $src['remote'] ? lab58_scp_download($p, $src, $dst, $port, $identity) : lab58_scp_upload($p, $src, $dst, $port, $identity));
    }
    return $status;
}

/** @return array{ok:bool,spec:?array,user:string}|int připraví ověřené spojení, nebo vrátí exit kód chyby */
function lab58_scp_connect(Lab57Proc $p, string $host, ?string $user, int $port, ?string $identity): array|int
{
    $w = $p->w;
    $user ??= $w->user;
    $conn = lab58_ssh_reach($w, $host, $port);
    if (!$conn['ok']) return lab58_ssh_conn_error($p, $conn, $host, $port);
    $spec = lab58_ssh_host_spec($w, $host);
    if ($spec === null) { $p->err("$user@$host: Permission denied (publickey,password).\n"); return 255; }
    $hk = lab58_ssh_hostkey($spec);
    $verdict = lab58_ssh_hostkey_check($p, $host, $hk, (string)($conn['ip'] ?? $host));
    if ($verdict !== 0) return $verdict;
    $id = lab58_ssh_identity($w, $identity);
    if ($id['exists'] && !$id['perms_ok']) { lab58_ssh_bad_perms($p, $id); return 255; }
    if ($id['fp'] === null || !in_array($id['fp'], lab58_ssh_authorized($w, $spec, $user), true)) return lab58_ssh_auth_fail($p, $spec, $user, $host, $id);
    return ['ok' => true, 'spec' => $spec, 'user' => $user];
}

function lab58_scp_download(Lab57Proc $p, array $src, array $dst, int $port, ?string $identity): int
{
    $w = $p->w;
    $conn = lab58_scp_connect($p, (string)$src['host'], $src['user'], $port, $identity);
    if (is_int($conn)) return $conn;
    $rw = lab58_ssh_remote_world($w, $conn['spec'], $conn['user'], $w->now);
    $remotePath = $src['path'];
    if (str_starts_with($remotePath, '~')) $remotePath = $rw->home($conn['user']) . substr($remotePath, 1);
    $remoteAbs = Lab57Vfs::normalize($remotePath, $rw->home($conn['user']));
    $err = null;
    $data = $rw->readFile($remoteAbs, $err);
    if ($data === null) { $p->err('scp: ' . $src['path'] . ': ' . $err . "\n"); return 1; }
    $localTarget = $dst['path'];
    if ($w->fs->isDir($w->abs($localTarget))) $localTarget = rtrim($localTarget, '/') . '/' . Lab57Vfs::basename($remoteAbs);
    if (!$w->writeFile($localTarget, $data, false, $err)) { $p->err('scp: ' . $localTarget . ': ' . $err . "\n"); return 1; }
    $p->line(Lab57Vfs::basename($remoteAbs) . str_repeat(' ', max(1, 30 - strlen(Lab57Vfs::basename($remoteAbs)))) . '100%  ' . str_pad((string)strlen($data), 4, ' ', STR_PAD_LEFT) . '     0.0KB/s   00:00');
    return 0;
}

function lab58_scp_upload(Lab57Proc $p, array $src, array $dst, int $port, ?string $identity): int
{
    $w = $p->w;
    $err = null;
    $data = $w->readFile($src['path'], $err);
    if ($data === null) { $p->err('scp: ' . $src['path'] . ': ' . $err . "\n"); return 1; }
    $conn = lab58_scp_connect($p, (string)$dst['host'], $dst['user'], $port, $identity);
    if (is_int($conn)) return $conn;
    $host = (string)($conn['spec']['name'] ?? $dst['host']);
    $user = $conn['user'];
    $target = $dst['path'] === '.' || $dst['path'] === '' ? '~/' . Lab57Vfs::basename($src['path']) : $dst['path'];
    if (str_ends_with($target, '/')) $target .= Lab57Vfs::basename($src['path']);
    $w->ext['ssh']['uploads'][$host][$user][$target] = $data;
    // Nahrání veřejného klíče do authorized_keys = přidání otisku (jako ssh-copy-id ručně).
    if (str_contains($target, 'authorized_keys')) {
        foreach (lab57_lines($data) as $line) {
            $fp = lab58_ssh_fp_of_pub($line);
            if ($fp !== null) $w->ext['ssh']['authorized'][$host][$user][] = $fp;
        }
    }
    $p->line(Lab57Vfs::basename($src['path']) . str_repeat(' ', max(1, 30 - strlen(Lab57Vfs::basename($src['path'])))) . '100%  ' . str_pad((string)strlen($data), 4, ' ', STR_PAD_LEFT) . '     0.0KB/s   00:00');
    return 0;
}

// ---------------------------------------------------------------------------
// ssh-copy-id
// ---------------------------------------------------------------------------

function lab58_cmd_ssh_copy_id(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'i:p:f', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    $target = (string)($ops[0] ?? '');
    if ($target === '') { $p->err("usage: ssh-copy-id [-i identity_file] [-p port] [user@]hostname\n"); return 1; }
    [$user, $host] = str_contains($target, '@') ? explode('@', $target, 2) : [$w->user, $target];
    $port = isset($o['p']) ? (int)$o['p'] : 22;
    $identity = isset($o['i']) ? (string)$o['i'] : null;
    $id = lab58_ssh_identity($w, $identity !== null ? (str_ends_with($identity, '.pub') ? substr($identity, 0, -4) : $identity) : null);
    if (!$id['exists'] || $id['pub'] === '') {
        $p->err("/usr/bin/ssh-copy-id: ERROR: No identities found\n");
        $w->tip(tr('Nemáš veřejný klíč. Nejdřív si ho vygeneruj: ssh-keygen -t ed25519.'));
        return 1;
    }
    $conn = lab58_ssh_reach($w, $host, $port);
    if (!$conn['ok']) return lab58_ssh_conn_error($p, $conn, $host, $port);
    $spec = lab58_ssh_host_spec($w, $host);
    if ($spec === null || empty($spec['users'][$user]['password'])) {
        $p->err("Permission denied (publickey,password).\n");
        $w->tip(tr('ssh-copy-id nahrává klíč po přihlášení heslem. Tenhle server ho v laboratoři nepřijímá – klíč přenes přes scp do ~/.ssh/authorized_keys.'));
        return 1;
    }
    $hostName = (string)($spec['name'] ?? $host);
    $fp = $id['fp'];
    $existing = lab58_ssh_authorized($w, $spec, $user);
    $p->err('/usr/bin/ssh-copy-id: INFO: Source of key(s) to be installed: "' . $id['path'] . ".pub\"\n");
    if ($fp !== null && in_array($fp, $existing, true)) {
        $p->err("/usr/bin/ssh-copy-id: WARNING: All keys were skipped because they already exist on the remote system.\n");
        return 0;
    }
    $p->err("/usr/bin/ssh-copy-id: INFO: attempting to log in with the new key(s), to filter out any that are already installed\n");
    $p->err("/usr/bin/ssh-copy-id: INFO: 1 key(s) remain to be installed -- if you are prompted now it is to install the new keys\n");
    if ($fp !== null) $w->ext['ssh']['authorized'][$hostName][$user][] = $fp;
    $w->ext['ssh']['uploads'][$hostName][$user]['~/.ssh/authorized_keys'] = ($w->ext['ssh']['uploads'][$hostName][$user]['~/.ssh/authorized_keys'] ?? '') . $id['pub'];
    $p->line('');
    $p->line('Number of key(s) added: 1');
    $p->line('');
    $p->line('Now try logging into the machine, with:   "ssh \'' . $user . '@' . $host . '\'"');
    $p->line('and check to make sure that only the key(s) you wanted were added.');
    $w->tip(tr('Ve skutečnosti se ssh-copy-id jednou zeptá na heslo účtu. V laboratoři ho za tebe potvrdí, aby ses mohl(a) hned přihlásit klíčem.'));
    return 0;
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

lab58_register_command('ssh', 'lab58_cmd_ssh', ['bin' => '/usr/bin/ssh']);
lab58_register_command('ssh-keygen', 'lab58_cmd_ssh_keygen', ['bin' => '/usr/bin/ssh-keygen']);
lab58_register_command('ssh-copy-id', 'lab58_cmd_ssh_copy_id', ['bin' => '/usr/bin/ssh-copy-id']);
lab58_register_command('scp', 'lab58_cmd_scp', ['bin' => '/usr/bin/scp']);

lab58_register_manual(['commands' => [
    'ssh' => ['extend' => true, 'tldr' => [['ssh user@host \'ls -la\'', 'spustí jeden příkaz na serveru'], ['ssh -i ~/.ssh/id_ed25519 user@host \'cat kod.txt\'', 'přihlásí se konkrétním klíčem'], ['ssh-keygen -R host', 'zapomene starý otisk serveru']], 'see_also' => ['ssh-keygen', 'ssh-copy-id', 'scp']],
    'scp' => ['extend' => true, 'tldr' => [['scp user@host:~/kod.txt .', 'stáhne soubor ze serveru'], ['scp soubor.txt user@host:~/', 'nahraje soubor na server'], ['scp -r slozka user@host:~/', 'přenese celou složku']], 'see_also' => ['ssh', 'ssh-copy-id', 'cp']],
    'ssh-keygen' => ['cat' => 'sit', 'summary' => 'Vytvoří pár SSH klíčů a ukáže jejich otisk.', 'synopsis' => 'ssh-keygen -t ed25519 [-f soubor] [-C komentář]', 'about' => 'Vygeneruje soukromý a veřejný klíč pro přihlášení bez hesla. Soukromý klíč (id_ed25519) si necháš a chráníš právy 600, veřejný (id_ed25519.pub) nahraješ na server. Otisk (SHA256:…) slouží k ověření, že klíč je opravdu ten tvůj. V laboratoři jsou klíče fiktivní deterministická data.', 'options' => [['-t typ', 'typ klíče: ed25519 (doporučeno), rsa, ecdsa'], ['-b bity', 'délka klíče (u rsa, např. 4096)'], ['-f soubor', 'kam klíč uložit'], ['-C komentář', 'popisek klíče (obvykle e-mail)'], ['-N fráze', 'přístupová fráze k soukromému klíči'], ['-l -f soubor', 'vypíše otisk existujícího klíče'], ['-R host', 'odstraní hosta z known_hosts']], 'examples' => [['ssh-keygen -t ed25519 -C "student@lab"', 'vytvoří moderní klíč ed25519'], ['ssh-keygen -t rsa -b 4096', 'vytvoří RSA klíč 4096 bitů'], ['ssh-keygen -R intranet.skola.test', 'zapomene starý otisk serveru']], 'tldr' => [['ssh-keygen -t ed25519', 'vytvoří pár klíčů'], ['ssh-keygen -l -f ~/.ssh/id_ed25519.pub', 'ukáže otisk klíče']], 'see_also' => ['ssh', 'ssh-copy-id'], 'related' => ['ssh', 'scp'], 'level' => 3, 'in_lab' => true, 'tip' => 'Soukromý klíč nikdy nikomu neposílej ani nenahrávej – sdílí se jen veřejný klíč (.pub).'],
    'ssh-copy-id' => ['cat' => 'sit', 'summary' => 'Nahraje tvůj veřejný klíč na server pro přihlášení bez hesla.', 'synopsis' => 'ssh-copy-id [user@]host', 'about' => 'Přidá tvůj veřejný klíč do souboru ~/.ssh/authorized_keys na serveru. Po nahrání se přihlásíš klíčem, aniž bys zadával(a) heslo. Poprvé se ještě přihlásíš heslem.', 'options' => [['-i soubor', 'který veřejný klíč nahrát'], ['-p port', 'port SSH serveru']], 'examples' => [['ssh-copy-id student@intranet.skola.test', 'nahraje klíč na školní server']], 'tldr' => [['ssh-copy-id user@host', 'povolí přihlášení klíčem']], 'see_also' => ['ssh-keygen', 'ssh', 'scp'], 'related' => ['ssh', 'ssh-keygen'], 'level' => 3, 'in_lab' => true],
]]);
