<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Týmové hry – projekční pohled (`teacher.php?tab=hry&projektor=<id>`), samostatná stránka.
 * Ukazuje jen souhrny týmů (nikdy jednotlivé chyby, nikdy identitu za anonymním režimem) – jména podle
 * nastavení soukromí relace. Dotazuje se každé 2 s (viz assets/teamgames-v58.js), zpomalí ve skryté kartě.
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

function tg58_render_projector(string $gameId): void
{
    $now = tg58_now();
    $session = tg58_valid_id($gameId) ? tg58_get($gameId) : null;
    // v59 · SEC59-02: hra mimo rozsah učitele se chová jako nenalezená (403) – obrana i mimo guard teacher.php.
    $denied = $session !== null && function_exists('teacher59_can_class') && !teacher59_can_class((string)($session['class_id'] ?? ''));
    if ($denied) $session = null;
    if (!headers_sent()) {
        if ($session === null) http_response_code($denied ? 403 : 404);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
    }
    ?><!doctype html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><meta name="color-scheme" content="dark">
<title><?= tg58_h($session !== null ? 'Projektor · ' . (string)$session['title'] : 'Projektor · Týmové hry') ?></title>
<link rel="stylesheet" href="assets/teamgames-v58.css?v=58.0"></head>
<body class="tg58-projector-body">
<?php if ($session === null): ?>
  <main class="tg58-projector"><h1>Hra nebyla nalezena</h1><p>Zavři tuhle kartu a otevři projektor znovu ze záložky Týmové hry.</p></main>
<?php else:
    $status = tg58_status($session, $now);
    // &projektor=1 (navíc k tg_poll povinnému pro teacher58_handle_get) přepne tg58_teacher_poll() na
    // redigovaná data projektoru (tg58_projector_view) – nikdy ne na plný pohled učitele.
    $poll = 'teacher.php?tab=hry&tg_poll=1&projektor=1&game=' . rawurlencode($gameId);
    ?>
  <main class="tg58-projector" data-tg58-projector data-poll="<?= tg58_h($poll) ?>" data-status="<?= tg58_h($status) ?>">
    <header class="tg58-proj-head">
      <div><span class="tg58-proj-kicker"><?= tg58_h(tg58_type_label((string)$session['type'])) ?> · <span data-tg58-status-text><?= tg58_h(tg58_status_label($status)) ?></span></span><h1><?= tg58_h((string)$session['title']) ?></h1></div>
      <?php tg58_render_projector_clock($session, $now); ?>
    </header>
    <?php if ($status === 'lobby'): ?>
      <p class="tg58-proj-wait">Hra se připravuje – týmy se objeví hned po startu.</p>
    <?php else: $view = tg58_projector_view($session, $now); ?>
      <div class="tg58-proj-grid" data-tg58-proj-body><?php tg58_render_projector_body($session, $view); ?></div>
    <?php endif; ?>
    <p class="tg58-net" data-tg58-net role="status"></p>
    <footer class="tg58-proj-foot">Terminál (linie sítě) je jen simulace. Hrajeme fér: každý tým má vlastní data.</footer>
  </main>
  <script src="assets/teamgames-v58.js?v=58.0" defer></script>
<?php endif; ?>
</body></html>
<?php
}

function tg58_render_projector_clock(array $session, int $now): void
{
    $remaining = tg58_remaining($session, $now);
    ?>
    <div class="tg58-clock is-projector<?= $remaining === null ? ' is-open' : '' ?>" data-tg58-countdown data-remaining="<?= $remaining === null ? '' : (int)$remaining ?>">
      <span class="tg58-clock-label"><?= $remaining === null ? 'Bez časového limitu' : 'Zbývá' ?></span>
      <?php if ($remaining !== null): ?><strong class="tg58-clock-value" role="timer" aria-live="off" data-tg58-clock-value><?= (int)floor($remaining / 60) ?>:<?= str_pad((string)($remaining % 60), 2, '0', STR_PAD_LEFT) ?></strong><?php endif; ?>
    </div>
    <?php
}

/** Generický souhrn týmů (funguje pro všechny typy her – žádná hra nevystavuje projektoru jednotlivé chyby). */
function tg58_render_projector_body(array $session, array $view): void
{
    $game = (array)($view['game'] ?? []);
    if (isset($game['rows'])) {
        echo '<table class="tg58-table is-projector" data-tg58-proj-rows><thead><tr>';
        foreach (array_keys($game['rows'][0] ?? ['team' => '']) as $col) echo '<th scope="col">' . tg58_h(tg58_col_label((string)$col)) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ((array)$game['rows'] as $row) {
            echo '<tr>';
            foreach ($row as $col => $v) {
                if (in_array((string)$col, ['penalty_s', 'hints_used', 'roles'], true)) continue; // projektor: bez detailů, jen výsledek
                echo '<td>' . tg58_h(str_ends_with((string)$col, '_s') && ($v === null || is_int($v)) ? tg58_secs_text($v) : tg58_cell_text($v)) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    } elseif (isset($game['scores'])) {
        echo '<ol class="tg58-board is-projector" data-tg58-proj-scores>';
        foreach ((array)$game['scores'] as $s) echo '<li><span class="tg58-name">' . tg58_h((string)$s['team']) . '</span><span class="tg58-pts">' . (int)$s['points'] . ' b.</span></li>';
        echo '</ol>';
    } elseif (isset($game['sides'])) {
        echo '<div class="tg58-tug-rope" data-tg58-tug-rope data-position="' . (float)($game['position'] ?? 0) . '"><i></i></div>';
        echo '<ol class="tg58-board is-projector">';
        foreach ((array)$game['rounds_won'] as $r) echo '<li><span class="tg58-name">' . tg58_h((string)$r['team']) . '</span><span class="tg58-pts">' . (int)$r['rounds_won'] . ' kol</span></li>';
        echo '</ol>';
    }
    if (isset($game['nodes'])) {
        $visible = array_values(array_filter((array)$game['nodes'], static fn(array $n): bool => !empty($n['visible'])));
        echo '<p class="tg58-muted">Uzly opravené: ' . count(array_filter($visible, static fn(array $n): bool => $n['owner'] !== null)) . ' / ' . count($visible) . '</p>';
    }
    if (isset($game['escaped'])) echo '<p class="tg58-goal">Unikly týmy: <strong>' . (int)$game['escaped'] . ' / ' . (int)$game['teams_total'] . '</strong></p>';
}
