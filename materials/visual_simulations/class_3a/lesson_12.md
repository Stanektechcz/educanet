# Lekce 12 · Procesy + systemd: služba není magie

**Třída:** class_3a  
**Lekce:** 12 / 28  
**Rodina:** systemd  
**Renderer:** system

## Konkrétní situace

Služba se po restartu okamžitě vrací do failed nebo startuje ve špatném pořadí. V této lekci je cílem: Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.

## Princip

systemd řídí lifecycle procesu, ne garantovaný aplikační výsledek.

## Manipulovatelné parametry

- **Restart rate** (`restart`): 0–60/h; start 18, target 1. Časté restarty jsou symptom, ne oprava.
- **Zdraví dependencies** (`deps`): 0–100%; start 55, target 95. Služba může být active, ale závislost ne.
- **Validita configu** (`config`): 0–100%; start 58, target 98. Validace před restartem snižuje riziko.
- **Kvalita log evidence** (`logs`): 0–100%; start 30, target 92. Journal má potvrdit skutečnou příčinu.

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
