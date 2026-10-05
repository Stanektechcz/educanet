# EDUCANET v67 · kandidáti na vyřazení (starší pohledy a vrstvy v42–v50)

Vygenerováno `php tools/v67_dead_code_report.php --md` (jen čte, nic nemění ani nemaže). **Nic nebylo vyřazeno.** Rozhoduje člověk: soubor, který se má vyřadit, se vyjmenuje v `RETIRE67_APPROVED` v `tools/v67_retire.php` a vyřazuje se po jednom (`--file=<cesta> --apply`: kopie do `retired/v67/` → SHA-256 → smazání originálu). Přesměrování starých URL se řeší až s vyřazením.

Počet kandidátů: 2

## Jak číst tabulku

- **routes** – zmínky souboru v `app/routes.php`; **kód** – zmínky z živého (nekandidátního) PHP/JS kódu; **odkazy ?view=** – odkazy na pohled (`?view=název`, `module_url('název')`) z živého kódu; **audity** – zmínky v `tools/` a `tests/`; **precache** – service worker / seznam přednačtení.
- **kandidát** = pohled bez jediného odkazu `?view=` z kódu (a mimo precache), nebo soubor bez odkazu z routes i z živého kódu. **živý** = jinak.
- Heuristika čte jen text souborů; dynamicky sestavené adresy (`'?view=' . $x`) nevidí – každého kandidáta proto před vyřazením otevři v prohlížeči a ověř, že na něj nevede žádná cesta.

## Tabulka

