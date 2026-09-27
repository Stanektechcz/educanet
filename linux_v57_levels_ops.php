<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57/v58 · Linux Lab – balíčky úrovní (2/2): Síťový detektiv, Opravna serverů, Shell golf.
 *
 * v58 (CNT-01): stejně jako linux_v57_levels.php – úrovně jsou deklarativní (generate/checks/
 * answer/solution), žádné closures v datech úrovní. Sdílené síťové pomocníky (renumber LAN,
 * další WAN skoky, lan()/can_reach()/http_status()) používají generátory a kontroly v katalogu
 * core.* (linux_v58_levels_generators.php) – proto tu zůstávají jako obyčejné funkce.
 */

/** Přečísluje školní LAN 10.0.0.0/24 na 10.X.0.0/24 (každý žák má jinou síť). */
function lab57_net_renumber(Lab57World $w, int $x): void
{
    $from = '10.0.0.';
    $to = '10.' . $x . '.0.';
    $swap = static fn(string $s): string => str_replace($from, $to, $s);
    foreach ($w->net['ifaces'] as $name => $iface) $w->net['ifaces'][$name]['ip'] = $swap((string)$iface['ip']);
    foreach ($w->net['routes'] as $i => $route) {
        foreach (['dst', 'via', 'src'] as $key) if (isset($route[$key])) $w->net['routes'][$i][$key] = str_replace('10.0.0.0/24', '10.' . $x . '.0.0/24', $swap((string)$route[$key]));
    }
    foreach ($w->net['nodes'] as $id => $node) $w->net['nodes'][$id]['ip'] = $swap((string)($node['ip'] ?? ''));
    foreach ($w->net['dns'] as $name => $rec) foreach ((array)($rec['A'] ?? []) as $k => $ip) $w->net['dns'][$name]['A'][$k] = $swap((string)$ip);
    $newHttp = [];
    foreach ($w->net['http'] as $key => $pages) $newHttp[$swap((string)$key)] = $pages;
    $w->net['http'] = $newHttp;
    $arp = [];
    foreach ((array)$w->net['arp'] as $ip => $mac) $arp[$swap((string)$ip)] = $mac;
    $w->net['arp'] = $arp;
    $hosts = $w->fs->get('/etc/hosts');
    if ($hosts !== null) { $hosts['c'] = $swap((string)$hosts['c']); $w->fs->set('/etc/hosts', $hosts); }
    $w->mem['lan'] = $x;
}

/** Přidá mezi školu a páteř další routery (každý žák má jinak dlouhou cestu do internetu). */
function lab57_net_extra_hops(Lab57World $w, int $extra): void
{
    $path = ['router', 'isp'];
    for ($i = 1; $i <= $extra; $i++) {
        $id = 'hop' . $i;
        $w->net['nodes'][$id] = ['ip' => '100.64.' . $i . '.1', 'name' => 'r' . $i . '.poskytovatel.test', 'kind' => 'router', 'label' => 'Router ' . ($i + 1), 'zone' => 'wan', 'ttl' => 255, 'lat' => 6.2 + $i * 1.7, 'x' => 410 + $i * 30, 'y' => 150 + ($i % 2 ? -30 : 30), 'hidden' => true];
        $path[] = $id;
    }
    $path[] = 'ix';
    $w->net['wan_path'] = $path;
    $w->net['nodes']['ix']['lat'] = 10.8 + $extra * 1.7;
    foreach (['dns1', 'dns2', 'www', 'mail', 'mirror'] as $id) if (isset($w->net['nodes'][$id])) $w->net['nodes'][$id]['lat'] = (float)$w->net['nodes'][$id]['lat'] + $extra * 1.7;
}

function lab57_lan(Lab57World $w, int $host): string
{
    return '10.' . (int)($w->mem['lan'] ?? 0) . '.0.' . $host;
}

function lab57_can_reach(Lab57World $w, string $name): bool
{
    $resolved = lab57_net_resolve($w, $name);
    return $resolved['ip'] !== null && lab57_net_path($w, (string)$resolved['ip'])['ok'];
}

function lab57_http_status(Lab57World $w, string $host, int $port, string $path = '/'): int
{
    $conn = lab57_net_connect($w, $host, $port);
    if (!$conn['ok']) return 0;
    return lab57_http_fetch($w, $conn, $host, $port, $path)[0];
}

