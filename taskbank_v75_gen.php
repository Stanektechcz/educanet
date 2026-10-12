<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · banka úloh s generovanými variantami – GENERÁTORY (čisté funkce, nic nespouští, nic se nepřipojuje).
 *
 * Varianta = deterministická funkce semínka (student_id | úloha | kolo) → každý žák má jiná čísla, takže opisování nedává smysl,
 * a učitel dopočítá klíč přesně té varianty, kterou žák vidí. Výpočty jsou čistě aritmetické (IPv4 jako 32bitové číslo, cesty jako
 * zásobník, oprávnění jako bity, grep/wc nad vygenerovaným textem v paměti). Adresy jen privátní (RFC 1918) nebo dokumentační
 * (RFC 5737). Texty zadání jsou výukový obsah (česky, žákovské UI je obaluje edu_content_lang_attr()).
 *
 * Varianta: ['task','round','prompt','data'=>list<string> (řádky ukázky), 'fields'=>list<['id','label','kind'=>text|number|select,
 *   'options'?, 'answer','hint']>, 'explain'=>string].
 */

const TB75_TASKS = [
    'net_subnet' => ['title' => 'Podsíť: síť, broadcast a počet adres', 'area' => 'site', 'minutes' => 5],
    'net_mask' => ['title' => 'Maska a prefix', 'area' => 'site', 'minutes' => 3],
    'net_ports' => ['title' => 'Porty a služby', 'area' => 'site', 'minutes' => 3],
    'net_route' => ['title' => 'Směrovací tabulka', 'area' => 'site', 'minutes' => 5],
    'lnx_path' => ['title' => 'Cesty v Linuxu', 'area' => 'linux', 'minutes' => 4],
    'lnx_chmod' => ['title' => 'Oprávnění a chmod', 'area' => 'linux', 'minutes' => 4],
    'lnx_grep' => ['title' => 'grep a roury', 'area' => 'linux', 'minutes' => 5],
];

/** Deterministický generátor celých čísel ze semínka (SHA-256 v režimu čítače). */
function tb75_rng(string $seed): Closure
{
    $counter = 0;
    return static function (int $min, int $max) use ($seed, &$counter): int {
        if ($max <= $min) return $min;
        $n = hexdec(substr(hash('sha256', $seed . '#' . $counter++), 0, 12));
        return $min + (int)($n % ($max - $min + 1));
    };
}

function tb75_ip2long(string $ip): int
{
    $p = array_map('intval', explode('.', $ip));
    return (($p[0] << 24) | ($p[1] << 16) | ($p[2] << 8) | $p[3]) & 0xFFFFFFFF;
}

function tb75_long2ip(int $n): string
{
    return implode('.', [($n >> 24) & 255, ($n >> 16) & 255, ($n >> 8) & 255, $n & 255]);
}

