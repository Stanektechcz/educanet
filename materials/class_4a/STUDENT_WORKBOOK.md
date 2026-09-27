# 4.A · Studentský pracovní sešit

Předmět: **Seminář operačních systémů a počítačových sítí**

> Vyplňuj stručně. Důležitější je konkrétní důkaz a zdůvodnění než dlouhý text.

## Lekce 10 · systemd do hloubky: dependencies, restart policy, failure

**Cíl:** Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.

### Můj pracovní list
- [ ] Unit anatomy.
- [ ] Dependency diagram.
- [ ] Failure evidence.
- [ ] Override + rollback.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident

**Cíl:** Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.

### Můj pracovní list
- [ ] Storage diagram.
- [ ] df vs du vs inode vysvětlení.
- [ ] Incident disk full – 5 kroků.
- [ ] Preventivní monitoring.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 12 · Backup strategie: RPO/RTO a restore drill

**Cíl:** Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.

### Můj pracovní list
- [ ] RPO/RTO.
- [ ] Backup matrix.
- [ ] Restore evidence.
- [ ] Runbook.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 13 · Hardening Linux služby: SSH, firewall, aktualizace a minimální práva

**Cíl:** Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.

### Můj pracovní list
- [ ] Attack surface list.
- [ ] SSH baseline.
- [ ] Firewall scope.
- [ ] Patch+rollback plán.
- [ ] Positive/negative validation.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 14 · Containers: proces, image, volume a síť

**Cíl:** Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.

### Můj pracovní list
- [ ] Image/container/volume/network diagram.
- [ ] Port mapping.
- [ ] Persistent data decision.
- [ ] Container incident evidence.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 15 · Automatizace + configuration consistency

**Cíl:** Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.

### Můj pracovní list
- [ ] Current vs desired state.
- [ ] Preconditions.
- [ ] Pseudo-script.
- [ ] Dry-run output.
- [ ] Validation.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 16 · Pokročilá síťová diagnostika: packet evidence + socket state

**Cíl:** Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.

### Můj pracovní list
- [ ] Hypotéza.
- [ ] Packet pattern.
- [ ] Socket evidence.
- [ ] Layer boundary.
- [ ] Root cause nebo další test.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 17 · Incident response: runbook, komunikace a blameless postmortem

**Cíl:** Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.

### Můj pracovní list
- [ ] Severity/impact.
- [ ] Role assignment.
- [ ] Timeline.
- [ ] Mitigation.
- [ ] Postmortem: 2 follow-up actions.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 18 · Capstone: produkční reliability drill

**Cíl:** Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

### Můj pracovní list
- [ ] Incident timeline.
- [ ] Evidence matrix.
- [ ] Mitigation + rollback.
- [ ] Validation matrix.
- [ ] Postmortem.
- [ ] 2 preventive actions.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

<!-- V30_LESSONS:START -->
## v30 · Student Workbook — lekce 19–28

> Když něčemu nerozumíš, použij **Vysvětli jinak**. Nápověda není selhání. Po každém vysvětlení ale udělej vlastní mini-pokus a zapiš, co jsi zjistil/a.

### Lekce 19 · Golden signals lab

**Cíl:** Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 20 · Reverse proxy evidence

**Cíl:** Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 21 · Container networking

**Cíl:** Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 22 · Restore game day

**Cíl:** Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 23 · Hardening audit

**Cíl:** Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 24 · Safe change & rollback

**Cíl:** Definovat stop conditions, rollback trigger a následnou end-to-end validaci.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 25 · Drift & automation

**Cíl:** Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 26 · Performance capacity

**Cíl:** Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 27 · Incident command

**Cíl:** Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________

### Lekce 28 · Reliability mastery

**Cíl:** Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.

#### Než začnu
- Moje předpověď / co si myslím, že se stane: ______________________________
- Který způsob vysvětlení mi dnes pomohl nejvíc? ☐ animace ☐ jednoduše ☐ přirovnání ☐ krok za krokem ☐ příklad ☐ typická chyba ☐ mini-pokus

#### Evidence
- [ ] Předpověď: co očekávám před změnou / testem?
- [ ] Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- [ ] Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

#### Umím to vysvětlit?
- Princip vlastními slovy: ________________________________________________
- Jeden konkrétní důkaz / příklad: _______________________________________
- Co ještě potřebuji vysvětlit jinak: ____________________________________
<!-- V30_LESSONS:END -->
