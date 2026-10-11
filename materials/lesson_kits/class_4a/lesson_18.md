# Lekce 18 · Capstone: produkční reliability drill

**Třída:** 4.A  
**Předmět:** Seminář OS a sítě  
**Cíl:** Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

## 1. Doporučená videa

### Automatizace monitoringu serverů snadno a rychle

- URL: https://www.youtube.com/watch?v=sZajyA4vUOA
- Délka: 20 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Proxmox Backup Server: obnova jednoho souboru

- URL: https://www.youtube.com/watch?v=54Sc77n4pDQ
- Délka: krátké · cca 5–10 min
- Úroveň: Středně pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### Moderní linuxový firewall s nftables

- URL: https://www.youtube.com/watch?v=7h5PMWbmXIo
- Délka: 49 min
- Úroveň: Pokročilý
- Před: Před videem si napiš jednu věc, kterou očekáváš, že po zhlédnutí dokážeš vysvětlit.
- Během: Pozastav video při prvním důležitém rozhodnutí/postupu a zkus předpovědět další krok.
- Po: Po videu napiš jednu konkrétní větu: „Teď už umím rozlišit…“ a vrať se k praktické úloze.

### 90s micro-video storyboard

- **0:00–0:12 · Situace** — Začínáme konkrétním problémem, ne definicí. Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem. _Vizuál: Jedna realistická situace z Reality Demo._
- **0:12–0:35 · systemd: dependencies a failure policy** — Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop. _Vizuál: Symptom → test → důkaz._
- **0:35–0:58 · Storage a filesystems** — Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat. _Vizuál: Symptom → test → důkaz._
- **0:58–1:15 · Typická chyba** — Neměň několik věcí současně. Každý test má zúžit jednu nejistotu. _Vizuál: Kontrast správného a slepého postupu._
- **1:15–1:30 · Zkus si** — Teď princip přenes do nového případu bez kopírování hotového řešení. _Vizuál: Krátká výzva + stop frame před odpovědí._

## 2. Prezentace

### Slide 1 · Lekce 18 · Capstone: produkční reliability drill

**Start**  
Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

> Poznámka pro výklad: Nečti slide. Zeptej se studentů, co by byl pozorovatelný důkaz splnění cíle.

### Slide 2 · Reálný problém

**Proč to řešíme**  
V praxi nestačí vědět příkaz. Potřebuješ rozlišit vrstvy problému a ověřit změnu.

> Poznámka pro výklad: Použij krátkou situaci z Reality Demo.

### Slide 3 · systemd: dependencies a failure policy

**Mentální model**  
Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 4 · Storage a filesystems

**Mentální model**  
Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.

> Poznámka pro výklad: Nech studenty předpovědět výsledek před animací.

### Slide 5 · Backup strategie, RPO a RTO

**Mentální model**  
Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.

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

Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

### Klíčové principy
- systemd: dependencies a failure policy
- Storage a filesystems
- Backup strategie, RPO a RTO
- SSH hardening a bezpečný management

### Hotovo znamená
- [ ] Mám pozorovaný symptom.
- [ ] Každý test má hypotézu.
- [ ] Změnil/a jsem jednu věc.
- [ ] Výsledek jsem ověřil/a z pohledu klienta.

## 4. Pracovní list

- Incident timeline.
- Evidence matrix.
- Mitigation + rollback.
- Validation matrix.
- Postmortem.
- 2 preventive actions.

**Rozšíření:** Vytvoř podobný incident s jinou kořenovou příčinou a napiš, jaké evidence mají spolužáka navést bez prozrazení řešení.

## 5. Mini slovník

- **systemd: dependencies a failure policy** — Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.
- **Storage a filesystems** — Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.
- **Backup strategie, RPO a RTO** — Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.
- **SSH hardening a bezpečný management** — Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.
- **Containers: image, runtime, volume a network** — Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.
- **Bezpečná automatizace a idempotence** — Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.

## 6. AI prompty pro studenta

