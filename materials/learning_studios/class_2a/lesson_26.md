# Learning Studio · Lekce 26 · Design QA clinic

**Třída:** class_2a  
**Lekce:** 26  
**Rodina:** handoff  
**Primary topic:** design-handoff-qa

## Big idea
Handoff předává záměr a pravidla, ne pouze screenshot. Implementátor musí poznat strukturu, stavy a akceptační kritéria.

## Reprezentace
- **Realita** — Lekce 26 · Design QA clinic: Návrh vypadá správně v design nástroji, ale implementátor nezná pravidla ani stavy. V této lekci je cílem: Převést nalezený nesoulad na reprodukovatelné issue a ověřit opravu.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Handoff předává záměr a pravidla, ne pouze screenshot. Implementátor musí poznat strukturu, stavy a akceptační kritéria.
- **Kontrast** — Rozdíl, který rozhoduje: Kvalitní handoff předává systém rozhodnutí, ne jen pixely.
- **Transfer** — Použij stejný princip jinde: Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Difference Lens
- Typická varianta: `Intent → States → Rules → Responsive → QA`
- Funkční model: `Intent → Rules → States → Responsive → QA`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako technický výkres: hezká vizualizace nestačí, pokud chybí rozměry, materiál a tolerance.

**Limit přirovnání:** Digitální produkt se mění v čase; handoff je spíš společná smlouva než jednorázové předání.

## Teach-back
Vysvětli vlastními slovy: Co se změnilo pro uživatele — a proč? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `specifikace`, `stav`, `token`, `akceptace`, `implementace`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Handoff předává záměr a pravidla, ne pouze screenshot. Implementátor musí poznat strukturu, stavy a akceptační kritéria.
- **Past** — Handoff je obrázek bez pravidel.
- **Transfer** — Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot seed
- Jedna věta: Handoff předává pravidla a chování; QA ověřuje implementaci proti cíli, ne proti jednomu screenshotu.
- Past: Handoff je obrázek bez pravidel.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

