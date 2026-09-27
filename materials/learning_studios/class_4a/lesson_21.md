# Learning Studio · Lekce 21 · Container networking

**Třída:** class_4a  
**Lekce:** 21  
**Rodina:** containers  
**Primary topic:** container-networking

## Big idea
Kontejner izoluje proces a jeho filesystem/network namespace, ale sdílí kernel a závisí na hostu i orchestrace.

## Reprezentace
- **Realita** — Lekce 21 · Container networking: Container běží, ale služba není dostupná z hosta nebo z jiné služby. V této lekci je cílem: Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Kontejner izoluje proces a jeho filesystem/network namespace, ale sdílí kernel a závisí na hostu i orchestrace.
- **Kontrast** — Rozdíl, který rozhoduje: Container přidává síťový namespace, ne magickou konektivitu.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Container → Bind address → Process → Container network → Published port/DNS → Client`
- Funkční model: `Container → Process → Bind address → Container network → Published port/DNS → Client`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to oddělená pracovní kabina ve stejné hale: vlastní prostor, ale společná budova a infrastruktura.

**Limit přirovnání:** Izolace není plná virtualizace a síť, storage či cgroups mají vlastní komplexitu.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `image`, `container`, `namespace`, `port`, `volume`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Kontejner izoluje proces a jeho filesystem/network namespace, ale sdílí kernel a závisí na hostu i orchestrace.
- **Past** — Container running = port dostupný.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Kontejner má vlastní network namespace; dostupnost služby závisí na bindu, port mappingu, síti a policy.
- Past: Container running = port dostupný.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