function tb75_mask(int $prefix): int
{
    return $prefix === 0 ? 0 : (0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF;
}

/** @return array{network:string,broadcast:string,hosts:int,mask:string} */
function tb75_subnet(string $ip, int $prefix): array
{
    $mask = tb75_mask($prefix);
    $net = tb75_ip2long($ip) & $mask;
    $bc = $net | (~$mask & 0xFFFFFFFF);
    return ['network' => tb75_long2ip($net), 'broadcast' => tb75_long2ip($bc), 'hosts' => $prefix >= 31 ? 0 : (2 ** (32 - $prefix)) - 2, 'mask' => tb75_long2ip($mask)];
}

/** Náhodná privátní adresa: 10.x.x.x, 172.16–31.x.x nebo 192.168.x.x. */
function tb75_private_ip(Closure $r): string
{
    return match ($r(0, 2)) {
        0 => '10.' . $r(0, 255) . '.' . $r(0, 255) . '.' . $r(1, 254),
        1 => '172.' . $r(16, 31) . '.' . $r(0, 255) . '.' . $r(1, 254),
        default => '192.168.' . $r(0, 255) . '.' . $r(1, 254),
    };
}

function tb75_variant(string $task, string $seed, int $round): array
{
    $r = tb75_rng($task . '|' . $seed . '|' . $round);
    $v = match ($task) {
        'net_subnet' => tb75_gen_subnet($r),
        'net_mask' => tb75_gen_mask($r),
        'net_ports' => tb75_gen_ports($r),
        'net_route' => tb75_gen_route($r),
        'lnx_path' => tb75_gen_path($r),
        'lnx_chmod' => tb75_gen_chmod($r),
        'lnx_grep' => tb75_gen_grep($r),
        default => throw new InvalidArgumentException('Neznámá úloha.'),
    };
    return ['task' => $task, 'round' => $round] + $v + ['data' => []];
}

function tb75_gen_subnet(Closure $r): array
{
    $prefix = $r(22, 29);
    $ip = tb75_private_ip($r);
    $s = tb75_subnet($ip, $prefix);
    return ['prompt' => 'Zařízení má adresu ' . $ip . '/' . $prefix . '. Urči síťovou adresu, broadcastovou adresu a počet použitelných adres pro zařízení.',
        'fields' => [
            ['id' => 'network', 'label' => 'Síťová adresa', 'kind' => 'text', 'answer' => $s['network'], 'hint' => 'např. 192.168.1.0'],
            ['id' => 'broadcast', 'label' => 'Broadcastová adresa', 'kind' => 'text', 'answer' => $s['broadcast'], 'hint' => 'např. 192.168.1.255'],
            ['id' => 'hosts', 'label' => 'Použitelných adres', 'kind' => 'number', 'answer' => (string)$s['hosts'], 'hint' => 'celé číslo'],
        ],
        'explain' => 'Maska /' . $prefix . ' = ' . $s['mask'] . '. Blok má ' . (2 ** (32 - $prefix)) . ' adres; síť je první adresa bloku (' . $s['network'] . '), broadcast poslední ('
            . $s['broadcast'] . '), pro zařízení zbývá ' . $s['hosts'] . ' adres.'];
}

function tb75_gen_mask(Closure $r): array
{
    $a = $r(8, 30);
    $b = $r(8, 30);
    if ($b === $a) $b = $a === 30 ? 29 : $a + 1;
    return ['prompt' => 'Převeď zápis prefixu na masku a masku na prefix.',
        'fields' => [
            ['id' => 'mask', 'label' => 'Maska pro /' . $a, 'kind' => 'text', 'answer' => tb75_long2ip(tb75_mask($a)), 'hint' => 'např. 255.255.255.0'],
            ['id' => 'prefix', 'label' => 'Prefix pro masku ' . tb75_long2ip(tb75_mask($b)), 'kind' => 'number', 'answer' => (string)$b, 'hint' => 'číslo 0–32 bez lomítka'],
        ],
        'explain' => '/' . $a . ' = ' . $a . ' jedniček zleva = ' . tb75_long2ip(tb75_mask($a)) . '. Maska ' . tb75_long2ip(tb75_mask($b)) . ' má ' . $b . ' jedniček, tedy /' . $b . '.'];
}

/** @return array<string,int> služba → port (dobře známé porty IANA) */
function tb75_ports_table(): array
{
    return ['HTTP' => 80, 'HTTPS' => 443, 'SSH' => 22, 'DNS' => 53, 'SMTP' => 25, 'IMAP' => 143, 'POP3' => 110, 'FTP (řízení)' => 21, 'Telnet' => 23,
        'RDP' => 3389, 'MySQL' => 3306, 'PostgreSQL' => 5432, 'NTP' => 123, 'SNMP' => 161, 'LDAP' => 389, 'DHCP (server)' => 67];
}

function tb75_gen_ports(Closure $r): array
{
    $table = tb75_ports_table();
    $names = array_keys($table);
    $picked = [];
    while (count($picked) < 3) $picked[$names[$r(0, count($names) - 1)]] = true;
    $fields = [];
    $i = 0;
    foreach (array_keys($picked) as $name) $fields[] = ['id' => 'p' . $i++, 'label' => 'Port služby ' . $name, 'kind' => 'number', 'answer' => (string)$table[$name], 'hint' => 'číslo portu'];
    return ['prompt' => 'Na kterém výchozím portu naslouchají tyto služby?', 'fields' => $fields,
        'explain' => implode(', ', array_map(static fn(string $n): string => $n . ' = ' . $table[$n], array_keys($picked))) . '. Výchozí porty jsou dohoda (IANA) – správce je může změnit.'];
}

function tb75_gen_route(Closure $r): array
{
    $a = $r(1, 200);
    $b = $r(0, 63) * 4;
    $routes = [
        ['10.' . $a . '.0.0/16', '10.0.0.2'],
        ['10.' . $a . '.' . $b . '.0/22', '10.0.0.3'],
        ['192.168.' . $r(0, 255) . '.0/24', '10.0.0.4'],
        ['0.0.0.0/0', '10.0.0.1'],
    ];
    $dest = [
        '10.' . $a . '.' . ($b + $r(0, 3)) . '.' . $r(1, 254),
        '10.' . $a . '.' . (($b + 4 + $r(0, 200)) % 256) . '.' . $r(1, 254),
        '203.0.113.' . $r(1, 254),
    ];
    $hops = ['10.0.0.1', '10.0.0.2', '10.0.0.3', '10.0.0.4'];
    $fields = [];
    foreach (array_slice($dest, 0, 2) as $i => $d) $fields[] = ['id' => 'd' . $i, 'label' => 'Další skok pro ' . $d, 'kind' => 'select', 'options' => $hops, 'answer' => tb75_route_lookup($routes, $d), 'hint' => ''];
    $fields[] = ['id' => 'd2', 'label' => 'Další skok pro ' . $dest[2], 'kind' => 'select', 'options' => $hops, 'answer' => tb75_route_lookup($routes, $dest[2]), 'hint' => ''];
    return ['prompt' => 'Směrovač má tuto tabulku. Kam pošle pakety pro uvedené cíle? Platí nejdelší shodný prefix.',
        'data' => array_map(static fn(array $rt): string => str_pad($rt[0], 20) . ' via ' . $rt[1], $routes), 'fields' => $fields,
        'explain' => 'Ze všech tras, do kterých cíl spadá, vyhrává ta s nejdelším prefixem; výchozí trasa 0.0.0.0/0 sedí na všechno, ale má prefix 0.'];
}

/** Nejdelší shodný prefix. @param list<array{0:string,1:string}> $routes */
function tb75_route_lookup(array $routes, string $ip): string
{
    $best = -1;
    $hop = '';
    foreach ($routes as [$net, $via]) {
        [$addr, $len] = explode('/', $net);
        $mask = tb75_mask((int)$len);
        if ((tb75_ip2long($ip) & $mask) === (tb75_ip2long($addr) & $mask) && (int)$len > $best) { $best = (int)$len; $hop = $via; }
    }
    return $hop;
}

function tb75_gen_path(Closure $r): array
{
    $dirs = ['projekty', 'web', 'logy', 'zalohy', 'skripty', 'obrazky', 'data', 'dokumenty'];
    $user = ['jana', 'petr', 'eva', 'tomas'][$r(0, 3)];
    $start = '/home/' . $user . '/' . $dirs[$r(0, 7)] . '/' . $dirs[$r(0, 7)];
    $cmds = [];
    $choices = ['..', '../' . $dirs[$r(0, 7)], './' . $dirs[$r(0, 7)], '/var/log', '~', '../..', $dirs[$r(0, 7)] . '/' . $dirs[$r(0, 7)]];
    for ($i = 0, $n = $r(3, 4); $i < $n; $i++) $cmds[] = $choices[$r(0, count($choices) - 1)];
    $cwd = $start;
    foreach ($cmds as $c) $cwd = tb75_cd($cwd, $c, '/home/' . $user);
    return ['prompt' => 'Jsi ve složce ' . $start . ' (uživatel ' . $user . '). Postupně zadáš tyto příkazy. Co vypíše na konci pwd?',
        'data' => array_map(static fn(string $c): string => '$ cd ' . $c, $cmds),
        'fields' => [['id' => 'pwd', 'label' => 'Výstup pwd', 'kind' => 'text', 'answer' => $cwd, 'hint' => 'absolutní cesta, např. /home/jana']],
        'explain' => '„..“ je nadřazená složka, „.“ aktuální, „~“ domovská složka (/home/' . $user . '), cesta začínající „/“ je absolutní. Výsledek: ' . $cwd . '.'];
}

/** Změna složky jako operace nad zásobníkem (bez souborového systému). */
function tb75_cd(string $cwd, string $arg, string $home): string
{
    if ($arg === '~') return $home;
    $parts = str_starts_with($arg, '/') ? [] : array_values(array_filter(explode('/', $cwd), 'strlen'));
    foreach (explode('/', $arg) as $seg) {
        if ($seg === '' || $seg === '.') continue;
        if ($seg === '..') { array_pop($parts); continue; }
        $parts[] = $seg;
    }
    return '/' . implode('/', $parts);
}

function tb75_symbolic(int $octal): string
{
    $out = '';
    foreach ([6, 3, 0] as $shift) {
        $d = ($octal >> $shift) & 7;
        $out .= ($d & 4 ? 'r' : '-') . ($d & 2 ? 'w' : '-') . ($d & 1 ? 'x' : '-');
    }
    return $out;
}

function tb75_gen_chmod(Closure $r): array
{
    $pool = [0644, 0640, 0600, 0755, 0750, 0700, 0711, 0664, 0660, 0775, 0444, 0555];
    $a = $pool[$r(0, count($pool) - 1)];
    $b = $pool[$r(0, count($pool) - 1)];
    $base = $pool[$r(0, count($pool) - 1)];
    $ops = [['u+x', 0100, true], ['g+w', 0020, true], ['o-r', 0004, false], ['g-x', 0010, false], ['o+x', 0001, true], ['u-w', 0200, false]];
    [$op, $bit, $add] = $ops[$r(0, count($ops) - 1)];
    $after = $add ? ($base | $bit) : ($base & ~$bit & 0777);
    return ['prompt' => 'Převeď oprávnění mezi zápisy a urči výsledek příkazu chmod.',
        'fields' => [
            ['id' => 'oct', 'label' => 'Osmičkově pro ' . tb75_symbolic($a), 'kind' => 'text', 'answer' => sprintf('%03o', $a), 'hint' => 'tři číslice, např. 644'],
            ['id' => 'sym', 'label' => 'Symbolicky pro ' . sprintf('%03o', $b), 'kind' => 'text', 'answer' => tb75_symbolic($b), 'hint' => '9 znaků, např. rw-r--r--'],
            ['id' => 'after', 'label' => 'Soubor má ' . tb75_symbolic($base) . ', pak chmod ' . $op . ' – výsledek symbolicky', 'kind' => 'text', 'answer' => tb75_symbolic($after), 'hint' => '9 znaků'],
        ],
        'explain' => 'r = 4, w = 2, x = 1 po trojicích vlastník / skupina / ostatní. ' . tb75_symbolic($a) . ' = ' . sprintf('%03o', $a) . '; ' . sprintf('%03o', $b) . ' = ' . tb75_symbolic($b)
            . '; chmod ' . $op . ' na ' . tb75_symbolic($base) . ' dá ' . tb75_symbolic($after) . '.'];
}

function tb75_gen_grep(Closure $r): array
{
    $levels = ['INFO', 'WARN', 'ERROR', 'DEBUG'];
    $services = ['web', 'db', 'mail', 'cron'];
    $lines = [];
    for ($i = 0, $n = $r(8, 11); $i < $n; $i++) {
        $lvl = $levels[$r(0, 3)];
        $lines[] = sprintf('10:%02d %s %s %s', $i * 3 + $r(0, 2), $r(0, 4) === 0 ? strtolower($lvl) : $lvl, $services[$r(0, 3)], ['start', 'timeout', 'restart', 'ok', 'denied'][$r(0, 4)]);
    }
    $word = $levels[$r(0, 2)];
    $svc = $services[$r(0, 3)];
    $q = [
        ['grep -c "' . $word . '" app.log', tb75_grep_count($lines, $word, false, false)],
        ['grep -i "' . strtolower($word) . '" app.log | wc -l', tb75_grep_count($lines, $word, true, false)],
        ['grep -v "' . $svc . '" app.log | wc -l', tb75_grep_count($lines, $svc, false, true)],
    ];
    return ['prompt' => 'V souboru app.log jsou tyto řádky. Kolik vypíšou uvedené příkazy?', 'data' => $lines,
        'fields' => array_map(static fn(array $x, int $i): array => ['id' => 'g' . $i, 'label' => '$ ' . $x[0], 'kind' => 'number', 'answer' => (string)$x[1], 'hint' => 'počet řádků'], $q, array_keys($q)),
        'explain' => 'grep -c spočítá řádky se shodou (rozlišuje velikost písmen), -i ji ignoruje, -v vybere řádky BEZ shody; wc -l spočítá řádky na vstupu. Správně: '
            . implode(', ', array_map(static fn(array $x): string => (string)$x[1], $q)) . '.'];
}

/** Počet řádků se shodou (pevný řetězec), volitelně bez ohledu na velikost písmen nebo inverzně (-v). */
function tb75_grep_count(array $lines, string $needle, bool $ignoreCase, bool $invert): int
{
    $n = 0;
    foreach ($lines as $line) {
        $hit = $ignoreCase ? stripos($line, $needle) !== false : str_contains($line, $needle);
        if ($hit !== $invert) $n++;
    }
    return $n;
}

/** Normalizace odpovědi pro porovnání (mezery, koncové lomítko u cest, úvodní nula u osmičkového zápisu). */
function tb75_norm(string $task, string $fieldId, string $value): string
{
    $v = strtolower(trim((string)preg_replace('/\s+/u', '', $value)));
    if ($task === 'lnx_path' && strlen($v) > 1) $v = rtrim($v, '/');
    if ($task === 'lnx_chmod' && $fieldId === 'oct' && preg_match('/^0[0-7]{3}$/', $v) === 1) $v = substr($v, 1);
    if ($task === 'net_mask' && $fieldId === 'prefix') $v = ltrim($v, '/');
    return $v;
}
