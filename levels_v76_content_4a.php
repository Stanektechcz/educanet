<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · úrovně aktivit 4.A (provoz sítí a služeb), lekce 17–28 – NÁVRH (žákům až po schválení učitelem v záložce urovne75).
 * Navazuje na levels_v75_content_4a.php (L1–L16). Vše v simulátoru Linux Labu; adresy RFC 1918 / RFC 5737.
 */

return [
    17 => ['activity' => 'Incident podle runbooku',
        'zaklad' => ['task' => 'Zařaď čtyři scénáře do stupňů závažnosti podle dopadu.', 'check' => 'Každé zařazení má zdůvodnění dopadem.'],
        'standard' => ['task' => 'Veď cvičný incident v roli, napiš stavové zprávy a rozbor bez viníka.', 'check' => 'Rozbor má tři opatření s vlastníkem.'],
        'vyzva' => ['task' => 'Navrhni metriku, podle které tým pozná, že opatření pomohla.', 'check' => 'Metrika má výchozí a cílovou hodnotu.']],
    18 => ['activity' => 'Cvičení spolehlivosti',
        'zaklad' => ['task' => 'Podle karty ověř dvě oblasti (služba, disk) s důkazem.', 'check' => 'Každá oblast má výstup a závěr.'],
        'standard' => ['task' => 'Ověř čtyři oblasti včetně obnovy zálohy a vyplň tabulku rizik.', 'check' => 'Záloha je ověřená obnovou.'],
        'vyzva' => ['task' => 'Spočítej RPO a RTO pro zálohu jednou denně a obnovu za 20 minut.', 'check' => 'RPO 24 h, RTO aspoň 20 min s ověřením.']],
    19 => ['activity' => 'Zlaté signály z logu',
        'zaklad' => ['task' => 'Spočítej počet požadavků a chyb hotovými příkazy.', 'check' => 'Podíl chyb je v procentech.'],
        'standard' => ['task' => 'Spočítej provoz, chyby, p95 a saturaci a navrhni dvě upozornění.', 'check' => 'Upozornění mají práh a dobu trvání.'],
        'vyzva' => ['task' => 'Navrhni SLI a SLO pro chyby a spočítej rozpočet chyb.', 'check' => 'Rozpočet je v minutách nebo požadavcích.']],
    20 => ['activity' => 'Reverzní proxy a health check',
        'zaklad' => ['task' => 'Doplň schéma klient → proxy → upstream a přečti hlavičky odpovědi.', 'check' => 'Stav, server a typ obsahu jsou zapsané.'],
        'standard' => ['task' => 'Najdi v konfiguraci místo pro upstream a navrhni health check.', 'check' => 'Health check má cestu, odpověď, interval a práh.'],
        'vyzva' => ['task' => 'Navrhni rozložení zátěže mezi dva upstreamy a chování při výpadku jednoho.', 'check' => 'Návrh zmiňuje vyřazení nemocného upstreamu.']],
    21 => ['activity' => 'Síť kontejnerů',
        'zaklad' => ['task' => 'Doplň schéma image → container → volume a co se stane při aktualizaci.', 'check' => 'Data jsou ve svazku.'],
        'standard' => ['task' => 'Navrhni mapování portů bez kolize a síť pro web, api a db.', 'check' => 'Ven je publikovaný jen web.'],
        'vyzva' => ['task' => 'Popiš, jak omezit db jen na přístup z api.', 'check' => 'Popis neobsahuje publikovaný port db.']],
    22 => ['activity' => 'Cvičná obnova',
        'zaklad' => ['task' => 'Podle karty kroků proveď zálohu a obnovu a ověř obsah souboru.', 'check' => 'Obnovený soubor obsahuje původní text.'],
        'standard' => ['task' => 'Proveď obnovu, ověř ji otiskem a porovnej skutečné RPO a RTO s cíli.', 'check' => 'Otisky originálu a obnovy se shodují.'],
        'vyzva' => ['task' => 'Navrhni pravidlo 3-2-1 pro cvičnou službu.', 'check' => 'Návrh uvádí tři kopie, dvě média a jednu mimo provoz.']],
    23 => ['activity' => 'Audit zpevnění',
        'zaklad' => ['task' => 'Podle seznamu ohodnoť konfiguraci SSH a naslouchající porty.', 'check' => 'Každé nastavení má hodnocení.'],
        'standard' => ['task' => 'Proveď audit čtyř oblastí a napiš plán dvou změn s návratem.', 'check' => 'Plán nehrozí zamčením správce.'],
        'vyzva' => ['task' => 'Navrhni ochranu proti hádání hesel a popiš riziko zablokování.', 'check' => 'Návrh má práh a dobu zablokování.']],
    24 => ['activity' => 'Změna s rollbackem',
        'zaklad' => ['task' => 'Podle karty zazálohuj konfiguraci, proveď změnu a porovnej ji příkazem diff.', 'check' => 'diff ukazuje změněný řádek.'],
        'standard' => ['task' => 'Proveď chybnou změnu, vrať zálohu a test zopakuj; doplň podmínku zastavení.', 'check' => 'Po rollbacku test konfigurace projde.'],
        'vyzva' => ['task' => 'Navrhni automatizaci změny s testem a rollbackem bez ručního zásahu.', 'check' => 'Automatizace se při selhání testu sama vrátí.']],
    25 => ['activity' => 'Odchylka konfigurace',
        'zaklad' => ['task' => 'Spočítej otisk konfigurace před a po ruční změně.', 'check' => 'Otisky se liší.'],
        'standard' => ['task' => 'Dolož odchylku příkazem diff, roztřiď kroky podle idempotence a navrhni řešení.', 'check' => 'Řešení vede přes repozitář a revizi.'],
        'vyzva' => ['task' => 'Navrhni noční kontrolu odchylek a reakci na nález.', 'check' => 'Reakce zohledňuje dopad změny.']],
    26 => ['activity' => 'Kapacitní rezerva',
        'zaklad' => ['task' => 'Přečti dostupnou paměť a zatížení a spočítej rezervu podle vzorce.', 'check' => 'Rezerva je v procentech.'],
        'standard' => ['task' => 'Najdi zlom v zátěžovém testu, spočítej rezervu a napiš doporučení.', 'check' => 'Doporučení má čísla a plán ověření.'],
        'vyzva' => ['task' => 'Spočítej počet instancí pro špičku 600 req/s při rezervě 30 %.', 'check' => 'Výpočet vede na 3 instance.']],
    27 => ['activity' => 'Velení incidentu',
        'zaklad' => ['task' => 'Podle vzorce spočítej rozpočet chyb pro SLO 99,5 %.', 'check' => 'Výsledek je 216 minut.'],
        'standard' => ['task' => 'Spočítej spotřebu rozpočtu, rozhodni o nápravě nebo rollbacku a napiš zprávu pro vedení.', 'check' => 'Zpráva má dopad, stav, rozhodnutí a čas.'],
        'vyzva' => ['task' => 'Navrhni pravidlo zmrazení změn podle spotřeby rozpočtu.', 'check' => 'Pravidlo má práh a schvalovatele výjimek.']],
    28 => ['activity' => 'Scénář spolehlivého provozu',
        'zaklad' => ['task' => 'Podle karty ověř stav služby, test konfigurace a odpověď webu.', 'check' => 'Tři kroky mají důkaz.'],
        'standard' => ['task' => 'Projdi scénář s rollbackem, napiš rozbor, mapu kompetencí a plán rozvoje.', 'check' => 'Plán je měřitelný.'],
        'vyzva' => ['task' => 'Doplň do scénáře SLO a upozornění, která by problém odhalila dřív.', 'check' => 'Upozornění se váže na dopad na uživatele.']],
];
