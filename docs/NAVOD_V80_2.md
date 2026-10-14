# Návod v80.2 · Ověření přístupnosti, úklid CSS a stabilní měření výkonu

Vydání pro správce a vývoj – učitelé ani žáci nemusí nic dělat jinak. Všimnou si jen, že **drobné popisky jsou čitelnější** (nikdy pod 14 px),
šedé texty mají lepší kontrast a odkazy a tlačítka jdou snáz trefit prstem. Přehled změn: `CHANGELOG_V80_2.md`, detaily: `BUILD_MANIFEST_V80_2.md`.

## Co se změnilo pro učitele a žáky
- **Písmo:** štítky, záhlaví tabulek, čipy stavů a poznámky pod čísly mají aspoň 14 px. Tabulky cockpitu mohou být o něco širší – široké tabulky
  se posouvají ve svém rámečku, stránka nikdy do strany.
- **Kontrast:** doplňkový šedý text (data lekcí, čísla lekcí, „otevře se …“, úrovně v labu) je tmavší a čitelný i na projektoru.
- **Úlohy s variantami, portfolio a projekty** používají barvy design systému v78 – ve tmavém režimu zařízení jsou tmavé i karty úloh a projektů.
- **Úloha s variantou:** název úlohy je hlavní nadpis stránky (čtečka obrazovky ho ohlásí jako první).

## Ověření (správce, vývoj)
```bash
# jednotný audit přístupnosti a responzivity (28 stránek × 11 šířek × světlý/tmavý, reflow 400 %, Tab, kontrast)
EDUCANET_V802_BROWSER=require C:/php/php.exe tools/v80_2_a11y_audit.php
# obnovit reprezentativní snímky (docs/screens-v78/e802-*) a pak index
EDUCANET_V802_SHOTS=docs/screens-v78 C:/php/php.exe tools/v80_2_a11y_audit.php
# spodní hranice písma ve starších vrstvách: kontrola / použití po úpravě CSS
C:/php/php.exe tools/v80_2_font_floor.php --check
C:/php/php.exe tools/v80_2_font_floor.php
```
- Prohlížečová část potřebuje Chrome a `playwright-core` (`NODE_PATH`); bez nich se přeskočí (`=require` ji vynutí).
- Ladění: `EDUCANET_V802_ONLY=t-hodina,s-domu` (jen vybrané stránky), `V802_WIDTHS=390,1280`, `V802_DETAIL=1` (úplný seznam porušení
  se selektorem rodiče, přetékající prvky, pořadí fokusu), `EDUCANET_V802_RAW=<soubor.json>` (surová data).
- Co stroj neověří (NVDA, skutečný telefon, klávesnice „po smyslu“), projde člověk podle `docs/MANUAL_CHECKLIST_V80.md` (30–45 minut).

## Měření výkonu na vytíženém počítači
- `tools/v61_perf_audit.php` a `tools/v67_perf_audit.php` měří stroj **v každém měřicím procesu** (referenční stránka) a čas stránky normalizují.
  Výpis ukazuje surový čas, zpomalení stroje a normalizovaný čas, např. `79.8 ms (stroj 1.32× → 60.3 ms, limit 75.0)`.
- Při zpomalení nad 3× (přetížený počítač) audit měření neuzná a selže s poznámkou „PŘETÍŽENÝ – zopakovat“ – spusťte ho znovu při nižší zátěži.
- **Kontrola regrese** porovná poměr stránka / reference se základnou `tools/lib/v61_perf_baseline.php`. Základna platí jen pro počítač, kde vznikla;
  jinde se porovnání přeskočí (absolutní limity platí dál). **Po záměrné změně, která stránku zpomalí** (nová funkce), nebo na jiném počítači:
  `C:/php/php.exe tools/v61_perf_audit.php --update-baseline` (3 kola, ~5 min) a změnu základny zapsat do manifestu vydání jako změnu limitu.

## Pravidla pro další vývoj (shrnutí, podrobně `docs/DESIGN_SYSTEM_V78.md`)
- Nové styly jen na tokenech v78 uvnitř `.ds78`; písmo `--fs-*`, cíle `var(--target-min)`.
- Úprava starší vrstvy CSS: po ní `php tools/v80_2_font_floor.php --check`, `php tools/v68_tokenize_css.php --derive`,
  `php tools/v69_dark_overlay.php` a `php tools/v68_dark_overlay.php` (odvozené kopie a tmavé vrstvy) a audit `v80_2_a11y`.
- Vyřazení souboru jen postupem kopie → SHA-256 → unlink, po jednom, s tabulkou SHA v manifestu (viz `CLAUDE.md`).
