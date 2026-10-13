<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · AI prompty původní lekce skládané za běhu (náhrada sekce „## 6. AI prompty pro studenta“ v lesson kitech).
 *
 * Šablonové lesson kity (materials/lesson_kits/<třída>/lesson_NN.md) jsou ve v77 vyřazené: jejich sekci 6 generoval
 * tools/v72_1_ai_prompts_build.php deterministicky z dat, která žák v lekci vidí (lm71_bundle: název, cíl, témata a jejich
 * shrnutí, konkrétní úkol kroku). Stejný generátor teď běží přímo – výstup je pro všech 112 lekcí shodný s vyřazenými kity
 * (otisk SHA-256 každé lekce hlídá tools/v77_content_audit.php proti tools/lib/v77_kit_prompts_fixture.php).
 * Neschválený návrh obsahu (overlay), odpovědi testů ani exit ticketů se nepoužívají. Lekce se schváleným obsahem L1–L4
 * a L17–L28 berou prompty z obsahu (ai_prompts_v76.php); tento soubor řeší jen zbytek (původní obsah).
 * Vyžaduje lm71 (lesson_model_v71.php); bez něj vrací [] (blok AI promptů se pak nevykreslí).
 */

const AIP77_GENERIC_STEPS = ['exit ticket', 'předpověz výsledek', 'pochop princip více způsoby', 'guided practice', 'samostatná aplikace', 'review / qa', 'primární blok'];
const AIP77_MAX_PROMPTS = 8;
const AIP77_MAX_LEN = 1200;

/** Úklid textu: mezery, zdvojené tečky, koncová tečka pryč (vkládá se do věty). */
function aip77_clean(string $text, int $max = 260): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    $text = (string)preg_replace('/\.{2,}/u', '.', $text);
    if (mb_strlen($text) > $max) {
        $cut = mb_substr($text, 0, $max);
        $dot = mb_strrpos($cut, '. ');
        $text = $dot !== false && $dot > 60 ? mb_substr($cut, 0, $dot + 1) : rtrim($cut, ' ,;:') . '…';
    }
    return rtrim($text, ' .');
}

/** Konkrétní úkol z kroků lekce (bez obecných kroků šablony), jinak ''. */
function aip77_task(array $steps): string
{
    foreach ($steps as $step) {
        $title = mb_strtolower(trim((string)($step['title'] ?? '')));
        $title = trim((string)preg_replace('/^\d+\s*·\s*/u', '', $title));
        if ($title === '' || in_array($title, AIP77_GENERIC_STEPS, true) || preg_match('/^(předpověď|evidence|reflexe)\s*:/u', $title) === 1) continue;
        $tasks = array_values(array_filter(array_map(static fn($t): string => aip77_clean((string)$t, 160), (array)($step['tasks'] ?? [])), static fn(string $t): bool => $t !== ''));
        if ($tasks === []) continue;
        return aip77_clean((string)($step['title'] ?? ''), 80) . ': ' . implode('. ', array_slice($tasks, 0, 2));
    }
    return '';
}

