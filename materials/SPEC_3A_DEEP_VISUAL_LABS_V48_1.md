# EDUCANET v48.1 · 3.A Deep Visual Labs · specifikace

## Cíl

V48.1 převádí 28/28 lekcí 3.A z obecného V48 frameworku na plnohodnotné lesson-specific praktické prostředí. Každý lab musí studentovi umožnit **pozorovat → diagnostikovat → manipulovat → ověřit → přenést**, nikoli pouze přečíst vysvětlení nebo zvolit odpověď.

## Priority podle dopadu

### P0 · Evidence-first troubleshooting a transfer
Nejvyšší dopad na skutečnou kompetenci: incidentní diagnostika, packet evidence, service recovery a capstone. Patří sem zejména L1, L7, L18, L19, L21, L26, L27 a L28. Úspěch není rychlý fix, ale schopnost zvolit nejmenší rozlišovací test a vysvětlit jeho informační hodnotu.

### P0 · Síťové kauzální modely
L2, L3, L5, L6, L8, L9, L23 a L24. Student musí vidět tok mezi vrstvami a dokázat odlišit adresaci, DNS/DHCP, routing, NAT, state a aplikační endpoint.

### P1 · Linux operační modely
L10–L17, L20, L22 a L25. Cílem je přestat používat příkazy jako recepty a chápat vztah filesystem → permissions → process → unit → socket → log → služba.

### P1 · Bezpečné změny a automatizace
Hardening, firewall, permissions a shell jsou postavené na least privilege, validaci před změnou, positive + negative testu a explicitním fail path.

### P2 · Týmový a mastery transfer
L27–L28. Až po zvládnutí jednotlivých domén se ověřuje přenos napříč technologiemi a práce ve sdílené evidence timeline.

## Kvalitativní kontrakt každého labu

Každý z 28 labů obsahuje:

- vlastní mission/scenario, nikoli generický text;
- vlastní vizuální model se 4+ uzly;
- vlastní interaction archetype;
- minimálně 3 realistické fault scénáře;
- minimálně 3 serverově validované praktické checkpointy;
- lesson-specific transfer prompt;
- Guided / Practice / Challenge režim;
- učitelský cold-call, diskusní prompt a extension;
- čistě formativní evidence (`grade_impact=false`, `xp_impact=false`, `mastery_impact=false`).

## Režimy podpory

**Guided** – popisy vrstev, viditelné hinty a vyšší scaffolding.  
**Practice** – plný scénář, ale hint je schovaný a student jej otevírá jen podle potřeby.  
**Challenge** – minimum popisků a žádný hint před vlastním diagnostickým pokusem.

Výchozí režim se odvozuje z již existujícího v42 learning lane, student si ho ale může pro konkrétní lab přepnout bez dopadu na známku.

## Typy praktických checkpointů

- `choice` – rozhodnutí podle evidence;
- `sequence` – click-to-build diagnostický/postupový řetězec;
- `multi` – sada nutných evidencí/guardů;
- `matrix` – service/firewall flow policy;
- `pair` – mapování portů, rolí, záznamů, prefixů;
- `command` – konkrétní diagnostický příkaz;
- `number` – výpočet adresace, TTL apod.

Validace probíhá server-side podle lesson-specific kontraktu. Frontend není zdroj pravdy.

## 28 implementovaných laboratoří

| Lekce | Deep Lab | Archetype | Checkpointy | Faulty |
|---:|---|---|---:|---:|
| 1 | Network Incident Control Room | `incident_chain` | 3 | 3 |
| 2 | Small Office Network Builder | `topology_builder` | 3 | 3 |
| 3 | DNS + DHCP Service Console | `service_board` | 3 | 3 |
| 4 | Monitoring & Hardening Operations Board | `monitoring_board` | 3 | 3 |
| 5 | IPv6 & DNS Record Lab | `address_record_lab` | 3 | 3 |
| 6 | Monitoring + DHCP Reservations | `reservation_monitor_lab` | 3 | 3 |
| 7 | Packet Journey & Wireshark Mindset | `packet_forensics` | 3 | 3 |
| 8 | VLSM & Trust Zones Planner | `vlsm_planner` | 3 | 3 |
| 9 | DNS/DHCP Timeline + Service Debug Chain | `cache_lease_chain` | 3 | 3 |
| 10 | Linux Filesystem Explorer | `filesystem_explorer` | 3 | 3 |
| 11 | Permissions Matrix Lab | `permission_matrix` | 3 | 3 |
| 12 | Process & systemd Service Graph | `systemd_graph` | 3 | 3 |
| 13 | Journal Forensic Timeline | `log_forensics` | 3 | 3 |
| 14 | SSH/SFTP Trust Chain | `ssh_chain` | 3 | 3 |
| 15 | Listener → Bind → Firewall Lab | `listener_firewall` | 3 | 3 |
| 16 | Linux Web Service End-to-End | `web_service_chain` | 3 | 3 |
| 17 | Safe Shell + Scheduler Pipeline | `automation_pipeline` | 3 | 3 |
| 18 | Capstone Incident Command Console | `capstone_incident` | 3 | 3 |
| 19 | Filesystem Incident Triage | `filesystem_incident` | 3 | 3 |
| 20 | systemd Dependency Lab | `systemd_dependencies` | 3 | 3 |
| 21 | Journal Incident Reconstruction | `incident_timeline` | 3 | 3 |
| 22 | SSH Key Operations Bench | `ssh_identity_ops` | 3 | 3 |
| 23 | DNS Evidence Chain | `dns_evidence_chain` | 3 | 3 |
| 24 | Stateful Firewall State Machine | `stateful_firewall` | 3 | 3 |
| 25 | Safe Bash Automation Workshop | `bash_safety` | 3 | 3 |
| 26 | Service Recovery Drill | `service_recovery` | 3 | 3 |
| 27 | Team Incident Command Board | `team_incident` | 3 | 3 |
| 28 | 3.A Mastery Review Arena | `mastery_arena` | 3 | 3 |

## Teacher orchestration

V Lesson Mode zůstává V48 živá sekvence Prediction → Discussion → Contrast → Debug → Sandbox → Build → Transfer. V48.1 pod ní přidává lesson-specific teacher panel:

- počet studentů, kteří Deep Lab zkusili;
- počet dokončených;
- průměr serverově validovaných checkpointů;
- distribuci Guided / Practice / Challenge;
- cold-call, diskusní otázku a extension prompt.

Na projektor se nadále nemají zobrazovat jména studentů.

## Evidence a bezpečnost

Deep Lab ukládá pouze vlastní formativní event stream `v481_deep_lab_events`. Záznam obsahuje score checkpointů, zvolený režim, transferovou reflexi a explicitně:

- `grade_impact=false`
- `xp_impact=false`
- `mastery_impact=false`

V48.1 nesmí zapisovat do project grades, grade history, Skill progress/evidence, Prestige exams ani XP profilu.

## Definition of Done

Release je hotový pouze pokud:

1. existuje 28/28 explicitních 3.A speců;
2. každý má ≥3 faulty a ≥3 checkpointy;
3. task engine přijme všechny správné kontrakty a odmítne zjevně chybné;
4. student Visual Lab a teacher Lesson Mode projdou HTTP smoke testem;
5. formativní hash isolation zůstane čistá;
6. všechny předchozí audity platformy zůstanou zelené;
7. PATCH nad čistou v48 vytvoří byte-for-byte stejný aplikační strom jako FULL build, mimo chráněné runtime adresáře.
