<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v74 · týdenní souhrn učitele (modul `tyden74`, sekce Dnes) – jen čtení, bez jmen žáků (dá se tisknout).
 * Za týden (pondělí–neděle, ?tyden=datum) a každou třídu v rozsahu:
 *   - odučeno vs. plán: výukové dny z kalendáře třídy (lekce i dny bez lekce) proti reflexím hodiny v73 (které učitel smí číst),
 *   - čeká na schválení s termínem: obsah lekcí a vazby na ŠVP, které se učí v příštích 14 dnech (T-14: schválit do data výuky − 14 dní),
 *   - žáci, kteří potřebují pomoc: počty z modelu Dnešní hodiny (zaostávají / bez aktivity / vybráno „Komu dnes pomoct“), bez jmen,
 *   - termíny: učitelské úkoly po termínu a do týdne, vlastní a týmové follow-upy s termínem v týdnu nebo po termínu.
 * Nic nezapisuje kromě odvoditelné cache zvládnutí v62 (stejně jako Dnešní hodina).
 */

const WK74_T14_DAYS = 14;

/** Pondělí týdne (Y-m-d) z parametru ?tyden= nebo z dneška. */
function wk74_week_start(string $param, string $today): string
{
    $base = preg_match('/^\d{4}-\d{2}-\d{2}$/D', $param) === 1 && strtotime($param) !== false ? $param : $today;
    return date('Y-m-d', (int)strtotime('monday this week', (int)strtotime($base)));
}

/** Výukové dny třídy v týdnu: [{date, lesson, title, kind}] podle kalendáře (s výjimkami). */
function wk74_planned(string $classId, string $weekStart): array
{
    $end = date('Y-m-d', (int)strtotime($weekStart . ' +6 days'));
    $out = [];
    foreach (adaptive_school_year_rows(lt70_school_year(), $classId) as $row) {
        $date = (string)($row['date'] ?? '');
        if ($date < $weekStart || $date > $end || (string)($row['status'] ?? '') !== 'teaching') continue;
        $n = (int)($row['lesson_number'] ?? 0);
        $out[] = ['date' => $date, 'lesson' => $n, 'title' => $n > 0 ? 'Lekce ' . $n : trim((string)($row['title'] ?? 'Den bez lekce')), 'kind' => $n > 0 ? 'lesson' : 'day'];
    }
    return $out;
}

/** Stav hodiny podle reflexe v73 (jen záznamy, které učitel smí číst): datum → stav. */
function wk74_reflections(string $classId): array
{
    $out = [];
    if (!function_exists('lr73_visible')) return $out;
    foreach (lr73_visible($classId) as $r) $out[(string)($r['date'] ?? '')] ??= (string)($r['state'] ?? '');
    return $out;
}

/**
 * Lekce do 14 dní (a tento týden), které nejsou schválené: obsah i vazba na ŠVP, s termínem schválení.
 * @return list<array{lesson:int,kind:string,date:string,deadline:string,late:bool,label:string}>
 */
function wk74_pending(string $classId, string $today, string $weekStart): array
{
    $horizon = date('Y-m-d', (int)strtotime($today . ' +' . WK74_T14_DAYS . ' days'));
    $from = min($today, $weekStart);
    $out = [];
    foreach (lc72_lesson_dates($classId) as $n => $date) {
        if ($date === '' || $date < $from || $date > $horizon) continue;
        $deadline = date('Y-m-d', (int)strtotime($date . ' -' . WK74_T14_DAYS . ' days'));
        $st = lc72_overlay($classId, (int)$n) !== [] ? lc72_status($classId, (int)$n) : null;
        if ($st !== null && $st['status'] !== 'schvaleno') $out[] = ['lesson' => (int)$n, 'kind' => 'lesson', 'date' => $date, 'deadline' => $deadline, 'late' => $deadline < $today, 'label' => $st['label']];
        $sv = function_exists('svp72_status') ? svp72_status($classId, (int)$n) : null;
        if ($sv !== null && !in_array($sv['status'], ['schvaleno', 'chybi'], true)) $out[] = ['lesson' => (int)$n, 'kind' => 'svp', 'date' => $date, 'deadline' => $deadline, 'late' => $deadline < $today, 'label' => $sv['label']];
    }
    usort($out, static fn(array $a, array $b): int => strcmp($a['date'], $b['date']) ?: strcmp($a['kind'], $b['kind']));
    return $out;
}

