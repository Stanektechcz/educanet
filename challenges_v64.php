<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
// v59 OPS-02: tr() i při načtení bez bootstrap.php (CLI audity, podprocesy).
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v64 · týdenní výzva navázaná na „Co dál“ + odznaky za milníky kompetencí.
 *
 * Týdenní výzva = kompetence z doporučení „Co dál“ (p63_next) + volitelně úroveň Labu / hra se stejnou kompetencí.
 * Zafixuje se při prvním zobrazení v týdnu (storage/challenges_v64.json.php, klíč student_id). Splněno je, když má žák
 * v tom týdnu v evidenci důkazů v62 záznam zdroje game|arena pro tu kompetenci. Série = po sobě jdoucí splněné týdny;
 * týden, který je celý volno (prázdniny, svátky; mot61_is_free_day), sérii nepřeruší ani neprodlouží (omluvené dny).
 * Žádné XP ani body za výzvu – je to ukazatel „co dál“. Odznaky vznikají jen při zvládnutí / upevnění kompetence
 * (m62), starší odznaky zůstávají zobrazené beze změny.
 */

const CH64_STORE_KEEP_WEEKS = 60;
const CH64_BADGE_STATES = ['zvladnuto', 'upevneno'];
const CH64_SOURCES = ['game', 'arena'];

function ch64_path(): string { return STORAGE_DIR . '/challenges_v64.json.php'; }

/** Pondělí týdne (Y-m-d) pro čas $ts. */
function ch64_monday(int $ts): string
{
    return date('Y-m-d', strtotime('monday this week', strtotime(date('Y-m-d', $ts) . ' 12:00:00')) ?: $ts);
}

function ch64_week_key(string $monday): string
{
    return date('o-\WW', (int)strtotime($monday . ' 12:00:00'));
}

/** Je celý týden (po–ne) volno? Taková série se kvůli omluvě nepočítá ani nepřerušuje. */
function ch64_week_is_free(string $monday): bool
{
    if (!function_exists('mot61_is_free_day')) return false;
    for ($i = 0; $i < 7; $i++) if (!mot61_is_free_day(date('Y-m-d', (int)strtotime($monday . ' +' . $i . ' day 12:00:00')))) return false;
    return true;
}

/**
 * Série splněných týdnů končící v $currentMonday (aktuální týden se počítá, jen když je splněný; nesplněný ho nepřeruší).
 * Čistá funkce. @param array<string,bool> $doneByMonday monday => splněno
 */
function ch64_streak(array $doneByMonday, string $currentMonday, int $maxWeeks = 52): int
{
    $streak = !empty($doneByMonday[$currentMonday]) ? 1 : 0;
    $monday = $currentMonday;
    for ($i = 0; $i < $maxWeeks; $i++) {
        $monday = date('Y-m-d', (int)strtotime($monday . ' -7 day 12:00:00'));
        if (!empty($doneByMonday[$monday])) { $streak++; continue; }
        if (ch64_week_is_free($monday)) continue; // omluvený týden
        break;
    }
    return $streak;
}

/** Splnění: existuje důkaz zdroje game|arena pro kompetenci v týdnu [monday, monday+7d). Čistá funkce nad řádky evidence. */
function ch64_week_done(array $rows, string $competency, string $monday): bool
{
    $from = (int)strtotime($monday . ' 00:00:00');
    $to = $from + 7 * 86400;
    foreach ($rows as $r) {
        if (!is_array($r) || (string)($r['competency'] ?? '') !== $competency || !in_array((string)($r['source'] ?? ''), CH64_SOURCES, true)) continue;
        $t = (int)strtotime((string)($r['at'] ?? ''));
        if ($t >= $from && $t < $to) return true;
    }
    return false;
}

/** Přeložený název kompetence (katalog domény competency), jinak česky. */
function ch64_label(string $id, string $label): string
{
    return function_exists('comp62_t_label') ? comp62_t_label($id, $label) : $label;
}

function ch64_student_id(string $classId, string $studentKey): ?string
{
    return function_exists('p63_student_id') ? p63_student_id($classId, $studentKey) : null;
}

/** Balíčky Labu (tag pack:*) kompetence – jen metadata katalogu, bez načtení simulátoru. @return list<string> */
function ch64_packs_of(string $classId, string $competency): array
{
    $subject = comp62_subject_for_class($classId);
    $tags = $subject !== null ? (array)(comp62_competencies($subject)[$competency]['tags'] ?? []) : [];
    return array_values(array_map(static fn(string $t): string => substr($t, 5), array_filter($tags, static fn($t): bool => str_starts_with((string)$t, 'pack:'))));
}

