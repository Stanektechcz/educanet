<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úloh „incident“ (ARN-03, inspirace SadServers) + registrace kontextu
 * incident:<id>. 6 scénářů, každý typu „check“ (automatické ověření). Balíček je schovaný z běžného
 * procvičování stejným trikem jako u CTF (classes => [ARENA58_INC_HIDDEN_CLASS]) – scénáře jsou dostupné
 * jen přes vyhlášenou incidentní směnu, protože mají osobní 15minutový odpočet, který v procvičování nedává
 * smysl. Reálná logika (přístup, časovač, postmortem) je v arena_v58_incident.php – volá se přes
 * function_exists (stejný vzor jako jádrový kontext „race“ a náš vlastní kontext „ctf“).
 */

lab58_register_context('incident', [
    'label' => 'Incident',
    'access' => static fn(array $level, array $ctx): ?string => function_exists('arena58_inc_access') ? arena58_inc_access($level, $ctx) : 'Incidenty momentálně nejsou dostupné.',
    'levels' => static function (array $ctx): ?array {
        if (!function_exists('arena58_inc_session') || !function_exists('arena58_inc_level_ids')) return null;
        $session = arena58_inc_session((string)$ctx['id']);
        return $session !== null ? arena58_inc_level_ids($session) : [];
    },
    'state_key' => static fn(array $ctx): string => function_exists('arena58_inc_state_key') ? arena58_inc_state_key($ctx) : (string)$ctx['student'],
    'info' => static fn(array $ctx): ?array => function_exists('arena58_inc_info') ? arena58_inc_info($ctx) : null,
    // Body dá až postmortem (arena58_inc_submit_postmortem) – technická oprava sama o sobě body nedává.
    'points' => static fn(array $level, int $hints, array $ctx): int => 0,
    'on_complete' => static function (array $ctx, array $level, array $event): void {
        if (function_exists('arena58_inc_on_complete')) arena58_inc_on_complete($ctx, $level, $event);
    },
    'reset' => false,
]);

