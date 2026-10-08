<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v72 · exit ticket lekce (formativní, nikdy nevstupuje do známky).
 *
 * Úložiště kvízových kroků v52 (tutorial_v52_scores) drží jen nejlepší body bez obsahu odpovědi, proto má exit ticket
 * minimální vlastní úložiště po třídách: storage/lesson_exit_v72_<třída>.json.php =
 *   {"v":1,"answers":{"<lekce>":{"<hash žáka>":{"v":varianta,"c":volba,"ok":0|1,"at":"ISO 8601"}}}}
 * Hash žáka = prvních 20 znaků SHA-256 z „lx72|“ + klíče žáka (žádné jméno ani e-mail). Jedna odpověď na lekci,
 * zápis jen přes storage_update. Retence: při každém zápisu se mažou odpovědi starší než LX72_RETENTION_DAYS (školní rok).
 *
 * Žák vidí exit ticket jen u schválené lekce (lc72_student_overlay) a jen do aktuální lekce podle kalendáře.
 * Variantu určuje server z identity v session (ne parametr požadavku), aby se sousedé lišili.
 * V pilotních třídách kompetencí (COMP62_PILOT_CLASSES) se zapíše důkaz v62 se zdrojem „lesson“ – ten podle
 * G66_MASTERY_SOURCES nevstupuje do návrhů hodnocení (formativní, LC72_EXIT_FORMATIVE_ONLY).
 */

const LX72_RETENTION_DAYS = 365;
const LX72_HASH_LEN = 20;

require_once __DIR__ . '/lesson_approval_v72.php';

function lx72_path(string $classId): string
{
    if (!lm71_class_ok($classId)) throw new InvalidArgumentException('Neplatná třída.');
    return STORAGE_DIR . '/lesson_exit_v72_' . $classId . '.json.php';
}

function lx72_student_hash(string $studentKey): string
{
    return substr(hash('sha256', 'lx72|' . $studentKey), 0, LX72_HASH_LEN);
}

/**
 * Normalizovaný exit ticket z overlaye (nebo null): competence, competence_label, variants[question, options, correct, explanation].
 * @return array{competence:string,competence_label:string,variants:list<array{question:string,options:list<string>,correct:int,explanation:string}>}|null
 */
function lx72_normalize(array $overlay): ?array
{
    $et = is_array($overlay['exit_ticket'] ?? null) ? $overlay['exit_ticket'] : null;
    if ($et === null) return null;
    $variants = [];
    foreach ((array)($et['variants'] ?? []) as $v) {
        if (!is_array($v)) continue;
        $options = array_values(array_filter(array_map(static fn($o): string => trim((string)$o), (array)($v['options'] ?? [])), static fn(string $o): bool => $o !== ''));
        $correct = (int)($v['correct'] ?? -1);
        if (trim((string)($v['question'] ?? '')) === '' || count($options) < 2 || $correct < 0 || $correct >= count($options)) continue;
        $variants[] = ['question' => trim((string)$v['question']), 'options' => $options, 'correct' => $correct, 'explanation' => trim((string)($v['explanation'] ?? ''))];
    }
    if ($variants === []) return null;
    return ['competence' => (string)($et['competence'] ?? ''), 'competence_label' => (string)($et['competence_label'] ?? ''), 'variants' => $variants];
}

/** Exit ticket, který smí vidět žák (jen schválená lekce). */
function lx72_ticket(string $classId, int $number): ?array
{
    $ov = lc72_student_overlay($classId, $number);
    return $ov === null ? null : lx72_normalize($ov);
}

/** Varianta pro žáka: deterministicky z identity a čísla lekce. */
function lx72_variant_index(string $studentKey, int $number, int $count): int
{
    return $count < 1 ? 0 : (int)(hexdec(substr(hash('sha256', 'lx72v|' . $studentKey . '|' . $number), 0, 7)) % $count);
}

