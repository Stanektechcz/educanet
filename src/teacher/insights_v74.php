<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · „Komu dnes pomoct“ (fáze D, vhledy na žáka) – jen čtení, žádné nové úložiště ani POST akce.
 *
 * Nad modelem Dnešní hodiny (lt70_model: žáci, postup, signály v61, úkoly) vybere nejvýš IN74_MAX žáků s konkrétními důvody:
 *   chybějící exit ticket schválené lekce, 2× chybný exit ticket, klesající výsledky ve třech lekcích, nedokončená minulá lekce,
 *   chybění v minulé hodině s kódem, 7+ dní bez aktivity, nízké zvládnutí (v61), úkol po termínu, otevřený follow-up učitele.
 * Každý důvod má zdroj dat a navržený krok s odkazem na existující záložku (detail žáka, intervence, komunikace) – zásah jedním
 * klikem bez nové akce. Žák „zatím bez aktivity“ (žádná data) se nevybírá: bez dat ≠ zaostává. Žák štítek nikdy nevidí, nic se
 * neukládá, neexportuje ani nevstupuje do hodnocení. Rozsah: model je už omezený na třídu v rozsahu učitele (lt70_class_allowed).
 */

const IN74_MAX = 5;
const IN74_DECLINE_LESSONS = 3;
const IN74_UNFINISHED_PCT = 75;
/** Váhy důvodů (pořadí žáků); vyšší = naléhavější. */
const IN74_WEIGHTS = ['exit_wrong' => 3, 'declining' => 3, 'absent' => 2, 'inactive' => 2, 'low_mastery' => 2, 'overdue' => 2, 'unfinished' => 2, 'exit_missing' => 1, 'open_note' => 1];

/** Lekce třídy, které se už učily (datum < dnes), podle kalendáře: číslo → datum. */
function in74_taught_dates(string $classId, string $today): array
{
    $out = [];
    if (!function_exists('lt70_school_year')) return $out;
    foreach (adaptive_school_year_rows(lt70_school_year(), $classId) as $row) {
        $n = (int)($row['lesson_number'] ?? 0);
        $date = (string)($row['date'] ?? '');
        if ($n >= 1 && $date !== '' && $date < $today && (string)($row['status'] ?? '') === 'teaching') $out[$n] = $date;
    }
    ksort($out);
    return $out;
}

/** Schválené exit tickety učených lekcí (nejnovější poslední). @return list<int> */
function in74_ticket_lessons(string $classId, array $taught): array
{
    if (!function_exists('lx72_normalize') || !function_exists('lc72_status')) return [];
    $out = [];
    foreach (array_keys($taught) as $n) {
        if (lc72_status($classId, (int)$n)['status'] === 'schvaleno' && lx72_normalize(lc72_overlay($classId, (int)$n)) !== null) $out[] = (int)$n;
    }
    return $out;
}

/**
 * Důvody jednoho žáka (čistá funkce nad připravenými vstupy).
 * $ctx: exits (lekce → odpověď|null pro tohoto žáka), ticket_lessons, trend (lekce → % fází), absent_date (''), followups (list titulků), prev_lesson.
 * @return list<array{code:string,text:string,source:string,action:string,weight:int}>
 */
