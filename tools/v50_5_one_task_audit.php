<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/app_source.php';
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root=dirname(__DIR__);$read=static fn(string $f):string=>(string)@file_get_contents($root.'/'.$f);
$index=edu_app_source();$ot=$read('one_task_v50_5.php');$css=$read('assets/one-task-v50-5.css');$js=$read('assets/one-task-v50-5.js');$skill=$read('skill_views.php');$growth=$read('independent_growth_views_v50.php');$goal=$read('goal_navigator_v50_4.php');$lab=$read('hands_on_learning_views_v50.php');$teacher=$read('teacher.php');$coach=$read('student_learning_coach_views_v47.php');$sw=$read('sw.js');
$fail=0;$n=0;$check=function(bool $ok,string $label)use(&$fail,&$n){$n++;echo '['.($ok?'PASS':'FAIL').'] '.$label."\n";if(!$ok)$fail++;};
// i18n (v59): student-facing Czech texty ve views/logice se postupně obalují no-op markerem trm('…')
// (vykreslení pak jde přes tr($label)) – kontrola literálu musí tolerovat holý řetězec i obalený tvar.
// Czech UI text can be plain HTML content, a plain PHP string literal, or wrapped as trm('…')/tr('…')
// (single or double quotes) by the i18n builder – accept any of those forms.
$v505_hasCzech = static function (string $haystack, string $literal): bool {
    if (str_contains($haystack, $literal)) return true;
    foreach (["trm('", 'trm("', "tr('", 'tr("'] as $prefix) if (str_contains($haystack, $prefix . $literal)) return true;
    return false;
};
$v505_hasCzechArray = static function (string $haystack, array $literals): bool {
    $parts = array_map(static fn(string $l): string => '(?:tr(?:m)?\(\s*)?[\'"]' . preg_quote($l, '~') . '[\'"]\s*\)?', $literals);
    return (bool)preg_match('~\[\s*' . implode('\s*,\s*', $parts) . '\s*\]~', $haystack);
};
$check(is_file($root.'/one_task_v50_5.php'),'One Task orchestrator exists');
$check(in_array('one_task_v50_5.php', edu_app_view_libs('one_task'), true),'orchestrator loaded by student runtime');
$check(str_contains($index,"\$view === 'one_task'"),'One Task route wired');
$check(str_contains($index,"str_starts_with(\$action, 'v505_')"),'One Task POST actions routed');
$check(str_contains($ot,"'course'=>v505_course_task"),'course adapter exists');
$check(str_contains($ot,"'kb'=>v505_kb_task"),'knowledge adapter exists');
$check(str_contains($ot,"'skill'=>v505_skill_task"),'skill adapter exists');
$check(str_contains($ot,"'growth'=>v505_growth_task"),'growth adapter exists');
$check(str_contains($ot,"'lab'=>v505_lab_task"),'lab adapter exists');
$check(str_contains($ot,"'result'=>v505_result_task"),'result adapter exists');
$check(str_contains($ot,'learning_next_lesson_progress')&&str_contains($ot,'learning_course_lesson_progress'),'course uses existing progress source');
$check(str_contains($ot,'learning_set_kb_step')===false&&str_contains($js,"body.set('action','kb_step')")&&str_contains($js,"body.set('action','kb_check')"),'knowledge writes through existing progress endpoint');
$check(str_contains($ot,'skill_learning_mark')&&str_contains($ot,'skill_submit_quick_check')&&str_contains($ot,'skill_request_validation'),'skill writes use existing mastery functions');
$check(str_contains($ot,'v50_growth_complete_module'),'growth writes use existing source of truth');
$check(str_contains($ot,'v50_hypothesis_submit')&&str_contains($ot,'v50_command_submit')&&str_contains($ot,'v50_validate_state_submit'),'lab writes use existing hands-on runtime');
$check(str_contains($ot,'project_student_results'),'result adapter reads published project results');
$check(str_contains($ot,"'grade_impact'")===false&&str_contains($ot,"'xp_impact'")===false&&str_contains($ot,"'mastery_impact'")===false,'One Task does not create parallel grading flags');
$check(str_contains($ot,'learning_primary_block_complete')&&str_contains($ot,'learning_course_lesson_unlocked'),'course deep-links respect prerequisites');
$check(str_contains($ot,'v505_safe_return_url')&&str_contains($ot,"str_starts_with(\$raw,'?')"),'return context restricted to local routes');
$check(str_contains($ot,'v505_safe_task_url'),'draft resume task URL validated');
$check(str_contains($ot,"adaptive_v505_"),'One Task runtime uses dedicated adaptive storage namespace');
$check(str_contains($ot,'v505_draft_save')&&str_contains($ot,'v505_draft_clear')&&str_contains($ot,'v505_latest_draft'),'autosave and resume storage implemented');
$check(str_contains($js,"action:'v505_draft_save'")&&str_contains($js,'setTimeout(saveDraft, 650)'),'autosave debounce wired');
$check(str_contains($js,"(e.ctrlKey || e.metaKey) && e.key === 'Enter'"),'keyboard primary action wired');
$check(str_contains($js,"e.key === '?'"),'keyboard help shortcut wired');
$check(str_contains($js,"e.key === 'Escape'"),'keyboard escape wired');
$check($v505_hasCzechArray($ot,['Zadání','Pracuj','Ověř','Další krok']),'four-stage visual flow implemented');
$check(substr_count($ot,'data-ot-primary')>=2&&str_contains($ot,'v505_primary_action'),'single primary CTA resolver used');
$check(str_contains($css,'.ot-actionbar')&&str_contains($css,'position:fixed'),'primary action bar stays persistent');
$check(str_contains($css,'@media(max-width:720px)'),'mobile One Task layout exists');
$check(str_contains($css,'.ot-help-panel[hidden]'),'help uses progressive disclosure');
$check(str_contains($index,'v505_latest_draft')&&$v505_hasCzech($index,'Pokračovat v rozdělaném'),'dashboard resumes unfinished One Task');
$check(str_contains($index,'v505_local_href_to_task')&&str_contains($index,'dashNextAction'), 'dashboard next action bridges to One Task');
// ř. 41: courseFocusTaskHref žije jen v app/views/course_classic.php, což je nedosažitelný pohled
// (tutorial.php ř. 11–23 vždy exit dřív, viz PLAN_F4_PROD.md A.1) – reálně dosažitelné napojení kurzu
// na One Task je app/views/_lesson.php (sdílená šablona lekcí, vkládaná routerem do všech course_* pohledů).
$lesson=$read('app/views/_lesson.php');
$check(str_contains($lesson,"v505_task_url('course'"),'course lesson template (the reachable one) bridges to One Task');
$check(str_contains($skill,"v505_task_url('skill'")&&$v505_hasCzech($skill,'Spustit One Task Mode'),'skill system bridges to One Task');
$check(str_contains($growth,"v505_task_url('growth'")&&$v505_hasCzech($growth,'Pokračovat v One Task'),'growth paths bridge to One Task');
$check(str_contains($goal,'v505_local_href_to_task')&&$v505_hasCzech($goal,'Začít v One Task'),'Goal Navigator bridges to One Task');
$check(str_contains($coach,'v505_local_href_to_task'),'Learning Coach recommendations bridge to One Task');
$check(str_contains($index,'$coachFirst[\'href\']')&&str_contains($index,'v505_local_href_to_task'),'dashboard Learning Coach recommendation preserves its real target');
$check(str_contains($lab,"v505_task_url('lab'")&&$v505_hasCzech($lab,'One Task Mode'),'hands-on labs bridge to One Task');
// ř. 48: "One Task · projít feedback" existuje jen jako marker v _layout.php; skutečné napojení výsledků
// je app/views/project_results.php s tlačítkem "Projít feedback".
$projectResults=$read('app/views/project_results.php');
$check(str_contains($projectResults,"v505_task_url('result'")&&$v505_hasCzech($projectResults,'Projít feedback'),'results bridge to One Task (reachable project_results view)');
$check(str_contains($teacher,'v505_render_teacher_metrics')&&str_contains($teacher,"one_task_v50_5.php"),'teacher analytics receives One Task telemetry');
$check(str_contains($ot,"'started'")&&str_contains($ot,"'retry'")&&str_contains($ot,"'help'")&&str_contains($ot,"'passed'"),'task lifecycle telemetry captured');
// ř. 51/54: "v50-5-one-task" a "v50-4-goal-nav" jsou dnes jen historické komentáře v sw.js, ne živé
// precache záznamy – ověřujeme skutečný obsah SHELL (nahrazuje PLAN_F4_PROD.md A.2 ř. 52-53).
if (preg_match('~const CACHE="(educanet-v\d+[^"]*)";~', $sw, $cm)) { $activeCache = $cm[1]; } else { $activeCache = ''; }
$check($activeCache !== '', 'PWA cache namespace is a versioned educanet-vNN identifier');
if (preg_match('~const SHELL=(\[[^;]+\]);~s', $sw, $sm)) { $activeShell = $sm[1]; } else { $activeShell = ''; }
$check($activeShell !== '' && str_contains($activeShell,'assets/one-task-v50-5.css?v=50.5'),'PWA SHELL precaches One Task CSS');
$check($activeShell !== '' && str_contains($activeShell,'assets/one-task-v50-5.js?v=50.5'),'PWA SHELL precaches One Task JS');
$check($activeShell !== '' && str_contains($activeShell,'assets/one-task-v50-7-7.css?v=50.7.7'),'PWA SHELL also carries the One Task v50.7.7 successor stylesheet (no regression on cache bump)');
if($fail){echo "V50_5_ONE_TASK_AUDIT_FAILED=$fail/$n\n";exit(1);}echo "V50_5_ONE_TASK_AUDIT_OK checks=$n\n";
