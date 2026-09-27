# Lekce 10 · Informační architektura + user flow

**Třída:** class_2a  
**Lekce:** 10 / 28  
**Rodina:** information  
**Renderer:** design

## Konkrétní situace

Obsah existuje, ale struktura odráží interní názvy týmu místo úkolů uživatele. V této lekci je cílem: Navrhnout strukturu malého webu podle úkolů uživatele a převést ji do jednoznačného user flow.

## Princip

IA snižuje nutnost hádat, kde informace jsou.

## Manipulovatelné parametry

- **Hloubka navigace** (`depth`): 1–8 úrovně; start 6, target 3. Přílišná hloubka zvyšuje hledání.
- **Volby na úrovni** (`choices`): 2–18; start 14, target 7. Příliš mnoho rovnocenných voleb zvyšuje nejistotu.
- **Jasnost názvů** (`labels`): 0–100%; start 48, target 90. Názvy musí odpovídat mentálnímu modelu uživatele.
- **Výzkumná evidence** (`evidence`): 0–100%; start 25, target 85. Struktura má vycházet z úkolů uživatele.

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
