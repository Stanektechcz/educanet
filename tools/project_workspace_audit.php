<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

require dirname(__DIR__) . '/bootstrap.php';

$errors=[];
$roles=project_role_definitions();
$expected=['leader','designer','researcher','developer','presenter','qa'];
if(array_keys($roles)!==$expected)$errors[]='Role catalog must contain exactly Leader, Designer, Researcher, Developer, Presenter and QA in canonical order.';
foreach($roles as $slug=>$role){
    if(count((array)($role['responsibilities']??[]))<5)$errors[]="$slug has fewer than five responsibilities.";
    if(count((array)($role['rubric']??[]))<5)$errors[]="$slug has fewer than five rubric criteria.";
    $pb=project_role_playbook((string)$slug);foreach(['before','during','done'] as $key)if(trim((string)($pb[$key]??''))==='')$errors[]="$slug playbook misses $key.";
}

$groupProjects=0;$mappedProjects=0;
foreach(project_catalog() as $classId=>$projects){
    foreach((array)$projects as $project){
        if(!is_array($project)||(string)($project['type']??'')!=='group')continue;
        $groupProjects++;$pid=(string)$project['id'];$requirements=project_role_requirements((string)$classId,$pid);
        foreach($expected as $role)if(!array_key_exists($role,$requirements))$errors[]="$classId/$pid misses role requirement $role.";
        if(count(array_filter($requirements,static fn($v)=>$v==='required'))<4)$errors[]="$classId/$pid has too few required responsibility areas.";
        $skillReqs=skill_project_requirements((string)$classId,$pid);if($skillReqs)$mappedProjects++;else $errors[]="$classId/$pid has no explicit Skill Tree mapping.";
        foreach($expected as $role){
            foreach(project_role_skill_candidates((string)$classId,$pid,$role) as $slug){if(!skill_find((string)$slug))$errors[]="$classId/$pid/$role references missing skill $slug.";}
        }
    }
}

$statuses=project_task_statuses();
foreach(['todo','in_progress','review','qa','done','blocked'] as $state)if(!isset($statuses[$state]))$errors[]="Missing task status $state.";
$levels=project_role_level_values();
if($levels!==['not_demonstrated'=>0,'developing'=>50,'competent'=>70,'strong'=>85,'exceptional'=>100])$errors[]='Role evaluation scale changed unexpectedly.';

$ach=project_achievement_definitions();$badges=project_badge_definitions();
if(count($ach)<10)$errors[]='Project Workspace should expose at least ten role/collaboration achievements.';
if(count($badges)<3)$errors[]='Project Workspace should expose at least three prestige role badges.';

if($errors){
    fwrite(STDERR,"Project Workspace audit FAILED\n- ".implode("\n- ",$errors)."\n");
    exit(1);
}

echo "Project Workspace audit OK · ".count($roles)." roles · $groupProjects group projects · $mappedProjects skill-mapped · ".count($ach)." achievements · ".count($badges)." prestige badges\n";
