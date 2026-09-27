# Cognitive Lab · Lekce 12 · Procesy + systemd: služba není magie

**Třída:** class_3a  
**Rodina:** systemd  
**Renderer:** flow  
**Primary topic:** processes-systemd

## Situace
Služba se po restartu okamžitě vrací do failed nebo startuje ve špatném pořadí. V této lekci je cílem: Najít běžící proces, stav služby a bezpečně rozlišit restart, reload a enable.

## Freeze & predict
Kde hledat první důkaz?
- A. Browser localStorage
- B. Unit stav, dependencies a journal pro konkrétní start **← očekávaný směr**
- C. DNS cache

## X-Ray vrstvy
- **Unit** — Deklarace služby a jejího procesu.
- **Dependencies** — Requires/After a pořadí.
- **Process** — Skutečný PID a exit status.
- **Journal** — Časová evidence startu a selhání.

## Timeline scrubber
1. systemd načte unit
2. Vyřeší dependencies
3. Spustí ExecStart
4. Proces běží/končí
5. Restart policy nebo failed

## Contrast case
**Typická past:** `systemctl start` = aplikace určitě funguje.

**Funkční model:** Unit state, proces, socket a aplikační health jsou různé evidence.

**Proč:** systemd řídí lifecycle procesu, ne garantovaný aplikační výsledek.

## Build the model
`Unit → Dependencies → ExecStart → Process/PID → Journal → Health check`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Unit: Deklarace služby a jejího procesu.
- Dependencies: Requires/After a pořadí.
- Process: Skutečný PID a exit status.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Proces je běžící program; systemd service unit popisuje, jak službu spouštět, sledovat a řídit.
- **Typická past:** `systemctl start` = aplikace určitě funguje.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: `systemctl start` = aplikace určitě funguje.