// ---------------------------------------------------------------------------
// SÍŤOVÝ DETEKTIV
// ---------------------------------------------------------------------------

function lab57_levels_sit(): array
{
    return [
        [
            'id' => 'sit-1', 'pack' => 'sit', 'type' => 'answer', 'title' => 'Moje IP adresa', 'minutes' => 3, 'topology' => true, 'v' => 2,
            'story' => 'Každé zařízení v síti má svou IP adresu. Tvůj počítač je připojený kabelem přes rozhraní eth0.',
            'task' => 'Zjisti IPv4 adresu rozhraní eth0 (bez /24) a odpověz: answer <adresa>',
            'commands' => ['ip', 'hostname', 'answer'],
            'hints' => ['Síťová rozhraní a jejich adresy vypíše ip a (ip address).', 'Hledej řádek inet u eth0. Část za lomítkem je maska sítě.', 'hostname -I ukáže adresu i bez masky.'],
            'generate' => [['core_net_lan', []]],
            'answer' => '{f:eth0_ip}',
            'answer_format' => 'IPv4 adresa, např. 10.1.0.25',
            'solution' => ['ip a', 'answer {f:eth0_ip}'],
            'learn' => 'ip a vypíše rozhraní a adresy. Lomítko /24 říká, že prvních 24 bitů adresy tvoří síť (maska 255.255.255.0).',
        ],
        [
            'id' => 'sit-2', 'pack' => 'sit', 'type' => 'answer', 'title' => 'Kudy vede cesta ven?', 'minutes' => 3, 'topology' => true, 'v' => 2,
            'story' => 'Do internetu posílá počítač pakety přes výchozí bránu – školní router.',
            'task' => 'Zjisti IP adresu výchozí brány a odpověz: answer <adresa>',
            'commands' => ['ip', 'ping', 'answer'],
            'hints' => ['Směrovací tabulku ukáže ip route (zkráceně ip r).', 'Řádek default via … říká, kam jdou pakety do ostatních sítí.'],
            'generate' => [['core_net_lan', []]],
            'answer' => '{f:gateway_ip}',
            'answer_format' => 'IPv4 adresa',
            'solution' => ['ip route', 'answer {f:gateway_ip}'],
            'learn' => 'Výchozí brána (default gateway) je router, přes který jde vše mimo tvou místní síť.',
        ],
        [
            'id' => 'sit-3', 'pack' => 'sit', 'type' => 'answer', 'title' => 'Kolik routerů do internetu?', 'difficulty' => 2, 'points' => 150, 'minutes' => 5, 'topology' => true, 'v' => 2,
            'story' => 'Cesta k webu www.example.com vede přes několik routerů. Každý z nich je jeden „skok“ (hop).',
            'task' => 'Kolik řádků (skoků) vypíše traceroute k www.example.com? Odpověz číslem.',
            'commands' => ['traceroute', 'ping', 'answer'],
            'hints' => ['traceroute ukáže každý router na cestě, po jednom řádku.', 'Poslední řádek je samotný cíl – počítá se také.'],
            'generate' => [['core_net_lan', []], ['core_net_extra_hops', ['min' => 0, 'max' => 3, 'fact' => 'hop_count']]],
            'answer' => '{f:hop_count}',
            'answer_format' => 'celé číslo',
            'solution' => ['traceroute www.example.com', 'answer {f:hop_count}'],
            'learn' => 'Každý router sníží TTL paketu o jedna. traceroute toho využívá a postupně „osahá“ celou cestu.',
        ],
        [
            'id' => 'sit-4', 'pack' => 'sit', 'type' => 'check', 'title' => 'Vypnuté rozhraní', 'minutes' => 5, 'topology' => true, 'v' => 2,
            'story' => 'Po údržbě nefunguje vůbec nic – ani ping na školní router. Kabel je v pořádku, ale rozhraní eth0 někdo vypnul.',
            'task' => 'Zapni rozhraní eth0 a ověř, že se dostaneš na www.example.com.',
            'commands' => ['ip', 'sudo', 'ping'],
            'hints' => ['Stav rozhraní ukáže ip link – hledej state DOWN.', 'Zapnout ho může jen správce: sudo ip link set eth0 up', 'Potom ověř ping -c 2 www.example.com'],
            'generate' => [['core_net_lan', []], ['core_net_iface_down', ['iface' => 'eth0']]],
            'checks' => [
                ['core_net_iface_up', ['iface' => 'eth0', 'label' => 'Rozhraní eth0 je zapnuté (UP)']],
                ['core_net_reachable', ['host' => '{f:gateway_ip}', 'label' => 'Školní router odpovídá']],
                ['core_net_reachable', ['host' => 'www.example.com', 'label' => 'www.example.com je dosažitelný']],
            ],
            'solution' => ['ip link', 'sudo ip link set eth0 up', 'ping -c 2 www.example.com'],
            'learn' => 'Diagnostika zdola: nejdřív linka (ip link), pak adresa (ip a), brána (ip r), DNS a nakonec služba.',
        ],
        [
            'id' => 'sit-5', 'pack' => 'sit', 'type' => 'check', 'title' => 'Chybí výchozí brána', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'topology' => true, 'v' => 2,
            'story' => 'Ping na školní router funguje, ale do internetu to hlásí „Network is unreachable“. Ze směrovací tabulky zmizela výchozí brána.',
            'task' => 'Přidej výchozí trasu přes školní router (adresa končí .1) a ověř přístup k 8.8.8.8.',
            'commands' => ['ip', 'sudo', 'ping'],
            'hints' => ['Porovnej ip route s tím, co znáš z úrovně „Kudy vede cesta ven“ – chybí řádek default.', 'Trasu přidá správce: sudo ip route add default via <adresa routeru>', 'Adresu routeru poznáš z ip a: stejné první tři čísla a na konci .1.'],
            'generate' => [['core_net_lan', []], ['core_net_remove_default_route', []]],
            'checks' => [
                ['core_net_has_default_route', ['label' => 'Směrovací tabulka má výchozí trasu']],
                ['core_net_reachable', ['host' => '8.8.8.8', 'label' => '8.8.8.8 je dosažitelný']],
            ],
            'solution' => ['ip route', 'sudo ip route add default via {f:gateway_ip}', 'ping -c 2 8.8.8.8'],
            'learn' => 'Bez výchozí brány zná počítač jen svou místní síť. Ve skutečnosti ji obvykle nastaví DHCP.',
        ],
        [
            'id' => 'sit-6', 'pack' => 'sit', 'type' => 'check', 'title' => 'DNS neodpovídá', 'difficulty' => 2, 'points' => 150, 'minutes' => 7, 'topology' => true, 'v' => 2,
            'story' => 'ping 1.1.1.1 funguje, ale ping www.example.com hlásí „Temporary failure in name resolution“. Počítač se ptá na jména špatného DNS serveru.',
            'task' => 'Oprav /etc/resolv.conf tak, aby používal DNS server 1.1.1.1, a ověř, že jméno www.example.com jde přeložit.',
            'commands' => ['cat', 'ping', 'dig', 'sudo', 'nano', 'tee'],
            'hints' => ['Který server se používá, je v souboru /etc/resolv.conf (řádek nameserver).', 'Soubor patří rootovi: sudo nano /etc/resolv.conf, nebo echo "nameserver 1.1.1.1" | sudo tee /etc/resolv.conf', 'Ověř dig www.example.com nebo ping www.example.com.'],
            'generate' => [['core_net_lan', []], ['core_net_bad_dns', ['bad_host' => 99]]],
            'checks' => [
                ['core_net_dns_has', ['ip' => '1.1.1.1', 'label' => 'resolv.conf obsahuje nameserver 1.1.1.1']],
                ['core_net_resolves', ['name' => 'www.example.com', 'label' => 'Jméno www.example.com se přeloží na IP']],
            ],
            'solution' => ['ping -c 1 1.1.1.1', 'cat /etc/resolv.conf', 'echo "nameserver 1.1.1.1" | sudo tee /etc/resolv.conf', 'dig +short www.example.com'],
            'learn' => 'DNS překládá jména na IP adresy. Když ping na IP funguje a na jméno ne, je chyba skoro vždy v DNS.',
        ],
        [
            'id' => 'sit-7', 'pack' => 'sit', 'type' => 'answer', 'title' => 'Kde se to ztrácí?', 'difficulty' => 3, 'points' => 200, 'minutes' => 7, 'topology' => true, 'v' => 2,
            'story' => 'Internet nejde, ale školní router odpovídá. Někde u poskytovatele je výpadek.',
            'task' => 'Zjisti IP adresu posledního routeru, který na cestě k www.example.com ještě odpovídá. Odpověz: answer <adresa>',
            'commands' => ['traceroute', 'ping', 'answer'],
            'hints' => ['traceroute ukáže routery na cestě; za výpadkem jsou jen hvězdičky * * *.', 'Odpovědí je adresa v závorce na posledním řádku, který ještě není * * *.'],
            'generate' => [['core_net_lan', []], ['core_net_extra_hops', ['min' => 1, 'max' => 3]], ['core_net_wan_fault', ['fact' => 'break_node']]],
            'answer' => '{f:break_node}',
            'answer_format' => 'IPv4 adresa routeru',
            'solution' => ['traceroute www.example.com', 'answer {f:break_node}'],
            'learn' => 'Hvězdičky v traceroute ukazují místo, kde se pakety ztrácí. S tímhle údajem voláš poskytovateli.',
        ],
        [
            'id' => 'sit-8', 'pack' => 'sit', 'type' => 'check', 'title' => 'Intranet se nenačte', 'difficulty' => 2, 'points' => 150, 'minutes' => 7, 'topology' => true, 'v' => 2,
            'story' => 'Školní intranet se přestěhoval na nový server, ale tvůj počítač má v /etc/hosts napevno starou adresu.',
            'task' => 'Oprav záznam pro intranet.skola.test v /etc/hosts na správnou adresu (zjistíš ji přes DNS) a ověř, že curl vrací 200.',
            'commands' => ['cat', 'dig', 'curl', 'sudo', 'sed', 'nano'],
            'hints' => ['/etc/hosts má přednost před DNS. Správnou adresu ti řekne dig +short intranet.skola.test.', 'Úprava souboru: sudo nano /etc/hosts nebo sudo sed -i s/stará/nová/ /etc/hosts', 'Ověř: curl -I http://intranet.skola.test'],
            'generate' => [['core_net_lan', []], ['core_net_hosts_mismatch', ['name' => 'intranet', 'right_host' => 10, 'wrong_host' => 11]]],
            'checks' => [
                ['core_net_resolves', ['name' => 'intranet.skola.test', 'to' => '{f:hosts_right_ip}', 'label' => 'intranet.skola.test se překládá na ' . '…10']],
                ['core_http_status', ['host' => 'intranet.skola.test', 'port' => 80, 'equals' => 200, 'label' => 'Web intranetu vrací HTTP 200']],
            ],
            'solution' => ['cat /etc/hosts', 'dig +short intranet.skola.test', "sudo sed -i 's/{f:hosts_wrong_ip_esc}/{f:hosts_right_ip}/' /etc/hosts", 'curl -I http://intranet.skola.test'],
            'learn' => 'Pořadí překladu jmen: nejdřív /etc/hosts, pak DNS. Zapomenutý záznam v hosts umí potrápit i profíky.',
        ],
        [
            'id' => 'sit-9', 'pack' => 'sit', 'type' => 'answer', 'title' => 'Veřejná adresa školy', 'minutes' => 4, 'topology' => true, 'v' => 2,
            'story' => 'Tvůj počítač má soukromou adresu 10.x.x.x. Servery v internetu ale vidí veřejnou adresu školního routeru (NAT).',
            'task' => 'Zjisti, jakou veřejnou adresu vidí internet – služba ifconfig.me ji vypíše. Odpověz: answer <adresa>',
            'commands' => ['curl', 'answer'],
            'hints' => ['Stačí stáhnout stránku ifconfig.me příkazem curl.', 'curl ifconfig.me'],
            'generate' => [['core_net_lan', []], ['core_net_public_ip', ['fact' => 'public_ip']]],
            'answer' => '{f:public_ip}',
            'answer_format' => 'IPv4 adresa',
            'solution' => ['curl ifconfig.me', 'answer {f:public_ip}'],
            'learn' => 'Soukromé adresy (10.x, 192.168.x, 172.16–31.x) se do internetu nedostanou. Router je přeloží na veřejnou (NAT).',
        ],
    ];
}

