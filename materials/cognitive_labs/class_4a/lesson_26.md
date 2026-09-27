# Cognitive Lab · Lekce 26 · Performance capacity

**Třída:** class_4a  
**Rodina:** performance  
**Renderer:** metrics  
**Primary topic:** performance-engineering

## Situace
Latency roste pod zátěží. Potřebuješ určit limitující zdroj místo náhodného škálování všeho. V této lekci je cílem: Najít bottleneck, pracovat s p95/error rate a navrhnout headroom.

## Freeze & predict
Co hledáš?
- A. Největší log file
- B. Vztah load → saturation → latency/errors u konkrétního zdroje **← očekávaný směr**
- C. Pouze nejvyšší CPU číslo

## X-Ray vrstvy
- **Load** — RPS/concurrency/throughput.
- **Resource** — CPU, memory, IO, connections, queue.
- **Saturation** — Fronta a čekání na limitu.
- **Impact** — Latency/errors a business dopad.

## Timeline scrubber
1. Baseline load
2. Load grows
3. Resource approaches limit
4. Queue/saturation
5. Latency/errors rise

## Contrast case
**Typická past:** Vysoké CPU samo o sobě = bottleneck.

**Funkční model:** Bottleneck je zdroj, jehož saturace koreluje s dopadem pod konkrétní zátěží.

**Proč:** Capacity planning potřebuje model load, limitu a rezervy.

## Build the model
`Load → Resource → Capacity limit → Saturation → Latency/errors → Headroom`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Load: RPS/concurrency/throughput.
- Resource: CPU, memory, IO, connections, queue.
- Saturation: Fronta a čekání na limitu.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Rozliš hlavní výkonové signály a nauč se hledat skutečné úzké hrdlo místo slepého přidávání výkonu.
- **Typická past:** Vysoké CPU samo o sobě = bottleneck.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Vysoké CPU samo o sobě = bottleneck.

