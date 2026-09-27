<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · TG-BANK – souhrnná kvízová banka, grafická linie (grafika a webdesign).
 * Slučuje kategorie ze samostatných souborů teamgames_v58_bank_gfx_<slug>.php. Načítá ji TG-CORE
 * (teamgames_v58_quiz.php ⇒ tg58_quiz_external_bank) obyčejným `include`, který smí v jednom
 * requestu proběhnout i vícekrát – proto tento soubor obsahuje jen jediný `return`, žádné funkce,
 * třídy ani konstanty (stejné pravidlo platí pro všechny require dané soubory).
 *
 * Mapa kategorií (slug → český název, jen pro dokumentaci a případné zobrazení jinde v aplikaci):
 *   color    – Barvy a kontrast
 *   type     – Typografie
 *   formats  – Rastr, vektor a formáty
 *   html     – HTML
 *   css      – CSS
 *   a11y     – Přístupnost webu
 *   ux       – Kompozice a UX
 *   license  – Licence (Creative Commons)
 */

return array_merge(
    require __DIR__ . '/teamgames_v58_bank_gfx_color.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_type.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_formats.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_html.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_css.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_a11y.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_ux.php',
    require __DIR__ . '/teamgames_v58_bank_gfx_license.php'
);
