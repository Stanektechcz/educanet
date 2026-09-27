<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/teacher_curriculum.php';
$schoolYear = require __DIR__ . '/school_year.php';

$classId = (string)($_GET['class'] ?? ($_SESSION['next_class_id'] ?? ''));
$allowed = ['class_1a','class_2a','class_3a','class_4a'];
if (!in_array($classId,$allowed,true)) { http_response_code(400); exit('Neplatná třída.'); }

$teacher = teacher_export_authenticated();
$studentClass = (string)($_SESSION['next_class_id'] ?? '');
if (!$teacher && $studentClass !== $classId) { http_response_code(403); exit('Kalendář je dostupný pouze pro tvoji třídu.'); }

function ics_escape(string $value): string
{
    return str_replace(["\\",";",",","\r\n","\n","\r"],["\\\\","\\;","\\,","\\n","\\n","\\n"],$value);
}

$lessons=[];
foreach(teacher_curriculum_lessons($classId) as $lesson){if(is_array($lesson))$lessons[(int)($lesson['number']??0)]=$lesson;}
$rows=adaptive_school_year_rows($schoolYear,$classId);
$schedule=adaptive_class_schedule($schoolYear,$classId);
$label=trim((string)($schedule['label']??$classId).' '.(string)($schedule['subject']??''));
$startTime=(string)($schedule['start']??'');
$endTime=(string)($schedule['end']??'');

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="educanet-'.str_replace('class_','',$classId).'-2026-2027.ics"');
echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//EDUCANET//School Year 2026-2027//CS\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\nX-WR-CALNAME:".ics_escape('EDUCANET '.$label)."\r\nX-WR-TIMEZONE:Europe/Prague\r\n";
foreach($rows as $row){
    if(!is_array($row)||(string)($row['status']??'')!=='teaching')continue;
    $date=(string)($row['date']??''); if($date==='')continue;
    $n=(int)($row['lesson_number']??0);
    $title=$n>0?(string)($lessons[$n]['title']??('Lekce '.$n)):(string)($row['title']??'Projekt / Mastery blok');
    $desc=$n>0?(string)($lessons[$n]['goal']??'2 × 45 minut'):(string)($row['description']??'Aplikovaný blok');
    if(!empty($row['auto_shifted']))$desc.=' · Automaticky posunuto z '.date('d.m.Y',strtotime((string)$row['shifted_from']));
    $uid=sha1($classId.'|'.$date.'|'.$title).'@educanet';
    $startDate=date('Ymd',strtotime($date));
    $summary=((string)($schedule['label']??'')!=='' ? (string)$schedule['label'].' · ' : '').$title;
    $room=trim((string)($schedule['room']??''));
    $periods=array_map('intval',(array)($schedule['periods']??[]));
    $desc.=' · '.($startTime!==''&&$endTime!=='' ? $startTime.'–'.$endTime : '2 × 45 minut');
    if($periods)$desc.=' · hodiny '.implode('–',$periods);
    echo "BEGIN:VEVENT\r\nUID:$uid\r\nDTSTAMP:".gmdate('Ymd\THis\Z')."\r\n";
    if($startTime!==''&&$endTime!==''){
        echo "DTSTART;TZID=Europe/Prague:".$startDate.'T'.str_replace(':','',$startTime)."00\r\n";
        echo "DTEND;TZID=Europe/Prague:".$startDate.'T'.str_replace(':','',$endTime)."00\r\n";
    }else{
        $endDate=date('Ymd',strtotime($date.' +1 day'));
        echo "DTSTART;VALUE=DATE:$startDate\r\nDTEND;VALUE=DATE:$endDate\r\n";
    }
    echo "SUMMARY:".ics_escape($summary)."\r\nDESCRIPTION:".ics_escape($desc)."\r\n";
    if($room!=='') echo "LOCATION:".ics_escape($room)."\r\n";
    echo "CATEGORIES:".ics_escape($label)."\r\nEND:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";
