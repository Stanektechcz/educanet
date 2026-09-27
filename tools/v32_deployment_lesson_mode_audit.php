<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/teacher_curriculum.php';
require dirname(__DIR__) . '/teacher_lesson_mode.php';

$root = dirname(__DIR__);
$errors=[];
$assert=static function(bool $ok,string $message) use (&$errors):void{if(!$ok)$errors[]=$message;};

foreach([
    'private/educanet.secrets.php','educanet.secrets.example.php','tools/install_teacher_secret.sh',
    'tools/check_install.php','teacher_lesson_mode.php','calendar.ics.php'
] as $file) $assert(is_file($root.'/'.$file),'Chybí '.$file);

$template=file_get_contents($root.'/private/educanet.secrets.php')?:'';
$assert(str_contains($template,'CHANGE_ME_WITH_A_LONG_RANDOM_SECRET'),'Private secret template nemá bezpečný placeholder.');
$templateConfig=require $root.'/private/educanet.secrets.php';
$assert(is_array($templateConfig) && (string)($templateConfig['teacher_export_key']??'')==='CHANGE_ME_WITH_A_LONG_RANDOM_SECRET','Private template nesmí obsahovat skutečný teacher key.');

$bootstrap=file_get_contents($root.'/bootstrap.php')?:'';
$assert(str_contains($bootstrap, "'course_mastery' => (int)\$metrics['lessons'] >= 28"),'Course Mastery stále nekončí na 28 lekcích.');
$runtimeBuilder=file_get_contents($root.'/tools/build_runtime_cache.php')?:'';
$assert(str_contains($bootstrap,'runtime_content_load_classes') && str_contains($runtimeBuilder,"'lessons_v30.php'"),'Course completion nezapočítává lekce 19–28 přes runtime cache.');
$assert(str_contains($bootstrap,"'course_28'"),'Chybí L28 achievement.');

foreach(['class_1a','class_2a','class_3a','class_4a'] as $cid){
    $lessons=teacher_curriculum_lessons($cid);
    $assert(count($lessons)===28,$cid.': teacher curriculum != 28');
    $lesson28=teacher_lesson_mode_find($cid,28);
    $assert(is_array($lesson28),$cid.': Lesson Mode nenajde lekci 28');
    if(is_array($lesson28)){
        $schedule=teacher_lesson_mode_schedule($lesson28);
        $assert(count($schedule)>=4,$cid.': Lesson Mode timeline je příliš krátká');
    }
}

$curriculum=file_get_contents($root.'/teacher_curriculum.php')?:'';
$assert(str_contains($curriculum,'$number>28'),'Teacher checklist toggle není rozšířený na lekci 28.');
$assert(str_contains($curriculum,'Spustit hodinu'),'Výuka nemá vstup do Lesson Mode.');

$teacher=file_get_contents($root.'/teacher.php')?:'';
$assert(str_contains($teacher,"'teach'"),'Teacher router nezná teach tab.');
$assert(str_contains($teacher,'install_teacher_secret.sh'),'Nenakonfigurovaný teacher screen nenabízí instalační skript.');

$index=edu_app_source()?:'';
$assert(str_contains($index,'Soustředěný režim'),'Student dashboard nemá Focus Mode.');
$assert(str_contains($index,'calendar.ics.php'),'Student kalendář nemá ICS export.');

$ics=file_get_contents($root.'/calendar.ics.php')?:'';
$assert(str_contains($ics,'DTSTART;VALUE=DATE'),'ICS nesmí vymýšlet konkrétní čas rozvrhu.');

$css=file_get_contents($root.'/assets/app.css')?:'';
foreach(['.lesson-mode','.focus-mode-toggle','prefers-reduced-motion'] as $needle)$assert(str_contains($css,$needle),'CSS chybí '.$needle);

if($errors){fwrite(STDERR,"v32 audit FAILED\n- ".implode("\n- ",$errors)."\n");exit(1);} 
echo "v32 deployment/lesson mode audit OK · secure template + installer + 28-course mastery + lesson mode + ICS + focus mode\n";
