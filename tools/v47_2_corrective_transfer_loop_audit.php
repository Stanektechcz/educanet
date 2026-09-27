<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require $root.'/runtime_content.php';
require_once $root.'/student_learning_coach_v47.php';
require_once $root.'/student_learning_accelerator_v47_1.php';
require_once $root.'/student_corrective_cycle_v47_2.php';
require_once $root.'/student_corrective_cycle_views_v47_2.php';
function v472_fp(string $dir):array{$o=[];foreach(glob($dir.'/*.php')?:[] as $f)$o[basename($f)]=[filesize($f)?:0,filemtime($f)?:0,hash_file('sha256',$f)?:''];ksort($o);return $o;}
// i18n (v59): tolerate Czech UI text wrapped in trm()/tr() by an i18n builder (see v47_hasLit).
function v472_hasLit(string $h, string $needle): bool { return (bool)preg_match('~(?:tr(?:m)?\(\s*)?' . preg_quote($needle, '~') . '~', $h); }
$errors=[];$before=v472_fp(STORAGE_DIR);$classes=[];
foreach(array_keys($modules) as $classId){
    $runtime=runtime_content_load_classes([$classId]);$GLOBALS['nextLessons']=$runtime['nextLessons'];$GLOBALS['extendedLessons']=$runtime['extendedLessons'];
    $tourMap=is_array($runtime['knowledgeTours'][$classId]??null)?$runtime['knowledgeTours'][$classId]:[];$topic=(string)(array_key_first($tourMap)??array_key_first((array)($modules[$classId]['knowledgebase']??[]))??'');
    if($topic===''){$errors[]=$classId.' nemá téma pro corrective loop.';continue;}
    $check=is_array($tourMap[$topic]['check']??null)?$tourMap[$topic]['check']:[];$opts=array_values(array_map('strval',(array)($check['options']??[])));$correct=(int)($check['correct']??0);$wrong=0;foreach(array_keys($opts) as $i)if((int)$i!==$correct){$wrong=(int)$i;break;}
    $probe=['topic'=>$topic,'question'=>(string)($check['q']??''),'correct'=>false,'why'=>(string)($check['why']??''),'selected'=>$wrong,'correct_index'=>$correct,'selected_label'=>(string)($opts[$wrong]??''),'correct_label'=>(string)($opts[$correct]??'')];
    $spec=v472_corrective_cycle_spec($classId,$classId.':student:v472audit',$topic,$probe,$modules[$classId],$tourMap);
    foreach(['principle','similar','contrast','transfer','transfer_mode','grade_impact'] as $k)if(!array_key_exists($k,$spec))$errors[]=$classId.' corrective spec chybí '.$k.'.';
    if(($spec['grade_impact']??true)!==false)$errors[]=$classId.' corrective loop není označený jako non-graded.';
    if(!in_array((string)($spec['transfer_mode']??''),['choice','open'],true))$errors[]=$classId.' má neplatný transfer mode.';
    if(trim((string)($spec['principle']['text']??''))==='')$errors[]=$classId.' nemá vysvětlení principu.';
    if(trim((string)($spec['contrast']['mistake']??''))==='')$errors[]=$classId.' nemá kontrastní chybu.';
    $classes[$classId]=['topic'=>$topic,'transfer_mode'=>(string)($spec['transfer_mode']??''),'similar_source'=>(string)($spec['similar']['source']??(!empty($spec['similar']['worked'])?'worked_fallback':''))];
}
$after=v472_fp(STORAGE_DIR);$writes=[];foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $n)if(($before[$n]??null)!==($after[$n]??null))$writes[]=$n;if($writes)$errors[]='Read-only v47.2 změnilo storage: '.implode(', ',$writes);
$index=edu_app_source()?:'';$core=file_get_contents($root.'/student_corrective_cycle_v47_2.php')?:'';$views=file_get_contents($root.'/student_corrective_cycle_views_v47_2.php')?:'';$js=file_get_contents($root.'/assets/student-coach-v47.js')?:'';$css=file_get_contents($root.'/assets/student-coach-v47.css')?:'';$sw=file_get_contents($root.'/sw.js')?:'';
foreach(['student_corrective_cycle_v47_2.php','student_corrective_cycle_views_v47_2.php','coach_corrective_complete'] as $needle)if(!str_contains($index,$needle))$errors[]='index.php chybí '.$needle;
foreach(['v472_corrective_cycle_spec','v472_topic_question_bank','v472_corrective_cycle_complete',"'grade_impact'=>false","'mastery_impact'=>false"] as $needle)if(!str_contains($core,$needle))$errors[]='Core chybí '.$needle;
foreach(['Opravný cyklus','Podobný příklad','Kontrastní příklad','Transfer do nové situace','0 bodů'] as $needle)if(!v472_hasLit($views,$needle))$errors[]='Views chybí '.$needle;
foreach(['data-corrective-cycle','data-corrective-check','data-corrective-transfer-note'] as $needle)if(!str_contains($js,$needle))$errors[]='JS chybí '.$needle;
if(!str_contains($css,'.corrective-cycle'))$errors[]='CSS chybí corrective-cycle.';
// Dřív se testoval literální marker "educanet-vNN-shell" v komentářích sw.js; teď čteme aktivní
// `const CACHE=`/`const SHELL=` (viz PLAN_F4_PROD.md A.2).
if(preg_match('~const CACHE="(educanet-v\d+[^"]*)";~',$sw,$v472cm)){$v472cache=$v472cm[1];}else{$v472cache='';}
if(preg_match('~const SHELL=(\[[^;]+\]);~s',$sw,$v472sm)){$v472shell=$v472sm[1];}else{$v472shell='';}
if($v472cache===''||$v472shell===''||!str_contains($v472shell,'student-coach-v47.css?v=47.2'))$errors[]='PWA cache/SHELL není kompatibilní s v47.2+.';
echo json_encode(['ok'=>!$errors,'version'=>'47.2','features'=>['principle_explanation'=>true,'similar_worked_example'=>true,'contrastive_example'=>true,'novel_transfer_check'=>true,'non_graded'=>true],'classes'=>$classes,'read_only_storage_writes'=>$writes,'errors'=>$errors],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
