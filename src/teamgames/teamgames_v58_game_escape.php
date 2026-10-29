<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr()/trn() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – Úniková místnost (ARN-04, kooperativní mise).
 *
 * Role s asymetrickými informacemi (linie sítě: Síťař/Správce/Detektiv/Dokumentátor ve sdíleném světě
 * Labu; linie grafika: Typograf/Kolorista/Kodér/Art director u kvízových otázek). Zámek se otevírá kódem
 * složeným ze 4 částí, každá patří jedné roli – API nikdy nepošle cizí část, žák si je musí říct s týmem
 * nahlas (appka chat nemá). Tým může použít nápovědu (přidá čas), ale limit 5 pokusů o zámek za minutu
 * brání zkoušení naslepo. Cíl třídy: uniknou všechny týmy – kdo doběhne dřív, jen se pochlubí časem,
 * ale XP dostane každý tým, který unikl.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';

const TG58_ESCAPE_ROLES_GFX = ['typograf', 'kolorista', 'koder', 'art_director'];
const TG58_ESCAPE_MAX_HINTS = 3;
const TG58_ESCAPE_HINT_PENALTY_S = 120;
const TG58_ESCAPE_RATE_MAX = 5;
const TG58_ESCAPE_RATE_WINDOW_S = 60;

function tg58_escape_default_settings(): array
{
    return [];
}

function tg58_escape_parse_settings(array $in, array $ctx): array
{
    return [];
}

function tg58_escape_roles(string $line): array
{
    return $line === 'networks' ? TG58_ESCAPE_ROLES_NET : TG58_ESCAPE_ROLES_GFX;
}

function tg58_escape_role_label(string $role): string
{
    return match ($role) {
        'sitar' => tr('Síťař'), 'spravce' => tr('Správce'), 'detektiv' => tr('Detektiv'), 'dokumentator' => tr('Dokumentátor'),
        'typograf' => tr('Typograf'), 'kolorista' => tr('Kolorista'), 'koder' => tr('Kodér'), 'art_director' => tr('Art director'),
        default => $role,
    };
}

function tg58_escape_gfx_category(string $role): string
{
    return match ($role) { 'typograf' => 'typografie', 'kolorista' => 'barvy a kontrast', 'koder' => 'HTML', default => 'rozbitá stránka' };
}

/**
 * FAIR58-05: fragment role = HMAC(tajemství serveru, hra|tým|role) – nejde dopočítat a každý tým má jiný kód.
 * Obě linie používají stejný výpočet (tg58_escape_fragment v linux_v58_levels_tg.php); v Labu ho vidí jen daná role.
 */
function tg58_escape_fragment_expected(array $session, string $teamId, string $role, int $now): ?string
{
    return tg58_escape_fragment((string)$session['id'], $teamId, $role);
}

function tg58_escape_on_start(array $session, array $ctx): array
{
    $roles = tg58_escape_roles((string)$session['line']);
    $teams = [];
    $roleOf = [];
    foreach ((array)$ctx['teams'] as $team) {
        $members = array_values(array_map('strval', (array)$team['members']));
        $assign = [];
        foreach ($members as $i => $key) { $r = $roles[$i % count($roles)]; $assign[$key] = $r; $roleOf[$key] = $r; }
        $quizIds = [];
        if ((string)$session['line'] !== 'networks') {
            foreach ($roles as $r) {
                $items = tg58_quiz_select('graphics', (string)$session['class_id'], 1, (string)$session['id'] . '|esc|' . $team['id'] . '|' . $r, ['category' => tg58_escape_gfx_category($r)]);
                if ($items === []) $items = tg58_quiz_select('graphics', (string)$session['class_id'], 1, (string)$session['id'] . '|esc2|' . $team['id'] . '|' . $r);
                $quizIds[$r] = $items[0]['id'] ?? null;
            }
        }
        $teams[(string)$team['id']] = ['assign' => $assign, 'gates' => array_fill_keys($roles, false), 'quiz_ids' => $quizIds, 'hints_used' => 0, 'penalty_s' => 0, 'escaped_at' => null];
    }
    $session['game'] = ['roles' => $roles, 'teams' => $teams, 'role_of' => $roleOf, 'winners' => []];
    return $session;
}

