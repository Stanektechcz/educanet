# Cognitive Lab · Lekce 21 · Container networking

**Třída:** class_4a  
**Rodina:** containers  
**Renderer:** flow  
**Primary topic:** container-networking

## Situace
Container běží, ale služba není dostupná z hosta nebo z jiné služby. V této lekci je cílem: Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.

## Freeze & predict
Co rozlišit?
- A. Process, container network, binding/port publish a DNS/service discovery **← očekávaný směr**
- B. Jen image tag
- C. Jen host firewall

## X-Ray vrstvy
- **Process** — Co běží uvnitř containeru.
- **Container network** — Namespace a interní IP/DNS.
- **Binding** — Na jaké adrese/portu proces poslouchá.
- **Publish/route** — Jak se provoz dostane zvenku dovnitř.

## Timeline scrubber
1. Container starts
2. Process binds
3. Service discovery/IP
4. Port publish/route
5. Client connect

## Contrast case
**Typická past:** Container running = port dostupný.

**Funkční model:** Runtime stav, process listener a síťové zpřístupnění jsou různé vrstvy.

**Proč:** Container přidává síťový namespace, ne magickou konektivitu.

## Build the model
`Container → Process → Bind address → Container network → Published port/DNS → Client`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Process: Co běží uvnitř containeru.
- Container network: Namespace a interní IP/DNS.
- Binding: Na jaké adrese/portu proces poslouchá.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Kontejner má vlastní network namespace; dostupnost služby závisí na bindu, port mappingu, síti a policy.
- **Typická past:** Container running = port dostupný.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Container running = port dostupný.

