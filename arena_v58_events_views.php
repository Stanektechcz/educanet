<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }
if (!function_exists('tr')) { require_once __DIR__ . '/i18n_v58.php'; require_once __DIR__ . '/i18n_v59.php'; }

/**
 * EDUCANET v58 · Aréna – pohledy pro CTF týden (ARN-02) a Incidenty (ARN-03).
 * Žák: arena58_ctf_render_student / arena58_inc_render_student. Učitel: *_render_teacher_tab.
 * Terminál je pořád jen simulace (linux_v57_*) – nic se nespouští doopravdy.
 */

require_once __DIR__ . '/arena_v58_ctf.php';
require_once __DIR__ . '/arena_v58_incident.php';

// ---------------------------------------------------------------------------
// Sdílené drobné pomocníky
// ---------------------------------------------------------------------------

function arn58ev_h(string $value): string
{
    return function_exists('e') ? e($value) : htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function arn58ev_class_label(string $classId): string
{
    return function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;
}

/** @return list<string> všechny třídy školy (pro výběr tříd u CTF akce) */
function arn58ev_all_classes(): array
{
    $modules = $GLOBALS['modules'] ?? null;
    return is_array($modules) ? array_map('strval', array_keys($modules)) : [];
}

function arn58ev_duration_text(int $secs): string
{
    $secs = max(0, $secs);
    if ($secs >= 86400) return intdiv($secs, 86400) . ' d ' . intdiv($secs % 86400, 3600) . ' h';
    if ($secs >= 3600) return intdiv($secs, 3600) . ' h ' . intdiv($secs % 3600, 60) . ' min';
    return sprintf('%d:%02d', intdiv($secs, 60), $secs % 60);
}

function arn58ev_time_tag(?string $iso): string
{
    $ts = $iso !== null && $iso !== '' ? strtotime($iso) : false;
    if ($ts === false) return '<time>–</time>';
    return '<time datetime="' . arn58ev_h(date(DATE_ATOM, $ts)) . '">' . arn58ev_h(date('j. n. H:i', $ts)) . '</time>';
}

/** role="timer" odpočet se sekundovou hodnotou pro JS a hlášením po minutách (aria-live). */
function arn58ev_render_countdown(int $remaining, string $label, string $extraClass = ''): void
{
    ?>
    <div class="arn58e-clock <?= arn58ev_h($extraClass) ?>" data-arn58e-countdown data-remaining="<?= (int)$remaining ?>">
      <span class="arn58e-clock-label"><?= arn58ev_h($label) ?></span>
      <strong class="arn58e-clock-value" data-arn58e-clock-value role="timer" aria-live="off"><?= arn58ev_h(arn58ev_duration_text($remaining)) ?></strong>
      <span class="arn58e-sr" data-arn58e-clock-announce aria-live="polite"></span>
    </div>
    <?php
}

function arn58ev_hidden(string $csrf, string $action, ?string $classId, ?string $idField = null, ?string $idValue = null): void
{
    echo '<input type="hidden" name="csrf" value="' . arn58ev_h($csrf) . '">';
    echo '<input type="hidden" name="action" value="' . arn58ev_h($action) . '">';
    if ($classId !== null) echo '<input type="hidden" name="class_id" value="' . arn58ev_h($classId) . '">';
    if ($idField !== null && $idValue !== null) echo '<input type="hidden" name="' . arn58ev_h($idField) . '" value="' . arn58ev_h($idValue) . '">';
}

// =============================================================================
// CTF TÝDEN – ŽÁK  (?view=ctf)
// =============================================================================

function arena58_ctf_render_student(string $classId, array $module): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    if (function_exists('arena58_ctf_award_finished_xp')) arena58_ctf_award_finished_xp($classId, $studentKey);
    $event = arena58_ctf_live_for_class($classId);
    render_header(tr('CTF týden'), $module);
    arena58_events_assets();
    ?>
    <div class="t52 arn58e arn58e-ctf">
    <?php if ($event === null): ?>
      <section class="arn58e-card arn58e-empty"><h1><?= e(tr('CTF týden')) ?></h1><p><?= e(tr('Zatím tu není žádná vyhlášená akce. Zeptej se učitele, kdy začne další CTF týden.')) ?></p><p><a href="?view=lab">← <?= e(tr('Zpět do Labu')) ?></a></p></section>
    <?php else:
        $now = arena58_ctf_now();
        $status = arena58_ctf_status($event, $now);
        $stateKey = arena58_ctf_state_key(['id' => (string)$event['id'], 'student' => $studentKey]);
        $end = arena58_ctf_ts($event['ends_at'] ?? null);
        $remaining = $status === 'live' && $end !== null ? max(0, $end - $now) : 0;
        $tasks = arena58_ctf_task_rows($event, $classId, $stateKey);
        $wanted = isset($_GET['uloha']) && is_string($_GET['uloha']) ? (string)$_GET['uloha'] : '';
        $selected = $wanted !== '' ? lab57_level($wanted) : null;
        $board = arena58_ctf_board((string)$event['id'], $classId, $studentKey);
        $base = '?view=ctf';
        $statusLabel = $status === 'live' ? tr('Běží') : ($status === 'finished' ? tr('Skončil') : tr('Připravuje se'));
        $modeLabel = $event['mode'] === 'teams' ? tr('týmy') : tr('každý sám za sebe');
        $frozenNote = !empty($board['event']['frozen']) ? tr(' · žebříček je teď na chvíli zmrazený, ať je konec napínavý') : '';
    ?>
      <header class="arn58e-head">
        <div><span class="t52-kicker"><?= e(tr('CTF týden · {stav}', ['stav' => $statusLabel])) ?></span><h1><?= arn58ev_h((string)$event['title']) ?></h1>
        <p><?= e(tr('5 kategorií · {mod} · body klesají s počtem řešitelů (1.–3. místo 100 %, dál míň, nikdy pod 40 %) · první řešitel v celé akci dostane bonus ×1,25{zmrazeno}', ['mod' => $modeLabel, 'zmrazeno' => $frozenNote])) ?></p></div>
        <?php if ($status === 'live') arn58ev_render_countdown($remaining, tr('Zbývá')); ?>
        <div class="arn58e-me"><div><strong><?= $board['me']['rank'] !== null ? (int)$board['me']['rank'] . '.' : '–' ?></strong><small><?= e(tr('místo')) ?></small></div><div><strong><?= (int)$board['me']['points'] ?></strong><small><?= e(tr('bodů')) ?></small></div><?php if ($board['my_team'] !== null): ?><div><strong><?= arn58ev_h((string)$board['my_team']['name']) ?></strong><small><?= e(tr('můj tým')) ?></small></div><?php endif; ?></div>
      </header>
      <?php if ($status === 'draft'): ?>
        <section class="arn58e-card"><p><?= e(tr('CTF týden ještě nezačal. Sleduj termín spuštění – dá ti vědět učitel.')) ?></p></section>
      <?php else: ?>
      <div class="arn58e-layout">
        <nav class="arn58e-cats" aria-label="<?= e(tr('Úlohy podle kategorie')) ?>">
          <?php foreach ($tasks as $catKey => $cat): ?>
            <section class="arn58e-cat">
              <h2><?= arn58ev_h((string)$cat['meta']['icon']) ?> <?= arn58ev_h((string)$cat['meta']['label']) ?></h2>
              <p class="arn58e-muted small"><?= arn58ev_h((string)$cat['meta']['lead']) ?></p>
              <ul>
                <?php foreach ($cat['items'] as $item): $isCurrent = $selected !== null && (string)$selected['id'] === $item['id']; ?>
                  <li>
                    <?php if ($item['locked'] !== null): ?>
                      <span class="arn58e-task is-locked" aria-disabled="true"><strong><?= arn58ev_h($item['title']) ?></strong><small>🔒 <?= e(tr('nejdřív: {uloha}', ['uloha' => (string)$item['locked']])) ?></small></span>
                    <?php else: ?>
                      <a class="arn58e-task<?= $item['solved'] ? ' is-solved' : '' ?><?= $isCurrent ? ' is-current' : '' ?>" href="<?= arn58ev_h($base . '&uloha=' . rawurlencode($item['id'])) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>>
                        <strong><?= arn58ev_h($item['title']) ?></strong><small><?= $item['solved'] ? e(tr('✓ vyřešeno ({body} b)', ['body' => (int)$item['earned']])) : e(tr('{body} b základ', ['body' => (int)$item['points']])) ?></small>
                      </a>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </section>
          <?php endforeach; ?>
        </nav>
        <main class="arn58e-main">
          <?php if ($selected !== null): ?>
            <?php if (function_exists('lab57_render_workspace')): lab57_render_workspace($selected, 'ctf:' . (string)$event['id'], ['next_url' => $base, 'compact' => true]); else: ?>
              <div class="arn58e-card arn58e-muted"><?= e(tr('Terminál se právě připravuje, zkus stránku obnovit.')) ?></div>
            <?php endif; ?>
          <?php else: ?>
            <section class="arn58e-card"><h2><?= e(tr('Vyber si úlohu')) ?></h2><p><?= e(tr('Vyber kategorii vlevo a pusť se do první úlohy. Nápovědy jsou {stav_np}, každá stojí {procent} % bodů dané úlohy.', ['stav_np' => !empty($event['settings']['hints']) ? tr('zapnuté') : tr('vypnuté'), 'procent' => (int)round(100 * (float)$event['settings']['hint_penalty'])])) ?></p></section>
          <?php endif; ?>
        </main>
        <aside class="arn58e-side" aria-label="<?= e(tr('Žebříček CTF')) ?>">
          <?php if ($event['mode'] === 'teams'): ?>
            <section class="arn58e-card"><h2><?= e(tr('Týmy')) ?></h2><ol class="arn58e-board"><?php if ($board['teams'] === []): ?><li class="is-empty"><?= e(tr('Zatím nikdo nemá body.')) ?></li><?php endif; foreach ($board['teams'] as $t): ?><li class="<?= !empty($t['me']) ? 'is-me' : '' ?>"><b><?= $t['rank'] !== null ? (int)$t['rank'] . '.' : '–' ?></b> <?= arn58ev_h((string)$t['name']) ?> <span><?= e(tr('{n} b', ['n' => (int)$t['points']])) ?></span></li><?php endforeach; ?></ol></section>
          <?php endif; ?>
          <section class="arn58e-card"><h2><?= e(tr('Pořadí')) ?></h2><ol class="arn58e-board"><?php if ($board['rows'] === []): ?><li class="is-empty"><?= e(tr('Zatím nikdo nemá body. Buď první!')) ?></li><?php endif; foreach ($board['rows'] as $row): ?><li class="<?= !empty($row['me']) ? 'is-me' : '' ?>"><b><?= $row['rank'] !== null ? (int)$row['rank'] . '.' : '–' ?></b> <?= arn58ev_h((string)$row['name']) ?> <span><?= e(tr('{n} b', ['n' => (int)$row['points']])) ?></span></li><?php endforeach; ?></ol></section>
        </aside>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    <p class="arn58e-foot"><?= e(tr('Terminál je simulace – nic se nespouští doopravdy. Vlajky jsou jen tvoje – kód spolužáka nikdy neprojde.')) ?> <a href="?view=lab">← <?= e(tr('Zpět do Labu')) ?></a></p>
    </div>
    <?php
    render_footer();
}

