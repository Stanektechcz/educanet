<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Přetahovaná.
 *
 * Celá třída ve dvou týmech; každý žák má vlastní proud krátkých otázek (žádné čekání na spolužáky).
 * Správná odpověď posune lano o 1 / počet aktivních členů týmu – menší tým tak není znevýhodněný.
 * Obtížnost se přizpůsobuje žákovi (server, ne klient). Limit 1 odpověď / 4 s brání klikací spamu.
 * 3 kola, lano se na začátku kola vynuluje. Žádný individuální žebříček – jde jen o tým, aby nikdo
 * neměl pocit, že ho spolužáci vidí „pomalého“.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';

const TG58_TUG_ANSWER_COOLDOWN_S = 4;
const TG58_TUG_RECENT_MAX = 6;

function tg58_tug_default_settings(): array
{
    return ['rounds' => 3];
}

function tg58_tug_parse_settings(array $in, array $ctx): array
{
    $rounds = (int)($in['rounds'] ?? 3);
    if ($rounds < 1 || $rounds > 5) throw new RuntimeException('Přetahovaná má mít 1 až 5 kol.');
    return ['rounds' => $rounds];
}

function tg58_tug_on_start(array $session, array $ctx): array
{
    $teamIds = array_map(static fn(array $t): string => (string)$t['id'], (array)$ctx['teams']);
    $side = [];
    foreach (array_values($teamIds) as $i => $tid) $side[$tid] = $i === 0 ? -1 : 1;
    $session['game'] = ['rounds' => (int)$session['settings']['rounds'], 'round_index' => 0, 'position' => 0.0, 'side' => $side, 'active' => array_fill_keys($teamIds, []), 'rounds_history' => [], 'students' => []];
    return $session;
}

function tg58_tug_round_len_s(array $session): int
{
    $duration = (int)$session['duration_s'];
    $rounds = max(1, (int)$session['game']['rounds']);
    return $duration > 0 ? max(30, intdiv($duration, $rounds)) : 0;
}

/** Uzavře kolo, jakmile uplyne jeho časový úsek (jen když má hra pevnou délku) – nuluje lano na další kolo. */
function tg58_tug_tick(array $session, int $now): array
{
    $roundLen = tg58_tug_round_len_s($session);
    if ($roundLen <= 0) return $session;
    $elapsed = tg58_elapsed($session, $now);
    $target = min((int)$session['game']['rounds'] - 1, intdiv($elapsed, $roundLen));
    if ($target <= (int)$session['game']['round_index']) return $session;
    $pos = (float)$session['game']['position'];
    $winnerSide = $pos > 0.001 ? 1 : ($pos < -0.001 ? -1 : 0);
    $session['game']['rounds_history'][] = ['round' => (int)$session['game']['round_index'] + 1, 'winner_side' => $winnerSide, 'position' => $pos];
    $session['game']['round_index'] = $target;
    $session['game']['position'] = 0.0;
    return $session;
}

function tg58_tug_active_count(array $session, string $teamId): int
{
    return max(1, count((array)($session['game']['active'][$teamId] ?? [])));
}

function tg58_tug_pick_question(array $session, int $level, array $recent): ?array
{
    $items = tg58_quiz_select((string)$session['line'], (string)$session['class_id'], 4, (string)$session['id'] . '|tug|' . $level . '|' . count($recent), ['difficulty' => $level, 'exclude' => $recent]);
    return $items[0] ?? (tg58_quiz_select((string)$session['line'], (string)$session['class_id'], 1, (string)$session['id'] . '|tug-any|' . random_int(0, 999999))[0] ?? null);
}

function tg58_tug_student_row(array $session, string $studentKey, string $teamId): array
{
    return (array)($session['game']['students'][$studentKey] ?? ['team' => $teamId, 'level' => 1, 'current_item_id' => null, 'asked_at' => null, 'last_answer_at' => 0, 'recent' => [], 'correct' => 0, 'wrong' => 0]);
}

function tg58_tug_student_view(array $session, array $ctx): array
{
    $teamId = (string)$ctx['team_id'];
    $row = tg58_tug_student_row($session, (string)$ctx['student_key'], $teamId);
    $question = null;
    if ($row['current_item_id'] !== null) {
        $item = tg58_quiz_find((string)$row['current_item_id']);
        if ($item !== null) $question = tg58_quiz_public($item, (string)$session['id'] . '|tug|' . $ctx['student_key'] . '|' . $row['current_item_id']);
    }
    $roundLen = tg58_tug_round_len_s($session);
    return [
        'round' => (int)$session['game']['round_index'] + 1, 'rounds_total' => (int)$session['game']['rounds'],
        'position' => (float)$session['game']['position'], 'my_side' => (int)($session['game']['side'][$teamId] ?? 0),
        'round_remaining' => $roundLen > 0 ? max(0, $roundLen - (tg58_elapsed($session, (int)$ctx['now']) % $roundLen)) : null,
        'rounds_won' => tg58_tug_rounds_won($session), 'question' => $question,
        'can_answer' => (int)$ctx['now'] - (int)($row['last_answer_at'] ?? 0) >= TG58_TUG_ANSWER_COOLDOWN_S,
    ];
}

