<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – kvízová banka (kategorie, výběr otázek, kontrola odpovědí).
 *
 * Zdroje otázek (sloučené podle id, novější zdroj smí přepsat starší – umožňuje učiteli otázku opravit):
 *   1) vestavěná záložní banka (nižší v tomto souboru) – funguje i bez TG-BANK,
 *   2) teamgames_v58_bank_net.php / teamgames_v58_bank_gfx.php (TG-BANK, `return [...]`), pokud existují,
 *   3) storage/teamgames_v58_bank.json.php – otázky učitele (formulář v záložce Hry), kontrola cnt58_check_text().
 * Schéma položky: id, line(networks|graphics|both), classes(?list), category, difficulty(1-3),
 * type(single|multi|bool|numeric|order|text|command), prompt, options, answer, tolerance, accept, explain,
 * time_s, source, status(published|draft). Otázka se klientovi posílá vždy bez `answer`/`explain`
 * (tg58_quiz_public) – kontrolu dělá jen server (tg58_quiz_check), řešení se odkrývá až tg58_quiz_reveal().
 */

require_once __DIR__ . '/teamgames_v58_core.php';

const TG58_QUIZ_TYPES = ['single', 'multi', 'bool', 'numeric', 'order', 'text', 'command'];

function tg58_quiz_norm_text(string $s): string
{
    return (string)preg_replace('/\s+/u', ' ', mb_strtolower(trim($s)));
}

