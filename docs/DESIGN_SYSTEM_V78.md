# Design systém EDUCANET v78 (fáze A–F, dokončeno ve v80.2; vizuální jazyk v81 v §13)

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

## 5. Dělej / nedělej (s příklady)
| Dělej | Nedělej |
|---|---|
| Barvy jen přes tokeny: `.ds78 .x { color: var(--muted); background: var(--surface-2); }` | Pevné barvy v komponentě: `color: #697381;` (kontrast i tmavý režim se rozbijí) |
| Nová barva = nový sémantický token v `tokens-v78.css` (světlý + obě tmavé sady) + dvojice v `design_v78.php` + `php tools/v78_design_tokens_audit.php` | Lokální odstín „jen pro tuhle kartu“ |
| Stav textem a ikonou: `<span class="ds-badge is-warn">Čeká na schválení</span>` | Stav jen barvou (zelená / červená tečka bez textu) |
| Ikonové tlačítko s názvem: `<button class="ds-btn ds-btn--icon" type="button" aria-label="Zavřít">×</button>` | Ikona bez `aria-label`, `type` chybí u tlačítka mimo formulář |
| Písmo `font-size: var(--fs-sm)` (≥ 14 px); u starší vrstvy `max(.875rem, …)` | `font-size: .72rem`, `<small>` jako jediný nosič informace, text < 14 px |
| Cíl dotyku `min-height: var(--target-min)` (44 px), u odkazu v kartě `display: inline-flex; align-items: center` | Odkaz 20 px vysoký mimo běžný text, zaškrtávátko 16 px bez popisku |
| Široká tabulka v rámečku `.ds-table-wrap` / `overflow-x: auto` (posouvá se rámeček) | Tabulka s `white-space: nowrap`, která posune celou stránku |
| `@media` jen se šířkami z tabulky breakpointů (320/480/768/1024/1366/1920), karty a tabulky přes `@container` | Vlastní breakpoint `@media (max-width: 640px)` v nových souborech |
| Fokus: nechat `:focus-visible` z tokenů (`--focus`, 3 px) | `outline: none` bez náhrady, hover jako jediný nosič informace |
| Text pro čtečky `.ds-sr`, nadpis stránky `h1` (může být vizuálně skrytý) | Stránka bez `h1`, přeskočená úroveň `h2 → h4` |
| Data jen přes `textContent` / `cloneNode` v JS | `innerHTML` s daty, webfonty, CDN, analytika |
- **Nedělej:** třídu `.teacher-page-head` uvnitř `.ds78` (starší vrstva nastavuje odstavci 12 px) – použij `header` + `.ds-eyebrow`.
- **Starší vrstva převedená na v78** (vzor `learn-v75.css`, `projects-v65.css` ve v80.2): kořenový prvek dostane třídu `ds78`
  (`<div class="l75-wrap ds78">`), selektory `.ds78 .l75-…` (specifičnost přebije `.ds78 p`, `.ds78 a`), `background: transparent` na kořeni,
  soubor se vyřadí ze zdrojů tmavé vrstvy v69 (`DO69_VIEWS`) a z odvozených kopií cockpitu (`TK68_DERIVED`), v cockpitu se k záložce přidá `tokens-v78.css`.

## 6. Most ke starším vrstvám (zůstává i po v80.2)
`ui-v51.css` nastavuje polím a fokusu barvy s `!important` přes `--u-*`. Uvnitř `.ds78` jsou proto tyto proměnné přesměrované na tokeny
(`--u-text`, `--u-surface`, `--u-accent`, `--u-faint`) a fokus má `:root .ds78 :is(a, button, input, …):focus-visible` s `!important`. Mimo `.ds78` se nic nemění.
**Proč nebyl ve fázi F vyřazen (v80.2):** `ui-v51.css` je dál načtený na **každé** stránce žáka (`app/views/_layout.php`) a v cockpitu jako
`cx-ui-v51.css`. Bez mostu by uvnitř `.ds78` vyhrálo `!important` z ui-v51: primární tlačítka modrá místo akcentu, fokus 2 px tyrkysový
`#007a87` místo `--focus` 3 px (ve tmavém režimu pod 3 : 1 na tmavém pozadí) a pole by nebrala tmavé tokeny v cockpitu. Most se vyřadí spolu
s ui-v51 (návrh sloučení č. 4 v `docs/CSS_INVENTORY_V78.md` – přepis pohledů žáka na v78 po jednom pohledu).

## 7. Ověření
- `php tools/v78_design_tokens_audit.php` – tokeny, kontrast dvojic tokenů, barvy mimo tokeny, písmo, 44 px, breakpointy, rozpočty, registrace, HTTP.
- **`php tools/v80_2_a11y_audit.php` (v80.2)** – jednotný audit hlavních stránek učitele i žáka: 11 šířek × světlý/tmavý (posun, 44 px, 14 px,
  kontrast vykresleného textu, `lang`, nadpisy, landmarky, tabindex, tmavý režim), reflow 200 % a 400 %, pořadí a viditelnost fokusu (Tab).
  Schválené výjimky jen ve `v802_exceptions()` (`tools/lib/v80_2_a11y_pages.php`) s důvodem v manifestu – ve v80.2 žádné.
