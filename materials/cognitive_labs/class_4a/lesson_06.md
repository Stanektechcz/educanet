# Cognitive Lab · Lekce 6 · Canary release + backup/restore drill

**Třída:** class_4a  
**Rodina:** release  
**Renderer:** timeline  
**Primary topic:** canary-release

## Situace
Po deployi roste error rate. Potřebuješ rozhodnout, zda pokračovat, zastavit nebo rollbackovat. V této lekci je cílem: Provést řízené nasazení na malou část provozu a nacvičit rozhodnutí rollback vs. restore na základě měřitelných signálů.

## Freeze & predict
Jaké rozhodnutí je bezpečné?
- A. Porovnat předem definované health/SLO signály s rollback threshold **← očekávaný směr**
- B. Čekat bez měření
- C. Pokračovat, protože deploy doběhl bez chyby

## X-Ray vrstvy
- **Baseline** — Known-good stav před změnou.
- **Change** — Přesně známý scope deploye.
- **Observe** — Health, errors, latency a business signal.
- **Rollback** — Předem připravený návrat a validace.

## Timeline scrubber
1. Baseline
2. Deploy small scope
3. Observe
4. Threshold crossed?
5. Continue / rollback
6. Verify

## Contrast case
**Typická past:** Úspěšný CI job = bezpečný release.

**Funkční model:** Release je změna + měření dopadu + připravený návrat.

**Proč:** Bez rollback kritérií je rozhodnutí ovlivněné dojmem a tlakem.

## Build the model
`Baseline → Change → Canary → Observe → Decision gate → Rollback/continue → Verify`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Baseline: Known-good stav před změnou.
- Change: Přesně známý scope deploye.
- Observe: Health, errors, latency a business signal.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Canary nasadí novou verzi malé části uživatelů a dovolí rozhodnout podle měření před plošným rolloutem.
- **Typická past:** Úspěšný CI job = bezpečný release.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Úspěšný CI job = bezpečný release.

