# EDUCANET

## v50.7.6 · Quiet Focus Release

Aktuální autoritativní build je **v50.7.6**. Navazuje na v50.7.5 Minimal Surface Pass a dokončuje princip **kde jsem → co teď → jedna hlavní akce → detail až na vyžádání**. Dashboard progres je dvoustupňový, upozornění jsou sbalená, navigační dropdowny neobsahují pomocné věty ani dekorativní šipky, Unified Page Shell je kompaktnější a One Task používá stejný Quiet Focus slovník i hustotu.

### Produkční zásady v50.7.6

- první pohled studentovi neukazuje analytiku ani gamifikační stěnu;
- první otevření progresu obsahuje pouze **lekce / témata / projekty**;
- XP, grafy, badge, achievementy, roadmapa a historie jsou pod jediným `Další detail`;
- upozornění se zobrazují jako jeden sbalený vstup místo několika současných CTA;
- navigační dropdowny používají pouze názvy položek, bez hintů, šipek a dalších dekorativních metadat;
- Unified Page Shell a pracovní režim používají krátké textové akce bez dekorativních šipek;
- PATCH nikdy nepřepisuje `storage/`, `private/`, `uploads/` ani `cache/`.


## v50.7.2 · UI Consistency Pass

Aktuální autoritativní build je **v50.7.2**. Navazuje na v50.7.1 UI Repair a dokončuje sjednocení studentského designu: čitelná textová navigace **Pomoc / Profil**, zjednodušený Student Compass bez duplicitních stavů, stabilní Dashboard disclosure, Skill overview postavený na jednom doporučeném kroku a jednotný font/surface systém i v One Task Mode. Detail: `RELEASE_V50_7_2.md`, `CHANGELOG_UI_CONSISTENCY_V50_7_2.md`, `UPGRADE_V50_7_2.md`.

### Produkční zásady v50.7.2

- výchozí studentská obrazovka vždy preferuje **jeden aktuální krok** před statistikami a mapami;
- sekundární mastery, mapy, portfolio a historie zůstávají dostupné přes progressive disclosure;
- primární navigace nepoužívá nejasné samostatné Unicode ikony místo textu;
- Student Compass nezdvojuje Help, Guided Flow ani class chip;
- One Task používá stejný systémový font, světlý surface model a stejnou hustotu ovládání jako zbytek studentské aplikace;
- PATCH nepřepisuje `storage/`, `private/`, `uploads/` ani `cache/`.

## v50.4 · Goal Navigator & Minimal Step-by-step UX

Aktuální autoritativní build je **v50.4**. Student si vybere jediný srozumitelný cíl (např. Webdesign, PHP, Sítě, Linux, Security nebo DevOps) a systém propojí **povinný kurz → Skill Trees → Independent Growth → praktickou evidence** do jedné průběžně přepočítávané cesty. Na obrazovce jsou maximálně tři nejbližší kroky a pouze jeden dominantní CTA. Detail: `RELEASE_V50_4.md`, `CHANGELOG_GOAL_NAVIGATOR_V50_4.md`, `UPGRADE_V50_4.md`.

### Produkční zásady v50.4

- jeden zvolený cíl a jeden aktuální krok; žádná paralelní sada dashboardů;
- hlavní menu zůstává pouze **Domů / Učit se / Moje cesta / Třída** a dropdowny obsahují jen nejdůležitější vstupy;
- Knowledge Base, Growth, Skill Passport, labs, projekty a mastery zůstávají funkční, ale otevírají se kontextově z aktuální cesty;
- výběr cíle je pouze preference: `grade_impact=false`, `xp_impact=false`, `mastery_impact=false`;
- cíl se po každém dokončeném kroku přepočítá z reálného course/skill/growth stavu;
- PATCH nepřepisuje `storage/`, `private/`, `uploads/` ani `cache/`.

## v50.3 · Guided Learning Flow

v50.3 zavedla jednotný tok **Pochop → Zkus → Oprav → Ověř → Pokračuj**, current-task lekce a checkpoint-first praktické laby. Detail: `RELEASE_V50_3.md`.

## v50.2 · Student-first UX

v50.2 zjednodušila dashboard a navigaci na Domů / Učit se / Moje cesta / Třída a zavedla dominantní blok „Teď udělej toto“. Detail: `RELEASE_V50_2.md`.

## v50 · Hands-on Learning Runtime & Independent Growth

Aktuální autoritativní build je **v50**. Pro 3.A a 4.A SOSaPS přidává 56 Hands-on labů s hypotézou před příkazem, stateful simulací, evidence-first validací, vysvětlením, transferem, Skill Passportem a Peer Debuggingem. Dobrovolná vrstva Independent Growth přidává 12 rozvojových cest a 22 PHP learning cards; Growth je oddělený od povinného kurikula, známek, XP i standardní Mastery.

Výchozí runtime je bezpečný deterministický simulator. Volitelně lze zapnout localhost-only rootless OCI broker v `lab-runtime/`; při jeho nedostupnosti aplikace zůstává funkční v simulator režimu. PWA v50 cachuje novou Hands-on UI vrstvu. Detail: `RELEASE_V50.md`, `CHANGELOG_HANDS_ON_LEARNING_V50.md`, `UPGRADE_V50.md`, `materials/SPEC_HANDS_ON_LEARNING_V50.md`.

### Produkční zásady v50

- Hands-on/Growth evidence je formativní: `grade_impact=false`, `xp_impact=false`, `mastery_impact=false`;
- Growth nelze teacherem změnit na povinný;
- PATCH je kumulativní proti autoritativnímu v48.1 FULL a neobsahuje `storage/`, `private/`, `uploads/`, `cache/`;
- volitelný OCI broker smí běžet pouze rootless na loopbacku, s bearer tokenem, fixními profily/obrazy, allowlistem, limity a `--network none`;
- bez brokeru se automaticky používá bezpečný simulator.

## v48.1 · 3.A Deep Visual Labs

Aktuální autoritativní build je **v48.1**. Nad společným Visual & Practical Learning Engine v48 přidává 28/28 hluboce lesson-specific praktických laboratoří pro **3.A SOSaPS**. Každý lab má vlastní vizuální model, minimálně tři realistické faulty, minimálně tři serverově validované checkpointy, Guided/Practice/Challenge, transfer a učitelský orchestration doplněk. Deep Lab je čistě formativní a nemění známku, XP ani Mastery. Detail: `RELEASE_V48_1.md`, `CHANGELOG_3A_DEEP_VISUAL_LABS_V48_1.md`, `materials/SPEC_3A_DEEP_VISUAL_LABS_V48_1.md`.

## v48 · Visual & Practical Learning Engine

V48 vytvořila společný framework **Predikuj → Porovnej → Debuguj → Experimentuj → Sestav → Přenes** pro 112/112 strukturovaných lekcí a Live Teacher Orchestration. V48.1 tento framework poprvé naplňuje hlubokým lesson-specific obsahem pro celý jeden ročník. Detail: `RELEASE_V48.md`, `materials/SPEC_VISUAL_PRACTICAL_LEARNING_V48.md`.

## v47.2 · Corrective Transfer Loop

Aktuální autoritativní build je **v47.2**. Po chybné mikrodiagnostické odpovědi se student nevrací na začátek lekce, ale projde krátký čtyřkrokový cyklus **princip → podobný worked example → kontrastní příklad → transfer do nové situace**. Cyklus je čistě formativní: nemění známku, XP ani Mastery. Detail: `RELEASE_V47_2.md`, `CHANGELOG_CORRECTIVE_TRANSFER_LOOP_V47_2.md`.

### Produkční zásady v47.2

- corrective loop se zobrazí pouze po chybné odpovědi;
- transfer check je lokální a není součást klasifikace;
- podpůrná historie má explicitně `grade_impact=false`, `xp_impact=false`, `mastery_impact=false`;
- pokud pro téma není kvalitní nová uzavřená otázka, systém použije otevřený transfer s kontrolními kritérii místo generování pochybného distractoru;
- PATCH nepřepisuje produkční runtime data.

## v47 · Student Learning Coach

V47 byla předchozí vrstva Student Learning Coach. Studentská část dostává jeden osobní studijní cyklus: **10/25/45min Learning Coach → focus → retrieval / cílený krok → Mistake Notebook → 60s exit ticket**. Systém skládá plán z učitelských úkolů, intervencí, spaced retrieval, opakovaných chyb, další odemčené lekce a Skill/Mastery evidence. Podporuje režim málo energie i přípravu na test, ale nepřidává penalizační streak ani automatické známkování. Detail: `RELEASE_V47.md`, `CHANGELOG_STUDENT_LEARNING_COACH_V47.md`, `BUILD_MANIFEST_V47.md`.

### Produkční zásady v47

- startovní diagnostika má přednost; po ní je Learning Coach jediný primární doporučený další krok;
- sestavení plánu je read-only a nemění XP, Mastery ani známku;
- focus timer běží lokálně v browseru;
- Mistake Notebook ukazuje vzorce k opravě, ne seznam trestů;
- preference, self-check a exit ticket se zapisují jen explicitní akcí studenta;
- PATCH zachovává produkční runtime data a secrets.

## v46.2 · Control Tower & Reliability

V46.2 byla předchozí Control Tower vrstva. Nad v46.1 přidává **Control Tower**, explicitní přehled změn od poslední kontroly, týmově konfigurovatelné **SLA/Escalations**, 14denní Planner, osobní/týmové **Communication Templates**, cross-class Reporty, CSV/JSON export a **Data Quality Center**. Všechny vrstvy v46/v46.1 zůstávají zachované. Detail: `RELEASE_V46_2.md`, `CHANGELOG_CONTROL_TOWER_V46_2.md`, `BUILD_MANIFEST_V46_2.md`.

### Produkční zásady v46.2

- otevření Control Tower/Reportů/Kvality dat nic nezapisuje; baseline vznikne až explicitním potvrzením;
- komunikační šablony připravují pouze draft a nic automaticky neposílají;
- Data Quality Center pouze diagnostikuje a neprovádí automatické opravy;
- SLA politika je team-scoped a měnit ji může Admin/Lead;
- PATCH nepřepisuje runtime data ani secrets.

## v46.1 · Teacher Operations + Production Operations

V46.1 byla předchozí autoritativní produkční vrstva. Teacher/Admin Cockpit nyní funguje jako operační systém výuky: **Attention Center → Student 360° → Intervence → Follow-up → měření výsledku**. V46 přidává Smart Filters, automatizace/notifikace, Class Analytics, Lesson Intelligence, Bulk Actions 2.0, globální Command Palette a serverové role. V46.1 doplňuje transparentní **Class Health**, osobní/týmový **Watchlist**, **Follow-up Queue**, intervenční playbooky, **Operations Audit** a konfliktově bezpečný **Safe Undo**.

Detail: `RELEASE_V46.md`, `RELEASE_V46_1.md`, `CHANGELOG_TEACHER_OPERATIONS_V46.md`, `CHANGELOG_PRODUCTION_OPERATIONS_V46_1.md`, `BUILD_MANIFEST_V46_1.md`.

### Produkční zásady v46.1

- analytické/read-only obrazovky při otevření nezapisují do storage;
- žádná intervence ani Health Score automaticky nemění známku nebo Mastery;
- automatizace běží explicitně přes `tools/v46_automation_tick.php`, nikoli skrytě v dashboard requestu;
- týmové záznamy jsou izolované přes `teacher_team_id` a owner-only mutace;
- Safe Undo má 15minutové okno a před obnovením kontroluje hash aktuálního post-action stavu.

## v46 · Teacher Operations Suite

V46 přidala Attention Center, Student 360°, Intervention Engine, Smart Saved Filters, automation watches/notifikace, Class Analytics, Lesson Intelligence, Bulk Actions 2.0, `Ctrl/⌘+K` Command Palette a role Admin / Lead / Teacher / Assistant. Podrobnosti jsou v `RELEASE_V46.md`.

## v45.7 · Osobní a týmové Saved Filters

Historická v45.7 vrstva Saved Filters. Uložené filtry nyní rozlišují **Osobní** a **Týmové** presety. Týmový filtr je dostupný učitelům se stejným `teacher_team_id`, zobrazuje autora a může jej smazat pouze vlastník. Existující v45.5/v45.6 presety zůstávají automaticky osobní. Zachovány jsou všechny dimenze v45.6: třída, stav výsledku, priorita, stav úkolu a termín úkolu. Detail: `RELEASE_V45_7.md`, `CHANGELOG_TEAM_SAVED_FILTERS_V45_7.md`, `BUILD_MANIFEST_V45_7.md`.

## Historie vydání

## v45.6 · Saved Filters pro stav a termín úkolu

