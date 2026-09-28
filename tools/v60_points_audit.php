<?php

declare(strict_types=1);

/**
 * EDUCANET v60 · behaviorální audit peněženky bodů (points_v53.php RMW oprava + points_v60.php).
 *   php tools/v60_points_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/.
 * Konec: V60_POINTS_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v60-points')), '/');
require $root . '/bootstrap.php';
require_once $root . '/points_v53.php';
require_once $root . '/points_v60.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp);

$classId = 'class_3a';
$student = 'class_3a:student:Audit Test';

// --- 1) Základní award/purchase/balance (beze změny chování) ---------------------------------
$check('award: nový klíč nastaví body', pts53_award($classId, $student, 'hint:a:1', 3) === 3);
$check('award: nižší hodnota stejného klíče se nepřepíše', pts53_award($classId, $student, 'hint:a:1', 1) === 3);
$check('balance: odpovídá součtu awards', pts53_balance($classId, $student) === 3);

// --- 2) RMW oprava pts53_save_wallet – souběžný zápis se neztratí ------------------------------
// Dva „paralelní“ mutátory: každý vidí AKTUÁLNÍ (v zámku čerstvou) peněženku, ne kopii zvenku.
pts53_save_wallet($classId, $student, static function (array $w): array {
    $w['awards']['manual:x'] = ['points' => 2, 'reason' => 'x', 'at' => date(DATE_ATOM)];
    return $w;
});
pts53_save_wallet($classId, $student, static function (array $w): array {
    $w['awards']['manual:y'] = ['points' => 4, 'reason' => 'y', 'at' => date(DATE_ATOM)];
    return $w;
});
$wallet = pts53_wallet($classId, $student);
$check('RMW: dva po sobě jdoucí pts53_save_wallet neztratí žádný zápis', isset($wallet['awards']['manual:x']) && isset($wallet['awards']['manual:y']));
$check('RMW: earned se přepočítal ze VŠECH awards (3+2+4=9)', (int)$wallet['earned'] === 9);

// Simulace souběhu: mutátor dostane data načtená PŘED zápisem druhého volání (storage_map_update čte znovu v zámku).
$before = pts53_wallet($classId, $student); // „zastaralá“ kopie, jako by ji držel jiný proces
pts53_save_wallet($classId, $student, static function (array $w): array {
    $w['awards']['manual:z'] = ['points' => 1, 'reason' => 'z', 'at' => date(DATE_ATOM)];
    return $w;
});
// Kdyby pts53_save_wallet zapisovalo $before (zastaralou kopii) místo čerstvé, manual:z by teď zmizelo.
$after = pts53_wallet($classId, $student);
$check('RMW: následné čtení vidí data načtená přímo pod zámkem (žádný stale write)', isset($after['awards']['manual:z']) && isset($after['awards']['manual:x']));

// --- 3) pts53_purchase je idempotentní a hlídá zůstatek ----------------------------------------
$balanceBefore = pts53_balance($classId, $student);
$check('purchase: nákup do rámce zůstatku projde', pts53_purchase($classId, $student, 'buy:1', 2, 'test'));
$check('purchase: opakování stejného klíče je idempotentní (bez druhého odečtu)', pts53_purchase($classId, $student, 'buy:1', 2, 'test') && pts53_balance($classId, $student) === $balanceBefore - 2);
$check('purchase: nedostatek bodů odmítne nákup', !pts53_purchase($classId, $student, 'buy:huge', 999999, 'test'));

// --- 4) pts60_summary/pts60_weekly_series ------------------------------------------------------
$summary = pts60_summary($classId, $student, 5);
$check('pts60_summary: balance = earned - spent', $summary['balance'] === max(0, (int)$summary['earned'] - (int)$summary['spent']));
$check('pts60_summary: history respektuje limit', count($summary['history']) <= 5);
$weekly = pts60_weekly_series($classId, $student, 4);
$check('pts60_weekly_series: vrací přesně požadovaný počet týdnů', count($weekly) === 4);
$weeklySum = array_sum(array_column($weekly, 'value'));
$check('pts60_weekly_series: součet týdnů odpovídá součtu awards v okně (nezáporný)', $weeklySum >= 0);

// --- 5) pts60_refund – vrátí body, nákup zůstává v historii, idempotentní ----------------------
$balanceAfterBuy = pts53_balance($classId, $student);
$check('refund: vrátí body kupujícímu', pts60_refund($classId, $student, 'buy:1', 'test refund') && pts53_balance($classId, $student) === $balanceAfterBuy + 2);
$walletAfterRefund = pts53_wallet($classId, $student);
$check('refund: původní nákup zůstává v historii (nikdy se nemaže)', isset($walletAfterRefund['purchases']['buy:1']));
$check('refund: je idempotentní (druhé volání nic nevrátí navíc)', !pts60_refund($classId, $student, 'buy:1', 'znovu') && pts53_balance($classId, $student) === $balanceAfterBuy + 2);
$check('pts60_is_refunded: hlásí vrácený nákup', pts60_is_refunded($classId, $student, 'buy:1'));
$check('refund: neexistující nákup vrátit nejde', !pts60_refund($classId, $student, 'buy:neexistuje', 'x'));

exit(audit_summary($state, 'V60_POINTS'));
