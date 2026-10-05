<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Týmové hry – žákovské stránky (`?view=hry`, `?view=hry&hra=<id>`).
 *
 * Server vykreslí přístupný základ (tabulky, skutečné odkazy pro linii sítě do vestavěného terminálu
 * Labu) i bez JavaScriptu; živé skóre, kola a signály pak dopočítává a přepisuje assets/teamgames-v58.js
 * přes JSON API (teamgames_v58_api.php). Terminál pro linii sítě je vždy vestavěná relace Labu
 * (lab57_render_workspace, kontext `tg:<id>`) – žádná vlastní simulace, jen napojení na existující jádro.
 */

require_once __DIR__ . '/teamgames_v58_registry.php';
require_once __DIR__ . '/teamgames_v58_quiz.php';
require_once __DIR__ . '/linux_v58_levels_tg.php';
require_once __DIR__ . '/teamgames_v58_game_relay.php';
require_once __DIR__ . '/teamgames_v58_game_bingo.php';
require_once __DIR__ . '/teamgames_v58_game_jeopardy.php';
require_once __DIR__ . '/teamgames_v58_game_tug.php';
require_once __DIR__ . '/teamgames_v58_game_netadmin.php';
require_once __DIR__ . '/teamgames_v58_game_escape.php';
require_once __DIR__ . '/teamgames_v64_views.php';

function tg58_assets(): void
{
    ?><link rel="stylesheet" href="assets/teamgames-v58.css?v=58.0"><script src="assets/teamgames-v58.js?v=58.0" defer></script><?php
}

function tg58_render_header(string $title, array $module): void
{
    if (function_exists('render_header')) { render_header($title, $module); return; }
    ?><!doctype html><html lang="<?= e(edu_html_lang()) ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= tg58_h($title) ?></title><script src="assets/i18n-v58.js" defer></script><?= edu_tr_json_js() ?></head><body><?php
}

function tg58_render_footer(): void
{
    if (function_exists('render_footer')) { render_footer(); return; }
    ?></body></html><?php
}

// ---------------------------------------------------------------------------
// Vstupní bod: ?view=hry[&hra=<id>]
// ---------------------------------------------------------------------------

function tg58_render_student(string $classId, array $module, string $gameId = ''): void
{
    if ($gameId !== '' && tg58_valid_id($gameId)) {
        $session = tg58_get($gameId);
        if ($session !== null && (string)$session['class_id'] === $classId) { tg58_render_game_page($classId, $module, $session); return; }
    }
    tg58_render_list($classId, $module);
}

