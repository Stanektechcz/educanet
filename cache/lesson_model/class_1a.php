<?php

declare(strict_types=1);

// EDUCANET v71 · odvozená cache modelu lekce (tools/build_runtime_cache.php nebo první čtení). Neupravovat ručně.
return array (
  'version' => 2,
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
      5 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 5 · Paleta, ikony a konzistence',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím postavit malý vizuální systém – paletu se třemi rolemi, jednu rodinu ikon a pravidlo ořezu – a použít ho na informační kartě.',
          'success_criteria' => 
          array (
            0 => 'Paleta má pojmenované role (pozadí, text, akcent) a zapsané HEX kódy.',
            1 => 'Tři ikony mají stejný styl kresby i stejnou optickou velikost.',
            2 => 'Karta 1080 × 1350 px se čte v pořadí nadpis → informace → akce.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_color_contrast',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start a cíl',
            'teacher' => 'Ukáže dvě karty se stejným obsahem (sjednocenou a chaotickou) a nechá třídu hlasovat.',
            'student' => 'Řeknou, která karta je čitelnější a proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Paleta s rolemi',
            'teacher' => 'Předvede volbu barvy pozadí, textu a akcentu a kontrolu kontrastu.',
            'student' => 'V Color Harmony Lab zvolí paletu a zapíší HEX kódy rolí.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Jedna rodina ikon',
            'teacher' => 'Ukáže rozdíl obrysových a vyplněných ikon a tloušťky čáry.',
            'student' => 'Vyberou 3 ikony z jedné sady a sjednotí jejich velikost.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 52,
            'phase' => 'Ohnisko a ořez',
            'teacher' => 'Na jedné fotce ukáže dva ořezy a místo pro text.',
            'student' => 'Připraví dva ořezy a vyberou lepší.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 52,
            'to' => 80,
            'phase' => 'Realizace karty',
            'teacher' => 'Obchází, ptá se „co uvidím první?“, hlídá limit 3 ikon.',
            'student' => 'Složí kartu v Canvě nebo Figmě a vyexportují ji.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Spustí párovou kontrolu ikon a exit ticket.',
            'student' => 'Najdou u souseda ikonu, která vybočuje, a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zvol paletu se třemi rolemi (pozadí, text, akcent) a zapiš jejich HEX kódy.',
            'output' => 'Tabulka rolí s HEX kódy a poznámkou o kontrastu textu.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Vyber 3 ikony z jedné sady a sjednoť jejich optickou velikost a tloušťku čáry.',
            'output' => 'Řádek tří ikon ve stejném stylu.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Připrav dva ořezy (crop) fotografie a vyber ten, který nechává klidné místo pro text.',
            'output' => 'Dva ořezy a jedna věta, proč vítězí ten vybraný.',
            'time' => '12 min',
          ),
          3 => 
          array (
            'text' => 'Slož informační kartu 1080 × 1350 px s nejvýš třemi ikonami a jedním akcentem.',
            'output' => 'Export PNG a uložený zdrojový soubor.',
            'time' => '28 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou paletu a sadu šesti ikon; vybírá z nich a skládá kartu do připravené mřížky.',
          'standard' => 'Vlastní paleta, ikony z jedné bezplatné sady s licencí a karta podle zadání.',
          'challenge' => 'Vytvoří druhou variantu karty v tmavém provedení se stejnými rolemi barev a ověří kontrast obou.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po paletě: každý ukáže HEX akcentu a řekne, k čemu ho použije.',
            1 => 'Párová kontrola: soused najde ikonu, která stylem vybočuje.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Role barev',
              'levels' => 
              array (
                0 => 'Barvy nemají role.',
                1 => 'Role jsou určené, kontrast neověřený.',
                2 => 'Tři role s HEX kódy a ověřeným kontrastem.',
                3 => 'Role zdůvodní a udrží je i v druhé variantě.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Konzistence ikon',
              'levels' => 
              array (
                0 => 'Ikony z různých sad.',
                1 => 'Jedna sada, nesourodé velikosti.',
                2 => 'Stejný styl i optická velikost.',
                3 => 'Vysvětlí pravidlo a pohlídá ho i u nové ikony.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Pořadí čtení',
              'levels' => 
              array (
                0 => 'Není jasné, co číst první.',
                1 => 'Nadpis vyniká, ostatní prvky soupeří.',
                2 => 'Nadpis → informace → akce.',
                3 => 'Obstojí v testu tří sekund u spolužáka.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_color_contrast',
          'competence_label' => 'Barvy a kontrast',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'K čemu v paletě slouží barva s rolí „akcent“?',
              'options' => 
              array (
                0 => 'Na veškerý běžný text, aby byl výrazný.',
                1 => 'Na jednu věc, kterou má divák najít jako první, třeba tlačítko.',
                2 => 'Na pozadí celé karty.',
              ),
              'correct' => 1,
              'explanation' => 'Akcent upozorňuje jen tehdy, když je ho málo.',
            ),
            1 => 
            array (
              'question' => 'Máš dvě obrysové ikony a jednu vyplněnou. Co uděláš?',
              'options' => 
              array (
                0 => 'Nechám je, rozmanitost je zajímavá.',
                1 => 'Vyplněnou zvětším, ať je vidět.',
                2 => 'Vyplněnou vyměním za obrysovou ze stejné sady.',
              ),
              'correct' => 2,
              'explanation' => 'Jedna rodina ikon = stejný styl kresby.',
            ),
            2 => 
            array (
              'question' => 'Kdy je ořez fotografie pro informační kartu dobrý?',
              'options' => 
              array (
                0 => 'Když ohnisko zůstane vidět a zbude klidné místo pro text.',
                1 => 'Když je na fotce co nejvíc detailů.',
                2 => 'Když text vede přes obličej, aby byl uprostřed.',
              ),
              'correct' => 0,
              'explanation' => 'Ořez slouží sdělení: ohnisko + prostor pro text.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi kolem sebe jeden plakát nebo obal a zapiš jeho tři barvy s rolemi (pozadí, text, akcent).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Ikony a fotky jen vlastní nebo z bezplatných zdrojů s licencí; zdroj zapiš do poznámky.',
          1 => 'Na kartě nejsou jména ani fotky spolužáků.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: akcentní barva na všem. Ptej se „co má divák najít jako první?“.',
          1 => 'Otázka do třídy: Kdy přestane akcent upozorňovat?',
          2 => 'Tempo: realizace se protahuje – v 80. minutě ukonči práci, nedokončené karty se dodělají příště.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: ukáže dvě karty z úvodu, žáci plní úkoly 1–4 a odevzdají export.',
          1 => 'Plán B offline: paleta pastelkami, ikony obkreslením a karta jako skica na papír A5.',
        ),
        'glossary' => 
        array (
          0 => 'hex',
          1 => 'crop',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      6 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 6 · Redesign: z chaosu na systém',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím najít skutečné problémy slabého plakátu, navrhnout redesign a obhájit každé rozhodnutí funkcí, ne vkusem.',
          'success_criteria' => 
          array (
            0 => 'Pojmenuji tři konkrétní problémy a jejich dopad na diváka.',
            1 => 'Drátěný model (wireframe) určuje první, druhou a třetí informaci.',
            2 => 'Porovnání před/po ukazuje lepší hierarchii a splněný cíl zadání.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 12,
            'phase' => 'Diagnostika',
            'teacher' => 'Promítne slabý plakát a zadá: hledáme problémy funkce, ne vkusu.',
            'student' => 'Zapíší tři problémy a dopad na diváka.',
            'form' => 've dvojicích',
          ),
          1 => 
          array (
            'from' => 12,
            'to' => 27,
            'phase' => 'Plán redesignu',
            'teacher' => 'Ukáže, jak seřadit informace podle důležitosti.',
            'student' => 'Určí 1., 2. a 3. informaci, paletu a obrazový princip.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 27,
            'to' => 45,
            'phase' => 'Drátěný model',
            'teacher' => 'Připomene mřížku (grid) a výzvu k akci (CTA).',
            'student' => 'Nakreslí wireframe bez dekorací a ověří mřížku.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 70,
            'phase' => 'Realizace',
            'teacher' => 'Hlídá, aby nepřibýval text, který zadání nepotřebuje.',
            'student' => 'Realizují redesign v Canvě nebo Figmě.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Před a po',
            'teacher' => 'Řídí krátkou obhajobu ve dvojicích.',
            'student' => 'Obhájí 2 rozhodnutí: problém → změna → efekt.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Kontrola exportu a exit ticket',
            'teacher' => 'Připomene kontrolu exportu mimo editor.',
            'student' => 'Otevřou export mimo editor a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Najdi na slabém plakátu tři konkrétní problémy a ke každému napiš, co způsobí divákovi.',
            'output' => 'Seznam: problém → dopad (3 řádky).',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Seřaď informace plakátu na první, druhou a třetí a zvol paletu.',
            'output' => 'Pořadí informací a paleta se třemi rolemi.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Nakresli drátěný model (wireframe) v mřížce s jasnou výzvou k akci (CTA).',
            'output' => 'Wireframe bez barev a dekorací.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Realizuj redesign a ulož porovnání před/po do jednoho souboru.',
            'output' => 'Export redesignu a obrázek před/po.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kontrolní seznam pěti typických chyb (hierarchie, kontrast, zarovnání, přeplnění, chybějící akce) a vybírá z něj.',
          'standard' => 'Samostatná diagnostika, wireframe a redesign podle zadání.',
          'challenge' => 'Vytvoří dvě odlišné varianty redesignu a vybere tu, která lépe plní cíl zadání, s jedním měřitelným argumentem.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po diagnostice: dvojice přečte jeden problém a dopad – třída posoudí, zda jde o funkci, nebo vkus.',
            1 => 'U wireframu ukáže každý prstem pořadí čtení.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Diagnostika',
              'levels' => 
              array (
                0 => 'Jen „nelíbí se mi“.',
                1 => 'Problémy bez dopadu na diváka.',
                2 => 'Tři problémy s dopadem.',
                3 => 'Seřadí problémy podle závažnosti.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Hierarchie redesignu',
              'levels' => 
              array (
                0 => 'Pořadí čtení chybí.',
                1 => 'Nadpis vyniká, zbytek soupeří.',
                2 => 'Jasné 1.–2.–3. a viditelná akce.',
                3 => 'Obstojí v testu tří sekund i na náhledu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Obhajoba',
              'levels' => 
              array (
                0 => 'Neumí vysvětlit změny.',
                1 => 'Popíše změnu bez důvodu.',
                2 => 'Problém → změna → efekt u 2 změn.',
                3 => 'Přidá i kompromis, který vědomě přijal.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_composition',
          'competence_label' => 'Kompozice a hierarchie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který zápis je dobře formulovaný problém plakátu?',
              'options' => 
              array (
                0 => 'Plakát je ošklivý.',
                1 => 'Datum akce je malé a ve stejné barvě jako pozadí, divák ho přehlédne.',
                2 => 'Chtělo by to víc barev.',
              ),
              'correct' => 1,
              'explanation' => 'Dobrý problém = co přesně je špatně + dopad na diváka.',
            ),
            1 => 
            array (
              'question' => 'Proč se drátěný model kreslí bez barev a efektů?',
              'options' => 
              array (
                0 => 'Aby se nejdřív ověřilo pořadí informací a rozvržení.',
                1 => 'Protože barvy se do plakátu nepřidávají.',
                2 => 'Aby byl hotový rychleji než ostatní.',
              ),
              'correct' => 0,
              'explanation' => 'Wireframe testuje strukturu dřív, než ji zakryje styl.',
            ),
            2 => 
            array (
              'question' => 'Co do redesignu nepatří?',
              'options' => 
              array (
                0 => 'Zachovat význam původního zadání.',
                1 => 'Sjednotit zarovnání do mřížky.',
                2 => 'Přidat nový text, který zadání nevyžaduje.',
              ),
              'correct' => 2,
              'explanation' => 'Redesign zlepšuje komunikaci, nemění obsah zadání.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyfoť jeden přeplněný leták nebo vývěsku a napiš dva problémy ve tvaru problém → dopad.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Slabý plakát do hodiny připravuje učitel (vymyšlená akce); nepoužívá se cizí práce spolužáka bez souhlasu.',
          1 => 'Obrázky v redesignu jen vlastní nebo s licencí.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: redesign = víc efektů. Vracej žáky k otázce „co má divák udělat?“.',
          1 => 'Otázka do třídy: Je tohle problém funkce, nebo vkusu?',
          2 => 'Tempo: wireframe nesmí přesáhnout 45. minutu, jinak nezbude čas na realizaci.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vytištěný slabý plakát, žáci plní úkoly 1–3 na papír a úkol 4 podle možností učebny.',
          1 => 'Plán B offline: celý redesign jako skica A4 tužkou + popisky rozhodnutí.',
        ),
        'glossary' => 
        array (
          0 => 'wireframe',
          1 => 'cta',
          2 => 'grid',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      7 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 7 · Jeden design, tři formáty',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést jeden plakát do čtverce a vertikálního formátu tak, aby zůstala hierarchie, čitelnost a vizuální identita.',
          'success_criteria' => 
          array (
            0 => 'Zapíšu tři pravidla, která se mezi formáty nesmí změnit.',
            1 => 'Čtverec 1080 × 1080 i vertikála 1080 × 1920 drží stejné pořadí čtení.',
            2 => 'Text ve vertikále je uvnitř bezpečné zóny (safe zone).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_formats',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže jeden plakát špatně zmenšený do tří formátů.',
            'student' => 'Najdou, co se při zmenšení rozbilo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Neměnná pravidla',
            'teacher' => 'Vysvětlí bezpečnou zónu a náhled (thumbnail).',
            'student' => 'Sepíšou tři neměnná pravidla svého plakátu.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 43,
            'phase' => 'Čtverec',
            'teacher' => 'Připomene: měníme ořez a rozvržení, ne identitu.',
            'student' => 'Vytvoří verzi 1080 × 1080.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 43,
            'to' => 63,
            'phase' => 'Vertikála',
            'teacher' => 'Ukáže, kam v příbězích zasahuje rozhraní aplikace.',
            'student' => 'Vytvoří verzi 1080 × 1920 s textem v bezpečné zóně.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 63,
            'to' => 80,
            'phase' => 'Sjednocení a export',
            'teacher' => 'Ukáže konzistentní pojmenování souborů.',
            'student' => 'Sjednotí paletu a písmo, exportují 3 soubory.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Kontrola série a exit ticket',
            'teacher' => 'Spustí rychlou kontrolu série vedle sebe.',
            'student' => 'Porovnají tři formáty a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zapiš tři pravidla svého plakátu, která se mezi formáty nesmí změnit (například pořadí informací, paleta, písmo).',
            'output' => 'Tři neměnná pravidla.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Vytvoř čtvercovou verzi 1080 × 1080 – změň ořez a rozvržení, ne identitu.',
            'output' => 'Čtverec ve stejném stylu.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Vytvoř vertikální verzi 1080 × 1920 a drž texty uvnitř bezpečné zóny (safe zone).',
            'output' => 'Vertikála s vyznačenou bezpečnou zónou.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Exportuj tři formáty a pojmenuj je jednotně (akce_post, akce_ctverec, akce_story).',
            'output' => 'Tři exporty se srozumitelnými názvy.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu se zakreslenými bezpečnými zónami a vyplňuje jen dva formáty (post a čtverec).',
          'standard' => 'Tři formáty podle zadání včetně jednotných názvů souborů.',
          'challenge' => 'Přidá čtvrtý formát (široký banner 1500 × 500) a zdůvodní, co v něm vynechal.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po pravidlech: dvojice si vymění seznamy a hledá pravidlo, které je jen „hezké“, ne funkční.',
            1 => 'Kontrola náhledu: vertikála zmenšená na šířku palce – je nadpis čitelný?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Neměnná pravidla',
              'levels' => 
              array (
                0 => 'Pravidla chybí.',
                1 => 'Pravidla obecná („aby to bylo hezké“).',
                2 => 'Tři funkční pravidla.',
                3 => 'Pravidla dodrží ve všech formátech a doloží to.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Adaptace formátů',
              'levels' => 
              array (
                0 => 'Jen zmenšený plakát.',
                1 => 'Změněný formát, rozbité pořadí.',
                2 => 'Čtverec i vertikála drží pořadí čtení.',
                3 => 'Přidaný formát s vědomým vynecháním.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Produkce',
              'levels' => 
              array (
                0 => 'Chybí exporty.',
                1 => 'Exporty s náhodnými názvy.',
                2 => 'Tři exporty s jednotnými názvy.',
                3 => 'Exporty zkontrolované mimo editor včetně náhledu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_formats',
          'competence_label' => 'Formáty a export',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co je bezpečná zóna ve vertikálním formátu?',
              'options' => 
              array (
                0 => 'Oblast, kam se dává logo autora.',
                1 => 'Prostor, kde text nepřekryje rozhraní aplikace.',
                2 => 'Okraj, který se při exportu ořízne.',
              ),
              'correct' => 1,
              'explanation' => 'Mimo bezpečnou zónu může text zakrýt rozhraní aplikace.',
            ),
            1 => 
            array (
              'question' => 'Co je při převodu do čtverce správně?',
              'options' => 
              array (
                0 => 'Zmenšit celý plakát a doplnit prázdné okraje.',
                1 => 'Zachovat pořadí čtení a změnit rozvržení a ořez.',
                2 => 'Změnit písmo, ať je čtverec jiný.',
              ),
              'correct' => 1,
              'explanation' => 'Mění se rozvržení, identita zůstává.',
            ),
            2 => 
            array (
              'question' => 'Který název souboru je pro sérii nejlepší?',
              'options' => 
              array (
                0 => 'final2_opravdu.png',
                1 => 'obrazek.png',
                2 => 'koncert_story_1080x1920.png',
              ),
              'correct' => 2,
              'explanation' => 'Název říká obsah, formát i rozměr.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi jednu kampaň ve třech formátech (plakát, příspěvek, příběh) a zapiš, co zůstalo stejné.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pracuje se s vymyšlenou akcí, bez skutečných kontaktů a jmen.',
          1 => 'Fotky a písma jen s licencí, která dovoluje úpravy.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: vertikála = zmenšený plakát s prázdnými pruhy.',
          1 => 'Otázka do třídy: Které tři věci musí divák poznat v každém formátu?',
          2 => 'Tempo: vertikála zabere nejvíc času – úkol 4 lze dokončit doma nebo příště.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: promítne zadání, žáci plní úkoly 1–4, učitel zástupu sbírá exporty.',
          1 => 'Plán B offline: tři obdélníky ve správném poměru na papíře a rozvržení tužkou.',
        ),
        'glossary' => 
        array (
          0 => 'safe-zone',
          1 => 'thumbnail',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      8 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 8 · Typografie a rozvržení stránky',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím nastavit čtyři textové role, rozumnou délku řádku a opakovaný rytmus mezer v dvousloupcovém rozvržení.',
          'success_criteria' => 
          array (
            0 => 'Čtyři role textu (nadpis, podnadpis, text, popisek) mají jasný rozdíl velikosti.',
            1 => 'Odstavec má rozumnou délku řádku a řádkování.',
            2 => 'Mezery mezi skupinami se opakují podle jedné škály.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_typography',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže článek s deseti různými velikostmi písma.',
            'student' => 'Spočítají velikosti a řeknou, co ztěžuje čtení.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Textové role',
            'teacher' => 'Předvede Type Scale Lab a pojmenování rolí.',
            'student' => 'Nastaví 4 role a zapíší jejich velikosti.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Řádek a řádkování',
            'teacher' => 'Ukáže příliš dlouhý řádek a jeho zkrácení.',
            'student' => 'Upraví šířku sloupce a řádkování odstavce.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Rytmus mezer',
            'teacher' => 'Vysvětlí škálu mezer a volný prostor (whitespace).',
            'student' => 'V Vertical Rhythm Lab zvolí škálu a oddělí skupiny.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 80,
            'phase' => 'Dvousloupcové rozvržení',
            'teacher' => 'Hlídá, aby se používaly jen definované role.',
            'student' => 'Postaví stránku časopisu v Canvě nebo Figmě.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Test čitelnosti a exit ticket',
            'teacher' => 'Spustí test „přečti jen nadpisy“.',
            'student' => 'Soused zkusí pochopit stránku z nadpisů; exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Nastav čtyři textové role (nadpis, podnadpis, text, popisek) a zapiš jejich velikosti a řez písma.',
            'output' => 'Tabulka rolí: velikost, řez, použití.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zkrať příliš dlouhý řádek odstavce a uprav řádkování tak, aby se text dobře četl.',
            'output' => 'Odstavec před a po úpravě.',
            'time' => '12 min',
          ),
          2 => 
          array (
            'text' => 'Zvol škálu mezer (například 8 / 16 / 32 px) a použij ji mezi skupinami textu.',
            'output' => 'Stránka, kde se mezery opakují podle škály.',
            'time' => '13 min',
          ),
          3 => 
          array (
            'text' => 'Postav dvousloupcovou stránku časopisu jen s definovanými rolemi a srovnanými hranami.',
            'output' => 'Export stránky.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane připravenou škálu velikostí a mezer; přiřazuje role textům v hotové šabloně.',
          'standard' => 'Vlastní role, škála a dvousloupcová stránka podle zadání.',
          'challenge' => 'Navrhne druhou verzi stránky pro mobil (jeden sloupec) se stejnými rolemi.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola rolí: každý ukáže dvě role, které jsou si nejpodobnější – liší se dost?',
            1 => 'Test nadpisů: soused řekne, o čem stránka je, jen z nadpisů.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Textové role',
              'levels' => 
              array (
                0 => 'Náhodné velikosti.',
                1 => 'Role existují, ale splývají.',
                2 => 'Čtyři zřetelně odlišené role.',
                3 => 'Role popíše tak, aby je použil i spolužák.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Čitelnost odstavce',
              'levels' => 
              array (
                0 => 'Dlouhé řádky, hustý text.',
                1 => 'Upravená jen jedna vlastnost.',
                2 => 'Rozumná délka řádku i řádkování.',
                3 => 'Zdůvodní volbu a ověří ji na náhledu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rytmus a rozvržení',
              'levels' => 
              array (
                0 => 'Mezery náhodné.',
                1 => 'Škála zvolená, nedodržená.',
                2 => 'Mezery podle škály, srovnané hrany.',
                3 => 'Rytmus drží i v mobilní verzi.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_typography',
          'competence_label' => 'Typografie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Odstavec se táhne přes celou šířku velkého monitoru. Co pomůže nejvíc?',
              'options' => 
              array (
                0 => 'Zmenšit písmo, aby se vešlo víc slov.',
                1 => 'Zúžit textový sloupec a přidat řádkování.',
                2 => 'Zvýraznit celý odstavec tučně.',
              ),
              'correct' => 1,
              'explanation' => 'Oko se na konci dlouhého řádku ztrácí.',
            ),
            1 => 
            array (
              'question' => 'Kolik velikostí písma potřebuje jednoduchá stránka časopisu?',
              'options' => 
              array (
                0 => 'Malou škálu pojmenovaných rolí, typicky 3–5.',
                1 => 'Každý odstavec jinou.',
                2 => 'Jednu velikost pro všechno.',
              ),
              'correct' => 0,
              'explanation' => 'Malá škála rolí je čitelná a opakovatelná.',
            ),
            2 => 
            array (
              'question' => 'Co dělá volný prostor (whitespace) v rozvržení?',
              'options' => 
              array (
                0 => 'Je to chyba, kterou je třeba zaplnit.',
                1 => 'Šetří barvu tiskárny.',
                2 => 'Odděluje skupiny a vede oko.',
              ),
              'correct' => 2,
              'explanation' => 'Volný prostor je aktivní prvek kompozice.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyfoť stránku knihy nebo časopisu a označ na ní čtyři textové role.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Písma jen s licencí pro volné použití (např. z bezplatné knihovny písem); zdroj zapiš.',
          1 => 'Texty do rozvržení jsou vymyšlené nebo s uvedeným zdrojem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rozdíl rolí jen o 1–2 px. Chtěj viditelný skok.',
          1 => 'Otázka do třídy: Kdy je řádek už moc dlouhý? Zkuste ho přečíst nahlas.',
          2 => 'Tempo: Type Scale Lab zabere víc, než se zdá – nastav časovač na 15 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci plní úkoly 1–4 podle zadání v lekci, výstupem je export stránky.',
          1 => 'Plán B offline: role a mezery na papír – nadpisy fixou, text tužkou, mezery pravítkem.',
        ),
        'glossary' => 
        array (
          0 => 'whitespace',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      9 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 9 · Vizuální příběh a odevzdání výstupu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím řídit cestu pozornosti od ohniska přes informaci k akci a odevzdat výstup ve správném formátu se zdrojovým souborem.',
          'success_criteria' => 
          array (
            0 => 'Určím ohnisko a zapíšu plánovanou cestu oka.',
            1 => 'Varianta B mění styl, ne význam, a umím zdůvodnit výběr.',
            2 => 'Odevzdám pojmenovaný export správného rozměru a zachovaný zdrojový soubor.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_formats',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Na 3 sekundy ukáže plakát a zeptá se, co si žáci zapamatovali.',
            'student' => 'Zapíší první věc, kterou viděli.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Ohnisko a cesta oka',
            'teacher' => 'Předvede Visual Story Lab.',
            'student' => 'Určí ohnisko a nakreslí šipkami cestu oka.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Obraz a text',
            'teacher' => 'Ukáže, jak směr pohledu postavy vede k textu.',
            'student' => 'Upraví ořez a ověří čitelnost textu přes obraz.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 60,
            'phase' => 'Varianta B',
            'teacher' => 'Zadá: jiný styl, stejný význam.',
            'student' => 'Vytvoří variantu B a vyberou lepší podle cíle.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Kontrola před odevzdáním',
            'teacher' => 'Projde kontrolní seznam (preflight).',
            'student' => 'Zkontrolují rozměr, ořez, kontrast a pojmenují soubory.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Odevzdání a exit ticket',
            'teacher' => 'Ukáže, kam se odevzdává export i zdrojový soubor.',
            'student' => 'Odevzdají výstup a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Urči ohnisko (focal point) svého návrhu a šipkami zakresli plánovanou cestu oka: obraz → nadpis → informace → akce.',
            'output' => 'Náhled se šipkami cesty oka.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Uprav ořez a umístění textu tak, aby obraz vedl pozornost k nadpisu a text byl čitelný.',
            'output' => 'Upravený návrh.',
            'time' => '17 min',
          ),
          2 => 
          array (
            'text' => 'Vytvoř variantu B se stejným obsahem a jiným stylem; vyber lepší a napiš proč.',
            'output' => 'Varianty A a B + jedna věta zdůvodnění.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Projdi kontrolní seznam před odevzdáním (preflight) a odevzdej export i zdrojový soubor.',
            'output' => 'Export se správným názvem a rozměrem + zdrojový soubor.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Pracuje s připraveným obrázkem a šablonou; kontrolní seznam má zaškrtávací políčka.',
          'standard' => 'Vlastní návrh, varianta B a kontrola podle seznamu.',
          'challenge' => 'Varianta C pro tmavé pozadí a krátké porovnání, která varianta je čitelnější na mobilu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Test 3 sekund: soused řekne, co viděl první – shoduje se to s plánem?',
            1 => 'Před odevzdáním: učitel namátkou otevře dva exporty mimo editor.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Cesta pozornosti',
              'levels' => 
              array (
                0 => 'Ohnisko chybí.',
                1 => 'Ohnisko je, cesta oka bloudí.',
                2 => 'Obraz → nadpis → akce funguje.',
                3 => 'Doloží testem 3 sekund u spolužáka.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Varianta a výběr',
              'levels' => 
              array (
                0 => 'Varianta B chybí.',
                1 => 'Varianta mění i význam.',
                2 => 'Jiný styl, stejný význam, výběr zdůvodněný.',
                3 => 'Výběr opře o pozorování spolužáka.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Odevzdání',
              'levels' => 
              array (
                0 => 'Jen snímek obrazovky.',
                1 => 'Export bez zdrojového souboru.',
                2 => 'Pojmenovaný export + zdrojový soubor.',
                3 => 'Kontrolní seznam vyplněný a bez chyb.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_formats',
          'competence_label' => 'Formáty a export',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč se odevzdává i zdrojový (upravitelný) soubor?',
              'options' => 
              array (
                0 => 'Aby šel návrh později opravit nebo převést do jiného formátu.',
                1 => 'Protože je větší a vypadá lépe.',
                2 => 'Zdrojový soubor se neodevzdává nikdy.',
              ),
              'correct' => 0,
              'explanation' => 'Z exportu se dobře neopravuje; zdroj je pojistka.',
            ),
            1 => 
            array (
              'question' => 'Co je ohnisko obrazu?',
              'options' => 
              array (
                0 => 'Nejmenší text na plakátu.',
                1 => 'Místo, kam se oko podívá jako první.',
                2 => 'Barva pozadí.',
              ),
              'correct' => 1,
              'explanation' => 'Ohnisko zahajuje cestu pozornosti.',
            ),
            2 => 
            array (
              'question' => 'Co patří do kontroly před odevzdáním (preflight)?',
              'options' => 
              array (
                0 => 'Jen to, jestli se mi návrh líbí.',
                1 => 'Počet vrstev v editoru.',
                2 => 'Rozměr, ořez, kontrast, údaje a název souboru.',
              ),
              'correct' => 2,
              'explanation' => 'Preflight hledá chyby dřív, než je uvidí divák.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: pusť si 15 sekund libovolné reklamy a zapiš, kam ti padl pohled v prvních 3 sekundách.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Fotky osob jen se souhlasem nebo z bezplatných zdrojů s licencí; žádné fotky spolužáků bez souhlasu.',
          1 => 'Do názvů souborů nepiš celé jméno – stačí třída a téma.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: dvě stejně silné dominanty. Nech žáky jednu ztlumit.',
          1 => 'Otázka do třídy: Kam se dívá postava na fotce a kam tím posílá diváka?',
          2 => 'Tempo: varianta B nemá být nový projekt – max. 18 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: test 3 sekund s promítnutým plakátem, pak úkoly 1–4 podle lekce.',
          1 => 'Plán B offline: cesta oka šipkami na vytištěném plakátu, varianta B jako skica.',
        ),
        'glossary' => 
        array (
          0 => 'focal-point',
          1 => 'preflight',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      10 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 10 · Anatomie webu: hierarchie od plakátu k obrazovce',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím rozložit webovou stránku na části (záhlaví, úvodní sekce, obsah, výzva k akci, zápatí) a nakreslit jejich pořadí podle důležitosti.',
          'success_criteria' => 
          array (
            0 => 'U pěti částí stránky napíšu jejich hlavní účel.',
            1 => 'Nadpis úvodní sekce má nejvýš 8 slov a jednu hlavní akci.',
            2 => 'Kostra stránky má společné hrany a stejné vodorovné okraje.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_html_structure',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Promítne úvodní stránku vymyšlené školní akce.',
            'student' => 'Ukážou, kde končí záhlaví a kde začíná obsah.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Části stránky',
            'teacher' => 'Pojmenuje záhlaví, úvodní sekci (hero), obsah, CTA a zápatí.',
            'student' => 'Ke každé části napíšou její jediný účel.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Priorita obsahu',
            'teacher' => 'Ukáže zkrácení dlouhého zadání na nadpis, podtext a akci.',
            'student' => 'Napíšou nadpis do 8 slov a vyberou hlavní akci.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 60,
            'phase' => 'Kostra stránky',
            'teacher' => 'Připomene mřížku (grid) a stejné okraje.',
            'student' => 'Nakreslí kostru pro šířku 1440 px bez dekorací.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 78,
            'phase' => 'Mobil',
            'teacher' => 'Zeptá se: co musí být vidět bez posouvání?',
            'student' => 'Označí, co na mobilu půjde pod sebe.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Sdílení a exit ticket',
            'teacher' => 'Vybere dvě kostry k rychlé zpětné vazbě.',
            'student' => 'Řeknou „co vidím první“ a odpoví na exit ticket.',
            'form' => 'frontálně',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Na ukázkové stránce označ záhlaví, úvodní sekci (hero), obsah, výzvu k akci (CTA) a zápatí a ke každé části napiš její účel.',
            'output' => 'Popsaný snímek stránky.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Zkrať zadání akce na nadpis do 8 slov, jednu větu podtextu a jednu hlavní akci.',
            'output' => 'Nadpis, podtext a text tlačítka.',
            'time' => '12 min',
          ),
          2 => 
          array (
            'text' => 'Nakresli kostru stránky pro šířku 1440 px se společnými hranami a stejnými okraji.',
            'output' => 'Kostra stránky (papír nebo Figma).',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Označ, které části půjdou na mobilu pod sebe a co musí být vidět bez posouvání.',
            'output' => 'Poznámky k mobilní verzi.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vytištěnou stránku s předkreslenými rámečky; doplňuje názvy částí a jejich účel.',
          'standard' => 'Samostatná analýza, nadpis, kostra a poznámky k mobilu.',
          'challenge' => 'Navrhne druhou kostru pro jiný cíl (přihláška místo informace) a popíše, co se změnilo.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž prstem: učitel jmenuje část stránky, žáci ji ukážou na své kostře.',
            1 => 'Nadpis nahlas: tři žáci přečtou nadpis – rozumí třída, o co jde?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Části stránky',
              'levels' => 
              array (
                0 => 'Části nerozliší.',
                1 => 'Pojmenuje části bez účelu.',
                2 => 'Pět částí s účelem.',
                3 => 'Vysvětlí, proč je pořadí právě takové.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Priorita obsahu',
              'levels' => 
              array (
                0 => 'Nadpis chybí nebo je dlouhý.',
                1 => 'Nadpis ok, akcí je víc.',
                2 => 'Nadpis do 8 slov a jedna hlavní akce.',
                3 => 'Nadpis obstojí v testu 3 sekund.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Kostra a mobil',
              'levels' => 
              array (
                0 => 'Bez mřížky.',
                1 => 'Mřížka, nesouhlasí okraje.',
                2 => 'Společné hrany a poznámky k mobilu.',
                3 => 'Mobilní pořadí zdůvodní prioritou obsahu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_html_structure',
          'competence_label' => 'Struktura webu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co je úkolem zápatí stránky?',
              'options' => 
              array (
                0 => 'Hlavní nabídka akce.',
                1 => 'Doplňkové informace: kontakt, odkazy, právní údaje.',
                2 => 'Největší obrázek stránky.',
              ),
              'correct' => 1,
              'explanation' => 'Zápatí uzavírá stránku doplňkovými informacemi.',
            ),
            1 => 
            array (
              'question' => 'Kolik hlavních akcí má mít úvodní sekce?',
              'options' => 
              array (
                0 => 'Jednu výraznou, ostatní méně nápadné.',
                1 => 'Pět stejně velkých tlačítek.',
                2 => 'Žádnou, akce patří jen do zápatí.',
              ),
              'correct' => 0,
              'explanation' => 'Jedna hlavní akce nesoutěží sama se sebou.',
            ),
            2 => 
            array (
              'question' => 'Proč kreslíme kostru stránky dřív než barvy?',
              'options' => 
              array (
                0 => 'Barvy na webu nejsou potřeba.',
                1 => 'Aby byla práce kratší.',
                2 => 'Ověříme pořadí obsahu, než ho zakryje styl.',
              ),
              'correct' => 2,
              'explanation' => 'Struktura je základ, styl přichází potom.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na webu, který používáš, najdi úvodní sekci a zapiš její nadpis a hlavní akci.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Analyzuje se vymyšlená stránka nebo veřejný web; nic se nepřihlašuje ani neodesílá.',
          1 => 'Do kostry se nepíšou skutečné kontakty.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: začít kódem. Dnes jen model stránky – HTML až později.',
          1 => 'Otázka do třídy: Co vidíš první? (místo „líbí se ti to?“)',
          2 => 'Tempo: kostra 1440 px se kreslí rychle, pokud mají žáci připravený rámeček.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vytištěnou stránku, žáci plní úkoly 1–4 na papír.',
          1 => 'Plán B offline: celá hodina na papíře A4 s pravítkem.',
        ),
        'glossary' => 
        array (
          0 => 'hero',
          1 => 'cta',
          2 => 'grid',
        ),
        '_file' => 'lesson_content_v72_1a_a.php',
      ),
      11 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 11 · Responzivní základ: jedna stránka, tři šířky',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout stejný obsah pro mobil, tablet a počítač tak, aby se přeskupil podle priority, a ne jen zmenšil.',
          'success_criteria' => 
          array (
            0 => 'Mobilní verze 390 px řadí obsah podle priority.',
            1 => 'U každé šířky zapíšu jednu změnu rozvržení a její důvod.',
            2 => 'Text na počítači nepřesahuje rozumnou šířku sloupce.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_css_layout',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže stejný web zmenšený na mobil bez úprav.',
            'student' => 'Najdou tři místa, kde se obsah rozbil.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Tři šířky',
            'teacher' => 'Porovná 390, 768 a 1440 px a vysvětlí mobile first.',
            'student' => 'Určí, co se přeskupí, co zůstane a co se může skrýt.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 42,
            'phase' => 'Mobil jako první',
            'teacher' => 'Hlídá řazení podle priority, ne podle počítačové verze.',
            'student' => 'Navrhnou kostru 390 px.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 42,
            'to' => 57,
            'phase' => 'Tablet',
            'teacher' => 'Ukáže, kdy druhý sloupec pomáhá a kdy škodí.',
            'student' => 'Přidají druhý sloupec jen tam, kde dává smysl.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 57,
            'to' => 75,
            'phase' => 'Počítač',
            'teacher' => 'Připomene maximální šířku textu.',
            'student' => 'Rozšíří rozvržení na 1440 px bez roztaženého textu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Bod zlomu a exit ticket',
            'teacher' => 'Vysvětlí, že bod zlomu určuje obsah, ne pevné číslo.',
            'student' => 'Zapíší změny u každé šířky a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Porovnej pět sekcí stránky v šířkách 390, 768 a 1440 px a zapiš, co se přeskupí, co zůstane a co se skryje.',
            'output' => 'Tabulka sekcí × šířky.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni mobilní kostru 390 px, ve které je obsah seřazený podle priority (mobile first).',
            'output' => 'Mobilní kostra.',
            'time' => '17 min',
          ),
          2 => 
          array (
            'text' => 'Přidej verzi pro tablet a počítač; text na počítači drž v rozumně širokém sloupci.',
            'output' => 'Kostry 768 a 1440 px.',
            'time' => '30 min',
          ),
          3 => 
          array (
            'text' => 'U každého bodu zlomu (breakpoint) zapiš jednu změnu rozvržení a důvod.',
            'output' => 'Tři věty: změna → důvod.',
            'time' => '8 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou počítačovou verzi a navrhuje jen mobil; pořadí sekcí vybírá z kartiček.',
          'standard' => 'Tři šířky podle zadání včetně důvodů změn.',
          'challenge' => 'Najde šířku mezi 390 a 768 px, kde se jeho návrh začne lámat, a navrhne vlastní bod zlomu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po mobilní kostře: každý přečte pořadí sekcí, soused řekne, zda souhlasí s prioritou.',
            1 => 'Rychlá otázka: proč nestačí stránku zmenšit?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Priorita na mobilu',
              'levels' => 
              array (
                0 => 'Jen zmenšená verze.',
                1 => 'Přeskupené, ale bez logiky.',
                2 => 'Pořadí podle priority obsahu.',
                3 => 'Zdůvodní pořadí potřebou uživatele.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Adaptace šířek',
              'levels' => 
              array (
                0 => 'Jedna šířka.',
                1 => 'Tři šířky, stejné rozvržení.',
                2 => 'Tři šířky s vědomými změnami.',
                3 => 'Vlastní bod zlomu podle obsahu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Čitelnost textu',
              'levels' => 
              array (
                0 => 'Text přes celou šířku.',
                1 => 'Omezený jen na jedné šířce.',
                2 => 'Rozumná šířka sloupce všude.',
                3 => 'Ověří na náhledu a doloží.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_css_layout',
          'competence_label' => 'Rozvržení a responzivita',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co znamená postup „mobile first“?',
              'options' => 
              array (
                0 => 'Web se dělá jen pro mobily.',
                1 => 'Nejdřív navrhnu nejužší verzi s nejdůležitějším obsahem a pak ji rozšiřuji.',
                2 => 'Mobilní verze se navrhuje až nakonec.',
              ),
              'correct' => 1,
              'explanation' => 'Úzký displej nutí rozhodnout, co je nejdůležitější.',
            ),
            1 => 
            array (
              'question' => 'Na tabletu máš dva sloupce textu po čtyřech slovech na řádek. Co uděláš?',
              'options' => 
              array (
                0 => 'Nechám to, dva sloupce jsou moderní.',
                1 => 'Zmenším písmo.',
                2 => 'Vrátím jeden sloupec – druhý tu obsahu nepomáhá.',
              ),
              'correct' => 2,
              'explanation' => 'Sloupec přidávám jen tam, kde obsahu pomůže.',
            ),
            2 => 
            array (
              'question' => 'Jak poznám, kde má být bod zlomu (breakpoint)?',
              'options' => 
              array (
                0 => 'Tam, kde se obsah začne lámat nebo špatně číst.',
                1 => 'Vždy přesně po 100 px.',
                2 => 'Podle velikosti mého monitoru.',
              ),
              'correct' => 0,
              'explanation' => 'Bod zlomu určuje obsah, ne pevná čísla.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: otevři svůj oblíbený web na mobilu i na počítači a zapiš dvě věci, které se přeskupily.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Pracuje se s vymyšleným obsahem; nic se nepublikuje.',
          1 => 'Při prohlížení cizích webů se nepřihlašuješ a nic neodesíláš.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: na mobilu skrýt polovinu obsahu. Skrývat jen to, co opravdu není potřeba.',
          1 => 'Otázka do třídy: Co musí uživatel na mobilu vidět bez posouvání?',
          2 => 'Tempo: tablet bývá rychlý – ušetřený čas dej verzi pro počítač.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci kreslí tři kostry na papír podle úkolů 1–4.',
          1 => 'Plán B offline: tři obdélníky (390 / 768 / 1440 v poměru) na A4.',
        ),
        'glossary' => 
        array (
          0 => 'mobile-first',
          1 => 'breakpoint',
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      12 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 12 · Komponenty: tlačítka, karty a opakovatelná pravidla',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím popsat tlačítko a kartu jako komponentu – opakovatelný vzor s pravidly a stavy, ne jednorázový obrázek.',
          'success_criteria' => 
          array (
            0 => 'Tlačítko má zapsanou výšku, vnitřní okraj, zaoblení a styl textu.',
            1 => 'Navrhnu stavy výchozí, fokus a nedostupné, které nejsou odlišené jen barvou.',
            2 => 'Karta používá stejnou škálu mezer jako tlačítko.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_a11y',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže web s pěti různě vypadajícími tlačítky pro stejnou akci.',
            'student' => 'Řeknou, proč je to matoucí.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Co je komponenta',
            'teacher' => 'Vysvětlí rozdíl jednorázového bloku a komponenty.',
            'student' => 'Najdou na ukázce tři opakované komponenty.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 40,
            'phase' => 'Tlačítka',
            'teacher' => 'Ukáže hlavní a vedlejší tlačítko.',
            'student' => 'Navrhnou obě tlačítka se stejnými rozměry.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Stavy',
            'teacher' => 'Předvede ovládání klávesnicí a viditelný fokus.',
            'student' => 'Doplní stavy výchozí, najetí (hover), fokus a nedostupné.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Karta',
            'teacher' => 'Hlídá stejné mezery jako u tlačítka.',
            'student' => 'Postaví kartu s názvem, popisem a akcí a použijí ji třikrát.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Spustí test: poznáš stav bez barev?',
            'student' => 'Převedou návrh do odstínů šedi a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Najdi na ukázkové stránce tři komponenty, které se opakují, a pojmenuj je podle funkce.',
            'output' => 'Tři pojmenované komponenty.',
            'time' => '10 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni hlavní a vedlejší tlačítko a zapiš výšku, vnitřní okraj (padding), zaoblení a styl textu.',
            'output' => 'Specifikace tlačítka.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Doplň stavy výchozí, najetí (hover), fokus (focus) a nedostupné; stav nesmí poznat jen podle barvy.',
            'output' => 'Řádek čtyř stavů.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Postav kartu s názvem, popisem a akcí a použij ji třikrát s různým obsahem.',
            'output' => 'Tři karty se shodnou strukturou.',
            'time' => '23 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane rozměry tlačítka předem; doplňuje jen stavy a skládá karty z hotových částí.',
          'standard' => 'Vlastní specifikace tlačítka, stavy a karta podle zadání.',
          'challenge' => 'Přidá variantu karty bez obrázku a ověří, že se struktura nerozpadne.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Test v šedi: soused pozná fokus a nedostupný stav bez barev?',
            1 => 'Rychlé ověření: mají všechny tři karty stejné mezery?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Pravidla tlačítka',
              'levels' => 
              array (
                0 => 'Bez zapsaných hodnot.',
                1 => 'Část hodnot zapsaná.',
                2 => 'Výška, okraj, zaoblení a text zapsané.',
                3 => 'Pravidla použije i pro nové tlačítko.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Stavy',
              'levels' => 
              array (
                0 => 'Jen výchozí stav.',
                1 => 'Stavy odlišené jen barvou.',
                2 => 'Čtyři stavy čitelné i v šedi.',
                3 => 'Vysvětlí, k čemu je fokus při ovládání klávesnicí.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Opakovatelnost karty',
              'levels' => 
              array (
                0 => 'Každá karta jiná.',
                1 => 'Podobné, nesouhlasí mezery.',
                2 => 'Tři karty se stejnou strukturou.',
                3 => 'Karta obstojí i bez obrázku.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_ux',
          'competence_label' => 'Ovládání a komponenty',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'K čemu slouží viditelný stav fokus u tlačítka?',
              'options' => 
              array (
                0 => 'Ukazuje, kde právě je ovládání z klávesnice.',
                1 => 'Je to jen ozdoba při najetí myší.',
                2 => 'Označuje tlačítko, které nefunguje.',
              ),
              'correct' => 0,
              'explanation' => 'Bez fokusu se uživatel klávesnice ztratí.',
            ),
            1 => 
            array (
              'question' => 'Proč má mít každá karta na webu stejné vnitřní mezery?',
              'options' => 
              array (
                0 => 'Aby se ušetřila paměť.',
                1 => 'Aby web působil uspořádaně a změna šla udělat na jednom místě.',
                2 => 'Na mezerách nezáleží.',
              ),
              'correct' => 1,
              'explanation' => 'Komponenta = jedno pravidlo použité mnohokrát.',
            ),
            2 => 
            array (
              'question' => 'Jak odlišíš nedostupné tlačítko, aby to poznal i člověk, který nerozliší barvy?',
              'options' => 
              array (
                0 => 'Jen šedou barvou.',
                1 => 'Jen menším písmem.',
                2 => 'Změnou kontrastu i textem nebo ikonou, proč akce nejde.',
              ),
              'correct' => 2,
              'explanation' => 'Význam nesmí nést jen barva.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na jednom webu najdi tlačítko v různých stavech (najetí, fokus po Tabu) a zapiš, čím se liší.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Ukázkové weby se jen prohlížejí, nic se neodesílá.',
          1 => 'Do karet se nepíšou skutečné osobní údaje.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: fokus = hover. Nech žáky projít stránku klávesou Tab.',
          1 => 'Otázka do třídy: Co se stane, když budeme chtít změnit všechna tlačítka najednou?',
          2 => 'Tempo: neřeš velký design systém – cílem je opakovatelnost dvou komponent.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci plní úkoly 1–4 podle lekce, výstupem je obrázek tlačítek a karet.',
          1 => 'Plán B offline: komponenty jako papírové výstřižky, stavy fixami.',
        ),
        'glossary' => 
        array (
          0 => 'hover',
          1 => 'focus',
          2 => 'padding',
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      13 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 13 · Webová typografie: čitelnost, délka řádku a rytmus',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout typografii pro obrazovku: pět textových rolí, čitelný odstavec a opakovaný rytmus mezer.',
          'success_criteria' => 
          array (
            0 => 'Pět rolí (velký nadpis, nadpis, text, drobný text, text akce) má každá svůj účel.',
            1 => 'Odstavec má zapsanou velikost, řádkování a maximální šířku.',
            2 => 'Nadpis se na šířce 390 px láme čitelně.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_typography',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_css_layout',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže stejný článek jako hustý blok a jako upravený text.',
            'student' => 'Odhadnou, který se čte rychleji a proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Textové role',
            'teacher' => 'Vysvětlí role a jejich účel.',
            'student' => 'Definují pět rolí a k čemu slouží.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Odstavec',
            'teacher' => 'Ukáže vliv délky řádku a řádkování.',
            'student' => 'Nastaví odstavci velikost, řádkování a maximální šířku.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Škála velikostí',
            'teacher' => 'Ukáže malou škálu místo deseti náhodných velikostí.',
            'student' => 'Sestaví škálu a otestují nadpis na 390 px.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 78,
            'phase' => 'Rytmus na stránce',
            'teacher' => 'Hlídá stejné mezery mezi nadpisem, textem a akcí.',
            'student' => 'Použijí role a mezery na článku se třemi sekcemi.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Test skenování a exit ticket',
            'teacher' => 'Spustí test: pochopíš stránku jen z nadpisů?',
            'student' => 'Vyzkouší test u souseda a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Definuj pět textových rolí a ke každé napiš, k čemu na stránce slouží.',
            'output' => 'Tabulka rolí a účelů.',
            'time' => '13 min',
          ),
          1 => 
          array (
            'text' => 'Pro odstavec zapiš velikost písma, řádkování a maximální šířku sloupce.',
            'output' => 'Tři hodnoty odstavce + ukázka.',
            'time' => '13 min',
          ),
          2 => 
          array (
            'text' => 'Sestav malou škálu velikostí a ověř, že se velký nadpis na šířce 390 px láme čitelně.',
            'output' => 'Škála + náhled nadpisu na mobilu.',
            'time' => '13 min',
          ),
          3 => 
          array (
            'text' => 'Nasaď role a mezery na článek se třemi sekcemi a jednou akcí.',
            'output' => 'Hotová stránka článku.',
            'time' => '22 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane hotovou škálu velikostí; přiřazuje role a ladí jen řádkování a šířku.',
          'standard' => 'Vlastní role, škála a stránka článku.',
          'challenge' => 'Porovná dvě písma pro text a zdůvodní volbu podle čitelnosti na mobilu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Ukaž roli: učitel přečte účel, žáci ukážou odpovídající text na své stránce.',
            1 => 'Test skenování: soused z nadpisů řekne, o čem článek je.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Role a účel',
              'levels' => 
              array (
                0 => 'Role chybí.',
                1 => 'Role bez účelu.',
                2 => 'Pět rolí s účelem.',
                3 => 'Role popíše tak, aby je použil kdokoli z týmu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Odstavec',
              'levels' => 
              array (
                0 => 'Bez úprav.',
                1 => 'Upravená jedna hodnota.',
                2 => 'Velikost, řádkování i šířka zapsané.',
                3 => 'Ověřeno na mobilu i počítači.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Rytmus',
              'levels' => 
              array (
                0 => 'Mezery náhodné.',
                1 => 'Rytmus jen v části.',
                2 => 'Mezery se opakují v celé stránce.',
                3 => 'Stránka projde testem skenování.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_typography',
          'competence_label' => 'Typografie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Které nastavení nejvíc pomůže čtení dlouhého textu na webu?',
              'options' => 
              array (
                0 => 'Omezená šířka sloupce a dostatečné řádkování.',
                1 => 'Text psaný celý velkými písmeny.',
                2 => 'Co nejmenší písmo, aby se vešlo víc.',
              ),
              'correct' => 0,
              'explanation' => 'Krátký řádek a vzduch mezi řádky usnadňují čtení.',
            ),
            1 => 
            array (
              'question' => 'Velký nadpis se na mobilu láme na pět řádků po jednom slově. Co uděláš?',
              'options' => 
              array (
                0 => 'Nechám to, na mobilu je to normální.',
                1 => 'Pro mobil zmenším roli nadpisu podle škály.',
                2 => 'Nadpis smažu.',
              ),
              'correct' => 1,
              'explanation' => 'Role mohou mít pro mobil vlastní velikost ze škály.',
            ),
            2 => 
            array (
              'question' => 'Co je „textová role“?',
              'options' => 
              array (
                0 => 'Jméno písma.',
                1 => 'Barva textu.',
                2 => 'Pojmenované použití textu s pevnými pravidly, např. nadpis sekce.',
              ),
              'correct' => 2,
              'explanation' => 'Role říká, k čemu text slouží a jak vypadá.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na webu zpráv změř (odhadni), kolik slov má jeden řádek článku, a porovnej s naší doporučenou šířkou.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Písma jen s licencí pro web; zdroj zapiš.',
          1 => 'Testovací text je vymyšlený nebo s uvedeným zdrojem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: honba za „správným číslem“. Vysvětluj role a důvody, ne magická čísla.',
          1 => 'Otázka do třídy: Dá se stránka pochopit bez barev, jen z písma?',
          2 => 'Tempo: úkol 4 je hlavní výstup – nedovol, aby škála zabrala víc než 15 minut.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 podle lekce, výstupem je obrázek stránky.',
          1 => 'Plán B offline: role nakreslené na papír, odstavec přepsaný do úzkého a širokého sloupce.',
        ),
        'glossary' => 
        array (
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      14 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 14 · Obraz pro web: ořez, rozměr a export',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím připravit obrázek pro web: zvolit jeho úlohu, ořez pro počítač i mobil, přiměřený rozměr exportu a alternativní text.',
          'success_criteria' => 
          array (
            0 => 'Určím, zda obrázek vysvětluje, dokumentuje, nebo jen zdobí.',
            1 => 'Připravím ořez pro počítač i mobil, který nezakryje důležitý detail.',
            2 => 'Napíšu alternativní text, nebo zdůvodním, že obrázek je jen dekorace.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_formats',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_a11y',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže web, který se načítá pomalu kvůli obřímu obrázku.',
            'student' => 'Odhadnou, proč stránka čeká.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Úloha obrázku',
            'teacher' => 'Vysvětlí tři úlohy: vysvětluje, dokumentuje, zdobí.',
            'student' => 'Zařadí pět ukázkových obrázků.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 40,
            'phase' => 'Ořez pro dvě šířky',
            'teacher' => 'Ukáže ořez úvodní fotky pro počítač a mobil.',
            'student' => 'Připraví dva ořezy jedné fotky.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 58,
            'phase' => 'Export',
            'teacher' => 'Vysvětlí zdrojový soubor a webový export a přiměřený rozměr.',
            'student' => 'Exportují obrázek v rozměru podle použití.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 58,
            'to' => 78,
            'phase' => 'Alternativní text',
            'teacher' => 'Ukáže dobrý a špatný alt text.',
            'student' => 'Napíšou alt text nebo zdůvodní dekoraci.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 78,
            'to' => 90,
            'phase' => 'Kontrola a exit ticket',
            'teacher' => 'Projde kontrolní seznam obrázku.',
            'student' => 'Zkontrolují export mimo editor a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Zařaď pět ukázkových obrázků podle úlohy: vysvětluje, dokumentuje, nebo jen zdobí.',
            'output' => 'Pět obrázků s úlohou.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Připrav ořez (crop) jedné fotky pro počítač a pro mobil tak, aby nezakryl důležitý detail.',
            'output' => 'Dva ořezy.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Exportuj obrázek v rozměru podle místa použití a zapiš cílovou šířku v pixelech.',
            'output' => 'Export + zapsaný rozměr.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Napiš alternativní text (alt text) pro informační obrázek nebo zdůvodni, proč je obrázek jen dekorace.',
            'output' => 'Alt text nebo zdůvodnění.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane fotku s vyznačeným ohniskem a šablonu alt textu „Co je na obrázku + proč to tu je“.',
          'standard' => 'Vlastní ořezy, export a alt text podle zadání.',
          'challenge' => 'Porovná velikost souboru dvou exportů (různá kvalita) a vybere přiměřený s jedním argumentem.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po zařazení: učitel ukáže obrázek, třída zvedne 1/2/3 prsty podle úlohy.',
            1 => 'Alt text nahlas: soused bez obrázku řekne, co si představil.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Úloha obrázku',
              'levels' => 
              array (
                0 => 'Nerozliší úlohy.',
                1 => 'Zařadí s chybami.',
                2 => 'Správně zařadí a zdůvodní.',
                3 => 'Navrhne vypustit zbytečnou dekoraci.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Ořez a export',
              'levels' => 
              array (
                0 => 'Originál bez úprav.',
                1 => 'Ořez bez ohledu na detail.',
                2 => 'Dva ořezy a přiměřený rozměr.',
                3 => 'Porovná velikost souborů a vybere rozumně.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Alternativní text',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen „obrázek“ nebo název souboru.',
                2 => 'Popisuje obsah a účel.',
                3 => 'Rozliší informační obrázek a dekoraci.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_formats',
          'competence_label' => 'Formáty a export',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který alternativní text je pro graf návštěvnosti nejlepší?',
              'options' => 
              array (
                0 => 'obrazek1.png',
                1 => 'Graf návštěvnosti: v září vzrostla o třetinu proti srpnu.',
                2 => 'Graf',
              ),
              'correct' => 1,
              'explanation' => 'Alt text předává informaci, kterou obrázek nese.',
            ),
            1 => 
            array (
              'question' => 'Obrázek bude na webu široký nejvýš 800 px. Jak ho exportuješ?',
              'options' => 
              array (
                0 => 'V rozměru blízkém použití, ne v plném rozlišení fotoaparátu.',
                1 => 'V plném rozlišení, ať je ostrý.',
                2 => 'Jako snímek obrazovky.',
              ),
              'correct' => 0,
              'explanation' => 'Přiměřený rozměr = rychlejší stránka.',
            ),
            2 => 
            array (
              'question' => 'Fotka je na webu čistě dekorativní. Co s alternativním textem?',
              'options' => 
              array (
                0 => 'Napíšu dlouhý popis každého detailu.',
                1 => 'Do alt textu dám klíčová slova.',
                2 => 'Označím ji jako dekoraci (prázdný alt), aby ji čtečka přeskočila.',
              ),
              'correct' => 2,
              'explanation' => 'Dekorace nemá rušit uživatele čtečky.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyfoť jednu vlastní fotku a napiš k ní alt text do 15 slov.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Fotky vlastní nebo s licencí; fotky lidí jen se souhlasem.',
          1 => 'Před sdílením fotky zkontroluj, že neprozrazuje polohu ani osobní údaje.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Neuč pevné limity v kB bez kontextu – uč přiměřenost podle použití.',
          1 => 'Otázka do třídy: Co ztratí uživatel čtečky, když alt text chybí?',
          2 => 'Tempo: export bývá technicky zdlouhavý – připrav si postup v editoru na tabuli.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1 a 4 jdou na papíře, úkoly 2–3 podle učebny.',
          1 => 'Plán B offline: ořez rámečkem z papíru na vytištěné fotce, alt text písemně.',
        ),
        'glossary' => 
        array (
          0 => 'crop',
          1 => 'alt-text',
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      15 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 15 · Formuláře a přístupnost: stav musí být pochopitelný',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout jednoduchý formulář s popisky, viditelným fokusem, srozumitelnou chybou a potvrzením odeslání.',
          'success_criteria' => 
          array (
            0 => 'Každé pole má trvalý popisek, ne jen zástupný text v poli.',
            1 => 'Chyba je vyjádřená textem i ikonou a říká, jak ji opravit.',
            2 => 'Formulář je pochopitelný i v odstínech šedi.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_a11y',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Ukáže formulář, kde po kliknutí zmizí nápověda a chyba je jen červená.',
            'student' => 'Zkusí odhadnout, co je špatně.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Části formuláře',
            'teacher' => 'Pojmenuje popisek, pole, nápovědu, chybu a odeslání.',
            'student' => 'Označí části na ukázce.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 38,
            'phase' => 'Výchozí stav a fokus',
            'teacher' => 'Předvede průchod formulářem klávesou Tab.',
            'student' => 'Navrhnou pole ve výchozím stavu a s fokusem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 38,
            'to' => 55,
            'phase' => 'Chyba',
            'teacher' => 'Ukáže chybovou hlášku „co se stalo + jak to opravit“.',
            'student' => 'Navrhnou chybový stav a napíšou hlášku.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 75,
            'phase' => 'Odesílání a úspěch',
            'teacher' => 'Vysvětlí stav „odesílám“ a potvrzení.',
            'student' => 'Navrhnou tlačítko při odesílání a potvrzení.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 75,
            'to' => 90,
            'phase' => 'Test v šedi a exit ticket',
            'teacher' => 'Nechá převést návrhy do šedi.',
            'student' => 'Ověří, že stavy jsou pochopitelné, a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Na ukázce označ popisek (label), pole, nápovědu, chybovou hlášku a tlačítko odeslání.',
            'output' => 'Popsaný formulář.',
            'time' => '10 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni pole e-mailu ve stavech výchozí, fokus, chyba a vyplněno správně.',
            'output' => 'Řádek čtyř stavů pole.',
            'time' => '25 min',
          ),
          2 => 
          array (
            'text' => 'Napiš chybovou hlášku, která říká, co se stalo a jak to opravit, a umísti ji u pole.',
            'output' => 'Text hlášky + umístění.',
            'time' => '10 min',
          ),
          3 => 
          array (
            'text' => 'Navrhni tlačítko ve stavu „odesílám“ a obrazovku potvrzení; celé to zkontroluj v odstínech šedi.',
            'output' => 'Stav odesílání, potvrzení a test v šedi.',
            'time' => '20 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu pole a vzorovou hlášku; upravuje ji pro jiné pole (telefon).',
          'standard' => 'Čtyři stavy pole, vlastní hláška, odesílání a potvrzení.',
          'challenge' => 'Přidá souhrn chyb nahoře formuláře s odkazy na chybná pole a vysvětlí, komu pomáhá.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Tab test: učitel projde jeden návrh „klávesnicí“ – je vidět, kde jsem?',
            1 => 'Hláška nahlas: třída řekne, zda ví, co opravit.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Popisky a struktura',
              'levels' => 
              array (
                0 => 'Jen zástupný text.',
                1 => 'Popisky u části polí.',
                2 => 'Trvalé popisky u všech polí.',
                3 => 'Doplněná nápověda tam, kde je potřeba.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Chybový stav',
              'levels' => 
              array (
                0 => 'Jen červený rámeček.',
                1 => 'Text bez návodu.',
                2 => 'Text + ikona + jak opravit.',
                3 => 'Souhrn chyb s odkazy.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Stavy a přístupnost',
              'levels' => 
              array (
                0 => 'Jen výchozí stav.',
                1 => 'Stavy jen barvou.',
                2 => 'Fokus, chyba, odesílání, úspěch čitelné v šedi.',
                3 => 'Vysvětlí, komu které řešení pomáhá.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_a11y',
          'competence_label' => 'Přístupnost webu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč nestačí zástupný text (placeholder) místo popisku pole?',
              'options' => 
              array (
                0 => 'Po začátku psaní zmizí a uživatel neví, co pole chtělo.',
                1 => 'Zástupný text nejde obarvit.',
                2 => 'Prohlížeče ho nezobrazují.',
              ),
              'correct' => 0,
              'explanation' => 'Popisek musí zůstat vidět celou dobu.',
            ),
            1 => 
            array (
              'question' => 'Která chybová hláška je nejlepší?',
              'options' => 
              array (
                0 => 'Chyba 12.',
                1 => 'Neplatný vstup.',
                2 => 'E-mail musí obsahovat zavináč, například jana@example.com.',
              ),
              'correct' => 2,
              'explanation' => 'Hláška říká, co je špatně a jak to opravit.',
            ),
            2 => 
            array (
              'question' => 'Co má udělat tlačítko po kliknutí na „Odeslat“, než přijde odpověď?',
              'options' => 
              array (
                0 => 'Zmizet beze stopy.',
                1 => 'Ukázat stav „Odesílám…“ a nepovolit druhé kliknutí.',
                2 => 'Nic, uživatel počká.',
              ),
              'correct' => 1,
              'explanation' => 'Uživatel má vědět, že se něco děje.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na jednom webovém formuláři zkus projít pole jen klávesou Tab a zapiš, zda bylo vidět, kde jsi.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Do žádného skutečného formuláře nic neodesíláme; ukázkové údaje jsou vymyšlené.',
          1 => 'Vzorové e-maily jen ve tvaru jméno@example.com (rezervovaná ukázková doména) s vymyšleným jménem.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Fokus je designový stav, ne technická poznámka – trvej na něm.',
          1 => 'Otázka do třídy: Pozná chybu člověk, který nerozliší červenou?',
          2 => 'Tempo: čtyři stavy pole jsou jádro hodiny – potvrzení může být jednodušší.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: úkoly 1–4 podle lekce; test v šedi lze udělat i černobílým tiskem.',
          1 => 'Plán B offline: stavy pole kreslené tužkou, chyba fixou + ikona.',
        ),
        'glossary' => 
        array (
          0 => 'label',
          1 => 'placeholder',
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      16 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 16 · Úvodní stránka: drátěný model od zadání k průchodu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím převést krátké zadání na strukturu úvodní stránky (landing page) a obhájit pořadí sekcí dřív, než řeším vzhled.',
          'success_criteria' => 
          array (
            0 => 'Zadání shrnu do čtyř vět: pro koho, jaký problém, co nabízím, jaká je hlavní akce.',
            1 => 'Sekce mají pořadí, u každé napíšu otázku, na kterou odpovídá.',
            2 => 'Po testu se spolužákem udělám aspoň jednu změnu podle pozorování.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_ux',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'web_html_structure',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start',
            'teacher' => 'Představí zadání vymyšleného školního kroužku.',
            'student' => 'Řeknou, co má návštěvník po přečtení udělat.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 22,
            'phase' => 'Zadání ve čtyřech větách',
            'teacher' => 'Ukáže vzor: pro koho, problém, nabídka, akce.',
            'student' => 'Napíšou zadání ve čtyřech větách.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 22,
            'to' => 35,
            'phase' => 'Soupis obsahu',
            'teacher' => 'Pomáhá vyškrtnout ozdobný text bez funkce.',
            'student' => 'Sepíšou, co stránka musí obsahovat.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 35,
            'to' => 50,
            'phase' => 'Pořadí sekcí',
            'teacher' => 'Ukáže vzor úvod → důvěra → přínos → detail → akce.',
            'student' => 'Seřadí sekce a připíšou otázky.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 50,
            'to' => 72,
            'phase' => 'Drátěný model',
            'teacher' => 'Hlídá, aby nevznikaly finální barvy a fotky.',
            'student' => 'Nakreslí wireframe pro počítač a mobil.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 72,
            'to' => 90,
            'phase' => 'Test průchodu a exit ticket',
            'teacher' => 'Řídí 3minutový test ve dvojicích.',
            'student' => 'Spolužák popíše, co by udělal první; zapíší změnu a odpoví na exit ticket.',
            'form' => 've dvojicích',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš zadání ve čtyřech větách: pro koho stránka je, jaký problém řeší, co nabízí a jaká je jediná hlavní akce.',
            'output' => 'Zadání ve 4 větách.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Sepiš obsah, který stránka musí mít, a škrtni duplicity a ozdobný text bez funkce.',
            'output' => 'Soupis obsahu.',
            'time' => '13 min',
          ),
          2 => 
          array (
            'text' => 'Seřaď 5–7 sekcí a u každé napiš otázku návštěvníka, na kterou odpovídá.',
            'output' => 'Pořadí sekcí s otázkami.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Nakresli drátěný model (wireframe) pro počítač i mobil a po testu se spolužákem udělej jednu změnu.',
            'output' => 'Dva wireframy + zapsaná změna a důvod.',
            'time' => '35 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičky sekcí a vzorové otázky; skládá pořadí a kreslí jen počítačovou verzi.',
          'standard' => 'Zadání, soupis, pořadí a dva wireframy podle zadání.',
          'challenge' => 'Navrhne druhé pořadí sekcí pro jinou cílovou skupinu (rodiče místo žáků) a porovná je.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Po pořadí sekcí: dvojice si přečtou otázky – odpovídá sekce opravdu na svou otázku?',
            1 => 'Test průchodu: co by návštěvník udělal jako první?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Zadání',
              'levels' => 
              array (
                0 => 'Chybí cíl.',
                1 => 'Cíl je, chybí cílová skupina nebo akce.',
                2 => 'Čtyři věty včetně hlavní akce.',
                3 => 'Zadání je tak jasné, že podle něj pracuje i spolužák.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Struktura a pořadí',
              'levels' => 
              array (
                0 => 'Náhodné sekce.',
                1 => 'Sekce bez otázek.',
                2 => 'Pořadí s otázkami návštěvníka.',
                3 => 'Pořadí obhájí proti alternativě.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Test a iterace',
              'levels' => 
              array (
                0 => 'Bez testu.',
                1 => 'Test bez záznamu.',
                2 => 'Zapsaná změna podle pozorování.',
                3 => 'Odliší pozorování od názoru spolužáka.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_ux',
          'competence_label' => 'Ovládání a komponenty',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Při testu průchodu se spolužáka zeptáš:',
              'options' => 
              array (
                0 => '„Líbí se ti to?“',
                1 => '„Co bys tady udělal jako první?“',
                2 => '„Vidíš to modré tlačítko vpravo?“',
              ),
              'correct' => 1,
              'explanation' => 'Ptáme se na chování, ne na názor, a nenavádíme.',
            ),
            1 => 
            array (
              'question' => 'K čemu slouží sekce „důvěra“ na úvodní stránce?',
              'options' => 
              array (
                0 => 'Dodává důkazy, proč nabídce věřit (zkušenosti, čísla, ukázky).',
                1 => 'Je tam jen logo školy.',
                2 => 'Obsahuje právní podmínky.',
              ),
              'correct' => 0,
              'explanation' => 'Důvěra odpovídá na otázku „proč zrovna tohle?“.',
            ),
            2 => 
            array (
              'question' => 'Kdy je správný čas vybírat finální fotky a barvy?',
              'options' => 
              array (
                0 => 'Hned na začátku, aby se dobře pracovalo.',
                1 => 'Během psaní zadání.',
                2 => 'Až po ověření struktury a průchodu.',
              ),
              'correct' => 2,
              'explanation' => 'Nejdřív struktura, pak styl.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi jednu úvodní stránku (např. kroužku nebo akce) a vypiš pořadí jejích sekcí.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zadání je vymyšlené; na stránku se nepíší skutečné kontakty ani jména.',
          1 => 'Test průchodu probíhá jen ve třídě, bez nahrávání.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Nechte studenty testovat průchod bez vizuálního efektu – wow efekt maskuje problémy.',
          1 => 'Otázka do třídy: Na jakou otázku návštěvníka odpovídá tahle sekce?',
          2 => 'Tempo: wireframe nesmí sebrat čas testu – v 72. minutě přejdi k testu.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá zadání, žáci plní úkoly 1–4 na papír, test proběhne ve dvojicích.',
          1 => 'Plán B offline: celá hodina na papíře s kartičkami sekcí.',
        ),
        'glossary' => 
        array (
          0 => 'landing-page',
          1 => 'wireframe',
        ),
        '_file' => 'lesson_content_v72_1a_b.php',
      ),
      17 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 17 · Malá sada pravidel UI: tokeny místo náhodných hodnot',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím sepsat malou sadu pravidel rozhraní – barvy s rolemi, škálu mezer a dvě velikosti písma – a podle ní poskládat tlačítko a kartu.',
          'success_criteria' => 
          array (
            0 => 'Každá barva v sadě má jméno podle role (např. text, akcent), ne podle odstínu.',
            1 => 'Mezery používám jen ze škály 4, 8, 16, 24, 32 px.',
            2 => 'Tlačítko i karta jsou postavené jen z hodnot sady, bez jediné „náhodné“ hodnoty.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_typography',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: dvě karty',
            'teacher' => 'Ukáže dvě karty – jednu s mezerami 7, 13 a 22 px, druhou se škálou 8, 16 a 24 px.',
            'student' => 'Hledají, proč druhá působí klidněji.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Barvy s rolemi',
            'teacher' => 'Vysvětlí pojmenování podle role (barva-text, barva-akcent) místo „modrá-2“.',
            'student' => 'Pojmenují 4 barvy své sady a zapíší HEX kódy.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Škála mezer a písma',
            'teacher' => 'Předvede škálu 4–32 px a dvě velikosti písma (16 a 24 px).',
            'student' => 'Doplní tabulku tokenů o mezery a písmo.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 65,
            'phase' => 'Tlačítko a karta z tokenů',
            'teacher' => 'Obchází a ptá se: „Ze kterého tokenu je tahle hodnota?“',
            'student' => 'Poskládají tlačítko a kartu jen z hodnot sady.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Kontrola souseda',
            'teacher' => 'Zadá párovou kontrolu „najdi hodnotu mimo sadu“.',
            'student' => 'Vymění si návrhy a označí hodnoty, které v sadě nejsou.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Shrnutí a exit ticket',
            'teacher' => 'Shrne: pravidlo šetří rozhodování.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Pojmenuj 4 barvy své sady podle role (pozadí, text, akcent, rámeček) a ke každé zapiš HEX kód.',
            'output' => 'Tabulka čtyř barevných tokenů se jménem role a HEX kódem.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Doplň do tabulky tokenů škálu mezer 4, 8, 16, 24, 32 px a dvě velikosti písma (běžný text 16 px, nadpis 24 px).',
            'output' => 'Tabulka tokenů: barvy, mezery a písmo na jedné straně.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni tlačítko a kartu výhradně z hodnot své sady a u každé hodnoty napiš jméno tokenu.',
            'output' => 'Tlačítko a karta s popisky tokenů (např. mezera-16, barva-akcent).',
            'time' => '25 min',
          ),
          3 => 
          array (
            'text' => 'Zkontroluj návrh spolužáka: označ každou hodnotu, která v jeho sadě není, a navrhni nejbližší token.',
            'output' => 'Seznam nalezených hodnot mimo sadu s návrhem náhrady.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane předvyplněnou tabulku tokenů s barvami; doplňuje jen mezery a skládá tlačítko podle předlohy.',
          'standard' => 'Vlastní sada tokenů, tlačítko a karta s popisky a párová kontrola.',
          'challenge' => 'Doplní tmavou variantu sady se stejnými jmény rolí a ověří, že karta funguje v obou.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Rychlá kontrola: učitel ukáže hodnotu 13 px a třída řekne nejbližší token ze škály.',
            1 => 'Párová kontrola: kolik hodnot mimo sadu soused našel?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Jména tokenů',
              'levels' => 
              array (
                0 => 'Barvy bez jmen.',
                1 => 'Jména podle odstínu.',
                2 => 'Jména podle role s HEX kódem.',
                3 => 'Jména drží i v tmavé variantě.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Škála mezer',
              'levels' => 
              array (
                0 => 'Mezery náhodně.',
                1 => 'Škála existuje, ale nedodržuje se.',
                2 => 'Všechny mezery ze škály.',
                3 => 'Zdůvodní, proč je škála násobkem 4 nebo 8.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Komponenty z tokenů',
              'levels' => 
              array (
                0 => 'Komponenty nevznikly.',
                1 => 'Část hodnot mimo sadu.',
                2 => 'Tlačítko i karta jen z tokenů s popisky.',
                3 => 'Najde a opraví hodnoty mimo sadu i u souseda.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_ux',
          'competence_label' => 'Ovládání a výzva k akci',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Které jméno barevného tokenu je nejlepší?',
              'options' => 
              array (
                0 => 'modra-tmava-2',
                1 => 'barva-akcent',
                2 => 'nova-barva',
              ),
              'correct' => 1,
              'explanation' => 'Jméno podle role přežije i změnu odstínu.',
            ),
            1 => 
            array (
              'question' => 'Škála mezer je 4, 8, 16, 24 a 32 px. V návrhu je mezera 15 px. Co uděláš?',
              'options' => 
              array (
                0 => 'Nechám ji, rozdíl není vidět.',
                1 => 'Přidám 15 do škály.',
                2 => 'Nahradím ji tokenem 16 px.',
              ),
              'correct' => 2,
              'explanation' => 'Hodnota mimo sadu se nahradí nejbližším tokenem.',
            ),
            2 => 
            array (
              'question' => 'Proč se vyplatí stavět komponenty jen z tokenů?',
              'options' => 
              array (
                0 => 'Změna tokenu se projeví všude a návrh zůstane jednotný.',
                1 => 'Soubor návrhu je pak menší.',
                2 => 'Tokeny zakazují používat barvy.',
              ),
              'correct' => 0,
              'explanation' => 'Jedno místo pravdy = konzistence a rychlé změny.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: na oblíbeném webu najdi tři mezery mezi prvky a odhadni, jestli patří do jedné škály.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Barvy a písma jen z volně použitelných zdrojů s licencí; zdroj písma zapiš do sady.',
          1 => 'V ukázkových kartách nejsou jména ani fotky spolužáků.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: tokeny (anglicky design token) pojmenované podle odstínu – po změně barvy jméno lže.',
          1 => 'Otázka do třídy: Kolik různých mezer snese jedna obrazovka, aby působila klidně?',
          2 => 'Tempo: když skládání komponent trvá déle, kartu dokončete příště a párovou kontrolu udělejte na tlačítku.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá šablonu tabulky tokenů, žáci plní úkoly 1–4 na papíře nebo v editoru.',
          1 => 'Plán B offline: tokeny jako barevné kartičky a mezery odměřené pravítkem ve čtverečkovaném sešitu.',
        ),
        'glossary' => 
        array (
          0 => 'design-token',
          1 => 'spacing',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      18 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 18 · Závěrečný projekt: responzivní landing page',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím postavit jednoduchou landing page se sémantickou kostrou, čitelným písmem a rozvržením, které funguje na mobilu i na počítači.',
          'success_criteria' => 
          array (
            0 => 'Stránka má header, main se sekcemi a footer a jeden nadpis h1.',
            1 => 'Na šířce 390 px se nic nepřekrývá a text nejde mimo obrazovku.',
            2 => 'Formulář má u každého pole viditelný label.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_html_structure',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_css_layout',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Zadání projektu',
            'teacher' => 'Představí zadání (vymyšlený kroužek, akce nebo služba) a kontrolní seznam.',
            'student' => 'Vyberou téma a zapíší jedno hlavní sdělení stránky.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kostra HTML',
            'teacher' => 'Připomene header, main, section a footer.',
            'student' => 'Napíší kostru se třemi sekcemi a jedním h1.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Písmo a rozvržení',
            'teacher' => 'Ukáže postup mobile first: nejdřív jeden sloupec, pak breakpoint.',
            'student' => 'Nastaví písmo, mezery a jeden breakpoint pro širší obrazovku.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Formulář přihlášky',
            'teacher' => 'Ukáže label propojený s polem a chybovou hlášku.',
            'student' => 'Doplní formulář se dvěma poli a tlačítkem.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Test na mobilu',
            'teacher' => 'Vede kontrolu v náhledu 390 px a klávesnicí.',
            'student' => 'Projdou kontrolní seznam a opraví první chybu.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Odevzdání a exit ticket',
            'teacher' => 'Vybere dvě stránky na rychlou ukázku.',
            'student' => 'Odevzdají soubory a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš kostru landing page: header, main se třemi sekcemi (co, pro koho, přihláška), footer a jeden nadpis h1.',
            'output' => 'Soubor index.html s platnou kostrou a logickými nadpisy.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Nastav písmo s velikostí běžného textu 16 px, výškou řádku 1,5 a jeden breakpoint, od kterého se sekce řadí vedle sebe.',
            'output' => 'Soubor styl.css s pravidly pro mobil a jedním breakpointem.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Doplň formulář přihlášky se dvěma poli (jméno akce, e-mail ve tvaru jan@example.com) a s viditelným labelem u každého pole.',
            'output' => 'Formulář, kde kliknutí na label přesune kurzor do pole.',
            'time' => '20 min',
          ),
          3 => 
          array (
            'text' => 'Projdi stránku na šířce 390 px a jen klávesnicí (Tab) a zapiš první nalezenou chybu i její opravu.',
            'output' => 'Řádek do kontrolního seznamu: chyba → oprava → ověřeno.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru HTML s komentáři, doplňuje texty sekcí a styly podle předlohy.',
          'standard' => 'Celá stránka podle zadání včetně formuláře a testu na mobilu.',
          'challenge' => 'Přidá druhý breakpoint a ověří, že pořadí čtení při Tab odpovídá pořadí na obrazovce.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola kostry: zvednuté ruce – kdo má jen jeden h1?',
            1 => 'Dvojice: soused projde stránku Tabem a řekne, kde se ztratil.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Sémantická kostra',
              'levels' => 
              array (
                0 => 'Jen div bez struktury.',
                1 => 'Části kostry chybí.',
                2 => 'header, main se sekcemi, footer a jeden h1.',
                3 => 'Nadpisy tvoří logickou osnovu i bez stylů.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Responzivita',
              'levels' => 
              array (
                0 => 'Na mobilu se obsah překrývá.',
                1 => 'Mobil funguje, širší obrazovka ne.',
                2 => 'Mobil i breakpoint bez překryvů.',
                3 => 'Dva breakpointy a stejné pořadí čtení.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Přístupný formulář',
              'levels' => 
              array (
                0 => 'Pole bez popisku.',
                1 => 'Popisek jen jako placeholder.',
                2 => 'Viditelný label propojený s polem.',
                3 => 'Srozumitelná chybová hláška u pole.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_html_structure',
          'competence_label' => 'Sémantická kostra webu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Kolik nadpisů h1 má mít jedna landing page?',
              'options' => 
              array (
                0 => 'Jeden – hlavní téma stránky.',
                1 => 'Jeden v každé sekci.',
                2 => 'Žádný, stačí tučný text.',
              ),
              'correct' => 0,
              'explanation' => 'h1 je jedno hlavní téma; sekce používají h2.',
            ),
            1 => 
            array (
              'question' => 'Co znamená postup mobile first?',
              'options' => 
              array (
                0 => 'Stránka se dělá jen pro mobil.',
                1 => 'Nejdřív styly pro úzkou obrazovku, širší rozvržení až od breakpointu.',
                2 => 'Na mobilu se skryje polovina obsahu.',
              ),
              'correct' => 1,
              'explanation' => 'Základ platí pro mobil, breakpoint přidává rozvržení pro větší šířku.',
            ),
            2 => 
            array (
              'question' => 'Proč nestačí placeholder místo labelu?',
              'options' => 
              array (
                0 => 'Placeholder je moc velký.',
                1 => 'Placeholder nejde obarvit.',
                2 => 'Po začátku psaní zmizí a čtečka ho nemusí přečíst jako popisek.',
              ),
              'correct' => 2,
              'explanation' => 'Label zůstává vidět a je s polem propojený.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: otevři svou stránku na telefonu a zapiš jednu věc, která se tam chová jinak než v počítači.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Téma stránky je vymyšlené; žádná skutečná jména, telefonní čísla ani e-maily (jen tvar jan@example.com).',
          1 => 'Obrázky jen vlastní nebo s licencí; zdroj uveď ve footeru.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: rozvržení nejdřív pro počítač a pak „oprava“ mobilu – vracej žáky k jednomu sloupci.',
          1 => 'Otázka do třídy: Co uvidí člověk na mobilu jako první a je to to hlavní?',
          2 => 'Tempo: když formulář nestihnou, odevzdají kostru a styly a formulář dokončí v L20.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: vytiskne kontrolní seznam, žáci plní úkoly 1–4 a odevzdají soubory.',
          1 => 'Plán B offline: drátěný model stránky na papíře pro mobil a pro počítač se šipkami pořadí čtení.',
        ),
        'glossary' => 
        array (
          0 => 'landing-page',
          1 => 'mobile-first',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      19 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 19 · Vizuální audit: jak dávat užitečnou zpětnou vazbu',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím projít návrh podle kontrolního seznamu a dát zpětnou vazbu ve tvaru pozorování → dopad → návrh, která se týká návrhu, ne autora.',
          'success_criteria' => 
          array (
            0 => 'Každá moje poznámka obsahuje pozorování, dopad na uživatele a konkrétní návrh.',
            1 => 'Najdu aspoň jeden problém v hierarchii a jeden v čitelnosti.',
            2 => 'Zpětnou vazbu formuluji k návrhu („nadpis splývá“), ne k osobě.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: dvě věty',
            'teacher' => 'Přečte dvě zpětné vazby: „je to hnusné“ a „nadpis splývá s pozadím, zvětši kontrast“.',
            'student' => 'Řeknou, se kterou se dá pracovat a proč.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Kontrolní seznam',
            'teacher' => 'Představí 5 bodů: hierarchie, mezery, kontrast, zarovnání, výzva k akci.',
            'student' => 'Projdou ukázkový návrh a u každého bodu napíší ano/ne.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Pozorování → dopad → návrh',
            'teacher' => 'Ukáže tvar poznámky na jednom příkladu.',
            'student' => 'Přepíší tři své poznámky do tohoto tvaru.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 70,
            'phase' => 'Audit ve dvojicích',
            'teacher' => 'Hlídá tón poznámek a čas.',
            'student' => 'Auditují landing page spolužáka z L18 a zapíší 3 poznámky.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Priorita oprav',
            'teacher' => 'Vysvětlí, že první se opravuje to, co brání použití.',
            'student' => 'Seřadí přijaté poznámky podle dopadu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne tři části dobré poznámky.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Projdi ukázkový návrh podle pěti bodů kontrolního seznamu (hierarchie, mezery, kontrast, zarovnání, výzva k akci) a u každého napiš ano/ne.',
            'output' => 'Vyplněný kontrolní seznam s pěti odpověďmi.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Přepiš tři své poznámky do tvaru pozorování → dopad → návrh.',
            'output' => 'Tři poznámky, např. „Text tlačítka je šedý na bílé → špatně se čte → ztmav ho“.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Proveď audit landing page spolužáka a zapiš tři poznámky: jednu k hierarchii, jednu k čitelnosti a jednu k výzvě k akci.',
            'output' => 'Tři poznámky ve správném tvaru předané autorovi.',
            'time' => '25 min',
          ),
          3 => 
          array (
            'text' => 'Seřaď poznámky, které jsi dostal(a), podle dopadu na uživatele a vyber jednu, kterou opravíš jako první.',
            'output' => 'Pořadí poznámek a jedna vybraná oprava se zdůvodněním.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičku se začátky vět („Všiml(a) jsem si…“, „Uživatel proto…“, „Zkus…“).',
          'standard' => 'Kontrolní seznam, tři poznámky ve správném tvaru a seřazení podle dopadu.',
          'challenge' => 'Ke každé poznámce navrhne, jak by ověřil(a), že oprava pomohla (např. test tří sekund).',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel čte poznámky a třída zvedne kartu: chybí pozorování, dopad, nebo návrh?',
            1 => 'Kontrola tónu: přepiš poznámku o osobě na poznámku o návrhu.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Tvar poznámky',
              'levels' => 
              array (
                0 => 'Jen hodnocení („hezké“).',
                1 => 'Pozorování bez dopadu.',
                2 => 'Pozorování, dopad i návrh.',
                3 => 'Navrhne i ověření opravy.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Věcnost',
              'levels' => 
              array (
                0 => 'Poznámka o autorovi.',
                1 => 'Smíšený tón.',
                2 => 'Poznámky jen o návrhu.',
                3 => 'Pomůže autorovi zvolit prioritu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Priorita',
              'levels' => 
              array (
                0 => 'Bez pořadí.',
                1 => 'Pořadí podle vkusu.',
                2 => 'Pořadí podle dopadu na uživatele.',
                3 => 'Zdůvodní pořadí i u sporné poznámky.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_composition',
          'competence_label' => 'Kompozice a hierarchie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Která zpětná vazba má tvar pozorování → dopad → návrh?',
              'options' => 
              array (
                0 => 'Ten nadpis se mi nelíbí.',
                1 => 'Nadpis má stejnou velikost jako text, uživatel neví, kde začít – zvětši ho.',
                2 => 'Uděláš to celé znovu.',
              ),
              'correct' => 1,
              'explanation' => 'Popisuje, co vidím, proč to vadí a co zkusit.',
            ),
            1 => 
            array (
              'question' => 'Co opravíš jako první?',
              'options' => 
              array (
                0 => 'Tlačítko přihlášky, které na mobilu není vidět.',
                1 => 'Odstín šedé ve footeru.',
                2 => 'Zaoblení rohů karet.',
              ),
              'correct' => 0,
              'explanation' => 'Nejdřív to, co brání použití stránky.',
            ),
            2 => 
            array (
              'question' => 'Jak přepíšeš poznámku „jsi líný, je tu chaos“?',
              'options' => 
              array (
                0 => 'Nechám ji, je upřímná.',
                1 => 'Napíšu jen „chaos“.',
                2 => 'Prvky nejsou zarovnané, oko skáče – zarovnej je k jedné hraně.',
              ),
              'correct' => 2,
              'explanation' => 'Poznámka patří k návrhu, ne k autorovi.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: napiš jednu poznámku ve tvaru pozorování → dopad → návrh k plakátu, který potkáš cestou domů.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Auditujeme jen práce spolužáků, kteří s tím souhlasí, a jen ve třídě; nic se nesdílí ven.',
          1 => 'Poznámky nejsou známka – nehodnotíme autora, ale návrh.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: zpětná vazba jen pochvalou nebo jen kritikou bez návrhu – vyžaduj všechny tři části.',
          1 => 'Otázka do třídy: Jak poznáš, že poznámka autorovi pomohla?',
          2 => 'Tempo: audit ve dvojicích je jádro hodiny – kontrolní seznam zkrať, pokud start trvá déle.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá kontrolní seznam a kartičky se začátky vět; žáci auditují vytištěné návrhy.',
          1 => 'Plán B offline: audit plakátů nebo letáků ve škole podle stejného seznamu.',
        ),
        'glossary' => 
        array (
          0 => 'whitespace',
          1 => 'cta',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      20 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 20 · Nejdřív obsah: landing page od textu k rozvržení',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím napsat obsah landing page dřív než rozvržení – hlavní sdělení, tři přínosy a výzvu k akci – a podle něj nakreslit drátěný model.',
          'success_criteria' => 
          array (
            0 => 'Hlavní sdělení řeknu jednou větou do 12 slov.',
            1 => 'Pořadí sekcí v drátěném modelu odpovídá otázkám návštěvníka.',
            2 => 'Výzva k akci je jen jedna a je jasné, co se stane po kliknutí.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_html_structure',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: krabice bez textu',
            'teacher' => 'Ukáže krásné rozvržení s textem „Lorem ipsum“ a zeptá se, o čem stránka je.',
            'student' => 'Zjistí, že bez obsahu nejde posoudit, jestli stránka funguje.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Hlavní sdělení',
            'teacher' => 'Ukáže vzorec: komu – co – proč teď.',
            'student' => 'Napíší hlavní sdělení do 12 slov a otestují ho na sousedovi.',
            'form' => 've dvojicích',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Otázky návštěvníka',
            'teacher' => 'Vede sběr otázek: co to je, pro koho, kolik stojí, jak se přihlásím.',
            'student' => 'Seřadí otázky a ke každé napíší krátkou odpověď.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 65,
            'phase' => 'Drátěný model',
            'teacher' => 'Předvede drátěný model z obdélníků a skutečných textů.',
            'student' => 'Nakreslí drátěný model pro mobil podle pořadí otázek.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Test pěti sekund',
            'teacher' => 'Vysvětlí test: 5 s pohledu, pak otázka „o čem to je?“.',
            'student' => 'Otestují drátěný model na dvou spolužácích.',
            'form' => 've skupinách',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: obsah určuje rozvržení, ne naopak.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Napiš hlavní sdělení své stránky do 12 slov podle vzorce komu – co – proč teď.',
            'output' => 'Jedna věta hlavního sdělení a počet slov.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Sepiš 4 otázky, které si návštěvník položí, seřaď je a ke každé napiš odpověď do dvou vět.',
            'output' => 'Seřazený seznam otázek s krátkými odpověďmi.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Nakresli drátěný model mobilní verze, ve kterém každá sekce odpovídá jedné otázce, a použij skutečné texty místo „Lorem ipsum“.',
            'output' => 'Drátěný model pro šířku 390 px se skutečnými texty.',
            'time' => '25 min',
          ),
          3 => 
          array (
            'text' => 'Proveď test pěti sekund na dvou spolužácích a zapiš, co si zapamatovali jako hlavní sdělení.',
            'output' => 'Zápis dvou odpovědí a jedna úprava, kterou z testu uděláš.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane vzor hlavního sdělení a seznam otázek; vybírá a řadí je.',
          'standard' => 'Vlastní sdělení, otázky, drátěný model a test pěti sekund.',
          'challenge' => 'Napíše dvě varianty hlavního sdělení a testem rozhodne, která je srozumitelnější.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kolečko: každý přečte své sdělení a třída hádá, pro koho stránka je.',
            1 => 'Kontrola modelu: ukaž sekci a řekni, na kterou otázku odpovídá.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Hlavní sdělení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Dlouhé nebo nejasné.',
                2 => 'Do 12 slov, komu a co.',
                3 => 'Obstojí v testu pěti sekund.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Pořadí obsahu',
              'levels' => 
              array (
                0 => 'Náhodné.',
                1 => 'Částečně podle otázek.',
                2 => 'Sekce odpovídají seřazeným otázkám.',
                3 => 'Zdůvodní pořadí z pohledu návštěvníka.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Drátěný model',
              'levels' => 
              array (
                0 => 'Nevznikl.',
                1 => 'Model s „Lorem ipsum“.',
                2 => 'Model mobilu se skutečnými texty.',
                3 => 'Model upravený podle testu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_ux',
          'competence_label' => 'Ovládání a výzva k akci',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč psát obsah dřív než rozvržení?',
              'options' => 
              array (
                0 => 'Protože rozvržení se nedá změnit.',
                1 => 'Protože pořadí a délka obsahu určují, jak má rozvržení vypadat.',
                2 => 'Protože text je důležitější než grafika vždycky.',
              ),
              'correct' => 1,
              'explanation' => 'Rozvržení slouží obsahu – bez něj stavíš krabice naslepo.',
            ),
            1 => 
            array (
              'question' => 'Které hlavní sdělení je nejlepší?',
              'options' => 
              array (
                0 => 'Víkendový kurz kreslení pro začátečníky, přihlášky do pátku.',
                1 => 'Vítejte na našich stránkách.',
                2 => 'Jsme nejlepší, zkuste nás.',
              ),
              'correct' => 0,
              'explanation' => 'Říká, co to je, pro koho a proč jednat teď.',
            ),
            2 => 
            array (
              'question' => 'Co ověřuje test pěti sekund?',
              'options' => 
              array (
                0 => 'Jestli se stránka načte do 5 sekund.',
                1 => 'Kolik obrázků stránka má.',
                2 => 'Jestli návštěvník po krátkém pohledu pozná, o čem stránka je.',
              ),
              'correct' => 2,
              'explanation' => 'První dojem musí nést hlavní sdělení.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: přepiš úvodní větu webu, který znáš, do 12 slov podle vzorce komu – co – proč teď.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Témata a texty stránek jsou vymyšlené; nepoužíváme skutečné firmy, jména ani kontakty.',
          1 => 'Testy na spolužácích jsou dobrovolné a nic se nezaznamenává.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: drátěný model s „Lorem ipsum“ – trvej na skutečných textech, i krátkých.',
          1 => 'Otázka do třídy: Na co se návštěvník zeptá jako první a odpovídá na to první sekce?',
          2 => 'Tempo: test pěti sekund lze dát jako rychlou výměnu mezi lavicemi, pokud model zabere víc času.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vzorec sdělení a papír s rámečkem mobilu; žáci plní úkoly 1–4.',
          1 => 'Plán B offline: celý úkol je na papíře – sdělení, otázky, drátěný model a test pěti sekund.',
        ),
        'glossary' => 
        array (
          0 => 'wireframe',
          1 => 'landing-page',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      21 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 21 · Úvodní blok stránky pro mobil i počítač',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout úvodní blok (hero) stránky tak, aby na mobilu i na počítači byl vidět nadpis, výzva k akci a podstatná část obrázku.',
          'success_criteria' => 
          array (
            0 => 'Na šířce 390 px je nadpis i tlačítko vidět bez posouvání.',
            1 => 'Pro mobil a počítač mám dva výřezy obrázku se stejným ohniskem.',
            2 => 'Text nad obrázkem má dostatečný kontrast (aspoň 4,5 : 1).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_css_layout',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_formats',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: useknutá hlava',
            'teacher' => 'Ukáže hero, kde na mobilu zmizí ohnisko fotky a tlačítko spadne pod okraj.',
            'student' => 'Popíší, co se při zúžení pokazilo.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Co musí být vidět',
            'teacher' => 'Vysvětlí první obrazovku: nadpis, jedna věta, tlačítko.',
            'student' => 'Vyznačí v drátěném modelu, co musí být nad okrajem.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Dva výřezy',
            'teacher' => 'Předvede výřez na výšku pro mobil a na šířku pro počítač.',
            'student' => 'Připraví dva výřezy obrázku se stejným ohniskem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Hero v CSS',
            'teacher' => 'Ukáže min-height, zarovnání a ztmavení pod textem.',
            'student' => 'Postaví hero pro mobil a od breakpointu pro počítač.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Kontrola kontrastu',
            'teacher' => 'Ukáže měření kontrastu textu nad obrázkem.',
            'student' => 'Změří kontrast a případně ztmaví podklad.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne pravidla první obrazovky.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'V drátěném modelu vyznač, co musí být na šířce 390 px vidět bez posouvání (nadpis, jedna věta, tlačítko).',
            'output' => 'Model s vyznačenou hranou první obrazovky.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Připrav dva výřezy jednoho obrázku – na výšku pro mobil a na šířku pro počítač – se stejným ohniskem.',
            'output' => 'Dva exportované výřezy a poznámka, kde je ohnisko.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Postav hero v CSS: na mobilu nadpis a tlačítko nad okrajem, od breakpointu text vedle obrázku.',
            'output' => 'Funkční hero ve dvou rozvrženích.',
            'time' => '22 min',
          ),
          3 => 
          array (
            'text' => 'Změř kontrast textu nad obrázkem; když je pod 4,5 : 1, přidej ztmavený podklad a měř znovu.',
            'output' => 'Zapsaný kontrast před úpravou a po ní.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane připravené výřezy obrázku a kostru hero; nastavuje jen zarovnání a tlačítko.',
          'standard' => 'Dva výřezy, hero v CSS a měření kontrastu.',
          'challenge' => 'Použije prvek picture, aby prohlížeč sám vybral výřez podle šířky, a ověří to v náhledu.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola v náhledu 390 px: je tlačítko vidět bez posouvání?',
            1 => 'Dvojice: soused řekne, kde je ohnisko obou výřezů.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'První obrazovka',
              'levels' => 
              array (
                0 => 'Tlačítko pod okrajem.',
                1 => 'Nadpis vidět, tlačítko ne.',
                2 => 'Nadpis i tlačítko bez posouvání.',
                3 => 'Platí i na šířce 320 px.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Výřezy obrázku',
              'levels' => 
              array (
                0 => 'Jeden výřez, ohnisko mizí.',
                1 => 'Dva výřezy, ohnisko jinde.',
                2 => 'Dva výřezy se stejným ohniskem.',
                3 => 'Prohlížeč vybírá výřez sám (picture).',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Kontrast textu',
              'levels' => 
              array (
                0 => 'Neměřen.',
                1 => 'Pod 4,5 : 1.',
                2 => 'Aspoň 4,5 : 1.',
                3 => 'Ověřeno ve světlé i tmavé části obrázku.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_css_layout',
          'competence_label' => 'Rozvržení v CSS a mobil',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co musí být v hero na mobilu vidět bez posouvání?',
              'options' => 
              array (
                0 => 'Celá fotografie v plné výšce.',
                1 => 'Patička s kontakty.',
                2 => 'Nadpis a tlačítko výzvy k akci.',
              ),
              'correct' => 2,
              'explanation' => 'První obrazovka nese sdělení a akci.',
            ),
            1 => 
            array (
              'question' => 'Proč mít pro mobil a počítač jiný výřez obrázku?',
              'options' => 
              array (
                0 => 'Aby ohnisko zůstalo vidět i v úzkém formátu na výšku.',
                1 => 'Protože mobil neumí formát JPEG.',
                2 => 'Aby byl soubor vždy menší než 1 kB.',
              ),
              'correct' => 0,
              'explanation' => 'Jiný poměr stran vyžaduje jiný výřez, ohnisko zůstává.',
            ),
            2 => 
            array (
              'question' => 'Text nad fotkou má kontrast 2,8 : 1. Co uděláš?',
              'options' => 
              array (
                0 => 'Zmenším písmo.',
                1 => 'Přidám pod text ztmavený podklad a změřím znovu.',
                2 => 'Nechám to, fotka je hezká.',
              ),
              'correct' => 1,
              'explanation' => 'Běžný text potřebuje aspoň 4,5 : 1.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyfoť (bez lidí) jeden motiv a nakresli do fotky výřez pro mobil a pro počítač.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Fotografie bez lidí, nebo s jejich souhlasem; cizí obrázky jen s licencí a uvedeným zdrojem.',
          1 => 'Neukládáme do obrázků údaje o poloze (EXIF) – před exportem je odstraň.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: obrázek přes celou výšku mobilu a tlačítko až pod ním – ptej se „co uvidí palec?“.',
          1 => 'Otázka do třídy: Kde je ohnisko a přežije zúžení na 390 px?',
          2 => 'Tempo: prvek picture nechej jen pro výzvu; standard zvládne dvě pravidla CSS s breakpointem.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: žáci plní úkoly 1, 2 a 4 v editoru obrázků, úkol 3 podle vytištěné kostry.',
          1 => 'Plán B offline: výřezy jako rámečky z papíru přiložené na vytištěnou fotku.',
        ),
        'glossary' => 
        array (
          0 => 'hero',
          1 => 'breakpoint',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      22 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 22 · Mikrotexty a tlačítka, která říkají, co se stane',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím napsat text tlačítka, chybové hlášky a potvrzení tak, aby uživatel věděl, co se stane a co má udělat dál.',
          'success_criteria' => 
          array (
            0 => 'Text tlačítka začíná slovesem a říká výsledek (např. „Odeslat přihlášku“).',
            1 => 'Chybová hláška říká, co je špatně a jak to opravit, bez obviňování.',
            2 => 'Po odeslání formuláře je potvrzení s dalším krokem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: tlačítko OK',
            'teacher' => 'Ukáže dialog s tlačítky „OK“ a „Zrušit“ u otázky „Nechcete neodejít?“.',
            'student' => 'Hádají, co které tlačítko udělá.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Sloveso a výsledek',
            'teacher' => 'Vysvětlí pravidlo: tlačítko = sloveso + co se stane.',
            'student' => 'Přepíší 5 obecných tlačítek.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Chybové hlášky',
            'teacher' => 'Ukáže hlášku „Chyba 42“ a její lepší verzi.',
            'student' => 'Napíší hlášky ke třem chybám formuláře.',
            'form' => 've dvojicích',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 60,
            'phase' => 'Potvrzení a další krok',
            'teacher' => 'Ukáže potvrzení s tím, co bude následovat.',
            'student' => 'Napíší potvrzení odeslané přihlášky.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 60,
            'to' => 80,
            'phase' => 'Test srozumitelnosti',
            'teacher' => 'Zadá test: soused bez vysvětlení popíše, co se stane.',
            'student' => 'Otestují texty a jeden přepíší.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: text je součást ovládání.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Přepiš pět obecných tlačítek (OK, Odeslat, Klikni sem, Další, Ano) tak, aby každé začínalo slovesem a říkalo výsledek.',
            'output' => 'Tabulka: původní text → nový text tlačítka.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Napiš chybové hlášky pro tři situace formuláře: prázdné povinné pole, e-mail bez zavináče, datum v minulosti.',
            'output' => 'Tři hlášky ve tvaru co je špatně + jak to opravit.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Napiš potvrzení po odeslání přihlášky, které řekne, co se stalo a co uživatel dostane dál.',
            'output' => 'Text potvrzení do tří vět s dalším krokem.',
            'time' => '12 min',
          ),
          3 => 
          array (
            'text' => 'Nech souseda bez vysvětlení říct, co udělá každé tvoje tlačítko, a přepiš to, u kterého se spletl.',
            'output' => 'Zápis testu a jedna přepsaná verze tlačítka.',
            'time' => '18 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane seznam sloves (odeslat, uložit, stáhnout, přihlásit se) a vzor jedné hlášky.',
          'standard' => 'Tlačítka, tři hlášky, potvrzení a test se sousedem.',
          'challenge' => 'Navrhne i krátkou mikrointerakci (stav tlačítka při odesílání) a popíše, co uživatel uvidí.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Hlasování: které ze dvou tlačítek je jasnější a proč?',
            1 => 'Učitel přečte hlášku, třída řekne, jestli obsahuje i opravu.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Text tlačítek',
              'levels' => 
              array (
                0 => 'Obecné texty.',
                1 => 'Část se slovesem.',
                2 => 'Všechna se slovesem a výsledkem.',
                3 => 'Obstojí v testu bez vysvětlení.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Chybové hlášky',
              'levels' => 
              array (
                0 => 'Kód chyby nebo obvinění.',
                1 => 'Co je špatně, bez opravy.',
                2 => 'Co je špatně i jak opravit.',
                3 => 'Hláška u pole a bez technického žargonu.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Potvrzení',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen „Hotovo“.',
                2 => 'Co se stalo a další krok.',
                3 => 'Uvádí i co dělat, když něco nepřijde.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_ux',
          'competence_label' => 'Ovládání a výzva k akci',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Který text tlačítka je nejlepší pro odeslání přihlášky na kurz?',
              'options' => 
              array (
                0 => 'OK',
                1 => 'Klikni sem',
                2 => 'Odeslat přihlášku',
              ),
              'correct' => 2,
              'explanation' => 'Sloveso + výsledek: uživatel ví, co se stane.',
            ),
            1 => 
            array (
              'question' => 'Která chybová hláška pomůže nejvíc?',
              'options' => 
              array (
                0 => 'Chyba 42.',
                1 => 'Zadal(a) jsi to špatně.',
                2 => 'E-mail musí obsahovat @, například jan@example.com.',
              ),
              'correct' => 2,
              'explanation' => 'Říká, co je špatně, a ukazuje správný tvar.',
            ),
            2 => 
            array (
              'question' => 'Co má obsahovat potvrzení po odeslání formuláře?',
              'options' => 
              array (
                0 => 'Co se stalo a co bude následovat.',
                1 => 'Jen slovo „Hotovo“.',
                2 => 'Odkaz zpět na úvodní stránku a nic víc.',
              ),
              'correct' => 0,
              'explanation' => 'Uživatel potřebuje jistotu i další krok.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: najdi v aplikaci v telefonu jedno nejasné tlačítko nebo hlášku a přepiš ji podle pravidel z hodiny.',
            'minutes' => 10,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Ukázkové formuláře jen s vymyšlenými údaji; e-maily jen ve tvaru …@example.com.',
          1 => 'Screenshoty cizích aplikací bez osobních údajů (zakryj jména a čísla).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: hláška vyčítá uživateli („Zadal jste špatně“) – veď k neutrálnímu „Zkontroluj…“.',
          1 => 'Otázka do třídy: Co přesně se stane po kliknutí a dá se to vrátit?',
          2 => 'Tempo: test se sousedem je krátký – raději ho nevynechávej, je to jádro ověření.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá pracovní list s pěti tlačítky a třemi chybami; žáci plní úkoly 1–4.',
          1 => 'Plán B offline: celé na papíře, test se sousedem ústně.',
        ),
        'glossary' => 
        array (
          0 => 'cta',
          1 => 'microinteraction',
        ),
        '_file' => 'lesson_content_v76_1a_a.php',
      ),
      23 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 23 · Přístupné barvy a písmo: kontrast, velikost, řádkování',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím změřit kontrast textu, zvolit čitelnou velikost písma a řádkování a opravit stránku tak, aby ji přečetl i člověk se slabším zrakem.',
          'success_criteria' => 
          array (
            0 => 'Běžný text má kontrast aspoň 4,5 : 1, velký text a ikony aspoň 3 : 1.',
            1 => 'Běžný text má aspoň 16 px a výšku řádku kolem 1,5.',
            2 => 'Informaci nepředávám jen barvou (chyba má i text nebo ikonu).',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_color_contrast',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'web_a11y',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: šedá na šedé',
            'teacher' => 'Promítne text #999999 na bílé a nechá třídu číst z poslední lavice.',
            'student' => 'Zažijí, jak se špatný kontrast čte.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Měření kontrastu',
            'teacher' => 'Ukáže měření poměru kontrastu a hranice 4,5 : 1 a 3 : 1.',
            'student' => 'Změří kontrast pěti dvojic barev ze své stránky.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 40,
            'phase' => 'Velikost a řádkování',
            'teacher' => 'Porovná text 12 px / řádkování 1,1 a 16 px / 1,5.',
            'student' => 'Upraví velikost písma, řádkování a délku řádku.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 40,
            'to' => 55,
            'phase' => 'Nejen barvou',
            'teacher' => 'Ukáže formulář, kde chybu značí jen červený rámeček.',
            'student' => 'Doplní k chybě text a ikonu.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 55,
            'to' => 80,
            'phase' => 'Oprava stránky',
            'teacher' => 'Obchází, kontroluje měření před a po.',
            'student' => 'Opraví landing page a zapíší hodnoty před a po.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne tři hranice: 4,5 : 1, 3 : 1 a 16 px.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Změř kontrast pěti dvojic barev text/pozadí na své stránce a u každé napiš, jestli splňuje 4,5 : 1.',
            'output' => 'Tabulka pěti dvojic s poměrem kontrastu a ano/ne.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Nastav běžnému textu velikost aspoň 16 px, výšku řádku 1,5 a omez délku řádku zhruba na 70 znaků.',
            'output' => 'Upravená pravidla CSS a snímek před a po.',
            'time' => '15 min',
          ),
          2 => 
          array (
            'text' => 'Doplň chybový stav formulářového pole tak, aby chybu nesla i textová hláška a ikona, ne jen barva.',
            'output' => 'Pole v chybovém stavu se třemi signály: barva, ikona, text.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Oprav všechny dvojice barev pod hranicí a zapiš hodnotu kontrastu před opravou a po ní.',
            'output' => 'Seznam oprav s kontrastem před → po.',
            'time' => '25 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane tabulku s připravenými dvojicemi barev a vzorem měření; opravuje podle nápovědy tmavší odstín.',
          'standard' => 'Měření, úprava písma, chybový stav a opravy na vlastní stránce.',
          'challenge' => 'Ověří stránku i v tmavém režimu a v náhledu „odstíny šedi“, jestli hierarchie drží bez barev.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel ukáže dvojici barev, třída tipne, jestli projde 4,5 : 1, pak se měří.',
            1 => 'Kontrola souseda: ukaž chybu formuláře bez barev – poznám ji?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Kontrast',
              'levels' => 
              array (
                0 => 'Neměřen.',
                1 => 'Změřen, opravy chybí.',
                2 => 'Všechny dvojice nad hranicí.',
                3 => 'Ověřeno i v tmavém režimu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Čitelnost písma',
              'levels' => 
              array (
                0 => 'Pod 14 px.',
                1 => '16 px, řádkování těsné.',
                2 => '16 px, výška řádku 1,5, rozumná délka řádku.',
                3 => 'Zdůvodní volby pro mobil i počítač.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Nejen barvou',
              'levels' => 
              array (
                0 => 'Chyba jen barvou.',
                1 => 'Barva + ikona.',
                2 => 'Barva, ikona i text.',
                3 => 'Hláška říká i opravu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_color_contrast',
          'competence_label' => 'Barvy a kontrast',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jaký nejmenší kontrast potřebuje běžný text podle WCAG 2.2 AA?',
              'options' => 
              array (
                0 => '2 : 1',
                1 => '4,5 : 1',
                2 => '10 : 1',
              ),
              'correct' => 1,
              'explanation' => 'Běžný text 4,5 : 1, velký text a prvky ovládání 3 : 1.',
            ),
            1 => 
            array (
              'question' => 'Chybné pole je označené jen červeným rámečkem. Co je špatně?',
              'options' => 
              array (
                0 => 'Člověk, který červenou nerozliší, chybu nepozná.',
                1 => 'Červená je moc výrazná.',
                2 => 'Rámeček má být silnější.',
              ),
              'correct' => 0,
              'explanation' => 'Informace nesmí stát jen na barvě.',
            ),
            2 => 
            array (
              'question' => 'Které nastavení běžného textu je nejčitelnější?',
              'options' => 
              array (
                0 => '12 px, výška řádku 1,1',
                1 => '14 px, výška řádku 1,0',
                2 => '16 px, výška řádku 1,5',
              ),
              'correct' => 2,
              'explanation' => 'Dostatečná velikost a vzduch mezi řádky.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: změř kontrast textu na jedné školní nástěnce nebo plakátu a navrhni lepší dvojici barev.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'K měření kontrastu použij nástroj povolený školou; nevkládej do něj nic než kódy barev.',
          1 => 'Stránky a texty jsou vymyšlené, bez osobních údajů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: „vypadá to dobře na mém monitoru“ – rozhoduje změřená hodnota, ne dojem.',
          1 => 'Otázka do třídy: Jak pozná chybu člověk, který nerozliší červenou a zelenou?',
          2 => 'Tempo: opravy stránky jsou hlavní práce – měření v úvodu omez na pět dvojic.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá tabulku dvojic barev a kontrolní seznam; žáci plní úkoly 1–4.',
          1 => 'Plán B offline: porovnání čitelnosti vytištěných vzorků písma a barev z různé vzdálenosti.',
        ),
        'glossary' => 
        array (
          0 => 'hex',
          1 => 'validation',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
      ),
      24 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 24 · Obrázek, který vypráví: výřez, ohnisko a export',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vybrat výřez obrázku podle sdělení, zachovat ohnisko v různých poměrech stran a vyexportovat obrázek ve vhodném formátu a velikosti pro web.',
          'success_criteria' => 
          array (
            0 => 'Ve výřezu je ohnisko na průsečíku třetin nebo záměrně uprostřed.',
            1 => 'Mám tři poměry stran (16 : 9, 4 : 5, 1 : 1) se stejným ohniskem.',
            2 => 'Fotku exportuji jako JPEG nebo WebP, grafiku s průhledností jako PNG, a soubor má rozumnou velikost.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_formats',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'gfx_composition',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: jedna fotka, tři příběhy',
            'teacher' => 'Ukáže tři výřezy jedné fotky, každý s jiným sdělením.',
            'student' => 'Pojmenují sdělení každého výřezu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Ohnisko a třetiny',
            'teacher' => 'Vysvětlí pravidlo třetin a ohnisko (focal point).',
            'student' => 'Vyznačí ohnisko na dvou vlastních fotkách.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 45,
            'phase' => 'Tři poměry stran',
            'teacher' => 'Předvede výřez 16 : 9, 4 : 5 a 1 : 1.',
            'student' => 'Vytvoří tři výřezy se stejným ohniskem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 45,
            'to' => 65,
            'phase' => 'Formát a velikost',
            'teacher' => 'Porovná JPEG, PNG, WebP a SVG a ukáže velikosti souborů.',
            'student' => 'Vyexportují výřezy a zapíší formát a velikost souboru.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Popis obrázku',
            'teacher' => 'Ukáže alternativní text, který nese sdělení.',
            'student' => 'Napíší alternativní text k hlavnímu výřezu.',
            'form' => 'jednotlivě',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: výřez, formát, popis.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Na dvou vlastních fotkách (bez lidí) vyznač ohnisko a mřížku třetin a napiš, co má divák vidět jako první.',
            'output' => 'Dvě fotky s vyznačeným ohniskem a jednou větou sdělení.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Z jedné fotky vytvoř tři výřezy v poměrech 16 : 9, 4 : 5 a 1 : 1 se stejným ohniskem.',
            'output' => 'Tři výřezy pojmenované podle poměru stran.',
            'time' => '20 min',
          ),
          2 => 
          array (
            'text' => 'Vyexportuj výřez 16 : 9 v šířce 1600 px jako JPEG a jako WebP a zapiš velikosti obou souborů.',
            'output' => 'Dva soubory a tabulka formát → velikost v kB.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Napiš alternativní text k hlavnímu výřezu, který řekne, co je na obrázku podstatné pro sdělení stránky.',
            'output' => 'Alternativní text do 125 znaků.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane fotku s předkreslenou mřížkou třetin a hotový rámeček pro poměr 1 : 1.',
          'standard' => 'Ohnisko, tři výřezy, export ve dvou formátech a alternativní text.',
          'challenge' => 'Připraví i export 800 px pro mobil a spočítá, kolik dat ušetří oproti 1600 px.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Třída ukáže prstem ohnisko na promítnuté fotce.',
            1 => 'Rychlá otázka: logo s průhledným pozadím – jaký formát?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Výřez a ohnisko',
              'levels' => 
              array (
                0 => 'Ohnisko useknuté.',
                1 => 'Ohnisko v jednom poměru.',
                2 => 'Ohnisko ve třech poměrech.',
                3 => 'Každý výřez nese jiné záměrné sdělení.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Formát a velikost',
              'levels' => 
              array (
                0 => 'Nevhodný formát.',
                1 => 'Vhodný formát, obří soubor.',
                2 => 'Vhodný formát a rozumná velikost.',
                3 => 'Porovná formáty a zdůvodní volbu daty.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Alternativní text',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Jen „obrázek“ nebo název souboru.',
                2 => 'Popisuje podstatné pro sdělení.',
                3 => 'Stručný a liší se podle kontextu stránky.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_formats',
          'competence_label' => 'Grafické formáty a export',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Jaký formát zvolíš pro logo s průhledným pozadím, které se nemá rozmazat?',
              'options' => 
              array (
                0 => 'JPEG s nejvyšší kompresí',
                1 => 'SVG nebo PNG',
                2 => 'BMP',
              ),
              'correct' => 1,
              'explanation' => 'JPEG neumí průhlednost; vektor nebo PNG ji zachová.',
            ),
            1 => 
            array (
              'question' => 'Co je při změně poměru stran výřezu nejdůležitější?',
              'options' => 
              array (
                0 => 'Aby obrázek měl co nejvíc pixelů.',
                1 => 'Aby byl výřez vždy čtvercový.',
                2 => 'Aby ohnisko zůstalo vidět.',
              ),
              'correct' => 2,
              'explanation' => 'Výřez slouží sdělení – ohnisko nesmí zmizet.',
            ),
            2 => 
            array (
              'question' => 'Který alternativní text je nejlepší pro fotku na stránce kurzu kreslení?',
              'options' => 
              array (
                0 => 'Ruce kreslí uhlem portrét na velký arch papíru.',
                1 => 'obrazek1.jpg',
                2 => 'Fotka.',
              ),
              'correct' => 0,
              'explanation' => 'Popisuje, co je pro sdělení podstatné.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: vyfoť jeden předmět a připrav z fotky výřez 1 : 1 a 16 : 9 se stejným ohniskem.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Fotíme bez lidí, nebo jen se souhlasem; cizí fotky jen s licencí a uvedeným autorem.',
          1 => 'Před exportem odstraň z fotky údaje o poloze (EXIF).',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: obrázek pro web v plném rozlišení z fotoaparátu (několik MB) – vždy porovnej velikost souboru.',
          1 => 'Otázka do třídy: Co má divák vidět jako první a zůstane to i ve čtverci?',
          2 => 'Tempo: export a porovnání formátů je praktické jádro – úvod o třetinách zkrať.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá vytištěné fotky a rámečky poměrů; žáci plní úkoly 1, 2 a 4, úkol 3 doma nebo příště.',
          1 => 'Plán B offline: výřezy z papírových rámečků přiložených na vytištěné fotky.',
        ),
        'glossary' => 
        array (
          0 => 'crop',
          1 => 'focal-point',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
      ),
      25 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 25 · Startovní sada komponent: tlačítko, karta a pole formuláře',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím navrhnout tři opakovatelné komponenty – tlačítko, kartu a pole formuláře – včetně stavů výchozí, najetí, fokus, chyba a nedostupné.',
          'success_criteria' => 
          array (
            0 => 'Každá komponenta má popsané stavy a viditelný fokus pro ovládání klávesnicí.',
            1 => 'Tlačítko má cílovou plochu (touch target) aspoň 44 × 44 px.',
            2 => 'Pole formuláře má label, nápovědu a chybovou hlášku pod polem.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_a11y',
            'level' => 2,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: kde jsem?',
            'teacher' => 'Projde stránku Tabem, kde fokus není vidět.',
            'student' => 'Zažijí ztrátu orientace bez viditelného fokusu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 30,
            'phase' => 'Stavy tlačítka',
            'teacher' => 'Ukáže stavy výchozí, hover, focus, stisk a nedostupné.',
            'student' => 'Navrhnou tlačítko ve všech stavech.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 30,
            'to' => 50,
            'phase' => 'Pole formuláře',
            'teacher' => 'Předvede label, nápovědu, chybu a placeholder jen jako příklad.',
            'student' => 'Navrhnou pole ve stavech výchozí, fokus a chyba.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 65,
            'phase' => 'Karta',
            'teacher' => 'Ukáže kartu s jedním odkazem přes celou plochu.',
            'student' => 'Navrhnou kartu z tokenů z L17.',
            'form' => 've dvojicích',
          ),
          4 => 
          array (
            'from' => 65,
            'to' => 80,
            'phase' => 'Kontrola klávesnicí',
            'teacher' => 'Zadá test: projdi komponenty jen Tabem a Enterem.',
            'student' => 'Otestují a opraví stav, který chybí.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 80,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne: stav = informace pro uživatele.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Navrhni tlačítko v pěti stavech (výchozí, hover, focus, stisknuté, nedostupné) s cílovou plochou aspoň 44 × 44 px.',
            'output' => 'Řádek pěti stavů tlačítka s popisem změny.',
            'time' => '18 min',
          ),
          1 => 
          array (
            'text' => 'Navrhni pole formuláře s labelem, nápovědou a chybovou hláškou ve stavech výchozí, fokus a chyba.',
            'output' => 'Tři stavy pole pod sebou s texty.',
            'time' => '18 min',
          ),
          2 => 
          array (
            'text' => 'Navrhni kartu z tokenů své sady (L17), na které je celá plocha jedním odkazem.',
            'output' => 'Karta s popisky použitých tokenů.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Projdi své komponenty jen klávesnicí a zapiš, kde fokus nebyl vidět nebo chyběl stav, a oprav to.',
            'output' => 'Zápis testu klávesnicí a opravená komponenta.',
            'time' => '14 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane předlohu tlačítka ve výchozím stavu a doplňuje ostatní stavy podle tabulky změn.',
          'standard' => 'Tlačítko, pole a karta se stavy a test klávesnicí.',
          'challenge' => 'Navrhne i stav „odesílám“ s omezeným pohybem pro uživatele s nastavením reduced motion.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel promítne tlačítko a třída vyjmenuje chybějící stavy.',
            1 => 'Test klávesnicí ve dvojici: vidím vždy, kde jsem?',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Stavy komponent',
              'levels' => 
              array (
                0 => 'Jen výchozí stav.',
                1 => 'Dva až tři stavy.',
                2 => 'Všechny stavy popsané.',
                3 => 'Stavy drží i v tmavém režimu.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Fokus a cílová plocha',
              'levels' => 
              array (
                0 => 'Fokus neviditelný.',
                1 => 'Fokus slabý, plocha malá.',
                2 => 'Viditelný fokus a plocha 44 × 44 px.',
                3 => 'Ověřeno testem klávesnicí.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Pole formuláře',
              'levels' => 
              array (
                0 => 'Bez labelu.',
                1 => 'Label, bez chyby.',
                2 => 'Label, nápověda i chybová hláška.',
                3 => 'Hláška říká opravu a je u pole.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_a11y',
          'competence_label' => 'Přístupnost webu',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč musí být stav focus viditelný?',
              'options' => 
              array (
                0 => 'Aby tlačítko bylo barevnější.',
                1 => 'Aby člověk ovládající web klávesnicí věděl, kde právě je.',
                2 => 'Kvůli rychlejšímu načítání.',
              ),
              'correct' => 1,
              'explanation' => 'Viditelný fokus je orientace pro klávesnici.',
            ),
            1 => 
            array (
              'question' => 'Jakou cílovou plochu doporučujeme pro tlačítko na dotykovém displeji?',
              'options' => 
              array (
                0 => 'Aspoň 44 × 44 px',
                1 => '10 × 10 px',
                2 => 'Přesně velikost textu',
              ),
              'correct' => 0,
              'explanation' => 'Dostatečná plocha pro prst; WCAG 2.2 AA vyžaduje aspoň 24 × 24 px, doporučujeme 44.',
            ),
            2 => 
            array (
              'question' => 'Kam patří chybová hláška pole formuláře?',
              'options' => 
              array (
                0 => 'Jen do vyskakovacího okna nahoře.',
                1 => 'Do placeholderu.',
                2 => 'K poli, kterého se týká, s návodem na opravu.',
              ),
              'correct' => 2,
              'explanation' => 'Hláška u pole je vidět i čtečce a říká opravu.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: projdi jeden web jen klávesnicí a zapiš, kde ses ztratil(a).',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Komponenty testujeme na vlastních návrzích; do formulářů zadáváme jen vymyšlené údaje.',
          1 => 'Ikony jen z bezplatných sad s licencí; zdroj zapiš.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: outline: none bez náhrady – fokus pak zmizí. Vždy nabídni jiný viditelný styl.',
          1 => 'Otázka do třídy: Kdo všechno ovládá web bez myši?',
          2 => 'Tempo: když karta nevyjde, stačí tlačítko a pole – test klávesnicí nevynechávej.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá tabulku stavů; žáci kreslí komponenty na papír a test klávesnicí udělají na hotovém webu.',
          1 => 'Plán B offline: komponenty jako papírové kartičky v pěti stavech.',
        ),
        'glossary' => 
        array (
          0 => 'focus',
          1 => 'touch-target',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
      ),
      26 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 26 · Workshop kritiky: Líbí se mi – Ptám se – Navrhuji',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vést a přijímat kritiku návrhu ve skupině podle struktury Líbí se mi – Ptám se – Navrhuji a z poznámek sestavit plán úprav.',
          'success_criteria' => 
          array (
            0 => 'Ke každému návrhu dám jednu poznámku do každé ze tří částí.',
            1 => 'Jako autor nejdřív poslouchám a ptám se, až potom vysvětluji.',
            2 => 'Z poznámek sestavím plán tří úprav seřazených podle dopadu.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_composition',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 2,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Pravidla workshopu',
            'teacher' => 'Představí roli autora, moderátora a zapisovatele a časomíru.',
            'student' => 'Rozdělí se do skupin po čtyřech a rozdělí role.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 20,
            'phase' => 'Ukázkové kolo',
            'teacher' => 'Předvede jedno kolo na anonymním návrhu.',
            'student' => 'Sledují strukturu a čas.',
            'form' => 'frontálně',
          ),
          2 => 
          array (
            'from' => 20,
            'to' => 60,
            'phase' => 'Kola kritiky',
            'teacher' => 'Hlídá čas (10 min na návrh) a tón.',
            'student' => 'Každý představí návrh, ostatní dají Líbí – Ptám se – Navrhuji.',
            'form' => 've skupinách',
          ),
          3 => 
          array (
            'from' => 60,
            'to' => 75,
            'phase' => 'Plán úprav',
            'teacher' => 'Ukáže, jak poznámky seřadit podle dopadu.',
            'student' => 'Sestaví plán tří úprav.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 75,
            'to' => 82,
            'phase' => 'Reflexe skupiny',
            'teacher' => 'Zeptá se, která poznámka pomohla nejvíc a proč.',
            'student' => 'Sdílí jednu užitečnou poznámku.',
            'form' => 'frontálně',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Exit ticket',
            'teacher' => 'Shrne role a strukturu.',
            'student' => 'Odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Připrav svůj návrh na kritiku: jednu větu cíle návrhu a jednu otázku, na kterou chceš od skupiny odpověď.',
            'output' => 'Návrh s větou cíle a otázkou pro skupinu.',
            'time' => '8 min',
          ),
          1 => 
          array (
            'text' => 'Ke každému návrhu ve skupině napiš jednu poznámku Líbí se mi, jednu Ptám se a jednu Navrhuji.',
            'output' => 'Lístky se třemi poznámkami ke každému návrhu.',
            'time' => '30 min',
          ),
          2 => 
          array (
            'text' => 'Jako zapisovatel zapiš poznámky ke kolu spolužáka přehledně do tří sloupců.',
            'output' => 'Tabulka Líbí – Ptám se – Navrhuji k jednomu návrhu.',
            'time' => '10 min',
          ),
          3 => 
          array (
            'text' => 'Z poznámek ke svému návrhu sestav plán tří úprav seřazených podle dopadu na uživatele.',
            'output' => 'Plán úprav: úprava → proč → jak ověřím.',
            'time' => '15 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kartičky se začátky vět pro všechny tři části a roli zapisovatele.',
          'standard' => 'Poznámky ke všem návrhům ve skupině a vlastní plán úprav.',
          'challenge' => 'Moderuje kolo: hlídá čas, strukturu a ptá se tišších členů skupiny.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Moderátor po kole řekne, jestli zazněly všechny tři části.',
            1 => 'Učitel vybere náhodnou poznámku a třída určí, do které části patří.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Struktura poznámek',
              'levels' => 
              array (
                0 => 'Jen hodnocení.',
                1 => 'Jedna nebo dvě části.',
                2 => 'Všechny tři části ke každému návrhu.',
                3 => 'Návrhy jsou konkrétní a ověřitelné.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Role autora',
              'levels' => 
              array (
                0 => 'Obhajuje se a skáče do řeči.',
                1 => 'Poslouchá, neptá se.',
                2 => 'Poslouchá a doptá se.',
                3 => 'Shrne slyšené vlastními slovy.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Plán úprav',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Seznam bez pořadí.',
                2 => 'Tři úpravy podle dopadu.',
                3 => 'U každé úpravy způsob ověření.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_composition',
          'competence_label' => 'Kompozice a hierarchie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co dělá autor návrhu během kola kritiky?',
              'options' => 
              array (
                0 => 'Hned vysvětluje, proč to udělal takhle.',
                1 => 'Odchází, aby ostatní mohli mluvit.',
                2 => 'Poslouchá, zapisuje a doptá se, až když ostatní domluví.',
              ),
              'correct' => 2,
              'explanation' => 'Nejdřív poslouchat, pak se ptát.',
            ),
            1 => 
            array (
              'question' => 'Která poznámka patří do části „Ptám se“?',
              'options' => 
              array (
                0 => 'Co má uživatel udělat jako první po otevření stránky?',
                1 => 'Líbí se mi barva tlačítka.',
                2 => 'Zmenši nadpis.',
              ),
              'correct' => 0,
              'explanation' => 'Otázka otevírá zamyšlení autora.',
            ),
            2 => 
            array (
              'question' => 'Podle čeho seřadíš úpravy z workshopu?',
              'options' => 
              array (
                0 => 'Podle toho, kdo poznámku řekl.',
                1 => 'Podle dopadu na uživatele.',
                2 => 'Abecedně.',
              ),
              'correct' => 1,
              'explanation' => 'Nejdřív to, co nejvíc pomůže uživateli.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: proveď jednu úpravu z plánu a zapiš, jestli pomohla.',
            'minutes' => 20,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Kritizujeme návrh, ne autora; kdo nechce svůj návrh ukázat, použije anonymní ukázku.',
          1 => 'Poznámky zůstávají ve skupině, nefotí se a nesdílí.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: autor se obhajuje místo poslechu – moderátor ho jemně zastaví.',
          1 => 'Otázka do třídy: Která poznámka vám dnes ušetřila nejvíc práce?',
          2 => 'Tempo: 10 minut na návrh drž přesně; skupiny po čtyřech stihnou čtyři kola.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: vytiskne pravidla workshopu a časomíru; skupiny pracují samostatně podle úkolů 1–4.',
          1 => 'Plán B offline: kritika vytištěných návrhů nebo plakátů ve škole.',
        ),
        'glossary' => 
        array (
          0 => 'thumbnail',
          1 => 'whitespace',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
      ),
      27 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 27 · Sprint landing page: od zadání k funkční stránce',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím v časovém limitu převést krátké zadání na funkční landing page s mřížkou, výzvou k akci a ověřením na mobilu.',
          'success_criteria' => 
          array (
            0 => 'Do 15 minut mám hlavní sdělení a drátěný model.',
            1 => 'Rozvržení stavím mřížkou nebo flexboxem s mezerou gap, bez pevných šířek v px.',
            2 => 'Na konci mám kontrolní seznam s ověřením na šířce 390 px.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'web_css_layout',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'web_html_structure',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 5,
            'phase' => 'Zadání sprintu',
            'teacher' => 'Rozdá zadání (vymyšlená akce) a časový plán sprintu.',
            'student' => 'Přečtou zadání a podtrhnou cílovou skupinu.',
            'form' => 'frontálně',
          ),
          1 => 
          array (
            'from' => 5,
            'to' => 20,
            'phase' => 'Sdělení a model',
            'teacher' => 'Hlídá limit 15 minut.',
            'student' => 'Napíší sdělení a nakreslí drátěný model.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 20,
            'to' => 55,
            'phase' => 'Stavba stránky',
            'teacher' => 'Obchází a připomíná mřížku a gap.',
            'student' => 'Postaví kostru a rozvržení mřížkou nebo flexboxem.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 55,
            'to' => 70,
            'phase' => 'Výzva k akci a formulář',
            'teacher' => 'Kontroluje texty tlačítek a labely.',
            'student' => 'Doplní tlačítko a formulář.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Ověření',
            'teacher' => 'Vede kontrolu na 390 px, klávesnicí a kontrastu.',
            'student' => 'Projdou kontrolní seznam a opraví jednu chybu.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Odevzdání a exit ticket',
            'teacher' => 'Ukončí sprint.',
            'student' => 'Odevzdají stránku a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Do 15 minut napiš hlavní sdělení a nakresli drátěný model mobilní verze stránky ze zadání sprintu.',
            'output' => 'Sdělení do 12 slov a drátěný model.',
            'time' => '15 min',
          ),
          1 => 
          array (
            'text' => 'Postav rozvržení sekcí pomocí CSS grid nebo flexbox s mezerou gap a bez pevných šířek v pixelech.',
            'output' => 'Stránka, která se na úzké obrazovce skládá do sloupce.',
            'time' => '35 min',
          ),
          2 => 
          array (
            'text' => 'Doplň jednu výzvu k akci a formulář přihlášky s labely a srozumitelným textem tlačítka.',
            'output' => 'Tlačítko se slovesem a formulář s labely.',
            'time' => '15 min',
          ),
          3 => 
          array (
            'text' => 'Ověř stránku na šířce 390 px, klávesnicí a měřením kontrastu a zapiš výsledek do kontrolního seznamu.',
            'output' => 'Kontrolní seznam: tři kontroly s výsledkem a jednou opravou.',
            'time' => '12 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane kostru HTML a třídu mřížky; doplňuje obsah a jedno pravidlo rozvržení.',
          'standard' => 'Celý sprint podle časového plánu.',
          'challenge' => 'Přidá druhé rozvržení pro široké obrazovky a srovná grid a flexbox ve dvou větách.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Kontrola po 20 minutách: ukažte drátěný model zvednutím papíru.',
            1 => 'Kontrolní seznam ve dvojici: soused potvrdí tři kontroly.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Plán v limitu',
              'levels' => 
              array (
                0 => 'Bez plánu.',
                1 => 'Plán po limitu.',
                2 => 'Sdělení a model do 15 min.',
                3 => 'Model se ve stavbě dodržel.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Rozvržení',
              'levels' => 
              array (
                0 => 'Pevné šířky, přetéká.',
                1 => 'Funguje jen na počítači.',
                2 => 'Mřížka nebo flexbox s gap na mobilu i počítači.',
                3 => 'Dvě rozvržení a zdůvodněná volba techniky.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Ověření',
              'levels' => 
              array (
                0 => 'Neověřeno.',
                1 => 'Jedna kontrola.',
                2 => 'Tři kontroly s výsledkem.',
                3 => 'Oprava nalezené chyby ověřená znovu.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'web_css_layout',
          'competence_label' => 'Rozvržení v CSS a mobil',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Proč se v responzivním rozvržení vyhýbáme pevné šířce width: 1200px?',
              'options' => 
              array (
                0 => 'Protože pixely nejsou povolené.',
                1 => 'Protože na úzké obrazovce obsah přeteče a vznikne vodorovné posouvání.',
                2 => 'Protože pevná šířka zpomalí načítání.',
              ),
              'correct' => 1,
              'explanation' => 'Pružné rozvržení se přizpůsobí šířce displeje.',
            ),
            1 => 
            array (
              'question' => 'K čemu slouží vlastnost gap?',
              'options' => 
              array (
                0 => 'Určí mezeru mezi položkami mřížky nebo flexboxu.',
                1 => 'Skryje prvek na mobilu.',
                2 => 'Nastaví barvu okraje.',
              ),
              'correct' => 0,
              'explanation' => 'gap = mezera mezi položkami bez okrajů navíc.',
            ),
            2 => 
            array (
              'question' => 'Co uděláš jako první v sprintu landing page?',
              'options' => 
              array (
                0 => 'Vyberu barvy a písmo.',
                1 => 'Napíšu CSS animace.',
                2 => 'Hlavní sdělení a drátěný model podle zadání.',
              ),
              'correct' => 2,
              'explanation' => 'Obsah a plán určují, co se bude stavět.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: dokonči jednu nedodělanou část sprintu a zapiš, kolik času ti zabrala.',
            'minutes' => 25,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Zadání sprintu je vymyšlené; žádné skutečné kontakty ani fotky lidí bez souhlasu.',
          1 => 'Kód nekopírujeme z cizích webů; vlastní řešení nebo ukázky z hodin.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: ve sprintu se začne barvami a písmem – po 15 minutách zkontroluj, že každý má model.',
          1 => 'Otázka do třídy: Co se stane s tvou stránkou na šířce 320 px?',
          2 => 'Tempo: když stavba trvá, zkrať formulář na jedno pole, ale ověření nech celé.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá zadání sprintu a časový plán; žáci pracují samostatně a odevzdají soubory.',
          1 => 'Plán B offline: sprint na papíře – sdělení, model a kontrolní seznam podle vytištěného webu.',
        ),
        'glossary' => 
        array (
          0 => 'grid',
          1 => 'gap',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
      ),
      28 => 
      array (
        'status' => 'navrh',
        'version' => 1,
        'title' => 'Lekce 28 · Portfolio a ověření dovedností prvního ročníku',
        'goal' => 
        array (
          'student' => 'Na konci hodiny umím vybrat dvě své práce do portfolia, ke každé napsat krátkou případovou studii (case study: problém → rozhodnutí → výsledek) a podle kompetencí určit, co zlepším příště.',
          'success_criteria' => 
          array (
            0 => 'Ke každé práci mám problém, dvě rozhodnutí a výsledek s důkazem.',
            1 => 'U každé kompetence ročníku umím ukázat konkrétní práci jako důkaz.',
            2 => 'Napíšu jeden konkrétní cíl na další ročník.',
          ),
        ),
        'competencies' => 
        array (
          0 => 
          array (
            'id' => 'gfx_composition',
            'level' => 3,
          ),
          1 => 
          array (
            'id' => 'web_ux',
            'level' => 3,
          ),
        ),
        'timeline' => 
        array (
          0 => 
          array (
            'from' => 0,
            'to' => 10,
            'phase' => 'Start: co jsem se naučil(a)',
            'teacher' => 'Promítne seznam kompetencí ročníku.',
            'student' => 'Ke každé kompetenci si vzpomenou na jednu práci.',
            'form' => 'jednotlivě',
          ),
          1 => 
          array (
            'from' => 10,
            'to' => 25,
            'phase' => 'Výběr prací',
            'teacher' => 'Vysvětlí, proč vybrat práci s příběhem, ne jen nejhezčí.',
            'student' => 'Vyberou dvě práce do portfolia.',
            'form' => 'jednotlivě',
          ),
          2 => 
          array (
            'from' => 25,
            'to' => 50,
            'phase' => 'Případová studie',
            'teacher' => 'Ukáže vzor: problém → rozhodnutí → výsledek.',
            'student' => 'Napíší dvě krátké případové studie.',
            'form' => 'jednotlivě',
          ),
          3 => 
          array (
            'from' => 50,
            'to' => 70,
            'phase' => 'Mapa kompetencí',
            'teacher' => 'Vysvětlí sebehodnocení podle důkazů.',
            'student' => 'Přiřadí práce ke kompetencím a označí mezery.',
            'form' => 'jednotlivě',
          ),
          4 => 
          array (
            'from' => 70,
            'to' => 82,
            'phase' => 'Sdílení ve dvojici',
            'teacher' => 'Zadá: přečti sousedovi jednu studii za 1 minutu.',
            'student' => 'Sdílí a dostanou jednu otázku.',
            'form' => 've dvojicích',
          ),
          5 => 
          array (
            'from' => 82,
            'to' => 90,
            'phase' => 'Cíl a exit ticket',
            'teacher' => 'Shrne rok.',
            'student' => 'Zapíší cíl na další ročník a odpoví na exit ticket.',
            'form' => 'jednotlivě',
          ),
        ),
        'tasks' => 
        array (
          0 => 
          array (
            'text' => 'Vyber dvě své práce z ročníku, které ukazují, jak ses rozhodoval(a), a ke každé napiš jednu větu proč.',
            'output' => 'Dvě vybrané práce s větou zdůvodnění.',
            'time' => '12 min',
          ),
          1 => 
          array (
            'text' => 'Napiš ke každé vybrané práci krátkou případovou studii: problém, dvě rozhodnutí a výsledek s důkazem.',
            'output' => 'Dvě studie po 5–7 větách.',
            'time' => '25 min',
          ),
          2 => 
          array (
            'text' => 'Přiřaď své práce k osmi kompetencím ročníku a označ kompetence, ke kterým zatím důkaz nemáš.',
            'output' => 'Tabulka kompetence → důkaz (práce) → mezera.',
            'time' => '18 min',
          ),
          3 => 
          array (
            'text' => 'Napiš jeden konkrétní a ověřitelný cíl na další ročník podle největší mezery.',
            'output' => 'Cíl ve tvaru: co, jak poznám, že to umím, do kdy.',
            'time' => '10 min',
          ),
        ),
        'differentiation' => 
        array (
          'support' => 'Dostane šablonu případové studie s otázkami a seznam svých prací z portfolia.',
          'standard' => 'Dvě studie, mapa kompetencí a cíl.',
          'challenge' => 'Připojí ke studii srovnání první a poslední verze práce s popisem, co se zlepšilo.',
        ),
        'assessment' => 
        array (
          'formative' => 
          array (
            0 => 'Učitel přečte anonymní studii, třída najde problém, rozhodnutí a výsledek.',
            1 => 'Dvojice: soused položí jednu otázku k rozhodnutí.',
          ),
          'rubric' => 
          array (
            0 => 
            array (
              'criterion' => 'Případová studie',
              'levels' => 
              array (
                0 => 'Jen popis práce.',
                1 => 'Problém a výsledek bez rozhodnutí.',
                2 => 'Problém, dvě rozhodnutí a výsledek.',
                3 => 'Výsledek doložený srovnáním nebo testem.',
              ),
            ),
            1 => 
            array (
              'criterion' => 'Mapa kompetencí',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Částečná, bez mezer.',
                2 => 'Všech 8 kompetencí s důkazem nebo mezerou.',
                3 => 'Mezery zdůvodněné.',
              ),
            ),
            2 => 
            array (
              'criterion' => 'Cíl',
              'levels' => 
              array (
                0 => 'Chybí.',
                1 => 'Obecný („zlepšit se“).',
                2 => 'Konkrétní a ověřitelný.',
                3 => 'S plánem prvního kroku.',
              ),
            ),
          ),
        ),
        'exit_ticket' => 
        array (
          'competence' => 'gfx_composition',
          'competence_label' => 'Kompozice a hierarchie',
          'variants' => 
          array (
            0 => 
            array (
              'question' => 'Co patří do krátké případové studie práce?',
              'options' => 
              array (
                0 => 'Seznam použitých programů.',
                1 => 'Jen finální obrázek.',
                2 => 'Problém, rozhodnutí a výsledek s důkazem.',
              ),
              'correct' => 2,
              'explanation' => 'Studie ukazuje, jak přemýšlíš.',
            ),
            1 => 
            array (
              'question' => 'Kterou práci vybereš do portfolia?',
              'options' => 
              array (
                0 => 'Tu, u které umím vysvětlit svá rozhodnutí a zlepšení.',
                1 => 'Tu s nejvíce barvami.',
                2 => 'Tu, kterou jsem dělal(a) nejkratší dobu.',
              ),
              'correct' => 0,
              'explanation' => 'Portfolio ukazuje proces i výsledek.',
            ),
            2 => 
            array (
              'question' => 'Který cíl na další ročník je ověřitelný?',
              'options' => 
              array (
                0 => 'Chci být lepší v grafice.',
                1 => 'Do listopadu postavím stránku, která projde kontrolou kontrastu i klávesnicí.',
                2 => 'Budu se víc snažit.',
              ),
              'correct' => 1,
              'explanation' => 'Konkrétní výsledek a termín se dají ověřit.',
            ),
          ),
        ),
        'homework' => 
        array (
          0 => 
          array (
            'text' => 'Volitelné: připni jednu vybranou práci s reflexí do svého portfolia v EDUCANETu.',
            'minutes' => 15,
            'optional' => true,
          ),
        ),
        'safety' => 
        array (
          0 => 'Do portfolia dávej jen vlastní práce a obrázky s licencí; žádné fotky ani jména spolužáků bez souhlasu.',
          1 => 'Portfolio vidíš ty a tvoji učitelé; sdílení ven jen se souhlasem a bez osobních údajů.',
        ),
        'teacher_notes' => 
        array (
          0 => 'Častý omyl: do portfolia jen „nejhezčí“ práce – veď žáky k pracím s viditelným rozhodováním.',
          1 => 'Otázka do třídy: Které rozhodnutí z letoška bys dnes udělal(a) jinak a proč?',
          2 => 'Tempo: mapa kompetencí může pokračovat doma; případové studie dokonči v hodině.',
        ),
        'substitution' => 
        array (
          0 => 'Zástup bez odborníka: rozdá šablonu případové studie a seznam kompetencí; žáci plní úkoly 1–4.',
          1 => 'Plán B offline: studie na papíře k vytištěným pracím.',
        ),
        'glossary' => 
        array (
          0 => 'case-study',
        ),
        '_file' => 'lesson_content_v76_1a_b.php',
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
  'built_at' => '2026-10-12T21:50:29+02:00',
);
