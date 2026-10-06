<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v50.4 · Goal Navigator
 * One student goal, one current step, one compact route across curriculum,
 * Skill Trees, Independent Growth, labs and evidence. Preference only:
 * selecting a goal never changes grades, XP, curriculum mastery or deadlines.
 */

function v504_goal_catalog(): array
{
    return [
        'class_1a'=>[
            'visual-design'=>['icon'=>'◇','title'=>trm('Grafický design'),'copy'=>'Kompozice, typografie a vizuální systém.','branches'=>['design','typography'],'path'=>'webdesign','finish'=>'Silné portfolio vizuálních návrhů.'],
            'ui-ux'=>['icon'=>'◫','title'=>trm('UI / UX'),'copy'=>'Rozhraní, prototypy a použitelnost.','branches'=>['ui-ux','design'],'path'=>'webdesign','finish'=>'Použitelný prototyp s ověřenou hierarchií.'],
            'webdesign'=>['icon'=>'▤','title'=>trm('Webdesign'),'copy'=>'Od návrhu stránky k responzivnímu systému.','branches'=>['ui-ux','typography'],'path'=>'webdesign','finish'=>'Přístupná responzivní landing page.'],
            'frontend'=>['icon'=>'</>','title'=>trm('Frontend'),'copy'=>'HTML, CSS, JavaScript a komponenty.','branches'=>['ui-ux'],'path'=>'frontend-foundations','finish'=>'Funkční responzivní web bez frameworku.'],
            'php'=>['icon'=>'PHP','title'=>trm('PHP / backend'),'copy'=>'Formuláře, data, session a bezpečný backend.','branches'=>['ui-ux'],'path'=>'php-foundations','finish'=>'Malá bezpečná stateful PHP aplikace.'],
        ],
        'class_2a'=>[
            'visual-design'=>['icon'=>'◇','title'=>trm('Grafický design'),'copy'=>'Kompozice, typografie a vizuální systém.','branches'=>['design','typography'],'path'=>'webdesign','finish'=>'Silné portfolio vizuálních návrhů.'],
            'ui-ux'=>['icon'=>'◫','title'=>trm('UI / UX'),'copy'=>'Rozhraní, prototypy a použitelnost.','branches'=>['ui-ux','design'],'path'=>'ux-engineering','finish'=>'Design system a použitelné komponenty.'],
            'webdesign'=>['icon'=>'▤','title'=>trm('Webdesign'),'copy'=>'Od návrhu stránky k responzivnímu systému.','branches'=>['ui-ux','typography'],'path'=>'webdesign','finish'=>'Přístupná responzivní landing page.'],
            'frontend'=>['icon'=>'</>','title'=>trm('Frontend'),'copy'=>'HTML, CSS, JavaScript a moderní komponenty.','branches'=>['ui-ux'],'path'=>'frontend-foundations','finish'=>'Funkční responzivní web a další cesta k Reactu.'],
            'php'=>['icon'=>'PHP','title'=>trm('PHP / full-stack'),'copy'=>'Backend, databáze, API a webový produkt.','branches'=>['ui-ux'],'path'=>'php-foundations','finish'=>'Bezpečná PHP aplikace s daty a formuláři.'],
        ],
        'class_3a'=>[
            'networking'=>['icon'=>'◎','title'=>trm('Sítě'),'copy'=>'Adresace, routing, služby a diagnostika.','branches'=>['networking'],'path'=>'network-engineering','finish'=>'Navrhni a diagnostikuj funkční síť.'],
            'linux'=>['icon'=>'⌘','title'=>trm('Linux administrace'),'copy'=>'CLI, služby, logy a automatizace.','branches'=>['linux'],'path'=>'linux-automation','finish'=>'Bezpečná Linux automatizace s důkazy.'],
            'security'=>['icon'=>'◈','title'=>trm('Security'),'copy'=>'Hardening, evidence a incident response.','branches'=>['security'],'path'=>'blue-team','finish'=>'Vyřeš obranný incident od důkazu po nápravu.'],
            'devops'=>['icon'=>'⬡','title'=>trm('DevOps'),'copy'=>'Linux, kontejnery, deploy a provoz.','branches'=>['linux','networking'],'path'=>'containers-devops','finish'=>'Reprodukovatelný kontejnerový stack.'],
            'sre'=>['icon'=>'↗','title'=>trm('SRE / provoz'),'copy'=>'Monitoring, spolehlivost a incidenty.','branches'=>['linux','networking','security'],'path'=>'cloud-sre','finish'=>'SLO, runbook a provozní evidence.'],
        ],
        'class_4a'=>[
            'networking'=>['icon'=>'◎','title'=>trm('Sítě'),'copy'=>'Routing, služby, segmentace a troubleshooting.','branches'=>['networking'],'path'=>'network-engineering','finish'=>'Navrhni a diagnostikuj produkční síť.'],
            'linux'=>['icon'=>'⌘','title'=>trm('Linux administrace'),'copy'=>'Služby, systemd, logy a automatizace.','branches'=>['linux'],'path'=>'linux-automation','finish'=>'Bezpečná provozní automatizace s rollbackem.'],
            'security'=>['icon'=>'◈','title'=>trm('Security'),'copy'=>'Hardening, analýza a incident response.','branches'=>['security'],'path'=>'blue-team','finish'=>'Vyřeš incident od evidence po postmortem.'],
            'devops'=>['icon'=>'⬡','title'=>trm('DevOps'),'copy'=>'Kontejnery, CI/CD, proxy a deploy.','branches'=>['linux','networking'],'path'=>'containers-devops','finish'=>'Produkční kontejnerový stack s health checkem.'],
            'sre'=>['icon'=>'↗','title'=>trm('SRE / cloud'),'copy'=>'SLO, observability, resilience a provoz.','branches'=>['linux','networking','security'],'path'=>'cloud-sre','finish'=>'Reliability dossier a provozní runbook.'],
        ],
    ];
}

