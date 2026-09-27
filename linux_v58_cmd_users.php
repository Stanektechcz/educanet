<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – uživatelé, skupiny a práva (LAB-05).
 *
 * useradd/userdel/usermod, groupadd/groupdel, gpasswd, passwd, chpasswd, chage, su,
 * getent, visudo, umask a čtení /etc/passwd, /etc/group, /etc/shadow. Vše jen mění
 * $w->users / $w->groups a soubory ve VFS – žádná skutečná kryptografie: hashe v /etc/shadow
 * jsou fiktivní deterministická data ($6$lab$…). Nic se nespouští, žádná síť.
 */

// ---------------------------------------------------------------------------
// Účty: synchronizace /etc/passwd, /etc/group, /etc/shadow
// ---------------------------------------------------------------------------

function lab58_users_sync(Lab57World $w): void
{
    lab57_world_write_accounts($w);
    lab58_users_write_shadow($w);
}

/** Fiktivní řádek /etc/shadow. Nikdy neukládá skutečné heslo – jen příznak pw ve světě. */
function lab58_users_shadow_field(Lab57World $w, string $name, array $u): string
{
    $state = $u['pw'] ?? null;
    $shell = (string)($u['shell'] ?? '');
    $hash = '$6$lab$' . substr(hash('sha256', 'lab|shadow|' . $name), 0, 43);
    if ($state === 'locked') return '!' . $hash;
    if ($state === true) return $hash;
    if ($name === 'root' || in_array($shell, ['/usr/sbin/nologin', '/bin/false'], true)) return '*';
    return '!';
}

function lab58_users_write_shadow(Lab57World $w): void
{
    if (!isset($w->groups['shadow'])) $w->groups['shadow'] = 42;
    $today = (int)floor($w->now / 86400);
    $lines = '';
    foreach ($w->users as $name => $u) {
        $lines .= $name . ':' . lab58_users_shadow_field($w, (string)$name, (array)$u) . ':' . $today . ':0:99999:7:::' . "\n";
    }
    $w->mkfile('/etc/shadow', $lines, 0640, 'root', 'shadow', $w->now);
}

function lab58_users_next_uid(Lab57World $w): int
{
    $max = 999;
    foreach ($w->users as $u) { $uid = (int)($u['uid'] ?? 0); if ($uid >= 1000 && $uid < 60000 && $uid > $max) $max = $uid; }
    return $max + 1;
}

function lab58_users_next_gid(Lab57World $w): int
{
    $max = 999;
    foreach ($w->groups as $gid) { $gid = (int)$gid; if ($gid >= 1000 && $gid < 60000 && $gid > $max) $max = $gid; }
    return $max + 1;
}

function lab58_users_require_root(Lab57Proc $p, string $name): bool
{
    if ($p->w->root) return true;
    $p->err($name . ": Permission denied.\n" . $name . ": cannot lock /etc/passwd; try again later.\n");
    $p->w->tip(tr('Účty a skupiny spravuje jen správce systému. Zkus příkaz zopakovat se sudo, např. sudo {name} …', ['name' => $name]));
    return false;
}

// ---------------------------------------------------------------------------
// useradd
// ---------------------------------------------------------------------------

