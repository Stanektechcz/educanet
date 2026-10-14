<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli' && basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80.1 · deterministická fixture pro audit Mapy třídy (a místní ukázku): žáci třídy jsou ze student_directory.php (fiktivní jména),
 * důkazy kompetencí a výsledky úloh se generují ze vzoru podle pořadí žáka (i) a sloupce (j), takže audit zná očekávanou úroveň každé buňky.
 * Zapisuje JEN do STORAGE_DIR (audit = dočasné úložiště; ostrou storage/ nikdy).
 *   vzor kompetence: (i * 7 + j * 3) % 5 → 0 bez dat · 1 potřebuje pomoc (skóre 0,2) · 2 učí se (0,55) · 3 umí (0,85, jeden zdroj) · 4 umí jistě (0,9, test + lab o 10 dní)
 *   vzor úlohy: pokusů (i + j) % 5, z nich správně min(pokusy, (i * 2 + j) % 4)
 */

function v801_fx_comp_level(int $i, int $j): string
{
    return ['none', 'low', 'mid', 'ok', 'strong'][($i * 7 + $j * 3) % 5];
}

/** @return array{0:int,1:int} [pokusy, správně] */
function v801_fx_task(int $i, int $j): array
{
    $n = ($i + $j) % 5;
    return [$n, min($n, ($i * 2 + $j) % 4)];
}

/** Očekávaná úroveň buňky úlohy (stejná pravidla jako cm801_cell_task). */
function v801_fx_task_level(int $i, int $j): string
{
    [$n, $ok] = v801_fx_task($i, $j);
    if ($n === 0) return 'none';
    $rate = (int)round($ok / $n * 100);
    return $rate >= 75 ? ($n >= 4 && $rate >= 90 ? 'strong' : 'ok') : ($rate >= 40 ? 'mid' : 'low');
}

/**
 * Zapíše fixture třídy. Vrací žáky v pořadí mapy (podle jména) s jejich indexem i (0…) – podle něj audit počítá očekávání.
 * @return list<array{key:string,label:string,sid:string,ref:string,i:int}>
 */
function v801_fx_seed(string $classId, int $now): array
{
    if (function_exists('identity58_ensure')) identity58_ensure();
    $students = cm801_students($classId);
    $subject = comp62_enabled_for_class($classId) ? comp62_subject_for_class($classId) : null;
    $comps = $subject !== null ? array_keys(comp62_competencies($subject)) : [];
    $tasks = tb75_tasks_for_class($classId);
    $out = [];
    $tb = [];
    foreach ($students as $i => $s) {
        if ($s['sid'] !== '' && $comps !== []) {
            $rows = [];
            foreach ($comps as $j => $comp) {
                $lvl = v801_fx_comp_level($i, $j);
                $at = static fn(int $d): string => date(DATE_ATOM, $now - $d * 86400);
                $row = static fn(string $src, float $score, int $d, string $ref): array => ['competency' => $comp, 'level' => 2, 'source' => $src, 'score' => $score, 'at' => $at($d), 'artefact_ref' => $ref];
                if ($lvl === 'low') $rows[] = $row('test', 0.2, 3, 'fx801:' . $j . ':a');
                if ($lvl === 'mid') $rows[] = $row('test', 0.55, 3, 'fx801:' . $j . ':a');
                if ($lvl === 'ok') $rows[] = $row('test', 0.85, 2, 'fx801:' . $j . ':a');
                if ($lvl === 'strong') { $rows[] = $row('test', 0.9, 12, 'fx801:' . $j . ':a'); $rows[] = $row('lab', 0.9, 2, 'fx801:' . $j . ':b'); }
            }
            if ($rows !== []) ev62_append($s['sid'], $rows);
        }
        foreach ($tasks as $j => $task) {
            [$n, $ok] = v801_fx_task($i, $j);
            if ($n === 0) continue;
            $log = [];
            for ($k = 0; $k < $n; $k++) $log[] = ['r' => $k + 1, 'ok' => $k < $ok ? 1 : 0, 'f' => [], 'at' => $now - (10 - $k) * 3600];
            $tb[$s['ref']][$task] = ['round' => $n + 1, 'tries' => 0, 'solved' => $ok, 'shown' => false, 'last_ok' => false, 'at' => $now - 3600, 'log' => $log];
        }
        $out[] = $s + ['i' => $i];
    }
    if ($tb !== []) storage_update(tb75_path(), static function (array $d) use ($classId, $tb): array {
        $d['v'] = 1;
        foreach ($tb as $ref => $state) $d['classes'][$classId][$ref] = $state;
        return $d;
    });
    if (function_exists('ev62_memo_reset')) ev62_memo_reset();
    return $out;
}
