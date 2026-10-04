<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · obsah výukových cest pro 3.A (operační systémy a sítě) – statická data, žádná logika ani zápis.
 *
 * Schéma viz paths_v63.php. Texty jsou výukový obsah (zůstávají česky). Otázky ověřování a opakování se berou
 * z banky týmových her (teamgames_v58_bank_net_*.php) podle id; varianty ověření jsou ≥ 3 a navzájem disjunktní.
 * Krok „pre“ (predict–run–explain) má výstupy příkazů předpočítané simulátorem do paths_v63_pre_data.php
 * (tools/v63_paths_build_pre.php, pevné semínko) – za běhu se nic nespouští. `setup` jsou tiché přípravné příkazy,
 * `cmd` je příkaz, jehož výstup žák předpovídá, `expect` je řetězec, který výstup musí obsahovat (hlídá audit).
 */

return [
    'lnx_chmod' => [
        'title' => 'Práva k souborům a chmod',
        'goal' => 'Přečteš práva z výpisu, nastavíš je příkazem chmod a poznáš, co dovolují.',
        'class' => 'class_3a',
        'competency' => 'lnx_users',
        'minutes' => 25,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Jak číst práva', 'competency' => 'lnx_users', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'Výpis ls -l ukazuje u každého souboru deset znaků, například -rw-r--r--. První znak je typ (- soubor, d složka), dalších devět jsou tři trojice práv: vlastník, skupina, ostatní.',
                    'V každé trojici znamená r čtení (4), w zápis (2) a x spuštění (1). Součet dává číslici: rw- je 6, r-x je 5, rwx je 7, --- je 0.',
                    'Příkaz chmod práva mění: chmod 644 soubor dá vlastníkovi čtení a zápis a ostatním jen čtení. Příkaz chmod +x soubor přidá právo spuštění, bez něj systém skript jako program nespustí.',
                ],
                'example' => "-rw-r--r--  soubor.txt   (644)\n-rwxr-xr-x  skript.sh    (755)\nchmod 600 soubor.txt   →   -rw-------"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Co příkaz vypíše?', 'competency' => 'lnx_users', 'level' => 2, 'minutes' => 5,
                'cases' => [
                    ['id' => 'c1', 'setup' => ['touch report.txt', 'chmod 600 report.txt'], 'cmd' => "stat -c '%A %n' report.txt",
                        'question' => 'Co příkaz vypíše? Soubor report.txt má nastavená práva 600.',
                        'options' => ['-rw------- report.txt', '-rw-r--r-- report.txt', '-rwx------ report.txt', '-rw-rw-rw- report.txt'], 'correct' => 0, 'expect' => '-rw------- report.txt',
                        'explain' => 'Číslice 6 je rw- a 0 je ---. Práva 600 má jen vlastník: čtení a zápis, nikdo jiný nic.'],
                    ['id' => 'c2', 'setup' => ['touch tajne.txt', 'chmod 640 tajne.txt'], 'cmd' => "stat -c '%A %n' tajne.txt",
                        'question' => 'Co příkaz vypíše? Soubor tajne.txt má nastavená práva 640.',
                        'options' => ['-rw-r----- tajne.txt', '-rw-r--r-- tajne.txt', '-rwxr----- tajne.txt', '-rw----r-- tajne.txt'], 'correct' => 0, 'expect' => '-rw-r----- tajne.txt',
                        'explain' => 'Vlastník má 6 (rw-), skupina 4 (r--) a ostatní 0 (---).'],
                    ['id' => 'c3', 'setup' => ['touch skript.sh'], 'cmd' => "chmod 755 skript.sh && stat -c '%A %n' skript.sh",
                        'question' => 'Co příkaz vypíše po nastavení práv 755?',
                        'options' => ['-rwxr-xr-x skript.sh', '-rwxrwxrwx skript.sh', '-rw-r--r-- skript.sh', '-rwx------ skript.sh'], 'correct' => 0, 'expect' => '-rwxr-xr-x skript.sh',
                        'explain' => 'Číslice 7 je rwx a 5 je r-x. Vlastník smí vše, ostatní číst a spouštět.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Poskládej příkazy', 'competency' => 'lnx_users', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Ve složce skripty vytvoř soubor pozdrav.sh, nastav mu práva rwxr-xr-x a výsledek ověř. Seřaď příkazy od prvního k poslednímu.',
                'lines' => ['mkdir skripty', 'cd skripty', 'touch pozdrav.sh', 'chmod 755 pozdrav.sh', 'ls -l pozdrav.sh'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'lnx_users', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['net.files.001', 'net.files.002', 'net.files.003', 'net.files.004', 'net.files.005', 'net.files.006', 'net.files.010', 'net.files.020']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'lnx_users', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['net.files.011', 'net.files.014', 'net.files.016', 'net.files.019'],
                    ['net.files.012', 'net.files.013', 'net.files.018', 'net.files.022'],
                    ['net.files.015', 'net.files.021', 'net.files.023', 'net.files.024'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'lnx_users', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['net.files.003', 'net.files.004', 'net.files.005', 'net.files.011', 'net.files.012', 'net.files.014', 'net.files.016', 'net.files.019', 'net.files.020', 'net.files.022']],
    ],
    'net_dns' => [
        'title' => 'DNS: jak se z jména stane adresa',
        'goal' => 'Vysvětlíš, jak zařízení překládá jména na IP adresy, a přečteš nastavení DNS ve vlastním systému.',
        'class' => 'class_3a',
        'competency' => 'net_dns_dhcp',
        'minutes' => 24,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Jak funguje DNS', 'competency' => 'net_dns_dhcp', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'DNS překládá doménová jména (www.skola.cz) na IP adresy. Zařízení se zeptá DNS serveru a ten vrátí adresu, na kterou se pak připojí.',
                    'Adresu DNS serveru zařízení obvykle dostane od DHCP spolu s IP adresou a bránou. V Linuxu ji najdeš v souboru /etc/resolv.conf. Soubor /etc/hosts umí jméno přiřadit adrese ručně, bez dotazu na DNS.',
                    'Nejčastější záznamy: A (jméno na IPv4), AAAA (jméno na IPv6), MX (kam doručovat e-mail), CNAME (alias). TTL říká, jak dlouho si má odpověď zůstat v mezipaměti.',
                ],
                'example' => "nameserver 1.1.1.1\nhost intranet.skola.test\nintranet.skola.test has address 10.0.0.10"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Co vypíše systém?', 'competency' => 'net_dns_dhcp', 'level' => 2, 'minutes' => 5,
                'cases' => [
                    ['id' => 'c1', 'setup' => [], 'cmd' => 'cat /etc/hosts',
                        'question' => 'Na kterou IP adresu ukazuje v souboru hosts jméno intranet.skola.test?',
                        'options' => ['10.0.0.10', '127.0.0.1', '1.1.1.1', '8.8.8.8'], 'correct' => 0, 'expect' => '10.0.0.10',
                        'explain' => 'Řádek v hosts začíná adresou, za ní následují jména. Adresa 127.0.0.1 patří jménu localhost.'],
                    ['id' => 'c2', 'setup' => [], 'cmd' => 'cat /etc/resolv.conf',
                        'question' => 'Který DNS server se podle souboru zkusí jako první?',
                        'options' => ['1.1.1.1', '8.8.8.8', '10.0.0.10', '127.0.0.1'], 'correct' => 0, 'expect' => 'nameserver 1.1.1.1',
                        'explain' => 'Servery se zkouší v pořadí řádků nameserver. Druhý řádek slouží jako záloha.'],
                    ['id' => 'c3', 'setup' => [], 'cmd' => 'host intranet.skola.test',
                        'question' => 'Co příkaz host vypíše pro jméno intranet.skola.test?',
                        'options' => ['intranet.skola.test has address 10.0.0.10', 'Host intranet.skola.test not found: 3(NXDOMAIN)', 'intranet.skola.test mail is handled by 10 mx.skola.test', 'intranet.skola.test is an alias for www.skola.test'], 'correct' => 0, 'expect' => 'intranet.skola.test has address 10.0.0.10',
                        'explain' => 'Jméno existuje a má záznam A, proto host vypíše jeho adresu.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Poskládej postup překladu', 'competency' => 'net_dns_dhcp', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Seřaď zjednodušený postup, jak zařízení zjistí adresu serveru podle jména www.priklad.cz.',
                'lines' => ['Zařízení zkontroluje mezipaměť a soubor hosts', 'Zařízení se zeptá DNS serveru na jméno www.priklad.cz', 'DNS server najde nebo dohledá IP adresu', 'Zařízení dostane IP adresu jako odpověď', 'Zařízení se podle IP adresy připojí k cílovému serveru'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'net_dns_dhcp', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['net.dns.001', 'net.dns.002', 'net.dns.004', 'net.dns.005', 'net.dns.006', 'net.dns.007', 'net.dns.009', 'net.dns.010']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'net_dns_dhcp', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['net.dns.011', 'net.dns.012', 'net.dns.014', 'net.dns.016'],
                    ['net.dns.013', 'net.dns.015', 'net.dns.018', 'net.dns.019'],
                    ['net.dns.020', 'net.dns.022', 'net.dns.023', 'net.dns.024'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'net_dns_dhcp', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['net.dns.001', 'net.dns.003', 'net.dns.004', 'net.dns.006', 'net.dns.007', 'net.dns.011', 'net.dns.012', 'net.dns.013', 'net.dns.018', 'net.dns.020']],
    ],
];
