<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 2.A (grafika a webdesign II), lekce 17–22.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php.
 * Kompetence: návrh katalogu v75 „grafika_web_ii“ (competencies_v75.php) – do pilotu v62 se zapojí až schválením katalogu.
 */

return ['class_2a' => ['lessons' => [
    17 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 17 · Kontrola návrhu v kódu: pravidla místo pixelů',
        'goal' => [
            'student' => 'Na konci hodiny umím porovnat hotovou stránku s předaným návrhem podle pravidel (tokeny, stavy, mezery, přístupnost) a zapsat nalezené odchylky jako srozumitelná hlášení.',
            'success_criteria' => ['Odchylku popíšu jako pravidlo (např. návrhový token, design token), které se porušilo, ne jako „o 2 px jinak“.', 'Každé hlášení má kde, co se očekávalo, co je skutečnost a závažnost.', 'Zkontroluji i stavy komponent a ovládání klávesnicí.'],
        ],
        'competencies' => [['id' => 'g2_handoff', 'level' => 2], ['id' => 'w2_a11y_audit', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: pixel, nebo pravidlo?', 'teacher' => 'Ukáže návrh a implementaci s rozdílem 2 px a s chybějícím stavem fokusu.', 'student' => 'Rozhodnou, která odchylka je skutečný problém.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Kontrolní seznam QA', 'teacher' => 'Představí seznam: tokeny, typografie, mezery, stavy, responzivita, přístupnost.', 'student' => 'Doplní k bodům, jak je ověří.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 55, 'phase' => 'Kontrola implementace', 'teacher' => 'Rozdá ukázkovou stránku s 8 záměrnými chybami.', 'student' => 'Hledají odchylky podle seznamu.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 72, 'phase' => 'Hlášení odchylek', 'teacher' => 'Ukáže tvar hlášení a stupnici závažnosti.', 'student' => 'Zapíší 4 hlášení a určí závažnost.', 'form' => 'jednotlivě'],
            ['from' => 72, 'to' => 82, 'phase' => 'Porovnání nálezů', 'teacher' => 'Odhalí seznam všech 8 chyb.', 'student' => 'Porovnají svá zjištění a doplní, co přehlédli.', 'form' => 'frontálně'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: kontrolujeme pravidla a dopad.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Doplň ke každému bodu kontrolního seznamu QA (tokeny, typografie, mezery, stavy, responzivita, přístupnost) jednu konkrétní kontrolu.', 'output' => 'Seznam šesti bodů s tím, jak je ověříš.', 'time' => '15 min'],
            ['text' => 'Najdi na ukázkové stránce co nejvíc odchylek od předaného návrhu podle kontrolního seznamu.', 'output' => 'Seznam nalezených odchylek s místem na stránce.', 'time' => '28 min'],
            ['text' => 'Zapiš čtyři odchylky jako hlášení: kde, co se očekávalo, skutečnost, závažnost (blokuje / vadí / kosmetické).', 'output' => 'Čtyři hlášení ve stejném tvaru.', 'time' => '17 min'],
            ['text' => 'Porovnej své nálezy se seznamem všech chyb a zapiš, kterou kategorii kontroly jsi přehlédl(a).', 'output' => 'Krátká reflexe: co přidám do svého postupu kontroly.', 'time' => '10 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam s předvyplněnými kontrolami a šablonu hlášení.',
            'standard' => 'Vlastní kontroly, hledání odchylek a čtyři hlášení.',
            'challenge' => 'Navrhne, kterou kontrolu lze automatizovat (např. kontrast) a kterou musí udělat člověk.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: rozdíl 2 px v okraji karty – blokuje, vadí, nebo kosmetické?', 'Kontrola hlášení ve dvojici: dá se oprava udělat bez doptávání?'],
            'rubric' => [
                ['criterion' => 'Kontrolní seznam', 'levels' => ['Chybí.', 'Jen vzhled.', 'Všech šest oblastí s kontrolou.', 'Rozliší automatické a ruční kontroly.']],
                ['criterion' => 'Nalezené odchylky', 'levels' => ['Méně než 2.', '3–4.', '5–6 včetně stavu a přístupnosti.', '7–8 a pochopí příčinu.']],
                ['criterion' => 'Hlášení', 'levels' => ['Nejasná.', 'Bez závažnosti.', 'Kde, očekávání, skutečnost, závažnost.', 'Navrhuje pravidlo, které chybě předejde.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_handoff', 'competence_label' => 'Předání návrhu a případová studie', 'variants' => [
            ['question' => 'Která odchylka při kontrole implementace je nejzávažnější?', 'options' => ['Okraj karty je o 2 px menší.', 'Tlačítko nemá viditelný fokus a nedá se ovládat klávesnicí.', 'Stín je o odstín světlejší.'], 'correct' => 1, 'explanation' => 'Blokuje část uživatelů – to má přednost před kosmetikou.'],
            ['question' => 'Co musí obsahovat hlášení odchylky?', 'options' => ['Kde, co se očekávalo, co je skutečnost a závažnost.', 'Jméno toho, kdo chybu udělal.', 'Jen snímek obrazovky.'], 'correct' => 0, 'explanation' => 'Vývojář pak opraví bez doptávání.'],
            ['question' => 'Proč kontrolovat podle pravidel (tokenů) a ne podle pixelů?', 'options' => ['Protože pixely nejdou změřit.', 'Protože pravidla jsou kratší.', 'Protože pravidlo říká záměr a odchylka od něj se opakuje na více místech.'], 'correct' => 2, 'explanation' => 'Oprava pravidla opraví všechna místa najednou.'],
        ]],
        'homework' => [['text' => 'Volitelné: zkontroluj jednu stránku své předchozí práce podle kontrolního seznamu QA a zapiš dvě hlášení.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Ukázková stránka je vymyšlená; neprověřujeme cizí weby bez dovolení.', 'Hlášení se týkají produktu, ne lidí – bez jmen autorů.'],
        'teacher_notes' => ['Častý omyl: hlášení „vypadá to jinak“ – vyžaduj očekávání a skutečnost.', 'Otázka do třídy: Kterou chybu by uživatel poznal jako první?', 'Tempo: seznam 8 chyb odhal až po hlášeních, jinak žáci jen odškrtávají.'],
        'substitution' => ['Zástup bez odborníka: rozdá vytištěnou ukázkovou stránku a šablonu hlášení; žáci plní úkoly 1–4.', 'Plán B offline: kontrola vytištěného návrhu proti vytištěné implementaci.'],
        'glossary' => ['handoff', 'design-token'],
    ],
    18 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 18 · Závěrečný projekt: produktová stránka a případová studie',
        'goal' => [
            'student' => 'Na konci hodiny umím dokončit produktovou landing page podle informační architektury a design systému a sepsat k ní případovou studii s důkazem z testu.',
            'success_criteria' => ['Stránka používá tokeny design systému a funguje od 320 do 1440 px.', 'Formulář má stavy chyby a potvrzení.', 'Případová studie (case study) má problém, tři rozhodnutí a výsledek s důkazem (test, měření).'],
        ],
        'competencies' => [['id' => 'w2_css_responsive', 'level' => 3], ['id' => 'g2_handoff', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Stav projektu', 'teacher' => 'Promítne kontrolní seznam závěrečného projektu.', 'student' => 'Označí, co mají hotové a co chybí.', 'form' => 'jednotlivě'],
            ['from' => 10, 'to' => 40, 'phase' => 'Dokončení stránky', 'teacher' => 'Obchází podle seznamu a ptá se na důkazy.', 'student' => 'Dokončí sekce, tokeny a responzivitu.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Stavy formuláře', 'teacher' => 'Připomene stavy chyby a potvrzení.', 'student' => 'Doplní stavy formuláře.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 75, 'phase' => 'Případová studie', 'teacher' => 'Ukáže osnovu studie a příklad důkazu.', 'student' => 'Napíší studii s důkazem z testu nebo měření.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 83, 'phase' => 'Rychlá kontrola', 'teacher' => 'Zadá vzájemnou kontrolu podle seznamu.', 'student' => 'Zkontrolují projekt souseda.', 'form' => 've dvojicích'],
            ['from' => 83, 'to' => 90, 'phase' => 'Odevzdání a exit ticket', 'teacher' => 'Ukončí práci.', 'student' => 'Odevzdají projekt a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Dokonči sekce produktové stránky tak, aby používaly jen tokeny design systému a fungovaly od 320 do 1440 px.', 'output' => 'Stránka bez vodorovného posouvání na 320 px a bez hodnot mimo tokeny.', 'time' => '30 min'],
            ['text' => 'Doplň do formuláře stav chyby u pole a stav potvrzení po odeslání s dalším krokem.', 'output' => 'Formulář se třemi stavy: výchozí, chyba, potvrzení.', 'time' => '15 min'],
            ['text' => 'Napiš případovou studii projektu: problém, tři klíčová rozhodnutí a výsledek doložený testem nebo měřením.', 'output' => 'Studie na jednu stranu s jedním důkazem.', 'time' => '20 min'],
            ['text' => 'Zkontroluj projekt spolužáka podle kontrolního seznamu a zapiš dvě nejdůležitější odchylky.', 'output' => 'Dvě hlášení se závažností.', 'time' => '8 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane osnovu studie s otázkami a zkrácený kontrolní seznam.',
            'standard' => 'Dokončená stránka, stavy formuláře, studie a kontrola souseda.',
            'challenge' => 'Doplní do studie srovnání před/po (např. úspěšnost úkolu v testu použitelnosti).',
        ],
        'assessment' => [
            'formative' => ['Kontrola na 320 px: učitel náhodně zúží okno u tří projektů.', 'Dvojice: najdi v sousedově studii důkaz.'],
            'rubric' => [
                ['criterion' => 'Stránka a tokeny', 'levels' => ['Nedokončená.', 'Hodnoty mimo tokeny.', 'Tokeny a 320–1440 px bez chyb.', 'Zdůvodní výjimky z tokenů.']],
                ['criterion' => 'Stavy formuláře', 'levels' => ['Bez stavů.', 'Jen chyba.', 'Chyba i potvrzení s dalším krokem.', 'Chyba říká opravu a je u pole.']],
                ['criterion' => 'Případová studie', 'levels' => ['Chybí.', 'Popis bez rozhodnutí.', 'Problém, tři rozhodnutí, výsledek s důkazem.', 'Srovnání před/po.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_handoff', 'competence_label' => 'Předání návrhu a případová studie', 'variants' => [
            ['question' => 'Co je v případové studii nejlepší důkaz výsledku?', 'options' => ['Že se mi stránka líbí.', 'Výsledek testu použitelnosti: 4 z 5 uživatelů dokončili přihlášku.', 'Počet hodin práce.'], 'correct' => 1, 'explanation' => 'Důkaz je ověřitelný výsledek, ne dojem.'],
            ['question' => 'Na jakém rozsahu šířek má produktová stránka fungovat bez vodorovného posouvání?', 'options' => ['Jen 1440 px.', 'Jen 390 px.', 'Od 320 px až po široké obrazovky.'], 'correct' => 2, 'explanation' => 'Responzivita = celý rozsah šířek.'],
            ['question' => 'Co má obsahovat stav potvrzení formuláře?', 'options' => ['Co se stalo a jaký je další krok.', 'Jen zelenou fajfku.', 'Kód odpovědi serveru.'], 'correct' => 0, 'explanation' => 'Uživatel potřebuje jistotu a další krok.'],
        ]],
        'homework' => [['text' => 'Volitelné: požádej jednoho člověka z rodiny o test přihlášky na tvé stránce a zapiš výsledek do studie.', 'minutes' => 25, 'optional' => true]],
        'safety' => ['Produkt a texty jsou vymyšlené; v testech nezaznamenáváme jména ani tváře testujících.', 'Obrázky a písma jen s licencí; zdroje uveď ve studii.'],
        'teacher_notes' => ['Častý omyl: studie popisuje, co žák dělal, ne proč – ptej se „jaké rozhodnutí a podle čeho?“.', 'Otázka do třídy: Jaký důkaz přesvědčí někoho, kdo váš projekt neviděl?', 'Tempo: když stránka není hotová, studii piš o rozpracované verzi – rozhodnutí jsou cennější než dokonalost.'],
        'substitution' => ['Zástup bez odborníka: rozdá kontrolní seznam a osnovu studie; žáci pracují samostatně a odevzdají soubory.', 'Plán B offline: případová studie na papíře k vytištěným snímkům projektu.'],
        'glossary' => ['case-study', 'landing-page'],
    ],
    19 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 19 · Výzkum s uživateli: dobrá otázka a bez navádění',
        'goal' => [
            'student' => 'Na konci hodiny umím sestavit výzkumnou otázku, napsat scénář krátkého rozhovoru bez návodných otázek a z odpovědí oddělit pozorování od domněnky.',
            'success_criteria' => ['Výzkumná otázka říká, co nevíme a jaké rozhodnutí odpověď ovlivní.', 'Scénář rozhovoru obsahuje aspoň 5 otevřených otázek bez navádění.', 'V zápisu odliším, co respondent řekl nebo udělal, od toho, co si myslím já.'],
        ],
        'competencies' => [['id' => 'w2_ux_research', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: návodná otázka', 'teacher' => 'Položí otázku „Že se vám líbí naše nová stránka?“ a zeptá se, co je špatně.', 'student' => 'Najdou navádění v otázce.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Výzkumná otázka', 'teacher' => 'Ukáže vzorec: co nevíme → jaké rozhodnutí na tom závisí.', 'student' => 'Napíší výzkumnou otázku k projektu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Scénář rozhovoru', 'teacher' => 'Ukáže otevřené otázky („Popište, kdy naposledy…“).', 'student' => 'Napíší 5–7 otázek a přepíší návodné.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 68, 'phase' => 'Zkušební rozhovor', 'teacher' => 'Hlídá roli tazatele, respondenta a zapisovatele.', 'student' => 'Provedou krátký rozhovor ve trojici.', 'form' => 've skupinách'],
            ['from' => 68, 'to' => 82, 'phase' => 'Pozorování vs. domněnka', 'teacher' => 'Ukáže dva sloupce zápisu.', 'student' => 'Roztřídí zápis do sloupců.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne zásady rozhovoru.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš výzkumnou otázku pro svůj projekt ve tvaru „Nevíme…, proto se rozhodujeme o…“.', 'output' => 'Jedna výzkumná otázka s rozhodnutím, které ovlivní.', 'time' => '12 min'],
            ['text' => 'Sestav scénář rozhovoru s 5–7 otevřenými otázkami a přepiš každou návodnou otázku na otevřenou.', 'output' => 'Scénář s otázkami a poznámkou, kterou jsi přepsal(a).', 'time' => '18 min'],
            ['text' => 'Proveď ve trojici zkušební rozhovor (tazatel, respondent, zapisovatel) podle scénáře, role se vystřídají.', 'output' => 'Zápis jednoho rozhovoru bez jmen (respondent A).', 'time' => '23 min'],
            ['text' => 'Roztřiď zápis do dvou sloupců: co respondent řekl nebo udělal a co si o tom myslíš ty.', 'output' => 'Tabulka pozorování / domněnka s aspoň 4 řádky.', 'time' => '14 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane seznam otevřených začátků otázek a vzor jedné výzkumné otázky.',
            'standard' => 'Výzkumná otázka, scénář, rozhovor a roztřídění zápisu.',
            'challenge' => 'Navrhne, jak by výzkumnou otázku ověřil(a) pozorováním chování místo rozhovoru.',
        ],
        'assessment' => [
            'formative' => ['Učitel čte otázky, třída zvedne kartu „navádí / nenavádí“.', 'Zapisovatel přečte jeden řádek a třída určí, jestli je to pozorování.'],
            'rubric' => [
                ['criterion' => 'Výzkumná otázka', 'levels' => ['Chybí.', 'Obecná.', 'Co nevíme a jaké rozhodnutí.', 'Ověřitelná a úzce zaměřená.']],
                ['criterion' => 'Scénář', 'levels' => ['Návodné otázky.', 'Mix.', '5–7 otevřených otázek.', 'Otázky na minulé chování, ne na názory.']],
                ['criterion' => 'Zápis', 'levels' => ['Jen dojmy.', 'Smíšený.', 'Oddělené pozorování a domněnka.', 'Domněnky převedené na hypotézy k ověření.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_ux_research', 'competence_label' => 'Test použitelnosti a uspořádání obsahu', 'variants' => [
            ['question' => 'Která otázka v rozhovoru nenavádí?', 'options' => ['Že je ta navigace přehledná?', 'Popište, jak jste naposledy hledal(a) rozvrh na webu školy.', 'Nechtěl(a) byste spíš větší tlačítko?'], 'correct' => 1, 'explanation' => 'Otevřená otázka na skutečné chování.'],
            ['question' => 'Co je pozorování, ne domněnka?', 'options' => ['Respondent třikrát klikl na logo a pak se vrátil zpět.', 'Respondent je asi nešikovný.', 'Stránka se mu určitě nelíbila.'], 'correct' => 0, 'explanation' => 'Pozorování popisuje, co se stalo.'],
            ['question' => 'Co má obsahovat dobrá výzkumná otázka?', 'options' => ['Co chceme uživatelům prodat.', 'Počet respondentů.', 'Co nevíme a jaké rozhodnutí odpověď ovlivní.'], 'correct' => 2, 'explanation' => 'Výzkum slouží rozhodnutí.'],
        ]],
        'homework' => [['text' => 'Volitelné: přepiš tři návodné otázky z ankety, kterou znáš, na otevřené.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Respondenti jsou dobrovolníci ze třídy; zápis bez jmen (respondent A, B), žádné nahrávky.', 'Neptáme se na citlivé osobní údaje (zdraví, rodina, adresa).'],
        'teacher_notes' => ['Častý omyl: otázky na názor („líbí se vám…“) místo na chování – veď k „popište, kdy naposledy…“.', 'Otázka do třídy: Které rozhodnutí v projektu by odpověď změnila?', 'Tempo: rozhovory ve trojicích drž na 7 minut na kolo, ať se vystřídají všichni. Chování pak ověří test použitelnosti (usability test) v L25.'],
        'substitution' => ['Zástup bez odborníka: rozdá vzor scénáře a role; trojice provádějí rozhovory podle úkolů 2–4.', 'Plán B offline: celé bez počítače, zápis do sešitu.'],
        'glossary' => ['usability-test'],
    ],
    20 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 20 · Třídění karet: navigace podle myšlení uživatelů',
        'goal' => [
            'student' => 'Na konci hodiny umím připravit a vyhodnotit třídění karet a podle výsledku navrhnout strukturu navigace (mapu stránek) s názvy, kterým uživatelé rozumějí.',
            'success_criteria' => ['Připravím 15–20 karet s obsahem webu bez nápovědy v pojmenování.', 'Z výsledků najdu skupiny, na kterých se shodla většina, a sporné karty.', 'Navrhnu mapu stránek s nejvýš 6 položkami hlavní navigace.'],
        ],
        'competencies' => [['id' => 'w2_ux_research', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: kde je rozvrh?', 'teacher' => 'Ukáže navigaci, kde je rozvrh pod „O nás“.', 'student' => 'Hádají, kam by ho dali oni.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Příprava karet', 'teacher' => 'Vysvětlí otevřené třídění a pravidla pojmenování karet.', 'student' => 'Napíší 15–20 karet obsahu webu.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 50, 'phase' => 'Třídění', 'teacher' => 'Hlídá, aby autoři nenapovídali.', 'student' => 'Třídí karty jiné dvojice a pojmenují skupiny.', 'form' => 've skupinách'],
            ['from' => 50, 'to' => 68, 'phase' => 'Vyhodnocení', 'teacher' => 'Ukáže tabulku shody karet ve skupinách.', 'student' => 'Spočítají shodu a najdou sporné karty.', 'form' => 've dvojicích'],
            ['from' => 68, 'to' => 82, 'phase' => 'Mapa stránek', 'teacher' => 'Ukáže mapu stránek a limit hlavní navigace.', 'student' => 'Nakreslí mapu stránek podle výsledků.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: názvy podle uživatelů.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš 15–20 karet s obsahem webu (např. rozvrh, jídelna, přijímačky) tak, aby název karty nenapovídal skupinu.', 'output' => 'Sada karet připravená k třídění.', 'time' => '15 min'],
            ['text' => 'Roztřiď karty jiné dvojice do skupin a každou skupinu pojmenuj vlastními slovy.', 'output' => 'Skupiny karet s názvy (fotka nebo tabulka).', 'time' => '20 min'],
            ['text' => 'Vyhodnoť třídění tří skupin: u každé karty zapiš, kolikrát skončila ve stejné skupině, a označ sporné karty.', 'output' => 'Tabulka shody a seznam sporných karet.', 'time' => '18 min'],
            ['text' => 'Nakresli mapu stránek s nejvýš 6 položkami hlavní navigace a pojmenuj je slovy z třídění.', 'output' => 'Mapa stránek se zdůvodněním dvou názvů.', 'time' => '14 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou sadu 15 karet a šablonu tabulky shody.',
            'standard' => 'Vlastní karty, třídění, vyhodnocení a mapa stránek.',
            'challenge' => 'Navrhne uzavřené třídění, kterým ověří sporné karty v nové struktuře.',
        ],
        'assessment' => [
            'formative' => ['Kontrola karet: najde soused kartu, jejíž název napovídá skupinu?', 'Učitel ukáže spornou kartu a třída navrhne, jak s ní naložit.'],
            'rubric' => [
                ['criterion' => 'Karty', 'levels' => ['Méně než 10.', 'Názvy napovídají.', '15–20 neutrálních karet.', 'Karty pokrývají celý obsah webu.']],
                ['criterion' => 'Vyhodnocení', 'levels' => ['Chybí.', 'Jen dojem.', 'Tabulka shody a sporné karty.', 'Návrh, jak sporné karty ověřit.']],
                ['criterion' => 'Mapa stránek', 'levels' => ['Chybí.', 'Přes 6 položek.', 'Nejvýš 6 položek s názvy z třídění.', 'Zdůvodněná podle dat.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_ux_research', 'competence_label' => 'Test použitelnosti a uspořádání obsahu', 'variants' => [
            ['question' => 'Co zjišťuje otevřené třídění karet?', 'options' => ['Které barvy mají uživatelé rádi.', 'Jak rychle se stránka načte.', 'Jak si uživatelé sami seskupí a pojmenují obsah.'], 'correct' => 2, 'explanation' => 'Ukazuje mentální model uživatelů.'],
            ['question' => 'Karta „Rozvrh“ skončila u 3 skupin pokaždé jinde. Co uděláš?', 'options' => ['Označím ji jako spornou a ověřím umístění dalším testem.', 'Smažu ji z webu.', 'Dám ji tam, kde se líbí mně.'], 'correct' => 0, 'explanation' => 'Sporné karty potřebují další ověření.'],
            ['question' => 'Proč omezit hlavní navigaci zhruba na 6 položek?', 'options' => ['Protože víc se nevejde do HTML.', 'Protože méně voleb se rychleji prohlédne a lépe zapamatuje.', 'Protože to vyžaduje zákon.'], 'correct' => 1, 'explanation' => 'Přehlednost a rychlé rozhodnutí.'],
        ]],
        'homework' => [['text' => 'Volitelné: nakresli mapu stránek webu, který často používáš, a označ jednu položku, kterou bys přejmenoval(a).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Třídění je anonymní; výsledky zapisujeme bez jmen.', 'Obsah karet je vymyšlený nebo z veřejného webu školy, bez osobních údajů.'],
        'teacher_notes' => ['Častý omyl: autoři karet během třídění napovídají – posaď je stranou.', 'Otázka do třídy: Které slovo používají uživatelé a které my?', 'Tempo: vyhodnocení stačí ze tří třídění; víc dat dává smysl až doma.'],
        'substitution' => ['Zástup bez odborníka: rozdá papírové kartičky a tabulku shody; skupiny plní úkoly 1–4.', 'Plán B offline: celé s papírovými kartičkami.'],
        'glossary' => ['sitemap'],
    ],
    21 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 21 · Zátěžový test komponenty: dlouhý text, úzký displej',
        'goal' => [
            'student' => 'Na konci hodiny umím otestovat komponentu extrémními daty (dlouhý text, chybějící obrázek, zvětšené písmo, 320 px) a upravit CSS tak, aby se nerozbila.',
            'success_criteria' => ['Komponentu otestuji aspoň v 5 zátěžových situacích.', 'Opravy dělám pružnými hodnotami (min/max, clamp, flex-wrap), ne pevnou výškou.', 'Komponenta funguje při zvětšení písma na 200 %.'],
        ],
        'competencies' => [['id' => 'w2_css_responsive', 'level' => 3], ['id' => 'w2_design_system', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: krásná karta, ošklivá data', 'teacher' => 'Vloží do karty nadpis o 90 znacích a německé slovo.', 'student' => 'Sledují, jak se karta rozpadne.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Zátěžové situace', 'teacher' => 'Představí 6 situací: dlouhý text, prázdný text, bez obrázku, 320 px, 200 % písma, mnoho položek.', 'student' => 'Připraví testovací data pro svou komponentu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 50, 'phase' => 'Test a zápis', 'teacher' => 'Obchází a ptá se na příčinu rozbití.', 'student' => 'Otestují komponentu a zapíší, co se rozbilo.', 'form' => 've dvojicích'],
            ['from' => 50, 'to' => 75, 'phase' => 'Pružné opravy', 'teacher' => 'Ukáže min-height, clamp(), flex-wrap a zalamování slov.', 'student' => 'Opraví nalezené problémy.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 83, 'phase' => 'Opakovaný test', 'teacher' => 'Zadá opakování testu po opravě.', 'student' => 'Ověří opravy stejnými daty.', 'form' => 've dvojicích'],
            ['from' => 83, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: návrh musí přežít skutečná data.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Připrav testovací data pro svou komponentu pro 6 zátěžových situací (dlouhý text, prázdný text, bez obrázku, 320 px, 200 % písma, mnoho položek).', 'output' => 'Tabulka situací s konkrétními daty.', 'time' => '15 min'],
            ['text' => 'Otestuj komponentu ve všech situacích a zapiš, co se rozbilo a proč.', 'output' => 'Tabulka situace → výsledek → příčina.', 'time' => '25 min'],
            ['text' => 'Oprav nalezené problémy pružnými hodnotami (min-height místo height, clamp() pro písmo, flex-wrap, zalamování dlouhých slov).', 'output' => 'Upravené CSS s komentářem u každé opravy.', 'time' => '22 min'],
            ['text' => 'Zopakuj test se stejnými daty a zapiš výsledek po opravě.', 'output' => 'Doplněná tabulka se sloupcem „po opravě“.', 'time' => '8 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane připravenou kartu a testovací data; opravuje dvě situace podle nápovědy.',
            'standard' => 'Vlastní data, test, opravy a opakovaný test.',
            'challenge' => 'Přidá test s pravým-doleva textem nebo dlouhou URL a navrhne pravidlo do design systému.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže rozbitou kartu, třída určí příčinu (pevná výška, nezalomené slovo).', 'Dvojice si vymění data a otestují komponentu souseda.'],
            'rubric' => [
                ['criterion' => 'Testovací data', 'levels' => ['Žádná.', '1–2 situace.', '5–6 situací s konkrétními daty.', 'Data z reálného obsahu webu.']],
                ['criterion' => 'Opravy', 'levels' => ['Pevné hodnoty.', 'Část pružně.', 'Všechny opravy pružnými hodnotami.', 'Pravidlo zapsané do design systému.']],
                ['criterion' => 'Ověření', 'levels' => ['Neověřeno.', 'Jen vizuálně.', 'Opakovaný test stejnými daty.', 'Test i při 200 % písma a 320 px.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_css_responsive', 'competence_label' => 'Responzivní rozvržení v CSS', 'variants' => [
            ['question' => 'Karta s height: 200px se při dlouhém nadpisu rozbije. Co je lepší oprava?', 'options' => ['Zmenšit písmo nadpisu na 10 px.', 'Skrýt přetečení overflow: hidden.', 'Použít min-height, aby karta mohla růst.'], 'correct' => 2, 'explanation' => 'Pružná výška přizpůsobí kartu obsahu.'],
            ['question' => 'Proč testovat komponentu při zvětšení písma na 200 %?', 'options' => ['Lidé se slabším zrakem si písmo zvětšují a obsah se nesmí ztratit.', 'Protože to vyžaduje prohlížeč.', 'Aby se ověřila rychlost.'], 'correct' => 0, 'explanation' => 'Zvětšení textu je běžná potřeba uživatelů.'],
            ['question' => 'Co dělá v CSS funkce clamp(1rem, 2vw, 1.5rem)?', 'options' => ['Nastaví barvu podle šířky.', 'Hodnota roste s šířkou okna, ale nikdy není menší než 1rem ani větší než 1.5rem.', 'Ořízne text na jeden řádek.'], 'correct' => 1, 'explanation' => 'Pružná hodnota s dolní a horní mezí.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi na webu jednu komponentu, která se rozbije při dlouhém textu, a popiš příčinu.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Testovací data jsou vymyšlená; žádná skutečná jména ani kontakty.', 'Testujeme vlastní komponenty, ne cizí weby.'],
        'teacher_notes' => ['Častý omyl: oprava skrytím přetečení (overflow: hidden) – obsah se tím ztratí.', 'Otázka do třídy: Jaká data přijdou od skutečných uživatelů, na která návrh nepočítal?', 'Tempo: opravy jsou jádro – když test trvá dlouho, opravte aspoň tři situace.'],
        'substitution' => ['Zástup bez odborníka: rozdá kartu a testovací data; žáci plní úkoly 1–4 v editoru.', 'Plán B offline: zátěžový test na papírových maketách s vystřiženými texty.'],
        'glossary' => ['flexbox', 'breakpoint'],
    ],
    22 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 22 · Formulář, který pomáhá z chyby ven',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout stavy formuláře pro chybu, ztrátu spojení a nedokončené vyplnění tak, aby uživatel neztratil data a věděl, jak pokračovat.',
            'success_criteria' => ['Chyba se ukáže u pole s návodem na opravu a vyplněná data zůstanou.', 'Navrhnu stav pro ztrátu spojení s možností zkusit znovu.', 'Kontrolu pole provádím po opuštění pole, ne při každém stisku klávesy.'],
        ],
        'competencies' => [['id' => 'w2_forms_states', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: smazaný formulář', 'teacher' => 'Ukáže formulář, který po chybě smaže všechna pole.', 'student' => 'Popíší, jak by reagovali jako uživatelé.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Mapa stavů', 'teacher' => 'Ukáže stavový diagram formuláře: prázdný, vyplňuje, chyba, odesílá, hotovo, selhalo.', 'student' => 'Nakreslí stavy svého formuláře.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Chyba u pole', 'teacher' => 'Vysvětlí validaci po opuštění pole a souhrn chyb nahoře.', 'student' => 'Navrhnou hlášky a umístění chyb.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 65, 'phase' => 'Ztráta spojení', 'teacher' => 'Ukáže stav „Nepodařilo se odeslat – data máte uložená, zkusit znovu“.', 'student' => 'Navrhnou stav selhání a obnovy.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Průchod scénářem', 'teacher' => 'Zadá scénáře chyb pro test.', 'student' => 'Projdou návrh souseda třemi scénáři.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: chyba nesmí trestat uživatele.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Nakresli stavový diagram formuláře přihlášky se stavy prázdný, vyplňuje, chyba, odesílá, hotovo a selhalo.', 'output' => 'Diagram šesti stavů s přechody.', 'time' => '15 min'],
            ['text' => 'Navrhni chybové hlášky ke třem polím a souhrn chyb nad formulářem s odkazy na chybná pole.', 'output' => 'Návrh chyb u polí a souhrnu.', 'time' => '20 min'],
            ['text' => 'Navrhni stav „odeslání selhalo“, ve kterém zůstanou data a je tlačítko pro nový pokus.', 'output' => 'Obrazovka selhání s textem a akcí.', 'time' => '18 min'],
            ['text' => 'Projdi návrh spolužáka třemi scénáři (překlep v e-mailu, výpadek spojení, odchod a návrat) a zapiš, kde by uživatel ztratil data.', 'output' => 'Zápis tří scénářů s nálezy.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane předkreslený diagram se šesti stavy; doplňuje texty a přechody.',
            'standard' => 'Diagram, hlášky, stav selhání a test scénáři.',
            'challenge' => 'Navrhne i průběžné ukládání konceptu a popíše, kdy a jak se uživateli oznámí.',
        ],
        'assessment' => [
            'formative' => ['Učitel ukáže hlášku a třída řekne, jestli obsahuje opravu.', 'Dvojice: kde v návrhu se ztratí data?'],
            'rubric' => [
                ['criterion' => 'Stavy formuláře', 'levels' => ['Jen výchozí.', '2–3 stavy.', 'Všech šest stavů s přechody.', 'Stavy pokrývají i návrat uživatele.']],
                ['criterion' => 'Chybové hlášky', 'levels' => ['Obecné.', 'U pole bez opravy.', 'U pole s opravou a souhrn nahoře.', 'Souhrn odkazuje na pole a čte ho čtečka.']],
                ['criterion' => 'Obnova po selhání', 'levels' => ['Data se ztratí.', 'Data zůstanou, bez akce.', 'Data zůstanou a lze zkusit znovu.', 'Průběžné ukládání konceptu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_forms_states', 'competence_label' => 'Formuláře a stavy', 'variants' => [
            ['question' => 'Co se má stát s vyplněnými daty, když odeslání formuláře selže kvůli výpadku spojení?', 'options' => ['Data zůstanou a uživatel může zkusit odeslat znovu.', 'Formulář se vymaže, aby začal znovu.', 'Stránka se přesměruje na úvod.'], 'correct' => 0, 'explanation' => 'Selhání nesmí trestat uživatele ztrátou dat.'],
            ['question' => 'Kdy je nejlepší kontrolovat formát e-mailu v poli?', 'options' => ['Při každém stisku klávesy hned od prvního písmene.', 'Až po opuštění pole nebo při odeslání.', 'Nikdy.'], 'correct' => 1, 'explanation' => 'Hláška při psaní ruší; po opuštění pole pomůže.'],
            ['question' => 'K čemu je souhrn chyb nad formulářem?', 'options' => ['Nahrazuje hlášky u polí.', 'Je jen dekorace.', 'Řekne, kolik a kde je chyb, a odkazuje na chybná pole.'], 'correct' => 2, 'explanation' => 'Pomůže hlavně u dlouhých formulářů a čtečkám.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyzkoušej formulář na webu, který znáš, se záměrnou chybou a zapiš, jestli ti zůstala data.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Formuláře testujeme s vymyšlenými daty (jan@example.com), nikdy se skutečnými údaji.', 'Na cizích webech nic neodesíláme; testujeme jen chování před odesláním.'],
        'teacher_notes' => ['Častý omyl: validace při každém stisku klávesy – uživatel vidí chybu dřív, než dopíše.', 'Otázka do třídy: Co uživatel ztratí, když formulář po chybě vymaže pole?', 'Tempo: diagram stavů zkrať na 10 minut, pokud ho třída zvládá; stav selhání je nejnovější téma.'],
        'substitution' => ['Zástup bez odborníka: rozdá šablonu diagramu a scénáře; žáci plní úkoly 1–4 na papíře.', 'Plán B offline: stavy formuláře jako papírové obrazovky.'],
        'glossary' => ['validation', 'label'],
    ],
]]];
