<?php

declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require_once $root.'/teacher_class_dashboard.php';
require_once $root.'/teacher_operations_v46.php';
require_once $root.'/teacher_operations_plus_v46_1.php';
require_once $root.'/teacher_operations_control_v46_2.php';
require_once $root.'/teacher_operations_control_views_v46_2.php';
function v462_fp(string $dir):array{$o=[];foreach(glob($dir.'/*.php')?:[] as $f)$o[basename($f)]=[filesize($f)?:0,filemtime($f)?:0,hash_file('sha256',$f)?:''];ksort($o);return $o;}
$errors=[];$before=v462_fp(STORAGE_DIR);
$digest=teacher_ops_review_digest(null);if(!isset($digest['current']['critical']))$errors[]='Review digest nemá critical metriku.';
$policy=teacher_ops_sla_policy();if(!isset($policy['task_high'],$policy['task_critical'],$policy['followup_critical']))$errors[]='Chybí SLA politika.';$esc=teacher_ops_escalations(null);if(!is_array($esc))$errors[]='Escalation engine nevrátil pole.';
$planner=teacher_ops_planner(14);if((int)($planner['days']??0)!==14)$errors[]='Planner nemá 14denní horizont.';
$report=teacher_ops_cross_class_report();if(count($report)!==4)$errors[]='Cross-class report neobsahuje 4 třídy.';
$q=teacher_ops_data_quality();if(!isset($q['counts']['high'],$q['counts']['medium'],$q['counts']['low']))$errors[]='Data Quality nemá severity counts.';
$rendered=teacher_ops_message_render('Ahoj {{student}}, {{class}}, {{teacher}}.','class_2a',(string)(array_key_first(project_students_for_class('class_2a'))??''));if(str_contains($rendered,'{{student}}')||str_contains($rendered,'{{class}}'))$errors[]='Komunikační placeholdery nebyly nahrazeny.';
$after=v462_fp(STORAGE_DIR);$writes=[];foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $n)if(($before[$n]??null)!==($after[$n]??null))$writes[]=$n;if($writes)$errors[]='Read-only v46.2 změnilo storage: '.implode(', ',$writes);
$teacher=file_get_contents($root.'/teacher.php')?:'';$views=file_get_contents($root.'/teacher_operations_control_views_v46_2.php')?:'';$js=file_get_contents($root.'/assets/teacher-admin-v46.js')?:'';
foreach(["tab==='control'","tab==='reports'","tab==='communications'","tab==='quality'",'teacher_ops_report_export','teacher_review_ack'] as $needle)if(!str_contains($teacher,$needle))$errors[]='teacher.php chybí marker '.$needle;
foreach(['teacher_render_control_tower','teacher_render_reports','teacher_render_communications','teacher_render_quality'] as $needle)if(!str_contains($views,$needle))$errors[]='Views chybí '.$needle;
if(!str_contains($js,'data-copy-message'))$errors[]='Chybí copy handler komunikačního draftu.';
echo json_encode(['ok'=>!$errors,'version'=>'46.2','features'=>['review_digest'=>true,'sla_escalations'=>true,'configurable_sla'=>true,'planner'=>true,'communication_templates'=>true,'cross_class_reporting'=>true,'data_quality'=>true,'csv_json_export'=>true],'classes'=>count($report),'escalations'=>count($esc),'planner_events'=>count((array)($planner['events']??[])),'quality_findings'=>count((array)($q['issues']??[])),'read_only_storage_writes'=>$writes,'errors'=>$errors],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
