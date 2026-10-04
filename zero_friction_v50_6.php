<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
require_once __DIR__ . '/nav_v61.php';   // v61: drobečková navigace místo kompasu

/** EDUCANET v50.6 · Zero Friction Student UX */

function v506_continue_url(string $classId,array $module,array $nextLessons,array $extendedLessons): string
{
    if (active_test()) return '?view=test';
    $studentKey=adaptive_student_key($classId);
    $draft=$studentKey!==''?v505_latest_draft($classId,$studentKey):null;
    if(is_array($draft)&&!empty($draft['task_url'])) return (string)$draft['task_url'];

    if(!learning_primary_block_complete($classId)) return '?view=dashboard';

    $lesson2=is_array($nextLessons[$classId]??null)?$nextLessons[$classId]:null;
    if(is_array($lesson2)&&!learning_next_lesson_complete($classId,$lesson2)){
        return v505_task_url('course',['lesson'=>'next'],'?view=dashboard');
    }
    foreach((array)($extendedLessons[$classId]??[]) as $lesson){
        if(!is_array($lesson))continue;
        $number=(int)($lesson['number']??0);
        if($number<3)continue;
        if(!learning_course_lesson_unlocked($classId,$number,$nextLessons,$extendedLessons))continue;
        if(learning_course_lesson_complete($classId,$lesson))continue;
        return v505_task_url('course',['lesson'=>(string)($lesson['id']??$number)],'?view=dashboard');
    }

    if(function_exists('v504_goal_selected')){
        $goal=v504_goal_selected($classId,$studentKey);
        if(is_array($goal)){
            $plan=v504_goal_plan($classId,$studentKey,$goal);
            $first=$plan[0]??null;
            if(is_array($first)&&!empty($first['href'])){
                return v505_local_href_to_task((string)$first['href'],'?view=goal_nav');
            }
        }
    }
    if(function_exists('skill_next_recommendation')){
        $next=skill_next_recommendation($classId);
        if(is_array($next)&&is_array($next['skill']??null)&&!empty($next['skill']['slug'])){
            return v505_task_url('skill',['skill'=>(string)$next['skill']['slug']], '?view=skills');
        }
    }
    return '?view=goal_nav';
}

function v506_section_for_view(string $view): string
{
    return match($view){
        'dashboard'=>trm('Domů'),
        'course','course_lesson','next_lesson','lesson_kit','knowledgebase','kb_lesson','kb_quiz','study','study_loop','review','recovery','mistakes','visual_lab','hands_on','graphics_studio','graphics_guide','case_study','practice','cognitive_lab'=>trm('Učení'),
        'goal_nav','growth','growth_path','skills','skill_branch','skill_detail','mastery_challenge','skill_passport','prestige_exams','prestige_exam','project_results','project_result'=>trm('Moje cesta'),
        'community','student_directory','calendar','project_lobbies','project_workspace','peer_lab','profile'=>trm('Třída'),
        default=>trm('EDUCANET'),
    };
}

/** v61: místo „kompasu“ (jen název stránky) vykreslí drobečkovou navigaci Domů › sekce › stránka (nav_v61.php). */
function v506_render_student_compass(string $classId,string $view,string $title,array $module,string $continueUrl,bool $titleIsContent=false): void
{
    echo nav61_breadcrumb_html($classId!==''?$classId:null,$view,$title,$titleIsContent);
}
