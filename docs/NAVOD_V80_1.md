# Návod v80.1 · Mapa třídy, rychlá zpětná vazba a přidělení procvičování

Učitel vidí na jedné obrazovce, **kdo co umí**, a jedním klikem pošle vybraným žákům **zpětnou vazbu** nebo jim **přidělí procvičování**.
Žák to najde v **Můj pokrok** jako **Další krok „Od učitele“**. Přehled změn: `CHANGELOG_V80_1.md`, technické detaily: `BUILD_MANIFEST_V80_1.md`.

## Učitel

### Otevřít mapu
- Menu **Žáci › Třída a žáci › Mapa třídy** (nebo `teacher.php?tab=mapa801&class=class_3a`). Nahoře přepínač tříd (jen třídy ve vašem rozsahu).
- **Co zobrazit:** *Kompetence* (třídy zapojené do kompetencí – 1.A, 3.A a schválené 2.A/4.A) nebo *Úlohy s variantami*.
- **Jak číst buňku:** ikona + hodnota textem – ✓✓ umí jistě, ✓ umí, ◐ učí se, ! potřebuje pomoc, ○ bez dat. U kompetencí je číslo skóre z posledních 8 důkazů,
  u úloh úspěšnost pokusů. Barva pozadí je jen pomůcka. **Klik na buňku nebo jméno** otevře detail žáka.
- Patička tabulky: souhrn za třídu (kolik žáků umí / učí se / potřebuje pomoc / je bez dat). Sloupec **Od učitele**: kolik přidělení čeká a je hotovo, datum poslední zprávy.

### Filtrovat a řadit
- **Oblast** (Sítě, Linux, Grafika, Web …) zúží sloupce. **Lekce (rozsah)** – např. 5–8 – nechá jen kompetence, jejichž témata se v těchto lekcích probírají.
- **Řadit podle** jména, průměru nebo konkrétní kompetence / úlohy; **Vzestupně** = nejslabší první (žáci bez dat jsou vždy na konci). Totéž klikem na záhlaví sloupce.
- **Zobrazení: Tabulka (jen text)** – stejná data celými slovy, bez barev (pro tisk, čtečku obrazovky nebo projektor).
- Filtry jsou v adrese stránky (bez jmen žáků) – odkaz jde uložit do záložek. **Zrušit filtr** vrátí výchozí mapu.
- Jména žáků se řídí vaší volbou v **Nastavení výuky**: při skrytí uvidíte „Žák 1, Žák 2 …“ (pořadí podle jména).

### Poslat zpětnou vazbu
1. Zaškrtněte žáky v mapě (nebo **Vybrat všechny** / **Vybrat, kdo potřebuje pomoc** – žáci s aspoň jedním „!“). Počet vybraných se ukazuje nad formulářem.
2. V části **Rychlá zpětná vazba** vyberte frázi (*Pochvala*, *Další krok*, *Oprava*). Fráze se zkopíruje do pole **Text zprávy**, kde ji můžete upravit nebo napsat vlastní.
3. **Poslat zpětnou vazbu.** Žák zprávu uvidí v Můj pokrok › Od učitele přesně tak, jak je napsaná. Do zprávy nepište známky ani citlivé údaje.
- **Moje fráze:** pod formulářem si uložíte až 30 vlastních frází (a smažete je). Vidíte je jen vy.
- **Odesláno a přiděleno:** seznam zpráv s tlačítkem **Stáhnout zprávu** (žák ji pak už neuvidí).

### Přidělit procvičování
1. Zaškrtněte žáky. 2. V části **Přidělit procvičování** zvolte **Co procvičit**:
   - *Úloha s variantami* (každý žák má vlastní čísla), *Sada úloh* (všechny úlohy oblasti),
   - *Opakování chyb* – **balíček 3 nejslabších úloh každého žáka** (podle jeho výsledků) nebo konkrétní úloha; zařadí se do jeho opakování na dnešek,
   - *Interaktivní cvičení* (podsítě, chmod, barvy, specificita CSS).
3. **Přidělit procvičování.** Žák ho uvidí jako **Další krok „Od učitele“**.
- **Splnění se pozná samo** z výsledků žáka (vyřešené kolo úlohy, zopakovaná úloha, další vyřešené cvičení). V seznamu Odesláno a přiděleno je stav *čeká / hotovo* (u sad „N z M“)
  a tlačítko **Zrušit přidělení**. Žák má nejvýš 10 nesplněných přidělení – dalším se přeskočí (hláška to řekne).

### Z karty „Komu dnes pomoct“
- V **Dnešní hodině** má karta Komu dnes pomoct tlačítko **Poslat zpětnou vazbu nebo procvičování** – otevře Mapu třídy s těmito žáky už zaškrtnutými.

### Kdo smí co
- Zprávy a přidělení posílají **učitelé třídy** (oprávnění „správa žáků“). **Asistent** mapu vidí, ale nic neodesílá. Cizí třída → přístup odepřen.

## Žák

### Můj pokrok › Od učitele
- **Další krok · Od učitele** nahoře je nejstarší nesplněné přidělení – jedno tlačítko (Procvičit úlohu / Zopakovat / Otevřít cvičení).
- Sekce **Od učitele**: přidělené procvičování se stavem *čeká / hotovo* a zprávy učitele (Pochvala, Další krok, Oprava) s datem.
- Splnění se označí samo, jakmile úlohu nebo cvičení vyřešíš. Zprávy a přidělení vidíš jen ty a učitelé tvé třídy.

### Menu
- **Učení** má nově i **Interaktivní cvičení** a **Opakování chyb**. Na mobilu je najdeš v **Menu** vpravo nahoře.

## Správce
- Nová data: `storage/support_v80_1_<třída>.json.php` (zprávy a přidělení, bez jmen) a `storage/support_v80_1_phrases.json.php` (vlastní fráze učitelů). Zálohují se se `storage/`.
- Retence: po 180 dnech se mažou samy; po odchodu žáka `php tools/v58_retention.php --apply` (řádek `v80_1_data_purged=`).
- Kontrola: `php tools/v80_1_teacher_audit.php` → `V80_1_TEACHER_AUDIT_OK`.