/** Termíny třídy: úkoly (po termínu, do týdne, nejčastější) a follow-upy s termínem v týdnu nebo po termínu (bez jmen žáků). */
function wk74_deadlines(string $classId, string $today, string $weekStart): array
{
    $tasks = lt70_tasks($classId, $today);
    $end = date('Y-m-d', (int)strtotime($weekStart . ' +6 days'));
    $follow = [];
    if (function_exists('teacher_ops_followups_visible')) {
        foreach (teacher_ops_followups_visible($classId) as $f) {
            $due = (string)($f['due_at'] ?? '');
            if ($due === '' || $due > $end) continue;
            $follow[] = ['title' => (string)($f['title'] ?? 'Follow-up'), 'due' => $due, 'late' => $due < $today, 'student' => (string)($f['student_key'] ?? '') !== ''];
        }
    }
    return ['tasks' => $tasks, 'followups' => $follow];
}

/**
 * Souhrn týdne za všechny třídy v rozsahu. $modules = už omezené na třídy učitele.
 * @return array{week:string,today:string,classes:list<array<string,mixed>>,totals:array<string,int>}
 */
function wk74_summary(array $modules, string $weekStart, ?int $now = null): array
{
    $now ??= time();
    $today = date('Y-m-d', $now);
    $totals = ['planned' => 0, 'past' => 0, 'taught' => 0, 'unreflected' => 0, 'pending' => 0, 'late' => 0, 'behind' => 0, 'idle' => 0, 'overdue' => 0, 'soon' => 0];
    $out = [];
    foreach (lt70_classes($modules, lt70_school_year()) as $classId) {
        $refl = wk74_reflections($classId);
        $rows = [];
        foreach (wk74_planned($classId, $weekStart) as $p) {
            $past = $p['date'] <= $today;
            $state = $refl[$p['date']] ?? '';
            $rows[] = $p + ['past' => $past, 'state' => $state];
            $totals['planned']++;
            if ($past) {
                $totals['past']++;
                if (in_array($state, ['cele', 'cast'], true)) $totals['taught']++;
                if ($state === '') $totals['unreflected']++;
            }
        }
        $pending = wk74_pending($classId, $today, $weekStart);
        $model = lt70_model($modules, $classId, 0, $now);
        $help = function_exists('in74_candidates') ? count(in74_candidates($model, $now)['rows']) : 0;
        $deadlines = wk74_deadlines($classId, $today, $weekStart);
        $totals['pending'] += count($pending);
        $totals['late'] += count(array_filter($pending, static fn(array $p): bool => $p['late']));
        $totals['behind'] += (int)$model['kpis']['behind'];
        $totals['idle'] += (int)$model['kpis']['idle'];
        $totals['overdue'] += (int)$deadlines['tasks']['overdue'];
        $totals['soon'] += (int)$deadlines['tasks']['soon'] + count(array_filter($deadlines['followups'], static fn(array $f): bool => !$f['late']));
        $out[] = ['class' => $classId, 'name' => (string)($modules[$classId]['name'] ?? $classId), 'days' => $rows, 'pending' => $pending,
            'students' => (int)$model['kpis']['students'], 'behind' => (int)$model['kpis']['behind'], 'idle' => (int)$model['kpis']['idle'], 'help' => $help, 'deadlines' => $deadlines];
    }
    return ['week' => $weekStart, 'today' => $today, 'classes' => $out, 'totals' => $totals];
}
