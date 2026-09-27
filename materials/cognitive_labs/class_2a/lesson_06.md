# Cognitive Lab · Lekce 6 · Portfolio case study + mikrointerakce

**Třída:** class_2a  
**Rodina:** motion  
**Renderer:** design  
**Primary topic:** portfolio-case-study

## Situace
Animace je efektní, ale nekomunikuje stav ani vztah mezi prvky. V této lekci je cílem: Připravit krátkou case study vlastního projektu a navrhnout jednoduchou mikrointerakci, která má jasnou funkci.

## Freeze & predict
Kdy má motion smysl?
- A. Vždy, když je stránka prázdná
- B. Když chceme skrýt pomalé načítání bez feedbacku
- C. Když vysvětluje změnu stavu, orientaci nebo příčinu/následek **← očekávaný směr**

## X-Ray vrstvy
- **Stav** — Co se změnilo.
- **Původ** — Odkud prvek přichází.
- **Pohyb** — Vysvětluje vztah mezi stavy.
- **Reduced motion** — Stejná informace musí fungovat i bez animace.

## Timeline scrubber
1. Urči změnu stavu
2. Definuj start/end
3. Zkrať pohyb na nutné minimum
4. Ověř reduced-motion
5. Otestuj pochopení bez efektu

## Cause → Effect controls
- Délka: 80–1200ms; start 900ms; functional target 260ms
- Vzdálenost: 0–160px; start 120px; functional target 24px

## Contrast case
**Typická past:** Dekorativní pohyb bez informační role.

**Funkční model:** Krátký přechod vysvětluje, odkud nový stav vznikl.

**Proč:** Motion má snižovat nejistotu, ne odvádět pozornost.

## Build the model
`State A → Trigger → Transition → State B → Reduced motion`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Stav: Co se změnilo.
- Původ: Odkud prvek přichází.
- Pohyb: Vysvětluje vztah mezi stavy.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Silná case study vysvětluje problém, proces a výsledek, ne jen galerii hotových obrázků.
- **Typická past:** Dekorativní pohyb bez informační role.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Dekorativní pohyb bez informační role.

