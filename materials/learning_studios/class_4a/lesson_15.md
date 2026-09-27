# Learning Studio · Lekce 15 · Automatizace + configuration consistency

**Třída:** class_4a  
**Lekce:** 15  
**Rodina:** automation  
**Primary topic:** automation-shell

## Big idea
Bezpečná automatizace musí být předvídatelná, idempotentní, pozorovatelná a bezpečně selhat.

## Reprezentace
- **Realita** — Lekce 15 · Automatizace + configuration consistency: Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby. V této lekci je cílem: Navrhnout idempotentní administrátorský postup, který umí zjistit current state, změnit jen potřebné a doložit výsledek.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Bezpečná automatizace musí být předvídatelná, idempotentní, pozorovatelná a bezpečně selhat.
- **Kontrast** — Rozdíl, který rozhoduje: Automatizace násobí dobré i špatné předpoklady.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Precondition → Check → Action → Error path → Log → Exit status`
- Funkční model: `Precondition → Action → Check → Error path → Log → Exit status`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to výrobní linka: každý krok musí mít jasný vstup, výstup a způsob zastavení při chybě.

**Limit přirovnání:** Software může pracovat s nedeterministickými API, concurrency a částečnými selháními.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `vstup`, `stav`, `chyba`, `idempotence`, `ověření`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Bezpečná automatizace musí být předvídatelná, idempotentní, pozorovatelná a bezpečně selhat.
- **Past** — Skript předpokládá prostředí a ignoruje chyby.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Automatizace má zjistit current state, změnit jen rozdíl a bezpečně selhat, pokud preconditions neplatí.
- Past: Skript předpokládá prostředí a ignoruje chyby.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

