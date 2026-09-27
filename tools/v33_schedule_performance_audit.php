<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';

if (PHP_SAPI !== 'cli') { http_response_code(400); exit; }
$root=dirname(__DIR__);
require $root.'/bootstrap.php';
$schoolYear=require $root.'/school_year.php';
$errors=[];
$expected=[
 'class_1a'=>['08:00','09:40',[1,2]],
 'class_4a'=>['10:00','11:40',[3,4]],
 'class_2a'=>['12:45','14:20',[6,7]],
 'class_3a'=>['14:30','16:05',[8,9]],
];
foreach($expected as $cid=>[$from,$to,$periods]){
    $s=adaptive_class_schedule($schoolYear,$cid);
    if(($s['start']??'')!==$from||($s['end']??'')!==$to||array_map('intval',(array)($s['periods']??[]))!==$periods)$errors[]="$cid: rozvrh nesedí";
    echo $cid." · ".$from."–".$to." · ".implode('–',$periods).". hodina\n";
}
$index=edu_app_source()?:'';
// ř. 23 (dřív): "Středa <?=e($dashClassScheduleTime)" je jen komentář v app/views/dashboard.php ř. 177
// (dashClassScheduleTime se nikde nevykresluje) – ověřujeme místo toho skutečné vykreslení času
// vyučovacího bloku přes v55_block_window()['range'] v student_v55_views.php/learning_v56_views.php.
// i18n (v59): "Každou středu ·" je Czech UI text, který se může obalit no-op markerem trm('…').
$v33HasLit = static fn(string $h, string $needle): bool => (bool)preg_match('~(?:tr(?:m)?\(\s*)?' . preg_quote($needle, '~') . '~', $h);
foreach(['runtime_content_load_classes','Každou středu ·'] as $needle) if(!$v33HasLit($index,$needle))$errors[]="index missing $needle";
$v55Views=file_get_contents($root.'/student_v55_views.php')?:'';
$v56Views=file_get_contents($root.'/learning_v56_views.php')?:'';
// i18n (v59): rendering of $window['range'] may be wrapped in e()/tr()/interpolated into a template
// string with placeholders (e.g. tr('… {range}', ['range'=>$window['range']])) – check for the literal
// key access itself rather than one exact wrapping shape.
if(!str_contains($v55Views,"\$window['range']")||!str_contains($index,'v55_block_window'))$errors[]='class block time (v55_block_window range) is not actually rendered';
if(!str_contains($v56Views,"\$window['range']"))$errors[]="today's lesson time is not actually rendered in learning_v56_views.php";
$ics=file_get_contents($root.'/calendar.ics.php')?:'';
foreach(['DTSTART;TZID=Europe/Prague','Kalendář je dostupný pouze pro tvoji třídu'] as $needle) if(!str_contains($ics,$needle))$errors[]="ICS missing $needle";
$bootstrap=file_get_contents($root.'/bootstrap.php')?:'';
foreach(['educanet_json_request_cache','php_json_cache_forget','runtime_content_load_classes'] as $needle) if(!str_contains($bootstrap,$needle))$errors[]="bootstrap missing $needle";
foreach(array_keys($expected) as $cid) if(!is_file($root.'/cache/runtime/'.$cid.'.php'))$errors[]="runtime cache missing $cid";
foreach(['tools/create_test_student.php','tools/performance_check.php','tools/build_runtime_cache.php'] as $file) if(!is_file($root.'/'.$file))$errors[]="missing $file";
if($errors){fwrite(STDERR,"v33 audit FAILED\n- ".implode("\n- ",$errors)."\n");exit(1);}echo "v33 schedule/performance audit OK · class-private calendar · timed ICS · runtime cache · test student tool\n";
