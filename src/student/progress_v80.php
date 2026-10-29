<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once dirname(__DIR__, 2) . '/src/ui/widgets_v80.php';

/**
 * EDUCANET v80 · Můj pokrok: souhrn kompetencí (v62), cest (v63), úloh (v75/v76), exit ticketů (v72), opakování a cvičení (v80),
 * osobní cíle na týden a odznaky za zlepšení. Jen čtení kromě cílů (pg80_goal_add / pg80_goal_remove přes lr80_update).
 *
 * Cíle: nejvýš PG80_GOALS_MAX na týden, druh z pevného seznamu (žádný volný text) a cílové číslo z nabídky; plnění se počítá z dat
 * (vyřešená kola úloh, úspěšná opakování, vyřešená cvičení, kola bez nápovědy) v týdnu, kdy cíl vznikl. Splnění zapíše done_at při dalším zápisu.
 * Odznaky jsou za zlepšení, ne za pořadí: opravená chyba, dokončená řada opakování, lepší úspěšnost než minulý týden, vyřešeno bez nápovědy
 * po kole s nápovědou, splněný cíl. Žádný žebříček; vidí je jen žák a učitelé jeho třídy v rozsahu (záložka pokrok80).
 */

const PG80_GOALS = ['tasks' => [3, 5, 10], 'review' => [1, 3, 5], 'widgets' => [2, 4, 6], 'nohint' => [1, 3, 5]];
const PG80_GOALS_MAX = 3;
const PG80_GOALS_KEEP_WEEKS = 8;
const PG80_BADGES = ['fix', 'series', 'better', 'solo', 'goal'];
const PG80_BETTER_MIN = 3;
const PG80_MASTER_RATE = 75;

function pg80_week_start(int $ts): int
{
    return (int)strtotime(date('Y-m-d', $ts) . ' -' . ((int)date('N', $ts) - 1) . ' days 00:00:00');
}

function pg80_week_key(int $ts): string
{
    return date('o-\WW', $ts);
}

/** Vyřešená kola úloh v okně [od, do): list of [úloha, kolo]. */
function pg80_solved_rounds(array $tbState, int $from, int $to): array
{
    $out = [];
    foreach ($tbState as $task => $raw) {
        if (!is_array($raw) || !isset(TB75_TASKS[(string)$task])) continue;
        foreach (tb75_entry([$task => $raw], (string)$task)['log'] as $l) {
            $at = (int)($l['at'] ?? 0);
            if (!empty($l['ok']) && $at >= $from && $at < $to) $out[] = [(string)$task, (int)($l['r'] ?? 1)];
        }
    }
    return $out;
}

/** Plnění cíle (počet). */
function pg80_goal_progress(array $goal, array $state, array $tbState): int
{
    $from = pg80_week_start((int)($goal['at'] ?? 0));
    $to = $from + 7 * 86400;
    $in = static fn(array $times): int => count(array_filter($times, static fn(int $t): bool => $t >= $from && $t < $to));
    return match ((string)($goal['k'] ?? '')) {
        'tasks' => count(pg80_solved_rounds($tbState, $from, $to)),
        'nohint' => count(array_filter(pg80_solved_rounds($tbState, $from, $to), static fn(array $x): bool => !lr80_round_hinted($state, $x[0], $x[1]))),
        'review' => $in($state['log']['rep_ok_at']),
        'widgets' => $in($state['log']['w_ok_at']),
        default => 0,
    };
}

/** Zapíše splnění cílů a odstraní cíle starší než PG80_GOALS_KEEP_WEEKS (volá lr80_update při každém zápisu). */
function pg80_goals_settle(string $classId, string $ref, array $state, int $now): array
{
    if ($state['goals'] === []) return $state;
    $tb = tb75_student_state($classId, $ref);
    $keep = [];
    foreach ($state['goals'] as $g) {
        if ((int)($g['at'] ?? 0) < $now - PG80_GOALS_KEEP_WEEKS * 7 * 86400) continue;
        if ((int)($g['done_at'] ?? 0) === 0 && pg80_goal_progress($g, $state, $tb) >= (int)($g['n'] ?? 1)) $g['done_at'] = $now;
        $keep[] = $g;
    }
    $state['goals'] = $keep;
    return $state;
}

/** Cíle tohoto týdne. */
function pg80_goals_week(array $state, int $now): array
{
    $wk = pg80_week_key($now);
    return array_values(array_filter($state['goals'], static fn(array $g): bool => (string)($g['wk'] ?? '') === $wk));
}

/** @return array{ok:bool,error:?string} chyby: kind, limit, duplicate */
function pg80_goal_add(string $classId, string $studentKey, string $kind, int $n, ?int $now = null): array
{
    if (!isset(PG80_GOALS[$kind]) || !in_array($n, PG80_GOALS[$kind], true)) return ['ok' => false, 'error' => 'kind'];
    $res = ['ok' => false, 'error' => 'limit'];
    lr80_update($classId, lr80_ref($classId, $studentKey), static function (array $s, int $now) use ($kind, $n, &$res): array {
        $week = pg80_goals_week($s, $now);
        if (count($week) >= PG80_GOALS_MAX) return $s;
        if (in_array($kind, array_column($week, 'k'), true)) { $res['error'] = 'duplicate'; return $s; }
        $s['goals'][] = ['id' => 'g_' . bin2hex(random_bytes(4)), 'k' => $kind, 'n' => $n, 'wk' => pg80_week_key($now), 'at' => $now, 'done_at' => 0];
        $res = ['ok' => true, 'error' => null];
        return $s;
    }, $now);
    return $res;
}

