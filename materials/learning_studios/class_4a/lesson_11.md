# Learning Studio · Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident

**Třída:** class_4a  
**Lekce:** 11  
**Rodina:** filesystem  
**Primary topic:** storage-filesystems

## Big idea
Kapacita filesystemu není jen počet volných gigabajtů; limitem mohou být inode, mount, práva nebo cesta zápisu.

## Reprezentace
- **Realita** — Lekce 11 · Storage: disk, filesystem, mount a „disk full“ incident: Aplikace hlásí „No space left“, ale `df -h` stále ukazuje volnou kapacitu. V této lekci je cílem: Rozlišit blokové zařízení, filesystem, mount point, kapacitu a inode problém a bezpečně diagnostikovat nedostatek místa.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Kapacita filesystemu není jen počet volných gigabajtů; limitem mohou být inode, mount, práva nebo cesta zápisu.
- **Kontrast** — Rozdíl, který rozhoduje: Symptom pochází z konkrétního write path, ne z abstraktního „disku“.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Write path → Permissions → Mount → Inode → Data blocks`
- Funkční model: `Write path → Mount → Permissions → Inode → Data blocks`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to sklad: může mít volnou podlahu, ale žádné volné přihrádky pro nové položky.

**Limit přirovnání:** Filesystémy mají další vlastnosti jako quota, reserved blocks, overlay vrstvy a síťová úložiště.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `disk`, `inode`, `mount`, `cesta`, `kapacita`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Kapacita filesystemu není jen počet volných gigabajtů; limitem mohou být inode, mount, práva nebo cesta zápisu.
- **Past** — „Disk full“ znamená vždy 100 % v `df -h`.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Provozní incident „disk full“ může být kapacita, inodes, špatný mount nebo nekontrolovaný růst dat.
- Past: „Disk full“ znamená vždy 100 % v `df -h`.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

