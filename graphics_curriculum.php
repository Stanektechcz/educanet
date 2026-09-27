<?php

declare(strict_types=1);

return [
    'class_1a' => [
        'title' => 'První plakát: od nastavení v sandboxu k návrhu v Canvě',
        'goal' => 'Nejdřív si v aplikaci bezpečně nastav hierarchii, kontrast, grid a export. Potom stejná rozhodnutí přenes do Canvy a vytvoř jednoduchý plakát, který se dá přečíst během několika sekund.',
        'tools' => 'Nejdřív vestavěný Design Studio, potom Canva. Volitelně Figma nebo jiný grafický editor.',
        'mandatory' => [
            'Projdi alespoň tři doporučené Knowledge Tours: hierarchie, kompozice/grid a kontrast.',
            'V Design Studiu vytvoř zkušební konfiguraci a dosáhni čitelného kontrastu hlavního textu.',
            'Vyber digitální formát 1080 × 1350 px nebo A4 na výšku.',
            'Použij jeden jasný headline, jeden informační blok a jedno CTA.',
            'Použij maximálně dvě rodiny písem a jednu hlavní akcentní barvu.',
            'Zachovej společné hrany prvků a vědomý negativní prostor.',
            'Převeď konfiguraci do Canvy — nejde o přesné kopírování pixelů, ale stejnou hierarchii a vztahy.',
            'Export otevři mimo editor a vizuálně zkontroluj.',
        ],
        'lesson1' => [
            ['time' => '0–8 min', 'title' => 'Start + krátký test', 'text' => 'Zjisti, co už poznáš intuitivně. Test není na známku a knowledgebase je během něj zamčená.'],
            ['time' => '8–20 min', 'title' => 'Knowledge Tour', 'text' => 'Projdi vizuálně hierarchii, grid a kontrast. Nečti vše — nejdřív používej demo a simulace.'],
            ['time' => '20–37 min', 'title' => 'Design Studio', 'text' => 'Vyber brief, nastav rozměr, barvy, velikosti textů, grid a spacing. Ověř kontrast, miniaturu a exportní nastavení.'],
            ['time' => '37–45 min', 'title' => 'Canva Transfer Guide', 'text' => 'Projdi dokument, grid, hierarchii a paletu v krokovém handoffu. V Canvě založ správný formát a přenes základní strukturu bez dekorací.'],
        ],
        'lesson2' => [
            ['time' => '0–8 min', 'title' => 'Kontrola skeletonu', 'text' => 'Zmenši návrh, zakryj dekorace a ověř, zda stále funguje headline → informace → CTA.'],
            ['time' => '8–30 min', 'title' => 'Finální vizuál', 'text' => 'Doplň obrazový materiál, barvy a typografické detaily. Každý efekt musí mít důvod.'],
            ['time' => '30–38 min', 'title' => 'Visual QA + porovnání', 'text' => 'Projdi 10bodový checklist, nahraj Canva export zpět do Design Studia a porovnej poměr stran, paletu i odchylky proti plánu.'],
            ['time' => '38–43 min', 'title' => 'Export', 'text' => 'Použij připravený export preset a zkontroluj výsledný soubor.'],
            ['time' => '43–45 min', 'title' => 'Mini-reflexe', 'text' => 'Napiš jednu větu: co jsi změnil/a po vizuálním testu a proč.'],
        ],
        'checklist' => [
            'Headline poznám i v malém náhledu.',
            'Text má proti pozadí dostatečný vizuální kontrast.',
            'Prvky mají společné hrany nebo záměrné zarovnání.',
            'Mezery nejsou náhodné — podobné věci jsou blíž u sebe.',
            'Používám maximálně dvě rodiny písem.',
            'Akcentní barva označuje skutečně důležitou věc.',
            'Obrázek nepřebíjí hlavní sdělení.',
            'CTA je snadno dohledatelné.',
            'Export odpovídá cílovému médiu.',
            'Výsledný soubor jsem otevřel/a mimo Canvu.',
        ],
        'extension' => [
            'Vytvoř alternativu stejného plakátu pouze změnou typografické hierarchie a spacingu — bez výměny obrázku.',
            'Porovnej obě varianty v malém náhledu a napiš, která komunikuje rychleji a proč.',
        ],
        'end_challenge' => [
            'title' => 'Rychlík: 15min redesign',
            'duration' => '15–20 min',
            'brief' => 'Vytvoř druhou variantu stejného plakátu pro 1080 × 1080 px. Musí zachovat stejné sdělení, ale layout nesmí být jen oříznutá kopie.',
            'deliverables' => ['druhý layout', 'screenshot/PNG obou variant vedle sebe', '3 věty: co ses rozhodl/a změnit a proč'],
            'rubric' => ['hierarchie 0–2', 'kompozice 0–2', 'čitelnost/kontrast 0–2', 'smysluplná adaptace formátu 0–2', 'zdůvodnění 0–2'],
        ],
    ],
    'class_2a' => [
        'goal' => 'Vytvoř plakát, který komunikuje do tří sekund. Nejdřív si v Design Studiu otestuj hierarchii, kontrast, grid a exportní směr; potom stejná rozhodnutí přenes do Canvy/Figmy nebo jiného editoru.',
        'mandatory' => [
            'Dokonči startovní test a projdi doporučené části Knowledge Tour.',
            'V Design Studiu vytvoř konfiguraci se čitelným kontrastem, jasnou hierarchií a definovaným exportem.',
            'Formát A4 na výšku nebo digitální 1080 × 1350 px.',
            'Jeden jasný hlavní nadpis, klíčový informační blok a CTA.',
            'Maximálně 2 rodiny písem a omezená, záměrná barevná paleta.',
            'Použij grid/společné hrany a vědomý negativní prostor.',
            'Použité fotografie/ilustrace musí být vlastní nebo s ověřenou licencí.',
            'Před exportem proveď thumbnail, grayscale a kontrastní kontrolu.',
            'Export otevři mimo editor a zkontroluj skutečný soubor.',
        ],
        'lesson1' => [
            ['time' => '0–10 min', 'title' => 'Startovní test', 'text' => '15 otázek základů grafiky. Nejde o známku; cílem je najít témata, která potřebuješ před tvorbou rychle dovysvětlit.'],
            ['time' => '10–18 min', 'title' => 'Knowledge Tour', 'text' => 'Projdi jen doporučené vizuální lekce. U simulací nejdřív predikuj výsledek, potom experimentuj.'],
            ['time' => '18–35 min', 'title' => 'Design Studio', 'text' => 'Nastav brief, formát, grid, hierarchii, barvy a export. Ověř kontrast i thumbnail a exportuj konfiguraci/checklist pro Canvu.'],
            ['time' => '35–45 min', 'title' => 'Canva Transfer Guide + skeleton', 'text' => 'Projdi krokový handoff: rozměr, grid, textové role a paleta. Pak v Canvě přenes strukturu, spacing a barvy bez zbytečných efektů.'],
        ],
        'lesson2' => [
            ['time' => '0–5 min', 'title' => 'Návrat k briefu', 'text' => 'Porovnej Canva skeleton s konfigurací z Design Studia. Každá změna proti plánu musí mít důvod.'],
            ['time' => '5–28 min', 'title' => 'Art direction + finální vizuál', 'text' => 'Doplň obrazový materiál, detailní typografii a vlastní styl. Hierarchie a čitelnost mají přednost před efektem.'],
            ['time' => '28–37 min', 'title' => 'Visual QA + porovnání', 'text' => 'Thumbnail, grayscale, kontrast, společné hrany, CTA a zdroje. Nahraj export do porovnání Studio vs. Canva a oprav největší nalezený problém.'],
            ['time' => '37–43 min', 'title' => 'Export preset', 'text' => 'Nastav výstup podle cílového média, exportuj a otevři soubor mimo editor.'],
            ['time' => '43–45 min', 'title' => 'Odevzdání + reflexe', 'text' => 'Nahraj finální soubor a vysvětli jedno vizuální rozhodnutí, které jsi po testování změnil/a.'],
        ],
        'end_challenge' => [
            'title' => 'Rychlík: art-direction sprint',
            'duration' => '20–30 min',
            'brief' => 'Z původního plakátu vytvoř druhý art-direction směr pro stejné zadání. Obsah musí zůstat stejný, ale změň typografii, kompozici a práci s obrazem tak, aby působil jako jiná kampaň.',
            'deliverables' => ['varianta B', '1080 × 1080 sociální adaptace', 'krátké srovnání A/B', 'export preset z Design Studia'],
            'rubric' => ['jasnost konceptu 0–2', 'hierarchie 0–2', 'typografie 0–2', 'kontrast a barva 0–2', 'adaptace + zdůvodnění 0–2'],
        ],
    ],
];
