<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 2.A (grafika a webdesign, 2. ročník), lekce 11–16.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php.
 * Kompetence: 2.A zatím bez katalogu v62 (v75) – exit ticket jen s popisem kompetence. Vazba na ŠVP: chybí.
 */

return ['class_2a' => ['lessons' => [
    11 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 11 · Formuláře: validace, chyby a stavy',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout formulář jako stavový systém – od nápovědy přes odesílání až po chybu a zotavení bez ztráty vyplněných dat.',
            'success_criteria' => ['Mám tabulku stavů pole i celého formuláře (výchozí, fokus, vyplněno, chyba, nedostupné, odesílám, úspěch).', 'Chybové hlášky jsou konkrétní a říkají, jak chybu opravit.', 'Po chybě zůstanou správně vyplněná pole zachovaná a pozornost se přesune k problému.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže formulář, který po chybě smaže všechna pole.', 'student' => 'Popíšou, jak by se cítili jako uživatelé.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Soupis stavů', 'teacher' => 'Rozliší stavy pole a stavy celého formuláře.', 'student' => 'Sepíšou tabulku stavů.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Validace (validation)', 'teacher' => 'Ukáže nápovědu předem vs. chybu po odeslání.', 'student' => 'Napíšou nápovědy a chybové hlášky pro 3 pole.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Odesílání', 'teacher' => 'Vysvětlí riziko dvojího odeslání.', 'student' => 'Navrhnou průběh po kliknutí na Odeslat.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Zotavení a prototyp', 'teacher' => 'Hlídá zachování vyplněných dat.', 'student' => 'Propojí větev úspěch i chyba v prototypu a projdou ho klávesnicí.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Vybere dva prototypy k ukázce chybové větve.', 'student' => 'Ověří chybovou větev a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Sepiš tabulku stavů: které patří jednomu poli a které celému formuláři.', 'output' => 'Tabulka stavů.', 'time' => '15 min'],
            ['text' => 'Pro pole jméno, e-mail a heslo napiš nápovědu předem a konkrétní chybovou hlášku.', 'output' => 'Šest textů (3 nápovědy, 3 chyby).', 'time' => '15 min'],
            ['text' => 'Navrhni stav „odesílám“, který zabrání nejasnému dvojímu odeslání.', 'output' => 'Stav tlačítka a formuláře při odesílání.', 'time' => '15 min'],
            ['text' => 'Propoj v prototypu větev úspěch i chyba, zachovej správně vyplněná data a projdi prototyp klávesnicí.', 'output' => 'Prototyp se dvěma větvemi.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzorovou tabulku stavů s polovinou vyplněnou a vzor chybové hlášky.',
            'standard' => 'Tabulka, texty, odesílání a prototyp podle zadání.',
            'challenge' => 'Přidá souhrn chyb nahoře formuláře s odkazy na pole a popíše, kam se přesune fokus.',
        ],
        'assessment' => [
            'formative' => ['Hláška nahlas: třída řekne, zda ví, co přesně opravit.', 'Kontrola zotavení: zůstalo po chybě vyplněné jméno?'],
            'rubric' => [
                ['criterion' => 'Stavový model', 'levels' => ['Jen výchozí stav.', 'Stavy bez rozlišení pole/formulář.', 'Úplná tabulka stavů.', 'Doplněné přechody mezi stavy.']],
                ['criterion' => 'Texty validace', 'levels' => ['„Chyba.“', 'Obecné hlášky.', 'Konkrétní hlášky s návodem.', 'Nápověda předchází chybám.']],
                ['criterion' => 'Zotavení', 'levels' => ['Data se ztratí.', 'Data zůstanou, fokus ne.', 'Data zůstanou a fokus jde na chybu.', 'Souhrn chyb s odkazy na pole.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Formuláře a stavy', 'variants' => [
            ['question' => 'Uživatel odeslal formulář s chybou v e-mailu. Co se má stát s ostatními vyplněnými poli?', 'options' => ['Zůstanou vyplněná, opraví jen e-mail.', 'Vymažou se, ať začne znovu.', 'Zablokují se.'], 'correct' => 0, 'explanation' => 'Zotavení po chybě nesmí trestat uživatele.'],
            ['question' => 'Jaký je rozdíl mezi nápovědou a chybovou hláškou?', 'options' => ['Žádný, jsou to synonyma.', 'Nápověda je vždy červená.', 'Nápověda radí předem, chyba popisuje problém po kontrole.'], 'correct' => 2, 'explanation' => 'Dobrá nápověda předchází chybám.'],
            ['question' => 'Proč tlačítko během odesílání ukáže „Odesílám…“ a je dočasně nedostupné?', 'options' => ['Aby vypadalo moderně.', 'Aby uživatel věděl, že se něco děje, a neodeslal formulář dvakrát.', 'Aby uživatel počkal na reklamu.'], 'correct' => 1, 'explanation' => 'Jasný stav brání dvojímu odeslání.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi jeden registrační formulář a zapiš, jak hlásí chybu hesla.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Do skutečných formulářů nic neodesíláme; testovací údaje jsou vymyšlené.', 'Nikdy nepoužívej svá skutečná hesla v prototypu.'],
        'teacher_notes' => ['Hodnoť zotavení po chybě, ne jen krásný výchozí stav.', 'Otázka do třídy: Kdy je nedostupné tlačítko bez vysvětlení problém?', 'Tempo: prototyp zabere nejvíc času – texty lze dopsat doma.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 na papíře, prototyp podle učebny.', 'Plán B offline: stavy formuláře kreslené jako komiks.'],
        'glossary' => ['validation'],
    ],
    12 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 12 · Auto Layout, varianty a responzivní komponenty',
        'goal' => [
            'student' => 'Na konci hodiny umím postavit komponentu s automatickým rozvržením (Auto Layout) a variantami tak, aby vydržela dlouhý text i úzký displej bez ručních výjimek.',
            'success_criteria' => ['Rozliším chování „přizpůsobit obsahu“, „vyplnit“ a „pevně“ a vysvětlím rozdíl vnitřního okraje a mezery.', 'Tlačítko vydrží delší text, karta má variantu kompaktní a výchozí.', 'Při zátěžovém testu najdu a opravím dva problémy.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Vloží do tlačítka dlouhé slovo a tlačítko se rozpadne.', 'student' => 'Odhadnou, proč se to stalo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Mentální model', 'teacher' => 'Vysvětlí hug / fill / fixed a padding vs. gap.', 'student' => 'Na ukázce určí chování každého prvku.', 'form' => 've dvojicích'],
            ['from' => 22, 'to' => 37, 'phase' => 'Odolné tlačítko', 'teacher' => 'Ukáže tlačítko s automatickým rozvržením.', 'student' => 'Postaví tlačítko, které unese delší text.', 'form' => 'jednotlivě'],
            ['from' => 37, 'to' => 55, 'phase' => 'Varianty karty', 'teacher' => 'Připomene pojmenování variant podle významu.', 'student' => 'Vytvoří kartu kompaktní a výchozí se stejnými tokeny.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 75, 'phase' => 'Vnořené rozvržení', 'teacher' => 'Hlídá, aby se nepoužívalo absolutní umístění.', 'student' => 'Složí kartu z menších komponent.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Zátěžový test a exit ticket', 'teacher' => 'Rozdá extrémní vstupy: dlouhý text, malý displej, chybějící obrázek.', 'student' => 'Otestují komponentu, zapíší 2 opravy a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Na ukázce urči u každého prvku chování hug, fill nebo fixed a vysvětli rozdíl mezi vnitřním okrajem (padding) a mezerou (gap).', 'output' => 'Popsaná ukázka.', 'time' => '12 min'],
            ['text' => 'Postav tlačítko s automatickým rozvržením (Auto Layout), které unese i dvakrát delší text.', 'output' => 'Tlačítko + test s dlouhým textem.', 'time' => '15 min'],
            ['text' => 'Vytvoř kartu ve variantě kompaktní a výchozí se stejnými tokeny a skládej ji z menších komponent.', 'output' => 'Dvě varianty karty.', 'time' => '38 min'],
            ['text' => 'Proveď zátěžový test (dlouhý text, šířka 320 px, chybějící obrázek) a zapiš dvě opravy.', 'output' => 'Záznam testu a dvou oprav.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotové tlačítko s Auto Layoutem jako vzor; staví jen kartu.',
            'standard' => 'Tlačítko, dvě varianty karty a zátěžový test podle zadání.',
            'challenge' => 'Přidá variantu karty pro tmavý motiv jen přes tokeny a otestuje ji stejnými vstupy.',
        ],
        'assessment' => [
            'formative' => ['Rychlé určení: učitel ukáže prvek, žáci řeknou hug, fill nebo fixed.', 'Zátěžový test: rozpadne se něco při 320 px?'],
            'rubric' => [
                ['criterion' => 'Model rozvržení', 'levels' => ['Nerozliší chování.', 'Rozliší s chybami.', 'Správně určí a vysvětlí padding vs. gap.', 'Vysvětlí to spolužákovi na vlastní komponentě.']],
                ['criterion' => 'Odolnost komponent', 'levels' => ['Rozpadá se.', 'Unese delší text, ne úzký displej.', 'Vydrží všechny tři vstupy.', 'Bez jediné ruční výjimky.']],
                ['criterion' => 'Varianty', 'levels' => ['Jedna verze.', 'Varianty s nejasnými názvy.', 'Varianty pojmenované podle významu.', 'Tmavý motiv přes tokeny.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Komponenty a rozvržení', 'variants' => [
            ['question' => 'Co znamená, že prvek „vyplňuje“ (fill) dostupný prostor?', 'options' => ['Má pevnou šířku.', 'Roztáhne se do šířky rodiče.', 'Je tak velký jako jeho text.'], 'correct' => 1, 'explanation' => 'Fill = přizpůsobí se rodiči.'],
            ['question' => 'Jaký je rozdíl mezi vnitřním okrajem (padding) a mezerou (gap)?', 'options' => ['Padding je uvnitř kolem obsahu, gap je mezi prvky.', 'Jsou to dvě jména pro totéž.', 'Gap je jen na mobilu.'], 'correct' => 0, 'explanation' => 'Padding obaluje, gap rozestupuje.'],
            ['question' => 'V deseti kartách ručně posouváš nadpis o 4 px. Co to znamená?', 'options' => ['Je to běžná práce, nic neměním.', 'Mám malý monitor.', 'Pravidlo komponenty je špatně – opravím ho v hlavní komponentě.'], 'correct' => 2, 'explanation' => 'Opakovaná výjimka patří do pravidla.'],
        ]],
        'homework' => [['text' => 'Volitelné: v jedné aplikaci najdi tlačítko a zkus odhadnout, zda se přizpůsobuje textu, nebo má pevnou šířku.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Pracuje se v návrhovém nástroji se školním účtem; sdílení jen se třídou.', 'Obsah karet je vymyšlený.'],
        'teacher_notes' => ['Neomezuj výuku na mechaniku nástroje – žák má vysvětlit pravidlo rozvržení.', 'Otázka do třídy: Co se stane s komponentou, když do ní dáme text v němčině?', 'Tempo: vnořené rozvržení je těžké – slabší žáci mohou skončit u jedné varianty.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 4 jdou i na papíře, úkoly 2–3 v nástroji podle návodu v lekci.', 'Plán B offline: komponenty z papírových proužků, které se „roztahují“.'],
        'glossary' => ['auto-layout', 'padding', 'gap'],
    ],
    13 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 13 · Audit přístupnosti webového návrhu',
        'goal' => [
            'student' => 'Na konci hodiny umím provést strukturovaný audit přístupnosti návrhu (kontrast, fokus, pořadí, cíle dotyku, texty, stavy), seřadit nálezy podle závažnosti a opravit dva nejdůležitější.',
            'success_criteria' => ['Projdu audit v osmi bodech a zapíšu nálezy.', 'Nálezy seřadím na vysoké, střední a nízké.', 'Dvě největší bariéry opravím a doložím před/po.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Pustí ukázku čtečky obrazovky (screen reader) nad špatně popsaným tlačítkem.', 'student' => 'Popíšou, co uživatel uslyšel.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Kontrolní seznam', 'teacher' => 'Projde 8 bodů auditu.', 'student' => 'Zkontrolují vlastní prototyp podle seznamu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Klávesnice', 'teacher' => 'Ukáže očekávané pořadí fokusu.', 'student' => 'Seřadí pořadí fokusu a ověří, že fokus není zakrytý.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 55, 'phase' => 'Zvětšení a přeskládání', 'teacher' => 'Zvětší text na 200 % a zúží okno.', 'student' => 'Najdou oříznutý obsah nebo vodorovné posouvání.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Priorita a oprava', 'teacher' => 'Vysvětlí závažnost = dopad × četnost.', 'student' => 'Seřadí nálezy a opraví dvě největší bariéry.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Sdílení a exit ticket', 'teacher' => 'Vybere dvě opravy k ukázce před/po.', 'student' => 'Ukážou opravu a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Projdi svůj prototyp podle 8 bodů auditu (kontrast, fokus, pořadí, cíle dotyku, popisky, chyby, zvětšení, ne jen barva).', 'output' => 'Vyplněný kontrolní seznam s nálezy.', 'time' => '15 min'],
            ['text' => 'Zapiš očekávané pořadí fokusu (focus order) na stránce a ověř, že fokus nikde není zakrytý.', 'output' => 'Číslované pořadí fokusu.', 'time' => '15 min'],
            ['text' => 'Ověř zvětšení textu na 200 % a úzké okno; zapiš oříznutí nebo vodorovné posouvání.', 'output' => 'Seznam problémů se zvětšením.', 'time' => '15 min'],
            ['text' => 'Seřaď nálezy na vysoké / střední / nízké a oprav dva nejzávažnější; ulož před/po.', 'output' => 'Seřazený seznam + 2 opravy před/po.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam s příklady chyb ke každému bodu a audituje připravenou ukázku.',
            'standard' => 'Audit vlastního prototypu, priorita a dvě opravy.',
            'challenge' => 'Napíše krátkou zprávu pro tým: tři nejčastější chyby a jak jim předcházet už při návrhu komponent.',
        ],
        'assessment' => [
            'formative' => ['Po kontrolním seznamu: každý řekne jeden nález a jeho dopad.', 'Priorita: proč je tenhle nález „vysoký“?'],
            'rubric' => [
                ['criterion' => 'Systematičnost auditu', 'levels' => ['Náhodné nálezy.', 'Část bodů.', 'Všech 8 bodů s nálezy.', 'Nálezy s dopadem na konkrétní uživatele.']],
                ['criterion' => 'Prioritizace', 'levels' => ['Bez pořadí.', 'Pořadí bez důvodu.', 'Vysoké / střední / nízké podle dopadu.', 'Zdůvodní dopad × četnost.']],
                ['criterion' => 'Opravy', 'levels' => ['Žádná.', 'Oprava méně závažného.', 'Dvě největší bariéry opravené.', 'Doložené před/po a prevence do komponent.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Přístupnost rozhraní', 'variants' => [
            ['question' => 'Při zvětšení textu na 200 % musíš stránku posouvat do stran. Co to je?', 'options' => ['Problém přeskládání obsahu, který je potřeba opravit.', 'Normální chování, za to návrh nemůže.', 'Chyba prohlížeče.'], 'correct' => 0, 'explanation' => 'Obsah se má přeskládat, ne utéct do strany.'],
            ['question' => 'Jak určíš, která chyba přístupnosti má přednost?', 'options' => ['Podle toho, kterou je nejsnazší opravit.', 'Podle abecedy.', 'Podle dopadu na uživatele a toho, jak často nastává.'], 'correct' => 2, 'explanation' => 'Závažnost = dopad × četnost.'],
            ['question' => 'Kdy je nejlepší řešit přístupnost?', 'options' => ['Až po exportu hotového webu.', 'Už při návrhu komponent a průchodů.', 'Jen když si někdo stěžuje.'], 'correct' => 1, 'explanation' => 'Pozdní opravy jsou dražší.'],
        ]],
        'homework' => [['text' => 'Volitelné: zvětši na jednom webu text na 200 % (Ctrl a +) a zapiš, co se rozbilo.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Audituje se vlastní prototyp nebo připravená ukázka; cizí weby se nemění.', 'Nálezy se nesdílejí veřejně ani s autory cizích webů bez souhlasu učitele.'],
        'teacher_notes' => ['Nehodnoť zapamatování čísel – důležitý je systematický audit a oprava.', 'Otázka do třídy: Komu konkrétně tahle chyba vadí?', 'Tempo: používej reálné prototypy žáků, ušetří to čas na přípravu ukázky.'],
        'substitution' => ['Zástup bez odborníka: audit podle tištěného seznamu, úkoly 1–4 podle lekce.', 'Plán B offline: audit vytištěných obrazovek.'],
        'glossary' => ['focus', 'screen-reader'],
    ],
    14 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 14 · Od návrhu k HTML a CSS: předání, které jde postavit',
        'goal' => [
            'student' => 'Na konci hodiny umím převést návrh na specifikaci pro vývojáře: strukturu stránky, tokeny, komponenty, pravidla responzivity a tabulku stavů.',
            'success_criteria' => ['Obrazovku rozdělím na záhlaví, hlavní obsah, sekce a zápatí a komponenty pojmenuji podle funkce.', 'Tokeny barev, mezer a písma nahradí seznam náhodných pixelů.', 'Spolužák podle mé specifikace popíše implementaci a já opravím dvě nejasnosti.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže předání „jeden obrázek“ a zeptá se, co vývojáři chybí.', 'student' => 'Vyjmenují chybějící informace.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Struktura stránky', 'teacher' => 'Ukáže vztah návrhu a struktury HTML (header, main, section, footer).', 'student' => 'Rozdělí obrazovku a pojmenují komponenty podle funkce.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Tokeny', 'teacher' => 'Ukáže seznam tokenů místo 40 náhodných hodnot.', 'student' => 'Zapíší tokeny barev, mezer a písma.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Pravidla responzivity', 'teacher' => 'Vysvětlí maximální šířku a chování obrázků.', 'student' => 'Popíšou, kdy se rozvržení skládá pod sebe.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 70, 'phase' => 'Stavy', 'teacher' => 'Připomene tabulku stavů a klávesnici.', 'student' => 'Sepíšou stavy komponent a poznámky ke klávesnici.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 90, 'phase' => 'Kontrola „vývojářem“ a exit ticket', 'teacher' => 'Spáruje žáky do rolí návrhář a vývojář.', 'student' => 'Spolužák popíše implementaci, autor opraví 2 nejasnosti; exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Rozděl svou obrazovku na záhlaví, hlavní obsah, sekce a zápatí a pojmenuj komponenty podle funkce.', 'output' => 'Popsaná struktura obrazovky.', 'time' => '15 min'],
            ['text' => 'Zapiš tokeny barev, mezer a písma, které návrh používá.', 'output' => 'Tabulka tokenů.', 'time' => '15 min'],
            ['text' => 'Popiš pravidla responzivity: maximální šířka obsahu, kdy se sloupce skládají pod sebe, jak se chovají obrázky.', 'output' => '3–5 pravidel.', 'time' => '15 min'],
            ['text' => 'Doplň tabulku stavů komponent a nech spolužáka podle celé specifikace (handoff) popsat implementaci; oprav 2 nejasnosti.', 'output' => 'Tabulka stavů + 2 opravy.', 'time' => '30 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu specifikace s nadpisy a vzorem jednoho vyplněného řádku.',
            'standard' => 'Úplná specifikace na 1 stranu a kontrola spolužákem.',
            'challenge' => 'Postaví jednu sekci v HTML a CSS podle vlastní specifikace a porovná výsledek s návrhem.',
        ],
        'assessment' => [
            'formative' => ['Po struktuře: soused podle názvů komponent odhadne jejich funkci.', 'Kontrola vývojářem: kolik otázek musel položit?'],
            'rubric' => [
                ['criterion' => 'Struktura a názvy', 'levels' => ['Bez struktury.', 'Struktura, názvy podle vzhledu.', 'Struktura a názvy podle funkce.', 'Odpovídá sémantickým prvkům HTML.']],
                ['criterion' => 'Tokeny a pravidla', 'levels' => ['Náhodné pixely.', 'Tokeny bez responzivity.', 'Tokeny i pravidla responzivity.', 'Pravidla ověřená na třech šířkách.']],
                ['criterion' => 'Srozumitelnost předání', 'levels' => ['Jen obrázek.', 'Hodně nejasností.', 'Spolužák popíše implementaci.', 'Mini implementace odpovídá návrhu.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Interakce a předání', 'variants' => [
            ['question' => 'Proč komponenty pojmenováváme podle funkce (např. „karta-akce“), a ne podle vzhledu („modrá-krabice“)?', 'options' => ['Vzhled se může změnit, funkce zůstává.', 'Názvy podle barev jsou zakázané.', 'Kvůli délce názvu.'], 'correct' => 0, 'explanation' => 'Funkční název přežije redesign.'],
            ['question' => 'Co je v předání nejužitečnější místo 40 různých hodnot v pixelech?', 'options' => ['Snímek obrazovky ve vyšším rozlišení.', 'Malý soubor pojmenovaných tokenů.', 'Video z návrhového nástroje.'], 'correct' => 1, 'explanation' => 'Tokeny jsou opakovatelná pravidla.'],
            ['question' => 'Který prvek HTML odpovídá hlavnímu obsahu stránky?', 'options' => ['footer', 'header', 'main'], 'correct' => 2, 'explanation' => 'main obaluje hlavní obsah stránky.'],
        ]],
        'homework' => [['text' => 'Volitelné: v prohlížeči otevři nástroje vývojáře na jednom webu a najdi prvky header, main a footer.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Nástroje vývojáře používáme jen ke čtení – produkční weby neměníme.', 'Specifikace neobsahuje skutečná data uživatelů.'],
        'teacher_notes' => ['Nemusí se psát celý kód – cílem je spojit návrh a implementační myšlení.', 'Otázka do třídy: Co by se vývojář musel zeptat, kdyby měl jen obrázek?', 'Tempo: kontrola ve dvojicích je jádro hodiny, začni ji nejpozději v 70. minutě.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 jako papírová specifikace, kontrola ve dvojicích.', 'Plán B offline: celá specifikace ručně na A4.'],
        'glossary' => ['handoff'],
    ],
    15 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 15 · Rozvržení v CSS: Flexbox, Grid a responzivní pravidla',
        'goal' => [
            'student' => 'Na konci hodiny umím rozhodnout, kdy rozvržení postavit jako Flexbox (jedna osa) a kdy jako Grid (dvě osy), a popsat, jak se mřížka karet mění 3 → 2 → 1 sloupec.',
            'success_criteria' => ['U tří částí stránky zdůvodním volbu Flexbox, nebo Grid.', 'Mřížka karet se mění 3 → 2 → 1 bez pevné šířky, která přetéká.', 'U existující stránky najdu kontejner, mezeru, maximální šířku a směr rozvržení.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže web, kde karty na mobilu přetékají ven z obrazovky.', 'student' => 'Odhadnou příčinu (pevná šířka).', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Flex vs. Grid', 'teacher' => 'Vysvětlí jednu osu vs. dvě osy na příkladech.', 'student' => 'Zařadí 5 příkladů rozvržení.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Úvodní sekce jako pravidlo', 'teacher' => 'Ukáže dva sloupce na počítači a jeden na mobilu.', 'student' => 'Popíšou pravidlo úvodní sekce.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 58, 'phase' => 'Mřížka karet', 'teacher' => 'Ukáže mřížku 3 → 2 → 1 bez pevných šířek.', 'student' => 'Navrhnou mřížku karet a zapíší pravidlo.', 'form' => 'jednotlivě'],
            ['from' => 58, 'to' => 75, 'phase' => 'Prohlížení existující stránky', 'teacher' => 'Předvede nástroje vývojáře jen pro čtení.', 'student' => 'Najdou kontejner, mezeru, maximální šířku a směr.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Poznámky k implementaci a exit ticket', 'teacher' => 'Zadá poznámky ke třem komponentám.', 'student' => 'Napíšou model rozvržení a chování a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zařaď pět příkladů rozvržení (menu, karty, formulář, galerie, zápatí) jako Flexbox, nebo Grid a zdůvodni.', 'output' => 'Pět rozhodnutí s důvodem.', 'time' => '15 min'],
            ['text' => 'Popiš pravidlo úvodní sekce: dva sloupce na počítači, jeden na mobilu.', 'output' => 'Pravidlo v jedné až dvou větách.', 'time' => '15 min'],
            ['text' => 'Navrhni mřížku karet, která se mění 3 → 2 → 1 sloupec a nikde nemá pevnou šířku, která přetéká.', 'output' => 'Náčrt tří stavů mřížky + pravidlo.', 'time' => '18 min'],
            ['text' => 'V nástrojích vývojáře (jen ke čtení) najdi na existující stránce kontejner, mezeru (gap), maximální šířku a směr rozvržení.', 'output' => 'Čtyři zjištěné hodnoty.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičky s obrázky rozvržení a nápovědu „řada nebo tabulka?“.',
            'standard' => 'Rozhodnutí, pravidla a prohlížení podle zadání.',
            'challenge' => 'Napíše mřížku karet v CSS (grid-template-columns s auto-fit) a ověří ji na třech šířkách.',
        ],
        'assessment' => [
            'formative' => ['Ukaž rukou: jedna ruka = jedna osa (Flex), dvě ruce = dvě osy (Grid).', 'Kontrola mřížky: kde by karta přetekla?'],
            'rubric' => [
                ['criterion' => 'Volba modelu', 'levels' => ['Náhodná.', 'Správně bez důvodu.', 'Správně se zdůvodněním.', 'Ukáže hranici, kdy se volba mění.']],
                ['criterion' => 'Responzivní pravidla', 'levels' => ['Jen popis vzhledu.', 'Pevné šířky.', 'Mřížka 3 → 2 → 1 bez přetečení.', 'Ověřeno nebo napsané v CSS.']],
                ['criterion' => 'Čtení existující stránky', 'levels' => ['Nenajde nic.', 'Jedna hodnota.', 'Všechny čtyři hodnoty.', 'Vysvětlí, proč autor zvolil daný model.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Komponenty a rozvržení', 'variants' => [
            ['question' => 'Navigační lišta s položkami v jedné řadě – který model je nejpřirozenější?', 'options' => ['Grid se dvěma osami.', 'Flexbox v jedné ose.', 'Absolutní umístění.'], 'correct' => 1, 'explanation' => 'Jedna řada = jedna osa.'],
            ['question' => 'Galerie fotek v řádcích i sloupcích, které se mají zarovnat – co použiješ?', 'options' => ['Grid.', 'Jen vnitřní okraje.', 'Tabulku s pevnými šířkami.'], 'correct' => 0, 'explanation' => 'Dvě osy zarovnání = Grid.'],
            ['question' => 'Proč karty na mobilu přetékají ven z obrazovky?', 'options' => ['Protože mobil neumí CSS.', 'Protože je moc barev.', 'Často kvůli pevné šířce, která je větší než displej.'], 'correct' => 2, 'explanation' => 'Pevná šířka nebere ohled na displej.'],
        ]],
        'homework' => [['text' => 'Volitelné: na jednom webu zmenšuj okno prohlížeče a zapiš, při jaké šířce se karty přeskládají.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Nástroje vývojáře jen ke čtení; na produkčních webech nic neměníme ani neukládáme.', 'Při zkoušení CSS pracujeme ve vlastním souboru.'],
        'teacher_notes' => ['Výklad může být bez programování; důležitý je převod vztahů do modelu rozvržení.', 'Otázka do třídy: Je tohle řada, nebo tabulka?', 'Tempo: kdo umí HTML a CSS, ať si zkusí mini implementaci (výzva).'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–3 na papíře, úkol 4 ukáže vyučující zástupu na projektoru.', 'Plán B offline: rozvržení ze čtverečků papíru, které se přeskládají.'],
        'glossary' => ['flexbox', 'grid', 'gap'],
    ],
    16 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 16 · Test použitelnosti: pozorování místo dojmů',
        'goal' => [
            'student' => 'Na konci hodiny umím připravit a vést krátký test použitelnosti bez navádění, zapsat pozorování odděleně od výkladu a seřadit problémy podle dopadu.',
            'success_criteria' => ['Připravím tři realistické úkoly, které neprozrazují cestu.', 'Zapisuji, co účastník dělal, ne co si o tom myslím.', 'Vyberu nejvýš tři problémy podle četnosti × dopadu a navrhnu změnu.'],
        ],
        'competencies' => [],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Zahraje test s navádějícími otázkami a pak bez nich.', 'student' => 'Řeknou, který test dal víc informací.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Plán testu', 'teacher' => 'Ukáže rozdíl úkolu „najdi přihlášku“ a „klikni na Přihlásit“.', 'student' => 'Napíšou tři úkoly bez prozrazení cesty.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Moderování', 'teacher' => 'Nacvičí věty „Co byste teď udělali?“ a ticho.', 'student' => 'Vyzkouší si roli moderátora ve dvojici.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 60, 'phase' => 'Test a záznam', 'teacher' => 'Hlídá oddělení pozorování od výkladu.', 'student' => 'Otestují prototyp se 1–2 spolužáky a zapisují pozorování.', 'form' => 've skupinách'],
            ['from' => 60, 'to' => 75, 'phase' => 'Priorita', 'teacher' => 'Vysvětlí četnost × dopad.', 'student' => 'Seřadí problémy a vyberou nejvýš tři.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Změna a exit ticket', 'teacher' => 'Zadá formát problém → důkaz → změna.', 'student' => 'Navrhnou 1–3 změny a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš tři realistické úkoly pro test použitelnosti (usability test), které neprozrazují cestu ani názvy tlačítek.', 'output' => 'Plán testu se 3 úkoly.', 'time' => '15 min'],
            ['text' => 'Otestuj prototyp s 1–2 spolužáky a zapisuj pozorování (co dělal) odděleně od výkladu (co si myslím).', 'output' => 'Záznam pozorování ve dvou sloupcích.', 'time' => '20 min'],
            ['text' => 'Ohodnoť problémy podle četnosti a dopadu a vyber nejvýš tři k úpravě.', 'output' => 'Seřazený seznam problémů.', 'time' => '15 min'],
            ['text' => 'Navrhni 1–3 změny ve tvaru problém → důkaz → změna.', 'output' => 'Návrh změn s důkazem.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzorový plán testu a záznamový arch se dvěma sloupci.',
            'standard' => 'Vlastní plán, test, priorita a změny podle zadání.',
            'challenge' => 'Porovná výsledky dvou účastníků a odliší náhodný problém od opakovaného.',
        ],
        'assessment' => [
            'formative' => ['Po plánu: soused hledá úkol, který prozrazuje cestu.', 'Záznam: učitel namátkou přečte řádek – je to pozorování, nebo výklad?'],
            'rubric' => [
                ['criterion' => 'Plán testu', 'levels' => ['Úkoly chybí.', 'Úkoly navádějí.', 'Tři realistické úkoly bez nápovědy.', 'Úkoly pokrývají hlavní cíl webu.']],
                ['criterion' => 'Záznam', 'levels' => ['Jen dojmy.', 'Pozorování smíchané s výkladem.', 'Oddělené pozorování a výklad.', 'Přesné citace a časy.']],
                ['criterion' => 'Priorita a změna', 'levels' => ['Bez priority.', 'Všechno je „důležité“.', 'Nejvýš 3 problémy s důkazem.', 'Změna přímo vychází z důkazu.']],
            ],
        ],
        'exit_ticket' => ['competence' => '', 'competence_label' => 'Výzkum a test', 'variants' => [
            ['question' => 'Který zápis je pozorování (ne výklad)?', 'options' => ['Navigace je špatná.', 'Účastník třikrát klikl na Zpět a pak hledal v zápatí.', 'Uživatel je zmatený, protože web je ošklivý.'], 'correct' => 1, 'explanation' => 'Pozorování popisuje chování.'],
            ['question' => 'Účastník se při testu zasekne. Co uděláš jako moderátor?', 'options' => ['Hned mu ukážu správné tlačítko.', 'Ukončím test.', 'Chvíli počkám a zeptám se: „Co byste teď udělali?“'], 'correct' => 2, 'explanation' => 'Brzká nápověda zakryje problém.'],
            ['question' => 'Kolik účastníků stačí na nácvik metody ve třídě?', 'options' => ['1–3 účastníci, cílem je naučit se metodu.', 'Nejméně 100.', 'Žádný, stačí vlastní názor.'], 'correct' => 0, 'explanation' => 'Ve třídě jde o nácvik, ne o reprezentativní výzkum.'],
        ]],
        'homework' => [['text' => 'Volitelné: požádej někoho doma o jeden úkol v aplikaci a zapiš jen to, co dělal (bez hodnocení).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Test bez nahrávání a bez jmen účastníků v záznamu (účastník A, B).', 'Účast je dobrovolná; kdo nechce být testován, je zapisovatel.'],
        'teacher_notes' => ['Stačí 1–3 testující – jde o nácvik metody, ne o reprezentativní výzkum.', 'Otázka do třídy: Je tohle, co viděl, nebo co si myslí?', 'Tempo: test ve skupinách pohlídej časovačem – 20 minut.'],
        'substitution' => ['Zástup bez odborníka: test na papírovém prototypu podle úkolů 1–4.', 'Plán B offline: papírový prototyp, moderátor „hraje počítač“.'],
        'glossary' => ['usability-test'],
    ],
]]];
