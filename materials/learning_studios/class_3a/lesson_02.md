# Learning Studio · Lekce 2 · Navrhni a ověř malou kancelářskou síť

**Třída:** class_3a  
**Lekce:** 2  
**Rodina:** routing  
**Primary topic:** vlan-basics

## Big idea
Routing rozhoduje o dalším hopu podle cílové adresy a routovací tabulky; gateway není obecný „internetový server“.

## Reprezentace
- **Realita** — Lekce 2 · Navrhni a ověř malou kancelářskou síť: Lokální gateway odpovídá, ale cílová síť mimo subnet je nedostupná. V této lekci je cílem: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Routing rozhoduje o dalším hopu podle cílové adresy a routovací tabulky; gateway není obecný „internetový server“.
- **Kontrast** — Rozdíl, který rozhoduje: Routing je lokální rozhodnutí opakované na každém routeru.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Destination IP → Route → Prefix match → Next hop → Interface → Remote network`
- Funkční model: `Destination IP → Prefix match → Route → Next hop → Interface → Remote network`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako třídění zásilek podle směrovací tabulky: každý uzel vybírá další úsek cesty.

**Limit přirovnání:** Reálné routování může být dynamické, asymetrické a ovlivněné politikami nebo metrikou.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `cíl`, `route`, `gateway`, `prefix`, `hop`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Routing rozhoduje o dalším hopu podle cílové adresy a routovací tabulky; gateway není obecný „internetový server“.
- **Past** — Každý vzdálený cíl se posílá „na internet“.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.
- Past: Každý vzdálený cíl se posílá „na internet“.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

