# EDUCANET v68 · vyřazení zastaralých stránek a kandidáti na v69

Rozhodnutí uživatele (zadání v68): vyřadit zastaralé a nevyužívané stránky, zmodernizovat cockpit. Vyřazení probíhá vždy po jednom souboru přes `tools/v68_retire.php`
(kopie do `retired/v68/` → SHA-256 → smazání originálu; seznam `RETIRE68_APPROVED` je v kódu), nikdy shellovou smyčkou. Data ve `storage/` se nemažou.

## 1. Co se ve v68 skutečně změnilo

| Co | Jak | Důkaz |
|---|---|---|
| `app/views/v48_state.php` | **vyřazen** (kopie `retired/v68/app/views/v48_state.php`, SHA-256 `675d9a59…`), route odstraněna; `?view=v48_state` → 302 `?view=dashboard` (`app/redirects_v68.php`, volá `index.php` před routerem). Učitelská projekce má vlastní `?tab=teach&v48_state=1` (beze změny). | `tools/v68_cockpit_audit.php`, `tests/app_router_audit.php` |
| `?view=continue`, `?view=one_task` | route zůstává v tabulce, ale požadavek GET se přesměruje 302 na `?view=dashboard` (výjimka: `one_task&task=kb&topic=…` → `?view=kb_lesson`, jako dosud). Soubor `app/views/one_task.php` a knihovny One Task **zůstávají do v69** (načítá je skupina `layout`). Odkaz „Pokračovat ve výuce“ v `independent_growth_views_v50.php` vede na přehled. | `tools/v68_cockpit_audit.php` |
| Učitelské záložky `control`, `class_overview`, `growth`, `skills`, `mastery`, `filters`, `automations` | z menu zmizely; staré `?tab=` vrací 302: control/class_overview → `prehled`, growth → `cesty`, skills/mastery → `kompetence`, filters/automations → rozcestník sekce Správa. Cizí třída zůstává 403 (guard běží před přesměrováním). Soubory zůstávají do v69. | `tools/v68_cockpit_audit.php`, `tools/v59_teacher_scope_audit.php` |
| `tools/v45_3_admin_navigation_audit.php` | testoval staré menu (`teacher-nav-groups`, `teacher-contextbar`); má i behaviorální kontroly (read-only snapshoty, 28 lekcí) → **upraven** na funkce nového rámce (`teacher68_*_html`), ne vyřazen. Nové menu pokrývá navíc `tools/v68_cockpit_audit.php`. | – |

## 2. Další kandidáti z `tools/v68_dead_code_report.php`

Pravidlo zadání: vyřadit jen soubor **zcela bez vazby** (0 route, 0 odkazů v UI, 0 akcí, 0 načtení layoutem/knihovnou, mimo precache). Report (`php tools/v68_dead_code_report.php --md`)
rozlišuje verdikty *kandidát* (zcela bez vazby), *vázaný* (bez odkazu `?view=`, ale s route/načtením) a *živý*. **Výsledek: `V68_DEAD_CODE_REPORT_OK candidates=0 bound=0 live=100`** – žádný další soubor
nesplnil podmínku, proto se **nevyřazoval nic dalšího**. Soubory, které jsou po v68 prakticky mrtvé, ale ještě vázané (rozhodnutí ve v69):

| Soubor / skupina | Proč je vázaný | Návrh pro v69 |
|---|---|---|
| `app/views/one_task.php`, `one_task_v50_5.php`, `goal_navigator_v50_4.php`, `assets/one-task-v50-5.{css,js}`, `assets/one-task-v50-7-7.css` | Skupina `layout` v `app/routes.php` je načítá; funkce `v505_task_url()` stále sestavuje odkazy `?view=one_task` (všechny teď končí na přehledu) | přepojit volající `v505_task_url()` na cílové pohledy, pak soubory vyřadit přes `v68_retire.php` |
| `teacher_skill_views.php` (záložka `skills`), část `mastery_learning_views_v41.php` (`ml_render_teacher_hub`, záložka `mastery`; `ml_render_authoring` žije v „Obsah a otázky“) | UI nedostupné, POST akce `skill_*`, `ml_live_*`, `ml_failure_inject` jsou v `teacher.php` stále obsloužené | odebrat akce i pohledy, nebo znovu vystavit v sekci Hodnocení |
| `independent_growth_views_v50.php` – část `v50_render_teacher_growth`; akce `v50_teacher_growth_control` | záložka `growth` → 302 na `cesty` | odebrat učitelskou část |
| `teacher_operations_control_v46_2.php`, `teacher_operations_control_views_v46_2.php` (Control Tower), `teacher_render_automations`, `teacher_render_filters_manager`, `teacher_overview_dashboard.php` (`class_overview`) | záložky vyřazeny z UI, akce `teacher_automation_*`, `teacher_saved_filter_*`, `teacher_review_ack`, `teacher_sla_policy_save` zůstaly | po rozhodnutí školy odebrat, upravit audity (`v59_teacher_scope_audit`, `v46_*`) |
| `assets/hands-on-v50.css/js`, `assets/mastery.css` | cockpit je načítá už jen na záložkách analytics/teach, resp. authoring; žákovské stránky je používají dál | ponechat |

