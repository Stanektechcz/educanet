<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · Plán vs. skutečnost (modul `plan76`, jen čtení, bez jmen žáků, s tiskem).
 *
 * Pro třídu porovná tematický plán (základní kalendář school_year.php: lekce 1–28 a jejich termín) se skutečností:
 *   - kalendář třídy s výjimkami (adaptive_school_year_rows: odpadlé hodiny posouvají frontu obsahu, co se nevejde, je „mimo kalendář“),
 *   - reflexe hodiny v73 (lr73_visible – jen záznamy, které učitel smí číst): odučeno celé / část / neodučeno.
 * Stav lekce: odučeno | část | neodučeno | posunuto (kalendář ji přesunul) | nepotvrzeno (termín minul, reflexe chybí) | naplánováno | mimo kalendář.
 * Zpoždění = lekce, které měly být podle plánu odučené do dneška, minus skutečně odučené (část = ½; nepotvrzené se počítají jako odučené
 *   podle kalendáře a stránka je vyjmenuje k potvrzení). Prognóza = 28 lekcí minus to, co se nevejde ani do rezervy (Mastery clinic / rezervní dny).
 * Návrh dohnání (sloučit / zkrátit / dobrovolná domácí příprava / rezerva) je jen nabídka: nic se nezapisuje, posun kalendáře potvrzuje učitel
 *   výjimkou v Kalendáři a stav hodiny reflexí. Výpočet je čistá funkce pv76_analyze() – audit ji ověřuje na vymyšlených datech.
 */

const PV76_LESSONS = 28;
const PV76_STATUSES = ['odučeno' => 'ok', 'část' => 'warn', 'neodučeno' => 'bad', 'posunuto' => 'warn', 'nepotvrzeno' => 'warn', 'naplánováno' => '', 'mimo kalendář' => 'bad'];
const PV76_RESERVE_KINDS = ['reserve', 'mastery'];
const PV76_SUGGEST_MAX = 6;

/** Tematický plán: číslo lekce → termín v základním kalendáři (bez výjimek). @return array<int,string> */
function pv76_plan_dates(array $schoolYear): array
{
    $out = [];
    foreach ((array)($schoolYear['calendar'] ?? []) as $row) {
        $n = is_array($row) ? (int)($row['lesson_number'] ?? 0) : 0;
        if ($n >= 1 && $n <= PV76_LESSONS && (string)($row['status'] ?? '') === 'teaching' && !isset($out[$n])) $out[$n] = (string)($row['date'] ?? '');
    }
    ksort($out);
    return $out;
}

/**
 * Kalendář třídy s výjimkami: termín lekce, nevešlé lekce a rezervní dny po dnešku.
 * @return array{dates:array<int,string>,unscheduled:list<int>,reserve:list<array{date:string,title:string}>,cancelled:list<string>}
 */
function pv76_calendar(array $rows, string $today): array
{
    $dates = [];
    $unscheduled = [];
    $reserve = [];
    $cancelled = [];
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $n = (int)($row['lesson_number'] ?? 0);
        if ($n >= 1 && (string)($row['status'] ?? '') === 'teaching' && !isset($dates[$n])) $dates[$n] = (string)($row['date'] ?? '');
        if (in_array((string)($row['kind'] ?? ''), PV76_RESERVE_KINDS, true) && (string)($row['status'] ?? '') === 'teaching' && (string)($row['date'] ?? '') > $today) {
            $reserve[] = ['date' => (string)$row['date'], 'title' => (string)($row['title'] ?? 'Rezerva')];
        }
        if (isset($row['original_lesson_number'])) $cancelled[] = (string)($row['date'] ?? '');
        foreach ((array)($row['unscheduled_agenda'] ?? []) as $item) {
            $u = is_array($item) ? (int)($item['lesson_number'] ?? 0) : 0;
            if ($u >= 1) $unscheduled[] = $u;
        }
    }
    return ['dates' => $dates, 'unscheduled' => array_values(array_unique($unscheduled)), 'reserve' => $reserve, 'cancelled' => $cancelled];
}

/** Nejnovější stav hodiny pro každou lekci z reflexí v73 (záznamy už omezené na ty, které učitel smí číst). @return array<int,array{state:string,date:string}> */
function pv76_reflection_states(array $reflections): array
{
    $out = [];
    foreach ($reflections as $r) {
        $n = (int)($r['lesson'] ?? 0);
        $state = (string)($r['state'] ?? '');
        if ($n < 1 || $n > PV76_LESSONS || !isset(LR73_STATES[$state])) continue;
        $date = (string)($r['date'] ?? '');
        if (!isset($out[$n]) || strcmp($date, $out[$n]['date']) > 0) $out[$n] = ['state' => $state, 'date' => $date];
    }
    return $out;
}

