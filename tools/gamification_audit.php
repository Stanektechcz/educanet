<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/bootstrap.php';

$errors=[];
$badges=learning_badge_definitions();
$achievements=learning_achievement_definitions();
$legacy=['first_step','quick_mind','first_lesson','explorer','simulator','design_ready','troubleshooter','next_block','course_builder','course_finisher','course_advanced','course_six','course_seven','course_eight','course_nine'];
foreach($legacy as $id) if(isset($badges[$id])) $errors[]="Běžný legacy badge stále existuje: $id";
for($level=10;$level<=100;$level+=10){
    $id='level_'.$level;
    if(!isset($badges[$id])) $errors[]="Chybí level milestone badge $id";
    elseif((string)($badges[$id]['rarity']??'')==='') $errors[]="Badge $id nemá raritu";
}
foreach(['exam_perfect','challenge_distinction','project_masterpiece','triple_distinction','knowledge_grandmaster','course_mastery'] as $id){
    if(!isset($badges[$id])) $errors[]="Chybí speciální badge $id";
    elseif(trim((string)($badges[$id]['condition']??''))==='') $errors[]="Badge $id nemá jasnou podmínku";
}
if(count($achievements)<12) $errors[]='Achievementů je příliš málo pro průběžnou motivaci.';
foreach($achievements as $id=>$a){
    if(empty($a['title'])||empty($a['text'])||empty($a['target'])||empty($a['kind'])) $errors[]="Achievement $id nemá kompletní metadata";
}
if($errors){
    fwrite(STDERR,"Gamification audit FAILED\n- ".implode("\n- ",$errors)."\n");
    exit(1);
}
echo 'Gamification audit OK · '.count($badges).' prestige badges · '.count($achievements)." achievements\n";
