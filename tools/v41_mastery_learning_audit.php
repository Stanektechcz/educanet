<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/assessment_visuals.php';
require dirname(__DIR__).'/reality_demos.php';
require dirname(__DIR__).'/mastery_learning_views_v41.php';
$root=dirname(__DIR__);$errors=[];
$files=['mastery_learning_v41.php','mastery_learning_views_v41.php','manifest.webmanifest','sw.js'];foreach($files as $f)if(!is_file($root.'/'.$f))$errors[]='missing '.$f;
$index=edu_app_source()?:'';$teacher=file_get_contents($root.'/teacher.php')?:'';$progress=file_get_contents($root.'/progress.php')?:'';$js=file_get_contents($root.'/assets/app.js')?:'';$css=(file_get_contents($root.'/assets/app.css')?:'').(file_get_contents($root.'/assets/mastery.css')?:'');
foreach(['ml_render_worked_example','ml_render_browser_lab','ml_render_transfer','ml_render_explain_back','ml_render_knowledge_map','ml_render_class_tips','ml_render_error_journal','ml_render_live_student','ml_goal_save','ml_portfolio_narrative_save','create_challenge'] as $n)if(!str_contains($index,$n))$errors[]='index missing '.$n;
foreach(['ml_record_learning_event','confidence','latency_ms'] as $n)if(!str_contains($progress,$n))$errors[]='progress missing '.$n;
foreach(['tab===\'authoring\'','ml_scenario_save'] as $n)if(!str_contains($teacher,$n))$errors[]='teacher missing '.$n;
// v69: živé otázky a Mastery hub z učitelského rozhraní zmizely (záložka mastery vede 302 na Kompetence).
foreach(['tab===\'mastery\'','ml_live_start','ml_failure_inject'] as $n)if(str_contains($teacher,$n))$errors[]='teacher still has retired '.$n;
foreach(['data-ml-transfer','data-ml-browser-lab','data-ml-worked','data-ml-before','data-ml-template','serviceWorker','data-confidence'] as $n)if(!str_contains($js,$n))$errors[]='JS missing '.$n;
foreach(['.ml-worked','.ml-terminal','.ml-transfer','.ml-live-card','.ml-template-row','prefers-reduced-motion'] as $n)if(!str_contains($css,$n))$errors[]='CSS missing '.$n;
$spec=ml_transfer_spec('class_3a','audit-student','dns-dhcp-operations',['title'=>'DNS','summary'=>'DNS resolver']);if(count($spec['options'])<3)$errors[]='transfer spec invalid';
$lab=ml_browser_lab_spec('class_3a','ssh-keys-ops',['title'=>'SSH']);if(($lab['type']??'')!=='terminal')$errors[]='ops lab not terminal';
$lab2=ml_browser_lab_spec('class_2a','responsive-ui',['title'=>'Responsive UI']);if(($lab2['type']??'')!=='visual')$errors[]='design lab not visual';
$v=ml_scenario_validate(['title'=>'x','brief'=>'y','choices'=>['a','b'],'correct'=>0,'why'=>'z']);if(!$v['ok'])$errors[]='authoring validation broken';
if($errors){foreach($errors as $e)fwrite(STDERR,"ERROR: $e\n");exit(1);}echo "v41 mastery learning OK · misconception + confidence + fading + transfer + interleaving + labs + live + failure injection + authoring + class KB + portfolio + PWA\n";
