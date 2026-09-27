# Cognitive Lab · Lekce 6 · Monitoring + DHCP reservations

**Třída:** class_3a  
**Rodina:** dhcp  
**Renderer:** flow  
**Primary topic:** monitoring-basics

## Situace
Nový klient se připojí do sítě, ale získá adresu 169.254.x.x a nedosáhne na gateway. V této lekci je cílem: Přestat čekat na hlášení uživatele: měřit dostupnost služby a navrhnout stabilní DHCP adresaci pro známá zařízení.

## Freeze & predict
Co máš ověřit jako první?
- A. Restart webserveru
- B. Zda proběhla DHCP výměna Discover/Offer/Request/ACK **← očekávaný směr**
- C. DNS cache

## X-Ray vrstvy
- **Discover** — Klient hledá DHCP server.
- **Offer** — Server nabízí parametry.
- **Request** — Klient žádá vybranou nabídku.
- **ACK** — Server lease potvrzuje.

## Timeline scrubber
1. DHCP Discover
2. DHCP Offer
3. DHCP Request
4. DHCP ACK
5. Klient nastaví IP/gateway/DNS

## Contrast case
**Typická past:** Klient má link, tedy musí mít i správnou IP konfiguraci.

**Funkční model:** Link vrstva může fungovat, zatímco DHCP konfigurace selže.

**Proč:** APIPA je symptom chybějící lease, ne důkaz chyby DNS.

## Build the model
`Client → Discover → Offer → Request → ACK → Lease`

## Reverse engineering
Vidíš pouze chybný výsledek. Která předchozí vrstva nebo rozhodnutí ho mohlo způsobit?
- Discover: Klient hledá DHCP server.
- Offer: Server nabízí parametry.
- Request: Klient žádá vybranou nabídku.

## Transfer
Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.

## Memory Snapshot
- **Jedna věta:** Ping serveru nestačí. Monitoring má co nejlépe napodobit reálnou uživatelskou cestu.
- **Typická past:** Klient má link, tedy musí mít i správnou IP konfiguraci.
- **Retrieval:** Bez nápovědy nakresli nebo popiš celý model v maximálně 60 sekundách.

## Teacher Live Explainer
- Zastav model těsně před výsledkem a nech studenty předpovědět další stav.
- Co právě víme, co ještě nevíme a jaký nejmenší test by to rozlišil?
- Misconception: Klient má link, tedy musí mít i správnou IP konfiguraci.

