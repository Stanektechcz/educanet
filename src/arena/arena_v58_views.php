<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Aréna – pohledy pro Týdenní hádanku (ARN-01): stránka žáka `?view=hadanka`
 * a panel učitele uvnitř záložky Aréna (`?tab=arena&rezim=hadanka`). Logika je v arena_v58_weekly.php.
 */

require_once __DIR__ . '/arena_v57_views.php';
require_once __DIR__ . '/arena_v58_weekly.php';

// ---------------------------------------------------------------------------
// Žák: ?view=hadanka
// ---------------------------------------------------------------------------

function arena58_weekly_render_student(string $classId, array $module): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    if (function_exists('arena58_weekly_claim_xp')) arena58_weekly_claim_xp($classId, $studentKey);
    $now = arena57_now();
    $weekKey = arena58_weekly_current_key($now);
    $week = arena58_weekly_week($weekKey);

    // Explicitní žádost o cizí řešení (?view=hadanka&reseni=1) před uzávěrkou = 403 přímo z routy,
    // ne jen skrytí v UI. Musí běžet ještě před render_header() (jinak by šly hlavičky pozdě).
    if (isset($_GET['reseni']) && $week !== null && !arena58_weekly_reveal_allowed($week, $now)) {
        if (!headers_sent()) { http_response_code(403); header('Content-Type: text/plain; charset=utf-8'); header('Cache-Control: no-store'); }
        echo tr("Řešení spolužáků se odemknou až po uzávěrce (v pondělí 0:00), nebo když je učitel odemkne dřív pro rozbor ve třídě.") . "\n";
        return;
    }

    render_header(tr('Týdenní hádanka'), $module);
    if ($week === null) {
        ?>
        <div class="t52 arena58w"><section class="arena57-card"><h1><?= arena57_h(tr('Týdenní hádanka')) ?></h1><p><?= arena57_h(tr('Momentálně není k dispozici žádná hádanka. Zkus to prosím zas příště.')) ?></p><p><a href="?view=lab"><?= arena57_h(tr('← Zpět do Labu')) ?></a></p></section></div>
        <?php
        render_footer();
        return;
    }
    $level = lab57_level($week['level']);
    $board = arena58_weekly_board(['id' => $weekKey, 'class' => $classId, 'student' => $studentKey, 'now' => $now]);
    $closed = ($board['status'] ?? 'open') === 'closed';
    ?>
    <div class="t52 arena57 arena58w" data-arena58-weekly data-week="<?= arena57_h($weekKey) ?>" data-api="lab_v57_api.php" data-ctx="<?= arena57_h('weekly:' . $weekKey) ?>" data-poll-interval="15000">
      <header class="arena57-head">
        <div class="arena57-head-title">
          <span class="t52-kicker"><?= arena57_h(tr('Týdenní hádanka')) ?> · <?= arena57_h(tr('celá škola')) ?> · <?= $closed ? arena57_h(tr('uzavřeno')) : arena57_h(tr('běží')) ?></span>
          <h1><?= edu_cs((string)($level['title'] ?? '')) ?></h1>
          <p><?= arena57_h(tr('Golfová úloha: napiš co nejkratší funkční příkaz. Vyhrává nejkratší řešení, při shodě nejrychlejší. Nová hádanka každé pondělí 0:00.')) ?></p>
        </div>
        <div class="arena57-clock<?= $closed ? ' is-stopped' : '' ?>" data-arena58-countdown data-ends="<?= arena57_h((string)$board['ends_at']) ?>" data-now="<?= (int)$now ?>">
          <span class="arena57-clock-label"><?= $closed ? arena57_h(tr('Skončilo')) : arena57_h(tr('Zbývá')) ?></span>
          <strong class="arena57-clock-value" role="timer" aria-live="off" data-arena58-clock-value><?= $closed ? arena57_h(tr('{n} min', ['n' => 0])) : arena57_h(arena58_weekly_countdown_text((int)$board['remaining'])) ?></strong>
          <span class="arena57-sr" data-arena58-clock-announce aria-live="polite"></span>
        </div>
      </header>

      <?php if (function_exists('lab57_render_workspace')): lab57_render_workspace($level, 'weekly:' . $weekKey, ['next_url' => '?view=hadanka', 'compact' => true]); else: ?>
        <div class="arena57-card arena57-muted"><?= arena57_h(tr('Terminál se právě připravuje. Zkus stránku obnovit za chvilku.')) ?></div>
      <?php endif; ?>

      <section class="arena57-card arena58w-me" aria-label="<?= arena57_h(tr('Moje výsledky')) ?>" data-arena58-me>
        <h2><?= arena57_h(tr('Moje výsledky')) ?></h2>
        <?php if (!empty($board['me']['solved'])): ?>
          <p><?= tr_html('Tvoje nejkratší řešení má {znaky} ({cas}) – zkoušel(a) jsi to {n}×.', ['znaky' => '<strong data-arena58-me-len>' . arena57_h(tr('{n} znaků', ['n' => (int)$board['me']['len']])) . '</strong>', 'cas' => arena57_h(arena57_secs_text((int)$board['me']['secs'])), 'n' => (int)$board['me']['attempts']]) ?></p>
          <p><?= tr_html('Pořadí: třída {trida} · škola {skola}', ['trida' => '<strong data-arena58-me-rank-class>' . ($board['me']['rank_class'] !== null ? (int)$board['me']['rank_class'] . '.' : '–') . '</strong>', 'skola' => '<strong data-arena58-me-rank-school>' . ($board['me']['rank_school'] !== null ? (int)$board['me']['rank_school'] . '.' : '–') . '</strong>']) ?></p>
        <?php else: ?>
          <p><?= arena57_h(tr('Zatím jsi to nevyřešil(a). Nápověda něco stojí (−10 % bodů), ale žebříček řeší jen DÉLKU řešení – zkoušej směle, reset úlohy je zdarma a délku můžeš zlepšovat, dokud hádanka běží.')) ?></p>
        <?php endif; ?>
      </section>

      <div class="arena58w-boards">
        <?php arena58_weekly_render_board(tr('Naše třída'), $board['class'], 'class'); ?>
        <?php arena58_weekly_render_board(tr('Celá škola'), $board['school'], 'school'); ?>
      </div>

      <?php if (!$board['solutions_unlocked']): ?>
        <p class="arena57-muted"><?= arena57_h(tr('Skutečná řešení spolužáků uvidíš, až hádanka skončí (v pondělí) nebo je učitel odemkne dřív – ať se každý poctivě popere sám za sebe. 🙂')) ?></p>
      <?php else: ?>
        <p class="arena57-muted"><?= arena57_h(tr('Hádanka je uzavřená – u nejlepších řešení výše vidíš i jejich přesný příkaz. Zkus pochopit, proč je kratší než to tvoje.')) ?></p>
      <?php endif; ?>

      <?php arena58_weekly_render_history($board['history']); ?>

      <p class="arena57-foot"><?= arena57_h(tr('Terminál je simulace – nic se nespouští na skutečném počítači.')) ?> <a href="?view=lab"><?= arena57_h(tr('← Zpět do Labu')) ?></a></p>
    </div>
    <?php
    render_footer();
}

