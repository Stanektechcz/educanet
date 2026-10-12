<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · další výukové cesty pro 1.A (grafika a webdesign) – statická data, žádná logika ani zápis.
 * Doplňují paths_v63_content_gfx.php (fáze E bod 3: 1.A ≥ 4 cesty). Schéma viz paths_v63.php; načítá p63_paths_for_class() přes P76_PATH_FILES.
 * Kroky „pre“ jsou statické (nic se nespouští); výpočty v `calc` (box model, flexbox, grid, velikost obrázku, tisk) přepočítává
 * tools/v76_paths_audit.php. Otázky ověřování a opakování jsou z banky týmových her (gfx.css, gfx.formats) – kategorie odpovídá kompetenci.
 */

return [
    'web_css' => [
        'title' => 'Rozvržení v CSS: box model, flexbox a mřížka',
        'goal' => 'Spočítáš, jak široký bude prvek podle box modelu, flexboxu a mřížky, a rozvržení přizpůsobíš úzkému displeji.',
        'class' => 'class_1a',
        'competency' => 'web_css_layout',
        'minutes' => 24,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Jak CSS počítá šířku', 'competency' => 'web_css_layout', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'Každý prvek je krabice: obsah, kolem něj vnitřní okraj (padding), rámeček (border) a vnější okraj (margin). Při výchozím box-sizing: content-box se padding a rámeček k šířce přičítají.',
                    'S box-sizing: border-box zahrnuje šířka i padding a rámeček – prvek s width: 200px je opravdu 200 px široký. Proto ho většina stylů nastavuje všem prvkům.',
                    'Flexbox rozdělí volné místo řádku mezi položky (flex: 1 = stejným dílem) až po odečtení mezer gap. Mřížka (grid) dělí šířku po odečtení mezer podle jednotek fr: 1fr 2fr = třetina a dvě třetiny.',
                ],
                'example' => "width: 200px; padding: 20px; border: 5px solid;\ncontent-box → 200 + 2·20 + 2·5 = 250 px\nborder-box  → 200 px (obsah 150 px)"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Jak široké to bude?', 'competency' => 'web_css_layout', 'level' => 2, 'minutes' => 5,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => '.karta { width: 200px; padding: 20px; border: 5px solid; box-sizing: content-box; }',
                        'calc' => ['kind' => 'box', 'width' => 200, 'padding' => 20, 'border' => 5, 'sizing' => 'content-box', 'result' => 250],
                        'question' => 'Jak široká bude karta na obrazovce?', 'options' => ['250 px', '200 px', '225 px', '240 px'], 'correct' => 0,
                        'reveal' => 'Karta bude široká 250 px: 200 + 2 × 20 + 2 × 5.', 'explain' => 'Při content-box se padding a rámeček přičítají k šířce na obou stranách.'],
                    ['id' => 'c2', 'static' => true, 'show' => '.karta { width: 200px; padding: 20px; border: 5px solid; box-sizing: border-box; }',
                        'calc' => ['kind' => 'box', 'width' => 200, 'padding' => 20, 'border' => 5, 'sizing' => 'border-box', 'result' => 200],
                        'question' => 'Jak široká bude karta tentokrát?', 'options' => ['200 px', '250 px', '150 px', '210 px'], 'correct' => 0,
                        'reveal' => 'Karta bude široká přesně 200 px; obsahu zbude 150 px.', 'explain' => 'Při border-box se padding a rámeček vejdou dovnitř zadané šířky.'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Kontejner 960 px, display: flex; gap: 24px; tři karty s flex: 1.',
                        'calc' => ['kind' => 'flex', 'container' => 960, 'gap' => 24, 'items' => 3, 'result' => 304],
                        'question' => 'Jak široká bude jedna karta?', 'options' => ['304 px', '320 px', '296 px', '288 px'], 'correct' => 0,
                        'reveal' => 'Mezery jsou 2 × 24 = 48 px; zbylých 912 px se dělí třemi: 304 px.', 'explain' => 'Mezer je o jednu méně než položek; flex: 1 dělí zbytek stejným dílem.'],
                    ['id' => 'c4', 'static' => true, 'show' => 'Mřížka 900 px: grid-template-columns: 1fr 2fr; gap: 30px.',
                        'calc' => ['kind' => 'grid', 'container' => 900, 'gap' => 30, 'fr' => [1, 2], 'pick' => 1, 'result' => 580],
                        'question' => 'Jak široký bude širší sloupec?', 'options' => ['580 px', '600 px', '450 px', '870 px'], 'correct' => 0,
                        'reveal' => 'Po odečtení mezery zbude 870 px; 2fr jsou dvě třetiny: 580 px.', 'explain' => 'Jednotky fr dělí jen místo, které zbude po mezerách.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Řada karet, která se zalomí', 'competency' => 'web_css_layout', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Seřaď kroky tak, aby vznikla řada karet, která se na úzkém displeji zalomí pod sebe.',
                'lines' => ['Napiš HTML se třemi kartami v jednom kontejneru', 'Nastav kontejneru display: flex', 'Přidej mezeru mezi kartami gap: 1rem', 'Povol zalamování flex-wrap: wrap a kartám minimální šířku', 'Ověř rozvržení na šířce 390 px i na počítači'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'web_css_layout', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['gfx.css.001', 'gfx.css.002', 'gfx.css.004', 'gfx.css.005', 'gfx.css.006', 'gfx.css.007', 'gfx.css.008', 'gfx.css.009']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'web_css_layout', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['gfx.css.010', 'gfx.css.011', 'gfx.css.012', 'gfx.css.013'],
                    ['gfx.css.014', 'gfx.css.015', 'gfx.css.016', 'gfx.css.017'],
                    ['gfx.css.019', 'gfx.css.020', 'gfx.css.021', 'gfx.css.022'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'web_css_layout', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['gfx.css.001', 'gfx.css.004', 'gfx.css.006', 'gfx.css.009', 'gfx.css.010', 'gfx.css.013', 'gfx.css.016', 'gfx.css.019', 'gfx.css.023', 'gfx.css.024']],
    ],
    'gfx_formats' => [
        'title' => 'Grafické formáty a export pro web i tisk',
        'goal' => 'Vybereš formát podle obsahu a použití, odhadneš velikost obrázku v paměti a spočítáš pixely potřebné pro tisk.',
        'class' => 'class_1a',
        'competency' => 'gfx_formats',
        'minutes' => 24,
        'steps' => [
            ['id' => 'explain', 'type' => 'explain', 'title' => 'Formát podle obsahu', 'competency' => 'gfx_formats', 'level' => 1, 'minutes' => 3,
                'paragraphs' => [
                    'Fotografie s plynulými přechody patří do JPEG nebo WebP (ztrátová komprese, malé soubory). Grafika s ostrými hranami, text a průhlednost patří do PNG, logo a ikony nejlépe do vektorového SVG, které se nerozmaže při žádné velikosti.',
                    'Nekomprimovaný obrázek zabere v paměti šířka × výška × počet bajtů na pixel: RGB má 3 bajty, RGBA s průhledností 4 bajty. Komprese pak velikost souboru výrazně zmenší.',
                    'Pro tisk rozhoduje hustota bodů: obvykle 300 DPI. Potřebné pixely = rozměr v palcích × DPI, palec má 2,54 cm. Pro web rozhodují jen pixely – údaj DPI prohlížeč ignoruje.',
                ],
                'example' => "1920 × 1080 × 3 B = 6 220 800 B ≈ 6,2 MB (nekomprimovaně)\n10 cm při 300 DPI: 10 ÷ 2,54 × 300 ≈ 1181 px"],
            ['id' => 'predict', 'type' => 'pre', 'title' => 'Kolik to bude?', 'competency' => 'gfx_formats', 'level' => 2, 'minutes' => 5,
                'cases' => [
                    ['id' => 'c1', 'static' => true, 'show' => 'Fotografie 1920 × 1080 px v barvách RGB (3 bajty na pixel), bez komprese.',
                        'calc' => ['kind' => 'raw', 'w' => 1920, 'h' => 1080, 'bytes' => 3, 'result' => '6,2'],
                        'question' => 'Kolik megabajtů (MB, milion bajtů) zabere v paměti?', 'options' => ['6,2 MB', '2,1 MB', '62 MB', '0,6 MB'], 'correct' => 0,
                        'reveal' => '1920 × 1080 × 3 = 6 220 800 bajtů ≈ 6,2 MB.', 'explain' => 'Počet pixelů krát bajty na pixel; komprese JPEG nebo WebP soubor pak výrazně zmenší.'],
                    ['id' => 'c2', 'static' => true, 'show' => 'Grafika na sociální síť 1080 × 1350 px s průhledností RGBA (4 bajty na pixel), bez komprese.',
                        'calc' => ['kind' => 'raw', 'w' => 1080, 'h' => 1350, 'bytes' => 4, 'result' => '5,8'],
                        'question' => 'Kolik megabajtů zabere v paměti?', 'options' => ['5,8 MB', '4,4 MB', '1,5 MB', '58 MB'], 'correct' => 0,
                        'reveal' => '1080 × 1350 × 4 = 5 832 000 bajtů ≈ 5,8 MB.', 'explain' => 'Průhlednost přidává čtvrtý bajt na pixel (kanál alfa).'],
                    ['id' => 'c3', 'static' => true, 'show' => 'Pohlednice 10 × 15 cm k tisku při 300 DPI.',
                        'calc' => ['kind' => 'print', 'cm' => [10, 15], 'dpi' => 300, 'result' => '1181 × 1772'],
                        'question' => 'Kolik pixelů potřebuješ?', 'options' => ['1181 × 1772 px', '300 × 450 px', '3000 × 4500 px', '590 × 886 px'], 'correct' => 0,
                        'reveal' => '10 ÷ 2,54 × 300 ≈ 1181 px a 15 ÷ 2,54 × 300 ≈ 1772 px.', 'explain' => 'Centimetry převeď na palce a vynásob hustotou tisku.'],
                    ['id' => 'c4', 'static' => true, 'show' => 'Logo školního kroužku s průhledným pozadím a ostrými hranami, použité na webu v různých velikostech.',
                        'question' => 'Jaký formát zvolíš?', 'options' => ['SVG (případně PNG)', 'JPEG', 'GIF s 256 barvami', 'BMP'], 'correct' => 0,
                        'reveal' => 'SVG je vektor – ostrý v každé velikosti a umí průhlednost; PNG je rastrová záloha.', 'explain' => 'JPEG neumí průhlednost a na ostrých hranách vytváří artefakty.'],
                ]],
            ['id' => 'order', 'type' => 'parsons', 'title' => 'Postup exportu obrázku', 'competency' => 'gfx_formats', 'level' => 2, 'minutes' => 5,
                'prompt' => 'Seřaď kroky tak, jak správně připravíš obrázek k použití.',
                'lines' => ['Zjisti, kde se obrázek použije (web, tisk, sociální síť)', 'Vyber formát podle obsahu (fotka, grafika, průhlednost)', 'Nastav rozměr v pixelech podle místa použití', 'Exportuj a zkontroluj velikost souboru', 'Ověř kvalitu v náhledu na mobilu i počítači'],
                'accept' => []],
            ['id' => 'recall', 'type' => 'retrieval', 'title' => 'Co si pamatuješ', 'competency' => 'gfx_formats', 'level' => 1, 'minutes' => 4, 'count' => 4,
                'pool' => ['gfx.formats.001', 'gfx.formats.002', 'gfx.formats.003', 'gfx.formats.004', 'gfx.formats.005', 'gfx.formats.006', 'gfx.formats.007', 'gfx.formats.008']],
            ['id' => 'verify', 'type' => 'verify', 'title' => 'Ověř, co umíš', 'competency' => 'gfx_formats', 'level' => 2, 'minutes' => 5,
                'variants' => [
                    ['gfx.formats.009', 'gfx.formats.010', 'gfx.formats.011', 'gfx.formats.012'],
                    ['gfx.formats.013', 'gfx.formats.014', 'gfx.formats.015', 'gfx.formats.016'],
                    ['gfx.formats.018', 'gfx.formats.019', 'gfx.formats.020', 'gfx.formats.021'],
                ]],
            ['id' => 'reflect', 'type' => 'reflect', 'title' => 'Jak ti to šlo', 'competency' => 'gfx_formats', 'level' => 2, 'minutes' => 2],
        ],
        'spaced' => ['count' => 4, 'minutes' => 3, 'pool' => ['gfx.formats.002', 'gfx.formats.004', 'gfx.formats.006', 'gfx.formats.008', 'gfx.formats.010', 'gfx.formats.014', 'gfx.formats.018', 'gfx.formats.022', 'gfx.formats.023', 'gfx.formats.024']],
    ],
];
