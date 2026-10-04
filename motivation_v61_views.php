<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · Motivace – vykreslení (karta „Dnešní cíle“ na přehledu a v profilu, sezónní sbírka odznaků).
 * Jen HTML nad daty z motivation_v61.php; vše přes e(). Styl: design systém v61 (.ui-*) + assets/motivation-v61.css.
 */

require_once __DIR__ . '/motivation_v61.php';

function mot61_assets(): void
{
    echo '<link rel="stylesheet" href="' . e(asset_url('assets/motivation-v61.css?v=61.0')) . '">' . "\n";
}

/** Jeden cíl: popisek, ukazatel postupu a stav (splněno / odměna XP). */
function mot61_goal_row(array $goal): string
{
    $percent = (int)floor((int)$goal['value'] / max(1, (int)$goal['target']) * 100);
    $label = (string)$goal['label'];
    $state = $goal['done']
        ? ($goal['paid'] !== null && $goal['paid'] > 0 ? tr('Splněno · +{n} XP', ['n' => (int)$goal['paid']]) : tr('Splněno'))
        : tr('{value} / {target}', ['value' => (int)$goal['value'], 'target' => (int)$goal['target']]);
    return '<li class="mot61-goal' . ($goal['done'] ? ' is-done' : '') . '"><div class="mot61-goal-row"><span>' . e($label) . '</span><b>' . e($state) . '</b></div>'
        . '<span class="ui-meter-bar" role="progressbar" aria-label="' . e($label) . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . $percent . '"><i style="width:' . $percent . '%"></i></span></li>';
}

function mot61_goal_list(string $heading, array $goals): string
{
    $html = '';
    foreach ($goals as $goal) { $html .= mot61_goal_row($goal); }
    return '<h3 class="mot61-sub">' . e($heading) . '</h3><ul class="mot61-goals">' . $html . '</ul>';
}

/** Věta o sérii: číslo + vysvětlení ochrany (volný den neruší sérii). */
function mot61_streak_html(array $o): string
{
    $note = $o['free_today'] ? tr('Dnes je volno – série se nepřeruší.') : ($o['streak'] > 0 ? tr('Víkendy a prázdniny sérii nepřerušují.') : tr('Začni dnes, série se počítá po aktivních dnech.'));
    return '<div class="mot61-streak"><strong>' . e(trn(['one' => '{n} den v sérii', 'few' => '{n} dny v sérii', 'other' => '{n} dní v sérii'], (int)$o['streak'])) . '</strong>'
        . '<small>' . e($note) . '</small></div>';
}

/**
 * Karta „Dnešní cíle“ (≤ 3 denní + 2 týdenní cíle, série, strop XP). $class = další CSS třída karty.
 * Odměna se připíše při zobrazení (líně); nepřihlášený žák cíle vidí, ale XP nezískává.
 */
function mot61_render_goals_card(string $classId, string $class = ''): void
{
    $o = mot61_overview_for_session($classId, time());
    if ($o === null) return;
    mot61_assets();
    echo '<section class="mot61-card ui-card' . ($class !== '' ? ' ' . e($class) : '') . '" aria-labelledby="mot61-title"><header class="ui-card-head"><div><p class="ui-eyebrow">' . e(tr('Cíle')) . '</p>'
        . '<h2 id="mot61-title">' . e(tr('Dnešní cíle')) . '</h2></div></header>';
    if ($o['granted'] > 0) echo '<p class="ui-flash" role="status">' . e(tr('Za splněné cíle přibylo {n} XP.', ['n' => (int)$o['granted']])) . '</p>';
    echo mot61_streak_html($o) . mot61_goal_list(tr('Dnes'), $o['goals']['day']) . mot61_goal_list(tr('Tento týden'), $o['goals']['week']);
    echo '<p class="mot61-foot">' . e(tr('XP za cíle: dnes {d} z {dc}, tento týden {w} z {wc}. Cíle neovlivní známky ani žebříček.', ['d' => (int)$o['xp_today'], 'dc' => MOT61_DAILY_XP_CAP, 'w' => (int)$o['xp_week'], 'wc' => MOT61_WEEKLY_XP_CAP])) . '</p>';
    if (!auth_is_signed_in()) echo '<p class="mot61-foot">' . e(tr('XP za cíle se připisuje po přihlášení účtem.')) . '</p>';
    echo '</section>';
}

