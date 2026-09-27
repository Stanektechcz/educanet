# 3.A · Assessment bank

Otázky jsou určeny jako rychlý check/exit ticket. Správná odpověď je uvedena pod každou otázkou pro učitele.

## 1. Lekce 10 · Linux CLI + filesystem: orientace bez klikání / 04 · Cesty
Která cesta začínající / je absolutní?

- A. /var/log/nginx/error.log
- B. ../logs/error.log
- C. ./error.log

**Správně:** A — Absolutní cesta začíná od root filesystemu.

## 2. Lekce 10 · Linux CLI + filesystem: orientace bez klikání / 06 · Exit ticket
Proč je před destruktivním příkazem důležité ověřit pwd a argument?

- A. Chyba v cestě může zasáhnout jiná data než zamýšlíš.
- B. Protože pwd restartuje shell.
- C. Kvůli DNS.

**Správně:** A — Bezpečná práce v CLI stojí na kontrole scope změny.

## 3. Lekce 11 · Uživatelé, skupiny a oprávnění / 04 · Least privilege
Které oprávnění je bezpečnější pro soubor, který má číst vlastník i skupina, ale měnit jen vlastník?

- A. 640
- B. 777
- C. 666

**Správně:** A — 640 dává owner rw, group r a others nic.

## 4. Lekce 11 · Uživatelé, skupiny a oprávnění / 06 · Exit ticket
Proč není chmod 777 univerzální oprava?

- A. Dává všem zbytečně široká práva a maskuje skutečný model přístupu.
- B. Protože zakazuje čtení.
- C. Protože mění IP adresu.

**Správně:** A — Oprávnění se mají odvíjet od potřebných rolí.

## 5. Lekce 12 · Procesy + systemd: služba není magie / 04 · Enable vs start
Co dělá enable typicky?

- A. Nastaví automatické spuštění služby při odpovídajícím boot targetu; nemusí ji právě teď spustit.
- B. Vymaže logy služby.
- C. Otevře firewall port.

**Správně:** A — Enable a runtime start jsou dvě odlišné věci.

## 6. Lekce 12 · Procesy + systemd: služba není magie / 06 · Exit ticket
Proč je „restartuj to“ slabá první diagnostika?

- A. Mění stav dřív, než získáš evidence, a může skrýt příčinu.
- B. Restart je vždy pomalý.
- C. Restart funguje jen na Windows.

**Správně:** A — Diagnostika má nejprve pozorovat a lokalizovat problém.

## 7. Lekce 13 · Logy a journalctl: časová osa důkazů / 04 · Noise
Co je nejlepší při dlouhém logu?

- A. Začít incidentním časem a konkrétní službou.
- B. Číst celý log od začátku systému.
- C. Vymazat log a čekat.

**Správně:** A — Filtr snižuje šum a chrání kauzalitu.

## 8. Lekce 13 · Logy a journalctl: časová osa důkazů / 06 · Exit ticket
Co znamená, že log neobsahuje chybu?

- A. Samo o sobě to nedokazuje, že systém je v pořádku; možná sleduješ špatnou vrstvu nebo zdroj.
- B. Služba je určitě zdravá.
- C. Firewall je vypnutý.

**Správně:** A — Absence záznamu je slabší evidence než cílený pozitivní test.

## 9. Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup / 04 · Permission denied
TCP/22 funguje, ale SSH vrací Permission denied. Kde je nejsilnější hypotéza?

- A. Autentizace/uživatel/klíč, ne základní síťová dostupnost.
- B. DHCP server.
- C. Monitor počítače.

**Správně:** A — Aplikace už odpověděla, takže síťová cesta k SSH existuje.

## 10. Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup / 06 · Exit ticket
Který soubor je tajný?

- A. Private key.
- B. Public key.
- C. known_hosts bez dalšího kontextu.

**Správně:** A — Private key chrání identitu a nesmí se sdílet.

## 11. Lekce 15 · Firewall + služby: co poslouchá a kdo se tam dostane / 04 · Bind
Služba poslouchá pouze 127.0.0.1:8080. Co očekáváš z jiného stroje?

- A. Nebude přímo dostupná přes síťové rozhraní, i kdyby firewall port dovoloval.
- B. Bude vždy dostupná.
- C. DNS se automaticky změní.

**Správně:** A — Loopback bind omezuje listener na lokální host.

## 12. Lekce 15 · Firewall + služby: co poslouchá a kdo se tam dostane / 06 · Exit ticket
Co je správná firewall validace?

- A. Otestovat povolený i zakázaný scénář z relevantního zdroje.
- B. Otestovat jen ping.
- C. Zkontrolovat pouze syntaxi pravidla.

**Správně:** A — Bez negativního testu nevíš, zda jsi neotevřel příliš mnoho.

## 13. Lekce 16 · Linux webová služba: od procesu k HTTP / 04 · HTTP evidence
Když reverse proxy vrátí 502, co to dokazuje?

- A. Proxy je dosažitelná, ale má problém komunikovat s upstreamem nebo dostat validní odpověď.
- B. DNS vždy selhalo.
- C. Klient nemá IP.

**Správně:** A — HTTP 502 vzniká na proxy/gateway vrstvě.

## 14. Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním / 05 · Cron problém
Skript funguje ručně, ale ne z cronu. Co ověřit mezi prvními?

- A. PATH, pracovní adresář, uživatele a logovaný stderr.
- B. Barvu terminálu.
- C. DNS TTL webu.

**Správně:** A — Naplánované prostředí se může lišit od interaktivního shellu.

## 15. Lekce 18 · Capstone: nefunguje služba – Linux + síť v jednom incidentu / 06 · Incident note
Co patří do stručného incident záznamu?

- A. Symptom, evidence, root cause, změna a validační důkaz.
- B. Jen „restart hotov“.
- C. Seznam všech příkazů bez závěru.

**Správně:** A — Záznam má umožnit pochopit rozhodnutí a ověřit opravu.

<!-- V30_LESSONS:START -->
## v30 · Assessment Bank — lekce 19–28

Otázky jsou určené hlavně jako **retrieval practice a exit tickets**. Pokud student odpoví špatně, vrať jej k jiné formě vysvětlení a potom nabídni novou aplikaci stejného principu, ne pouze stejnou otázku.

### Lekce 19 · Filesystem incident / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 19 · Filesystem incident / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 20 · systemd dependency lab / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 20 · systemd dependency lab / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 21 · Journal forensic / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 21 · Journal forensic / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 22 · SSH keys in practice / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 22 · SSH keys in practice / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 23 · DNS evidence chain / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 23 · DNS evidence chain / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 24 · Stateful firewall lab / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 24 · Stateful firewall lab / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 25 · Safe Bash automation / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 25 · Safe Bash automation / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 26 · Service recovery drill / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 26 · Service recovery drill / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 27 · Team incident lab / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 27 · Team incident lab / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 28 · Mastery review / 02 · Předpověz výsledek
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.

### Lekce 28 · Mastery review / 06 · Exit ticket
Který postup je nejlepší při diagnostice?

- A. Symptom → hypotéza → test → důkaz → nejmenší změna → validace.
- B. Restartovat vše a sledovat, zda problém zmizí.
- C. Měnit firewall, DNS a službu současně.

**Správně:** A — Evidence-first diagnostika minimalizuje vedlejší škody a umožňuje prokázat skutečnou příčinu.
<!-- V30_LESSONS:END -->
