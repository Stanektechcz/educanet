# Cognitive Lab · Lekce 28 · Case study & mastery

**Třída:** class_2a  
**Rodina:** handoff  
**Renderer:** map  
**Primary topic:** design-handoff-qa

## Situace
Návrh vypadá správně v design nástroji, ale implementátor nezná pravidla ani stavy. V této lekci je cílem: Vytvořit stručnou case study se skutečným problémem, iterací, důkazem a výsledkem.

## Freeze & predict
Co dělá handoff implementovatelným?
- A. Jedna poznámka „pixel perfect“
- B. Pravidla, tokeny, stavy, responsive chování a acceptance criteria **← očekávaný směr**
- C. Více screenshotů

## X-Ray vrstvy
- **Záměr** — Co komponenta řeší.
- **Pravidla** — Layout, tokeny a chování.
- **Stavy** — Loading, error, empty, hover, focus.
- **QA** — Jak ověřit, že implementace plní záměr.

## Timeline scrubber
1. Pojmenuj záměr
2. Sepiš pravidla
3. Doplň stavy
4. Doplň responsive
5. Definuj QA kritéria

## Contrast case
**Typická past:** Handoff je obrázek bez pravidel.

**Funkční model:** Implementátor zná záměr, tokeny, stavy i způsob validace.

**Proč:** Kvalitní handoff předává systém rozhodnutí, ne jen pixely.

## Build the model
`Intent → Rules → States → Responsive → QA`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Záměr: Co komponenta řeší.
- Pravidla: Layout, tokeny a chování.
- Stavy: Loading, error, empty, hover, focus.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Handoff předává pravidla a chování; QA ověřuje implementaci proti cíli, ne proti jednomu screenshotu.
- **Typická past:** Handoff je obrázek bez pravidel.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Handoff je obrázek bez pravidel.

