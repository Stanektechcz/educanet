<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v65 · Peer review (dobrovolné, jen plné projekty): 2 recenzenti na práci, při nedodání stačí 1, při < 3 odevzdáních se přeskakuje.
 *
 * Pravidla (rozhodnutí školy): recenzent je pro autora anonymní (view model autora neobsahuje identitu ani id recenze),
 * učitel recenzenta vidí a texty moderuje; autor vidí text až po schválení. Peer review NIKDY neovlivní skóre,
 * známku ani důkaz kompetence – číselné úrovně recenzenta slouží jen ke kalibraci a k informativnímu průměru pro učitele.
 * Sidecar storage/projects_v65_peer.json.php = {id recenze => řádek}.
 */

const PROJ65_REVIEWERS_PER_WORK = 2;
const PROJ65_REVIEW_TEXT_MIN = 20;
const PROJ65_REVIEW_TEXT_MAX = 400;
const PROJ65_PEARSON_MIN_PAIRS = 6;

function proj65_review_id(string $ref, string $reviewerKey): string
{
    return 'r65_' . substr(hash('sha256', $ref . '|' . $reviewerKey), 0, 16);
}

/** Klíče žáků, kteří odevzdali práci projektu a smějí recenzovat. @return list<string> */
function proj65_peer_pool(array $cycle, string $classId, string $projectId): array
{
    $pool = [];
    foreach ($cycle as $row) {
        if (!is_array($row) || (string)$row['class_id'] !== $classId || (string)$row['project_id'] !== $projectId) continue;
        if (!in_array((string)$row['state'], ['submitted', 'peer_review', 'graded', 'portfolio'], true)) continue;
        foreach (proj65_member_keys($row) as $k) $pool[$k] = true;
    }
    $keys = array_keys($pool);
    sort($keys, SORT_STRING);
    return $keys;
}

/** Recenzent nikdy nehodnotí sebe ani člena vlastního týmu. */
function proj65_peer_forbidden(string $reviewerKey, array $authorMembers): bool
{
    return $reviewerKey === '' || in_array($reviewerKey, array_map('strval', $authorMembers), true);
}

/**
 * Deterministické přidělení (stejný vstup → stejný výsledek, nejméně vytížení první, ties podle hashe). Existující přidělení se nemění.
 * @param array<string,list<string>> $authors ref → členové autora @param list<string> $pool
 */
function proj65_peer_assign(array $peer, array $authors, array $pool, string $classId, string $projectId): array
{
    $load = [];
    $have = [];
    foreach ($peer as $r) {
        if (!is_array($r)) continue;
        $load[(string)$r['reviewer_key']] = ($load[(string)$r['reviewer_key']] ?? 0) + 1;
        $have[(string)$r['ref']][(string)$r['reviewer_key']] = true;
    }
    $seed = $classId . '|' . $projectId;
    $refs = array_keys($authors);
    usort($refs, static fn(string $a, string $b): int => strcmp(hash('sha256', $seed . '|' . $a), hash('sha256', $seed . '|' . $b)));
    foreach ($refs as $ref) {
        $needed = PROJ65_REVIEWERS_PER_WORK - count($have[$ref] ?? []);
        $cands = array_values(array_filter($pool, static fn(string $k): bool => !proj65_peer_forbidden($k, $authors[$ref]) && !isset($have[$ref][$k])));
        usort($cands, static fn(string $a, string $b): int => ($load[$a] ?? 0) <=> ($load[$b] ?? 0)
            ?: strcmp(hash('sha256', $seed . '|' . $ref . '|' . $a), hash('sha256', $seed . '|' . $ref . '|' . $b)));
        foreach (array_slice($cands, 0, max(0, $needed)) as $key) {
            $id = proj65_review_id($ref, $key);
            $peer[$id] = ['id' => $id, 'ref' => $ref, 'class_id' => $classId, 'project_id' => $projectId, 'reviewer_key' => $key, 'levels' => null,
                'strength' => '', 'suggestion' => '', 'state' => 'assigned', 'created_at' => date(DATE_ATOM)];
            $load[$key] = ($load[$key] ?? 0) + 1;
        }
    }
    return $peer;
}

