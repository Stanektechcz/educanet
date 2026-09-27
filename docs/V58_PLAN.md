# EDUCANET v58 – plán vln, vlastnictví a kontrakty

Pracovní dokument integrátora pro paralelní agenty. Zdroj: `ROADMAP_V58.md`, `AUDIT_V57.md`, plánovač (2026-09-25).
**Pravidla:** vše v Linux Labu a hrách je simulace (nic se nespouští, žádná síť); obsah výukový; žádná herní mechanika
nesmí útočit na spolužáky, jejich účty, data nebo zařízení – soutěží se jen časem a body za vlastní řešení, stavbu a opravy.

## 1. Vlny a vlastnictví souborů

| Vlna | Agent | Položky | Soubory (jediný zapisující) |
|---|---|---|---|
| 1a | OPS | F1, OPS-03/04/05, SEC-14 | `tools/backup_storage.php`, `tools/restore_storage.php`, `tools/run_audits.php`, `tools/v58_smoke_audit.php`, `tools/v58_retention.php`, `tools/v58_ops_audit.php`, `tools/lib/http_harness.php`, `ops_v58*.php`, `assets/ops-v58.css`, `tests/skill_tree_audit.php` |
| 1a | SEC | SEC-01 (jednorázová hesla), SEC-11/12/16/17, Z3, Z4 | `accounts_v53.php`, `session_v53*.php`, `bootstrap.php` (jen auth), `teacher_operations_v46.php` (oprávnění), `tools/v53_provision_accounts.php`, `accounts_v58*.php`, `assets/accounts-v58.*`, `tools/v58_issue_passwords.php`, `tools/v58_accounts_audit.php` |
| 1a | ROBOTS | LAB-01 Robotí liga | `robots_v58*.php`, `assets/robots-v58.*`, `tools/v58_robots_audit.php`, `docs/ROBOTI_V58.md` |
| 1a | SPLIT | F5, ARC-02, DAT-05, část DAT-04 | `index.php`, `app/**`, `tools/lib/app_source.php`, audity čtoucí `index.php` (kromě v57 lab/arena a v58) |
| 1a | LABCORE | rozšiřitelné jádro labu | `linux_v57_*` (kromě views a úrovní), `lab_v57_api.php`, `linux_v58_ext.php`, `lab_v58_log.php`, `docs/LAB_V58_API.md`, `tools/v58_lab_ext_audit.php` |
| 1b | SIM-A | LAB-02 klíče (SSH), LAB-03 archivy, LAB-04 cron, LAB-05 uživatelé a práva | `linux_v58_cmd_ssh.php`, `linux_v58_cmd_archive.php`, `linux_v58_cmd_cron.php`, `linux_v58_cmd_users.php`, `linux_v58_levels_keys.php`, `linux_v58_levels_archives.php`, `linux_v58_levels_cron.php`, `linux_v58_levels_users.php`, `linux_v57_cmd_files.php`, `linux_v57_cmd_shell.php`, `tools/v58_sim_a_audit.php` |
| 1b | SIM-B | LAB-06 git, LAB-07 grafici, CNT-02 manuál ≥ 130 | `linux_v58_cmd_git.php`, `linux_v58_cmd_media.php`, `linux_v58_cmd_extra.php`, `linux_v58_levels_git.php`, `linux_v58_levels_grafika.php`, `tools/v58_sim_b_audit.php` |
| 1b | LEVELS | CNT-01, TCH-01, CNT-03 | `linux_v57_levels.php`, `linux_v57_levels_ops.php`, `linux_v58_generators.php`, `lab_v58_editor.php`, `lab_v58_editor_views.php`, `lab_v58_content.php`, `assets/lab-editor-v58.*`, `tools/v58_levels_audit.php`, `tools/v57_linux_lab_audit.php` |
| 1b | ARENA-A | ARN-01 týdenní hádanka, ARN-05 záznam závodu, ARN-06 férové režimy | `arena_v57.php`, `arena_v57_views.php`, `assets/arena-v57.*`, `tools/v57_arena_audit.php`, `arena_v58_weekly.php`, `arena_v58_replay.php`, `arena_v58_views.php`, `assets/arena-v58.*`, `linux_v58_levels_weekly.php`, `tools/v58_arena_audit.php` |
| 1b | ARENA-B | ARN-02 CTF týden, ARN-03 incidenty | `arena_v58_ctf.php`, `arena_v58_incident.php`, `arena_v58_events_views.php`, `assets/arena-events-v58.*`, `linux_v58_levels_ctf.php`, `linux_v58_levels_incident.php`, `tools/v58_ctf_incident_audit.php` |
| 1b | TG-CORE | 6 týmových her (vč. ARN-04 = Úniková místnost) | `teamgames_v58_*.php` (kromě bank), `assets/teamgames-v58.*`, `linux_v58_levels_tg.php`, `tools/v58_teamgames_audit.php` |
| 1b | TG-BANK | kvízová banka (≈ 400 otázek) | `teamgames_v58_bank_net.php`, `teamgames_v58_bank_gfx.php`, `tools/v58_quizbank_audit.php` |
| 1b | LABUI | A11Y-01..03, EDU-05, UI pro EDU-03/04, LAB-08/09, rozcestník her | `linux_v57_views.php`, `assets/linux-v57.js`, `assets/linux-v57.css`, `assets/lab-explain-v58.js`, `tools/v58_lab_ui_audit.php` |
| 1b | LEARN | EDU-01..04, LAB-08, LAB-09, TCH-02..04 | `learning_v56.php`, `learning_v56_views.php`, `assets/learning-v56.*`, `lab_v58_learning.php`, `lab_v58_review.php`, `lab_v58_teacher.php`, `lab_v58_teacher_views.php`, `assets/lab-teacher-v58.*`, `linux_v58_levels_review.php`, `tools/v58_learning_lab_audit.php` |
| 1b | OFFLINE | OPS-01 | `lab-offline.html`, `assets/lab-offline-v58.*`, `assets/linux-core-v58.js`, `assets/lab-manual-v58.json`, `tools/v58_export_manual.php`, `tools/v58_offline_audit.php` |
| 2 | F2 | storage kernel, DAT-03/07, Z1 | `bootstrap.php` (úložiště), všechny zbývající RMW soubory (seznam v §6) |
| 2 | F3, F4, F6 | behaviorální testy, mrtvý kód, identita + rollover | určí integrátor po vlně 1 |
| 3 | I18N | OPS-02 | `lang/*`, `i18n_v58.php` + převod UI po vrstvách |

