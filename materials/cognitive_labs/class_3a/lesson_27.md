# Cognitive Lab · Lekce 27 · Team incident lab

**Třída:** class_3a  
**Rodina:** firewall  
**Renderer:** flow  
**Primary topic:** stateful-firewall

## Situace
Služba poslouchá na serveru, lokálně funguje, ale klient z jiné sítě se nepřipojí. V této lekci je cílem: Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.

## Freeze & predict
Co musíš odlišit?
- A. CPU od inode usage
- B. Binding služby, routing a firewall policy/state **← očekávaný směr**
- C. DNS od typografie

## X-Ray vrstvy
- **Listener** — Na jaké adrese/portu služba poslouchá.
- **Route** — Zda paket k serveru dorazí.
- **Firewall policy** — Pravidlo pro směr, adresu, port a state.
- **Return path** — Odpověď musí mít platnou cestu zpět.

## Timeline scrubber
1. Client SYN
2. Route to server
3. Firewall decision
4. Listener receives
5. SYN/ACK return

## Contrast case
**Typická past:** Open port v konfiguraci = dostupná služba.

**Funkční model:** Dostupnost vyžaduje listener + route + policy + return path.

**Proč:** Firewall je jen jedna část celé cesty.

## Build the model
`Client → Route → Firewall rule/state → Listener → Return path`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Listener: Na jaké adrese/portu služba poslouchá.
- Route: Zda paket k serveru dorazí.
- Firewall policy: Pravidlo pro směr, adresu, port a state.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Stavový firewall rozlišuje nové a navazující spojení a umožňuje přesnější least-privilege pravidla.
- **Typická past:** Open port v konfiguraci = dostupná služba.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Open port v konfiguraci = dostupná služba.

