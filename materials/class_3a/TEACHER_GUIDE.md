# 3.A · Teacher guide

Předmět: **Seminář operačních systémů a počítačových sítí**

## Doporučený rytmus 2 × 45 minut

- 0–15 min: aktivace / Knowledge Tour / model
- 15–45 min: první řízený výstup
- 45–75 min: aplikace a iterace
- 75–90 min: QA / test / reflexe

## Lekce 10–18

### Lekce 10 — Linux CLI + filesystem: orientace bez klikání
**Cíl:** Orientovat se v Linux filesystemu, používat pwd/ls/cd/cp/mv/rm bezpečně a vysvětlit rozdíl absolutní a relativní cesty.

**Příprava učitele:**
- Používej sandbox/testovací adresář.
- Nedávej destruktivní příklady nad skutečnými systémovými cestami.

**Časový plán:**
- 0–15 — Filesystem mapa: Rozliš /home, /etc, /var, /tmp a /usr.
- 15–30 — Navigace: Použij pwd, ls -la, cd.
- 30–45 — Práce se soubory: Vytvoř adresář lab.
- 45–60 — Cesty: 
- 60–75 — Inspect before change: Použij file/stat/cat nebo head podle typu dat.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- Napiš význam 5 adresářů.
- 5 příkazů + co vrací.
- Příklad absolutní/relativní cesty.
- Bezpečnostní pravidlo před rm.

### Lekce 11 — Uživatelé, skupiny a oprávnění
**Cíl:** Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.

**Příprava učitele:**
- Používej model „kdo potřebuje co dělat“.
- Propoj s budoucím SSH a webserverem.

**Časový plán:**
- 0–15 — Owner / Group / Other: Přečti ls -l.
- 15–30 — rwx: Převeď rw-r----- na význam.
- 30–45 — Skupinový přístup: Navrhni skupinu webteam.
- 45–60 — Least privilege: 
- 60–75 — Vlastnictví: Vysvětli rozdíl chmod a chown.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- Rozepiš 3 permission strings.
- Navrhni mode pro config/log/shared file.
- Vysvětli chmod vs chown.

### Lekce 12 — Procesy + systemd: služba není magie
**Cíl:** Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.

**Příprava učitele:**
- Doporuč bezpečný lokální lab/VM.
- Připomínej syntax check před reloadem webserveru.

**Časový plán:**
- 0–15 — Proces a služba: Použij ps/top nebo ekvivalent.
- 15–30 — systemctl status: Přečti Active, Main PID a poslední log řádky.
- 30–45 — start/restart/reload: Vysvětli rozdíl restart a reload.
- 45–60 — Enable vs start: 
- 60–75 — Failed service: Z statusu najdi první konkrétní chybu.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- Process vs service.
- systemctl status – 5 polí k přečtení.
- Restart vs reload vs enable.
- Incident: služba failed – další test.

### Lekce 13 — Logy a journalctl: časová osa důkazů
**Cíl:** Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.

**Příprava učitele:**
- Učte časovou korelaci napříč logy.
- Student má vysvětlit, proč konkrétní filtr používá.

**Časový plán:**
- 0–15 — Co je užitečný log: Najdi timestamp, source/service, severity a message.
- 15–30 — Filtruj: Omez log na konkrétní unit/službu.
- 30–45 — Sleduj změnu: Spusť follow/tail jen při reprodukci testovacího problému.
- 45–60 — Noise: 
- 60–75 — Korelace: Propoj service status + journal + port test.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- Incident timestamp.
- 3 filtry logu.
- Hypotéza potvrzena/vyloučena.

### Lekce 14 — SSH/SFTP: bezpečný vzdálený přístup
**Cíl:** Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.

**Příprava učitele:**
- Nikdy nevyžaduj reálné studentské private keys do odevzdání.
- Pracuj s testovacími klíči/VM.

**Časový plán:**
- 0–15 — Cesta SSH spojení: Ověř DNS/IP, TCP/22 a teprve potom autentizaci.
- 15–30 — Klíče: Rozliš private/public key.
- 30–45 — Permissions: Ověř bezpečné oprávnění privátního klíče.
- 45–60 — Permission denied: 
- 60–75 — SFTP: Přeneste testovací soubor.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- SSH diagnostická cesta.
- Private vs public key.
- 3 příčiny Permission denied.
- SFTP transfer check.

### Lekce 15 — Firewall + služby: co poslouchá a kdo se tam dostane
**Cíl:** Propojit listener, bind adresu, firewall a client test do jednoho diagnostického modelu.

