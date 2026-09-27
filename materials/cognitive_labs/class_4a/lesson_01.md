# Cognitive Lab · Lekce 1 · Základní dvouhodinový blok

**Třída:** class_4a  
**Rodina:** dns  
**Renderer:** flow  
**Primary topic:** cidr

## Situace
Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.

## Freeze & predict
Který test má největší informační hodnotu jako první?
- A. Restartovat webserver
- B. Vypnout firewall
- C. Ověřit DNS odpověď pro hostname **← očekávaný směr**

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
- **Jedna věta:** Prefix, síťová adresa, broadcast a použitelné adresy.
- **Typická past:** DNS → webová stránka
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: DNS → webová stránka

