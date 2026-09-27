<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if(PHP_SAPI!=='cli'){http_response_code(400);exit("CLI only\n");}
require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/runtime_content.php';
$runtime=runtime_content_load_classes(['class_1a','class_2a','class_3a','class_4a']);
$nextLessons=$runtime['nextLessons'];$extendedLessons=$runtime['extendedLessons'];
$errors=[];$counts=[];$families=[];$total=0;$paramIds=[];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
  $counts[$classId]=0;
  foreach(v42_lessons_for_class($classId) as $lesson){
    $counts[$classId]++;$total++;
    $s=v45_simulation_spec($classId,$lesson,$modules[$classId]);$id=(string)($s['id']??'');$family=(string)($s['family']??'');$families[$family]=($families[$family]??0)+1;
    if($id==='')$errors[]="$classId L{$lesson['number']}: missing id";
    if(!in_array((string)($s['renderer']??''),['design','system'],true))$errors[]="$id: invalid renderer";
    $params=(array)($s['params']??[]);if(count($params)<4)$errors[]="$id: <4 manipulable params";
    $seen=[];foreach($params as $p){$pid=(string)($p['id']??'');if($pid===''||isset($seen[$pid]))$errors[]="$id: duplicate/empty param";$seen[$pid]=true;$min=(float)($p['min']??0);$max=(float)($p['max']??0);$target=(float)($p['target']??0);$value=(float)($p['value']??0);if(!($min<$max))$errors[]="$id:$pid invalid range";if($target<$min||$target>$max)$errors[]="$id:$pid target outside range";if($value<$min||$value>$max)$errors[]="$id:$pid default outside range";$paramIds[$pid]=($paramIds[$pid]??0)+1;}
    if(count((array)($s['what_if']??[]))<3)$errors[]="$id: <3 what-if scenarios";
    foreach((array)($s['what_if']??[]) as $w){
      if(empty($w['title'])||empty($w['text'])||!is_array($w['values']??null)){$errors[]="$id: incomplete what-if";continue;}
      $changed=0;foreach($params as $p){$pid=(string)$p['id'];if(!array_key_exists($pid,$w['values'])){$errors[]="$id: what-if missing $pid";continue;}$wv=(float)$w['values'][$pid];if($wv<(float)$p['min']||$wv>(float)$p['max'])$errors[]="$id: what-if $pid outside range";if(abs($wv-(float)$p['value'])>.0001)$changed++;}
      if($changed<1)$errors[]="$id: what-if has no changed parameter";
    }
    if(count((array)($s['metric_labels']??[]))!==4)$errors[]="$id: metric labels != 4";
    foreach(['problem','princip','transfer'] as $k)if(trim((string)($s[$k]??''))==='')$errors[]="$id: empty $k";
    foreach(['ask','reveal','compare'] as $k)if(trim((string)($s['projection'][$k]??''))==='')$errors[]="$id: projection $k";
    $base=v45_evaluate_state($s,(array)$s['baseline']);$target=v45_evaluate_state($s,(array)$s['target']);
    if(($target['metrics']['quality']??0)<95||($target['metrics']['risk']??100)>10)$errors[]="$id: target evaluation not strong";
    if(!isset($base['metrics']['quality'],$base['metrics']['risk']))$errors[]="$id: baseline evaluation missing";
  }
}
foreach($counts as $c=>$n)if($n!==28)$errors[]="$c has $n simulations, expected 28";
$root=dirname(__DIR__);
foreach(['visual_simulation_v45.php','visual_simulation_views_v45.php','assets/visual-simulation-v45.css','assets/visual-simulation-v45.js'] as $f)if(!is_file($root.'/'.$f))$errors[]="missing $f";
$index=edu_app_source()?:'';$teacher=file_get_contents($root.'/teacher_lesson_mode.php')?:'';$cv=file_get_contents($root.'/cognitive_visualization_views_v43.php')?:'';$boot=file_get_contents($root.'/bootstrap.php')?:'';
if(!str_contains($boot,'visual_simulation_v45.php'))$errors[]='bootstrap v45 include missing';
if(!str_contains($index,'visual_simulation_views_v45.php'))$errors[]='student v45 views include missing';
if(!str_contains($index,"v45_sim_save"))$errors[]='v45 POST save missing';
if(!str_contains($cv,'v45_render_visual_simulation'))$errors[]='v45 student extension missing';
if(!str_contains($teacher,'v45_render_teacher_projection'))$errors[]='v45 teacher projection missing';
$views=file_get_contents($root.'/visual_simulation_views_v45.php')?:'';$client=file_get_contents($root.'/assets/visual-simulation-v45.js')?:'';
foreach(['data-v45-scenario-step','data-v45-ab-detail','data-v45-history-list','data-v45-phase','data-v45-spotlight','data-v45-evidence-note'] as $marker)if(!str_contains($views,$marker))$errors[]="experiment layer view marker missing: $marker";
foreach(['renderCausal','renderHistory','startScenarioWalk','setTeacherPhase','setSpotlight'] as $marker)if(!str_contains($client,$marker))$errors[]="experiment layer JS capability missing: $marker";
if(!str_contains($index,"\$_POST['note']"))$errors[]='evidence note POST persistence missing';
$css=filesize($root.'/assets/visual-simulation-v45.css')?:0;$js=filesize($root.'/assets/visual-simulation-v45.js')?:0;
if($css>42000)$errors[]='v45 CSS exceeds 42 KB';if($js>30000)$errors[]='v45 JS exceeds 30 KB';
echo "v45 Visual Simulation Audit\nSimulations: $total / 112\n";foreach($counts as $c=>$n)echo "$c: $n\n";ksort($families);echo "Families: ";foreach($families as $f=>$n)echo "$f=$n ";echo "\nAssets: CSS ".round($css/1024,1)." KB · JS ".round($js/1024,1)." KB\n";
if($errors){foreach($errors as $e)echo "[FAIL] $e\n";exit(1);}echo "[OK] 112/112 lesson simulations, manipulable parameters, deterministic feedback, A/B comparison, what-if scenarios and teacher projection mode.\n";
