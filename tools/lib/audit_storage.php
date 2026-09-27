<?php

declare(strict_types=1);

/**
 * Izolované úložiště a kontrolovaný vstup pro audity (v58 · F5).
 *
 * edu_audit_temp_storage() – vytvoří prázdné dočasné úložiště a nastaví EDUCANET_STORAGE_DIR.
 *   Volat PŘED `require bootstrap.php` (bootstrap podle proměnné definuje STORAGE_DIR). Po skončení
 *   auditu se adresář smaže. Audit tak nikdy nečte ani nezapisuje ostrá data žáků.
 * edu_audit_open_sessions() – otevře hodiny s kódem pro pevné vyučovací datum stejně jako učitelská
 *   záložka Hodina (sess53_open). Kontroly „hodiny na daný den“ pak nezávisí na tom, jaký je dnes den.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Pevný vyučovací den pro kontroly hodin (středa – vyučovací den všech tříd ve školním roce 2026/27). */
const EDU_AUDIT_TEACHING_DATE = '2026-09-16';

function edu_audit_temp_storage(string $tag): string
{
    if (defined('STORAGE_DIR')) throw new LogicException('edu_audit_temp_storage() musí běžet před bootstrap.php.');
    $dir = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-audit-' . preg_replace('/[^a-z0-9_-]/i', '', $tag) . '-' . bin2hex(random_bytes(6));
    if (!mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Nelze vytvořit dočasné úložiště auditu.');
    putenv('EDUCANET_STORAGE_DIR=' . $dir);
    $_ENV['EDUCANET_STORAGE_DIR'] = $dir;
    register_shutdown_function(static function () use ($dir): void { edu_audit_remove_dir($dir); });
    return $dir;
}

function edu_audit_remove_dir(string $dir): void
{
    if (!is_dir($dir) || !str_contains($dir, 'educanet-audit-')) return;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $item) { $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname()); }
    @rmdir($dir);
}

/**
 * Otevře hodiny pro všechny třídy na $date (kontrolovaný vstup). Číslo lekce se určí ze školního roku
 * stejně jako v session_v53_teacher.php; třídy v $intakeClasses dostanou seznamovací dotazník.
 * Vyžaduje načtené: bootstrap.php, runtime_content.php, tutorial_v52.php, session_v53.php.
 */
function edu_audit_open_sessions(array $modules, array $schoolYear, string $date = EDU_AUDIT_TEACHING_DATE, array $intakeClasses = ['class_1a']): array
{
    // Učitelská záložka má načtenou v56 (zadání hodiny = kroky projektu lekce) – stejně i tady.
    require_once dirname(__DIR__, 2) . '/learning_v56.php';
    $opened = [];
    foreach ($modules as $classId => $module) {
        $classId = (string)$classId;
        $rt = runtime_content_load_classes([$classId]);
        $lessons = tut52_lessons($classId, $module, $rt['nextLessons'], $rt['extendedLessons'], $schoolYear, null);
        $lessonNo = 0;
        foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
            if (is_array($row) && (string)($row['date'] ?? '') === $date && (string)($row['status'] ?? '') === 'teaching') { $lessonNo = (int)($row['lesson_number'] ?? 0); break; }
        }
        if ($lessonNo === 0) $lessonNo = (int)((tut52_current_lesson($lessons)['number'] ?? 1));
        $lesson = $lessons[$lessonNo] ?? ['title' => 'Hodina', 'goal' => '', 'steps' => []];
        $raw = null;
        foreach ((array)($rt['extendedLessons'][$classId] ?? []) as $cand) if (is_array($cand) && (int)($cand['number'] ?? 0) === $lessonNo) { $raw = $cand; break; }
        if ($raw === null && $lessonNo === 2) $raw = $rt['nextLessons'][$classId] ?? null;
        $kind = in_array($classId, $intakeClasses, true) ? 'intake' : 'work';
        $opened[$classId] = sess53_open($classId, $date, $modules, is_array($raw) ? $raw : [], $kind, [
            'lesson_number' => $lessonNo,
            'title' => $kind === 'intake' ? 'Seznamovací dotazník' : 'Lekce ' . $lessonNo . ' · ' . (string)($lesson['title'] ?? 'Hodina'),
            'goal' => $kind === 'intake' ? 'Poznáme se a nastavíme výuku podle vás.' : (string)($lesson['goal'] ?? ''),
        ]);
    }
    return $opened;
}
