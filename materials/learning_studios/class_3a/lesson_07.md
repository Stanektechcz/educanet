# Learning Studio · Lekce 7 · Packet journey + Wireshark mindset

**Třída:** class_3a  
**Lekce:** 7  
**Rodina:** dns  
**Primary topic:** packet-analysis

## Big idea
DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.

## Reprezentace
- **Realita** — Lekce 7 · Packet journey + Wireshark mindset: Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Použít packet-level evidence k rozlišení lokální, DNS, TCP a aplikační závady bez bezhlavého spouštění všech nástrojů.
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
- Jedna věta: Nauč se číst jednoduchou packet journey a z minimální evidence určit, ve které vrstvě komunikace selhává.
- Past: DNS → webová stránka
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

