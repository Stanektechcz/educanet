<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require_once $root.'/teacher_curriculum.php';
if(!function_exists('teacher_class_label')){
    function teacher_class_label(string $classId): string { return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId}; }
}
require_once $root.'/teacher_overview_dashboard.php';
require_once $root.'/teacher_class_dashboard.php';

$errors=[];$summary=[];$storageBefore=[];
foreach(glob(STORAGE_DIR.'/*.php')?:[] as $file)$storageBefore[$file]=hash_file('sha256',$file);

foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $snapshot=teacher_class_results_snapshot($classId);$priorities=['high'=>0,'medium'=>0,'normal'=>0];$statuses=[];
    foreach((array)($snapshot['students']??[]) as $row){
        if(!is_array($row)){$errors[]="$classId: invalid row";continue;}
        foreach(['status_key','priority','marker','active_tasks','done_tasks'] as $key)if(!array_key_exists($key,$row)){$errors[]="$classId: missing $key";break;}
        $priority=(string)($row['priority']??'');if(!isset($priorities[$priority]))$errors[]="$classId: invalid priority $priority";else $priorities[$priority]++;
        $status=(string)($row['status_key']??'');if(!in_array($status,['returned','attention','ok','no_result'],true))$errors[]="$classId: invalid status $status";$statuses[$status]=($statuses[$status]??0)+1;
    }
    $summary[$classId]=['students'=>(int)($snapshot['student_count']??0),'priorities'=>$priorities,'statuses'=>$statuses];
}

if(teacher_csv_safe('=2+2')!=="'=2+2")$errors[]='CSV formula injection guard failed.';
if(teacher_csv_safe('Adam')!=='Adam')$errors[]='CSV safe value changed unexpectedly.';
$dummy=['returned'=>1,'needs_work'=>0,'mastery'=>80,'avg_grade'=>2.0,'attention'=>true,'drafts'=>0,'published'=>1];
if(teacher_student_priority($dummy)!=='high')$errors[]='Returned work should resolve to high priority.';
if(teacher_student_priority($dummy,'resolved')!=='normal')$errors[]='Resolved marker should suppress automatic priority.';

$teacherSource=file_get_contents($root.'/teacher.php')?:'';
foreach(['teacher_bulk_mark','teacher_bulk_export','teacher_bulk_assign_task'] as $needle)if(!str_contains($teacherSource,$needle))$errors[]="teacher.php missing $needle";
if(!str_contains($teacherSource,'teacher-admin-v45-4.js')&&!str_contains($teacherSource,'teacher-admin-v45-5.js')&&!str_contains($teacherSource,'teacher-admin-v45-6.js')&&!str_contains($teacherSource,'teacher-admin-v45-7.js'))$errors[]='teacher.php missing compatible teacher admin JS';
$dashboardSource=file_get_contents($root.'/teacher_class_dashboard.php')?:'';
foreach(['data-class-result-status','data-class-result-priority','data-select-visible','data-row-select','teacher_class_export_csv'] as $needle)if(!str_contains($dashboardSource,$needle))$errors[]="teacher_class_dashboard.php missing $needle";
$studentSource=edu_app_source()?:'';
foreach(['teacher_task_complete','Úkoly od učitele','teacher_tasks_for_student'] as $needle)if(!str_contains($studentSource,$needle))$errors[]="index.php missing $needle";
$jsFile=is_file($root.'/assets/teacher-admin-v45-7.js')?$root.'/assets/teacher-admin-v45-7.js':(is_file($root.'/assets/teacher-admin-v45-6.js')?$root.'/assets/teacher-admin-v45-6.js':(is_file($root.'/assets/teacher-admin-v45-5.js')?$root.'/assets/teacher-admin-v45-5.js':$root.'/assets/teacher-admin-v45-4.js'));
$js=file_get_contents($jsFile)?:'';
foreach(['data-class-result-status','data-class-result-priority','data-select-visible','teacher_bulk_assign_task'] as $needle)if(!str_contains($js,$needle))$errors[]="teacher admin JS missing $needle";

$storageWrites=[];
foreach($storageBefore as $file=>$before){clearstatcache(true,$file);if(!is_file($file)||hash_file('sha256',$file)!==$before)$storageWrites[]=basename($file);}
if($storageWrites)$errors[]='Read-only v45.4 audit changed storage: '.implode(', ',$storageWrites);

$result=['ok'=>!$errors,'version'=>'45.4','classes'=>$summary,'storage_writes'=>$storageWrites,'checks'=>['csv_injection_guard'=>true,'bulk_actions'=>3,'student_task_completion'=>true],'errors'=>$errors];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