function tg58_render_list(string $classId, array $module): void
{
    $live = tg58_live_for_class($classId);
    $recent = array_values(array_filter(tg58_sessions_for_class($classId), static fn(array $s): bool => in_array((string)$s['status'], ['finished'], true)));
    tg58_render_header(tr('Týmové hry'), $module);
    ?>
    <div class="t52 tg58 tg58-list">
      <header class="t52-page-head"><span class="t52-kicker"><?= tg58_h(tr('Týmové hry')) ?></span><h1><?= tg58_h(tr('Hry třídy')) ?></h1><p><?= tg58_h(tr('Krátké soutěže pro celou třídu – týmy, ne jednotlivci. Sleduj tabuli, poslouchej pokyny učitele.')) ?></p></header>
      <?php if ($live !== null): ?>
        <section class="tg58-card tg58-live">
          <h2><?= tg58_h(tg58_type_label((string)$live['type'])) ?> · <?= tg58_h((string)$live['title']) ?></h2>
          <p><?= tg58_h(tg58_status_label(tg58_status($live, tg58_now()))) ?></p>
          <a class="btn primary" href="<?= tg58_h('?view=hry&hra=' . rawurlencode((string)$live['id'])) ?>"><?= tg58_h(tr('Vstoupit do hry →')) ?></a>
        </section>
      <?php else: ?>
        <p class="tg58-muted"><?= tg58_h(tr('Teď neběží žádná hra. Až učitel nějakou spustí, objeví se tady i v nabídce Labu.')) ?></p>
      <?php endif; ?>
      <?php if ($recent !== []): ?>
        <section class="tg58-card">
          <h2><?= tg58_h(tr('Poslední hry')) ?></h2>
          <ul class="tg58-list-plain">
            <?php foreach (array_slice($recent, 0, 6) as $s): ?>
              <li><a href="<?= tg58_h('?view=hry&hra=' . rawurlencode((string)$s['id'])) ?>"><?= tg58_h(tg58_type_label((string)$s['type'])) ?> · <?= tg58_h((string)$s['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php endif; ?>
    </div>
    <?php
    tg58_render_footer();
}

// ---------------------------------------------------------------------------
// Stránka konkrétní hry
// ---------------------------------------------------------------------------

function tg58_render_game_page(string $classId, array $module, array $session): void
{
    $now = tg58_now();
    $gameId = (string)$session['id'];
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    $view = tg58_student_view($session, $studentKey, $now);
    $status = (string)$view['status'];
    tg58_render_header(tg58_type_label((string)$session['type']) . ' · ' . (string)$session['title'], $module);
    ?>
    <div class="t52 tg58 tg58-student is-<?= tg58_h($status) ?>" data-tg58-app data-game="<?= tg58_h($gameId) ?>" data-type="<?= tg58_h((string)$session['type']) ?>" data-status="<?= tg58_h($status) ?>" data-api="teamgames_v58_api.php">
      <noscript><p class="tg58-notice"><?= tg58_h(tr('Tahle hra potřebuje zapnutý JavaScript – bez něj uvidíš jen poslední načtený stav.')) ?></p></noscript>
      <header class="tg58-head">
        <div><span class="t52-kicker"><?= tg58_h(tg58_type_label((string)$session['type'])) ?> · <?= tg58_h(tr('Týmová hra')) ?></span><h1><?= tg58_h((string)$session['title']) ?></h1></div>
        <?php tg58_render_countdown($view); ?>
      </header>
      <p class="tg58-status" role="status" data-tg58-status-text><?= tg58_h(tg58_status_label($status)) ?></p>
      <?php if ($status === 'lobby'): ?>
        <section class="tg58-card" role="status"><h2><?= tg58_h(tr('Hra se připravuje')) ?></h2><p><?= tg58_h(tr('Počkej na signál učitele – týmy se sestaví hned při startu.')) ?></p></section>
      <?php else: ?>
        <?php tg58_render_team_panel($view); ?>
        <?php if ($status === 'finished') tg64_render_retro_form($session, $classId, $studentKey, csrf_token()); else tg64_render_role_panel($session, $studentKey); // v64: role a retrospektiva ?>
        <?php tg58_render_lab_embed($session, $classId, $studentKey, $gameId); ?>
        <section class="tg58-card tg58-game" aria-label="<?= tg58_h(tr('Průběh hry')) ?>" data-tg58-game-panel data-tg58-ssr>
          <?php tg58_render_ssr_fallback($session, $view); ?>
        </section>
        <?php tg58_render_signals($view); ?>
      <?php endif; ?>
      <p class="tg58-foot"><?= tg58_h(tr('Terminál (linie sítě) je jen simulace – nic se nespouští doopravdy.')) ?></p>
    </div>
    <?php
    tg58_assets();
    tg58_render_footer();
}

function tg58_render_countdown(array $view): void
{
    $remaining = $view['remaining'] ?? null;
    ?>
    <div class="tg58-clock<?= $remaining === null ? ' is-open' : '' ?>" data-tg58-countdown data-remaining="<?= $remaining === null ? '' : (int)$remaining ?>">
      <span class="tg58-clock-label"><?= $remaining === null ? tg58_h(tr('Bez časového limitu')) : tg58_h(tr('Zbývá')) ?></span>
      <?php if ($remaining !== null): ?><strong class="tg58-clock-value" role="timer" aria-live="off" data-tg58-clock-value><?= (int)floor($remaining / 60) ?>:<?= str_pad((string)($remaining % 60), 2, '0', STR_PAD_LEFT) ?></strong><?php endif; ?>
      <span class="tg58-sr" data-tg58-clock-announce aria-live="polite"></span>
    </div>
    <?php
}

function tg58_render_team_panel(array $view): void
{
    $team = $view['team'] ?? null;
    ?>
    <section class="tg58-card tg58-team" aria-label="<?= tg58_h(tr('Můj tým')) ?>">
      <?php if ($team === null): ?>
        <p><?= tg58_h((string)($view['waiting'] ?? tr('Hra běží, ale zatím nejsi v žádném týmu.'))) ?></p>
      <?php else: ?>
        <p><strong><?= tg58_h(tr('Tým: {jmeno}', ['jmeno' => (string)$team['name']])) ?></strong><?php if (!empty($team['mates'])): ?> · <?= tg58_h(tr('spoluhráči: {jmena}', ['jmena' => implode(', ', (array)$team['mates'])])) ?><?php endif; ?></p>
      <?php endif; ?>
    </section>
    <?php
}

function tg58_render_signals(array $view): void
{
    if (($view['team'] ?? null) === null) return;
    // Literální tr() volání pro msgid extrakci – hodnoty musí přesně odpovídat TG58_SIGNALS (teamgames_v58_core.php).
    $signalLabels = ['help' => tr('Potřebujeme pomoc'), 'done' => tr('Máme to!'), 'check' => tr('Zkontrolujte nás')];
    ?>
    <section class="tg58-card tg58-signals" aria-label="<?= tg58_h(tr('Signály týmu')) ?>">
      <h2><?= tg58_h(tr('Signály týmu')) ?></h2>
      <p class="tg58-muted small"><?= tg58_h(tr('Appka nemá chat – domluvte se nahlas. Signál jen upozorní učitele a spoluhráče.')) ?></p>
      <div class="tg58-signal-btns" role="group" aria-label="<?= tg58_h(tr('Odeslat signál')) ?>">
        <?php foreach (TG58_SIGNALS as $kind => $label): ?><button type="button" class="btn secondary" data-tg58-signal="<?= tg58_h($kind) ?>"><?= tg58_h($signalLabels[$kind] ?? tr($label)) ?></button><?php endforeach; ?>
      </div>
      <ul class="tg58-feed" data-tg58-signal-feed>
        <?php foreach ((array)$view['signals'] as $s): ?><li><?= tg58_h((string)$s['label']) ?></li><?php endforeach; ?>
      </ul>
      <p class="tg58-net" data-tg58-net role="status"></p>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Vestavěný terminál Labu (jen linie sítě) – Štafeta / Správci sítě / Úniková místnost
// ---------------------------------------------------------------------------

function tg58_render_lab_embed(array $session, string $classId, string $studentKey, string $gameId): void
{
    if ((string)$session['line'] !== 'networks' || !function_exists('lab57_render_workspace') || !function_exists('lab57_level')) return;
    $type = (string)$session['type'];
    $level = null;
    if ($type === 'relay') {
        $teamId = tg58_team_of($session, $studentKey);
        $t = $teamId !== null ? ($session['game']['teams'][$teamId] ?? null) : null;
        if ($t !== null && $t['finished_at'] === null) { $legIds = tg58_relay_net_leg_ids(); $level = lab57_level((string)($legIds[(int)$t['leg'] - 1] ?? '')); }
    } elseif ($type === 'netadmin') {
        $nodeId = is_string($_GET['uzel'] ?? null) ? $_GET['uzel'] : '';
        if (in_array($nodeId, (array)($session['game']['node_ids'] ?? []), true)) $level = lab57_level($nodeId);
    } elseif ($type === 'bingo') {
        $cellId = (string)($_GET['bunka'] ?? '');
        if (in_array($cellId, tg58_bingo_net_cell_ids(), true)) $level = lab57_level($cellId);
    } elseif ($type === 'escape') {
        $teamId = tg58_team_of($session, $studentKey);
        $t = $teamId !== null ? ($session['game']['teams'][$teamId] ?? null) : null;
        if ($t !== null && $t['escaped_at'] === null) {
            $base = lab57_level('tg-escape-net-1');
            $role = (string)($session['game']['role_of'][$studentKey] ?? '');
            $level = $base !== null && function_exists('lab58_level_for_role') ? lab58_level_for_role($base, $role !== '' ? $role : null) : $base;
        }
    }
    if ($level === null) return;
    ?><section class="tg58-terminal" aria-label="<?= tg58_h(tr('Terminál Linux Labu')) ?>">
    <?php lab57_render_workspace($level, 'tg:' . $gameId, ['compact' => true, 'next_url' => '?view=hry&hra=' . rawurlencode($gameId)]); ?>
    </section><?php
}

// ---------------------------------------------------------------------------
// Přístupný základ bez JS (JS ho po prvním úspěšném dotazu nahradí – data-tg58-ssr)
// ---------------------------------------------------------------------------

function tg58_render_ssr_fallback(array $session, array $view): void
{
    $game = (array)($view['game'] ?? []);
    $type = (string)$session['type'];
    if ($type === 'relay' && isset($game['mode'])) {
        echo '<p>' . tg58_h(tr('Úsek {usek} z {celkem}.', ['usek' => (string)(int)$game['leg'], 'celkem' => (string)(int)$game['legs_total']])) . '</p>';
        if ($game['mode'] === 'lab') echo '<p>' . tg58_h(tr('Otevři terminál výše a splň úkol – po vyřešení úsek automaticky postoupí.')) . '</p>';
    } elseif ($type === 'netadmin' && isset($game['nodes'])) {
        echo '<table class="tg58-table"><caption>' . tg58_h(tr('Uzly sítě')) . '</caption><thead><tr><th scope="col">' . tg58_h(tr('Uzel')) . '</th><th scope="col">' . tg58_h(tr('Stav')) . '</th><th scope="col"></th></tr></thead><tbody>';
        foreach ((array)$game['nodes'] as $i => $node) {
            if (empty($node['visible'])) continue;
            $label = $node['owner'] ?? tr('volný');
            echo '<tr><td>' . tg58_h(tr('Uzel {cislo}', ['cislo' => (string)($i + 1)])) . '</td><td>' . tg58_h((string)$label) . '</td><td>';
            if ((string)$session['line'] === 'networks' && isset($node['level_id'])) echo '<a href="?view=hry&hra=' . rawurlencode((string)$session['id']) . '&uzel=' . rawurlencode((string)$node['id']) . '">' . tg58_h(tr('Pracovat na uzlu')) . '</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    } elseif ($type === 'escape' && isset($game['role_label'])) {
        echo '<p>' . tg58_h(tr('Tvoje role: {role}', ['role' => (string)$game['role_label']])) . '</p>';
        if (($game['mode'] ?? '') === 'lab') echo '<p>' . tg58_h(tr('Najdi svou stopu v terminálu výše a řekni ji nahlas týmu.')) . '</p>';
    } elseif ($type === 'bingo' && isset($game['cells'])) {
        echo '<p class="tg58-muted">' . tg58_h(tr('Karta bingo potřebuje zapnutý JavaScript. '));
        $labCells = array_values(array_filter((array)$game['cells'], static fn(array $c): bool => $c['kind'] === 'lab' && empty($c['solved'])));
        if ($labCells !== []) { echo tg58_h(tr('Terminálová políčka jdou otevřít i takhle: ')); foreach ($labCells as $c) echo '<a href="?view=hry&hra=' . rawurlencode((string)$session['id']) . '&bunka=' . rawurlencode((string)$c['id']) . '">' . tg58_h((string)$c['label']) . '</a> '; }
        echo '</p>';
    } elseif (in_array($type, ['jeopardy', 'tug'], true)) {
        echo '<p class="tg58-muted">' . tg58_h(tr('Tahle část hry potřebuje zapnutý JavaScript.')) . '</p>';
    }
}
