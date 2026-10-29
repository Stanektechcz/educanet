<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Příkazové bingo.
 *
 * Karta 4×4 (5×5 volitelně) s mikroúlohami – kvízové otázky + (jen linie sítě) pár políček s úlohou
 * Linux Labu. Každý tým má STEJNÉ úlohy, ale jiné rozložení na kartě (zamícháno podle id hry+týmu),
 * takže spolužáci ze sousedního týmu si nemůžou jen tak „opsat souřadnice“. Označení políčka platí
 * jen po ověření na serveru (nikdy podle kliku samotného). Řada (celý řádek karty) dá bonus, ale jen
 * pokud na kartě přispěl aspoň jedním políčkem každý člen týmu – aby jednu kartu nevyplnil jen jeden žák.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';

const TG58_BINGO_ROW_BONUS = 3;

function tg58_bingo_default_settings(): array
{
    return ['size' => 4];
}

function tg58_bingo_parse_settings(array $in, array $ctx): array
{
    $size = (int)($in['size'] ?? 4);
    if (!in_array($size, [4, 5], true)) throw new RuntimeException('Bingo karta je 4×4 nebo 5×5.');
    return ['size' => $size];
}

/** Společný seznam políček karty (stejný pro všechny týmy) – logický index 0..N²-1. */
function tg58_bingo_cells(array $session): array
{
    $size = (int)$session['settings']['size'];
    $total = $size * $size;
    $centerIndex = $size === 5 ? 12 : null;
    $labIds = (string)$session['line'] === 'networks' ? tg58_bingo_net_cell_ids() : [];
    $quizCount = $total - count($labIds) - ($centerIndex !== null ? 1 : 0);
    $items = tg58_quiz_select((string)$session['line'], (string)$session['class_id'], $quizCount, (string)$session['id'] . '|bingo|cells');
    $cells = [];
    $qi = 0;
    $li = 0;
    for ($i = 0; $i < $total; $i++) {
        if ($i === $centerIndex) { $cells[] = ['id' => $i, 'kind' => 'free', 'ref' => null, 'label' => tr('Zdarma')]; continue; }
        if ($li < count($labIds) && ($i % 4) === 1) { $cells[] = ['id' => $i, 'kind' => 'lab', 'ref' => $labIds[$li], 'label' => tr('Terminál')]; $li++; continue; }
        $item = $items[$qi] ?? null;
        $qi++;
        $cells[] = $item !== null ? ['id' => $i, 'kind' => 'quiz', 'ref' => $item['id'], 'label' => $item['category']] : ['id' => $i, 'kind' => 'free', 'ref' => null, 'label' => tr('Zdarma')];
    }
    // Doplní zbylé laboratorní buňky, pokud předchozí smyčka nenašla dost volných pozic (malá karta).
    if ($li < count($labIds)) {
        foreach ($cells as $k => $c) {
            if ($li >= count($labIds)) break;
            if ($c['kind'] === 'quiz') { $cells[$k] = ['id' => $c['id'], 'kind' => 'lab', 'ref' => $labIds[$li], 'label' => tr('Terminál')]; $li++; }
        }
    }
    return $cells;
}

function tg58_bingo_on_start(array $session, array $ctx): array
{
    $cells = tg58_bingo_cells($session);
    $size = (int)$session['settings']['size'];
    $teams = [];
    foreach ((array)$ctx['teams'] as $team) {
        $layout = tg58_seeded_shuffle(array_column($cells, 'id'), (string)$session['id'] . '|layout|' . $team['id']);
        $solved = [];
        foreach ($cells as $c) { if ($c['kind'] === 'free') $solved[(string)$c['id']] = ['student_key' => '', 'at' => tg58_iso((int)$ctx['now'])]; }
        $teams[(string)$team['id']] = ['layout' => $layout, 'solved' => $solved];
    }
    $session['game'] = ['size' => $size, 'cells' => $cells, 'teams' => $teams, 'winners' => []];
    return $session;
}

function tg58_bingo_cell(array $session, int $id): ?array
{
    foreach ((array)$session['game']['cells'] as $c) { if ((int)$c['id'] === $id) return $c; }
    return null;
}

/** Řádky karty (podle rozložení týmu) a jejich stav; bonus jen když každý člen týmu má aspoň 1 vyřešené políčko. */
function tg58_bingo_team_rows(array $session, string $teamId): array
{
    $size = (int)$session['game']['size'];
    $t = $session['game']['teams'][$teamId] ?? ['layout' => [], 'solved' => []];
    $layout = (array)$t['layout'];
    $solved = (array)$t['solved'];
    $members = array_map('strval', (array)(tg58_team($session, $teamId)['members'] ?? []));
    $contributors = array_unique(array_column($solved, 'student_key'));
    $everyoneContributed = $members !== [] && count(array_diff($members, $contributors)) === 0;
    $rows = 0;
    for ($r = 0; $r < $size; $r++) {
        $ids = array_slice($layout, $r * $size, $size);
        if ($ids !== [] && count(array_diff($ids, array_map('intval', array_keys($solved)))) === 0) $rows++;
    }
    return ['rows_complete' => $rows, 'bonus_rows' => $everyoneContributed ? $rows : 0, 'everyone_contributed' => $everyoneContributed];
}

function tg58_bingo_team_points(array $session, string $teamId): int
{
    $t = $session['game']['teams'][$teamId] ?? ['solved' => []];
    $solved = count((array)$t['solved']);
    return $solved + tg58_bingo_team_rows($session, $teamId)['bonus_rows'] * TG58_BINGO_ROW_BONUS;
}

