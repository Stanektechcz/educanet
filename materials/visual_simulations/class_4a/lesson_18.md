# Lekce 18 · Capstone: produkční reliability drill

**Třída:** class_4a  
**Lekce:** 18 / 28  
**Rodina:** packet  
**Renderer:** system

## Konkrétní situace

Aplikace hlásí timeout. Potřebuješ určit, ve které vrstvě komunikace se cesta zastavila. V této lekci je cílem: Vyřešit propojený incident služby s Linuxem, sítí, proxy/TLS, observability, bezpečnou změnou a recovery a vytvořit auditovatelný postmortem.

## Princip

Stejný symptom může vzniknout v různých vrstvách.

## Manipulovatelné parametry

- **RTT** (`rtt`): 1–900 ms; start 320, target 70. Round-trip time mezi konci.
- **Packet loss** (`loss`): 0–30%; start 9, target 1. Ztráta ovlivní retransmise i throughput.
- **TCP window** (`window`): 8–1024 KB; start 64, target 512. Okno ovlivňuje využití linky při vyšším RTT.
- **Kvalita capture** (`capture`): 0–100%; start 25, target 90. Capture point musí odpovídat hypotéze.

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
