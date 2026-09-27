# 2.A · Teacher guide

Předmět: **Grafika a webdesign · UI/UX**

## Doporučený rytmus 2 × 45 minut

- 0–15 min: aktivace / Knowledge Tour / model
- 15–45 min: první řízený výstup
- 45–75 min: aplikace a iterace
- 75–90 min: QA / test / reflexe

## Lekce 10–18

### Lekce 10 — Informační architektura + user flow
**Cíl:** Navrhnout strukturu malého webu podle úkolů uživatele a převést ji do jednoznačného user flow.

**Příprava učitele:**
- Testujte strukturu bez vizuálu, aby vzhled nemaskoval problém IA.
- Nepředepisuj jedinou správnou sitemapu.

**Časový plán:**
- 0–15 — Obsah jako struktura: Sepiš obsah do skupin podle uživatelského cíle.
- 15–30 — Sitemap: Navrhni 5–8 stránek/sekcí.
- 30–45 — User flow: Nakresli cestu od vstupu k cíli.
- 45–60 — Navigace: 
- 60–75 — Tree test nanečisto: Dej spolužákovi 3 úkoly bez ukázání designu.
- 75–90 — Iterace: Uprav jednu část IA podle testu.

**Evidence k uzavření lekce:**
- Content inventory.
- Sitemap.
- 1 user flow.
- 3 testovací úkoly.
- 1 iterace podle pozorování.

### Lekce 11 — Form UX: validace, chyby a stavy
**Cíl:** Navrhnout formulář jako stavový systém včetně loading, error, success a obnovy po chybě.

**Příprava učitele:**
- Hodnoť zotavení po chybě, ne jen krásný default.
- Připomeň, že disabled bez vysvětlení může být problém.

**Časový plán:**
- 0–15 — State inventory: Sepiš default/focus/filled/error/disabled/loading/success.
- 15–30 — Validace: Rozliš preventivní nápovědu a error po odeslání.
- 30–45 — Loading a double submit: Navrhni průběh po kliknutí Submit.
- 45–60 — Recovery: Po chybě zachovej správně vyplněná data.
- 60–75 — Chybová zpráva: 
- 75–90 — Prototype: Propoj success i error větev.

**Evidence k uzavření lekce:**
- State matrix formuláře.
- Error texty.
- Success stav.
- Klikací prototype nebo sekvence screenshotů.

### Lekce 12 — Auto Layout, varianty a responsive komponenty
**Cíl:** Postavit responzivní komponentu s variantami a minimem lokálních override.

**Příprava učitele:**
- Neomezuj výuku na Figma mechaniku; student má vysvětlit pravidlo layoutu.
- Vyžaduj názvy variant podle významu.

**Časový plán:**
- 0–15 — Auto Layout mental model: Rozliš hug/fill/fixed.
- 15–30 — Responsive button: Text změň na delší variantu.
- 30–45 — Card variants: Vytvoř compact/default variantu.
- 45–60 — Nested layout: Sestav kartu z menších komponent.
- 60–75 — Override: 
- 75–90 — Stress test: Použij dlouhý text, malý viewport a chybějící obrázek.

**Evidence k uzavření lekce:**
- Komponenta Button + Card.
- Varianty.
- Stress test se 3 extrémními vstupy.

### Lekce 13 — Accessibility audit webového návrhu
**Cíl:** Provést strukturovaný audit kontrastu, focusu, pořadí, targetů, textů a stavů a opravit prioritní problémy.

**Příprava učitele:**
- Učitel nemusí známkovat studenty za zapamatování čísel; důležitější je systematický audit a náprava.
- Používej reálný prototyp studentů.

**Časový plán:**
- 0–15 — Audit checklist: Zkontroluj kontrast, focus, texty, stavy a velikost ovládacích prvků.
- 15–30 — Keyboard flow: Seřaď očekávané pořadí focusu.
- 30–45 — Zoom a reflow: Ověř, co se stane při větším textu/užším viewportu.
- 45–60 — Form errors: Ověř labely a chybové stavy.
- 60–75 — Accessibility: 
- 75–90 — Prioritized fixes: Seřaď nálezy High/Medium/Low.

**Evidence k uzavření lekce:**
- Audit 8 bodů.
- Prioritizace nálezů.
- Before/after 2 oprav.

### Lekce 14 — Design → HTML/CSS: handoff, který jde implementovat
**Cíl:** Převést designové rozhodnutí do implementační specifikace: struktura, tokeny, komponenty a responsive pravidla.

