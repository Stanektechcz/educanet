<?php

declare(strict_types=1);

// v59 OPS-02: trm()/tr() i při načtení bez bootstrap.php (CLI audity).
if (!function_exists('trm')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

function assessment_visual_spec(string $classId, string $topic, string $questionText = ''): array
{
    $graphics = in_array($classId, ['class_1a','class_2a'], true);
    $hay = strtolower($topic . ' ' . $questionText);
    $kind = 'process';
    $title = trm('Pozoruj princip');
    $concept = trm('Model situace');
    $prompt = trm('Sleduj změnu a zkus předem odhadnout výsledek.');
    $evidence = '';

    if ($graphics) {
        if (preg_match('/contrast|barv|color|accessib/u', $hay)) {
            $kind='contrast'; $title=trm('Kontrast a čitelnost'); $concept=trm('Rozdíl mezi prvkem a pozadím');
            $prompt=trm('Přepni stav a sleduj, kdy hierarchie zůstává čitelná i bez barvy.');
            $evidence=trm('Dobrá volba obstojí i v grayscale a při rychlém pohledu.');
        } elseif (preg_match('/hierarch|cta/u', $hay)) {
            $kind='hierarchy'; $title=trm('Vizuální hierarchie'); $concept=trm('Pořadí pozornosti');
            $prompt=trm('Pusť 3sekundovou simulaci a sleduj, co oko zachytí jako první, druhé a třetí.');
            $evidence=trm('Headline → klíčová informace → CTA.');
        } elseif (preg_match('/typograph/u', $hay)) {
            $kind='typography'; $title=trm('Typografie'); $concept=trm('Velikost, řádkování a kontrast řezu');
            $prompt=trm('Přepni variantu a porovnej čitelnost stejného sdělení.');
            $evidence=trm('Čitelnost nevzniká jen větším písmem, ale kombinací velikosti, řezu a spacingu.');
        } elseif (preg_match('/grid|alignment|composition|white|spacing|responsive/u', $hay)) {
            $kind='layout'; $title=trm('Layout a spacing'); $concept=trm('Společné hrany, rytmus a bezpečné mezery');
            $prompt=trm('Zapni mřížku a sleduj, jak se prvky opírají o stejné linie.');
            $evidence=trm('Přehledný layout sdílí zarovnávací linie a konzistentní mezery.');
        } elseif (preg_match('/raster|vector|resolution|logo|export|format/u', $hay)) {
            $kind='raster'; $title=trm('Raster vs. vektor'); $concept=trm('Co se stane při zvětšení');
            $prompt=trm('Přepni zoom a porovnej hranu rasteru s vektorovou křivkou.');
            $evidence=trm('Raster má konečný počet pixelů; vektor se přepočítává z geometrie.');
        } elseif (preg_match('/asset|copyright|licen/u', $hay)) {
            $kind='assets'; $title=trm('Zdroj a licence'); $concept=trm('Použít ≠ mít právo použít');
            $prompt=trm('Přepni stav zdroje a sleduj, které informace musíš znát před publikací.');
            $evidence=trm('Autor + licence + podmínky použití jsou součást designového workflow.');
        } elseif (preg_match('/photo|image|crop|icon/u', $hay)) {
            $kind='crop'; $title=trm('Obraz a výřez'); $concept=trm('Focal point a bezpečný výřez');
            $prompt=trm('Přepni výřez a sleduj, zda zůstane zachovaný hlavní motiv a prostor pro text.');
            $evidence=trm('Výřez má podporovat sdělení, ne jen vyplnit plochu.');
        } else {
            $kind='hierarchy'; $title=trm('Designové rozhodnutí'); $concept=trm('Cíl → hierarchie → ověření');
            $prompt=trm('Sleduj cestu oka a zkus pojmenovat první dvě informace.');
            $evidence=trm('Každý vizuální efekt musí mít komunikační důvod.');
        }
    } else {
        if (preg_match('/dhcp/u', $hay)) {
            $kind='dhcp'; $title=trm('DHCP DORA'); $concept=trm('Jak klient získá konfiguraci');
            $prompt=trm('Pusť tok a sleduj čtyři zprávy Discover → Offer → Request → ACK.');
            $evidence=trm('Pokud DHCP selže, klient může skončit s adresou 169.254.x.x.');
        } elseif (preg_match('/dns/u', $hay)) {
            $kind='dns'; $title=trm('DNS překlad'); $concept=trm('Jméno → resolver → odpověď');
            $prompt=trm('Pusť dotaz a sleduj, kde vzniká informace o cílové IP.');
            $evidence=trm('nslookup/dig ukáže, zda problém vzniká už při překladu jména.');
        } elseif (preg_match('/packet|wireshark/u', $hay)) {
            $kind='packet'; $title=trm('Packet journey'); $concept=trm('Důkaz v jednotlivých paketech');
            $prompt=trm('Pusť capture a sleduj ARP, DNS a TCP jako samostatné kroky.');
            $evidence=trm('Pořadí paketů pomáhá určit, ve které vrstvě se komunikace zastavila.');
        } elseif (preg_match('/rout|gateway|subnet|cidr|ipv6|ip-address|vlan|arp|nat|firewall|security|acl|zone/u', $hay)) {
            $kind='route'; $title=trm('Cesta paketu'); $concept=trm('Lokální síť, gateway a další hop');
            $prompt=trm('Přepni cíl a sleduj, kdy host odešle rámec přímo a kdy přes gateway.');
            $evidence=trm('Maska/prefix rozhoduje, zda je cíl lokální; route určuje další hop.');
        } elseif (preg_match('/ssh|sftp|permission|key|chmod/u', $hay)) {
            $kind='ssh'; $title=trm('SSH přístupová cesta'); $concept=trm('TCP → služba → autentizace → práva');
            $prompt=trm('Pusť přihlášení a sleduj, ve kterém kroku může vzniknout chyba.');
            $evidence=trm('Permission denied (publickey) neznamená stejný problém jako nedostupný TCP/22.');
        } elseif (preg_match('/https|tls|tcp|port|bind|service|proxy|http|load-balanc/u', $hay)) {
            $kind='service'; $title=trm('Service stack'); $concept=trm('Host → port → protokol → aplikace');
            $prompt=trm('Pusť request a sleduj, přes které vrstvy musí projít až k aplikaci.');
            $evidence=trm('Funkční ping nedokazuje funkční TCP port ani funkční aplikaci.');
        } elseif (preg_match('/icmp|ping|traceroute|diagnostic|monitor|log|slo|observ|golden|performance|capacity|latency|throughput|saturation/u', $hay)) {
            $kind='probe'; $title=trm('Diagnostická sonda'); $concept=trm('Test → měření → závěr');
            $prompt=trm('Pusť sondu a sleduj rozdíl mezi dostupností hosta a dostupností služby.');
            $evidence=trm('Jeden test potvrzuje jen konkrétní část cesty; nepřidávej závěry, které neměří.');
        } elseif (preg_match('/filesystem|storage|disk|inode|mount/u', $hay)) {
            $kind='probe'; $title=trm('Storage evidence'); $concept=trm('Filesystem → využití → příčina');
            $prompt=trm('Porovnej dostupnou kapacitu, inode stav a místo, kde data skutečně rostou.');
            $evidence=trm('df, inode informace a cílené du odpovídají na jiné části otázky „proč je disk plný?“.');
        } elseif (preg_match('/systemd|process|container|runtime/u', $hay)) {
            $kind='service'; $title=trm('Životní cyklus služby'); $concept=trm('Proces → služba → dependency → stav');
            $prompt=trm('Sleduj, co musí být splněno, aby služba skutečně běžela a byla dostupná.');
            $evidence=trm('Running proces, správný listener a splněné dependency jsou odlišné důkazy.');
        } elseif (preg_match('/automation|shell|script|bash|error-handling|cron|idempoten|runbook|incident/u', $hay)) {
            $kind='decision'; $title=trm('Bezpečný operační postup'); $concept=trm('Stav → rozhodnutí → změna → ověření');
            $prompt=trm('Projdi kroky a sleduj, kde patří guard, rollback a následné ověření.');
            $evidence=trm('Opakovatelný postup nejdřív zjišťuje current state a po změně vždy ověřuje výsledek.');
        } elseif (preg_match('/change|canary|backup|restore|rollback|troubleshoot|infrastructure-as-code|desired|drift|iac/u', $hay)) {
            $kind='decision'; $title=trm('Incident decision loop'); $concept=trm('Symptom → hypotéza → test → důkaz');
            $prompt=trm('Pusť rozhodovací smyčku a sleduj, proč změna konfigurace patří až za měření.');
            $evidence=trm('Nejdřív stabilizuj a ověř, teprve potom měň další proměnnou.');
        }
    }
    $stateLabels = [
        'dns'=>trm('Simulovat chybu DNS'),'dhcp'=>trm('Simulovat chybějící ACK'),'packet'=>trm('Zvýraznit problém'),
        'route'=>trm('Zvýraznit rozhodnutí'),'ssh'=>trm('Simulovat chybu klíče'),'service'=>trm('Simulovat blokovanou vrstvu'),
        'probe'=>trm('Změnit výsledek testu'),'decision'=>trm('Zvýraznit správné pořadí'),'contrast'=>trm('Přepnout kontrast'),
        'hierarchy'=>trm('Potlačit hierarchii'),'typography'=>trm('Porovnat variantu'),'layout'=>trm('Skrýt mřížku'),
        'raster'=>trm('Zvětšit raster'),'assets'=>trm('Ověřit zdroj'),'crop'=>trm('Posunout výřez'),
    ];
    $stateLabel = $stateLabels[$kind] ?? trm('Změnit stav');
    return compact('kind','title','concept','prompt','evidence','stateLabel');
}
