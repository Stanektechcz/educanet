# Learning Studio · Lekce 12 · Backup strategie: RPO/RTO a restore drill

**Třída:** class_4a  
**Lekce:** 12  
**Rodina:** backup  
**Primary topic:** backup-strategy

## Big idea
Backup není hotový, dokud nebyl ověřen restore. Cílem není soubor zálohy, ale obnovitelná služba a data.

## Reprezentace
- **Realita** — Lekce 12 · Backup strategie: RPO/RTO a restore drill: Backup job je zelený, ale nikdo neověřil, zda lze data v požadovaném čase obnovit. V této lekci je cílem: Navrhnout backup podle požadovaného RPO/RTO a prokázat obnovitelnost testovacím restore.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Backup není hotový, dokud nebyl ověřen restore. Cílem není soubor zálohy, ale obnovitelná služba a data.
- **Kontrast** — Rozdíl, který rozhoduje: Hodnota backupu se projeví až při úspěšné obnově.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Source → Independent storage → Backup → Loss event → Restore → Verify RPO/RTO`
- Funkční model: `Source → Backup → Independent storage → Loss event → Restore → Verify RPO/RTO`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to náhradní klíč: hodnotu má jen tehdy, když skutečně odemkne správné dveře ve správný okamžik.

**Limit přirovnání:** Obnova zahrnuje pořadí systémů, konzistenci, RPO/RTO a závislosti, nejen jeden soubor.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `backup`, `restore`, `RPO`, `RTO`, `ověření`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Backup není hotový, dokud nebyl ověřen restore. Cílem není soubor zálohy, ale obnovitelná služba a data.
- **Past** — Backup exists = recovery guaranteed.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Backup musí vycházet z požadované ztráty dat a času obnovy a musí být pravidelně testovaný restore.
- Past: Backup exists = recovery guaranteed.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

