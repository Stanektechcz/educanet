<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

require_once __DIR__ . '/progress_v80.php';
require_once __DIR__ . '/learning_v80_views.php';

/**
 * EDUCANET v80 · stránka Můj pokrok (?view=pokrok): jeden Další krok, přehled s pruhy (hodnota vždy textem + tabulka), Co už umím / Co procvičit,
 * opakování, osobní cíle na týden a odznaky za zlepšení. Mobilní rozvržení první (1 sloupec), od 768 px dva sloupce. Texty přes tr();
 * názvy kompetencí, cest a úloh jsou výukový obsah (česky). Bez žebříčku a srovnání se spolužáky.
 */

function pg80_state_label(string $state): string
{
    return ['neovereno' => tr('zatím neověřeno'), 'rozpracovano' => tr('učím se'), 'zvladnuto' => tr('umím'), 'upevneno' => tr('umím jistě')][$state] ?? $state;
}

// ---------------------------------------------------------------------------------------------- souhrn (jen Můj pokrok: kompetence v62, cesty v63, exit tickety v72)

/** Odpovědi žáka na exit tickety (lekce → ok). Stejný hash jako lx72_student_hash (hlídá audit v80). @return array<int,bool> */
function pg80_exit_rows(string $classId, string $studentKey): array
{
    if (preg_match(LR80_CLASS_RE, $classId) !== 1) return [];
    $answers = (array)(storage_read(STORAGE_DIR . '/lesson_exit_v72_' . $classId . '.json.php', false)['answers'] ?? []);   // jen čtení souboru v72 (bez modelu lekcí)
    $hash = substr(hash('sha256', 'lx72|' . $studentKey), 0, 20);
    $out = [];
    foreach ($answers as $lesson => $byStudent) if (is_array($byStudent[$hash] ?? null)) $out[(int)$lesson] = !empty($byStudent[$hash]['ok']);
    ksort($out);
    return $out;
}

/** Kompetence žáka (jen třídy s kompetencemi): id → [label, state]. */
function pg80_competencies(string $classId, string $studentKey, int $now): array
{
    if (!function_exists('comp62_enabled_for_class') || !function_exists('m62_student') || !comp62_enabled_for_class($classId)) return [];
    $subject = comp62_subject_for_class($classId);
    if ($subject === null) return [];
    $map = (array)(m62_student($classId, $studentKey, $now)['map'] ?? []);
    $out = [];
    foreach (comp62_competencies($subject) as $id => $c) $out[$id] = ['label' => (string)$c['label'], 'state' => (string)($map[$id]['state'] ?? 'neovereno')];
    return $out;
}

/** Cesty žáka: id → [title, done, total]. */
function pg80_paths(string $classId, string $studentKey): array
{
    if (!function_exists('p63_paths_for_class')) return [];
    $paths = p63_paths_for_class($classId);
    $sid = $paths === [] ? null : p63_student_id($classId, $studentKey);
    if ($sid === null) return [];
    $st = p63_state($sid);
    $out = [];
    foreach ($paths as $id => $path) {
        $p = p63_path_progress($path, $st);
        $out[$id] = ['title' => (string)$path['title'], 'done' => $p['done'], 'total' => $p['total']];
    }
    return $out;
}

/** Celý souhrn pro stránku Můj pokrok. */
function pg80_overview(string $classId, string $studentKey, ?int $now = null): array
{
    $now ??= time();
    $ref = lr80_ref($classId, $studentKey);
    $state = lr80_state($classId, $ref);
    $tb = tb75_student_state($classId, $ref);
    $widgets = [];
    foreach (wg80_for_class($classId) as $w) $widgets[$w] = (int)(($state['w'][$w] ?? [])['ok'] ?? 0);
    $o = ['ref' => $ref, 'state' => $state, 'tasks' => pg80_task_rows($classId, $tb, $state), 'competencies' => pg80_competencies($classId, $studentKey, $now),
        'paths' => pg80_paths($classId, $studentKey), 'exits' => pg80_exit_rows($classId, $studentKey), 'due' => lr80_rep_due($state, $now), 'planned' => lr80_rep_planned($state, $now),
        'widgets' => $widgets, 'goals' => pg80_goals_week($state, $now), 'badges' => pg80_badges($state, $tb, $now), 'tb' => $tb];
    $o['next'] = pg80_next_step($classId, $studentKey, $o, $now);
    return $o;
}

