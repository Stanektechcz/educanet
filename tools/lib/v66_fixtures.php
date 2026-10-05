<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · fixtura položkové analýzy pro tools/v66_assessment_audit.php (vymyšlení žáci „Audit Žák NN“, jen dočasné úložiště).
 *
 * 40 žáků s pořadím schopnosti s = 0…39 odpovídá na 11 otázek reálného modulu 3.A:
 *   F1–F6 (otázky 5–10)  „výplň“ – správně, když s ≥ 5, 10, 15, 20, 25, 30 (utváří součet ostatních otázek),
 *   A (otázka 0)  správně s ≥ 4            → p = 0,9 (36 ze 40), korelace kladná,
 *   B (otázka 1)  správně s ≥ 20           → silně rozlišuje (r > 0,5),
 *   C (otázka 2)  správně, když (s mod 4) < 2 → nerozlišuje (|r| < 0,1),
 *   D (otázka 3)  správně s ≥ 14, špatné odpovědi jen na dvě možnosti → třetí špatná možnost nikdy (0×),
 *   E (otázka 4)  s ≥ 30 volí špatnou možnost „horních“, 10 ≤ s < 30 správně, s < 10 jinou špatnou → špatná možnost s kladnou korelací.
 * Tři žáci (s = 11, 22, 33) odevzdají test za 30 s (< 5 s na otázku), ostatní za 330 s.
 */

const V66FX_CLASS = 'class_3a';
const V66FX_COUNT = 40;
const V66FX_ITEMS = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4];
const V66FX_FILLERS = [5 => 5, 6 => 10, 7 => 15, 8 => 20, 9 => 25, 10 => 30];
const V66FX_FAST = [11, 22, 33];

function v66fx_label(int $s): string
{
    return 'Audit Žák ' . str_pad((string)($s + 1), 2, '0', STR_PAD_LEFT);
}

/** Klíče možností otázky: [správná, špatné…] podle pořadí v modulu. @return array{correct:string,wrong:list<string>} */
function v66fx_options(array $question): array
{
    $correct = (string)$question['correct'];
    return ['correct' => $correct, 'wrong' => array_values(array_filter(array_map('strval', array_keys($question['options'])), static fn(string $k): bool => $k !== $correct))];
}

/** Odpověď žáka s pořadím $s na otázku $q: [správně?, zvolená možnost]. @return array{0:bool,1:string} */
function v66fx_answer(string $item, int $s, array $opt): array
{
    $ok = match ($item) {
        'A' => $s >= 4, 'B' => $s >= 20, 'C' => ($s % 4) < 2, 'D' => $s >= 14, 'E' => $s >= 10 && $s < 30,
        default => false,
    };
    if ($ok) return [true, $opt['correct']];
    $w = $opt['wrong'];
    if ($item === 'D') return [false, $s % 2 === 1 ? $w[0] : $w[1]];
    if ($item === 'E') return [false, $s >= 30 ? $w[1] : $w[0]];
    return [false, $w[0]];
}

/** Řádky pro proud practice_results (jeden pokus každého ze 40 žáků). @return list<array<string,mixed>> */
function v66fx_rows(array $questions, int $now): array
{
    $rows = [];
    for ($s = 0; $s < V66FX_COUNT; $s++) {
        $answers = [];
        $score = 0;
        $all = V66FX_ITEMS + array_combine(array_map(static fn(int $i): string => 'F' . $i, array_keys(V66FX_FILLERS)), array_keys(V66FX_FILLERS));
        foreach ($all as $item => $qi) {
            $q = $questions[$qi];
            $opt = v66fx_options($q);
            if (isset(V66FX_FILLERS[$qi])) { $ok = $s >= V66FX_FILLERS[$qi]; $sel = $ok ? $opt['correct'] : $opt['wrong'][0]; }
            else [$ok, $sel] = v66fx_answer((string)$item, $s, $opt);
            $score += $ok ? 1 : 0;
            $answers[] = ['question_id' => (string)$q['id'], 'selected' => $sel, 'correct' => $ok];
        }
        $dur = in_array($s, V66FX_FAST, true) ? 30 : 330;
        $end = $now - 3600 * 24 + $s * 60;
        $rows[] = ['id' => 'fx66_' . $s, 'class_id' => V66FX_CLASS, 'student_label' => v66fx_label($s), 'student_email' => '', 'auth_key' => '', 'google_sub' => '',
            'started_at' => date(DATE_ATOM, $end - $dur), 'finished_at' => date(DATE_ATOM, $end), 'score' => $score, 'max_score' => count($answers), 'answers' => $answers];
    }
    return $rows;
}

/** Nezávislý výpočet p a korigované r (kovariance) pro kontrolu modulu: položka => [p, r, n]. @param list<array<string,mixed>> $rows */
function v66fx_expected(array $rows): array
{
    $byItem = [];
    foreach ($rows as $row) {
        $total = 0;
        foreach ($row['answers'] as $a) $total += $a['correct'] ? 1 : 0;
        foreach ($row['answers'] as $a) $byItem[$a['question_id']][] = [$a['correct'] ? 1.0 : 0.0, (float)($total - ($a['correct'] ? 1 : 0))];
    }
    $out = [];
    foreach ($byItem as $id => $pairs) {
        $n = count($pairs);
        $mx = array_sum(array_column($pairs, 0)) / $n;
        $my = array_sum(array_column($pairs, 1)) / $n;
        $cov = 0.0; $vx = 0.0; $vy = 0.0;
        foreach ($pairs as [$x, $y]) { $cov += ($x - $mx) * ($y - $my); $vx += ($x - $mx) ** 2; $vy += ($y - $my) ** 2; }
        $out[$id] = ['p' => $mx, 'r' => $vx > 0 && $vy > 0 ? $cov / sqrt($vx * $vy) : null, 'n' => $n];
    }
    return $out;
}
