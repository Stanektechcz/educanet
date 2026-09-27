# Learning Studio · Lekce 11 · Uživatelé, skupiny a oprávnění

**Třída:** class_3a  
**Lekce:** 11  
**Rodina:** permissions  
**Primary topic:** users-permissions

## Big idea
Přístup vzniká kombinací identity procesu, vlastníka, skupiny, mode bitů a případných dalších politik.

## Reprezentace
- **Realita** — Lekce 11 · Uživatelé, skupiny a oprávnění: Proces běží, soubor existuje, ale služba jej nedokáže přečíst. V této lekci je cílem: Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Přístup vzniká kombinací identity procesu, vlastníka, skupiny, mode bitů a případných dalších politik.
- **Kontrast** — Rozdíl, který rozhoduje: Permissions jsou rozhodnutí kernelu pro konkrétní subjekt, objekt a operaci.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Process UID/GID → Owner/group → Directory path → Mode/ACL → Access decision`
- Funkční model: `Process UID/GID → Directory path → Owner/group → Mode/ACL → Access decision`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako sada klíčů a oprávnění v budově: nestačí vědět, komu místnost patří.

**Limit přirovnání:** ACL, capabilities, SELinux/AppArmor a kontejnery mohou standardní Unix práva dále měnit.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `vlastník`, `skupina`, `práva`, `proces`, `přístup`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Přístup vzniká kombinací identity procesu, vlastníka, skupiny, mode bitů a případných dalších politik.
- **Past** — Soubor existuje = proces ho může číst.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.
- Past: Soubor existuje = proces ho může číst.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

