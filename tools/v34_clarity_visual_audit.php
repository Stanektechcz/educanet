<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__).'/assessment_visuals.php';

$root=dirname(__DIR__);
$errors=[];
$index=edu_app_source()?:'';
$css=file_get_contents($root.'/assets/app.css')?:'';
$js=file_get_contents($root.'/assets/app.js')?:'';

foreach([
    'lesson-story-demo'=>'lesson-specific demo shell',
    'render_lesson_visual_overview'=>'lesson demo renderer',
    'course-full-map'=>'collapsed full course map',
    'main-menu-more'=>'progressive navigation',
] as $needle=>$label){ if(!str_contains($index,$needle) && !str_contains($css,$needle))$errors[]="$label missing"; }
if(str_contains($index,'<?php render_kb_concept_loop('))$errors[]='generic concept-loop is still rendered inside KB visual step';
if(!str_contains($index,"apply(saved===null?true:saved==='1')"))$errors[]='focus mode is not default-on for first visit';
if(!str_contains($js,'data-lesson-story-demo'))$errors[]='lesson story JS missing';
if(!str_contains($css,'.lesson-story-demo'))$errors[]='lesson story CSS missing';

$totalTopics=0;$generic=0;$classRows=[];
foreach(glob($root.'/cache/runtime/class_*.php')?:[] as $file){
    $data=require $file; $class=basename($file,'.php'); $topics=[];$lessons=0;
    if(is_array($data['nextLessons']??null)){
        $lessons++; foreach((array)($data['nextLessons']['knowledge']??[]) as $t)$topics[(string)$t]=true;
    }
    foreach((array)($data['extendedLessons']??[]) as $lesson){
        if(!is_array($lesson))continue;$lessons++;$ks=(array)($lesson['knowledge']??[]);if(!$ks)$errors[]="$class lesson ".($lesson['number']??'?')." has no lesson-specific knowledge topics";foreach($ks as $t)$topics[(string)$t]=true;
    }
    $kinds=[];
    foreach(array_keys($topics) as $topic){$totalTopics++;$spec=assessment_visual_spec($class,$topic);$kind=(string)($spec['kind']??'');$kinds[$kind]=($kinds[$kind]??0)+1;if($kind==='process'){$generic++;$errors[]="$class topic $topic falls back to generic process visual";}}
    $classRows[]="$class: structured=$lessons, visual-topics=".count($topics).", kinds=".json_encode($kinds,JSON_UNESCAPED_UNICODE);
}
foreach($classRows as $r)echo $r."\n";
if($errors){foreach($errors as $e)fwrite(STDERR,"ERROR: $e\n");exit(1);} 
echo "v34 clarity/visual audit OK · lesson-specific animated models=$totalTopics · generic-fallback=$generic · simplified nav/course/focus verified.\n";
