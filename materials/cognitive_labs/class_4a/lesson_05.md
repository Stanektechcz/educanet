# Cognitive Lab · Lekce 5 · HTTP observability + load balancing

**Třída:** class_4a  
**Rodina:** observability  
**Renderer:** metrics  
**Primary topic:** http-observability

## Situace
Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Číst HTTP odpověď jako diagnostický důkaz a pochopit základní chování load balanceru při zdravém i degradovaném backendu.

## Freeze & predict
Co máš korelovat?
- A. Pouze poslední error log
- B. Metriky, logy, health a časovou osu změn **← očekávaný směr**
- C. Jen průměrné CPU

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
- **Jedna věta:** Jedna HTTP odpověď obsahuje více důkazů než jen body stránky. Status a headers často rychle ukážou, která vrstva reaguje.
- **Typická past:** Jedna metrika = příčina.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Jedna metrika = příčina.