Ve v45.6 začaly uložené učitelské filtry kombinovat **třídu + stav výsledku + prioritu + stav úkolu + termín úkolu**. Učitel může jedním presetem otevřít například studenty s aktivním úkolem **po termínu**, s termínem **dnes**, **do 3 dnů**, **do 7 dnů** nebo aktivním úkolem **bez termínu**; stejný filtr funguje server-side v Přehledu třídy i interaktivně ve Výsledcích třídy. Staré v45.5 presety jsou zpětně kompatibilní. Detail: `RELEASE_V45_6.md`, `CHANGELOG_SAVED_FILTER_TASKS_V45_6.md`, `BUILD_MANIFEST_V45_6.md`.

## v45.5 · Saved Filters across Class Overview & Results

V45.5 přidala administraci, která ukládá pojmenované učitelské presety pro kombinaci **třída + stav + priorita**. Stejný filtr lze jedním klikem použít v **Přehledu třídy** i ve **Výsledcích třídy**; Přehled třídy pak přímo filtruje studentské signály server-side a Výsledky používají stejný stav v rychlé tabulce. Presety jsou oddělené podle učitele, čtení je read-only a zápis nastává pouze při explicitním Uložit/Smazat. Detail: `RELEASE_V45_5.md`, `CHANGELOG_SAVED_FILTERS_V45_5.md`, `BUILD_MANIFEST_V45_5.md`.

## v45.4 · Quick Filters, Bulk Actions & Teacher Tasks

Ve v45.4 dostaly Výsledky třídy rychlé filtry podle **třídy, stavu a priority**, hromadný výběr viditelných studentů, interní označení, bezpečný CSV export a hromadné přiřazení úkolu. Úkol z administrace se zobrazí přímo ve studentském dashboardu a student jej může po dokončení uzavřít. V45.2 performance layer i v45.3 přehledná dropdown administrace zůstávají zachované. Detail: `RELEASE_V45_4.md`, `CHANGELOG_ADMIN_BULK_ACTIONS_V45_4.md`, `BUILD_MANIFEST_V45_4.md`.

## v45.3 · Clear Admin & Class Insights

Aktuální autoritativní build je **v45.3**. Teacher/Admin Cockpit má novou dropdown navigaci, samostatný **Přehled třídy** a **Výsledky třídy**, kontextový přepínač 1.A–4.A a jednu výsledkovou tabulku spojující projektová hodnocení, testy, laboratoře, prestižní zkoušky a Skill Mastery. Výkonová architektura v45.2 zůstává zachovaná. Detail: `RELEASE_V45_3.md`, `CHANGELOG_ADMIN_NAVIGATION_V45_3.md`, `BUILD_MANIFEST_V45_3.md`.

## v45.2 · Admin Performance Layer

Aktuální autoritativní build je **v45.2**. Výukové funkce v45.1 zůstávají zachované, ale Teacher/Admin Cockpit nově používá read-only Skill snapshoty, indexované projektové a mastery datasety, lazy PHP view moduly, podmíněné simulation assety a volitelnou APCu cache. V referenčním lokálním testu klesl warm request globálního Teacher Overview přibližně z 368 ms na 9,6 ms. Detail: `RELEASE_V45_2.md`, `CHANGELOG_ADMIN_PERFORMANCE_V45_2.md`, `BUILD_MANIFEST_V45_2.md`.

## v45 · Visual Simulation Engine

Aktuální autoritativní build je **v45**. Navazuje na v43 Cognitive Labs a v44 Learning Studio a přidává ke všem **112 strukturovaným lekcím 1.A–4.A** manipulovatelné vizuální simulace: lesson-specific parametry, okamžitou deterministickou zpětnou vazbu, transparentní metriky, A/B varianty, scénáře „co-když“, evidence checkpoint a fullscreen učitelský režim pro projektor. Designové předměty používají specializované scény pro typography/layout, formuláře/accessibility, komponenty/handoff, IA/usability, image/crop a motion; SOSaPS používá vrstvené systémové modely pro DNS, DHCP, routing, packet flow, systemd, permissions, firewall, proxy, observability, storage, backup, containers, release a incident response.

V45 **nehodnotí studenty podle pohybu sliderů**. Ukládají se pouze explicitní A/B/best snapshoty a server hodnoty znovu validuje a přepočítá. Detail: `RELEASE_V45.md`, `BUILD_MANIFEST_V45.md`, `materials/SPEC_VISUAL_SIMULATION_V45.md`.

## v43 · Cognitive Visualization & Visual Labs

Aktuální autoritativní build je **v43**. Každá z 112 strukturovaných lekcí 1.A–4.A má vlastní Cognitive Lab: konkrétní situaci, Freeze & Predict, confidence, X-Ray vrstvy, timeline scrubber, manipulovatelný model podle typu předmětu, contrast case, sestavení vlastního mentálního modelu, reverse engineering, transfer a Memory Snapshot. Učitel má stejný model v Lesson Mode jako Live Explainer pro projektor.

V43 navazuje na v42 Adaptive Lesson Kits, v41 Mastery Learning a v35 Reality Demos. Detail je v `RELEASE_V43.md`, `BUILD_MANIFEST_V43.md` a `materials/SPEC_COGNITIVE_VISUALIZATION_V43.md`.

## v35 · Interactive Reality Demos

V35 nahrazuje obecné „animované karty“ konkrétními **Reality Demos**. Student řeší reálný problém z právě probírané lekce: nejdřív vidí symptom a důkazy, potom zvolí další krok, následně se přehraje důsledek a systém přesně oddělí **co výsledek dokazuje** od toho, **co z něj ještě tvrdit nelze**. Knowledge Tour používá prediction-first mechaniku bez penalizace; správná predikce pouze dokončí přípravný vizuální krok.

Frontend používá nativní Web Animations API, IntersectionObserver, CSS container queries, `content-visibility:auto` a jako progressive enhancement View Transition API. Vše respektuje `prefers-reduced-motion` a student má navíc vlastní přepínač `Pohyb: auto / omezený`. Učitel má stejné situace v Lesson Mode jako projektorové otázky s oddělenou „Cestou pro učitele“. Detaily jsou v `RELEASE_V35.md`, `CHANGELOG_REALITY_DEMOS_V35.md` a `materials/REALITY_DEMO_METHOD.md`. Bezpečný inplace upgrade z v34 je popsán v `UPGRADE_V35.md`.

## v30 · Adaptive Learning + školní rok 2026/2027

Aktuální build má pro každou třídu **28 strukturovaných dvouhodinových lekcí** a společný kalendář **40 výukových střed**. Prvních 28 dostupných střed používá plnohodnotné lekce; dalších 12 bloků je záměrně vyhrazeno pro projektové checkpointy, Mastery, capstone, obhajoby, retrospektivu, portfolio, individuální podporu a rezervu. 1.A/2.A zůstávají v Grafice a webdesignu, 3.A/4.A v Semináři OS a počítačových sítí.

Knowledge Base nyní obsahuje **132 materiálů**. Nová v30 vrstva přidává Help Ladder `Jednoduše → Přirovnání → Krok za krokem → Příklad → Typická chyba → Zkus si to`, krátké animované konceptové smyčky a alternativní video/reference zdroje. Použití nápovědy samo o sobě nesnižuje známku ani Skill Mastery; hodnotí se následná samostatná evidence.

Student má nový pohled **Kalendář** a učitel globální **Kalendář** pro 1.A–4.A. Plán je v `school_year.php`, metodika v `materials/ADAPTIVE_EXPLANATION_GUIDE.md` a lidsky čitelná roční mapa v `materials/school_year/SCHOOL_YEAR_2026_2027.md`.

### Administrátorský / učitelský přístup

EDUCANET z bezpečnostních důvodů **neobsahuje statické heslo v ZIPu**. Teacher/Admin cockpit je `teacher.php`; přihlašovací jméno je auditní zobrazované jméno učitele a tajný klíč se nastavuje pouze server-side přes `EDUCANET_TEACHER_EXPORT_KEY`. Bez nastaveného klíče vrací teacher cockpit 503. Podrobný postup je v `INSTALL.md`.

> Starší sekce níže popisují postupný vývoj jednotlivých verzí a mohou uvádět historické počty lekcí/materialů. Pro aktuální stav je autoritativní **v50**: úvodní sekce README, `RELEASE_V50.md`, `BUILD_MANIFEST_V50.md` a stále platné obsahové specifikace starších výukových vrstev.

## v29 · Teacher Global Overview Dashboard

Výchozí **Přehled** v `teacher.php` nyní ukazuje 1.A–4.A současně: stav všech 72 lekcí, interaktivních checklistů, projektů/týmů, hodnocení a Skill Mastery. Každá třída má vlastní kartu s capstone, Project Workspace signály, mastery větvemi a doporučenou další akcí. Detail implementace je v `CHANGELOG_TEACHER_GLOBAL_DASHBOARD_V29.md`.

## Project Workspace v26 · týmové role a evidence spolupráce

Aktuální build obsahuje plnohodnotný **Project Workspace** nad existujícími skupinovými projekty. Student má jednu primární a až dvě podpůrné role (Leader, Designer, Researcher, Developer, Presenter, QA), osobní „další akci“, jednoduchý týmový board, dependencies, Definition of Done, blocker flow, QA, Decision Log, check-iny, self-retrospektivu, peer feedback a Keep/Improve/Try týmovou reflexi. Role Fit vychází ze Skill Mastery a ověřené historie, ale funguje pouze jako doporučení a podporuje rotaci rolí.

Učitel má v `teacher.php` samostatný **Workspace** cockpit s role coverage, workload/bus-factor signály, QA, soukromými žádostmi o pomoc, moderací peer feedbacku a role-specific evaluation. Teprve publikované učitelské hodnocení role vytváří idempotentní Project Evidence pro relevantní Skill Trees; počet tasků, kliknutí ani peer popularita Mastery ani známku automaticky nezvyšují. Referenční MySQL 8 schéma je v `database/project_workspace_mysql.sql`, implementační přehled v `CHANGELOG_PROJECT_WORKSPACE_V26.md`.

Samostatný PHP 8+ modul pro třídy **1.A Grafika**, **2.A Grafika**, **3.A Seminář OS a sítě** a **4.A Seminář OS a sítě**.

## Rozšíření kurzu: Lekce 5–7 + interaktivní materiály

Aktuální verze obsahuje pro **1.A Grafiku, 2.A Grafiku, 3.A SOSPS a 4.A SOSPS celkem 7 navazujících lekcí**, každou navrženou na 2 × 45 minut. Lekce 5 a 6 jsou v `lessons_plus.php`, Lekce 7 v `lessons_more.php`; všechny se odemykají stejnou serverovou sekvenční logikou jako předchozí části kurzu.

Knowledge Base má nyní **69 materiálů**. K předchozím rozšířením přibyly čtyři další interaktivní materiály v `knowledge_extensions_more.php`: responzivní grafická série, accessibility design, packet analysis a SLO/postmortem. Každý má vlastní Knowledge Tour (`knowledge_tours_more.php`), knowledge check a povinnou simulaci (`simulations_more.php`). Content audit kontroluje i tuto vrstvu a nový materiál bez simulace neprojde QA.

### Nové bloky

- **1.A Grafika:** paleta + ikonografie + obrazová kompozice; redesign slabého návrhu; následně responzivní série do tří formátů.
- **2.A Grafika:** spacing/component design system; portfolio case study + mikrointerakce; následně accessibility + responsive UI.
- **3.A SOSPS:** IPv6 + DNS A/AAAA/CNAME; monitoring služeb + DHCP reservations; následně packet journey a Wireshark mindset.
- **4.A SOSPS:** HTTP observability + load balancing; canary release + backup/restore drill; následně SLO, error budget a postmortem.

### Interaktivní simulace

Nové simulace zahrnují Palette Lab, Icon Lab, Crop Lab, Critique Lab, Spacing Tokens, Component Lab, Motion Lab, Case Study Builder, IPv6 Lab, DNS Records Lab, Monitoring Lab, DHCP Reservation Lab, HTTP Probe, Load Balancer, Canary Lab a Restore Drill. Všechny používají stávající jednotný loop **predikce → experiment → evidence → závěr → XP**.

Animace jsou implementované nativně přes CSS/SVG/Web Animations API, takže základní kurz není závislý na CDN nebo externí JS knihovně. To je záměrné kvůli spolehlivosti ve školní síti.

### Externí video / referenční zdroje

`learning_resources.php` přidává ke konkrétním materiálům volitelné karty „jiné vysvětlení“. Video zdroje mají vlastní vizuální kartu s thumbnail a označením jazyka. Zařazené jsou mimo jiné české Canva tutoriály, české video o IP/DNS/NAT a česká přednáška k TLS certifikátům; dále používáme Canva Design School, Cisco Skills for All, Cloudflare Learning Center, MDN, ITnetwork a Pochop.it. Externí zdroj nikdy neodemyká povinný krok a nenahrazuje interní Knowledge Tour; pokud není internet, celý kurz dál funguje.


## Co obsahuje
### Lekce 7 a katalog 4 × na řádek

