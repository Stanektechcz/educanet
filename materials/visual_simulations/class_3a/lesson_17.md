# Lekce 17 · Shell + cron: malá automatizace s logem a bezpečným selháním

**Třída:** class_3a  
**Lekce:** 17 / 28  
**Rodina:** automation  
**Renderer:** system

## Konkrétní situace

Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby. V této lekci je cílem: Napsat jednoduchý skript pro opakovatelný administrátorský úkol, logovat výsledek a naplánovat spuštění.

## Princip

Automatizace násobí dobré i špatné předpoklady.

## Manipulovatelné parametry

- **Blast radius** (`scope`): 1–100%; start 80, target 20. Automatizace má začít malým bezpečným rozsahem.
- **Idempotence** (`idempotence`): 0–100%; start 45, target 95. Opakované spuštění nesmí nekontrolovaně měnit stav.
- **Validace** (`validation`): 0–100%; start 35, target 95. Pre-check + post-check.
- **Rollback připravenost** (`rollback`): 0–100%; start 25, target 90. Změna bez návratu je vysoké riziko.

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

Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.
