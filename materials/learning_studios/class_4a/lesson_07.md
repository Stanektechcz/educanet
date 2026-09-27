# Learning Studio · Lekce 7 · SLO, error budget a postmortem

**Třída:** class_4a  
**Lekce:** 7  
**Rodina:** observability  
**Primary topic:** slo-postmortem

## Big idea
Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.

## Reprezentace
- **Realita** — Lekce 7 · SLO, error budget a postmortem: Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Převést provozní data na rozhodnutí: kdy pokračovat v release, kdy stabilizovat a jak z incidentu vytvořit konkrétní follow-up.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.
- **Kontrast** — Rozdíl, který rozhoduje: Observability pomáhá klást otázky nad systémem, ne jen sbírat grafy.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Traffic → Latency → Errors → Saturation → Change timeline`
- Funkční model: `Traffic → Errors → Latency → Saturation → Change timeline`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to palubní deska auta doplněná servisním logem: jeden budík málokdy vysvětlí příčinu.

**Limit přirovnání:** Více telemetrie automaticky neznamená lepší diagnózu; záleží na správných signálech a kontextu.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `metrika`, `log`, `latence`, `chyba`, `saturace`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.
- **Past** — Jedna metrika = příčina.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Provozní rozhodování musí stát na měřených cílech. SLO říká očekávanou spolehlivost, error budget prostor pro riziko a postmortem pomáhá zabránit opakování incidentu.
- Past: Jedna metrika = příčina.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

