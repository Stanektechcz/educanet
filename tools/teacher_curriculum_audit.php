<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';
if (!function_exists('teacher_class_label')) {
    function teacher_class_label(string $classId): string { return ['class_1a'=>'1.A','class_2a'=>'2.A','class_3a'=>'3.A','class_4a'=>'4.A'][$classId] ?? $classId; }
}
require dirname(__DIR__) . '/teacher_curriculum.php';

$root=dirname(__DIR__); $errors=[]; $warnings=[]; $specs=teacher_curriculum_spec();
$expected=['class_1a','class_2a','class_3a','class_4a'];
foreach($expected as $cid){
    if(!isset($specs[$cid])){$errors[]="$cid: chybí curriculum spec";continue;}
    $lessons=teacher_curriculum_lessons($cid);
    if(count($lessons)!==28)$errors[]="$cid: očekáváno 28 lekcí, nalezeno ".count($lessons);
    $numbers=array_map(static fn($l)=>(int)($l['number']??0),$lessons);
    if($numbers!==range(1,28))$errors[]="$cid: číslování není přesně 1–28";
    $ready=0; foreach($lessons as $l){$r=teacher_curriculum_lesson_readiness($l);if($r['ready'])$ready++; else $warnings[]="$cid L".($l['number']??'?').": strukturální readiness {$r['score']}/{$r['total']}";}
    $defs=teacher_curriculum_checklist_definitions($cid); $items=0; foreach($defs as $s)$items+=count((array)($s['items']??[]));
    if($items<20)$errors[]="$cid: checklist má jen $items položek";
    foreach(teacher_curriculum_materials($cid) as $m){ if(!is_file($root.'/'.$m['file']))$errors[]="$cid: chybí {$m['file']}"; }
    echo teacher_class_label($cid).": lessons=28, ready=$ready, checklist=$items\n";
}
foreach(['materials/TEACHER_OVERVIEW.md','materials/ASSESSMENT_MASTERY_MATRIX.md','materials/PROJECT_ROLE_GUIDE.md','materials/CURRICULUM_OVERVIEW.csv'] as $f){if(!is_file($root.'/'.$f))$errors[]="Chybí $f";}
$teacher=file_get_contents($root.'/teacher.php')?:'';
foreach(["teacher_curriculum.php","tab==='curriculum'","teacher_curriculum_toggle","teacher_curriculum_lesson_toggle","teacher_curriculum_note"] as $needle){if(!str_contains($teacher,$needle))$errors[]="teacher.php: chybí $needle";}
if($warnings){echo "Warnings:\n- ".implode("\n- ",$warnings)."\n";}
if($errors){fwrite(STDERR,"ERRORS:\n- ".implode("\n- ",$errors)."\n");exit(1);} echo "Teacher curriculum audit OK\n";