function arena58_weekly_render_board(string $title, array $scope, string $kind): void
{
    ?>
    <section class="arena57-card arena58w-board" aria-labelledby="arena58w-<?= arena57_h($kind) ?>-title">
      <h2 id="arena58w-<?= arena57_h($kind) ?>-title"><?= arena57_h($title) ?> <small class="arena57-muted">(<?= arena57_h(tr('{n} řešitelů', ['n' => (int)$scope['participants']])) ?>)</small></h2>
      <div class="arena57-table-wrap">
        <table class="arena57-table" data-arena58-board="<?= arena57_h($kind) ?>">
          <caption class="arena57-sr"><?= tr_html('Žebříček nejkratších řešení – {nazev}', ['nazev' => arena57_h($title)]) ?></caption>
          <thead><tr><th scope="col">#</th><th scope="col"><?= arena57_h(tr('Žák')) ?></th><th scope="col"><?= arena57_h(tr('Znaků')) ?></th><th scope="col"><?= arena57_h(tr('Čas')) ?></th></tr></thead>
          <tbody>
            <?php if ($scope['rows'] === []): ?>
              <tr><td colspan="4" class="arena57-muted"><?= arena57_h(tr('Zatím nikdo nevyřešil. Buď první!')) ?></td></tr>
            <?php else: foreach ($scope['rows'] as $row): ?>
              <tr class="<?= !empty($row['me']) ? 'is-me' : '' ?>">
                <td><?= (int)$row['rank'] ?>.</td>
                <td><?= edu_cs((string)$row['name']) ?><?= !empty($row['me']) ? ' <small>(' . arena57_h(tr('ty')) . ')</small>' : '' ?></td>
                <td><?= (int)$row['len'] ?></td>
                <td><?= arena57_h(arena57_secs_text((int)$row['secs'])) ?></td>
              </tr>
              <?php if (!empty($row['cmd'])): ?><tr class="arena58w-solution"><td></td><td colspan="3"><code<?= edu_content_lang_attr() ?>><?= arena57_h((string)$row['cmd']) ?></code></td></tr><?php endif; ?>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php
}

