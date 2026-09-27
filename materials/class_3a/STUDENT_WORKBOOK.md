# 3.A · Studentský pracovní sešit

Předmět: **Seminář operačních systémů a počítačových sítí**

> Vyplňuj stručně. Důležitější je konkrétní důkaz a zdůvodnění než dlouhý text.

## Lekce 10 · Linux CLI + filesystem: orientace bez klikání

**Cíl:** Orientovat se v Linux filesystemu, používat pwd/ls/cd/cp/mv/rm bezpečně a vysvětlit rozdíl absolutní a relativní cesty.

### Můj pracovní list
- [ ] Napiš význam 5 adresářů.
- [ ] 5 příkazů + co vrací.
- [ ] Příklad absolutní/relativní cesty.
- [ ] Bezpečnostní pravidlo před rm.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 11 · Uživatelé, skupiny a oprávnění

**Cíl:** Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.

### Můj pracovní list
- [ ] Rozepiš 3 permission strings.
- [ ] Navrhni mode pro config/log/shared file.
- [ ] Vysvětli chmod vs chown.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 12 · Procesy + systemd: služba není magie

**Cíl:** Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.

### Můj pracovní list
- [ ] Process vs service.
- [ ] systemctl status – 5 polí k přečtení.
- [ ] Restart vs reload vs enable.
- [ ] Incident: služba failed – další test.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 13 · Logy a journalctl: časová osa důkazů

**Cíl:** Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.

### Můj pracovní list
- [ ] Incident timestamp.
- [ ] 3 filtry logu.
- [ ] Hypotéza potvrzena/vyloučena.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup

**Cíl:** Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.

### Můj pracovní list
- [ ] SSH diagnostická cesta.
- [ ] Private vs public key.
- [ ] 3 příčiny Permission denied.
- [ ] SFTP transfer check.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 15 · Firewall + služby: co poslouchá a kdo se tam dostane

**Cíl:** Propojit listener, bind adresu, firewall a client test do jednoho diagnostického modelu.

### Můj pracovní list
- [ ] Service matrix 4 řádky.
- [ ] Listener/bind evidence.
- [ ] Firewall allow + deny test.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 16 · Linux webová služba: od procesu k HTTP

**Cíl:** Nasadit nebo analyzovat jednoduchou webovou službu a ověřit process → listener → firewall → DNS → HTTP.

### Můj pracovní list
- [ ] Service chain diagram.
- [ ] Local/remote test.
- [ ] HTTP status evidence.
- [ ] 5krokový runbook.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním

**Cíl:** Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.

### Můj pracovní list
- [ ] Pseudo/real script 8–20 řádků.
- [ ] Guard condition.
- [ ] Log sample.
- [ ] Schedule.
- [ ] Verification evidence.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

## Lekce 18 · Capstone: nefunguje služba – Linux + síť v jednom incidentu

**Cíl:** Systematicky vyřešit kombinovaný incident od klienta přes DNS/síť/firewall až po systemd službu a doložit root cause.

### Můj pracovní list
- [ ] Incident timeline.
- [ ] Hypotézy a testy.
- [ ] Root cause.
- [ ] Oprava + rollback.
- [ ] 3 validační důkazy.

### Reflexe
- Co jsem změnil/a po testu nebo feedbacku?
- Jaký konkrétní důkaz mě k tomu vedl?
- Co bych příště udělal/a dřív nebo jinak?

<!-- V30_LESSONS:START -->
## v30 · Student Workbook — lekce 19–28

> Když něčemu nerozumíš, použij **Vysvětli jinak**. Nápověda není selhání. Po každém vysvětlení ale udělej vlastní mini-pokus a zapiš, co jsi zjistil/a.

### Lekce 19 · Filesystem incident

**Cíl:** Diagnostikovat permission/disk/mount problém bez změn naslepo.

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

### Lekce 20 · systemd dependency lab

**Cíl:** Rozlišit problém procesu, unit konfigurace, dependency a restart policy.

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

### Lekce 21 · Journal forensic

**Cíl:** Najít relevantní incidentní okno a spojit log se změnou a symptomem.

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

### Lekce 22 · SSH keys in practice

**Cíl:** Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.

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

### Lekce 23 · DNS evidence chain

**Cíl:** Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.

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

### Lekce 24 · Stateful firewall lab

**Cíl:** Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.

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

### Lekce 25 · Safe Bash automation

**Cíl:** Napsat opakovatelný skript s kontrolou vstupů, chybami a logem.

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

### Lekce 26 · Service recovery drill

**Cíl:** Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

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

### Lekce 27 · Team incident lab

**Cíl:** Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.

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

### Lekce 28 · Mastery review

**Cíl:** Zopakovat nejslabší skills a dokončit praktické mastery evidence před závěrečným projektem.

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
