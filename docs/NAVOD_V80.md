# Návod v80 · Podpora učení (žák a učitel)

Žák má nově jedno místo, kde vidí, **co už umí, co procvičit a jaký je jeden další krok**. K úlohám přibyly **odstupňované nápovědy**,
po chybě **vysvětlení konkrétní chyby**, chyby se **vracejí k opakování po 1, 3 a 7 dnech** a jsou tu čtyři **interaktivní cvičení**.
Krátké osobní cíle na týden a odznaky za zlepšení motivují bez žebříčku. Přehled pro správce: `CHANGELOG_V80.md`, technické detaily: `BUILD_MANIFEST_V80.md`.

## Žák

### Můj pokrok (menu Dnes › Můj pokrok)
- Nahoře je karta **Další krok** s jediným tlačítkem. Pořadí: nejdřív opakování chyb na dnes, pak rozpracovaná výuková cesta,
  pak úloha, která ti jde nejméně (nebo jsi ji ještě nezkusil/a), a nakonec cvičení, které jsi ještě nevyřešil/a.
- **Přehled** ukazuje pruhy s hodnotou vždy i textem (např. „3 z 7 typů vyřešeno“); „Zobrazit jako tabulku“ dá stejná čísla do tabulky.
  Oblasti: úlohy s variantami, kompetence (třídy s kompetencemi), výukové cesty (1.A, 3.A), exit tickety, interaktivní cvičení.
- **Co už umím / Co procvičit:** zvládnuté kompetence a úlohy s vysokou úspěšností; kompetence „učím se“, úlohy s nízkou úspěšností
  (odkaz rovnou na úlohu), lekce s chybným exit ticketem (odkaz na lekci) a počet úloh k opakování.
- Stránku vidíš jen ty a učitelé tvé třídy. Spolužáci ji nevidí, není tu žádné pořadí.

### Nápovědy a vysvětlení chyb (Úlohy s variantami)
- V kroku **Řešení** je blok **Nápověda**. Tlačítko „Ukázat nápovědu 1 ze 3“ odkryje postupně:
  1. **Postrčení** – kam se dívat, 2. **Postup řešení** – jak počítat, 3. **Vyřešený krok** – jeden krok spočítaný na tvých číslech.
- Nápověda nesnižuje body ani známku. Ukládá se jen **počet** použitých nápověd (učitel vidí číslo, ne co jsi psal/a).
- Po chybné odpovědi najdeš v kroku **Kontrola** blok **Kde je chyba** – vysvětlení konkrétní chyby u každého pole
  (např. „Tohle je broadcast – poslední adresa bloku“, „Číslice jsou obráceně“, „em se počítá z písma rodiče“). Tvoje odpověď se nikam neukládá.

### Opakování chyb (odkaz „Opakování chyb“ na stránce Úlohy a v Mém pokroku)
- Úloha, ve které se objevila chyba (i chyba v interaktivním cvičení), se vrátí **zítra**, po správné odpovědi **za 3 dny** a pak **za týden** –
  pokaždé s jinými čísly. Po třetí správné odpovědi je řada hotová. Když se spleteš, začíná znovu od zítřka.
- Na stránce Úlohy uvidíš upoutávku **„Na dnes k opakování: N“**. Každé opakování má jeden pokus denně a vlastní nápovědy.

### Interaktivní cvičení (odkaz „Interaktivní cvičení“ na stránce Úlohy a v Mém pokroku)
| Cvičení | Co děláš | Pomůcka v prohlížeči |
|---|---|---|
| Kalkulačka podsítí | určíš síť, broadcast a počet adres pro zadanou IP/prefix | zadáš prefix → hned vidíš masku, velikost bloku a krok v oktetu |
| Stavebnice oprávnění chmod | zaškrtáš r/w/x pro vlastníka, skupinu a ostatní | osmičkový i symbolický zápis se mění hned; osmičku můžeš i napsat |
| Míchání barev HEX a RGB | nastavíš R, G, B tak, aby vznikla zadaná barva | posuvníky i čísla, náhled barvy a HEX kód |
| Souboj specificity CSS | spočítáš (a,b,c) dvou selektorů a vybereš vítěze | z tvých trojic hned řekne, kdo by vyhrál |

- Odpověď vždy zkontroluje **server** (tlačítko Zkontrolovat); pomůcka jen počítá. Bez JavaScriptu funguje vše kromě živé pomůcky.
- Ovládání klávesnicí: Tab mezi prvky, mezerník zaškrtne, šipky posouvají posuvník, Enter odešle. Výsledky čte čtečka (aria-live).
- 3 pokusy na příklad; pak se ukáže řešení a dostaneš nový příklad.

### Cíle a odznaky (Můj pokrok)
- **Cíl na tento týden** vybereš z nabídky (např. „Vyřešit 3 varianty úloh“, „Zopakovat 1 položku z opakování“, „Vyřešit 2 interaktivní cvičení“,
  „Vyřešit 1 úlohu bez nápovědy“). Nejvýš 3 cíle na týden, plní se samy podle toho, co vyřešíš. Volný text se nepíše.
- **Odznaky za zlepšení:** Opravená chyba, Celá řada (1-3-7), Lepší než minulý týden, Zvládnuto samostatně (bez nápovědy po kole s nápovědou), Splněný cíl.
- Texty stránek jsou česky, anglicky i ukrajinsky (přepínač jazyka); zadání, nápovědy a vysvětlení chyb jsou výukový obsah a zůstávají česky.

## Učitel
- **Třída a žáci › Pokrok a podpora žáků** (`teacher.php?tab=pokrok80`) – jen čtení, jen třídy ve vašem rozsahu:
  souhrn (žáci s cílem, k opakování dnes, nápovědy, vyřešená cvičení), tabulka žáků (cíle a jejich plnění, odznaky, k opakování dnes,
  ve frontě, počet nápověd, cvičení) a **Kde žáci potřebují nápovědu** (součet nápověd po úlohách za třídu).
- Žádné odpovědi žáků ani pořadí. Jména podle volby v Nastavení výuky (skrytá jména = „Žák 1, 2 …“).
- Asistent vidí totéž (jen čtení). Záložka nemá žádnou akci – nic se tu nemění.

## Data a soukromí (pro správce)
- Úložiště po třídách `storage/learning_v80_<třída>.json.php`: plán opakování (úloha, stupeň, den), **počty** nápověd, stav cvičení,
  časy úspěšných opakování a cvičení, cíle (druh, číslo, týden). Klíč žáka = stejný pseudonym jako u úloh v75 (student_id), žádné jméno ani odpověď.
- Retence: záznam žáka bez aktivity déle než 365 dní se maže při zápisu třídy; po odchodu žáka (30 dní po left/archived) ho smaže
  `php tools/v58_retention.php --apply` (výstup `v80_data_purged=`).