**Příprava učitele:**
- Nemusí se psát plný kód. Cílem je spojit design a implementační myšlení.
- U technicky silnějších studentů lze přidat jednoduchou HTML/CSS realizaci.

**Časový plán:**
- 0–15 — Design vs DOM struktura: Rozděl obrazovku na header/main/sections/footer.
- 15–30 — Token handoff: Zapiš color/spacing/type tokeny.
- 30–45 — Responsive rules: Popiš, kdy se layout skládá pod sebe.
- 45–60 — States specification: Sepiš hover/focus/loading/error/disabled.
- 60–75 — Handoff: 
- 75–90 — Developer review: Nech spolužáka podle handoffu popsat implementaci.

**Evidence k uzavření lekce:**
- 1 stránka handoff specifikace.
- Tokeny.
- Responsive pravidla.
- State matrix.
- 2 opravy po review.

### Lekce 15 — CSS layout mindset: Flex, Grid a responsive pravidla
**Cíl:** Pochopit, jak se návrhové vztahy mapují do Flexbox/Grid modelu, a navrhovat proveditelné responsive rozložení.

**Příprava učitele:**
- Výklad může být bez rozsáhlého programování; důležitý je převod vztahů do layout modelu.
- Pokud umí HTML/CSS, dovol mini implementaci.

**Časový plán:**
- 0–15 — Flex vs Grid: Flex použij pro jednorozměrné uspořádání.
- 15–30 — Hero jako layout rule: Popiš desktop dvě kolony.
- 30–45 — Card grid: Navrhni grid, který se přirozeně mění 3→2→1.
- 45–60 — Responsive: 
- 60–75 — DevTools/inspection mindset: U existující stránky identifikuj container, gap, max-width a layout směr.
- 75–90 — Implementation notes: Ke 3 komponentám napiš layout model a breakpoint behavior.

**Evidence k uzavření lekce:**
- Flex/Grid rozhodnutí pro 3 části stránky.
- Breakpoint behavior.
- Card grid 3→2→1.

### Lekce 16 — Usability test: pozorování místo dojmologie
**Cíl:** Připravit krátký usability test, pozorovat chování bez navádění a prioritizovat zjištění podle dopadu.

**Příprava učitele:**
- Stačí 1–3 testující ve třídě pro nácvik metody; nejde o reprezentativní výzkum.
- Vyžaduj oddělení pozorování od interpretace.

**Časový plán:**
- 0–15 — Test plan: Definuj 3 realistické úkoly.
- 15–30 — Moderace: Nedoplňuj nápovědu příliš brzy.
- 30–45 — Evidence notes: Rozliš „kliknul třikrát zpět“ od „navigace je špatná“.
- 45–60 — Prioritizace: Ohodnoť četnost × dopad.
- 60–75 — Navádění: 
- 75–90 — Redesign: Proveď 1–3 změny.

**Evidence k uzavření lekce:**
- Test plan 3 úkolů.
- Pozorovací záznam.
- Seznam problémů + severity.
- Before/after redesign.

### Lekce 17 — Design QA: porovnej implementaci s pravidly, ne s pixely
**Cíl:** Provést design QA realizace a rozlišit funkční odchylku, accessibility problém a přijatelnou implementační variaci.

**Příprava učitele:**
- QA učí spolupráci Designer–Developer.
- Nepodporuj blame culture; issue popisuje stav produktu.

**Časový plán:**
- 0–15 — Design QA mindset: Kontroluj hierarchii, spacing, komponenty, states a responsive behavior.
- 15–30 — Compare: Porovnej design a implementaci ve 3 viewports.
- 30–45 — Severity: Critical = brání úkolu; High = zásadní funkční/a11y problém; Low = kosmetika.
- 45–60 — QA issue: Napiš steps, expected, actual a screenshot/reference.
- 60–75 — Priorita: 
- 75–90 — Verify fix: Po opravě zopakuj původní scénář.

**Evidence k uzavření lekce:**
- 3 viewport compare.
- 5 QA issues max.
- Severity + expected/actual.
- Verification poznámka.

### Lekce 18 — Capstone: produktová landing page + case study
**Cíl:** Spojit IA, responsive UI, komponenty, accessibility, testování a handoff do jednoho obhajitelného produktového návrhu.

**Příprava učitele:**
- Vhodné jako závěrečný projekt 2.A.
- U týmové varianty použij Project Workspace role Designer/Researcher/Developer/QA/Presenter.

