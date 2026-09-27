# Robotí liga (v58) – návod pro učitele a žáky

Robotí liga je programovatelná aréna (ROADMAP LAB-01, inspirace Screeps). Každý žák napíše krátký
skript v jazyce **RoboScript**. Skript po tazích řídí robota v simulovaném datacentru: robot sbírá datové
balíčky, vozí je na základnu, opravuje rozbité uzly a hlídá si baterii.

**Bezpečnost:** nic se nespouští doopravdy. Skripty čte a vyhodnocuje vlastní interpret EDUCANETU
(`robots_v58_lang.php`), který umí jen příkazy RoboScriptu. Nemá přístup k souborům, síti ani systému.

---

## Pro žáky

### Jak to funguje

1. Otevři **Robotí liga** (`?view=roboti`).
2. Načti ukázku (1 až 4, od nejjednodušší) nebo piš vlastní skript.
3. **Zkontrolovat** najde chyby a u každé ukáže číslo řádku a tip, jak ji opravit.
4. **Otestovat** pustí tvého robota na tréninkovou mapu (volitelně s cvičným soupeřem). Uvidíš záznam,
   statistiky a seznam chyb nebo varování z jednotlivých tahů.
5. Když učitel vypíše zápas, vyber ho a klikni na **Odevzdat do zápasu**. Počítá se tvůj poslední
   **platný** skript. Skript s chybou se neuloží a předchozí odevzdání zůstane.
6. Po zápase si pusť záznam a podívej se na výsledky a ligovou tabulku.

### Nejdůležitější pravidlo

Skript běží **každý tah znovu odshora**, dokud robot neudělá **jednu akci**. Pak tah končí. Když si
chceš něco zapamatovat do dalšího tahu, použij `keep`.

```
# Nejjednodušší sběrač
if here == "packet" and cargo < max_cargo:
    pick
elif cargo > 0:
    if here == "base":
        drop
    else:
        step_to nearest("base")
else:
    step_to nearest("packet")
```

### Tahák

| Co | Zápis | Poznámka |
|---|---|---|
| Pohyb | `move up` / `down` / `left` / `right` | stojí 1 energii |
| Cesta k cíli | `step_to cil` | jeden krok nejkratší cestou |
| Zvednout balíček | `pick` | náklad max 3 |
| Odevzdat | `drop` | jen na vlastní základně, +3 body za balíček |
| Opravit uzel | `repair` | musíš stát na uzlu, +2 body, 2 energie, uzel potřebuje 3 opravy |
| Nabít se | `charge` | jen na nabíječce, +20 energie |
| Čekat | `wait` | |
| Mluvit | `say "text"` | tah neukončí, max 40 znaků, hlídá ho filtr slušnosti |
| Senzory | `pos`, `pos.x`, `pos.y`, `energy`, `cargo`, `max_cargo`, `here`, `turn`, `turns_left`, `score` | `here` je "packet", "node", "charger", "base" nebo "empty" |
| Hledání | `nearest("packet")` | také "node", "charger", "base"; vrací pozici nebo `none` |
| Vzdálenost | `distance(cil)` | počet kroků, −1 = nedosažitelné |
| Pohled | `look(up)` | "wall", "edge", "robot", "packet", "node", "charger", "base", "other_base", "empty" |
| Ostatní | `count("packet")`, `random(6)`, `abs`, `min`, `max`, `point(x, y)` | |
| Podmínky | `if` / `elif` / `else`, `and`, `or`, `not` | porovnání `== != < <= > >=` |
| Smyčky | `while podminka:`, `repeat 3:`, `break`, `continue` | |
| Funkce | `def jmeno(a):` … `return a + 1` | volání max 8 do hloubky |
| Paměť | `keep cesty = 0` | nastaví se jen poprvé a přežije do dalšího tahu (max 16) |
| Komentář | `# text` | |

Čísla jsou celá (`7 / 2` je `3`). Text spojíš plusem: `say "Cesta " + cesty`.

### Limity

- skript max **4 KB** a 200 řádků, vnoření bloků max 8, max 32 proměnných
- **500 kroků** na tah (každý příkaz a každé kolo smyčky je jeden krok). Když se skript nezastaví, robot
  ten tah čeká a ty dostaneš varování. Po **3 přetečeních za sebou** se procesor přehřeje a robot 10 tahů
  chladne.
- text max 100 znaků, čísla max ±1 000 000 000

### Fér hra