| Soubor | Řádků | routes | kód | rodina | audity | precache | Verdikt |
|---|---:|---:|---:|---:|---:|---:|---|
| `adaptive_lesson_kits_v42.php` | 263 | 0 | 1 | 0 | 0 | 0 | živý |
| `adaptive_lesson_views_v42.php` | 101 | 1 | 3 | 0 | 0 | 0 | živý |
| `app/views/activate.php` | 15 | 1 | 1 | 2 | 0 | 0 | živý |
| `app/views/arena_events_v58.php` | 17 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/auth_links.php` | 49 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/case_study.php` | 76 | 1 | 3 | 2 | 0 | 0 | živý |
| `app/views/change_password.php` | 32 | 1 | 3 | 0 | 1 | 0 | živý |
| `app/views/dashboard.php` | 403 | 1 | 3 | 23 | 18 | 0 | živý |
| `app/views/extra_challenge.php` | 98 | 1 | 3 | 1 | 0 | 0 | živý |
| `app/views/feedback.php` | 22 | 1 | 2 | 5 | 1 | 0 | živý |
| `app/views/games_v58.php` | 16 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/graphics_guide.php` | 119 | 1 | 3 | 2 | 0 | 0 | živý |
| `app/views/graphics_studio.php` | 261 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/graphics_studio_gate.php` | 21 | 1 | 1 | 2 | 1 | 0 | živý |
| `app/views/hadanka.php` | 16 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/hands_on_growth.php` | 32 | 1 | 3 | 3 | 0 | 0 | živý |
| `app/views/hodina.php` | 20 | 1 | 1 | 7 | 0 | 0 | živý |
| `app/views/hodnoceni.php` | 17 | 1 | 1 | 3 | 1 | 0 | živý |
| `app/views/home.php` | 107 | 1 | 3 | 0 | 1 | 0 | živý |
| `app/views/hry.php` | 15 | 1 | 1 | 7 | 0 | 0 | živý |
| `app/views/intake.php` | 17 | 1 | 3 | 8 | 0 | 0 | živý |
| `app/views/join.php` | 11 | 1 | 3 | 1 | 2 | 0 | živý |
| `app/views/kb_lesson.php` | 76 | 1 | 3 | 3 | 0 | 0 | živý |
| `app/views/kb_quiz.php` | 82 | 1 | 3 | 1 | 0 | 0 | živý |
| `app/views/knowledgebase.php` | 243 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/lesson_hub.php` | 33 | 1 | 1 | 1 | 0 | 0 | živý |
| `app/views/lesson_path.php` | 31 | 1 | 2 | 5 | 1 | 0 | živý |
| `app/views/link_account.php` | 34 | 1 | 3 | 5 | 1 | 0 | živý |
| `app/views/linux_lab.php` | 28 | 1 | 2 | 17 | 0 | 0 | živý |
| `app/views/marketplace.php` | 18 | 1 | 2 | 6 | 0 | 0 | živý |
| `app/views/materials_redirect.php` | 15 | 1 | 0 | 7 | 1 | 0 | živý |
| `app/views/one_task.php` | 25 | 1 | 3 | 0 | 0 | 0 | kandidát |
| `app/views/paths.php` | 35 | 1 | 2 | 8 | 1 | 0 | živý |
| `app/views/portfolio.php` | 22 | 1 | 1 | 5 | 1 | 0 | živý |
| `app/views/practice.php` | 242 | 1 | 4 | 2 | 0 | 0 | živý |
| `app/views/precache.php` | 29 | 1 | 0 | 0 | 3 | 0 | živý |
| `app/views/privacy.php` | 19 | 1 | 3 | 1 | 0 | 0 | živý |
| `app/views/project_results.php` | 59 | 1 | 1 | 2 | 1 | 0 | živý |
| `app/views/projects.php` | 19 | 1 | 2 | 2 | 0 | 0 | živý |
| `app/views/projects_v65.php` | 23 | 1 | 5 | 6 | 11 | 0 | živý |
| `app/views/review_recovery.php` | 55 | 1 | 3 | 3 | 0 | 0 | živý |
| `app/views/structured_lessons.php` | 28 | 1 | 1 | 1 | 0 | 0 | živý |
| `app/views/student_hubs.php` | 23 | 1 | 1 | 16 | 0 | 0 | živý |
| `app/views/test_result.php` | 160 | 1 | 3 | 4 | 0 | 0 | živý |
| `app/views/tutorial.php` | 24 | 1 | 1 | 10 | 3 | 0 | živý |
| `app/views/tutorial_redirect.php` | 20 | 1 | 1 | 1 | 0 | 0 | živý |
| `app/views/v48_state.php` | 16 | 1 | 0 | 0 | 2 | 0 | kandidát |
| `app/views/visual_lab.php` | 15 | 1 | 1 | 3 | 0 | 0 | živý |
| `assets/cognitive-v43.css` | 14 | 0 | 2 | 0 | 2 | 2 | živý |
| `assets/cognitive-v43.js` | 131 | 0 | 5 | 0 | 2 | 2 | živý |
| `assets/goal-navigator-v50-4.js` | 7 | 0 | 1 | 0 | 2 | 0 | živý |
| `assets/hands-on-v50.css` | 29 | 0 | 2 | 0 | 0 | 1 | živý |
| `assets/hands-on-v50.js` | 80 | 0 | 5 | 0 | 0 | 1 | živý |
| `assets/learning-studio-v44.css` | 16 | 0 | 2 | 0 | 2 | 2 | živý |
| `assets/learning-studio-v44.js` | 134 | 0 | 5 | 0 | 2 | 2 | živý |
| `assets/one-task-v50-5.css` | 13 | 0 | 0 | 0 | 1 | 1 | živý |
| `assets/one-task-v50-5.js` | 133 | 0 | 3 | 0 | 1 | 1 | živý |
| `assets/one-task-v50-7-7.css` | 32 | 0 | 0 | 0 | 1 | 1 | živý |
| `assets/student-coach-v47.css` | 19 | 0 | 1 | 0 | 2 | 1 | živý |
| `assets/student-coach-v47.js` | 88 | 0 | 2 | 0 | 3 | 1 | živý |
| `assets/student-ui-v50-7-7.css` | 679 | 0 | 6 | 0 | 7 | 2 | živý |
| `assets/student-ui-v50-7-7.js` | 126 | 0 | 4 | 0 | 1 | 2 | živý |
| `assets/teacher-admin-v45-7.js` | 185 | 0 | 1 | 0 | 4 | 0 | živý |
| `assets/teacher-admin-v46.js` | 102 | 0 | 1 | 0 | 3 | 0 | živý |
| `assets/teacher-ops-v46.css` | 36 | 0 | 1 | 0 | 0 | 0 | živý |
| `assets/visual-labs-3a-v48-1.css` | 9 | 0 | 2 | 0 | 1 | 1 | živý |
| `assets/visual-labs-3a-v48-1.js` | 99 | 0 | 5 | 0 | 1 | 1 | živý |
| `assets/visual-practical-v48.css` | 3 | 0 | 2 | 0 | 1 | 1 | živý |
| `assets/visual-practical-v48.js` | 83 | 0 | 5 | 0 | 1 | 1 | živý |
| `assets/visual-simulation-v45.css` | 25 | 0 | 2 | 0 | 2 | 2 | živý |
| `assets/visual-simulation-v45.js` | 236 | 0 | 5 | 0 | 2 | 2 | živý |
| `cognitive_visualization_v43.php` | 466 | 0 | 1 | 0 | 1 | 0 | živý |
| `cognitive_visualization_views_v43.php` | 181 | 1 | 4 | 0 | 4 | 0 | živý |
| `goal_navigator_v50_4.php` | 131 | 1 | 3 | 0 | 6 | 0 | živý |
| `hands_on_learning_v50.php` | 509 | 1 | 4 | 0 | 6 | 0 | živý |
| `hands_on_learning_views_v50.php` | 86 | 1 | 4 | 0 | 2 | 0 | živý |
| `independent_growth_v50.php` | 185 | 1 | 4 | 0 | 6 | 0 | živý |
| `independent_growth_views_v50.php` | 39 | 1 | 4 | 0 | 2 | 0 | živý |
| `learning_studio_v44.php` | 213 | 0 | 2 | 0 | 2 | 0 | živý |
| `learning_studio_views_v44.php` | 127 | 1 | 4 | 0 | 1 | 0 | živý |
| `one_task_v50_5.php` | 328 | 1 | 4 | 0 | 6 | 0 | živý |
| `student_corrective_cycle_v47_2.php` | 137 | 1 | 3 | 0 | 4 | 0 | živý |
| `student_corrective_cycle_views_v47_2.php` | 32 | 1 | 3 | 0 | 4 | 0 | živý |
| `student_learning_accelerator_v47_1.php` | 144 | 1 | 3 | 0 | 5 | 0 | živý |
| `student_learning_accelerator_views_v47_1.php` | 55 | 1 | 3 | 0 | 4 | 0 | živý |
| `student_learning_coach_v47.php` | 288 | 1 | 3 | 0 | 6 | 0 | živý |
| `student_learning_coach_views_v47.php` | 90 | 1 | 3 | 0 | 5 | 0 | živý |
| `teacher_operations_control_v46_2.php` | 115 | 0 | 1 | 0 | 4 | 0 | živý |
| `teacher_operations_control_views_v46_2.php` | 52 | 0 | 1 | 0 | 2 | 0 | živý |
| `teacher_operations_plus_v46_1.php` | 184 | 0 | 1 | 0 | 5 | 0 | živý |
| `teacher_operations_plus_views_v46_1.php` | 40 | 0 | 1 | 0 | 2 | 0 | živý |
| `teacher_operations_v46.php` | 488 | 1 | 5 | 0 | 31 | 0 | živý |
| `teacher_operations_views_v46.php` | 96 | 0 | 1 | 0 | 3 | 0 | živý |
| `unified_page_shell_v50_7.php` | 125 | 1 | 3 | 0 | 5 | 0 | živý |
| `visual_labs_3a_v48_1.php` | 258 | 0 | 2 | 0 | 1 | 0 | živý |
| `visual_labs_3a_views_v48_1.php` | 111 | 1 | 4 | 0 | 1 | 0 | živý |
| `visual_practical_learning_v48.php` | 223 | 0 | 1 | 0 | 0 | 0 | živý |
| `visual_practical_learning_views_v48.php` | 94 | 1 | 4 | 0 | 2 | 0 | živý |
| `visual_simulation_v45.php` | 328 | 0 | 1 | 0 | 1 | 0 | živý |
| `visual_simulation_views_v45.php` | 128 | 1 | 4 | 0 | 2 | 0 | živý |
| `zero_friction_v50_6.php` | 65 | 1 | 3 | 0 | 5 | 0 | živý |

## Doporučení

- `app/views/v48_state.php`, `app/views/one_task.php`: bez odkazu `?view=`; ověř ručně (JS/endpointy), pak vyřadit po jednom a přidat přesměrování starého URL na přehled.
- Vstupní stránky `home.php`, `change_password.php`, `precache.php` vkládá přímo `index.php`/service worker – jsou záměrně vyňaté (živé).
- Vrstvy v42–v50 (`*_v4x.php`, `assets/*-v4x.*`) jsou dál načítané routerem a přehledem; jejich vyřazení vyžaduje rozhodnutí školy a samostatnou vrstvu (riziko: audity v51–v66 je pokrývají).
