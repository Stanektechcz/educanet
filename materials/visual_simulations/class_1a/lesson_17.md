# Lekce 17 · Mini UI kit: pravidla místo náhodných hodnot

**Třída:** class_1a  
**Lekce:** 17 / 28  
**Rodina:** components  
**Renderer:** design

## Konkrétní situace

Stejný typ prvku má na třech obrazovkách jiné spacingy, radius a stavy. V této lekci je cílem: Sestavit malý UI kit s barvami, typografií, spacingem a 3 komponentami a použít jej v jedné obrazovce.

## Princip

Design systém minimalizuje náhodná rozhodnutí a drift.

## Manipulovatelné parametry

- **Počet ad-hoc variant** (`variants`): 1–14; start 10, target 3. Méně jasně pojmenovaných variant je udržitelnější.
- **Využití tokenů** (`token_use`): 0–100%; start 28, target 90. Podíl hodnot řízených systémem.
- **Vizuální drift** (`drift`): 0–100%; start 62, target 8. Odchylka mezi stejnými komponentami.
- **Pokrytí stavů** (`states`): 0–100%; start 35, target 90. Default, hover, focus, error, disabled…

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