/** Přidělené recenze žáka (s kontextem práce, bez identity autora). @return list<array<string,mixed>> */
function proj65_reviews_for_reviewer(string $classId, string $studentKey): array
{
    $cycle = proj65_all();
    $out = [];
    foreach (storage_read(proj65_path('peer'), false) as $r) {
        if (!is_array($r) || (string)$r['class_id'] !== $classId || (string)$r['reviewer_key'] !== $studentKey) continue;
        $work = is_array($cycle[$r['ref']] ?? null) ? $cycle[$r['ref']] : null;
        if ($work === null || proj65_peer_forbidden($studentKey, proj65_member_keys($work))) continue;
        $out[] = $r + ['work' => ['title' => (string)$work['title'], 'url' => (string)($work['submission']['url'] ?? ''), 'note' => (string)($work['submission']['note'] ?? '')]];
    }
    usort($out, static fn(array $a, array $b): int => strcmp((string)$a['id'], (string)$b['id']));
    return $out;
}

/** @return array{ok:bool,error:?string} */
function proj65_review_save(string $reviewId, string $reviewerKey, mixed $rawLevels, string $strength, string $suggestion): array
{
    $strength = proj65_text($strength, PROJ65_REVIEW_TEXT_MAX);
    $suggestion = proj65_text($suggestion, PROJ65_REVIEW_TEXT_MAX);
    if (u_strlen($strength) < PROJ65_REVIEW_TEXT_MIN || u_strlen($suggestion) < PROJ65_REVIEW_TEXT_MIN) return ['ok' => false, 'error' => 'text_short'];
    $out = ['ok' => false, 'error' => 'not_found'];
    $flagged = project_feedback_flagged($strength . ' ' . $suggestion);
    storage_update(proj65_path('peer'), static function (array $peer) use ($reviewId, $reviewerKey, $rawLevels, $strength, $suggestion, $flagged, &$out): array {
        $r = is_array($peer[$reviewId] ?? null) ? $peer[$reviewId] : null;
        if ($r === null || (string)$r['reviewer_key'] !== $reviewerKey) return $peer;
        if (!in_array((string)$r['state'], ['assigned', 'submitted', 'flagged'], true)) { $out = ['ok' => false, 'error' => 'locked']; return $peer; }
        $work = proj65_get((string)$r['ref']);
        $rubric = $work !== null ? proj65_rubric_for($work) : null;
        if ($work === null || $rubric === null || proj65_peer_forbidden($reviewerKey, proj65_member_keys($work))) { $out = ['ok' => false, 'error' => 'forbidden']; return $peer; }
        $levels = proj65_levels_from_input($rubric, $rawLevels);
        if (in_array(null, $levels, true)) { $out = ['ok' => false, 'error' => 'level_range']; return $peer; }
        $peer[$reviewId] = array_replace($r, ['levels' => $levels, 'strength' => $strength, 'suggestion' => $suggestion, 'state' => $flagged ? 'flagged' : 'submitted', 'submitted_at' => date(DATE_ATOM)]);
        $out = ['ok' => true, 'error' => null];
        return $peer;
    });
    return $out;
}

/** Moderace učitelem: approve → autor text uvidí, reject → nikdy. Rozsah třídy ověřuje $canClass. */
function proj65_review_moderate(string $reviewId, string $decision, callable $canClass, string $by): array
{
    $out = ['ok' => false, 'error' => 'not_found'];
    if (!in_array($decision, ['approve', 'reject'], true)) return ['ok' => false, 'error' => 'invalid'];
    storage_update(proj65_path('peer'), static function (array $peer) use ($reviewId, $decision, $canClass, $by, &$out): array {
        $r = is_array($peer[$reviewId] ?? null) ? $peer[$reviewId] : null;
        if ($r === null) return $peer;
        if (!$canClass((string)$r['class_id'])) { $out = ['ok' => false, 'error' => 'class_out_of_scope']; return $peer; }
        if (!in_array((string)$r['state'], ['submitted', 'flagged', 'approved', 'rejected'], true)) { $out = ['ok' => false, 'error' => 'not_submitted']; return $peer; }
        $peer[$reviewId] = array_replace($r, ['state' => $decision === 'approve' ? 'approved' : 'rejected', 'moderated_by' => $by, 'moderated_at' => date(DATE_ATOM)]);
        $out = ['ok' => true, 'error' => null];
        return $peer;
    });
    return $out;
}

/** View model pro AUTORA: jen schválené recenze, bez identity recenzenta, id recenze i času (nejde dohledat podle pořadí). */
function proj65_peer_author_view(string $ref): array
{
    $rows = [];
    foreach (storage_read(proj65_path('peer'), false) as $r) {
        if (is_array($r) && (string)$r['ref'] === $ref && (string)$r['state'] === 'approved') $rows[] = $r;
    }
    usort($rows, static fn(array $a, array $b): int => strcmp(hash('sha256', $ref . (string)$a['id']), hash('sha256', $ref . (string)$b['id'])));
    return array_map(static fn(array $r): array => ['strength' => (string)$r['strength'], 'suggestion' => (string)$r['suggestion'], 'levels' => (array)$r['levels']], $rows);
}

