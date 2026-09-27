# Cognitive Lab · Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident

**Třída:** class_4a  
**Rodina:** filesystem  
**Renderer:** flow  
**Primary topic:** storage-filesystems

## Situace
Aplikace hlásí „No space left“, ale `df -h` stále ukazuje volnou kapacitu. V této lekci je cílem: Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.

## Freeze & predict
Co musíš ověřit vedle blokové kapacity?
- A. TLS certifikát
- B. Inody, mount point a místo zápisu **← očekávaný směr**
- C. DNS TTL

## X-Ray vrstvy
- **Path** — Kam aplikace opravdu zapisuje.
- **Mount** — Na jakém filesystemu cesta leží.
- **Blocks** — Datová kapacita filesystemu.
- **Inodes** — Počet souborových objektů může dojít dřív než GB.

## Timeline scrubber
1. Aplikace otevře cestu
2. Kernel najde mount
3. Filesystem alokuje inode
4. Alokuje datové bloky
5. Write uspěje/selže

## Contrast case
**Typická past:** „Disk full“ znamená vždy 100 % v `df -h`.

**Funkční model:** Selhat může kapacita, inody, permissions, mount nebo quota.

**Proč:** Symptom pochází z konkrétního write path, ne z abstraktního „disku“.

## Build the model
`Write path → Mount → Permissions → Inode → Data blocks`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Path: Kam aplikace opravdu zapisuje.
- Mount: Na jakém filesystemu cesta leží.
- Blocks: Datová kapacita filesystemu.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.
- **Typická past:** „Disk full“ znamená vždy 100 % v `df -h`.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: „Disk full“ znamená vždy 100 % v `df -h`.