function v504_goals_for_class(string $classId): array { return (array)(v504_goal_catalog()[$classId] ?? []); }
function v504_goal_student_key(string $classId): string { return adaptive_student_key($classId); }
function v504_goal_rows(): array { return v50_rows('goal_navigator'); }
function v504_goal_selected(string $classId,string $studentKey=''): ?array
{
    $studentKey=$studentKey!==''?$studentKey:v504_goal_student_key($classId);
    if($studentKey==='')return null;
    $row=v504_goal_rows()[$classId.'|'.$studentKey]??null;
    if(!is_array($row))return null;
    $id=(string)($row['goal_id']??'');$def=v504_goals_for_class($classId)[$id]??null;
    if(!is_array($def))return null;
    return array_replace($def,['id'=>$id,'selected_at'=>(string)($row['selected_at']??''),'student_key'=>$studentKey]);
}
function v504_goal_select(string $classId,string $studentKey,string $goalId): array
{
    $goals=v504_goals_for_class($classId);if(!isset($goals[$goalId]))throw new RuntimeException(tr('Tento cíl není pro tvoji třídu dostupný.'));
    if($studentKey==='')throw new RuntimeException(tr('Nepodařilo se určit studentský profil.'));
    $key=$classId.'|'.$studentKey;
    v50_update_row('goal_navigator',$key,static fn(?array $current): array => ['class_id'=>$classId,'student_key'=>$studentKey,'goal_id'=>$goalId,'selected_at'=>date(DATE_ATOM),'grade_impact'=>false,'xp_impact'=>false,'mastery_impact'=>false]);return array_replace($goals[$goalId],['id'=>$goalId]);
}
function v504_goal_clear(string $classId,string $studentKey): void
{
    if($studentKey==='')return;v50_update_row('goal_navigator',$classId.'|'.$studentKey,static fn(?array $current): ?array => null);
}

function v504_goal_course_step(string $classId): ?array
{
    global $nextLessons,$extendedLessons;
    if(!learning_primary_block_complete($classId))return ['kind'=>'course','title'=>tr('Dokonči základní blok'),'copy'=>tr('Nejdřív dokonči právě odemčený školní krok. Tím si postavíš základ pro zvolený směr.'),'href'=>'?view=dashboard','label'=>tr('Pokračovat')];
    $lesson2=is_array($nextLessons[$classId]??null)?$nextLessons[$classId]:null;
    if($lesson2&&!learning_next_lesson_complete($classId,$lesson2))return ['kind'=>'course','title'=>(string)$lesson2['title'],'copy'=>(string)($lesson2['goal']??tr('Navazující školní krok.')),'href'=>'?view=next_lesson','label'=>tr('Pokračovat v lekci')];
    $rows=[];foreach((array)($extendedLessons[$classId]??[]) as $lesson){if(!is_array($lesson))continue;$rows[]=$lesson;}usort($rows,static fn($a,$b)=>(int)($a['number']??0)<=>(int)($b['number']??0));
    foreach($rows as $lesson){$no=(int)($lesson['number']??0);if($no<3)continue;if(!learning_course_lesson_unlocked($classId,$no,$nextLessons,$extendedLessons))continue;if(learning_course_lesson_complete($classId,$lesson))continue;return ['kind'=>'course','title'=>(string)($lesson['title']??tr('Lekce {n}',['n'=>$no])),'copy'=>(string)($lesson['goal']??tr('Navazující školní krok.')),'href'=>module_url('course_lesson',['lesson'=>(string)($lesson['id']??'')]),'label'=>tr('Pokračovat v lekci')];}
    return null;
}

