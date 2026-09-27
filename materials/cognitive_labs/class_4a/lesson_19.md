# Cognitive Lab · Lekce 19 · Golden signals lab

**Třída:** class_4a  
**Rodina:** observability  
**Renderer:** metrics  
**Primary topic:** golden-signals

## Situace
Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Rozpoznat user-facing incident kombinací latency, traffic, errors a saturation.

## Freeze & predict
Co máš korelovat?
- A. Jen průměrné CPU
- B. Pouze poslední error log
- C. Metriky, logy, health a časovou osu změn **← očekávaný směr**

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
- **Jedna věta:** Latency, traffic, errors a saturation dávají rychlý přehled, zda problém vidí uživatel a kde hledat dál.
- **Typická past:** Jedna metrika = příčina.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Jedna metrika = příčina.