- `php tools/v80_2_font_floor.php --check` – žádná deklarace písma pod 14 px ve starších vrstvách (mimo tisk, SVG a generované soubory).
- Ruční kontrola: `docs/MANUAL_CHECKLIST_V80.md` (klávesnice, NVDA, skutečný telefon a tablet). Snímky: `docs/screens-v78/INDEX.md`.

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

## 10. Vzory podpory učení (v80)
| Vzor | Značky / funkce | Pravidla |
|---|---|---|
| Jeden další krok | `section.ds-card.lr80-next` (`pg80_next_html`) | na stránce právě jedno primární tlačítko (`ds-btn--primary ds-btn--block`) s důvodem v jedné větě; starší patičkové „Pokračovat“ se na stránce nevykresluje |
| Pruh s hodnotou | `ul.lr80-bars > li.lr80-bar` (`pg80_bar_html`) | popisek + hodnota textem („3 z 7 …“), pruh `aria-hidden`; vedle `details.lr80-table` se stejnými čísly v tabulce (caption, `th scope`) |
| Odstupňovaná nápověda | `section.ds78.lr80-hints` (`lr80_hints_html`) | 1 postrčení → 2 postup řešení → 3 vyřešený krok; vždy tlačítko „Ukázat nápovědu N ze 3“ (POST), ukládá se jen počet; nápověda nesnižuje body |
| Vysvětlení chyby | `div.ds78.lr80-why[role=status]` | u každého chybného pole jedna věta o konkrétní chybě (ne „špatně“), bez opakování odpovědi žáka |
| Živá pomůcka | `output.lr80-out[aria-live=polite][data-t]` | text šablony z HTML (`tr()`), JS jen dosazuje čísla (`textContent`, zpoždění 120 ms); odpověď vždy kontroluje server |
| Odznak za zlepšení | `ul.lr80-badges > li.lr80-badge(.is-earned)` | ikona jen dekorace, stav textem („získáno“ / „zatím ne“) + jak ho získat; žádné pořadí ani srovnání |

## 11. Vzory podpory učitele (v80.1)
| Vzor | Značky / funkce | Pravidla |
|---|---|---|
| Mapa (heatmapa) | `.ds78.cm801 > .cm801-wrap[role=region][tabindex=0] > table.cm801-table` (`cm801_table_html`) | buňka `td.cm801-c.is-{strong,ok,mid,low,none}` = ikona (`aria-hidden`) + hodnota textem + skrytý popis úrovně; barva jen doplněk (`--ok-soft`, `--warn-soft`, `--danger-soft`); legenda nad tabulkou; `caption`, `th scope`, `aria-sort`, souhrn v `tfoot` |
| Lepicí sloupce | `thead th` sticky nahoře, výběr (`.cm801-sel`, 3rem) + jméno (`th[scope=row]`, left 3rem) sticky vlevo | obal má `position: relative` (skryté texty `.c70-sr` uvnitř nesmí roztáhnout stránku); bez výběru třída `no-pick` posune jméno na left 0 |
| Tabulková alternativa | GET `zobrazeni=tabulka` → `table.cm801-table.is-plain` | stejná data celými slovy bez barev |
| Výběr + akce | jeden `form.cm801-form` kolem mapy, akce tlačítky `name="action" value="…"` | zaškrtávátka v `label.cm801-check` (≥ 44 px), počet vybraných `[data-cm801-count]` s `aria-live`; JS jen pomáhá (vybrat vše, fráze do textu) |
| Sdílené značky menu | `<div class="nav61-drawer" data-nav61-skip data-nav61-head>` a `<div data-nav61-lang>` | obsah klonuje `assets/nav-v61.js` (`cloneNode`) z horní lišty a patičky; použít jen tam, kde kontejner bez JS stejně není dostupný |

## 12. Starší vrstvy a přístupnost (v80.2)
| Pravidlo | Kde | Poznámka |
|---|---|---|
| Písmo ≥ 14 px i ve starších vrstvách | `tools/v80_2_font_floor.php` (`font-size: max(.875rem, V)`), `assets/lab-floor-v80-2.css` (zamčené soubory labu), `--ui-text-xs: .875rem`, `small` v `app.css` | po úpravě CSS spustit `--check`; tisk (`@media print`) a SVG beze změny |
| Doplňkový text ≥ 4,5 : 1 | `--u-faint: #5f6b7a` (`ui-v51.css`) | tmavé vrstvy v69 a kopie cockpitu se přegenerují nástroji |
| Opravy cílů, kontrastu a fokusu starších tříd | `assets/a11y-v80-2.css` (poslední styl hlavičky žáka), `assets/a11y-cockpit-v80-2.css` (poslední styl rámce cockpitu, jen `#t68-main`) | jen existující proměnné (`--u-*`, `--ui-*`, `--edu-*`), žádné nové barvy |
| Tabulka vždy v rámečku s posunem | `.ds-table-wrap`, `.c72-scroll`, `.cm801-wrap` | stránka nikdy nepřeteče do strany (audit 11 šířek) |