// =============================================================================
// CTF TÝDEN – UČITEL  (?tab=ctf)
// =============================================================================

function arena58_ctf_render_teacher_tab(string $classId, string $csrf): void
{
    $classes = arn58ev_all_classes();
    if (!in_array($classId, $classes, true) && $classes !== []) $classId = $classes[0];
    $now = arena58_ctf_now();
    $events = arena58_ctf_events_for_class($classId);
    // v59: učitel vidí jen akce, jejichž všechny třídy má v rozsahu (smíšená akce 2.A+3.A není pro učitele jen 2.A).
    if (function_exists('teacher59_can_classes')) {
        $events = array_values(array_filter($events, static fn(array $e): bool => teacher59_can_classes(array_map('strval', (array)($e['class_ids'] ?? [])))));
    }
    $wanted = is_string($_GET['event'] ?? null) ? $_GET['event'] : '';
    $selected = null;
    foreach ($events as $event) if ((string)$event['id'] === $wanted) $selected = $event;
    $selected ??= $events[0] ?? null;
    ?>
    <div class="t52 arn58e arn58e-teacher">
      <header class="t52-page-head"><div><span class="t52-kicker">Aréna · CTF týden</span><h1>Sezónní CTF</h1><p>Jedna akce může běžet pro víc tříd zároveň – každá třída má ale vlastní žebříček a týmy, protože se neučí to samé.</p></div></header>
      <?php arena58_ctf_render_create_form($csrf, $classes); ?>
      <?php if ($selected !== null): ?>
        <?php arena58_ctf_render_event_panel($selected, $classId, $now, $csrf); ?>
      <?php endif; ?>
      <section class="arn58e-card"><h2>Akce třídy <?= arn58ev_h(arn58ev_class_label($classId)) ?></h2>
        <?php if ($events === []): ?><p class="arn58e-muted">Zatím žádná CTF akce.</p><?php endif; ?>
        <ul class="arn58e-list">
          <?php foreach ($events as $event): $st = arena58_ctf_status($event, $now); ?>
            <li class="<?= $selected !== null && $selected['id'] === $event['id'] ? 'is-selected' : '' ?>">
              <a href="?tab=ctf&class=<?= rawurlencode($classId) ?>&event=<?= rawurlencode((string)$event['id']) ?>"><span class="arn58e-badge is-<?= arn58ev_h($st) ?>"><?= arn58ev_h($st === 'live' ? 'Běží' : ($st === 'finished' ? 'Skončil' : 'Připravený')) ?></span> <strong><?= arn58ev_h((string)$event['title']) ?></strong></a>
              <small class="arn58e-muted"><?= implode(', ', array_map('arn58ev_class_label', (array)$event['class_ids'])) ?> · <?= (int)$event['duration_days'] ?> dní</small>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
}