/**
 * Analýza plán vs. skutečnost (čistá funkce). $lessons = [n => ['title', 'competencies' => list<id>, 'timeline' => list, 'homework' => list<string>]].
 * @return array<string,mixed>
 */
function pv76_analyze(array $planDates, array $calendar, array $states, array $lessons, string $today): array
{
    $rows = [];
    $count = ['odučeno' => 0, 'část' => 0, 'neodučeno' => 0, 'posunuto' => 0, 'nepotvrzeno' => 0, 'naplánováno' => 0, 'mimo kalendář' => 0];
    $plannedByToday = 0;
    $done = 0.0;
    foreach (range(1, PV76_LESSONS) as $n) {
        $plan = (string)($planDates[$n] ?? '');
        $cal = in_array($n, $calendar['unscheduled'], true) ? '' : (string)($calendar['dates'][$n] ?? '');
        $state = (string)($states[$n]['state'] ?? '');
        if ($plan !== '' && $plan <= $today) $plannedByToday++;
        $status = match (true) {
            $state === 'cele' => 'odučeno',
            $state === 'cast' => 'část',
            $state === 'neoduceno' => 'neodučeno',
            $cal === '' => 'mimo kalendář',
            $cal <= $today => 'nepotvrzeno',
            $plan !== '' && $cal > $plan => 'posunuto',
            default => 'naplánováno',
        };
        $count[$status]++;
        $done += match ($status) { 'odučeno', 'nepotvrzeno' => 1.0, 'část' => 0.5, default => 0.0 };
        $rows[$n] = ['number' => $n, 'title' => (string)($lessons[$n]['title'] ?? ('Lekce ' . $n)), 'plan' => $plan, 'calendar' => $cal, 'status' => $status,
            'reflected_at' => (string)($states[$n]['date'] ?? ''), 'shift_days' => $plan !== '' && $cal !== '' ? (int)round(((int)strtotime($cal) - (int)strtotime($plan)) / 86400) : null];
    }
    $delay = max(0.0, $plannedByToday - $done);
    $needed = $count['neodučeno'] + (int)ceil($count['část'] / 2) + $count['mimo kalendář'];
    $reserve = count($calendar['reserve']);
    $missing = max(0, $needed - $reserve);
    $forecast = ['fits' => PV76_LESSONS - $missing, 'needed' => $needed, 'reserve' => $reserve, 'reserve_left' => max(0, $reserve - $needed), 'missing' => $missing];
    $warnings = [];
    if ($missing > 0) $warnings[] = 'Podle současného kalendáře se nevejde ' . $missing . ' ' . ($missing === 1 ? 'lekce' : ($missing < 5 ? 'lekce' : 'lekcí')) . ' ani do rezervy – zvolte způsob dohnání níže.';
    if ($delay >= 2) $warnings[] = 'Třída je ' . pv76_num($delay) . ' lekce za tematickým plánem.';
    if ($count['nepotvrzeno'] > 0) $warnings[] = 'Hodin bez reflexe: ' . $count['nepotvrzeno'] . ' – do zpoždění se počítají jako odučené podle kalendáře; potvrďte je v Reflexi hodiny.';
    return ['rows' => $rows, 'count' => $count, 'planned_by_today' => $plannedByToday, 'done' => $done, 'delay' => $delay, 'forecast' => $forecast,
        'warnings' => $warnings, 'cancelled' => $calendar['cancelled'], 'suggestions' => pv76_suggestions($rows, $lessons, $calendar['reserve'], max($needed, (int)ceil($delay)), $today)];
}

