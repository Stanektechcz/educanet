<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 3.A (operační systémy a sítě), lekce 17–22.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php.
 * Ověřované příkazy simulátoru (klíč `sim`): každý `cmd` běží v čistém světě Linux Labu (nginx běží), výstup musí obsahovat `expect`
 * – hlídá tools/v76_content_audit.php. Nic se nespouští doopravdy; adresy jen z laboratoře (10.0.0.x, intranet.skola.test).
 * Kompetence: katalog v62 „os_site“.
 */

return ['class_3a' => ['lessons' => [
    17 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 17 · Shell a cron: malá automatizace s logem',
        'goal' => [
            'student' => 'Na konci hodiny umím napsat krátký skript zálohy, který zapisuje do logu, končí srozumitelným návratovým kódem, a naplánovat ho v cronu se správným zápisem času.',
            'success_criteria' => ['Skript zapisuje začátek a konec práce do souboru logu.', 'Umím přečíst návratový kód ($?) a vysvětlím, co znamená 0 a nenulová hodnota.', 'Zapíšu cron výraz pro „každý den ve 2:30“ a vysvětlím všech pět polí.'],
        ],
        'competencies' => [['id' => 'lnx_automation', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: tichá záloha', 'teacher' => 'Popíše zálohu, která měsíc „běžela“, ale nic nezálohovala, protože nikdo neviděl chybu.', 'student' => 'Navrhnou, co by chybu odhalilo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Návratový kód', 'teacher' => 'Ukáže $? po úspěšném a neúspěšném příkazu.', 'student' => 'Vyzkoušejí návratové kódy v Linux Labu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Skript s logem', 'teacher' => 'Předvede zápis do logu přes >> a archiv tar s datem v názvu.', 'student' => 'Napíší skript zálohy s logem.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 62, 'phase' => 'Cron', 'teacher' => 'Rozebere /etc/crontab a pět polí času.', 'student' => 'Zapíší cron výrazy pro tři situace.', 'form' => 've dvojicích'],
            ['from' => 62, 'to' => 80, 'phase' => 'Bezpečné selhání', 'teacher' => 'Ukáže kontrolu existence adresáře před zálohou.', 'student' => 'Doplní do skriptu kontrolu a hlášku do logu.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: automatizace bez logu je slepá.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spusť jeden úspěšný a jeden neúspěšný příkaz a po každém vypiš návratový kód příkazem echo $?.', 'output' => 'Po false je kód 1, po ls neexistujícího adresáře 2, po úspěchu 0.', 'time' => '12 min',
                'sim' => [['cmd' => 'false; echo $?', 'expect' => '1'], ['cmd' => 'ls /neexistuje; echo $?', 'expect' => 'No such file or directory']]],
            ['text' => 'Vytvoř archiv adresáře data s datem v názvu a zapiš začátek a konec zálohy do souboru zaloha.log.', 'output' => 'Soubor zaloha-RRRR-MM-DD.tar.gz a log se dvěma řádky.', 'time' => '20 min',
                'sim' => [['cmd' => 'mkdir -p data; echo a > data/x.txt; tar -czf zaloha-$(date +%F).tar.gz data; ls', 'expect' => '.tar.gz'], ['cmd' => 'echo start >> zaloha.log; echo konec >> zaloha.log; cat zaloha.log', 'expect' => 'konec']]],
            ['text' => 'Přečti systémový /etc/crontab, vysvětli pět polí času a zapiš cron výrazy pro: každý den ve 2:30, každou neděli ve 23:00 a každých 15 minut.', 'output' => '30 2 * * *, 0 23 * * 0 a */15 * * * * s vysvětlením polí.', 'time' => '15 min',
                'sim' => [['cmd' => 'cat /etc/crontab', 'expect' => 'm h dom mon dow user'], ['cmd' => 'crontab -l', 'expect' => 'no crontab for student']]],
            ['text' => 'Doplň do skriptu kontrolu, že zdrojový adresář existuje; když chybí, zapiš do logu chybu a skonči nenulovým kódem.', 'output' => 'Řádek typu [ -d data ] || { echo "chybí data" >> zaloha.log; exit 1; } a ověření.', 'time' => '18 min',
                'sim' => [['cmd' => "[ -d /zaloha ] || echo 'chybi adresar'", 'expect' => 'chybi adresar']]],
        ],
        'differentiation' => [
            'support' => 'Dostane kostru skriptu s komentáři a tabulku polí cronu; doplňuje příkazy.',
            'standard' => 'Návratové kódy, skript s logem a archivem, cron výrazy a kontrola adresáře.',
            'challenge' => 'Navrhne rotaci záloh (ponechat posledních 7 archivů) a popíše, jak ji ověří.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: echo $? vypsalo 2 – uspěl příkaz?', 'Kartičky: učitel řekne čas, dvojice zapíše cron výraz.'],
            'rubric' => [
                ['criterion' => 'Návratový kód', 'levels' => ['Nezná.', 'Ví, že 0 je úspěch.', 'Čte $? a používá ho ve skriptu.', 'Vysvětlí, proč skript končí nenulovým kódem.']],
                ['criterion' => 'Skript s logem', 'levels' => ['Bez logu.', 'Log přepisuje (>).', 'Log připisuje (>>) začátek i konec.', 'Log s datem a výsledkem každého kroku.']],
                ['criterion' => 'Cron', 'levels' => ['Nezná pole.', 'Pole zná, výrazy s chybou.', 'Tři správné výrazy.', 'Vysvětlí, jak ověří, že úloha opravdu běží.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_automation', 'competence_label' => 'Linux: automatizace skriptem a cronem', 'variants' => [
            ['question' => 'Který cron výraz spustí úlohu každý den ve 2:30?', 'options' => ['2 30 * * *', '30 2 * * *', '* * 2 30 *'], 'correct' => 1, 'explanation' => 'Pořadí polí: minuta, hodina, den v měsíci, měsíc, den v týdnu.'],
            ['question' => 'Co znamená, když echo $? po příkazu vypíše 0?', 'options' => ['Předchozí příkaz skončil úspěšně.', 'Příkaz nic nevypsal.', 'Příkaz skončil chybou číslo 0.'], 'correct' => 0, 'explanation' => 'Návratový kód 0 = úspěch, nenulový = chyba.'],
            ['question' => 'Proč skript zálohy zapisuje do logu pomocí >> a ne >?', 'options' => ['>> je rychlejší.', '> nefunguje v cronu.', '>> připisuje na konec, > by log pokaždé přepsal.'], 'correct' => 2, 'explanation' => 'Historie logu zůstane zachovaná.'],
        ]],
        'homework' => [['text' => 'Volitelné: zapiš cron výraz pro úlohu každé pondělí v 7:15 a ověř ho v nápovědě crontab v Linux Labu.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Skripty a cron zkoušíme jen v simulátoru Linux Labu, ne na školních počítačích.', 'Do skriptů ani logů nepíšeme hesla; zálohujeme jen ukázková data.'],
        'teacher_notes' => ['Častý omyl: zaměněné pořadí minuty a hodiny v cronu – nech žáky číst pole nahlas.', 'Otázka do třídy: Jak poznáš ráno, že noční záloha opravdu proběhla?', 'Tempo: simulátor nepodporuje řídicí struktury ve skriptech – podmínku zapisuj jako [ … ] || …; if/else ukaž na tabuli.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 v Linux Labu podle zadání (výstupy jsou v zadání), úkol 4 na papír.', 'Plán B offline: tabulka polí cronu a kartičky s časy; skript zálohy napsaný do sešitu.'],
        'glossary' => ['shell', 'retention'],
    ],
    18 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 18 · Závěrečný incident: nefunguje služba – Linux i síť',
        'goal' => [
            'student' => 'Na konci hodiny umím vyřešit incident „web nejde“ postupem od důkazu k nápravě: stav služby, naslouchající port, konfigurace, oprava a ověření, a sepsat krátký záznam incidentu.',
            'success_criteria' => ['Každý krok podložím výstupem příkazu (systemctl, ss, curl).', 'Před restartem ověřím konfiguraci (nginx -t).', 'Záznam incidentu má příznak, příčinu, opravu, ověření a prevenci.'],
        ],
        'competencies' => [['id' => 'lnx_services', 'level' => 3], ['id' => 'net_diagnose', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Hlášení incidentu', 'teacher' => 'Přečte hlášení „intranet nejde“ a rozdá role ve dvojici (řešitel, zapisovatel).', 'student' => 'Zapíší příznak a čas.', 'form' => 've dvojicích'],
            ['from' => 10, 'to' => 30, 'phase' => 'Sběr důkazů', 'teacher' => 'Připomene pořadí: služba → port → konfigurace → aplikace.', 'student' => 'Zjistí stav služby a naslouchající porty.', 'form' => 've dvojicích'],
            ['from' => 30, 'to' => 50, 'phase' => 'Příčina a oprava', 'teacher' => 'Hlídá, aby oprava byla nejmenší možná.', 'student' => 'Ověří konfiguraci a službu spustí.', 'form' => 've dvojicích'],
            ['from' => 50, 'to' => 65, 'phase' => 'Ověření', 'teacher' => 'Vyžaduje ověření z pohledu uživatele (curl).', 'student' => 'Ověří odpověď webu a zkontrolují log.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Záznam incidentu', 'teacher' => 'Ukáže osnovu záznamu bez hledání viníka.', 'student' => 'Sepíší záznam incidentu.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne postup od důkazu k nápravě.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V simulátoru zastav nginx a jako řešitel zjisti stav služby a odpověď webu na localhost.', 'output' => 'systemctl is-active vrátí inactive a curl hlásí, že se nemůže připojit.', 'time' => '12 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx', 'expect' => 'inactive'], ['cmd' => 'sudo systemctl stop nginx; curl -I http://localhost', 'expect' => "Couldn't connect to server"]]],
            ['text' => 'Zjisti, které porty server poslouchá, a ověř konfiguraci webového serveru dřív, než ho spustíš.', 'output' => 'ss -tln ukáže porty 22 a 80, nginx -t hlásí syntax is ok.', 'time' => '12 min',
                'sim' => [['cmd' => 'ss -tln', 'expect' => '0.0.0.0:22'], ['cmd' => 'sudo nginx -t', 'expect' => 'syntax is ok']]],
            ['text' => 'Spusť službu a ověř opravu z pohledu uživatele i v logu služby.', 'output' => 'curl -I vrací HTTP/1.1 200 OK a journal obsahuje Started nginx.service.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; curl -I http://localhost', 'expect' => 'HTTP/1.1 200 OK'], ['cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; journalctl -u nginx -n 5', 'expect' => 'Started nginx.service']]],
            ['text' => 'Sepiš záznam incidentu: příznak, časová osa, příčina, oprava, ověření a jedno opatření prevence – bez jmen a hledání viníka.', 'output' => 'Záznam incidentu na půl strany.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu s pořadím příkazů (systemctl → ss → nginx -t → curl) a šablonu záznamu.',
            'standard' => 'Celý incident podle postupu a záznam.',
            'challenge' => 'Navrhne kontrolu (monitoring), která by výpadek ohlásila dřív než uživatel.',
        ],
        'assessment' => [
            'formative' => ['Zapisovatel ukáže, který příkaz dokládá každý krok.', 'Učitel se zeptá: proč nginx -t před startem?'],
            'rubric' => [
                ['criterion' => 'Důkazy', 'levels' => ['Hádá.', 'Jeden důkaz.', 'Každý krok podložený výstupem.', 'Důkazy seřazené od uživatele ke službě.']],
                ['criterion' => 'Oprava', 'levels' => ['Restartuje naslepo.', 'Restart bez kontroly konfigurace.', 'nginx -t, start a ověření.', 'Nejmenší oprava se zdůvodněním.']],
                ['criterion' => 'Záznam incidentu', 'levels' => ['Chybí.', 'Jen oprava.', 'Příznak, příčina, oprava, ověření, prevence.', 'Časová osa a měřitelné opatření.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'net_diagnose', 'competence_label' => 'Diagnostika síťového problému', 'variants' => [
            ['question' => 'Co uděláš před restartem webového serveru po změně konfigurace?', 'options' => ['Smažu log, ať je přehledný.', 'Restartuji celý server.', 'Ověřím konfiguraci příkazem nginx -t.'], 'correct' => 2, 'explanation' => 'Chybná konfigurace by službu po restartu shodila.'],
            ['question' => 'curl -I http://localhost hlásí „Couldn’t connect to server“. Co ověříš jako první?', 'options' => ['Jestli služba běží a poslouchá na portu 80.', 'Jestli funguje DNS na internetu.', 'Jestli má uživatel správné heslo.'], 'correct' => 0, 'explanation' => 'Spojení odmítnuto = nikdo neposlouchá.'],
            ['question' => 'Co do záznamu incidentu nepatří?', 'options' => ['Ověření opravy.', 'Jméno člověka, který chybu způsobil.', 'Opatření prevence.'], 'correct' => 1, 'explanation' => 'Záznam hledá příčinu v systému, ne viníka.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu vyřeš jednu úroveň s nefunkční službou a zapiš důkaz, který tě dovedl k příčině.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Služby zastavujeme a spouštíme jen v simulátoru Linux Labu.', 'Záznam incidentu neobsahuje jména, hesla ani skutečné adresy.'],
        'teacher_notes' => ['Častý omyl: restart jako první krok – nech žáky nejdřív zapsat důkaz.', 'Otázka do třídy: Který výstup by přesvědčil kolegu, že je opraveno?', 'Tempo: když incident trvá, záznam dokončete jako domácí dobrovolnou část, ověření ale v hodině.'],
        'substitution' => ['Zástup bez odborníka: dvojice plní úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 na papír.', 'Plán B offline: karty s výpisy systemctl, ss a curl; žáci podle nich sestaví postup a záznam.'],
        'glossary' => ['listener', 'runbook'],
    ],
    19 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 19 · Incident souborového systému: kde zmizelo místo?',
        'goal' => [
            'student' => 'Na konci hodiny umím zjistit zaplnění disku, najít největší adresáře a soubory a rozlišit, jestli problém způsobilo místo, nebo počet souborů (inody).',
            'success_criteria' => ['Z výpisu df -h přečtu zaplnění a přípojný bod oddílu.', 'Příkazy du a find najdu, co zabírá nejvíc místa v /var/log.', 'Vysvětlím, co je inode a proč může dojít, i když místo zbývá.'],
        ],
        'competencies' => [['id' => 'lnx_navigation', 'level' => 3], ['id' => 'lnx_services', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: „No space left on device“', 'teacher' => 'Ukáže hlášku a zeptá se, co všechno mohlo dojít.', 'student' => 'Navrhnou hypotézy.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'df a lsblk', 'teacher' => 'Vysvětlí oddíly, přípojné body (mount point) a sloupce df.', 'student' => 'Přečtou zaplnění a najdou, kde je připojený kořen.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'du a find', 'teacher' => 'Předvede hledání velkých adresářů a souborů.', 'student' => 'Najdou největší položky v /var/log.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 60, 'phase' => 'Inody', 'teacher' => 'Vysvětlí inode a ukáže ho ve výpisu stat.', 'student' => 'Najdou číslo inode souboru a popíší, kdy dojdou.', 'form' => 've dvojicích'],
            ['from' => 60, 'to' => 80, 'phase' => 'Bezpečný úklid', 'teacher' => 'Ukáže, proč se nemaže log služby, která do něj právě píše.', 'student' => 'Navrhnou bezpečný úklid a retenci logů.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne postup: df → du → find → bezpečný úklid.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Přečti zaplnění disků příkazem df -h a v lsblk najdi, který oddíl je připojený jako kořen /.', 'output' => '/dev/sda1 připojený na /, zaplnění v procentech a volné místo.', 'time' => '12 min',
                'sim' => [['cmd' => 'df -h', 'expect' => '/dev/sda1'], ['cmd' => 'lsblk', 'expect' => 'sda1']]],
            ['text' => 'Zjisti velikost položek v /var/log a najdi všechny soubory s příponou .log.', 'output' => 'du ukáže /var/log/nginx jako největší; find vypíše čtyři soubory .log.', 'time' => '18 min',
                'sim' => [['cmd' => 'du -sh /var/log/*', 'expect' => '/var/log/nginx'], ['cmd' => "find /var/log -type f -name '*.log'", 'expect' => '/var/log/nginx/error.log']]],
            ['text' => 'Najdi ve výpisu stat číslo inode souboru /etc/hosts a vysvětli, co by se stalo, kdyby inody došly.', 'output' => 'Řádek Inode: 120501 a vysvětlení: nový soubor nejde vytvořit, i když místo zbývá.', 'time' => '13 min',
                'sim' => [['cmd' => 'stat /etc/hosts', 'expect' => 'Inode: 120501']]],
            ['text' => 'Navrhni bezpečný postup úklidu plného /var/log: co smažeš, co zkomprimuješ, jak nastavíš retenci a jak ověříš, že služba dál zapisuje.', 'output' => 'Postup ve 4–5 krocích s ověřením.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane popsaný výpis df -h se zvýrazněnými sloupci a pořadí příkazů.',
            'standard' => 'Zaplnění, hledání velkých položek, inody a návrh úklidu.',
            'challenge' => 'Vysvětlí, proč po smazání otevřeného logu místo nepřibude, dokud služba soubor nezavře.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže řádek df a třída řekne zaplnění a přípojný bod.', 'Dvojice: místo, nebo inody? (učitel popisuje situace)'],
            'rubric' => [
                ['criterion' => 'Zaplnění disku', 'levels' => ['Nečte df.', 'Přečte procenta.', 'Oddíl, přípojný bod, zaplnění.', 'Spojí s lsblk a odhadne růst.']],
                ['criterion' => 'Hledání příčiny', 'levels' => ['Nehledá.', 'Jen du.', 'du i find s filtrem.', 'Najde příčinu i dobu vzniku.']],
                ['criterion' => 'Bezpečný úklid', 'levels' => ['Smaže vše.', 'Maže bez ověření.', 'Komprese, retence a ověření.', 'Navrhne prevenci (rotace, upozornění).']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_navigation', 'competence_label' => 'Linux: shell a práce se soubory', 'variants' => [
            ['question' => 'Disk hlásí „No space left on device“, ale df -h ukazuje 40 % volného místa. Co ověříš?', 'options' => ['Rychlost disku.', 'Jestli nedošly inody (df -i).', 'Jestli je zapnutý firewall.'], 'correct' => 1, 'explanation' => 'Každý soubor potřebuje inode; může jich dojít dřív než místa.'],
            ['question' => 'Kterým příkazem zjistíš, kolik místa zabírají jednotlivé položky v /var/log?', 'options' => ['du -sh /var/log/*', 'ls /var/log', 'pwd /var/log'], 'correct' => 0, 'explanation' => 'du sčítá velikost obsahu adresářů.'],
            ['question' => 'Proč nemazat log, do kterého služba právě zapisuje?', 'options' => ['Protože log je vždy prázdný.', 'Protože to zakazuje df.', 'Protože místo se uvolní až po zavření souboru službou a ztratíš historii.'], 'correct' => 2, 'explanation' => 'Bezpečnější je komprese a rotace logů.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu najdi příkazem find všechny soubory větší než 1 kB v /var/log a zapiš, co v nich je.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Úklid a mazání zkoušíme jen v simulátoru; na skutečném systému se nejdřív zálohuje.', 'Logy mohou obsahovat osobní údaje – nesdílíme je mimo hodinu.'],
        'teacher_notes' => ['Častý omyl: rm -rf /var/log/* jako „oprava“ – zdůrazni ztrátu důkazů i historie.', 'Otázka do třídy: Jak se dozvíte o plném disku dřív, než ho nahlásí uživatel?', 'Tempo: inody stačí vysvětlit na jednom výpisu stat; víc času dej návrhu úklidu.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), úkol 4 písemně.', 'Plán B offline: vytištěné výpisy df, du a stat s otázkami.'],
        'glossary' => ['inode', 'mount-point'],
    ],
    20 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 20 · Služby a jejich závislosti v systemd',
        'goal' => [
            'student' => 'Na konci hodiny umím vypsat služby systému, zjistit, které selhaly, rozlišit stav „běží“ a „povolena po startu“ a vysvětlit, proč služba závisí na síti nebo jiné službě.',
            'success_criteria' => ['Z výpisu list-units najdu běžící služby a v --failed selhané.', 'Rozliším is-active (běží teď) a is-enabled (spustí se po startu).', 'Ke službě nginx popíšu, na čem závisí (síť, konfigurace, port).'],
        ],
        'competencies' => [['id' => 'lnx_services', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: po restartu nejede', 'teacher' => 'Popíše situaci: web běžel, po restartu serveru ne.', 'student' => 'Hádají, co se změnilo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Seznam služeb', 'teacher' => 'Předvede systemctl list-units a --failed.', 'student' => 'Vypíší služby a zjistí, která selhala.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Active vs. enabled', 'teacher' => 'Vysvětlí rozdíl mezi stavem teď a po startu.', 'student' => 'Ověří is-active a is-enabled u nginx a ssh.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 65, 'phase' => 'Závislosti', 'teacher' => 'Nakreslí graf: síť → nginx → web; ssh → správa.', 'student' => 'Nakreslí závislosti služeb svého serveru.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 80, 'phase' => 'Scénář po restartu', 'teacher' => 'Zadá scénář: služba je active, ale disabled.', 'student' => 'Navrhnou opravu a ověření.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: běží ≠ spustí se.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vypiš služby systému a zjisti, jestli některá selhala.', 'output' => 'list-units ukáže cron, nginx, ssh a journald; --failed hlásí 0 loaded units listed.', 'time' => '12 min',
                'sim' => [['cmd' => 'systemctl list-units --type=service', 'expect' => 'nginx.service'], ['cmd' => 'systemctl --failed', 'expect' => '0 loaded units listed']]],
            ['text' => 'Ověř u nginx, jestli běží a jestli se spustí po startu; potom službu zastav a stav ověř znovu.', 'output' => 'is-enabled = enabled; po stop je is-active inactive, ale enabled zůstává.', 'time' => '15 min',
                'sim' => [['cmd' => 'systemctl is-enabled nginx', 'expect' => 'enabled'], ['cmd' => 'sudo systemctl stop nginx; systemctl is-active nginx', 'expect' => 'inactive']]],
            ['text' => 'Přečti stav služby ssh a najdi v něm hlavní proces (Main PID) a řádek, který říká, že je povolená po startu.', 'output' => 'Main PID: 180 (sshd) a Loaded: … enabled.', 'time' => '10 min',
                'sim' => [['cmd' => 'systemctl status ssh', 'expect' => 'Main PID: 180']]],
            ['text' => 'Nakresli graf závislostí služeb serveru (síť, nginx, ssh, cron, journald) a popiš scénář „po restartu web nejede“ s opravou a ověřením.', 'output' => 'Graf závislostí a postup: is-enabled → enable → restart serveru → ověření.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku stavů (active/inactive, enabled/disabled) s příklady.',
            'standard' => 'Seznam služeb, stavy, status a graf závislostí.',
            'challenge' => 'Vysvětlí, co je drop-in soubor a proč se úprava jednotky dělá raději v něm než v originálu.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže dvojici stavů (active + disabled), třída řekne, co se stane po restartu.', 'Dvojice: kde ve výpisu status je informace o povolení?'],
            'rubric' => [
                ['criterion' => 'Přehled služeb', 'levels' => ['Nevypíše.', 'Vypíše bez porozumění.', 'Najde běžící i selhané služby.', 'Pozná neobvyklou službu.']],
                ['criterion' => 'Stav služby', 'levels' => ['Zaměňuje.', 'Zná jen active.', 'Rozliší active a enabled.', 'Navrhne ověření po restartu.']],
                ['criterion' => 'Závislosti', 'levels' => ['Chybí.', 'Seznam bez vazeb.', 'Graf s vazbami.', 'Odvodí z grafu pořadí kontroly.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_services', 'competence_label' => 'Linux: služby a systémové logy', 'variants' => [
            ['question' => 'Služba je active, ale disabled. Co se stane po restartu serveru?', 'options' => ['Spustí se dvakrát.', 'Nespustí se sama.', 'Smaže se.'], 'correct' => 1, 'explanation' => 'disabled = po startu se automaticky nespustí.'],
            ['question' => 'Který příkaz ukáže služby, které selhaly?', 'options' => ['systemctl --failed', 'ls /failed', 'ps aux --failed'], 'correct' => 0, 'explanation' => 'systemctl --failed vypíše jednotky ve stavu failed.'],
            ['question' => 'Co znamená stav „enabled“ u služby?', 'options' => ['Služba právě běží.', 'Služba má chybu.', 'Služba se spustí automaticky při startu systému.'], 'correct' => 2, 'explanation' => 'enabled se týká startu, ne aktuálního běhu.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu zjisti stav služby cron (běží? povolená?) a zapiš oba výstupy.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Služby měníme jen v simulátoru Linux Labu.', 'Na skutečném serveru se službám mění stav jen v domluveném okně změn.'],
        'teacher_notes' => ['Častý omyl: „běží, tak je to v pořádku“ – po restartu může služba chybět, když není enabled.', 'Otázka do třídy: Jak ověříte chování po restartu bez skutečného restartu produkce?', 'Tempo: drop-in je jen pro výzvu; standard je active vs. enabled a graf.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), graf na papír.', 'Plán B offline: kartičky služeb a šipky závislostí na lavici.'],
        'glossary' => ['systemd-unit', 'drop-in'],
    ],
    21 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 21 · Forenzní čtení logů: co se stalo a kdy',
        'goal' => [
            'student' => 'Na konci hodiny umím z logů služby a přístupového logu webu sestavit časovou osu události, spočítat odpovědi podle stavových kódů a oddělit fakt od domněnky.',
            'success_criteria' => ['Z journalctl přečtu, kdy se služba zastavila a znovu spustila.', 'Z přístupového logu spočítám odpovědi podle stavového kódu (200, 403, 404).', 'Časová osa obsahuje jen fakta s časem a zdrojem.'],
        ],
        'competencies' => [['id' => 'lnx_services', 'level' => 3], ['id' => 'lnx_text', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: kdo za to může?', 'teacher' => 'Přečte stížnost „web byl ráno pomalý a hlásil chyby“.', 'student' => 'Zapíší, jaké důkazy by potřebovali.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 28, 'phase' => 'Journal služby', 'teacher' => 'Předvede journalctl -u a -n.', 'student' => 'Najdou zastavení a start služby.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 48, 'phase' => 'Přístupový log', 'teacher' => 'Rozebere řádek access logu a pole stavového kódu.', 'student' => 'Spočítají odpovědi podle kódů.', 'form' => 've dvojicích'],
            ['from' => 48, 'to' => 62, 'phase' => 'Podezřelé požadavky', 'teacher' => 'Ukáže filtr na 403 a opakované adresy.', 'student' => 'Najdou požadavky na /admin.', 'form' => 've dvojicích'],
            ['from' => 62, 'to' => 82, 'phase' => 'Časová osa', 'teacher' => 'Ukáže sloupce čas – zdroj – fakt.', 'student' => 'Sestaví časovou osu a oddělí domněnky.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: fakt má čas a zdroj.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zastav v simulátoru nginx a v journalu služby najdi záznamy o zastavení.', 'output' => 'Řádky Stopping a Stopped nginx.service s časem.', 'time' => '13 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; journalctl -u nginx -n 5', 'expect' => 'Stopped nginx.service']]],
            ['text' => 'Spočítej odpovědi webu podle stavového kódu z přístupového logu (pole 9) pomocí awk, sort a uniq -c.', 'output' => '15× 200, 6× 403, 9× 404.', 'time' => '18 min',
                'sim' => [['cmd' => "sudo awk '{print \$9}' /var/log/nginx/access.log | sort | uniq -c", 'expect' => '9 404']]],
            ['text' => 'Vypiš první dva požadavky, na které server odpověděl 403, a zapiš, na jakou stránku mířily.', 'output' => 'Požadavky GET /admin z adres 10.0.0.23 a 10.0.0.44.', 'time' => '12 min',
                'sim' => [['cmd' => "sudo grep ' 403 ' /var/log/nginx/access.log | head -n 2", 'expect' => 'GET /admin']]],
            ['text' => 'Sestav časovou osu ve sloupcích čas – zdroj – fakt a pod ni zvlášť napiš domněnky, které je třeba ověřit.', 'output' => 'Časová osa s aspoň 5 fakty a seznam 2 domněnek.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane rozebraný řádek access logu s očíslovanými poli a hotový příkaz pro počítání kódů.',
            'standard' => 'Journal, počty kódů, filtr 403 a časová osa.',
            'challenge' => 'Najde, ze které adresy přišlo nejvíc chybových odpovědí (awk + sort + uniq -c).',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže řádek logu, třída řekne čas, adresu, stránku a kód.', 'Dvojice: je tvrzení fakt, nebo domněnka?'],
            'rubric' => [
                ['criterion' => 'Čtení journalu', 'levels' => ['Nečte.', 'Najde jen poslední řádek.', 'Najde zastavení i start s časem.', 'Spojí s jinými logy.']],
                ['criterion' => 'Analýza access logu', 'levels' => ['Nečte.', 'Ruční počítání.', 'awk + sort + uniq -c.', 'Rozdělení podle adres i stránek.']],
                ['criterion' => 'Časová osa', 'levels' => ['Chybí.', 'Fakta bez zdroje.', 'Fakta s časem a zdrojem, domněnky zvlášť.', 'Domněnky s návrhem ověření.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_text', 'competence_label' => 'Linux: práce s textem v příkazové řádce', 'variants' => [
            ['question' => 'Co dělá roura awk "{print $9}" log | sort | uniq -c?', 'options' => ['Smaže devátý řádek logu.', 'Seřadí log podle času.', 'Vypíše 9. pole každého řádku a spočítá, kolikrát se která hodnota opakuje.'], 'correct' => 2, 'explanation' => 'uniq -c počítá stejné sousední řádky, proto se nejdřív řadí.'],
            ['question' => 'Které tvrzení je fakt z logu?', 'options' => ['V 08:23:55 přišel z 10.0.0.23 požadavek GET /admin s odpovědí 403.', 'Někdo se pokoušel web hacknout.', 'Server je pomalý kvůli útoku.'], 'correct' => 0, 'explanation' => 'Fakt má čas, zdroj a obsah; zbytek jsou domněnky.'],
            ['question' => 'Jak zobrazíš posledních 5 záznamů journalu služby nginx?', 'options' => ['tail nginx', 'journalctl -u nginx -n 5', 'cat /nginx/5'], 'correct' => 1, 'explanation' => '-u vybere jednotku, -n počet řádků.'],
        ]],
        'homework' => [['text' => 'Volitelné: v Linux Labu zjisti, kolik požadavků v access logu skončilo kódem 404 a na které stránky mířily.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Logy jsou ze simulátoru; adresy 10.0.0.x jsou laboratorní.', 'Skutečné logy mohou obsahovat osobní údaje – čte je jen oprávněná osoba a nesdílí je.'],
        'teacher_notes' => ['Častý omyl: závěr „útok“ z jednoho 403 – veď k oddělení faktů a domněnek.', 'Otázka do třídy: Co bychom ještě potřebovali vědět, abychom domněnku potvrdili?', 'Tempo: časová osa je výstup hodiny; když awk trvá, dej hotový příkaz a soustřeď se na výklad.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), časová osa na papír.', 'Plán B offline: vytištěný výřez logu a ruční sestavení časové osy.'],
        'glossary' => ['journal', 'severity'],
    ],
    22 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 22 · SSH klíče v praxi: vytvořit, chránit, ověřit',
        'goal' => [
            'student' => 'Na konci hodiny umím vytvořit pár klíčů ed25519, zkontrolovat práva soukromého klíče, vysvětlit roli souborů authorized_keys a known_hosts a ověřit, že server nepovoluje přihlášení roota.',
            'success_criteria' => ['Soukromý klíč má práva 600 a nikdy ho nikomu neposílám.', 'Rozliším authorized_keys (na serveru, veřejné klíče) a known_hosts (u klienta, otisky serverů).', 'V konfiguraci SSH najdu PermitRootLogin a vysvětlím jeho hodnotu.'],
        ],
        'competencies' => [['id' => 'lnx_ssh', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: heslo, nebo klíč?', 'teacher' => 'Porovná přihlášení heslem a klíčem z pohledu útočníka.', 'student' => 'Vyjmenují rizika hesel.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 28, 'phase' => 'Vytvoření klíče', 'teacher' => 'Předvede ssh-keygen -t ed25519.', 'student' => 'Vytvoří pár klíčů a prohlédnou si veřejnou část.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 45, 'phase' => 'Práva a ochrana', 'teacher' => 'Vysvětlí práva 600 a heslo ke klíči (passphrase).', 'student' => 'Ověří práva souborů v ~/.ssh.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 62, 'phase' => 'authorized_keys a known_hosts', 'teacher' => 'Nakreslí, kde který soubor leží a co obsahuje.', 'student' => 'Doplní schéma klient–server.', 'form' => 've dvojicích'],
            ['from' => 62, 'to' => 80, 'phase' => 'Konfigurace serveru', 'teacher' => 'Ukáže sshd_config a port 22.', 'student' => 'Ověří dostupnost SSH a nastavení PermitRootLogin.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: soukromý klíč zůstává u tebe.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vytvoř pár klíčů ed25519 a vypiš veřejnou část klíče.', 'output' => 'Soubory id_ed25519 a id_ed25519.pub; veřejný klíč začíná ssh-ed25519.', 'time' => '13 min',
                'sim' => [['cmd' => "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ''; cat ~/.ssh/id_ed25519.pub", 'expect' => 'ssh-ed25519 AAAA']]],
            ['text' => 'Ověř práva souborů v ~/.ssh a vysvětli, proč soukromý klíč smí číst jen vlastník.', 'output' => 'id_ed25519 má -rw------- (600), veřejný klíč -rw-r--r-- (644).', 'time' => '12 min',
                'sim' => [['cmd' => "ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ''; ls -l ~/.ssh", 'expect' => '-rw------- 1 student student']]],
            ['text' => 'Nakresli schéma klient–server a doplň, kde leží soukromý klíč, veřejný klíč v authorized_keys a otisk serveru v known_hosts.', 'output' => 'Schéma se třemi soubory a šipkou, co se kam kopíruje.', 'time' => '15 min'],
            ['text' => 'Ověř, že na serveru 10.0.0.10 poslouchá SSH, a v konfiguraci najdi nastavení přihlášení roota.', 'output' => 'nc hlásí succeeded na portu 22, sshd_config obsahuje PermitRootLogin no.', 'time' => '15 min',
                'sim' => [['cmd' => 'nc -zv 10.0.0.10 22', 'expect' => 'succeeded'], ['cmd' => 'sudo grep -n PermitRootLogin /etc/ssh/sshd_config', 'expect' => 'PermitRootLogin no']]],
        ],
        'differentiation' => [
            'support' => 'Dostane schéma s prázdnými políčky a tabulku práv 600/644.',
            'standard' => 'Klíče, práva, schéma a kontrola konfigurace.',
            'challenge' => 'Vysvětlí, co se stane při změně otisku serveru v known_hosts a jak postupovat bezpečně.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: který soubor smíš poslat správci serveru?', 'Dvojice: ukaž ve výpisu ls -l, kdo smí číst soukromý klíč.'],
            'rubric' => [
                ['criterion' => 'Vytvoření klíče', 'levels' => ['Nezvládne.', 'Klíč bez porozumění.', 'Pár klíčů a rozlišení částí.', 'Vysvětlí volbu ed25519 a passphrase.']],
                ['criterion' => 'Ochrana klíče', 'levels' => ['Posílá soukromý klíč.', 'Práva nezkontroluje.', 'Práva 600 ověřená.', 'Navrhne postup při úniku klíče.']],
                ['criterion' => 'Konfigurace serveru', 'levels' => ['Nezná.', 'Najde port.', 'Port i PermitRootLogin s vysvětlením.', 'Navrhne další zpevnění.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'lnx_ssh', 'competence_label' => 'Linux: SSH a klíče', 'variants' => [
            ['question' => 'Který soubor pošleš správci serveru, aby ti povolil přihlášení klíčem?', 'options' => ['id_ed25519', 'known_hosts', 'id_ed25519.pub'], 'correct' => 2, 'explanation' => 'Posílá se jen veřejná část; soukromý klíč zůstává u tebe.'],
            ['question' => 'Jaká práva má mít soukromý klíč?', 'options' => ['600 – číst a zapisovat jen vlastník.', '777 – všichni všechno.', '644 – číst může každý.'], 'correct' => 0, 'explanation' => 'Soukromý klíč nesmí číst nikdo jiný.'],
            ['question' => 'Co obsahuje soubor known_hosts u klienta?', 'options' => ['Hesla k serverům.', 'Otisky serverů, ke kterým ses už připojil(a).', 'Veřejné klíče uživatelů serveru.'], 'correct' => 1, 'explanation' => 'Chrání před podvrženým serverem.'],
        ]],
        'homework' => [['text' => 'Volitelné: zapiš si postup, co uděláš, když ti unikne soukromý klíč (tři kroky).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Klíče vytváříme jen v simulátoru; skutečné soukromé klíče nikdy nevkládáme do dokumentů, chatů ani AI nástrojů.', 'Ke školním serverům se připojujeme jen s povolením správce.'],
        'teacher_notes' => ['Častý omyl: žák pošle soubor bez přípony .pub – zdůrazni, že soukromý klíč neopouští počítač.', 'Otázka do třídy: Proč je přihlášení roota přes SSH zakázané?', 'Tempo: navazuje na práva z L11 a SSH z L14 – připomeň je rychle, neopakuj.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 2 a 4 v Linux Labu (výstupy v zadání), schéma na papír.', 'Plán B offline: schéma klient–server s kartičkami „soukromý / veřejný / otisk“.'],
        'glossary' => ['authorized-keys', 'known-hosts'],
    ],
]]];
