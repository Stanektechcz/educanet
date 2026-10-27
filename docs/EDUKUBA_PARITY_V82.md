# EDUCANET v82 – analýza eduKuba a implementační mapa

Analýza veřejného webu k 27. 10. 2026:
- https://virtual.edukuba.cz/
- https://edukuba.cz/

## Co je veřejně ověřené

**virtual.edukuba.cz** popisuje tři moduly: Počítačová dílna (okna, soubory, jednotky a oprávnění, referát a PDF, antivirus, Správce úloh, režim Porucha), Síťová dílna (router – napájení/WAN/LAN, Wi-Fi, heslo, měření download/upload/ping, cizí zařízení) a Linuxová dílna (ls/cd/cat, chmod/rwx, man, bezpečné příkazy). Stránka výslovně rozlišuje anonymní demo bez trvalých výsledků od studentského spuštění přes eduKuba, kde se eviduje postup, chyby a zpětná vazba.

**edukuba.cz** nabízí herní katalog s vyhledáváním, předmětovými a ročníkovými filtry, ukázkami různých výukových mechanik, přístupovým tokenem a deklarovaným ukládáním výsledků pro učitele.

**Limity ověření:** V této analýze se podařilo načíst obě úvodní stránky, nikoli všechny vnitřní interaktivní hry, autentizované učitelské stránky, jejich API ani zdrojové kódy. Nic zde netvrdí, že je známá jejich neveřejná implementace, algoritmy nebo databázové schéma. Nejde o převzetí cizího kódu nebo grafiky.

## EDUCANET: zachovat, nepřepisovat

Repozitář již obsahuje:
- Linux Lab a deterministický terminál (linux_v57*, linux_v58*), misijní a diagnostické úkoly;
- robotické, týmové, bezpečnostní, CTF a incidentní hry;
- řízené výukové cesty, otázky s variantami, praktické labs a skutečnou osobní identitu;
- učitelské hodnocení, Skill Mastery, Teacher Cockpit a analytics;
- přístupová oprávnění učitelů podle tříd a centrální CSRF a storage.

Duplikovat Linux terminál, uživatelské účty, klasifikaci a evidenci by vedlo k nejednotnému stavu.

## Stav v82 – první funkční vertikální část

| Funkce | v82 | Poznámka |
|---|---|---|
| Počítačová dílna s 7 navazujícími kroky | Ano | Souborové operace, práva, PDF, antivirus, oprava procesu |
| Síťová dílna s 8 kroky | Ano | Napájení, WAN, LAN, Wi-Fi, heslo, měření, neznámý klient |
| Linuxová dílna | Stávající EDUCANET | Přímý odkaz do existujícího Linux Labu |
| Viditelná simulace stavu | Ano | Desktop a topologie se aktualizují po správné akci |
| Ověření kroků serverem | Ano | Odpovědi nikdy neurčuje JS klienta |
| Nápověda, opakované pokusy | Ano | Bez trestání za chybu nebo nápovědu |
| Pokrok po studentech a třídách | Ano | Serverově, po explicitním POST |
| Přehled učitele | Ano | teacher.php?tab=analytics – statistika pro povolenou třídu |
| Oddělení známek/XP/Mastery | Ano | Vše výhradně formativní |
| Interaktivní drag-and-drop složky/okna | Ne | v82 je průvodce, nikoli plně simulovaný OS |
| Plná stateful virtuální souborová struktura | Ne | Scénářový model; navazuje další iterace |
| Kabely myší a živý síťový stav | Ne | Vizuální stav, volba akce, validace |
| Učitelské zadání scénáře/token a režim Porucha | Ne | Navržená další iterace |
| Synchronizace úkolů na konkrétní středu | Ne | Navržená další iterace |

## Další plánované kroky pro funkční paritu

1. **Windows-like desktop:** rozšiřit scénářový engine na skutečný stav virtuálního souborového systému (složky, adresáře, editace dokumentu, kopírování, přesuny, koš, práva, procesy a antivirová karanténa); klikání, klávesnice i drag-and-drop se stejným backendovým ověřením.
2. **Síťová topologie:** připojování kabelů na interaktivních portech, změny routeru a Wi-Fi, klienti a emulované hodnoty download/upload/ping (jasně označené jako simulace), průchod paketů, troubleshooting incidentů.
3. **Režim Porucha:** seznam scénářů s počátečním symptomem a různými možnými příčinami, diagnostika před opravou, recovery a závěrečná reflexe; režimy průvodce / praxe / samostatný úkol.
4. **Učitelská orchestrace:** přiřazení modulu konkrétní třídě, dvouhodinovému bloku, časovému oknu a obtížnosti; učitelská anonymní demo ukázka; sledování otevřených pokusů, export a případná ruční rubrika.
5. **Společný katalog:** jednotné vyhledávání aktivit a filtrování podle třídy, předmětu, kompetence a týdne. Nevyžadovat další přihlašování ani vedlejší token, pokud student již má školní účet.
6. **Hodnocení:** ukázky i domácí trénink mají mít neomezené pokusy; školní hodnocené testy se smí zpřístupnit jen při hodině a nesmí být dokončitelné doma. Přepojit pouze na existující zabezpečený assessment engine.
7. **Diferenciace pro SŠ:** 1.A/2.A webdesign, rozlišení rastr/vektor, typografie, CSS, přístupnost a publikace; 3.A/4.A Linux, síťové služby, DNS, firewall, incidenty, deployment a bezpečnost.

## Ochrana dat, bezpečnost a pedagogika

- Identita studenta vychází výhradně z již ověřené session / třídy EDUCANETu.
- Storage: atomic read-modify-write přes storage_update; údaje se zapisují až po explicitním POST, nikoli při GET.
- Nový klíč ve storage je SHA-256 ze studentKey + classId; jména nejsou přímo zapsaná, učitel je mapuje na roster ve své povolené třídě.
- Centrální verify_csrf() běží před novými student actions.
- Žádné vykonávání skutečných příkazů, přístup k reálným diskům nebo konfiguraci skutečného routeru.
- Přehled učitele je vložen do existujícího chráněného Teacher Cockpitu (analytics) a respektuje rozsah tříd.
- Výsledky v82 jsou výhradně formativní (bez automatické klasifikace, XP a Skill Mastery).
- Chyby, hinty a volby neobsahují hesla, volný text ani obsah reálných souborů.
- Zkontrolovat běžné politiky školy: účel zpracování a retenční dobu, GDPR informační povinnost, testy přístupnosti a dokumentaci učitele.

## Soubory a test

- virtualni_dilna_v82.php – scénáře, ověření kroků, ukládání, souhrny
- app/views/virtualni_dilna_v82.php – studentské vizuální prostředí
- app/actions/virtualni_dilna_v82.php – CSRF-chráněné rozhodnutí a nápověda
- assets/virtualni-dilna-v82.css – responzivní vzhled
- app/routes.php / nav_v61.php / app/views/_layout.php – lazy routing a navigace
- teacher.php – přehled v Analytics
- tools/v82_workshop_audit.php – čisté regresní testy reduceru

Lokální kontrola v kompletním checkoutu:
- php -l virtualni_dilna_v82.php
- php -l app/views/virtualni_dilna_v82.php
- php -l app/actions/virtualni_dilna_v82.php
- php -l app/routes.php
- php -l teacher.php
- php tools/v82_workshop_audit.php
- php tools/check_install.php

Nasazovat teprve po HTTP smoke testu s testovacím studentem v 1.A až 4.A a učitelským účtem s omezením třídy. Existující storage/ při aktualizaci nepřepisovat.
