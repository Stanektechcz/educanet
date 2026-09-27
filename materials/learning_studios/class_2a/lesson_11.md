# Learning Studio · Lekce 11 · Form UX: validace, chyby a stavy

**Třída:** class_2a  
**Lekce:** 11  
**Rodina:** forms  
**Primary topic:** form-states-ii

## Big idea
Formulář je dialog. Každé pole musí mít jasný účel, očekávání, stav a možnost zotavení z chyby.

## Reprezentace
- **Realita** — Lekce 11 · Form UX: validace, chyby a stavy: Uživatel odešle formulář, dostane chybu, ale neví kde ani jak ji opravit. V této lekci je cílem: Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Formulář je dialog. Každé pole musí mít jasný účel, očekávání, stav a možnost zotavení z chyby.
- **Kontrast** — Rozdíl, který rozhoduje: Recovery je součást user flow, ne jen vizuální stav.
- **Transfer** — Použij stejný princip jinde: Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Difference Lens
- Typická varianta: `Label → Validate → Input → Error → Recovery → Success`
- Funkční model: `Label → Input → Validate → Error → Recovery → Success`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako rozhovor s úředníkem: potřebuješ vědět, na co se ptá, co je špatně a jak chybu opravit.

**Limit přirovnání:** Formulář musí fungovat bez lidského doplňujícího vysvětlení, proto je semantika a mikrocopy kritická.

## Teach-back
Vysvětli vlastními slovy: Co se změnilo pro uživatele — a proč? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `label`, `chyba`, `stav`, `validace`, `obnova`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Formulář je dialog. Každé pole musí mít jasný účel, očekávání, stav a možnost zotavení z chyby.
- **Past** — Obecná chyba nahoře, pole bez vazby a ztracený vstup.
- **Transfer** — Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot seed
- Jedna věta: Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.
- Past: Obecná chyba nahoře, pole bez vazby a ztracený vstup.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

