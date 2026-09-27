<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';

if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require dirname(__DIR__).'/bootstrap.php';
require_once dirname(__DIR__).'/visual_practical_learning_views_v48.php';

function v48_audit_tree_hash(string $dir): array {
    $out=[];if(!is_dir($dir))return $out;$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));
    foreach($it as $f){if(!$f->isFile())continue;$rel=substr($f->getPathname(),strlen($dir)+1);$out[$rel]=hash_file('sha256',$f->getPathname());}ksort($out);return $out;
}

$root=dirname(__DIR__);$before=v48_audit_tree_hash($root.'/storage');$errors=[];$specs=v48_all_lab_specs();
if(count($specs)!==112)$errors[]='Expected 112 V48LabSpec records, got '.count($specs).'.';
$counts=[];$modes=['prediction','compare','debug','sandbox','build','transfer'];
foreach($specs as $id=>$spec){
    if(!is_array($spec)){$errors[]="$id is not an array";continue;}
    $cid=(string)($spec['class_id']??'');$counts[$cid]=($counts[$cid]??0)+1;
    foreach($modes as $mode)if(empty($spec[$mode])||!is_array($spec[$mode]))$errors[]="$id missing $mode";
    if(count((array)($spec['prediction']['options']??[]))<2)$errors[]="$id prediction has too few options";
    if(count((array)($spec['compare']['options']??[]))<2)$errors[]="$id compare has too few options";
    if(count((array)($spec['debug']['options']??[]))<2)$errors[]="$id debug has too few options";
    if(count((array)($spec['sandbox']['params']??[]))<3)$errors[]="$id sandbox has too few parameters";
    if(count((array)($spec['build']['expected']??[]))<2)$errors[]="$id build model is too small";
    if(count((array)($spec['teacher']['phases']??[]))<7)$errors[]="$id teacher orchestration is incomplete";
    foreach(['grade_impact','xp_impact','mastery_impact'] as $flag)if(($spec['assessment'][$flag]??true)!==false)$errors[]="$id assessment flag $flag must be false";
}
foreach(['class_1a','class_2a','class_3a','class_4a'] as $cid)if(($counts[$cid]??0)!==28)$errors[]="$cid expected 28 specs, got ".($counts[$cid]??0);
$after=v48_audit_tree_hash($root.'/storage');if($before!==$after)$errors[]='Read-only V48 spec/audit path changed storage.';

$index=edu_app_source()?:'';$teacher=file_get_contents($root.'/teacher.php')?:'';$lessonMode=file_get_contents($root.'/teacher_lesson_mode.php')?:'';$sw=file_get_contents($root.'/sw.js')?:'';
foreach(['visual_practical_learning_views_v48.php','v48_prediction','v48_compare','v48_debug','v48_build','v48_transfer',"\$view === 'visual_lab'",'visual-practical-v48.css','visual-practical-v48.js'] as $needle)if(!str_contains($index,$needle))$errors[]='index.php missing '.$needle;
foreach(['v48_orchestrate','v48_state','visual-practical-v48.css','visual-practical-v48.js'] as $needle)if(!str_contains($teacher,$needle))$errors[]='teacher.php missing '.$needle;
if(!str_contains($lessonMode,'v48_render_teacher_orchestration'))$errors[]='teacher lesson mode missing V48 orchestration panel.';
foreach(['visual-practical-v48.css?v=48','visual-practical-v48.js?v=48'] as $needle)if(!str_contains($sw,$needle))$errors[]='service worker missing '.$needle;

$out=['ok'=>!$errors,'version'=>'48','specs'=>count($specs),'per_class'=>$counts,'priority_order'=>['prediction_before_reveal','debugging_missions','compare_contrast','visual_concept_studio','sandbox_what_if','draw_build','teacher_orchestration'],'modes'=>$modes,'teacher_phases'=>7,'assessment'=>['grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false],'storage_writes'=>$before===$after?[]:['storage changed'],'errors'=>$errors];
echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
