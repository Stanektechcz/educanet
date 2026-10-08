<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 2.A (grafika a webdesign, 2. ročník), lekce 5–10.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php.
 * Kompetence: 2.A zatím nemá katalog v62 (rozhodnutí školy, fáze E/v75) – pole `competencies` je prázdné,
 * exit ticket nese jen popis kompetence (competence_label) a nezapisuje důkaz v62. Vazba na ŠVP: chybí.
 */

return ['class_2a' => ['lessons' => [
    5 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 5 · Design systém: mezery, komponenty, stavy',
        'goal' => [
            'student' => 'Na konci hodiny umím převést vizuální cit na opakovatelný systém: škálu mezer, tři komponenty a jejich stavy v jednoduchém rozhraní.',
            'success_criteria' => ['Používám 4–5 hodnot mezer (spacing) místo náhodných čísel.', 'Tlačítko, karta a štítek sdílí zaoblení, vnitřní okraj a textové role.', 'Rozhraní funguje na šířce 1440 i 390 px.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže rozhraní, kde má každá karta jiné mezery.', 'student' => 'Najdou pět různých mezer, které měly být stejné.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Škála mezer', 'teacher' => 'Předvede simulaci mezer a výběr 4–5 hodnot.', 'student' => 'Zvolí škálu a pojmenují ji (S, M, L…).', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 38, 'phase' => 'Komponenty', 'teacher' => 'Vysvětlí komponentu jako pravidlo.', 'student' => 'Definují tlačítko, kartu a štítek se společnými pravidly.', 'form' => 'jednotlivě'],
            ['from' => 38, 'to' => 53, 'phase' => 'Stavy', 'teacher' => 'Ukáže výchozí, najetí a nedostupný stav.', 'student' => 'Navrhnou stavy a ověří kontrast textu.', 'form' => 've dvojicích'],
            ['from' => 53, 'to' => 80, 'phase' => 'Mini přehled', 'teacher' => 'Hlídá, aby se používal jen vlastní systém.', 'student' => 'Poskládají záhlaví, 3 karty a akci; otestují 1440 a 390 px.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Kontrola systému a exit ticket', 'teacher' => 'Spustí hledání „výjimek“ mimo systém.', 'student' => 'Soused najde hodnotu mimo škálu; exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Zvol škálu 4–5 hodnot mezer a pojmenuj ji tak, aby ji použil i spolužák.', 'output' => 'Tabulka škály mezer.', 'time' => '12 min'],
            ['text' => 'Definuj tlačítko, kartu a štítek se společným zaoblením, vnitřním okrajem a textovými rolemi.', 'output' => 'Tři komponenty s pravidly.', 'time' => '16 min'],
            ['text' => 'Navrhni stavy výchozí, najetí (hover) a nedostupné a ověř kontrast textu v každém.', 'output' => 'Řádek stavů s poznámkou o kontrastu.', 'time' => '15 min'],
            ['text' => 'Poskládej mini přehled (záhlaví, 3 karty, akce) jen ze svého systému a otestuj ho na 1440 a 390 px.', 'output' => 'Dva náhledy rozhraní.', 'time' => '27 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou škálu mezer a kostru přehledu; doplňuje komponenty a stavy.',
            'standard' => 'Vlastní škála, tři komponenty, stavy a přehled podle zadání.',
            'challenge' => 'Popíše systém na jedné stránce dokumentace tak, aby podle ní spolužák postavil čtvrtou kartu.',
        ],
        'assessment' => [
            'formative' => ['Hon na výjimky: soused hledá mezeru nebo barvu mimo systém.', 'Rychlá otázka: kde v systému změníš zaoblení všech karet najednou?'],
            'rubric' => [
                ['criterion' => 'Škála mezer', 'levels' => ['Náhodné mezery.', 'Škála existuje, nedodržuje se.', 'Všechny mezery ze škály.', 'Škálu zdůvodní a zdokumentuje.']],
                ['criterion' => 'Komponenty a stavy', 'levels' => ['Každý prvek jiný.', 'Komponenty bez stavů.', 'Tři komponenty se stavy a kontrastem.', 'Stavy čitelné i bez barvy.']],
                ['criterion' => 'Rozhraní na dvou šířkách', 'levels' => ['Jedna šířka.', 'Mobil se rozpadá.', 'Funguje na 1440 i 390 px.', 'Systém se na mobilu nemění, jen přeskládá.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Design systém', 'variants' => [
            ['question' => 'Proč používáme omezenou škálu mezer?', 'options' => ['Aby rozhraní působilo jednotně a změny šly dělat systémově.', 'Protože jiné mezery prohlížeč nezobrazí.', 'Aby bylo méně práce s exportem.'], 'correct' => 0, 'explanation' => 'Škála = méně náhodných rozhodnutí.'],
            ['question' => 'Tři karty mají různé vnitřní okraje 13, 17 a 22 px. Co uděláš?', 'options' => ['Nechám je, každá karta je jiná.', 'Sjednotím je na jednu hodnotu ze škály.', 'Okraje odstraním.'], 'correct' => 1, 'explanation' => 'Komponenta má jedno pravidlo pro všechny výskyty.'],
            ['question' => 'Co musí platit pro nedostupné tlačítko?', 'options' => ['Musí vypadat stejně jako aktivní.', 'Stačí ho skrýt.', 'Je rozpoznatelné a uživatel ví, proč akce teď nejde.'], 'correct' => 2, 'explanation' => 'Nedostupnost bez vysvětlení mate.'],
        ]],
        'homework' => [['text' => 'Volitelné: v jedné aplikaci v telefonu najdi tři místa se stejnou mezerou a jedno, které systém porušuje.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Obsah rozhraní je vymyšlený; žádná skutečná data uživatelů.', 'Ikony jen s licencí; zdroj zapiš do dokumentace.'],
        'teacher_notes' => ['Častý omyl: „systém“ = paleta barev. Začni mezerami, jsou nejvíc vidět.', 'Otázka do třídy: Kolik různých mezer najdete na své obrazovce?', 'Tempo: přehled je hlavní výstup – škálu omez na 12 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem jsou dva náhledy rozhraní.', 'Plán B offline: škála mezer pravítkem, komponenty jako papírové výstřižky.'],
        'glossary' => ['spacing', 'hover'],
    ],
    6 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 6 · Případová studie do portfolia a mikrointerakce',
        'goal' => [
            'student' => 'Na konci hodiny umím sepsat krátkou případovou studii svého projektu (problém → rozhodnutí → výsledek) a navrhnout mikrointerakci s jasnou funkcí.',
            'success_criteria' => ['Studie má problém, dvě klíčová rozhodnutí a výsledek s ukázkou před/po.', 'Mikrointerakce má spouštěč, zpětnou vazbu a délku.', 'Stav je pochopitelný i bez animace.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže dvě portfolia: galerii obrázků a krátkou studii.', 'student' => 'Řeknou, ze kterého víc poznají, jak autor přemýšlí.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Problém → rozhodnutí → výsledek', 'teacher' => 'Předvede strukturu případové studie (case study).', 'student' => 'Napíšou problém a vyberou dvě rozhodnutí.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Důkazy procesu', 'teacher' => 'Ukáže, jak anotovat před/po a skicu.', 'student' => 'Připraví před/po, jednu skicu a krátké popisky.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Proč animovat', 'teacher' => 'Vysvětlí mikrointerakci: spouštěč → zpětná vazba.', 'student' => 'Vyberou jednu akci (najetí, odeslání, načítání).', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 78, 'phase' => 'Storyboard a prototyp', 'teacher' => 'Hlídá, aby pohyb nesl informaci.', 'student' => 'Nakreslí 4 snímky a vytvoří jednoduchý prototyp.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Obhajoba a exit ticket', 'teacher' => 'Řídí 30sekundové obhajoby ve dvojicích.', 'student' => 'Obhájí studii za 30 s a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Napiš problém svého projektu jednou větou a vyber dvě rozhodnutí, která ho řešila.', 'output' => 'Problém + 2 rozhodnutí.', 'time' => '15 min'],
            ['text' => 'Připrav ukázku před/po, jednu skicu a ke každé krátkou anotaci.', 'output' => 'Tři anotované obrázky.', 'time' => '15 min'],
            ['text' => 'Navrhni mikrointerakci (microinteraction): urči spouštěč, zpětnou vazbu a délku, a nakresli ji ve 4 snímcích.', 'output' => 'Storyboard 4 snímků.', 'time' => '18 min'],
            ['text' => 'Vytvoř jednoduchý prototyp nebo GIF a ověř, že stav pozná i ten, kdo animaci nevidí.', 'output' => 'Prototyp + statický náhled koncového stavu.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu studie s pěti bloky a vybírá mikrointerakci ze tří připravených.',
            'standard' => 'Vlastní studie, storyboard a prototyp podle zadání.',
            'challenge' => 'Přidá do studie jedno měřitelné zjištění (např. kolik spolužáků našlo akci napoprvé).',
        ],
        'assessment' => [
            'formative' => ['Po prvním bloku: soused přečte problém – rozumí mu bez dalšího vysvětlení?', 'Test bez animace: je stav jasný i ze statického snímku?'],
            'rubric' => [
                ['criterion' => 'Struktura studie', 'levels' => ['Jen galerie obrázků.', 'Problém bez rozhodnutí.', 'Problém → 2 rozhodnutí → výsledek.', 'Doplněné měřitelné zjištění.']],
                ['criterion' => 'Mikrointerakce', 'levels' => ['Pohyb bez funkce.', 'Funkce nejasná.', 'Spouštěč, zpětná vazba a délka.', 'Funguje i bez animace.']],
                ['criterion' => 'Obhajoba', 'levels' => ['Nedokončí.', 'Popis bez důvodů.', 'Za 30 s problém a dvě rozhodnutí.', 'Odpoví i na doplňující otázku.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Prezentace práce', 'variants' => [
            ['question' => 'Co do případové studie patří nejvíc?', 'options' => ['Co nejvíc obrázků bez textu.', 'Problém, klíčová rozhodnutí a výsledek s důkazem.', 'Seznam programů, které jsem použil.'], 'correct' => 1, 'explanation' => 'Studie ukazuje přemýšlení, ne jen výsledek.'],
            ['question' => 'Kdy má mikrointerakce smysl?', 'options' => ['Když dává uživateli zpětnou vazbu o tom, co se stalo.', 'Když je stránka nudná.', 'Vždy, čím víc pohybu, tím lépe.'], 'correct' => 0, 'explanation' => 'Pohyb má nést informaci.'],
            ['question' => 'Uživatel má v systému vypnuté animace. Co se má stát s tvou mikrointerakcí?', 'options' => ['Akce přestane fungovat.', 'Animace se přehraje dvakrát rychleji.', 'Stav se ukáže bez pohybu, ale stejně srozumitelně.'], 'correct' => 2, 'explanation' => 'Význam nesmí záviset jen na pohybu.'],
        ]],
        'homework' => [['text' => 'Volitelné: dopiš do studie jeden odstavec „co bych příště udělal jinak“.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Do portfolia jen vlastní práce; práce spolužáků jen s jejich souhlasem a uvedením autora.', 'V portfoliu nezveřejňuj osobní údaje (adresa, telefon).'],
        'teacher_notes' => ['Častý omyl: studie = popis nástrojů. Ptej se „proč jsi to rozhodl takhle?“.', 'Otázka do třídy: Co by se stalo, kdyby animace chyběla?', 'Tempo: prototyp může být i jednoduchý GIF – nenech žáky utopit se v nástroji.'],
        'substitution' => ['Zástup bez odborníka: žáci píšou studii podle úkolů 1–2 a kreslí storyboard (úkol 3).', 'Plán B offline: studie na papíře, storyboard jako komiks o 4 políčkách.'],
        'glossary' => ['case-study', 'microinteraction'],
    ],
    7 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 7 · Přístupnost a responzivní rozhraní',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout sekci rozhraní, která je čitelná, ovladatelná klávesnicí i dotykem a srozumitelná v chybových stavech.',
            'success_criteria' => ['Text má dostatečný kontrast a fokus (focus) je viditelný.', 'Cíle dotyku (touch target) mají aspoň 44 × 44 px.', 'Chybový a nedostupný stav nejsou odlišené jen barvou.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Nechá třídu projít ukázku jen klávesnicí.', 'student' => 'Zapíší, kde se ztratili.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Audit přístupnosti', 'teacher' => 'Předvede Accessibility Audit Lab.', 'student' => 'Ověří kontrast a fokus na ukázce.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Čitelnost a mezery', 'teacher' => 'Připomene textové role a velikost cílů dotyku.', 'student' => 'Nastaví role a mezery, zkontrolují 44 px.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 57, 'phase' => 'Sekce pro počítač', 'teacher' => 'Hlídá viditelný fokus.', 'student' => 'Navrhnou kartu s akcí a stavem fokus.', 'form' => 'jednotlivě'],
            ['from' => 57, 'to' => 78, 'phase' => 'Mobil a stavy', 'teacher' => 'Ukáže chybu vyjádřenou textem a ikonou.', 'student' => 'Převedou sekci na 390 px a navrhnou chybu, nedostupnost a fokus.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Spustí kontrolu v šedi a na mobilu.', 'student' => 'Ověří stavy bez barev a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Proveď audit ukázky: zkontroluj kontrast textu a viditelnost fokusu při ovládání klávesnicí.', 'output' => 'Seznam 3 nálezů.', 'time' => '15 min'],
            ['text' => 'Navrhni kartu s akcí pro počítač s viditelným fokusem a dostatečným kontrastem.', 'output' => 'Karta pro počítač.', 'time' => '17 min'],
            ['text' => 'Převeď kartu na 390 px, zachovej pořadí čtení a cíle dotyku aspoň 44 × 44 px.', 'output' => 'Mobilní verze.', 'time' => '13 min'],
            ['text' => 'Navrhni stavy chyba, nedostupné a fokus tak, aby byly pochopitelné i bez barvy.', 'output' => 'Tři stavy + test v šedi.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam 5 bodů (kontrast, fokus, 44 px, text u chyby, pořadí) a vzorovou kartu.',
            'standard' => 'Audit, karta, mobilní verze a stavy podle zadání.',
            'challenge' => 'Popíše pořadí fokusu na celé sekci a najde místo, kde by se uživatel klávesnice zasekl.',
        ],
        'assessment' => [
            'formative' => ['Tab průchod: učitel projde jeden návrh „jako klávesnice“.', 'Palec: má každé tlačítko aspoň 44 px?'],
            'rubric' => [
                ['criterion' => 'Kontrast a fokus', 'levels' => ['Neověřeno.', 'Ověřen jen kontrast.', 'Kontrast i fokus v pořádku.', 'Vysvětlí, komu které řešení pomáhá.']],
                ['criterion' => 'Mobil a dotyk', 'levels' => ['Bez mobilní verze.', 'Malé cíle dotyku.', 'Pořadí čtení zachované, cíle ≥ 44 px.', 'Ověří i otočení displeje.']],
                ['criterion' => 'Stavy bez barvy', 'levels' => ['Jen barva.', 'Část stavů s textem.', 'Všechny stavy čitelné v šedi.', 'Stavy zdokumentuje pro předání.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Přístupnost rozhraní', 'variants' => [
            ['question' => 'Jak velký má být cíl dotyku tlačítka na mobilu?', 'options' => ['Aspoň zhruba 44 × 44 px, aby se dal pohodlně trefit.', 'Stačí 10 × 10 px, když je vidět.', 'Na velikosti nezáleží.'], 'correct' => 0, 'explanation' => 'Malé cíle se špatně trefují prstem.'],
            ['question' => 'Uživatel ovládá web jen klávesnicí. Co potřebuje nejvíc?', 'options' => ['Animace při najetí myší.', 'Tmavý režim.', 'Viditelný fokus a logické pořadí prvků.'], 'correct' => 2, 'explanation' => 'Fokus je „kurzor“ klávesnice.'],
            ['question' => 'Jak správně ukázat chybu v poli formuláře?', 'options' => ['Jen červeným rámečkem.', 'Textem u pole, ikonou a návodem, jak chybu opravit.', 'Vyskakovacím oknem bez textu.'], 'correct' => 1, 'explanation' => 'Chyba musí být pochopitelná i bez barvy.'],
        ]],
        'homework' => [['text' => 'Volitelné: projdi jeden web jen klávesou Tab a zapiš, kde nebylo vidět, kde jsi.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Weby se jen prohlížejí; žádné přihlašování ani odesílání formulářů.', 'Ukázkový obsah je vymyšlený.'],
        'teacher_notes' => ['Častý omyl: přístupnost = až na konec. Zařaď kontrolu do každé fáze.', 'Otázka do třídy: Kdo všechno používá klávesnici místo myši?', 'Tempo: audit lab nepřetahuj – 15 minut stačí.'],
        'substitution' => ['Zástup bez odborníka: Tab průchod ukázkou, pak úkoly 2–4 podle lekce.', 'Plán B offline: audit vytištěné obrazovky podle kontrolního seznamu, stavy kreslené.'],
        'glossary' => ['focus', 'touch-target'],
    ],
    8 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 8 · Typografie II a návrhové tokeny',
        'goal' => [
            'student' => 'Na konci hodiny umím převést typografii, mezery a barvy do návrhových tokenů a napojit je na varianty komponent tak, aby změna tokenu prošla celým systémem.',
            'success_criteria' => ['Textové role mají hodnoty pro mobil i počítač a nadpis se láme čitelně.', 'Odliším základní tokeny (např. modrá-600) od významových (např. barva-akce).', 'Varianty tlačítka používají jen tokeny a mají stavy najetí, fokus, nedostupné.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Změní jednu barvu ve dvou souborech – v jednom se propíše, v druhém ne.', 'student' => 'Odhadnou, v čem je rozdíl.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Responzivní typografie', 'teacher' => 'Předvede Responsive Type Lab.', 'student' => 'Definují role pro mobil a počítač a ověří zalomení nadpisu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Kontrola šířek', 'teacher' => 'Ukáže nečitelný stav na 768 px.', 'student' => 'Ověří 390 / 768 / 1440 px a opraví jeden problém.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Významové tokeny', 'teacher' => 'Předvede Semantic Token Lab.', 'student' => 'Oddělí základní a významové tokeny a pojmenují je.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 80, 'phase' => 'Varianty komponent', 'teacher' => 'Hlídá, aby komponenty neměly ruční hodnoty.', 'student' => 'Vytvoří varianty tlačítka napojené na tokeny se stavy.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Test změny a exit ticket', 'teacher' => 'Vyzve ke změně jednoho tokenu.', 'student' => 'Změní barvu akce a sledují, co se propsalo; exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Definuj textové role pro mobil a počítač a ověř, že se nadpis láme čitelně na 390 px.', 'output' => 'Tabulka rolí pro dvě šířky.', 'time' => '15 min'],
            ['text' => 'Zkontroluj návrh na 390, 768 a 1440 px a oprav jeden nečitelný stav (řádkování nebo šířka).', 'output' => 'Před/po opravy.', 'time' => '13 min'],
            ['text' => 'Odděl základní a významové návrhové tokeny (design tokens) a pojmenuj tokeny mezer a barev.', 'output' => 'Seznam tokenů ve dvou vrstvách.', 'time' => '15 min'],
            ['text' => 'Vytvoř varianty tlačítka (hlavní, vedlejší) jen z tokenů a se stavy najetí, fokus a nedostupné.', 'output' => 'Varianty tlačítka.', 'time' => '22 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou vrstvu základních tokenů; vytváří jen významové tokeny a jedno tlačítko.',
            'standard' => 'Role, kontrola šířek, tokeny a varianty podle zadání.',
            'challenge' => 'Přidá tmavý motiv jen záměnou významových tokenů a ověří kontrast.',
        ],
        'assessment' => [
            'formative' => ['Test změny: změna jednoho tokenu se propíše – kolik komponent se změnilo?', 'Pojmenování: soused podle názvu tokenu odhadne, kde se používá.'],
            'rubric' => [
                ['criterion' => 'Responzivní typografie', 'levels' => ['Jedna sada velikostí.', 'Dvě sady, nadpis se láme špatně.', 'Role pro obě šířky, čitelné zalomení.', 'Ověřeno i na 768 px s opravou.']],
                ['criterion' => 'Tokeny', 'levels' => ['Ruční hodnoty.', 'Tokeny bez vrstev.', 'Základní a významové tokeny odděleně.', 'Tmavý motiv jen záměnou tokenů.']],
                ['criterion' => 'Varianty komponent', 'levels' => ['Jedna verze.', 'Varianty s ručními hodnotami.', 'Varianty z tokenů se stavy.', 'Změna tokenu se propíše bez ruční opravy.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Design systém', 'variants' => [
            ['question' => 'Který název tokenu je významový?', 'options' => ['modra-600', 'barva-akce-hlavni', '#1a73e8'], 'correct' => 1, 'explanation' => 'Významový token říká k čemu, ne jakou barvou.'],
            ['question' => 'Proč komponenty používají významové tokeny, a ne přímo základní?', 'options' => ['Aby šlo změnit význam (např. barvu akce) na jednom místě.', 'Protože základní tokeny nejdou exportovat.', 'Je to jedno.'], 'correct' => 0, 'explanation' => 'Vrstva významu umožní motivy a změny bez přepisování.'],
            ['question' => 'Nadpis se na 768 px láme na 4 krátké řádky. Co je nejlepší oprava?', 'options' => ['Smazat polovinu nadpisu.', 'Zvětšit písmo.', 'Upravit velikost role pro tuto šířku nebo šířku bloku.'], 'correct' => 2, 'explanation' => 'Opravujeme pravidlo role, ne jednotlivý výskyt.'],
        ]],
        'homework' => [['text' => 'Volitelné: vymysli názvy pěti významových tokenů pro školní web (barvy a mezery).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Pracuje se s vlastním návrhem; žádná data uživatelů.', 'Písma jen s licencí pro web.'],
        'teacher_notes' => ['Častý omyl: token pojmenovaný podle barvy („modrá“) použitý jako význam.', 'Otázka do třídy: Co se stane, až škola změní barvu loga?', 'Tempo: Semantic Token Lab je klíčový – kontrolu šířek klidně zkrať.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem je obrázek variant a seznam tokenů.', 'Plán B offline: tokeny jako tabulka na papíře, tlačítka kreslená.'],
        'glossary' => ['design-token'],
    ],
    9 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 9 · Interakce II a profesionální předání návrhu',
        'goal' => [
            'student' => 'Na konci hodiny umím popsat úplný stavový model interakce, přidat pohyb jen jako zpětnou vazbu a předat komponentu vývojáři se všemi stavy.',
            'success_criteria' => ['Stavový model obsahuje výchozí, načítání, úspěch, chybu, fokus a nedostupné.', 'Varianta bez pohybu zachová význam každého stavu.', 'Předávací list (handoff) obsahuje stavy, tokeny, chování a poznámky k přístupnosti.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže tlačítko, které po kliknutí nic nedělá 3 sekundy.', 'student' => 'Popíšou, co si uživatel myslí.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Stavový model', 'teacher' => 'Předvede State & Motion Lab.', 'student' => 'Sepíšou stavy komponenty a přechody mezi nimi.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Pohyb jako zpětná vazba', 'teacher' => 'Ukáže krátký pohyb, který něco sděluje, a zbytečný pohyb.', 'student' => 'Navrhnou pohyb jen tam, kde nese informaci.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 57, 'phase' => 'Prototyp', 'teacher' => 'Pomáhá propojit větve úspěch a chyba.', 'student' => 'Propojí stavy v prototypu a otestují obě větve.', 'form' => 'jednotlivě'],
            ['from' => 57, 'to' => 72, 'phase' => 'Bez pohybu', 'teacher' => 'Vysvětlí nastavení „omezit pohyb“ (reduced motion).', 'student' => 'Vytvoří variantu bez pohybu a ověří fokus.', 'form' => 've dvojicích'],
            ['from' => 72, 'to' => 90, 'phase' => 'Předání a exit ticket', 'teacher' => 'Ukáže vzor předávacího listu.', 'student' => 'Sepíšou handoff a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Sepiš stavový model tlačítka „Odeslat“: výchozí, načítání, úspěch, chyba, fokus, nedostupné a přechody mezi nimi.', 'output' => 'Diagram stavů.', 'time' => '15 min'],
            ['text' => 'Navrhni krátkou zpětnou vazbu pohybem jen u stavů, kde nese informaci, a zbytečný pohyb odstraň.', 'output' => 'Seznam pohybů s funkcí a délkou.', 'time' => '15 min'],
            ['text' => 'Propoj stavy v prototypu a otestuj větev úspěch i chyba; vytvoř variantu bez pohybu (reduced motion).', 'output' => 'Prototyp se dvěma větvemi a variantou bez pohybu.', 'time' => '32 min'],
            ['text' => 'Sepiš předávací list (handoff): stavy, tokeny, chování a poznámky k přístupnosti.', 'output' => 'Předávací list na 1 stranu.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu stavového diagramu a předávacího listu s nadpisy kapitol.',
            'standard' => 'Stavový model, prototyp, varianta bez pohybu a handoff podle zadání.',
            'challenge' => 'Přidá do handoffu stav „offline“ a popíše, jak se komponenta zotaví po obnovení připojení.',
        ],
        'assessment' => [
            'formative' => ['Po stavovém modelu: soused najde chybějící přechod (např. z chyby zpět).', 'Test bez pohybu: je stav jasný i ze statického snímku?'],
            'rubric' => [
                ['criterion' => 'Stavový model', 'levels' => ['Jen výchozí stav.', 'Stavy bez přechodů.', 'Šest stavů a přechody.', 'Doplněný stav offline a zotavení.']],
                ['criterion' => 'Pohyb', 'levels' => ['Pohyb bez funkce.', 'Funkční, ale dlouhý nebo rušivý.', 'Krátký pohyb jen se zpětnou vazbou.', 'Varianta bez pohybu zachová význam.']],
                ['criterion' => 'Předání', 'levels' => ['Jen snímek obrazovky.', 'Chybí stavy nebo tokeny.', 'Stavy, tokeny, chování, přístupnost.', 'Spolužák podle listu popíše implementaci.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Interakce a předání', 'variants' => [
            ['question' => 'Uživatel má zapnuté „omezit pohyb“. Co uděláš se svou animací načítání?', 'options' => ['Nahradím ji statickým ukazatelem s textem „Načítám…“.', 'Nechám ji, je krátká.', 'Načítání úplně skryji.'], 'correct' => 0, 'explanation' => 'Význam zůstane, pohyb zmizí.'],
            ['question' => 'Co nejvíc chybí v předání, které obsahuje jen obrázek výchozího stavu?', 'options' => ['Jméno autora.', 'Ostatní stavy, chování a pravidla rozměrů.', 'Vyšší rozlišení obrázku.'], 'correct' => 1, 'explanation' => 'Vývojář musí vědět, jak se komponenta chová.'],
            ['question' => 'Kdy má pohyb v rozhraní smysl?', 'options' => ['Když je na stránce málo barev.', 'Vždy, když to nástroj umí.', 'Když uživateli říká, co se stalo nebo kam se něco přesunulo.'], 'correct' => 2, 'explanation' => 'Pohyb je zpětná vazba, ne ozdoba.'],
        ]],
        'homework' => [['text' => 'Volitelné: v jedné aplikaci najdi animaci, která ti něco sděluje, a jednu, která je jen ozdoba.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Prototypy nesbírají skutečná data; vzorové údaje jsou vymyšlené.', 'Při sdílení prototypu nastav přístup jen pro třídu.'],
        'teacher_notes' => ['Častý omyl: chybí cesta zpět z chyby. Ptej se „co uživatel udělá teď?“.', 'Otázka do třídy: Komu může pohyb na webu vadit?', 'Tempo: prototyp je nejdelší část – předávací list může mít jednodušší podobu.'],
        'substitution' => ['Zástup bez odborníka: stavový model a handoff jdou na papíře (úkoly 1 a 4), prototyp podle učebny.', 'Plán B offline: stavy jako komiks, přechody šipkami.'],
        'glossary' => ['handoff', 'reduced-motion'],
    ],
    10 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 10 · Informační architektura a cesta uživatele',
        'goal' => [
            'student' => 'Na konci hodiny umím uspořádat obsah malého webu podle úkolů uživatele, nakreslit mapu webu a jednu cestu uživatele a ověřit je jednoduchým testem.',
            'success_criteria' => ['Obsah je ve skupinách pojmenovaných srozumitelně pro uživatele.', 'Mapa webu má 5–8 stránek bez zbytečných úrovní.', 'Po testu se spolužákem upravím jednu část struktury.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Zadá: najdi na školním webu jídelníček – kolik kliků?', 'student' => 'Popíšou cestu a kde váhali.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Obsah do skupin', 'teacher' => 'Ukáže třídění kartiček podle cíle uživatele.', 'student' => 'Roztřídí obsah do skupin a pojmenují je.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Mapa webu', 'teacher' => 'Připomene: méně úrovní, jasné názvy.', 'student' => 'Nakreslí mapu webu (sitemap) s 5–8 stránkami.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Cesta uživatele', 'teacher' => 'Ukáže diagram cesty od vstupu k cíli.', 'student' => 'Nakreslí jednu cestu uživatele (user flow).', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 75, 'phase' => 'Test stromu', 'teacher' => 'Vysvětlí test bez designu: jen názvy.', 'student' => 'Dají spolužákovi 3 úkoly a zapíší, kde váhal.', 'form' => 've dvojicích'],
            ['from' => 75, 'to' => 90, 'phase' => 'Úprava a exit ticket', 'teacher' => 'Vyzve k jedné změně podle pozorování.', 'student' => 'Upraví strukturu, zapíší před/po a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Roztřiď obsah vymyšleného webu kroužku do skupin podle toho, co uživatel chce udělat, a skupiny pojmenuj.', 'output' => 'Skupiny obsahu s názvy.', 'time' => '15 min'],
            ['text' => 'Nakresli mapu webu (sitemap) s 5–8 stránkami a co nejméně úrovněmi.', 'output' => 'Mapa webu.', 'time' => '15 min'],
            ['text' => 'Nakresli cestu uživatele (user flow) od vstupu na web k přihlášce do kroužku.', 'output' => 'Diagram cesty.', 'time' => '15 min'],
            ['text' => 'Dej spolužákovi 3 úkoly nad názvy stránek (bez designu), zapiš, kde váhal, a uprav jednu část struktury.', 'output' => 'Záznam testu + před/po úpravy.', 'time' => '30 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičky obsahu a vzor mapy webu; třídí a doplňuje názvy.',
            'standard' => 'Skupiny, mapa webu, cesta uživatele a test podle zadání.',
            'challenge' => 'Navrhne druhou variantu mapy s jinými názvy skupin a porovná výsledky testu.',
        ],
        'assessment' => [
            'formative' => ['Po pojmenování skupin: soused podle názvu odhadne obsah skupiny.', 'Test: kolik úkolů spolužák splnil napoprvé?'],
            'rubric' => [
                ['criterion' => 'Skupiny a názvy', 'levels' => ['Obsah netříděný.', 'Skupiny s interními názvy.', 'Skupiny podle cíle uživatele, srozumitelné názvy.', 'Názvy ověřené testem.']],
                ['criterion' => 'Mapa a cesta', 'levels' => ['Chybí.', 'Moc úrovní, nejasná cesta.', '5–8 stránek a jasná cesta k cíli.', 'Cesta má jen nutné rozhodovací body.']],
                ['criterion' => 'Test a úprava', 'levels' => ['Bez testu.', 'Test bez záznamu.', 'Zapsané váhání a jedna úprava.', 'Úprava zdůvodněná pozorováním.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Informační architektura', 'variants' => [
            ['question' => 'Proč se test stromu dělá bez grafického návrhu?', 'options' => ['Aby vzhled nezakryl problém ve struktuře a názvech.', 'Protože design ještě nikdo neumí.', 'Aby byl test rychlejší.'], 'correct' => 0, 'explanation' => 'Test ověřuje strukturu, ne vzhled.'],
            ['question' => 'Co je mapa webu (sitemap)?', 'options' => ['Seznam obrázků na webu.', 'Přehled stránek webu a jejich vztahů.', 'Návod k instalaci webu.'], 'correct' => 1, 'explanation' => 'Mapa ukazuje strukturu stránek.'],
            ['question' => 'Spolužák při testu hledal „Přihlášku“ ve skupině „Aktuality“. Co z toho plyne?', 'options' => ['Spolužák je nepozorný.', 'Je potřeba přidat víc stránek.', 'Název nebo umístění přihlášky neodpovídá očekávání – uprav strukturu.'], 'correct' => 2, 'explanation' => 'Váhání je důkaz problému ve struktuře.'],
        ]],
        'homework' => [['text' => 'Volitelné: zkus na jednom webu najít konkrétní informaci a zapiš počet kliků a kde jsi váhal.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Web kroužku je vymyšlený; test probíhá jen ve třídě a bez nahrávání.', 'Do záznamu testu nepiš jméno testujícího – stačí „spolužák A“.'],
        'teacher_notes' => ['Testujte strukturu bez vizuálu – vzhled maskuje problémy architektury.', 'Otázka do třídy: Jak by tuhle skupinu pojmenoval někdo, kdo web nezná?', 'Tempo: test je klíčový – kartičky omez na 15 minut.'],
        'substitution' => ['Zástup bez odborníka: kartičky obsahu, mapa a cesta na papíře, test ve dvojicích.', 'Plán B offline: celá hodina s papírovými kartičkami.'],
        'glossary' => ['sitemap', 'user-flow'],
    ],
]]];
