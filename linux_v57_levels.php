<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57/v58 · Linux Lab – balíčky úrovní (1/2): Start, Průzkumník, Kódy a formáty.
 *
 * v58 (CNT-01): úrovně jsou deklarativní – žádné closures v datech úrovní (jen řetězce, čísla,
 * pole). Svět staví `generate` (seznam [generátor, parametry] z katalogu core.* v
 * linux_v58_levels_generators.php, případně pojmenovaný generátor v57_<id> pro bezpečnou
 * levelovou logiku), cíl popisují `checks` ([kontrola, parametry]), `answer` (šablona řetězce)
 * a `solution` (seznam šablon příkazů). Šablony viz lab58_fill(): {CODE} {HOME} {USER} {HOST}
 * {N} {TOKEN} {DECOY} {f:fakt}. Typy úrovní beze změny:
 *   code   – najdi kód EDU-XXXX-XXXX a odevzdej ho příkazem submit
 *   answer – zjisti hodnotu a odpověz příkazem answer
 *   check  – oprav/uprav systém; kontroly běží automaticky po každém příkazu
 *   golf   – napiš jednořádkový příkaz s přesným výstupem, odevzdej submit
 * 'solution' je referenční postup; audit ho spouští na více semínkách, takže je každá úroveň
 * prokazatelně řešitelná i s jinými (žákovými) daty. Pole 'v' se při této úpravě zvýšilo –
 * staré uložené instance se tím bezpečně zahodí (jiný mechanismus stavby světa = jiná data).
 */

function lab57_packs(): array
{
    return [
        'start' => ['title' => 'Start v terminálu', 'icon' => '›_', 'lead' => 'První kroky: kde jsem, co tu je, složky, soubory, hledání.', 'inspired' => 'úvod do příkazové řádky', 'tone' => 'teal'],
        'quest' => ['title' => 'Průzkumník', 'icon' => '⌕', 'lead' => 'Najdi ukrytý kód v souborovém systému. Každá úroveň naučí jeden trik.', 'inspired' => 've stylu OverTheWire Bandit', 'tone' => 'blue'],
        'kody' => ['title' => 'Kódy a formáty', 'icon' => '⌗', 'lead' => 'Base64, šestnáctková soustava, ROT13, kontrolní součty.', 'inspired' => 've stylu picoCTF General Skills', 'tone' => 'violet'],
        'sit' => ['title' => 'Síťový detektiv', 'icon' => '⇄', 'lead' => 'Testuj připojení krok za krokem: adresa, brána, DNS, internet, web.', 'inspired' => 'diagnostika sítě a internetu', 'tone' => 'green'],
        'opravna' => ['title' => 'Opravna serverů', 'icon' => '⚙', 'lead' => 'Něco na serveru nefunguje. Najdi příčinu v logu a oprav to.', 'inspired' => 've stylu SadServers', 'tone' => 'amber'],
        'golf' => ['title' => 'Shell golf', 'icon' => '⛳', 'lead' => 'Jeden řádek, přesný výstup, skryté testy. Kratší řešení = lepší.', 'inspired' => 've stylu CodinGame', 'tone' => 'rose'],
    ];
}

/**
 * @return array<string,array> všechny úrovně podle id (v57 balíčky + v58 balíčky/zdroje přes lab58_extra_levels)
 *
 * v57 balíčky teď procházejí stejnou přípravou (lab58_level_prepare) jako v58 balíčky: doplní
 * výchozí hodnoty a převede deklarativní generate/checks/answer/solution na closures, které
 * zbytek enginu (build/eval_checks/answer/solution) očekává beze změny.
 */
function lab57_levels(): array
{
    static $levels = null;
    static $ver = -1;
    $current = function_exists('lab58_registry_version') ? lab58_registry_version() : 0;
    if (is_array($levels) && $ver === $current) return $levels;
    $ver = $current;
    $levels = [];
    $providers = ['lab57_levels_start', 'lab57_levels_quest', 'lab57_levels_kody', 'lab57_levels_sit', 'lab57_levels_opravna', 'lab57_levels_golf'];
    foreach ($providers as $provider) {
        if (!function_exists($provider)) continue;
        foreach ($provider() as $no => $level) {
            $packId = (string)($level['pack'] ?? '');
            if (function_exists('lab58_level_prepare')) {
                $prepared = lab58_level_prepare($level, $packId, $no + 1);
                if (is_string($prepared)) {
                    if (function_exists('lab58_reg_error')) lab58_reg_error($prepared);
                    continue;
                }
                $level = $prepared;
            } else {
                $level['no'] = $no + 1;
                $level += ['v' => 1, 'difficulty' => 1, 'points' => 100, 'minutes' => 5, 'commands' => [], 'hints' => [], 'world' => [], 'topology' => false, 'learn' => ''];
            }
            $levels[$level['id']] = $level;
        }
    }
    if (function_exists('lab58_extra_levels')) $levels += lab58_extra_levels($levels);
    return $levels;
}

function lab57_level(string $id): ?array
{
    return lab57_levels()[$id] ?? null;
}

/** @return list<array> úrovně balíčku v pořadí */
function lab57_pack_levels(string $pack): array
{
    static $cache = [];
    static $ver = -1;
    $current = function_exists('lab58_registry_version') ? lab58_registry_version() : 0;
    if ($ver !== $current) { $cache = []; $ver = $current; }
    return $cache[$pack] ??= array_values(array_filter(lab57_levels(), static fn(array $l): bool => $l['pack'] === $pack));
}

/** Pískoviště – volný terminál bez úkolu. */
function lab57_sandbox_level(): array
{
    return ['id' => 'sandbox', 'pack' => 'free', 'no' => 0, 'v' => 1, 'title' => 'Volný terminál', 'type' => 'free', 'story' => 'Cvičný počítač jen pro tebe. Zkoušej cokoli – nic se doopravdy nerozbije a příkazem reset vrátíš vše do původního stavu.', 'task' => 'Žádný úkol, jen zkoušení. Nápovědu vypíše help, příručku man <příkaz>.', 'difficulty' => 1, 'points' => 0, 'minutes' => 0, 'commands' => ['ls', 'cd', 'cat', 'grep', 'man'], 'hints' => [], 'world' => ['sandbox' => true], 'topology' => true, 'learn' => '', 'build' => null];
}

// ---------------------------------------------------------------------------
// Pomocníci pro tvorbu světů (používají i pojmenované generátory v linux_v58_levels_generators.php)
// ---------------------------------------------------------------------------

function lab57_home_file(Lab57World $w, string $rel, string $content, int $mode = 0644): void
{
    $w->mkfile('/home/student/' . $rel, $content, $mode, 'student', 'student', $w->now - 86400);
}

