<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 4.A (provoz sítí a služeb), lekce 5–10.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php,
 * ověřované příkazy simulátoru (klíč `sim`) viz lesson_content_v72_3a_a.php.
 * Kompetence: 4.A zatím nemá katalog v62 (rozhodnutí školy, fáze E/v75) – exit ticket jen s popisem kompetence.
 * Zdroje norem: stavové kódy a hlavičky HTTP – RFC 9110 (HTTP Semantics); systemd – manuálové stránky systemd.unit(5) a systemd.service(5).
 */

return ['class_4a' => ['lessons' => [
    5 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 5 · Pozorovatelnost HTTP a vyvažování zátěže',
        'goal' => [
            'student' => 'Na konci hodiny umím číst odpověď HTTP jako diagnostický důkaz a vysvětlit, jak vyvažovač zátěže (load balancer) rozděluje provoz a vyřazuje nezdravý backend.',
            'success_criteria' => ['Ze stavového kódu a hlaviček (Server, Location, Retry-After) odvodím, co odpověď dokazuje.', 'Porovnám rozdělování round-robin a vážené a popíšu jejich dopad.', 'Navrhnu kontrolu zdraví /health s intervalem a prahem vyřazení.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže tři odpovědi HTTP (200, 301, 503) bez kontextu.', 'student' => 'Odhadnou, co se na serveru děje.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Stav není jen číslo', 'teacher' => 'Vysvětlí třídy 2xx–5xx a hlavičky Location a Retry-After (RFC 9110) jako základ pozorovatelnosti (observability).', 'student' => 'Ke každé třídě napíšou, co dokazuje.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 38, 'phase' => 'Rychlá sonda curl -I', 'teacher' => 'Předvede curl -I v Linux Labu.', 'student' => 'Přečtou stav a hlavičky odpovědi.', 'form' => 'jednotlivě'],
            ['from' => 38, 'to' => 55, 'phase' => 'Rozdělení provozu', 'teacher' => 'Předvede lab vyvažování (round-robin, vážené).', 'student' => 'Sledují rozložení požadavků a vyřadí nezdravý backend.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 78, 'phase' => 'Kontrola zdraví', 'teacher' => 'Ukáže rozdíl „port otevřen“ a „/health vrací 200“.', 'student' => 'Navrhnou kontrolu zdraví s intervalem a prahem.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Mini postmortem a exit ticket', 'teacher' => 'Zadá formát příznak → důkaz → prevence.', 'student' => 'Sepíšou mini postmortem a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Ke třídám stavů 2xx, 3xx, 4xx a 5xx napiš, co odpověď dokazuje o klientovi a serveru.', 'output' => 'Tabulka čtyř tříd s významem.', 'time' => '12 min'],
            ['text' => 'V Linux Labu spusť curl -I http://localhost a zapiš stav a hlavičku Server.', 'output' => '„HTTP/1.1 200 OK“ a „Server: nginx/1.22.1“.', 'time' => '13 min',
                'sim' => [['cmd' => 'curl -I http://localhost', 'expect' => 'HTTP/1.1 200 OK'], ['cmd' => 'curl -I http://localhost', 'expect' => 'Server: nginx/1.22.1']]],
            ['text' => 'V labu vyvažování porovnej round-robin a vážené rozdělení a vyřaď backend, který vrací 500.', 'output' => 'Záznam rozložení požadavků před a po vyřazení.', 'time' => '17 min'],
            ['text' => 'Navrhni kontrolu zdraví (health check): adresa /health, interval, timeout, počet neúspěchů do vyřazení a do návratu.', 'output' => 'Specifikace kontroly zdraví.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku stavových kódů s příklady a šablonu kontroly zdraví.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne, co má /health kontrolovat uvnitř aplikace (např. databázi) a proč ne všechno.',
        ],
        'assessment' => [
            'formative' => ['Karty stavů: učitel řekne kód, třída ukáže „klient / server / přesměrování“.', 'Kontrola návrhu: vyřadí tvoje kontrola backend, který má otevřený port, ale vrací 500?'],
            'rubric' => [
                ['criterion' => 'Čtení HTTP', 'levels' => ['Jen „funguje / nefunguje“.', 'Zná třídy, ne hlavičky.', 'Stav + hlavičky = konkrétní závěr.', 'Navrhne další sondu podle odpovědi.']],
                ['criterion' => 'Vyvažování zátěže', 'levels' => ['Nerozumí.', 'Popíše jen round-robin.', 'Porovná algoritmy a vyřazení.', 'Zdůvodní volbu pro konkrétní situaci.']],
                ['criterion' => 'Kontrola zdraví', 'levels' => ['Jen port.', 'HTTP bez prahů.', 'HTTP /health s intervalem a prahy.', 'Řeší i návrat backendu a falešné poplachy.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Provoz webových služeb', 'variants' => [
            ['question' => 'Odpověď 503 s hlavičkou Retry-After: 120 říká:', 'options' => ['Stránka neexistuje.', 'Služba je dočasně nedostupná a klient to může zkusit znovu zhruba za 120 sekund.', 'Klient nemá oprávnění.'], 'correct' => 1, 'explanation' => '503 = dočasná nedostupnost, Retry-After = kdy zkusit znovu.'],
            ['question' => 'Proč nestačí kontrola zdraví „port 443 je otevřený“?', 'options' => ['Port může přijímat spojení, i když aplikace vrací chyby.', 'Port 443 se nedá testovat.', 'Protože kontrola portu je pomalejší.'], 'correct' => 0, 'explanation' => 'Otevřený port ≠ zdravá aplikace.'],
            ['question' => 'Backend A má váhu 3, backend B váhu 1. Kolik ze 100 požadavků dostane zhruba B?', 'options' => ['50', '75', '25'], 'correct' => 2, 'explanation' => 'Váhy 3 : 1 → B dostane 1/4.'],
        ]],
        'homework' => [['text' => 'Volitelné: v nástrojích vývojáře prohlížeče (záložka Síť) najdi u libovolného webu jeden požadavek se stavem 3xx a zapiš hlavičku Location.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Sondy (curl) posíláme jen na lab nebo na servery, které spravujeme; žádné zátěžové testy cizích služeb.', 'Nástroje vývojáře jen ke čtení.'],
        'teacher_notes' => ['Častý omyl: 5xx = vždy chyba sítě. Je to chyba na straně serveru/aplikace.', 'Otázka do třídy: Co tahle odpověď dokazuje a co ještě ne?', 'Tempo: lab vyvažování je atraktivní, hlídej 17 minut – hlavní výstup je kontrola zdraví.'],
        'substitution' => ['Zástup bez odborníka: úkol 2 v Linux Labu (výstup v zadání), úkoly 1, 3 a 4 na papír.', 'Plán B offline: karty odpovědí HTTP a papírová simulace rozdělování požadavků (kartičky).'],
        'glossary' => ['load-balancer', 'round-robin', 'health-check', 'observability'],
    ],
    6 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 6 · Postupné nasazení a nácvik obnovy ze zálohy',
        'goal' => [
            'student' => 'Na konci hodiny umím naplánovat postupné (kanárkové) nasazení s podmínkou zastavení a rozhodnout mezi návratem verze a obnovou ze zálohy podle měřitelných signálů.',
            'success_criteria' => ['Před změnou zapíšu výchozí chybovost, p95 latence a stav poslední použitelné zálohy.', 'Definuji podmínku zastavení kanárku předem, ne až během problému.', 'Popíšu nácvik obnovy do testovacího cíle s kontrolou funkčnosti.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše nasazení „všem najednou“, které shodilo web.', 'student' => 'Navrhnou, jak riziko zmenšit.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Než změníš produkci', 'teacher' => 'Vysvětlí výchozí hodnoty (baseline).', 'student' => 'Zapíší chybovost, p95 a poslední zálohu.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 38, 'phase' => 'Kanárek místo velkého třesku', 'teacher' => 'Předvede lab: 10 % provozu na v2.', 'student' => 'Porovnají v1 a v2 a definují podmínku zastavení.', 'form' => 've dvojicích'],
            ['from' => 38, 'to' => 55, 'phase' => 'Rozhodnutí podle dat', 'teacher' => 'Ukáže tři scénáře metrik.', 'student' => 'Rozhodnou: rozšířit, držet, nebo vrátit.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 78, 'phase' => 'Nácvik obnovy', 'teacher' => 'Vysvětlí výběr zálohy, kontrolu integrity a obnovu do testu.', 'student' => 'Projdou nácvik obnovy v labu a ověří funkčnost.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Runbook a exit ticket', 'teacher' => 'Zadá runbook nasazení a návratu.', 'student' => 'Sepíšou 5 kroků a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Před změnou zapiš výchozí chybovost, p95 latence a datum poslední zálohy, kterou umíš obnovit.', 'output' => 'Tabulka výchozích hodnot.', 'time' => '12 min'],
            ['text' => 'Nastav v labu kanárkové nasazení (canary) 10 % provozu na v2 a předem zapiš podmínku zastavení (např. chybovost 2× nad výchozí po 5 minut).', 'output' => 'Podmínka zastavení + porovnání v1/v2.', 'time' => '16 min'],
            ['text' => 'U tří scénářů metrik rozhodni: rozšířit, držet, nebo vrátit (rollback), a zdůvodni podle dat.', 'output' => 'Tři rozhodnutí se zdůvodněním.', 'time' => '17 min'],
            ['text' => 'Projdi nácvik obnovy (restore): vyber zálohu, ověř integritu, obnov do testovacího cíle a proveď funkční kontrolu.', 'output' => 'Záznam nácviku ve 4 krocích.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku „signál → rozhodnutí“ a šablonu runbooku.',
            'standard' => 'Úkoly 1–4 a runbook podle zadání.',
            'challenge' => 'Popíše situaci, kdy návrat verze nestačí (změněná data) a je potřeba obnova ze zálohy.',
        ],
        'assessment' => [
            'formative' => ['Hlasování: scénář metrik na tabuli – rozšířit / držet / vrátit?', 'Kontrola podmínky: je podmínka zastavení měřitelná?'],
            'rubric' => [
                ['criterion' => 'Výchozí stav', 'levels' => ['Chybí.', 'Jen jedna metrika.', 'Chybovost, p95, poslední obnovitelná záloha.', 'Zdůvodní, proč právě tyto metriky.']],
                ['criterion' => 'Kanárek a rozhodnutí', 'levels' => ['Velký třesk.', 'Kanárek bez podmínky.', 'Měřitelná podmínka a správná rozhodnutí.', 'Odliší šum od trendu.']],
                ['criterion' => 'Obnova', 'levels' => ['Jen „máme zálohu“.', 'Obnova bez kontroly.', 'Výběr, integrita, test, funkční kontrola.', 'Odliší rollback od restore.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Řízené změny', 'variants' => [
            ['question' => 'Proč se podmínka zastavení kanárku píše předem?', 'options' => ['Aby se pod tlakem nerozhodovalo podle dojmu a zastavení bylo rychlé.', 'Protože to vyžaduje zákon.', 'Aby kanárek běžel déle.'], 'correct' => 0, 'explanation' => 'Předem dohodnuté kritérium = rychlé a nestranné rozhodnutí.'],
            ['question' => 'Nová verze poškodila data v databázi. Co pomůže víc?', 'options' => ['Jen návrat na starou verzi aplikace.', 'Obnova dat ze zálohy (a návrat verze).', 'Restart serveru.'], 'correct' => 1, 'explanation' => 'Rollback vrátí kód, ne data.'],
            ['question' => 'Kdy je záloha opravdu důvěryhodná?', 'options' => ['Když je soubor dost velký.', 'Když úloha zálohy skončila bez chyby.', 'Když byla obnovena a výsledek ověřen.'], 'correct' => 2, 'explanation' => 'Důkazem je úspěšná obnova.'],
        ]],
        'homework' => [['text' => 'Volitelné: zjisti, jak často se zálohuje tvůj telefon, a zkus odhadnout, kolik dat bys při ztrátě přišel.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Nasazení a obnova jen v labu; produkční data školy se nikdy neobnovují pro cvičení.', 'Zálohy mohou obsahovat osobní údaje – pracujeme jen s vymyšlenými daty.'],
        'teacher_notes' => ['Častý omyl: „rozšíříme na 100 %, ať máme víc dat“. Data máme – rozhodujeme podle podmínky.', 'Otázka do třídy: Co vrátí rollback a co ne?', 'Tempo: nácvik obnovy je nejdelší – scénáře metrik mohou být rychlé hlasování.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 3 na papír, úkoly 2 a 4 v labu podle zadání.', 'Plán B offline: scénáře metrik na kartách a nácvik obnovy jako papírový checklist.'],
        'glossary' => ['canary', 'rollback', 'restore', 'p95'],
    ],
    7 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 7 · Cíle spolehlivosti, rozpočet chyb a rozbor incidentu',
        'goal' => [
            'student' => 'Na konci hodiny umím převést cíl spolehlivosti (SLO) na rozpočet chyb v minutách, rozhodnout podle něj o dalších změnách a sepsat rozbor incidentu bez hledání viníka.',
            'success_criteria' => ['Spočítám rozpočet chyb pro SLO 99,9 % za 30 dní (43,2 minuty).', 'Seřadím časovou osu incidentu a oddělím fakta od domněnek.', 'Navrhnu tři opatření s vlastníkem a termínem.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Zeptá se: je 99 % dostupnost hodně, nebo málo?', 'student' => 'Odhadnou, kolik hodin výpadku to je za měsíc.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'SLI → SLO', 'teacher' => 'Předvede SLO Lab a ukazatel z pohledu uživatele.', 'student' => 'Vyberou ukazatel (SLI) a nastaví 30denní SLO.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Rozpočet chyb', 'teacher' => 'Spočítá s třídou rozpočet pro 99,9 %.', 'student' => 'Spočítají rozpočet a porovnají s incidentem.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 57, 'phase' => 'Časová osa důkazů', 'teacher' => 'Rozdá kartičky událostí incidentu.', 'student' => 'Seřadí upozornění, nasazení, příznak a návrat a označí rozhodovací bod.', 'form' => 've dvojicích'],
            ['from' => 57, 'to' => 80, 'phase' => 'Rozbor bez viníka', 'teacher' => 'Vysvětlí blameless postmortem.', 'student' => 'Sepíšou příčinu, přispívající faktory a 3 opatření.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Rozhodnutí o vydání a exit ticket', 'teacher' => 'Zeptá se: pustíme další změnu?', 'student' => 'Rozhodnou podle rozpočtu a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Vyber ukazatel spolehlivosti z pohledu uživatele (SLI) pro školní web a nastav 30denní cíl (SLO).', 'output' => 'SLI (např. podíl úspěšných požadavků) a SLO v %.', 'time' => '15 min'],
            ['text' => 'Spočítej rozpočet chyb (error budget) pro SLO 99,9 % a 99,5 % za 30 dní v minutách.', 'output' => '99,9 % → 43,2 min; 99,5 % → 216 min.', 'time' => '15 min'],
            ['text' => 'Seřaď kartičky incidentu na časovou osu, odděl fakta od domněnek a označ rozhodovací bod.', 'output' => 'Časová osa s označenými fakty a domněnkami.', 'time' => '17 min'],
            ['text' => 'Sepiš rozbor incidentu bez hledání viníka (blameless postmortem): příčina, přispívající faktory a 3 opatření s vlastníkem a termínem.', 'output' => 'Rozbor na 1 stranu.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzorec rozpočtu (1 − SLO) × 30 × 24 × 60 a šablonu rozboru.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne pravidlo „když je spotřebováno 50 % rozpočtu do poloviny měsíce, zpomalíme změny“ a zdůvodní ho.',
        ],
        'assessment' => [
            'formative' => ['Rychlý výpočet na tabuli: 99,9 % za 30 dní = ? minut.', 'Fakt, nebo domněnka? Učitel čte věty z rozboru.'],
            'rubric' => [
                ['criterion' => 'SLI a SLO', 'levels' => ['Jen „uptime“.', 'SLI bez pohledu uživatele.', 'Uživatelský SLI a rozumné SLO.', 'Zdůvodní cíl potřebami uživatelů.']],
                ['criterion' => 'Rozpočet chyb', 'levels' => ['Nespočítá.', 'Výpočet s chybou.', 'Správně pro 99,9 % i 99,5 %.', 'Navrhne pravidlo podle spotřeby rozpočtu.']],
                ['criterion' => 'Rozbor incidentu', 'levels' => ['Hledá viníka.', 'Jen popis.', 'Příčina, faktory, 3 opatření s vlastníkem.', 'Opatření jsou měřitelná.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Spolehlivost a incidenty', 'variants' => [
            ['question' => 'Kolik minut výpadku za 30 dní dovoluje SLO 99,9 %?', 'options' => ['43,2 minuty', '4,32 minuty', '432 minut'], 'correct' => 0, 'explanation' => '0,001 × 30 × 24 × 60 = 43,2.'],
            ['question' => 'Co je hlavní myšlenka rozboru incidentu „bez viníka“?', 'options' => ['Nikdo nic nezapisuje.', 'Hledáme, co v systému a postupech chybu umožnilo, ne koho potrestat.', 'Rozbor dělá jen vedení.'], 'correct' => 1, 'explanation' => 'Lidé pak mluví otevřeně a opatření míří na systém.'],
            ['question' => 'Který ukazatel je nejblíž zkušenosti uživatele?', 'options' => ['Vytížení procesoru serveru.', 'Počet restartů za týden.', 'Podíl požadavků, které uživatel dostal úspěšně a dost rychle.'], 'correct' => 2, 'explanation' => 'SLI měří to, co uživatel zažívá.'],
        ]],
        'homework' => [['text' => 'Volitelné: spočítej, kolik minut výpadku za rok dovoluje SLO 99,95 %.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Incident je vymyšlený; v rozboru nepoužíváme jména skutečných lidí.', 'Rozbory se sdílejí jen ve třídě.'],
        'teacher_notes' => ['Častý omyl: 99 % zní skvěle – přepočítej na hodiny (7,2 h za 30 dní).', 'Otázka do třídy: Je tohle fakt, nebo náš výklad?', 'Tempo: výpočty jsou rychlé, nech čas na rozbor ve dvojicích.'],
        'substitution' => ['Zástup bez odborníka: celá hodina jde na papíře (výsledky výpočtů jsou v zadání).', 'Plán B offline: kartičky incidentu a kalkulačka.'],
        'glossary' => ['slo', 'sli', 'error-budget', 'postmortem'],
    ],
    8 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 8 · Infrastruktura jako kód a odchylka konfigurace',
        'goal' => [
            'student' => 'Na konci hodiny umím popsat konfiguraci jako verzovaný požadovaný stav (infrastruktura jako kód, IaC), přečíst plán změn před aplikací a bezpečně vyřešit odchylku konfigurace (drift).',
            'success_criteria' => ['Určím zdroj pravdy (repozitář) a požadovaný stav.', 'V plánu změn najdu neočekávanou změnu a odhadnu dosah (blast radius).', 'U driftu rozhodnu, zda platí repozitář, nebo běžící stav, a navrhnu sjednocení s ověřením.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Popíše dva servery, které „mají být stejné“, a nejsou.', 'student' => 'Odhadnou, jak to vzniklo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Požadovaný stav', 'teacher' => 'Předvede lab Plan → Apply.', 'student' => 'Popíšou požadovaný stav a zdroj pravdy.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Plán a rozdíl', 'teacher' => 'Ukáže, jak číst plán změn.', 'student' => 'Najdou neočekávanou změnu a odhadnou dosah.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Kontrola před aplikací', 'teacher' => 'Vysvětlí malý rozsah, kritéria úspěchu a návrat.', 'student' => 'Rozdělí změnu na menší kroky.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Odchylka a sjednocení', 'teacher' => 'Předvede lab Drift Detection.', 'student' => 'Porovnají požadovaný a skutečný stav a navrhnou sjednocení.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Shrnutí a exit ticket', 'teacher' => 'Shrne bezpečné výchozí chování.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V labu Plan → Apply popiš požadovaný stav jedné služby a urči zdroj pravdy (source of truth).', 'output' => 'Popis požadovaného stavu + zdroj pravdy.', 'time' => '15 min'],
            ['text' => 'Přečti plán změn (diff), najdi neočekávanou změnu a odhadni její dosah (blast radius).', 'output' => 'Označená změna + odhad dosahu.', 'time' => '15 min'],
            ['text' => 'Rozděl velkou změnu na menší kroky, ke každému napiš kritérium úspěchu a postup návratu.', 'output' => 'Plán kroků s kritérii a návratem.', 'time' => '15 min'],
            ['text' => 'V labu Drift Detection porovnej požadovaný a skutečný stav, zjisti původ odchylky a navrhni sjednocení s ověřením.', 'output' => 'Rozhodnutí o zdroji pravdy + kroky sjednocení + test.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane plán změn s barevně odlišenými řádky (přidat / změnit / smazat) a otázky k němu.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne kontrolu, která bude drift hlásit pravidelně (např. denně), a co s nálezem udělat.',
        ],
        'assessment' => [
            'formative' => ['Ukaž změnu: učitel promítne plán, třída ukáže řádek „smazat“.', 'Otázka: kdo ručně změnil server a jak to zjistíme?'],
            'rubric' => [
                ['criterion' => 'Požadovaný stav', 'levels' => ['Nerozumí.', 'Popíše, chybí zdroj pravdy.', 'Požadovaný stav + zdroj pravdy.', 'Vysvětlí výhodu verzování.']],
                ['criterion' => 'Čtení plánu', 'levels' => ['Aplikuje bez čtení.', 'Čte, nevidí riziko.', 'Najde neočekávanou změnu a dosah.', 'Navrhne rozdělení změny.']],
                ['criterion' => 'Drift', 'levels' => ['Přepíše naslepo.', 'Sjednotí bez ověření.', 'Rozhodne o zdroji pravdy a ověří.', 'Navrhne pravidelnou detekci.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Řízené změny', 'variants' => [
            ['question' => 'Co je odchylka konfigurace (drift)?', 'options' => ['Rozdíl mezi požadovaným stavem v repozitáři a skutečným stavem serveru.', 'Pomalé síťové připojení.', 'Nová verze aplikace.'], 'correct' => 0, 'explanation' => 'Drift vzniká typicky ruční změnou mimo repozitář.'],
            ['question' => 'V plánu změn vidíš „smazat databázi“, i když jsi měnil jen DNS záznam. Co uděláš?', 'options' => ['Aplikuji, plán se nemýlí.', 'Zastavím, zjistím příčinu a změnu neaplikuji, dokud ji nevysvětlím.', 'Aplikuji v noci, kdy nikdo nepracuje.'], 'correct' => 1, 'explanation' => 'Neočekávaná změna = stop.'],
            ['question' => 'Co znamená „dosah změny“ (blast radius)?', 'options' => ['Rychlost aplikace změny.', 'Velikost repozitáře.', 'Kolik systémů a uživatelů změna ovlivní, když se pokazí.'], 'correct' => 2, 'explanation' => 'Malý dosah = menší riziko.'],
        ]],
        'homework' => [['text' => 'Volitelné: zapiš, co je „požadovaný stav“ tvého pokoje nebo stolu a jaké odchylky obvykle vznikají.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Změny aplikujeme jen v labu; produkční konfigurace školy se nemění.', 'Do repozitáře nepatří hesla ani klíče – jen odkazy na bezpečné úložiště.'],
        'teacher_notes' => ['Častý omyl: „IaC = skript, který jednou spustím“. Zdůrazni opakovatelnost a review.', 'Otázka do třídy: Kdo a kdy změnil server mimo repozitář?', 'Tempo: lab Drift Detection je klíčový – úkol 3 může být kratší.'],
        'substitution' => ['Zástup bez odborníka: vytištěný plán změn a tabulka požadovaného/skutečného stavu, úkoly 2–4 na papír.', 'Plán B offline: porovnání dvou vytištěných konfigurací.'],
        'glossary' => ['iac', 'drift', 'blast-radius', 'source-of-truth'],
    ],
    9 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 9 · Výkon a plánování kapacity',
        'goal' => [
            'student' => 'Na konci hodiny umím najít úzké hrdlo z latence, propustnosti (throughput) a vytížení a převést měření na plán kapacity s rezervou.',
            'success_criteria' => ['Porovnám p50 a p95 a vysvětlím, co znamená „dlouhý ocas“ latence.', 'Najdu zdroj, jehož vytížení roste spolu s latencí.', 'Spočítám potřebnou kapacitu s rezervou (cíl × (1 + rezerva)).'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže průměrnou odezvu 200 ms a stížnosti uživatelů.', 'student' => 'Odhadnou, proč průměr klame.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Výchozí měření', 'teacher' => 'Předvede Latency & Saturation Lab.', 'student' => 'Zapíší p50, p95 a chybovost.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Percentily', 'teacher' => 'Vysvětlí percentily na příkladu 20 požadavků.', 'student' => 'Spočítají p50 a p95 z malého vzorku.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Úzké hrdlo', 'teacher' => 'Ukáže grafy CPU, I/O a fronty.', 'student' => 'Najdou korelaci s latencí a napíšou hypotézu.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Kapacita a rezerva', 'teacher' => 'Předvede Capacity & Headroom Lab.', 'student' => 'Najdou bod degradace a spočítají potřebnou kapacitu.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Rozhodnutí a exit ticket', 'teacher' => 'Porovná škálování nahoru, do šířky a optimalizaci.', 'student' => 'Rozhodnou pro svou situaci a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'V labu zapiš výchozí p50, p95 a chybovost a jeden signál vytížení zdroje.', 'output' => 'Tabulka výchozího měření.', 'time' => '15 min'],
            ['text' => 'Z 20 seřazených doby odezvy urči p50 (10.–11. hodnota) a p95 (19. hodnota) a vysvětli rozdíl.', 'output' => 'p50, p95 a věta o dlouhém ocasu (tail latency).', 'time' => '15 min'],
            ['text' => 'Najdi zdroj (CPU, disk, fronta), jehož vytížení roste spolu s latencí, a napiš hypotézu úzkého hrdla.', 'output' => 'Hypotéza + důkaz z grafu.', 'time' => '15 min'],
            ['text' => 'Spočítej potřebnou kapacitu pro cíl 800 požadavků/s s rezervou (headroom) 30 % a porovnej s bodem degradace z labu.', 'output' => '800 × 1,3 = 1040 požadavků/s + rozhodnutí, zda stačí současný stav.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane seřazený vzorek s vyznačenými pozicemi percentilů a vzorec kapacity.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Navrhne test výpadku jednoho z N serverů a spočítá, zda zbylé servery cíl unesou.',
        ],
        'assessment' => [
            'formative' => ['Tabule: p95 z deseti hodnot – kdo to zvládne?', 'Hypotéza nahlas: co ji vyvrátí?'],
            'rubric' => [
                ['criterion' => 'Percentily', 'levels' => ['Jen průměr.', 'Spočítá s chybou.', 'Správně p50 a p95 a význam.', 'Vysvětlí dopad dlouhého ocasu na uživatele.']],
                ['criterion' => 'Úzké hrdlo', 'levels' => ['Hádá.', 'Ukáže graf bez vztahu.', 'Hypotéza s korelací.', 'Navrhne test, který hypotézu ověří.']],
                ['criterion' => 'Kapacita', 'levels' => ['Bez výpočtu.', 'Bez rezervy.', 'Cíl × (1 + rezerva) a porovnání.', 'Zahrne i výpadek jednoho serveru.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Výkon a kapacita', 'variants' => [
            ['question' => 'Proč sledujeme p95 latence, a ne jen průměr?', 'options' => ['Průměr schová pomalé požadavky, které část uživatelů opravdu zažívá.', 'p95 se počítá rychleji.', 'Průměr je vždy vyšší než p95.'], 'correct' => 0, 'explanation' => 'Dlouhý ocas = reálná bolest části uživatelů.'],
            ['question' => 'Cíl je 600 požadavků/s a chceš rezervu 50 %. Jakou kapacitu potřebuješ?', 'options' => ['650 požadavků/s', '900 požadavků/s', '1200 požadavků/s'], 'correct' => 1, 'explanation' => '600 × 1,5 = 900.'],
            ['question' => 'Latence roste přesně ve chvílích, kdy roste fronta zápisů na disk. Co je nejlepší hypotéza?', 'options' => ['Pomalé DNS.', 'Málo paměti v prohlížeči klienta.', 'Úzkým hrdlem je disk (I/O).'], 'correct' => 2, 'explanation' => 'Korelace vytížení a latence ukazuje na hrdlo.'],
        ]],
        'homework' => [['text' => 'Volitelné: změř stopkami 10× dobu načtení jedné stránky, seřaď hodnoty a urči medián.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Zátěžové testy jen v labu – nikdy proti školním nebo cizím serverům.', 'Měření nesbírá osobní údaje uživatelů.'],
        'teacher_notes' => ['Častý omyl: rezerva se přičítá k současné, ne k cílové zátěži.', 'Otázka do třídy: Co zažívá těch 5 % nejpomalejších požadavků?', 'Tempo: výpočet percentilů ve dvojicích drž na 15 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2 a 4 na papír (výsledky v zadání), labové úkoly podle návodu.', 'Plán B offline: vzorek časů na kartičkách, ruční seřazení.'],
        'glossary' => ['p95', 'tail-latency', 'headroom', 'throughput'],
    ],
    10 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 10 · systemd do hloubky: závislosti, restart a selhání',
        'goal' => [
            'student' => 'Na konci hodiny umím rozebrat soubor jednotky služby, odlišit pořadí startu od závislosti a bezpečně řešit opakované selhání bez smyčky restartů.',
            'success_criteria' => ['V jednotce najdu sekce [Unit], [Service], [Install] a klíče ExecStart, User a Restart.', 'Vysvětlím rozdíl After= (pořadí) a Requires= / Wants= (závislost).', 'Navrhnu minimální úpravu (drop-in) s návratem a ověřením po daemon-reload.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže službu, která padá a systemd ji každou sekundu restartuje.', 'student' => 'Odhadnou, proč restart nepomáhá.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Anatomie jednotky', 'teacher' => 'Rozebere ukázkovou jednotku (systemd.unit, systemd.service).', 'student' => 'Najdou sekce a klíčové řádky.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Závislosti', 'teacher' => 'Vysvětlí After=, Wants= a Requires=.', 'student' => 'Nakreslí řetěz závislostí webové služby.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 57, 'phase' => 'Smyčka restartů (restart loop)', 'teacher' => 'Předvede čtení statusu a logu v Linux Labu.', 'student' => 'Z logu zjistí, proč proces padá.', 'form' => 'jednotlivě'],
            ['from' => 57, 'to' => 78, 'phase' => 'Minimální úprava', 'teacher' => 'Ukáže drop-in místo kopie celé jednotky.', 'student' => 'Navrhnou drop-in s návratem.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Ověření a exit ticket', 'teacher' => 'Shrne: daemon-reload → status → log → kontrola zdraví.', 'student' => 'Zapíší postup ověření a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V Linux Labu zjisti ze systemctl status nginx, kde leží soubor jednotky a zda je služba povolená po startu.', 'output' => '„Loaded: loaded (/lib/systemd/system/nginx.service; enabled …)“.', 'time' => '12 min',
                'sim' => [['cmd' => 'systemctl status nginx', 'expect' => 'Loaded: loaded (/lib/systemd/system/nginx.service; enabled']]],
            ['text' => 'V ukázkové jednotce označ sekce [Unit], [Service], [Install] a klíče ExecStart, User a Restart a vysvětli je.', 'output' => 'Popsaná jednotka.', 'time' => '13 min'],
            ['text' => 'Nakresli řetěz závislostí webové služby a u každé vazby rozliš, zda jde o pořadí (After=), nebo závislost (Wants= / Requires=).', 'output' => 'Diagram závislostí.', 'time' => '15 min'],
            ['text' => 'Navrhni minimální úpravu jako drop-in (např. Restart=on-failure s omezením počtu pokusů), postup návratu a ověření po systemctl daemon-reload.', 'output' => 'Drop-in, návrat a 3 ověřovací kroky.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane ukázkovou jednotku s barevně odlišenými sekcemi a slovníček klíčů.',
            'standard' => 'Úkoly 1–4 podle zadání.',
            'challenge' => 'Vysvětlí, k čemu slouží StartLimitBurst a StartLimitIntervalSec a jak brání nekonečné smyčce.',
        ],
        'assessment' => [
            'formative' => ['Ukaž sekci: učitel řekne klíč, třída řekne sekci.', 'Pořadí, nebo závislost? (situace na tabuli)'],
            'rubric' => [
                ['criterion' => 'Anatomie jednotky', 'levels' => ['Nezná sekce.', 'Zná sekce, ne klíče.', 'Sekce i klíče s významem.', 'Najde chybu v ukázkové jednotce.']],
                ['criterion' => 'Závislosti', 'levels' => ['Nerozliší.', 'Plete After a Requires.', 'Správně rozliší pořadí a závislost.', 'Diagram celého řetězu.']],
                ['criterion' => 'Bezpečná úprava', 'levels' => ['Kopie celé jednotky.', 'Drop-in bez návratu.', 'Drop-in + návrat + ověření.', 'Limity restartů proti smyčce.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Linux: služby a systemd', 'variants' => [
            ['question' => 'Jednotka má jen „After=network-online.target“. Co to zajistí?', 'options' => ['Že síťový cíl se spustí vždy, i když ho nic nevyžaduje.', 'Pořadí: služba se spustí až po něm, pokud se spouští také.', 'Že se služba restartuje při výpadku sítě.'], 'correct' => 1, 'explanation' => 'After= určuje pořadí, ne závislost.'],
            ['question' => 'Proč dělat úpravu jako drop-in, a ne kopii celé jednotky?', 'options' => ['Drop-in mění jen potřebné řádky a aktualizace balíčku původní jednotku dál opravuje.', 'Kopie je zakázaná.', 'Drop-in nemusí projít daemon-reload.'], 'correct' => 0, 'explanation' => 'Minimální změna = menší riziko a jednodušší návrat.'],
            ['question' => 'Služba padá kvůli chybě v konfiguraci. Pomůže zvýšit četnost restartů?', 'options' => ['Ano, služba se časem chytí.', 'Ano, když se restartuje každou sekundu.', 'Ne – nejdřív je potřeba z logu najít a opravit příčinu.'], 'correct' => 2, 'explanation' => 'Restart neopraví chybnou konfiguraci.'],
        ]],
        'homework' => [['text' => 'Volitelné: v manuálové stránce systemd.service (online dokumentace systemd) najdi popis Restart= a zapiš tři možné hodnoty.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Jednotky upravujeme jen v labu nebo testovacím virtuálním počítači.', 'Na serveru vždy nejdřív záloha jednotky a plán návratu.'],
        'teacher_notes' => ['Používej testovací jednotku; důraz na důkaz před restartem.', 'Otázka do třídy: Je tohle pořadí, nebo závislost?', 'Tempo: simulátor neumí systemctl cat – ukázkovou jednotku rozdej vytištěnou.'],
        'substitution' => ['Zástup bez odborníka: úkol 1 v Linux Labu (výstup v zadání), úkoly 2–4 na vytištěné jednotce.', 'Plán B offline: vytištěná jednotka a diagram závislostí na papíře.'],
        'glossary' => ['systemd-unit', 'drop-in', 'restart-loop'],
    ],
]]];
