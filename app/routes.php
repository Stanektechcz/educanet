<?php

declare(strict_types=1);

/**
 * Tabulka rout žákovské aplikace (v58 · F5). Čte ji app_routes() v app/lib.php, používá index.php.
 *
 * JAK TO FUNGUJE
 * - Fáze se vyhodnocují v pořadí, v jakém je volá index.php:
 *     POST:  actions_pre   – akce, které třídu nepotřebují nebo si ji ověří samy (přihlášení, v56_*, sess53_* …)
 *            (index.php pak vyžaduje třídu; bez ní přesměruje na ?view=home, jinak nastaví $classId a $module)
 *            actions_class – akce, které spoléhají na $classId a $module z index.php
 *            Neznámá akce se třídou pokračuje na GET pohled (stejně jako před rozdělením).
 *     GET:   views_early   – před vynucenou změnou hesla (activate, join)
 *            [index.php: vynucená změna hesla → pages.change_password]
 *            views_public  – odkazy z e-mailu, soukromí, propojení účtu
 *            [index.php: bez třídy vždy přihlašovací stránka → pages.home]
 *            views_student – žákovské pohledy; na konci index.php přesměruje na ?view=dashboard
 * - V rámci fáze se vloží VŠECHNY záznamy, jejichž 'match' odpovídá, a to v pořadí tabulky.
 *   Soubor si svou podmínku (např. `if ($view === 'kb_quiz') { … exit; }`) drží sám, takže záznam,
 *   který stránku nevykreslí, jen propustí request dál – pořadí tedy odpovídá původnímu index.php.
 * - 'match': přesný název, 'prefix_*' nebo '*'.
 * - 'file':  cesta relativní k app/.
 * - 'libs':  skupiny knihoven z klíče 'libs', které se líně načtou (require_once) těsně před vložením souboru.
 *            Skupiny musí pokrýt všechny funkce/konstanty/třídy, které segment volá – hlídá to
 *            tests/app_router_audit.php (statická analýza volání). Jádro (bootstrap.php a vše, co načítá,
 *            runtime_content.php, skupina 'core') je načtené vždy.
 * - 'session' => 'read': pohled prokazatelně nezapisuje do session → app_release_session() uvolní zámek
 *            (DAT-04). Audit ověřuje, že v dosahu segmentu není zápis do $_SESSION.
 *
 * NOVÝ POHLED (např. ?view=roboti)
 *   1. Vytvoř app/views/roboti.php: `<?php declare(strict_types=1);`, guard knihovny (zkopíruj z jiného
 *      souboru v app/) a kód `if ($view === 'roboti') { guarded_study_redirect(); … render_header(…); … render_footer(); exit; }`.
 *      Kód běží v globálním rozsahu: $modules, $classId, $module, $flash, $schoolYear, $nextLessons … jsou k dispozici.
 *   2. Do 'libs' přidej skupinu, např. 'robots' => ['robots_v58.php', 'robots_v58_views.php'].
 *   3. Do 'views_student' přidej ['match' => ['roboti'], 'file' => 'views/roboti.php', 'libs' => ['layout', 'robots']].
 *   4. Spusť `C:/php/php.exe tests/app_router_audit.php` (musí skončit APP_ROUTER_AUDIT_OK).
 * NOVÁ POST AKCE
 *   1. app/actions/<domena>.php s blokem `if ($action === 'robots_run') { … redirect_to(…); }` (CSRF už ověřil index.php).
 *   2. Záznam do 'actions_pre' (akce si třídu ověří sama přes current_class_id()) nebo do 'actions_class'.
 *   3. Audit jako výše.
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    // Skupiny knihoven (cesty od kořene projektu). Pořadí souborů = pořadí načtení.
    'libs' => [
        // Vždy (index.php): hromadné zakládání účtů a import dotazníků běží na každém requestu.
        'core' => ['accounts_v53.php', 'intake_v51.php'],
        // Hlavička a patička žákovských stránek (render_header/render_footer) a vše, co volají.
        // arena_v57.php si při načtení vyžádá linux_v57_lab.php (a ten celý simulátor) – viz INTEGRATION.md.
        'layout' => ['app/views/_layout.php', 'student_v55.php', 'student_v55_views.php', 'zero_friction_v50_6.php', 'unified_page_shell_v50_7.php', 'one_task_v50_5.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php', 'independent_growth_v50.php', 'learning_v56.php', 'session_v53.php', 'tutorial_v52.php', 'linux_v57_lab.php', 'arena_v57.php'],
        'intake_views' => ['intake_v51_views.php'],
        'session_views' => ['session_v53.php', 'session_v53_views.php'],
        'one_task' => ['one_task_v50_5.php', 'goal_navigator_v50_4.php', 'hands_on_learning_v50.php', 'independent_growth_v50.php', 'zero_friction_v50_6.php', 'teacher_operations_v46.php', 'project_workspace_views.php'],
        'visual_lab' => ['visual_simulation_views_v45.php', 'visual_practical_learning_views_v48.php', 'visual_labs_3a_views_v48_1.php'],
        'hands_on_views' => ['hands_on_learning_v50.php', 'independent_growth_v50.php', 'hands_on_learning_views_v50.php', 'independent_growth_views_v50.php'],
        'lesson_kit_views' => ['adaptive_lesson_views_v42.php', 'cognitive_visualization_views_v43.php', 'learning_studio_views_v44.php', 'visual_simulation_views_v45.php'],
        'tutorial_views' => ['tutorial_v52.php', 'points_v53.php', 'tutorial_v52_views.php'],
        'lesson_path_views' => ['learning_v56.php', 'learning_v56_views.php'],
        // v58 · Robotí liga (LAB-01); další herní moduly v58 přidává integrátor po dokončení.
        'robots' => ['robots_v58.php', 'robots_v58_game.php', 'robots_v58_views.php'],
        // v58 · CTF týden a Incidenty (ARN-02, ARN-03).
        'arena_events' => ['arena_v58_ctf.php', 'arena_v58_incident.php', 'arena_v58_events_views.php'],
        // v58 · Týmové hry (6 her; views si samy načtou registr, kvíz a herní moduly).
        'teamgames' => ['teamgames_v58_views.php'],
        // v58 LAB-08: odznaky za Linux dovednosti na tabuli odznaků v55 (profil a studijní centra).
        'lab_badges' => ['lab_v58_learning.php'],
        // v58 · ARN-01 Týdenní hádanka (golfové úlohy, mimo třídní závod).
        'weekly' => ['arena_v58_weekly.php', 'arena_v58_views.php'],
        'linux_lab' => ['linux_v57_core.php', 'linux_v57_world.php', 'linux_v57_shell.php', 'linux_v57_cmd_files.php', 'linux_v57_cmd_shell.php', 'linux_v57_cmd_text.php', 'linux_v57_cmd_sys.php', 'linux_v57_cmd_net.php', 'linux_v57_manual.php', 'linux_v57_levels.php', 'linux_v57_levels_ops.php', 'linux_v57_lab.php', 'linux_v57_views.php', 'arena_v57.php', 'arena_v57_views.php', 'lab_v58_learning.php', 'lab_v58_review.php'],
        'kb' => ['assessment_visuals.php', 'app/views/_kb.php'],
        'lesson_page' => ['reality_demos.php', 'assessment_visuals.php', 'mastery_learning_views_v41.php', 'app/views/_lesson.php'],
        'structured_lesson' => ['reality_demos.php', 'assessment_visuals.php', 'adaptive_lesson_views_v42.php', 'cognitive_visualization_views_v43.php', 'learning_studio_views_v44.php', 'visual_simulation_views_v45.php', 'app/views/_lesson.php'],
        'student_hubs' => ['teacher_operations_v46.php', 'student_learning_coach_v47.php', 'student_learning_coach_views_v47.php', 'student_learning_accelerator_v47_1.php', 'student_learning_accelerator_views_v47_1.php', 'student_corrective_cycle_v47_2.php', 'student_corrective_cycle_views_v47_2.php', 'student_social_views.php', 'skill_views.php', 'project_workspace_views.php', 'points_v53.php', 'points_v60.php', 'learning_v56_views.php', 'profile_v60.php', 'profile_v60_views.php', 'marketplace_v60.php', 'arena_v60_challenge.php', 'arena_v60_challenge_views.php'],
        // v60 · obchod bodů (?view=obchod).
        'marketplace' => ['points_v53.php', 'points_v60.php', 'marketplace_v60.php', 'marketplace_v60_views.php'],
        // v60 · projekty podle levelu (?view=projekty).
        'projects' => ['projects_v60.php', 'projects_v60_views.php'],
        'dashboard' => ['teacher_operations_v46.php', 'student_learning_coach_v47.php', 'student_learning_coach_views_v47.php', 'mastery_learning_views_v41.php', 'learning_studio_views_v44.php', 'skill_views.php', 'project_workspace_views.php'],
        'diagrams' => ['app/views/_diagrams.php'],
        // POST
        'lesson_path' => ['tutorial_v52.php', 'session_v53.php', 'learning_v56.php'],
        'hodina' => ['tutorial_v52.php', 'session_v53.php', 'student_v55.php'],
        'session' => ['session_v53.php'],
        'points' => ['tutorial_v52.php', 'points_v53.php'],
        'teacher_ops' => ['teacher_operations_v46.php'],
        'coach' => ['student_learning_coach_v47.php', 'student_learning_accelerator_v47_1.php', 'student_corrective_cycle_v47_2.php'],
        'hands_on' => ['hands_on_learning_v50.php', 'independent_growth_v50.php', 'goal_navigator_v50_4.php'],
        'mastery' => ['teacher_operations_v46.php', 'reality_demos.php', 'assessment_visuals.php'],
        'project_workspace' => ['teacher_operations_v46.php', 'project_workspace_views.php'],
        'auth' => ['session_v53.php', 'student_v55.php'],
    ],
    // Stránky, které vkládá přímo index.php (globální ochrany).
    'pages' => [
        'change_password' => ['file' => 'views/change_password.php', 'libs' => ['layout']],
        'home' => ['file' => 'views/home.php', 'libs' => ['layout']],
    ],
    'views_early' => [
        ['match' => ['activate'], 'file' => 'views/activate.php', 'libs' => ['layout', 'intake_views']],
        ['match' => ['join'], 'file' => 'views/join.php', 'libs' => ['layout', 'session_views']],
    ],
    'views_public' => [
        ['match' => ['verify_email', 'reset_password'], 'file' => 'views/auth_links.php', 'libs' => ['layout']],
        ['match' => ['privacy'], 'file' => 'views/privacy.php', 'libs' => ['layout']],
        ['match' => ['link_account'], 'file' => 'views/link_account.php', 'libs' => ['layout']],
    ],
    'views_student' => [
        ['match' => ['intake', 'my_intake', 'my_intake_file'], 'file' => 'views/intake.php', 'libs' => ['layout', 'intake_views']],
        ['match' => ['v48_state'], 'file' => 'views/v48_state.php', 'libs' => [], 'session' => 'read'],
        ['match' => ['continue', 'one_task'], 'file' => 'views/one_task.php', 'libs' => ['one_task']],
        ['match' => ['visual_lab'], 'file' => 'views/visual_lab.php', 'libs' => ['layout', 'visual_lab']],
        ['match' => ['hands_on', 'goal_nav', 'growth', 'growth_path', 'skill_passport', 'peer_lab'], 'file' => 'views/hands_on_growth.php', 'libs' => ['layout', 'hands_on_views']],
        ['match' => ['cognitive_lab', 'lesson_kit', 'lesson_slides'], 'file' => 'views/lesson_hub.php', 'libs' => ['layout', 'lesson_kit_views']],
        ['match' => ['course', 'topics', 'tools', 'knowledgebase'], 'file' => 'views/materials_redirect.php', 'libs' => []],
        ['match' => ['materialy', 'vysledky', 'lekce'], 'file' => 'views/lesson_path.php', 'libs' => ['layout', 'tutorial_views', 'lesson_path_views']],
        ['match' => ['lab', 'prikazy'], 'file' => 'views/linux_lab.php', 'libs' => ['layout', 'linux_lab']],
        ['match' => ['roboti'], 'file' => 'views/games_v58.php', 'libs' => ['layout', 'robots']],
        ['match' => ['ctf', 'incident'], 'file' => 'views/arena_events_v58.php', 'libs' => ['layout', 'linux_lab', 'arena_events']],
        ['match' => ['hry'], 'file' => 'views/hry.php', 'libs' => ['layout', 'linux_lab', 'teamgames']],
        ['match' => ['hadanka'], 'file' => 'views/hadanka.php', 'libs' => ['layout', 'linux_lab', 'weekly']],
        ['match' => ['obchod'], 'file' => 'views/marketplace.php', 'libs' => ['layout', 'marketplace']],
        ['match' => ['projekty'], 'file' => 'views/projects.php', 'libs' => ['layout', 'projects']],
        ['match' => ['hodina'], 'file' => 'views/hodina.php', 'libs' => ['layout', 'tutorial_views', 'lesson_path_views']],
        ['match' => ['course_lesson', 'next_lesson'], 'file' => 'views/tutorial_redirect.php', 'libs' => []],
        ['match' => ['course', 'calendar', 'tutorial', 'topics', 'tools', 'knowledgebase'], 'file' => 'views/tutorial.php', 'libs' => ['layout', 'tutorial_views']],
        ['match' => ['knowledgebase'], 'file' => 'views/knowledgebase.php', 'libs' => ['layout']],
        ['match' => ['kb_lesson'], 'file' => 'views/kb_lesson.php', 'libs' => ['layout', 'kb', 'lesson_page']],
        ['match' => ['kb_quiz'], 'file' => 'views/kb_quiz.php', 'libs' => ['layout', 'kb']],
        ['match' => ['graphics_studio'], 'file' => 'views/graphics_studio_gate.php', 'libs' => []],
        // Nedosažitelné (calendar vždy obslouží tutorial.php) – ponecháno: tools/v32_deployment_lesson_mode_audit.php
        // a tools/v33_schedule_performance_audit.php čtou edu_app_source() a spoléhají na text „Přidat svůj rozvrh
        // (.ics)“/„Každou středu ·“, který jinde v app/ není (viz educanet-retired/v59-F4/MANIFEST.md). NEMAZAT bez
        // přepsání těch auditů na skutečně dosažitelnou stránku (tut52_render_calendar()).
        ['match' => ['calendar'], 'file' => 'views/calendar_classic.php', 'libs' => ['layout']],
        ['match' => ['next_lesson', 'course_lesson'], 'file' => 'views/structured_lessons.php', 'libs' => ['layout', 'structured_lesson']],
        ['match' => ['study', 'mistakes', 'study_loop', 'skills', 'skill_branch', 'skill_detail', 'mastery_challenge', 'mastery_result', 'profile', 'community', 'project_lobbies', 'project_workspace', 'prestige_exams'], 'file' => 'views/student_hubs.php', 'libs' => ['layout', 'student_hubs', 'lab_badges']],
        ['match' => ['project_result', 'project_results'], 'file' => 'views/project_results.php', 'libs' => ['layout']],
        ['match' => ['review', 'recovery', 'create_challenge'], 'file' => 'views/review_recovery.php', 'libs' => ['layout']],
        ['match' => ['dashboard'], 'file' => 'views/dashboard.php', 'libs' => ['layout', 'dashboard']],
        ['match' => ['case_study'], 'file' => 'views/case_study.php', 'libs' => ['layout', 'diagrams']],
        ['match' => ['test', 'result'], 'file' => 'views/test_result.php', 'libs' => ['layout', 'kb']],
        ['match' => ['practice', 'practice_done'], 'file' => 'views/practice.php', 'libs' => ['layout', 'diagrams']],
        ['match' => ['extra_challenge'], 'file' => 'views/extra_challenge.php', 'libs' => ['layout']],
        ['match' => ['graphics_studio'], 'file' => 'views/graphics_studio.php', 'libs' => ['layout']],
        ['match' => ['graphics_guide'], 'file' => 'views/graphics_guide.php', 'libs' => ['layout']],
    ],
    'actions_pre' => [
        ['match' => ['intake_*'], 'file' => 'actions/intake.php', 'libs' => []],
        // v58 OPS-02: přepnutí jazyka rozhraní (funguje i bez třídy – např. na přihlašovací stránce).
        ['match' => ['edu_set_lang'], 'file' => 'actions/i18n.php', 'libs' => []],
        // v58 EDU-01: krok projektu může vyžadovat vyřešenou úroveň labu → odevzdání potřebuje i knihovny labu.
        ['match' => ['v56_*'], 'file' => 'actions/lesson_path.php', 'libs' => ['lesson_path', 'linux_lab']],
        ['match' => ['v55_*'], 'file' => 'actions/hodina.php', 'libs' => ['hodina']],
        ['match' => ['arena58_rank_toggle'], 'file' => 'actions/arena_prefs.php', 'libs' => ['linux_lab']],
        ['match' => ['sess53_*'], 'file' => 'actions/session_join.php', 'libs' => ['session']],
        ['match' => ['pts53_hint', 'tut52_score'], 'file' => 'actions/points.php', 'libs' => ['points']],
        ['match' => ['v505_*'], 'file' => 'actions/one_task.php', 'libs' => ['one_task']],
        ['match' => ['teacher_task_complete', 'coach_*'], 'file' => 'actions/coach.php', 'libs' => ['coach']],
        ['match' => ['v42_set_lane', 'v481_deep_submit', 'v48_*', 'v45_sim_save', 'cv43_*', 'v44_*'], 'file' => 'actions/visual_labs.php', 'libs' => []],
        ['match' => ['v50_*', 'v504_*'], 'file' => 'actions/hands_on_growth.php', 'libs' => ['hands_on']],
        ['match' => ['adaptive_*'], 'file' => 'actions/adaptive.php', 'libs' => ['teacher_ops']],
        ['match' => ['ml_*'], 'file' => 'actions/mastery.php', 'libs' => ['mastery']],
        ['match' => ['save_student_profile', 'friend_*', 'team_*', 'submit_prestige_exam'], 'file' => 'actions/social.php', 'libs' => ['teacher_ops']],
        ['match' => ['project_*'], 'file' => 'actions/project_workspace.php', 'libs' => ['project_workspace']],
        ['match' => ['skill_*'], 'file' => 'actions/skills.php', 'libs' => ['teacher_ops']],
        ['match' => ['google_login', 'local_register', 'local_login', 'acc53_change_password', 'local_resend_verification', 'local_forgot_password', 'local_reset_password', 'link_google_account', 'link_account', 'enter_class', 'logout_class'], 'file' => 'actions/auth.php', 'libs' => ['auth']],
    ],
    'actions_class' => [
        ['match' => ['start_test', 'answer', 'continue_test', 'abort_test', 'start_practice', 'practice_hint', 'practice_answer', 'continue_practice', 'abort_practice'], 'file' => 'actions/assessment.php', 'libs' => []],
        ['match' => ['submit_extra', 'submit_graphics'], 'file' => 'actions/submissions.php', 'libs' => []],
        ['match' => ['mkt60_buy', 'mkt60_cosmetic_set'], 'file' => 'actions/marketplace.php', 'libs' => ['marketplace']],
        ['match' => ['proj60_apply', 'proj60_withdraw'], 'file' => 'actions/projects.php', 'libs' => ['projects']],
        // v60 · ARN-07 – výzvy spolužákům (opt-in 1v1 souboje z profilu).
        ['match' => ['arena60_challenge_create', 'arena60_challenge_respond', 'arena60_challenge_cancel', 'arena60_optin_set'], 'file' => 'actions/arena_challenge.php', 'libs' => ['layout', 'student_hubs']],
    ],
];