/** Data původní lekce pro prompty (jen to, co žák v lekci vidí). @return array{title:string,goal:string,topics:list<array{title:string,summary:string}>,task:string,network:bool} */
function aip77_lesson_data(string $classId, int $number): array
{
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    $b = lm71_bundle($classId, $module, $number);
    $topics = [];
    foreach ((array)$b['topics'] as $key => $t) {
        $title = aip77_clean((string)($t['title'] ?? $key), 90);
        if ($title !== '' && !in_array($title, array_column($topics, 'title'), true)) $topics[] = ['title' => $title, 'summary' => aip77_clean((string)($t['summary'] ?? ''), 240)];
    }
    $title = aip77_clean((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', (string)$b['title']), 90);
    return ['title' => $title, 'goal' => aip77_clean((string)$b['goal'], 260), 'topics' => $topics, 'task' => aip77_task((array)$b['steps']),
        'network' => in_array($classId, ['class_3a', 'class_4a'], true)];
}

/** Šest promptů z dat lekce (stejné texty jako vyřazené kity). @return list<array{title:string,text:string}> */
function aip77_build(array $d): array
{
    $net = $d['network'];
    $lesson = '„' . $d['title'] . '“';
    $area = $net ? 'Linux a počítačové sítě' : 'grafika a webdesign';
    $t1 = $d['topics'][0] ?? ['title' => $d['title'], 'summary' => ''];
    $t2 = $d['topics'][1] ?? $t1;
    $list = implode(', ', array_map(static fn(array $t): string => '„' . $t['title'] . '“', array_slice($d['topics'], 0, 4))) ?: $lesson;
    $sum = static fn(array $t): string => $t['summary'] !== '' ? ' Z hodiny vím: ' . $t['summary'] . '.' : '';
    $example = $net ? 'ze správy malé firemní nebo školní sítě' : 'z plakátu nebo webové stránky';
    $task = $d['task'] !== '' ? $d['task'] : 'vlastní příklad k cíli lekce – ' . $d['goal'];
    return [
        ['title' => 'Vysvětli mi jednoduše: ' . $t1['title'],
            'text' => 'Učím se téma „' . $t1['title'] . '“ (lekce ' . $lesson . ', obor ' . $area . ').' . $sum($t1) . "\n"
                . 'Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ' . $example . '. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.'],
        ['title' => 'Přirovnání a jeho hranice: ' . $t2['title'],
            'text' => 'V lekci ' . $lesson . ' jsme probírali téma „' . $t2['title'] . '“. Vysvětli mi ho pomocí přirovnání z běžného života.' . ($t2 === $t1 ? '' : $sum($t2)) . "\n"
                . 'Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.'],
        ['title' => 'Sokratický tutor k cíli lekce',
            'text' => 'Cíl lekce ' . $lesson . ': ' . $d['goal'] . ".\n"
                . 'Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.'],
        ['title' => 'Zkontroluj můj postup',
            'text' => 'Řeším úkol z lekce ' . $lesson . ': ' . $task . ".\n" . ($net
                ? 'Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.'
                : 'Popíšu ti slovy svůj návrh (rozvržení, písmo, barvy, texty) – bez fotek lidí a bez osobních údajů. Najdi jedno nejslabší místo vzhledem k cíli lekce a zeptej se mě, jak bych ho ověřil(a). Nepředělávej návrh za mě.')],
        ['title' => 'Nový procvičovací úkol',
            'text' => 'Vymysli mi jeden nový realistický úkol na stejný princip jako lekce ' . $lesson . ' (' . $list . '), ale v jiné situaci'
                . ($net ? ' – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).' : ' – pro vymyšlenou firmu nebo akci.') . "\n"
                . 'Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.'],
        ['title' => 'Vyzkoušej mě',
            'text' => 'Opakuji si lekci ' . $lesson . '. Polož mi 5 otázek k tématům ' . $list . ' od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.' . "\n"
                . 'Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.'],
    ];
}

/**
 * Prompty původní lekce 1–28 (cache za požadavek). Stejná pravidla jako dřívější parser kitu: nejvýš 8 promptů,
 * text do 1 200 znaků, prázdné se vynechají. @return list<array{title:string,text:string}>
 */
function aip77_original_prompts(string $classId, int $number): array
{
    static $memo = [];
    $key = $classId . '#' . $number;
    if (isset($memo[$key])) return $memo[$key];
    if (preg_match('/^class_[a-z0-9]{1,8}$/D', $classId) !== 1 || $number < 1 || $number > 28 || !function_exists('lm71_bundle')) return $memo[$key] = [];
    $out = [];
    foreach (aip77_build(aip77_lesson_data($classId, $number)) as $p) {
        $title = trim($p['title']);
        $text = trim($p['text']);
        if ($title === '' || $text === '' || mb_strlen($text) > AIP77_MAX_LEN) continue;
        $out[] = ['title' => $title, 'text' => $text];
        if (count($out) >= AIP77_MAX_PROMPTS) break;
    }
    return $memo[$key] = $out;
}
