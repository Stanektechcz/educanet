# 4.A · Seminář operačních systémů a počítačových sítí

**Roční oblouk:** Produkční provoz → observability → Linux admin → hardening → kontejnery → incident/reliability capstone

## Struktura

Lekce 1–9 jsou součástí původní intenzivní cesty; v27 přidává lekce 10–18 jako druhou polovinu kurzu.

## Lekce 10–18

### 10. systemd do hloubky: dependencies, restart policy, failure
Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.

**Knowledge:** systemd-advanced, logs-monitoring

**Výstup:** Unit anatomy.; Dependency diagram.; Failure evidence.

### 11. Storage: disk, filesystem, mount a „disk full“ incident
Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.

**Knowledge:** storage-filesystems, journal-logs

**Výstup:** Storage diagram.; df vs du vs inode vysvětlení.; Incident disk full – 5 kroků.

### 12. Backup strategie: RPO/RTO a restore drill
Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.

**Knowledge:** backup-strategy, backup-restore

**Výstup:** RPO/RTO.; Backup matrix.; Restore evidence.

### 13. Hardening Linux služby: SSH, firewall, aktualizace a minimální práva
Navrhnout bezpečné minimum služby bez „security by checkbox“ a současně zachovat ověřitelný přístup a rollback.

**Knowledge:** ssh-hardening, network-security-basics, backups-patching

**Výstup:** Attack surface list.; SSH baseline.; Firewall scope.

### 14. Containers: proces, image, volume a síť
Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.

**Knowledge:** containers-basics, binding

**Výstup:** Image/container/volume/network diagram.; Port mapping.; Persistent data decision.

### 15. Automatizace + configuration consistency
Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.

**Knowledge:** automation-shell, config-drift

**Výstup:** Current vs desired state.; Preconditions.; Pseudo-script.

### 16. Pokročilá síťová diagnostika: packet evidence + socket state
Propojit packet capture, TCP stavy a serverový listener do jedné incidentní hypotézy.

**Knowledge:** packet-diagnostics-advanced, packet-analysis, tcp

**Výstup:** Hypotéza.; Packet pattern.; Socket evidence.

### 17. Incident response: runbook, komunikace a blameless postmortem
Řídit incident podle severity, rolí, timeline, mitigation a následného postmortemu bez chaosu a hledání viníka.

**Knowledge:** incident-runbook, slo-postmortem

**Výstup:** Severity/impact.; Role assignment.; Timeline.

### 18. Capstone: produkční reliability drill
Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

**Knowledge:** systemd-advanced, storage-filesystems, backup-strategy, ssh-hardening, containers-basics, automation-shell, packet-diagnostics-advanced, incident-runbook

**Výstup:** Incident timeline.; Evidence matrix.; Mitigation + rollback.

<!-- V30_LESSONS:START -->
## v30 · Lekce 19–28 — hlubší pochopení a aplikace

Tyto bloky používají společný model **animace → předpověď → alternativní vysvětlení → guided practice → samostatná aplikace → QA → exit ticket**. Help Ladder je kdykoli dostupný a jeho použití není penalizované.

### 19. Golden signals lab
Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

**Knowledge:** golden-signals, logs-monitoring

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 20. Reverse proxy evidence
Odlišit DNS/TLS/proxy/upstream závadu pomocí minimálního test chainu.

**Knowledge:** reverse-proxy, http-observability

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 21. Container networking
Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.

**Knowledge:** container-networking

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 22. Restore game day
Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.

**Knowledge:** backup-restore

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 23. Hardening audit
Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.

**Knowledge:** ssh-hardening, ssh-hardening

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 24. Safe change & rollback
Definovat stop conditions, rollback trigger a následnou end-to-end validaci.

**Knowledge:** rollback-strategy, change-management

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 25. Drift & automation
Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.

**Knowledge:** config-drift, infrastructure-as-code

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 26. Performance capacity
Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.

**Knowledge:** performance-engineering, capacity-planning

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 27. Incident command
Rozdělit IC/tech/comms odpovědnosti, vést timeline a připravit blameless postmortem.

**Knowledge:** incident-command, slo-postmortem

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 28. Reliability mastery
Propojit observability, safe change, incident response a recovery do závěrečného reliability drill.

**Knowledge:** golden-signals, rollback-strategy, incident-command

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?
<!-- V30_LESSONS:END -->
