# Learning Studio · Lekce 3 · Observability: logy, metriky, health

**Třída:** class_4a  
**Lekce:** 3  
**Rodina:** observability  
**Primary topic:** logs-monitoring

## Big idea
Observability spojuje metriky, logy a trasování tak, aby šlo z pozorovaných výstupů odvodit vnitřní stav systému.

## Reprezentace
- **Realita** — Lekce 3 · Observability: logy, metriky, health: Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Získat jistotu v tom, jak z monitoringu a logů vytvořit důkaz místo intuice.
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
- Jedna věta: Monitoring říká, že něco selhává. Logy pomáhají vysvětlit proč. Největší hodnotu mají, když je spojíš s časem, requestem a změnou.
- Past: Jedna metrika = příčina.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

