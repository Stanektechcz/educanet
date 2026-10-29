<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v50 · Hands-on Learning Runtime
 *
 * Formative, curriculum-isolated practical learning for 3.A / 4.A.
 * The module supports a deterministic stateful simulator on every host and an
 * optional localhost OCI broker for real ephemeral Linux sessions.
 */

function v50_storage_path(string $name): string
{
    $safe = preg_replace('/[^a-z0-9_\-]/i', '', $name) ?: 'events';
    return STORAGE_DIR . '/adaptive_v50_' . $safe . '.json.php';
}
/** Čtení kolekce v50; u registrovaného proudu (hands_on_events) čte měsíční JSONL (v58). */
function v50_rows(string $name): array
{
    return storage_rows(v50_storage_path($name));
}
/** v58 (F2): RMW jednoho záznamu kolekce v50 pod zámkem – $fn(?array $current): ?array (null = smazat). */
function v50_update_row(string $name, string $key, callable $fn): ?array
{
    return storage_map_update(v50_storage_path($name), $key, $fn);
}
function v50_clean_text(string $value, int $max = 1600): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    return u_substr($value, 0, $max);
}
function v50_lab_key(string $classId, int $lessonNo): string
{
    return $classId . '|L' . str_pad((string)$lessonNo, 2, '0', STR_PAD_LEFT);
}

