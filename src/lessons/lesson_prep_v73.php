<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v73 · přípravný balíček hodiny (logika bez výstupu, JEN ČTENÍ): tisk, projektor a list pro zástup.
 *
 * Tři záložky cockpitu (`hodina_tisk`, `hodina_projektor`, `hodina_zastup`) čtou stejný model: třída a hodina jako v Dnešní hodině
 * (rozvrh + kalendář, obecné ?class= hlídá teacher59_guard_get_check, ?lesson= je jen číslo 1–28), obsah z jednotného modelu lm71,
 * kód hodiny z v53. Nic nezapisují, nemají vlastní POST akci ani GET parametr registru.
 *
 * Jména žáků (rozhodnutí školy 8. 10. 2026, bod 4): povolená s vypínačem – kde se žáci objeví (kontrolní list třídy na tisku),
 * jde popisek přes tp721_student_label() podle osobní volby učitele tp721_names_hidden(). Projektor ukazuje jen počty (veřejná
 * obrazovka), list pro zástup je záměrně bez jmen. Projektor nikdy neukazuje správné odpovědi (jen text otázek a zadání).
 * Kontakt na koordinaci zástupů je z konfigurace školy (EDUCANET_SUBSTITUTION_CONTACT / klíč substitution_contact v secrets),
 * nikdy osobní telefon z dat. Časově omezený přístup zastupujícího do systému NENÍ součástí v73 (čeká na rozhodnutí školy).
 */

const LP73_CONTACT_MAX = 160;
const LP73_PROJECTOR_TASKS_MAX = 6;
const LP73_PRINT_TASKS_MAX = 8;
const LP73_CONTACT_DEFAULT = 'Zástupce ředitele pro zástupy / kancelář školy (kontakt nastaví administrátor v konfiguraci EDUCANET_SUBSTITUTION_CONTACT).';

/** Parametry požadavku: třída (ověří rozsah lt70_classes) a číslo lekce 1–28 (0 = podle kalendáře). @return array{0:string,1:int} */
function lp73_request(): array
{
    $class = is_string($_GET['class'] ?? null) ? (string)$_GET['class'] : '';
    $lesson = is_string($_GET['lesson'] ?? null) && preg_match('/^\d{1,2}$/', (string)$_GET['lesson']) === 1 ? (int)$_GET['lesson'] : 0;
    return [$class, $lesson >= 1 && $lesson <= LT70_LESSONS_MAX ? $lesson : 0];
}

/** Model pro tisk, projektor a zástup (lehčí než Dnešní hodina: bez postupu žáků). */
function lp73_model(array $modules, string $requestedClass, int $lessonParam, ?int $now = null): array
{
    $now ??= time();
    $year = lt70_school_year();
    $today = date('Y-m-d', $now);
    $classes = lt70_classes($modules, $year);
    $classId = lt70_pick_class($classes, $requestedClass, $year, $today, date('H:i', $now));
    if ($classId === '') return ['class' => '', 'classes' => [], 'today' => $today];
    $module = is_array($modules[$classId] ?? null) ? $modules[$classId] : [];
    $slot = lt70_slot($year, $classId, $today, $lessonParam);
    $lesson = lm71_lesson($classId, $slot['lesson'], $module);
    if (function_exists('lc72_status') && (string)($lesson['meta']['status'] ?? '') !== 'puvodni') $lesson['meta']['status'] = lc72_status($classId, (int)$lesson['number'])['status'];
    $schedule = adaptive_class_schedule($year, $classId);
    $session = lt70_session($classId, $slot['is_today'] ? $today : '');
    return [
        'class' => $classId, 'classes' => $classes, 'today' => $today, 'now' => $now, 'module' => $module, 'slot' => $slot, 'schedule' => $schedule,
        'lesson' => $lesson, 'session' => $session, 'current' => lp73_current_segment($lesson['timeline'], $schedule, $slot['is_today'], $today, $now),
        'svp' => lp73_svp_rows($classId, $lesson),
    ];
}

/**
 * Aktuální úsek plánu: během dnešního bloku podle hodin (od začátku bloku), jinak první úsek a časovač stojí.
 * @return array{index:int,running:bool,minute:int,ends_at:int,remaining:int}
 */
function lp73_current_segment(array $timeline, array $schedule, bool $isToday, string $today, int $now): array
{
    $idle = ['index' => 0, 'running' => false, 'minute' => 0, 'ends_at' => 0, 'remaining' => 0];
    $startText = (string)($schedule['start'] ?? '');
    if ($timeline === [] || !$isToday || preg_match('/^\d{1,2}:\d{2}$/', $startText) !== 1) return $idle;
    $start = strtotime($today . ' ' . $startText);
    if ($start === false) return $idle;
    $minute = intdiv($now - $start, 60);
    if ($now < $start || $minute >= (int)end($timeline)['to']) return $idle;
    foreach (array_values($timeline) as $i => $seg) {
        if ($minute >= (int)$seg['from'] && $minute < (int)$seg['to']) {
            $ends = $start + (int)$seg['to'] * 60;
            return ['index' => $i, 'running' => true, 'minute' => $minute, 'ends_at' => $ends, 'remaining' => max(0, $ends - $now)];
        }
    }
    return $idle;
}

