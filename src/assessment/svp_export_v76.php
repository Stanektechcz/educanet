<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · plnění ŠVP po třídách a pololetích (modul `svp76`, jen čtení, bez jmen žáků; tisk a CSV pro třídního učitele a vedení).
 *
 * Výsledek vzdělávání ŠVP (svp_v72.php) je v pololetí:
 *   naplněno      – aspoň jedna lekce pololetí se SCHVÁLENOU vazbou na výsledek je odučená (reflexe v73: celé nebo část),
 *   čeká na vazbu – lekce je odučená, ale vazba na ŠVP ještě není schválená (Schvalování lekcí → Vazba na ŠVP),
 *   nepotvrzeno   – termín lekce se schválenou vazbou minul, reflexe chybí,
 *   naplánováno   – lekce s vazbou teprve přijde,
 *   mezera        – výsledek ročníku třídy (předmět z jejího plánu), ke kterému v pololetí není žádná lekce.
 * Pololetí: 1. = září–leden, 2. = únor–srpen (podle termínu lekce v kalendáři třídy s výjimkami). Čistá funkce sx76_analyze() hlídá audit.
 */

const SX76_STATUSES = ['naplněno' => 'ok', 'čeká na vazbu' => 'warn', 'nepotvrzeno' => 'warn', 'naplánováno' => '', 'mezera' => 'bad'];
const SX76_CLASSES = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];

/** Hranice pololetí podle začátku školního roku (rok prvního data kalendáře). @return array{0:string,1:string} */
function sx76_half_range(array $schoolYear, int $half): array
{
    $first = (string)(((array)($schoolYear['calendar'] ?? []))[0]['date'] ?? date('Y') . '-09-01');
    $y = (int)substr($first, 0, 4);
    return $half === 1 ? [$y . '-09-01', ($y + 1) . '-01-31'] : [($y + 1) . '-02-01', ($y + 1) . '-08-31'];
}

/** Aktuální pololetí podle data (únor–srpen = 2). */
function sx76_current_half(string $today): int
{
    $m = (int)substr($today, 5, 2);
    return $m >= 2 && $m <= 8 ? 2 : 1;
}

/**
 * Analýza (čistá funkce). $lessons = [n => ['date' => termín v kalendáři, 'ids' => list výsledků, 'svp' => stav vazby, 'state' => stav reflexe]].
 * @return array{rows:list<array<string,mixed>>,count:array<string,int>,expected:int,lessons_taught:int,lessons_total:int,approved:int}
 */
function sx76_analyze(array $lessons, array $outcomes, int $classYear, array $range, string $today): array
{
    $byOutcome = [];
    $subjects = [];
    $taught = 0;
    $total = 0;
    $approved = 0;
    foreach ($lessons as $n => $l) {
        foreach ((array)$l['ids'] as $id) if (isset($outcomes[$id])) $subjects[$outcomes[$id]['subject']] = true;
        $date = (string)($l['date'] ?? '');
        if ($date === '' || $date < $range[0] || $date > $range[1]) continue;
        $total++;
        $isTaught = in_array((string)($l['state'] ?? ''), ['cele', 'cast'], true);
        $taught += $isTaught ? 1 : 0;
        $approved += (string)($l['svp'] ?? '') === 'schvaleno' ? 1 : 0;
        foreach ((array)$l['ids'] as $id) if (isset($outcomes[$id])) $byOutcome[$id][] = ['n' => (int)$n, 'date' => $date, 'svp' => (string)($l['svp'] ?? ''), 'taught' => $isTaught, 'past' => $date <= $today];
    }
    foreach ($outcomes as $id => $o) if ((int)$o['year'] === $classYear && isset($subjects[$o['subject']])) $byOutcome[$id] ??= [];
    $rows = [];
    $count = array_fill_keys(array_keys(SX76_STATUSES), 0);
    foreach ($byOutcome as $id => $refs) {
        $ok = array_filter($refs, static fn(array $r): bool => $r['taught'] && $r['svp'] === 'schvaleno');
        $status = match (true) {
            $refs === [] => 'mezera',
            $ok !== [] => 'naplněno',
            array_filter($refs, static fn(array $r): bool => $r['taught']) !== [] => 'čeká na vazbu',
            array_filter($refs, static fn(array $r): bool => $r['past'] && $r['svp'] === 'schvaleno') !== [] => 'nepotvrzeno',
            default => 'naplánováno',
        };
        $count[$status]++;
        $o = $outcomes[$id];
        $rows[] = ['id' => (string)$id, 'subject' => (string)$o['subject'], 'subject_label' => SVP72_SUBJECTS[$o['subject']] ?? (string)$o['subject'], 'year' => (int)$o['year'],
            'page' => (int)$o['page'], 'text' => (string)$o['text'], 'status' => $status, 'other_year' => (int)$o['year'] !== $classYear,
            'taught' => array_values(array_map(static fn(array $r): int => $r['n'], array_filter($refs, static fn(array $r): bool => $r['taught']))),
            'lessons' => array_values(array_map(static fn(array $r): int => $r['n'], $refs))];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a['subject'], $b['subject']) ?: ($a['year'] <=> $b['year']) ?: strcmp($a['id'], $b['id']));
    $expected = count(array_filter($rows, static fn(array $r): bool => !$r['other_year']));
    return ['rows' => $rows, 'count' => $count, 'expected' => $expected, 'lessons_taught' => $taught, 'lessons_total' => $total, 'approved' => $approved];
}

