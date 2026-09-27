<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v59 · Linux Lab offline – export katalogu domény „js_lab_offline“ pro stránku bez
 * připojení (lab-offline.html + assets/lab-offline-v58.js).
 *
 * Ta stránka nemá vlastní PHP request (žádné `edu_tr_json_js()`), takže si svůj katalog nemůže
 * vyžádat přes obvyklé bloky `<script class="edu-tr-json">` – JS si ho místo toho stáhne jako
 * statický JSON ze stejného originu (assets/lab-offline-i18n-v59.json), jazyk čte z
 * localStorage.edu_lang (viz PLAN_I18N.md „Pasti“ #7).
 *
 * Načte katalogy lang/en/ui/js_lab_offline.php a lang/uk/ui/js_lab_offline.php a uloží je jako
 * {"en": {...}, "uk": {...}} (plurály beze změny, stejný tvar jako v katalogu) do
 * assets/lab-offline-i18n-v59.json. Žádná osobní data – jen statické texty UI.
 *
 * Spuštění:  C:/php/php.exe tools/v59_export_offline_i18n.php
 * Integrátor tento nástroj spustí znovu po každé změně katalogu js_lab_offline a přidá výstupní
 * JSON do seznamu OFFLINE_LAB v sw.js (aby fungoval i bez připojení).
 */

$ROOT = dirname(__DIR__);

$locales = ['en', 'uk'];
$out = [];
foreach ($locales as $locale) {
    $path = $ROOT . '/lang/' . $locale . '/ui/js_lab_offline.php';
    if (!is_file($path)) {
        fwrite(STDERR, "FAIL katalog nenalezen: $path\n");
        exit(1);
    }
    $catalog = require $path;
    if (!is_array($catalog)) {
        fwrite(STDERR, "FAIL katalog $path nevrátil pole\n");
        exit(1);
    }
    ksort($catalog, SORT_STRING);
    $out[$locale] = $catalog;
}

$outPath = $ROOT . '/assets/lab-offline-i18n-v59.json';
$json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
if (file_put_contents($outPath, $json . "\n") === false) {
    fwrite(STDERR, "FAIL nelze zapsat $outPath\n");
    exit(1);
}

echo 'V59_EXPORT_OFFLINE_I18N_OK en=' . count($out['en']) . ' uk=' . count($out['uk']) . "\n";