Každá třída má nově **7 navazujících dvouhodinových lekcí**. Lekce 7 mají přesně 90 minut: 1.A adaptuje jeden design do tří formátů, 2.A řeší accessibility a responsive UI, 3.A packet journey/Wireshark mindset a 4.A SLO, error budget a postmortem. Data jsou v `lessons_more.php`.

Knowledge Base katalog používá na širokém desktopu **4 materiály na řádek**, na menších šířkách automaticky přechází na 3/2/1. Karty jsou záměrně kompaktnější, ale zachovávají stav, cíle, návaznost a tlačítko Pokračovat. Externí video/reference grid používá stejné 4/2/1 responzivní rozložení.


- krokový startovní test bez známkování,
- okamžité vysvětlení po každé odpovědi,
- pouze počet správných odpovědí jako orientační výsledek,
- odkazy na odpovídající články knowledgebase po dokončení,
- knowledgebase je během aktivního startovního testu zablokovaná serverovou session,
- **3.A a 4.A mají kompletní program na 2×45 minut** včetně časového plánu, praktických incidentů, simulovaných výpisů, tří úrovní nápovědy, opakovaných pokusů bez penalizace, okamžité kontroly a návaznosti na knowledgebase,
- **3.A a 4.A mají samostatnou realistickou případovou studii**: 3.A „Den juniorního administrátora“, 4.A „Produkční incident po migraci“, včetně vizuální síťové topologie v inline SVG, časové osy tiketů a interaktivního simulovaného terminálu,
- **1.A i 2.A mají kompletní grafický blok na 2×45 minut**: startovní test → Knowledge Tour → interaktivní Design Studio → převod konfigurace do Canvy → Visual QA → export → závěrečný challenge pro rychlejší,
- Design Studio umožňuje živě měnit hierarchii, grid, spacing, barvy a formát, vizuálně testovat kontrast / grayscale / thumbnail, nastavovat export preset, stáhnout PNG náhled, JSON konfiguraci a zkopírovat checklist pro Canvu,
- výsledky startovních testů se ukládají do `storage/practice_results.json.php`,
- dokončené praktické laby 3.A/4.A do `storage/lab_results.json.php`,
- dobrovolné známkované Extra challenge 3.A/4.A do `storage/extra_submissions.json.php`,
- chráněný učitelský CSV export extra úkolů přes `teacher.php` / `export_extra_csv.php`,
- grafické výstupy do `uploads/` a metadata do `storage/graphics_submissions.json.php`.


## Realistické case studies, diagramy a interaktivní terminál

Na dashboardu 3.A a 4.A je nová položka **Případová studie**. Studijní stránka je stejně jako knowledgebase během aktivního startovního testu serverově zablokovaná, aby se z ní nestala nápověda k diagnostickému testu.

Case studies nejsou prezentovány jako skutečné události z konkrétní firmy. Jde o **fiktivní infrastrukturu složenou z realistických incidentů**, se skutečnou syntaxí běžných administračních příkazů a věrohodnými výpisy.

### 3.A – Den juniorního administrátora

Student prochází jednu směnu helpdesku:

- `08:05 / INC-301` – klient skončí na APIPA `169.254.x.x`, následuje oddělení DHCP, routingu a DNS,
- `09:20 / INC-302` – server odpovídá na ICMP, ale TCP/443 není dostupný,
- `10:35 / INC-303` – SFTP timeout; SSH naslouchá, ale provoz blokuje firewall,
- `11:20` – předání směny a shrnutí diagnostických důkazů.

Vizuální diagram zobrazuje notebook, VLAN/switch, gateway, DNS, interní web, SFTP/SSH server a cestu do internetu. V praktickém labu se podle právě řešeného tiketu zvýrazní relevantní uzly.

### 4.A – Produkční incident po migraci

Student dostane roli on-call administrátora a incident se v čase rozvíjí:

- služba je `active`, lokální `curl` vrací 200, ale socket je navázán pouze na `127.0.0.1`,
- DNS po migraci stále ukazuje na původní IPv4 adresu a je potřeba pracovat s TTL/cache,
- nový server má chybnou default route mimo svůj přímo připojený `/24` subnet,
- SSH klient odmítá příliš otevřený privátní klíč,
- závěr prověřuje celý řetězec DNS → TCP → TLS/HTTP.

Diagram ukazuje admin klienta, DNS, gateway, starý a nový aplikační uzel, loopback socket a vzdálenou síť.

### Interaktivní výpisy příkazů

Příkazové ukázky nejsou ihned rozepsané. Student nejdřív vidí **účel diagnostického kroku a samotný příkaz** a tlačítkem `Spustit příkaz` odkryje simulovaný výstup. Teprve potom může otevřít položku **Co tento výstup dokazuje?**.

Tím lze s výpisy pracovat ve třech krocích:

1. formulovat hypotézu,
2. předpovědět výsledek / spustit simulovaný příkaz,
3. interpretovat důkaz a teprve potom pokračovat k řešení.

Simulovaný terminál je dostupný jak na stránce případové studie, tak přímo uvnitř příslušného praktického incidentu. Neprovádí žádné příkazy na studentském zařízení ani na serveru.

## 90minutová struktura 3.A

- 0–5 min: briefing a pravidla troubleshootingu,
- 5–25 min: 15otázkový startovní test,
- 25–35 min: individuální rozbor chyb + knowledgebase,
- 35–50 min: Lab 1 – DHCP → DNS,
- 50–65 min: Lab 2 – dostupnost hosta vs. webová služba,
- 65–80 min: Lab 3 – SSH/SFTP a firewall,
- 80–90 min: exit ticket.

Praktické checkpointy pracují s výpisy `ipconfig`, `ping`, `nslookup`, `Test-NetConnection` a `ss`. Student musí vždy zvolit další krok podle dostupného důkazu.

## 90minutová struktura 4.A

- 0–5 min: briefing – hypotéza → měření → důkaz,
- 5–25 min: 15otázkový scénářový test,
- 25–35 min: rozbor + relevantní knowledgebase,
- 35–48 min: Incident 1 – service binding,
- 48–60 min: Incident 2 – DNS A záznam, TTL a cache,
- 60–72 min: Incident 3 – chybná default route,
- 72–82 min: Incident 4 – SSH private key a oprávnění,
- 82–90 min: exit ticket kombinující DNS, TCP, TLS a HTTP.

Incidenty používají reálné typy výpisů z `systemctl`, `ss`, `curl`, `dig`, `ip addr`, `ip route`, `nc` a `ssh`.

## Nápovědy a kontrola praktických úloh

Každý praktický krok má:

1. konkrétní scénář a dostupné důkazy,
2. automaticky kontrolovanou volbu diagnostického kroku nebo závěru,
3. až tři postupně odkrývané nápovědy,
4. možnost libovolného počtu pokusů bez penalizace,
5. po správném řešení vysvětlení, co výsledek skutečně dokazuje,
6. přímý odkaz do relevantní části knowledgebase.

Knowledgebase je v praktické části záměrně dostupná jako dokumentace. Zamčená je pouze během startovního testu.


## Extra challenge pro rychlejší – možnost získat známku

Po dokončení celého hlavního praktického bloku se u 3.A a 4.A odemkne samostatná dobrovolná větev na dalších **35–45 minut**. Neodemyká se po samotném testu – student musí dokončit i hlavní lab.

Extra challenge není hodnocen podle rychlosti. Student odevzdává strukturovaný incident report a žádá tím o hodnocení. Rubrika má **25 bodů** a je studentovi viditelná před odevzdáním. Doporučený převod: 23–25 = 1, 20–22 = 2, 16–19 = 3, 12–15 = 4, 0–11 = 5 / doporučeno nejprve vrátit k dopracování.

### 3.A – Dva tikety bez restartu

Student řeší dva propojené incidenty po přesunu učebny do nové VLAN:

- interní jméno `filesrv.school.lan` nefunguje, přestože IP konektivita funguje; musí odhalit nesprávně použitý veřejný DNS resolver místo interního DNS,
- po opravě DNS nefunguje SFTP; SSH server naslouchá, ale firewall stále povoluje TCP/22 jen ze starého subnetu.

Odevzdání obsahuje root cause + důkazy, diagnostické příkazy v pořadí, bezpečnou nápravu, validační plán a vysvětlení, proč samotný ping nestačí.

### 4.A – Produkční incident po deployi

Student řeší dva současné symptomy:

- `app.school.cz` vrací `502 Bad Gateway`; DNS, TCP/443 a TLS fungují, ale Nginx proxy míří na `127.0.0.1:9000`, zatímco aplikace naslouchá na `127.0.0.1:8000`,
- monitoring nedosáhne na `/metrics`, protože port 9100 naslouchá jen na loopbacku; řešení musí bezpečně zpřístupnit metriky pouze monitoring hostu `10.40.5.20`.

Student musí navrhnout změnu, validaci i rollback. Automatické nápovědy u známkovaného challenge nejsou; knowledgebase je povolená jako dokumentace.

Poznámka: položky `teacher_key` v `practice.php` jsou **řešení/ověřovací klíče jednotlivých cvičení**, nikoli přihlašovací heslo učitele. Přístup do `teacher.php` chrání výhradně serverová proměnná `EDUCANET_TEACHER_EXPORT_KEY`.


## CSV export výsledků Extra challenge

Učitelský export je dostupný na `teacher.php`. Z bezpečnostních důvodů se nejprve musí na serveru nastavit proměnná prostředí:

```bash
EDUCANET_TEACHER_EXPORT_KEY="zvoleny-silny-klic"
```

Po přihlášení lze stáhnout:

- společný CSV export 3.A + 4.A,
- pouze 3.A,
- pouze 4.A.

CSV používá UTF-8 s BOM a středník jako oddělovač kvůli českému Excelu. Obsahuje sloupce: **Jméno studenta, Třída, Body, Maximum, Známka, Stav hodnocení, Odevzdáno, Extra úkol**.

Nové odevzdání ukládá `points`, `grade` a `graded_at` jako `null` a stav `awaiting_teacher_grading`. Dokud učitel práci neohodnotí, jsou body a známka v CSV prázdné a stav se zobrazí jako **Čeká na hodnocení**. Pokud je u záznamu vyplněný počet bodů, ale chybí známka, export ji umí dopočítat podle rubriky 25 bodů: 23–25 → 1, 20–22 → 2, 16–19 → 3, 12–15 → 4, 0–11 → 5.

Textová pole jsou před exportem chráněna proti CSV/Excel formula injection. Export vyžaduje aktivní učitelskou session a neposkytuje veřejné stažení bez přihlášení.

## Nasazení

1. Nahraj celý adresář na server s PHP 8+.
2. Webserver musí mít právo zapisovat do `storage/` a `uploads/`.
3. Otevři `index.php`.
4. Studenti vstupují kódem třídy:
   - 1.A Grafika (startovací modul): `1AGRAF`
   - 2.A Grafika: `RV3F4F`
   - 3.A: `75GXC9`
   - 4.A: `S9EG3J`

Pro integraci do existujícího přihlášení lze studentovi poslat přímo například:

`index.php?class=class_3a&student=Martin`

Pokud už aplikace zná jméno a třídu ze session, nahraď GET integraci v horní části `index.php` vlastní session logikou.

## Bezpečnost

- formuláře mají CSRF token,
- výstupy jsou escapované,
- upload kontroluje MIME přes `finfo`, povoluje PNG/JPG/WEBP/PDF a limit 12 MB,
- uploadované soubory dostávají náhodný název s neprováděcí příponou `.upload`,
- `storage/` a `uploads/` obsahují Apache `.htaccess` s `Require all denied`.

U Nginx doporučuji navíc zakázat přímý webový přístup k `/storage` a `/uploads`, případně oba adresáře přesunout mimo document root.

## Obsah vychází z diagnostiky

### 3.A
10 odpovědí, průměr původního testu 7,1/10. Nejčastější mezery: HTTPS, privátní IP rozsahy, SSH/SFTP, ping/ICMP a DHCP. Navazující blok proto začíná základy a přechází do řízeného troubleshootingu klienta, webové služby a SSH/SFTP.

### 4.A
Zatím 2 odpovědi, oba výsledky 9/10. Obsah je proto scénářový a posunutý k diagnostice služby: DNS A záznam, TTL/cache, loopback a bind adresy, routing, SSH klíče, oprávnění a vrstvený troubleshooting.

### 2.A Grafika
19 odpovědí. Nejčastější nástroje: Canva, Figma, Krita/GIMP a Photoshop. Výuka je postavená na jasném cíli, krátkém vysvětlení, vizuálním/praktickém postupu a vlastním tempu. Projekt cíleně procvičuje hierarchii, kompozici, grid, kontrast, typografii, barvy a export.

## Kontrola syntaxe

```bash
php -l index.php
php -l bootstrap.php
php -l modules.php
php -l practice.php
php -l teacher.php
php -l export_extra_csv.php
```

## Visual Knowledge Tour

Knowledgebase byla rozšířena na interaktivní **Knowledge Tour** pro všechny čtyři moduly (1.A Grafika, 2.A Grafika, 3.A SOSPS a 4.A SOSPS). Každé téma má vlastní obrazovku místo dlouhé textové stránky:

