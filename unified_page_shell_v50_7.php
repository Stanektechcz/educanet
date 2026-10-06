<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** EDUCANET v50.7 · Unified Student Page Shell */

function v507_shell_views(): array
{
    return [
        'course_lesson','next_lesson','skill_detail','kb_lesson','recovery','review',
        'project_result','project_workspace','project_lobbies','community','profile',
    ];
}

function v507_current_return_url(string $view): string
{
    $allowed=['lesson','skill','topic','kb_class','reference','return','recovery_date','date','record','project','section','student'];
    $params=['view'=>$view];
    foreach($allowed as $key){
        if(!isset($_GET[$key]) || !is_scalar($_GET[$key])) continue;
        $value=trim((string)$_GET[$key]);
        if($value==='') continue;
        $params[$key]=u_substr($value,0,180);
    }
    return '?'.http_build_query($params,'','&',PHP_QUERY_RFC3986);
}

function v507_page_shell_definition(string $classId,string $view,string $title,array $module,array $nextLessons,array $extendedLessons): ?array
{
    if(!in_array($view,v507_shell_views(),true)) return null;
    $return=v507_current_return_url($view);
    $base=[
        'title'=>$title,
        'section'=>v506_section_for_view($view),
        'instruction'=>trm('Dokonči další krok. Více otevři jen když to potřebuješ.'),
        'back'=>'?view=dashboard',
        'back_label'=>trm('Domů'),
        'primary'=>'#v507-primary-work',
        'primary_label'=>trm('Pokračovat'),
    ];
    switch($view){
        case 'course_lesson':
            $lesson=(string)($_GET['lesson']??'');
            $base=array_replace($base,[
                'section'=>trm('Kurz'),'instruction'=>trm('Splň aktuální krok lekce. Další krok se odemkne automaticky.'),
                'back'=>'?view=course','back_label'=>trm('Kurz'),
                'primary'=>'#v507-primary-work','primary_label'=>trm('Přejít na aktuální krok'),
            ]); break;
        case 'next_lesson':
            $base=array_replace($base,[
                'section'=>trm('Kurz'),'instruction'=>trm('Dokonči jediný aktuální krok. Zbytek lekce nemusíš řešit dopředu.'),
                'back'=>'?view=course','back_label'=>trm('Kurz'),
                'primary'=>'#v507-primary-work','primary_label'=>trm('Přejít na aktuální krok'),
            ]); break;
        case 'skill_detail':
            $skill=(string)($_GET['skill']??'');
            $base=array_replace($base,[
                'section'=>trm('Dovednosti'),'instruction'=>trm('Uděláš jeden krok, který přidá skutečný důkaz k této dovednosti.'),
                'back'=>'?view=skills','back_label'=>trm('Dovednosti'),
                'primary'=>v69_task_url('skill',['skill'=>$skill],$return),'primary_label'=>trm('Pokračovat v dovednosti'),
            ]); break;
        case 'kb_lesson':
            $topic=(string)($_GET['topic']??'');
            $kbClass=(string)($_GET['kb_class']??$classId);
            $crossClass=$kbClass!==''&&!hash_equals($classId,$kbClass);
            $base=array_replace($base,[
                'section'=>trm('Vysvětlení'),'instruction'=>trm('Postupuj po krocích: podívej se na model, vyzkoušej simulaci, projdi postup a ověř pochopení.'),
                'back'=>'?view=knowledgebase','back_label'=>trm('Vysvětlení'),
                'primary'=>'#v507-primary-work','primary_label'=>$crossClass?trm('Otevřít materiál'):trm('Začít lekci'),
            ]); break;
        case 'recovery':
            $base=array_replace($base,[
                'section'=>trm('Dohnat látku'),'instruction'=>trm('Řeš pouze první nedokončený recovery krok. Ostatní kroky se zobrazí až na vyžádání.'),
                'back'=>'?view=calendar','back_label'=>trm('Kalendář'),'primary'=>'#v507-primary-work','primary_label'=>trm('Pokračovat v recovery'),
            ]); break;
        case 'review':
            $base=array_replace($base,[
                'section'=>trm('Opakování'),'instruction'=>trm('Odpověz na krátké opakování. Nejde o známku ani XP.'),
                'back'=>'?view=dashboard','back_label'=>trm('Domů'),'primary'=>'#v507-primary-work','primary_label'=>trm('Začít opakování'),
            ]); break;
        case 'project_result':
            $record=(string)($_GET['record']??'');
            $base=array_replace($base,[
                'section'=>trm('Výsledky'),'instruction'=>trm('Přečti si jeden konkrétní další krok z feedbacku. Podrobný rozpis otevři jen pokud ho potřebuješ.'),
                'back'=>'?view=project_results','back_label'=>trm('Výsledky'),
                'primary'=>v69_task_url('result',['record'=>$record],$return),'primary_label'=>trm('Projít feedback'),
            ]); break;
        case 'project_workspace':
            $base=array_replace($base,[
                'section'=>trm('Týmový projekt'),'instruction'=>trm('Soustřeď se na svůj nejbližší úkol. Role, QA, týmové signály a reflexe jsou sekundární kontext.'),
                'back'=>'?view=project_lobbies','back_label'=>trm('Týmy'),'primary'=>'#v507-primary-work','primary_label'=>trm('Pokračovat na úkolu'),
            ]); break;
        case 'project_lobbies':
            $base=array_replace($base,[
                'section'=>trm('Týmy'),'instruction'=>trm('Pokud už tým máš, pokračuj do workspace. Jinak udělej jen jeden krok: založ lobby nebo se připoj k existující.'),
                'back'=>'?view=community','back_label'=>trm('Třída'),'primary'=>'#v507-primary-work','primary_label'=>trm('Pokračovat s týmem'),
            ]); break;
        case 'community':
            $base=array_replace($base,[
                'section'=>trm('Třída'),'instruction'=>trm('Třídu používej hlavně pro spolupráci. Pokud řešíš projekt, nejrychlejší cesta vede rovnou do týmů.'),
                'back'=>'?view=dashboard','back_label'=>trm('Domů'),'primary'=>'?view=project_lobbies','primary_label'=>trm('Najít nebo otevřít tým'),
            ]); break;
        case 'profile':
            $base=array_replace($base,[
                'section'=>trm('Profil'),'instruction'=>trm('Profil slouží spolupráci. Uprav jen informace, které mají spolužákům pomoct zjistit, s čím můžeš v týmu pomoci.'),
                'back'=>'?view=community','back_label'=>trm('Třída'),'primary'=>'#v507-primary-work','primary_label'=>trm('Upravit profil'),
            ]); break;
    }
    return $base;
}

function v507_render_page_shell(string $classId,string $view,string $title,array $module,array $nextLessons,array $extendedLessons,bool $titleIsContent=false): void
{
    $def=v507_page_shell_definition($classId,$view,$title,$module,$nextLessons,$extendedLessons);
    if(!is_array($def)) return;
    $shellInstruction=(string)$def['instruction'];
    $shellPrimaryLabel=(string)$def['primary_label'];
    ?>
    <section class="v507-page-shell" data-v507-page-shell data-view="<?=e($view)?>">
      <div class="v507-shell-kicker"><a href="<?=e((string)$def['back'])?>"><?=e(tr('Zpět'))?></a></div>
      <div class="v507-shell-main"><div><h1<?=$titleIsContent?edu_content_lang_attr():''?>><?=e((string)$def['title'])?></h1><p><?=e(tr($shellInstruction))?></p></div><div class="v507-shell-actions"><a class="btn primary v507-primary" href="<?=e((string)$def['primary'])?>" data-v507-primary><?=e(tr($shellPrimaryLabel))?></a></div></div>
    </section>
    <?php
}