/** Vstupní data třídy: termín lekce (kalendář s výjimkami), výsledky ŠVP, stav vazby a stav reflexe (jen čtení). */
function sx76_lesson_inputs(string $classId): array
{
    $dates = [];
    foreach (adaptive_school_year_rows(lt70_school_year(), $classId) as $row) {
        $n = is_array($row) ? (int)($row['lesson_number'] ?? 0) : 0;
        if ($n >= 1 && $n <= 28 && (string)($row['status'] ?? '') === 'teaching' && !isset($dates[$n])) $dates[$n] = (string)($row['date'] ?? '');
    }
    $states = function_exists('pv76_reflection_states') && function_exists('lr73_visible') ? pv76_reflection_states(lr73_visible($classId)) : [];
    $records = svp72_records();
    $out = [];
    for ($n = 1; $n <= 28; $n++) {
        $out[$n] = ['date' => (string)($dates[$n] ?? ''), 'ids' => svp72_lesson_ids($classId, $n), 'svp' => svp72_status($classId, $n, $records)['status'], 'state' => (string)($states[$n]['state'] ?? '')];
    }
    return $out;
}

/** Celá analýza třídy a pololetí. */
function sx76_class(string $classId, int $half, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $half = $half === 2 ? 2 : 1;
    $range = sx76_half_range(lt70_school_year(), $half);
    return sx76_analyze(sx76_lesson_inputs($classId), svp72_outcomes(), (int)(SVP72_CLASS_YEAR[$classId] ?? 0), $range, $today)
        + ['class' => $classId, 'half' => $half, 'range' => $range, 'today' => $today];
}

/** Buňka CSV bez vzorců (=, +, -, @ na začátku → apostrof) a bez řídicích znaků. */
function sx76_csv_cell(string|int $value): string
{
    $v = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$value));
    return $v !== '' && str_contains('=+-@', $v[0]) ? "'" . $v : $v;
}

/** CSV (UTF-8 s BOM, středník) – bez jmen žáků. */
function sx76_csv_body(array $a): string
{
    $fp = fopen('php://temp', 'r+');
    fwrite($fp, "\xEF\xBB\xBF");
    fputcsv($fp, ['trida', 'pololeti', 'vysledek_id', 'predmet', 'rocnik', 'strana_svp', 'vysledek', 'stav', 'lekce_oducene', 'lekce_s_vazbou'], ';', '"', '');
    foreach ($a['rows'] as $r) {
        fputcsv($fp, array_map('sx76_csv_cell', [$a['class'], $a['half'] . '. pololetí', $r['id'], $r['subject_label'], $r['year'], $r['page'], $r['text'], $r['status'],
            implode(' ', array_map(static fn(int $n): string => 'L' . $n, $r['taught'])), implode(' ', array_map(static fn(int $n): string => 'L' . $n, $r['lessons']))]), ';', '"', '');
    }
    rewind($fp);
    $body = (string)stream_get_contents($fp);
    fclose($fp);
    return $body;
}