function arena58_ctf_render_create_form(string $csrf, array $classes): void
{
    ?>
    <section class="arn58e-card" aria-labelledby="arn58e-ctf-create-title">
      <h2 id="arn58e-ctf-create-title">Nová CTF akce</h2>
      <form method="post" class="arn58e-form">
        <?php arn58ev_hidden($csrf, 'arena58_ctf_create', null); ?>
        <label>Název<input name="title" maxlength="80" placeholder="např. Podzimní CTF týden"></label>
        <fieldset><legend>Třídy</legend>
          <?php foreach ($classes as $cid): ?><label class="arn58e-check"><input type="checkbox" name="class_ids[]" value="<?= arn58ev_h($cid) ?>"> <?= arn58ev_h(arn58ev_class_label($cid)) ?></label><?php endforeach; ?>
        </fieldset>
        <div class="arn58e-fields">
          <label>Délka (dní)<input type="number" name="duration_days" min="<?= ARENA58_CTF_DAYS_MIN ?>" max="<?= ARENA58_CTF_DAYS_MAX ?>" value="<?= ARENA58_CTF_DAYS_DEFAULT ?>"></label>
          <label>Režim<select name="mode"><option value="solo">Každý sám</option><option value="teams">Týmy (vyrovnané)</option></select></label>
          <label>Velikost týmu<select name="team_size"><option value="2">2</option><option value="3" selected>3</option><option value="4">4</option></select></label>
          <label>Srážka za nápovědu<select name="hint_penalty"><?php foreach ([0.1, 0.2, 0.3, 0.4, 0.5] as $p): ?><option value="<?= $p ?>"<?= $p === 0.2 ? ' selected' : '' ?>><?= (int)round($p * 100) ?> %</option><?php endforeach; ?></select></label>
          <label>Jména na žebříčku<select name="names"><option value="initials">Jméno + iniciála (doporučeno)</option><option value="full">Celá jména</option><option value="anon">Anonymně</option></select></label>
          <label>Zmrazit žebříček před koncem<select name="freeze_before_end_min"><?php foreach (ARENA58_CTF_FREEZE_OPTIONS as $m): ?><option value="<?= $m ?>"><?= $m === 0 ? 'Nikdy' : $m . ' min před koncem' ?></option><?php endforeach; ?></select></label>
        </div>
        <label class="arn58e-check"><input type="checkbox" name="hints" value="1" checked> Povolit nápovědy</label>
        <div class="arn58e-actions"><button class="btn secondary" type="submit">Připravit</button><button class="btn primary" type="submit" name="start_now" value="1">Vytvořit a hned spustit ▶</button></div>
      </form>
    </section>
    <?php
}

