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

### Vysvětli mi to jednoduše

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Vysvětli mi princip co nejjednodušeji v 5–7 větách. Použij jeden konkrétní příklad a na konci mi polož jednu kontrolní otázku. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Přirovnání + hranice přirovnání

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Vysvětli téma pomocí přirovnání z běžného života. Pak výslovně napiš, kde přirovnání přestává platit, abych si nevytvořil chybný mentální model. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Sokratický tutor

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Buď můj sokratický tutor. Nepřednášej. Ptej se vždy jen na jednu otázku, vycházej z mé odpovědi a veď mě k tomu, abych princip odvodil sám. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Najdi díru v mém postupu

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Pošlu ti svůj postup/řešení. Najdi první místo, kde můj závěr není podložený důkazem nebo kde měním příliš mnoho věcí najednou. Neopravuj vše za mě; polož otázku, která mě dovede k lepšímu testu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vytvoř mi nový transfer úkol

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Vytvoř jeden nový realistický problém, který používá stejný princip, ale v jiném kontextu. Nedávej řešení. Nejdřív chtěj moji hypotézu, potom důkaz a až nakonec mi dej zpětnou vazbu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vyzkoušej mě ústně

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Simuluj ústní zkoušení. Polož 5 otázek od základní po aplikační. Po každé mé odpovědi stručně řekni, co bylo přesné, co chybělo, a polož navazující otázku. Známku navrhni až na konci. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Praktická zkouška nanečisto

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Připrav krátkou praktickou zkoušku nanečisto na 15–20 minut. Dej mi realistické zadání, success criteria a 3 kontrolní body. Během řešení mi neposkytuj hotový postup; reaguj jen na konkrétní evidence, které ti pošlu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Zkontroluj moje vysvětlení

```text
Jsem student EDUCANETu. Probírám Lekce 2 · Navrhni a ověř malou kancelářskou síť v předmětu operační systémy a počítačové sítě. Cíl lekce: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.. Klíčová témata: VLAN: logické oddělení jedné fyzické sítě, ARP: jak host najde MAC adresu dalšího hopu, Výchozí brána a směrování, NAT/PAT: privátní klient ven na internet, Service matrix: kdo smí kam a na jaký port.
Pošlu ti své vysvětlení tématu. Ohodnoť pouze: správnost mentálního modelu, práci s důkazem, schopnost přenést princip a jednu největší mezeru. Pak mi dej jediný další krok k opravě. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
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

