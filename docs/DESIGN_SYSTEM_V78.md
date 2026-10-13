# Design systém EDUCANET v78 (počáteční verze, fáze A)

Jedna knihovna tokenů a komponent pro postupné sjednocení vzhledu cockpitu učitele i žákovské části.
Živá ukázka: **`teacher.php?tab=design78`** (Správa › Design systém, jen administrátor, fiktivní data, nic neukládá).

| Soubor | Obsah |
|---|---|
| `assets/tokens-v78.css` | jediný soubor s barvami, mezerami, typografií, breakpointy, stíny a pohybem (světlý + tmavý režim) |
| `assets/components-v78.css` | komponenty `.ds-*` (jen tokeny, žádné pevné barvy) |
| `assets/components-v78.js` | chování záložek, `<dialog>`/draweru a toastu (1,3 kB gzip, bez innerHTML) |
| `assets/design-v78.css` | jen ukázková záložka (vzorky barev, schéma rozvržení) |
| `design_v78.php` | čistá logika: načtení tokenů, dvojice pro kontrast, výpočet WCAG |
| `tools/v78_design_tokens_audit.php` | audit tokenů, kontrastu, barev mimo tokeny, písma, cílů dotyku, breakpointů a registrace záložky |
| `tools/v78_css_inventory.php` | inventura všech vrstev CSS → `docs/CSS_INVENTORY_V78.md` |

## 1. Rozsah a motiv
- Tokeny platí **uvnitř prvku s třídou `.ds78`** (ne na `:root`): starší vrstvy (`app.css`, `ui-v51.css`, `teacher-shell-v68.css`)
  mají vlastní `--bg`, `--text`, `--accent` … s jinými hodnotami. Stránka převedená na v78 dostane `.ds78` na kořenový prvek
  obsahu (fáze B: na `<body>`).
- Motiv řídí `<html data-theme>` stejně jako v67/v68:
  - `dark` → tmavé tokeny vždy (`:root[data-theme="dark"] .ds78`),
  - `system` nebo bez atributu → podle `prefers-color-scheme` (`@media (prefers-color-scheme: dark)`),
  - `light` → vždy světlé. Záložka mimo `UI68_TEACHER_DARK_TABS` / pohled žáka mimo `UI67_DARK_VIEWS` má vždy `light`.
- **Žák:** výchozí volba je „podle systému“ (`ui67_theme_pref()` bez cookie = `system`) – rozhodnutí školy: tmavý režim žáka
  se řídí zařízením, ruční přepínač (Světlý / Tmavý / Systém) zůstává. Platí pro pohledy převedené na tokeny (Domů, Přehled, Profil);
  další pohledy přibudou s převodem na v78.
- **Učitel:** cockpit je laděný pro **PC (lg 1024–1365 a xl 1366–1919)** – rozhodnutí školy, učitelé pracují hlavně u PC.
  Plně responzivní zůstává od 320 px.

## 2. Barvy – identita školy
Zdroj: veřejný web školy `brno.educanet.cz` (CSS šablony a logo, jen hodnoty barev; nic se nenačítá z cizího serveru).

| Barva školy | Hex na webu | Použití v78 |
|---|---|---|
| tmavě modrá (nadpisy, pozadí hlavičky) | `#0c113f` | `--text`, `--brand` (světlý) |
| modrá z loga | `#3d3c66` | `--brand-2` |
| tyrkysová (akcent) | `#00a8b9` (logo `#009ab1`) | na bílé jen 2,88 : 1 → ztmaveno na **`--accent: #00707d`** (5,7 : 1); v tmavém režimu zesvětleno na `#3cc8d6` |
| žlutá | `#fbb900` / `#f3b21f` | `--highlight` (odznak počtu) s tmavě modrým textem |
| oranžová | `#ec6b10` / `#eb5d0b` | základ pro `--warn: #8f4500` (na bílé 3,3 : 1 by nestačilo) |