lab58_register_pack(
    [
        'id' => 'incident', 'title' => 'Incidenty', 'description' => 'Časově tlačené opravné scénáře – dostupné jen během vyhlášené směny.',
        'order' => 81, 'classes' => [defined('ARENA58_INC_HIDDEN_CLASS') ? ARENA58_INC_HIDDEN_CLASS : 'zzz_nikdy_trida_inc58'],
        'unlock' => 'free', 'badge' => ['id' => 'hasic58', 'label' => 'Požárník serverů', 'icon' => '🧯'], 'icon' => '🧯', 'tone' => 'amber', 'inspired' => 'SadServers', 'source' => 'v58',
    ],
    static function (): array {
        return [
            [
                'id' => 'inc-web', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'Web neodpovídá',
                'difficulty' => 2, 'points' => 150, 'minutes' => 15, 'world' => ['hostname' => 'web-01'],
                'pager' => '📟 PAGER: web-01 neodpovídá na health-check! Zákazníci hlásí chybu, SLA hodiny běží.',
                'story' => 'Po nasazení nové verze webu přestal server nginx odpovídat. Tým čeká na tvoji diagnózu.',
                'task' => 'Zjisti příčinu, oprav konfiguraci a spusť nginx tak, aby http://localhost vracelo 200.',
                'commands' => ['systemctl', 'journalctl', 'nginx', 'sudo', 'nano', 'sed', 'curl'],
                'hints' => ['systemctl status nginx a journalctl -u nginx ukážou, že služba spadla.', 'sudo nginx -t zkontroluje konfiguraci a řekne řádek s chybou.', 'Oprav řádek, pak sudo systemctl restart nginx a ověř curl -I localhost.'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $directive = (string)$r->pick(['root /var/www/html;', 'index index.html;', 'server_name _;']);
                    $site = str_replace('    ' . $directive, '    ' . rtrim($directive, ';'), lab57_nginx_site());
                    $w->mkfile('/etc/nginx/sites-enabled/default', $site, 0644, 'root', 'root', $w->now - 900);
                    $w->mem['incweb_directive'] = $directive;
                    $w->services['nginx']['active'] = false;
                    $w->services['nginx']['failed'] = true;
                    $w->services['nginx']['result'] = 'exit-code';
                    $w->services['nginx']['since'] = $w->now - 800;
                    foreach ($w->procs as $pid => $proc) if (($proc['service'] ?? '') === 'nginx') unset($w->procs[$pid]);
                    foreach (lab57_nginx_check($w) as $line) $w->journalAdd('nginx', $line, 'err');
                    $w->journalAdd('systemd', 'nginx.service: Control process exited, code=exited, status=1/FAILURE', 'err');
                },
                'checks' => [
                    ['label' => 'Konfigurace nginx je bez chyb', 'fn' => static fn(Lab57World $w): bool => lab57_nginx_check($w) === []],
                    ['label' => 'Služba nginx běží', 'fn' => static fn(Lab57World $w): bool => !empty($w->services['nginx']['active'])],
                    ['label' => 'http://localhost vrací 200', 'fn' => static fn(Lab57World $w): bool => lab57_http_status($w, 'localhost', 80) === 200],
                ],
                'solution' => static function (Lab57World $w): array {
                    $bad = rtrim((string)($w->mem['incweb_directive'] ?? 'root /var/www/html;'), ';');
                    return ['systemctl status nginx', 'sudo nginx -t', "sudo sed -i 's|" . $bad . "$|" . $bad . ";|' /etc/nginx/sites-enabled/default", 'sudo systemctl restart nginx', 'curl -I localhost'];
                },
                'learn' => 'Postup opravy: status → log → test konfigurace → oprava → restart → ověření. Funguje na skoro každou spadlou službu.',
            ],
            [
                'id' => 'inc-disk', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'Disk je plný',
                'difficulty' => 2, 'points' => 150, 'minutes' => 15, 'world' => ['hostname' => 'app-01'],
                'pager' => '📟 PAGER: aplikace „zapisovac-udalosti“ spadla – No space left on device!',
                'story' => 'Aplikace, co ukládá záznamy o docházce, přestala fungovat. V logu je „No space left on device“.',
                'task' => 'Najdi, co zabírá místo na disku, uvolni ho pod 90 % a službu znovu spusť.',
                'commands' => ['df', 'du', 'ls', 'truncate', 'sudo', 'systemctl'],
                'hints' => ['df -h ukáže zaplnění disků.', 'Obří soubor v /var/log najdeš: sudo du -ah /var/log | sort -h | tail', 'Vyprázdni ho: sudo truncate -s 0 <soubor>, pak sudo systemctl restart zapisovac-udalosti.'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $name = (string)$r->pick(['ladeni.log', 'podrobny.log', 'udalosti-verbose.log']);
                    $w->mem['incdisk_log'] = '/var/log/zapisovac-udalosti/' . $name;
                    $w->mkfile('/var/log/zapisovac-udalosti/' . $name, "[DEBUG] start\n", 0640, 'root', 'adm', $w->now - 60, ['s' => 15100000000]);
                    $w->mkfile('/var/log/zapisovac-udalosti/zapisovac.log', "INFO start\nERROR write failed: No space left on device\n", 0640, 'root', 'adm', $w->now - 120);
                    $w->services['zapisovac-udalosti'] = ['desc' => 'Zapisovac udalosti dochazky', 'active' => false, 'enabled' => true, 'failed' => true, 'result' => 'exit-code', 'since' => $w->now - 120, 'ports' => [], 'validate' => 'disk_inc', 'procs' => [['root', '/usr/local/bin/zapisovac-udalosti']]];
                    $w->journalAdd('zapisovac-udalosti', 'write failed: No space left on device', 'err');
                },
                'programs' => static function (Lab57World $w): void {
                    $w->programs['validate:disk_inc'] = static function (Lab57World $world, string $unit): array {
                        $disk = lab57_disk_usage($world);
                        return $disk['used'] / $disk['size'] >= 0.99 ? ['zapisovac-udalosti: write failed: No space left on device'] : [];
                    };
                },
                'checks' => [
                    ['label' => 'Disk je zaplněný pod 90 %', 'fn' => static function (Lab57World $w): bool { $d = lab57_disk_usage($w); return $d['used'] / $d['size'] < 0.9; }],
                    ['label' => 'Služba zapisovac-udalosti běží', 'fn' => static fn(Lab57World $w): bool => !empty($w->services['zapisovac-udalosti']['active'])],
                ],
                'solution' => static fn(Lab57World $w): array => ['df -h', 'sudo du -ah /var/log | sort -h | tail -3', 'sudo truncate -s 0 ' . (string)($w->mem['incdisk_log'] ?? ''), 'sudo systemctl restart zapisovac-udalosti'],
                'learn' => 'Plný disk umí shodit cokoli. df ukáže KDE, du ukáže CO. V praxi logy hlídá logrotate.',
            ],
            [
                'id' => 'inc-dns', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'DNS nefunguje',
                'difficulty' => 2, 'points' => 150, 'minutes' => 15,
                'pager' => '📟 PAGER: uživatelé hlásí „Temporary failure in name resolution“ na intranetu.',
                'story' => 'ping na 1.1.1.1 funguje, ale jména se nepřekládají – DNS server v resolv.conf je špatně.',
                'task' => 'Oprav /etc/resolv.conf tak, aby používal DNS server 1.1.1.1, a ověř, že jméno www.example.com jde přeložit.',
                'commands' => ['cat', 'ping', 'dig', 'sudo', 'tee'],
                'hints' => ['Použitý DNS server je v /etc/resolv.conf (řádek nameserver).', 'echo "nameserver 1.1.1.1" | sudo tee /etc/resolv.conf', 'Ověř: dig +short www.example.com'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $w->mkfile('/etc/resolv.conf', "# Vygeneroval DHCP klient\nnameserver 10.99.99.99\nsearch skola.test\n", 0644, 'root', 'root', $w->now - 600);
                },
                'checks' => [
                    ['label' => 'resolv.conf obsahuje nameserver 1.1.1.1', 'fn' => static fn(Lab57World $w): bool => in_array('1.1.1.1', lab57_net_nameservers($w), true)],
                    ['label' => 'Jméno www.example.com se přeloží', 'fn' => static fn(Lab57World $w): bool => lab57_net_resolve($w, 'www.example.com')['ip'] !== null],
                ],
                'solution' => static fn(Lab57World $w): array => ['cat /etc/resolv.conf', 'echo "nameserver 1.1.1.1" | sudo tee /etc/resolv.conf', 'dig +short www.example.com'],
                'learn' => 'Když ping na IP funguje, ale na jméno ne, chyba je skoro vždy v DNS.',
            ],
            [
                'id' => 'inc-perms', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'Služba nemůže zapisovat',
                'difficulty' => 2, 'points' => 150, 'minutes' => 15,
                'pager' => '📟 PAGER: monitorovací služba „hlidac“ hlásí Permission denied a spadla.',
                'story' => 'Monitorovací služba hlidac běží pod účtem student, ale svůj log soubor nemůže otevřít k zápisu.',
                'task' => 'Oprav práva/vlastníka souboru /var/log/hlidac/hlidac.log tak, aby do něj uživatel student mohl zapisovat, a službu restartuj.',
                'commands' => ['ls', 'chmod', 'chown', 'sudo', 'systemctl'],
                'hints' => ['ls -l /var/log/hlidac ukáže, komu soubor patří a jaká má práva.', 'sudo chown student /var/log/hlidac/hlidac.log (nebo sudo chmod o+w …).', 'Pak: sudo systemctl restart hlidac'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $w->mkfile('/var/log/hlidac/hlidac.log', "[INFO] start\n", 0644, 'root', 'root', $w->now - 500);
                    $w->services['hlidac'] = ['desc' => 'Hlidac - monitorovaci sluzba', 'active' => false, 'enabled' => true, 'failed' => true, 'result' => 'exit-code', 'since' => $w->now - 400, 'ports' => [], 'validate' => 'hlidac_perm', 'procs' => [['student', '/usr/local/bin/hlidac']]];
                    $w->journalAdd('hlidac', 'nelze zapsat do /var/log/hlidac/hlidac.log: Permission denied', 'err');
                },
                'programs' => static function (Lab57World $w): void {
                    $w->programs['validate:hlidac_perm'] = static function (Lab57World $world, string $unit): array {
                        $node = $world->fs->get('/var/log/hlidac/hlidac.log');
                        if ($node === null) return ['hlidac: log soubor chybí'];
                        $ok = (string)($node['u'] ?? '') === 'student' || ((int)($node['m'] ?? 0) & 0002) !== 0;
                        return $ok ? [] : ['hlidac: nelze zapsat do /var/log/hlidac/hlidac.log: Permission denied'];
                    };
                },
                'checks' => [
                    ['label' => 'Log soubor je zapisovatelný pro hlidac', 'fn' => static function (Lab57World $w): bool {
                        $n = $w->fs->get('/var/log/hlidac/hlidac.log');
                        return $n !== null && ((string)($n['u'] ?? '') === 'student' || ((int)($n['m'] ?? 0) & 0002) !== 0);
                    }],
                    ['label' => 'Služba hlidac běží', 'fn' => static fn(Lab57World $w): bool => !empty($w->services['hlidac']['active'])],
                ],
                'solution' => static fn(Lab57World $w): array => ['ls -l /var/log/hlidac', 'sudo chown student /var/log/hlidac/hlidac.log', 'sudo systemctl restart hlidac'],
                'learn' => 'Služba běží pod nějakým uživatelem – ten musí mít práva ke všem souborům, se kterými pracuje.',
            ],
            [
                'id' => 'inc-cpu', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'Zatoulaný proces',
                'difficulty' => 2, 'points' => 150, 'minutes' => 15,
                'pager' => '📟 PAGER: server je extrémně pomalý, procesor na 100 %!',
                'story' => 'Server je hrozně pomalý. Nějaký zapomenutý skript se zasekl v nekonečné smyčce a žere skoro celý procesor.',
                'task' => 'Najdi proces, který vytěžuje procesor, a ukonči ho.',
                'commands' => ['top', 'ps', 'kill'],
                'hints' => ['ps aux --sort=-%cpu seřadí procesy podle zátěže.', 'Ukonči ho: kill <PID>. Když nereaguje: kill -9 <PID>.'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $pid = $r->int(2800, 2990);
                    $name = (string)$r->pick(['python3 import_dat.py', 'bash zaloha_smycka.sh', 'python3 report_gen.py']);
                    $w->procs[$pid] = ['user' => 'student', 'cmd' => $name, 'cpu' => 97.8, 'mem' => 1.6, 'vsz' => 29800, 'rss' => 11800, 'tty' => 'pts/1', 'stat' => 'R', 'start' => $w->now - 2600, 'service' => null, 'ignore_term' => $r->int(0, 1) === 1];
                    $w->mem['inccpu_pid'] = $pid;
                },
                'checks' => [
                    ['label' => 'Proces s vysokou zátěží už neběží', 'fn' => static fn(Lab57World $w): bool => !isset($w->procs[(int)($w->mem['inccpu_pid'] ?? 0)])],
                ],
                'solution' => static fn(Lab57World $w): array => ['ps aux --sort=-%cpu', 'kill ' . (int)($w->mem['inccpu_pid'] ?? 0), 'kill -9 ' . (int)($w->mem['inccpu_pid'] ?? 0)],
                'learn' => 'kill posílá signál TERM (slušná žádost o konec). kill -9 ukončí proces okamžitě – použij ho, až když normální kill nezabere.',
            ],
            [
                'id' => 'inc-cron', 'pack' => 'incident', 'type' => 'check', 'category' => 'incident', 'title' => 'Naplánovaná úloha zaplňuje log',
                'difficulty' => 3, 'points' => 200, 'minutes' => 15,
                'pager' => '📟 PAGER: disk se rychle plní, naplánovaná úloha běží mnohem častěji, než má.',
                'story' => 'Služba pravidelny-report se má spouštět jednou denně, ale kvůli chybě v nastavení běží pořád dokola a její log rychle roste. Disk brzy dojde.',
                'task' => 'Zastav přemrštěně běžící službu pravidelny-report natrvalo a ulev disku, aby zaplnění kleslo pod 90 %.',
                'commands' => ['systemctl', 'df', 'du', 'truncate', 'sudo'],
                'hints' => ['systemctl status pravidelny-report ukáže, že běží.', 'Zastav ji natrvalo: sudo systemctl disable --now pravidelny-report', 'Log vyprázdni: sudo truncate -s 0 /var/log/pravidelny-report.log'],
                'build' => static function (Lab57World $w, Lab57Rng $r): void {
                    $w->mkfile('/var/log/pravidelny-report.log', str_repeat("[INFO] report vygenerovan\n", 40), 0644, 'root', 'root', $w->now - 60, ['s' => 15300000000]);
                    $w->services['pravidelny-report'] = ['desc' => 'Pravidelny report - naplanovana uloha', 'active' => true, 'enabled' => true, 'failed' => false, 'since' => $w->now - 30, 'ports' => [], 'procs' => [['root', '/usr/local/bin/pravidelny-report --loop']]];
                },
                'checks' => [
                    ['label' => 'Služba pravidelny-report už neběží', 'fn' => static fn(Lab57World $w): bool => empty($w->services['pravidelny-report']['active'])],
                    ['label' => 'Disk je zaplněný pod 90 %', 'fn' => static function (Lab57World $w): bool { $d = lab57_disk_usage($w); return $d['used'] / $d['size'] < 0.9; }],
                ],
                'solution' => static fn(Lab57World $w): array => ['systemctl status pravidelny-report', 'sudo systemctl disable --now pravidelny-report', 'sudo truncate -s 0 /var/log/pravidelny-report.log', 'df -h'],
                'learn' => 'Naplánované úlohy (cron, systemd timery) musí mít rozumný interval – špatně nastavené umí zahltit disk i procesor během chvilky.',
            ],
        ];
    }
);
