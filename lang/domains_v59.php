<?php

declare(strict_types=1);

/**
 * v59 · OPS-02 – manifest převodu UI na tr()/trn() (soubor → doména, jeden zapisující agent na soubor).
 *
 * Zdroj: scratchpad/v59/PLAN_I18N.md, oddíl „Rozdělení“ (plán edu-planner, schváleno integrátorem).
 * `domains` = PHP domény (katalogy lang/<en|uk>/ui/<doména>.php); `js` = JS domény (katalogy
 * lang/<en|uk>/ui/<js_doména>.php, vkládá je edu_tr_json()/edu_tr_json_js()). Cesty jsou relativní
 * ke kořeni projektu. Soubor patří vždy přesně jedné doméně (PHP i JS zvlášť).
 *
 * Používá tools/v59_i18n_audit.php pro extrakci (token_get_all/regex), pokrytí katalogů a hlídání,
 * že soubor s tr()/EduI18n.tr() nezůstal mimo žádnou doménu. `--domain=<d>` audit omezí jen na ni.
 *
 * Nepřevádí se (obsah, mrtvý kód, učitelské větve) – viz PLAN_I18N.md „Rozdělení“ a docs/I18N_V59.md;
 * tento manifest vyjmenovává jen soubory, které builder skutečně převádí (i když jen jejich část –
 * smíšené soubory viz plán, sekce „Kolize s učitelskými účty“).
 */
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

