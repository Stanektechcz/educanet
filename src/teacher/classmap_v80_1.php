<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v80.1 · Mapa třídy – výpočet (čisté funkce nad daty v62/v75, bez zápisu a bez výstupu).
 *
 * Řádky = žáci třídy (pořadí podle jména; číslo „Žák N“ při skrytých jménech je pořadí v tomto seznamu, stejně jako mapa kompetencí v62
 * a pozorování v76). Sloupce podle zdroje:
 *   komp   kompetence třídy (v62/v75, jen zapojené třídy): stav z m62_class (posledních 8 důkazů, útlum 60 dní) + skóre v %,
 *   ulohy  úlohy s variantami (v75/v76): úspěšnost pokusů žáka (správně / pokusy) z logu úloh.
 * Úroveň buňky (strong · ok · mid · low · none) je jen doplněk – každá buňka má hodnotu textem a ikonu.
 * Filtry z GET (whitelist, neplatné hodnoty = výchozí): zdroj, oblast (oblast kompetencí / skupina úloh), od–do (rozsah lekcí 1–28 → kompetence,
 * jejichž témata se v lekcích probírají; úlohy přes vazbu úloha → kompetence v76), řazení (jméno, průměr, sloupec) a směr, zobrazení (mapa / tabulka).
 * Žádná jména ani klíče v URL: GET nese jen filtry; výběr žáků jde POSTem (support_v80_1.php).
 */

const CM801_SOURCES = ['komp' => 'Kompetence', 'ulohy' => 'Úlohy s variantami'];
const CM801_TASK_AREAS = ['site' => 'Sítě', 'linux' => 'Linux', 'grafika' => 'Grafika', 'web' => 'Web a CSS'];
const CM801_LESSONS = 28;
const CM801_RATE_OK = 75;
const CM801_RATE_MID = 40;
const CM801_LEVELS = [
    'strong' => ['icon' => '✓✓', 'label' => 'umí jistě'],
    'ok' => ['icon' => '✓', 'label' => 'umí'],
    'mid' => ['icon' => '◐', 'label' => 'učí se'],
    'low' => ['icon' => '!', 'label' => 'potřebuje pomoc'],
    'none' => ['icon' => '○', 'label' => 'bez dat'],
];

/** Dostupné zdroje mapy pro třídu (pořadí = výchozí první). @return list<string> */
function cm801_sources(string $classId): array
{
    $out = [];
    if (function_exists('comp62_enabled_for_class') && comp62_enabled_for_class($classId) && comp62_subject_for_class($classId) !== null) $out[] = 'komp';
    if (function_exists('tb75_tasks_for_class') && tb75_tasks_for_class($classId) !== []) $out[] = 'ulohy';
    return $out;
}

/** Žáci třídy podle jména: list<['key','label','index','ref','sid']>. */
function cm801_students(string $classId): array
{
    $rows = [];
    foreach (project_students_for_class($classId) as $key => $s) {
        $label = (string)($s['label'] ?? '');
        $sid = function_exists('identity58_id_for_student') ? identity58_id_for_student($classId, $label) : null;
        $rows[] = ['key' => (string)$key, 'label' => $label, 'ref' => function_exists('tb75_student_ref') ? tb75_student_ref($classId, (string)$key) : '', 'sid' => is_string($sid) ? $sid : ''];
    }
    usort($rows, static fn(array $a, array $b): int => strcmp($a['label'], $b['label']) ?: strcmp($a['key'], $b['key']));
    foreach ($rows as $i => $r) $rows[$i]['index'] = $i + 1;
    return $rows;
}

/** Sloupce zdroje: id => ['label','group','group_label']. */
function cm801_columns(string $classId, string $source): array
{
    $out = [];
    if ($source === 'komp') {
        foreach (comp62_competencies((string)comp62_subject_for_class($classId)) as $id => $c) {
            $out[(string)$id] = ['label' => (string)$c['label'], 'group' => (string)$c['area'], 'group_label' => (string)$c['area_label']];
        }
    } elseif ($source === 'ulohy') {
        foreach (tb75_tasks_for_class($classId) as $task) {
            $area = (string)(TB75_TASKS[$task]['area'] ?? '');
            $out[$task] = ['label' => (string)(TB75_TASKS[$task]['title'] ?? $task), 'group' => $area, 'group_label' => CM801_TASK_AREAS[$area] ?? $area];
        }
    }
    return $out;
}

/** Oblasti (skupiny sloupců) zdroje: id => popisek. */
function cm801_groups(array $columns): array
{
    $out = [];
    foreach ($columns as $c) $out[$c['group']] = $c['group_label'];
    return $out;
}

/**
 * Stav filtrů z GET (whitelist). @param list<string> $sources
 * @return array{zdroj:string,oblast:string,od:int,do:int,razeni:string,smer:string,zobrazeni:string}
 */
