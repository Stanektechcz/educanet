# 3.A · Seminář operačních systémů a počítačových sítí

**Roční oblouk:** Síťové základy → služby → Linux CLI → permissions → systemd → SSH/firewall → capstone

## Struktura

Lekce 1–9 jsou součástí původní intenzivní cesty; v27 přidává lekce 10–18 jako druhou polovinu kurzu.

## Lekce 10–18

### 10. Linux CLI + filesystem: orientace bez klikání
Orientovat se v Linux filesystemu, používat pwd/ls/cd/cp/mv/rm bezpečně a vysvětlit rozdíl absolutní a relativní cesty.

**Knowledge:** linux-filesystem, permissions

**Výstup:** Napiš význam 5 adresářů.; 5 příkazů + co vrací.; Příklad absolutní/relativní cesty.

### 11. Uživatelé, skupiny a oprávnění
Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.

**Knowledge:** users-permissions, permissions

**Výstup:** Rozepiš 3 permission strings.; Navrhni mode pro config/log/shared file.; Vysvětli chmod vs chown.

### 12. Procesy + systemd: služba není magie
Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.

**Knowledge:** processes-systemd, journal-logs

**Výstup:** Process vs service.; systemctl status – 5 polí k přečtení.; Restart vs reload vs enable.

### 13. Logy a journalctl: časová osa důkazů
Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.

**Knowledge:** journal-logs, logs-monitoring

**Výstup:** Incident timestamp.; 3 filtry logu.; Hypotéza potvrzena/vyloučena.

### 14. SSH/SFTP: bezpečný vzdálený přístup
Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.

**Knowledge:** ssh-keys-ops, ssh-sftp

**Výstup:** SSH diagnostická cesta.; Private vs public key.; 3 příčiny Permission denied.

### 15. Firewall + služby: co poslouchá a kdo se tam dostane
Propojit listener, bind adresu, firewall a client test do jednoho diagnostického modelu.

**Knowledge:** linux-firewall, ports

**Výstup:** Service matrix 4 řádky.; Listener/bind evidence.; Firewall allow + deny test.

### 16. Linux webová služba: od procesu k HTTP
Nasadit nebo analyzovat jednoduchou webovou službu a ověřit process → listener → firewall → DNS → HTTP.

**Knowledge:** web-service-linux, dns

**Výstup:** Service chain diagram.; Local/remote test.; HTTP status evidence.

### 17. Shell + cron: malá automatizace s logem a bezpečným selháním
Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.

**Knowledge:** shell-cron, backups-patching

**Výstup:** Pseudo/real script 8–20 řádků.; Guard condition.; Log sample.

### 18. Capstone: nefunguje služba – Linux + síť v jednom incidentu
Systematicky vyřešit kombinovaný incident od klienta přes DNS/síť/firewall až po systemd službu a doložit root cause.

**Knowledge:** linux-filesystem, processes-systemd, journal-logs, ssh-keys-ops, linux-firewall, web-service-linux

**Výstup:** Incident timeline.; Hypotézy a testy.; Root cause.

<!-- V30_LESSONS:START -->
## v30 · Lekce 19–28 — hlubší pochopení a aplikace

Tyto bloky používají společný model **animace → předpověď → alternativní vysvětlení → guided practice → samostatná aplikace → QA → exit ticket**. Help Ladder je kdykoli dostupný a jeho použití není penalizované.

### 19. Filesystem incident
Diagnostikovat permission/disk/mount problém bez změn naslepo.

**Knowledge:** linux-file-troubleshooting, linux-filesystem

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 20. systemd dependency lab
Rozlišit problém procesu, unit konfigurace, dependency a restart policy.

**Knowledge:** processes-systemd, processes-systemd

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 21. Journal forensic
Najít relevantní incidentní okno a spojit log se změnou a symptomem.

**Knowledge:** journal-logs, journal-logs

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 22. SSH keys in practice
Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.

**Knowledge:** ssh-key-operations, ssh-keys-ops

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 23. DNS evidence chain
Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.

**Knowledge:** dns, dns-record-types

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 24. Stateful firewall lab
Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.

**Knowledge:** stateful-firewall, linux-firewall

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 25. Safe Bash automation
Napsat opakovatelný skript s kontrolou vstupů, chybami a logem.

**Knowledge:** bash-error-handling, shell-cron

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 26. Service recovery drill
Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

**Knowledge:** service-debug-chain, web-service-linux

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 27. Team incident lab
Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.

**Knowledge:** stateful-firewall, bash-error-handling

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### 28. Mastery review
Zopakovat nejslabší skills a dokončit praktické mastery evidence před závěrečným projektem.

**Knowledge:** linux-file-troubleshooting, ssh-key-operations

**Evidence / pracovní záznam:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?
<!-- V30_LESSONS:END -->