> **Dobrovolné procvičení s AI – než začneš:**
> - Použij jen AI nástroj, který povolila škola nebo tvůj učitel.
> - Nevkládej osobní údaje – své ani cizí: jméno, e-mail, telefon, adresu, fotky ani třídu.
> - Nevkládej přihlašovací údaje školy: hesla, jednorázové kódy, kód hodiny ani odkazy z EDUCANETu.
> - Výstup AI vždy ověř (v simulátoru Linux Labu, v zápisu z hodiny nebo u učitele). AI se plete i s jistotou.
> - Když ti AI pomohla s odevzdanou prací, napiš to: jaký nástroj, k čemu a co jsi upravil(a) sám/sama.

### 1. Vysvětli mi jednoduše: systemd: dependencies a failure policy

```text
Učím se téma „systemd: dependencies a failure policy“ (lekce „Capstone: produkční reliability drill“, obor Linux a počítačové sítě). Z hodiny vím: Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.
Vysvětli mi ho v 5–7 větách pro začátečníka a přidej jeden konkrétní příklad ze správy malé firemní nebo školní sítě. Na konci mi polož jednu kontrolní otázku a počkej na moji odpověď.
```

### 2. Přirovnání a jeho hranice: Storage a filesystems

```text
V lekci „Capstone: produkční reliability drill“ jsme probírali téma „Storage a filesystems“. Vysvětli mi ho pomocí přirovnání z běžného života. Z hodiny vím: Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.
Potom napiš, ve kterém místě přirovnání přestává platit, abych si nevytvořil(a) chybnou představu. Odpověz nejvýš na 10 řádků.
```

### 3. Sokratický tutor k cíli lekce

```text
Cíl lekce „Capstone: produkční reliability drill“: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.
Buď můj sokratický tutor. Nevysvětluj hned celé řešení – ptej se vždy jen na jednu otázku a podle mé odpovědi mě veď dál, dokud cíl nedokážu vysvětlit vlastními slovy.
```

### 4. Zkontroluj můj postup

```text
Řeším úkol z lekce „Capstone: produkční reliability drill“: Triage: Urči impact/severity. Zmraz riskantní změny.
Pošlu ti svůj postup a výpis ze simulátoru Linux Labu (bez skutečných IP adres, jmen počítačů a hesel). Najdi první krok, který není podložený důkazem, a polož mi otázku, která mě dovede k lepšímu ověření. Neopravuj celý postup za mě.
```

### 5. Nový procvičovací úkol

```text
Vymysli mi jeden nový realistický úkol na stejný princip jako lekce „Capstone: produkční reliability drill“ („systemd: dependencies a failure policy“, „Storage a filesystems“, „Backup strategie, RPO a RTO“, „SSH hardening a bezpečný management“), ale v jiné situaci – jen s adresami z dokumentačních nebo privátních rozsahů (např. 192.0.2.0/24, 10.0.0.0/8).
Nedávej mi řešení. Nejdřív chtěj můj odhad, pak postup a až nakonec mi dej zpětnou vazbu.
```

### 6. Vyzkoušej mě

```text
Opakuji si lekci „Capstone: produkční reliability drill“. Polož mi 5 otázek k tématům „systemd: dependencies a failure policy“, „Storage a filesystems“, „Backup strategie, RPO a RTO“, „SSH hardening a bezpečný management“ od základní po praktickou. Ptej se po jedné a po každé odpovědi řekni, co bylo přesné a co chybělo.
Nejde o známku ani o školní test – nehledej a neprozrazuj jeho odpovědi.
```

## 7. Zkouška nanečisto

### Ústní otázky
- Vysvětli vlastními slovy „systemd: dependencies a failure policy“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „Storage a filesystems“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Vysvětli vlastními slovy „Backup strategie, RPO a RTO“ a uveď jeden důkaz, podle kterého poznáš, že princip funguje.
- Co nejlépe dokazuje zvládnutí capstone?
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

