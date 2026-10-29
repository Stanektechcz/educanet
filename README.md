# EDUCANET / Quiet Study

EDUCANET je školní aplikace pro výuku grafiky, webdesignu, operačních systémů a počítačových sítí ve třídách 1.A až 4.A. Quiet Study označuje studentské rozhraní s jedním doporučeným krokem a podrobnostmi na vyžádání.

Student v [index.php](index.php) najde lekce, kalendář, materiály, procvičování, projekty a vlastní pokrok. Učitel v [teacher.php](teacher.php) připravuje hodiny, spravuje přístupy, sleduje práci třídy a hodnotí projekty. Aplikace používá souborové JSON úložiště.

## Moduly

| Zdrojový kód | Obsah |
| --- | --- |
| [src/lessons/](src/lessons/) | Model a schvalování lekcí, hodiny, tutoriály, adaptivní podpora a vizuální laboratoře. |
| [src/content/](src/content/) | Obsahové soubory lekcí, základní kurikulum, zdroje a simulace. |
| [src/knowledge/](src/knowledge/) | Znalostní knihovna a řízené průchody materiály. |
| [src/teacher/](src/teacher/) | Učitelský cockpit, přípravy, účty, oprávnění a přehledy tříd. |
| [src/student/](src/student/) | Studentské rozhraní, účty, profily, portfolio, pokrok a zpětná vazba. |
| [src/assessment/](src/assessment/) | Testy, úlohy, kompetence, dovednosti, hodnocení a ŠVP. |
| [src/projects/](src/projects/) | Projektové pracoviště, týmy, evidence práce a recenze spolužáků. |
| [src/paths/](src/paths/), [src/levels/](src/levels/) | Výukové cesty, obsah a obtížnost aktivit. |
| [src/linux/](src/linux/) | Simulovaný Linux Lab, příkazy, úrovně a učitelské nástroje labu. |
| [src/arena/](src/arena/), [src/teamgames/](src/teamgames/), [src/robots/](src/robots/) | Aréna, týmové hry, kvízové banky a robotí liga. |
| [src/i18n/](src/i18n/), [src/ui/](src/ui/) | Překlady, navigace, motivy a sdílené prvky rozhraní. |
| [src/ops/](src/ops/) | Úložiště, cache, provoz, školní rok a kalendář. |

Knihovna a její verzované `*_views.php` patří do stejné oblasti. Názvy souborů zůstávají zachované. Čísla v názvech označují jednotlivé vrstvy aplikace, nikoli samostatná vydání celého repozitáře.

## Mapa složek

```text
./                  veřejné vstupy, bootstrap.php, PWA a dokumenty vydání
src/                interní knihovny, obsah a pohledy podle oblastí
app/                router, obsluha akcí a segmenty studentských stránek
assets/             CSS, JavaScript, obrázky a lokální knihovny
lang/               překladové katalogy a přiřazení zdrojů k doménám
materials/          výukové materiály a podklady pro učitele
storage/            provozní data aplikace
private/            neveřejné soubory; skutečné secrets patří mimo web root
uploads/            studentské přílohy
cache/              odvozená cache
migrations/         migrace dat
tools/              CLI správa, sestavení vydání a audity
docs/               provozní dokumentace, vzory nasazení a výsledky kontrol
```

Veřejné adresy zůstávají stejné: `index.php`, `teacher.php`, `calendar.ics.php`, `progress.php`, `export_extra_csv.php` a API `lab_v57_api.php`, `arena_v58_events_api.php`, `robots_v58_api.php`, `teamgames_v58_api.php`. V kořeni zůstává i obsahový soubor `practice.php`, `lab-offline.html`, `sw.js` a `manifest.webmanifest`. Studentské pohledy vybírá `?view=…`, učitelské záložky `teacher.php?tab=…`; registry jsou v [app/routes.php](app/routes.php) a [teacher_v58.php](src/teacher/teacher_v58.php).

## Instalace a lokální spuštění

Požadavky podle [INSTALL.md](INSTALL.md) a [check_install.php](tools/check_install.php): PHP 8.1 nebo novější s rozšířeními `json`, `session`, `fileinfo` a `mbstring`. Adresář `storage/` a při používání příloh také `uploads/` musí být zapisovatelné uživatelem PHP. Cache sestaví nástroj níže.

Z kořene projektu spusť:

```bash
read -rsp 'Dočasný učitelský klíč: ' EDUCANET_TEACHER_EXPORT_KEY
echo
export EDUCANET_TEACHER_EXPORT_KEY
php tools/build_runtime_cache.php
php tools/check_install.php
php -S 127.0.0.1:8090
```

Student otevře `http://127.0.0.1:8090/`, učitel `http://127.0.0.1:8090/teacher.php`. Učitelský formulář vyžaduje jméno a nastavený klíč. Bez nakonfigurovaného přístupu vrací učitelská stránka záměrně HTTP 503. Repozitář nemá výchozí statické učitelské heslo. Po ukončení serveru odstraň dočasnou proměnnou pomocí `unset EDUCANET_TEACHER_EXPORT_KEY`.

Pro testování použij kopii aplikace včetně kopie `storage/`. Proměnná `EDUCANET_STORAGE_DIR` přesměruje úložiště; sama nenahrazuje izolaci cache a ostatních zapisovaných souborů. Lokální vývojový vstup dovoluje `EDUCANET_DEV_BYPASS=1`; v produkci jej nezapínej. Linux Lab v57+ pracuje jako simulátor a nevyžaduje externí broker.

## Nasazení na aaPanel

Podrobný postup je v [NASAZENI_AAPANEL.md](docs/NASAZENI_AAPANEL.md), konfigurace v [docs/deploy/aapanel/](docs/deploy/aapanel/). Instalační hodnoty ve vzorech musí správce upravit pro svůj web.