function tg58_tug_rounds_won(array $session): array
{
    $wins = [];
    foreach (array_keys((array)$session['game']['side']) as $teamId) $wins[$teamId] = 0;
    foreach ((array)$session['game']['rounds_history'] as $r) {
        foreach ((array)$session['game']['side'] as $teamId => $side) { if ($side === $r['winner_side']) $wins[$teamId] = ($wins[$teamId] ?? 0) + 1; }
    }
    $out = [];
    foreach ($wins as $teamId => $n) $out[] = ['team' => tg58_team_label($session, (string)$teamId), 'rounds_won' => $n];
    return $out;
}

function tg58_tug_student_action(string $action, array $session, array $ctx, array $payload): array
{
    $teamId = (string)$ctx['team_id'];
    $studentKey = (string)$ctx['student_key'];
    $row = tg58_tug_student_row($session, $studentKey, $teamId);
    if ($action === 'tug_next') {
        if ($row['current_item_id'] === null) {
            $item = tg58_tug_pick_question($session, (int)$row['level'], (array)$row['recent']);
            if ($item !== null) { $row['current_item_id'] = $item['id']; $row['asked_at'] = tg58_iso((int)$ctx['now']); }
            $session['game']['students'][$studentKey] = $row;
        }
        return ['session' => $session, 'response' => ['ok' => true]];
    }
    if ($action === 'tug_answer') {
        if ($row['current_item_id'] === null) throw new RuntimeException(tr('Teď na tebe nečeká žádná otázka.'));
        if ((int)$ctx['now'] - (int)($row['last_answer_at'] ?? 0) < TG58_TUG_ANSWER_COOLDOWN_S) throw new RuntimeException(tr('Tak rychle to nejde – počkej pár vteřin.'));
        $item = tg58_quiz_find((string)$row['current_item_id']);
        if ($item === null) throw new RuntimeException(tr('Otázka se nenašla.'));
        $correct = tg58_quiz_check($item, $payload['answer'] ?? null);
        $row['last_answer_at'] = (int)$ctx['now'];
        $row['level'] = max(1, min(3, (int)$row['level'] + ($correct ? 1 : -1)));
        $row[$correct ? 'correct' : 'wrong'] = (int)$row[$correct ? 'correct' : 'wrong'] + 1;
        $row['recent'] = array_slice(array_merge((array)$row['recent'], [$item['id']]), -TG58_TUG_RECENT_MAX);
        $row['current_item_id'] = null;
        $session['game']['students'][$studentKey] = $row;
        $active = (array)$session['game']['active'][$teamId];
        if (!in_array($studentKey, $active, true)) { $active[] = $studentKey; $session['game']['active'][$teamId] = $active; }
        if ($correct) {
            $pull = 1 / tg58_tug_active_count($session, $teamId);
            $side = (int)($session['game']['side'][$teamId] ?? 0);
            $session['game']['position'] = (float)$session['game']['position'] + $pull * $side;
        }
        return ['session' => $session, 'response' => ['ok' => true, 'correct' => $correct, 'explain' => $item['explain']]];
    }
    throw new RuntimeException(tr('Neznámá akce přetahované.'));
}

function tg58_tug_teacher_view(array $session, array $ctx): array
{
    $counts = [];
    foreach ((array)$session['teams'] as $t) $counts[$t['id']] = ['team' => $t['name'], 'active' => tg58_tug_active_count($session, (string)$t['id']), 'size' => count((array)$t['members'])];
    return ['round' => (int)$session['game']['round_index'] + 1, 'rounds_total' => (int)$session['game']['rounds'], 'position' => (float)$session['game']['position'], 'sides' => (array)$session['game']['side'], 'rounds_won' => tg58_tug_rounds_won($session), 'teams' => array_values($counts)];
}

function tg58_tug_projector_view(array $session, array $ctx): array
{
    $v = tg58_tug_teacher_view($session, $ctx);
    unset($v['teams']);
    return $v;
}

function tg58_tug_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    throw new RuntimeException('Přetahovaná nemá další učitelské akce.');
}

function tg58_tug_winners(array $session): array
{
    $wins = [];
    foreach (tg58_tug_rounds_won($session) as $row) $wins[] = $row['rounds_won'];
    $max = $wins === [] ? 0 : max($wins);
    $out = [];
    foreach ((array)$session['game']['side'] as $teamId => $side) {
        $label = tg58_team_label($session, (string)$teamId);
        foreach (tg58_tug_rounds_won($session) as $row) { if ($row['team'] === $label && $row['rounds_won'] === $max && $max > 0) $out[] = (string)$teamId; }
    }
    return array_values(array_unique($out));
}

function tg58_tug_summary(array $session): array
{
    return ['rounds' => (int)($session['game']['rounds'] ?? 0)];
}

tg58_register_game('tug', [
    'label' => tr('Přetahovaná'), 'default_duration_min' => 15, 'fixed_team_count' => 2,
    'default_settings' => 'tg58_tug_default_settings', 'parse_settings' => 'tg58_tug_parse_settings', 'on_start' => 'tg58_tug_on_start',
    'student_view' => 'tg58_tug_student_view', 'student_action' => 'tg58_tug_student_action',
    'teacher_view' => 'tg58_tug_teacher_view', 'teacher_action' => 'tg58_tug_teacher_action', 'projector_view' => 'tg58_tug_projector_view',
    'tick' => 'tg58_tug_tick', 'winners' => 'tg58_tug_winners', 'summary' => 'tg58_tug_summary',
]);