function arena58_ctf_render_event_panel(array $event, string $classId, int $now, string $csrf): void
{
    $status = arena58_ctf_status($event, $now);
    $eventId = (string)$event['id'];
    $data = in_array($classId, (array)$event['class_ids'], true) ? arena58_ctf_teacher_data($eventId, $classId, $now) : null;
    $poll = '?tab=ctf&ctf_poll=1&event=' . rawurlencode($eventId) . '&class=' . rawurlencode($classId);
    ?>
    <section class="arn58e-card arn58e-panel" data-arn58e-ctf-panel data-poll="<?= arn58ev_h($poll) ?>">
      <header class="arn58e-panel-head">
        <div><span class="arn58e-badge is-<?= arn58ev_h($status) ?>"><?= arn58ev_h($status === 'live' ? 'Běží' : ($status === 'finished' ? 'Skončil' : 'Připravený')) ?></span><h2><?= arn58ev_h((string)$event['title']) ?></h2>
        <p class="arn58e-muted small">Třídy: <?= implode(', ', array_map('arn58ev_class_label', (array)$event['class_ids'])) ?> · <?= (int)$event['duration_days'] ?> dní · <?= $event['mode'] === 'teams' ? 'týmy po ' . (int)$event['team_size'] : 'jednotlivci' ?></p></div>
        <div class="arn58e-actions">
          <?php if ($status === 'draft'): ?><form method="post"><?php arn58ev_hidden($csrf, 'arena58_ctf_start', $classId, 'event', $eventId); ?><button class="btn primary" type="submit">▶ Spustit</button></form><?php endif; ?>
          <?php if ($status === 'live'): ?>
            <form method="post"><?php arn58ev_hidden($csrf, 'arena58_ctf_extend', $classId, 'event', $eventId); ?><button class="btn secondary" type="submit">+1 den</button></form>
            <form method="post" data-arn58e-confirm="Opravdu ukončit akci teď?"><?php arn58ev_hidden($csrf, 'arena58_ctf_stop', $classId, 'event', $eventId); ?><button class="btn secondary" type="submit">■ Ukončit</button></form>
          <?php endif; ?>
          <?php if ($status !== 'live'): ?><form method="post" data-arn58e-confirm="Smazat akci?"><?php arn58ev_hidden($csrf, 'arena58_ctf_delete', $classId, 'event', $eventId); ?><button class="link-button danger" type="submit">Smazat</button></form><?php endif; ?>
        </div>
      </header>
      <?php if ($data === null): ?>
        <p class="arn58e-muted">Vyber třídu z téhle akce, ať vidíš žebříček.</p>
      <?php else: ?>
        <div class="arn58e-panel-grid">
          <div class="arn58e-block arn58e-span2"><h3>Pořadí (<?= (int)$data['participants'] ?> zapojených)</h3>
            <div class="arn58e-table-wrap"><table class="arn58e-table"><thead><tr><th scope="col">#</th><th scope="col">Žák</th><th scope="col">Body</th><th scope="col">Vyřešeno</th></tr></thead><tbody data-arn58e-t-board>
              <?php foreach ($data['board'] as $row): ?><tr><td><?= $row['rank'] !== null ? (int)$row['rank'] . '.' : '–' ?></td><td><?= arn58ev_h((string)$row['name']) ?></td><td><?= (int)$row['points'] ?></td><td><?= (int)$row['solved'] ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
          </div>
          <div class="arn58e-block"><h3>Úlohy – aktuální tier</h3><ul class="arn58e-list" data-arn58e-t-levels>
            <?php foreach ($data['levels'] as $l): ?><li><strong><?= arn58ev_h((string)$l['title']) ?></strong> · <?= (int)$l['solves'] ?>× vyřešeno · teď <?= (int)$l['current_tier'] ?> % bodů<?= $l['first'] !== null ? ' · první: ' . arn58ev_h((string)$l['first']) : '' ?></li><?php endforeach; ?>
          </ul></div>
          <div class="arn58e-block"><h3>Cizí kódy</h3><ul class="arn58e-list is-alert" data-arn58e-t-alerts>
            <?php if ($data['alerts'] === []): ?><li class="is-empty">Žádné.</li><?php endif; foreach ($data['alerts'] as $a): ?><li><?= arn58ev_time_tag($a['at']) ?> <strong><?= arn58ev_h((string)$a['name']) ?></strong> zkusil(a) cizí kód v <em><?= arn58ev_h((string)$a['level']) ?></em></li><?php endforeach; ?>
          </ul></div>
          <div class="arn58e-block arn58e-span2"><h3>Dění</h3><ul class="arn58e-list arn58e-feed" data-arn58e-t-feed>
            <?php foreach ($data['feed'] as $f): ?><li><?= arn58ev_time_tag($f['at']) ?> <strong><?= arn58ev_h((string)$f['name']) ?></strong> <?= $f['kind'] === 'solve' ? 'vyřešil(a)' : 'zkusil(a) cizí kód v' ?> <em><?= arn58ev_h((string)$f['level']) ?></em><?= $f['kind'] === 'solve' ? ' (+' . (int)$f['points'] . ' b' . (!empty($f['first']) ? ', první krev!' : '') . ')' : '' ?></li><?php endforeach; ?>
          </ul></div>
        </div>
      <?php endif; ?>
    </section>
    <?php
}

