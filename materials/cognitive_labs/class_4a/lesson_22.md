# Cognitive Lab · Lekce 22 · Restore game day

**Třída:** class_4a  
**Rodina:** backup  
**Renderer:** timeline  
**Primary topic:** backup-restore

## Situace
Backup job je zelený, ale nikdo neověřil, zda lze data v požadovaném čase obnovit. V této lekci je cílem: Provést restore podle RPO/RTO a doložit integritu obnovené služby/dat.

## Freeze & predict
Co je skutečný důkaz funkční zálohy?
- A. Existující ZIP soubor
- B. Zelená ikona backup jobu
- C. Úspěšný restore test splňující RPO/RTO **← očekávaný směr**

## X-Ray vrstvy
- **Source** — Co a v jaké konzistenci zálohujeme.
- **Backup** — Kopie + metadata + retention.
- **Restore** — Reálný návrat do izolovaného cíle.
- **Verification** — Integrita, RPO/RTO a aplikační kontrola.

## Timeline scrubber
1. Create backup
2. Store separately
3. Simulate loss
4. Restore
5. Validate integrity/app
6. Measure RPO/RTO

## Contrast case
**Typická past:** Backup exists = recovery guaranteed.

**Funkční model:** Restore game day pravidelně ověřuje obnovitelnost a čas.

**Proč:** Hodnota backupu se projeví až při úspěšné obnově.

## Build the model
`Source → Backup → Independent storage → Loss event → Restore → Verify RPO/RTO`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Source: Co a v jaké konzistenci zálohujeme.
- Backup: Kopie + metadata + retention.
- Restore: Reálný návrat do izolovaného cíle.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Záloha je hodnotná až tehdy, když je ověřená, dostupná a umíš z ní bezpečně obnovit službu nebo data.
- **Typická past:** Backup exists = recovery guaranteed.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Backup exists = recovery guaranteed.

