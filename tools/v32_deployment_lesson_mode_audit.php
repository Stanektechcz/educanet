<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/http_harness.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$tmpStorage = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v32-deploy')), '/'); // v61: audit nikdy nesahá na ostrou storage/
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/teacher_curriculum.php';
require dirname(__DIR__) . '/teacher_lesson_mode.php';

$root = dirname(__DIR__);
$errors=[];
$assert=static function(bool $ok,string $message) use (&$errors):void{if(!$ok)$errors[]=$message;};

foreach([
    'private/educanet.secrets.php','educanet.secrets.example.php','tools/install_teacher_secret.sh',
    'tools/check_install.php','teacher_lesson_mode.php','calendar.ics.php'
] as $file) $assert(is_file($root.'/'.$file),'Chybí '.$file);

$template=file_get_contents($root.'/private/educanet.secrets.php')?:'';
$assert(str_contains($template,'CHANGE_ME_WITH_A_LONG_RANDOM_SECRET'),'Private secret template nemá bezpečný placeholder.');
$templateConfig=require $root.'/private/educanet.secrets.php';
$assert(is_array($templateConfig) && (string)($templateConfig['teacher_export_key']??'')==='CHANGE_ME_WITH_A_LONG_RANDOM_SECRET','Private template nesmí obsahovat skutečný teacher key.');

$bootstrap=file_get_contents($root.'/bootstrap.php')?:'';
$assert(str_contains($bootstrap, "'course_mastery' => (int)\$metrics['lessons'] >= 28"),'Course Mastery stále nekončí na 28 lekcích.');
$runtimeBuilder=file_get_contents($root.'/tools/build_runtime_cache.php')?:'';
$assert(str_contains($bootstrap,'runtime_content_load_classes') && str_contains($runtimeBuilder,"'lessons_v30.php'"),'Course completion nezapočítává lekce 19–28 přes runtime cache.');
$assert(str_contains($bootstrap,"'course_28'"),'Chybí L28 achievement.');

foreach(['class_1a','class_2a','class_3a','class_4a'] as $cid){
    $lessons=teacher_curriculum_lessons($cid);
    $assert(count($lessons)===28,$cid.': teacher curriculum != 28');
    $lesson28=teacher_lesson_mode_find($cid,28);
    $assert(is_array($lesson28),$cid.': Lesson Mode nenajde lekci 28');
    if(is_array($lesson28)){
        $schedule=teacher_lesson_mode_schedule($lesson28);
        $assert(count($schedule)>=4,$cid.': Lesson Mode timeline je příliš krátká');
    }
}

$curriculum=file_get_contents($root.'/teacher_curriculum.php')?:'';
$assert(str_contains($curriculum,'$number>28'),'Teacher checklist toggle není rozšířený na lekci 28.');
$assert(str_contains($curriculum,'Spustit hodinu'),'Výuka nemá vstup do Lesson Mode.');

$teacher=file_get_contents($root.'/teacher.php')?:'';
$assert(str_contains($teacher,"'teach'"),'Teacher router nezná teach tab.');
$assert(str_contains($teacher,'install_teacher_secret.sh'),'Nenakonfigurovaný teacher screen nenabízí instalační skript.');

$index=edu_app_source()?:'';
$assert(str_contains($index,'Soustředěný režim'),'Student dashboard nemá Focus Mode.');
// v61 (ROADMAP_V60 §3): export .ics se ověřuje na skutečně dosažitelné stránce (?view=calendar → tut52_render_calendar()):
// odkaz „Přidat svůj rozvrh (.ics)“ vede na calendar.ics.php?class=<třída žáka> a ten vrátí platný VCALENDAR.
$v32Http = Harness::start(['EDUCANET_STORAGE_DIR' => $tmpStorage, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    $v32Http->request('GET', '/', ['class' => 'class_3a', 'student' => 'Audit Kalendar']);
    $calendarPage = $v32Http->request('GET', '/?view=calendar');
    $linkOk = (int)$calendarPage['status'] === 200 && str_contains((string)$calendarPage['body'], 'href="calendar.ics.php?class=class_3a"') && str_contains((string)$calendarPage['body'], 'Přidat svůj rozvrh (.ics)');
    $assert($linkOk, 'Student kalendář nemá odkaz na ICS export.');
    $icsOwn = $v32Http->request('GET', '/calendar.ics.php?class=class_3a');
    $assert((int)$icsOwn['status'] === 200 && str_starts_with((string)$icsOwn['body'], 'BEGIN:VCALENDAR') && str_contains((string)$icsOwn['body'], 'BEGIN:VEVENT'), 'ICS vlastní třídy není platný kalendář.');
    $icsForeign = $v32Http->request('GET', '/calendar.ics.php?class=class_4a');
    $assert((int)$icsForeign['status'] === 403, 'ICS cizí třídy musí být zakázán (403).');
} finally {
    $v32Http->stop();
}

$ics=file_get_contents($root.'/calendar.ics.php')?:'';
$assert(str_contains($ics,'DTSTART;VALUE=DATE'),'ICS nesmí vymýšlet konkrétní čas rozvrhu.');

$css=file_get_contents($root.'/assets/app.css')?:'';
foreach(['.lesson-mode','.focus-mode-toggle','prefers-reduced-motion'] as $needle)$assert(str_contains($css,$needle),'CSS chybí '.$needle);

if($errors){fwrite(STDERR,"v32 audit FAILED\n- ".implode("\n- ",$errors)."\n");exit(1);} 
echo "v32 deployment/lesson mode audit OK · secure template + installer + 28-course mastery + lesson mode + ICS + focus mode\n";
