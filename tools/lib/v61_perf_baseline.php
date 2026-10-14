<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · základna poměrů ms stránky / ms referenční stránky pro audit v61 (regrese > 20 % = FAIL).
 * Generováno: php tools/v61_perf_audit.php --update-baseline (3× medián z 11 běhů).
 * Platí jen pro stroj s otiskem 'machine'; na jiném stroji se porovnání se základnou jen vypíše.
 */

return array (
  'machine' => '75439c9e5410',
  'created' => '2026-10-14',
  'pages' => 
  array (
    'dashboard' => 2.076,
    'dovednosti' => 2.096,
    'hadanka' => 1.597,
    'hlaseni' => 1.602,
    'hodina' => 1.92,
    'hry' => 1.64,
    'komunita' => 2.146,
    'lab' => 1.834,
    'lekce' => 1.938,
    'materialy' => 1.81,
    'obchod' => 1.741,
    'prikazy' => 1.884,
    'profil' => 2.011,
    'profil-nastaveni' => 2.034,
    'profil-odznaky' => 2.313,
    'projekty' => 1.649,
    'roboti' => 1.599,
    'soukromi' => 1.633,
    'studium' => 2.198,
    'ucitel-analytics' => 2.5,
    'ucitel-arena' => 2.442,
    'ucitel-attention' => 2.392,
    'ucitel-class_results' => 2.379,
    'ucitel-overview' => 2.52,
    'ucitel-prehled' => 2.48,
    'ucitel-pristupy' => 2.252,
    'ucitel-reports' => 2.36,
    'ucitel-session' => 2.196,
    'ucitel-student360' => 2.478,
    'vysledky' => 1.777,
  ),
);