**Příprava učitele:**
- Používej sandbox pravidla; nedělej změny na produkčním školním serveru.
- Důraz na rollback/console access v reálné správě.

**Časový plán:**
- 0–15 — Listener ≠ dostupnost: Rozliš proces poslouchá / bind address / firewall / routa.
- 15–30 — ss/lsof mindset: Zjisti port a bind adresu.
- 30–45 — Firewall rule: Povol jen potřebný source/service.
- 45–60 — Bind: 
- 60–75 — Positive + negative test: Ověř povolenou cestu.
- 75–90 — Exit ticket: 

**Evidence k uzavření lekce:**
- Service matrix 4 řádky.
- Listener/bind evidence.
- Firewall allow + deny test.

### Lekce 16 — Linux webová služba: od procesu k HTTP
**Cíl:** Nasadit nebo analyzovat jednoduchou webovou službu a ověřit process → listener → firewall → DNS → HTTP.

**Příprava učitele:**
- Může být simulované prostředí, Docker/VM nebo předpřipravený lab.
- Nezáviset na veřejném DNS.

**Časový plán:**
- 0–15 — Service chain: Proces běží.
- 15–30 — Local health: Otestuj localhost/health nebo lokální curl.
- 30–45 — Remote path: Otestuj službu z klienta.
- 45–60 — HTTP evidence: 
- 60–75 — DNS name: Namapuj testovací jméno nebo interpretuj záznam.
- 75–90 — Runbook: Napiš 5 kroků ověření služby od lokálního procesu po klienta.

**Evidence k uzavření lekce:**
- Service chain diagram.
- Local/remote test.
- HTTP status evidence.
- 5krokový runbook.

### Lekce 17 — Shell + cron: malá automatizace s logem a bezpečným selháním
**Cíl:** Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.

**Příprava učitele:**
- Používej neškodné úlohy (např. health report, kopie test souboru).
- Neautomatizujte destruktivní příkazy.

**Časový plán:**
- 0–15 — Skript jako opakovatelný postup: Použij jasný vstup, výstup a exit status.
- 15–30 — Guard clauses: Před změnou ověř podmínky.
- 30–45 — Log output: Přidej timestamp a výsledek.
- 45–60 — Cron/system timer: Naplánuj testovací úlohu.
- 60–75 — Cron problém: 
- 75–90 — Ověření: Prokaž, že job proběhl.

**Evidence k uzavření lekce:**
- Pseudo/real script 8–20 řádků.
- Guard condition.
- Log sample.
- Schedule.
- Verification evidence.

### Lekce 18 — Capstone: nefunguje služba – Linux + síť v jednom incidentu
**Cíl:** Systematicky vyřešit kombinovaný incident od klienta přes DNS/síť/firewall až po systemd službu a doložit root cause.

**Příprava učitele:**
- Vhodné jako mastery/capstone 3.A.
- Hodnoť diagnostický proces, ne rychlost náhodného nalezení chyby.

**Časový plán:**
- 0–15 — Symptom + scope: Zapiš co nefunguje, komu a od kdy.
- 15–30 — Síťová evidence: IP/gateway/DNS.
- 30–45 — Server evidence: Service status.
- 45–60 — Nejmenší oprava: Změň jen potvrzenou příčinu.
- 60–75 — Validace: Ověř původní user path.
- 75–90 — Incident note: 

**Evidence k uzavření lekce:**
- Incident timeline.
- Hypotézy a testy.
- Root cause.
- Oprava + rollback.
- 3 validační důkazy.

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

### Lekce 19 — Filesystem incident
**Cíl:** Diagnostikovat permission/disk/mount problém bez změn naslepo.

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

### Lekce 20 — systemd dependency lab
**Cíl:** Rozlišit problém procesu, unit konfigurace, dependency a restart policy.

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

### Lekce 21 — Journal forensic
**Cíl:** Najít relevantní incidentní okno a spojit log se změnou a symptomem.

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

### Lekce 22 — SSH keys in practice
**Cíl:** Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.

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

### Lekce 23 — DNS evidence chain
**Cíl:** Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.

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

### Lekce 24 — Stateful firewall lab
**Cíl:** Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.

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

### Lekce 25 — Safe Bash automation
**Cíl:** Napsat opakovatelný skript s kontrolou vstupů, chybami a logem.

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

### Lekce 26 — Service recovery drill
**Cíl:** Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

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

### Lekce 27 — Team incident lab
**Cíl:** Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.

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

### Lekce 28 — Mastery review
**Cíl:** Zopakovat nejslabší skills a dokončit praktické mastery evidence před závěrečným projektem.

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
