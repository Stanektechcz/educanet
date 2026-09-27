# Lekce 14 · Containers: proces, image, volume a síť

**Třída:** class_4a  
**Lekce:** 14 / 28  
**Rodina:** service  
**Renderer:** system

## Konkrétní situace

Proces běží, ale uživatel stále dostává chybu nebo connection refused. V této lekci je cílem: Pochopit kontejner jako izolovaný proces s explicitním image, konfigurací, volume a network mappingem a diagnostikovat základní failure.

## Princip

Každá vrstva potřebuje vlastní důkaz.

## Manipulovatelné parametry

- **Listener zdraví** (`listener`): 0–100%; start 75, target 98. Proces musí skutečně naslouchat na správném socketu.
- **Upstream zdraví** (`upstream`): 0–100%; start 42, target 98. Proxy může být healthy, upstream ne.
- **Timeout** (`timeout`): 50–10000 ms; start 7000, target 1200. Timeout musí odlišit pomalost od nedostupnosti.
- **Layer evidence** (`evidence`): 0–100%; start 35, target 92. Testuj jednotlivé přechody zvlášť.

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
