<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Riskuj! (kvízová tabule 5×5, projekční pohled).
 *
 * Týmy vybírají pole střídavě, ale odpovídají VŠICHNI žáci na svých zařízeních – tým dostane
 * hodnota × podíl správných členů (nikdy záporně), takže žádný tým nesedí bez práce, když je řada
 * na jiném. Správná odpověď se klientovi nepošle, dokud se okno neuzavře (tg58_settle/tick); pozdní
 * odpověď (po uzavření) se odmítne. Bez negativních bodů, žádné veřejné jmenování špatných odpovědí.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';

function tg58_jeopardy_default_settings(): array
{
    return ['size' => 5, 'window_s' => 35];
}

function tg58_jeopardy_parse_settings(array $in, array $ctx): array
{
    $size = (int)($in['size'] ?? 5);
    if (!in_array($size, [4, 5], true)) throw new RuntimeException('Tabule Riskuj! je 4×4 nebo 5×5.');
    $window = (int)($in['window_s'] ?? 35);
    if ($window < 20 || $window > 60) throw new RuntimeException('Okno na odpověď musí být 20–60 s.');
    return ['size' => $size, 'window_s' => $window];
}

function tg58_jeopardy_on_start(array $session, array $ctx): array
{
    $size = (int)$session['settings']['size'];
    $board = tg58_quiz_board((string)$session['line'], (string)$session['class_id'], (string)$session['id'], $size);
    $teamIds = array_map(static fn(array $t): string => (string)$t['id'], (array)$ctx['teams']);
    $order = tg58_seeded_shuffle($teamIds, (string)$session['id'] . '|turn');
    $session['game'] = ['board' => $board, 'turn_order' => $order, 'turn_index' => 0, 'used' => [], 'current' => null, 'scores' => array_fill_keys($teamIds, 0), 'history' => []];
    return $session;
}

function tg58_jeopardy_turn_team(array $session): ?string
{
    $order = (array)($session['game']['turn_order'] ?? []);
    return $order[($session['game']['turn_index'] ?? 0) % max(1, count($order))] ?? null;
}

/** Uzavře vypršelé kolo: připočte tým. body = hodnota × (počet správných / velikost týmu), zaokrouhleno. */
function tg58_jeopardy_tick(array $session, int $now): array
{
    $g = $session['game'];
    $cur = $g['current'] ?? null;
    if ($cur === null || $now < (int)$cur['closes_at']) return $session;
    $sizes = [];
    foreach ((array)$session['teams'] as $t) $sizes[(string)$t['id']] = max(1, count((array)$t['members']));
    $correct = array_fill_keys(array_keys($sizes), 0);
    foreach ((array)$cur['answers'] as $a) { if (!empty($a['correct']) && isset($correct[$a['team']])) $correct[$a['team']]++; }
    foreach ($sizes as $teamId => $size) {
        $gain = (int)round((int)$cur['value'] * ($correct[$teamId] / $size));
        $g['scores'][$teamId] = (int)($g['scores'][$teamId] ?? 0) + $gain;
    }
    $g['history'][] = ['category' => (string)$g['board'][$cur['cat']]['category'], 'value' => (int)$cur['value'], 'item_id' => (string)$cur['item_id'], 'at' => tg58_iso($now)];
    $g['turn_index'] = (int)($g['turn_index'] + 1) % max(1, count((array)$g['turn_order']));
    $g['current'] = null;
    $session['game'] = $g;
    return $session;
}

function tg58_jeopardy_public_scores(array $session): array
{
    $out = [];
    foreach ((array)$session['game']['scores'] as $teamId => $pts) $out[] = ['team' => tg58_team_label($session, (string)$teamId), 'points' => (int)$pts];
    usort($out, static fn(array $a, array $b): int => $b['points'] <=> $a['points']);
    return $out;
}

function tg58_jeopardy_board_view(array $session): array
{
    $used = (array)$session['game']['used'];
    $out = [];
    foreach ((array)$session['game']['board'] as $ci => $cat) {
        $cells = [];
        foreach ((array)$cat['cells'] as $vi => $cell) $cells[] = ['cat' => $ci, 'cell' => $vi, 'value' => (int)$cell['value'], 'used' => in_array($ci . '|' . $vi, $used, true)];
        $out[] = ['category' => (string)$cat['category'], 'cells' => $cells];
    }
    return $out;
}

function tg58_jeopardy_current_public(array $session, ?string $studentKey): ?array
{
    $cur = $session['game']['current'] ?? null;
    if ($cur === null) return null;
    $item = tg58_quiz_find((string)$cur['item_id']);
    if ($item === null) return null;
    $public = tg58_quiz_public($item, (string)$session['id'] . '|jeop|' . $cur['item_id']);
    return $public + ['value' => (int)$cur['value'], 'closes_at' => tg58_iso((int)$cur['closes_at']), 'picked_by' => tg58_team_label($session, (string)$cur['picked_by_team']), 'answered' => $studentKey !== null && isset($cur['answers'][$studentKey])];
}

function tg58_jeopardy_last_public(array $session): ?array
{
    $history = (array)$session['game']['history'];
    if ($history === []) return null;
    $last = end($history);
    $item = tg58_quiz_find((string)$last['item_id']);
    return ['category' => (string)$last['category'], 'value' => (int)$last['value'], 'answer' => $item['answer'] ?? null, 'explain' => $item['explain'] ?? ''];
}

