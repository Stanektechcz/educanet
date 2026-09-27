<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
// v59: spuštěno napřímo (ne přes run_audits.php s kopií úložiště) → vlastní prázdné dočasné úložiště, nikdy ostrá storage/.
if ((string)getenv('EDUCANET_STORAGE_DIR') === '') { require_once __DIR__ . '/lib/audit_storage.php'; edu_audit_temp_storage('v45_2'); }
require $root . '/bootstrap.php';
if (!function_exists('teacher_class_label')) {
    function teacher_class_label(string $classId): string {
        return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId};
    }
}
require_once $root . '/teacher_curriculum.php';
require_once $root . '/teacher_overview_dashboard.php';

$watched = [
    STORAGE_DIR . '/skill_progress.json.php',
    STORAGE_DIR . '/skill_branches.json.php',
    STORAGE_DIR . '/skill_evidence.json.php',
    STORAGE_DIR . '/project_grades.json.php',
    STORAGE_DIR . '/project_groups.json.php',
];
$hashesBefore=[];
foreach($watched as $path) $hashesBefore[$path]=is_file($path)?hash_file('sha256',$path):null;

$started=microtime(true);
$rows=[];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $t=microtime(true);
    $snapshot=teacher_overview_class_snapshot($classId);
    $rows[$classId]=[
        'duration_ms'=>round((microtime(true)-$t)*1000,2),
        'students'=>(int)($snapshot['students']??0),
        'lessons'=>(int)($snapshot['lessons_total']??0),
        'groups'=>(int)($snapshot['groups_total']??0),
    ];
}
$totalMs=round((microtime(true)-$started)*1000,2);

$writes=[];
foreach($watched as $path){
    $after=is_file($path)?hash_file('sha256',$path):null;
    if($after!==$hashesBefore[$path])$writes[]=basename($path);
}

$errors=[];
if($writes)$errors[]='Read-only overview modified storage: '.implode(', ',$writes);
if(array_sum(array_column($rows,'lessons'))!==112)$errors[]='Expected 112 lessons in global overview.';

$teacherPhp=file_get_contents($root.'/teacher.php')?:'';
if(!str_contains($teacherPhp,'teacher_require_modules'))$errors[]='Lazy teacher module loader is missing.';
if(!str_contains($teacherPhp,"\$teacherRequestTab==='teach'"))$errors[]='Simulation assets are not scoped to Lesson Mode.';
// ř. 53: teacher-admin-v45-2.js se sám o sobě už nenačítá (viz PLAN_F4_PROD.md A.2) – teacher.php
// dnes vždy vkládá konsolidovaný teacher-admin-v[\d-]+.js (dnes v45-7); ověřujeme, že ten skutečně
// existuje a je opravdu ve <script> tagu, ne jen zmíněný.
if(!preg_match('~<script src="assets/(teacher-admin-v[\d-]+\.js)\?v=[\d.]+"~',$teacherPhp,$adminJsMatch)){
    $errors[]='No consolidated teacher-admin-v*.js <script> tag found in teacher.php.';
} elseif(!is_file($root.'/assets/'.$adminJsMatch[1])){
    $errors[]='Loaded teacher admin JS file is missing on disk: '.$adminJsMatch[1];
}

$result=[
    'ok'=>!$errors,
    'version'=>'45.2',
    'overview_total_ms'=>$totalMs,
    'classes'=>$rows,
    'storage_writes'=>$writes,
    'apcu_available'=>educanet_apcu_enabled(),
    'errors'=>$errors,
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
