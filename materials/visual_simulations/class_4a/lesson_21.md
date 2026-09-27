# Lekce 21 · Container networking

**Třída:** class_4a  
**Lekce:** 21 / 28  
**Rodina:** containers  
**Renderer:** system

## Konkrétní situace

Container běží, ale služba není dostupná z hosta nebo z jiné služby. V této lekci je cílem: Diagnostikovat bind, publish port, container network a host firewall bez plošného restartu.

## Princip

Container přidává síťový namespace, ne magickou konektivitu.

## Manipulovatelné parametry

- **CPU limit** (`cpu`): 10–400%; start 60, target 160. Limit nesmí škrtit běžnou zátěž.
- **Memory headroom** (`memory`): 0–100%; start 12, target 35. Omez riziko OOM.
- **Healthcheck kvalita** (`health`): 0–100%; start 35, target 90. Healthcheck má měřit skutečnou schopnost sloužit.
- **Restart loop** (`restart`): 0–30/h; start 12, target 1. Restart policy nesmí maskovat příčinu.

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