/** Deterministické zamíchání podle textového semínka (bez závislosti na Lab57Rng) – férové, ne kryptografické. */
function tg58_seeded_shuffle(array $items, string $seed): array
{
    $items = array_values($items);
    $keyed = [];
    foreach ($items as $i => $item) $keyed[] = [hash('sha256', $seed . '|' . $i), $item];
    usort($keyed, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
    return array_column($keyed, 1);
}

// ---------------------------------------------------------------------------
// Validace a normalizace položky
// ---------------------------------------------------------------------------

function tg58_quiz_normalize(array $item): ?array
{
    $id = (string)($item['id'] ?? '');
    if (preg_match('/^[a-z0-9][a-z0-9_.-]{1,63}$/', $id) !== 1) return null;
    $line = in_array($item['line'] ?? '', ['networks', 'graphics', 'both'], true) ? $item['line'] : 'both';
    $type = in_array($item['type'] ?? '', TG58_QUIZ_TYPES, true) ? $item['type'] : null;
    if ($type === null) return null;
    $prompt = trim((string)($item['prompt'] ?? ''));
    if ($prompt === '' || mb_strlen($prompt) > 400) return null;
    $classes = $item['classes'] ?? null;
    if ($classes !== null) { $classes = array_values(array_filter((array)$classes, 'is_string')); if ($classes === []) $classes = null; }
    $options = array_values(array_filter((array)($item['options'] ?? []), static fn($o): bool => is_string($o) && $o !== ''));
    $answer = $item['answer'] ?? null;
    if (in_array($type, ['single'], true) && (!is_string($answer) || $options === [] || !in_array($answer, $options, true))) return null;
    if ($type === 'multi' && (!is_array($answer) || $answer === [] || $options === [])) return null;
    if ($type === 'bool' && !is_bool($answer)) return null;
    if ($type === 'numeric' && !is_numeric($answer)) return null;
    if ($type === 'order' && (!is_array($answer) || count($answer) < 2)) return null;
    if (in_array($type, ['text', 'command'], true) && (!is_string($answer) || trim($answer) === '')) return null;
    return [
        'id' => $id, 'line' => $line, 'classes' => $classes, 'category' => (string)($item['category'] ?? 'obecné'),
        'difficulty' => max(1, min(3, (int)($item['difficulty'] ?? 1))), 'type' => $type, 'prompt' => mb_substr($prompt, 0, 400),
        'options' => array_slice($options, 0, 8), 'answer' => $answer, 'tolerance' => is_numeric($item['tolerance'] ?? null) ? (float)$item['tolerance'] : 0.0,
        'accept' => array_values(array_filter((array)($item['accept'] ?? []), 'is_string')), 'explain' => mb_substr((string)($item['explain'] ?? ''), 0, 400),
        'time_s' => max(10, min(120, (int)($item['time_s'] ?? 30))), 'source' => (string)($item['source'] ?? 'builtin'), 'status' => (string)($item['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
    ];
}

// ---------------------------------------------------------------------------
// Zdroje banky
// ---------------------------------------------------------------------------

/** Malá vestavěná záložní banka – funguje i bez TG-BANK. Zahrnuje i „rozbitá stránka“ (webová přístupnost). */
function tg58_quiz_builtin_bank(): array
{
    return [
        ['id' => 'b.net.cmd.01', 'line' => 'networks', 'category' => 'příkazy Linuxu', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Který příkaz vypíše obsah aktuální složky?', 'options' => ['ls', 'cd', 'pwd', 'rm'], 'answer' => 'ls', 'explain' => 'ls vypíše obsah složky; cd mění složku, pwd vypíše cestu.'],
        ['id' => 'b.net.cmd.02', 'line' => 'networks', 'category' => 'příkazy Linuxu', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Jak zjistíš, ve které složce právě jsi?', 'options' => ['pwd', 'ls', 'whoami', 'top'], 'answer' => 'pwd', 'explain' => 'pwd = print working directory.'],
        ['id' => 'b.net.perm.01', 'line' => 'networks', 'category' => 'soubory a práva', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Který příkaz změní práva k souboru?', 'options' => ['chmod', 'chown', 'chgrp', 'stat'], 'answer' => 'chmod', 'explain' => 'chmod mění práva; chown vlastníka; chgrp skupinu.'],
        ['id' => 'b.net.perm.02', 'line' => 'networks', 'category' => 'soubory a práva', 'difficulty' => 2, 'type' => 'bool', 'prompt' => 'Práva 644 dovolují komukoli soubor spustit jako program.', 'answer' => false, 'explain' => '644 = čtení/zápis vlastníkovi, čtení ostatním – bez práva spouštět (to by bylo 7 na patřičné pozici).'],
        ['id' => 'b.net.ip.01', 'line' => 'networks', 'category' => 'IP a maska', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Kolik bitů má IPv4 adresa?', 'options' => ['32', '64', '128', '16'], 'answer' => '32', 'explain' => 'IPv4 = 4 × 8 bitů = 32 bitů.'],
        ['id' => 'b.net.ip.02', 'line' => 'networks', 'category' => 'IP a maska', 'difficulty' => 3, 'type' => 'numeric', 'prompt' => 'Kolik použitelných adres pro zařízení má síť /24 (bez adresy sítě a broadcastu)?', 'answer' => 254, 'explain' => '/24 = 256 adres − síťová − broadcast = 254.'],
        ['id' => 'b.net.dns.01', 'line' => 'networks', 'category' => 'DNS', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Co překládá DNS?', 'options' => ['doménová jména na IP adresy', 'hesla na účty', 'soubory na složky', 'MAC adresy na porty'], 'answer' => 'doménová jména na IP adresy', 'explain' => 'DNS = Domain Name System.'],
        ['id' => 'b.net.svc.01', 'line' => 'networks', 'category' => 'služby a porty', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Na kterém portu obvykle běží nešifrovaný web (HTTP)?', 'options' => ['80', '443', '22', '25'], 'answer' => '80', 'explain' => '443 je HTTPS, 22 SSH, 25 mail (SMTP).'],
        ['id' => 'b.net.svc.02', 'line' => 'networks', 'category' => 'služby a porty', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Který port patří šifrovanému webu (HTTPS)?', 'options' => ['443', '80', '21', '53'], 'answer' => '443', 'explain' => 'HTTPS = port 443.'],
        ['id' => 'b.net.proc.01', 'line' => 'networks', 'category' => 'procesy', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Který příkaz ukáže běžící procesy?', 'options' => ['ps', 'ls', 'cat', 'grep'], 'answer' => 'ps', 'explain' => 'ps vypíše procesy, top je jejich živý přehled.'],
        ['id' => 'b.net.sec.01', 'line' => 'networks', 'category' => 'bezpečnost vlastního systému', 'difficulty' => 1, 'type' => 'bool', 'prompt' => 'Heslo „12345678“ je bezpečné, protože má 8 znaků.', 'answer' => false, 'explain' => 'Délka nestačí – heslo musí být i nepředvídatelné.'],
        ['id' => 'b.net.hw.01', 'line' => 'networks', 'category' => 'hardware a OS', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Co dělá jádro (kernel) operačního systému?', 'options' => ['řídí přístup programů k hardwaru', 'kreslí okna aplikací', 'ukládá hesla do prohlížeče', 'překládá web do češtiny'], 'answer' => 'řídí přístup programů k hardwaru', 'explain' => 'Kernel je jádro OS – zprostředkovává přístup k CPU, paměti, zařízením.'],
        ['id' => 'b.gfx.color.01', 'line' => 'graphics', 'category' => 'barvy a kontrast', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Jaký je minimální poměr kontrastu textu k pozadí podle WCAG AA pro běžný text?', 'options' => ['4.5:1', '1:1', '2:1', '10:1'], 'answer' => '4.5:1', 'explain' => 'WCAG 2.2 AA: běžný text 4,5:1, velký text 3:1.'],
        ['id' => 'b.gfx.color.02', 'line' => 'graphics', 'category' => 'barvy a kontrast', 'difficulty' => 2, 'type' => 'bool', 'prompt' => 'Světle šedý text na bílém pozadí má vždy dostatečný kontrast.', 'answer' => false, 'explain' => 'Světle šedá na bílé bývá pod hranicí 4,5:1 – je potřeba to změřit.'],
        ['id' => 'b.gfx.type.01', 'line' => 'graphics', 'category' => 'typografie', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Jak se nazývá písmo s patkami, jako je Times New Roman?', 'options' => ['patkové (serif)', 'bezpatkové (sans-serif)', 'monospace', 'kurzíva'], 'answer' => 'patkové (serif)', 'explain' => 'Serif = patkové, sans-serif = bezpatkové.'],
        ['id' => 'b.gfx.raster.01', 'line' => 'graphics', 'category' => 'rastr/vektor/formáty', 'difficulty' => 2, 'type' => 'bool', 'prompt' => 'Vektorová grafika (např. SVG) se při zvětšení nerozmazává.', 'answer' => true, 'explain' => 'Vektor je popsaný křivkami – škáluje se ostře na jakoukoli velikost.'],
        ['id' => 'b.gfx.raster.02', 'line' => 'graphics', 'category' => 'rastr/vektor/formáty', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Který formát je nejlepší pro fotografii s plynulými přechody barev?', 'options' => ['JPEG', 'SVG', 'ICO', 'GIF (256 barev)'], 'answer' => 'JPEG', 'explain' => 'JPEG snese ztrátovou kompresi fotografií; SVG je vektor, GIF má málo barev.'],
        ['id' => 'b.gfx.html.01', 'line' => 'graphics', 'category' => 'HTML', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Který atribut obrázku popisuje jeho obsah lidem, kteří obrázek nevidí?', 'options' => ['alt', 'src', 'title', 'width'], 'answer' => 'alt', 'explain' => 'alt = alternativní text pro čtečky obrazovky a při nenačtení obrázku.'],
        ['id' => 'b.gfx.css.01', 'line' => 'graphics', 'category' => 'CSS', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Kterou vlastností CSS rozmístíš prvky v řádku vedle sebe pružně (flexbox)?', 'options' => ['display: flex', 'display: none', 'position: fixed', 'color: red'], 'answer' => 'display: flex', 'explain' => 'display:flex zapíná flexbox layout.'],
        ['id' => 'b.gfx.a11y.01', 'line' => 'graphics', 'category' => 'přístupnost webu', 'difficulty' => 2, 'type' => 'bool', 'prompt' => 'Odkaz s textem „klikni sem“ je pro čtečku obrazovky stejně srozumitelný jako popisný text odkazu.', 'answer' => false, 'explain' => 'Popisné odkazy (např. „stáhnout ceník“) pomáhají orientaci v seznamu odkazů čtečky.'],
        ['id' => 'b.gfx.ux.01', 'line' => 'graphics', 'category' => 'kompozice a UX', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Jak se nazývá pravidlo kompozice dělící obraz na třetiny?', 'options' => ['pravidlo třetin', 'zlatý řez naruby', 'pravidlo poloviny', 'diagonální test'], 'answer' => 'pravidlo třetin', 'explain' => 'Pravidlo třetin umísťuje důležité prvky na průsečíky mřížky 3×3.'],
        ['id' => 'b.gfx.license.01', 'line' => 'graphics', 'category' => 'licence (CC)', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Co znamená licence CC BY?', 'options' => ['smíš dílo použít, pokud uvedeš autora', 'dílo je zakázáno šířit', 'dílo smí použít jen autor', 'licence platí jen 1 rok'], 'answer' => 'smíš dílo použít, pokud uvedeš autora', 'explain' => 'BY = povinnost uvést autorství (Attribution).'],
        ['id' => 'b.wf.alt.01', 'line' => 'graphics', 'category' => 'rozbitá stránka', 'difficulty' => 1, 'type' => 'single', 'prompt' => 'Rozbitá stránka: obrázek loga nemá vyplněný atribut alt. Jak to nejlépe opravit?', 'options' => ['doplnit alt="Logo školy EDUCANET"', 'smazat obrázek úplně', 'doplnit alt=""', 'zvětšit obrázek'], 'answer' => 'doplnit alt="Logo školy EDUCANET"', 'explain' => 'Smysluplný alt popisuje obsah obrázku; prázdný alt="" patří jen čistě dekorativním obrázkům.'],
        ['id' => 'b.wf.contrast.01', 'line' => 'graphics', 'category' => 'rozbitá stránka', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Rozbitá stránka: nadpis je světle žlutý na bílém pozadí a špatně se čte. Která oprava dá dostatečný kontrast?', 'options' => ['tmavě šedý text #333 na bílém pozadí', 'ještě světlejší žlutá', 'bílý text na bílém pozadí', 'blikající text'], 'answer' => 'tmavě šedý text #333 na bílém pozadí', 'explain' => 'Tmavý text na světlém pozadí splní kontrast 4,5:1, světlá na bílé ne.'],
        ['id' => 'b.wf.layout.01', 'line' => 'graphics', 'category' => 'rozbitá stránka', 'difficulty' => 2, 'type' => 'single', 'prompt' => 'Rozbitá stránka: karty produktů se na mobilu (390 px) přetahují přes sebe. Co nejspíš pomůže?', 'options' => ['přepnout layout karet na sloupec (flex-direction: column) pod danou šířkou', 'zmenšit text na 6 px', 'schovat všechny obrázky', 'zvětšit okno prohlížeče'], 'answer' => 'přepnout layout karet na sloupec (flex-direction: column) pod danou šířkou', 'explain' => 'Responzivní layout na úzké obrazovce skládá prvky pod sebe.'],
        ['id' => 'b.wf.alt.02', 'line' => 'graphics', 'category' => 'rozbitá stránka', 'difficulty' => 1, 'type' => 'bool', 'prompt' => 'Rozbitá stránka: čistě dekorativní ozdobná čárka může mít alt="".', 'answer' => true, 'explain' => 'Pro čistě dekorativní obrázky je prázdný alt správně – čtečka je přeskočí.'],
    ];
}

/** TG-BANK: teamgames_v58_bank_net.php / teamgames_v58_bank_gfx.php – každý `return [...]`. Chybí-li, běžíme dál bez nich. */
function tg58_quiz_external_bank(): array
{
    $items = [];
    foreach (['teamgames_v58_bank_net.php', 'teamgames_v58_bank_gfx.php'] as $file) {
        $path = __DIR__ . '/' . $file;
        if (!is_file($path)) continue;
        try {
            $data = include $path;
        } catch (Throwable $e) {
            error_log('EDUCANET v58 tg banka ' . $file . ': ' . $e->getMessage());
            continue;
        }
        if (is_array($data)) foreach ($data as $row) if (is_array($row)) $items[] = $row;
    }
    return $items;
}

function tg58_quiz_teacher_bank(): array
{
    $rows = (array)(tg58_read(tg58_bank_path())['items'] ?? []);
    return array_values(array_filter($rows, 'is_array'));
}

/**
 * Sloučená, normalizovaná, jen publikované položky. Novější zdroj (učitel) smí přepsat starší se stejným id.
 * Mezivýsledek se cachuje na požadavek – po zápisu do banky učitele zavolej tg58_quiz_bank_forget().
 */
function tg58_quiz_bank(): array
{
    if (is_array($GLOBALS['tg58_quiz_bank_cache'] ?? null)) return $GLOBALS['tg58_quiz_bank_cache'];
    $byId = [];
    foreach (array_merge(tg58_quiz_builtin_bank(), tg58_quiz_external_bank(), tg58_quiz_teacher_bank()) as $raw) {
        $item = tg58_quiz_normalize($raw);
        if ($item === null || $item['status'] !== 'published') continue;
        $byId[$item['id']] = $item;
    }
    return $GLOBALS['tg58_quiz_bank_cache'] = array_values($byId);
}

/** Zahodí mezipaměť banky (po uložení/smazání otázky učitele, nebo v CLI auditu mezi kroky). */
function tg58_quiz_bank_forget(): void
{
    unset($GLOBALS['tg58_quiz_bank_cache']);
}

// ---------------------------------------------------------------------------
// Výběr otázek pro hru
// ---------------------------------------------------------------------------

/** @return list<array> otázky vyhovující lince/třídě/kategorii, deterministicky zamíchané podle $seed */
function tg58_quiz_select(string $line, ?string $classId, int $count, string $seed, array $opts = []): array
{
    $pool = array_values(array_filter(tg58_quiz_bank(), static function (array $it) use ($line, $classId, $opts): bool {
        if ($it['line'] !== 'both' && $it['line'] !== $line) return false;
        if ($it['classes'] !== null && $classId !== null && !in_array($classId, $it['classes'], true)) return false;
        if (isset($opts['category']) && $it['category'] !== $opts['category']) return false;
        if (isset($opts['difficulty']) && $it['difficulty'] !== $opts['difficulty']) return false;
        if (isset($opts['exclude']) && in_array($it['id'], (array)$opts['exclude'], true)) return false;
        return true;
    }));
    return array_slice(tg58_seeded_shuffle($pool, $seed), 0, max(0, $count));
}

/** @return list<string> kategorie dostupné pro linii, seřazené podle počtu otázek (nejvíc první) */
function tg58_quiz_categories(string $line): array
{
    $counts = [];
    foreach (tg58_quiz_bank() as $it) {
        if ($it['line'] !== 'both' && $it['line'] !== $line) continue;
        $counts[$it['category']] = ($counts[$it['category']] ?? 0) + 1;
    }
    arsort($counts);
    return array_keys($counts);
}

/** Tabule 5×5 (Riskuj!): kategorie jako sloupce, hodnoty 100–500 podle obtížnosti v rámci kategorie. */
function tg58_quiz_board(string $line, ?string $classId, string $seed, int $size = 5): array
{
    $cats = tg58_quiz_categories($line);
    if (count($cats) < $size) $cats = array_merge($cats, tg58_quiz_categories($line === 'networks' ? 'graphics' : 'networks'));
    $cats = array_values(array_unique($cats));
    $cats = array_slice(tg58_seeded_shuffle($cats, $seed . '|cat'), 0, $size);
    $values = [100, 200, 300, 400, 500];
    $board = [];
    foreach ($cats as $ci => $cat) {
        $items = array_values(array_filter(tg58_quiz_bank(), static fn(array $it): bool => $it['category'] === $cat));
        usort($items, static fn(array $a, array $b): int => $a['difficulty'] <=> $b['difficulty']);
        $items = tg58_seeded_shuffle($items, $seed . '|cell|' . $ci);
        $cells = [];
        for ($v = 0; $v < $size; $v++) {
            $item = $items[$v % max(1, count($items))] ?? null;
            $cells[] = ['value' => $values[$v] ?? (($v + 1) * 100), 'item_id' => $item['id'] ?? null];
        }
        $board[] = ['category' => $cat, 'cells' => $cells];
    }
    return $board;
}

function tg58_quiz_find(string $id): ?array
{
    foreach (tg58_quiz_bank() as $it) { if ($it['id'] === $id) return $it; }
    return null;
}

// ---------------------------------------------------------------------------
// Veřejný pohled (bez odpovědi) a kontrola odpovědi
// ---------------------------------------------------------------------------

/** Data pro klienta – NIKDY neobsahují 'answer' ani 'explain'. Možnosti zamíchané podle $seed (per žák/tým). */
function tg58_quiz_public(array $item, string $seed): array
{
    $out = ['id' => $item['id'], 'category' => $item['category'], 'difficulty' => $item['difficulty'], 'type' => $item['type'], 'prompt' => $item['prompt'], 'time_s' => $item['time_s']];
    if (in_array($item['type'], ['single', 'multi'], true)) $out['options'] = tg58_seeded_shuffle($item['options'], $seed . '|opt');
    if ($item['type'] === 'bool') $out['options'] = [tr('Ano'), tr('Ne')];
    if ($item['type'] === 'order') $out['options'] = tg58_seeded_shuffle(array_map('strval', (array)$item['answer']), $seed . '|ord');
    return $out;
}

function tg58_quiz_reveal(array $item): array
{
    return ['id' => $item['id'], 'answer' => $item['answer'], 'explain' => $item['explain']];
}

function tg58_quiz_check(array $item, mixed $given): bool
{
    switch ($item['type']) {
        case 'bool':
            if (is_bool($given)) return $given === (bool)$item['answer'];
            $norm = tg58_quiz_norm_text((string)$given);
            return $norm === (((bool)$item['answer']) ? 'ano' : 'ne');
        case 'single':
            return is_string($given) && tg58_quiz_norm_text($given) === tg58_quiz_norm_text((string)$item['answer']);
        case 'multi':
            if (!is_array($given) || $given === []) return false;
            $g = array_unique(array_map('tg58_quiz_norm_text', array_map('strval', $given)));
            $a = array_unique(array_map('tg58_quiz_norm_text', array_map('strval', (array)$item['answer'])));
            sort($g);
            sort($a);
            return $g === $a;
        case 'numeric':
            if (!is_numeric($given)) return false;
            $tol = max(0.0, (float)($item['tolerance'] ?? 0));
            return abs((float)$given - (float)$item['answer']) <= $tol + 1e-9;
        case 'order':
            if (!is_array($given)) return false;
            return array_map('tg58_quiz_norm_text', array_map('strval', $given)) === array_map('tg58_quiz_norm_text', array_map('strval', (array)$item['answer']));
        case 'text':
        case 'command':
            $g = tg58_quiz_norm_text((string)$given);
            if ($g === '') return false;
            foreach (array_merge([(string)$item['answer']], (array)($item['accept'] ?? [])) as $accepted) {
                if ($g === tg58_quiz_norm_text((string)$accepted)) return true;
            }
            return false;
        default:
            return false;
    }
}

// ---------------------------------------------------------------------------
// Banka učitele (formulář v záložce Hry): jen jednodušší typy pro bezpečné vyplnění
// ---------------------------------------------------------------------------

function tg58_bank_add_question(array $in, int $now): array
{
    $prompt = trim((string)($in['prompt'] ?? ''));
    if ($prompt === '') throw new RuntimeException('Otázka nemůže být prázdná.');
    if (mb_strlen($prompt) > 300) throw new RuntimeException('Otázka může mít nejvýš 300 znaků.');
    if (function_exists('cnt58_check_text')) {
        $check = cnt58_check_text($prompt, 'tg58_bank');
        if (is_array($check) && empty($check['ok'])) {
            $msg = (string)($check['issues'][0]['message'] ?? 'text obsahuje nevhodné slovo.');
            throw new RuntimeException('Otázku nejde uložit – ' . $msg);
        }
    }
    $type = in_array($in['type'] ?? '', ['single', 'bool', 'text'], true) ? $in['type'] : 'single';
    $line = in_array($in['line'] ?? '', TG58_LINES, true) ? $in['line'] : 'both';
    $category = trim((string)($in['category'] ?? 'vlastní otázky'));
    if ($category === '') $category = 'vlastní otázky';
    $raw = ['id' => 'teacher.' . substr(hash('sha256', $prompt . '|' . $now . '|' . random_int(0, PHP_INT_MAX)), 0, 20), 'line' => $line, 'category' => mb_substr($category, 0, 40), 'difficulty' => max(1, min(3, (int)($in['difficulty'] ?? 1))), 'type' => $type, 'prompt' => $prompt, 'time_s' => 30, 'source' => 'teacher', 'status' => 'published'];
    if ($type === 'single') {
        $options = array_values(array_filter(array_map('trim', (array)($in['options'] ?? [])), static fn(string $o): bool => $o !== ''));
        if (count($options) < 2) throw new RuntimeException('Otázka s výběrem potřebuje aspoň dvě možnosti.');
        $answer = trim((string)($in['answer'] ?? ''));
        if (!in_array($answer, $options, true)) throw new RuntimeException('Správná odpověď musí být jedna z nabízených možností.');
        $raw['options'] = $options;
        $raw['answer'] = $answer;
    } elseif ($type === 'bool') {
        $raw['answer'] = in_array((string)($in['answer'] ?? ''), ['1', 'true', 'ano'], true);
    } else {
        $answer = trim((string)($in['answer'] ?? ''));
        if ($answer === '') throw new RuntimeException('Vyplň správnou odpověď.');
        $raw['answer'] = mb_substr($answer, 0, 120);
    }
    $raw['explain'] = mb_substr(trim((string)($in['explain'] ?? '')), 0, 300);
    $item = tg58_quiz_normalize($raw);
    if ($item === null) throw new RuntimeException('Otázku se nepodařilo uložit – zkontroluj vyplněná pole.');
    tg58_update(tg58_bank_path(), static function (array $d) use ($item): array {
        $d['items'] = array_values(array_filter((array)($d['items'] ?? []), 'is_array'));
        $d['items'][] = $item;
        return $d;
    });
    tg58_quiz_bank_forget();
    return $item;
}

function tg58_bank_delete_question(string $id): void
{
    tg58_update(tg58_bank_path(), static function (array $d) use ($id): array {
        $d['items'] = array_values(array_filter((array)($d['items'] ?? []), static fn($it): bool => is_array($it) && (string)($it['id'] ?? '') !== $id));
        return $d;
    });
    tg58_quiz_bank_forget();
}
