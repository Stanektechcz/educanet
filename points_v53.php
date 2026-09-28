<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v53 · Body a nápovědy.
 * - známka se přepočítá na body: 1 = 3 body, 2 = 2 body, 3 = 1 bod (4 a 5 = 0),
 * - interaktivní úkoly dávají 1–3 body,
 * - první nápověda je zdarma, každá další stojí 2 body.
 */

const PTS53_HINT_COST = 2;

/** 1 bod · 2 body · 5 bodů */
function pts53_points_label(int $points): string
{
    return trn(['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'], $points);
}

function pts53_points_for_grade(int $grade): int
{
    return match ($grade) { 1 => 3, 2 => 2, 3 => 1, default => 0 };
}

function pts53_path(): string
{
    return STORAGE_DIR . '/points_v53.json.php';
}

function pts53_key(string $classId, string $studentKey): string
{
    return $classId . '|' . $studentKey;
}

function pts53_wallet(string $classId, string $studentKey): array
{
    $all = load_php_json(pts53_path());
    $row = is_array($all[pts53_key($classId, $studentKey)] ?? null) ? $all[pts53_key($classId, $studentKey)] : [];
    return array_replace(['earned' => 0, 'spent' => 0, 'awards' => [], 'purchases' => []], $row);
}

function pts53_balance(string $classId, string $studentKey): int
{
    $w = pts53_wallet($classId, $studentKey);
    return max(0, (int)$w['earned'] - (int)$w['spent']);
}

function pts53_wallet_row(array $all, string $classId, string $studentKey): array
{
    $row = is_array($all[pts53_key($classId, $studentKey)] ?? null) ? $all[pts53_key($classId, $studentKey)] : [];
    return array_replace(['earned' => 0, 'spent' => 0, 'awards' => [], 'purchases' => []], $row);
}

function pts53_wallet_totals(array $wallet): array
{
    $wallet['earned'] = array_sum(array_map(static fn($a) => (int)($a['points'] ?? 0), (array)$wallet['awards']));
    $wallet['spent'] = array_sum(array_map(static fn($p) => (int)($p['cost'] ?? 0), (array)$wallet['purchases']));
    $wallet['updated_at'] = date(DATE_ATOM);
    return $wallet;
}

/**
 * RMW jednoho klíče peněženky: $mutate(array $wallet): array dostane AKTUÁLNÍ (v zámku čerstvě
 * načtenou) peněženku a vrací novou – žádné volání nesmí počítat s peněženkou načtenou mimo zámek,
 * jinak by souběžný zápis druhého procesu mohl být přepsán (ztracený update).
 */
function pts53_save_wallet(string $classId, string $studentKey, callable $mutate): array
{
    return (array)storage_map_update(pts53_path(), pts53_key($classId, $studentKey), static function (?array $current) use ($mutate): array {
        $wallet = pts53_wallet_totals($mutate(is_array($current) ? array_replace(['earned' => 0, 'spent' => 0, 'awards' => [], 'purchases' => []], $current) : ['earned' => 0, 'spent' => 0, 'awards' => [], 'purchases' => []]));
        return $wallet;
    });
}

/** Přidělí body za konkrétní věc. Opakované volání se stejným klíčem drží nejvyšší hodnotu. */
function pts53_award(string $classId, string $studentKey, string $key, int $points, string $reason = ''): int
{
    if ($studentKey === '' || $key === '') return 0;
    $points = max(0, min(10, $points));
    $wallet = pts53_wallet($classId, $studentKey);
    $current = (int)($wallet['awards'][$key]['points'] ?? 0);
    if ($points <= $current) return $current;
    $result = $points;
    // Rozhodnutí i zápis proběhnou pod jedním zámkem (souběžné body se neztratí).
    storage_update(pts53_path(), static function (array $all) use ($classId, $studentKey, $key, $points, $reason, &$result): array {
        $wallet = pts53_wallet_row($all, $classId, $studentKey);
        $current = (int)($wallet['awards'][$key]['points'] ?? 0);
        if ($points <= $current) { $result = $current; return $all; }
        $wallet['awards'][$key] = ['points' => $points, 'reason' => $reason, 'at' => date(DATE_ATOM)];
        $all[pts53_key($classId, $studentKey)] = pts53_wallet_totals($wallet);
        return $all;
    });
    return $result;
}