### Tokeny barev (světlý / tmavý)
| Token | Světlý | Tmavý | Účel |
|---|---|---|---|
| `--bg` | `#f5f6fa` | `#0a0e2a` | pozadí stránky |
| `--surface` / `--surface-2` | `#ffffff` / `#eceef4` | `#121838` / `#1b2249` | karta / druhá plocha, hlavička tabulky |
| `--text` / `--muted` | `#0c113f` / `#4b5070` | `#eef0f8` / `#b4b9d3` | text / doplňkový text |
| `--line` | `#d3d6e0` | `#2e3561` | dělicí čára (jen dekorace) |
| `--border` | `#737896` | `#8a90b8` | rámeček pole a tlačítka (≥ 3 : 1) |
| `--accent` / `--accent-hover` / `--on-accent` / `--accent-soft` | `#00707d` / `#005a65` / `#ffffff` / `#e1f3f5` | `#3cc8d6` / `#6fd8e3` / `#03262b` / `#0e3540` | primární akce, odkazy, aktivní stav |
| `--brand` / `--on-brand` | `#0c113f` / `#ffffff` | `#1b2249` / `#ffffff` | barva školy (toast, hlavička) |
| `--highlight` / `--on-highlight` | `#fbb900` / `#0c113f` | stejné | zvýraznění (počet) |
| `--ok` / `--ok-soft` | `#1d6b3a` / `#e3f3e8` | `#6fd69a` / `#12331f` | hotovo |
| `--warn` / `--warn-soft` | `#8f4500` / `#fdf0e1` | `#f6b45c` / `#3a2810` | pozor |
| `--danger` / `--danger-hover` / `--on-danger` / `--danger-soft` | `#b02a1e` / `#8e2018` / `#ffffff` / `#fbe7e5` | `#ff9a8f` / `#ffbab2` / `#2a0605` / `#3d1717` | chyba, nevratná akce |
| `--info` / `--info-soft` | `#2a4f9c` / `#e6edf9` | `#9dbcff` / `#172a4d` | informace |
| `--focus` | `#00707d` | `#3cc8d6` | fokus (3 px, odsazení 2 px) |

Kontrast: **57 dvojic** (text ≥ 4,5 : 1, prvky UI ≥ 3 : 1) v obou režimech – nejnižší text 5,01 : 1, nejnižší UI 3,73 : 1
(seznam dvojic `ds78_text_pairs()` / `ds78_ui_pairs()`, tabulka v ukázce, kontrola v auditu).

**Výměna akcentu:** změňte jen `--accent`, `--accent-hover`, `--on-accent`, `--accent-soft` (a `--focus`) ve dvou blocích
(světlý + dvě shodné tmavé sady) a spusťte `php tools/v78_design_tokens_audit.php` – audit ohlásí každou dvojici pod limitem.

## 3. Ostatní tokeny
- **Mezery po 4 px:** `--space-1` … `--space-7` = 4, 8, 12, 16, 24, 32, 48 px.
- **Tvar:** `--radius-sm` 4, `--radius` 8, `--radius-lg` 12 px, `--radius-pill`; stíny `--shadow-1`, `--shadow-2`, `--shadow-up`.
- **Typografie:** systémová písma (`--font-sans`, `--font-mono`, žádný webfont), plynulé velikosti `clamp()`:
  `--fs-sm` 14–15 px, `--fs-base` 16–18 px, `--fs-lg`, `--fs-xl`, `--fs-2xl`; na xxl (≥ 1920 px) větší sada. Nikdy pod 14 px.
  Řádkování `--lh` 1,5, nadpisy `--lh-tight` 1,25.
- **Cíl dotyku:** `--target-min: 44px`, spodní lišta `--bottomnav-h: 64px`.
- **Pohyb:** `--dur-fast`, `--dur`, `--ease`; při `prefers-reduced-motion: reduce` = 0 ms (animace skeletonu, toastu a draweru vypnuté).
- **Breakpointy:** `--bp-xs` 320 · `--bp-sm` 480 · `--bp-md` 768 · `--bp-lg` 1024 · `--bp-xl` 1366 · `--bp-xxl` 1920 px,
  `--content-max` 1600 px. CSS proměnné nejdou použít v `@media`, proto audit hlídá, že soubory v78 používají v `@media`
  a `@container` **jen tyto hodnoty**.

| Token | Šířka | Zařízení | Rozvržení (`.ds-layout`) |
|---|---|---|---|
| xs | 320–479 | malý mobil | 1 sloupec, spodní navigace |
| sm | 480–767 | mobil | 1 sloupec, větší karty |
| md | 768–1023 | tablet na výšku | obsah + panel, navigace v draweru |
| lg | 1024–1365 | notebook | boční navigace + obsah (panel pod obsahem) |
| xl | 1366–1919 | PC – **výchozí cíl cockpitu** | boční navigace + obsah + kontextový panel |
| xxl | ≥ 1920 | monitor, projektor | obsah max. 1600 px, větší písmo |

Media dotazy jen pro rozvržení stránky; karty, tabulky a stepper reagují na šířku **kontejneru** (`@container`).

## 4. Komponenty
Všechny třídy fungují uvnitř `.ds78`. Selektory mají tvar `.ds78 .ds-…` (specifičnost 0,2,0 přebije starší `.teacher-app input` apod.).