/** Uložené odpovědi třídy (jen čtení). @return array<string,array<string,array>> lekce → hash žáka → odpověď */
function lx72_answers(string $classId): array
{
    if (!lm71_class_ok($classId)) return [];
    $data = storage_read(lx72_path($classId), false);
    return is_array($data['answers'] ?? null) ? $data['answers'] : [];
}

function lx72_answer_of(string $classId, string $studentKey, int $number): ?array
{
    $row = lx72_answers($classId)[(string)$number][lx72_student_hash($studentKey)] ?? null;
    return is_array($row) ? $row : null;
}

/** Odstraní odpovědi starší než retence (čistá funkce). */
function lx72_purge(array $answers, int $now): array
{
    $limit = $now - LX72_RETENTION_DAYS * 86400;
    foreach ($answers as $lesson => $rows) {
        $answers[$lesson] = array_filter((array)$rows, static fn($r): bool => is_array($r) && (int)strtotime((string)($r['at'] ?? '')) >= $limit);
        if ($answers[$lesson] === []) unset($answers[$lesson]);
    }
    return $answers;
}

/**
 * Odevzdá odpověď žáka. Identita ($classId, $studentKey) vždy ze session volajícího.
 * @return array{ok:bool,error:string,correct:bool,explanation:string,evidence:int}
 */
function lx72_submit(string $classId, string $studentKey, int $number, int $choice, ?int $now = null): array
{
    $now ??= time();
    $res = ['ok' => false, 'error' => '', 'correct' => false, 'explanation' => '', 'evidence' => 0];
    $ticket = lx72_ticket($classId, $number);
    if ($ticket === null || $studentKey === '') { $res['error'] = 'none'; return $res; }
    $vi = lx72_variant_index($studentKey, $number, count($ticket['variants']));
    $variant = $ticket['variants'][$vi];
    if ($choice < 0 || $choice >= count($variant['options'])) { $res['error'] = 'choice'; return $res; }
    $hash = lx72_student_hash($studentKey);
    $ok = $choice === $variant['correct'];
    $dup = false;
    storage_update(lx72_path($classId), static function (array $d) use ($number, $hash, $vi, $choice, $ok, $now, &$dup): array {
        $answers = lx72_purge(is_array($d['answers'] ?? null) ? $d['answers'] : [], $now);
        if (isset($answers[(string)$number][$hash])) { $dup = true; return $d; }
        $answers[(string)$number][$hash] = ['v' => $vi, 'c' => $choice, 'ok' => $ok ? 1 : 0, 'at' => date(DATE_ATOM, $now)];
        return ['v' => 1, 'answers' => $answers];
    });
    if ($dup) { $res['error'] = 'duplicate'; return $res; }
    $res = ['ok' => true, 'error' => '', 'correct' => $ok, 'explanation' => $variant['explanation'], 'evidence' => 0];
    $res['evidence'] = lx72_record_evidence($classId, $ticket['competence'], $number, $vi, $ok, $now);
    return $res;
}

/** Důkaz v62 v pilotních třídách (zdroj „lesson“, formativní). Selhání zápisu důkazu nesmí zablokovat odpověď. */
function lx72_record_evidence(string $classId, string $competency, int $number, int $variant, bool $ok, int $now): int
{
    if ($competency === '' || !function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId) || !function_exists('ev62_append')) return 0;
    if (!isset(comp62_competencies((string)comp62_subject_for_class($classId))[$competency])) return 0;
    $studentId = function_exists('identity58_current_student_id') ? identity58_current_student_id() : null;
    if ($studentId === null || !ev62_valid_id($studentId)) return 0;
    $level = (int)(comp62_competencies((string)comp62_subject_for_class($classId))[$competency]['level'] ?? 2);
    $row = ['competency' => $competency, 'level' => max(1, min(4, $level)), 'source' => 'lesson', 'score' => $ok ? 1.0 : 0.0,
        'at' => date(DATE_ATOM, $now), 'artefact_ref' => 'lx72:l' . str_pad((string)$number, 2, '0', STR_PAD_LEFT) . ':v' . ($variant + 1)];
    try {
        return (int)ev62_append($studentId, [$row])['added'];
    } catch (Throwable $e) {
        error_log('EDUCANET v72 exit ticket: důkaz v62 se nezapsal (' . get_class($e) . ')');
        return 0;
    }
}

