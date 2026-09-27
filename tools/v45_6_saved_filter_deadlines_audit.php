<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root=dirname(__DIR__);
require $root.'/bootstrap.php';
if(!function_exists('teacher_class_label')){
    function teacher_class_label(string $classId): string { return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId}; }
}
require_once $root.'/teacher_curriculum.php';
require_once $root.'/teacher_overview_dashboard.php';
require_once $root.'/teacher_class_dashboard.php';

$errors=[];$storageBefore=[];
foreach(glob(STORAGE_DIR.'/*.php')?:[] as $file)$storageBefore[$file]=hash_file('sha256',$file);

foreach([
    ['active','active'],['done','done'],['no_task','no_task'],['invalid','all'],
] as [$input,$expected]) if(teacher_saved_filter_task_status($input)!==$expected)$errors[]="Task status normalization failed for $input";
foreach([
    ['overdue','overdue'],['today','today'],['next3','next3'],['next7','next7'],['no_due','no_due'],['invalid','all'],
] as [$input,$expected]) if(teacher_saved_filter_task_due($input)!==$expected)$errors[]="Task due normalization failed for $input";

$summary=['active'=>2,'done'=>1,'total'=>3,'overdue'=>1,'due_today'=>0,'due_3'=>1,'due_7'=>1,'no_due_active'=>1];
if(!teacher_task_summary_matches($summary,'active','overdue'))$errors[]='Active overdue task match failed.';
if(!teacher_task_summary_matches($summary,'done','next7'))$errors[]='Cross-dimension task match failed.';
if(teacher_task_summary_matches($summary,'no_task','all'))$errors[]='No-task match false positive.';

$filter=['class_id'=>'class_2a','status'=>'returned','priority'=>'high','task_status'=>'active','task_due'=>'overdue'];
if(!teacher_saved_filter_match($filter,'class_2a','returned','high','active','overdue'))$errors[]='Extended saved-filter match failed.';
if(teacher_saved_filter_match($filter,'class_2a','returned','high','active','next7'))$errors[]='Extended saved-filter deadline isolation failed.';
$link=teacher_saved_filter_link('class_results',$filter);
foreach(['tab=class_results','class=class_2a','status=returned','priority=high','task_status=active','task_due=overdue'] as $needle)if(!str_contains($link,$needle))$errors[]="Saved-filter URL missing $needle";

foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $snapshot=teacher_class_results_snapshot($classId);
    foreach((array)($snapshot['students']??[]) as $row){
        if(!array_key_exists('task_summary',$row)){$errors[]="$classId: task_summary missing";break;}
    }
    $filtered=teacher_class_filter_students((array)($snapshot['students']??[]),'all','all','active','overdue');
    foreach($filtered as $row)if(!teacher_task_summary_matches((array)($row['task_summary']??[]),'active','overdue'))$errors[]="$classId: server task filter mismatch";
}

$teacherSource=file_get_contents($root.'/teacher.php')?:'';
foreach(['task_status','task_due'] as $needle)if(!str_contains($teacherSource,$needle))$errors[]="teacher.php missing $needle";
$dashboard=file_get_contents($root.'/teacher_class_dashboard.php')?:'';
foreach(['data-class-task-status','data-class-task-due','data-filter-task-status','data-filter-task-due','teacher_task_deadline_label'] as $needle)if(!str_contains($dashboard,$needle))$errors[]="teacher_class_dashboard.php missing $needle";
$jsFile=is_file($root.'/assets/teacher-admin-v45-7.js')?$root.'/assets/teacher-admin-v45-7.js':$root.'/assets/teacher-admin-v45-6.js';
$js=file_get_contents($jsFile)?:'';
foreach(['taskStatus','taskDue','task_status','task_due','taskOverdue','taskNext7'] as $needle)if(!str_contains($js,$needle))$errors[]="teacher admin compatible JS missing $needle";

$storageWrites=[];
foreach($storageBefore as $file=>$before){clearstatcache(true,$file);if(!is_file($file)||hash_file('sha256',$file)!==$before)$storageWrites[]=basename($file);}
if($storageWrites)$errors[]='Read-only v45.6 audit changed storage: '.implode(', ',$storageWrites);

$result=['ok'=>!$errors,'version'=>'45.6','saved_filter_fields'=>['class','status','priority','task_status','task_due'],'deadline_windows'=>['overdue','today','next3','next7','no_due'],'cross_views'=>['class_overview','class_results'],'storage_writes'=>$storageWrites,'errors'=>$errors];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
