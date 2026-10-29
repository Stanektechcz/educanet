<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 3.A (operační systémy a sítě), lekce 5–10.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php.
 * Příkazy: u úkolů s klíčem `sim` je uvedený příkaz a očekávaný úsek výstupu simulátoru Linux Labu (výchozí svět
 * lab57_world_new, počítač lab-pc, síť 10.0.0.0/24, intranet.skola.test = 10.0.0.10). tools/v72_lesson_content_audit.php
 * každý takový příkaz spustí v simulátoru (nic se nespouští ve skutečném systému) a ověří, že výstup obsahuje `expect`.
 * Sítě se zkouší jen v simulátoru; adresy jsou privátní (RFC 1918) nebo dokumentační (RFC 5737). Kompetence: katalog v62 „os_site“.
 */

return ['class_3a' => ['lessons' => [
    5 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 5 · IPv6 a záznamy DNS',
        'goal' => [
            'student' => 'Na konci hodiny umím přečíst a zkrátit adresu IPv6, poznat lokální adresu linky a ověřit záznamy DNS typu A, AAAA a CNAME příkazem dig.',
            'success_criteria' => ['Zkrátím adresu IPv6 podle pravidel a určím prefix /64.', 'V simulátoru najdu adresu fe80:: a vysvětlím, proč je jen pro danou linku.', 'Z výstupu dig poznám, zda jméno má záznam A, AAAA, nebo neexistuje (NXDOMAIN).'],
        ],
        'competencies' => [['id' => 'net_dns_dhcp', 'level' => 2], ['id' => 'net_addressing', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Napíše na tabuli dlouhou adresu IPv6 a zeptá se, jak ji zkrátit.', 'student' => 'Zkusí zkrácení a porovnají výsledky.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'IPv6 bez paniky', 'teacher' => 'Vysvětlí pravidla zkracování a prefix /64.', 'student' => 'Zkrátí 4 adresy a určí síťovou část.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 38, 'phase' => 'Adresa linky v simulátoru', 'teacher' => 'Ukáže ip -6 addr v Linux Labu.', 'student' => 'Najdou adresu fe80:: a její rozsah (scope link).', 'form' => 'jednotlivě'],
            ['from' => 38, 'to' => 55, 'phase' => 'Záznamy A / AAAA / CNAME', 'teacher' => 'Vysvětlí typy záznamů a alias.', 'student' => 'Přiřadí záznamy k účelu a navrhnou alias www.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 78, 'phase' => 'Ověření příkazem dig', 'teacher' => 'Předvede čtení odpovědi dig (status, ANSWER).', 'student' => 'Ověří tři jména v simulátoru a zapíší výsledky.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Incident dual-stack a exit ticket', 'teacher' => 'Popíše incident: AAAA míří na nedostupnou adresu.', 'student' => 'Diskutují dopad a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Zkrať adresy IPv6 2001:0db8:0000:0000:0000:0000:0000:0001 a 2001:0db8:0a00:0000:0000:00ff:0000:0010 a u obou urči prefix /64.', 'output' => 'Zkrácené tvary 2001:db8::1 a 2001:db8:a00::ff:0:10 a jejich prefix 2001:db8::/64 a 2001:db8:a00::/64.', 'time' => '15 min'],
            ['text' => 'V Linux Labu spusť ip -6 addr a najdi adresu, která platí jen pro danou linku (link-local).', 'output' => 'Adresa fe80::5054:ff:fe3a:7c17/64 se „scope link“.', 'time' => '13 min',
                'sim' => [['cmd' => 'ip -6 addr', 'expect' => 'inet6 fe80::5054:ff:fe3a:7c17/64 scope link']]],
            ['text' => 'Ověř příkazem dig jméno intranet.skola.test: jaký má záznam A a má i záznam AAAA?', 'output' => 'A = 10.0.0.10 (TTL 3600), AAAA chybí (ANSWER: 0).', 'time' => '15 min',
                'sim' => [['cmd' => 'dig +short intranet.skola.test', 'expect' => '10.0.0.10'], ['cmd' => 'dig intranet.skola.test AAAA', 'expect' => 'ANSWER: 0']]],
            ['text' => 'Ověř neexistující jméno www.skola.lab a navrhni alias (CNAME) www.skola.test → intranet.skola.test.', 'output' => 'status: NXDOMAIN + zápis aliasu bez duplikace IP.', 'time' => '12 min',
                'sim' => [['cmd' => 'dig www.skola.lab', 'expect' => 'status: NXDOMAIN']]],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičku pravidel zkracování (vynechat úvodní nuly, jedna skupina nul = ::) a vzorový výstup dig s vyznačenými řádky.',
            'standard' => 'Zkracování, ověření v simulátoru a návrh aliasu podle zadání.',
            'challenge' => 'Vysvětlí, proč se :: smí v adrese použít jen jednou, a zapíše adresu s dvěma skupinami nul správně.',
        ],
        'assessment' => [
            'formative' => ['Tabule: tři žáci zkrátí adresu, třída hlasuje, zda je správně.', 'Kontrola výstupu: každý ukáže v dig řádek status a ANSWER.'],
            'rubric' => [
                ['criterion' => 'Zápis IPv6', 'levels' => ['Nezkrátí.', 'Zkrátí s chybou (dvakrát ::).', 'Správně zkrátí a určí /64.', 'Vysvětlí pravidla spolužákovi.']],
                ['criterion' => 'Záznamy DNS', 'levels' => ['Nerozliší typy.', 'Zná A, plete AAAA/CNAME.', 'Přiřadí A, AAAA, CNAME k účelu.', 'Navrhne alias bez duplikace IP.']],
                ['criterion' => 'Ověření v simulátoru', 'levels' => ['Nespustí.', 'Spustí, nevyčte výsledek.', 'Vyčte A, AAAA i NXDOMAIN.', 'Odhadne dopad chybějícího AAAA.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_dns_dhcp', 'competence_label' => 'DNS a DHCP', 'variants' => [
            ['question' => 'Jaký je správný zkrácený zápis adresy 2001:0db8:0000:0000:0000:0000:0000:0001?', 'options' => ['2001:db8::1', '2001:db8:0:1', '2001::db8::1'], 'correct' => 0, 'explanation' => 'Úvodní nuly se vynechají a jedna souvislá řada nul je ::.'],
            ['question' => 'K čemu slouží záznam AAAA?', 'options' => ['Přesměruje jméno na jiné jméno.', 'Přiřadí jménu adresu IPv6.', 'Určí poštovní server domény.'], 'correct' => 1, 'explanation' => 'A = IPv4, AAAA = IPv6, CNAME = alias.'],
            ['question' => 'dig vrátí „status: NXDOMAIN“. Co to znamená?', 'options' => ['Server DNS neběží.', 'Jméno existuje, ale nemá adresu IPv6.', 'Dotazované jméno v DNS neexistuje.'], 'correct' => 2, 'explanation' => 'NXDOMAIN = neexistující doména (jméno).'],
        ]],
        'homework' => [['text' => 'Volitelné: na domácím počítači nebo telefonu najdi v nastavení sítě adresu začínající fe80:: a opiš si ji.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Sítě a DNS zkoušíme jen v simulátoru Linux Labu; skutečné servery školy neměníme.', 'Příklady adres jsou dokumentační (2001:db8::/32, RFC 3849) nebo privátní.'],
        'teacher_notes' => ['Častý omyl: dvě zkrácení :: v jedné adrese – adresa pak není jednoznačná.', 'Otázka do třídy: Proč adresa fe80:: nepomůže na internetu?', 'Tempo: dig ukaž nejdřív s +short, plný výstup až potom – jinak se žáci ztratí.'],
        'substitution' => ['Zástup bez odborníka: žáci pracují v Linux Labu podle úkolů 2–4 (výstupy jsou v zadání), úkol 1 na papír.', 'Plán B offline: zkracování adres a čtení vytištěného výstupu dig.'],
        'glossary' => ['link-local', 'dual-stack', 'nxdomain'],
    ],
    6 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 6 · Monitoring služeb a rezervace DHCP',
        'goal' => [
            'student' => 'Na konci hodiny umím rozlišit „počítač odpovídá“ od „služba funguje“, navrhnout kontrolu dostupnosti webu a stabilní adresu pro známé zařízení přes rezervaci DHCP.',
            'success_criteria' => ['Vysvětlím rozdíl kontroly ping, TCP a HTTP a vyberu správnou pro web.', 'Navrhnu varování a kritický stav tak, aby jeden náhodný výpadek nevyvolal poplach.', 'Navrhnu rezervaci DHCP (MAC → IP) a vysvětlím rozdíl proti ručně nastavené adrese.'],
        ],
        'competencies' => [['id' => 'net_dns_dhcp', 'level' => 2], ['id' => 'net_security', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše situaci: ping na server jde, ale web nefunguje.', 'student' => 'Hádají, kde může být problém.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Počítač ≠ služba', 'teacher' => 'Porovná kontrolu ping, TCP a HTTP.', 'student' => 'V simulátoru zastaví web a ověří, co která kontrola uvidí.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Signál bez šumu', 'teacher' => 'Vysvětlí interval, timeout a práh.', 'student' => 'Navrhnou 3 metriky a hranice varování a kritického stavu.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Rezervace DHCP', 'teacher' => 'Vysvětlí zápůjčku adresy (lease) a rezervaci podle MAC.', 'student' => 'V simulátoru najdou MAC, adresu a dobu zápůjčky.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Tabulka zápůjček', 'teacher' => 'Rozdá vymyšlenou tabulku zápůjček s konfliktem.', 'student' => 'Najdou zařízení podle MAC, odhalí konflikt a navrhnou rezervaci tiskárny.', 'form' => 've dvojicích'],
            ['from' => 78, 'to' => 90, 'phase' => 'Shrnutí a exit ticket', 'teacher' => 'Shrne: měřit službu, ne jen počítač.', 'student' => 'Navrhnou kontrolu pro DNS a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V Linux Labu zastav web (sudo systemctl stop nginx) a ověř curl -I http://localhost; porovnej s tím, co by ukázal ping.', 'output' => 'curl hlásí „Couldn\'t connect to server“, počítač přitom běží – ping by byl v pořádku.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; curl -I http://localhost', 'expect' => "Couldn't connect to server"]]],
            ['text' => 'Navrhni tři metriky monitoringu webu (dostupnost HTTP, doba odpovědi, chybovost) s hranicí varování a kritického stavu a pravidlem „3 neúspěchy po sobě“.', 'output' => 'Tabulka metrik a hranic.', 'time' => '15 min'],
            ['text' => 'Pomocí ip a zjisti MAC adresu, IPv4 adresu a zbývající dobu zápůjčky (valid_lft) rozhraní eth0.', 'output' => 'MAC 52:54:00:3a:7c:17, adresa 10.0.0.23/24 „dynamic“, valid_lft v sekundách.', 'time' => '12 min',
                'sim' => [['cmd' => 'ip a', 'expect' => 'inet 10.0.0.23/24 brd 10.0.0.255 scope global dynamic eth0'], ['cmd' => 'ip a', 'expect' => 'link/ether 52:54:00:3a:7c:17']]],
            ['text' => 'V tabulce zápůjček najdi konflikt adres a navrhni rezervaci pro tiskárnu mimo dynamický rozsah.', 'output' => 'Zápis rezervace MAC → IP + zdůvodnění.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku „kontrola → co ověří → co neověří“ s polovinou vyplněnou.',
            'standard' => 'Monitoring, výstupy simulátoru a rezervace podle zadání.',
            'challenge' => 'Navrhne kontrolu DNS, která pozná, že jméno vrací špatnou adresu (ne jen žádnou).',
        ],
        'assessment' => [
            'formative' => ['Rychlé hlasování: ping OK + HTTP 503 – kde je problém?', 'Dvojice si vymění hranice metrik a hledají „poplach z jednoho výpadku“.'],
            'rubric' => [
                ['criterion' => 'Volba kontroly', 'levels' => ['Jen ping.', 'TCP i HTTP bez rozdílu.', 'Správná kontrola pro web se zdůvodněním.', 'Navrhne i kontrolu DNS.']],
                ['criterion' => 'Hranice a šum', 'levels' => ['Bez hranic.', 'Hranice bez ochrany proti šumu.', 'Varování, kritický stav a opakování.', 'Zdůvodní interval a timeout.']],
                ['criterion' => 'Rezervace DHCP', 'levels' => ['Nerozumí.', 'Ruční adresa uvnitř rozsahu.', 'Rezervace MAC → IP mimo dynamický rozsah.', 'Vysvětlí výhodu proti ruční adrese.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_dns_dhcp', 'competence_label' => 'DNS a DHCP', 'variants' => [
            ['question' => 'Ping na webový server odpovídá, ale kontrola HTTP hlásí chybu. Co je nejlepší první závěr?', 'options' => ['Server je vypnutý.', 'Síť k serveru funguje, problém je ve webové službě.', 'Selhal DNS.'], 'correct' => 1, 'explanation' => 'Ping ověřuje počítač, ne službu.'],
            ['question' => 'Jaký je hlavní rozdíl rezervace DHCP proti ručně nastavené adrese na zařízení?', 'options' => ['Adresu přiděluje centrálně server DHCP podle MAC, správa je na jednom místě.', 'Rezervace funguje jen pro IPv6.', 'Ruční adresa se mění při každém restartu.'], 'correct' => 0, 'explanation' => 'Rezervace = stabilní adresa, centrální správa.'],
            ['question' => 'Proč monitoring hlásí poplach až po třech neúspěšných kontrolách za sebou?', 'options' => ['Aby se šetřila elektřina.', 'Protože první dvě kontroly se nepočítají.', 'Aby jeden náhodný výpadek nespustil falešný poplach.'], 'correct' => 2, 'explanation' => 'Opakování odfiltruje šum.'],
        ]],
        'homework' => [['text' => 'Volitelné: v nastavení domácího routeru (jen prohlížení, nic neměň) najdi seznam připojených zařízení a jejich MAC adres.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Služby zastavujeme a měníme jen v simulátoru, ne na školní síti.', 'Na domácím routeru nic neměň – jen se dívej, a jen se souhlasem rodičů.'],
        'teacher_notes' => ['Častý omyl: „ping jde = všechno jde“. Ukaž to na zastaveném nginx.', 'Otázka do třídy: Co přesně ověřuje ping a co ne?', 'Tempo: tabulka zápůjček je papírová – připrav ji vytištěnou, ušetří to 5 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 3 v Linux Labu podle zadání (výstupy jsou uvedené), úkoly 2 a 4 na papír.', 'Plán B offline: vytištěné výstupy ip a a tabulka zápůjček.'],
        'glossary' => ['monitoring', 'lease', 'mac'],
    ],
    7 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 7 · Cesta paketu a čtení zachyceného provozu',
        'goal' => [
            'student' => 'Na konci hodiny umím z důkazů o provozu rozlišit závadu v síti, v DNS, na úrovni TCP a v aplikaci a navrhnout nejkratší další test.',
            'success_criteria' => ['Rozliším „spojení odmítnuto“ (RST) od „vypršel čas“ (žádná odpověď).', 'Napíšu zobrazovací filtr pro DNS a pro TCP port 443.', 'Ke zjištěné závadě navrhnu test, který ji po opravě ověří.'],
        ],
        'competencies' => [['id' => 'net_diagnose', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže záznam provozu se stovkami paketů.', 'student' => 'Řeknou, proč hledat bez otázky nejde.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Cesta paketu', 'teacher' => 'Předvede Packet Journey Lab (ARP → DNS → TCP → HTTP).', 'student' => 'Zapíší hypotézu k ukázkové závadě.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Filtr podle otázky', 'teacher' => 'Ukáže filtry dns a tcp.port == 443 (Wireshark).', 'student' => 'Navrhnou filtry pro tři otázky.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 58, 'phase' => 'Odmítnuto vs. vypršel čas', 'teacher' => 'Vysvětlí SYN, SYN-ACK a RST.', 'student' => 'V simulátoru vyzkouší nc na tři cíle a zapíší rozdíl.', 'form' => 'jednotlivě'],
            ['from' => 58, 'to' => 78, 'phase' => 'Incident podle důkazů', 'teacher' => 'Zadá incident „web nejde“ s připravenými důkazy.', 'student' => 'Vyberou 3 nejkratší testy a určí příčinu.', 'form' => 've dvojicích'],
            ['from' => 78, 'to' => 90, 'phase' => 'Ověření a exit ticket', 'teacher' => 'Zeptá se: jak poznáme, že oprava zabrala?', 'student' => 'Navrhnou ověřovací test a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'K ukázkové závadě zapiš hypotézu ve tvaru „myslím, že selhává … protože …“ a důkaz, který ji ověří.', 'output' => 'Hypotéza + potřebný důkaz.', 'time' => '12 min'],
            ['text' => 'Napiš zobrazovací filtr (display filter) Wireshark pro DNS (dns) a pro HTTPS (tcp.port == 443) a vysvětli, proč nechceš celý záznam.', 'output' => 'Dva filtry + jedna věta zdůvodnění.', 'time' => '15 min'],
            ['text' => 'V Linux Labu vyzkoušej nc -zv na 10.0.0.10 port 80, 10.0.0.10 port 443 a 10.0.0.99 port 443 a zapiš, co znamená každý výsledek.', 'output' => 'succeeded = port otevřen; Connection refused = cíl odpověděl RST; Connection timed out = žádná odpověď.', 'time' => '18 min',
                'sim' => [['cmd' => 'nc -zv 10.0.0.10 80', 'expect' => 'succeeded'], ['cmd' => 'nc -zv 10.0.0.10 443', 'expect' => 'Connection refused'], ['cmd' => 'nc -zv 10.0.0.99 443', 'expect' => 'Connection timed out']]],
            ['text' => 'V incidentu „web nejde“ vyber tři nejkratší testy, urči vrstvu závady a navrhni ověření po opravě.', 'output' => 'Tři testy, vrstva závady, ověřovací test.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku „výsledek → význam“ (succeeded / refused / timed out) a vzorový filtr.',
            'standard' => 'Hypotéza, filtry, testy v simulátoru a incident podle zadání.',
            'challenge' => 'Popíše, jak by v záznamu provozu vypadal DNS dotaz bez odpovědi a jak NXDOMAIN.',
        ],
        'assessment' => [
            'formative' => ['Karty: učitel přečte výsledek nc, třída zvedne kartu „síť / port / služba“.', 'Hypotéza nahlas: dá se ověřit jedním testem?'],
            'rubric' => [
                ['criterion' => 'Hypotéza a důkaz', 'levels' => ['Bez hypotézy.', 'Hypotéza bez důkazu.', 'Hypotéza + konkrétní důkaz.', 'Vyloučí i alternativní hypotézu.']],
                ['criterion' => 'Čtení TCP', 'levels' => ['Nerozliší výsledky.', 'Rozliší jen úspěch.', 'Rozliší RST a vypršení času.', 'Vysvětlí, kdo poslal RST a proč.']],
                ['criterion' => 'Postup v incidentu', 'levels' => ['Zkouší všechno.', 'Testy bez pořadí.', 'Tři nejkratší testy a vrstva závady.', 'Navrhne ověření po opravě.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_diagnose', 'competence_label' => 'Diagnostika sítě', 'variants' => [
            ['question' => 'nc hlásí „Connection timed out“. Co to nejspíš znamená?', 'options' => ['Port je otevřený.', 'Na pokus o spojení nepřišla žádná odpověď (cíl nedostupný nebo provoz zahazuje filtr).', 'Cíl spojení aktivně odmítl.'], 'correct' => 1, 'explanation' => 'Vypršení času = ticho, odmítnutí = RST.'],
            ['question' => 'Který filtr ve Wiresharku ukáže jen provoz na portu 443?', 'options' => ['tcp.port == 443', 'dns', 'arp'], 'correct' => 0, 'explanation' => 'Filtr vychází z otázky: zajímá mě HTTPS.'],
            ['question' => 'Proč začínáme diagnostiku hypotézou, a ne spuštěním všech nástrojů?', 'options' => ['Protože nástroje jsou placené.', 'Protože hypotéza je povinná podle zákona.', 'Hypotéza určí, jaký důkaz hledat, a zkrátí cestu k příčině.'], 'correct' => 2, 'explanation' => 'Bez otázky se v datech utopíš.'],
        ]],
        'homework' => [['text' => 'Volitelné: přečti v dokumentaci Wireshark (sekce Display Filters) tři příklady filtrů a jeden si zapiš s vysvětlením.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Zachytávat provoz smíme jen v izolovaném labu nebo na připravených záznamech – nikdy cizí provoz.', 'Záznamy provozu mohou obsahovat citlivé údaje; nesdílej je mimo třídu.'],
        'teacher_notes' => ['Používej předpřipravené záznamy nebo izolovaný lab; nezachytávej provoz třetích osob.', 'Otázka do třídy: Kdo poslal RST – klient, nebo server?', 'Tempo: simulátor je rychlý, incident nech na dvojice s časovačem.'],
        'substitution' => ['Zástup bez odborníka: úkol 3 v Linux Labu (výsledky jsou v zadání), ostatní úkoly na papír.', 'Plán B offline: vytištěný záznam provozu a karty s výsledky nc.'],
        'glossary' => ['rst', 'display-filter'],
    ],
    8 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 8 · IPv4 II: dělení sítě (VLSM) a síťové zóny',
        'goal' => [
            'student' => 'Na konci hodiny umím rozdělit síť 192.168.10.0/24 na podsítě různé velikosti a určit, jaká komunikace mezi zónami je opravdu potřeba.',
            'success_criteria' => ['Rozdělím /24 na podsítě pro 100, 50, 20 a 2 zařízení bez překryvu.', 'Ke každé podsíti zapíšu bránu, rozsah a rezervu.', 'Pro zóny USERS, SERVERS, MGMT navrhnu pravidla „povolit jen nutné“ a ověřím je pozitivním i negativním testem.'],
        ],
        'competencies' => [['id' => 'net_addressing', 'level' => 2], ['id' => 'net_security', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže síť, kde jsou všichni v jedné velké podsíti.', 'student' => 'Vyjmenují rizika.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 30, 'phase' => 'VLSM plánovač', 'teacher' => 'Předvede VLSM Planner Lab: od největší podsítě.', 'student' => 'Rozdělí 192.168.10.0/24 pro 4 segmenty.', 'form' => 'jednotlivě'],
            ['from' => 30, 'to' => 42, 'phase' => 'Adresní plán', 'teacher' => 'Ukáže tabulku plánu s bránou a rezervou.', 'student' => 'Zapíší plán a zkontrolují hranice.', 'form' => 've dvojicích'],
            ['from' => 42, 'to' => 57, 'phase' => 'Zóny důvěry', 'teacher' => 'Předvede Security Zones Lab.', 'student' => 'Definují zóny a potřebné služby.', 'form' => 'jednotlivě'],
            ['from' => 57, 'to' => 78, 'phase' => 'Pravidla a ověření', 'teacher' => 'Zdůrazní test povoleného i zakázaného scénáře.', 'student' => 'Napíšou pravidla a pozitivní i negativní testy.', 'form' => 've dvojicích'],
            ['from' => 78, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Namátkou zkontroluje dva plány.', 'student' => 'Opraví překryv, pokud je, a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Rozděl 192.168.10.0/24 pro segmenty USERS (100 zařízení), SERVERS (50), MGMT (20) a propoj (2) – začni od největšího.', 'output' => 'USERS 192.168.10.0/25, SERVERS 192.168.10.128/26, MGMT 192.168.10.192/27, propoj 192.168.10.224/30, rezerva od .228.', 'time' => '20 min'],
            ['text' => 'Ke každé podsíti zapiš první použitelnou adresu jako bránu (gateway), poslední použitelnou adresu a počet použitelných adres.', 'output' => 'Tabulka: /25 = 126, /26 = 62, /27 = 30, /30 = 2 použitelné adresy; brány .1, .129, .193, .225.', 'time' => '12 min'],
            ['text' => 'Pro zóny USERS, SERVERS a MGMT napiš pravidla (ACL): USERS → WEB 443 povolit, MGMT → SERVERS 22 povolit, ostatní provoz na servery zakázat.', 'output' => 'Tabulka pravidel (zdroj, cíl, port, akce).', 'time' => '15 min'],
            ['text' => 'Ke každému pravidlu napiš pozitivní a negativní test (co musí projít a co nesmí).', 'output' => 'Šest testů s očekávaným výsledkem.', 'time' => '21 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku velikostí podsítí (/24 až /30 s počty adres) a předvyplněný první řádek plánu.',
            'standard' => 'Úplný plán, pravidla a testy podle zadání.',
            'challenge' => 'Doplní pátý segment (Wi-Fi hosté, 25 zařízení) do rezervy a ověří, že se nepřekrývá.',
        ],
        'assessment' => [
            'formative' => ['Kontrola hranic: dvojice si vymění plány a hledají překryv.', 'Otázka: proč testujeme i to, co má být zakázané?'],
            'rubric' => [
                ['criterion' => 'Dělení sítě', 'levels' => ['Překryvy.', 'Bez překryvu, plýtvání adresami.', 'Správné podsítě od největší.', 'Doplní další segment do rezervy.']],
                ['criterion' => 'Adresní plán', 'levels' => ['Chybí brány.', 'Chyby v rozsazích.', 'Brány, rozsahy a počty správně.', 'Plán zdůvodní a je čitelný pro kolegu.']],
                ['criterion' => 'Pravidla a ověření', 'levels' => ['Povolit vše.', 'Jen pozitivní testy.', 'Minimální pravidla + pozitivní i negativní testy.', 'Zapíše i důkazy testů.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_addressing', 'competence_label' => 'Adresace sítí', 'variants' => [
            ['question' => 'Kolik použitelných adres zařízení má podsíť /26?', 'options' => ['64', '30', '62'], 'correct' => 2, 'explanation' => '2^6 = 64 adres, minus adresa sítě a všesměrová = 62.'],
            ['question' => 'Proč se při VLSM přiděluje nejdřív největší podsíť?', 'options' => ['Aby se podsítě nepřekrývaly a nevznikaly nevyužitelné mezery.', 'Protože je nejdůležitější.', 'Kvůli rychlosti routeru.'], 'correct' => 0, 'explanation' => 'Velké bloky musí začínat na své hranici.'],
            ['question' => 'Co ověřuje negativní test pravidla firewallu?', 'options' => ['Že povolená služba funguje.', 'Že zakázaný provoz opravdu neprojde.', 'Že firewall je zapnutý.'], 'correct' => 1, 'explanation' => 'Bez negativního testu nevíš, zda pravidlo něco blokuje.'],
        ]],
        'homework' => [['text' => 'Volitelné: rozděl 10.20.0.0/24 pro segmenty 60, 28 a 10 zařízení a zapiš brány.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Pravidla navrhujeme a ověřujeme jen v labu; školní síť ani firewall neměníme.', 'Adresy jsou privátní (RFC 1918) – v praxi je přiděluje správce sítě.'],
        'teacher_notes' => ['Častý omyl: podsíť /25 začínající na .100. Ukaž hranice bloků na číselné ose.', 'Otázka do třídy: Kdo potřebuje mluvit s kým – a proč?', 'Tempo: VLSM zabere slabším žákům víc – pravidla mohou dostat s předvyplněnými zónami.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 na papír, výsledky úkolů 1–2 jsou v zadání ke kontrole.', 'Plán B offline: celé dělení na číselné ose 0–255 na papíře.'],
        'glossary' => ['vlsm', 'gateway', 'acl'],
    ],
    9 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 9 · DNS a DHCP II: čas v síti a řetěz diagnostiky služby',
        'goal' => [
            'student' => 'Na konci hodiny umím vysvětlit, proč se změna DNS neprojeví hned (TTL) a jak se obnovuje zápůjčka DHCP, a projít řetěz DNS → TCP → TLS → HTTP při diagnostice webu.',
            'success_criteria' => ['Z výstupu dig vyčtu TTL a odhadnu, jak dlouho může klient držet starou odpověď.', 'Naplánuji změnu DNS: snížit TTL předem, ověřit, vrátit TTL.', 'U incidentu najdu první vrstvu řetězu, ve které chybí důkaz.'],
        ],
        'competencies' => [['id' => 'net_diagnose', 'level' => 3], ['id' => 'net_dns_dhcp', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše: změnili jsme adresu webu a polovina třídy pořád vidí starý server.', 'student' => 'Odhadnou proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'TTL a cache', 'teacher' => 'Předvede TTL & Lease Timeline Lab.', 'student' => 'V simulátoru vyčtou TTL záznamu a spočítají, kdy cache vyprší.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 38, 'phase' => 'Životní cyklus zápůjčky', 'teacher' => 'Vysvětlí obnovu zápůjčky (lease): renew a rebind.', 'student' => 'Popíšou, co se stane při změně rozsahu DHCP.', 'form' => 've dvojicích'],
            ['from' => 38, 'to' => 50, 'phase' => 'Řízená změna', 'teacher' => 'Ukáže plán změny DNS s oknem ověření.', 'student' => 'Napíšou plán změny ve třech krocích.', 'form' => 'jednotlivě'],
            ['from' => 50, 'to' => 78, 'phase' => 'Řetěz diagnostiky', 'teacher' => 'Předvede Service Chain Lab a zadá cvičný incident.', 'student' => 'Ověří DNS, TCP a HTTP v simulátoru a najdou první chybějící důkaz.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Oprava a exit ticket', 'teacher' => 'Zeptá se na opakovaný test po opravě.', 'student' => 'Navrhnou opravu a ověření a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Spusť dig intranet.skola.test a vyčti z řádku odpovědi TTL; spočítej, jak dlouho může klient držet starou adresu po změně.', 'output' => 'TTL 3600 s = až 1 hodina.', 'time' => '12 min',
                'sim' => [['cmd' => 'dig intranet.skola.test', 'expect' => '3600']]],
            ['text' => 'Napiš plán změny adresy webu: kdy snížit TTL, jak dlouho počkat, jak ověřit a kdy TTL vrátit.', 'output' => 'Plán změny ve třech krocích s časy.', 'time' => '15 min'],
            ['text' => 'V Linux Labu projdi řetěz pro intranet.skola.test: DNS (dig +short), TCP (nc -zv … 80) a HTTP (curl -I) a zapiš výsledek každé vrstvy.', 'output' => 'DNS → 10.0.0.10, TCP 80 → succeeded, HTTP → 200 OK.', 'time' => '20 min',
                'sim' => [['cmd' => 'dig +short intranet.skola.test', 'expect' => '10.0.0.10'], ['cmd' => 'nc -zv 10.0.0.10 80', 'expect' => 'succeeded'], ['cmd' => 'curl -I http://intranet.skola.test', 'expect' => 'HTTP/1.1 200 OK']]],
            ['text' => 'V cvičném incidentu (DNS i TCP 443 v pořádku, certifikát prošlý) urči vrstvu závady, navrhni opravu a opakovaný test.', 'output' => 'Vrstva TLS, oprava (obnova certifikátu) a test.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu řetězu DNS → TCP → TLS → HTTP s příkazem u každé vrstvy.',
            'standard' => 'TTL, plán změny, řetěz v simulátoru a incident podle zadání.',
            'challenge' => 'Navrhne monitorovací kontrolu, která upozorní na certifikát 14 dní před vypršením.',
        ],
        'assessment' => [
            'formative' => ['Kartičky vrstev: učitel popíše symptom, třída ukáže vrstvu.', 'Plán změny: kde je v plánu krok „vrátit TTL“?'],
            'rubric' => [
                ['criterion' => 'Čas v síti', 'levels' => ['Nezná TTL.', 'Zná TTL, nevyčte ho.', 'Vyčte TTL a odhadne dopad.', 'Vysvětlí i obnovu zápůjčky DHCP.']],
                ['criterion' => 'Plán změny', 'levels' => ['Bez plánu.', 'Změna bez ověření.', 'Snížit TTL → ověřit → vrátit.', 'Plán obsahuje i postup návratu.']],
                ['criterion' => 'Řetěz diagnostiky', 'levels' => ['Náhodné testy.', 'Testy bez pořadí.', 'Projde vrstvy a najde první chybějící důkaz.', 'Navrhne monitoring dané vrstvy.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_diagnose', 'competence_label' => 'Diagnostika sítě', 'variants' => [
            ['question' => 'Záznam DNS má TTL 3600. Za jak dlouho nejpozději uvidí klient s uloženou odpovědí novou adresu?', 'options' => ['Okamžitě.', 'Nejpozději zhruba za hodinu, až mu vyprší uložená odpověď.', 'Za 3600 dní.'], 'correct' => 1, 'explanation' => 'TTL je v sekundách: 3600 s = 1 h.'],
            ['question' => 'Proč se TTL snižuje už několik hodin před plánovanou změnou adresy?', 'options' => ['Aby si klienti starou odpověď nepamatovali dlouho a změna se projevila rychle.', 'Aby DNS server méně pracoval.', 'Protože nízké TTL zrychlí web.'], 'correct' => 0, 'explanation' => 'Krátké TTL zkrátí přechodné období.'],
            ['question' => 'dig vrací správnou adresu, port 443 je otevřený, ale prohlížeč hlásí neplatný certifikát. Kde hledat?', 'options' => ['V DHCP.', 'V záznamu A.', 'Ve vrstvě TLS (certifikát).'], 'correct' => 2, 'explanation' => 'První chybějící důkaz je v TLS.'],
        ]],
        'homework' => [['text' => 'Volitelné: v prohlížeči klikni na zámek u adresy libovolného webu a zapiš, do kdy platí jeho certifikát.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Změny DNS a DHCP jen v labu; skutečné záznamy školy nikdy neměníme.', 'Při prohlížení certifikátů se nic nestahuje ani neinstaluje.'],
        'teacher_notes' => ['Častý omyl: „změnil jsem DNS, tak to hned platí“. Ukaž TTL ve výstupu.', 'Otázka do třídy: Která vrstva řetězu je první, kde chybí důkaz?', 'Tempo: řetěz v simulátoru je jádro – lab TTL lze zkrátit na 10 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 3 v Linux Labu (výstupy v zadání), úkoly 2 a 4 na papír.', 'Plán B offline: časová osa TTL na papíře a karty vrstev řetězu.'],
        'glossary' => ['ttl', 'lease', 'tls'],
    ],
    10 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 10 · Příkazová řádka Linuxu: orientace bez klikání',
        'goal' => [
            'student' => 'Na konci hodiny umím se v Linuxu (v shellu) orientovat příkazy pwd, ls, cd, vytvářet, kopírovat a přesouvat soubory a vysvětlit rozdíl absolutní a relativní cesty (path).',
            'success_criteria' => ['Vysvětlím účel adresářů /home, /etc, /var, /tmp a /usr.', 'Použiji absolutní i relativní cestu a vím, kde právě jsem (pwd).', 'Před mazáním ověřím umístění a argument příkazu.'],
        ],
        'competencies' => [['id' => 'lnx_navigation', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže strom adresářů a zeptá se, kde se ukládá nastavení.', 'student' => 'Tipují adresář.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Mapa souborového systému', 'teacher' => 'Vysvětlí /home, /etc, /var, /tmp, /usr.', 'student' => 'Ke každému adresáři zapíší, co obsahuje.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 40, 'phase' => 'Navigace', 'teacher' => 'Předvede pwd, ls -la, cd s absolutní a relativní cestou.', 'student' => 'V Linux Labu projdou /var/log a vrátí se domů.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 60, 'phase' => 'Práce se soubory', 'teacher' => 'Ukáže mkdir, touch, cp, mv v testovacím adresáři.', 'student' => 'Vytvoří adresář lab a zkopírují a přejmenují soubor.', 'form' => 'jednotlivě'],
            ['from' => 60, 'to' => 78, 'phase' => 'Nejdřív zkoumat, pak měnit', 'teacher' => 'Ukáže file a head před úpravou souboru.', 'student' => 'Zjistí typ souboru a první řádky /etc/passwd.', 'form' => 've dvojicích'],
            ['from' => 78, 'to' => 90, 'phase' => 'Pravidlo před rm a exit ticket', 'teacher' => 'Zformuluje se třídou bezpečnostní pravidlo.', 'student' => 'Zapíší pravidlo a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Spusť pwd hned po přihlášení, pak cd /var/log a ls; zapiš, kde jsi a jaké soubory tam jsou.', 'output' => '/home/student; v /var/log jsou auth.log, dpkg.log, adresář nginx a syslog.', 'time' => '15 min',
                'sim' => [['cmd' => 'pwd', 'expect' => '/home/student'], ['cmd' => 'cd /var/log; ls', 'expect' => 'dpkg.log']]],
            ['text' => 'Ve svém domovském adresáři vytvoř adresář lab, v něm soubor test.txt, zkopíruj ho jako kopie.txt a přejmenuj na zaloha.txt.', 'output' => 'ls lab ukáže test.txt a zaloha.txt.', 'time' => '15 min',
                'sim' => [['cmd' => 'cd ~; mkdir lab; touch lab/test.txt; cp lab/test.txt lab/kopie.txt; mv lab/kopie.txt lab/zaloha.txt; ls lab', 'expect' => 'test.txt  zaloha.txt']]],
            ['text' => 'Napiš jednu absolutní a jednu relativní cestu k souboru lab/test.txt a vysvětli rozdíl.', 'output' => '/home/student/lab/test.txt vs. lab/test.txt (z domovského adresáře).', 'time' => '10 min'],
            ['text' => 'Než budeš cokoli měnit, zjisti typ souboru /etc/hosts (file) a první tři řádky /etc/passwd (head -n 3).', 'output' => 'file: „Unicode text, UTF-8 text“; první řádek root:x:0:0:root:/root:/bin/bash.', 'time' => '15 min',
                'sim' => [['cmd' => 'file /etc/hosts', 'expect' => 'UTF-8 text'], ['cmd' => 'head -n 3 /etc/passwd', 'expect' => 'root:x:0:0:root:/root:/bin/bash']]],
        ],
        'differentiation' => [
            'support' => 'Dostane tahák příkazů s jedním příkladem ke každému a pracuje podle číslovaných kroků.',
            'standard' => 'Úkoly 1–4 v Linux Labu podle zadání.',
            'challenge' => 'Použije find ~/lab -name "*.txt" a vysvětlí, proč je výpis jiný než ls.',
        ],
        'assessment' => [
            'formative' => ['„Kde jsem?“: učitel napíše sérii cd, třída určí výsledné pwd.', 'Ukázka příkazu: každý ukáže svůj výstup ls lab.'],
            'rubric' => [
                ['criterion' => 'Orientace', 'levels' => ['Neví, kde je.', 'Používá jen cd bez kontroly.', 'pwd, ls, cd s oběma typy cest.', 'Vysvětlí cestu spolužákovi.']],
                ['criterion' => 'Práce se soubory', 'levels' => ['Nezvládne.', 'S chybami v cestě.', 'mkdir, touch, cp, mv bez chyb.', 'Ověřuje výsledek po každém kroku.']],
                ['criterion' => 'Bezpečný postup', 'levels' => ['Mění naslepo.', 'Ověří jen někdy.', 'Před změnou zkoumá (file, head) a ověří cestu.', 'Formuluje pravidlo pro ostatní.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_navigation', 'competence_label' => 'Linux: orientace a soubory', 'variants' => [
            ['question' => 'Jsi v /home/student. Kam tě přenese příkaz cd ../..?', 'options' => ['Do /home', 'Do kořenového adresáře /', 'Do /home/student/..'], 'correct' => 1, 'explanation' => 'Dvakrát o úroveň výš: /home → /.'],
            ['question' => 'Ve kterém adresáři hledáš nastavení systému a služeb?', 'options' => ['/tmp', '/home', '/etc'], 'correct' => 2, 'explanation' => '/etc = konfigurace.'],
            ['question' => 'Co uděláš jako první, než spustíš rm na soubor?', 'options' => ['Ověřím pwd a přesnou cestu argumentu.', 'Spustím ho dvakrát pro jistotu.', 'Restartuji počítač.'], 'correct' => 0, 'explanation' => 'Chyba v cestě může smazat jiná data.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu vyřeš první úroveň balíčku Start a zapiš si příkazy, které jsi použil.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Pracujeme jen v simulátoru a v testovacím adresáři lab.', 'Destruktivní příkazy nikdy nad systémovými cestami – ani v simulátoru je nezkoušej „pro zábavu“.'],
        'teacher_notes' => ['Používej testovací adresář; nedávej destruktivní příklady nad skutečnými systémovými cestami.', 'Otázka do třídy: Jak poznám, kde právě jsem?', 'Tempo: barevný výpis ls může mást – ukaž, že modré jsou adresáře.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 v Linux Labu, očekávané výstupy jsou přímo v zadání.', 'Plán B offline: strom adresářů na papíře a „hra na cd“ s kartičkami.'],
        'glossary' => ['shell', 'path'],
    ],
]]];
