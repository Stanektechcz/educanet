# Cognitive Lab · Lekce 11 · Form UX: validace, chyby a stavy

**Třída:** class_2a  
**Rodina:** forms  
**Renderer:** design  
**Primary topic:** form-states-ii

## Situace
Uživatel odešle formulář, dostane chybu, ale neví kde ani jak ji opravit. V této lekci je cílem: Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.

## Freeze & predict
Co musí recovery stav obsahovat?
- A. Konkrétní chybu u pole, zachovaný vstup a další krok **← očekávaný směr**
- B. Jen červený banner „chyba“
- C. Vymazání formuláře a nový pokus

## X-Ray vrstvy
- **Vstup** — Srozumitelný label a očekávaný formát.
- **Validace** — Kontrola ve správný čas.
- **Chyba** — Co je špatně a kde.
- **Recovery** — Jak to uživatel opraví bez ztráty práce.

## Timeline scrubber
1. Vyplň formulář
2. Odešli
3. Najdi chybu
4. Oprav jen problém
5. Potvrď úspěch

## Cause → Effect controls
- Mezera label/pole: 2–32px; start 5px; functional target 10px
- Text chyby: 11–24px; start 12px; functional target 16px
- Kontrast stavu: 1–10:1; start 2:1; functional target 5:1

## Contrast case
**Typická past:** Obecná chyba nahoře, pole bez vazby a ztracený vstup.

**Funkční model:** Chyba je u konkrétního pole, popisuje opravu a zachovává data.

**Proč:** Recovery je součást user flow, ne jen vizuální stav.

## Build the model
`Label → Input → Validate → Error → Recovery → Success`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Vstup: Srozumitelný label a očekávaný formát.
- Validace: Kontrola ve správný čas.
- Chyba: Co je špatně a kde.

## Transfer
Použij stejný princip na jiný formát, obsah nebo viewport, který v ukázce nebyl.

## Memory Snapshot
- **Jedna věta:** Formulář je malý stavový systém: default, focus, validace, loading, error, success a recovery.
- **Typická past:** Obecná chyba nahoře, pole bez vazby a ztracený vstup.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Obecná chyba nahoře, pole bez vazby a ztracený vstup.

