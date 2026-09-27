# Learning Studio · Lekce 23 · Hardening audit

**Třída:** class_4a  
**Lekce:** 23  
**Rodina:** ssh  
**Primary topic:** ssh-hardening

## Big idea
SSH spojení má oddělenou síťovou dostupnost, identitu serveru, autentizaci a autorizaci.

## Reprezentace
- **Realita** — Lekce 23 · Hardening audit: TCP/22 odpovídá, ale SSH končí `Permission denied (publickey)`. V této lekci je cílem: Najít zbytečný attack surface a zavést změnu bez ztráty recovery cesty.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: SSH spojení má oddělenou síťovou dostupnost, identitu serveru, autentizaci a autorizaci.
- **Kontrast** — Rozdíl, který rozhoduje: Text chyby lokalizuje fázi protokolu.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `TCP/22 → User → SSH handshake → Client key → authorized_keys → Session`
- Funkční model: `TCP/22 → SSH handshake → User → Client key → authorized_keys → Session`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to vstup do zabezpečené budovy: nejdřív se k ní musíš dostat, pak ověřit budovu, prokázat identitu a mít oprávnění.

**Limit přirovnání:** SSH podporuje různé metody autentizace, forwarding, bastiony a politiky, které analogie nezachytí.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `port`, `host key`, `autentizace`, `klíč`, `oprávnění`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — SSH spojení má oddělenou síťovou dostupnost, identitu serveru, autentizaci a autorizaci.
- **Past** — Permission denied = firewall.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Hardening snižuje attack surface, ale nesmí administrátora odříznout bez recovery cesty.
- Past: Permission denied = firewall.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