function tg58_bingo_student_view(array $session, array $ctx): array
{
    $teamId = (string)$ctx['team_id'];
    $t = $session['game']['teams'][$teamId] ?? ['layout' => [], 'solved' => []];
    $mode = (string)$session['names'];
    $roster = tg58_roster((string)$session['class_id']);
    $seed = (string)$session['id'] . '|' . $teamId . '|' . (string)$ctx['student_key'];
    $out = [];
    foreach ((array)$t['layout'] as $pos => $cellId) {
        $cell = tg58_bingo_cell($session, (int)$cellId);
        if ($cell === null) continue;
        $solvedRow = $t['solved'][(string)$cellId] ?? null;
        $row = ['pos' => $pos, 'id' => (int)$cellId, 'kind' => $cell['kind'], 'label' => $cell['label'], 'solved' => $solvedRow !== null];
        if ($solvedRow !== null && $solvedRow['student_key'] !== '' && $mode !== 'anon') $row['solved_by'] = tg58_public_name($mode, (string)$solvedRow['student_key'], tg58_label((string)$solvedRow['student_key'], '', $roster), (string)$ctx['student_key'], 0);
        if ($solvedRow === null && $cell['kind'] === 'quiz') { $item = tg58_quiz_find((string)$cell['ref']); if ($item !== null) $row['question'] = tg58_quiz_public($item, $seed . '|' . $cellId); }
        if ($solvedRow === null && $cell['kind'] === 'lab') $row['level_id'] = $cell['ref'];
        $out[] = $row;
    }
    $rows = tg58_bingo_team_rows($session, $teamId);
    return ['size' => (int)$session['game']['size'], 'cells' => $out, 'points' => tg58_bingo_team_points($session, $teamId)] + $rows;
}

function tg58_bingo_student_action(string $action, array $session, array $ctx, array $payload): array
{
    if ($action !== 'bingo_mark') throw new RuntimeException(tr('Neznámá akce bingo.'));
    $teamId = (string)$ctx['team_id'];
    $cellId = (int)($payload['id'] ?? -1);
    $t = $session['game']['teams'][$teamId] ?? null;
    if ($t === null || !in_array($cellId, array_map('intval', (array)$t['layout']), true)) throw new RuntimeException(tr('Tohle políčko na tvé kartě není.'));
    if (isset($t['solved'][(string)$cellId])) return ['session' => $session, 'response' => ['ok' => true, 'already' => true]];
    $cell = tg58_bingo_cell($session, $cellId);
    if ($cell === null) throw new RuntimeException(tr('Políčko nebylo nalezeno.'));
    $correct = false;
    if ($cell['kind'] === 'quiz') {
        $item = tg58_quiz_find((string)$cell['ref']);
        tg58_wrong_guard($session, (string)$ctx['student_key'], (int)$ctx['now']);
        $correct = $item !== null && tg58_quiz_check($item, $payload['answer'] ?? null);
        if (!$correct) $session = tg58_wrong_record($session, (string)$ctx['student_key'], (int)$ctx['now']);
    } elseif ($cell['kind'] === 'lab') {
        $classId = (string)$session['class_id'];
        $studentKey = (string)$ctx['student_key'];
        $correct = function_exists('lab57_solved') && isset(lab57_solved($classId, $studentKey, 'tg:' . (string)$session['id'])[(string)$cell['ref']]);
    }
    if (!$correct) return ['session' => $session, 'response' => ['ok' => true, 'correct' => false]];
    $session['game']['teams'][$teamId]['solved'][(string)$cellId] = ['student_key' => (string)$ctx['student_key'], 'at' => tg58_iso((int)$ctx['now'])];
    return ['session' => $session, 'response' => ['ok' => true, 'correct' => true]];
}

function tg58_bingo_teacher_view(array $session, array $ctx): array
{
    $rows = [];
    foreach ((array)$session['teams'] as $team) {
        $id = (string)$team['id'];
        $rowInfo = tg58_bingo_team_rows($session, $id);
        $rows[] = ['team' => $team['name'], 'points' => tg58_bingo_team_points($session, $id), 'solved' => count((array)($session['game']['teams'][$id]['solved'] ?? [])), 'cells_total' => (int)$session['game']['size'] ** 2] + $rowInfo;
    }
    usort($rows, static fn(array $a, array $b): int => $b['points'] <=> $a['points']);
    return ['rows' => $rows];
}

function tg58_bingo_projector_view(array $session, array $ctx): array
{
    return tg58_bingo_teacher_view($session, $ctx);
}

function tg58_bingo_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    throw new RuntimeException('Bingo nemá další učitelské akce.');
}

function tg58_bingo_summary(array $session): array
{
    $best = 0;
    foreach (array_keys((array)($session['game']['teams'] ?? [])) as $id) $best = max($best, tg58_bingo_team_points($session, (string)$id));
    return ['size' => (int)($session['game']['size'] ?? 0), 'best_points' => $best];
}

tg58_register_game('bingo', [
    'label' => tr('Příkazové bingo'), 'default_duration_min' => 20,
    'default_settings' => 'tg58_bingo_default_settings', 'parse_settings' => 'tg58_bingo_parse_settings', 'on_start' => 'tg58_bingo_on_start',
    'student_view' => 'tg58_bingo_student_view', 'student_action' => 'tg58_bingo_student_action',
    'teacher_view' => 'tg58_bingo_teacher_view', 'teacher_action' => 'tg58_bingo_teacher_action', 'projector_view' => 'tg58_bingo_projector_view',
    'summary' => 'tg58_bingo_summary',
]);
