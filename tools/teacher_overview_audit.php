<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/teacher_curriculum.php';
if (!function_exists('teacher_class_label')) {
    function teacher_class_label(string $classId): string { return match($classId){'class_1a'=>'1.A Grafika','class_2a'=>'2.A Grafika','class_3a'=>'3.A SOSPS','class_4a'=>'4.A SOSPS',default=>$classId}; }
}
require dirname(__DIR__) . '/teacher_overview_dashboard.php';

$errors=[];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $s=teacher_overview_class_snapshot($classId);
    if((int)$s['lessons_total']!==28)$errors[]="$classId: lessons != 28";
    if((int)$s['content_ready']!==28)$errors[]="$classId: content readiness != 28";
    if((int)$s['checklist_total']!==29)$errors[]="$classId: checklist != 29";
    if(trim((string)$s['capstone_name'])==='')$errors[]="$classId: missing capstone";
    if(!$s['mastery_branches'])$errors[]="$classId: missing mastery branches";
    echo teacher_class_label($classId).': lessons='.$s['content_ready'].'/28, prepared='.$s['prepared'].'/28, checklist='.$s['checklist_done'].'/'.$s['checklist_total'].', projects='.$s['projects_total'].', teams='.$s['groups_total'].', mastery='.($s['mastery_overall']===null?'no-data':$s['mastery_overall'].'%').PHP_EOL;
}
if($errors){foreach($errors as $e)fwrite(STDERR,"ERROR: $e\n");exit(1);} echo "Teacher global overview audit OK\n";