// =============================================================================
// INCIDENTY – ŽÁK  (?view=incident)
// =============================================================================

function arena58_inc_render_student(string $classId, array $module): void
{
    $studentKey = function_exists('adaptive_student_key') ? adaptive_student_key($classId) : '';
    $session = arena58_inc_live_for_class($classId);
    render_header(tr('Incidenty'), $module);
    arena58_events_assets();
    ?>
    <div class="t52 arn58e arn58e-inc">
    <?php if ($session === null): ?>
      <section class="arn58e-card arn58e-empty"><h1><?= e(tr('Incidenty')) ?></h1><p><?= e(tr('Zatím tu není žádná vyhlášená směna. Zeptej se učitele, kdy začne další.')) ?></p><p><a href="?view=lab">← <?= e(tr('Zpět do Labu')) ?></a></p></section>
    <?php else:
        $now = arena58_inc_now();
        $status = arena58_inc_status($session, $now);
        $stateKey = arena58_inc_state_key(['id' => (string)$session['id'], 'student' => $studentKey]);
        $rows = arena58_inc_scenario_rows($session, $stateKey, $now);
        $wanted = isset($_GET['scenar']) && is_string($_GET['scenar']) ? (string)$_GET['scenar'] : '';
        $selectedLevel = $wanted !== '' ? lab57_level($wanted) : null;
        $selectedRow = null;
        foreach ($rows as $row) if ($row['id'] === $wanted) $selectedRow = $row;
        $base = '?view=incident';
        $statusLabel = $status === 'live' ? tr('Směna běží') : ($status === 'finished' ? tr('Směna skončila') : tr('Připravuje se'));
    ?>
      <header class="arn58e-head">
        <div><span class="t52-kicker"><?= e(tr('Incidenty · {stav}', ['stav' => $statusLabel])) ?></span><h1><?= arn58ev_h((string)$session['title']) ?></h1>
        <p><?= e(tr('Každý scénář má vlastní odpočet {min} minut od chvíle, kdy ho spustíš. Body dostaneš až za postmortem – i nedokončená oprava se počítá, pokud napíšeš, cos zkusil(a).', ['min' => ARENA58_INC_MINUTES])) ?></p></div>
      </header>
      <?php if ($status === 'draft'): ?>
        <section class="arn58e-card"><p><?= e(tr('Směna ještě nezačala.')) ?></p></section>
      <?php elseif ($selectedRow !== null && $selectedLevel !== null): ?>
        <?php arena58_inc_render_scenario_detail((string)$session['id'], $classId, $selectedLevel, $selectedRow, $base); ?>
      <?php else: ?>
        <div class="arn58e-scenarios">
          <?php foreach ($rows as $row): ?>
            <article class="arn58e-card arn58e-scenario<?= $row['solved'] ? ' is-solved' : '' ?>">
              <h2><?= arn58ev_h($row['title']) ?></h2>
              <p class="arn58e-pager"><?= arn58ev_h($row['pager']) ?></p>
              <p class="arn58e-muted small">
                <?php if ($row['postmortem'] !== null): ?><?= e(tr('Postmortem odevzdán')) ?><?= (int)$row['postmortem']['points'] > 0 ? ' · ' . e(tr('+{body} b', ['body' => (int)$row['postmortem']['points']])) : ' · ' . e(tr('bez bodů (nestihl se opravit v čase)')) ?>
                <?php elseif ($row['solved']): ?><?= e(tr('Technicky vyřešeno – napiš postmortem pro body')) ?>
                <?php elseif ($row['expired']): ?><?= e(tr('Čas vypršel – napiš postmortem')) ?>
                <?php elseif ($row['started']): ?><?= e(tr('Rozjeto{pauza}, zbývá {cas}', ['pauza' => $row['paused'] ? tr(' (pauza)') : '', 'cas' => arn58ev_duration_text((int)$row['remaining'])])) ?>
                <?php else: ?><?= e(tr('Ještě nezačato')) ?>
                <?php endif; ?>
              </p>
              <a class="btn <?= $row['started'] ? 'secondary' : 'primary' ?>" href="<?= arn58ev_h($base . '&scenar=' . rawurlencode($row['id'])) ?>"><?= $row['started'] ? e(tr('Pokračovat')) : e(tr('Otevřít scénář')) ?></a>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
    <p class="arn58e-foot"><?= e(tr('Terminál je simulace – nic se nespouští doopravdy.')) ?> <a href="?view=lab">← <?= e(tr('Zpět do Labu')) ?></a></p>
    </div>
    <?php
    render_footer();
}