function lab57_decoy_code(Lab57Rng $r, string $real): string
{
    do {
        $code = 'EDU-' . strtoupper($r->token(4, 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789')) . '-' . strtoupper($r->token(4, 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'));
    } while ($code === $real);
    return $code;
}

function lab57_filler(Lab57Rng $r, int $lines): array
{
    $words = ['server', 'síť', 'paket', 'router', 'kabel', 'switch', 'linux', 'terminál', 'soubor', 'složka', 'disk', 'port', 'brána', 'protokol', 'skript', 'proces', 'služba', 'log', 'uživatel', 'práva'];
    $out = [];
    for ($i = 0; $i < $lines; $i++) {
        $n = $r->int(3, 7);
        $row = [];
        for ($k = 0; $k < $n; $k++) $row[] = (string)$r->pick($words);
        $out[] = ucfirst(implode(' ', $row)) . '.';
    }
    return $out;
}

function lab57_binary_noise(Lab57Rng $r, int $len): string
{
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $b = $r->next() & 0xFF;
        $out .= chr($b < 32 || $b > 126 ? $b : ($b % 3 === 0 ? 0 : $b));
    }
    return $out;
}

// ---------------------------------------------------------------------------
// START – základy terminálu
// ---------------------------------------------------------------------------

function lab57_levels_start(): array
{
    return [
        [
            'id' => 'start-1', 'pack' => 'start', 'type' => 'answer', 'title' => 'Kde to jsem?', 'minutes' => 2, 'v' => 2,
            'story' => 'Právě ses přihlásil(a) ke školnímu linuxovému počítači. Terminál vždy „stojí“ v nějaké složce.',
            'task' => 'Zjisti celou cestu ke složce, ve které právě jsi, a odpověz: answer <cesta>',
            'commands' => ['pwd', 'answer'],
            'hints' => ['Anglicky se to řekne „print working directory“ – zkratka má tři písmena.', 'Napiš pwd a výsledek pošli jako odpověď.', 'answer /home/student'],
            'answer' => '{HOME}',
            'answer_format' => 'cesta začínající lomítkem, např. /neco/neco',
            'solution' => ['pwd', 'answer {HOME}'],
            'learn' => 'pwd vypíše absolutní cestu k aktuální složce. Tvoje domovská složka je /home/student (zkratka ~).',
        ],
        [
            'id' => 'start-2', 'pack' => 'start', 'type' => 'code', 'title' => 'Co je tu schované?', 'minutes' => 3, 'v' => 2,
            'story' => 'Ve tvé domovské složce je vzkaz, který běžný výpis neukáže. Soubory začínající tečkou jsou v Linuxu skryté.',
            'task' => 'Najdi skrytý soubor, přečti ho a kód z něj odevzdej: submit EDU-…',
            'commands' => ['ls', 'cat', 'submit'],
            'hints' => ['Samotné ls skryté soubory nevypíše. Která volba přidá i soubory s tečkou?', 'ls -a ukáže všechno. Pak soubor otevři příkazem cat.', 'cat .tajny_vzkaz'],
            'generate' => [
                ['file', ['path' => '~/poznamky.txt', 'content' => "Nákup: chleba, mléko\n"]],
                ['file', ['path' => '~/.tajny_vzkaz', 'content' => "Dobrá práce! Skryté soubory vidíš přes ls -a.\nKód: {CODE}\n"]],
            ],
            'solution' => ['ls -a', 'cat .tajny_vzkaz', 'submit {CODE}'],
            'learn' => 'ls -a vypíše i skryté soubory (začínají tečkou). Skryté bývají hlavně konfigurace, např. ~/.bashrc.',
        ],
        [
            'id' => 'start-3', 'pack' => 'start', 'type' => 'code', 'title' => 'Výlet po složkách', 'minutes' => 4, 'v' => 2,
            'story' => 'Kód je uložený hluboko ve složkách vylet → hory → chata. Po cestě jsou i slepé odbočky.',
            'task' => 'Dostaň se do složky, kde je soubor mapa.txt, přečti ho a odevzdej kód.',
            'commands' => ['cd', 'ls', 'cat', 'pwd'],
            'hints' => ['Do složky vstoupíš příkazem cd jméno. Zpět o úroveň výš: cd ..', 'Můžeš jít i najednou: cd vylet/hory/chata', 'cat vylet/hory/chata/mapa.txt'],
            'generate' => [
                ['core_file_set', ['files' => [
                    ['path' => '~/vylet/more/plaz/mapa.txt', 'content' => "Tady nic není. Zkus hory.\n"],
                    ['path' => '~/vylet/hory/les/mapa.txt', 'content' => "Tady taky ne. Hledej chatu.\n"],
                    ['path' => '~/vylet/hory/chata/mapa.txt', 'content' => "Našel(a) jsi chatu!\nKód: {CODE}\n"],
                ]]],
            ],
            'solution' => ['cd vylet/hory/chata', 'cat mapa.txt', 'submit {CODE}'],
            'learn' => 'cd mění složku, cd .. jde o úroveň výš, samotné cd vrací domů. Cesty můžeš psát najednou: cd a/b/c.',
        ],
        [
            'id' => 'start-4', 'pack' => 'start', 'type' => 'check', 'title' => 'Založ projekt', 'minutes' => 4, 'v' => 2,
            'story' => 'Začínáš nový projekt a potřebuješ pro něj složku a soubor s popisem.',
            'task' => 'Vytvoř složku projekt a v ní soubor README.txt, který obsahuje slovo ahoj.',
            'commands' => ['mkdir', 'echo', 'cat', 'ls'],
            'hints' => ['Složku vytvoří mkdir, soubor s textem třeba echo text > soubor.', 'echo ahoj > projekt/README.txt zapíše text do souboru.', 'mkdir projekt && echo ahoj > projekt/README.txt'],
            'checks' => [
                ['core_dir_exists', ['path' => '~/projekt', 'label' => 'Existuje složka ~/projekt']],
                ['core_file_exists', ['path' => '~/projekt/README.txt', 'label' => 'V ní je soubor README.txt']],
                ['file_contains', ['path' => '~/projekt/README.txt', 'contains' => 'ahoj', 'ci' => true, 'label' => 'Soubor obsahuje slovo ahoj']],
            ],
            'solution' => ['mkdir projekt', 'echo ahoj > projekt/README.txt', 'cat projekt/README.txt'],
            'learn' => 'mkdir vytváří složky, > přesměruje výstup do souboru (přepíše ho), >> přidá na konec.',
        ],
        [
            'id' => 'start-5', 'pack' => 'start', 'type' => 'check', 'title' => 'Kopírování a přejmenování', 'minutes' => 5, 'v' => 2,
            'story' => 'Ve složce fotky jsou snímky z výletu. Chceš je zálohovat a jeden pojmenovat srozumitelněji.',
            'task' => 'Vytvoř složku zaloha a zkopíruj do ní všechny .jpg soubory z fotky. Pak přejmenuj fotky/IMG_0001.jpg na fotky/vylet.jpg.',
            'commands' => ['mkdir', 'cp', 'mv', 'ls'],
            'hints' => ['Hvězdička * zastoupí libovolný text: fotky/*.jpg jsou všechny .jpg soubory.', 'cp fotky/*.jpg zaloha/ kopíruje, mv staré nové přejmenovává.', 'mkdir zaloha; cp fotky/*.jpg zaloha/; mv fotky/IMG_0001.jpg fotky/vylet.jpg'],
            'generate' => [
                ['core_series', ['dir' => '~/fotky', 'count' => 3, 'name' => 'IMG_000{N}.jpg', 'content' => "\xff\xd8\xff\xe0JFIF", 'binary_len' => 40]],
                ['file', ['path' => '~/fotky/popisky.txt', 'content' => "IMG_0001 – vrchol\nIMG_0002 – jezero\nIMG_0003 – chata\n"]],
            ],
            'checks' => [
                ['core_children_count', ['dir' => '~/zaloha', 'suffix' => '.jpg', 'count' => 3, 'label' => 'Složka ~/zaloha obsahuje 3 fotky .jpg']],
                ['core_file_exists', ['path' => '~/fotky/vylet.jpg', 'label' => 'Existuje fotky/vylet.jpg']],
                ['core_not_exists', ['path' => '~/fotky/IMG_0001.jpg', 'label' => 'fotky/IMG_0001.jpg už neexistuje']],
            ],
            'solution' => ['mkdir zaloha', 'cp fotky/*.jpg zaloha/', 'mv fotky/IMG_0001.jpg fotky/vylet.jpg'],
            'learn' => 'cp kopíruje, mv přesouvá i přejmenovává. Hvězdička * funguje s každým příkazem (tomu se říká glob).',
        ],
        [
            'id' => 'start-6', 'pack' => 'start', 'type' => 'check', 'title' => 'Úklid na stole', 'minutes' => 4, 'v' => 2,
            'story' => 'Na pracovní ploše (složka stul) se nahromadily dočasné soubory .tmp a jedna prázdná složka.',
            'task' => 'Smaž ze složky stul všechny soubory .tmp (ostatní nech) a odstraň prázdnou složku stul/prazdna.',
            'commands' => ['rm', 'rmdir', 'ls'],
            'hints' => ['rm maže soubory. Vzor *.tmp vybere jen dočasné soubory.', 'Prázdnou složku smaže rmdir.', 'rm stul/*.tmp && rmdir stul/prazdna'],
            'generate' => [
                ['core_file_set', ['files' => [
                    ['path' => '~/stul/ukol.odt', 'content' => "dokument ukol.odt\n"],
                    ['path' => '~/stul/rozvrh.pdf', 'content' => "dokument rozvrh.pdf\n"],
                    ['path' => '~/stul/fotka.png', 'content' => "dokument fotka.png\n"],
                ]]],
                ['core_series', ['dir' => '~/stul', 'count' => [3, 6], 'name' => '~docasny{N}.tmp', 'content' => "tmp\n"]],
                ['core_mkdir', ['path' => '~/stul/prazdna']],
            ],
            'checks' => [
                ['core_children_count', ['dir' => '~/stul', 'suffix' => '.tmp', 'count' => 0, 'label' => 'Ve stul/ nejsou žádné .tmp soubory']],
                ['core_files_exist', ['paths' => ['~/stul/ukol.odt', '~/stul/rozvrh.pdf', '~/stul/fotka.png'], 'label' => 'Tři důležité soubory zůstaly']],
                ['core_not_exists', ['path' => '~/stul/prazdna', 'label' => 'Složka stul/prazdna je pryč']],
            ],
            'solution' => ['ls stul', 'rm stul/*.tmp', 'rmdir stul/prazdna'],
            'learn' => 'rm maže bez koše – ve skutečném systému nevratně. Proto se vyplatí před mazáním vzor vyzkoušet přes ls stul/*.tmp.',
        ],
        [
            'id' => 'start-7', 'pack' => 'start', 'type' => 'code', 'title' => 'Hledání v knize', 'minutes' => 4, 'v' => 2,
            'story' => 'Soubor kniha.txt má stovky řádků. Kód je na řádku se slovem poklad.',
            'task' => 'Najdi v souboru kniha.txt řádek se slovem poklad a odevzdej kód, který na něm je.',
            'commands' => ['grep', 'less', 'wc'],
            'hints' => ['Číst stovky řádků ručně nemusíš – na hledání textu je příkaz grep.', 'grep slovo soubor vypíše jen řádky, které slovo obsahují.', 'grep poklad kniha.txt'],
            'generate' => [
                ['core_filler_lines', ['path' => '~/kniha.txt', 'count' => 400, 'needle' => 'Pod starým dubem leží poklad a kód {CODE}.', 'needle_pos' => [120, 380]]],
            ],
            'solution' => ['grep poklad kniha.txt', 'submit {CODE}'],
            'learn' => 'grep hledá text v souborech. Užitečné volby: -i (bez ohledu na velikost písmen), -n (čísla řádků), -c (počet).',
        ],
        [
            'id' => 'start-8', 'pack' => 'start', 'type' => 'answer', 'title' => 'Roura a počítání', 'minutes' => 5, 'v' => 2,
            'story' => 'Webový server zapisuje každou návštěvu do navstevy.log. Stránky, které neexistují, končí kódem 404.',
            'task' => 'Kolik řádků v navstevy.log obsahuje 404? Odpověz číslem: answer <počet>',
            'commands' => ['grep', 'wc', 'answer'],
            'hints' => ['Nejdřív vyber řádky s 404, pak je spočítej.', 'grep 404 navstevy.log | wc -l – nebo rovnou grep -c 404 navstevy.log', 'grep -c 404 navstevy.log'],
            'generate' => [
                ['core_access_log', ['path' => '~/navstevy.log', 'lines' => [60, 90], 'start_offset' => 7200, 'count_needle' => '404', 'fact' => 'count_404']],
            ],
            'answer' => '{f:count_404}',
            'answer_format' => 'celé číslo',
            'solution' => ['grep -c 404 navstevy.log', 'answer {f:count_404}'],
            'learn' => 'Roura | posílá výstup jednoho příkazu na vstup dalšího. grep | wc -l je klasika pro počítání.',
        ],
    ];
}

