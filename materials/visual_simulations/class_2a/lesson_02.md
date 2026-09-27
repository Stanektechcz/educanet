# Lekce 2 · Z jednoho plakátu udělej malou kampaň

**Třída:** class_2a  
**Lekce:** 2 / 28  
**Rodina:** responsive  
**Renderer:** design

## Konkrétní situace

Desktop vypadá dobře, ale na úzkém viewportu obsah přetéká a CTA mizí mimo první pohled. V této lekci je cílem: Převést master plakát do konzistentního systému: master 1080×1350, square 1080×1080 a story 1080×1920. Student se učí adaptaci, photo treatment a obhajobu design systému.

## Princip

Responsive design není zmenšený desktop, ale sada pravidel pro různé podmínky.

## Manipulovatelné parametry

- **Viewport** (`viewport`): 280–1440px; start 1280, target 390. Sleduj bod, kde se obsah přestává vejít.
- **Počet sloupců** (`columns`): 1–4; start 2, target 1. Na úzkém prostoru je důležitější priorita než hustota.
- **Gap** (`gap`): 4–48px; start 28, target 16. Mezera mezi prvky musí odpovídat prostoru.
- **Min. šířka prvku** (`min_item`): 120–520px; start 340, target 220. Určuje, kdy má layout přejít do jiné struktury.

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
