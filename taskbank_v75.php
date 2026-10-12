<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/taskbank_v75_gen.php';

/**
 * EDUCANET v75 · banka úloh s variantami – stav žáků, odevzdání a souhrny (zápis jen storage_update).
 *
 * Úložiště storage/taskbank_v75.json.php = {"v":1,"classes":{třída:{ref žáka:{úloha:{round,tries,solved,shown,last_ok,at,log:[{r,ok,f,at}]}}}}}
 * ref žáka = student_id (identita v58), jinak pseudonym z hashe třídy a klíče žáka – nikdy jméno ani e-mail. Odpovědi se neukládají
 * (jen výsledek po polích), takže soubor neobsahuje text od žáka. Úlohy nejsou hodnocením: nedávají body ani XP ani známku.
 * Pravidla kola: správně všechna pole = vyřešeno → „Další varianta“ (nové kolo); po TB75_TRIES chybných pokusech se ukáže
 * řešení a další pokus je možný jen v nové variantě (žádné hádání do vyčerpání). Sady: síť a Linux pro třídy OS a sítí (3.A, 4.A).
 */

const TB75_CLASS_SETS = ['class_3a' => ['net_subnet', 'net_mask', 'net_ports', 'net_route', 'lnx_path', 'lnx_chmod', 'lnx_grep'],
    'class_4a' => ['net_subnet', 'net_mask', 'net_ports', 'net_route', 'lnx_path', 'lnx_chmod', 'lnx_grep']];
const TB75_TRIES = 3;
const TB75_LOG_MAX = 20;
const TB75_ACTIONS = ['tb75_answer', 'tb75_next'];

function tb75_path(): string
{
    return STORAGE_DIR . '/taskbank_v75.json.php';
}

/** @return list<string> úlohy třídy (prázdné = třída banku úloh nemá) */
function tb75_tasks_for_class(string $classId): array
{
    return TB75_CLASS_SETS[$classId] ?? [];
}

/** Stabilní reference žáka: student_id z identity v58, jinak pseudonym (hash). */
function tb75_student_ref(string $classId, string $studentKey): string
{
    if (function_exists('identity58_id_for_student') && function_exists('project_students_for_class')) {
        $student = project_students_for_class($classId)[$studentKey] ?? null;
        $id = is_array($student) ? identity58_id_for_student($classId, (string)$student['label']) : null;
        if (is_string($id) && preg_match('/^stu_[0-9a-f]{16}$/', $id) === 1) return $id;
    }
    return 'h' . substr(hash('sha256', 'tb75|' . $classId . '|' . $studentKey), 0, 24);
}

function tb75_data(): array
{
    $d = storage_read(tb75_path(), false);
    return is_array($d) ? $d : [];
}

/** Stav úloh žáka (úloha → záznam). */
function tb75_student_state(string $classId, string $ref, ?array $data = null): array
{
    return (array)((($data ?? tb75_data())['classes'][$classId] ?? [])[$ref] ?? []);
}

function tb75_entry(array $state, string $task): array
{
    $e = (array)($state[$task] ?? []);
    return ['round' => max(1, (int)($e['round'] ?? 1)), 'tries' => (int)($e['tries'] ?? 0), 'solved' => (int)($e['solved'] ?? 0), 'shown' => !empty($e['shown']),
        'last_ok' => !empty($e['last_ok']), 'at' => (int)($e['at'] ?? 0), 'log' => array_values(array_filter((array)($e['log'] ?? []), 'is_array'))];
}

/** Aktuální varianta úlohy pro žáka (semínko = ref žáka). */
function tb75_current(string $classId, string $ref, string $task, ?array $data = null): array
{
    $e = tb75_entry(tb75_student_state($classId, $ref, $data), $task);
    return ['entry' => $e, 'variant' => tb75_variant($task, $ref, $e['round'])];
}

/** Porovná odpovědi s klíčem varianty. @return array{ok:bool,fields:array<string,bool>,correct:int} */
function tb75_check(array $variant, array $answers): array
{
    $fields = [];
    foreach ($variant['fields'] as $f) {
        $given = is_string($answers[$f['id']] ?? null) ? (string)$answers[$f['id']] : '';
        $fields[$f['id']] = $given !== '' && tb75_norm((string)$variant['task'], (string)$f['id'], $given) === tb75_norm((string)$variant['task'], (string)$f['id'], (string)$f['answer']);
    }
    $correct = count(array_filter($fields));
    return ['ok' => $correct === count($fields) && $fields !== [], 'fields' => $fields, 'correct' => $correct];
}

/**
 * Odevzdání aktuální varianty. Vrací ['ok'=>bool,'error'=>?string,'result'=>?array,'entry'=>array].
 * Chyby: unknown_task, empty, solved (kolo už je vyřešené), shown (řešení už bylo ukázáno → jen nová varianta).
 */