function pg80_goal_remove(string $classId, string $studentKey, string $goalId, ?int $now = null): bool
{
    if (preg_match('/^g_[0-9a-f]{8}$/', $goalId) !== 1) return false;
    $done = false;
    lr80_update($classId, lr80_ref($classId, $studentKey), static function (array $s) use ($goalId, &$done): array {
        $before = count($s['goals']);
        $s['goals'] = array_values(array_filter($s['goals'], static fn(array $g): bool => (string)($g['id'] ?? '') !== $goalId));
        $done = count($s['goals']) < $before;
        return $s;
    }, $now);
    return $done;
}

/** Úspěšnost pokusů úloh v okně [od, do). @return array{0:int,1:int} [pokusy, správně] */
function pg80_rate_window(array $tbState, int $from, int $to): array
{
    $n = 0;
    $ok = 0;
    foreach ($tbState as $task => $raw) {
        if (!is_array($raw)) continue;
        foreach (tb75_entry([$task => $raw], (string)$task)['log'] as $l) {
            $at = (int)($l['at'] ?? 0);
            if ($at < $from || $at >= $to) continue;
            $n++;
            $ok += (int)!empty($l['ok']);
        }
    }
    return [$n, $ok];
}

/** Odznaky za zlepšení: id → ['earned'=>bool,'have'=>int,'need'=>int]. */
function pg80_badges(array $state, array $tbState, int $now): array
{
    [$n1, $ok1] = pg80_rate_window($tbState, $now - 7 * 86400, $now + 1);
    [$n0, $ok0] = pg80_rate_window($tbState, $now - 14 * 86400, $now - 7 * 86400);
    $better = $n1 >= PG80_BETTER_MIN && $n0 >= PG80_BETTER_MIN && $ok1 * $n0 > $ok0 * $n1;
    $solo = false;
    foreach ($tbState as $task => $raw) {
        if (!is_array($raw)) continue;
        $hinted = array_map('intval', (array)(($state['hint'][(string)$task] ?? [])['u'] ?? []));
        if ($hinted === []) continue;
        foreach (tb75_entry([$task => $raw], (string)$task)['log'] as $l) {
            if (!empty($l['ok']) && (int)$l['r'] > min($hinted) && !in_array((int)$l['r'], $hinted, true)) { $solo = true; break 2; }
        }
    }
    $goals = count(array_filter($state['goals'], static fn(array $g): bool => (int)($g['done_at'] ?? 0) > 0));
    $b = static fn(int $have, int $need): array => ['earned' => $have >= $need, 'have' => min($have, $need), 'need' => $need];
    return ['fix' => $b(count($state['log']['rep_ok_at']), 1), 'series' => $b((int)$state['log']['rep_done'], 1), 'better' => $b($better ? 1 : 0, 1),
        'solo' => $b($solo ? 1 : 0, 1), 'goal' => $b($goals, 1)];
}

/** Souhrn úloh žáka: úloha → [attempts, ok, solved, rate, status master|practise|new|progress]. */
function pg80_task_rows(string $classId, array $tbState, array $state): array
{
    $rows = [];
    foreach (tb75_tasks_for_class($classId) as $task) {
        $e = tb75_entry($tbState, $task);
        $attempts = count($e['log']);
        $ok = count(array_filter($e['log'], static fn(array $l): bool => !empty($l['ok'])));
        $rate = $attempts > 0 ? (int)round($ok / $attempts * 100) : 0;
        $status = $attempts === 0 ? 'new' : (isset($state['rep'][$task]) || $rate < 50 ? 'practise' : ($e['solved'] >= 2 && $rate >= PG80_MASTER_RATE ? 'master' : 'progress'));
        $rows[$task] = ['attempts' => $attempts, 'ok' => $ok, 'solved' => $e['solved'], 'rate' => $rate, 'status' => $status];
    }
    return $rows;
}

/** Souhrn žáka pro učitele (jen čísla, bez odpovědí). */
function pg80_teacher_row(string $classId, string $ref, array $data, array $tbData, int $now): array
{
    $state = lr80_state($classId, $ref, $data);
    $tb = (array)(($tbData['classes'][$classId] ?? [])[$ref] ?? []);
    $goals = pg80_goals_week($state, $now);
    $badges = pg80_badges($state, $tb, $now);
    return ['due' => count(lr80_rep_due($state, $now)), 'queued' => count($state['rep']), 'hints' => lr80_hint_total($state),
        'widgets' => array_sum(array_map(static fn($w): int => is_array($w) ? (int)($w['ok'] ?? 0) : 0, $state['w'])),
        'goals' => $goals, 'goals_done' => count(array_filter($goals, static fn(array $g): bool => pg80_goal_progress($g, $state, $tb) >= (int)$g['n'])),
        'badges' => count(array_filter($badges, static fn(array $b): bool => $b['earned'])), 'progress' => array_map(static fn(array $g): int => pg80_goal_progress($g, $state, $tb), $goals)];
}
