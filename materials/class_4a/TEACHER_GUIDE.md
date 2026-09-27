# 4.A · Teacher guide

Předmět: **Seminář operačních systémů a počítačových sítí**

## Doporučený rytmus 2 × 45 minut

- 0–15 min: aktivace / Knowledge Tour / model
- 15–45 min: první řízený výstup
- 45–75 min: aplikace a iterace
- 75–90 min: QA / test / reflexe

## Lekce 10–18

### Lekce 10 — systemd do hloubky: dependencies, restart policy, failure
**Cíl:** Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.

**Příprava učitele:**
- Používej testovací unit.
- Důraz na evidence před restartem.

**Časový plán:**
- 0–15 — Unit anatomy: Rozliš Unit/Service/Install.
- 15–30 — Dependencies: Rozliš After/Wants/Requires konceptuálně.
- 30–45 — Restart loop: Z logu zjisti proč proces padá.
- 45–60 — Ordering: 
- 60–75 — Drop-in/override mindset: Navrhni minimální override místo kopie celého unit souboru.
- 75–90 — Validate: daemon-reload konceptuálně.

**Evidence k uzavření lekce:**
- Unit anatomy.
- Dependency diagram.
- Failure evidence.
- Override + rollback.

### Lekce 11 — Storage: disk, filesystem, mount a „disk full“ incident
**Cíl:** Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.

**Příprava učitele:**
- Nedávej studentům mazat skutečné systémové logy.
- Pracujte s připravenou strukturou dat.

**Časový plán:**
- 0–15 — Storage layers: Device → partition/LV → filesystem → mount point.
- 15–30 — df/du mindset: Zjisti, který filesystem je plný.
- 30–45 — Log growth: Najdi podezřelý růst logu.
- 45–60 — Inodes: 
- 60–75 — Mount validation: Ověř správný mount point po reboot scénáři.
- 75–90 — Prevence: Navrhni monitoring kapacity + retention.

**Evidence k uzavření lekce:**
- Storage diagram.
- df vs du vs inode vysvětlení.
- Incident disk full – 5 kroků.
- Preventivní monitoring.

### Lekce 12 — Backup strategie: RPO/RTO a restore drill
**Cíl:** Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.

**Příprava učitele:**
- Vhodné pro simulovaný dataset.
- Uč rozdíl backup success vs business recovery.

**Časový plán:**
- 0–15 — RPO/RTO: RPO = kolik dat smíš ztratit.
- 15–30 — Co zálohovat: Data, konfigurace, metadata/secrets odděleně podle rizika.
- 30–45 — Restore drill: Obnov do testovacího cíle.
- 45–60 — Backup vs restore: 
- 60–75 — Integrity + app validation: Ověř soubory/databázi podle typu.
- 75–90 — Runbook: Napiš restore kroky, odpovědnosti a stop conditions.

**Evidence k uzavření lekce:**
- RPO/RTO.
- Backup matrix.
- Restore evidence.
- Runbook.

### Lekce 13 — Hardening Linux služby: SSH, firewall, aktualizace a minimální práva
**Cíl:** Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.

**Příprava učitele:**
- Defenzivní lab; nepracovat s cizími systémy.
- Důraz na minimální scope a recovery.

**Časový plán:**
- 0–15 — Attack surface: Sepiš vystavené služby.
- 15–30 — SSH baseline: Klíče, omezené účty a bezpečná práva.
- 30–45 — Firewall scope: Povol management pouze z potřebné zóny/IP rozsahu.
- 45–60 — Patch plan: Zjisti dopad aktualizace.
- 60–75 — Lockout risk: 
- 75–90 — Security validation: Pozitivní test oprávněného přístupu.

**Evidence k uzavření lekce:**
- Attack surface list.
- SSH baseline.
- Firewall scope.
- Patch+rollback plán.
- Positive/negative validation.

### Lekce 14 — Containers: proces, image, volume a síť
**Cíl:** Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.

**Příprava učitele:**
- Lze realizovat v Dockeru/Podmanu nebo čistě simulovat.
- Nezaváděj orchestraci dřív, než studenti chápou jednu instanci.

**Časový plán:**
- 0–15 — Container mental model: Image ≠ container.
- 15–30 — Port mapping: Rozliš container port a host port.
- 30–45 — Persistent data: Urči, která data musí přežít nový container.
- 45–60 — Recreate: 
- 60–75 — Health + logs: Ověř status/health.
- 75–90 — Incident: Host port je otevřen, ale app uvnitř poslouchá jen na jiné adrese/portu.

**Evidence k uzavření lekce:**
- Image/container/volume/network diagram.
- Port mapping.
- Persistent data decision.
- Container incident evidence.

### Lekce 15 — Automatizace + configuration consistency
**Cíl:** Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.

**Příprava učitele:**
- Zadání drž defenzivní a v sandboxu.
- Hodnoť safe failure a idempotenci.

