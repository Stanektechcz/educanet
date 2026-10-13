<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v77 · „Středa: T-14“ – týdenní přehled, co musí učitel schválit v příštích 14 dnech (modul `streda77`, sekce Dnes).
 *
 * Pravidlo T-14 (docs/PROMPT_V71_V75.md): lekce je úplná a schválená nejpozději 14 dní před výukou. Termín schválení =
 * datum výuky podle kalendáře třídy (s výjimkami) − 14 dní. Přehled ukazuje výuku do 28 dní (termín do 14 dní):
 *   po termínu  – výuka do 14 dní a obsah lekce není schválený (návrh, vráceno, změněno po schválení),
 *   tento týden – termín schválení za 0–6 dní, příští týden – za 7–14 dní, schváleno – hotovo.
 * Vazba na ŠVP se ukazuje vedle (schvaluje se zvlášť, do T-14 nepočítá). Dny bez lekce vidí jen učitel – schválení nepotřebují.
 * Jen čtení: žádná POST akce ani GET parametr registru (jen ?class= – obecná kontrola rozsahu). Jeden klik vede do Hromadného
 * schválení s rozsahem `t14` a třídou (bulk_approval_v74.php). t1477_overdue_count() = odznak v menu (lehká cesta bez modelu lm71).
 */

const T1477_DAYS = 14;
const T1477_HORIZON = 28;
const T1477_BADGE_TTL = 300;   // s – odznak v menu se v relaci přepočítá nejpozději po 5 minutách nebo po změně schválení
const T1477_BUCKETS = ['po_terminu' => 'po termínu', 'tento_tyden' => 'schválit do 7 dní', 'pristi_tyden' => 'schválit za 8–14 dní', 'schvaleno' => 'schváleno'];

/** Třídy v rozsahu učitele (bez cockpitu v70 přímo přes teacher59). @return list<string> */
function t1477_classes(): array
{
    if (function_exists('c70_classes')) return c70_classes();
    $all = ['class_1a', 'class_2a', 'class_3a', 'class_4a'];
    return function_exists('teacher59_allowed_class_ids') ? array_values(array_intersect($all, teacher59_allowed_class_ids())) : $all;
}

/** Termín schválení (Y-m-d) pro datum výuky. */
function t1477_deadline(string $date): string
{
    return date('Y-m-d', (int)strtotime($date . ' -' . T1477_DAYS . ' days'));
}

/** Kategorie řádku podle stavu obsahu a termínu (čistá funkce). */
function t1477_bucket(string $status, string $date, string $today): string
{
    if ($status === 'schvaleno') return 'schvaleno';
    $deadline = t1477_deadline($date);
    if ($deadline < $today) return 'po_terminu';
    $days = (int)floor(((int)strtotime($deadline) - (int)strtotime($today)) / 86400);
    return $days <= 6 ? 'tento_tyden' : 'pristi_tyden';
}

/** Dny mezi dvěma daty (Y-m-d). */
function t1477_days_between(string $from, string $to): int
{
    return (int)floor(((int)strtotime($to) - (int)strtotime($from)) / 86400);
}

/**
 * Přehled jedné třídy: lekce s výukou v [dnes, dnes + 28] a dny bez lekce ve stejném okně.
 * @return array{class:string,rows:list<array<string,mixed>>,days:list<array{date:string,title:string}>,counts:array<string,int>,pending:int}
 */
function t1477_class_plan(string $classId, string $today, ?array $records = null): array
{
    $records ??= lc72_records();
    $svpRecords = function_exists('svp72_records') ? svp72_records() : [];
    $end = date('Y-m-d', (int)strtotime($today . ' +' . T1477_HORIZON . ' days'));
    $rows = [];
    $counts = array_fill_keys(array_keys(T1477_BUCKETS), 0);
    $pending = 0;
    foreach (lc72_lesson_dates($classId) as $n => $date) {
        if ($date === '' || $date < $today || $date > $end) continue;
        $st = lc72_status($classId, (int)$n, $records);
        if ($st['status'] === 'puvodni') continue;
        $model = lm71_lesson($classId, (int)$n);
        $svp = function_exists('svp72_status') ? svp72_status($classId, (int)$n, $svpRecords) : ['status' => 'chybi', 'label' => 'chybí vazba'];
        $bucket = t1477_bucket($st['status'], $date, $today);
        $counts[$bucket]++;
        if ($st['status'] !== 'schvaleno') $pending++;
        if (!in_array($svp['status'], ['schvaleno', 'chybi'], true)) $pending++;
        $rows[] = ['lesson' => (int)$n, 'title' => (string)$model['title'], 'date' => $date, 'deadline' => t1477_deadline($date), 'days' => t1477_days_between($today, $date),
            'status' => $st, 'svp' => $svp, 'bucket' => $bucket, 'score' => (int)$model['completeness']['score'], 'total' => (int)$model['completeness']['total']];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a['date'], $b['date']) ?: ($a['lesson'] <=> $b['lesson']));
    $days = [];
    foreach (adaptive_school_year_rows(lc72_school_year(), $classId) as $row) {
        $date = (string)($row['date'] ?? '');
        if ((string)($row['status'] ?? '') !== 'teaching' || (int)($row['lesson_number'] ?? 0) > 0 || $date < $today || $date > $end) continue;
        $days[] = ['date' => $date, 'title' => trim((string)($row['calendar_label'] ?? ($row['title'] ?? 'Den bez lekce')))];
    }
    return ['class' => $classId, 'rows' => $rows, 'days' => $days, 'counts' => $counts, 'pending' => $pending];
}

