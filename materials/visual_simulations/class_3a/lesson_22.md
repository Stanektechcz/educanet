# Lekce 22 · SSH keys in practice

**Třída:** class_3a  
**Lekce:** 22 / 28  
**Rodina:** ssh  
**Renderer:** system

## Konkrétní situace

TCP/22 odpovídá, ale SSH končí `Permission denied (publickey)`. V této lekci je cílem: Oddělit TCP dostupnost, výběr identity, oprávnění a serverovou autorizaci.

## Princip

Text chyby lokalizuje fázi protokolu.

## Manipulovatelné parametry

- **TCP/22 dostupnost** (`reach`): 0–100%; start 70, target 98. Nejdřív ověř transport.
- **Správnost klíče** (`key`): 0–100%; start 38, target 98. Autentizace musí odpovídat účtu a serveru.
- **Práva .ssh** (`perm`): 0–100%; start 44, target 95. Příliš otevřená práva mohou klíč zneplatnit.
- **Debug evidence** (`evidence`): 0–100%; start 35, target 90. Verbose výstup oddělí fáze spojení.

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
