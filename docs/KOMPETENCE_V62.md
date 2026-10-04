# EDUCANET v62 · Kompetence a důkazy (fáze 1, pilot 3.A)

Páteř učení: každá aktivita (lekce, test, Linux Lab, hra, týmová hra, aréna, projekt) se mapuje na jeden katalog
kompetencí a vytváří stejný záznam důkazu. Z důkazů se počítá zvládnutí. Žák vidí mapu „Umím / Učím se / Zatím ne“,
učitel tutéž mapu za třídu. Vrstva nic nemění ve zdrojových datech – jen je čte.

## Rozhodnutí školy (konstanty v kódu)

| Rozhodnutí | Hodnota | Kde |
|---|---|---|
| Pilot | jen `class_3a` (3.A, OS a sítě) | `COMP62_PILOT_CLASSES` |
| Retence důkazů | do konce studia; smazání 30 dní po stavu `left`/`archived` | `COMP62_RETENTION_GRACE_DAYS` |
| Práh zvládnutí | 0,70 | `M62_THRESHOLD` |
| Poločas útlumu | 60 dní | `M62_HALF_LIFE_DAYS` |
| Počet posledních důkazů | 8 | `M62_LAST_N` |
| Váhy zdrojů | test, projekt 1,0 · lab 0,7 · hra, aréna 0,4 · lekce 0,3 | `M62_WEIGHTS` |
| Incidenty v58 | typ zdroje `lab` | `ev62_collect_arena_v58()` |
| Ruční přepočet (`comp62_sync`) | každý učitel s rozsahem na 3.A | politika `comp62_` |
| Žák vidí | jen sebe (záložka není mezi veřejnými záložkami profilu) | `profile60_public_tabs()` |
| Projekty | v MVP bez tagů → nepřiřazují se (WARN v auditu) | `ev62_collect_projects()` |

## Katalog (`competencies_v62.php`)

Strom předmět → oblast → kompetence, 11 kompetencí „Umím …“ pro předmět `os_site` (5× Sítě, 6× Linux), každá s cílovou úrovní
1–4 (pamatuje / použije / analyzuje / tvoří) a tagy `cmd:` (příkaz labu), `pack:` (balíček labu), `topic:` (téma znalostní báze
u testů a lekcí), `bank:` (rezerva), `tg:` (linka týmové hry). Aktivita se přiřadí kompetenci s nejvíce společnými tagy
(nejvýš 2); `COMP62_OVERRIDES` umožní ruční výjimku pro konkrétní artefakt. Úprava katalogu mění `comp62_catalog_version()`
a tím zneplatní cache zvládnutí. Názvy kompetencí pro žáka musí mít msgid v `comp62_label_msgids()` a překlad v `lang/{en,uk}/ui/competency.php`
(hlídá audit).

## Záznam důkazu (`evidence_v62.php`)

`storage/evidence_v62/<student_id>.json.php` = `{"v":1,"rows":[…],"meta":{"synced_at":…}}`. Řádek:
`{student_id, competency, level 1–4, source, score 0–1, at (ISO 8601), artefact_ref, k}`.

- **Append-only:** řádky se jen přidávají, existující se nikdy nemění. Duplicita (`sha1(source|artefact_ref|competency|at)`)
  se tiše přeskočí, takže opakovaná synchronizace i backfill jsou idempotentní.
- **Žádný volný text:** `artefact_ref` má pevný tvar (`lab:practice:start-1`, `test:<id>:<téma>` …), neplatná pole se odmítnou.
  Důkaz neobsahuje jméno, e-mail ani obsah práce; identifikuje žáka jen pseudonymem `student_id` (identita v58).
- Strop 5000 řádků na žáka. Zápis jen přes `storage_update`.

## Adaptéry (`evidence_v62_adapters.php`)

Jen čtení; nevolají `lab57_build_world`/relaci labu, žádné příkazy ani síť (hlídá token-sken v auditu).

| Adaptér | Zdroj dat | Typ zdroje | Skóre |
|---|---|---|---|
| `lab_practice` | `linux_v57/<třída>__<sha1>.json.php` → `solved.practice`, `solved.review` | lab | 1,0 − 0,1 za nápovědu (min. 0,5) |
| `v56_lessons` | `progress_v56.json.php` | lesson | přečtená teorie 0,5; test lekce = podíl správných po tématech |
| `practice_results` | proudy `practice_results`, `lab_results` | test / lab | podíl správných po tématech / podle pokusů a nápověd |
| `arena_v57_races` | `solved.race:*` | arena | jako lab |
| `arena_v58` | `solved.weekly:*`, `ctf:*` (aréna); `incident:*` (lab) | arena / lab | jako lab |
| `arena_v60` | `arena_v60_challenges.json.php` (souboje `done`) | arena | vítěz 1,0, druhý účastník 0,6 |
| `teamgames_v58` | `solved.tg:*` + dohrané relace (`teamgames_v58/*.json.php`) | game | úroveň jako lab; účast 0,6 (výsledek jednotlivce se neměří) |
| `projects` | `project_grades.json.php` (zveřejněné/vrácené) | project | body / maximum |

