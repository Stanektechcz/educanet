<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · úrovně aktivit 3.A (operační systémy a sítě), lekce 17–28 – NÁVRH (žákům až po schválení učitelem v záložce urovne75).
 * Navazuje na levels_v75_content_3a.php (L1–L16). Sítě jen v simulátoru a s adresami RFC 1918 / RFC 5737; nic se nespouští mimo Linux Lab.
 */

return [
    17 => ['activity' => 'Skript zálohy s logem a cron',
        'zaklad' => ['task' => 'Doplň do kostry skriptu zápis začátku a konce do logu a ověř návratový kód.', 'check' => 'Log má dva řádky a $? je 0.'],
        'standard' => ['task' => 'Napiš skript zálohy s archivem, logem a kontrolou adresáře a zapiš tři cron výrazy.', 'check' => 'Cron výrazy odpovídají zadaným časům.'],
        'vyzva' => ['task' => 'Navrhni rotaci záloh (posledních 7 archivů) a postup ověření.', 'check' => 'Rotace nesmaže poslední funkční zálohu.']],
    18 => ['activity' => 'Incident „web nejde“',
        'zaklad' => ['task' => 'Podle karty příkazů zjisti stav služby a port a službu spusť.', 'check' => 'curl -I vrací 200 OK.'],
        'standard' => ['task' => 'Vyřeš incident od důkazu k nápravě a sepiš záznam incidentu.', 'check' => 'Záznam má příznak, příčinu, opravu, ověření a prevenci.'],
        'vyzva' => ['task' => 'Navrhni kontrolu, která výpadek ohlásí dřív než uživatel.', 'check' => 'Kontrola má interval a práh upozornění.']],
    19 => ['activity' => 'Zaplněný disk',
        'zaklad' => ['task' => 'Z popsaného výpisu df -h přečti zaplnění a přípojný bod a najdi největší adresář v /var/log.', 'check' => 'Odpověď obsahuje procenta a cestu.'],
        'standard' => ['task' => 'Najdi příčinu zaplnění (df, du, find, stat) a navrhni bezpečný úklid s retencí.', 'check' => 'Úklid má ověření, že služba dál zapisuje.'],
        'vyzva' => ['task' => 'Vysvětli, proč po smazání otevřeného logu místo nepřibude.', 'check' => 'Vysvětlení zmiňuje otevřený soubor a inode.']],
    20 => ['activity' => 'Služby po restartu',
        'zaklad' => ['task' => 'Vypiš služby a u nginx zjisti is-active a is-enabled.', 'check' => 'Rozliší „běží“ a „spustí se po startu“.'],
        'standard' => ['task' => 'Nakresli graf závislostí služeb a popiš scénář „po restartu web nejede“ s opravou.', 'check' => 'Postup obsahuje ověření po restartu.'],
        'vyzva' => ['task' => 'Vysvětli, proč se úprava jednotky dělá v drop-in souboru.', 'check' => 'Vysvětlení zmiňuje aktualizace balíčku.']],
    21 => ['activity' => 'Časová osa z logů',
        'zaklad' => ['task' => 'Podle rozebraného řádku access logu spočítej odpovědi podle kódů hotovým příkazem.', 'check' => 'Počty 200, 403 a 404 sedí.'],
        'standard' => ['task' => 'Sestav časovou osu z journalu a access logu a odděl fakta od domněnek.', 'check' => 'Každý fakt má čas a zdroj.'],
        'vyzva' => ['task' => 'Zjisti, ze které adresy přišlo nejvíc chybových odpovědí.', 'check' => 'Příkaz kombinuje awk, sort a uniq -c.']],
    22 => ['activity' => 'SSH klíče',
        'zaklad' => ['task' => 'Vytvoř pár klíčů a doplň schéma, který soubor je soukromý a který veřejný.', 'check' => 'Soukromý klíč má práva 600.'],
        'standard' => ['task' => 'Vytvoř klíče, ověř práva, nakresli schéma s authorized_keys a known_hosts a zkontroluj sshd_config.', 'check' => 'Schéma má tři soubory na správném místě.'],
        'vyzva' => ['task' => 'Popiš bezpečný postup při změně otisku serveru v known_hosts.', 'check' => 'Postup ověřuje otisk jiným kanálem.']],
    23 => ['activity' => 'Řetěz důkazů DNS',
        'zaklad' => ['task' => 'V připravené tabulce doplň důkazy pro jméno intranetu (dig, nc, curl).', 'check' => 'Všechny čtyři články mají výstup.'],
        'standard' => ['task' => 'Sestav řetěz důkazů pro funkční a pro neexistující jméno.', 'check' => 'Řetěz u neexistujícího jména končí NXDOMAIN.'],
        'vyzva' => ['task' => 'Vysvětli prázdnou odpověď AAAA se stavem NOERROR.', 'check' => 'Vysvětlení rozliší „jméno existuje“ a „záznam chybí“.']],
    24 => ['activity' => 'Pravidla firewallu',
        'zaklad' => ['task' => 'Otestuj nc tři cíle a výsledky zařaď do tabulky otevřeno / zavřeno / zahozeno.', 'check' => 'Refused a timeout jsou správně rozlišené.'],
        'standard' => ['task' => 'Seřaď pravidla stavového firewallu a navrhni tabulku pravidel s testy.', 'check' => 'Výchozí zákaz je na konci a každé pravidlo má test.'],
        'vyzva' => ['task' => 'Navrhni omezení naslouchání služby na 127.0.0.1 a zdůvodni ho.', 'check' => 'Zdůvodnění zmiňuje menší útočnou plochu.']],
    25 => ['activity' => 'Bezpečný skript',
        'zaklad' => ['task' => 'V kostře skriptu doplň uvozovky kolem proměnných s cestou.', 'check' => 'Soubor s mezerou vznikne jako jeden.'],
        'standard' => ['task' => 'Přidej do skriptu kontroly, log chyb a režim nanečisto.', 'check' => 'Nanečisto se nic nesmaže.'],
        'vyzva' => ['task' => 'Popiš, co dělá set -euo pipefail a kterou chybu by zachytilo.', 'check' => 'Popis vysvětlí všechny tři přepínače.']],
    26 => ['activity' => 'Obnova služby po vrstvách',
        'zaklad' => ['task' => 'Podle karty vrstev ověř ping, port 80 a odpověď webu.', 'check' => 'Každá vrstva má výstup.'],
        'standard' => ['task' => 'Projdi diagnostiku po vrstvách, obnov službu a napiš runbook.', 'check' => 'Runbook má 5–7 kroků s očekávaným výsledkem.'],
        'vyzva' => ['task' => 'Popiš, co je potřeba pro HTTPS a jak ho ověřit.', 'check' => 'Popis zmiňuje certifikát a port 443.']],
    27 => ['activity' => 'Týmový incident',
        'zaklad' => ['task' => 'V roli zapisovatele veď časovou osu incidentu.', 'check' => 'Každý záznam má čas a zdroj.'],
        'standard' => ['task' => 'Pracuj ve své roli a s týmem uzavři incident se záznamem.', 'check' => 'Uzavření má příčinu, nápravu, ověření a prevenci.'],
        'vyzva' => ['task' => 'Jako vedoucí rozhoduj o krocích a na konci zhodnoť nejrizikovější rozhodnutí.', 'check' => 'Hodnocení zmiňuje alternativu.']],
    28 => ['activity' => 'Stanoviště a plán procvičení',
        'zaklad' => ['task' => 'Projdi tři stanoviště s nápovědou prvního příkazu.', 'check' => 'Každé stanoviště má důkaz.'],
        'standard' => ['task' => 'Projdi pět stanovišť, vyplň mapu kompetencí a napiš plán procvičení.', 'check' => 'Plán má úroveň labu nebo cestu a termín.'],
        'vyzva' => ['task' => 'Na stanovišti síť rozděl 10.0.0.0/24 na čtyři podsítě.', 'check' => 'Rozsahy /26 na sebe navazují.']],
];
