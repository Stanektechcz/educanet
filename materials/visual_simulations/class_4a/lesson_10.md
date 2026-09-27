# Lekce 10 · systemd do hloubky: dependencies, restart policy, failure

**Třída:** class_4a  
**Lekce:** 10 / 28  
**Rodina:** observability  
**Renderer:** system

## Konkrétní situace

Uživatelé hlásí pomalost, ale jeden log řádek sám nevysvětluje, zda je problém load, latency nebo errors. V této lekci je cílem: Analyzovat service unit, dependency chain a restart policy a bezpečně řešit opakovaný failure bez restart loopu.

## Princip

Observability pomáhá klást otázky nad systémem, ne jen sbírat grafy.

## Manipulovatelné parametry

- **p95 latence** (`latency`): 20–2000 ms; start 720, target 180. Tail latency zachytí problémy, které průměr schová.
- **Error rate** (`errors`): 0–25%; start 8, target 1. Poměr selhání.
- **Saturace** (`saturation`): 0–100%; start 92, target 62. Rezerva před vyčerpáním zdroje.
- **Kvalita signálu** (`signal`): 0–100%; start 38, target 90. Metrika musí být akční a vztahovat se k cíli.

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
