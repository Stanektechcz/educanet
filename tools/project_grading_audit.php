<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';

$errors=[]; $projectCount=0; $rubricCount=0; $ids=[];
$expected=['class_1a','class_2a','class_3a','class_4a'];
$catalog=project_catalog();
foreach($expected as $classId){
    $projects=$catalog[$classId]??null;
    if(!is_array($projects)||count($projects)<2){$errors[]="$classId: chybí projektový katalog";continue;}
    $hasIndividual=false;$hasGroup=false;
    foreach($projects as $project){
        if(!is_array($project)){$errors[]="$classId: neplatná definice projektu";continue;}
        $projectCount++;
        $id=(string)($project['id']??'');
        if($id===''||isset($ids[$id]))$errors[]="$classId: prázdné nebo duplicitní project id $id";
        $ids[$id]=true;
        $type=(string)($project['type']??'');
        $hasIndividual=$hasIndividual||$type==='individual';$hasGroup=$hasGroup||$type==='group';
        if(!in_array($type,['individual','group'],true))$errors[]="$id: neplatný typ $type";
        $criteria=(array)($project['rubric']??[]); if(!$criteria)$errors[]="$id: chybí rubrika";
        $criterionIds=[];$max=0;
        foreach($criteria as $c){
            if(!is_array($c)){$errors[]="$id: neplatné kritérium";continue;}
            $rubricCount++;$cid=(string)($c['id']??'');$points=(int)($c['max']??0);$max+=$points;
            if($cid===''||isset($criterionIds[$cid]))$errors[]="$id: duplicitní/prázdné criterion id $cid";
            $criterionIds[$cid]=true;if($points<=0)$errors[]="$id/$cid: max musí být >0";
            if(trim((string)($c['description']??''))==='')$errors[]="$id/$cid: chybí popis kritéria";
        }
        if($max<10)$errors[]="$id: příliš malé maximum rubriky ($max)";
    }
    if(!$hasIndividual)$errors[]="$classId: chybí individuální projekt";
    if(!$hasGroup)$errors[]="$classId: chybí skupinový projekt";
}

foreach(project_groups() as $g){
    if(!is_array($g))continue;
    $p=project_find((string)($g['class_id']??''),(string)($g['project_id']??''));
    if(!$p||(string)($p['type']??'')!=='group')$errors[]='Tým '.($g['id']??'?').' odkazuje na neplatný skupinový projekt.';
    if(count((array)($g['member_keys']??[]))<2)$errors[]='Tým '.($g['id']??'?').' má méně než 2 členy.';
}
foreach(project_grade_records() as $r){
    if(!is_array($r))continue;
    $p=project_find((string)($r['class_id']??''),(string)($r['project_id']??''));
    if(!$p){$errors[]='Hodnocení '.($r['id']??'?').' odkazuje na neplatný projekt.';continue;}
    if((int)($r['points']??-1)<0||(int)($r['points']??0)>(int)($r['max_points']??0))$errors[]='Hodnocení '.($r['id']??'?').' má neplatné body.';
    if(!in_array((int)($r['grade']??0),[1,2,3,4,5],true))$errors[]='Hodnocení '.($r['id']??'?').' má neplatnou známku.';
    if(!in_array((string)($r['status']??''),['draft','published','returned'],true))$errors[]='Hodnocení '.($r['id']??'?').' má neplatný stav.';
}

echo "Project grading audit: $projectCount projektů · $rubricCount kritérií · ".count($errors)." chyb\n";
foreach($errors as $e)echo "- $e\n";
exit($errors?1:0);
