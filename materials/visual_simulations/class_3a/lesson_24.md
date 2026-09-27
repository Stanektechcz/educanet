# Lekce 24 · Stateful firewall lab

**Třída:** class_3a  
**Lekce:** 24 / 28  
**Rodina:** firewall  
**Renderer:** system

## Konkrétní situace

Služba poslouchá na serveru, lokálně funguje, ale klient z jiné sítě se nepřipojí. V této lekci je cílem: Navrhnout least-privilege pravidlo a potvrdit pozitivní i negativní test.

## Princip

Firewall je jen jedna část celé cesty.

## Manipulovatelné parametry

- **Rozsah pravidla** (`scope`): 0–100%; start 88, target 20. Menší rozsah = menší blast radius.
- **Legitimní průchod** (`allow`): 0–100%; start 55, target 98. Povolení správného provozu.
- **Blokace rizika** (`block`): 0–100%; start 48, target 92. Blokuj jen to, co má být blokováno.
- **Log evidence** (`logging`): 0–100%; start 30, target 85. Důkaz o hitu pravidla.

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
