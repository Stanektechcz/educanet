# Learning Studio · Lekce 1 · Základní dvouhodinový blok

**Třída:** class_3a  
**Lekce:** 1  
**Rodina:** dns  
**Primary topic:** ip-addressing

## Big idea
DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.

## Reprezentace
- **Realita** — Lekce 1 · Základní dvouhodinový blok: Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Kompletní blok na 2 × 45 minut: startovní test → cílený rozbor → realistická case study s topologií a simulovaným terminálem → praktické incidenty s nápovědami a kontrolou → exit ticket. Kdo dokončí celý blok dřív, odemkne dobrovolný Extra challenge na známku.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.
- **Kontrast** — Rozdíl, který rozhoduje: DNS nepřenáší web; pouze poskytuje informace potřebné k nalezení cíle.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Aplikace → DNS odpověď → Resolver → IP adresa → TCP/TLS → HTTP`
- Funkční model: `Aplikace → Resolver → DNS odpověď → IP adresa → TCP/TLS → HTTP`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** DNS je podobné kontaktům v telefonu: jméno musíš převést na číslo, než můžeš spojení zahájit.

**Limit přirovnání:** DNS není jeden centrální seznam; existují cache, delegace, více typů záznamů a různé resolvery.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `hostname`, `resolver`, `IP`, `dotaz`, `odpověď`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.
- **Past** — DNS → webová stránka
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Jak poznat síť, hosta a rozdíl mezi privátní a veřejnou adresou.
- Past: DNS → webová stránka
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

