# Cognitive Lab · Lekce 11 · Uživatelé, skupiny a oprávnění

**Třída:** class_3a  
**Rodina:** permissions  
**Renderer:** flow  
**Primary topic:** users-permissions

## Situace
Proces běží, soubor existuje, ale služba jej nedokáže přečíst. V této lekci je cílem: Pochopit owner/group/other, rwx a navrhnout minimální oprávnění pro sdílený soubor a službu.

## Freeze & predict
Co musíš porovnat?
- A. DNS server
- B. TCP congestion window
- C. Identitu procesu s owner/group/mode/ACL cesty **← očekávaný směr**

## X-Ray vrstvy
- **Process identity** — UID/GID procesu.
- **Path traversal** — Execute permission na adresářích.
- **File permissions** — Owner/group/other nebo ACL.
- **Access decision** — Kernel povolí nebo odmítne operaci.

## Timeline scrubber
1. Proces má UID/GID
2. Projde adresářovou cestou
3. Kernel vyhodnotí oprávnění
4. Operace read/write
5. Allow / EACCES

## Contrast case
**Typická past:** Soubor existuje = proces ho může číst.

**Funkční model:** Přístup závisí na identitě procesu a oprávněních celé cesty.

**Proč:** Permissions jsou rozhodnutí kernelu pro konkrétní subjekt, objekt a operaci.

## Build the model
`Process UID/GID → Directory path → Owner/group → Mode/ACL → Access decision`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Process identity: UID/GID procesu.
- Path traversal: Execute permission na adresářích.
- File permissions: Owner/group/other nebo ACL.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Unix permissions rozdělují práva pro owner, group a others a umožňují aplikovat least privilege.
- **Typická past:** Soubor existuje = proces ho může číst.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Soubor existuje = proces ho může číst.

