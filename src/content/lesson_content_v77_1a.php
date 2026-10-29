<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · obsahová stopa, doplnění L1–L4 – 1.A (grafika a webdesign).
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla jako vlna 1 a 2 (lesson_content_v76_1a_a.php):
 * cíl + 2–4 kritéria, plán 90 min, úkoly, diferenciace, formativní kontrola a rubrika 3 × 1–4, exit ticket (3 varianty,
 * jiné než kvíz lekce), bezpečnost, poznámky, zástup, dobrovolná domácí příprava a glosář.
 * Vychází z témat původních lekcí (first_year.php L1, next_lessons.php L2, extended_lessons.php L3–L4); texty jsou vlastní a česky.
 * Kompetence: katalog v62 „grafika_web“. Úrovně: levels_v75_content_1a.php (L1–L4). Vazba na ŠVP: svp_v72_map.php (návrh).
 * AI prompty: ai_prompts_v76.php z tohoto obsahu (po schválení). Kontrasty barev v úkolech jsou spočítané podle WCAG 2.2.
 */

return ['class_1a' => ['lessons' => [
    1 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 1 · První plakát: hierarchie, kontrast a test tří vteřin',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout plakát, ze kterého divák do tří vteřin pochopí, co se děje, kdy a co má udělat.',
            'success_criteria' => ['Nadpis je první věc, kterou divák přečte, a je aspoň 3× větší než nejmenší text.', 'Používám nejvýš dvě písma a tři barvy a text je čitelný proti pozadí.', 'Spolužák po třech vteřinách řekne, o jakou akci jde.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 2], ['id' => 'gfx_color_contrast', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: tři plakáty', 'teacher' => 'Ukáže na tři vteřiny tři plakáty a zeptá se, co si třída zapamatovala.', 'student' => 'Zapíší, co viděli první, a hledají proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Brief', 'teacher' => 'Vysvětlí, co je zadání: akce, nadpis, datum, místo a výzva k akci.', 'student' => 'Vyberou si akci a sepíší brief.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 37, 'phase' => 'Skica hierarchie', 'teacher' => 'Nakreslí tři obdélníky podle pořadí čtení.', 'student' => 'Skicují rozvržení: co divák uvidí první, druhé a třetí.', 'form' => 'jednotlivě'],
            ['from' => 37, 'to' => 67, 'phase' => 'Sestavení plakátu', 'teacher' => 'Obchází a ptá se: „Co je tu nejdůležitější?“', 'student' => 'Poskládají plakát A3 podle skici s nejvýš dvěma písmy a třemi barvami.', 'form' => 'jednotlivě'],
            ['from' => 67, 'to' => 82, 'phase' => 'Test tří vteřin a export', 'teacher' => 'Zadá párový test: ukaž plakát na tři vteřiny jako malý náhled.', 'student' => 'Otestují plakát u souseda, upraví ho a exportují.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: velikost a kontrast řídí pořadí čtení.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vyber akci (ples, turnaj, kroužek) a sepiš brief: nadpis do šesti slov, datum, místo a jednu výzvu k akci (CTA) se slovesem.', 'output' => 'Karta briefu se čtyřmi údaji a výzvou typu „Přihlas se do pátku“.', 'time' => '12 min'],
            ['text' => 'Na papír nakresli tři obdélníky podle pořadí čtení (1 nadpis, 2 informace nebo obraz, 3 výzva) tak, aby hlavní prvek byl aspoň 3× větší než nejmenší text.', 'output' => 'Skica rozvržení s očíslovanými prvky a poměrem velikostí.', 'time' => '13 min'],
            ['text' => 'Založ dokument A3 na výšku s okraji 2 cm a poskládej plakát podle skici: nejvýš dvě písma, tři barvy a volný prostor kolem nadpisu.', 'output' => 'Plakát A3 s viditelnou hierarchií nadpis → informace → výzva.', 'time' => '30 min'],
            ['text' => 'Ukaž plakát spolužákovi na tři vteřiny jako malý náhled (thumbnail), zapiš, co si zapamatoval, jednu věc uprav a exportuj PDF pro tisk a PNG pro web.', 'output' => 'Zápis testu (co divák řekl), jedna úprava a dva exportované soubory.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane skicu se třemi obdélníky a dvojici písem předem vybranou; doplňuje texty a barvy.',
            'standard' => 'Brief, skica hierarchie, plakát A3, test tří vteřin a export.',
            'challenge' => 'Navrhne druhou verzi se stejným textem a jinou kompozicí a testem rozhodne, která funguje lépe.',
        ],
        'assessment' => [
            'formative' => ['Ukaž prstem, co na plakátu souseda vidíš první.', 'Rychlá otázka: kolik písem a barev plakát používá?'],
            'rubric' => [
                ['criterion' => 'Hierarchie', 'levels' => ['Všechno stejně velké.', 'Nadpis je větší, ale nevede oko.', 'Jasné pořadí nadpis → informace → výzva.', 'Pořadí obstojí v testu tří vteřin bez vysvětlování.']],
                ['criterion' => 'Kontrast a čitelnost', 'levels' => ['Text splývá s pozadím.', 'Nadpis čitelný, drobný text ne.', 'Všechny texty čitelné z dálky.', 'Kontrast vědomě použitý ke zdůraznění výzvy.']],
                ['criterion' => 'Střídmost', 'levels' => ['Víc než tři písma a mnoho barev.', 'Tři písma nebo čtyři barvy.', 'Dvě písma, tři barvy.', 'Každý prvek má funkci, nic navíc.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_composition', 'competence_label' => 'Grafika: kompozice s jasnou hierarchií', 'variants' => [
            ['question' => 'Co na plakátu nejvíc určuje, co divák přečte jako první?', 'options' => ['Počet barev.', 'Velikost a kontrast prvku vůči okolí.', 'Typ souboru při exportu.'], 'correct' => 1, 'explanation' => 'Oko jde nejdřív k největšímu a nejkontrastnějšímu prvku.'],
            ['question' => 'Proč dělat test tří vteřin na malém náhledu?', 'options' => ['Ukáže, jestli hlavní sdělení funguje i při letmém pohledu.', 'Zmenší velikost souboru.', 'Zkontroluje pravopis.'], 'correct' => 0, 'explanation' => 'Plakát se čte v chůzi a na mobilu – musí fungovat okamžitě.'],
            ['question' => 'Kolik písem je na prvním plakátu rozumné použít?', 'options' => ['Pět, aby byl zajímavý.', 'Každý text jiné.', 'Nejvýš dvě – jedno pro nadpis, druhé pro text.'], 'correct' => 2, 'explanation' => 'Méně písem = klidnější a čitelnější plakát.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť jeden plakát ve městě (bez lidí na fotce) a zapiš, co jsi přečetl/a první a proč.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Fotky a ilustrace jen vlastní nebo s jasnou licencí; u cizích zapíšeme zdroj.', 'Na plakát nedáváme skutečné telefonní číslo ani adresu – jen vymyšlené údaje akce.'],
        'teacher_notes' => ['Častý omyl: „víc barev = zajímavější“ – ukaž stejný plakát se třemi a s osmi barvami.', 'Otázka do třídy: Co by divák měl udělat po přečtení plakátu?', 'Tempo: sestavení plakátu je jádro; test tří vteřin nezkracuj, i kdyby export zůstal na doma.'],
        'substitution' => ['Zástup bez odborníka: brief a skica na papír (úkoly 1 a 2), sestavení v libovolném editoru podle pravidel v zadání.', 'Plán B offline: plakát z vystřižených papírových obdélníků a fixů, test tří vteřin ve dvojicích.'],
        'glossary' => ['cta', 'thumbnail', 'whitespace'],
    ],
    2 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 2 · Písmo a fotografie: plakát bez šablony',
        'goal' => [
            'student' => 'Na konci hodiny umím postavit plakát bez šablony: rozdělit texty do typografických rolí, připravit ořez fotografie a dodržet jednu škálu mezer.',
            'success_criteria' => ['Texty mají tři role (nadpis, informace, výzva) a nejvýš dvě rodiny písem.', 'Ořez fotografie drží ohnisko pozornosti a nechává místo pro text.', 'Mezery jsou jen z jedné škály a text je čitelný i přes fotografii.'],
        ],
        'competencies' => [['id' => 'gfx_typography', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: šablona vs. vlastní', 'teacher' => 'Ukáže plakát ze šablony a zeptá se, co na něm nesedí ke sdělení.', 'student' => 'Najdou tři prvky, které tam jsou „jen proto, že byly v šabloně“.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Typografické role', 'teacher' => 'Vysvětlí role nadpis / informace / výzva a dvojici písem.', 'student' => 'Rozepíší texty do rolí a zvolí písmo, řez a velikost.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Ořez fotografie', 'teacher' => 'Předvede ořez na výšku a ohnisko pozornosti.', 'student' => 'Připraví ořez a vyznačí zónu pro text.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 67, 'phase' => 'Plakát bez šablony', 'teacher' => 'Hlídá škálu mezer a kontrast textu na fotografii.', 'student' => 'Postaví plakát s rolemi, ořezem a jednou škálou mezer.', 'form' => 'jednotlivě'],
            ['from' => 67, 'to' => 82, 'phase' => 'Kontrola před odevzdáním', 'teacher' => 'Ukáže převod do odstínů šedi jako test kontrastu.', 'student' => 'Provedou kontrolu, opraví chyby a porovnají s prvním plakátem.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: pravidla místo šablony.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Rozepiš texty plakátu do tří rolí – nadpis, informace, výzva – a ke každé urči písmo, řez a velikost; použij nejvýš dvě rodiny písem.', 'output' => 'Tabulka role → písmo → řez → velikost (např. nadpis 96 pt tučně, informace 28 pt, výzva 36 pt).', 'time' => '13 min'],
            ['text' => 'Vyber vlastní nebo jasně licencovanou fotografii, připrav ořez (crop) na výšku a vyznač ohnisko pozornosti (focal point) a zónu pro text.', 'output' => 'Ořezaná fotografie s vyznačeným ohniskem a obdélníkem zóny pro text, u cizí fotky zdroj a licence.', 'time' => '15 min'],
            ['text' => 'Postav plakát bez šablony: všechny mezery ber jen ze škály 8 / 16 / 32 px (spacing) a text na fotografii podlož tak, aby byl čitelný.', 'output' => 'Plakát s rolemi písma, ořezem a mezerami jen ze škály.', 'time' => '27 min'],
            ['text' => 'Proveď kontrolu před odevzdáním (preflight): převeď plakát do odstínů šedi, udělej test tří vteřin a zapiš tři konkrétní rozdíly proti prvnímu plakátu.', 'output' => 'Verze v odstínech šedi, výsledek testu a tři rozdíly s pojmy hierarchie, mezery, ořez.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane předvybranou dvojici písem a fotografii s naznačeným ořezem; skládá podle vzoru mezer.',
            'standard' => 'Typografické role, ořez s ohniskem, plakát bez šablony a kontrola před odevzdáním.',
            'challenge' => 'Připraví druhý ořez stejné fotky na šířku a vysvětlí, co se musí v rozvržení změnit.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: kolik rodin písem je na plakátu souseda?', 'Kontrola souseda: najdi jednu mezeru, která není ze škály.'],
            'rubric' => [
                ['criterion' => 'Typografie', 'levels' => ['Bez rolí.', 'Role nejasné, víc písem.', 'Tři role, dvě rodiny písem.', 'Role drží i v malém náhledu.']],
                ['criterion' => 'Ořez', 'levels' => ['Ořez uřízl hlavní motiv.', 'Motiv celý, bez místa pro text.', 'Ohnisko drží a je místo pro text.', 'Ořez vede oko k nadpisu.']],
                ['criterion' => 'Mezery a kontrast', 'levels' => ['Náhodné mezery, text nečitelný.', 'Mezery náhodné, text čitelný.', 'Mezery ze škály, text čitelný přes fotku.', 'Mezery seskupují související informace.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_typography', 'competence_label' => 'Grafika: volba a skloubení písem', 'variants' => [
            ['question' => 'K čemu je typografická role „výzva“?', 'options' => ['Ke zdobení volného místa.', 'K uvedení data akce.', 'Říká divákovi, co má udělat, a musí být vidět hned po nadpisu.'], 'correct' => 2, 'explanation' => 'Výzva k akci je cílem plakátu.'],
            ['question' => 'Co je dobrý ořez fotografie pro plakát?', 'options' => ['Celá fotka bez úprav.', 'Ořez, který drží ohnisko pozornosti a nechá místo pro text.', 'Co nejmenší výřez obličeje.'], 'correct' => 1, 'explanation' => 'Ořez slouží sdělení – motivu i textu.'],
            ['question' => 'Proč brát mezery z jedné škály (např. 8, 16, 32 px)?', 'options' => ['Plakát působí uspořádaně a související věci drží pohromadě.', 'Soubor bude menší.', 'Tiskárna jiné mezery neumí.'], 'correct' => 0, 'explanation' => 'Opakované mezery vytváří rytmus a skupiny.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi dvě dvojice písem, které spolu fungují, a jednu, která ne; zapiš proč (bez stahování neověřených písem).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Fotografie jen vlastní nebo s jasnou licencí; lidi fotíme jen s jejich souhlasem.', 'Písma používáme jen z nabídky editoru nebo s volnou licencí.'],
        'teacher_notes' => ['Častý omyl: tučné všechno – připomeň, že zdůraznění funguje jen proti klidnému okolí.', 'Otázka do třídy: Co by se stalo, kdyby výzva byla větší než nadpis?', 'Tempo: ořez fotky žáky zdržuje – nabídni dvě připravené fotografie.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 4 na papír, úkoly 2 a 3 v libovolném editoru podle pravidel v zadání.', 'Plán B offline: výstřižky z časopisů na ořez a papírový plakát s tabulkou rolí písma.'],
        'glossary' => ['crop', 'focal-point', 'spacing', 'preflight'],
    ],
    3 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 3 · Barvy a písmo jako systém: varianty A a B',
        'goal' => [
            'student' => 'Na konci hodiny umím z plakátu udělat malý systém barev a písma, ověřit kontrast výpočtem a testem náhledu vybrat lepší ze dvou variant.',
            'success_criteria' => ['Kontrast běžného textu ověřím nástrojem a vím, že potřebuje aspoň 4,5 : 1.', 'Paleta má nejvýš tři barvy zapsané v HEX s rolí (text, pozadí, akcent).', 'Výběr varianty zdůvodním výsledkem testu náhledu.'],
        ],
        'competencies' => [['id' => 'gfx_color_contrast', 'level' => 2], ['id' => 'gfx_typography', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Kontrastní laboratoř', 'teacher' => 'Ukáže šedý text na bílé a zeptá se, kdo ho přečte ze zadní lavice.', 'student' => 'Odhadnou, která dvojice barev projde, a ověří ji nástrojem.', 'form' => 'frontálně'],
            ['from' => 12, 'to' => 28, 'phase' => 'Paleta s rolemi', 'teacher' => 'Vysvětlí role barev a zápis v HEX.', 'student' => 'Sestaví paletu tří barev s rolemi a ověří kontrast.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 45, 'phase' => 'Varianty A a B', 'teacher' => 'Zadá pravidlo: varianta B mění kompozici, ne text.', 'student' => 'Vytvoří dvě varianty stejného obsahu.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 58, 'phase' => 'Test náhledu', 'teacher' => 'Zmenší varianty na velikost palce a ukáže je třídě.', 'student' => 'Vyberou lepší variantu a zdůvodní volbu.', 'form' => 've dvojicích'],
            ['from' => 58, 'to' => 82, 'phase' => 'Realizace a export', 'teacher' => 'Hlídá dodržení palety a rolí písma.', 'student' => 'Dokončí vítěznou variantu, exportují ji a zkontrolují mimo editor.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: systém = opakovatelná pravidla.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Ověř kontrastním nástrojem dvě dvojice zadané v HEX – #1F2937 na #FFFFFF a #9CA3AF na #FFFFFF – a rozhodni, která projde pro běžný text.', 'output' => 'První dvojice asi 14,7 : 1 (projde), druhá asi 2,5 : 1 (neprojde, nejvýš na dekoraci).', 'time' => '12 min'],
            ['text' => 'Sestav paletu nejvýš tří barev v HEX s rolemi text, pozadí a akcent a ke každé dvojici text/pozadí zapiš ověřený kontrast.', 'output' => 'Tabulka tří barev s rolí, HEX kódem a kontrastem aspoň 4,5 : 1 pro text.', 'time' => '15 min'],
            ['text' => 'Vytvoř dvě varianty stejného obsahu: A podle dosavadní skici, B s jinou kompozicí (texty se nemění), a obě zmenši na náhled (thumbnail).', 'output' => 'Dvě varianty vedle sebe ve velikosti náhledu.', 'time' => '20 min'],
            ['text' => 'Vyber variantu, ve které jde nadpis v náhledu přečíst první, dokonči ji podle palety a rolí písma, exportuj a soubor otevři mimo editor.', 'output' => 'Exportovaná vítězná varianta a tři věty o barvě, písmu a mezerách.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tři připravené palety s ověřeným kontrastem a vybírá jednu; varianta B se mu zadá náčrtem.',
            'standard' => 'Ověření kontrastu, paleta s rolemi, varianty A a B, test náhledu a export.',
            'challenge' => 'Najde k barvě #9CA3AF tmavší odstín, který na bílé projde 4,5 : 1, a zapíše postup hledání.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: 3 : 1 – projde to pro běžný text?', 'Kartičky: změnila varianta B kompozici, nebo jen barvy?'],
            'rubric' => [
                ['criterion' => 'Kontrast', 'levels' => ['Neověřuje.', 'Ověří, ale nevyvodí závěr.', 'Ověří a rozhodne podle 4,5 : 1.', 'Opraví nevyhovující barvu a ověří znovu.']],
                ['criterion' => 'Paleta a role', 'levels' => ['Náhodné barvy.', 'Tři barvy bez rolí.', 'Tři barvy s rolí a HEX.', 'Paleta funguje i ve druhém formátu.']],
                ['criterion' => 'Varianty a výběr', 'levels' => ['Jedna varianta.', 'B mění jen barvy.', 'B mění kompozici, výběr podle testu.', 'Výběr zdůvodní pojmy hierarchie a kontrast.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_color_contrast', 'competence_label' => 'Grafika: barvy a ověřený kontrast', 'variants' => [
            ['question' => 'Jaký nejmenší kontrast potřebuje běžný text podle WCAG (úroveň AA)?', 'options' => ['4,5 : 1', '2 : 1', '1,5 : 1'], 'correct' => 0, 'explanation' => 'Běžný text 4,5 : 1, velký text 3 : 1.'],
            ['question' => 'Co smí změnit varianta B při porovnání A a B?', 'options' => ['Texty i barvy.', 'Jen velikost písma.', 'Kompozici – texty zůstávají stejné.'], 'correct' => 2, 'explanation' => 'Měníme jednu věc, abychom věděli, co rozhodlo.'],
            ['question' => 'Co znamená zápis barvy #FFFFFF?', 'options' => ['Černou.', 'Bílou – všechny složky naplno.', 'Průhlednou.'], 'correct' => 1, 'explanation' => 'FF v každé složce RGB = maximum = bílá.'],
        ]],
        'homework' => [['text' => 'Volitelné: ověř kontrast textu na jednom webu, který používáš, a zapiš, jestli projde 4,5 : 1.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Kontrastní nástroje používáme jen ty, které doporučí škola; nepřihlašujeme se do cizích služeb.', 'Obrázky ve variantách jen vlastní nebo s licencí.'],
        'teacher_notes' => ['Častý omyl: „světle šedá je elegantní“ – ukaž ji na projektoru ze zadní lavice.', 'Otázka do třídy: Proč u varianty B neměníme text?', 'Tempo: výpočet kontrastu nech na nástroji; důležitý je závěr, ne vzorec.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 2 s vytištěnou tabulkou kontrastů, úkoly 3 a 4 v editoru podle zadání.', 'Plán B offline: barevné papíry a fixy, test čitelnosti ze tří metrů místo nástroje.'],
        'glossary' => ['hex', 'thumbnail'],
    ],
    4 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 4 · Mini identita akce: plakát a příspěvek jedním stylem',
        'goal' => [
            'student' => 'Na konci hodiny umím sepsat mini vizuální identitu akce a podle ní připravit plakát i čtvercový příspěvek, které na první pohled patří k sobě.',
            'success_criteria' => ['Mini kit má tři barvy v HEX, dvě role písma, styl výzvy a pravidlo pro obraz.', 'Hlavní formát 1080 × 1350 px stojí na mřížce.', 'Čtverec 1080 × 1080 px mění rozvržení, ne identitu, a exporty mají správné rozměry.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 2], ['id' => 'gfx_formats', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Brief akce', 'teacher' => 'Ukáže dvě sady materiálů – jednu jednotnou, jednu „každý pes jiná ves“.', 'student' => 'Zapíší cílovou skupinu, hlavní sdělení a požadovanou akci.', 'form' => 'frontálně'],
            ['from' => 12, 'to' => 28, 'phase' => 'Mini kit', 'teacher' => 'Vysvětlí, co mini identita obsahuje.', 'student' => 'Sestaví mini kit na jednu stranu.', 'form' => 'jednotlivě'],
            ['from' => 28, 'to' => 48, 'phase' => 'Hlavní formát', 'teacher' => 'Předvede mřížku se čtyřmi sloupci pro formát 1080 × 1350 px.', 'student' => 'Postaví hlavní formát podle mřížky.', 'form' => 'jednotlivě'],
            ['from' => 48, 'to' => 68, 'phase' => 'Čtverec', 'teacher' => 'Zadá pravidlo: změň rozvržení, ne identitu.', 'student' => 'Převedou návrh do formátu 1080 × 1080 px.', 'form' => 'jednotlivě'],
            ['from' => 68, 'to' => 82, 'phase' => 'Kontrola konzistence', 'teacher' => 'Ukáže oba formáty vedle sebe a ptá se, co je drží pohromadě.', 'student' => 'Zkontrolují rozměry a texty mimo editor a zapíší větu o identitě.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: identita = pravidla, která se opakují.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Sepiš brief vymyšlené školní akce: cílová skupina, hlavní sdělení jednou větou a jedna akce, kterou má divák udělat.', 'output' => 'Brief se třemi body.', 'time' => '10 min'],
            ['text' => 'Sestav mini kit identity na jednu stranu: tři barvy v HEX s rolí, dvě role písma, styl tlačítka výzvy a pravidlo pro obraz.', 'output' => 'Jedna strana mini kitu se čtyřmi oddíly.', 'time' => '16 min'],
            ['text' => 'Postav hlavní formát 1080 × 1350 px na mřížce (grid) se čtyřmi sloupci a okraji 64 px; každý prvek zarovnej ke sloupcům.', 'output' => 'Hlavní formát se zobrazenou mřížkou a zarovnanými prvky.', 'time' => '20 min'],
            ['text' => 'Převeď návrh do čtverce 1080 × 1080 px se stejnou identitou a proveď kontrolu před odevzdáním (preflight): rozměry, texty a čitelnost mimo editor.', 'output' => 'Dva exporty se správnými rozměry a věta „systém drží pohromadě …“.', 'time' => '24 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzor mini kitu s vyplněnými rolemi písma a mřížku jako podklad.',
            'standard' => 'Brief, mini kit, hlavní formát na mřížce, čtverec a kontrola.',
            'challenge' => 'Přidá třetí formát 1080 × 1920 px a popíše, které pravidlo identity musel/a upřesnit.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: co se mezi formáty mění a co nesmí?', 'Kontrola souseda: zakryj logo – poznáš i tak, že oba formáty patří k sobě?'],
            'rubric' => [
                ['criterion' => 'Mini kit', 'levels' => ['Chybí.', 'Barvy bez rolí.', 'Barvy, písmo, výzva i obraz.', 'Pravidla jsou tak přesná, že by podle nich pracoval i spolužák.']],
                ['criterion' => 'Mřížka a rozvržení', 'levels' => ['Bez mřížky.', 'Mřížka bez zarovnání.', 'Prvky zarovnané ke sloupcům.', 'Mřížka pomáhá i ve čtverci.']],
                ['criterion' => 'Konzistence a export', 'levels' => ['Formáty nepatří k sobě.', 'Shodné barvy, jiné písmo.', 'Shodná identita a správné rozměry.', 'Jedna věta vystihne, co systém drží.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_formats', 'competence_label' => 'Grafika: formáty a export', 'variants' => [
            ['question' => 'Co musí zůstat stejné, když převádíš plakát do čtverce?', 'options' => ['Přesné rozmístění všech prvků.', 'Barvy, písmo a styl výzvy – identita.', 'Velikost souboru.'], 'correct' => 1, 'explanation' => 'Rozvržení se přizpůsobí formátu, identita zůstává.'],
            ['question' => 'Jaký rozměr má čtvercový příspěvek ze zadání?', 'options' => ['1080 × 1350 px', '1920 × 1080 px', '1080 × 1080 px'], 'correct' => 2, 'explanation' => 'Čtverec má stejnou šířku i výšku.'],
            ['question' => 'K čemu je mřížka (grid) v návrhu?', 'options' => ['Pomáhá zarovnat prvky a udržet stejný řád ve všech formátech.', 'Je to dekorace na pozadí.', 'Zmenšuje velikost exportu.'], 'correct' => 0, 'explanation' => 'Mřížka dává návrhu řád a opakovatelnost.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi značku, kterou poznáš i bez loga, a zapiš tři pravidla její identity (barva, písmo, obraz).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Akce i texty jsou vymyšlené; nepoužíváme loga cizích značek ve vlastním návrhu.', 'Obrázky jen vlastní nebo s licencí, u cizích zdroj.'],
        'teacher_notes' => ['Častý omyl: ve čtverci jen zmenšit plakát – ukaž, že se musí přeskládat.', 'Otázka do třídy: Podle čeho poznáte, že dva materiály patří ke stejné akci?', 'Tempo: mini kit drž krátký – je to nástroj, ne výsledek.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 2 na papír, úkoly 3 a 4 v editoru podle rozměrů v zadání.', 'Plán B offline: plakát a čtverec z papíru na čtverečkovaném podkladu s vyznačenou mřížkou.'],
        'glossary' => ['grid', 'preflight'],
    ],
]]];
