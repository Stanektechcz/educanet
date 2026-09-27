# Learning Studio · Lekce 26 · Service recovery drill

**Třída:** class_3a  
**Lekce:** 26  
**Rodina:** service  
**Primary topic:** service-debug-chain

## Big idea
Funkční služba vyžaduje proces, správný bind/listener, dostupnost závislostí a validní odpověď.

## Reprezentace
- **Realita** — Lekce 26 · Service recovery drill: Proces běží, ale uživatel stále dostává chybu nebo connection refused. V této lekci je cílem: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Funkční služba vyžaduje proces, správný bind/listener, dostupnost závislostí a validní odpověď.
- **Kontrast** — Rozdíl, který rozhoduje: Každá vrstva potřebuje vlastní důkaz.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Unit → Socket → Process → Dependency → Health check → User request`
- Funkční model: `Unit → Process → Socket → Dependency → Health check → User request`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to obchod: nestačí, že je zaměstnanec uvnitř; dveře musí být otevřené a služba musí skutečně obsloužit zákazníka.

**Limit přirovnání:** Služba může být distribuovaná, health check může měřit jen část funkce a odpověď může být cacheovaná.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `proces`, `listener`, `port`, `health`, `odpověď`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Funkční služba vyžaduje proces, správný bind/listener, dostupnost závislostí a validní odpověď.
- **Past** — Running process = funkční aplikace.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází.
- Past: Running process = funkční aplikace.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

