<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require $root.'/runtime_content.php';
require_once $root.'/teacher_operations_v46.php';
require_once $root.'/student_learning_coach_v47.php';
require_once $root.'/student_learning_coach_views_v47.php';
function v47_fp(string $dir):array{$o=[];foreach(glob($dir.'/*.php')?:[] as $f)$o[basename($f)]=[filesize($f)?:0,filemtime($f)?:0,hash_file('sha256',$f)?:''];ksort($o);return $o;}
// i18n (v59): student-facing Czech texty ve views/logice se postupně obalují no-op markerem trm('…')
// (vykreslení pak jde přes tr($label)) – needle kontrola musí tolerovat holý řetězec i obalený tvar.
function v47_hasLit(string $h, string $needle): bool { return (bool)preg_match('~(?:tr(?:m)?\(\s*)?' . preg_quote($needle, '~') . '~', $h); }
$errors=[];$before=v47_fp(STORAGE_DIR);$classes=[];
foreach(array_keys($modules) as $classId){
    $runtime=runtime_content_load_classes([$classId]);
    $GLOBALS['nextLessons']=$runtime['nextLessons'];$GLOBALS['extendedLessons']=$runtime['extendedLessons'];
    $students=project_students_for_class($classId);$studentKey=(string)(array_key_first($students)??($classId.':student:v47audit'));
    $tour=is_array($runtime['knowledgeTours'][$classId]??null)?$runtime['knowledgeTours'][$classId]:[];
    $quick=coach_plan($classId,$studentKey,$modules[$classId],$tour,10,'low','balanced');
    $deep=coach_plan($classId,$studentKey,$modules[$classId],$tour,45,'high','exam');
    foreach([$quick,$deep] as $plan){
        if(!isset($plan['items'],$plan['planned_minutes'],$plan['done_count']))$errors[]=$classId.' plan nemá očekávanou strukturu.';
        if((int)$plan['planned_minutes']>(int)$plan['minutes'])$errors[]=$classId.' plán překročil časový budget.';
        $ids=array_map(static fn($x)=>(string)($x['id']??''),(array)$plan['items']);if(count($ids)!==count(array_unique($ids)))$errors[]=$classId.' plán obsahuje duplicitní item ID.';
    }
    $mistakes=coach_mistake_notebook($classId,$studentKey,$modules[$classId],5);if(!is_array($mistakes))$errors[]=$classId.' Mistake Notebook nevrátil pole.';
    $forecast=coach_review_forecast($classId,$studentKey,7);if(count((array)($forecast['buckets']??[]))!==8)$errors[]=$classId.' review forecast nemá 0–7 dní.';
    $cal=coach_confidence_calibration($classId,$studentKey);if(!isset($cal['state'],$cal['title'],$cal['total']))$errors[]=$classId.' confidence calibration je neúplná.';
    $week=coach_week_summary($classId,$studentKey);if(!isset($week['active_days'],$week['retrieval_total'],$week['tasks_done']))$errors[]=$classId.' weekly summary je neúplné.';
    $classes[$classId]=['quick_items'=>count((array)$quick['items']),'deep_items'=>count((array)$deep['items']),'mistakes'=>count($mistakes),'review_due'=>(int)($forecast['buckets'][0]??0)];
}
$after=v47_fp(STORAGE_DIR);$writes=[];foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $n)if(($before[$n]??null)!==($after[$n]??null))$writes[]=$n;if($writes)$errors[]='Read-only v47 změnilo storage: '.implode(', ',$writes);
$index=edu_app_source()?:'';$views=file_get_contents($root.'/student_learning_coach_views_v47.php')?:'';$core=file_get_contents($root.'/student_learning_coach_v47.php')?:'';$sw=file_get_contents($root.'/sw.js')?:'';
// "'label'=>'Studium'" žilo jen v mrtvém komentáři app/views/_layout.php ř. 65 (nikdy nerenderováno) –
// navigace v55 dnes nemá samostatnou položku "Studium"/"Trénink" (viz student_v55.php v55_primary_nav()),
// takže reálný ekvivalent neexistuje a marker se maže bez náhrady (funkce zůstává ověřená přes route+akce).
foreach(["student_learning_coach_v47.php","view === 'study'","view === 'mistakes'",'coach_preferences_save','coach_step_toggle','coach_exit_ticket','Spustit dnešní plán','coachDashboardSnapshot'] as $needle)if(!v47_hasLit($index,$needle))$errors[]='index.php chybí marker '.$needle;
foreach(['render_student_coach_view','render_student_mistakes_view','render_student_coach_dashboard','Exit ticket','Mistake Notebook'] as $needle)if(!v47_hasLit($views,$needle))$errors[]='Views chybí '.$needle;
foreach(['coach_plan','coach_mistake_notebook','coach_confidence_calibration','coach_review_forecast','coach_week_summary','static $cache=[]'] as $needle)if(!str_contains($core,$needle))$errors[]='Core chybí '.$needle;
// Dřív se testoval literální marker "educanet-vNN-shell" v komentářích sw.js (vždy PASS bez ohledu na
// skutečný precache); teď čteme aktivní `const CACHE=` a `const SHELL=` a ověřujeme skutečný obsah.
if(preg_match('~const CACHE="(educanet-v\d+[^"]*)";~',$sw,$v47cm)){$v47cache=$v47cm[1];}else{$v47cache='';}
if($v47cache==='')$errors[]='PWA cache namespace není verzovaný educanet-vNN identifikátor.';
if(preg_match('~const SHELL=(\[[^;]+\]);~s',$sw,$v47sm)){$v47shell=$v47sm[1];}else{$v47shell='';}
foreach(['assets/student-coach-v47.css?v=47.2','assets/student-coach-v47.js?v=47.2'] as $needle)if($v47shell===''||!str_contains($v47shell,$needle))$errors[]='Aktivní PWA SHELL chybí '.$needle;
foreach(['assets/student-coach-v47.css','assets/student-coach-v47.js'] as $rel)if(!is_file($root.'/'.$rel)||filesize($root.'/'.$rel)===0)$errors[]='Chybí asset '.$rel;
if((filesize($root.'/assets/student-coach-v47.css')?:0)>40000)$errors[]='Coach CSS je zbytečně velké.';
if((filesize($root.'/assets/student-coach-v47.js')?:0)>15000)$errors[]='Coach JS je zbytečně velké.';
echo json_encode(['ok'=>!$errors,'version'=>'47','features'=>['adaptive_micro_plan'=>true,'low_energy_mode'=>true,'exam_mode'=>true,'spaced_retrieval'=>true,'mistake_notebook'=>true,'confidence_calibration'=>true,'weekly_consistency'=>true,'exit_ticket'=>true,'focus_timer'=>true,'student_preferences'=>true],'classes'=>$classes,'read_only_storage_writes'=>$writes,'asset_bytes'=>['css'=>filesize($root.'/assets/student-coach-v47.css')?:0,'js'=>filesize($root.'/assets/student-coach-v47.js')?:0],'errors'=>$errors],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;exit($errors?1:0);