function cm801_state(array $get, array $sources, array $columnsBySource): array
{
    $str = static fn(string $k): string => is_string($get[$k] ?? null) ? (string)$get[$k] : '';
    $zdroj = in_array($str('zdroj'), $sources, true) ? $str('zdroj') : (string)($sources[0] ?? '');
    $columns = (array)($columnsBySource[$zdroj] ?? []);
    $oblast = array_key_exists($str('oblast'), cm801_groups($columns)) ? $str('oblast') : '';
    $num = static fn(string $k): int => preg_match('/^\d{1,2}$/', $str($k)) === 1 && (int)$str($k) >= 1 && (int)$str($k) <= CM801_LESSONS ? (int)$str($k) : 0;
    $od = $num('od');
    $do = $num('do');
    if ($od > 0 && $do === 0) $do = CM801_LESSONS;
    if ($do > 0 && $od === 0) $od = 1;
    if ($od > $do) [$od, $do] = [$do, $od];
    $razeni = $str('razeni');
    if (!in_array($razeni, ['jmeno', 'prumer'], true) && !isset($columns[$razeni])) $razeni = 'jmeno';
    $smer = $str('smer') === 'desc' ? 'desc' : 'asc';
    $zobrazeni = $str('zobrazeni') === 'tabulka' ? 'tabulka' : 'mapa';
    return ['zdroj' => $zdroj, 'oblast' => $oblast, 'od' => $od, 'do' => $do, 'razeni' => $razeni, 'smer' => $smer, 'zobrazeni' => $zobrazeni];
}

/** Kompetence probírané v lekcích od–do (podle témat lekcí a tagů topic: katalogu). @param array<int,list<string>> $lessonTopics */
function cm801_lesson_competencies(array $columns, array $lessonTopics, int $od, int $do, array $tagsById): array
{
    $topics = [];
    for ($n = $od; $n <= $do; $n++) foreach ((array)($lessonTopics[$n] ?? []) as $t) $topics['topic:' . $t] = true;
    $out = [];
    foreach (array_keys($columns) as $id) {
        foreach ((array)($tagsById[$id] ?? []) as $tag) if (isset($topics[(string)$tag])) { $out[] = (string)$id; break; }
    }
    return $out;
}

/** Úroveň a text buňky kompetence z výsledku m62. */
function cm801_cell_komp(?array $info): array
{
    $state = (string)($info['state'] ?? 'neovereno');
    $score = isset($info['score']) && $info['score'] !== null ? (int)round((float)$info['score'] * 100) : null;
    $level = match ($state) {
        'upevneno' => 'strong',
        'zvladnuto' => 'ok',
        'rozpracovano' => ($score ?? 0) >= CM801_RATE_MID ? 'mid' : 'low',
        default => 'none',
    };
    $text = $level === 'none' ? 'neověřeno' : CM801_LEVELS[$level]['label'] . ($score !== null ? ' ' . $score . ' %' : '');
    return ['level' => $level, 'value' => $level === 'none' ? null : $score, 'text' => $text, 'n' => (int)($info['n'] ?? 0)];
}

/** Úroveň a text buňky úlohy z logu žáka (správně / pokusy). */
function cm801_cell_task(array $entryRaw): array
{
    $ok = 0;
    $n = 0;
    foreach ((array)($entryRaw['log'] ?? []) as $l) {
        if (!is_array($l)) continue;
        $n++;
        $ok += !empty($l['ok']) ? 1 : 0;
    }
    if ($n === 0) return ['level' => 'none', 'value' => null, 'text' => 'nezkoušeno', 'n' => 0];
    $rate = (int)round($ok / $n * 100);
    $level = $rate >= CM801_RATE_OK ? ($n >= 4 && $rate >= 90 ? 'strong' : 'ok') : ($rate >= CM801_RATE_MID ? 'mid' : 'low');
    return ['level' => $level, 'value' => $rate, 'text' => $ok . ' z ' . $n . ' správně (' . $rate . ' %)', 'n' => $n];
}

/** Buňky všech žáků: [klíč žáka => [sloupec => buňka]]. $mastery = m62_class(), $tbClass = data úloh třídy (ref => stav). */
function cm801_cells(string $source, array $students, array $columns, array $mastery, array $tbClass): array
{
    $out = [];
    foreach ($students as $s) {
        $row = [];
        foreach (array_keys($columns) as $col) {
            $row[$col] = $source === 'komp'
                ? cm801_cell_komp($s['sid'] !== '' ? ($mastery['students'][$s['sid']][$col] ?? null) : null)
                : cm801_cell_task((array)(($tbClass[$s['ref']] ?? [])[$col] ?? []));
        }
        $out[$s['key']] = $row;
    }
    return $out;
}

