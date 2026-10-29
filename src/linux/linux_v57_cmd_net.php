<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – simulovaná síť a síťové příkazy.
 *
 * Síť je graf uzlů v paměti (router, poskytovatel, DNS, weby…). ping, traceroute, dig, curl
 * a spol. jen procházejí tento graf – žádný paket nikdy neopustí server a PHP nevolá
 * žádné síťové funkce. Výsledek každého příkazu navíc popisuje cestu pro animaci topologie.
 */

function lab57_cmds_net(): array
{
    return [
        'ip' => 'lab57_cmd_ip', 'ifconfig' => 'lab57_cmd_ifconfig', 'ping' => 'lab57_cmd_ping', 'traceroute' => 'lab57_cmd_traceroute',
        'tracepath' => 'lab57_cmd_traceroute', 'dig' => 'lab57_cmd_dig', 'nslookup' => 'lab57_cmd_nslookup', 'host' => 'lab57_cmd_host',
        'curl' => 'lab57_cmd_curl', 'wget' => 'lab57_cmd_wget', 'ss' => 'lab57_cmd_ss', 'netstat' => 'lab57_cmd_netstat', 'nc' => 'lab57_cmd_nc',
    ];
}

// ---------------------------------------------------------------------------
// Model sítě
// ---------------------------------------------------------------------------

function lab57_ip_long(string $ip): ?int
{
    $long = ip2long($ip);
    return $long === false ? null : $long;
}

function lab57_ip_in(string $ip, string $cidr): bool
{
    if (!str_contains($cidr, '/')) return $ip === $cidr;
    [$net, $bits] = explode('/', $cidr, 2);
    $ipL = lab57_ip_long($ip);
    $netL = lab57_ip_long($net);
    if ($ipL === null || $netL === null) return false;
    $bits = (int)$bits;
    $mask = $bits === 0 ? 0 : (~0 << (32 - $bits)) & 0xFFFFFFFF;
    return ($ipL & $mask) === ($netL & $mask);
}

function lab57_prefix_mask(int $bits): string
{
    return (string)long2ip($bits === 0 ? 0 : (~0 << (32 - $bits)) & 0xFFFFFFFF);
}

function lab57_broadcast(string $ip, int $bits): string
{
    $mask = $bits === 0 ? 0 : (~0 << (32 - $bits)) & 0xFFFFFFFF;
    return (string)long2ip(((int)lab57_ip_long($ip) & $mask) | (~$mask & 0xFFFFFFFF));
}

/** @return array{0:string,1:array}|null [id uzlu, uzel] */
function lab57_net_node_by_ip(Lab57World $w, string $ip): ?array
{
    foreach ((array)($w->net['nodes'] ?? []) as $id => $node) {
        if ((string)($node['ip'] ?? '') === $ip) return [(string)$id, $node];
    }
    return null;
}

function lab57_net_node_up(Lab57World $w, string $id): bool
{
    $node = $w->net['nodes'][$id] ?? null;
    if ($node === null) return false;
    return !in_array($id, (array)($w->net['faults']['down'] ?? []), true) && ($node['up'] ?? true) !== false;
}

function lab57_net_is_local(Lab57World $w, string $ip): bool
{
    if (str_starts_with($ip, '127.')) return true;
    foreach ((array)$w->net['ifaces'] as $iface) if ((string)($iface['ip'] ?? '') === $ip) return true;
    return false;
}

/** Aktivní trasy (trasy přes vypnuté rozhraní systém nepoužívá). */
function lab57_net_routes(Lab57World $w): array
{
    $routes = [];
    foreach ((array)($w->net['routes'] ?? []) as $route) {
        $iface = $w->net['ifaces'][(string)($route['dev'] ?? '')] ?? null;
        if ($iface === null || empty($iface['up']) || (string)($iface['ip'] ?? '') === '') continue;
        $routes[] = $route;
    }
    return $routes;
}

/**
 * Cesta paketu k IP adrese.
 * @return array{ok:bool,error:?string,hops:list<string>,dest:?string,via:?string,dev:?string,ms:float,fail:?string}
 */
function lab57_net_path(Lab57World $w, string $ip): array
{
    $res = ['ok' => false, 'error' => null, 'hops' => [], 'dest' => null, 'via' => null, 'dev' => null, 'ms' => 0.0, 'fail' => null];
    if (lab57_net_is_local($w, $ip)) {
        $lo = $w->net['ifaces']['lo'] ?? ['up' => true];
        if (str_starts_with($ip, '127.') && empty($lo['up'])) return ['error' => 'unreachable'] + $res;
        return ['ok' => true, 'hops' => ['pc'], 'dest' => 'pc', 'dev' => 'lo', 'ms' => 0.04] + $res;
    }
    $best = null;
    $bestLen = -1;
    foreach (lab57_net_routes($w) as $route) {
        $dst = (string)$route['dst'];
        $len = $dst === 'default' ? 0 : (int)(explode('/', $dst)[1] ?? 32);
        if ($dst !== 'default' && !lab57_ip_in($ip, $dst)) continue;
        if ($len > $bestLen) { $best = $route; $bestLen = $len; }
    }
    if ($best === null) return ['error' => 'unreachable'] + $res;
    $res['dev'] = (string)$best['dev'];
    if (!empty($w->net['faults']['no_carrier'][(string)$best['dev']])) return ['error' => 'host_unreachable', 'fail' => 'switch'] + $res;
    $via = isset($best['via']) ? (string)$best['via'] : null;
    $target = lab57_net_node_by_ip($w, $ip);
    if ($via === null) {
        if ($target === null || !lab57_net_node_up($w, $target[0])) return ['error' => 'host_unreachable', 'hops' => ['switch'], 'fail' => $target[0] ?? 'switch'] + $res;
        return ['ok' => true, 'hops' => [$target[0]], 'dest' => $target[0], 'ms' => (float)($target[1]['lat'] ?? 0.5)] + $res;
    }
    $res['via'] = $via;
    $gw = lab57_net_node_by_ip($w, $via);
    if ($gw === null || !lab57_net_node_up($w, $gw[0])) return ['error' => 'host_unreachable', 'hops' => ['switch'], 'fail' => $gw[0] ?? 'switch'] + $res;
    $hops = [];
    $path = (array)($w->net['wan_path'] ?? []);
    if (($path[0] ?? null) !== $gw[0]) $path = [$gw[0]];
    $break = (string)($w->net['faults']['wan_break_after'] ?? '');
    foreach ($path as $hop) {
        if (!lab57_net_node_up($w, (string)$hop)) return ['error' => 'timeout', 'hops' => $hops, 'fail' => (string)$hop] + $res;
        $hops[] = (string)$hop;
        if ($break !== '' && $hop === $break) {
            return ['error' => 'timeout', 'hops' => $hops, 'fail' => $hop] + $res;
        }
    }
    if ($target === null || !lab57_net_node_up($w, $target[0])) return ['error' => 'timeout', 'hops' => $hops, 'fail' => $target[0] ?? null] + $res;
    if (in_array($target[0], $hops, true)) {
        $hops = array_slice($hops, 0, (int)array_search($target[0], $hops, true) + 1);
    } else {
        $hops[] = $target[0];
    }
    return ['ok' => true, 'hops' => $hops, 'dest' => $target[0], 'ms' => (float)($target[1]['lat'] ?? 15.0)] + $res;
}

/** Jmenné servery z /etc/resolv.conf. */
function lab57_net_nameservers(Lab57World $w): array
{
    $content = (string)($w->fs->get('/etc/resolv.conf')['c'] ?? '');
    preg_match_all('/^\s*nameserver\s+(\S+)/m', $content, $m);
    return array_values(array_unique($m[1]));
}

function lab57_net_search_domains(Lab57World $w): array
{
    $content = (string)($w->fs->get('/etc/resolv.conf')['c'] ?? '');
    return preg_match('/^\s*(?:search|domain)\s+(.+)$/m', $content, $m) === 1 ? (preg_split('/\s+/', trim($m[1])) ?: []) : [];
}

/** Záznam z DNS tabulky (sleduje CNAME). @return array{found:bool,values:list,cname:?string,name:string} */
function lab57_dns_lookup(Lab57World $w, string $name, string $type = 'A'): array
{
    $name = strtolower(rtrim($name, '.'));
    $table = (array)($w->net['dns'] ?? []);
    $cname = null;
    for ($i = 0; $i < 5; $i++) {
        $rec = $table[$name] ?? null;
        if ($rec === null) return ['found' => $cname !== null, 'values' => [], 'cname' => $cname, 'name' => $name, 'exists' => $cname !== null];
        if ($type !== 'CNAME' && isset($rec['CNAME'])) { $cname = (string)$rec['CNAME'][0]; $name = strtolower($cname); continue; }
        return ['found' => true, 'values' => (array)($rec[$type] ?? []), 'cname' => $cname, 'name' => $name, 'exists' => true];
    }
    return ['found' => false, 'values' => [], 'cname' => $cname, 'name' => $name, 'exists' => false];
}