function arena58_weekly_render_history(array $history): void
{
    if ($history === []) return;
    ?>
    <section class="arena57-card arena58w-history">
      <h2><?= arena57_h(tr('Minulé hádanky')) ?></h2>
      <div class="arena57-table-wrap">
        <table class="arena57-table">
          <caption class="arena57-sr"><?= arena57_h(tr('Tvoje výsledky v minulých týdnech')) ?></caption>
          <thead><tr><th scope="col"><?= arena57_h(tr('Týden')) ?></th><th scope="col"><?= arena57_h(tr('Hádanka')) ?></th><th scope="col"><?= arena57_h(tr('Tvůj výsledek')) ?></th><th scope="col"><?= arena57_h(tr('Pořadí ve třídě')) ?></th></tr></thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><?= arena57_h((string)$h['week']) ?></td>
                <td><?= edu_cs((string)$h['title']) ?></td>
                <td><?= !empty($h['played']) ? arena57_h(tr('{n} znaků', ['n' => (int)$h['len']])) : '–' ?></td>
                <td><?= $h['rank_class'] !== null ? (int)$h['rank_class'] . '.' : '–' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Učitel: panel uvnitř záložky Aréna (?tab=arena&rezim=hadanka)
// ---------------------------------------------------------------------------

function arena58_weekly_hidden(string $csrf, string $action, string $classId, string $week): string
{
    return '<input type="hidden" name="csrf" value="' . $csrf . '"><input type="hidden" name="action" value="' . arena57_h($action) . '">'
        . '<input type="hidden" name="class_id" value="' . arena57_h($classId) . '"><input type="hidden" name="week" value="' . arena57_h($week) . '">'
        . '<input type="hidden" name="return_tab" value="arena">';
}

function arena58_weekly_render_teacher(array $modules, string $classId, string $csrf): void
{
    $now = arena57_now();
    $data = arena58_weekly_teacher_data($now);
    if (($data['level'] ?? null) === null) {
        echo '<section class="arena57-card"><p>Týdenní hádanka není momentálně dostupná – banka úloh je prázdná.</p></section>';
        return;
    }
    $level = (array)$data['level'];
    $poll = 'teacher.php?tab=arena&rezim=hadanka&hadanka_poll=1';
    $isClosed = $now >= (int)$data['end'];
    ?>
    <section class="arena57-card arena57-panel" data-arena58-weekly-teacher data-poll="<?= arena57_h($poll) ?>">
      <header class="arena57-panel-head">
        <div>
          <span class="arena57-badge <?= $isClosed ? 'is-finished' : 'is-live' ?>"><?= $isClosed ? 'Uzavřeno' : 'Běží' ?></span>
          <h2><?= arena57_h((string)$level['title']) ?> <small class="arena57-muted">(týden <?= arena57_h((string)$data['week']) ?>)</small></h2>
          <p class="arena57-muted small">
            Konec v <?= arena57_h(date('j. n. H:i', (int)$data['end'])) ?> · <?= (int)$data['participants'] ?> řešitelů ve škole
            · medián délky <?= $data['median_len'] !== null ? (int)$data['median_len'] . ' znaků' : '–' ?>
            <?= !empty($data['override']) ? ' · ručně vybráno' : '' ?><?= !empty($data['revealed_early']) ? ' · řešení odemčena předčasně' : '' ?>
          </p>
        </div>
      </header>

      <div class="arena57-panel-grid">
        <div class="arena57-block arena57-span2">
          <h3>Nejkratší řešení – celá škola</h3>
          <div class="arena57-table-wrap"><table class="arena57-table" data-arena58-t-top>
            <thead><tr><th scope="col">#</th><th scope="col">Žák</th><th scope="col">Třída</th><th scope="col">Znaků</th><th scope="col">Čas</th></tr></thead>
            <tbody>
              <?php if (($data['top'] ?? []) === []): ?><tr><td colspan="5" class="arena57-muted">Zatím nikdo nevyřešil.</td></tr>
              <?php else: foreach ($data['top'] as $row): ?>
                <tr><td><?= (int)$row['rank'] ?>.</td><td><?= arena57_h((string)$row['label']) ?></td><td><?= arena57_h(arena57_class_label((string)$row['class_id'])) ?></td><td><?= (int)$row['len'] ?></td><td><?= arena57_h(arena57_secs_text((int)$row['secs'])) ?></td></tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table></div>
        </div>
        <div class="arena57-block">
          <h3><?= arena57_h(arena57_class_label($classId)) ?></h3>
          <?php $mine = (array)($data['by_class'][$classId] ?? []); ?>
          <?php if ($mine === []): ?><p class="arena57-muted">Nikdo z téhle třídy zatím nevyřešil.</p>
          <?php else: ?><ol class="arena57-list"><?php foreach ($mine as $row): ?><li><?= (int)$row['rank'] ?>. <?= arena57_h((string)$row['label']) ?> – <?= (int)$row['len'] ?> znaků</li><?php endforeach; ?></ol>
          <?php endif; ?>
        </div>
      </div>

      <div class="arena57-panel-grid">
        <form method="post" class="arena57-block">
          <?= arena58_weekly_hidden($csrf, 'arena58_weekly_override', $classId, (string)$data['week']) ?>
          <h3>Přeskočit / vybrat ručně</h3>
          <label>Hádanka<select name="level_id">
            <option value="">– výchozí (podle rotace) –</option>
            <?php foreach ((array)$data['bank'] as $id => $lvl): ?><option value="<?= arena57_h((string)$id) ?>"<?= $id === $level['id'] ? ' selected' : '' ?>><?= arena57_h((string)$lvl['title']) ?> (obtížnost <?= (int)$lvl['difficulty'] ?>/3)</option><?php endforeach; ?>
          </select></label>
          <button class="btn secondary" type="submit">Nastavit pro tenhle týden</button>
        </form>
        <form method="post" class="arena57-block">
          <?= arena58_weekly_hidden($csrf, 'arena58_weekly_reroll', $classId, (string)$data['week']) ?>
          <h3>Náhodně jiná</h3>
          <p class="arena57-muted small">Vybere jinou úlohu z banky náhodně (ne tuhle).</p>
          <button class="btn secondary" type="submit">🎲 Přeskočit na jinou</button>
        </form>
        <form method="post" class="arena57-block">
          <?= arena58_weekly_hidden($csrf, 'arena58_weekly_reveal', $classId, (string)$data['week']) ?>
          <input type="hidden" name="revealed" value="<?= !empty($data['revealed_early']) ? '0' : '1' ?>">
          <h3>Řešení spolužáků</h3>
          <p class="arena57-muted small">Normálně se odemknou samy po uzávěrce (pondělí 0:00). Odemkni dřív, když je chceš probrat ve třídě hned.</p>
          <button class="btn secondary" type="submit"><?= !empty($data['revealed_early']) ? '🔒 Zamknout zpět' : '🔓 Odemknout teď' ?></button>
        </form>
      </div>
      <p class="arena57-net" data-arena57-net role="status"></p>
    </section>
    <?php
}
