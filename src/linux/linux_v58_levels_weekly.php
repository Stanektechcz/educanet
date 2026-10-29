<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Aréna – banka úloh pro „Týdenní hádanku“ (ARN-01) + registrace kontextu `weekly:<RRRRtTT>`.
 *
 * Soubor načítá automaticky jádro Labu (linux_v58_ext.php → lab58_load_extensions), proto je tu i registrace
 * kontextu – jinak by ho JSON API Labu (lab_v57_api.php) neznalo (lab58_context_valid()). Skutečná logika
 * (rotace, žebříčky, uzávěrka, odhalení) je v arena_v58_weekly.php – sem se natahuje jen (require_once), aby
 * ho tenhle auto-načítaný soubor „líně“ přitáhl s sebou.
 *
 * Úlohy jsou typu golf (jeden řádek, přesný výstup, skryté testy na několika semínkách – lab57_golf_eval()).
 * Balíček 'tyden' je schválně skrytý z běžného procvičování (classes = smyšlená třída, kterou nikdo nemá) –
 * hraje se výhradně přes kontext weekly:<týden>, ne přes ?view=lab&uroven=tyden-N.
 */

require_once __DIR__ . '/arena_v58_weekly.php';

const ARENA58_WEEKLY_PACK_ID = 'tyden';

/** Id skrytého balíčku Týdenní hádanky – ostatní kód ho používá k vyloučení z běžných výpisů balíčků. */
function arena58_weekly_pack_id(): string
{
    return ARENA58_WEEKLY_PACK_ID;
}

/** Krátký seznam příkazů z referenčního řešení pro chipy „Příkazy k použití“ (stejný trik jako u golf-1..6 v57). */
function lab58_weekly_commands_from(string $reference): array
{
    $tokens = preg_split('/[\s|;&<>]+/', $reference) ?: [];
    return array_values(array_unique(array_filter($tokens, static fn(string $t): bool => preg_match('/^[a-z][a-z0-9]*$/', $t) === 1 && function_exists('lab57_command_registry') && isset(lab57_command_registry()[$t]))));
}

/**
 * @param array{tests?:int} $golf
 * @param list<string> $hints
 */
function lab58_weekly_level(string $id, string $title, string $task, string $reference, callable $build, array $hints, string $learn, int $difficulty = 1): array
{
    $points = [1 => 100, 2 => 150, 3 => 200][$difficulty] ?? 100;
    return [
        'id' => $id, 'pack' => ARENA58_WEEKLY_PACK_ID, 'type' => 'golf', 'title' => $title, 'difficulty' => $difficulty, 'points' => $points, 'minutes' => 8,
        'story' => 'Týdenní hádanka: napiš jeden příkaz s přesně požadovaným výstupem. Po odevzdání se ověří na několika skrytých sadách dat – stejný postup musí fungovat pokaždé.',
        'task' => $task . ' Až bude výstup sedět, napiš submit (odevzdá tvůj poslední příkaz). Vyhrává nejkratší funkční řešení.',
        'commands' => lab58_weekly_commands_from($reference),
        'hints' => $hints,
        'golf' => ['tests' => 5, 'reference' => $reference],
        'build' => $build,
        'solution' => static fn(Lab57World $w): array => [$reference, 'submit'],
        'learn' => $learn,
    ];
}

