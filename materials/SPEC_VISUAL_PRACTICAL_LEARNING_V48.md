# EDUCANET v48 · Visual & Practical Learning Engine

## Cíl

V48 převádí každou strukturovanou lekci do jednotného praktického cyklu:

**Předpověz → Porovnej → Diagnostikuj → Manipuluj → Sestav → Přenes.**

Engine je vrstva nad existujícími lesson-specific daty. Cognitive Lab v43 dodává problém, mentální model, vrstvy a kontrast; Visual Simulation v45 dodává deterministické parametry, metriky, A/B a what-if. V48 přidává didaktické pořadí, praktické mise a živou orchestraci učitele.

## Priority podle dopadu

| Priorita | Modul | Dopad | Proč je vysoko |
|---|---|---:|---|
| P0 | Prediction Before Reveal | velmi vysoký | Nutí studenta aktivovat mentální model před tím, než uvidí řešení. Zabraňuje pasivnímu „to je přece jasné“ po revealu. |
| P0 | Debugging Missions | velmi vysoký | Trénuje proces řešení a výběr prvního diagnostického kroku, ne memorování finální odpovědi. |
| P0 | Compare & Contrast Lab | velmi vysoký | Učí diskriminovat podobné situace a pojmenovat rozhodující rozdíl; cíleně pracuje s misconceptions. |
| P1 | Visual Concept Studio | vysoký | Zviditelňuje vrstvy a kauzalitu a spojuje abstraktní princip se změnou stavu. |
| P1 | Sandbox / What-if | vysoký | Umožňuje bezpečně experimentovat, měnit jednu proměnnou a ověřovat předpověď bez penalizace. |
| P1 | Draw / Build Mode | vysoký | Student aktivně rekonstruuje mentální model místo výběru hotové odpovědi. |
| P2 | Live Teacher Orchestration | vysoký pro výuku ve třídě | Umožňuje řídit společnou sekvenci a sledovat agregovaný postup bez veřejného zobrazování jmen. |

## V48LabSpec

Každá z 112 lekcí musí generovat jeden `V48LabSpec`:

- `id`, `class_id`, `lesson_number`, `lesson_title`, `topic`, `family`, `goal`;
- `prediction`: otázka, možnosti, correct index, reveal princip;
- `compare`: bad/good varianta, rozhodující princip, kontrastní otázka;
- `debug`: symptom, diagnostické možnosti, správný první krok, fault injection, timeline;
- `sandbox`: kompletní kompatibilní `v45_simulation_spec`;
- `build`: tokeny a očekávané pořadí mentálního modelu;
- `transfer`: nový kontext + kontrolní kritéria;
- `teacher.phases`: predict, discuss, compare, debug, sandbox, build, transfer;
- `assessment`: `grade_impact=false`, `xp_impact=false`, `mastery_impact=false`.

## Studentský UX kontrakt

### 1. Prediction Before Reveal
- student musí nejprve commitnout predikci;
- teprve po odpovědi se zobrazí vysvětlení principu;
- chyba není penalizovaná;
- výsledek se ukládá pouze jako formativní v48 evidence.

### 2. Compare & Contrast
- zobrazí se vedle sebe slepá ulička a funkční model;
- student vybírá **rozhodující rozdíl**, nikoli jen „které je správně“;
- feedback vysvětluje příčinu rozdílu.

### 3. Debugging Mission
- student dostane symptom a musí vybrat první diagnostický krok;
- po odpovědi dostane diagnostický algoritmus;
- fault injection odkazuje na stresový scénář v sandboxu a na nejslabší metriku;
- cílem je hypothesis-driven troubleshooting.

### 4. Visual Concept Studio + Sandbox
- používá stávající v45 deterministický engine;
- parametry jsou manipulovatelné;
- dostupné what-if scénáře, stepped scenario, A/B comparison a evidence note;
- doporučený režim je „měň jednu proměnnou a předpověz směr“;
- žádné známkování podle sliderů.

### 5. Draw / Build
- student skládá mentální model z lesson-specific tokenů;
- pořadí lze vracet a resetovat;
- kontrola je formativní;
- při chybě systém ukáže referenční tok, ale nezvýší známku ani Mastery.

### 6. Transfer
- student popíše, co se v nové situaci změnilo, co zůstává stejné a jak by výsledek ověřil;
- minimální odpověď zabraňuje prázdnému potvrzení;
- transfer je podpůrná evidence, ne klasifikace.

## Live Teacher Orchestration

Učitel může v Lesson Mode spustit jednu aktivní session pro třídu a lekci.

Fáze:
1. Predikce
2. Diskuse
3. Kontrast
4. Debug
5. Sandbox
6. Build
7. Transfer

Pravidla:
- student dostává pouze `active`, `phase`, `label`, `prompt`;
- teacher endpoint vrací agregované počty dokončení;
- na projektoru se nezobrazují jména;
- session má vlastní `session_id`, aby historické pokusy nekontaminovaly živé počty;
- změna fáze zachovává session, nové spuštění vytváří novou session;
- stop pouze deaktivuje session.

## Evidence a bezpečnost

V48 ukládá pouze `adaptive_v48_events.json.php` a `adaptive_v48_orchestration.json.php`, vytvořené až při explicitní akci.

Každý studentský event obsahuje:

```text
grade_impact=false
xp_impact=false
mastery_impact=false
```

Read-only render/spec/audit nesmí zapisovat do storage.

## Coverage

- 1.A: 28/28
- 2.A: 28/28
- 3.A: 28/28
- 4.A: 28/28
- celkem: **112/112**

Generování je založené na existujících lesson-specific datech, proto nejsou použity generické pevné scénáře odtržené od konkrétní lekce.

## Definition of Done

V48 je hotová, pokud:

1. 112/112 lekcí má validní V48LabSpec;
2. každá obsahuje všech 6 studentských režimů a 7 teacher phases;
3. studentský Visual Lab je dostupný z Lesson Kitu i strukturované lekce;
4. Teacher Lesson Mode obsahuje live orchestration;
5. student/teacher state endpointy fungují přes autentizované HTTP;
6. formativní flow nemění grades/XP/Mastery;
7. PWA shell obsahuje v48 CSS/JS;
8. starší audity zůstávají zelené;
9. PATCH neobsahuje `storage/`, `private/`, `uploads/`, `cache/`.
