# Cognitive Lab · Lekce 15 · Automatizace + configuration consistency

**Třída:** class_4a  
**Rodina:** automation  
**Renderer:** flow  
**Primary topic:** automation-shell

## Situace
Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby. V této lekci je cílem: Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.

## Freeze & predict
Co z něj udělá bezpečnou automatizaci?
- A. Explicitní vstupy, error handling, idempotence, log a exit status **← očekávaný směr**
- B. Více `sleep`
- C. Spouštět vše jako root

## X-Ray vrstvy
- **Inputs** — Explicitní cesty, proměnné a preconditions.
- **Action** — Jeden kontrolovaný krok.
- **Failure handling** — Neskrývat chybu a nepokračovat naslepo.
- **Evidence** — Log/exit code a ověření výsledku.

## Timeline scrubber
1. Validate inputs
2. Run idempotent action
3. Check result
4. Log evidence
5. Exit success/failure

## Contrast case
**Typická past:** Skript předpokládá prostředí a ignoruje chyby.

**Funkční model:** Každý předpoklad je explicitní, chyba zastaví bezpečně a zůstane evidence.

**Proč:** Automatizace násobí dobré i špatné předpoklady.

## Build the model
`Precondition → Action → Check → Error path → Log → Exit status`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Inputs: Explicitní cesty, proměnné a preconditions.
- Action: Jeden kontrolovaný krok.
- Failure handling: Neskrývat chybu a nepokračovat naslepo.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.
- **Typická past:** Skript předpokládá prostředí a ignoruje chyby.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Skript předpokládá prostředí a ignoruje chyby.

