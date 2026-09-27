# Learning Studio · Lekce 8 · Infrastructure as Code + drift

**Třída:** class_4a  
**Lekce:** 8  
**Rodina:** iac  
**Primary topic:** infrastructure-as-code

## Big idea
Infrastructure as Code převádí očekávaný stav do verzované deklarace; drift je rozdíl mezi deklarací a realitou.

## Reprezentace
- **Realita** — Lekce 8 · Infrastructure as Code + drift: Produkce se liší od deklarované konfigurace, protože někdo provedl ruční změnu. V této lekci je cílem: Řídit produkční konfiguraci jako verzovaný desired state, kontrolovat diff před změnou a bezpečně řešit configuration drift.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Infrastructure as Code převádí očekávaný stav do verzované deklarace; drift je rozdíl mezi deklarací a realitou.
- **Kontrast** — Rozdíl, který rozhoduje: IaC přesouvá infrastrukturu z paměti lidí do kontrolovaného systému změn.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Desired config → Diff → Actual config → Review → Apply → Verify`
- Funkční model: `Desired config → Actual config → Diff → Review → Apply → Verify`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to recept a skutečně uvařené jídlo: pokud kuchař něco změní bokem, výsledek už receptu neodpovídá.

**Limit přirovnání:** Některé zdroje mají runtime stav a externí zásahy, které nelze bezpečně řídit čistou deklarací.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `desired state`, `drift`, `plan`, `apply`, `verze`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Infrastructure as Code převádí očekávaný stav do verzované deklarace; drift je rozdíl mezi deklarací a realitou.
- **Past** — Ruční změny bez zdroje pravdy.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Přeneste provozní změny z ručních kroků do deklarativního, verzovaného workflow s plánem změn a kontrolovatelným review.
- Past: Ruční změny bez zdroje pravdy.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

