# Lekce 19 · Golden signals lab

**Třída:** 4.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

## 1. Doporučená videa

### Automatizace monitoringu serverů snadno a rychle

- URL: https://www.youtube.com/watch?v=sZajyA4vUOA
- Délka: 20 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Centralized logs ElasticSearch way

- URL: https://www.youtube.com/watch?v=m6zpzczf2p8
- Délka: 50 min
- Úroveň: Pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · Golden signals observability** — Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · Logy a monitoring: korelace místo hádání** — Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 19 · Golden signals lab

**Start**  
Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · Golden signals observability

**Mentální model**  
Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · Logy a monitoring: korelace místo hádání

**Mentální model**  
Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 5 · Nejdřív celý vzor

**Worked example**  
Symptom → hypotéza → nejmenší test → evidence → bezpečná změna → validace.

> Poznámka pro výklad: Další příklad už nech s jedním chybějícím krokem.

### Slide 6 · Co se často plete

**Typická chyba**  
Pozitivní test jedné vrstvy neznamená, že celý systém funguje end-to-end.

> Poznámka pro výklad: Použij misconception data třídy, pokud existují.

### Slide 7 · Teď to udělej ty

**Practice**  
Vyřeš jeden nový případ. Zapiš předpověď, zásah a evidence, která výsledek potvrzuje nebo vyvrací.

> Poznámka pro výklad: Učitel obchází třídu a ptá se „co tímto krokem dokazuješ?“.

### Slide 8 · Stejný princip, jiný kontext

**Transfer**  
Neopakuj povrchový postup. Rozpoznej stejný princip v jiné situaci a znovu jej ověř.

> Poznámka pro výklad: Silná evidence mastery vzniká až při přenosu do nového kontextu.

### Slide 9 · Jedna věta, jeden důkaz

**Exit ticket**  
Vysvětli vlastními slovy nejdůležitější princip lekce a uveď, jak bys ověřil/a, že opravdu funguje.

> Poznámka pro výklad: Z odpovědí vytvoř starter příští hodiny.

## 3. One-pager / tahák

### Cíl

Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

### Klíčové principy
- Golden signals observability
- Logy a monitoring: korelace místo hádání

### Hotovo znamená
- [ ] Mám pozorovaný symptom.
- [ ] Každý test má hypotézu.
- [ ] Změnil/a jsem jednu věc.
- [ ] Výsledek jsem ověřil/a z pohledu klienta.

## 4. Pracovní list

- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

**Rozšíření:** Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.

## 5. Mini slovník

- **Golden signals observability** — Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.
- **Logy a monitoring: korelace místo hádání** — Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: Golden signals observability

```text
Učím se téma „Golden signals observability“ (lekce „Golden signals lab“, obor Linux a počítačové sítě). Z hodiny vím: Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: Logy a monitoring: korelace místo hádání

```text
V lekci „Golden signals lab“ jsme probírali téma „Logy a monitoring: korelace místo hádání“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „Golden signals lab“: Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „Golden signals lab“: vlastní příklad k cíli lekce – Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „Golden signals lab“ („Golden signals observability“, „Logy a monitoring: korelace místo hádání“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „Golden signals lab“. Polož mi 5 otázek k tématům „Golden signals observability“, „Logy a monitoring: korelace místo hádání“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „Golden signals observability“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „Logy a monitoring: korelace místo hádání“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Který postup je nejlepší při diagnostice?
- Který postup je nejlepší při diagnostice?
- Dostaneš nový symptom. Jaký nejmenší bezpečný test bys provedl/a jako první a proč?

### Praktická část

Vyřeš nový incident bez restartu „naslepo“. Odevzdej symptom, hypotézu, nejmenší test, evidence, jednu bezpečnou změnu a pozitivní + negativní validaci.

### Self-check
- [ ] Umím princip vysvětlit bez opisování definice.
- [ ] Umím poznat typickou chybu.
- [ ] Umím uvést důkaz, ne jen výsledek.
- [ ] Umím princip použít v novém kontextu.
- [ ] Vím, co bych ověřil/a jako další krok.

### Rubrika
- **Rozvíjí se** — Zná pojmy, ale závěr ještě často stojí na intuici nebo kopírování postupu.
- **Kompetentní** — Použije princip ve známé situaci a dokáže uvést základní evidence.
- **Silný výkon** — Samostatně volí vhodný postup, vysvětluje důvod a validuje výsledek.
- **Mastery** — Přenese princip do nové situace, rozpozná limity důkazu a umí postup obhájit.

