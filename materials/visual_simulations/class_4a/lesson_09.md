# Lekce 9 · Performance + capacity engineering

**Třída:** class_4a  
**Lekce:** 9 / 28  
**Rodina:** performance  
**Renderer:** system

## Konkrétní situace

Latency roste pod zátěží. Potřebuješ určit limitující zdroj místo náhodného škálování všeho. V této lekci je cílem: Najít bottleneck pomocí latency/throughput/saturation a převést měření na realistický plán kapacity s headroomem.

## Princip

Capacity planning potřebuje model load, limitu a rezervy.

## Manipulovatelné parametry

- **Zátěž** (`load`): 0–150%; start 120, target 65. Sleduj chování i před saturací.
- **p95 latence** (`latency`): 20–3000 ms; start 1100, target 220. Latence obvykle roste nelineárně u limitu.
- **Headroom** (`headroom`): 0–100%; start 12, target 35. Rezerva pro špičky a selhání.
- **Queue depth** (`queue`): 0–100%; start 72, target 12. Fronta je signál, že poptávka převyšuje zpracování.

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