// ---------------------------------------------------------------------------
// OPRAVNA SERVERŮ – ve stylu SadServers
// ---------------------------------------------------------------------------

function lab57_levels_opravna(): array
{
    $srv = ['hostname' => 'srv-web', 'nginx' => true, 'sudo' => true];
    return [
        [
            'id' => 'opr-1', 'pack' => 'opravna', 'type' => 'check', 'title' => 'Web server stojí', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'world' => $srv, 'v' => 2,
            'story' => 'Po úpravě konfigurace se webový server nginx nerozběhl a školní web nejde. Správce odešel domů – je to na tobě.',
            'task' => 'Zjisti, proč nginx nenaběhl, oprav konfiguraci a službu spusť. Web na localhost musí vracet 200.',
            'commands' => ['systemctl', 'journalctl', 'nginx', 'sudo', 'nano', 'sed', 'curl'],
            'hints' => ['Začni systemctl status nginx a journalctl -u nginx – ukážou, že služba spadla.', 'sudo nginx -t zkontroluje konfiguraci a řekne soubor i číslo řádku s chybou.', 'Oprav řádek (sudo nano …), pak sudo systemctl restart nginx a ověř curl -I localhost.'],
            // parita: ne – nutné je náhodně rozbít jednu ze tří direktiv a zapsat konzistentní
            // žurnál/stav služby; pojmenovaný generátor v57_opr_1 (linux_v58_levels_generators.php).
            'generate' => [['v57_opr_1', []]],
            'checks' => [
                ['core_nginx_ok', ['label' => 'Konfigurace nginx je bez chyb (nginx -t)']],
                ['service_running', ['service' => 'nginx', 'label' => 'Služba nginx běží']],
                ['core_http_status', ['host' => 'localhost', 'port' => 80, 'equals' => 200, 'label' => 'http://localhost vrací 200']],
            ],
            'solution' => ['systemctl status nginx', 'sudo nginx -t', "sudo sed -i 's|{f:broken_directive}\$|{f:broken_directive};|' /etc/nginx/sites-enabled/default", 'sudo nginx -t', 'sudo systemctl restart nginx', 'curl -I localhost'],
            'learn' => 'Postup opravy služby: status → log → test konfigurace → oprava → restart → ověření. Funguje pro nginx, Apache i databáze.',
        ],
        [
            'id' => 'opr-2', 'pack' => 'opravna', 'type' => 'check', 'title' => 'Plný disk', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'world' => $srv, 'v' => 2,
            'story' => 'Aplikace zapisovac přestala ukládat data a spadla. V logu je „No space left on device“ – disk je plný.',
            'task' => 'Najdi, co zabírá místo, uvolni disk pod 90 % a službu zapisovac znovu spusť.',
            'commands' => ['df', 'du', 'ls', 'truncate', 'rm', 'systemctl', 'sudo'],
            'hints' => ['df -h ukáže zaplnění disků, du -sh složka velikost složky.', 'Hledej obří soubor v /var/log: sudo du -ah /var/log | sort -h | tail', 'Log vyprázdníš sudo truncate -s 0 <soubor>, pak sudo systemctl restart zapisovac.'],
            // parita: ne – náhodný název přerostlého logu; pojmenovaný generátor v57_opr_2.
            // 'programs' zůstává closure (dokumentovaný v58 hák pro simulované programy služeb,
            // stejné odůvodnění jako 'net' u quest-11) – přepočítává se při každé stavbě světa.
            'generate' => [['v57_opr_2', []]],
            'programs' => static function (Lab57World $w): void {
                $w->programs['validate:disk'] = static function (Lab57World $world, string $unit): array {
                    $disk = lab57_disk_usage($world);
                    return $disk['used'] / $disk['size'] >= 0.99 ? ['zapisovac: write failed: No space left on device'] : [];
                };
            },
            'checks' => [
                ['core_disk_below', ['ratio' => 0.9, 'label' => 'Kořenový disk je zaplněný pod 90 %']],
                ['service_running', ['service' => 'zapisovac', 'label' => 'Služba zapisovac běží']],
            ],
            'solution' => ['df -h', 'sudo du -ah /var/log | sort -h | tail -3', 'sudo truncate -s 0 {f:big_log_path}', 'sudo systemctl restart zapisovac', 'df -h'],
            'learn' => 'Plný disk umí shodit cokoli. df ukáže kde, du co. Logy se v praxi hlídají rotací (logrotate).',
        ],
        [
            'id' => 'opr-3', 'pack' => 'opravna', 'type' => 'check', 'title' => 'Záloha se nespustí', 'minutes' => 6, 'world' => $srv, 'v' => 2,
            'story' => 'Skript /opt/zaloha/zaloha.sh má každý večer zálohovat web. Když ho ale spustíš, hlásí „Permission denied“.',
            'task' => 'Oprav práva skriptu, spusť ho jako správce a ověř, že vznikla záloha /srv/zaloha/web/index.html.',
            'commands' => ['ls', 'chmod', 'sudo'],
            'hints' => ['ls -l /opt/zaloha ukáže práva. Chybí x (spuštění).', 'sudo chmod +x /opt/zaloha/zaloha.sh', 'Pak sudo /opt/zaloha/zaloha.sh'],
            'generate' => [
                ['file', ['path' => '/opt/zaloha/zaloha.sh', 'content' => "#!/bin/bash\n# Noční záloha webu\nmkdir -p /srv/zaloha\ncp -r /var/www/html /srv/zaloha/web\necho \"Záloha hotová: /srv/zaloha/web\"\n", 'mode' => 0644, 'owner' => 'root', 'group' => 'root', 'days' => 3]],
            ],
            'checks' => [
                ['core_executable', ['path' => '/opt/zaloha/zaloha.sh', 'label' => 'Skript má právo ke spuštění']],
                ['core_file_exists', ['path' => '/srv/zaloha/web/index.html', 'label' => 'Existuje /srv/zaloha/web/index.html']],
            ],
            'solution' => ['ls -l /opt/zaloha', 'sudo chmod +x /opt/zaloha/zaloha.sh', 'sudo /opt/zaloha/zaloha.sh', 'ls /srv/zaloha/web'],
            'learn' => 'Spustit jde jen soubor s právem x. chmod +x ho přidá; číselně je to třeba 755 (rwxr-xr-x).',
        ],
        [
            'id' => 'opr-4', 'pack' => 'opravna', 'type' => 'check', 'title' => 'Proces žere procesor', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'world' => $srv, 'v' => 2,
            'story' => 'Server je hrozně pomalý. Nějaký tvůj starý skript se zasekl v nekonečné smyčce a bere skoro celý procesor.',
            'task' => 'Najdi proces, který vytěžuje procesor, a ukonči ho.',
            'commands' => ['top', 'ps', 'kill'],
            'hints' => ['top nebo ps aux --sort=-%cpu seřadí procesy podle zátěže.', 'Proces ukončíš kill <PID>. Když nereaguje, zkus kill -9 <PID>.'],
            'generate' => [
                ['core_process', ['pid' => [2800, 2990], 'user' => 'student', 'cmd' => ['python3 kalkulacka.py', 'python3 generator_rozvrhu.py', 'bash nekonecna_smycka.sh'], 'cpu' => 98.7, 'mem' => 1.4, 'vsz' => 31200, 'rss' => 12400, 'tty' => 'pts/1', 'stat' => 'R', 'start_offset' => 3000, 'ignore_term' => 'random']],
            ],
            'checks' => [
                ['core_process_gone', ['pid' => '{f:pid}', 'label' => 'Proces s vysokou zátěží už neběží']],
            ],
            'solution' => ['ps aux --sort=-%cpu', 'kill {f:pid}', 'kill -9 {f:pid}'],
            'learn' => 'kill pošle signál TERM (slušná žádost o konec). kill -9 (KILL) proces ukončí okamžitě – používej ho až jako poslední možnost.',
        ],
        [
            'id' => 'opr-5', 'pack' => 'opravna', 'type' => 'check', 'title' => 'Po restartu to nenaběhne', 'minutes' => 5, 'world' => $srv, 'v' => 2,
            'story' => 'Aplikace rozvrh po každém restartu serveru neběží a někdo ji musí pouštět ručně.',
            'task' => 'Zařiď, aby se služba rozvrh spouštěla automaticky při startu, a zároveň ji hned spusť.',
            'commands' => ['systemctl', 'sudo'],
            'hints' => ['systemctl status rozvrh ukáže „disabled“ a „inactive“.', 'enable = spouštět při startu, start = spustit teď. Obojí najednou: sudo systemctl enable --now rozvrh'],
            'generate' => [
                ['core_service', ['name' => 'rozvrh', 'desc' => 'Rozvrh - skolni webova aplikace', 'active' => false, 'enabled' => false, 'failed' => false, 'ports' => [8080], 'procs' => [['www-data', '/usr/bin/python3 /opt/rozvrh/app.py --port 8080']]]],
                ['file', ['path' => '/opt/rozvrh/app.py', 'content' => "print('Rozvrh bezi na portu 8080')\n", 'days' => 10]],
            ],
            'checks' => [
                ['core_service_enabled', ['service' => 'rozvrh', 'label' => 'Služba rozvrh je povolená (enabled)']],
                ['service_running', ['service' => 'rozvrh', 'label' => 'Služba rozvrh běží']],
            ],
            'solution' => ['systemctl status rozvrh', 'sudo systemctl enable --now rozvrh', 'systemctl is-active rozvrh'],
            'learn' => 'enable/disable řídí automatický start, start/stop aktuální běh. Jsou to dvě nezávislé věci.',
        ],
        [
            'id' => 'opr-6', 'pack' => 'opravna', 'type' => 'check', 'title' => '403 Forbidden', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'world' => $srv, 'v' => 2,
            'story' => 'Po nahrání nové verze webu vrací server „403 Forbidden“. Nginx běží pod uživatelem www-data a soubor nemůže přečíst.',
            'task' => 'Oprav práva souboru /var/www/html/index.html tak, aby ho web server mohl číst, a ověř, že localhost vrací 200.',
            'commands' => ['curl', 'ls', 'chmod', 'sudo'],
            'hints' => ['curl -I localhost ukáže stavový kód, ls -l /var/www/html práva.', 'Ostatní (o) musí mít právo číst (r). Běžná práva webových souborů jsou 644.', 'sudo chmod 644 /var/www/html/index.html'],
            'generate' => [
                ['core_chmod', ['path' => '/var/www/html/index.html', 'modes' => [0600, 0640, 0400]]],
            ],
            'checks' => [
                ['core_http_status', ['host' => 'localhost', 'port' => 80, 'equals' => 200, 'label' => 'http://localhost vrací 200']],
                ['core_mode_no_other_write', ['path' => '/var/www/html/index.html', 'label' => 'Soubor není zapisovatelný pro ostatní']],
            ],
            'solution' => ['curl -I localhost', 'ls -l /var/www/html', 'sudo chmod 644 /var/www/html/index.html', 'curl -I localhost'],
            'learn' => '403 = server soubor vidí, ale nesmí ho číst. Práva 777 „pro jistotu“ nikdy – stačí 644 pro soubory a 755 pro složky.',
        ],
    ];
}

