<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 1.A (grafika a webdesign), lekce 23–28.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php.
 * Přepis šablonových lekcí lessons_v30.php: vlastní úkoly, exit tickety a české titulky. Kompetence: katalog v62 „grafika_web“.
 */

return ['class_1a' => ['lessons' => [
    23 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 23 · Přístupné barvy a písmo: kontrast, velikost, řádkování',
        'goal' => [
            'student' => 'Na konci hodiny umím změřit kontrast textu, zvolit čitelnou velikost písma a řádkování a opravit stránku tak, aby ji přečetl i člověk se slabším zrakem.',
            'success_criteria' => ['Běžný text má kontrast aspoň 4,5 : 1, velký text a ikony aspoň 3 : 1.', 'Běžný text má aspoň 16 px a výšku řádku kolem 1,5.', 'Informaci nepředávám jen barvou (chyba má i text nebo ikonu).'],
        ],
        'competencies' => [['id' => 'gfx_color_contrast', 'level' => 3], ['id' => 'web_a11y', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: šedá na šedé', 'teacher' => 'Promítne text #999999 na bílé a nechá třídu číst z poslední lavice.', 'student' => 'Zažijí, jak se špatný kontrast čte.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Měření kontrastu', 'teacher' => 'Ukáže měření poměru kontrastu a hranice 4,5 : 1 a 3 : 1.', 'student' => 'Změří kontrast pěti dvojic barev ze své stránky.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Velikost a řádkování', 'teacher' => 'Porovná text 12 px / řádkování 1,1 a 16 px / 1,5.', 'student' => 'Upraví velikost písma, řádkování a délku řádku.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Nejen barvou', 'teacher' => 'Ukáže formulář, kde chybu značí jen červený rámeček.', 'student' => 'Doplní k chybě text a ikonu.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 80, 'phase' => 'Oprava stránky', 'teacher' => 'Obchází, kontroluje měření před a po.', 'student' => 'Opraví landing page a zapíší hodnoty před a po.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne tři hranice: 4,5 : 1, 3 : 1 a 16 px.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Změř kontrast pěti dvojic barev text/pozadí na své stránce a u každé napiš, jestli splňuje 4,5 : 1.', 'output' => 'Tabulka pěti dvojic s poměrem kontrastu a ano/ne.', 'time' => '15 min'],
            ['text' => 'Nastav běžnému textu velikost aspoň 16 px, výšku řádku 1,5 a omez délku řádku zhruba na 70 znaků.', 'output' => 'Upravená pravidla CSS a snímek před a po.', 'time' => '15 min'],
            ['text' => 'Doplň chybový stav formulářového pole tak, aby chybu nesla i textová hláška a ikona, ne jen barva.', 'output' => 'Pole v chybovém stavu se třemi signály: barva, ikona, text.', 'time' => '15 min'],
            ['text' => 'Oprav všechny dvojice barev pod hranicí a zapiš hodnotu kontrastu před opravou a po ní.', 'output' => 'Seznam oprav s kontrastem před → po.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku s připravenými dvojicemi barev a vzorem měření; opravuje podle nápovědy tmavší odstín.',
            'standard' => 'Měření, úprava písma, chybový stav a opravy na vlastní stránce.',
            'challenge' => 'Ověří stránku i v tmavém režimu a v náhledu „odstíny šedi“, jestli hierarchie drží bez barev.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže dvojici barev, třída tipne, jestli projde 4,5 : 1, pak se měří.', 'Kontrola souseda: ukaž chybu formuláře bez barev – poznám ji?'],
            'rubric' => [
                ['criterion' => 'Kontrast', 'levels' => ['Neměřen.', 'Změřen, opravy chybí.', 'Všechny dvojice nad hranicí.', 'Ověřeno i v tmavém režimu.']],
                ['criterion' => 'Čitelnost písma', 'levels' => ['Pod 14 px.', '16 px, řádkování těsné.', '16 px, výška řádku 1,5, rozumná délka řádku.', 'Zdůvodní volby pro mobil i počítač.']],
                ['criterion' => 'Nejen barvou', 'levels' => ['Chyba jen barvou.', 'Barva + ikona.', 'Barva, ikona i text.', 'Hláška říká i opravu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_color_contrast', 'competence_label' => 'Barvy a kontrast', 'variants' => [
            ['question' => 'Jaký nejmenší kontrast potřebuje běžný text podle WCAG 2.2 AA?', 'options' => ['2 : 1', '4,5 : 1', '10 : 1'], 'correct' => 1, 'explanation' => 'Běžný text 4,5 : 1, velký text a prvky ovládání 3 : 1.'],
            ['question' => 'Chybné pole je označené jen červeným rámečkem. Co je špatně?', 'options' => ['Člověk, který červenou nerozliší, chybu nepozná.', 'Červená je moc výrazná.', 'Rámeček má být silnější.'], 'correct' => 0, 'explanation' => 'Informace nesmí stát jen na barvě.'],
            ['question' => 'Které nastavení běžného textu je nejčitelnější?', 'options' => ['12 px, výška řádku 1,1', '14 px, výška řádku 1,0', '16 px, výška řádku 1,5'], 'correct' => 2, 'explanation' => 'Dostatečná velikost a vzduch mezi řádky.'],
        ]],
        'homework' => [['text' => 'Volitelné: změř kontrast textu na jedné školní nástěnce nebo plakátu a navrhni lepší dvojici barev.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['K měření kontrastu použij nástroj povolený školou; nevkládej do něj nic než kódy barev.', 'Stránky a texty jsou vymyšlené, bez osobních údajů.'],
        'teacher_notes' => ['Častý omyl: „vypadá to dobře na mém monitoru“ – rozhoduje změřená hodnota, ne dojem.', 'Otázka do třídy: Jak pozná chybu člověk, který nerozliší červenou a zelenou?', 'Tempo: opravy stránky jsou hlavní práce – měření v úvodu omez na pět dvojic.'],
        'substitution' => ['Zástup bez odborníka: rozdá tabulku dvojic barev a kontrolní seznam; žáci plní úkoly 1–4.', 'Plán B offline: porovnání čitelnosti vytištěných vzorků písma a barev z různé vzdálenosti.'],
        'glossary' => ['hex', 'validation'],
    ],
    24 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 24 · Obrázek, který vypráví: výřez, ohnisko a export',
        'goal' => [
            'student' => 'Na konci hodiny umím vybrat výřez obrázku podle sdělení, zachovat ohnisko v různých poměrech stran a vyexportovat obrázek ve vhodném formátu a velikosti pro web.',
            'success_criteria' => ['Ve výřezu je ohnisko na průsečíku třetin nebo záměrně uprostřed.', 'Mám tři poměry stran (16 : 9, 4 : 5, 1 : 1) se stejným ohniskem.', 'Fotku exportuji jako JPEG nebo WebP, grafiku s průhledností jako PNG, a soubor má rozumnou velikost.'],
        ],
        'competencies' => [['id' => 'gfx_formats', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: jedna fotka, tři příběhy', 'teacher' => 'Ukáže tři výřezy jedné fotky, každý s jiným sdělením.', 'student' => 'Pojmenují sdělení každého výřezu.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Ohnisko a třetiny', 'teacher' => 'Vysvětlí pravidlo třetin a ohnisko (focal point).', 'student' => 'Vyznačí ohnisko na dvou vlastních fotkách.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Tři poměry stran', 'teacher' => 'Předvede výřez 16 : 9, 4 : 5 a 1 : 1.', 'student' => 'Vytvoří tři výřezy se stejným ohniskem.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Formát a velikost', 'teacher' => 'Porovná JPEG, PNG, WebP a SVG a ukáže velikosti souborů.', 'student' => 'Vyexportují výřezy a zapíší formát a velikost souboru.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 80, 'phase' => 'Popis obrázku', 'teacher' => 'Ukáže alternativní text, který nese sdělení.', 'student' => 'Napíší alternativní text k hlavnímu výřezu.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: výřez, formát, popis.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Na dvou vlastních fotkách (bez lidí) vyznač ohnisko a mřížku třetin a napiš, co má divák vidět jako první.', 'output' => 'Dvě fotky s vyznačeným ohniskem a jednou větou sdělení.', 'time' => '15 min'],
            ['text' => 'Z jedné fotky vytvoř tři výřezy v poměrech 16 : 9, 4 : 5 a 1 : 1 se stejným ohniskem.', 'output' => 'Tři výřezy pojmenované podle poměru stran.', 'time' => '20 min'],
            ['text' => 'Vyexportuj výřez 16 : 9 v šířce 1600 px jako JPEG a jako WebP a zapiš velikosti obou souborů.', 'output' => 'Dva soubory a tabulka formát → velikost v kB.', 'time' => '15 min'],
            ['text' => 'Napiš alternativní text k hlavnímu výřezu, který řekne, co je na obrázku podstatné pro sdělení stránky.', 'output' => 'Alternativní text do 125 znaků.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane fotku s předkreslenou mřížkou třetin a hotový rámeček pro poměr 1 : 1.',
            'standard' => 'Ohnisko, tři výřezy, export ve dvou formátech a alternativní text.',
            'challenge' => 'Připraví i export 800 px pro mobil a spočítá, kolik dat ušetří oproti 1600 px.',
        ],
        'assessment' => [
            'formative' => ['Třída ukáže prstem ohnisko na promítnuté fotce.', 'Rychlá otázka: logo s průhledným pozadím – jaký formát?'],
            'rubric' => [
                ['criterion' => 'Výřez a ohnisko', 'levels' => ['Ohnisko useknuté.', 'Ohnisko v jednom poměru.', 'Ohnisko ve třech poměrech.', 'Každý výřez nese jiné záměrné sdělení.']],
                ['criterion' => 'Formát a velikost', 'levels' => ['Nevhodný formát.', 'Vhodný formát, obří soubor.', 'Vhodný formát a rozumná velikost.', 'Porovná formáty a zdůvodní volbu daty.']],
                ['criterion' => 'Alternativní text', 'levels' => ['Chybí.', 'Jen „obrázek“ nebo název souboru.', 'Popisuje podstatné pro sdělení.', 'Stručný a liší se podle kontextu stránky.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_formats', 'competence_label' => 'Grafické formáty a export', 'variants' => [
            ['question' => 'Jaký formát zvolíš pro logo s průhledným pozadím, které se nemá rozmazat?', 'options' => ['JPEG s nejvyšší kompresí', 'SVG nebo PNG', 'BMP'], 'correct' => 1, 'explanation' => 'JPEG neumí průhlednost; vektor nebo PNG ji zachová.'],
            ['question' => 'Co je při změně poměru stran výřezu nejdůležitější?', 'options' => ['Aby obrázek měl co nejvíc pixelů.', 'Aby byl výřez vždy čtvercový.', 'Aby ohnisko zůstalo vidět.'], 'correct' => 2, 'explanation' => 'Výřez slouží sdělení – ohnisko nesmí zmizet.'],
            ['question' => 'Který alternativní text je nejlepší pro fotku na stránce kurzu kreslení?', 'options' => ['Ruce kreslí uhlem portrét na velký arch papíru.', 'obrazek1.jpg', 'Fotka.'], 'correct' => 0, 'explanation' => 'Popisuje, co je pro sdělení podstatné.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť jeden předmět a připrav z fotky výřez 1 : 1 a 16 : 9 se stejným ohniskem.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Fotíme bez lidí, nebo jen se souhlasem; cizí fotky jen s licencí a uvedeným autorem.', 'Před exportem odstraň z fotky údaje o poloze (EXIF).'],
        'teacher_notes' => ['Častý omyl: obrázek pro web v plném rozlišení z fotoaparátu (několik MB) – vždy porovnej velikost souboru.', 'Otázka do třídy: Co má divák vidět jako první a zůstane to i ve čtverci?', 'Tempo: export a porovnání formátů je praktické jádro – úvod o třetinách zkrať.'],
        'substitution' => ['Zástup bez odborníka: rozdá vytištěné fotky a rámečky poměrů; žáci plní úkoly 1, 2 a 4, úkol 3 doma nebo příště.', 'Plán B offline: výřezy z papírových rámečků přiložených na vytištěné fotky.'],
        'glossary' => ['crop', 'focal-point'],
    ],
    25 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 25 · Startovní sada komponent: tlačítko, karta a pole formuláře',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout tři opakovatelné komponenty – tlačítko, kartu a pole formuláře – včetně stavů výchozí, najetí, fokus, chyba a nedostupné.',
            'success_criteria' => ['Každá komponenta má popsané stavy a viditelný fokus pro ovládání klávesnicí.', 'Tlačítko má cílovou plochu (touch target) aspoň 44 × 44 px.', 'Pole formuláře má label, nápovědu a chybovou hlášku pod polem.'],
        ],
        'competencies' => [['id' => 'web_a11y', 'level' => 2], ['id' => 'web_ux', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: kde jsem?', 'teacher' => 'Projde stránku Tabem, kde fokus není vidět.', 'student' => 'Zažijí ztrátu orientace bez viditelného fokusu.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 30, 'phase' => 'Stavy tlačítka', 'teacher' => 'Ukáže stavy výchozí, hover, focus, stisk a nedostupné.', 'student' => 'Navrhnou tlačítko ve všech stavech.', 'form' => 'jednotlivě'],
            ['from' => 30, 'to' => 50, 'phase' => 'Pole formuláře', 'teacher' => 'Předvede label, nápovědu, chybu a placeholder jen jako příklad.', 'student' => 'Navrhnou pole ve stavech výchozí, fokus a chyba.', 'form' => 'jednotlivě'],
            ['from' => 50, 'to' => 65, 'phase' => 'Karta', 'teacher' => 'Ukáže kartu s jedním odkazem přes celou plochu.', 'student' => 'Navrhnou kartu z tokenů z L17.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 80, 'phase' => 'Kontrola klávesnicí', 'teacher' => 'Zadá test: projdi komponenty jen Tabem a Enterem.', 'student' => 'Otestují a opraví stav, který chybí.', 'form' => 've dvojicích'],
            ['from' => 80, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: stav = informace pro uživatele.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Navrhni tlačítko v pěti stavech (výchozí, hover, focus, stisknuté, nedostupné) s cílovou plochou aspoň 44 × 44 px.', 'output' => 'Řádek pěti stavů tlačítka s popisem změny.', 'time' => '18 min'],
            ['text' => 'Navrhni pole formuláře s labelem, nápovědou a chybovou hláškou ve stavech výchozí, fokus a chyba.', 'output' => 'Tři stavy pole pod sebou s texty.', 'time' => '18 min'],
            ['text' => 'Navrhni kartu z tokenů své sady (L17), na které je celá plocha jedním odkazem.', 'output' => 'Karta s popisky použitých tokenů.', 'time' => '15 min'],
            ['text' => 'Projdi své komponenty jen klávesnicí a zapiš, kde fokus nebyl vidět nebo chyběl stav, a oprav to.', 'output' => 'Zápis testu klávesnicí a opravená komponenta.', 'time' => '14 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane předlohu tlačítka ve výchozím stavu a doplňuje ostatní stavy podle tabulky změn.',
            'standard' => 'Tlačítko, pole a karta se stavy a test klávesnicí.',
            'challenge' => 'Navrhne i stav „odesílám“ s omezeným pohybem pro uživatele s nastavením reduced motion.',
        ],
        'assessment' => [
            'formative' => ['Učitel promítne tlačítko a třída vyjmenuje chybějící stavy.', 'Test klávesnicí ve dvojici: vidím vždy, kde jsem?'],
            'rubric' => [
                ['criterion' => 'Stavy komponent', 'levels' => ['Jen výchozí stav.', 'Dva až tři stavy.', 'Všechny stavy popsané.', 'Stavy drží i v tmavém režimu.']],
                ['criterion' => 'Fokus a cílová plocha', 'levels' => ['Fokus neviditelný.', 'Fokus slabý, plocha malá.', 'Viditelný fokus a plocha 44 × 44 px.', 'Ověřeno testem klávesnicí.']],
                ['criterion' => 'Pole formuláře', 'levels' => ['Bez labelu.', 'Label, bez chyby.', 'Label, nápověda i chybová hláška.', 'Hláška říká opravu a je u pole.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_a11y', 'competence_label' => 'Přístupnost webu', 'variants' => [
            ['question' => 'Proč musí být stav focus viditelný?', 'options' => ['Aby tlačítko bylo barevnější.', 'Aby člověk ovládající web klávesnicí věděl, kde právě je.', 'Kvůli rychlejšímu načítání.'], 'correct' => 1, 'explanation' => 'Viditelný fokus je orientace pro klávesnici.'],
            ['question' => 'Jakou cílovou plochu doporučujeme pro tlačítko na dotykovém displeji?', 'options' => ['Aspoň 44 × 44 px', '10 × 10 px', 'Přesně velikost textu'], 'correct' => 0, 'explanation' => 'Dostatečná plocha pro prst; WCAG 2.2 AA vyžaduje aspoň 24 × 24 px, doporučujeme 44.'],
            ['question' => 'Kam patří chybová hláška pole formuláře?', 'options' => ['Jen do vyskakovacího okna nahoře.', 'Do placeholderu.', 'K poli, kterého se týká, s návodem na opravu.'], 'correct' => 2, 'explanation' => 'Hláška u pole je vidět i čtečce a říká opravu.'],
        ]],
        'homework' => [['text' => 'Volitelné: projdi jeden web jen klávesnicí a zapiš, kde ses ztratil(a).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Komponenty testujeme na vlastních návrzích; do formulářů zadáváme jen vymyšlené údaje.', 'Ikony jen z bezplatných sad s licencí; zdroj zapiš.'],
        'teacher_notes' => ['Častý omyl: outline: none bez náhrady – fokus pak zmizí. Vždy nabídni jiný viditelný styl.', 'Otázka do třídy: Kdo všechno ovládá web bez myši?', 'Tempo: když karta nevyjde, stačí tlačítko a pole – test klávesnicí nevynechávej.'],
        'substitution' => ['Zástup bez odborníka: rozdá tabulku stavů; žáci kreslí komponenty na papír a test klávesnicí udělají na hotovém webu.', 'Plán B offline: komponenty jako papírové kartičky v pěti stavech.'],
        'glossary' => ['focus', 'touch-target'],
    ],
    26 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 26 · Workshop kritiky: Líbí se mi – Ptám se – Navrhuji',
        'goal' => [
            'student' => 'Na konci hodiny umím vést a přijímat kritiku návrhu ve skupině podle struktury Líbí se mi – Ptám se – Navrhuji a z poznámek sestavit plán úprav.',
            'success_criteria' => ['Ke každému návrhu dám jednu poznámku do každé ze tří částí.', 'Jako autor nejdřív poslouchám a ptám se, až potom vysvětluji.', 'Z poznámek sestavím plán tří úprav seřazených podle dopadu.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 3], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Pravidla workshopu', 'teacher' => 'Představí roli autora, moderátora a zapisovatele a časomíru.', 'student' => 'Rozdělí se do skupin po čtyřech a rozdělí role.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 20, 'phase' => 'Ukázkové kolo', 'teacher' => 'Předvede jedno kolo na anonymním návrhu.', 'student' => 'Sledují strukturu a čas.', 'form' => 'frontálně'],
            ['from' => 20, 'to' => 60, 'phase' => 'Kola kritiky', 'teacher' => 'Hlídá čas (10 min na návrh) a tón.', 'student' => 'Každý představí návrh, ostatní dají Líbí – Ptám se – Navrhuji.', 'form' => 've skupinách'],
            ['from' => 60, 'to' => 75, 'phase' => 'Plán úprav', 'teacher' => 'Ukáže, jak poznámky seřadit podle dopadu.', 'student' => 'Sestaví plán tří úprav.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 82, 'phase' => 'Reflexe skupiny', 'teacher' => 'Zeptá se, která poznámka pomohla nejvíc a proč.', 'student' => 'Sdílí jednu užitečnou poznámku.', 'form' => 'frontálně'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne role a strukturu.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Připrav svůj návrh na kritiku: jednu větu cíle návrhu a jednu otázku, na kterou chceš od skupiny odpověď.', 'output' => 'Návrh s větou cíle a otázkou pro skupinu.', 'time' => '8 min'],
            ['text' => 'Ke každému návrhu ve skupině napiš jednu poznámku Líbí se mi, jednu Ptám se a jednu Navrhuji.', 'output' => 'Lístky se třemi poznámkami ke každému návrhu.', 'time' => '30 min'],
            ['text' => 'Jako zapisovatel zapiš poznámky ke kolu spolužáka přehledně do tří sloupců.', 'output' => 'Tabulka Líbí – Ptám se – Navrhuji k jednomu návrhu.', 'time' => '10 min'],
            ['text' => 'Z poznámek ke svému návrhu sestav plán tří úprav seřazených podle dopadu na uživatele.', 'output' => 'Plán úprav: úprava → proč → jak ověřím.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičky se začátky vět pro všechny tři části a roli zapisovatele.',
            'standard' => 'Poznámky ke všem návrhům ve skupině a vlastní plán úprav.',
            'challenge' => 'Moderuje kolo: hlídá čas, strukturu a ptá se tišších členů skupiny.',
        ],
        'assessment' => [
            'formative' => ['Moderátor po kole řekne, jestli zazněly všechny tři části.', 'Učitel vybere náhodnou poznámku a třída určí, do které části patří.'],
            'rubric' => [
                ['criterion' => 'Struktura poznámek', 'levels' => ['Jen hodnocení.', 'Jedna nebo dvě části.', 'Všechny tři části ke každému návrhu.', 'Návrhy jsou konkrétní a ověřitelné.']],
                ['criterion' => 'Role autora', 'levels' => ['Obhajuje se a skáče do řeči.', 'Poslouchá, neptá se.', 'Poslouchá a doptá se.', 'Shrne slyšené vlastními slovy.']],
                ['criterion' => 'Plán úprav', 'levels' => ['Chybí.', 'Seznam bez pořadí.', 'Tři úpravy podle dopadu.', 'U každé úpravy způsob ověření.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_composition', 'competence_label' => 'Kompozice a hierarchie', 'variants' => [
            ['question' => 'Co dělá autor návrhu během kola kritiky?', 'options' => ['Hned vysvětluje, proč to udělal takhle.', 'Odchází, aby ostatní mohli mluvit.', 'Poslouchá, zapisuje a doptá se, až když ostatní domluví.'], 'correct' => 2, 'explanation' => 'Nejdřív poslouchat, pak se ptát.'],
            ['question' => 'Která poznámka patří do části „Ptám se“?', 'options' => ['Co má uživatel udělat jako první po otevření stránky?', 'Líbí se mi barva tlačítka.', 'Zmenši nadpis.'], 'correct' => 0, 'explanation' => 'Otázka otevírá zamyšlení autora.'],
            ['question' => 'Podle čeho seřadíš úpravy z workshopu?', 'options' => ['Podle toho, kdo poznámku řekl.', 'Podle dopadu na uživatele.', 'Abecedně.'], 'correct' => 1, 'explanation' => 'Nejdřív to, co nejvíc pomůže uživateli.'],
        ]],
        'homework' => [['text' => 'Volitelné: proveď jednu úpravu z plánu a zapiš, jestli pomohla.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Kritizujeme návrh, ne autora; kdo nechce svůj návrh ukázat, použije anonymní ukázku.', 'Poznámky zůstávají ve skupině, nefotí se a nesdílí.'],
        'teacher_notes' => ['Častý omyl: autor se obhajuje místo poslechu – moderátor ho jemně zastaví.', 'Otázka do třídy: Která poznámka vám dnes ušetřila nejvíc práce?', 'Tempo: 10 minut na návrh drž přesně; skupiny po čtyřech stihnou čtyři kola.'],
        'substitution' => ['Zástup bez odborníka: vytiskne pravidla workshopu a časomíru; skupiny pracují samostatně podle úkolů 1–4.', 'Plán B offline: kritika vytištěných návrhů nebo plakátů ve škole.'],
        'glossary' => ['thumbnail', 'whitespace'],
    ],
    27 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 27 · Sprint landing page: od zadání k funkční stránce',
        'goal' => [
            'student' => 'Na konci hodiny umím v časovém limitu převést krátké zadání na funkční landing page s mřížkou, výzvou k akci a ověřením na mobilu.',
            'success_criteria' => ['Do 15 minut mám hlavní sdělení a drátěný model.', 'Rozvržení stavím mřížkou nebo flexboxem s mezerou gap, bez pevných šířek v px.', 'Na konci mám kontrolní seznam s ověřením na šířce 390 px.'],
        ],
        'competencies' => [['id' => 'web_css_layout', 'level' => 3], ['id' => 'web_html_structure', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 5, 'phase' => 'Zadání sprintu', 'teacher' => 'Rozdá zadání (vymyšlená akce) a časový plán sprintu.', 'student' => 'Přečtou zadání a podtrhnou cílovou skupinu.', 'form' => 'frontálně'],
            ['from' => 5, 'to' => 20, 'phase' => 'Sdělení a model', 'teacher' => 'Hlídá limit 15 minut.', 'student' => 'Napíší sdělení a nakreslí drátěný model.', 'form' => 'jednotlivě'],
            ['from' => 20, 'to' => 55, 'phase' => 'Stavba stránky', 'teacher' => 'Obchází a připomíná mřížku a gap.', 'student' => 'Postaví kostru a rozvržení mřížkou nebo flexboxem.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 70, 'phase' => 'Výzva k akci a formulář', 'teacher' => 'Kontroluje texty tlačítek a labely.', 'student' => 'Doplní tlačítko a formulář.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 82, 'phase' => 'Ověření', 'teacher' => 'Vede kontrolu na 390 px, klávesnicí a kontrastu.', 'student' => 'Projdou kontrolní seznam a opraví jednu chybu.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Odevzdání a exit ticket', 'teacher' => 'Ukončí sprint.', 'student' => 'Odevzdají stránku a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Do 15 minut napiš hlavní sdělení a nakresli drátěný model mobilní verze stránky ze zadání sprintu.', 'output' => 'Sdělení do 12 slov a drátěný model.', 'time' => '15 min'],
            ['text' => 'Postav rozvržení sekcí pomocí CSS grid nebo flexbox s mezerou gap a bez pevných šířek v pixelech.', 'output' => 'Stránka, která se na úzké obrazovce skládá do sloupce.', 'time' => '35 min'],
            ['text' => 'Doplň jednu výzvu k akci a formulář přihlášky s labely a srozumitelným textem tlačítka.', 'output' => 'Tlačítko se slovesem a formulář s labely.', 'time' => '15 min'],
            ['text' => 'Ověř stránku na šířce 390 px, klávesnicí a měřením kontrastu a zapiš výsledek do kontrolního seznamu.', 'output' => 'Kontrolní seznam: tři kontroly s výsledkem a jednou opravou.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kostru HTML a třídu mřížky; doplňuje obsah a jedno pravidlo rozvržení.',
            'standard' => 'Celý sprint podle časového plánu.',
            'challenge' => 'Přidá druhé rozvržení pro široké obrazovky a srovná grid a flexbox ve dvou větách.',
        ],
        'assessment' => [
            'formative' => ['Kontrola po 20 minutách: ukažte drátěný model zvednutím papíru.', 'Kontrolní seznam ve dvojici: soused potvrdí tři kontroly.'],
            'rubric' => [
                ['criterion' => 'Plán v limitu', 'levels' => ['Bez plánu.', 'Plán po limitu.', 'Sdělení a model do 15 min.', 'Model se ve stavbě dodržel.']],
                ['criterion' => 'Rozvržení', 'levels' => ['Pevné šířky, přetéká.', 'Funguje jen na počítači.', 'Mřížka nebo flexbox s gap na mobilu i počítači.', 'Dvě rozvržení a zdůvodněná volba techniky.']],
                ['criterion' => 'Ověření', 'levels' => ['Neověřeno.', 'Jedna kontrola.', 'Tři kontroly s výsledkem.', 'Oprava nalezené chyby ověřená znovu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_css_layout', 'competence_label' => 'Rozvržení v CSS a mobil', 'variants' => [
            ['question' => 'Proč se v responzivním rozvržení vyhýbáme pevné šířce width: 1200px?', 'options' => ['Protože pixely nejsou povolené.', 'Protože na úzké obrazovce obsah přeteče a vznikne vodorovné posouvání.', 'Protože pevná šířka zpomalí načítání.'], 'correct' => 1, 'explanation' => 'Pružné rozvržení se přizpůsobí šířce displeje.'],
            ['question' => 'K čemu slouží vlastnost gap?', 'options' => ['Určí mezeru mezi položkami mřížky nebo flexboxu.', 'Skryje prvek na mobilu.', 'Nastaví barvu okraje.'], 'correct' => 0, 'explanation' => 'gap = mezera mezi položkami bez okrajů navíc.'],
            ['question' => 'Co uděláš jako první v sprintu landing page?', 'options' => ['Vyberu barvy a písmo.', 'Napíšu CSS animace.', 'Hlavní sdělení a drátěný model podle zadání.'], 'correct' => 2, 'explanation' => 'Obsah a plán určují, co se bude stavět.'],
        ]],
        'homework' => [['text' => 'Volitelné: dokonči jednu nedodělanou část sprintu a zapiš, kolik času ti zabrala.', 'minutes' => 25, 'optional' => true]],
        'safety' => ['Zadání sprintu je vymyšlené; žádné skutečné kontakty ani fotky lidí bez souhlasu.', 'Kód nekopírujeme z cizích webů; vlastní řešení nebo ukázky z hodin.'],
        'teacher_notes' => ['Častý omyl: ve sprintu se začne barvami a písmem – po 15 minutách zkontroluj, že každý má model.', 'Otázka do třídy: Co se stane s tvou stránkou na šířce 320 px?', 'Tempo: když stavba trvá, zkrať formulář na jedno pole, ale ověření nech celé.'],
        'substitution' => ['Zástup bez odborníka: rozdá zadání sprintu a časový plán; žáci pracují samostatně a odevzdají soubory.', 'Plán B offline: sprint na papíře – sdělení, model a kontrolní seznam podle vytištěného webu.'],
        'glossary' => ['grid', 'gap'],
    ],
    28 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 28 · Portfolio a ověření dovedností prvního ročníku',
        'goal' => [
            'student' => 'Na konci hodiny umím vybrat dvě své práce do portfolia, ke každé napsat krátkou případovou studii (case study: problém → rozhodnutí → výsledek) a podle kompetencí určit, co zlepším příště.',
            'success_criteria' => ['Ke každé práci mám problém, dvě rozhodnutí a výsledek s důkazem.', 'U každé kompetence ročníku umím ukázat konkrétní práci jako důkaz.', 'Napíšu jeden konkrétní cíl na další ročník.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 3], ['id' => 'web_ux', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: co jsem se naučil(a)', 'teacher' => 'Promítne seznam kompetencí ročníku.', 'student' => 'Ke každé kompetenci si vzpomenou na jednu práci.', 'form' => 'jednotlivě'],
            ['from' => 10, 'to' => 25, 'phase' => 'Výběr prací', 'teacher' => 'Vysvětlí, proč vybrat práci s příběhem, ne jen nejhezčí.', 'student' => 'Vyberou dvě práce do portfolia.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 50, 'phase' => 'Případová studie', 'teacher' => 'Ukáže vzor: problém → rozhodnutí → výsledek.', 'student' => 'Napíší dvě krátké případové studie.', 'form' => 'jednotlivě'],
            ['from' => 50, 'to' => 70, 'phase' => 'Mapa kompetencí', 'teacher' => 'Vysvětlí sebehodnocení podle důkazů.', 'student' => 'Přiřadí práce ke kompetencím a označí mezery.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 82, 'phase' => 'Sdílení ve dvojici', 'teacher' => 'Zadá: přečti sousedovi jednu studii za 1 minutu.', 'student' => 'Sdílí a dostanou jednu otázku.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Cíl a exit ticket', 'teacher' => 'Shrne rok.', 'student' => 'Zapíší cíl na další ročník a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Vyber dvě své práce z ročníku, které ukazují, jak ses rozhodoval(a), a ke každé napiš jednu větu proč.', 'output' => 'Dvě vybrané práce s větou zdůvodnění.', 'time' => '12 min'],
            ['text' => 'Napiš ke každé vybrané práci krátkou případovou studii: problém, dvě rozhodnutí a výsledek s důkazem.', 'output' => 'Dvě studie po 5–7 větách.', 'time' => '25 min'],
            ['text' => 'Přiřaď své práce k osmi kompetencím ročníku a označ kompetence, ke kterým zatím důkaz nemáš.', 'output' => 'Tabulka kompetence → důkaz (práce) → mezera.', 'time' => '18 min'],
            ['text' => 'Napiš jeden konkrétní a ověřitelný cíl na další ročník podle největší mezery.', 'output' => 'Cíl ve tvaru: co, jak poznám, že to umím, do kdy.', 'time' => '10 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu případové studie s otázkami a seznam svých prací z portfolia.',
            'standard' => 'Dvě studie, mapa kompetencí a cíl.',
            'challenge' => 'Připojí ke studii srovnání první a poslední verze práce s popisem, co se zlepšilo.',
        ],
        'assessment' => [
            'formative' => ['Učitel přečte anonymní studii, třída najde problém, rozhodnutí a výsledek.', 'Dvojice: soused položí jednu otázku k rozhodnutí.'],
            'rubric' => [
                ['criterion' => 'Případová studie', 'levels' => ['Jen popis práce.', 'Problém a výsledek bez rozhodnutí.', 'Problém, dvě rozhodnutí a výsledek.', 'Výsledek doložený srovnáním nebo testem.']],
                ['criterion' => 'Mapa kompetencí', 'levels' => ['Chybí.', 'Částečná, bez mezer.', 'Všech 8 kompetencí s důkazem nebo mezerou.', 'Mezery zdůvodněné.']],
                ['criterion' => 'Cíl', 'levels' => ['Chybí.', 'Obecný („zlepšit se“).', 'Konkrétní a ověřitelný.', 'S plánem prvního kroku.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_composition', 'competence_label' => 'Kompozice a hierarchie', 'variants' => [
            ['question' => 'Co patří do krátké případové studie práce?', 'options' => ['Seznam použitých programů.', 'Jen finální obrázek.', 'Problém, rozhodnutí a výsledek s důkazem.'], 'correct' => 2, 'explanation' => 'Studie ukazuje, jak přemýšlíš.'],
            ['question' => 'Kterou práci vybereš do portfolia?', 'options' => ['Tu, u které umím vysvětlit svá rozhodnutí a zlepšení.', 'Tu s nejvíce barvami.', 'Tu, kterou jsem dělal(a) nejkratší dobu.'], 'correct' => 0, 'explanation' => 'Portfolio ukazuje proces i výsledek.'],
            ['question' => 'Který cíl na další ročník je ověřitelný?', 'options' => ['Chci být lepší v grafice.', 'Do listopadu postavím stránku, která projde kontrolou kontrastu i klávesnicí.', 'Budu se víc snažit.'], 'correct' => 1, 'explanation' => 'Konkrétní výsledek a termín se dají ověřit.'],
        ]],
        'homework' => [['text' => 'Volitelné: připni jednu vybranou práci s reflexí do svého portfolia v EDUCANETu.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Do portfolia dávej jen vlastní práce a obrázky s licencí; žádné fotky ani jména spolužáků bez souhlasu.', 'Portfolio vidíš ty a tvoji učitelé; sdílení ven jen se souhlasem a bez osobních údajů.'],
        'teacher_notes' => ['Častý omyl: do portfolia jen „nejhezčí“ práce – veď žáky k pracím s viditelným rozhodováním.', 'Otázka do třídy: Které rozhodnutí z letoška bys dnes udělal(a) jinak a proč?', 'Tempo: mapa kompetencí může pokračovat doma; případové studie dokonči v hodině.'],
        'substitution' => ['Zástup bez odborníka: rozdá šablonu případové studie a seznam kompetencí; žáci plní úkoly 1–4.', 'Plán B offline: studie na papíře k vytištěným pracím.'],
        'glossary' => ['case-study'],
    ],
]]];