function v50_family_catalog(): array
{
    return [
        'network-basics'=>[
            'label'=>'IP cesta a gateway','layer'=>'L3','commands'=>['ip addr','ip route','ping 10.20.30.1','traceroute 10.20.30.20'],
            'symptom'=>'Stanice má adresu, ale nedosáhne na službu mimo vlastní síť.',
            'target'=>['route_ok'=>true],
            'fix'=>'sudo ip route replace default via {gateway}',
            'proof'=>'Výchozí route míří na dosažitelnou gateway a vzdálený cíl odpovídá.',
        ],
        'subnetting'=>[
            'label'=>'Subnetting a VLSM','layer'=>'L3','commands'=>['ip addr','ip route','python3 -V','ping {gateway}'],
            'symptom'=>'Dvě zařízení mají adresy, ale student musí rozhodnout, zda jsou ve stejném subnetu a proč.',
            'target'=>['subnet_ok'=>true],
            'fix'=>'sudo ip addr replace {client_ip}/{prefix} dev lab0',
            'proof'=>'Adresa a prefix odpovídají navrženému subnetu bez překryvu.',
        ],
        'packet-flow'=>[
            'label'=>'Packet journey','layer'=>'L4','commands'=>['ip addr','ip route','ss -tulpn','nc -vz {server_ip} {port}'],
            'symptom'=>'Paket se na cestě z klienta ke službě ztratí. Najdi vrstvu, která ho zastavila.',
            'target'=>['path_ok'=>true],
            'fix'=>'sudo nft add rule inet filter input tcp dport {port} accept',
            'proof'=>'TCP test na cílový port prochází a packet path má všechny potřebné kroky.',
        ],
        'dns'=>[
            'label'=>'DNS resolution','layer'=>'L7','commands'=>['dig {hostname}','dig +trace {hostname}','getent hosts {hostname}','resolvectl status'],
            'symptom'=>'IP adresa funguje, ale jméno služby ne. Rozliš resolver, záznam a cache.',
            'target'=>['dns_ok'=>true],
            'fix'=>'sudo sh -c "printf nameserver\\ 10.20.30.53 > /etc/resolv.conf"',
            'proof'=>'Resolver vrací očekávanou adresu a služba je dosažitelná jménem.',
        ],
        'dhcp'=>[
            'label'=>'DHCP a klientská konfigurace','layer'=>'L3','commands'=>['ip addr','ip route','cat /etc/resolv.conf','journalctl -n 20'],
            'symptom'=>'Klient skončil bez použitelné konfigurace. Urči, zda selhal DHCP, VLAN, pool nebo klient.',
            'target'=>['dhcp_ok'=>true],
            'fix'=>'sudo ip addr replace {client_ip}/{prefix} dev lab0',
            'proof'=>'Klient má očekávanou IP, gateway i DNS a nepotřebuje fallback adresu.',
        ],
        'linux-filesystem'=>[
            'label'=>'Filesystem a data','layer'=>'OS','commands'=>['pwd','ls -la','find /srv -maxdepth 2 -type f','du -sh /srv'],
            'symptom'=>'Služba nenachází soubor nebo pracuje s jinou cestou, než student očekává.',
            'target'=>['filesystem_ok'=>true],
            'fix'=>'mkdir -p /tmp/lab-evidence',
            'proof'=>'Student lokalizoval správnou cestu a vytvořil ověřovací evidence adresář.',
        ],
        'permissions'=>[
            'label'=>'Linux permissions','layer'=>'OS','commands'=>['ls -l /tmp/lab-secret','stat /tmp/lab-secret','id','chmod 600 /tmp/lab-secret'],
            'symptom'=>'Soubor je příliš otevřený nebo ho správný proces nemůže bezpečně použít.',
            'target'=>['permissions_ok'=>true],
            'fix'=>'chmod 600 /tmp/lab-secret',
            'proof'=>'Citlivý soubor má least-privilege oprávnění 600.',
        ],
        'processes'=>[
            'label'=>'Procesy a sockety','layer'=>'OS','commands'=>['ps aux','ss -tulpn','pgrep -af nginx','kill -0 1'],
            'symptom'=>'Proces může běžet, ale služba přesto nemusí naslouchat. Odděl process state od socket state.',
            'target'=>['process_ok'=>true],
            'fix'=>'touch /tmp/process-validated',
            'proof'=>'Student doložil proces i odpovídající socket místo pouhého tvrzení „běží“.',
        ],
        'systemd'=>[
            'label'=>'systemd dependency graph','layer'=>'OS','commands'=>['systemctl status nginx','systemctl is-active nginx','journalctl -u nginx -n 30','systemctl restart nginx'],
            'symptom'=>'Služba je v chybovém nebo zavádějícím stavu. Najdi příčinu dřív než restartuješ.',
            'target'=>['service_ok'=>true],
            'fix'=>'systemctl restart nginx',
            'proof'=>'Služba je active a lokální health check vrací očekávaný výsledek.',
        ],
        'logs'=>[
            'label'=>'Log forensics','layer'=>'OS','commands'=>['journalctl -n 40','journalctl -p warning -n 40','grep -R "error" /var/log 2>/dev/null','date'],
            'symptom'=>'Incident má několik symptomů. Z logů sestav časovou osu a odděl příčinu od vedlejšího šumu.',
            'target'=>['logs_ok'=>true],
            'fix'=>'touch /tmp/root-cause-found',
            'proof'=>'Student má alespoň dva konzistentní důkazy a označený root cause.',
        ],
        'ssh'=>[
            'label'=>'SSH trust chain','layer'=>'L7','commands'=>['ssh -V','ls -la ~/.ssh','stat ~/.ssh/id_ed25519','ssh -vvv user@{server_ip}'],
            'symptom'=>'SSH klient spojení odmítá nebo autentizace selže. Rozliš síť, host key, identitu a permissions.',
            'target'=>['ssh_ok'=>true],
            'fix'=>'chmod 600 ~/.ssh/id_ed25519',
            'proof'=>'Privátní klíč má bezpečná oprávnění a autentizační řetězec je konzistentní.',
        ],
        'firewall'=>[
            'label'=>'Stateful firewall','layer'=>'L4','commands'=>['nft list ruleset','ss -tulpn','nc -vz {server_ip} {port}','nft add rule inet filter input tcp dport {port} accept'],
            'symptom'=>'Služba naslouchá, ale klient se nedostane na port. Ověř pravidla a jejich pořadí.',
            'target'=>['firewall_ok'=>true],
            'fix'=>'nft add rule inet filter input tcp dport {port} accept',
            'proof'=>'Listener existuje a firewall povoluje pouze požadovaný provoz.',
        ],
        'web-stack'=>[
            'label'=>'HTTP/TLS/reverse proxy','layer'=>'L7','commands'=>['curl -I http://127.0.0.1:{app_port}','curl -kI https://{hostname}','ss -tulpn','grep -R "proxy_pass" /etc/nginx 2>/dev/null'],
            'symptom'=>'Uživatel vidí 502 nebo timeout. Rozlož cestu na DNS → TCP → TLS → proxy → aplikaci.',
            'target'=>['web_ok'=>true],
            'fix'=>'touch /tmp/proxy-fixed',
            'proof'=>'Backend health check i reverse-proxy path vrací očekávaný stav.',
        ],
        'storage'=>[
            'label'=>'Storage a kapacita','layer'=>'OS','commands'=>['df -h','du -xh /var 2>/dev/null | sort -h | tail','lsblk','mount'],
            'symptom'=>'Aplikace hlásí chyby zápisu. Rozliš plný filesystem, inode problém, mount a velká data.',
            'target'=>['storage_ok'=>true],
            'fix'=>'rm -f /tmp/lab-large-file',
            'proof'=>'Student lokalizoval skutečný bottleneck a obnovil bezpečnou rezervu.',
        ],
        'automation'=>[
            'label'=>'Bezpečná shell automatizace','layer'=>'OS','commands'=>['bash -n /tmp/lab-script.sh','shellcheck /tmp/lab-script.sh','cat /tmp/lab-script.sh','chmod 750 /tmp/lab-script.sh'],
            'symptom'=>'Údržbový skript může poškodit data při chybě. Najdi riziko a udělej běh idempotentní a bezpečný.',
            'target'=>['automation_ok'=>true],
            'fix'=>'chmod 750 /tmp/lab-script.sh',
            'proof'=>'Skript projde syntax checkem a má bezpečná oprávnění.',
        ],
        'containers'=>[
            'label'=>'Containers & Compose','layer'=>'L7','commands'=>['podman ps','podman logs lab-app','podman inspect lab-app','curl -I http://127.0.0.1:{app_port}'],
            'symptom'=>'Container běží, ale služba není zdravá. Odděl container state, health, port mapping a aplikaci.',
            'target'=>['container_ok'=>true],
            'fix'=>'touch /tmp/container-healthy',
            'proof'=>'Student ověřil health i aplikační endpoint, ne pouze status kontejneru.',
        ],
        'observability'=>[
            'label'=>'Observability a SLO','layer'=>'L7','commands'=>['curl -s http://127.0.0.1:{metrics_port}/metrics','journalctl -n 40','uptime','free -m'],
            'symptom'=>'Služba je „nahoře“, ale uživatelé hlásí degradaci. Najdi signál, který vysvětluje dopad.',
            'target'=>['observability_ok'=>true],
            'fix'=>'touch /tmp/observability-evidence',
            'proof'=>'Student spojil symptom s metrikou/logem a formuloval ověřitelnou hypotézu.',
        ],
        'incident-response'=>[
            'label'=>'Incident response','layer'=>'L7','commands'=>['date','ss -tulpn','journalctl -n 50','ps aux','ip route'],
            'symptom'=>'Produkční incident má více symptomů. Stabilizuj situaci bez ztráty důkazů a vytvoř validační plán.',
            'target'=>['incident_ok'=>true],
            'fix'=>'touch /tmp/incident-stabilized',
            'proof'=>'Student má timeline, root cause, minimální fix, validaci a rollback.',
        ],
    ];
}

