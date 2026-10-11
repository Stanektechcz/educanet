# Lekce 5 · IPv6 + DNS záznamy

**Třída:** 3.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.

## 1. Doporučená videa

### Seminář IPv6: deset let poté

- URL: https://www.youtube.com/watch?v=JJvqBH02GiA
- Délka: delší seminář
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Jak fungují IP adresy?

- URL: https://www.youtube.com/watch?v=J1nudfAQCDE
- Délka: 35 min
- Úroveň: Začátečník
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · IPv6 základy: adresa, prefix, link-local** — IPv6 je 128bitová adresace. Pro začátek stačí umět číst zápis, /64 a rozdíl mezi global a link-local adresou. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · DNS A, AAAA a CNAME** — DNS záznamy mají různé role. A mapuje jméno na IPv4, AAAA na IPv6 a CNAME vytváří alias na jiné jméno. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 5 · IPv6 + DNS záznamy

**Start**  
Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · IPv6 základy: adresa, prefix, link-local

**Mentální model**  
IPv6 je 128bitová adresace. Pro začátek stačí umět číst zápis, /64 a rozdíl mezi global a link-local adresou.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · DNS A, AAAA a CNAME

**Mentální model**  
DNS záznamy mají různé role. A mapuje jméno na IPv4, AAAA na IPv6 a CNAME vytváří alias na jiné jméno.

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

Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.

### Klíčové principy
- IPv6 základy: adresa, prefix, link-local
- DNS A, AAAA a CNAME

### Hotovo znamená
- [ ] Mám pozorovaný symptom.
- [ ] Každý test má hypotézu.
- [ ] Změnil/a jsem jednu věc.
- [ ] Výsledek jsem ověřil/a z pohledu klienta.

## 4. Pracovní list

- Předpověď: co očekávám před změnou/testem?
- Evidence: co přesně jsem pozoroval/a?
- Závěr: co z evidence plyne a co ještě ne?
- Transfer: kde jinde se stejný princip objeví?

**Rozšíření:** Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.

## 5. Mini slovník

- **IPv6 základy: adresa, prefix, link-local** — IPv6 je 128bitová adresace. Pro začátek stačí umět číst zápis, /64 a rozdíl mezi global a link-local adresou.
- **DNS A, AAAA a CNAME** — DNS záznamy mají různé role. A mapuje jméno na IPv4, AAAA na IPv6 a CNAME vytváří alias na jiné jméno.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: IPv6 základy: adresa, prefix, link-local

```text
Učím se téma „IPv6 základy: adresa, prefix, link-local“ (lekce „IPv6 + DNS záznamy“, obor Linux a počítačové sítě). Z hodiny vím: IPv6 je 128bitová adresace. Pro začátek stačí umět číst zápis, /64 a rozdíl mezi global a link-local adresou.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: DNS A, AAAA a CNAME

```text
V lekci „IPv6 + DNS záznamy“ jsme probírali téma „DNS A, AAAA a CNAME“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: DNS záznamy mají různé role. A mapuje jméno na IPv4, AAAA na IPv6 a CNAME vytváří alias na jiné jméno.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „IPv6 + DNS záznamy“: Pochopit základní IPv6 adresaci a navázat ji na praktickou práci s DNS A/AAAA/CNAME záznamy.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „IPv6 + DNS záznamy“: IPv6 bez paniky: Projdi zkracování adres. Najdi prefix /64.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „IPv6 + DNS záznamy“ („IPv6 základy: adresa, prefix, link-local“, „DNS A, AAAA a CNAME“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „IPv6 + DNS záznamy“. Polož mi 5 otázek k tématům „IPv6 základy: adresa, prefix, link-local“, „DNS A, AAAA a CNAME“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „IPv6 základy: adresa, prefix, link-local“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „DNS A, AAAA a CNAME“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- AAAA záznam ukazuje na nedostupnou IPv6 adresu, A záznam je správně. Co může uživatel pozorovat?
- Dostaneš nový symptom. Jaký nejmenší bezpečný test bys provedl/a jako první a proč?
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