function tg58_escape_student_view(array $session, array $ctx): array
{
    $teamId = (string)$ctx['team_id'];
    $t = $session['game']['teams'][$teamId] ?? null;
    if ($t === null) throw new RuntimeException(tr('Tým nebyl nalezen.'));
    $role = $t['assign'][(string)$ctx['student_key']] ?? null;
    $out = [
        'role' => $role, 'role_label' => $role !== null ? tg58_escape_role_label($role) : null,
        'roles_order' => array_map('tg58_escape_role_label', (array)$session['game']['roles']),
        'escaped' => $t['escaped_at'] !== null, 'hints_used' => (int)$t['hints_used'], 'hints_max' => TG58_ESCAPE_MAX_HINTS,
        'can_hint' => $t['escaped_at'] === null && (int)$t['hints_used'] < TG58_ESCAPE_MAX_HINTS,
    ];
    if ($t['escaped_at'] !== null) return $out;
    if ((string)$session['line'] === 'networks') {
        $out['mode'] = 'lab';
        $level = function_exists('lab57_level') ? lab57_level('tg-escape-net-1') : null;
        $out['level_title'] = $level['title'] ?? null;
    } else {
        $out['mode'] = 'quiz';
        $gateDone = $role !== null && !empty($t['gates'][$role]);
        $out['gate_done'] = $gateDone;
        if (!$gateDone && $role !== null) {
            $item = tg58_quiz_find((string)($t['quiz_ids'][$role] ?? ''));
            $out['question'] = $item !== null ? tg58_quiz_public($item, (string)$session['id'] . '|esc|' . $teamId . '|' . $role) : null;
        }
        if ($gateDone && $role !== null) $out['fragment'] = tg58_escape_fragment((string)$session['id'], $teamId, $role);
    }
    return $out;
}

function tg58_escape_student_action(string $action, array $session, array $ctx, array $payload): array
{
    $teamId = (string)$ctx['team_id'];
    $t = $session['game']['teams'][$teamId] ?? null;
    if ($t === null) throw new RuntimeException(tr('Tým nebyl nalezen.'));
    $now = (int)$ctx['now'];
    if ($action === 'escape_hint') {
        if ($t['escaped_at'] !== null) throw new RuntimeException(tr('Tenhle tým už unikl.'));
        if ((int)$t['hints_used'] >= TG58_ESCAPE_MAX_HINTS) throw new RuntimeException(tr('Nápovědy pro tenhle tým došly.'));
        $t['hints_used'] = (int)$t['hints_used'] + 1;
        $t['penalty_s'] = (int)$t['penalty_s'] + TG58_ESCAPE_HINT_PENALTY_S;
        $session['game']['teams'][$teamId] = $t;
        return ['session' => $session, 'response' => ['ok' => true, 'message' => tr('Tým dostal nápovědu (+{s} s k výslednému času).', ['s' => TG58_ESCAPE_HINT_PENALTY_S])]];
    }
    if ($action === 'escape_answer') {
        if ((string)$session['line'] !== 'graphics') throw new RuntimeException(tr('Tahle role se řeší v terminálu Labu.'));
        $role = $t['assign'][(string)$ctx['student_key']] ?? null;
        if ($role === null) throw new RuntimeException(tr('Nemáš přiřazenou roli.'));
        if (!empty($t['gates'][$role])) return ['session' => $session, 'response' => ['ok' => true, 'already' => true]];
        $item = tg58_quiz_find((string)($t['quiz_ids'][$role] ?? ''));
        if ($item === null) throw new RuntimeException(tr('Otázka se nenašla.'));
        tg58_wrong_guard($session, (string)$ctx['student_key'], $now);
        $correct = tg58_quiz_check($item, $payload['answer'] ?? null);
        if ($correct) { $t['gates'][$role] = true; $session['game']['teams'][$teamId] = $t; }
        else $session = tg58_wrong_record($session, (string)$ctx['student_key'], $now);
        return ['session' => $session, 'response' => ['ok' => true, 'correct' => $correct]];
    }
    if ($action === 'escape_unlock') {
        if (!tg58_rate_ok('escape:' . (string)$session['id'], TG58_ESCAPE_RATE_MAX, TG58_ESCAPE_RATE_WINDOW_S, $now)) throw new RuntimeException(tr('Moc pokusů o zámek za minutu – chvilku počkej.'));
        if ($t['escaped_at'] !== null) return ['session' => $session, 'response' => ['ok' => true, 'already' => true]];
        $roles = (array)$session['game']['roles'];
        $parts = array_values(array_map(static fn($p): string => strtoupper(trim((string)$p)), (array)($payload['parts'] ?? [])));
        if (count($parts) !== count($roles)) throw new RuntimeException(tr('Zámek potřebuje {n} částí kódu (jednu od každé role).', ['n' => count($roles)]));
        $ok = true;
        foreach ($roles as $i => $role) {
            $expected = tg58_escape_fragment_expected($session, $teamId, $role, $now);
            if ($expected === null || !hash_equals($expected, (string)($parts[$i] ?? ''))) { $ok = false; break; }
        }
        if ($ok) {
            $t['escaped_at'] = tg58_iso($now);
            $session['game']['teams'][$teamId] = $t;
            $session['game']['winners'] = array_values(array_unique(array_merge((array)$session['game']['winners'], [$teamId])));
        }
        return ['session' => $session, 'response' => ['ok' => true, 'correct' => $ok]];
    }
    throw new RuntimeException(tr('Neznámá akce únikové místnosti.'));
}