return [
    'domains' => [
        // B1 – přihlášení, účet, session, onboarding (integrátor doplní ř. index.php:58 při vynucené změně hesla).
        'auth' => [
            'app/views/_layout.php',
            'app/views/home.php',
            'app/views/change_password.php',
            'app/views/auth_links.php',
            'app/views/link_account.php',
            'app/views/privacy.php',
            'app/views/activate.php',
            'app/views/join.php',
            'app/views/intake.php',
            'app/lib.php',
            'app/actions/auth.php',
            'app/actions/session_join.php',
            'app/actions/intake.php',
            'intake_v51_views.php',
            'intake_v51.php',
            'session_v53_views.php',
            'session_v53.php',
            'progress.php',
            'unified_page_shell_v50_7.php',
            'zero_friction_v50_6.php',
        ],
        // B2 – lekce, hodina, tutoriál, body.
        'learn' => [
            'app/views/dashboard.php',
            'app/views/lesson_path.php',
            'app/views/hodina.php',
            'app/views/tutorial.php',
            'app/views/tutorial_redirect.php',
            'app/views/structured_lessons.php',
            'app/views/project_results.php',
            'app/actions/lesson_path.php',
            'app/actions/hodina.php',
            'app/actions/points.php',
            'learning_v56.php',
            'learning_v56_views.php',
            'tutorial_v52.php',
            'tutorial_v52_views.php',
            'points_v53.php',
            'student_v55_views.php',
        ],
        // B3 – Knowledge Base, testy, praxe, vizuální/kognitivní simulace (UI, ne obsah simulací).
        'study' => [
            'app/views/_kb.php',
            'app/views/knowledgebase.php',
            'app/views/kb_lesson.php',
            'app/views/kb_quiz.php',
            'app/views/_lesson.php',
            'app/views/test_result.php',
            'app/views/practice.php',
            'app/views/case_study.php',
            'app/views/extra_challenge.php',
            'app/views/review_recovery.php',
            'app/views/_diagrams.php',
            'app/views/lesson_hub.php',
            'app/views/visual_lab.php',
            'app/actions/assessment.php',
            'app/actions/submissions.php',
            'app/actions/mastery.php',
            'app/actions/visual_labs.php',
            'assessment_visuals.php',
            'mastery_learning_views_v41.php',
            'mastery_learning_v41.php',
            'adaptive_lesson_views_v42.php',
            'cognitive_visualization_views_v43.php',
            'learning_studio_views_v44.php',
            'visual_simulation_views_v45.php',
            'visual_practical_learning_views_v48.php',
            'visual_labs_3a_views_v48_1.php',
        ],
        // B4 – One Task, Cíle, Hands-on/Independent growth, Kouč/Akcelerátor/Náprava, dovednosti, projekty, grafika.
        'hubs' => [
            'app/views/one_task.php',
            'app/views/hands_on_growth.php',
            'app/views/student_hubs.php',
            'app/views/graphics_studio.php',
            'app/views/graphics_studio_gate.php',
            'app/views/graphics_guide.php',
            'app/actions/one_task.php',
            'app/actions/coach.php',
            'app/actions/hands_on_growth.php',
            'app/actions/adaptive.php',
            'app/actions/social.php',
            'app/actions/project_workspace.php',
            'app/actions/skills.php',
            'one_task_v50_5.php',
            'goal_navigator_v50_4.php',
            'hands_on_learning_v50.php',
            'hands_on_learning_views_v50.php',
            'independent_growth_v50.php',
            'independent_growth_views_v50.php',
            'student_learning_coach_v47.php',
            'student_learning_coach_views_v47.php',
            'student_learning_accelerator_v47_1.php',
            'student_learning_accelerator_views_v47_1.php',
            'student_corrective_cycle_v47_2.php',
            'student_corrective_cycle_views_v47_2.php',
            'student_social_views.php',
            'skill_views.php',
            'skill_trees.php',
            'project_workspace_views.php',
            'project_workspace.php',
            'profile_v60.php',
            'profile_v60_views.php',
            'profile_v60_ui.php',
        ],
        // v60 · unikátní odznaky (inline SVG) – badges_v60.php.
        'badges' => [
            'badges_v60.php',
        ],
        // v60 · ARN-07 – výzvy spolužákům (1v1 souboje z profilu, opt-in).
        'arena_challenge' => [
            'arena_v60_challenge.php',
            'arena_v60_challenge_views.php',
            'app/actions/arena_challenge.php',
            'arena_v61_duels.php',
            'arena_v61_views.php',
        ],
        // v61 · motivace: denní/týdenní cíle, série, sezónní odznaky (karta na přehledu a v profilu).
        'motivation' => [
            'motivation_v61.php',
            'motivation_v61_views.php',
            'app/actions/motivation.php',
        ],
        // v62 · kompetence: žákovská záložka profilu; učitelský registr competency_v62_teacher_views.php je vždy česky.
        'competency' => [
            'competency_v62_views.php',
        ],
        // v63 · výukové cesty (žákovské UI); obsah cest (paths_v63_content_*.php) a cockpit paths_v63_teacher_views.php jsou česky.
        'paths' => [
            'app/views/paths.php',
            'app/actions/paths.php',
            'paths_v63_views.php',
            'paths_v63_actions.php',
        ],
        // v60 · obchod bodů (žákovské UI); učitelský registr marketplace_v60_teacher_views.php je vždy česky.
        'marketplace' => [
            'app/views/marketplace.php',
            'app/actions/marketplace.php',
            'marketplace_v60.php',
            'marketplace_v60_views.php',
            'marketplace_v61_views.php',
        ],
        // v60 · projekty podle levelu (žákovské UI); učitelský registr projects_v60_teacher_views.php je vždy česky.
        'projects' => [
            'app/views/projects.php',
            'app/actions/projects.php',
            'projects_v60.php',
            'projects_v60_views.php',
        ],
        // v60 · nahlášení chyby / návrh vylepšení (žákovské UI); učitelský registr feedback_v60_teacher_views.php je vždy česky.
        'feedback' => [
            'app/views/feedback.php',
            'app/actions/feedback.php',
            'feedback_v60.php',
            'feedback_v60_views.php',
        ],
        // B5 – Linux Lab, Aréna, hádanka (offline stránka viz js_lab_offline + assets/lab-offline-i18n-v59.json).
        'lab' => [
            'app/views/linux_lab.php',
            'app/views/hadanka.php',
            'app/actions/arena_prefs.php',
            'linux_v57_views.php',
            'linux_v57_lab.php',
            'lab_v57_api.php',
            'linux_v57_shell.php',
            'lab_v58_learning.php',
            'lab_v58_log.php',
            'linux_v58_ext.php',
            'arena_v57.php',
            'arena_v57_views.php',
            'arena_v58_weekly.php',
            'arena_v58_views.php',
            'lab-offline.html',
        ],
        // B6 – Robotí liga, týmové hry, CTF/incident.
        'games' => [
            'app/views/games_v58.php',
            'app/views/hry.php',
            'app/views/arena_events_v58.php',
            'robots_v58_views.php',
            'robots_v58.php',
            'robots_v58_game.php',
            'robots_v58_lang.php',
            'robots_v58_api.php',
        ],
        // B6d – týmové hry: stránka, jádro, registr, kvíz, API (vyčleněno z games kvůli rozsahu).
        'teamgames' => [
            'teamgames_v58_views.php',
            'teamgames_v58_core.php',
            'teamgames_v58_registry.php',
            'teamgames_v58_quiz.php',
            'teamgames_v58_api.php',
        ],
        // B6e – šest týmových her (vyčleněno z games kvůli rozsahu).
        'teamgames_play' => [
            'teamgames_v58_game_bingo.php',
            'teamgames_v58_game_escape.php',
            'teamgames_v58_game_jeopardy.php',
            'teamgames_v58_game_netadmin.php',
            'teamgames_v58_game_relay.php',
            'teamgames_v58_game_tug.php',
        ],
        // B6c – CTF týden, incidenty (vyčleněno z games kvůli rozsahu; vlastní katalogy events.php).
        // v59: nápovědy simulátoru Linuxu ($w->tip()) – UI nápověda, překládá se (výstup programů zůstává anglicky).
        'lab_tips_v57' => [
            'linux_v57_world.php',
            'linux_v57_cmd_files.php',
            'linux_v57_cmd_net.php',
            'linux_v57_cmd_shell.php',
            'linux_v57_cmd_sys.php',
            'linux_v57_cmd_text.php',
        ],
        'lab_tips_v58' => [
            'linux_v58_cmd_archive.php',
            'linux_v58_cmd_cron.php',
            'linux_v58_cmd_extra.php',
            'linux_v58_cmd_extra_calc.php',
            'linux_v58_cmd_extra_sys.php',
            'linux_v58_cmd_git.php',
            'linux_v58_cmd_git_more.php',
            'linux_v58_cmd_media_im.php',
            'linux_v58_cmd_media_tools.php',
            'linux_v58_cmd_ssh.php',
            'linux_v58_cmd_users.php',
        ],
        // v59: popisky kroků (GPS v44, režimy v48.1) z logických souborů – převedl integrátor.
        'study_logic' => [
            'learning_studio_v44.php',
            'visual_labs_3a_v48_1.php',
        ],
        'events' => [
            'arena_v58_events_views.php',
            'arena_v58_ctf.php',
            'arena_v58_incident.php',
            'arena_v58_events_api.php',
        ],
        // v61 · C – navigace žákovské aplikace (drobečky, spodní lišta).
        'nav_v61' => [
            'nav_v61.php',
        ],
        // Integrátor – sdílené soubory, dokončí se až po B1–B6 (F2).
        'shared' => [
            'bootstrap.php',
            'index.php',
            'student_v55.php',
            'sw.js',
        ],
        // Integrátor – jádro i18n samo (zatím bez tr() – viz core_ui katalogy, prázdné je v pořádku).
        'core_ui' => [
            'i18n_v58.php',
            'i18n_v59.php',
        ],
    ],
    'js' => [
        'js_auth' => [
            'assets/ui-v51.js',
            'assets/auth-v54.js',
            'assets/session-v53.js',
            'assets/student-ui-v50-7-7.js',
        ],
        'js_learn' => [
            'assets/tutorial-v52.js',
            'assets/learning-v56.js',
            'assets/student-v55.js',
            'assets/student-coach-v47.js',
        ],
        'js_study' => [
            'assets/app.js',
            'assets/cognitive-v43.js',
            'assets/learning-studio-v44.js',
            'assets/visual-simulation-v45.js',
            'assets/visual-practical-v48.js',
            'assets/visual-labs-3a-v48-1.js',
        ],
        'js_hubs' => [
            'assets/one-task-v50-5.js',
            'assets/hands-on-v50.js',
        ],
        'js_lab' => [
            'assets/linux-v57.js',
            'assets/arena-v57.js',
            'assets/lab-a11y-v58.js',
            'assets/lab-explain-v58.js',
        ],
        'js_lab_offline' => [
            'assets/lab-offline-v58.js',
        ],
        'js_games' => [
            'assets/robots-v58.js',
        ],
        'js_teamgames' => [
            'assets/teamgames-v58.js',
        ],
        'js_events' => [
            'assets/arena-events-v58.js',
        ],
    ],
];
