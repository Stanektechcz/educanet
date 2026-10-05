<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · tools/v67_effect_report.php – souhrn efektu učení po třídách (JEN ČTE: EDUCANET_STORAGE_READONLY=1, nic nezapisuje).
 *
 *   php tools/v67_effect_report.php [--class=class_3a]
 *
 * Za každou třídu: počet žáků, podíl aktivních za 7 dní, podíl kompetencí ve stavu Zvládnuto/Upevněno (pilotní třídy), podíl
 * dokončených cest (v63) a projektů dokončených do hodnocení/portfolia (v65). Agregace jen po třídách; třída s méně než
 * 5 žáky se potlačí (nevypíše se nic než „potlačeno“), aby šlo z čísel poznat jednotlivce. Výstup neobsahuje jména ani e-maily.
 * Konec: V67_EFFECT_REPORT_OK (vše vypočteno) | V67_EFFECT_REPORT_WARN suppressed=N (některá třída potlačena).
 */

putenv('EDUCANET_STORAGE_READONLY=1');
$_ENV['EDUCANET_STORAGE_READONLY'] = '1';
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/intake_v51.php';
foreach (['identity_v58.php', 'competencies_v62.php', 'evidence_v62.php', 'mastery_v62.php', 'paths_v63.php', 'projects_v60.php', 'projects_v65.php'] as $lib) require_once dirname(__DIR__) . '/' . $lib;

const EFFECT67_MIN_GROUP = 5;

function effect67_pct(int $part, int $whole): string
{
    return $whole > 0 ? (string)round($part / $whole * 100) . ' %' : '–';
}

/** Počet žáků s aktivitou (událost v profilu) za posledních 7 dní. */
function effect67_active(string $classId, array $students, array $profiles, int $now): int
{
    $n = 0;
    foreach ($students as $s) {
        $row = $profiles[skill_student_key_for_label($classId, (string)$s['label'])] ?? null;
        foreach ((array)($row['events'] ?? []) as $e) {
            if (is_array($e) && $now - (int)strtotime((string)($e['at'] ?? '')) <= 7 * 86400) { $n++; break; }
        }
    }
    return $n;
}

function effect67_class(string $classId, int $now, array $profiles): ?array
{
    $students = project_students_for_class($classId);
    if (count($students) < EFFECT67_MIN_GROUP) return null;
    $row = ['students' => count($students), 'active7' => effect67_pct(effect67_active($classId, $students, $profiles, $now), count($students)), 'competencies' => '–', 'paths' => '–', 'projects' => '–'];
    $subject = comp62_subject_for_class($classId);
    if (comp62_enabled_for_class($classId) && $subject !== null) {
        $summary = (array)(m62_class($classId, $now)['summary'] ?? []);
        $good = 0;
        $all = 0;
        foreach ($summary as $states) { $good += (int)($states['zvladnuto'] ?? 0) + (int)($states['upevneno'] ?? 0); $all += array_sum((array)$states); }
        $row['competencies'] = effect67_pct($good, $all);
    }
    if (p63_enabled_for_class($classId)) {
        $paths = p63_paths_for_class($classId);
        $done = 0;
        foreach ($students as $key => $s) {
            $id = p63_student_id($classId, (string)$key);
            if ($id === null) continue;
            $state = p63_state($id);
            foreach ($paths as $p) if (p63_path_progress($p, $state)['finished']) $done++;
        }
        $row['paths'] = effect67_pct($done, count($students) * max(1, count($paths)));
    }
    $cycles = proj65_for_class($classId);
    if ($cycles !== []) $row['projects'] = effect67_pct(count(array_filter($cycles, static fn(array $c): bool => in_array((string)($c['state'] ?? ''), ['graded', 'portfolio'], true))), count($cycles));
    return $row;
}

$only = null;
foreach ($argv as $a) if (str_starts_with((string)$a, '--class=')) $only = substr((string)$a, 8);
$now = time();
$profiles = storage_read(learning_profiles_path(), false);
$suppressed = 0;
printf("%-10s %7s %9s %12s %7s %9s\n", 'třída', 'žáků', 'aktivní7d', 'kompetence', 'cesty', 'projekty');
foreach (array_keys($modules) as $classId) {
    if ($only !== null && $only !== $classId) continue;
    $r = effect67_class((string)$classId, $now, $profiles);
    if ($r === null) { $suppressed++; printf("%-10s %s\n", $classId, 'potlačeno (méně než ' . EFFECT67_MIN_GROUP . ' žáků)'); continue; }
    printf("%-10s %7d %9s %12s %7s %9s\n", $classId, $r['students'], $r['active7'], $r['competencies'], $r['paths'], $r['projects']);
}
echo $suppressed === 0 ? "V67_EFFECT_REPORT_OK\n" : "V67_EFFECT_REPORT_WARN suppressed=$suppressed\n";
exit($suppressed === 0 ? 0 : 2);