function pv76_num(float $n): string
{
    return str_replace('.', ',', rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.'));
}

/**
 * Návrhy dohnání (jen nabídka, nic se nezapisuje): rezerva, sloučení sousedních budoucích lekcí se společnou kompetencí,
 * zkrácení nejdelšího úseku plánu a dobrovolná domácí příprava místo části dlužné lekce.
 * @return list<array{kind:string,label:string,detail:string,saves:int}>
 */
function pv76_suggestions(array $rows, array $lessons, array $reserve, int $needed, string $today): array
{
    if ($needed <= 0) return [];
    $out = [];
    $backlog = array_values(array_filter($rows, static fn(array $r): bool => in_array($r['status'], ['neodučeno', 'část', 'mimo kalendář'], true)));
    $first = $backlog[0]['number'] ?? null;
    foreach ($reserve as $day) {
        $out[] = ['kind' => 'rezerva', 'label' => 'Využít ' . $day['title'] . ' (' . date('j. n.', (int)strtotime($day['date'])) . ')',
            'detail' => $first !== null ? 'Dobrat L' . $first . ' v termínu, který kalendář drží jako rezervu.' : 'Den rezervy pohltí jednu odpadlou hodinu.', 'saves' => 90];
    }
    $future = array_values(array_filter($rows, static fn(array $r): bool => in_array($r['status'], ['naplánováno', 'posunuto'], true)));
    $merges = 0;
    $merged = [];
    for ($i = 0; $i + 1 < count($future) && $merges < 2 && count($out) < PV76_SUGGEST_MAX; $i++) {
        [$a, $b] = [$future[$i], $future[$i + 1]];
        if ($b['number'] !== $a['number'] + 1) continue;
        $common = array_values(array_intersect((array)($lessons[$a['number']]['competencies'] ?? []), (array)($lessons[$b['number']]['competencies'] ?? [])));
        if ($common === []) continue;
        $out[] = ['kind' => 'sloučit', 'label' => 'Sloučit L' . $a['number'] . ' a L' . $b['number'],
            'detail' => 'Společná kompetence ' . implode(', ', $common) . ': z každé lekce hlavní úkol na úrovni Základ, zbytek jako dobrovolné procvičení.', 'saves' => 90];
        $merges++;
        $merged[$a['number']] = $merged[$b['number']] = true;
        $i++;
    }
    foreach ($future as $r) {
        if (count($out) >= PV76_SUGGEST_MAX) break;
        if (isset($merged[$r['number']])) continue;   // sloučenou lekci už nezkracujeme
        $segs = array_slice((array)($lessons[$r['number']]['timeline'] ?? []), 1, -1);
        if ($segs === []) continue;
        usort($segs, static fn(array $x, array $y): int => (int)$y['minutes'] <=> (int)$x['minutes']);
        $long = $segs[0];
        if ((int)$long['minutes'] < 20) continue;
        $cut = intdiv((int)$long['minutes'], 2);
        $out[] = ['kind' => 'zkrátit', 'label' => 'Zkrátit L' . $r['number'] . ': úsek „' . (string)$long['phase'] . '“',
            'detail' => 'Z ' . (int)$long['minutes'] . ' na ' . ((int)$long['minutes'] - $cut) . ' min (úroveň Základ pro všechny, výzva jako dobrovolná).', 'saves' => $cut];
        if (count(array_filter($out, static fn(array $s): bool => $s['kind'] === 'zkrátit')) >= 2) break;
    }
    foreach ($backlog as $r) {
        if (count($out) >= PV76_SUGGEST_MAX) break;
        $hw = trim((string)($lessons[$r['number']]['homework'][0] ?? ''));
        if ($hw === '') continue;
        $out[] = ['kind' => 'domácí příprava', 'label' => 'Dobrovolná domácí příprava k L' . $r['number'],
            'detail' => 'Nabídnout jako dobrovolné (nezapočítává se do hodnocení): ' . preg_replace('/^Volitelné:\s*/u', '', $hw), 'saves' => 0];
    }
    return array_slice($out, 0, PV76_SUGGEST_MAX);
}

/** Data lekcí z modelu lm71 pro návrhy (titulek, kompetence, plán, domácí příprava). @return array<int,array<string,mixed>> */
function pv76_lessons(string $classId): array
{
    $out = [];
    foreach (lm71_lessons($classId) as $n => $l) {
        $out[(int)$n] = ['title' => (string)$l['title'], 'competencies' => array_values(array_filter(array_map(static fn($c): string => is_array($c) ? (string)($c['id'] ?? '') : '', (array)$l['competencies']))),
            'timeline' => (array)$l['timeline'], 'homework' => array_values(array_map('strval', (array)$l['homework']))];
    }
    return $out;
}

/** Celá analýza třídy (čte kalendář, reflexe a model lekcí; nic nezapisuje). */
function pv76_class(string $classId, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $year = lt70_school_year();
    $calendar = pv76_calendar(adaptive_school_year_rows($year, $classId), $today);
    $states = pv76_reflection_states(function_exists('lr73_visible') ? lr73_visible($classId) : []);
    return pv76_analyze(pv76_plan_dates($year), $calendar, $states, pv76_lessons($classId), $today) + ['class' => $classId, 'today' => $today];
}
