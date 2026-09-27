# Cognitive Lab · Lekce 16 · Landing page: wireframe od briefu k flow

**Třída:** class_1a  
**Rodina:** responsive  
**Renderer:** design  
**Primary topic:** web-layout-basics-i

## Situace
Desktop vypadá dobře, ale na úzkém viewportu obsah přetéká a CTA mizí mimo první pohled. V této lekci je cílem: Převést jednoduchý brief do struktury landing page a obhájit pořadí sekcí před vizuálním stylingem.

## Freeze & predict
Jaký je správný první krok?
- A. Jen zmenšit celý design na 70 %
- B. Skrýt problematický obsah
- C. Najít breakpoint podle chování obsahu a změnit layout **← očekávaný směr**

## X-Ray vrstvy
- **Obsah** — Co musí zůstat dostupné.
- **Kontejner** — Dostupná šířka komponenty.
- **Responsive pravidla** — Stack, wrap, min/max a breakpoint.
- **Art direction** — Crop a priorita obrazu podle prostoru.

## Timeline scrubber
1. Zmenši viewport
2. Najdi první bod selhání
3. Změň pravidlo, ne obsah
4. Ověř extrémy
5. Otestuj touch/čitelnost

## Cause → Effect controls
- Viewport: 280–1440px; start 1200px; functional target 390px
- Gap: 4–48px; start 28px; functional target 16px
- Fluidní nadpis: 22–72px; start 58px; functional target 38px

## Contrast case
**Typická past:** Fixní dvousloupec přetéká na mobilu.

**Funkční model:** Komponenta se skládá podle dostupného prostoru a zachovává prioritu obsahu.

**Proč:** Responsive design není zmenšený desktop, ale sada pravidel pro různé podmínky.

## Build the model
`Obsah → Container → Breakpoint → Layout rule → Art direction`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Obsah: Co musí zůstat dostupné.
- Kontejner: Dostupná šířka komponenty.
- Responsive pravidla: Stack, wrap, min/max a breakpoint.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Webový layout převádí vizuální hierarchii do sekcí, které mají jasný účel a pořadí.
- **Typická past:** Fixní dvousloupec přetéká na mobilu.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Fixní dvousloupec přetéká na mobilu.

