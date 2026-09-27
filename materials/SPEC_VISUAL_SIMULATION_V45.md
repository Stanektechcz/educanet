# EDUCANET v45 · Visual Simulation Engine — specifikace

## Cíl

V45 převádí statickou nebo krokovanou vizualizaci z v43/v44 na **manipulovatelný model**. Student mění jednu nebo více vstupních podmínek a okamžitě vidí změnu stavu, metrik a rizika. Laboratoř není fyzikální simulátor reálné infrastruktury; je to didaktický model, který musí zachovat správnou kauzalitu a explicitně uvádět, co daná změna dokazuje a co ne.

## Povinný learning loop

Každá strukturovaná lekce 1.A–4.A má:

1. konkrétní lesson-specific situaci;
2. nejméně 4 manipulovatelné parametry s jednotkou, rozsahem, výchozí a cílovou hodnotou;
3. okamžitou deterministickou zpětnou vazbu;
4. 4 transparentní výstupní indikátory;
5. reset do výchozího problému;
6. nejméně 3 `what-if` scénáře;
7. A/B snapshoty a jejich numerické porovnání;
8. možnost znovu načíst A nebo B a pokračovat v experimentu;
9. `evidence checkpoint`, který uloží obhájenou variantu;
10. teacher projection mode se skrytým feedbackem;
11. reduced-motion a statický významový ekvivalent.

## Datový kontrakt `SimulationSpec`

```text
id
class_id
lesson_number
lesson_title
family
renderer: design | system
problem
princip
transfer
layers[]
timeline[]
params[]
  id
  label
  min/max/step
  value
  target
  unit
  hint
metric_labels
baseline
target
what_if[]
projection
```

## Hodnocení stavu

Vyhodnocení je deterministické a stejné na klientu i serveru. Každý parametr dostává skóre podle vzdálenosti od cílového rozsahu. Z něj se odvozují čtyři srozumitelné indikátory:

- **Funkčnost / Dostupnost** — jak blízko je celek funkčnímu stavu;
- **Čitelnost / Evidence** — zda změny poskytují dobře interpretovatelný signál;
- **Adaptivita / Odolnost** — zda celek nestojí na jednom slabém parametru;
- **Riziko** — inverzní pohled na odolnost.

Tyto indikátory nejsou známka ani skryté student-health score. Slouží pouze k experimentování a adaptaci výuky.

## Renderery

### Design renderer

Používá specializované vizuální scény:

- `page` — hierarchy, typography, responsive/layout;
- `form` — accessibility, error recovery, labels, keyboard focus;
- `components` — design tokens, variants, handoff;
- `ia` — information architecture a usability task path;
- `image` — crop, scale, focal point a export;
- `motion` — čas, vzdálenost a význam animace.

### System renderer

Používá vrstvený systémový tok, jehož uzly vycházejí přímo z `LabSpec` dané lekce. Je vhodný pro DNS, DHCP, routing, packet flow, systemd, permissions, SSH, firewall, service/proxy, observability, storage, backup, containers, release a incident response.

## What-if

Každá simulace má tři základní perturbace:

- **zhoršení podmínek** — zvýrazní nejslabší místo;
- **částečná oprava** — ukáže rozdíl mezi zmírněním symptomu a odstraněním příčiny;
- **funkční varianta** — přiblíží parametry cílovému stavu.

Po aplikaci scénáře může student každý parametr dále ručně měnit.

## A/B comparison

A/B porovnání je evidence-first:

1. student uloží stav A;
2. změní parametry;
3. uloží stav B;
4. systém ukáže delta funkčnosti a rizika;
5. student má vysvětlit **která konkrétní změna** rozdíl způsobila.

Pouhé „B je zelenější“ není cílem.

## Teacher projection

Teacher Lesson Mode obsahuje plnohodnotnou simulaci. Výchozí režim schová numerický feedback a učitel může:

- spustit fullscreen;
- `Freeze & ask`;
- měnit parametry živě;
- spouštět what-if scénáře;
- teprve po diskusi odkrýt feedback/metriky.

Tím se simulátor dá používat jako prediction-first aktivita celé třídy.

## Persistence

Studentovy uložené varianty jsou v `adaptive_v45_sim_snapshots.json.php`. Event log je v `adaptive_v45_sim_events.json.php`. Ukládají se pouze explicitní snapshoty, nikoliv každý pohyb slideru, aby se minimalizoval I/O a sběr zbytečných dat.

## Výkon

V45 nepoužívá WebGL ani externí JS framework. Runtime je HTML/SVG/CSS/vanilla JS a navazuje na stávající v43/v44 DOM. Simulace počítá lokálně; server se volá jen při explicitním uložení evidence.

## Definition of Done nové simulace

Nová lesson simulation je hotová pouze pokud:

- má konkrétní situaci a princip;
- parametry reprezentují skutečné příčiny/stavy, ne dekorativní efekty;
- každá proměnná má popsaný význam;
- změna parametru okamžitě mění vizuální stav nebo metriku;
- existuje nejméně jeden smysluplný trade-off;
- existují 3 what-if scénáře;
- A/B funguje bez reloadu;
- server validuje uložené hodnoty proti rozsahům;
- teacher projekce funguje bez studentského účtu;
- reduced-motion neztrácí obsah;
- simulace je použitelná na telefonu i projektoru.


## Experimentální vrstva v45.1

### Kauzální konzole

Každý živý stav navíc ukazuje tři didaktické informace: aktivně změněný parametr, nejslabší parametr podle vzdálenosti od cíle a typ vzniklého trade-offu. Závěr je odvozen ze stejného deterministického vyhodnocení jako hlavní metriky.

### Historie a undo/redo

Historie změn je lokální v aktuální stránce a nikdy se sama neposílá na server. Commit vzniká po dokončení změny slideru, aplikaci scénáře, nastavení cíle, resetu nebo načtení varianty. Student se může vracet mezi posledními stavy a hledat bod, kdy se model zlomil.

### Krokované what-if scénáře

Každý scénář má dva způsoby použití. `Aplikovat vše` zachová rychlý experiment. `Krokovat` rozloží cílový stav scénáře na změny jednotlivých parametrů. Před každým krokem UI ukáže další zásah a vyžaduje prediction-first práci: student má nejdřív odhadnout směr dopadu a až potom změnu aplikovat.

### Detailní A/B diff

A/B už neporovnává pouze souhrnnou funkčnost a riziko. Workspace vypisuje hodnoty A, B a deltu pro každý parametr a souhrnné metriky. Tím je viditelné, zda mezi variantami byla změněna jedna příčina, nebo několik podmínek současně.

### Evidence note

Při uložení obhájené varianty může student přidat krátké vysvětlení ve formátu „změna → pozorování → závěr“. Text je na serveru sanitizovaný a omezený na 600 znaků. Nejde o automaticky hodnocenou odpověď.

### Teacher projection phases

Projektor používá tři explicitní fáze:

1. **Predikce** — skryté metriky i diagnostický výsledek; třída formuluje očekávání.
2. **Diskuse** — model se mění, ale numerické metriky zůstávají skryté; třída popisuje pozorování.
3. **Reveal** — odkryjí se metriky, feedback a kauzální konzole; následuje vysvětlení evidence.

Teacher A/B snapshoty jsou pouze lokální a nezapisují se do studentského storage. Spotlight dovoluje zvýraznit jeden parametr. V projekčním režimu jsou dostupné klávesy `P`, `D`, `R`, `F`, šipky a `1–3` pro rychlé řízení aktivity.
