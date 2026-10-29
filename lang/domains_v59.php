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
            'src/student/intake_v51_views.php',
            'src/student/intake_v51.php',
            'src/lessons/session_v53_views.php',
            'src/lessons/session_v53.php',
            'progress.php',
            'src/ui/unified_page_shell_v50_7.php',
            'src/student/zero_friction_v50_6.php',
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
            'src/lessons/learning_v56.php',
            'src/lessons/learning_v56_views.php',
            'src/lessons/tutorial_v52.php',
            'src/lessons/tutorial_v52_views.php',
            'src/student/points_v53.php',
            'src/student/student_v55_views.php',
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
            'src/assessment/assessment_visuals.php',
            'src/lessons/mastery_learning_views_v41.php',
            'src/lessons/mastery_learning_v41.php',
            'src/lessons/adaptive_lesson_views_v42.php',
            'src/lessons/cognitive_visualization_views_v43.php',
            'src/lessons/learning_studio_views_v44.php',
            'src/lessons/visual_simulation_views_v45.php',
            'src/lessons/visual_practical_learning_views_v48.php',
            'src/lessons/visual_labs_3a_views_v48_1.php',
        ],
        // B4 – One Task, Cíle, Hands-on/Independent growth, Kouč/Akcelerátor/Náprava, dovednosti, projekty, grafika.
        'hubs' => [
            'app/views/hands_on_growth.php',
            'app/views/student_hubs.php',
            'app/views/graphics_studio.php',
            'app/views/graphics_studio_gate.php',
            'app/views/graphics_guide.php',
            'app/actions/coach.php',
            'app/actions/hands_on_growth.php',
            'app/actions/adaptive.php',
            'app/actions/social.php',
            'app/actions/project_workspace.php',
            'app/actions/skills.php',
            'src/student/goal_navigator_v50_4.php',
            'src/lessons/hands_on_learning_v50.php',
            'src/lessons/hands_on_learning_views_v50.php',
            'src/student/independent_growth_v50.php',
            'src/student/independent_growth_views_v50.php',
            'src/student/student_learning_coach_v47.php',
            'src/student/student_learning_coach_views_v47.php',
            'src/student/student_learning_accelerator_v47_1.php',
            'src/student/student_learning_accelerator_views_v47_1.php',
            'src/student/student_corrective_cycle_v47_2.php',
            'src/student/student_corrective_cycle_views_v47_2.php',
            'src/student/student_social_views.php',
            'src/assessment/skill_views.php',
            'src/assessment/skill_trees.php',
            'src/projects/project_workspace_views.php',
            'src/projects/project_workspace.php',
            'src/student/profile_v60.php',
            'src/student/profile_v60_views.php',
            'src/student/profile_v60_ui.php',
        ],
        // v60 · unikátní odznaky (inline SVG) – badges_v60.php.
        'badges' => [
            'src/student/badges_v60.php',
        ],
        // v60 · ARN-07 – výzvy spolužákům (1v1 souboje z profilu, opt-in).
        'arena_challenge' => [
            'src/arena/arena_v60_challenge.php',
            'src/arena/arena_v60_challenge_views.php',
            'app/actions/arena_challenge.php',
            'src/arena/arena_v61_duels.php',
            'src/arena/arena_v61_views.php',
        ],
        // v61 · motivace: denní/týdenní cíle, série, sezónní odznaky (karta na přehledu a v profilu).
        'motivation' => [
            'src/student/motivation_v61.php',
            'src/student/motivation_v61_views.php',
            'app/actions/motivation.php',
        ],
        // v62 · kompetence: žákovská záložka profilu; učitelský registr competency_v62_teacher_views.php je vždy česky.
        'competency' => [
            'src/assessment/competency_v62_views.php',
            'src/assessment/competency_v75_labels.php',   // v75: názvy kompetencí 2.A a 4.A
        ],
        // v64 · hry, ligy a férová ekonomika (žákovské UI); učitelský registr economy_v64_teacher_views.php je vždy česky.
        'games64' => [
            'src/arena/arena_v64_fair.php',
            'src/arena/arena_v64_views.php',
            'src/teamgames/teamgames_v64_roles.php',
            'src/teamgames/teamgames_v64_views.php',
            'app/actions/teamgames_v64.php',
            'src/student/challenges_v64.php',
        ],
        // v63 · výukové cesty (žákovské UI); obsah cest (paths_v63_content_*.php) a cockpit paths_v63_teacher_views.php jsou česky.
        'paths' => [
            'app/views/paths.php',
            'app/actions/paths.php',
            'src/paths/paths_v63_views.php',
            'src/paths/paths_v63_actions.php',
        ],
        // v66 · Moje hodnocení (žákovské UI); cockpit assessment_v66_teacher_*.php je vždy česky.
        'grading_v66' => [
            'app/views/hodnoceni.php',
            'src/assessment/grading_v66_views.php',
        ],
        // v60 · obchod bodů (žákovské UI); učitelský registr marketplace_v60_teacher_views.php je vždy česky.
        'marketplace' => [
            'app/views/marketplace.php',
            'app/actions/marketplace.php',
            'src/student/marketplace_v60.php',
            'src/student/marketplace_v60_views.php',
            'src/student/marketplace_v61_views.php',
        ],
        // v60 · projekty podle levelu (žákovské UI); učitelský registr projects_v60_teacher_views.php je vždy česky.
        'projects' => [
            'app/views/projects.php',
            'app/actions/projects.php',
            'src/projects/projects_v60.php',
            'src/projects/projects_v60_views.php',
        ],
        // v65 · cyklus projektu, peer review, týmové prvky a portfolio (žákovské UI); učitelský registr projects_v65_teacher_*.php je vždy česky.
        'projects_v65' => [
            'app/views/projects_v65.php',
            'app/views/portfolio.php',
            'app/actions/projects_v65.php',
            'src/projects/projects_v65_views.php',
            'src/projects/projects_v65_detail_views.php',
            'src/student/portfolio_v65_views.php',
        ],
        // v60 · nahlášení chyby / návrh vylepšení (žákovské UI); učitelský registr feedback_v60_teacher_views.php je vždy česky.
        'feedback' => [
            'app/views/feedback.php',
            'app/actions/feedback.php',
            'src/student/feedback_v60.php',
            'src/student/feedback_v60_views.php',
        ],
        // B5 – Linux Lab, Aréna, hádanka (offline stránka viz js_lab_offline + assets/lab-offline-i18n-v59.json).
        'lab' => [
            'app/views/linux_lab.php',
            'app/views/hadanka.php',
            'app/actions/arena_prefs.php',
            'src/linux/linux_v57_views.php',
            'src/linux/linux_v57_lab.php',
            'lab_v57_api.php',
            'src/linux/linux_v57_shell.php',
            'src/linux/lab_v58_learning.php',
            'src/linux/lab_v58_log.php',
            'src/linux/linux_v58_ext.php',
            'src/arena/arena_v57.php',
            'src/arena/arena_v57_views.php',
            'src/arena/arena_v58_weekly.php',
            'src/arena/arena_v58_views.php',
            'lab-offline.html',
        ],
        // B6 – Robotí liga, týmové hry, CTF/incident.
        'games' => [
            'app/views/games_v58.php',
            'app/views/hry.php',
            'app/views/arena_events_v58.php',
            'src/robots/robots_v58_views.php',
            'src/robots/robots_v58.php',
            'src/robots/robots_v58_game.php',
            'src/robots/robots_v58_lang.php',
            'robots_v58_api.php',
        ],
        // B6d – týmové hry: stránka, jádro, registr, kvíz, API (vyčleněno z games kvůli rozsahu).
        'teamgames' => [
            'src/teamgames/teamgames_v58_views.php',
            'src/teamgames/teamgames_v58_core.php',
            'src/teamgames/teamgames_v58_registry.php',
            'src/teamgames/teamgames_v58_quiz.php',
            'teamgames_v58_api.php',
        ],
        // B6e – šest týmových her (vyčleněno z games kvůli rozsahu).
        'teamgames_play' => [
            'src/teamgames/teamgames_v58_game_bingo.php',
            'src/teamgames/teamgames_v58_game_escape.php',
            'src/teamgames/teamgames_v58_game_jeopardy.php',
            'src/teamgames/teamgames_v58_game_netadmin.php',
            'src/teamgames/teamgames_v58_game_relay.php',
            'src/teamgames/teamgames_v58_game_tug.php',
        ],
        // B6c – CTF týden, incidenty (vyčleněno z games kvůli rozsahu; vlastní katalogy events.php).
        // v59: nápovědy simulátoru Linuxu ($w->tip()) – UI nápověda, překládá se (výstup programů zůstává anglicky).
        'lab_tips_v57' => [
            'src/linux/linux_v57_world.php',
            'src/linux/linux_v57_cmd_files.php',
            'src/linux/linux_v57_cmd_net.php',
            'src/linux/linux_v57_cmd_shell.php',
            'src/linux/linux_v57_cmd_sys.php',
            'src/linux/linux_v57_cmd_text.php',
        ],
        'lab_tips_v58' => [
            'src/linux/linux_v58_cmd_archive.php',
            'src/linux/linux_v58_cmd_cron.php',
            'src/linux/linux_v58_cmd_extra.php',
            'src/linux/linux_v58_cmd_extra_calc.php',
            'src/linux/linux_v58_cmd_extra_sys.php',
            'src/linux/linux_v58_cmd_git.php',
            'src/linux/linux_v58_cmd_git_more.php',
            'src/linux/linux_v58_cmd_media_im.php',
            'src/linux/linux_v58_cmd_media_tools.php',
            'src/linux/linux_v58_cmd_ssh.php',
            'src/linux/linux_v58_cmd_users.php',
        ],
        // v59: popisky kroků (GPS v44, režimy v48.1) z logických souborů – převedl integrátor.
        'study_logic' => [
            'src/lessons/learning_studio_v44.php',
            'src/lessons/visual_labs_3a_v48_1.php',
        ],
        'events' => [
            'src/arena/arena_v58_events_views.php',
            'src/arena/arena_v58_ctf.php',
            'src/arena/arena_v58_incident.php',
            'arena_v58_events_api.php',
        ],
        // v61 · C – navigace žákovské aplikace (drobečky, spodní lišta).
        'nav_v61' => [
            'src/ui/nav_v61.php',
        ],
        // v67 · profil: Můj růst (cíle, příběh růstu, sdílení) a prázdné stavy/přepínač vzhledu.
        'growth_v67' => [
            'src/student/growth_v67_views.php',
            'app/actions/growth_v67.php',
            'app/actions/ui_v67.php',
            'src/ui/ui_v67.php',
        ],
        // v72 · cíl hodiny, exit ticket a volitelná domácí příprava schválené lekce (výukový obsah zůstává česky).
        'lesson_v72' => [
            'src/lessons/lesson_exit_v72_views.php',
            'app/actions/lesson_exit_v72.php',
        ],
        // v72.1 · dobrovolné AI prompty pro studenta v lekci (upozornění na soukromí; prompty zůstávají česky).
        'lesson_v72_1' => [
            'src/teacher/ai_prompts_v72_1.php',
        ],
        // v75 · úlohy s variantami, úrovně v hodině, připnuté práce v portfoliu a opakování před písemkou (zadání úloh a úrovní zůstávají česky).
        'v75' => [
            'src/assessment/taskbank_v75_views.php',
            'src/levels/levels_v75_views.php',
            'src/student/portfolio_v75_views.php',
            'src/student/review_v75_views.php',
            'app/views/ulohy_v75.php',
            'app/views/opakovani_v75.php',
            'app/actions/v75.php',
        ],
        // v79 · krokové postupy žáka (stepper): úloha s variantou a připnutí práce do portfolia (zadání úloh zůstává česky).
        'stepper_v79' => [
            'src/ui/stepper_v79.php',
            'src/ui/stepper_v79_student.php',
            'src/student/portfolio_v79.php',
            'app/actions/stepper_v79.php',
        ],
        // v80 · podpora učení žáka: Můj pokrok, nápovědy a vysvětlení chyb, opakování chyb, interaktivní cvičení, cíle a odznaky (nápovědy a zadání zůstávají česky).
        'learning_v80' => [
            'src/lessons/learning_v80_views.php',
            'src/student/progress_v80_views.php',
            'src/ui/widgets_v80_views.php',
            'src/lessons/learning_v80_post.php',
            'app/views/learning_v80.php',
            'app/views/pokrok_v80.php',
        ],
        // v80.1 · Od učitele na stránce Můj pokrok: přidělené procvičování a zpětná vazba učitele (text zprávy se nepřekládá).
        'support_v80_1' => [
            'src/student/support_v80_1_views.php',
        ],
        // Integrátor – sdílené soubory, dokončí se až po B1–B6 (F2).
        'shared' => [
            'bootstrap.php',
            'index.php',
            'src/student/student_v55.php',
            'sw.js',
        ],
        // Integrátor – jádro i18n samo (zatím bez tr() – viz core_ui katalogy, prázdné je v pořádku).
        'core_ui' => [
            'src/i18n/i18n_v58.php',
            'src/i18n/i18n_v59.php',
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
