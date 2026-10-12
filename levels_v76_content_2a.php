<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · úrovně aktivit 2.A (grafika a webdesign II), lekce 17–28 – NÁVRH (žákům až po schválení učitelem v záložce urovne75).
 * Navazuje na levels_v75_content_2a.php (L1–L16); hlavní aktivita odpovídá obsahu vlny 2 (lesson_content_v76_2a_*.php). Výukový obsah česky.
 */

return [
    17 => ['activity' => 'Kontrola implementace proti návrhu',
        'zaklad' => ['task' => 'Podle předvyplněného seznamu najdi na ukázkové stránce tři odchylky.', 'check' => 'Každá odchylka má místo na stránce.'],
        'standard' => ['task' => 'Najdi odchylky podle vlastního seznamu a zapiš čtyři hlášení se závažností.', 'check' => 'Hlášení jde opravit bez doptávání.'],
        'vyzva' => ['task' => 'Rozděl kontroly na automatizovatelné a ruční a zdůvodni rozdělení.', 'check' => 'U každé kontroly je nástroj nebo postup.']],
    18 => ['activity' => 'Produktová stránka a případová studie',
        'zaklad' => ['task' => 'Dokonči jednu sekci stránky z tokenů a vyplň osnovu studie podle otázek.', 'check' => 'Sekce funguje na 320 px.'],
        'standard' => ['task' => 'Dokonči stránku se stavy formuláře a napiš studii s jedním důkazem.', 'check' => 'Studie obsahuje tři rozhodnutí.'],
        'vyzva' => ['task' => 'Doplň do studie srovnání před a po z testu použitelnosti.', 'check' => 'Srovnání má čísla (úspěšnost, čas).']],
    19 => ['activity' => 'Scénář rozhovoru',
        'zaklad' => ['task' => 'Ze seznamu otázek vyber pět otevřených a jednu návodnou přepiš.', 'check' => 'Žádná otázka nenaznačuje odpověď.'],
        'standard' => ['task' => 'Napiš výzkumnou otázku, scénář rozhovoru a proveď zkušební rozhovor.', 'check' => 'Zápis odděluje pozorování od domněnky.'],
        'vyzva' => ['task' => 'Navrhni, jak ověřit výzkumnou otázku pozorováním chování.', 'check' => 'Návrh říká, co se bude měřit.']],
    20 => ['activity' => 'Třídění karet a mapa stránek',
        'zaklad' => ['task' => 'Roztřiď připravenou sadu 15 karet a pojmenuj skupiny.', 'check' => 'Každá karta je v nějaké skupině.'],
        'standard' => ['task' => 'Připrav karty, vyhodnoť třídění tří skupin a nakresli mapu stránek.', 'check' => 'Hlavní navigace má nejvýš 6 položek.'],
        'vyzva' => ['task' => 'Navrhni uzavřené třídění, které ověří sporné karty.', 'check' => 'Sporné karty mají plán ověření.']],
    21 => ['activity' => 'Zátěžový test komponenty',
        'zaklad' => ['task' => 'Otestuj připravenou kartu dlouhým textem a 320 px a oprav jednu situaci.', 'check' => 'Karta se při dlouhém textu nerozbije.'],
        'standard' => ['task' => 'Otestuj komponentu v šesti situacích a oprav problémy pružnými hodnotami.', 'check' => 'Opakovaný test projde ve všech situacích.'],
        'vyzva' => ['task' => 'Přidej test s dlouhou URL a zapiš pravidlo do design systému.', 'check' => 'Pravidlo je formulované obecně.']],
    22 => ['activity' => 'Stavy formuláře',
        'zaklad' => ['task' => 'Do předkresleného diagramu doplň texty šesti stavů formuláře.', 'check' => 'Každý stav má text pro uživatele.'],
        'standard' => ['task' => 'Navrhni chyby u polí, souhrn chyb a stav selhání odeslání.', 'check' => 'Při selhání se data neztratí.'],
        'vyzva' => ['task' => 'Navrhni průběžné ukládání konceptu a jeho oznámení uživateli.', 'check' => 'Uživatel ví, kdy byl koncept uložen.']],
    23 => ['activity' => 'Test a oprava interakcí',
        'zaklad' => ['task' => 'Oprav tlačítko z prvku div na element button a otestuj ho klávesnicí.', 'check' => 'Tlačítko reaguje na Enter i mezerník.'],
        'standard' => ['task' => 'Otestuj menu, dialog a záložky klávesnicí a oprav tlačítko i dialog.', 'check' => 'Fokus se po zavření dialogu vrátí.'],
        'vyzva' => ['task' => 'Ověř animaci dialogu s nastavením omezeného pohybu a doplň pravidlo do design systému.', 'check' => 'Při omezeném pohybu se animace vypne.']],
    24 => ['activity' => 'Tokeny jako proměnné CSS',
        'zaklad' => ['task' => 'K hotovým základním hodnotám doplň čtyři sémantické role a použij je v tlačítku.', 'check' => 'Tlačítko nemá přímý HEX kód.'],
        'standard' => ['task' => 'Zapiš dvě vrstvy tokenů, přepiš dvě komponenty na role a přidej tmavý režim.', 'check' => 'Tmavý režim mění jen role.'],
        'vyzva' => ['task' => 'Přidej přepínač režimu atributem data-theme a krátkou dokumentaci pro předání.', 'check' => 'Dokumentace popisuje role i přepínač.']],
    25 => ['activity' => 'Druhé kolo testu použitelnosti',
        'zaklad' => ['task' => 'Jako zapisovatel zapiš výsledky dvou úkolů do připravené tabulky.', 'check' => 'U každého úkolu je úspěch a čas.'],
        'standard' => ['task' => 'Napiš úkoly jako situace, moderuj dva testy a seřaď nálezy podle závažnosti.', 'check' => 'Plán oprav má tři položky se závažností.'],
        'vyzva' => ['task' => 'Porovnej výsledky s prvním kolem a vyhodnoť, jestli opravy pomohly.', 'check' => 'Srovnání má úspěšnost obou kol.']],
    26 => ['activity' => 'Klinika design QA',
        'zaklad' => ['task' => 'Jako tester zapiš dvě úplná hlášení podle šablony.', 'check' => 'Hlášení mají kroky k reprodukci.'],
        'standard' => ['task' => 'Zapiš hlášení, slouč duplicity, oprav tři chyby a ověř je.', 'check' => 'Každé uzavřené hlášení má důkaz.'],
        'vyzva' => ['task' => 'Navrhni pravidlo design systému, které by nejčastější chybě předešlo.', 'check' => 'Pravidlo míří na příčinu, ne na příznak.']],
    27 => ['activity' => 'Produktový sprint v týmu',
        'zaklad' => ['task' => 'V roli kontroly přístupnosti projdi prototyp podle seznamu.', 'check' => 'Nálezy mají návrh opravy.'],
        'standard' => ['task' => 'Spolu s týmem vytvoř mapu, tok, prototyp se stavy a předávací list.', 'check' => 'Předávací list obsahuje tokeny, stavy a otevřené otázky.'],
        'vyzva' => ['task' => 'Jako architekt zdůvodni mapu stránek daty z třídění karet.', 'check' => 'Zdůvodnění odkazuje na konkrétní výsledky.']],
    28 => ['activity' => 'Případová studie a obhajoba',
        'zaklad' => ['task' => 'Podle osnovy napiš studii jednoho projektu s jedním důkazem.', 'check' => 'Studie má problém, rozhodnutí a výsledek.'],
        'standard' => ['task' => 'Napiš studii se třemi rozhodnutími, vyplň mapu kompetencí a obhaj ji do 3 minut.', 'check' => 'Odpověď na otázku je opřená o důkaz.'],
        'vyzva' => ['task' => 'Přidej odstavec, co bys udělal(a) jinak a jak bys to ověřil(a).', 'check' => 'Odstavec obsahuje konkrétní test.']],
];