function v504_goal_skill_step(string $classId,array $goal): ?array
{
    $studentKey=skill_current_student_key($classId);$map=skill_progress_map($classId,$studentKey);$best=null;$bestScore=-999.0;
    foreach((array)($goal['branches']??[]) as $branch){foreach(skill_for_branch((string)$branch) as $skill){$slug=(string)($skill['slug']??'');if($slug==='')continue;$p=$map[$slug]??skill_progress($classId,$slug,$studentKey);$state=(string)($p['unlock_state']??'locked');if($state==='locked'||$state==='mastered')continue;$m=(float)($p['mastery_percent']??0);$score=($m>0?50:20)+(100-$m)/20-(int)($skill['tier']??1);if($score>$bestScore){$bestScore=$score;$best=['kind'=>'skill','title'=>(string)($skill['name']??$slug),'copy'=>$m>0?tr('Pokračuj v rozpracované dovednosti.'):tr('Tohle je nejbližší odemčená dovednost pro tvůj cíl.'),'href'=>module_url('skill_detail',['skill'=>$slug]),'label'=>$m>0?tr('Pokračovat'):tr('Začít dovednost'),'mastery'=>(int)round($m)];}}}
    return $best;
}

function v504_goal_growth_target(string $classId,string $studentKey,string $pathId,int $depth=0): ?array
{
    if($pathId===''||$depth>5)return null;$catalog=v50_growth_catalog();$path=$catalog[$pathId]??null;if(!is_array($path))return null;
    $state=v50_growth_state($classId,$studentKey,$pathId);
    if(empty($state['unlocked'])){
        foreach((array)($path['requires']??[]) as $req){if(!is_array($req)||(string)($req['type']??'')!=='path')continue;$pre=(string)($req['path']??'');$preState=v50_growth_state($classId,$studentKey,$pre);$need=(float)($req['min']??0);if((float)($preState['progress']??0)+1e-9<$need){$resolved=v504_goal_growth_target($classId,$studentKey,$pre,$depth+1);if($resolved){$resolved['copy']=tr('Nejdřív potřebuješ tento předstupeň, aby se odemkl cíl „{title}“. ',['title'=>(string)$path['title']]);return $resolved;}}}
        return ['kind'=>'growth','title'=>tr('Odemkni {title}',['title'=>(string)$path['title']]),'copy'=>implode(' ',(array)($state['reasons']??[])),'href'=>'?view=skills','label'=>tr('Posílit základy')];
    }
    $done=(array)($state['completed']??[]);foreach((array)($path['modules']??[]) as $m){if(!is_array($m))continue;$slug=(string)($m['slug']??'');if($slug!==''&&!isset($done[$slug]))return ['kind'=>'growth','title'=>(string)$m['title'],'copy'=>tr('Další malý modul v cestě „{title}“.',['title'=>(string)$path['title']]),'href'=>module_url('growth_path',['path'=>$pathId]).'#goal-current-module','label'=>tr('Otevřít modul'),'path'=>$pathId,'module'=>$slug];}
    return ['kind'=>'evidence','title'=>tr('Dokonči capstone: {title}',['title'=>(string)$path['title']]),'copy'=>(string)($path['capstone']??tr('Přidej vlastní výstup do Skill Passportu.')),'href'=>module_url('growth_path',['path'=>$pathId]).'#goal-capstone','label'=>tr('Dokončit výstup'),'path'=>$pathId];
}

function v504_goal_progress(string $classId,string $studentKey,array $goal): int
{
    $branchScores=[];$branches=skill_branch_snapshot_map($classId,v50_adaptive_to_skill_key($classId,$studentKey));foreach((array)($goal['branches']??[]) as $branch)$branchScores[]=(float)($branches[(string)$branch]['mastery_percent']??0);
    $branch=$branchScores?array_sum($branchScores)/count($branchScores):0.0;$path=(string)($goal['path']??'');$growth=$path!==''?v50_growth_progress_ratio($classId,$studentKey,$path)*100:0.0;
    return (int)round(min(100,($branch*0.65)+($growth*0.35)));
}