- přehled kapitol se serverově řízeným sekvenčním postupem a zamčenými budoucími lekcemi,
- časovou náročnost a úroveň tématu,
- krátký mentální model („jak o tom přemýšlet“),
- animované vizuální demo,
- krokový tutorial s ovládáním předchozí/další,
- část „Pozor na slepé uličky“ s typickými chybami,
- Deep Dive s původním plným vysvětlením z `modules.php`, praktickým příkladem a rozbalitelným hlubším výkladem,
- samostatný **Knowledge Check** na vlastní stránce s vizuálním zadáním, náhodným pořadím odpovědí a okamžitou zpětnou vazbou,
- předchozí/další téma a návaznost na praktický lab.

Síťová témata mají vizualizace subnetů, DNS/DHCP/TCP sekvencí, portů a služeb, routing flow, service bindingu, SSH klíčů, permissions a troubleshooting vrstev. Grafická knowledgebase používá vizuální plakátové ukázky hierarchie a typografie, grid, kontrast, RGB/CMYK, raster vs. vektor, exportní workflow a ověřování licencí assetů.

Progress Knowledge Tour se drží v PHP session konkrétního studenta/třídy. Každá lekce je sekvenční: vizuální model → simulace (pokud existuje) → krokový postup → deep dive → samostatný Knowledge Check. Knowledge Check se otevírá na vlastní čisté stránce bez scrollování dlouhé lekce. Další část ani další kapitolu nelze běžným průchodem otevřít před dokončením předchozího kroku. Progress není známka.

## Interaktivní Knowledge Lab – simulace

Knowledge Tour obsahuje **22 odlišných interaktivních simulačních scénářů**; 1.A používá stejné grafické jádro jako 2.A s jednodušším vedením. Jsou záměrně soustředěné do témat, kde manipulace s parametry a okamžitá vizuální odezva přináší největší výukový efekt. Simulace nejsou známkované; jejich skóre je motivační. Dokončení simulace se současně zapisuje do serverového learning progressu a odemyká další krok lekce.

Každá simulace používá stejný výukový cyklus:

1. **Předpověz** – student musí formulovat očekávání před experimentem.
2. **Experimentuj** – mění parametry nebo volí diagnostické příkazy.
3. **Čti důkaz** – vedle vizualizace se průběžně odděluje „Co víme?“ od „Co ještě nevíme?“.
4. **Udělej závěr** – student vybere technicky správnou interpretaci; systém vyžaduje nejen správnou odpověď, ale i dostatečnou evidenci.

Nápovědy mají tři úrovně. První je zdarma, druhá snižuje maximální motivační skóre o 1 bod a třetí o 2 body. Chybný pokus body neodečítá; cílem je bezpečné experimentování a pochopení, ne trest za chybu.

### 1.A / 2.A Grafika

- **Zachraň plakát: vizuální hierarchie** – živé ovládání velikosti titulku, informací, CTA a spacingu; současně se zobrazuje malý „pohled z dálky“.
- **Grid Lab** – počet sloupců, margin, gutter a snap-to-grid.
- **Contrast Lab** – živé barvy, velikost textu, orientační kontrastní poměr a grayscale kontrola.
- **Type Lab** – počet rodin písem, poměr headline/body a line-height.
- **Scale Lab** – raster vs. vektor při extrémním zvětšení.
- **Screen vs. print** – didaktická simulace rozdílu extrémně syté RGB barvy a tiskového náhledu.
- **Export Lab** – WebP/JPG/PNG, šířka a kvalita; orientační datová velikost a riziko artefaktů.

### 3.A SOSPS

- **Subnet Lab** – /25 vs. /24 a rozhodnutí lokální cesta vs. gateway.
- **DNS Cache Lab** – TTL, virtuální čas a stará vs. nová IP.
- **HTTPS Stack** – DNS → TCP → TLS → HTTP s možností izolovat konkrétní selhání.
- **Gateway Lab** – platná vs. nedosažitelná default gateway.
- **DHCP DORA** – server ON/OFF, správná VLAN a stav poolu; vizualizace DISCOVER → OFFER → REQUEST → ACK a výsledný `ipconfig`.
- **Ping ≠ služba** – samostatné řízení ICMP, HTTPS služby a firewallu TCP/443; student může vytvořit stav „ping nefunguje, HTTPS funguje“.
- **Incident „IP funguje, intranet ne“** – student si volí `ipconfig`, `ping`, `nslookup` a `Test-NetConnection`, dostává realistické simulované výpisy a z evidence hledá chybný DNS resolver.

### 4.A SOSPS

- **CIDR Planner** – sizing subnetu pro konkrétní počet hostů.
- **Longest Prefix Match** – vizuální volba nejkonkrétnější routy.
- **DNS Migration** – TTL před plánovanou migrací.
- **SSH Key Lab** – permissions, username a matching public key.
- **Permissions Lab** – princip least privilege na módu 640.
- **Service Binding** – přepínání `127.0.0.1`, `0.0.0.0` a konkrétní LAN IP spolu s firewallem; okamžitá ukázka lokální vs. vzdálené dostupnosti.
- **TCP Handshake** – listener ON/OFF a firewall `DROP / REJECT / ALLOW`; animace SYN, SYN-ACK a RST vysvětluje rozdíl mezi timeoutem a `connection refused`.
- **Produkční incident 502** – interaktivní on-call scénář. Student volí `curl`, `dig`, `ss`, kontrolu `proxy_pass`, routing a logy. Správný závěr se uzná až po získání klíčových důkazů a označení validačních kroků po opravě.

Simulace jsou implementované čistě v PHP/HTML/CSS/JavaScriptu bez externích knihoven a bez skutečného spouštění systémových příkazů. Terminálové výstupy jsou bezpečné didaktické simulace.


## Design Studio pro grafiku

Nová stránka `?view=graphics_studio` funguje jako návrhový sandbox před Canvou. Student si zde nastaví brief, headline/info/CTA, formát, počet sloupců, velikost textových úrovní, spacing a tři barvy. Živý náhled má volitelný grid, grayscale test a záměrný blur test pro simulaci rychlého pohledu.

Studio počítá orientační kontrast textu proti pozadí a současně hlídá čtyři základní kontroly: kontrast hlavního textu, rozdíl headline/body, minimální spacing a rozpoznatelnost akcentu. Nejde o tiskový preflight ani náhradu profesionálního color managementu; je to výuková vizuální kontrola.

Exportní část nabízí cíl `social / web / print`, PNG/JPG/WebP/PDF jako plánovaný výstup a kvalitu. Student může:

- stáhnout PNG náhled zkušebního návrhu,
- exportovat JSON konfiguraci rozhodnutí,
- zkopírovat textový checklist pro ruční přenos do Canvy.

Tím je explicitně oddělen **návrhový systém** od finální art direction: aplikace pomůže udělat a otestovat rozhodnutí, Canva je místo, kde student vytvoří skutečný plakát.

## 90 minut pro grafické třídy

### 1.A Grafika

- 0–8 min startovní test,
- 8–20 min vizuální Knowledge Tour,
- 20–37 min Design Studio,
- 37–45 min Canva skeleton,
- 45–53 min kontrola skeletonu,
- 53–75 min finální vizuál,
- 75–83 min Visual QA,
- 83–88 min export,
- 88–90 min mini-reflexe.

Pro 1.A nejsou v dodaných zdrojových datech vlastní diagnostické odpovědi. Modul je proto označen jako obecný startovací blok pro začátečníky, nikoli jako datově personalizovaná diagnostika.

### 2.A Grafika

- 0–10 min startovní test,
- 10–18 min cílená Knowledge Tour,
- 18–35 min Design Studio,
- 35–45 min Canva skeleton,
- 45–50 min návrat k briefu,
- 50–73 min art direction + finální vizuál,
- 73–82 min Visual QA,
- 82–88 min export preset,
- 88–90 min odevzdání + reflexe.

Obě grafické třídy mají navíc dobrovolný závěrečný challenge pro rychlejší. 1.A dělá 15–20min redesign do čtvercového formátu, 2.A 20–30min druhý art-direction směr a sociální adaptaci.

### Canva Transfer Guide (1.A a 2.A)

Design Studio nyní obsahuje sedmikrokový handoff do Canvy: dokument a rozměry, grid/vodítka, textovou hierarchii, barevnou paletu s kontrastem, exportní preset, 10bodový Visual QA checklist a finální porovnání Design Studio vs. Canva. Hodnoty se živě přebírají z aktuální konfigurace Design Studia. Student může kliknutím kopírovat HEX barvy a nastavení dokumentu.

Do posledního kroku lze lokálně načíst PNG/JPG/WebP export z Canvy. Aplikace zobrazí návrh vedle původního plánu, ověří poměr stran a orientačně zjistí přítomnost plánované palety. Soubor se tímto krokem neodesílá na server; slouží pouze k vizuální kontrole. Odevzdání zůstává v projektovém průvodci. Canva handoff je nyní sekvenčně zamčený. Serverová session eviduje dokončení jednotlivých kroků; lokální úložiště se používá jen pro pracovní stav formulářů/QA a rozepsanou reflexi. Finální Canva zadání se neodemkne před dokončením celého Design Studia.


## Sekvenční learning journey, XP a badge

Aktuální verze používá jeden jasný princip: **student vidí vždy aktuální krok; hotové kroky mají ✓ a budoucí kroky jsou zamčené**. Samotné přepsání URL neumožní přeskočit na budoucí Knowledge Tour kapitolu ani do grafického finálního zadání.

### Grafika 1.A / 2.A

1. dokončit startovní test,
2. projít tři povinné základní Knowledge Tours: hierarchie → kompozice/grid → kontrast,
3. dokončit Design Studio: brief → vizuální kontrola 4/4 → exportní preset,
4. projít všech 7 kroků Canva handoffu v pořadí,
5. teprve potom se odemkne finální dvouhodinové Canva zadání a odevzdání.

Visual QA krok vyžaduje všech 10 kontrolních bodů. Poslední porovnání vyžaduje lokálně načtený Canva export a obě reflexe. Odevzdávací formulář navíc serverově vyžaduje celý finální checklist.

### 3.A / 4.A SOSPS

1. dokončit startovní test,
2. projít případovou studii / briefing a potvrdit pochopení topologie a symptomu,
3. praktický lab se odemkne až potom; checkpointy jsou už z principu serverově sekvenční,
4. Extra challenge se odemkne teprve po dokončení celého hlavního labu.

Knowledgebase zůstává během labu referenční dokumentací; pokud student otevře konkrétní Knowledge Tour, musí danou lekci projít celou v pořadí.

### XP

XP nejsou známka. Jsou motivační vrstva za ověřené dokončení:

- +10 XP za unikátní správnou odpověď ve vstupním testu,
- +5 XP za vizuální model Knowledge Tour,
- +20 XP za dokončenou interaktivní simulaci,
- +10 XP za krokový tutorial,
- +5 XP za deep dive,
- +15 XP za správný mini-check,
- +25 XP bonus za kompletní Knowledge Tour lekci,
- +15 XP za briefing případové studie,
- +15 XP za každý správný checkpoint praktického labu + bonus za celý lab,
- Design Studio uděluje XP za každý ověřený krok a bonus za kompletní handoff.

Stejný ověřený krok lze na XP započítat pouze jednou, takže opakovaným klikáním nelze XP farmit. Každých 250 XP zvyšuje vizuální level.

### Badge

UI používá minimalistické badge: **První krok**, **Jistý start**, **První lekce**, **Průzkumník**, **Experimentátor**, **Knowledge Master**, **Design Ready** a **Troubleshooter**. Badge se odemykají automaticky podle skutečně dokončených milníků a zobrazují se v kompaktním panelu v horní navigaci.

Learning progress je oddělen podle třídy a jména/přezdívky v aktuální PHP session, aby si dva studenti na stejném zařízení nepřebírali XP průchod.

Nový endpoint `progress.php` řeší serverové ukládání kroků, XP, badge a validaci pořadí.

## Navazující blok č. 2 — další 2 × 45 minut pro každou třídu

Modul nyní obsahuje druhý, samostatný 90minutový blok pro všechny čtyři třídy. Otevírá se přes `?view=next_lesson` a na dashboardu se zobrazí jako **Navazující 2 hodiny**. Blok se odemkne až po dokončení prvního hlavního bloku (u grafiků Design Studio/Canva handoff, u SOSPS hlavní praktický lab).

Každý navazující blok má šest povinných kroků. Další krok je zamčený, dokud server nepotvrdí předchozí. Pokud krok obsahuje Knowledge Tour, nelze ho dokončit bez dokončení všech uvedených témat. Pokud obsahuje kontrolní otázku, musí být odpověď správná. Pokud obsahuje checklist, musí student potvrdit všechny povinné body. Za každý ověřený krok se připisují XP; za dokončení celého druhého bloku je navíc bonus 75 XP a badge **Navazuji**.

