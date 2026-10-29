<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 4.A (provoz sítí a služeb), lekce 23–28.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php,
 * ověřované příkazy simulátoru viz lesson_content_v76_3a_a.php. Přepis šablonových lekcí lessons_v30.php.
 * Kompetence: návrh katalogu v75 „provoz_site“.
 */

return ['class_4a' => ['lessons' => [
    23 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 23 · Audit zpevnění serveru: přístup, porty a práva',
        'goal' => [
            'student' => 'Na konci hodiny umím provést audit zpevnění serveru – konfigurace SSH, naslouchající služby, práva citlivých souborů a členství ve skupině sudo – a navrhnout opatření bez rizika, že se správce sám zamkne.',
            'success_criteria' => ['Ke každé kontrole mám příkaz, výstup a hodnocení (v pořádku / riziko).', 'Každá naslouchající služba má důvod, jinak ji navrhnu vypnout (menší útočná plocha, attack surface).', 'Před změnou SSH mám otevřené druhé připojení a plán návratu.'],
        ],
        'competencies' => [['id' => 'ops_hardening', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: zamčený správce', 'teacher' => 'Popíše správce, který zakázal hesla dřív, než nahrál klíč, a zamkl se.', 'student' => 'Navrhnou, jak tomu předejít.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Kontrolní seznam', 'teacher' => 'Představí oblasti auditu: přístup, služby, práva, účty.', 'student' => 'Ke každé oblasti zapíší jednu kontrolu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 50, 'phase' => 'Audit v simulátoru', 'teacher' => 'Hlídá, aby každá kontrola měla důkaz.', 'student' => 'Provedou kontroly a výstupy zapíší.', 'form' => 've dvojicích'],
            ['from' => 50, 'to' => 65, 'phase' => 'Hodnocení rizik', 'teacher' => 'Vysvětlí útočnou plochu a nejmenší práva.', 'student' => 'Ohodnotí nálezy a seřadí je.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Bezpečný plán změn', 'teacher' => 'Ukáže postup bez zamčení: druhé připojení, test, návrat.', 'student' => 'Napíší plán dvou změn s návratem.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: zpevňuj s plánem návratu.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Přečti konfiguraci SSH serveru a ohodnoť každé nastavení (port, přihlášení roota, přesměrování X11, SFTP).', 'output' => 'PermitRootLogin no a X11Forwarding no jsou v pořádku; chybí výslovné zakázání přihlášení heslem.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo cat /etc/ssh/sshd_config', 'expect' => 'X11Forwarding no']]],
            ['text' => 'Zjisti naslouchající služby a práva konfiguračního souboru SSH; ke každé službě napiš, proč na serveru běží.', 'output' => 'Porty 22 a 80; sshd_config -rw-r--r-- root root (zapisuje jen root).', 'time' => '12 min',
                'sim' => [['cmd' => 'ss -tln', 'expect' => '0.0.0.0:80'], ['cmd' => 'ls -l /etc/ssh/sshd_config', 'expect' => '-rw-r--r-- 1 root root']]],
            ['text' => 'Ověř, kteří uživatelé mají práva správce přes skupinu sudo, a vysvětli princip nejmenších práv.', 'output' => 'Uživatel student je ve skupině 27(sudo) – oprávnění má mít jen ten, kdo server spravuje.', 'time' => '10 min',
                'sim' => [['cmd' => 'id student', 'expect' => '27(sudo)']]],
            ['text' => 'Napiš plán dvou změn zpevnění (např. zakázat přihlášení heslem, omezit SSH na síť správců) s ověřením a plánem návratu, aby ses nezamkl(a).', 'output' => 'Plán: druhé připojení otevřené → změna → sshd -t → reload → test nového připojení → případný návrat.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam s příkazy a tabulku hodnocení.',
            'standard' => 'Audit čtyř oblastí, hodnocení a plán dvou změn.',
            'challenge' => 'Navrhne ochranu proti hádání hesel (omezení pokusů, lockout) a popíše riziko zablokování legitimních uživatelů.',
        ],
        'assessment' => [
            'formative' => ['Učitel přečte nastavení, třída zvedne kartu „v pořádku / riziko“.', 'Dvojice: kde v plánu změny je krok návratu?'],
            'rubric' => [
                ['criterion' => 'Audit', 'levels' => ['Bez důkazů.', '1–2 oblasti.', 'Čtyři oblasti s výstupem.', 'Nálezy s odkazem na princip.']],
                ['criterion' => 'Hodnocení rizik', 'levels' => ['Chybí.', 'Bez pořadí.', 'Seřazeno podle dopadu.', 'Zdůvodněno útočnou plochou.']],
                ['criterion' => 'Plán změn', 'levels' => ['Chybí.', 'Bez návratu.', 'Ověření a plán návratu.', 'Bez rizika zamčení, s testem konfigurace.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_hardening', 'competence_label' => 'Zabezpečení služby', 'variants' => [
            ['question' => 'Co uděláš před zakázáním přihlášení heslem přes SSH?', 'options' => ['Restartuji server.', 'Ověřím, že funguje přihlášení klíčem, a nechám otevřené druhé připojení.', 'Smažu všechny klíče.'], 'correct' => 1, 'explanation' => 'Jinak se můžeš zamknout.'],
            ['question' => 'Co znamená „zmenšit útočnou plochu“?', 'options' => ['Vypnout služby a otevřené porty, které server nepotřebuje.', 'Zmenšit disk serveru.', 'Používat kratší hesla.'], 'correct' => 0, 'explanation' => 'Co neběží, nejde napadnout.'],
            ['question' => 'Proč nemá být každý uživatel ve skupině sudo?', 'options' => ['Protože sudo je pomalé.', 'Protože skupina má limit členů.', 'Kvůli principu nejmenších práv – správcovská oprávnění jen pro toho, kdo je potřebuje.'], 'correct' => 2, 'explanation' => 'Méně oprávnění = menší škoda při zneužití účtu.'],
        ]],
        'homework' => [['text' => 'Volitelné: zkontroluj na svém telefonu, které aplikace mají přístup k poloze, a odeber jednu nepotřebnou.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Audit provádíme jen v simulátoru; audit cizího serveru bez povolení je zakázaný.', 'Výsledky auditu jsou citlivé – nesdílíme je mimo hodinu.'],
        'teacher_notes' => ['Častý omyl: zpevnění bez plánu návratu – připomeň zamčeného správce z úvodu.', 'Otázka do třídy: Která služba na vašem serveru nemusí poslouchat na všech rozhraních?', 'Tempo: plán změn je hlavní výstup; audit drž v časovém limitu.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 v Linux Labu (výstupy v zadání), plán na papír.', 'Plán B offline: vytištěná konfigurace a výpisy s kontrolním seznamem.'],
        'glossary' => ['attack-surface', 'lockout'],
    ],
    24 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 24 · Bezpečná změna a rollback: plán návratu předem',
        'goal' => [
            'student' => 'Na konci hodiny umím provést změnu konfigurace služby bezpečně: záloha, změna, test konfigurace, ověření – a když test selže, vrátit předchozí stav (rollback) a ověřit ho.',
            'success_criteria' => ['Před změnou zazálohuji konfiguraci a zapíšu kritéria úspěchu.', 'Změnu ověřím testem konfigurace a porovnáním (diff) se zálohou.', 'Při selhání provedu rollback a doložím, že služba je ve výchozím stavu.'],
        ],
        'competencies' => [['id' => 'ops_change', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: páteční změna', 'teacher' => 'Popíše změnu bez zálohy, která shodila web na celý víkend.', 'student' => 'Vyjmenují chybějící kroky.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Plán změny', 'teacher' => 'Ukáže šablonu: co, proč, kritéria úspěchu, rollback, okno.', 'student' => 'Vyplní plán změny portu webu.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 45, 'phase' => 'Změna s ověřením', 'teacher' => 'Předvede zálohu, změnu a diff.', 'student' => 'Provedou změnu a porovnají ji se zálohou.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 65, 'phase' => 'Chybná změna a rollback', 'teacher' => 'Zadá změnu s překlepem v konfiguraci.', 'student' => 'Test selže, provedou rollback a ověří ho.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Postupné nasazení', 'teacher' => 'Vysvětlí postupné nasazení (canary) a podmínku zastavení.', 'student' => 'Doplní do plánu kritérium zastavení.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: rollback se plánuje před změnou.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vyplň plán změny „web na portu 8080“: co, proč, kritéria úspěchu, postup rollbacku a časové okno.', 'output' => 'Plán změny na půl strany s měřitelným kritériem úspěchu.', 'time' => '12 min'],
            ['text' => 'Zazálohuj konfiguraci webu, změň port 80 na 8080 a porovnej změnu se zálohou příkazem diff.', 'output' => 'diff ukáže řádek listen 80 → listen 8080.', 'time' => '18 min',
                'sim' => [['cmd' => "sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i 's/listen 80 default_server/listen 8080 default_server/' /etc/nginx/sites-enabled/default; diff /tmp/default.bak /etc/nginx/sites-enabled/default", 'expect' => 'listen 8080 default_server']]],
            ['text' => 'Proveď chybnou změnu (překlep v direktivě), spusť test konfigurace a po selhání vrať zálohu a test zopakuj.', 'output' => 'Test hlásí unknown directive a test failed; po rollbacku syntax is ok.', 'time' => '20 min',
                'sim' => [['cmd' => "sudo sed -i 's/try_files/try_filez/' /etc/nginx/sites-enabled/default; sudo nginx -t", 'expect' => 'test failed'], ['cmd' => "sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i 's/try_files/try_filez/' /etc/nginx/sites-enabled/default; sudo cp /tmp/default.bak /etc/nginx/sites-enabled/default; sudo nginx -t", 'expect' => 'syntax is ok']]],
            ['text' => 'Doplň do plánu postupné nasazení na 10 % provozu s podmínkou zastavení (např. podíl chyb nad 2 % po 10 minutách) a kdo rozhoduje o rollbacku.', 'output' => 'Kroky nasazení s měřitelnou podmínkou zastavení a odpovědnou rolí.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu plánu s vyplněným příkladem a kartu příkazů.',
            'standard' => 'Plán, změna s diff, chybná změna s rollbackem a postupné nasazení.',
            'challenge' => 'Navrhne, jak změnu automatizovat tak, aby test konfigurace a rollback proběhly bez ručního zásahu.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže plán bez rollbacku, třída doplní chybějící krok.', 'Dvojice: ukaž důkaz, že rollback vrátil výchozí stav.'],
            'rubric' => [
                ['criterion' => 'Plán změny', 'levels' => ['Chybí.', 'Bez kritérií.', 'Kritéria úspěchu a rollback.', 'Podmínka zastavení a role.']],
                ['criterion' => 'Provedení', 'levels' => ['Bez zálohy.', 'Záloha bez ověření.', 'Záloha, diff, test konfigurace.', 'Ověření i z pohledu uživatele.']],
                ['criterion' => 'Rollback', 'levels' => ['Neumí.', 'Bez ověření.', 'Rollback ověřený testem.', 'Rollback v časovém limitu okna.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_change', 'competence_label' => 'Řízená změna s plánem návratu', 'variants' => [
            ['question' => 'Kdy se má připravit postup rollbacku?', 'options' => ['Až když změna selže.', 'Před provedením změny jako součást plánu.', 'Nikdy, změny se nevracejí.'], 'correct' => 1, 'explanation' => 'V krizi není čas vymýšlet návrat.'],
            ['question' => 'nginx -t hlásí „test failed“ po tvé změně. Co uděláš?', 'options' => ['Vrátím zálohu konfigurace a test zopakuji.', 'Restartuji službu, třeba to projde.', 'Smažu konfiguraci.'], 'correct' => 0, 'explanation' => 'Chybnou konfiguraci nenasazujeme; vracíme ověřený stav.'],
            ['question' => 'Co je postupné (kanárkové) nasazení?', 'options' => ['Nasazení jen v noci.', 'Nasazení bez testů.', 'Nová verze nejdřív pro malou část provozu s podmínkou zastavení.'], 'correct' => 2, 'explanation' => 'Chyba zasáhne jen malou část uživatelů.'],
        ]],
        'homework' => [['text' => 'Volitelné: napiš plán změny pro aktualizaci aplikace v telefonu rodiče (záloha, kritérium úspěchu, návrat).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Konfiguraci měníme jen v simulátoru Linux Labu.', 'Na skutečném serveru se změny dělají v domluveném okně a se schváleným plánem.'],
        'teacher_notes' => ['Častý omyl: restart po neúspěšném testu „pro jistotu“ – shodí službu úplně.', 'Otázka do třídy: Podle jakého čísla poznáte, že máte nasazení zastavit (canary)?', 'Tempo: chybná změna a rollback jsou jádro – plán vyplňuj stručně.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.', 'Plán B offline: vytištěná konfigurace před a po, žáci najdou chybu a napíší postup návratu.'],
        'glossary' => ['rollback', 'canary'],
    ],
    25 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 25 · Odchylka konfigurace a infrastruktura jako kód',
        'goal' => [
            'student' => 'Na konci hodiny umím odhalit odchylku skutečné konfigurace od požadovaného stavu porovnáním otisků a příkazem diff a vysvětlit, proč je repozitář s konfigurací jediný zdroj pravdy a proč musí být automatizace idempotentní.',
            'success_criteria' => ['Porovnám otisk SHA-256 požadované a skutečné konfigurace a odchylku doložím výpisem diff.', 'Vysvětlím, proč se ruční změna na serveru musí vrátit do repozitáře, nebo zahodit.', 'Rozliším idempotentní krok (opakování nic nezmění) od neidempotentního.'],
        ],
        'competencies' => [['id' => 'ops_iac', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: „u mě to funguje“', 'teacher' => 'Popíše dva servery se „stejnou“ konfigurací, z nichž jeden selhává.', 'student' => 'Hádají, co se liší.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Požadovaný vs. skutečný stav', 'teacher' => 'Vysvětlí zdroj pravdy a odchylku.', 'student' => 'Zapíší, kde je požadovaný stav jejich služby.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Otisky a diff', 'teacher' => 'Předvede sha256sum a diff.', 'student' => 'Najdou odchylku po ruční změně.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 62, 'phase' => 'Idempotence', 'teacher' => 'Porovná krok „přidej řádek“ a „zajisti, že řádek existuje“.', 'student' => 'Roztřídí kroky na idempotentní a ne.', 'form' => 've dvojicích'],
            ['from' => 62, 'to' => 80, 'phase' => 'Proces změn', 'teacher' => 'Ukáže postup: změna v repozitáři → revize → plán → nasazení.', 'student' => 'Navrhnou, jak odchylku řešit.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: změna patří do repozitáře.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spočítej otisk SHA-256 konfigurace webu a zapiš ho jako „požadovaný stav“.', 'output' => 'Otisk začínající 317e7529….', 'time' => '10 min',
                'sim' => [['cmd' => 'sha256sum /etc/nginx/sites-enabled/default', 'expect' => '317e7529728a0f8b']]],
            ['text' => 'Proveď ruční změnu na serveru (index.html → index.htm), spočítej otisk znovu a odchylku dolož výpisem diff proti záloze.', 'output' => 'Nový otisk 101fd394… a diff s řádkem index index.htm.', 'time' => '20 min',
                'sim' => [['cmd' => "sudo sed -i 's/index index.html/index index.htm/' /etc/nginx/sites-enabled/default; sha256sum /etc/nginx/sites-enabled/default", 'expect' => '101fd39451648f77'], ['cmd' => "sudo cp /etc/nginx/sites-enabled/default /tmp/pozadovany; sudo sed -i 's/index index.html/index index.htm/' /etc/nginx/sites-enabled/default; diff /tmp/pozadovany /etc/nginx/sites-enabled/default", 'expect' => 'index index.htm;']]],
            ['text' => 'Roztřiď pět kroků automatizace na idempotentní a neidempotentní (např. „přidej řádek na konec souboru“ vs. „zajisti, že soubor obsahuje řádek“).', 'output' => 'Tabulka kroků se zdůvodněním.', 'time' => '15 min'],
            ['text' => 'Navrhni postup, jak odchylku vyřešit: vrátit server do požadovaného stavu, nebo změnu převzít do repozitáře přes revizi – a kdo rozhoduje.', 'output' => 'Postup ve 4–6 krocích s rolí a ověřením otiskem.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku požadovaný/skutečný stav a pět kroků s nápovědou.',
            'standard' => 'Otisky, diff, idempotence a postup řešení odchylky.',
            'challenge' => 'Popíše, jak by se odchylka hlídala automaticky každou noc a co by se stalo při nálezu (blast radius změny).',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže dva otisky, třída řekne, jestli je odchylka.', 'Dvojice: je krok „mkdir -p“ idempotentní?'],
            'rubric' => [
                ['criterion' => 'Odhalení odchylky', 'levels' => ['Neumí.', 'Jen otisk.', 'Otisk a diff s vysvětlením.', 'Navrhne automatickou kontrolu.']],
                ['criterion' => 'Idempotence', 'levels' => ['Nezná.', 'Zaměňuje.', 'Správné roztřídění.', 'Přepíše neidempotentní krok.']],
                ['criterion' => 'Proces', 'levels' => ['Ruční opravy.', 'Bez revize.', 'Repozitář, revize, ověření.', 'Rozhodnutí s rolí a záznamem.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_iac', 'competence_label' => 'Infrastruktura jako kód a odchylka', 'variants' => [
            ['question' => 'Co znamená, že server má odchylku konfigurace (drift)?', 'options' => ['Rozdíl mezi požadovaným stavem v repozitáři a skutečným stavem serveru.', 'Pomalé načítání konfigurace.', 'Chyba v názvu souboru.'], 'correct' => 0, 'explanation' => 'Vzniká typicky ruční změnou mimo proces.'],
            ['question' => 'Který krok je idempotentní?', 'options' => ['Přidej řádek na konec souboru.', 'Zvyš číslo verze o 1.', 'Zajisti, že adresář /srv/web existuje (mkdir -p).'], 'correct' => 2, 'explanation' => 'Opakované spuštění nic dalšího nezmění.'],
            ['question' => 'Někdo opravil konfiguraci ručně na serveru. Co je správně?', 'options' => ['Nechat to tak, funguje to.', 'Změnu buď převzít do repozitáře přes revizi, nebo server vrátit do požadovaného stavu.', 'Smazat repozitář.'], 'correct' => 1, 'explanation' => 'Repozitář musí zůstat jediným zdrojem pravdy.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi doma „ruční odchylku“ (např. nastavení, které jsi změnil a nikde nezapsal) a zapiš ho.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Změny konfigurace jen v simulátoru Linux Labu.', 'Do repozitáře s konfigurací nepatří hesla ani klíče – patří do správce tajemství.'],
        'teacher_notes' => ['Častý omyl: „otisk se liší, tak je to rozbité“ – odchylka může být i chtěná, rozhoduje proces.', 'Otázka do třídy: Proč je IaC bez revize jen rychlejší způsob, jak udělat chybu?', 'Tempo: otisk a diff jsou rychlé; víc času dej idempotenci a procesu.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.', 'Plán B offline: dva vytištěné konfigurační soubory, hledání odchylky ručně.'],
        'glossary' => ['drift', 'idempotence'],
    ],
    26 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 26 · Výkon a kapacita: měřit, spočítat rezervu, rozhodnout',
        'goal' => [
            'student' => 'Na konci hodiny umím přečíst využití CPU a paměti, z měření zátěžového testu spočítat propustnost a kapacitní rezervu a rozhodnout, jestli server škálovat, nebo optimalizovat.',
            'success_criteria' => ['Z free a top přečtu dostupnou paměť a zatížení CPU.', 'Spočítám kapacitní rezervu: maximální propustnost proti očekávané špičce.', 'Rozhodnutí o škálování zdůvodním čísly a zvolím cílovou rezervu (např. 30 %).'],
        ],
        'competencies' => [['id' => 'ops_performance', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: přijímačky na webu', 'teacher' => 'Popíše den, kdy se na web přihlásí 10× víc lidí než obvykle.', 'student' => 'Odhadnou, co musíme vědět předem.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Měření systému', 'teacher' => 'Předvede free, top a lscpu.', 'student' => 'Přečtou paměť, zatížení a počet CPU.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Výsledky zátěžového testu', 'teacher' => 'Rozdá tabulku: požadavky za sekundu vs. p95 a chybovost.', 'student' => 'Najdou bod, kde latence prudce roste.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 62, 'phase' => 'Rezerva', 'teacher' => 'Ukáže výpočet rezervy a cílové hodnoty.', 'student' => 'Spočítají rezervu pro očekávanou špičku.', 'form' => 'jednotlivě'],
            ['from' => 62, 'to' => 80, 'phase' => 'Rozhodnutí', 'teacher' => 'Porovná škálování a optimalizaci.', 'student' => 'Napíší doporučení se zdůvodněním.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: kapacita se plánuje čísly.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zjisti dostupnou paměť, zatížení a počet CPU serveru a zapiš, jestli je server teď vytížený.', 'output' => 'Dostupné 3,0 GiB z 3,8 GiB, load 0,08 při 2 CPU – nevytížený.', 'time' => '12 min',
                'sim' => [['cmd' => 'free -h', 'expect' => '3.0Gi'], ['cmd' => 'top -b -n 1 | head -n 3', 'expect' => 'Tasks:   9 total']]],
            ['text' => 'V tabulce zátěžového testu (100, 200, 300, 400 požadavků/s; p95 80, 90, 140, 900 ms) najdi bod, kde latence prudce roste, a urči maximální použitelnou propustnost.', 'output' => 'Zlom mezi 300 a 400 req/s; použitelné maximum ≈ 300 req/s.', 'time' => '15 min'],
            ['text' => 'Spočítej kapacitní rezervu pro očekávanou špičku 240 požadavků/s při maximu 300 požadavků/s a porovnej ji s cílem 30 %.', 'output' => 'Rezerva (300 − 240) / 300 = 20 % – pod cílem 30 %.', 'time' => '15 min'],
            ['text' => 'Napiš doporučení: škálovat (víc instancí), nebo optimalizovat (např. cache) – s čísly, rizikem a způsobem ověření novým testem.', 'output' => 'Doporučení na půl strany se zdůvodněním a plánem ověření.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzorec rezervy a tabulku testu s vyznačenými řádky.',
            'standard' => 'Měření, zlom v testu, rezerva a doporučení.',
            'challenge' => 'Spočítá, kolik instancí je potřeba pro špičku 600 req/s při rezervě 30 % a maximu 300 req/s na instanci.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže řádek free, třída řekne dostupnou paměť.', 'Dvojice: kde v tabulce testu začíná latence růst prudce?'],
            'rubric' => [
                ['criterion' => 'Měření', 'levels' => ['Nečte.', 'Jen jedna hodnota.', 'Paměť, zatížení, CPU s výkladem.', 'Spojí s latencí a chybami.']],
                ['criterion' => 'Výpočet rezervy', 'levels' => ['Chybí.', 'S chybou.', 'Správně s porovnáním s cílem.', 'I počet instancí pro růst.']],
                ['criterion' => 'Rozhodnutí', 'levels' => ['Bez zdůvodnění.', 'Dojem.', 'Čísla, riziko, ověření.', 'Srovnání dvou variant s náklady.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_performance', 'competence_label' => 'Výkon a kapacita', 'variants' => [
            ['question' => 'Maximum je 500 req/s, očekávaná špička 400 req/s. Jaká je rezerva?', 'options' => ['25 %', '80 %', '20 %'], 'correct' => 2, 'explanation' => '(500 − 400) / 500 = 0,2 = 20 %.'],
            ['question' => 'Při 400 req/s skočí p95 z 140 ms na 900 ms. Co to znamená?', 'options' => ['Server se blíží saturaci a fronty rostou.', 'Test je chybný.', 'Server je rychlejší.'], 'correct' => 0, 'explanation' => 'Prudký růst latence = blízko kapacitního limitu.'],
            ['question' => 'Proč nechávat kapacitní rezervu (headroom)?', 'options' => ['Aby server nic nedělal.', 'Pro nečekané špičky a výpadek části systému.', 'Protože to vyžaduje DNS.'], 'correct' => 1, 'explanation' => 'Rezerva absorbuje špičky a selhání.'],
        ]],
        'homework' => [['text' => 'Volitelné: odhadni, kolik lidí najednou otevře web školy v den zveřejnění výsledků přijímaček, a zdůvodni odhad.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Zátěžové testy se pouštějí jen na vlastní testovací prostředí; zátěž cizí služby je útok.', 'Měříme v simulátoru a z připravených dat.'],
        'teacher_notes' => ['Častý omyl: rezerva počítaná z očekávané zátěže místo z maxima – ukaž vzorec na tabuli.', 'Otázka do třídy: Kdy je lepší optimalizovat než přidat server?', 'Tempo: výpočty jsou krátké; víc času dej doporučení s čísly.'],
        'substitution' => ['Zástup bez odborníka: úkol 1 v Linux Labu (výstup v zadání), úkoly 2–4 z vytištěné tabulky.', 'Plán B offline: vytištěná tabulka testu a výpočty ručně.'],
        'glossary' => ['headroom', 'throughput'],
    ],
    27 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 27 · Velení incidentu: rozpočet chyb a rozhodování pod tlakem',
        'goal' => [
            'student' => 'Na konci hodiny umím jako velitel incidentu rozhodovat podle dat – spočítat, kolik rozpočtu chyb incident spotřeboval, zvolit mezi rychlou nápravou a rollbackem a vést komunikaci s vedením.',
            'success_criteria' => ['Spočítám měsíční rozpočet chyb (error budget) z SLO (např. 99,5 % za 30 dní = 216 minut).', 'Rozhodnutí (náprava, rollback, eskalace) zdůvodním dopadem a rozpočtem chyb.', 'Zpráva pro vedení má dopad, stav, rozhodnutí a čas další zprávy.'],
        ],
        'competencies' => [['id' => 'ops_incident', 'level' => 4], ['id' => 'ops_observability', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: tlak vedení', 'teacher' => 'Hraje vedení, které chce „hned opravit“.', 'student' => 'Zapíší, co velitel potřebuje vědět.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'SLO a rozpočet chyb', 'teacher' => 'Vysvětlí SLI, SLO a rozpočet chyb.', 'student' => 'Spočítají rozpočet pro dvě SLO.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 48, 'phase' => 'Cvičný incident', 'teacher' => 'Postupně odhaluje fakta incidentu.', 'student' => 'Velitel rozhoduje, tým sbírá důkazy.', 'form' => 've skupinách'],
            ['from' => 48, 'to' => 62, 'phase' => 'Rozhodnutí', 'teacher' => 'Nabídne volby: náprava, rollback, eskalace.', 'student' => 'Zvolí a zdůvodní daty.', 'form' => 've skupinách'],
            ['from' => 62, 'to' => 80, 'phase' => 'Zpráva pro vedení', 'teacher' => 'Ukáže vzor krátké zprávy.', 'student' => 'Napíší zprávu a spotřebu rozpočtu.', 'form' => 've skupinách'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: rozhoduj daty, komunikuj pravidelně.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spočítej měsíční rozpočet chyb pro SLO 99,5 % a 99,9 % dostupnosti za 30 dní v minutách.', 'output' => '99,5 % → 216 min; 99,9 % → 43,2 min.', 'time' => '12 min'],
            ['text' => 'Jako tým zjisti z přístupového logu, na kterou stránku mířily odpovědi 404, a rozhodni, jestli jde o incident, nebo chybný odkaz.', 'output' => 'Požadavky GET /stara-stranka.html z 10.0.0.31 – starý odkaz, ne výpadek služby.', 'time' => '15 min',
                'sim' => [['cmd' => "sudo grep ' 404 ' /var/log/nginx/access.log | head -n 2", 'expect' => 'GET /stara-stranka.html']]],
            ['text' => 'Incident trval 45 minut s úplným výpadkem. Spočítej, kolik procent měsíčního rozpočtu (SLO 99,5 %) spotřeboval, a rozhodni, jestli zmrazit nové změny.', 'output' => '45 / 216 ≈ 21 % rozpočtu; zdůvodněné rozhodnutí o zmrazení.', 'time' => '15 min'],
            ['text' => 'Napiš zprávu pro vedení do 5 vět: dopad, stav, rozhodnutí, spotřeba rozpočtu chyb a čas další zprávy.', 'output' => 'Zpráva bez technického žargonu a bez jmen.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzorec rozpočtu chyb a šablonu zprávy.',
            'standard' => 'Rozpočet, analýza logu, spotřeba rozpočtu a zpráva.',
            'challenge' => 'Navrhne pravidlo: při jaké spotřebě rozpočtu se zastaví nasazování a kdo může udělit výjimku.',
        ],
        'assessment' => [
            'formative' => ['Učitel řekne SLO, třída spočítá rozpočet v minutách.', 'Skupiny si přečtou zprávy navzájem: je jasné, co se rozhodlo?'],
            'rubric' => [
                ['criterion' => 'Rozpočet chyb', 'levels' => ['Nespočítá.', 'S chybou.', 'Správně pro obě SLO.', 'Spočítá i spotřebu incidentu.']],
                ['criterion' => 'Rozhodnutí', 'levels' => ['Bez důvodu.', 'Podle pocitu.', 'Podle dopadu a rozpočtu.', 'Zváží i riziko rozhodnutí.']],
                ['criterion' => 'Zpráva pro vedení', 'levels' => ['Chybí.', 'Technický žargon.', 'Dopad, stav, rozhodnutí, čas.', 'Stručná a srozumitelná do 5 vět.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_observability', 'competence_label' => 'Pozorovatelnost a SLO', 'variants' => [
            ['question' => 'SLO dostupnosti je 99,9 % za 30 dní. Kolik minut výpadku si služba smí dovolit?', 'options' => ['432 minut', '43,2 minuty', '4,3 minuty'], 'correct' => 1, 'explanation' => '0,1 % z 43 200 minut = 43,2 minuty.'],
            ['question' => 'Rozpočet chyb je na konci měsíce skoro vyčerpaný. Co je rozumné?', 'options' => ['Zrychlit nasazování nových funkcí.', 'Vypnout monitoring.', 'Omezit riskantní změny a soustředit se na spolehlivost.'], 'correct' => 2, 'explanation' => 'Rozpočet chyb řídí tempo změn.'],
            ['question' => 'Co je hlavní úkol velitele incidentu?', 'options' => ['Koordinovat tým, rozhodovat a zajistit komunikaci.', 'Sám opravit všechny chyby.', 'Najít viníka.'], 'correct' => 0, 'explanation' => 'Velitel řídí, technici opravují.'],
        ]],
        'homework' => [['text' => 'Volitelné: spočítej rozpočet chyb pro SLO 99 % za týden a porovnej ho s 99,9 %.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Incident a logy jsou ze simulátoru; žádné skutečné služby se nezastavují.', 'Zprávy neobsahují jména, adresy uživatelů ani technické tajnosti.'],
        'teacher_notes' => ['Častý omyl: velitel incidentu sám píše příkazy – drž ho v roli koordinátora.', 'Otázka do třídy: Kdy je rollback lepší než rychlá oprava?', 'Tempo: výpočty rozpočtu na začátku, ať je mají při rozhodování; vedení hraj s mírným tlakem.'],
        'substitution' => ['Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), ostatní na papír podle vzorců.', 'Plán B offline: incident podle karet s fakty, výpočty a zpráva na papíře.'],
        'glossary' => ['error-budget', 'slo'],
    ],
    28 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 28 · Ověření dovedností: spolehlivý provoz v kostce',
        'goal' => [
            'student' => 'Na konci hodiny umím v jednom scénáři propojit pozorovatelnost, bezpečnou změnu a řízení incidentu – každou část doložit důkazem – a podle kompetencí ročníku určit své silné stránky a mezery.',
            'success_criteria' => ['Ve scénáři doložím stav služby, test konfigurace a ověření z pohledu uživatele.', 'Mapa jedenácti kompetencí ročníku má u každé důkaz, nebo pojmenovanou mezeru.', 'Napíšu plán na rozvoj jedné kompetence s měřitelným výsledkem.'],
        ],
        'competencies' => [['id' => 'ops_change', 'level' => 4], ['id' => 'ops_observability', 'level' => 4]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: mapa roku', 'teacher' => 'Promítne kompetence ročníku a scénář ověření.', 'student' => 'Odhadnou své nejsilnější oblasti.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 45, 'phase' => 'Scénář', 'teacher' => 'Hlídá důkazy a čas.', 'student' => 'Projdou scénář: signály, změna s rollbackem, ověření.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 60, 'phase' => 'Krátký rozbor', 'teacher' => 'Připomene osnovu rozboru.', 'student' => 'Sepíší rozbor scénáře.', 'form' => 've dvojicích'],
            ['from' => 60, 'to' => 75, 'phase' => 'Mapa kompetencí', 'teacher' => 'Vysvětlí hodnocení podle důkazů.', 'student' => 'Vyplní mapu kompetencí.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 85, 'phase' => 'Plán rozvoje', 'teacher' => 'Ukáže příklad měřitelného plánu.', 'student' => 'Napíší plán rozvoje.', 'form' => 'jednotlivě'],
            ['from' => 85, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne rok.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zjisti stav webové služby, otestuj konfiguraci a ověř odpověď z pohledu uživatele; ke každému kroku zapiš důkaz.', 'output' => 'active (running), test is successful, HTTP/1.1 200 OK.', 'time' => '15 min',
                'sim' => [['cmd' => 'systemctl status nginx', 'expect' => 'active (running)'], ['cmd' => 'sudo nginx -t', 'expect' => 'test is successful'], ['cmd' => 'curl -I http://localhost', 'expect' => 'HTTP/1.1 200 OK']]],
            ['text' => 'Proveď chybnou změnu konfigurace s rollbackem a zapiš, jak dlouho by služba byla ohrožená, kdybys změnu nasadil(a) bez testu.', 'output' => 'Rollback ověřený testem syntax is ok a odhad dopadu.', 'time' => '20 min',
                'sim' => [['cmd' => "sudo cp /etc/nginx/sites-enabled/default /tmp/default.bak; sudo sed -i 's/try_files/try_filez/' /etc/nginx/sites-enabled/default; sudo cp /tmp/default.bak /etc/nginx/sites-enabled/default; sudo nginx -t", 'expect' => 'test is successful']]],
            ['text' => 'Sepiš krátký rozbor scénáře: co signály ukázaly, jak test změny zabránil výpadku a jedno opatření.', 'output' => 'Rozbor do 8 vět s jedním měřitelným opatřením.', 'time' => '15 min'],
            ['text' => 'Vyplň mapu jedenácti kompetencí ročníku (důkaz nebo mezera) a napiš plán rozvoje jedné kompetence s měřitelným výsledkem.', 'output' => 'Mapa kompetencí a plán: co, jak, do kdy, jak poznám zlepšení.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu scénáře s pořadím příkazů a šablonu mapy kompetencí.',
            'standard' => 'Scénář s důkazy, rozbor, mapa a plán.',
            'challenge' => 'Doplní do scénáře návrh SLO a upozornění, které by problém odhalily dřív než uživatel.',
        ],
        'assessment' => [
            'formative' => ['Dvojice si navzájem potvrzují důkazy ze scénáře.', 'Učitel přečte plán a třída posoudí, jestli je měřitelný.'],
            'rubric' => [
                ['criterion' => 'Scénář s důkazy', 'levels' => ['Bez důkazů.', 'Část kroků.', 'Všechny kroky s důkazem.', 'Důkazy seřazené a vysvětlené.']],
                ['criterion' => 'Rozbor', 'levels' => ['Chybí.', 'Bez opatření.', 'Signály, změna, opatření.', 'Opatření měřitelné s vlastníkem.']],
                ['criterion' => 'Mapa a plán', 'levels' => ['Chybí.', 'Podle pocitu.', 'Podle důkazů s plánem.', 'Plán s měřítkem a termínem.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_change', 'competence_label' => 'Řízená změna s plánem návratu', 'variants' => [
            ['question' => 'Jaké pořadí kroků je při změně konfigurace správné?', 'options' => ['Záloha → změna → test konfigurace → nasazení → ověření.', 'Změna → nasazení → záloha.', 'Nasazení → test → změna.'], 'correct' => 0, 'explanation' => 'Záloha a test chrání před výpadkem.'],
            ['question' => 'Který důkaz nejlépe potvrdí, že služba po změně funguje?', 'options' => ['Že příkaz proběhl bez chyby.', 'Že je server zapnutý.', 'Odpověď 200 z pohledu uživatele a čistý log.'], 'correct' => 2, 'explanation' => 'Ověřujeme to, co cítí uživatel.'],
            ['question' => 'Který plán rozvoje je měřitelný?', 'options' => ['Zlepším se v provozu.', 'Do konce května vyřeším 5 incidentů v Linux Labu s rozborem a ověřím se exit ticketem.', 'Budu víc číst.'], 'correct' => 1, 'explanation' => 'Konkrétní výstup, termín a ověření.'],
        ]],
        'homework' => [['text' => 'Volitelné: splň první krok plánu rozvoje a zapiš, co ti ukázal.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Scénář běží v simulátoru Linux Labu; žádné skutečné systémy.', 'Mapa kompetencí je osobní – vidí ji žák a jeho učitel.'],
        'teacher_notes' => ['Častý omyl: sebehodnocení bez důkazu – každá kompetence potřebuje odkaz na práci nebo výstup.', 'Otázka do třídy: Který návyk z provozu si odnesete i mimo IT?', 'Tempo: scénář drž do 45. minuty; mapa a plán jsou povinný výstup.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.', 'Plán B offline: scénář podle vytištěných výpisů, mapa a plán na papíře.'],
        'glossary' => ['rollback', 'sli'],
    ],
]]];