## 3. Úplná tabulka reportu (stav po v68)

| Soubor | Řádků | routes | kód | rodina | audity | precache | Verdikt |
|---|---:|---:|---:|---:|---:|---:|---|
| `adaptive_lesson_kits_v42.php` | 263 | 0 | 1 | 0 | 0 | 0 | živý |
| `adaptive_lesson_views_v42.php` | 101 | 1 | 3 | 0 | 0 | 0 | živý |
| `app/views/activate.php` | 15 | 1 | 1 | 2 | 0 | 0 | živý |
| `app/views/arena_events_v58.php` | 17 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/auth_links.php` | 49 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/case_study.php` | 76 | 1 | 3 | 2 | 0 | 0 | živý |
| `app/views/change_password.php` | 32 | 1 | 3 | 0 | 2 | 0 | živý |
| `app/views/dashboard.php` | 403 | 1 | 3 | 24 | 17 | 0 | živý |
| `app/views/extra_challenge.php` | 98 | 1 | 3 | 1 | 0 | 0 | živý |
| `app/views/feedback.php` | 22 | 1 | 2 | 5 | 1 | 0 | živý |
| `app/views/games_v58.php` | 16 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/graphics_guide.php` | 119 | 1 | 3 | 2 | 0 | 0 | živý |
| `app/views/graphics_studio.php` | 261 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/graphics_studio_gate.php` | 21 | 1 | 1 | 2 | 1 | 0 | živý |
| `app/views/hadanka.php` | 16 | 1 | 1 | 3 | 0 | 0 | živý |
| `app/views/hands_on_growth.php` | 32 | 1 | 3 | 3 | 0 | 0 | živý |
| `app/views/hodina.php` | 20 | 1 | 1 | 7 | 0 | 0 | živý |
| `app/views/hodnoceni.php` | 17 | 1 | 1 | 4 | 1 | 0 | živý |
| `app/views/home.php` | 107 | 1 | 3 | 0 | 2 | 0 | živý |
| `app/views/hry.php` | 15 | 1 | 1 | 7 | 0 | 0 | živý |
| `app/views/intake.php` | 17 | 1 | 3 | 8 | 0 | 0 | živý |
| `app/views/join.php` | 11 | 1 | 3 | 1 | 2 | 0 | živý |
| `app/views/kb_lesson.php` | 76 | 1 | 3 | 4 | 0 | 0 | živý |
| `app/views/kb_quiz.php` | 82 | 1 | 3 | 1 | 0 | 0 | živý |
| `app/views/knowledgebase.php` | 243 | 1 | 3 | 2 | 1 | 0 | živý |
| `app/views/lesson_hub.php` | 33 | 1 | 1 | 1 | 0 | 0 | živý |
| `app/views/lesson_path.php` | 31 | 1 | 2 | 5 | 1 | 0 | živý |
| `app/views/link_account.php` | 34 | 1 | 3 | 5 | 1 | 0 | živý |
| `app/views/linux_lab.php` | 28 | 1 | 2 | 18 | 0 | 0 | živý |
| `app/views/marketplace.php` | 18 | 1 | 2 | 6 | 0 | 0 | živý |
| `app/views/materials_redirect.php` | 15 | 1 | 0 | 7 | 1 | 0 | živý |
| `app/views/one_task.php` | 25 | 1 | 4 | 1 | 1 | 0 | živý |
| `app/views/paths.php` | 35 | 1 | 2 | 8 | 1 | 0 | živý |
| `app/views/portfolio.php` | 22 | 1 | 1 | 5 | 1 | 0 | živý |
| `app/views/practice.php` | 242 | 1 | 4 | 2 | 0 | 0 | živý |
| `app/views/precache.php` | 29 | 1 | 0 | 0 | 4 | 0 | živý |
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
| `app/views/visual_lab.php` | 15 | 1 | 1 | 3 | 0 | 0 | živý |
| `assets/cognitive-v43.css` | 14 | 0 | 4 | 0 | 4 | 2 | živý |
| `assets/cognitive-v43.js` | 131 | 0 | 5 | 0 | 2 | 2 | živý |
| `assets/goal-navigator-v50-4.js` | 7 | 0 | 1 | 0 | 2 | 0 | živý |
| `assets/hands-on-v50.css` | 29 | 0 | 3 | 0 | 1 | 1 | živý |
| `assets/hands-on-v50.js` | 80 | 0 | 5 | 0 | 0 | 1 | živý |
| `assets/learning-studio-v44.css` | 16 | 0 | 4 | 0 | 4 | 2 | živý |
| `assets/learning-studio-v44.js` | 134 | 0 | 5 | 0 | 2 | 2 | živý |
| `assets/one-task-v50-5.css` | 13 | 0 | 0 | 0 | 1 | 1 | živý |
| `assets/one-task-v50-5.js` | 133 | 0 | 3 | 0 | 1 | 1 | živý |
| `assets/one-task-v50-7-7.css` | 32 | 0 | 0 | 0 | 1 | 1 | živý |
| `assets/student-coach-v47.css` | 19 | 0 | 2 | 0 | 3 | 1 | živý |
| `assets/student-coach-v47.js` | 88 | 0 | 2 | 0 | 3 | 1 | živý |
| `assets/student-ui-v50-7-7.css` | 679 | 0 | 12 | 0 | 9 | 2 | živý |
| `assets/student-ui-v50-7-7.js` | 126 | 0 | 4 | 0 | 1 | 2 | živý |
| `assets/teacher-admin-v45-7.js` | 185 | 0 | 1 | 0 | 4 | 0 | živý |
| `assets/teacher-admin-v46.js` | 102 | 0 | 1 | 0 | 3 | 0 | živý |
| `assets/teacher-ops-v46.css` | 36 | 0 | 1 | 0 | 1 | 0 | živý |
| `assets/visual-labs-3a-v48-1.css` | 9 | 0 | 2 | 0 | 2 | 1 | živý |
| `assets/visual-labs-3a-v48-1.js` | 99 | 0 | 5 | 0 | 1 | 1 | živý |
| `assets/visual-practical-v48.css` | 3 | 0 | 2 | 0 | 2 | 1 | živý |
| `assets/visual-practical-v48.js` | 83 | 0 | 5 | 0 | 1 | 1 | živý |
| `assets/visual-simulation-v45.css` | 25 | 0 | 4 | 0 | 4 | 2 | živý |
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
| `teacher_operations_v46.php` | 494 | 1 | 5 | 0 | 33 | 0 | živý |
| `teacher_operations_views_v46.php` | 96 | 0 | 1 | 0 | 3 | 0 | živý |
| `unified_page_shell_v50_7.php` | 125 | 1 | 3 | 0 | 5 | 0 | živý |
| `visual_labs_3a_v48_1.php` | 258 | 0 | 2 | 0 | 1 | 0 | živý |
| `visual_labs_3a_views_v48_1.php` | 111 | 1 | 4 | 0 | 1 | 0 | živý |
| `visual_practical_learning_v48.php` | 223 | 0 | 1 | 0 | 0 | 0 | živý |
| `visual_practical_learning_views_v48.php` | 94 | 1 | 4 | 0 | 2 | 0 | živý |
| `visual_simulation_v45.php` | 328 | 0 | 1 | 0 | 1 | 0 | živý |
| `visual_simulation_views_v45.php` | 128 | 1 | 4 | 0 | 2 | 0 | živý |
| `zero_friction_v50_6.php` | 65 | 1 | 3 | 0 | 5 | 0 | živý |
V68_DEAD_CODE_REPORT_OK candidates=0 bound=0 live=100
