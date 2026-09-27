# EDUCANET v43 · Cognitive Visualization & Visual Labs — specification

## 1. Cíl

Každá strukturovaná lekce musí studentovi umožnit nejen obsah přečíst, ale vytvořit a opravit mentální model. Povinný tok laboratoře je:

`konkrétní situace → předpověď → vizuální model → manipulace → důkaz → kontrastní případ → vlastní model → reverse engineering → transfer → memory snapshot`

Animace nikdy nesmí být dekorativní nebo jediným nositelem informace. Všechny stavy musí mít textový ekvivalent a fungovat při `prefers-reduced-motion: reduce`.

## 2. Pokrytí

- 1.A DGD / grafika + webdesign: 28/28 lekcí
- 2.A GRA / pokročilá grafika + UI/UX: 28/28 lekcí
- 3.A SOSaPS / OS + sítě: 28/28 lekcí
- 4.A SOSaPS / pokročilé systémy + reliability: 28/28 lekcí
- Celkem: 112 lesson-specific LabSpeců.

## 3. Architektura

Každá lekce generuje `LabSpec` přes `cv43_lab_spec(classId, lesson, module)`. Spec je konkrétní pro danou lekci, ale používá znovupoužitelné kognitivní rodiny a renderery.

### Povinná pole LabSpec

- `id`, `class_id`, `lesson_number`, `lesson_title`, `goal`
- `topic`, `family`, `renderer`
- `problem`
- `question`, `options`, `correct`
- `layers[]`
- `timeline[]`
- `controls[]` (pokud je manipulovatelný designový model)
- `compare.bad`, `compare.good`, `compare.why`
- `model.tokens`, `model.expected`
- `reverse.result`, `reverse.prompt`, `reverse.clues`
- `transfer`
- `memory.sentence`, `memory.trap`, `memory.retrieval`
- `vocabulary[]`
- `teacher.pause`, `teacher.ask`, `teacher.misconception`

## 4. Renderery

### `design`
Manipulovatelný vizuální canvas. Používá CSS custom properties a range controls. Vhodné pro typografii, spacing, responsive, image art direction, accessibility, design systems a motion.

### `flow`
SVG/system flow pro DNS, DHCP, routing, SSH, firewall, services, systemd, backup, containers a další procesy. X-Ray vrstvy zvýrazňují právě řešenou část.

### `map`
Informační architektura, usability, handoff a portfolio. Zobrazuje vztahy mezi uživatelským úkolem, obsahem, pravidly a výsledkem.

### `metrics`
Observability/performance. Více signálů se čte společně, ne jako izolované grafy.

### `timeline` / `incident`
Časově orientované procesy: logs, release, incident response. Hlavní význam má pořadí změna → symptom → evidence → rozhodnutí → validace.

## 5. Pedagogické mechanismy

### Freeze & Predict
Student musí před pokračováním označit jistotu a zvolit hypotézu. Výsledek se zapisuje jako learning event, ale nemění známku.

### X-Ray
Student může izolovat jednu vrstvu modelu. Cílem je snížit extraneous cognitive load a ukázat, která vrstva právě rozhoduje o výsledku.

### Timeline Scrubber
Proces lze zastavit v libovolné fázi. Každý krok zvýrazňuje odpovídající část vizuálního modelu.

### Cause → Effect
U designových laboratoří student mění jeden parametr. UI okamžitě ukazuje pozorovatelný dopad. Je dostupná výchozí varianta, vlastní varianta i funkční target.

### Contrast Case
Chybný mentální model je explicitně postaven proti funkčnímu modelu. Student vidí hranici konceptu, ne pouze správnou odpověď.

### Build the Model
Student skládá proces z tokenů bez hotového diagramu. Pokus se ukládá a vytváří learning event. Systém zvýrazní první rozpor v pořadí, neodhaluje řešení před pokusem.

### Reverse Engineering
Student dostane výsledek/symptom a jde zpět k možné příčině. Nápovědy se odkrývají po jedné.

### Vocabulary on demand
Pojem je vysvětlen krátce. Akce „Ukázat v modelu“ zvýrazní odpovídající vrstvu.

### Memory Snapshot
Lekce se komprimuje na jednu větu, jednu typickou past a jeden budoucí retrieval úkol.

### Transfer
Každá laboratoř končí aplikací stejného principu na nový kontext.

## 6. Evidence a adaptace

V43 zapisuje:

- prediction correct/incorrect + confidence,
- mental-model sequence + correct/incorrect,
- lesson/family/topic context,
- timestamp.

Události se propisují i do existujícího v41 Mastery Learning event streamu. V42 Adaptive Lesson Lane je proto může používat jako další evidence opakovaných chyb a úspěchu.

Tyto signály nejsou automatická známka ani penalizace.

## 7. Teacher Live Explainer

Každá lekce v Teacher Lesson Mode obsahuje:

- konkrétní situaci,
- stop-and-predict otázku,
- typickou misconception,
- projektorový vizuální model,
- X-Ray vrstvy,
- celou procesní timeline.

Učitel má odkrývat model postupně a před výsledkem zastavit výklad otázkou „co bude dál a jaký důkaz očekáváme?“.

## 8. Accessibility a performance

- všechny controls jsou nativní `button`, `input[type=range]`, `details/summary`,
- `prefers-reduced-motion` vypne transition/animation,
- explicitní Motion toggle se ukládá jen lokálně,
- View Transition API je pouze progressive enhancement,
- vizuální modely jsou SVG/CSS/HTML, bez WebGL závislosti,
- laboratoř funguje bez externího API,
- PWA cache obsahuje v43 CSS/JS,
- žádná animace není nutná k získání informace.

## 9. Data a soukromí

V43 ukládá pouze výukové evidence navázané na existující studentský klíč. Nezapisuje biometrická data, audio ani video studenta. Confidence je pedagogický signál a nemá být používán jako známka.

## 10. Definition of Done nové laboratoře

Nová lekce není považována za pokrytou, pokud nemá:

1. konkrétní problém,
2. prediction question se 3 odlišnými volbami,
3. nejméně 4 X-Ray vrstvy,
4. nejméně 5 timeline kroků,
5. contrast case,
6. vlastní model s nejméně 5 prvky,
7. reverse-engineering prompt,
8. transfer,
9. memory snapshot,
10. teacher Live Explainer prompt,
11. reduced-motion ekvivalent.
