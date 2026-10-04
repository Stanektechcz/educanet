<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/http_harness.php';

if (PHP_SAPI !== 'cli') { http_response_code(400); exit; }
$root=dirname(__DIR__);
$tmpStorage=rtrim(str_replace('\\','/',edu_audit_temp_storage('v33-schedule')),'/'); // v61: audit nikdy nesahá na ostrou storage/
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
// i18n (v59): texty mohou být obalené no-op markerem trm('…') / tr('…').
$v33HasLit = static fn(string $h, string $needle): bool => (bool)preg_match('~(?:tr(?:m)?\(\s*)?' . preg_quote($needle, '~') . '~', $h);
if(!$v33HasLit($index,'runtime_content_load_classes'))$errors[]='index missing runtime_content_load_classes';
// v61 (ROADMAP_V60 §3): čas vyučovacího bloku se ověřuje na skutečně dosažitelné stránce kalendáře
// (?view=calendar → tut52_render_calendar()), ne na textu v mrtvém souboru. Stránka každé třídy musí říkat
// „Každou středu <začátek>–<konec>“ podle rozvrhu třídy a nabízet export .ics této třídy.
$v33Http=Harness::start(['EDUCANET_STORAGE_DIR'=>$tmpStorage,'EDUCANET_DEV_BYPASS'=>'1','EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY'=>'0']);
try{
    foreach($expected as $cid=>[$from,$to,$periods]){
        $v33Http->request('GET','/',['class'=>$cid,'student'=>'Audit Kalendar']);
        $page=$v33Http->request('GET','/?view=calendar');
        $body=html_entity_decode((string)$page['body'],ENT_QUOTES|ENT_HTML5,'UTF-8');
        if((int)$page['status']!==200)$errors[]="$cid: ?view=calendar vrátil ".$page['status'];
        if(!str_contains($body,'Každou středu '.$from.'–'.$to))$errors[]="$cid: kalendář neříká „Každou středu $from–$to“";
        if(!str_contains($body,'href="calendar.ics.php?class='.$cid.'"'))$errors[]="$cid: kalendář nenabízí export .ics";
        echo $cid." · stránka kalendáře: čas bloku a odkaz .ics ověřeny\n";
    }
}finally{$v33Http->stop();}
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
if($errors){fwrite(STDERR,"v33 audit FAILED\n- ".implode("\n- ",$errors)."\n");exit(1);}echo "v33 schedule/performance audit OK · class-private calendar (rendered page) · timed ICS link · runtime cache · test student tool\n";
