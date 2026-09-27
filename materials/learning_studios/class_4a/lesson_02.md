# Learning Studio · Lekce 2 · Produkční change window: proxy, TLS, logy a rollback

**Třída:** class_4a  
**Lekce:** 2  
**Rodina:** observability  
**Primary topic:** reverse-proxy

## Big idea
Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.

## Reprezentace
- **Realita** — Lekce 2 · Produkční change window: proxy, TLS, logy a rollback: Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Navázat na produkční incident a naučit se řízenou změnu: precheck, reverse proxy upstream, TLS certifikát, korelace logů, validace a rollback.
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
- Jedna věta: Reverse proxy přijímá požadavek klienta a předává ho interní aplikaci. Odděluje veřejný vstup od aplikačního procesu.
- Past: Jedna metrika = příčina.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

