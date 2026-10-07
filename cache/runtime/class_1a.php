<?php

declare(strict_types=1);

return array (
  'knowledgeTours' => 
  array (
    'hierarchy' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'poster',
        'mode' => 'hierarchy',
      ),
      'mental' => 'Hierarchie říká oku, co má vidět první, druhé a třetí. Když je všechno stejně důležité, ve výsledku není důležité nic.',
      'steps' => 
      array (
        0 => 'Urči jedinou hlavní zprávu.',
        1 => 'Vyber dominantní prvek — obvykle headline nebo obraz.',
        2 => 'Sekundární informace zmenši a seskup.',
        3 => 'CTA nebo klíčový údaj dej na čitelné místo.',
        4 => 'Zkontroluj návrh z dálky / jako malý náhled.',
      ),
      'mistakes' => 
      array (
        0 => 'Všechen text stejně velký.',
        1 => 'Pět různých důrazů najednou.',
        2 => 'Důležitý datum/CTA schovaný mezi dekoracemi.',
      ),
      'check' => 
      array (
        'q' => 'Co je cílem vizuální hierarchie?',
        'options' => 
        array (
          0 => 'Řídit pořadí, ve kterém divák informace vnímá.',
          1 => 'Použít co nejvíc fontů.',
          2 => 'Vyplnit každý prázdný prostor.',
        ),
        'correct' => 0,
        'why' => 'Hierarchie vytváří jasnou cestu oka přes obsah.',
      ),
    ),
    'composition' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'grid',
        'columns' => 6,
      ),
      'mental' => 'Grid není vězení. Je to skrytá konstrukce, která pomáhá zarovnat prvky a vytvořit rytmus. Negativní prostor dává obsahu prostor dýchat.',
      'steps' => 
      array (
        0 => 'Zvol okraje.',
        1 => 'Nastav jednoduchý grid/sloupce.',
        2 => 'Zarovnej příbuzné prvky.',
        3 => 'Použij whitespace jako aktivní prvek.',
        4 => 'Zkontroluj rovnováhu a těžiště kompozice.',
      ),
      'mistakes' => 
      array (
        0 => 'Lepit prvky náhodně k sobě.',
        1 => 'Bát se prázdného prostoru.',
        2 => 'Mít téměř stejné, ale ne přesné zarovnání.',
      ),
      'check' => 
      array (
        'q' => 'K čemu je grid?',
        'options' => 
        array (
          0 => 'K systematickému zarovnání a rytmu.',
          1 => 'K automatickému výběru barev.',
          2 => 'Jen pro tabulky.',
        ),
        'correct' => 0,
        'why' => 'Grid pomáhá držet konzistenci pozic a proporcí.',
      ),
    ),
    'contrast-color' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'color',
        'mode' => 'contrast',
      ),
      'mental' => 'Kontrast vytváří rozdíl, díky kterému věci rozeznáme a pochopíme jejich důležitost. Barva je jen jeden z nástrojů kontrastu.',
      'steps' => 
      array (
        0 => 'Zkontroluj světlost textu vůči pozadí.',
        1 => 'Omez paletu na hlavní + podpůrné barvy.',
        2 => 'Použij akcent pro jednu důležitou věc.',
        3 => 'Nespoléhej jen na barvu pro význam.',
        4 => 'Otestuj návrh i v malém náhledu.',
      ),
      'mistakes' => 
      array (
        0 => 'Šedý text na šedém pozadí.',
        1 => 'Každý prvek jinou výraznou barvou.',
        2 => 'Červená/zelená jako jediný nositel informace.',
      ),
      'check' => 
      array (
        'q' => 'Silný kontrast je nejdůležitější hlavně pro:',
        'options' => 
        array (
          0 => 'Čitelnost a jasný důraz.',
          1 => 'Počet vrstev v souboru.',
          2 => 'Velikost PDF.',
        ),
        'correct' => 0,
        'why' => 'Dostatečný kontrast pomáhá textu i důležitým prvkům vystoupit.',
      ),
    ),
    'typography' => 
    array (
      'time' => '9–13 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'poster',
        'mode' => 'type',
      ),
      'mental' => 'Typografie není výběr „hezkého fontu“. Je to práce s velikostí, řezem, řádkováním, délkou řádku a vztahem textových úrovní.',
      'steps' => 
      array (
        0 => 'Vyber 1–2 rodiny písem.',
        1 => 'Nastav jasný rozdíl headline/body.',
        2 => 'Uprav řádkování a délku řádku.',
        3 => 'Zarovnávej konzistentně.',
        4 => 'Kontroluj českou diakritiku a čitelnost.',
      ),
      'mistakes' => 
      array (
        0 => 'Čtyři dekorativní fonty v jednom plakátu.',
        1 => 'Příliš malý body text.',
        2 => 'Těsné řádkování nebo dlouhé řádky.',
      ),
      'check' => 
      array (
        'q' => 'Kolik písem je pro jednoduchý školní plakát obvykle bezpečný start?',
        'options' => 
        array (
          0 => '1–2 dobře kombinované rodiny.',
          1 => 'Nejméně 6.',
          2 => 'Každý řádek jiné.',
        ),
        'correct' => 0,
        'why' => 'Omezený počet písem pomáhá konzistenci a hierarchii.',
      ),
    ),
    'raster-vector' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'pixels',
      ),
      'mental' => 'Raster je mřížka pixelů — skvělá pro fotografie. Vektor popisuje tvary matematicky — skvělý pro loga, ikony a škálovatelné ilustrace.',
      'steps' => 
      array (
        0 => 'Rozhodni, zda pracuješ s fotografií nebo tvarem/logem.',
        1 => 'U rasteru hlídej rozlišení.',
        2 => 'U vektoru hlídej křivky a výplně.',
        3 => 'Exportuj do formátu podle cíle.',
        4 => 'Nenech malé rasterové logo zvětšovat do billboardu.',
      ),
      'mistakes' => 
      array (
        0 => 'Považovat PNG za vektor.',
        1 => 'Vložit JPG logo a čekat nekonečnou ostrost.',
        2 => 'Použít SVG pro fotografii.',
      ),
      'check' => 
      array (
        'q' => 'Který typ je vhodnější pro logo, které se má zvětšovat bez ztráty kvality?',
        'options' => 
        array (
          0 => 'Vektor.',
          1 => 'Nízké JPG.',
          2 => 'Screenshot.',
        ),
        'correct' => 0,
        'why' => 'Vektor se škáluje bez závislosti na původním počtu pixelů.',
      ),
    ),
    'color' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'color',
        'mode' => 'rgb-cmyk',
      ),
      'mental' => 'RGB je světlo na obrazovce, CMYK je model běžného čtyřbarevného tisku. Stejná „barva“ nemusí v obou světech vypadat stejně.',
      'steps' => 
      array (
        0 => 'Zjisti, zda výstup míří na obrazovku nebo tisk.',
        1 => 'Pracuj v odpovídajícím barevném prostoru/workflow.',
        2 => 'U tisku počítej s omezenějším gamutem.',
        3 => 'Pro kritický tisk používej správný profil/soft proof podle procesu.',
        4 => 'Vždy zkontroluj finální export.',
      ),
      'mistakes' => 
      array (
        0 => 'Navrhnout neonovou RGB barvu a čekat stejný běžný CMYK tisk.',
        1 => 'Převést vše do CMYK i pro web bez důvodu.',
        2 => 'Ignorovat profil tiskárny/procesu.',
      ),
      'check' => 
      array (
        'q' => 'Webový banner typicky připravíš v:',
        'options' => 
        array (
          0 => 'RGB.',
          1 => 'CMYK jako povinnost.',
          2 => 'Pouze černobíle.',
        ),
        'correct' => 0,
        'why' => 'Digitální obrazovky pracují s RGB světlem.',
      ),
    ),
    'export' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'export',
      ),
      'mental' => 'Dobrý návrh může zničit špatný export. Výstup je součást designu: rozměr, rozlišení, komprese, formát a cílové médium.',
      'steps' => 
      array (
        0 => 'Zjisti cílový rozměr a médium.',
        1 => 'Pro raster zkontroluj dostatek pixelů.',
        2 => 'Vyber správný formát.',
        3 => 'Nastav rozumnou kompresi.',
        4 => 'Otevři exportovaný soubor a skutečně ho zkontroluj.',
      ),
      'mistakes' => 
      array (
        0 => 'Odevzdat screenshot editoru místo exportu.',
        1 => 'Zvětšovat malý obrázek a považovat to za vyšší kvalitu.',
        2 => 'Použít obří PNG tam, kde stačí kvalitní JPG/WebP.',
      ),
      'check' => 
      array (
        'q' => 'Co uděláš jako poslední krok po exportu?',
        'options' => 
        array (
          0 => 'Otevřu a zkontroluji skutečný export.',
          1 => 'Smažu zdrojový soubor.',
          2 => 'Změním font bez dalšího exportu.',
        ),
        'correct' => 0,
        'why' => 'Kontrola výsledného souboru zachytí chybějící prvky, špatné rozměry, artefakty i barvy.',
      ),
    ),
    'assets' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'assets',
      ),
      'mental' => 'Obrázek na internetu není automaticky „zdarma k použití“. Designér řeší zároveň kvalitu zdroje, licenci, souhlas i dohledatelnost původu.',
      'steps' => 
      array (
        0 => 'Zjisti, odkud asset pochází.',
        1 => 'Přečti licenci/podmínky.',
        2 => 'Ověř povolené použití a případnou atribuci.',
        3 => 'Ukládej si zdroj/odkaz/licenci.',
        4 => 'Pro školní/portfolio projekt preferuj vlastní nebo jasně licencované materiály.',
      ),
      'mistakes' => 
      array (
        0 => 'Google Images = fotobanka zdarma.',
        1 => 'Odstranit watermark.',
        2 => 'Ignorovat licenci u fontu nebo ikony.',
      ),
      'check' => 
      array (
        'q' => 'Našel jsi fotografii přes Google Images. Co je správný další krok?',
        'options' => 
        array (
          0 => 'Dohledat původní zdroj a licenci.',
          1 => 'Automaticky ji použít.',
          2 => 'Odstranit watermark.',
        ),
        'correct' => 0,
        'why' => 'Vyhledávač není licence; je potřeba zjistit původ a podmínky použití.',
      ),
    ),
    'branding-system' => 
    array (
      'time' => '9–13 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => '1',
            1 => 'Typografie',
            2 => 'stálá hierarchie',
          ),
          1 => 
          array (
            0 => '2',
            1 => 'Barvy',
            2 => 'hlavní + akcent',
          ),
          2 => 
          array (
            0 => '3',
            1 => 'Obraz',
            2 => 'crop / treatment',
          ),
          3 => 
          array (
            0 => '4',
            1 => 'Grid',
            2 => 'společné hrany',
          ),
          4 => 
          array (
            0 => '5',
            1 => 'CTA',
            2 => 'stejný charakter',
          ),
        ),
      ),
      'mental' => 'Vizuální systém je sada konstant. Formát se mění, identita zůstává rozpoznatelná.',
      'steps' => 
      array (
        0 => 'Sepiš 3–5 konstant kampaně.',
        1 => 'Urči, co se smí měnit podle formátu.',
        2 => 'Postav master návrh.',
        3 => 'Adaptuj bez kopírování pozic.',
        4 => 'Porovnej varianty vedle sebe.',
      ),
      'mistakes' => 
      array (
        0 => 'Každý formát jiná typografie.',
        1 => 'Považovat konzistenci za kopírování layoutu.',
        2 => 'Přidávat další barvy při každé adaptaci.',
      ),
      'check' => 
      array (
        'q' => 'Co má být při adaptaci nejstabilnější?',
        'options' => 
        array (
          0 => 'Role prvků a vizuální systém.',
          1 => 'Přesná pixelová pozice.',
          2 => 'Stejný crop za každou cenu.',
        ),
        'correct' => 0,
        'why' => 'Konzistence je o pravidlech a rolích, ne o identických souřadnicích.',
      ),
    ),
    'photo-treatment' => 
    array (
      'time' => '8–12 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Bez treatmentu',
          1 => 'text soutěží s detailem',
          2 => 'slabá čitelnost',
        ),
        'right' => 
        array (
          0 => 'Řízený obraz',
          1 => 'crop + overlay + focal point',
          2 => 'obraz podporuje zprávu',
        ),
      ),
      'mental' => 'Obraz má sloužit komunikaci. Crop a treatment jsou stejně důležité jako výběr fotografie.',
      'steps' => 
      array (
        0 => 'Najdi focal point.',
        1 => 'Vyber crop pro formát.',
        2 => 'Najdi klidnou textovou zónu.',
        3 => 'Případně použij overlay/tónování.',
        4 => 'Ověř čitelnost i bez zoomu.',
      ),
      'mistakes' => 
      array (
        0 => 'Text přes nejdetailnější oblast.',
        1 => 'Přehnaný overlay, který zabije fotografii.',
        2 => 'Stejný crop pro všechny formáty.',
      ),
      'check' => 
      array (
        'q' => 'Proč měnit crop při adaptaci?',
        'options' => 
        array (
          0 => 'Jiný poměr stran mění kompozici a prostor pro text.',
          1 => 'Protože Canva to vyžaduje.',
          2 => 'Aby byl soubor větší.',
        ),
        'correct' => 0,
        'why' => 'Každý formát vytváří jiný prostor a jiné těžiště kompozice.',
      ),
    ),
    'responsive-adaptation' => 
    array (
      'time' => '10–14 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Master',
            1 => '1080×1350',
          ),
          1 => 
          array (
            0 => 'Square',
            1 => '1080×1080',
          ),
          2 => 
          array (
            0 => 'Story',
            1 => '1080×1920',
          ),
          3 => 
          array (
            0 => 'Kontrola',
            1 => 'hierarchie zůstává',
          ),
        ),
      ),
      'mental' => 'Responsive grafika zachovává zprávu a systém, ale znovu řeší layout pro nový prostor.',
      'steps' => 
      array (
        0 => 'Sepiš priority obsahu.',
        1 => 'Zkopíruj design tokens, ne souřadnice.',
        2 => 'Změň crop/kompozici.',
        3 => 'Ověř CTA a safe area.',
        4 => 'Exportuj každý formát samostatně.',
      ),
      'mistakes' => 
      array (
        0 => 'Jen oříznout master.',
        1 => 'Nechat text mimo safe area story.',
        2 => 'Zmenšit vše rovnoměrně bez nové hierarchie.',
      ),
      'check' => 
      array (
        'q' => 'Co při adaptaci NESMÍ být automatický cíl?',
        'options' => 
        array (
          0 => 'Přesné zachování pozic.',
          1 => 'Zachování role headline.',
          2 => 'Zachování hlavní palety.',
        ),
        'correct' => 0,
        'why' => 'Nový formát potřebuje nové rozmístění, ale stejné role a systém.',
      ),
    ),
    'portfolio-presentation' => 
    array (
      'time' => '8–12 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Brief',
            1 => 'co řeším',
          ),
          1 => 
          array (
            0 => 'Systém',
            1 => 'jaká pravidla',
          ),
          2 => 
          array (
            0 => 'Master',
            1 => 'hlavní návrh',
          ),
          3 => 
          array (
            0 => 'Adaptace',
            1 => 'důkaz flexibility',
          ),
          4 => 
          array (
            0 => 'Rationale',
            1 => 'proč to funguje',
          ),
        ),
      ),
      'mental' => 'Proces dává finálnímu vizuálu kontext a dokazuje, že rozhodnutí nejsou náhodná.',
      'steps' => 
      array (
        0 => 'Napiš cíl jednou větou.',
        1 => 'Ukaž klíčová pravidla.',
        2 => 'Ukaž master.',
        3 => 'Přidej jednu adaptaci nebo A/B.',
        4 => 'Vysvětli 2–3 rozhodnutí odbornými pojmy.',
      ),
      'mistakes' => 
      array (
        0 => 'Prezentovat jen screenshot Canvy.',
        1 => 'Obhajoba „líbí se mi to“.',
        2 => 'Ukázat deset téměř stejných variant bez pointy.',
      ),
      'check' => 
      array (
        'q' => 'Co patří do stručné obhajoby?',
        'options' => 
        array (
          0 => 'Cíl + důvod klíčových rozhodnutí.',
          1 => 'Seznam všech kliknutí v Canvě.',
          2 => 'Jen osobní pocit.',
        ),
        'correct' => 0,
        'why' => 'Obhajoba spojuje řešení s cílem a design principy.',
      ),
    ),
    'ui-spacing-system' => 
    array (
      'time' => '9–12 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => '4',
            1 => 'micro',
          ),
          1 => 
          array (
            0 => '8',
            1 => 'inside',
          ),
          2 => 
          array (
            0 => '16',
            1 => 'group',
          ),
          3 => 
          array (
            0 => '24',
            1 => 'section',
          ),
          4 => 
          array (
            0 => '32',
            1 => 'major',
          ),
        ),
      ),
      'mental' => 'Tokeny zmenšují počet náhodných rozhodnutí a vytvářejí rytmus.',
      'steps' => 
      array (
        0 => 'Vyber základní krok.',
        1 => 'Definuj 4–6 hodnot.',
        2 => 'Přiřaď role.',
        3 => 'Použij systém na cards.',
        4 => 'Audituj výjimky.',
      ),
      'mistakes' => 
      array (
        0 => '13, 17, 23, 29 px bez důvodu.',
        1 => 'Stejná mezera uvnitř i mezi sekcemi.',
        2 => 'Tokeny bez názvu/role.',
      ),
      'check' => 
      array (
        'q' => 'Proč používat spacing tokeny?',
        'options' => 
        array (
          0 => 'Kvůli konzistenci a rychlejším rozhodnutím.',
          1 => 'Aby všechny mezery byly stejné.',
          2 => 'Aby UI mělo více čísel.',
        ),
        'correct' => 0,
        'why' => 'Tokeny jsou omezená sada smysluplných hodnot.',
      ),
    ),
    'component-consistency' => 
    array (
      'time' => '10–14 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Component',
            1 => 'Button',
          ),
          1 => 
          array (
            0 => 'Variant',
            1 => 'Primary',
          ),
          2 => 
          array (
            0 => 'State',
            1 => 'Hover',
          ),
          3 => 
          array (
            0 => 'Token',
            1 => 'padding/radius/type',
          ),
        ),
      ),
      'mental' => 'Komponenta drží stejné významové role ve stejném vizuálním pravidle.',
      'steps' => 
      array (
        0 => 'Definuj anatomy.',
        1 => 'Nastav tokeny.',
        2 => 'Vytvoř varianty.',
        3 => 'Vytvoř states.',
        4 => 'Použij v reálném layoutu.',
      ),
      'mistakes' => 
      array (
        0 => 'Každý button jiný radius.',
        1 => 'Disabled bez kontrastního rozlišení.',
        2 => 'Varianty bez významu.',
      ),
      'check' => 
      array (
        'q' => 'Co je správná varianta komponenty?',
        'options' => 
        array (
          0 => 'Primary/secondary podle významu.',
          1 => 'Náhodný gradient pro každou instanci.',
          2 => 'Jiná výška každého tlačítka.',
        ),
        'correct' => 0,
        'why' => 'Varianty mají odpovídat funkčním rolím.',
      ),
    ),
    'microinteraction-storyboard' => 
    array (
      'time' => '9–13 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Trigger',
            1 => 'click',
          ),
          1 => 
          array (
            0 => 'Transition',
            1 => 'loading',
          ),
          2 => 
          array (
            0 => 'Feedback',
            1 => 'success',
          ),
          3 => 
          array (
            0 => 'Rest',
            1 => 'stable state',
          ),
        ),
      ),
      'mental' => 'Mikrointerakce komunikuje změnu stavu v čase.',
      'steps' => 
      array (
        0 => 'Definuj trigger.',
        1 => 'Urči očekávání uživatele.',
        2 => 'Navrhni přechod.',
        3 => 'Navrhni feedback.',
        4 => 'Ověř fallback bez animace.',
      ),
      'mistakes' => 
      array (
        0 => 'Animace bez funkce.',
        1 => 'Příliš dlouhý delay.',
        2 => 'Stav pochopitelný jen přes pohyb.',
      ),
      'check' => 
      array (
        'q' => 'Co je dobrá mikrointerakce?',
        'options' => 
        array (
          0 => 'Krátký feedback na konkrétní akci.',
          1 => 'Náhodné poskakování prvků.',
          2 => 'Pětisekundový přechod při každém hoveru.',
        ),
        'correct' => 0,
        'why' => 'Pohyb má pomáhat orientaci a potvrdit stav.',
      ),
    ),
    'portfolio-case-study' => 
    array (
      'time' => '10–15 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Problem',
            1 => 'brief',
          ),
          1 => 
          array (
            0 => 'Process',
            1 => 'wireframe',
          ),
          2 => 
          array (
            0 => 'Decision',
            1 => 'why',
          ),
          3 => 
          array (
            0 => 'Result',
            1 => 'final',
          ),
          4 => 
          array (
            0 => 'Reflection',
            1 => 'next',
          ),
        ),
      ),
      'mental' => 'Case study ukazuje schopnost rozhodovat, ne jen schopnost exportovat obrázek.',
      'steps' => 
      array (
        0 => 'Zkrať problém na 2 věty.',
        1 => 'Vyber 2 důležitá rozhodnutí.',
        2 => 'Ukaž relevantní proces.',
        3 => 'Ukaž výsledek.',
        4 => 'Přidej reflexi.',
      ),
      'mistakes' => 
      array (
        0 => 'Galerie bez kontextu.',
        1 => 'Deset screenshotů editoru.',
        2 => 'Text plný prázdných superlativů.',
      ),
      'check' => 
      array (
        'q' => 'Co je jádro case study?',
        'options' => 
        array (
          0 => 'Problém, rozhodnutí a výsledek.',
          1 => 'Seznam použitých kláves.',
          2 => 'Pouze finální mockup.',
        ),
        'correct' => 0,
        'why' => 'Portfolio dokazuje způsob řešení problému.',
      ),
    ),
    'accessibility-design' => 
    array (
      'time' => '11–15 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Contrast',
            1 => 'text/background',
            2 => 'čitelnost',
          ),
          1 => 
          array (
            0 => 'Size',
            1 => 'text + target',
            2 => 'rozpoznání',
          ),
          2 => 
          array (
            0 => 'Focus',
            1 => 'keyboard',
            2 => 'orientace',
          ),
          3 => 
          array (
            0 => 'State',
            1 => 'icon + text',
            2 => 'význam',
          ),
        ),
      ),
      'mental' => 'Přístupnost je funkční kvalita návrhu. Dobrý vizuál nesmí záviset na ideálním zraku, displeji nebo způsobu ovládání.',
      'steps' => 
      array (
        0 => 'Změř kontrast.',
        1 => 'Ověř velikost textu.',
        2 => 'Přidej viditelný focus.',
        3 => 'Ověř stav bez barvy.',
        4 => 'Zkontroluj grayscale a zoom.',
      ),
      'mistakes' => 
      array (
        0 => 'Šedý text na světle šedém pozadí.',
        1 => 'Focus odstraněný bez náhrady.',
        2 => 'Error = pouze červená barva.',
      ),
      'check' => 
      array (
        'q' => 'Která úprava nejlépe zvyšuje přístupnost tlačítka?',
        'options' => 
        array (
          0 => 'Dostatečný kontrast + viditelný focus + srozumitelný label.',
          1 => 'Pouze výraznější gradient.',
          2 => 'Menší text, aby se vešlo více obsahu.',
        ),
        'correct' => 0,
        'why' => 'Přístupnost kombinuje čitelnost, ovladatelnost a srozumitelný stav.',
      ),
    ),
    'responsive-type-ii' => 
    array (
      'time' => '13–17 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Desktop',
          1 => '64 / 22 / 18',
        ),
        'right' => 
        array (
          0 => 'Mobile',
          1 => '40 / 20 / 17',
        ),
      ),
      'mental' => 'Role zůstávají stejné, ale jejich velikost a zalomení se přizpůsobují dostupnému prostoru.',
      'steps' => 
      array (
        0 => 'Definuj min/max pro role.',
        1 => 'Otestuj desktop.',
        2 => 'Zúž viewport.',
        3 => 'Oprav wrap a line-height.',
        4 => 'Ověř hierarchii na mobilu.',
      ),
      'mistakes' => 
      array (
        0 => 'Proporcionálně zmenšit úplně vše.',
        1 => 'Body text pod čitelné minimum.',
        2 => 'Ignorovat zalomení headline.',
      ),
      'check' => 
      array (
        'q' => 'Co se má při změně viewportu zachovat?',
        'options' => 
        array (
          0 => 'Role a priorita textu, ne nutně stejná velikost nebo počet řádků.',
          1 => 'Přesná pixelová velikost.',
          2 => 'Stejný počet znaků na řádku.',
        ),
        'correct' => 0,
        'why' => 'Responsivní typografie chrání hierarchii a čitelnost.',
      ),
    ),
    'design-tokens-ii' => 
    array (
      'time' => '13–18 min',
      'level' => 'Střední+',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Primitive',
            1 => 'blue-600 / 16',
          ),
          1 => 
          array (
            0 => 'Semantic',
            1 => 'action-primary / space-m',
          ),
          2 => 
          array (
            0 => 'Component',
            1 => 'Button / Card',
          ),
          3 => 
          array (
            0 => 'Product',
            1 => 'Screen',
          ),
        ),
      ),
      'mental' => 'Token vytváří vrstvu mezi hodnotou a konkrétním použitím.',
      'steps' => 
      array (
        0 => 'Odděl primitive a semantic tokeny.',
        1 => 'Pojmenuj token podle účelu.',
        2 => 'Napoj komponentu.',
        3 => 'Vytvoř varianty.',
        4 => 'Změň token a sleduj dopad.',
      ),
      'mistakes' => 
      array (
        0 => 'Token nazvaný podle náhodné hodnoty.',
        1 => 'Lokální override na každé komponentě.',
        2 => 'Duplikovat komponentu pro každý stav.',
      ),
      'check' => 
      array (
        'q' => 'Proč je semantic token užitečný?',
        'options' => 
        array (
          0 => 'Popisuje účel a umožní měnit hodnotu bez přepisování všech komponent.',
          1 => 'Zvyšuje počet barev.',
          2 => 'Nahrazuje potřebu komponent.',
        ),
        'correct' => 0,
        'why' => 'Sémantická vrstva odděluje designové rozhodnutí od konkrétní hodnoty.',
      ),
    ),
    'interaction-patterns-ii' => 
    array (
      'time' => '12–17 min',
      'level' => 'Střední+',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Default',
            1 => 'ready',
          ),
          1 => 
          array (
            0 => 'Loading',
            1 => 'čekám',
          ),
          2 => 
          array (
            0 => 'Success',
            1 => 'potvrzení',
          ),
          3 => 
          array (
            0 => 'Error',
            1 => 'náprava',
          ),
        ),
      ),
      'mental' => 'Interakce je stavový model. Motion pouze pomáhá vysvětlit přechod mezi stavy.',
      'steps' => 
      array (
        0 => 'Sepiš všechny stavy.',
        1 => 'Definuj trigger.',
        2 => 'Přidej feedback.',
        3 => 'Ověř reduced motion.',
        4 => 'Zkontroluj stav bez barvy.',
      ),
      'mistakes' => 
      array (
        0 => 'Hover jako jediný stav.',
        1 => 'Nekonečná dekorativní animace.',
        2 => 'Error jen červenou.',
      ),
      'check' => 
      array (
        'q' => 'Kdy je motion užitečný?',
        'options' => 
        array (
          0 => 'Když vysvětluje změnu stavu nebo potvrzuje akci.',
          1 => 'Když je co nejdelší.',
          2 => 'Když nahrazuje text chyby.',
        ),
        'correct' => 0,
        'why' => 'Motion má zlepšit porozumění a feedback.',
      ),
    ),
    'design-critique-ii' => 
    array (
      'time' => '12–16 min',
      'level' => 'Pokročilé',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Slabá kritika',
          1 => '„nelíbí se mi“',
        ),
        'right' => 
        array (
          0 => 'Silná kritika',
          1 => 'cíl → evidence → rozhodnutí',
        ),
      ),
      'mental' => 'Profesionální kritika hodnotí návrh vůči cíli a důkazům, ne osobnímu vkusu.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl.',
        1 => 'Popiš pozorovaný problém.',
        2 => 'Přidej evidence.',
        3 => 'Navrhni konkrétní změnu.',
        4 => 'Dokumentuj handoff.',
      ),
      'mistakes' => 
      array (
        0 => 'Komentovat bez cíle.',
        1 => 'Používat jen preference.',
        2 => 'Handoff = screenshot.',
      ),
      'check' => 
      array (
        'q' => 'Co je nejsilnější design argument?',
        'options' => 
        array (
          0 => 'Rozhodnutí navázané na cíl a ověřitelnou evidenci.',
          1 => '„Mně se to líbí víc“.',
          2 => 'Co nejvíce efektů.',
        ),
        'correct' => 0,
        'why' => 'Argumentace musí být přenositelná a testovatelná.',
      ),
    ),
    'information-architecture-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Content inventory je vstup; následně obsah seskupuj podl',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Sitemap popisuje hierarchii, user flow popisuje konkrétn',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Test struktury může proběhnout i bez finálního vizuálu.',
          ),
        ),
      ),
      'mental' => 'IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.',
      'steps' => 
      array (
        0 => 'Content inventory je vstup; následně obsah seskupuj podle významu a úkolů.',
        1 => 'Sitemap popisuje hierarchii, user flow popisuje konkrétní cestu za cílem.',
        2 => 'Test struktury může proběhnout i bez finálního vizuálu.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Informační architektura II“?',
        'options' => 
        array (
          0 => 'IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Content inventory je vstup; následně obsah seskupuj podle významu a úkolů.',
      ),
    ),
    'form-states-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Error musí vést k opravě a zachovat správně vyplněná dat',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Loading chrání uživatele před nejistotou a dvojím odeslá',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Focus order a keyboard flow jsou součást návrhu.',
          ),
        ),
      ),
      'mental' => 'Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.',
      'steps' => 
      array (
        0 => 'Error musí vést k opravě a zachovat správně vyplněná data.',
        1 => 'Loading chrání uživatele před nejistotou a dvojím odesláním.',
        2 => 'Focus order a keyboard flow jsou součást návrhu.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Form UX a stavový model“?',
        'options' => 
        array (
          0 => 'Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Error musí vést k opravě a zachovat správně vyplněná data.',
      ),
    ),
    'auto-layout-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Rozliš hug, fill a fixed podle role prvku.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Gap a padding jsou jiné veličiny.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Varianty mají popisovat skutečné stavy/velikosti, ne nah',
          ),
        ),
      ),
      'mental' => 'Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.',
      'steps' => 
      array (
        0 => 'Rozliš hug, fill a fixed podle role prvku.',
        1 => 'Gap a padding jsou jiné veličiny.',
        2 => 'Varianty mají popisovat skutečné stavy/velikosti, ne nahodilé kopie komponent.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Auto Layout a varianty II“?',
        'options' => 
        array (
          0 => 'Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Rozliš hug, fill a fixed podle role prvku.',
      ),
    ),
    'accessibility-audit-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Barva nesmí být jediným nositelem významu.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Keyboard focus musí být viditelný a ovládání musí mít sm',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Při zoom/reflow nesmí klíčový obsah zmizet nebo být zakr',
          ),
        ),
      ),
      'mental' => 'Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.',
      'steps' => 
      array (
        0 => 'Barva nesmí být jediným nositelem významu.',
        1 => 'Keyboard focus musí být viditelný a ovládání musí mít smysluplné pořadí.',
        2 => 'Při zoom/reflow nesmí klíčový obsah zmizet nebo být zakrytý.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Accessibility audit II“?',
        'options' => 
        array (
          0 => 'Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Barva nesmí být jediným nositelem významu.',
      ),
    ),
    'html-css-handoff-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Statický screenshot nepopisuje responsive chování ani st',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Tokeny snižují množství náhodných lokálních hodnot.',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Komponenta potřebuje obsahové hranice a edge cases, neje',
          ),
        ),
      ),
      'mental' => 'Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.',
      'steps' => 
      array (
        0 => 'Statický screenshot nepopisuje responsive chování ani stavy.',
        1 => 'Tokeny snižují množství náhodných lokálních hodnot.',
        2 => 'Komponenta potřebuje obsahové hranice a edge cases, nejen ideální demo.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Design → HTML/CSS handoff“?',
        'options' => 
        array (
          0 => 'Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Statický screenshot nepopisuje responsive chování ani stavy.',
      ),
    ),
    'css-responsive-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro d',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Max-width chrání čitelnost a kompozici na velkých obrazo',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Breakpoint vybírej podle chování obsahu.',
          ),
        ),
      ),
      'mental' => 'Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.',
      'steps' => 
      array (
        0 => 'Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro dvourozměrné oblasti.',
        1 => 'Max-width chrání čitelnost a kompozici na velkých obrazovkách.',
        2 => 'Breakpoint vybírej podle chování obsahu.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „CSS layout mindset“?',
        'options' => 
        array (
          0 => 'Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Flexbox je vhodný pro tok v jedné hlavní ose; Grid pro dvourozměrné oblasti.',
      ),
    ),
    'usability-test-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Úkol popisuje cíl, ne postup.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Pozorování zapisuj jako chování, interpretaci až následn',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Prioritizuj problém podle dopadu a četnosti, ne podle os',
          ),
        ),
      ),
      'mental' => 'Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.',
      'steps' => 
      array (
        0 => 'Úkol popisuje cíl, ne postup.',
        1 => 'Pozorování zapisuj jako chování, interpretaci až následně.',
        2 => 'Prioritizuj problém podle dopadu a četnosti, ne podle osobního vkusu.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Usability test II“?',
        'options' => 
        array (
          0 => 'Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Úkol popisuje cíl, ne postup.',
      ),
    ),
    'design-qa-handoff-ii' => 
    array (
      'time' => '10–16 min',
      'level' => 'Střední',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Issue popisuje steps, expected, actual a severity.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Nejdřív řeš funkční a accessibility dopad, potom kosmeti',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Po opravě zopakuj původní scénář a teprve potom issue uz',
          ),
        ),
      ),
      'mental' => 'Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.',
      'steps' => 
      array (
        0 => 'Issue popisuje steps, expected, actual a severity.',
        1 => 'Nejdřív řeš funkční a accessibility dopad, potom kosmetické odchylky.',
        2 => 'Po opravě zopakuj původní scénář a teprve potom issue uzavři.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Design QA a handoff II“?',
        'options' => 
        array (
          0 => 'Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Issue popisuje steps, expected, actual a severity.',
      ),
    ),
    'card-sorting' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Card sorting pomáhá ověřit, jak lidé přirozeně seskupují informace.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'error-recovery-ux' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Chyba má vysvětlit problém, ukázat opravu a zachovat co nejvíc uživatelovy práce.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'interaction-accessibility' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Interakce musí fungovat klávesnicí, mít viditelný focus a nespoléhat jen na barvu či gesto.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'design-handoff-qa' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Handoff předává pravidla a chování; QA ověřuje implementaci proti cíli, ne proti jednomu screenshotu.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'spacing' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Bez rytmu',
          1 => '8 / 27 / 13 / 41',
          2 => 'vzdálenosti působí náhodně',
        ),
        'right' => 
        array (
          0 => 'Systém',
          1 => '8 / 16 / 24 / 32',
          2 => 'opakující se rytmus',
        ),
      ),
      'mental' => 'Spacing vytváří vztahy. Malá mezera říká „patříme k sobě“, větší mezera „začíná nová skupina“.',
      'steps' => 
      array (
        0 => 'Najdi obsahové skupiny.',
        1 => 'Zvol základní krok spacingu.',
        2 => 'Uvnitř skupiny používej menší mezery.',
        3 => 'Mezi skupinami použij větší mezery.',
        4 => 'Zkontroluj rytmus jako thumbnail.',
      ),
      'mistakes' => 
      array (
        0 => 'Každá mezera jiná bez důvodu.',
        1 => 'Vyplnit každý prázdný prostor.',
        2 => 'Zaměnit větší mezery za chybu kompozice.',
      ),
      'check' => 
      array (
        'q' => 'Které pravidlo je dobrý start?',
        'options' => 
        array (
          0 => 'Příbuzné prvky blíž, různé skupiny dál.',
          1 => 'Všechny mezery stejně malé.',
          2 => 'Prázdné místo vždy vyplnit.',
        ),
        'correct' => 0,
        'why' => 'Proximity pomáhá oku poznat obsahové skupiny.',
      ),
    ),
    'image-crop' => 
    array (
      'time' => '8–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Náhodný crop',
          1 => 'subjekt uříznutý + text přes detail',
          2 => 'pozornost nemá jasný cíl',
        ),
        'right' => 
        array (
          0 => 'Řízený crop',
          1 => 'focal point + klidná textová zóna',
          2 => 'obraz a text spolupracují',
        ),
      ),
      'mental' => 'Crop není technický detail. Určuje, co je nejdůležitější a kde vznikne prostor pro text.',
      'steps' => 
      array (
        0 => 'Najdi focal point.',
        1 => 'Urči, kde musí být text.',
        2 => 'Vyber crop pro konkrétní poměr stran.',
        3 => 'Ověř, že důležitá část obrazu zůstala celá.',
        4 => 'Zkontroluj kontrast textové zóny.',
      ),
      'mistakes' => 
      array (
        0 => 'Automatický crop bez kontroly.',
        1 => 'Text přes obličej nebo nejdetailnější část.',
        2 => 'Stejný crop pro portrait i square.',
      ),
      'check' => 
      array (
        'q' => 'Co řešíš jako první při ořezu?',
        'options' => 
        array (
          0 => 'Focal point a cílový formát.',
          1 => 'Exportní název souboru.',
          2 => 'Počet vrstev.',
        ),
        'correct' => 0,
        'why' => 'Výřez musí chránit hlavní obsah a fungovat v konkrétním poměru stran.',
      ),
    ),
    'cta' => 
    array (
      'time' => '6–9 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'compare',
        'left' => 
        array (
          0 => 'Slabé CTA',
          1 => 'více informací',
          2 => 'nejasná akce',
        ),
        'right' => 
        array (
          0 => 'Jasné CTA',
          1 => 'REGISTRUJ SE →',
          2 => 'konkrétní další krok',
        ),
      ),
      'mental' => 'CTA uzavírá cestu oka: už vím, co se děje — teď vím, co mám udělat.',
      'steps' => 
      array (
        0 => 'Definuj jednu hlavní akci.',
        1 => 'Napiš ji slovesem.',
        2 => 'Dej CTA dostatečný kontrast.',
        3 => 'Nech ho až po hlavní informaci.',
        4 => 'Otestuj, zda ho najdeš během 3 sekund.',
      ),
      'mistakes' => 
      array (
        0 => 'Tři stejně silná CTA.',
        1 => 'Nejasný text „klikni zde“.',
        2 => 'CTA jako nejméně čitelný prvek.',
      ),
      'check' => 
      array (
        'q' => 'Které CTA je nejsrozumitelnější?',
        'options' => 
        array (
          0 => 'REGISTRUJ SE →',
          1 => 'Více zde',
          2 => 'Něco udělej',
        ),
        'correct' => 0,
        'why' => 'Konkrétní sloveso říká, jaká akce následuje.',
      ),
    ),
    'preflight' => 
    array (
      'time' => '7–10 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'export',
      ),
      'mental' => 'Preflight znamená zastavit se před odevzdáním a zkontrolovat skutečný výstup, ne jen editor.',
      'steps' => 
      array (
        0 => 'Zkontroluj obsah a údaje.',
        1 => 'Zkontroluj rozměr a formát.',
        2 => 'Ověř čitelnost a okraje.',
        3 => 'Otevři export mimo editor.',
        4 => 'Pojmenuj soubor a teprve potom odevzdej.',
      ),
      'mistakes' => 
      array (
        0 => 'Odevzdat screenshot.',
        1 => 'Neotevřít finální export.',
        2 => 'Přehlédnout špatné datum nebo QR.',
      ),
      'check' => 
      array (
        'q' => 'Co je poslední krok před odevzdáním?',
        'options' => 
        array (
          0 => 'Otevřít a zkontrolovat skutečný export.',
          1 => 'Přidat další efekt.',
          2 => 'Smazat zdroj.',
        ),
        'correct' => 0,
        'why' => 'Výsledkem je exportovaný soubor, ne pracovní plocha editoru.',
      ),
    ),
    'color-harmony' => 
    array (
      'time' => '9–12 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Background',
            1 => 'největší plocha',
          ),
          1 => 
          array (
            0 => 'Text',
            1 => 'čitelnost',
          ),
          2 => 
          array (
            0 => 'Accent',
            1 => 'pozornost',
          ),
          3 => 
          array (
            0 => 'State',
            1 => 'význam',
          ),
        ),
      ),
      'mental' => 'Barva funguje nejlépe, když má jasnou roli a neopakuje se bez důvodu.',
      'steps' => 
      array (
        0 => 'Vyber neutrální nebo klidný background.',
        1 => 'Nastav čitelný text.',
        2 => 'Přidej jeden accent.',
        3 => 'Zkontroluj grayscale.',
        4 => 'Ověř CTA.',
      ),
      'mistakes' => 
      array (
        0 => 'Pět akcentních barev.',
        1 => 'Paleta bez textového kontrastu.',
        2 => 'Použít accent na všechno.',
      ),
      'check' => 
      array (
        'q' => 'Která paleta má nejjasnější strukturu?',
        'options' => 
        array (
          0 => 'Background + text + jeden accent.',
          1 => 'Šest stejně silných barev.',
          2 => 'Každý blok jinou barvou.',
        ),
        'correct' => 0,
        'why' => 'Role barev snižují vizuální šum.',
      ),
    ),
    'iconography' => 
    array (
      'time' => '8–11 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Meaning',
            1 => 'datum',
          ),
          1 => 
          array (
            0 => 'Icon family',
            1 => 'outline',
          ),
          2 => 
          array (
            0 => 'Size',
            1 => '24 px',
          ),
          3 => 
          array (
            0 => 'Label',
            1 => '12. 10.',
          ),
        ),
      ),
      'mental' => 'Ikona je součást informačního systému. Musí být stylově i významově čitelná.',
      'steps' => 
      array (
        0 => 'Vyber jednu rodinu ikon.',
        1 => 'Sjednoť optickou velikost.',
        2 => 'Použij konzistentní barvu/stroke.',
        3 => 'Přidej label u nejasné ikony.',
        4 => 'Ověř v thumbnailu.',
      ),
      'mistakes' => 
      array (
        0 => 'Emoji + 3D + outline dohromady.',
        1 => 'Různé tloušťky.',
        2 => 'Ikona místo důležitého textu bez labelu.',
      ),
      'check' => 
      array (
        'q' => 'Co nejvíc vytváří konzistenci ikon?',
        'options' => 
        array (
          0 => 'Společná kresba a optická velikost.',
          1 => 'Náhodné efekty.',
          2 => 'Každá jiná perspektiva.',
        ),
        'correct' => 0,
        'why' => 'Opakovatelné vizuální vlastnosti tvoří systém.',
      ),
    ),
    'image-composition' => 
    array (
      'time' => '9–13 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Photo',
            1 => 'focal point',
          ),
          1 => 
          array (
            0 => 'Crop',
            1 => 'priorita',
          ),
          2 => 
          array (
            0 => 'Quiet area',
            1 => 'text',
          ),
          3 => 
          array (
            0 => 'Contrast',
            1 => 'čitelnost',
          ),
        ),
      ),
      'mental' => 'Crop je rozhodnutí o hierarchii obrazu, ne jen technické oříznutí.',
      'steps' => 
      array (
        0 => 'Najdi focal point.',
        1 => 'Vyzkoušej portrait/square crop.',
        2 => 'Vytvoř klidovou zónu pro text.',
        3 => 'Ověř lokální kontrast.',
        4 => 'Porovnej thumbnail.',
      ),
      'mistakes' => 
      array (
        0 => 'Uříznout důležitou část.',
        1 => 'Text přes nejdetailnější místo.',
        2 => 'Stejný crop pro všechny formáty.',
      ),
      'check' => 
      array (
        'q' => 'Co je dobrý crop pro plakát s textem?',
        'options' => 
        array (
          0 => 'Zachová focal point a vytvoří prostor pro text.',
          1 => 'Vždy vycentruje všechno.',
          2 => 'Použije nejvíc detailů.',
        ),
        'correct' => 0,
        'why' => 'Crop podporuje hlavní sdělení a čitelnost.',
      ),
    ),
    'template-critique' => 
    array (
      'time' => '10–14 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Cíl',
            1 => 'co má divák pochopit',
          ),
          1 => 
          array (
            0 => 'Symptom',
            1 => 'co nefunguje',
          ),
          2 => 
          array (
            0 => 'Příčina',
            1 => 'proč',
          ),
          3 => 
          array (
            0 => 'Oprava',
            1 => 'konkrétní změna',
          ),
          4 => 
          array (
            0 => 'Test',
            1 => 'thumbnail/contrast',
          ),
        ),
      ),
      'mental' => 'Kritika je diagnóza: problém → dopad → oprava → ověření.',
      'steps' => 
      array (
        0 => 'Napiš cíl.',
        1 => 'Najdi tři symptomy.',
        2 => 'Urči příčinu.',
        3 => 'Navrhni konkrétní změnu.',
        4 => 'Ověř before/after.',
      ),
      'mistakes' => 
      array (
        0 => '„Nelíbí se mi to“.',
        1 => 'Měnit styl bez znalosti cíle.',
        2 => 'Přidat další dekorace.',
      ),
      'check' => 
      array (
        'q' => 'Která kritika je použitelná?',
        'options' => 
        array (
          0 => 'Datum soupeří s titulkem; zmenším ho a seskupím s místem.',
          1 => 'Je to divné.',
          2 => 'Přidáme neon.',
        ),
        'correct' => 0,
        'why' => 'Popisuje konkrétní problém, důsledek i opravu.',
      ),
    ),
    'responsive-series' => 
    array (
      'time' => '10–14 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Master',
            1 => '1080×1350',
          ),
          1 => 
          array (
            0 => 'Square',
            1 => '1080×1080',
          ),
          2 => 
          array (
            0 => 'Story',
            1 => '1080×1920',
          ),
          3 => 
          array (
            0 => 'QA',
            1 => 'thumbnail + safe zone',
          ),
        ),
      ),
      'mental' => 'Nepřenášej souřadnice. Přenášej hierarchii, role a vizuální systém.',
      'steps' => 
      array (
        0 => 'Urči nezměnitelné prvky.',
        1 => 'Založ cílový formát.',
        2 => 'Přesuň hlavní hierarchii.',
        3 => 'Uprav crop a spacing.',
        4 => 'Ověř safe zone a thumbnail.',
      ),
      'mistakes' => 
      array (
        0 => 'Pouze oříznout master.',
        1 => 'Posunout CTA mimo bezpečnou oblast.',
        2 => 'Změnit v každém formátu jiný font nebo paletu.',
      ),
      'check' => 
      array (
        'q' => 'Co se má mezi formáty zachovat nejvíc?',
        'options' => 
        array (
          0 => 'Hierarchie, role a vizuální identita.',
          1 => 'Přesná pixelová pozice všech prvků.',
          2 => 'Stejný crop za každou cenu.',
        ),
        'correct' => 0,
        'why' => 'Formát se mění, ale systém a pořadí komunikace musí zůstat konzistentní.',
      ),
    ),
    'editorial-typography-i' => 
    array (
      'time' => '12–16 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Display',
            1 => '48 px',
            2 => 'dominanta',
          ),
          1 => 
          array (
            0 => 'Heading',
            1 => '30 px',
            2 => 'sekce',
          ),
          2 => 
          array (
            0 => 'Body',
            1 => '18 px',
            2 => 'čtení',
          ),
          3 => 
          array (
            0 => 'Meta',
            1 => '14 px',
            2 => 'detail',
          ),
        ),
      ),
      'mental' => 'Typografie je systém rolí, ne sbírka náhodných velikostí.',
      'steps' => 
      array (
        0 => 'Pojmenuj textové role.',
        1 => 'Sestav malou škálu velikostí.',
        2 => 'Nastav line-height.',
        3 => 'Ověř délku řádku.',
        4 => 'Porovnej návrh v malém náhledu.',
      ),
      'mistakes' => 
      array (
        0 => 'Příliš mnoho velikostí.',
        1 => 'Dlouhý řádek bez prostoru.',
        2 => 'Stejná váha všech rolí.',
      ),
      'check' => 
      array (
        'q' => 'Co nejlépe drží delší text čitelný?',
        'options' => 
        array (
          0 => 'Jasné role, rozumná délka řádku a konzistentní line-height.',
          1 => 'Co nejvíc fontů.',
          2 => 'Stejná velikost všech textů.',
        ),
        'correct' => 0,
        'why' => 'Čitelnost je kombinace hierarchie a rytmu.',
      ),
    ),
    'layout-rhythm-i' => 
    array (
      'time' => '11–15 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'grid',
        'cols' => 6,
        'label' => 'Rytmus 8 / 16 / 24 / 40',
      ),
      'mental' => 'Whitespace a opakovaný spacing vytvářejí strukturu stejně jako samotný grid.',
      'steps' => 
      array (
        0 => 'Vyber spacing základ.',
        1 => 'Seskup související prvky.',
        2 => 'Odděl sekce větší mezerou.',
        3 => 'Zarovnej hrany.',
        4 => 'Ověř rytmus bez dekorací.',
      ),
      'mistakes' => 
      array (
        0 => 'Každá mezera jiná.',
        1 => 'Vyplnit každé prázdné místo.',
        2 => 'Nesouvisející prvky příliš blízko.',
      ),
      'check' => 
      array (
        'q' => 'Kdy použít větší mezeru?',
        'options' => 
        array (
          0 => 'Mezi odlišnými skupinami nebo sekcemi.',
          1 => 'Mezi každými dvěma písmeny.',
          2 => 'Náhodně pro dynamiku.',
        ),
        'correct' => 0,
        'why' => 'Velikost mezery komunikuje vztah mezi prvky.',
      ),
    ),
    'visual-story-i' => 
    array (
      'time' => '12–16 min',
      'level' => 'Základ+',
      'visual' => 
      array (
        'type' => 'sequence',
        'items' => 
        array (
          0 => 
          array (
            0 => '01',
            1 => 'Focal point',
          ),
          1 => 
          array (
            0 => '02',
            1 => 'Kontext',
          ),
          2 => 
          array (
            0 => '03',
            1 => 'Detail',
          ),
          3 => 
          array (
            0 => '04',
            1 => 'CTA',
          ),
        ),
      ),
      'mental' => 'I statický návrh má časovou osu: divák něco uvidí první a něco až potom.',
      'steps' => 
      array (
        0 => 'Urči první pohled.',
        1 => 'Nasměruj druhý krok.',
        2 => 'Odstraň konkurující dominantu.',
        3 => 'Ověř 3s test.',
        4 => 'Zkontroluj CTA.',
      ),
      'mistakes' => 
      array (
        0 => 'Tři dominanty současně.',
        1 => 'Fotografie vede pohled mimo obsah.',
        2 => 'CTA nemá vizuální návaznost.',
      ),
      'check' => 
      array (
        'q' => 'Co je dobrý focal point?',
        'options' => 
        array (
          0 => 'Jasný první bod, který zahájí zamýšlenou sekvenci čtení.',
          1 => 'Nejbarevnější prvek bez ohledu na obsah.',
          2 => 'Každý prvek stejně výrazný.',
        ),
        'correct' => 0,
        'why' => 'Focal point má zahájit příběh, ne jen poutat pozornost.',
      ),
    ),
    'production-preflight-i' => 
    array (
      'time' => '10–14 min',
      'level' => 'Praktické',
      'visual' => 
      array (
        'type' => 'export',
        'formats' => 
        array (
          0 => 'master',
          1 => 'web',
          2 => 'print',
        ),
        'label' => 'Preflight workflow',
      ),
      'mental' => 'Kvalitní návrh končí až tehdy, když je správně pojmenovaný, zkontrolovaný a exportovaný.',
      'steps' => 
      array (
        0 => 'Odděl master od exportů.',
        1 => 'Použij verzování.',
        2 => 'Ověř rozměry a obsah.',
        3 => 'Zvol preset podle cíle.',
        4 => 'Otevři export a zkontroluj ho.',
      ),
      'mistakes' => 
      array (
        0 => 'final_final2.png.',
        1 => 'Export bez kontroly.',
        2 => 'Přepsat master exportem.',
      ),
      'check' => 
      array (
        'q' => 'Co patří do preflightu?',
        'options' => 
        array (
          0 => 'Rozměr, obsah, crop, kontrast, název a správný export.',
          1 => 'Pouze kontrola názvu souboru.',
          2 => 'Jen přidání dalšího efektu.',
        ),
        'correct' => 0,
        'why' => 'Preflight je poslední kontrola funkčnosti před předáním.',
      ),
    ),
    'web-layout-basics-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Header pomáhá orientaci, hero rychle vysvětluje hodnotu ',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Nezačínej dekoracemi. Nejprve určuj priority obsahu a vz',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Dobrá stránka zůstává pochopitelná i jako jednoduchý čer',
          ),
        ),
      ),
      'mental' => 'Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.',
      'steps' => 
      array (
        0 => 'Header pomáhá orientaci, hero rychle vysvětluje hodnotu a hlavní obsah vede uživatele k cíli.',
        1 => 'Nezačínej dekoracemi. Nejprve určuj priority obsahu a vztahy mezi bloky.',
        2 => 'Dobrá stránka zůstává pochopitelná i jako jednoduchý černobílý wireframe.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Anatomie webové stránky“?',
        'options' => 
        array (
          0 => 'Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Header pomáhá orientaci, hero rychle vysvětluje hodnotu a hlavní obsah vede uživatele k cíli.',
      ),
    ),
    'responsive-layout-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Mobile není zmenšený desktop. Často potřebuje jiné pořad',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Breakpoint dává smysl ve chvíli, kdy obsah nebo komponen',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Testuj také mezilehlé šířky, nejen dvě předem připravené',
          ),
        ),
      ),
      'mental' => 'Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.',
      'steps' => 
      array (
        0 => 'Mobile není zmenšený desktop. Často potřebuje jiné pořadí, kratší headline nebo změnu kolony na stack.',
        1 => 'Breakpoint dává smysl ve chvíli, kdy obsah nebo komponenta přestává fungovat.',
        2 => 'Testuj také mezilehlé šířky, nejen dvě předem připravené obrazovky.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Responsive layout I“?',
        'options' => 
        array (
          0 => 'Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Mobile není zmenšený desktop. Často potřebuje jiné pořadí, kratší headline nebo změnu kolony na stack.',
      ),
    ),
    'components-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Tlačítko má roli, variantu a stavy. Karta má jasnou vnit',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Konzistence neznamená, že vše vypadá stejně; znamená, že',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Stav focus, disabled nebo error je stejně důležitý jako ',
          ),
        ),
      ),
      'mental' => 'Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.',
      'steps' => 
      array (
        0 => 'Tlačítko má roli, variantu a stavy. Karta má jasnou vnitřní strukturu a opakovatelný spacing.',
        1 => 'Konzistence neznamená, že vše vypadá stejně; znamená, že podobné věci používají stejná pravidla.',
        2 => 'Stav focus, disabled nebo error je stejně důležitý jako default.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Komponenty I: opakovatelné UI vzory“?',
        'options' => 
        array (
          0 => 'Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Tlačítko má roli, variantu a stavy. Karta má jasnou vnitřní strukturu a opakovatelný spacing.',
      ),
    ),
    'web-typography-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Používej malý počet pojmenovaných textových rolí.',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Čitelnost ovlivňuje velikost, line-height, délka řádku i',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Headline musí být testovaný i na mobilu, kde se zalomení',
          ),
        ),
      ),
      'mental' => 'Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.',
      'steps' => 
      array (
        0 => 'Používej malý počet pojmenovaných textových rolí.',
        1 => 'Čitelnost ovlivňuje velikost, line-height, délka řádku i kontrast.',
        2 => 'Headline musí být testovaný i na mobilu, kde se zalomení může dramaticky změnit.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Webová typografie I“?',
        'options' => 
        array (
          0 => 'Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Používej malý počet pojmenovaných textových rolí.',
      ),
    ),
    'image-web-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Jeden master může potřebovat více cropů pro různé poměry',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Rozměr exportu má odpovídat zobrazované ploše; zbytečně ',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Informační obraz potřebuje textovou alternativu; čistě d',
          ),
        ),
      ),
      'mental' => 'Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.',
      'steps' => 
      array (
        0 => 'Jeden master může potřebovat více cropů pro různé poměry stran.',
        1 => 'Rozměr exportu má odpovídat zobrazované ploše; zbytečně velký originál zvyšuje datovou zátěž.',
        2 => 'Informační obraz potřebuje textovou alternativu; čistě dekorativní obraz nesmí nést jedinou důležitou informaci.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Obraz a assety pro web“?',
        'options' => 
        array (
          0 => 'Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Jeden master může potřebovat více cropů pro různé poměry stran.',
      ),
    ),
    'forms-a11y-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Label popisuje význam pole a zůstává čitelný i po zadání',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Chyba má být konkrétní a nemá být signalizovaná jen barv',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Focus pomáhá uživateli chápat, který prvek právě ovládá.',
          ),
        ),
      ),
      'mental' => 'Formulář musí být srozumitelný v defaultu, při focusu i při chybě.',
      'steps' => 
      array (
        0 => 'Label popisuje význam pole a zůstává čitelný i po zadání hodnoty.',
        1 => 'Chyba má být konkrétní a nemá být signalizovaná jen barvou.',
        2 => 'Focus pomáhá uživateli chápat, který prvek právě ovládá.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Formuláře a přístupnost I“?',
        'options' => 
        array (
          0 => 'Formulář musí být srozumitelný v defaultu, při focusu i při chybě.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Label popisuje význam pole a zůstává čitelný i po zadání hodnoty.',
      ),
    ),
    'design-system-i' => 
    array (
      'time' => '10–16 min',
      'level' => 'Základ',
      'visual' => 
      array (
        'type' => 'layers',
        'items' => 
        array (
          0 => 
          array (
            0 => 'Krok 1',
            1 => 'Začni typografií, barvami a spacingem; teprve potom sklá',
          ),
          1 => 
          array (
            0 => 'Krok 2',
            1 => 'Token pojmenuj podle účelu, pokud má fungovat napříč pro',
          ),
          2 => 
          array (
            0 => 'Krok 3',
            1 => 'Dokumentace má ukázat správné použití i typickou chybu.',
          ),
        ),
      ),
      'mental' => 'Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.',
      'steps' => 
      array (
        0 => 'Začni typografií, barvami a spacingem; teprve potom skládej komponenty.',
        1 => 'Token pojmenuj podle účelu, pokud má fungovat napříč produktem.',
        2 => 'Dokumentace má ukázat správné použití i typickou chybu.',
        3 => 'Použij princip na krátkém příkladu nebo labu.',
        4 => 'Vysvětli vlastními slovy, jaký důkaz bys hledal/a.',
      ),
      'mistakes' => 
      array (
        0 => 'Přeskočit přímo k řešení bez modelu.',
        1 => 'Zaměnit pozorování za domněnku.',
        2 => 'Neověřit výsledek po změně.',
      ),
      'check' => 
      array (
        'q' => 'Co je hlavní princip tématu „Mini design systém I“?',
        'options' => 
        array (
          0 => 'Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.',
          1 => 'Stačí si zapamatovat název nástroje bez kontextu.',
          2 => 'Nejdůležitější je provést co nejvíc změn najednou.',
        ),
        'correct' => 0,
        'why' => 'Začni typografií, barvami a spacingem; teprve potom skládej komponenty.',
      ),
    ),
    'content-first-layout' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Nejdřív urči obsahovou prioritu, až potom kresli boxy a dekorace.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'microcopy-cta' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Krátký text v rozhraní má říkat, co se stane a proč má uživatel pokračovat.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'responsive-art-direction' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Na různých formátech nemusí být stejný crop, ale význam a focal point musí zůstat.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
    'design-feedback' => 
    array (
      'time' => '8–12 min',
      'level' => 'Applied',
      'mental' => 'Užitečný feedback popisuje cíl, pozorování a dopad – ne osobní vkus.',
      'steps' => 
      array (
        0 => 'Pojmenuj cíl nebo problém jednou větou.',
        1 => 'Najdi jeden konkrétní prvek, na kterém je princip vidět.',
        2 => 'Změň pouze jednu věc a předpověz dopad.',
        3 => 'Ověř návrh v miniatuře, jiném viewportu nebo krátkým uživatelským testem.',
        4 => 'Vysvětli vlastními slovy, proč výsledek funguje lépe.',
      ),
      'mistakes' => 
      array (
        0 => 'Řešit vzhled dřív než cíl a obsah.',
        1 => 'Měnit několik věcí současně a nevědět, co pomohlo.',
        2 => 'Brát osobní vkus jako jediný důkaz kvality.',
      ),
      'visual' => 
      array (
        'type' => 'flow',
        'items' => 
        array (
          0 => 'Cíl / obsah',
          1 => 'Návrhové rozhodnutí',
          2 => 'Rychlý test',
          3 => 'Úprava',
          4 => 'Ověřený výsledek',
        ),
      ),
      'check' => 
      array (
        'q' => 'Který postup nejlépe odpovídá principu této lekce?',
        'options' => 
        array (
          0 => 'Nejdřív formulovat cíl/symptom, získat důkaz a teprve potom měnit řešení.',
          1 => 'Změnit několik věcí najednou a nechat si tu, která náhodou pomůže.',
          2 => 'Přeskočit ověření, pokud výsledek vypadá správně.',
        ),
        'correct' => 0,
        'why' => 'Správný postup je vysvětlitelný, ověřitelný a umožňuje poznat, která změna skutečně vedla k výsledku.',
      ),
    ),
  ),
  'nextLesson' => 
  array (
    'id' => 'graphics_type_image',
    'title' => 'Lekce 2 · Typografie + obraz: plakát bez šablony',
    'subtitle' => 'Dalších 2 × 45 minut',
    'goal' => 'Navázat na první plakát a vědomě pracovat s typografií, spacingem, fotografií a CTA. Na konci vznikne druhý Canva plakát, který student umí obhájit.',
    'unlock_note' => 'Odemkne se po dokončení Design Studia z prvního bloku.',
    'knowledge' => 
    array (
      0 => 'typography',
      1 => 'spacing',
      2 => 'image-crop',
      3 => 'cta',
      4 => 'preflight',
    ),
    'schedule' => 
    array (
      0 => 
      array (
        'time' => '0–10',
        'title' => 'Rekapitulace + typografie',
        'text' => 'Krátké porovnání první práce a Knowledge Tour typografie/spacing.',
      ),
      1 => 
      array (
        'time' => '10–25',
        'title' => 'Obraz a crop',
        'text' => 'Vyber fotografii, focal point a připrav textovou zónu.',
      ),
      2 => 
      array (
        'time' => '25–42',
        'title' => 'Typografický skeleton',
        'text' => 'Postav návrh bez dekorací: headline → info → CTA.',
      ),
      3 => 
      array (
        'time' => '42–45',
        'title' => 'Checkpoint',
        'text' => 'Ověř hierarchii a přejdi do druhé hodiny.',
      ),
      4 => 
      array (
        'time' => '45–67',
        'title' => 'Canva realizace',
        'text' => 'Doplň fotografii, spacing a styl, ale zachovej informační hierarchii.',
      ),
      5 => 
      array (
        'time' => '67–80',
        'title' => 'CTA + preflight',
        'text' => 'Otestuj thumbnail, export a přesnost údajů.',
      ),
      6 => 
      array (
        'time' => '80–90',
        'title' => 'A/B reflexe',
        'text' => 'Porovnej s prvním plakátem a napiš 3 konkrétní zlepšení.',
      ),
    ),
    'steps' => 
    array (
      0 => 
      array (
        'id' => 'type_spacing',
        'time' => '10 min',
        'title' => '01 · Typografie a spacing',
        'kind' => 'knowledge',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'typography',
          1 => 'spacing',
        ),
        'intro' => 'Než otevřeš Canvu, projdi dvě krátké lekce. Zaměř se na role textu a vztah mezer mezi skupinami.',
        'tasks' => 
        array (
          0 => 'Dokonči Knowledge Tour „Typografie“.',
          1 => 'Dokonči Knowledge Tour „Spacing a vizuální rytmus“.',
          2 => 'Napiš si tři role: headline / info / CTA.',
        ),
      ),
      1 => 
      array (
        'id' => 'crop',
        'time' => '15 min',
        'title' => '02 · Obraz, focal point a crop',
        'kind' => 'quiz',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'image-crop',
        ),
        'intro' => 'Pracuješ s fotografií koncertu. V portrait plakátu chceš text vlevo a hlavní osobu vpravo.',
        'demo' => 
        array (
          'label' => 'Představa layoutu',
          'lines' => 
          array (
            0 => '[ HEADLINE            ]   [ osoba ]',
            1 => '[ datum / místo      ]   [ osoba ]',
            2 => '[ CTA                ]   [ obraz ]',
          ),
        ),
        'question' => 'Jaký je nejlepší první krok?',
        'options' => 
        array (
          0 => 'Zvětšit fotografii tak, aby vyplnila vše bez ohledu na subjekt.',
          1 => 'Najít focal point a zvolit crop, který nechá vlevo klidnou textovou zónu.',
          2 => 'Přidat další tři efekty.',
        ),
        'correct' => 1,
        'explanation' => 'Nejdřív řešíš význam obrazu a prostor pro obsah. Efekty přijdou až potom.',
        'tasks' => 
        array (
          0 => 'Otevři Knowledge Tour „Výřez fotografie“.',
          1 => 'Vyber jednu vlastní nebo jasně licencovanou fotografii.',
          2 => 'Připrav portrait crop a označ textovou zónu.',
        ),
      ),
      2 => 
      array (
        'id' => 'skeleton',
        'time' => '17 min',
        'title' => '03 · Typografický skeleton',
        'kind' => 'manual',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'typography',
          1 => 'spacing',
        ),
        'intro' => 'V Canvě postav návrh pouze z textu a jednoho šedého obdélníku místo fotografie. Žádné efekty.',
        'tasks' => 
        array (
          0 => 'Headline musí být první čitelný prvek.',
          1 => 'Datum + místo tvoří jednu informační skupinu.',
          2 => 'CTA má jasný tvar a konkrétní sloveso.',
          3 => 'Použij maximálně dvě rodiny písem.',
          4 => 'Použij opakující se spacing místo náhodných mezer.',
        ),
        'done_label' => 'Skeleton splňuje všech 5 bodů',
      ),
      3 => 
      array (
        'id' => 'hierarchy_check',
        'time' => '3 min',
        'title' => '04 · Checkpoint před stylováním',
        'kind' => 'quiz',
        'xp' => 15,
        'intro' => 'Před přidáním fotografie a stylu zmenši návrh na malou miniaturu.',
        'question' => 'Co musí zůstat čitelné i v thumbnailu?',
        'options' => 
        array (
          0 => 'Hlavní zpráva a základní hierarchie.',
          1 => 'Všechny drobné dekorace.',
          2 => 'Názvy vrstev v editoru.',
        ),
        'correct' => 0,
        'explanation' => 'Thumbnail test odhalí, zda design funguje strukturou, ne jen detaily.',
      ),
      4 => 
      array (
        'id' => 'canva_finish',
        'time' => '22 min',
        'title' => '05 · Canva: obraz + styl + CTA',
        'kind' => 'manual',
        'xp' => 30,
        'knowledge' => 
        array (
          0 => 'cta',
        ),
        'intro' => 'Teď skeleton dokonči. Přidej fotografii a vlastní vizuální styl, ale každá změna musí podporovat hlavní sdělení.',
        'tasks' => 
        array (
          0 => 'Použij zvolený crop a zachovej focal point.',
          1 => 'Zkontroluj kontrast textu proti fotografii.',
          2 => 'CTA je viditelné, ale nepřebíjí headline.',
          3 => 'Dekorace nepřidávej, pokud nemají funkci.',
          4 => 'Proveď grayscale a 3sekundový test.',
        ),
        'done_label' => 'Finální vizuál prošel 5 kontrolami',
      ),
      5 => 
      array (
        'id' => 'preflight_reflection',
        'time' => '23 min',
        'title' => '06 · Preflight + porovnání',
        'kind' => 'quiz',
        'xp' => 30,
        'knowledge' => 
        array (
          0 => 'preflight',
        ),
        'intro' => 'Exportuj plakát a otevři skutečný soubor mimo Canvu. Potom porovnej s první prací.',
        'demo' => 
        array (
          'label' => 'Preflight',
          'lines' => 
          array (
            0 => '□ správné datum / místo',
            1 => '□ správný rozměr',
            2 => '□ headline čitelný',
            3 => '□ CTA čitelné',
            4 => '□ export otevřen mimo editor',
          ),
        ),
        'question' => 'Kdy je práce skutečně připravená k odevzdání?',
        'options' => 
        array (
          0 => 'Když vypadá dobře v editoru.',
          1 => 'Když jsem otevřel/a a zkontroloval/a skutečný export a všechny údaje.',
          2 => 'Když má hodně efektů.',
        ),
        'correct' => 1,
        'explanation' => 'Preflight kontroluje výsledek, který skutečně dostane divák.',
        'tasks' => 
        array (
          0 => 'Ulož export.',
          1 => 'Napiš 3 konkrétní rozdíly proti prvnímu plakátu.',
          2 => 'Jednu změnu zdůvodni pojmy hierarchie / spacing / crop / CTA.',
        ),
      ),
    ),
    'finisher' => 
    array (
      'title' => 'Rychlík · Story adaptace',
      'duration' => '15–20 min',
      'text' => 'Převeď stejný plakát do 1080 × 1920. Nekopíruj pozice — zachovej role textu, focal point a CTA.',
      'deliverables' => 
      array (
        0 => 'story 1080×1920',
        1 => '1 screenshot gridu',
        2 => '2 věty proč se layout změnil',
      ),
    ),
  ),
  'extendedLessons' => 
  array (
    0 => 
    array (
      'id' => 'graphics_color_type_system',
      'number' => 3,
      'title' => 'Lekce 3 · Barva + typografický systém',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Z jednoho plakátu vytvořit čitelný systém barev a typografie, který funguje v mobilním náhledu i na větším formátu.',
      'knowledge' => 
      array (
        0 => 'contrast-color',
        1 => 'typography',
        2 => 'spacing',
        3 => 'export',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Kontrastní laboratoř',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Typografické role',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'A/B skeleton',
        ),
        3 => 
        array (
          'time' => '45–65',
          'title' => 'Canva varianta',
        ),
        4 => 
        array (
          'time' => '65–80',
          'title' => 'QA + export',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Reflexe',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'contrast',
          'time' => '15 min',
          'title' => '01 · Kontrast jako funkce',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'contrast-color',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Dokonči Knowledge Tour kontrastu.',
            1 => 'Ověř dvě kombinace text/pozadí.',
            2 => 'Vyber jednu kombinaci pro hlavní text a jednu pro akcent.',
          ),
        ),
        1 => 
        array (
          'id' => 'type_roles',
          'time' => '15 min',
          'title' => '02 · Tři role textu',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'typography',
            1 => 'spacing',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj headline, info a CTA.',
            1 => 'Použij maximálně dvě rodiny písem.',
            2 => 'Nastav opakující se spacing.',
          ),
        ),
        2 => 
        array (
          'id' => 'ab',
          'time' => '15 min',
          'title' => '03 · A/B skeleton',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Vytvoř dvě varianty stejného obsahu.',
            1 => 'Varianta B musí změnit kompozici, ne text.',
            2 => 'Vyber lepší variantu podle thumbnail testu.',
          ),
        ),
        3 => 
        array (
          'id' => 'canva',
          'time' => '20 min',
          'title' => '04 · Přenos do Canvy',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přenes vybraný skeleton.',
            1 => 'Dodrž paletu a typografické role.',
            2 => 'Přidej obraz pouze pokud podporuje hlavní sdělení.',
          ),
        ),
        4 => 
        array (
          'id' => 'qa',
          'time' => '15 min',
          'title' => '05 · QA + export',
          'kind' => 'quiz',
          'knowledge' => 
          array (
            0 => 'export',
          ),
          'xp' => 25,
          'question' => 'Co je nejspolehlivější závěrečná kontrola?',
          'options' => 
          array (
            0 => 'Otevřít skutečný export mimo editor a zkontrolovat ho v cílové velikosti.',
            1 => 'Přidat ještě jeden efekt.',
            2 => 'Zkontrolovat pouze názvy vrstev.',
          ),
          'correct' => 0,
          'explanation' => 'Kontroluješ to, co skutečně dostane divák.',
          'tasks' => 
          array (
            0 => 'Exportuj finální soubor.',
            1 => 'Otevři jej mimo editor.',
          ),
        ),
        5 => 
        array (
          'id' => 'reflection',
          'time' => '10 min',
          'title' => '06 · Vysvětli rozhodnutí',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Napiš 3 věty: barva, typografie, spacing.',
            1 => 'Uveď jednu věc, kterou bys při další verzi změnil/a.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'id' => 'graphics_mini_identity',
      'number' => 4,
      'title' => 'Lekce 4 · Mini vizuální identita',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout malý vizuální systém pro fiktivní školní akci a aplikovat ho na plakát + sociální post.',
      'knowledge' => 
      array (
        0 => 'hierarchy',
        1 => 'composition',
        2 => 'color',
        3 => 'preflight',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Brief',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Mini brand kit',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Master plakát',
        ),
        3 => 
        array (
          'time' => '45–65',
          'title' => 'Social post',
        ),
        4 => 
        array (
          'time' => '65–82',
          'title' => 'Konzistence',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Preflight',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'brief',
          'time' => '12 min',
          'title' => '01 · Brief bez šablony',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Zapiš cílovou skupinu.',
            1 => 'Zapiš hlavní sdělení.',
            2 => 'Zapiš jednu požadovanou akci uživatele.',
          ),
        ),
        1 => 
        array (
          'id' => 'kit',
          'time' => '16 min',
          'title' => '02 · Mini brand kit',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'hierarchy',
            1 => 'composition',
            2 => 'color',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vyber 3 barvy max.',
            1 => 'Definuj 2 typografické role.',
            2 => 'Definuj styl CTA a obrazový princip.',
          ),
        ),
        2 => 
        array (
          'id' => 'master',
          'time' => '17 min',
          'title' => '03 · Master plakát',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Postav master 1080×1350.',
            1 => 'Ověř hierarchii a grid.',
            2 => 'Nepoužívej více prvků, než potřebuje sdělení.',
          ),
        ),
        3 => 
        array (
          'id' => 'social',
          'time' => '20 min',
          'title' => '04 · Sociální adaptace',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Převeď systém do 1080×1080.',
            1 => 'Změň layout, ne identitu.',
            2 => 'Zachovej CTA a hlavní sdělení.',
          ),
        ),
        4 => 
        array (
          'id' => 'consistency',
          'time' => '17 min',
          'title' => '05 · Audit konzistence',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co musí být mezi formáty stejné?',
          'options' => 
          array (
            0 => 'Vizuální pravidla a role prvků; přesné pozice stejné být nemusí.',
            1 => 'Každá souřadnice prvků.',
            2 => 'Počet řádků textu.',
          ),
          'correct' => 0,
          'explanation' => 'Identita drží pravidly, ne kopírováním souřadnic.',
        ),
        5 => 
        array (
          'id' => 'preflight',
          'time' => '8 min',
          'title' => '06 · Preflight',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'preflight',
          ),
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Otevři oba exporty mimo editor.',
            1 => 'Ověř rozměry a texty.',
            2 => 'Zapiš jednu větu, co systém drží pohromadě.',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'id' => 'graphics_palette_icons',
      'number' => 5,
      'title' => 'Lekce 5 · Paleta, ikony a konzistence',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Vytvořit malý vizuální jazyk z barev, ikon a obrazových pravidel a použít ho v jedné informační grafice.',
      'knowledge' => 
      array (
        0 => 'color-harmony',
        1 => 'iconography',
        2 => 'image-composition',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Paleta',
        ),
        1 => 
        array (
          'time' => '12–27',
          'title' => 'Ikony',
        ),
        2 => 
        array (
          'time' => '27–42',
          'title' => 'Obraz + focal point',
        ),
        3 => 
        array (
          'time' => '42–62',
          'title' => 'Kompozice',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Canva realizace',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'QA',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'palette',
          'time' => '12 min',
          'title' => '01 · Paleta, která má role',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'color-harmony',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Dokonči Color Harmony Lab.',
            1 => 'Vyber background, text a accent.',
            2 => 'Zapiš HEX hodnoty.',
          ),
        ),
        1 => 
        array (
          'id' => 'icons',
          'time' => '15 min',
          'title' => '02 · Jedna rodina ikon',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'iconography',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej stroke/fill styl.',
            1 => 'Vyber 3 ikony stejné rodiny.',
            2 => 'Sjednoť jejich optickou velikost.',
          ),
        ),
        2 => 
        array (
          'id' => 'image',
          'time' => '15 min',
          'title' => '03 · Focal point a crop',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'image-composition',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi focal point.',
            1 => 'Vyzkoušej dva cropy.',
            2 => 'Vyber crop s místem pro text.',
          ),
        ),
        3 => 
        array (
          'id' => 'compose',
          'time' => '20 min',
          'title' => '04 · Informační karta',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vytvoř 1080×1350 informační kartu.',
            1 => 'Použij 3 ikony max.',
            2 => 'Zachovej jasné pořadí čtení.',
          ),
        ),
        4 => 
        array (
          'id' => 'canva',
          'time' => '18 min',
          'title' => '05 · Přenos do Canvy',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přenes systém do Canvy.',
            1 => 'Nesnaž se „vylepšit“ každou ikonu jiným efektem.',
            2 => 'Ověř thumbnail.',
          ),
        ),
        5 => 
        array (
          'id' => 'qa',
          'time' => '10 min',
          'title' => '06 · Konzistence audit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co nejvíc drží tři ikony pohromadě jako jeden systém?',
          'options' => 
          array (
            0 => 'Stejný styl kresby, tloušťka a vizuální logika.',
            1 => 'Každá ikona jiná barva a jiný stín.',
            2 => 'Co největší počet detailů.',
          ),
          'correct' => 0,
          'explanation' => 'Konzistence vzniká společnými pravidly, ne množstvím efektů.',
        ),
      ),
    ),
    3 => 
    array (
      'id' => 'graphics_redesign_case',
      'number' => 6,
      'title' => 'Lekce 6 · Redesign: z chaosu na systém',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Analyzovat slabý návrh, formulovat problém a vytvořit nový plakát s jasně obhajitelnými rozhodnutími.',
      'knowledge' => 
      array (
        0 => 'template-critique',
        1 => 'color-harmony',
        2 => 'image-composition',
        3 => 'preflight',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Diagnostika',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Redesign plán',
        ),
        2 => 
        array (
          'time' => '30–48',
          'title' => 'Skeleton',
        ),
        3 => 
        array (
          'time' => '48–68',
          'title' => 'Canva redesign',
        ),
        4 => 
        array (
          'time' => '68–82',
          'title' => 'A/B porovnání',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Preflight',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'critique',
          'time' => '15 min',
          'title' => '01 · Co je opravdu problém',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'template-critique',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Najdi 3 konkrétní problémy.',
            1 => 'Ke každému napiš důsledek pro diváka.',
            2 => 'Neřeš zatím styl, jen funkci.',
          ),
        ),
        1 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '02 · Plán redesignu',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Urči první, druhou a třetí informaci.',
            1 => 'Vyber paletu.',
            2 => 'Urči obrazový princip.',
          ),
        ),
        2 => 
        array (
          'id' => 'skeleton',
          'time' => '18 min',
          'title' => '03 · Wireframe bez dekorací',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nakresli skeleton.',
            1 => 'Ověř grid.',
            2 => 'Ověř CTA.',
          ),
        ),
        3 => 
        array (
          'id' => 'build',
          'time' => '20 min',
          'title' => '04 · Canva redesign',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Realizuj redesign.',
            1 => 'Zachovej význam briefu.',
            2 => 'Nepřidávej text, který nebyl potřeba.',
          ),
        ),
        4 => 
        array (
          'id' => 'compare',
          'time' => '14 min',
          'title' => '05 · A/B: staré vs. nové',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je nejlepší důkaz, že redesign funguje lépe?',
          'options' => 
          array (
            0 => 'Jasnější hierarchie, čitelnost a splnění cíle briefu.',
            1 => 'Víc gradientů a efektů.',
            2 => 'Vyšší počet prvků.',
          ),
          'correct' => 0,
          'explanation' => 'Redesign se hodnotí podle funkce a komunikace, ne podle množství dekorací.',
        ),
        5 => 
        array (
          'id' => 'preflight',
          'time' => '8 min',
          'title' => '06 · Finální preflight',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'preflight',
          ),
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Otevři export mimo editor.',
            1 => 'Ověř údaje, CTA a rozměr.',
            2 => 'Ulož before/after do jedné prezentace.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'id' => 'graphics_responsive_series',
      'number' => 7,
      'title' => 'Lekce 7 · Jeden design, tři formáty',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést jeden plakát do postu, square a story tak, aby zůstala hierarchie, čitelnost a vizuální identita.',
      'knowledge' => 
      array (
        0 => 'responsive-series',
        1 => 'preflight',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Responsive princip',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Master audit',
        ),
        2 => 
        array (
          'time' => '30–48',
          'title' => 'Square',
        ),
        3 => 
        array (
          'time' => '48–66',
          'title' => 'Story',
        ),
        4 => 
        array (
          'time' => '66–82',
          'title' => 'Canva build',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'QA',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'principle',
          'time' => '15 min',
          'title' => '01 · Co se mezi formáty nesmí ztratit',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'responsive-series',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Responsive Preview Lab.',
            1 => 'Sepiš 3 invarianty návrhu.',
            2 => 'Urči safe zone.',
          ),
        ),
        1 => 
        array (
          'id' => 'audit',
          'time' => '15 min',
          'title' => '02 · Audit masteru',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Urči headline/info/CTA.',
            1 => 'Ověř thumbnail.',
            2 => 'Najdi prvek, který bude při změně formátu problém.',
          ),
        ),
        2 => 
        array (
          'id' => 'square',
          'time' => '18 min',
          'title' => '03 · Square adaptace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř 1080×1080.',
            1 => 'Změň crop, ne identitu.',
            2 => 'Ověř CTA.',
          ),
        ),
        3 => 
        array (
          'id' => 'story',
          'time' => '18 min',
          'title' => '04 · Story adaptace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř 1080×1920.',
            1 => 'Dodrž safe zone.',
            2 => 'Přeskup info blok.',
          ),
        ),
        4 => 
        array (
          'id' => 'build',
          'time' => '16 min',
          'title' => '05 · Canva série',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Sjednoť paletu a typografii.',
            1 => 'Exportuj 3 formáty.',
            2 => 'Pojmenuj soubory konzistentně.',
          ),
        ),
        5 => 
        array (
          'id' => 'qa',
          'time' => '8 min',
          'title' => '06 · Multi-format QA',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co je nejlepší důkaz, že série drží pohromadě?',
          'options' => 
          array (
            0 => 'Stejné role, hierarchie a vizuální pravidla i při odlišném layoutu.',
            1 => 'Úplně stejné souřadnice všech prvků.',
            2 => 'V každém formátu jiný styl.',
          ),
          'correct' => 0,
          'explanation' => 'Konzistence je systém vztahů, ne kopie pixelů.',
        ),
      ),
    ),
    5 => 
    array (
      'id' => 'graphics_editorial_system',
      'number' => 8,
      'title' => 'Lekce 8 · Typografie I + Layout I',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést základní znalost typografie a gridu do čitelného editorial layoutu s jasnou škálou, rytmem a whitespace.',
      'knowledge' => 
      array (
        0 => 'editorial-typography-i',
        1 => 'layout-rhythm-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Typografické role',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Škála a line-height',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Rytmus',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Editorial layout',
        ),
        4 => 
        array (
          'time' => '62–82',
          'title' => 'Canva/Figma build',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'QA',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'roles',
          'time' => '15 min',
          'title' => '01 · Typografické role',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'editorial-typography-i',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Type Scale Lab.',
            1 => 'Pojmenuj 4 textové role.',
            2 => 'Nastav jasný rozdíl headline/body.',
          ),
        ),
        1 => 
        array (
          'id' => 'scale',
          'time' => '15 min',
          'title' => '02 · Škála a čitelnost',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Nastav velikosti rolí.',
            1 => 'Ověř line-height.',
            2 => 'Zkrať příliš dlouhý řádek.',
          ),
        ),
        2 => 
        array (
          'id' => 'rhythm',
          'time' => '15 min',
          'title' => '03 · Vertikální rytmus',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'layout-rhythm-i',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Vertical Rhythm Lab.',
            1 => 'Vyber spacing škálu.',
            2 => 'Odděl skupiny a sekce.',
          ),
        ),
        3 => 
        array (
          'id' => 'layout',
          'time' => '17 min',
          'title' => '04 · Editorial layout',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř dvousloupcový layout.',
            1 => 'Použij whitespace jako aktivní prvek.',
            2 => 'Srovnej hlavní hrany.',
          ),
        ),
        4 => 
        array (
          'id' => 'build',
          'time' => '20 min',
          'title' => '05 · Realizace',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přeneste layout do Canvy/Figmy.',
            1 => 'Použij jen definované role.',
            2 => 'Zachovej spacing systém.',
          ),
        ),
        5 => 
        array (
          'id' => 'qa',
          'time' => '8 min',
          'title' => '06 · Čitelnost',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co nejlépe ukazuje, že typografický systém funguje?',
          'options' => 
          array (
            0 => 'Role textu jsou rychle rozpoznatelné a rytmus se opakuje napříč layoutem.',
            1 => 'Každý blok používá jinou velikost.',
            2 => 'Na stránce nezůstalo žádné prázdné místo.',
          ),
          'correct' => 0,
          'explanation' => 'Systém je čitelný, opakovatelný a předvídatelný.',
        ),
      ),
    ),
    6 => 
    array (
      'id' => 'graphics_story_delivery',
      'number' => 9,
      'title' => 'Lekce 9 · Vizuální příběh + produkční workflow',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Spojit obraz, text a CTA do řízené cesty pozornosti a odevzdat výstup ve správně verzovaném, zkontrolovaném formátu.',
      'knowledge' => 
      array (
        0 => 'visual-story-i',
        1 => 'production-preflight-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Focal point',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Sekvence',
        ),
        2 => 
        array (
          'time' => '30–47',
          'title' => 'Obraz + text',
        ),
        3 => 
        array (
          'time' => '47–65',
          'title' => 'Varianta',
        ),
        4 => 
        array (
          'time' => '65–82',
          'title' => 'Preflight',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Delivery',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'focus',
          'time' => '15 min',
          'title' => '01 · První pohled',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'visual-story-i',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Visual Story Lab.',
            1 => 'Urči focal point.',
            2 => 'Zapiš plánovanou cestu oka.',
          ),
        ),
        1 => 
        array (
          'id' => 'sequence',
          'time' => '15 min',
          'title' => '02 · Sekvence čtení',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Seřaď headline/info/CTA.',
            1 => 'Odstraň jednu konkurující dominantu.',
            2 => 'Ověř 3s test.',
          ),
        ),
        2 => 
        array (
          'id' => 'image',
          'time' => '17 min',
          'title' => '03 · Obraz podporuje sdělení',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Uprav crop.',
            1 => 'Zkontroluj směr pohledu/linie.',
            2 => 'Ověř čitelnost textu přes obraz.',
          ),
        ),
        3 => 
        array (
          'id' => 'variant',
          'time' => '18 min',
          'title' => '04 · Druhá varianta',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř B variantu stejného obsahu.',
            1 => 'Změň art direction, ne význam.',
            2 => 'Vyber lepší variantu podle cíle.',
          ),
        ),
        4 => 
        array (
          'id' => 'preflight',
          'time' => '17 min',
          'title' => '05 · Produkční preflight',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'production-preflight-i',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Preflight Lab.',
            1 => 'Pojmenuj master a export.',
            2 => 'Ověř rozměr, crop, kontrast a export.',
          ),
        ),
        5 => 
        array (
          'id' => 'delivery',
          'time' => '8 min',
          'title' => '06 · Delivery check',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který soubor je nejlepší finální odevzdání?',
          'options' => 
          array (
            0 => 'Jasně pojmenovaný export správného rozměru plus zachovaný editovatelný master.',
            1 => 'final_final2.png bez zdrojového souboru.',
            2 => 'Screenshot editoru.',
          ),
          'correct' => 0,
          'explanation' => 'Produkční workflow musí být dohledatelné a znovu upravitelné.',
        ),
      ),
    ),
    7 => 
    array (
      'id' => 'gfx_web_anatomy',
      'number' => 10,
      'title' => 'Lekce 10 · Anatomie webu: hierarchie od plakátu k obrazovce',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Přenést principy vizuální hierarchie do jednoduché webové stránky a rozlišit header, hero, obsah, CTA a footer.',
      'knowledge' => 
      array (
        0 => 'web-layout-basics-i',
        1 => 'layout-rhythm-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Rozlož stránku na role',
          'text' => 'Najdi header, hero, hlavní obsah, CTA a footer.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Priorita obsahu',
          'text' => 'Zkrať zadání na headline, supporting text a CTA.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Webový skeleton',
          'text' => 'Nakresli desktop frame 1440 px.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Scan test',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Co se stane na mobilu',
          'text' => 'Označ prvky, které mohou jít pod sebe.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'anatomy',
          'time' => '15 min',
          'title' => '01 · Rozlož stránku na role',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'web-layout-basics-i',
          ),
          'tasks' => 
          array (
            0 => 'Najdi header, hero, hlavní obsah, CTA a footer.',
            1 => 'U každé části napiš její jediný hlavní účel.',
          ),
        ),
        1 => 
        array (
          'id' => 'hierarchy',
          'time' => '15 min',
          'title' => '02 · Priorita obsahu',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zkrať zadání na headline, supporting text a CTA.',
            1 => 'Vyber jednu informaci, která nesmí soutěžit s headline.',
          ),
        ),
        2 => 
        array (
          'id' => 'grid',
          'time' => '15 min',
          'title' => '03 · Webový skeleton',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nakresli desktop frame 1440 px.',
            1 => 'Použij společné hrany a konzistentní horizontální padding.',
            2 => 'Neřeš zatím dekorace.',
          ),
          'knowledge' => 
          array (
            0 => 'layout-rhythm-i',
          ),
        ),
        3 => 
        array (
          'id' => 'scan',
          'time' => '12 min',
          'title' => '04 · Scan test',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co má uživatel pochopit z hero sekce během několika sekund?',
          'options' => 
          array (
            0 => 'Co stránka nabízí a jaký je hlavní další krok.',
            1 => 'Všechny detaily webu a celý footer.',
            2 => 'Pouze název použitého fontu.',
          ),
          'correct' => 0,
          'explanation' => 'Hero má rychle sdělit hodnotu a primární akci.',
        ),
        4 => 
        array (
          'id' => 'mobile_think',
          'time' => '15 min',
          'title' => '05 · Co se stane na mobilu',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Označ prvky, které mohou jít pod sebe.',
            1 => 'Urči, co musí zůstat nad přehybem.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Který princip se z plakátu přenáší na web nejlépe?',
          'options' => 
          array (
            0 => 'Jasná hierarchie a řízená cesta pozornosti.',
            1 => 'Pevné souřadnice každého prvku.',
            2 => 'Co nejvíce dekorativních efektů.',
          ),
          'correct' => 0,
          'explanation' => 'Médium se mění, ale práce s prioritou a pozorností zůstává.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Zakresli 5 hlavních oblastí landing page.',
        1 => 'Napiš headline do 8 slov.',
        2 => 'Vyber primární CTA.',
        3 => 'Popiš jednu věc, kterou bys na mobilu přesunul/a.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nezačínej HTML/CSS. Cílem je vizuální a informační model.',
        1 => 'Při review se ptej „co vidíš první?“ místo „líbí se ti to?“.',
      ),
    ),
    8 => 
    array (
      'id' => 'gfx_responsive_basics',
      'number' => 11,
      'title' => 'Lekce 11 · Responsive základ: jedna stránka, tři šířky',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout stejný obsah pro mobil, tablet a desktop bez slepého zmenšování.',
      'knowledge' => 
      array (
        0 => 'responsive-layout-i',
        1 => 'responsive-series',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Responsive ≠ zmenšení',
          'text' => 'Porovnej 390, 768 a 1440 px.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Mobile first skeleton',
          'text' => 'Navrhni 390px frame.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Tablet adaptace',
          'text' => 'Přidej druhý sloupec jen tam, kde pomůže.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Desktop kompozice',
          'text' => 'Rozšiř layout na 1440 px.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Breakpoint mindset',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'principle',
          'time' => '15 min',
          'title' => '01 · Responsive ≠ zmenšení',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'responsive-layout-i',
          ),
          'tasks' => 
          array (
            0 => 'Porovnej 390, 768 a 1440 px.',
            1 => 'Urči, co se přeskupí, co zůstane a co se může skrýt.',
          ),
        ),
        1 => 
        array (
          'id' => 'mobile',
          'time' => '15 min',
          'title' => '02 · Mobile first skeleton',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni 390px frame.',
            1 => 'Obsah řaď podle priority, ne podle desktopového pořadí.',
          ),
        ),
        2 => 
        array (
          'id' => 'tablet',
          'time' => '15 min',
          'title' => '03 · Tablet adaptace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přidej druhý sloupec jen tam, kde pomůže.',
            1 => 'Ověř délku řádků a spacing.',
          ),
        ),
        3 => 
        array (
          'id' => 'desktop',
          'time' => '15 min',
          'title' => '04 · Desktop kompozice',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozšiř layout na 1440 px.',
            1 => 'Neroztahuj text přes celou šířku.',
          ),
        ),
        4 => 
        array (
          'id' => 'breakpoint',
          'time' => '12 min',
          'title' => '05 · Breakpoint mindset',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Kdy dává smysl změnit layout?',
          'options' => 
          array (
            0 => 'Když obsah přestává fungovat, ne jen na předem naučeném čísle.',
            1 => 'Vždy přesně po 100 px.',
            2 => 'Jen na desktopu.',
          ),
          'correct' => 0,
          'explanation' => 'Breakpoint řeší konkrétní problém obsahu a prostoru.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co se má napříč šířkami zachovat?',
          'options' => 
          array (
            0 => 'Obsahová priorita a vizuální systém.',
            1 => 'Přesný počet sloupců.',
            2 => 'Stejná velikost nadpisu v pixelech.',
          ),
          'correct' => 0,
          'explanation' => 'Responsive mění provedení, ne význam a prioritu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Nakresli mobile/tablet/desktop stejných 5 sekcí.',
        1 => 'U každého breakpointu napiš jednu změnu a důvod.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Studentům dovol odlišná řešení, pokud umí vysvětlit prioritu obsahu.',
        1 => 'Kontroluj především přetečení, dlouhé řádky a CTA.',
      ),
    ),
    9 => 
    array (
      'id' => 'gfx_components_intro',
      'number' => 12,
      'title' => 'Lekce 12 · Komponenty: tlačítka, karty a opakovatelná pravidla',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit komponentu jako opakovatelný vzor se stavy, ne jako jednorázový obrázek.',
      'knowledge' => 
      array (
        0 => 'components-i',
        1 => 'design-system-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Co je komponenta',
          'text' => 'Rozliš jednorázový blok a opakovatelnou komponentu.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Button systém',
          'text' => 'Navrhni primary a secondary button.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Stavy',
          'text' => 'Přidej default, hover/focus a disabled.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Karta',
          'text' => 'Vytvoř kartu s názvem, popisem a akcí.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Konzistence',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'component',
          'time' => '15 min',
          'title' => '01 · Co je komponenta',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'components-i',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš jednorázový blok a opakovatelnou komponentu.',
            1 => 'Vyber Button a Card jako první dva vzory.',
          ),
        ),
        1 => 
        array (
          'id' => 'button',
          'time' => '15 min',
          'title' => '02 · Button systém',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni primary a secondary button.',
            1 => 'Sjednoť výšku, padding, radius a typografii.',
          ),
        ),
        2 => 
        array (
          'id' => 'states',
          'time' => '15 min',
          'title' => '03 · Stavy',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přidej default, hover/focus a disabled.',
            1 => 'Stav nesmí být vyjádřen jen barvou.',
          ),
        ),
        3 => 
        array (
          'id' => 'card',
          'time' => '15 min',
          'title' => '04 · Karta',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř kartu s názvem, popisem a akcí.',
            1 => 'Použij stejné spacing tokeny jako u buttonu.',
          ),
        ),
        4 => 
        array (
          'id' => 'consistency',
          'time' => '12 min',
          'title' => '05 · Konzistence',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je největší výhoda komponent?',
          'options' => 
          array (
            0 => 'Stejné pravidlo lze bezpečně opakovat a měnit na jednom místě.',
            1 => 'Každá karta může mít náhodný padding.',
            2 => 'Není potřeba řešit stavy.',
          ),
          'correct' => 0,
          'explanation' => 'Komponenty snižují náhodnost a zrychlují iteraci.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co patří do definice tlačítka?',
          'options' => 
          array (
            0 => 'Vzhled, obsahová pravidla a interakční stavy.',
            1 => 'Jen barva pozadí.',
            2 => 'Pouze export PNG.',
          ),
          'correct' => 0,
          'explanation' => 'Komponenta není statický screenshot.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Definuj Button: height, padding, radius, text style.',
        1 => 'Nakresli 3 stavy.',
        2 => 'Definuj Card se stejným spacing systémem.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Neřeš komplexní design system. Cílem je opakovatelnost.',
        1 => 'Důraz na focus state připraví studenty na accessibility.',
      ),
    ),
    10 => 
    array (
      'id' => 'gfx_web_typography',
      'number' => 13,
      'title' => 'Lekce 13 · Webová typografie: čitelnost, délka řádku a rytmus',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout typografii pro obrazovku s jasnými rolemi, rozumnou délkou řádku a konzistentním vertikálním rytmem.',
      'knowledge' => 
      array (
        0 => 'web-typography-i',
        1 => 'editorial-typography-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Textové role',
          'text' => 'Definuj Display/H1, H2, body, small a action.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Řádek a line-height',
          'text' => 'Zkrať příliš dlouhý body text.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Type scale',
          'text' => 'Sestav malou škálu bez 10 náhodných velikostí.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Vertikální rytmus',
          'text' => 'Použij opakující se spacing mezi headingem, textem a akcí.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Čitelnost',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'roles',
          'time' => '15 min',
          'title' => '01 · Textové role',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'web-typography-i',
          ),
          'tasks' => 
          array (
            0 => 'Definuj Display/H1, H2, body, small a action.',
            1 => 'Každá role musí mít konkrétní účel.',
          ),
        ),
        1 => 
        array (
          'id' => 'line',
          'time' => '15 min',
          'title' => '02 · Řádek a line-height',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zkrať příliš dlouhý body text.',
            1 => 'Uprav line-height tak, aby odstavce byly čitelné.',
          ),
        ),
        2 => 
        array (
          'id' => 'scale',
          'time' => '15 min',
          'title' => '03 · Type scale',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sestav malou škálu bez 10 náhodných velikostí.',
            1 => 'Ověř mobile headline wrap.',
          ),
        ),
        3 => 
        array (
          'id' => 'rhythm',
          'time' => '15 min',
          'title' => '04 · Vertikální rytmus',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Použij opakující se spacing mezi headingem, textem a akcí.',
          ),
        ),
        4 => 
        array (
          'id' => 'readability',
          'time' => '12 min',
          'title' => '05 · Čitelnost',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Která změna nejčastěji zlepší dlouhý odstavec na desktopu?',
          'options' => 
          array (
            0 => 'Omezit šířku textového sloupce a nastavit vhodný line-height.',
            1 => 'Roztáhnout text přes celý monitor.',
            2 => 'Snížit font pod 12 px.',
          ),
          'correct' => 0,
          'explanation' => 'Čitelnost ovlivňuje šířka řádku, velikost i line-height.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je typografický systém?',
          'options' => 
          array (
            0 => 'Malý soubor pojmenovaných rolí a pravidel používaných konzistentně.',
            1 => 'Jeden font pro každý odstavec.',
            2 => 'Seznam efektů v editoru.',
          ),
          'correct' => 0,
          'explanation' => 'Systém dává textu předvídatelnou strukturu.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Navrhni 5 textových rolí.',
        1 => 'Pro body text napiš size/line-height/max-width.',
        2 => 'Otestuj H1 na 390 px.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Vysvětluj role, ne magická čísla.',
        1 => 'Při hodnocení se ptej, zda je stránka skenovatelná bez barev.',
      ),
    ),
    11 => 
    array (
      'id' => 'gfx_web_images',
      'number' => 14,
      'title' => 'Lekce 14 · Obraz pro web: crop, rozlišení a export',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Připravit vizuály pro web tak, aby podporovaly obsah, měly správný crop a nebyly zbytečně datově těžké.',
      'knowledge' => 
      array (
        0 => 'image-web-i',
        1 => 'production-preflight-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Úloha obrazu',
          'text' => 'Urči, zda obrázek vysvětluje, dokumentuje nebo jen dekoruje.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Crop pro hero',
          'text' => 'Najdi focal point.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Export variant',
          'text' => 'Připrav master a web export.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Formát',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Obsah vs dekorace',
          'text' => 'Napiš smysluplný alt popis pro informační obraz.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'purpose',
          'time' => '15 min',
          'title' => '01 · Úloha obrazu',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'image-web-i',
          ),
          'tasks' => 
          array (
            0 => 'Urči, zda obrázek vysvětluje, dokumentuje nebo jen dekoruje.',
            1 => 'U dekorativního obrazu zvaž, zda ho vůbec potřebuješ.',
          ),
        ),
        1 => 
        array (
          'id' => 'crop',
          'time' => '15 min',
          'title' => '02 · Crop pro hero',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Najdi focal point.',
            1 => 'Připrav desktop a mobile crop.',
            2 => 'Nenech text překrýt důležitý detail.',
          ),
        ),
        2 => 
        array (
          'id' => 'export',
          'time' => '15 min',
          'title' => '03 · Export variant',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Připrav master a web export.',
            1 => 'Zvol rozumný rozměr podle použití.',
          ),
          'knowledge' => 
          array (
            0 => 'production-preflight-i',
          ),
        ),
        3 => 
        array (
          'id' => 'format',
          'time' => '12 min',
          'title' => '04 · Formát',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je nejhorší produkční návyk?',
          'options' => 
          array (
            0 => 'Nahrát na web obří originál bez potřeby a bez kontroly výsledku.',
            1 => 'Připravit samostatný crop pro mobil.',
            2 => 'Otevřít export mimo editor.',
          ),
          'correct' => 0,
          'explanation' => 'Webový asset má odpovídat reálnému použití.',
        ),
        4 => 
        array (
          'id' => 'alt',
          'time' => '15 min',
          'title' => '05 · Obsah vs dekorace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš smysluplný alt popis pro informační obraz.',
            1 => 'U čistě dekorativního prvku označ, že nemá nést informaci.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co kontroluješ před předáním obrázku na web?',
          'options' => 
          array (
            0 => 'Crop, rozměr, vizuální kvalitu, datovou přiměřenost a význam.',
            1 => 'Jen název vrstvy ve Figmě.',
            2 => 'Pouze počet barev.',
          ),
          'correct' => 0,
          'explanation' => 'Asset musí fungovat vizuálně i produkčně.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Vyber 1 fotografii a připrav desktop/mobile crop.',
        1 => 'Zapiš cílový rozměr exportu.',
        2 => 'Napiš alt text nebo zdůvodni dekorativní použití.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nepotřebujeme přesné KB limity bez kontextu. Uč datovou přiměřenost.',
        1 => 'Dovol vlastní fotografie i legální stock.',
      ),
    ),
    12 => 
    array (
      'id' => 'gfx_forms_accessibility',
      'number' => 15,
      'title' => 'Lekce 15 · Formuláře a přístupnost: stav musí být pochopitelný',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout jednoduchý formulář s jasnými labely, focus stavem, chybou a úspěchem bez závislosti pouze na barvě.',
      'knowledge' => 
      array (
        0 => 'forms-a11y-i',
        1 => 'components-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Anatomie formuláře',
          'text' => 'Rozliš label, input, helper text, error a submit.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Default + focus',
          'text' => 'Navrhni field default.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Error state',
          'text' => 'Zobraz chybu textem i vizuálním signálem.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Success + loading',
          'text' => 'Navrhni potvrzení odeslání.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Bez barvy',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Exit ticket',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'form',
          'time' => '15 min',
          'title' => '01 · Anatomie formuláře',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'forms-a11y-i',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš label, input, helper text, error a submit.',
            1 => 'Placeholder nepoužívej jako jediný label.',
          ),
        ),
        1 => 
        array (
          'id' => 'default',
          'time' => '15 min',
          'title' => '02 · Default + focus',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni field default.',
            1 => 'Přidej jasně viditelný focus stav.',
          ),
        ),
        2 => 
        array (
          'id' => 'error',
          'time' => '15 min',
          'title' => '03 · Error state',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zobraz chybu textem i vizuálním signálem.',
            1 => 'Řekni uživateli, jak ji opravit.',
          ),
        ),
        3 => 
        array (
          'id' => 'success',
          'time' => '15 min',
          'title' => '04 · Success + loading',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni potvrzení odeslání.',
            1 => 'Přidej loading/disabled stav tlačítka.',
          ),
        ),
        4 => 
        array (
          'id' => 'color',
          'time' => '12 min',
          'title' => '05 · Bez barvy',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Proč nestačí označit chybu jen červeným okrajem?',
          'options' => 
          array (
            0 => 'Význam musí být pochopitelný i bez rozlišení barvy; pomůže text/ikona.',
            1 => 'Protože červená je zakázaná barva.',
            2 => 'Protože každý input musí být zelený.',
          ),
          'correct' => 0,
          'explanation' => 'Stav má více než jeden vizuální signál.',
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '12 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je nejlepší error message?',
          'options' => 
          array (
            0 => 'Konkrétní, blízko pole a říká, co má uživatel opravit.',
            1 => '„ERROR 12“.',
            2 => 'Pouze červená hvězdička.',
          ),
          'correct' => 0,
          'explanation' => 'Dobrá chyba vede k nápravě.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Nakresli pole v default/focus/error/success.',
        1 => 'Napiš konkrétní chybovou hlášku.',
        2 => 'Zkontroluj, že error funguje i v grayscale.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Používej keyboard/focus jako designový stav, ne technickou poznámku.',
        1 => 'Nehodnoť estetiku erroru výš než srozumitelnost.',
      ),
    ),
    13 => 
    array (
      'id' => 'gfx_landing_wireframe',
      'number' => 16,
      'title' => 'Lekce 16 · Landing page: wireframe od briefu k flow',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést jednoduchý brief do struktury landing page a obhájit pořadí sekcí před vizuálním stylingem.',
      'knowledge' => 
      array (
        0 => 'web-layout-basics-i',
        1 => 'responsive-layout-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Brief',
          'text' => 'Napiš cílovou skupinu, problém, nabídku a jedinou hlavní akci.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Content inventory',
          'text' => 'Sepiš obsah, který musí stránka obsahovat.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Sekvence sekcí',
          'text' => 'Seřaď hero → důvěra → benefit → detail → CTA.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Wireframe',
          'text' => 'Postav low-fi desktop a mobile.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Co testuje wireframe',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => '3min user walk-through',
          'text' => 'Nech spolužáka popsat, co by udělal jako první.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'brief',
          'time' => '15 min',
          'title' => '01 · Brief',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš cílovou skupinu, problém, nabídku a jedinou hlavní akci.',
          ),
        ),
        1 => 
        array (
          'id' => 'content',
          'time' => '15 min',
          'title' => '02 · Content inventory',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sepiš obsah, který musí stránka obsahovat.',
            1 => 'Odstraň duplicity a dekorativní text bez funkce.',
          ),
        ),
        2 => 
        array (
          'id' => 'flow',
          'time' => '15 min',
          'title' => '03 · Sekvence sekcí',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď hero → důvěra → benefit → detail → CTA.',
            1 => 'U každé sekce napiš otázku, na kterou odpovídá.',
          ),
        ),
        3 => 
        array (
          'id' => 'wireframe',
          'time' => '15 min',
          'title' => '04 · Wireframe',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Postav low-fi desktop a mobile.',
            1 => 'Nepoužívej finální fotografie ani styling.',
          ),
        ),
        4 => 
        array (
          'id' => 'wire',
          'time' => '12 min',
          'title' => '05 · Co testuje wireframe',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je primární účel wireframu?',
          'options' => 
          array (
            0 => 'Ověřit strukturu, prioritu a flow dříve než vizuální detaily.',
            1 => 'Vybrat finální stíny a gradienty.',
            2 => 'Exportovat hotový web.',
          ),
          'correct' => 0,
          'explanation' => 'Wireframe zlevňuje změny struktury.',
        ),
        5 => 
        array (
          'id' => 'review',
          'time' => '15 min',
          'title' => '06 · 3min user walk-through',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech spolužáka popsat, co by udělal jako první.',
            1 => 'Zapiš jednu změnu podle pozorování.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Brief ve 4 větách.',
        1 => 'Content inventory.',
        2 => 'Pořadí 5–7 sekcí.',
        3 => 'Desktop + mobile wireframe.',
        4 => '1 změna po peer walk-through.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nechte studenty nejprve testovat flow bez vizuálního wow efektu.',
        1 => 'Peer test nesmí být „líbí/nelíbí“, ale „co očekáváš, že se stane?“.',
      ),
    ),
    14 => 
    array (
      'id' => 'gfx_ui_kit_intro',
      'number' => 17,
      'title' => 'Lekce 17 · Mini UI kit: pravidla místo náhodných hodnot',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Sestavit malý UI kit s barvami, typografií, spacingem a 3 komponentami a použít jej v jedné obrazovce.',
      'knowledge' => 
      array (
        0 => 'design-system-i',
        1 => 'components-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Malá sada tokenů',
          'text' => 'Definuj 1–2 text colors, background, primary action.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Buttons',
          'text' => 'Vytvoř primary/secondary + stavy.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Card + input',
          'text' => 'Vytvoř card a form field.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Slož obrazovku',
          'text' => 'Postav jednoduchou landing/registration obrazovku jen z definovaných pravidel.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Změna systému',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Dokumentace',
          'text' => 'Na jednu stránku napiš barvy, typografii, spacing a komponenty.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'tokens',
          'time' => '15 min',
          'title' => '01 · Malá sada tokenů',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'design-system-i',
          ),
          'tasks' => 
          array (
            0 => 'Definuj 1–2 text colors, background, primary action.',
            1 => 'Zvol spacing škálu 4/8 násobků.',
          ),
        ),
        1 => 
        array (
          'id' => 'buttons',
          'time' => '15 min',
          'title' => '02 · Buttons',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř primary/secondary + stavy.',
            1 => 'Použij stejné height/padding pravidlo.',
          ),
        ),
        2 => 
        array (
          'id' => 'cards',
          'time' => '15 min',
          'title' => '03 · Card + input',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř card a form field.',
            1 => 'Použij stejné radius/spacing principy.',
          ),
        ),
        3 => 
        array (
          'id' => 'screen',
          'time' => '15 min',
          'title' => '04 · Slož obrazovku',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Postav jednoduchou landing/registration obrazovku jen z definovaných pravidel.',
          ),
        ),
        4 => 
        array (
          'id' => 'token',
          'time' => '12 min',
          'title' => '05 · Změna systému',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co má nastat, když změníš primary color token?',
          'options' => 
          array (
            0 => 'Relevantní komponenty se změní konzistentně.',
            1 => 'Každou komponentu musíš hledat a měnit náhodně.',
            2 => 'Změní se pouze název souboru.',
          ),
          'correct' => 0,
          'explanation' => 'Sdílené pravidlo je smysl systému.',
        ),
        5 => 
        array (
          'id' => 'doc',
          'time' => '15 min',
          'title' => '06 · Dokumentace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Na jednu stránku napiš barvy, typografii, spacing a komponenty.',
            1 => 'Přidej 2 „do/don’t“ příklady.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => '1 stránka UI kit dokumentace.',
        1 => 'Barvy + type scale + spacing.',
        2 => 'Button, Card, Field se stavy.',
        3 => 'Ukázková obrazovka.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Omez rozsah; cílem není napodobit enterprise design system.',
        1 => 'Hodnoť konzistenci a vysvětlitelnost pravidel.',
      ),
    ),
    15 => 
    array (
      'id' => 'gfx_landing_capstone',
      'number' => 18,
      'title' => 'Lekce 18 · Capstone: responzivní landing page',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Spojit hierarchii, typografii, obraz, responsive layout, komponenty a accessibility do finální landing page s krátkou case study.',
      'knowledge' => 
      array (
        0 => 'web-layout-basics-i',
        1 => 'responsive-layout-i',
        2 => 'web-typography-i',
        3 => 'forms-a11y-i',
        4 => 'design-system-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Scope a success criteria',
          'text' => 'Vyber brief.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Wireframe',
          'text' => 'Připrav mobile + desktop flow.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Visual system',
          'text' => 'Použij vlastní mini UI kit.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Responsive + states',
          'text' => 'Ověř 390/768/1440.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Design QA',
          'text' => 'Proveď 3s test, grayscale, crop, focus a export/prezentaci.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Obhajoba',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '01 · Scope a success criteria',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vyber brief.',
            1 => 'Definuj 3 věci, které musí uživatel pochopit.',
            2 => 'Definuj hlavní CTA.',
          ),
        ),
        1 => 
        array (
          'id' => 'wire',
          'time' => '15 min',
          'title' => '02 · Wireframe',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Připrav mobile + desktop flow.',
            1 => 'Neřeš detaily dříve než strukturu.',
          ),
        ),
        2 => 
        array (
          'id' => 'visual',
          'time' => '15 min',
          'title' => '03 · Visual system',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Použij vlastní mini UI kit.',
            1 => 'Nastav typografii, barvy a obraz.',
          ),
        ),
        3 => 
        array (
          'id' => 'responsive',
          'time' => '15 min',
          'title' => '04 · Responsive + states',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř 390/768/1440.',
            1 => 'Přidej focus/error stavy tam, kde jsou relevantní.',
          ),
        ),
        4 => 
        array (
          'id' => 'qa',
          'time' => '15 min',
          'title' => '05 · Design QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Proveď 3s test, grayscale, crop, focus a export/prezentaci.',
            1 => 'Oprav největší nalezený problém.',
          ),
        ),
        5 => 
        array (
          'id' => 'defense',
          'time' => '12 min',
          'title' => '06 · Obhajoba',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je nejsilnější obhajoba designového rozhodnutí?',
          'options' => 
          array (
            0 => 'Cíl → pozorování/evidence → konkrétní rozhodnutí.',
            1 => '„Protože se mi to líbí“.',
            2 => '„Protože to bylo v šabloně“.',
          ),
          'correct' => 0,
          'explanation' => 'Case study má ukázat myšlení, ne jen finální screenshot.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Brief + success criteria.',
        1 => 'Mobile/Desktop wireframe.',
        2 => 'Finální návrh.',
        3 => 'Mini UI kit.',
        4 => '5bodový QA záznam.',
        5 => '5 vět case study.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Použij jako semestrální checkpoint.',
        1 => 'U studentů s rychlejším tempem požaduj druhou variantu hero nebo jednoduchý prototyp.',
      ),
    ),
    16 => 
    array (
      'id' => 'v30_1a_19',
      'number' => 19,
      'title' => 'Lekce 19 · Visual audit clinic',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Najít konkrétní problém v existujícím návrhu a opravit jej na základě hierarchie, kontrastu a spacingu',
      'knowledge' => 
      array (
        0 => 'design-feedback',
        1 => 'content-first-layout',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'design-feedback',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'design-feedback',
            1 => 'content-first-layout',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    17 => 
    array (
      'id' => 'v30_1a_20',
      'number' => 20,
      'title' => 'Lekce 20 · Content-first landing',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Převést stručný obsahový brief do webového skeletonu bez zbytečných dekorací.',
      'knowledge' => 
      array (
        0 => 'content-first-layout',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'content-first-layout',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'content-first-layout',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    18 => 
    array (
      'id' => 'v30_1a_21',
      'number' => 21,
      'title' => 'Lekce 21 · Responsive hero',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Navrhnout hero sekci, která zachová prioritu obsahu na desktopu i mobilu.',
      'knowledge' => 
      array (
        0 => 'responsive-art-direction',
        1 => 'content-first-layout',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'responsive-art-direction',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'responsive-art-direction',
            1 => 'content-first-layout',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    19 => 
    array (
      'id' => 'v30_1a_22',
      'number' => 22,
      'title' => 'Lekce 22 · Microcopy & CTA',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Napsat konkrétní CTA a pomocné texty, které snižují nejistotu uživatele.',
      'knowledge' => 
      array (
        0 => 'microcopy-cta',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'microcopy-cta',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'microcopy-cta',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    20 => 
    array (
      'id' => 'v30_1a_23',
      'number' => 23,
      'title' => 'Lekce 23 · Accessible color & type',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Ověřit kontrast, čitelnost a hierarchii bez závislosti jen na barvě.',
      'knowledge' => 
      array (
        0 => 'forms-a11y-i',
        1 => 'web-typography-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'forms-a11y-i',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'forms-a11y-i',
            1 => 'web-typography-i',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    21 => 
    array (
      'id' => 'v30_1a_24',
      'number' => 24,
      'title' => 'Lekce 24 · Image art direction',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Připravit různé cropy stejného obrazu pro desktop, card a mobil bez ztráty focal pointu.',
      'knowledge' => 
      array (
        0 => 'responsive-art-direction',
        1 => 'image-composition',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'responsive-art-direction',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'responsive-art-direction',
            1 => 'image-composition',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    22 => 
    array (
      'id' => 'v30_1a_25',
      'number' => 25,
      'title' => 'Lekce 25 · Component starter',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Navrhnout jednoduchou komponentu s default/focus/error stavem a jasným obsahem.',
      'knowledge' => 
      array (
        0 => 'components-i',
        1 => 'forms-a11y-i',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'components-i',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'components-i',
            1 => 'forms-a11y-i',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    23 => 
    array (
      'id' => 'v30_1a_26',
      'number' => 26,
      'title' => 'Lekce 26 · Critique workshop',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Dávat a přijímat konkrétní design feedback podle cíle a evidence.',
      'knowledge' => 
      array (
        0 => 'design-feedback',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'design-feedback',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'design-feedback',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    24 => 
    array (
      'id' => 'v30_1a_27',
      'number' => 27,
      'title' => 'Lekce 27 · Landing sprint',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'V týmu vytvořit funkční responzivní landing page od briefu po QA.',
      'knowledge' => 
      array (
        0 => 'content-first-layout',
        1 => 'microcopy-cta',
        2 => 'responsive-art-direction',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'content-first-layout',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'content-first-layout',
            1 => 'microcopy-cta',
            2 => 'responsive-art-direction',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
    25 => 
    array (
      'id' => 'v30_1a_28',
      'number' => 28,
      'title' => 'Lekce 28 · Portfolio & mastery',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Vybrat nejlepší výstup, popsat rozhodnutí a doložit, které skills byly prokázány.',
      'knowledge' => 
      array (
        0 => 'design-feedback',
        1 => 'design-feedback',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Rychlý mentální model',
          'text' => 'Animovaná ukázka + předpověď výsledku.',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Dva způsoby vysvětlení',
          'text' => 'Jednoduché vysvětlení a konkrétní příklad.',
        ),
        2 => 
        array (
          'time' => '25–45',
          'title' => 'Guided practice',
          'text' => 'Jedna změna, jeden test, jedna evidence.',
        ),
        3 => 
        array (
          'time' => '45–70',
          'title' => 'Samostatná aplikace',
          'text' => 'Přenesení principu do vlastního případu.',
        ),
        4 => 
        array (
          'time' => '70–82',
          'title' => 'Peer / QA check',
          'text' => 'Krátký test s konkrétním feedbackem.',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Exit ticket',
          'text' => 'Vysvětli princip vlastními slovy.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'model',
          'time' => '12 min',
          'title' => '01 · Pochop princip více způsoby',
          'kind' => 'knowledge',
          'xp' => 25,
          'knowledge' => 
          array (
            0 => 'design-feedback',
          ),
          'tasks' => 
          array (
            0 => 'Projdi animovaný model.',
            1 => 'Použij alespoň jednu alternativní cestu „Vysvětli jinak“.',
            2 => 'Napiš princip jednou vlastní větou.',
          ),
        ),
        1 => 
        array (
          'id' => 'predict',
          'time' => '12 min',
          'title' => '02 · Předpověz výsledek',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
        2 => 
        array (
          'id' => 'guided',
          'time' => '20 min',
          'title' => '03 · Guided practice',
          'kind' => 'manual',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'design-feedback',
            1 => 'design-feedback',
          ),
          'tasks' => 
          array (
            0 => 'Vyber jeden konkrétní případ.',
            1 => 'Před změnou napiš, co očekáváš.',
            2 => 'Proveď jeden krok a zaznamenej evidence.',
          ),
        ),
        3 => 
        array (
          'id' => 'apply',
          'time' => '25 min',
          'title' => '04 · Samostatná aplikace',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Aplikuj princip na nový příklad.',
            1 => 'Nepiš jen výsledek – přidej důvod a ověření.',
          ),
        ),
        4 => 
        array (
          'id' => 'review',
          'time' => '13 min',
          'title' => '05 · Review / QA',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech výsledek zkontrolovat podle jasného checklistu.',
            1 => 'Oprav jednu konkrétní slabinu nebo vysvětli, proč změna není potřeba.',
          ),
        ),
        5 => 
        array (
          'id' => 'exit',
          'time' => '8 min',
          'title' => '06 · Exit ticket',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Který postup vede k nejlépe obhajitelnému návrhu?',
          'options' => 
          array (
            0 => 'Nejdřív definovat cíl, změnit jednu věc a výsledek ověřit.',
            1 => 'Začít dekoracemi a rozhodnout podle osobního vkusu.',
            2 => 'Měnit několik parametrů současně bez testu.',
          ),
          'correct' => 0,
          'explanation' => 'Návrhové rozhodnutí je silné tehdy, když je propojené s cílem a ověřené konkrétní evidence.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Předpověď: co očekávám před změnou / testem?',
        1 => 'Evidence: co přesně jsem pozoroval/a a co z toho plyne?',
        2 => 'Reflexe: co bych příště vysvětlil/a spolužákovi jinak?',
      ),
      'teacher_notes' => 
      array (
        0 => 'Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.',
        1 => 'Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.',
      ),
    ),
  ),
  'simulations' => 
  array (
    'hierarchy' => 
    array (
      'id' => 'gfx-hierarchy',
      'type' => 'hierarchy',
      'title' => 'Zachraň plakát: vizuální hierarchie',
      'lead' => 'Uprav poměry prvků tak, aby divák během dvou sekund pochopil, co je akce, kdy je a co má udělat.',
      'task' => 'Nastav jasné pořadí: název akce → datum/místo → CTA → doplňující informace.',
      'prediction' => 
      array (
        'q' => 'Co má být na plakátu nejsilnější první vizuální bod?',
        'options' => 
        array (
          0 => 'Doplňkový text',
          1 => 'Název akce',
          2 => 'Logo nástroje',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Představ si plakát jako malý náhled na mobilu. Co musí být čitelné i bez detailů?',
        1 => 'Zvětši rozdíl mezi titulkem a běžným textem a dej důležitým prvkům více prostoru.',
        2 => 'Pro tento úkol zkus titulek alespoň 54 px, info nejvýše 28 px, CTA alespoň 20 px a vertikální spacing alespoň 16 px.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký je nejlepší závěr?',
        'options' => 
        array (
          0 => 'Hierarchie je jen velikost fontu.',
          1 => 'Hierarchie vzniká kombinací velikosti, kontrastu, váhy, pozice a prostoru.',
          2 => 'Každý prvek má být stejně výrazný.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'composition' => 
    array (
      'id' => 'gfx-grid',
      'type' => 'grid',
      'title' => 'Grid Lab: postav pořádek z chaosu',
      'lead' => 'Měň počet sloupců, okraje a mezery. Sleduj, jak se stejný obsah začne nebo přestane vizuálně držet pohromadě.',
      'task' => 'Vytvoř layout s jasnými společnými hranami, dostatkem whitespace a konzistentními rozestupy.',
      'prediction' => 
      array (
        'q' => 'Co grid řeší nejlépe?',
        'options' => 
        array (
          0 => 'Náhodně zvětšuje obrázky',
          1 => 'Vytváří konzistentní vztahy a zarovnání',
          2 => 'Automaticky vybírá barvy',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Sleduj levé a pravé hrany bloků. Kolik různých neviditelných linií vytváříš?',
        1 => 'Větší okraj a konzistentní gutter často pomohou víc než další dekorace.',
        2 => 'Pro tento úkol použij alespoň 4 sloupce, margin 20+ px, gutter 12+ px a zapnuté zarovnání.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč layout působí profesionálněji?',
        'options' => 
        array (
          0 => 'Protože je všechno uprostřed.',
          1 => 'Protože prvky sdílejí systém zarovnání a spacing.',
          2 => 'Protože má co nejvíc objektů.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'export' => 
    array (
      'id' => 'gfx-export',
      'type' => 'export',
      'title' => 'Export Lab: kvalita vs. velikost',
      'lead' => 'Připrav webový hero obrázek. Hledej rovnováhu mezi ostrostí, datovou velikostí a vhodným formátem.',
      'task' => 'Pro webový hero zvol rozumný formát, šířku a kvalitu tak, aby byl výstup ostrý a zbytečně těžký nebyl.',
      'prediction' => 
      array (
        'q' => 'Je pro web vždy nejlepší největší možný soubor?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne, cílem je vhodný kompromis kvality a velikosti',
          2 => 'Jen když je PNG',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Pro fotografický nebo smíšený webový obsah obvykle nepotřebuješ bezztrátový PNG.',
        1 => 'Pro běžný hero stačí rozumná šířka kolem 1920 px a kvalita kolem 80 %.',
        2 => 'Zkus WebP nebo JPG, 1920 px a kvalitu 75–90 %.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je správná exportní strategie?',
        'options' => 
        array (
          0 => 'Vždy 100 % kvalita a největší rozlišení.',
          1 => 'Formát a parametry volím podle cílového média a ověřím kvalitu v reálné velikosti.',
          2 => 'Vše exportuji jako PDF.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'contrast-color' => 
    array (
      'id' => 'gfx-contrast',
      'type' => 'contrast',
      'title' => 'Contrast Lab: je text opravdu čitelný?',
      'lead' => 'Měň barvu pozadí, textu a velikost písma. Sleduj orientační kontrastní poměr, náhled v grayscale a čitelnost v malém formátu.',
      'task' => 'Najdi kombinaci, která je čitelná v běžném i malém náhledu a nepoužívá barvu jako jediný nositel významu.',
      'prediction' => 
      array (
        'q' => 'Stačí, když jsou obě barvy hodně syté?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne, důležitý je i rozdíl světlosti a velikost textu',
          2 => 'Jen u plakátu',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Přepni náhled do grayscale. Pokud text téměř zmizí, sytost sama nepomohla.',
        1 => 'Zkus velmi světlý text na tmavém pozadí nebo naopak.',
        2 => 'Pro tento úkol dostaň orientační poměr alespoň na 4.5 : 1.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je nejlepší závěr?',
        'options' => 
        array (
          0 => 'Kontrast je jen otázka barevného vkusu.',
          1 => 'Čitelnost vzniká kombinací kontrastu, velikosti, řezu a kontextu.',
          2 => 'Neonové barvy jsou vždy nejčitelnější.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'typography' => 
    array (
      'id' => 'gfx-type',
      'type' => 'typography',
      'title' => 'Type Lab: méně stylů, jasnější role',
      'lead' => 'Měň poměr titulku a body textu, řádkování a počet fontů. Náhled ti ukáže, kdy už systém působí roztříštěně.',
      'task' => 'Vytvoř tři čitelné textové úrovně s maximálně dvěma rodinami písem a dostatečným rozdílem mezi titulkem a běžným textem.',
      'prediction' => 
      array (
        'q' => 'Co obvykle zlepší plakát víc?',
        'options' => 
        array (
          0 => 'Přidání čtvrtého dekorativního fontu',
          1 => 'Jasnější rozdíl velikostí a konzistentní textové role',
          2 => 'Náhodná změna zarovnání',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Nejdřív pracuj s velikostí a řezem, až potom s jiným fontem.',
        1 => 'Headline by měl být jasně dominantní a body text pohodlně čitelný.',
        2 => 'Zkus 1–2 fonty, headline alespoň 2× větší než body a line-height kolem 1.3–1.6.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je typografický systém?',
        'options' => 
        array (
          0 => 'Seznam všech fontů, které se mi líbí.',
          1 => 'Opakovatelné role a vztahy mezi textovými úrovněmi.',
          2 => 'Pouze velikost titulku.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'raster-vector' => 
    array (
      'id' => 'gfx-raster-vector',
      'type' => 'rastervector',
      'title' => 'Scale Lab: kdy se logo rozsype?',
      'lead' => 'Porovnej rastrový a vektorový zdroj při zvětšení. Sleduj, jak se mění ostrost a kde dává který typ smysl.',
      'task' => 'Nastav velké zvětšení a vyber vhodný typ zdroje pro logo na plakátu i billboardu.',
      'prediction' => 
      array (
        'q' => 'Co se stane s malým PNG logem při extrémním zvětšení?',
        'options' => 
        array (
          0 => 'Zůstane vždy dokonale ostré',
          1 => 'Může se projevit pixelace',
          2 => 'Automaticky se změní na SVG',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Raster má konečný počet pixelů.',
        1 => 'Logo obvykle dává smysl držet ve vektoru.',
        2 => 'Zkus zoom 800 % a porovnej PNG vs. SVG.',
      ),
      'conclusion' => 
      array (
        'q' => 'Který závěr je správně?',
        'options' => 
        array (
          0 => 'Vektor je vždy lepší i pro fotografie.',
          1 => 'Raster a vektor řeší různé typy obsahu; logo typicky těží z vektoru.',
          2 => 'PNG je vektorový formát.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'color' => 
    array (
      'id' => 'gfx-output-color',
      'type' => 'colormode',
      'title' => 'Screen vs. print: co se stane s neonem?',
      'lead' => 'Přepínej mezi obrazovkovým a simulovaným tiskovým náhledem a sleduj, jak se extrémně syté barvy mohou změnit.',
      'task' => 'Vyber výstup pro web a následně simuluj tisk. Najdi paletu, která zůstává rozumně čitelná v obou režimech.',
      'prediction' => 
      array (
        'q' => 'Bude každá RGB barva na běžném tisku vypadat totožně?',
        'options' => 
        array (
          0 => 'Ano',
          1 => 'Ne',
          2 => 'Jen modrá',
        ),
        'correct' => 1,
      ),
      'hints' => 
      array (
        0 => 'Obrazovka světlo vyzařuje, papír ho odráží.',
        1 => 'Extrémně neonové RGB odstíny jsou dobrý příklad rozdílu.',
        2 => 'Sniž saturaci neonu a sleduj stabilnější výsledek v print preview.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co má designér udělat před důležitým tiskem?',
        'options' => 
        array (
          0 => 'Spoléhat jen na monitor.',
          1 => 'Řídit se specifikací výstupu a kontrolovat proof/export.',
          2 => 'Vždy převést vše do PNG.',
        ),
        'correct' => 1,
      ),
      'points' => 5,
    ),
    'color-harmony' => 
    array (
      'id' => 'gfx-palette',
      'type' => 'palette',
      'title' => 'Palette Lab: dej barvám role',
      'lead' => 'Měň hue akcentu, sílu backgroundu a počet akcentů. Sleduj, kdy systém zůstává čitelný a kdy začne soupeřit sám se sebou.',
      'task' => 'Vytvoř paletu s jedním dominantním akcentem a čitelným textem.',
      'prediction' => 
      array (
        'q' => 'Co je pro začátečnickou paletu nejbezpečnější?',
        'options' => 
        array (
          0 => 'Jeden hlavní akcent + čitelný základ.',
          1 => 'Šest stejně silných barev.',
          2 => 'Každý blok jinou barvou.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni backgroundem a textem.',
        1 => 'Accent má přitáhnout pozornost, ne pokrýt celý design.',
        2 => 'Nastav 1 accent a kontrastní text.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co drží paletu pohromadě?',
        'options' => 
        array (
          0 => 'Jasné role barev.',
          1 => 'Počet gradientů.',
          2 => 'Náhodnost.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'iconography' => 
    array (
      'id' => 'gfx-icons',
      'type' => 'iconset',
      'title' => 'Icon Lab: jedna rodina',
      'lead' => 'Přepínej stroke/fill styl, tloušťku a velikost. Sleduj, jak rychle systém působí nekonzistentně.',
      'task' => 'Nastav tři ikony na společný styl a optickou velikost.',
      'prediction' => 
      array (
        'q' => 'Co je největší problém směsi outline + emoji + 3D?',
        'options' => 
        array (
          0 => 'Ztráta vizuální konzistence.',
          1 => 'Příliš malý datový soubor.',
          2 => 'Nedostatek DNS.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sjednoť style.',
        1 => 'Sjednoť tloušťku.',
        2 => 'Sjednoť velikost.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je dobrý icon system?',
        'options' => 
        array (
          0 => 'Stejná vizuální logika napříč významy.',
          1 => 'Každá ikona jiná.',
          2 => 'Jen velké ikony.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'image-composition' => 
    array (
      'id' => 'gfx-crop',
      'type' => 'cropfocus',
      'title' => 'Crop Lab: kam jde oko?',
      'lead' => 'Posouvej focal point a textový blok. Náhled ukazuje kolizi i klidovou zónu.',
      'task' => 'Umísti focal point a text tak, aby se vzájemně nepřekrývaly a kompozice měla směr.',
      'prediction' => 
      array (
        'q' => 'Je vždy nejlepší dát vše doprostřed?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Vytvoř vztah mezi focal point a textem.',
        1 => 'Nech text v klidnější oblasti.',
        2 => 'Focal point vpravo, text vlevo je dobrý začátek.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je hlavní role cropu?',
        'options' => 
        array (
          0 => 'Řídit význam a prostor v obraze.',
          1 => 'Jen zmenšit soubor.',
          2 => 'Přidat filtr.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'template-critique' => 
    array (
      'id' => 'gfx-critique',
      'type' => 'critique',
      'title' => 'Critique Lab: symptom → příčina → oprava',
      'lead' => 'Zapínej typické chyby šablony a sleduj dopad na čitelnost. Cílem je opravit funkční problém, ne jen změnit styl.',
      'task' => 'Odstraň tři zásadní chyby: plochou hierarchii, nízký kontrast a chaotický spacing.',
      'prediction' => 
      array (
        'q' => 'Která věta je kvalitní kritika?',
        'options' => 
        array (
          0 => 'Datum soupeří s headline, proto ho zmenším a seskupím.',
          1 => 'Je to ošklivé.',
          2 => 'Přidáme efekt.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Najdi první problém s hierarchií.',
        1 => 'Potom kontrast.',
        2 => 'Nakonec spacing.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co má redesign dokazovat?',
        'options' => 
        array (
          0 => 'Lepší funkci vůči cíli.',
          1 => 'Více dekorací.',
          2 => 'Více fontů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'responsive-series' => 
    array (
      'id' => 'gfx-responsive',
      'type' => 'responsivepreview',
      'title' => 'Responsive Preview Lab',
      'lead' => 'Přepínej cílový formát, safe zone a zachování hierarchie. Náhled ukáže, co se při slepém ořezu ztratí.',
      'task' => 'Připrav story variantu se zachovanou hierarchií a bezpečnou zónou.',
      'prediction' => 
      array (
        'q' => 'Stačí master 1080×1350 jen oříznout na story?',
        'options' => 
        array (
          0 => 'Ne, layout a crop se musí znovu posoudit.',
          1 => 'Ano, vždy.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Přepni na story.',
        1 => 'Nech alespoň 8 % safe zone.',
        2 => 'Zachovej hierarchii headline → info → CTA.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co přenášíš mezi formáty?',
        'options' => 
        array (
          0 => 'Systém a hierarchii.',
          1 => 'Přesné souřadnice.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'editorial-typography-i' => 
    array (
      'id' => 'gfx-type-i',
      'type' => 'spacingtokens',
      'title' => 'Type Scale Lab',
      'lead' => 'Měň základní rytmus a sleduj, jak se typografické role skládají do čitelného systému.',
      'task' => 'Vytvoř konzistentní škálu a rozestupy pro display, heading, body a metadata.',
      'prediction' => 
      array (
        'q' => 'Je lepší mít mnoho náhodných velikostí?',
        'options' => 
        array (
          0 => 'Ne, malá konzistentní škála je čitelnější.',
          1 => 'Ano, čím více tím lépe.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Použij několik opakovaných hodnot.',
        1 => 'Zvětši rozdíl mezi display a body.',
        2 => 'Sleduj whitespace mezi rolemi.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co vytváří typografický systém?',
        'options' => 
        array (
          0 => 'Jasné role a opakované vztahy.',
          1 => 'Náhodné změny velikostí.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'layout-rhythm-i' => 
    array (
      'id' => 'gfx-layout-i',
      'type' => 'spacingtokens',
      'title' => 'Vertical Rhythm Lab',
      'lead' => 'Experimentuj s opakovanými spacing hodnotami a pozoruj, jak se z chaosu stane předvídatelný layout.',
      'task' => 'Použij omezenou spacing škálu a vytvoř jasné skupiny obsahu.',
      'prediction' => 
      array (
        'q' => 'Je každá mezera samostatné rozhodnutí?',
        'options' => 
        array (
          0 => 'Ne, rytmus má používat opakovaný systém.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni 8px základem.',
        1 => 'Menší mezery drž uvnitř skupiny.',
        2 => 'Větší mezery oddělují sekce.',
      ),
      'conclusion' => 
      array (
        'q' => 'K čemu whitespace slouží?',
        'options' => 
        array (
          0 => 'Komunikuje vztahy a prioritu.',
          1 => 'Jen zabírá místo.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'visual-story-i' => 
    array (
      'id' => 'gfx-story-i',
      'type' => 'cropfocus',
      'title' => 'Visual Story Lab',
      'lead' => 'Měň focal point a crop fotografie a sleduj, jak se mění cesta pohledu mezi obrazem, headline a CTA.',
      'task' => 'Nastav výřez tak, aby obraz podporoval headline a CTA.',
      'prediction' => 
      array (
        'q' => 'Může směr fotografie ovlivnit čtení layoutu?',
        'options' => 
        array (
          0 => 'Ano.',
          1 => 'Ne.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Najdi hlavní subjekt.',
        1 => 'Nenech ho soutěžit s headline.',
        2 => 'Ověř malý náhled.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je cílem focal pointu?',
        'options' => 
        array (
          0 => 'Zahájit zamýšlenou cestu pozornosti.',
          1 => 'Být vždy uprostřed.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'production-preflight-i' => 
    array (
      'id' => 'gfx-preflight-i',
      'type' => 'responsivepreview',
      'title' => 'Preflight Lab',
      'lead' => 'Přepínej cílový formát a kontroluj safe zone, velikost a konzistenci před exportem.',
      'task' => 'Připrav export tak, aby odpovídal cílovému formátu a zachoval důležité prvky.',
      'prediction' => 
      array (
        'q' => 'Stačí exportovat bez kontroly výsledného souboru?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Zkontroluj rozměr.',
        1 => 'Ověř safe zone.',
        2 => 'Otevři exportovaný soubor.',
      ),
      'conclusion' => 
      array (
        'q' => 'Kdy je práce skutečně hotová?',
        'options' => 
        array (
          0 => 'Po kontrole finálního exportu.',
          1 => 'Po kliknutí na Export.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'web-layout-basics-i' => 
    array (
      'id' => 'yp-web-layout-basics-i',
      'type' => 'grid',
      'title' => 'Anatomie webové stránky · Lab',
      'lead' => 'Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'responsive-layout-i' => 
    array (
      'id' => 'yp-responsive-layout-i',
      'type' => 'responsivepreview',
      'title' => 'Responsive layout I · Lab',
      'lead' => 'Responzivní návrh zachovává informační prioritu a přeskupuje obsah podle dostupného prostoru.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'components-i' => 
    array (
      'id' => 'yp-components-i',
      'type' => 'components',
      'title' => 'Komponenty I: opakovatelné UI vzory · Lab',
      'lead' => 'Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'web-typography-i' => 
    array (
      'id' => 'yp-web-typography-i',
      'type' => 'typography',
      'title' => 'Webová typografie I · Lab',
      'lead' => 'Typografie na obrazovce musí být skenovatelná, čitelná a odolná vůči změně šířky.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'image-web-i' => 
    array (
      'id' => 'yp-image-web-i',
      'type' => 'export',
      'title' => 'Obraz a assety pro web · Lab',
      'lead' => 'Webový obraz musí podporovat obsah, mít správný crop a odpovídat skutečnému použití.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'forms-a11y-i' => 
    array (
      'id' => 'yp-forms-a11y-i',
      'type' => 'accessibilityaudit',
      'title' => 'Formuláře a přístupnost I · Lab',
      'lead' => 'Formulář musí být srozumitelný v defaultu, při focusu i při chybě.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'design-system-i' => 
    array (
      'id' => 'yp-design-system-i',
      'type' => 'spacingtokens',
      'title' => 'Mini design systém I · Lab',
      'lead' => 'Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.',
      'task' => 'Nastav nebo ověř stav tak, aby odpovídal principu této lekce.',
      'prediction' => 
      array (
        'q' => 'Je dobré před změnou nebo návrhem nejprve určit cíl a očekávaný výsledek?',
        'options' => 
        array (
          0 => 'Ano — nejprve potřebuji cíl/hypotézu a pak důkaz.',
          1 => 'Ne — nejlepší je měnit věci náhodně a až potom hledat důvod.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni nejmenší ověřitelnou částí problému.',
        1 => 'Porovnej očekávaný a skutečný stav.',
        2 => 'Po změně zopakuj původní test.',
      ),
      'conclusion' => 
      array (
        'q' => 'Jaký způsob práce je přenositelný do dalšího úkolu?',
        'options' => 
        array (
          0 => 'Cíl/hypotéza → malý test nebo návrh → evidence → změna → validace.',
          1 => 'Co nejvíce kroků bez zapisování důkazů.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
  ),
  'learningResources' => 
  array (
    '_default' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
      1 => 
      array (
        'type' => 'course',
        'title' => 'Canva · Graphic Design Essentials',
        'meta' => 'Volitelný kurz · principy designu',
        'url' => 'https://www.canva.com/design-school/courses/graphic-design-essentials?lesson=understanding-the-principles-of-design',
      ),
    ),
    'hierarchy' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'composition' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'contrast-color' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Barevné prostory – Lab, CMYK a RGB',
        'url' => 'https://www.youtube.com/watch?v=7qPLnHLaBRQ',
        'youtube_id' => '7qPLnHLaBRQ',
        'lang' => 'CZ',
        'duration' => 'cca 20–30 min',
        'level' => 'Středně pokročilý',
        'description' => 'České vysvětlení barevných prostorů a proč se stejné barvy mohou na obrazovce a ve výstupu chovat jinak.',
      ),
    ),
    'typography' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'raster-vector' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak na vektorovou grafiku #1 – úvod a Inkscape',
        'url' => 'https://www.youtube.com/watch?v=9gz3GIP6r5w',
        'youtube_id' => '9gz3GIP6r5w',
        'lang' => 'CZ',
        'duration' => 'cca 10–15 min',
        'level' => 'Začátečník',
        'description' => 'Srozumitelný český úvod do vektorové grafiky, jejích výhod, omezení a vhodného použití.',
      ),
    ),
    'color' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Barevné prostory – Lab, CMYK a RGB',
        'url' => 'https://www.youtube.com/watch?v=7qPLnHLaBRQ',
        'youtube_id' => '7qPLnHLaBRQ',
        'lang' => 'CZ',
        'duration' => 'cca 20–30 min',
        'level' => 'Středně pokročilý',
        'description' => 'České vysvětlení barevných prostorů a proč se stejné barvy mohou na obrazovce a ve výstupu chovat jinak.',
      ),
    ),
    'export' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'assets' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'branding-system' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'photo-treatment' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'responsive-adaptation' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'portfolio-presentation' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'ui-spacing-system' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'component-consistency' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'microinteraction-storyboard' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'portfolio-case-study' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'accessibility-design' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'responsive-type-ii' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'design-tokens-ii' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'interaction-patterns-ii' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'design-critique-ii' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'spacing' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'image-crop' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'cta' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'preflight' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'color-harmony' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Barevné prostory – Lab, CMYK a RGB',
        'url' => 'https://www.youtube.com/watch?v=7qPLnHLaBRQ',
        'youtube_id' => '7qPLnHLaBRQ',
        'lang' => 'CZ',
        'duration' => 'cca 20–30 min',
        'level' => 'Středně pokročilý',
        'description' => 'České vysvětlení barevných prostorů a proč se stejné barvy mohou na obrazovce a ve výstupu chovat jinak.',
      ),
    ),
    'iconography' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Jak na vektorovou grafiku #1 – úvod a Inkscape',
        'url' => 'https://www.youtube.com/watch?v=9gz3GIP6r5w',
        'youtube_id' => '9gz3GIP6r5w',
        'lang' => 'CZ',
        'duration' => 'cca 10–15 min',
        'level' => 'Začátečník',
        'description' => 'Srozumitelný český úvod do vektorové grafiky, jejích výhod, omezení a vhodného použití.',
      ),
    ),
    'image-composition' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'template-critique' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'responsive-series' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'editorial-typography-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'layout-rhythm-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'visual-story-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'production-preflight-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'web-layout-basics-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'responsive-layout-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'components-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'web-typography-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'image-web-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Kompozice ve fotografii 1 – čtení fotografie a pravidlo třetin',
        'url' => 'https://www.youtube.com/watch?v=xq1KMC1I6DY',
        'youtube_id' => 'xq1KMC1I6DY',
        'lang' => 'CZ',
        'duration' => 'cca 10–20 min',
        'level' => 'Začátečník',
        'description' => 'Český vizuální výklad kompozice, práce s pozorností diváka, pravidlem třetin a čtením obrazu.',
      ),
    ),
    'forms-a11y-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'design-system-i' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Figma do hloubky: Auto layout, komponenty a interakce',
        'url' => 'https://www.youtube.com/watch?v=2MfzBQoTC1o',
        'youtube_id' => '2MfzBQoTC1o',
        'lang' => 'CZ',
        'duration' => '90 min',
        'level' => 'Středně pokročilý',
        'description' => 'Český workshop Česko.Digital o Auto Layoutu, komponentách, variantách, mikrointerakcích a základech design systémů.',
      ),
    ),
    'content-first-layout' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'microcopy-cta' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
    'responsive-art-direction' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Nejkratší Figma Kurz na Světě',
        'url' => 'https://www.youtube.com/watch?v=2AN92es01YQ',
        'youtube_id' => '2AN92es01YQ',
        'lang' => 'CZ',
        'duration' => '18 min',
        'level' => 'Začátečník',
        'description' => 'Rychlý český průchod Figmou: frame, text, barvy, grid, Auto Layout, komponenty a jednoduchá landing page.',
      ),
    ),
    'design-feedback' => 
    array (
      0 => 
      array (
        'type' => 'video',
        'title' => 'Canva – ovládni design v Canvě',
        'url' => 'https://www.youtube.com/watch?v=ZLeahsLAw-U',
        'youtube_id' => 'ZLeahsLAw-U',
        'lang' => 'CZ',
        'duration' => '30 min',
        'level' => 'Začátečník',
        'description' => 'Praktický český průvodce Canvou: dashboard, mřížky, fotografie, tvary, šablony a export hotového návrhu.',
      ),
    ),
  ),
  '_sources_hash' => '2515a71fc18766cb957672597cc4f11e9d250fce27d350d3506a7ad643f968c7',
);
