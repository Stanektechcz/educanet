<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · obsahová stopa, vlna 1 – 1.A (grafika a webdesign), lekce 5–10.
 * STAV: NÁVRH. Žák uvidí obsah až po schválení učitelem (lesson_approval_v72.php); do té doby vidí původní lekci.
 * Tvar podle modelu lm71 (lesson_model_v71.php, lm71_overlay_merge): cíl + 2–4 kritéria, plán 90 min, úkoly,
 * diferenciace, formativní kontrola a rubrika 3–5 × 1–4, exit ticket (≥ 3 varianty, jiné než kvíz lekce),
 * bezpečnost, poznámky, zástup, volitelná domácí příprava (limit LC72_HOMEWORK_MAX_MIN) a glosář (lesson_glossary_v72.php).
 * Vychází z témat původních lekcí (lessons_plus.php, lessons_more.php, lessons_ecosystem.php, lessons_yearpack.php); texty jsou vlastní.
 * Kompetence: katalog v62 „grafika_web“ (pilot 1.A). Vazba na ŠVP: chybí (čeká na dokument školy).
 */

return ['class_1a' => ['lessons' => [
    5 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 5 · Paleta, ikony a konzistence',
        'goal' => [
            'student' => 'Na konci hodiny umím postavit malý vizuální systém – paletu se třemi rolemi, jednu rodinu ikon a pravidlo ořezu – a použít ho na informační kartě.',
            'success_criteria' => ['Paleta má pojmenované role (pozadí, text, akcent) a zapsané HEX kódy.', 'Tři ikony mají stejný styl kresby i stejnou optickou velikost.', 'Karta 1080 × 1350 px se čte v pořadí nadpis → informace → akce.'],
        ],
        'competencies' => [['id' => 'gfx_color_contrast', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start a cíl', 'teacher' => 'Ukáže dvě karty se stejným obsahem (sjednocenou a chaotickou) a nechá třídu hlasovat.', 'student' => 'Řeknou, která karta je čitelnější a proč.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Paleta s rolemi', 'teacher' => 'Předvede volbu barvy pozadí, textu a akcentu a kontrolu kontrastu.', 'student' => 'V Color Harmony Lab zvolí paletu a zapíší HEX kódy rolí.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Jedna rodina ikon', 'teacher' => 'Ukáže rozdíl obrysových a vyplněných ikon a tloušťky čáry.', 'student' => 'Vyberou 3 ikony z jedné sady a sjednotí jejich velikost.', 'form' => 've dvojicích'],
            ['from' => 40, 'to' => 52, 'phase' => 'Ohnisko a ořez', 'teacher' => 'Na jedné fotce ukáže dva ořezy a místo pro text.', 'student' => 'Připraví dva ořezy a vyberou lepší.', 'form' => 'jednotlivě'],
            ['from' => 52, 'to' => 80, 'phase' => 'Realizace karty', 'teacher' => 'Obchází, ptá se „co uvidím první?“, hlídá limit 3 ikon.', 'student' => 'Složí kartu v Canvě nebo Figmě a vyexportují ji.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Kontrola a exit ticket', 'teacher' => 'Spustí párovou kontrolu ikon a exit ticket.', 'student' => 'Najdou u souseda ikonu, která vybočuje, a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Zvol paletu se třemi rolemi (pozadí, text, akcent) a zapiš jejich HEX kódy.', 'output' => 'Tabulka rolí s HEX kódy a poznámkou o kontrastu textu.', 'time' => '15 min'],
            ['text' => 'Vyber 3 ikony z jedné sady a sjednoť jejich optickou velikost a tloušťku čáry.', 'output' => 'Řádek tří ikon ve stejném stylu.', 'time' => '15 min'],
            ['text' => 'Připrav dva ořezy (crop) fotografie a vyber ten, který nechává klidné místo pro text.', 'output' => 'Dva ořezy a jedna věta, proč vítězí ten vybraný.', 'time' => '12 min'],
            ['text' => 'Slož informační kartu 1080 × 1350 px s nejvýš třemi ikonami a jedním akcentem.', 'output' => 'Export PNG a uložený zdrojový soubor.', 'time' => '28 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane hotovou paletu a sadu šesti ikon; vybírá z nich a skládá kartu do připravené mřížky.',
            'standard' => 'Vlastní paleta, ikony z jedné bezplatné sady s licencí a karta podle zadání.',
            'challenge' => 'Vytvoří druhou variantu karty v tmavém provedení se stejnými rolemi barev a ověří kontrast obou.',
        ],
        'assessment' => [
            'formative' => ['Po paletě: každý ukáže HEX akcentu a řekne, k čemu ho použije.', 'Párová kontrola: soused najde ikonu, která stylem vybočuje.'],
            'rubric' => [
                ['criterion' => 'Role barev', 'levels' => ['Barvy nemají role.', 'Role jsou určené, kontrast neověřený.', 'Tři role s HEX kódy a ověřeným kontrastem.', 'Role zdůvodní a udrží je i v druhé variantě.']],
                ['criterion' => 'Konzistence ikon', 'levels' => ['Ikony z různých sad.', 'Jedna sada, nesourodé velikosti.', 'Stejný styl i optická velikost.', 'Vysvětlí pravidlo a pohlídá ho i u nové ikony.']],
                ['criterion' => 'Pořadí čtení', 'levels' => ['Není jasné, co číst první.', 'Nadpis vyniká, ostatní prvky soupeří.', 'Nadpis → informace → akce.', 'Obstojí v testu tří sekund u spolužáka.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_color_contrast', 'competence_label' => 'Barvy a kontrast', 'variants' => [
            ['question' => 'K čemu v paletě slouží barva s rolí „akcent“?', 'options' => ['Na veškerý běžný text, aby byl výrazný.', 'Na jednu věc, kterou má divák najít jako první, třeba tlačítko.', 'Na pozadí celé karty.'], 'correct' => 1, 'explanation' => 'Akcent upozorňuje jen tehdy, když je ho málo.'],
            ['question' => 'Máš dvě obrysové ikony a jednu vyplněnou. Co uděláš?', 'options' => ['Nechám je, rozmanitost je zajímavá.', 'Vyplněnou zvětším, ať je vidět.', 'Vyplněnou vyměním za obrysovou ze stejné sady.'], 'correct' => 2, 'explanation' => 'Jedna rodina ikon = stejný styl kresby.'],
            ['question' => 'Kdy je ořez fotografie pro informační kartu dobrý?', 'options' => ['Když ohnisko zůstane vidět a zbude klidné místo pro text.', 'Když je na fotce co nejvíc detailů.', 'Když text vede přes obličej, aby byl uprostřed.'], 'correct' => 0, 'explanation' => 'Ořez slouží sdělení: ohnisko + prostor pro text.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi kolem sebe jeden plakát nebo obal a zapiš jeho tři barvy s rolemi (pozadí, text, akcent).', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Ikony a fotky jen vlastní nebo z bezplatných zdrojů s licencí; zdroj zapiš do poznámky.', 'Na kartě nejsou jména ani fotky spolužáků.'],
        'teacher_notes' => ['Častý omyl: akcentní barva na všem. Ptej se „co má divák najít jako první?“.', 'Otázka do třídy: Kdy přestane akcent upozorňovat?', 'Tempo: realizace se protahuje – v 80. minutě ukonči práci, nedokončené karty se dodělají příště.'],
        'substitution' => ['Zástup bez odborníka: ukáže dvě karty z úvodu, žáci plní úkoly 1–4 a odevzdají export.', 'Plán B offline: paleta pastelkami, ikony obkreslením a karta jako skica na papír A5.'],
        'glossary' => ['hex', 'crop'],
    ],
    6 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 6 · Redesign: z chaosu na systém',
        'goal' => [
            'student' => 'Na konci hodiny umím najít skutečné problémy slabého plakátu, navrhnout redesign a obhájit každé rozhodnutí funkcí, ne vkusem.',
            'success_criteria' => ['Pojmenuji tři konkrétní problémy a jejich dopad na diváka.', 'Drátěný model (wireframe) určuje první, druhou a třetí informaci.', 'Porovnání před/po ukazuje lepší hierarchii a splněný cíl zadání.'],
        ],
        'competencies' => [['id' => 'gfx_composition', 'level' => 2], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Diagnostika', 'teacher' => 'Promítne slabý plakát a zadá: hledáme problémy funkce, ne vkusu.', 'student' => 'Zapíší tři problémy a dopad na diváka.', 'form' => 've dvojicích'],
            ['from' => 12, 'to' => 27, 'phase' => 'Plán redesignu', 'teacher' => 'Ukáže, jak seřadit informace podle důležitosti.', 'student' => 'Určí 1., 2. a 3. informaci, paletu a obrazový princip.', 'form' => 'jednotlivě'],
            ['from' => 27, 'to' => 45, 'phase' => 'Drátěný model', 'teacher' => 'Připomene mřížku (grid) a výzvu k akci (CTA).', 'student' => 'Nakreslí wireframe bez dekorací a ověří mřížku.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 70, 'phase' => 'Realizace', 'teacher' => 'Hlídá, aby nepřibýval text, který zadání nepotřebuje.', 'student' => 'Realizují redesign v Canvě nebo Figmě.', 'form' => 'jednotlivě'],
            ['from' => 70, 'to' => 82, 'phase' => 'Před a po', 'teacher' => 'Řídí krátkou obhajobu ve dvojicích.', 'student' => 'Obhájí 2 rozhodnutí: problém → změna → efekt.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Kontrola exportu a exit ticket', 'teacher' => 'Připomene kontrolu exportu mimo editor.', 'student' => 'Otevřou export mimo editor a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Najdi na slabém plakátu tři konkrétní problémy a ke každému napiš, co způsobí divákovi.', 'output' => 'Seznam: problém → dopad (3 řádky).', 'time' => '12 min'],
            ['text' => 'Seřaď informace plakátu na první, druhou a třetí a zvol paletu.', 'output' => 'Pořadí informací a paleta se třemi rolemi.', 'time' => '15 min'],
            ['text' => 'Nakresli drátěný model (wireframe) v mřížce s jasnou výzvou k akci (CTA).', 'output' => 'Wireframe bez barev a dekorací.', 'time' => '18 min'],
            ['text' => 'Realizuj redesign a ulož porovnání před/po do jednoho souboru.', 'output' => 'Export redesignu a obrázek před/po.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane kontrolní seznam pěti typických chyb (hierarchie, kontrast, zarovnání, přeplnění, chybějící akce) a vybírá z něj.',
            'standard' => 'Samostatná diagnostika, wireframe a redesign podle zadání.',
            'challenge' => 'Vytvoří dvě odlišné varianty redesignu a vybere tu, která lépe plní cíl zadání, s jedním měřitelným argumentem.',
        ],
        'assessment' => [
            'formative' => ['Po diagnostice: dvojice přečte jeden problém a dopad – třída posoudí, zda jde o funkci, nebo vkus.', 'U wireframu ukáže každý prstem pořadí čtení.'],
            'rubric' => [
                ['criterion' => 'Diagnostika', 'levels' => ['Jen „nelíbí se mi“.', 'Problémy bez dopadu na diváka.', 'Tři problémy s dopadem.', 'Seřadí problémy podle závažnosti.']],
                ['criterion' => 'Hierarchie redesignu', 'levels' => ['Pořadí čtení chybí.', 'Nadpis vyniká, zbytek soupeří.', 'Jasné 1.–2.–3. a viditelná akce.', 'Obstojí v testu tří sekund i na náhledu.']],
                ['criterion' => 'Obhajoba', 'levels' => ['Neumí vysvětlit změny.', 'Popíše změnu bez důvodu.', 'Problém → změna → efekt u 2 změn.', 'Přidá i kompromis, který vědomě přijal.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_composition', 'competence_label' => 'Kompozice a hierarchie', 'variants' => [
            ['question' => 'Který zápis je dobře formulovaný problém plakátu?', 'options' => ['Plakát je ošklivý.', 'Datum akce je malé a ve stejné barvě jako pozadí, divák ho přehlédne.', 'Chtělo by to víc barev.'], 'correct' => 1, 'explanation' => 'Dobrý problém = co přesně je špatně + dopad na diváka.'],
            ['question' => 'Proč se drátěný model kreslí bez barev a efektů?', 'options' => ['Aby se nejdřív ověřilo pořadí informací a rozvržení.', 'Protože barvy se do plakátu nepřidávají.', 'Aby byl hotový rychleji než ostatní.'], 'correct' => 0, 'explanation' => 'Wireframe testuje strukturu dřív, než ji zakryje styl.'],
            ['question' => 'Co do redesignu nepatří?', 'options' => ['Zachovat význam původního zadání.', 'Sjednotit zarovnání do mřížky.', 'Přidat nový text, který zadání nevyžaduje.'], 'correct' => 2, 'explanation' => 'Redesign zlepšuje komunikaci, nemění obsah zadání.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť jeden přeplněný leták nebo vývěsku a napiš dva problémy ve tvaru problém → dopad.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Slabý plakát do hodiny připravuje učitel (vymyšlená akce); nepoužívá se cizí práce spolužáka bez souhlasu.', 'Obrázky v redesignu jen vlastní nebo s licencí.'],
        'teacher_notes' => ['Častý omyl: redesign = víc efektů. Vracej žáky k otázce „co má divák udělat?“.', 'Otázka do třídy: Je tohle problém funkce, nebo vkusu?', 'Tempo: wireframe nesmí přesáhnout 45. minutu, jinak nezbude čas na realizaci.'],
        'substitution' => ['Zástup bez odborníka: rozdá vytištěný slabý plakát, žáci plní úkoly 1–3 na papír a úkol 4 podle možností učebny.', 'Plán B offline: celý redesign jako skica A4 tužkou + popisky rozhodnutí.'],
        'glossary' => ['wireframe', 'cta', 'grid'],
    ],
    7 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 7 · Jeden design, tři formáty',
        'goal' => [
            'student' => 'Na konci hodiny umím převést jeden plakát do čtverce a vertikálního formátu tak, aby zůstala hierarchie, čitelnost a vizuální identita.',
            'success_criteria' => ['Zapíšu tři pravidla, která se mezi formáty nesmí změnit.', 'Čtverec 1080 × 1080 i vertikála 1080 × 1920 drží stejné pořadí čtení.', 'Text ve vertikále je uvnitř bezpečné zóny (safe zone).'],
        ],
        'competencies' => [['id' => 'gfx_formats', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže jeden plakát špatně zmenšený do tří formátů.', 'student' => 'Najdou, co se při zmenšení rozbilo.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Neměnná pravidla', 'teacher' => 'Vysvětlí bezpečnou zónu a náhled (thumbnail).', 'student' => 'Sepíšou tři neměnná pravidla svého plakátu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 43, 'phase' => 'Čtverec', 'teacher' => 'Připomene: měníme ořez a rozvržení, ne identitu.', 'student' => 'Vytvoří verzi 1080 × 1080.', 'form' => 'jednotlivě'],
            ['from' => 43, 'to' => 63, 'phase' => 'Vertikála', 'teacher' => 'Ukáže, kam v příbězích zasahuje rozhraní aplikace.', 'student' => 'Vytvoří verzi 1080 × 1920 s textem v bezpečné zóně.', 'form' => 'jednotlivě'],
            ['from' => 63, 'to' => 80, 'phase' => 'Sjednocení a export', 'teacher' => 'Ukáže konzistentní pojmenování souborů.', 'student' => 'Sjednotí paletu a písmo, exportují 3 soubory.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Kontrola série a exit ticket', 'teacher' => 'Spustí rychlou kontrolu série vedle sebe.', 'student' => 'Porovnají tři formáty a odpoví na exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Zapiš tři pravidla svého plakátu, která se mezi formáty nesmí změnit (například pořadí informací, paleta, písmo).', 'output' => 'Tři neměnná pravidla.', 'time' => '12 min'],
            ['text' => 'Vytvoř čtvercovou verzi 1080 × 1080 – změň ořez a rozvržení, ne identitu.', 'output' => 'Čtverec ve stejném stylu.', 'time' => '18 min'],
            ['text' => 'Vytvoř vertikální verzi 1080 × 1920 a drž texty uvnitř bezpečné zóny (safe zone).', 'output' => 'Vertikála s vyznačenou bezpečnou zónou.', 'time' => '20 min'],
            ['text' => 'Exportuj tři formáty a pojmenuj je jednotně (akce_post, akce_ctverec, akce_story).', 'output' => 'Tři exporty se srozumitelnými názvy.', 'time' => '12 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu se zakreslenými bezpečnými zónami a vyplňuje jen dva formáty (post a čtverec).',
            'standard' => 'Tři formáty podle zadání včetně jednotných názvů souborů.',
            'challenge' => 'Přidá čtvrtý formát (široký banner 1500 × 500) a zdůvodní, co v něm vynechal.',
        ],
        'assessment' => [
            'formative' => ['Po pravidlech: dvojice si vymění seznamy a hledá pravidlo, které je jen „hezké“, ne funkční.', 'Kontrola náhledu: vertikála zmenšená na šířku palce – je nadpis čitelný?'],
            'rubric' => [
                ['criterion' => 'Neměnná pravidla', 'levels' => ['Pravidla chybí.', 'Pravidla obecná („aby to bylo hezké“).', 'Tři funkční pravidla.', 'Pravidla dodrží ve všech formátech a doloží to.']],
                ['criterion' => 'Adaptace formátů', 'levels' => ['Jen zmenšený plakát.', 'Změněný formát, rozbité pořadí.', 'Čtverec i vertikála drží pořadí čtení.', 'Přidaný formát s vědomým vynecháním.']],
                ['criterion' => 'Produkce', 'levels' => ['Chybí exporty.', 'Exporty s náhodnými názvy.', 'Tři exporty s jednotnými názvy.', 'Exporty zkontrolované mimo editor včetně náhledu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_formats', 'competence_label' => 'Formáty a export', 'variants' => [
            ['question' => 'Co je bezpečná zóna ve vertikálním formátu?', 'options' => ['Oblast, kam se dává logo autora.', 'Prostor, kde text nepřekryje rozhraní aplikace.', 'Okraj, který se při exportu ořízne.'], 'correct' => 1, 'explanation' => 'Mimo bezpečnou zónu může text zakrýt rozhraní aplikace.'],
            ['question' => 'Co je při převodu do čtverce správně?', 'options' => ['Zmenšit celý plakát a doplnit prázdné okraje.', 'Zachovat pořadí čtení a změnit rozvržení a ořez.', 'Změnit písmo, ať je čtverec jiný.'], 'correct' => 1, 'explanation' => 'Mění se rozvržení, identita zůstává.'],
            ['question' => 'Který název souboru je pro sérii nejlepší?', 'options' => ['final2_opravdu.png', 'obrazek.png', 'koncert_story_1080x1920.png'], 'correct' => 2, 'explanation' => 'Název říká obsah, formát i rozměr.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi jednu kampaň ve třech formátech (plakát, příspěvek, příběh) a zapiš, co zůstalo stejné.', 'minutes' => 15, 'optional' => true]],
        'safety' => ['Pracuje se s vymyšlenou akcí, bez skutečných kontaktů a jmen.', 'Fotky a písma jen s licencí, která dovoluje úpravy.'],
        'teacher_notes' => ['Častý omyl: vertikála = zmenšený plakát s prázdnými pruhy.', 'Otázka do třídy: Které tři věci musí divák poznat v každém formátu?', 'Tempo: vertikála zabere nejvíc času – úkol 4 lze dokončit doma nebo příště.'],
        'substitution' => ['Zástup bez odborníka: promítne zadání, žáci plní úkoly 1–4, učitel zástupu sbírá exporty.', 'Plán B offline: tři obdélníky ve správném poměru na papíře a rozvržení tužkou.'],
        'glossary' => ['safe-zone', 'thumbnail'],
    ],
    8 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 8 · Typografie a rozvržení stránky',
        'goal' => [
            'student' => 'Na konci hodiny umím nastavit čtyři textové role, rozumnou délku řádku a opakovaný rytmus mezer v dvousloupcovém rozvržení.',
            'success_criteria' => ['Čtyři role textu (nadpis, podnadpis, text, popisek) mají jasný rozdíl velikosti.', 'Odstavec má rozumnou délku řádku a řádkování.', 'Mezery mezi skupinami se opakují podle jedné škály.'],
        ],
        'competencies' => [['id' => 'gfx_typography', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Ukáže článek s deseti různými velikostmi písma.', 'student' => 'Spočítají velikosti a řeknou, co ztěžuje čtení.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Textové role', 'teacher' => 'Předvede Type Scale Lab a pojmenování rolí.', 'student' => 'Nastaví 4 role a zapíší jejich velikosti.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 40, 'phase' => 'Řádek a řádkování', 'teacher' => 'Ukáže příliš dlouhý řádek a jeho zkrácení.', 'student' => 'Upraví šířku sloupce a řádkování odstavce.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 55, 'phase' => 'Rytmus mezer', 'teacher' => 'Vysvětlí škálu mezer a volný prostor (whitespace).', 'student' => 'V Vertical Rhythm Lab zvolí škálu a oddělí skupiny.', 'form' => 've dvojicích'],
            ['from' => 55, 'to' => 80, 'phase' => 'Dvousloupcové rozvržení', 'teacher' => 'Hlídá, aby se používaly jen definované role.', 'student' => 'Postaví stránku časopisu v Canvě nebo Figmě.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Test čitelnosti a exit ticket', 'teacher' => 'Spustí test „přečti jen nadpisy“.', 'student' => 'Soused zkusí pochopit stránku z nadpisů; exit ticket.', 'form' => 've dvojicích'],
        ],
        'tasks' => [
            ['text' => 'Nastav čtyři textové role (nadpis, podnadpis, text, popisek) a zapiš jejich velikosti a řez písma.', 'output' => 'Tabulka rolí: velikost, řez, použití.', 'time' => '15 min'],
            ['text' => 'Zkrať příliš dlouhý řádek odstavce a uprav řádkování tak, aby se text dobře četl.', 'output' => 'Odstavec před a po úpravě.', 'time' => '12 min'],
            ['text' => 'Zvol škálu mezer (například 8 / 16 / 32 px) a použij ji mezi skupinami textu.', 'output' => 'Stránka, kde se mezery opakují podle škály.', 'time' => '13 min'],
            ['text' => 'Postav dvousloupcovou stránku časopisu jen s definovanými rolemi a srovnanými hranami.', 'output' => 'Export stránky.', 'time' => '25 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane připravenou škálu velikostí a mezer; přiřazuje role textům v hotové šabloně.',
            'standard' => 'Vlastní role, škála a dvousloupcová stránka podle zadání.',
            'challenge' => 'Navrhne druhou verzi stránky pro mobil (jeden sloupec) se stejnými rolemi.',
        ],
        'assessment' => [
            'formative' => ['Kontrola rolí: každý ukáže dvě role, které jsou si nejpodobnější – liší se dost?', 'Test nadpisů: soused řekne, o čem stránka je, jen z nadpisů.'],
            'rubric' => [
                ['criterion' => 'Textové role', 'levels' => ['Náhodné velikosti.', 'Role existují, ale splývají.', 'Čtyři zřetelně odlišené role.', 'Role popíše tak, aby je použil i spolužák.']],
                ['criterion' => 'Čitelnost odstavce', 'levels' => ['Dlouhé řádky, hustý text.', 'Upravená jen jedna vlastnost.', 'Rozumná délka řádku i řádkování.', 'Zdůvodní volbu a ověří ji na náhledu.']],
                ['criterion' => 'Rytmus a rozvržení', 'levels' => ['Mezery náhodné.', 'Škála zvolená, nedodržená.', 'Mezery podle škály, srovnané hrany.', 'Rytmus drží i v mobilní verzi.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_typography', 'competence_label' => 'Typografie', 'variants' => [
            ['question' => 'Odstavec se táhne přes celou šířku velkého monitoru. Co pomůže nejvíc?', 'options' => ['Zmenšit písmo, aby se vešlo víc slov.', 'Zúžit textový sloupec a přidat řádkování.', 'Zvýraznit celý odstavec tučně.'], 'correct' => 1, 'explanation' => 'Oko se na konci dlouhého řádku ztrácí.'],
            ['question' => 'Kolik velikostí písma potřebuje jednoduchá stránka časopisu?', 'options' => ['Malou škálu pojmenovaných rolí, typicky 3–5.', 'Každý odstavec jinou.', 'Jednu velikost pro všechno.'], 'correct' => 0, 'explanation' => 'Malá škála rolí je čitelná a opakovatelná.'],
            ['question' => 'Co dělá volný prostor (whitespace) v rozvržení?', 'options' => ['Je to chyba, kterou je třeba zaplnit.', 'Šetří barvu tiskárny.', 'Odděluje skupiny a vede oko.'], 'correct' => 2, 'explanation' => 'Volný prostor je aktivní prvek kompozice.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť stránku knihy nebo časopisu a označ na ní čtyři textové role.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Písma jen s licencí pro volné použití (např. z bezplatné knihovny písem); zdroj zapiš.', 'Texty do rozvržení jsou vymyšlené nebo s uvedeným zdrojem.'],
        'teacher_notes' => ['Častý omyl: rozdíl rolí jen o 1–2 px. Chtěj viditelný skok.', 'Otázka do třídy: Kdy je řádek už moc dlouhý? Zkuste ho přečíst nahlas.', 'Tempo: Type Scale Lab zabere víc, než se zdá – nastav časovač na 15 minut.'],
        'substitution' => ['Zástup bez odborníka: žáci plní úkoly 1–4 podle zadání v lekci, výstupem je export stránky.', 'Plán B offline: role a mezery na papír – nadpisy fixou, text tužkou, mezery pravítkem.'],
        'glossary' => ['whitespace'],
    ],
    9 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 9 · Vizuální příběh a odevzdání výstupu',
        'goal' => [
            'student' => 'Na konci hodiny umím řídit cestu pozornosti od ohniska přes informaci k akci a odevzdat výstup ve správném formátu se zdrojovým souborem.',
            'success_criteria' => ['Určím ohnisko a zapíšu plánovanou cestu oka.', 'Varianta B mění styl, ne význam, a umím zdůvodnit výběr.', 'Odevzdám pojmenovaný export správného rozměru a zachovaný zdrojový soubor.'],
        ],
        'competencies' => [['id' => 'gfx_formats', 'level' => 2], ['id' => 'gfx_composition', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Na 3 sekundy ukáže plakát a zeptá se, co si žáci zapamatovali.', 'student' => 'Zapíší první věc, kterou viděli.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Ohnisko a cesta oka', 'teacher' => 'Předvede Visual Story Lab.', 'student' => 'Určí ohnisko a nakreslí šipkami cestu oka.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 42, 'phase' => 'Obraz a text', 'teacher' => 'Ukáže, jak směr pohledu postavy vede k textu.', 'student' => 'Upraví ořez a ověří čitelnost textu přes obraz.', 'form' => 'jednotlivě'],
            ['from' => 42, 'to' => 60, 'phase' => 'Varianta B', 'teacher' => 'Zadá: jiný styl, stejný význam.', 'student' => 'Vytvoří variantu B a vyberou lepší podle cíle.', 'form' => 've dvojicích'],
            ['from' => 60, 'to' => 80, 'phase' => 'Kontrola před odevzdáním', 'teacher' => 'Projde kontrolní seznam (preflight).', 'student' => 'Zkontrolují rozměr, ořez, kontrast a pojmenují soubory.', 'form' => 'jednotlivě'],
            ['from' => 80, 'to' => 90, 'phase' => 'Odevzdání a exit ticket', 'teacher' => 'Ukáže, kam se odevzdává export i zdrojový soubor.', 'student' => 'Odevzdají výstup a odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Urči ohnisko (focal point) svého návrhu a šipkami zakresli plánovanou cestu oka: obraz → nadpis → informace → akce.', 'output' => 'Náhled se šipkami cesty oka.', 'time' => '15 min'],
            ['text' => 'Uprav ořez a umístění textu tak, aby obraz vedl pozornost k nadpisu a text byl čitelný.', 'output' => 'Upravený návrh.', 'time' => '17 min'],
            ['text' => 'Vytvoř variantu B se stejným obsahem a jiným stylem; vyber lepší a napiš proč.', 'output' => 'Varianty A a B + jedna věta zdůvodnění.', 'time' => '18 min'],
            ['text' => 'Projdi kontrolní seznam před odevzdáním (preflight) a odevzdej export i zdrojový soubor.', 'output' => 'Export se správným názvem a rozměrem + zdrojový soubor.', 'time' => '20 min'],
        ],
        'differentiation' => [
            'support' => 'Pracuje s připraveným obrázkem a šablonou; kontrolní seznam má zaškrtávací políčka.',
            'standard' => 'Vlastní návrh, varianta B a kontrola podle seznamu.',
            'challenge' => 'Varianta C pro tmavé pozadí a krátké porovnání, která varianta je čitelnější na mobilu.',
        ],
        'assessment' => [
            'formative' => ['Test 3 sekund: soused řekne, co viděl první – shoduje se to s plánem?', 'Před odevzdáním: učitel namátkou otevře dva exporty mimo editor.'],
            'rubric' => [
                ['criterion' => 'Cesta pozornosti', 'levels' => ['Ohnisko chybí.', 'Ohnisko je, cesta oka bloudí.', 'Obraz → nadpis → akce funguje.', 'Doloží testem 3 sekund u spolužáka.']],
                ['criterion' => 'Varianta a výběr', 'levels' => ['Varianta B chybí.', 'Varianta mění i význam.', 'Jiný styl, stejný význam, výběr zdůvodněný.', 'Výběr opře o pozorování spolužáka.']],
                ['criterion' => 'Odevzdání', 'levels' => ['Jen snímek obrazovky.', 'Export bez zdrojového souboru.', 'Pojmenovaný export + zdrojový soubor.', 'Kontrolní seznam vyplněný a bez chyb.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'gfx_formats', 'competence_label' => 'Formáty a export', 'variants' => [
            ['question' => 'Proč se odevzdává i zdrojový (upravitelný) soubor?', 'options' => ['Aby šel návrh později opravit nebo převést do jiného formátu.', 'Protože je větší a vypadá lépe.', 'Zdrojový soubor se neodevzdává nikdy.'], 'correct' => 0, 'explanation' => 'Z exportu se dobře neopravuje; zdroj je pojistka.'],
            ['question' => 'Co je ohnisko obrazu?', 'options' => ['Nejmenší text na plakátu.', 'Místo, kam se oko podívá jako první.', 'Barva pozadí.'], 'correct' => 1, 'explanation' => 'Ohnisko zahajuje cestu pozornosti.'],
            ['question' => 'Co patří do kontroly před odevzdáním (preflight)?', 'options' => ['Jen to, jestli se mi návrh líbí.', 'Počet vrstev v editoru.', 'Rozměr, ořez, kontrast, údaje a název souboru.'], 'correct' => 2, 'explanation' => 'Preflight hledá chyby dřív, než je uvidí divák.'],
        ]],
        'homework' => [['text' => 'Volitelné: pusť si 15 sekund libovolné reklamy a zapiš, kam ti padl pohled v prvních 3 sekundách.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Fotky osob jen se souhlasem nebo z bezplatných zdrojů s licencí; žádné fotky spolužáků bez souhlasu.', 'Do názvů souborů nepiš celé jméno – stačí třída a téma.'],
        'teacher_notes' => ['Častý omyl: dvě stejně silné dominanty. Nech žáky jednu ztlumit.', 'Otázka do třídy: Kam se dívá postava na fotce a kam tím posílá diváka?', 'Tempo: varianta B nemá být nový projekt – max. 18 minut.'],
        'substitution' => ['Zástup bez odborníka: test 3 sekund s promítnutým plakátem, pak úkoly 1–4 podle lekce.', 'Plán B offline: cesta oka šipkami na vytištěném plakátu, varianta B jako skica.'],
        'glossary' => ['focal-point', 'preflight'],
    ],
    10 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 10 · Anatomie webu: hierarchie od plakátu k obrazovce',
        'goal' => [
            'student' => 'Na konci hodiny umím rozložit webovou stránku na části (záhlaví, úvodní sekce, obsah, výzva k akci, zápatí) a nakreslit jejich pořadí podle důležitosti.',
            'success_criteria' => ['U pěti částí stránky napíšu jejich hlavní účel.', 'Nadpis úvodní sekce má nejvýš 8 slov a jednu hlavní akci.', 'Kostra stránky má společné hrany a stejné vodorovné okraje.'],
        ],
        'competencies' => [['id' => 'web_html_structure', 'level' => 2], ['id' => 'web_ux', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Start', 'teacher' => 'Promítne úvodní stránku vymyšlené školní akce.', 'student' => 'Ukážou, kde končí záhlaví a kde začíná obsah.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Části stránky', 'teacher' => 'Pojmenuje záhlaví, úvodní sekci (hero), obsah, CTA a zápatí.', 'student' => 'Ke každé části napíšou její jediný účel.', 'form' => 've dvojicích'],
            ['from' => 25, 'to' => 40, 'phase' => 'Priorita obsahu', 'teacher' => 'Ukáže zkrácení dlouhého zadání na nadpis, podtext a akci.', 'student' => 'Napíšou nadpis do 8 slov a vyberou hlavní akci.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 60, 'phase' => 'Kostra stránky', 'teacher' => 'Připomene mřížku (grid) a stejné okraje.', 'student' => 'Nakreslí kostru pro šířku 1440 px bez dekorací.', 'form' => 'jednotlivě'],
            ['from' => 60, 'to' => 78, 'phase' => 'Mobil', 'teacher' => 'Zeptá se: co musí být vidět bez posouvání?', 'student' => 'Označí, co na mobilu půjde pod sebe.', 'form' => 'jednotlivě'],
            ['from' => 78, 'to' => 90, 'phase' => 'Sdílení a exit ticket', 'teacher' => 'Vybere dvě kostry k rychlé zpětné vazbě.', 'student' => 'Řeknou „co vidím první“ a odpoví na exit ticket.', 'form' => 'frontálně'],
        ],
        'tasks' => [
            ['text' => 'Na ukázkové stránce označ záhlaví, úvodní sekci (hero), obsah, výzvu k akci (CTA) a zápatí a ke každé části napiš její účel.', 'output' => 'Popsaný snímek stránky.', 'time' => '15 min'],
            ['text' => 'Zkrať zadání akce na nadpis do 8 slov, jednu větu podtextu a jednu hlavní akci.', 'output' => 'Nadpis, podtext a text tlačítka.', 'time' => '12 min'],
            ['text' => 'Nakresli kostru stránky pro šířku 1440 px se společnými hranami a stejnými okraji.', 'output' => 'Kostra stránky (papír nebo Figma).', 'time' => '20 min'],
            ['text' => 'Označ, které části půjdou na mobilu pod sebe a co musí být vidět bez posouvání.', 'output' => 'Poznámky k mobilní verzi.', 'time' => '15 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane vytištěnou stránku s předkreslenými rámečky; doplňuje názvy částí a jejich účel.',
            'standard' => 'Samostatná analýza, nadpis, kostra a poznámky k mobilu.',
            'challenge' => 'Navrhne druhou kostru pro jiný cíl (přihláška místo informace) a popíše, co se změnilo.',
        ],
        'assessment' => [
            'formative' => ['Ukaž prstem: učitel jmenuje část stránky, žáci ji ukážou na své kostře.', 'Nadpis nahlas: tři žáci přečtou nadpis – rozumí třída, o co jde?'],
            'rubric' => [
                ['criterion' => 'Části stránky', 'levels' => ['Části nerozliší.', 'Pojmenuje části bez účelu.', 'Pět částí s účelem.', 'Vysvětlí, proč je pořadí právě takové.']],
                ['criterion' => 'Priorita obsahu', 'levels' => ['Nadpis chybí nebo je dlouhý.', 'Nadpis ok, akcí je víc.', 'Nadpis do 8 slov a jedna hlavní akce.', 'Nadpis obstojí v testu 3 sekund.']],
                ['criterion' => 'Kostra a mobil', 'levels' => ['Bez mřížky.', 'Mřížka, nesouhlasí okraje.', 'Společné hrany a poznámky k mobilu.', 'Mobilní pořadí zdůvodní prioritou obsahu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'web_html_structure', 'competence_label' => 'Struktura webu', 'variants' => [
            ['question' => 'Co je úkolem zápatí stránky?', 'options' => ['Hlavní nabídka akce.', 'Doplňkové informace: kontakt, odkazy, právní údaje.', 'Největší obrázek stránky.'], 'correct' => 1, 'explanation' => 'Zápatí uzavírá stránku doplňkovými informacemi.'],
            ['question' => 'Kolik hlavních akcí má mít úvodní sekce?', 'options' => ['Jednu výraznou, ostatní méně nápadné.', 'Pět stejně velkých tlačítek.', 'Žádnou, akce patří jen do zápatí.'], 'correct' => 0, 'explanation' => 'Jedna hlavní akce nesoutěží sama se sebou.'],
            ['question' => 'Proč kreslíme kostru stránky dřív než barvy?', 'options' => ['Barvy na webu nejsou potřeba.', 'Aby byla práce kratší.', 'Ověříme pořadí obsahu, než ho zakryje styl.'], 'correct' => 2, 'explanation' => 'Struktura je základ, styl přichází potom.'],
        ]],
        'homework' => [['text' => 'Volitelné: na webu, který používáš, najdi úvodní sekci a zapiš její nadpis a hlavní akci.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Analyzuje se vymyšlená stránka nebo veřejný web; nic se nepřihlašuje ani neodesílá.', 'Do kostry se nepíšou skutečné kontakty.'],
        'teacher_notes' => ['Častý omyl: začít kódem. Dnes jen model stránky – HTML až později.', 'Otázka do třídy: Co vidíš první? (místo „líbí se ti to?“)', 'Tempo: kostra 1440 px se kreslí rychle, pokud mají žáci připravený rámeček.'],
        'substitution' => ['Zástup bez odborníka: rozdá vytištěnou stránku, žáci plní úkoly 1–4 na papír.', 'Plán B offline: celá hodina na papíře A4 s pravítkem.'],
        'glossary' => ['hero', 'cta', 'grid'],
    ],
]]];
