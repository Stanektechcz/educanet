<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Štafeta.
 *
 * 3–5 úseků; výstup (postup) úseku n je vstupem úseku n+1 – tým nesmí na úsek n+1, dokud nemá n hotový.
 * Linie sítě: úsek = úloha Linux Labu ve sdíleném světě týmu (kontext `tg:<id>`, viz linux_v58_levels_tg.php).
 * Linie grafika (1.A/2.A): úsek = kvízová/„rozbitá stránka“ otázka z banky, odpovídá se přímo ve hře.
 * Bodování: vyhrává čas týmu (dřív dohráno = líp); po 4 minutách bez postupu smí kdokoli z týmu pomoct,
 * ale připočte se 30 s penalizace – fér vůči týmům, které úsek zvládly samy. Bez záporných bodů, žádné
 * veřejné zesměšnění posledních – tým, který nedohraje, dostane účastnickou XP jako všichni ostatní.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';

const TG58_RELAY_HELP_AFTER_S = 240;
const TG58_RELAY_HELP_PENALTY_S = 30;

function tg58_relay_default_settings(): array
{
    return ['legs' => TG58_LAB_RELAY_LEGS];
}

function tg58_relay_parse_settings(array $in, array $ctx): array
{
    $legs = (int)($in['legs'] ?? TG58_LAB_RELAY_LEGS);
    if ($legs < 3 || $legs > 5) throw new RuntimeException('Štafeta má mít 3 až 5 úseků.');
    return ['legs' => $legs];
}

/** Otázky grafické linie pro jeden tým – deterministické podle semínka hry+týmu, kategorie „rozbitá stránka“ přednostně. */
function tg58_relay_gfx_quiz_ids(string $gameId, string $teamId, string $classId, int $legs): array
{
    $items = tg58_quiz_select('graphics', $classId, $legs, $gameId . '|relay|' . $teamId);
    return array_map(static fn(array $it): string => $it['id'], $items);
}

function tg58_relay_on_start(array $session, array $ctx): array
{
    $legs = (int)$session['settings']['legs'];
    $now = (int)$ctx['now'];
    $teams = [];
    foreach ((array)$ctx['teams'] as $team) {
        $row = ['leg' => 1, 'leg_started_at' => tg58_iso($now), 'penalty_s' => 0, 'helped_legs' => [], 'history' => [], 'finished_at' => null];
        if ((string)$session['line'] === 'graphics') $row['quiz_ids'] = tg58_relay_gfx_quiz_ids((string)$session['id'], (string)$team['id'], (string)$session['class_id'], $legs);
        $teams[(string)$team['id']] = $row;
    }
    $session['game'] = ['legs' => $legs, 'teams' => $teams, 'winners' => []];
    return $session;
}

/** Sdílená logika postupu (volá jak lab hook pro linii sítě, tak student_action pro linii grafika). Idempotentní. */
function tg58_relay_advance_team(array $session, string $teamId, int $expectedLeg, int $now): array
{
    $t = $session['game']['teams'][$teamId] ?? null;
    if ($t === null || (int)$t['leg'] !== $expectedLeg) return $session; // už postoupeno (souběh) – no-op
    $legs = (int)$session['game']['legs'];
    $t['history'][] = ['leg' => $expectedLeg, 'at' => tg58_iso($now)];
    if ($expectedLeg >= $legs) {
        $t['finished_at'] = tg58_iso($now);
        $session['game']['winners'] = array_values(array_unique(array_merge((array)$session['game']['winners'], [$teamId])));
    } else {
        $t['leg'] = $expectedLeg + 1;
        $t['leg_started_at'] = tg58_iso($now);
    }
    $session['game']['teams'][$teamId] = $t;
    return $session;
}

/** Volá linux_v58_levels_tg.php po vyřešení úlohy Labu v kontextu tg (linie sítě). */
function tg58_relay_on_leg_complete(string $gameId, string $studentKey, string $levelId, int $now): void
{
    $legNo = tg58_relay_leg_no($levelId);
    if ($legNo === null) return;
    tg58_mutate($gameId, static function (array $s) use ($studentKey, $legNo, $now): array {
        if ((string)($s['type'] ?? '') !== 'relay') return $s;
        $teamId = tg58_team_of($s, $studentKey);
        return $teamId === null ? $s : tg58_relay_advance_team($s, $teamId, $legNo, $now);
    });
}

function tg58_relay_team_view(array $session, string $teamId, int $now): array
{
    $t = $session['game']['teams'][$teamId] ?? ['leg' => 1, 'leg_started_at' => tg58_iso($now), 'helped_legs' => [], 'finished_at' => null];
    $legs = (int)$session['game']['legs'];
    $started = tg58_ts($t['leg_started_at'] ?? null) ?? $now;
    $out = ['leg' => (int)$t['leg'], 'legs_total' => $legs, 'finished' => $t['finished_at'] !== null, 'legs_done' => count((array)$t['history']), 'can_help' => $t['finished_at'] === null && ($now - $started) >= TG58_RELAY_HELP_AFTER_S && !in_array((int)$t['leg'], (array)$t['helped_legs'], true)];
    if ($t['finished_at'] !== null) return $out;
    if ((string)$session['line'] === 'networks') {
        $legIds = tg58_relay_net_leg_ids();
        $levelId = $legIds[$t['leg'] - 1] ?? null;
        $level = $levelId !== null && function_exists('lab57_level') ? lab57_level($levelId) : null;
        $out += ['mode' => 'lab', 'level_id' => $levelId, 'level_title' => $level['title'] ?? null];
    } else {
        $itemId = $t['quiz_ids'][$t['leg'] - 1] ?? null;
        $item = $itemId !== null ? tg58_quiz_find($itemId) : null;
        $out += ['mode' => 'quiz', 'question' => $item !== null ? tg58_quiz_public($item, (string)$session['id'] . '|' . $teamId . '|' . $t['leg']) : null];
    }
    return $out;
}

