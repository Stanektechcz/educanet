<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 1.A (grafika a webdesign), lekce 17–22.
 * STAV: NÁVRH. Žák uvidí obsah až po schválení učitelem (lesson_approval_v72.php); do té doby vidí původní lekci.
 * Tvar podle modelu lm71 a pravidel vlny 1 (lesson_content_v72_1a_a.php): cíl + 2–4 kritéria, plán 90 min, úkoly,
 * diferenciace, formativní kontrola a rubrika 3 × 1–4, exit ticket (3 varianty, jiné než kvíz lekce), bezpečnost,
 * poznámky, zástup, dobrovolná domácí příprava a glosář. Úrovně Základ / Standard / Výzva: levels_v76_content_1a.php.
 * Vychází z témat původních lekcí (lessons_yearpack.php L17–L18, lessons_v30.php L19–L28); texty jsou vlastní a česky.
 * Kompetence: katalog v62 „grafika_web“. Vazba na ŠVP: svp_v72_map.php (návrh). AI prompty: ai_prompts_v76.php z tohoto obsahu.
 */

return ['class_1a' => ['lessons' => [
    17 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 17 · Malá sada pravidel UI: tokeny místo náhodných hodnot',
        'goal' => [
            'student' => 'Na konci hodiny umím sepsat malou sadu pravidel rozhraní – barvy s rolemi, škálu mezer a dvě velikosti písma – a podle ní poskládat tlačítko a kartu.',
            'success_criteria' => ['Každá barva v sadě má jméno podle role (např. text, akcent), ne podle odstínu.', 'Mezery používám jen ze škály 4, 8, 16, 24, 32 px.', 'Tlačítko i karta jsou postavené jen z hodnot sady, bez jediné „náhodné“ hodnoty.'],
        ],
        'competencies' => [['id' => 'web_ux', 'level' => 2], ['id' => 'gfx_typography', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: dvě karty', 'teacher' => 'Ukáže dvě karty – jednu s mezerami 7, 13 a 22 px, druhou se škálou 8, 16 a 24 px.', 'student' => 'Hledají, proč druhá působí klidněji.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Barvy s rolemi', 'teacher' => 'Vysvětlí pojmenování podle role (barva-text, barva-akcent) místo „modrá-2“.', 'student' => 'Pojmenují 4 barvy své sady a zapíší HEX kódy.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Škála mezer a písma', 'teacher' => 'Předvede škálu 4–32 px a dvě velikosti písma (16 a 24 px).', 'student' => 'Doplní tabulku tokenů o mezery a písmo.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 65, 'phase' => 'Tlačítko a karta z tokenů', 'teacher' => 'Obchází a ptá se: „Ze kterého tokenu je tahle hodnota?“', 'student' => 'Poskládají tlačítko a kartu jen z hodnot sady.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 80, 'phase' => 'Kontrola souseda', 'teacher' => 'Zadá párovou kontrolu „najdi hodnotu mimo sadu“.', 'student' => 'Vymění si návrhy a označí hodnoty, které v sadě nejsou.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Shrnutí a exit ticket', 'teacher' => 'Shrne: pravidlo šetří rozhodování.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Pojmenuj 4 barvy své sady podle role (pozadí, text, akcent, rámeček) a ke každé zapiš HEX kód.', 'output' => 'Tabulka čtyř barevných tokenů se jménem role a HEX kódem.', 'time' => '15 min'],
            ['text' => 'Doplň do tabulky tokenů škálu mezer 4, 8, 16, 24, 32 px a dvě velikosti písma (běžný text 16 px, nadpis 24 px).', 'output' => 'Tabulka tokenů: barvy, mezery a písmo na jedné straně.', 'time' => '15 min'],
            ['text' => 'Navrhni tlačítko a kartu výhradně z hodnot své sady a u každé hodnoty napiš jméno tokenu.', 'output' => 'Tlačítko a karta s popisky tokenů (např. mezera-16, barva-akcent).', 'time' => '25 min'],
            ['text' => 'Zkontroluj návrh spolužáka: označ každou hodnotu, která v jeho sadě není, a navrhni nejbližší token.', 'output' => 'Seznam nalezených hodnot mimo sadu s návrhem náhrady.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane předvyplněnou tabulku tokenů s barvami; doplňuje jen mezery a skládá tlačítko podle předlohy.',
            'standard' => 'Vlastní sada tokenů, tlačítko a karta s popisky a párová kontrola.',
            'challenge' => 'Doplní tmavou variantu sady se stejnými jmény rolí a ověří, že karta funguje v obou.',
        ],
        'assessment' => [
            'formative' => ['Rychlá kontrola: učitel ukáže hodnotu 13 px a třída řekne nejbližší token ze škály.', 'Párová kontrola: kolik hodnot mimo sadu soused našel?'],
            'rubric' => [
                ['criterion' => 'Jména tokenů', 'levels' => ['Barvy bez jmen.', 'Jména podle odstínu.', 'Jména podle role s HEX kódem.', 'Jména drží i v tmavé variantě.']],
                ['criterion' => 'Škála mezer', 'levels' => ['Mezery náhodně.', 'Škála existuje, ale nedodržuje se.', 'Všechny mezery ze škály.', 'Zdůvodní, proč je škála násobkem 4 nebo 8.']],
                ['criterion' => 'Komponenty z tokenů', 'levels' => ['Komponenty nevznikly.', 'Část hodnot mimo sadu.', 'Tlačítko i karta jen z tokenů s popisky.', 'Najde a opraví hodnoty mimo sadu i u souseda.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_ux', 'competence_label' => 'Ovládání a výzva k akci', 'variants' => [
            ['question' => 'Které jméno barevného tokenu je nejlepší?', 'options' => ['modra-tmava-2', 'barva-akcent', 'nova-barva'], 'correct' => 1, 'explanation' => 'Jméno podle role přežije i změnu odstínu.'],
            ['question' => 'Škála mezer je 4, 8, 16, 24 a 32 px. V návrhu je mezera 15 px. Co uděláš?', 'options' => ['Nechám ji, rozdíl není vidět.', 'Přidám 15 do škály.', 'Nahradím ji tokenem 16 px.'], 'correct' => 2, 'explanation' => 'Hodnota mimo sadu se nahradí nejbližším tokenem.'],
            ['question' => 'Proč se vyplatí stavět komponenty jen z tokenů?', 'options' => ['Změna tokenu se projeví všude a návrh zůstane jednotný.', 'Soubor návrhu je pak menší.', 'Tokeny zakazují používat barvy.'], 'correct' => 0, 'explanation' => 'Jedno místo pravdy = konzistence a rychlé změny.'],
        ]],
        'homework' => [['text' => 'Volitelné: na oblíbeném webu najdi tři mezery mezi prvky a odhadni, jestli patří do jedné škály.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Barvy a písma jen z volně použitelných zdrojů s licencí; zdroj písma zapiš do sady.', 'V ukázkových kartách nejsou jména ani fotky spolužáků.'],
        'teacher_notes' => ['Častý omyl: tokeny (anglicky design token) pojmenované podle odstínu – po změně barvy jméno lže.', 'Otázka do třídy: Kolik různých mezer snese jedna obrazovka, aby působila klidně?', 'Tempo: když skládání komponent trvá déle, kartu dokončete příště a párovou kontrolu udělejte na tlačítku.'],
        'substitution' => ['Zástup bez odborníka: rozdá šablonu tabulky tokenů, žáci plní úkoly 1–4 na papíře nebo v editoru.', 'Plán B offline: tokeny jako barevné kartičky a mezery odměřené pravítkem ve čtverečkovaném sešitu.'],
        'glossary' => ['design-token', 'spacing'],
    ],
    18 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 18 · Závěrečný projekt: responzivní landing page',
        'goal' => [
            'student' => 'Na konci hodiny umím postavit jednoduchou landing page se sémantickou kostrou, čitelným písmem a rozvržením, které funguje na mobilu i na počítači.',
            'success_criteria' => ['Stránka má header, main se sekcemi a footer a jeden nadpis h1.', 'Na šířce 390 px se nic nepřekrývá a text nejde mimo obrazovku.', 'Formulář má u každého pole viditelný label.'],
        ],
        'competencies' => [['id' => 'web_html_structure', 'level' => 2], ['id' => 'web_css_layout', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Zadání projektu', 'teacher' => 'Představí zadání (vymyšlený kroužek, akce nebo služba) a kontrolní seznam.', 'student' => 'Vyberou téma a zapíší jedno hlavní sdělení stránky.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Kostra HTML', 'teacher' => 'Připomene header, main, section a footer.', 'student' => 'Napíší kostru se třemi sekcemi a jedním h1.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Písmo a rozvržení', 'teacher' => 'Ukáže postup mobile first: nejdřív jeden sloupec, pak breakpoint.', 'student' => 'Nastaví písmo, mezery a jeden breakpoint pro širší obrazovku.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Formulář přihlášky', 'teacher' => 'Ukáže label propojený s polem a chybovou hlášku.', 'student' => 'Doplní formulář se dvěma poli a tlačítkem.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 80, 'phase' => 'Test na mobilu', 'teacher' => 'Vede kontrolu v náhledu 390 px a klávesnicí.', 'student' => 'Projdou kontrolní seznam a opraví první chybu.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Odevzdání a exit ticket', 'teacher' => 'Vybere dvě stránky na rychlou ukázku.', 'student' => 'Odevzdají soubory a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš kostru landing page: header, main se třemi sekcemi (co, pro koho, přihláška), footer a jeden nadpis h1.', 'output' => 'Soubor index.html s platnou kostrou a logickými nadpisy.', 'time' => '15 min'],
            ['text' => 'Nastav písmo s velikostí běžného textu 16 px, výškou řádku 1,5 a jeden breakpoint, od kterého se sekce řadí vedle sebe.', 'output' => 'Soubor styl.css s pravidly pro mobil a jedním breakpointem.', 'time' => '20 min'],
            ['text' => 'Doplň formulář přihlášky se dvěma poli (jméno akce, e-mail ve tvaru jan@example.com) a s viditelným labelem u každého pole.', 'output' => 'Formulář, kde kliknutí na label přesune kurzor do pole.', 'time' => '20 min'],
            ['text' => 'Projdi stránku na šířce 390 px a jen klávesnicí (Tab) a zapiš první nalezenou chybu i její opravu.', 'output' => 'Řádek do kontrolního seznamu: chyba → oprava → ověřeno.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kostru HTML s komentáři, doplňuje texty sekcí a styly podle předlohy.',
            'standard' => 'Celá stránka podle zadání včetně formuláře a testu na mobilu.',
            'challenge' => 'Přidá druhý breakpoint a ověří, že pořadí čtení při Tab odpovídá pořadí na obrazovce.',
        ],
        'assessment' => [
            'formative' => ['Kontrola kostry: zvednuté ruce – kdo má jen jeden h1?', 'Dvojice: soused projde stránku Tabem a řekne, kde se ztratil.'],
            'rubric' => [
                ['criterion' => 'Sémantická kostra', 'levels' => ['Jen div bez struktury.', 'Části kostry chybí.', 'header, main se sekcemi, footer a jeden h1.', 'Nadpisy tvoří logickou osnovu i bez stylů.']],
                ['criterion' => 'Responzivita', 'levels' => ['Na mobilu se obsah překrývá.', 'Mobil funguje, širší obrazovka ne.', 'Mobil i breakpoint bez překryvů.', 'Dva breakpointy a stejné pořadí čtení.']],
                ['criterion' => 'Přístupný formulář', 'levels' => ['Pole bez popisku.', 'Popisek jen jako placeholder.', 'Viditelný label propojený s polem.', 'Srozumitelná chybová hláška u pole.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_html_structure', 'competence_label' => 'Sémantická kostra webu', 'variants' => [
            ['question' => 'Kolik nadpisů h1 má mít jedna landing page?', 'options' => ['Jeden – hlavní téma stránky.', 'Jeden v každé sekci.', 'Žádný, stačí tučný text.'], 'correct' => 0, 'explanation' => 'h1 je jedno hlavní téma; sekce používají h2.'],
            ['question' => 'Co znamená postup mobile first?', 'options' => ['Stránka se dělá jen pro mobil.', 'Nejdřív styly pro úzkou obrazovku, širší rozvržení až od breakpointu.', 'Na mobilu se skryje polovina obsahu.'], 'correct' => 1, 'explanation' => 'Základ platí pro mobil, breakpoint přidává rozvržení pro větší šířku.'],
            ['question' => 'Proč nestačí placeholder místo labelu?', 'options' => ['Placeholder je moc velký.', 'Placeholder nejde obarvit.', 'Po začátku psaní zmizí a čtečka ho nemusí přečíst jako popisek.'], 'correct' => 2, 'explanation' => 'Label zůstává vidět a je s polem propojený.'],
        ]],
        'homework' => [['text' => 'Volitelné: otevři svou stránku na telefonu a zapiš jednu věc, která se tam chová jinak než v počítači.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Téma stránky je vymyšlené; žádná skutečná jména, telefonní čísla ani e-maily (jen tvar jan@example.com).', 'Obrázky jen vlastní nebo s licencí; zdroj uveď ve footeru.'],
        'teacher_notes' => ['Častý omyl: rozvržení nejdřív pro počítač a pak „oprava“ mobilu – vracej žáky k jednomu sloupci.', 'Otázka do třídy: Co uvidí člověk na mobilu jako první a je to to hlavní?', 'Tempo: když formulář nestihnou, odevzdají kostru a styly a formulář dokončí v L20.'],
        'substitution' => ['Zástup bez odborníka: vytiskne kontrolní seznam, žáci plní úkoly 1–4 a odevzdají soubory.', 'Plán B offline: drátěný model stránky na papíře pro mobil a pro počítač se šipkami pořadí čtení.'],
        'glossary' => ['landing-page', 'mobile-first'],
    ],
    19 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 19 · Vizuální audit: jak dávat užitečnou zpětnou vazbu',
        'goal' => [
            'student' => 'Na konci hodiny umím projít návrh podle kontrolního seznamu a dát zpětnou vazbu ve tvaru pozorování → dopad → návrh, která se týká návrhu, ne autora.',
            'success_criteria' => ['Každá moje poznámka obsahuje pozorování, dopad na uživatele a konkrétní návrh.', 'Najdu aspoň jeden problém v hierarchii a jeden v čitelnosti.', 'Zpětnou vazbu formuluji k návrhu („nadpis splývá“), ne k osobě.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 2], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: dvě věty', 'teacher' => 'Přečte dvě zpětné vazby: „je to hnusné“ a „nadpis splývá s pozadím, zvětši kontrast“.', 'student' => 'Řeknou, se kterou se dá pracovat a proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Kontrolní seznam', 'teacher' => 'Představí 5 bodů: hierarchie, mezery, kontrast, zarovnání, výzva k akci.', 'student' => 'Projdou ukázkový návrh a u každého bodu napíší ano/ne.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Pozorování → dopad → návrh', 'teacher' => 'Ukáže tvar poznámky na jednom příkladu.', 'student' => 'Přepíší tři své poznámky do tohoto tvaru.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 70, 'phase' => 'Audit ve dvojicích', 'teacher' => 'Hlídá tón poznámek a čas.', 'student' => 'Auditují landing page spolužáka z L18 a zapíší 3 poznámky.', 'form' => 've dvojicích'],
            ['from' => 70, 'to' => 82, 'phase' => 'Priorita oprav', 'teacher' => 'Vysvětlí, že první se opravuje to, co brání použití.', 'student' => 'Seřadí přijaté poznámky podle dopadu.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne tři části dobré poznámky.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Projdi ukázkový návrh podle pěti bodů kontrolního seznamu (hierarchie, mezery, kontrast, zarovnání, výzva k akci) a u každého napiš ano/ne.', 'output' => 'Vyplněný kontrolní seznam s pěti odpověďmi.', 'time' => '15 min'],
            ['text' => 'Přepiš tři své poznámky do tvaru pozorování → dopad → návrh.', 'output' => 'Tři poznámky, např. „Text tlačítka je šedý na bílé → špatně se čte → ztmav ho“.', 'time' => '15 min'],
            ['text' => 'Proveď audit landing page spolužáka a zapiš tři poznámky: jednu k hierarchii, jednu k čitelnosti a jednu k výzvě k akci.', 'output' => 'Tři poznámky ve správném tvaru předané autorovi.', 'time' => '25 min'],
            ['text' => 'Seřaď poznámky, které jsi dostal(a), podle dopadu na uživatele a vyber jednu, kterou opravíš jako první.', 'output' => 'Pořadí poznámek a jedna vybraná oprava se zdůvodněním.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičku se začátky vět („Všiml(a) jsem si…“, „Uživatel proto…“, „Zkus…“).',
            'standard' => 'Kontrolní seznam, tři poznámky ve správném tvaru a seřazení podle dopadu.',
            'challenge' => 'Ke každé poznámce navrhne, jak by ověřil(a), že oprava pomohla (např. test tří sekund).',
        ],
        'assessment' => [
            'formative' => ['Učitel čte poznámky a třída zvedne kartu: chybí pozorování, dopad, nebo návrh?', 'Kontrola tónu: přepiš poznámku o osobě na poznámku o návrhu.'],
            'rubric' => [
                ['criterion' => 'Tvar poznámky', 'levels' => ['Jen hodnocení („hezké“).', 'Pozorování bez dopadu.', 'Pozorování, dopad i návrh.', 'Navrhne i ověření opravy.']],
                ['criterion' => 'Věcnost', 'levels' => ['Poznámka o autorovi.', 'Smíšený tón.', 'Poznámky jen o návrhu.', 'Pomůže autorovi zvolit prioritu.']],
                ['criterion' => 'Priorita', 'levels' => ['Bez pořadí.', 'Pořadí podle vkusu.', 'Pořadí podle dopadu na uživatele.', 'Zdůvodní pořadí i u sporné poznámky.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_composition', 'competence_label' => 'Kompozice a hierarchie', 'variants' => [
            ['question' => 'Která zpětná vazba má tvar pozorování → dopad → návrh?', 'options' => ['Ten nadpis se mi nelíbí.', 'Nadpis má stejnou velikost jako text, uživatel neví, kde začít – zvětši ho.', 'Uděláš to celé znovu.'], 'correct' => 1, 'explanation' => 'Popisuje, co vidím, proč to vadí a co zkusit.'],
            ['question' => 'Co opravíš jako první?', 'options' => ['Tlačítko přihlášky, které na mobilu není vidět.', 'Odstín šedé ve footeru.', 'Zaoblení rohů karet.'], 'correct' => 0, 'explanation' => 'Nejdřív to, co brání použití stránky.'],
            ['question' => 'Jak přepíšeš poznámku „jsi líný, je tu chaos“?', 'options' => ['Nechám ji, je upřímná.', 'Napíšu jen „chaos“.', 'Prvky nejsou zarovnané, oko skáče – zarovnej je k jedné hraně.'], 'correct' => 2, 'explanation' => 'Poznámka patří k návrhu, ne k autorovi.'],
        ]],
        'homework' => [['text' => 'Volitelné: napiš jednu poznámku ve tvaru pozorování → dopad → návrh k plakátu, který potkáš cestou domů.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Auditujeme jen práce spolužáků, kteří s tím souhlasí, a jen ve třídě; nic se nesdílí ven.', 'Poznámky nejsou známka – nehodnotíme autora, ale návrh.'],
        'teacher_notes' => ['Častý omyl: zpětná vazba jen pochvalou nebo jen kritikou bez návrhu – vyžaduj všechny tři části.', 'Otázka do třídy: Jak poznáš, že poznámka autorovi pomohla?', 'Tempo: audit ve dvojicích je jádro hodiny – kontrolní seznam zkrať, pokud start trvá déle.'],
        'substitution' => ['Zástup bez odborníka: rozdá kontrolní seznam a kartičky se začátky vět; žáci auditují vytištěné návrhy.', 'Plán B offline: audit plakátů nebo letáků ve škole podle stejného seznamu.'],
        'glossary' => ['whitespace', 'cta'],
    ],
    20 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 20 · Nejdřív obsah: landing page od textu k rozvržení',
        'goal' => [
            'student' => 'Na konci hodiny umím napsat obsah landing page dřív než rozvržení – hlavní sdělení, tři přínosy a výzvu k akci – a podle něj nakreslit drátěný model.',
            'success_criteria' => ['Hlavní sdělení řeknu jednou větou do 12 slov.', 'Pořadí sekcí v drátěném modelu odpovídá otázkám návštěvníka.', 'Výzva k akci je jen jedna a je jasné, co se stane po kliknutí.'],
        ],
        'competencies' => [['id' => 'web_html_structure', 'level' => 2], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: krabice bez textu', 'teacher' => 'Ukáže krásné rozvržení s textem „Lorem ipsum“ a zeptá se, o čem stránka je.', 'student' => 'Zjistí, že bez obsahu nejde posoudit, jestli stránka funguje.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Hlavní sdělení', 'teacher' => 'Ukáže vzorec: komu – co – proč teď.', 'student' => 'Napíší hlavní sdělení do 12 slov a otestují ho na sousedovi.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Otázky návštěvníka', 'teacher' => 'Vede sběr otázek: co to je, pro koho, kolik stojí, jak se přihlásím.', 'student' => 'Seřadí otázky a ke každé napíší krátkou odpověď.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 65, 'phase' => 'Drátěný model', 'teacher' => 'Předvede drátěný model z obdélníků a skutečných textů.', 'student' => 'Nakreslí drátěný model pro mobil podle pořadí otázek.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 80, 'phase' => 'Test pěti sekund', 'teacher' => 'Vysvětlí test: 5 s pohledu, pak otázka „o čem to je?“.', 'student' => 'Otestují drátěný model na dvou spolužácích.', 'form' => 've skupinách'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: obsah určuje rozvržení, ne naopak.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš hlavní sdělení své stránky do 12 slov podle vzorce komu – co – proč teď.', 'output' => 'Jedna věta hlavního sdělení a počet slov.', 'time' => '12 min'],
            ['text' => 'Sepiš 4 otázky, které si návštěvník položí, seřaď je a ke každé napiš odpověď do dvou vět.', 'output' => 'Seřazený seznam otázek s krátkými odpověďmi.', 'time' => '15 min'],
            ['text' => 'Nakresli drátěný model mobilní verze, ve kterém každá sekce odpovídá jedné otázce, a použij skutečné texty místo „Lorem ipsum“.', 'output' => 'Drátěný model pro šířku 390 px se skutečnými texty.', 'time' => '25 min'],
            ['text' => 'Proveď test pěti sekund na dvou spolužácích a zapiš, co si zapamatovali jako hlavní sdělení.', 'output' => 'Zápis dvou odpovědí a jedna úprava, kterou z testu uděláš.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzor hlavního sdělení a seznam otázek; vybírá a řadí je.',
            'standard' => 'Vlastní sdělení, otázky, drátěný model a test pěti sekund.',
            'challenge' => 'Napíše dvě varianty hlavního sdělení a testem rozhodne, která je srozumitelnější.',
        ],
        'assessment' => [
            'formative' => ['Kolečko: každý přečte své sdělení a třída hádá, pro koho stránka je.', 'Kontrola modelu: ukaž sekci a řekni, na kterou otázku odpovídá.'],
            'rubric' => [
                ['criterion' => 'Hlavní sdělení', 'levels' => ['Chybí.', 'Dlouhé nebo nejasné.', 'Do 12 slov, komu a co.', 'Obstojí v testu pěti sekund.']],
                ['criterion' => 'Pořadí obsahu', 'levels' => ['Náhodné.', 'Částečně podle otázek.', 'Sekce odpovídají seřazeným otázkám.', 'Zdůvodní pořadí z pohledu návštěvníka.']],
                ['criterion' => 'Drátěný model', 'levels' => ['Nevznikl.', 'Model s „Lorem ipsum“.', 'Model mobilu se skutečnými texty.', 'Model upravený podle testu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_ux', 'competence_label' => 'Ovládání a výzva k akci', 'variants' => [
            ['question' => 'Proč psát obsah dřív než rozvržení?', 'options' => ['Protože rozvržení se nedá změnit.', 'Protože pořadí a délka obsahu určují, jak má rozvržení vypadat.', 'Protože text je důležitější než grafika vždycky.'], 'correct' => 1, 'explanation' => 'Rozvržení slouží obsahu – bez něj stavíš krabice naslepo.'],
            ['question' => 'Které hlavní sdělení je nejlepší?', 'options' => ['Víkendový kurz kreslení pro začátečníky, přihlášky do pátku.', 'Vítejte na našich stránkách.', 'Jsme nejlepší, zkuste nás.'], 'correct' => 0, 'explanation' => 'Říká, co to je, pro koho a proč jednat teď.'],
            ['question' => 'Co ověřuje test pěti sekund?', 'options' => ['Jestli se stránka načte do 5 sekund.', 'Kolik obrázků stránka má.', 'Jestli návštěvník po krátkém pohledu pozná, o čem stránka je.'], 'correct' => 2, 'explanation' => 'První dojem musí nést hlavní sdělení.'],
        ]],
        'homework' => [['text' => 'Volitelné: přepiš úvodní větu webu, který znáš, do 12 slov podle vzorce komu – co – proč teď.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Témata a texty stránek jsou vymyšlené; nepoužíváme skutečné firmy, jména ani kontakty.', 'Testy na spolužácích jsou dobrovolné a nic se nezaznamenává.'],
        'teacher_notes' => ['Častý omyl: drátěný model s „Lorem ipsum“ – trvej na skutečných textech, i krátkých.', 'Otázka do třídy: Na co se návštěvník zeptá jako první a odpovídá na to první sekce?', 'Tempo: test pěti sekund lze dát jako rychlou výměnu mezi lavicemi, pokud model zabere víc času.'],
        'substitution' => ['Zástup bez odborníka: rozdá vzorec sdělení a papír s rámečkem mobilu; žáci plní úkoly 1–4.', 'Plán B offline: celý úkol je na papíře – sdělení, otázky, drátěný model a test pěti sekund.'],
        'glossary' => ['wireframe', 'landing-page'],
    ],
    21 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 21 · Úvodní blok stránky pro mobil i počítač',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout úvodní blok (hero) stránky tak, aby na mobilu i na počítači byl vidět nadpis, výzva k akci a podstatná část obrázku.',
            'success_criteria' => ['Na šířce 390 px je nadpis i tlačítko vidět bez posouvání.', 'Pro mobil a počítač mám dva výřezy obrázku se stejným ohniskem.', 'Text nad obrázkem má dostatečný kontrast (aspoň 4,5 : 1).'],
        ],
        'competencies' => [['id' => 'web_css_layout', 'level' => 2], ['id' => 'gfx_formats', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: useknutá hlava', 'teacher' => 'Ukáže hero, kde na mobilu zmizí ohnisko fotky a tlačítko spadne pod okraj.', 'student' => 'Popíší, co se při zúžení pokazilo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Co musí být vidět', 'teacher' => 'Vysvětlí první obrazovku: nadpis, jedna věta, tlačítko.', 'student' => 'Vyznačí v drátěném modelu, co musí být nad okrajem.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Dva výřezy', 'teacher' => 'Předvede výřez na výšku pro mobil a na šířku pro počítač.', 'student' => 'Připraví dva výřezy obrázku se stejným ohniskem.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Hero v CSS', 'teacher' => 'Ukáže min-height, zarovnání a ztmavení pod textem.', 'student' => 'Postaví hero pro mobil a od breakpointu pro počítač.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 80, 'phase' => 'Kontrola kontrastu', 'teacher' => 'Ukáže měření kontrastu textu nad obrázkem.', 'student' => 'Změří kontrast a případně ztmaví podklad.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne pravidla první obrazovky.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'V drátěném modelu vyznač, co musí být na šířce 390 px vidět bez posouvání (nadpis, jedna věta, tlačítko).', 'output' => 'Model s vyznačenou hranou první obrazovky.', 'time' => '12 min'],
            ['text' => 'Připrav dva výřezy jednoho obrázku – na výšku pro mobil a na šířku pro počítač – se stejným ohniskem.', 'output' => 'Dva exportované výřezy a poznámka, kde je ohnisko.', 'time' => '18 min'],
            ['text' => 'Postav hero v CSS: na mobilu nadpis a tlačítko nad okrajem, od breakpointu text vedle obrázku.', 'output' => 'Funkční hero ve dvou rozvrženích.', 'time' => '22 min'],
            ['text' => 'Změř kontrast textu nad obrázkem; když je pod 4,5 : 1, přidej ztmavený podklad a měř znovu.', 'output' => 'Zapsaný kontrast před úpravou a po ní.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane připravené výřezy obrázku a kostru hero; nastavuje jen zarovnání a tlačítko.',
            'standard' => 'Dva výřezy, hero v CSS a měření kontrastu.',
            'challenge' => 'Použije prvek picture, aby prohlížeč sám vybral výřez podle šířky, a ověří to v náhledu.',
        ],
        'assessment' => [
            'formative' => ['Kontrola v náhledu 390 px: je tlačítko vidět bez posouvání?', 'Dvojice: soused řekne, kde je ohnisko obou výřezů.'],
            'rubric' => [
                ['criterion' => 'První obrazovka', 'levels' => ['Tlačítko pod okrajem.', 'Nadpis vidět, tlačítko ne.', 'Nadpis i tlačítko bez posouvání.', 'Platí i na šířce 320 px.']],
                ['criterion' => 'Výřezy obrázku', 'levels' => ['Jeden výřez, ohnisko mizí.', 'Dva výřezy, ohnisko jinde.', 'Dva výřezy se stejným ohniskem.', 'Prohlížeč vybírá výřez sám (picture).']],
                ['criterion' => 'Kontrast textu', 'levels' => ['Neměřen.', 'Pod 4,5 : 1.', 'Aspoň 4,5 : 1.', 'Ověřeno ve světlé i tmavé části obrázku.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_css_layout', 'competence_label' => 'Rozvržení v CSS a mobil', 'variants' => [
            ['question' => 'Co musí být v hero na mobilu vidět bez posouvání?', 'options' => ['Celá fotografie v plné výšce.', 'Patička s kontakty.', 'Nadpis a tlačítko výzvy k akci.'], 'correct' => 2, 'explanation' => 'První obrazovka nese sdělení a akci.'],
            ['question' => 'Proč mít pro mobil a počítač jiný výřez obrázku?', 'options' => ['Aby ohnisko zůstalo vidět i v úzkém formátu na výšku.', 'Protože mobil neumí formát JPEG.', 'Aby byl soubor vždy menší než 1 kB.'], 'correct' => 0, 'explanation' => 'Jiný poměr stran vyžaduje jiný výřez, ohnisko zůstává.'],
            ['question' => 'Text nad fotkou má kontrast 2,8 : 1. Co uděláš?', 'options' => ['Zmenším písmo.', 'Přidám pod text ztmavený podklad a změřím znovu.', 'Nechám to, fotka je hezká.'], 'correct' => 1, 'explanation' => 'Běžný text potřebuje aspoň 4,5 : 1.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť (bez lidí) jeden motiv a nakresli do fotky výřez pro mobil a pro počítač.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Fotografie bez lidí, nebo s jejich souhlasem; cizí obrázky jen s licencí a uvedeným zdrojem.', 'Neukládáme do obrázků údaje o poloze (EXIF) – před exportem je odstraň.'],
        'teacher_notes' => ['Častý omyl: obrázek přes celou výšku mobilu a tlačítko až pod ním – ptej se „co uvidí palec?“.', 'Otázka do třídy: Kde je ohnisko a přežije zúžení na 390 px?', 'Tempo: prvek picture nechej jen pro výzvu; standard zvládne dvě pravidla CSS s breakpointem.'],
        'substitution' => ['Zástup bez odborníka: žáci plní úkoly 1, 2 a 4 v editoru obrázků, úkol 3 podle vytištěné kostry.', 'Plán B offline: výřezy jako rámečky z papíru přiložené na vytištěnou fotku.'],
        'glossary' => ['hero', 'breakpoint'],
    ],
    22 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 22 · Mikrotexty a tlačítka, která říkají, co se stane',
        'goal' => [
            'student' => 'Na konci hodiny umím napsat text tlačítka, chybové hlášky a potvrzení tak, aby uživatel věděl, co se stane a co má udělat dál.',
            'success_criteria' => ['Text tlačítka začíná slovesem a říká výsledek (např. „Odeslat přihlášku“).', 'Chybová hláška říká, co je špatně a jak to opravit, bez obviňování.', 'Po odeslání formuláře je potvrzení s dalším krokem.'],
        ],
        'competencies' => [['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: tlačítko OK', 'teacher' => 'Ukáže dialog s tlačítky „OK“ a „Zrušit“ u otázky „Nechcete neodejít?“.', 'student' => 'Hádají, co které tlačítko udělá.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Sloveso a výsledek', 'teacher' => 'Vysvětlí pravidlo: tlačítko = sloveso + co se stane.', 'student' => 'Přepíší 5 obecných tlačítek.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Chybové hlášky', 'teacher' => 'Ukáže hlášku „Chyba 42“ a její lepší verzi.', 'student' => 'Napíší hlášky ke třem chybám formuláře.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 60, 'phase' => 'Potvrzení a další krok', 'teacher' => 'Ukáže potvrzení s tím, co bude následovat.', 'student' => 'Napíší potvrzení odeslané přihlášky.', 'form' => 'jednotlivě'],
            ['from' => 60, 'to' => 80, 'phase' => 'Test srozumitelnosti', 'teacher' => 'Zadá test: soused bez vysvětlení popíše, co se stane.', 'student' => 'Otestují texty a jeden přepíší.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: text je součást ovládání.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Přepiš pět obecných tlačítek (OK, Odeslat, Klikni sem, Další, Ano) tak, aby každé začínalo slovesem a říkalo výsledek.', 'output' => 'Tabulka: původní text → nový text tlačítka.', 'time' => '15 min'],
            ['text' => 'Napiš chybové hlášky pro tři situace formuláře: prázdné povinné pole, e-mail bez zavináče, datum v minulosti.', 'output' => 'Tři hlášky ve tvaru co je špatně + jak to opravit.', 'time' => '18 min'],
            ['text' => 'Napiš potvrzení po odeslání přihlášky, které řekne, co se stalo a co uživatel dostane dál.', 'output' => 'Text potvrzení do tří vět s dalším krokem.', 'time' => '12 min'],
            ['text' => 'Nech souseda bez vysvětlení říct, co udělá každé tvoje tlačítko, a přepiš to, u kterého se spletl.', 'output' => 'Zápis testu a jedna přepsaná verze tlačítka.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane seznam sloves (odeslat, uložit, stáhnout, přihlásit se) a vzor jedné hlášky.',
            'standard' => 'Tlačítka, tři hlášky, potvrzení a test se sousedem.',
            'challenge' => 'Navrhne i krátkou mikrointerakci (stav tlačítka při odesílání) a popíše, co uživatel uvidí.',
        ],
        'assessment' => [
            'formative' => ['Hlasování: které ze dvou tlačítek je jasnější a proč?', 'Učitel přečte hlášku, třída řekne, jestli obsahuje i opravu.'],
            'rubric' => [
                ['criterion' => 'Text tlačítek', 'levels' => ['Obecné texty.', 'Část se slovesem.', 'Všechna se slovesem a výsledkem.', 'Obstojí v testu bez vysvětlení.']],
                ['criterion' => 'Chybové hlášky', 'levels' => ['Kód chyby nebo obvinění.', 'Co je špatně, bez opravy.', 'Co je špatně i jak opravit.', 'Hláška u pole a bez technického žargonu.']],
                ['criterion' => 'Potvrzení', 'levels' => ['Chybí.', 'Jen „Hotovo“.', 'Co se stalo a další krok.', 'Uvádí i co dělat, když něco nepřijde.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_ux', 'competence_label' => 'Ovládání a výzva k akci', 'variants' => [
            ['question' => 'Který text tlačítka je nejlepší pro odeslání přihlášky na kurz?', 'options' => ['OK', 'Klikni sem', 'Odeslat přihlášku'], 'correct' => 2, 'explanation' => 'Sloveso + výsledek: uživatel ví, co se stane.'],
            ['question' => 'Která chybová hláška pomůže nejvíc?', 'options' => ['Chyba 42.', 'Zadal(a) jsi to špatně.', 'E-mail musí obsahovat @, například jan@example.com.'], 'correct' => 2, 'explanation' => 'Říká, co je špatně, a ukazuje správný tvar.'],
            ['question' => 'Co má obsahovat potvrzení po odeslání formuláře?', 'options' => ['Co se stalo a co bude následovat.', 'Jen slovo „Hotovo“.', 'Odkaz zpět na úvodní stránku a nic víc.'], 'correct' => 0, 'explanation' => 'Uživatel potřebuje jistotu i další krok.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi v aplikaci v telefonu jedno nejasné tlačítko nebo hlášku a přepiš ji podle pravidel z hodiny.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Ukázkové formuláře jen s vymyšlenými údaji; e-maily jen ve tvaru …@example.com.', 'Screenshoty cizích aplikací bez osobních údajů (zakryj jména a čísla).'],
        'teacher_notes' => ['Častý omyl: hláška vyčítá uživateli („Zadal jste špatně“) – veď k neutrálnímu „Zkontroluj…“.', 'Otázka do třídy: Co přesně se stane po kliknutí a dá se to vrátit?', 'Tempo: test se sousedem je krátký – raději ho nevynechávej, je to jádro ověření.'],
        'substitution' => ['Zástup bez odborníka: rozdá pracovní list s pěti tlačítky a třemi chybami; žáci plní úkoly 1–4.', 'Plán B offline: celé na papíře, test se sousedem ústně.'],
        'glossary' => ['cta', 'microinteraction'],
    ],
]]];