**Sdílené soubory** (`index.php`/`app/` routy, `teacher.php`, `student_v55.php`, `sw.js`, `INSTALL.md`, `README.md`, `CLAUDE.md`)
upravuje jen integrátor. Agent dodá patch do `<SCRATCH>/INTEGRATION.md`.

## 2. Adresy (routy) a akce – závazné názvy

| Funkce | Žák (`index.php?view=…`) | Učitel (`teacher.php?tab=…`) | Prefix POST akcí učitele |
|---|---|---|---|
| Linux Lab (stávající) | `lab`, `lab&uroven=<id>`, `lab&zavod=<id>`, `prikazy[&c=<cmd>]` | `arena` | `arena57_` |
| Mapa dovedností / opakování (LABUI + LEARN) | `lab&sekce=dovednosti`, `lab&sekce=opakovani` | – | – |
| Týdenní hádanka (ARENA-A) | `hadanka` | `arena&rezim=hadanka` | `arena58_weekly_` |
| Záznam závodu (ARENA-A) | – | `arena&race=<id>&zaznam=1` (projektor) | `arena57_` |
| CTF týden (ARENA-B) | `ctf` | `ctf` | `arena58_ctf_` |
| Incidenty (ARENA-B) | `incident` | `incidenty` | `arena58_inc_` |
| Týmové hry (TG) | `hry`, `hry&hra=<id>` | `hry` (projektor `hry&projektor=<id>`) | `tg58_` |
| Robotí liga (ROBOTS) | `roboti` | `roboti` | `robots58_` |
| Analytika labu (LEARN) | – | `labdata` (heatmapa, dohled, přehrávání, export CSV) | `lab58t_` |
| Editor úrovní (LEVELS) | – | `editor` | `lab58e_` |
| Přístupy žáků (SEC) | – | `pristupy` (tisk `pristupy&print=1&class=…`) | `acc58_` |
| Provoz (OPS) | – | `provoz` | `ops58_` |

