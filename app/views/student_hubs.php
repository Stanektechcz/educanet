<?php

declare(strict_types=1);

/**
 * ?view=study, mistakes, study_loop, skills, skill_*, mastery_*, profile, community, project_*, prestige_exams.
 * Přesunuto beze změny z index.php (v58 · F5); spouští ho jen router v index.php (viz app/routes.php).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if ($view === 'study') { guarded_study_redirect(); $coachTourMap=is_array($knowledgeTours[$classId]??null)?$knowledgeTours[$classId]:[]; render_student_coach_view((string)$classId,$module,$coachTourMap,$flash); exit; }
if ($view === 'mistakes') { guarded_study_redirect(); render_student_mistakes_view((string)$classId,$module,$flash); exit; }
if ($view === 'study_loop') { guarded_study_redirect(); $v471TourMap=is_array($knowledgeTours[$classId]??null)?$knowledgeTours[$classId]:[]; v471_render_study_loop((string)$classId,$module,$v471TourMap,$flash); exit; }
if ($view === 'skills') { guarded_study_redirect(); render_skill_overview((string)$classId,$module,$flash); exit; }
if ($view === 'skill_branch') { guarded_study_redirect(); render_skill_branch((string)$classId,$module,(string)($_GET['branch']??''),$flash); exit; }
if ($view === 'skill_detail') { guarded_study_redirect(); render_skill_detail((string)$classId,$module,(string)($_GET['skill']??''),$flash); exit; }
if ($view === 'mastery_challenge') { guarded_study_redirect(); render_mastery_challenge((string)$classId,$module,(string)($_GET['challenge']??''),$flash); exit; }
if ($view === 'mastery_result') { guarded_study_redirect(); render_mastery_result((string)$classId,$module,(string)($_GET['attempt']??'')); exit; }
if ($view === 'profile') { guarded_study_redirect(); render_student_profile_view((string)$classId,$module,$flash); exit; }
if ($view === 'community') { guarded_study_redirect(); render_community_view((string)$classId,$module,$flash); exit; }
if ($view === 'project_lobbies') { guarded_study_redirect(); render_project_lobbies_view((string)$classId,$module,$flash); exit; }
if ($view === 'project_workspace') { guarded_study_redirect(); render_project_workspace_view((string)$classId,$module,$flash); exit; }
if ($view === 'prestige_exams') { guarded_study_redirect(); render_prestige_exams_view((string)$classId,$module,$flash); exit; }
