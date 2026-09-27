# Lekce 19 · Filesystem incident

**Třída:** class_3a  
**Lekce:** 19 / 28  
**Rodina:** filesystem  
**Renderer:** system

## Konkrétní situace

Aplikace hlásí „No space left“, ale `df -h` stále ukazuje volnou kapacitu. V této lekci je cílem: Diagnostikovat permission/disk/mount problém bez změn naslepo.

## Princip

Symptom pochází z konkrétního write path, ne z abstraktního „disku“.

## Manipulovatelné parametry

- **Disk usage** (`disk`): 0–100%; start 72, target 60. Kapacita bloků.
- **Inode usage** (`inodes`): 0–100%; start 98, target 55. Volné GB nepomohou, když dojdou inody.
- **I/O wait** (`iowait`): 0–100%; start 38, target 8. Čekání procesů na storage.
- **Kvalita diagnostiky** (`evidence`): 0–100%; start 30, target 90. Rozliš bloky, inody a výkon.

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
