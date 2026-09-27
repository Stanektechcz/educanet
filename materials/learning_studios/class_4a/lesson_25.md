# Learning Studio · Lekce 25 · Drift & automation

**Třída:** class_4a  
**Lekce:** 25  
**Rodina:** automation  
**Primary topic:** config-drift

## Big idea
Bezpečná automatizace musí být předvídatelná, idempotentní, pozorovatelná a bezpečně selhat.

## Reprezentace
- **Realita** — Lekce 25 · Drift & automation: Skript funguje při ručním spuštění, ale cron/automatizace někdy selže bez viditelné chyby. V této lekci je cílem: Porovnat desired/actual stav a navrhnout idempotentní automatizovanou nápravu.
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
- Jedna věta: Nauč se odhalit rozdíl mezi deklarovanou konfigurací a skutečným stavem prostředí a bezpečně rozhodnout, co je zdrojem pravdy.
- Past: Skript předpokládá prostředí a ignoruje chyby.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

