<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require $root.'/runtime_content.php';
require_once $root.'/teacher_operations_v46.php';
require_once $root.'/student_learning_coach_v47.php';
require_once $root.'/student_learning_accelerator_v47_1.php';
require_once $root.'/student_learning_accelerator_views_v47_1.php';
function v471_fp(string $dir):array{$o=[];foreach(glob($dir.'/*.php')?:[] as $f)$o[basename($f)]=[filesize($f)?:0,filemtime($f)?:0,hash_file('sha256',$f)?:''];ksort($o);return $o;}
// i18n (v59): tolerate Czech UI text wrapped in trm()/tr() by an i18n builder (see v47_hasLit).
function v471_hasLit(string $h, string $needle): bool { return (bool)preg_match('~(?:tr(?:m)?\(\s*)?' . preg_quote($needle, '~') . '~', $h); }
$errors=[];$before=v471_fp(STORAGE_DIR);$classes=[];
foreach(array_keys($modules) as $classId){
    $runtime=runtime_content_load_classes([$classId]);
    $GLOBALS['nextLessons']=$runtime['nextLessons'];$GLOBALS['extendedLessons']=$runtime['extendedLessons'];
    $students=project_students_for_class($classId);$studentKey=(string)(array_key_first($students)??($classId.':student:v471audit'));
    $tour=is_array($runtime['knowledgeTours'][$classId]??null)?$runtime['knowledgeTours'][$classId]:[];
    $snap=v471_session_snapshot($classId,$studentKey,$modules[$classId],$tour);
    if(!array_key_exists('focus',$snap)||!array_key_exists('mix',$snap))$errors[]=$classId.' snapshot nemá focus/mix.';
    $focus=$snap['focus']??null;
    if(is_array($focus)){
        if(!in_array((string)($focus['stage']??''),['model','guided','faded','independent'],true))$errors[]=$classId.' má neplatnou guidance stage.';
        foreach(['support_score','risk_score','streak','incorrect','cluster'] as $k)if(!array_key_exists($k,$focus))$errors[]=$classId.' focus chybí '.$k.'.';
        if(!is_array($snap['visible_steps']??null)||!is_array($snap['hidden_steps']??null))$errors[]=$classId.' fading kroky nejsou pole.';
    }
    $mix=(array)($snap['mix']??[]);$topics=array_map(static fn($x)=>(string)($x['topic']??''),$mix);if(count($topics)!==count(array_unique($topics)))$errors[]=$classId.' interleaving obsahuje duplicitní téma.';
    $classes[$classId]=['focus'=>(string)($focus['topic']??''),'stage'=>(string)($focus['stage']??''),'interleaved'=>count($mix),'mastery_ready'=>!empty($snap['mastery_ready'])];
}
$after=v471_fp(STORAGE_DIR);$writes=[];foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $n)if(($before[$n]??null)!==($after[$n]??null))$writes[]=$n;if($writes)$errors[]='Read-only v47.1 změnilo storage: '.implode(', ',$writes);
$index=edu_app_source()?:'';$core=file_get_contents($root.'/student_learning_accelerator_v47_1.php')?:'';$views=file_get_contents($root.'/student_learning_accelerator_views_v47_1.php')?:'';$js=file_get_contents($root.'/assets/student-coach-v47.js')?:'';$sw=file_get_contents($root.'/sw.js')?:'';
// "'label'=>'Trénink'" žilo jen v mrtvém komentáři app/views/_layout.php ř. 65 (nikdy nerenderováno) –
// navigace v55 dnes nemá samostatnou položku "Trénink" (viz student_v55.php v55_primary_nav()), takže
// reálný ekvivalent neexistuje a marker se maže bez náhrady (funkce zůstává ověřená přes route+akce).
foreach(["student_learning_accelerator_v47_1.php","view === 'study_loop'",'coach_probe_submit','coach_difficulty_feedback'] as $needle)if(!str_contains($index,$needle))$errors[]='index.php chybí '.$needle;
foreach(['v471_topic_evidence','v471_interleaved_topics','v471_probe_submit','v471_session_snapshot','v471_stage_label'] as $needle)if(!str_contains($core,$needle))$errors[]='Core chybí '.$needle;
foreach(['generation-first','mikrodiagnostika','Interleaving','kalibrace obtížnosti','data-reveal-scaffold'] as $needle)if(!v471_hasLit($views,$needle))$errors[]='Views chybí '.$needle;
foreach(['data-generation-note','data-reveal-step','educanet:study-break'] as $needle)if(!str_contains($js,$needle))$errors[]='JS chybí '.$needle;
// Dřív se testoval literální marker "educanet-vNN-shell" v komentářích sw.js; teď čteme aktivní
// `const CACHE=`/`const SHELL=` (viz PLAN_F4_PROD.md A.2).
if(preg_match('~const CACHE="(educanet-v\d+[^"]*)";~',$sw,$v471cm)){$v471cache=$v471cm[1];}else{$v471cache='';}
if(preg_match('~const SHELL=(\[[^;]+\]);~s',$sw,$v471sm)){$v471shell=$v471sm[1];}else{$v471shell='';}
if($v471cache===''||$v471shell===''||!preg_match('~assets/student-coach-v47\.css\?v=47\.[12]~',$v471shell))$errors[]='PWA cache/SHELL není kompatibilní s v47.1+.';
echo json_encode(['ok'=>!$errors,'version'=>'47.1','features'=>['microdiagnostic'=>true,'evidence_guidance'=>true,'worked_example_fading'=>true,'generation_first'=>true,'interleaving'=>true,'difficulty_calibration'=>true,'mastery_checkpoint'=>true,'smart_breaks'=>true],'classes'=>$classes,'read_only_storage_writes'=>$writes,'errors'=>$errors],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
