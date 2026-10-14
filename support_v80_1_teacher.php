<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/support_v80_1.php';

/**
 * EDUCANET v80.1 · POST akce učitele modulu `mapa801` (Mapa třídy). CSRF, politiku teacher59 (třída povinná a v rozsahu; fráze jen vlastní
 * záznam učitele) a oprávnění (prefix ts801_ = students.manage → asistent nic neodešle) ověřuje teacher.php před voláním.
 * Handler ověří třídu znovu a žáky jen ze seznamu třídy (klíče z POST, nikdy z URL). Hlášky jsou česky (cockpit), bez jmen žáků.
 */

function ts801_post_str(string $key, int $max = 80): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? mb_substr($v, 0, $max) : '';
}

/** Třída z POST, jen v rozsahu učitele a se žáky (jinak výjimka). */
function ts801_post_class(): string
{
    $classId = ts801_post_str('class_id', 20);
    if (preg_match(TS801_CLASS_RE, $classId) !== 1 || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Třída není ve vašem rozsahu.');
    return $classId;
}

/** Klíče vybraných žáků z POST students[] → [klíč => ref] (jen žáci ze seznamu třídy). */
function ts801_post_students(string $classId): array
{
    $keys = is_array($_POST['students'] ?? null) ? array_values(array_filter($_POST['students'], 'is_string')) : [];
    return ts801_student_refs($classId, array_slice($keys, 0, TS801_SELECT_MAX * 2));
}

/** Odkud vzít text: vlastní text (pole „text“) má přednost, jinak vybraná fráze. @return array{0:string,1:string} [druh, text] */
function ts801_post_message(string $teacherHash): array
{
    $text = ts801_clean_text(ts801_post_str('text', TS801_TEXT_MAX * 2), TS801_TEXT_MAX);
    $kind = ts801_post_str('druh', 10);
    $phraseId = ts801_post_str('fraze', 40);
    $phrase = null;
    foreach (ts801_phrases($teacherHash) as $k => $list) foreach ($list as $p) if ($p['id'] === $phraseId) $phrase = ['k' => $k, 't' => $p['t']];
    if ($text === '' && $phrase !== null) return [(string)$phrase['k'], (string)$phrase['t']];
    if (!isset(TS801_KINDS[$kind])) $kind = $phrase !== null ? (string)$phrase['k'] : 'next';
    return [$kind, $text];
}

function ts801_plural(int $n, string $one, string $few, string $many): string
{
    return $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
}

/** POST ts801_* (teacher58_handle_post podle prefixu). */
function ts801_teacher_handle_post(string $action): void
{
    if (!in_array($action, TS801_ACTIONS, true)) throw new RuntimeException('Neznámá akce.');
    $teacher = ts801_teacher_hash();
    if ($action === 'ts801_phrase_add' || $action === 'ts801_phrase_delete') {
        $ok = $action === 'ts801_phrase_add' ? ts801_phrase_add($teacher, ts801_post_str('druh', 10), ts801_post_str('text', TS801_PHRASE_MAX * 2)) : ts801_phrase_delete($teacher, ts801_post_str('id', 40));
        $msg = $action === 'ts801_phrase_add' ? ($ok ? 'Fráze je uložená mezi vašimi frázemi.' : 'Frázi se nepodařilo přidat (stejná už existuje, nebo máte ' . TS801_OWN_PHRASES_MAX . ' vlastních frází).') : ($ok ? 'Fráze je smazaná.' : 'Fráze nebyla nalezena.');
        $_SESSION['teacher_export_flash'] = ['type' => $ok ? 'ok' : 'error', 'message' => $msg];
        $class = preg_match(TS801_CLASS_RE, ts801_post_str('class_id', 20)) === 1 ? ts801_post_str('class_id', 20) : '';
        teacher_redirect(['tab' => 'mapa801'] + ($class !== '' ? ['class' => $class] : []));
    }
    $classId = ts801_post_class();
    $back = ['tab' => 'mapa801', 'class' => $classId];
    $msg = '';
    $type = 'ok';
    switch ($action) {
        case 'ts801_select':
            $refs = ts801_post_students($classId);
            $_SESSION['ts801_sel'] = [$classId => array_keys($refs)];
            $msg = $refs === [] ? 'Nikdo nebyl vybrán.' : 'Vybráno ' . count($refs) . ' ' . ts801_plural(count($refs), 'žák', 'žáci', 'žáků') . ' – níže zvolte zpětnou vazbu nebo procvičování.';
            break;
        case 'ts801_feedback_send':
            $refs = ts801_post_students($classId);
            [$kind, $text] = ts801_post_message($teacher);
            $n = ts801_feedback_send($classId, $refs, $kind, $text, $teacher);
            unset($_SESSION['ts801_sel']);
            $msg = 'Zpětná vazba (' . mb_strtolower(TS801_KINDS[$kind]) . ') odeslána ' . $n . ' ' . ts801_plural($n, 'žákovi', 'žákům', 'žákům') . '. Žák ji uvidí v Můj pokrok.';
            break;
        case 'ts801_assign':
            $refs = ts801_post_students($classId);
            $res = ts801_assign($classId, $refs, ts801_post_str('polozka', 40), $teacher);
            unset($_SESSION['ts801_sel']);
            $msg = 'Procvičování přiděleno ' . $res['assigned'] . ' ' . ts801_plural($res['assigned'], 'žákovi', 'žákům', 'žákům') . ' – žák ho uvidí jako Další krok „Od učitele“.'
                . ($res['skipped'] > 0 ? ' Přeskočeno ' . $res['skipped'] . ' (už mají ' . TS801_OPEN_MAX . ' nesplněných přidělení).' : '');
            break;
        case 'ts801_feedback_withdraw':
            $ok = ts801_feedback_withdraw($classId, ts801_post_str('id', 40));
            [$type, $msg] = $ok ? ['ok', 'Zpětná vazba je stažená – žák ji už neuvidí.'] : ['error', 'Zpráva nebyla nalezena.'];
            break;
        case 'ts801_assign_cancel':
            $ok = ts801_assign_cancel($classId, ts801_post_str('id', 40));
            [$type, $msg] = $ok ? ['ok', 'Přidělení je zrušené. Co už žák vyřešil, zůstává v jeho výsledcích.'] : ['error', 'Přidělení nebylo nalezeno.'];
            break;
    }
    $_SESSION['teacher_export_flash'] = ['type' => $type, 'message' => $msg];
    teacher_redirect($back);
}