/** Obslouží DNS dotaz přes konkrétní server. @return array{ok:bool,error:?string,ms:float} */
function lab57_dns_server_reach(Lab57World $w, string $server): array
{
    $path = lab57_net_path($w, $server);
    if (!$path['ok']) return ['ok' => false, 'error' => 'timeout', 'ms' => 0.0, 'path' => $path];
    $node = lab57_net_node_by_ip($w, $server);
    if ($node === null) return ['ok' => false, 'error' => 'timeout', 'ms' => 0.0, 'path' => $path];
    $isDns = !empty($node[1]['dns']) || isset($node[1]['ports'][53]);
    if (!$isDns || in_array($node[0], (array)($w->net['faults']['dns_dead'] ?? []), true)) return ['ok' => false, 'error' => 'refused', 'ms' => $path['ms'], 'path' => $path];
    return ['ok' => true, 'error' => null, 'ms' => $path['ms'], 'path' => $path];
}

/**
 * Přeloží jméno na IPv4 jako systém: /etc/hosts, potom DNS servery z resolv.conf.
 * @return array{ip:?string,error:?string,via:string,server:?string}
 */
function lab57_net_resolve(Lab57World $w, string $name): array
{
    $name = strtolower(trim($name));
    if (filter_var($name, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) return ['ip' => $name, 'error' => null, 'via' => 'ip', 'server' => null];
    if ($name === 'localhost') return ['ip' => '127.0.0.1', 'error' => null, 'via' => 'hosts', 'server' => null];
    foreach (explode("\n", (string)($w->fs->get('/etc/hosts')['c'] ?? '')) as $line) {
        $line = trim((string)preg_replace('/#.*$/', '', $line));
        if ($line === '') continue;
        $parts = preg_split('/\s+/', $line) ?: [];
        $ip = (string)array_shift($parts);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) continue;
        if (in_array($name, array_map('strtolower', $parts), true)) return ['ip' => $ip, 'error' => null, 'via' => 'hosts', 'server' => null];
    }
    $candidates = [$name];
    if (!str_contains($name, '.')) foreach (lab57_net_search_domains($w) as $domain) $candidates[] = $name . '.' . $domain;
    $servers = lab57_net_nameservers($w);
    $answered = false;
    foreach ($servers as $server) {
        $reach = lab57_dns_server_reach($w, $server);
        if (!$reach['ok']) continue;
        $answered = true;
        foreach ($candidates as $candidate) {
            $rec = lab57_dns_lookup($w, $candidate, 'A');
            if ($rec['values'] !== []) return ['ip' => (string)$rec['values'][0], 'error' => null, 'via' => 'dns', 'server' => $server];
        }
        return ['ip' => null, 'error' => 'nxdomain', 'via' => 'dns', 'server' => $server];
    }
    return ['ip' => null, 'error' => $answered ? 'nxdomain' : 'noservers', 'via' => 'dns', 'server' => null];
}

function lab57_net_name_for(Lab57World $w, string $ip): ?string
{
    $node = lab57_net_node_by_ip($w, $ip);
    return $node !== null && (string)($node[1]['name'] ?? '') !== '' && ($node[1]['kind'] ?? '') !== 'switch' ? (string)$node[1]['name'] : null;
}

/** Záznam pro animaci topologie v UI. */
function lab57_net_effect(Lab57World $w, string $kind, array $path, string $label): void
{
    $w->effects['net'][] = [
        'kind' => $kind,
        'hops' => array_values(array_merge(['pc'], array_values(array_filter((array)$path['hops'], static fn(string $h): bool => $h !== 'pc')))),
        'ok' => (bool)$path['ok'],
        'fail' => $path['fail'] ?? null,
        'label' => $label,
    ];
    if (count($w->effects['net']) > 6) $w->effects['net'] = array_slice($w->effects['net'], -6);
}

function lab57_jitter(Lab57World $w, float $ms, int $seq): float
{
    $r = (crc32($w->seed . '|' . count($w->history) . '|' . $seq) % 1000) / 1000;
    return max(0.021, $ms * (0.92 + $r * 0.16));
}

function lab57_resolve_error_tip(Lab57World $w, array $resolved, string $host): void
{
    if ($resolved['error'] === 'noservers') {
        $w->tip(tr('Jméno „{jmeno}“ nešlo přeložit na IP adresu – žádný DNS server neodpověděl. Zkus ping na IP adresu (např. 1.1.1.1) a podívej se do /etc/resolv.conf.', ['jmeno' => $host]));
    } else {
        $w->tip(tr('DNS server odpověděl, že jméno „{jmeno}“ neexistuje. Zkontroluj překlep.', ['jmeno' => $host]));
    }
}

// ---------------------------------------------------------------------------
// ip, ifconfig
// ---------------------------------------------------------------------------

function lab57_cmd_ip(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_slice($argv, 1);
    $brief = false;
    $family = null;
    while ($args !== [] && str_starts_with($args[0], '-')) {
        $flag = (string)array_shift($args);
        if ($flag === '-br' || $flag === '-brief') $brief = true;
        elseif ($flag === '-4') $family = 4;
        elseif ($flag === '-6') $family = 6;
        elseif ($flag === '-c' || $flag === '-color') continue;
        else { $p->err("Option \"$flag\" is unknown, try \"ip -help\".\n"); return 255; }
    }
    $object = (string)($args[0] ?? '');
    $sub = array_slice($args, 1);
    if ($object === '') { $p->err("Usage: ip [ OPTIONS ] OBJECT { COMMAND | help }\nwhere  OBJECT := { address | link | neighbour | route }\n"); return 255; }
    $match = static fn(string $full, string $abbr): bool => $abbr !== '' && str_starts_with($full, $abbr);
    if ($match('address', $object) || $object === 'a' || $object === 'addr') return lab57_ip_addr($p, $sub, $brief, $family);
    if ($match('link', $object) || $object === 'l') return lab57_ip_link($p, $sub, $brief);
    if ($match('route', $object) || $object === 'r' || $object === 'ro') return lab57_ip_route($p, $sub);
    if ($match('neighbour', $object) || $match('neighbor', $object) || $object === 'n') {
        foreach ((array)($w->net['arp'] ?? []) as $ip => $mac) {
            $p->line($ip . ' dev eth0 lladdr ' . $mac . ' ' . ($ip === '10.0.0.1' ? 'REACHABLE' : 'STALE'));
        }
        return 0;
    }
    $p->err("Object \"$object\" is unknown, try \"ip help\".\n");
    return 255;
}

function lab57_iface_flags(Lab57World $w, string $name, array $iface): array
{
    $noCarrier = !empty($w->net['faults']['no_carrier'][$name]);
    if ($name === 'lo') return [empty($iface['up']) ? 'LOOPBACK' : 'LOOPBACK,UP,LOWER_UP', empty($iface['up']) ? 'DOWN' : 'UNKNOWN'];
    if (empty($iface['up'])) return ['BROADCAST,MULTICAST', 'DOWN'];
    if ($noCarrier) return ['NO-CARRIER,BROADCAST,MULTICAST,UP', 'DOWN'];
    return ['BROADCAST,MULTICAST,UP,LOWER_UP', 'UP'];
}