function lab58_cmd_useradd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'ms:G:d:c:g:rN', ['create-home' => false, 'shell' => true, 'groups' => true, 'home-dir' => true, 'comment' => true, 'gid' => true, 'system' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($ops === []) { $p->err("useradd: missing operand\nTry 'useradd --help' for more information.\n"); return 2; }
    if (!lab58_users_require_root($p, 'useradd')) return 1;
    $name = (string)$ops[0];
    if (preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $name) !== 1) { $p->err("useradd: invalid user name '$name'\n"); return 3; }
    if (isset($w->users[$name])) { $p->err("useradd: user '$name' already exists\n"); return 9; }
    $uid = lab58_users_next_uid($w);
    $groups = [$name];
    if (!isset($w->groups[$name])) $w->groups[$name] = $uid;
    $gid = (int)$w->groups[$name];
    foreach (preg_split('/,/', (string)($o['G'] ?? $o['--groups'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $g) {
        if (!isset($w->groups[$g])) { $p->err("useradd: group '$g' does not exist\n"); return 6; }
        if (!in_array($g, $groups, true)) $groups[] = $g;
    }
    $home = (string)($o['d'] ?? $o['--home-dir'] ?? '/home/' . $name);
    $shell = (string)($o['s'] ?? $o['--shell'] ?? '/bin/sh');
    $gecos = (string)($o['c'] ?? $o['--comment'] ?? '');
    $w->users[$name] = ['uid' => $uid, 'gid' => $gid, 'home' => $home, 'shell' => $shell, 'groups' => $groups, 'gecos' => $gecos, 'pw' => null];
    if (isset($o['m']) || isset($o['--create-home'])) {
        $w->mkdirp($home, 0755, $name, $name);
        foreach (['.bashrc' => "# ~/.bashrc\n", '.profile' => "# ~/.profile\n", '.bash_logout' => ''] as $f => $c) {
            $w->mkfile($home . '/' . $f, $c, 0644, $name, $name, $w->now);
        }
    }
    lab58_users_sync($w);
    return 0;
}

// ---------------------------------------------------------------------------
// userdel
// ---------------------------------------------------------------------------

function lab58_cmd_userdel(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'rf', ['remove' => false, 'force' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($ops === []) { $p->err("userdel: missing operand\nTry 'userdel --help' for more information.\n"); return 2; }
    if (!lab58_users_require_root($p, 'userdel')) return 1;
    $name = (string)$ops[0];
    if (!isset($w->users[$name])) { $p->err("userdel: user '$name' does not exist\n"); return 6; }
    if (in_array($name, ['root', 'student'], true)) { $p->err("userdel: user $name is currently used by process 1\n"); return 8; }
    $home = $w->home($name);
    unset($w->users[$name]);
    if (isset($w->groups[$name])) {
        $used = false;
        foreach ($w->users as $u) if ((int)$u['gid'] === (int)$w->groups[$name]) { $used = true; break; }
        if (!$used) unset($w->groups[$name]);
    }
    if (isset($o['r']) || isset($o['--remove'])) {
        if ($w->fs->isDir($home)) $w->fs->deleteTree($home);
        if ($w->fs->exists('/var/mail/' . $name)) $w->fs->delete('/var/mail/' . $name);
    }
    lab58_users_sync($w);
    return 0;
}

// ---------------------------------------------------------------------------
// usermod
// ---------------------------------------------------------------------------

function lab58_cmd_usermod(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'aG:s:d:c:g:l:LU', ['append' => false, 'groups' => true, 'shell' => true, 'home' => true, 'comment' => true, 'login' => true, 'lock' => false, 'unlock' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($ops === []) { $p->err("usermod: missing operand\nTry 'usermod --help' for more information.\n"); return 2; }
    if (!lab58_users_require_root($p, 'usermod')) return 1;
    $name = (string)$ops[0];
    if (!isset($w->users[$name])) { $p->err("usermod: user '$name' does not exist\n"); return 6; }
    $u = $w->users[$name];
    $append = isset($o['a']) || isset($o['--append']);
    if (isset($o['G']) || isset($o['--groups'])) {
        $requested = preg_split('/,/', (string)($o['G'] ?? $o['--groups']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($requested as $g) if (!isset($w->groups[$g])) { $p->err("usermod: group '$g' does not exist\n"); return 6; }
        $primary = $w->primaryGroup($name);
        $current = $append ? $u['groups'] : array_values(array_filter((array)$u['groups'], static fn($g): bool => $g === $primary));
        foreach ($requested as $g) if (!in_array($g, $current, true)) $current[] = $g;
        $u['groups'] = array_values(array_unique($current));
    }
    if (isset($o['s']) || isset($o['--shell'])) $u['shell'] = (string)($o['s'] ?? $o['--shell']);
    if (isset($o['d']) || isset($o['--home'])) $u['home'] = (string)($o['d'] ?? $o['--home']);
    if (isset($o['c']) || isset($o['--comment'])) $u['gecos'] = (string)($o['c'] ?? $o['--comment']);
    if (isset($o['L']) || isset($o['--lock'])) $u['pw'] = 'locked';
    if (isset($o['U']) || isset($o['--unlock'])) $u['pw'] = ($u['pw'] ?? null) === 'locked' ? true : ($u['pw'] ?? null);
    $w->users[$name] = $u;
    if (isset($o['l']) || isset($o['--login'])) {
        $new = (string)($o['l'] ?? $o['--login']);
        if (preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $new) !== 1) { $p->err("usermod: invalid user name '$new'\n"); return 3; }
        if (isset($w->users[$new])) { $p->err("usermod: user '$new' already exists\n"); return 9; }
        $w->users[$new] = $w->users[$name];
        unset($w->users[$name]);
    }
    lab58_users_sync($w);
    return 0;
}

// ---------------------------------------------------------------------------
// groupadd / groupdel / gpasswd
// ---------------------------------------------------------------------------

function lab58_cmd_groupadd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'g:rf', ['gid' => true, 'system' => false, 'force' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if ($ops === []) { $p->err("groupadd: missing operand\nTry 'groupadd --help' for more information.\n"); return 2; }
    if (!lab58_users_require_root($p, 'groupadd')) return 1;
    $name = (string)$ops[0];
    if (preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $name) !== 1) { $p->err("groupadd: '$name' is not a valid group name\n"); return 3; }
    if (isset($w->groups[$name])) { if (isset($o['f'])) return 0; $p->err("groupadd: group '$name' already exists\n"); return 9; }
    $w->groups[$name] = isset($o['g']) ? (int)$o['g'] : lab58_users_next_gid($w);
    lab58_users_sync($w);
    return 0;
}

function lab58_cmd_groupdel(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $ops = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-')));
    if ($ops === []) { $p->err("groupdel: missing operand\nTry 'groupdel --help' for more information.\n"); return 2; }
    if (!lab58_users_require_root($p, 'groupdel')) return 1;
    $name = (string)$ops[0];
    if (!isset($w->groups[$name])) { $p->err("groupdel: group '$name' does not exist\n"); return 6; }
    foreach ($w->users as $uname => $u) {
        if ((int)$u['gid'] === (int)$w->groups[$name]) { $p->err("groupdel: cannot remove the primary group of user '$uname'\n"); return 8; }
    }
    unset($w->groups[$name]);
    foreach ($w->users as $uname => $u) {
        $w->users[$uname]['groups'] = array_values(array_filter((array)$u['groups'], static fn($g): bool => $g !== $name));
    }
    lab58_users_sync($w);
    return 0;
}

function lab58_cmd_gpasswd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'a:d:', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (!lab58_users_require_root($p, 'gpasswd')) return 1;
    $group = (string)($ops[0] ?? '');
    if ($group === '' || !isset($w->groups[$group])) { $p->err("gpasswd: group '$group' does not exist in /etc/group\n"); return 3; }
    $add = (string)($o['a'] ?? '');
    $del = (string)($o['d'] ?? '');
    if ($add !== '') {
        if (!isset($w->users[$add])) { $p->err("gpasswd: user '$add' does not exist\n"); return 3; }
        $groups = (array)$w->users[$add]['groups'];
        if (!in_array($group, $groups, true)) $groups[] = $group;
        $w->users[$add]['groups'] = array_values(array_unique($groups));
        $p->line("Adding user $add to group $group");
        lab58_users_sync($w);
        return 0;
    }
    if ($del !== '') {
        if (!isset($w->users[$del])) { $p->err("gpasswd: user '$del' does not exist\n"); return 3; }
        $w->users[$del]['groups'] = array_values(array_filter((array)$w->users[$del]['groups'], static fn($g): bool => $g !== $group || $g === $w->primaryGroup($del)));
        $p->line("Removing user $del from group $group");
        lab58_users_sync($w);
        return 0;
    }
    $p->err("Usage: gpasswd [option] GROUP\n");
    $w->tip(tr('Do skupiny přidáš člena: sudo gpasswd -a uzivatel skupina; odebereš: sudo gpasswd -d uzivatel skupina.'));
    return 2;
}

// ---------------------------------------------------------------------------
// passwd / chpasswd / chage
// ---------------------------------------------------------------------------

function lab58_cmd_passwd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-')));
    $target = (string)($args[0] ?? $w->effectiveUser());
    if (!isset($w->users[$target])) { $p->err("passwd: user '$target' does not exist\n"); return 1; }
    $p->err("passwd: heslo se zadává interaktivně, což simulovaný terminál nepodporuje.\n");
    $w->tip(tr('Heslo v laboratoři nastavíš neinteraktivně přes stdin: echo "{target}:NoveHeslo123" | sudo chpasswd. Skutečné heslo se nikde neukládá – jen příznak, že je nastavené.', ['target' => $target]));
    return 1;
}

function lab58_cmd_chpasswd(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    if (!lab58_users_require_root($p, 'chpasswd')) return 1;
    if (!$p->hasStdin) { $p->err("chpasswd: nedostal žádný vstup.\n"); $w->tip(tr('chpasswd čte dvojice uzivatel:heslo ze standardního vstupu: echo "student:Tajne123" | sudo chpasswd.')); return 1; }
    $status = 0;
    foreach (lab57_lines($p->stdin) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        [$user, $pw] = array_pad(explode(':', $line, 2), 2, null);
        if ($pw === null || $pw === '') { $p->err("chpasswd: line '$line' is badly formatted\n"); $status = 1; continue; }
        if (!isset($w->users[$user])) { $p->err("chpasswd: line '$line': user '$user' does not exist\n"); $status = 1; continue; }
        // Ukládá se jen příznak, nikdy skutečné heslo.
        $w->users[$user]['pw'] = true;
    }
    lab58_users_sync($w);
    return $status;
}

function lab58_cmd_chage(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'lE:M:m:d:', ['list' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $user = (string)($ops[0] ?? $w->effectiveUser());
    if (!isset($w->users[$user])) { $p->err("chage: user '$user' does not exist in /etc/passwd\n"); return 15; }
    if (isset($o['l']) || isset($o['--list'])) {
        if ($user !== $w->effectiveUser() && !$w->root) { $p->err("chage: Permission denied.\n"); $w->tip(tr('Údaje o hesle jiného uživatele smí číst jen správce: sudo chage -l {user}.', ['user' => $user])); return 1; }
        $state = $w->users[$user]['pw'] ?? null;
        $changed = $state === true ? date('M d, Y', $w->now - 86400 * 3) : ($state === 'locked' ? 'password must be changed' : 'never');
        $p->line('Last password change                                    : ' . $changed);
        $p->line('Password expires                                        : never');
        $p->line('Password inactive                                       : never');
        $p->line('Account expires                                         : never');
        $p->line('Minimum number of days between password change          : 0');
        $p->line('Maximum number of days between password change          : 99999');
        $p->line('Number of days of warning before password expires       : 7');
        return 0;
    }
    if (!lab58_users_require_root($p, 'chage')) return 1;
    return 0;
}

// ---------------------------------------------------------------------------
// su
// ---------------------------------------------------------------------------

function lab58_cmd_su(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => $a !== '-' && $a !== '-l' && $a !== '--login'));
    $target = (string)($args[0] ?? 'root');
    if (!isset($w->users[$target])) { $p->err("su: user $target does not exist or the user entry does not contain all the required fields\n"); return 1; }
    $p->err("su: Authentication failure\n");
    $w->tip(tr('V laboratoři se na jiného uživatele nepřepneš heslem – terminál je neinteraktivní. Jako správce spusť konkrétní příkaz přes sudo a svá oprávnění si ověř příkazem sudo -l.'));
    return 1;
}

// ---------------------------------------------------------------------------
// getent
// ---------------------------------------------------------------------------

function lab58_cmd_getent(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $db = (string)($argv[1] ?? '');
    $key = (string)($argv[2] ?? '');
    if ($db === 'passwd') {
        $found = false;
        foreach ($w->users as $name => $u) {
            if ($key !== '' && $name !== $key && (string)$u['uid'] !== $key) continue;
            $p->line($name . ':x:' . $u['uid'] . ':' . $u['gid'] . ':' . ($u['gecos'] ?? '') . ':' . $u['home'] . ':' . $u['shell']);
            $found = true;
        }
        return $key !== '' && !$found ? 2 : 0;
    }
    if ($db === 'group') {
        $found = false;
        foreach ($w->groups as $name => $gid) {
            if ($key !== '' && $name !== $key && (string)$gid !== $key) continue;
            $members = [];
            foreach ($w->users as $uname => $u) {
                if ((int)$u['gid'] !== (int)$gid && in_array($name, (array)$u['groups'], true)) $members[] = $uname;
            }
            $p->line($name . ':x:' . $gid . ':' . implode(',', $members));
            $found = true;
        }
        return $key !== '' && !$found ? 2 : 0;
    }
    if ($db === '') { $p->err("Usage: getent [OPTION]... database [key ...]\n"); return 1; }
    $p->err("getent: Unknown database: $db\n");
    $w->tip(tr('getent umí v laboratoři databáze passwd a group, např. getent passwd student.'));
    return 1;
}

// ---------------------------------------------------------------------------
// sudo -l / visudo -c (parser /etc/sudoers a /etc/sudoers.d)
// ---------------------------------------------------------------------------

/** @return list<string> soubory sudoers (hlavní + sudoers.d), v pořadí čtení */
function lab58_sudoers_files(Lab57World $w): array
{
    $files = [];
    if ($w->fs->exists('/etc/sudoers')) $files[] = '/etc/sudoers';
    if ($w->fs->isDir('/etc/sudoers.d')) {
        foreach ($w->fs->children('/etc/sudoers.d') as $name) {
            if (str_starts_with($name, '.') || str_ends_with($name, '~')) continue;
            $files[] = '/etc/sudoers.d/' . $name;
        }
    }
    return $files;
}

/** @return list<array{who:string,is_group:bool,spec:string}> pravidla platná pro uživatele */
function lab58_sudoers_rules_for(Lab57World $w, string $user): array
{
    $groups = $w->userGroups($user);
    $rules = [];
    foreach (lab58_sudoers_files($w) as $file) {
        $content = (string)($w->fs->get($file)['c'] ?? '');
        foreach (explode("\n", $content) as $line) {
            $line = trim((string)preg_replace('/#.*/', '', $line));
            if ($line === '' || str_starts_with($line, 'Defaults') || preg_match('/^(User_Alias|Cmnd_Alias|Host_Alias|Runas_Alias)\b/', $line) === 1 || str_starts_with($line, '@include')) continue;
            if (preg_match('/^(%?\S+)\s+\S+\s*=\s*(.+)$/', $line, $m) !== 1) continue;
            $who = $m[1];
            $spec = trim($m[2]);
            $isGroup = str_starts_with($who, '%');
            $bare = ltrim($who, '%');
            $matches = $isGroup ? in_array($bare, $groups, true) : ($bare === $user || $bare === 'ALL');
            if ($matches) $rules[] = ['who' => $who, 'is_group' => $isGroup, 'spec' => $spec];
        }
    }
    return $rules;
}

/** @return array{exit:int,lines:list<string>} výstup pro sudo -l (volá lab57_cmd_sudo). */
function lab58_sudo_list(Lab57World $w): array
{
    $user = $w->user;
    $files = lab58_sudoers_files($w);
    if ($files === []) {
        if (!$w->sudoAllowed) return ['exit' => 1, 'lines' => ["Sorry, user $user may not run sudo on {$w->hostname}."]];
        return ['exit' => 0, 'lines' => ["User $user may run the following commands on {$w->hostname}:", '    (ALL : ALL) ALL']];
    }
    $rules = lab58_sudoers_rules_for($w, $user);
    if ($rules === []) return ['exit' => 1, 'lines' => ["Sorry, user $user may not run sudo on {$w->hostname}."]];
    $lines = [
        "Matching Defaults entries for $user on {$w->hostname}:",
        '    env_reset, mail_badpass, secure_path=/usr/local/sbin\\:/usr/local/bin\\:/usr/sbin\\:/usr/bin\\:/sbin\\:/bin',
        '',
        "User $user may run the following commands on {$w->hostname}:",
    ];
    foreach ($rules as $rule) $lines[] = '    ' . $rule['spec'];
    return ['exit' => 0, 'lines' => $lines];
}

function lab58_cmd_visudo(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $check = in_array('-c', array_slice($argv, 1), true);
    if (!$check) {
        $p->err("visudo: sudoers upravuj v laboratoři přes editor: sudo nano /etc/sudoers.d/nazev, pak zkontroluj: sudo visudo -c\n");
        $w->tip(tr('visudo -c ověří syntaxi souborů sudoers, aniž bys je otevíral(a).'));
        return 1;
    }
    if (!$w->root) { $p->err("visudo: /etc/sudoers: Permission denied\n"); return 1; }
    $status = 0;
    foreach (array_merge(['/etc/sudoers'], array_slice(lab58_sudoers_files($w), 1)) as $file) {
        if (!$w->fs->exists($file)) { if ($file === '/etc/sudoers') { $p->err("visudo: unable to open /etc/sudoers: No such file or directory\n"); $status = 1; } continue; }
        $err = lab58_sudoers_syntax_error((string)($w->fs->get($file)['c'] ?? ''));
        if ($err === null) { $p->line($file . ': parsed OK'); continue; }
        $p->err('>>> ' . $file . ': syntax error near line ' . $err . ' <<<' . "\n");
        $status = 1;
    }
    return $status;
}

/** @return int|null číslo řádku s chybou, nebo null když je syntaxe v pořádku */
function lab58_sudoers_syntax_error(string $content): ?int
{
    $no = 0;
    foreach (explode("\n", $content) as $line) {
        $no++;
        $line = trim((string)preg_replace('/#.*/', '', $line));
        if ($line === '' || str_starts_with($line, 'Defaults') || preg_match('/^(User_Alias|Cmnd_Alias|Host_Alias|Runas_Alias)\b/', $line) === 1 || str_starts_with($line, '@include')) continue;
        if (preg_match('/^%?\S+\s+\S+\s*=\s*\S.*$/', $line) !== 1) return $no;
    }
    return null;
}

// ---------------------------------------------------------------------------
// umask (builtin)
// ---------------------------------------------------------------------------

function lab58_cmd_umask(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $symbolic = false;
    $value = null;
    foreach ($args as $a) {
        if ($a === '-S') { $symbolic = true; continue; }
        if ($a === '-p') continue;
        $value = (string)$a;
    }
    if ($value === null) {
        $mask = lab57_umask($w);
        if ($symbolic) { $p->line(lab58_umask_symbolic($mask)); return 0; }
        $p->line(sprintf('%04o', $mask));
        return 0;
    }
    if (preg_match('/^[0-7]{1,4}$/', $value) !== 1) { $p->err("bash: umask: `$value': invalid symbolic mode operator\n"); $w->tip(tr('Masku zadej osmičkově, např. umask 022 (nové soubory 644) nebo umask 077 (soukromé soubory).')); return 1; }
    $w->ext['umask'] = (int)octdec($value) & 0777;
    return 0;
}

function lab58_umask_symbolic(int $mask): string
{
    $parts = [];
    foreach (['u' => 6, 'g' => 3, 'o' => 0] as $who => $shift) {
        $allow = (~$mask >> $shift) & 7;
        $s = '';
        if ($allow & 4) $s .= 'r';
        if ($allow & 2) $s .= 'w';
        if ($allow & 1) $s .= 'x';
        $parts[] = $who . '=' . $s;
    }
    return implode(',', $parts);
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

lab58_register_command('useradd', 'lab58_cmd_useradd', ['bin' => '/usr/sbin/useradd']);
lab58_register_command('userdel', 'lab58_cmd_userdel', ['bin' => '/usr/sbin/userdel']);
lab58_register_command('usermod', 'lab58_cmd_usermod', ['bin' => '/usr/sbin/usermod']);
lab58_register_command('groupadd', 'lab58_cmd_groupadd', ['bin' => '/usr/sbin/groupadd']);
lab58_register_command('groupdel', 'lab58_cmd_groupdel', ['bin' => '/usr/sbin/groupdel']);
lab58_register_command('gpasswd', 'lab58_cmd_gpasswd', ['bin' => '/usr/bin/gpasswd']);
lab58_register_command('passwd', 'lab58_cmd_passwd', ['bin' => '/usr/bin/passwd']);
lab58_register_command('chpasswd', 'lab58_cmd_chpasswd', ['bin' => '/usr/sbin/chpasswd']);
lab58_register_command('chage', 'lab58_cmd_chage', ['bin' => '/usr/bin/chage']);
lab58_register_command('su', 'lab58_cmd_su', ['bin' => '/usr/bin/su']);
lab58_register_command('getent', 'lab58_cmd_getent', ['bin' => '/usr/bin/getent']);
lab58_register_command('visudo', 'lab58_cmd_visudo', ['bin' => '/usr/sbin/visudo']);
lab58_register_command('umask', 'lab58_cmd_umask', ['builtin' => true, 'help' => false]);

lab58_register_manual(['commands' => [
    'su' => ['extend' => true, 'tldr' => [['sudo -l', 'ukáže, co smíš jako správce']], 'see_also' => ['sudo', 'id']],
    'passwd' => ['extend' => true, 'tldr' => [['echo "student:Heslo123" | sudo chpasswd', 'neinteraktivně nastaví heslo']], 'see_also' => ['chpasswd', 'chage']],
    'umask' => ['extend' => true, 'tldr' => [['umask', 'vypíše aktuální masku'], ['umask 077', 'nové soubory budou soukromé (600)'], ['umask -S', 'ukáže masku slovně']], 'see_also' => ['chmod', 'touch']],
    'useradd' => ['cat' => 'prava', 'summary' => 'Vytvoří nový uživatelský účet.', 'synopsis' => 'useradd [-m] [-s shell] [-G skupiny] uživatel', 'about' => 'Založí nový účet v /etc/passwd. Volba -m vytvoří domovskou složku, -s určí přihlašovací shell a -G přidá uživatele do dalších skupin. Nové heslo se nastavuje zvlášť (passwd/chpasswd). Vyžaduje práva správce.', 'options' => [['-m', 'vytvoří domovskou složku'], ['-s shell', 'nastaví přihlašovací shell'], ['-G g1,g2', 'přidá do dalších (doplňkových) skupin'], ['-d cesta', 'nastaví domovskou složku'], ['-c popis', 'poznámka (celé jméno)']], 'examples' => [['sudo useradd -m -s /bin/bash novak', 'vytvoří účet novak s domovem a bashem'], ['sudo useradd -m -G sudo,lab spravce', 'vytvoří správce ve skupinách sudo a lab']], 'tldr' => [['sudo useradd -m -s /bin/bash jmeno', 'vytvoří uživatele s domovem'], ['sudo useradd -m -G sudo jmeno', 'rovnou přidá do skupiny sudo']], 'see_also' => ['usermod', 'userdel', 'passwd', 'groupadd'], 'related' => ['usermod', 'passwd'], 'level' => 3, 'in_lab' => true, 'warn' => 'Chybně nastavené účty mohou znemožnit přihlášení. Změny dělej rozvážně a jen se sudo.'],
    'userdel' => ['cat' => 'prava', 'summary' => 'Odstraní uživatelský účet.', 'synopsis' => 'userdel [-r] uživatel', 'about' => 'Smaže účet z /etc/passwd. Volba -r odstraní i domovskou složku a poštu uživatele. Vyžaduje práva správce.', 'options' => [['-r', 'smaže i domovskou složku a poštu']], 'examples' => [['sudo userdel novak', 'zruší účet, domov ponechá'], ['sudo userdel -r novak', 'zruší účet i s domovskou složkou']], 'tldr' => [['sudo userdel -r jmeno', 'zruší účet i s domovem']], 'see_also' => ['useradd', 'usermod'], 'related' => ['useradd'], 'level' => 3, 'in_lab' => true, 'warn' => 'Volba -r nevratně smaže data uživatele.'],
    'usermod' => ['cat' => 'prava', 'summary' => 'Změní nastavení uživatelského účtu.', 'synopsis' => 'usermod [-aG skupiny] [-s shell] [-L|-U] uživatel', 'about' => 'Upraví existující účet: přidá ho do skupin (-aG přidá, samotné -G nahradí!), změní shell (-s), zamkne (-L) nebo odemkne (-U) heslo. Vyžaduje práva správce.', 'options' => [['-aG skupiny', 'PŘIDÁ do doplňkových skupin (bez -a se nahradí!)'], ['-G skupiny', 'nastaví doplňkové skupiny (přepíše stávající)'], ['-s shell', 'změní přihlašovací shell'], ['-L', 'zamkne účet (nelze se přihlásit heslem)'], ['-U', 'odemkne účet']], 'examples' => [['sudo usermod -aG sudo novak', 'přidá novaka do skupiny sudo'], ['sudo usermod -L novak', 'zamkne účet novak']], 'tldr' => [['sudo usermod -aG skupina uzivatel', 'přidá uživatele do skupiny'], ['sudo usermod -L uzivatel', 'zamkne účet']], 'see_also' => ['useradd', 'gpasswd', 'groups'], 'related' => ['useradd', 'groups'], 'level' => 3, 'in_lab' => true, 'warn' => 'Pozor: usermod -G bez -a přepíše všechny doplňkové skupiny. Pro přidání používej -aG.'],
    'groupadd' => ['cat' => 'prava', 'summary' => 'Vytvoří novou skupinu.', 'synopsis' => 'groupadd [-g gid] skupina', 'about' => 'Přidá skupinu do /etc/group. Skupiny sdílejí přístup k souborům mezi více uživateli. Vyžaduje práva správce.', 'options' => [['-g gid', 'zvolí konkrétní číselné ID skupiny']], 'examples' => [['sudo groupadd projekt', 'vytvoří skupinu projekt'], ['sudo groupadd -g 1500 web', 'vytvoří skupinu web s GID 1500']], 'tldr' => [['sudo groupadd nazev', 'vytvoří novou skupinu']], 'see_also' => ['groupdel', 'gpasswd', 'usermod'], 'related' => ['gpasswd', 'usermod'], 'level' => 3, 'in_lab' => true],
    'groupdel' => ['cat' => 'prava', 'summary' => 'Odstraní skupinu.', 'synopsis' => 'groupdel skupina', 'about' => 'Smaže skupinu z /etc/group. Nelze smazat skupinu, která je něčí primární skupinou. Vyžaduje práva správce.', 'options' => [], 'examples' => [['sudo groupdel projekt', 'zruší skupinu projekt']], 'tldr' => [['sudo groupdel nazev', 'zruší skupinu']], 'see_also' => ['groupadd', 'gpasswd'], 'related' => ['groupadd'], 'level' => 3, 'in_lab' => true],
    'gpasswd' => ['cat' => 'prava', 'summary' => 'Přidá nebo odebere člena skupiny.', 'synopsis' => 'gpasswd -a|-d uživatel skupina', 'about' => 'Rychlá správa členství ve skupině bez přepisování ostatních skupin uživatele. Vyžaduje práva správce.', 'options' => [['-a uživatel', 'přidá uživatele do skupiny'], ['-d uživatel', 'odebere uživatele ze skupiny']], 'examples' => [['sudo gpasswd -a novak projekt', 'přidá novaka do skupiny projekt'], ['sudo gpasswd -d novak projekt', 'odebere novaka ze skupiny projekt']], 'tldr' => [['sudo gpasswd -a uzivatel skupina', 'přidá člena do skupiny']], 'see_also' => ['usermod', 'groups', 'getent'], 'related' => ['usermod', 'groups'], 'level' => 3, 'in_lab' => true],
    'chpasswd' => ['cat' => 'prava', 'summary' => 'Nastaví hesla dávkově ze standardního vstupu.', 'synopsis' => 'echo "uživatel:heslo" | sudo chpasswd', 'about' => 'Čte dvojice uživatel:heslo ze vstupu a nastaví hesla bez interaktivního dotazu. V laboratoři se skutečné heslo nikdy neukládá – zapíše se jen příznak, že účet heslo má. Vyžaduje práva správce.', 'options' => [], 'examples' => [['echo "student:Tajne123" | sudo chpasswd', 'nastaví heslo uživateli student']], 'tldr' => [['echo "user:heslo" | sudo chpasswd', 'neinteraktivně nastaví heslo']], 'see_also' => ['passwd', 'chage'], 'related' => ['passwd'], 'level' => 3, 'in_lab' => true, 'warn' => 'Heslo v příkazu je vidět v historii – v reálu používej opatrně. V laboratoři se žádné heslo neukládá.'],
    'chage' => ['cat' => 'prava', 'summary' => 'Zobrazí a upraví platnost hesla účtu.', 'synopsis' => 'chage -l uživatel', 'about' => 'Ukáže, kdy bylo naposledy změněno heslo a kdy vyprší. Volbou -l si vypíšeš přehled u sebe; u jiných uživatelů je potřeba správce.', 'options' => [['-l', 'vypíše informace o platnosti hesla']], 'examples' => [['chage -l student', 'vypíše stav hesla uživatele student']], 'tldr' => [['chage -l uzivatel', 'ukáže platnost hesla']], 'see_also' => ['passwd', 'chpasswd'], 'related' => ['passwd'], 'level' => 3, 'in_lab' => true],
    'getent' => ['cat' => 'prava', 'summary' => 'Vypíše záznamy z databází systému (uživatelé, skupiny).', 'synopsis' => 'getent passwd|group [klíč]', 'about' => 'Zobrazí obsah systémových databází stejně, jak je vidí systém – užitečné pro ověření, že účet nebo skupina existuje a do jaké skupiny kdo patří.', 'options' => [], 'examples' => [['getent passwd', 'vypíše všechny uživatele'], ['getent passwd student', 'vypíše jen řádek uživatele student'], ['getent group sudo', 'ukáže členy skupiny sudo']], 'tldr' => [['getent passwd student', 'ověří, že účet existuje'], ['getent group sudo', 'ukáže členy skupiny']], 'see_also' => ['id', 'groups', 'useradd'], 'related' => ['id', 'groups'], 'level' => 2, 'in_lab' => true],
    'visudo' => ['cat' => 'prava', 'summary' => 'Zkontroluje syntaxi konfigurace sudo.', 'synopsis' => 'sudo visudo -c', 'about' => 'Ověří, že soubory /etc/sudoers a /etc/sudoers.d neobsahují chybu, která by mohla zablokovat sudo. V laboratoři sudoers upravuj přes sudo nano a pak spusť kontrolu visudo -c.', 'options' => [['-c', 'jen zkontroluje syntaxi, nic neotevírá']], 'examples' => [['sudo visudo -c', 'zkontroluje všechny soubory sudoers']], 'tldr' => [['sudo visudo -c', 'ověří syntaxi sudoers']], 'see_also' => ['sudo'], 'related' => ['sudo'], 'level' => 3, 'in_lab' => true, 'warn' => 'Chyba v sudoers může znepřístupnit sudo. Před uložením vždy spusť visudo -c.'],
]]);
