# Learning Studio · Lekce 9 · DNS/DHCP II + service debugging

**Třída:** class_3a  
**Lekce:** 9  
**Rodina:** dns  
**Primary topic:** dns-dhcp-operations

## Big idea
DNS překládá jméno na adresu. Úspěch přes IP a selhání přes hostname izoluje problém před aplikační vrstvu.

## Reprezentace
- **Realita** — Lekce 9 · DNS/DHCP II + service debugging: Služba funguje přes IP adresu, ale ne přes hostname. V této lekci je cílem: Pochopit časové chování cache/lease a spojit DNS, TCP, TLS a HTTP do rychlé evidence-first diagnostiky služby.
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
- Jedna věta: Navazuje na DNS a DHCP základy: pochop dopad TTL, cache, lease time a změn adresace na reálný provoz.
- Past: DNS → webová stránka
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

