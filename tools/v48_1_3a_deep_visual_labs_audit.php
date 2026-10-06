<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

chdir(dirname(__DIR__));
require __DIR__ . '/../bootstrap.php';

function v481_audit_storage_snapshot(string $dir): array {
    $out=[]; if(!is_dir($dir)) return $out;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,FilesystemIterator::SKIP_DOTS));
    foreach($it as $file){ if(!$file->isFile())continue; $path=$file->getPathname(); $rel=substr($path,strlen($dir)+1); $out[$rel]=hash_file('sha256',$path)?:''; }
    ksort($out); return $out;
}
function v481_audit_wrong_answer(array $task): mixed {
    $type=(string)($task['type']??'');
    if($type==='choice'){foreach((array)($task['options']??[]) as $o)if(v481_norm_scalar($o)!==v481_norm_scalar($task['expected']??''))return $o;return '__wrong__';}
    if($type==='sequence'){ $x=array_values((array)($task['expected']??[])); return array_reverse($x); }
    if($type==='multi'||$type==='matrix'){ $x=array_values((array)($task['expected']??[])); array_pop($x); return $x; }
    if($type==='pair'){ $expected=(array)($task['expected']??[]);$right=array_values((array)($task['right']??[])); foreach($expected as $k=>$v){foreach($right as $candidate){if(v481_norm_scalar($candidate)!==v481_norm_scalar($v)){$expected[$k]=$candidate;return $expected;}}} return []; }
    if($type==='number')return (float)($task['expected']??0)+1;
    if($type==='command')return '__definitely_wrong_command__';
    return '__wrong__';
}

$before=v481_audit_storage_snapshot(__DIR__.'/../storage');
$errors=[];$labs=v481_3a_specs();$types=[];$archetypes=[];$titles=[];
if(count($labs)!==28)$errors[]='Expected exactly 28 class_3a deep labs, got '.count($labs);
foreach(range(1,28) as $n){
    $spec=$labs[$n]??null;if(!is_array($spec)){$errors[]="Missing lesson {$n}";continue;}
    $title=trim((string)($spec['title']??''));if($title==='')$errors[]="L{$n}: missing title";elseif(isset($titles[$title]))$errors[]="L{$n}: duplicate title";else$titles[$title]=1;
    $arch=trim((string)($spec['archetype']??''));if($arch==='')$errors[]="L{$n}: missing archetype";else$archetypes[$arch]=1;
    if(count((array)($spec['scene']['nodes']??[]))<4)$errors[]="L{$n}: scene has fewer than 4 nodes";
    if(count((array)($spec['faults']??[]))<3)$errors[]="L{$n}: fewer than 3 faults";
    if(count((array)($spec['tasks']??[]))<3)$errors[]="L{$n}: fewer than 3 practical tasks";
    if(trim((string)($spec['transfer']??''))==='')$errors[]="L{$n}: missing transfer";
    foreach(['guided','practice','challenge'] as $mode)if(trim((string)($spec['mode_notes'][$mode]??''))==='')$errors[]="L{$n}: missing {$mode} mode note";
    foreach(['cold_call','discussion','extension'] as $key)if(trim((string)($spec['teacher'][$key]??''))==='')$errors[]="L{$n}: missing teacher {$key}";
    foreach(['grade_impact','xp_impact','mastery_impact'] as $key)if(($spec['assessment'][$key]??null)!==false)$errors[]="L{$n}: assessment {$key} must be false";
    foreach((array)($spec['tasks']??[]) as $task){
        if(!is_array($task))continue;$id=(string)($task['id']??'');$type=(string)($task['type']??'');$types[$type]=1;
        $ok=v481_validate_task($task,v481_expected_answer($task));if(empty($ok['correct']))$errors[]="L{$n}/{$id}: expected answer rejected";
        $bad=v481_validate_task($task,v481_audit_wrong_answer($task));if(!empty($bad['correct']))$errors[]="L{$n}/{$id}: obvious wrong answer accepted";
    }
}
foreach(['choice','sequence','multi','matrix','pair','command','number'] as $type)if(!isset($types[$type]))$errors[]='Missing task type '.$type;
if(count($archetypes)<12)$errors[]='Expected at least 12 interaction archetypes, got '.count($archetypes);

$root=file_get_contents(__DIR__.'/../bootstrap.php')?:'';$index=edu_app_source()?:'';$teacher=(file_get_contents(__DIR__.'/../teacher.php')?:'').(file_get_contents(__DIR__.'/../teacher_shell_v68.php')?:'');/* v68: odkazy na CSS cockpitu jsou v teacher_shell_v68.php */$lessonMode=file_get_contents(__DIR__.'/../teacher_lesson_mode.php')?:'';$views=file_get_contents(__DIR__.'/../visual_practical_learning_views_v48.php')?:'';$sw=file_get_contents(__DIR__.'/../sw.js')?:'';
foreach(['visual_labs_3a_v48_1.php'] as $needle)if(!str_contains($root,$needle))$errors[]='bootstrap missing '.$needle;
foreach(['visual_labs_3a_views_v48_1.php','v481_deep_submit','visual-labs-3a-v48-1.css','visual-labs-3a-v48-1.js'] as $needle)if(!str_contains($index,$needle))$errors[]='index missing '.$needle;
foreach(['visual_labs_3a_views_v48_1.php','visual-labs-3a-v48-1.css','visual-labs-3a-v48-1.js','v481_teacher_summary'] as $needle)if(!str_contains($teacher,$needle))$errors[]='teacher missing '.$needle;
if(!str_contains($lessonMode,'v481_render_teacher_extension'))$errors[]='teacher lesson mode missing deep extension';
if(!str_contains($views,'v481_render_deep_lab'))$errors[]='visual practical view missing deep lab hook';
// Dřív se testoval literální marker "educanet-vNN-shell" v komentářích sw.js; teď čteme aktivní
// `const CACHE=`/`const SHELL=` (viz PLAN_F4_PROD.md A.2). Assety v48.1 se cachují jen na stránce
// visual_lab (viz _layout.php), ne v globálním SHELL, takže tam ověřujeme jejich přítomnost přímo.
if(!preg_match('~<link[^>]+visual-labs-3a-v48-1\.css\?v=48\.1~',$index)||!preg_match('~<script[^>]+visual-labs-3a-v48-1\.js\?v=48\.1~',$index))$errors[]='visual-labs-3a-v48-1 assets are not linked/scripted for the visual_lab view';
if(preg_match('~const CACHE="(educanet-v\d+[^"]*)";~',$sw,$v481cm)){$v481cache=$v481cm[1];}else{$v481cache='';}
if($v481cache==='')$errors[]='service worker missing a versioned educanet-vNN cache namespace';

$after=v481_audit_storage_snapshot(__DIR__.'/../storage');
if($before!==$after)$errors[]='Read-only v48.1 audit changed storage';

$result=[
    'ok'=>!$errors,'version'=>'48.1','class'=>'class_3a','labs'=>count($labs),'archetypes'=>count($archetypes),
    'task_types'=>array_values(array_keys($types)),'fault_scenarios'=>array_sum(array_map(static fn($s)=>count((array)($s['faults']??[])),$labs)),
    'formative'=>true,'storage_writes'=>$before===$after?[]:['changed'],'errors'=>$errors,
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