/**
 * Úroveň Labu (první nevyřešená v balíčku se stejnou kompetencí). Simulátor se kvůli přehledu NENAČÍTÁ (výkon):
 * je-li Lab už načtený v požadavku, úroveň se určí a uloží; jinak '' a odkaz vede na přehled Labu.
 */
function ch64_pick_level(string $classId, string $studentKey, string $competency): string
{
    $packs = ch64_packs_of($classId, $competency);
    if ($packs === [] || !function_exists('lab57_pack_levels')) return '';
    try {
        $solved = lab57_solved($classId, $studentKey, 'practice');
        foreach ($packs as $pack) {
            foreach (lab57_pack_levels($pack) as $level) {
                if (!isset($solved[(string)$level['id']]) && (string)($level['type'] ?? '') !== 'free') return (string)$level['id'];
            }
        }
    } catch (Throwable $e) {
        error_log('EDUCANET v64 výzva týdne: ' . get_class($e));
    }
    return '';
}

/**
 * Týdenní výzva žáka nebo null (třída bez cest / mimo pilot / bez doporučení). Při prvním zobrazení v týdnu ji zafixuje.
 * @return array{week:string,competency:string,label:string,level_id:string,href_path:string,href_game:string,done:bool,streak:int,minutes:int}|null
 */
function ch64_weekly_for(string $classId, string $studentKey, ?int $now = null): ?array
{
    $now ??= time();
    $sid = ch64_student_id($classId, $studentKey);
    if ($sid === null || !function_exists('p63_next') || !function_exists('comp62_enabled_for_class') || !comp62_enabled_for_class($classId)) return null;
    $monday = ch64_monday($now);
    $week = ch64_week_key($monday);
    $store = storage_read(ch64_path(), false);
    $entry = $store[$sid]['weeks'][$week] ?? null;
    $rec = p63_next($classId, $studentKey, $now);
    if (!is_array($entry)) {
        if ($rec === null || (string)($rec['competency'] ?? '') === '') return null;
        $entry = ['c' => (string)$rec['competency'], 'level' => ch64_pick_level($classId, $studentKey, (string)$rec['competency']), 'm' => $monday, 'min' => (int)($rec['minutes'] ?? 5)];
        if (!storage_readonly()) {
            storage_update(ch64_path(), static function (array $d) use ($sid, $week, $entry): array {
                $d[$sid]['weeks'][$week] = $entry;
                $d[$sid]['weeks'] = array_slice((array)$d[$sid]['weeks'], -CH64_STORE_KEEP_WEEKS, null, true);
                return $d;
            });
        }
    }
    return ch64_weekly_view($classId, $sid, $week, $entry, $store[$sid] ?? [], $monday, is_array($rec) ? (string)$rec['href'] : '?view=cesty');
}

function ch64_weekly_view(string $classId, string $sid, string $week, array $entry, array $mine, string $monday, string $pathHref): array
{
    $competency = (string)$entry['c'];
    $rows = function_exists('ev62_read') ? ev62_read($sid) : [];
    $done = [];
    foreach ((array)($mine['weeks'] ?? []) as $w) {
        if (is_array($w)) $done[(string)$w['m']] = ch64_week_done($rows, (string)$w['c'], (string)$w['m']);
    }
    $done[$monday] = ch64_week_done($rows, $competency, $monday);
    $subject = comp62_subject_for_class($classId);
    $label = $subject !== null ? (string)(comp62_competencies($subject)[$competency]['label'] ?? $competency) : $competency;
    $level = (string)($entry['level'] ?? '');
    return ['week' => $week, 'competency' => $competency, 'label' => $label, 'level_id' => $level, 'href_path' => $pathHref,
        'href_game' => $level !== '' ? '?view=lab&uroven=' . rawurlencode($level) : (ch64_packs_of($classId, $competency) !== [] ? '?view=lab' : '?view=hry'), 'done' => $done[$monday], 'streak' => ch64_streak($done, $monday), 'minutes' => (int)($entry['min'] ?? 5)];
}

