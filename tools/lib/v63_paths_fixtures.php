<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v63 · fixture pro tools/v63_paths_audit.php (vymyšlená jména „Audit …“, jen dočasné úložiště).
 *
 * v63fx_seed() zapíše soupis tříd 3.A a 1.A (čtyři fiktivní žáci v každé) a postaví identitu (student_id).
 * v63fx_answers() sestaví správné (nebo záměrně špatné) odpovědi na otázky kroku, v63fx_complete_steps() projde kroky
 * cesty až po zadaný krok jako žák (volá p63_submit_step, takže ověřuje skutečný tok včetně důkazů).
 */

const V63FX_LABELS = [
    'class_3a' => ['a' => 'Audit Cesta Jedna', 'b' => 'Audit Cesta Dva', 'c' => 'Audit Cesta Tri', 'd' => 'Audit Cesta Ctyri'],
    'class_1a' => ['a' => 'Audit Grafik Jedna', 'b' => 'Audit Grafik Dva', 'c' => 'Audit Grafik Tri', 'd' => 'Audit Grafik Ctyri'],
];

function v63fx_key(string $classId, string $who): string
{
    return project_student_key($classId, V63FX_LABELS[$classId][$who]);
}

function v63fx_sid(string $classId, string $who): string
{
    return (string)p63_student_id($classId, v63fx_key($classId, $who));
}

/** Zapíše soupis obou tříd a postaví identitu. */
function v63fx_seed(string $tmp): void
{
    foreach (['intake_v51.php', 'identity_v58.php'] as $lib) require_once dirname(__DIR__, 2) . '/' . $lib;
    @mkdir($tmp . '/intake', 0700, true);
    $rows = [];
    $i = 0;
    foreach (V63FX_LABELS as $classId => $labels) {
        foreach ($labels as $label) {
            [$first, $last] = explode(' ', $label, 2);
            $rows[] = ['id' => 'fx63_' . $i++, 'class_id' => $classId, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''],
                'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
        }
    }
    file_put_contents($tmp . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rows, JSON_UNESCAPED_UNICODE));
    unset($GLOBALS['educanet_runtime_indexes']);
    identity58_build(false);
}

/** Odpovědi na otázky kroku: správné, nebo (pokud $right = false) všechny špatně. @param list<string> $ids @return array<int,string> */
function v63fx_answers(array $ids, bool $right, int $wrongFrom = 0): array
{
    $out = [];
    foreach ($ids as $i => $id) {
        $q = p63_question($id);
        $correct = $q === null ? '' : ($q['type'] === 'bool' ? ($q['answer'] ? '1' : '0') : (string)$q['answer']);
        $good = $right && $i >= $wrongFrom ? true : false;
        if ($good) { $out[$i] = $correct; continue; }
        if ($q['type'] === 'bool') $out[$i] = $correct === '1' ? '0' : '1';
        elseif ($q['type'] === 'numeric') $out[$i] = (string)((float)$q['answer'] + 17);
        else $out[$i] = (string)(array_values(array_filter($q['options'], static fn(string $o): bool => $o !== $correct))[0] ?? 'x');
    }
    return $out;
}

/** Vstup pro krok podle jeho typu; $right = správně. */
function v63fx_input(string $classId, string $who, array $path, array $step, bool $right = true): array
{
    $sid = v63fx_sid($classId, $who);
    $state = p63_state($sid);
    $attempt = p63_step_entry($state, (string)$path['id'], (string)$step['id'])['attempts'] + 1;
    switch ((string)$step['type']) {
        case 'retrieval':
        case 'verify':
            return ['a' => v63fx_answers(p63_step_question_ids($path, $step, $sid, $state, $attempt), $right)];
        case 'parsons':
            $order = $right ? range(0, count((array)$step['lines']) - 1) : array_reverse(range(0, count((array)$step['lines']) - 1));
            return ['order' => implode(',', $order), 'sig' => p63_parsons_sign($order, (string)$path['id'], (string)$step['id'], $attempt, p63_parsons_secret())];
        case 'pre':
            $cases = array_values((array)$step['cases']);
            $case = (array)$cases[p63_current_variant($path, $step, $sid, $state)];
            $wrong = (int)$case['correct'] === 0 ? 1 : 0;
            return ['choice' => $right ? (int)$case['correct'] : $wrong];
        default:
            return [];
    }
}

/** Projde kroky cesty (správně) až PŘED krok $until (nebo všechny kromě reflexe, když $until = ''). @return list<array<string,mixed>> výsledky */
function v63fx_complete_steps(string $classId, string $who, string $pathId, string $until = '', ?int $now = null): array
{
    $path = (array)p63_path_for_class($classId, $pathId);
    $results = [];
    foreach ((array)$path['steps'] as $step) {
        if ((string)$step['id'] === $until || ($until === '' && (string)$step['type'] === 'reflect')) break;
        $results[] = p63_submit_step($classId, v63fx_key($classId, $who), $pathId, (string)$step['id'], v63fx_input($classId, $who, $path, $step), $now);
    }
    return $results;
}
