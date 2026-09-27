# Learning Studio · Lekce 12 · Procesy + systemd: služba není magie

**Třída:** class_3a  
**Lekce:** 12  
**Rodina:** systemd  
**Primary topic:** processes-systemd

## Big idea
systemd spravuje životní cyklus služby, závislosti a stav; „proces existuje“ není totéž jako „služba je zdravá“.

## Reprezentace
- **Realita** — Lekce 12 · Procesy + systemd: služba není magie: Služba se po restartu okamžitě vrací do failed nebo startuje ve špatném pořadí. V této lekci je cílem: Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.
- **X-Ray** — Vrstvy systému: Rozděl problém na 4 vrstev a sleduj vždy jen jednu.
- **Princip** — Co si z toho odnést: systemd spravuje životní cyklus služby, závislosti a stav; „proces existuje“ není totéž jako „služba je zdravá“.
- **Kontrast** — Rozdíl, který rozhoduje: systemd řídí lifecycle procesu, ne garantovaný aplikační výsledek.
- **Transfer** — Použij stejný princip jinde: Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Difference Lens
- Typická varianta: `Unit → ExecStart → Dependencies → Process/PID → Journal → Health check`
- Funkční model: `Unit → Dependencies → ExecStart → Process/PID → Journal → Health check`
- Prompt: Posuň hranici a sleduj jedinou změnu. Který vztah v modelu se tím opraví?

## Analogy bridge
**Přirovnání:** Je to provozní řád budovy: říká, co se má spustit, v jakém pořadí a co dělat při problému.

**Limit přirovnání:** Aplikace může mít vlastní supervisor, container runtime nebo externí orchestraci.

## Teach-back
Vysvětli vlastními slovy: Která vrstva se změnila — a co tím opravdu dokazujeme? Nepopisuj jen postup; uveď vztah příčina → důkaz → závěr.

Očekávané pojmy/signály: `unit`, `service`, `status`, `dependency`, `restart`

## Model před / po
Student uloží vlastní pořadí před korekcí a po ní. Rozdíl slouží jako evidence změny mentálního modelu, ne jako známka.

## 90s Replay
- **Princip** — systemd spravuje životní cyklus služby, závislosti a stav; „proces existuje“ není totéž jako „služba je zdravá“.
- **Past** — `systemctl start` = aplikace určitě funguje.
- **Transfer** — Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot seed
- Jedna věta: Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.
- Past: `systemctl start` = aplikace určitě funguje.
- Retrieval cue: Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

