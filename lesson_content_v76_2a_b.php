<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · obsahová stopa, vlna 2 – 2.A (grafika a webdesign II), lekce 23–28.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v76_1a_a.php.
 * Přepis šablonových lekcí lessons_v30.php. Kompetence: návrh katalogu v75 „grafika_web_ii“.
 */

return ['class_2a' => ['lessons' => [
    23 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 23 · Přístupné interakce: klávesnice, fokus a čtečka obrazovky',
        'goal' => [
            'student' => 'Na konci hodiny umím ověřit interaktivní prvky (menu, dialog, záložky) klávesnicí a čtečkou obrazovky a opravit nejčastější chyby: chybějící fokus, past fokusu a tlačítko z prvku div.',
            'success_criteria' => ['Projdu prvek jen klávesnicí (Tab, Shift+Tab, Enter, mezerník, Esc) a zapíšu, co nefunguje.', 'Pro tlačítko použiji element button, ne div s kliknutím.', 'Dialog vrátí po zavření fokus na prvek, který ho otevřel.'],
        ],
        'competencies' => [['id' => 'w2_a11y_audit', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: myš schovaná', 'teacher' => 'Požádá třídu, aby na minutu schovala myš a otevřela menu stránky.', 'student' => 'Zkusí ovládat ukázkový web jen klávesnicí.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Klávesové vzory', 'teacher' => 'Ukáže očekávané ovládání menu, dialogu a záložek.', 'student' => 'Zapíší si tabulku kláves a očekávaného chování.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Test klávesnicí', 'teacher' => 'Rozdá ukázkovou stránku se třemi chybnými prvky.', 'student' => 'Otestují prvky a zapíší nálezy.', 'form' => 've dvojicích'],
            ['from' => 45, 'to' => 60, 'phase' => 'Čtečka obrazovky', 'teacher' => 'Předvede, co přečte čtečka u div a u button.', 'student' => 'Poslechnou si rozdíl a doplní nálezy.', 'form' => 'frontálně'],
            ['from' => 60, 'to' => 82, 'phase' => 'Opravy', 'teacher' => 'Obchází a hlídá sémantické řešení před ARIA.', 'student' => 'Opraví tři nalezené chyby a test zopakují.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: nejdřív správný HTML prvek.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Sepiš tabulku: menu, dialog a záložky – které klávesy je ovládají a co se má stát.', 'output' => 'Tabulka tří prvků s klávesami a očekáváním.', 'time' => '12 min'],
            ['text' => 'Otestuj ukázkovou stránku jen klávesnicí a zapiš nálezy: kde zmizí fokus, kde uvízne a co nejde spustit.', 'output' => 'Seznam nálezů s místem a klávesou.', 'time' => '20 min'],
            ['text' => 'Oprav tlačítko vytvořené z prvku div tak, že použiješ element button, a ověř ho klávesou Enter i mezerníkem.', 'output' => 'Opravený kód a výsledek testu.', 'time' => '12 min'],
            ['text' => 'Oprav dialog: Esc ho zavře, fokus zůstane uvnitř, dokud je otevřený, a po zavření se vrátí na tlačítko, které ho otevřelo.', 'output' => 'Dialog, který projde testem klávesnicí.', 'time' => '18 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane tabulku kláves vyplněnou a opravuje jen tlačítko z div.',
            'standard' => 'Test klávesnicí, nálezy a opravy tlačítka i dialogu.',
            'challenge' => 'Ověří i nastavení reduced motion u animace dialogu a doplní pravidlo do design systému.',
        ],
        'assessment' => [
            'formative' => ['Učitel stiskne Tab na promítnuté stránce a třída hlásí, kde je fokus.', 'Dvojice: poslechni si čtečku – zní prvek jako tlačítko?'],
            'rubric' => [
                ['criterion' => 'Test klávesnicí', 'levels' => ['Netestováno.', 'Jen Tab.', 'Tab, Shift+Tab, Enter, mezerník, Esc.', 'Nálezy s doporučením.']],
                ['criterion' => 'Sémantika', 'levels' => ['div s kliknutím.', 'div s ARIA.', 'Správný HTML prvek.', 'Zdůvodní, proč sémantika před ARIA.']],
                ['criterion' => 'Fokus v dialogu', 'levels' => ['Fokus se ztratí.', 'Esc funguje.', 'Fokus uvnitř a návrat po zavření.', 'Ověřeno i čtečkou obrazovky.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_a11y_audit', 'competence_label' => 'Audit přístupnosti', 'variants' => [
            ['question' => 'Jak správně vytvoříš klikací tlačítko?', 'options' => ['div s obsluhou kliknutí.', 'Element button.', 'Obrázek s odkazem #.'], 'correct' => 1, 'explanation' => 'button umí fokus, Enter, mezerník i roli pro čtečku.'],
            ['question' => 'Kam se má vrátit fokus po zavření dialogu?', 'options' => ['Na začátek stránky.', 'Nikam, stačí zavřít.', 'Na prvek, který dialog otevřel.'], 'correct' => 2, 'explanation' => 'Uživatel pokračuje tam, kde skončil.'],
            ['question' => 'Co je „past fokusu“ (focus trap) jako chyba?', 'options' => ['Fokus uvízne v prvku a klávesnicí se z něj nedá dostat.', 'Fokus je moc výrazný.', 'Stránka nemá žádné odkazy.'], 'correct' => 0, 'explanation' => 'Mimo otevřený dialog nesmí fokus uvíznout.'],
        ]],
        'homework' => [['text' => 'Volitelné: projdi web své oblíbené služby jen klávesnicí a zapiš jednu past nebo ztracený fokus.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Testujeme ukázkové a vlastní stránky; na cizích webech nic neodesíláme ani nepřihlašujeme.', 'Čtečku obrazovky používáme jen se zapnutými sluchátky nebo tiše, ať nerušíme ostatní.'],
        'teacher_notes' => ['Častý omyl: oprava pomocí ARIA rolí místo správného prvku – „nejlepší ARIA je žádná ARIA“.', 'Otázka do třídy: Kdo všechno ovládá web jinak než myší (screen reader, klávesnice, hlas)?', 'Tempo: dialog je nejnáročnější – kdo nestihne, odevzdá opravu tlačítka a test.'],
        'substitution' => ['Zástup bez odborníka: žáci testují ukázkovou stránku klávesnicí podle tabulky a odevzdají nálezy (úkoly 1–2).', 'Plán B offline: na papíře rozkreslí pořadí fokusu na vytištěné stránce.'],
        'glossary' => ['screen-reader', 'reduced-motion'],
    ],
    24 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 24 · Od návrhových tokenů ke kódu: proměnné CSS',
        'goal' => [
            'student' => 'Na konci hodiny umím převést tokeny design systému (barvy, mezery, písmo) do proměnných CSS ve dvou vrstvách – základní hodnoty a sémantické role – a přepnout světlý a tmavý režim jen změnou rolí.',
            'success_criteria' => ['Základní tokeny mají neutrální jména (např. --blue-600), sémantické role jména podle účelu (--color-action).', 'Komponenty používají jen sémantické role, ne základní hodnoty.', 'Tmavý režim vznikne přepsáním rolí v jednom bloku.'],
        ],
        'competencies' => [['id' => 'w2_design_system', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: přebarvi tlačítko', 'teacher' => 'Ukáže web, kde je barva tlačítka zapsaná na 14 místech.', 'student' => 'Odhadnou, kolik práce je změna barvy.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Dvě vrstvy tokenů', 'teacher' => 'Vysvětlí základní hodnoty a sémantické role.', 'student' => 'Roztřídí tokeny svého návrhu do dvou vrstev.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Proměnné CSS', 'teacher' => 'Předvede :root, var() a pojmenování.', 'student' => 'Zapíší tokeny jako proměnné CSS.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Komponenty na role', 'teacher' => 'Ukáže přepis tlačítka a karty na role.', 'student' => 'Přepíší dvě komponenty na sémantické role.', 'form' => 've dvojicích'],
            ['from' => 65, 'to' => 82, 'phase' => 'Tmavý režim', 'teacher' => 'Ukáže přepsání rolí v prefers-color-scheme.', 'student' => 'Přidají tmavý režim a ověří kontrast.', 'form' => 'jednotlivě'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: role chrání komponenty před změnou.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Roztřiď tokeny svého návrhu do dvou vrstev: základní hodnoty (odstíny, škála mezer) a sémantické role (akce, text, povrch, chyba).', 'output' => 'Tabulka dvou vrstev s aspoň 6 rolemi.', 'time' => '15 min'],
            ['text' => 'Zapiš obě vrstvy jako proměnné CSS v :root a role odkaž na základní hodnoty přes var().', 'output' => 'Blok :root se základními hodnotami a rolemi.', 'time' => '18 min'],
            ['text' => 'Přepiš tlačítko a kartu tak, aby používaly jen sémantické role a žádnou základní hodnotu ani HEX kód.', 'output' => 'CSS dvou komponent bez přímých barev.', 'time' => '20 min'],
            ['text' => 'Přidej tmavý režim přepsáním rolí v bloku @media (prefers-color-scheme: dark) a ověř kontrast textu a tlačítka.', 'output' => 'Tmavý režim a změřený kontrast dvou dvojic.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou vrstvu základních hodnot; doplňuje role a přepisuje jednu komponentu.',
            'standard' => 'Dvě vrstvy, proměnné CSS, dvě komponenty a tmavý režim.',
            'challenge' => 'Přidá přepínač režimu atributem data-theme a pravidla zapíše jako krátkou dokumentaci pro předání (handoff).',
        ],
        'assessment' => [
            'formative' => ['Učitel napíše --blue-600 a --color-action; třída řekne, který token patří do komponenty.', 'Dvojice: změň jednu roli a ukaž, co se přebarvilo.'],
            'rubric' => [
                ['criterion' => 'Vrstvy tokenů', 'levels' => ['Jedna vrstva.', 'Role pojmenované podle barvy.', 'Základní hodnoty a sémantické role.', 'Role pokrývají stavy (hover, chyba).']],
                ['criterion' => 'Komponenty', 'levels' => ['Přímé HEX kódy.', 'Mix rolí a hodnot.', 'Jen role.', 'Bez jediné výjimky a s komentářem.']],
                ['criterion' => 'Tmavý režim', 'levels' => ['Chybí.', 'Přepisuje komponenty.', 'Přepisuje jen role.', 'Kontrast ověřen v obou režimech.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_design_system', 'competence_label' => 'Design systém', 'variants' => [
            ['question' => 'Který token má používat tlačítko v CSS?', 'options' => ['--blue-600', '--color-action', '#1D4ED8'], 'correct' => 1, 'explanation' => 'Komponenta používá roli, ne konkrétní odstín.'],
            ['question' => 'Jak nejjednodušeji vznikne tmavý režim při dvou vrstvách tokenů?', 'options' => ['Přepíšu hodnoty sémantických rolí v jednom bloku.', 'Napíšu všechny komponenty znovu.', 'Invertuji barvy filtrem.'], 'correct' => 0, 'explanation' => 'Komponenty zůstanou, mění se jen role.'],
            ['question' => 'Co zapisuje var(--space-16)?', 'options' => ['Novou proměnnou s hodnotou 16.', 'Komentář pro vývojáře.', 'Použití hodnoty proměnné --space-16.'], 'correct' => 2, 'explanation' => 'var() čte hodnotu vlastní vlastnosti CSS.'],
        ]],
        'homework' => [['text' => 'Volitelné: přidej do své sady jednu roli pro stav chyby a použij ji ve formuláři.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Kód píšeme vlastní; ukázky z dokumentace jen s uvedením zdroje.', 'Barvy tmavého režimu ověřujeme kontrastem – přístupnost má přednost před vzhledem.'],
        'teacher_notes' => ['Častý omyl: role pojmenované podle barvy (--color-blue) – po změně odstínu lžou. Návrhový token (design token) nese účel.', 'Otázka do třídy: Co se musí změnit, když škola změní hlavní barvu?', 'Tempo: tmavý režim je pro rychlejší; standard je dvě vrstvy a jedna komponenta.'],
        'substitution' => ['Zástup bez odborníka: rozdá kostru CSS s :root a zadání; žáci plní úkoly 1–3, tmavý režim příště.', 'Plán B offline: tabulka dvou vrstev tokenů na papíře a přepis komponenty ručně.'],
        'glossary' => ['design-token', 'handoff'],
    ],
    25 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 25 · Druhé kolo testu použitelnosti: úspěšnost a závažnost',
        'goal' => [
            'student' => 'Na konci hodiny umím připravit a moderovat test použitelnosti se třemi úkoly, změřit úspěšnost a čas a nálezy seřadit podle závažnosti do plánu oprav.',
            'success_criteria' => ['Úkoly jsou napsané jako situace, ne jako návod („Chceš se přihlásit na kurz…“, ne „Klikni na Přihláška“).', 'U každého úkolu zapíšu úspěch, čas a místo, kde uživatel zaváhal.', 'Nálezy seřadím podle závažnosti (blokuje / zdržuje / drobnost) a četnosti.'],
        ],
        'competencies' => [['id' => 'w2_ux_research', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: co se změnilo od prvního kola', 'teacher' => 'Připomene nálezy z prvního testu a co se opravilo.', 'student' => 'Zapíší, co chtějí ověřit ve druhém kole.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Úkoly jako situace', 'teacher' => 'Ukáže rozdíl mezi návodem a situací.', 'student' => 'Napíší tři úkoly jako situace.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 60, 'phase' => 'Testování', 'teacher' => 'Hlídá roli moderátora (nenapovídá) a zapisovatele.', 'student' => 'Otestují návrh na dvou spolužácích z jiné skupiny.', 'form' => 've skupinách'],
            ['from' => 60, 'to' => 75, 'phase' => 'Vyhodnocení', 'teacher' => 'Ukáže tabulku úspěšnosti a času.', 'student' => 'Spočítají úspěšnost a seřadí nálezy.', 'form' => 've dvojicích'],
            ['from' => 75, 'to' => 83, 'phase' => 'Plán oprav', 'teacher' => 'Připomene řazení podle závažnosti a četnosti.', 'student' => 'Vyberou tři opravy.', 'form' => 'jednotlivě'],
            ['from' => 83, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: měříme chování, ne názory.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš tři úkoly testu jako situace z života uživatele, bez slov, která jsou na tlačítkách.', 'output' => 'Tři úkoly a u každého, co znamená „splněno“.', 'time' => '15 min'],
            ['text' => 'Moderuj test se dvěma spolužáky z jiné skupiny: nenapovídej, jen se ptej „co teď čekáš?“.', 'output' => 'Zápis dvou testů bez jmen (účastník A, B).', 'time' => '30 min'],
            ['text' => 'Spočítej úspěšnost každého úkolu (kolik z kolika splnilo) a průměrný čas a zapiš místa zaváhání.', 'output' => 'Tabulka úkol → úspěšnost → čas → zaváhání.', 'time' => '15 min'],
            ['text' => 'Seřaď nálezy podle závažnosti a četnosti a vyber tři opravy do plánu.', 'output' => 'Plán tří oprav se závažností.', 'time' => '10 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vzor úkolů jako situací a připravenou tabulku vyhodnocení.',
            'standard' => 'Vlastní úkoly, dva testy, vyhodnocení a plán oprav.',
            'challenge' => 'Porovná výsledky prvního a druhého kola a zapíše, jestli opravy pomohly.',
        ],
        'assessment' => [
            'formative' => ['Učitel přečte úkol, třída zvedne kartu „situace / návod“.', 'Kontrola moderátora: napověděl? (zapisovatel zaškrtne)'],
            'rubric' => [
                ['criterion' => 'Úkoly', 'levels' => ['Návody.', 'Mix.', 'Tři situace s kritériem splnění.', 'Úkoly míří na sporná místa z prvního kola.']],
                ['criterion' => 'Moderování', 'levels' => ['Napovídá.', 'Občas napoví.', 'Nenapovídá, ptá se na očekávání.', 'Zapíše i tiché zaváhání.']],
                ['criterion' => 'Vyhodnocení', 'levels' => ['Jen dojmy.', 'Úspěšnost bez řazení.', 'Úspěšnost, čas a řazení podle závažnosti.', 'Srovnání s prvním kolem.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_ux_research', 'competence_label' => 'Test použitelnosti a uspořádání obsahu', 'variants' => [
            ['question' => 'Který úkol do testu použitelnosti je napsaný správně?', 'options' => ['Klikni na tlačítko Přihláška a vyplň formulář.', 'Najdi menu a otevři stránku Kurzy.', 'Chceš chodit na kurz kreslení ve středu. Zjisti, jestli je volné místo, a přihlas se.'], 'correct' => 2, 'explanation' => 'Situace neprozrazuje cestu ani slova z rozhraní.'],
            ['question' => 'Účastník se zasekne. Co udělá moderátor?', 'options' => ['Ukáže mu správné tlačítko.', 'Zeptá se „Co teď čekáš, že se stane?“ a zapíše odpověď.', 'Ukončí test.'], 'correct' => 1, 'explanation' => 'Moderátor nenapovídá; zjišťuje očekávání.'],
            ['question' => 'Úkol splnili 2 ze 4 účastníků. Jaká je úspěšnost?', 'options' => ['50 %', '25 %', '2 %'], 'correct' => 0, 'explanation' => '2 / 4 = 0,5 = 50 %.'],
        ]],
        'homework' => [['text' => 'Volitelné: proveď jeden test se členem rodiny podle svých úkolů a doplň výsledek do tabulky.', 'minutes' => 20, 'optional' => true]],
        'safety' => ['Účast v testu je dobrovolná; zápis bez jmen a bez nahrávání obrazovky či obličeje.', 'Testujeme návrh, ne člověka – „chyba“ je vždy chyba rozhraní.'],
        'teacher_notes' => ['Častý omyl: moderátor napovídá, když je ticho – nauč žáky vydržet 10 sekund.', 'Otázka do třídy: Co nám řekne usability test, co nám neřekne anketa?', 'Tempo: dva testy po 12 minutách; vyhodnocení nesmí vypadnout.'],
        'substitution' => ['Zástup bez odborníka: rozdá vzor úkolů a tabulku; skupiny provádějí testy podle úkolů 1–4.', 'Plán B offline: test na papírovém prototypu (obrazovky na kartičkách).'],
        'glossary' => ['usability-test'],
    ],
    26 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 26 · Klinika design QA: hlášení chyb, která se dají opravit',
        'goal' => [
            'student' => 'Na konci hodiny umím vést kliniku kontroly kvality: projít stránku podle kontrolního seznamu, zapsat chyby do sdílené tabulky se závažností a krokem k reprodukci a ověřit opravu.',
            'success_criteria' => ['Každé hlášení má kroky k reprodukci, očekávání, skutečnost a závažnost.', 'Duplicitní hlášení sloučím a ponechám jedno s odkazem.', 'Opravu ověřím stejnými kroky a hlášení uzavřu jen s důkazem.'],
        ],
        'competencies' => [['id' => 'g2_handoff', 'level' => 3], ['id' => 'w2_a11y_audit', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: hlášení „nefunguje to“', 'teacher' => 'Ukáže tři špatná hlášení a jedno dobré.', 'student' => 'Určí, co dobrému hlášení dává sílu.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 20, 'phase' => 'Role kliniky', 'teacher' => 'Rozdělí role: testeři, třídič hlášení, opravář.', 'student' => 'Domluví se na tabulce hlášení.', 'form' => 've skupinách'],
            ['from' => 20, 'to' => 45, 'phase' => 'Kontrola a hlášení', 'teacher' => 'Hlídá kroky k reprodukci.', 'student' => 'Testeři zapisují chyby, třídič slučuje duplicity.', 'form' => 've skupinách'],
            ['from' => 45, 'to' => 70, 'phase' => 'Opravy', 'teacher' => 'Pomáhá s prioritou podle závažnosti.', 'student' => 'Opravář opraví tři nejzávažnější chyby.', 'form' => 've skupinách'],
            ['from' => 70, 'to' => 82, 'phase' => 'Ověření a uzavření', 'teacher' => 'Vyžaduje důkaz u uzavřeného hlášení.', 'student' => 'Testeři ověří opravy a hlášení uzavřou.', 'form' => 've skupinách'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne životní cyklus chyby.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Jako tester zapiš do sdílené tabulky aspoň 4 chyby s kroky k reprodukci, očekáváním, skutečností a závažností.', 'output' => 'Čtyři úplná hlášení v tabulce skupiny.', 'time' => '25 min'],
            ['text' => 'Jako třídič projdi tabulku, slouč duplicitní hlášení a seřaď je podle závažnosti.', 'output' => 'Seřazená tabulka bez duplicit.', 'time' => '10 min'],
            ['text' => 'Oprav tři nejzávažnější chyby a ke každé zapiš, co jsi změnil(a).', 'output' => 'Tři opravy s popisem změny.', 'time' => '20 min'],
            ['text' => 'Ověř opravy stejnými kroky k reprodukci a uzavři hlášení jen se snímkem nebo popisem výsledku.', 'output' => 'Uzavřená hlášení s důkazem.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu hlášení s vyplněným příkladem a roli testera.',
            'standard' => 'Hlášení, třídění, opravy a ověření ve skupině.',
            'challenge' => 'Navrhne pravidlo do design systému, které by nejčastější chybě předešlo.',
        ],
        'assessment' => [
            'formative' => ['Učitel přečte hlášení, třída zkusí chybu zopakovat jen podle kroků.', 'Kontrola uzavření: má každé uzavřené hlášení důkaz?'],
            'rubric' => [
                ['criterion' => 'Hlášení', 'levels' => ['„Nefunguje to.“', 'Bez kroků k reprodukci.', 'Kroky, očekávání, skutečnost, závažnost.', 'Doplněno o prostředí (šířka, prohlížeč).']],
                ['criterion' => 'Třídění', 'levels' => ['Bez pořadí.', 'Duplicity zůstaly.', 'Sloučeno a seřazeno podle závažnosti.', 'Zdůvodněné pořadí.']],
                ['criterion' => 'Ověření opravy', 'levels' => ['Neověřeno.', 'Uzavřeno bez důkazu.', 'Ověřeno stejnými kroky s důkazem.', 'Ověřeny i související části.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_handoff', 'competence_label' => 'Předání návrhu a případová studie', 'variants' => [
            ['question' => 'Co musí mít hlášení chyby, aby ji šlo opravit bez doptávání?', 'options' => ['Jméno autora chyby.', 'Kroky k reprodukci, očekávání, skutečnost a závažnost.', 'Jen slovo „urgentní“.'], 'correct' => 1, 'explanation' => 'Opravář musí chybu zopakovat a vědět, co je správně.'],
            ['question' => 'Kdy smíš hlášení uzavřít?', 'options' => ['Hned, jak opravář řekne, že je hotovo.', 'Když se o chybě přestane mluvit.', 'Až po ověření stejnými kroky s důkazem.'], 'correct' => 2, 'explanation' => 'Uzavření bez ověření je jen naděje.'],
            ['question' => 'Dva testeři nahlásili stejnou chybu. Co udělá třídič?', 'options' => ['Hlášení sloučí a ponechá jedno s odkazem na druhé.', 'Smaže obě.', 'Nechá obě, ať je chyba vidět víc.'], 'correct' => 0, 'explanation' => 'Duplicity rozptylují práci.'],
        ]],
        'homework' => [['text' => 'Volitelné: napiš jedno úplné hlášení chyby k aplikaci, kterou používáš, a zkus ji zopakovat podle svých kroků.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Hlášení se týkají produktu, ne lidí; bez jmen a bez obviňování.', 'Snímky obrazovky bez osobních údajů (vymyšlená data).'],
        'teacher_notes' => ['Častý omyl: hlášení uzavřené bez ověření – trvej na důkazu.', 'Otázka do třídy: Kterou chybu by uživatel poznal jako první a kterou nikdy?', 'Tempo: role se mohou po 20 minutách vyměnit, aby si každý zkusil hlášení i opravu.'],
        'substitution' => ['Zástup bez odborníka: rozdá šablonu hlášení a role; skupiny pracují podle úkolů 1–4.', 'Plán B offline: kontrola vytištěné stránky a hlášení na papírových lístcích.'],
        'glossary' => ['handoff', 'focus'],
    ],
    27 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 27 · Produktový sprint: architektura, přístupnost a předání',
        'goal' => [
            'student' => 'Na konci hodiny umím v týmu za 90 minut navrhnout malý produkt od mapy stránek přes prototyp hlavního toku po předání vývoji se specifikací komponent a stavů.',
            'success_criteria' => ['Tým má mapu stránek a jeden hlavní uživatelský tok o nejvýš 5 krocích.', 'Prototyp toku používá komponenty design systému včetně stavů chyby.', 'Předávací list obsahuje tokeny, stavy, chování klávesnicí a otevřené otázky.'],
        ],
        'competencies' => [['id' => 'w2_design_system', 'level' => 3], ['id' => 'w2_forms_states', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 8, 'phase' => 'Zadání sprintu', 'teacher' => 'Rozdá zadání (vymyšlená služba) a role v týmu.', 'student' => 'Rozdělí role: architekt, designér, kontrola přístupnosti, předání.', 'form' => 've skupinách'],
            ['from' => 8, 'to' => 25, 'phase' => 'Mapa a tok', 'teacher' => 'Hlídá limit 5 kroků toku.', 'student' => 'Nakreslí mapu stránek a hlavní tok.', 'form' => 've skupinách'],
            ['from' => 25, 'to' => 55, 'phase' => 'Prototyp', 'teacher' => 'Obchází a ptá se na stavy chyby.', 'student' => 'Postaví prototyp toku z komponent.', 'form' => 've skupinách'],
            ['from' => 55, 'to' => 68, 'phase' => 'Kontrola přístupnosti', 'teacher' => 'Připomene kontrast, fokus a labely.', 'student' => 'Zkontrolují prototyp a opraví nálezy.', 'form' => 've skupinách'],
            ['from' => 68, 'to' => 82, 'phase' => 'Předávací list', 'teacher' => 'Ukáže osnovu předávacího listu.', 'student' => 'Sepíší tokeny, stavy, chování a otevřené otázky.', 'form' => 've skupinách'],
            ['from' => 82, 'to' => 90, 'phase' => 'Prezentace a exit ticket', 'teacher' => 'Vybere dva týmy na minutovou ukázku.', 'student' => 'Odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Nakreslete v týmu mapu stránek služby a hlavní uživatelský tok o nejvýš 5 krocích.', 'output' => 'Mapa stránek a diagram toku.', 'time' => '17 min'],
            ['text' => 'Postavte prototyp hlavního toku z komponent design systému včetně stavu chyby ve formuláři.', 'output' => 'Klikací prototyp s aspoň jedním chybovým stavem.', 'time' => '30 min'],
            ['text' => 'Zkontrolujte prototyp z hlediska přístupnosti (kontrast, viditelný fokus, labely) a opravte nálezy.', 'output' => 'Seznam nálezů a provedených oprav.', 'time' => '13 min'],
            ['text' => 'Sepište předávací list: použité tokeny, stavy komponent, chování klávesnicí a otevřené otázky.', 'output' => 'Předávací list na jednu stranu.', 'time' => '14 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane roli kontroly přístupnosti s připraveným kontrolním seznamem.',
            'standard' => 'Plná práce v týmu podle rolí.',
            'challenge' => 'Jako architekt zdůvodní mapu stránek podle třídění karet z L20.',
        ],
        'assessment' => [
            'formative' => ['Kontrola po 25 minutách: má každý tým tok o nejvýš 5 krocích?', 'Učitel vybere náhodnou komponentu a tým ukáže její chybový stav.'],
            'rubric' => [
                ['criterion' => 'Architektura a tok', 'levels' => ['Chybí.', 'Tok přes 5 kroků.', 'Mapa a tok do 5 kroků.', 'Zdůvodněno daty z výzkumu.']],
                ['criterion' => 'Prototyp a stavy', 'levels' => ['Nedokončený.', 'Bez stavů.', 'Tok z komponent se stavem chyby.', 'Stavy i pro selhání a potvrzení.']],
                ['criterion' => 'Předávací list', 'levels' => ['Chybí.', 'Jen obrázky.', 'Tokeny, stavy, klávesnice, otázky.', 'Vývojář by podle něj stavěl bez doptávání.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_design_system', 'competence_label' => 'Design systém', 'variants' => [
            ['question' => 'Proč omezit hlavní uživatelský tok na málo kroků?', 'options' => ['Protože prototyp nesmí mít víc obrazovek.', 'Protože každý krok navíc zvyšuje šanci, že uživatel odejde.', 'Kvůli velikosti souboru.'], 'correct' => 1, 'explanation' => 'Méně kroků = méně míst, kde se uživatel ztratí.'],
            ['question' => 'Co nepatří do předávacího listu vývoji?', 'options' => ['Hodnocení spolužáků v týmu.', 'Stavy komponent a chování klávesnicí.', 'Použité tokeny.'], 'correct' => 0, 'explanation' => 'Předávací list popisuje produkt, ne lidi.'],
            ['question' => 'K čemu jsou v předávacím listu „otevřené otázky“?', 'options' => ['K vyplnění místa.', 'Aby vývojář nic nestavěl.', 'Aby bylo jasné, co tým ještě neví a musí se rozhodnout.'], 'correct' => 2, 'explanation' => 'Otevřené otázky brání tichým domněnkám.'],
        ]],
        'homework' => [['text' => 'Volitelné: doplň do předávacího listu jednu odpověď na otevřenou otázku, kterou jste v týmu nevyřešili.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Služba ze zadání je vymyšlená; prototyp nepoužívá skutečná osobní data.', 'Hodnotíme produkt, ne členy týmu; role se rozdělují dobrovolně.'],
        'teacher_notes' => ['Častý omyl: tým kreslí obrazovky dřív, než má tok – po 25 minutách zkontroluj diagram.', 'Otázka do třídy: Co by se stalo, kdyby vývojář dostal jen obrázky bez stavů?', 'Tempo: kontrola přístupnosti může běžet souběžně s prototypem, pokud tým zaostává.'],
        'substitution' => ['Zástup bez odborníka: rozdá zadání a role; týmy pracují podle úkolů 1–4 a odevzdají soubory.', 'Plán B offline: papírový prototyp a předávací list na papíře.'],
        'glossary' => ['sitemap', 'handoff'],
    ],
    28 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 28 · Případová studie a ověření dovedností druhého ročníku',
        'goal' => [
            'student' => 'Na konci hodiny umím sestavit případovou studii (case study) svého nejlepšího projektu s důkazy rozhodnutí a podle kompetencí ročníku obhájit, co umím a co zlepším.',
            'success_criteria' => ['Studie má problém, výzkum, tři rozhodnutí s důkazem a výsledek.', 'Ke každé z osmi kompetencí ročníku mám důkaz, nebo pojmenovanou mezeru.', 'Obhajobu studie zvládnu za 3 minuty a odpovím na jednu otázku.'],
        ],
        'competencies' => [['id' => 'g2_handoff', 'level' => 3], ['id' => 'g2_visual_system', 'level' => 3]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start: osnova studie', 'teacher' => 'Ukáže osnovu studie a příklad silného důkazu.', 'student' => 'Vyberou projekt pro studii.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 40, 'phase' => 'Psaní studie', 'teacher' => 'Obchází a ptá se: „Jak víš, že to fungovalo?“', 'student' => 'Napíší studii s důkazy.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Mapa kompetencí', 'teacher' => 'Promítne osm kompetencí ročníku.', 'student' => 'Přiřadí důkazy a označí mezery.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 80, 'phase' => 'Obhajoby ve skupinách', 'teacher' => 'Hlídá limit 3 minuty a otázky.', 'student' => 'Obhájí studii a položí otázku ostatním.', 'form' => 've skupinách'],
            ['from' => 80, 'to' => 85, 'phase' => 'Cíl', 'teacher' => 'Připomene ověřitelný cíl.', 'student' => 'Zapíší cíl na další rok.', 'form' => 'jednotlivě'],
            ['from' => 85, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne rok.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš případovou studii projektu: problém, výzkum, tři rozhodnutí s důkazem a výsledek.', 'output' => 'Studie na 1–2 strany s aspoň dvěma důkazy.', 'time' => '30 min'],
            ['text' => 'Přiřaď své práce k osmi kompetencím ročníku a u chybějících důkazů napiš, proč chybí.', 'output' => 'Tabulka kompetence → důkaz → mezera.', 'time' => '15 min'],
            ['text' => 'Obhaj studii ve skupině do 3 minut a odpověz na jednu otázku spolužáka.', 'output' => 'Obhajoba a zapsaná otázka s odpovědí.', 'time' => '20 min'],
            ['text' => 'Napiš jeden ověřitelný cíl na další ročník podle největší mezery v mapě kompetencí.', 'output' => 'Cíl: co, jak poznám, do kdy.', 'time' => '5 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane osnovu studie s návodnými otázkami a seznam svých odevzdaných prací.',
            'standard' => 'Studie, mapa kompetencí, obhajoba a cíl.',
            'challenge' => 'Přidá do studie odstavec, co by udělal(a) jinak a jak by to ověřil(a).',
        ],
        'assessment' => [
            'formative' => ['Ve skupině: posluchači najdou ve studii aspoň jeden důkaz.', 'Učitel přečte cíl a třída řekne, jestli je ověřitelný.'],
            'rubric' => [
                ['criterion' => 'Studie', 'levels' => ['Jen popis.', 'Rozhodnutí bez důkazu.', 'Tři rozhodnutí s důkazem a výsledek.', 'Reflexe, co by udělal(a) jinak.']],
                ['criterion' => 'Mapa kompetencí', 'levels' => ['Chybí.', 'Částečná.', 'Osm kompetencí s důkazem nebo mezerou.', 'Mezery s plánem.']],
                ['criterion' => 'Obhajoba', 'levels' => ['Nezazněla.', 'Přes limit, bez odpovědi.', 'Do 3 minut s odpovědí.', 'Odpověď opřená o důkaz.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_handoff', 'competence_label' => 'Předání návrhu a případová studie', 'variants' => [
            ['question' => 'Co je v případové studii nejsilnější?', 'options' => ['Množství obrázků.', 'Popis použitých nástrojů.', 'Rozhodnutí doložená výzkumem nebo testem.'], 'correct' => 2, 'explanation' => 'Studie ukazuje, jak a proč ses rozhodoval(a).'],
            ['question' => 'Co uděláš s kompetencí, ke které nemáš důkaz?', 'options' => ['Pojmenuji mezeru a naplánuji, jak důkaz získám.', 'Napíšu, že ji umím.', 'Vynechám ji.'], 'correct' => 0, 'explanation' => 'Upřímná mezera je základ dalšího cíle.'],
            ['question' => 'Proč má obhajoba studie časový limit?', 'options' => ['Aby se nestihly otázky.', 'Aby autor vybral to podstatné a nechal prostor pro otázky.', 'Protože delší obhajoba je zakázaná.'], 'correct' => 1, 'explanation' => 'Limit nutí k výběru a stručnosti.'],
        ]],
        'homework' => [['text' => 'Volitelné: připni studii jako práci do svého portfolia v EDUCANETu s reflexí do 400 znaků.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Ve studii nejsou jména ani fotky testujících; citace bez osobních údajů.', 'Portfolio vidí jen žák a jeho učitelé; sdílení ven jen se souhlasem.'],
        'teacher_notes' => ['Častý omyl: studie popisuje nástroje místo rozhodnutí – ptej se „proč právě takhle?“.', 'Otázka do třídy: Který důkaz by přesvědčil budoucího zaměstnavatele?', 'Tempo: obhajoby ve skupinách po čtyřech; čas hlídá jeden ze skupiny.'],
        'substitution' => ['Zástup bez odborníka: rozdá osnovu studie a seznam kompetencí; žáci píší studii a mapu (úkoly 1, 2 a 4).', 'Plán B offline: studie na papíře, obhajoby ústně ve skupinách.'],
        'glossary' => ['case-study'],
    ],
]]];
