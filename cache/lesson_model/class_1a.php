<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 1,
  'class' => 'class_1a',
  'lessons' => 
  array (
    1 => 
    array (
      'number' => 1,
      'lm71_source' => 'primary',
    ),
    2 => 
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
      'number' => 2,
      'lm71_source' => 'next',
    ),
    3 => 
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
      'lm71_source' => 'extended',
    ),
    4 => 
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
      'lm71_source' => 'extended',
    ),
    5 => 
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
      'lm71_source' => 'plus',
    ),
    6 => 
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
      'lm71_source' => 'plus',
    ),
    7 => 
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
      'lm71_source' => 'more',
    ),
    8 => 
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
      'lm71_source' => 'ecosystem',
    ),
    9 => 
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
      'lm71_source' => 'ecosystem',
    ),
    10 => 
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
      'lm71_source' => 'yearpack',
    ),
    11 => 
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
      'lm71_source' => 'yearpack',
    ),
    12 => 
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
      'lm71_source' => 'yearpack',
    ),
    13 => 
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
      'lm71_source' => 'yearpack',
    ),
    14 => 
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
      'lm71_source' => 'yearpack',
    ),
    15 => 
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
      'lm71_source' => 'yearpack',
    ),
    16 => 
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
      'lm71_source' => 'yearpack',
    ),
    17 => 
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
      'lm71_source' => 'yearpack',
    ),
    18 => 
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
      'lm71_source' => 'yearpack',
    ),
    19 => 
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
      'lm71_source' => 'v30',
    ),
    20 => 
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
      'lm71_source' => 'v30',
    ),
    21 => 
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
      'lm71_source' => 'v30',
    ),
    22 => 
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
      'lm71_source' => 'v30',
    ),
    23 => 
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
      'lm71_source' => 'v30',
    ),
    24 => 
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
      'lm71_source' => 'v30',
    ),
    25 => 
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
      'lm71_source' => 'v30',
    ),
    26 => 
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
      'lm71_source' => 'v30',
    ),
    27 => 
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
      'lm71_source' => 'v30',
    ),
    28 => 
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
