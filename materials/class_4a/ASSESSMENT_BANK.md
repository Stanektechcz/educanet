# 4.A · Assessment bank

Otázky jsou určeny jako rychlý check/exit ticket. Správná odpověď je uvedena pod každou otázkou pro učitele.

## 1. Lekce 10 · systemd do hloubky: dependencies, restart policy, failure / 04 · Ordering
Co typicky vyjadřuje After=?

- A. Pořadí startu vůči jiné unit; samo o sobě nemusí vytvářet tvrdou závislost.
- B. Firewall allow rule.
- C. DNS priority.

**Správně:** A — Ordering a dependency nejsou totéž.

## 2. Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident / 04 · Inodes
Filesystem hlásí volné GB, ale nelze vytvořit soubor. Co může být problém?

- A. Vyčerpané inodes / příliš mnoho souborů.
- B. DNS cache.
- C. TLS SAN.

**Správně:** A — Kapacita v bajtech není jediný limit filesystemu.

## 3. Lekce 12 · Backup strategie: RPO/RTO a restore drill / 04 · Backup vs restore
Kdy je backup skutečně důvěryhodný?

- A. Když byl obnoven a výsledek ověřen.
- B. Když job skončil zelenou ikonou.
- C. Když je soubor velký.

**Správně:** A — Bez restore testu neznáš reálnou obnovitelnost.

## 4. Lekce 13 · Hardening Linux služby: SSH, firewall, aktualizace a minimální práva / 05 · Lockout risk
Co musíš řešit před zpřísněním vzdáleného SSH/firewall přístupu?

- A. Ověřený alternativní přístup/rollback, aby ses nezamkl venku.
- B. Jen barvu promptu.
- C. TTL webového obrázku.

**Správně:** A — Hardening bez recovery plánu může vytvořit vlastní incident.

## 5. Lekce 14 · Containers: proces, image, volume a síť / 04 · Recreate
Co se typicky stane s daty uloženými jen ve writable layer containeru po jeho odstranění/recreate?

- A. Mohou být ztracena; persistentní data patří do vhodného volume/storage.
- B. Automaticky se přesunou do DNS.
- C. Vždy se uloží do image registry.

**Správně:** A — Ephemeral runtime a persistent storage jsou oddělené koncepty.

## 6. Lekce 15 · Automatizace + configuration consistency / 04 · Automatizace
Co je horší než ruční postup?

- A. Automatizace, která rychle a opakovaně provádí chybnou změnu bez guardů.
- B. Skript s dry-runem.
- C. Validace po změně.

**Správně:** A — Automatizace násobí dobré i špatné rozhodnutí.

## 7. Lekce 16 · Pokročilá síťová diagnostika: packet evidence + socket state / 04 · RST
SYN → okamžitý RST typicky znamená?

- A. Cíl je dosažitelný, ale port/spojení je aktivně odmítnuté.
- B. DNS dotaz se nikdy neposlal.
- C. Klient nemá MAC adresu gateway.

**Správně:** A — RST je explicitní TCP odpověď.

## 8. Lekce 17 · Incident response: runbook, komunikace a blameless postmortem / 05 · Postmortem
Co je cílem blameless postmortemu?

- A. Pochopit systémové faktory a definovat konkrétní preventivní akce.
- B. Najít jednoho člověka k potrestání.
- C. Vyhnout se technickým detailům.

**Správně:** A — Postmortem má zlepšit systém a proces.

## 9. Lekce 18 · Capstone: produkční reliability drill / 06 · Postmortem defense
Co nejlépe dokazuje zvládnutí capstone?

- A. Konzistentní evidence chain, bezpečná obnova, validace a konkrétní preventivní kroky.
- B. Co nejvíc spuštěných příkazů.
- C. Jedna správná náhodná změna.

**Správně:** A — Reliability je opakovatelný rozhodovací proces.

<!-- V30_LESSONS:START -->
## v30 · Assessment Bank — lekce 19–28

Otázky jsou určené hlavně jako **retrieval practice a exit tickets**. Pokud student odpoví špatně, vrať jej k jiné formě vysvětlení a potom nabídni novou aplikaci stejného principu, ne pouze stejnou otázku.

### Lekce 19 · Golden signals lab / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 19 · Golden signals lab / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 20 · Reverse proxy evidence / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 20 · Reverse proxy evidence / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 21 · Container networking / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 21 · Container networking / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 22 · Restore game day / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 22 · Restore game day / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 23 · Hardening audit / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 23 · Hardening audit / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 24 · Safe change & rollback / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 24 · Safe change & rollback / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 25 · Drift & automation / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 25 · Drift & automation / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 26 · Performance capacity / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 26 · Performance capacity / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 27 · Incident command / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 27 · Incident command / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 28 · Reliability mastery / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 28 · Reliability mastery / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.
<!-- V30_LESSONS:END -->
