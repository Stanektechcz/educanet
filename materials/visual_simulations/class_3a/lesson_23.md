# Lekce 23 · DNS evidence chain

**Třída:** class_3a  
**Lekce:** 23 / 28  
**Rodina:** dns  
**Renderer:** system

## Konkrétní situace

Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Postavit DNS diagnostiku od resolveru přes záznam po aplikační test.

## Princip

DNS nepřenáší web; pouze poskytuje informace potřebné k nalezení cíle.

## Manipulovatelné parametry

- **DNS latence** (`dns_latency`): 1–800 ms; start 380, target 45. Čas získání odpovědi resolveru.
- **Cache hit** (`cache_hit`): 0–100%; start 20, target 85. Vyšší hit rate snižuje opakované dotazy.
- **NXDOMAIN/SERVFAIL** (`failure`): 0–40%; start 18, target 1. Chybové odpovědi DNS vrstvy.
- **Izolace vrstvy** (`evidence`): 0–100%; start 30, target 90. Jak dobře test odděluje DNS od HTTP služby.

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
