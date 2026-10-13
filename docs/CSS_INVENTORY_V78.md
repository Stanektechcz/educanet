# Inventura CSS v78 (fáze A)

Generováno `php tools/v78_css_inventory.php --out=docs/CSS_INVENTORY_V78.md` (13. 10. 2026). Jen čtení: nic se nemaže ani nepřesouvá – vyřazení je až ve fázi F postupem kopie → SHA-256 → unlink, po jednom souboru.

## Souhrn
- **105 souborů**, 2 051,5 kB raw / 422,1 kB gzip, 23 218 selektorů, 3 333 deklarací s pevnou barvou, 2 754 × `!important`.
- **Odvozené kopie cockpitu `cx-*`**: 26 souborů, 708,6 kB raw (generuje `tools/v68_tokenize_css.php --derive` z originálů žáka; v duplicitách se nepočítají).
- **Selektory definované ve více souborech** (stejný kontext @media): 625; shodné bloky (selektor + deklarace) ve více souborech: 407.
- **Konflikty** (stejný selektor a vlastnost, jiná hodnota v jiném souboru, bez vlastních proměnných): 272 ve 38 dvojicích souborů.
- **Nenačítané soubory** (název se nevyskytuje v PHP ani JS aplikace): žádné.

## Rodiny vrstev
| Rodina | Souborů | Raw kB | Gzip kB |
|---|---:|---:|---:|
| Lab, Aréna a moduly v57–v58 | 14 | 112,3 | 31,2 |
| cockpit učitele | 14 | 103,2 | 25,3 |
| cx (odvozené kopie cockpitu) | 26 | 708,6 | 142,2 |
| design systém v78 | 3 | 27,8 | 7,3 |
| tisk a projektor | 5 | 11,9 | 4,7 |
| tmavý režim | 7 | 241,0 | 39,5 |
| tokeny | 2 | 21,6 | 7,4 |
| žák a sdílené vrstvy | 34 | 825,1 | 164,5 |

