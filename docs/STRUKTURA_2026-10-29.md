# Struktura EDUCANET / Quiet Study · 29. 10. 2026

## Rozsah

Výchozí commit `e1d6c7c` navazuje na větev `feature/vizual-a-testy-2026-10-29` (PR #2). Úpravy vznikly ve větvi `feature/struktura-slozek-2026-10-29`, bez pushnutí a bez nasazení.

Z 429 PHP souborů v kořeni se 418 přesunulo pomocí `git mv` do 16 oblastí `src/`. Názvy souborů zůstaly zachované. Kořen nyní obsahuje 11 PHP souborů: devět veřejných vstupů/API, `bootstrap.php` a obsahový `practice.php`. Zachované veřejné soubory a jejich URL:

- `index.php`, `teacher.php`, `calendar.ics.php`, `progress.php`, `export_extra_csv.php`;
- `lab_v57_api.php`, `arena_v58_events_api.php`, `robots_v58_api.php`, `teamgames_v58_api.php`;
- `practice.php`, `lab-offline.html`, `sw.js`, `manifest.webmanifest`.

`app/` zůstává routerem studentské aplikace. Knihovny a jejich pohledy jsou u příslušných modulů. Obsahové soubory `lesson_content_*` jsou v `src/content/`; dynamický seznam obsahu i jeho cache sledují tento adresář. Cesty v registrech routeru, učitelských modulech, překladových doménách, migracích, generátorech a auditech odpovídají přesunu. Ochranu `src/` obsahuje `.htaccess` i vzorové konfigurace Nginxu. Build vydání zahrnuje `src/` a vynechává nové lokální důkazy kontrol.

Adresáře `storage/`, `private/`, `uploads/`, `cache/`, `migrations/`, `assets/`, `materials/`, `lang/`, `tools/` a `docs/` zůstaly na místě. SHA-256 kontroly testovacích nástrojů potvrdily nezměněná provozní data. V `lang/`, `migrations/`, `tools/` a vzorech v `docs/deploy/` se změnily odkazy na přesunuté zdroje.

## Ověření

Kontroly proběhly lokálně 29. 10. 2026. HTTP a vizuální běhy používaly `/usr/bin/php -S 127.0.0.1:8090`, `PHP_INI_SCAN_DIR=:$HOME/.config/php/conf.d`, náhodný dočasný `EDUCANET_TEACHER_EXPORT_KEY` a kopii aplikace včetně `storage/` v dočasném adresáři. Klíč se neukládal do repozitáře. Další PHP audity a sestavení cache běžely rovněž v oddělené kopii.

| Kontrola | Před přesunem | Po přesunu |
| --- | --- | --- |
| HTTP vstupy a odkazy, tři role | 892 požadavků, 0 PHP chyb | 892 požadavků, všechny stavy a přesměrování shodné, 0 PHP chyb |
| Vizuální QA | 52 zobrazení, 72 úspěšných interakcí | 52 zobrazení, 72 úspěšných interakcí |
| Přístupnost a přetečení ve vizuálním QA | 0 hlášených problémů | 0 hlášených problémů, metriky shodné |
| Chyby JavaScriptu, konzole, HTTP zdrojů, PHP ve vizuálním QA | 0 | 0 |
| Chráněná data kontrolovaná HTTP nástrojem | SHA-256 beze změny: storage, private, uploads, cache | Totéž |
| Syntaxe všech PHP souborů | — | 860 souborů, 0 chyb |
| Sestavení runtime cache a modelu lekcí | — | 4 třídy × 28 lekcí, 0 konfliktů, cache aktuální |
| Sestavení vydání s `--lint` | — | Úspěch; manifest obsahuje 418 PHP souborů v src a src/.htaccess |

`tools/structure_http_2026_10_29.cjs` kontroluje veřejné PHP vstupy, offline stránku a PWA soubory, přesné názvy pohledů z `app/routes.php` a odkazy/formulářové cíle jejich stránek. Běží jako nepřihlášený návštěvník, student 3.A a učitel. Prochází jednu úroveň odkazů; nevymýšlí všechny kombinace parametrů a neodesílá POST formuláře. Zachované odmítavé stavy API bez požadované metody a autentizace nejsou regresí. Porovnání se týká HTTP stavu, přesměrování a PHP logu, nikoli totožnosti celého HTML.

`tools/visual_qa_2026_10_29.cjs` běžel s fázemi `structure-before` a `structure-after`: světlý/tmavý motiv, šířky 1440/390 px, 48 běžných zobrazení a 4 zobrazení aktivní hodiny. Seznam všech 72 interakcí a sledované metriky jsou shodné. Z 48 samostatně uložených párů snímků je 46 pixelově shodných; 2 má drobné odchylky v JPEG snímcích, nejvýše 4 z 255 na barevný kanál, bez změny rozměrů. Nejde o tvrzení o pixelové shodě všech 52 zobrazení.

### Doplňkové audity

| Audit | Výsledek po přesunu |
| --- | --- |
| `v57_linux_lab_audit.php` | 330 kontrol, 0 chyb |
| `v58_levels_audit.php` | 202 kontrol, 0 chyb |
| `v58_quizbank_audit.php` | 438 kontrol, 0 chyb |
| `v71_lesson_model_audit.php` | 32 kontrol, 0 chyb |
| `v72_lesson_content_audit.php` | 16 kontrol, 0 chyb |

Další audity mají stejné existující chyby jako na výchozím kódu; jejich selhání bylo samostatně zopakováno před přesunem i po něm:

- `v58_lab_ext_audit.php`: 186 kontrol, 1 chyba `team:logs-per-player`.
- `v59_i18n_audit.php`: 1075 kontrol, 3 chyby pokrytí překladové domény `learn` (angličtina, ukrajinština, nepoužité položky).
- `v76_content_audit.php`: 23 kontrol, 1 chyba odkazující na chybějící historické dokumenty vydání v kořeni.
- `v77_content_audit.php`: 26 kontrol, 1 stejná chyba dokumentace.
- `v81_visual_audit.php`: 38 kontrol, 1 stejná chyba dokumentace.

Tyto závady nebyly opravovány změnou aplikační logiky. Kompletní `run_audits.php --since=all`, produkční aaPanel, skutečné odesílání e-mailů ani všechny POST akce nebyly ověřeny. Nástroje codebase-memory v tomto běhu nebyly dostupné; mapování závislostí vycházelo z přímého čtení zdrojů a hledání cest. Lokální HTTP důkazy mohou obsahovat identifikátory žáků, proto jsou mimo Git i sestavené vydání.

Kontrola `bash -n` prošla pro všechny tři aaPanel shellové vzory; lokální odkazy v README i v mapě přesunů existují. Kontrola build filtrů ověřila pět případů: knihovnu v src, src/.htaccess a vyloučení Git konfigurace i lokálních HTTP/vizuálních důkazů.

## Počty podle oblastí

| Složka | Přesunuté PHP soubory |
| --- | ---: |
| `src/arena/` | 15 |
| `src/assessment/` | 34 |
| `src/content/` | 37 |
| `src/i18n/` | 2 |
| `src/knowledge/` | 13 |
| `src/lessons/` | 53 |
| `src/levels/` | 11 |
| `src/linux/` | 52 |
| `src/ops/` | 14 |
| `src/paths/` | 14 |
| `src/projects/` | 14 |
| `src/robots/` | 4 |
| `src/student/` | 62 |
| `src/teacher/` | 46 |
| `src/teamgames/` | 32 |
| `src/ui/` | 15 |

## Mapa přesunů

| Stará cesta | Nová cesta |
| --- | --- |
| `accounts_v53.php` | [src/student/accounts_v53.php](../src/student/accounts_v53.php) |
| `accounts_v58.php` | [src/student/accounts_v58.php](../src/student/accounts_v58.php) |
| `accounts_v58_views.php` | [src/student/accounts_v58_views.php](../src/student/accounts_v58_views.php) |
| `adaptive_learning.php` | [src/lessons/adaptive_learning.php](../src/lessons/adaptive_learning.php) |
| `adaptive_lesson_kits_v42.php` | [src/lessons/adaptive_lesson_kits_v42.php](../src/lessons/adaptive_lesson_kits_v42.php) |
| `adaptive_lesson_views_v42.php` | [src/lessons/adaptive_lesson_views_v42.php](../src/lessons/adaptive_lesson_views_v42.php) |
| `admin_overview_v70.php` | [src/teacher/admin_overview_v70.php](../src/teacher/admin_overview_v70.php) |
| `admin_overview_v70_views.php` | [src/teacher/admin_overview_v70_views.php](../src/teacher/admin_overview_v70_views.php) |
| `ai_prompts_v72_1.php` | [src/teacher/ai_prompts_v72_1.php](../src/teacher/ai_prompts_v72_1.php) |
| `ai_prompts_v76.php` | [src/teacher/ai_prompts_v76.php](../src/teacher/ai_prompts_v76.php) |
| `ai_prompts_v77.php` | [src/teacher/ai_prompts_v77.php](../src/teacher/ai_prompts_v77.php) |
| `arena_v57.php` | [src/arena/arena_v57.php](../src/arena/arena_v57.php) |
| `arena_v57_views.php` | [src/arena/arena_v57_views.php](../src/arena/arena_v57_views.php) |
| `arena_v58_ctf.php` | [src/arena/arena_v58_ctf.php](../src/arena/arena_v58_ctf.php) |
| `arena_v58_events_views.php` | [src/arena/arena_v58_events_views.php](../src/arena/arena_v58_events_views.php) |
| `arena_v58_incident.php` | [src/arena/arena_v58_incident.php](../src/arena/arena_v58_incident.php) |
| `arena_v58_replay.php` | [src/arena/arena_v58_replay.php](../src/arena/arena_v58_replay.php) |
| `arena_v58_views.php` | [src/arena/arena_v58_views.php](../src/arena/arena_v58_views.php) |
| `arena_v58_weekly.php` | [src/arena/arena_v58_weekly.php](../src/arena/arena_v58_weekly.php) |
| `arena_v60_challenge.php` | [src/arena/arena_v60_challenge.php](../src/arena/arena_v60_challenge.php) |
| `arena_v60_challenge_views.php` | [src/arena/arena_v60_challenge_views.php](../src/arena/arena_v60_challenge_views.php) |
| `arena_v61_duels.php` | [src/arena/arena_v61_duels.php](../src/arena/arena_v61_duels.php) |
| `arena_v61_teacher_views.php` | [src/arena/arena_v61_teacher_views.php](../src/arena/arena_v61_teacher_views.php) |
| `arena_v61_views.php` | [src/arena/arena_v61_views.php](../src/arena/arena_v61_views.php) |
| `arena_v64_fair.php` | [src/arena/arena_v64_fair.php](../src/arena/arena_v64_fair.php) |
| `arena_v64_views.php` | [src/arena/arena_v64_views.php](../src/arena/arena_v64_views.php) |
| `assessment_v66.php` | [src/assessment/assessment_v66.php](../src/assessment/assessment_v66.php) |
| `assessment_v66_build.php` | [src/assessment/assessment_v66_build.php](../src/assessment/assessment_v66_build.php) |
| `assessment_v66_teacher_grading.php` | [src/assessment/assessment_v66_teacher_grading.php](../src/assessment/assessment_v66_teacher_grading.php) |
| `assessment_v66_teacher_views.php` | [src/assessment/assessment_v66_teacher_views.php](../src/assessment/assessment_v66_teacher_views.php) |
| `assessment_visuals.php` | [src/assessment/assessment_visuals.php](../src/assessment/assessment_visuals.php) |
| `badges_v60.php` | [src/student/badges_v60.php](../src/student/badges_v60.php) |
| `bulk_approval_v74.php` | [src/teacher/bulk_approval_v74.php](../src/teacher/bulk_approval_v74.php) |
| `bulk_approval_v74_views.php` | [src/teacher/bulk_approval_v74_views.php](../src/teacher/bulk_approval_v74_views.php) |
| `bulk_approval_v79.php` | [src/teacher/bulk_approval_v79.php](../src/teacher/bulk_approval_v79.php) |
| `calendar_days_v71.php` | [src/ops/calendar_days_v71.php](../src/ops/calendar_days_v71.php) |
| `challenges_v64.php` | [src/student/challenges_v64.php](../src/student/challenges_v64.php) |
| `classmap_v80_1.php` | [src/teacher/classmap_v80_1.php](../src/teacher/classmap_v80_1.php) |
| `classmap_v80_1_views.php` | [src/teacher/classmap_v80_1_views.php](../src/teacher/classmap_v80_1_views.php) |
| `cockpit_v70.php` | [src/teacher/cockpit_v70.php](../src/teacher/cockpit_v70.php) |
| `cognitive_visualization_v43.php` | [src/lessons/cognitive_visualization_v43.php](../src/lessons/cognitive_visualization_v43.php) |
| `cognitive_visualization_views_v43.php` | [src/lessons/cognitive_visualization_views_v43.php](../src/lessons/cognitive_visualization_views_v43.php) |
| `competencies_v62.php` | [src/assessment/competencies_v62.php](../src/assessment/competencies_v62.php) |
| `competencies_v75.php` | [src/assessment/competencies_v75.php](../src/assessment/competencies_v75.php) |
| `competencies_v75_views.php` | [src/assessment/competencies_v75_views.php](../src/assessment/competencies_v75_views.php) |
| `competency_v62_teacher_views.php` | [src/assessment/competency_v62_teacher_views.php](../src/assessment/competency_v62_teacher_views.php) |
| `competency_v62_views.php` | [src/assessment/competency_v62_views.php](../src/assessment/competency_v62_views.php) |
| `competency_v75_labels.php` | [src/assessment/competency_v75_labels.php](../src/assessment/competency_v75_labels.php) |
| `design_v78.php` | [src/ui/design_v78.php](../src/ui/design_v78.php) |
| `design_v78_views.php` | [src/ui/design_v78_views.php](../src/ui/design_v78_views.php) |
| `economy_v64.php` | [src/student/economy_v64.php](../src/student/economy_v64.php) |
| `economy_v64_teacher_views.php` | [src/student/economy_v64_teacher_views.php](../src/student/economy_v64_teacher_views.php) |
| `evidence_v62.php` | [src/assessment/evidence_v62.php](../src/assessment/evidence_v62.php) |
| `evidence_v62_adapters.php` | [src/assessment/evidence_v62_adapters.php](../src/assessment/evidence_v62_adapters.php) |
| `extended_lessons.php` | [src/content/extended_lessons.php](../src/content/extended_lessons.php) |
| `feedback_v60.php` | [src/student/feedback_v60.php](../src/student/feedback_v60.php) |
| `feedback_v60_teacher_views.php` | [src/student/feedback_v60_teacher_views.php](../src/student/feedback_v60_teacher_views.php) |
| `feedback_v60_views.php` | [src/student/feedback_v60_views.php](../src/student/feedback_v60_views.php) |
| `first_year.php` | [src/content/first_year.php](../src/content/first_year.php) |
| `goal_navigator_v50_4.php` | [src/student/goal_navigator_v50_4.php](../src/student/goal_navigator_v50_4.php) |
| `grading_v66.php` | [src/assessment/grading_v66.php](../src/assessment/grading_v66.php) |
| `grading_v66_views.php` | [src/assessment/grading_v66_views.php](../src/assessment/grading_v66_views.php) |
| `graphics_curriculum.php` | [src/content/graphics_curriculum.php](../src/content/graphics_curriculum.php) |
| `growth_v67.php` | [src/student/growth_v67.php](../src/student/growth_v67.php) |
| `growth_v67_views.php` | [src/student/growth_v67_views.php](../src/student/growth_v67_views.php) |
| `hands_on_learning_v50.php` | [src/lessons/hands_on_learning_v50.php](../src/lessons/hands_on_learning_v50.php) |
| `hands_on_learning_views_v50.php` | [src/lessons/hands_on_learning_views_v50.php](../src/lessons/hands_on_learning_views_v50.php) |
| `hints_v80.php` | [src/lessons/hints_v80.php](../src/lessons/hints_v80.php) |
| `hints_v80_gfx.php` | [src/lessons/hints_v80_gfx.php](../src/lessons/hints_v80_gfx.php) |
| `i18n_v58.php` | [src/i18n/i18n_v58.php](../src/i18n/i18n_v58.php) |
| `i18n_v59.php` | [src/i18n/i18n_v59.php](../src/i18n/i18n_v59.php) |
| `identity_v58.php` | [src/student/identity_v58.php](../src/student/identity_v58.php) |
| `identity_v58_rollover.php` | [src/student/identity_v58_rollover.php](../src/student/identity_v58_rollover.php) |
| `identity_v58_views.php` | [src/student/identity_v58_views.php](../src/student/identity_v58_views.php) |
| `identity_v79.php` | [src/student/identity_v79.php](../src/student/identity_v79.php) |
| `independent_growth_v50.php` | [src/student/independent_growth_v50.php](../src/student/independent_growth_v50.php) |
| `independent_growth_views_v50.php` | [src/student/independent_growth_views_v50.php](../src/student/independent_growth_views_v50.php) |
| `insights_v74.php` | [src/teacher/insights_v74.php](../src/teacher/insights_v74.php) |
| `insights_v74_views.php` | [src/teacher/insights_v74_views.php](../src/teacher/insights_v74_views.php) |
| `intake_v51.php` | [src/student/intake_v51.php](../src/student/intake_v51.php) |
| `intake_v51_teacher.php` | [src/student/intake_v51_teacher.php](../src/student/intake_v51_teacher.php) |
| `intake_v51_views.php` | [src/student/intake_v51_views.php](../src/student/intake_v51_views.php) |
| `integrity_v66.php` | [src/assessment/integrity_v66.php](../src/assessment/integrity_v66.php) |
| `knowledge_extensions.php` | [src/knowledge/knowledge_extensions.php](../src/knowledge/knowledge_extensions.php) |
| `knowledge_extensions_ecosystem.php` | [src/knowledge/knowledge_extensions_ecosystem.php](../src/knowledge/knowledge_extensions_ecosystem.php) |
| `knowledge_extensions_more.php` | [src/knowledge/knowledge_extensions_more.php](../src/knowledge/knowledge_extensions_more.php) |
| `knowledge_extensions_plus.php` | [src/knowledge/knowledge_extensions_plus.php](../src/knowledge/knowledge_extensions_plus.php) |
| `knowledge_extensions_v30.php` | [src/knowledge/knowledge_extensions_v30.php](../src/knowledge/knowledge_extensions_v30.php) |
| `knowledge_extensions_yearpack.php` | [src/knowledge/knowledge_extensions_yearpack.php](../src/knowledge/knowledge_extensions_yearpack.php) |
| `knowledge_tours.php` | [src/knowledge/knowledge_tours.php](../src/knowledge/knowledge_tours.php) |
| `knowledge_tours_ecosystem.php` | [src/knowledge/knowledge_tours_ecosystem.php](../src/knowledge/knowledge_tours_ecosystem.php) |
| `knowledge_tours_more.php` | [src/knowledge/knowledge_tours_more.php](../src/knowledge/knowledge_tours_more.php) |
| `knowledge_tours_next.php` | [src/knowledge/knowledge_tours_next.php](../src/knowledge/knowledge_tours_next.php) |
| `knowledge_tours_plus.php` | [src/knowledge/knowledge_tours_plus.php](../src/knowledge/knowledge_tours_plus.php) |
| `knowledge_tours_v30.php` | [src/knowledge/knowledge_tours_v30.php](../src/knowledge/knowledge_tours_v30.php) |
| `knowledge_tours_yearpack.php` | [src/knowledge/knowledge_tours_yearpack.php](../src/knowledge/knowledge_tours_yearpack.php) |
| `lab_v58_content.php` | [src/linux/lab_v58_content.php](../src/linux/lab_v58_content.php) |
| `lab_v58_editor.php` | [src/linux/lab_v58_editor.php](../src/linux/lab_v58_editor.php) |
| `lab_v58_editor_views.php` | [src/linux/lab_v58_editor_views.php](../src/linux/lab_v58_editor_views.php) |
| `lab_v58_learning.php` | [src/linux/lab_v58_learning.php](../src/linux/lab_v58_learning.php) |
| `lab_v58_log.php` | [src/linux/lab_v58_log.php](../src/linux/lab_v58_log.php) |
| `lab_v58_review.php` | [src/linux/lab_v58_review.php](../src/linux/lab_v58_review.php) |
| `lab_v58_teacher.php` | [src/linux/lab_v58_teacher.php](../src/linux/lab_v58_teacher.php) |
| `lab_v58_teacher_views.php` | [src/linux/lab_v58_teacher_views.php](../src/linux/lab_v58_teacher_views.php) |
| `learning_resources.php` | [src/content/learning_resources.php](../src/content/learning_resources.php) |
| `learning_studio_v44.php` | [src/lessons/learning_studio_v44.php](../src/lessons/learning_studio_v44.php) |
| `learning_studio_views_v44.php` | [src/lessons/learning_studio_views_v44.php](../src/lessons/learning_studio_views_v44.php) |
| `learning_v56.php` | [src/lessons/learning_v56.php](../src/lessons/learning_v56.php) |
| `learning_v56_views.php` | [src/lessons/learning_v56_views.php](../src/lessons/learning_v56_views.php) |
| `learning_v80.php` | [src/lessons/learning_v80.php](../src/lessons/learning_v80.php) |
| `learning_v80_post.php` | [src/lessons/learning_v80_post.php](../src/lessons/learning_v80_post.php) |
| `learning_v80_teacher_views.php` | [src/lessons/learning_v80_teacher_views.php](../src/lessons/learning_v80_teacher_views.php) |
| `learning_v80_views.php` | [src/lessons/learning_v80_views.php](../src/lessons/learning_v80_views.php) |
| `lesson_approval_v72.php` | [src/lessons/lesson_approval_v72.php](../src/lessons/lesson_approval_v72.php) |
| `lesson_approval_v72_views.php` | [src/lessons/lesson_approval_v72_views.php](../src/lessons/lesson_approval_v72_views.php) |
| `lesson_content_v72_1a_a.php` | [src/content/lesson_content_v72_1a_a.php](../src/content/lesson_content_v72_1a_a.php) |
| `lesson_content_v72_1a_b.php` | [src/content/lesson_content_v72_1a_b.php](../src/content/lesson_content_v72_1a_b.php) |
| `lesson_content_v72_2a_a.php` | [src/content/lesson_content_v72_2a_a.php](../src/content/lesson_content_v72_2a_a.php) |
| `lesson_content_v72_2a_b.php` | [src/content/lesson_content_v72_2a_b.php](../src/content/lesson_content_v72_2a_b.php) |
| `lesson_content_v72_3a_a.php` | [src/content/lesson_content_v72_3a_a.php](../src/content/lesson_content_v72_3a_a.php) |
| `lesson_content_v72_3a_b.php` | [src/content/lesson_content_v72_3a_b.php](../src/content/lesson_content_v72_3a_b.php) |
| `lesson_content_v72_4a_a.php` | [src/content/lesson_content_v72_4a_a.php](../src/content/lesson_content_v72_4a_a.php) |
| `lesson_content_v72_4a_b.php` | [src/content/lesson_content_v72_4a_b.php](../src/content/lesson_content_v72_4a_b.php) |
| `lesson_content_v76_1a_a.php` | [src/content/lesson_content_v76_1a_a.php](../src/content/lesson_content_v76_1a_a.php) |
| `lesson_content_v76_1a_b.php` | [src/content/lesson_content_v76_1a_b.php](../src/content/lesson_content_v76_1a_b.php) |
| `lesson_content_v76_2a_a.php` | [src/content/lesson_content_v76_2a_a.php](../src/content/lesson_content_v76_2a_a.php) |
| `lesson_content_v76_2a_b.php` | [src/content/lesson_content_v76_2a_b.php](../src/content/lesson_content_v76_2a_b.php) |
| `lesson_content_v76_3a_a.php` | [src/content/lesson_content_v76_3a_a.php](../src/content/lesson_content_v76_3a_a.php) |
| `lesson_content_v76_3a_b.php` | [src/content/lesson_content_v76_3a_b.php](../src/content/lesson_content_v76_3a_b.php) |
| `lesson_content_v76_4a_a.php` | [src/content/lesson_content_v76_4a_a.php](../src/content/lesson_content_v76_4a_a.php) |
| `lesson_content_v76_4a_b.php` | [src/content/lesson_content_v76_4a_b.php](../src/content/lesson_content_v76_4a_b.php) |
| `lesson_content_v77_1a.php` | [src/content/lesson_content_v77_1a.php](../src/content/lesson_content_v77_1a.php) |
| `lesson_content_v77_2a.php` | [src/content/lesson_content_v77_2a.php](../src/content/lesson_content_v77_2a.php) |
| `lesson_content_v77_3a.php` | [src/content/lesson_content_v77_3a.php](../src/content/lesson_content_v77_3a.php) |
| `lesson_content_v77_4a.php` | [src/content/lesson_content_v77_4a.php](../src/content/lesson_content_v77_4a.php) |
| `lesson_content_v77_days_1a.php` | [src/content/lesson_content_v77_days_1a.php](../src/content/lesson_content_v77_days_1a.php) |
| `lesson_content_v77_days_2a.php` | [src/content/lesson_content_v77_days_2a.php](../src/content/lesson_content_v77_days_2a.php) |
| `lesson_content_v77_days_3a.php` | [src/content/lesson_content_v77_days_3a.php](../src/content/lesson_content_v77_days_3a.php) |
| `lesson_content_v77_days_4a.php` | [src/content/lesson_content_v77_days_4a.php](../src/content/lesson_content_v77_days_4a.php) |
| `lesson_exit_v72.php` | [src/lessons/lesson_exit_v72.php](../src/lessons/lesson_exit_v72.php) |
| `lesson_exit_v72_views.php` | [src/lessons/lesson_exit_v72_views.php](../src/lessons/lesson_exit_v72_views.php) |
| `lesson_glossary_v72.php` | [src/lessons/lesson_glossary_v72.php](../src/lessons/lesson_glossary_v72.php) |
| `lesson_model_v71.php` | [src/lessons/lesson_model_v71.php](../src/lessons/lesson_model_v71.php) |
| `lesson_model_v71_sources.php` | [src/lessons/lesson_model_v71_sources.php](../src/lessons/lesson_model_v71_sources.php) |
| `lesson_prep_v73.php` | [src/lessons/lesson_prep_v73.php](../src/lessons/lesson_prep_v73.php) |
| `lesson_print_v73_views.php` | [src/lessons/lesson_print_v73_views.php](../src/lessons/lesson_print_v73_views.php) |
| `lesson_projector_v73_views.php` | [src/lessons/lesson_projector_v73_views.php](../src/lessons/lesson_projector_v73_views.php) |
| `lesson_reflect_v73.php` | [src/lessons/lesson_reflect_v73.php](../src/lessons/lesson_reflect_v73.php) |
| `lesson_reflect_v73_views.php` | [src/lessons/lesson_reflect_v73_views.php](../src/lessons/lesson_reflect_v73_views.php) |
| `lesson_reflect_v79.php` | [src/lessons/lesson_reflect_v79.php](../src/lessons/lesson_reflect_v79.php) |
| `lesson_today_v70.php` | [src/lessons/lesson_today_v70.php](../src/lessons/lesson_today_v70.php) |
| `lesson_today_v70_views.php` | [src/lessons/lesson_today_v70_views.php](../src/lessons/lesson_today_v70_views.php) |
| `lesson_today_v71_views.php` | [src/lessons/lesson_today_v71_views.php](../src/lessons/lesson_today_v71_views.php) |
| `lesson_today_v79_1_views.php` | [src/lessons/lesson_today_v79_1_views.php](../src/lessons/lesson_today_v79_1_views.php) |
| `lessons_ecosystem.php` | [src/lessons/lessons_ecosystem.php](../src/lessons/lessons_ecosystem.php) |
| `lessons_more.php` | [src/lessons/lessons_more.php](../src/lessons/lessons_more.php) |
| `lessons_plus.php` | [src/lessons/lessons_plus.php](../src/lessons/lessons_plus.php) |
| `lessons_v30.php` | [src/lessons/lessons_v30.php](../src/lessons/lessons_v30.php) |
| `lessons_yearpack.php` | [src/lessons/lessons_yearpack.php](../src/lessons/lessons_yearpack.php) |
| `levels_v75.php` | [src/levels/levels_v75.php](../src/levels/levels_v75.php) |
| `levels_v75_content_1a.php` | [src/levels/levels_v75_content_1a.php](../src/levels/levels_v75_content_1a.php) |
| `levels_v75_content_2a.php` | [src/levels/levels_v75_content_2a.php](../src/levels/levels_v75_content_2a.php) |
| `levels_v75_content_3a.php` | [src/levels/levels_v75_content_3a.php](../src/levels/levels_v75_content_3a.php) |
| `levels_v75_content_4a.php` | [src/levels/levels_v75_content_4a.php](../src/levels/levels_v75_content_4a.php) |
| `levels_v75_teacher_views.php` | [src/levels/levels_v75_teacher_views.php](../src/levels/levels_v75_teacher_views.php) |
| `levels_v75_views.php` | [src/levels/levels_v75_views.php](../src/levels/levels_v75_views.php) |
| `levels_v76_content_1a.php` | [src/levels/levels_v76_content_1a.php](../src/levels/levels_v76_content_1a.php) |
| `levels_v76_content_2a.php` | [src/levels/levels_v76_content_2a.php](../src/levels/levels_v76_content_2a.php) |
| `levels_v76_content_3a.php` | [src/levels/levels_v76_content_3a.php](../src/levels/levels_v76_content_3a.php) |
| `levels_v76_content_4a.php` | [src/levels/levels_v76_content_4a.php](../src/levels/levels_v76_content_4a.php) |
| `linux_v57_cmd_files.php` | [src/linux/linux_v57_cmd_files.php](../src/linux/linux_v57_cmd_files.php) |
| `linux_v57_cmd_net.php` | [src/linux/linux_v57_cmd_net.php](../src/linux/linux_v57_cmd_net.php) |
| `linux_v57_cmd_shell.php` | [src/linux/linux_v57_cmd_shell.php](../src/linux/linux_v57_cmd_shell.php) |
| `linux_v57_cmd_sys.php` | [src/linux/linux_v57_cmd_sys.php](../src/linux/linux_v57_cmd_sys.php) |
| `linux_v57_cmd_text.php` | [src/linux/linux_v57_cmd_text.php](../src/linux/linux_v57_cmd_text.php) |
| `linux_v57_core.php` | [src/linux/linux_v57_core.php](../src/linux/linux_v57_core.php) |
| `linux_v57_lab.php` | [src/linux/linux_v57_lab.php](../src/linux/linux_v57_lab.php) |
| `linux_v57_levels.php` | [src/linux/linux_v57_levels.php](../src/linux/linux_v57_levels.php) |
| `linux_v57_levels_ops.php` | [src/linux/linux_v57_levels_ops.php](../src/linux/linux_v57_levels_ops.php) |
| `linux_v57_manual.php` | [src/linux/linux_v57_manual.php](../src/linux/linux_v57_manual.php) |
| `linux_v57_shell.php` | [src/linux/linux_v57_shell.php](../src/linux/linux_v57_shell.php) |
| `linux_v57_views.php` | [src/linux/linux_v57_views.php](../src/linux/linux_v57_views.php) |
| `linux_v57_world.php` | [src/linux/linux_v57_world.php](../src/linux/linux_v57_world.php) |
| `linux_v58_cmd_archive.php` | [src/linux/linux_v58_cmd_archive.php](../src/linux/linux_v58_cmd_archive.php) |
| `linux_v58_cmd_cron.php` | [src/linux/linux_v58_cmd_cron.php](../src/linux/linux_v58_cmd_cron.php) |
| `linux_v58_cmd_extra.php` | [src/linux/linux_v58_cmd_extra.php](../src/linux/linux_v58_cmd_extra.php) |
| `linux_v58_cmd_extra_bc.php` | [src/linux/linux_v58_cmd_extra_bc.php](../src/linux/linux_v58_cmd_extra_bc.php) |
| `linux_v58_cmd_extra_calc.php` | [src/linux/linux_v58_cmd_extra_calc.php](../src/linux/linux_v58_cmd_extra_calc.php) |
| `linux_v58_cmd_extra_sys.php` | [src/linux/linux_v58_cmd_extra_sys.php](../src/linux/linux_v58_cmd_extra_sys.php) |
| `linux_v58_cmd_git.php` | [src/linux/linux_v58_cmd_git.php](../src/linux/linux_v58_cmd_git.php) |
| `linux_v58_cmd_git_diff.php` | [src/linux/linux_v58_cmd_git_diff.php](../src/linux/linux_v58_cmd_git_diff.php) |
| `linux_v58_cmd_git_more.php` | [src/linux/linux_v58_cmd_git_more.php](../src/linux/linux_v58_cmd_git_more.php) |
| `linux_v58_cmd_git_store.php` | [src/linux/linux_v58_cmd_git_store.php](../src/linux/linux_v58_cmd_git_store.php) |
| `linux_v58_cmd_media.php` | [src/linux/linux_v58_cmd_media.php](../src/linux/linux_v58_cmd_media.php) |
| `linux_v58_cmd_media_im.php` | [src/linux/linux_v58_cmd_media_im.php](../src/linux/linux_v58_cmd_media_im.php) |
| `linux_v58_cmd_media_tools.php` | [src/linux/linux_v58_cmd_media_tools.php](../src/linux/linux_v58_cmd_media_tools.php) |
| `linux_v58_cmd_ssh.php` | [src/linux/linux_v58_cmd_ssh.php](../src/linux/linux_v58_cmd_ssh.php) |
| `linux_v58_cmd_tldr_a.php` | [src/linux/linux_v58_cmd_tldr_a.php](../src/linux/linux_v58_cmd_tldr_a.php) |
| `linux_v58_cmd_tldr_b.php` | [src/linux/linux_v58_cmd_tldr_b.php](../src/linux/linux_v58_cmd_tldr_b.php) |
| `linux_v58_cmd_tldr_c.php` | [src/linux/linux_v58_cmd_tldr_c.php](../src/linux/linux_v58_cmd_tldr_c.php) |
| `linux_v58_cmd_users.php` | [src/linux/linux_v58_cmd_users.php](../src/linux/linux_v58_cmd_users.php) |
| `linux_v58_ext.php` | [src/linux/linux_v58_ext.php](../src/linux/linux_v58_ext.php) |
| `linux_v58_levels_archives.php` | [src/linux/linux_v58_levels_archives.php](../src/linux/linux_v58_levels_archives.php) |
| `linux_v58_levels_cron.php` | [src/linux/linux_v58_levels_cron.php](../src/linux/linux_v58_levels_cron.php) |
| `linux_v58_levels_ctf.php` | [src/linux/linux_v58_levels_ctf.php](../src/linux/linux_v58_levels_ctf.php) |
| `linux_v58_levels_generators.php` | [src/linux/linux_v58_levels_generators.php](../src/linux/linux_v58_levels_generators.php) |
| `linux_v58_levels_git.php` | [src/linux/linux_v58_levels_git.php](../src/linux/linux_v58_levels_git.php) |
| `linux_v58_levels_grafika.php` | [src/linux/linux_v58_levels_grafika.php](../src/linux/linux_v58_levels_grafika.php) |
| `linux_v58_levels_incident.php` | [src/linux/linux_v58_levels_incident.php](../src/linux/linux_v58_levels_incident.php) |
| `linux_v58_levels_keys.php` | [src/linux/linux_v58_levels_keys.php](../src/linux/linux_v58_levels_keys.php) |
| `linux_v58_levels_review.php` | [src/linux/linux_v58_levels_review.php](../src/linux/linux_v58_levels_review.php) |
| `linux_v58_levels_tg.php` | [src/linux/linux_v58_levels_tg.php](../src/linux/linux_v58_levels_tg.php) |
| `linux_v58_levels_users.php` | [src/linux/linux_v58_levels_users.php](../src/linux/linux_v58_levels_users.php) |
| `linux_v58_levels_weekly.php` | [src/linux/linux_v58_levels_weekly.php](../src/linux/linux_v58_levels_weekly.php) |
| `marketplace_v60.php` | [src/student/marketplace_v60.php](../src/student/marketplace_v60.php) |
| `marketplace_v60_teacher_views.php` | [src/student/marketplace_v60_teacher_views.php](../src/student/marketplace_v60_teacher_views.php) |
| `marketplace_v60_views.php` | [src/student/marketplace_v60_views.php](../src/student/marketplace_v60_views.php) |
| `marketplace_v61.php` | [src/student/marketplace_v61.php](../src/student/marketplace_v61.php) |
| `marketplace_v61_views.php` | [src/student/marketplace_v61_views.php](../src/student/marketplace_v61_views.php) |
| `mastery_learning_v41.php` | [src/lessons/mastery_learning_v41.php](../src/lessons/mastery_learning_v41.php) |
| `mastery_learning_views_v41.php` | [src/lessons/mastery_learning_views_v41.php](../src/lessons/mastery_learning_views_v41.php) |
| `mastery_v62.php` | [src/assessment/mastery_v62.php](../src/assessment/mastery_v62.php) |
| `materials_v74.php` | [src/ops/materials_v74.php](../src/ops/materials_v74.php) |
| `materials_v74_views.php` | [src/ops/materials_v74_views.php](../src/ops/materials_v74_views.php) |
| `modules.php` | [src/content/modules.php](../src/content/modules.php) |
| `morning_v66.php` | [src/teacher/morning_v66.php](../src/teacher/morning_v66.php) |
| `motivation_v61.php` | [src/student/motivation_v61.php](../src/student/motivation_v61.php) |
| `motivation_v61_views.php` | [src/student/motivation_v61_views.php](../src/student/motivation_v61_views.php) |
| `nav_v61.php` | [src/ui/nav_v61.php](../src/ui/nav_v61.php) |
| `nav_v78_1.php` | [src/ui/nav_v78_1.php](../src/ui/nav_v78_1.php) |
| `nav_v78_1_views.php` | [src/ui/nav_v78_1_views.php](../src/ui/nav_v78_1_views.php) |
| `next_lessons.php` | [src/content/next_lessons.php](../src/content/next_lessons.php) |
| `observation_v76.php` | [src/teacher/observation_v76.php](../src/teacher/observation_v76.php) |
| `ops_v58.php` | [src/ops/ops_v58.php](../src/ops/ops_v58.php) |
| `ops_v58_views.php` | [src/ops/ops_v58_views.php](../src/ops/ops_v58_views.php) |
| `ops_v61.php` | [src/ops/ops_v61.php](../src/ops/ops_v61.php) |
| `ops_v67.php` | [src/ops/ops_v67.php](../src/ops/ops_v67.php) |
| `palette_v74.php` | [src/ui/palette_v74.php](../src/ui/palette_v74.php) |
| `paths_v63.php` | [src/paths/paths_v63.php](../src/paths/paths_v63.php) |
| `paths_v63_actions.php` | [src/paths/paths_v63_actions.php](../src/paths/paths_v63_actions.php) |
| `paths_v63_class.php` | [src/paths/paths_v63_class.php](../src/paths/paths_v63_class.php) |
| `paths_v63_content_gfx.php` | [src/paths/paths_v63_content_gfx.php](../src/paths/paths_v63_content_gfx.php) |
| `paths_v63_content_os.php` | [src/paths/paths_v63_content_os.php](../src/paths/paths_v63_content_os.php) |
| `paths_v63_flow.php` | [src/paths/paths_v63_flow.php](../src/paths/paths_v63_flow.php) |
| `paths_v63_pre_data.php` | [src/paths/paths_v63_pre_data.php](../src/paths/paths_v63_pre_data.php) |
| `paths_v63_teacher_views.php` | [src/paths/paths_v63_teacher_views.php](../src/paths/paths_v63_teacher_views.php) |
| `paths_v63_views.php` | [src/paths/paths_v63_views.php](../src/paths/paths_v63_views.php) |
| `paths_v75_bank.php` | [src/paths/paths_v75_bank.php](../src/paths/paths_v75_bank.php) |
| `paths_v75_content_gfx2.php` | [src/paths/paths_v75_content_gfx2.php](../src/paths/paths_v75_content_gfx2.php) |
| `paths_v75_content_ops.php` | [src/paths/paths_v75_content_ops.php](../src/paths/paths_v75_content_ops.php) |
| `paths_v76_content_gfx.php` | [src/paths/paths_v76_content_gfx.php](../src/paths/paths_v76_content_gfx.php) |
| `paths_v76_content_os.php` | [src/paths/paths_v76_content_os.php](../src/paths/paths_v76_content_os.php) |
| `perf_v67.php` | [src/ops/perf_v67.php](../src/ops/perf_v67.php) |
| `plan_v76.php` | [src/ops/plan_v76.php](../src/ops/plan_v76.php) |
| `plan_v76_views.php` | [src/ops/plan_v76_views.php](../src/ops/plan_v76_views.php) |
| `points_v53.php` | [src/student/points_v53.php](../src/student/points_v53.php) |
| `points_v60.php` | [src/student/points_v60.php](../src/student/points_v60.php) |
| `portfolio_v65.php` | [src/student/portfolio_v65.php](../src/student/portfolio_v65.php) |
| `portfolio_v65_views.php` | [src/student/portfolio_v65_views.php](../src/student/portfolio_v65_views.php) |
| `portfolio_v75.php` | [src/student/portfolio_v75.php](../src/student/portfolio_v75.php) |
| `portfolio_v75_teacher_views.php` | [src/student/portfolio_v75_teacher_views.php](../src/student/portfolio_v75_teacher_views.php) |
| `portfolio_v75_views.php` | [src/student/portfolio_v75_views.php](../src/student/portfolio_v75_views.php) |
| `portfolio_v79.php` | [src/student/portfolio_v79.php](../src/student/portfolio_v79.php) |
| `priprava_v79.php` | [src/teacher/priprava_v79.php](../src/teacher/priprava_v79.php) |
| `profile_v60.php` | [src/student/profile_v60.php](../src/student/profile_v60.php) |
| `profile_v60_ui.php` | [src/student/profile_v60_ui.php](../src/student/profile_v60_ui.php) |
| `profile_v60_views.php` | [src/student/profile_v60_views.php](../src/student/profile_v60_views.php) |
| `progress_v80.php` | [src/student/progress_v80.php](../src/student/progress_v80.php) |
| `progress_v80_views.php` | [src/student/progress_v80_views.php](../src/student/progress_v80_views.php) |
| `project_assessments.php` | [src/assessment/project_assessments.php](../src/assessment/project_assessments.php) |
| `project_workspace.php` | [src/projects/project_workspace.php](../src/projects/project_workspace.php) |
| `project_workspace_views.php` | [src/projects/project_workspace_views.php](../src/projects/project_workspace_views.php) |
| `projects_v60.php` | [src/projects/projects_v60.php](../src/projects/projects_v60.php) |
| `projects_v60_teacher_views.php` | [src/projects/projects_v60_teacher_views.php](../src/projects/projects_v60_teacher_views.php) |
| `projects_v60_views.php` | [src/projects/projects_v60_views.php](../src/projects/projects_v60_views.php) |
| `projects_v65.php` | [src/projects/projects_v65.php](../src/projects/projects_v65.php) |
| `projects_v65_detail_views.php` | [src/projects/projects_v65_detail_views.php](../src/projects/projects_v65_detail_views.php) |
| `projects_v65_evidence.php` | [src/projects/projects_v65_evidence.php](../src/projects/projects_v65_evidence.php) |
| `projects_v65_peer.php` | [src/projects/projects_v65_peer.php](../src/projects/projects_v65_peer.php) |
| `projects_v65_rubrics.php` | [src/projects/projects_v65_rubrics.php](../src/projects/projects_v65_rubrics.php) |
| `projects_v65_teacher_actions.php` | [src/projects/projects_v65_teacher_actions.php](../src/projects/projects_v65_teacher_actions.php) |
| `projects_v65_teacher_views.php` | [src/projects/projects_v65_teacher_views.php](../src/projects/projects_v65_teacher_views.php) |
| `projects_v65_team.php` | [src/projects/projects_v65_team.php](../src/projects/projects_v65_team.php) |
| `projects_v65_views.php` | [src/projects/projects_v65_views.php](../src/projects/projects_v65_views.php) |
| `question_meta_v66.php` | [src/assessment/question_meta_v66.php](../src/assessment/question_meta_v66.php) |
| `reality_demos.php` | [src/content/reality_demos.php](../src/content/reality_demos.php) |
| `retention_v76.php` | [src/student/retention_v76.php](../src/student/retention_v76.php) |
| `review_v75.php` | [src/student/review_v75.php](../src/student/review_v75.php) |
| `review_v75_teacher_views.php` | [src/student/review_v75_teacher_views.php](../src/student/review_v75_teacher_views.php) |
| `review_v75_views.php` | [src/student/review_v75_views.php](../src/student/review_v75_views.php) |
| `review_v79.php` | [src/student/review_v79.php](../src/student/review_v79.php) |
| `robots_v58.php` | [src/robots/robots_v58.php](../src/robots/robots_v58.php) |
| `robots_v58_game.php` | [src/robots/robots_v58_game.php](../src/robots/robots_v58_game.php) |
| `robots_v58_lang.php` | [src/robots/robots_v58_lang.php](../src/robots/robots_v58_lang.php) |
| `robots_v58_views.php` | [src/robots/robots_v58_views.php](../src/robots/robots_v58_views.php) |
| `rollover_v67.php` | [src/ops/rollover_v67.php](../src/ops/rollover_v67.php) |
| `runtime_content.php` | [src/ops/runtime_content.php](../src/ops/runtime_content.php) |
| `school_year.php` | [src/ops/school_year.php](../src/ops/school_year.php) |
| `session_v53.php` | [src/lessons/session_v53.php](../src/lessons/session_v53.php) |
| `session_v53_teacher.php` | [src/lessons/session_v53_teacher.php](../src/lessons/session_v53_teacher.php) |
| `session_v53_views.php` | [src/lessons/session_v53_views.php](../src/lessons/session_v53_views.php) |
| `simulations.php` | [src/content/simulations.php](../src/content/simulations.php) |
| `simulations_ecosystem.php` | [src/content/simulations_ecosystem.php](../src/content/simulations_ecosystem.php) |
| `simulations_extra.php` | [src/content/simulations_extra.php](../src/content/simulations_extra.php) |
| `simulations_more.php` | [src/content/simulations_more.php](../src/content/simulations_more.php) |
| `simulations_plus.php` | [src/content/simulations_plus.php](../src/content/simulations_plus.php) |
| `simulations_yearpack.php` | [src/content/simulations_yearpack.php](../src/content/simulations_yearpack.php) |
| `skill_catalog.php` | [src/assessment/skill_catalog.php](../src/assessment/skill_catalog.php) |
| `skill_trees.php` | [src/assessment/skill_trees.php](../src/assessment/skill_trees.php) |
| `skill_views.php` | [src/assessment/skill_views.php](../src/assessment/skill_views.php) |
| `special_assessments.php` | [src/assessment/special_assessments.php](../src/assessment/special_assessments.php) |
| `stepper_v79.php` | [src/ui/stepper_v79.php](../src/ui/stepper_v79.php) |
| `stepper_v79_student.php` | [src/ui/stepper_v79_student.php](../src/ui/stepper_v79_student.php) |
| `stepper_v79_views.php` | [src/ui/stepper_v79_views.php](../src/ui/stepper_v79_views.php) |
| `storage_v58.php` | [src/ops/storage_v58.php](../src/ops/storage_v58.php) |
| `student_corrective_cycle_v47_2.php` | [src/student/student_corrective_cycle_v47_2.php](../src/student/student_corrective_cycle_v47_2.php) |
| `student_corrective_cycle_views_v47_2.php` | [src/student/student_corrective_cycle_views_v47_2.php](../src/student/student_corrective_cycle_views_v47_2.php) |
| `student_directory.php` | [src/student/student_directory.php](../src/student/student_directory.php) |
| `student_learning_accelerator_v47_1.php` | [src/student/student_learning_accelerator_v47_1.php](../src/student/student_learning_accelerator_v47_1.php) |
| `student_learning_accelerator_views_v47_1.php` | [src/student/student_learning_accelerator_views_v47_1.php](../src/student/student_learning_accelerator_views_v47_1.php) |
| `student_learning_coach_v47.php` | [src/student/student_learning_coach_v47.php](../src/student/student_learning_coach_v47.php) |
| `student_learning_coach_views_v47.php` | [src/student/student_learning_coach_views_v47.php](../src/student/student_learning_coach_views_v47.php) |
| `student_links_v69.php` | [src/student/student_links_v69.php](../src/student/student_links_v69.php) |
| `student_social_views.php` | [src/student/student_social_views.php](../src/student/student_social_views.php) |
| `student_v55.php` | [src/student/student_v55.php](../src/student/student_v55.php) |
| `student_v55_views.php` | [src/student/student_v55_views.php](../src/student/student_v55_views.php) |
| `support_v80_1.php` | [src/student/support_v80_1.php](../src/student/support_v80_1.php) |
| `support_v80_1_teacher.php` | [src/student/support_v80_1_teacher.php](../src/student/support_v80_1_teacher.php) |
| `support_v80_1_views.php` | [src/student/support_v80_1_views.php](../src/student/support_v80_1_views.php) |
| `svp_export_v76.php` | [src/assessment/svp_export_v76.php](../src/assessment/svp_export_v76.php) |
| `svp_export_v76_views.php` | [src/assessment/svp_export_v76_views.php](../src/assessment/svp_export_v76_views.php) |
| `svp_v72.php` | [src/assessment/svp_v72.php](../src/assessment/svp_v72.php) |
| `svp_v72_map.php` | [src/assessment/svp_v72_map.php](../src/assessment/svp_v72_map.php) |
| `svp_v72_views.php` | [src/assessment/svp_v72_views.php](../src/assessment/svp_v72_views.php) |
| `t14_v77.php` | [src/teacher/t14_v77.php](../src/teacher/t14_v77.php) |
| `t14_v77_views.php` | [src/teacher/t14_v77_views.php](../src/teacher/t14_v77_views.php) |
| `taskbank_v75.php` | [src/assessment/taskbank_v75.php](../src/assessment/taskbank_v75.php) |
| `taskbank_v75_gen.php` | [src/assessment/taskbank_v75_gen.php](../src/assessment/taskbank_v75_gen.php) |
| `taskbank_v75_teacher_views.php` | [src/assessment/taskbank_v75_teacher_views.php](../src/assessment/taskbank_v75_teacher_views.php) |
| `taskbank_v75_views.php` | [src/assessment/taskbank_v75_views.php](../src/assessment/taskbank_v75_views.php) |
| `taskbank_v76.php` | [src/assessment/taskbank_v76.php](../src/assessment/taskbank_v76.php) |
| `taskbank_v76_gen.php` | [src/assessment/taskbank_v76_gen.php](../src/assessment/taskbank_v76_gen.php) |
| `teacher_accounts_v59.php` | [src/teacher/teacher_accounts_v59.php](../src/teacher/teacher_accounts_v59.php) |
| `teacher_accounts_v59_admin.php` | [src/teacher/teacher_accounts_v59_admin.php](../src/teacher/teacher_accounts_v59_admin.php) |
| `teacher_accounts_v59_limits.php` | [src/teacher/teacher_accounts_v59_limits.php](../src/teacher/teacher_accounts_v59_limits.php) |
| `teacher_accounts_v59_views.php` | [src/teacher/teacher_accounts_v59_views.php](../src/teacher/teacher_accounts_v59_views.php) |
| `teacher_calendar.php` | [src/teacher/teacher_calendar.php](../src/teacher/teacher_calendar.php) |
| `teacher_class_dashboard.php` | [src/teacher/teacher_class_dashboard.php](../src/teacher/teacher_class_dashboard.php) |
| `teacher_curriculum.php` | [src/teacher/teacher_curriculum.php](../src/teacher/teacher_curriculum.php) |
| `teacher_demo_accounts.php` | [src/teacher/teacher_demo_accounts.php](../src/teacher/teacher_demo_accounts.php) |
| `teacher_lesson_mode.php` | [src/teacher/teacher_lesson_mode.php](../src/teacher/teacher_lesson_mode.php) |
| `teacher_nav_v68.php` | [src/teacher/teacher_nav_v68.php](../src/teacher/teacher_nav_v68.php) |
| `teacher_operations_control_v46_2.php` | [src/teacher/teacher_operations_control_v46_2.php](../src/teacher/teacher_operations_control_v46_2.php) |
| `teacher_operations_control_views_v46_2.php` | [src/teacher/teacher_operations_control_views_v46_2.php](../src/teacher/teacher_operations_control_views_v46_2.php) |
| `teacher_operations_plus_v46_1.php` | [src/teacher/teacher_operations_plus_v46_1.php](../src/teacher/teacher_operations_plus_v46_1.php) |
| `teacher_operations_plus_views_v46_1.php` | [src/teacher/teacher_operations_plus_views_v46_1.php](../src/teacher/teacher_operations_plus_views_v46_1.php) |
| `teacher_operations_v46.php` | [src/teacher/teacher_operations_v46.php](../src/teacher/teacher_operations_v46.php) |
| `teacher_operations_views_v46.php` | [src/teacher/teacher_operations_views_v46.php](../src/teacher/teacher_operations_views_v46.php) |
| `teacher_overview_dashboard.php` | [src/teacher/teacher_overview_dashboard.php](../src/teacher/teacher_overview_dashboard.php) |
| `teacher_overview_v61.php` | [src/teacher/teacher_overview_v61.php](../src/teacher/teacher_overview_v61.php) |
| `teacher_overview_v61_views.php` | [src/teacher/teacher_overview_v61_views.php](../src/teacher/teacher_overview_v61_views.php) |
| `teacher_prefs_v72_1.php` | [src/teacher/teacher_prefs_v72_1.php](../src/teacher/teacher_prefs_v72_1.php) |
| `teacher_prefs_v72_1_views.php` | [src/teacher/teacher_prefs_v72_1_views.php](../src/teacher/teacher_prefs_v72_1_views.php) |
| `teacher_project_workspace_views.php` | [src/teacher/teacher_project_workspace_views.php](../src/teacher/teacher_project_workspace_views.php) |
| `teacher_scope_v59.php` | [src/teacher/teacher_scope_v59.php](../src/teacher/teacher_scope_v59.php) |
| `teacher_shell_v68.php` | [src/teacher/teacher_shell_v68.php](../src/teacher/teacher_shell_v68.php) |
| `teacher_tasks.php` | [src/teacher/teacher_tasks.php](../src/teacher/teacher_tasks.php) |
| `teacher_v58.php` | [src/teacher/teacher_v58.php](../src/teacher/teacher_v58.php) |
| `teamgames_v58_bank_gfx.php` | [src/teamgames/teamgames_v58_bank_gfx.php](../src/teamgames/teamgames_v58_bank_gfx.php) |
| `teamgames_v58_bank_gfx_a11y.php` | [src/teamgames/teamgames_v58_bank_gfx_a11y.php](../src/teamgames/teamgames_v58_bank_gfx_a11y.php) |
| `teamgames_v58_bank_gfx_color.php` | [src/teamgames/teamgames_v58_bank_gfx_color.php](../src/teamgames/teamgames_v58_bank_gfx_color.php) |
| `teamgames_v58_bank_gfx_css.php` | [src/teamgames/teamgames_v58_bank_gfx_css.php](../src/teamgames/teamgames_v58_bank_gfx_css.php) |
| `teamgames_v58_bank_gfx_formats.php` | [src/teamgames/teamgames_v58_bank_gfx_formats.php](../src/teamgames/teamgames_v58_bank_gfx_formats.php) |
| `teamgames_v58_bank_gfx_html.php` | [src/teamgames/teamgames_v58_bank_gfx_html.php](../src/teamgames/teamgames_v58_bank_gfx_html.php) |
| `teamgames_v58_bank_gfx_license.php` | [src/teamgames/teamgames_v58_bank_gfx_license.php](../src/teamgames/teamgames_v58_bank_gfx_license.php) |
| `teamgames_v58_bank_gfx_type.php` | [src/teamgames/teamgames_v58_bank_gfx_type.php](../src/teamgames/teamgames_v58_bank_gfx_type.php) |
| `teamgames_v58_bank_gfx_ux.php` | [src/teamgames/teamgames_v58_bank_gfx_ux.php](../src/teamgames/teamgames_v58_bank_gfx_ux.php) |
| `teamgames_v58_bank_net.php` | [src/teamgames/teamgames_v58_bank_net.php](../src/teamgames/teamgames_v58_bank_net.php) |
| `teamgames_v58_bank_net_dns.php` | [src/teamgames/teamgames_v58_bank_net_dns.php](../src/teamgames/teamgames_v58_bank_net_dns.php) |
| `teamgames_v58_bank_net_files.php` | [src/teamgames/teamgames_v58_bank_net_files.php](../src/teamgames/teamgames_v58_bank_net_files.php) |
| `teamgames_v58_bank_net_hw.php` | [src/teamgames/teamgames_v58_bank_net_hw.php](../src/teamgames/teamgames_v58_bank_net_hw.php) |
| `teamgames_v58_bank_net_ip.php` | [src/teamgames/teamgames_v58_bank_net_ip.php](../src/teamgames/teamgames_v58_bank_net_ip.php) |
| `teamgames_v58_bank_net_linux.php` | [src/teamgames/teamgames_v58_bank_net_linux.php](../src/teamgames/teamgames_v58_bank_net_linux.php) |
| `teamgames_v58_bank_net_ports.php` | [src/teamgames/teamgames_v58_bank_net_ports.php](../src/teamgames/teamgames_v58_bank_net_ports.php) |
| `teamgames_v58_bank_net_procs.php` | [src/teamgames/teamgames_v58_bank_net_procs.php](../src/teamgames/teamgames_v58_bank_net_procs.php) |
| `teamgames_v58_bank_net_security.php` | [src/teamgames/teamgames_v58_bank_net_security.php](../src/teamgames/teamgames_v58_bank_net_security.php) |
| `teamgames_v58_core.php` | [src/teamgames/teamgames_v58_core.php](../src/teamgames/teamgames_v58_core.php) |
| `teamgames_v58_game_bingo.php` | [src/teamgames/teamgames_v58_game_bingo.php](../src/teamgames/teamgames_v58_game_bingo.php) |
| `teamgames_v58_game_escape.php` | [src/teamgames/teamgames_v58_game_escape.php](../src/teamgames/teamgames_v58_game_escape.php) |
| `teamgames_v58_game_jeopardy.php` | [src/teamgames/teamgames_v58_game_jeopardy.php](../src/teamgames/teamgames_v58_game_jeopardy.php) |
| `teamgames_v58_game_netadmin.php` | [src/teamgames/teamgames_v58_game_netadmin.php](../src/teamgames/teamgames_v58_game_netadmin.php) |
| `teamgames_v58_game_relay.php` | [src/teamgames/teamgames_v58_game_relay.php](../src/teamgames/teamgames_v58_game_relay.php) |
| `teamgames_v58_game_tug.php` | [src/teamgames/teamgames_v58_game_tug.php](../src/teamgames/teamgames_v58_game_tug.php) |
| `teamgames_v58_projector_views.php` | [src/teamgames/teamgames_v58_projector_views.php](../src/teamgames/teamgames_v58_projector_views.php) |
| `teamgames_v58_quiz.php` | [src/teamgames/teamgames_v58_quiz.php](../src/teamgames/teamgames_v58_quiz.php) |
| `teamgames_v58_registry.php` | [src/teamgames/teamgames_v58_registry.php](../src/teamgames/teamgames_v58_registry.php) |
| `teamgames_v58_teacher_views.php` | [src/teamgames/teamgames_v58_teacher_views.php](../src/teamgames/teamgames_v58_teacher_views.php) |
| `teamgames_v58_views.php` | [src/teamgames/teamgames_v58_views.php](../src/teamgames/teamgames_v58_views.php) |
| `teamgames_v64_roles.php` | [src/teamgames/teamgames_v64_roles.php](../src/teamgames/teamgames_v64_roles.php) |
| `teamgames_v64_views.php` | [src/teamgames/teamgames_v64_views.php](../src/teamgames/teamgames_v64_views.php) |
| `tutorial_v52.php` | [src/lessons/tutorial_v52.php](../src/lessons/tutorial_v52.php) |
| `tutorial_v52_teacher.php` | [src/lessons/tutorial_v52_teacher.php](../src/lessons/tutorial_v52_teacher.php) |
| `tutorial_v52_views.php` | [src/lessons/tutorial_v52_views.php](../src/lessons/tutorial_v52_views.php) |
| `ui_v67.php` | [src/ui/ui_v67.php](../src/ui/ui_v67.php) |
| `ui_v81.php` | [src/ui/ui_v81.php](../src/ui/ui_v81.php) |
| `unified_page_shell_v50_7.php` | [src/ui/unified_page_shell_v50_7.php](../src/ui/unified_page_shell_v50_7.php) |
| `ux_v79_1.php` | [src/ui/ux_v79_1.php](../src/ui/ux_v79_1.php) |
| `visual_labs_3a_v48_1.php` | [src/lessons/visual_labs_3a_v48_1.php](../src/lessons/visual_labs_3a_v48_1.php) |
| `visual_labs_3a_views_v48_1.php` | [src/lessons/visual_labs_3a_views_v48_1.php](../src/lessons/visual_labs_3a_views_v48_1.php) |
| `visual_practical_learning_v48.php` | [src/lessons/visual_practical_learning_v48.php](../src/lessons/visual_practical_learning_v48.php) |
| `visual_practical_learning_views_v48.php` | [src/lessons/visual_practical_learning_views_v48.php](../src/lessons/visual_practical_learning_views_v48.php) |
| `visual_simulation_v45.php` | [src/lessons/visual_simulation_v45.php](../src/lessons/visual_simulation_v45.php) |
| `visual_simulation_views_v45.php` | [src/lessons/visual_simulation_views_v45.php](../src/lessons/visual_simulation_views_v45.php) |
| `weekly_v74.php` | [src/teacher/weekly_v74.php](../src/teacher/weekly_v74.php) |
| `weekly_v74_views.php` | [src/teacher/weekly_v74_views.php](../src/teacher/weekly_v74_views.php) |
| `widgets_v80.php` | [src/ui/widgets_v80.php](../src/ui/widgets_v80.php) |
| `widgets_v80_views.php` | [src/ui/widgets_v80_views.php](../src/ui/widgets_v80_views.php) |
| `zero_friction_v50_6.php` | [src/student/zero_friction_v50_6.php](../src/student/zero_friction_v50_6.php) |
