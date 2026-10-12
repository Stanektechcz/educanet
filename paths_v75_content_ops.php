<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v75 · obsah výukových cest pro 4.A (provoz sítí a služeb) – NÁVRH, statická data bez logiky a zápisu.
 *
 * Schéma viz paths_v63.php. Texty jsou výukový obsah (česky). Otázky ověřování a opakování: banka týmových her (net.ip.*, net.ports.*)
 * a banka cest v75 (ops.backup.*, paths_v75_bank.php); varianty ověření jsou 3 × 4 navzájem disjunktní. Kroky „pre“ jsou statické
 * (nic se nespouští ani nepřipojuje); případy s polem `calc` přepočítá tools/v75_competency_audit.php (adresy, RPO/RTO, objem).
 * Adresy jen privátní nebo dokumentační (RFC 1918 / RFC 5737), domény *.test / *.example.
 */

return [
    'ops_routing' => [
        'title' => 'Adresní plán a směrování',
        'goal' => 'Rozdělíš adresní prostor na podsítě podle potřeby, určíš síť a broadcast a vybereš správnou trasu.',
        'class' => 'class_4a',
        'competency' => 'ops_routing',
        'minutes' => 26,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Prefix, podsíť a trasa', 'competency' => 'ops_routing', 'level' => 2, 'minutes' => 4,
                'paragraphs' => [
                    'Prefix /n říká, kolik bitů adresy patří síti. Zbylých 32 − n bitů je pro zařízení: síť má 2^(32 − n) adres, použitelných je o dvě méně (síťová a broadcastová adresa).',
                    'Síťovou adresu dostaneš, když vynuluješ bity zařízení; broadcast, když je nastavíš na jedničky. U /26 jsou bloky po 64 adresách: 0, 64, 128, 192 – adresa .77 tedy leží v síti .64 a broadcast je .127.',
                    'Směrovač vybírá trasu s nejdelším shodným prefixem: když má trasu 10.0.0.0/8 i 10.0.4.0/22, paket pro 10.0.5.9 půjde přes /22. Výchozí trasa 0.0.0.0/0 se použije, jen když nic jiného nesedí.',
                ],
                'example' => "192.168.10.77/26\n  síť      192.168.10.64\n  broadcast 192.168.10.127\n  zařízení  .65 – .126 (62 adres)\n\nip route\n  default via 10.0.0.1 dev eth0\n  10.0.4.0/22 via 10.0.0.254 dev eth0"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Spočítej dřív, než to ověříš', 'competency' => 'ops_routing', 'level' => 3, 'minutes' => 6,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => 'Podsíť 10.0.8.0/22 pro novou budovu.', 'calc' => ['kind' => 'hosts', 'prefix' => 22],
                        'question' => 'Kolik použitelných adres pro zařízení má síť /22?', 'options' => ['1022', '1024', '510'], 'correct' => 0,
                        'reveal' => '2^(32 − 22) = 1024 adres, bez síťové a broadcastové 1022.', 'explain' => '/22 jsou čtyři sítě /24 vedle sebe; vždy odečti dvě vyhrazené adresy.'],
                    ['id' => 'c2', 'static' => true, 'show' => 'Server má adresu 192.168.10.77/26.', 'calc' => ['kind' => 'network', 'ip' => '192.168.10.77', 'prefix' => 26],
                        'question' => 'Jaká je síťová adresa?', 'options' => ['192.168.10.64', '192.168.10.0', '192.168.10.77'], 'correct' => 0,
                        'reveal' => 'Bloky /26 začínají po 64: 0, 64, 128, 192. Adresa .77 je v bloku od .64.', 'explain' => 'Síťová adresa = nejbližší nižší násobek velikosti bloku.'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Rozhraní má adresu 172.16.5.130/25.', 'calc' => ['kind' => 'broadcast', 'ip' => '172.16.5.130', 'prefix' => 25],
                        'question' => 'Jaká je broadcastová adresa této sítě?', 'options' => ['172.16.5.255', '172.16.5.127', '172.16.5.254'], 'correct' => 0,
                        'reveal' => '/25 dělí oktet na 0–127 a 128–255; .130 je v horní polovině, broadcast je .255.', 'explain' => 'Broadcast je poslední adresa bloku (síť + velikost bloku − 1).'],
                    ['id' => 'c4', 'static' => true, 'show' => 'Firewall povoluje přístup ze sítě 10.0.0.0/22. Připojuje se klient 10.0.3.200.', 'calc' => ['kind' => 'contains', 'net' => '10.0.0.0', 'prefix' => 22, 'ip' => '10.0.3.200'],
                        'question' => 'Spadá klient do povolené sítě?', 'options' => ['Ano', 'Ne'], 'correct' => 0,
                        'reveal' => '10.0.0.0/22 pokrývá 10.0.0.0 až 10.0.3.255, takže 10.0.3.200 patří do ní.', 'explain' => 'U /22 se mění třetí oktet po čtyřech: 0–3, 4–7, 8–11 …'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Postup návrhu adresního plánu', 'competency' => 'ops_routing', 'level' => 3, 'minutes' => 5,
                'prompt' => 'Seřaď kroky tak, jak navrhneš adresní plán pro tři oddělení a ověříš ho.',
                'lines' => ['Spočítej, kolik zařízení potřebuje každé oddělení (s rezervou)', 'Seřaď oddělení od největšího a každému přiřaď nejmenší dostatečný prefix', 'Rozděl přidělený blok adres bez překryvů', 'Zapiš síť, bránu a broadcast do tabulky plánu', 'Doplň trasy na směrovači a ověř je příkazem ip route', 'Otestuj spojení mezi oddíly a zapiš výsledek'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'ops_routing', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['net.ip.001', 'net.ip.002', 'net.ip.003', 'net.ip.005', 'net.ip.006', 'net.ip.007', 'net.ip.016', 'net.ip.017']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'ops_routing', 'level' => 3, 'minutes' => 5,
                'variants' => [
                    ['net.ip.011', 'net.ip.018', 'net.ip.019', 'net.ip.004'],
                    ['net.ip.012', 'net.ip.020', 'net.ip.008', 'net.ip.024'],
                    ['net.ip.013', 'net.ip.021', 'net.ip.009', 'net.ip.023'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'ops_routing', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['net.ip.005', 'net.ip.006', 'net.ip.011', 'net.ip.012', 'net.ip.013', 'net.ip.016', 'net.ip.017', 'net.ip.018', 'net.ip.021', 'net.ip.024']],
    ],
    'ops_proxy' => [
        'title' => 'Reverzní proxy a certifikát TLS',
        'goal' => 'Předpovíš, co vrátí služba za reverzní proxy, a podle chyby poznáš, jestli je problém v backendu, portu nebo certifikátu.',
        'class' => 'class_4a',
        'competency' => 'ops_proxy_tls',
        'minutes' => 25,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Proxy, backend a TLS', 'competency' => 'ops_proxy_tls', 'level' => 2, 'minutes' => 4,
                'paragraphs' => [
                    'Reverzní proxy (např. nginx) přijímá požadavky na portu 443, ukončí šifrování TLS a předá požadavek aplikaci (backendu) na vnitřním portu, třeba 127.0.0.1:8080.',
                    'Když se proxy k backendu nedovolá (aplikace neběží nebo poslouchá jinde), klient dostane 502 Bad Gateway. Když backend odpovídá příliš pomalu, přijde 504 Gateway Timeout. Chyba 404 naopak znamená, že backend odpověděl, jen stránku nezná.',
                    'Certifikát TLS musí být platný (datum), vydaný pro stejné jméno, jaké klient otevírá, a podepsaný důvěryhodnou autoritou. Jinak klient spojení odmítne ještě před odesláním požadavku – proxy ani backend ho vůbec neuvidí.',
                ],
                'example' => "server {\n  listen 443 ssl;\n  server_name shop.example.test;\n  ssl_certificate     /etc/ssl/shop.crt;\n  ssl_certificate_key /etc/ssl/shop.key;\n  location / { proxy_pass http://127.0.0.1:8080; }\n}"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Co klient uvidí?', 'competency' => 'ops_proxy_tls', 'level' => 3, 'minutes' => 6,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => 'Proxy má proxy_pass http://127.0.0.1:8080, ale aplikace po změně naslouchá na 127.0.0.1:8081.',
                        'question' => 'Co vrátí curl -I https://shop.example.test?', 'options' => ['502 Bad Gateway', '200 OK', '404 Not Found'], 'correct' => 0,
                        'reveal' => 'HTTP/1.1 502 Bad Gateway – v error logu proxy je „connect() failed (111: Connection refused)“.', 'explain' => '502 = proxy se nedovolala na backend; zkontroluj port v proxy_pass a ss -ltn.'],
                    ['id' => 'c2', 'static' => true, 'show' => 'Certifikát shop.example.test platí do 10. 10. 2026, dnes je 12. 10. 2026.',
                        'question' => 'Co se stane při curl https://shop.example.test?', 'options' => ['curl spojení odmítne: certifikát vypršel', 'spojení projde, jen se zobrazí varování v logu', 'proxy vrátí 502'], 'correct' => 0,
                        'reveal' => 'curl: (60) SSL certificate problem: certificate has expired.', 'explain' => 'Kontrola certifikátu proběhne při navazování TLS – dřív, než se požadavek dostane k proxy.'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Certifikát je vydaný jen pro www.shop.example.test, uživatel otevírá shop.example.test.',
                        'question' => 'Projde ověření certifikátu?', 'options' => ['Ne, jméno v certifikátu nesouhlasí', 'Ano, je to stejná doména', 'Ano, pokud běží port 443'], 'correct' => 0,
                        'reveal' => 'curl: (60) SSL: no alternative certificate subject name matches target host name.', 'explain' => 'Jméno musí být v poli SAN certifikátu; řešením je certifikát pro obě jména.'],
                    ['id' => 'c4', 'static' => true, 'show' => 'Vyvažovač má dva backendy s kontrolou zdraví (health check). Backend B neodpovídá, A běží.',
                        'question' => 'Co dostane většina klientů?', 'options' => ['200 OK z backendu A', '502 pro každý druhý požadavek', '504 Gateway Timeout'], 'correct' => 0,
                        'reveal' => 'Health check B vyřadí a provoz jde jen na A; v metrikách uvidíš 1 zdravý backend ze 2.', 'explain' => 'Bez kontroly zdraví by round-robin posílal polovinu požadavků na mrtvý backend.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Diagnostika 502', 'competency' => 'ops_proxy_tls', 'level' => 3, 'minutes' => 5,
                'prompt' => 'Seřaď kroky, jak najdeš příčinu chyby 502 za reverzní proxy.',
                'lines' => ['Zopakuj požadavek curl -I a zapiš přesný stavový kód', 'Přečti error log proxy (journalctl -u nginx nebo error.log)', 'Ověř, že backend běží (systemctl status)', 'Zkontroluj, na jakém portu backend naslouchá (ss -ltn)', 'Oprav port v proxy_pass nebo spusť backend a otestuj konfiguraci (nginx -t)', 'Načti konfiguraci znovu a ověř 200 OK'],
                'accept' => [[0, 1, 3, 2, 4, 5]]],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'ops_proxy_tls', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['net.ports.001', 'net.ports.002', 'net.ports.003', 'net.ports.004', 'net.ports.006', 'net.ports.007', 'net.ports.008', 'net.ports.010']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'ops_proxy_tls', 'level' => 3, 'minutes' => 5,
                'variants' => [
                    ['net.ports.015', 'net.ports.016', 'net.ports.017', 'net.ports.022'],
                    ['net.ports.019', 'net.ports.020', 'net.ports.024', 'net.ports.018'],
                    ['net.ports.021', 'net.ports.009', 'net.ports.013', 'net.ports.023'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'ops_proxy_tls', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['net.ports.002', 'net.ports.010', 'net.ports.015', 'net.ports.016', 'net.ports.017', 'net.ports.019', 'net.ports.020', 'net.ports.022', 'net.ports.024']],
    ],
    'ops_backup' => [
        'title' => 'Záloha a obnova podle RPO a RTO',
        'goal' => 'Spočítáš skutečné RPO a RTO, navrhneš zálohu podle pravidla 3-2-1 a víš, jak ověřit obnovu.',
        'class' => 'class_4a',
        'competency' => 'ops_backup',
        'minutes' => 26,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'RPO, RTO a 3-2-1', 'competency' => 'ops_backup', 'level' => 2, 'minutes' => 4,
                'paragraphs' => [
                    'RPO říká, kolik dat smíme ztratit: zálohuje-li se každých 6 hodin, v nejhorším případě přijdeme o 6 hodin práce. RTO říká, jak dlouho smí obnova trvat – patří do něj přenos dat, spuštění služby i kontrola.',
                    'Pravidlo 3-2-1: tři kopie dat, na dvou různých typech médií, jedna mimo budovu. RAID ani snapshot na stejném úložišti zálohu nenahrazují – smazání nebo zašifrování se do nich propíše také.',
                    'Záloha je ověřená, až když z ní obnovíš data a zkontroluješ je (kontrolní součet, spuštění služby). Nácvik obnovy změří skutečné RTO a odhalí chybějící klíče nebo kroky v runbooku.',
                ],
                'example' => "zálohy: 00:00 a 12:00\nvýpadek: 23:30\nztráta dat (RPO) = 23:30 − 12:00 = 11,5 h\nobnova 120 GB při 1,5 GB/min = 80 min"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Spočítej dopad výpadku', 'competency' => 'ops_backup', 'level' => 3, 'minutes' => 6,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => 'Zálohy běží ve 0:00 a ve 12:00. Disk selže ve 23:30.', 'calc' => ['kind' => 'minutes_between', 'from' => '12:00', 'to' => '23:30', 'answer' => 690],
                        'question' => 'Kolik dat se ztratí?', 'options' => ['11,5 hodiny', '30 minut', '12 hodin'], 'correct' => 0,
                        'reveal' => 'Poslední záloha je z 12:00, ztratí se 11 h 30 min práce.', 'explain' => 'Skutečné RPO = čas výpadku − čas poslední úspěšné zálohy.'],
                    ['id' => 'c2', 'static' => true, 'show' => 'Obnova přenáší 120 GB rychlostí 1,5 GB/min. RTO je 60 minut.', 'calc' => ['kind' => 'transfer', 'size' => 120, 'rate' => 1.5, 'answer' => 80],
                        'question' => 'Splní obnova RTO?', 'options' => ['Ne, jen přenos trvá 80 minut', 'Ano, trvá 40 minut', 'Ano, trvá přesně 60 minut'], 'correct' => 0,
                        'reveal' => '120 / 1,5 = 80 minut – RTO je překročené ještě před spuštěním služby.', 'explain' => 'Do RTO se počítá i ověření a spuštění; rychlejší je obnova z lokální kopie.'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Plná záloha 100 GB v neděli, pak 5 denních přírůstků po 4 GB.', 'calc' => ['kind' => 'sum', 'parts' => [100, 4, 4, 4, 4, 4], 'answer' => 120],
                        'question' => 'Kolik dat musíš v pátek přenést, abys obnovil/a stav z pátku?', 'options' => ['120 GB', '104 GB', '100 GB'], 'correct' => 0,
                        'reveal' => '100 + 5 × 4 = 120 GB: plná záloha a všechny přírůstky po ní.', 'explain' => 'U přírůstkové zálohy potřebuješ celý řetězec – chybějící přírůstek přeruší obnovu.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Nácvik obnovy (game day)', 'competency' => 'ops_backup', 'level' => 3, 'minutes' => 5,
                'prompt' => 'Seřaď kroky nácviku obnovy ze zálohy.',
                'lines' => ['Vyber zálohu a zapiš čas jejího vzniku', 'Obnov data na oddělený testovací server', 'Ověř kontrolní součty obnovených souborů', 'Spusť službu a zkontroluj, že odpovídá', 'Změř celkový čas a porovnej ho s RTO', 'Doplň zjištění do runbooku obnovy'],
                'accept' => [[0, 1, 3, 2, 4, 5]]],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'ops_backup', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['ops.backup.001', 'ops.backup.002', 'ops.backup.005', 'ops.backup.006', 'ops.backup.007', 'ops.backup.010', 'ops.backup.011', 'ops.backup.018']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'ops_backup', 'level' => 3, 'minutes' => 5,
                'variants' => [
                    ['ops.backup.003', 'ops.backup.012', 'ops.backup.014', 'ops.backup.017'],
                    ['ops.backup.004', 'ops.backup.008', 'ops.backup.015', 'ops.backup.020'],
                    ['ops.backup.009', 'ops.backup.013', 'ops.backup.019', 'ops.backup.022'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'ops_backup', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['ops.backup.001', 'ops.backup.002', 'ops.backup.003', 'ops.backup.004', 'ops.backup.008', 'ops.backup.009', 'ops.backup.013', 'ops.backup.016', 'ops.backup.019', 'ops.backup.021']],
    ],
];
