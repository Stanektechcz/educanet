# Learning Studio · Lekce 6 · Monitoring + DHCP reservations

**Třída:** class_3a  
**Lekce:** 6  
**Rodina:** dhcp  
**Primary topic:** monitoring-basics

## Big idea
DHCP vyjednává síťovou konfiguraci v několika stavech; klient před ACK ještě nemá potvrzenou konfiguraci.

## Reprezentace
- **Realita** — Lekce 6 · Monitoring + DHCP reservations: Nový klient se připojí do sítě, ale získá adresu 169.254.x.x a nedosáhne na gateway. V této lekci je cílem: Přestat čekat na hlášení uživatele: měřit dostupnost služby a navrhnout stabilní DHCP adresaci pro známá zařízení.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: DHCP vyjednává síťovou konfiguraci v několika stavech; klient před ACK ještě nemá potvrzenou konfiguraci.
- **Kontrast** — Rozdíl, který rozhoduje: APIPA je symptom chybějící lease, ne důkaz chyby DNS.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Client → Offer → Discover → Request → ACK → Lease`
- Funkční model: `Client → Discover → Offer → Request → ACK → Lease`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to jako nabídka pronájmu: nejdřív poptáš, dostaneš nabídku, požádáš o ni a teprve potom je potvrzena.

**Limit přirovnání:** Síť může mít relay, více serverů, lease renewal a statické rezervace.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `discover`, `offer`, `request`, `ack`, `lease`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — DHCP vyjednává síťovou konfiguraci v několika stavech; klient před ACK ještě nemá potvrzenou konfiguraci.
- **Past** — Klient má link, tedy musí mít i správnou IP konfiguraci.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Ping serveru nestačí. Monitoring má co nejlépe napodobit reálnou uživatelskou cestu.
- Past: Klient má link, tedy musí mít i správnou IP konfiguraci.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