function lab57_ip_addr(Lab57Proc $p, array $sub, bool $brief, ?int $family): int
{
    $w = $p->w;
    $dev = null;
    if (($sub[0] ?? '') === 'show' || ($sub[0] ?? '') === 'list') array_shift($sub);
    if (in_array($sub[0] ?? '', ['add', 'del'], true)) {
        if (!$w->root) { $p->err("RTNETLINK answers: Operation not permitted\n"); $w->tip(tr('Síťovou konfiguraci mění správce: sudo ip addr …')); return 2; }
        $p->err("ip: změnu adres simulace v této úrovni nepodporuje\n");
        return 2;
    }
    if (($sub[0] ?? '') === 'dev') $dev = (string)($sub[1] ?? '');
    elseif (isset($sub[0])) $dev = (string)$sub[0];
    if ($dev !== null && !isset($w->net['ifaces'][$dev])) { $p->err("Device \"$dev\" does not exist.\n"); return 1; }
    foreach ((array)$w->net['ifaces'] as $name => $iface) {
        if ($dev !== null && $name !== $dev) continue;
        [$flags, $state] = lab57_iface_flags($w, (string)$name, $iface);
        $cidr = (string)$iface['ip'] . '/' . (int)$iface['prefix'];
        if ($brief) {
            $p->line(str_pad((string)$name, 16) . ' ' . str_pad($state, 14) . ' ' . ($family === 6 ? '' : $cidr . ' ') . ($family === 4 ? '' : (string)$iface['ip6']));
            continue;
        }
        $p->line((int)$iface['idx'] . ': ' . $name . ': <' . $flags . '> mtu ' . (int)$iface['mtu'] . ' qdisc ' . ($name === 'lo' ? 'noqueue' : 'fq_codel') . ' state ' . $state . ' group default qlen 1000');
        $p->line('    link/' . ($name === 'lo' ? 'loopback' : 'ether') . ' ' . $iface['mac'] . ' brd ' . ($name === 'lo' ? '00:00:00:00:00:00' : 'ff:ff:ff:ff:ff:ff'));
        if ($family !== 6 && (string)$iface['ip'] !== '') {
            $p->line('    inet ' . $cidr . ($name === 'lo' ? ' scope host lo' : ' brd ' . lab57_broadcast((string)$iface['ip'], (int)$iface['prefix']) . ' scope global dynamic ' . $name));
            $p->line('       valid_lft ' . ($name === 'lo' ? 'forever preferred_lft forever' : '3417sec preferred_lft 3417sec'));
        }
        if ($family !== 4 && (string)($iface['ip6'] ?? '') !== '') {
            $p->line('    inet6 ' . $iface['ip6'] . ' scope ' . ($name === 'lo' ? 'host noprefixroute' : 'link'));
            $p->line('       valid_lft forever preferred_lft forever');
        }
    }
    return 0;
}

function lab57_ip_link(Lab57Proc $p, array $sub, bool $brief): int
{
    $w = $p->w;
    if (($sub[0] ?? '') === 'set') {
        $rest = array_slice($sub, 1);
        if (($rest[0] ?? '') === 'dev') array_shift($rest);
        $dev = (string)($rest[0] ?? '');
        $state = (string)($rest[1] ?? '');
        if (!isset($w->net['ifaces'][$dev])) { $p->err("Cannot find device \"$dev\"\n"); return 1; }
        if (!in_array($state, ['up', 'down'], true)) { $p->err("Error: either \"dev\" is duplicate, or \"$state\" is a garbage.\n"); return 255; }
        if (!$w->root) { $p->err("RTNETLINK answers: Operation not permitted\n"); $w->tip(tr('Rozhraní zapíná a vypíná správce: sudo ip link set {rozhrani} {stav}', ['rozhrani' => $dev, 'stav' => $state])); return 2; }
        $w->net['ifaces'][$dev]['up'] = $state === 'up';
        $w->journalAdd('kernel', $dev . ': link ' . ($state === 'up' ? 'becomes ready' : 'is not ready'));
        return 0;
    }
    if (($sub[0] ?? '') === 'show') array_shift($sub);
    foreach ((array)$w->net['ifaces'] as $name => $iface) {
        if (isset($sub[0]) && $sub[0] !== $name && !(($sub[0] ?? '') === 'dev' && ($sub[1] ?? '') === $name)) continue;
        [$flags, $state] = lab57_iface_flags($w, (string)$name, $iface);
        if ($brief) { $p->line(str_pad((string)$name, 16) . ' ' . str_pad($state, 14) . ' ' . $iface['mac'] . ' <' . $flags . '>'); continue; }
        $p->line((int)$iface['idx'] . ': ' . $name . ': <' . $flags . '> mtu ' . (int)$iface['mtu'] . ' qdisc ' . ($name === 'lo' ? 'noqueue' : 'fq_codel') . ' state ' . $state . ' mode DEFAULT group default qlen 1000');
        $p->line('    link/' . ($name === 'lo' ? 'loopback' : 'ether') . ' ' . $iface['mac'] . ' brd ' . ($name === 'lo' ? '00:00:00:00:00:00' : 'ff:ff:ff:ff:ff:ff'));
    }
    return 0;
}

