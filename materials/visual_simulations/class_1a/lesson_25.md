# Lekce 25 · Component starter

**Třída:** class_1a  
**Lekce:** 25 / 28  
**Rodina:** accessibility  
**Renderer:** design

## Konkrétní situace

Rozhraní působí čistě, ale část uživatelů nedokáže přečíst stav nebo ovládat prvek klávesnicí. V této lekci je cílem: Navrhnout jednoduchou komponentu s default/focus/error stavem a jasným obsahem.

## Princip

Přístupnost je vlastnost fungování rozhraní, ne dodatečná kosmetika.

## Manipulovatelné parametry

- **Kontrast** (`contrast`): 1–10:1; start 2.4, target 5. Čitelnost textu a stavů.
- **Touch target** (`target`): 20–64px; start 28, target 44. Velikost interaktivní plochy.
- **Viditelnost focusu** (`focus`): 0–100%; start 20, target 85. Ovládání klávesnicí musí být čitelné.
- **Intenzita pohybu** (`motion`): 0–100%; start 80, target 28. Pohyb nemá nést jedinou informaci.

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
