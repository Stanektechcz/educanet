<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 2,
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
      5 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 5 · Design systém: mezery, komponenty, stavy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést vizuální cit na opakovatelný systém: škálu mezer, tři komponenty a jejich stavy v jednoduchém rozhraní.',
          'success_criteria' => 
          array (
            0 => 'Používám 4–5 hodnot mezer (spacing) místo náhodných čísel.',
            1 => 'Tlačítko, karta a štítek sdílí zaoblení, vnitřní okraj a textové role.',
            2 => 'Rozhraní funguje na šířce 1440 i 390 px.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže rozhraní, kde má každá karta jiné mezery.',
            'student' => 'Najdou pět různých mezer, které měly být stejné.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Škála mezer',
            'teacher' => 'Předvede simulaci mezer a výběr 4–5 hodnot.',
            'student' => 'Zvolí škálu a pojmenují ji (S, M, L…).',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 38,
            'phase' => 'Komponenty',
            'teacher' => 'Vysvětlí komponentu jako pravidlo.',
            'student' => 'Definují tlačítko, kartu a štítek se společnými pravidly.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 53,
            'phase' => 'Stavy',
            'teacher' => 'Ukáže výchozí, najetí a nedostupný stav.',
            'student' => 'Navrhnou stavy a ověří kontrast textu.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 53,
            'to' => 80,
            'phase' => 'Mini přehled',
            'teacher' => 'Hlídá, aby se používal jen vlastní systém.',
            'student' => 'Poskládají záhlaví, 3 karty a akci; otestují 1440 a 390 px.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Kontrola systému a exit ticket',
            'teacher' => 'Spustí hledání „výjimek“ mimo systém.',
            'student' => 'Soused najde hodnotu mimo škálu; exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zvol škálu 4–5 hodnot mezer a pojmenuj ji tak, aby ji použil i spolužák.',
            'output' => 'Tabulka škály mezer.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Definuj tlačítko, kartu a štítek se společným zaoblením, vnitřním okrajem a textovými rolemi.',
            'output' => 'Tři komponenty s pravidly.',
            'time' => '16 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni stavy výchozí, najetí (hover) a nedostupné a ověř kontrast textu v každém.',
            'output' => 'Řádek stavů s poznámkou o kontrastu.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Poskládej mini přehled (záhlaví, 3 karty, akce) jen ze svého systému a otestuj ho na 1440 a 390 px.',
            'output' => 'Dva náhledy rozhraní.',
            'time' => '27 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou škálu mezer a kostru přehledu; doplňuje komponenty a stavy.',
          'standard' => 'Vlastní škála, tři komponenty, stavy a přehled podle zadání.',
          'challenge' => 'Popíše systém na jedné stránce dokumentace tak, aby podle ní spolužák postavil čtvrtou kartu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Hon na výjimky: soused hledá mezeru nebo barvu mimo systém.',
            1 => 'Rychlá otázka: kde v systému změníš zaoblení všech karet najednou?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Škála mezer',
              'levels' => 
              array (
                0 => 'Náhodné mezery.',
                1 => 'Škála existuje, nedodržuje se.',
                2 => 'Všechny mezery ze škály.',
                3 => 'Škálu zdůvodní a zdokumentuje.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Komponenty a stavy',
              'levels' => 
              array (
                0 => 'Každý prvek jiný.',
                1 => 'Komponenty bez stavů.',
                2 => 'Tři komponenty se stavy a kontrastem.',
                3 => 'Stavy čitelné i bez barvy.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rozhraní na dvou šířkách',
              'levels' => 
              array (
                0 => 'Jedna šířka.',
                1 => 'Mobil se rozpadá.',
                2 => 'Funguje na 1440 i 390 px.',
                3 => 'Systém se na mobilu nemění, jen přeskládá.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Design systém',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč používáme omezenou škálu mezer?',
              'options' => 
              array (
                0 => 'Aby rozhraní působilo jednotně a změny šly dělat systémově.',
                1 => 'Protože jiné mezery prohlížeč nezobrazí.',
                2 => 'Aby bylo méně práce s exportem.',
              ),
              'correct' => 0,
              'explanation' => 'Škála = méně náhodných rozhodnutí.',
            ),
            1 => 
            array (
              'question' => 'Tři karty mají různé vnitřní okraje 13, 17 a 22 px. Co uděláš?',
              'options' => 
              array (
                0 => 'Nechám je, každá karta je jiná.',
                1 => 'Sjednotím je na jednu hodnotu ze škály.',
                2 => 'Okraje odstraním.',
              ),
              'correct' => 1,
              'explanation' => 'Komponenta má jedno pravidlo pro všechny výskyty.',
            ),
            2 => 
            array (
              'question' => 'Co musí platit pro nedostupné tlačítko?',
              'options' => 
              array (
                0 => 'Musí vypadat stejně jako aktivní.',
                1 => 'Stačí ho skrýt.',
                2 => 'Je rozpoznatelné a uživatel ví, proč akce teď nejde.',
              ),
              'correct' => 2,
              'explanation' => 'Nedostupnost bez vysvětlení mate.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v jedné aplikaci v telefonu najdi tři místa se stejnou mezerou a jedno, které systém porušuje.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Obsah rozhraní je vymyšlený; žádná skutečná data uživatelů.',
          1 => 'Ikony jen s licencí; zdroj zapiš do dokumentace.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „systém“ = paleta barev. Začni mezerami, jsou nejvíc vidět.',
          1 => 'Otázka do třídy: Kolik různých mezer najdete na své obrazovce?',
          2 => 'Tempo: přehled je hlavní výstup – škálu omez na 12 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem jsou dva náhledy rozhraní.',
          1 => 'Plán B offline: škála mezer pravítkem, komponenty jako papírové výstřižky.',
        ),
        'glossary' => 
        array (
          0 => 'spacing',
          1 => 'hover',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      6 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 6 · Případová studie do portfolia a mikrointerakce',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím sepsat krátkou případovou studii svého projektu (problém → rozhodnutí → výsledek) a navrhnout mikrointerakci s jasnou funkcí.',
          'success_criteria' => 
          array (
            0 => 'Studie má problém, dvě klíčová rozhodnutí a výsledek s ukázkou před/po.',
            1 => 'Mikrointerakce má spouštěč, zpětnou vazbu a délku.',
            2 => 'Stav je pochopitelný i bez animace.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže dvě portfolia: galerii obrázků a krátkou studii.',
            'student' => 'Řeknou, ze kterého víc poznají, jak autor přemýšlí.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Problém → rozhodnutí → výsledek',
            'teacher' => 'Předvede strukturu případové studie (case study).',
            'student' => 'Napíšou problém a vyberou dvě rozhodnutí.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Důkazy procesu',
            'teacher' => 'Ukáže, jak anotovat před/po a skicu.',
            'student' => 'Připraví před/po, jednu skicu a krátké popisky.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Proč animovat',
            'teacher' => 'Vysvětlí mikrointerakci: spouštěč → zpětná vazba.',
            'student' => 'Vyberou jednu akci (najetí, odeslání, načítání).',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Storyboard a prototyp',
            'teacher' => 'Hlídá, aby pohyb nesl informaci.',
            'student' => 'Nakreslí 4 snímky a vytvoří jednoduchý prototyp.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Obhajoba a exit ticket',
            'teacher' => 'Řídí 30sekundové obhajoby ve dvojicích.',
            'student' => 'Obhájí studii za 30 s a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš problém svého projektu jednou větou a vyber dvě rozhodnutí, která ho řešila.',
            'output' => 'Problém + 2 rozhodnutí.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Připrav ukázku před/po, jednu skicu a ke každé krátkou anotaci.',
            'output' => 'Tři anotované obrázky.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni mikrointerakci (microinteraction): urči spouštěč, zpětnou vazbu a délku, a nakresli ji ve 4 snímcích.',
            'output' => 'Storyboard 4 snímků.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Vytvoř jednoduchý prototyp nebo GIF a ověř, že stav pozná i ten, kdo animaci nevidí.',
            'output' => 'Prototyp + statický náhled koncového stavu.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu studie s pěti bloky a vybírá mikrointerakci ze tří připravených.',
          'standard' => 'Vlastní studie, storyboard a prototyp podle zadání.',
          'challenge' => 'Přidá do studie jedno měřitelné zjištění (např. kolik spolužáků našlo akci napoprvé).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po prvním bloku: soused přečte problém – rozumí mu bez dalšího vysvětlení?',
            1 => 'Test bez animace: je stav jasný i ze statického snímku?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Struktura studie',
              'levels' => 
              array (
                0 => 'Jen galerie obrázků.',
                1 => 'Problém bez rozhodnutí.',
                2 => 'Problém → 2 rozhodnutí → výsledek.',
                3 => 'Doplněné měřitelné zjištění.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Mikrointerakce',
              'levels' => 
              array (
                0 => 'Pohyb bez funkce.',
                1 => 'Funkce nejasná.',
                2 => 'Spouštěč, zpětná vazba a délka.',
                3 => 'Funguje i bez animace.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obhajoba',
              'levels' => 
              array (
                0 => 'Nedokončí.',
                1 => 'Popis bez důvodů.',
                2 => 'Za 30 s problém a dvě rozhodnutí.',
                3 => 'Odpoví i na doplňující otázku.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Prezentace práce',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co do případové studie patří nejvíc?',
              'options' => 
              array (
                0 => 'Co nejvíc obrázků bez textu.',
                1 => 'Problém, klíčová rozhodnutí a výsledek s důkazem.',
                2 => 'Seznam programů, které jsem použil.',
              ),
              'correct' => 1,
              'explanation' => 'Studie ukazuje přemýšlení, ne jen výsledek.',
            ),
            1 => 
            array (
              'question' => 'Kdy má mikrointerakce smysl?',
              'options' => 
              array (
                0 => 'Když dává uživateli zpětnou vazbu o tom, co se stalo.',
                1 => 'Když je stránka nudná.',
                2 => 'Vždy, čím víc pohybu, tím lépe.',
              ),
              'correct' => 0,
              'explanation' => 'Pohyb má nést informaci.',
            ),
            2 => 
            array (
              'question' => 'Uživatel má v systému vypnuté animace. Co se má stát s tvou mikrointerakcí?',
              'options' => 
              array (
                0 => 'Akce přestane fungovat.',
                1 => 'Animace se přehraje dvakrát rychleji.',
                2 => 'Stav se ukáže bez pohybu, ale stejně srozumitelně.',
              ),
              'correct' => 2,
              'explanation' => 'Význam nesmí záviset jen na pohybu.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: dopiš do studie jeden odstavec „co bych příště udělal jinak“.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Do portfolia jen vlastní práce; práce spolužáků jen s jejich souhlasem a uvedením autora.',
          1 => 'V portfoliu nezveřejňuj osobní údaje (adresa, telefon).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: studie = popis nástrojů. Ptej se „proč jsi to rozhodl takhle?“.',
          1 => 'Otázka do třídy: Co by se stalo, kdyby animace chyběla?',
          2 => 'Tempo: prototyp může být i jednoduchý GIF – nenech žáky utopit se v nástroji.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci píšou studii podle úkolů 1–2 a kreslí storyboard (úkol 3).',
          1 => 'Plán B offline: studie na papíře, storyboard jako komiks o 4 políčkách.',
        ),
        'glossary' => 
        array (
          0 => 'case-study',
          1 => 'microinteraction',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      7 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 7 · Přístupnost a responzivní rozhraní',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout sekci rozhraní, která je čitelná, ovladatelná klávesnicí i dotykem a srozumitelná v chybových stavech.',
          'success_criteria' => 
          array (
            0 => 'Text má dostatečný kontrast a fokus (focus) je viditelný.',
            1 => 'Cíle dotyku (touch target) mají aspoň 44 × 44 px.',
            2 => 'Chybový a nedostupný stav nejsou odlišené jen barvou.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Nechá třídu projít ukázku jen klávesnicí.',
            'student' => 'Zapíší, kde se ztratili.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Audit přístupnosti',
            'teacher' => 'Předvede Accessibility Audit Lab.',
            'student' => 'Ověří kontrast a fokus na ukázce.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Čitelnost a mezery',
            'teacher' => 'Připomene textové role a velikost cílů dotyku.',
            'student' => 'Nastaví role a mezery, zkontrolují 44 px.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 57,
            'phase' => 'Sekce pro počítač',
            'teacher' => 'Hlídá viditelný fokus.',
            'student' => 'Navrhnou kartu s akcí a stavem fokus.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 78,
            'phase' => 'Mobil a stavy',
            'teacher' => 'Ukáže chybu vyjádřenou textem a ikonou.',
            'student' => 'Převedou sekci na 390 px a navrhnou chybu, nedostupnost a fokus.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Spustí kontrolu v šedi a na mobilu.',
            'student' => 'Ověří stavy bez barev a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Proveď audit ukázky: zkontroluj kontrast textu a viditelnost fokusu při ovládání klávesnicí.',
            'output' => 'Seznam 3 nálezů.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni kartu s akcí pro počítač s viditelným fokusem a dostatečným kontrastem.',
            'output' => 'Karta pro počítač.',
            'time' => '17 min',
          ),
          2 => 
          array (
            'text' => 'Převeď kartu na 390 px, zachovej pořadí čtení a cíle dotyku aspoň 44 × 44 px.',
            'output' => 'Mobilní verze.',
            'time' => '13 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni stavy chyba, nedostupné a fokus tak, aby byly pochopitelné i bez barvy.',
            'output' => 'Tři stavy + test v šedi.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam 5 bodů (kontrast, fokus, 44 px, text u chyby, pořadí) a vzorovou kartu.',
          'standard' => 'Audit, karta, mobilní verze a stavy podle zadání.',
          'challenge' => 'Popíše pořadí fokusu na celé sekci a najde místo, kde by se uživatel klávesnice zasekl.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Tab průchod: učitel projde jeden návrh „jako klávesnice“.',
            1 => 'Palec: má každé tlačítko aspoň 44 px?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Kontrast a fokus',
              'levels' => 
              array (
                0 => 'Neověřeno.',
                1 => 'Ověřen jen kontrast.',
                2 => 'Kontrast i fokus v pořádku.',
                3 => 'Vysvětlí, komu které řešení pomáhá.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Mobil a dotyk',
              'levels' => 
              array (
                0 => 'Bez mobilní verze.',
                1 => 'Malé cíle dotyku.',
                2 => 'Pořadí čtení zachované, cíle ≥ 44 px.',
                3 => 'Ověří i otočení displeje.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Stavy bez barvy',
              'levels' => 
              array (
                0 => 'Jen barva.',
                1 => 'Část stavů s textem.',
                2 => 'Všechny stavy čitelné v šedi.',
                3 => 'Stavy zdokumentuje pro předání.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Přístupnost rozhraní',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jak velký má být cíl dotyku tlačítka na mobilu?',
              'options' => 
              array (
                0 => 'Aspoň zhruba 44 × 44 px, aby se dal pohodlně trefit.',
                1 => 'Stačí 10 × 10 px, když je vidět.',
                2 => 'Na velikosti nezáleží.',
              ),
              'correct' => 0,
              'explanation' => 'Malé cíle se špatně trefují prstem.',
            ),
            1 => 
            array (
              'question' => 'Uživatel ovládá web jen klávesnicí. Co potřebuje nejvíc?',
              'options' => 
              array (
                0 => 'Animace při najetí myší.',
                1 => 'Tmavý režim.',
                2 => 'Viditelný fokus a logické pořadí prvků.',
              ),
              'correct' => 2,
              'explanation' => 'Fokus je „kurzor“ klávesnice.',
            ),
            2 => 
            array (
              'question' => 'Jak správně ukázat chybu v poli formuláře?',
              'options' => 
              array (
                0 => 'Jen červeným rámečkem.',
                1 => 'Textem u pole, ikonou a návodem, jak chybu opravit.',
                2 => 'Vyskakovacím oknem bez textu.',
              ),
              'correct' => 1,
              'explanation' => 'Chyba musí být pochopitelná i bez barvy.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: projdi jeden web jen klávesou Tab a zapiš, kde nebylo vidět, kde jsi.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Weby se jen prohlížejí; žádné přihlašování ani odesílání formulářů.',
          1 => 'Ukázkový obsah je vymyšlený.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: přístupnost = až na konec. Zařaď kontrolu do každé fáze.',
          1 => 'Otázka do třídy: Kdo všechno používá klávesnici místo myši?',
          2 => 'Tempo: audit lab nepřetahuj – 15 minut stačí.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: Tab průchod ukázkou, pak úkoly 2–4 podle lekce.',
          1 => 'Plán B offline: audit vytištěné obrazovky podle kontrolního seznamu, stavy kreslené.',
        ),
        'glossary' => 
        array (
          0 => 'focus',
          1 => 'touch-target',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      8 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 8 · Typografie II a návrhové tokeny',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést typografii, mezery a barvy do návrhových tokenů a napojit je na varianty komponent tak, aby změna tokenu prošla celým systémem.',
          'success_criteria' => 
          array (
            0 => 'Textové role mají hodnoty pro mobil i počítač a nadpis se láme čitelně.',
            1 => 'Odliším základní tokeny (např. modrá-600) od významových (např. barva-akce).',
            2 => 'Varianty tlačítka používají jen tokeny a mají stavy najetí, fokus, nedostupné.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Změní jednu barvu ve dvou souborech – v jednom se propíše, v druhém ne.',
            'student' => 'Odhadnou, v čem je rozdíl.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Responzivní typografie',
            'teacher' => 'Předvede Responsive Type Lab.',
            'student' => 'Definují role pro mobil a počítač a ověří zalomení nadpisu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Kontrola šířek',
            'teacher' => 'Ukáže nečitelný stav na 768 px.',
            'student' => 'Ověří 390 / 768 / 1440 px a opraví jeden problém.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Významové tokeny',
            'teacher' => 'Předvede Semantic Token Lab.',
            'student' => 'Oddělí základní a významové tokeny a pojmenují je.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 80,
            'phase' => 'Varianty komponent',
            'teacher' => 'Hlídá, aby komponenty neměly ruční hodnoty.',
            'student' => 'Vytvoří varianty tlačítka napojené na tokeny se stavy.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Test změny a exit ticket',
            'teacher' => 'Vyzve ke změně jednoho tokenu.',
            'student' => 'Změní barvu akce a sledují, co se propsalo; exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Definuj textové role pro mobil a počítač a ověř, že se nadpis láme čitelně na 390 px.',
            'output' => 'Tabulka rolí pro dvě šířky.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zkontroluj návrh na 390, 768 a 1440 px a oprav jeden nečitelný stav (řádkování nebo šířka).',
            'output' => 'Před/po opravy.',
            'time' => '13 min',
          ),
          2 => 
          array (
            'text' => 'Odděl základní a významové návrhové tokeny (design tokens) a pojmenuj tokeny mezer a barev.',
            'output' => 'Seznam tokenů ve dvou vrstvách.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Vytvoř varianty tlačítka (hlavní, vedlejší) jen z tokenů a se stavy najetí, fokus a nedostupné.',
            'output' => 'Varianty tlačítka.',
            'time' => '22 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou vrstvu základních tokenů; vytváří jen významové tokeny a jedno tlačítko.',
          'standard' => 'Role, kontrola šířek, tokeny a varianty podle zadání.',
          'challenge' => 'Přidá tmavý motiv jen záměnou významových tokenů a ověří kontrast.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Test změny: změna jednoho tokenu se propíše – kolik komponent se změnilo?',
            1 => 'Pojmenování: soused podle názvu tokenu odhadne, kde se používá.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Responzivní typografie',
              'levels' => 
              array (
                0 => 'Jedna sada velikostí.',
                1 => 'Dvě sady, nadpis se láme špatně.',
                2 => 'Role pro obě šířky, čitelné zalomení.',
                3 => 'Ověřeno i na 768 px s opravou.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Tokeny',
              'levels' => 
              array (
                0 => 'Ruční hodnoty.',
                1 => 'Tokeny bez vrstev.',
                2 => 'Základní a významové tokeny odděleně.',
                3 => 'Tmavý motiv jen záměnou tokenů.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Varianty komponent',
              'levels' => 
              array (
                0 => 'Jedna verze.',
                1 => 'Varianty s ručními hodnotami.',
                2 => 'Varianty z tokenů se stavy.',
                3 => 'Změna tokenu se propíše bez ruční opravy.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Design systém',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který název tokenu je významový?',
              'options' => 
              array (
                0 => 'modra-600',
                1 => 'barva-akce-hlavni',
                2 => '#1a73e8',
              ),
              'correct' => 1,
              'explanation' => 'Významový token říká k čemu, ne jakou barvou.',
            ),
            1 => 
            array (
              'question' => 'Proč komponenty používají významové tokeny, a ne přímo základní?',
              'options' => 
              array (
                0 => 'Aby šlo změnit význam (např. barvu akce) na jednom místě.',
                1 => 'Protože základní tokeny nejdou exportovat.',
                2 => 'Je to jedno.',
              ),
              'correct' => 0,
              'explanation' => 'Vrstva významu umožní motivy a změny bez přepisování.',
            ),
            2 => 
            array (
              'question' => 'Nadpis se na 768 px láme na 4 krátké řádky. Co je nejlepší oprava?',
              'options' => 
              array (
                0 => 'Smazat polovinu nadpisu.',
                1 => 'Zvětšit písmo.',
                2 => 'Upravit velikost role pro tuto šířku nebo šířku bloku.',
              ),
              'correct' => 2,
              'explanation' => 'Opravujeme pravidlo role, ne jednotlivý výskyt.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vymysli názvy pěti významových tokenů pro školní web (barvy a mezery).',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pracuje se s vlastním návrhem; žádná data uživatelů.',
          1 => 'Písma jen s licencí pro web.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: token pojmenovaný podle barvy („modrá“) použitý jako význam.',
          1 => 'Otázka do třídy: Co se stane, až škola změní barvu loga?',
          2 => 'Tempo: Semantic Token Lab je klíčový – kontrolu šířek klidně zkrať.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem je obrázek variant a seznam tokenů.',
          1 => 'Plán B offline: tokeny jako tabulka na papíře, tlačítka kreslená.',
        ),
        'glossary' => 
        array (
          0 => 'design-token',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      9 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 9 · Interakce II a profesionální předání návrhu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím popsat úplný stavový model interakce, přidat pohyb jen jako zpětnou vazbu a předat komponentu vývojáři se všemi stavy.',
          'success_criteria' => 
          array (
            0 => 'Stavový model obsahuje výchozí, načítání, úspěch, chybu, fokus a nedostupné.',
            1 => 'Varianta bez pohybu zachová význam každého stavu.',
            2 => 'Předávací list (handoff) obsahuje stavy, tokeny, chování a poznámky k přístupnosti.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže tlačítko, které po kliknutí nic nedělá 3 sekundy.',
            'student' => 'Popíšou, co si uživatel myslí.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Stavový model',
            'teacher' => 'Předvede State & Motion Lab.',
            'student' => 'Sepíšou stavy komponenty a přechody mezi nimi.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Pohyb jako zpětná vazba',
            'teacher' => 'Ukáže krátký pohyb, který něco sděluje, a zbytečný pohyb.',
            'student' => 'Navrhnou pohyb jen tam, kde nese informaci.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 57,
            'phase' => 'Prototyp',
            'teacher' => 'Pomáhá propojit větve úspěch a chyba.',
            'student' => 'Propojí stavy v prototypu a otestují obě větve.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 72,
            'phase' => 'Bez pohybu',
            'teacher' => 'Vysvětlí nastavení „omezit pohyb“ (reduced motion).',
            'student' => 'Vytvoří variantu bez pohybu a ověří fokus.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 72,
            'to' => 90,
            'phase' => 'Předání a exit ticket',
            'teacher' => 'Ukáže vzor předávacího listu.',
            'student' => 'Sepíšou handoff a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Sepiš stavový model tlačítka „Odeslat“: výchozí, načítání, úspěch, chyba, fokus, nedostupné a přechody mezi nimi.',
            'output' => 'Diagram stavů.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni krátkou zpětnou vazbu pohybem jen u stavů, kde nese informaci, a zbytečný pohyb odstraň.',
            'output' => 'Seznam pohybů s funkcí a délkou.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Propoj stavy v prototypu a otestuj větev úspěch i chyba; vytvoř variantu bez pohybu (reduced motion).',
            'output' => 'Prototyp se dvěma větvemi a variantou bez pohybu.',
            'time' => '32 min',
          ),
          3 => 
          array (
            'text' => 'Sepiš předávací list (handoff): stavy, tokeny, chování a poznámky k přístupnosti.',
            'output' => 'Předávací list na 1 stranu.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu stavového diagramu a předávacího listu s nadpisy kapitol.',
          'standard' => 'Stavový model, prototyp, varianta bez pohybu a handoff podle zadání.',
          'challenge' => 'Přidá do handoffu stav „offline“ a popíše, jak se komponenta zotaví po obnovení připojení.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po stavovém modelu: soused najde chybějící přechod (např. z chyby zpět).',
            1 => 'Test bez pohybu: je stav jasný i ze statického snímku?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Stavový model',
              'levels' => 
              array (
                0 => 'Jen výchozí stav.',
                1 => 'Stavy bez přechodů.',
                2 => 'Šest stavů a přechody.',
                3 => 'Doplněný stav offline a zotavení.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Pohyb',
              'levels' => 
              array (
                0 => 'Pohyb bez funkce.',
                1 => 'Funkční, ale dlouhý nebo rušivý.',
                2 => 'Krátký pohyb jen se zpětnou vazbou.',
                3 => 'Varianta bez pohybu zachová význam.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Předání',
              'levels' => 
              array (
                0 => 'Jen snímek obrazovky.',
                1 => 'Chybí stavy nebo tokeny.',
                2 => 'Stavy, tokeny, chování, přístupnost.',
                3 => 'Spolužák podle listu popíše implementaci.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Interakce a předání',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Uživatel má zapnuté „omezit pohyb“. Co uděláš se svou animací načítání?',
              'options' => 
              array (
                0 => 'Nahradím ji statickým ukazatelem s textem „Načítám…“.',
                1 => 'Nechám ji, je krátká.',
                2 => 'Načítání úplně skryji.',
              ),
              'correct' => 0,
              'explanation' => 'Význam zůstane, pohyb zmizí.',
            ),
            1 => 
            array (
              'question' => 'Co nejvíc chybí v předání, které obsahuje jen obrázek výchozího stavu?',
              'options' => 
              array (
                0 => 'Jméno autora.',
                1 => 'Ostatní stavy, chování a pravidla rozměrů.',
                2 => 'Vyšší rozlišení obrázku.',
              ),
              'correct' => 1,
              'explanation' => 'Vývojář musí vědět, jak se komponenta chová.',
            ),
            2 => 
            array (
              'question' => 'Kdy má pohyb v rozhraní smysl?',
              'options' => 
              array (
                0 => 'Když je na stránce málo barev.',
                1 => 'Vždy, když to nástroj umí.',
                2 => 'Když uživateli říká, co se stalo nebo kam se něco přesunulo.',
              ),
              'correct' => 2,
              'explanation' => 'Pohyb je zpětná vazba, ne ozdoba.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v jedné aplikaci najdi animaci, která ti něco sděluje, a jednu, která je jen ozdoba.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Prototypy nesbírají skutečná data; vzorové údaje jsou vymyšlené.',
          1 => 'Při sdílení prototypu nastav přístup jen pro třídu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: chybí cesta zpět z chyby. Ptej se „co uživatel udělá teď?“.',
          1 => 'Otázka do třídy: Komu může pohyb na webu vadit?',
          2 => 'Tempo: prototyp je nejdelší část – předávací list může mít jednodušší podobu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: stavový model a handoff jdou na papíře (úkoly 1 a 4), prototyp podle učebny.',
          1 => 'Plán B offline: stavy jako komiks, přechody šipkami.',
        ),
        'glossary' => 
        array (
          0 => 'handoff',
          1 => 'reduced-motion',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      10 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 10 · Informační architektura a cesta uživatele',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím uspořádat obsah malého webu podle úkolů uživatele, nakreslit mapu webu a jednu cestu uživatele a ověřit je jednoduchým testem.',
          'success_criteria' => 
          array (
            0 => 'Obsah je ve skupinách pojmenovaných srozumitelně pro uživatele.',
            1 => 'Mapa webu má 5–8 stránek bez zbytečných úrovní.',
            2 => 'Po testu se spolužákem upravím jednu část struktury.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Zadá: najdi na školním webu jídelníček – kolik kliků?',
            'student' => 'Popíšou cestu a kde váhali.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Obsah do skupin',
            'teacher' => 'Ukáže třídění kartiček podle cíle uživatele.',
            'student' => 'Roztřídí obsah do skupin a pojmenují je.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Mapa webu',
            'teacher' => 'Připomene: méně úrovní, jasné názvy.',
            'student' => 'Nakreslí mapu webu (sitemap) s 5–8 stránkami.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Cesta uživatele',
            'teacher' => 'Ukáže diagram cesty od vstupu k cíli.',
            'student' => 'Nakreslí jednu cestu uživatele (user flow).',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Test stromu',
            'teacher' => 'Vysvětlí test bez designu: jen názvy.',
            'student' => 'Dají spolužákovi 3 úkoly a zapíší, kde váhal.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Úprava a exit ticket',
            'teacher' => 'Vyzve k jedné změně podle pozorování.',
            'student' => 'Upraví strukturu, zapíší před/po a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Roztřiď obsah vymyšleného webu kroužku do skupin podle toho, co uživatel chce udělat, a skupiny pojmenuj.',
            'output' => 'Skupiny obsahu s názvy.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Nakresli mapu webu (sitemap) s 5–8 stránkami a co nejméně úrovněmi.',
            'output' => 'Mapa webu.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Nakresli cestu uživatele (user flow) od vstupu na web k přihlášce do kroužku.',
            'output' => 'Diagram cesty.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Dej spolužákovi 3 úkoly nad názvy stránek (bez designu), zapiš, kde váhal, a uprav jednu část struktury.',
            'output' => 'Záznam testu + před/po úpravy.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičky obsahu a vzor mapy webu; třídí a doplňuje názvy.',
          'standard' => 'Skupiny, mapa webu, cesta uživatele a test podle zadání.',
          'challenge' => 'Navrhne druhou variantu mapy s jinými názvy skupin a porovná výsledky testu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po pojmenování skupin: soused podle názvu odhadne obsah skupiny.',
            1 => 'Test: kolik úkolů spolužák splnil napoprvé?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Skupiny a názvy',
              'levels' => 
              array (
                0 => 'Obsah netříděný.',
                1 => 'Skupiny s interními názvy.',
                2 => 'Skupiny podle cíle uživatele, srozumitelné názvy.',
                3 => 'Názvy ověřené testem.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Mapa a cesta',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Moc úrovní, nejasná cesta.',
                2 => '5–8 stránek a jasná cesta k cíli.',
                3 => 'Cesta má jen nutné rozhodovací body.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Test a úprava',
              'levels' => 
              array (
                0 => 'Bez testu.',
                1 => 'Test bez záznamu.',
                2 => 'Zapsané váhání a jedna úprava.',
                3 => 'Úprava zdůvodněná pozorováním.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Informační architektura',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč se test stromu dělá bez grafického návrhu?',
              'options' => 
              array (
                0 => 'Aby vzhled nezakryl problém ve struktuře a názvech.',
                1 => 'Protože design ještě nikdo neumí.',
                2 => 'Aby byl test rychlejší.',
              ),
              'correct' => 0,
              'explanation' => 'Test ověřuje strukturu, ne vzhled.',
            ),
            1 => 
            array (
              'question' => 'Co je mapa webu (sitemap)?',
              'options' => 
              array (
                0 => 'Seznam obrázků na webu.',
                1 => 'Přehled stránek webu a jejich vztahů.',
                2 => 'Návod k instalaci webu.',
              ),
              'correct' => 1,
              'explanation' => 'Mapa ukazuje strukturu stránek.',
            ),
            2 => 
            array (
              'question' => 'Spolužák při testu hledal „Přihlášku“ ve skupině „Aktuality“. Co z toho plyne?',
              'options' => 
              array (
                0 => 'Spolužák je nepozorný.',
                1 => 'Je potřeba přidat víc stránek.',
                2 => 'Název nebo umístění přihlášky neodpovídá očekávání – uprav strukturu.',
              ),
              'correct' => 2,
              'explanation' => 'Váhání je důkaz problému ve struktuře.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zkus na jednom webu najít konkrétní informaci a zapiš počet kliků a kde jsi váhal.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Web kroužku je vymyšlený; test probíhá jen ve třídě a bez nahrávání.',
          1 => 'Do záznamu testu nepiš jméno testujícího – stačí „spolužák A“.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Testujte strukturu bez vizuálu – vzhled maskuje problémy architektury.',
          1 => 'Otázka do třídy: Jak by tuhle skupinu pojmenoval někdo, kdo web nezná?',
          2 => 'Tempo: test je klíčový – kartičky omez na 15 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: kartičky obsahu, mapa a cesta na papíře, test ve dvojicích.',
          1 => 'Plán B offline: celá hodina s papírovými kartičkami.',
        ),
        'glossary' => 
        array (
          0 => 'sitemap',
          1 => 'user-flow',
        ),
        '_file' => 'lesson_content_v72_2a_a.php',
      ),
      11 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 11 · Formuláře: validace, chyby a stavy',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout formulář jako stavový systém – od nápovědy přes odesílání až po chybu a zotavení bez ztráty vyplněných dat.',
          'success_criteria' => 
          array (
            0 => 'Mám tabulku stavů pole i celého formuláře (výchozí, fokus, vyplněno, chyba, nedostupné, odesílám, úspěch).',
            1 => 'Chybové hlášky jsou konkrétní a říkají, jak chybu opravit.',
            2 => 'Po chybě zůstanou správně vyplněná pole zachovaná a pozornost se přesune k problému.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže formulář, který po chybě smaže všechna pole.',
            'student' => 'Popíšou, jak by se cítili jako uživatelé.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Soupis stavů',
            'teacher' => 'Rozliší stavy pole a stavy celého formuláře.',
            'student' => 'Sepíšou tabulku stavů.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Validace (validation)',
            'teacher' => 'Ukáže nápovědu předem vs. chybu po odeslání.',
            'student' => 'Napíšou nápovědy a chybové hlášky pro 3 pole.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Odesílání',
            'teacher' => 'Vysvětlí riziko dvojího odeslání.',
            'student' => 'Navrhnou průběh po kliknutí na Odeslat.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Zotavení a prototyp',
            'teacher' => 'Hlídá zachování vyplněných dat.',
            'student' => 'Propojí větev úspěch i chyba v prototypu a projdou ho klávesnicí.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Vybere dva prototypy k ukázce chybové větve.',
            'student' => 'Ověří chybovou větev a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Sepiš tabulku stavů: které patří jednomu poli a které celému formuláři.',
            'output' => 'Tabulka stavů.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Pro pole jméno, e-mail a heslo napiš nápovědu předem a konkrétní chybovou hlášku.',
            'output' => 'Šest textů (3 nápovědy, 3 chyby).',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni stav „odesílám“, který zabrání nejasnému dvojímu odeslání.',
            'output' => 'Stav tlačítka a formuláře při odesílání.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Propoj v prototypu větev úspěch i chyba, zachovej správně vyplněná data a projdi prototyp klávesnicí.',
            'output' => 'Prototyp se dvěma větvemi.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzorovou tabulku stavů s polovinou vyplněnou a vzor chybové hlášky.',
          'standard' => 'Tabulka, texty, odesílání a prototyp podle zadání.',
          'challenge' => 'Přidá souhrn chyb nahoře formuláře s odkazy na pole a popíše, kam se přesune fokus.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Hláška nahlas: třída řekne, zda ví, co přesně opravit.',
            1 => 'Kontrola zotavení: zůstalo po chybě vyplněné jméno?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Stavový model',
              'levels' => 
              array (
                0 => 'Jen výchozí stav.',
                1 => 'Stavy bez rozlišení pole/formulář.',
                2 => 'Úplná tabulka stavů.',
                3 => 'Doplněné přechody mezi stavy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Texty validace',
              'levels' => 
              array (
                0 => '„Chyba.“',
                1 => 'Obecné hlášky.',
                2 => 'Konkrétní hlášky s návodem.',
                3 => 'Nápověda předchází chybám.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Zotavení',
              'levels' => 
              array (
                0 => 'Data se ztratí.',
                1 => 'Data zůstanou, fokus ne.',
                2 => 'Data zůstanou a fokus jde na chybu.',
                3 => 'Souhrn chyb s odkazy na pole.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Formuláře a stavy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Uživatel odeslal formulář s chybou v e-mailu. Co se má stát s ostatními vyplněnými poli?',
              'options' => 
              array (
                0 => 'Zůstanou vyplněná, opraví jen e-mail.',
                1 => 'Vymažou se, ať začne znovu.',
                2 => 'Zablokují se.',
              ),
              'correct' => 0,
              'explanation' => 'Zotavení po chybě nesmí trestat uživatele.',
            ),
            1 => 
            array (
              'question' => 'Jaký je rozdíl mezi nápovědou a chybovou hláškou?',
              'options' => 
              array (
                0 => 'Žádný, jsou to synonyma.',
                1 => 'Nápověda je vždy červená.',
                2 => 'Nápověda radí předem, chyba popisuje problém po kontrole.',
              ),
              'correct' => 2,
              'explanation' => 'Dobrá nápověda předchází chybám.',
            ),
            2 => 
            array (
              'question' => 'Proč tlačítko během odesílání ukáže „Odesílám…“ a je dočasně nedostupné?',
              'options' => 
              array (
                0 => 'Aby vypadalo moderně.',
                1 => 'Aby uživatel věděl, že se něco děje, a neodeslal formulář dvakrát.',
                2 => 'Aby uživatel počkal na reklamu.',
              ),
              'correct' => 1,
              'explanation' => 'Jasný stav brání dvojímu odeslání.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi jeden registrační formulář a zapiš, jak hlásí chybu hesla.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Do skutečných formulářů nic neodesíláme; testovací údaje jsou vymyšlené.',
          1 => 'Nikdy nepoužívej svá skutečná hesla v prototypu.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Hodnoť zotavení po chybě, ne jen krásný výchozí stav.',
          1 => 'Otázka do třídy: Kdy je nedostupné tlačítko bez vysvětlení problém?',
          2 => 'Tempo: prototyp zabere nejvíc času – texty lze dopsat doma.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 na papíře, prototyp podle učebny.',
          1 => 'Plán B offline: stavy formuláře kreslené jako komiks.',
        ),
        'glossary' => 
        array (
          0 => 'validation',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      12 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 12 · Auto Layout, varianty a responzivní komponenty',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím postavit komponentu s automatickým rozvržením (Auto Layout) a variantami tak, aby vydržela dlouhý text i úzký displej bez ručních výjimek.',
          'success_criteria' => 
          array (
            0 => 'Rozliším chování „přizpůsobit obsahu“, „vyplnit“ a „pevně“ a vysvětlím rozdíl vnitřního okraje a mezery.',
            1 => 'Tlačítko vydrží delší text, karta má variantu kompaktní a výchozí.',
            2 => 'Při zátěžovém testu najdu a opravím dva problémy.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Vloží do tlačítka dlouhé slovo a tlačítko se rozpadne.',
            'student' => 'Odhadnou, proč se to stalo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Mentální model',
            'teacher' => 'Vysvětlí hug / fill / fixed a padding vs. gap.',
            'student' => 'Na ukázce určí chování každého prvku.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 37,
            'phase' => 'Odolné tlačítko',
            'teacher' => 'Ukáže tlačítko s automatickým rozvržením.',
            'student' => 'Postaví tlačítko, které unese delší text.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 37,
            'to' => 55,
            'phase' => 'Varianty karty',
            'teacher' => 'Připomene pojmenování variant podle významu.',
            'student' => 'Vytvoří kartu kompaktní a výchozí se stejnými tokeny.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Vnořené rozvržení',
            'teacher' => 'Hlídá, aby se nepoužívalo absolutní umístění.',
            'student' => 'Složí kartu z menších komponent.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Zátěžový test a exit ticket',
            'teacher' => 'Rozdá extrémní vstupy: dlouhý text, malý displej, chybějící obrázek.',
            'student' => 'Otestují komponentu, zapíší 2 opravy a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Na ukázce urči u každého prvku chování hug, fill nebo fixed a vysvětli rozdíl mezi vnitřním okrajem (padding) a mezerou (gap).',
            'output' => 'Popsaná ukázka.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Postav tlačítko s automatickým rozvržením (Auto Layout), které unese i dvakrát delší text.',
            'output' => 'Tlačítko + test s dlouhým textem.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Vytvoř kartu ve variantě kompaktní a výchozí se stejnými tokeny a skládej ji z menších komponent.',
            'output' => 'Dvě varianty karty.',
            'time' => '38 min',
          ),
          3 => 
          array (
            'text' => 'Proveď zátěžový test (dlouhý text, šířka 320 px, chybějící obrázek) a zapiš dvě opravy.',
            'output' => 'Záznam testu a dvou oprav.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotové tlačítko s Auto Layoutem jako vzor; staví jen kartu.',
          'standard' => 'Tlačítko, dvě varianty karty a zátěžový test podle zadání.',
          'challenge' => 'Přidá variantu karty pro tmavý motiv jen přes tokeny a otestuje ji stejnými vstupy.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlé určení: učitel ukáže prvek, žáci řeknou hug, fill nebo fixed.',
            1 => 'Zátěžový test: rozpadne se něco při 320 px?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Model rozvržení',
              'levels' => 
              array (
                0 => 'Nerozliší chování.',
                1 => 'Rozliší s chybami.',
                2 => 'Správně určí a vysvětlí padding vs. gap.',
                3 => 'Vysvětlí to spolužákovi na vlastní komponentě.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Odolnost komponent',
              'levels' => 
              array (
                0 => 'Rozpadá se.',
                1 => 'Unese delší text, ne úzký displej.',
                2 => 'Vydrží všechny tři vstupy.',
                3 => 'Bez jediné ruční výjimky.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Varianty',
              'levels' => 
              array (
                0 => 'Jedna verze.',
                1 => 'Varianty s nejasnými názvy.',
                2 => 'Varianty pojmenované podle významu.',
                3 => 'Tmavý motiv přes tokeny.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Komponenty a rozvržení',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co znamená, že prvek „vyplňuje“ (fill) dostupný prostor?',
              'options' => 
              array (
                0 => 'Má pevnou šířku.',
                1 => 'Roztáhne se do šířky rodiče.',
                2 => 'Je tak velký jako jeho text.',
              ),
              'correct' => 1,
              'explanation' => 'Fill = přizpůsobí se rodiči.',
            ),
            1 => 
            array (
              'question' => 'Jaký je rozdíl mezi vnitřním okrajem (padding) a mezerou (gap)?',
              'options' => 
              array (
                0 => 'Padding je uvnitř kolem obsahu, gap je mezi prvky.',
                1 => 'Jsou to dvě jména pro totéž.',
                2 => 'Gap je jen na mobilu.',
              ),
              'correct' => 0,
              'explanation' => 'Padding obaluje, gap rozestupuje.',
            ),
            2 => 
            array (
              'question' => 'V deseti kartách ručně posouváš nadpis o 4 px. Co to znamená?',
              'options' => 
              array (
                0 => 'Je to běžná práce, nic neměním.',
                1 => 'Mám malý monitor.',
                2 => 'Pravidlo komponenty je špatně – opravím ho v hlavní komponentě.',
              ),
              'correct' => 2,
              'explanation' => 'Opakovaná výjimka patří do pravidla.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v jedné aplikaci najdi tlačítko a zkus odhadnout, zda se přizpůsobuje textu, nebo má pevnou šířku.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pracuje se v návrhovém nástroji se školním účtem; sdílení jen se třídou.',
          1 => 'Obsah karet je vymyšlený.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Neomezuj výuku na mechaniku nástroje – žák má vysvětlit pravidlo rozvržení.',
          1 => 'Otázka do třídy: Co se stane s komponentou, když do ní dáme text v němčině?',
          2 => 'Tempo: vnořené rozvržení je těžké – slabší žáci mohou skončit u jedné varianty.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1 a 4 jdou i na papíře, úkoly 2–3 v nástroji podle návodu v lekci.',
          1 => 'Plán B offline: komponenty z papírových proužků, které se „roztahují“.',
        ),
        'glossary' => 
        array (
          0 => 'auto-layout',
          1 => 'padding',
          2 => 'gap',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      13 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 13 · Audit přístupnosti webového návrhu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím provést strukturovaný audit přístupnosti návrhu (kontrast, fokus, pořadí, cíle dotyku, texty, stavy), seřadit nálezy podle závažnosti a opravit dva nejdůležitější.',
          'success_criteria' => 
          array (
            0 => 'Projdu audit v osmi bodech a zapíšu nálezy.',
            1 => 'Nálezy seřadím na vysoké, střední a nízké.',
            2 => 'Dvě největší bariéry opravím a doložím před/po.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Pustí ukázku čtečky obrazovky (screen reader) nad špatně popsaným tlačítkem.',
            'student' => 'Popíšou, co uživatel uslyšel.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kontrolní seznam',
            'teacher' => 'Projde 8 bodů auditu.',
            'student' => 'Zkontrolují vlastní prototyp podle seznamu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Klávesnice',
            'teacher' => 'Ukáže očekávané pořadí fokusu.',
            'student' => 'Seřadí pořadí fokusu a ověří, že fokus není zakrytý.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Zvětšení a přeskládání',
            'teacher' => 'Zvětší text na 200 % a zúží okno.',
            'student' => 'Najdou oříznutý obsah nebo vodorovné posouvání.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Priorita a oprava',
            'teacher' => 'Vysvětlí závažnost = dopad × četnost.',
            'student' => 'Seřadí nálezy a opraví dvě největší bariéry.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Sdílení a exit ticket',
            'teacher' => 'Vybere dvě opravy k ukázce před/po.',
            'student' => 'Ukážou opravu a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Projdi svůj prototyp podle 8 bodů auditu (kontrast, fokus, pořadí, cíle dotyku, popisky, chyby, zvětšení, ne jen barva).',
            'output' => 'Vyplněný kontrolní seznam s nálezy.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zapiš očekávané pořadí fokusu (focus order) na stránce a ověř, že fokus nikde není zakrytý.',
            'output' => 'Číslované pořadí fokusu.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Ověř zvětšení textu na 200 % a úzké okno; zapiš oříznutí nebo vodorovné posouvání.',
            'output' => 'Seznam problémů se zvětšením.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Seřaď nálezy na vysoké / střední / nízké a oprav dva nejzávažnější; ulož před/po.',
            'output' => 'Seřazený seznam + 2 opravy před/po.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam s příklady chyb ke každému bodu a audituje připravenou ukázku.',
          'standard' => 'Audit vlastního prototypu, priorita a dvě opravy.',
          'challenge' => 'Napíše krátkou zprávu pro tým: tři nejčastější chyby a jak jim předcházet už při návrhu komponent.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po kontrolním seznamu: každý řekne jeden nález a jeho dopad.',
            1 => 'Priorita: proč je tenhle nález „vysoký“?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Systematičnost auditu',
              'levels' => 
              array (
                0 => 'Náhodné nálezy.',
                1 => 'Část bodů.',
                2 => 'Všech 8 bodů s nálezy.',
                3 => 'Nálezy s dopadem na konkrétní uživatele.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Prioritizace',
              'levels' => 
              array (
                0 => 'Bez pořadí.',
                1 => 'Pořadí bez důvodu.',
                2 => 'Vysoké / střední / nízké podle dopadu.',
                3 => 'Zdůvodní dopad × četnost.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Opravy',
              'levels' => 
              array (
                0 => 'Žádná.',
                1 => 'Oprava méně závažného.',
                2 => 'Dvě největší bariéry opravené.',
                3 => 'Doložené před/po a prevence do komponent.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Přístupnost rozhraní',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Při zvětšení textu na 200 % musíš stránku posouvat do stran. Co to je?',
              'options' => 
              array (
                0 => 'Problém přeskládání obsahu, který je potřeba opravit.',
                1 => 'Normální chování, za to návrh nemůže.',
                2 => 'Chyba prohlížeče.',
              ),
              'correct' => 0,
              'explanation' => 'Obsah se má přeskládat, ne utéct do strany.',
            ),
            1 => 
            array (
              'question' => 'Jak určíš, která chyba přístupnosti má přednost?',
              'options' => 
              array (
                0 => 'Podle toho, kterou je nejsnazší opravit.',
                1 => 'Podle abecedy.',
                2 => 'Podle dopadu na uživatele a toho, jak často nastává.',
              ),
              'correct' => 2,
              'explanation' => 'Závažnost = dopad × četnost.',
            ),
            2 => 
            array (
              'question' => 'Kdy je nejlepší řešit přístupnost?',
              'options' => 
              array (
                0 => 'Až po exportu hotového webu.',
                1 => 'Už při návrhu komponent a průchodů.',
                2 => 'Jen když si někdo stěžuje.',
              ),
              'correct' => 1,
              'explanation' => 'Pozdní opravy jsou dražší.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zvětši na jednom webu text na 200 % (Ctrl a +) a zapiš, co se rozbilo.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Audituje se vlastní prototyp nebo připravená ukázka; cizí weby se nemění.',
          1 => 'Nálezy se nesdílejí veřejně ani s autory cizích webů bez souhlasu učitele.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Nehodnoť zapamatování čísel – důležitý je systematický audit a oprava.',
          1 => 'Otázka do třídy: Komu konkrétně tahle chyba vadí?',
          2 => 'Tempo: používej reálné prototypy žáků, ušetří to čas na přípravu ukázky.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: audit podle tištěného seznamu, úkoly 1–4 podle lekce.',
          1 => 'Plán B offline: audit vytištěných obrazovek.',
        ),
        'glossary' => 
        array (
          0 => 'focus',
          1 => 'screen-reader',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      14 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 14 · Od návrhu k HTML a CSS: předání, které jde postavit',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést návrh na specifikaci pro vývojáře: strukturu stránky, tokeny, komponenty, pravidla responzivity a tabulku stavů.',
          'success_criteria' => 
          array (
            0 => 'Obrazovku rozdělím na záhlaví, hlavní obsah, sekce a zápatí a komponenty pojmenuji podle funkce.',
            1 => 'Tokeny barev, mezer a písma nahradí seznam náhodných pixelů.',
            2 => 'Spolužák podle mé specifikace popíše implementaci a já opravím dvě nejasnosti.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže předání „jeden obrázek“ a zeptá se, co vývojáři chybí.',
            'student' => 'Vyjmenují chybějící informace.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Struktura stránky',
            'teacher' => 'Ukáže vztah návrhu a struktury HTML (header, main, section, footer).',
            'student' => 'Rozdělí obrazovku a pojmenují komponenty podle funkce.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Tokeny',
            'teacher' => 'Ukáže seznam tokenů místo 40 náhodných hodnot.',
            'student' => 'Zapíší tokeny barev, mezer a písma.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Pravidla responzivity',
            'teacher' => 'Vysvětlí maximální šířku a chování obrázků.',
            'student' => 'Popíšou, kdy se rozvržení skládá pod sebe.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 70,
            'phase' => 'Stavy',
            'teacher' => 'Připomene tabulku stavů a klávesnici.',
            'student' => 'Sepíšou stavy komponent a poznámky ke klávesnici.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 70,
            'to' => 90,
            'phase' => 'Kontrola „vývojářem“ a exit ticket',
            'teacher' => 'Spáruje žáky do rolí návrhář a vývojář.',
            'student' => 'Spolužák popíše implementaci, autor opraví 2 nejasnosti; exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Rozděl svou obrazovku na záhlaví, hlavní obsah, sekce a zápatí a pojmenuj komponenty podle funkce.',
            'output' => 'Popsaná struktura obrazovky.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zapiš tokeny barev, mezer a písma, které návrh používá.',
            'output' => 'Tabulka tokenů.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Popiš pravidla responzivity: maximální šířka obsahu, kdy se sloupce skládají pod sebe, jak se chovají obrázky.',
            'output' => '3–5 pravidel.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Doplň tabulku stavů komponent a nech spolužáka podle celé specifikace (handoff) popsat implementaci; oprav 2 nejasnosti.',
            'output' => 'Tabulka stavů + 2 opravy.',
            'time' => '30 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu specifikace s nadpisy a vzorem jednoho vyplněného řádku.',
          'standard' => 'Úplná specifikace na 1 stranu a kontrola spolužákem.',
          'challenge' => 'Postaví jednu sekci v HTML a CSS podle vlastní specifikace a porovná výsledek s návrhem.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po struktuře: soused podle názvů komponent odhadne jejich funkci.',
            1 => 'Kontrola vývojářem: kolik otázek musel položit?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Struktura a názvy',
              'levels' => 
              array (
                0 => 'Bez struktury.',
                1 => 'Struktura, názvy podle vzhledu.',
                2 => 'Struktura a názvy podle funkce.',
                3 => 'Odpovídá sémantickým prvkům HTML.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Tokeny a pravidla',
              'levels' => 
              array (
                0 => 'Náhodné pixely.',
                1 => 'Tokeny bez responzivity.',
                2 => 'Tokeny i pravidla responzivity.',
                3 => 'Pravidla ověřená na třech šířkách.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Srozumitelnost předání',
              'levels' => 
              array (
                0 => 'Jen obrázek.',
                1 => 'Hodně nejasností.',
                2 => 'Spolužák popíše implementaci.',
                3 => 'Mini implementace odpovídá návrhu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Interakce a předání',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč komponenty pojmenováváme podle funkce (např. „karta-akce“), a ne podle vzhledu („modrá-krabice“)?',
              'options' => 
              array (
                0 => 'Vzhled se může změnit, funkce zůstává.',
                1 => 'Názvy podle barev jsou zakázané.',
                2 => 'Kvůli délce názvu.',
              ),
              'correct' => 0,
              'explanation' => 'Funkční název přežije redesign.',
            ),
            1 => 
            array (
              'question' => 'Co je v předání nejužitečnější místo 40 různých hodnot v pixelech?',
              'options' => 
              array (
                0 => 'Snímek obrazovky ve vyšším rozlišení.',
                1 => 'Malý soubor pojmenovaných tokenů.',
                2 => 'Video z návrhového nástroje.',
              ),
              'correct' => 1,
              'explanation' => 'Tokeny jsou opakovatelná pravidla.',
            ),
            2 => 
            array (
              'question' => 'Který prvek HTML odpovídá hlavnímu obsahu stránky?',
              'options' => 
              array (
                0 => 'footer',
                1 => 'header',
                2 => 'main',
              ),
              'correct' => 2,
              'explanation' => 'main obaluje hlavní obsah stránky.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: v prohlížeči otevři nástroje vývojáře na jednom webu a najdi prvky header, main a footer.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Nástroje vývojáře používáme jen ke čtení – produkční weby neměníme.',
          1 => 'Specifikace neobsahuje skutečná data uživatelů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Nemusí se psát celý kód – cílem je spojit návrh a implementační myšlení.',
          1 => 'Otázka do třídy: Co by se vývojář musel zeptat, kdyby měl jen obrázek?',
          2 => 'Tempo: kontrola ve dvojicích je jádro hodiny, začni ji nejpozději v 70. minutě.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 jako papírová specifikace, kontrola ve dvojicích.',
          1 => 'Plán B offline: celá specifikace ručně na A4.',
        ),
        'glossary' => 
        array (
          0 => 'handoff',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      15 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 15 · Rozvržení v CSS: Flexbox, Grid a responzivní pravidla',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozhodnout, kdy rozvržení postavit jako Flexbox (jedna osa) a kdy jako Grid (dvě osy), a popsat, jak se mřížka karet mění 3 → 2 → 1 sloupec.',
          'success_criteria' => 
          array (
            0 => 'U tří částí stránky zdůvodním volbu Flexbox, nebo Grid.',
            1 => 'Mřížka karet se mění 3 → 2 → 1 bez pevné šířky, která přetéká.',
            2 => 'U existující stránky najdu kontejner, mezeru, maximální šířku a směr rozvržení.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže web, kde karty na mobilu přetékají ven z obrazovky.',
            'student' => 'Odhadnou příčinu (pevná šířka).',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Flex vs. Grid',
            'teacher' => 'Vysvětlí jednu osu vs. dvě osy na příkladech.',
            'student' => 'Zařadí 5 příkladů rozvržení.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Úvodní sekce jako pravidlo',
            'teacher' => 'Ukáže dva sloupce na počítači a jeden na mobilu.',
            'student' => 'Popíšou pravidlo úvodní sekce.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 58,
            'phase' => 'Mřížka karet',
            'teacher' => 'Ukáže mřížku 3 → 2 → 1 bez pevných šířek.',
            'student' => 'Navrhnou mřížku karet a zapíší pravidlo.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 75,
            'phase' => 'Prohlížení existující stránky',
            'teacher' => 'Předvede nástroje vývojáře jen pro čtení.',
            'student' => 'Najdou kontejner, mezeru, maximální šířku a směr.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Poznámky k implementaci a exit ticket',
            'teacher' => 'Zadá poznámky ke třem komponentám.',
            'student' => 'Napíšou model rozvržení a chování a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zařaď pět příkladů rozvržení (menu, karty, formulář, galerie, zápatí) jako Flexbox, nebo Grid a zdůvodni.',
            'output' => 'Pět rozhodnutí s důvodem.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Popiš pravidlo úvodní sekce: dva sloupce na počítači, jeden na mobilu.',
            'output' => 'Pravidlo v jedné až dvou větách.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni mřížku karet, která se mění 3 → 2 → 1 sloupec a nikde nemá pevnou šířku, která přetéká.',
            'output' => 'Náčrt tří stavů mřížky + pravidlo.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'V nástrojích vývojáře (jen ke čtení) najdi na existující stránce kontejner, mezeru (gap), maximální šířku a směr rozvržení.',
            'output' => 'Čtyři zjištěné hodnoty.',
            'time' => '17 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičky s obrázky rozvržení a nápovědu „řada nebo tabulka?“.',
          'standard' => 'Rozhodnutí, pravidla a prohlížení podle zadání.',
          'challenge' => 'Napíše mřížku karet v CSS (grid-template-columns s auto-fit) a ověří ji na třech šířkách.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž rukou: jedna ruka = jedna osa (Flex), dvě ruce = dvě osy (Grid).',
            1 => 'Kontrola mřížky: kde by karta přetekla?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Volba modelu',
              'levels' => 
              array (
                0 => 'Náhodná.',
                1 => 'Správně bez důvodu.',
                2 => 'Správně se zdůvodněním.',
                3 => 'Ukáže hranici, kdy se volba mění.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Responzivní pravidla',
              'levels' => 
              array (
                0 => 'Jen popis vzhledu.',
                1 => 'Pevné šířky.',
                2 => 'Mřížka 3 → 2 → 1 bez přetečení.',
                3 => 'Ověřeno nebo napsané v CSS.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Čtení existující stránky',
              'levels' => 
              array (
                0 => 'Nenajde nic.',
                1 => 'Jedna hodnota.',
                2 => 'Všechny čtyři hodnoty.',
                3 => 'Vysvětlí, proč autor zvolil daný model.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Komponenty a rozvržení',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Navigační lišta s položkami v jedné řadě – který model je nejpřirozenější?',
              'options' => 
              array (
                0 => 'Grid se dvěma osami.',
                1 => 'Flexbox v jedné ose.',
                2 => 'Absolutní umístění.',
              ),
              'correct' => 1,
              'explanation' => 'Jedna řada = jedna osa.',
            ),
            1 => 
            array (
              'question' => 'Galerie fotek v řádcích i sloupcích, které se mají zarovnat – co použiješ?',
              'options' => 
              array (
                0 => 'Grid.',
                1 => 'Jen vnitřní okraje.',
                2 => 'Tabulku s pevnými šířkami.',
              ),
              'correct' => 0,
              'explanation' => 'Dvě osy zarovnání = Grid.',
            ),
            2 => 
            array (
              'question' => 'Proč karty na mobilu přetékají ven z obrazovky?',
              'options' => 
              array (
                0 => 'Protože mobil neumí CSS.',
                1 => 'Protože je moc barev.',
                2 => 'Často kvůli pevné šířce, která je větší než displej.',
              ),
              'correct' => 2,
              'explanation' => 'Pevná šířka nebere ohled na displej.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na jednom webu zmenšuj okno prohlížeče a zapiš, při jaké šířce se karty přeskládají.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Nástroje vývojáře jen ke čtení; na produkčních webech nic neměníme ani neukládáme.',
          1 => 'Při zkoušení CSS pracujeme ve vlastním souboru.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Výklad může být bez programování; důležitý je převod vztahů do modelu rozvržení.',
          1 => 'Otázka do třídy: Je tohle řada, nebo tabulka?',
          2 => 'Tempo: kdo umí HTML a CSS, ať si zkusí mini implementaci (výzva).',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–3 na papíře, úkol 4 ukáže vyučující zástupu na projektoru.',
          1 => 'Plán B offline: rozvržení ze čtverečků papíru, které se přeskládají.',
        ),
        'glossary' => 
        array (
          0 => 'flexbox',
          1 => 'grid',
          2 => 'gap',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      16 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 16 · Test použitelnosti: pozorování místo dojmů',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím připravit a vést krátký test použitelnosti bez navádění, zapsat pozorování odděleně od výkladu a seřadit problémy podle dopadu.',
          'success_criteria' => 
          array (
            0 => 'Připravím tři realistické úkoly, které neprozrazují cestu.',
            1 => 'Zapisuji, co účastník dělal, ne co si o tom myslím.',
            2 => 'Vyberu nejvýš tři problémy podle četnosti × dopadu a navrhnu změnu.',
          ),
        ),
        'competencies' => 
        array (
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Zahraje test s navádějícími otázkami a pak bez nich.',
            'student' => 'Řeknou, který test dal víc informací.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Plán testu',
            'teacher' => 'Ukáže rozdíl úkolu „najdi přihlášku“ a „klikni na Přihlásit“.',
            'student' => 'Napíšou tři úkoly bez prozrazení cesty.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Moderování',
            'teacher' => 'Nacvičí věty „Co byste teď udělali?“ a ticho.',
            'student' => 'Vyzkouší si roli moderátora ve dvojici.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 60,
            'phase' => 'Test a záznam',
            'teacher' => 'Hlídá oddělení pozorování od výkladu.',
            'student' => 'Otestují prototyp se 1–2 spolužáky a zapisují pozorování.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 75,
            'phase' => 'Priorita',
            'teacher' => 'Vysvětlí četnost × dopad.',
            'student' => 'Seřadí problémy a vyberou nejvýš tři.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Změna a exit ticket',
            'teacher' => 'Zadá formát problém → důkaz → změna.',
            'student' => 'Navrhnou 1–3 změny a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš tři realistické úkoly pro test použitelnosti (usability test), které neprozrazují cestu ani názvy tlačítek.',
            'output' => 'Plán testu se 3 úkoly.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Otestuj prototyp s 1–2 spolužáky a zapisuj pozorování (co dělal) odděleně od výkladu (co si myslím).',
            'output' => 'Záznam pozorování ve dvou sloupcích.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Ohodnoť problémy podle četnosti a dopadu a vyber nejvýš tři k úpravě.',
            'output' => 'Seřazený seznam problémů.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni 1–3 změny ve tvaru problém → důkaz → změna.',
            'output' => 'Návrh změn s důkazem.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzorový plán testu a záznamový arch se dvěma sloupci.',
          'standard' => 'Vlastní plán, test, priorita a změny podle zadání.',
          'challenge' => 'Porovná výsledky dvou účastníků a odliší náhodný problém od opakovaného.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po plánu: soused hledá úkol, který prozrazuje cestu.',
            1 => 'Záznam: učitel namátkou přečte řádek – je to pozorování, nebo výklad?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Plán testu',
              'levels' => 
              array (
                0 => 'Úkoly chybí.',
                1 => 'Úkoly navádějí.',
                2 => 'Tři realistické úkoly bez nápovědy.',
                3 => 'Úkoly pokrývají hlavní cíl webu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Záznam',
              'levels' => 
              array (
                0 => 'Jen dojmy.',
                1 => 'Pozorování smíchané s výkladem.',
                2 => 'Oddělené pozorování a výklad.',
                3 => 'Přesné citace a časy.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Priorita a změna',
              'levels' => 
              array (
                0 => 'Bez priority.',
                1 => 'Všechno je „důležité“.',
                2 => 'Nejvýš 3 problémy s důkazem.',
                3 => 'Změna přímo vychází z důkazu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => '',
          'competence_label' => 'Výzkum a test',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který zápis je pozorování (ne výklad)?',
              'options' => 
              array (
                0 => 'Navigace je špatná.',
                1 => 'Účastník třikrát klikl na Zpět a pak hledal v zápatí.',
                2 => 'Uživatel je zmatený, protože web je ošklivý.',
              ),
              'correct' => 1,
              'explanation' => 'Pozorování popisuje chování.',
            ),
            1 => 
            array (
              'question' => 'Účastník se při testu zasekne. Co uděláš jako moderátor?',
              'options' => 
              array (
                0 => 'Hned mu ukážu správné tlačítko.',
                1 => 'Ukončím test.',
                2 => 'Chvíli počkám a zeptám se: „Co byste teď udělali?“',
              ),
              'correct' => 2,
              'explanation' => 'Brzká nápověda zakryje problém.',
            ),
            2 => 
            array (
              'question' => 'Kolik účastníků stačí na nácvik metody ve třídě?',
              'options' => 
              array (
                0 => '1–3 účastníci, cílem je naučit se metodu.',
                1 => 'Nejméně 100.',
                2 => 'Žádný, stačí vlastní názor.',
              ),
              'correct' => 0,
              'explanation' => 'Ve třídě jde o nácvik, ne o reprezentativní výzkum.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: požádej někoho doma o jeden úkol v aplikaci a zapiš jen to, co dělal (bez hodnocení).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Test bez nahrávání a bez jmen účastníků v záznamu (účastník A, B).',
          1 => 'Účast je dobrovolná; kdo nechce být testován, je zapisovatel.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Stačí 1–3 testující – jde o nácvik metody, ne o reprezentativní výzkum.',
          1 => 'Otázka do třídy: Je tohle, co viděl, nebo co si myslí?',
          2 => 'Tempo: test ve skupinách pohlídej časovačem – 20 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: test na papírovém prototypu podle úkolů 1–4.',
          1 => 'Plán B offline: papírový prototyp, moderátor „hraje počítač“.',
        ),
        'glossary' => 
        array (
          0 => 'usability-test',
        ),
        '_file' => 'lesson_content_v72_2a_b.php',
      ),
      17 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 17 · Kontrola návrhu v kódu: pravidla místo pixelů',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím porovnat hotovou stránku s předaným návrhem podle pravidel (tokeny, stavy, mezery, přístupnost) a zapsat nalezené odchylky jako srozumitelná hlášení.',
          'success_criteria' => 
          array (
            0 => 'Odchylku popíšu jako pravidlo (např. návrhový token, design token), které se porušilo, ne jako „o 2 px jinak“.',
            1 => 'Každé hlášení má kde, co se očekávalo, co je skutečnost a závažnost.',
            2 => 'Zkontroluji i stavy komponent a ovládání klávesnicí.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'g2_handoff',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'w2_a11y_audit',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: pixel, nebo pravidlo?',
            'teacher' => 'Ukáže návrh a implementaci s rozdílem 2 px a s chybějícím stavem fokusu.',
            'student' => 'Rozhodnou, která odchylka je skutečný problém.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kontrolní seznam QA',
            'teacher' => 'Představí seznam: tokeny, typografie, mezery, stavy, responzivita, přístupnost.',
            'student' => 'Doplní k bodům, jak je ověří.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 55,
            'phase' => 'Kontrola implementace',
            'teacher' => 'Rozdá ukázkovou stránku s 8 záměrnými chybami.',
            'student' => 'Hledají odchylky podle seznamu.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 55,
            'to' => 72,
            'phase' => 'Hlášení odchylek',
            'teacher' => 'Ukáže tvar hlášení a stupnici závažnosti.',
            'student' => 'Zapíší 4 hlášení a určí závažnost.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 72,
            'to' => 82,
            'phase' => 'Porovnání nálezů',
            'teacher' => 'Odhalí seznam všech 8 chyb.',
            'student' => 'Porovnají svá zjištění a doplní, co přehlédli.',
            'form' => 'frontálně',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: kontrolujeme pravidla a dopad.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Doplň ke každému bodu kontrolního seznamu QA (tokeny, typografie, mezery, stavy, responzivita, přístupnost) jednu konkrétní kontrolu.',
            'output' => 'Seznam šesti bodů s tím, jak je ověříš.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Najdi na ukázkové stránce co nejvíc odchylek od předaného návrhu podle kontrolního seznamu.',
            'output' => 'Seznam nalezených odchylek s místem na stránce.',
            'time' => '28 min',
          ),
          2 => 
          array (
            'text' => 'Zapiš čtyři odchylky jako hlášení: kde, co se očekávalo, skutečnost, závažnost (blokuje / vadí / kosmetické).',
            'output' => 'Čtyři hlášení ve stejném tvaru.',
            'time' => '17 min',
          ),
          3 => 
          array (
            'text' => 'Porovnej své nálezy se seznamem všech chyb a zapiš, kterou kategorii kontroly jsi přehlédl(a).',
            'output' => 'Krátká reflexe: co přidám do svého postupu kontroly.',
            'time' => '10 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam s předvyplněnými kontrolami a šablonu hlášení.',
          'standard' => 'Vlastní kontroly, hledání odchylek a čtyři hlášení.',
          'challenge' => 'Navrhne, kterou kontrolu lze automatizovat (např. kontrast) a kterou musí udělat člověk.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá otázka: rozdíl 2 px v okraji karty – blokuje, vadí, nebo kosmetické?',
            1 => 'Kontrola hlášení ve dvojici: dá se oprava udělat bez doptávání?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Kontrolní seznam',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen vzhled.',
                2 => 'Všech šest oblastí s kontrolou.',
                3 => 'Rozliší automatické a ruční kontroly.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Nalezené odchylky',
              'levels' => 
              array (
                0 => 'Méně než 2.',
                1 => '3–4.',
                2 => '5–6 včetně stavu a přístupnosti.',
                3 => '7–8 a pochopí příčinu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Hlášení',
              'levels' => 
              array (
                0 => 'Nejasná.',
                1 => 'Bez závažnosti.',
                2 => 'Kde, očekávání, skutečnost, závažnost.',
                3 => 'Navrhuje pravidlo, které chybě předejde.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'g2_handoff',
          'competence_label' => 'Předání návrhu a případová studie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Která odchylka při kontrole implementace je nejzávažnější?',
              'options' => 
              array (
                0 => 'Okraj karty je o 2 px menší.',
                1 => 'Tlačítko nemá viditelný fokus a nedá se ovládat klávesnicí.',
                2 => 'Stín je o odstín světlejší.',
              ),
              'correct' => 1,
              'explanation' => 'Blokuje část uživatelů – to má přednost před kosmetikou.',
            ),
            1 => 
            array (
              'question' => 'Co musí obsahovat hlášení odchylky?',
              'options' => 
              array (
                0 => 'Kde, co se očekávalo, co je skutečnost a závažnost.',
                1 => 'Jméno toho, kdo chybu udělal.',
                2 => 'Jen snímek obrazovky.',
              ),
              'correct' => 0,
              'explanation' => 'Vývojář pak opraví bez doptávání.',
            ),
            2 => 
            array (
              'question' => 'Proč kontrolovat podle pravidel (tokenů) a ne podle pixelů?',
              'options' => 
              array (
                0 => 'Protože pixely nejdou změřit.',
                1 => 'Protože pravidla jsou kratší.',
                2 => 'Protože pravidlo říká záměr a odchylka od něj se opakuje na více místech.',
              ),
              'correct' => 2,
              'explanation' => 'Oprava pravidla opraví všechna místa najednou.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: zkontroluj jednu stránku své předchozí práce podle kontrolního seznamu QA a zapiš dvě hlášení.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Ukázková stránka je vymyšlená; neprověřujeme cizí weby bez dovolení.',
          1 => 'Hlášení se týkají produktu, ne lidí – bez jmen autorů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: hlášení „vypadá to jinak“ – vyžaduj očekávání a skutečnost.',
          1 => 'Otázka do třídy: Kterou chybu by uživatel poznal jako první?',
          2 => 'Tempo: seznam 8 chyb odhal až po hlášeních, jinak žáci jen odškrtávají.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vytištěnou ukázkovou stránku a šablonu hlášení; žáci plní úkoly 1–4.',
          1 => 'Plán B offline: kontrola vytištěného návrhu proti vytištěné implementaci.',
        ),
        'glossary' => 
        array (
          0 => 'handoff',
          1 => 'design-token',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      18 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 18 · Závěrečný projekt: produktová stránka a případová studie',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím dokončit produktovou landing page podle informační architektury a design systému a sepsat k ní případovou studii s důkazem z testu.',
          'success_criteria' => 
          array (
            0 => 'Stránka používá tokeny design systému a funguje od 320 do 1440 px.',
            1 => 'Formulář má stavy chyby a potvrzení.',
            2 => 'Případová studie (case study) má problém, tři rozhodnutí a výsledek s důkazem (test, měření).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_css_responsive',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'g2_handoff',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Stav projektu',
            'teacher' => 'Promítne kontrolní seznam závěrečného projektu.',
            'student' => 'Označí, co mají hotové a co chybí.',
            'form' => 'jednotlivě',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 40,
            'phase' => 'Dokončení stránky',
            'teacher' => 'Obchází podle seznamu a ptá se na důkazy.',
            'student' => 'Dokončí sekce, tokeny a responzivitu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Stavy formuláře',
            'teacher' => 'Připomene stavy chyby a potvrzení.',
            'student' => 'Doplní stavy formuláře.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Případová studie',
            'teacher' => 'Ukáže osnovu studie a příklad důkazu.',
            'student' => 'Napíší studii s důkazem z testu nebo měření.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 75,
            'to' => 83,
            'phase' => 'Rychlá kontrola',
            'teacher' => 'Zadá vzájemnou kontrolu podle seznamu.',
            'student' => 'Zkontrolují projekt souseda.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 83,
            'to' => 90,
            'phase' => 'Odevzdání a exit ticket',
            'teacher' => 'Ukončí práci.',
            'student' => 'Odevzdají projekt a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Dokonči sekce produktové stránky tak, aby používaly jen tokeny design systému a fungovaly od 320 do 1440 px.',
            'output' => 'Stránka bez vodorovného posouvání na 320 px a bez hodnot mimo tokeny.',
            'time' => '30 min',
          ),
          1 => 
          array (
            'text' => 'Doplň do formuláře stav chyby u pole a stav potvrzení po odeslání s dalším krokem.',
            'output' => 'Formulář se třemi stavy: výchozí, chyba, potvrzení.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Napiš případovou studii projektu: problém, tři klíčová rozhodnutí a výsledek doložený testem nebo měřením.',
            'output' => 'Studie na jednu stranu s jedním důkazem.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Zkontroluj projekt spolužáka podle kontrolního seznamu a zapiš dvě nejdůležitější odchylky.',
            'output' => 'Dvě hlášení se závažností.',
            'time' => '8 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane osnovu studie s otázkami a zkrácený kontrolní seznam.',
          'standard' => 'Dokončená stránka, stavy formuláře, studie a kontrola souseda.',
          'challenge' => 'Doplní do studie srovnání před/po (např. úspěšnost úkolu v testu použitelnosti).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola na 320 px: učitel náhodně zúží okno u tří projektů.',
            1 => 'Dvojice: najdi v sousedově studii důkaz.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Stránka a tokeny',
              'levels' => 
              array (
                0 => 'Nedokončená.',
                1 => 'Hodnoty mimo tokeny.',
                2 => 'Tokeny a 320–1440 px bez chyb.',
                3 => 'Zdůvodní výjimky z tokenů.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Stavy formuláře',
              'levels' => 
              array (
                0 => 'Bez stavů.',
                1 => 'Jen chyba.',
                2 => 'Chyba i potvrzení s dalším krokem.',
                3 => 'Chyba říká opravu a je u pole.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Případová studie',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Popis bez rozhodnutí.',
                2 => 'Problém, tři rozhodnutí, výsledek s důkazem.',
                3 => 'Srovnání před/po.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'g2_handoff',
          'competence_label' => 'Předání návrhu a případová studie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co je v případové studii nejlepší důkaz výsledku?',
              'options' => 
              array (
                0 => 'Že se mi stránka líbí.',
                1 => 'Výsledek testu použitelnosti: 4 z 5 uživatelů dokončili přihlášku.',
                2 => 'Počet hodin práce.',
              ),
              'correct' => 1,
              'explanation' => 'Důkaz je ověřitelný výsledek, ne dojem.',
            ),
            1 => 
            array (
              'question' => 'Na jakém rozsahu šířek má produktová stránka fungovat bez vodorovného posouvání?',
              'options' => 
              array (
                0 => 'Jen 1440 px.',
                1 => 'Jen 390 px.',
                2 => 'Od 320 px až po široké obrazovky.',
              ),
              'correct' => 2,
              'explanation' => 'Responzivita = celý rozsah šířek.',
            ),
            2 => 
            array (
              'question' => 'Co má obsahovat stav potvrzení formuláře?',
              'options' => 
              array (
                0 => 'Co se stalo a jaký je další krok.',
                1 => 'Jen zelenou fajfku.',
                2 => 'Kód odpovědi serveru.',
              ),
              'correct' => 0,
              'explanation' => 'Uživatel potřebuje jistotu a další krok.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: požádej jednoho člověka z rodiny o test přihlášky na tvé stránce a zapiš výsledek do studie.',
            'minutes' => 25,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Produkt a texty jsou vymyšlené; v testech nezaznamenáváme jména ani tváře testujících.',
          1 => 'Obrázky a písma jen s licencí; zdroje uveď ve studii.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: studie popisuje, co žák dělal, ne proč – ptej se „jaké rozhodnutí a podle čeho?“.',
          1 => 'Otázka do třídy: Jaký důkaz přesvědčí někoho, kdo váš projekt neviděl?',
          2 => 'Tempo: když stránka není hotová, studii piš o rozpracované verzi – rozhodnutí jsou cennější než dokonalost.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá kontrolní seznam a osnovu studie; žáci pracují samostatně a odevzdají soubory.',
          1 => 'Plán B offline: případová studie na papíře k vytištěným snímkům projektu.',
        ),
        'glossary' => 
        array (
          0 => 'case-study',
          1 => 'landing-page',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      19 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 19 · Výzkum s uživateli: dobrá otázka a bez navádění',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím sestavit výzkumnou otázku, napsat scénář krátkého rozhovoru bez návodných otázek a z odpovědí oddělit pozorování od domněnky.',
          'success_criteria' => 
          array (
            0 => 'Výzkumná otázka říká, co nevíme a jaké rozhodnutí odpověď ovlivní.',
            1 => 'Scénář rozhovoru obsahuje aspoň 5 otevřených otázek bez navádění.',
            2 => 'V zápisu odliším, co respondent řekl nebo udělal, od toho, co si myslím já.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_ux_research',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: návodná otázka',
            'teacher' => 'Položí otázku „Že se vám líbí naše nová stránka?“ a zeptá se, co je špatně.',
            'student' => 'Najdou navádění v otázce.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Výzkumná otázka',
            'teacher' => 'Ukáže vzorec: co nevíme → jaké rozhodnutí na tom závisí.',
            'student' => 'Napíší výzkumnou otázku k projektu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Scénář rozhovoru',
            'teacher' => 'Ukáže otevřené otázky („Popište, kdy naposledy…“).',
            'student' => 'Napíší 5–7 otázek a přepíší návodné.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 68,
            'phase' => 'Zkušební rozhovor',
            'teacher' => 'Hlídá roli tazatele, respondenta a zapisovatele.',
            'student' => 'Provedou krátký rozhovor ve trojici.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 68,
            'to' => 82,
            'phase' => 'Pozorování vs. domněnka',
            'teacher' => 'Ukáže dva sloupce zápisu.',
            'student' => 'Roztřídí zápis do sloupců.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne zásady rozhovoru.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš výzkumnou otázku pro svůj projekt ve tvaru „Nevíme…, proto se rozhodujeme o…“.',
            'output' => 'Jedna výzkumná otázka s rozhodnutím, které ovlivní.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Sestav scénář rozhovoru s 5–7 otevřenými otázkami a přepiš každou návodnou otázku na otevřenou.',
            'output' => 'Scénář s otázkami a poznámkou, kterou jsi přepsal(a).',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Proveď ve trojici zkušební rozhovor (tazatel, respondent, zapisovatel) podle scénáře, role se vystřídají.',
            'output' => 'Zápis jednoho rozhovoru bez jmen (respondent A).',
            'time' => '23 min',
          ),
          3 => 
          array (
            'text' => 'Roztřiď zápis do dvou sloupců: co respondent řekl nebo udělal a co si o tom myslíš ty.',
            'output' => 'Tabulka pozorování / domněnka s aspoň 4 řádky.',
            'time' => '14 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane seznam otevřených začátků otázek a vzor jedné výzkumné otázky.',
          'standard' => 'Výzkumná otázka, scénář, rozhovor a roztřídění zápisu.',
          'challenge' => 'Navrhne, jak by výzkumnou otázku ověřil(a) pozorováním chování místo rozhovoru.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel čte otázky, třída zvedne kartu „navádí / nenavádí“.',
            1 => 'Zapisovatel přečte jeden řádek a třída určí, jestli je to pozorování.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Výzkumná otázka',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Obecná.',
                2 => 'Co nevíme a jaké rozhodnutí.',
                3 => 'Ověřitelná a úzce zaměřená.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Scénář',
              'levels' => 
              array (
                0 => 'Návodné otázky.',
                1 => 'Mix.',
                2 => '5–7 otevřených otázek.',
                3 => 'Otázky na minulé chování, ne na názory.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Zápis',
              'levels' => 
              array (
                0 => 'Jen dojmy.',
                1 => 'Smíšený.',
                2 => 'Oddělené pozorování a domněnka.',
                3 => 'Domněnky převedené na hypotézy k ověření.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_ux_research',
          'competence_label' => 'Test použitelnosti a uspořádání obsahu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Která otázka v rozhovoru nenavádí?',
              'options' => 
              array (
                0 => 'Že je ta navigace přehledná?',
                1 => 'Popište, jak jste naposledy hledal(a) rozvrh na webu školy.',
                2 => 'Nechtěl(a) byste spíš větší tlačítko?',
              ),
              'correct' => 1,
              'explanation' => 'Otevřená otázka na skutečné chování.',
            ),
            1 => 
            array (
              'question' => 'Co je pozorování, ne domněnka?',
              'options' => 
              array (
                0 => 'Respondent třikrát klikl na logo a pak se vrátil zpět.',
                1 => 'Respondent je asi nešikovný.',
                2 => 'Stránka se mu určitě nelíbila.',
              ),
              'correct' => 0,
              'explanation' => 'Pozorování popisuje, co se stalo.',
            ),
            2 => 
            array (
              'question' => 'Co má obsahovat dobrá výzkumná otázka?',
              'options' => 
              array (
                0 => 'Co chceme uživatelům prodat.',
                1 => 'Počet respondentů.',
                2 => 'Co nevíme a jaké rozhodnutí odpověď ovlivní.',
              ),
              'correct' => 2,
              'explanation' => 'Výzkum slouží rozhodnutí.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přepiš tři návodné otázky z ankety, kterou znáš, na otevřené.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Respondenti jsou dobrovolníci ze třídy; zápis bez jmen (respondent A, B), žádné nahrávky.',
          1 => 'Neptáme se na citlivé osobní údaje (zdraví, rodina, adresa).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: otázky na názor („líbí se vám…“) místo na chování – veď k „popište, kdy naposledy…“.',
          1 => 'Otázka do třídy: Které rozhodnutí v projektu by odpověď změnila?',
          2 => 'Tempo: rozhovory ve trojicích drž na 7 minut na kolo, ať se vystřídají všichni. Chování pak ověří test použitelnosti (usability test) v L25.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vzor scénáře a role; trojice provádějí rozhovory podle úkolů 2–4.',
          1 => 'Plán B offline: celé bez počítače, zápis do sešitu.',
        ),
        'glossary' => 
        array (
          0 => 'usability-test',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      20 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 20 · Třídění karet: navigace podle myšlení uživatelů',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím připravit a vyhodnotit třídění karet a podle výsledku navrhnout strukturu navigace (mapu stránek) s názvy, kterým uživatelé rozumějí.',
          'success_criteria' => 
          array (
            0 => 'Připravím 15–20 karet s obsahem webu bez nápovědy v pojmenování.',
            1 => 'Z výsledků najdu skupiny, na kterých se shodla většina, a sporné karty.',
            2 => 'Navrhnu mapu stránek s nejvýš 6 položkami hlavní navigace.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_ux_research',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: kde je rozvrh?',
            'teacher' => 'Ukáže navigaci, kde je rozvrh pod „O nás“.',
            'student' => 'Hádají, kam by ho dali oni.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Příprava karet',
            'teacher' => 'Vysvětlí otevřené třídění a pravidla pojmenování karet.',
            'student' => 'Napíší 15–20 karet obsahu webu.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 50,
            'phase' => 'Třídění',
            'teacher' => 'Hlídá, aby autoři nenapovídali.',
            'student' => 'Třídí karty jiné dvojice a pojmenují skupiny.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 68,
            'phase' => 'Vyhodnocení',
            'teacher' => 'Ukáže tabulku shody karet ve skupinách.',
            'student' => 'Spočítají shodu a najdou sporné karty.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 68,
            'to' => 82,
            'phase' => 'Mapa stránek',
            'teacher' => 'Ukáže mapu stránek a limit hlavní navigace.',
            'student' => 'Nakreslí mapu stránek podle výsledků.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: názvy podle uživatelů.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš 15–20 karet s obsahem webu (např. rozvrh, jídelna, přijímačky) tak, aby název karty nenapovídal skupinu.',
            'output' => 'Sada karet připravená k třídění.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Roztřiď karty jiné dvojice do skupin a každou skupinu pojmenuj vlastními slovy.',
            'output' => 'Skupiny karet s názvy (fotka nebo tabulka).',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Vyhodnoť třídění tří skupin: u každé karty zapiš, kolikrát skončila ve stejné skupině, a označ sporné karty.',
            'output' => 'Tabulka shody a seznam sporných karet.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Nakresli mapu stránek s nejvýš 6 položkami hlavní navigace a pojmenuj je slovy z třídění.',
            'output' => 'Mapa stránek se zdůvodněním dvou názvů.',
            'time' => '14 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou sadu 15 karet a šablonu tabulky shody.',
          'standard' => 'Vlastní karty, třídění, vyhodnocení a mapa stránek.',
          'challenge' => 'Navrhne uzavřené třídění, kterým ověří sporné karty v nové struktuře.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola karet: najde soused kartu, jejíž název napovídá skupinu?',
            1 => 'Učitel ukáže spornou kartu a třída navrhne, jak s ní naložit.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Karty',
              'levels' => 
              array (
                0 => 'Méně než 10.',
                1 => 'Názvy napovídají.',
                2 => '15–20 neutrálních karet.',
                3 => 'Karty pokrývají celý obsah webu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Vyhodnocení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen dojem.',
                2 => 'Tabulka shody a sporné karty.',
                3 => 'Návrh, jak sporné karty ověřit.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Mapa stránek',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Přes 6 položek.',
                2 => 'Nejvýš 6 položek s názvy z třídění.',
                3 => 'Zdůvodněná podle dat.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_ux_research',
          'competence_label' => 'Test použitelnosti a uspořádání obsahu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co zjišťuje otevřené třídění karet?',
              'options' => 
              array (
                0 => 'Které barvy mají uživatelé rádi.',
                1 => 'Jak rychle se stránka načte.',
                2 => 'Jak si uživatelé sami seskupí a pojmenují obsah.',
              ),
              'correct' => 2,
              'explanation' => 'Ukazuje mentální model uživatelů.',
            ),
            1 => 
            array (
              'question' => 'Karta „Rozvrh“ skončila u 3 skupin pokaždé jinde. Co uděláš?',
              'options' => 
              array (
                0 => 'Označím ji jako spornou a ověřím umístění dalším testem.',
                1 => 'Smažu ji z webu.',
                2 => 'Dám ji tam, kde se líbí mně.',
              ),
              'correct' => 0,
              'explanation' => 'Sporné karty potřebují další ověření.',
            ),
            2 => 
            array (
              'question' => 'Proč omezit hlavní navigaci zhruba na 6 položek?',
              'options' => 
              array (
                0 => 'Protože víc se nevejde do HTML.',
                1 => 'Protože méně voleb se rychleji prohlédne a lépe zapamatuje.',
                2 => 'Protože to vyžaduje zákon.',
              ),
              'correct' => 1,
              'explanation' => 'Přehlednost a rychlé rozhodnutí.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: nakresli mapu stránek webu, který často používáš, a označ jednu položku, kterou bys přejmenoval(a).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Třídění je anonymní; výsledky zapisujeme bez jmen.',
          1 => 'Obsah karet je vymyšlený nebo z veřejného webu školy, bez osobních údajů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: autoři karet během třídění napovídají – posaď je stranou.',
          1 => 'Otázka do třídy: Které slovo používají uživatelé a které my?',
          2 => 'Tempo: vyhodnocení stačí ze tří třídění; víc dat dává smysl až doma.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá papírové kartičky a tabulku shody; skupiny plní úkoly 1–4.',
          1 => 'Plán B offline: celé s papírovými kartičkami.',
        ),
        'glossary' => 
        array (
          0 => 'sitemap',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      21 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 21 · Zátěžový test komponenty: dlouhý text, úzký displej',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím otestovat komponentu extrémními daty (dlouhý text, chybějící obrázek, zvětšené písmo, 320 px) a upravit CSS tak, aby se nerozbila.',
          'success_criteria' => 
          array (
            0 => 'Komponentu otestuji aspoň v 5 zátěžových situacích.',
            1 => 'Opravy dělám pružnými hodnotami (min/max, clamp, flex-wrap), ne pevnou výškou.',
            2 => 'Komponenta funguje při zvětšení písma na 200 %.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_css_responsive',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'w2_design_system',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: krásná karta, ošklivá data',
            'teacher' => 'Vloží do karty nadpis o 90 znacích a německé slovo.',
            'student' => 'Sledují, jak se karta rozpadne.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Zátěžové situace',
            'teacher' => 'Představí 6 situací: dlouhý text, prázdný text, bez obrázku, 320 px, 200 % písma, mnoho položek.',
            'student' => 'Připraví testovací data pro svou komponentu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 50,
            'phase' => 'Test a zápis',
            'teacher' => 'Obchází a ptá se na příčinu rozbití.',
            'student' => 'Otestují komponentu a zapíší, co se rozbilo.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 75,
            'phase' => 'Pružné opravy',
            'teacher' => 'Ukáže min-height, clamp(), flex-wrap a zalamování slov.',
            'student' => 'Opraví nalezené problémy.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 75,
            'to' => 83,
            'phase' => 'Opakovaný test',
            'teacher' => 'Zadá opakování testu po opravě.',
            'student' => 'Ověří opravy stejnými daty.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 83,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: návrh musí přežít skutečná data.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Připrav testovací data pro svou komponentu pro 6 zátěžových situací (dlouhý text, prázdný text, bez obrázku, 320 px, 200 % písma, mnoho položek).',
            'output' => 'Tabulka situací s konkrétními daty.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Otestuj komponentu ve všech situacích a zapiš, co se rozbilo a proč.',
            'output' => 'Tabulka situace → výsledek → příčina.',
            'time' => '25 min',
          ),
          2 => 
          array (
            'text' => 'Oprav nalezené problémy pružnými hodnotami (min-height místo height, clamp() pro písmo, flex-wrap, zalamování dlouhých slov).',
            'output' => 'Upravené CSS s komentářem u každé opravy.',
            'time' => '22 min',
          ),
          3 => 
          array (
            'text' => 'Zopakuj test se stejnými daty a zapiš výsledek po opravě.',
            'output' => 'Doplněná tabulka se sloupcem „po opravě“.',
            'time' => '8 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane připravenou kartu a testovací data; opravuje dvě situace podle nápovědy.',
          'standard' => 'Vlastní data, test, opravy a opakovaný test.',
          'challenge' => 'Přidá test s pravým-doleva textem nebo dlouhou URL a navrhne pravidlo do design systému.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže rozbitou kartu, třída určí příčinu (pevná výška, nezalomené slovo).',
            1 => 'Dvojice si vymění data a otestují komponentu souseda.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Testovací data',
              'levels' => 
              array (
                0 => 'Žádná.',
                1 => '1–2 situace.',
                2 => '5–6 situací s konkrétními daty.',
                3 => 'Data z reálného obsahu webu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Opravy',
              'levels' => 
              array (
                0 => 'Pevné hodnoty.',
                1 => 'Část pružně.',
                2 => 'Všechny opravy pružnými hodnotami.',
                3 => 'Pravidlo zapsané do design systému.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření',
              'levels' => 
              array (
                0 => 'Neověřeno.',
                1 => 'Jen vizuálně.',
                2 => 'Opakovaný test stejnými daty.',
                3 => 'Test i při 200 % písma a 320 px.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_css_responsive',
          'competence_label' => 'Responzivní rozvržení v CSS',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Karta s height: 200px se při dlouhém nadpisu rozbije. Co je lepší oprava?',
              'options' => 
              array (
                0 => 'Zmenšit písmo nadpisu na 10 px.',
                1 => 'Skrýt přetečení overflow: hidden.',
                2 => 'Použít min-height, aby karta mohla růst.',
              ),
              'correct' => 2,
              'explanation' => 'Pružná výška přizpůsobí kartu obsahu.',
            ),
            1 => 
            array (
              'question' => 'Proč testovat komponentu při zvětšení písma na 200 %?',
              'options' => 
              array (
                0 => 'Lidé se slabším zrakem si písmo zvětšují a obsah se nesmí ztratit.',
                1 => 'Protože to vyžaduje prohlížeč.',
                2 => 'Aby se ověřila rychlost.',
              ),
              'correct' => 0,
              'explanation' => 'Zvětšení textu je běžná potřeba uživatelů.',
            ),
            2 => 
            array (
              'question' => 'Co dělá v CSS funkce clamp(1rem, 2vw, 1.5rem)?',
              'options' => 
              array (
                0 => 'Nastaví barvu podle šířky.',
                1 => 'Hodnota roste s šířkou okna, ale nikdy není menší než 1rem ani větší než 1.5rem.',
                2 => 'Ořízne text na jeden řádek.',
              ),
              'correct' => 1,
              'explanation' => 'Pružná hodnota s dolní a horní mezí.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi na webu jednu komponentu, která se rozbije při dlouhém textu, a popiš příčinu.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Testovací data jsou vymyšlená; žádná skutečná jména ani kontakty.',
          1 => 'Testujeme vlastní komponenty, ne cizí weby.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: oprava skrytím přetečení (overflow: hidden) – obsah se tím ztratí.',
          1 => 'Otázka do třídy: Jaká data přijdou od skutečných uživatelů, na která návrh nepočítal?',
          2 => 'Tempo: opravy jsou jádro – když test trvá dlouho, opravte aspoň tři situace.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá kartu a testovací data; žáci plní úkoly 1–4 v editoru.',
          1 => 'Plán B offline: zátěžový test na papírových maketách s vystřiženými texty.',
        ),
        'glossary' => 
        array (
          0 => 'flexbox',
          1 => 'breakpoint',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      22 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 22 · Formulář, který pomáhá z chyby ven',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout stavy formuláře pro chybu, ztrátu spojení a nedokončené vyplnění tak, aby uživatel neztratil data a věděl, jak pokračovat.',
          'success_criteria' => 
          array (
            0 => 'Chyba se ukáže u pole s návodem na opravu a vyplněná data zůstanou.',
            1 => 'Navrhnu stav pro ztrátu spojení s možností zkusit znovu.',
            2 => 'Kontrolu pole provádím po opuštění pole, ne při každém stisku klávesy.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_forms_states',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: smazaný formulář',
            'teacher' => 'Ukáže formulář, který po chybě smaže všechna pole.',
            'student' => 'Popíší, jak by reagovali jako uživatelé.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Mapa stavů',
            'teacher' => 'Ukáže stavový diagram formuláře: prázdný, vyplňuje, chyba, odesílá, hotovo, selhalo.',
            'student' => 'Nakreslí stavy svého formuláře.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Chyba u pole',
            'teacher' => 'Vysvětlí validaci po opuštění pole a souhrn chyb nahoře.',
            'student' => 'Navrhnou hlášky a umístění chyb.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Ztráta spojení',
            'teacher' => 'Ukáže stav „Nepodařilo se odeslat – data máte uložená, zkusit znovu“.',
            'student' => 'Navrhnou stav selhání a obnovy.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Průchod scénářem',
            'teacher' => 'Zadá scénáře chyb pro test.',
            'student' => 'Projdou návrh souseda třemi scénáři.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: chyba nesmí trestat uživatele.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Nakresli stavový diagram formuláře přihlášky se stavy prázdný, vyplňuje, chyba, odesílá, hotovo a selhalo.',
            'output' => 'Diagram šesti stavů s přechody.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni chybové hlášky ke třem polím a souhrn chyb nad formulářem s odkazy na chybná pole.',
            'output' => 'Návrh chyb u polí a souhrnu.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni stav „odeslání selhalo“, ve kterém zůstanou data a je tlačítko pro nový pokus.',
            'output' => 'Obrazovka selhání s textem a akcí.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Projdi návrh spolužáka třemi scénáři (překlep v e-mailu, výpadek spojení, odchod a návrat) a zapiš, kde by uživatel ztratil data.',
            'output' => 'Zápis tří scénářů s nálezy.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane předkreslený diagram se šesti stavy; doplňuje texty a přechody.',
          'standard' => 'Diagram, hlášky, stav selhání a test scénáři.',
          'challenge' => 'Navrhne i průběžné ukládání konceptu a popíše, kdy a jak se uživateli oznámí.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže hlášku a třída řekne, jestli obsahuje opravu.',
            1 => 'Dvojice: kde v návrhu se ztratí data?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Stavy formuláře',
              'levels' => 
              array (
                0 => 'Jen výchozí.',
                1 => '2–3 stavy.',
                2 => 'Všech šest stavů s přechody.',
                3 => 'Stavy pokrývají i návrat uživatele.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Chybové hlášky',
              'levels' => 
              array (
                0 => 'Obecné.',
                1 => 'U pole bez opravy.',
                2 => 'U pole s opravou a souhrn nahoře.',
                3 => 'Souhrn odkazuje na pole a čte ho čtečka.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obnova po selhání',
              'levels' => 
              array (
                0 => 'Data se ztratí.',
                1 => 'Data zůstanou, bez akce.',
                2 => 'Data zůstanou a lze zkusit znovu.',
                3 => 'Průběžné ukládání konceptu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_forms_states',
          'competence_label' => 'Formuláře a stavy',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co se má stát s vyplněnými daty, když odeslání formuláře selže kvůli výpadku spojení?',
              'options' => 
              array (
                0 => 'Data zůstanou a uživatel může zkusit odeslat znovu.',
                1 => 'Formulář se vymaže, aby začal znovu.',
                2 => 'Stránka se přesměruje na úvod.',
              ),
              'correct' => 0,
              'explanation' => 'Selhání nesmí trestat uživatele ztrátou dat.',
            ),
            1 => 
            array (
              'question' => 'Kdy je nejlepší kontrolovat formát e-mailu v poli?',
              'options' => 
              array (
                0 => 'Při každém stisku klávesy hned od prvního písmene.',
                1 => 'Až po opuštění pole nebo při odeslání.',
                2 => 'Nikdy.',
              ),
              'correct' => 1,
              'explanation' => 'Hláška při psaní ruší; po opuštění pole pomůže.',
            ),
            2 => 
            array (
              'question' => 'K čemu je souhrn chyb nad formulářem?',
              'options' => 
              array (
                0 => 'Nahrazuje hlášky u polí.',
                1 => 'Je jen dekorace.',
                2 => 'Řekne, kolik a kde je chyb, a odkazuje na chybná pole.',
              ),
              'correct' => 2,
              'explanation' => 'Pomůže hlavně u dlouhých formulářů a čtečkám.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyzkoušej formulář na webu, který znáš, se záměrnou chybou a zapiš, jestli ti zůstala data.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Formuláře testujeme s vymyšlenými daty (jan@example.com), nikdy se skutečnými údaji.',
          1 => 'Na cizích webech nic neodesíláme; testujeme jen chování před odesláním.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: validace při každém stisku klávesy – uživatel vidí chybu dřív, než dopíše.',
          1 => 'Otázka do třídy: Co uživatel ztratí, když formulář po chybě vymaže pole?',
          2 => 'Tempo: diagram stavů zkrať na 10 minut, pokud ho třída zvládá; stav selhání je nejnovější téma.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá šablonu diagramu a scénáře; žáci plní úkoly 1–4 na papíře.',
          1 => 'Plán B offline: stavy formuláře jako papírové obrazovky.',
        ),
        'glossary' => 
        array (
          0 => 'validation',
          1 => 'label',
        ),
        '_file' => 'lesson_content_v76_2a_a.php',
      ),
      23 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 23 · Přístupné interakce: klávesnice, fokus a čtečka obrazovky',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím ověřit interaktivní prvky (menu, dialog, záložky) klávesnicí a čtečkou obrazovky a opravit nejčastější chyby: chybějící fokus, past fokusu a tlačítko z prvku div.',
          'success_criteria' => 
          array (
            0 => 'Projdu prvek jen klávesnicí (Tab, Shift+Tab, Enter, mezerník, Esc) a zapíšu, co nefunguje.',
            1 => 'Pro tlačítko použiji element button, ne div s kliknutím.',
            2 => 'Dialog vrátí po zavření fokus na prvek, který ho otevřel.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_a11y_audit',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: myš schovaná',
            'teacher' => 'Požádá třídu, aby na minutu schovala myš a otevřela menu stránky.',
            'student' => 'Zkusí ovládat ukázkový web jen klávesnicí.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Klávesové vzory',
            'teacher' => 'Ukáže očekávané ovládání menu, dialogu a záložek.',
            'student' => 'Zapíší si tabulku kláves a očekávaného chování.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Test klávesnicí',
            'teacher' => 'Rozdá ukázkovou stránku se třemi chybnými prvky.',
            'student' => 'Otestují prvky a zapíší nálezy.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 60,
            'phase' => 'Čtečka obrazovky',
            'teacher' => 'Předvede, co přečte čtečka u div a u button.',
            'student' => 'Poslechnou si rozdíl a doplní nálezy.',
            'form' => 'frontálně',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 82,
            'phase' => 'Opravy',
            'teacher' => 'Obchází a hlídá sémantické řešení před ARIA.',
            'student' => 'Opraví tři nalezené chyby a test zopakují.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: nejdřív správný HTML prvek.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Sepiš tabulku: menu, dialog a záložky – které klávesy je ovládají a co se má stát.',
            'output' => 'Tabulka tří prvků s klávesami a očekáváním.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Otestuj ukázkovou stránku jen klávesnicí a zapiš nálezy: kde zmizí fokus, kde uvízne a co nejde spustit.',
            'output' => 'Seznam nálezů s místem a klávesou.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Oprav tlačítko vytvořené z prvku div tak, že použiješ element button, a ověř ho klávesou Enter i mezerníkem.',
            'output' => 'Opravený kód a výsledek testu.',
            'time' => '12 min',
          ),
          3 => 
          array (
            'text' => 'Oprav dialog: Esc ho zavře, fokus zůstane uvnitř, dokud je otevřený, a po zavření se vrátí na tlačítko, které ho otevřelo.',
            'output' => 'Dialog, který projde testem klávesnicí.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku kláves vyplněnou a opravuje jen tlačítko z div.',
          'standard' => 'Test klávesnicí, nálezy a opravy tlačítka i dialogu.',
          'challenge' => 'Ověří i nastavení reduced motion u animace dialogu a doplní pravidlo do design systému.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel stiskne Tab na promítnuté stránce a třída hlásí, kde je fokus.',
            1 => 'Dvojice: poslechni si čtečku – zní prvek jako tlačítko?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Test klávesnicí',
              'levels' => 
              array (
                0 => 'Netestováno.',
                1 => 'Jen Tab.',
                2 => 'Tab, Shift+Tab, Enter, mezerník, Esc.',
                3 => 'Nálezy s doporučením.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Sémantika',
              'levels' => 
              array (
                0 => 'div s kliknutím.',
                1 => 'div s ARIA.',
                2 => 'Správný HTML prvek.',
                3 => 'Zdůvodní, proč sémantika před ARIA.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Fokus v dialogu',
              'levels' => 
              array (
                0 => 'Fokus se ztratí.',
                1 => 'Esc funguje.',
                2 => 'Fokus uvnitř a návrat po zavření.',
                3 => 'Ověřeno i čtečkou obrazovky.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_a11y_audit',
          'competence_label' => 'Audit přístupnosti',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jak správně vytvoříš klikací tlačítko?',
              'options' => 
              array (
                0 => 'div s obsluhou kliknutí.',
                1 => 'Element button.',
                2 => 'Obrázek s odkazem #.',
              ),
              'correct' => 1,
              'explanation' => 'button umí fokus, Enter, mezerník i roli pro čtečku.',
            ),
            1 => 
            array (
              'question' => 'Kam se má vrátit fokus po zavření dialogu?',
              'options' => 
              array (
                0 => 'Na začátek stránky.',
                1 => 'Nikam, stačí zavřít.',
                2 => 'Na prvek, který dialog otevřel.',
              ),
              'correct' => 2,
              'explanation' => 'Uživatel pokračuje tam, kde skončil.',
            ),
            2 => 
            array (
              'question' => 'Co je „past fokusu“ (focus trap) jako chyba?',
              'options' => 
              array (
                0 => 'Fokus uvízne v prvku a klávesnicí se z něj nedá dostat.',
                1 => 'Fokus je moc výrazný.',
                2 => 'Stránka nemá žádné odkazy.',
              ),
              'correct' => 0,
              'explanation' => 'Mimo otevřený dialog nesmí fokus uvíznout.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: projdi web své oblíbené služby jen klávesnicí a zapiš jednu past nebo ztracený fokus.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Testujeme ukázkové a vlastní stránky; na cizích webech nic neodesíláme ani nepřihlašujeme.',
          1 => 'Čtečku obrazovky používáme jen se zapnutými sluchátky nebo tiše, ať nerušíme ostatní.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: oprava pomocí ARIA rolí místo správného prvku – „nejlepší ARIA je žádná ARIA“.',
          1 => 'Otázka do třídy: Kdo všechno ovládá web jinak než myší (screen reader, klávesnice, hlas)?',
          2 => 'Tempo: dialog je nejnáročnější – kdo nestihne, odevzdá opravu tlačítka a test.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci testují ukázkovou stránku klávesnicí podle tabulky a odevzdají nálezy (úkoly 1–2).',
          1 => 'Plán B offline: na papíře rozkreslí pořadí fokusu na vytištěné stránce.',
        ),
        'glossary' => 
        array (
          0 => 'screen-reader',
          1 => 'reduced-motion',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
      24 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 24 · Od návrhových tokenů ke kódu: proměnné CSS',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést tokeny design systému (barvy, mezery, písmo) do proměnných CSS ve dvou vrstvách – základní hodnoty a sémantické role – a přepnout světlý a tmavý režim jen změnou rolí.',
          'success_criteria' => 
          array (
            0 => 'Základní tokeny mají neutrální jména (např. --blue-600), sémantické role jména podle účelu (--color-action).',
            1 => 'Komponenty používají jen sémantické role, ne základní hodnoty.',
            2 => 'Tmavý režim vznikne přepsáním rolí v jednom bloku.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_design_system',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: přebarvi tlačítko',
            'teacher' => 'Ukáže web, kde je barva tlačítka zapsaná na 14 místech.',
            'student' => 'Odhadnou, kolik práce je změna barvy.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Dvě vrstvy tokenů',
            'teacher' => 'Vysvětlí základní hodnoty a sémantické role.',
            'student' => 'Roztřídí tokeny svého návrhu do dvou vrstev.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Proměnné CSS',
            'teacher' => 'Předvede :root, var() a pojmenování.',
            'student' => 'Zapíší tokeny jako proměnné CSS.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Komponenty na role',
            'teacher' => 'Ukáže přepis tlačítka a karty na role.',
            'student' => 'Přepíší dvě komponenty na sémantické role.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 82,
            'phase' => 'Tmavý režim',
            'teacher' => 'Ukáže přepsání rolí v prefers-color-scheme.',
            'student' => 'Přidají tmavý režim a ověří kontrast.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: role chrání komponenty před změnou.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Roztřiď tokeny svého návrhu do dvou vrstev: základní hodnoty (odstíny, škála mezer) a sémantické role (akce, text, povrch, chyba).',
            'output' => 'Tabulka dvou vrstev s aspoň 6 rolemi.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zapiš obě vrstvy jako proměnné CSS v :root a role odkaž na základní hodnoty přes var().',
            'output' => 'Blok :root se základními hodnotami a rolemi.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Přepiš tlačítko a kartu tak, aby používaly jen sémantické role a žádnou základní hodnotu ani HEX kód.',
            'output' => 'CSS dvou komponent bez přímých barev.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Přidej tmavý režim přepsáním rolí v bloku @media (prefers-color-scheme: dark) a ověř kontrast textu a tlačítka.',
            'output' => 'Tmavý režim a změřený kontrast dvou dvojic.',
            'time' => '17 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou vrstvu základních hodnot; doplňuje role a přepisuje jednu komponentu.',
          'standard' => 'Dvě vrstvy, proměnné CSS, dvě komponenty a tmavý režim.',
          'challenge' => 'Přidá přepínač režimu atributem data-theme a pravidla zapíše jako krátkou dokumentaci pro předání (handoff).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel napíše --blue-600 a --color-action; třída řekne, který token patří do komponenty.',
            1 => 'Dvojice: změň jednu roli a ukaž, co se přebarvilo.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Vrstvy tokenů',
              'levels' => 
              array (
                0 => 'Jedna vrstva.',
                1 => 'Role pojmenované podle barvy.',
                2 => 'Základní hodnoty a sémantické role.',
                3 => 'Role pokrývají stavy (hover, chyba).',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Komponenty',
              'levels' => 
              array (
                0 => 'Přímé HEX kódy.',
                1 => 'Mix rolí a hodnot.',
                2 => 'Jen role.',
                3 => 'Bez jediné výjimky a s komentářem.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Tmavý režim',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Přepisuje komponenty.',
                2 => 'Přepisuje jen role.',
                3 => 'Kontrast ověřen v obou režimech.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_design_system',
          'competence_label' => 'Design systém',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který token má používat tlačítko v CSS?',
              'options' => 
              array (
                0 => '--blue-600',
                1 => '--color-action',
                2 => '#1D4ED8',
              ),
              'correct' => 1,
              'explanation' => 'Komponenta používá roli, ne konkrétní odstín.',
            ),
            1 => 
            array (
              'question' => 'Jak nejjednodušeji vznikne tmavý režim při dvou vrstvách tokenů?',
              'options' => 
              array (
                0 => 'Přepíšu hodnoty sémantických rolí v jednom bloku.',
                1 => 'Napíšu všechny komponenty znovu.',
                2 => 'Invertuji barvy filtrem.',
              ),
              'correct' => 0,
              'explanation' => 'Komponenty zůstanou, mění se jen role.',
            ),
            2 => 
            array (
              'question' => 'Co zapisuje var(--space-16)?',
              'options' => 
              array (
                0 => 'Novou proměnnou s hodnotou 16.',
                1 => 'Komentář pro vývojáře.',
                2 => 'Použití hodnoty proměnné --space-16.',
              ),
              'correct' => 2,
              'explanation' => 'var() čte hodnotu vlastní vlastnosti CSS.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přidej do své sady jednu roli pro stav chyby a použij ji ve formuláři.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Kód píšeme vlastní; ukázky z dokumentace jen s uvedením zdroje.',
          1 => 'Barvy tmavého režimu ověřujeme kontrastem – přístupnost má přednost před vzhledem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: role pojmenované podle barvy (--color-blue) – po změně odstínu lžou. Návrhový token (design token) nese účel.',
          1 => 'Otázka do třídy: Co se musí změnit, když škola změní hlavní barvu?',
          2 => 'Tempo: tmavý režim je pro rychlejší; standard je dvě vrstvy a jedna komponenta.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá kostru CSS s :root a zadání; žáci plní úkoly 1–3, tmavý režim příště.',
          1 => 'Plán B offline: tabulka dvou vrstev tokenů na papíře a přepis komponenty ručně.',
        ),
        'glossary' => 
        array (
          0 => 'design-token',
          1 => 'handoff',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
      25 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 25 · Druhé kolo testu použitelnosti: úspěšnost a závažnost',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím připravit a moderovat test použitelnosti se třemi úkoly, změřit úspěšnost a čas a nálezy seřadit podle závažnosti do plánu oprav.',
          'success_criteria' => 
          array (
            0 => 'Úkoly jsou napsané jako situace, ne jako návod („Chceš se přihlásit na kurz…“, ne „Klikni na Přihláška“).',
            1 => 'U každého úkolu zapíšu úspěch, čas a místo, kde uživatel zaváhal.',
            2 => 'Nálezy seřadím podle závažnosti (blokuje / zdržuje / drobnost) a četnosti.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_ux_research',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: co se změnilo od prvního kola',
            'teacher' => 'Připomene nálezy z prvního testu a co se opravilo.',
            'student' => 'Zapíší, co chtějí ověřit ve druhém kole.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Úkoly jako situace',
            'teacher' => 'Ukáže rozdíl mezi návodem a situací.',
            'student' => 'Napíší tři úkoly jako situace.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 60,
            'phase' => 'Testování',
            'teacher' => 'Hlídá roli moderátora (nenapovídá) a zapisovatele.',
            'student' => 'Otestují návrh na dvou spolužácích z jiné skupiny.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 60,
            'to' => 75,
            'phase' => 'Vyhodnocení',
            'teacher' => 'Ukáže tabulku úspěšnosti a času.',
            'student' => 'Spočítají úspěšnost a seřadí nálezy.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 75,
            'to' => 83,
            'phase' => 'Plán oprav',
            'teacher' => 'Připomene řazení podle závažnosti a četnosti.',
            'student' => 'Vyberou tři opravy.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 83,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: měříme chování, ne názory.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš tři úkoly testu jako situace z života uživatele, bez slov, která jsou na tlačítkách.',
            'output' => 'Tři úkoly a u každého, co znamená „splněno“.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Moderuj test se dvěma spolužáky z jiné skupiny: nenapovídej, jen se ptej „co teď čekáš?“.',
            'output' => 'Zápis dvou testů bez jmen (účastník A, B).',
            'time' => '30 min',
          ),
          2 => 
          array (
            'text' => 'Spočítej úspěšnost každého úkolu (kolik z kolika splnilo) a průměrný čas a zapiš místa zaváhání.',
            'output' => 'Tabulka úkol → úspěšnost → čas → zaváhání.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Seřaď nálezy podle závažnosti a četnosti a vyber tři opravy do plánu.',
            'output' => 'Plán tří oprav se závažností.',
            'time' => '10 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzor úkolů jako situací a připravenou tabulku vyhodnocení.',
          'standard' => 'Vlastní úkoly, dva testy, vyhodnocení a plán oprav.',
          'challenge' => 'Porovná výsledky prvního a druhého kola a zapíše, jestli opravy pomohly.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte úkol, třída zvedne kartu „situace / návod“.',
            1 => 'Kontrola moderátora: napověděl? (zapisovatel zaškrtne)',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Úkoly',
              'levels' => 
              array (
                0 => 'Návody.',
                1 => 'Mix.',
                2 => 'Tři situace s kritériem splnění.',
                3 => 'Úkoly míří na sporná místa z prvního kola.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Moderování',
              'levels' => 
              array (
                0 => 'Napovídá.',
                1 => 'Občas napoví.',
                2 => 'Nenapovídá, ptá se na očekávání.',
                3 => 'Zapíše i tiché zaváhání.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Vyhodnocení',
              'levels' => 
              array (
                0 => 'Jen dojmy.',
                1 => 'Úspěšnost bez řazení.',
                2 => 'Úspěšnost, čas a řazení podle závažnosti.',
                3 => 'Srovnání s prvním kolem.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_ux_research',
          'competence_label' => 'Test použitelnosti a uspořádání obsahu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který úkol do testu použitelnosti je napsaný správně?',
              'options' => 
              array (
                0 => 'Klikni na tlačítko Přihláška a vyplň formulář.',
                1 => 'Najdi menu a otevři stránku Kurzy.',
                2 => 'Chceš chodit na kurz kreslení ve středu. Zjisti, jestli je volné místo, a přihlas se.',
              ),
              'correct' => 2,
              'explanation' => 'Situace neprozrazuje cestu ani slova z rozhraní.',
            ),
            1 => 
            array (
              'question' => 'Účastník se zasekne. Co udělá moderátor?',
              'options' => 
              array (
                0 => 'Ukáže mu správné tlačítko.',
                1 => 'Zeptá se „Co teď čekáš, že se stane?“ a zapíše odpověď.',
                2 => 'Ukončí test.',
              ),
              'correct' => 1,
              'explanation' => 'Moderátor nenapovídá; zjišťuje očekávání.',
            ),
            2 => 
            array (
              'question' => 'Úkol splnili 2 ze 4 účastníků. Jaká je úspěšnost?',
              'options' => 
              array (
                0 => '50 %',
                1 => '25 %',
                2 => '2 %',
              ),
              'correct' => 0,
              'explanation' => '2 / 4 = 0,5 = 50 %.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: proveď jeden test se členem rodiny podle svých úkolů a doplň výsledek do tabulky.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Účast v testu je dobrovolná; zápis bez jmen a bez nahrávání obrazovky či obličeje.',
          1 => 'Testujeme návrh, ne člověka – „chyba“ je vždy chyba rozhraní.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: moderátor napovídá, když je ticho – nauč žáky vydržet 10 sekund.',
          1 => 'Otázka do třídy: Co nám řekne usability test, co nám neřekne anketa?',
          2 => 'Tempo: dva testy po 12 minutách; vyhodnocení nesmí vypadnout.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vzor úkolů a tabulku; skupiny provádějí testy podle úkolů 1–4.',
          1 => 'Plán B offline: test na papírovém prototypu (obrazovky na kartičkách).',
        ),
        'glossary' => 
        array (
          0 => 'usability-test',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
      26 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 26 · Klinika design QA: hlášení chyb, která se dají opravit',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vést kliniku kontroly kvality: projít stránku podle kontrolního seznamu, zapsat chyby do sdílené tabulky se závažností a krokem k reprodukci a ověřit opravu.',
          'success_criteria' => 
          array (
            0 => 'Každé hlášení má kroky k reprodukci, očekávání, skutečnost a závažnost.',
            1 => 'Duplicitní hlášení sloučím a ponechám jedno s odkazem.',
            2 => 'Opravu ověřím stejnými kroky a hlášení uzavřu jen s důkazem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'g2_handoff',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'w2_a11y_audit',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: hlášení „nefunguje to“',
            'teacher' => 'Ukáže tři špatná hlášení a jedno dobré.',
            'student' => 'Určí, co dobrému hlášení dává sílu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 20,
            'phase' => 'Role kliniky',
            'teacher' => 'Rozdělí role: testeři, třídič hlášení, opravář.',
            'student' => 'Domluví se na tabulce hlášení.',
            'form' => 've skupinách',
          ),
          2 => 
          array (
            'from' => 20,
            'to' => 45,
            'phase' => 'Kontrola a hlášení',
            'teacher' => 'Hlídá kroky k reprodukci.',
            'student' => 'Testeři zapisují chyby, třídič slučuje duplicity.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 70,
            'phase' => 'Opravy',
            'teacher' => 'Pomáhá s prioritou podle závažnosti.',
            'student' => 'Opravář opraví tři nejzávažnější chyby.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Ověření a uzavření',
            'teacher' => 'Vyžaduje důkaz u uzavřeného hlášení.',
            'student' => 'Testeři ověří opravy a hlášení uzavřou.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne životní cyklus chyby.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Jako tester zapiš do sdílené tabulky aspoň 4 chyby s kroky k reprodukci, očekáváním, skutečností a závažností.',
            'output' => 'Čtyři úplná hlášení v tabulce skupiny.',
            'time' => '25 min',
          ),
          1 => 
          array (
            'text' => 'Jako třídič projdi tabulku, slouč duplicitní hlášení a seřaď je podle závažnosti.',
            'output' => 'Seřazená tabulka bez duplicit.',
            'time' => '10 min',
          ),
          2 => 
          array (
            'text' => 'Oprav tři nejzávažnější chyby a ke každé zapiš, co jsi změnil(a).',
            'output' => 'Tři opravy s popisem změny.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Ověř opravy stejnými kroky k reprodukci a uzavři hlášení jen se snímkem nebo popisem výsledku.',
            'output' => 'Uzavřená hlášení s důkazem.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu hlášení s vyplněným příkladem a roli testera.',
          'standard' => 'Hlášení, třídění, opravy a ověření ve skupině.',
          'challenge' => 'Navrhne pravidlo do design systému, které by nejčastější chybě předešlo.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte hlášení, třída zkusí chybu zopakovat jen podle kroků.',
            1 => 'Kontrola uzavření: má každé uzavřené hlášení důkaz?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Hlášení',
              'levels' => 
              array (
                0 => '„Nefunguje to.“',
                1 => 'Bez kroků k reprodukci.',
                2 => 'Kroky, očekávání, skutečnost, závažnost.',
                3 => 'Doplněno o prostředí (šířka, prohlížeč).',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Třídění',
              'levels' => 
              array (
                0 => 'Bez pořadí.',
                1 => 'Duplicity zůstaly.',
                2 => 'Sloučeno a seřazeno podle závažnosti.',
                3 => 'Zdůvodněné pořadí.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření opravy',
              'levels' => 
              array (
                0 => 'Neověřeno.',
                1 => 'Uzavřeno bez důkazu.',
                2 => 'Ověřeno stejnými kroky s důkazem.',
                3 => 'Ověřeny i související části.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'g2_handoff',
          'competence_label' => 'Předání návrhu a případová studie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co musí mít hlášení chyby, aby ji šlo opravit bez doptávání?',
              'options' => 
              array (
                0 => 'Jméno autora chyby.',
                1 => 'Kroky k reprodukci, očekávání, skutečnost a závažnost.',
                2 => 'Jen slovo „urgentní“.',
              ),
              'correct' => 1,
              'explanation' => 'Opravář musí chybu zopakovat a vědět, co je správně.',
            ),
            1 => 
            array (
              'question' => 'Kdy smíš hlášení uzavřít?',
              'options' => 
              array (
                0 => 'Hned, jak opravář řekne, že je hotovo.',
                1 => 'Když se o chybě přestane mluvit.',
                2 => 'Až po ověření stejnými kroky s důkazem.',
              ),
              'correct' => 2,
              'explanation' => 'Uzavření bez ověření je jen naděje.',
            ),
            2 => 
            array (
              'question' => 'Dva testeři nahlásili stejnou chybu. Co udělá třídič?',
              'options' => 
              array (
                0 => 'Hlášení sloučí a ponechá jedno s odkazem na druhé.',
                1 => 'Smaže obě.',
                2 => 'Nechá obě, ať je chyba vidět víc.',
              ),
              'correct' => 0,
              'explanation' => 'Duplicity rozptylují práci.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš jedno úplné hlášení chyby k aplikaci, kterou používáš, a zkus ji zopakovat podle svých kroků.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Hlášení se týkají produktu, ne lidí; bez jmen a bez obviňování.',
          1 => 'Snímky obrazovky bez osobních údajů (vymyšlená data).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: hlášení uzavřené bez ověření – trvej na důkazu.',
          1 => 'Otázka do třídy: Kterou chybu by uživatel poznal jako první a kterou nikdy?',
          2 => 'Tempo: role se mohou po 20 minutách vyměnit, aby si každý zkusil hlášení i opravu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá šablonu hlášení a role; skupiny pracují podle úkolů 1–4.',
          1 => 'Plán B offline: kontrola vytištěné stránky a hlášení na papírových lístcích.',
        ),
        'glossary' => 
        array (
          0 => 'handoff',
          1 => 'focus',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
      27 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 27 · Produktový sprint: architektura, přístupnost a předání',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v týmu za 90 minut navrhnout malý produkt od mapy stránek přes prototyp hlavního toku po předání vývoji se specifikací komponent a stavů.',
          'success_criteria' => 
          array (
            0 => 'Tým má mapu stránek a jeden hlavní uživatelský tok o nejvýš 5 krocích.',
            1 => 'Prototyp toku používá komponenty design systému včetně stavů chyby.',
            2 => 'Předávací list obsahuje tokeny, stavy, chování klávesnicí a otevřené otázky.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'w2_design_system',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'w2_forms_states',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 8,
            'phase' => 'Zadání sprintu',
            'teacher' => 'Rozdá zadání (vymyšlená služba) a role v týmu.',
            'student' => 'Rozdělí role: architekt, designér, kontrola přístupnosti, předání.',
            'form' => 've skupinách',
          ),
          1 => 
          array (
            'from' => 8,
            'to' => 25,
            'phase' => 'Mapa a tok',
            'teacher' => 'Hlídá limit 5 kroků toku.',
            'student' => 'Nakreslí mapu stránek a hlavní tok.',
            'form' => 've skupinách',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 55,
            'phase' => 'Prototyp',
            'teacher' => 'Obchází a ptá se na stavy chyby.',
            'student' => 'Postaví prototyp toku z komponent.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 55,
            'to' => 68,
            'phase' => 'Kontrola přístupnosti',
            'teacher' => 'Připomene kontrast, fokus a labely.',
            'student' => 'Zkontrolují prototyp a opraví nálezy.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 68,
            'to' => 82,
            'phase' => 'Předávací list',
            'teacher' => 'Ukáže osnovu předávacího listu.',
            'student' => 'Sepíší tokeny, stavy, chování a otevřené otázky.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Prezentace a exit ticket',
            'teacher' => 'Vybere dva týmy na minutovou ukázku.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Nakreslete v týmu mapu stránek služby a hlavní uživatelský tok o nejvýš 5 krocích.',
            'output' => 'Mapa stránek a diagram toku.',
            'time' => '17 min',
          ),
          1 => 
          array (
            'text' => 'Postavte prototyp hlavního toku z komponent design systému včetně stavu chyby ve formuláři.',
            'output' => 'Klikací prototyp s aspoň jedním chybovým stavem.',
            'time' => '30 min',
          ),
          2 => 
          array (
            'text' => 'Zkontrolujte prototyp z hlediska přístupnosti (kontrast, viditelný fokus, labely) a opravte nálezy.',
            'output' => 'Seznam nálezů a provedených oprav.',
            'time' => '13 min',
          ),
          3 => 
          array (
            'text' => 'Sepište předávací list: použité tokeny, stavy komponent, chování klávesnicí a otevřené otázky.',
            'output' => 'Předávací list na jednu stranu.',
            'time' => '14 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane roli kontroly přístupnosti s připraveným kontrolním seznamem.',
          'standard' => 'Plná práce v týmu podle rolí.',
          'challenge' => 'Jako architekt zdůvodní mapu stránek podle třídění karet z L20.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola po 25 minutách: má každý tým tok o nejvýš 5 krocích?',
            1 => 'Učitel vybere náhodnou komponentu a tým ukáže její chybový stav.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Architektura a tok',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Tok přes 5 kroků.',
                2 => 'Mapa a tok do 5 kroků.',
                3 => 'Zdůvodněno daty z výzkumu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Prototyp a stavy',
              'levels' => 
              array (
                0 => 'Nedokončený.',
                1 => 'Bez stavů.',
                2 => 'Tok z komponent se stavem chyby.',
                3 => 'Stavy i pro selhání a potvrzení.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Předávací list',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen obrázky.',
                2 => 'Tokeny, stavy, klávesnice, otázky.',
                3 => 'Vývojář by podle něj stavěl bez doptávání.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'w2_design_system',
          'competence_label' => 'Design systém',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč omezit hlavní uživatelský tok na málo kroků?',
              'options' => 
              array (
                0 => 'Protože prototyp nesmí mít víc obrazovek.',
                1 => 'Protože každý krok navíc zvyšuje šanci, že uživatel odejde.',
                2 => 'Kvůli velikosti souboru.',
              ),
              'correct' => 1,
              'explanation' => 'Méně kroků = méně míst, kde se uživatel ztratí.',
            ),
            1 => 
            array (
              'question' => 'Co nepatří do předávacího listu vývoji?',
              'options' => 
              array (
                0 => 'Hodnocení spolužáků v týmu.',
                1 => 'Stavy komponent a chování klávesnicí.',
                2 => 'Použité tokeny.',
              ),
              'correct' => 0,
              'explanation' => 'Předávací list popisuje produkt, ne lidi.',
            ),
            2 => 
            array (
              'question' => 'K čemu jsou v předávacím listu „otevřené otázky“?',
              'options' => 
              array (
                0 => 'K vyplnění místa.',
                1 => 'Aby vývojář nic nestavěl.',
                2 => 'Aby bylo jasné, co tým ještě neví a musí se rozhodnout.',
              ),
              'correct' => 2,
              'explanation' => 'Otevřené otázky brání tichým domněnkám.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: doplň do předávacího listu jednu odpověď na otevřenou otázku, kterou jste v týmu nevyřešili.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Služba ze zadání je vymyšlená; prototyp nepoužívá skutečná osobní data.',
          1 => 'Hodnotíme produkt, ne členy týmu; role se rozdělují dobrovolně.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: tým kreslí obrazovky dřív, než má tok – po 25 minutách zkontroluj diagram.',
          1 => 'Otázka do třídy: Co by se stalo, kdyby vývojář dostal jen obrázky bez stavů?',
          2 => 'Tempo: kontrola přístupnosti může běžet souběžně s prototypem, pokud tým zaostává.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá zadání a role; týmy pracují podle úkolů 1–4 a odevzdají soubory.',
          1 => 'Plán B offline: papírový prototyp a předávací list na papíře.',
        ),
        'glossary' => 
        array (
          0 => 'sitemap',
          1 => 'handoff',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
      28 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 28 · Případová studie a ověření dovedností druhého ročníku',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím sestavit případovou studii (case study) svého nejlepšího projektu s důkazy rozhodnutí a podle kompetencí ročníku obhájit, co umím a co zlepším.',
          'success_criteria' => 
          array (
            0 => 'Studie má problém, výzkum, tři rozhodnutí s důkazem a výsledek.',
            1 => 'Ke každé z osmi kompetencí ročníku mám důkaz, nebo pojmenovanou mezeru.',
            2 => 'Obhajobu studie zvládnu za 3 minuty a odpovím na jednu otázku.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'g2_handoff',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'g2_visual_system',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: osnova studie',
            'teacher' => 'Ukáže osnovu studie a příklad silného důkazu.',
            'student' => 'Vyberou projekt pro studii.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 40,
            'phase' => 'Psaní studie',
            'teacher' => 'Obchází a ptá se: „Jak víš, že to fungovalo?“',
            'student' => 'Napíší studii s důkazy.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Mapa kompetencí',
            'teacher' => 'Promítne osm kompetencí ročníku.',
            'student' => 'Přiřadí důkazy a označí mezery.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 55,
            'to' => 80,
            'phase' => 'Obhajoby ve skupinách',
            'teacher' => 'Hlídá limit 3 minuty a otázky.',
            'student' => 'Obhájí studii a položí otázku ostatním.',
            'form' => 've skupinách',
          ),
          4 => 
          array (
            'from' => 80,
            'to' => 85,
            'phase' => 'Cíl',
            'teacher' => 'Připomene ověřitelný cíl.',
            'student' => 'Zapíší cíl na další rok.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 85,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne rok.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš případovou studii projektu: problém, výzkum, tři rozhodnutí s důkazem a výsledek.',
            'output' => 'Studie na 1–2 strany s aspoň dvěma důkazy.',
            'time' => '30 min',
          ),
          1 => 
          array (
            'text' => 'Přiřaď své práce k osmi kompetencím ročníku a u chybějících důkazů napiš, proč chybí.',
            'output' => 'Tabulka kompetence → důkaz → mezera.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Obhaj studii ve skupině do 3 minut a odpověz na jednu otázku spolužáka.',
            'output' => 'Obhajoba a zapsaná otázka s odpovědí.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Napiš jeden ověřitelný cíl na další ročník podle největší mezery v mapě kompetencí.',
            'output' => 'Cíl: co, jak poznám, do kdy.',
            'time' => '5 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane osnovu studie s návodnými otázkami a seznam svých odevzdaných prací.',
          'standard' => 'Studie, mapa kompetencí, obhajoba a cíl.',
          'challenge' => 'Přidá do studie odstavec, co by udělal(a) jinak a jak by to ověřil(a).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ve skupině: posluchači najdou ve studii aspoň jeden důkaz.',
            1 => 'Učitel přečte cíl a třída řekne, jestli je ověřitelný.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Studie',
              'levels' => 
              array (
                0 => 'Jen popis.',
                1 => 'Rozhodnutí bez důkazu.',
                2 => 'Tři rozhodnutí s důkazem a výsledek.',
                3 => 'Reflexe, co by udělal(a) jinak.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Mapa kompetencí',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Částečná.',
                2 => 'Osm kompetencí s důkazem nebo mezerou.',
                3 => 'Mezery s plánem.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obhajoba',
              'levels' => 
              array (
                0 => 'Nezazněla.',
                1 => 'Přes limit, bez odpovědi.',
                2 => 'Do 3 minut s odpovědí.',
                3 => 'Odpověď opřená o důkaz.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'g2_handoff',
          'competence_label' => 'Předání návrhu a případová studie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co je v případové studii nejsilnější?',
              'options' => 
              array (
                0 => 'Množství obrázků.',
                1 => 'Popis použitých nástrojů.',
                2 => 'Rozhodnutí doložená výzkumem nebo testem.',
              ),
              'correct' => 2,
              'explanation' => 'Studie ukazuje, jak a proč ses rozhodoval(a).',
            ),
            1 => 
            array (
              'question' => 'Co uděláš s kompetencí, ke které nemáš důkaz?',
              'options' => 
              array (
                0 => 'Pojmenuji mezeru a naplánuji, jak důkaz získám.',
                1 => 'Napíšu, že ji umím.',
                2 => 'Vynechám ji.',
              ),
              'correct' => 0,
              'explanation' => 'Upřímná mezera je základ dalšího cíle.',
            ),
            2 => 
            array (
              'question' => 'Proč má obhajoba studie časový limit?',
              'options' => 
              array (
                0 => 'Aby se nestihly otázky.',
                1 => 'Aby autor vybral to podstatné a nechal prostor pro otázky.',
                2 => 'Protože delší obhajoba je zakázaná.',
              ),
              'correct' => 1,
              'explanation' => 'Limit nutí k výběru a stručnosti.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: připni studii jako práci do svého portfolia v EDUCANETu s reflexí do 400 znaků.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Ve studii nejsou jména ani fotky testujících; citace bez osobních údajů.',
          1 => 'Portfolio vidí jen žák a jeho učitelé; sdílení ven jen se souhlasem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: studie popisuje nástroje místo rozhodnutí – ptej se „proč právě takhle?“.',
          1 => 'Otázka do třídy: Který důkaz by přesvědčil budoucího zaměstnavatele?',
          2 => 'Tempo: obhajoby ve skupinách po čtyřech; čas hlídá jeden ze skupiny.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá osnovu studie a seznam kompetencí; žáci píší studii a mapu (úkoly 1, 2 a 4).',
          1 => 'Plán B offline: studie na papíře, obhajoby ústně ve skupinách.',
        ),
        'glossary' => 
        array (
          0 => 'case-study',
        ),
        '_file' => 'lesson_content_v76_2a_b.php',
      ),
    ),
    'days' => 
    array (
    ),
    'files' => 
    array (
      0 => 'lesson_content_v72_1a_a.php',
      1 => 'lesson_content_v72_1a_b.php',
      2 => 'lesson_content_v72_2a_a.php',
      3 => 'lesson_content_v72_2a_b.php',
      4 => 'lesson_content_v72_3a_a.php',
      5 => 'lesson_content_v72_3a_b.php',
      6 => 'lesson_content_v72_4a_a.php',
      7 => 'lesson_content_v72_4a_b.php',
      8 => 'lesson_content_v76_1a_a.php',
      9 => 'lesson_content_v76_1a_b.php',
      10 => 'lesson_content_v76_2a_a.php',
      11 => 'lesson_content_v76_2a_b.php',
      12 => 'lesson_content_v76_3a_a.php',
      13 => 'lesson_content_v76_3a_b.php',
      14 => 'lesson_content_v76_4a_a.php',
      15 => 'lesson_content_v76_4a_b.php',
    ),
  ),
  'sig' => '2d75c86284779fb9b76df1d5328dfa465a4d5c6c',
  'hash' => 'b6839f22ed2e37169161256429f65bbfb5d3a98c87d9959be325e99dbd7acf8a',
  'built_at' => '2026-10-12T21:50:39+02:00',
);
