# Learning Studio · Lekce 14 · Containers: proces, image, volume a síť

**Třída:** class_4a  
**Lekce:** 14  
**Rodina:** service  
**Primary topic:** containers-basics

## Big idea
Funkční služba vyžaduje proces, správný bind/listener, dostupnost závislostí a validní odpověď.

## Reprezentace
- **Realita** — Lekce 14 · Containers: proces, image, volume a síť: Proces běží, ale uživatel stále dostává chybu nebo connection refused. V této lekci je cílem: Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.
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
- Jedna věta: Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.
- Past: Running process = funkční aplikace.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