/** JEDEN další krok: opakování → cesta (v63) → úloha k procvičení → nové cvičení → úlohy. @return array{href:string,kind:string,arg:string} */
function pg80_next_step(string $classId, string $studentKey, array $o, int $now): array
{
    if ($o['due'] !== []) return ['href' => '?view=opakovat', 'kind' => 'review', 'arg' => (string)count($o['due'])];
    if (function_exists('p63_next')) {
        $rec = p63_next($classId, $studentKey, $now);
        if (is_array($rec)) return ['href' => (string)$rec['href'], 'kind' => 'path', 'arg' => (string)$rec['title']];
    }
    $pick = null;
    foreach ($o['tasks'] as $task => $row) {
        if ($row['status'] === 'practise' && ($pick === null || $row['rate'] < $o['tasks'][$pick]['rate'])) $pick = $task;
    }
    foreach ($o['tasks'] as $task => $row) if ($pick === null && $row['status'] === 'new') $pick = $task;
    if ($pick !== null) return ['href' => '?' . http_build_query(['view' => 'ulohy', 'uloha' => $pick]), 'kind' => 'task', 'arg' => (string)$pick];
    foreach ($o['widgets'] as $w => $solved) if ($solved === 0) return ['href' => '?' . http_build_query(['view' => 'cviceni', 'w' => $w]), 'kind' => 'widget', 'arg' => $w];
    return $o['tasks'] !== [] ? ['href' => '?view=ulohy', 'kind' => 'tasks', 'arg' => ''] : ['href' => '?view=cviceni', 'kind' => 'widgets', 'arg' => ''];
}

/** Popisek cíle (druh + číslo). */
function pg80_goal_label(string $kind, int $n): string
{
    return match ($kind) {
        'tasks' => trn(['one' => 'Vyřešit {n} variantu úloh', 'few' => 'Vyřešit {n} varianty úloh', 'other' => 'Vyřešit {n} variant úloh'], $n),
        'review' => trn(['one' => 'Zopakovat {n} položku z opakování', 'few' => 'Zopakovat {n} položky z opakování', 'other' => 'Zopakovat {n} položek z opakování'], $n),
        'widgets' => trn(['one' => 'Vyřešit {n} interaktivní cvičení', 'few' => 'Vyřešit {n} interaktivní cvičení', 'other' => 'Vyřešit {n} interaktivních cvičení'], $n),
        'nohint' => trn(['one' => 'Vyřešit {n} úlohu bez nápovědy', 'few' => 'Vyřešit {n} úlohy bez nápovědy', 'other' => 'Vyřešit {n} úloh bez nápovědy'], $n),
        default => $kind,
    };
}

/** Odznaky: název a jak ho získat. */
function pg80_badge_texts(): array
{
    return [
        'fix' => [tr('Opravená chyba'), tr('Správně vyřeš úlohu z opakování chyb.'), '✓'],
        'series' => [tr('Celá řada'), tr('Dokonči opakování jedné úlohy po 1, 3 a 7 dnech.'), '↻'],
        'better' => [tr('Lepší než minulý týden'), tr('Měj tento týden vyšší úspěšnost v úlohách než minulý (aspoň 3 pokusy v obou týdnech).'), '↗'],
        'solo' => [tr('Zvládnuto samostatně'), tr('Vyřeš úlohu bez nápovědy, i když jsi ji dřív potřeboval(a).'), '★'],
        'goal' => [tr('Splněný cíl'), tr('Splň svůj cíl na týden.'), '◎'],
    ];
}

/** Ukazatel: popisek, hodnota textem a dekorativní pruh. */
function pg80_bar_html(string $label, int $have, int $total, string $value): string
{
    $pct = $total > 0 ? (int)round(min($have, $total) / $total * 100) : 0;
    return '<li class="lr80-bar"><span class="lr80-bar__label">' . e($label) . '</span><span class="lr80-bar__value">' . e($value) . '</span>'
        . '<span class="lr80-bar__track" aria-hidden="true"><span class="lr80-bar__fill" style="width:' . $pct . '%"></span></span></li>';
}

