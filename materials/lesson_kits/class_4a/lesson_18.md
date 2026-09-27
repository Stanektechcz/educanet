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

### Vysvětli mi to jednoduše

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Vysvětli mi princip co nejjednodušeji v 5–7 větách. Použij jeden konkrétní příklad a na konci mi polož jednu kontrolní otázku. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Přirovnání + hranice přirovnání

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Vysvětli téma pomocí přirovnání z běžného života. Pak výslovně napiš, kde přirovnání přestává platit, abych si nevytvořil chybný mentální model. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Sokratický tutor

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Buď můj sokratický tutor. Nepřednášej. Ptej se vždy jen na jednu otázku, vycházej z mé odpovědi a veď mě k tomu, abych princip odvodil sám. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Najdi díru v mém postupu

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Pošlu ti svůj postup/řešení. Najdi první místo, kde můj závěr není podložený důkazem nebo kde měním příliš mnoho věcí najednou. Neopravuj vše za mě; polož otázku, která mě dovede k lepšímu testu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vytvoř mi nový transfer úkol

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Vytvoř jeden nový realistický problém, který používá stejný princip, ale v jiném kontextu. Nedávej řešení. Nejdřív chtěj moji hypotézu, potom důkaz a až nakonec mi dej zpětnou vazbu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Vyzkoušej mě ústně

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Simuluj ústní zkoušení. Polož 5 otázek od základní po aplikační. Po každé mé odpovědi stručně řekni, co bylo přesné, co chybělo, a polož navazující otázku. Známku navrhni až na konci. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Praktická zkouška nanečisto

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Připrav krátkou praktickou zkoušku nanečisto na 15–20 minut. Dej mi realistické zadání, success criteria a 3 kontrolní body. Během řešení mi neposkytuj hotový postup; reaguj jen na konkrétní evidence, které ti pošlu. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
```

### Zkontroluj moje vysvětlení

```text
Jsem student EDUCANETu. Probírám Lekce 18 · Capstone: produkční reliability drill v předmětu operační systémy a počítačové sítě. Cíl lekce: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.. Klíčová témata: systemd: dependencies a failure policy, Storage a filesystems, Backup strategie, RPO a RTO, SSH hardening a bezpečný management, Containers: image, runtime, volume a network, Bezpečná automatizace a idempotence, Pokročilá packet diagnostika, Incident runbook a koordinace.
Pošlu ti své vysvětlení tématu. Ohodnoť pouze: správnost mentálního modelu, práci s důkazem, schopnost přenést princip a jednu největší mezeru. Pak mi dej jediný další krok k opravě. Neprozrazuj řešení hodnoceného testu ani hotovou odpověď za mě. Když tápu, dávej menší nápovědy a vždy se ptej, jaký důkaz mám.
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

