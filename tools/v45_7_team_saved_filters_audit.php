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

if(teacher_saved_filter_scope('team')!=='team')$errors[]='Team scope normalization failed.';
if(teacher_saved_filter_scope('invalid')!=='personal')$errors[]='Invalid scope must fall back to personal.';
if(teacher_saved_filter_scope_label('team')!=='Týmový')$errors[]='Team scope label failed.';
if(teacher_team_key()==='')$errors[]='Teacher team key must not be empty.';

$originalName=$_SESSION['teacher_display_name']??null;
$_SESSION['teacher_display_name']='Audit Alice';
$_SESSION['teacher_saved_filter_actor_token']=str_repeat('a',64);
$aliceKey=teacher_saved_filter_owner_key();
$teamKey=teacher_team_key();
$personal=['owner_key'=>$aliceKey,'owner_version'=>2,'scope'=>'personal','team_key'=>'','class_id'=>'class_2a'];
$team=['owner_key'=>$aliceKey,'owner_version'=>2,'owner_label'=>'Audit Alice','scope'=>'team','team_key'=>$teamKey,'class_id'=>'class_2a'];
$otherTeam=['owner_key'=>$aliceKey,'owner_version'=>2,'owner_label'=>'Audit Alice','scope'=>'team','team_key'=>hash('sha256','different-team'),'class_id'=>'class_2a'];
if(!teacher_saved_filter_visible_to_current_teacher($personal))$errors[]='Owner cannot see own personal preset.';
if(!teacher_saved_filter_visible_to_current_teacher($team))$errors[]='Owner cannot see own team preset.';
if(teacher_saved_filter_visible_to_current_teacher($otherTeam))$errors[]='Preset from another team leaked into current team.';

$_SESSION['teacher_display_name']='Audit Bob';
if(teacher_saved_filter_visible_to_current_teacher($personal))$errors[]='Personal preset visible to another teacher.';
if(!teacher_saved_filter_visible_to_current_teacher($team))$errors[]='Team preset not visible to teammate.';
if(teacher_saved_filter_is_owner($team))$errors[]='Teammate incorrectly treated as owner.';

$_SESSION['teacher_display_name']='Audit Alice';
$_SESSION['teacher_saved_filter_actor_token']=str_repeat('b',64);
if(teacher_saved_filter_is_owner($team))$errors[]='Same display name in another browser incorrectly owns v2 preset.';
$legacy=['owner_key'=>teacher_saved_filter_legacy_owner_key(),'class_id'=>'class_2a'];
if(!teacher_saved_filter_is_owner($legacy))$errors[]='Legacy personal preset compatibility failed.';

unset($_SESSION['teacher_saved_filter_actor_token']);
if($originalName===null)unset($_SESSION['teacher_display_name']);else $_SESSION['teacher_display_name']=$originalName;

$tasks=file_get_contents($root.'/teacher_tasks.php')?:'';
foreach(['teacher_team_key','teacher_team_label','teacher_saved_filter_scope','teacher_saved_filter_visible_to_current_teacher','Můžete mazat pouze vlastní uložené filtry'] as $needle)if(!str_contains($tasks,$needle))$errors[]="teacher_tasks.php missing $needle";
$dashboard=file_get_contents($root.'/teacher_class_dashboard.php')?:'';
foreach(['filter_scope','Osobní a týmové pohledy','scope-<?=e($filterScope)?>','jen autor','teacher_team_label'] as $needle)if(!str_contains($dashboard,$needle))$errors[]="teacher_class_dashboard.php missing $needle";
$bootstrap=file_get_contents($root.'/bootstrap.php')?:'';
foreach(['EDUCANET_TEACHER_TEAM_ID','EDUCANET_TEACHER_TEAM_NAME'] as $needle)if(!str_contains($bootstrap,$needle))$errors[]="bootstrap.php missing $needle";
$teacherSource=file_get_contents($root.'/teacher.php')?:'';
if(!str_contains($teacherSource,'teacher-admin-v45-7.js'))$errors[]='teacher.php does not load v45.7 teacher admin asset.';

$storageWrites=[];
foreach($storageBefore as $file=>$before){clearstatcache(true,$file);if(!is_file($file)||hash_file('sha256',$file)!==$before)$storageWrites[]=basename($file);}
if($storageWrites)$errors[]='Read-only v45.7 audit changed storage: '.implode(', ',$storageWrites);

$result=[
    'ok'=>!$errors,
    'version'=>'45.7',
    'scopes'=>['personal','team'],
    'team_isolation'=>true,
    'delete_policy'=>'owner_only_actor_v2',
    'actor_binding'=>'browser_token+teacher_name',
    'legacy_default_scope'=>'personal',
    'storage_writes'=>$storageWrites,
    'errors'=>$errors,
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