- Mapa se zrcadlí podle obou os, takže všechny čtyři rohy (základny) jsou si rovné.
- Pořadí robotů se každý tah losuje ze semínka zápasu, takže nikdo není pořád první.
- Roboti se nemůžou poškodit ani nic ukrást. Do cizí základny se nevjede.
- Robot, který 3 tahy stojí na místě, je průhledný a nikoho neblokuje.
- Tvůj skript nevidí skripty ani paměť ostatních.
- Pod 5 energie se robot, který v tahu nic nespotřebuje, dobije o 1. Nikdy tedy nezůstane stát navždy.

### Proč jsou příkazy anglicky

Klíčová slova (`if`, `while`, `def`, `move`, `pick`…) jsou stejná jako v Pythonu, JavaScriptu a v Linuxu.
Co se tu naučíš, použiješ i jinde. Taky se píšou bez diakritiky a bez přepínání klávesnice. Všechny
vysvětlivky, chybové hlášky a tipy jsou česky. Odsazení bloků funguje jako v Pythonu (Tab v editoru
vloží 4 mezery).

---

## Pro učitele

### Založení zápasu (učitel → záložka Robotí liga)

1. Zvol třídu a vyplň **Nový zápas**:
   - **Režim:** *Každý sám*, nebo *Týmy* (2 až 4 týmy). Týmy se vyváží hadím draftem podle bodů z Linux
     Labu, stejně jako v Aréně. Každý tým má svůj roh mapy. Žák, který nebyl na soupisce, se při
     odevzdání přidá do nejmenšího týmu.
   - **Tahů:** 100 až 500 (doporučeno 300).
   - **Uzávěrka:** do té doby žáci odevzdávají.
   - **Jména:** jméno + iniciála (výchozí), celá jména, nebo anonymně („Robot 1, 2…“). Platí pro
     projektor i pro to, co vidí žáci.
2. Během přípravy vidíš stav odevzdání: čas, počet pokusů, velikost a platnost skriptu. Kód vidíš
   jen ty. Žáci cizí skripty nevidí ani po zápase.
3. **Uzavřít odevzdávání** ukončí přípravu dřív. **Spustit simulaci** zápas odehraje, typicky za méně
   než sekundu. Bez jediného odevzdání se simulace nespustí a příprava zůstane otevřená.
4. **Projektor (nové okno)** ukáže záznam s ovládáním přehrávání a tabulky výsledků. Dokud se nehraje,
   ukazuje počet odevzdaných skriptů a po spuštění simulace se sám obnoví.

### Bodování

- **Robot:** +3 body za každý doručený balíček, +2 body za každou opravu uzlu.
- **Tým:** součet bodů jeho robotů. Počítají se jen roboti, kteří odevzdali skript.
- **Liga (pololetí):** za každý zápas 1. místo 10 bodů, 2. místo 7, 3. místo 5 a každá další účast 3
  (v týmech rozhoduje umístění týmu). Při shodě rozhodují body robotů.
- **XP:** 20 za účast, navíc 30/20/10 za 1./2./3. místo (stejně jako Aréna). Žák si je vyzvedne sám
  při další návštěvě Robotí ligy. Každý zápas se započítá jen jednou.

### Nápady do hodin

- **1. hodina:** ukázka 1, změna jednoho řádku a pozorování záznamu. Úkol: „Ať robot vozí plný náklad.“
- **2. hodina:** energie a nabíječky (`energy`, `charge`), funkce `def`.
- **3. hodina:** paměť `keep` a strategie (opravy, nebo sběr?). Pak první zápas třídy.
- **Týmová hodina:** týmy se domluví na rolích (sběrač, opravář), každý odevzdá svůj skript.
- **Reflexe:** projektor, pauza v klíčovém tahu, diskuse „proč se tady robot zasekl?“.

### Soukromí a data

- Úložiště: `storage/robots_v58.json.php` (zápasy a výsledky), `storage/robots_v58/match_<id>.json.php`
  (odevzdané skripty), `storage/robots_v58/drafts_<třída>.json.php` (koncepty) a
  `storage/robots_v58/replay_<id>.json.php` (záznam). Všechny soubory mají ochranný první řádek.
- Klíče žáků zůstávají jen na serveru. Projektor a žáci dostávají jména podle zvoleného režimu soukromí.
- Smazání zápasu smaže i jeho záznam a odevzdané skripty.

### Kontrola

```
C:/php/php.exe tools/v58_robots_audit.php   # konec: V58_ROBOTS_AUDIT_OK checks=N failed=0
```

Audit běží v dočasném úložišti. Kontroluje bezpečnostní invariant, gramatiku a chybové hlášky, rozpočet
kroků, limity, determinismus, férovost mapy a pořadí, izolaci robotů, výkon, CSRF, soukromí na
projektoru, týmy, ligu a jednorázové vyzvednutí XP.
