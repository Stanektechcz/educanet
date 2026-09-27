# Lekce 9 · Interakce II + profesionální handoff

**Třída:** class_2a  
**Lekce:** 9 / 28  
**Rodina:** handoff  
**Renderer:** design

## Konkrétní situace

Návrh vypadá správně v design nástroji, ale implementátor nezná pravidla ani stavy. V této lekci je cílem: Navrhnout kompletní stavový model interakce, přidat smysluplný motion feedback a obhájit řešení pomocí evidence.

## Princip

Kvalitní handoff předává systém rozhodnutí, ne jen pixely.

## Manipulovatelné parametry

- **Úplnost specifikace** (`spec`): 0–100%; start 45, target 95. Stavy, tokeny, rozměry a chování musí být dohledatelné.
- **Připravenost assetů** (`assets`): 0–100%; start 55, target 95. Exporty, názvy a formáty mají být jednoznačné.
- **Pokrytí stavů** (`states`): 0–100%; start 32, target 92. Default, loading, empty, error, hover/focus a responsive.
- **Nejednoznačnost** (`ambiguity`): 0–100%; start 72, target 10. Čím méně domýšlení při implementaci, tím menší drift.

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