function in74_reasons(array $row, array $ctx): array
{
    $out = [];
    $add = static function (string $code, string $text, string $source, string $action) use (&$out): void {
        $out[] = ['code' => $code, 'text' => $text, 'source' => $source, 'action' => $action, 'weight' => IN74_WEIGHTS[$code] ?? 1];
    };
    $answered = array_filter((array)($ctx['exits'] ?? []), 'is_array');
    $lastTwo = array_slice($answered, -2, 2, true);
    if (count($lastTwo) === 2 && array_sum(array_map(static fn(array $a): int => (int)($a['ok'] ?? 0), $lastTwo)) === 0) {
        $add('exit_wrong', '2× chybný exit ticket (lekce ' . implode(' a ', array_keys($lastTwo)) . ')', 'Exit tickety schválených lekcí – poslední dvě odpovědi nebyly správně.',
            'Při samostatné práci se zastavte a nechte si postup vysvětlit; nabídněte podporu z diferenciace lekce.');
    }
    $tickets = (array)($ctx['ticket_lessons'] ?? []);
    $lastTicket = $tickets !== [] ? (int)end($tickets) : 0;
    if ($lastTicket > 0 && !isset($answered[$lastTicket])) {
        $add('exit_missing', 'Chybí exit ticket z lekce ' . $lastTicket, 'Lekce ' . $lastTicket . ' má schválený exit ticket, odpověď žáka chybí.', 'Na začátku hodiny dejte 2 minuty na exit ticket z lekce ' . $lastTicket . '.');
    }
    $trend = array_values((array)($ctx['trend'] ?? []));
    if (count($trend) === IN74_DECLINE_LESSONS && $trend[0] > $trend[1] && $trend[1] > $trend[2] && $trend[2] < IN74_UNFINISHED_PCT) {
        $add('declining', 'Klesající výsledky: ' . implode(' → ', array_map(static fn(int $p): string => $p . ' %', $trend)), 'Splněné fáze posledních ' . IN74_DECLINE_LESSONS . ' lekcí (výklad, test, projekt, odevzdání).',
            'Domluvte krátký plán podpory (Intervence) a za týden výsledek porovnejte.');
    } elseif ($row['prev'] !== null && (int)$row['prev'] < IN74_UNFINISHED_PCT) {
        $add('unfinished', 'Minulá lekce hotová na ' . (int)$row['prev'] . ' %', 'Postup v lekci ' . (int)($ctx['prev_lesson'] ?? 0) . ' (fáze výklad, test, projekt, odevzdání).',
            'Dejte 10 minut na dokončení minulé lekce, než začne nová práce.');
    }
    if ((string)($ctx['absent_date'] ?? '') !== '') {
        $add('absent', 'Chyběl/a v minulé hodině (' . date('j. n.', (int)strtotime((string)$ctx['absent_date'])) . ')', 'V hodině s kódem se nepřipojil/a ke kódu hodiny (docházku systém nevede).',
            'Pošlete shrnutí minulé hodiny a co je potřeba doplnit.');
    }
    foreach ((array)($row['signals'] ?? []) as $signal) {
        if (str_starts_with((string)$signal, 'Bez aktivity')) $add('inactive', (string)$signal, 'Poslední aktivita v aplikaci (Přehled třídy v61).', 'Krátce se zeptejte, co blokuje – přihlášení, čas, nebo téma.');
        if (str_starts_with((string)$signal, 'Nízké mastery')) $add('low_mastery', (string)$signal, 'Průměrné zvládnutí dovedností (Přehled třídy v61).', 'Zařaďte krátké opakování základů nebo výukovou cestu.');
    }
    if ((int)($row['overdue'] ?? 0) > 0) {
        $c = (int)$row['overdue'];
        $add('overdue', $c . ' ' . ($c === 1 ? 'úkol' : ($c < 5 ? 'úkoly' : 'úkolů')) . ' po termínu', 'Učitelské úkoly třídy (stav „zadáno“, termín uplynul).', 'Domluvte nový termín nebo nejmenší další krok.');
    }
    foreach (array_slice((array)($ctx['followups'] ?? []), 0, 1) as $title) {
        $add('open_note', 'Otevřený follow-up: ' . mb_substr((string)$title, 0, 60), 'Vaše nebo týmové follow-upy k žákovi, které nejsou uzavřené.', 'Vyřešte a uzavřete follow-up „' . mb_substr((string)$title, 0, 60) . '“.');
    }
    usort($out, static fn(array $a, array $b): int => $b['weight'] <=> $a['weight']);
    return $out;
}

/**
 * Kandidáti „Komu dnes pomoct“ pro model Dnešní hodiny. Index = pořadí žáka v tabulce žáků (kvůli „Žák N“ při skrytých jménech).
 * @return array{rows:list<array{index:int,key:string,label:string,score:int,reasons:list<array>}>,idle:int,students:int,with_reasons:int}
 */
