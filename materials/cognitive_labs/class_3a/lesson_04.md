# Cognitive Lab · Lekce 4 · Monitoring + základní hardening

**Třída:** class_3a  
**Rodina:** observability  
**Renderer:** metrics  
**Primary topic:** service-matrix

## Situace
Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Rozlišit dostupnost, zdraví služby a bezpečné minimum: monitoring, logy, firewall a účty.

## Freeze & predict
Co máš korelovat?
- A. Metriky, logy, health a časovou osu změn **← očekávaný směr**
- B. Jen průměrné CPU
- C. Pouze poslední error log

## X-Ray vrstvy
- **Traffic** — Kolik práce systém dostává.
- **Errors** — Kolik požadavků selhává.
- **Latency** — Jak dlouho trvá odpověď.
- **Saturation** — Kde dochází kapacita.

## Timeline scrubber
1. Baseline
2. Změna/deploy
3. Růst traffic
4. Saturace zdroje
5. Latency/errors rostou

## Contrast case
**Typická past:** Jedna metrika = příčina.

**Funkční model:** Více signálů + časová korelace = podložená hypotéza.

**Proč:** Observability pomáhá klást otázky nad systémem, ne jen sbírat grafy.

## Build the model
`Traffic → Errors → Latency → Saturation → Change timeline`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Traffic: Kolik práce systém dostává.
- Errors: Kolik požadavků selhává.
- Latency: Jak dlouho trvá odpověď.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Místo pravidla „povolíme server“ je přesnější popsat zdroj, cíl, protokol a port. To je základ čitelného firewall návrhu.
- **Typická past:** Jedna metrika = příčina.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Jedna metrika = příčina.

