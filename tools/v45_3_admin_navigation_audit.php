<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
require $root . '/bootstrap.php';
require $root . '/teacher_curriculum.php';
if (!function_exists('teacher_class_label')) {
    function teacher_class_label(string $classId): string { return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId}; }
}
require $root . '/teacher_overview_dashboard.php';
require $root . '/teacher_class_dashboard.php';

$errors = [];
$storageBefore = [];
foreach (glob(STORAGE_DIR . '/*.php') ?: [] as $file) $storageBefore[$file] = [filesize($file), filemtime($file), hash_file('sha256',$file)];

$summary = [];
foreach (['class_1a','class_2a','class_3a','class_4a'] as $classId) {
    $start = hrtime(true);
    $overview = teacher_overview_class_snapshot($classId);
    $results = teacher_class_results_snapshot($classId);
    $durationMs = (hrtime(true)-$start)/1e6;
    if ((int)($overview['lessons_total'] ?? 0) !== 28) $errors[] = "$classId: expected 28 lessons";
    if (count((array)($results['projects'] ?? [])) !== 4) $errors[] = "$classId: expected 4 projects in results";
    if ((int)($results['student_count'] ?? -1) !== count(project_students_for_class($classId))) $errors[] = "$classId: student count mismatch";
    foreach ((array)($results['students'] ?? []) as $row) {
        if (!array_key_exists('avg_project_pct',$row) || !array_key_exists('avg_grade',$row) || !array_key_exists('mastery',$row) || !array_key_exists('activity',$row)) {
            $errors[] = "$classId: incomplete student result row"; break;
        }
    }
    $summary[$classId] = ['students'=>(int)$results['student_count'],'projects'=>count((array)$results['projects']),'duration_ms'=>round($durationMs,2)];
}

$storageWrites = [];
foreach ($storageBefore as $file => $before) {
    clearstatcache(true,$file);
    $after = [filesize($file), filemtime($file), hash_file('sha256',$file)];
    if ($before !== $after) $storageWrites[] = basename($file);
}
if ($storageWrites) $errors[] = 'Read-only admin snapshots changed storage: ' . implode(', ', $storageWrites);

$teacherSource = file_get_contents($root . '/teacher.php') ?: '';
// v68: staré menu (teacher-nav-groups, teacher-contextbar) nahradil rámec v68 (teacher_shell_v68.php); kontrola míří na nové vykreslovací funkce.
foreach (['class_overview','class_results','teacher68_stylesheets_html','teacher68_topbar_html','teacher68_nav_html','teacher68_class_bar_html'] as $needle) if (!str_contains($teacherSource,$needle)) $errors[] = "teacher.php missing $needle";
// ř. 46-47: teacher-admin-v45-3.js se sám o sobě už nenačítá – čteme JS, který teacher.php skutečně
// vkládá (konsolidovaný teacher-admin-v[\d-]+.js, dnes v45-7).
if (!preg_match('~<script src="assets/(teacher-admin-v[\d-]+\.js)\?v=[\d.]+"~', $teacherSource, $adminJsMatch)) {
    $errors[] = 'No consolidated teacher-admin-v*.js <script> tag found in teacher.php';
    $js = '';
    $adminJsName = '(none)';
} else {
    $adminJsName = $adminJsMatch[1];
    $js = file_get_contents($root . '/assets/' . $adminJsName) ?: '';
}
foreach (['teacher-nav-group','data-class-results-table','data-class-result-search'] as $needle) if (!str_contains($js,$needle)) $errors[] = "teacher admin JS ($adminJsName) missing $needle";

$result = ['ok'=>!$errors,'version'=>'45.3','classes'=>$summary,'storage_writes'=>$storageWrites,'errors'=>$errors];
echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($errors ? 1 : 0);