function v50_family_sequence(string $classId): array
{
    $three = ['network-basics','subnetting','dns','dhcp','packet-flow','linux-filesystem','permissions','processes','systemd','logs','ssh','firewall','web-stack','automation','network-basics','dns','packet-flow','permissions','systemd','logs','storage','automation','containers','observability','firewall','web-stack','incident-response','incident-response'];
    $four = ['subnetting','network-basics','packet-flow','dns','ssh','permissions','systemd','logs','firewall','web-stack','storage','automation','containers','observability','incident-response','network-basics','dns','packet-flow','systemd','firewall','web-stack','storage','automation','containers','observability','ssh','incident-response','incident-response'];
    return $classId === 'class_4a' ? $four : $three;
}

function v50_seeded_params(string $classId, int $lessonNo, string $studentKey = ''): array
{
    $seed = abs(crc32($classId.'|'.$lessonNo.'|'.$studentKey));
    $vlan = 10 + ($seed % 80);
    $octet = 10 + (($seed >> 5) % 180);
    $prefixes = [24,25,26,27,28];
    $prefix = $prefixes[$seed % count($prefixes)];
    $base = '10.' . $octet . '.' . (($vlan * 2) % 250);
    return [
        'vlan'=>$vlan,
        'prefix'=>$prefix,
        'client_ip'=>$base.'.20',
        'gateway'=>$base.'.1',
        'server_ip'=>$base.'.50',
        'dns_ip'=>$base.'.53',
        'hostname'=>'app-l'.$lessonNo.'.school.lan',
        'port'=>[22,80,443,8080,8443][$seed % 5],
        'app_port'=>8000 + ($seed % 20),
        'metrics_port'=>9100 + ($seed % 10),
        'latency_ms'=>8 + ($seed % 70),
        'error_rate'=>($seed % 12),
    ];
}

function v50_expand(string $text, array $params): string
{
    foreach ($params as $key=>$value) $text = str_replace('{'.$key.'}', (string)$value, $text);
    return $text;
}

function v50_topology(string $family, array $p): array
{
    $nodes = [
        ['id'=>'client','label'=>'Student client','kind'=>'client','layer'=>'L3','x'=>8,'y'=>50,'meta'=>$p['client_ip'].'/'.$p['prefix']],
        ['id'=>'switch','label'=>'Switch / VLAN '.$p['vlan'],'kind'=>'switch','layer'=>'L2','x'=>28,'y'=>50,'meta'=>'VLAN '.$p['vlan']],
        ['id'=>'gateway','label'=>'Gateway','kind'=>'router','layer'=>'L3','x'=>48,'y'=>50,'meta'=>$p['gateway']],
        ['id'=>'service','label'=>'Target service','kind'=>'server','layer'=>'L7','x'=>78,'y'=>50,'meta'=>$p['server_ip'].':'.$p['port']],
    ];
    if (in_array($family,['dns','web-stack','incident-response'],true)) {
        $nodes[]=['id'=>'dns','label'=>'DNS resolver','kind'=>'dns','layer'=>'L7','x'=>48,'y'=>18,'meta'=>$p['dns_ip']];
    }
    if (in_array($family,['firewall','packet-flow','ssh','web-stack','incident-response'],true)) {
        $nodes[]=['id'=>'firewall','label'=>'Firewall','kind'=>'firewall','layer'=>'L4','x'=>63,'y'=>50,'meta'=>'stateful'];
    }
    $edges=[];
    $link = static function(string $a,string $b,string $layer,string $label='') use (&$edges):void {$edges[]=['from'=>$a,'to'=>$b,'layer'=>$layer,'label'=>$label];};
    $link('client','switch','L2','Ethernet');$link('switch','gateway','L3','IP');
    if (in_array($family,['firewall','packet-flow','ssh','web-stack','incident-response'],true)) {$link('gateway','firewall','L3','route');$link('firewall','service','L4','TCP');}
    else $link('gateway','service','L3','route');
    if (array_filter($nodes,static fn(array $n):bool=>$n['id']==='dns')) {$link('client','dns','L7','DNS');$link('dns','service','L7','answer');}
    return ['nodes'=>$nodes,'edges'=>$edges,'packet_path'=>array_values(array_filter(['client','switch','gateway',in_array($family,['firewall','packet-flow','ssh','web-stack','incident-response'],true)?'firewall':null,'service']))];
}

