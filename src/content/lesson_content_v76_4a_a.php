<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 4.A (provoz sítí a služeb), lekce 17–22.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php,
 * ověřované příkazy simulátoru viz lesson_content_v76_3a_a.php. Kompetence: návrh katalogu v75 „provoz_site“ (competencies_v75.php).
 */

return ['class_4a' => ['lessons' => [
    17 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 17 · Reakce na incident: runbook, komunikace a rozbor bez viníka',
        'goal' => [
            'student' => 'Na konci hodiny umím vést incident podle runbooku – určit závažnost, rozdělit role, pravidelně informovat a po vyřešení sepsat rozbor (postmortem), který hledá příčiny v systému, ne viníka.',
            'success_criteria' => ['Incident zařadím do stupně závažnosti podle dopadu na uživatele.', 'Stavová zpráva obsahuje dopad, co děláme a čas další zprávy.', 'Rozbor má časovou osu, příčinu, co pomohlo, co ne a měřitelná opatření s vlastníkem.'],
        ],
        'competencies' => [['id' => 'ops_incident', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: dva incidenty', 'teacher' => 'Porovná incident, kde všichni opravovali najednou, s incidentem vedeným podle runbooku.', 'student' => 'Zapíší, co v prvním chybělo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Závažnost a role', 'teacher' => 'Představí stupně závažnosti a role (vedoucí, technik, komunikace, zapisovatel).', 'student' => 'Zařadí čtyři scénáře do stupňů.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 50, 'phase' => 'Cvičný incident', 'teacher' => 'V simulátoru zastaví webovou službu a hraje uživatele.', 'student' => 'Ve čtveřici vedou incident podle runbooku.', 'form' => 've skupinách'],
            ['from' => 50, 'to' => 62, 'phase' => 'Komunikace', 'teacher' => 'Ukáže vzor stavové zprávy.', 'student' => 'Napíší dvě stavové zprávy.', 'form' => 've skupinách'],
            ['from' => 62, 'to' => 82, 'phase' => 'Rozbor bez viníka', 'teacher' => 'Vede rozbor otázkami „proč systém dovolil…“.', 'student' => 'Sepíší rozbor s opatřeními.', 'form' => 've skupinách'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne cyklus incidentu.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zařaď čtyři scénáře (nefunguje celý web, pomalý export, překlep v patičce, výpadek přihlášení pro polovinu uživatelů) do stupňů závažnosti a zdůvodni podle dopadu.', 'output' => 'Tabulka scénář → stupeň → dopad.', 'time' => '13 min'],
            ['text' => 'Jako technik v cvičném incidentu zjisti stav webové služby a záznam o jejím zastavení v logu.', 'output' => 'is-active vrací inactive, journal obsahuje Stopped nginx.service.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx', 'expect' => 'inactive'], ['cmd' => 'sudo systemctl stop nginx; journalctl -u nginx -n 5', 'expect' => 'Stopped nginx.service']]],
            ['text' => 'Obnov službu a ověř ji z pohledu uživatele; výsledek zapiš jako závěrečnou stavovou zprávu.', 'output' => 'curl -I vrací 200 OK a zpráva „vyřešeno, ověřeno v …, sledujeme do …“.', 'time' => '12 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; curl -I http://localhost', 'expect' => '200 OK']]],
            ['text' => 'Sepište rozbor incidentu: časová osa, dopad, příčina, co pomohlo, co ne a tři opatření s vlastníkem a termínem – bez jmen viníků.', 'output' => 'Rozbor na jednu stranu se třemi měřitelnými opatřeními.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu stavové zprávy a rozboru s otázkami.',
            'standard' => 'Závažnost, cvičný incident, komunikace a rozbor.',
            'challenge' => 'Navrhne metriku (např. doba do zjištění, doba do obnovy), kterou bude tým sledovat po opatřeních.',
        ],
        'assessment' => [
            'formative' => ['Učitel přečte scénář, skupiny ukážou stupeň závažnosti.', 'Kontrola rozboru: najdi v něm větu, která hledá viníka, a přepiš ji.'],
            'rubric' => [
                ['criterion' => 'Závažnost a role', 'levels' => ['Bez rozlišení.', 'Stupně bez zdůvodnění.', 'Stupně podle dopadu a role dodržené.', 'Eskalace podle pravidel.']],
                ['criterion' => 'Komunikace', 'levels' => ['Žádná.', 'Nepravidelná.', 'Dopad, postup a čas další zprávy.', 'Srozumitelná i pro vedení.']],
                ['criterion' => 'Rozbor', 'levels' => ['Chybí.', 'Hledá viníka.', 'Časová osa, příčina, opatření s vlastníkem.', 'Opatření měřitelná a ověřitelná.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_incident', 'competence_label' => 'Řízení incidentu', 'variants' => [
            ['question' => 'Podle čeho určíš závažnost incidentu?', 'options' => ['Podle toho, jak dlouho oprava potrvá.', 'Podle dopadu na uživatele a služby.', 'Podle toho, kdo incident nahlásil.'], 'correct' => 1, 'explanation' => 'Závažnost = dopad, ne obtížnost opravy.'],
            ['question' => 'Co je cílem rozboru incidentu bez hledání viníka?', 'options' => ['Najít, co v systému a procesech chybu umožnilo, a zabránit opakování.', 'Najít, kdo má dostat trest.', 'Rychle incident zapomenout.'], 'correct' => 0, 'explanation' => 'Lidé chybují; systém má chybám bránit.'],
            ['question' => 'Které opatření z rozboru je nejlepší?', 'options' => ['Dávat větší pozor.', 'Lépe komunikovat.', 'Do pátku přidat kontrolu dostupnosti webu s upozorněním (vlastník: správce webu).'], 'correct' => 2, 'explanation' => 'Konkrétní, s vlastníkem a termínem.'],
        ]],
        'homework' => [['text' => 'Volitelné: přepiš jedno opatření z rozboru tak, aby mělo vlastníka, termín a způsob ověření.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Incident se odehrává jen v simulátoru; žádné skutečné služby se nezastavují.', 'Rozbor neobsahuje jména ani hodnocení lidí; zápisy zůstávají ve třídě.'],
        'teacher_notes' => ['Častý omyl: všichni ve skupině opravují a nikdo nekomunikuje – trvej na rolích.', 'Otázka do třídy: Které opatření by tento incident zkrátilo nejvíc?', 'Tempo: cvičný incident drž na 25 minut; rozbor je výstup hodiny.'],
        'substitution' => ['Zástup bez odborníka: skupiny plní úkoly 1–4, simulátor podle zadání (výstupy jsou v zadání).', 'Plán B offline: incident podle vytištěných výpisů, role a rozbor na papíře.'],
        'glossary' => ['postmortem', 'runbook'],
    ],
    18 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 18 · Závěrečné cvičení spolehlivosti: služba, data a přístup',
        'goal' => [
            'student' => 'Na konci hodiny umím v jednom cvičení ověřit spolehlivost serveru ve čtyřech oblastech – služba, místo na disku, záloha s obnovou a zabezpečený přístup – a každou oblast doložit důkazem.',
            'success_criteria' => ['U každé oblasti mám příkaz, výstup a závěr „v pořádku / riziko“.', 'Zálohu ověřím skutečnou obnovou, ne jen existencí archivu.', 'Výsledky shrnu do tabulky rizik s návrhem opatření.'],
        ],
        'competencies' => [['id' => 'ops_services', 'level' => 3], ['id' => 'ops_backup', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Zadání cvičení', 'teacher' => 'Představí čtyři oblasti a hodnoticí tabulku.', 'student' => 'Rozdělí si oblasti ve dvojici.', 'form' => 've dvojicích'],
            ['from' => 10, 'to' => 25, 'phase' => 'Služba', 'teacher' => 'Připomene kontrolu konfigurace a stavu.', 'student' => 'Ověří konfiguraci a stav webové služby.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 38, 'phase' => 'Místo na disku', 'teacher' => 'Připomene df a du.', 'student' => 'Ověří zaplnění a největší adresáře.', 'form' => 've dvojicích'],
            ['from' => 38, 'to' => 58, 'phase' => 'Záloha a obnova', 'teacher' => 'Hlídá, aby obnova proběhla do jiného místa a byla ověřená.', 'student' => 'Vytvoří zálohu, obnoví ji a porovnají obsah.', 'form' => 've dvojicích'],
            ['from' => 58, 'to' => 72, 'phase' => 'Přístup', 'teacher' => 'Připomene zpevnění SSH.', 'student' => 'Ověří nastavení SSH a naslouchající porty.', 'form' => 've dvojicích'],
            ['from' => 72, 'to' => 90, 'phase' => 'Tabulka rizik a exit ticket', 'teacher' => 'Ukáže tabulku oblast – důkaz – riziko – opatření.', 'student' => 'Vyplní tabulku a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Ověř konfiguraci webového serveru a jeho stav; obojí doložit výstupem.', 'output' => 'nginx -t: syntax is ok; služba active (running).', 'time' => '12 min',
                'sim' => [['cmd' => 'sudo nginx -t', 'expect' => 'test is successful'], ['cmd' => 'systemctl status nginx', 'expect' => 'active (running)']]],
            ['text' => 'Zjisti zaplnění kořenového oddílu a velikost logů v /var/log.', 'output' => '/dev/sda1 zaplněn na 31 %, logy v řádu kB.', 'time' => '10 min',
                'sim' => [['cmd' => 'df -h', 'expect' => '31%'], ['cmd' => 'du -sh /var/log/*', 'expect' => '/var/log/nginx']]],
            ['text' => 'Vytvoř zálohu adresáře web, adresář smaž, obnov ho z archivu a ověř obsah souboru.', 'output' => 'Po obnově soubor web/index.html obsahuje původní text „ahoj“.', 'time' => '20 min',
                'sim' => [['cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; rm -r web; tar -xzf zaloha.tar.gz; cat web/index.html', 'expect' => 'ahoj']]],
            ['text' => 'Ověř nastavení SSH (port, přihlášení roota) a naslouchající porty a vyplň tabulku rizik pro všechny čtyři oblasti.', 'output' => 'Port 22, PermitRootLogin no, porty 22 a 80; tabulka oblast – důkaz – riziko – opatření.', 'time' => '20 min',
                'sim' => [['cmd' => "sudo grep -E 'Port|PermitRootLogin' /etc/ssh/sshd_config", 'expect' => 'PermitRootLogin no'], ['cmd' => 'ss -tln', 'expect' => '0.0.0.0:22']]],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu se čtyřmi oblastmi a prvním příkazem každé oblasti.',
            'standard' => 'Všechny čtyři oblasti s důkazy a tabulka rizik.',
            'challenge' => 'Doplní k záloze výpočet RPO a RTO pro zálohu jednou denně a obnovu za 20 minut.',
        ],
        'assessment' => [
            'formative' => ['Učitel se zeptá: existuje archiv – je záloha v pořádku? (ne, dokud neproběhne obnova)', 'Dvojice si vymění tabulky rizik a doplní chybějící důkaz.'],
            'rubric' => [
                ['criterion' => 'Důkazy', 'levels' => ['Bez důkazů.', '1–2 oblasti.', 'Všechny 4 oblasti s výstupem.', 'Důkazy srozumitelné i bez autora.']],
                ['criterion' => 'Záloha', 'levels' => ['Chybí.', 'Archiv bez obnovy.', 'Obnova ověřená obsahem.', 'Obnova do jiného místa a výpočet RPO/RTO.']],
                ['criterion' => 'Tabulka rizik', 'levels' => ['Chybí.', 'Bez opatření.', 'Riziko a opatření u každé oblasti.', 'Opatření seřazená podle dopadu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_backup', 'competence_label' => 'Záloha a obnova', 'variants' => [
            ['question' => 'Kdy je záloha opravdu ověřená?', 'options' => ['Když archiv existuje.', 'Když má archiv velikost větší než 0.', 'Když z ní proběhla obnova a obsah sedí.'], 'correct' => 2, 'explanation' => 'Důvěryhodná je jen ověřená obnova.'],
            ['question' => 'Co ověří nginx -t?', 'options' => ['Syntaxi konfigurace webového serveru.', 'Rychlost webu.', 'Platnost certifikátu.'], 'correct' => 0, 'explanation' => 'Test konfigurace před restartem.'],
            ['question' => 'PermitRootLogin no v konfiguraci SSH znamená:', 'options' => ['SSH je vypnuté.', 'Přihlásit se přímo jako root přes SSH nelze.', 'Root nemá heslo.'], 'correct' => 1, 'explanation' => 'Správce se přihlásí svým účtem a použije sudo.'],
        ]],
        'homework' => [['text' => 'Volitelné: spočítej RPO a RTO pro zálohu každých 6 hodin a obnovu trvající 45 minut.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Zálohy, mazání a obnovu zkoušíme jen v simulátoru Linux Labu.', 'Tabulka rizik neobsahuje skutečné adresy ani přístupové údaje.'],
        'teacher_notes' => ['Častý omyl: „archiv existuje = máme zálohu“ – nech žáky obnovu skutečně provést.', 'Otázka do třídy: Které riziko z tabulky byste řešili jako první a proč?', 'Tempo: oblasti mohou dvojice dělat souběžně; tabulka rizik je povinný výstup.'],
        'substitution' => ['Zástup bez odborníka: dvojice plní úkoly 1–4 v Linux Labu (výstupy v zadání).', 'Plán B offline: vytištěné výpisy čtyř oblastí a tabulka rizik.'],
        'glossary' => ['restore', 'hardening'],
    ],
    19 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 19 · Čtyři zlaté signály: provoz, chyby, latence, saturace',
        'goal' => [
            'student' => 'Na konci hodiny umím ze záznamů služby spočítat provoz a podíl chyb, vysvětlit latenci pomocí p95 a ze stavu systému odhadnout saturaci – a navrhnout, které signály hlídat upozorněním.',
            'success_criteria' => ['Z přístupového logu spočítám počet požadavků a podíl odpovědí 4xx/5xx v procentech.', 'Vysvětlím, proč se latence sleduje percentilem p95, ne průměrem.', 'Z load average a počtu CPU odhadnu, jestli je server přetížený.'],
        ],
        'competencies' => [['id' => 'ops_observability', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: „je to pomalé“', 'teacher' => 'Přečte stížnost bez čísel a zeptá se, co potřebujeme změřit.', 'student' => 'Navrhnou měřitelné signály.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Čtyři signály', 'teacher' => 'Vysvětlí provoz, chyby, latenci a saturaci.', 'student' => 'Ke každému signálu napíší otázku, na kterou odpovídá.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 42, 'phase' => 'Provoz a chyby z logu', 'teacher' => 'Předvede počítání požadavků a chybových kódů.', 'student' => 'Spočítají provoz a podíl chyb.', 'form' => 've dvojicích'],
            ['from' => 42, 'to' => 58, 'phase' => 'Latence a p95', 'teacher' => 'Na deseti časech odezvy ukáže průměr a p95.', 'student' => 'Spočítají p95 z připravených hodnot.', 'form' => 'jednotlivě'],
            ['from' => 58, 'to' => 80, 'phase' => 'Saturace a upozornění', 'teacher' => 'Ukáže load average a počet CPU.', 'student' => 'Odhadnou saturaci a navrhnou dvě upozornění.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: měř, než začneš opravovat.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spočítej počet požadavků v přístupovém logu a počet odpovědí s kódem 400 a vyšším; vyjádři podíl chyb v procentech.', 'output' => '30 požadavků, 15 odpovědí ≥ 400 → 50 % chyb.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo grep -c . /var/log/nginx/access.log', 'expect' => '30'], ['cmd' => "sudo awk '\$9 >= 400' /var/log/nginx/access.log | wc -l", 'expect' => '15']]],
            ['text' => 'Zjisti, ze které adresy přišlo nejvíc požadavků (provoz podle klienta).', 'output' => 'Nejvíc 10.0.0.31 (13 požadavků).', 'time' => '10 min',
                'sim' => [['cmd' => "sudo awk '{print \$1}' /var/log/nginx/access.log | sort | uniq -c | sort -rn | head -n 3", 'expect' => '13 10.0.0.31']]],
            ['text' => 'Z deseti časů odezvy (40, 42, 45, 41, 44, 43, 46, 40, 300, 42 ms) spočítej průměr a p95 a vysvětli, proč se liší.', 'output' => 'Průměr 68,3 ms, p95 = 300 ms – jeden pomalý požadavek se v průměru schová.', 'time' => '15 min'],
            ['text' => 'Zjisti zatížení (load average) a počet CPU a odhadni saturaci; navrhni dvě upozornění s prahem a dobou trvání.', 'output' => 'Load 0,08 při 2 CPU = nízká saturace; např. „chyby > 5 % po 5 min“, „p95 > 500 ms po 10 min“.', 'time' => '20 min',
                'sim' => [['cmd' => 'uptime', 'expect' => 'load average: 0.08'], ['cmd' => 'lscpu | head -n 6', 'expect' => 'CPU(s):                  2']]],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku čtyř signálů s příkazy a seřazené časy odezvy.',
            'standard' => 'Provoz, chyby, p95, saturace a dvě upozornění.',
            'challenge' => 'Navrhne SLI a SLO pro chyby (např. 99 % úspěšných požadavků za 30 dní) a spočítá rozpočet chyb.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže čísla, třída určí, který signál popisují.', 'Dvojice: proč průměr 68 ms neznamená, že je web rychlý?'],
            'rubric' => [
                ['criterion' => 'Provoz a chyby', 'levels' => ['Nespočítá.', 'Počty bez podílu.', 'Počty i podíl v procentech.', 'Rozdělení podle klienta i stránky.']],
                ['criterion' => 'Latence', 'levels' => ['Nezná.', 'Jen průměr.', 'p95 s vysvětlením.', 'Vysvětlí dopad pomalého konce (tail latency).']],
                ['criterion' => 'Saturace a upozornění', 'levels' => ['Chybí.', 'Upozornění bez prahu.', 'Prah i doba trvání.', 'Upozornění vázané na dopad na uživatele.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_observability', 'competence_label' => 'Pozorovatelnost a SLO', 'variants' => [
            ['question' => 'Proč se latence sleduje p95, a ne průměrem?', 'options' => ['p95 je vždy menší.', 'Průměr schová pomalé požadavky, které uživatelé cítí.', 'Průměr nejde spočítat.'], 'correct' => 1, 'explanation' => 'p95 ukazuje zkušenost i „pomalého konce“.'],
            ['question' => 'Z 200 požadavků skončilo 10 chybou 5xx. Jaký je podíl chyb?', 'options' => ['5 %', '10 %', '20 %'], 'correct' => 0, 'explanation' => '10 / 200 = 0,05 = 5 %.'],
            ['question' => 'Server má 2 CPU a load average 6. Co to naznačuje?', 'options' => ['Server je nevyužitý.', 'Je málo místa na disku.', 'Procesy čekají na CPU – saturace.'], 'correct' => 2, 'explanation' => 'Load výrazně nad počtem CPU = fronta čekajících.'],
        ]],
        'homework' => [['text' => 'Volitelné: změř 10× dobu načtení oblíbeného webu a spočítej průměr a nejpomalejší hodnotu.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Logy jsou ze simulátoru; skutečné logy obsahují adresy uživatelů a čte je jen oprávněná osoba.', 'Upozornění navrhujeme, nenastavujeme na skutečných systémech.'],
        'teacher_notes' => ['Častý omyl: průměr jako „rychlost webu“ – ukaž jeden pomalý požadavek, který p95 odhalí.', 'Otázka do třídy: Který signál byste hlídali, kdybyste mohli jen jeden?', 'Tempo: p95 počítejte ručně na deseti hodnotách – seřadit, vzít hodnotu na 95. percentilu.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 2 a 4 v Linux Labu (výstupy v zadání), úkol 3 na papír.', 'Plán B offline: vytištěný výřez logu a časy odezvy; výpočty ručně.'],
        'glossary' => ['p95', 'observability'],
    ],
    20 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 20 · Reverzní proxy: důkazy z hlaviček a konfigurace',
        'goal' => [
            'student' => 'Na konci hodiny umím vysvětlit cestu požadavku přes reverzní proxy (reverse proxy) k upstream službě, přečíst důkazy z hlaviček odpovědi a konfigurace a navrhnout kontrolu zdraví (health check) upstreamu.',
            'success_criteria' => ['Nakreslím cestu klient → proxy → upstream a u každého článku řeknu, kde hledat důkaz.', 'Z hlaviček odpovědi přečtu stav, server a typ obsahu.', 'Navrhnu health check s cestou, očekávanou odpovědí a intervalem.'],
        ],
        'competencies' => [['id' => 'ops_proxy_tls', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: 502 Bad Gateway', 'teacher' => 'Ukáže chybu 502 a zeptá se, kdo ji vrátil.', 'student' => 'Odhadnou, kde v cestě je problém.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Cesta požadavku', 'teacher' => 'Nakreslí klient → proxy → upstream a vysvětlí 502 a 504.', 'student' => 'Doplní schéma o místa důkazů.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 42, 'phase' => 'Hlavičky odpovědi', 'teacher' => 'Předvede curl -I a rozbor hlaviček.', 'student' => 'Přečtou stav, server a typ obsahu.', 'form' => 've dvojicích'],
            ['from' => 42, 'to' => 60, 'phase' => 'Konfigurace', 'teacher' => 'Ukáže konfiguraci serveru a kde by byl proxy_pass na upstream.', 'student' => 'Najdou v konfiguraci listen a location.', 'form' => 'jednotlivě'],
            ['from' => 60, 'to' => 80, 'phase' => 'Health check', 'teacher' => 'Vysvětlí aktivní kontrolu upstreamu.', 'student' => 'Navrhnou health check a reakci na selhání.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: kdo vrátil chybu, tam hledej.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Nakresli cestu požadavku klient → reverzní proxy → upstream a ke každému článku zapiš, kde najdeš důkaz (hlavička, log proxy, log aplikace).', 'output' => 'Schéma tří článků s místy důkazů a významem 502 a 504.', 'time' => '13 min'],
            ['text' => 'Přečti hlavičky odpovědi webového serveru a zapiš stav, server a typ obsahu.', 'output' => 'HTTP/1.1 200 OK, Server: nginx/1.22.1, Content-Type: text/html.', 'time' => '13 min',
                'sim' => [['cmd' => 'curl -I http://localhost', 'expect' => 'Server: nginx/1.22.1']]],
            ['text' => 'Najdi v konfiguraci výchozího webu direktivy listen a location a vysvětli, kam bys doplnil(a) přesměrování na upstream.', 'output' => 'listen 80 default_server; location / { try_files … } – proxy_pass by nahradil try_files v location.', 'time' => '17 min',
                'sim' => [['cmd' => 'cat /etc/nginx/sites-enabled/default', 'expect' => 'location / {'], ['cmd' => 'ss -tln', 'expect' => '0.0.0.0:80']]],
            ['text' => 'Navrhni health check upstreamu: cesta (např. /zdravi), očekávaná odpověď, interval, počet selhání do vyřazení a co uvidí uživatel.', 'output' => 'Tabulka parametrů health checku a reakce proxy.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane schéma cesty se třemi rámečky a rozepsaný výstup curl -I.',
            'standard' => 'Schéma, hlavičky, konfigurace a návrh health checku.',
            'challenge' => 'Navrhne rozložení zátěže mezi dva upstreamy (round-robin) a popíše, co se stane při výpadku jednoho.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže kód 502 / 504 / 404 a třída řekne, který článek ho nejspíš vrátil.', 'Dvojice: ukaž v konfiguraci místo pro proxy_pass.'],
            'rubric' => [
                ['criterion' => 'Cesta požadavku', 'levels' => ['Chybí.', 'Bez důkazů.', 'Tři články s místy důkazů.', 'Vysvětlí 502 vs. 504.']],
                ['criterion' => 'Hlavičky a konfigurace', 'levels' => ['Nečte.', 'Jen stav.', 'Stav, server, typ obsahu a direktivy.', 'Navrhne změnu konfigurace s testem.']],
                ['criterion' => 'Health check', 'levels' => ['Chybí.', 'Jen cesta.', 'Cesta, odpověď, interval, prah.', 'Reakce proxy i informace pro uživatele.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_proxy_tls', 'competence_label' => 'Reverzní proxy a TLS', 'variants' => [
            ['question' => 'Uživatel dostane 502 Bad Gateway. Kdo tu odpověď vytvořil?', 'options' => ['Prohlížeč uživatele.', 'DNS server.', 'Reverzní proxy, protože nedostala platnou odpověď od upstreamu.'], 'correct' => 2, 'explanation' => '502 vrací brána, když upstream selže.'],
            ['question' => 'K čemu je health check upstreamu?', 'options' => ['Proxy pozná nefunkční upstream a přestane na něj posílat požadavky.', 'Zrychlí odpověď DNS.', 'Zašifruje provoz.'], 'correct' => 0, 'explanation' => 'Aktivní kontrola vyřadí nemocný upstream.'],
            ['question' => 'Co je rozdíl mezi 502 a 504?', 'options' => ['Žádný.', '502 = neplatná odpověď upstreamu, 504 = upstream neodpověděl včas.', '504 je chyba klienta.'], 'correct' => 1, 'explanation' => 'Bad Gateway vs. Gateway Timeout.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu přečti hlavičky odpovědi intranetu a porovnej je s localhost – co se liší?', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Proxy a hlavičky zkoumáme jen v simulátoru a na laboratorních jménech.', 'Hlavičky skutečných služeb mohou prozradit verze softwaru – v produkci je omezujeme.'],
        'teacher_notes' => ['Častý omyl: „502 je chyba proxy“ – proxy jen hlásí, že upstream selhal.', 'Otázka do třídy: Kde v cestě byste hledali, kdyby chyba přišla jen u každého druhého požadavku?', 'Tempo: konfigurace bez skutečného proxy_pass – jde o porozumění, ne o nastavení.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.', 'Plán B offline: vytištěná konfigurace a hlavičky, schéma cesty na papíře.'],
        'glossary' => ['reverse-proxy', 'health-check'],
    ],
    21 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 21 · Síť kontejnerů: porty, sítě a data mimo kontejner',
        'goal' => [
            'student' => 'Na konci hodiny umím popsat, jak kontejner komunikuje s hostitelem a s ostatními kontejnery, navrhnout mapování portů bez kolize a vysvětlit, proč data patří do svazku (volume), ne do kontejneru.',
            'success_criteria' => ['Zapíšu mapování portů (port mapping) hostitel:kontejner a odhalím kolizi s již naslouchající službou.', 'Rozliším komunikaci kontejnerů ve vlastní síti podle jména a zvenku přes publikovaný port.', 'Vysvětlím, co se stane s daty po smazání kontejneru bez svazku.'],
        ],
        'competencies' => [['id' => 'ops_services', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: zmizelá databáze', 'teacher' => 'Popíše situaci: po aktualizaci kontejneru zmizela data.', 'student' => 'Hádají proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Obraz, kontejner, svazek', 'teacher' => 'Vysvětlí image, container a volume.', 'student' => 'Doplní schéma tří pojmů.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Mapování portů', 'teacher' => 'Ukáže zápis 8080:80 a kolizi portů na hostiteli.', 'student' => 'Zjistí obsazené porty hostitele a navrhnou mapování.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 65, 'phase' => 'Síť kontejnerů', 'teacher' => 'Nakreslí vlastní síť: web → api → db podle jmen.', 'student' => 'Navrhnou síť pro tři kontejnery, ven jen web.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 80, 'phase' => 'Kontrolní scénáře', 'teacher' => 'Zadá tři chybové scénáře.', 'student' => 'Určí příčinu a opravu.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: ven jen to, co musí.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Doplň schéma: obraz (image) → kontejner (container) → svazek (volume) a ke každému napiš, co se stane při aktualizaci.', 'output' => 'Schéma s poznámkou: kontejner se nahradí, svazek zůstane.', 'time' => '13 min'],
            ['text' => 'Zjisti, které porty hostitel už poslouchá, a navrhni mapování portů pro webový kontejner tak, aby nekolidovalo.', 'output' => 'Hostitel poslouchá 22 a 80 → mapování např. 8080:80 se zdůvodněním.', 'time' => '15 min',
                'sim' => [['cmd' => 'ss -tln', 'expect' => '0.0.0.0:80']]],
            ['text' => 'Navrhni síť pro tři kontejnery (web, api, db): kdo s kým mluví podle jména a který jediný port se publikuje ven.', 'output' => 'Schéma sítě s publikovaným portem jen u webu a db bez portu ven.', 'time' => '20 min'],
            ['text' => 'U tří scénářů (port už obsazen, api nevidí db, data zmizela po aktualizaci) urči příčinu a opravu.', 'output' => 'Tabulka scénář → příčina → oprava → ověření.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane schéma s prázdnými rámečky a tabulku mapování portů s příkladem.',
            'standard' => 'Schéma pojmů, mapování portů, síť a scénáře.',
            'challenge' => 'Popíše, jak by kontejner db omezil(a), aby byl dostupný jen z kontejneru api (síť, žádný publikovaný port).',
        ],
        'assessment' => [
            'formative' => ['Učitel napíše 8080:80, třída řekne, který port je na hostiteli.', 'Dvojice: kde jsou data po smazání kontejneru?'],
            'rubric' => [
                ['criterion' => 'Pojmy', 'levels' => ['Zaměňuje.', 'Zná dva.', 'Image, container, volume správně.', 'Vysvětlí životní cyklus při aktualizaci.']],
                ['criterion' => 'Porty a síť', 'levels' => ['Bez návrhu.', 'Kolize portů.', 'Bez kolize, ven jen web.', 'Zdůvodní menší útočnou plochu.']],
                ['criterion' => 'Scénáře', 'levels' => ['Neřeší.', 'Jeden.', 'Tři s příčinou a opravou.', 'I s ověřením opravy.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_services', 'competence_label' => 'Služby a kontejnery', 'variants' => [
            ['question' => 'Co znamená mapování portů 8080:80?', 'options' => ['Port 8080 na hostiteli vede na port 80 v kontejneru.', 'Port 80 na hostiteli vede na 8080 v kontejneru.', 'Kontejner má dva porty pro rychlost.'], 'correct' => 0, 'explanation' => 'Zápis je hostitel:kontejner.'],
            ['question' => 'Proč databázový kontejner nemá publikovaný port ven?', 'options' => ['Protože databáze port nepotřebuje.', 'Aby k ní přistupovaly jen kontejnery ve stejné síti – menší útočná plocha.', 'Protože porty jsou pomalé.'], 'correct' => 1, 'explanation' => 'Ven jen to, co musí.'],
            ['question' => 'Kam patří data databáze v kontejneru?', 'options' => ['Do vrstvy kontejneru.', 'Do obrazu.', 'Do svazku (volume) mimo životní cyklus kontejneru.'], 'correct' => 2, 'explanation' => 'Svazek přežije nahrazení kontejneru.'],
        ]],
        'homework' => [['text' => 'Volitelné: nakresli síť kontejnerů pro blog (web, databáze, zálohy) a označ, co se publikuje ven.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Kontejnery v hodině navrhujeme na papíře a v simulátoru; nic se nespouští na školních počítačích.', 'Obrazy kontejnerů jen z důvěryhodných zdrojů; hesla nepatří do obrazu ani do konfigurace v repozitáři.'],
        'teacher_notes' => ['Častý omyl: zaměněné pořadí hostitel:kontejner – nech žáky číst mapování nahlas.', 'Otázka do třídy: Co všechno se ztratí, když smažete kontejner bez svazku?', 'Tempo: simulátor kontejnery nespouští – praktická část je zjištění portů hostitele, zbytek je návrh.'],
        'substitution' => ['Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), ostatní úkoly na papíře.', 'Plán B offline: kartičky kontejnerů, sítí a portů skládané na lavici.'],
        'glossary' => ['container', 'port-mapping'],
    ],
    22 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 22 · Den obnovy: záloha nestačí, obnova rozhoduje',
        'goal' => [
            'student' => 'Na konci hodiny umím provést cvičnou obnovu ze zálohy, ověřit shodu dat otiskem SHA-256, změřit dobu obnovy a porovnat výsledek s cíli RPO a RTO.',
            'success_criteria' => ['Obnovím data z archivu do jiného místa a ověřím je otiskem.', 'Spočítám skutečné RPO a RTO cvičení a porovnám je s cíli.', 'Zapíšu do runbooku obnovy krok, který cvičení ukázalo jako slabý.'],
        ],
        'competencies' => [['id' => 'ops_backup', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: záloha z minulého roku', 'teacher' => 'Popíše firmu, která zjistila nefunkční zálohy až při havárii.', 'student' => 'Navrhnou, jak tomu předejít.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Cíle RPO a RTO', 'teacher' => 'Připomene RPO a RTO na příkladu.', 'student' => 'Zapíší cíle pro cvičnou službu.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 45, 'phase' => 'Záloha a obnova', 'teacher' => 'Spouští časovač cvičení.', 'student' => 'Vytvoří zálohu, „ztratí“ data a obnoví je.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 60, 'phase' => 'Ověření otiskem', 'teacher' => 'Ukáže sha256sum před a po.', 'student' => 'Porovnají otisky originálu a obnovy.', 'form' => 've dvojicích'],
            ['from' => 60, 'to' => 80, 'phase' => 'Vyhodnocení', 'teacher' => 'Vede porovnání s cíli.', 'student' => 'Spočítají skutečné RPO/RTO a upraví runbook.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: neověřená záloha je jen naděje.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zapiš cíle cvičné služby: RPO 1 hodina, RTO 30 minut, a vysvětli, co každý cíl znamená pro četnost záloh a rychlost obnovy.', 'output' => 'Dvě věty: záloha aspoň každou hodinu; obnova do 30 minut včetně ověření.', 'time' => '10 min'],
            ['text' => 'Vytvoř zálohu adresáře web, adresář smaž a obnov ho z archivu; ověř obsah obnoveného souboru.', 'output' => 'Po rozbalení web/index.html obsahuje „ahoj“.', 'time' => '20 min',
                'sim' => [['cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; rm -r web; tar -xzf zaloha.tar.gz; cat web/index.html', 'expect' => 'ahoj'], ['cmd' => 'mkdir web; echo ahoj > web/index.html; tar -czf zaloha.tar.gz web; tar -tzf zaloha.tar.gz', 'expect' => 'web/index.html']]],
            ['text' => 'Ověř shodu originálu a kopie otiskem SHA-256 a vysvětli, proč nestačí porovnat velikost.', 'output' => 'Oba soubory mají stejný otisk 0b4f321b…; velikost se může shodovat i u jiného obsahu.', 'time' => '15 min',
                'sim' => [['cmd' => 'echo ahoj > a.txt; cp a.txt b.txt; sha256sum a.txt b.txt', 'expect' => '0b4f321bf090fcf3792f5fffc4fcd1801fab37e0470c66d46cb0f0181b316a73  b.txt']]],
            ['text' => 'Spočítej skutečné RPO a RTO cvičení (poslední záloha před „havárií“, čas obnovy včetně ověření) a doplň do runbooku obnovy jeden zlepšený krok.', 'output' => 'Porovnání se cíli a upravený krok runbooku.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu kroků obnovy a vzorec pro RPO a RTO.',
            'standard' => 'Cíle, záloha a obnova, otisk a vyhodnocení.',
            'challenge' => 'Navrhne pravidlo 3-2-1 pro cvičnou službu a vysvětlí, proti kterým hrozbám chrání.',
        ],
        'assessment' => [
            'formative' => ['Učitel řekne „záloha každých 6 hodin“, třída řekne nejhorší možnou ztrátu dat.', 'Dvojice: ukažte otisk originálu a obnovy.'],
            'rubric' => [
                ['criterion' => 'RPO a RTO', 'levels' => ['Nezná.', 'Zaměňuje.', 'Správně vysvětlí a spočítá.', 'Navrhne zálohování podle cílů.']],
                ['criterion' => 'Obnova', 'levels' => ['Neprovede.', 'Bez ověření.', 'Obnova ověřená obsahem a otiskem.', 'Obnova do jiného místa s měřením času.']],
                ['criterion' => 'Runbook', 'levels' => ['Bez úprav.', 'Obecná poznámka.', 'Konkrétní zlepšený krok.', 'Krok s ověřením a vlastníkem.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_backup', 'competence_label' => 'Záloha a obnova', 'variants' => [
            ['question' => 'Záloha běží každých 6 hodin. Jaké je nejhorší RPO?', 'options' => ['6 hodin ztracených dat.', '6 minut.', 'Žádná ztráta.'], 'correct' => 0, 'explanation' => 'Havárie těsně před další zálohou = ztráta až 6 hodin.'],
            ['question' => 'Proč se obnova ověřuje otiskem (např. SHA-256)?', 'options' => ['Otisk zrychlí obnovu.', 'Otisk zašifruje data.', 'Shodný otisk potvrdí, že obnovená data jsou stejná jako originál.'], 'correct' => 2, 'explanation' => 'Velikost se může shodovat i u poškozeného obsahu.'],
            ['question' => 'Co přesně udává cíl RTO u služby?', 'options' => ['Kolik dat smíme ztratit.', 'Za jak dlouho musí být služba po výpadku zase dostupná.', 'Kolik záloh uchováváme.'], 'correct' => 1, 'explanation' => 'Recovery Time Objective = cílová doba obnovy.'],
        ]],
        'homework' => [['text' => 'Volitelné: zjisti, jak často se zálohuje tvůj telefon nebo počítač, a odhadni své RPO.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Zálohu a mazání zkoušíme jen v simulátoru; skutečné obnovy se dělají do odděleného prostředí.', 'Zálohy osobních dat jsou citlivé – šifrují se a uchovávají jen po nutnou dobu (retence).'],
        'teacher_notes' => ['Častý omyl: obnova přímo přes originál – učte obnovu do jiného místa a pak porovnání.', 'Otázka do třídy: Kdy jste naposledy viděli, že záloha opravdu jde obnovit?', 'Tempo: časovač cvičení je důležitý – bez měření nejde spočítat skutečné RTO.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.', 'Plán B offline: kartičky kroků obnovy seřazené na lavici a výpočty RPO/RTO.'],
        'glossary' => ['rpo', 'rto'],
    ],
]]];
