# Cognitive Lab · Lekce 3 · UI hero sekce: grafika potkává web

**Třída:** class_2a  
**Rodina:** accessibility  
**Renderer:** design  
**Primary topic:** hierarchy

## Situace
Rozhraní působí čistě, ale část uživatelů nedokáže přečíst stav nebo ovládat prvek klávesnicí. V této lekci je cílem: Převést principy plakátu do responzivní hero sekce webu a pochopit hierarchii, CTA, grid a adaptaci.

## Freeze & predict
Co je správná diagnóza?
- A. Přidat výraznější animaci
- B. Zvětšit logo
- C. Ověřit kontrast, fokus, label a význam stavu **← očekávaný směr**

## X-Ray vrstvy
- **Vizuál** — Kontrast, velikost a stav.
- **Význam** — Label, role a srozumitelná chyba.
- **Ovládání** — Fokus a pořadí klávesnice.
- **Pohyb** — Informace nesmí záviset jen na animaci.

## Timeline scrubber
1. Najdi bariéru
2. Ověř klávesnici
3. Změř kontrast
4. Zkontroluj label/stav
5. Ověř bez barvy/pohybu

## Cause → Effect controls
- Kontrast: 1–10:1; start 2:1; functional target 5:1
- Text: 12–32px; start 14px; functional target 18px
- Touch target: 24–56px; start 28px; functional target 44px

## Contrast case
**Typická past:** Stav je sdělen jen barvou a focus není vidět.

**Funkční model:** Stav je textově i vizuálně srozumitelný a ovladatelný klávesnicí.

**Proč:** Přístupnost je vlastnost fungování rozhraní, ne dodatečná kosmetika.

## Build the model
`Úkol uživatele → Semantika → Klávesnice → Vizuální stav → Reduced motion`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Vizuál: Kontrast, velikost a stav.
- Význam: Label, role a srozumitelná chyba.
- Ovládání: Fokus a pořadí klávesnice.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Jak rozhodnout, co divák uvidí jako první, druhé a třetí.
- **Typická past:** Stav je sdělen jen barvou a focus není vidět.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Stav je sdělen jen barvou a focus není vidět.