## Soubory
| Soubor | Rodina | Raw kB | Gzip kB | Řádky | Pravidla | Selektory | Proměnné | Pevné barvy | !important | @media | @container | Duplicity v souboru | Načítá |
|---|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|---|
| `accounts-v58.css` | Lab, Aréna a moduly v57–v58 | 6,7 | 1,9 | 96 | 66 | 79 | 12 | 0 | 2 | 3 | 0 | 0 | accounts_v58_views.php |
| `app.css` | žák a sdílené vrstvy | 398,8 | 69,2 | 2341 | 4296 | 4810 | 66 | 868 | 372 | 114 | 2 | 6 | app/lib.php, app/views/precache.php, app/views/_layout.php +4 |
| `arena-events-v58.css` | Lab, Aréna a moduly v57–v58 | 6,5 | 2,0 | 110 | 68 | 73 | 0 | 28 | 2 | 2 | 0 | 0 | arena_v58_events_views.php, teacher_v58.php |
| `arena-v57.css` | Lab, Aréna a moduly v57–v58 | 16,2 | 3,8 | 266 | 173 | 184 | 25 | 40 | 2 | 4 | 1 | 0 | app/views/_layout.php, arena_v57_views.php, arena_v58_replay.php +1 |
| `arena-v58.css` | Lab, Aréna a moduly v57–v58 | 3,6 | 1,5 | 58 | 31 | 32 | 0 | 15 | 3 | 2 | 0 | 0 | arena_v58_replay.php |
| `arena-v64.css` | Lab, Aréna a moduly v57–v58 | 1,7 | 0,7 | 23 | 21 | 33 | 0 | 10 | 0 | 2 | 0 | 0 | app/views/precache.php, arena_v64_views.php, teacher_v58.php +1 |
| `assessment-v66-print.css` | tisk a projektor | 0,6 | 0,4 | 11 | 7 | 16 | 0 | 3 | 1 | 1 | 0 | 0 | assessment_v66_teacher_grading.php, assessment_v66_teacher_views.php |
| `assessment-v66.css` | žák a sdílené vrstvy | 4,6 | 1,3 | 37 | 36 | 55 | 0 | 25 | 3 | 2 | 0 | 0 | grading_v66_views.php, teacher_v58.php |
| `brand-v54.css` | žák a sdílené vrstvy | 13,2 | 3,2 | 155 | 115 | 127 | 31 | 68 | 24 | 2 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +1 |
| `cockpit-dark-v68.css` | tmavý režim | 3,3 | 0,8 | 25 | 21 | 34 | 13 | 1 | 0 | 0 | 0 | 0 | teacher_shell_v68.php |
| `cockpit-v70.css` | cockpit učitele | 9,6 | 2,2 | 111 | 83 | 116 | 0 | 0 | 0 | 3 | 0 | 0 | teacher_shell_v68.php |
| `cockpit-v71.css` | cockpit učitele | 2,5 | 1,0 | 38 | 19 | 22 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_v58.php |
| `cockpit-v72.css` | cockpit učitele | 2,2 | 0,9 | 22 | 19 | 23 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `cockpit-v73.css` | cockpit učitele | 2,1 | 0,8 | 21 | 19 | 27 | 0 | 0 | 0 | 0 | 0 | 0 | teacher_v58.php |
| `cockpit-v74.css` | cockpit učitele | 7,2 | 1,8 | 73 | 62 | 119 | 0 | 10 | 9 | 3 | 0 | 0 | bulk_approval_v74_views.php, insights_v74_views.php, teacher_v58.php +1 |
| `cockpit-v75.css` | cockpit učitele | 5,8 | 1,5 | 54 | 50 | 68 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `cockpit-v76.css` | cockpit učitele | 2,1 | 0,8 | 20 | 19 | 24 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_v58.php |
| `cockpit-v77.css` | cockpit učitele | 0,9 | 0,5 | 11 | 9 | 9 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `cognitive-v43.css` | žák a sdílené vrstvy | 19,5 | 4,0 | 15 | 181 | 239 | 0 | 84 | 7 | 3 | 1 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +2 |
| `competency-v62.css` | žák a sdílené vrstvy | 3,4 | 1,0 | 32 | 30 | 34 | 0 | 22 | 2 | 2 | 0 | 0 | competency_v62_views.php, teacher_v58.php |
| `components-v61.css` | žák a sdílené vrstvy | 11,6 | 2,7 | 112 | 89 | 98 | 0 | 0 | 5 | 4 | 0 | 0 | app/views/precache.php, app/views/_layout.php, nav_v61.php |
| `components-v78.css` | design systém v78 | 20,5 | 4,4 | 206 | 158 | 181 | 13 | 0 | 3 | 7 | 4 | 0 | design_v78.php, teacher_v58.php |
| `cx-app.css` | cx (odvozené kopie cockpitu) | 400,9 | 67,9 | 2342 | 4296 | 4810 | 66 | 1 | 372 | 114 | 2 | 6 | teacher_shell_v68.php (kopie app.css) |
| `cx-arena-events-v58.css` | cx (odvozené kopie cockpitu) | 6,8 | 2,1 | 111 | 68 | 73 | 0 | 0 | 2 | 2 | 0 | 0 | teacher_shell_v68.php (kopie arena-events-v58.css) |
| `cx-arena-v57.css` | cx (odvozené kopie cockpitu) | 16,6 | 3,8 | 267 | 173 | 184 | 25 | 0 | 2 | 4 | 1 | 0 | teacher_shell_v68.php (kopie arena-v57.css) |
| `cx-arena-v64.css` | cx (odvozené kopie cockpitu) | 1,9 | 0,8 | 24 | 21 | 33 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_shell_v68.php (kopie arena-v64.css) |
| `cx-assessment-v66.css` | cx (odvozené kopie cockpitu) | 4,9 | 1,4 | 38 | 36 | 55 | 0 | 0 | 3 | 2 | 0 | 0 | teacher_shell_v68.php (kopie assessment-v66.css) |
| `cx-brand-v54.css` | cx (odvozené kopie cockpitu) | 13,4 | 3,2 | 156 | 115 | 127 | 31 | 0 | 24 | 2 | 0 | 0 | teacher_shell_v68.php (kopie brand-v54.css) |
| `cx-cognitive-v43.css` | cx (odvozené kopie cockpitu) | 20,0 | 4,1 | 16 | 181 | 239 | 0 | 0 | 7 | 3 | 1 | 0 | teacher_shell_v68.php (kopie cognitive-v43.css) |
| `cx-competency-v62.css` | cx (odvozené kopie cockpitu) | 3,6 | 1,1 | 33 | 30 | 34 | 0 | 0 | 2 | 2 | 0 | 0 | teacher_shell_v68.php (kopie competency-v62.css) |
| `cx-feedback-v60.css` | cx (odvozené kopie cockpitu) | 3,8 | 1,3 | 39 | 37 | 50 | 0 | 0 | 2 | 2 | 0 | 0 | teacher_shell_v68.php (kopie feedback-v60.css) |
| `cx-hands-on-v50.css` | cx (odvozené kopie cockpitu) | 15,2 | 3,7 | 31 | 152 | 198 | 10 | 0 | 4 | 2 | 2 | 0 | teacher_shell_v68.php (kopie hands-on-v50.css) |
| `cx-learning-studio-v44.css` | cx (odvozené kopie cockpitu) | 14,6 | 3,3 | 18 | 145 | 169 | 1 | 0 | 11 | 3 | 1 | 0 | teacher_shell_v68.php (kopie learning-studio-v44.css) |
| `cx-linux-v57.css` | cx (odvozené kopie cockpitu) | 25,8 | 5,9 | 284 | 237 | 245 | 9 | 0 | 12 | 4 | 0 | 0 | teacher_shell_v68.php (kopie linux-v57.css) |
| `cx-marketplace-v60.css` | cx (odvozené kopie cockpitu) | 3,1 | 1,2 | 28 | 24 | 29 | 0 | 0 | 3 | 2 | 0 | 0 | teacher_shell_v68.php (kopie marketplace-v60.css) |
| `cx-mastery.css` | cx (odvozené kopie cockpitu) | 18,5 | 4,2 | 58 | 197 | 237 | 0 | 0 | 9 | 13 | 0 | 0 | teacher_shell_v68.php (kopie mastery.css) |
| `cx-motivation-v61.css` | cx (odvozené kopie cockpitu) | 2,9 | 1,1 | 28 | 25 | 25 | 0 | 0 | 2 | 1 | 0 | 0 | teacher_shell_v68.php (kopie motivation-v61.css) |
| `cx-paths-v63.css` | cx (odvozené kopie cockpitu) | 7,5 | 1,9 | 62 | 61 | 78 | 0 | 0 | 3 | 2 | 0 | 0 | teacher_shell_v68.php (kopie paths-v63.css) |
| `cx-projects-v60.css` | cx (odvozené kopie cockpitu) | 3,1 | 1,1 | 29 | 25 | 33 | 0 | 0 | 3 | 2 | 0 | 0 | teacher_shell_v68.php (kopie projects-v60.css) |
| `cx-projects-v65.css` | cx (odvozené kopie cockpitu) | 4,5 | 1,5 | 50 | 46 | 64 | 0 | 0 | 5 | 3 | 0 | 0 | teacher_shell_v68.php (kopie projects-v65.css) |
| `cx-robots-v58.css` | cx (odvozené kopie cockpitu) | 13,7 | 3,5 | 200 | 144 | 166 | 26 | 0 | 2 | 6 | 0 | 0 | teacher_shell_v68.php (kopie robots-v58.css) |
| `cx-session-v53.css` | cx (odvozené kopie cockpitu) | 7,7 | 2,3 | 97 | 67 | 69 | 23 | 0 | 2 | 1 | 0 | 0 | teacher_shell_v68.php (kopie session-v53.css) |
| `cx-teamgames-v58.css` | cx (odvozené kopie cockpitu) | 13,5 | 3,6 | 188 | 127 | 141 | 22 | 0 | 3 | 3 | 0 | 0 | teacher_shell_v68.php (kopie teamgames-v58.css) |
| `cx-tutorial-v52.css` | cx (odvozené kopie cockpitu) | 35,1 | 6,7 | 419 | 367 | 392 | 21 | 0 | 18 | 3 | 0 | 0 | teacher_shell_v68.php (kopie tutorial-v52.css) |
| `cx-ui-v51.css` | cx (odvozené kopie cockpitu) | 33,9 | 7,4 | 473 | 298 | 373 | 78 | 0 | 139 | 6 | 0 | 0 | teacher_shell_v68.php (kopie ui-v51.css) |
| `cx-visual-labs-3a-v48-1.css` | cx (odvozené kopie cockpitu) | 8,6 | 2,1 | 11 | 105 | 121 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_shell_v68.php (kopie visual-labs-3a-v48-1.css) |
| `cx-visual-practical-v48.css` | cx (odvozené kopie cockpitu) | 6,1 | 1,7 | 5 | 76 | 90 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_shell_v68.php (kopie visual-practical-v48.css) |
| `cx-visual-simulation-v45.css` | cx (odvozené kopie cockpitu) | 22,8 | 5,3 | 27 | 241 | 289 | 6 | 1 | 9 | 7 | 0 | 0 | teacher_shell_v68.php (kopie visual-simulation-v45.css) |
| `dark/student-dark-dashboard-v69.css` | tmavý režim | 73,8 | 10,8 | 618 | 593 | 681 | 187 | 274 | 176 | 12 | 0 | 2 | ui_v67.php (UI69_STUDENT_DARK_CSS, pohled dashboard) |
| `dark/student-dark-home-v69.css` | tmavý režim | 45,9 | 7,5 | 391 | 363 | 438 | 163 | 181 | 63 | 11 | 0 | 0 | ui_v67.php (UI69_STUDENT_DARK_CSS, pohled home) |
| `dark/student-dark-profile-v69.css` | tmavý režim | 97,3 | 13,7 | 859 | 821 | 949 | 199 | 351 | 167 | 11 | 0 | 1 | ui_v67.php (UI69_STUDENT_DARK_CSS, pohled profile) |
| `design-v78.css` | design systém v78 | 1,6 | 0,6 | 15 | 13 | 13 | 0 | 0 | 0 | 0 | 0 | 0 | design_v78.php, teacher_v58.php |
| `feedback-v60.css` | žák a sdílené vrstvy | 3,6 | 1,2 | 38 | 37 | 50 | 0 | 19 | 2 | 2 | 0 | 0 | feedback_v60_views.php, teacher_v58.php |
| `growth-v67.css` | žák a sdílené vrstvy | 3,4 | 1,0 | 37 | 35 | 42 | 0 | 0 | 0 | 1 | 0 | 0 | app/views/precache.php, growth_v67_views.php |
| `hands-on-v50.css` | žák a sdílené vrstvy | 14,8 | 3,7 | 30 | 152 | 198 | 10 | 62 | 4 | 2 | 2 | 0 | app/views/_layout.php, sw.js, teacher_shell_v68.php |
| `i18n-v59.css` | žák a sdílené vrstvy | 2,4 | 1,0 | 102 | 13 | 14 | 0 | 12 | 0 | 2 | 0 | 0 | app/views/precache.php, app/views/_layout.php |
| `identity-v58.css` | Lab, Aréna a moduly v57–v58 | 2,4 | 0,9 | 117 | 24 | 27 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `lab-a11y-v58.css` | Lab, Aréna a moduly v57–v58 | 7,1 | 2,2 | 89 | 52 | 66 | 9 | 30 | 16 | 2 | 0 | 0 | linux_v57_views.php |
| `lab-editor-v58.css` | Lab, Aréna a moduly v57–v58 | 4,4 | 1,4 | 86 | 41 | 58 | 13 | 1 | 2 | 3 | 0 | 0 | lab_v58_editor_views.php |
| `lab-offline-v58.css` | Lab, Aréna a moduly v57–v58 | 5,9 | 1,8 | 235 | 49 | 50 | 12 | 14 | 3 | 2 | 0 | 0 | sw.js |
| `lab-teacher-v58.css` | Lab, Aréna a moduly v57–v58 | 5,0 | 1,4 | 59 | 42 | 48 | 0 | 0 | 0 | 2 | 0 | 0 | teacher_v58.php |
| `learn-v75.css` | žák a sdílené vrstvy | 6,6 | 1,6 | 58 | 56 | 80 | 0 | 0 | 0 | 2 | 0 | 0 | levels_v75_views.php, portfolio_v75_views.php, taskbank_v75_views.php |
| `learning-studio-v44.css` | žák a sdílené vrstvy | 14,2 | 3,2 | 17 | 145 | 169 | 1 | 67 | 11 | 3 | 1 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +2 |
| `learning-v56.css` | žák a sdílené vrstvy | 22,5 | 4,7 | 265 | 211 | 231 | 0 | 119 | 12 | 8 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js |
| `lesson-print-v73.css` | tisk a projektor | 4,8 | 1,6 | 55 | 48 | 78 | 0 | 10 | 15 | 3 | 0 | 0 | teacher_v58.php |
| `lesson-projector-v73.css` | tisk a projektor | 3,9 | 1,3 | 43 | 40 | 69 | 0 | 0 | 1 | 1 | 0 | 0 | teacher_v58.php |
| `lesson-v72.css` | žák a sdílené vrstvy | 3,5 | 1,0 | 28 | 25 | 27 | 0 | 0 | 0 | 1 | 0 | 0 | ai_prompts_v72_1.php, lesson_exit_v72_views.php |
| `linux-v57.css` | Lab, Aréna a moduly v57–v58 | 25,0 | 6,0 | 283 | 237 | 245 | 9 | 198 | 12 | 4 | 0 | 0 | app/views/_layout.php, teacher_shell_v68.php |
| `login-v63.css` | žák a sdílené vrstvy | 6,6 | 2,1 | 80 | 58 | 68 | 0 | 10 | 1 | 5 | 0 | 0 | app/views/home.php, app/views/_layout.php |
| `marketplace-v60.css` | žák a sdílené vrstvy | 2,9 | 1,1 | 27 | 24 | 29 | 0 | 20 | 3 | 2 | 0 | 0 | marketplace_v60_views.php, teacher_v58.php |
| `mastery.css` | žák a sdílené vrstvy | 18,2 | 4,2 | 57 | 197 | 237 | 0 | 85 | 9 | 13 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +2 |
| `motivation-v61.css` | žák a sdílené vrstvy | 2,8 | 1,0 | 27 | 25 | 25 | 0 | 0 | 2 | 1 | 0 | 0 | marketplace_v60_views.php, motivation_v61_views.php, teacher_v58.php |
| `nav-v61.css` | žák a sdílené vrstvy | 10,6 | 2,5 | 93 | 72 | 87 | 10 | 0 | 2 | 5 | 0 | 0 | app/views/precache.php, app/views/_layout.php, assets/nav-v61.js +1 |
| `ops-v58.css` | Lab, Aréna a moduly v57–v58 | 1,4 | 0,6 | 88 | 14 | 20 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `paths-card-v63.css` | žák a sdílené vrstvy | 0,5 | 0,3 | 9 | 7 | 9 | 0 | 2 | 0 | 0 | 0 | 0 | app/views/precache.php, paths_v63_views.php |
| `paths-v63.css` | žák a sdílené vrstvy | 7,2 | 1,8 | 61 | 61 | 78 | 0 | 41 | 3 | 2 | 0 | 0 | paths_v63_views.php, teacher_v58.php |
| `print-v75.css` | tisk a projektor | 1,9 | 0,9 | 27 | 21 | 26 | 0 | 5 | 0 | 2 | 0 | 0 | plan_v76_views.php, portfolio_v75_teacher_views.php, review_v75_teacher_views.php +1 |
| `print-v76.css` | tisk a projektor | 0,7 | 0,5 | 10 | 7 | 9 | 0 | 1 | 0 | 2 | 0 | 0 | plan_v76_views.php, svp_export_v76_views.php |
| `profile-v60.css` | žák a sdílené vrstvy | 18,1 | 4,4 | 214 | 208 | 228 | 64 | 52 | 11 | 4 | 0 | 0 | arena_v60_challenge_views.php, badges_v60.php, marketplace_v61_views.php +2 |
| `projects-v60.css` | žák a sdílené vrstvy | 2,9 | 1,0 | 28 | 25 | 33 | 0 | 20 | 3 | 2 | 0 | 0 | projects_v60_views.php, teacher_v58.php |
| `projects-v65.css` | žák a sdílené vrstvy | 4,2 | 1,4 | 49 | 46 | 64 | 0 | 36 | 5 | 3 | 0 | 0 | app/views/precache.php, projects_v65_views.php, teacher_v58.php |
| `robots-v58.css` | Lab, Aréna a moduly v57–v58 | 13,3 | 3,5 | 199 | 144 | 166 | 26 | 52 | 2 | 6 | 0 | 0 | robots_v58_views.php |
| `session-v53.css` | žák a sdílené vrstvy | 7,3 | 2,3 | 96 | 67 | 69 | 23 | 31 | 2 | 1 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +1 |
| `student-coach-v47.css` | žák a sdílené vrstvy | 17,1 | 3,7 | 20 | 208 | 239 | 0 | 39 | 1 | 5 | 0 | 0 | app/views/_layout.php, sw.js |
| `student-ui-v50-7-7.css` | žák a sdílené vrstvy | 86,4 | 13,7 | 631 | 864 | 1090 | 28 | 99 | 931 | 23 | 0 | 2 | app/views/precache.php, app/views/_layout.php, sw.js |
| `student-v55.css` | žák a sdílené vrstvy | 12,1 | 3,0 | 146 | 114 | 116 | 0 | 67 | 12 | 5 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js |
| `teacher-accounts-v59.css` | cockpit učitele | 5,1 | 1,6 | 76 | 52 | 62 | 0 | 0 | 2 | 3 | 0 | 0 | teacher_accounts_v59_views.php, teacher_scope_v59.php, teacher_v58.php |
| `teacher-contrast-v69.css` | cockpit učitele | 1,6 | 0,8 | 46 | 1 | 34 | 0 | 0 | 0 | 0 | 0 | 0 | teacher_shell_v68.php |
| `teacher-dark-v68.css` | tmavý režim | 1,1 | 0,7 | 15 | 2 | 2 | 22 | 0 | 0 | 0 | 0 | 0 | teacher_shell_v68.php |
| `teacher-ops-v46.css` | cockpit učitele | 18,8 | 3,6 | 37 | 223 | 293 | 0 | 0 | 2 | 5 | 0 | 1 | teacher_shell_v68.php |
| `teacher-overview-v61.css` | cockpit učitele | 3,0 | 1,1 | 34 | 34 | 47 | 0 | 0 | 0 | 1 | 0 | 0 | teacher_v58.php |
| `teacher-shell-v68.css` | cockpit učitele | 13,1 | 3,1 | 124 | 106 | 119 | 31 | 0 | 13 | 5 | 0 | 0 | teacher_shell_v68.php |
| `teacher.css` | cockpit učitele | 29,4 | 5,6 | 56 | 334 | 382 | 0 | 0 | 23 | 20 | 0 | 2 | teacher_scope_v59.php, teacher_shell_v68.php |
| `teamgames-v58.css` | Lab, Aréna a moduly v57–v58 | 13,1 | 3,6 | 187 | 127 | 141 | 22 | 27 | 3 | 3 | 0 | 0 | teamgames_v58_projector_views.php, teamgames_v58_teacher_views.php, teamgames_v58_views.php |
| `tokens-dark-v67.css` | tmavý režim | 1,1 | 0,6 | 34 | 2 | 2 | 27 | 0 | 0 | 0 | 0 | 0 | app/views/precache.php, teacher_shell_v68.php, ui_v67.php |
| `tokens-palette-dark-v68.css` | tmavý režim | 18,4 | 5,5 | 886 | 1 | 1 | 882 | 0 | 0 | 0 | 0 | 0 | teacher_shell_v68.php |
| `tokens-palette-v68.css` | tokeny | 18,6 | 6,1 | 886 | 1 | 1 | 882 | 0 | 0 | 0 | 0 | 0 | accounts_v58_views.php, teacher_scope_v59.php, teacher_shell_v68.php |
| `tokens-v61.css` | tokeny | 3,0 | 1,3 | 88 | 3 | 4 | 75 | 0 | 0 | 0 | 0 | 0 | app/views/precache.php, app/views/_layout.php, nav_v61.php +1 |
| `tokens-v78.css` | design systém v78 | 5,7 | 2,3 | 196 | 6 | 6 | 146 | 0 | 0 | 5 | 1 | 0 | design_v78.php, design_v78_views.php, teacher_v58.php |
| `tutorial-v52.css` | žák a sdílené vrstvy | 34,8 | 6,7 | 418 | 367 | 392 | 21 | 49 | 18 | 3 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +1 |
| `ui-v51.css` | žák a sdílené vrstvy | 33,6 | 7,4 | 472 | 298 | 373 | 78 | 29 | 139 | 6 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +1 |
| `ui-v67.css` | žák a sdílené vrstvy | 0,9 | 0,5 | 8 | 6 | 6 | 0 | 0 | 0 | 1 | 0 | 0 | ui_v67.php |
| `visual-labs-3a-v48-1.css` | žák a sdílené vrstvy | 8,5 | 2,0 | 10 | 105 | 121 | 0 | 0 | 0 | 2 | 0 | 0 | app/views/_layout.php, sw.js, teacher_shell_v68.php |
| `visual-practical-v48.css` | žák a sdílené vrstvy | 6,0 | 1,5 | 4 | 76 | 90 | 0 | 1 | 0 | 2 | 0 | 0 | app/views/_layout.php, sw.js, teacher_shell_v68.php |
| `visual-simulation-v45.css` | žák a sdílené vrstvy | 22,5 | 5,3 | 26 | 241 | 289 | 6 | 153 | 9 | 7 | 0 | 0 | app/views/precache.php, app/views/_layout.php, sw.js +2 |