// ---------------------------------------------------------------------------
// SHELL GOLF – ve stylu CodinGame (skryté testy s jinými daty)
// ---------------------------------------------------------------------------

function lab57_levels_golf(): array
{
    $golf = static fn(string $id, string $title, string $task, string $reference, array $generate, array $hints, string $learn, int $difficulty = 1): array => [
        'id' => $id, 'pack' => 'golf', 'type' => 'golf', 'v' => 3, 'title' => $title, 'difficulty' => $difficulty, 'points' => [1 => 100, 2 => 150, 3 => 200][$difficulty], 'minutes' => 6,
        'story' => 'Napiš jeden příkaz, který vypíše přesně požadovaný výstup. Po odevzdání se spustí na třech skrytých testech s jinými daty – nejde tedy výsledek jen opsat.',
        'task' => $task . ' Až bude výstup sedět, napiš submit (odevzdá tvůj poslední příkaz).',
        'commands' => array_values(array_unique(array_filter(preg_split('/[\s|;&]+/', $reference) ?: [], static fn(string $t): bool => preg_match('/^[a-z0-9]+$/', $t) === 1 && function_exists('lab57_command_registry') && isset(lab57_command_registry()[$t])))),
        'hints' => $hints,
        'golf' => ['tests' => 3, 'reference' => $reference],
        'generate' => $generate,
        'solution' => [$reference, 'submit'],
        'learn' => $learn,
    ];
    return [
        $golf('golf-1', 'Počet řádků', 'Vypiš jen počet řádků souboru log.txt (samotné číslo, bez jména souboru).', 'wc -l < log.txt',
            [['core_filler_lines', ['path' => '~/log.txt', 'count' => [20, 90]]]],
            ['wc -l soubor vypíše i jméno souboru. Jak mu dát obsah bez jména?', 'Přesměrování vstupu < pošle obsah souboru na stdin.'], 'wc -l < soubor vypíše jen číslo – jméno souboru wc nezná.'),
        $golf('golf-2', 'Unikátní návštěvníci', 'Vypiš počet různých IP adres v access.log.', "cut -d' ' -f1 access.log | sort -u | wc -l",
            [['core_access_log_topips', ['path' => '~/access.log']]],
            ['IP adresa je první sloupec (oddělovač mezera).', 'cut → sort -u → wc -l'], 'cut vybere sloupec, sort -u odstraní duplicity, wc -l spočítá řádky.', 2),
        $golf('golf-3', 'Top 3 IP adresy', 'Vypiš tři nejčastější IP adresy v access.log i s počtem výskytů (formát jako uniq -c, od nejčastější).', "cut -d' ' -f1 access.log | sort | uniq -c | sort -rn | head -3",
            [['core_access_log_topips', ['path' => '~/access.log']]],
            ['uniq -c počítá opakování, ale jen sousedních řádků.', 'cut … | sort | uniq -c | sort -rn | head -3'], 'Tahle roura je klasika analýzy logů – stejně se hledají nejčastější chyby nebo stránky.', 3),
        $golf('golf-4', 'Chyby v logu', 'Vypiš počet řádků souboru app.log, které obsahují slovo ERROR (velkými písmeny).', 'grep -c ERROR app.log',
            [['core_level_log', ['path' => '~/app.log', 'rows' => [40, 90], 'levels' => ['INFO', 'INFO', 'INFO', 'WARN', 'ERROR', 'DEBUG'], 'messages' => ['přihlášení', 'uložení', 'dotaz', 'záloha'], 'decoy_line' => 'INFO text obsahuje slovo error malými písmeny']]],
            ['grep umí rovnou počítat.', 'grep -c'], 'grep -c vrací počet řádků se shodou, grep -i by ignoroval velikost písmen.'),
        $golf('golf-5', 'Konfigurační soubory', 'Vypiš cesty ke všem souborům *.conf ve složce /etc/app (i v podsložkách), seřazené abecedně.', "find /etc/app -name '*.conf' | sort",
            [['core_conf_tree', ['root' => '/etc/app', 'subdirs' => ['', 'moduly/', 'moduly/extra/', 'sablony/'], 'count_range' => [1, 3], 'names' => ['db', 'web', 'cache', 'mail', 'log', 'auth'], 'extra_readme' => true]]],
            ['find prochází podsložky a -name vybere podle vzoru (dej ho do apostrofů).', "find /etc/app -name '*.conf' | sort"], 'find vypisuje v pořadí procházení, proto se výsledek často posílá přes | sort.', 2),
        $golf('golf-6', 'Nejlepší žák', 'Soubor body.csv má hlavičku jmeno,body. Vypiš jen jméno žáka s nejvíce body.', 'tail -n +2 body.csv | sort -t, -k2 -n | tail -1 | cut -d, -f1',
            [['core_name_score_csv', ['path' => '~/body.csv']]],
            ['Hlavičku přeskočíš tail -n +2.', 'Řazení podle 2. sloupce: sort -t, -k2 -n; pak vezmi poslední řádek a první sloupec.'], 'sort -t, -k2 -n řadí CSV podle čísla ve druhém sloupci – základ práce s tabulkami v terminálu.', 3),
    ];
}
