# Cognitive Lab · Lekce 18 · Capstone: produkční reliability drill

**Třída:** class_4a  
**Rodina:** packet  
**Renderer:** flow  
**Primary topic:** systemd-advanced

## Situace
Aplikace hlásí timeout. Potřebuješ určit, ve které vrstvě komunikace se cesta zastavila. V této lekci je cílem: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

## Freeze & predict
Co ti dá packet/socket evidence?
- A. Rozliší, zda selhal handshake, přenos nebo aplikační odpověď **← očekávaný směr**
- B. Automaticky opraví konfiguraci
- C. Nahradí potřebu hypotézy

## X-Ray vrstvy
- **L2** — ARP/ND a doručení k next hopu.
- **L3** — IP adresa a routing.
- **L4** — TCP/UDP port a socket state.
- **L7** — Aplikační protokol a odpověď.

## Timeline scrubber
1. SYN odchází
2. SYN/ACK se vrací
3. ACK dokončí handshake
4. Aplikační request
5. Aplikační response

## Contrast case
**Typická past:** Timeout = „síť nefunguje“.

**Funkční model:** Evidence ukáže konkrétní místo: routing, TCP handshake nebo aplikace.

**Proč:** Stejný symptom může vzniknout v různých vrstvách.

## Build the model
`L2 reachability → IP route → TCP state → Application request → Application response`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- L2: ARP/ND a doručení k next hopu.
- L3: IP adresa a routing.
- L4: TCP/UDP port a socket state.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Pokročilá správa služby vyžaduje rozumět ordering, dependencies a restart policy, ne pouze příkazům start/stop.
- **Typická past:** Timeout = „síť nefunguje“.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Timeout = „síť nefunguje“.