`ev62_sync_student()` má TTL 10 minut, mimo pilot je no-op, v režimu `EDUCANET_STORAGE_READONLY=1` nezapisuje
a žáka bez `student_id` přeskočí. Nenamapované aktivity (např. projekty bez tagů) se jen započítají do statistiky.

## Výpočet zvládnutí (`mastery_v62.php`)

Čistá funkce `m62_compute($rows, $now, $ids)`: posledních 8 důkazů kompetence (shoda času rozhodne klíč → deterministické),
váha zdroje × útlum `0,5^(stáří / 60 dní)`, vážený průměr skóre.

| Stav | Podmínka |
|---|---|
| neověřeno | žádný důkaz |
| rozpracováno | má důkazy, ale nesplňuje podmínky níže |
| zvládnuto | skóre ≥ 0,70, součet vah (bez útlumu) ≥ 1 a aspoň jeden důkaz, který není hra/aréna |
| upevněno | zvládnuto + dva různé typy zdrojů se skóre ≥ 0,6 s odstupem ≥ 7 dní, aspoň jeden z nich není hra |

Hra a aréna samy zvládnutí ani upevnění nikdy nedají. Hranice odstupu: 6 dní = zvládnuto, přesně 7 dní = upevněno.
Cache `storage/mastery_v62/` (žák i `_class_<třída>`, bez jmen) se zneplatní změnou důkazů, katalogu nebo dnem.

## Zobrazení

- **Žák:** profil → záložka *Kompetence* (jen vlastní profil a jen 3.A; `?tab=kompetence` jinde → Přehled). Tři skupiny, stav vždy
  textem a ikonou, krátká věta o tom, že hra zvládnutí nedá. Texty přes `tr()`, domény `competency` (+ `hubs` pro název záložky).
- **Učitel:** cockpit → Podpora → *Kompetence*: tabulka žák × kompetence (`<th scope>`, posuvný kontejner), souhrn třídy, tlačítko
  *Přepočítat důkazy třídy* (POST `comp62_sync`, CSRF, třída v rozsahu).
- CSS `assets/competency-v62.css` (3,4 kB, limit 8 kB), žádný JS.

## Provoz

- Zpětné doplnění: `php tools/v62_evidence_backfill.php` (výchozí náhled), `--apply` jen nad kopií (`EDUCANET_STORAGE_DIR`),
  ostrou `storage/` odmítne (kód 2). Výstup bez jmen. Poslední řádek `V62_BACKFILL_OK`.
- Retence: `tools/v58_retention.php [--apply]` volá `ev62_retention_purge()`; souhrn `evidence_v62_purged=N`. Žák bez `archived_at`
  se nemaže. Záznam v `ops58_retention_policy()` (akce `student_left`) je jen informativní náhled politiky.
- Audit: `php tools/v62_competency_audit.php` (dočasné úložiště, fiktivní žáci „Audit …“).

## Ochrana údajů (podklad pro DPIA)

Důkaz = pseudonym + kompetence + skóre + čas + odkaz na zdroj; žádný volný text, obsah práce ani jméno. Vidí ho žák (svůj)
a učitelé s rozsahem na třídu. Mazání 30 dní po odchodu/archivaci žáka. Žádné externí služby ani CDN.

## Výsledky ověření (běh 2026-10-04, nad kopiemi storage)

Viz `CHANGELOG_V62.md`, sekce Ověření (skutečné výstupy auditů).

## Známá omezení

- Projekty se v MVP nemapují (bez tagů), jen se hlásí jako WARN. Rubriky navázané na kompetence jsou fáze 4 (v65).
- Týmové hry měří jen účast týmu; individuální přínos se v nich zatím neukládá (fáze 3, v64).
- Stav lekčního testu v56 se ukládá jen jako poslední pokus; každý nově pozorovaný pokus (jiný čas) přidá nový důkaz.
- Opakovaná synchronizace čte proudy výsledků celé třídy; při velkém objemu dat doporučujeme jen ruční přepočet v cockpitu.