## Duplicity: nejčastěji sdílené selektory (bez `cx-*`)
| Selektor (kontext) | Souborů |
|---|---:|
| `:root` | 11 |
| `body.accent-graphics` | 6 |
| `@media print ⟂ body` | 6 |
| `body.accent-advanced` | 5 |
| `html` | 4 |
| `body` | 4 |
| `body.accent-network` | 4 |
| `.topbar` | 4 |
| `.brand-mark` | 4 |
| `a` | 3 |
| `.notice` | 3 |
| `.btn.secondary` | 3 |
| `.footer` | 3 |
| `.calendar-warning-badge` | 3 |
| `.calendar-exception-badge.shifted` | 3 |
| `@media print ⟂ .topbar` | 3 |
| `@media print ⟂ a` | 3 |
| `:root:not(.x)` | 3 |
| `@media print ⟂ .t68-top` | 3 |
| `@media print ⟂ .t68-side` | 3 |

## Duplicity: dvojice souborů s nejvíce společnými selektory
| Dvojice | Společné selektory | Z toho konfliktních vlastností | Na stejné stránce |
|---|---:|---:|---|
| dark/student-dark-dashboard-v69.css ↔ dark/student-dark-profile-v69.css | 418 | 0 | ne |
| dark/student-dark-dashboard-v69.css ↔ dark/student-dark-home-v69.css | 336 | 1 | ne |
| dark/student-dark-home-v69.css ↔ dark/student-dark-profile-v69.css | 336 | 1 | ne |
| app.css ↔ student-ui-v50-7-7.css | 58 | 52 | ano |
| app.css ↔ ui-v51.css | 47 | 81 | ano |
| teacher-contrast-v69.css ↔ teacher.css | 19 | 19 | ano |
| brand-v54.css ↔ session-v53.css | 14 | 15 | ano |
| student-ui-v50-7-7.css ↔ ui-v51.css | 12 | 18 | ano |
| brand-v54.css ↔ ui-v51.css | 10 | 8 | ano |
| cockpit-v74.css ↔ lesson-print-v73.css | 10 | 0 | ano |
| session-v53.css ↔ tutorial-v52.css | 8 | 1 | ano |
| app.css ↔ teacher-contrast-v69.css | 7 | 7 | ano |
| lab-offline-v58.css ↔ ui-v51.css | 6 | 4 | ano |
| session-v53.css ↔ ui-v51.css | 6 | 1 | ano |
| app.css ↔ brand-v54.css | 5 | 1 | ano |
| app.css ↔ lab-offline-v58.css | 5 | 5 | ano |
| brand-v54.css ↔ tutorial-v52.css | 5 | 3 | ano |
| cockpit-v74.css ↔ teacher-shell-v68.css | 5 | 0 | ne |
| lesson-print-v73.css ↔ teacher-shell-v68.css | 5 | 0 | ne |
| cockpit-v75.css ↔ learn-v75.css | 5 | 9 | ne |