function lab57_ip_route(Lab57Proc $p, array $sub): int
{
    $w = $p->w;
    $action = (string)($sub[0] ?? 'show');
    if (in_array($action, ['show', 'list', 'ls'], true)) {
        foreach (lab57_net_routes($w) as $r) {
            $line = (string)$r['dst'] . (isset($r['via']) ? ' via ' . $r['via'] : '') . ' dev ' . $r['dev'];
            if (isset($r['proto'])) $line .= ' proto ' . $r['proto'];
            if (isset($r['scope'])) $line .= ' scope ' . $r['scope'];
            if (isset($r['src'])) $line .= ' src ' . $r['src'];
            if (isset($r['metric'])) $line .= ' metric ' . $r['metric'];
            $p->line($line);
        }
        return 0;
    }
    if (in_array($action, ['add', 'del', 'delete', 'replace', 'change'], true)) {
        if (!$w->root) { $p->err("RTNETLINK answers: Operation not permitted\n"); $w->tip(tr('Směrovací tabulku mění správce: sudo ip route {argumenty}', ['argumenty' => implode(' ', $sub)])); return 2; }
        $dst = (string)($sub[1] ?? '');
        if ($dst === '') { $p->err("Error: argument is missing.\n"); return 255; }
        $via = null;
        $dev = null;
        for ($i = 2; $i < count($sub); $i++) {
            if ($sub[$i] === 'via') $via = (string)($sub[++$i] ?? '');
            elseif ($sub[$i] === 'dev') $dev = (string)($sub[++$i] ?? '');
        }
        if ($dst !== 'default' && preg_match('/^\d+\.\d+\.\d+\.\d+(\/\d{1,2})?$/', $dst) !== 1) { $p->err("Error: any valid prefix is expected rather than \"$dst\".\n"); return 1; }
        if ($dst !== 'default' && !str_contains($dst, '/')) $dst .= '/32';
        $routes = (array)$w->net['routes'];
        $idx = null;
        foreach ($routes as $k => $r) if ((string)$r['dst'] === $dst) { $idx = $k; break; }
        if ($action === 'del' || $action === 'delete') {
            if ($idx === null) { $p->err("RTNETLINK answers: No such process\n"); return 2; }
            unset($routes[$idx]);
            $w->net['routes'] = array_values($routes);
            return 0;
        }
        if ($idx !== null && $action === 'add') { $p->err("RTNETLINK answers: File exists\n"); $w->tip(tr('Tahle trasa už existuje. Zobrazíš je ip route; nahradit ji jde přes ip route replace.')); return 2; }
        if ($via !== null) {
            if (filter_var($via, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) { $p->err("Error: inet address is expected rather than \"$via\".\n"); return 1; }
            $connected = null;
            foreach ((array)$w->net['ifaces'] as $name => $iface) {
                if ($name === 'lo' || (string)$iface['ip'] === '') continue;
                if (lab57_ip_in($via, $iface['ip'] . '/' . $iface['prefix'])) { $connected = (string)$name; break; }
            }
            if ($connected === null) { $p->err("Error: Nexthop has invalid gateway.\n"); $w->tip(tr('Brána musí ležet ve stejné síti jako tvoje rozhraní (ip a ukáže adresu a masku).')); return 2; }
            $dev ??= $connected;
        }
        if ($dev === null || !isset($w->net['ifaces'][$dev])) { $p->err("Error: Device for nexthop is not up.\n"); return 2; }
        $route = ['dst' => $dst, 'dev' => $dev] + ($via !== null ? ['via' => $via] : ['scope' => 'link']);
        if ($idx !== null) $routes[$idx] = $route; else array_unshift($routes, $route);
        $w->net['routes'] = array_values($routes);
        return 0;
    }
    if ($action === 'get') {
        $ip = (string)($sub[1] ?? '');
        $path = lab57_net_path($w, $ip);
        if ($path['error'] === 'unreachable') { $p->err("RTNETLINK answers: Network is unreachable\n"); return 2; }
        $p->line($ip . ($path['via'] ? ' via ' . $path['via'] : '') . ' dev ' . ($path['dev'] ?? 'eth0') . ' src ' . (string)($w->net['ifaces']['eth0']['ip'] ?? '') . ' uid ' . $w->uid($w->user));
        return 0;
    }
    $p->err("Command \"$action\" is unknown, try \"ip route help\".\n");
    return 255;
}

function lab57_cmd_ifconfig(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    foreach ((array)$w->net['ifaces'] as $name => $iface) {
        if (empty($iface['up']) && !in_array('-a', $argv, true)) continue;
        $flags = $name === 'lo' ? '73<UP,LOOPBACK,RUNNING>' : (empty($iface['up']) ? '4098<BROADCAST,MULTICAST>' : '4163<UP,BROADCAST,RUNNING,MULTICAST>');
        $p->line($name . ': flags=' . $flags . '  mtu ' . $iface['mtu']);
        if ((string)$iface['ip'] !== '') $p->line('        inet ' . $iface['ip'] . '  netmask ' . lab57_prefix_mask((int)$iface['prefix']) . ($name === 'lo' ? '' : '  broadcast ' . lab57_broadcast((string)$iface['ip'], (int)$iface['prefix'])));
        $p->line('        ' . ($name === 'lo' ? 'loop  txqueuelen 1000  (Local Loopback)' : 'ether ' . $iface['mac'] . '  txqueuelen 1000  (Ethernet)'));
        $p->line('        RX packets ' . (18234 + (int)$iface['idx'] * 11) . '  bytes 20411872 (19.4 MiB)');
        $p->line('        TX packets ' . (9121 + (int)$iface['idx'] * 7) . '  bytes 1311450 (1.2 MiB)');
        $p->line('');
    }
    $w->tip(tr('ifconfig je starší nástroj z balíčku net-tools. Dnes se používá ip a (adresy) a ip route (trasy).'));
    return 0;
}

// ---------------------------------------------------------------------------
// ping, traceroute
// ---------------------------------------------------------------------------

function lab57_cmd_ping(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'c:W:w:i:s:q46n', []);
    if ($error !== null) { $p->err("ping: " . $error . "\n"); return 2; }
    $host = (string)($ops[0] ?? '');
    if ($host === '') { $p->err("ping: usage error: Destination address required\n"); return 1; }
    $count = isset($o['c']) ? max(1, min(20, (int)$o['c'])) : 4;
    if (!isset($o['c'])) $w->tip(tr('Skutečný ping běží, dokud ho nezastavíš Ctrl+C. Simulace pošle 4 pakety (jako ping -c 4).'));
    $resolved = lab57_net_resolve($w, $host);
    if ($resolved['ip'] === null) {
        $p->err('ping: ' . $host . ': ' . ($resolved['error'] === 'nxdomain' ? 'Name or service not known' : 'Temporary failure in name resolution') . "\n");
        lab57_resolve_error_tip($w, $resolved, $host);
        $w->effects['net'][] = ['kind' => 'dns', 'hops' => ['pc'], 'ok' => false, 'fail' => 'dns', 'label' => 'DNS: ' . $host];
        return 2;
    }
    $ip = (string)$resolved['ip'];
    $path = lab57_net_path($w, $ip);
    if ($path['error'] === 'unreachable') {
        $p->err("ping: connect: Network is unreachable\n");
        $w->tip(tr('Počítač nezná cestu do cílové sítě. Zkontroluj ip route – chybí výchozí brána (default via …)? Nebo je rozhraní vypnuté (ip link)?'));
        lab57_net_effect($w, 'ping', $path, 'ping ' . $host);
        return 2;
    }
    $display = $host === $ip ? $ip : $host;
    $p->line('PING ' . $display . ' (' . $ip . ') 56(84) bytes of data.');
    $name = lab57_net_name_for($w, $ip);
    $from = $name !== null && $host !== $ip ? $name . ' (' . $ip . ')' : $ip;
    $node = lab57_net_node_by_ip($w, $ip);
    $blocked = $node !== null && !empty($node[1]['no_icmp']);
    $times = [];
    $errors = 0;
    for ($seq = 1; $seq <= $count; $seq++) {
        if ($path['ok'] && !$blocked) {
            $ms = lab57_jitter($w, $path['ms'], $seq);
            $times[] = $ms;
            $ttl = lab57_net_is_local($w, $ip) ? 64 : max(1, (int)($node[1]['ttl'] ?? 64) - max(0, count($path['hops']) - 1));
            if (!isset($o['q'])) $p->line('64 bytes from ' . $from . ': icmp_seq=' . $seq . ' ttl=' . $ttl . ' time=' . ($ms < 1 ? number_format($ms, 3) : ($ms < 10 ? number_format($ms, 2) : number_format($ms, 1))) . ' ms');
        } elseif ($path['error'] === 'host_unreachable') {
            $errors++;
            if (!isset($o['q'])) $p->line('From ' . (string)($w->net['ifaces']['eth0']['ip'] ?? '10.0.0.23') . ' icmp_seq=' . $seq . ' Destination Host Unreachable');
        }
    }
    if ($path['ok'] && !$blocked && isset($resolved['ip']) && ($node[1]['zone'] ?? '') === 'lan') $w->net['arp'][$ip] = '52:54:00:' . substr(md5($ip), 0, 2) . ':' . substr(md5($ip), 2, 2) . ':' . substr(md5($ip), 4, 2);
    $received = count($times);
    $loss = (int)round(100 * ($count - $received) / $count);
    $p->line('');
    $p->line('--- ' . $display . ' ping statistics ---');
    $p->line($count . ' packets transmitted, ' . $received . ' received, ' . ($errors > 0 ? '+' . $errors . ' errors, ' : '') . $loss . '% packet loss, time ' . (($count - 1) * 1001 + 4) . 'ms');
    if ($times !== []) {
        $avg = array_sum($times) / count($times);
        $mdev = sqrt(array_sum(array_map(static fn(float $t): float => ($t - $avg) ** 2, $times)) / count($times));
        $p->line('rtt min/avg/max/mdev = ' . number_format(min($times), 3) . '/' . number_format($avg, 3) . '/' . number_format(max($times), 3) . '/' . number_format($mdev, 3) . ' ms');
    }
    lab57_net_effect($w, 'ping', $path + ['ok' => $path['ok'] && !$blocked], 'ping ' . $host);
    if ($received === 0) {
        if ($path['error'] === 'host_unreachable') $w->tip(tr('Cíl (nebo brána) v místní síti neodpovídá. Je zapojený kabel a zapnuté rozhraní? Zkus ip link a ip neigh.'));
        elseif ($blocked) $w->tip(tr('Na ping neodpovídá, ale to ještě neznamená, že nefunguje – některé servery ICMP blokují. Zkus curl nebo nc na jejich port.'));
        else $w->tip(tr('Pakety se někde po cestě ztrácí. Kde přesně, ukáže traceroute {cil}.', ['cil' => $host]));
        return 1;
    }
    return 0;
}

