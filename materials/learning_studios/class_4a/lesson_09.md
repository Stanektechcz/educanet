# Learning Studio · Lekce 9 · Performance + capacity engineering

**Třída:** class_4a  
**Lekce:** 9  
**Rodina:** performance  
**Primary topic:** performance-engineering

## Big idea
Výkon je řetězec limitů. Optimalizace má začít měřením bottlenecku, ne náhodnou změnou konfigurace.

## Reprezentace
- **Realita** — Lekce 9 · Performance + capacity engineering: Latency roste pod zátěží. Potřebuješ určit limitující zdroj místo náhodného škálování všeho. V této lekci je cílem: Najít bottleneck pomocí latency/throughput/saturation a převést měření na realistický plán kapacity s headroomem.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Výkon je řetězec limitů. Optimalizace má začít měřením bottlenecku, ne náhodnou změnou konfigurace.
- **Kontrast** — Rozdíl, který rozhoduje: Capacity planning potřebuje model load, limitu a rezervy.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Load → Capacity limit → Resource → Saturation → Latency/errors → Headroom`
- Funkční model: `Load → Resource → Capacity limit → Saturation → Latency/errors → Headroom`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to doprava přes několik úzkých míst: rozšíření široké části silnice nepomůže, pokud kolona stojí jinde.

**Limit přirovnání:** Bottleneck se může měnit se zatížením a latence vzniká součtem i frontami v několika vrstvách.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `latence`, `throughput`, `saturace`, `bottleneck`, `měření`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Výkon je řetězec limitů. Optimalizace má začít měřením bottlenecku, ne náhodnou změnou konfigurace.
- **Past** — Vysoké CPU samo o sobě = bottleneck.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Rozliš hlavní výkonové signály a nauč se hledat skutečné úzké hrdlo místo slepého přidávání výkonu.
- Past: Vysoké CPU samo o sobě = bottleneck.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

