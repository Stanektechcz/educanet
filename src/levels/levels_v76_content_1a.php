<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · úrovně aktivit 1.A (grafika a webdesign), lekce 17–28 – NÁVRH (žákům až po schválení učitelem v záložce urovne75).
 * Navazuje na levels_v75_content_1a.php (L1–L16); hlavní aktivita odpovídá obsahu vlny 2 (lesson_content_v76_1a_*.php). Výukový obsah česky.
 */

return [
    17 => ['activity' => 'Tlačítko a karta z tokenů',
        'zaklad' => ['task' => 'Podle předvyplněné tabulky tokenů slož tlačítko a u každé hodnoty napiš jméno tokenu.', 'check' => 'Tlačítko nemá žádnou hodnotu mimo tabulku.'],
        'standard' => ['task' => 'Sestav vlastní sadu tokenů (barvy, mezery, písmo) a postav z ní tlačítko i kartu.', 'check' => 'Soused v návrhu nenajde hodnotu mimo sadu.'],
        'vyzva' => ['task' => 'Doplň tmavou variantu sady se stejnými jmény rolí a ověř kartu v obou režimech.', 'check' => 'Karta v obou režimech projde kontrolou kontrastu.']],
    18 => ['activity' => 'Responzivní landing page',
        'zaklad' => ['task' => 'Doplň texty do připravené kostry a nastav písmo 16 px s výškou řádku 1,5.', 'check' => 'Na 390 px se nic nepřekrývá.'],
        'standard' => ['task' => 'Postav celou stránku s jedním breakpointem a formulářem s labely.', 'check' => 'Projde kontrolním seznamem na 390 px i klávesnicí.'],
        'vyzva' => ['task' => 'Přidej druhý breakpoint a ověř, že pořadí Tab odpovídá pořadí na obrazovce.', 'check' => 'Pořadí čtení je stejné na mobilu i počítači.']],
    19 => ['activity' => 'Audit návrhu spolužáka',
        'zaklad' => ['task' => 'Pomocí kartiček se začátky vět napiš dvě poznámky k návrhu spolužáka.', 'check' => 'Každá poznámka má pozorování a návrh.'],
        'standard' => ['task' => 'Napiš tři poznámky ve tvaru pozorování → dopad → návrh a seřaď je podle dopadu.', 'check' => 'Autor ví, co opravit jako první.'],
        'vyzva' => ['task' => 'Ke každé poznámce navrhni, jak ověřit, že oprava pomohla.', 'check' => 'Každá poznámka má konkrétní test.']],
    20 => ['activity' => 'Drátěný model od obsahu',
        'zaklad' => ['task' => 'Z nabídnutých otázek návštěvníka vyber čtyři a seřaď je do sekcí modelu.', 'check' => 'Každá sekce odpovídá jedné otázce.'],
        'standard' => ['task' => 'Napiš hlavní sdělení do 12 slov a nakresli model se skutečnými texty.', 'check' => 'Spolužák po 5 sekundách řekne, o čem stránka je.'],
        'vyzva' => ['task' => 'Napiš dvě varianty sdělení a testem pěti sekund rozhodni, která vyhrála.', 'check' => 'Rozhodnutí je podložené odpověďmi dvou testujících.']],
    21 => ['activity' => 'Úvodní blok pro mobil i počítač',
        'zaklad' => ['task' => 'Do připraveného hero doplň nadpis a tlačítko tak, aby byly na 390 px vidět bez posouvání.', 'check' => 'Tlačítko je nad okrajem obrazovky.'],
        'standard' => ['task' => 'Připrav dva výřezy obrázku a postav hero pro mobil i počítač s kontrastem aspoň 4,5 : 1.', 'check' => 'Ohnisko zůstává vidět v obou výřezech.'],
        'vyzva' => ['task' => 'Použij prvek picture, aby prohlížeč sám vybral výřez podle šířky.', 'check' => 'V náhledu se výřez mění při zúžení okna.']],
    22 => ['activity' => 'Texty tlačítek a hlášek',
        'zaklad' => ['task' => 'Přepiš tři obecná tlačítka podle seznamu sloves.', 'check' => 'Každé tlačítko začíná slovesem.'],
        'standard' => ['task' => 'Přepiš pět tlačítek, napiš tři chybové hlášky a potvrzení odeslání.', 'check' => 'Soused bez vysvětlení řekne, co se stane.'],
        'vyzva' => ['task' => 'Navrhni stav tlačítka při odesílání a popiš, co uživatel uvidí.', 'check' => 'Stav říká, že se něco děje, a nedá se kliknout dvakrát.']],
    23 => ['activity' => 'Oprava kontrastu a písma',
        'zaklad' => ['task' => 'Změř kontrast tří připravených dvojic barev a oprav tu, která neprojde.', 'check' => 'Opravená dvojice má aspoň 4,5 : 1.'],
        'standard' => ['task' => 'Změř a oprav všechny dvojice na své stránce a doplň chybový stav pole bez spoléhání na barvu.', 'check' => 'Tabulka před → po a chyba nese text i ikonu.'],
        'vyzva' => ['task' => 'Ověř stránku v tmavém režimu a v odstínech šedi.', 'check' => 'Hierarchie drží i bez barev.']],
    24 => ['activity' => 'Výřezy a export obrázku',
        'zaklad' => ['task' => 'Na fotce s předkreslenou mřížkou vyznač ohnisko a udělej výřez 1 : 1.', 'check' => 'Ohnisko je ve výřezu celé.'],
        'standard' => ['task' => 'Vytvoř tři výřezy se stejným ohniskem, vyexportuj JPEG a WebP a napiš alternativní text.', 'check' => 'Tabulka formátů má velikosti v kB.'],
        'vyzva' => ['task' => 'Připrav i export 800 px pro mobil a spočítej úsporu dat.', 'check' => 'Úspora je vyjádřená v procentech.']],
    25 => ['activity' => 'Komponenty se stavy',
        'zaklad' => ['task' => 'Doplň k předloze tlačítka stavy hover a focus podle tabulky změn.', 'check' => 'Fokus je vidět i bez myši.'],
        'standard' => ['task' => 'Navrhni tlačítko, pole a kartu se všemi stavy a projdi je klávesnicí.', 'check' => 'Test klávesnicí nenajde ztracený fokus.'],
        'vyzva' => ['task' => 'Přidej stav „odesílám“ s ohledem na nastavení omezeného pohybu.', 'check' => 'Při omezeném pohybu animace neběží.']],
    26 => ['activity' => 'Workshop kritiky',
        'zaklad' => ['task' => 'Jako zapisovatel zapiš poznámky jednoho kola do tří sloupců.', 'check' => 'Každý sloupec má aspoň jednu poznámku.'],
        'standard' => ['task' => 'Dej ke každému návrhu poznámku do všech tří částí a sestav vlastní plán tří úprav.', 'check' => 'Úpravy jsou seřazené podle dopadu.'],
        'vyzva' => ['task' => 'Moderuj kolo: hlídej čas, strukturu a zapoj tišší členy skupiny.', 'check' => 'Každý člen skupiny promluvil.']],
    27 => ['activity' => 'Sprint landing page',
        'zaklad' => ['task' => 'V připravené kostře doplň obsah a jedno pravidlo mřížky s gap.', 'check' => 'Na 390 px se sekce skládají pod sebe.'],
        'standard' => ['task' => 'Projdi celý sprint: sdělení, model, stavba mřížkou, výzva k akci a ověření.', 'check' => 'Kontrolní seznam má tři ověřené kontroly.'],
        'vyzva' => ['task' => 'Přidej rozvržení pro široké obrazovky a porovnej grid a flexbox ve dvou větách.', 'check' => 'Volba techniky je zdůvodněná.']],
    28 => ['activity' => 'Portfolio a mapa kompetencí',
        'zaklad' => ['task' => 'Podle šablony napiš jednu případovou studii k vybrané práci.', 'check' => 'Studie má problém, rozhodnutí a výsledek.'],
        'standard' => ['task' => 'Napiš dvě studie, vyplň mapu osmi kompetencí a ověřitelný cíl.', 'check' => 'Každá kompetence má důkaz nebo mezeru.'],
        'vyzva' => ['task' => 'Přidej srovnání první a poslední verze práce s popisem zlepšení.', 'check' => 'Zlepšení je doložené ukázkou.']],
];