/** Karta výzvy týdne na přehledu (bez nového CSS – třídy karty „Co dál“). Jen čtení + jednorázové zafixování týdne. */
function ch64_render_card(string $classId, string $studentKey): void
{
    $c = ch64_weekly_for($classId, $studentKey);
    if ($c === null) return;
    if (function_exists('p63_card_assets')) p63_card_assets(); // styly karty Co dál (už načtené na přehledu)
    echo '<section class="p63-card ui-card" aria-labelledby="ch64-title"><p class="ui-eyebrow">' . e(tr('Výzva týdne')) . '</p>'
        . '<h2 id="ch64-title">' . e(ch64_label((string)$c['competency'], (string)$c['label'])) . '</h2>'
        . '<p class="p63-reason">' . e($c['done'] ? tr('Splněno: v téhle kompetenci jsi tento týden prokázal/a, co umíš.') : tr('Vyzkoušej to v Labu nebo v týmové hře. Odměnou je jistota, že to umíš – žádné body.')) . '</p>'
        . '<p class="p63-meta">' . e(tr('Série týdnů: {n}', ['n' => (int)$c['streak']])) . ' · ' . e(tr('Prázdniny sérii nepřeruší.')) . '</p>'
        . '<p><a class="ui-btn ui-btn--primary p63-go" href="' . e((string)$c['href_game']) . '">' . e(tr('Do hry')) . '</a> '
        . '<a class="ui-link p63-all" href="' . e((string)$c['href_path']) . '">' . e(tr('Cesta k téhle kompetenci')) . '</a></p></section>';
}

// ---------------------------------------------------------------------------
// Odznaky za milníky kompetencí
// ---------------------------------------------------------------------------

/** Id odznaku: comp64_<kompetence>_<stav>. */
function ch64_badge_id(string $competency, string $state): string
{
    return 'comp64_' . $competency . '_' . $state;
}

/** Metadata odznaku pro badge60_card. */
function ch64_badge_meta(string $label, string $state, string $earnedAt): array
{
    $upevneno = $state === 'upevneno';
    return ['title' => ($upevneno ? tr('Upevněno') : tr('Zvládnuto')) . ': ' . $label, 'text' => $upevneno ? tr('Kompetenci jsi prokázal/a různými způsoby i s odstupem času.') : tr('Kompetence je ověřená testem, projektem nebo úlohou.'),
        'rarity' => $upevneno ? 'rare' : 'common', 'earned_at' => $earnedAt, 'category' => 'competency'];
}

/**
 * Při přechodu kompetence na zvládnuto/upevněno zapíše odznak (jednou). Hra sama „upevněno“ nedá (m62), takže ani odznak ne.
 * @param array<string,array<string,mixed>> $map výstup m62_student()['map'] (kompetence => stav)
 * @return list<string> nově udělená id odznaků
 */
function ch64_award_competency_badges(string $sid, array $map, int $now): array
{
    $new = [];
    foreach ($map as $competency => $row) {
        $state = is_array($row) ? (string)($row['state'] ?? '') : '';
        if (in_array($state, CH64_BADGE_STATES, true)) $new[] = ch64_badge_id((string)$competency, $state);
    }
    if ($new === [] || storage_readonly()) return [];
    $awarded = [];
    storage_update(ch64_path(), static function (array $d) use ($sid, $new, $now, &$awarded): array {
        foreach ($new as $id) {
            if (isset($d[$sid]['badges'][$id])) continue;
            $d[$sid]['badges'][$id] = date(DATE_ATOM, $now);
            $awarded[] = $id;
        }
        return $d;
    });
    return $awarded;
}

/** Odznaky žáka (id => čas) pro vykreslení. */
function ch64_badges_of(string $sid): array
{
    return array_map('strval', (array)(storage_read(ch64_path(), false)[$sid]['badges'] ?? []));
}

/** HTML odznaků za kompetence (badge60_card); prázdné, když žádný není. */
function ch64_render_badges(string $classId, string $studentKey): string
{
    $sid = ch64_student_id($classId, $studentKey);
    if ($sid === null || !function_exists('badge60_card')) return '';
    $subject = comp62_subject_for_class($classId);
    $competencies = $subject !== null ? comp62_competencies($subject) : [];
    $html = '';
    foreach (ch64_badges_of($sid) as $id => $at) {
        if (preg_match('/^comp64_([a-z0-9_]+)_(zvladnuto|upevneno)$/', (string)$id, $m) !== 1 || !isset($competencies[$m[1]])) continue;
        $html .= badge60_card((string)$id, ch64_badge_meta(ch64_label($m[1], (string)$competencies[$m[1]]['label']), $m[2], $at), true);
    }
    return $html === '' ? '' : '<section class="ui-card" aria-labelledby="ch64-badges"><h2 id="ch64-badges">' . e(tr('Odznaky za kompetence')) . '</h2><div class="b60-grid">' . $html . '</div></section>';
}