## Konflikty mezi soubory načítanými na stejné stránce (bez tmavých vrstev, které přepisují záměrně)
Pořadí kaskády rozhoduje, která hodnota platí – při sloučení ve fázi F se musí zachovat výsledná hodnota (ověřit v prohlížeči). Nejvýše 3 ukázky na dvojici.

| Dvojice | Konfliktních vlastností |
|---|---:|
| app.css ↔ ui-v51.css | 81 |
| app.css ↔ student-ui-v50-7-7.css | 52 |
| teacher-contrast-v69.css ↔ teacher.css | 19 |
| student-ui-v50-7-7.css ↔ ui-v51.css | 18 |
| brand-v54.css ↔ session-v53.css | 15 |
| brand-v54.css ↔ ui-v51.css | 8 |
| app.css ↔ teacher-contrast-v69.css | 7 |
| app.css ↔ lab-offline-v58.css | 5 |
| teacher.css ↔ ui-v51.css | 5 |
| lab-offline-v58.css ↔ ui-v51.css | 4 |
| app.css ↔ linux-v57.css | 3 |
| app.css ↔ mastery.css | 3 |
| app.css ↔ teacher.css | 3 |
| app.css ↔ student-coach-v47.css | 3 |
| brand-v54.css ↔ tutorial-v52.css | 3 |
| components-v61.css ↔ ui-v51.css | 3 |
| learning-v56.css ↔ student-v55.css | 3 |
| learning-v56.css ↔ nav-v61.css | 2 |
| app.css ↔ student-v55.css | 1 |
| app.css ↔ brand-v54.css | 1 |
| cockpit-v74.css ↔ teacher-accounts-v59.css | 1 |
| projects-v65.css ↔ teacher-accounts-v59.css | 1 |
| session-v53.css ↔ tutorial-v52.css | 1 |
| session-v53.css ↔ ui-v51.css | 1 |
| teacher-ops-v46.css ↔ ui-v51.css | 1 |

