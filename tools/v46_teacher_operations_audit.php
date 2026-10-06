<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require_once $root.'/teacher_class_dashboard.php';
require_once $root.'/teacher_operations_v46.php';

function v46_storage_fingerprint(string $dir): array {
    $out=[];foreach(glob($dir.'/*.php')?:[] as $file)$out[basename($file)]=[filesize($file)?:0,filemtime($file)?:0,hash_file('sha256',$file)?:''];ksort($out);return $out;
}
$errors=[];$before=v46_storage_fingerprint(STORAGE_DIR);
$attention=teacher_ops_attention_center();
if(count((array)($attention['classes']??[]))!==4)$errors[]='Attention Center nemá 4 třídy.';
$analytics=teacher_ops_class_analytics('class_2a');
if(count((array)($analytics['weeks']??[]))!==8)$errors[]='Class Analytics nemá 8 týdnů.';
$lessons=teacher_ops_lesson_intelligence('class_2a');
if(count($lessons)!==28)$errors[]='Lesson Intelligence nemá 28 lekcí.';
$students=project_students_for_class('class_2a');$firstKey=(string)(array_key_first($students)??'');
if($firstKey==='')$errors[]='2.A nemá testovacího studenta.'; else {$profile=teacher_ops_student360('class_2a',$firstKey);if((string)($profile['student_key']??'')!==$firstKey)$errors[]='Student 360° nevrátil správného studenta.';}
$snapshot=teacher_class_results_snapshot('class_2a');$sample=(array)(($snapshot['students']??[])[0]??[]);
$smart=['class_id'=>'class_2a','status'=>'all','priority'=>'all','task_status'=>'all','task_due'=>'all','rule_mode'=>'all','rules'=>[['field'=>'active_tasks','op'=>'gte','value'=>'0']]];
if($sample&&!teacher_saved_filter_matches_student($smart,$sample))$errors[]='Smart filter AND pravidlo selhalo.';
$after=v46_storage_fingerprint(STORAGE_DIR);$writes=[];foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $name)if(($before[$name]??null)!==($after[$name]??null))$writes[]=$name;
if($writes)$errors[]='Read-only operace změnily storage: '.implode(', ',$writes);
$teacher=file_get_contents($root.'/teacher.php')?:'';$ops=file_get_contents($root.'/teacher_operations_v46.php')?:'';$views=file_get_contents($root.'/teacher_operations_views_v46.php')?:'';$js=file_get_contents($root.'/assets/teacher-admin-v46.js')?:'';
foreach([
    'Attention Center'=>"tab==='attention'",'Student 360'=>"tab==='student360'",'Interventions'=>"tab==='interventions'",'Analytics'=>"tab==='analytics'",'Roles'=>"tab==='team_admin'"
] as $label=>$needle)if(!str_contains($teacher,$needle))$errors[]="$label není zapojen v teacher.php.";
foreach(['teacher_ops_attention_center','teacher_ops_student360','teacher_ops_intervention_create','teacher_ops_class_analytics','teacher_ops_lesson_intelligence','teacher_ops_automation_tick','teacher_ops_bulk_update_tasks','teacher_ops_team_member_set_role'] as $fn)if(!function_exists($fn))$errors[]='Chybí '.$fn.'.';
if(!str_contains($js,'data-command-backdrop')&&!str_contains($js,'data-command-open'))$errors[]='Command Palette JS marker chybí.';
if(!is_file($root.'/tools/v46_automation_tick.php'))$errors[]='Chybí CLI automation tick.';
// v69: Smart filtry a Automatizace (záložky filters, automations) vyřazeny z rozhraní; knihovna (uložené filtry, cron tick) zůstává.
if(str_contains($teacher,'teacher_render_automations')||str_contains($views,'teacher_render_filters_manager'))$errors[]='Vyřazené pohledy Automatizace / Smart filtry jsou stále v kódu.';
$result=['ok'=>!$errors,'version'=>'46.0','features'=>['attention_center'=>true,'student_360'=>true,'interventions'=>true,'smart_saved_filters'=>true,'notifications_automations'=>true,'class_analytics'=>true,'lesson_intelligence'=>true,'bulk_actions_2'=>true,'command_palette'=>true,'roles_permissions'=>true],'class_2a_students'=>count($students),'analytics_weeks'=>count((array)($analytics['weeks']??[])),'lesson_intelligence'=>count($lessons),'read_only_storage_writes'=>$writes,'errors'=>$errors];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
