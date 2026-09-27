<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
require_once $root.'/teacher_class_dashboard.php';
require_once $root.'/teacher_operations_v46.php';
require_once $root.'/teacher_operations_plus_v46_1.php';
require_once $root.'/teacher_operations_views_v46.php';
require_once $root.'/teacher_operations_plus_views_v46_1.php';

function v461_fingerprint(string $dir): array {
    $out=[];
    foreach(glob($dir.'/*.php')?:[] as $file){
        $out[basename($file)]=[filesize($file)?:0,filemtime($file)?:0,hash_file('sha256',$file)?:''];
    }
    ksort($out);return $out;
}

$errors=[];
$before=v461_fingerprint(STORAGE_DIR);

$health=[];
foreach(['class_1a','class_2a','class_3a','class_4a'] as $classId){
    $row=teacher_ops_class_health($classId);$health[$classId]=$row;
    $score=$row['score']??null;
    if(!is_numeric($score)||(float)$score<0||(float)$score>100)$errors[]="$classId má neplatný Health Score.";
    foreach(['results','deadlines','mastery','activity'] as $component){
        $value=$row['components'][$component]??null;
        if(!is_numeric($value)||(float)$value<0||(float)$value>100)$errors[]="$classId: chybí/neplatná Health komponenta $component.";
    }
}

$templates=teacher_ops_builtin_templates();
if(count($templates)<4)$errors[]='Chybí vestavěné intervenční playbooky.';
foreach(['teacher_ops_watchlist_visible','teacher_ops_followups_visible','teacher_ops_templates_visible','teacher_ops_audit_visible','teacher_ops_undo_available','teacher_ops_undo_execute'] as $fn){
    if(!function_exists($fn))$errors[]="Chybí $fn.";
}

// Read-only operations must not mutate persistent storage.
teacher_ops_watchlist_visible('class_2a');
teacher_ops_followups_visible('class_2a',true);
teacher_ops_templates_visible();
teacher_ops_undo_available('class_2a');
teacher_ops_audit_visible(['class_id'=>'class_2a']);
$after=v461_fingerprint(STORAGE_DIR);
$writes=[];
foreach(array_unique(array_merge(array_keys($before),array_keys($after))) as $name){
    if(($before[$name]??null)!==($after[$name]??null))$writes[]=$name;
}
if($writes)$errors[]='Read-only v46.1 operace změnily storage: '.implode(', ',$writes);

$teacher=file_get_contents($root.'/teacher.php')?:'';
$plus=file_get_contents($root.'/teacher_operations_plus_v46_1.php')?:'';
$views=file_get_contents($root.'/teacher_operations_plus_views_v46_1.php')?:'';
$js=file_get_contents($root.'/assets/teacher-admin-v46.js')?:'';
foreach([
    "tab==='ops_audit'"=>'Audit operací není zapojen v teacher.php.',
    'teacher_render_ops_plus_attention'=>'Produkční vrstva není zapojena do Attention Center.',
    'teacher_render_ops_plus_student360'=>'Watchlist/follow-up není zapojen do Student 360°.',
    'teacher_render_ops_plus_interventions'=>'Playbooky nejsou zapojeny do Intervencí.',
    'teacher_render_ops_class_health_strip'=>'Class Health není zapojen do Přehledu třídy.',
] as $needle=>$message) if(!str_contains($teacher,$needle))$errors[]=$message;
foreach(['data-ops-health','data-ops-followups','data-ops-watchlist','data-ops-undo','data-ops-audit'] as $needle){
    if(!str_contains($views,$needle))$errors[]="Chybí UI marker $needle.";
}
foreach(['teacher_ops_undo_record','teacher_ops_hash_row','Undo bylo zablokováno'] as $needle){
    if(!str_contains($plus,$needle))$errors[]="Chybí Safe Undo marker $needle.";
}
if(!str_contains($js,'data-intervention-template'))$errors[]='Chybí client-side aplikace intervenční šablony.';

$result=[
    'ok'=>!$errors,
    'version'=>'46.1',
    'features'=>[
        'class_health'=>true,
        'watchlist'=>true,
        'followup_queue'=>true,
        'intervention_playbooks'=>true,
        'operation_audit'=>true,
        'safe_bulk_undo'=>true,
    ],
    'health'=>$health,
    'builtin_templates'=>count($templates),
    'read_only_storage_writes'=>$writes,
    'errors'=>$errors,
];
echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
exit($errors?1:0);