### 1.A Grafika — Typografie + obraz

Druhý blok navazuje na první plakát. Student prochází typografií a spacingem, cropem fotografie, typografickým skeletonem, thumbnail checkpointem, finální Canva realizací a preflightem. Nové Knowledge Tour: **Spacing a vizuální rytmus**, **Výřez fotografie a focal point**, **CTA** a **Preflight**. Rychlejší studenti na konci adaptují návrh do Story 1080 × 1920.

### 2.A Grafika — Malá kampaň ze stejného systému

Student z původního master plakátu vytvoří konzistentní systém a dvě adaptace: 1080 × 1080 a 1080 × 1920. Nová témata: **Mini vizuální systém**, **Photo treatment**, **Responsive adaptace** a **Prezentace procesu**. Blok končí export matrix a krátkou obhajobou design systému.

### 3.A SOSPS — Malá kancelářská síť

Navazuje na základní troubleshooting. Student pracuje s VLAN10 USERS, VLAN20 SERVERS, inter-VLAN routingem, ARP, NAT/PAT a service matrix. Nová Knowledge Tour: **VLAN**, **ARP**, **NAT/PAT** a **Service matrix**. Závěrečný incident vyžaduje doplnit nejmenší bezpečné firewall pravidlo pro HTTPS a zároveň zachovat blokované SSH pro běžné uživatele.

### 4.A SOSPS — Produkční change window

Navazuje na první produkční incident. Student připraví change plan a rollback, diagnostikuje reverse proxy/upstream, ověří TLS certifikát, koreluje monitoring a logy, provede validační matici a rozhodne o rollbacku při failure injection. Nové Knowledge Tour: **Reverse proxy**, **TLS certifikáty**, **Logy a monitoring** a **Change management**.

## Knowledgebase: řízená cesta vs. referenční knihovna

Knowledgebase má dva režimy:

- **Řízená cesta** — kapitoly se odemykají postupně pro systematické studium.
- **Referenční knihovna** — `?view=knowledgebase&reference=1`; student může během praktické práce otevřít libovolné téma, ale uvnitř konkrétní Knowledge Tour stále musí projít vizuál → simulaci (pokud existuje) → postup → deep dive → mini-check.

Navazující blok má vlastní sticky **Knowledge Dock**, který ukazuje pouze témata potřebná pro danou 90minutovou lekci. U každého tématu je stav `○` / `✓` a přímý odkaz do referenčního režimu. V horní navigaci se po odemčení studijních materiálů zobrazuje rychlý odkaz **Knowledgebase** a po dokončení prvního bloku také **Další 2 h**.

Nové datové soubory:

- `next_lessons.php` — obsah a sekvence druhých dvouhodinových bloků,
- `knowledge_extensions.php` — nové články Knowledgebase,
- `knowledge_tours_next.php` — vizuální/krokové Knowledge Tour k novým článkům.

## Knowledgebase katalog: vyhledávání, filtry a stavy materiálů

Referenční knihovna má nyní samostatný katalog nad všemi Knowledge Tour materiály. Student může fulltextově hledat v názvu, shrnutí, interním klíči i tematické oblasti a filtrovat bez reloadu podle:

- **třídy** — 1.A, 2.A, 3.A nebo 4.A,
- **tématu** — např. Typografie & layout, Barva & kontrast, DNS & DHCP, Routing & segmentace nebo Troubleshooting & provoz,
- **priority** — Povinné / Doporučené,
- **stavu** — Dokončené / Rozpracované / Nezačaté.

Klávesová zkratka `Ctrl/Cmd + K` přesune kurzor přímo do vyhledávání. Katalog okamžitě zobrazuje počet výsledků a při nulovém výsledku nabídne vymazání filtrů.

Označení **Povinné** se neudržuje ručně: sestavuje se automaticky z povinných Knowledge Tour v aktuální studijní cestě, praktických labech a navazující 90minutové lekci vlastní třídy. Ostatní články jsou označeny jako **Doporučené**. Stav **Dokončeno** a **Rozpracováno** vychází ze skutečného serverového learning progressu.

Po dokončení prvního hlavního bloku lze v referenční knihovně filtrovat a procházet i materiály ostatních tříd. Ty se zobrazují jako doporučené rozšiřující materiály a mají oddělený progress podle třídy. Filtrování samo o sobě nikdy neodemyká budoucí povinný krok; řízená cesta i sekvenční průchod uvnitř Knowledge Tour zůstávají zachované.

### Detailní karty materiálů

Každý materiál v katalogu má nově vlastní detailní kartu, aby student ještě před otevřením věděl, co přesně ho čeká. Karta zobrazuje:

- **odhad času** převzatý z konkrétní Knowledge Tour,
- **úroveň** materiálu,
- **návaznost** na předchozí materiál nebo označení startovního tématu,
- **2 hlavní cíle učení** odvozené z kroků dané tour,
- informaci, **co následuje potom**,
- stav Povinné / Doporučené / Rozpracováno / Dokončeno / Zamčeno,
- informaci o přítomnosti interaktivní simulace,
- dominantní tlačítko **Pokračovat**, které vede přímo na začátek konkrétní Knowledge Tour.

Pokud je materiál zamčený, karta místo tlačítka přímo vysvětlí, co je potřeba dokončit. Po otevření materiálu se nad samotnou tour zobrazí ještě samostatný **Lesson brief**: shrnutí, čas, úroveň, návaznost, až tři očekávané learning outcomes a tlačítko **Pokračovat v lekci**. Student tak nemusí začít dlouhým textem a vždy předem zná očekávaný výsledek lekce.

### Přístup ke Knowledge Base

Knowledge Base je dostupná **před testem i po jeho dokončení**. Jakmile student spustí startovní test, Knowledge Base se serverově zamkne až do dokončení nebo ukončení testu. Přímá URL během aktivního testu přesměruje zpět na test a otevřená Knowledge Base v jiné kartě průběžně kontroluje stav testu a při jeho spuštění se přesměruje také.


## Navigace, předmětová oprávnění a moderní testovací režim

Aktuální verze používá jednotné hlavní menu: **Přehled · Knowledgebase · Design Studio / Case study · Projekt / Praktický lab · Další 2 h**. Budoucí nebo nesplněné části se zobrazují jako zamčené, takže student vždy vidí, co existuje, ale zároveň přesně pozná aktuální další krok.

### Knowledgebase podle předmětu

Přístup není globální přes všechny třídy. Řídí se rodinou předmětu:

- **Grafika:** 1.A + 2.A. Student 2.A tedy může otevřít i základy z 1.A a student 1.A může jako rozšíření studovat 2.A.
- **Seminář OS a sítě:** 3.A + 4.A. Student může používat materiály obou ročníků stejného předmětu jako referenci.
- Grafici nevidí materiály SOSPS a studenti SOSPS nevidí grafickou knowledgebase.

Referenční knihovna standardně ukazuje všechny dostupné ročníky daného předmětu. Povinný/doporučený stav se stále počítá podle vlastní třídy studenta.

V katalogu je navíc sekce **Knihovna předmětu**, která ukazuje všechny připravené ročníky, počet témat a navazující bloky. Celkem je aktuálně připraveno **49 knowledge materiálů** napříč čtyřmi třídami; obsahová kontrola ověřuje vazby startovních testů, praktických úloh i navazujících dvouhodinových lekcí.

### Samostatné Knowledge Checky

Mini-test už není součástí spodní části dlouhé Knowledge Tour. Po dokončení deep dive se odemkne tlačítko **Spustit knowledge check**, které vede na vlastní stránku `?view=kb_quiz`.

Knowledge Check používá soustředěný layout:

- vlevo vizuální nebo interaktivní důkaz,
- vpravo jedinou otázku a odpovědi,
- bez dlouhého scrollování,
- při chybě postupnou nápovědu,
- při správném řešení vysvětlení a tlačítko na další materiál,
- +15 XP za správný check a +25 XP za kompletní lekci.

Během aktivního startovního testu zůstává Knowledge Base i Knowledge Check serverově zamčený, včetně kontroly již otevřených karet.

### Startovní test – náhodné odpovědi a omezení testových vodítek

Při každém spuštění startovního testu se vytvoří nové náhodné pořadí odpovědí pro každou otázku a toto pořadí se po dobu jednoho pokusu drží v session. Písmena A/B/C/D tedy neodpovídají stabilním interním klíčům správné odpovědi.

Testovací UI neukazuje původní klíč správné možnosti a při zpětné vazbě vypisuje přímo text odpovědi. Autorovací guard navíc kontroluje běžnou chybu **„nejdelší odpověď = správná“** a v případě jednoznačně nejdelší správné varianty vyrovná délku věrohodného distraktoru neutrálním kontextem.

CLI kontrola:

```bash
php tools/content_audit.php
```

Audit aktuálně kontroluje:

- existenci Knowledge Base tématu pro každou testovou otázku,
- existenci Knowledge Tour a Knowledge Checku pro každý materiál,
- vazby praktických a navazujících úloh do Knowledge Base,
- že po normalizaci není správná odpověď jedinou nejdelší variantou.

### Vizuální testovací obrazovka

Startovní test i Knowledge Check používají nový `assessment-mode`. Na desktopu je otázka navržená tak, aby se běžně vešla do jednoho viewportu:

- kompaktní progress rail,
- vizuální/interaktivní demo vlevo,
- 2×2 answer grid vpravo,
- vysvětlení nahrazuje odpovědi místo přidávání další dlouhé sekce,
- na mobilu se rozložení skládá do jednoho sloupce,
- klávesy **1–4** vybírají odpověď a Enter ji v hlavním testu odešle.

Vizuální zadání se přizpůsobuje tématu: grafika používá 3sekundový test, kontrast, grid nebo raster/vektor; síťová témata používají packet flow, routing, DNS evidence, service stack nebo SSH/auth cestu.

---

## Přihlášení školním účtem `@educanet.cz`

Student má dvě rovnocenné možnosti přihlášení:

1. **Google Workspace / Sign in with Google**,
2. **školní e-mail `@educanet.cz` + vlastní heslo**.

Obě varianty používají stejné přiřazení ke třídě a stejný studijní profil. Pokud student propojí Google i lokální účet se stejným studentem/třídou, aplikace používá společný kanonický learning profile, takže XP, badge a dokončené lekce nejsou rozdělené mezi dva účty.

### Google Workspace

Google varianta používá **Google Identity Services / Sign in with Google** pouze pro autentizaci. Aplikace nepožaduje Gmail, Disk, Kalendář ani jiné Google API scopes.

1. V Google Cloud / Google Auth Platform vytvořte projekt vlastněný organizací EDUCANET. Pro použití jen uvnitř Workspace lze publikum nastavit jako **Internal**.
2. Vytvořte OAuth 2.0 Client ID typu **Web application**.
3. Do **Authorized JavaScript origins** přidejte produkční origin aplikace, např. `https://learning.educanet.cz`.
4. Na serveru nastavte:

```bash
EDUCANET_GOOGLE_CLIENT_ID="xxxxxxxx.apps.googleusercontent.com"
EDUCANET_GOOGLE_DOMAIN="educanet.cz"
```

`Client secret` tato varianta Sign in with Google nepotřebuje. Server kontroluje podpis ID tokenu, `iss`, `aud`, expiraci, `email_verified` a Workspace claim `hd=educanet.cz`.

### Lokální účet e-mail + heslo

Lokální registrace je ve výchozím stavu zapnutá a přijímá pouze adresy ve školní doméně z `EDUCANET_GOOGLE_DOMAIN` (výchozí `educanet.cz`). Heslo se **nikdy neukládá v čitelné podobě**. PHP použije Argon2id, pokud je k dispozici, jinak aktuální `PASSWORD_DEFAULT`.

Doporučené produkční nastavení:

```bash
EDUCANET_LOCAL_AUTH_ENABLED=1
EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY=1
EDUCANET_GOOGLE_DOMAIN="educanet.cz"
EDUCANET_APP_URL="https://learning.educanet.cz"
EDUCANET_MAIL_FROM="learning@educanet.cz"
```

Při zapnutém ověřování e-mailu aplikace po registraci odešle jednorázový ověřovací odkaz platný 24 hodin. Pro reset hesla používá samostatný jednorázový token platný 60 minut. V úložišti jsou jen SHA-256 hashe těchto tokenů, nikoli samotné tokeny.

Odesílání používá standardní PHP `mail()`. Produkční PHP server proto musí mít funkční mail transport / relay. Pokud škola používá vlastní SMTP relay, nakonfigurujte jej na úrovni serveru/PHP. `EDUCANET_APP_URL` nastavte explicitně, aby odkazy v e-mailech vždy vedly na správnou HTTPS adresu.

Pro důvěryhodné lokální vývojové prostředí lze ověření e-mailu dočasně vypnout:

```bash
EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY=0
```

