# EDUCANET v63 · Výukové cesty a moderní metody (fáze 2, 3.A a 1.A)

Žák vždy ví, co dělat dál a proč. **Cesta** je krátká posloupnost kroků k jedné kompetenci z katalogu v62:
vysvětlení → cvičení → ověření → reflexe. Každý hodnocený krok zapisuje důkaz do mapy kompetencí (v62), takže cesty
a profil „Umím / Učím se / Zatím ne“ ukazují totéž. Učitel cestu třídě přiřadí a vidí, kde žáci odpadají.

## Rozhodnutí školy (konstanty v kódu)

| Rozhodnutí | Hodnota | Kde |
|---|---|---|
| Q1 Katalog pro 1.A | předmět `grafika_web`, 8 kompetencí; pilot kompetencí = 3.A + 1.A | `competencies_v62.php` (`COMP62_PILOT_CLASSES`) |
| Q2 XP a body | cesty nedávají žádné XP ani body | audit kontroluje, že tok nezmění žádný jiný soubor úložiště |
| Q3 Reflexe | jedna věta, max 200 znaků, bez značek; vidí ji jen žák; nikdy do důkazů, exportu, logu ani URL; učitel jen souhrn | `P63_NOTE_MAX`, `p63_clean_note()` |
| Q3 Retence věty | maže se 30 dní po stavu left/archived spolu se stavem žáka | `P63_RETENTION_GRACE_DAYS`, `p63_retention_purge()` |
| Q4 Pokusy | nejvýš 3 pokusy ověření za den; opakování po 1 / 3 / 7 / 14 / 30 dnech | `P63_VERIFY_DAILY_LIMIT`, `P63_SPACED_INTERVALS` |
| Q5 Přiřazení | učitel přiřazuje v rozsahu svých tříd; žák smí i nepřiřazené cesty své třídy | politiky `p63_assign` / `p63_unassign` |
| Q6 Karta „Co dál“ | na přehledu přímo pod kartou „Teď“ | `app/views/dashboard.php` |

## Žák (jedna obrazovka)

1. Navigace **Dnes → Moje cesty** (nebo karta **Co dál** na přehledu) otevře seznam cest třídy: u každé cesty je cíl, čas, postup
   „Hotovo 3 z 6 kroků“ a seznam kroků se stavem textem i ikonou (Hotovo / Další krok / Zamčeno).
2. Kroky jdou po řadě: **Vysvětlení** (přečti), **Předpověz výstup** (predict–run–explain: vybereš, co příkaz vypíše, a uvidíš
   skutečný výsledek), **Poskládej pořadí** (Parsonova úloha tlačítky nahoru/dolů, funguje i bez JavaScriptu),
   **Vybavování** (4 otázky z banky), **Ověření** (4 otázky, jedna ze 3 variant; správné odpovědi se neukazují) a **Reflexe**
   (sebehodnocení 1–4 a jedna věta). Opakování (spaced) se nabídne, až je splatné.
3. Ověření jde nejvýš 3× denně; každý další pokus dostane jinou variantu. Kroky se odemykají postupně.
4. Karta **Co dál** vždy říká důvod a čas, např. „Je čas zopakovat cestu … · 3 minuty“. Pořadí doporučení: splatné opakování →
   rozpracovaná cesta → přiřazená cesta → nejslabší kompetence (podle mapy v62) → další cesta.

## Učitel (jedna obrazovka)

Cockpit → **Podpora → Výukové cesty**: přepínač tříd 1.A / 3.A (jen třídy v rozsahu učitele), pro každou cestu tlačítko **Přiřadit
třídě / Zrušit přiřazení**, počet žáků, kteří začali a dokončili, **trychtýř kroků** (kolik žáků krok zkusilo a splnilo, bez jmen)
a **průměrná kalibrace** (průměrný odhad žáků vs. výsledek ověření, od 3 reflexí; níže jen počet). Reflexní věty učitel nevidí.
Přiřazení smí jen role s oprávněním `content.manage` (asistent jen čte).

## Data a soukromí

| Soubor | Obsah |
|---|---|
| `storage/paths_v63/<student_id>.json.php` | `{v, paths:{cesta:{krok:{status,attempts,best,last_variant,at,verify_day,verify_count}}}, reflect:{cesta:{self,mastery_at_time,note,at}}, spaced:{kompetence:{due_at,interval_d}}, assigned:{cesta:čas}}` |
| `storage/paths_v63/assign.json.php` | `{třída:{cesta:{assigned_at,assigned_by_hash,open}}}` (hash učitele, ne jméno) |
| `storage/paths_v63/_funnel_<třída>.json.php` | jen počty a průměry bez jmen a bez vět; smazatelná cache |

- Důkazy jdou přes `ev62_append`: ověření = zdroj `test` (`p63:<cesta>:verify:v<n>`), ostatní hodnocené kroky = zdroj `lesson`
  (`p63:<cesta>:<krok>:a<pokus>`). Bez volného textu, bez jmen.
- Identita žáka je vždy ze session; cesta a krok se validují proti katalogu třídy (cizí třída, neznámý krok = 404 / odmítnutí).
- `tools/v58_retention.php --apply` maže stav odešlých žáků (souhrn `paths_v63_purged=N`).

## Metody (4 naplno)

| Metoda | Krok | Hodnocení |
|---|---|---|
| Retrieval practice | `retrieval` (a opakování `spaced`) | 4 otázky z poolu banky, úspěch od 75 % |
| Parsonovy úlohy | `parsons` | LCS/délka vůči správnému pořadí; 100 % jen přesné pořadí nebo akceptovaná alternativa |
| Predict–run–explain | `pre` | volba z nabídky; výstup je předpočítaný simulátorem (`paths_v63_pre_data.php`), za běhu se nic nespouští |
| Rozložené opakování | `spaced` | po splnění ověření se naplánuje 1 den, pak 3 / 7 / 14 / 30; neúspěch vrací na 1 den |

Debugging úlohy a peer review nejsou součástí v63 (peer review patří do fáze 4).

## Ukázkové cesty

- 3.A: `lnx_chmod` (práva a chmod, kompetence `lnx_users`), `net_dns` (DNS, `net_dns_dhcp`).
- 1.A: `web_html` (kostra stránky, `web_html_structure`), `gfx_contrast` (kontrast barev, `gfx_color_contrast`).

## Jak přidat cestu

1. Přidej záznam do `paths_v63_content_os.php` nebo `paths_v63_content_gfx.php` (schéma viz komentář v souboru a `paths_v63.php`):
   kroky `explain → … → verify → reflect`, každý s `competency` z katalogu třídy a `level` 1–4; ověření ≥ 3 navzájem různé varianty po
   4 otázkách z banky (id `net.*` / `gfx.*`, typ single/bool/numeric); pool vybavování a opakování ≥ 8.
2. U kroku `pre` nastav `setup`, `cmd`, `options`, `correct`, `expect`; pak `php tools/v63_paths_build_pre.php --apply`
   přepíše `paths_v63_pre_data.php` (výstupy z simulátoru, pevné semínko).
3. `php tools/v63_paths_audit.php` musí skončit `V63_PATHS_AUDIT_OK`.

## Ověření

`php tools/v63_paths_audit.php` (dočasné úložiště, fiktivní žáci, HTTP část nad vestavěným serverem) → `V63_PATHS_AUDIT_OK`.
Celá sada: `php tools/run_audits.php --since=all --with-smoke --with-router`.
