<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Správci sítě (linie sítě) / Správci webu (linie grafika).
 *
 * Mapa 16–24 uzlů se závadou. Linie sítě: závada je mikroúloha Linux Labu (vlastní kopie světa na žáka,
 * viz linux_v58_levels_tg.php). Linie grafika: závada je otázka „rozbitá stránka“ z kvízové banky.
 * První tým, který uzel opraví, ho spravuje – uzel se týmu nedá vzít; když ho opraví soupeř, dostane
 * jen malý bonus „za učení“ (žádné odebírání bodů, žádné trestání). Souběh řeší atomický zápis
 * (tg58_mutate) – i když uzel řeší 10 žáků najednou, vlastníkem se stane přesně jeden tým.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';

const TG58_NETADMIN_POINTS = 10;
const TG58_NETADMIN_REGION_BONUS = 2;
const TG58_NETADMIN_LEARN_BONUS = 5;
const TG58_NETADMIN_WAVES = 4;
const TG58_NETADMIN_GRID_COLS = 5;
const TG58_NETADMIN_RESERVE_S = 360;

function tg58_netadmin_default_settings(): array
{
    return ['node_count' => 20, 'wave_minutes' => 5];
}

function tg58_netadmin_parse_settings(array $in, array $ctx): array
{
    $count = (int)($in['node_count'] ?? 20);
    if ($count < 16 || $count > 24) throw new RuntimeException('Mapa má mít 16 až 24 uzlů.');
    $wave = (int)($in['wave_minutes'] ?? 5);
    if ($wave < 3 || $wave > 15) throw new RuntimeException('Vlna nových závad musí být 3–15 minut.');
    return ['node_count' => $count, 'wave_minutes' => $wave];
}

function tg58_netadmin_node_ids(array $session): array
{
    $count = (int)$session['settings']['node_count'];
    if ((string)$session['line'] === 'networks') return tg58_netadmin_net_node_ids($count);
    $ids = [];
    for ($n = 1; $n <= $count; $n++) $ids[] = 'tg-netadmin-gfx-' . $n;
    return $ids;
}

function tg58_netadmin_on_start(array $session, array $ctx): array
{
    $nodeIds = tg58_netadmin_node_ids($session);
    $waveN = min(TG58_NETADMIN_WAVES, count($nodeIds));
    $wave = [];
    $quiz = [];
    if ((string)$session['line'] !== 'networks') {
        $items = tg58_quiz_select('graphics', (string)$session['class_id'], count($nodeIds), (string)$session['id'] . '|netadmin');
        foreach ($nodeIds as $i => $id) $quiz[$id] = $items[$i % max(1, count($items))]['id'] ?? null;
    }
    foreach ($nodeIds as $i => $id) $wave[$id] = 1 + ($i % $waveN);
    $session['game'] = ['node_ids' => $nodeIds, 'node_wave' => $wave, 'node_quiz' => $quiz, 'nodes' => [], 'winners' => []];
    return $session;
}

function tg58_netadmin_visible_wave(array $session, int $now): int
{
    $waveLen = max(60, (int)$session['settings']['wave_minutes'] * 60);
    return 1 + intdiv(tg58_elapsed($session, $now), $waveLen);
}

function tg58_netadmin_node_row(array $session, string $nodeId): array
{
    return (array)($session['game']['nodes'][$nodeId] ?? ['owner' => null, 'fixed_by' => null, 'fixed_at' => null, 'learn_by' => [], 'reserved_team' => null, 'reserved_until' => 0]);
}

/** Atomicky nastaví vlastníka (jen pokud je uzel volný) nebo připočte bonus „za učení“ (uzel nikomu nebere). */
function tg58_netadmin_claim(array $session, string $teamId, string $studentKey, string $nodeId, int $now): array
{
    $node = tg58_netadmin_node_row($session, $nodeId);
    if ($node['owner'] === null) {
        $node['owner'] = $teamId;
        $node['fixed_by'] = $studentKey;
        $node['fixed_at'] = tg58_iso($now);
    } elseif ($node['owner'] !== $teamId && !in_array($teamId, (array)$node['learn_by'], true)) {
        $node['learn_by'][] = $teamId;
    }
    $session['game']['nodes'][$nodeId] = $node;
    return $session;
}