function arena58_inc_render_scenario_detail(string $sessionId, string $classId, array $level, array $row, string $base): void
{
    $csrf = function_exists('csrf_token') ? csrf_token() : '';
    ?>
    <section class="arn58e-card arn58e-incident-detail" data-arn58e-incident data-session="<?= arn58ev_h($sessionId) ?>" data-scenario="<?= arn58ev_h($row['id']) ?>" data-api="arena_v58_events_api.php" data-csrf="<?= arn58ev_h($csrf) ?>">
      <p class="arn58e-pager" role="status"><?= arn58ev_h($row['pager']) ?></p>
      <?php if (!$row['started']): ?>
        <form method="post" data-arn58e-inc-start>
          <input type="hidden" name="csrf" value="<?= arn58ev_h($csrf) ?>"><input type="hidden" name="op" value="start"><input type="hidden" name="session" value="<?= arn58ev_h($sessionId) ?>"><input type="hidden" name="scenario" value="<?= arn58ev_h($row['id']) ?>">
          <p><?= e(tr('Jakmile klikneš na Spustit, začne běžet tvůj vlastní odpočet {min} minut.', ['min' => ARENA58_INC_MINUTES])) ?></p>
          <button class="btn primary" type="submit">▶ <?= e(tr('Spustit scénář')) ?></button>
        </form>
      <?php else: ?>
        <?php if (!$row['solved'] && !$row['expired']): arn58ev_render_countdown((int)($row['remaining'] ?? 0), tr('Zbývá'), 'is-incident'); ?>
          <button type="button" class="btn secondary" data-arn58e-inc-pause data-paused="<?= $row['paused'] ? '1' : '0' ?>"><?= $row['paused'] ? '▶ ' . e(tr('Pokračovat')) : '⏸ ' . e(tr('Pauza')) ?></button>
        <?php endif; ?>
        <?php if ($row['solved'] && $row['postmortem'] === null): ?><p class="arn58e-note">✔ <?= e(tr('Technicky vyřešeno! Teď napiš krátký postmortem, ať dostaneš body.')) ?></p><?php endif; ?>
        <?php if ($row['expired'] && $row['postmortem'] === null): ?><p class="arn58e-note"><?= e(tr('Čas vypršel. Postmortem pořád napiš – i z nedokončené opravy je co se učit.')) ?></p><?php endif; ?>
        <?php if (!$row['solved'] && !$row['expired']): ?>
          <?php if (function_exists('lab57_render_workspace')): lab57_render_workspace($level, 'incident:' . $sessionId, ['next_url' => $base, 'compact' => true]); endif; ?>
        <?php endif; ?>
        <?php if ($row['postmortem'] !== null): ?>
          <section class="arn58e-postmortem-done"><h3><?= e(tr('Tvůj postmortem')) ?></h3>
            <p><strong><?= e(tr('Příčina:')) ?></strong> <?= nl2br(arn58ev_h((string)$row['postmortem']['cause'])) ?></p>
            <p><strong><?= e(tr('Oprava:')) ?></strong> <?= nl2br(arn58ev_h((string)$row['postmortem']['fix'])) ?></p>
            <p><strong><?= e(tr('Prevence:')) ?></strong> <?= nl2br(arn58ev_h((string)$row['postmortem']['prevention'])) ?></p>
            <?php if (!empty($row['postmortem']['teacher_note'])): ?><p class="arn58e-teacher-note"><strong><?= e(tr('Poznámka učitele:')) ?></strong> <?= nl2br(arn58ev_h((string)$row['postmortem']['teacher_note'])) ?></p><?php endif; ?>
          </section>
        <?php elseif ($row['solved'] || $row['expired']): ?>
          <form data-arn58e-inc-postmortem>
            <input type="hidden" name="csrf" value="<?= arn58ev_h($csrf) ?>"><input type="hidden" name="op" value="postmortem"><input type="hidden" name="session" value="<?= arn58ev_h($sessionId) ?>"><input type="hidden" name="scenario" value="<?= arn58ev_h($row['id']) ?>">
            <label><?= e(tr('Příčina (co se pokazilo?)')) ?><textarea name="cause" required minlength="<?= ARENA58_INC_POSTMORTEM_MIN ?>" rows="2"></textarea></label>
            <label><?= e(tr('Oprava (co jsi udělal(a)?)')) ?><textarea name="fix" required minlength="<?= ARENA58_INC_POSTMORTEM_MIN ?>" rows="2"></textarea></label>
            <label><?= e(tr('Prevence (jak tomu příště předejít?)')) ?><textarea name="prevention" required minlength="<?= ARENA58_INC_POSTMORTEM_MIN ?>" rows="2"></textarea></label>
            <button class="btn primary" type="submit"><?= e(tr('Odeslat postmortem')) ?></button>
          </form>
        <?php endif; ?>
      <?php endif; ?>
      <p><a href="<?= arn58ev_h($base) ?>">← <?= e(tr('Zpět na seznam scénářů')) ?></a></p>
    </section>
    <?php
}