/** Sezónní sbírka v záložce Odznaky: získané a rozpracované jako karty, budoucí jako řádky. */
function mot61_render_seasons(string $classId): void
{
    $studentKey = adaptive_student_key($classId);
    $defs = mot61_season_badge_defs();
    if ($studentKey === '' || $defs === []) return;
    $events = mot61_events_by_key(learning_profile_key($classId));
    $activity = mot61_collect_activity($classId, $studentKey, $events);
    $today = date('Y-m-d');
    $progress = mot61_season_progress($activity, $events, $today, (array)(mot61_student_row($classId, $studentKey)['seasons'] ?? []));
    mot61_season_sync($classId, $studentKey, $progress);
    $earned = count(array_filter($progress, static fn(array $p): bool => !empty($p['earned'])));
    mot61_assets();
    echo '<section class="mot61-card ui-card" aria-labelledby="mot61-seasons"><header class="ui-card-head"><div><p class="ui-eyebrow">' . e(tr('Sezónní sbírka')) . '</p>'
        . '<h2 id="mot61-seasons">' . e(tr('Odznaky pololetí')) . '</h2></div><span class="mot61-count">' . e(tr('{n} z {total}', ['n' => $earned, 'total' => count($defs)])) . '</span></header>';
    $showLocked = function_exists('profile60_show_locked') && profile60_show_locked();
    foreach (mot61_seasons() as $season) {
        echo '<h3 class="mot61-sub">' . e((string)$season['label']) . '</h3>';
        $future = $season['from'] > $today;
        echo $future ? '<p class="mot61-foot">' . e(tr('Pololetí začne {date}.', ['date' => edu_date(mot61_day_ts((string)$season['from']))])) . '</p>' : '';
        mot61_render_season_cards($defs, $progress, (string)$season['id'], $showLocked);
    }
    echo '</section>';
}

/**
 * Získané odznaky pololetí jako karty; rozpracované jen jako ukazatele postupu (bez karet zamčených odznaků, ty se
 * kvůli lehké stránce vykreslují až na ?zamcene=1 – stejné pravidlo jako ve zbytku záložky Odznaky).
 */
function mot61_render_season_cards(array $defs, array $progress, string $seasonId, bool $showLocked): void
{
    $cards = $bars = '';
    $lockedCount = 0;
    foreach ($defs as $id => $meta) {
        if ($meta['season'] !== $seasonId) continue;
        $p = $progress[$id];
        $meta['earned_at'] = (string)$p['earned_at'];
        if (!empty($p['earned'])) { $cards .= badge60_card($id, $meta, true); continue; }
        $lockedCount++;
        if ($showLocked) { $cards .= badge60_card($id, $meta, false, (int)$p['percent']); continue; }
        $bars .= '<li><div class="mot61-goal-row"><span>' . e((string)$meta['title']) . '</span><b>' . e(tr('{value} / {target}', ['value' => (int)$p['value'], 'target' => (int)$p['target']])) . '</b></div>'
            . '<span class="ui-meter-bar" role="progressbar" aria-label="' . e((string)$meta['title']) . '" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . (int)$p['percent'] . '"><i style="width:' . (int)$p['percent'] . '%"></i></span></li>';
    }
    echo $cards !== '' ? '<div class="mot61-badges">' . $cards . '</div>' : '';
    echo $bars !== '' ? '<ul class="mot61-goals">' . $bars . '</ul>' : '';
    if ($lockedCount > 0 && !$showLocked && function_exists('profile60_locked_url')) {
        echo '<p class="mot61-foot"><a href="' . e(profile60_locked_url()) . '">' . e(tr('Zobrazit zamčené ({n})', ['n' => $lockedCount])) . '</a></p>';
    }
}