/** Volá linux_v58_levels_tg.php po vyřešení uzlu v Labu (linie sítě). Level id == node id. */
function tg58_netadmin_on_node_complete(string $gameId, string $studentKey, string $levelId, int $now): void
{
    tg58_mutate($gameId, static function (array $s) use ($studentKey, $levelId, $now): array {
        if ((string)($s['type'] ?? '') !== 'netadmin' || !in_array($levelId, (array)($s['game']['node_ids'] ?? []), true)) return $s;
        $teamId = tg58_team_of($s, $studentKey);
        return $teamId === null ? $s : tg58_netadmin_claim($s, $teamId, $studentKey, $levelId, $now);
    });
}

/** Sousedi v pevné mřížce (jen pro výpočet „největší souvislé oblasti“ – vizuální rozestavění mapy). */
function tg58_netadmin_neighbors(int $index, int $cols, int $total): array
{
    $row = intdiv($index, $cols);
    $col = $index % $cols;
    $out = [];
    foreach ([[0, -1], [0, 1], [-1, 0], [1, 0]] as [$dr, $dc]) {
        $nr = $row + $dr;
        $nc = $col + $dc;
        $ni = $nr * $cols + $nc;
        if ($nr >= 0 && $nc >= 0 && $nc < $cols && $ni < $total) $out[] = $ni;
    }
    return $out;
}

/** Body týmu: 10/uzel + 2×velikost největší souvislé oblasti + 5 za každý „naučný“ zásah do cizího uzlu. */
function tg58_netadmin_scores(array $session): array
{
    $nodeIds = array_values((array)$session['game']['node_ids']);
    $total = count($nodeIds);
    $ownerOf = [];
    foreach ($nodeIds as $i => $id) $ownerOf[$i] = tg58_netadmin_node_row($session, $id)['owner'];
    $bestRegion = [];
    $owned = [];
    $learn = [];
    $visited = array_fill(0, $total, false);
    for ($i = 0; $i < $total; $i++) {
        $team = $ownerOf[$i];
        if ($team === null) continue;
        $owned[$team] = ($owned[$team] ?? 0) + 1;
        if ($visited[$i]) continue;
        $stack = [$i];
        $visited[$i] = true;
        $size = 0;
        while ($stack !== []) {
            $cur = array_pop($stack);
            $size++;
            foreach (tg58_netadmin_neighbors($cur, TG58_NETADMIN_GRID_COLS, $total) as $n) {
                if (!$visited[$n] && $ownerOf[$n] === $team) { $visited[$n] = true; $stack[] = $n; }
            }
        }
        $bestRegion[$team] = max($bestRegion[$team] ?? 0, $size);
    }
    foreach ($nodeIds as $id) {
        foreach ((array)tg58_netadmin_node_row($session, $id)['learn_by'] as $team) $learn[$team] = ($learn[$team] ?? 0) + 1;
    }
    $teams = [];
    foreach (array_unique(array_merge(array_keys($owned), array_keys($learn))) as $team) {
        $teams[(string)$team] = TG58_NETADMIN_POINTS * (int)($owned[$team] ?? 0) + TG58_NETADMIN_REGION_BONUS * (int)($bestRegion[$team] ?? 0) + TG58_NETADMIN_LEARN_BONUS * (int)($learn[$team] ?? 0);
    }
    return $teams;
}

function tg58_netadmin_node_public(array $session, string $nodeId, int $now, ?string $viewerTeam): array
{
    $node = tg58_netadmin_node_row($session, $nodeId);
    $wave = (int)$session['game']['node_wave'][$nodeId];
    $visible = $wave <= tg58_netadmin_visible_wave($session, $now);
    $out = ['id' => $nodeId, 'visible' => $visible, 'owner' => $node['owner'] !== null ? tg58_team_label($session, (string)$node['owner']) : null, 'mine' => $viewerTeam !== null && $node['owner'] === $viewerTeam];
    if ($visible && $node['owner'] !== null && (int)($node['reserved_until'] ?? 0) > $now) $out['reserved_team'] = tg58_team_label($session, (string)$node['reserved_team']);
    return $out;
}

function tg58_netadmin_student_view(array $session, array $ctx): array
{
    $now = (int)$ctx['now'];
    $teamId = (string)$ctx['team_id'];
    $nodes = [];
    foreach ((array)$session['game']['node_ids'] as $id) {
        $row = tg58_netadmin_node_public($session, $id, $now, $teamId);
        if (!$row['visible']) { $nodes[] = $row; continue; }
        if ($row['owner'] === null && (string)$session['line'] !== 'networks') {
            $item = tg58_quiz_find((string)($session['game']['node_quiz'][$id] ?? ''));
            if ($item !== null) $row['question'] = tg58_quiz_public($item, (string)$session['id'] . '|na|' . $id);
        }
        if ((string)$session['line'] === 'networks') $row['level_id'] = $id;
        $nodes[] = $row;
    }
    $scores = tg58_netadmin_scores($session);
    return ['nodes' => $nodes, 'wave' => tg58_netadmin_visible_wave($session, $now), 'waves_total' => TG58_NETADMIN_WAVES, 'my_points' => (int)($scores[$teamId] ?? 0), 'scores' => tg58_netadmin_public_scores($session)];
}