function tg58_relay_student_view(array $session, array $ctx): array
{
    $teamId = (string)$ctx['team_id'];
    return tg58_relay_team_view($session, $teamId, (int)$ctx['now']);
}

function tg58_relay_student_action(string $action, array $session, array $ctx, array $payload): array
{
    $teamId = (string)$ctx['team_id'];
    $now = (int)$ctx['now'];
    $t = $session['game']['teams'][$teamId] ?? null;
    if ($t === null) throw new RuntimeException(tr('Tým nebyl nalezen.'));
    if ($action === 'relay_help') {
        $started = tg58_ts($t['leg_started_at'] ?? null) ?? $now;
        if ($t['finished_at'] !== null) throw new RuntimeException(tr('Tenhle tým už doběhl.'));
        if ($now - $started < TG58_RELAY_HELP_AFTER_S) throw new RuntimeException(tr('Pomoc jde přivolat až po {m} minutách na úseku.', ['m' => intdiv(TG58_RELAY_HELP_AFTER_S, 60)]));
        if (in_array((int)$t['leg'], (array)$t['helped_legs'], true)) throw new RuntimeException(tr('Na tomhle úseku už jste pomoc použili.'));
        $t['helped_legs'][] = (int)$t['leg'];
        $t['penalty_s'] = (int)($t['penalty_s'] ?? 0) + TG58_RELAY_HELP_PENALTY_S;
        $session['game']['teams'][$teamId] = $t;
        return ['session' => $session, 'response' => ['ok' => true, 'message' => tr('Kterýkoli spoluhráč teď může pomoct – tým dostal +{s} s.', ['s' => TG58_RELAY_HELP_PENALTY_S])]];
    }
    if ($action === 'relay_answer') {
        if ((string)$session['line'] !== 'graphics') throw new RuntimeException(tr('Tahle štafeta se odpovídá v terminálu Labu.'));
        if ($t['finished_at'] !== null) throw new RuntimeException(tr('Tenhle tým už doběhl.'));
        $itemId = $t['quiz_ids'][$t['leg'] - 1] ?? null;
        $item = $itemId !== null ? tg58_quiz_find($itemId) : null;
        if ($item === null) throw new RuntimeException(tr('Otázka se nenašla.'));
        tg58_wrong_guard($session, (string)$ctx['student_key'], $now);
        $correct = tg58_quiz_check($item, $payload['answer'] ?? null);
        $session = $correct ? tg58_relay_advance_team($session, $teamId, (int)$t['leg'], $now) : tg58_wrong_record($session, (string)$ctx['student_key'], $now);
        return ['session' => $session, 'response' => ['ok' => true, 'correct' => $correct]];
    }
    throw new RuntimeException(tr('Neznámá akce štafety.'));
}

function tg58_relay_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    if ($action !== 'relay_skip') throw new RuntimeException('Neznámá akce štafety.');
    $teamId = (string)($payload['team_id'] ?? '');
    if (!isset($session['game']['teams'][$teamId])) throw new RuntimeException('Tým nebyl nalezen.');
    $session = tg58_relay_advance_team($session, $teamId, (int)$session['game']['teams'][$teamId]['leg'], (int)$ctx['now']);
    return ['session' => $session, 'response' => ['ok' => true]];
}

function tg58_relay_teacher_view(array $session, array $ctx): array
{
    $now = (int)$ctx['now'];
    $rows = [];
    foreach ((array)$session['teams'] as $team) {
        $view = tg58_relay_team_view($session, (string)$team['id'], $now);
        $t = $session['game']['teams'][$team['id']] ?? [];
        $started = tg58_ts($session['started_at'] ?? null) ?? $now;
        $finishSecs = $t['finished_at'] !== null ? (tg58_ts($t['finished_at']) - $started) : null;
        $rows[] = ['team' => $team['name'], 'leg' => $view['leg'], 'legs_total' => $view['legs_total'], 'finished' => $view['finished'], 'time_s' => $finishSecs !== null ? $finishSecs + (int)($t['penalty_s'] ?? 0) : null, 'penalty_s' => (int)($t['penalty_s'] ?? 0)];
    }
    usort($rows, static fn(array $a, array $b): int => ((int)$b['finished'] <=> (int)$a['finished']) ?: (($a['time_s'] ?? PHP_INT_MAX) <=> ($b['time_s'] ?? PHP_INT_MAX)) ?: ($b['leg'] <=> $a['leg']));
    return ['rows' => $rows];
}

function tg58_relay_projector_view(array $session, array $ctx): array
{
    $out = tg58_relay_teacher_view($session, $ctx);
    foreach ($out['rows'] as &$row) unset($row['time_s'], $row['penalty_s']);
    return $out;
}

function tg58_relay_summary(array $session): array
{
    return ['legs' => (int)($session['game']['legs'] ?? 0), 'finished_teams' => count((array)($session['game']['winners'] ?? []))];
}

tg58_register_game('relay', [
    'label' => tr('Štafeta'), 'default_duration_min' => 25,
    'default_settings' => 'tg58_relay_default_settings', 'parse_settings' => 'tg58_relay_parse_settings', 'on_start' => 'tg58_relay_on_start',
    'student_view' => 'tg58_relay_student_view', 'student_action' => 'tg58_relay_student_action',
    'teacher_view' => 'tg58_relay_teacher_view', 'teacher_action' => 'tg58_relay_teacher_action', 'projector_view' => 'tg58_relay_projector_view',
    'summary' => 'tg58_relay_summary',
]);
