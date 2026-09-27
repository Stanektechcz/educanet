# Lekce 13 · Logy a journalctl: časová osa důkazů

**Třída:** class_3a  
**Lekce:** 13 / 28  
**Rodina:** logs  
**Renderer:** system

## Konkrétní situace

V logu je mnoho chyb, ale potřebuješ najít první událost, která změnila stav systému. V této lekci je cílem: Použít logy jako cílený zdroj evidence podle služby, času a severity místo čtení tisíců řádků.

## Princip

Logy jsou evidence, ale příčina vzniká až jejich interpretací v kontextu.

## Manipulovatelné parametry

- **Log noise** (`noise`): 0–100%; start 82, target 25. Příliš šumu skrývá signál.
- **Kontext logu** (`context`): 0–100%; start 35, target 90. Čas, service, request/correlation ID.
- **Retence** (`retention`): 1–90 dní; start 3, target 21. Musí pokrýt očekávané incidenty.
- **Přesnost filtru** (`query`): 0–100%; start 25, target 90. Filtruj podle hypotézy, ne náhodně.

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
