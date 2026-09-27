# Cognitive Lab · Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním

**Třída:** class_3a  
**Rodina:** automation  
**Renderer:** flow  
**Primary topic:** shell-cron

## Situace
Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby. V této lekci je cílem: Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.

## Freeze & predict
Co z něj udělá bezpečnou automatizaci?
- A. Více `sleep`
- B. Spouštět vše jako root
- C. Explicitní vstupy, error handling, idempotence, log a exit status **← očekávaný směr**

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
- **Jedna věta:** Bezpečný admin skript kontroluje preconditions, vrací exit status, loguje a je opakovatelný.
- **Typická past:** Skript předpokládá prostředí a ignoruje chyby.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Skript předpokládá prostředí a ignoruje chyby.

