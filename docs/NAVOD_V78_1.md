# Návod v78.1 · Nová navigace (učitel a žák)

Krátký průvodce změnami menu z vydání v78.1. Nic se nepřesunulo „pryč“ – všechny stránky cockpitu jsou na stejných adresách,
jen menu je přehlednější. Přehled pro správce: `CHANGELOG_V78_1.md`, technické detaily: `BUILD_MANIFEST_V78_1.md`.

## Učitel: 4 sekce místo 6
| Sekce | Co v ní najdete |
|---|---|
| **Dnes** | Co řešit dnes, Dnešní hodina (+ Komu dnes pomoct), Projektor, Tisk, Zástup, Reflexe, Týdenní souhrn, Středa: T-14, Přehled třídy, Hlášení |
| **Výuka** | *Plán, obsah a hodina:* cesty, úrovně, úlohy, opakování, plán vs. skutečnost, ŠVP, schvalování, hromadné schválení, knihovna materiálů, kalendář, kód hodiny, editor … · *Hodnocení:* testy, kompetence, projekty, hodnocení projektů, historie |
| **Žáci** | *Třída a žáci:* detail žáka, výsledky, analytika, intervence, komunikace, dotazník, přístupy (hesla), týmy, portfolia · *Hry a motivace:* Aréna, Robotí liga, týmové hry, CTF, incidenty, souboje, ekonomika, obchod |
| **Správa** | Přehled správy, učitelé, identita a nový rok, provoz, kvalita dat, audit, role, demo účty, přehled školy, reporty (podle role) |

### Na počítači (od 1024 px)
- Vlevo panel se sekcemi. Klepnutím na název sekce ji rozbalíte / sbalíte; sekce, ve které jste, je otevřená.
- „Přehled sekce …“ otevře rozcestník s kartami a souhrnem (dnešní výuka, třídy, stav správy).
- Vpravo nahoře: **⌘K** (Ctrl+K) rychlé hledání stránky, třídy nebo žáka, vzhled (Systém / Světlý / Tmavý), účet a Odhlásit.

### Na tabletu (768–1023 px)
- Tlačítko **Menu** vlevo nahoře otevře panel s celým menu. Nahoře v panelu je **vyhledávání** (stejné jako ⌘K), dole vzhled, účet a odhlášení.
- Panel zavřete křížkem, klávesou **Esc** nebo klepnutím mimo něj.

### Na telefonu (do 767 px)
- Dole je lišta **Dnes · Výuka · Žáci · Správa · Více**. Ikona sekce otevře rozcestník s kartami (2 klepnutí na libovolnou stránku).
- **Více** otevře celé menu s vyhledáváním, vzhledem a odhlášením.

### Drobečky a „Zpět“
- Nad obsahem je cesta **Cockpit › sekce › stránka**. Tlačítko **Zpět** vás vrátí na předchozí stránku cockpitu i se zvolenou třídou,
  filtrem a týdnem. Když jste přišli odjinud, vede na přehled sekce se stejnou třídou.

### Odznaky (čísla u položek)
| Odznak | Kde | Co znamená |
|---|---|---|
| T-14 | Středa: T-14, sekce Dnes | lekce, jejichž termín schválení (14 dní před výukou) už uplynul |
| neschválené | Schvalování lekcí, sekce Výuka | lekce s návrhem obsahu a výukou do 4 týdnů, které ještě nejsou schválené |
| Komu pomoct | Dnešní hodina | kolik žáků navrhuje karta „Komu dnes pomoct“ (z poslední otevřené Dnešní hodiny, platí 5 minut) |
| upozornění | Co řešit dnes | nepřečtená upozornění |

U sekce se ukáže nejdůležitější odznak (T-14, pak neschválené, Komu pomoct, upozornění). Čtečka obrazovky přečte celý význam
(např. „8 lekcí po termínu T-14“), na rozcestníku sekce je význam napsaný i pod kartou.

### Nejčastější úlohy – kolik klepnutí
Dnešní hodina, projektor, tisk, reflexe, Středa T-14 a hlášení: na PC **1 klepnutí** (sekce Dnes je otevřená). Schvalování, materiály,
hodnocení, výsledky třídy, detail žáka, přístupy, Aréna: **2 klepnutí** (rozbalit sekci, vybrat stránku); na telefonu ikona sekce → karta.
Úplná tabulka je v `BUILD_MANIFEST_V78_1.md`.

## Žák: spodní lišta Domů · Dnes · Úlohy · Profil
- Na telefonu (do 767 px) je dole lišta **Domů · Dnes · Úlohy · Profil**.
  - **Dnes** otevře Dnešní hodinu, když ji učitel spustil (u ikony je tečka a pro čtečku „právě běží“), jinak kalendář.
  - **Úlohy** = úlohy s variantami (a opakování před písemkou, když ho učitel zveřejnil).
- Materiály, výsledky, Linux Lab, projekty, obchod a další najdete v tlačítku **Menu** vpravo nahoře.
- Na tabletu a počítači zůstává horní panel se skupinami (Dnes, Učení, Hry a aréna, Projekty, Profil).
- Popisky lišty jsou česky, anglicky i ukrajinsky (podle zvoleného jazyka).

## Přístupnost
- Všechny položky menu a lišt mají cíl aspoň 44 × 44 px, viditelný fokus a fungují z klávesnice (Tab, Enter, Esc).
- Aktuální stránka je odlišená barvou i tvarem (podklad, proužek, tečka, tučné písmo) a je označená pro čtečky (`aria-current`).
- Světlý i tmavý režim; při omezení pohybu (prefers-reduced-motion) se panel nevysouvá animací.