function pts53_purchase(string $classId, string $studentKey, string $key, int $cost, string $reason = ''): bool
{
    $wallet = pts53_wallet($classId, $studentKey);
    if (isset($wallet['purchases'][$key])) return true;
    if (pts53_balance($classId, $studentKey) < $cost) return false;
    $ok = false;
    // Zůstatek se ověřuje znovu uvnitř zámku – dva souběžné nákupy neutratí body dvakrát.
    storage_update(pts53_path(), static function (array $all) use ($classId, $studentKey, $key, $cost, $reason, &$ok): array {
        $wallet = pts53_wallet_row($all, $classId, $studentKey);
        if (isset($wallet['purchases'][$key])) { $ok = true; return $all; }
        if (max(0, (int)$wallet['earned'] - (int)$wallet['spent']) < $cost) { $ok = false; return $all; }
        $wallet['purchases'][$key] = ['cost' => $cost, 'reason' => $reason, 'at' => date(DATE_ATOM)];
        $all[pts53_key($classId, $studentKey)] = pts53_wallet_totals($wallet);
        $ok = true;
        return $all;
    });
    return $ok;
}

function pts53_owns(string $classId, string $studentKey, string $key): bool
{
    $wallet = pts53_wallet($classId, $studentKey);
    return isset($wallet['purchases'][$key]);
}

function pts53_history(string $classId, string $studentKey, int $limit = 12): array
{
    $wallet = pts53_wallet($classId, $studentKey);
    $rows = [];
    foreach ((array)$wallet['awards'] as $key => $a) $rows[] = ['at' => (string)($a['at'] ?? ''), 'text' => (string)($a['reason'] ?? $key), 'delta' => (int)($a['points'] ?? 0)];
    foreach ((array)$wallet['purchases'] as $key => $p) $rows[] = ['at' => (string)($p['at'] ?? ''), 'text' => (string)($p['reason'] ?? $key), 'delta' => -(int)($p['cost'] ?? 0)];
    usort($rows, static fn(array $a, array $b): int => strcmp($b['at'], $a['at']));
    return array_slice($rows, 0, $limit);
}

/**
 * Nápovědy k úkolu: první je zdarma, další dvě za body.
 * Text vzniká z titulků ukázky a z řešení, takže vždy sedí na konkrétní úkol.
 */
function pts53_hints(string $scene): array
{
    $meta = tut52_scene_meta($scene);
    $caps = array_values((array)$meta['caption']);
    $ex = tut52_exercise($scene);
    return [
        ['level' => 1, 'cost' => 0, 'label' => tr('Nápověda zdarma'), 'text' => (string)($caps[1] ?? $caps[0] ?? tr('Projdi si znovu ukázku po krocích.'))],
        ['level' => 2, 'cost' => PTS53_HINT_COST, 'label' => tr('Extra nápověda'), 'text' => trim((string)($caps[2] ?? '') . ' ' . (string)($caps[3] ?? '')) ?: tr('Zaměř se na poslední krok ukázky.')],
        ['level' => 3, 'cost' => PTS53_HINT_COST, 'label' => tr('Ukázat řešení'), 'text' => tut52_exercise_solution($ex)],
    ];
}

/** POST z tutoriálu: odemkne nápovědu (první zdarma, další za 2 body). */
function pts53_handle_hint_post(string $classId): never
{
    header('Content-Type: application/json; charset=utf-8');
    $studentKey = adaptive_student_key($classId);
    $scene = preg_replace('/[^a-z]/', '', (string)($_POST['scene'] ?? '')) ?? '';
    $exercise = preg_replace('/[^A-Za-z0-9:_-]/', '', (string)($_POST['exercise'] ?? '')) ?? '';
    $level = max(1, min(3, (int)($_POST['level'] ?? 1)));
    $hints = $scene !== '' ? pts53_hints($scene) : [];
    $hint = $hints[$level - 1] ?? null;
    if ($studentKey === '' || !$hint) { http_response_code(422); echo json_encode(['ok' => false, 'error' => tr('Nápověda není dostupná.')]); exit; }
    $key = 'hint:' . $exercise . ':' . $level;
    if ((int)$hint['cost'] > 0 && !pts53_owns($classId, $studentKey, $key)) {
        if (!pts53_purchase($classId, $studentKey, $key, (int)$hint['cost'], $hint['label'] . ' · ' . $scene)) {
            http_response_code(402);
            echo json_encode(['ok' => false, 'error' => tr('Nemáš dost bodů. Získej je vyřešením úkolů nebo známkou za práci.'), 'balance' => pts53_balance($classId, $studentKey)], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    echo json_encode([
        'ok' => true, 'level' => $level, 'label' => (string)$hint['label'], 'text' => (string)$hint['text'],
        'cost' => (int)$hint['cost'], 'balance' => pts53_balance($classId, $studentKey),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