Každý modul dodá: `render_student(...)` / `render_teacher_tab(string $classId, string $csrf)` / volitelně `render_projector(...)`
a `teacher_handle_post(string $action, array $modules): void` pro svůj prefix (pro mapu oprávnění uveď požadované oprávnění).
JSON API modulů: vlastní vstupní soubor (`*_api.php`), jen POST, CSRF (`hash_equals`), identita ze session,
`session_write_close()` hned po ověření, `Cache-Control: no-store`, polling s `since=<verze>` → `{changed:false}`.

## 3. Kontrakty mezi agenty vlny 1b

1. **Jádro labu** – výhradně podle `docs/LAB_V58_API.md` (registry příkazů, manuálu, balíčků, úrovní, generátorů, kontrol,
   kontextů, událostí, filtrů; log příkazů `lab_v58_log.php`). Nové soubory `linux_v58_cmd_*.php` a `linux_v58_levels_*.php`
   načítá jádro automaticky; generátory/kontroly/manuál registruj z nich.
2. **Filtr `file_type`** (SIM-A ho zavede v příkazu `file` v `linux_v57_cmd_files.php`, SIM-B ho použije pro obrázky):
   `lab58_filter('file_type', '', ['path' => string, 'content' => string, 'world' => Lab57World])` → neprázdný popis
   přebije výchozí detekci v57.
3. **Kontrola obsahu CNT-03** (LEVELS): `cnt58_check_text(string $text, string $context = 'level'): array` →
   `['ok' => bool, 'issues' => [['code' => string, 'message' => string]]]` v `lab_v58_content.php`. TG-CORE/TG-BANK ji
   volají přes `function_exists()` pro otázky učitele.
4. **LEARN → LABUI** (data pro UI terminálu a domovskou stránku labu; LEARN implementuje, LABUI jen volá přes `function_exists()`):
   - `lab58_badges(string $classId, string $studentKey): array` → `[['id','title','icon','earned'=>bool,'progress'=>float 0..1,'hint'], …]`
   - `lab58_review_today(string $classId, string $studentKey): array` → `['date'=>'Y-m-d','streak'=>int,'items'=>[['level'=>id,'title','box'=>1..5,'done'=>bool,'url'], …]]`
   - `lab58_skill_map(string $classId, string $studentKey): array` → `['commands'=>[name=>['uses'=>int,'ok'=>int,'state'=>'new'|'learning'|'mastered']], 'concepts'=>[id=>['title','state','progress'=>float]], 'summary'=>['mastered'=>int,'learning'=>int,'total'=>int]]`
   - Adaptivní nápověda: LEARN registruje filtr `state_payload` a přidá klíč `adaptive_hint` = `null|['text'=>string,'reason'=>string]`; LABUI ho zobrazí.
5. **LABUI → OFFLINE**: `window.Lab57.setTransport(fn)`, kde `fn(op, payload) → Promise<object>` vrací stejný JSON jako `lab_v57_api.php`
   (`op=state|run|…`). OFFLINE píše jen vlastní soubory a `lab-offline.html`.
6. **Arena API** – signatury `arena57_roster()`, `arena57_snake_teams()`, `arena57_public_name()`, `lab57_display_name()`
   se ve vlně 1b NEMĚNÍ (používají je TG, ROBOTS, ARENA-B).
7. **XP a profil žáka (Z2)**: XP/odznaky uděluj jen v requestu daného žáka přes `learning_award_once(<unikátní klíč>)`
   („vyzvednutí“ při další návštěvě), nikdy z requestu učitele.

