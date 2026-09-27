<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · LAB-09 opakování s rozestupy – veřejná funkce pro LABUI (kontrakt §3.4).
 *
 * Motor (Leitnerovy přihrádky, fond úloh, kontext `review:<RRRRMMDD>`) je v `linux_v58_levels_review.php`
 * (auto-načítaný soubor jádra – musí tam být, aby platil i uvnitř lab_v57_api.php). Tenhle soubor jen
 * skládá výstup pro domovskou stránku Labu / stránku `lab&sekce=opakovani`.
 */

/**
 * Dnešní výběr 3 krátkých úloh pro žáka (vygeneruje se při první návštěvě dne, dál je stálý).
 * @return array{date:string,streak:int,items:list<array{level:string,title:string,box:int,done:bool,url:string}>}
 */
function lab58_review_today(string $classId, string $studentKey, ?int $now = null): array
{
    $now ??= time();
    $today = date('Y-m-d', $now);
    if (!function_exists('lab58_review_ensure_day')) return ['date' => $today, 'streak' => 0, 'items' => []];

    $ctxId = date('Ymd', $now);
    $data = lab58_review_ensure_day($classId, $studentKey, $now);
    $dayItems = (array)($data['days'][$today]['items'] ?? []);
    $solved = function_exists('lab57_solved') ? lab57_solved($classId, $studentKey, 'review:' . $ctxId) : [];

    $items = [];
    foreach ($dayItems as $it) {
        $levelId = (string)($it['level'] ?? '');
        $level = $levelId !== '' && function_exists('lab57_level') ? lab57_level($levelId) : null;
        if ($level === null) continue;
        $items[] = [
            'level' => $levelId,
            'title' => (string)$level['title'],
            'box' => max(1, min(5, (int)($it['box'] ?? 1))),
            'done' => isset($solved[$levelId]),
            'url' => '?view=lab&uroven=' . rawurlencode($levelId) . '&opakovani=' . $ctxId,
        ];
    }
    return ['date' => $today, 'streak' => lab58_review_streak($classId, $studentKey), 'items' => $items];
}