/** Řádky přehledu: [popisek, má, celkem, text]. */
function pg80_summary_rows(array $o): array
{
    $rows = [];
    $tasks = $o['tasks'];
    if ($tasks !== []) {
        $solved = count(array_filter($tasks, static fn(array $t): bool => $t['solved'] > 0));
        $rows[] = [tr('Úlohy s variantami'), $solved, count($tasks), tr('{n} z {m} typů vyřešeno', ['n' => $solved, 'm' => count($tasks)])];
    }
    if ($o['competencies'] !== []) {
        $m = count(array_filter($o['competencies'], static fn(array $c): bool => in_array($c['state'], ['zvladnuto', 'upevneno'], true)));
        $rows[] = [tr('Kompetence'), $m, count($o['competencies']), tr('{n} z {m} zvládnuto', ['n' => $m, 'm' => count($o['competencies'])])];
    }
    if ($o['paths'] !== []) {
        $done = array_sum(array_column($o['paths'], 'done'));
        $all = array_sum(array_column($o['paths'], 'total'));
        $rows[] = [tr('Výukové cesty'), $done, $all, tr('{n} z {m} kroků hotovo', ['n' => $done, 'm' => $all])];
    }
    if ($o['exits'] !== []) {
        $ok = count(array_filter($o['exits']));
        $rows[] = [tr('Exit tickety'), $ok, count($o['exits']), tr('{n} z {m} správně', ['n' => $ok, 'm' => count($o['exits'])])];
    }
    $w = count(array_filter($o['widgets']));
    $rows[] = [tr('Interaktivní cvičení'), $w, count($o['widgets']), tr('{n} z {m} cvičení vyřešeno', ['n' => $w, 'm' => count($o['widgets'])])];
    return $rows;
}

function pg80_next_html(array $next): string
{
    [$why, $label] = match ($next['kind']) {
        'review' => [tr('Máš úlohy k opakování – chyby se nejlíp opraví, dokud jsou čerstvé.'), tr('Zopakovat chyby ({n})', ['n' => $next['arg']])],
        'path' => [tr('Pokračuj ve výukové cestě, kterou máš rozpracovanou nebo doporučenou.'), tr('Pokračovat v cestě')],
        'task' => [tr('Tahle úloha ti zatím jde nejméně, nebo jsi ji ještě nezkusil(a).'), tr('Procvičit úlohu')],
        'widget' => [tr('Zkus interaktivní cvičení – výsledek uvidíš hned.'), tr('Otevřít cvičení')],
        'tasks' => [tr('Všechno je splněno. Vezmi si další variantu úlohy.'), tr('Otevřít úlohy')],
        default => [tr('Zkus interaktivní cvičení.'), tr('Otevřít cvičení')],
    };
    $what = match ($next['kind']) { 'path' => (string)$next['arg'], 'task' => tb75_t_title((string)$next['arg']), 'widget' => wg80_title((string)$next['arg']), default => '' };
    return '<section class="ds-card lr80-next" aria-labelledby="lr80-next-h"><p class="ds-eyebrow lr80-eyebrow" id="lr80-next-h">' . e(tr('Další krok')) . '</p>'
        . ($what !== '' ? '<p class="lr80-next__what"' . edu_content_lang_attr() . '>' . e($what) . '</p>' : '') . '<p>' . e($why) . '</p>'
        . '<a class="ds-btn ds-btn--primary ds-btn--block lr80-next__btn" href="' . e($next['href']) . '">' . e($label) . '</a></section>';
}

