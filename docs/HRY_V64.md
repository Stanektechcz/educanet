# EDUCANET v64 · Hry, ligy a férová ekonomika (1 obrazovka)

## Pro žáka
- **Hry dávají jen XP**, dohromady nejvýš **60 XP denně**. Další den je strop zase volný. Body (obchod) a Kč (projekty) z her nejsou.
- **Moje liga** (Profil → Aréna): bronzová, stříbrná, zlatá. Liga se každé pololetí začíná znovu a nikdy se nepočítá do známek.
  Ostatní vidí jen tvou ligu, ne číslo.
- **Vyrovnaní soupeři** jsou jen rada, vyzvat můžeš kohokoli. Opakované souboje se stejným spolužákem se počítají míň.
- **Kdo se za 14 dní nejvíc posunul:** žebříček jen kladného zlepšení, jména jako iniciály.
- **Týmové hry:** máš roli (navigátor, operátor, kontrolor), která se mezi hrami střídá. Po hře odpovíš na 3 otázky (max 280 znaků),
  uvidí je jen učitel, bez jména.
- **Výzva týdne** na přehledu pod „Co dál“: kompetence na tento týden. Splníš ji hrou nebo soubojem; prázdniny sérii nepřeruší.
- **Odznaky** nové jen za zvládnutí a upevnění kompetence. Staré odznaky zůstávají.

## Pro učitele (cockpit → Hry → Ekonomika)
- **Měsíční report inflace XP:** medián, 90. percentil, podíl událostí zastavených stropem, nákupy v obchodě, změna proti minulému měsíci.
- **Absolutní žebříček** je výchozí vypnutý. Zapnete ho u konkrétní akce (CTF, robotí liga, týmová hra); u závodu volbou „žebříček“
  při vytvoření (výchozí je osobní rekord). Žák bez zapnutí vidí jen svou pozici a ligu.
- **Retrospektivy týmů** (bez jmen) vidí jen učitel s rozsahem třídy, asistent ne.
- **ELO a liga se nikdy nepoužívají pro známky ani exporty známek.**
- **Rollback ekonomiky:** `EDUCANET_ECONOMY_V64=0` vrátí chování v63 (bez stropu a deníku).
- **Metrika:** `EDUCANET_STORAGE_DIR=<kopie> php tools/v64_engagement_report.php` – podíl aktivních žáků ze spodního kvartilu (jen čtení).

## Pravidla (rozhodnutí školy)
ELO start 1000, K=32 do 10 her, pak 16 (v souboji se bere průměr K obou hráčů, aby se součet ELO zachoval), minimum 600; ligy
bronz <950, stříbro 950–1100, zlato >1100; reset každé pololetí. Anti-farm: opakovaná dvojice v pololetí ×0,5, čtvrtý a další zápas
téhož dne proti stejnému soupeři = 0. Výsledek hry je důkaz s nízkou vahou; hra sama nikdy nedá „upevněno“.
