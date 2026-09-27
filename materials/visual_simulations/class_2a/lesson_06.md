# Lekce 6 · Portfolio case study + mikrointerakce

**Třída:** class_2a  
**Lekce:** 6 / 28  
**Rodina:** motion  
**Renderer:** design

## Konkrétní situace

Animace je efektní, ale nekomunikuje stav ani vztah mezi prvky. V této lekci je cílem: Připravit krátkou case study vlastního projektu a navrhnout jednoduchou mikrointerakci, která má jasnou funkci.

## Princip

Motion má snižovat nejistotu, ne odvádět pozornost.

## Manipulovatelné parametry

- **Délka animace** (`duration`): 80–1800 ms; start 1200, target 280. Rychlost má podporovat pochopení změny stavu.
- **Vzdálenost pohybu** (`distance`): 0–220px; start 160, target 32. Velký pohyb zvyšuje vizuální hluk.
- **Současně animované vrstvy** (`layers`): 1–8; start 6, target 2. Jeden významový vztah v jeden moment.
- **Významovost pohybu** (`meaning`): 0–100%; start 35, target 90. Pohyb má vysvětlit změnu, ne pouze dekorovat.

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
