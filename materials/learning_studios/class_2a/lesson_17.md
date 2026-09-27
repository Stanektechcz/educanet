# Learning Studio · Lekce 17 · Design QA: porovnej implementaci s pravidly, ne s pixely

**Třída:** class_2a  
**Lekce:** 17  
**Rodina:** accessibility  
**Primary topic:** design-qa-handoff-ii

## Big idea
Přístupnost znamená, že význam i ovládání zůstávají dostupné různým lidem a způsobům interakce.

## Reprezentace
- **Realita** — Lekce 17 · Design QA: porovnej implementaci s pravidly, ne s pixely: Rozhraní působí čistě, ale část uživatelů nedokáže přečíst stav nebo ovládat prvek klávesnicí. V této lekci je cílem: Provést design QA realizace a rozlišit funkční odchylku, accessibility problém a přijatelnou implementační variaci.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Přístupnost znamená, že význam i ovládání zůstávají dostupné různým lidem a způsobům interakce.
- **Kontrast** — Rozdíl, který rozhoduje: Přístupnost je vlastnost fungování rozhraní, ne dodatečná kosmetika.
- **Transfer** — Použij stejný princip jinde: Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Difference Lens
- Typická varianta: `Úkol uživatele → Klávesnice → Semantika → Vizuální stav → Reduced motion`
- Funkční model: `Úkol uživatele → Semantika → Klávesnice → Vizuální stav → Reduced motion`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako dveře do budovy: nestačí, že vypadají dobře; musí se dát najít, otevřít a použít.

**Limit přirovnání:** Digitální bariéry nejsou jen fyzické; patří sem semantika, fokus, kontrast, pohyb i kognitivní zátěž.

## Teach-back
Vysvětli vlastními slovy: Co se změnilo pro uživatele — a proč? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `fokus`, `kontrast`, `label`, `klávesnice`, `význam`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Přístupnost znamená, že význam i ovládání zůstávají dostupné různým lidem a způsobům interakce.
- **Past** — Stav je sdělen jen barvou a focus není vidět.
- **Transfer** — Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot seed
- Jedna věta: Design QA porovnává implementaci s funkčními a systémovými pravidly a vytváří reprodukovatelné issues.
- Past: Stav je sdělen jen barvou a focus není vidět.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