function lab57_cmd_traceroute(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $tracepath = $argv[0] === 'tracepath';
    $ops = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-')));
    $numeric = in_array('-n', $argv, true);
    $host = (string)($ops[0] ?? '');
    if ($host === '') { $p->err($tracepath ? "Usage: tracepath [-4] [-6] [-n] [-b] [-l <len>] [-p port] <destination>\n" : "Usage: traceroute [ -46dFITnreAUDV ] host [ packetlen ]\n"); return 2; }
    $resolved = lab57_net_resolve($w, $host);
    if ($resolved['ip'] === null) {
        $p->err($host . ': ' . ($resolved['error'] === 'nxdomain' ? 'Name or service not known' : 'Temporary failure in name resolution') . "\nCannot handle \"host\" cmdline arg `" . $host . "' on position 1 (argc 1)\n");
        lab57_resolve_error_tip($w, $resolved, $host);
        return 2;
    }
    $ip = (string)$resolved['ip'];
    $path = lab57_net_path($w, $ip);
    if (!$tracepath) $p->line('traceroute to ' . $host . ' (' . $ip . '), 30 hops max, 60 byte packets');
    if ($path['error'] === 'unreachable') { $p->err("connect: Network is unreachable\n"); lab57_net_effect($w, 'trace', $path, 'traceroute ' . $host); return 1; }
    $label = static function (string $id) use ($w, $numeric): string {
        $node = $w->net['nodes'][$id] ?? [];
        $ip = (string)($node['ip'] ?? '');
        return $numeric ? $ip : ((string)($node['name'] ?? $ip) . ' (' . $ip . ')');
    };
    $hop = 0;
    if ($tracepath) $p->line(' 1?: [LOCALHOST]                      pmtu 1500');
    foreach ($path['hops'] as $id) {
        if ($id === 'pc' || $id === 'switch') continue;
        $hop++;
        $ms = (float)($w->net['nodes'][$id]['lat'] ?? 1.0);
        if ($tracepath) $p->line(str_pad((string)$hop, 2, ' ', STR_PAD_LEFT) . ':  ' . str_pad($label($id), 48) . number_format(lab57_jitter($w, $ms, $hop), 3) . 'ms');
        else $p->line(str_pad((string)$hop, 2, ' ', STR_PAD_LEFT) . '  ' . $label($id) . '  ' . number_format(lab57_jitter($w, $ms, $hop * 3), 3) . ' ms  ' . number_format(lab57_jitter($w, $ms, $hop * 3 + 1), 3) . ' ms  ' . number_format(lab57_jitter($w, $ms, $hop * 3 + 2), 3) . ' ms');
    }
    if (!$path['ok']) {
        if ($path['error'] === 'host_unreachable' && $hop === 0) {
            $p->line(' 1  ' . (string)($w->net['ifaces']['eth0']['ip'] ?? '10.0.0.23') . ' (' . (string)($w->net['ifaces']['eth0']['ip'] ?? '10.0.0.23') . ')  3059.140 ms !H  3059.110 ms !H  3059.098 ms !H');
        } else {
            for ($k = $hop + 1; $k <= min($hop + 5, 30); $k++) $p->line(str_pad((string)$k, 2, ' ', STR_PAD_LEFT) . ($tracepath ? ':  no reply' : '  * * *'));
            $w->tip(tr('Hvězdičky * * * znamenají, že od tohoto skoku už nepřišla odpověď. Problém je mezi posledním odpovídajícím routerem a dalším. (Skutečný traceroute by zkoušel až 30 skoků.)'));
        }
    } elseif ($tracepath) {
        $p->line('     Resume: pmtu 1500 hops ' . $hop . ' back ' . $hop);
    }
    lab57_net_effect($w, 'trace', $path, 'traceroute ' . $host);
    return $path['ok'] ? 0 : 1;
}

// ---------------------------------------------------------------------------
// DNS: dig, nslookup, host
// ---------------------------------------------------------------------------

function lab57_dns_query(Lab57World $w, string $name, string $type, ?string $server): array
{
    $servers = $server !== null ? [$server] : lab57_net_nameservers($w);
    foreach ($servers as $srv) {
        $reach = lab57_dns_server_reach($w, $srv);
        if (!$reach['ok']) continue;
        $rec = lab57_dns_lookup($w, $name, $type);
        return ['server' => $srv, 'status' => $rec['exists'] ? 'NOERROR' : 'NXDOMAIN', 'rec' => $rec, 'ms' => max(1, (int)round($reach['ms'])), 'path' => $reach['path'], 'tried' => $servers];
    }
    return ['server' => null, 'status' => 'TIMEOUT', 'rec' => null, 'ms' => 0, 'path' => ['ok' => false, 'hops' => [], 'fail' => 'dns'], 'tried' => $servers];
}

function lab57_dns_rdata(string $type, mixed $value): string
{
    if ($type === 'MX' && is_array($value)) return $value[0] . ' ' . $value[1] . '.';
    if (in_array($type, ['NS', 'CNAME'], true)) return (string)$value . '.';
    return (string)$value;
}

function lab57_cmd_dig(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $server = null;
    $short = false;
    $type = 'A';
    $name = null;
    $reverse = false;
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '@')) { $server = substr($arg, 1); continue; }
        if ($arg === '+short') { $short = true; continue; }
        if (str_starts_with($arg, '+')) continue;
        if ($arg === '-x') { $reverse = true; $type = 'PTR'; continue; }
        if (in_array(strtoupper($arg), ['A', 'AAAA', 'MX', 'NS', 'TXT', 'CNAME', 'SOA', 'ANY', 'PTR'], true) && $name !== null) { $type = strtoupper($arg); continue; }
        if ($name === null) { $name = $arg; continue; }
        if (in_array(strtoupper($arg), ['A', 'AAAA', 'MX', 'NS', 'TXT', 'CNAME', 'SOA', 'ANY'], true)) $type = strtoupper($arg);
    }
    if ($name === null) { $name = '.'; $type = 'NS'; }
    if ($server !== null && filter_var($server, FILTER_VALIDATE_IP) === false) {
        $resolvedServer = lab57_net_resolve($w, $server);
        $server = $resolvedServer['ip'];
        if ($server === null) { $p->err("dig: couldn't get address for '" . substr((string)$argv[1], 1) . "': not found\n"); return 10; }
    }
    $q = lab57_dns_query($w, $reverse ? 'ptr' : $name, $type === 'PTR' ? 'A' : $type, $server);
    lab57_net_effect($w, 'dns', $q['path'], 'dig ' . $name);
    if ($q['status'] === 'TIMEOUT') {
        foreach ($q['tried'] as $srv) $p->line(';; communications error to ' . $srv . '#53: timed out');
        if (!$short) {
            $p->line('');
            $p->line('; <<>> DiG 9.18.24-1-Debian <<>> ' . $name);
            $p->line(';; global options: +cmd');
        }
        $p->line(';; no servers could be reached');
        $w->tip(tr('Žádný DNS server z /etc/resolv.conf neodpověděl. Je adresa serveru správná a dostupná (ping)?'));
        return 9;
    }
    $answers = [];
    if ($reverse) {
        $ptr = lab57_net_name_for($w, $name);
        if ($ptr !== null) {
            $arpa = implode('.', array_reverse(explode('.', $name))) . '.in-addr.arpa.';
            $answers[] = [$arpa, 'PTR', $ptr . '.'];
        }
    } else {
        $rec = $q['rec'];
        if ($rec['cname'] !== null) $answers[] = [$name . '.', 'CNAME', $rec['cname'] . '.'];
        foreach ((array)$rec['values'] as $value) $answers[] = [$rec['name'] . '.', $type, lab57_dns_rdata($type, $value)];
    }
    if ($short) {
        foreach ($answers as [, , $data]) $p->line($data);
        return 0;
    }
    $status = $reverse ? ($answers === [] ? 'NXDOMAIN' : 'NOERROR') : $q['status'];
    $p->line('');
    $p->line('; <<>> DiG 9.18.24-1-Debian <<>> ' . implode(' ', array_slice($argv, 1)));
    $p->line(';; global options: +cmd');
    $p->line(';; Got answer:');
    $p->line(';; ->>HEADER<<- opcode: QUERY, status: ' . $status . ', id: ' . (crc32($name . $w->seed) % 65535));
    $p->line(';; flags: qr rd ra; QUERY: 1, ANSWER: ' . count($answers) . ', AUTHORITY: ' . ($status === 'NXDOMAIN' ? 1 : 0) . ', ADDITIONAL: 1');
    $p->line('');
    $p->line(';; QUESTION SECTION:');
    $p->line(';' . ($reverse ? implode('.', array_reverse(explode('.', $name))) . '.in-addr.arpa.' : rtrim($name, '.') . '.') . "\t\tIN\t" . $type);
    if ($answers !== []) {
        $p->line('');
        $p->line(';; ANSWER SECTION:');
        foreach ($answers as [$owner, $rtype, $data]) $p->line($owner . "\t" . ($rtype === 'CNAME' ? 300 : 3600) . "\tIN\t" . $rtype . "\t" . $data);
    } elseif ($status === 'NXDOMAIN') {
        $p->line('');
        $p->line(';; AUTHORITY SECTION:');
        $p->line(".\t\t\t86400\tIN\tSOA\ta.root-servers.net. nstld.verisign-grs.com. 2026092500 1800 900 604800 86400");
    }
    $p->line('');
    $p->line(';; Query time: ' . $q['ms'] . ' msec');
    $p->line(';; SERVER: ' . $q['server'] . '#53(' . $q['server'] . ') (UDP)');
    $p->line(';; WHEN: ' . date('D M d H:i:s T Y', $w->now));
    $p->line(';; MSG SIZE  rcvd: ' . (40 + 16 * count($answers)));
    return 0;
}

