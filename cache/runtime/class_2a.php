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
  ),
  'nextLesson' => 
  array (
    'id' => 'graphics_campaign_system',
    'title' => 'Lekce 2 · Z jednoho plakátu udělej malou kampaň',
    'subtitle' => 'Dalších 2 × 45 minut',
    'goal' => 'Převést master plakát do konzistentního systému: master 1080×1350, square 1080×1080 a story 1080×1920. Student se učí adaptaci, photo treatment a obhajobu design systému.',
    'unlock_note' => 'Odemkne se po dokončení Design Studia z prvního bloku.',
    'knowledge' => 
    array (
      0 => 'branding-system',
      1 => 'photo-treatment',
      2 => 'responsive-adaptation',
      3 => 'export',
      4 => 'portfolio-presentation',
    ),
    'schedule' => 
    array (
      0 => 
      array (
        'time' => '0–12',
        'title' => 'Rozbor systému',
        'text' => 'Definuj konstanty kampaně: typografie, barvy, obraz, grid, CTA.',
      ),
      1 => 
      array (
        'time' => '12–25',
        'title' => 'Photo treatment',
        'text' => 'Vyřeš focal point, crop a textovou zónu.',
      ),
      2 => 
      array (
        'time' => '25–45',
        'title' => 'Master refresh',
        'text' => 'Zpevni master plakát jako zdroj pravidel pro adaptace.',
      ),
      3 => 
      array (
        'time' => '45–60',
        'title' => 'Square adaptace',
        'text' => '1080×1080 — přeuspořádej, ne jen ořízni.',
      ),
      4 => 
      array (
        'time' => '60–73',
        'title' => 'Story adaptace',
        'text' => '1080×1920 — pracuj se safe area a vertikálním rytmem.',
      ),
      5 => 
      array (
        'time' => '73–83',
        'title' => 'Export matrix',
        'text' => 'Připrav všechny tři výstupy a zkontroluj konzistenci.',
      ),
      6 => 
      array (
        'time' => '83–90',
        'title' => 'Mini case presentation',
        'text' => 'Stručně obhaj systém a jednu zásadní změnu mezi formáty.',
      ),
    ),
    'steps' => 
    array (
      0 => 
      array (
        'id' => 'system',
        'time' => '12 min',
        'title' => '01 · Definuj design systém',
        'kind' => 'knowledge',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'branding-system',
        ),
        'intro' => 'Z původního plakátu vytáhni pravidla, která musí přežít všechny formáty.',
        'tasks' => 
        array (
          0 => 'Dokonči Knowledge Tour „Mini vizuální systém“.',
          1 => 'Zapiš 2 fontové role.',
          2 => 'Zapiš 3 barvy max.',
          3 => 'Zapiš pravidlo obrazu/cropu.',
          4 => 'Zapiš podobu CTA.',
        ),
      ),
      1 => 
      array (
        'id' => 'photo',
        'time' => '13 min',
        'title' => '02 · Photo treatment',
        'kind' => 'quiz',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'photo-treatment',
        ),
        'intro' => 'Master má výraznou fotografii. Square formát ji ale ořízne jinak.',
        'question' => 'Co máš při adaptaci fotografie zachovat?',
        'options' => 
        array (
          0 => 'Přesné souřadnice cropu.',
          1 => 'Focal point a funkční prostor pro text.',
          2 => 'Stejný počet pixelů vlevo a vpravo.',
        ),
        'correct' => 1,
        'explanation' => 'Formát mění crop, ale hlavní subjekt a čitelnost musí zůstat.',
        'tasks' => 
        array (
          0 => 'Dokonči Knowledge Tour „Práce s fotografií“.',
          1 => 'Připrav crop master + square + story.',
        ),
      ),
      2 => 
      array (
        'id' => 'master',
        'time' => '20 min',
        'title' => '03 · Master jako zdroj pravidel',
        'kind' => 'manual',
        'xp' => 25,
        'intro' => 'Oprav původní 1080×1350 návrh tak, aby byl dobrým masterem.',
        'tasks' => 
        array (
          0 => 'Jasná hierarchie headline → info → CTA.',
          1 => 'Konzistentní grid.',
          2 => 'Jedna hlavní akcentní barva.',
          3 => 'Definovaný treatment fotografie.',
          4 => 'Bez náhodných dekorací, které nepůjdou adaptovat.',
        ),
        'done_label' => 'Master má 5 jasných pravidel',
      ),
      3 => 
      array (
        'id' => 'adapt',
        'time' => '28 min',
        'title' => '04 · Square + Story',
        'kind' => 'knowledge',
        'xp' => 35,
        'knowledge' => 
        array (
          0 => 'responsive-adaptation',
        ),
        'intro' => 'Vytvoř dvě adaptace. Obsahové priority zůstávají, layout se může zásadně změnit.',
        'tasks' => 
        array (
          0 => 'Dokonči Knowledge Tour „Adaptace designu“.',
          1 => 'Square 1080×1080: nový crop + vlastní layout.',
          2 => 'Story 1080×1920: safe area + vertikální rytmus.',
          3 => 'V obou zachovej paletu, typografické role a CTA charakter.',
        ),
      ),
      4 => 
      array (
        'id' => 'export_matrix',
        'time' => '10 min',
        'title' => '05 · Export matrix',
        'kind' => 'quiz',
        'xp' => 20,
        'knowledge' => 
        array (
          0 => 'export',
        ),
        'intro' => 'Máš tři formáty stejné kampaně.',
        'question' => 'Jaký je správný exportní postup?',
        'options' => 
        array (
          0 => 'Exportovat každý formát samostatně a otevřít jej mimo editor.',
          1 => 'Exportovat jen master a ostatní nechat jako screenshot.',
          2 => 'Vše uložit do jednoho obřího PNG bez kontroly.',
        ),
        'correct' => 0,
        'explanation' => 'Každý formát je samostatný výstup a musí projít vlastním QA.',
        'tasks' => 
        array (
          0 => 'Zkontroluj rozměry všech tří souborů.',
          1 => 'Pojmenuj soubory konzistentně.',
          2 => 'Otevři exporty mimo Canvu.',
        ),
      ),
      5 => 
      array (
        'id' => 'presentation',
        'time' => '7 min',
        'title' => '06 · Mini obhajoba kampaně',
        'kind' => 'knowledge',
        'xp' => 25,
        'knowledge' => 
        array (
          0 => 'portfolio-presentation',
        ),
        'intro' => 'Dokonči krátký case slide / poznámku, která vysvětlí systém.',
        'tasks' => 
        array (
          0 => 'Dokonči Knowledge Tour „Prezentace procesu“.',
          1 => 'Napiš cíl kampaně jednou větou.',
          2 => 'Uveď 3 konstanty systému.',
          3 => 'Popiš jednu změnu square nebo story a proč byla nutná.',
        ),
      ),
    ),
    'finisher' => 
    array (
      'title' => 'Rychlík · Varianta B bez změny obsahu',
      'duration' => '15–25 min',
      'text' => 'Vytvoř alternativní art direction stejné kampaně, ale zachovej stejné informace a CTA.',
      'deliverables' => 
      array (
        0 => 'varianta B master',
        1 => '1 adaptace',
        2 => 'krátké A/B zdůvodnění',
      ),
    ),
  ),
  'extendedLessons' => 
  array (
    0 => 
    array (
      'id' => 'graphics_ui_hero',
      'number' => 3,
      'title' => 'Lekce 3 · UI hero sekce: grafika potkává web',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést principy plakátu do responzivní hero sekce webu a pochopit hierarchii, CTA, grid a adaptaci.',
      'knowledge' => 
      array (
        0 => 'hierarchy',
        1 => 'composition',
        2 => 'contrast-color',
        3 => 'responsive-adaptation',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Rozbor hero',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Desktop grid',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Desktop návrh',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Mobile adaptace',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'CTA + kontrast',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Prezentace',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'analyze',
          'time' => '12 min',
          'title' => '01 · Co musí hero sdělit',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Headline jednou větou.',
            1 => 'Doplňující text max. 2 řádky.',
            2 => 'Jedno primární CTA.',
          ),
        ),
        1 => 
        array (
          'id' => 'grid',
          'time' => '16 min',
          'title' => '02 · Desktop grid',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'composition',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni 12sloupcový grid.',
            1 => 'Urči textovou a obrazovou zónu.',
          ),
        ),
        2 => 
        array (
          'id' => 'desktop',
          'time' => '17 min',
          'title' => '03 · Desktop 1440 px',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Postav hero v Canvě/Figmě.',
            1 => 'Ověř hierarchii a kontrast.',
          ),
        ),
        3 => 
        array (
          'id' => 'mobile',
          'time' => '17 min',
          'title' => '04 · Mobile 390 px',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'responsive-adaptation',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Přeskup layout pro mobil.',
            1 => 'Zachovej obsahové priority.',
          ),
        ),
        4 => 
        array (
          'id' => 'cta',
          'time' => '18 min',
          'title' => '05 · CTA a stav kontrastu',
          'kind' => 'quiz',
          'knowledge' => 
          array (
            0 => 'contrast-color',
          ),
          'xp' => 25,
          'question' => 'Co je u CTA nejdůležitější?',
          'options' => 
          array (
            0 => 'Aby byla rozpoznatelná jako akce a měla dostatečný kontrast.',
            1 => 'Aby měla nejvíc efektů na stránce.',
            2 => 'Aby byla vždy největším prvkem.',
          ),
          'correct' => 0,
          'explanation' => 'CTA musí být jasná a čitelná, ale stále respektovat celkovou hierarchii.',
        ),
        5 => 
        array (
          'id' => 'present',
          'time' => '10 min',
          'title' => '06 · Mini case',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Ukaž desktop + mobile.',
            1 => 'Vysvětli jednu změnu layoutu a proč byla nutná.',
          ),
        ),
      ),
    ),
    1 => 
    array (
      'id' => 'graphics_motion_storyboard',
      'number' => 4,
      'title' => 'Lekce 4 · Motion storyboard pro sociální sítě',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout 4–6 snímků krátkého motion/story videa dřív, než se otevře animační software.',
      'knowledge' => 
      array (
        0 => 'hierarchy',
        1 => 'typography',
        2 => 'responsive-adaptation',
        3 => 'portfolio-presentation',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–10',
          'title' => 'Cíl a timing',
        ),
        1 => 
        array (
          'time' => '10–25',
          'title' => 'Storyboard',
        ),
        2 => 
        array (
          'time' => '25–42',
          'title' => 'Keyframes',
        ),
        3 => 
        array (
          'time' => '42–60',
          'title' => 'Typografie v čase',
        ),
        4 => 
        array (
          'time' => '60–78',
          'title' => 'Prototype',
        ),
        5 => 
        array (
          'time' => '78–90',
          'title' => 'Review',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'goal',
          'time' => '10 min',
          'title' => '01 · Jedna zpráva / 6 sekund',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Definuj jedinou zprávu.',
            1 => 'Definuj CTA.',
            2 => 'Zvol délku 6–10 s.',
          ),
        ),
        1 => 
        array (
          'id' => 'story',
          'time' => '15 min',
          'title' => '02 · Storyboard',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nakresli 4–6 framů.',
            1 => 'Každý frame má jediný hlavní úkol.',
          ),
        ),
        2 => 
        array (
          'id' => 'keyframes',
          'time' => '17 min',
          'title' => '03 · Keyframes',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'hierarchy',
            1 => 'typography',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř čitelnost každého klíčového framu.',
            1 => 'Zachovej typografické role.',
          ),
        ),
        3 => 
        array (
          'id' => 'time',
          'time' => '18 min',
          'title' => '04 · Hierarchie v čase',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co znamená hierarchie v motionu?',
          'options' => 
          array (
            0 => 'Řídíš nejen velikost a kontrast, ale i okamžik, kdy se informace objeví.',
            1 => 'Vše musí být vidět od první sekundy.',
            2 => 'Každý frame má mít jiný font.',
          ),
          'correct' => 0,
          'explanation' => 'Čas je další osa hierarchie.',
        ),
        4 => 
        array (
          'id' => 'prototype',
          'time' => '18 min',
          'title' => '05 · Rychlý prototyp',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vytvoř jednoduchou animaci/prototyp.',
            1 => 'Nepřidávej efekt bez funkce.',
            2 => 'Ověř CTA na posledním framu.',
          ),
        ),
        5 => 
        array (
          'id' => 'review',
          'time' => '12 min',
          'title' => '06 · Review',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'portfolio-presentation',
          ),
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Exportuj krátké video/GIF nebo storyboard PDF.',
            1 => 'Vysvětli timing jedné informace.',
          ),
        ),
      ),
    ),
    2 => 
    array (
      'id' => 'graphics_design_system',
      'number' => 5,
      'title' => 'Lekce 5 · Design systém: spacing, komponenty, stavy',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést vizuální intuici do opakovatelného systému komponent a spacing tokenů pro jednoduché UI.',
      'knowledge' => 
      array (
        0 => 'ui-spacing-system',
        1 => 'component-consistency',
        2 => 'contrast-color',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–12',
          'title' => 'Spacing tokens',
        ),
        1 => 
        array (
          'time' => '12–28',
          'title' => 'Komponenty',
        ),
        2 => 
        array (
          'time' => '28–45',
          'title' => 'Button states',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Card system',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Mini UI',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Audit',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'spacing',
          'time' => '12 min',
          'title' => '01 · Spacing tokeny',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'ui-spacing-system',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Dokonči spacing simulaci.',
            1 => 'Vyber 4–5 tokenů.',
            2 => 'Použij je místo náhodných mezer.',
          ),
        ),
        1 => 
        array (
          'id' => 'components',
          'time' => '16 min',
          'title' => '02 · Komponenta jako pravidlo',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'component-consistency',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj button, card a tag.',
            1 => 'Sjednoť radius a padding.',
            2 => 'Nastav textové role.',
          ),
        ),
        2 => 
        array (
          'id' => 'states',
          'time' => '17 min',
          'title' => '03 · Stavy prvku',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni default, hover a disabled.',
            1 => 'Zachovej rozpoznatelnost akce.',
            2 => 'Ověř kontrast textu.',
          ),
        ),
        3 => 
        array (
          'id' => 'cards',
          'time' => '17 min',
          'title' => '04 · Systém 3 karet',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř 3 obsahově různé karty.',
            1 => 'Struktura musí být konzistentní.',
            2 => 'Nepoužívej unikátní spacing pro každou kartu.',
          ),
        ),
        4 => 
        array (
          'id' => 'ui',
          'time' => '18 min',
          'title' => '05 · Mini dashboard',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Poskládej header + 3 cards + CTA.',
            1 => 'Použij pouze svůj systém.',
            2 => 'Otestuj 1440 i 390 px.',
          ),
        ),
        5 => 
        array (
          'id' => 'audit',
          'time' => '10 min',
          'title' => '06 · System audit',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Kdy design systém skutečně pomáhá?',
          'options' => 
          array (
            0 => 'Když stejné role vedou ke stejným pravidlům napříč obrazovkou.',
            1 => 'Když má každý prvek vlastní náhodnou hodnotu.',
            2 => 'Když je komponenta použitá právě jednou.',
          ),
          'correct' => 0,
          'explanation' => 'Systém zrychluje rozhodování díky opakovatelnosti a konzistenci.',
        ),
      ),
    ),
    3 => 
    array (
      'id' => 'graphics_portfolio_proto',
      'number' => 6,
      'title' => 'Lekce 6 · Portfolio case study + mikrointerakce',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Připravit krátkou case study vlastního projektu a navrhnout jednoduchou mikrointerakci, která má jasnou funkci.',
      'knowledge' => 
      array (
        0 => 'portfolio-case-study',
        1 => 'microinteraction-storyboard',
        2 => 'portfolio-presentation',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Case story',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Before/after',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Mikrointerakce',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Storyboard',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Prototype',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Review',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'story',
          'time' => '15 min',
          'title' => '01 · Problém → rozhodnutí → výsledek',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'portfolio-case-study',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Napiš problém.',
            1 => 'Vyber 2 klíčová rozhodnutí.',
            2 => 'Ukaž výsledek.',
          ),
        ),
        1 => 
        array (
          'id' => 'before',
          'time' => '15 min',
          'title' => '02 · Evidence procesu',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Připrav before/after.',
            1 => 'Přidej jeden wireframe.',
            2 => 'Přidej krátkou anotaci.',
          ),
        ),
        2 => 
        array (
          'id' => 'micro',
          'time' => '15 min',
          'title' => '03 · Proč animovat',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'microinteraction-storyboard',
          ),
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vyber jednu akci: hover, success nebo loading.',
            1 => 'Definuj trigger.',
            2 => 'Definuj feedback.',
          ),
        ),
        3 => 
        array (
          'id' => 'board',
          'time' => '17 min',
          'title' => '04 · Storyboard 4 framy',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nakresli start.',
            1 => 'Nakresli přechod.',
            2 => 'Nakresli výsledek.',
            3 => 'Uveď délku.',
          ),
        ),
        4 => 
        array (
          'id' => 'proto',
          'time' => '18 min',
          'title' => '05 · Rychlý prototyp',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Vytvoř jednoduchý prototype/GIF/video.',
            1 => 'Použij pohyb pouze pro feedback.',
            2 => 'Ověř, že i bez animace zůstává stav pochopitelný.',
          ),
        ),
        5 => 
        array (
          'id' => 'review',
          'time' => '10 min',
          'title' => '06 · Portfolio review',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'portfolio-presentation',
          ),
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Uspořádej case study do 5 bloků.',
            1 => 'Zkrať text.',
            2 => 'Připrav 30s obhajobu.',
          ),
        ),
      ),
    ),
    4 => 
    array (
      'id' => 'graphics_accessibility',
      'number' => 7,
      'title' => 'Lekce 7 · Accessibility & responsive UI',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout jednoduchou responzivní UI sekci, která zůstává čitelná, ovladatelná a srozumitelná v různých stavech.',
      'knowledge' => 
      array (
        0 => 'accessibility-design',
        1 => 'ui-spacing-system',
        2 => 'component-consistency',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Accessibility audit',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Contrast + focus',
        ),
        2 => 
        array (
          'time' => '30–48',
          'title' => 'Desktop',
        ),
        3 => 
        array (
          'time' => '48–65',
          'title' => 'Mobile',
        ),
        4 => 
        array (
          'time' => '65–82',
          'title' => 'States',
        ),
        5 => 
        array (
          'time' => '82–90',
          'title' => 'Review',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'audit',
          'time' => '15 min',
          'title' => '01 · Accessibility audit',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'accessibility-design',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Accessibility Audit Lab.',
            1 => 'Ověř kontrast.',
            2 => 'Ověř focus.',
          ),
        ),
        1 => 
        array (
          'id' => 'tokens',
          'time' => '15 min',
          'title' => '02 · Čitelnost + spacing',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'ui-spacing-system',
          ),
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Nastav textové role.',
            1 => 'Zvol spacing tokeny.',
            2 => 'Udrž click target.',
          ),
        ),
        2 => 
        array (
          'id' => 'desktop',
          'time' => '18 min',
          'title' => '03 · Desktop komponenta',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni card + CTA.',
            1 => 'Použij komponentní pravidla.',
            2 => 'Přidej focus state.',
          ),
        ),
        3 => 
        array (
          'id' => 'mobile',
          'time' => '17 min',
          'title' => '04 · Mobile adaptace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Přepni na 390 px.',
            1 => 'Zachovej pořadí čtení.',
            2 => 'Ověř text i touch targety.',
          ),
        ),
        4 => 
        array (
          'id' => 'states',
          'time' => '17 min',
          'title' => '05 · Error / disabled / focus',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Navrhni 3 stavy.',
            1 => 'Nepoužívej jen barvu.',
            2 => 'Ověř konzistenci.',
          ),
        ),
        5 => 
        array (
          'id' => 'review',
          'time' => '8 min',
          'title' => '06 · Accessibility review',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co je největší chyba u error stavu?',
          'options' => 
          array (
            0 => 'Význam je sdělen pouze červenou barvou bez textu nebo ikony.',
            1 => 'Text má dostatečný kontrast.',
            2 => 'Focus je viditelný.',
          ),
          'correct' => 0,
          'explanation' => 'Stav nesmí záviset jen na vnímání barvy.',
        ),
      ),
    ),
    5 => 
    array (
      'id' => 'graphics_type_system_ii',
      'number' => 8,
      'title' => 'Lekce 8 · Typografie II + Design systém II',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navázat na první ročník a převést typografii, spacing a komponenty do responsivního systému řízeného tokeny.',
      'knowledge' => 
      array (
        0 => 'responsive-type-ii',
        1 => 'design-tokens-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Responsive type',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Viewport audit',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Token layers',
        ),
        3 => 
        array (
          'time' => '45–62',
          'title' => 'Components',
        ),
        4 => 
        array (
          'time' => '62–82',
          'title' => 'Mini system',
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
          'id' => 'type',
          'time' => '15 min',
          'title' => '01 · Responsive typografie',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'responsive-type-ii',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Responsive Type Lab.',
            1 => 'Definuj desktop/mobile role.',
            2 => 'Ověř headline wrap.',
          ),
        ),
        1 => 
        array (
          'id' => 'audit',
          'time' => '15 min',
          'title' => '02 · Viewport audit',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Ověř 390/768/1440 px.',
            1 => 'Najdi nečitelný stav.',
            2 => 'Uprav line-height nebo width.',
          ),
        ),
        2 => 
        array (
          'id' => 'tokens',
          'time' => '15 min',
          'title' => '03 · Semantic tokens',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'design-tokens-ii',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Semantic Token Lab.',
            1 => 'Odděl primitive/semantic.',
            2 => 'Pojmenuj spacing a color tokeny.',
          ),
        ),
        3 => 
        array (
          'id' => 'components',
          'time' => '17 min',
          'title' => '04 · Varianty komponent',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř Button varianty.',
            1 => 'Napoj tokeny.',
            2 => 'Přidej hover/focus/disabled.',
          ),
        ),
        4 => 
        array (
          'id' => 'system',
          'time' => '20 min',
          'title' => '05 · Mini design system',
          'kind' => 'manual',
          'xp' => 35,
          'tasks' => 
          array (
            0 => 'Sestav 3 komponenty.',
            1 => 'Použij jeden type scale.',
            2 => 'Dokumentuj tokeny a stavy.',
          ),
        ),
        5 => 
        array (
          'id' => 'qa',
          'time' => '8 min',
          'title' => '06 · System QA',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co je nejsilnější známka design systému?',
          'options' => 
          array (
            0 => 'Změna tokenu se předvídatelně propíše do relevantních komponent.',
            1 => 'Každá komponenta má vlastní náhodné hodnoty.',
            2 => 'Všechny obrazovky jsou pixelově stejné.',
          ),
          'correct' => 0,
          'explanation' => 'Systém vytváří sdílená pravidla a snižuje lokální duplicity.',
        ),
      ),
    ),
    6 => 
    array (
      'id' => 'graphics_interaction_handoff',
      'number' => 9,
      'title' => 'Lekce 9 · Interakce II + profesionální handoff',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout kompletní stavový model interakce, přidat smysluplný motion feedback a obhájit řešení pomocí evidence.',
      'knowledge' => 
      array (
        0 => 'interaction-patterns-ii',
        1 => 'design-critique-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'State model',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Motion',
        ),
        2 => 
        array (
          'time' => '30–47',
          'title' => 'Prototype',
        ),
        3 => 
        array (
          'time' => '47–62',
          'title' => 'Reduced motion',
        ),
        4 => 
        array (
          'time' => '62–80',
          'title' => 'Critique',
        ),
        5 => 
        array (
          'time' => '80–90',
          'title' => 'Handoff',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'states',
          'time' => '15 min',
          'title' => '01 · Stavový model',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'interaction-patterns-ii',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči State & Motion Lab.',
            1 => 'Sepiš default/loading/success/error.',
            2 => 'Přidej focus/disabled.',
          ),
        ),
        1 => 
        array (
          'id' => 'motion',
          'time' => '15 min',
          'title' => '02 · Motion jako feedback',
          'kind' => 'manual',
          'xp' => 20,
          'tasks' => 
          array (
            0 => 'Definuj trigger.',
            1 => 'Nastav krátký feedback.',
            2 => 'Odstraň zbytečný pohyb.',
          ),
        ),
        2 => 
        array (
          'id' => 'prototype',
          'time' => '17 min',
          'title' => '03 · Prototype',
          'kind' => 'manual',
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Propoj stavy.',
            1 => 'Otestuj success/error.',
            2 => 'Zkontroluj čitelnost feedbacku.',
          ),
        ),
        3 => 
        array (
          'id' => 'reduced',
          'time' => '15 min',
          'title' => '04 · Reduced motion',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř variantu bez pohybu.',
            1 => 'Zachovej význam stavu.',
            2 => 'Ověř keyboard focus.',
          ),
        ),
        4 => 
        array (
          'id' => 'critique',
          'time' => '18 min',
          'title' => '05 · Design critique',
          'kind' => 'knowledge',
          'knowledge' => 
          array (
            0 => 'design-critique-ii',
          ),
          'xp' => 30,
          'tasks' => 
          array (
            0 => 'Dokonči Design Critique Lab.',
            1 => 'Formuluj cíl a evidence.',
            2 => 'Přidej konkrétní doporučení.',
          ),
        ),
        5 => 
        array (
          'id' => 'handoff',
          'time' => '10 min',
          'title' => '06 · Handoff',
          'kind' => 'quiz',
          'xp' => 20,
          'question' => 'Co má obsahovat handoff interaktivní komponenty?',
          'options' => 
          array (
            0 => 'Stavy, tokeny, chování, accessibility poznámky a očekávaný feedback.',
            1 => 'Jen jeden screenshot default stavu.',
            2 => 'Pouze název komponenty.',
          ),
          'correct' => 0,
          'explanation' => 'Implementace potřebuje znát celý stavový model, ne jen statický vzhled.',
        ),
      ),
    ),
    7 => 
    array (
      'id' => 'ui_ia_flow',
      'number' => 10,
      'title' => 'Lekce 10 · Informační architektura + user flow',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout strukturu malého webu podle úkolů uživatele a převést ji do jednoznačného user flow.',
      'knowledge' => 
      array (
        0 => 'information-architecture-ii',
        1 => 'portfolio-case-study',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Obsah jako struktura',
          'text' => 'Sepiš obsah do skupin podle uživatelského cíle.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Sitemap',
          'text' => 'Navrhni 5–8 stránek/sekcí.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'User flow',
          'text' => 'Nakresli cestu od vstupu k cíli.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Navigace',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Tree test nanečisto',
          'text' => 'Dej spolužákovi 3 úkoly bez ukázání designu.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Iterace',
          'text' => 'Uprav jednu část IA podle testu.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'ia',
          'time' => '15 min',
          'title' => '01 · Obsah jako struktura',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'information-architecture-ii',
          ),
          'tasks' => 
          array (
            0 => 'Sepiš obsah do skupin podle uživatelského cíle.',
            1 => 'Pojmenuj skupiny běžným jazykem.',
          ),
        ),
        1 => 
        array (
          'id' => 'sitemap',
          'time' => '15 min',
          'title' => '02 · Sitemap',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni 5–8 stránek/sekcí.',
            1 => 'Omez zbytečné úrovně navigace.',
          ),
        ),
        2 => 
        array (
          'id' => 'flow',
          'time' => '15 min',
          'title' => '03 · User flow',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nakresli cestu od vstupu k cíli.',
            1 => 'Přidej rozhodovací bod jen když je skutečně potřeba.',
          ),
        ),
        3 => 
        array (
          'id' => 'nav',
          'time' => '12 min',
          'title' => '04 · Navigace',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je dobrý název položky navigace?',
          'options' => 
          array (
            0 => 'Takový, u kterého uživatel rozumně předvídá obsah po kliknutí.',
            1 => 'Interní název databázové tabulky.',
            2 => 'Nejasný kreativní termín bez kontextu.',
          ),
          'correct' => 0,
          'explanation' => 'Navigace má snižovat nejistotu.',
        ),
        4 => 
        array (
          'id' => 'test',
          'time' => '15 min',
          'title' => '05 · Tree test nanečisto',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Dej spolužákovi 3 úkoly bez ukázání designu.',
            1 => 'Zapiš, kde váhal.',
          ),
        ),
        5 => 
        array (
          'id' => 'iterate',
          'time' => '15 min',
          'title' => '06 · Iterace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Uprav jednu část IA podle testu.',
            1 => 'Zapiš před/po a důvod.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Content inventory.',
        1 => 'Sitemap.',
        2 => '1 user flow.',
        3 => '3 testovací úkoly.',
        4 => '1 iterace podle pozorování.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Testujte strukturu bez vizuálu, aby vzhled nemaskoval problém IA.',
        1 => 'Nepředepisuj jedinou správnou sitemapu.',
      ),
    ),
    8 => 
    array (
      'id' => 'ui_forms_states',
      'number' => 11,
      'title' => 'Lekce 11 · Form UX: validace, chyby a stavy',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.',
      'knowledge' => 
      array (
        0 => 'form-states-ii',
        1 => 'interaction-patterns-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'State inventory',
          'text' => 'Sepiš default/focus/filled/error/disabled/loading/success.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Validace',
          'text' => 'Rozliš preventivní nápovědu a error po odeslání.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Loading a double submit',
          'text' => 'Navrhni průběh po kliknutí Submit.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Recovery',
          'text' => 'Po chybě zachovej správně vyplněná data.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Chybová zpráva',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Prototype',
          'text' => 'Propoj success i error větev.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'states',
          'time' => '15 min',
          'title' => '01 · State inventory',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'form-states-ii',
          ),
          'tasks' => 
          array (
            0 => 'Sepiš default/focus/filled/error/disabled/loading/success.',
            1 => 'Urči, které stavy patří fieldu a které celému formuláři.',
          ),
        ),
        1 => 
        array (
          'id' => 'validation',
          'time' => '15 min',
          'title' => '02 · Validace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš preventivní nápovědu a error po odeslání.',
            1 => 'Error formuluj konkrétně.',
          ),
        ),
        2 => 
        array (
          'id' => 'loading',
          'time' => '15 min',
          'title' => '03 · Loading a double submit',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni průběh po kliknutí Submit.',
            1 => 'Zabraň nejasnému opakovanému odeslání.',
          ),
        ),
        3 => 
        array (
          'id' => 'recovery',
          'time' => '15 min',
          'title' => '04 · Recovery',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Po chybě zachovej správně vyplněná data.',
            1 => 'Přesuň pozornost k problému.',
          ),
        ),
        4 => 
        array (
          'id' => 'error',
          'time' => '12 min',
          'title' => '05 · Chybová zpráva',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Která zpráva pomáhá nejvíc?',
          'options' => 
          array (
            0 => '„Heslo musí mít alespoň 10 znaků.“',
            1 => '„Error.“',
            2 => '„Něco je špatně.“',
          ),
          'correct' => 0,
          'explanation' => 'Konkrétní chyba umožní uživateli situaci opravit.',
        ),
        5 => 
        array (
          'id' => 'prototype',
          'time' => '15 min',
          'title' => '06 · Prototype',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Propoj success i error větev.',
            1 => 'Otestuj klávesnicí základní průchod.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'State matrix formuláře.',
        1 => 'Error texty.',
        2 => 'Success stav.',
        3 => 'Klikací prototype nebo sekvence screenshotů.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Hodnoť zotavení po chybě, ne jen krásný default.',
        1 => 'Připomeň, že disabled bez vysvětlení může být problém.',
      ),
    ),
    9 => 
    array (
      'id' => 'ui_autolayout_variants',
      'number' => 12,
      'title' => 'Lekce 12 · Auto Layout, varianty a responsive komponenty',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Postavit responzivní komponentu s variantami a minimem lokálních override.',
      'knowledge' => 
      array (
        0 => 'auto-layout-ii',
        1 => 'design-tokens-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Auto Layout mental model',
          'text' => 'Rozliš hug/fill/fixed.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Responsive button',
          'text' => 'Text změň na delší variantu.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Card variants',
          'text' => 'Vytvoř compact/default variantu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Nested layout',
          'text' => 'Sestav kartu z menších komponent.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Override',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Stress test',
          'text' => 'Použij dlouhý text, malý viewport a chybějící obrázek.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'auto',
          'time' => '15 min',
          'title' => '01 · Auto Layout mental model',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'auto-layout-ii',
          ),
          'tasks' => 
          array (
            0 => 'Rozliš hug/fill/fixed.',
            1 => 'Pochop padding vs gap.',
          ),
        ),
        1 => 
        array (
          'id' => 'button',
          'time' => '15 min',
          'title' => '02 · Responsive button',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Text změň na delší variantu.',
            1 => 'Komponenta se nesmí rozpadnout.',
          ),
        ),
        2 => 
        array (
          'id' => 'card',
          'time' => '15 min',
          'title' => '03 · Card variants',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Vytvoř compact/default variantu.',
            1 => 'Napoj stejné tokeny.',
          ),
        ),
        3 => 
        array (
          'id' => 'nested',
          'time' => '15 min',
          'title' => '04 · Nested layout',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sestav kartu z menších komponent.',
            1 => 'Omez absolutní pozicování.',
          ),
        ),
        4 => 
        array (
          'id' => 'override',
          'time' => '12 min',
          'title' => '05 · Override',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Kdy je lokální override varovný signál?',
          'options' => 
          array (
            0 => 'Když stejnou výjimku opakuješ na mnoha instancích a měla by být systémovým pravidlem.',
            1 => 'Když změníš text instance.',
            2 => 'Když komponentu pojmenuješ.',
          ),
          'correct' => 0,
          'explanation' => 'Opakovaná výjimka často znamená chybějící variantu nebo token.',
        ),
        5 => 
        array (
          'id' => 'stress',
          'time' => '15 min',
          'title' => '06 · Stress test',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Použij dlouhý text, malý viewport a chybějící obrázek.',
            1 => 'Zapiš 2 opravy.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Komponenta Button + Card.',
        1 => 'Varianty.',
        2 => 'Stress test se 3 extrémními vstupy.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Neomezuj výuku na Figma mechaniku; student má vysvětlit pravidlo layoutu.',
        1 => 'Vyžaduj názvy variant podle významu.',
      ),
    ),
    10 => 
    array (
      'id' => 'ui_accessibility_audit',
      'number' => 13,
      'title' => 'Lekce 13 · Accessibility audit webového návrhu',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Provést strukturovaný audit kontrastu, focusu, pořadí, targetů, textů a stavů a opravit prioritní problémy.',
      'knowledge' => 
      array (
        0 => 'accessibility-audit-ii',
        1 => 'accessibility-design',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Audit checklist',
          'text' => 'Zkontroluj kontrast, focus, texty, stavy a velikost ovládacích prvků.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Keyboard flow',
          'text' => 'Seřaď očekávané pořadí focusu.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Zoom a reflow',
          'text' => 'Ověř, co se stane při větším textu/užším viewportu.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Form errors',
          'text' => 'Ověř labely a chybové stavy.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Accessibility',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Prioritized fixes',
          'text' => 'Seřaď nálezy High/Medium/Low.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'audit',
          'time' => '15 min',
          'title' => '01 · Audit checklist',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'accessibility-audit-ii',
          ),
          'tasks' => 
          array (
            0 => 'Zkontroluj kontrast, focus, texty, stavy a velikost ovládacích prvků.',
            1 => 'Nespoléhej jen na barvu.',
          ),
        ),
        1 => 
        array (
          'id' => 'keyboard',
          'time' => '15 min',
          'title' => '02 · Keyboard flow',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď očekávané pořadí focusu.',
            1 => 'Navrhni focus state, který není překrytý.',
          ),
        ),
        2 => 
        array (
          'id' => 'zoom',
          'time' => '15 min',
          'title' => '03 · Zoom a reflow',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř, co se stane při větším textu/užším viewportu.',
            1 => 'Najdi clipping nebo horizontální overflow.',
          ),
        ),
        3 => 
        array (
          'id' => 'errors',
          'time' => '15 min',
          'title' => '04 · Form errors',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ověř labely a chybové stavy.',
            1 => 'Každá chyba má textovou nápravu.',
          ),
        ),
        4 => 
        array (
          'id' => 'a11y',
          'time' => '12 min',
          'title' => '05 · Accessibility',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je správný přístup?',
          'options' => 
          array (
            0 => 'Accessibility řešit od návrhu komponent a flow, ne až jako poslední kontrolu.',
            1 => 'Přidat accessibility po exportu.',
            2 => 'Stačí zvýšit saturaci barev.',
          ),
          'correct' => 0,
          'explanation' => 'Přístupnost je součást funkční kvality produktu.',
        ),
        5 => 
        array (
          'id' => 'fix',
          'time' => '15 min',
          'title' => '06 · Prioritized fixes',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Seřaď nálezy High/Medium/Low.',
            1 => 'Oprav 2 největší bariéry a dokumentuj změnu.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Audit 8 bodů.',
        1 => 'Prioritizace nálezů.',
        2 => 'Before/after 2 oprav.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Učitel nemusí známkovat studenty za zapamatování čísel; důležitější je systematický audit a náprava.',
        1 => 'Používej reálný prototyp studentů.',
      ),
    ),
    11 => 
    array (
      'id' => 'ui_handoff_html_css',
      'number' => 14,
      'title' => 'Lekce 14 · Design → HTML/CSS: handoff, který jde implementovat',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Převést designové rozhodnutí do implementační specifikace: struktura, tokeny, komponenty a responsive pravidla.',
      'knowledge' => 
      array (
        0 => 'html-css-handoff-ii',
        1 => 'design-tokens-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Design vs DOM struktura',
          'text' => 'Rozděl obrazovku na header/main/sections/footer.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Token handoff',
          'text' => 'Zapiš color/spacing/type tokeny.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Responsive rules',
          'text' => 'Popiš, kdy se layout skládá pod sebe.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'States specification',
          'text' => 'Sepiš hover/focus/loading/error/disabled.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Handoff',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Developer review',
          'text' => 'Nech spolužáka podle handoffu popsat implementaci.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'structure',
          'time' => '15 min',
          'title' => '01 · Design vs DOM struktura',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'html-css-handoff-ii',
          ),
          'tasks' => 
          array (
            0 => 'Rozděl obrazovku na header/main/sections/footer.',
            1 => 'Pojmenuj komponenty podle funkce.',
          ),
        ),
        1 => 
        array (
          'id' => 'tokens',
          'time' => '15 min',
          'title' => '02 · Token handoff',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zapiš color/spacing/type tokeny.',
            1 => 'Vyhni se seznamu 40 náhodných pixel hodnot.',
          ),
        ),
        2 => 
        array (
          'id' => 'responsive',
          'time' => '15 min',
          'title' => '03 · Responsive rules',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Popiš, kdy se layout skládá pod sebe.',
            1 => 'Uveď max-width a chování obrázku.',
          ),
        ),
        3 => 
        array (
          'id' => 'states',
          'time' => '15 min',
          'title' => '04 · States specification',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sepiš hover/focus/loading/error/disabled.',
            1 => 'Doplň keyboard poznámky.',
          ),
        ),
        4 => 
        array (
          'id' => 'handoff',
          'time' => '12 min',
          'title' => '05 · Handoff',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je slabý handoff?',
          'options' => 
          array (
            0 => 'Jeden screenshot bez stavů, rozměrových pravidel a chování.',
            1 => 'Tokeny + komponenty + state matrix.',
            2 => 'Responsive anotace.',
          ),
          'correct' => 0,
          'explanation' => 'Implementace potřebuje pravidla, ne pouze vzhled jednoho viewportu.',
        ),
        5 => 
        array (
          'id' => 'devreview',
          'time' => '15 min',
          'title' => '06 · Developer review',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nech spolužáka podle handoffu popsat implementaci.',
            1 => 'Oprav 2 nejasnosti.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => '1 stránka handoff specifikace.',
        1 => 'Tokeny.',
        2 => 'Responsive pravidla.',
        3 => 'State matrix.',
        4 => '2 opravy po review.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Nemusí se psát plný kód. Cílem je spojit design a implementační myšlení.',
        1 => 'U technicky silnějších studentů lze přidat jednoduchou HTML/CSS realizaci.',
      ),
    ),
    12 => 
    array (
      'id' => 'ui_css_layout',
      'number' => 15,
      'title' => 'Lekce 15 · CSS layout mindset: Flex, Grid a responsive pravidla',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Pochopit, jak se návrhové vztahy mapují do Flexbox/Grid modelu, a navrhovat proveditelné responsive rozložení.',
      'knowledge' => 
      array (
        0 => 'css-responsive-ii',
        1 => 'html-css-handoff-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Flex vs Grid',
          'text' => 'Flex použij pro jednorozměrné uspořádání.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Hero jako layout rule',
          'text' => 'Popiš desktop dvě kolony.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Card grid',
          'text' => 'Navrhni grid, který se přirozeně mění 3→2→1.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Responsive',
          'text' => '',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'DevTools/inspection mindset',
          'text' => 'U existující stránky identifikuj container, gap, max-width a layout směr.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Implementation notes',
          'text' => 'Ke 3 komponentám napiš layout model a breakpoint behavior.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'flexgrid',
          'time' => '15 min',
          'title' => '01 · Flex vs Grid',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'css-responsive-ii',
          ),
          'tasks' => 
          array (
            0 => 'Flex použij pro jednorozměrné uspořádání.',
            1 => 'Grid pro dvourozměrné vztahy a oblasti.',
          ),
        ),
        1 => 
        array (
          'id' => 'hero',
          'time' => '15 min',
          'title' => '02 · Hero jako layout rule',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Popiš desktop dvě kolony.',
            1 => 'Na mobilu změň flow na jeden sloupec.',
          ),
        ),
        2 => 
        array (
          'id' => 'cards',
          'time' => '15 min',
          'title' => '03 · Card grid',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Navrhni grid, který se přirozeně mění 3→2→1.',
            1 => 'Nedrž pevnou šířku, která způsobí overflow.',
          ),
        ),
        3 => 
        array (
          'id' => 'breakpoint',
          'time' => '12 min',
          'title' => '04 · Responsive',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co je lepší než navrhovat pouze „desktop a mobil“?',
          'options' => 
          array (
            0 => 'Definovat chování komponent mezi šířkami a testovat, kdy se obsah začne lámat.',
            1 => 'Ignorovat tablet.',
            2 => 'Použít obrázek celé stránky.',
          ),
          'correct' => 0,
          'explanation' => 'Responsive je kontinuální chování, ne dvě fotografie.',
        ),
        4 => 
        array (
          'id' => 'inspect',
          'time' => '15 min',
          'title' => '05 · DevTools/inspection mindset',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'U existující stránky identifikuj container, gap, max-width a layout směr.',
            1 => 'Neměň produkční web.',
          ),
        ),
        5 => 
        array (
          'id' => 'spec',
          'time' => '15 min',
          'title' => '06 · Implementation notes',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ke 3 komponentám napiš layout model a breakpoint behavior.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Flex/Grid rozhodnutí pro 3 části stránky.',
        1 => 'Breakpoint behavior.',
        2 => 'Card grid 3→2→1.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Výklad může být bez rozsáhlého programování; důležitý je převod vztahů do layout modelu.',
        1 => 'Pokud umí HTML/CSS, dovol mini implementaci.',
      ),
    ),
    13 => 
    array (
      'id' => 'ui_usability_test',
      'number' => 16,
      'title' => 'Lekce 16 · Usability test: pozorování místo dojmologie',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Připravit krátký usability test, pozorovat chování bez navádění a prioritizovat zjištění podle dopadu.',
      'knowledge' => 
      array (
        0 => 'usability-test-ii',
        1 => 'design-critique-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Test plan',
          'text' => 'Definuj 3 realistické úkoly.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Moderace',
          'text' => 'Nedoplňuj nápovědu příliš brzy.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Evidence notes',
          'text' => 'Rozliš „kliknul třikrát zpět“ od „navigace je špatná“.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Prioritizace',
          'text' => 'Ohodnoť četnost × dopad.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Navádění',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Redesign',
          'text' => 'Proveď 1–3 změny.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'plan',
          'time' => '15 min',
          'title' => '01 · Test plan',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'usability-test-ii',
          ),
          'tasks' => 
          array (
            0 => 'Definuj 3 realistické úkoly.',
            1 => 'Nepopisuj přesnou cestu k cíli.',
          ),
        ),
        1 => 
        array (
          'id' => 'observe',
          'time' => '15 min',
          'title' => '02 · Moderace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Nedoplňuj nápovědu příliš brzy.',
            1 => 'Zapisuj pozorování, ne interpretaci.',
          ),
        ),
        2 => 
        array (
          'id' => 'notes',
          'time' => '15 min',
          'title' => '03 · Evidence notes',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Rozliš „kliknul třikrát zpět“ od „navigace je špatná“.',
          ),
        ),
        3 => 
        array (
          'id' => 'severity',
          'time' => '15 min',
          'title' => '04 · Prioritizace',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Ohodnoť četnost × dopad.',
            1 => 'Vyber maximálně 3 problémy k redesignu.',
          ),
        ),
        4 => 
        array (
          'id' => 'leading',
          'time' => '12 min',
          'title' => '05 · Navádění',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Která věta je při testu nejlepší?',
          'options' => 
          array (
            0 => '„Co byste teď udělal/a?“',
            1 => '„Klikněte na modré tlačítko vpravo.“',
            2 => '„Vidíte, že menu je tady?“',
          ),
          'correct' => 0,
          'explanation' => 'Moderátor má zjišťovat mentální model, ne učit řešení.',
        ),
        5 => 
        array (
          'id' => 'iterate',
          'time' => '15 min',
          'title' => '06 · Redesign',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Proveď 1–3 změny.',
            1 => 'U každé napiš problém → evidence → změna.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Test plan 3 úkolů.',
        1 => 'Pozorovací záznam.',
        2 => 'Seznam problémů + severity.',
        3 => 'Before/after redesign.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Stačí 1–3 testující ve třídě pro nácvik metody; nejde o reprezentativní výzkum.',
        1 => 'Vyžaduj oddělení pozorování od interpretace.',
      ),
    ),
    14 => 
    array (
      'id' => 'ui_design_qa',
      'number' => 17,
      'title' => 'Lekce 17 · Design QA: porovnej implementaci s pravidly, ne s pixely',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Provést design QA realizace a rozlišit funkční odchylku, accessibility problém a přijatelnou implementační variaci.',
      'knowledge' => 
      array (
        0 => 'design-qa-handoff-ii',
        1 => 'accessibility-audit-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Design QA mindset',
          'text' => 'Kontroluj hierarchii, spacing, komponenty, states a responsive behavior.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'Compare',
          'text' => 'Porovnej design a implementaci ve 3 viewports.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'Severity',
          'text' => 'Critical = brání úkolu; High = zásadní funkční/a11y problém; Low = kosmetika.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'QA issue',
          'text' => 'Napiš steps, expected, actual a screenshot/reference.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Priorita',
          'text' => '',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Verify fix',
          'text' => 'Po opravě zopakuj původní scénář.',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'qa',
          'time' => '15 min',
          'title' => '01 · Design QA mindset',
          'kind' => 'knowledge',
          'xp' => 30,
          'knowledge' => 
          array (
            0 => 'design-qa-handoff-ii',
          ),
          'tasks' => 
          array (
            0 => 'Kontroluj hierarchii, spacing, komponenty, states a responsive behavior.',
            1 => 'Neřeš 1px rozdíl před rozbitou funkcí.',
          ),
        ),
        1 => 
        array (
          'id' => 'compare',
          'time' => '15 min',
          'title' => '02 · Compare',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Porovnej design a implementaci ve 3 viewports.',
            1 => 'Sepiš pouze ověřitelné rozdíly.',
          ),
        ),
        2 => 
        array (
          'id' => 'severity',
          'time' => '15 min',
          'title' => '03 · Severity',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Critical = brání úkolu; High = zásadní funkční/a11y problém; Low = kosmetika.',
          ),
        ),
        3 => 
        array (
          'id' => 'issue',
          'time' => '15 min',
          'title' => '04 · QA issue',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Napiš steps, expected, actual a screenshot/reference.',
            1 => 'Přiřaď severity.',
          ),
        ),
        4 => 
        array (
          'id' => 'priority',
          'time' => '12 min',
          'title' => '05 · Priorita',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co opravovat první?',
          'options' => 
          array (
            0 => 'Problém, který blokuje úkol nebo zásadně porušuje použitelnost/přístupnost.',
            1 => 'Nejmenší rozdíl stínu.',
            2 => 'Pořadí názvů vrstev ve Figmě.',
          ),
          'correct' => 0,
          'explanation' => 'QA řadí podle dopadu.',
        ),
        5 => 
        array (
          'id' => 'verify',
          'time' => '15 min',
          'title' => '06 · Verify fix',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Po opravě zopakuj původní scénář.',
            1 => 'Issue uzavři až po ověření.',
          ),
        ),
      ),
      'worksheet' => 
      array (
        0 => '3 viewport compare.',
        1 => '5 QA issues max.',
        2 => 'Severity + expected/actual.',
        3 => 'Verification poznámka.',
      ),
      'teacher_notes' => 
      array (
        0 => 'QA učí spolupráci Designer–Developer.',
        1 => 'Nepodporuj blame culture; issue popisuje stav produktu.',
      ),
    ),
    15 => 
    array (
      'id' => 'ui_product_capstone',
      'number' => 18,
      'title' => 'Lekce 18 · Capstone: produktová landing page + case study',
      'subtitle' => '2 × 45 minut',
      'goal' => 'Spojit IA, responsive UI, komponenty, accessibility, testování a handoff do jednoho obhajitelného produktového návrhu.',
      'knowledge' => 
      array (
        0 => 'information-architecture-ii',
        1 => 'auto-layout-ii',
        2 => 'accessibility-audit-ii',
        3 => 'usability-test-ii',
        4 => 'design-qa-handoff-ii',
      ),
      'schedule' => 
      array (
        0 => 
        array (
          'time' => '0–15',
          'title' => 'Product brief',
          'text' => 'Definuj uživatele, problém, cíl stránky a success signal.',
        ),
        1 => 
        array (
          'time' => '15–30',
          'title' => 'IA + wireframe',
          'text' => 'Sitemap/sekvence.',
        ),
        2 => 
        array (
          'time' => '30–45',
          'title' => 'UI system',
          'text' => 'Tokeny, komponenty a stavy.',
        ),
        3 => 
        array (
          'time' => '45–60',
          'title' => 'Usability + accessibility',
          'text' => 'Proveď krátký test.',
        ),
        4 => 
        array (
          'time' => '60–75',
          'title' => 'Iterace + handoff',
          'text' => 'Zapracuj 2–4 podložené změny.',
        ),
        5 => 
        array (
          'time' => '75–90',
          'title' => 'Case study',
          'text' => '',
        ),
      ),
      'steps' => 
      array (
        0 => 
        array (
          'id' => 'brief',
          'time' => '15 min',
          'title' => '01 · Product brief',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Definuj uživatele, problém, cíl stránky a success signal.',
          ),
        ),
        1 => 
        array (
          'id' => 'flow',
          'time' => '15 min',
          'title' => '02 · IA + wireframe',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Sitemap/sekvence.',
            1 => 'Mobile + desktop wireframe.',
          ),
        ),
        2 => 
        array (
          'id' => 'system',
          'time' => '15 min',
          'title' => '03 · UI system',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Tokeny, komponenty a stavy.',
            1 => 'Responzivní pravidla.',
          ),
        ),
        3 => 
        array (
          'id' => 'test',
          'time' => '15 min',
          'title' => '04 · Usability + accessibility',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Proveď krátký test.',
            1 => 'Proveď accessibility audit.',
            2 => 'Prioritizuj nálezy.',
          ),
        ),
        4 => 
        array (
          'id' => 'iterate',
          'time' => '15 min',
          'title' => '05 · Iterace + handoff',
          'kind' => 'manual',
          'xp' => 25,
          'tasks' => 
          array (
            0 => 'Zapracuj 2–4 podložené změny.',
            1 => 'Připrav implementační handoff.',
          ),
        ),
        5 => 
        array (
          'id' => 'case',
          'time' => '12 min',
          'title' => '06 · Case study',
          'kind' => 'quiz',
          'xp' => 25,
          'question' => 'Co má kvalitní case study ukázat?',
          'options' => 
          array (
            0 => 'Problém, proces, evidence, rozhodnutí, výsledek a reflexi.',
            1 => 'Jen galerii finálních screenshotů.',
            2 => 'Pouze použitý software.',
          ),
          'correct' => 0,
          'explanation' => 'Case study dokazuje způsob uvažování.',
        ),
      ),
      'worksheet' => 
      array (
        0 => 'Brief.',
        1 => 'IA/user flow.',
        2 => 'Wireframes.',
        3 => 'Design system mini-spec.',
        4 => 'Responsive screens.',
        5 => 'Test evidence.',
        6 => 'Accessibility audit.',
        7 => 'Handoff.',
        8 => 'Case study.',
      ),
      'teacher_notes' => 
      array (
        0 => 'Vhodné jako závěrečný projekt 2.A.',
        1 => 'U týmové varianty použij Project Workspace role Designer/Researcher/Developer/QA/Presenter.',
      ),
    ),
    16 => 
    array (
      'id' => 'v30_2a_19',
      'number' => 19,
      'title' => 'Lekce 19 · Research framing',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Převést neurčitý produktový problém do výzkumné otázky a jednoduchého testovacího plánu.',
      'knowledge' => 
      array (
        0 => 'information-architecture-ii',
        1 => 'usability-test-ii',
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
            0 => 'information-architecture-ii',
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
            0 => 'information-architecture-ii',
            1 => 'usability-test-ii',
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
      'id' => 'v30_2a_20',
      'number' => 20,
      'title' => 'Lekce 20 · Card sorting lab',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Ověřit seskupení obsahu a navrhnout informační architekturu podle mentálních modelů uživatelů.',
      'knowledge' => 
      array (
        0 => 'card-sorting',
        1 => 'information-architecture-ii',
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
            0 => 'card-sorting',
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
            0 => 'card-sorting',
            1 => 'information-architecture-ii',
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
      'id' => 'v30_2a_21',
      'number' => 21,
      'title' => 'Lekce 21 · Responsive component stress test',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Otestovat komponentu na dlouhý obsah, malé viewporty a různé stavy.',
      'knowledge' => 
      array (
        0 => 'auto-layout-ii',
        1 => 'css-responsive-ii',
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
            0 => 'auto-layout-ii',
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
            0 => 'auto-layout-ii',
            1 => 'css-responsive-ii',
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
      'id' => 'v30_2a_22',
      'number' => 22,
      'title' => 'Lekce 22 · Form recovery states',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Navrhnout validaci, chyby a recovery tak, aby uživatel nepřišel o práci.',
      'knowledge' => 
      array (
        0 => 'error-recovery-ux',
        1 => 'form-states-ii',
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
            0 => 'error-recovery-ux',
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
            0 => 'error-recovery-ux',
            1 => 'form-states-ii',
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
      'id' => 'v30_2a_23',
      'number' => 23,
      'title' => 'Lekce 23 · Accessible interaction',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Ověřit focus, klávesnici, target size a alternativu k drag/hover gestům.',
      'knowledge' => 
      array (
        0 => 'interaction-accessibility',
        1 => 'accessibility-audit-ii',
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
            0 => 'interaction-accessibility',
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
            0 => 'interaction-accessibility',
            1 => 'accessibility-audit-ii',
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
      'id' => 'v30_2a_24',
      'number' => 24,
      'title' => 'Lekce 24 · Design tokens to code',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Připravit tokeny a komponentovou specifikaci použitelnou při implementaci.',
      'knowledge' => 
      array (
        0 => 'design-tokens-ii',
        1 => 'design-handoff-qa',
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
            0 => 'design-tokens-ii',
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
            0 => 'design-tokens-ii',
            1 => 'design-handoff-qa',
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
      'id' => 'v30_2a_25',
      'number' => 25,
      'title' => 'Lekce 25 · Usability round 2',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Provést krátký test bez navádění, zaznamenat evidence a upravit návrh.',
      'knowledge' => 
      array (
        0 => 'usability-test-ii',
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
            0 => 'usability-test-ii',
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
            0 => 'usability-test-ii',
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
      'id' => 'v30_2a_26',
      'number' => 26,
      'title' => 'Lekce 26 · Design QA clinic',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Převést nalezený nesoulad na reprodukovatelné issue a ověřit opravu.',
      'knowledge' => 
      array (
        0 => 'design-handoff-qa',
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
            0 => 'design-handoff-qa',
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
            0 => 'design-handoff-qa',
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
      'id' => 'v30_2a_27',
      'number' => 27,
      'title' => 'Lekce 27 · Product sprint',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Propojit IA, komponenty, responsive pravidla, accessibility a testování v týmovém capstone.',
      'knowledge' => 
      array (
        0 => 'card-sorting',
        1 => 'interaction-accessibility',
        2 => 'design-handoff-qa',
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
            0 => 'card-sorting',
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
            0 => 'card-sorting',
            1 => 'interaction-accessibility',
            2 => 'design-handoff-qa',
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
      'id' => 'v30_2a_28',
      'number' => 28,
      'title' => 'Lekce 28 · Case study & mastery',
      'subtitle' => '2 × 45 minut · více cest k pochopení',
      'goal' => 'Vytvořit stručnou case study se skutečným problémem, iterací, důkazem a výsledkem.',
      'knowledge' => 
      array (
        0 => 'design-handoff-qa',
        1 => 'portfolio-case-study',
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
            0 => 'design-handoff-qa',
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
            0 => 'design-handoff-qa',
            1 => 'portfolio-case-study',
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
    'ui-spacing-system' => 
    array (
      'id' => 'ui-spacing',
      'type' => 'spacingtokens',
      'title' => 'Spacing Lab: tokeny místo chaosu',
      'lead' => 'Měň počet tokenů a základní krok. Sleduj konzistenci karet a sekcí.',
      'task' => 'Vytvoř malý spacing systém s 4–6 hodnotami a použitelným rytmem.',
      'prediction' => 
      array (
        'q' => 'Co je lepší?',
        'options' => 
        array (
          0 => 'Omezená sada spacing hodnot.',
          1 => 'Náhodná hodnota pro každý gap.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni 4 nebo 8 px.',
        1 => 'Použij násobky.',
        2 => 'Drž 4–6 hodnot.',
      ),
      'conclusion' => 
      array (
        'q' => 'Proč tokeny?',
        'options' => 
        array (
          0 => 'Konzistence a rychlost rozhodování.',
          1 => 'Aby bylo více čísel.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'component-consistency' => 
    array (
      'id' => 'ui-components',
      'type' => 'components',
      'title' => 'Component Lab: stejné role, stejná pravidla',
      'lead' => 'Měň radius, padding a states tří tlačítek. Cílem je sjednotit systém, ne všechny prvky udělat identické.',
      'task' => 'Sjednoť primary, secondary a disabled variantu pod jednu komponentu.',
      'prediction' => 
      array (
        'q' => 'Má disabled button vypadat stejně jako primary?',
        'options' => 
        array (
          0 => 'Ne, stav musí být rozpoznatelný.',
          1 => 'Ano vždy.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sjednoť anatomy.',
        1 => 'Rozliš variantu přes barvu/kontrast.',
        2 => 'Drž stejné paddingy/radius.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je komponenta?',
        'options' => 
        array (
          0 => 'Opakovatelné pravidlo a jeho varianty.',
          1 => 'Náhodný blok.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'microinteraction-storyboard' => 
    array (
      'id' => 'ui-motion',
      'type' => 'motioncurve',
      'title' => 'Motion Lab: feedback v čase',
      'lead' => 'Měň délku a easing krátké mikrointerakce. Sleduj, kdy je feedback rychlý a kdy už obtěžuje.',
      'task' => 'Nastav success feedback, který je rychlý, čitelný a funkční.',
      'prediction' => 
      array (
        'q' => 'Je 4s animace běžného kliknutí vhodná?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Mikrointerakce má být krátká.',
        1 => 'Zkus 180–300 ms.',
        2 => 'Použij ease-out.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co má pohyb dělat?',
        'options' => 
        array (
          0 => 'Vysvětlit změnu stavu.',
          1 => 'Jen přitahovat pozornost.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'portfolio-case-study' => 
    array (
      'id' => 'ui-portfolio',
      'type' => 'portfolioflow',
      'title' => 'Case Study Builder',
      'lead' => 'Přepínej bloky case study a sleduj, jestli příběh dává smysl bez dlouhého vysvětlování.',
      'task' => 'Sestav pořadí problém → proces → rozhodnutí → výsledek → reflexe.',
      'prediction' => 
      array (
        'q' => 'Co má být první?',
        'options' => 
        array (
          0 => 'Problém/cíl.',
          1 => 'Finální mockup bez kontextu.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni problémem.',
        1 => 'Proces bez rozhodnutí nestačí.',
        2 => 'Ukonči výsledkem a reflexí.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co portfolio dokazuje?',
        'options' => 
        array (
          0 => 'Schopnost řešit problém.',
          1 => 'Pouze znalost exportu.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'accessibility-design' => 
    array (
      'id' => 'ui-a11y',
      'type' => 'accessibilityaudit',
      'title' => 'Accessibility Audit Lab',
      'lead' => 'Měň barvy, velikost a focus state. Simulace počítá kontrast a ukazuje, zda je interakce čitelná i bez ideálních podmínek.',
      'task' => 'Nastav čitelný text, alespoň 16 px a viditelný focus ring.',
      'prediction' => 
      array (
        'q' => 'Může design vypadat dobře a přesto být špatně použitelný?',
        'options' => 
        array (
          0 => 'Ano.',
          1 => 'Ne.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Zvedni kontrast nad 4,5:1.',
        1 => 'Nastav text alespoň 16 px.',
        2 => 'Zapni focus ring.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co je přístupný interaktivní prvek?',
        'options' => 
        array (
          0 => 'Čitelný, srozumitelný a jasně ovladatelný.',
          1 => 'Pouze barevně výrazný.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'responsive-type-ii' => 
    array (
      'id' => 'gfx-type-ii',
      'type' => 'responsivepreview',
      'title' => 'Responsive Type Lab',
      'lead' => 'Přepínej viewport a sleduj, jak se musí měnit velikost, wrap a spacing typografických rolí.',
      'task' => 'Udrž jasnou hierarchii na desktopu i mobilu.',
      'prediction' => 
      array (
        'q' => 'Má se desktopová typografie jen zmenšit stejným poměrem?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Sleduj headline wrap.',
        1 => 'Body text nenech příliš malý.',
        2 => 'Uprav spacing mezi rolemi.',
      ),
      'conclusion' => 
      array (
        'q' => 'Co se zachovává?',
        'options' => 
        array (
          0 => 'Role a priorita.',
          1 => 'Přesný počet řádků.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'design-tokens-ii' => 
    array (
      'id' => 'gfx-tokens-ii',
      'type' => 'components',
      'title' => 'Semantic Token Lab',
      'lead' => 'Měň sémantické tokeny a sleduj dopad do více komponent bez lokálních override.',
      'task' => 'Sestav konzistentní Button/Card systém z pojmenovaných tokenů.',
      'prediction' => 
      array (
        'q' => 'Má komponenta používat náhodné lokální barvy?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Odděl primitive a semantic token.',
        1 => 'Napoj varianty.',
        2 => 'Změň jeden token a sleduj celý systém.',
      ),
      'conclusion' => 
      array (
        'q' => 'Hlavní výhoda tokenů?',
        'options' => 
        array (
          0 => 'Konzistence a snadná globální změna.',
          1 => 'Více duplicit.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'interaction-patterns-ii' => 
    array (
      'id' => 'gfx-interactions-ii',
      'type' => 'motioncurve',
      'title' => 'State & Motion Lab',
      'lead' => 'Sestav stavovou sekvenci default → loading → success/error a uprav motion tak, aby podporoval feedback.',
      'task' => 'Navrhni přechod, který je pochopitelný i bez animace.',
      'prediction' => 
      array (
        'q' => 'Má motion nahrazovat informaci?',
        'options' => 
        array (
          0 => 'Ne, má ji podporovat.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Začni stavovým modelem.',
        1 => 'Motion drž krátký.',
        2 => 'Ověř reduced motion.',
      ),
      'conclusion' => 
      array (
        'q' => 'Dobrá mikrointerakce?',
        'options' => 
        array (
          0 => 'Rychle potvrzuje stav a další krok.',
          1 => 'Pouze dekoruje.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'design-critique-ii' => 
    array (
      'id' => 'gfx-critique-ii',
      'type' => 'critique',
      'title' => 'Design Critique Lab',
      'lead' => 'Porovnej subjektivní komentář s kritikou postavenou na cíli, evidenci a konkrétním doporučení.',
      'task' => 'Přeformuluj kritiku do profesionálního design argumentu.',
      'prediction' => 
      array (
        'q' => 'Je „nelíbí se mi modrá“ dostatečná kritika?',
        'options' => 
        array (
          0 => 'Ne.',
          1 => 'Ano.',
        ),
        'correct' => 0,
      ),
      'hints' => 
      array (
        0 => 'Pojmenuj cíl.',
        1 => 'Přidej pozorování.',
        2 => 'Navrhni testovatelnou změnu.',
      ),
      'conclusion' => 
      array (
        'q' => 'Kvalitní kritika stojí na?',
        'options' => 
        array (
          0 => 'Cíli a evidenci.',
          1 => 'Osobním vkusu.',
        ),
        'correct' => 0,
      ),
      'points' => 5,
    ),
    'information-architecture-ii' => 
    array (
      'id' => 'yp-information-architecture-ii',
      'type' => 'portfolioflow',
      'title' => 'Informační architektura II · Lab',
      'lead' => 'IA organizuje obsah podle očekávání a cílů uživatele, ne podle interní struktury firmy.',
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
    'form-states-ii' => 
    array (
      'id' => 'yp-form-states-ii',
      'type' => 'motioncurve',
      'title' => 'Form UX a stavový model · Lab',
      'lead' => 'Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.',
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
    'auto-layout-ii' => 
    array (
      'id' => 'yp-auto-layout-ii',
      'type' => 'components',
      'title' => 'Auto Layout a varianty II · Lab',
      'lead' => 'Auto Layout vyjadřuje vztahy mezi obsahem a prostorem, takže komponenta lépe reaguje na změnu textu a viewportu.',
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
    'accessibility-audit-ii' => 
    array (
      'id' => 'yp-accessibility-audit-ii',
      'type' => 'accessibilityaudit',
      'title' => 'Accessibility audit II · Lab',
      'lead' => 'Audit hledá bariéry v kontrastu, ovládání, focusu, reflow, textech a stavových informacích.',
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
    'html-css-handoff-ii' => 
    array (
      'id' => 'yp-html-css-handoff-ii',
      'type' => 'components',
      'title' => 'Design → HTML/CSS handoff · Lab',
      'lead' => 'Handoff popisuje strukturu, pravidla a chování, které developer potřebuje k věrné a robustní implementaci.',
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
    'css-responsive-ii' => 
    array (
      'id' => 'yp-css-responsive-ii',
      'type' => 'responsivepreview',
      'title' => 'CSS layout mindset · Lab',
      'lead' => 'Flexbox a Grid jsou modely vztahů mezi prvky, které lze využít k implementaci responzivních pravidel.',
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
    'usability-test-ii' => 
    array (
      'id' => 'yp-usability-test-ii',
      'type' => 'critique',
      'title' => 'Usability test II · Lab',
      'lead' => 'Krátký usability test sleduje, zda uživatel dokáže splnit úkol bez navádění a kde vzniká nejistota.',
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
    'design-qa-handoff-ii' => 
    array (
      'id' => 'yp-design-qa-handoff-ii',
      'type' => 'critique',
      'title' => 'Design QA a handoff II · Lab',
      'lead' => 'Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.',
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
        'title' => 'Canva Design School · Courses',
        'meta' => 'Volitelné kurzy · layout, typografie a brand systémy',
        'url' => 'https://www.canva.com/design-school/courses/',
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
    'information-architecture-ii' => 
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
    'form-states-ii' => 
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
    'auto-layout-ii' => 
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
    'accessibility-audit-ii' => 
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
    'html-css-handoff-ii' => 
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
    'css-responsive-ii' => 
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
    'usability-test-ii' => 
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
    'design-qa-handoff-ii' => 
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
    'card-sorting' => 
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
    'error-recovery-ux' => 
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
    'interaction-accessibility' => 
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
    'design-handoff-qa' => 
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
  ),
);
