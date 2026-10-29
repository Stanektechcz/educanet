<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v60 · Přehled bodů pro profil žáka (nová záložka „body“) a učitelské vrácení nákupu.
 * Čte a zapisuje výhradně přes API points_v53.php (pts53_wallet/pts53_save_wallet); žádné vlastní
 * úložiště. Nikdy nevrací cizí peněženku – volající (profile_v60.php) smí zavolat pts60_summary()
 * jen pro vlastní profil (isMe), nikdy pro cizí.
 */

/** Souhrn pro záložku „body“: zůstatek, získáno/utraceno, posledních $limit pohybů. */
function pts60_summary(string $classId, string $studentKey, int $limit = 20): array
{
    $wallet = pts53_wallet($classId, $studentKey);
    return [
        'balance' => max(0, (int)$wallet['earned'] - (int)$wallet['spent']),
        'earned' => (int)$wallet['earned'],
        'spent' => (int)$wallet['spent'],
        'history' => pts53_history($classId, $studentKey, $limit),
    ];
}

/**
 * Body získané po týdnech (pondělí, ISO) za posledních $weeks týdnů – jen kladné přírůstky (awards).
 * @return list<array{label:string,value:int}>
 */
function pts60_weekly_series(string $classId, string $studentKey, int $weeks = 8): array
{
    $wallet = pts53_wallet($classId, $studentKey);
    $buckets = [];
    $order = [];
    $today = new DateTimeImmutable('today');
    for ($i = $weeks - 1; $i >= 0; $i--) {
        $weekStart = $today->modify('monday this week')->modify('-' . $i . ' week');
        $key = $weekStart->format('o-\WW');
        $buckets[$key] = 0;
        $order[] = ['key' => $key, 'label' => $weekStart->format('j.n.')];
    }
    foreach ((array)$wallet['awards'] as $a) {
        $at = (string)($a['at'] ?? '');
        if ($at === '') continue;
        $ts = strtotime($at);
        if ($ts === false) continue;
        $key = (new DateTimeImmutable('@' . $ts))->format('o-\WW');
        if (array_key_exists($key, $buckets)) $buckets[$key] += (int)($a['points'] ?? 0);
    }
    $out = [];
    foreach ($order as $row) {
        $out[] = ['label' => $row['label'], 'value' => (int)$buckets[$row['key']]];
    }
    return $out;
}

/**
 * Vrácení nákupu učitelem/adminem: přidá award `refund:<purchaseKey>` ve výši ceny nákupu (peníze se
 * vrátí do zůstatku), samotný nákup NIKDY nemaže (zůstává v historii jako doklad, že proběhl a byl
 * vrácen). Volající (teacher_* akce) si musí ověřit oprávnění a rozsah třídy PŘED zavoláním.
 * Idempotentní: druhé zavolání pro stejný nákup vrátí false (žádné body navíc).
 */
function pts60_refund(string $classId, string $studentKey, string $purchaseKey, string $reason = ''): bool
{
    if ($studentKey === '' || $purchaseKey === '') return false;
    $wallet = pts53_wallet($classId, $studentKey);
    $purchase = $wallet['purchases'][$purchaseKey] ?? null;
    if (!is_array($purchase)) return false;
    $refundKey = 'refund:' . $purchaseKey;
    if (isset($wallet['awards'][$refundKey])) return false;
    $cost = max(0, (int)($purchase['cost'] ?? 0));
    if ($cost <= 0) return false;
    $awarded = pts53_award($classId, $studentKey, $refundKey, $cost, $reason !== '' ? $reason : ('Vráceno: ' . (string)($purchase['reason'] ?? $purchaseKey)));
    return $awarded >= $cost;
}

/** true, pokud byl daný nákup vrácen (má odpovídající award). */
function pts60_is_refunded(string $classId, string $studentKey, string $purchaseKey): bool
{
    $wallet = pts53_wallet($classId, $studentKey);
    return isset($wallet['awards']['refund:' . $purchaseKey]);
}