function lab57_cmd_nslookup(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $ops = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-')));
    $type = 'A';
    foreach (array_slice($argv, 1) as $arg) if (preg_match('/^-(?:type|query)=(\w+)$/i', $arg, $m) === 1) $type = strtoupper($m[1]);
    $name = (string)($ops[0] ?? '');
    if ($name === '') { $p->err("nslookup: interaktivní režim simulace nepodporuje – napiš např. nslookup www.example.com\n"); return 1; }
    $q = lab57_dns_query($w, $name, $type, isset($ops[1]) ? (string)$ops[1] : null);
    lab57_net_effect($w, 'dns', $q['path'], 'nslookup ' . $name);
    if ($q['status'] === 'TIMEOUT') { $p->line(';; connection timed out; no servers could be reached'); return 1; }
    $p->line('Server:		' . $q['server']);
    $p->line('Address:	' . $q['server'] . '#53');
    $p->line('');
    if ($q['status'] === 'NXDOMAIN' || $q['rec']['values'] === []) {
        $p->line('** server can\'t find ' . $name . ': ' . ($q['status'] === 'NXDOMAIN' ? 'NXDOMAIN' : 'NODATA'));
        return 1;
    }
    $p->line('Non-authoritative answer:');
    if ($q['rec']['cname'] !== null) $p->line($name . "\tcanonical name = " . $q['rec']['cname'] . '.');
    foreach ((array)$q['rec']['values'] as $value) {
        if ($type === 'MX') { $p->line($q['rec']['name'] . "\tmail exchanger = " . lab57_dns_rdata('MX', $value)); continue; }
        if ($type === 'NS') { $p->line($q['rec']['name'] . "\tnameserver = " . $value . '.'); continue; }
        if ($type === 'TXT') { $p->line($q['rec']['name'] . "\ttext = " . $value); continue; }
        $p->line('Name:	' . $q['rec']['name']);
        $p->line('Address: ' . $value);
    }
    return 0;
}

function lab57_cmd_host(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $ops = array_values(array_filter(array_slice($argv, 1), static fn(string $a): bool => !str_starts_with($a, '-')));
    $name = (string)($ops[0] ?? '');
    if ($name === '') { $p->err("Usage: host [-aCdilrTvVw] [-c class] [-N ndots] [-t type] [-W time]\n            [-R number] [-m flag] [-p port] hostname [server]\n"); return 1; }
    if (filter_var($name, FILTER_VALIDATE_IP) !== false) {
        $ptr = lab57_net_name_for($w, $name);
        if ($ptr === null) { $p->line('Host ' . implode('.', array_reverse(explode('.', $name))) . '.in-addr.arpa. not found: 3(NXDOMAIN)'); return 1; }
        $p->line(implode('.', array_reverse(explode('.', $name))) . '.in-addr.arpa domain name pointer ' . $ptr . '.');
        return 0;
    }
    $q = lab57_dns_query($w, $name, 'A', isset($ops[1]) ? (string)$ops[1] : null);
    lab57_net_effect($w, 'dns', $q['path'], 'host ' . $name);
    if ($q['status'] === 'TIMEOUT') { $p->line(';; connection timed out; no servers could be reached'); return 1; }
    if ($q['status'] === 'NXDOMAIN') { $p->line('Host ' . $name . ' not found: 3(NXDOMAIN)'); return 1; }
    if ($q['rec']['cname'] !== null) $p->line($name . ' is an alias for ' . $q['rec']['cname'] . '.');
    foreach ((array)$q['rec']['values'] as $value) $p->line($q['rec']['name'] . ' has address ' . $value);
    foreach ((array)lab57_dns_lookup($w, $q['rec']['name'], 'AAAA')['values'] as $value) $p->line($q['rec']['name'] . ' has IPv6 address ' . $value);
    foreach ((array)lab57_dns_lookup($w, $q['rec']['name'], 'MX')['values'] as $value) $p->line($q['rec']['name'] . ' mail is handled by ' . lab57_dns_rdata('MX', $value));
    return 0;
}

// ---------------------------------------------------------------------------
// Spojení na porty: HTTP (curl, wget), nc, ss
// ---------------------------------------------------------------------------

/** Naslouchající porty tohoto počítače: port → [proces, uživatel, pid]. */
function lab57_local_listeners(Lab57World $w): array
{
    $out = [];
    foreach ($w->services as $name => $svc) {
        if (empty($svc['active'])) continue;
        $ports = (array)($svc['ports'] ?? []);
        if ($name === 'nginx') $ports = array_keys(lab57_nginx_sites($w)) ?: $ports;
        foreach ($ports as $port) {
            $pid = null;
            foreach ($w->procs as $procPid => $proc) if ((string)($proc['service'] ?? '') === $name) { $pid = $procPid; break; }
            $out[(int)$port] = ['proc' => $name === 'ssh' ? 'sshd' : $name, 'user' => 'root', 'pid' => $pid ?? 0, 'addr' => '0.0.0.0'];
        }
    }
    foreach ((array)($w->mem['listen'] ?? []) as $port => $info) {
        $out[(int)$port] = ['proc' => (string)($info['proc'] ?? 'service'), 'user' => (string)($info['user'] ?? 'root'), 'pid' => (int)($info['pid'] ?? 0), 'addr' => (string)($info['addr'] ?? '127.0.0.1')];
    }
    ksort($out);
    return $out;
}

/**
 * Otevře TCP spojení na host:port. @return array{ok:bool,error:?string,ip:?string,local:bool,path:array,ms:float}
 * error: resolve|nxdomain|unreachable|timeout|refused|host_unreachable
 */
function lab57_net_connect(Lab57World $w, string $host, int $port): array
{
    $resolved = lab57_net_resolve($w, $host);
    if ($resolved['ip'] === null) return ['ok' => false, 'error' => $resolved['error'] === 'nxdomain' ? 'nxdomain' : 'resolve', 'ip' => null, 'local' => false, 'path' => ['ok' => false, 'hops' => [], 'fail' => 'dns'], 'ms' => 0.0];
    $ip = (string)$resolved['ip'];
    $path = lab57_net_path($w, $ip);
    if (!$path['ok']) return ['ok' => false, 'error' => (string)$path['error'], 'ip' => $ip, 'local' => false, 'path' => $path, 'ms' => 0.0];
    if (lab57_net_is_local($w, $ip)) {
        $listeners = lab57_local_listeners($w);
        $open = isset($listeners[$port]) && ($listeners[$port]['addr'] !== '127.0.0.1' || str_starts_with($ip, '127.'));
        return ['ok' => $open, 'error' => $open ? null : 'refused', 'ip' => $ip, 'local' => true, 'path' => $path, 'ms' => 0.05];
    }
    $node = lab57_net_node_by_ip($w, $ip);
    $ports = (array)($node[1]['ports'] ?? []);
    $blocked = in_array($port, (array)($node[1]['blocked'] ?? []), true);
    if ($blocked) return ['ok' => false, 'error' => 'timeout', 'ip' => $ip, 'local' => false, 'path' => ['ok' => false, 'fail' => $node[0] ?? null] + $path, 'ms' => 0.0];
    $open = isset($ports[$port]);
    return ['ok' => $open, 'error' => $open ? null : 'refused', 'ip' => $ip, 'local' => false, 'path' => $path, 'ms' => $path['ms']];
}

/** HTTP odpověď cíle. @return array{0:int,1:array<string,string>,2:string} */
function lab57_http_fetch(Lab57World $w, array $conn, string $host, int $port, string $path): array
{
    if ($conn['local']) {
        if (isset($w->netServices['127.0.0.1:' . $port])) {
            $body = (string)($w->netServices['127.0.0.1:' . $port])("GET $path HTTP/1.1\r\nHost: $host\r\n\r\n", $w);
            return [200, ['Content-Type' => 'text/plain'], $body];
        }
        [$status, $type, $body] = lab57_nginx_serve($w, $port, $path);
        return [$status, ['Server' => 'nginx/1.22.1', 'Content-Type' => $type], $body];
    }
    $pages = (array)($w->net['http'][$conn['ip'] . ':' . $port] ?? []);
    $cleanPath = (string)(parse_url($path, PHP_URL_PATH) ?: '/');
    $entry = $pages[$cleanPath] ?? null;
    if ($entry === null) {
        return [404, ['Server' => 'nginx', 'Content-Type' => 'text/html'], "<html><head><title>404 Not Found</title></head><body><h1>404 Not Found</h1></body></html>\n"];
    }
    $headers = ['Server' => (string)($entry[4] ?? 'nginx'), 'Content-Type' => (string)$entry[1]] + (array)($entry[3] ?? []);
    return [(int)$entry[0], $headers, (string)$entry[2]];
}

function lab57_http_reason(int $status): string
{
    return [200 => 'OK', 301 => 'Moved Permanently', 302 => 'Found', 403 => 'Forbidden', 404 => 'Not Found', 500 => 'Internal Server Error', 502 => 'Bad Gateway', 503 => 'Service Unavailable'][$status] ?? 'OK';
}