function tg58_netadmin_public_scores(array $session): array
{
    $scores = tg58_netadmin_scores($session);
    $out = [];
    foreach ($scores as $teamId => $pts) $out[] = ['team' => tg58_team_label($session, (string)$teamId), 'points' => (int)$pts];
    usort($out, static fn(array $a, array $b): int => $b['points'] <=> $a['points']);
    return $out;
}

function tg58_netadmin_student_action(string $action, array $session, array $ctx, array $payload): array
{
    $nodeId = (string)($payload['node_id'] ?? '');
    if (!in_array($nodeId, (array)$session['game']['node_ids'], true)) throw new RuntimeException(tr('Tenhle uzel neexistuje.'));
    if ((int)$session['game']['node_wave'][$nodeId] > tg58_netadmin_visible_wave($session, (int)$ctx['now'])) throw new RuntimeException(tr('Tenhle uzel se ještě neobjevil.'));
    if ($action === 'netadmin_reserve') {
        $session['game']['nodes'][$nodeId] = array_merge(tg58_netadmin_node_row($session, $nodeId), ['reserved_team' => $ctx['team_id'], 'reserved_until' => (int)$ctx['now'] + TG58_NETADMIN_RESERVE_S]);
        return ['session' => $session, 'response' => ['ok' => true]];
    }
    if ($action === 'netadmin_answer') {
        if ((string)$session['line'] === 'networks') throw new RuntimeException(tr('Tenhle uzel se opravuje v terminálu Labu.'));
        $item = tg58_quiz_find((string)($session['game']['node_quiz'][$nodeId] ?? ''));
        if ($item === null) throw new RuntimeException(tr('Otázka se nenašla.'));
        $correct = tg58_quiz_check($item, $payload['answer'] ?? null);
        if ($correct) $session = tg58_netadmin_claim($session, (string)$ctx['team_id'], (string)$ctx['student_key'], $nodeId, (int)$ctx['now']);
        return ['session' => $session, 'response' => ['ok' => true, 'correct' => $correct]];
    }
    throw new RuntimeException(tr('Neznámá akce Správců sítě.'));
}

function tg58_netadmin_teacher_view(array $session, array $ctx): array
{
    $now = (int)$ctx['now'];
    $nodes = [];
    foreach ((array)$session['game']['node_ids'] as $id) $nodes[] = tg58_netadmin_node_public($session, $id, $now, null);
    return ['nodes' => $nodes, 'wave' => tg58_netadmin_visible_wave($session, $now), 'waves_total' => TG58_NETADMIN_WAVES, 'scores' => tg58_netadmin_public_scores($session)];
}

function tg58_netadmin_projector_view(array $session, array $ctx): array
{
    return tg58_netadmin_teacher_view($session, $ctx);
}

function tg58_netadmin_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    throw new RuntimeException('Správci sítě/webu nemají další učitelské akce.');
}

function tg58_netadmin_winners(array $session): array
{
    $scores = tg58_netadmin_scores($session);
    if ($scores === []) return [];
    $max = max($scores);
    return array_keys(array_filter($scores, static fn(int $p): bool => $p === $max && $p > 0));
}

function tg58_netadmin_summary(array $session): array
{
    $owned = 0;
    foreach ((array)$session['game']['node_ids'] as $id) { if (tg58_netadmin_node_row($session, $id)['owner'] !== null) $owned++; }
    return ['nodes_total' => count((array)($session['game']['node_ids'] ?? [])), 'nodes_owned' => $owned];
}

tg58_register_game('netadmin', [
    'label' => tr('Správci sítě / webu'), 'default_duration_min' => 30,
    'default_settings' => 'tg58_netadmin_default_settings', 'parse_settings' => 'tg58_netadmin_parse_settings', 'on_start' => 'tg58_netadmin_on_start',
    'student_view' => 'tg58_netadmin_student_view', 'student_action' => 'tg58_netadmin_student_action',
    'teacher_view' => 'tg58_netadmin_teacher_view', 'teacher_action' => 'tg58_netadmin_teacher_action', 'projector_view' => 'tg58_netadmin_projector_view',
    'winners' => 'tg58_netadmin_winners', 'summary' => 'tg58_netadmin_summary',
]);