## 4. Týmové hry (TG-CORE)

Sdílená infrastruktura: relace her (`lobby → running ↔ paused → finished → archived`), týmy (had podle bodů / náhodně / ručně,
pozdní příchozí do nejmenšího týmu), verze stavu, polling (žák 3 s, projektor 2 s), projektor jen se souhrny týmů,
jména podle soukromí (iniciály / celé / anonymně), žádný chat – jen signály („Potřebuji pomoc“, „Mám to“, „Zkontroluj“),
XP jen za účast a jen jednou (vyzvednutí žákem). Úložiště `storage/teamgames_v58/<id>.json.php` (stav, `storage_update`),
index her `storage/teamgames_v58_index.json.php`, otázky učitele `storage/teamgames_v58_bank.json.php`.

| Hra | Pravidla | Férovost a bodování | Audit |
|---|---|---|---|
| Štafeta | 3–5 úseků, výstup (token) úseku n je vstupem úseku n+1; sítě: najdi → vyfiltruj → oprav; grafika: řetěz úloh (kontrast → selektor → export) | čas týmu; úseky s hvězdičkami; po 4 min bez postupu smí pomoct spoluhráč (+30 s) | úsek n nejde bez tokenu n−1; cizí token neprojde; svět per tým |
| Příkazové bingo | karta 4×4/5×5 s mikroúlohami (lab / grafika + kvíz), střed lehký | 1 bod pole, 3 řada; řada platí, jen když každý přítomný člen splnil ≥ 1 pole; stejné úlohy, jiné rozložení | žádné dvojí označení; označení jen po serverové kontrole |
| Riskuj! | tabule 5 × 5 na projektoru, týmy vybírají pole střídavě, odpovídají všichni na svých zařízeních | hodnota × podíl správných členů; bez záporných bodů; okno 30–45 s (lze prodloužit) | správná odpověď se neodešle klientovi před uzavřením; pozdní odpověď odmítnuta |
| Přetahovaná | celá třída ve 2 týmech, každý žák vlastní proud krátkých otázek | tah = 1/počet aktivních členů; adaptivní obtížnost; max 1 odpověď / 4 s; 3 kola; bez individuálního žebříčku | normalizace podle velikosti týmu; limit spamu |
| Správci sítě / webu | mapa 16–24 uzlů se závadou (lab mikroúloha / rozbitá stránka: alt, kontrast, layout); první tým, který uzel opraví, ho spravuje – uzel nelze vzít | 10 b. za uzel + 2 b. za uzel největší souvislé oblasti; rezervace 6 min; vlny nových závad po 5 min; +5 b. „za učení“ při opravě obsazeného uzlu | souběh → právě jeden vlastník; každý řešitel má vlastní kopii světa |
| Úniková místnost (ARN-04) | role s asymetrickými informacemi (Síťař/Správce/Detektiv/Dokumentátor; Typograf/Kolorista/Kodér/Art director), zámek otevře kód z dílčích výsledků rolí | čas; týmová nápověda přidá čas; cíl třídy „uniknou všechny týmy“; role se střídají | API vrací jen data vlastní role; kód nejde složit bez všech dílů; limit pokusů 5/min |

**Kvízová banka** (TG-BANK): položka
`['id'=>'net.dns.001','line'=>'networks|graphics|both','classes'=>[...]|null,'category'=>…,'difficulty'=>1..3,'type'=>'single|multi|bool|numeric|order|text|command','prompt'=>…,'options'=>[…],'answer'=>…,'tolerance'=>null,'accept'=>[…],'explain'=>…,'time_s'=>30,'source'=>'builtin','status'=>'published']`.
Kategorie sítě: příkazy Linuxu, soubory a práva, IP a maska, DNS, služby a porty, procesy, bezpečnost vlastního systému, hardware a OS.
Kategorie grafika: barvy a kontrast, typografie, rastr/vektor/formáty, HTML, CSS, přístupnost webu, kompozice a UX, licence (CC).
Cíl ≈ 25 otázek na kategorii (≈ 400), minimum 150.