function tg58_jeopardy_student_view(array $session, array $ctx): array
{
    $turnTeam = tg58_jeopardy_turn_team($session);
    return [
        'board' => tg58_jeopardy_board_view($session), 'turn_team' => $turnTeam !== null ? tg58_team_label($session, $turnTeam) : null,
        'my_turn' => $turnTeam !== null && $turnTeam === $ctx['team_id'], 'scores' => tg58_jeopardy_public_scores($session), 'window_s' => (int)$session['settings']['window_s'],
        'question' => tg58_jeopardy_current_public($session, (string)$ctx['student_key']), 'last' => tg58_jeopardy_last_public($session),
    ];
}

function tg58_jeopardy_student_action(string $action, array $session, array $ctx, array $payload): array
{
    if ($action === 'jeopardy_pick') {
        if ($session['game']['current'] !== null) throw new RuntimeException(tr('Otázka právě běží – nejdřív se musí uzavřít.'));
        if (tg58_jeopardy_turn_team($session) !== $ctx['team_id']) throw new RuntimeException(tr('Teď je na tahu jiný tým.'));
        $ci = (int)($payload['cat'] ?? -1);
        $vi = (int)($payload['cell'] ?? -1);
        $cat = $session['game']['board'][$ci] ?? null;
        $cell = $cat['cells'][$vi] ?? null;
        if ($cat === null || $cell === null) throw new RuntimeException(tr('Neplatné pole tabule.'));
        if (in_array($ci . '|' . $vi, (array)$session['game']['used'], true)) throw new RuntimeException(tr('Tohle pole je už zahrané.'));
        if ($cell['item_id'] === null) throw new RuntimeException(tr('Pro tohle pole chybí otázka.'));
        $session['game']['used'][] = $ci . '|' . $vi;
        $session['game']['current'] = ['cat' => $ci, 'cell' => $vi, 'item_id' => $cell['item_id'], 'value' => (int)$cell['value'], 'picked_by_team' => $ctx['team_id'], 'opens_at' => tg58_iso((int)$ctx['now']), 'closes_at' => (int)$ctx['now'] + (int)$session['settings']['window_s'], 'answers' => []];
        return ['session' => $session, 'response' => ['ok' => true]];
    }
    if ($action === 'jeopardy_answer') {
        $cur = $session['game']['current'] ?? null;
        if ($cur === null) throw new RuntimeException(tr('Teď neběží žádná otázka.'));
        if ((int)$ctx['now'] >= (int)$cur['closes_at']) throw new RuntimeException(tr('Čas na odpověď vypršel.'));
        if (isset($cur['answers'][$ctx['student_key']])) throw new RuntimeException(tr('Na tuhle otázku už jsi odpověděl(a).'));
        $item = tg58_quiz_find((string)$cur['item_id']);
        if ($item === null) throw new RuntimeException(tr('Otázka se nenašla.'));
        $correct = tg58_quiz_check($item, $payload['answer'] ?? null);
        $cur['answers'][(string)$ctx['student_key']] = ['team' => (string)$ctx['team_id'], 'correct' => $correct, 'at' => tg58_iso((int)$ctx['now'])];
        $session['game']['current'] = $cur;
        return ['session' => $session, 'response' => ['ok' => true, 'recorded' => true]];
    }
    throw new RuntimeException(tr('Neznámá akce Riskuj!.'));
}

function tg58_jeopardy_teacher_view(array $session, array $ctx): array
{
    $turnTeam = tg58_jeopardy_turn_team($session);
    return ['board' => tg58_jeopardy_board_view($session), 'turn_team' => $turnTeam !== null ? tg58_team_label($session, $turnTeam) : null, 'scores' => tg58_jeopardy_public_scores($session), 'question' => tg58_jeopardy_current_public($session, null), 'answers_in' => $session['game']['current'] !== null ? count((array)$session['game']['current']['answers']) : 0, 'last' => tg58_jeopardy_last_public($session)];
}

function tg58_jeopardy_projector_view(array $session, array $ctx): array
{
    return tg58_jeopardy_teacher_view($session, $ctx);
}

function tg58_jeopardy_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    if ($action !== 'jeopardy_extend') throw new RuntimeException('Neznámá akce Riskuj!.');
    if ($session['game']['current'] === null) throw new RuntimeException('Teď neběží žádná otázka.');
    $session['game']['current']['closes_at'] = (int)$session['game']['current']['closes_at'] + 15;
    return ['session' => $session, 'response' => ['ok' => true]];
}

function tg58_jeopardy_winners(array $session): array
{
    $scores = (array)($session['game']['scores'] ?? []);
    if ($scores === []) return [];
    $max = max($scores);
    return array_keys(array_filter($scores, static fn(int $p): bool => $p === $max && $p > 0));
}

function tg58_jeopardy_summary(array $session): array
{
    return ['played' => count((array)($session['game']['history'] ?? [])), 'cells_total' => (int)$session['settings']['size'] ** 2];
}

tg58_register_game('jeopardy', [
    'label' => tr('Riskuj!'), 'default_duration_min' => 30,
    'default_settings' => 'tg58_jeopardy_default_settings', 'parse_settings' => 'tg58_jeopardy_parse_settings', 'on_start' => 'tg58_jeopardy_on_start',
    'student_view' => 'tg58_jeopardy_student_view', 'student_action' => 'tg58_jeopardy_student_action',
    'teacher_view' => 'tg58_jeopardy_teacher_view', 'teacher_action' => 'tg58_jeopardy_teacher_action', 'projector_view' => 'tg58_jeopardy_projector_view',
    'tick' => 'tg58_jeopardy_tick', 'winners' => 'tg58_jeopardy_winners', 'summary' => 'tg58_jeopardy_summary',
]);
