# Cognitive Lab · Lekce 21 · Journal forensic

**Třída:** class_3a  
**Rodina:** logs  
**Renderer:** timeline  
**Primary topic:** journal-logs

## Situace
V logu je mnoho chyb, ale potřebuješ najít první událost, která změnila stav systému. V této lekci je cílem: Najít relevantní incidentní okno a spojit log se změnou a symptomem.

## Freeze & predict
Jak pracovat s logy?
- A. Číst od začátku souboru bez času
- B. Sestavit časovou osu kolem symptomu a změny **← očekávaný směr**
- C. Vybrat nejčervenější řádek

## X-Ray vrstvy
- **Change** — Deploy/config/restart.
- **Symptom** — První pozorovaný dopad.
- **Evidence** — Relevantní logy v časovém okně.
- **Verification** — Co se stane po opravě.

## Timeline scrubber
1. Known good
2. Změna konfigurace
3. První warning
4. První failure
5. Oprava + ověření

## Contrast case
**Typická past:** Jedna error message bez časového kontextu.

**Funkční model:** Časová korelace změny, symptomu a dalších důkazů.

**Proč:** Logy jsou evidence, ale příčina vzniká až jejich interpretací v kontextu.

## Build the model
`Known good → Change → Symptom → Evidence window → Fix → Verification`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Change: Deploy/config/restart.
- Symptom: První pozorovaný dopad.
- Evidence: Relevantní logy v časovém okně.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.
- **Typická past:** Jedna error message bez časového kontextu.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Jedna error message bez časového kontextu.

