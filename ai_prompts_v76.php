<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · AI prompty pro studenta u lekcí vlny 2 (L17–L28) ve stylu v72.1.
 *
 * Lesson kity L19–L28 vycházejí ze šablonového obsahu (lessons_v30.php) – jejich prompty by mluvily o anglických titulcích
 * a obecných úkolech. Proto se u lekce vlny 2 se SCHVÁLENÝM obsahem (lc72_student_overlay) prompty skládají ze schváleného
 * obsahu: název, cíl, kritéria úspěchu, první konkrétní úkol a termíny glosáře. Neschválený návrh žák nikdy nevidí;
 * učitel vidí v náhledu Schvalování lekcí prompty z návrhu, které žák dostane po schválení.
 * Bez schválení (nebo pro L5–L16) zůstávají prompty původní lekce (v77: ai_prompts_v77.php, shodné s dřívějšími lesson kity).
 * v77: stejně se chovají i L1–L4, které dostaly obsah (lesson_content_v77_*) – aip76_lesson_in_scope().
 * Upozornění na soukromí vykresluje aip721_render_student_block() (tr(), en/uk); prompty jsou výukový obsah (česky).
 * Prompty neobsahují odpovědi exit ticketu ani klíč – jen to, co žák v lekci vidí. Výstup je deterministický.
 */

const AIP76_FIRST_LESSON = 17;
const AIP76_EARLY_LESSONS = [1, 4];   // v77: L1–L4 s obsahem (lesson_content_v77_*)

/** Lekce, u kterých se prompty skládají ze (schváleného) obsahu: L1–L4 (v77) a L17–L28 (v76). */
function aip76_lesson_in_scope(int $number): bool
{
    return ($number >= AIP76_EARLY_LESSONS[0] && $number <= AIP76_EARLY_LESSONS[1]) || ($number >= AIP76_FIRST_LESSON && $number <= 28);
}

/** Úklid textu pro vložení do věty (mezery, koncová tečka, délka). */
function aip76_clean(string $text, int $max = 240): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    if (mb_strlen($text) > $max) $text = rtrim(mb_substr($text, 0, $max), ' ,;:') . '…';
    return rtrim($text, ' .');
}

/** Prompty ze schváleného (nebo pro učitele z navrženého) obsahu lekce. @return list<array{title:string,text:string}> */
function aip76_prompts(string $classId, array $ov): array
{
    $net = in_array($classId, ['class_3a', 'class_4a'], true);
    $title = aip76_clean((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', (string)($ov['title'] ?? '')), 90);
    if ($title === '') return [];
    $lesson = '„' . $title . '“';
    $area = $net ? 'Linux, sítě a provoz serverů' : 'grafika a webdesign';
    $goal = aip76_clean((string)preg_replace('/^Na konci hodiny umím\s*/u', '', (string)($ov['goal']['student'] ?? '')), 260);
    $criteria = array_values(array_filter(array_map(static fn($c): string => aip76_clean((string)$c, 160), (array)($ov['goal']['success_criteria'] ?? []))));
    $tasks = array_values(array_filter(array_map(static fn($t): string => is_array($t) ? aip76_clean((string)($t['text'] ?? ''), 200) : '', (array)($ov['tasks'] ?? []))));
    $terms = [];
    if (function_exists('lg72_term')) {
        foreach ((array)($ov['glossary'] ?? []) as $id) {
            $t = lg72_term((string)$id);
            if (is_array($t)) $terms[] = (string)$t['cs'] . ' (' . (string)$t['term'] . ')';
        }
    }
    $concepts = $terms !== [] ? implode(', ', $terms) : $title;
    $example = $net ? 'ze správy malé školní nebo firemní sítě' : 'z plakátu, aplikace nebo webové stránky';
    $task = $tasks[0] ?? $goal;
    $practice = $tasks[1] ?? $task;
    $prompts = [
        ['title' => 'Vysvětli mi jednoduše: ' . $title,
            'text' => 'Učím se lekci ' . $lesson . ' (obor ' . $area . '). Cíl hodiny: umím ' . $goal . ".\n"
                . 'Vysvětli mi hlavní myšlenku v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ' . $example . '. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.'],
        ['title' => 'Přirovnání a jeho hranice',
            'text' => 'V lekci ' . $lesson . ' používáme pojmy: ' . $concepts . ". Vysvětli mi je pomocí přirovnání z běžného života.\n"
                . 'Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.'],
        ['title' => 'Sokratický tutor k cíli lekce',
            'text' => 'Cíl lekce ' . $lesson . ': umím ' . $goal . ".\n"
                . 'Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.'],
        ['title' => 'Zkontroluj můj postup',
            'text' => 'Řeším úkol z lekce ' . $lesson . ': ' . $task . ".\n" . ($net
                ? 'Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.'
                : 'Popíšu ti slovy svůj návrh (rozvržení, písmo, barvy, texty) – bez fotek lidí a bez osobních údajů. Najdi jedno nejslabší místo vzhledem k cíli lekce a zeptej se mě, jak bych ho ověřil(a). Nepředělávej návrh za mě.')],
        ['title' => 'Nový procvičovací úkol',
            'text' => 'Vymysli mi jeden nový realistický úkol podobný tomuto z lekce ' . $lesson . ': ' . $practice . ', ale v jiné situaci'
                . ($net ? ' – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).' : ' – pro vymyšlenou firmu nebo akci.') . "\n"
                . 'Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.'],
        ['title' => 'Ověř, jestli splňuji kritéria',
            'text' => 'Opakuji si lekci ' . $lesson . '. Kritéria úspěchu: ' . ($criteria !== [] ? implode('; ', $criteria) : $goal) . ".\n"
                . 'Ke každému kritériu mi polož jednu otázku nebo malý úkol, po každé odpovědi řekni, co bylo přesné a co chybělo. Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.'],
    ];
    return $prompts;
}

/**
 * Prompty lekce L1–L4 / L17–L28: žák jen ze schváleného obsahu ($draft = false), učitel z návrhu ($draft = true).
 * null = lekce není v rozsahu nebo nemá (schválený) obsah – použijí se prompty původní lekce (ai_prompts_v77.php).
 * @return list<array{title:string,text:string}>|null
 */
function aip76_prompts_for(string $classId, int $number, bool $draft = false): ?array
{
    if (!aip76_lesson_in_scope($number)) return null;
    if (!function_exists('lc72_student_overlay')) {
        if (!is_file(__DIR__ . '/lesson_approval_v72.php')) return null;
        require_once __DIR__ . '/lesson_approval_v72.php';
    }
    if (!function_exists('lg72_term') && is_file(__DIR__ . '/lesson_glossary_v72.php')) require_once __DIR__ . '/lesson_glossary_v72.php';
    $ov = $draft ? lc72_overlay($classId, $number) : lc72_student_overlay($classId, $number);
    if (!is_array($ov) || $ov === []) return null;
    $prompts = aip76_prompts($classId, $ov);
    return $prompts === [] ? null : $prompts;
}
