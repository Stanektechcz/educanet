# Cognitive Lab · Lekce 6 · Redesign: z chaosu na systém

**Třída:** class_1a  
**Rodina:** image  
**Renderer:** design  
**Primary topic:** template-critique

## Situace
Obraz vypadá správně v jednom formátu, ale crop nebo rozlišení selže v jiném použití. V této lekci je cílem: Analyzovat slabý návrh, formulovat problém a vytvořit nový plakát s jasně obhajitelnými rozhodnutími.

## Freeze & predict
Co máš definovat dřív než export?
- A. Focal point, cílový poměr stran a potřebné rozlišení **← očekávaný směr**
- B. Pouze maximální JPEG kvalitu
- C. Jednu univerzální velikost pro vše

## X-Ray vrstvy
- **Zdroj** — Rozlišení a charakter obrazu.
- **Focal point** — Co nesmí crop odstranit.
- **Crop** — Kompozice v cílovém poměru.
- **Export** — Formát a rozměry pro reálné použití.

## Timeline scrubber
1. Najdi focal point
2. Změň poměr stran
3. Zkontroluj crop
4. Ověř velikost výstupu
5. Exportuj pro konkrétní cíl

## Cause → Effect controls
- Šířka výřezu: 220–900px; start 760px; functional target 520px
- Focal point: 0–100%; start 25%; functional target 58%
- Export quality: 40–100%; start 100%; functional target 82%

## Contrast case
**Typická past:** Jeden crop a jeden export pro všechny formáty.

**Funkční model:** Focal point a art direction se přizpůsobují cílovému kontextu.

**Proč:** Obrazový asset je součást layout systému, ne izolovaný soubor.

## Build the model
`Zdroj → Focal point → Crop → Cílový slot → Export`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Zdroj: Rozlišení a charakter obrazu.
- Focal point: Co nesmí crop odstranit.
- Crop: Kompozice v cílovém poměru.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Kritika designu má pojmenovat problém, jeho dopad a návrh opravy.
- **Typická past:** Jeden crop a jeden export pro všechny formáty.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Jeden crop a jeden export pro všechny formáty.