| Komponenta | Značky | Poznámka |
|---|---|---|
| Karta | `article.ds-card` (+ `__title`, `__meta`, `__body`, `__body--split`, `__actions`) | container query 480 px: akce vedle sebe |
| Stavová karta | `.ds-card.ds-status.is-ok · is-warn · is-danger` (výchozí info) + `p.ds-status__label` s ikonou a textem | stav nikdy jen barvou |
| Tlačítka | `.ds-btn` + `--primary`, `--secondary`, `--danger`, `--ghost`, `--icon` (vždy `aria-label`), `--block` | ≥ 44 × 44 px, `disabled` / `aria-disabled` |
| Odznak | `.ds-badge` + `is-ok · is-warn · is-danger · is-info · is-accent`, `--count` (s `aria-label`) | text ≥ 14 px |
| Stepper | `.ds-stepper-wrap > p.ds-stepper__count („Krok 2 ze 4“) + ol.ds-stepper > li.ds-step.is-done · .is-current[aria-current=step]` | pod 480 px kontejneru jen aktuální krok s popiskem |
| Záložky | `[data-ds-tabs] > .ds-tabs__list[role=tablist] > button.ds-tab[role=tab][aria-controls]` + `.ds-tabs__panel[role=tabpanel]` | šipky, Home, End; bez JS jsou vidět všechny panely |
| Akordeon | `details.ds-acc > summary + .ds-acc__body` | nativní, bez JS |
| Tabulka → karty | `.ds-table-wrap > table.ds-table`, buňky s `data-label` | pod 768 px kontejneru řádky jako karty; hlavička jen pro čtečky |
| Pole formuláře | `.ds-field > label.ds-label + .ds-input + p.ds-help / p.ds-error` (`aria-describedby`, `aria-invalid`) | souhrn chyb `.ds-errors[role=alert]` s odkazy na pole |
| Zaškrtávátko | `label.ds-check > input[type=checkbox]` | celý řádek ≥ 44 px |
| Prázdný stav | `.ds-empty` (ikona, nadpis, proč je prázdno, tlačítko co dělat) | |
| Toast | `.ds-toasts[role=status][aria-live=polite]`, spuštění `data-ds-toast="text"` nebo `ds78Toast(text)` | 5 s nebo křížek |
| Dialog | `dialog.ds-dialog` (`__head`, `__body`, `__foot`), otevření `data-ds-open="id"`, zavření `data-ds-close` | Esc, klik na pozadí, fokus se vrací |
| Skeleton | `.ds-skeleton` (`--title`, `--short`) v kontejneru `aria-busy="true"` + skrytý text „Načítám…“ | |
| Drobečky | `nav.ds-breadcrumb > ol > li`, poslední `aria-current="page"` | oddělovač je pro čtečky neviditelný |
| Spodní navigace | `nav.ds-bottomnav > ul > li > a` (ikona + popisek, `aria-current="page"`), `--fixed` jen pod 768 px | aktivní = barva + proužek |
| Drawer | `dialog.ds-drawer` + `ul.ds-drawer__nav > li > a.ds-navlink` | md: navigace |
| Lepicí akční lišta | `.ds-actionbar[role=region][aria-label]` (+ `__info`) | drží se dole v rámci své sekce |
| Rozvržení | `.ds-layout > .ds-layout__nav / __main / __aside`, `.ds-container`, `.ds-stack`, `.ds-cluster`, `.ds-grid` | |

## 5. Dělej / nedělej
- **Dělej:** barvy jen `var(--token)`; nová barva = nový sémantický token v `tokens-v78.css` + dvojice v `design_v78.php` + audit.
- **Dělej:** stav vždy textem (a ikonou), barva je jen doplněk; ikonová tlačítka s `aria-label`; `type="button"` u tlačítek mimo formulář.
- **Dělej:** písmo přes `--fs-*`; interaktivní prvek min. `var(--target-min)`; nové breakpointy jen z tabulky výše.
- **Nedělej:** pevné hex/rgb v komponentách, `innerHTML` s daty, hover jako jediný nosič informace, písmo pod 14 px,
  vlastní `@media` šířky, webfonty a CDN.
- **Nedělej:** třídu `.teacher-page-head` uvnitř `.ds78` (starší vrstva nastavuje odstavci 12 px) – použij `header` + `.ds-eyebrow`.

## 6. Most ke starším vrstvám (dočasný, vyřadit ve fázi F)
`ui-v51.css` nastavuje polím a fokusu barvy s `!important` přes `--u-*`. Uvnitř `.ds78` jsou proto tyto proměnné přesměrované na tokeny
(`--u-text`, `--u-surface`, `--u-accent`, `--u-faint`) a fokus má `:root .ds78 :is(a, button, input, …):focus-visible`
s `!important`. Mimo `.ds78` se nic nemění.

