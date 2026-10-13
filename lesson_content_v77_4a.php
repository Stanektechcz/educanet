<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · obsahová stopa, doplnění L1–L4 – 4.A (provoz sítí a serverů).
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla jako vlna 1 a 2 (lesson_content_v76_1a_a.php).
 * Vychází z témat původních lekcí (modules.php L1, next_lessons.php L2, extended_lessons.php L3–L4); texty jsou vlastní a česky.
 * Ověřované příkazy simulátoru (klíč `sim`) hlídá tools/v77_content_audit.php: čistý svět Linux Labu (nginx běží), výstup musí
 * obsahovat `expect`. Nic se nespouští doopravdy. Kompetence: návrh katalogu v75 „provoz_site“. Úrovně: levels_v75_content_4a.php.
 * Vazba na ŠVP: svp_v72_map.php (návrh). AI prompty: ai_prompts_v76.php z tohoto obsahu (po schválení).
 */

return ['class_4a' => ['lessons' => [
    1 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 1 · Start: řízený rozbor výpadku od vrstvy k vrstvě',
        'goal' => [
            'student' => 'Na konci hodiny umím projít výpadek webu od linky po službu, ke každé hypotéze najít jeden ověřovací příkaz a příčinu doložit výstupem.',
            'success_criteria' => ['Sestavím aspoň tři hypotézy seřazené od nejnižší vrstvy.', 'Ke každé hypotéze uvedu příkaz a výstup, který ji potvrdil nebo vyloučil.', 'Příčinu popíšu jednou větou s opravou a prevencí.'],
        ],
        'competencies' => [['id' => 'ops_network_diag', 'level' => 2], ['id' => 'ops_incident', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Hlášení výpadku', 'teacher' => 'Přečte hlášení uživatele „intranet nejde“ a rozdá role řešitel a zapisovatel.', 'student' => 'Zapíší příznak, čas a rozsah dopadu.', 'form' => 've dvojicích'],
            ['from' => 10, 'to' => 22, 'phase' => 'Hypotézy po vrstvách', 'teacher' => 'Připomene pořadí linka → adresace → směrování → DNS → služba.', 'student' => 'Sepíší tři hypotézy a ke každé jeden ověřovací příkaz.', 'form' => 've dvojicích'],
            ['from' => 22, 'to' => 45, 'phase' => 'Spodní vrstvy', 'teacher' => 'Hlídá, aby každý závěr měl výstup.', 'student' => 'Ověří adresu, trasu, bránu a překlad jména.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 65, 'phase' => 'Služba', 'teacher' => 'Nasimuluje výpadek služby a nechá dvojice najít důkaz.', 'student' => 'Najdou příčinu, službu obnoví a ověří odpověď.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Rozbor bez obviňování', 'teacher' => 'Ukáže osnovu rozboru: příznak, příčina, důkaz, oprava, prevence.', 'student' => 'Sepíší krátký rozbor výpadku.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: hypotéza bez důkazu se nepočítá.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zapiš příznak výpadku tak, jak ho nahlásil uživatel, urči rozsah dopadu a sestav tři hypotézy od nejnižší vrstvy nahoru, každou s jedním ověřovacím příkazem.', 'output' => 'Tabulka hypotéza → vrstva → příkaz → co výstup potvrdí nebo vyloučí.', 'time' => '12 min'],
            ['text' => 'Ověř spodní vrstvy: trasu a výchozí bránu – gateway (ip r) a dosažitelnost brány (ping -c 2); zapiš, kterou hypotézu výstupy vyloučily.', 'output' => 'Výstupy s default via 10.0.0.1 a 0 % ztrát a vyškrtnutá hypotéza o síti.', 'time' => '12 min',
                'sim' => [['cmd' => 'ip r', 'expect' => 'default via 10.0.0.1'], ['cmd' => 'ping -c 2 10.0.0.1', 'expect' => '0% packet loss']]],
            ['text' => 'Ověř překlad jména a odpověď služby: dig +short intranet.skola.test a curl -v http://localhost; u každého výstupu napiš, co dokazuje.', 'output' => 'Adresa 10.0.0.10 a odpověď HTTP/1.1 200 OK s komentářem.', 'time' => '12 min',
                'sim' => [['cmd' => 'dig +short intranet.skola.test', 'expect' => '10.0.0.10'], ['cmd' => 'curl -v http://localhost', 'expect' => 'HTTP/1.1 200 OK']]],
            ['text' => 'Nasimuluj výpadek: zastav nginx, ukaž důkaz příčiny (spojení na port 80 selže), službu znovu spusť a ověř, že je aktivní; pak sepiš krátký postmortem.', 'output' => 'Výstup „Couldn\'t connect to server“, po startu stav active a rozbor: příznak, příčina, důkaz, oprava, prevence.', 'time' => '25 min',
                'sim' => [['cmd' => 'sudo systemctl stop nginx; curl -I http://localhost', 'expect' => "Couldn't connect to server"], ['cmd' => 'sudo systemctl stop nginx; sudo systemctl start nginx; systemctl is-active nginx', 'expect' => 'active']]],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku hypotéz s předvyplněnými vrstvami a seznam příkazů k výběru.',
            'standard' => 'Hypotézy po vrstvách, ověření sítě, DNS a služby, nácvik výpadku a rozbor.',
            'challenge' => 'Doplní do rozboru metriku, která by výpadek odhalila dřív než uživatel, a práh upozornění.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: DNS odpovídá a brána také – kam pokračuješ?', 'Kontrola souseda: má každá hypotéza výstup, který ji rozhodl?'],
            'rubric' => [
                ['criterion' => 'Hypotézy', 'levels' => ['Bez hypotéz.', 'Hypotézy bez pořadí.', 'Tři hypotézy od nejnižší vrstvy.', 'U každé předem napíše, co ji vyvrátí.']],
                ['criterion' => 'Důkazy', 'levels' => ['Bez výstupů.', 'Výstupy bez závěru.', 'Ke každé hypotéze výstup a závěr.', 'Rozliší potvrzení a vyloučení hypotézy.']],
                ['criterion' => 'Rozbor', 'levels' => ['Chybí.', 'Jen popis příznaku.', 'Příznak, příčina, důkaz, oprava, prevence.', 'Prevence je měřitelná (metrika, práh).']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_incident', 'competence_label' => 'Provoz: vedení incidentu a rozbor', 'variants' => [
            ['question' => 'Brána odpovídá a jméno se přeloží. Kterou vrstvu ověříš jako další?', 'options' => ['Kabel ke stanici.', 'Masku sítě.', 'Službu: jestli naslouchá na portu a odpovídá.'], 'correct' => 2, 'explanation' => 'Spodní vrstvy jsou vyloučené – zbývá služba.'],
            ['question' => 'curl na port 80 vrací „Couldn\'t connect to server“. Co je nejsilnější hypotéza?', 'options' => ['Na portu 80 nic nenaslouchá – služba neběží.', 'Chybí záznam DNS.', 'Server má málo paměti.'], 'correct' => 0, 'explanation' => 'Odmítnuté spojení znamená, že na portu není služba.'],
            ['question' => 'Co do rozboru výpadku nepatří?', 'options' => ['Důkaz, který potvrdil příčinu.', 'Jméno člověka, který udělal chybu.', 'Opatření, aby se výpadek neopakoval.'], 'correct' => 1, 'explanation' => 'Rozbor je bez obviňování – řeší systém, ne viníka.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyber výpadek známé služby z médií a podle osnovy z hodiny odhadni hypotézy po vrstvách (bez jmen lidí).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Výpadek simulujeme jen v Linux Labu; na školních serverech služby nezastavujeme.', 'Rozbory píšeme bez jmen a bez obviňování.'],
        'teacher_notes' => ['Častý omyl: skok rovnou na restart služby bez důkazu – trvej na výstupu před opravou.', 'Otázka do třídy: Jak poznáš, že hypotéza je vyloučená, a ne jen neověřená?', 'Tempo: nácvik výpadku je nejdelší úsek; rozbor lze dopsat doma.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání), úkol 1 na papír.', 'Plán B offline: vytištěné výstupy příkazů, žáci podle nich rozhodují hypotézy.'],
        'glossary' => ['postmortem', 'gateway'],
    ],
    2 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 2 · Změnové okno: proxy, TLS, logy a plán návratu',
        'goal' => [
            'student' => 'Na konci hodiny umím připravit řízenou změnu webového serveru: zálohu a kontrolu konfigurace, reload bez výpadku, ověření a jednoznačný krok návratu.',
            'success_criteria' => ['Před změnou zálohuji konfiguraci a ověřím syntaxi.', 'Rozliším reload od restartu a po změně ověřím službu z pohledu uživatele.', 'Plán návratu má jeden jednoznačný krok a podmínku, kdy ho použiji.'],
        ],
        'competencies' => [['id' => 'ops_change', 'level' => 2], ['id' => 'ops_proxy_tls', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Změnový plán', 'teacher' => 'Ukáže vzor změnového tiketu: rozsah, prechecky, kritéria úspěchu, návrat.', 'student' => 'Doplní tiket pro změnu „web za reverzní proxy“.', 'form' => 've dvojicích'],
            ['from' => 12, 'to' => 27, 'phase' => 'Záloha a syntaxe', 'teacher' => 'Předvede zálohu konfigurace a nginx -t.', 'student' => 'Zálohují konfiguraci a ověří syntaxi.', 'form' => 'jednotlivě'],
            ['from' => 27, 'to' => 42, 'phase' => 'Reverzní proxy', 'teacher' => 'Rozebere server blok a location s proxy_pass na upstream.', 'student' => 'Navrhnou blok location pro aplikaci na 127.0.0.1:8080.', 'form' => 'jednotlivě'],
            ['from' => 42, 'to' => 58, 'phase' => 'Reload a ověření', 'teacher' => 'Vysvětlí, proč reload nepřeruší spojení.', 'student' => 'Provedou reload a ověří stav služby a odpověď 200.', 'form' => 'jednotlivě'],
            ['from' => 58, 'to' => 80, 'phase' => 'Logy a TLS', 'teacher' => 'Ukáže korelaci access a error logu a kontrolu certifikátu (platnost, SAN).', 'student' => 'Najdou chybové požadavky a sepíší kontrolu certifikátu a krok návratu.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: změna bez plánu návratu není připravená.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Před změnou zazálohuj konfiguraci nginx do souboru .bak a ověř syntaxi příkazem nginx -t.', 'output' => 'Soubor nginx.conf.bak ve výpisu /etc/nginx a hláška „syntax is ok“.', 'time' => '12 min',
                'sim' => [['cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; ls /etc/nginx', 'expect' => 'nginx.conf.bak'], ['cmd' => 'nginx -t', 'expect' => 'syntax is ok']]],
            ['text' => 'Přečti konfiguraci webu v sites-enabled/default a navrhni blok location s proxy_pass, který pošle požadavky na upstream aplikace na 127.0.0.1:8080.', 'output' => 'Blok location / { proxy_pass http://127.0.0.1:8080; } a věta, co se změní pro uživatele (reverse proxy).', 'time' => '18 min',
                'sim' => [['cmd' => 'cat /etc/nginx/sites-enabled/default', 'expect' => 'try_files']]],
            ['text' => 'Proveď reload služby, ověř, že je aktivní, a že web odpovídá stavem 200; vysvětli, proč jsi nepoužil/a restart.', 'output' => 'Stav active, HTTP 200 a věta o rozdílu reload / restart.', 'time' => '12 min',
                'sim' => [['cmd' => 'sudo systemctl reload nginx; systemctl is-active nginx', 'expect' => 'active'], ['cmd' => 'curl -I http://localhost', 'expect' => '200 OK']]],
            ['text' => 'Najdi v access logu požadavky s chybou 404 a v error logu poslední záznam; sepiš kontrolu certifikátu TLS (platnost, SAN pro app.example.cz) a jednoznačný krok rollback.', 'output' => 'Chybné požadavky s časem, poslední řádek error logu, kontrolní seznam TLS a krok návratu „obnov nginx.conf.bak, nginx -t, reload“.', 'time' => '25 min',
                'sim' => [['cmd' => 'sudo grep 404 /var/log/nginx/access.log', 'expect' => '404 162'], ['cmd' => 'sudo tail -n 3 /var/log/nginx/error.log', 'expect' => 'signal process started']]],
        ],
        'differentiation' => [
            'support' => 'Dostane předvyplněný změnový tiket a vzor bloku location; doplňuje adresu upstreamu a kroky ověření.',
            'standard' => 'Záloha a kontrola syntaxe, návrh proxy, reload s ověřením, logy, kontrola TLS a návrat.',
            'challenge' => 'Doplní do tiketu kritérium úspěchu s číslem (chybovost, doba odpovědi) a podmínku, kdy návrat spustí.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: nginx -t hlásí chybu – smíš udělat reload?', 'Kontrola souseda: dá se krok návratu provést bez přemýšlení?'],
            'rubric' => [
                ['criterion' => 'Příprava změny', 'levels' => ['Bez zálohy.', 'Záloha bez kontroly syntaxe.', 'Záloha i kontrola syntaxe.', 'Prechecky s očekávaným výsledkem.']],
                ['criterion' => 'Provedení a ověření', 'levels' => ['Restart bez ověření.', 'Reload bez ověření.', 'Reload, stav služby a HTTP 200.', 'Ověří i z pohledu uživatele a logu.']],
                ['criterion' => 'Plán návratu', 'levels' => ['Chybí.', 'Obecný („vrátit to“).', 'Jednoznačný krok.', 'Krok s podmínkou spuštění a ověřením.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_change', 'competence_label' => 'Provoz: řízená změna s plánem návratu', 'variants' => [
            ['question' => 'nginx -t po úpravě hlásí chybu syntaxe. Co uděláš?', 'options' => ['Přesto provedu reload, nginx chybu opraví.', 'Restartuji server.', 'Změnu neprovedu, opravím konfiguraci nebo vrátím zálohu.'], 'correct' => 2, 'explanation' => 'S chybnou konfigurací se služba nemusí znovu spustit.'],
            ['question' => 'Proč při změně konfigurace volíš reload místo restartu?', 'options' => ['Reload načte konfiguraci bez přerušení běžících spojení.', 'Reload smaže logy.', 'Restart nejde vrátit.'], 'correct' => 0, 'explanation' => 'Reload je šetrnější – uživatelé výpadek nepoznají.'],
            ['question' => 'Který krok návratu je dobře napsaný?', 'options' => ['„Když se něco pokazí, vrátíme to.“', '„Obnov nginx.conf.bak, ověř nginx -t a proveď reload.“', '„Zavolej správce.“'], 'correct' => 1, 'explanation' => 'Krok návratu musí jít provést přesně a bez přemýšlení.'],
        ]],
        'homework' => [['text' => 'Volitelné: napiš změnový tiket pro libovolnou změnu, kterou jsi doma dělal/a (aktualizace, nový router) – rozsah, kontrola a krok návratu.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Konfiguraci měníme jen v simulátoru Linux Labu; adresy a domény jsou ukázkové.', 'Soukromé klíče certifikátů nikdy nekopírujeme do sešitu ani do chatu.'],
        'teacher_notes' => ['Častý omyl: „reload a restart jsou totéž“ – ukaž rozdíl na běžícím spojení.', 'Otázka do třídy: Kdy je správné rozhodnutí změnu vůbec neprovést?', 'Tempo: proxy_pass jen navrhují na papír – simulátor upstream na 8080 nespouští.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 3 a 4 v Linux Labu podle zadání, úkol 2 na papír podle vzoru.', 'Plán B offline: vytištěný změnový tiket a výpis konfigurace k doplnění.'],
        'glossary' => ['reverse-proxy', 'upstream', 'reload', 'rollback', 'tls'],
    ],
    3 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 3 · Pozorovatelnost: logy, metriky a kontrola zdraví',
        'goal' => [
            'student' => 'Na konci hodiny umím z logů a kontroly zdraví sestavit důkaz o stavu služby, spočítat chybovost a navrhnout tři ukazatele s cílem SLO.',
            'success_criteria' => ['Ke třem ukazatelům (latence, chybovost, dostupnost) zapíšu výchozí hodnotu a cíl.', 'Z access logu spočítám podíl chybových odpovědí.', 'Navrhnu endpoint /health a rozhodnu, kdo ho smí vidět.'],
        ],
        'competencies' => [['id' => 'ops_observability', 'level' => 2], ['id' => 'ops_incident', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Start: pocit vs. důkaz', 'teacher' => 'Ukáže dvě hlášení „web je pomalý“ – jedno s čísly, jedno bez – a zeptá se, se kterým se dá pracovat.', 'student' => 'Navrhnou, co by měli měřit.', 'form' => 'frontálně'],
            ['from' => 12, 'to' => 28, 'phase' => 'SLI a SLO', 'teacher' => 'Vysvětlí ukazatel (SLI), cíl (SLO) a výchozí hodnotu.', 'student' => 'Zapíší tři ukazatele s výchozí hodnotou a cílem.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 48, 'phase' => 'Chybovost z logu', 'teacher' => 'Předvede počítání stavových kódů jedním řádkem.', 'student' => 'Spočítají podíl odpovědí jiných než 200.', 'form' => 'jednotlivě'],
            ['from' => 48, 'to' => 65, 'phase' => 'Kontrola zdraví', 'teacher' => 'Ukáže, že / vrací 200, ale /health zatím neexistuje.', 'student' => 'Navrhnou /health a jeho odpověď a zabezpečení.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Korelace', 'teacher' => 'Ukáže, jak spojit čas chyby v access logu se záznamem služby.', 'student' => 'Najdou opakovanou chybu 403 a porovnají ji s žurnálem služby.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: měříme, než začneme opravovat.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vyber tři ukazatele (latence, chybovost, dostupnost), ke každému zapiš výchozí hodnotu a cíl SLO a vysvětli, čím se liší SLI od SLO.', 'output' => 'Tabulka ukazatel → výchozí hodnota → cíl (např. 99,5 % dostupnost za měsíc) a věta o SLI a SLO.', 'time' => '15 min'],
            ['text' => 'Spočítej v access logu stavové kódy (sudo awk … | sort | uniq -c) a urči podíl odpovědí jiných než 200.', 'output' => 'Počty 200: 15, 403: 6, 404: 9 → 15 z 30 požadavků (50 %) není úspěšných, s komentářem, které chyby jsou „naše“.', 'time' => '18 min',
                'sim' => [['cmd' => "sudo awk '{print \$9}' /var/log/nginx/access.log | sort | uniq -c", 'expect' => '15 200']]],
            ['text' => 'Ověř kontrolu zdraví: curl na / vrací 200, ale /health vrací 404; navrhni, co má /health vracet a proč nesmí být veřejný s detaily.', 'output' => 'Dva výstupy (200 a 404 Not Found) a návrh health check: stav 200 a krátký text, detaily jen pro monitoring.', 'time' => '15 min',
                'sim' => [['cmd' => 'curl -I http://localhost', 'expect' => '200 OK'], ['cmd' => 'curl -s http://localhost/health', 'expect' => '404 Not Found']]],
            ['text' => 'Najdi v access logu opakované požadavky s chybou 403, zapiš jejich časy a porovnej je se žurnálem služby; rozhodni, zda jde o chybu služby, nebo o zamítnutý přístup.', 'output' => 'Šest požadavků GET /admin se stavem 403 a závěr: služba běží, jde o zamítnutý přístup, ne výpadek.', 'time' => '20 min',
                'sim' => [['cmd' => 'sudo grep 403 /var/log/nginx/access.log', 'expect' => 'GET /admin'], ['cmd' => 'journalctl -u nginx -n 5', 'expect' => 'Started nginx.service']]],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku ukazatelů s vyplněnými názvy a kalkulačku podílu; doplňuje čísla.',
            'standard' => 'Tři ukazatele s cílem, chybovost z logu, návrh /health a korelace chyb.',
            'challenge' => 'Spočítá rozpočet chyb pro cíl 99,5 % za 30 dní v minutách a navrhne práh upozornění.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: 403 a 404 – jsou to výpadky služby?', 'Kartičky: je „web je pomalý“ ukazatel, nebo pocit?'],
            'rubric' => [
                ['criterion' => 'Ukazatele a cíle', 'levels' => ['Bez ukazatelů.', 'Ukazatele bez cíle.', 'Tři ukazatele s výchozí hodnotou a cílem.', 'Vysvětlí volbu cíle a rozpočet chyb.']],
                ['criterion' => 'Chybovost z logu', 'levels' => ['Nespočítá.', 'Spočítá kódy.', 'Spočítá podíl chybových odpovědí.', 'Rozliší chyby klienta a serveru.']],
                ['criterion' => 'Kontrola zdraví', 'levels' => ['Chybí.', 'Jen ping.', 'Návrh /health s odpovědí.', 'Návrh i se zabezpečením a tím, co /health nekontroluje.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_observability', 'competence_label' => 'Provoz: logy, metriky a cíle SLO', 'variants' => [
            ['question' => 'Z 30 požadavků v logu je 15 se stavem 200. Jaká je chybovost?', 'options' => ['15 %', '50 %', '30 %'], 'correct' => 1, 'explanation' => '15 z 30 požadavků nebylo úspěšných = 50 %.'],
            ['question' => 'Co je SLO?', 'options' => ['Cílová hodnota ukazatele, např. 99,5 % dostupnost za měsíc.', 'Program, který sbírá logy.', 'Chybová hláška serveru.'], 'correct' => 0, 'explanation' => 'SLI je měřený ukazatel, SLO jeho cíl.'],
            ['question' => 'V logu je šestkrát 403 pro /admin a služba běží. Co z toho plyne?', 'options' => ['Služba padá a je třeba ji restartovat.', 'Došlo místo na disku.', 'Přístup byl zamítnut – nejde o výpadek, ale ověř, kdo se o /admin pokouší.'], 'correct' => 2, 'explanation' => '403 = zakázáno; služba funguje, zamítla přístup.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyber aplikaci, kterou používáš, a navrhni pro ni tři ukazatele a cíl SLO (bez přihlašování a osobních údajů).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Logy čteme jen v Linux Labu; ve skutečných logech jsou osobní údaje a ty bez důvodu neprocházíme ani nesdílíme.', 'Endpoint /health nesmí veřejně ukazovat verze, cesty ani hesla.'],
        'teacher_notes' => ['Častý omyl: každá chyba v logu = výpadek – rozliš 4xx (klient) a 5xx (server).', 'Otázka do třídy: Proč je cíl 100 % dostupnosti špatný cíl?', 'Tempo: pojem observability stačí ukázat na příkladu, definice až na konci.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2–4 v Linux Labu podle zadání (očekávané výstupy jsou v zadání), úkol 1 na papír.', 'Plán B offline: vytištěný výřez access logu, žáci počítají chybovost ručně.'],
        'glossary' => ['observability', 'sli', 'slo', 'health-check'],
    ],
    4 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 4 · Nácvik vydání: kanárek, ověření a rozhodnutí o návratu',
        'goal' => [
            'student' => 'Na konci hodiny umím provést vydání jako řízenou změnu: prechecky, postupné nasazení na část provozu, ověření podle podmínek zastavení a rozhodnutí pokračovat nebo vrátit.',
            'success_criteria' => ['Změnový tiket má rozsah, kritéria úspěchu, dvě podmínky zastavení a krok návratu.', 'Prechecky ověřím výstupy (syntaxe, disk, paměť) před nasazením.', 'Rozhodnutí pokračovat / vrátit podložím čísly z ověření.'],
        ],
        'competencies' => [['id' => 'ops_change', 'level' => 3], ['id' => 'ops_observability', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Změnový tiket', 'teacher' => 'Zadá vydání nové verze webu a ukáže tiket s podmínkami zastavení.', 'student' => 'Doplní rozsah, kritéria úspěchu a dvě podmínky zastavení.', 'form' => 've dvojicích'],
            ['from' => 12, 'to' => 28, 'phase' => 'Prechecky', 'teacher' => 'Předvede kontrolu syntaxe, disku a paměti.', 'student' => 'Spustí prechecky a zapíší výchozí stav.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 45, 'phase' => 'Kanárek', 'teacher' => 'Vysvětlí nasazení na 10 % provozu (canary) a proč se nespouští na všechny najednou.', 'student' => 'Spočítají, kolik požadavků jde na novou verzi a kdy se překročí podmínka zastavení.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 62, 'phase' => 'Ověření', 'teacher' => 'Ukáže pořadí ověření: DNS, HTTP, služba, cesta uživatele.', 'student' => 'Ověří nasazení z pohledu uživatele.', 'form' => 've dvojicích'],
            ['from' => 62, 'to' => 80, 'phase' => 'Nácvik návratu', 'teacher' => 'Vyhlásí „chybovost kanárka 12 %“ a nechá dvojice rozhodnout.', 'student' => 'Provedou návrat konfigurace a ověří syntaxi i službu.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Rozhodnutí a exit ticket', 'teacher' => 'Shrne: rozhoduje předem dané číslo, ne nálada.', 'student' => 'Zapíší rozhodnutí s důkazem a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš změnový tiket vydání: rozsah, tři kritéria úspěchu, dvě podmínky zastavení (např. chybovost nad 3 %) a jednoznačný krok rollback.', 'output' => 'Tiket s čísly v kritériích i podmínkách zastavení a krokem návratu.', 'time' => '15 min'],
            ['text' => 'Spusť prechecky před nasazením: syntaxe konfigurace, volné místo na disku a paměť; zapiš výchozí hodnoty do tiketu.', 'output' => 'Výstupy „test is successful“, využití disku 31 % a volná paměť v MB.', 'time' => '12 min',
                'sim' => [['cmd' => 'nginx -t', 'expect' => 'test is successful'], ['cmd' => 'df -h', 'expect' => '/dev/sda1'], ['cmd' => 'free -m', 'expect' => 'Mem:']]],
            ['text' => 'Kanárek dostane 10 % z 2 000 požadavků; 24 z nich skončí chybou. Spočítej chybovost kanárka, porovnej ji s podmínkou zastavení 3 % a rozhodni.', 'output' => '200 požadavků, chybovost 12 % > 3 % → zastavit a vrátit; zápis rozhodnutí s čísly.', 'time' => '13 min'],
            ['text' => 'Ověř nasazení z pohledu uživatele (DNS, HTTP stav intranetu) a pak nacvič návrat: obnov konfiguraci ze zálohy a ověř syntaxi.', 'output' => 'Adresa 10.0.0.10, HTTP 200 a po návratu „syntax is ok“.', 'time' => '25 min',
                'sim' => [['cmd' => 'dig +short intranet.skola.test', 'expect' => '10.0.0.10'], ['cmd' => 'curl -I http://intranet.skola.test', 'expect' => '200 OK'],
                    ['cmd' => 'sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak; sudo cp /etc/nginx/nginx.conf.bak /etc/nginx/nginx.conf; nginx -t', 'expect' => 'syntax is ok']]],
        ],
        'differentiation' => [
            'support' => 'Dostane tiket s vyplněným rozsahem a vzorec pro výpočet chybovosti.',
            'standard' => 'Tiket, prechecky, výpočet kanárka, ověření a nácvik návratu.',
            'challenge' => 'Navrhne druhý stupeň kanárka (25 %) a podmínku, kdy smí pokračovat, a zdůvodní dobu sledování.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: chybovost kanárka je 2,5 % a podmínka zastavení 3 % – co uděláš?', 'Kontrola souseda: dá se každá podmínka v tiketu změřit?'],
            'rubric' => [
                ['criterion' => 'Tiket', 'levels' => ['Chybí.', 'Bez čísel.', 'Kritéria a podmínky zastavení s čísly.', 'Podmínky odvozené z výchozího stavu.']],
                ['criterion' => 'Prechecky a ověření', 'levels' => ['Bez kontroly.', 'Jen syntaxe.', 'Syntaxe, disk, paměť a ověření uživatele.', 'Zapíše výchozí stav pro srovnání.']],
                ['criterion' => 'Rozhodnutí', 'levels' => ['Bez rozhodnutí.', 'Rozhodnutí bez důkazu.', 'Rozhodnutí podle podmínky s čísly.', 'Návrat provede a ověří.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'ops_change', 'competence_label' => 'Provoz: řízené vydání a návrat', 'variants' => [
            ['question' => 'Chybovost kanárka vzroste na 12 %, podmínka zastavení je 3 %. Co uděláš?', 'options' => ['Počkám, jestli se to samo zlepší.', 'Nasadím na všechny, ať je víc dat.', 'Zastavím vydání a provedu návrat podle tiketu.'], 'correct' => 2, 'explanation' => 'Překročená podmínka zastavení = návrat, bez diskuse.'],
            ['question' => 'Proč se nová verze nejdřív nasazuje jen na část provozu?', 'options' => ['Chyba zasáhne jen malou část uživatelů a dá se včas zastavit.', 'Je to rychlejší než nasazení na všechny.', 'Server nezvládne víc verzí.'], 'correct' => 0, 'explanation' => 'Kanárek omezuje dosah případné chyby.'],
            ['question' => 'Který precheck patří před nasazení nové konfigurace?', 'options' => ['Smazání starých logů.', 'Kontrola syntaxe konfigurace (nginx -t).', 'Restart celého serveru.'], 'correct' => 1, 'explanation' => 'Chybnou konfiguraci odhalíš dřív, než způsobí výpadek.'],
        ]],
        'homework' => [['text' => 'Volitelné: navrhni podmínky zastavení pro vydání aplikace, kterou znáš (chybovost, doba odpovědi), a zdůvodni čísla.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Vydání i návrat zkoušíme jen v simulátoru Linux Labu.', 'Čísla a domény v úlohách jsou vymyšlené; skutečné provozní údaje školy nepoužíváme.'],
        'teacher_notes' => ['Častý omyl: „počkáme, ono se to zlepší“ – podmínka zastavení se dodržuje vždy.', 'Otázka do třídy: Kdo smí rozhodnout o návratu a musí se ptát?', 'Tempo: výpočet kanárka je krátký; čas nech na ověření a návrat.'],
        'substitution' => ['Zástup bez odborníka: úkoly 2 a 4 v Linux Labu podle zadání, úkoly 1 a 3 na papír (výsledek výpočtu je v zadání).', 'Plán B offline: kartičky s čísly z ověření, dvojice rozhodují „pokračovat / vrátit“.'],
        'glossary' => ['canary', 'rollback'],
    ],
]]];