// =============================================================================
// INCIDENTY – UČITEL  (?tab=incidenty)
// =============================================================================

function arena58_inc_render_teacher_tab(string $classId, string $csrf): void
{
    $classes = arn58ev_all_classes();
    if (!in_array($classId, $classes, true) && $classes !== []) $classId = $classes[0];
    $now = arena58_inc_now();
    $sessions = arena58_inc_sessions_for_class($classId);
    $wanted = is_string($_GET['session'] ?? null) ? $_GET['session'] : '';
    $selected = null;
    foreach ($sessions as $row) if ((string)$row['id'] === $wanted) $selected = $row;
    $selected ??= $sessions[0] ?? null;
    ?>
    <div class="t52 arn58e arn58e-teacher">
      <header class="t52-page-head"><div><span class="t52-kicker">Aréna · Incidenty</span><h1>Incidentní směny</h1><p>Časově tlačené opravy s postmortemem. Body dá až odevzdaný postmortem – hodnotí se i reflexe, ne jen rychlost.</p></div></header>
      <nav class="arn58e-classes" aria-label="Třída"><?php foreach ($classes as $cid): ?><a class="<?= $cid === $classId ? 'active' : '' ?>" href="?tab=incidenty&class=<?= rawurlencode($cid) ?>"><?= arn58ev_h(arn58ev_class_label($cid)) ?></a><?php endforeach; ?></nav>
      <?php arena58_inc_render_create_form($classId, $csrf); ?>
      <?php if ($selected !== null): ?><?php arena58_inc_render_session_panel($selected, $now, $csrf); ?><?php endif; ?>
      <section class="arn58e-card"><h2>Směny třídy</h2>
        <?php if ($sessions === []): ?><p class="arn58e-muted">Zatím žádná směna.</p><?php endif; ?>
        <ul class="arn58e-list">
          <?php foreach ($sessions as $row): $st = arena58_inc_status($row, $now); ?>
            <li class="<?= $selected !== null && $selected['id'] === $row['id'] ? 'is-selected' : '' ?>"><a href="?tab=incidenty&class=<?= rawurlencode($classId) ?>&session=<?= rawurlencode((string)$row['id']) ?>"><span class="arn58e-badge is-<?= arn58ev_h($st) ?>"><?= arn58ev_h($st === 'live' ? 'Běží' : ($st === 'finished' ? 'Skončila' : 'Připravená')) ?></span> <strong><?= arn58ev_h((string)$row['title']) ?></strong></a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
}

function arena58_inc_render_create_form(string $classId, string $csrf): void
{
    ?>
    <section class="arn58e-card">
      <h2>Nová incidentní směna</h2>
      <form method="post" class="arn58e-form">
        <?php arn58ev_hidden($csrf, 'arena58_inc_create', $classId); ?>
        <label>Název<input name="title" maxlength="80" placeholder="např. Opravárenská směna"></label>
        <div class="arn58e-fields">
          <label>Délka okna (hodin)<input type="number" name="duration_hours" min="<?= ARENA58_INC_HOURS_MIN ?>" max="<?= ARENA58_INC_HOURS_MAX ?>" value="<?= ARENA58_INC_HOURS_DEFAULT ?>"></label>
          <label>Režim<select name="mode"><option value="solo">Každý sám</option><option value="teams">Týmy (vyrovnané)</option></select></label>
          <label>Velikost týmu<select name="team_size"><option value="2" selected>2</option><option value="3">3</option><option value="4">4</option></select></label>
          <label>Srážka za nápovědu<select name="hint_penalty"><?php foreach ([0.1, 0.2, 0.3, 0.4, 0.5] as $p): ?><option value="<?= $p ?>"<?= $p === 0.2 ? ' selected' : '' ?>><?= (int)round($p * 100) ?> %</option><?php endforeach; ?></select></label>
          <label>Jména<select name="names"><option value="initials">Jméno + iniciála</option><option value="full">Celá jména</option><option value="anon">Anonymně</option></select></label>
        </div>
        <label class="arn58e-check"><input type="checkbox" name="hints" value="1" checked> Povolit nápovědy</label>
        <div class="arn58e-actions"><button class="btn secondary" type="submit">Připravit</button><button class="btn primary" type="submit" name="start_now" value="1">Vytvořit a hned spustit ▶</button></div>
      </form>
    </section>
    <?php
}

