<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 1.A (grafika a webdesign), lekce 11–16.
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla viz lesson_content_v72_1a_a.php.
 * Kompetence: katalog v62 „grafika_web“ (pilot 1.A). Vazba na ŠVP: chybí (čeká na dokument školy).
 */

return ['class_1a' => ['lessons' => [
    11 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 11 · Responzivní základ: jedna stránka, tři šířky',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout stejný obsah pro mobil, tablet a počítač tak, aby se přeskupil podle priority, a ne jen zmenšil.',
            'success_criteria' => ['Mobilní verze 390 px řadí obsah podle priority.', 'U každé šířky zapíšu jednu změnu rozvržení a její důvod.', 'Text na počítači nepřesahuje rozumnou šířku sloupce.'],
        ],
        'competencies' => [['id' => 'web_css_layout', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže stejný web zmenšený na mobil bez úprav.', 'student' => 'Najdou tři místa, kde se obsah rozbil.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Tři šířky', 'teacher' => 'Porovná 390, 768 a 1440 px a vysvětlí mobile first.', 'student' => 'Určí, co se přeskupí, co zůstane a co se může skrýt.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 42, 'phase' => 'Mobil jako první', 'teacher' => 'Hlídá řazení podle priority, ne podle počítačové verze.', 'student' => 'Navrhnou kostru 390 px.', 'form' => 'jednotlivě'],
            ['from' => 42, 'to' => 57, 'phase' => 'Tablet', 'teacher' => 'Ukáže, kdy druhý sloupec pomáhá a kdy škodí.', 'student' => 'Přidají druhý sloupec jen tam, kde dává smysl.', 'form' => 'jednotlivě'],
            ['from' => 57, 'to' => 75, 'phase' => 'Počítač', 'teacher' => 'Připomene maximální šířku textu.', 'student' => 'Rozšíří rozvržení na 1440 px bez roztaženého textu.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Bod zlomu a exit ticket', 'teacher' => 'Vysvětlí, že bod zlomu určuje obsah, ne pevné číslo.', 'student' => 'Zapíší změny u každé šířky a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Porovnej pět sekcí stránky v šířkách 390, 768 a 1440 px a zapiš, co se přeskupí, co zůstane a co se skryje.', 'output' => 'Tabulka sekcí × šířky.', 'time' => '12 min'],
            ['text' => 'Navrhni mobilní kostru 390 px, ve které je obsah seřazený podle priority (mobile first).', 'output' => 'Mobilní kostra.', 'time' => '17 min'],
            ['text' => 'Přidej verzi pro tablet a počítač; text na počítači drž v rozumně širokém sloupci.', 'output' => 'Kostry 768 a 1440 px.', 'time' => '30 min'],
            ['text' => 'U každého bodu zlomu (breakpoint) zapiš jednu změnu rozvržení a důvod.', 'output' => 'Tři věty: změna → důvod.', 'time' => '8 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou počítačovou verzi a navrhuje jen mobil; pořadí sekcí vybírá z kartiček.',
            'standard' => 'Tři šířky podle zadání včetně důvodů změn.',
            'challenge' => 'Najde šířku mezi 390 a 768 px, kde se jeho návrh začne lámat, a navrhne vlastní bod zlomu.',
        ],
        'assessment' => [
            'formative' => ['Po mobilní kostře: každý přečte pořadí sekcí, soused řekne, zda souhlasí s prioritou.', 'Rychlá otázka: proč nestačí stránku zmenšit?'],
            'rubric' => [
                ['criterion' => 'Priorita na mobilu', 'levels' => ['Jen zmenšená verze.', 'Přeskupené, ale bez logiky.', 'Pořadí podle priority obsahu.', 'Zdůvodní pořadí potřebou uživatele.']],
                ['criterion' => 'Adaptace šířek', 'levels' => ['Jedna šířka.', 'Tři šířky, stejné rozvržení.', 'Tři šířky s vědomými změnami.', 'Vlastní bod zlomu podle obsahu.']],
                ['criterion' => 'Čitelnost textu', 'levels' => ['Text přes celou šířku.', 'Omezený jen na jedné šířce.', 'Rozumná šířka sloupce všude.', 'Ověří na náhledu a doloží.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_css_layout', 'competence_label' => 'Rozvržení a responzivita', 'variants' => [
            ['question' => 'Co znamená postup „mobile first“?', 'options' => ['Web se dělá jen pro mobily.', 'Nejdřív navrhnu nejužší verzi s nejdůležitějším obsahem a pak ji rozšiřuji.', 'Mobilní verze se navrhuje až nakonec.'], 'correct' => 1, 'explanation' => 'Úzký displej nutí rozhodnout, co je nejdůležitější.'],
            ['question' => 'Na tabletu máš dva sloupce textu po čtyřech slovech na řádek. Co uděláš?', 'options' => ['Nechám to, dva sloupce jsou moderní.', 'Zmenším písmo.', 'Vrátím jeden sloupec – druhý tu obsahu nepomáhá.'], 'correct' => 2, 'explanation' => 'Sloupec přidávám jen tam, kde obsahu pomůže.'],
            ['question' => 'Jak poznám, kde má být bod zlomu (breakpoint)?', 'options' => ['Tam, kde se obsah začne lámat nebo špatně číst.', 'Vždy přesně po 100 px.', 'Podle velikosti mého monitoru.'], 'correct' => 0, 'explanation' => 'Bod zlomu určuje obsah, ne pevná čísla.'],
        ]],
        'homework' => [['text' => 'Volitelné: otevři svůj oblíbený web na mobilu i na počítači a zapiš dvě věci, které se přeskupily.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Pracuje se s vymyšleným obsahem; nic se nepublikuje.', 'Při prohlížení cizích webů se nepřihlašuješ a nic neodesíláš.'],
        'teacher_notes' => ['Častý omyl: na mobilu skrýt polovinu obsahu. Skrývat jen to, co opravdu není potřeba.', 'Otázka do třídy: Co musí uživatel na mobilu vidět bez posouvání?', 'Tempo: tablet bývá rychlý – ušetřený čas dej verzi pro počítač.'],
        'substitution' => ['Zástup bez odborníka: žáci kreslí tři kostry na papír podle úkolů 1–4.', 'Plán B offline: tři obdélníky (390 / 768 / 1440 v poměru) na A4.'],
        'glossary' => ['mobile-first', 'breakpoint'],
    ],
    12 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 12 · Komponenty: tlačítka, karty a opakovatelná pravidla',
        'goal' => [
            'student' => 'Na konci hodiny umím popsat tlačítko a kartu jako komponentu – opakovatelný vzor s pravidly a stavy, ne jednorázový obrázek.',
            'success_criteria' => ['Tlačítko má zapsanou výšku, vnitřní okraj, zaoblení a styl textu.', 'Navrhnu stavy výchozí, fokus a nedostupné, které nejsou odlišené jen barvou.', 'Karta používá stejnou škálu mezer jako tlačítko.'],
        ],
        'competencies' => [['id' => 'web_ux', 'level' => 2], ['id' => 'web_a11y', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže web s pěti různě vypadajícími tlačítky pro stejnou akci.', 'student' => 'Řeknou, proč je to matoucí.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Co je komponenta', 'teacher' => 'Vysvětlí rozdíl jednorázového bloku a komponenty.', 'student' => 'Najdou na ukázce tři opakované komponenty.', 'form' => 've dvojicích'],
            ['from' => 22, 'to' => 40, 'phase' => 'Tlačítka', 'teacher' => 'Ukáže hlavní a vedlejší tlačítko.', 'student' => 'Navrhnou obě tlačítka se stejnými rozměry.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Stavy', 'teacher' => 'Předvede ovládání klávesnicí a viditelný fokus.', 'student' => 'Doplní stavy výchozí, najetí (hover), fokus a nedostupné.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Karta', 'teacher' => 'Hlídá stejné mezery jako u tlačítka.', 'student' => 'Postaví kartu s názvem, popisem a akcí a použijí ji třikrát.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Spustí test: poznáš stav bez barev?', 'student' => 'Převedou návrh do odstínů šedi a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Najdi na ukázkové stránce tři komponenty, které se opakují, a pojmenuj je podle funkce.', 'output' => 'Tři pojmenované komponenty.', 'time' => '10 min'],
            ['text' => 'Navrhni hlavní a vedlejší tlačítko a zapiš výšku, vnitřní okraj (padding), zaoblení a styl textu.', 'output' => 'Specifikace tlačítka.', 'time' => '18 min'],
            ['text' => 'Doplň stavy výchozí, najetí (hover), fokus (focus) a nedostupné; stav nesmí poznat jen podle barvy.', 'output' => 'Řádek čtyř stavů.', 'time' => '15 min'],
            ['text' => 'Postav kartu s názvem, popisem a akcí a použij ji třikrát s různým obsahem.', 'output' => 'Tři karty se shodnou strukturou.', 'time' => '23 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane rozměry tlačítka předem; doplňuje jen stavy a skládá karty z hotových částí.',
            'standard' => 'Vlastní specifikace tlačítka, stavy a karta podle zadání.',
            'challenge' => 'Přidá variantu karty bez obrázku a ověří, že se struktura nerozpadne.',
        ],
        'assessment' => [
            'formative' => ['Test v šedi: soused pozná fokus a nedostupný stav bez barev?', 'Rychlé ověření: mají všechny tři karty stejné mezery?'],
            'rubric' => [
                ['criterion' => 'Pravidla tlačítka', 'levels' => ['Bez zapsaných hodnot.', 'Část hodnot zapsaná.', 'Výška, okraj, zaoblení a text zapsané.', 'Pravidla použije i pro nové tlačítko.']],
                ['criterion' => 'Stavy', 'levels' => ['Jen výchozí stav.', 'Stavy odlišené jen barvou.', 'Čtyři stavy čitelné i v šedi.', 'Vysvětlí, k čemu je fokus při ovládání klávesnicí.']],
                ['criterion' => 'Opakovatelnost karty', 'levels' => ['Každá karta jiná.', 'Podobné, nesouhlasí mezery.', 'Tři karty se stejnou strukturou.', 'Karta obstojí i bez obrázku.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_ux', 'competence_label' => 'Ovládání a komponenty', 'variants' => [
            ['question' => 'K čemu slouží viditelný stav fokus u tlačítka?', 'options' => ['Ukazuje, kde právě je ovládání z klávesnice.', 'Je to jen ozdoba při najetí myší.', 'Označuje tlačítko, které nefunguje.'], 'correct' => 0, 'explanation' => 'Bez fokusu se uživatel klávesnice ztratí.'],
            ['question' => 'Proč má mít každá karta na webu stejné vnitřní mezery?', 'options' => ['Aby se ušetřila paměť.', 'Aby web působil uspořádaně a změna šla udělat na jednom místě.', 'Na mezerách nezáleží.'], 'correct' => 1, 'explanation' => 'Komponenta = jedno pravidlo použité mnohokrát.'],
            ['question' => 'Jak odlišíš nedostupné tlačítko, aby to poznal i člověk, který nerozliší barvy?', 'options' => ['Jen šedou barvou.', 'Jen menším písmem.', 'Změnou kontrastu i textem nebo ikonou, proč akce nejde.'], 'correct' => 2, 'explanation' => 'Význam nesmí nést jen barva.'],
        ]],
        'homework' => [['text' => 'Volitelné: na jednom webu najdi tlačítko v různých stavech (najetí, fokus po Tabu) a zapiš, čím se liší.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Ukázkové weby se jen prohlížejí, nic se neodesílá.', 'Do karet se nepíšou skutečné osobní údaje.'],
        'teacher_notes' => ['Častý omyl: fokus = hover. Nech žáky projít stránku klávesou Tab.', 'Otázka do třídy: Co se stane, když budeme chtít změnit všechna tlačítka najednou?', 'Tempo: neřeš velký design systém – cílem je opakovatelnost dvou komponent.'],
        'substitution' => ['Zástup bez odborníka: žáci plní úkoly 1–4 podle lekce, výstupem je obrázek tlačítek a karet.', 'Plán B offline: komponenty jako papírové výstřižky, stavy fixami.'],
        'glossary' => ['hover', 'focus', 'padding'],
    ],
    13 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 13 · Webová typografie: čitelnost, délka řádku a rytmus',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout typografii pro obrazovku: pět textových rolí, čitelný odstavec a opakovaný rytmus mezer.',
            'success_criteria' => ['Pět rolí (velký nadpis, nadpis, text, drobný text, text akce) má každá svůj účel.', 'Odstavec má zapsanou velikost, řádkování a maximální šířku.', 'Nadpis se na šířce 390 px láme čitelně.'],
        ],
        'competencies' => [['id' => 'gfx_typography', 'level' => 2], ['id' => 'web_css_layout', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže stejný článek jako hustý blok a jako upravený text.', 'student' => 'Odhadnou, který se čte rychleji a proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Textové role', 'teacher' => 'Vysvětlí role a jejich účel.', 'student' => 'Definují pět rolí a k čemu slouží.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Odstavec', 'teacher' => 'Ukáže vliv délky řádku a řádkování.', 'student' => 'Nastaví odstavci velikost, řádkování a maximální šířku.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Škála velikostí', 'teacher' => 'Ukáže malou škálu místo deseti náhodných velikostí.', 'student' => 'Sestaví škálu a otestují nadpis na 390 px.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 78, 'phase' => 'Rytmus na stránce', 'teacher' => 'Hlídá stejné mezery mezi nadpisem, textem a akcí.', 'student' => 'Použijí role a mezery na článku se třemi sekcemi.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Test skenování a exit ticket', 'teacher' => 'Spustí test: pochopíš stránku jen z nadpisů?', 'student' => 'Vyzkouší test u souseda a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Definuj pět textových rolí a ke každé napiš, k čemu na stránce slouží.', 'output' => 'Tabulka rolí a účelů.', 'time' => '13 min'],
            ['text' => 'Pro odstavec zapiš velikost písma, řádkování a maximální šířku sloupce.', 'output' => 'Tři hodnoty odstavce + ukázka.', 'time' => '13 min'],
            ['text' => 'Sestav malou škálu velikostí a ověř, že se velký nadpis na šířce 390 px láme čitelně.', 'output' => 'Škála + náhled nadpisu na mobilu.', 'time' => '13 min'],
            ['text' => 'Nasaď role a mezery na článek se třemi sekcemi a jednou akcí.', 'output' => 'Hotová stránka článku.', 'time' => '22 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou škálu velikostí; přiřazuje role a ladí jen řádkování a šířku.',
            'standard' => 'Vlastní role, škála a stránka článku.',
            'challenge' => 'Porovná dvě písma pro text a zdůvodní volbu podle čitelnosti na mobilu.',
        ],
        'assessment' => [
            'formative' => ['Ukaž roli: učitel přečte účel, žáci ukážou odpovídající text na své stránce.', 'Test skenování: soused z nadpisů řekne, o čem článek je.'],
            'rubric' => [
                ['criterion' => 'Role a účel', 'levels' => ['Role chybí.', 'Role bez účelu.', 'Pět rolí s účelem.', 'Role popíše tak, aby je použil kdokoli z týmu.']],
                ['criterion' => 'Odstavec', 'levels' => ['Bez úprav.', 'Upravená jedna hodnota.', 'Velikost, řádkování i šířka zapsané.', 'Ověřeno na mobilu i počítači.']],
                ['criterion' => 'Rytmus', 'levels' => ['Mezery náhodné.', 'Rytmus jen v části.', 'Mezery se opakují v celé stránce.', 'Stránka projde testem skenování.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_typography', 'competence_label' => 'Typografie', 'variants' => [
            ['question' => 'Které nastavení nejvíc pomůže čtení dlouhého textu na webu?', 'options' => ['Omezená šířka sloupce a dostatečné řádkování.', 'Text psaný celý velkými písmeny.', 'Co nejmenší písmo, aby se vešlo víc.'], 'correct' => 0, 'explanation' => 'Krátký řádek a vzduch mezi řádky usnadňují čtení.'],
            ['question' => 'Velký nadpis se na mobilu láme na pět řádků po jednom slově. Co uděláš?', 'options' => ['Nechám to, na mobilu je to normální.', 'Pro mobil zmenším roli nadpisu podle škály.', 'Nadpis smažu.'], 'correct' => 1, 'explanation' => 'Role mohou mít pro mobil vlastní velikost ze škály.'],
            ['question' => 'Co je „textová role“?', 'options' => ['Jméno písma.', 'Barva textu.', 'Pojmenované použití textu s pevnými pravidly, např. nadpis sekce.'], 'correct' => 2, 'explanation' => 'Role říká, k čemu text slouží a jak vypadá.'],
        ]],
        'homework' => [['text' => 'Volitelné: na webu zpráv změř (odhadni), kolik slov má jeden řádek článku, a porovnej s naší doporučenou šířkou.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Písma jen s licencí pro web; zdroj zapiš.', 'Testovací text je vymyšlený nebo s uvedeným zdrojem.'],
        'teacher_notes' => ['Častý omyl: honba za „správným číslem“. Vysvětluj role a důvody, ne magická čísla.', 'Otázka do třídy: Dá se stránka pochopit bez barev, jen z písma?', 'Tempo: úkol 4 je hlavní výstup – nedovol, aby škála zabrala víc než 15 minut.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem je obrázek stránky.', 'Plán B offline: role nakreslené na papír, odstavec přepsaný do úzkého a širokého sloupce.'],
        'glossary' => [],
    ],
    14 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 14 · Obraz pro web: ořez, rozměr a export',
        'goal' => [
            'student' => 'Na konci hodiny umím připravit obrázek pro web: zvolit jeho úlohu, ořez pro počítač i mobil, přiměřený rozměr exportu a alternativní text.',
            'success_criteria' => ['Určím, zda obrázek vysvětluje, dokumentuje, nebo jen zdobí.', 'Připravím ořez pro počítač i mobil, který nezakryje důležitý detail.', 'Napíšu alternativní text, nebo zdůvodním, že obrázek je jen dekorace.'],
        ],
        'competencies' => [['id' => 'gfx_formats', 'level' => 2], ['id' => 'web_a11y', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže web, který se načítá pomalu kvůli obřímu obrázku.', 'student' => 'Odhadnou, proč stránka čeká.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Úloha obrázku', 'teacher' => 'Vysvětlí tři úlohy: vysvětluje, dokumentuje, zdobí.', 'student' => 'Zařadí pět ukázkových obrázků.', 'form' => 've dvojicích'],
            ['from' => 22, 'to' => 40, 'phase' => 'Ořez pro dvě šířky', 'teacher' => 'Ukáže ořez úvodní fotky pro počítač a mobil.', 'student' => 'Připraví dva ořezy jedné fotky.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 58, 'phase' => 'Export', 'teacher' => 'Vysvětlí zdrojový soubor a webový export a přiměřený rozměr.', 'student' => 'Exportují obrázek v rozměru podle použití.', 'form' => 'jednotlivě'],
            ['from' => 58, 'to' => 78, 'phase' => 'Alternativní text', 'teacher' => 'Ukáže dobrý a špatný alt text.', 'student' => 'Napíšou alt text nebo zdůvodní dekoraci.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Projde kontrolní seznam obrázku.', 'student' => 'Zkontrolují export mimo editor a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zařaď pět ukázkových obrázků podle úlohy: vysvětluje, dokumentuje, nebo jen zdobí.', 'output' => 'Pět obrázků s úlohou.', 'time' => '12 min'],
            ['text' => 'Připrav ořez (crop) jedné fotky pro počítač a pro mobil tak, aby nezakryl důležitý detail.', 'output' => 'Dva ořezy.', 'time' => '18 min'],
            ['text' => 'Exportuj obrázek v rozměru podle místa použití a zapiš cílovou šířku v pixelech.', 'output' => 'Export + zapsaný rozměr.', 'time' => '15 min'],
            ['text' => 'Napiš alternativní text (alt text) pro informační obrázek nebo zdůvodni, proč je obrázek jen dekorace.', 'output' => 'Alt text nebo zdůvodnění.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane fotku s vyznačeným ohniskem a šablonu alt textu „Co je na obrázku + proč to tu je“.',
            'standard' => 'Vlastní ořezy, export a alt text podle zadání.',
            'challenge' => 'Porovná velikost souboru dvou exportů (různá kvalita) a vybere přiměřený s jedním argumentem.',
        ],
        'assessment' => [
            'formative' => ['Po zařazení: učitel ukáže obrázek, třída zvedne 1/2/3 prsty podle úlohy.', 'Alt text nahlas: soused bez obrázku řekne, co si představil.'],
            'rubric' => [
                ['criterion' => 'Úloha obrázku', 'levels' => ['Nerozliší úlohy.', 'Zařadí s chybami.', 'Správně zařadí a zdůvodní.', 'Navrhne vypustit zbytečnou dekoraci.']],
                ['criterion' => 'Ořez a export', 'levels' => ['Originál bez úprav.', 'Ořez bez ohledu na detail.', 'Dva ořezy a přiměřený rozměr.', 'Porovná velikost souborů a vybere rozumně.']],
                ['criterion' => 'Alternativní text', 'levels' => ['Chybí.', 'Jen „obrázek“ nebo název souboru.', 'Popisuje obsah a účel.', 'Rozliší informační obrázek a dekoraci.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_formats', 'competence_label' => 'Formáty a export', 'variants' => [
            ['question' => 'Který alternativní text je pro graf návštěvnosti nejlepší?', 'options' => ['obrazek1.png', 'Graf návštěvnosti: v září vzrostla o třetinu proti srpnu.', 'Graf'], 'correct' => 1, 'explanation' => 'Alt text předává informaci, kterou obrázek nese.'],
            ['question' => 'Obrázek bude na webu široký nejvýš 800 px. Jak ho exportuješ?', 'options' => ['V rozměru blízkém použití, ne v plném rozlišení fotoaparátu.', 'V plném rozlišení, ať je ostrý.', 'Jako snímek obrazovky.'], 'correct' => 0, 'explanation' => 'Přiměřený rozměr = rychlejší stránka.'],
            ['question' => 'Fotka je na webu čistě dekorativní. Co s alternativním textem?', 'options' => ['Napíšu dlouhý popis každého detailu.', 'Do alt textu dám klíčová slova.', 'Označím ji jako dekoraci (prázdný alt), aby ji čtečka přeskočila.'], 'correct' => 2, 'explanation' => 'Dekorace nemá rušit uživatele čtečky.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť jednu vlastní fotku a napiš k ní alt text do 15 slov.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Fotky vlastní nebo s licencí; fotky lidí jen se souhlasem.', 'Před sdílením fotky zkontroluj, že neprozrazuje polohu ani osobní údaje.'],
        'teacher_notes' => ['Neuč pevné limity v kB bez kontextu – uč přiměřenost podle použití.', 'Otázka do třídy: Co ztratí uživatel čtečky, když alt text chybí?', 'Tempo: export bývá technicky zdlouhavý – připrav si postup v editoru na tabuli.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 4 jdou na papíře, úkoly 2–3 podle učebny.', 'Plán B offline: ořez rámečkem z papíru na vytištěné fotce, alt text písemně.'],
        'glossary' => ['crop', 'alt-text'],
    ],
    15 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 15 · Formuláře a přístupnost: stav musí být pochopitelný',
        'goal' => [
            'student' => 'Na konci hodiny umím navrhnout jednoduchý formulář s popisky, viditelným fokusem, srozumitelnou chybou a potvrzením odeslání.',
            'success_criteria' => ['Každé pole má trvalý popisek, ne jen zástupný text v poli.', 'Chyba je vyjádřená textem i ikonou a říká, jak ji opravit.', 'Formulář je pochopitelný i v odstínech šedi.'],
        ],
        'competencies' => [['id' => 'web_a11y', 'level' => 2], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže formulář, kde po kliknutí zmizí nápověda a chyba je jen červená.', 'student' => 'Zkusí odhadnout, co je špatně.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Části formuláře', 'teacher' => 'Pojmenuje popisek, pole, nápovědu, chybu a odeslání.', 'student' => 'Označí části na ukázce.', 'form' => 've dvojicích'],
            ['from' => 22, 'to' => 38, 'phase' => 'Výchozí stav a fokus', 'teacher' => 'Předvede průchod formulářem klávesou Tab.', 'student' => 'Navrhnou pole ve výchozím stavu a s fokusem.', 'form' => 'jednotlivě'],
            ['from' => 38, 'to' => 55, 'phase' => 'Chyba', 'teacher' => 'Ukáže chybovou hlášku „co se stalo + jak to opravit“.', 'student' => 'Navrhnou chybový stav a napíšou hlášku.', 'form' => 'jednotlivě'],
            ['from' => 55, 'to' => 75, 'phase' => 'Odesílání a úspěch', 'teacher' => 'Vysvětlí stav „odesílám“ a potvrzení.', 'student' => 'Navrhnou tlačítko při odesílání a potvrzení.', 'form' => 'jednotlivě'],
            ['from' => 75, 'to' => 90, 'phase' => 'Test v šedi a exit ticket', 'teacher' => 'Nechá převést návrhy do šedi.', 'student' => 'Ověří, že stavy jsou pochopitelné, a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Na ukázce označ popisek (label), pole, nápovědu, chybovou hlášku a tlačítko odeslání.', 'output' => 'Popsaný formulář.', 'time' => '10 min'],
            ['text' => 'Navrhni pole e-mailu ve stavech výchozí, fokus, chyba a vyplněno správně.', 'output' => 'Řádek čtyř stavů pole.', 'time' => '25 min'],
            ['text' => 'Napiš chybovou hlášku, která říká, co se stalo a jak to opravit, a umísti ji u pole.', 'output' => 'Text hlášky + umístění.', 'time' => '10 min'],
            ['text' => 'Navrhni tlačítko ve stavu „odesílám“ a obrazovku potvrzení; celé to zkontroluj v odstínech šedi.', 'output' => 'Stav odesílání, potvrzení a test v šedi.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu pole a vzorovou hlášku; upravuje ji pro jiné pole (telefon).',
            'standard' => 'Čtyři stavy pole, vlastní hláška, odesílání a potvrzení.',
            'challenge' => 'Přidá souhrn chyb nahoře formuláře s odkazy na chybná pole a vysvětlí, komu pomáhá.',
        ],
        'assessment' => [
            'formative' => ['Tab test: učitel projde jeden návrh „klávesnicí“ – je vidět, kde jsem?', 'Hláška nahlas: třída řekne, zda ví, co opravit.'],
            'rubric' => [
                ['criterion' => 'Popisky a struktura', 'levels' => ['Jen zástupný text.', 'Popisky u části polí.', 'Trvalé popisky u všech polí.', 'Doplněná nápověda tam, kde je potřeba.']],
                ['criterion' => 'Chybový stav', 'levels' => ['Jen červený rámeček.', 'Text bez návodu.', 'Text + ikona + jak opravit.', 'Souhrn chyb s odkazy.']],
                ['criterion' => 'Stavy a přístupnost', 'levels' => ['Jen výchozí stav.', 'Stavy jen barvou.', 'Fokus, chyba, odesílání, úspěch čitelné v šedi.', 'Vysvětlí, komu které řešení pomáhá.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_a11y', 'competence_label' => 'Přístupnost webu', 'variants' => [
            ['question' => 'Proč nestačí zástupný text (placeholder) místo popisku pole?', 'options' => ['Po začátku psaní zmizí a uživatel neví, co pole chtělo.', 'Zástupný text nejde obarvit.', 'Prohlížeče ho nezobrazují.'], 'correct' => 0, 'explanation' => 'Popisek musí zůstat vidět celou dobu.'],
            ['question' => 'Která chybová hláška je nejlepší?', 'options' => ['Chyba 12.', 'Neplatný vstup.', 'E-mail musí obsahovat zavináč, například jana@example.com.'], 'correct' => 2, 'explanation' => 'Hláška říká, co je špatně a jak to opravit.'],
            ['question' => 'Co má udělat tlačítko po kliknutí na „Odeslat“, než přijde odpověď?', 'options' => ['Zmizet beze stopy.', 'Ukázat stav „Odesílám…“ a nepovolit druhé kliknutí.', 'Nic, uživatel počká.'], 'correct' => 1, 'explanation' => 'Uživatel má vědět, že se něco děje.'],
        ]],
        'homework' => [['text' => 'Volitelné: na jednom webovém formuláři zkus projít pole jen klávesou Tab a zapiš, zda bylo vidět, kde jsi.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Do žádného skutečného formuláře nic neodesíláme; ukázkové údaje jsou vymyšlené.', 'Vzorové e-maily jen ve tvaru jméno@example.com (rezervovaná ukázková doména) s vymyšleným jménem.'],
        'teacher_notes' => ['Fokus je designový stav, ne technická poznámka – trvej na něm.', 'Otázka do třídy: Pozná chybu člověk, který nerozliší červenou?', 'Tempo: čtyři stavy pole jsou jádro hodiny – potvrzení může být jednodušší.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1–4 podle lekce; test v šedi lze udělat i černobílým tiskem.', 'Plán B offline: stavy pole kreslené tužkou, chyba fixou + ikona.'],
        'glossary' => ['label', 'placeholder'],
    ],
    16 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 16 · Úvodní stránka: drátěný model od zadání k průchodu',
        'goal' => [
            'student' => 'Na konci hodiny umím převést krátké zadání na strukturu úvodní stránky (landing page) a obhájit pořadí sekcí dřív, než řeším vzhled.',
            'success_criteria' => ['Zadání shrnu do čtyř vět: pro koho, jaký problém, co nabízím, jaká je hlavní akce.', 'Sekce mají pořadí, u každé napíšu otázku, na kterou odpovídá.', 'Po testu se spolužákem udělám aspoň jednu změnu podle pozorování.'],
        ],
        'competencies' => [['id' => 'web_ux', 'level' => 3], ['id' => 'web_html_structure', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Představí zadání vymyšleného školního kroužku.', 'student' => 'Řeknou, co má návštěvník po přečtení udělat.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Zadání ve čtyřech větách', 'teacher' => 'Ukáže vzor: pro koho, problém, nabídka, akce.', 'student' => 'Napíšou zadání ve čtyřech větách.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 35, 'phase' => 'Soupis obsahu', 'teacher' => 'Pomáhá vyškrtnout ozdobný text bez funkce.', 'student' => 'Sepíšou, co stránka musí obsahovat.', 'form' => 'jednotlivě'],
            ['from' => 35, 'to' => 50, 'phase' => 'Pořadí sekcí', 'teacher' => 'Ukáže vzor úvod → důvěra → přínos → detail → akce.', 'student' => 'Seřadí sekce a připíšou otázky.', 'form' => 've dvojicích'],
            ['from' => 50, 'to' => 72, 'phase' => 'Drátěný model', 'teacher' => 'Hlídá, aby nevznikaly finální barvy a fotky.', 'student' => 'Nakreslí wireframe pro počítač a mobil.', 'form' => 'jednotlivě'],
            ['from' => 72, 'to' => 90, 'phase' => 'Test průchodu a exit ticket', 'teacher' => 'Řídí 3minutový test ve dvojicích.', 'student' => 'Spolužák popíše, co by udělal první; zapíší změnu a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Napiš zadání ve čtyřech větách: pro koho stránka je, jaký problém řeší, co nabízí a jaká je jediná hlavní akce.', 'output' => 'Zadání ve 4 větách.', 'time' => '12 min'],
            ['text' => 'Sepiš obsah, který stránka musí mít, a škrtni duplicity a ozdobný text bez funkce.', 'output' => 'Soupis obsahu.', 'time' => '13 min'],
            ['text' => 'Seřaď 5–7 sekcí a u každé napiš otázku návštěvníka, na kterou odpovídá.', 'output' => 'Pořadí sekcí s otázkami.', 'time' => '15 min'],
            ['text' => 'Nakresli drátěný model (wireframe) pro počítač i mobil a po testu se spolužákem udělej jednu změnu.', 'output' => 'Dva wireframy + zapsaná změna a důvod.', 'time' => '35 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kartičky sekcí a vzorové otázky; skládá pořadí a kreslí jen počítačovou verzi.',
            'standard' => 'Zadání, soupis, pořadí a dva wireframy podle zadání.',
            'challenge' => 'Navrhne druhé pořadí sekcí pro jinou cílovou skupinu (rodiče místo žáků) a porovná je.',
        ],
        'assessment' => [
            'formative' => ['Po pořadí sekcí: dvojice si přečtou otázky – odpovídá sekce opravdu na svou otázku?', 'Test průchodu: co by návštěvník udělal jako první?'],
            'rubric' => [
                ['criterion' => 'Zadání', 'levels' => ['Chybí cíl.', 'Cíl je, chybí cílová skupina nebo akce.', 'Čtyři věty včetně hlavní akce.', 'Zadání je tak jasné, že podle něj pracuje i spolužák.']],
                ['criterion' => 'Struktura a pořadí', 'levels' => ['Náhodné sekce.', 'Sekce bez otázek.', 'Pořadí s otázkami návštěvníka.', 'Pořadí obhájí proti alternativě.']],
                ['criterion' => 'Test a iterace', 'levels' => ['Bez testu.', 'Test bez záznamu.', 'Zapsaná změna podle pozorování.', 'Odliší pozorování od názoru spolužáka.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_ux', 'competence_label' => 'Ovládání a komponenty', 'variants' => [
            ['question' => 'Při testu průchodu se spolužáka zeptáš:', 'options' => ['„Líbí se ti to?“', '„Co bys tady udělal jako první?“', '„Vidíš to modré tlačítko vpravo?“'], 'correct' => 1, 'explanation' => 'Ptáme se na chování, ne na názor, a nenavádíme.'],
            ['question' => 'K čemu slouží sekce „důvěra“ na úvodní stránce?', 'options' => ['Dodává důkazy, proč nabídce věřit (zkušenosti, čísla, ukázky).', 'Je tam jen logo školy.', 'Obsahuje právní podmínky.'], 'correct' => 0, 'explanation' => 'Důvěra odpovídá na otázku „proč zrovna tohle?“.'],
            ['question' => 'Kdy je správný čas vybírat finální fotky a barvy?', 'options' => ['Hned na začátku, aby se dobře pracovalo.', 'Během psaní zadání.', 'Až po ověření struktury a průchodu.'], 'correct' => 2, 'explanation' => 'Nejdřív struktura, pak styl.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi jednu úvodní stránku (např. kroužku nebo akce) a vypiš pořadí jejích sekcí.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Zadání je vymyšlené; na stránku se nepíší skutečné kontakty ani jména.', 'Test průchodu probíhá jen ve třídě, bez nahrávání.'],
        'teacher_notes' => ['Nechte studenty testovat průchod bez vizuálního efektu – wow efekt maskuje problémy.', 'Otázka do třídy: Na jakou otázku návštěvníka odpovídá tahle sekce?', 'Tempo: wireframe nesmí sebrat čas testu – v 72. minutě přejdi k testu.'],
        'substitution' => ['Zástup bez odborníka: rozdá zadání, žáci plní úkoly 1–4 na papír, test proběhne ve dvojicích.', 'Plán B offline: celá hodina na papíře s kartičkami sekcí.'],
        'glossary' => ['landing-page', 'wireframe'],
    ],
]]];