function v50_lab_spec(string $classId, array $lesson, string $studentKey = ''): array
{
    $lessonNo=(int)($lesson['number']??0);
    $seq=v50_family_sequence($classId);$family=(string)($seq[max(0,$lessonNo-1)]??'incident-response');
    $familySpec=(array)(v50_family_catalog()[$family]??v50_family_catalog()['incident-response']);
    $params=v50_seeded_params($classId,$lessonNo,$studentKey);
    $commands=array_values(array_map(static fn($c)=>v50_expand((string)$c,$params),(array)$familySpec['commands']));
    $fix=v50_expand((string)$familySpec['fix'],$params);
    if(!in_array($fix,$commands,true))$commands[]=$fix;
    $goal=v50_clean_text((string)($lesson['goal']??''),500);
    $title=v50_clean_text((string)($lesson['title']??('Lekce '.$lessonNo)),220);
    $problem=v50_expand((string)$familySpec['symptom'],$params);
    return [
        'id'=>'v50-'.$classId.'-L'.str_pad((string)$lessonNo,2,'0',STR_PAD_LEFT),
        'class_id'=>$classId,'lesson_number'=>$lessonNo,'lesson_title'=>$title,'goal'=>$goal,
        'family'=>$family,'family_label'=>(string)$familySpec['label'],'layer'=>(string)$familySpec['layer'],'params'=>$params,
        'problem'=>$problem,
        'mission'=>tr('Diagnostikuj situaci „{title}“ pomocí co nejmenšího množství kvalitních důkazů. Neopravuj nic naslepo.',['title'=>$title]),
        'hypothesis_prompt'=>tr('Co je podle tebe nejpravděpodobnější příčina a jaký výsledek by ji vyvrátil?'),
        'allowed_commands'=>$commands,
        'repair_command'=>$fix,
        'target_state'=>(array)$familySpec['target'],
        'proof'=>(string)$familySpec['proof'],
        'topology'=>v50_topology($family,$params),
        'hints'=>[
            ['level'=>1,'label'=>tr('Otázka'),'text'=>tr('Na které vrstvě už máš důkaz, že problém není? Než něco změníš, napiš jeden rozlišovací test.')],
            ['level'=>2,'label'=>'Clue','text'=>tr('Odděl stav hosta, cestu paketů, listener/službu a aplikační vrstvu. Hledej první místo, kde se očekávání rozchází s důkazem.')],
            ['level'=>3,'label'=>tr('Nástroj'),'text'=>tr('Pro tento scénář se hodí například: {commands}.',['commands'=>implode(' · ',array_slice($commands,0,3))])],
            ['level'=>4,'label'=>tr('Referenční model'),'text'=>tr('Minimální bezpečný fix je „{fix}“. Po změně ověř: {proof}',['fix'=>$fix,'proof'=>(string)$familySpec['proof']])],
        ],
        'transfer'=>tr('Představ si stejný symptom na jiném hostu nebo VLAN. Co by z tvého postupu zůstalo stejné a co bys musel znovu změřit?'),
        'boss_fight'=>in_array($lessonNo,[14,28],true),
        'runtime_profile'=>in_array($family,['network-basics','subnetting','packet-flow','dns','dhcp','firewall'],true)?'network':'linux',
        'assessment'=>['mode'=>'formative','grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'blocks_curriculum'=>false,'can_be_required'=>false],
    ];
}

function v50_all_lab_specs(): array
{
    static $cache=null;if(is_array($cache))return $cache;
    $out=[];
    foreach(['class_3a','class_4a'] as $classId){
        foreach(v42_lessons_for_class($classId) as $lesson){
            $spec=v50_lab_spec($classId,$lesson,'catalog');$out[(string)$spec['id']]=$spec;
        }
    }
    return $cache=$out;
}

function v50_initial_sim_state(array $spec,string $studentKey): array
{
    $family=(string)($spec['family']??'');$p=(array)($spec['params']??[]);
    $state=[
        'route_ok'=>!in_array($family,['network-basics'],true),
        'subnet_ok'=>!in_array($family,['subnetting','dhcp'],true),
        'path_ok'=>!in_array($family,['packet-flow'],true),
        'dns_ok'=>!in_array($family,['dns'],true),
        'dhcp_ok'=>!in_array($family,['dhcp'],true),
        'filesystem_ok'=>!in_array($family,['linux-filesystem'],true),
        'permissions_ok'=>!in_array($family,['permissions','ssh'],true),
        'process_ok'=>!in_array($family,['processes'],true),
        'service_ok'=>!in_array($family,['systemd'],true),
        'logs_ok'=>!in_array($family,['logs'],true),
        'ssh_ok'=>!in_array($family,['ssh'],true),
        'firewall_ok'=>!in_array($family,['firewall'],true),
        'web_ok'=>!in_array($family,['web-stack'],true),
        'storage_ok'=>!in_array($family,['storage'],true),
        'automation_ok'=>!in_array($family,['automation'],true),
        'container_ok'=>!in_array($family,['containers'],true),
        'observability_ok'=>!in_array($family,['observability'],true),
        'incident_ok'=>!in_array($family,['incident-response'],true),
        'evidence_count'=>0,'command_count'=>0,'last_command'=>'','last_output'=>'',
        'student_key'=>$studentKey,'client_ip'=>(string)($p['client_ip']??''),'gateway'=>(string)($p['gateway']??''),
    ];
    return $state;
}

function v50_command_allowed(array $spec,string $command): bool
{
    $command=trim($command);if($command==='')return false;
    if(preg_match('/[;&|`$><\n\r]/',$command))return false;
    foreach((array)($spec['allowed_commands']??[]) as $allowed){
        $allowed=trim((string)$allowed);if($allowed!==''&&hash_equals($allowed,$command))return true;
    }
    return false;
}

function v50_sim_output(array $spec,array $state,string $command): string
{
    $p=(array)($spec['params']??[]);$family=(string)($spec['family']??'');
    if(str_starts_with($command,'ip addr')) return "1: lo: <LOOPBACK,UP>\n    inet 127.0.0.1/8\n2: lab0: <BROADCAST,MULTICAST,UP>\n    inet ".($state['subnet_ok']?$p['client_ip'].'/'.$p['prefix']:'169.254.23.18/16')." scope global lab0";
    if(str_starts_with($command,'ip route')) return ($state['route_ok']?"default via {$p['gateway']} dev lab0\n":"").($p['server_ip']??'10.0.0.50')."/32 dev lab0 scope link";
    if(str_starts_with($command,'ping ')) return $state['route_ok']||$family!=='network-basics'?"64 bytes from {$p['gateway']}: icmp_seq=1 ttl=64 time=1.2 ms":"From {$p['client_ip']} Destination Host Unreachable";
    if(str_starts_with($command,'traceroute ')) return $state['route_ok']?"1  {$p['gateway']}  1.1 ms\n2  {$p['server_ip']}  5.4 ms":"1  * * *";
    if(str_starts_with($command,'dig ')) return $state['dns_ok']?";; status: NOERROR\n{$p['hostname']}. 300 IN A {$p['server_ip']}":";; status: SERVFAIL\n;; no servers could be reached";
    if(str_starts_with($command,'getent hosts')) return $state['dns_ok']?"{$p['server_ip']} {$p['hostname']}":'';
    if($command==='resolvectl status'||str_starts_with($command,'cat /etc/resolv.conf')) return $state['dns_ok']?"DNS Servers: {$p['dns_ip']}":"DNS Servers: 8.8.8.8";
    if(str_starts_with($command,'ss -tulpn')) return "tcp LISTEN 0 511 127.0.0.1:".($p['app_port']??8000)." 0.0.0.0:* users:((\"app\",pid=412))\ntcp LISTEN 0 128 0.0.0.0:".($p['port']??443)." 0.0.0.0:*";
    if(str_starts_with($command,'nc -vz')) return ($state['firewall_ok']&&$state['path_ok'])||(!in_array($family,['firewall','packet-flow'],true))?"Connection to {$p['server_ip']} {$p['port']} port [tcp/*] succeeded!":"nc: connect to {$p['server_ip']} port {$p['port']} (tcp) timed out";
    if(str_starts_with($command,'systemctl status')||str_starts_with($command,'systemctl is-active')) return $state['service_ok']?"● nginx.service - nginx\n   Active: active (running)":"● nginx.service - nginx\n   Active: failed (Result: exit-code)";
    if(str_starts_with($command,'journalctl')) return "Sep 13 18:10:12 lab service[412]: request failed upstream=127.0.0.1:".($p['app_port']??8000)."\nSep 13 18:10:13 lab kernel: evidence marker family={$family}";
    if(str_starts_with($command,'ls -l /tmp/lab-secret')||str_starts_with($command,'stat /tmp/lab-secret')) return $state['permissions_ok']?"-rw------- 1 student student 42 /tmp/lab-secret":"-rw-rw-rw- 1 student student 42 /tmp/lab-secret";
    if($command==='id') return 'uid=1000(student) gid=1000(student) groups=1000(student)';
    if(str_starts_with($command,'ssh -V')) return 'OpenSSH_10.0p2, OpenSSL 3.5';
    if(str_starts_with($command,'ls -la ~/.ssh')) return $state['permissions_ok']?"-rw------- id_ed25519\n-rw-r--r-- id_ed25519.pub":"-rw-rw-rw- id_ed25519\n-rw-r--r-- id_ed25519.pub";
    if(str_starts_with($command,'stat ~/.ssh/id_ed25519')) return $state['permissions_ok']?'Access: (0600/-rw-------)':'Access: (0666/-rw-rw-rw-)';
    if(str_starts_with($command,'ssh -vvv')) return $state['ssh_ok']?"Authenticated to {$p['server_ip']} using publickey.":"WARNING: UNPROTECTED PRIVATE KEY FILE!\nPermissions are too open.";
    if(str_starts_with($command,'nft list')) return $state['firewall_ok']?"tcp dport {$p['port']} accept\nct state established,related accept":"policy drop\nct state established,related accept";
    if(str_starts_with($command,'curl ')) return ($state['web_ok']||$family!=='web-stack')?"HTTP/1.1 200 OK\nServer: nginx\nX-Lab-Evidence: healthy":"HTTP/1.1 502 Bad Gateway\nServer: nginx";
    if(str_starts_with($command,'grep -R "proxy_pass"')) return 'proxy_pass http://127.0.0.1:'.($state['web_ok']?$p['app_port']:($p['app_port']+1)).';';
    if($command==='pwd')return '/home/student';
    if($command==='ls -la')return "drwxr-xr-x .\ndrwxr-xr-x ..\n-rw-r--r-- README-lab.txt";
    if(str_starts_with($command,'find /srv'))return "/srv/app/config.yml\n/srv/app/data/sample.db";
    if(str_starts_with($command,'du -sh'))return '84M /srv';
    if(str_starts_with($command,'ps aux'))return "root 1 0.0 init\nstudent 412 0.4 app-server\nwww-data 514 0.2 nginx";
    if(str_starts_with($command,'pgrep'))return '514 nginx: master process /usr/sbin/nginx';
    if(str_starts_with($command,'kill -0'))return '';
    if(str_starts_with($command,'df -h'))return $state['storage_ok']?"/dev/vda1 20G 7.1G 12G 38% /":"/dev/vda1 20G 20G 0 100% /";
    if(str_starts_with($command,'du -xh'))return "/var/log 9.4G\n/var/lib 4.2G";
    if($command==='lsblk')return "vda 20G disk\n└─vda1 20G part /";
    if($command==='mount')return '/dev/vda1 on / type ext4 (rw,relatime)';
    if(str_starts_with($command,'bash -n'))return $state['automation_ok']?'':'/tmp/lab-script.sh: line 12: syntax error near unexpected token';
    if(str_starts_with($command,'shellcheck'))return $state['automation_ok']?'No issues detected.':'SC2086: Double quote to prevent globbing and word splitting.';
    if(str_starts_with($command,'cat /tmp/lab-script.sh'))return "#!/bin/bash\nset -euo pipefail\nbackup_dir=/srv/backup";
    if(str_starts_with($command,'podman ps'))return "CONTAINER ID IMAGE STATUS PORTS NAMES\n1ab2 lab-app Up 4 minutes 127.0.0.1:{$p['app_port']} lab-app";
    if(str_starts_with($command,'podman logs'))return $state['container_ok']?'health=ok':'upstream connect error: connection refused';
    if(str_starts_with($command,'podman inspect'))return $state['container_ok']?'"Health": {"Status":"healthy"}':'"Health": {"Status":"unhealthy"}';
    if(str_starts_with($command,'uptime'))return '18:42:01 up 14 days, load average: 0.88, 0.73, 0.69';
    if(str_starts_with($command,'free -m'))return "Mem: 2048 1180 420 64 448 700";
    if(str_contains($command,'metrics'))return "http_requests_total 8412\nhttp_errors_total ".($state['observability_ok']?12:781)."\nrequest_latency_ms_p95 ".($state['observability_ok']?84:920);
    if($command==='date')return date('D M d H:i:s T Y');
    return 'Command completed in the isolated learning environment.';
}

function v50_apply_sim_mutation(array $spec,array $state,string $command): array
{
    $family=(string)($spec['family']??'');$repair=(string)($spec['repair_command']??'');
    if(hash_equals($repair,$command)){
        foreach((array)($spec['target_state']??[]) as $k=>$v)$state[(string)$k]=$v;
        if($family==='ssh'){$state['permissions_ok']=true;$state['ssh_ok']=true;}
        if($family==='packet-flow'){$state['firewall_ok']=true;$state['path_ok']=true;}
        if($family==='dhcp'){$state['subnet_ok']=true;$state['dhcp_ok']=true;}
    }
    if(str_starts_with($command,'chmod 600')){$state['permissions_ok']=true;if($family==='ssh')$state['ssh_ok']=true;}
    if(str_starts_with($command,'systemctl restart'))$state['service_ok']=true;
    if(str_starts_with($command,'nft add rule')){$state['firewall_ok']=true;$state['path_ok']=true;}
    if(str_starts_with($command,'sudo ip route replace'))$state['route_ok']=true;
    if(str_starts_with($command,'sudo ip addr replace')){$state['subnet_ok']=true;$state['dhcp_ok']=true;}
    if(str_starts_with($command,'mkdir -p'))$state['filesystem_ok']=true;
    if(str_starts_with($command,'touch /tmp/process'))$state['process_ok']=true;
    if(str_starts_with($command,'touch /tmp/root-cause'))$state['logs_ok']=true;
    if(str_starts_with($command,'touch /tmp/proxy'))$state['web_ok']=true;
    if(str_starts_with($command,'rm -f /tmp/lab-large'))$state['storage_ok']=true;
    if(str_starts_with($command,'chmod 750'))$state['automation_ok']=true;
    if(str_starts_with($command,'touch /tmp/container'))$state['container_ok']=true;
    if(str_starts_with($command,'touch /tmp/observability'))$state['observability_ok']=true;
    if(str_starts_with($command,'touch /tmp/incident'))$state['incident_ok']=true;
    $state['command_count']=(int)($state['command_count']??0)+1;
    $state['evidence_count']=(int)($state['evidence_count']??0)+1;
    $state['last_command']=$command;
    return $state;
}

function v50_simulate_command(array $spec,array $state,string $command): array
{
    if(!v50_command_allowed($spec,$command))return ['ok'=>false,'output'=>tr('Příkaz není v bezpečném allowlistu tohoto labu.'),'state'=>$state];
    $state=v50_apply_sim_mutation($spec,$state,$command);
    $output=v50_sim_output($spec,$state,$command);$state['last_output']=$output;
    return ['ok'=>true,'output'=>$output,'state'=>$state];
}

/** v59 · Kontejnerový broker v50 (lab-runtime/) je trvale odpojený: lab jen simuluje, nic nespouští a nikam se nepřipojuje. */
function v50_runtime_mode(): string { return 'simulator'; }

function v50_event_rows(): array { return v50_rows('hands_on_events'); }
function v50_peer_rows(): array { return v50_rows('peer_rooms'); }

function v50_student_events(string $classId,string $studentKey,?int $lessonNo=null): array
{
    $out=[];foreach(v50_event_rows() as $row){if(!is_array($row))continue;if((string)($row['class_id']??'')!==$classId||(string)($row['student_key']??'')!==$studentKey)continue;if($lessonNo!==null&&(int)($row['lesson_number']??0)!==$lessonNo)continue;$out[]=$row;}return $out;
}
function v50_latest_state(string $classId,string $studentKey,array $spec): array
{
    $state=v50_initial_sim_state($spec,$studentKey);
    foreach(v50_student_events($classId,$studentKey,(int)$spec['lesson_number']) as $row){if(isset($row['state'])&&is_array($row['state']))$state=$row['state'];}
    return $state;
}
function v50_record_event(string $classId,string $studentKey,array $spec,string $kind,array $payload=[]): array
{
    $allowed=['hypothesis','command','validation','explain','transfer','hint','peer'];if(!in_array($kind,$allowed,true))throw new RuntimeException(tr('Neplatný typ hands-on evidence.'));
    $id='v50_'.bin2hex(random_bytes(7));
    $row=['id'=>$id,'class_id'=>$classId,'student_key'=>$studentKey,'lesson_number'=>(int)$spec['lesson_number'],'lab_id'=>(string)$spec['id'],'kind'=>$kind,'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false,'created_at'=>date(DATE_ATOM)];
    foreach($payload as $k=>$v){if($k==='state'&&is_array($v))$row[$k]=$v;elseif(is_bool($v)||is_int($v)||is_float($v)||$v===null)$row[$k]=$v;elseif(is_string($v))$row[$k]=v50_clean_text($v,2200);}
    storage_append('adaptive_v50_hands_on_events',$row);return $row;
}
function v50_hypothesis_submit(string $classId,string $studentKey,array $spec,string $text): array
{
    $text=v50_clean_text($text,1400);if(u_strlen($text)<18)throw new RuntimeException(tr('Hypotéza musí být konkrétnější: uveď příčinu a ověřitelný důkaz.'));
    return v50_record_event($classId,$studentKey,$spec,'hypothesis',['text'=>$text,'dimension'=>'principle']);
}
function v50_command_submit(string $classId,string $studentKey,array $spec,string $command): array
{
    $command=trim($command);$state=v50_latest_state($classId,$studentKey,$spec);
    $sim=v50_simulate_command($spec,$state,$command);$output=(string)$sim['output'];$state=(array)$sim['state'];$mode='simulator';$ok=!empty($sim['ok']);
    if(!$ok)return ['ok'=>false,'output'=>$output,'state'=>$state,'mode'=>$mode];
    v50_record_event($classId,$studentKey,$spec,'command',['command'=>$command,'output'=>$output,'state'=>$state,'dimension'=>'recognition']);
    return ['ok'=>true,'output'=>$output,'state'=>$state,'mode'=>$mode];
}
function v50_state_valid(array $spec,array $state): bool
{
    foreach((array)($spec['target_state']??[]) as $k=>$v)if(($state[(string)$k]??null)!==$v)return false;return true;
}
function v50_validate_state_submit(string $classId,string $studentKey,array $spec): array
{
    $state=v50_latest_state($classId,$studentKey,$spec);$ok=v50_state_valid($spec,$state);
    v50_record_event($classId,$studentKey,$spec,'validation',['correct'=>$ok,'state'=>$state,'dimension'=>$ok?'repair':'diagnosis']);
    return ['correct'=>$ok,'proof'=>(string)$spec['proof'],'state'=>$state];
}
function v50_explain_submit(string $classId,string $studentKey,array $spec,string $text): array
{
    $text=v50_clean_text($text,1800);if(u_strlen($text)<28)throw new RuntimeException(tr('Vysvětlení musí pojmenovat příčinu i důkaz.'));
    return v50_record_event($classId,$studentKey,$spec,'explain',['text'=>$text,'dimension'=>'explain']);
}
function v50_transfer_submit(string $classId,string $studentKey,array $spec,string $text): array
{
    $text=v50_clean_text($text,1800);if(u_strlen($text)<28)throw new RuntimeException(tr('Transfer musí říct, co by zůstalo stejné a co bys znovu ověřil/a.'));
    return v50_record_event($classId,$studentKey,$spec,'transfer',['text'=>$text,'dimension'=>'transfer']);
}
function v50_hint_record(string $classId,string $studentKey,array $spec,int $level): array
{
    $level=max(1,min(4,$level));return v50_record_event($classId,$studentKey,$spec,'hint',['level'=>$level]);
}

function v50_passport_from_events(array $events): array
{
    $dims=['principle'=>0,'recognition'=>0,'diagnosis'=>0,'repair'=>0,'explain'=>0,'transfer'=>0];
    $lessonSeen=[];
    foreach($events as $row){if(!is_array($row))continue;$kind=(string)($row['kind']??'');$lesson=(int)($row['lesson_number']??0);$lessonSeen[$lesson]=true;
        if($kind==='hypothesis')$dims['principle']++;
        elseif($kind==='command')$dims['recognition']++;
        elseif($kind==='validation'){if(!empty($row['correct'])){$dims['diagnosis']++;$dims['repair']++;}else{$dims['diagnosis']++;}}
        elseif($kind==='explain')$dims['explain']++;
        elseif($kind==='transfer')$dims['transfer']++;
    }
    $den=max(1,count($lessonSeen));foreach($dims as $k=>$v)$dims[$k]=min(100,(int)round(($v/$den)*100));return $dims;
}
function v50_student_passport(string $classId,string $studentKey): array { return v50_passport_from_events(v50_student_events($classId,$studentKey)); }
function v50_stop_rule(string $classId,string $studentKey,int $lessonNo): bool
{
    $events=v50_student_events($classId,$studentKey,$lessonNo);$has=['validation'=>false,'explain'=>false,'transfer'=>false];
    foreach($events as $row){$k=(string)($row['kind']??'');if($k==='validation'&&!empty($row['correct']))$has['validation']=true;elseif(isset($has[$k]))$has[$k]=true;}
    return !in_array(false,$has,true);
}

function v50_peer_room(string $classId,string $roomCode): ?array
{
    $rows=v50_peer_rows();$row=$rows[$classId.'|'.$roomCode]??null;return is_array($row)?$row:null;
}
function v50_peer_join(string $classId,string $studentKey,string $roomCode): array
{
    $roomCode=strtoupper(preg_replace('/[^A-Z0-9]/','',strtoupper($roomCode))??'');if(strlen($roomCode)<4||strlen($roomCode)>10)throw new RuntimeException(tr('Kód peer room musí mít 4–10 znaků.'));
    $key=$classId.'|'.$roomCode;
    return (array)v50_update_row('peer_rooms',$key,static function(?array $room) use($classId,$roomCode,$studentKey): array {$room=$room??['class_id'=>$classId,'code'=>$roomCode,'members'=>[],'notes'=>[],'created_at'=>date(DATE_ATOM)];
    $members=(array)$room['members'];if(!isset($members[$studentKey])){if(count($members)>=3)throw new RuntimeException(tr('Peer room už má tři členy.'));$roles=['driver','observer','explainer'];$members[$studentKey]=['role'=>$roles[count($members)]??'observer','joined_at'=>date(DATE_ATOM)];}
    $room['members']=$members;return $room;});
}
function v50_peer_note(string $classId,string $studentKey,string $roomCode,string $type,string $text): array
{
    $type=in_array($type,['hypothesis','evidence','next_step'],true)?$type:'evidence';$key=$classId.'|'.strtoupper($roomCode);
    $text=v50_clean_text($text,900);if(u_strlen($text)<8)throw new RuntimeException(tr('Poznámka je příliš krátká.'));
    return (array)v50_update_row('peer_rooms',$key,static function(?array $room) use($studentKey,$type,$text): array {if(!is_array($room)||!isset($room['members'][$studentKey]))throw new RuntimeException(tr('Nejsi členem tohoto peer room.'));$room['notes'][]=['student_key'=>$studentKey,'type'=>$type,'text'=>$text,'created_at'=>date(DATE_ATOM)];$room['notes']=array_slice((array)$room['notes'],-60);return $room;});
}

function v50_teacher_heatmap(string $classId): array
{
    $students=project_students_for_class($classId);$out=[];$sum=['principle'=>0,'recognition'=>0,'diagnosis'=>0,'repair'=>0,'explain'=>0,'transfer'=>0];$n=0;
    foreach($students as $student){if(!is_array($student))continue;$label=(string)($student['label']??'');if($label==='')continue;$sk=project_student_key($classId,$label);$p=v50_student_passport($classId,$sk);$out[]=['student_key'=>$sk,'label'=>$label,'passport'=>$p];foreach($sum as $k=>$v)$sum[$k]+=(int)$p[$k];$n++;}
    $avg=[];foreach($sum as $k=>$v)$avg[$k]=$n?(int)round($v/$n):0;return ['students'=>$out,'average'=>$avg,'count'=>$n];
}