function tb75_submit(string $classId, string $studentKey, string $task, array $answers, ?int $now = null): array
{
    if (!in_array($task, tb75_tasks_for_class($classId), true)) return ['ok' => false, 'error' => 'unknown_task'];
    if (array_filter($answers, static fn($v): bool => is_string($v) && trim($v) !== '') === []) return ['ok' => false, 'error' => 'empty'];
    $answers = array_map(static fn($v): string => is_string($v) ? mb_substr($v, 0, 80) : '', $answers);
    $ref = tb75_student_ref($classId, $studentKey);
    $now ??= time();
    $out = ['ok' => false, 'error' => null, 'result' => null];
    storage_update(tb75_path(), static function (array $d) use ($classId, $ref, $task, $answers, $now, &$out): array {
        $e = tb75_entry((array)(($d['classes'][$classId] ?? [])[$ref] ?? []), $task);
        if ($e['last_ok'] && $e['tries'] > 0) { $out['error'] = 'solved'; return $d; }
        if ($e['shown']) { $out['error'] = 'shown'; return $d; }
        $res = tb75_check(tb75_variant($task, $ref, $e['round']), $answers);
        $e['tries']++;
        $e['last_ok'] = $res['ok'];
        if ($res['ok']) $e['solved']++;
        if (!$res['ok'] && $e['tries'] >= TB75_TRIES) $e['shown'] = true;
        $e['at'] = $now;
        $e['log'] = array_slice(array_merge($e['log'], [['r' => $e['round'], 'ok' => $res['ok'] ? 1 : 0, 'f' => array_map(static fn(bool $b): int => $b ? 1 : 0, $res['fields']), 'at' => $now]]), -TB75_LOG_MAX);
        $d['v'] = 1;
        $d['classes'][$classId][$ref][$task] = $e;
        $out = ['ok' => true, 'error' => null, 'result' => $res, 'entry' => $e];
        return $d;
    });
    return $out;
}

/** Nové kolo (další varianta) – jen po vyřešení nebo po ukázaném řešení. */
function tb75_next(string $classId, string $studentKey, string $task, ?int $now = null): bool
{
    if (!in_array($task, tb75_tasks_for_class($classId), true)) return false;
    $ref = tb75_student_ref($classId, $studentKey);
    $done = false;
    storage_update(tb75_path(), static function (array $d) use ($classId, $ref, $task, $now, &$done): array {
        $e = tb75_entry((array)(($d['classes'][$classId] ?? [])[$ref] ?? []), $task);
        if (!($e['last_ok'] && $e['tries'] > 0) && !$e['shown']) return $d;
        $e = ['round' => $e['round'] + 1, 'tries' => 0, 'solved' => $e['solved'], 'shown' => false, 'last_ok' => false, 'at' => $now ?? time(), 'log' => $e['log']];
        $d['v'] = 1;
        $d['classes'][$classId][$ref][$task] = $e;
        $done = true;
        return $d;
    });
    return $done;
}

/**
 * Souhrn třídy po úlohách: pokusy, úspěšnost a nejčastěji chybná pole (podklad pro „Opakování před písemkou“). Bez jmen.
 * @return array<string,array{students:int,attempts:int,ok:int,rate:int,fields:array<string,array{wrong:int,total:int}>}>
 */
function tb75_class_stats(string $classId, ?int $since = null, ?array $data = null): array
{
    $out = [];
    foreach (tb75_tasks_for_class($classId) as $task) $out[$task] = ['students' => 0, 'attempts' => 0, 'ok' => 0, 'rate' => 0, 'fields' => []];
    foreach ((array)(($data ?? tb75_data())['classes'][$classId] ?? []) as $state) {
        foreach ((array)$state as $task => $raw) {
            if (!isset($out[$task]) || !is_array($raw)) continue;
            $e = tb75_entry([$task => $raw], (string)$task);
            $log = array_values(array_filter($e['log'], static fn(array $l): bool => $since === null || (int)($l['at'] ?? 0) >= $since));
            if ($log === []) continue;
            $out[$task]['students']++;
            foreach ($log as $l) {
                $out[$task]['attempts']++;
                $out[$task]['ok'] += (int)!empty($l['ok']);
                foreach ((array)($l['f'] ?? []) as $fid => $good) {
                    $out[$task]['fields'][$fid]['total'] = ($out[$task]['fields'][$fid]['total'] ?? 0) + 1;
                    $out[$task]['fields'][$fid]['wrong'] = ($out[$task]['fields'][$fid]['wrong'] ?? 0) + ((int)$good === 1 ? 0 : 1);
                }
            }
        }
    }
    foreach ($out as $task => $s) $out[$task]['rate'] = $s['attempts'] > 0 ? (int)round($s['ok'] / $s['attempts'] * 100) : 0;
    return $out;
}

/** Úspěšnost žáka v bance úloh za posledních N dní (podklad doporučení úrovně). @return array{attempts:int,ok:int} */
function tb75_student_recent(string $classId, string $studentKey, int $days = 14, ?int $now = null): array
{
    $since = ($now ?? time()) - $days * 86400;
    $res = ['attempts' => 0, 'ok' => 0];
    foreach (tb75_student_state($classId, tb75_student_ref($classId, $studentKey)) as $task => $raw) {
        if (!is_array($raw)) continue;
        foreach (tb75_entry([$task => $raw], (string)$task)['log'] as $l) {
            if ((int)($l['at'] ?? 0) < $since) continue;
            $res['attempts']++;
            $res['ok'] += (int)!empty($l['ok']);
        }
    }
    return $res;
}
