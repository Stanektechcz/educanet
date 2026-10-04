<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · fixture pro tools/v62_competency_audit.php (vymyšlená jména „Audit …“, jen dočasné úložiště).
 *
 * v62fx_seed($tmp) zapíše roster pilotní třídy (3.A) a historii zdrojů, ze kterých čtou adaptéry:
 *   Dobrý  – stav labu (practice, review, race, weekly, ctf, incident, tg), průběh lekce v56, výsledek testu a praktického labu,
 *            souboj v60, týmová hra, zveřejněné hodnocení projektu (bez tagů → WARN),
 *   Hráč   – jen hry a aréna (race, weekly, tg, souboj prohraný),
 *   Nikdo  – nic,
 *   Odešel – žák s důkazem, kterému audit nastaví stav left (retence).
 * Zdrojová data se zapisují přímo do souborů v dočasném úložišti; adaptéry je jen čtou.
 */

const V62FX_CLASS = 'class_3a';
const V62FX_LABELS = ['good' => 'Audit Dobry', 'player' => 'Audit Hrac', 'none' => 'Audit Nikdo', 'left' => 'Audit Odesel'];

function v62fx_key(string $who): string
{
    return project_student_key(V62FX_CLASS, V62FX_LABELS[$who]);
}

function v62fx_write_roster(string $tmp): void
{
    @mkdir($tmp . '/intake', 0700, true);
    $rows = [];
    $i = 0;
    foreach (V62FX_LABELS as $label) {
        [$first, $last] = explode(' ', $label, 2);
        $rows[] = ['id' => 'fx62_' . $i++, 'class_id' => V62FX_CLASS, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''],
            'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
    }
    file_put_contents($tmp . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE));
    unset($GLOBALS['educanet_runtime_indexes']);
}

/** Zapíše vyřešené úrovně do stavu labu žáka: $solved = [kontext => [úroveň => [dnů zpět, nápovědy]]]. */
function v62fx_lab(string $who, array $solved, int $now): void
{
    $path = lab57_state_path(V62FX_CLASS, v62fx_key($who));
    storage_update($path, static function (array $d) use ($solved, $now): array {
        foreach ($solved as $context => $levels) {
            foreach ($levels as $level => [$days, $hints]) $d['solved'][$context][$level] = ['at' => date(DATE_ATOM, $now - $days * 86400), 'points' => 100, 'hints' => $hints, 'cmds' => 5, 'secs' => 60];
        }
        return $d;
    });
}

function v62fx_seed_good(int $now): void
{
    $key = v62fx_key('good');
    $ago = static fn(int $days): string => date(DATE_ATOM, $now - $days * 86400);
    v62fx_lab('good', [
        'practice' => ['start-1' => [20, 0], 'start-2' => [18, 1], 'sit-4' => [10, 0]],
        'review' => ['review-ls-1' => [4, 0]],
        'weekly:2026t39' => ['tyden-1' => [6, 0]],
        'ctf:fx62ctf' => ['ctf-kod-1' => [7, 0]],
        'incident:fx62inc' => ['inc-web' => [8, 0]],
        'tg:fx62tg' => ['tg-relay-net-1' => [9, 0]],
    ], $now);
    v62fx_lab('good', ['race:fx62race' => ['start-3' => [3, 0]]], $now);
    storage_update(STORAGE_DIR . '/progress_v56.json.php', static function (array $all) use ($key, $ago): array {
        $all[V62FX_CLASS . '|' . $key]['1'] = ['theory' => ['dns' => $ago(25)], 'test' => ['at' => $ago(24), 'percent' => 67, 'detail' => [
            ['id' => 'dns_role', 'given' => 'b', 'correct' => 'b', 'ok' => true], ['id' => 'dhcp_role', 'given' => 'a', 'correct' => 'a', 'ok' => true], ['id' => 'private_ip', 'given' => 'x', 'correct' => 'a', 'ok' => false]]]];
        return $all;
    });
    storage_append('practice_results', ['id' => 'fx62r1', 'class_id' => V62FX_CLASS, 'student_label' => V62FX_LABELS['good'], 'student_email' => '', 'started_at' => $ago(31), 'finished_at' => $ago(30), 'score' => 2, 'max_score' => 3,
        'answers' => [['question_id' => 'dns_role', 'selected' => 'b', 'correct' => true], ['question_id' => 'dhcp_role', 'selected' => 'a', 'correct' => true], ['question_id' => 'ping', 'selected' => 'a', 'correct' => false]]]);
    storage_append('lab_results', ['id' => 'fx62l1', 'class_id' => V62FX_CLASS, 'student_label' => V62FX_LABELS['good'], 'student_email' => '', 'started_at' => $ago(29), 'finished_at' => $ago(28), 'completed_steps' => 1,
        'answers' => [['task_id' => 'dhcp_dns', 'step_index' => 0, 'selected' => 'a', 'attempts' => 1, 'hints_used' => 0, 'kb' => 'dhcp']]]);
    storage_update(STORAGE_DIR . '/project_grades.json.php', static fn(array $all): array => array_merge($all, [['id' => 'fx62g1', 'class_id' => V62FX_CLASS, 'project_id' => 'fxp1', 'target_type' => 'individual', 'target_id' => $key,
        'points' => 8, 'max_points' => 10, 'status' => 'published', 'published_at' => $ago(2), 'updated_at' => $ago(2)]]));
}

function v62fx_seed_shared(int $now): void
{
    $ago = static fn(int $days): string => date(DATE_ATOM, $now - $days * 86400);
    storage_update(STORAGE_DIR . '/arena_v60_challenges.json.php', static fn(array $d): array => ['challenges' => [['id' => 'fx62c1', 'class_id' => V62FX_CLASS, 'from_key' => v62fx_key('good'), 'to_key' => v62fx_key('player'),
        'level_id' => 'sit-4', 'status' => 'done', 'winner_key' => v62fx_key('good'), 'finished_at' => $ago(5)]], 'optin' => []]);
    storage_update(STORAGE_DIR . '/teamgames_v58/aabbccdd11.json.php', static fn(array $d): array => ['id' => 'aabbccdd11', 'class_id' => V62FX_CLASS, 'status' => 'finished', 'finished_at' => $ago(9), 'line' => 'networks',
        'teams' => [['id' => 't1', 'members' => [v62fx_key('good'), v62fx_key('player')]]]]);
    storage_update(STORAGE_DIR . '/teamgames_v58_index.json.php', static fn(array $d): array => ['entries' => [['id' => 'aabbccdd11', 'class_id' => V62FX_CLASS, 'type' => 'relay', 'created_at' => $ago(10)]]]);
}

/** @return array{now:int} */
function v62fx_seed(string $tmp, int $now): array
{
    foreach (['intake_v51.php', 'linux_v57_lab.php', 'identity_v58.php'] as $lib) require_once dirname(__DIR__, 2) . '/' . $lib;
    v62fx_write_roster($tmp);
    v62fx_seed_good($now);
    v62fx_seed_shared($now);
    v62fx_lab('player', ['race:fx62race' => ['start-1' => [12, 0], 'start-2' => [11, 0]], 'weekly:2026t39' => ['tyden-1' => [5, 0]], 'tg:fx62tg' => ['tg-relay-net-1' => [9, 0]]], $now);
    v62fx_lab('left', ['practice' => ['start-1' => [40, 0]]], $now);
    identity58_build(false);
    return ['now' => $now];
}