/** Výsledky ŠVP lekce se stavem vazby (návrh / schváleno …). @return array{status:string,source:string,rows:list<array{text:string,subject:string,year:int}>} */
function lp73_svp_rows(string $classId, array $lesson): array
{
    $cur = $lesson['curriculum']['svp'] ?? null;
    if (!is_array($cur) || !is_array($cur['outcomes'] ?? null)) return ['status' => 'chybí vazba', 'source' => '', 'rows' => []];
    $status = function_exists('svp72_status') ? (string)(svp72_status($classId, (int)$lesson['number'])['status'] ?? 'navrh') : (string)($cur['status'] ?? 'navrh');
    $rows = [];
    foreach ($cur['outcomes'] as $o) {
        if (is_array($o) && trim((string)($o['text'] ?? '')) !== '') $rows[] = ['text' => (string)$o['text'], 'subject' => (string)($o['subject_label'] ?? ''), 'year' => (int)($o['year'] ?? 0)];
    }
    $label = ['navrh' => 'návrh vazby (čeká na schválení)', 'schvaleno' => 'schválená vazba', 'vraceno' => 'vazba vrácena k úpravě', 'zmeneno' => 'vazba změněna po schválení'][$status] ?? 'návrh vazby';
    return ['status' => $label, 'source' => (string)($cur['source'] ?? ''), 'rows' => $rows];
}

/** Zadání pro žáky bez očekávaných výstupů (ty mohou být odpovědí) – jen text úkolu. @return list<string> */
function lp73_task_texts(array $lesson, int $max): array
{
    $out = [];
    foreach ((array)($lesson['tasks'] ?? []) as $t) {
        $text = trim(is_array($t) ? (string)($t['text'] ?? '') : (string)$t);
        if ($text !== '' && !in_array($text, $out, true)) $out[] = $text;
        if (count($out) >= $max) break;
    }
    return $out;
}

/** Otázky exit ticketu (jen text otázky; možnosti, správná odpověď ani vysvětlení se nevydávají). @return list<string> */
function lp73_exit_questions(array $lesson): array
{
    return array_values(array_filter(array_map('strval', (array)($lesson['exit_ticket'] ?? [])), static fn(string $q): bool => trim($q) !== ''));
}

/** Materiály k tisku: názvy zdrojů a nástroje (bez URL – na papíře k ničemu; v aplikaci je najdete v Dnešní hodině). @return list<string> */
function lp73_material_lines(array $lesson): array
{
    $out = [];
    foreach ((array)($lesson['materials'] ?? []) as $m) {
        $title = trim((string)($m['title'] ?? ''));
        if ($title !== '') $out[] = $title . ((string)($m['type'] ?? '') !== '' ? ' (' . (string)$m['type'] . ')' : '');
    }
    foreach ((array)($lesson['tools'] ?? []) as $tool) if (trim((string)$tool) !== '') $out[] = 'Nástroj: ' . trim((string)$tool);
    return array_values(array_unique(array_slice($out, 0, 10)));
}

/** Kontrolní list třídy pro tisk: popisky žáků podle volby jmen učitele („Žák N“ při skrytí). @return list<string> */
function lp73_roster_labels(string $classId, ?bool $hidden = null): array
{
    if (!lt70_class_allowed($classId) || !function_exists('project_students_for_class')) return [];
    $hidden ??= function_exists('tp721_names_hidden') && tp721_names_hidden();
    $labels = [];
    $i = 0;
    foreach (project_students_for_class($classId) as $s) {
        $label = trim((string)($s['label'] ?? ''));
        if ($label === '') continue;
        $i++;
        $labels[] = function_exists('tp721_student_label') ? tp721_student_label($label, $i, $hidden) : ($hidden ? 'Žák ' . $i : $label);
    }
    return $labels;
}

/** Kontakt na koordinaci zástupů z konfigurace školy (role, ne osobní telefon); bez nastavení obecný text. */
function lp73_substitution_contact(): string
{
    $value = getenv('EDUCANET_SUBSTITUTION_CONTACT');
    if (!is_string($value) || trim($value) === '') {
        $secrets = is_array($GLOBALS['educanet_secrets'] ?? null) ? $GLOBALS['educanet_secrets'] : [];
        $value = is_scalar($secrets['substitution_contact'] ?? null) ? (string)$secrets['substitution_contact'] : '';
    }
    $value = trim((string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string)$value));
    if ($value === '') return LP73_CONTACT_DEFAULT;
    return mb_strlen($value) > LP73_CONTACT_MAX ? mb_substr($value, 0, LP73_CONTACT_MAX) . '…' : $value;
}

/** Jak žáci otevřou kód hodiny (pro zástup a projektor). @return list<string> */
function lp73_code_steps(array $session): array
{
    $code = $session['exists'] && $session['open'] ? (string)$session['code'] : '';
    return [
        'Žáci se přihlásí do EDUCANETu svým školním účtem (stejně jako v běžné hodině).',
        $code !== '' ? 'Zvolí „Mám kód hodiny“ a zadají kód ' . $code . '.' : 'Zvolí „Mám kód hodiny“ a zadají kód, který připraví vyučující (v Dnešní hodině nebo v záložce Kód hodiny). Když kód není, pracuje se podle plánu B níže.',
        'Kdo se nepřihlásí, pracuje podle plánu B na papíře – nic se tím nezmešká.',
    ];
}

/** Název lekce bez úvodního „Lekce N ·“ (číslo lekce se vypisuje zvlášť). */
function lp73_title(array $lesson): string
{
    $title = trim((string)preg_replace('/^Lekce\s+\d+\s*[·:–-]\s*/u', '', (string)($lesson['title'] ?? '')));
    return $title !== '' ? $title : (string)($lesson['title'] ?? '');
}

/** Věta s velkým počátečním písmenem (po odebrání štítku „Zástup bez odborníka:“ apod.). */
function lp73_sentence(string $text): string
{
    $text = trim($text);
    return $text === '' ? '' : mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

/** Text úseku pro žáky (co dělají) – projektor a zástup. */
function lp73_segment_student_text(array $seg): string
{
    $student = trim((string)($seg['student'] ?? ''));
    return $student !== '' ? $student : trim((string)($seg['teacher'] ?? ''));
}
