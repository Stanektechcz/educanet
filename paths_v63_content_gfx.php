<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · obsah výukových cest pro 1.A (grafika a webdesign) – statická data, žádná logika ani zápis.
 *
 * Schéma viz paths_v63.php. Texty jsou výukový obsah (zůstávají česky). Otázky ověřování a opakování se berou
 * z banky týmových her (teamgames_v58_bank_gfx_*.php) podle id; varianty ověření jsou ≥ 3 a navzájem disjunktní.
 * Kroky typu „pre“ ve statickém režimu (static) nespouštějí simulátor – správnost kontrastu hlídá audit výpočtem WCAG.
 */

return [
    'web_html' => [
        'title' => 'Kostra webové stránky',
        'goal' => 'Poskládáš správnou kostru HTML stránky, poznáš sémantické prvky a pořadí nadpisů.',
        'class' => 'class_1a',
        'competency' => 'web_html_structure',
        'minutes' => 22,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Z čeho se stránka skládá', 'competency' => 'web_html_structure', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'Každá HTML stránka má kostru. První řádek <!DOCTYPE html> říká prohlížeči, že jde o moderní HTML5. Element <html lang="cs"> určuje jazyk stránky, což potřebují čtečky obrazovky.',
                    'Uvnitř <head> je to, co se nezobrazuje: kódování <meta charset="utf-8"> a titulek <title>. Viditelný obsah patří do <body>.',
                    'V <body> používáme sémantické prvky podle významu: <header> hlavička, <nav> navigace, <main> hlavní obsah (jen jednou na stránce), <footer> patička. Hlavní nadpis <h1> bývá jeden, podnadpisy jdou po řadě h2, h3 bez přeskakování úrovní.',
                ],
                'example' => "<!DOCTYPE html>\n<html lang=\"cs\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>Moje stránka</title>\n</head>\n<body>\n  <main>\n    <h1>Ahoj</h1>\n  </main>\n</body>\n</html>"],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Poskládej kostru stránky', 'competency' => 'web_html_structure', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Seřaď řádky tak, aby vznikla platná kostra stránky s titulkem a nadpisem. Kódování a titulek mohou být v hlavičce v libovolném pořadí.',
                'lines' => ['<!DOCTYPE html>', '<html lang="cs">', '<head>', '<meta charset="utf-8">', '<title>Moje stránka</title>', '</head>', '<body>', '<main>', '<h1>Ahoj</h1>', '</main>', '</body>', '</html>'],
                'accept' => [[0, 1, 2, 4, 3, 5, 6, 7, 8, 9, 10, 11]]],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'web_html_structure', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['gfx.html.001', 'gfx.html.002', 'gfx.html.003', 'gfx.html.004', 'gfx.html.005', 'gfx.html.006', 'gfx.html.008', 'gfx.html.009']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'web_html_structure', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['gfx.html.011', 'gfx.html.012', 'gfx.html.015', 'gfx.html.016'],
                    ['gfx.html.013', 'gfx.html.014', 'gfx.html.018', 'gfx.html.020'],
                    ['gfx.html.019', 'gfx.html.021', 'gfx.html.022', 'gfx.html.023'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'web_html_structure', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['gfx.html.001', 'gfx.html.002', 'gfx.html.007', 'gfx.html.010', 'gfx.html.011', 'gfx.html.012', 'gfx.html.013', 'gfx.html.014', 'gfx.html.018', 'gfx.html.020']],
    ],
    'gfx_contrast' => [
        'title' => 'Kontrast barev a čitelnost',
        'goal' => 'Odhadneš, jestli dvojice barev splní kontrast WCAG AA, a vysvětlíš, jak ho ověřit a opravit.',
        'class' => 'class_1a',
        'competency' => 'gfx_color_contrast',
        'minutes' => 24,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Proč záleží na kontrastu', 'competency' => 'gfx_color_contrast', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'Kontrast je poměr jasu textu a pozadí. Čím vyšší poměr, tím snáz se text čte, i na slunci nebo při horším zraku.',
                    'Podle WCAG 2.2 úrovně AA potřebuje běžný text poměr aspoň 4,5:1 a velký text aspoň 3:1. Černá na bílé má 21:1, šedá #767676 na bílé je těsně nad hranicí (4,54:1).',
                    'Poměr se nedá odhadnout od oka: dvě barvy, které se zdají odlišné, mohou mít podobný jas. Vždy ho změř kontrolním nástrojem a důležitou informaci nikdy nepředávej jen barvou.',
                ],
                'example' => "#000000 na #ffffff = 21:1\n#767676 na #ffffff = 4,54:1 (splní AA)\n#777777 na #ffffff = 4,48:1 (nesplní AA)"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Splní to AA?', 'competency' => 'gfx_color_contrast', 'level' => 2, 'minutes' => 5,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => 'Text #767676 na pozadí #ffffff (běžná velikost).', 'fg' => '#767676', 'bg' => '#ffffff', 'ratio' => '4,54', 'passes' => true,
                        'question' => 'Splní tato dvojice barev kontrast AA (4,5:1) pro běžný text?', 'options' => ['Splní', 'Nesplní'], 'correct' => 0,
                        'reveal' => 'Změřený poměr je 4,54:1, takže hranici 4,5:1 těsně splní.', 'explain' => 'Šedá #767676 je nejsvětlejší šedá, která na bílé ještě projde pro běžný text.'],
                    ['id' => 'c2', 'static' => true, 'show' => 'Text #777777 na pozadí #ffffff (běžná velikost).', 'fg' => '#777777', 'bg' => '#ffffff', 'ratio' => '4,48', 'passes' => false,
                        'question' => 'Splní tato dvojice barev kontrast AA (4,5:1) pro běžný text?', 'options' => ['Splní', 'Nesplní'], 'correct' => 1,
                        'reveal' => 'Změřený poměr je 4,48:1, hranici 4,5:1 těsně nesplní.', 'explain' => 'I nepatrný rozdíl v odstínu může rozhodnout. Proto se poměr měří, ne odhaduje.'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Bílý text #ffffff na žlutém pozadí #f3b21f.', 'fg' => '#ffffff', 'bg' => '#f3b21f', 'ratio' => '1,88', 'passes' => false,
                        'question' => 'Splní tato dvojice barev kontrast AA (4,5:1) pro běžný text?', 'options' => ['Splní', 'Nesplní'], 'correct' => 1,
                        'reveal' => 'Změřený poměr je jen 1,88:1, text na žluté se čte špatně.', 'explain' => 'Světlý text na světlé barvě má nízký kontrast. Na žlutou patří tmavý text.'],
                    ['id' => 'c4', 'static' => true, 'show' => 'Bílý text #ffffff na modrém pozadí #0b5ed7.', 'fg' => '#ffffff', 'bg' => '#0b5ed7', 'ratio' => '5,84', 'passes' => true,
                        'question' => 'Splní tato dvojice barev kontrast AA (4,5:1) pro běžný text?', 'options' => ['Splní', 'Nesplní'], 'correct' => 0,
                        'reveal' => 'Změřený poměr je 5,84:1, hranici 4,5:1 splní s rezervou.', 'explain' => 'Sytá tmavší modrá s bílým textem je bezpečná volba pro tlačítka.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Postup kontroly kontrastu', 'competency' => 'gfx_color_contrast', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Seřaď kroky tak, jak správně ověříš a opravíš kontrast textu.',
                'lines' => ['Vyber barvu textu a barvu pozadí', 'Změř poměr kontrastu nástrojem', 'Porovnej poměr s hranicí 4,5:1 (u velkého textu 3:1)', 'Když poměr nestačí, ztmav text nebo zesvětli pozadí', 'Změř znovu a výsledek zapiš'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'gfx_color_contrast', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['gfx.color.001', 'gfx.color.002', 'gfx.color.004', 'gfx.color.005', 'gfx.color.006', 'gfx.color.007', 'gfx.color.008', 'gfx.color.010']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'gfx_color_contrast', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['gfx.color.009', 'gfx.color.011', 'gfx.color.013', 'gfx.color.015'],
                    ['gfx.color.014', 'gfx.color.017', 'gfx.color.018', 'gfx.color.020'],
                    ['gfx.color.021', 'gfx.color.022', 'gfx.color.023', 'gfx.color.024'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'gfx_color_contrast', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['gfx.color.003', 'gfx.color.006', 'gfx.color.007', 'gfx.color.008', 'gfx.color.009', 'gfx.color.013', 'gfx.color.015', 'gfx.color.020', 'gfx.color.023', 'gfx.color.024']],
    ],
];
