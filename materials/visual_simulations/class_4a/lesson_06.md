# Lekce 6 · Canary release + backup/restore drill

**Třída:** class_4a  
**Lekce:** 6 / 28  
**Rodina:** release  
**Renderer:** system

## Konkrétní situace

Po deployi roste error rate. Potřebuješ rozhodnout, zda pokračovat, zastavit nebo rollbackovat. V této lekci je cílem: Provést řízené nasazení na malou část provozu a nacvičit rozhodnutí rollback vs. restore na základě měřitelných signálů.

## Princip

Bez rollback kritérií je rozhodnutí ovlivněné dojmem a tlakem.

## Manipulovatelné parametry

- **Traffic na nové verzi** (`traffic`): 0–100%; start 100, target 10. Canary omezuje blast radius.
- **Error rate** (`errors`): 0–25%; start 7, target 1. Sleduj rozdíl proti baseline.
- **Observation window** (`observe`): 1–60 min; start 3, target 15. Příliš krátké okno může skrýt regresi.
- **Rollback readiness** (`rollback`): 0–100%; start 30, target 95. Návrat musí být ověřitelný a rychlý.

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