Na veřejném/produkčním serveru to **nedoporučujeme**, protože bez ověření by bylo možné zadat existující školní adresu bez prokázání přístupu k její schránce.

### Registrace, přihlášení a obnova

- registrační formulář vyžaduje jméno, školní e-mail, heslo a potvrzení hesla,
- heslo musí mít minimálně 10 znaků, alespoň jedno písmeno a jednu číslici,
- login má základní rate limiting proti opakovaným chybným pokusům; na produkci doporučujeme doplnit i rate limiting na reverse proxy/web serveru,
- neověřený účet se nepřihlásí, ale student si může poslat nový ověřovací odkaz,
- „Zapomenuté heslo“ odešle časově omezený reset link a odpověď je záměrně obecná, aby neprozrazovala existenci účtu,
- při úspěšném přihlášení se regeneruje session ID.

Lokální účty jsou v `storage/local_accounts.json.php`. Soubor je chráněný stejným PHP guardem a `.htaccess` jako ostatní storage soubory. Obsahuje pouze hash hesla a technická metadata účtu.

### První přihlášení a přiřazení ke třídě

- u Google Workspace účtu se aplikace nejdřív pokusí o jednoznačné spárování ověřeného školního jména s `student_directory.php`,
- u lokální registrace e-mail + heslo se jméno automaticky nepoužívá k přiřazení; student vždy jednorázově vybere třídu, zadá kód třídy a u tříd s diagnostikou vybere svůj profil,
- vazba identity na studenta/třídu je v `storage/student_accounts.json.php`,
- XP, badge a Knowledge Tour progress jsou v `storage/learning_profiles.json.php`,
- Google a lokální účet přiřazené ke stejnému studentovi používají stejný kanonický studijní profil.

Pro lokální vývoj lze explicitně povolit původní vstup bez účtu:

```bash
EDUCANET_DEV_BYPASS=1
```

Na produkci tuto proměnnou **nenastavujte**.

Veřejná stránka `?view=privacy` popisuje použití Google i lokální identity a způsob uložení hesel.

## Přehlednější kurz: 4 navazující lekce na třídu

Horní menu obsahuje novou položku **Kurz**, která otevírá `?view=course`. Student vidí všechny dvouhodinové lekce jako jednu mapu:

1. **Lekce 1** – původní hlavní 2×45min blok,
2. **Lekce 2** – dosavadní navazující 90min blok z `next_lessons.php`,
3. **Lekce 3** – nový 90min blok,
4. **Lekce 4** – nový 90min blok.

Lekce se odemykají postupně. Každá má vlastní harmonogram, Knowledge Dock, povinné checkpointy, XP a serverovou kontrolu pořadí. Data Lekcí 3–4 jsou v `extended_lessons.php`.

### Nové lekce

- **1.A Grafika**: Barva + typografický systém → Mini vizuální identita.
- **2.A Grafika**: UI hero sekce / responzivní layout → Motion storyboard pro sociální sítě.
- **3.A SOSPS**: DNS + DHCP jako služby → Monitoring + základní hardening.
- **4.A SOSPS**: Observability (logy, metriky, health) → Release drill (canary, stop conditions, rollback).

CLI audit `php tools/content_audit.php` nyní kontroluje i Knowledge Base odkazy a délkové vodítko odpovědí v těchto rozšířených lekcích.

## Kompaktní navigace a samostatné podstránky lekcí

Aktuální UI odděluje katalog, výuku a testování do samostatných obrazovek:

- `?view=knowledgebase` je pouze katalog/knihovna materiálů s vyhledáváním a filtry.
- `?view=kb_lesson&topic=...` je samostatná podstránka konkrétní Knowledge Tour.
- `?view=kb_quiz&topic=...` je samostatná focus stránka Knowledge Checku.
- `?view=test` je samostatná focus stránka vstupního testu.

Na desktopu jsou Knowledge Tour i testy navržené primárně do jednoho viewportu. Delší obsah (například Deep Dive) se posouvá uvnitř obsahového panelu místo toho, aby student scrolloval přes katalog, hlavičku a další nesouvisející bloky.

Hlavní menu je zjednodušené na pořadí **Přehled → Kurz → Knihovna → pracovní část → Projekt/Lab**. Nadpisy jsou úmyslně menší a hierarchie stojí více na kartách, stavu kroku a dominantním CTA než na velkých hero titulcích.

## Česká YouTube videa u všech 69 Knowledge Base materiálů

Každý Knowledge Base materiál má nyní vlastní **volitelnou českou YouTube kartu**. Externí video je doplněk pro studenta, který chce stejný princip slyšet nebo vidět ještě jiným způsobem; **není součástí podmínek pro dokončení lekce** a jeho nedostupnost nikdy neblokuje Knowledge Tour, simulaci, Knowledge Check, XP ani další postup.

Video karta zobrazuje:

- YouTube náhled (`i.ytimg.com`),
- název videa,
- jazyk `CZ`,
- délku / orientační délku,
- úroveň obtížnosti,
- krátký popis, co si z videa student odnese,
- přímé tlačítko pro otevření na YouTube v nové kartě.

Použité zdroje jsou kurátorované podle tématu. Jedno kvalitní české video se může záměrně objevit u více souvisejících materiálů (např. IP/DNS/NAT nebo Auto Layout/komponenty/design systém), aby nebyla knihovna zaplněná slabšími videi jen kvůli unikátnosti odkazu.

`learning_resources.php` obsahuje centrální knihovnu videí a mapování na jednotlivá KB témata. CLI audit nyní kontroluje, že každý z 69 materiálů má:

- `type = video`,
- `youtube_id`,
- označení `CZ`,
- `duration`,
- `level`,
- `description`,
- YouTube `watch` URL.

Kontrola:

```bash
php tools/content_audit.php
```

musí skončit hláškou, že prošlo i **Czech YouTube coverage**.

### Offline / blokovaný YouTube

Povinné části kurzu jsou i nadále plně lokální. Pokud školní síť blokuje YouTube, student pouze neuvidí externí thumbnail nebo video neotevře; všechny SVG diagramy, simulace, vysvětlivky, testy, XP a postupování zůstávají funkční.

## Assessment SVG 2.0

Startovní testy i samostatné Knowledge Checky používají nový datově řízený SVG renderer (`assessment_visuals.php`). Každý testový vizuál má jasný název principu, popis „Co právě sleduješ“, přehratelnou SVG animaci, interaktivní změnu stavu a panel „Co z toho můžu tvrdit?“ s interpretací důkazu. Síťové otázky používají konkrétní modely DNS, DHCP DORA, routingu, service stacku, SSH, diagnostických sond, packet capture a incident decision loopu. Grafické otázky používají modely hierarchie, kontrastu, typografie, grid/layoutu, raster/vektoru, zdrojů/licencí a práce s obrazem.

`php tools/assessment_visual_audit.php` kontroluje všech 57 startovních otázek i aktuálních 85 Knowledge Base materiálů a build selže, pokud některé téma skončí v generickém/nepodporovaném vizuálu nebo mu chybí název, princip, instrukce či evidence. Povinná výuka ani testy nejsou závislé na externím SVG/CDN.

## Full-width Knowledge lesson workspace (2026-09)

Knowledge lekce používají samostatný full-width pracovní režim navržený tak, aby na desktopu nebylo nutné scrollovat celou stránku:

- shell lekce využívá téměř celou šířku viewportu bez původního limitu 1180 px,
- cíl lekce a learning outcomes jsou v kompaktním vodorovném pásu,
- doporučená videa a zdroje se otevírají v překryvném panelu a neroztahují stránku,
- aktuální část Knowledge Tour využívá celý hlavní pracovní prostor,
- vizuální krok je na širokém displeji rozdělen na mentální model / interaktivní demo / interpretaci důkazu,
- Deep Dive využívá více sloupců místo jednoho dlouhého textového toku,
- knowledge check a pager zůstávají stále dostupné dole,
- na desktopu je zakázán scroll celé stránky; pokud je mimořádně dlouhý obsah, scrolluje pouze příslušný vnitřní panel,
- pro nižší notebookové viewporty se UI automaticky zkompaktní,
- mobilní/tabletová verze zůstává přirozeně vertikální a responzivní.

## Refinement KB lesson layoutu (září 2026)

Samostatná Knowledge Base lekce používá řízený pracovní rámec místo full-bleed layoutu. Na desktopu je obsah centrovaný do maximální šířky 1420 px s bezpečnými okraji 24–32 px a konzistentním 8px spacing systémem. Horní informace o lekci, cíle a zdroje mají jednotnou kartu a čitelnou typografickou hierarchii.

Vizuální krok je rozdělen do dvousloupcového workspace: vlevo mentální model a interpretace, vpravo hlavní interaktivní ukázka. Navigace kroků, Deep Dive a knowledge check používají stejný rytmus odsazení, radiusů a typografie. Celá stránka se na běžném desktopu nesnaží roztáhnout od kraje ke kraji; případný delší obsah se posouvá uvnitř pracovního panelu, nikoliv celým dokumentem.

## KB Lesson UX 3.0 – přirozený layout a funkční vizuální laby

KB lekce už nepoužívá pevnou výšku viewportu ani vnitřní `overflow:auto` pro hlavní výukový panel. Stránka má normální dokumentový tok a scrolluje se pouze tehdy, když obsah skutečně přesahuje výšku okna. Hlavní pracovní plocha má řízenou maximální šířku 1320 px, konzistentní spacing a jednoduchou hierarchii: kontext → krok výuky → interaktivní model → závěr.

Vizuální demo **Vizuální hierarchie** je nově živý design lab. Student může měnit velikost headline, sekundárních informací, CTA a spacing, přepínat tři předvolby a zapnout test malého náhledu. Výsledek se okamžitě přepočítává a rozhraní vysvětluje, zda je hierarchie plochá, použitelná nebo silná.

Stejný princip živého experimentu mají také:

- Grid Lab – počet sloupců, margin, gutter a přichycení ke gridu,
- Contrast Lab – barvy a živý kontrastní poměr,
- Raster/Vector Scale Lab – společný zoom a simulace pixelace rasteru,
- Export Lab – formát, rozměr, kvalita, orientační datová velikost a artefakty.

Ostatní vizuální modely (`flow`, `sequence`, `layers`, `ports`, `permissions`, `compare`, `subnet`, `assets`) jsou krokovatelné: student vidí vždy zvýrazněný aktuální krok a může model procházet tlačítkem **Další krok** nebo vrátit na začátek.

Nový audit `php tools/kb_visual_demo_audit.php` kontroluje všech aktuálních 85 Knowledge Base materiálů a selže, pokud některý používá nepodporovaný vizuální renderer.

## Automatický postup lekcí a XP feedback (září 2026)

Postupování už nepoužívá model „klikni na Vzít XP“. XP se připisují výhradně na serveru ve chvíli, kdy je krok skutečně dokončený a ověřený. API vrací detailní `awarded_events`, takže UI umí přesně zobrazit, za který krok byly body připsány.

### Knowledge Tour

- vizuální krok se dokončí automaticky po smysluplné interakci s demem; u krokovatelných modelů po dosažení posledního kroku,
- simulace se zapisuje po úspěšném dokončení simulace,
- postup krok za krokem se uloží po projití posledního kroku,
- Deep Dive se uloží po krátkém aktivním čtení otevřeného panelu,
- knowledge check se vyhodnotí na své vlastní stránce,
- další část se po uložení automaticky odemkne a otevře,
- v navigaci je u každého kroku vidět XP hodnota a stav dokončení.

### Strukturované dvouhodinové lekce

Checklistové kroky se automaticky odešlou po zaškrtnutí posledního povinného bodu. Quiz krok se vyhodnotí ihned po výběru odpovědi. Po úspěchu se stránka sama posune na nově odemčený krok. Všech 192 kroků napříč Lekcemi 2–9 má checklist nebo quiz trigger, takže není nutné používat samostatné tlačítko „Ověřit a pokračovat“.

### XP a badge notifikace

Vpravo nahoře se zobrazuje moderní animovaná toast notifikace s:

- přesnou hodnotou `+XP`,
- názvem kroku, za který byly XP získány,
- potvrzením, že byl postup uložen,
- samostatným vizuálním stylem pro nový badge,
- časovou linkou automatického zmizení,
- podporou `prefers-reduced-motion`.

Opakované kliknutí nebo reload XP znovu nepřidá; server používá `learning_award_once()` a každý odměňovaný krok má vlastní event key.

Kontrola automatického flow:

```bash
php tools/learning_flow_audit.php
```

Audit ověřuje odstranění claim-XP tlačítek z Knowledge Tour a strukturovaných lekcí, detailní XP události v API, automatické vizuální/deep dokončení, načtení Lekce 7 v progress endpointu a automatizační trigger u všech strukturovaných kroků.

# EDUCANET Learning Ecosystem v4 · studentský cockpit + 9 lekcí (září 2026)

Tato verze nahrazuje starší dashboard a rozšiřuje aplikaci z jednotlivých bloků na dlouhodobý studijní ekosystém.

