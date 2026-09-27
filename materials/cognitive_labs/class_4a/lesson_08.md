# Cognitive Lab · Lekce 8 · Infrastructure as Code + drift

**Třída:** class_4a  
**Rodina:** iac  
**Renderer:** flow  
**Primary topic:** infrastructure-as-code

## Situace
Produkce se liší od deklarované konfigurace, protože někdo provedl ruční změnu. V této lekci je cílem: Řídit produkční konfiguraci jako verzovaný desired state, kontrolovat diff před změnou a bezpečně řešit configuration drift.

## Freeze & predict
Jak minimalizovat drift?
- A. Zakázat monitoring
- B. Deklarovat stav, plánovat diff, reviewovat a aplikovat reprodukovatelně **← očekávaný směr**
- C. Dokumentovat ruční změny v chatu

## X-Ray vrstvy
- **Desired state** — Verzovaná deklarace.
- **Actual state** — Co skutečně běží.
- **Plan/diff** — Viditelný rozdíl před změnou.
- **Apply + verify** — Kontrolovaná změna a následná validace.

## Timeline scrubber
1. Read desired state
2. Read actual state
3. Compute diff
4. Review
5. Apply
6. Verify no drift

## Contrast case
**Typická past:** Ruční změny bez zdroje pravdy.

**Funkční model:** Deklarovaný stav + diff + repeatable apply.

**Proč:** IaC přesouvá infrastrukturu z paměti lidí do kontrolovaného systému změn.

## Build the model
`Desired config → Actual config → Diff → Review → Apply → Verify`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Desired state: Verzovaná deklarace.
- Actual state: Co skutečně běží.
- Plan/diff: Viditelný rozdíl před změnou.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Přeneste provozní změny z ručních kroků do deklarativního, verzovaného workflow s plánem změn a kontrolovatelným review.
- **Typická past:** Ruční změny bez zdroje pravdy.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Ruční změny bez zdroje pravdy.

