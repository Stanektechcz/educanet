# Lekce 11 · Form UX: validace, chyby a stavy

**Třída:** class_2a  
**Lekce:** 11 / 28  
**Rodina:** forms  
**Renderer:** design

## Konkrétní situace

Uživatel odešle formulář, dostane chybu, ale neví kde ani jak ji opravit. V této lekci je cílem: Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.

## Princip

Recovery je součást user flow, ne jen vizuální stav.

## Manipulovatelné parametry

- **Počet polí** (`fields`): 2–24; start 16, target 7. Požaduj jen data nutná pro úkol.
- **Srozumitelnost labelů** (`label`): 0–100%; start 45, target 90. Label má říct co a proč.
- **Kvalita chyby** (`error`): 0–100%; start 25, target 92. Chyba musí říct, co opravit.
- **Možnost opravy** (`recovery`): 0–100%; start 30, target 90. Student/uživatel nesmí ztratit celý postup.

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
