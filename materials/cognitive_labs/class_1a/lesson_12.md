# Cognitive Lab · Lekce 12 · Komponenty: tlačítka, karty a opakovatelná pravidla

**Třída:** class_1a  
**Rodina:** components  
**Renderer:** design  
**Primary topic:** components-i

## Situace
Stejný typ prvku má na třech obrazovkách jiné spacingy, radius a stavy. V této lekci je cílem: Pochopit komponentu jako opakovatelný vzor se stavy, ne jako jednorázový obrázek.

## Freeze & predict
Co opraví příčinu, ne jen screenshot?
- A. Definovat tokeny, komponentu a varianty **← očekávaný směr**
- B. Ruční doladění každé obrazovky
- C. Přidat víc CSS selektorů

## X-Ray vrstvy
- **Tokeny** — Hodnoty mají jméno a smysl.
- **Komponenta** — Jedna struktura místo kopií.
- **Varianty** — Explicitní stavy a velikosti.
- **Instance** — Konkrétní použití bez driftu.

## Timeline scrubber
1. Najdi opakování
2. Pojmenuj tokeny
3. Vytvoř komponentu
4. Přidej varianty
5. Stress-test obsahu

## Cause → Effect controls
- Spacing token: 4–40px; start 13px; functional target 16px
- Type token: 14–40px; start 27px; functional target 24px
- Radius: 0–32px; start 19px; functional target 12px

## Contrast case
**Typická past:** Každá instance používá vlastní náhodné hodnoty.

**Funkční model:** Instance sdílejí tokeny a varianty, změna pravidla se propíše konzistentně.

**Proč:** Design systém minimalizuje náhodná rozhodnutí a drift.

## Build the model
`Token → Component → Variant → Instance → QA`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Tokeny: Hodnoty mají jméno a smysl.
- Komponenta: Jedna struktura místo kopií.
- Varianty: Explicitní stavy a velikosti.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Komponenta je opakovatelný prvek s definovanými pravidly, obsahem a stavy.
- **Typická past:** Každá instance používá vlastní náhodné hodnoty.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Každá instance používá vlastní náhodné hodnoty.