## 13. Vizuální jazyk v81 (modernější, přehlednější, intuitivnější)
Nad tokeny v78, žádné nové barvy. Soubory: `ui_v81.php`, `assets/icons-v81.svg`, `assets/ui-v81.css` (sdílené), `assets/home-v81.css` (Domů žáka),
`assets/cockpit-v81.css` (cockpit), `assets/ui-v81.js`. Audit `tools/v81_visual_audit.php`.

| Prvek | Značky / funkce | Pravidla |
|---|---|---|
| Ikona | `i81('nazev')` → `svg.i81[aria-hidden][focusable=false] > use[href=assets/icons-v81.svg?v=…#i-nazev]` | jeden sprite, 24 × 24, tah `currentColor`; vždy vedle textu (nikdy jediný nosič informace); nový symbol = záznam v `UI81_ICONS` + `<symbol id="i-…">` |
| Hlavička stránky | `v81_head_html($title, $lead, $actions, $eyebrow, $icon)` → `header.v81-head.ds78` | `h1` + **jedna věta o účelu** (`.v81-head__lead`) + **nejvýš jedna primární akce** (další jako sekundární, max. 3); starší záložky: `.teacher-page-head` + třída `v81-legacy-head` |
| Stav | `v81_state_html('empty'|'ok'|'error'|'info', $title, $text, $actions)` | ikona + nadpis + proč + co dál; chyba `role=alert`, ostatní `role=status` |
| Nápověda při první návštěvě | `v81_hint_html($id, 'Jak to funguje', [2–4 body])` → `aside.v81-hint[hidden][data-v81-hint]` | až za `h1`; bez JS skrytá; JS ukáže, dokud ji uživatel nezavře (`localStorage` `v81-hint-<id>` = 1, `try/catch`); po zavření fokus do obsahu |
| Toast po POST | hláška s `data-v81-toast="ok|info|error"` (cockpit `teacher-flash`, žák `.notice`) | s JS oznámení v `.v81-toasts[role=status][aria-live=polite]`, 6 s, pauza při najetí/fokusu; bez JS zůstává hláška v textu; **chyba zůstává v textu i s JS** |
| Hustota tabulek | `v81_density_html()` + `v81_density_head_js()` (jen `UI81_DENSITY_TABS`) | skupina „Komfortní / Kompaktní“ s `aria-pressed`; `html[data-v81-density=compact]` zmenší jen svislé odsazení buněk (písmo ≥ 14 px zůstává) |
| Stavový štítek | `span.v81-chip.is-ok|is-warn|is-danger|is-info` | text + ikona, barva jen doplněk |
| Karta priority | `ol.v81-prio__list > li.v81-prio__item.is-…` | pořadí = naléhavost (Řešit hned → Pozor → Sledovat → V pořádku); nulové karty na konec |
| Pohyb | `.v81-lift` (zvednutí 2 px), `--v81-dur` 180 ms, `--v81-ease` | při `prefers-reduced-motion: reduce` 0 ms a bez transformací |
| Tvar a stín | `--radius-xl` 16 px, `--v81-shadow`, `--v81-shadow-hover` (tmavý režim vlastní hodnoty) | karty 16 px, tlačítka 12 px, štítky pilulka |

**Dělej / nedělej (v81):** na stránce **jedno** plné primární tlačítko (Domů: jen v kartě Dnes; karty Co dál / Výzva mají obrys) · ikonu vždy s textem ·
nová stránka žáka s v81 = přidat pohled do `UI81_STUDENT_VIEWS` a ověřit rozpočet `tools/v61_design_audit.php` (nezvyšovat – udělat místo) ·
**nedělej** série/„streaky“ na Domů, nápovědu viditelnou bez JS (nešla by zavřít), přechody delší než 200 ms, `localStorage` mimo `try/catch`.

### Ověření Quiet Focus 29. 10. 2026
Domů upřednostňuje kartu Dnes. Nápověda je nativně sbalená a funguje bez JavaScriptu.
První otevření progresu ukazuje lekce, témata a projekty; kruh, cíle a výzvy jsou pod `Další detail`.
XP v hlavičce jsou dostupné až v Menu účtu. Měsíce kalendáře podporují šipky, Home a End a mají propojené panely.
Výsledky a reprodukce: `docs/TEST_2026-10-29.md`.