/** Co už umím / Co procvičit. @return array{0:list<string>,1:list<string>} HTML položky */
function pg80_lists(array $o): array
{
    $know = [];
    $practise = [];
    foreach ($o['competencies'] as $c) {
        $item = '<span' . edu_content_lang_attr() . '>' . e($c['label']) . '</span> <span class="ds-badge ' . (in_array($c['state'], ['zvladnuto', 'upevneno'], true) ? 'is-ok' : 'is-warn') . '">' . e(pg80_state_label($c['state'])) . '</span>';
        if (in_array($c['state'], ['zvladnuto', 'upevneno'], true)) $know[] = $item; elseif ($c['state'] === 'rozpracovano') $practise[] = $item;
    }
    foreach ($o['tasks'] as $task => $t) {
        $title = '<span' . edu_content_lang_attr() . '>' . e(tb75_t_title($task)) . '</span>';
        if ($t['status'] === 'master') $know[] = $title . ' <span class="ds-badge is-ok">' . e(tr('úspěšnost {n} %', ['n' => $t['rate']])) . '</span>';
        if ($t['status'] === 'practise') $practise[] = '<a href="?' . e(http_build_query(['view' => 'ulohy', 'uloha' => $task])) . '">' . $title . '</a> <span class="ds-badge is-warn">' . e(tr('úspěšnost {n} %', ['n' => $t['rate']])) . '</span>';
    }
    foreach ($o['paths'] as $p) if ($p['total'] > 0 && $p['done'] === $p['total']) $know[] = '<span' . edu_content_lang_attr() . '>' . e($p['title']) . '</span> <span class="ds-badge is-ok">' . e(tr('cesta dokončena')) . '</span>';
    foreach ($o['exits'] as $lesson => $ok) if (!$ok) $practise[] = '<a href="' . e('?view=lekce&n=' . (int)$lesson) . '">' . e(tr('Lekce {n}: exit ticket', ['n' => (int)$lesson])) . '</a> <span class="ds-badge is-warn">' . e(tr('zopakovat lekci')) . '</span>';
    return [$know, $practise];
}

function pg80_list_html(string $id, string $title, array $items, string $empty): string
{
    $html = '<section class="ds-card lr80-sec" aria-labelledby="' . e($id) . '"><h2 id="' . e($id) . '">' . e($title) . '</h2>';
    if ($items === []) return $html . '<p class="lr80-muted">' . e($empty) . '</p></section>';
    return $html . '<ul class="lr80-list">' . implode('', array_map(static fn(string $i): string => '<li>' . $i . '</li>', array_slice($items, 0, 12))) . '</ul></section>';
}

function pg80_goals_html(array $o, int $now): string
{
    $html = '<section class="ds-card lr80-sec" id="lr80-goals" aria-labelledby="lr80-goals-h"><h2 id="lr80-goals-h">' . e(tr('Moje cíle na tento týden')) . '</h2>'
        . '<p class="lr80-muted">' . e(tr('Krátký cíl na týden – plní se sám podle toho, co vyřešíš. Vidíš ho jen ty a tví učitelé.')) . '</p>';
    if ($o['goals'] !== []) {
        $html .= '<ul class="lr80-bars lr80-goals">';
        foreach ($o['goals'] as $g) {
            $have = pg80_goal_progress($g, $o['state'], $o['tb']);
            $n = (int)$g['n'];
            $html .= pg80_bar_html(pg80_goal_label((string)$g['k'], $n), $have, $n, $have >= $n ? tr('splněno ({n} z {m})', ['n' => min($have, $n), 'm' => $n]) : tr('{n} z {m}', ['n' => $have, 'm' => $n]))
                . '<li class="lr80-goal-rm"><form method="post">' . lr80_csrf_html('lr80_goal_remove') . '<input type="hidden" name="goal" value="' . e((string)$g['id']) . '">'
                . '<button class="ds-btn ds-btn--ghost" type="submit">' . e(tr('Odebrat cíl: {cil}', ['cil' => pg80_goal_label((string)$g['k'], $n)])) . '</button></form></li>';
        }
        $html .= '</ul>';
    }
    if (count($o['goals']) < PG80_GOALS_MAX) {
        $used = array_column($o['goals'], 'k');
        $html .= '<form method="post" class="lr80-goal-form">' . lr80_csrf_html('lr80_goal_add') . '<div class="ds-field"><label class="ds-label" for="lr80-goal">' . e(tr('Nový cíl')) . '</label>'
            . '<select class="ds-input" id="lr80-goal" name="goal_pick" required><option value="">' . e(tr('Vyber…')) . '</option>';
        foreach (PG80_GOALS as $kind => $ns) {
            if (in_array($kind, $used, true)) continue;
            foreach ($ns as $n) $html .= '<option value="' . e($kind . ':' . $n) . '">' . e(pg80_goal_label($kind, $n)) . '</option>';
        }
        $html .= '</select></div><button class="ds-btn ds-btn--primary" type="submit">' . e(tr('Nastavit cíl')) . '</button></form>';
    }
    return $html . '</section>';
}

