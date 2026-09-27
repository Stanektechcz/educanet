# Cognitive Lab · Lekce 14 · SSH/SFTP: bezpečný vzdálený přístup

**Třída:** class_3a  
**Rodina:** ssh  
**Renderer:** flow  
**Primary topic:** ssh-keys-ops

## Situace
TCP/22 odpovídá, ale SSH končí `Permission denied (publickey)`. V této lekci je cílem: Nastavit a ověřit SSH klíčové přihlášení, rozlišit autentizaci od síťové dostupnosti a použít SFTP bezpečně.

## Freeze & predict
Co tato evidence už dokazuje?
- A. DNS určitě nefunguje
- B. Firewall blokuje port 22
- C. Síť a sshd jsou dosažitelné; problém je v autentizační části **← očekávaný směr**

## X-Ray vrstvy
- **Network** — Dosažitelnost IP/portu.
- **SSH transport** — Handshake a server banner.
- **Authentication** — Uživatel, klíč, agent, authorized_keys.
- **Session** — Shell/SFTP až po úspěšné autentizaci.

## Timeline scrubber
1. TCP connect
2. SSH handshake
3. Server nabízí auth metody
4. Client nabídne key
5. Server accept/reject
6. Session

## Contrast case
**Typická past:** Permission denied = firewall.

**Funkční model:** Permission denied po handshake znamená, že cesta k sshd funguje a řešíme auth.

**Proč:** Text chyby lokalizuje fázi protokolu.

## Build the model
`TCP/22 → SSH handshake → User → Client key → authorized_keys → Session`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Network: Dosažitelnost IP/portu.
- SSH transport: Handshake a server banner.
- Authentication: Uživatel, klíč, agent, authorized_keys.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** SSH diagnostika odděluje síťovou dostupnost TCP/22 od autentizace uživatele a klíče.
- **Typická past:** Permission denied = firewall.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Permission denied = firewall.

