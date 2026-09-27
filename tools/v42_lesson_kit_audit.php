<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/bootstrap.php';
$resources=require dirname(__DIR__) . '/learning_resources.php';
$errors=[];$summary=[];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){$lessons=v42_lessons_for_class($classId);$summary[$classId]=count($lessons);if(count($lessons)!==28)$errors[]="$classId má ".count($lessons)." lekcí místo 28.";foreach($lessons as $lesson){$n=(int)($lesson['number']??0);$pack=v42_lesson_pack($classId,$lesson,$modules[$classId],$resources,'');if(count((array)$pack['videos'])<1)$errors[]="$classId L$n nemá video.";if(count((array)$pack['slides'])<8)$errors[]="$classId L$n má málo slidů.";if(count((array)$pack['prompts'])<6)$errors[]="$classId L$n má málo promptů.";if(count((array)$pack['exam']['oral'])<5)$errors[]="$classId L$n má málo exam otázek.";if(empty($pack['materials']['worksheet']))$errors[]="$classId L$n nemá worksheet.";$path=dirname(__DIR__).'/materials/lesson_kits/'.$classId.'/'.sprintf('lesson_%02d.md',$n);if(!is_file($path))$errors[]="$classId L$n nemá vygenerovaný lesson kit.";}}
$teacherPhp=file_get_contents(dirname(__DIR__).'/teacher.php')?:'';$teacherMode=file_get_contents(dirname(__DIR__).'/teacher_lesson_mode.php')?:'';
if(!function_exists('v42_teacher_resource_save')||!function_exists('v42_teacher_resource_remove'))$errors[]='Chybí teacher resource override API.';
if(!str_contains($teacherPhp,'v42_lesson_resource_save')||!str_contains($teacherPhp,'v42_lesson_resource_remove'))$errors[]='Teacher POST actions pro video override nejsou zapojené.';
if(!str_contains($teacherMode,'data-v42-teacher-deck-open')||!str_contains($teacherMode,'data-v42-deck-slide'))$errors[]='Teacher fullscreen presentation není zapojená.';
if($errors){fwrite(STDERR,"v42 Lesson Kit audit FAILED\n- ".implode("\n- ",$errors)."\n");exit(1);}
echo "v42 Lesson Kit audit OK · ";foreach($summary as $k=>$v)echo "$k=$v ";echo "· 112/112 lessons have video + slides + materials + prompts + exam prep · teacher overrides + presentation OK\n";
