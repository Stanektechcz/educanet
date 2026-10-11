# Lekce 2 · Navrhni a ověř malou kancelářskou síť

**Třída:** 3.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.

## 1. Doporučená videa

### Pokročilejší síťování v Linuxu

- URL: https://www.youtube.com/watch?v=KI4ojd68IMA
- Délka: 50 min
- Úroveň: Pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Wireshark a modely TCP/IP a ISO/OSI

- URL: https://www.youtube.com/watch?v=VRhbOaw8sXA
- Délka: cca 39 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Nefunguje internet: jak najít příčinu výpadku připojení

- URL: https://www.youtube.com/watch?v=gGwsOpNOmr0
- Délka: 50 min
- Úroveň: Začátečník → středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · VLAN: logické oddělení jedné fyzické sítě** — VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · ARP: jak host najde MAC adresu dalšího hopu** — IPv4 host potřebuje pro lokální Ethernet rámec znát MAC adresu cíle nebo výchozí brány. ARP propojuje IP a MAC na lokálním segmentu. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 2 · Navrhni a ověř malou kancelářskou síť

**Start**  
Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · VLAN: logické oddělení jedné fyzické sítě

**Mentální model**  
VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · ARP: jak host najde MAC adresu dalšího hopu

**Mentální model**  
IPv4 host potřebuje pro lokální Ethernet rámec znát MAC adresu cíle nebo výchozí brány. ARP propojuje IP a MAC na lokálním segmentu.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 5 · Výchozí brána a směrování

**Mentální model**  
Kdy komunikuje host přímo a kdy přes router.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 6 · Nejdřív celý vzor

**Worked example**  
Symptom → hypotéza → nejmenší test → evidence → bezpečná změna → validace.

> Poznámka pro výklad: Další příklad už nech s jedním chybějícím krokem.

### Slide 7 · Co se často plete

**Typická chyba**  
Pozitivní test jedné vrstvy neznamená, že celý systém funguje end-to-end.

> Poznámka pro výklad: Použij misconception data třídy, pokud existují.

### Slide 8 · Teď to udělej ty

**Practice**  
Vyřeš jeden nový případ. Zapiš předpověď, zásah a evidence, která výsledek potvrzuje nebo vyvrací.

> Poznámka pro výklad: Učitel obchází třídu a ptá se „co tímto krokem dokazuješ?“.

### Slide 9 · Stejný princip, jiný kontext

**Transfer**  
Neopakuj povrchový postup. Rozpoznej stejný princip v jiné situaci a znovu jej ověř.

> Poznámka pro výklad: Silná evidence mastery vzniká až při přenosu do nového kontextu.

### Slide 10 · Jedna věta, jeden důkaz

**Exit ticket**  
Vysvětli vlastními slovy nejdůležitější princip lekce a uveď, jak bys ověřil/a, že opravdu funguje.

> Poznámka pro výklad: Z odpovědí vytvoř starter příští hodiny.

## 3. One-pager / tahák

### Cíl

Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.

### Klíčové principy
- VLAN: logické oddělení jedné fyzické sítě
- ARP: jak host najde MAC adresu dalšího hopu
- Výchozí brána a směrování
- NAT/PAT: privátní klient ven na internet

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

- **VLAN: logické oddělení jedné fyzické sítě** — VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.
- **ARP: jak host najde MAC adresu dalšího hopu** — IPv4 host potřebuje pro lokální Ethernet rámec znát MAC adresu cíle nebo výchozí brány. ARP propojuje IP a MAC na lokálním segmentu.
- **Výchozí brána a směrování** — Kdy komunikuje host přímo a kdy přes router.
- **NAT/PAT: privátní klient ven na internet** — NAT mění adresy mezi privátní a veřejnou částí sítě. PAT navíc rozlišuje více spojení pomocí portů.
- **Service matrix: kdo smí kam a na jaký port** — Místo pravidla „povolíme server“ je přesnější popsat zdroj, cíl, protokol a port. To je základ čitelného firewall návrhu.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: VLAN: logické oddělení jedné fyzické sítě

```text
Učím se téma „VLAN: logické oddělení jedné fyzické sítě“ (lekce „Navrhni a ověř malou kancelářskou síť“, obor Linux a počítačové sítě). Z hodiny vím: VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: ARP: jak host najde MAC adresu dalšího hopu

```text
V lekci „Navrhni a ověř malou kancelářskou síť“ jsme probírali téma „ARP: jak host najde MAC adresu dalšího hopu“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: IPv4 host potřebuje pro lokální Ethernet rámec znát MAC adresu cíle nebo výchozí brány. ARP propojuje IP a MAC na lokálním segmentu.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „Navrhni a ověř malou kancelářskou síť“: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „Navrhni a ověř malou kancelářskou síť“: Rozděl síť do VLAN: Dokonči VLAN Knowledge Tour. USERS: VLAN10 / 192.168.10.0/24.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „Navrhni a ověř malou kancelářskou síť“ („VLAN: logické oddělení jedné fyzické sítě“, „ARP: jak host najde MAC adresu dalšího hopu“, „Výchozí brána a směrování“, „NAT/PAT: privátní klient ven na internet“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „Navrhni a ověř malou kancelářskou síť“. Polož mi 5 otázek k tématům „VLAN: logické oddělení jedné fyzické sítě“, „ARP: jak host najde MAC adresu dalšího hopu“, „Výchozí brána a směrování“, „NAT/PAT: privátní klient ven na internet“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „VLAN: logické oddělení jedné fyzické sítě“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „ARP: jak host najde MAC adresu dalšího hopu“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „Výchozí brána a směrování“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Čí MAC adresu bude PC01 hledat přes ARP jako první?
- Která route se použije pro 192.168.20.20?

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