/**
 * Souhrn pro učitele (bez jmen): kolik žáků odpovědělo, % správně, nejčastější chybná volba, kompetence, varianty.
 * $studentKeys = klíče žáků v seznamu třídy (odpovědi žáků mimo seznam se nepočítají).
 * @return array{has_ticket:bool,approved:bool,responded:int,students:int,correct:int,percent:?int,wrong_top:?array{text:string,count:int,variant:int},competence:string,variants:list<array{question:string,responded:int,correct:int}>}
 */
function lx72_summary(string $classId, int $number, array $studentKeys): array
{
    $ticket = lx72_normalize(lc72_overlay($classId, $number));
    $out = ['has_ticket' => $ticket !== null, 'approved' => lc72_status($classId, $number)['status'] === 'schvaleno', 'responded' => 0, 'students' => count($studentKeys),
        'correct' => 0, 'percent' => null, 'wrong_top' => null, 'competence' => '', 'variants' => []];
    if ($ticket === null) return $out;
    $out['competence'] = lx72_competence_label($classId, $ticket);
    foreach ($ticket['variants'] as $v) $out['variants'][] = ['question' => $v['question'], 'responded' => 0, 'correct' => 0];
    $hashes = array_flip(array_map('lx72_student_hash', array_map('strval', $studentKeys)));
    $wrong = [];
    foreach ((array)(lx72_answers($classId)[(string)$number] ?? []) as $hash => $row) {
        $vi = (int)($row['v'] ?? -1);
        if (!isset($hashes[(string)$hash]) || !isset($ticket['variants'][$vi])) continue;
        $out['responded']++;
        $out['variants'][$vi]['responded']++;
        if ((int)($row['ok'] ?? 0) === 1) { $out['correct']++; $out['variants'][$vi]['correct']++; continue; }
        $key = $vi . ':' . (int)($row['c'] ?? -1);
        $wrong[$key] = ($wrong[$key] ?? 0) + 1;
    }
    if ($out['responded'] > 0) $out['percent'] = (int)round($out['correct'] / $out['responded'] * 100);
    if ($wrong !== []) {
        arsort($wrong);
        [$vi, $ci] = array_map('intval', explode(':', (string)array_key_first($wrong)));
        $out['wrong_top'] = ['text' => (string)($ticket['variants'][$vi]['options'][$ci] ?? ''), 'count' => (int)reset($wrong), 'variant' => $vi + 1];
    }
    return $out;
}

/** Popis kompetence: katalog v62 (pilot), jinak popis z obsahu lekce. */
function lx72_competence_label(string $classId, array $ticket): string
{
    $id = (string)$ticket['competence'];
    if ($id !== '' && function_exists('comp62_competencies') && function_exists('comp62_subject_for_class')) {
        $label = (string)(comp62_competencies((string)comp62_subject_for_class($classId))[$id]['label'] ?? '');
        if ($label !== '') return $label;
    }
    return (string)$ticket['competence_label'];
}

/** Volitelná domácí příprava schválené lekce (pro žáka). @return list<array{text:string,minutes:int}> */
function lx72_homework(string $classId, int $number): array
{
    $ov = lc72_student_overlay($classId, $number);
    if ($ov === null) return [];
    $out = [];
    foreach ((array)($ov['homework'] ?? []) as $h) {
        $text = is_array($h) ? trim((string)($h['text'] ?? '')) : trim((string)$h);
        $minutes = is_array($h) ? (int)($h['minutes'] ?? 0) : 0;
        if ($text !== '' && $minutes <= LC72_HOMEWORK_MAX_MIN) $out[] = ['text' => $text, 'minutes' => $minutes];
    }
    return $out;
}
