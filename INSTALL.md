# EDUCANET v30 – instalace a přístup učitele

## Požadavky

- PHP 8.1+ (doporučeno PHP 8.2/8.3)
- Apache/Nginx nebo PHP built-in server pro lokální ověření
- zapisovatelný `storage/`
- zapisovatelný `uploads/`, pokud se používají studentské uploady

## 1. Učitelský / administrátorský cockpit

Teacher/Admin rozhraní je dostupné na:

```text
/teacher.php
```

V ZIPu **není žádné výchozí statické heslo**. Před spuštěním nastav na serveru silný tajný klíč:

```bash
export EDUCANET_TEACHER_EXPORT_KEY='SEM_VLOZ_VLASTNI_DLOUHY_NAHODNY_KLIC'
```

Při PHP-FPM / aaPanelu nastav stejnou proměnnou v prostředí daného PHP poolu/webu a restartuj/reloadni PHP-FPM. Přihlašovací formulář používá:

- **Jméno učitele** – auditní/display jméno, např. `Adrian Staněk`
- **Učitelský klíč** – přesná hodnota `EDUCANET_TEACHER_EXPORT_KEY`

Pokud klíč není nakonfigurovaný, `teacher.php` záměrně odmítne přístup stavem 503.

## 2. Lokální spuštění

```bash
EDUCANET_TEACHER_EXPORT_KEY='testovaci-lokalni-klic' php -S 127.0.0.1:8080
```

Student: `http://127.0.0.1:8080/`  
Učitel: `http://127.0.0.1:8080/teacher.php`

## 3. Aktuální kurikulum v30

- 1.A – Grafika a webdesign · základy: 28 strukturovaných lekcí
- 2.A – Grafika a webdesign · UI/UX: 28 strukturovaných lekcí
- 3.A – Seminář OS a počítačových sítí: 28 strukturovaných lekcí
- 4.A – Seminář OS a počítačových sítí: 28 strukturovaných lekcí
- 40 plánovaných výukových střed na třídu
- 12 aplikačních/projektových/mastery bloků po lekci 28
- 132 Knowledge Base materiálů
- Adaptive Help Ladder + animované konceptové demo + alternativní vysvětlení
- Skill Trees + Mastery + Project Workspace + Teacher Global Overview

## 4. Školní rok

Datový plán je v `school_year.php`. Student má pohled `Kalendář`, učitel společný kalendář 1.A–4.A. Pokud odpadne nepředvídaná středa (ředitelské volno, exkurze, mimořádná akce), nepřeskakuj guided practice – posuň obsah a využij nejbližší aplikační/rezervní blok.

## 5. Materiály

V `materials/` jsou pro každou třídu Course Map, Teacher Guide, Student Workbook, Assessment Bank, Project Briefs, Teacher Checklist a Lesson Readiness Checklist. Společně navíc:

- `ADAPTIVE_EXPLANATION_GUIDE.md`
- `school_year/SCHOOL_YEAR_2026_2027.md`
- `TEACHER_OVERVIEW.md`
- `ASSESSMENT_MASTERY_MATRIX.md`
- `PROJECT_ROLE_GUIDE.md`
- `CURRICULUM_OVERVIEW.csv`

Po změně kurikula:

```bash
php tools/generate_teacher_curriculum_materials.php
php tools/generate_v30_learning_materials.php
```

## 6. Povinné kontroly před produkcí

```bash
php tools/content_audit.php
php tools/learning_flow_audit.php
php tools/assessment_visual_audit.php
php tools/kb_visual_demo_audit.php
php tools/project_grading_audit.php
php tools/gamification_audit.php
php tests/skill_tree_audit.php
php tools/project_workspace_audit.php
php tools/teacher_curriculum_audit.php
php tools/teacher_overview_audit.php
```