// ---------------------------------------------------------------------------
// PRŮZKUMNÍK – ve stylu OverTheWire Bandit (vlastní obsah)
// ---------------------------------------------------------------------------

function lab57_levels_quest(): array
{
    return [
        [
            'id' => 'quest-1', 'pack' => 'quest', 'type' => 'code', 'title' => 'Soubor jménem pomlčka', 'minutes' => 4, 'v' => 2,
            'story' => 'Kód je v souboru, který se jmenuje jen - (pomlčka). Jenže pomlčka má pro příkazy zvláštní význam.',
            'task' => 'Přečti obsah souboru - v domovské složce a odevzdej kód.',
            'commands' => ['cat', 'ls'],
            'hints' => ['cat - čte z klávesnice, ne ze souboru. Musíš příkazu dát najevo, že jde o soubor.', 'Pomůže cesta: ./ znamená „v této složce“.', 'cat ./-'],
            'generate' => [
                ['file', ['path' => '~/-', 'content' => "Kód: {CODE}\n"]],
            ],
            'solution' => ['cat ./-', 'submit {CODE}'],
            'learn' => 'Jména začínající pomlčkou se zapisují s cestou (./-) nebo za oddělovač voleb --.',
        ],
        [
            'id' => 'quest-2', 'pack' => 'quest', 'type' => 'code', 'title' => 'Mezery ve jménu', 'minutes' => 4, 'v' => 2,
            'story' => 'Někdo uložil kód do souboru s mezerami ve jménu. Shell ale podle mezer dělí slova.',
            'task' => 'Přečti soubor „plan na vikend.txt“ a odevzdej kód.',
            'commands' => ['cat', 'ls'],
            'hints' => ['cat plan na vikend.txt hledá tři různé soubory.', 'Celé jméno dej do uvozovek, nebo mezery „escapuj“ zpětným lomítkem.', 'cat "plan na vikend.txt"'],
            'generate' => [
                ['core_file_set', ['files' => [
                    ['path' => '~/plan na vikend.txt', 'content' => "Sobota: kolo\nNeděle: kód {CODE}\n"],
                    ['path' => '~/plan', 'content' => "Tohle není ten správný soubor.\n"],
                ]]],
            ],
            'solution' => ['cat "plan na vikend.txt"', 'submit {CODE}'],
            'learn' => 'Uvozovky "…" nebo \\ před mezerou drží jméno pohromadě. Tabulátor ho doplní sám.',
        ],
        [
            'id' => 'quest-3', 'pack' => 'quest', 'type' => 'code', 'title' => 'Skrytá archivní složka', 'minutes' => 5, 'v' => 2,
            'story' => 'V archivu jsou složky, které se schovávají stejně jako skryté soubory – tečkou na začátku.',
            'task' => 'Najdi ve složce archiv skrytý soubor se zprávou a odevzdej kód.',
            'commands' => ['ls', 'cd', 'cat', 'find'],
            'hints' => ['ls -a funguje i na složky: ls -a archiv', 'Skryté složky mohou být vnořené. Pomůže ls -aR archiv nebo find archiv.', 'find archiv -type f a potom cat nalezeného souboru'],
            'generate' => [
                ['core_file_set', ['files' => [
                    ['path' => '~/archiv/2024/souhrn.txt', 'content' => "Nic zajímavého.\n"],
                    ['path' => '~/archiv/.stare/poznamka.txt', 'content' => "Skoro! Ještě hlouběji.\n"],
                    ['path' => '~/archiv/.stare/.velmi_stare/zprava.txt', 'content' => "Kód: {CODE}\n"],
                ]]],
            ],
            'solution' => ['ls -aR archiv', 'cat archiv/.stare/.velmi_stare/zprava.txt', 'submit {CODE}'],
            'learn' => 'ls -R vypisuje rekurzivně, find prochází celé stromy složek. Skrytá může být i složka.',
        ],
        [
            'id' => 'quest-4', 'pack' => 'quest', 'type' => 'code', 'title' => 'Jediný čitelný soubor', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'v' => 2,
            'story' => 'Ve složce inhere je deset souborů -soubor00 až -soubor09. Jen jeden obsahuje text, ostatní jsou binární data.',
            'task' => 'Zjisti, který soubor je textový, a odevzdej kód z něj.',
            'commands' => ['file', 'ls', 'cat'],
            'hints' => ['Hádat obsah podle jména nejde. Příkaz file pozná typ souboru.', 'file ./inhere/* (pozor na pomlčku na začátku jmen!)', 'Hledej řádek s „ASCII text“ a ten soubor přečti přes cat ./inhere/-souborNN'],
            'generate' => [
                ['core_haystack_text', ['dir' => '~/inhere', 'count' => 10, 'pad' => 2, 'name_tpl' => '-soubor{N}', 'noise_len' => 33, 'content' => "Kód: {CODE}\n"]],
            ],
            'solution' => ['file ./inhere/*', 'cat "{f:good_path}"', 'submit {CODE}'],
            'learn' => 'file zkoumá obsah (tzv. magická čísla), ne příponu. Umí rozeznat text, obrázek, program…',
        ],
        [
            'id' => 'quest-5', 'pack' => 'quest', 'type' => 'code', 'title' => 'Přesná velikost', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'v' => 2,
            'story' => 'V inhere je 20 složek a v nich spousta souborů. Hledaný soubor má přesně 1033 bajtů a není spustitelný.',
            'task' => 'Najdi soubor o velikosti 1033 bajtů, který není spustitelný, a odevzdej kód z něj.',
            'commands' => ['find', 'ls', 'cat'],
            'hints' => ['Procházet 20 složek ručně je zdlouhavé. find umí hledat podle velikosti.', 'find inhere -size 1033c najde soubory s 1033 bajty (c = bajty).', 'find inhere -type f -size 1033c ! -executable'],
            'generate' => [
                ['core_haystack_size', ['dir' => '~/inhere', 'dirs' => 20, 'dir_tpl' => 'maybehere{N}', 'names' => ['.file1', '-file2', 'spaces file3', '.file4', '-file5', 'file6'], 'target_size' => 1033]],
            ],
            'solution' => ['find inhere -type f -size 1033c ! -executable', 'cat "{f:good_path}"', 'submit {CODE}'],
            'learn' => 'find umí kombinovat podmínky: -type, -size, -name, -user… Vykřičník ! podmínku obrací.',
        ],
        [
            'id' => 'quest-6', 'pack' => 'quest', 'type' => 'code', 'title' => 'Někde na serveru', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'v' => 2,
            'story' => 'Soubor s kódem je uložený kdekoli na disku. Víš jen, že patří uživateli archivar, skupině tym3 a má 33 bajtů.',
            'task' => 'Najdi ho v celém systému a odevzdej kód.',
            'commands' => ['find', 'cat'],
            'hints' => ['Hledej od kořene: find / … s podmínkami -user, -group a -size.', 'Chyby „Permission denied“ zahodíš přesměrováním 2>/dev/null.', 'find / -user archivar -group tym3 -size 33c 2>/dev/null'],
            'world' => ['sudo' => false],
            // Bezpečnostní poznámka (parita: ne): potřebuje vlastní uživatele/skupiny a několik
            // rozházených návnad s různým vlastníkem – pojmenovaný generátor v57_quest_6
            // (linux_v58_levels_generators.php), reálně nikde jinde v katalogu nepoužitelný.
            'generate' => [
                ['v57_quest_6', ['spots' => ['/var/lib/zaznamy/2025', '/usr/share/doc/stare', '/srv/archiv/sklep', '/opt/data/kopie'], 'size' => 33]],
            ],
            'solution' => ['find / -user archivar -group tym3 -size 33c 2>/dev/null', 'cat {f:zaznam_path}', 'submit {CODE}'],
            'learn' => '2>/dev/null zahodí chybový výstup (kanál 2). Výsledky pak nejsou zahlcené hláškami o právech.',
        ],
        [
            'id' => 'quest-7', 'pack' => 'quest', 'type' => 'code', 'title' => 'Vedle slova milionty', 'difficulty' => 1, 'minutes' => 4, 'v' => 2,
            'story' => 'Soubor data.txt má stovky řádků ve tvaru slovo a řetězec. Kód je vedle slova milionty.',
            'task' => 'Najdi řádek se slovem milionty a odevzdej kód.',
            'commands' => ['grep'],
            'hints' => ['Tohle je práce pro grep.', 'grep milionty data.txt'],
            'generate' => [
                ['core_keyword_table', ['path' => '~/data.txt', 'words' => ['prvni', 'druhy', 'stovky', 'tisice', 'miliony', 'miliardy', 'setiny', 'tisiciny', 'desitky', 'dvojice'], 'rows' => 600, 'needle_word' => 'milionty']],
            ],
            'solution' => ['grep milionty data.txt', 'submit {CODE}'],
            'learn' => 'grep s volbou -w hledá celá slova, takže „miliony“ nesplete s „milionty“.',
        ],
        [
            'id' => 'quest-8', 'pack' => 'quest', 'type' => 'code', 'title' => 'Jediný unikát', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'v' => 2,
            'story' => 'V data.txt se každý řádek opakuje několikrát. Kromě jednoho – ten je tam jen jednou a je to kód.',
            'task' => 'Najdi řádek, který se vyskytuje právě jednou, a odevzdej ho.',
            'commands' => ['sort', 'uniq'],
            'hints' => ['uniq porovnává jen sousední řádky – nejdřív je musíš seřadit.', 'uniq -u vypíše jen řádky bez opakování.', 'sort data.txt | uniq -u'],
            'generate' => [
                ['core_unique_row', ['path' => '~/data.txt', 'groups' => 40, 'repeat' => [2, 6]]],
            ],
            'solution' => ['sort data.txt | uniq -u', 'submit {CODE}'],
            'learn' => 'sort | uniq -c spočítá opakování, uniq -u ukáže jedinečné, uniq -d duplicitní řádky.',
        ],
        [
            'id' => 'quest-9', 'pack' => 'quest', 'type' => 'code', 'title' => 'Čitelné řetězce v datech', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'v' => 2,
            'story' => 'Soubor data.bin je binární. Uvnitř je pár čitelných řetězců a kód stojí za několika rovnítky ====.',
            'task' => 'Vytáhni z data.bin čitelné řetězce, najdi ten za ==== a odevzdej kód.',
            'commands' => ['strings', 'grep'],
            'hints' => ['cat na binární soubor ukáže nepořádek. Příkaz strings vypíše jen čitelné úseky.', 'strings data.bin | grep ===='],
            'generate' => [
                ['core_binary_markers', ['path' => '~/data.bin', 'parts' => [
                    ['noise' => 300], ['text' => '==== nic'], ['noise' => 200], ['text' => '==== {CODE}'], ['noise' => 250], ['text' => '=== skoro'], ['noise' => 120],
                ]]],
            ],
            'solution' => ['strings data.bin | grep ====', 'submit {CODE}'],
            'learn' => 'strings vytáhne text i z programů a obrázků – hodí se při zkoumání neznámých souborů.',
        ],
        [
            'id' => 'quest-10', 'pack' => 'quest', 'type' => 'code', 'title' => 'Co se změnilo?', 'difficulty' => 2, 'points' => 150, 'minutes' => 5, 'v' => 2,
            'story' => 'Máš dvě verze seznamu: seznam.old a seznam.new. Liší se jediným řádkem – ten nový je kód.',
            'task' => 'Porovnej soubory a odevzdej řádek, který přibyl v seznam.new.',
            'commands' => ['diff'],
            'hints' => ['Porovnávat řádek po řádku umí diff.', 'diff seznam.old seznam.new – řádek se znakem > je z nového souboru.'],
            'generate' => [
                ['core_diff_pair', ['path_a' => '~/seznam.old', 'path_b' => '~/seznam.new', 'rows' => 100, 'pos' => [10, 90]]],
            ],
            'solution' => ['diff seznam.old seznam.new', 'submit {CODE}'],
            'learn' => 'diff ukazuje změny: < je řádek z prvního souboru, > z druhého. Na tom stojí i Git.',
        ],
        [
            'id' => 'quest-11', 'pack' => 'quest', 'type' => 'code', 'title' => 'Služba na tajném portu', 'difficulty' => 3, 'points' => 200, 'minutes' => 8, 'v' => 2,
            'story' => 'Na tomhle počítači běží malá služba, která kód vydá, když jí pošleš text prosim-kod. Nevíš ale, na kterém portu poslouchá (je mezi 30000 a 30010).',
            'task' => 'Zjisti port služby a pošli jí text prosim-kod. Odevzdej kód, který vrátí.',
            'commands' => ['ss', 'nc', 'echo'],
            'hints' => ['Které porty na počítači poslouchají, ukáže ss -tln.', 'Text službě pošleš rourou do nc: echo text | nc localhost PORT', 'echo prosim-kod | nc localhost <port z ss>'],
            'generate' => [
                ['v57_quest_11', ['min' => 30000, 'max' => 30010]],
            ],
            // 'net' zůstává closure (dokumentovaný v58 hák, viz docs/LAB_V58_API.md §3/§7): simuluje
            // živou síťovou službu a musí se přepočítat při každé stavbě světa, ne jen jednou při
            // generování – proto ho generate/checks nenahrazují (mimo rozsah CNT-01).
            'net' => static function (Lab57World $w): void {
                $port = (int)($w->mem['q11'] ?? 30000);
                $code = $w->code();
                $w->netServices['127.0.0.1:' . $port] = static fn(string $in, Lab57World $world): string => trim($in) === 'prosim-kod' ? 'Tady je tvůj kód: ' . $code . "\n" : "Neznám tenhle požadavek. Pošli: prosim-kod\n";
            },
            'solution' => ['ss -tln', 'echo prosim-kod | nc localhost {f:service_port}', 'submit {CODE}'],
            'learn' => 'Služby poslouchají na portech. ss -tln je vypíše, nc (netcat) se k nim umí připojit a poslat data.',
        ],
    ];
}

