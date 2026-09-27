# EDUCAnet v44 · Learning Studio & Visual Reasoning — specifikace

## Cíl
Learning Studio převádí každou strukturovanou lekci z pasivního „viděl jsem vysvětlení“ na cyklus, ve kterém student musí vytvořit hypotézu, manipulovat s modelem, porovnat chybný a funkční vztah, sestavit vlastní mentální model, vysvětlit jej a použít princip v novém kontextu.

## Pokrytí
- 1.A DGD/Grafika + webdesign: 28/28 lekcí
- 2.A GRA/Grafika + UI/UX: 28/28 lekcí
- 3.A SOSaPS/OS + sítě: 28/28 lekcí
- 4.A SOSaPS/pokročilé systémy + reliability: 28/28 lekcí
- Celkem: 112/112 Learning Studios

## Povinný student flow
1. **Learning GPS** — ukazuje pouze další smysluplný krok.
2. **Freeze & Predict** — stávající v43 evidence.
3. **Manipulace** — student mění parametry nebo vrstvu modelu a explicitně potvrdí, že umí popsat změnu.
4. **Multi-representation deck** — Realita / X-Ray / Princip / Kontrast / Transfer.
5. **Difference Lens** — slider odhaluje rozdíl mezi typickou chybou a funkčním modelem.
6. **Build the Model** — v43 model builder.
7. **Model PŘED / PO** — student uloží dvě verze svého modelu.
8. **Teach-back** — krátké vysvětlení příčina → důkaz → závěr.
9. **90s Replay** — princip → past → transfer pro pozdější opakování.
10. **Memory Snapshot** — jedna věta + jedna past + retrieval cue.
11. **Transfer** — evidence v nové situaci z v41/v43.

## Adaptace
Learning GPS nepoužívá learning-style kategorii. Stav se odvozuje pouze z evidence: prediction, experiment, správnost modelu, teach-back a transfer. Žádná z těchto položek sama o sobě automaticky nevytváří známku.

## Vizuální standard
- jedna dominantní informace na obrazovce,
- progressive disclosure,
- žádná animace jako jediný nositel významu,
- reduced-motion ekvivalent,
- mobile/container responsive,
- žádný WebGL jako výchozí renderer,
- barevný stav vždy doplněný textem/strukturou,
- interakce musí fungovat i bez autoplay.

## Teacher flow
Teacher Lesson Mode obsahuje Visual Reasoning Board:
- bottleneck třídy,
- evidence Předpověď / Manipulace / Model / Vysvětlení / Transfer,
- pět postupně odkrývaných projekčních kroků,
- režim bez rušivého okolního UI.

## Definition of Done pro každou lekci
Každý StudioSpec musí mít:
- 5 reprezentací,
- lesson-specific big idea,
- analogy bridge + explicitní limit analogie,
- Difference Lens s chybným i funkčním modelem,
- alespoň 5 kroků modelu,
- alespoň 4 teach-back signály,
- 3 fáze 90s Replay,
- Memory Snapshot seed,
- napojení na v43 Cognitive Lab,
- napojení na v41 learning-event evidence,
- teacher Visual Reasoning Board.

## Výkon
v44 je doplňková progressive-enhancement vrstva. Aktuální budget:
- `learning-studio-v44.css` < 26 KB,
- `learning-studio-v44.js` < 18 KB,
- student state se v rámci requestu cachuje,
- teacher agregace načítá evidence dávkově místo opakovaného čtení souborů pro každého studenta.