function tg58_escape_team_time_s(array $session, string $teamId, int $now): ?int
{
    $t = $session['game']['teams'][$teamId] ?? null;
    $started = tg58_ts($session['started_at'] ?? null);
    if ($t === null || $started === null || $t['escaped_at'] === null) return null;
    return max(0, tg58_ts($t['escaped_at']) - $started) + (int)$t['penalty_s'];
}

function tg58_escape_teacher_view(array $session, array $ctx): array
{
    $now = (int)$ctx['now'];
    $rows = [];
    $escaped = 0;
    foreach ((array)$session['teams'] as $team) {
        $t = $session['game']['teams'][$team['id']] ?? ['escaped_at' => null, 'hints_used' => 0, 'assign' => []];
        if ($t['escaped_at'] !== null) $escaped++;
        $roles = [];
        $roster = tg58_roster((string)$session['class_id']);
        foreach ((array)$t['assign'] as $key => $role) $roles[] = tg58_escape_role_label($role) . ': ' . tg58_label((string)$key, '', $roster);
        $rows[] = ['team' => $team['name'], 'escaped' => $t['escaped_at'] !== null, 'time_s' => tg58_escape_team_time_s($session, (string)$team['id'], $now), 'hints_used' => (int)$t['hints_used'], 'roles' => $roles];
    }
    usort($rows, static fn(array $a, array $b): int => ((int)$b['escaped'] <=> (int)$a['escaped']) ?: (($a['time_s'] ?? PHP_INT_MAX) <=> ($b['time_s'] ?? PHP_INT_MAX)));
    return ['rows' => $rows, 'escaped' => $escaped, 'teams_total' => count((array)$session['teams'])];
}

function tg58_escape_projector_view(array $session, array $ctx): array
{
    $out = tg58_escape_teacher_view($session, $ctx);
    foreach ($out['rows'] as &$row) unset($row['roles'], $row['hints_used']);
    return $out;
}

function tg58_escape_teacher_action(string $action, array $session, array $ctx, array $payload): array
{
    if ($action !== 'escape_force_open') throw new RuntimeException('Neznámá akce únikové místnosti.');
    $teamId = (string)($payload['team_id'] ?? '');
    if (!isset($session['game']['teams'][$teamId])) throw new RuntimeException('Tým nebyl nalezen.');
    if ($session['game']['teams'][$teamId]['escaped_at'] === null) $session['game']['teams'][$teamId]['escaped_at'] = tg58_iso((int)$ctx['now']);
    return ['session' => $session, 'response' => ['ok' => true]];
}

function tg58_escape_winners(array $session): array
{
    return array_values((array)($session['game']['winners'] ?? []));
}

function tg58_escape_summary(array $session): array
{
    $escaped = 0;
    foreach ((array)($session['game']['teams'] ?? []) as $t) { if (($t['escaped_at'] ?? null) !== null) $escaped++; }
    return ['escaped' => $escaped, 'teams_total' => count((array)($session['game']['teams'] ?? []))];
}

tg58_register_game('escape', [
    'label' => tr('Úniková místnost'), 'default_duration_min' => 30,
    'default_settings' => 'tg58_escape_default_settings', 'parse_settings' => 'tg58_escape_parse_settings', 'on_start' => 'tg58_escape_on_start',
    'student_view' => 'tg58_escape_student_view', 'student_action' => 'tg58_escape_student_action',
    'teacher_view' => 'tg58_escape_teacher_view', 'teacher_action' => 'tg58_escape_teacher_action', 'projector_view' => 'tg58_escape_projector_view',
    'winners' => 'tg58_escape_winners', 'summary' => 'tg58_escape_summary',
]);