| Selektor (kontext) | Vlastnost | Soubor A | Hodnota A | Soubor B | Hodnota B |
|---|---|---|---|---|---|
| `body` | background | `app.css` | var(--bg) | `lab-offline-v58.css` | var(--lab58off-bg) |
| `body` | color | `app.css` | var(--text) | `lab-offline-v58.css` | var(--lab58off-ink) |
| `body` | font-family | `app.css` | Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSys… | `lab-offline-v58.css` | system-ui, -apple-system, "Segoe UI", Roboto, sans-serif |
| `body` | background | `app.css` | var(--bg) | `ui-v51.css` | var(--u-bg) |
| `body` | color | `app.css` | var(--text) | `ui-v51.css` | var(--u-text) |
| `body` | font-family | `app.css` | Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSys… | `ui-v51.css` | var(--u-font) |
| `body` | font-family | `lab-offline-v58.css` | system-ui, -apple-system, "Segoe UI", Roboto, sans-serif | `ui-v51.css` | var(--u-font) |
| `body` | background | `lab-offline-v58.css` | var(--lab58off-bg) | `ui-v51.css` | var(--u-bg) |
| `body` | color | `lab-offline-v58.css` | var(--lab58off-ink) | `ui-v51.css` | var(--u-text) |
| `.topbar` | gap | `app.css` | 16px | `student-v55.css` | 14px |
| `.brand-mark` | background | `app.css` | var(--text) | `brand-v54.css` | var(--edu-teal) |
| `.brand-mark` | width | `app.css` | 34px | `student-ui-v50-7-7.css` | 31px |
| `.brand-mark` | height | `app.css` | 34px | `student-ui-v50-7-7.css` | 31px |
| `.brand-mark` | border-radius | `app.css` | 11px | `student-ui-v50-7-7.css` | 10px |
| `.brand-mark` | background | `brand-v54.css` | var(--edu-teal) | `ui-v51.css` | var(--u-text) |
| `.u51-check` | background | `brand-v54.css` | var(--edu-teal-soft) | `ui-v51.css` | var(--u-ok-soft) |
| `.u51-check` | color | `brand-v54.css` | var(--edu-teal-dark) | `ui-v51.css` | var(--u-ok) |
| `.notice` | border-radius | `student-ui-v50-7-7.css` | 11px | `ui-v51.css` | var(--u-radius-sm) |
| `.btn.secondary` | background | `student-ui-v50-7-7.css` | transparent | `ui-v51.css` | var(--u-surface) |
| `.btn.secondary` | border | `student-ui-v50-7-7.css` | 1px solid var(--v506-line) | `ui-v51.css` | 1px solid var(--u-line-strong) |
| `.btn.small` | min-height | `app.css` | 36px | `linux-v57.css` | var(--ui-tap, 44px) |
| `.btn.small` | padding | `app.css` | 0 12px | `linux-v57.css` | 6px 14px |
| `.btn.small` | font-size | `app.css` | .6rem | `linux-v57.css` | .84rem |
| `.overview-global-kpis span` | color | `app.css` | #7a8793 | `teacher-contrast-v69.css` | var(--ui-muted) |
| `.overview-global-kpis small` | color | `app.css` | #7b8792 | `teacher-contrast-v69.css` | var(--ui-muted) |
| `.overview-class-metrics span` | color | `app.css` | #7c8793 | `teacher-contrast-v69.css` | var(--ui-muted) |
| `.calendar-warning-badge` | margin-top | `app.css` | .3rem | `mastery.css` | .35rem |
| `.calendar-warning-badge` | font-weight | `app.css` | 700 | `mastery.css` | 750 |
| `.calendar-exception-badge.shifted` | opacity | `app.css` | .8 | `mastery.css` | .82 |
| `.calendar-warning-badge` | margin-top | `app.css` | .3rem | `teacher.css` | .35rem |
| `.calendar-warning-badge` | font-weight | `app.css` | 700 | `teacher.css` | 750 |
| `.calendar-exception-badge.shifted` | opacity | `app.css` | .8 | `teacher.css` | .82 |