/** @return list<array> */
function lab58_weekly_bank(): array
{
    return [
        lab58_weekly_level('tyden-1', 'Kolik slov', 'V souboru poznamky.txt zjisti, kolik SLOV (ne řádků) obsahuje, a vypiš jen to číslo.', 'wc -w < poznamky.txt',
            static function (Lab57World $w, Lab57Rng $r): void { lab57_home_file($w, 'poznamky.txt', lab57_join(lab57_filler($r, $r->int(15, 40)))); },
            ['wc umí počítat i slova, ne jen řádky.', 'Přesměruj obsah na vstup, ať wc nevypíše i jméno souboru: wc -w < poznamky.txt'],
            'wc -w < soubor vypíše jen počet slov; přesměrování < skryje jméno souboru z výstupu.', 1),

        lab58_weekly_level('tyden-2', 'Poslední hlášení', 'V souboru hlaseni.txt najdi a vypiš jen úplně POSLEDNÍ řádek.', 'tail -n 1 hlaseni.txt',
            static function (Lab57World $w, Lab57Rng $r): void { lab57_home_file($w, 'hlaseni.txt', lab57_join(lab57_filler($r, $r->int(12, 30)))); },
            ['tail vypisuje konec souboru.', 'tail -n 1 soubor vypíše jen jeden poslední řádek.'],
            'tail -n 1 vypíše poslední řádek souboru; opak k head -n 1 (první řádek).', 1),

        lab58_weekly_level('tyden-3', 'Sestupně podle čísla', 'Soubor cisla.txt obsahuje čísla, jedno na řádek. Seřaď je SESTUPNĚ (od největšího po nejmenší).', 'sort -rn cisla.txt',
            static function (Lab57World $w, Lab57Rng $r): void {
                $nums = array_slice($r->shuffle(range(1, 80)), 0, $r->int(10, 18));
                lab57_home_file($w, 'cisla.txt', lab57_join(array_map('strval', $nums)));
            },
            ['sort bez voleb řadí jako text, takže „10“ vyjde před „9“.', 'sort -n řadí podle číselné hodnoty, -r otočí pořadí: sort -rn'],
            'sort -n řadí čísla podle hodnoty, ne jako text; -r pořadí obrátí.', 1),

        lab58_weekly_level('tyden-4', 'Otočené heslo', 'V souboru heslo.txt je jedno tajné slovo. Vypiš ho POZPÁTKU (znaky v obráceném pořadí).', 'rev heslo.txt',
            static function (Lab57World $w, Lab57Rng $r): void {
                $alphabet = 'abcdefghijklmnopqrstuvwxyz0123456789';
                $word = '';
                for ($i = $r->int(8, 14); $i > 0; $i--) $word .= $alphabet[$r->int(0, strlen($alphabet) - 1)];
                lab57_home_file($w, 'heslo.txt', lab57_join([$word]));
            },
            ['Příkaz rev otočí pořadí znaků na každém řádku.', 'rev heslo.txt'],
            'rev obrátí pořadí znaků na řádku – hodí se třeba na rychlou kontrolu palindromů.', 1),

        lab58_weekly_level('tyden-5', 'Sklad bez duplicit', 'Soubor sklad.txt je seznam položek na skladě a hodně se v něm opakují. Vypiš každou položku JEN JEDNOU, seřazenou abecedně.', 'sort -u sklad.txt',
            static function (Lab57World $w, Lab57Rng $r): void {
                $pool = ['sroubek', 'matice', 'kabel', 'baterie', 'zarovka', 'klic', 'drat', 'pojistka'];
                $items = [];
                for ($i = $r->int(16, 26); $i > 0; $i--) $items[] = (string)$r->pick($pool);
                lab57_home_file($w, 'sklad.txt', lab57_join($items));
            },
            ['sort seřadí řádky abecedně.', 'Volba -u navíc odstraní duplicity v jednom kroku: sort -u sklad.txt'],
            'sort -u = seřadit a zároveň odstranit duplicitní řádky najednou.', 2),

        lab58_weekly_level('tyden-6', 'Očísluj kroky', 'Soubor postup.txt obsahuje kroky návodu bez čísel. Očísluj řádky (stejně jako to dělá příkaz nl).', 'nl postup.txt',
            static function (Lab57World $w, Lab57Rng $r): void { lab57_home_file($w, 'postup.txt', lab57_join(lab57_filler($r, $r->int(5, 9)))); },
            ['Příkaz nl přidá pořadové číslo na začátek každého neprázdného řádku.', 'nl postup.txt'],
            'nl očísluje řádky souboru – šikovné pro návody a výpisy kroků.', 2),

        lab58_weekly_level('tyden-7', 'Malými písmeny', 'Soubor nadpisy.txt je napsaný celý VELKÝMI písmeny. Převeď celý text na malá písmena.', "tr 'A-Z' 'a-z' < nadpisy.txt",
            static function (Lab57World $w, Lab57Rng $r): void {
                $lines = array_map(static fn(string $l): string => mb_strtoupper($l), lab57_filler($r, $r->int(4, 8)));
                lab57_home_file($w, 'nadpisy.txt', lab57_join($lines));
            },
            ['tr převádí znaky podle dvou sad – první sada se mění na druhou.', "tr čte jen ze vstupu (roury), ne jako argument souboru: tr 'A-Z' 'a-z' < nadpisy.txt"],
            "tr 'A-Z' 'a-z' promění velká písmena na malá; obě sady musí mít stejnou délku.", 2),

        lab58_weekly_level('tyden-8', 'Skrytá zpráva', 'Soubor tajenka.txt obsahuje text zakódovaný přes base64. Dekóduj ho a vypiš původní text.', 'base64 -d tajenka.txt',
            static function (Lab57World $w, Lab57Rng $r): void {
                $word = (string)$r->pick(['terminal', 'sifra', 'heslo', 'pristup', 'system', 'server', 'soubor', 'prikaz']);
                lab57_home_file($w, 'tajenka.txt', lab57_join([base64_encode($word)]));
            },
            ['Zakódování zpět rozkóduje volba -d (decode).', 'base64 -d tajenka.txt'],
            'base64 jen mění formu dat (kódování), není to šifrování – dekóduje ho kdokoli s -d.', 2),

        lab58_weekly_level('tyden-9', 'Kolik bylo výpadků', 'V souboru sluzba.log zjisti, KOLIKRÁT se v něm objevuje slovo TIMEOUT (přesně, velkými písmeny). Vypiš jen to číslo.', 'grep -c TIMEOUT sluzba.log',
            static function (Lab57World $w, Lab57Rng $r): void {
                $rows = [];
                for ($i = $r->int(35, 70); $i > 0; $i--) {
                    $rows[] = date('H:i:s', $w->now - $i * 41) . ' ' . (string)$r->pick(['OK', 'OK', 'OK', 'WARN', 'TIMEOUT', 'INFO']) . ' pripojeni-' . $r->int(1, 999);
                }
                lab57_home_file($w, 'sluzba.log', lab57_join($r->shuffle($rows)));
            },
            ['grep umí místo řádků rovnou vypsat jejich počet.', 'grep -c TIMEOUT sluzba.log'],
            'grep -c spočítá řádky se shodou, aniž bys je musel(a) ručně počítat.', 2),

        lab58_weekly_level('tyden-10', 'Kolik podsložek', 'Ve složce ulohy je pár souborů a pár podsložek. Zjisti, KOLIK podsložek (ne souborů) je přímo v ulohy, a vypiš jen to číslo.', 'find ulohy -mindepth 1 -maxdepth 1 -type d | wc -l',
            static function (Lab57World $w, Lab57Rng $r): void {
                $names = $r->shuffle(['obrazky', 'texty', 'zaloha', 'stare', 'nove', 'tisk', 'web', 'data', 'sablony', 'vysledky']);
                $dirs = $r->int(4, 7);
                for ($i = 0; $i < $dirs; $i++) {
                    for ($f = $r->int(1, 3); $f > 0; $f--) lab57_home_file($w, 'ulohy/' . $names[$i] . '/soubor' . $f . '.txt', "obsah\n");
                }
                for ($f = $r->int(1, 3); $f > 0; $f--) lab57_home_file($w, 'ulohy/prehled' . $f . '.txt', "obsah\n");
            },
            ['find umí hledat podle typu (-type d = jen složky) a omezit hloubku hledání (-maxdepth, -mindepth).', 'find ulohy -mindepth 1 -maxdepth 1 -type d | wc -l'],
            'find -type d najde jen složky; -maxdepth/-mindepth omezí, jak hluboko a odkud find hledá.', 2),

        lab58_weekly_level('tyden-11', 'Vítěz hlasování', 'Soubor hlasovani.txt má na každém řádku jeden hlas pro oběd. Zjisti, která možnost VYHRÁLA (má nejvíc hlasů), a vypiš řádek s počtem a názvem (jako to dělá uniq -c).', 'sort hlasovani.txt | uniq -c | sort -rn | head -1',
            static function (Lab57World $w, Lab57Rng $r): void {
                $pool = ['svickova', 'gulas', 'rizoto', 'polevka', 'spagety'];
                $votes = [];
                for ($i = $r->int(24, 40); $i > 0; $i--) $votes[] = (string)$r->pick($pool);
                lab57_home_file($w, 'hlasovani.txt', lab57_join($r->shuffle($votes)));
            },
            ['Aby uniq -c počítal stejné řádky vedle sebe, musí být nejdřív seřazené.', 'sort hlasovani.txt | uniq -c | sort -rn | head -1'],
            'sort | uniq -c | sort -rn | head -N je klasický postup pro hledání nejčastějších hodnot v datech.', 3),

        lab58_weekly_level('tyden-12', 'Součet nákupu', 'Soubor nakup.csv má hlavičku polozka,cena. Sečti sloupec cena (bez hlavičky) a vypiš celkový součet.', "awk -F, 'NR>1{s+=\$2} END{print s}' nakup.csv",
            static function (Lab57World $w, Lab57Rng $r): void {
                $names = $r->shuffle(['tuzka', 'sesit', 'guma', 'pravitko', 'fix', 'lepidlo', 'nuzky', 'pero']);
                $rows = ['polozka,cena'];
                foreach (array_slice($names, 0, $r->int(6, 8)) as $name) $rows[] = $name . ',' . $r->int(5, 200);
                lab57_home_file($w, 'nakup.csv', lab57_join($rows));
            },
            ['awk umí sčítat hodnoty ve sloupci do proměnné a vypsat je v bloku END.', "awk -F, 'NR>1{s+=\$2} END{print s}' nakup.csv (NR>1 přeskočí hlavičku)"],
            'awk s proměnnou a END{} spočítá součty a jiné souhrny po řádcích souboru.', 3),

        lab58_weekly_level('tyden-13', 'Splněné úkoly', 'V souboru ukoly.txt nahraď každý výskyt slova TODO slovem HOTOVO a vypiš celý upravený text.', "sed 's/TODO/HOTOVO/g' ukoly.txt",
            static function (Lab57World $w, Lab57Rng $r): void {
                $rows = [];
                for ($i = $r->int(6, 10); $i > 0; $i--) {
                    $line = implode(' ', lab57_filler($r, 1));
                    $rows[] = $r->int(0, 2) === 0 ? 'TODO: ' . $line : $line;
                }
                lab57_home_file($w, 'ukoly.txt', lab57_join($rows));
            },
            ['sed nahrazuje text příkazem s/co/čím/.', "Přípona g nahradí všechny výskyty na řádku: sed 's/TODO/HOTOVO/g' ukoly.txt"],
            "sed 's/vzor/náhrada/g' je základní nástroj pro hromadné nahrazování textu v souboru.", 3),

        lab58_weekly_level('tyden-14', 'Nejlepší výsledek', 'Soubor vysledky.txt má na každém řádku jméno a body oddělené mezerou (Jméno Body). Zjisti, KDO má nejvíc bodů, a vypiš jen jeho jméno.', "sort -k2 -n vysledky.txt | tail -1 | cut -d' ' -f1",
            static function (Lab57World $w, Lab57Rng $r): void {
                $names = $r->shuffle(lab57_first_names());
                $scores = $r->shuffle(range(10, 99));
                $rows = [];
                for ($i = 0; $i < 10; $i++) $rows[] = $names[$i] . ' ' . $scores[$i];
                lab57_home_file($w, 'vysledky.txt', lab57_join($rows));
            },
            ['sort -k2 -n seřadí řádky podle druhého sloupce (čísla) vzestupně.', "sort -k2 -n vysledky.txt | tail -1 | cut -d' ' -f1"],
            'sort -k volí, podle kterého sloupce se řadí; tail -1 pak vezme poslední (= nejvyšší) řádek.', 3),
    ];
}

