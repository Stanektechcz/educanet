# Cognitive Lab · Lekce 14 · Containers: proces, image, volume a síť

**Třída:** class_4a  
**Rodina:** service  
**Renderer:** flow  
**Primary topic:** containers-basics

## Situace
Proces běží, ale uživatel stále dostává chybu nebo connection refused. V této lekci je cílem: Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.

## Freeze & predict
Co ověřit po process state?
- A. Barvu loga
- B. Socket/binding, dependency a aplikační health **← očekávaný směr**
- C. Jen PID

## X-Ray vrstvy
- **Service manager** — Unit/process lifecycle.
- **Process** — PID a exit status.
- **Socket** — Kde proces opravdu poslouchá.
- **Application health** — Zda odpoví správný protokol a obsah.

## Timeline scrubber
1. Unit start
2. Process spawn
3. Bind socket
4. Dependency ready
5. Health request
6. Valid response

## Contrast case
**Typická past:** Running process = funkční aplikace.

**Funkční model:** Process, listener a application health jsou oddělené vrstvy.

**Proč:** Každá vrstva potřebuje vlastní důkaz.

## Build the model
`Unit → Process → Socket → Dependency → Health check → User request`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Service manager: Unit/process lifecycle.
- Process: PID a exit status.
- Socket: Kde proces opravdu poslouchá.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Container je izolovaný runtime procesu vytvořený z image; persistentní data a síť jsou samostatné vrstvy.
- **Typická past:** Running process = funkční aplikace.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Running process = funkční aplikace.