## 7. Ověření (v78)
- `php tools/v78_design_tokens_audit.php` – 35 kontrol (tokeny, kontrast, barvy mimo tokeny, písmo, 44 px, breakpointy, rozpočty, registrace, HTTP).
- Prohlížeč (Chrome, Playwright): ukázka na 11 šířkách 320–1920 (+ 1366 × 600) ve světlém i tmavém režimu – bez vodorovného posunu,
  všechny cíle ≥ 44 px, žádné písmo pod 14 px, barvy komponent = tokeny (starší vrstvy je nepřebíjejí). Snímky `docs/screens-v78/`.

## 8. Obsahové vzory (v79.1, fáze D)
| Vzor | Značky / funkce | Pravidla |
|---|---|---|
| Hero karta hodiny | `section.ds78.lt791-hero` (`lesson_today_v79_1_views.php`) | třída a předmět v `h1`, lekce, `dl.lt791-facts` (Čas, Cíl, Stav jako odznak s textem), jedna primární akce + max. 2 sekundární |
| Pořadí obsahu hodiny | `.lt791-flow > .lt791-sec[data-lt791-sec]` | Plán po minutách → Materiály → Komu pomoct → Exit ticket → Poznámky, pak ostatní; na xl (≥ 1366 px) kontextový panel `.lt791-ctx` (`role=complementary`) vpravo přes CSS grid, bez duplicity v HTML |
| Sbalitelné sekce | `assets/today-v79-1.js` | jen pod 768 px, tlačítko v nadpisu s `aria-expanded`/`aria-controls`, stav v `localStorage` jen v `try/catch`; bez JS nebo úložiště je vše rozbalené |
| Tabulka s řazením | `dt791_table_html()` (`ux_v79_1.php`) | řazení, filtr a stránkování po 50 **na serveru** přes GET `?razeni`, `?smer`, `?filtr`, `?strana` (whitelist, neplatné hodnoty = výchozí); `caption`, `th scope`, `aria-sort`, `td[data-label]`; < 768 px (a v kontejneru < 480 px) karty, od 768 px lepicí hlavička a první sloupec |
| Prázdný stav | `ux791_empty_html($title, $why, $actions)` | nadpis = co tu bude, odstavec = proč je prázdno, tlačítko = co udělat (`role=status`); nebezpečné URL se vynechají |
| Ukazatel s pruhy | `ind791_bars_html()` | pruh jen dekorace (`aria-hidden`), hodnota vždy textem, tabulka pro čtečky se stejnými čísly; KPI dlaždice mají tón i textem („Pozor“, „V pořádku“) |
| Formulář | `fieldset` + `legend`, `.ds-help` pod polem s `aria-describedby` | na mobilu 1 sloupec; čísla `inputmode="numeric"`, e-mail `type=email autocomplete=email`, jméno učitele `autocomplete=username`, adresy `type=url` |

## 9. Mikrotexty a glosář (v79.1)
**Pravidla:** tlačítko začíná slovesem v infinitivu a říká výsledek (*Otevřít Přístupy*, ne *Přístupy →*); max. 3 slova; stejná akce má všude
stejný název; nadpis prázdného stavu říká, co tu chybí, druhá věta proč a co se stane; stav vždy textem, barva jen doplňuje. Bez vykřičníků a zdrobnělin.

| Akce / pojem | Používáme | Nepoužíváme |
|---|---|---|
| přejít na jinou záložku | **Otevřít …** (Otevřít Přehled třídy, Otevřít Přístupy, Otevřít Hlášení) | Přejít na…, Jít do…, šipka bez slovesa |
| projektor | **Spustit projektor** | Projektor, Promítat |
| tisk | **Vytisknout hodinu** / Tisk (plán + pracovní list) v odkazech | Tisknout, Print |
| příprava | **Otevřít přípravu hodiny** | Příprava → |
| filtr tabulky | **Filtrovat**, **Použít**, **Zrušit filtr** | Hledat (u tabulek), Reset |
| řazení | **Řadit podle**, **Směr** (Vzestupně / Sestupně) | Sort, Seřadit dle |
| počet řádků | **Zobrazeno 1–50 z 101** | Výsledky 1-50/101 |
| stránkování | **Strana 2 z 3** | Page, Další stránka » |
| schválení | **Schválit**, **Vrátit k úpravě**; stav *Návrh čeká na schválení* / *Schváleno* | OK, Potvrdit (u obsahu) |
| T-14 | **T-14: schválit do výuky** (lekce, které se učí do 14 dní) | dva týdny dopředu |
| Komu pomoct | **Komu dnes pomoct** (výběr podle signálů) | Problémoví žáci, Slabí žáci |
| exit ticket | **Exit ticket** (krátké ověření na konci hodiny) | Výstupní lístek, Kvízek |
| prázdno | *Třída zatím nemá žáky* · *Žádné nové hlášení* · *Filtru nic neodpovídá* | Nic tu není, Žádná data |