// ---------------------------------------------------------------------------
// KÓDY A FORMÁTY – ve stylu picoCTF General Skills
// ---------------------------------------------------------------------------

function lab57_rot13(string $text): string
{
    return strtr($text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', 'NOPQRSTUVWXYZABCDEFGHIJKLMnopqrstuvwxyzabcdefghijklm');
}

function lab57_levels_kody(): array
{
    return [
        [
            'id' => 'kody-1', 'pack' => 'kody', 'type' => 'code', 'title' => 'Base64', 'minutes' => 4, 'v' => 2,
            'story' => 'Zpráva v souboru zprava.txt vypadá jako náhodná písmena. Je ale jen zakódovaná v Base64 – kódování, které převádí data na čitelné znaky.',
            'task' => 'Dekóduj zprava.txt a odevzdej kód.',
            'commands' => ['base64', 'cat'],
            'hints' => ['Base64 se pozná podle písmen, číslic a často = na konci.', 'base64 umí i opačný směr – volba -d (decode).', 'base64 -d zprava.txt'],
            'generate' => [
                ['core_wrapped_message', ['path' => '~/zprava.txt', 'content' => 'Tajná zpráva: kód je {CODE}', 'encode' => ['newline', 'base64', 'newline']]],
            ],
            'solution' => ['base64 -d zprava.txt', 'submit {CODE}'],
            'learn' => 'Base64 není šifra – kdokoli ho dekóduje. Používá se k přenosu binárních dat textem (e-maily, obrázky v HTML).',
        ],
        [
            'id' => 'kody-2', 'pack' => 'kody', 'type' => 'code', 'title' => 'Šestnáctková soustava', 'minutes' => 5, 'v' => 2,
            'story' => 'Soubor zprava.hex obsahuje text zapsaný po bajtech v šestnáctkové (hexadecimální) soustavě.',
            'task' => 'Převeď hex zpět na text a odevzdej kód.',
            'commands' => ['xxd', 'cat'],
            'hints' => ['Každé dva znaky 0–9 a a–f jsou jeden bajt, např. 41 = A.', 'xxd umí převod zpět volbou -r, pro „holý“ hex přidej -p.', 'xxd -r -p zprava.hex'],
            'generate' => [
                ['core_wrapped_message', ['path' => '~/zprava.hex', 'content' => 'Kod: {CODE}', 'encode' => ['newline', 'hex', 'newline']]],
            ],
            'solution' => ['xxd -r -p zprava.hex', 'submit {CODE}'],
            'learn' => 'Hex je čitelný zápis bajtů. xxd soubor ukáže bajty, xxd -r je složí zpět.',
        ],
        [
            'id' => 'kody-3', 'pack' => 'kody', 'type' => 'code', 'title' => 'ROT13', 'minutes' => 5, 'v' => 2,
            'story' => 'Zpráva v zprava.rot13 je „zašifrovaná“ posunem každého písmene o 13 míst v abecedě. Stejný posun ji zase rozšifruje.',
            'task' => 'Rozšifruj zprávu a odevzdej kód.',
            'commands' => ['tr', 'cat'],
            'hints' => ['Záměnu znaků dělá příkaz tr: tr ABC XYZ nahradí A→X, B→Y, C→Z.', 'Rozsahy: A-Z a posunutá abeceda N-ZA-M (a totéž pro malá písmena).', "tr 'A-Za-z' 'N-ZA-Mn-za-m' < zprava.rot13"],
            'generate' => [
                ['core_wrapped_message', ['path' => '~/zprava.rot13', 'content' => 'Gratuluji, kod je {CODE}', 'encode' => ['rot13', 'newline']]],
            ],
            'solution' => ["tr 'A-Za-z' 'N-ZA-Mn-za-m' < zprava.rot13", 'submit {CODE}'],
            'learn' => 'ROT13 je Caesarova šifra s posunem 13. Nechrání nic – ukazuje ale, jak funguje substituce znaků.',
        ],
        [
            'id' => 'kody-4', 'pack' => 'kody', 'type' => 'answer', 'title' => 'Číselné soustavy', 'minutes' => 5, 'v' => 2,
            'story' => 'Kód ke dveřím serverovny je v souboru dvere.txt, ale zapsaný ve dvojkové soustavě.',
            'task' => 'Převeď číslo na desítkové a odpověz: answer <číslo>',
            'commands' => ['cat', 'echo'],
            'hints' => ['Bash umí počítat: echo $((1+2))', 'Číslo v jiné soustavě zapíšeš jako základ#číslice, např. 2#101.', 'echo $((2#<číslice ze souboru>))'],
            'generate' => [
                ['core_number_base', ['path' => '~/dvere.txt', 'min' => 300, 'max' => 4000, 'base' => 2, 'label' => 'Kód dveří (dvojkově): ', 'fact' => 'door']],
            ],
            'answer' => '{f:door}',
            'answer_format' => 'celé číslo v desítkové soustavě',
            'solution' => ['cat dvere.txt', 'echo $((2#{f:door_digits}))', 'answer {f:door}'],
            'learn' => 'Počítač ukládá čísla dvojkově. $((2#…)), $((16#…)) a $((8#…)) převádí ze soustav do desítkové.',
        ],
        [
            'id' => 'kody-5', 'pack' => 'kody', 'type' => 'code', 'title' => 'Kontrolní součet', 'difficulty' => 2, 'points' => 150, 'minutes' => 6, 'v' => 2,
            'story' => 'Dorazilo pět dodávek (dodavka-1.txt až dodavka-5.txt). Pravá je jen ta, jejíž SHA-256 součet je v souboru ocekavany.sha256.',
            'task' => 'Najdi pravou dodávku a odevzdej kód z ní.',
            'commands' => ['sha256sum', 'cat', 'grep'],
            'hints' => ['Kontrolní součet spočítá sha256sum soubor.', 'Spočítej ho pro všechny: sha256sum dodavka-*.txt a porovnej s ocekavany.sha256.', 'sha256sum dodavka-*.txt | grep $(cat ocekavany.sha256)'],
            'generate' => [
                ['core_checksum_pack', ['count' => 5, 'path_tpl' => '~/dodavka-{N}.txt', 'hash_path' => '~/ocekavany.sha256', 'content_tpl' => "Dodávka č. {N}\nKód: {VALUE}\n", 'fact' => 'dodavka']],
            ],
            'solution' => ['sha256sum dodavka-*.txt', 'cat ocekavany.sha256', 'cat {f:dodavka_path}', 'submit {CODE}'],
            'learn' => 'Kontrolní součet (hash) ověří, že soubor nikdo nezměnil – stačí jediný jiný bajt a součet je úplně jiný.',
        ],
        [
            'id' => 'kody-6', 'pack' => 'kody', 'type' => 'code', 'title' => 'Dvojitý obal', 'difficulty' => 3, 'points' => 200, 'minutes' => 8, 'v' => 2,
            'story' => 'Zpráva v obal.txt je zabalená dvakrát: nejdřív ROT13, potom Base64.',
            'task' => 'Rozbal obě vrstvy (v opačném pořadí) a odevzdej kód.',
            'commands' => ['base64', 'tr'],
            'hints' => ['Vrstvy se sundávají od poslední: nejdřív Base64, pak ROT13.', 'Spoj příkazy rourou: base64 -d obal.txt | tr …', "base64 -d obal.txt | tr 'A-Za-z' 'N-ZA-Mn-za-m'"],
            'generate' => [
                ['core_wrapped_message', ['path' => '~/obal.txt', 'content' => 'Vyborne! Kod: {CODE}', 'encode' => ['rot13', 'newline', 'base64', 'newline']]],
            ],
            'solution' => ["base64 -d obal.txt | tr 'A-Za-z' 'N-ZA-Mn-za-m'", 'submit {CODE}'],
            'learn' => 'Roura umí řetězit libovolně mnoho převodů. Tak se v praxi zpracovávají data krok za krokem.',
        ],
    ];
}

// ---------------------------------------------------------------------------
// v58: jádro deklarativních úrovní – registry generátorů/kontrol, šablony, příprava úrovně.
// Katalog konkrétních generátorů (core.*) je v linux_v58_levels_generators.php; tady zůstává
// jen obecná „instalatérská“ vrstva, kterou používá i jádro (linux_v58_ext.php) a další agenti.
// ---------------------------------------------------------------------------

/** $fn(Lab57World $w, Lab57Rng $rng, array $params): void – deterministicky jen z $rng. */
function lab58_register_generator(string $name, callable $fn): bool
{
    if (preg_match('/^[a-z][a-z0-9_]{1,31}$/', $name) !== 1) return lab58_reg_error("generátor '$name': neplatné jméno");
    $r = &lab58_registry();
    if (isset($r['generators'][$name])) return lab58_reg_error("generátor '$name' už existuje");
    $r['generators'][$name] = $fn;
    lab58_bump();
    return true;
}

/** $fn(Lab57World $w, array $params): bool */
function lab58_register_check(string $name, callable $fn): bool
{
    if (preg_match('/^[a-z][a-z0-9_]{1,31}$/', $name) !== 1) return lab58_reg_error("kontrola '$name': neplatné jméno");
    $r = &lab58_registry();
    if (isset($r['checks'][$name])) return lab58_reg_error("kontrola '$name' už existuje");
    $r['checks'][$name] = $fn;
    lab58_bump();
    return true;
}

function lab58_run_generators(Lab57World $w, array $generate): void
{
    foreach (array_values($generate) as $i => $item) {
        $name = (string)($item[0] ?? '');
        $fn = lab58_registry()['generators'][$name] ?? null;
        if (!is_callable($fn)) throw new RuntimeException('Neznámý generátor úrovně: ' . $name);
        $fn($w, new Lab57Rng($w->seed . '|gen|' . $i . '|' . $name), (array)($item[1] ?? []));
    }
}

/** v57 kontrola ['label','fn'] zůstane; deklarativní [jméno, params] se převede na ni. */
function lab58_check_item(mixed $check): array|string
{
    if (is_array($check) && isset($check['fn']) && is_callable($check['fn'])) return ['label' => (string)($check['label'] ?? 'Kontrola'), 'fn' => $check['fn']];
    if (!is_array($check) || !is_string($check[0] ?? null)) return 'kontrola musí být [jméno, parametry] nebo [label, fn]';
    $name = $check[0];
    $params = (array)($check[1] ?? []);
    return ['label' => (string)($params['label'] ?? $name), 'fn' => static fn(Lab57World $w): bool => lab58_run_check($name, $w, $params), 'decl' => [$name, $params]];
}

function lab58_run_check(string $name, Lab57World $w, array $params): bool
{
    $fn = lab58_registry()['checks'][$name] ?? null;
    if (!is_callable($fn)) { lab58_reg_error("neznámá kontrola '$name'"); return false; }
    return (bool)$fn($w, $params);
}

/**
 * Šablona: {CODE} {HOME} {USER} {HOST} {N} {TOKEN} {DECOY} {f:jméno_faktu}.
 * {TOKEN} a {DECOY} potřebují $rng (deterministické ze semínka generátoru).
 */
function lab58_fill(Lab57World $w, string $tpl, ?Lab57Rng $rng = null, array $vars = []): string
{
    return (string)preg_replace_callback('/\{(CODE|HOME|USER|HOST|N|TOKEN|DECOY|NUM|WORD|NAME|f:[a-z0-9_]{1,32})\}/', static function (array $m) use ($w, $rng, $vars): string {
        $key = $m[1];
        if (str_starts_with($key, 'f:')) return (string)($w->facts[substr($key, 2)] ?? $m[0]);
        return match ($key) {
            'CODE' => $w->code(),
            'HOME' => $w->home('student'),
            'USER' => 'student',
            'HOST' => $w->hostname,
            'N' => (string)($vars['N'] ?? ''),
            'TOKEN' => $rng !== null ? $rng->token(8) : $m[0],
            'NUM' => $rng !== null ? (string)$rng->int(10, 9999) : $m[0],
            'WORD' => $rng !== null ? (string)$rng->pick(['server', 'sit', 'paket', 'router', 'kabel', 'switch', 'linux', 'terminal', 'soubor', 'slozka', 'disk', 'port', 'brana', 'protokol', 'skript', 'proces', 'sluzba', 'log', 'uzivatel', 'prava']) : $m[0],
            'NAME' => $rng !== null && function_exists('lab57_first_names') ? (string)$rng->pick(lab57_first_names()) : $m[0],
            default => $rng !== null ? lab57_decoy_code($rng, $w->code()) : $m[0],
        };
    }, $tpl);
}

function lab58_gen_path(Lab57World $w, string $path, ?Lab57Rng $rng = null, array $vars = []): string
{
    $path = lab58_fill($w, $path, $rng, $vars);
    if (str_starts_with($path, '~')) $path = $w->home('student') . substr($path, 1);
    return Lab57Vfs::normalize($path);
}

function lab58_gen_mode(mixed $mode, int $default = 0644): int
{
    if (is_int($mode)) return $mode & 07777;
    if (is_string($mode) && preg_match('/^0?[0-7]{3,4}$/', $mode) === 1) return (int)octdec($mode) & 07777;
    return $default;
}

/** Celé číslo, nebo (je-li $val [min,max]) náhodné číslo z rozsahu – sjednocený tvar parametrů napříč katalogem. */
function lab58_gen_range(Lab57Rng $r, mixed $val, int $default): int
{
    if (is_array($val) && count($val) === 2) return $r->int((int)array_values($val)[0], (int)array_values($val)[1]);
    if ($val === null || $val === '') return $default;
    return (int)$val;
}

/** v58: volitelný 'extra' parametr (např. simulovaná velikost souboru pro df/du) se předá do mkfile beze změny. */
function lab58_gen_write(Lab57World $w, string $abs, string $content, array $p): void
{
    $owner = (string)($p['owner'] ?? (str_starts_with($abs, $w->home('student') . '/') ? 'student' : 'root'));
    if (!isset($w->users[$owner])) $owner = 'root';
    $w->mkfile($abs, $content, lab58_gen_mode($p['mode'] ?? null), $owner, isset($p['group']) ? (string)$p['group'] : null, $w->now - 86400 * max(0, (int)($p['days'] ?? 1)), (array)($p['extra'] ?? []));
}

/** Ukázkové generátory a kontroly (rozšířený katalog core.* je v linux_v58_levels_generators.php). */
function lab58_register_core_decl(): void
{
    // file: soubor s obsahem ze šablony. params: path, content, mode, owner, group, days, extra, fact
    lab58_register_generator('file', static function (Lab57World $w, Lab57Rng $r, array $p): void {
        $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/soubor.txt'), $r);
        lab58_gen_write($w, $abs, lab58_fill($w, (string)($p['content'] ?? ''), $r), $p);
        if (isset($p['fact'])) $w->facts[(string)$p['fact']] = $abs;
    });
    // code_file: kód úrovně na náhodné cestě. params: dirs, names, content, mode, owner, fact (výchozí code_path)
    lab58_register_generator('code_file', static function (Lab57World $w, Lab57Rng $r, array $p): void {
        $dir = lab58_gen_path($w, (string)$r->pick((array)($p['dirs'] ?? ['~'])), $r);
        $name = lab58_fill($w, (string)$r->pick((array)($p['names'] ?? ['kod.txt'])), $r);
        $abs = Lab57Vfs::normalize($name, $dir);
        lab58_gen_write($w, $abs, lab58_fill($w, (string)($p['content'] ?? "{CODE}\n"), $r), $p);
        $w->facts[(string)($p['fact'] ?? 'code_path')] = $abs;
    });
    // decoys: sada návnad s falešnými kódy. params: dir, count (1–50), name ({N}), content ({DECOY}), fact (výchozí decoys)
    lab58_register_generator('decoys', static function (Lab57World $w, Lab57Rng $r, array $p): void {
        $dir = lab58_gen_path($w, (string)($p['dir'] ?? '~/navnady'), $r);
        $count = max(1, min(50, (int)($p['count'] ?? 5)));
        $paths = [];
        for ($n = 1; $n <= $count; $n++) {
            $abs = Lab57Vfs::normalize(lab58_fill($w, (string)($p['name'] ?? 'zprava-{N}.txt'), $r, ['N' => (string)$n]), $dir);
            lab58_gen_write($w, $abs, lab58_fill($w, (string)($p['content'] ?? "Tady kód není: {DECOY}\n"), $r, ['N' => (string)$n]), $p);
            $paths[] = $abs;
        }
        $w->facts[(string)($p['fact'] ?? 'decoys')] = implode("\n", $paths);
    });
    // file_contains: soubor existuje a obsahuje (contains) / se rovná (equals, bez okrajových mezer). params: path, contains|equals, ci (bool, porovnání bez ohledu na velikost písmen), label
    lab58_register_check('file_contains', static function (Lab57World $w, array $p): bool {
        $node = $w->fs->get(lab58_gen_path($w, (string)($p['path'] ?? '')));
        if ($node === null || ($node['t'] ?? '') !== 'f') return false;
        $content = (string)($node['c'] ?? '');
        $ci = !empty($p['ci']);
        $norm = static fn(string $s): string => $ci ? mb_strtolower($s) : $s;
        if (isset($p['equals'])) return $norm(trim($content)) === $norm(trim(lab58_fill($w, (string)$p['equals'])));
        return !isset($p['contains']) || str_contains($norm($content), $norm(lab58_fill($w, (string)$p['contains'])));
    });
    // service_running: služba je aktivní a není ve stavu failed. params: service, label
    lab58_register_check('service_running', static function (Lab57World $w, array $p): bool {
        $svc = $w->services[(string)($p['service'] ?? '')] ?? null;
        return is_array($svc) && !empty($svc['active']) && empty($svc['failed']);
    });
}

/** Chyby deklarativní úrovně (pro editor učitele a audit). @return list<string> */
function lab58_validate_level(array $level): array
{
    $prepared = lab58_level_prepare($level, (string)($level['pack'] ?? 'x-validate'), 1);
    $errors = is_string($prepared) ? [$prepared] : [];
    foreach ((array)($level['generate'] ?? []) as $item) {
        if (!isset(lab58_registry()['generators'][(string)($item[0] ?? '')])) $errors[] = 'neznámý generátor: ' . (string)($item[0] ?? '?');
    }
    foreach ((array)($level['checks'] ?? []) as $item) {
        if (is_array($item) && is_string($item[0] ?? null) && !isset(lab58_registry()['checks'][$item[0]])) $errors[] = 'neznámá kontrola: ' . $item[0];
    }
    return $errors;
}
