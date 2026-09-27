# Lekce 22 · Restore game day

**Třída:** class_4a  
**Lekce:** 22 / 28  
**Rodina:** backup  
**Renderer:** system

## Konkrétní situace

Backup job je zelený, ale nikdo neověřil, zda lze data v požadovaném čase obnovit. V této lekci je cílem: Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.

## Princip

Hodnota backupu se projeví až při úspěšné obnově.

## Manipulovatelné parametry

- **Stáří backupu** (`freshness`): 0–168 h; start 72, target 12. RPO určuje tolerovanou ztrátu dat.
- **Doba restore** (`restore`): 1–240 min; start 180, target 30. RTO určuje potřebnou rychlost obnovy.
- **Ověřená obnovitelnost** (`verified`): 0–100%; start 20, target 95. Backup bez testu restore není evidence obnovy.
- **Nezávislé kopie** (`copies`): 1–5; start 1, target 3. Oddělené kopie snižují společné selhání.

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
