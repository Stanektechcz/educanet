<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/assessment_visuals.php';
require dirname(__DIR__).'/reality_demos.php';

$root=dirname(__DIR__);
$errors=[];$total=0;$scenes=[];$classes=[];
foreach(glob($root.'/cache/runtime/class_*.php')?:[] as $file){
    $data=require $file; $class=basename($file,'.php'); $topics=[];
    $collect=function(array $lesson) use (&$topics):void {foreach((array)($lesson['knowledge']??[]) as $t)$topics[(string)$t]=true;};
    if(is_array($data['nextLesson']??null))$collect($data['nextLesson']);
    foreach((array)($data['extendedLessons']??[]) as $lesson)if(is_array($lesson))$collect($lesson);
    $count=0;
    foreach(array_keys($topics) as $topic){
        $article=is_array($modules[$class]['knowledgebase'][$topic]??null)?$modules[$class]['knowledgebase'][$topic]:['title'=>$topic,'summary'=>''];
        $spec=reality_demo_spec($class,$topic,$article);$total++;$count++;
        $scene=(string)($spec['scene']??'');$scenes[$scene]=($scenes[$scene]??0)+1;
        if($scene==='')$errors[]="$class/$topic missing scene";
        if(trim((string)($spec['brief']??''))==='')$errors[]="$class/$topic missing real brief";
        if(count((array)($spec['choices']??[]))<3)$errors[]="$class/$topic has fewer than 3 decisions";
        if(trim((string)($spec['proof']??''))==='')$errors[]="$class/$topic missing proof explanation";
        if(trim((string)($spec['not_proof']??''))==='')$errors[]="$class/$topic missing evidence boundary";
    }
    $classes[$class]=$count;
}
$index=edu_app_source()?:'';$js=file_get_contents($root.'/assets/app.js')?:'';$css=file_get_contents($root.'/assets/app.css')?:'';$teacher=file_get_contents($root.'/teacher_lesson_mode.php')?:'';$teacherShell=file_get_contents($root.'/teacher.php')?:'';
foreach(['render_reality_demo','Reality demo · nejdřív situace, potom princip'] as $needle)if(!str_contains($index,$needle))$errors[]="index missing $needle";
foreach(['data-reality-demo','document.startViewTransition','reality:decision','IntersectionObserver','educanet-reality-motion','kb:visual-complete'] as $needle)if(!str_contains($js,$needle))$errors[]="JS missing $needle";
foreach(['.reality-demo','container-type:inline-size','content-visibility:auto','@media(prefers-reduced-motion:reduce)','.reality-motion-toggle','.lesson-reality-prompts'] as $needle)if(!str_contains($css,$needle))$errors[]="CSS missing $needle";
foreach(['Reality demo','Situace pro projektor / diskusi','Cesta pro učitele','reality_demo_spec'] as $needle)if(!str_contains($teacher,$needle))$errors[]="teacher lesson mode missing $needle";
if(!preg_match("/require(?:_once)?\s*__DIR__\s*\.\s*['\"]\/reality_demos\.php['\"]/",$teacherShell))$errors[]='teacher shell missing reality_demos.php require';
if(!str_contains($teacherShell,'assets/app.css?v='))$errors[]='teacher shell missing assets/app.css?v=';
if($errors){foreach($errors as $e)fwrite(STDERR,"ERROR: $e\n");exit(1);} 
echo 'v35 reality demos OK · topics='.$total.' · classes='.json_encode($classes,JSON_UNESCAPED_UNICODE).' · scenes='.json_encode($scenes,JSON_UNESCAPED_UNICODE)."\n";