function in74_candidates(array $m, ?int $now = null): array
{
    $class = (string)($m['class'] ?? '');
    $students = array_values((array)($m['students'] ?? []));
    $res = ['rows' => [], 'idle' => 0, 'students' => count($students), 'with_reasons' => 0];
    if ($class === '' || $students === [] || !lt70_class_allowed($class)) return $res;
    $today = (string)$m['today'];
    $current = (int)$m['lesson']['number'];
    $taught = array_filter(in74_taught_dates($class, $today), static fn(int $n): bool => $n < $current, ARRAY_FILTER_USE_KEY);
    $tickets = in74_ticket_lessons($class, array_slice($taught, -4, 4, true));   // stačí poslední 4 učené lekce (chybějící = poslední, chybné = poslední dvě odpovědi)
    $answers = $tickets !== [] && function_exists('lx72_answers') ? lx72_answers($class) : [];
    $progress = lt70_progress_by_hash($class);
    $module = is_array($m['module'] ?? null) ? $m['module'] : [];
    $trendLessons = [];
    for ($n = $current - IN74_DECLINE_LESSONS; $n < $current - 1; $n++) if ($n >= 1) $trendLessons[$n] = in74_lesson_shape($class, $module, $n);   // minulou lekci (N−1) už spočítal lt70_students jako `prev`
    $lastDate = $taught !== [] ? (string)end($taught) : '';
    $absentSession = $lastDate !== '' ? lt70_session($class, $lastDate) : ['exists' => false, 'status' => []];
    $followups = [];
    if (function_exists('teacher_ops_followups_visible')) {
        foreach (teacher_ops_followups_visible($class) as $f) if ((string)($f['student_key'] ?? '') !== '') $followups[(string)$f['student_key']][] = (string)($f['title'] ?? 'follow-up');
    }
    foreach ($students as $i => $row) {
        if (!empty($row['idle'])) { $res['idle']++; continue; }
        $hash = (string)$row['hash'];
        $exits = [];
        foreach ($tickets as $n) $exits[$n] = function_exists('lx72_student_hash') ? ($answers[(string)$n][lx72_student_hash((string)$row['key'])] ?? null) : null;
        $trend = [];
        foreach ($trendLessons as $n => $l) $trend[$n] = (int)round(lt70_phases_done((array)($progress[$hash][(string)$n] ?? []), array_column($l['topics'], 'key'), count($l['steps'])) / 4 * 100);
        if ($row['prev'] !== null) $trend[$current - 1] = (int)$row['prev'];
        $ctx = ['exits' => $exits, 'ticket_lessons' => $tickets, 'trend' => count($trend) === IN74_DECLINE_LESSONS ? $trend : [], 'prev_lesson' => $current - 1,
            'absent_date' => $absentSession['exists'] && !isset($absentSession['status'][$hash]) ? $lastDate : '', 'followups' => $followups[(string)$row['key']] ?? []];
        $signals = array_values(array_filter((array)($row['reasons'] ?? []), static fn($s): bool => str_starts_with((string)$s, 'Bez aktivity') || str_starts_with((string)$s, 'Nízké mastery')));   // signály v61 už spočítal lt70_students
        $reasons = in74_reasons($row + ['signals' => $signals], $ctx);
        if ($reasons === []) continue;
        $res['with_reasons']++;
        $res['rows'][] = ['index' => $i + 1, 'key' => (string)$row['key'], 'label' => (string)$row['label'], 'score' => array_sum(array_column($reasons, 'weight')), 'reasons' => $reasons];
    }
    usort($res['rows'], static fn(array $a, array $b): int => ($b['score'] <=> $a['score']) ?: (count($b['reasons']) <=> count($a['reasons'])) ?: ($a['index'] <=> $b['index']));
    $res['rows'] = array_slice($res['rows'], 0, IN74_MAX);
    return $res;
}

/**
 * v75 · výkon: pro trend stačí klíče témat a počet kroků lekce – bere se přímo z balíčku lekce (lm71_bundle, ≈ 0,01 ms) místo celého
 * modelu lekce (lt70_lesson ≈ 0,5 ms). Témata ani kroky overlay obsahové stopy nemění, výsledek je tedy shodný.
 * @return array{topics:list<array{key:string}>,steps:list<mixed>}
 */
function in74_lesson_shape(string $classId, array $module, int $number): array
{
    if (!function_exists('lm71_bundle')) {
        $l = lt70_lesson($classId, $module, $number);
        return ['topics' => $l['topics'], 'steps' => $l['steps']];
    }
    $bundle = lm71_bundle($classId, $module, max(1, min(LM71_LESSONS, $number)));
    return ['topics' => array_map(static fn($k): array => ['key' => (string)$k], array_keys((array)($bundle['topics'] ?? []))), 'steps' => array_values((array)($bundle['steps'] ?? []))];
}

/** Odkaz navrženého kroku na existující záložku (jen záložky, které role vidí); '' = bez odkazu. @return array{0:string,1:string} [url, popisek] */
function in74_action_link(string $code, string $classId, string $studentKey): array
{
    $visible = static fn(string $tab): bool => !function_exists('c70_tab_visible') || c70_tab_visible($tab);
    $q = static fn(string $tab): string => '?' . http_build_query(['tab' => $tab, 'class' => $classId, 'student' => $studentKey]);
    if (in_array($code, ['declining', 'low_mastery'], true) && $visible('interventions')) return [$q('interventions'), 'Naplánovat podporu'];
    if ($code === 'absent' && $visible('communications')) return ['?' . http_build_query(['tab' => 'communications', 'class' => $classId]), 'Napsat shrnutí'];
    if ($visible('student360')) return [$q('student360'), 'Detail žáka'];
    return ['', ''];
}