1. Připrav PHP a zapisovatelné datové adresáře. Učitelský klíč nastav v prostředí PHP-FPM nebo v souboru mimo veřejný kořen podle [ADMIN_ACCESS.md](ADMIN_ACCESS.md); lze použít `EDUCANET_SECRETS_FILE`. Skutečné klíče nepatří do Gitu ani do vydání.
2. Nastav `EDUCANET_APP_URL` na veřejnou HTTPS adresu aplikace pro ověřovací e-maily a obnovu hesla. Zálohy nastav přes `EDUCANET_BACKUP_DIR` mimo veřejný kořen.
3. Sestav vydání: `php tools/build_release.php --out=<adresar-vydani> --lint --zip`. Před aktualizací zálohuj data a kód. Prohlédni náhled deploy skriptu; skutečné nasazení používá jeho přepínač `--apply`.
4. Nahraj také celou složku `src/`. Nginx nečte `.htaccess`: použij dodané pravidlo zakazující přístup do `src/` a pravidla pro ostatní interní adresáře. Apache musí respektovat kořenový i vnořené `.htaccess`.
5. Podle provozního návodu proveď migrace, sestavení cache, `php tools/check_install.php`, `php tools/preflight.php` a HTTP kontrolu. Obnov OPcache nebo reloadni příslušný PHP-FPM pool.

### Pravidla PATCH

PATCH nesmí přepsat ani smazat `storage/`, `private/`, `uploads/` ani `cache/`. Aktualizace kódu není obnova dat. Před použitím konkrétního build/deploy nástroje zkontroluj jeho filtry; sestavení vydání a PATCH nejsou zaměnitelné postupy. Migrace dat nebo sestavení cache je samostatný, vědomě spuštěný krok. Přesun PHP souborů nevyžaduje přesun studentských dat.

## Testy

Syntaxi všech PHP souborů ověříš z kořene projektu:

```bash
find . -type f -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

Souhrnný běh auditů: `php tools/run_audits.php --since=all`. Spouštěj jej v testovací kopii. Runner vytváří dočasné kopie úložiště; jednotlivé audity mohou navíc sestavovat cache nebo spouštět HTTP server. Přepínače popisuje [run_audits.php](tools/run_audits.php).

Pro porovnání při změně struktury slouží:

```bash
node tools/structure_http_2026_10_29.cjs before
node tools/visual_qa_2026_10_29.cjs structure-before
# Po úpravě kódu:
node tools/structure_http_2026_10_29.cjs after
node tools/visual_qa_2026_10_29.cjs structure-after
```

Oba nástroje vytvářejí izolovanou kopii aplikace a používají port 8090; spouštěj je postupně. Potřebují Chromium a existující instalaci Playwrightu, jejíž umístění určuje jejich import. V jiném vývojovém prostředí uprav tento import. Nejde o produkční závislost aplikace. HTTP kontrola porovnává stavy a přesměrování pro návštěvníka, studenta i učitele a kontroluje PHP log. Formuláře objevuje, jejich POST akce neodesílá. Vizuální test má 52 zobrazení a 72 interakcí ve světlém a tmavém režimu na šířkách 1440 a 390 px.

Mapa přesunů a výsledky kontrol: [STRUKTURA_2026-10-29.md](docs/STRUKTURA_2026-10-29.md).

## Changelog

Historická označení z původního README nepopisují samostatně aktuální stav aplikace. Aktuální provozní návody jsou ve složce [docs/](docs/).

| Verze / období | Změna |
| --- | --- |
| 2026-10-29 | Interní PHP moduly rozděleny do `src/`, veřejné URL zachovány. |
| v50.7.6 | Quiet Focus: jeden aktuální krok, sbalená upozornění a analytika až v dalším detailu. |
| v50.7.3, v50.7.2, v50.7.1 | Hustota obsahu, sjednocení komponent a opravy studentského rozhraní. |
| v50.4, v50.3, v50.2 | Volba studijního cíle, řízený průchod učením a jednodušší navigace. |
| v50 | Hands-on laboratoře, evidence práce, Skill Passport a dobrovolný Independent Growth. |
| v48.1, v48 | Praktické laboratoře pro 3.A a společný engine vizuálního učení. |
| v47.2, v47.1, v47 | Oprava chyb přenosem do nové situace, podpora studia a Learning Coach. |
| v46.2, v46.1, v46 | Učitelské provozní nástroje, přehledy, intervence a spolehlivost provozu. |
| v45.7–v45.2 | Uložené a sdílené filtry, hromadné akce, přehled tříd a výkon administrace. |
| v45, v44, v43, v42, v41 | Vizuální simulace, Learning Studio, vizualizace, adaptivní sady lekcí a Mastery Learning. |
| v35, v34, v33 | Praktické ukázky, studentské prostředí, skutečný rozvrh a runtime cache. |
| v32.1, v32, v31 | Instalace a CLI, ISPConfig, jednodušší provoz a adaptivní pomoc. |
| v30, v29, v28, v27 | Adaptivní učení, školní rok 2026/2027, učitelský přehled, připravenost lekcí a materiály. |
| v26, v25, v24 | Projektové pracoviště, týmové role, dovednostní stromy, mastery a sociální vrstva. |
| Starší rozšíření, září 2026 | Kurikulum pro čtyři třídy, znalostní knihovna, grafické studio, praktické úlohy, školní účty, export výsledků, XP a odznaky. |

## English summary

EDUCANET / Quiet Study is a PHP school learning application for students and teachers. Public entry points remain at the project root. Internal modules live in `src/`; student routing remains in `app/`. These instructions describe the repository, not a verified live deployment. Preserve runtime data and secrets during updates.