/** @return array{scheme:string,host:string,port:int,path:string}|null */
function lab57_parse_url(string $url): ?array
{
    if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) $url = 'http://' . $url;
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) return null;
    $scheme = strtolower((string)($parts['scheme'] ?? 'http'));
    if (!in_array($scheme, ['http', 'https'], true)) return null;
    return ['scheme' => $scheme, 'host' => strtolower((string)$parts['host']), 'port' => (int)($parts['port'] ?? ($scheme === 'https' ? 443 : 80)), 'path' => (string)($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '')];
}

function lab57_curl_error(Lab57Proc $p, array $conn, array $url): int
{
    $w = $p->w;
    $target = $url['host'];
    switch ($conn['error']) {
        case 'resolve':
        case 'nxdomain':
            $p->err('curl: (6) Could not resolve host: ' . $target . "\n");
            lab57_resolve_error_tip($w, ['error' => $conn['error'] === 'nxdomain' ? 'nxdomain' : 'noservers'], $target);
            return 6;
        case 'refused':
            $p->err('curl: (7) Failed to connect to ' . $target . ' port ' . $url['port'] . ' after ' . max(0, (int)$conn['ms']) . " ms: Couldn't connect to server\n");
            $w->tip(tr('Počítač odpověděl, ale na portu {port} nic neposlouchá (Connection refused). Běží služba? Poslouchá na jiném portu? Zkus ss -tlnp nebo systemctl status.', ['port' => $url['port']]));
            return 7;
        case 'unreachable':
            $p->err('curl: (7) Failed to connect to ' . $target . ' port ' . $url['port'] . " after 0 ms: Couldn't connect to server\n");
            $w->tip(tr('Síť je nedosažitelná – zkontroluj ip route a ip link.'));
            return 7;
        default:
            $p->err('curl: (28) Failed to connect to ' . $target . ' port ' . $url['port'] . " after 3001 ms: Timeout was reached\n");
            $w->tip(tr('Spojení vypršelo – odpověď nepřišla. Pomůže ping a traceroute na {cil}.', ['cil' => $target]));
            return 28;
    }
}

function lab57_cmd_curl(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'IiLsSvo:OkfH:X:A:m:', ['head' => false, 'include' => false, 'location' => false, 'silent' => false, 'verbose' => false, 'output' => true, 'insecure' => false, 'max-time' => true]);
    if ($error !== null) { $p->err('curl: ' . $error . "\ncurl: try 'curl --help' or 'curl --manual' for more information\n"); return 2; }
    $raw = (string)($ops[0] ?? '');
    if ($raw === '') { $p->err("curl: try 'curl --help' or 'curl --manual' for more information\n"); return 2; }
    $url = lab57_parse_url($raw);
    if ($url === null) { $p->err("curl: (1) Protocol \"" . strtok($raw, ':') . "\" not supported\n"); return 1; }
    $head = isset($o['I']) || isset($o['--head']);
    $include = isset($o['i']) || isset($o['--include']);
    $follow = isset($o['L']) || isset($o['--location']);
    $silent = isset($o['s']) || isset($o['--silent']);
    $verbose = isset($o['v']) || isset($o['--verbose']);
    $outFile = $o['o'] ?? $o['--output'] ?? (isset($o['O']) ? (basename((string)parse_url($raw, PHP_URL_PATH)) ?: 'index.html') : null);
    $hops = 0;
    while (true) {
        $conn = lab57_net_connect($w, $url['host'], $url['port']);
        lab57_net_effect($w, 'http', $conn['path'] + ['ok' => $conn['ok']], 'curl ' . $url['host'] . ':' . $url['port']);
        if ($verbose) $p->err('*   Trying ' . ($conn['ip'] ?? $url['host']) . ':' . $url['port'] . "...\n");
        if (!$conn['ok']) return lab57_curl_error($p, $conn, $url);
        [$status, $headers, $body] = lab57_http_fetch($w, $conn, $url['host'], $url['port'], $url['path']);
        $proto = $url['scheme'] === 'https' ? 'HTTP/2' : 'HTTP/1.1';
        $statusLine = $proto . ' ' . $status . ($proto === 'HTTP/2' ? ' ' : ' ' . lab57_http_reason($status));
        $allHeaders = array_merge(['Date' => gmdate('D, d M Y H:i:s', $w->now) . ' GMT'], $headers, ['Content-Length' => (string)strlen($body)]);
        if ($verbose) {
            $p->err('* Connected to ' . $url['host'] . ' (' . $conn['ip'] . ') port ' . $url['port'] . "\n");
            $p->err('> ' . ($head ? 'HEAD' : 'GET') . ' ' . $url['path'] . " HTTP/1.1\n> Host: " . $url['host'] . "\n> User-Agent: curl/7.88.1\n> Accept: */*\n>\n");
            $p->err('< ' . $statusLine . "\n");
            foreach ($allHeaders as $k => $v) $p->err('< ' . $k . ': ' . $v . "\n");
            $p->err("<\n");
        }
        if ($follow && in_array($status, [301, 302, 307, 308], true) && isset($headers['Location']) && $hops < 5) {
            if ($include || $head) {
                $p->out($statusLine . "\n");
                foreach ($allHeaders as $k => $v) $p->out(strtolower($proto) === 'http/2' ? strtolower($k) . ': ' . $v . "\n" : $k . ': ' . $v . "\n");
                $p->out("\n");
            }
            $next = lab57_parse_url((string)$headers['Location']);
            if ($next === null) break;
            $url = $next;
            $hops++;
            continue;
        }
        $headerText = $statusLine . "\n";
        foreach ($allHeaders as $k => $v) $headerText .= ($proto === 'HTTP/2' ? strtolower($k) : $k) . ': ' . $v . "\n";
        $headerText .= "\n";
        $payload = $head ? $headerText : (($include ? $headerText : '') . $body);
        if ($outFile !== null) {
            $err = null;
            if (!$w->writeFile((string)$outFile, $payload, false, $err)) { $p->err('curl: (23) Failure writing output to destination, passed ' . strlen($payload) . " returned 0\n"); return 23; }
            if (!$silent) $p->err("  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current\n                                 Dload  Upload   Total   Spent    Left  Speed\n100 " . str_pad((string)strlen($payload), 5, ' ', STR_PAD_LEFT) . '  100 ' . str_pad((string)strlen($payload), 5, ' ', STR_PAD_LEFT) . "    0     0  51000      0 --:--:-- --:--:-- --:--:-- 51000\n");
            return 0;
        }
        $p->out($payload);
        if ($status >= 400 && isset($o['f'])) return 22;
        return 0;
    }
    return 0;
}

function lab57_cmd_wget(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'qO:S', ['quiet' => false, 'output-document' => true]);
    if ($error !== null) { $p->err('wget: ' . $error . "\nUsage: wget [OPTION]... [URL]...\n"); return 2; }
    $raw = (string)($ops[0] ?? '');
    if ($raw === '') { $p->err("wget: missing URL\nUsage: wget [OPTION]... [URL]...\n"); return 1; }
    $url = lab57_parse_url($raw);
    if ($url === null) { $p->err($raw . ": Unsupported scheme.\n"); return 1; }
    $quiet = isset($o['q']) || isset($o['--quiet']);
    $target = $o['O'] ?? $o['--output-document'] ?? null;
    $log = static function (string $text) use ($p, $quiet): void { if (!$quiet) $p->err($text . "\n"); };
    $log('--' . date('Y-m-d H:i:s', $w->now) . '--  ' . $url['scheme'] . '://' . $url['host'] . ($url['port'] !== ($url['scheme'] === 'https' ? 443 : 80) ? ':' . $url['port'] : '') . $url['path']);
    $conn = lab57_net_connect($w, $url['host'], $url['port']);
    lab57_net_effect($w, 'http', $conn['path'] + ['ok' => $conn['ok']], 'wget ' . $url['host']);
    if (in_array($conn['error'], ['resolve', 'nxdomain'], true)) { $log('Resolving ' . $url['host'] . ' (' . $url['host'] . ')... failed: ' . ($conn['error'] === 'nxdomain' ? 'Name or service not known' : 'Temporary failure in name resolution') . '.'); $log("wget: unable to resolve host address ‘{$url['host']}’"); return 4; }
    $log('Resolving ' . $url['host'] . ' (' . $url['host'] . ')... ' . $conn['ip']);
    $log('Connecting to ' . $url['host'] . ' (' . $url['host'] . ')|' . $conn['ip'] . '|:' . $url['port'] . '... ' . ($conn['ok'] ? 'connected.' : 'failed: ' . ($conn['error'] === 'refused' ? 'Connection refused.' : ($conn['error'] === 'unreachable' ? 'Network is unreachable.' : 'Connection timed out.'))));
    if (!$conn['ok']) return 4;
    [$status, $headers, $body] = lab57_http_fetch($w, $conn, $url['host'], $url['port'], $url['path']);
    $log('HTTP request sent, awaiting response... ' . $status . ' ' . lab57_http_reason($status));
    if ($status >= 400) { $log(date('Y-m-d H:i:s', $w->now) . ' ERROR ' . $status . ': ' . lab57_http_reason($status) . '.'); return 8; }
    if ($target === '-') { $p->out($body); return 0; }
    $name = $target ?? (basename((string)parse_url($url['path'], PHP_URL_PATH)) ?: 'index.html');
    if ($target === null) {
        $base = $name;
        for ($i = 1; $w->fs->exists($w->abs($name)) && $i < 50; $i++) $name = $base . '.' . $i;
    }
    $log('Length: ' . strlen($body) . ' [' . ($headers['Content-Type'] ?? 'text/html') . ']');
    $log('Saving to: ‘' . $name . '’');
    $log('');
    $err = null;
    if (!$w->writeFile($name, $body, false, $err)) { $log($name . ': ' . $err); return 3; }
    $log(str_pad($name, 20) . '100%[===================>]  ' . str_pad((string)strlen($body), 6, ' ', STR_PAD_LEFT) . '  --.-KB/s    in 0s');
    $log('');
    $log(date('Y-m-d H:i:s', $w->now) . ' (51.2 MB/s) - ‘' . $name . '’ saved [' . strlen($body) . '/' . strlen($body) . ']');
    return 0;
}