function arena58_inc_render_session_panel(array $session, int $now, string $csrf): void
{
    $status = arena58_inc_status($session, $now);
    $sessionId = (string)$session['id'];
    $classId = (string)$session['class_id'];
    $data = arena58_inc_teacher_data($sessionId, $now);
    $poll = '?tab=incidenty&inc_poll=1&session=' . rawurlencode($sessionId);
    ?>
    <section class="arn58e-card arn58e-panel" data-arn58e-inc-panel data-poll="<?= arn58ev_h($poll) ?>">
      <header class="arn58e-panel-head">
        <div><span class="arn58e-badge is-<?= arn58ev_h($status) ?>"><?= arn58ev_h($status === 'live' ? 'Běží' : ($status === 'finished' ? 'Skončila' : 'Připravená')) ?></span><h2><?= arn58ev_h((string)$session['title']) ?></h2></div>
        <div class="arn58e-actions">
          <?php if ($status === 'draft'): ?><form method="post"><?php arn58ev_hidden($csrf, 'arena58_inc_start', $classId, 'session', $sessionId); ?><button class="btn primary" type="submit">▶ Spustit</button></form><?php endif; ?>
          <?php if ($status === 'live'): ?>
            <form method="post"><?php arn58ev_hidden($csrf, 'arena58_inc_extend', $classId, 'session', $sessionId); ?><button class="btn secondary" type="submit">+24 h</button></form>
            <form method="post" data-arn58e-confirm="Ukončit směnu?"><?php arn58ev_hidden($csrf, 'arena58_inc_stop', $classId, 'session', $sessionId); ?><button class="btn secondary" type="submit">■ Ukončit</button></form>
          <?php endif; ?>
          <?php if ($status !== 'live'): ?><form method="post" data-arn58e-confirm="Smazat směnu?"><?php arn58ev_hidden($csrf, 'arena58_inc_delete', $classId, 'session', $sessionId); ?><button class="link-button danger" type="submit">Smazat</button></form><?php endif; ?>
        </div>
      </header>
      <?php if ($data === null): ?><p class="arn58e-muted">Žádná data.</p><?php else: ?>
        <p class="arn58e-muted small">Čeká na komentář: <?= (int)$data['pending_review'] ?></p>
        <div class="arn58e-table-wrap"><table class="arn58e-table"><thead><tr><th scope="col">Kdo</th><th scope="col">Scénář</th><th scope="col">Stav</th><th scope="col">Postmortem</th><th scope="col">Body</th><th scope="col">Komentář</th></tr></thead><tbody data-arn58e-inc-rows>
        <?php foreach ($data['attempts'] as $row): ?>
          <tr>
            <td><?= arn58ev_h($row['name']) ?></td><td><?= arn58ev_h($row['scenario']) ?></td>
            <td><?= $row['solved'] ? 'vyřešeno' : ($row['expired'] ? 'čas vypršel' : 'běží') ?></td>
            <td><?php if (is_array($row['postmortem'])): ?><details><summary>zobrazit</summary><p><b>Příčina:</b> <?= nl2br(arn58ev_h((string)$row['postmortem']['cause'])) ?></p><p><b>Oprava:</b> <?= nl2br(arn58ev_h((string)$row['postmortem']['fix'])) ?></p><p><b>Prevence:</b> <?= nl2br(arn58ev_h((string)$row['postmortem']['prevention'])) ?></p></details><?php else: ?>–<?php endif; ?></td>
            <td><?= is_array($row['postmortem']) ? (int)$row['postmortem']['points'] : '–' ?></td>
            <td><?php if (is_array($row['postmortem'])): ?><form method="post" class="arn58e-comment-form"><?php arn58ev_hidden($csrf, 'arena58_inc_comment', $classId, 'session', $sessionId); ?><input type="hidden" name="state_key" value="<?= arn58ev_h($row['state_key']) ?>"><input type="hidden" name="scenario" value="<?= arn58ev_h($row['scenario_id']) ?>"><input name="note" maxlength="300" placeholder="formativní poznámka" value="<?= arn58ev_h((string)($row['postmortem']['teacher_note'] ?? '')) ?>"><button class="btn secondary" type="submit">Uložit</button></form><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </section>
    <?php
}

/** v58 · styly a skript CTF týdne a Incidentů (odpočty, polling) – vloží se za hlavičku žákovské stránky. */
function arena58_events_assets(): void
{
    $css = edu_css_href('assets/arena-events-v58.css', function_exists('asset_url') ? asset_url('assets/arena-events-v58.css') : 'assets/arena-events-v58.css');
    $js = function_exists('asset_url') ? asset_url('assets/arena-events-v58.js') : 'assets/arena-events-v58.js';
    echo '<link rel="stylesheet" href="' . htmlspecialchars($css, ENT_QUOTES) . '"><script src="' . htmlspecialchars($js, ENT_QUOTES) . '" defer></script>';
}
