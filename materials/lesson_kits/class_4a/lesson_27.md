# Lekce 27 · Incident command

**Třída:** 4.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

## 1. Doporučená videa

### Chyť mě, když to dokážeš! Bezpečnostní monitoring s FOSS

- URL: https://www.youtube.com/watch?v=ATPBw6Dt6Ig
- Délka: 50 min
- Úroveň: Pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Minority Reports – chyby z prohlížeče jako provozní evidence

- URL: https://www.youtube.com/watch?v=aB7i_jLWGFc
- Délka: 50 min
- Úroveň: Pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · Incident command a komunikace** — Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · SLO, error budget a postmortem** — Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 27 · Incident command

**Start**  
Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · Incident command a komunikace

**Mentální model**  
Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · SLO, error budget a postmortem

**Mentální model**  
Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu.

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

Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

### Klíčové principy
- Incident command a komunikace
- SLO, error budget a postmortem

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

- **Incident command a komunikace** — Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost.
- **SLO, error budget a postmortem** — Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: Incident command a komunikace

```text
Učím se téma „Incident command a komunikace“ (lekce „Incident command“, obor Linux a počítačové sítě). Z hodiny vím: Při větším incidentu odděl rozhodování, technickou práci a komunikaci, aby tým nesoutěžil o pozornost.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: SLO, error budget a postmortem

```text
V lekci „Incident command“ jsme probírali téma „SLO, error budget a postmortem“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „Incident command“: Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „Incident command“: vlastní příklad k cíli lekce – Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „Incident command“ („Incident command a komunikace“, „SLO, error budget a postmortem“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „Incident command“. Polož mi 5 otázek k tématům „Incident command a komunikace“, „SLO, error budget a postmortem“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „Incident command a komunikace“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „SLO, error budget a postmortem“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
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

