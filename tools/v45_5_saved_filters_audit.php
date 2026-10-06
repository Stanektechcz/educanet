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

$filter=['class_id'=>'class_2a','status'=>'returned','priority'=>'high'];
if(!teacher_saved_filter_match($filter,'class_2a','returned','high'))$errors[]='Saved filter match failed.';
if(teacher_saved_filter_match($filter,'class_3a','returned','high'))$errors[]='Saved filter class isolation failed.';
if(teacher_saved_filter_status('invalid')!=='all')$errors[]='Status normalization failed.';
if(teacher_saved_filter_priority('invalid')!=='all')$errors[]='Priority normalization failed.';
$link=teacher_saved_filter_link('class_results',$filter);
foreach(['tab=class_results','class=class_2a','status=returned','priority=high'] as $needle)if(!str_contains($link,$needle))$errors[]="Saved filter URL missing $needle";

foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $snapshot=teacher_class_results_snapshot($classId);
    $students=(array)($snapshot['students']??[]);
    $filtered=teacher_class_filter_students($students,'no_result','medium');
    foreach($filtered as $row){
        if((string)($row['status_key']??'')!=='no_result'||(string)($row['priority']??'')!=='medium')$errors[]="$classId: server-side overview filter mismatch";
    }
}

$teacherSource=file_get_contents($root.'/teacher.php')?:'';
foreach(['teacher_saved_filter_save','teacher_saved_filter_delete'] as $needle)if(!str_contains($teacherSource,$needle))$errors[]="teacher.php missing $needle";
if(!str_contains($teacherSource,'teacher-admin-v45-5.js')&&!str_contains($teacherSource,'teacher-admin-v45-6.js')&&!str_contains($teacherSource,'teacher-admin-v45-7.js'))$errors[]='teacher.php missing compatible saved-filter admin JS';
$dashboard=file_get_contents($root.'/teacher_class_dashboard.php')?:'';
// v69: souhrn „Studenti podle aktivního filtru“ byl jen v vyřazeném Přehledu třídy (class_overview).
foreach(['teacher_render_saved_filters','data-saved-filter-panel','data-save-filter-form'] as $needle)if(!str_contains($dashboard,$needle))$errors[]="teacher_class_dashboard.php missing $needle";
$tasks=file_get_contents($root.'/teacher_tasks.php')?:'';
foreach(['teacher_saved_filters_path','teacher_saved_filters_for_current_teacher','teacher_saved_filter_save','teacher_saved_filter_delete'] as $needle)if(!str_contains($tasks,$needle))$errors[]="teacher_tasks.php missing $needle";
$jsFile=is_file($root.'/assets/teacher-admin-v45-7.js')?$root.'/assets/teacher-admin-v45-7.js':(is_file($root.'/assets/teacher-admin-v45-6.js')?$root.'/assets/teacher-admin-v45-6.js':$root.'/assets/teacher-admin-v45-5.js');
$js=file_get_contents($jsFile)?:'';
foreach(['syncSavedFilters','data-save-filter-form','data-saved-filter-item'] as $needle)if(!str_contains($js,$needle))$errors[]="teacher admin JS missing $needle";

$storageWrites=[];
foreach($storageBefore as $file=>$before){clearstatcache(true,$file);if(!is_file($file)||hash_file('sha256',$file)!==$before)$storageWrites[]=basename($file);}
if($storageWrites)$errors[]='Read-only v45.5 audit changed storage: '.implode(', ',$storageWrites);

$result=['ok'=>!$errors,'version'=>'45.5','saved_filter_fields'=>['class','status','priority'],'cross_views'=>['class_results'],'storage_writes'=>$storageWrites,'errors'=>$errors];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
