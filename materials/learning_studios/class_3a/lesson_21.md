# Learning Studio · Lekce 21 · Journal forensic

**Třída:** class_3a  
**Lekce:** 21  
**Rodina:** logs  
**Primary topic:** journal-logs

## Big idea
Log je časově uspořádaná evidence událostí. Hodnota vzniká až propojením času, komponenty a symptomu.

## Reprezentace
- **Realita** — Lekce 21 · Journal forensic: V logu je mnoho chyb, ale potřebuješ najít první událost, která změnila stav systému. V této lekci je cílem: Najít relevantní incidentní okno a spojit log se změnou a symptomem.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Log je časově uspořádaná evidence událostí. Hodnota vzniká až propojením času, komponenty a symptomu.
- **Kontrast** — Rozdíl, který rozhoduje: Logy jsou evidence, ale příčina vzniká až jejich interpretací v kontextu.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Known good → Symptom → Change → Evidence window → Fix → Verification`
- Funkční model: `Known good → Change → Symptom → Evidence window → Fix → Verification`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to deník provozu: jednotlivá věta dává smysl až v kontextu toho, co se dělo před a po ní.

**Limit přirovnání:** Logy mohou být neúplné, zpožděné nebo samy o sobě zavádějící bez metrik a reprodukce.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `čas`, `událost`, `služba`, `chyba`, `kontext`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Log je časově uspořádaná evidence událostí. Hodnota vzniká až propojením času, komponenty a symptomu.
- **Past** — Jedna error message bez časového kontextu.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Logy mají nejvyšší hodnotu, když je filtruješ podle služby a incidentního času.
- Past: Jedna error message bez časového kontextu.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