## Nové moderní menu a šířka aplikace

- hlavní aplikační shell používá na širokém desktopu maximální šířku **1620 px**,
- navigace je umístěná v moderní sticky app-bar kartě s jasným aktivním stavem,
- pořadí zůstává konzistentní: **Přehled → Kurz → Knihovna → Studio/Lab → Projekt/Případ**,
- na menších displejích se navigace automaticky skládá do kompaktního menu,
- samotné čtecí/výukové bloky uvnitř širokého shellu dál používají řízenou šířku a spacing; 1620 px neznamená extrémně dlouhé řádky textu.

## Studentský dashboard / Learning Cockpit

Po přihlášení student vidí na jedné stránce:

- jméno, školní účet, třídu, level a celkový progress,
- **jeden doporučený další krok**, který respektuje odemykání kurzu,
- celkové XP a postup do dalšího levelu,
- počet dokončených lekcí a procento kurzové mapy,
- počet zvládnutých Knowledge materiálů,
- počet uložených testů a poslední výsledek,
- počet aktivních studijních dnů,
- **SVG graf XP za posledních 14 dní**: kumulativní křivka + denní XP přírůstky,
- přehled všech odznaků včetně zamčených budoucích milníků,
- horizontální roadmapu všech 9 lekcí s počtem dokončených kroků,
- seznam „Co už umíš“ z dokončených Knowledge Tours,
- historii startovních testů a souhrn praktických/grafických odevzdání,
- poslední XP události,
- rychlé odkazy do kurzu, Knowledge Base a pracovního prostředí.

Výsledky startovního testu se při přihlášení obnovují z chráněného serverového úložiště, takže dashboard a odemykání kurzu nejsou závislé pouze na jedné PHP session.

## 9 dvouhodinových lekcí v každé třídě

Každá třída má nyní **9 × 90 minut**. Lekce 8 a 9 jsou nové:

### 1.A Grafika
- Lekce 8: **Typografie I + Layout I** — typografická škála, delší text, whitespace a vertikální rytmus.
- Lekce 9: **Vizuální příběh + produkční workflow** — focal point, sekvence čtení, varianty, naming, verze a preflight.

### 2.A Grafika
- Lekce 8: **Typografie II + Design systém II** — responsive type, semantic tokens, komponentní varianty.
- Lekce 9: **Interakce II + profesionální handoff** — stavové modely, motion feedback, reduced motion, critique a handoff.

2.A staví přímo na principech z 1.A. Student neopakuje stejný materiál; základní koncept dostává vyšší úroveň použití a systémové souvislosti.

### 3.A Seminář OS a sítě
- Lekce 8: **IPv4 II: VLSM + síťové zóny** — efektivní subnetting, ACL/service policy a least privilege.
- Lekce 9: **DNS/DHCP II + service debugging** — TTL, lease lifecycle a evidence chain DNS → TCP → TLS → HTTP.

### 4.A Seminář OS a sítě
- Lekce 8: **Infrastructure as Code + configuration drift** — desired state, plan, review, apply a reconciliation.
- Lekce 9: **Performance + capacity engineering** — percentily, saturation, bottleneck, load test, headroom a scale decision.

4.A předpokládá síťovou diagnostiku z 3.A a posouvá ji do produkčního provozu a reliability engineeringu.

## Vertikální kurikulum a přístup k předchozím ročníkům

Knowledge Base je nyní striktně směrová:

- 1.A Grafika vidí pouze 1.A,
- 2.A Grafika vidí 1.A + 2.A,
- 3.A SOSPS vidí pouze 3.A,
- 4.A SOSPS vidí 3.A + 4.A.

Student tak může kdykoliv otevřít materiál z předchozího ročníku jako připomenutí, ale nevidí obsah budoucího ročníku. Povinná studijní cesta se vždy odvíjí od jeho aktuální třídy.

## 85 Knowledge materiálů

Knowledge Base nyní obsahuje celkem **85 materiálů**. Nových 16 témat má vždy:

- detailní článek,
- Knowledge Tour,
- interaktivní vizuální model,
- samostatnou simulaci,
- knowledge check,
- české YouTube video s náhledem, délkou, úrovní a popisem,
- návaznost v konkrétní 90minutové lekci.

Povinná výuka zůstává plně lokální a není závislá na YouTube ani CDN.

## Aktuální automatické QA

```bash
php tools/content_audit.php
php tools/kb_visual_demo_audit.php
php tools/assessment_visual_audit.php
php tools/learning_flow_audit.php
node --check assets/app.js
```

Aktuální očekávaný stav:

- **85 Knowledge Base materiálů**,
- **57 startovních otázek**,
- **9 lekcí v každé ze 4 tříd**,
- každá lekce 2–9 má přesně **90 minut**,
- **192 automatizovaných strukturovaných kroků**,
- české YouTube pokrytí všech 85 materiálů,
- žádný nový materiál bez interaktivního vizuálu, Knowledge Tour nebo knowledge checku.

# Učitelské rozhraní · ruční projektové hodnocení

`teacher.php` je nově plnohodnotný hodnoticí cockpit, ne pouze CSV export. Přístup je dál chráněný `EDUCANET_TEACHER_EXPORT_KEY`; volitelně lze nastavit `EDUCANET_TEACHER_NAME`, případně učitel zadá jméno při přihlášení. Toto jméno se zapisuje do auditní historie.

## Co učitel umí

- přepínat mezi 1.A Grafikou, 2.A Grafikou, 3.A SOSPS a 4.A SOSPS,
- hodnotit **individuální i skupinové projekty**,
- bodovat každé kritérium přímo podle projektové rubriky,
- sledovat živý součet bodů a automatický návrh známky,
- známku ručně přepsat (override se uloží do historie),
- uložit hodnocení jako **Koncept**, **Publikováno** nebo **Vráceno k dopracování**,
- zapisovat souhrnný komentář, silné stránky a konkrétní další krok,
- ukládat soukromou poznámku, kterou student nikdy neuvidí,
- vytvářet a upravovat týmy pro skupinové projekty,
- ohodnotit společnou skupinovou rubriku pouze jednou,
- pro každého člena týmu nastavit individuální korekci bodů, vlastní známku nebo osobní komentář,
- otevřít kompletní auditní historii každého hodnocení.

## Projektový katalog

Nový `project_assessments.php` obsahuje 16 připravených hodnocených projektů (4 pro každou třídu) s 73 rubrikovými kritérii. Každá třída má individuální i skupinové projekty. Rubriky jsou zaměřené na skutečný výsledek práce: brief/UX, hierarchii, technické zpracování, evidence, bezpečnost, validaci, rollback, dokumentaci a obhajobu rozhodnutí podle typu předmětu.

## Skupinové hodnocení

Skupinové hodnocení funguje ve dvou vrstvách:

1. společná rubrika, body, feedback a základní známka týmu,
2. volitelná individuální korekce konkrétního člena (`± body`, ruční známka, osobní komentář).

Student vždy vidí transparentně svůj výsledný počet bodů a případnou individuální korekci. Společná rubrika zůstává zachovaná jako evidence týmového výsledku.

## Auditní historie

Každé uložení vytváří nový záznam v `storage/project_grade_history.json.php`:

- datum a čas,
- jméno učitele,
- typ akce,
- změněná pole,
- snapshot výsledku po změně,
- číslo verze hodnocení.

Historie tedy zachovává změny bodů, rubriky, známky, statusu, komentářů i individuálních korekcí. Soukromé poznámky zůstávají dostupné pouze učiteli.

## Studentské výsledky

Student má novou položku **Výsledky** v hlavní navigaci a podstránku `?view=project_results`. Zobrazuje pouze:

- publikovaná hodnocení,
- nebo práci vrácenou k dopracování,
- body a známku,
- rozpad rubriky,
- silné stránky,
- doporučený další krok,
- veřejný komentář učitele,
- osobní komentář u skupinového projektu,
- případnou individuální bodovou korekci.

`private_note` se do studentského HTML nevykresluje. Koncepty učitele nejsou studentovi dostupné.

Dashboard navíc ukazuje počet projektových hodnocení a poslední publikované výsledky.

## Úložiště

- `storage/project_grades.json.php` – aktuální platná hodnocení,
- `storage/project_grade_history.json.php` – append-only auditní historie,
- `storage/project_groups.json.php` – týmy a jejich členové.

Všechny soubory používají stejnou PHP ochrannou hlavičku jako ostatní serverové storage v projektu.

## Audit

```bash
php tools/project_grading_audit.php
```

Audit kontroluje projektový katalog, unikátní ID, typy projektů, rubriky, maxima bodů, přítomnost individuálních i skupinových projektů v každé třídě a konzistenci uložených skupin a známek.

### Dokončení projektového hodnocení · detail výsledku a bezpečnost týmů

Projektové hodnocení je dotažené jako samostatný workflow pro učitele i studenta:

- stránka **Výsledky** u studenta je přehledový katalog hodnocení, ne dlouhý seznam všech rubrik,
- každé publikované/vrácené hodnocení má vlastní podstránku `?view=project_result&record=...`,
- detail obsahuje výslednou známku, body, rubriku, silné stránky, další krok, veřejný komentář, osobní týmový komentář a případnou individuální bodovou korekci,
- koncepty se studentovi nezobrazují,
- `private_note` zůstává čistě učitelské pole a studentský renderer jej nepoužívá,
- historie v učitelském rozhraní nově umí rozbalit konkrétní změny **předchozí hodnota → nová hodnota**,
- server odmítne situaci, kdy by jeden student byl současně ve dvou týmech stejného projektu,
- editace existujícího týmu zůstává možná, dokud tím nevznikne kolize členství.

Funkční QA ověřuje scénář: koncept je skrytý → publikované individuální hodnocení je viditelné → vznikají verzované historické záznamy → duplicitní členství ve skupinách je odmítnuté → společná týmová rubrika se zobrazí všem členům → individuální korekce bodů/známky/komentáře se aplikuje pouze správnému studentovi.

## Prestige badge + achievement systém

Gamifikace je rozdělená do tří vrstev:

1. **XP** – okamžitá odměna za ověřený krok. Připisuje se automaticky a zobrazí se jako toast notifikace.
2. **Achievementy** – častější průběžné milníky s viditelným progressem, například 250/1 000/2 500/5 000 XP, 5/15/30 Knowledge lekcí, 5/15 simulací, aktivní dny nebo projektová série.
3. **Badge / trofeje** – vzácné a prestižní. Neudělují se za běžné úkoly ani každou lekci.

### Vzácné badge

- **Level milestone** po každých 10 levelech (10, 20, 30, …; systém je připraven až do levelu 200).
- **Bezchybný test** – celý startovní test bez jediné chyby.
- **Challenge Distinction** – jednička z dobrovolné Extra challenge.
- **Masterpiece** – publikovaný projekt alespoň 95 % se známkou 1.
- **Triple Distinction** – tři publikované projekty za 1 s výsledkem alespoň 90 %.
- **Knowledge Grandmaster** – dokončení celé Knowledge Base aktuálního ročníku.
- **Course Mastery** – dokončení všech osmnácti lekcí aktuální roční cesty.

Staré drobné badge typu „první krok“ nebo badge za každou jednotlivou lekci se při načtení profilu automaticky přestanou počítat jako trofeje. Jejich motivační roli přebírají achievementy.

Dashboard zobrazuje vzácné trofeje odděleně od achievementů. Achievement karta ukazuje průběžný stav, cílovou hodnotu a progress bar. Nový achievement má vlastní fialovou notifikaci, zatímco vzácný badge používá výraznou zlatou trofejovou notifikaci.

Audit `php tools/gamification_audit.php` kontroluje, že běžné legacy badge nebyly znovu zavedeny, existuje level badge pro každý desátý level, speciální trofeje mají jasnou podmínku a achievementy mají kompletní metadata.

### Doporučená další gamifikační rozšíření

- **Sezónní zkoušky / Boss Battles** – 2–4 velké praktické zkoušky za rok kombinující více znalostí najednou; badge pouze za opravdu výjimečný výsledek.
- **Skill mastery mapa** – samostatné mastery levely pro Typografii, Webdesign, Diagnostiku, Networking, Deployment apod. namísto jednoho globálního XP čísla.
- **Questy mimo výuku** – dobrovolné několikatýdenní mise bez dopadu na povinné testy, např. redesign lokálního projektu nebo domácí síťový lab.
- **Portfolio showcase** – učitel označí mimořádný výstup jako Showcase; student získá speciální achievement a práce se může zobrazit v interní galerii.
- **Class milestones bez žebříčku** – společný cíl třídy (např. 500 dokončených Knowledge checků), který podporuje spolupráci bez veřejného porovnávání jednotlivců.
- **Mastery recommendation engine** – doporučení dalšího tématu podle slabších oblastí testů, historie pokusů a dokončených simulací.

## Student social layer, vzácné badge a Team Finder (v24)

### Badge vs. achievement