**Časový plán:**
- 0–15 — Idempotentní změna: Nejdřív zjisti current state.
- 15–30 — Preconditions: Ověř host, config a backup/rollback.
- 30–45 — Drift: Porovnej deklarovaný a skutečný stav.
- 45–60 — Automatizace: 
- 60–75 — Dry-run/plan: Vygeneruj plán změn.
- 75–90 — Apply + validate: Po změně změř desired outcome.

**Evidence k uzavření lekce:**
- Current vs desired state.
- Preconditions.
- Pseudo-script.
- Dry-run output.
- Validation.

### Lekce 16 — Pokročilá síťová diagnostika: packet evidence + socket state
**Cíl:** Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.

**Příprava učitele:**
- Používej předpřipravené pcapy nebo izolovaný lab.
- Nezachytávej citlivý provoz třetích osob.

**Časový plán:**
- 0–15 — Capture with question: Formuluj hypotézu před filtrem.
- 15–30 — SYN patterns: Rozliš timeout, RST a SYN-ACK.
- 30–45 — Server socket: Porovnej capture s ss/lsof.
- 45–60 — RST: 
- 60–75 — Po TCP: Když TCP funguje, pokračuj TLS/HTTP podle symptomu.
- 75–90 — Incident timeline: Seřaď packet + server evidence podle času.

**Evidence k uzavření lekce:**
- Hypotéza.
- Packet pattern.
- Socket evidence.
- Layer boundary.
- Root cause nebo další test.

### Lekce 17 — Incident response: runbook, komunikace a blameless postmortem
**Cíl:** Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.

**Příprava učitele:**
- Použij Project Workspace role Leader/Developer/QA/Presenter pro týmový incident.
- Hodnoť rozhodování a komunikaci stejně jako techniku.

**Časový plán:**
- 0–15 — Incident roles: Incident lead, investigator, communicator.
- 15–30 — Severity + impact: Popiš uživatelský dopad.
- 30–45 — Mitigation first: Pokud existuje bezpečný rollback, zvaž rychlé obnovení služby.
- 45–60 — Timeline: Zapisuj fakta s časem.
- 60–75 — Postmortem: 
- 75–90 — Follow-up: Každá akce má ownera, prioritu a ověřitelný výsledek.

**Evidence k uzavření lekce:**
- Severity/impact.
- Role assignment.
- Timeline.
- Mitigation.
- Postmortem: 2 follow-up actions.

### Lekce 18 — Capstone: produkční reliability drill
**Cíl:** Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

**Příprava učitele:**
- Závěrečný týmový drill 4.A.
- Učitel může během scénáře injectovat další symptom, ale musí zachovat řešitelnost z evidence.

**Časový plán:**
- 0–15 — Triage: Urči impact/severity.
- 15–30 — Evidence matrix: DNS/TCP/TLS/HTTP.
- 30–45 — Mitigation: Rozhodni rollback/fix/failover podle evidence.
- 45–60 — Recovery validation: Ověř user path, health, error rate a security boundary.
- 60–75 — Prevent repeat: Navrhni guard/monitor/automation, který problém zachytí nebo omezí.
- 75–90 — Postmortem defense: 

**Evidence k uzavření lekce:**
- Incident timeline.
- Evidence matrix.
- Mitigation + rollback.
- Validation matrix.
- Postmortem.
- 2 preventive actions.

## Diferenciace

- Student, který potřebuje podporu: použije připravený checklist, jednu nápovědu a menší rozsah výstupu.
- Standard: splní všechny povinné evidence a stručně obhájí jedno rozhodnutí.
- Rychlík: přidá druhou variantu, failure/edge case nebo hlubší validaci; ne další dekorativní práci.

## Hodnocení

Preferuj evidence a vysvětlení rozhodnutí před rychlostí nebo množstvím kliknutí. U týmových projektů použij Project Workspace role a individuální role evaluation.

<!-- V30_LESSONS:START -->
## v30 · Teacher Guide — lekce 19–28

### Adaptivní rutina pro každou lekci

1. Nejprve nech studenta **předpovědět**, co se stane.
2. Ukaž animovaný model pouze jako krátkou mentální mapu.
3. Pokud tápe, nepřeříkávej totéž: použij Help Ladder v pořadí **jednoduše → přirovnání → krok za krokem → konkrétní příklad → typická chyba → mini-pokus**.
4. Po nápovědě musí vždy následovat malý samostatný krok.
5. Hodnoť evidence a schopnost vysvětlit rozhodnutí, ne počet použitých nápověd.

### Lekce 19 — Golden signals lab
**Cíl:** Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 20 — Reverse proxy evidence
**Cíl:** Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 21 — Container networking
**Cíl:** Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 22 — Restore game day
**Cíl:** Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 23 — Hardening audit
**Cíl:** Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 24 — Safe change & rollback
**Cíl:** Definovat stop conditions, rollback trigger a následnou end-to-end validaci.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 25 — Drift & automation
**Cíl:** Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 26 — Performance capacity
**Cíl:** Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 27 — Incident command
**Cíl:** Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 28 — Reliability mastery
**Cíl:** Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?
<!-- V30_LESSONS:END -->
