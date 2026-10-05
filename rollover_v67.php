<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v67 · přechod školního roku: PLÁN pro data vrstev v62–v67 (jen čtení, nic nezapisuje).
 *
 * Skutečný přechod (identity58_rollover_apply) přesouvá žáky podle stabilního student_id. Soubory vrstev v62–v67 jsou klíčované
 * právě student_id, takže se zachovají samy: důkazy kompetencí (v62), portfolio (v65) i cíle/sdílení profilu (v67) žáka
 * doprovodí do nové třídy; kompetence nové třídy se počítají z téhož katalogu.
 * Co je třeba po přechodu vyřešit ručně (a tento plán to spočítá předem):
 *   - stav cest (v63) staré třídy zůstane v souboru žáka jako archiv (cesty nové třídy mají jiná id),
 *   - rozpracované projektové cykly (v65) staré třídy (nejsou ve stavu graded/portfolio) je třeba uzavřít u učitele,
 *   - návrhy hodnocení (v66) se počítají z důkazů za školní rok; stará třída je dál zobrazí jen učiteli.
 * Zápis do cest/projektů/hodnocení přechod roku z bezpečnostních důvodů neprovádí (rozhodnutí školy, riziko ztráty důkazů).
 */

/**
 * @return array{year:int,students:int,evidence_files:int,path_states:int,portfolios:int,growth_files:int,open_project_cycles:int,by_action:array<string,int>}
 */
function rollover67_plan(int $year): array
{
    $plan = identity58_rollover_plan($year);
    $out = ['year' => $year, 'students' => 0, 'evidence_files' => 0, 'path_states' => 0, 'portfolios' => 0, 'growth_files' => 0, 'open_project_cycles' => 0, 'by_action' => []];
    $keysByClass = [];
    foreach ((array)$plan['moves'] as $m) {
        $id = (string)$m['stu_id'];
        $out['students']++;
        $out['by_action'][(string)$m['action']] = ($out['by_action'][(string)$m['action']] ?? 0) + 1;
        if (!ev62_valid_id($id)) continue;
        if (is_file(ev62_path($id))) $out['evidence_files']++;
        if (function_exists('p63_state_path') && is_file(p63_state_path($id))) $out['path_states']++;
        if (is_file(STORAGE_DIR . '/portfolio_v65/' . $id . '.json.php')) $out['portfolios']++;
        if (is_file(STORAGE_DIR . '/growth_v67/' . $id . '.json.php')) $out['growth_files']++;
        $keysByClass[(string)$m['from']][project_student_key((string)$m['from'], (string)$m['label'])] = true;
    }
    if (function_exists('proj65_all')) {
        foreach (proj65_all() as $row) {
            $class = (string)($row['class_id'] ?? '');
            if (in_array((string)($row['state'] ?? ''), ['graded', 'portfolio'], true) || !isset($keysByClass[$class])) continue;
            foreach (proj65_member_keys($row) as $key) {
                if (isset($keysByClass[$class][$key])) { $out['open_project_cycles']++; break; }
            }
        }
    }
    return $out;
}
