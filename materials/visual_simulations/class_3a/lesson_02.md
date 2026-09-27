# Lekce 2 · Navrhni a ověř malou kancelářskou síť

**Třída:** class_3a  
**Lekce:** 2 / 28  
**Rodina:** routing  
**Renderer:** system

## Konkrétní situace

Lokální gateway odpovídá, ale cílová síť mimo subnet je nedostupná. V této lekci je cílem: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.

## Princip

Routing je lokální rozhodnutí opakované na každém routeru.

## Manipulovatelné parametry

- **Specificita route** (`prefix`): 8–32 /; start 16, target 24. Longest-prefix match rozhoduje o cestě.
- **Route metric** (`metric`): 1–500; start 220, target 40. Nižší metrika může být preferovaná cesta.
- **Dostupnost gateway** (`gateway`): 0–100%; start 55, target 98. Next hop musí být dosažitelný.
- **Traceroute evidence** (`evidence`): 0–100%; start 35, target 90. Důkaz má ukázat, kde se cesta mění.

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
