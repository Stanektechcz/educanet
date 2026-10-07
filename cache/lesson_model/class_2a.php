<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 1,
  'class' => 'class_2a',
  'lessons' => 
  array (
    1 => 
    array (
      'number' => 1,
      'lm71_source' => 'primary',
    ),
    2 => 
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
      'number' => 2,
      'lm71_source' => 'next',
    ),
    3 => 
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
      'lm71_source' => 'extended',
    ),
    4 => 
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
      'lm71_source' => 'extended',
    ),
    5 => 
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
      'lm71_source' => 'plus',
    ),
    6 => 
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
      'lm71_source' => 'plus',
    ),
    7 => 
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
      'lm71_source' => 'more',
    ),
    8 => 
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
      'lm71_source' => 'ecosystem',
    ),
    9 => 
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
      'lm71_source' => 'ecosystem',
    ),
    10 => 
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
      'lm71_source' => 'yearpack',
    ),
    11 => 
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
      'lm71_source' => 'yearpack',
    ),
    12 => 
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
      'lm71_source' => 'yearpack',
    ),
    13 => 
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
      'lm71_source' => 'yearpack',
    ),
    14 => 
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
      'lm71_source' => 'yearpack',
    ),
    15 => 
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
      'lm71_source' => 'yearpack',
    ),
    16 => 
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
      'lm71_source' => 'yearpack',
    ),
    17 => 
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
      'lm71_source' => 'yearpack',
    ),
    18 => 
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
      'lm71_source' => 'yearpack',
    ),
    19 => 
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
      'lm71_source' => 'v30',
    ),
    20 => 
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
      'lm71_source' => 'v30',
    ),
    21 => 
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
      'lm71_source' => 'v30',
    ),
    22 => 
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
      'lm71_source' => 'v30',
    ),
    23 => 
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
      'lm71_source' => 'v30',
    ),
    24 => 
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
      'lm71_source' => 'v30',
    ),
    25 => 
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
      'lm71_source' => 'v30',
    ),
    26 => 
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
      'lm71_source' => 'v30',
    ),
    27 => 
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
      'lm71_source' => 'v30',
    ),
    28 => 
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
      'lm71_source' => 'v30',
    ),
  ),
  'conflicts' => 
  array (
  ),
  'template_tasks' => 
  array (
    'fcfaee0c8cf0' => 10,
    '9840f242be1c' => 10,
    '29bcf19a752f' => 10,
    'ecdd13e619ae' => 10,
    '3da4ec25375b' => 10,
    'de9dbe43c2ef' => 10,
    'faa9348a9583' => 10,
    '2c621dfcc048' => 10,
    '6342205f34e7' => 10,
    'e16e397d010e' => 10,
  ),
  'overlay' => 
  array (
    'lessons' => 
    array (
    ),
    'days' => 
    array (
    ),
    'files' => 
    array (
    ),
  ),
  'sig' => 'ac09530107d415acebf614c00c70b5113c87b14e',
  'hash' => '283715346b3baf7ca6021ab840773f1a0cb7c4b7b862c1e248e99979b28273e3',
  'built_at' => '2026-10-07T22:52:57+02:00',
);