/** Průměr hodnot řádku (jen buňky s daty), null = bez dat. */
function cm801_avg(array $row): ?int
{
    $vals = array_values(array_filter(array_column($row, 'value'), static fn($v): bool => $v !== null));
    return $vals === [] ? null : (int)round(array_sum($vals) / count($vals));
}

/** Seřadí žáky podle stavu (bez dat vždy na konci). @return list<array> */
function cm801_sort(array $students, array $cells, array $state): array
{
    $key = static function (array $s) use ($cells, $state): ?int {
        if ($state['razeni'] === 'prumer') return cm801_avg($cells[$s['key']] ?? []);
        return $cells[$s['key']][$state['razeni']]['value'] ?? null;
    };
    usort($students, static function (array $a, array $b) use ($key, $state): int {
        if ($state['razeni'] === 'jmeno') return ($state['smer'] === 'desc' ? -1 : 1) * ($a['index'] <=> $b['index']);
        $va = $key($a);
        $vb = $key($b);
        if ($va === null || $vb === null) return ($va === null) <=> ($vb === null) ?: $a['index'] <=> $b['index'];
        return ($state['smer'] === 'desc' ? $vb <=> $va : $va <=> $vb) ?: $a['index'] <=> $b['index'];
    });
    return $students;
}

/** Souhrn sloupce: počty žáků podle úrovně. @return array<string,array<string,int>> */
function cm801_summary(array $cells, array $columns): array
{
    $out = [];
    foreach (array_keys($columns) as $col) {
        $out[$col] = array_fill_keys(array_keys(CM801_LEVELS), 0);
        foreach ($cells as $row) $out[$col][(string)($row[$col]['level'] ?? 'none')]++;
    }
    return $out;
}

/** Témata lekcí 1–28 třídy (balíčky lekcí v71; jen když učitel filtruje rozsah lekcí). @return array<int,list<string>> */
function cm801_lesson_topics(string $classId, array $module): array
{
    if (function_exists('comp75_lesson_topics')) return comp75_lesson_topics($classId, $module);
    $out = [];
    for ($n = 1; $n <= CM801_LESSONS; $n++) {
        $bundle = function_exists('lm71_bundle') ? lm71_bundle($classId, $module, $n) : [];
        $out[$n] = array_map('strval', array_keys((array)($bundle['topics'] ?? [])));
    }
    return $out;
}

/**
 * Celá mapa pro záložku: zdroje, stav filtrů, sloupce (po filtrech), žáci (seřazení), buňky, souhrn a poznámky.
 * $mastery a $tbClass lze předat (audit); jinak se načtou.
 */
function cm801_build(string $classId, array $module, array $get, ?array $mastery = null, ?array $tbClass = null): array
{
    $sources = cm801_sources($classId);
    $bySource = [];
    foreach ($sources as $src) $bySource[$src] = cm801_columns($classId, $src);
    $state = cm801_state($get, $sources, $bySource);
    $res = ['sources' => $sources, 'state' => $state, 'groups' => [], 'columns' => [], 'students' => [], 'cells' => [], 'summary' => [], 'lesson_note' => ''];
    if ($state['zdroj'] === '') return $res;
    $columns = $bySource[$state['zdroj']];
    $res['groups'] = cm801_groups($columns);
    if ($state['oblast'] !== '') $columns = array_filter($columns, static fn(array $c): bool => $c['group'] === $state['oblast']);
    if ($state['od'] > 0) {
        $subject = function_exists('comp62_subject_for_class') ? comp62_subject_for_class($classId) : null;
        $comps = $subject !== null ? comp62_competencies($subject) : [];
        $hit = cm801_lesson_competencies($comps, cm801_lesson_topics($classId, $module), $state['od'], $state['do'], array_map(static fn(array $c): array => (array)$c['tags'], $comps));
        if ($state['zdroj'] === 'komp') {
            $columns = array_intersect_key($columns, array_flip($hit));
        } else {
            $columns = array_filter($columns, static fn(array $c, string $task): bool => $subject !== null && function_exists('tb76_task_competency') && in_array((string)tb76_task_competency($subject, $task), $hit, true), ARRAY_FILTER_USE_BOTH);
        }
        $res['lesson_note'] = $subject === null ? 'Třída nemá katalog kompetencí, rozsah lekcí nejde použít.' : '';
    }
    $students = cm801_students($classId);
    $mastery ??= $state['zdroj'] === 'komp' && function_exists('m62_class') ? m62_class($classId) : ['students' => []];
    $tbClass ??= $state['zdroj'] === 'ulohy' && function_exists('tb75_data') ? (array)((tb75_data()['classes'] ?? [])[$classId] ?? []) : [];
    $cells = cm801_cells($state['zdroj'], $students, $columns, $mastery, $tbClass);
    $res['columns'] = $columns;
    $res['cells'] = $cells;
    $res['students'] = cm801_sort($students, $cells, $state);
    $res['summary'] = cm801_summary($cells, $columns);
    return $res;
}
