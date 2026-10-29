<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 4.A (provoz sítí a služeb), lekce 11–16.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php,
 * ověřované příkazy simulátoru (klíč `sim`) viz lesson_content_v72_3a_a.php.
 * Pozn.: `df -i` simulátor nemodeluje (ukazuje bloky, ne inody) – inody se vysvětlují na připraveném výpisu (viz poznámky).
 * Kompetence: 4.A zatím bez katalogu v62 (v75) – exit ticket jen s popisem kompetence.
 */

return ['class_4a' => ['lessons' => [
    11 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 11 · Úložiště: disk, souborový systém, připojení a plný disk',
        'goal' => [
            'student' => 'Na konci hodiny umím rozlišit zařízení, oddíl, souborový systém a bod připojení a bezpečně diagnostikovat „plný disk“ včetně vyčerpaných inodů.',
            'success_criteria' => ['Nakreslím vrstvy zařízení → oddíl → souborový systém → bod připojení podle lsblk a df.', 'Nejdřív zjistím, který souborový systém je plný (df), teprve pak hledám velké adresáře (du).', 'Vysvětlím, proč může disk s volnými GB odmítnout nový soubor.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše incident: web hlásí „No space left on device“.', 'student' => 'Navrhnou první tři kroky.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Vrstvy úložiště', 'teacher' => 'Předvede lsblk a df -h v Linux Labu.', 'student' => 'Nakreslí vrstvy úložiště lab-pc.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'df, pak du', 'teacher' => 'Ukáže pořadí: který souborový systém → který adresář.', 'student' => 'Najdou zaplnění kořenového systému a velikost /var/log.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Růst logů a inody', 'teacher' => 'Vysvětlí inody na připraveném výpisu df -i ze skutečného serveru.', 'student' => 'Rozhodnou, zda jde o kapacitu, nebo inody.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 75, 'phase' => 'Bezpečná náprava', 'teacher' => 'Zdůrazní: aktivní log nemazat naslepo.', 'student' => 'Sepíšou postup nápravy v 5 krocích.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Prevence a exit ticket', 'teacher' => 'Zadá monitoring kapacity a retenci.', 'student' => 'Navrhnou práh a akci a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spusť lsblk a df -h a nakresli vrstvy: disk sda → oddíl sda1 → souborový systém → bod připojení /.', 'output' => 'sda1 20G připojený jako /, zaplněno 31 %.', 'time' => '15 min',
                'sim' => [['cmd' => 'lsblk', 'expect' => 'sda1'], ['cmd' => 'df -h', 'expect' => '31% /']]],
            ['text' => 'Zjisti velikost adresáře logů příkazem du -sh /var/log a vysvětli, proč se du spouští až po df.', 'output' => '28K /var/log + zdůvodnění pořadí.', 'time' => '15 min',
                'sim' => [['cmd' => 'du -sh /var/log', 'expect' => '/var/log']]],
            ['text' => 'Na připraveném výpisu df -i (sloupec IUse% 100 %) rozhodni, zda chybí místo, nebo inody (mount point /var/spool).', 'output' => 'Vyčerpané inody – příliš mnoho malých souborů.', 'time' => '15 min'],
            ['text' => 'Sepiš bezpečný postup nápravy plného disku v 5 krocích a navrhni monitoring kapacity (práh varování 80 %, kritický 90 %) a retenci logů (retention).', 'output' => 'Postup + práh + retence.', 'time' => '30 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane diagram vrstev k doplnění a vzorový výpis df s vyznačeným řádkem.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Vysvětlí, proč smazání aktivního logu nemusí uvolnit místo, dokud ho služba drží otevřený.',
        ],
        'assessment' => [
            'formative' => ['Ukaž vrstvu: učitel řekne „sda1“, třída řekne, co to je.', 'Kapacita, nebo inody? (dva výpisy na tabuli)'],
            'rubric' => [
                ['criterion' => 'Vrstvy úložiště', 'levels' => ['Nerozliší.', 'Plete oddíl a bod připojení.', 'Správný diagram vrstev.', 'Propojí s konfigurací připojení po restartu.']],
                ['criterion' => 'Diagnostika', 'levels' => ['Maže naslepo.', 'du bez df.', 'df → du, kapacita vs. inody.', 'Vysvětlí otevřený smazaný soubor.']],
                ['criterion' => 'Prevence', 'levels' => ['Žádná.', 'Práh bez akce.', 'Práh, akce a retence.', 'Zdůvodní hodnoty podle růstu dat.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Úložiště a zálohy', 'variants' => [
            ['question' => 'Který příkaz použiješ jako první při hlášce „No space left on device“?', 'options' => ['du -sh /*', 'df -h', 'rm -rf /var/log/*'], 'correct' => 1, 'explanation' => 'df ukáže, který souborový systém je plný.'],
            ['question' => 'df -h ukazuje volných 14G, ale nový soubor nejde vytvořit. Co ověříš?', 'options' => ['Zda nejsou vyčerpané inody (df -i).', 'DNS server.', 'Rychlost sítě.'], 'correct' => 0, 'explanation' => 'Každý soubor potřebuje inode.'],
            ['question' => 'Co je bod připojení (mount point)?', 'options' => ['Název disku.', 'Velikost oddílu.', 'Adresář, ve kterém je souborový systém zpřístupněný.'], 'correct' => 2, 'explanation' => 'Např. sda1 je připojený jako /.'],
        ]],
        'homework' => [['text' => 'Volitelné: v nastavení telefonu nebo počítače zjisti, kolik místa zabírají fotky a kolik aplikace.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Nikdy nemažeme skutečné systémové logy; pracujeme v simulátoru a s připravenými výpisy.', 'Příkaz rm -rf nad systémovými cestami je zakázaný i v labu.'],
        'teacher_notes' => ['Simulátor neumí df -i (ukazuje bloky) – inody vysvětli na připraveném výpisu.', 'Otázka do třídy: Který souborový systém je plný, a víme to jistě?', 'Tempo: postup nápravy je hlavní výstup – nech na něj 30 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na vytištěném výpisu.', 'Plán B offline: vytištěné výpisy lsblk, df a df -i.'],
        'glossary' => ['inode', 'mount-point', 'retention'],
    ],
    12 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 12 · Strategie záloh: RPO, RTO a nácvik obnovy',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout zálohování podle požadované ztráty dat (RPO) a doby obnovy (RTO) a doložit obnovitelnost testovací obnovou.',
            'success_criteria' => ['Vysvětlím RPO a RTO a odvodím z nich četnost záloh.', 'Sestavím tabulku záloh (data, konfigurace, tajné údaje odděleně) s retencí.', 'Popíšu obnovu do testovacího cíle s kontrolou integrity a funkčnosti.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Zeptá se: kolik práce bys snesl ztratit – den, hodinu, minutu?', 'student' => 'Odpoví pro školní systém známek.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'RPO a RTO', 'teacher' => 'Vysvětlí oba pojmy na příkladu.', 'student' => 'Určí RPO a RTO pro tři systémy.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 42, 'phase' => 'Co zálohovat', 'teacher' => 'Ukáže rozdělení data / konfigurace / tajné údaje.', 'student' => 'Sestaví tabulku záloh s retencí.', 'form' => 'jednotlivě'],
            ['from' => 42, 'to' => 62, 'phase' => 'Nácvik obnovy', 'teacher' => 'Zdůrazní testovací cíl, nikdy produkce.', 'student' => 'Projdou obnovu v labu a zapíší čas.', 'form' => 'jednotlivě'],
            ['from' => 62, 'to' => 78, 'phase' => 'Integrita a funkčnost', 'teacher' => 'Ukáže kontrolní součet a aplikační test.', 'student' => 'Ověří obnovená data a funkčnost.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Runbook a exit ticket', 'teacher' => 'Zadá kroky, odpovědnosti a podmínky zastavení.', 'student' => 'Sepíšou runbook obnovy a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Urči RPO a RTO pro tři systémy (známky, školní web, archiv fotek) a z RPO odvoď četnost záloh.', 'output' => 'Tabulka RPO, RTO a četnosti.', 'time' => '15 min'],
            ['text' => 'Sestav tabulku záloh: co (data, konfigurace, tajné údaje), jak často, kam, jak dlouho držet (retence, retention).', 'output' => 'Tabulka záloh.', 'time' => '17 min'],
            ['text' => 'Proveď nácvik obnovy (restore drill) do testovacího cíle a změř čas obnovy proti RTO.', 'output' => 'Záznam nácviku + naměřený čas.', 'time' => '20 min'],
            ['text' => 'Ověř integritu (kontrolní součet sha256sum) a funkčnost obnovené služby a sepiš runbook obnovy.', 'output' => 'Výsledek kontroly + runbook.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane příklad RPO/RTO pro jeden systém a šablonu tabulky záloh.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne pravidlo 3-2-1 pro školu (3 kopie, 2 typy médií, 1 mimo budovu) a jeho rizika.',
        ],
        'assessment' => [
            'formative' => ['Rychlý příklad: zálohujeme jednou denně – jaké je nejhorší RPO?', 'Kontrola tabulky: jsou tajné údaje oddělené?'],
            'rubric' => [
                ['criterion' => 'RPO a RTO', 'levels' => ['Nezná.', 'Plete pojmy.', 'Správně určí a odvodí četnost.', 'Zdůvodní podle dopadu na školu.']],
                ['criterion' => 'Tabulka záloh', 'levels' => ['Chybí.', 'Bez retence.', 'Data, konfigurace, tajné údaje + retence.', 'Pravidlo 3-2-1 s riziky.']],
                ['criterion' => 'Obnova', 'levels' => ['Jen „máme zálohu“.', 'Obnova bez kontroly.', 'Obnova, integrita, funkčnost.', 'Runbook s podmínkami zastavení.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Úložiště a zálohy', 'variants' => [
            ['question' => 'Zálohuje se jednou denně o půlnoci. Jaké je nejhorší RPO?', 'options' => ['Až 24 hodin dat.', '1 hodina.', '0 – nic se neztratí.'], 'correct' => 0, 'explanation' => 'Výpadek těsně před půlnocí = ztráta celého dne.'],
            ['question' => 'Co vyjadřuje RTO?', 'options' => ['Kolik dat smíme ztratit.', 'Jak dlouho smí trvat obnova provozu.', 'Kolik kopií zálohy máme.'], 'correct' => 1, 'explanation' => 'RPO = data, RTO = čas.'],
            ['question' => 'Kam obnovujeme při nácviku?', 'options' => ['Přímo na produkční server.', 'Na počítač spolužáka.', 'Do odděleného testovacího cíle.'], 'correct' => 2, 'explanation' => 'Nácvik nesmí ohrozit produkci.'],
        ]],
        'homework' => [['text' => 'Volitelné: zjisti, zda a jak se zálohuje tvůj školní nebo osobní e-mail, a odhadni jeho RPO.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Obnovujeme jen vymyšlená data do testovacího cíle; produkční data školy se nikdy nepoužívají.', 'Tajné údaje (hesla, klíče) se zálohují odděleně a šifrovaně – v labu je jen simulujeme.'],
        'teacher_notes' => ['Uč rozdíl „záloha doběhla“ vs. „podnik se obnovil“.', 'Otázka do třídy: Kdy jsme naposledy zálohu opravdu obnovili?', 'Tempo: výpočty RPO jsou rychlé – čas dej nácviku obnovy.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 2 a runbook na papír, nácvik podle návodu v labu.', 'Plán B offline: nácvik obnovy jako papírový checklist s časovačem.'],
        'glossary' => ['rpo', 'rto', 'restore', 'retention'],
    ],
    13 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 13 · Zabezpečení služby: SSH, firewall, aktualizace a práva',
        'goal' => [
            'student' => 'Na konci hodiny umím zabezpečit (hardening) službu: zmenšit plochu útoku, nastavit bezpečné minimum SSH a firewallu a připravit cestu návratu, abych se nezamkl venku.',
            'success_criteria' => ['Sepíšu vystavené služby podle ss -tln a navrhnu, co omezit.', 'Navrhnu SSH bez přihlášení roota, s klíči a omezenými účty.', 'Před zpřísněním přístupu mám ověřený náhradní přístup a plán návratu.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše správce, který zpřísnil firewall a zamkl se venku (lockout).', 'student' => 'Navrhnou, co měl udělat předem.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Plocha útoku', 'teacher' => 'Předvede ss -tln v Linux Labu.', 'student' => 'Sepíšou vystavené služby a kdo je potřebuje.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'SSH minimum', 'teacher' => 'Ukáže sshd_config v simulátoru.', 'student' => 'Navrhnou nastavení SSH (klíče, bez roota, omezení účtů).', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Rozsah firewallu', 'teacher' => 'Vysvětlí správu jen ze zóny správy.', 'student' => 'Navrhnou pravidla a náhradní přístup přes konzoli.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 70, 'phase' => 'Plán aktualizací', 'teacher' => 'Ukáže dopad aktualizace a potřebu restartu.', 'student' => 'Sepíšou plán aktualizace s ověřením.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 90, 'phase' => 'Ověření zabezpečení a exit ticket', 'teacher' => 'Zadá pozitivní a negativní testy a záznam změny.', 'student' => 'Napíšou testy a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Spusť ss -tln, sepiš vystavené služby a ke každé napiš, kdo ji opravdu potřebuje (plocha útoku, attack surface).', 'output' => '0.0.0.0:22 (SSH – jen správa), 0.0.0.0:80 (web – všichni).', 'time' => '15 min',
                'sim' => [['cmd' => 'ss -tln', 'expect' => '0.0.0.0:22'], ['cmd' => 'ss -tln', 'expect' => '0.0.0.0:80']]],
            ['text' => 'Přečti sudo cat /etc/ssh/sshd_config a navrhni bezpečné minimum SSH (přihlášení klíčem, bez roota, omezení účtů).', 'output' => 'Ověřené „PermitRootLogin no“ + 3 doporučení.', 'time' => '15 min',
                'sim' => [['cmd' => 'sudo cat /etc/ssh/sshd_config', 'expect' => 'PermitRootLogin no']]],
            ['text' => 'Navrhni pravidla firewallu: SSH jen ze zóny správy, web pro všechny; připiš náhradní přístup (konzole) a postup návratu.', 'output' => 'Pravidla + náhradní přístup + návrat.', 'time' => '15 min'],
            ['text' => 'Sepiš plán aktualizace (dopad, restart, ověření) a pozitivní i negativní test zabezpečení se záznamem změny.', 'output' => 'Plán aktualizace a 4 testy.', 'time' => '30 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam zabezpečení (služby, SSH, firewall, aktualizace, ověření).',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne, jak by změnu firewallu na vzdáleném serveru automaticky vrátil, pokud se do 5 minut nepotvrdí.',
        ],
        'assessment' => [
            'formative' => ['Kdo službu potřebuje? (učitel čte řádky z ss)', 'Kontrola plánu: kde je náhradní přístup?'],
            'rubric' => [
                ['criterion' => 'Plocha útoku', 'levels' => ['Neví, co běží.', 'Seznam bez zdůvodnění.', 'Služby a kdo je potřebuje.', 'Navrhne konkrétní omezení.']],
                ['criterion' => 'SSH a firewall', 'levels' => ['Bez změn.', 'Změny bez návratu.', 'Bezpečné minimum + náhradní přístup.', 'Automatický návrat při zamčení.']],
                ['criterion' => 'Ověření', 'levels' => ['Bez testu.', 'Jen pozitivní.', 'Pozitivní i negativní + záznam.', 'Testy ze správných zón.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Bezpečnost provozu', 'variants' => [
            ['question' => 'Co musíš mít připravené před zpřísněním vzdáleného přístupu SSH?', 'options' => ['Ověřený náhradní přístup (konzoli) a plán návratu.', 'Nové pozadí plochy.', 'Delší TTL v DNS.'], 'correct' => 0, 'explanation' => 'Jinak se můžeš zamknout venku.'],
            ['question' => 'Co znamená „zmenšit plochu útoku“?', 'options' => ['Zmenšit disk serveru.', 'Snížit počet uživatelů webu.', 'Omezit vystavené služby a cesty přístupu na nezbytné minimum.'], 'correct' => 2, 'explanation' => 'Co neběží a není vystavené, nejde napadnout.'],
            ['question' => 'Proč se nastavuje PermitRootLogin no?', 'options' => ['Root se tím smaže.', 'Útočník nemůže zkoušet přihlášení přímo jako nejvyšší správce; správci se přihlásí svým účtem.', 'Zrychlí to SSH.'], 'correct' => 1, 'explanation' => 'Vlastní účty + sudo = dohledatelnost a menší riziko.'],
        ]],
        'homework' => [['text' => 'Volitelné: zkontroluj na svém telefonu, které aplikace mají přístup k poloze, a jednu, která ho nepotřebuje, omez.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Defenzivní lab: pracujeme jen na vlastních testovacích systémech, nikdy s cizími.', 'Žádné skenování ani testování cizích serverů.'],
        'teacher_notes' => ['Defenzivní lab; nepracovat s cizími systémy. Důraz na minimální rozsah a návrat.', 'Otázka do třídy: Kdo všechno se teď dostane k portu 22?', 'Tempo: plán aktualizace a testy jsou hlavní výstup – nech na ně 30 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–2 v Linux Labu (výstupy v zadání), úkoly 3–4 na papír.', 'Plán B offline: kontrolní seznam zabezpečení na vytištěném výpisu ss.'],
        'glossary' => ['attack-surface', 'hardening', 'lockout'],
    ],
    14 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 14 · Kontejnery: proces, obraz, svazek a síť',
        'goal' => [
            'student' => 'Na konci hodiny umím vysvětlit kontejner jako izolovaný proces s obrazem, konfigurací, svazkem a mapováním portů a diagnostikovat jeho základní selhání.',
            'success_criteria' => ['Rozliším obraz (image) a kontejner a vím, co zmizí při znovuvytvoření.', 'Odliším port hostitele a port v kontejneru a ověřím, na jaké adrese aplikace poslouchá.', 'Určím, která data patří do svazku (volume), a vím, že tajné údaje nepatří do obrazu.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše: po aktualizaci kontejneru zmizela všechna nahraná data.', 'student' => 'Odhadnou proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Mentální model', 'teacher' => 'Vysvětlí obraz ≠ kontejner, svazek ≠ vrstva obrazu.', 'student' => 'Nakreslí diagram obraz → kontejner → svazek → síť.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Mapování portů', 'teacher' => 'Ukáže zápis 8080:80 (hostitel:kontejner).', 'student' => 'Rozeberou tři zápisy mapování.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Trvalá data', 'teacher' => 'Ukáže, co přežije znovuvytvoření.', 'student' => 'Rozhodnou, co patří do svazku a co do proměnných.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 75, 'phase' => 'Zdraví a logy', 'teacher' => 'Ukáže stav, kontrolu zdraví a log kontejneru (vytištěný výpis).', 'student' => 'Najdou v logu příčinu selhání.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Incident a exit ticket', 'teacher' => 'Zadá incident: port hostitele otevřený, aplikace poslouchá jinde.', 'student' => 'Navrhnou nejmenší opravu a ověření; exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Nakresli diagram: obraz (image) → kontejner (container) → svazek (volume) → síť a mapování portů (port mapping).', 'output' => 'Diagram se čtyřmi částmi.', 'time' => '15 min'],
            ['text' => 'U zápisů 8080:80, 127.0.0.1:8080:80 a 443:8443 urči port hostitele, port v kontejneru a odkud je služba dostupná.', 'output' => 'Tabulka tří mapování.', 'time' => '15 min'],
            ['text' => 'Rozhodni pro databázi, nahrané soubory, konfiguraci a heslo k databázi, kam patří (svazek, proměnná prostředí, tajné úložiště, obraz).', 'output' => 'Tabulka rozhodnutí se zdůvodněním.', 'time' => '15 min'],
            ['text' => 'V incidentu (hostitel 8080 otevřený, aplikace v kontejneru poslouchá jen na 127.0.0.1:3000) navrhni nejmenší opravu a ověření.', 'output' => 'Oprava (poslouchat na 0.0.0.0:3000 a mapovat 8080:3000) + test.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane diagram k doplnění a tabulku „přežije znovuvytvoření? ano/ne“.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Vysvětlí, proč se orchestrace (více instancí) zavádí až po pochopení jedné instance.',
        ],
        'assessment' => [
            'formative' => ['Přežije, nebo zmizí? (učitel jmenuje data)', 'Mapování na tabuli: odkud se k 127.0.0.1:8080:80 dostanu?'],
            'rubric' => [
                ['criterion' => 'Model kontejneru', 'levels' => ['Kontejner = virtuál.', 'Plete obraz a kontejner.', 'Obraz, kontejner, svazek, síť správně.', 'Vysvětlí izolaci procesu.']],
                ['criterion' => 'Porty a data', 'levels' => ['Nerozliší porty.', 'Porty ano, data ne.', 'Porty i trvalá data správně.', 'Tajné údaje mimo obraz se zdůvodněním.']],
                ['criterion' => 'Diagnostika', 'levels' => ['Restartuje.', 'Najde příčinu bez opravy.', 'Nejmenší oprava + ověření.', 'Navrhne kontrolu zdraví, která chybu odhalí.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Kontejnery', 'variants' => [
            ['question' => 'Co se typicky stane s daty uloženými jen uvnitř kontejneru po jeho znovuvytvoření?', 'options' => ['Uloží se do obrazu.', 'Ztratí se – trvalá data patří do svazku.', 'Přesunou se do DNS.'], 'correct' => 1, 'explanation' => 'Zapisovatelná vrstva kontejneru je dočasná.'],
            ['question' => 'Zápis mapování 8080:80 znamená:', 'options' => ['Port 8080 na hostiteli vede na port 80 v kontejneru.', 'Port 80 na hostiteli vede na 8080 v kontejneru.', 'Kontejner má dva webové servery.'], 'correct' => 0, 'explanation' => 'Pořadí je hostitel:kontejner.'],
            ['question' => 'Kam patří heslo k databázi?', 'options' => ['Přímo do obrazu kontejneru.', 'Do veřejného repozitáře.', 'Do tajného úložiště nebo bezpečně předané konfigurace, ne do obrazu.'], 'correct' => 2, 'explanation' => 'Obraz se sdílí – tajné údaje by unikly.'],
        ]],
        'homework' => [['text' => 'Volitelné: přečti úvodní stránku oficiální dokumentace Docker nebo Podman o svazcích (volumes) a zapiš jednu větu vlastními slovy.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Kontejnery spouštíme jen v labu (Docker/Podman) nebo čistě simulujeme; žádné obrazy z neověřených zdrojů.', 'Tajné údaje nikdy do obrazu ani do repozitáře.'],
        'teacher_notes' => ['Lze realizovat v Dockeru/Podmanu nebo čistě simulovat; simulátor Linux Labu kontejnery nemá.', 'Otázka do třídy: Co přežije, když kontejner smažu a vytvořím znovu?', 'Tempo: nezaváděj orchestraci dřív, než žáci chápou jednu instanci.'],
        'substitution' => ['Zástup bez odborníka: celá hodina jde na papíře (diagram, tabulky, incident).', 'Plán B offline: karty „obraz / kontejner / svazek / síť“ a skládání diagramu.'],
        'glossary' => ['container', 'image', 'volume', 'port-mapping'],
    ],
    15 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 15 · Automatizace a jednotná konfigurace',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout idempotentní správcovský postup: zjistí aktuální stav, změní jen potřebné, při nesplněné podmínce bezpečně skončí a výsledek doloží.',
            'success_criteria' => ['Postup nejdřív zjistí aktuální stav a při splněném cíli nic nemění.', 'Před změnou ověří podmínky (správný počítač, záloha) a jinak bezpečně skončí.', 'Druhý průchod postupu proběhne bez změny – to doložím.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže skript, který při každém spuštění přidá stejný řádek do konfigurace.', 'student' => 'Popíšou, co se stane po 10 spuštěních.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Idempotentní změna', 'teacher' => 'Vysvětlí: zjistit stav → porovnat → změnit jen rozdíl.', 'student' => 'Přepíšou postup tak, aby byl idempotentní.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Podmínky', 'teacher' => 'Ukáže kontrolu počítače a zálohy před změnou.', 'student' => 'Doplní podmínky a bezpečné ukončení.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Odchylka', 'teacher' => 'Připomene drift z lekce 8.', 'student' => 'Porovnají deklarovaný a skutečný stav.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 72, 'phase' => 'Nanečisto', 'teacher' => 'Ukáže výstup režimu nanečisto (dry-run).', 'student' => 'Zkontrolují rozsah plánu před aplikací.', 'form' => 'jednotlivě'],
            ['from' => 72, 'to' => 90, 'phase' => 'Aplikace, ověření a exit ticket', 'teacher' => 'Zadá důkaz druhého průchodu.', 'student' => 'Popíšou ověření a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Přepiš postup „přidej řádek do konfigurace“ tak, aby byl idempotentní (nejdřív zjistí, zda řádek existuje).', 'output' => 'Pseudoskript se zjištěním stavu.', 'time' => '15 min'],
            ['text' => 'Doplň podmínky: správný počítač, existující záloha, kontrola konfigurace; při nesplnění bezpečně skonči s hláškou.', 'output' => 'Pseudoskript s podmínkami.', 'time' => '15 min'],
            ['text' => 'Porovnej deklarovaný a skutečný stav (připravená tabulka) a urči zdroj pravdy.', 'output' => 'Seznam odchylek + rozhodnutí.', 'time' => '15 min'],
            ['text' => 'Popiš výstup režimu nanečisto (dry-run), aplikaci, měření výsledku a důkaz, že druhý průchod nic nezmění.', 'output' => 'Plán, ověření a záznam druhého průchodu.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kostru pseudoskriptu s komentáři „zjisti / porovnej / změň / ověř“.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Napíše skutečný krátký skript v bashi s grep -q a ověří ho v Linux Labu dvojím spuštěním.',
        ],
        'assessment' => [
            'formative' => ['Myšlenkový pokus: co udělá tvůj postup při druhém spuštění?', 'Kontrola podmínek: co se stane na špatném počítači?'],
            'rubric' => [
                ['criterion' => 'Idempotence', 'levels' => ['Mění vždy.', 'Zjistí stav, ale mění i tak.', 'Mění jen rozdíl.', 'Doloží druhý průchod bez změny.']],
                ['criterion' => 'Podmínky a bezpečné selhání', 'levels' => ['Žádné.', 'Podmínky bez ukončení.', 'Podmínky + bezpečné ukončení s hláškou.', 'Ověří i zálohu a návrat.']],
                ['criterion' => 'Nanečisto a ověření', 'levels' => ['Rovnou aplikuje.', 'Nanečisto bez kontroly rozsahu.', 'Nanečisto, aplikace, měření.', 'Funkční skript ověřený v labu.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Automatizace', 'variants' => [
            ['question' => 'Co znamená, že je postup idempotentní?', 'options' => ['Opakované spuštění vede ke stejnému výsledku a nic navíc nemění.', 'Postup běží jen jednou za den.', 'Postup se nedá zastavit.'], 'correct' => 0, 'explanation' => 'Druhý průchod = žádná změna.'],
            ['question' => 'Který skript je pro produkční server nejrizikovější?', 'options' => ['Skript s režimem nanečisto a kontrolou podmínek.', 'Skript, který po změně ověří výsledek.', 'Skript, který rychle a opakovaně mění konfiguraci bez kontrol.'], 'correct' => 2, 'explanation' => 'Automat bez pojistek násobí chyby.'],
            ['question' => 'Skript zjistí, že běží na jiném počítači, než má. Co má udělat?', 'options' => ['Pokračovat, změna je stejná.', 'Bezpečně skončit s jasnou hláškou a nic nezměnit.', 'Restartovat počítač.'], 'correct' => 1, 'explanation' => 'Nesplněná podmínka = bezpečné ukončení.'],
        ]],
        'homework' => [['text' => 'Volitelné: napiš pseudoskript „ranní rutina“ tak, aby se při druhém spuštění nic neopakovalo (např. snídaně už snědená).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Zadání je defenzivní a probíhá v sandboxu (Linux Lab); skripty nespouštíme na školních serverech.', 'Ve skriptech nejsou hesla – jen odkazy na bezpečné úložiště.'],
        'teacher_notes' => ['Hodnoť bezpečné selhání a idempotenci, ne délku skriptu.', 'Otázka do třídy: Co udělá tvůj skript, když ho omylem spustíš dvakrát?', 'Tempo: výzvu (skutečný skript) nabídni jen rychlejším.'],
        'substitution' => ['Zástup bez odborníka: celá hodina na papíře (pseudoskripty a tabulky).', 'Plán B offline: postup jako vývojový diagram na papíře.'],
        'glossary' => ['idempotence', 'dry-run', 'drift'],
    ],
    16 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 16 · Pokročilá diagnostika sítě: pakety a stav spojení',
        'goal' => [
            'student' => 'Na konci hodiny umím spojit záznam provozu, stavy TCP a naslouchání na serveru do jedné hypotézy incidentu a jasně říct, co už vím a co ještě ne.',
            'success_criteria' => ['Rozliším vzorce SYN bez odpovědi, SYN → RST a SYN → SYN-ACK.', 'Porovnám důkaz z provozu s naslouchajícími sockety na serveru (ss).', 'Napíšu hranici příčiny: co dokazuji a jaký test přijde dál.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše incident: aplikace se občas nepřipojí k API.', 'student' => 'Formulují první hypotézu.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Záznam s otázkou', 'teacher' => 'Ukáže, jak hypotéza určuje filtr.', 'student' => 'Navrhnou filtr jen na potřebný provoz.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Vzorce SYN', 'teacher' => 'Ukáže tři vzorce na připraveném záznamu.', 'student' => 'V simulátoru vyzkouší nc na tři cíle a přiřadí vzorce.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Socket na serveru', 'teacher' => 'Předvede ss -tln.', 'student' => 'Porovnají naslouchání s výsledky testů.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 72, 'phase' => 'Po TCP', 'teacher' => 'Zdůrazní: když TCP funguje, pokračuj TLS/HTTP.', 'student' => 'Navrhnou další vrstvu testů podle příznaku.', 'form' => 'jednotlivě'],
            ['from' => 72, 'to' => 90, 'phase' => 'Časová osa a exit ticket', 'teacher' => 'Zadá hranici příčiny.', 'student' => 'Seřadí důkazy podle času, napíšou hranici a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'K incidentu napiš hypotézu a z ní odvoď filtr záznamu provozu (display filter) jen na potřebný port a cíl.', 'output' => 'Hypotéza + filtr (např. tcp.port == 8443 and ip.addr == 10.0.0.10).', 'time' => '15 min'],
            ['text' => 'V Linux Labu vyzkoušej nc -zv 10.0.0.10 80, nc -zv 10.0.0.10 443 a nc -zv 10.0.0.99 443 a přiřaď vzorce SYN-ACK, RST a „bez odpovědi“.', 'output' => 'succeeded = SYN-ACK; Connection refused = RST; Connection timed out = bez odpovědi.', 'time' => '15 min',
                'sim' => [['cmd' => 'nc -zv 10.0.0.10 80', 'expect' => 'succeeded'], ['cmd' => 'nc -zv 10.0.0.10 443', 'expect' => 'Connection refused'], ['cmd' => 'nc -zv 10.0.0.99 443', 'expect' => 'Connection timed out']]],
            ['text' => 'Porovnej výpis ss -tln s výsledky testů a vysvětli, proč port 443 vrací RST.', 'output' => 'Na lab-pc poslouchají jen 22 a 80; na 443 nikdo nenaslouchá → RST.', 'time' => '15 min',
                'sim' => [['cmd' => 'ss -tln', 'expect' => '0.0.0.0:80']]],
            ['text' => 'Seřaď důkazy z provozu a serveru podle času a napiš hranici příčiny: co víš, co nevíš a jaký test přijde dál.', 'output' => 'Časová osa + hranice příčiny + další test.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartu tří vzorců SYN s ilustrací a tabulku výsledků nc.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Popíše, jak by v záznamu poznal ztrátu paketů (opakované SYN) a jak ji odlišit od filtrování.',
        ],
        'assessment' => [
            'formative' => ['Vzorec na tabuli: SYN → RST – kdo odpověděl?', 'Hranice příčiny: co tvůj důkaz nevylučuje?'],
            'rubric' => [
                ['criterion' => 'Vzorce TCP', 'levels' => ['Nerozliší.', 'Rozliší jen úspěch.', 'Tři vzorce správně.', 'Pozná i opakované SYN (ztráta).']],
                ['criterion' => 'Propojení se serverem', 'levels' => ['Bez ss.', 'ss bez vztahu k testům.', 'Naslouchání vysvětlí výsledky.', 'Navrhne test navázání na adresu.']],
                ['criterion' => 'Hranice příčiny', 'levels' => ['Tvrdí bez důkazu.', 'Jen co ví.', 'Co ví, co neví, další test.', 'Časová osa z více zdrojů.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Diagnostika sítě', 'variants' => [
            ['question' => 'Klient posílá SYN a nepřichází žádná odpověď. Co to nejspíš znamená?', 'options' => ['Port je otevřený.', 'Cíl je nedostupný nebo provoz zahazuje filtr cestou.', 'Server spojení aktivně odmítl.'], 'correct' => 1, 'explanation' => 'Ticho ≠ odmítnutí.'],
            ['question' => 'TCP spojení se naváže (SYN-ACK), ale aplikace hlásí chybu. Kam pokračuješ?', 'options' => ['Do vyšších vrstev: TLS a HTTP podle příznaku.', 'Zpět k DHCP.', 'K výměně síťové karty.'], 'correct' => 0, 'explanation' => 'Vrstva TCP je ověřená – pokračuj nahoru.'],
            ['question' => 'Co je „hranice příčiny“ v záznamu incidentu?', 'options' => ['Seznam viníků.', 'Čas konce směny.', 'Jasné oddělení toho, co důkazy dokazují, od toho, co je ještě potřeba ověřit.'], 'correct' => 2, 'explanation' => 'Chrání před ukvapeným závěrem.'],
        ]],
        'homework' => [['text' => 'Volitelné: nakresli časovou osu jednoho TCP spojení (SYN, SYN-ACK, ACK, data, FIN) a popiš každý krok jednou větou.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Používáme předpřipravené záznamy nebo izolovaný lab; nezachytáváme citlivý provoz třetích osob.', 'Záznamy provozu se nesdílejí mimo třídu.'],
        'teacher_notes' => ['Používej předpřipravené záznamy nebo izolovaný lab; nezachytávej citlivý provoz třetích osob.', 'Otázka do třídy: Kdo poslal RST – a co to říká o síti?', 'Tempo: simulátor nemá záchyt paketů – vzorce ukazuj na připraveném záznamu, testy dělej přes nc.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–3 v Linux Labu (výstupy v zadání), úkoly 1 a 4 na papír.', 'Plán B offline: vytištěný záznam provozu a karty vzorců.'],
        'glossary' => ['syn-ack', 'rst', 'display-filter'],
    ],
]]];