function lab57_service_name(int $port): string
{
    return [22 => 'ssh', 25 => 'smtp', 53 => 'domain', 80 => 'http', 443 => 'https', 631 => 'ipp', 3306 => 'mysql', 8080 => 'http-alt'][$port] ?? (string)$port;
}

function lab57_cmd_ss(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $flags = '';
    foreach (array_slice($argv, 1) as $arg) {
        if (str_starts_with($arg, '-') && !str_starts_with($arg, '--')) $flags .= substr($arg, 1);
    }
    $listening = str_contains($flags, 'l') || str_contains($flags, 'a');
    $numeric = str_contains($flags, 'n');
    $procs = str_contains($flags, 'p');
    $udp = str_contains($flags, 'u');
    $tcp = str_contains($flags, 't') || !$udp;
    $p->line('Netid State  Recv-Q Send-Q Local Address:Port  Peer Address:Port Process');
    if (!$listening) return 0;
    $hidden = false;
    foreach (lab57_local_listeners($w) as $port => $info) {
        if (!$tcp) continue;
        $local = $info['addr'] . ':' . ($numeric ? (string)$port : lab57_service_name($port));
        $proc = '';
        if ($procs) {
            if ($w->root || $info['user'] === $w->user) $proc = 'users:(("' . $info['proc'] . '",pid=' . $info['pid'] . ',fd=' . (3 + $port % 7) . '))';
            else $hidden = true;
        }
        $p->line('tcp   LISTEN 0      ' . str_pad('511', 6) . ' ' . str_pad($local, 18, ' ', STR_PAD_LEFT) . ' ' . str_pad('0.0.0.0:*', 17, ' ', STR_PAD_LEFT) . ' ' . $proc);
    }
    if ($udp) $p->line('udp   UNCONN 0      0            0.0.0.0:' . ($numeric ? '68' : 'bootpc') . '          0.0.0.0:*');
    if ($hidden) $w->tip(tr('Které procesy porty drží, uvidíš jen jako správce: sudo ss -tulpn'));
    return 0;
}

function lab57_cmd_netstat(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $p->line('Active Internet connections (only servers)');
    $p->line('Proto Recv-Q Send-Q Local Address           Foreign Address         State       PID/Program name');
    foreach (lab57_local_listeners($w) as $port => $info) {
        $prog = ($w->root || $info['user'] === $w->user) ? $info['pid'] . '/' . $info['proc'] : '-';
        $p->line('tcp        0      0 ' . str_pad($info['addr'] . ':' . $port, 23) . ' ' . str_pad('0.0.0.0:*', 23) . ' LISTEN      ' . $prog);
    }
    $w->tip(tr('netstat je starší nástroj z balíčku net-tools, dnes se používá ss -tulpn.'));
    return 0;
}

function lab57_cmd_nc(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'zvnw:uN', []);
    if ($error !== null) { $p->err('nc: ' . $error . "\nusage: nc [-46CDdFhklNnrStUuvZz] [-I length] [-i interval] [-M ttl] [-m minttl] [-O length] [-P proxy_username] [-p source_port] [-q seconds] [-s sourceaddr] [-T keyword] [-V rtable] [-W recvlimit] [-w timeout] [-X proxy_protocol] [-x proxy_address[:port]] [destination] [port]\n"); return 1; }
    if (count($ops) < 2) { $p->err("usage: nc [-46CDdFhklNnrStUuvZz] [destination] [port]\n"); return 1; }
    [$host, $portRaw] = [(string)$ops[0], (string)$ops[1]];
    if (preg_match('/^\d{1,5}$/', $portRaw) !== 1) {
        $p->err("nc: simulace kontroluje vždy jeden konkrétní port (např. nc -zv $host 80)\n");
        return 1;
    }
    $port = (int)$portRaw;
    $verbose = isset($o['v']);
    $conn = lab57_net_connect($w, $host, $port);
    lab57_net_effect($w, 'tcp', $conn['path'] + ['ok' => $conn['ok']], 'nc ' . $host . ' ' . $port);
    if (in_array($conn['error'], ['resolve', 'nxdomain'], true)) { $p->err("nc: getaddrinfo for host \"$host\" port $port: " . ($conn['error'] === 'nxdomain' ? 'Name or service not known' : 'Temporary failure in name resolution') . "\n"); return 1; }
    if (!$conn['ok']) {
        if ($verbose || isset($o['z'])) $p->err('nc: connect to ' . $host . ' (' . $conn['ip'] . ') port ' . $port . ' (tcp) failed: ' . ($conn['error'] === 'refused' ? 'Connection refused' : ($conn['error'] === 'unreachable' ? 'Network is unreachable' : 'Connection timed out')) . "\n");
        return 1;
    }
    if (isset($o['z'])) {
        if ($verbose) $p->err('Connection to ' . $host . ' (' . $conn['ip'] . ') ' . $port . ' port [tcp/' . lab57_service_name($port) . "] succeeded!\n");
        return 0;
    }
    if ($verbose) $p->err('Connection to ' . $host . ' (' . $conn['ip'] . ') ' . $port . ' port [tcp/' . lab57_service_name($port) . "] succeeded!\n");
    $key = ($conn['local'] ? '127.0.0.1' : $conn['ip']) . ':' . $port;
    if (isset($w->netServices[$key])) {
        if (!$p->hasStdin) { $w->tip(tr('Služba čeká na tvůj vstup. Pošli ho rourou: echo "text" | nc {hostitel} {port}', ['hostitel' => $host, 'port' => $port])); return 0; }
        $p->out((string)($w->netServices[$key])($p->stdin, $w));
        return 0;
    }
    if ($p->hasStdin && preg_match('/^(GET|HEAD) (\S+) HTTP/', $p->stdin, $m) === 1 && in_array($port, [80, 8080, 443], true)) {
        [$status, $headers, $body] = lab57_http_fetch($w, $conn, $host, $port, $m[2]);
        $out = 'HTTP/1.1 ' . $status . ' ' . lab57_http_reason($status) . "\r\n";
        foreach ($headers as $k => $v) $out .= $k . ': ' . $v . "\r\n";
        $p->out($out . "\r\n" . ($m[1] === 'HEAD' ? '' : $body));
        return 0;
    }
    if ($port === 22) { $p->out("SSH-2.0-OpenSSH_9.2p1 Debian-2+deb12u2\n"); return 0; }
    if ($port === 25) { $p->out('220 ' . $host . " ESMTP Postfix (Debian/GNU)\n"); return 0; }
    if (!$p->hasStdin) $w->tip(tr('Spojení je otevřené, ale služba nic neposlala. Zkus jí něco poslat rourou: echo "ahoj" | nc {hostitel} {port}', ['hostitel' => $host, 'port' => $port]));
    return 0;
}