- **Badge = vzácná trofej**, nikdy odměna za každý běžný úkol. Automaticky se udělují po každých 10 levelech (`10, 20, 30 … 200`) a za výjimečné výkony: perfektní startovní test, výborný projekt, úplné zvládnutí Knowledge Base / kurzu a prestižní certifikační zkoušky.
- **Achievement = průběžný milník**. Sleduje XP, Knowledge Tours, simulace, aktivní dny, projekty, přátelství a týmovou spolupráci. Slouží jako motivace mezi vzácnými badge.
- Profil umožňuje vystavit maximálně **3 skutečně získané badge**. Nezískané trofeje nelze ručně přidat.

### Prestižní zkoušky

Nová sekce `?view=prestige_exams` používá `special_assessments.php`. Zkoušky se odemykají levelem (aktuálně Level 10 a Level 20), ukládají nejlepší výsledek a úspěšné dokončení dává jednorázové XP. Badge jsou navázané na certifikaci, perfektní výsledek a dokončení dvou různých zkoušek. Výsledky jsou v `storage/special_exam_results.json.php`.

### Studentský profil a přátelé

- `?view=profile` – vlastní / třídní profil: motto, krátké představení, dovednosti, zájmy, preferovaná projektová role, týmový status a badge showcase.
- `?view=community` – seznam spolužáků, žádosti o přátelství a odkazy na profily.
- Profily jsou **class-only**: spolužákům se nezobrazuje e-mail, známky, soukromé poznámky ani citlivé údaje. Systém nemá soukromé zprávy ani veřejné profily mimo přihlášenou třídu.
- Přátelství je oboustranné: request → accept/reject → remove. Křížové žádosti se automaticky potvrdí.
- Data: `storage/student_profiles.json.php`, `storage/friendships.json.php`.

### Group Projects / Team Finder

`?view=project_lobbies` přidává studentské sestavování týmů pro projekty typu `group`.

1. Aplikace vezme skutečný počet studentů ve třídě a vygeneruje maximálně 3 vyvážené varianty rozdělení do týmů po **2–5 lidech** bez jednočlenného zbytku.
2. Studenti mohou hlasovat pro preferovanou variantu. Hlasování je orientační a nevytváří tým automaticky.
3. Student založí lobby, vybere jednu z doporučených velikostí a získá náhodný **6znakový invite kód** bez osobních údajů.
4. Kód funguje pouze pro stejnou třídu a stejný projekt. Student nesmí být současně v jiném aktivním lobby ani oficiálním týmu stejného projektu.
5. Lobby lze uzamknout pouze po naplnění zvolené kapacity. Teprve potom se bezpečně vytvoří záznam v `project_groups.json.php`, který používá i učitelské hodnocení.
6. Zakladatel může invite kód regenerovat. Při odchodu zakladatele se ownership předá dalšímu členu; prázdné lobby se odstraní.

Aktuální automatické návrhy podle třídního adresáře:

- **2.A – 19 studentů:** doporučeno `1×3 + 4×4`, alternativně `5×3 + 1×4` nebo `1×4 + 3×5`.
- **3.A – 10 studentů:** doporučeno `2×3 + 1×4`, alternativně `2×5` nebo `2×2 + 2×3`.
- **4.A – 2 studenti:** `1×2`.

Data: `storage/project_lobbies.json.php`, `storage/team_plan_votes.json.php`, finální týmy `storage/project_groups.json.php`.

### Navržená další rozšíření

Další logický krok je učitelský „Team Control Center“: uzávěrka tvorby týmů, teacher lock, přetažení studenta mezi týmy s audit logem, automatické doplnění nezařazených studentů a export týmů. Později lze přidat projektové role/odpovědnosti, peer feedback po dokončení projektu, týmový activity log bez chatu, sezónní achievement kolekce a učitelem vyhlašované jednorázové prestige challenges. Automatické párování studentů podle „skóre kompatibility“ záměrně není součástí této verze; profil slouží k hledání lidí, ne k žebříčku studentů.

## Skill Trees & Mastery v25

Aktuální build obsahuje produkční kompetenční vrstvu pro Networking, Linux, Security, Design, Typography a UI/UX. Mastery je oddělená od XP a vzniká pouze z auditovatelné evidence (Knowledge Tour, quick check, praktická validace, projekt a mastery challenge). Součástí je unlock/dependency engine, anti-farming, mastery caps, studentský profil/showcase, Skill Assignments, Team skill coverage, učitelská heatmapa a validation queue, achievementy i prestige badge. Referenční MySQL 8 model je v `database/skill_trees_mysql.sql`; aktuální runtime zůstává zero-migration nad chráněným JSON storage. Detail změn je v `CHANGELOG_SKILL_TREES_V25.md`.


## v27 · Curriculum & Materials Pack

Kurz je nyní rozdělen do 18 dvouhodinových bloků na třídu:

- **1.A — Grafika a webdesign · základy:** vizuální principy, typografie, responsive layout, jednoduché komponenty, formuláře, UI kit a landing page capstone.
- **2.A — Grafika a webdesign:** UI/UX, informační architektura, Auto Layout, accessibility, handoff, CSS layout mindset, usability testing a design QA.
- **3.A — Seminář operačních systémů a počítačových sítí:** sítě + Linux CLI/filesystem, permissions, systemd, logy, SSH/SFTP, firewall, web services, shell/cron a kombinovaný incident.
- **4.A — Seminář operačních systémů a počítačových sítí:** produkční provoz + pokročilý systemd, storage, backup/restore, hardening, containers, automatizace, packet diagnostics a incident/reliability capstone.

Tisknutelné/pracovní podklady jsou v `materials/` — každá třída má `COURSE_MAP.md`, `TEACHER_GUIDE.md`, `STUDENT_WORKBOOK.md`, `ASSESSMENT_BANK.md` a `PROJECT_BRIEFS.md`.


## v28 · Teacher Curriculum & Readiness

Teacher Cockpit obsahuje nový tab **Výuka**. Pro každou třídu zobrazuje 18 lekcí, tematické etapy, automatickou readiness kontrolu lesson dat, ruční učitelský stav přípravy, Skill Mastery váhy, capstone, assessment model a přímé odkazy na materiály.

Checklist se ukládá pro konkrétní třídu a učitele. Každá třída navíc dostala `TEACHER_CHECKLIST.md` a `LESSON_READINESS_CHECKLIST.md`. Společné materiály jsou `materials/TEACHER_OVERVIEW.md`, `materials/ASSESSMENT_MASTERY_MATRIX.md`, `materials/PROJECT_ROLE_GUIDE.md` a `materials/CURRICULUM_OVERVIEW.csv`.

Po změně lesson dat lze materiály znovu sestavit příkazem `php tools/generate_teacher_curriculum_materials.php` a ověřit přes `php tools/teacher_curriculum_audit.php`.

## v31 · Adaptive Support bez dalšího menu

Dashboard má kompaktní panel `Dnes`: 3min spaced retrieval, Recovery Path jen při absenci a rozbalovací Learning Journal. Help Ladder + EDU Tutor jsou pouze uvnitř Knowledge Tour. Učitel má Intervention Feed v globálním dashboardu a třídní výjimky přímo v Kalendáři.

Pro aaPanel je doporučen `/www/server/educanet/educanet.secrets.php`; viz `ADMIN_ACCESS.md`.

## v32 · zjednodušení provozu

- `private/educanet.secrets.php` je součástí distribuce jako bezpečný template.
- `tools/install_teacher_secret.sh` vytvoří produkční teacher secret mimo web root.
- `tools/check_install.php` slouží jako deployment doctor.
- Teacher → Výuka má u všech 28 lekcí projektorový Lesson Mode.
- Student dashboard má volitelný Soustředěný režim.
- Studentský i učitelský kalendář podporuje `.ics` export.
- Course Mastery a lekční achievementy počítají všech 28 strukturovaných lekcí.


## v32.1 · ISPConfig + CLI hotfix

Pokud je aplikace nasazená v ISPConfig cestě jako `/var/www/clients/client10/web9/web/...`, instalační skript nyní automaticky uloží secret do `/var/www/clients/client10/web9/private/educanet.secrets.php`. `tools/check_install.php` je CLI-only nástroj; přes web už nespadne na `STDERR`, ale bezpečně zobrazí instrukci ke spuštění přes SSH.

---

## v33 · skutečný rozvrh, rychlejší runtime a testovací student

Středeční výuka je nyní v aplikaci pevně navázaná na reálný rozvrh:

- 1.A: 08:00–09:40
- 4.A: 10:00–11:40
- 2.A: 12:45–14:20
- 3.A: 14:30–16:05

Student vidí pouze vlastní kalendář a export cizí třídy je serverově zakázaný.

Po nasazení ověř výkon:

```bash
php tools/performance_check.php
```

Po změně lekcí / Knowledge Tours / simulací obnov runtime cache:

```bash
php tools/build_runtime_cache.php
```

Testovacího studenta vytvoř bezpečně až na serveru:

```bash
php tools/create_test_student.php --class=class_1a --email=demo.student@educanet.cz --name='Testovací student'
```

Heslo se bez parametru `--password` vygeneruje náhodně a vypíše pouze do CLI.

## v34 · Přehlednější studentské UX a skutečně lesson-specific animace

Od v34 už „Animovaná ukázka“ není generická sada karet. Každá Knowledge lekce používá konkrétní vizuální model daného tématu a strukturované lekce mají vlastní walkthrough až tří klíčových principů. Primární studentská navigace je zjednodušená na Přehled / Kurz / Kalendář / Třída / Více a dlouhodobá mapa 28 lekcí je skrytá pod progresivním detailem.

---

## v41 · Mastery Learning

Aktuální release přidává unified learning events, Misconception Engine, confidence calibration, fading worked examples, transfer challenges, interleaved retrieval, Explain-it-back, Error Journal, Browser Terminal, Visual Editor, Peer Instruction, Failure Simulator, Teacher Starter/Intervention Groups, Mastery Conference, Content Authoring Studio, moderovanou Class Knowledge Base, Portfolio Narrative a PWA/offline shell. Viz `RELEASE_V41.md` a `materials/MASTERY_LEARNING_V41.md`.

---

## v42 · Adaptive Lesson Kits

Každá strukturovaná lekce má vlastní balíček **video → prezentace → one-pager → worksheet → prompty → zkouška nanečisto**. Systém současně doporučuje jednu ze tří evidence-based cest: Více podpory, Standard, Větší výzva. Student může doporučení kdykoli změnit.

Učitel najde lesson kit přímo v `Teacher → Výuka → Spustit hodinu`, kde může otevřít fullscreen prezentaci a případně nastavit vlastní doporučené video pro konkrétní lekci bez editace PHP.

Audit:

```bash
php tools/v42_lesson_kit_audit.php
```

## v43 · Cognitive Visualization & Visual Labs

Každá z 112 strukturovaných lekcí má vlastní Cognitive Lab: prediction-first, X-Ray vrstvy, timeline scrubber, cause/effect manipulaci podle typu předmětu, contrast case, sestavení vlastního mentálního modelu, reverse engineering, transfer a memory snapshot. Teacher Lesson Mode má stejný model jako Live Explainer pro projektor.

Viz `RELEASE_V43.md` a `materials/SPEC_COGNITIVE_VISUALIZATION_V43.md`.

## v44 · Learning Studio & Visual Reasoning
Každá z 112 strukturovaných lekcí má navíc Learning Studio: Learning GPS, pět reprezentací stejného konceptu, Difference Lens, model PŘED/PO, Teach-back, analogy bridge, 90s Replay a osobní Memory Snapshot. Teacher Lesson Mode obsahuje Visual Reasoning Board s agregovaným bottleneckem třídy.


## EDUCANET v50.7.1 · Student UI Repair

Hotfix sjednocuje studentskou typografii, ikonografii, spacing, karty a responsivitu. Opravuje zejména dvousloupcový regresní layout Dashboardu, kolize CTA, nekonzistentní navigační ikony a rozdílné vizuální tokeny mezi staršími CSS vrstvami. Finální normalizační vrstva `assets/student-design-system-v50-7-1.css` se načítá jako poslední studentský stylesheet a PWA cache je bumpnuta.

## EDUCANET v50.7.3 · Content Density & Component Purge

Studentské rozhraní už aktivně neskládá sedm historických UX stylesheetů a pět globálních UI runtime skriptů. Aktivní student UI je konsolidované do `assets/student-ui-v50-7-3.css` a `assets/student-ui-v50-7-3.js`; starší soubory zůstávají pouze jako kompatibilní archiv pro historické audity. Guided Flow strip se už samostatně nevykresluje ani nenačítá, skrytý XP/Badge HUD, class chip a skryté Unicode ikony byly odstraněny přímo z DOM. Současně je zmenšena vertikální hustota Dashboardu, mapových stránek, disclosure panelů, empty states a Unified Page Shellu.

Audit:

```bash
php tools/v50_7_3_content_density_audit.php
php tools/v50_integration_contract.php
```

