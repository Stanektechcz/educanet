# Cognitive Lab · Lekce 2 · Navrhni a ověř malou kancelářskou síť

**Třída:** class_3a  
**Rodina:** routing  
**Renderer:** flow  
**Primary topic:** vlan-basics

## Situace
Lokální gateway odpovídá, ale cílová síť mimo subnet je nedostupná. V této lekci je cílem: Navázat na troubleshooting a pochopit, co se děje mezi L2, VLAN, routingem, NATem a konkrétní službou. Na konci student umí vytvořit jednoduchou service matrix a diagnostikovat tok mezi dvěma VLAN.

## Freeze & predict
Který údaj rozhoduje o dalším hopu?
- A. DNS TTL
- B. Velikost MTU monitoru
- C. Routing table + prefix cíle **← očekávaný směr**

## X-Ray vrstvy
- **Host** — Porovná cílovou IP se svými prefixy.
- **Route lookup** — Vybere nejdelší odpovídající prefix.
- **Next hop** — Předá rámec gateway.
- **Remote network** — Další routery pokračují stejnou logikou.

## Timeline scrubber
1. Host má cílovou IP
2. Longest-prefix match
3. Vybere next hop
4. ARP/ND pro next hop
5. Packet pokračuje

## Contrast case
**Typická past:** Každý vzdálený cíl se posílá „na internet“.

**Funkční model:** Každý hop vybírá trasu podle routing table a prefixu.

**Proč:** Routing je lokální rozhodnutí opakované na každém routeru.

## Build the model
`Destination IP → Prefix match → Route → Next hop → Interface → Remote network`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Host: Porovná cílovou IP se svými prefixy.
- Route lookup: Vybere nejdelší odpovídající prefix.
- Next hop: Předá rámec gateway.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** VLAN umožňuje rozdělit zařízení do oddělených broadcast domén, i když používají stejnou fyzickou switch infrastrukturu.
- **Typická past:** Každý vzdálený cíl se posílá „na internet“.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Každý vzdálený cíl se posílá „na internet“.