**Časový plán:**
- 0–15 — Product brief: Definuj uživatele, problém, cíl stránky a success signal.
- 15–30 — IA + wireframe: Sitemap/sekvence.
- 30–45 — UI system: Tokeny, komponenty a stavy.
- 45–60 — Usability + accessibility: Proveď krátký test.
- 60–75 — Iterace + handoff: Zapracuj 2–4 podložené změny.
- 75–90 — Case study: 

**Evidence k uzavření lekce:**
- Brief.
- IA/user flow.
- Wireframes.
- Design system mini-spec.
- Responsive screens.
- Test evidence.
- Accessibility audit.
- Handoff.
- Case study.

## Diferenciace

- Student, který potřebuje podporu: použije připravený checklist, jednu nápovědu a menší rozsah výstupu.
- Standard: splní všechny povinné evidence a stručně obhájí jedno rozhodnutí.
- Rychlík: přidá druhou variantu, failure/edge case nebo hlubší validaci; ne další dekorativní práci.

## Hodnocení

Preferuj evidence a vysvětlení rozhodnutí před rychlostí nebo množstvím kliknutí. U týmových projektů použij Project Workspace role a individuální role evaluation.

<!-- V30_LESSONS:START -->
## v30 · Teacher Guide — lekce 19–28

### Adaptivní rutina pro každou lekci

1. Nejprve nech studenta **předpovědět**, co se stane.
2. Ukaž animovaný model pouze jako krátkou mentální mapu.
3. Pokud tápe, nepřeříkávej totéž: použij Help Ladder v pořadí **jednoduše → přirovnání → krok za krokem → konkrétní příklad → typická chyba → mini-pokus**.
4. Po nápovědě musí vždy následovat malý samostatný krok.
5. Hodnoť evidence a schopnost vysvětlit rozhodnutí, ne počet použitých nápověd.

### Lekce 19 — Research framing
**Cíl:** Převést neurčitý produktový problém do výzkumné otázky a jednoduchého testovacího plánu.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 20 — Card sorting lab
**Cíl:** Ověřit seskupení obsahu a navrhnout informační architekturu podle mentálních modelů uživatelů.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 21 — Responsive component stress test
**Cíl:** Otestovat komponentu na dlouhý obsah, malé viewporty a různé stavy.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 22 — Form recovery states
**Cíl:** Navrhnout validaci, chyby a recovery tak, aby uživatel nepřišel o práci.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 23 — Accessible interaction
**Cíl:** Ověřit focus, klávesnici, target size a alternativu k drag/hover gestům.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 24 — Design tokens to code
**Cíl:** Připravit tokeny a komponentovou specifikaci použitelnou při implementaci.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 25 — Usability round 2
**Cíl:** Provést krátký test bez navádění, zaznamenat evidence a upravit návrh.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 26 — Design QA clinic
**Cíl:** Převést nalezený nesoulad na reprodukovatelné issue a ověřit opravu.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 27 — Product sprint
**Cíl:** Propojit IA, komponenty, responsive pravidla, accessibility a testování v týmovém capstone.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?

### Lekce 28 — Case study & mastery
**Cíl:** Vytvořit stručnou case study se skutečným problémem, iterací, důkazem a výsledkem.

**90 minut:**
- **0–10 · Rychlý mentální model** — Animovaná ukázka + předpověď výsledku.
- **10–25 · Dva způsoby vysvětlení** — Jednoduché vysvětlení a konkrétní příklad.
- **25–45 · Guided practice** — Jedna změna, jeden test, jedna evidence.
- **45–70 · Samostatná aplikace** — Přenesení principu do vlastního případu.
- **70–82 · Peer / QA check** — Krátký test s konkrétním feedbackem.
- **82–90 · Exit ticket** — Vysvětli princip vlastními slovy.

**Poznámky pro učitele:**
- Začni animovaným modelem, ale po 2–3 minutách přejdi na aktivní práci studenta.
- Pokud student tápe, použij Help Ladder v pořadí jednoduše → přirovnání → příklad → mini pokus; neprozrazuj hotové řešení.

**Evidence k uzavření:**
- Předpověď: co očekávám před změnou / testem?
- Evidence: co přesně jsem pozoroval/a a co z toho plyne?
- Reflexe: co bych příště vysvětlil/a spolužákovi jinak?
<!-- V30_LESSONS:END -->
