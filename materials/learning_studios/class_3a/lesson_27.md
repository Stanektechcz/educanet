# Learning Studio · Lekce 27 · Team incident lab

**Třída:** class_3a  
**Lekce:** 27  
**Rodina:** firewall  
**Primary topic:** stateful-firewall

## Big idea
Firewall rozhoduje podle pravidel o toku provozu. Otevřený port aplikace ještě neznamená, že je dosažitelný z každé zóny.

## Reprezentace
- **Realita** — Lekce 27 · Team incident lab: Služba poslouchá na serveru, lokálně funguje, ale klient z jiné sítě se nepřipojí. V této lekci je cílem: Rozdělit diagnostiku mezi role a vytvořit společnou evidence timeline.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: Firewall rozhoduje podle pravidel o toku provozu. Otevřený port aplikace ještě neznamená, že je dosažitelný z každé zóny.
- **Kontrast** — Rozdíl, který rozhoduje: Firewall je jen jedna část celé cesty.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Client → Firewall rule/state → Route → Listener → Return path`
- Funkční model: `Client → Route → Firewall rule/state → Listener → Return path`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to kontrolní bod na trase: služba může čekat na cíli, ale cesta k ní může být zakázána.

**Limit přirovnání:** Pravidla mohou být stavová, vícevrstvá a rozdělená mezi host, cloud, router i aplikaci.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `pravidlo`, `zdroj`, `cíl`, `port`, `stav`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — Firewall rozhoduje podle pravidel o toku provozu. Otevřený port aplikace ještě neznamená, že je dosažitelný z každé zóny.
- **Past** — Open port v konfiguraci = dostupná služba.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Stavový firewall rozlišuje nové a navazující spojení a umožňuje přesnější least-privilege pravidla.
- Past: Open port v konfiguraci = dostupná služba.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

