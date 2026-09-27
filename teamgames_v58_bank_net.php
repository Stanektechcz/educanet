<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · TG-BANK – souhrnná kvízová banka, síťová linie (sítě a Linux).
 * Slučuje kategorie ze samostatných souborů teamgames_v58_bank_net_<slug>.php. Načítá ji TG-CORE
 * (teamgames_v58_quiz.php ⇒ tg58_quiz_external_bank) obyčejným `include`, který smí v jednom
 * requestu proběhnout i vícekrát – proto tento soubor obsahuje jen jediný `return`, žádné funkce,
 * třídy ani konstanty (stejné pravidlo platí pro všechny require dané soubory).
 *
 * Mapa kategorií (slug → český název, jen pro dokumentaci a případné zobrazení jinde v aplikaci):
 *   linux    – Příkazy Linuxu
 *   files    – Soubory a práva
 *   ip       – IP a maska
 *   dns      – DNS
 *   ports    – Služby a porty
 *   procs    – Procesy a služby (systemd)
 *   security – Bezpečnost vlastního systému
 *   hw       – Hardware a OS
 */

return array_merge(
    require __DIR__ . '/teamgames_v58_bank_net_linux.php',
    require __DIR__ . '/teamgames_v58_bank_net_files.php',
    require __DIR__ . '/teamgames_v58_bank_net_ip.php',
    require __DIR__ . '/teamgames_v58_bank_net_dns.php',
    require __DIR__ . '/teamgames_v58_bank_net_ports.php',
    require __DIR__ . '/teamgames_v58_bank_net_procs.php',
    require __DIR__ . '/teamgames_v58_bank_net_security.php',
    require __DIR__ . '/teamgames_v58_bank_net_hw.php'
);
