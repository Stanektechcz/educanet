# Týmové hry (v58) – návod pro učitele a žáky

Šest soutěžních režimů pro celou třídu najednou: **Štafeta**, **Příkazové bingo**, **Riskuj!**,
**Přetahovaná**, **Správci sítě/webu** a **Úniková místnost**. Síťová linie (3.A/4.A) řeší skutečné
mikroúlohy v Linux Labu (bezpečný simulátor – nic se doopravdy nespouští ani nepřipojuje). Grafická
linie (1.A/2.A) řeší kvízy a „rozbité stránky“ (kontrast, popisky obrázků, rozložení) bez síťové teorie.

**Cíl:** motivovat celou třídu, ne jen nejrychlejší. Proto: společný cíl třídy (např. „ať uniknou
všechny týmy“), osobní tempo v týmu, žádné veřejné pranýřování posledních, a jména na projektoru jen
podle nastavení učitele.

---

## Pro žáky

### Jak se zapojit

1. Otevři **Týmové hry** (`?view=hry`). Když učitel spustil hru pro tvoji třídu, uvidíš ji rovnou.
2. Na začátku (fáze *lobby*) čekáš, až učitel hru spustí – tehdy se sestaví týmy.
3. Za běhu (*running*) hraješ; učitel může hru kdykoli pozastavit (*paused*) a pak pokračovat.
4. Po konci (*finished*) vidíš výsledky. Odměnu (XP) si připíšeš automaticky při další návštěvě –
   jednou za odehranou hru, i když stránku obnovíš vícekrát.

### Společné věci ve všech hrách

- **Týmy:** učitel je sestaví hadem podle bodů z Linux Labu (vyrovnané), náhodně, nebo ručně. Kdo
  nebyl na soupisce, se automaticky přidá do zrovna nejmenšího týmu.
- **Soukromí jmen:** učitel zvolí, jestli se všude (i na projektoru) ukazují jen iniciály (výchozí),
  celá jména, nebo úplně anonymně („Hráč 1“, „Hráč 2“…). Na anonymní volbu se nedá přijít ani z adresy
  stránky.
- **Signály místo chatu:** aplikace nemá chat. Místo psaní použij tlačítka „Potřebujeme pomoc“, „Máme
  to!“ a „Zkontrolujte nás“ – učitel a spoluhráči je uvidí. Mezi dvěma signály od stejného týmu musí
  uplynout aspoň 15 s, ať tabule signálů nezahltí jeden zbrklý klik.
- **Body a XP:** za samotnou účast v dohrané hře dostaneš **15 XP**, vítězný tým navíc **10 XP**.
  Připíše se to jen jednou za hru, žádné body za cizí kód nebo cizí odpověď.

### Štafeta

4 úseky za sebou – tým je dokončí jen v pořadí 1 → 2 → 3 → 4, kód/odpověď z jednoho úseku otevírá
další. V síťové linii hledáš a opravuješ soubory v terminálu; v grafické odpovídáš na řetěz otázek.
Cizí kód (od jiného týmu) nikdy neprojde. Když tým **4 minuty** nikam nepostoupí, kdokoli ze spoluhráčů
může kliknout na nápovědu – pomůže to, ale připočte se **+30 s** k výslednému času týmu (jen jednou za
úsek).

**Bodování za 30 vteřin:** vyhrává tým, který dokončí všechny úseky v nejkratším čase (+30 s za každou
použitou nápovědu).

### Příkazové bingo

Karta 4×4 nebo 5×5 mikroúloh (lab i kvíz), každý tým má stejné úlohy, jen jinak rozmístěné. Klikneš na
políčko, které jsi vyřešil/a – kontrolu vždy dělá server, takže podvodem se políčko neoznačí.

**Bodování za 30 vteřin:** 1 bod za každé vyřešené políčko, +3 body za celou řadu/sloupec/diagonálu –
ale jen pokud v ní každý přítomný člen týmu vyřešil aspoň jedno políčko (aby netáhl jen jeden člověk).

### Riskuj!

Tabule 5×5 (nebo 4×4) na projektoru. Týmy si střídavě vybírají pole, ale **odpovídají úplně všichni**
na svém zařízení, ne jen jeden mluvčí. Správná odpověď se pošle až po uzavření okna (20–60 s), takže
nejde odkoukat, a pozdní odpověď se už nepočítá.

**Bodování za 30 vteřin:** hodnota pole × podíl členů týmu, kteří odpověděli správně. Nikdy záporně –
špatná odpověď prostě nic nepřidá.

### Přetahovaná

Celá třída ve **2 týmech**, lano se táhne podle odpovědí. Každý žák řeší vlastní proud krátkých otázek
vlastním tempem (obtížnost se přizpůsobuje – po správné odpovědi o něco těžší otázka, po špatné lehčí).
Odpovídat můžeš nejvýš jednou za 4 sekundy, ať to není jen o rychlosti klikání.

