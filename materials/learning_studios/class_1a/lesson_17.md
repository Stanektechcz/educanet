# Learning Studio · Lekce 17 · Mini UI kit: pravidla místo náhodných hodnot

**Třída:** class_1a  
**Lekce:** 17  
**Rodina:** components  
**Primary topic:** design-system-i

## Big idea
Komponenta je opakovatelná smlouva o struktuře, stavech a pravidlech; tokeny drží konzistenci napříč systémem.

## Reprezentace
- **Realita** — Lekce 17 · Mini UI kit: pravidla místo náhodných hodnot: Stejný typ prvku má na třech obrazovkách jiné spacingy, radius a stavy. V této lekci je cílem: Sestavit malý UI kit s barvami, typografií, spacingem a 3 komponentami a použít jej v jedné obrazovce.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Komponenta je opakovatelná smlouva o struktuře, stavech a pravidlech; tokeny drží konzistenci napříč systémem.
- **Kontrast** — Rozdíl, který rozhoduje: Design systém minimalizuje náhodná rozhodnutí a drift.
- **Transfer** — Použij stejný princip jinde: Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Difference Lens
- Typická varianta: `Token → Variant → Component → Instance → QA`
- Funkční model: `Token → Component → Variant → Instance → QA`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Design systém je stavebnice: díly mají společné rozměry a pravidla, takže se dají bezpečně kombinovat.

**Limit přirovnání:** Komponenty nejsou univerzální řešení všeho; špatná abstrakce může být stejně drahá jako duplicita.

## Teach-back
Vysvětli vlastními slovy: Co se změnilo pro uživatele — a proč? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `token`, `komponenta`, `varianta`, `stav`, `konzistence`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Komponenta je opakovatelná smlouva o struktuře, stavech a pravidlech; tokeny drží konzistenci napříč systémem.
- **Past** — Každá instance používá vlastní náhodné hodnoty.
- **Transfer** — Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot seed
- Jedna věta: Malý design systém je sada několika pravidel, tokenů a komponent, které drží produkt konzistentní.
- Past: Každá instance používá vlastní náhodné hodnoty.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

