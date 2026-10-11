<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · NÁVRH vazby 112 lekcí na výsledky vzdělávání ŠVP „Inteligentní digitální systémy 2026“ (id z svp_v72.php).
 * Podklad: témata lekcí (lm71) a krátké formulace výsledků. Stav každé vazby je „návrh“, dokud ji učitel neschválí
 * v záložce Schvalování lekcí (sekce Vazba na ŠVP). Výsledek jiného ročníku než ročník třídy je v přehledu označený.
 * Tvar: [třída => [číslo lekce => list id]].
 */

$dgd1 = 'ids26-dgd1-'; $dgd2 = 'ids26-dgd2-'; $as1 = 'ids26-as1-'; $fev = 'ids26-fev2-';
$sav2 = 'ids26-sav2-'; $sav3 = 'ids26-sav3-'; $sav4 = 'ids26-sav4-'; $sbe3 = 'ids26-sbe3-'; $sbe4 = 'ids26-sbe4-'; $asy4 = 'ids26-asy4-';

return [
    // 1.A · Grafika a webdesign · základy (1. ročník: DGD, AS; webové lekce L10+ vedou na FEV 2. ročníku – předstih)
    'class_1a' => [
        1 => [$dgd1 . '02', $dgd1 . '01'], 2 => [$dgd1 . '01', $dgd1 . '02'], 3 => [$dgd1 . '02', $dgd1 . '03'], 4 => [$dgd1 . '02', $dgd1 . '04'],
        5 => [$dgd1 . '01', $dgd1 . '02'], 6 => [$dgd1 . '02'], 7 => [$dgd1 . '04', $dgd1 . '03'], 8 => [$dgd1 . '02'],
        9 => [$dgd1 . '04', $as1 . '02'], 10 => [$fev . '01'], 11 => [$fev . '03', $fev . '01'], 12 => [$fev . '01', $dgd1 . '02'],
        13 => [$fev . '03'], 14 => [$dgd1 . '03', $dgd1 . '04'], 15 => [$fev . '02', $fev . '01'], 16 => [$dgd2 . '01'],
        17 => [$dgd1 . '02', $fev . '03'], 18 => [$fev . '02', $fev . '03'], 19 => [$dgd1 . '02'], 20 => [$fev . '01'],
        21 => [$fev . '03', $dgd1 . '04'], 22 => [$fev . '01'], 23 => [$fev . '03', $fev . '01'], 24 => [$dgd1 . '01', $dgd1 . '04'],
        25 => [$fev . '02', $fev . '03'], 26 => [$dgd1 . '02'], 27 => [$fev . '02', $fev . '04'], 28 => [$as1 . '01', $dgd1 . '02'],
    ],
    // 2.A · Grafika a webdesign (2. ročník: DGD, FEV)
    'class_2a' => [
        1 => [$dgd1 . '02'], 2 => [$dgd1 . '02', $dgd1 . '04'], 3 => [$dgd2 . '01', $fev . '01'], 4 => [$dgd2 . '02'],
        5 => [$dgd2 . '01', $fev . '03'], 6 => [$dgd2 . '01', $as1 . '01'], 7 => [$fev . '01', $fev . '03'], 8 => [$fev . '03'],
        9 => [$dgd2 . '01'], 10 => [$dgd2 . '01'], 11 => [$fev . '02', $dgd2 . '01'], 12 => [$dgd2 . '01'],
        13 => [$fev . '01', $fev . '02'], 14 => [$fev . '02', $fev . '03'], 15 => [$fev . '03'], 16 => [$dgd2 . '01'],
        17 => [$fev . '02', $fev . '03'], 18 => [$fev . '02', $dgd2 . '01'], 19 => [$dgd2 . '01'], 20 => [$dgd2 . '01'],
        21 => [$fev . '03'], 22 => [$fev . '02', $dgd2 . '01'], 23 => [$fev . '01'], 24 => [$fev . '03'],
        25 => [$dgd2 . '01'], 26 => [$fev . '02'], 27 => [$dgd2 . '01', $fev . '02'], 28 => [$as1 . '01', $dgd2 . '01'],
    ],
    // 3.A · Seminář OS a sítě (3. ročník: SAV, SBE; adresace a DHCP/NAT jsou v ŠVP ve 4. ročníku SBE, Linux uživatel v 2. ročníku SAV)
    'class_3a' => [
        1 => [$sbe4 . '03', $sbe4 . '01', $sbe3 . '01'], 2 => [$sbe3 . '05', $sbe3 . '04', $sbe4 . '06', $sbe4 . '05'], 3 => [$sav3 . '01', $sbe4 . '04'], 4 => [$sav3 . '07', $sav3 . '06'],
        5 => [$sbe4 . '03', $sbe3 . '03'], 6 => [$sav3 . '07', $sbe4 . '04'], 7 => [$sbe3 . '02', $sbe3 . '03'], 8 => [$sbe4 . '03', $sbe4 . '08'],
        9 => [$sav3 . '01', $sav3 . '09'], 10 => [$sav2 . '02'], 11 => [$sav3 . '02'], 12 => [$sav3 . '03'],
        13 => [$sav3 . '07', $sav3 . '03'], 14 => [$sav2 . '03', $sav2 . '05'], 15 => [$sav3 . '06'], 16 => [$sav3 . '01', $sav2 . '01'],
        17 => [$sav3 . '04', $sav2 . '04'], 18 => [$sav3 . '09', $sav3 . '03'], 19 => [$sav3 . '05', $sav3 . '09'], 20 => [$sav3 . '03'],
        21 => [$sav3 . '07'], 22 => [$sav2 . '05', $sav2 . '03'], 23 => [$sav3 . '01', $sbe3 . '03'], 24 => [$sav3 . '06'],
        25 => [$sav3 . '04'], 26 => [$sav3 . '09', $sav3 . '07'], 27 => [$sav3 . '09', $sav3 . '06'], 28 => [$sav3 . '09'],
    ],
    // 4.A · Seminář OS a sítě (4. ročník: SAV, SBE, ASY; provozní témata vedou i na SAV 3. ročníku)
    'class_4a' => [
        1 => [$sbe4 . '09', $sbe4 . '03'], 2 => [$sbe4 . '02', $sav3 . '07'], 3 => [$sav3 . '07', $sbe4 . '09'], 4 => [$sav3 . '09'],
        5 => [$sav4 . '04', $sav3 . '07'], 6 => [$sav3 . '05'], 7 => [$sav3 . '07', $sbe4 . '10'], 8 => [$sav3 . '04'],
        9 => [$asy4 . '01', $asy4 . '02'], 10 => [$sav3 . '03'], 11 => [$sav3 . '05'], 12 => [$sav3 . '05', $sav4 . '02'],
        13 => [$sav4 . '03', $sav4 . '01', $sbe4 . '08'], 14 => [$sav3 . '08'], 15 => [$sav3 . '04'], 16 => [$sbe4 . '09', $sbe4 . '10'],
        17 => [$sbe4 . '10', $sbe4 . '11'], 18 => [$sav3 . '09', $sav3 . '05'], 19 => [$sav3 . '07'], 20 => [$sav4 . '04', $sbe4 . '09'],
        21 => [$sav3 . '08', $sbe4 . '05'], 22 => [$sav3 . '05'], 23 => [$sav4 . '03', $sbe4 . '07', $sbe4 . '08'], 24 => [$sav3 . '09'],
        25 => [$sav3 . '04'], 26 => [$asy4 . '01', $asy4 . '02'], 27 => [$sbe4 . '10', $sbe4 . '11'], 28 => [$sav3 . '09', $sav3 . '07'],
    ],
];
