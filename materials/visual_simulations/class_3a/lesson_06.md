# Lekce 6 · Monitoring + DHCP reservations

**Třída:** class_3a  
**Lekce:** 6 / 28  
**Rodina:** dhcp  
**Renderer:** system

## Konkrétní situace

Nový klient se připojí do sítě, ale získá adresu 169.254.x.x a nedosáhne na gateway. V této lekci je cílem: Přestat čekat na hlášení uživatele: měřit dostupnost služby a navrhnout stabilní DHCP adresaci pro známá zařízení.

## Princip

APIPA je symptom chybějící lease, ne důkaz chyby DNS.

## Manipulovatelné parametry

- **Volný DHCP pool** (`pool`): 0–100%; start 12, target 70. Dostatek volných adres.
- **Doba lease** (`lease`): 5–1440 min; start 30, target 480. Příliš krátká lease zvyšuje provoz a churn.
- **Ztráta broadcastu** (`loss`): 0–40%; start 16, target 1. DORA potřebuje doručení klíčových kroků.
- **Správnost relay** (`relay`): 0–100%; start 40, target 95. Mezi VLAN je nutný správný relay/helper.

## What-if scénáře

Každý scénář lze použít najednou nebo krokovat po jedné změně. Při krokování má student před každým krokem vyslovit predikci směru dopadu.

### Co když se podmínky zhorší?

Zvýší se tlak na slabé místo. Sleduj, která metrika se zlomí jako první.

### Co když opravíš jen polovinu?

Částečný zásah může zlepšit symptom, ale nemusí odstranit příčinu.

### Co když nastavíš funkční variantu?

Přibliž parametry cílovému stavu a porovnej přínos i kompromisy.

## A/B experiment

1. Ulož výchozí stav jako A.
2. Změň pouze jednu proměnnou a ulož jako B.
3. Porovnej funkčnost, čitelnost/evidence, odolnost a riziko.
4. V detailním diffu projdi hodnoty A, B a deltu každého parametru.
5. Vysvětli, který parametr způsobil rozdíl a co z výsledku **nelze** tvrdit.
6. U obhájené varianty zapiš krátkou evidence note ve formátu změna → pozorování → závěr.

## Teacher projection

- **Otázka:** Který parametr podle vás změní výsledek nejvíc — a proč?
- **Reveal:** Nejdřív měň jen jednu proměnnou. Až pak dovol třídě kombinovat zásahy.
- **Compare:** Zamkni variantu A, vytvoř B a nech třídu popsat nejen „která je lepší“, ale jaký důkaz to ukazuje.
- **Fáze:** Predikce → Diskuse → Reveal.
- **Spotlight:** zvýrazni jeden parametr a nech třídu předpovědět směr změny.
- **Klávesy:** P / D / R / F / ← / → / 1–3.

## Transfer

Použij stejný diagnostický princip na jiný host/službu/topologii, ale nezačínej stejným příkazem automaticky.