## Návrh sloučení pro fázi F (nic se teď nevyřazuje)
1. **Tokeny → `tokens-v78.css`.** `tokens-v61.css` (`--ui-*`), `tokens-palette-v68.css` / `tokens-palette-dark-v68.css` (paleta `--k*`) a `tokens-dark-v67.css` dostanou aliasy na sémantické tokeny v78 (`--ui-ink: var(--text)` …); po převodu stránek se vyřadí. Tmavé vrstvy `teacher-dark-v68.css` a `cockpit-dark-v68.css` zaniknou, protože tokeny v78 mají tmavý režim v sobě.
2. **Cockpit učitele → jedna vrstva nad `components-v78.css`.** `teacher-shell-v68.css`, `teacher-contrast-v69.css` a `cockpit-v70.css` … `cockpit-v77.css` (rozvržení záložek v70–v77) sloučit do jednoho souboru rámce a komponent; kontrast v69 už řeší tokeny.
3. **Odvozené kopie `cx-*` (největší úspora).** Cockpit dnes načítá tokenizované kopie žákovských vrstev (`cx-app.css` ~400 kB, `cx-ui-v51.css`, `cx-brand-v54.css` …) jen kvůli starým třídám. Až záložky cockpitu přejdou na komponenty v78, kopie i generátor `tools/v68_tokenize_css.php --derive` se vyřadí.
4. **Žák.** `app.css` (~400 kB), `ui-v51.css`, `student-ui-v50-7-7.css`, `student-v55.css` a generované tmavé vrstvy `dark/student-dark-*-v69.css` nahradit vrstvou nad tokeny v78 po pohledech (Domů, Dnes, Úloha, Profil); tmavé vrstvy v69 pak nejsou potřeba. Vyřazovat po jednom pohledu s kontrolou v prohlížeči (390/1280 px, oba režimy).
5. **Tisk a projektor.** `print-v75.css`, `print-v76.css`, `lesson-print-v73.css`, `assessment-v66-print.css` a `lesson-projector-v73.css` sjednotit na tokeny v78 (tisk vždy světlý) – funkce beze změny.
6. **Moduly (Lab, Aréna, roboti, hry, účty).** Ponechat jako samostatné soubory, jen nahradit pevné barvy tokeny; sloučit až při další práci na modulu.
7. **Nenačítané soubory:** žádné (kopie `cx-*` načítá `teacher68_css_href()`, tmavé vrstvy v69 `ui67_dark_link_html()` dynamicky).

Postup vyřazení (fáze F): vlastní PHP skript pro jeden soubor – kopie mimo projekt → ověření SHA-256 kopie → unlink originálu; nikdy shellová smyčka. Záznam (soubor, bajty, SHA-256) do manifestu vydání.
