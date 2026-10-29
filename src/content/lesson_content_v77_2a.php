<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · obsahová stopa, doplnění L1–L4 – 2.A (grafika a webdesign II).
 * STAV: NÁVRH (žák vidí až po schválení učitelem). Tvar a pravidla jako vlna 1 a 2 (lesson_content_v76_1a_a.php).
 * Vychází z témat původních lekcí (modules.php L1, next_lessons.php L2, extended_lessons.php L3–L4); texty jsou vlastní a česky.
 * Kompetence: návrh katalogu v75 „grafika_web_2“. Úrovně: levels_v75_content_2a.php (L1–L4). Vazba na ŠVP: svp_v72_map.php (návrh).
 * AI prompty: ai_prompts_v76.php z tohoto obsahu (po schválení).
 */

return ['class_2a' => ['lessons' => [
    1 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 1 · Start: plakát od briefu po tiskový export',
        'goal' => [
            'student' => 'Na konci hodiny umím projít celý postup plakátu – brief, mřížka, typografická škála, barvy s ověřeným kontrastem a kontrola před exportem – a výsledek odevzdat ve dvou formátech.',
            'success_criteria' => ['Brief říká jednou větou, komu plakát mluví a co má člověk udělat.', 'Rozvržení stojí na mřížce a typografická škála má jasné rozdíly velikostí.', 'Před exportem zkontroluji překlepy, spadávku a rozlišení obrázků.'],
        ],
        'competencies' => [['id' => 'g2_visual_system', 'level' => 2], ['id' => 'g2_typography', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Diagnostika', 'teacher' => 'Ukáže tři plakáty s chybou (hierarchie, kontrast, mezery) a nechá třídu hlasovat, co je špatně.', 'student' => 'Pojmenují chyby a zapíší, co si z minulého roku pamatují.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 22, 'phase' => 'Brief', 'teacher' => 'Zadá vymyšlenou akci a ukáže vzor briefu.', 'student' => 'Sepíší cílovou skupinu, akci a povinné informace.', 'form' => 'jednotlivě'],
            ['from' => 22, 'to' => 40, 'phase' => 'Mřížka a optický bod', 'teacher' => 'Předvede mřížku se šesti sloupci a hlavní optický bod.', 'student' => 'Rozvrhnou plochu a umístí nadpis, obraz a výzvu.', 'form' => 'jednotlivě'],
            ['from' => 40, 'to' => 65, 'phase' => 'Písmo a barva', 'teacher' => 'Ukáže typografickou škálu a ověření kontrastu.', 'student' => 'Nastaví škálu a barvy a dokončí plakát.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Kontrola a export', 'teacher' => 'Projde kontrolní seznam před tiskem.', 'student' => 'Zkontrolují plakát a exportují PDF pro tisk a PNG pro web.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne celý postup jednou větou.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš brief vymyšlené akce: jednou větou, komu plakát mluví a co má udělat, tři informace, které tam být musí, a dvě, které vynecháš.', 'output' => 'Brief s cílovou skupinou, akcí, 3 povinnými a 2 vynechanými informacemi.', 'time' => '12 min'],
            ['text' => 'Rozvrhni plochu do mřížky (grid) se šesti sloupci, urči hlavní optický bod a rozmísti nadpis, obraz a výzvu tak, aby vedly oko shora dolů.', 'output' => 'Rozvržení s viditelnou mřížkou a šipkou pořadí čtení.', 'time' => '18 min'],
            ['text' => 'Nastav typografickou škálu nadpis / podnadpis / detail s poměrem velikostí aspoň 1,5 a barevnou dvojici s kontrastem textu aspoň 4,5 : 1; kolem hlavního prvku nech volný prostor (whitespace).', 'output' => 'Plakát se škálou tří velikostí, ověřeným kontrastem a záměrným volným prostorem.', 'time' => '25 min'],
            ['text' => 'Proveď kontrolu před exportem (preflight) – překlepy, spadávka 3 mm, rozlišení obrázků 300 DPI pro tisk – a exportuj tiskové PDF a webové PNG.', 'output' => 'Odškrtnutý kontrolní seznam a dva exporty.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane mřížku jako podklad a předvybranou typografickou škálu; doplňuje obsah a barvy.',
            'standard' => 'Brief, mřížka, škála a barvy s kontrastem, kontrola a dva exporty.',
            'challenge' => 'Připraví druhou barevnou variantu pro tmavé pozadí se stejným kontrastem a zdůvodní volbu.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: kde je na tvém plakátu hlavní optický bod?', 'Kontrola souseda: projde text kontrastem 4,5 : 1?'],
            'rubric' => [
                ['criterion' => 'Brief a hierarchie', 'levels' => ['Bez briefu.', 'Brief bez cílové skupiny.', 'Brief a pořadí čtení odpovídají.', 'Vynechané informace zdůvodní briefem.']],
                ['criterion' => 'Mřížka a typografie', 'levels' => ['Bez mřížky a škály.', 'Mřížka bez škály.', 'Mřížka a škála s poměrem 1,5.', 'Škála drží i v malém náhledu.']],
                ['criterion' => 'Kontrola a export', 'levels' => ['Bez kontroly.', 'Jen jeden export.', 'Kontrolní seznam a dva exporty.', 'Najde a opraví chybu sám/sama před odevzdáním.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_visual_system', 'competence_label' => 'Grafika II: vizuální systém s hierarchií a kontrastem', 'variants' => [
            ['question' => 'K čemu je spadávka 3 mm u tiskového PDF?', 'options' => ['Aby po ořezu nezůstal na kraji bílý proužek.', 'Aby byl soubor menší.', 'Aby text nebyl rozmazaný.'], 'correct' => 0, 'explanation' => 'Tiskárna ořezává s tolerancí – pozadí musí přesahovat.'],
            ['question' => 'Jaké rozlišení obrázků se běžně chce pro tisk?', 'options' => ['72 DPI', '300 DPI', '30 DPI'], 'correct' => 1, 'explanation' => '72 DPI stačí na obrazovku, tisk potřebuje víc.'],
            ['question' => 'Co je volný prostor (whitespace) kolem hlavního prvku?', 'options' => ['Chyba, kterou je třeba zaplnit.', 'Místo pro další logo.', 'Záměrná součást hierarchie, která prvek zdůrazní.'], 'correct' => 2, 'explanation' => 'Prázdné místo vede oko k tomu, co obklopuje.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi plakát s dobrou hierarchií a zapiš jeho tři úrovně písma a odhad poměru velikostí.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Obrázky jen vlastní nebo s jasnou licencí; u cizích zapisujeme zdroj.', 'Akce a údaje na plakátu jsou vymyšlené, bez skutečných kontaktů.'],
        'teacher_notes' => ['Častý omyl: spadávka = okraj – ukaž rozdíl na vytištěném archu.', 'Otázka do třídy: Kterou informaci z plakátu můžeš vynechat a proč?', 'Tempo: diagnostika slouží učiteli – zapiš si, kdo potřebuje podporu v typografii.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 2 na papír, úkoly 3 a 4 v editoru podle hodnot v zadání.', 'Plán B offline: plakát na čtverečkovaném papíře s mřížkou a kontrolní seznam k odškrtání.'],
        'glossary' => ['grid', 'whitespace', 'preflight'],
    ],
    2 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 2 · Z plakátu kampaň: tři formáty jedním systémem',
        'goal' => [
            'student' => 'Na konci hodiny umím převést hlavní plakát do malé kampaně ve třech formátech tak, aby se měnilo rozvržení a ořez, ale systém zůstal stejný, a obhájit to za minutu.',
            'success_criteria' => ['Zapíšu čtyři konstanty systému: role písma, nejvýš tři barvy, pravidlo ořezu a podobu výzvy.', 'Story drží texty v bezpečné zóně a čtverec má vlastní rozvržení.', 'Exporty mají správné rozměry a jednotné názvy.'],
        ],
        'competencies' => [['id' => 'g2_visual_system', 'level' => 3], ['id' => 'g2_handoff', 'level' => 1]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Rozbor systému', 'teacher' => 'Ukáže kampaň, kde každý formát vypadá jinak, a zeptá se, proč nefunguje.', 'student' => 'Najdou, co se mezi formáty nesmí měnit.', 'form' => 'frontálně'],
            ['from' => 12, 'to' => 25, 'phase' => 'Konstanty a ořez', 'teacher' => 'Vysvětlí konstanty systému a ořez pro tři poměry stran.', 'student' => 'Zapíší konstanty a připraví tři ořezy fotografie.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Čtverec', 'teacher' => 'Hlídá, aby čtverec měl vlastní rozvržení.', 'student' => 'Adaptují plakát na čtverec 1080 × 1080 px.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Story', 'teacher' => 'Ukáže bezpečnou zónu story a svislý rytmus.', 'student' => 'Adaptují plakát na story 1080 × 1920 px.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Export a obhajoba', 'teacher' => 'Zadá jmennou konvenci a minutovou obhajobu.', 'student' => 'Exportují soubory a ve dvojici si obhajobu přednesou.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: systém se pozná, i když se mění formát.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Zapiš čtyři konstanty systému kampaně: dvě role písma, nejvýš tři barvy v HEX, pravidlo ořezu fotografie a podobu tlačítka výzvy.', 'output' => 'Karta konstant se čtyřmi oddíly.', 'time' => '10 min'],
            ['text' => 'Připrav ořez (crop) jedné fotografie pro tři poměry – 1080 × 1350, 1080 × 1080 a 1080 × 1920 px – tak, aby ve všech zůstalo ohnisko.', 'output' => 'Tři ořezy vedle sebe s vyznačeným ohniskem.', 'time' => '13 min'],
            ['text' => 'Adaptuj plakát na čtverec a story: změň rozvržení, ne identitu, a ve story drž texty a výzvu v bezpečné zóně (safe zone) mimo horních a dolních 250 px.', 'output' => 'Čtverec a story se stejnými konstantami a texty v bezpečné zóně.', 'time' => '40 min'],
            ['text' => 'Pojmenuj exporty jednotně (akce_format_verze.png), ověř rozměry mimo editor a připrav minutovou obhajobu: cíl kampaně, tři konstanty a jedna nutná změna.', 'output' => 'Tři správně pojmenované exporty a osnova obhajoby.', 'time' => '17 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane šablonu bezpečné zóny pro story a vyplněnou kartu konstant; adaptuje jen čtverec a story.',
            'standard' => 'Konstanty, tři ořezy, čtverec a story, export a minutová obhajoba.',
            'challenge' => 'Přidá čtvrtý formát (banner 1200 × 628 px) a napíše, které pravidlo systému musel/a upřesnit.',
        ],
        'assessment' => [
            'formative' => ['Kontrola souseda: zakryj nadpis – poznáš, že formáty patří ke stejné kampani?', 'Rychlá otázka: je výzva ve story uvnitř bezpečné zóny?'],
            'rubric' => [
                ['criterion' => 'Konstanty systému', 'levels' => ['Chybí.', 'Jen barvy.', 'Čtyři konstanty.', 'Konstanty dodržené ve všech formátech bez výjimky.']],
                ['criterion' => 'Adaptace', 'levels' => ['Jen zmenšený plakát.', 'Nový ořez, staré rozvržení.', 'Vlastní rozvržení a bezpečná zóna.', 'Adaptace zlepšuje čitelnost ve formátu.']],
                ['criterion' => 'Předání a obhajoba', 'levels' => ['Chaotické soubory.', 'Názvy jednotné, rozměry ne.', 'Správné rozměry, názvy i obhajoba.', 'Obhajoba zdůvodní změnu pojmy systému.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_visual_system', 'competence_label' => 'Grafika II: vizuální systém kampaně', 'variants' => [
            ['question' => 'Co je bezpečná zóna (safe zone) ve formátu story?', 'options' => ['Místo pro logo školy.', 'Plocha mimo horní a dolní okraj, kam nezasahuje rozhraní aplikace.', 'Okraj pro tiskárnu.'], 'correct' => 1, 'explanation' => 'Nahoře a dole story překrývá rozhraní – texty tam nedáváme.'],
            ['question' => 'Co se při adaptaci do čtverce smí změnit?', 'options' => ['Rozvržení a ořez.', 'Barvy kampaně.', 'Písmo nadpisu.'], 'correct' => 0, 'explanation' => 'Mění se uspořádání, konstanty systému zůstávají.'],
            ['question' => 'Proč pojmenovat exporty jednotně (akce_format_verze)?', 'options' => ['Aby byly menší.', 'Kvůli rychlejšímu nahrání.', 'Kdo soubory převezme, hned pozná formát i verzi.'], 'correct' => 2, 'explanation' => 'Jednotné názvy usnadní předání a zabrání záměně verzí.'],
        ]],
        'homework' => [['text' => 'Volitelné: najdi kampaň, která funguje ve více formátech, a zapiš její tři konstanty (bez stahování cizích materiálů).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Fotografie jen vlastní nebo s licencí; lidi na fotkách jen s jejich souhlasem.', 'Kampaň nezveřejňujeme na sociálních sítích – výstupy zůstávají ve škole.'],
        'teacher_notes' => ['Častý omyl: story = plakát natažený na výšku – ukaž rozhraní aplikace přes okraje.', 'Otázka do třídy: Co je na kampani „podpis“, který ji odliší od jiných?', 'Tempo: obhajobu dělejte ve dvojicích, ne před celou třídou.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 4 na papír, úkoly 2 a 3 v editoru podle rozměrů v zadání.', 'Plán B offline: tři papírové formáty ve správném poměru a výstřižky prvků k přeskládání.'],
        'glossary' => ['crop', 'safe-zone'],
    ],
    3 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 3 · Úvodní sekce webu: z plakátu na obrazovku',
        'goal' => [
            'student' => 'Na konci hodiny umím převést principy plakátu do úvodní sekce webu na počítači i na mobilu a zachovat pořadí obsahu, kontrast a velikost tlačítka.',
            'success_criteria' => ['Úvodní sekce má nadpis jednou větou, krátký text a jednu primární výzvu.', 'Desktop stojí na 12sloupcové mřížce a mobil zachovává pořadí obsahu.', 'Text na obrázku má kontrast aspoň 4,5 : 1 a tlačítko na mobilu aspoň 44 × 44 px.'],
        ],
        'competencies' => [['id' => 'w2_css_responsive', 'level' => 2], ['id' => 'g2_visual_system', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 12, 'phase' => 'Rozbor úvodních sekcí', 'teacher' => 'Ukáže tři úvodní sekce webů a zeptá se, co návštěvník udělá jako první.', 'student' => 'Najdou nadpis, výzvu a to, co je navíc.', 'form' => 'frontálně'],
            ['from' => 12, 'to' => 25, 'phase' => 'Obsah', 'teacher' => 'Vysvětlí, že obsah je první a dekorace až potom.', 'student' => 'Napíší nadpis, krátký text a výzvu.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Desktop', 'teacher' => 'Předvede 12sloupcovou mřížku a textovou a obrazovou zónu.', 'student' => 'Postaví desktopovou verzi.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Mobil', 'teacher' => 'Ukáže přeskládání pro 390 px a bod zlomu.', 'student' => 'Přeskládají sekci pro mobil.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Kontrola a prezentace', 'teacher' => 'Zadá kontrolu kontrastu a velikosti tlačítka.', 'student' => 'Ověří obě hodnoty a ukáží desktop a mobil sousedovi.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: mobil není zmenšený desktop.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Napiš obsah úvodní sekce (hero): nadpis jednou větou, doplňující text nejvýš na dva řádky a jednu primární výzvu (CTA) se slovesem.', 'output' => 'Tři texty: nadpis, text, tlačítko typu „Rezervuj místo“.', 'time' => '12 min'],
            ['text' => 'Na 12sloupcové mřížce šířky 1280 px urči textovou zónu (sloupce 1–6) a obrazovou zónu (7–12) a postav desktopovou verzi.', 'output' => 'Desktopový návrh s viditelnou mřížkou a dvěma zónami.', 'time' => '20 min'],
            ['text' => 'Přeskup sekci pro šířku 390 px podle zásady mobile first (nejdřív nadpis a výzva, pak obrázek) a zapiš, při jaké šířce (breakpoint) se rozvržení mění.', 'output' => 'Mobilní návrh a zápis bodu zlomu, např. 768 px.', 'time' => '20 min'],
            ['text' => 'Ověř kontrast textu na obrázku (aspoň 4,5 : 1) a velikost tlačítka na mobilu (aspoň 44 × 44 px), opravy zapiš a jednu změnu rozvržení zdůvodni.', 'output' => 'Ověřený kontrast, rozměr tlačítka a věta o změně rozvržení.', 'time' => '16 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane drátěný model desktopu i mobilu a doplňuje obsah a barvy.',
            'standard' => 'Obsah, desktop na mřížce, mobil s bodem zlomu a kontrola kontrastu a tlačítka.',
            'challenge' => 'Popíše rozvržení zjednodušeným CSS (grid na desktopu, jeden sloupec na mobilu) a ověří ho v náhledu prohlížeče.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: co uvidí návštěvník na mobilu jako první?', 'Kontrola souseda: změř tlačítko – má aspoň 44 px?'],
            'rubric' => [
                ['criterion' => 'Obsah a výzva', 'levels' => ['Bez výzvy.', 'Výzva bez slovesa.', 'Nadpis, text a jasná výzva.', 'Výzva odpovídá cíli návštěvníka.']],
                ['criterion' => 'Rozvržení', 'levels' => ['Bez mřížky.', 'Desktop bez mobilu.', 'Desktop na mřížce a mobil se stejným pořadím.', 'Bod zlomu zdůvodněný obsahem.']],
                ['criterion' => 'Přístupnost', 'levels' => ['Neověřeno.', 'Jen kontrast.', 'Kontrast i velikost tlačítka.', 'Opravy ověřené znovu.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'w2_css_responsive', 'competence_label' => 'Web II: responzivní rozvržení', 'variants' => [
            ['question' => 'Co znamená zásada mobile first?', 'options' => ['Web je jen pro mobil.', 'Mobil dostane méně obsahu.', 'Navrhuji nejdřív pro malou obrazovku a pořadí obsahu, pak rozšiřuji.'], 'correct' => 2, 'explanation' => 'Začínáme tím nejdůležitějším na nejmenší ploše.'],
            ['question' => 'Jak velké má být tlačítko na mobilu?', 'options' => ['Aspoň 44 × 44 px, aby se trefil prst.', 'Stačí 16 × 16 px.', 'Na velikosti nezáleží.'], 'correct' => 0, 'explanation' => 'Malé cíle dotyku jsou nepřístupné.'],
            ['question' => 'Co je bod zlomu (breakpoint)?', 'options' => ['Chyba v CSS.', 'Šířka obrazovky, při které se mění rozvržení.', 'Místo, kde končí text.'], 'correct' => 1, 'explanation' => 'Při určité šířce přepneme např. ze dvou sloupců na jeden.'],
        ]],
        'homework' => [['text' => 'Volitelné: vyfoť úvodní sekci jednoho webu na mobilu a na počítači a zapiš, co se mezi nimi změnilo (bez přihlašování).', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Obrázky jen vlastní nebo s licencí; texty a značky jsou vymyšlené.', 'Ukázky cizích webů jen prohlížíme, nekopírujeme jejich grafiku ani texty.'],
        'teacher_notes' => ['Častý omyl: mobil = vše zmenšené – ukaž nečitelný nadpis na 390 px.', 'Otázka do třídy: Proč je na úvodní sekci jen jedna primární výzva?', 'Tempo: kdo nestihne mobil, dokončí ho v lekci 4 jako rozcvičku.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1 a 4 na papír, úkoly 2 a 3 v editoru podle rozměrů v zadání.', 'Plán B offline: drátěné modely na papíře ve dvou šířkách a měřítko 1 : 1 pro tlačítko.'],
        'glossary' => ['hero', 'cta', 'mobile-first', 'breakpoint'],
    ],
    4 => [
        'status' => 'navrh', 'version' => 1,
        'title' => 'Lekce 4 · Storyboard krátkého videa: jedna zpráva v čase',
        'goal' => [
            'student' => 'Na konci hodiny umím rozplánovat krátké video na 4–6 snímků s jednou zprávou, čitelným textem v čase a výzvou na konci a připravit klidnou variantu bez pohybu.',
            'success_criteria' => ['Video má jednu zprávu, délku 6–10 s a výzvu na posledním snímku.', 'Každý snímek má jediný hlavní úkol a text je vidět aspoň 1,5 s.', 'Každý efekt má funkci a existuje varianta pro omezení pohybu.'],
        ],
        'competencies' => [['id' => 'g2_visual_system', 'level' => 2], ['id' => 'g2_typography', 'level' => 2]],
        'timeline' => [
            ['from' => 0, 'to' => 10, 'phase' => 'Cíl a délka', 'teacher' => 'Pustí dvě krátká videa – jedno přeplněné, jedno s jednou zprávou – a zeptá se, co si třída pamatuje.', 'student' => 'Zapíší zprávu, kterou si zapamatovali.', 'form' => 'frontálně'],
            ['from' => 10, 'to' => 25, 'phase' => 'Zpráva a časování', 'teacher' => 'Vysvětlí rozpočet času: kolik sekund dostane každá informace.', 'student' => 'Definují zprávu, výzvu a rozpočet času.', 'form' => 'jednotlivě'],
            ['from' => 25, 'to' => 45, 'phase' => 'Storyboard', 'teacher' => 'Ukáže storyboard se 4–6 snímky.', 'student' => 'Nakreslí snímky, každý s jedním úkolem.', 'form' => 'jednotlivě'],
            ['from' => 45, 'to' => 65, 'phase' => 'Prototyp', 'teacher' => 'Předvede jednoduchou animaci a ptá se na funkci každého efektu.', 'student' => 'Vytvoří prototyp a vyřadí efekty bez funkce.', 'form' => 'jednotlivě'],
            ['from' => 65, 'to' => 82, 'phase' => 'Kontrola a klidná varianta', 'teacher' => 'Vysvětlí, proč někteří uživatelé vypínají pohyb.', 'student' => 'Ověří poslední snímek a připraví variantu bez pohybu.', 'form' => 've dvojicích'],
            ['from' => 82, 'to' => 90, 'phase' => 'Exit ticket', 'teacher' => 'Shrne: hierarchie v čase = co uvidím první, druhé a poslední.', 'student' => 'Odpoví na exit ticket.', 'form' => 'jednotlivě'],
        ],
        'tasks' => [
            ['text' => 'Definuj jednu zprávu videa, výzvu k akci a délku 6–10 s a rozepiš, kolik sekund dostane každá informace.', 'output' => 'Rozpočet času, např. 0–2 s nadpis, 2–5 s obraz, 5–8 s datum, 8–10 s výzva.', 'time' => '12 min'],
            ['text' => 'Nakresli storyboard 4–6 snímků: u každého napiš jeho jediný hlavní úkol a jak dlouho je text vidět (aspoň 1,5 s).', 'output' => 'Storyboard s očíslovanými snímky, úkolem a časem.', 'time' => '20 min'],
            ['text' => 'Vytvoř jednoduchý prototyp animace a u každého efektu (přechod, posun, zvětšení) napiš, jakou má funkci; efekt bez funkce vyřaď.', 'output' => 'Prototyp a tabulka efekt → funkce s vyškrtnutými efekty.', 'time' => '20 min'],
            ['text' => 'Ověř, že výzva (CTA) je na posledním snímku čitelná, připrav klidnou variantu pro uživatele s omezením pohybu (reduced motion) a exportuj storyboard do PDF.', 'output' => 'Poslední snímek s výzvou, popis klidné varianty a PDF storyboardu.', 'time' => '16 min'],
        ],
        'differentiation' => [
            'support' => 'Dostane prázdný storyboard se čtyřmi rámečky a časovou osou; doplňuje snímky a texty.',
            'standard' => 'Zpráva a časování, storyboard, prototyp s funkčními efekty a klidná varianta.',
            'challenge' => 'Navrhne mikrointerakci tlačítka výzvy (stav najetí a stisku) a zdůvodní její délku v milisekundách.',
        ],
        'assessment' => [
            'formative' => ['Rychlá otázka: kolik zpráv má tvoje video?', 'Kontrola souseda: zvládneš přečíst text snímku 2 za 1,5 s?'],
            'rubric' => [
                ['criterion' => 'Zpráva a čas', 'levels' => ['Víc zpráv.', 'Jedna zpráva bez časování.', 'Jedna zpráva s rozpočtem času.', 'Časování zdůvodní čitelností.']],
                ['criterion' => 'Storyboard', 'levels' => ['Chybí.', 'Snímky bez úkolu.', 'Snímky s úkolem a časem.', 'Snímky drží typografické role kampaně.']],
                ['criterion' => 'Pohyb a přístupnost', 'levels' => ['Efekty bez funkce.', 'Některé efekty zdůvodněné.', 'Všechny efekty mají funkci.', 'Klidná varianta a čitelná výzva na konci.']],
            ],
        ],
        'exit_ticket' => ['competence' => 'g2_visual_system', 'competence_label' => 'Grafika II: hierarchie v čase', 'variants' => [
            ['question' => 'Jak dlouho má být text ve videu vidět nejméně?', 'options' => ['0,2 s', 'Na délce nezáleží.', 'Aspoň 1,5 s, aby se dal přečíst.'], 'correct' => 2, 'explanation' => 'Krátce zobrazený text nikdo nepřečte.'],
            ['question' => 'Kdy patří efekt do videa?', 'options' => ['Když má funkci – vede pozornost nebo ukazuje změnu.', 'Vždy, video je pak živější.', 'Jen na prvním snímku.'], 'correct' => 0, 'explanation' => 'Efekt bez funkce odvádí pozornost od zprávy.'],
            ['question' => 'Proč připravit klidnou variantu bez pohybu?', 'options' => ['Je menší na disku.', 'Někomu pohyb způsobuje nevolnost, proto si ho vypíná.', 'Klidná varianta je vždy hezčí.'], 'correct' => 1, 'explanation' => 'Volba omezení pohybu v systému je pro přístupnost důležitá.'],
        ]],
        'homework' => [['text' => 'Volitelné: změř u jednoho krátkého videa, jak dlouho je vidět každý text, a zapiš, který byl moc rychlý.', 'minutes' => 10, 'optional' => true]],
        'safety' => ['Hudbu a obrázky jen s licencí nebo vlastní; videa nezveřejňujeme mimo školu.', 'Nepoužíváme rychle blikající efekty (riziko pro citlivé diváky).'],
        'teacher_notes' => ['Častý omyl: „čím víc pohybu, tím líp“ – nech třídu zkusit přečíst přeplněné video.', 'Otázka do třídy: Co si divák zapamatuje, když vypne zvuk?', 'Tempo: prototyp stačí jednoduchý; důležitý je storyboard.'],
        'substitution' => ['Zástup bez odborníka: úkoly 1, 2 a 4 na papír, úkol 3 v libovolném nástroji na prezentace s přechody.', 'Plán B offline: storyboard na papírových kartičkách a stopky pro měření času textu.'],
        'glossary' => ['cta', 'reduced-motion'],
    ],
]]];
