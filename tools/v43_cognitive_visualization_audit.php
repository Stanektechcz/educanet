<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/runtime_content.php';
$runtime=runtime_content_load_classes(['class_1a','class_2a','class_3a','class_4a']);$nextLessons=$runtime['nextLessons'];$extendedLessons=$runtime['extendedLessons'];
$errors=[];$counts=[];$families=[];$ids=[];$total=0;
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){$counts[$classId]=0;foreach(v42_lessons_for_class($classId) as $lesson){$total++;$counts[$classId]++;$s=cv43_lab_spec($classId,$lesson,$modules[$classId]);$id=(string)($s['id']??'');if($id===''||isset($ids[$id]))$errors[]="$classId L{$lesson['number']}: invalid/duplicate id";$ids[$id]=true;$family=(string)($s['family']??'');$families[$family]=($families[$family]??0)+1;if(!in_array((string)($s['renderer']??''),['design','flow','map','metrics','timeline','incident'],true))$errors[]="$id: renderer";if(count((array)($s['layers']??[]))<4)$errors[]="$id: <4 layers";if(count((array)($s['timeline']??[]))<5)$errors[]="$id: <5 timeline steps";if(count((array)($s['options']??[]))<3)$errors[]="$id: <3 prediction options";$correct=(int)($s['correct']??-1);if(!isset($s['options'][$correct]))$errors[]="$id: invalid correct";if(count((array)($s['model']['expected']??[]))<5)$errors[]="$id: model too small";if(trim((string)($s['problem']??''))==='')$errors[]="$id: empty problem";if(trim((string)($s['transfer']??''))==='')$errors[]="$id: empty transfer";if(empty($s['memory']['sentence'])||empty($s['memory']['trap']))$errors[]="$id: memory snapshot";if(empty($s['teacher']['ask']))$errors[]="$id: teacher explainer";}}
foreach($counts as $c=>$n)if($n!==28)$errors[]="$c has $n labs, expected 28";
foreach(['cognitive_visualization_v43.php','cognitive_visualization_views_v43.php','assets/cognitive-v43.css','assets/cognitive-v43.js'] as $file)if(!is_file(dirname(__DIR__).'/'.$file))$errors[]="missing $file";
if(!str_contains(edu_app_source()?:'','view === \'cognitive_lab\''))$errors[]='cognitive_lab route missing';
if(!str_contains(file_get_contents(dirname(__DIR__).'/teacher_lesson_mode.php')?:'','cv43_render_teacher_explainer'))$errors[]='teacher explainer missing';
echo "v43 Cognitive Visualization Audit\n";echo "Labs: $total / 112\n";foreach($counts as $c=>$n)echo "$c: $n\n";echo "Families: ";ksort($families);foreach($families as $f=>$n)echo "$f=$n ";echo "\n";
if($errors){foreach($errors as $e)echo "[FAIL] $e\n";exit(1);}echo "[OK] 112/112 lesson-specific cognitive labs, renderers, prediction, timeline, model, transfer and teacher explainer.\n";
