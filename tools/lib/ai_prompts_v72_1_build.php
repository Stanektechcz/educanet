<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · generátor sekce „## 6. AI prompty pro studenta“ v lesson kitech (sdílí tools/v72_1_ai_prompts_build.php a audit).
 * Vstup jsou jen data, která žák v lekci vidí (lm71_bundle: název, cíl, témata a jejich shrnutí, konkrétní úkol kroku).
 * Neschválený návrh obsahu (overlay v72), odpovědi testů ani exit ticketů se nepoužívají. Výstup je deterministický.
 * Vyžaduje načtené: bootstrap.php, tutorial_v52.php, learning_v56.php, lesson_model_v71.php.
 */

const AIP721B_GENERIC_STEPS = ['exit ticket', 'předpověz výsledek', 'pochop princip více způsoby', 'guided practice', 'samostatná aplikace', 'review / qa', 'primární blok'];

/** Úklid textu: mezery, zdvojené tečky, koncová tečka pryč (vkládá se do věty). */
function aip721b_clean(string $text, int $max = 260): string
{
    $text = trim((string)preg_replace('/\s+/u', ' ', $text));
    $text = (string)preg_replace('/\.{2,}/u', '.', $text);
    if (mb_strlen($text) > $max) {
        $cut = mb_substr($text, 0, $max);
        $dot = mb_strrpos($cut, '. ');
        $text = $dot !== false && $dot > 60 ? mb_substr($cut, 0, $dot + 1) : rtrim($cut, ' ,;:') . '…';
    }
    return rtrim($text, " .");
}

function aip721b_is_network(string $classId): bool
{
    return in_array($classId, ['class_3a', 'class_4a'], true);
}

/** Konkrétní úkol z kroků lekce (bez obecných kroků šablony), jinak ''. */
function aip721b_task(array $steps): string
{
    foreach ($steps as $step) {
        $title = mb_strtolower(trim((string)($step['title'] ?? '')));
        $title = trim((string)preg_replace('/^\d+\s*·\s*/u', '', $title));
        if ($title === '' || in_array($title, AIP721B_GENERIC_STEPS, true) || preg_match('/^(předpověď|evidence|reflexe)\s*:/u', $title) === 1) continue;
        $tasks = array_values(array_filter(array_map(static fn($t): string => aip721b_clean((string)$t, 160), (array)($step['tasks'] ?? [])), static fn(string $t): bool => $t !== ''));
        if ($tasks === []) continue;
        return aip721b_clean((string)($step['title'] ?? ''), 80) . ': ' . implode('. ', array_slice($tasks, 0, 2));
    }
    return '';
}

/** Data lekce pro prompty. @return array{title:string,goal:string,topics:list<array{title:string,summary:string}>,task:string,network:bool} */
function aip721b_lesson_data(string $classId, int $number): array
{
    $module = is_array($GLOBALS['modules'][$classId] ?? null) ? $GLOBALS['modules'][$classId] : [];
    $b = lm71_bundle($classId, $module, $number);
    $topics = [];
    foreach ((array)$b['topics'] as $key => $t) {
        $title = aip721b_clean((string)($t['title'] ?? $key), 90);
        if ($title !== '' && !in_array($title, array_column($topics, 'title'), true)) $topics[] = ['title' => $title, 'summary' => aip721b_clean((string)($t['summary'] ?? ''), 240)];
    }
    $title = aip721b_clean((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', (string)$b['title']), 90);
    return ['title' => $title, 'goal' => aip721b_clean((string)$b['goal'], 260), 'topics' => $topics, 'task' => aip721b_task((array)$b['steps']), 'network' => aip721b_is_network($classId)];
}

/** Prompty lekce (název => text). @return list<array{title:string,text:string}> */
function aip721b_prompts(array $d): array
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
    $prompts = [];
    $prompts[] = ['title' => 'Vysvětli mi jednoduše: ' . $t1['title'],
        'text' => 'Učím se téma „' . $t1['title'] . '“ (lekce ' . $lesson . ', obor ' . $area . ').' . $sum($t1) . "\n"
            . 'Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ' . $example . '. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.'];
    $prompts[] = ['title' => 'Přirovnání a jeho hranice: ' . $t2['title'],
        'text' => 'V lekci ' . $lesson . ' jsme probírali téma „' . $t2['title'] . '“. Vysvětli mi ho pomocí přirovnání z běžného života.' . ($t2 === $t1 ? '' : $sum($t2)) . "\n"
            . 'Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.'];
    $prompts[] = ['title' => 'Sokratický tutor k cíli lekce',
        'text' => 'Cíl lekce ' . $lesson . ': ' . $d['goal'] . ".\n"
            . 'Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.'];
    $prompts[] = ['title' => 'Zkontroluj můj postup',
        'text' => 'Řeším úkol z lekce ' . $lesson . ': ' . $task . ".\n" . ($net
            ? 'Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.'
            : 'Popíšu ti slovy svůj návrh (rozvržení, písmo, barvy, texty) – bez fotek lidí a bez osobních údajů. Najdi jedno nejslabší místo vzhledem k cíli lekce a zeptej se mě, jak bych ho ověřil(a). Nepředělávej návrh za mě.')];
    $prompts[] = ['title' => 'Nový procvičovací úkol',
        'text' => 'Vymysli mi jeden nový realistický úkol na stejný princip jako lekce ' . $lesson . ' (' . $list . '), ale v jiné situaci'
            . ($net ? ' – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).' : ' – pro vymyšlenou firmu nebo akci.') . "\n"
            . 'Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.'];
    $prompts[] = ['title' => 'Vyzkoušej mě',
        'text' => 'Opakuji si lekci ' . $lesson . '. Polož mi 5 otázek k tématům ' . $list . ' od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.' . "\n"
            . 'Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.'];
    return $prompts;
}

/** Celá sekce 6 v Markdownu (bez koncového nového řádku). */
function aip721b_section(string $classId, int $number): string
{
    $d = aip721b_lesson_data($classId, $number);
    $check = $d['network'] ? 'v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele' : 'v náhledu na mobilu i počítači, v zápisu z hodiny nebo u učitele';
    $md = "## 6. AI prompty pro studenta\n\n"
        . "> **Dobrovolné procvičení s AI – než začneš:**\n"
        . "> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.\n"
        . "> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.\n"
        . "> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.\n"
        . '> - Výstup AI vždy ověř (' . $check . "). AI se plete i s jistotou.\n"
        . "> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.\n";
    foreach (aip721b_prompts($d) as $i => $p) $md .= "\n### " . ($i + 1) . '. ' . $p['title'] . "\n\n```text\n" . $p['text'] . "\n```\n";
    return rtrim($md);
}

/** Nahradí sekci 6 v textu kitu (čistá funkce). Bez sekce 6 vrací text beze změny. */
function aip721b_replace(string $markdown, string $section): string
{
    $start = strpos($markdown, "## 6. AI prompty pro studenta");
    if ($start === false) return $markdown;
    $next = preg_match('/\n## /', $markdown, $m, PREG_OFFSET_CAPTURE, $start + 5) === 1 ? (int)$m[0][1] : strlen($markdown);
    return substr($markdown, 0, $start) . $section . "\n" . substr($markdown, $next);
}
