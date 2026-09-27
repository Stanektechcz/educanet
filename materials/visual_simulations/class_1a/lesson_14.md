# Lekce 14 · Obraz pro web: crop, rozlišení a export

**Třída:** class_1a  
**Lekce:** 14 / 28  
**Rodina:** image  
**Renderer:** design

## Konkrétní situace

Obraz vypadá správně v jednom formátu, ale crop nebo rozlišení selže v jiném použití. V této lekci je cílem: Připravit vizuály pro web tak, aby podporovaly obsah, měly správný crop a nebyly zbytečně datově těžké.

## Princip

Obrazový asset je součást layout systému, ne izolovaný soubor.

## Manipulovatelné parametry

- **Zvětšení rastru** (`scale`): 50–500%; start 320, target 100. Při zvětšení rastru roste viditelnost pixelů.
- **Síla cropu** (`crop`): 0–100%; start 72, target 35. Přílišný crop může odstranit význam.
- **Focal point** (`focus`): 0–100%; start 18, target 55. Pozice důležitého motivu v obrazu.
- **Export kvalita** (`quality`): 10–100%; start 45, target 82. Kompromis mezi kvalitou a velikostí.

## What-if scénáře

Každý scénář lze použít najednou nebo krokovat po jedné změně. Při krokování má student před každým krokem vyslovit predikci směru dopadu.

### Co když se podmínky zhorší?

Zvýší se tlak na slabé místo. Sleduj, která metrika se zlomí jako první.

### Co když opravíš jen polovinu?

Částečný zásah může zlepšit symptom, ale nemusí odstranit příčinu.

### Co když nastavíš funkční variantu?

Přibliž parametry cílovému stavu a porovnej přínos i kompromisy.

## A/B experiment

1. Ulož výchozí stav jako A.
2. Změň pouze jednu proměnnou a ulož jako B.
3. Porovnej funkčnost, čitelnost/evidence, odolnost a riziko.
4. V detailním diffu projdi hodnoty A, B a deltu každého parametru.
5. Vysvětli, který parametr způsobil rozdíl a co z výsledku **nelze** tvrdit.
6. U obhájené varianty zapiš krátkou evidence note ve formátu změna → pozorování → závěr.

## Teacher projection

- **Otázka:** Který parametr podle vás změní výsledek nejvíc — a proč?
- **Reveal:** Nejdřív měň jen jednu proměnnou. Až pak dovol třídě kombinovat zásahy.
- **Compare:** Zamkni variantu A, vytvoř B a nech třídu popsat nejen „která je lepší“, ale jaký důkaz to ukazuje.
- **Fáze:** Predikce → Diskuse → Reveal.
- **Spotlight:** zvýrazni jeden parametr a nech třídu předpovědět směr změny.
- **Klávesy:** P / D / R / F / ← / → / 1–3.

## Transfer

Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.
