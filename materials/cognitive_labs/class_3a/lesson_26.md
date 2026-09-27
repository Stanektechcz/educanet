# Cognitive Lab · Lekce 26 · Service recovery drill

**Třída:** class_3a  
**Rodina:** service  
**Renderer:** flow  
**Primary topic:** service-debug-chain

## Situace
Proces běží, ale uživatel stále dostává chybu nebo connection refused. V této lekci je cílem: Obnovit webovou službu přes process → socket → firewall → DNS → HTTP chain.

## Freeze & predict
Co ověřit po process state?
- A. Jen PID
- B. Barvu loga
- C. Socket/binding, dependency a aplikační health **← očekávaný směr**

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
- **Jedna věta:** Spoj předchozí síťové znalosti do jedné rychlé diagnostické cesty a rozhoduj podle evidence, ve které vrstvě se problém nachází.
- **Typická past:** Running process = funkční aplikace.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Running process = funkční aplikace.

