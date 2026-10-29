<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · obsahová stopa, doplnění L1–L4 – 3.A (operační systémy a sítě).
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla jako vlna 1 a 2 (lesson_content_v76_1a_a.php).
 * Vychází z témat původních lekcí (modules.php L1, next_lessons.php L2, extended_lessons.php L3–L4); texty jsou vlastní a česky.
 * Ověřované příkazy simulátoru (klíč `sim`): každý `cmd` běží v čistém světě Linux Labu (nginx běží), výstup musí obsahovat
 * `expect` – hlídá tools/v77_content_audit.php. Nic se nespouští doopravdy; adresy jen z laboratoře (10.0.0.x, intranet.skola.test)
 * a z privátních rozsahů (192.168.x.x). Kompetence: katalog v62 „os_site“. Úrovně: levels_v75_content_3a.php (L1–L4).
 * Vazba na ŠVP: svp_v72_map.php (návrh). AI prompty: ai_prompts_v76.php z tohoto obsahu (po schválení).
 */

return ['class_3a' => ['lessons' => [
    1 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 1 · Start: IP plán učebny a důkaz, že síť funguje',
        'goal' => [
            'student' => 'Na konci hodiny umím přečíst adresu, masku a výchozí bránu svého stroje, navrhnout IP plán malé učebny a každý krok ověřit výstupem příkazu.',
            'success_criteria' => ['Z výpisu ip a a ip r vyčtu adresu, prefix i bránu a zapíšu je do tabulky.', 'Můj IP plán má statické adresy pro server, tiskárnu a bránu a oddělený rozsah DHCP.', 'Ke každému tvrzení o síti přiložím výstup příkazu, který ho dokazuje.'],
        ],
        'competencies' => [['id' => 'net_addressing', 'level' => 2], ['id' => 'net_diagnose', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: co o síti víme', 'teacher' => 'Ukáže schéma učebny (20 stanic, tiskárna, server, router) a zeptá se, co musí mít každé zařízení nastavené.', 'student' => 'Ve dvojici sepíší čtyři údaje, bez kterých stanice do sítě nepůjde.', 'form' => 've dvojicích'],
            ['from' => 10, 'to' => 25, 'phase' => 'Vlastní stroj', 'teacher' => 'Předvede ip a a ip r v Linux Labu a ukáže, kde je adresa, prefix a výchozí brána.', 'student' => 'Zjistí údaje svého stroje a zapíší je do tabulky.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 50, 'phase' => 'IP plán učebny', 'teacher' => 'Zadá rozsah 192.168.50.0/24 a pravidlo: statické adresy na začátku, DHCP zbytek.', 'student' => 'Navrhnou tabulku zařízení, adres, masky, brány a DNS a vyznačí rozsah DHCP.', 'form' => 'jednotlivě'],
            ['from' => 50, 'to' => 70, 'phase' => 'Ověření', 'teacher' => 'Ukáže, že ping ověřuje dosažitelnost a nslookup překlad jména – každý dokazuje něco jiného.', 'student' => 'Ověří bránu a jméno intranetu a ke každému výstupu napíší, co dokazuje.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 82, 'phase' => 'Co když…', 'teacher' => 'Rozehraje dvě chyby: špatná maska a nedostupné DNS.', 'student' => 'Vysvětlí dopad obou chyb a ukáží výstup pro neexistující jméno.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: tvrzení o síti bez výstupu příkazu je jen odhad.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V Linux Labu zjisti příkazy ip a a ip r adresu, prefix a výchozí bránu (gateway) svého stroje a zapiš je do tabulky.', 'output' => 'Tabulka: adresa 10.0.0.23, prefix /24, brána 10.0.0.1, rozhraní eth0.', 'time' => '12 min',
                'sim' => [['cmd' => 'ip a', 'expect' => 'inet 10.0.0.23/24'], ['cmd' => 'ip r', 'expect' => 'default via 10.0.0.1']]],
            ['text' => 'Navrhni IP plán učebny v rozsahu 192.168.50.0/24: brána .1, server .10, tiskárna .20, rozsah DHCP pro 20 stanic a rezervu.', 'output' => 'Tabulka zařízení · adresa · maska · brána · DNS a rozsah DHCP .100–.150 bez kolize se statickými adresami.', 'time' => '25 min'],
            ['text' => 'Ověř dosažitelnost brány příkazem ping -c 2 10.0.0.1 a překlad jména intranet.skola.test příkazem nslookup; u každého výstupu napiš, co dokazuje.', 'output' => 'Dva výstupy s komentářem: brána odpovídá (0 % ztrát), jméno se přeloží na 10.0.0.10.', 'time' => '15 min',
                'sim' => [['cmd' => 'ping -c 2 10.0.0.1', 'expect' => '0% packet loss'], ['cmd' => 'nslookup intranet.skola.test', 'expect' => '10.0.0.10']]],
            ['text' => 'Vysvětli dvěma větami, co se stane při špatné masce a při nedostupném DNS, a ukaž výstup dotazu na jméno, které neexistuje.', 'output' => 'Dvě věty o dopadu chyb a výstup s odpovědí NXDOMAIN.', 'time' => '10 min',
                'sim' => [['cmd' => 'nslookup neexistuje.skola.test', 'expect' => 'NXDOMAIN']]],
        ],
        'differentiation' => [
            'support' => 'Dostane předvyplněnou tabulku IP plánu s vyznačenými řádky pro server, tiskárnu a bránu a doplňuje jen adresy.',
            'standard' => 'Údaje vlastního stroje, IP plán učebny, ověření brány a jména a vysvětlení dvou chyb.',
            'challenge' => 'Rozdělí rozsah na dvě podsítě (učebna a kabinet) a zdůvodní, proč zvolil/a právě takový prefix.',
        ],
        'assessment' => [
            'formative' => ['Ukaž prstem na výpisu ip r, kde je výchozí brána.', 'Kartičky „dokazuje / nedokazuje“: ping na bránu dokazuje funkční DNS?'],
            'rubric' => [
                ['criterion' => 'Čtení konfigurace', 'levels' => ['Nenajde adresu ani bránu.', 'Najde adresu, bránu ne.', 'Zapíše adresu, prefix i bránu.', 'Vysvětlí, co prefix /24 znamená pro počet adres.']],
                ['criterion' => 'IP plán', 'levels' => ['Adresy se opakují nebo jsou mimo rozsah.', 'Plán je úplný, DHCP koliduje se statickými.', 'Plán bez kolizí s rozsahem DHCP.', 'Plán s rezervou a zdůvodněním.']],
                ['criterion' => 'Důkaz', 'levels' => ['Bez výstupů.', 'Výstup bez vysvětlení.', 'Ke každému výstupu, co dokazuje.', 'Rozliší, co výstup nedokazuje.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_addressing', 'competence_label' => 'Sítě: adresa, maska a brána zařízení', 'variants' => [
            ['question' => 'Výpis ip r ukazuje „default via 10.0.0.1“. Co je 10.0.0.1?', 'options' => ['Adresa DNS serveru.', 'Výchozí brána, přes kterou jdou pakety do jiných sítí.', 'Adresa mého stroje.'], 'correct' => 1, 'explanation' => 'Default route ukazuje na bránu – první krok do jiných sítí.'],
            ['question' => 'Ping na bránu prošel bez ztrát. Co tím máš dokázané?', 'options' => ['Že je brána v mé síti dosažitelná.', 'Že funguje překlad jmen.', 'Že funguje i web na serveru.'], 'correct' => 0, 'explanation' => 'Ping dokazuje dosažitelnost, ne DNS ani běžící službu.'],
            ['question' => 'Proč v IP plánu oddělíš rozsah DHCP od statických adres?', 'options' => ['DHCP neumí přidělit adresy pod .100.', 'Kvůli rychlejšímu pingu.', 'Aby DHCP nepřidělil adresu, kterou už má server nebo tiskárna.'], 'correct' => 2, 'explanation' => 'Kolize adres způsobí, že jedno ze zařízení přestane fungovat.'],
        ]],
        'homework' => [['text' => 'Volitelné: doma zjisti adresu a bránu svého počítače nebo telefonu a porovnej je s laboratoří (adresu nikam nezveřejňuj).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Příkazy zkoušíme jen v simulátoru Linux Labu, ne na školní síti.', 'Do sešitu ani do sdílených souborů nepíšeme skutečné adresy domácí sítě.'],
        'teacher_notes' => ['Častý omyl: žáci zaměňují bránu a DNS server – nech je u každého říct, k čemu slouží.', 'Otázka do třídy: Co přesně dokazuje úspěšný ping a co už ne?', 'Tempo: IP plán je jádro hodiny; ověření zkrať, pokud plán zabere víc času.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 3 a 4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání), úkol 2 na papír.', 'Plán B offline: vytištěný výpis ip a / ip r a prázdná tabulka IP plánu.'],
        'glossary' => ['gateway', 'nxdomain'],
    ],
    2 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 2 · Kancelářská síť: VLAN, první hop a matice služeb',
        'goal' => [
            'student' => 'Na konci hodiny umím rozdělit kancelářskou síť do dvou VLAN, popsat cestu paketu přes bránu a sepsat matici služeb, kterou ověřím testem spojení.',
            'success_criteria' => ['Nakreslím cestu paketu z VLAN uživatelů na server ve VLAN serverů přes bránu.', 'Vysvětlím, proč klient pro vzdálený cíl hledá MAC adresu brány.', 'Matice služeb má zdroj, cíl, port a akci a žádné pravidlo „vše povolit“.'],
        ],
        'competencies' => [['id' => 'net_addressing', 'level' => 2], ['id' => 'net_security', 'level' => 2], ['id' => 'net_services', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: jedna síť pro všechny', 'teacher' => 'Popíše kancelář, kde host na Wi-Fi vidí účetní server, a zeptá se, co je špatně.', 'student' => 'Navrhnou, které skupiny zařízení oddělit.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'VLAN a adresy', 'teacher' => 'Ukáže dvě VLAN: uživatelé 192.168.10.0/24, servery 192.168.20.0/24, brána .1.', 'student' => 'Nakreslí topologii a cestu paketu PC → brána → server.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'První hop', 'teacher' => 'Vysvětlí, že pro cíl mimo vlastní síť hledá klient MAC adresu brány.', 'student' => 'V Linux Labu zjistí MAC adresu brány a ověří trasu k serveru.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 60, 'phase' => 'Matice služeb', 'teacher' => 'Ukáže tabulku zdroj → cíl → služba → akce a zakáže pravidlo „vše povolit“.', 'student' => 'Sepíší čtyři pravidla a ověří, které služby server opravdu nabízí.', 'form' => 've dvojicích'],
            ['from' => 60, 'to' => 80, 'phase' => 'Incident: web nejde', 'teacher' => 'Zadá hlášení: z VLAN uživatelů nejde web, správa přes SSH funguje.', 'student' => 'Najdou chybné pravidlo, opraví ho a ověří spojení na port 80.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne cestu paketu po vrstvách jednou větou.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Nakresli kancelářskou síť se dvěma VLAN (uživatelé 192.168.10.0/24, servery 192.168.20.0/24) a vyznač cestu paketu z PC na webový server přes bránu.', 'output' => 'Schéma se dvěma VLAN, branami .1 a šipkami cesty PC → brána → směrovač → server.', 'time' => '15 min'],
            ['text' => 'Zjisti příkazem ip neigh MAC adresu výchozí brány a vysvětli, proč ji klient potřebuje i pro cíl v jiné síti.', 'output' => 'MAC 00:1a:2b:3c:4d:01 a věta o prvním hopu.', 'time' => '12 min',
                'sim' => [['cmd' => 'ip neigh', 'expect' => '00:1a:2b:3c:4d:01'], ['cmd' => 'traceroute 10.0.0.10', 'expect' => 'intranet.skola.test']]],
            ['text' => 'Sepiš matici služeb (ACL) se čtyřmi pravidly zdroj → cíl → port → akce a ověř příkazem ss -tlnp, které porty server opravdu nabízí.', 'output' => 'Tabulka čtyř pravidel bez „vše povolit“ a poznámka, že server naslouchá na portech 22 a 80.', 'time' => '20 min',
                'sim' => [['cmd' => 'ss -tlnp', 'expect' => '0.0.0.0:80']]],
            ['text' => 'Incident: web z VLAN uživatelů nejde, SSH ze správy funguje. Najdi chybné pravidlo v matici, oprav ho a ověř spojení na port 80 příkazem nc -zv.', 'output' => 'Opravené pravidlo a výstup „succeeded“ pro port 80 serveru 10.0.0.10.', 'time' => '18 min',
                'sim' => [['cmd' => 'nc -zv 10.0.0.10 80', 'expect' => 'succeeded']]],
        ],
        'differentiation' => [
            'support' => 'Dostane předkreslenou topologii a tabulku matice se dvěma vyplněnými řádky; doplňuje zbytek.',
            'standard' => 'Schéma dvou VLAN, první hop, matice služeb a oprava incidentu s ověřením.',
            'challenge' => 'Doplní pravidlo pro VLAN hostů, kteří smí jen do internetu, a vysvětlí, kde v síti ho nastaví.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: PC z VLAN 10 posílá na 192.168.20.10 – čí MAC adresu hledá?', 'Kontrola souseda: najdi v matici pravidlo, které povoluje víc, než je potřeba.'],
            'rubric' => [
                ['criterion' => 'Topologie', 'levels' => ['Bez oddělení skupin.', 'Dvě VLAN bez brány.', 'Dvě VLAN, brány a cesta paketu.', 'Zdůvodní, proč oddělení zvyšuje bezpečnost.']],
                ['criterion' => 'První hop', 'levels' => ['Neví, co je MAC.', 'Najde MAC brány.', 'Vysvětlí, proč klient hledá MAC brány.', 'Rozliší lokální a vzdálený cíl na příkladu.']],
                ['criterion' => 'Matice služeb', 'levels' => ['Chybí.', 'Pravidla bez portů.', 'Čtyři úplná pravidla bez „vše povolit“.', 'Matici ověří testem spojení.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_security', 'competence_label' => 'Sítě: oddělení provozu a pravidla přístupu', 'variants' => [
            ['question' => 'PC ve VLAN uživatelů posílá data na server v jiné VLAN. Čí MAC adresu hledá v síti?', 'options' => ['MAC adresu serveru.', 'MAC adresu přepínače.', 'MAC adresu výchozí brány.'], 'correct' => 2, 'explanation' => 'Pro cíl v jiné síti jde rámec nejdřív na bránu.'],
            ['question' => 'Proč nepatří do matice služeb pravidlo „vše povolit“?', 'options' => ['Otevře i služby, které nikdo nepotřebuje, a zvětší plochu útoku.', 'Zpomalí síť na polovinu.', 'Přepínač ho neumí uložit.'], 'correct' => 0, 'explanation' => 'Povoluje se jen to, co má smysl – zbytek je zakázaný.'],
            ['question' => 'Čím nejlépe ověříš, že po opravě pravidla jde web z VLAN uživatelů?', 'options' => ['Pingem na bránu.', 'Testem TCP spojení na port 80 serveru.', 'Restartem přepínače.'], 'correct' => 1, 'explanation' => 'Test portu ověří přesně tu službu, kterou pravidlo povoluje.'],
        ]],
        'homework' => [['text' => 'Volitelné: nakresli síť svého domova nebo klubovny (bez skutečných adres) a označ, co bys oddělil/a do vlastní VLAN.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Testy spojení děláme jen v Linux Labu proti laboratorním adresám, nikdy proti cizím serverům.', 'Skutečná pravidla školní sítě nefotíme ani nesdílíme.'],
        'teacher_notes' => ['Častý omyl: „VLAN = jiný kabel“ – ukaž, že jde o logické oddělení na stejném přepínači.', 'Otázka do třídy: Proč SSH ze správy fungovalo, když web nešel?', 'Tempo: incident nech až na konec; když nestihnete, je to dobrý start lekce 3.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2 a 4 v Linux Labu podle zadání, úkoly 1 a 3 na papír podle vzoru tabulky.', 'Plán B offline: kartičky zařízení a pravidel, žáci skládají matici služeb na lavici.'],
        'glossary' => ['mac', 'acl'],
    ],
    3 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 3 · DNS a DHCP jako služby: porty, záznamy a ověření',
        'goal' => [
            'student' => 'Na konci hodiny umím popsat, jak klient dostane adresu přes DHCP a jméno přeloží přes DNS, a obojí ověřit v Linux Labu včetně portů.',
            'success_criteria' => ['Pojmenuji čtyři kroky DHCP (DORA) a porty UDP 67/68 a 53.', 'Z odpovědi dig vyčtu adresu a TTL záznamu typu A.', 'Při incidentu „jména nefungují“ postupuji od konfigurace klienta k serveru DNS.'],
        ],
        'competencies' => [['id' => 'net_dns_dhcp', 'level' => 2], ['id' => 'net_services', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: adresa odkud?', 'teacher' => 'Zeptá se: kdo vašemu notebooku řekl, jakou má adresu a kde je DNS?', 'student' => 'Zapíší odhad a porovnají ho se sousedem.', 'form' => 've dvojicích'],
            ['from' => 10, 'to' => 28, 'phase' => 'DHCP a zápůjčka', 'teacher' => 'Projde DORA na tabuli a ukáže porty 67 a 68.', 'student' => 'Ověří, že adresa je dynamická, a najdou naslouchající port DHCP klienta.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 48, 'phase' => 'Záznam DNS', 'teacher' => 'Předvede dig a vysvětlí sekci odpovědi a TTL.', 'student' => 'Vyčtou adresu a TTL záznamu intranetu.', 'form' => 'jednotlivě'],
            ['from' => 48, 'to' => 65, 'phase' => 'Služba a port', 'teacher' => 'Ukáže rozdíl mezi službou (program) a portem (číslo, na kterém poslouchá).', 'student' => 'Doplní tabulku služba → protokol → port.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Incident: jména nejdou', 'teacher' => 'Zadá: adresa i brána jsou v pořádku, ale jména se nepřekládají.', 'student' => 'Zkontrolují konfiguraci resolveru a porovnají odpověď pro existující a neexistující jméno.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: DHCP dává adresu a DNS server, DNS překládá jména.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zjisti příkazem ss -tunlp, na kterém portu UDP naslouchá DHCP klient, a doplň do tabulky porty DNS (53) a DHCP serveru (67).', 'output' => 'Tabulka služba → protokol → port s řádkem 0.0.0.0:68 z výstupu.', 'time' => '12 min',
                'sim' => [['cmd' => 'ss -tunlp', 'expect' => '0.0.0.0:68']]],
            ['text' => 'Popiš čtyři kroky DORA a z výpisu ip a ukaž, že adresa je přidělená dynamicky a jak dlouho ještě platí zápůjčka (lease).', 'output' => 'Čtyři kroky DORA a řádek s „dynamic“ a hodnotou valid_lft.', 'time' => '15 min',
                'sim' => [['cmd' => 'ip a', 'expect' => 'scope global dynamic eth0']]],
            ['text' => 'Ověř příkazem dig záznam typu A pro intranet.skola.test a vyčti z odpovědi adresu a TTL; výsledek porovnej s dig +short.', 'output' => 'Adresa 10.0.0.10, TTL 3600 s a vysvětlení, co TTL znamená pro změnu záznamu.', 'time' => '15 min',
                'sim' => [['cmd' => 'dig intranet.skola.test', 'expect' => 'ANSWER SECTION'], ['cmd' => 'dig +short intranet.skola.test', 'expect' => '10.0.0.10']]],
            ['text' => 'Incident „jména nefungují“: přečti /etc/resolv.conf, porovnej odpověď pro existující a neexistující jméno a sepiš postup diagnostiky ve čtyřech krocích.', 'output' => 'Řádky nameserver z konfigurace, odpověď NXDOMAIN pro neexistující jméno a postup se čtyřmi kroky.', 'time' => '18 min',
                'sim' => [['cmd' => 'cat /etc/resolv.conf', 'expect' => 'nameserver'], ['cmd' => 'nslookup neexistuje.skola.test', 'expect' => "server can't find"]]],
        ],
        'differentiation' => [
            'support' => 'Dostane obrázek DORA s prázdnými políčky a tabulku portů se dvěma vyplněnými řádky.',
            'standard' => 'Porty služeb, DORA s důkazem z ip a, záznam DNS s TTL a postup incidentu.',
            'challenge' => 'Vysvětlí, proč po změně záznamu DNS někteří uživatelé ještě hodinu vidí starou adresu, a navrhne, jak změnu naplánovat.',
        ],
        'assessment' => [
            'formative' => ['Ukaž na prstech: kolik kroků má DORA a který je poslední?', 'Kartičky: port 53 – DNS, nebo DHCP?'],
            'rubric' => [
                ['criterion' => 'DHCP', 'levels' => ['Nezná kroky.', 'Zná dva kroky DORA.', 'Popíše DORA a porty 67/68.', 'Vysvětlí, co se stane po vypršení zápůjčky.']],
                ['criterion' => 'DNS', 'levels' => ['Nepřečte odpověď.', 'Najde adresu.', 'Najde adresu i TTL.', 'Vysvětlí dopad TTL na změnu záznamu.']],
                ['criterion' => 'Diagnostika', 'levels' => ['Bez postupu.', 'Kroky bez pořadí.', 'Čtyři kroky od klienta k serveru.', 'U každého kroku uvede, co dokazuje.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_dns_dhcp', 'competence_label' => 'Sítě: DNS a DHCP v praxi', 'variants' => [
            ['question' => 'Který krok DHCP klient posílá jako úplně první?', 'options' => ['Discover – hledá DHCP server.', 'Acknowledge – potvrzuje adresu.', 'Offer – nabízí adresu.'], 'correct' => 0, 'explanation' => 'Pořadí DORA: Discover, Offer, Request, Acknowledge.'],
            ['question' => 'Odpověď dig obsahuje u záznamu A hodnotu 3600. Co znamená?', 'options' => ['Port, na kterém DNS poslouchá.', 'Počet uživatelů záznamu.', 'Jak dlouho (v sekundách) si smí resolver odpověď pamatovat.'], 'correct' => 2, 'explanation' => 'TTL je doba platnosti záznamu v mezipaměti.'],
            ['question' => 'Adresa i brána fungují, ale jména se nepřekládají. Co zkontroluješ nejdřív?', 'options' => ['Kabel k přepínači.', 'Které DNS servery má klient nastavené a jestli odpovídají.', 'Rychlost připojení.'], 'correct' => 1, 'explanation' => 'Když funguje brána, problém je výš – u DNS.'],
        ]],
        'homework' => [['text' => 'Volitelné: zjisti doma (bez zveřejnění adres), jakou dobu zápůjčky nastavuje domácí router, a zapiš ji do sešitu.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Dotazy DNS a testy portů děláme jen v Linux Labu a jen na laboratorní jména.', 'Konfiguraci skutečných serverů školy neměníme ani nezkoušíme.'],
        'teacher_notes' => ['Častý omyl: „DNS přiděluje adresy“ – vždy rozliš roli DHCP a DNS.', 'Otázka do třídy: Proč DHCP používá UDP a broadcast?', 'Tempo: dig má dlouhý výstup – ukaž nejdřív dig +short, pak plný výpis.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání).', 'Plán B offline: vytištěná odpověď dig a obrázek DORA k popsání.'],
        'glossary' => ['lease', 'ttl', 'nxdomain'],
    ],
    4 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 4 · Dohled a základní zabezpečení serveru',
        'goal' => [
            'student' => 'Na konci hodiny umím ověřit, že služba opravdu odpovídá, najít v logu chybové požadavky, zavřít zbytečné porty a nastavit bezpečný klíč pro SSH.',
            'success_criteria' => ['Rozliším ping od kontroly zdraví služby přes HTTP a obě umím spustit.', 'Z access logu spočítám stavové kódy a najdu opakovanou chybu.', 'Soukromý klíč SSH má práva 600 a root se přes SSH nepřihlásí.'],
        ],
        'competencies' => [['id' => 'lnx_services', 'level' => 2], ['id' => 'net_security', 'level' => 2], ['id' => 'lnx_ssh', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: server „žije“', 'teacher' => 'Ukáže, že server odpovídá na ping, ale web nejde, a zeptá se, co dohled přehlédl.', 'student' => 'Navrhnou tři věci, které by měl dohled sledovat.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 28, 'phase' => 'Kontrola zdraví', 'teacher' => 'Předvede systemctl is-active a curl -I a vysvětlí očekávaný stav 200.', 'student' => 'Ověří stav služby a odpověď webu a zapíší rozdíl proti pingu.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 48, 'phase' => 'Logy', 'teacher' => 'Ukáže access log a spočítání stavových kódů jedním řádkem.', 'student' => 'Najdou chybové požadavky a opakovanou stránku s chybou 404.', 'form' => 'jednotlivě'],
            ['from' => 48, 'to' => 65, 'phase' => 'Porty a firewall', 'teacher' => 'Vysvětlí pravidlo: otevřené jen to, co je potřeba.', 'student' => 'Vypíší naslouchající porty a navrhnou pravidla firewallu.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'SSH minimum', 'teacher' => 'Ukáže klíč ed25519, práva 600 a volbu PermitRootLogin.', 'student' => 'Vytvoří klíč, nastaví práva a ověří konfiguraci SSH serveru.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: dohled = služba odpovídá, log mluví, porty jsou jen nutné.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Ověř stav služby nginx a odpověď webu na adrese localhost (systemctl is-active nginx, curl -I) a vysvětli, proč je to lepší health check než ping.', 'output' => 'Stav active, odpověď HTTP 200 a věta o rozdílu proti pingu.', 'time' => '15 min',
                'sim' => [['cmd' => 'systemctl is-active nginx', 'expect' => 'active'], ['cmd' => 'curl -I http://localhost', 'expect' => '200 OK']]],
            ['text' => 'Spočítej v access logu stavové kódy jedním řádkem (sudo awk … | sort | uniq -c) a najdi stránku, která opakovaně vrací 404.', 'output' => 'Počty 200 / 403 / 404 a jméno stránky stara-stranka.html s návrhem opravy.', 'time' => '18 min',
                'sim' => [['cmd' => "sudo awk '{print \$9}' /var/log/nginx/access.log | sort | uniq -c", 'expect' => '404'], ['cmd' => 'sudo grep 404 /var/log/nginx/access.log', 'expect' => 'stara-stranka.html']]],
            ['text' => 'Vypiš naslouchající porty příkazem ss -tlnp a navrhni pravidla firewallu: které porty zůstanou otevřené, pro koho a proč.', 'output' => 'Seznam portů 22 a 80 a tabulka pravidel (port, zdroj, důvod) s výchozím zákazem.', 'time' => '15 min',
                'sim' => [['cmd' => 'ss -tlnp', 'expect' => '0.0.0.0:22']]],
            ['text' => 'Vytvoř klíč SSH typu ed25519, nastav soukromému klíči práva 600 a ověř v konfiguraci SSH serveru, že je přihlášení roota zakázané.', 'output' => 'Výpis -rw------- u klíče a řádek PermitRootLogin no.', 'time' => '15 min',
                'sim' => [['cmd' => "ssh-keygen -t ed25519 -f klic -N ''; chmod 600 klic; ls -l klic", 'expect' => '-rw-------'], ['cmd' => 'cat /etc/ssh/sshd_config', 'expect' => 'PermitRootLogin no']]],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu s příkazy kontroly a tabulku portů; doplňuje jen výsledky a důvody.',
            'standard' => 'Kontrola zdraví, rozbor logu, návrh pravidel firewallu a klíč SSH s právy 600.',
            'challenge' => 'Navrhne týdenní kontrolní seznam serveru (služby, log, porty, přihlášení) tak, aby ho zvládl i zástup.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: ping prošel – je web v pořádku?', 'Kartičky: port 22 – otevřít pro všechny, nebo jen pro správu?'],
            'rubric' => [
                ['criterion' => 'Kontrola zdraví', 'levels' => ['Jen ping.', 'Stav služby bez HTTP.', 'Stav služby i HTTP 200.', 'Vysvětlí, co který test nepokryje.']],
                ['criterion' => 'Logy', 'levels' => ['Log nenajde.', 'Najde chybový řádek.', 'Spočítá stavové kódy a najde opakovanou chybu.', 'Navrhne opravu a jak ověří, že pomohla.']],
                ['criterion' => 'Zabezpečení', 'levels' => ['Bez návrhu.', 'Zavře porty bez důvodu.', 'Pravidla s důvodem a klíč s právy 600.', 'Zdůvodní výchozí zákaz a zákaz přihlášení roota.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_services', 'competence_label' => 'Linux: služby a systémové logy', 'variants' => [
            ['question' => 'Server odpovídá na ping, ale web nejde. Který test to odhalí?', 'options' => ['Další ping s větším paketem.', 'Kontrola HTTP odpovědi, např. curl -I a očekávaný stav 200.', 'Výpis volného místa na disku.'], 'correct' => 1, 'explanation' => 'Ping ověřuje síť, ne běžící webovou službu.'],
            ['question' => 'ls -l u nového soukromého klíče ukazuje -rw-r--r--. Co uděláš?', 'options' => ['Nastavím práva 600, aby klíč četl jen vlastník.', 'Nic, čtení pro ostatní nevadí.', 'Nastavím 755, aby šel spustit.'], 'correct' => 0, 'explanation' => 'Soukromý klíč nesmí číst nikdo jiný než vlastník – jinak ho SSH odmítne použít.'],
            ['question' => 'V access logu je devětkrát 404 pro stejnou stránku. Co z toho plyne?', 'options' => ['Server je přetížený.', 'Útočník zná heslo.', 'Někde vede odkaz na stránku, která neexistuje – opravit odkaz nebo přesměrovat.'], 'correct' => 2, 'explanation' => '404 = stránka nenalezena; opakování ukazuje na rozbitý odkaz.'],
        ]],
        'homework' => [['text' => 'Volitelné: sepiš pět bodů týdenní kontroly serveru a ke každému příkaz, kterým ho ověříš v Linux Labu.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Logy, porty a klíče zkoušíme jen v simulátoru Linux Labu.', 'Soukromý klíč nikdy neposíláme ani nevkládáme do chatu; v logu nehledáme ani nesdílíme cizí osobní údaje.'],
        'teacher_notes' => ['Častý omyl: zelený ping = vše v pořádku – vrať se k úvodnímu příkladu.', 'Otázka do třídy: Proč je bezpečnější výchozí zákaz a výjimky než výchozí povolení?', 'Tempo: awk | sort | uniq -c je pro část třídy nové – nech ho opsat a vysvětli až výsledek.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání).', 'Plán B offline: vytištěný výřez access logu, žáci ručně počítají stavové kódy a navrhují pravidla firewallu.'],
        'glossary' => ['health-check', 'firewall'],
    ],
]]];