lab58_register_pack(
    [
        'id' => ARENA58_WEEKLY_PACK_ID, 'title' => 'Týdenní hádanka', 'description' => 'Jedna golfová úloha pro celou školu, nová každé pondělí.',
        'order' => 25, 'classes' => ['_hidden_weekly_'], 'unlock' => 'free', 'icon' => '🧩', 'tone' => 'gold', 'inspired' => 've stylu CodinGame', 'source' => 'v58',
    ],
    'lab58_weekly_bank'
);

lab58_register_context('weekly', [
    'label' => 'Týdenní hádanka',
    'access' => static fn(array $level, array $ctx): ?string => function_exists('arena58_weekly_access') ? arena58_weekly_access($level, $ctx) : 'Týdenní hádanka není dostupná.',
    'levels' => static fn(array $ctx): ?array => function_exists('arena58_weekly_levels_for') ? arena58_weekly_levels_for($ctx) : [],
    'on_complete' => static function (array $ctx, array $level, array $event): void { if (function_exists('arena58_weekly_on_complete')) arena58_weekly_on_complete($ctx, $level, $event); },
    'info' => static fn(array $ctx): ?array => function_exists('arena58_weekly_info') ? arena58_weekly_info($ctx) : null,
    'board' => static fn(array $ctx): array => function_exists('arena58_weekly_board') ? arena58_weekly_board($ctx) : [],
    'first_blood' => false,
    'reset' => true,
]);