/** Přehled všech tříd v rozsahu. @return array{today:string,classes:list<array<string,mixed>>,totals:array<string,int>} */
function t1477_plan(array $classes, ?string $today = null): array
{
    $today ??= date('Y-m-d');
    $records = lc72_records();
    $out = [];
    $totals = array_fill_keys(array_keys(T1477_BUCKETS), 0);
    foreach ($classes as $c) {
        $plan = t1477_class_plan($c, $today, $records);
        foreach ($plan['counts'] as $k => $v) $totals[$k] += $v;
        $out[] = $plan;
    }
    return ['today' => $today, 'classes' => $out, 'totals' => $totals];
}

/**
 * Počet lekcí po termínu T-14 (termín schválení už uplynul, výuka teprve bude, obsah neschválený) ve třídách učitele – odznak v menu cockpitu.
 * Lehká cesta: overlay jen ze souborů třídy (lc72_overlay_light), jedno čtení záznamů schválení, bez modelu lm71. Cache za požadavek.
 */
function t1477_overdue_count(?string $today = null, ?array $classes = null): int
{
    static $memo = [];
    $today ??= date('Y-m-d');
    $classes ??= t1477_classes();
    $key = $today . '|' . implode(',', $classes);
    if (isset($memo[$key])) return $memo[$key];
    // Cache v relaci (jen počet, bez dat žáků): platí do změny souboru schválení, nejdéle 5 minut (výjimky kalendáře, nový den).
    $approvals = defined('STORAGE_DIR') ? STORAGE_DIR . '/lesson_approvals_v72.json.php' : '';
    $sig = $key . '|' . ($approvals !== '' && is_file($approvals) ? (string)filemtime($approvals) . ':' . (string)filesize($approvals) : '-') . '|' . intdiv(time(), T1477_BADGE_TTL);
    $cached = $_SESSION['t1477_badge'] ?? null;
    if (session_status() === PHP_SESSION_ACTIVE && is_array($cached) && ($cached['sig'] ?? '') === $sig && is_int($cached['n'] ?? null)) return $memo[$key] = $cached['n'];
    if (!function_exists('lc72_overlay_light')) {
        if (!is_file(__DIR__ . '/lesson_approval_v72.php')) return $memo[$key] = 0;
        require_once __DIR__ . '/lesson_approval_v72.php';
    }
    $records = lc72_records();
    $end = date('Y-m-d', (int)strtotime($today . ' +' . T1477_DAYS . ' days'));
    $count = 0;   // stejná hranice jako t1477_bucket(): po termínu = výuka − 14 dní < dnes
    foreach ($classes as $c) {
        foreach (lc72_lesson_dates($c) as $n => $date) {
            if ($date === '' || $date < $today || $date > $end) continue;
            $ov = lc72_overlay_light($c, (int)$n);
            if ($ov === []) continue;
            $rec = $records[$c][(string)$n] ?? null;
            $approved = is_array($rec) && (string)($rec['status'] ?? '') === 'schvaleno' && hash_equals((string)($rec['hash'] ?? ''), lc72_overlay_hash($ov));
            if (!$approved && t1477_bucket('navrh', $date, $today) === 'po_terminu') $count++;
        }
    }
    if (session_status() === PHP_SESSION_ACTIVE) $_SESSION['t1477_badge'] = ['sig' => $sig, 'n' => $count];
    return $memo[$key] = $count;
}

/** Odkaz do Hromadného schválení s rozsahem T-14 a třídou (jeden klik). */
function t1477_bulk_url(string $classId): string
{
    return '?' . http_build_query(['tab' => 'hromadne74', 'f' => '1', 'rozsah' => 't14', 'class' => $classId, 'co_lekce' => '1', 'co_svp' => '1']);
}