function v504_goal_plan(string $classId,string $studentKey,array $goal): array
{
    $steps=[];$course=v504_goal_course_step($classId);if($course)$steps[]=$course;
    $skill=v504_goal_skill_step($classId,$goal);if($skill)$steps[]=$skill;
    $growth=v504_goal_growth_target($classId,$studentKey,(string)($goal['path']??''));if($growth)$steps[]=$growth;
    if(!$steps)$steps[]=['kind'=>'evidence','title'=>tr('Dolož, co už umíš'),'copy'=>tr('Přidej kvalitní výstup nebo projekt do Skill Passportu.'),'href'=>'?view=skill_passport','label'=>tr('Přidat důkaz')];
    return array_slice($steps,0,3);
}

function v504_render_goal_navigator(string $classId,array $module,string $flash=''): void
{
    $studentKey=v504_goal_student_key($classId);$goals=v504_goals_for_class($classId);$selected=v504_goal_selected($classId,$studentKey);render_header(tr('Můj cíl'),$module);
    ?><section class="v504-shell"><?php if($flash!==''):?><div class="notice"><?=e($flash)?></div><?php endif;?>
    <?php if(!$selected): ?>
      <header class="v504-goal-hero"><div class="eyebrow"><?=e(tr('Můj směr'))?></div><h1><?=e(tr('Co chceš umět?'))?></h1><p><?=e(tr('Vyber jeden směr. Nemusíš znát názvy lekcí ani skillů — systém ti vždy ukáže jen další krok.'))?></p></header>
      <div class="v504-goal-grid"><?php foreach($goals as $id=>$goal): $goalTitleLabel=(string)$goal['title']; ?><form method="post" class="v504-goal-choice"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v504_goal_select"><input type="hidden" name="goal_id" value="<?=e((string)$id)?>"><button type="submit"><i><?=e((string)$goal['icon'])?></i><span><strong><?=e(tr($goalTitleLabel))?></strong><small><?=edu_cs((string)$goal['copy'])?></small></span><b><?=e(tr('Vybrat →'))?></b></button></form><?php endforeach;?></div>
      <p class="v504-safe-note"><?=e(tr('Volba cíle nic neznámkuje a nemění povinnou výuku. Jen zjednoduší navigaci.'))?></p>
    <?php else: $progress=v504_goal_progress($classId,$studentKey,$selected);$plan=v504_goal_plan($classId,$studentKey,$selected);$current=$plan[0]??null;$selectedTitleLabel=(string)$selected['title']; ?>
      <header class="v504-goal-summary"><div><div class="eyebrow"><?=e(tr('Můj cíl'))?></div><h1><i><?=e((string)$selected['icon'])?></i><?=e(tr($selectedTitleLabel))?></h1><p><?=edu_cs((string)$selected['finish'])?></p></div><div class="v504-goal-progress"><strong><?=$progress?>%</strong><i><b style="width:<?=$progress?>%"></b></i><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="v504_goal_clear"><button type="submit"><?=e(tr('Změnit cíl'))?></button></form></div></header>
      <?php if(is_array($current)): ?><article class="v504-now"><span><?=e(tr('TEĎ · KROK 1'))?></span><h2><?=edu_cs((string)$current['title'])?></h2><p><?=edu_cs((string)$current['copy'])?></p><a class="btn primary" href="<?=e((string)$current['href'])?>"><?=e(tr('Začít v One Task →'))?></a><small><?=e(tr('Po dokončení se cesta automaticky přepočítá.'))?></small></article><?php endif;?>
      <ol class="v504-route"><?php foreach($plan as $i=>$step): ?><li class="<?=$i===0?'current':''?>"><i><?=$i+1?></i><div><span><?=e($i===0?tr('TEĎ'):($i===1?tr('POTOM'):tr('DÁL')))?></span><strong><?=edu_cs((string)$step['title'])?></strong></div><?php if($i>0):?><a href="<?=e((string)$step['href'])?>"><?=e(tr('Náhled →'))?></a><?php endif;?></li><?php endforeach;?></ol>
      <details class="v504-goal-details"><summary><?=e(tr('Jak se cíl propojuje se systémem?'))?></summary><div><a href="?view=course"><strong><?=e(tr('Kurz'))?></strong><span><?=e(tr('povinné základy'))?></span></a><a href="?view=skills"><strong><?=e(tr('Dovednosti'))?></strong><span><?=e(tr('co se skutečně odemyká'))?></span></a><a href="?view=growth"><strong><?=e(tr('Rozvoj'))?></strong><span><?=e(tr('volitelná specializace'))?></span></a><a href="?view=skill_passport"><strong><?=e(tr('Evidence'))?></strong><span><?=e(tr('co už umíš doložit'))?></span></a></div></details>
    <?php endif; ?></section><?php render_footer();
}