`tools/v30_adaptive_calendar_audit.php` byl ve v59 vyřazen mimo projekt (přesunut do
`C:\Users\medion\Desktop\educanet-retired\v59-F4\tools\legacy\` – testoval starší tvar adaptivního
kalendáře, který dnes neodpovídá `school_year.php`, a bez `EDUCANET_STORAGE_DIR` navíc sahal na
ostrá data). Od v59 doporučujeme místo ručního výčtu spouštět `php tools/run_audits.php --since=all`
(viz `## v59` níže) – zahrnuje všechny audity výše **kromě** `tests/skill_tree_audit.php`, který
zůstává mimo `run_audits.php` záměrně (umí při chybě smazat data) a spouští se ručně, vždy jen po
záloze `storage/`.

## 7. Datová vrstva

Aktuální build používá uzamčený JSON storage kompatibilní s prototypem. Referenční MySQL 8 schémata jsou v `database/`. Tajné přístupové klíče nikdy neukládej do Git repozitáře, README ani studentského frontendu.

## v31 · doporučené secrets na aaPanelu

Na aaPanelu vytvoř `/www/server/educanet/educanet.secrets.php` podle `educanet.secrets.example.php`. V31 tuto cestu načítá automaticky. Reálný secret soubor nepatří do `/www/wwwroot`, ZIPu ani Gitu. Alternativně použij `EDUCANET_TEACHER_EXPORT_KEY`; environment má přednost. Podrobnosti jsou v `ADMIN_ACCESS.md`.

## v32 quick install · teacher secret

```bash
cd /www/wwwroot/TVUJ_WEB
bash tools/install_teacher_secret.sh
php tools/check_install.php
```

`private/educanet.secrets.php` je pouze template. Reálný secret instalátor zapisuje mimo veřejný web root do `/www/server/educanet/educanet.secrets.php`.


## v32.1 · ISPConfig + CLI hotfix

Pokud je aplikace nasazená v ISPConfig cestě jako `/var/www/clients/client10/web9/web/...`, instalační skript nyní automaticky uloží secret do `/var/www/clients/client10/web9/private/educanet.secrets.php`. `tools/check_install.php` je CLI-only nástroj; přes web už nespadne na `STDERR`, ale bezpečně zobrazí instrukci ke spuštění přes SSH.

## v33 · po aktualizaci

Po nahrání v33 doporučujeme:

```bash
cd /var/www/clients/client10/web9/web/sub/w
php tools/build_runtime_cache.php
php tools/check_install.php
php tools/performance_check.php
```

Reálný středeční rozvrh je zabudovaný v `school_year.php` a student vidí pouze vlastní třídu.

Testovací studentský účet vytvoříš například:

```bash
php tools/create_test_student.php --class=class_1a --email=demo.student@educanet.cz --name='Testovací student'
```

Nástroj vygeneruje náhodné heslo a vypíše jej pouze v CLI.

## v43 · Cognitive Visualization

Po nasazení v43 doporučujeme jednorázově spustit:

```bash
php tools/build_runtime_cache.php
php tools/generate_v43_cognitive_labs.php
php tools/v43_cognitive_visualization_audit.php
php tools/v43_performance_accessibility_audit.php
```

Laboratoře nevyžadují externí službu ani WebGL. Všechna data studentů zůstávají ve stávajícím `storage/`; upgrade patch tento adresář neobsahuje.


## v45 · Visual Simulation Engine

Po upgrade z v44 spusť:

```bash
cd /var/www/clients/client10/web9/web/sub/w
php tools/build_runtime_cache.php
php tools/generate_v45_simulation_specs.php
php tools/v45_visual_simulation_audit.php
php tools/v45_performance_accessibility_audit.php
php tools/check_install.php
```

Potom reloadni PHP 8.3 FPM / OPcache. Simulační snapshoty se ukládají do `storage/adaptive_v45_sim_snapshots.json.php`; upgrade patch `storage/` neobsahuje.

## v51 · dotazník z V1 a aktivační kódy

- Složku `V1/` nahraj spolu s aplikací (nebo nastav `EDUCANET_V1_DATA_DIR=/cesta/k/V1/data`). Při prvním otevření `index.php` nebo `teacher.php` se odpovědi, plakáty a rozložení učeben automaticky převezmou do `storage/intake/`.
- `storage/intake/` musí být zapisovatelný pro PHP.
- Učitel: `teacher.php` → **Dotazník** → třída → **Aktivační kódy** → „Vytisknout kartičky“ nebo CSV. Kódy rozdej žákům z V1.
- Žák: úvodní stránka → „Aktivovat připravený účet“ → kód → školní e-mail a heslo. Aktivace kódem nahrazuje ověření e-mailu.
- Žáci 1.A (a noví žáci ostatních tříd) si vytvoří účet běžně; dotazník je pak jejich první krok.
- Kontrola: `php tools/v51_light_clarity_audit.php`

## v52 · Tutorial Mode

- Nahraj i složku `assets/vendor/gsap-3.15.0/` – animace se načítají z vlastního serveru, bez CDN.
- Žáci: Menu → **Kurz / Kalendář / Témata / Programy a zkratky**. Každá lekce se otevírá jako krokový tutoriál.
- Původní stránku lekce zobrazíš přidáním `&classic=1` do adresy.
- Body z interaktivních úkolů se ukládají do `storage/tutorial_v52_scores.json.php` (adresář `storage/` musí být zapisovatelný).
- Kontrola: `php tools/v52_tutorial_mode_audit.php`
- Učitel: `Výuka → Režim hodiny` obsahuje projekční ukázky lekce, interaktivní úkoly a jejich řešení (body se učiteli neukládají).

## v53 · účty, hodina s kódem a body

1. Účty žáků: `php tools/v53_provision_accounts.php` – založí `jmeno.prijmeni@educanet.cz` s heslem `demo001` a vypíše přehled. Po prvním přihlášení si žák musí nastavit vlastní heslo.
2. Učitelský přístup: `php tools/v53_provision_accounts.php --teacher --teacher_name="Jméno"` – vygeneruje klíč a uloží ho do secret souboru mimo web root.
3. Demo účty: `php tools/v53_provision_accounts.php --demo=class_2a` (nebo `class_4a`).
4. Zapomenuté heslo: `php tools/v53_provision_accounts.php --reset=jmeno.prijmeni@educanet.cz`.
5. Hodina: učitel → **Hodina** → Otevřít hodinu → promítnout kód. Žák: přihlásí se a zadá kód na `?view=join` (1.A vyplní jméno, e-mail a místo přímo v kódovém vstupu).
6. Kontrola: `php tools/v53_accounts_sessions_audit.php`

## v54 · firemní barvy, přihlášení a kalendář

1. Nasazení: nahrát `assets/brand-v54.css`, `assets/auth-v54.js`, upravené `index.php`, `teacher.php`, `bootstrap.php`, `tutorial_v52_views.php`, `assets/tutorial-v52.js`, `sw.js`.
2. Cache: service worker má nový namespace `educanet-v54-1-calendar`, asset verze `?v=54.1` – stačí jedno tvrdé načtení, staré cache se smažou samy.
3. Hesla: minimum je **6 znaků** (písmeno + číslice). Stávající hesla zůstávají platná, nové pravidlo platí pro registraci a změnu hesla.
4. Přihlašovací stránka: `?view=home` – záložky *Přihlášení* / *Nový účet*, karty *Mám kód hodiny* (`?view=join`) a *Mám aktivační kód* (`?view=activate`).
5. Kalendář: `?view=calendar` – měsíční mřížka pro třídu, klik na den sjede na detail hodiny.
6. Kontrola: `php tools/v54_brand_login_audit.php` (a pro jistotu v51–v53 audity).

## v55 · redesign studentské sekce

1. Nasazení: nahrát `student_v55.php`, `student_v55_views.php`, `assets/student-v55.css`, `assets/student-v55.js` a upravené `index.php`, `teacher.php`, `school_year.php`, `adaptive_learning.php`, `tutorial_v52_views.php`, `session_v53.php`, `session_v53_teacher.php`, `student_social_views.php`, `sw.js`.
2. Nové úložiště: `storage/bonus_v55.json.php` se vytvoří samo při prvním spuštění bonusu (práva zápisu jako u ostatních souborů ve `storage/`).
3. Posloupnost lekcí: kalendář počítá první lekci od 16. 9. 2026. Pokud upravíte `school_year.php`, hlídejte, že se fronta obsahu vejde do dostupných střed (audit to kontroluje).
4. Kontrola: `php tools/v55_student_experience_audit.php` (a pro jistotu v51–v54 audity).
5. Cache: service worker má namespace `educanet-v55-student`, assety `?v=55.0`.

## v56 · jedna cesta učení

1. Nasazení: nahrát `learning_v56.php`, `learning_v56_views.php`, `assets/learning-v56.css`, `assets/learning-v56.js` a upravené `index.php`, `student_v55.php`, `student_v55_views.php`, `student_social_views.php`, `session_v53.php`, `sw.js`.
2. Nové úložiště: `storage/progress_v56.json.php` se vytvoří samo (práva zápisu jako ostatní soubory ve `storage/`).
3. Adresy: lekce `?view=lekce&n=<číslo>&faze=theory|test|project|submit`, materiály `?view=materialy&sekce=lekce|temata|programy`, výsledky `?view=vysledky`. Staré `?view=course|topics|tools|knowledgebase` přesměrovávají do materiálů.
4. Po změně obsahu lekcí spusť znovu synchronizaci dnešních hodin, aby zadání učitele odpovídalo krokům projektu.
5. Kontrola: `php tools/v56_learning_path_audit.php` (a pro jistotu v51–v55 audity).

## v57 – Linux Lab a Aréna

1. **Požadavky:** žádná nová PHP rozšíření – stačí dosavadní `mbstring` a `json`. Žádná externí služba, CDN ani síťové spojení; Linux Lab je čistě simulace v PHP.
2. **Nasazení:** nahrát `linux_v57_*.php` (core, world, shell, cmd_files, cmd_shell, cmd_text, cmd_sys, cmd_net, manual, levels, levels_ops, lab, views), `lab_v57_api.php`, `arena_v57.php`, `arena_v57_views.php`, `assets/linux-v57.{css,js}`, `assets/arena-v57.{css,js}`, `tools/v57_linux_lab_audit.php`, `tools/v57_arena_audit.php` a upravené `index.php`, `teacher.php`, `student_v55.php`, `bootstrap.php`, `sw.js` (přesný seznam v `BUILD_MANIFEST_V57.md`).
3. **Nové úložiště** (vytvoří se samo, práva zápisu jako ostatní soubory ve `storage/`):
   - `storage/linux_v57/` – stav labu po žácích (`<třída>__<sha1>.json.php`),
   - `storage/lab_v57_events.json.php` – události labu a závodů,
   - `storage/lab_v57_secret.json.php` – tajemství pro kódy úloh (**nemazat během školního roku**, jinak se změní kódy rozpracovaných úloh),
   - `storage/arena_v57.json.php` – závody a zapnutí labu po třídách.
4. **Adresy:** žák `?view=lab`, úroveň `?view=lab&uroven=<id>`, závod `?view=lab&zavod=<id>`, příručka `?view=prikazy` a `?view=prikazy&c=<příkaz>`; API `lab_v57_api.php` (jen POST + CSRF); učitel `teacher.php?tab=arena`.
5. **Webový server:**
   - Apache: `storage/` a `uploads/` mají vlastní `.htaccess`; ve v57 přibyly `.htaccess` s `Require all denied` pro `private/`, `database/`, `tools/`, `tests/`. Vyžaduje `AllowOverride All` (nebo stejná pravidla ve vhostu).
   - Nginx `.htaccess` nečte – do bloku `server` doplň:

     ```nginx
     location ^~ /storage/  { deny all; }
     location ^~ /private/  { deny all; }
     location ^~ /database/ { deny all; }
     location ^~ /tools/    { deny all; }
     location ^~ /tests/    { deny all; }
     location ^~ /V1/data/  { deny all; }
     location ^~ /uploads/  { location ~ \.(php|phtml|phar|php[0-9]*)$ { deny all; } }
     ```

     Pokud je aplikace v podadresáři, přidej jeho prefix (např. `/sub/w/storage/`). Ověření: `curl -I https://<web>/storage/points_v53.json.php` → 403.
6. **Kontrola po nasazení:**

   ```bash
   php tools/v57_linux_lab_audit.php
   php tools/v57_arena_audit.php
   php tools/v56_learning_path_audit.php   # a pro jistotu v51–v55 audity
   ```

   Audit labu pracuje v dočasném adresáři a ostrou `storage/` nemění (u auditu Arény ověř totéž v `BUILD_MANIFEST_V57.md`). Před spuštěním starších testů (`tests/skill_tree_audit.php`) vždy zálohuj `storage/`.
7. **Kontejnerový broker `lab-runtime/` (z v50) musí zůstat vypnutý:** nenastavuj `EDUCANET_LAB_BROKER_URL` ani `EDUCANET_LAB_BROKER_TOKEN` a službu `broker.py` nespouštěj. Linux Lab v57 ho nepoužívá; skutečné spouštění příkazů pro žáky není pro výuku potřeba a přináší zbytečné riziko (viz `AUDIT_V57.md`, kap. 6).
8. **Třída bez labu:** učitel může Linux Lab pro třídu vypnout v záložce **Aréna**.
9. **Cache:** assety `?v=57.0`; namespace service workeru podle `BUILD_MANIFEST_V57.md` – stačí jedno tvrdé načtení.

## v58 · jednorázová hesla, rozšířený Linux Lab, soutěže, provoz a identita

Přesný seznam nových/upravených souborů: `BUILD_MANIFEST_V58.md`. Uživatelský popis: `CHANGELOG_V58.md`.

1. **Nasazení souborů:** nahraj všechny nové soubory z `BUILD_MANIFEST_V58.md` (oddíl 1) – mj. `accounts_v58*.php`,
   `ops_v58*.php`, `storage_v58.php`, `identity_v58*.php`, `linux_v58_*.php`, `lab_v58_*.php`, `arena_v58_*.php`,
   `robots_v58*.php`, `teamgames_v58_*.php`, `i18n_v58.php`, `teacher_v58.php`, adresáře `app/`, `migrations/`,
   `lang/`, `tools/lib/`, nové `assets/*v58*`, `docs/*_V58.md`, `lab-offline.html` – a upravené sdílené soubory
   (`index.php`, `teacher.php`, `bootstrap.php`, `student_v55.php`, `sw.js`, `arena_v57*.php`, `learning_v56.php`,
   `teacher_operations_v46.php`, `accounts_v53.php`, `session_v53*.php`, `intake_v51*.php`, `linux_v57_*.php`,
   `one_task_v50_5.php`, `tests/skill_tree_audit.php`).
2. **Nové úložiště** (vytvoří se samo, práva zápisu jako u zbytku `storage/`): registr identity, log příkazů
   labu, události nových soutěží a her, zápasy Robotí ligy, kvízová banka, schéma a log migrací – úplný seznam
   cest je v `BUILD_MANIFEST_V58.md` (oddíl 3). Zálohy míří **mimo web root** (viz bod 5).
3. **Webový server – nový adresář `app/`:**
   - Apache: `app/.htaccess` (`Require all denied`) je součástí nasazení – ověř, že `AllowOverride All` platí
     i pro tento adresář (stejně jako u `private/`, `database/`, `tools/`, `tests/` z v57).
   - Nginx – do bloku `server`, **před** obecné zpracování `.php`, přidej vedle pravidel z v57:

     ```nginx
     location ^~ /app/ { deny all; }
     ```

   - **Interní soubory (OPS58-14):** `docs/`, `lab-runtime/` a `.claude/` mají vlastní `.htaccess`
     (`Require all denied`). Kořenový `.htaccess` projekt nemá – pokud ho na serveru používáš, přidej do něj
     zákaz Markdownu (README, CHANGELOG, AUDIT, manifesty prozrazují strukturu a verze):

     ```apache
     <FilesMatch "\.md$">
         Require all denied
     </FilesMatch>
     ```

     Nginx (do bloku `server`, před zpracování `.php`):

     ```nginx
     location ~* \.md$ { deny all; }
     location ^~ /docs/ { deny all; }
     location ^~ /lab-runtime/ { deny all; }
     location ^~ /.claude/ { deny all; }
     ```

     Ověření: `curl -I https://<web>/INSTALL.md` a `curl -I https://<web>/docs/V58_PLAN.md` → 403.
   - `tools/` a `storage/` zůstávají nepřístupné z webu podle pravidel z v57 (`deny all`/`Require all denied`);
     v58 do nich nic nepřidává, co by vyžadovalo výjimku. Ověření: `curl -I https://<web>/app/lib.php` → 403.
4. **Adresy (nové):** žák `?view=roboti`, `?view=hry`, `?view=hadanka`, `?view=ctf`, `?view=incident`,
   `?view=lab&sekce=dovednosti`, `?view=lab&sekce=opakovani`; učitel `teacher.php?tab=pristupy` (tisk
   `&print=1&class=…`), `?tab=provoz`, `?tab=identita`, `?tab=editor`, `?tab=labdata`, `?tab=roboti`,
   `?tab=hry`, `?tab=ctf`, `?tab=incidenty`, `?tab=arena&rezim=hadanka`, `?tab=arena&race=<id>&zaznam=1`;
   offline `lab-offline.html`. Úplná tabulka rout: `docs/V58_PLAN.md` (oddíl 2).
5. **Proměnné prostředí a secrets (nové):**
   - `EDUCANET_STORAGE_DIR` – přepíše umístění `storage/` (užitečné hlavně pro izolované testy/audity).
   - `EDUCANET_APP_URL` – veřejná adresa aplikace (`https://…`). **V produkci povinná pro e-maily** (SEC58-02):
     odkazy pro ověření e-mailu a obnovu hesla se skládají jen z ní – bez ní se e-mail s odkazem **neodešle**
     a do error logu se zapíše varování (hlavička Host je pod kontrolou klienta). Lokálně s
     `EDUCANET_DEV_BYPASS=1` se použije adresa z požadavku. Kartičky s jednorázovým heslem ji také vypisují
     (bez ní adresu odvodí z požadavku). Ověřovací odkaz už nenese e-mail – jen jednorázový token.
     E-maily s odkazem: nejvýš 3 za hodinu na jednu adresu (napříč IP) a 20 za hodinu z jedné IP.
   - `EDUCANET_ALLOW_FREE_CLASS_ENTRY` – ve výchozím stavu **vypnuto**. Volný vstup do třídy pouhým jménem
     (bez účtu/kódu hodiny) je jinak dostupný jen s `EDUCANET_DEV_BYPASS=1` (SEC-11); zapínej jen vědomě.
   - `EDUCANET_BACKUP_DIR` – cíl záloh **mimo web root** (výchozí `<nadřazená složka kořene aplikace>/educanet-backups`).
     Používají ho `tools/backup_storage.php`, `tools/restore_storage.php`, `tools/migrate.php`, `tools/v58_rollover.php`.
   - Volitelný secret `otp_card_key` (base64, 32 B) – zapiš do stejného secret souboru mimo web root, kam už
     patří `teacher_export_key` (viz `## v31`/`## v32`). Slouží jen k opakovanému zobrazení/tisku už vydaného
     jednorázového hesla. Bez něj aplikace klíč sama založí do `storage/accounts_v58_key.json.php`.
     **Doporučení (SEC58-10):** nastav `otp_card_key` v secret souboru mimo web root. Soubor
     `storage/accounts_v58_key.json.php` se do záloh (`tools/backup_storage.php`) **nezahrnuje** – klíč nesmí
     ležet ve stejné záloze jako šifrované kopie hesel – a `restore_storage.php --prune` ho nesmaže. Po obnově
     na jiný server bez tohoto klíče jen vydej a vytiskni nové kartičky (staré kopie hesel nepůjde zobrazit).
   - **Limity přihlášení (SEC58-12):** žák – 8 neúspěchů/15 min na e-mail + IP, 60/15 min na IP napříč účty,
     25/15 min na účet napříč IP. Spolužák za stejnou školní IP tak cizí účet zamkne nejvýš na 15 min a jen
     na té IP (globální limit účtu vyžaduje víc IP). Registrace kódem hodiny: 40 pokusů/15 min na IP a jen
     v otevřené seznamovací hodině. Učitel – kompromis: 20 neúspěchů/10 min na IP + otisk prohlížeče
     (User-Agent) a strop 200/10 min na IP. Žák za stejnou IP tak učitele snadno nezamkne; útočník sice může
     User-Agent měnit, ale učitelský klíč je dlouhý náhodný řetězec, takže hádání v tomto limitu nemá šanci.
     Hlášky při chybném přihlášení/registraci neprozrazují, jestli účet existuje.
6. **Postup nasazení (v pořadí):**

   ```bash
   php tools/backup_storage.php                          # 1) záloha storage/ mimo web root
   php tools/migrate.php --apply --backup-now             # 2) storage kernel – migrace proudů na JSONL (F2)
   php tools/v58_issue_passwords.php --migrate             # 3) sdílené heslo → jednorázová hesla (SEC-01)
   # 4) teacher.php → záložka Přístupy → vytisknout kartičky pro každou třídu
   php tools/run_audits.php --with-smoke                   # 5) kompletní kontrola (viz BUILD_MANIFEST_V58.md)
   ```

   Krok 2 vyžaduje zálohu storage/ mladší než 1 hodina (proto `--backup-now`, pokud žádná čerstvá není).
   Krok 3 lze nejdřív ověřit s `--dry-run` (jen spočítá, nic nezmění). Do kroku 5 patří i `php -l` na změněné
   soubory, pokud jsi cokoli upravoval ručně.
7. **Přechod školního roku** (kdykoli později, ne při prvním nasazení):

   ```bash
   php tools/backup_storage.php
   php tools/v58_rollover.php --dry-run --year=<rok>       # náhled: počty, kolize jmen, stav zálohy
   php tools/v58_rollover.php --apply   --year=<rok>       # provedení (nic nemaže, jen archivuje/přemapuje)
   ```

   Kolize jmen (jmenovci ve třídě) vyřeš předem přes `teacher.php?tab=identita` nebo `tools/v58_identity.php
   --split=…`/`--relabel=…` – nástroj bez vyřešené kolize a bez čerstvé zálohy odmítne (exit 2). Podrobnosti
   a rozhodnutí, která má probrat škola: `docs/IDENTITA_A_ROLLOVER_V58.md`.
8. **Retence dat:** `php tools/v58_retention.php` (náhled) / `--apply` (archivace podle školního roku).
   Výjimka (DAT58-03): **logy příkazů Linux Labu** (`storage/linux_v58/log/<třída>__<hash>/…`) jsou data
   nezletilých bez hodnoty archivu – po 30 dnech bez změny se **mažou** (`--apply` volá i `lab58_log_purge()`,
   který uklidí prázdné složky). Spouštěj **denně** z cronu, např.:

   ```cron
   15 3 * * * cd /cesta/k/educanet && php tools/v58_retention.php --apply >> /cesta/mimo/web/educanet-retention.log 2>&1
   ```
9. **Offline trénink a service worker:** `lab-offline.html` a jeho assety se cachují jako jediná statická
   (bezpečná – bez PHP, bez přihlášení, bez osobních dat) výjimka z pravidla „HTML se necachuje“ (SEC-15
   beze změny pro zbytek aplikace). Po nasazení stačí jedno tvrdé načtení, aby prohlížeč stáhl novou verzi
   service workeru (`sw.js`, cache `educanet-v58-offline-lab-asset-url`).
10. **Zvýšení verzí assetů:** nové soubory v58 se načítají s `?v=58.0`; při vlastní úpravě CSS/JS v58 zvyš
    verzi v odkazu, ať se u žáků/učitelů projeví ihned (stejný princip jako u předchozích vrstev).
11. **Kontrola po nasazení:**

    ```bash
    php tools/run_audits.php --with-smoke   # očekávaný konec: RUN_AUDITS_OK total=26 failed=0
    php tests/app_router_audit.php          # očekávaný konec: 30/30
    ```

    Oba běží nad dočasnou kopií úložiště (smoke test navíc potřebuje spuštěný lokální HTTP server – viz
    `tools/v58_smoke_audit.php`); ostrá `storage/` se nemění. Přesná čísla a rozpad podle vrstev:
    `BUILD_MANIFEST_V58.md`.

## v59 · Vícejazyčnost, učitelské účty a příprava produkce

Uživatelský popis: `CHANGELOG_V59.md`. Přesný seznam souborů a výjimky nad 800 řádků:
`BUILD_MANIFEST_V59.md`. Výsledky komplexního auditu: `AUDIT_V59.md`. Pro **první ostré nasazení na
produkční server nebo větší upgrade** postupuj podle podrobného průvodce `docs/NASAZENI_PRODUKCE.md`
(rozvržení adresářů, secrets mimo web root, zálohy/rollback) a vzorových konfigurací `docs/deploy/`
– tady je jen stručný přehled toho, co je ve v59 nové.

1. **Nasazení souborů:** nahraj nové soubory podle `BUILD_MANIFEST_V59.md` (oddíl 1) – mj.
   `teacher_accounts_v59*.php`, `teacher_scope_v59.php`, `i18n_v59.php`, `lang/domains_v59.php`,
   `lang/{en,uk}/ui/*.php`, `tools/preflight.php`, `tools/build_release.php`, `tools/http_smoke.php`,
   nové `tools/v59_*.php` audity, `docs/I18N_V59.md`, `docs/NASAZENI_PRODUKCE.md`, `docs/deploy/*`,
   nová `.htaccess` v kořeni/`lang/`/`migrations/`/`materials/` – a upravené sdílené soubory
   (`bootstrap.php`, `index.php`, `teacher.php`, `teacher_v58.php`, `teacher_operations_v46.php`,
   `student_v55.php`, `sw.js`, `i18n_v58.php` a soubory v doménách `lang/domains_v59.php`, viz
   manifest oddíl 2).
2. **Nové úložiště** (vytvoří se samo, práva zápisu jako zbytek `storage/`): účty a rozsah učitelů
   – `storage/teacher_accounts_v59.json.php`, `_log.json.php`, `_noise.json.php`, `_locks.json.php`
   (nikdy otevřené heslo ani čitelný login/IP v zámcích).
3. **Adresy (nové):** `teacher.php?tab=ucitele` (jen admin – založení/úprava/zrušení učitelských
   účtů), `teacher.php?tab=ucet` (vlastní heslo, přehled relace, odhlášení ostatních zařízení,
   „Převzít moje stará data“). Přepínač jazyka žáka je formulář na přihlašovací stránce, v účtovém
   menu a v patičce – žádná nová samostatná adresa.
4. **Web server:** nový kořenový `.htaccess` (allowlist vstupních PHP skriptů, zákaz `.md`/`.sql`/…,
   `mod_rewrite` obrana proti přebití hlubším `.htaccess`) a nová `.htaccess` v `lang/`,
   `migrations/`, `materials/` (s výjimkou pro veřejný roční plán); `V1/.htaccess` nově zakazuje
   starší aplikaci z webu úplně. Nginx nemá `.htaccess` – doplň ekvivalent podle
   `docs/deploy/nginx-educanet.conf.example`. Ověření: `curl -I https://<web>/V1/diagnostics.php`,
   `curl -I https://<web>/AUDIT_V57.md` a `curl -I https://<web>/app/lib.php` → **403**.
5. **První učitelský administrátorský účet** (nahrazuje sdílený `teacher_export_key` – v accounts
   módu se už nepoužívá vůbec, ani pro `teacher.php`, ani pro `export_extra_csv.php`):
   ```bash
   php tools/v59_teacher_accounts.php create-admin --login=jan.novak --name="Jan Novák"
   ```
   Jednorázové heslo se vypíše **jen na terminál** (nikam se neukládá, nikam se neloguje) – předej
   ho administrátorovi bezpečným kanálem (ne e-mailem v otevřeném textu). Po prvním přihlášení na
   `teacher.php` si musí nastavit vlastní heslo. Pak v záložce **Učitelé** založ účty ostatním
   vyučujícím s rolí (`teacher`/`assistant`) a přiřazenými třídami a předměty.
6. **Po založení prvního admina:** nastav `EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1` (accounts mód pak
   při chybějícím/nečitelném souboru účtů vrátí chybu 503 místo tichého pádu zpět do legacy režimu
   se sdíleným klíčem) a **zruš nebo rotuj** starý `EDUCANET_TEACHER_EXPORT_KEY`/`teacher_export_key`
   v secret souboru – ponechaný klíč dál funguje jako platný přihlašovací secret, i když se
   nepoužívá. `tools/preflight.php` na ponechaný klíč v accounts módu hlásí `FAIL`.
7. **Jazyky žákovského rozhraní:** ve výchozím stavu nabízí čeština/angličtina/ukrajinština
   (ukrajinština označená jako „beta“). Proměnná `EDUCANET_UI_LOCALES` (např. `cs,en`) umožní
   ukrajinštinu dočasně skrýt, dokud ji neprojde jazyková revize rodilým mluvčím (viz
   `ROADMAP_V60.md`); čeština jde vypnout nejde.
8. **Příprava a kontrola produkce** (nové proti v58):
   ```bash
   php tools/preflight.php --env-file=/etc/educanet.env --strict     # 1) prostředí – žádné secrety/data ve výstupu
   php tools/build_release.php --out=/srv/educanet-releases/<datum> --lint   # 2) vydání mimo projekt
   # nahraj obsah vydání na server jako web/, pak proti běžící instanci:
   php tools/http_smoke.php --base=https://skola.example             # 3) chování naživo (403/200, hlavičky, cookies)
   php tools/run_audits.php --since=all --with-smoke                 # 4) funkční audity aplikace
   ```
   Podrobně (vč. rozvržení adresářů, `RELEASE_MANIFEST.json`, rollbacku): `docs/NASAZENI_PRODUKCE.md`.
9. **Cron** (nové proti v58): doplň podle `docs/deploy/educanet.cron.example` – k dosavadním denním
   zálohám/retenci přibyl doporučený týdenní běh `tools/preflight.php`.
10. **HSTS:** podporováno, ale ve výchozím stavu **vypnuté** (`EDUCANET_HSTS=0`) – zapni (`=1`) až
    po potvrzení domény a všech subdomén školou; zapnutí je nevratné, dokud prohlížečům nevyprší
    platnost uloženého nastavení.
11. **Kontrola po nasazení:**
    ```bash
    php tools/run_audits.php --since=all --with-smoke --with-router   # očekávaný konec: RUN_AUDITS_OK total=91 failed=0
    php tools/v59_i18n_audit.php                                      # očekávaný konec: V59_I18N_AUDIT_OK checks=809 failed=0
    ```
    Přesná čísla, rozpad podle vrstev a zbývající otevřené body: `BUILD_MANIFEST_V59.md`,
    `AUDIT_V59.md`, `ROADMAP_V60.md`.

## v61 · rychlost, jednotný design, motivace, učitelský přehled a provoz

Podrobnosti: `CHANGELOG_V61.md`, `BUILD_MANIFEST_V61.md`. Nic z v61 nevyžaduje nové PHP rozšíření kromě
`sodium` pro šifrované zálohy (volitelné).

- **Rychlost (B):** `tools/v61_perf_audit.php`, `tools/v61_perf_report.php` (měření nasazené instance jen
  čtením: `bash /www/server/educanet/educanet-cron.sh perf`); `EDUCANET_STORAGE_READONLY=1` = režim bez zápisů.
  Pravidla HTTP cache v nginx/Apache šablonách (`docs/deploy/…`) po aktualizaci znovu vlož a ověř `nginx -t`.
- **Design (C):** `assets/tokens-v61.css`, `components-v61.css`, `nav_v61.php` – žádná instalace navíc.
- **Motivace (D):** denní/týdenní cíle, sezónní odznaky, souboje v Aréně a obchod (`storage/motivation_v61.json.php`
  a další soubory se založí samy); bez vlivu na známky.
- **Učitel (E):** záložka **Přehled třídy** (skupina Podpora), hromadné potvrzení hlášení, CSV export;
  CLI `php tools/v61_teacher_data_reassign.php` (výchozí `--dry-run`, `--apply` jen se zálohou).
- **Provoz (F):**
  1. První nasazení: `EDUCANET_TEACHER_ACCOUNTS_REQUIRED=1` zůstává zapnuté; `php tools/v59_teacher_accounts.php create-admin …`
     funguje i bez souboru účtů (do té doby web učitelské části vrací 503).
  2. `update.sh` (`docs/deploy/aapanel/update_aapanel.sh.example` → `/www/server/educanet/update.sh`, root, 0750):
     jediný příkaz pro aktualizaci z GitHubu s ověřením; poslední řádek `NASAZENO_OK` / `NASAZENI_FAIL <důvod>`.
     Nahraj také aktualizovaný `deploy_aapanel.sh` (nový příkaz `from-dir`) a `educanet-cron.sh` (případ `health`).
  3. Týdenní kontrola: cron `bash /www/server/educanet/educanet-cron.sh health` (týdně) → `storage/ops_health_v61.json.php`,
     pruh pro administrátora v učitelském cockpitu, panel v záložce Provoz.
  4. Šifrované zálohy: `backup_key` v `educanet.secrets.php` (base64, 32 B; uložit i mimo server) →
     `tools/backup_storage.php --encrypt`, obnova `tools/restore_storage.php --decrypt`; návod `docs/ZALOHY_V61.md`.
- **Kontrola po nasazení:** `php tools/run_audits.php --since=all --with-smoke --with-router`
  (očekávaný konec `RUN_AUDITS_OK`), audit části F: `php tools/v61_ops_audit.php` → `V61_OPS_AUDIT_OK checks=67 failed=0`.

## v62 · Kompetence a důkazy

Podrobnosti: `docs/KOMPETENCE_V62.md`, `CHANGELOG_V62.md`, `BUILD_MANIFEST_V62.md`. Nic z v62 nevyžaduje nové PHP rozšíření.
Vrstva je **pilot jen pro třídu 3.A** (`COMP62_PILOT_CLASSES` v `competencies_v62.php`); ostatní třídy ji nevidí a nic se jim nepočítá.

- **Co se děje:** při otevření záložky *Kompetence* v profilu žáka 3.A (nebo ručním přepočtem v cockpitu, záložka
  *Kompetence*, skupina Podpora) se z existujících dat (stav labu, lekce, testy, souboje, týmové hry, projekty) odvodí
  důkazy do `storage/evidence_v62/<student_id>.json.php` (jen odkaz na zdroj, skóre a čas; žádný volný text). Cache
  zvládnutí je v `storage/mastery_v62/` (kdykoli smazatelná). Soubory se založí samy.
- **Zpětné doplnění z historie (jednou po nasazení):** nejdřív náhled `php tools/v62_evidence_backfill.php` (výchozí
  `--dry-run`, nic nezapisuje, vypíše statistiku), potom `php tools/v62_evidence_backfill.php --apply`. Příkaz `--apply`
  se na ostré `storage/` bez `EDUCANET_STORAGE_DIR` **odmítne** – před ostrým během si zazálohuj `storage/`
  (`tools/backup_storage.php`) a spusť ho jako uživatel webu. Opakované spuštění nic nepřidá (idempotentní).
  Na produkci se zápis spouští s výslovně nastaveným `EDUCANET_STORAGE_DIR=/www/wwwroot/is.stanektech.cz/storage`
  (cesta k datům se tak zadává záměrně, nástroj se jí neptá sám). Příkaz jako u update, přes `runuser`:
  `runuser -u www -- bash -c 'set -a; . /www/server/educanet/educanet.env; set +a; export EDUCANET_STORAGE_DIR=/www/wwwroot/is.stanektech.cz/storage; cd /www/wwwroot/is.stanektech.cz; /www/server/php/83/bin/php tools/v62_evidence_backfill.php --apply'`
  (stejný příkaz lze použít v cronu).
- **Retence:** důkazy se drží do konce studia a **30 dní po stavu `left`/`archived`** se mažou. Maže je existující denní cron
  `tools/v58_retention.php --apply` (souhrn `evidence_v62_purged=N`); bez `--apply` jen spočítá. Žák bez data odchodu
  (`archived_at`) se nemaže.
- **Učitelská oprávnění:** POST `comp62_sync` (ruční přepočet) vyžaduje třídu v rozsahu učitele (`teacher59_action_policy_prefixes`)
  a oprávnění `analytics.view`; mapu třídy vidí každý učitel s rozsahem na 3.A.
- **Kontrola po nasazení:** `php tools/v62_competency_audit.php` → `V62_COMPETENCY_AUDIT_OK`; celá sada
  `php tools/run_audits.php --with-smoke` (očekávaný konec `RUN_AUDITS_OK`).

## v63 · Výukové cesty a moderní metody

Podrobnosti: `docs/CESTY_V63.md`, `CHANGELOG_V63.md`, `BUILD_MANIFEST_V63.md`. Nic z v63 nevyžaduje nové PHP rozšíření.
Cesty existují pro **3.A** (`lnx_chmod`, `net_dns`) a **1.A** (`web_html`, `gfx_contrast`); ostatní třídy je nevidí. Rozhodnutím školy
je od v63 pilot kompetencí v62 **3.A + 1.A** (`COMP62_PILOT_CLASSES`), takže cesty 1.A zapisují důkazy do nového katalogu `grafika_web`.

- **Co se děje:** žák dělá cesty v *Dnes → Moje cesty* (nebo z karty *Co dál* na přehledu). Stav je v
  `storage/paths_v63/<student_id>.json.php` (včetně volitelné reflexní věty žáka, max 200 znaků), přiřazení učitelem v
  `assign.json.php`, počty trychtýře v `_funnel_<třída>.json.php` (jen počty, smazatelná cache). Soubory se založí samy.
  Hodnocené kroky zapisují důkazy do `storage/evidence_v62/`. Cesty nedávají XP ani body.
- **Předpočítané výstupy (jen při změně obsahu cest):** `php tools/v63_paths_build_pre.php` (náhled), `--apply` přepíše
  `paths_v63_pre_data.php`, `--check` skončí chybou, když data neodpovídají simulátoru. Za běhu se nic nespouští.
- **Retence:** stav cest se **mažou 30 dní po stavu `left`/`archived`** (i s reflexní větou) existujícím denním cronem
  `tools/v58_retention.php --apply` (souhrn `paths_v63_purged=N`). Žák bez data odchodu se nemaže.
- **Učitelská oprávnění:** přiřazení a zrušení přiřazení (`p63_assign`, `p63_unassign`) vyžaduje třídu v rozsahu učitele a oprávnění
  `content.manage` (asistent jen čte). Učitel vidí počty a průměrnou kalibraci, nikdy reflexní věty.
- **Zdrojový obsah:** `paths_v63_content_os.php` (3.A) a `paths_v63_content_gfx.php` (1.A); otázky ověřování a opakování jsou z banky
  týmových her (`teamgames_v58_bank_net*.php`, `teamgames_v58_bank_gfx*.php`).
- **Kontrola po nasazení:** `php tools/v63_paths_audit.php` → `V63_PATHS_AUDIT_OK`; `php tools/v62_competency_audit.php`; celá sada
  `php tools/run_audits.php --since=all --with-smoke --with-router`.

## v64 · Hry, týmové hry, arény a férová ekonomika

Podrobnosti: `docs/HRY_V64.md`, `CHANGELOG_V64.md`, `BUILD_MANIFEST_V64.md`. Nic z v64 nevyžaduje nové PHP rozšíření ani migraci.

- **Ekonomika:** hry a arény dávají jen XP, společně nejvýš **60 XP na žáka a den** (`economy_v64.php`). Denní čítač je v
  `storage/economy_v64/daily.json.php` (jen sha1 + XP, bez jmen), deník transakcí v `storage/economy_v64_ledger/<YYYY-MM>.jsonl.php`
  (bez jmen a klíčů žáků; archivuje se po školním roce přes `ops58_retention_policy`). **Vypnutí (rollback):** v prostředí
  `EDUCANET_ECONOMY_V64=0` – hry se vrátí k chování v63 (přímé připsání bez stropu a deníku).
- **Ligy a ELO:** `storage/arena_v64_ratings.json.php` (start 1000, K=32 do 10 her, pak 16, minimum 600, bronz <950 / stříbro 950–1100 /
  zlato >1100, reset každé pololetí). ELO ani liga nejdou do známek ani exportů známek; žáci vidí jen ligu.
- **Absolutní žebříček** je výchozí vypnutý. Učitel ho zapíná v cockpitu → *Ekonomika* u CTF, robotí ligy a týmové hry; u závodu volbou
  hodnocení „žebříček“ při vytvoření (výchozí je osobní rekord).
- **Týmové hry:** role navigátor/operátor/kontrolor se mezi hrami střídají, přínos žáka se měří, po hře 3 otázky retrospektivy
  (`storage/teamgames_v64_retro.json.php`, bez jmen, vidí je jen učitel s rozsahem třídy, ne asistent). Retrospektivy maže
  `tools/v58_retention.php --apply` po školním roce (souhrn `retro_v64_purged=N`).
- **Výzva týdne** (karta na přehledu pod „Co dál“, `storage/challenges_v64.json.php`) a odznaky za zvládnutí/upevnění kompetencí (3.A, 1.A).
- **Metrika fáze:** `EDUCANET_STORAGE_DIR=<kopie storage> php tools/v64_engagement_report.php` (jen čtení, nad ostrou `storage/` se odmítne).
- **Kontrola po nasazení:** `php tools/v64_games_audit.php` → `V64_GAMES_AUDIT_OK`; celá sada
  `php tools/run_audits.php --since=all --with-smoke --with-router`.

## v65 · Projekty (cyklus, rubriky, peer review, týmy) a portfolio

Podrobnosti: `docs/PROJEKTY_V65.md`, `CHANGELOG_V65.md`, `BUILD_MANIFEST_V65.md`. Nové PHP rozšíření nevyžaduje; kód běží na PHP 8.1.

- **Migrace 0003** (`migrations/0003_projects_v65.php`) jen **vytvoří prázdné sidecary** `projects_v65_{cycle,meta,rubrics,peer,milestones}.json.php`; staré soubory
  (`project_grades`, `project_workspace_*`, `projects_v60*`) nemění. Je idempotentní. Spuštění: `php tools/migrate.php` (náhled) a po záloze
  `php tools/migrate.php --apply --backup-now`. Aplikace funguje i bez ní (sidecary se vytvoří při prvním zápisu), migrace jen sjednotí stav a výchozí režim cyklu.
- **Rollback:** nasadit předchozí vydání a smazat pět sidecarů `storage/projects_v65_*.json.php`, adresář `storage/portfolio_v65/` a proud `storage/projects_v65_diary/`
  (nic jiného na nich nezávisí; `tools/migrate.php` funkci `down` neumí, proto je to ruční krok). Odznaky a známky v `project_grades` zůstávají.
- **Retence:** `tools/v58_retention.php --apply` (denní cron) maže pitch, poznámky k odevzdání, peer texty, deník, rozdělení bodů a reflexe portfolia žáků
  30 dní po stavu left/archived (`COMP62_RETENTION_GRACE_DAYS`); souhrn `projects_v65_purged=N`.
- **Nastavení:** výchozí režim projektu je malý; plný cyklus, peer review a milníky zapíná učitel v cockpitu → Podpora → *Cyklus projektů*.
  Zámek projektu klienta podle kompetencí se nastavuje ve formuláři projektu (záložka *Projekty*), platí jen v pilotních třídách.
- **Kontrola po nasazení:** `php tools/v65_projects_audit.php` → `V65_PROJECTS_AUDIT_OK`; celá sada
  `php tools/run_audits.php --since=all --with-smoke --with-router`.

## v66 · Testy, hodnocení a učitelská analytika

Podrobnosti: `docs/HODNOCENI_V66.md`, `CHANGELOG_V66.md`, `BUILD_MANIFEST_V66.md`. Nové PHP rozšíření nevyžaduje; kód běží na PHP 8.1. Migrace není potřeba (soubory `storage/assessment_v66/` se založí samy).

- **Cron (nově):** `php tools/v66_item_analysis.php` denně v noci (položková analýza, štítky integrity, cache návrhů) a `php tools/v66_morning_build.php` ve školní dny ráno
  (ranní přehled pro cockpit). Vzor: `docs/deploy/educanet.cron.example`; aaPanel: `educanet-cron.sh v66items` a `v66morning` (`docs/deploy/aapanel/educanet-cron.sh.example`).
  Bez cronu cockpit ukáže „připravuje se“; analýzu a návrhy lze přepočítat tlačítkem v záložce *Testy a hodnocení*.
- **Návrh hodnocení je výchozí VYPNUTÝ.** Zapíná ho jen administrátor po třídách (cockpit → Podpora → *Testy a hodnocení* → Nastavení návrhu hodnocení): váhy 40/40/20 %, hranice 90/75/50/30 %.
  V režimu sdíleného klíče musí mít učitel roli admin (`EDUCANET_TEACHER_ROLE=admin`), jinak nastavení nelze změnit.
- **Retence:** `tools/v58_retention.php --apply` maže štítky integrity a ranní log z minulého školního roku a převzaté známky 30 dní po odchodu žáka (souhrn `assessment_v66_purged=N`).
- **Rollback:** nasadit předchozí vydání a smazat adresář `storage/assessment_v66/` (jen odvozená data a nastavení hodnocení; výsledky testů, důkazy ani známky projektů se nemění).
  Pole `duration_s` a `student_id` ve výsledcích startovního testu starší kód ignoruje.
- **Kontrola po nasazení:** `php tools/v66_assessment_audit.php` → `V66_ASSESSMENT_AUDIT_OK`, `php tools/v66_assessment_http_audit.php` → `V66_ASSESSMENT_HTTP_AUDIT_OK`; celá sada
  `php tools/run_audits.php --since=all --with-smoke --with-router`.

## v67 · UX, výkon, provoz a profil růstu

Podrobnosti: `CHANGELOG_V67.md`, `BUILD_MANIFEST_V67.md`, `docs/NASAZENI_PRODUKCE.md` (oddíl „v67 · provozní doplňky“). Nové PHP rozšíření nevyžaduje (`sodium` jen pro šifrované zálohy, jako dosud); kód běží na PHP 8.1.

- **Cron (nově):** `educanet-cron.sh nightly` denně 01:30 (záloha → `tools/v67_nightly_recompute.php` s `EDUCANET_NIGHTLY_ALLOW=1`); vzor v `docs/deploy/educanet.cron.example` a `docs/deploy/aapanel/educanet-cron.sh.example`. Bez čerstvé zálohy se přepočet odmítne.
- **Klíč záloh:** `php tools/backup_key_init.php --apply` (jednou; vypíše jen 8 znaků otisku, klíč nikdy).
- **Migrace:** `php tools/migrate.php --rollback=<id>` (náhled / příkaz obnovy ze zálohy).
- **Profil:** pilotní třídy (3.A, 1.A) mají mapu kompetencí jako první záložku a záložku *Můj růst* (cíle, příběh růstu, sdílení). Sdílení se spolužáky je výchozí vypnuté; školu lze vypnout `EDUCANET_GROWTH_PUBLIC=0`. Data: `storage/growth_v67/` (maže se s důkazy v62 po 30 dnech od odchodu žáka).
- **Vzhled:** přepínač světlý/tmavý/podle systému je nasazený jako infrastruktura, ale **pro žádný pohled zapnutý** (viz `ROADMAP_V68.md`, bod 1).
- **Přehled žáka:** podrobný progres se načítá na `?view=dashboard&details=1`; dovednosti se přepočítávají po akcích a v noci, ne při každém GET.
- **Rollback:** nasadit předchozí vydání; `storage/growth_v67/` a `storage/skill_sync_v67.json.php` lze smazat (odvozená data a cíle žáků).
- **Kontrola po nasazení:** `php tools/v67_ux_audit.php`, `v67_perf_audit.php`, `v67_ops_audit.php`, `v67_growth_audit.php` → `V67_*_AUDIT_OK`; celá sada `php tools/run_audits.php --since=all --with-smoke --with-router`.

## v68 · nový cockpit, tmavý režim a vyřazení zastaralých stránek

Podrobnosti: `CHANGELOG_V68.md`, `BUILD_MANIFEST_V68.md`, `docs/V68_KANDIDATI_VYRAZENI.md`. Nové PHP rozšíření nevyžaduje, `storage/` se nemění.

- **Cockpit:** šest sekcí (Dnes / Třída a žáci / Výuka / Hodnocení / Hry a motivace / Správa), boční panel od 1024 px, na mobilu menu pod tlačítkem Menu. Staré adresy `?tab=control|class_overview|growth|skills|mastery|filters|automations` vracejí 302 na novou sekci (záložky jsou v kódu do v69).
- **Tmavý režim:** učitel i administrátor mají v záhlaví přepínač Systém / Světlý / Tmavý (cookie `edu_theme`, žádná osobní data). Výchozí je „podle systému“. Žáci mají tmavý vzhled u přihlášení, přehledu a profilu.
- **Generované soubory (po změně zdrojových CSS znovu vygenerovat, jinak audit `v68_theme_audit` ohlásí neaktuálnost):** `php tools/v68_tokenize_css.php --derive` (kopie `assets/cx-*.css` pro cockpit + paleta `tokens-palette*-v68.css`), `php tools/v68_dark_overlay.php` (`assets/student-dark-v68.css`, `assets/cockpit-dark-v68.css`).
- **Nasazení:** nahrát celé vydání (`tools/build_release.php`), `assets/` včetně nových souborů; po nasazení se service worker sám přepne na cache `educanet-v68-ui`. Server nemusí nic nastavovat.
- **Vyřazeno:** pohled `v48_state` (kopie v `retired/v68/`, který se do vydání nebalí), `?view=continue` a `?view=one_task` přesměrují na přehled.
- **Rollback:** nasadit předchozí vydání (data se nezměnila). Cookie `edu_theme` je neškodná i ve starší verzi.
- **Kontrola po nasazení:** `php tools/v68_cockpit_audit.php`, `php tools/v68_theme_audit.php` → `V68_*_AUDIT_OK`; celá sada `php tools/run_audits.php --since=all --with-smoke --with-router`.