/** Pearsonův koeficient; null při méně než PROJ65_PEARSON_MIN_PAIRS párech nebo nulovém rozptylu. */
function proj65_pearson(array $xs, array $ys): ?float
{
    $n = count($xs);
    if ($n !== count($ys) || $n < PROJ65_PEARSON_MIN_PAIRS) return null;
    $mx = array_sum($xs) / $n;
    $my = array_sum($ys) / $n;
    $sxy = $sxx = $syy = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $dx = (float)$xs[$i] - $mx;
        $dy = (float)$ys[$i] - $my;
        $sxy += $dx * $dy;
        $sxx += $dx * $dx;
        $syy += $dy * $dy;
    }
    return $sxx > 0.0 && $syy > 0.0 ? $sxy / sqrt($sxx * $syy) : null;
}

/**
 * Kalibrace recenzenta vůči učiteli z párů (úroveň recenzenta, úroveň učitele) po kritériích.
 * Odchylka: nějaká dvojice se liší o ≥ 2 úrovně, nebo průměrná absolutní chyba > 1. Zkalibrovaný = ≥ 6 párů, bez odchylky, r ≥ 0,6.
 * @param list<array{0:int,1:int}> $pairs
 * @return array{n:int,pearson:?float,mae:?float,deviation:bool,calibrated:bool,weight:float}
 */
function proj65_calibration(array $pairs): array
{
    $n = count($pairs);
    if ($n === 0) return ['n' => 0, 'pearson' => null, 'mae' => null, 'deviation' => false, 'calibrated' => false, 'weight' => 0.5];
    $xs = array_map(static fn(array $p): int => (int)$p[0], $pairs);
    $ys = array_map(static fn(array $p): int => (int)$p[1], $pairs);
    $abs = array_map(static fn(int $x, int $y): int => abs($x - $y), $xs, $ys);
    $mae = array_sum($abs) / $n;
    $deviation = max($abs) >= 2 || $mae > 1.0;
    $r = proj65_pearson($xs, $ys);
    $calibrated = $r !== null && $r >= 0.6 && !$deviation;
    return ['n' => $n, 'pearson' => $r === null ? null : round($r, 3), 'mae' => round($mae, 3), 'deviation' => $deviation, 'calibrated' => $calibrated, 'weight' => $calibrated ? 1.0 : 0.5];
}

/** Páry recenzenta ze všech jeho recenzí k pracím s publikovaným hodnocením učitele (poslední verze). */
function proj65_reviewer_pairs(string $reviewerKey, ?array $peer = null, ?array $cycle = null): array
{
    $peer ??= storage_read(proj65_path('peer'), false);
    $cycle ??= proj65_all();
    $pairs = [];
    foreach ($peer as $r) {
        if (!is_array($r) || (string)$r['reviewer_key'] !== $reviewerKey || !is_array($r['levels'] ?? null)) continue;
        $versions = array_values(array_filter((array)($cycle[$r['ref']]['versions'] ?? []), 'is_array'));
        if ($versions === []) continue;
        $teacher = (array)$versions[count($versions) - 1]['levels'];
        foreach ($r['levels'] as $crit => $lvl) {
            if (isset($teacher[$crit])) $pairs[] = [(int)$lvl, (int)$teacher[$crit]];
        }
    }
    return $pairs;
}

/** Přehled pro učitele: recenze třídy s identitou recenzenta (štítek), stavem, kalibrací a vážením. @return list<array<string,mixed>> */
function proj65_peer_teacher_rows(string $classId): array
{
    $peer = storage_read(proj65_path('peer'), false);
    $cycle = proj65_all();
    $cal = [];
    $out = [];
    foreach ($peer as $r) {
        if (!is_array($r) || (string)$r['class_id'] !== $classId) continue;
        $key = (string)$r['reviewer_key'];
        $cal[$key] ??= proj65_calibration(proj65_reviewer_pairs($key, $peer, $cycle));
        $out[] = $r + ['calibration' => $cal[$key], 'work_title' => (string)($cycle[$r['ref']]['title'] ?? '')];
    }
    usort($out, static fn(array $a, array $b): int => ((string)$a['state'] === 'flagged' ? 0 : 1) <=> ((string)$b['state'] === 'flagged' ? 0 : 1) ?: strcmp((string)$a['id'], (string)$b['id']));
    return $out;
}
