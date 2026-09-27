# EDUCANET v35 · Reality Demo Method

Reality Demo není dekorativní animace. Je to krátký model skutečné situace používaný před hodnocenou prací.

## Výukový loop

1. **Situace / symptom** – student dostane konkrétní problém bez prozrazení příčiny.
2. **Evidence boundary** – rozhraní oddělí, co už víme, od toho, co zatím pouze předpokládáme.
3. **Prediction-first rozhodnutí** – student zvolí nejvhodnější další krok.
4. **Vizuální důsledek** – model ukáže dopad volby a změní stav scény.
5. **Co to dokazuje** – explicitně se pojmenuje, jaký závěr z výsledku vyplývá.
6. **Co to nedokazuje** – systém zabrání příliš širokému závěru z jednoho testu.
7. **Teprve potom praxe / simulace / mastery evidence.**

Reality Demo samo o sobě nepřidává známku ani Mastery. V Knowledge Tour pouze potvrzuje vizuální přípravný krok po správném rozhodnutí.

## Technologie

- server-rendered HTML jako spolehlivý základ,
- CSS container queries pro responzivní mikro-scény,
- `content-visibility:auto` pro omezení práce prohlížeče mimo viewport,
- Web Animations API jako progressive enhancement,
- `IntersectionObserver` pro spuštění dema až ve chvíli, kdy je skutečně viditelné,
- View Transition API pro jemnou změnu scén lekce tam, kde je podporovaná,
- žádná externí animační knihovna ani CDN závislost,
- `prefers-reduced-motion` + ruční `Pohyb: omezený`,
- klávesnicové ovládání lesson tabs a textový ekvivalent každé animace.

## Design zásady

- jeden problém = jedna scéna,
- nejvýše tři rozhodnutí v prvním kroku,
- bez veřejného skóre a bez penalizace za chybnou predikci,
- zelená/červená nikdy nejsou jediný nositel informace,
- animace jsou krátké a mají začátek/konec; žádné nekonečné dekorativní pohyby,
- student může pohyb globálně omezit,
- detailní model je až druhá vrstva pod „Chci si princip rozebrat a ovládat“.

## Teacher Lesson Mode

Lesson Mode zobrazuje pro první klíčová témata lekce krátké Reality Prompts. Učitel nejdřív položí třídě otázku „Co byste udělali jako první a co by tím vzniklo za důkaz?“ a teprve potom rozbalí doporučenou cestu pro učitele.