function pg80_badges_html(array $badges): string
{
    $texts = pg80_badge_texts();
    $html = '<section class="ds-card lr80-sec" aria-labelledby="lr80-badges-h"><h2 id="lr80-badges-h">' . e(tr('Odznaky za zlepšení')) . '</h2>'
        . '<p class="lr80-muted">' . e(tr('Odznaky jsou za tvůj vlastní posun, ne za pořadí ve třídě.')) . '</p><ul class="lr80-badges">';
    foreach (PG80_BADGES as $id) {
        $b = $badges[$id];
        [$name, $how, $icon] = $texts[$id];
        $html .= '<li class="lr80-badge' . ($b['earned'] ? ' is-earned' : '') . '"><span class="lr80-badge__icon" aria-hidden="true">' . e($icon) . '</span><span class="lr80-badge__name">' . e($name) . '</span>'
            . '<span class="ds-badge ' . ($b['earned'] ? 'is-ok' : '') . '">' . e($b['earned'] ? tr('získáno') : tr('zatím ne')) . '</span><span class="lr80-badge__how">' . e($how) . '</span></li>';
    }
    return $html . '</ul></section>';
}

/** Celá stránka Můj pokrok. */
function pg80_render_page(string $classId, string $studentKey, ?int $now = null): void
{
    $now ??= time();
    $o = pg80_overview($classId, $studentKey, $now);
    $rows = pg80_summary_rows($o);
    echo lr80_assets_html() . '<div class="ds78 lr80-page lr80-progress"><h1>' . e(tr('Můj pokrok')) . '</h1>'
        . '<p class="lr80-lead">' . e(tr('Všechno na jednom místě: co už umíš, co procvičit a jeden další krok. Vidíš to jen ty a tví učitelé.')) . '</p>'
        . pg80_next_html($o['next']) . lr80_links_html();
    echo '<section class="ds-card lr80-sec" aria-labelledby="lr80-sum-h"><h2 id="lr80-sum-h">' . e(tr('Přehled')) . '</h2><ul class="lr80-bars">';
    foreach ($rows as [$label, $have, $total, $text]) echo pg80_bar_html($label, $have, $total, $text);
    echo '</ul><details class="lr80-table"><summary>' . e(tr('Zobrazit jako tabulku')) . '</summary><table class="lr80-tbl"><caption>' . e(tr('Přehled pokroku')) . '</caption><thead><tr><th scope="col">'
        . e(tr('Oblast')) . '</th><th scope="col">' . e(tr('Hotovo')) . '</th><th scope="col">' . e(tr('Celkem')) . '</th></tr></thead><tbody>';
    foreach ($rows as [$label, $have, $total]) echo '<tr><th scope="row">' . e($label) . '</th><td>' . (int)$have . '</td><td>' . (int)$total . '</td></tr>';
    echo '</tbody></table></details></section>';
    [$know, $practise] = pg80_lists($o);
    $due = count($o['due']);
    if ($due > 0) array_unshift($practise, '<a href="?view=opakovat">' . e(tr('Na dnes k opakování: {n}', ['n' => $due])) . '</a>');
    echo '<div class="lr80-cols">' . pg80_list_html('lr80-know-h', tr('Co už umím'), $know, tr('Zatím tu nic není – první vyřešené úlohy a zvládnuté kompetence se objeví tady.'))
        . pg80_list_html('lr80-prac-h', tr('Co procvičit'), $practise, tr('Teď nic nečeká. Až se v úloze objeví chyba, najdeš ji tady.')) . '</div>';
    echo pg80_goals_html($o, $now) . pg80_badges_html($o['badges']);
    echo '<p class="lr80-note">' . e(tr('Soukromí: stránku vidíš jen ty a učitelé tvé třídy. Nápovědy se ukládají jen jako počet, odpovědi se neukládají.')) . '</p></div>';
}