## 5. Akceptační kritéria (výběr – plná matice v závěrečném manifestu)

| ID | Kritérium |
|---|---|
| SEC-01 | unikátní jednorázové heslo pro každý neaktivovaný účet, jen hash, platnost 14 dní, vynucená změna, `demo001` nikde v kódu ani UI, kartičky jen pro učitele |
| SEC-11/12/16/17 | volný vstup do třídy jen v dev; každá POST akce učitele mapovaná (výchozí zamítnutí); heslo ≥ 10 znaků a ne z běžných; token resetu mimo URL, `no-referrer` |
| F1/OPS-04/05 | `run_audits` tabulka + exit kód; záloha → obnova `diff -rq` = 0 |
| F2/OPS-03 | žádné `save_php_json_map`/`append_php_json` mimo jádro; 20 procesů bez ztráty; čtení během zápisu nikdy nevrátí `[]`; retence logu 30 dní |
| F3 | ≥ 70 % behaviorálních kontrol v auditech v51+; žádný audit nezávisí na dnešním datu |
| F5 | `index.php` < 800 řádků; méně načtených souborů na request |
| F6 | každý účet má `student_id`; kolize jmen hlášeny; rollover `--dry-run` vypíše plán |
| LAB-01 | 30 skriptů × 300 tahů < 1 s; deterministický replay; rozpočet kroků |
| LAB-02..07 | každý balíček ≥ 6 úrovní; referenční řešení projdou na ≥ 5 semínkách; příkazy v manuálu |
| LAB-08/09 | odznak jednou a na tabuli v55; 3 úlohy denně, Leitnerovy přihrádky |
| ARN-01..06 | nová hádanka každé pondělí, cizí řešení až po uzávěrce; dynamické bodování CTF; 15min odpočet + postmortem; časová osa závodu; osobní rekord bez cizího pořadí (ani v API) |
| EDU-01..05 | krok projektu se zamčenou úrovní nejde odevzdat; heatmapa jen agregace (≥ 3 žáci na buňku); nápověda po 3 stejných chybách; mapa dovedností; vysvětlení řádku výstupu z klávesnice |
| TCH-01..04 | neřešitelnou/nevhodnou úroveň nelze zveřejnit; přehrávání jen vlastní třídy, retence 30 dní; dohled do 10 s; CSV UTF-8 s BOM bez e-mailů |
| CNT-01..03 | 46 úrovní deklarativně se stejnou řešitelností; manuál ≥ 130 příkazů = registr; zakázané slovo nejde uložit |
| A11Y-01..03 | živý region, zkratka „přečti poslední výstup“, tabulka cesty, nastavení písma/kontrastu se pamatuje |
| OPS-01/02 | offline manuál + pískoviště, žádné HTML v cache SW; přepnutí jazyka, plurály cs/uk, fallback do češtiny |
| Týmové hry | viz §4 + simulace 30 žáků × 4 týmy, projektor bez jednotlivých chyb, XP jen jednou, polling bez zámku session, 390/1280 px |

## 6. Známá rizika pro vlnu 2 (F2)

- **Z1 (C)** `load_php_json` čte bez `LOCK_SH`, zápis soubor nejdřív zkrátí → souběžné čtení může vrátit `[]` a následný zápis
  smaže data. Oprava: sdílený zámek při čtení + atomický zápis (tmp + `rename`) + poškozený JSON = výjimka, ne `[]`.
- Horká RMW místa: `local_accounts`, `learning_profiles` (XP v session), `project_lobbies`, `adaptive_*`, v50 události,
  `project_workspace_*`, `skill_trees`, `lesson_sessions`, `teacher_tasks:243`, `tutorial_v52:353`, `append_php_json` v index.php.
- Schéma: vedlejší manifest `storage/_schema_v58.json.php`; `tools/migrate.php` s `--dry-run`/`--apply` a zálohou.
