# Cognitive Lab · Lekce 7 · Packet journey + Wireshark mindset

**Třída:** class_3a  
**Rodina:** dns  
**Renderer:** flow  
**Primary topic:** packet-analysis

## Situace
Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Použít packet-level evidence k rozlišení lokální, DNS, TCP a aplikační závady bez bezhlavého spouštění všech nástrojů.

## Freeze & predict
Který test má největší informační hodnotu jako první?
- A. Ověřit DNS odpověď pro hostname **← očekávaný směr**
- B. Restartovat webserver
- C. Vypnout firewall

## X-Ray vrstvy
- **Jméno** — Aplikace pracuje s hostname.
- **Resolver** — Zjistí IP adresu.
- **Síť** — Teprve potom vzniká spojení na IP.
- **Aplikace** — HTTP/HTTPS probíhá až po překladu jména.

## Timeline scrubber
1. Aplikace použije hostname
2. Stub resolver pošle dotaz
3. Resolver získá odpověď
4. Klient má cílovou IP
5. Až potom naváže spojení

## Contrast case
**Typická past:** DNS → webová stránka

**Funkční model:** DNS → IP → TCP/TLS → HTTP → obsah

**Proč:** DNS nepřenáší web; pouze poskytuje informace potřebné k nalezení cíle.

## Build the model
`Aplikace → Resolver → DNS odpověď → IP adresa → TCP/TLS → HTTP`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Jméno: Aplikace pracuje s hostname.
- Resolver: Zjistí IP adresu.
- Síť: Teprve potom vzniká spojení na IP.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Nauč se číst jednoduchou packet journey a z minimální evidence určit, ve které vrstvě komunikace selhává.
- **Typická past:** DNS → webová stránka
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: DNS → webová stránka