**Bodování za 30 vteřin:** každá správná odpověď táhne lano o `1 ÷ počet zrovna aktivních spoluhráčů`
na tvou stranu – větší tým proto netáhne automaticky víc, záleží na podílu správných odpovědí. Žádný
veřejný žebříček jednotlivců, jen tah lana.

### Správci sítě / webu

Mapa 16–24 uzlů se závadou (síťová linie: mikroúloha v terminálu; grafická: rozbitá stránka – chybí
alt text, špatný kontrast, rozjetý layout…). Kdo uzel jako první opraví, ten ho spravuje – nikdo mu ho
nemůže sebrat. Čas od času (podle nastavení, výchozí 5 minut) se objeví nová vlna závad.

**Bodování za 30 vteřin:** 10 bodů za vlastní opravený uzel, +2 body za uzel v největší souvislé
oblasti tvého týmu, +5 bodů „za učení“, když pomůžeš opravit uzel, co už někdo jiný spravuje (uzel mu
tím nesebereš, jen se něco přiučíš).

### Úniková místnost

Tým dostane role s **různými informacemi** (síťová linie: Síťař, Správce, Detektiv, Dokumentátor;
grafická: Typograf, Kolorista, Kodér, Art director). Každá role najde svůj kousek kódu (v terminálu
nebo v zadání) a **nahlas ho řekne týmu** – aplikace chat nemá schválně, ať si týmy mluví mezi sebou.
Dokumentátor pak složí všechny 4 kousky v panelu „Zámek“. Nejvýš 5 pokusů o zámek za minutu, ať to
nejde jen zkoušet dokola. Nápověda za tým stojí **+120 s**, na tým jsou max 3 nápovědy.

**Bodování za 30 vteřin:** cíl je, aby unikly **všechny týmy** třídy – kdo unikne dřív, ten vyhrává, ale
smysl hry je, že to zvládnou úplně všichni.

---

## Pro učitele

### Založení hry (záložka **Týmové hry**, `teacher.php?tab=hry`)

1. Zvol třídu, typ hry, linii (sítě/grafika – doplní se sama podle třídy, jde přepsat), počet týmů
   (2–8, u Přetahované vždy přesně 2) a způsob sestavení (had podle bodů z Labu / náhodně / ručně).
   Zvol zobrazení jmen (iniciály / celá jména / anonymně) – platí pro žáky i pro projektor.
2. Hra vznikne ve stavu **lobby**. Tlačítkem **Spustit** se sestaví týmy a hra běží. Jedna třída může
   mít najednou rozehranou jen jednu hru – další musíš nejdřív ukončit nebo archivovat.
3. Za běhu můžeš hru **Pozastavit/Pokračovat** (např. o přestávce) nebo rovnou **Ukončit**. Ukončená
   hra jde **Archivovat** (zmizí z aktivní nabídky, zůstane k nahlédnutí) nebo smazat.
4. **Projektor** (`teacher.php?tab=hry&projektor=<id>`, aktualizace po 2 s) ukazuje jen souhrny týmů –
   nikdy jednotlivé chyby, penalizace ani role jednotlivců, a jména jen podle zvoleného soukromí.

### Otázková banka

V záložce je i správa vlastních otázek (přidání/smazání) vedle vestavěné banky a otázek od kolegů.
Zakázaná slova se do banky uložit nedají (kontroluje se automaticky).

### Nápady do hodin

- **Zahřátí (10 min):** Příkazové bingo 4×4 na začátku hodiny – rychlé opakování příkazů/pojmů.
- **Hlavní aktivita (25–30 min):** Štafeta nebo Správci sítě/webu – dá se napojit na aktuální látku.
- **Shrnutí (15 min):** Riskuj! na projektoru jako společné opakování před testem.
- **Teambuilding:** Úniková místnost – nutí týmy mluvit spolu nahlas, ne jen být vedle sebe potichu.

### Bezpečnost a soukromí (shrnutí)

Simulátor nic nespouští ani se nikam nepřipojuje – stejný bezpečný Linux Lab jako jinde v aplikaci.
Každá akce žáka jde přes server (žádné body za podvod v prohlížeči), identita je vždy ze session, ne
z adresy. Anonymní zobrazení jmen se nedá obejít ani z URL projektoru.

## Otevřené otázky pro učitele

- Výchozí penalizace (Štafeta +30 s, Úniková místnost +120 s za nápovědu) jsou navržené odhadem – klidně
  je po prvním vyzkoušení ve třídě upravte v nastavení hry.
- Výchozí vlna nových závad u Správců sítě je 5 minut (nastavitelné 3–15) – u kratší hodiny doporučujeme
  spíš 8–10 minut, ať se mapa nezaplní dřív, než týmy stihnou uzly opravit.
