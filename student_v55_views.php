<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v55 · studentské komponenty: LVL prstenec, sbírka odznaků, běh hodiny krok za krokem. */

// ---------------------------------------------------------------------------
// LVL prstenec v menu
// ---------------------------------------------------------------------------

function v55_render_level_chip(string $classId): void
{
    $lvl = v55_level_state($classId);
    ?>
    <a class="v55-lvl" href="?view=profile" data-v55-lvl
       title="<?= e(tr('Level {level} · {current}/{next} XP · do dalšího levelu zbývá {remaining} XP', ['level' => $lvl['level'], 'current' => $lvl['current'], 'next' => $lvl['next'], 'remaining' => $lvl['remaining']])) ?>"
       style="--v55-lvl-percent: <?= $lvl['percent'] ?>">
      <span class="v55-lvl-ring" aria-hidden="true"><b>LVL</b><i><?= $lvl['level'] ?></i></span>
      <span class="v55-lvl-meta"><strong><?= $lvl['current'] ?> / <?= $lvl['next'] ?> XP</strong><small><?= e(tr('do levelu {level} zbývá {remaining} XP', ['level' => $lvl['level'] + 1, 'remaining' => $lvl['remaining']])) ?></small></span>
    </a>
    <?php
}

// ---------------------------------------------------------------------------
// Sbírka odznaků v profilu
// ---------------------------------------------------------------------------

function v55_render_badge_card(array $b): void
{
    ?>
    <article class="v55-badge<?= $b['earned'] ? ' earned' : '' ?> rarity-<?= e($b['rarity']) ?>">
      <span class="v55-badge-mark" aria-hidden="true"><?= e($b['mark']) ?></span>
      <div class="v55-badge-body">
        <strong><?= e($b['title']) ?></strong>
        <small><?= e($b['earned'] ? $b['text'] : $b['condition']) ?></small>
        <?php if ($b['earned']): ?>
          <span class="v55-badge-state ok"><?= e(tr('✓ Získáno')) ?><?= $b['earned_at'] !== '' ? ' · ' . e(date('j. n. Y', strtotime($b['earned_at']))) : '' ?></span>
        <?php else: ?>
          <span class="v55-badge-bar" role="img" aria-label="<?= e(tr('Postup {percent} %', ['percent' => (int)$b['percent']])) ?>"><i style="width: <?= (int)$b['percent'] ?>%"></i></span>
          <span class="v55-badge-state"><?= (int)$b['percent'] ?> %<?= $b['progress_label'] !== '' ? ' · ' . e($b['progress_label']) : '' ?></span>
        <?php endif; ?>
      </div>
    </article>
    <?php
}

function v55_render_badge_board(string $classId, array $module, array $simulationMap = []): void
{
    $board = v55_badge_board($classId, $module, $simulationMap);
    $lvl = v55_level_state($classId);
    ?>
    <section class="dashboard-panel v55-badges" id="odznaky">
      <header class="v55-section-head">
        <div><span class="t52-kicker"><?= e(tr('Sbírka')) ?></span><h2><?= e(tr('Získávání odznaků')) ?></h2><p><?= e(tr('Odznak je vzácná trofej. Tady vidíš, co už máš a co ti k dalšímu chybí.')) ?></p></div>
        <div class="v55-badge-score"><strong><?= count($board['earned']) ?></strong><small><?= e(tr('z {total} odznaků', ['total' => (int)$board['total']])) ?></small></div>
      </header>
      <?php if ($board['earned']): ?>
        <h3 class="v55-badge-group"><?= e(tr('Získané')) ?></h3>
        <div class="v55-badge-grid"><?php foreach ($board['earned'] as $b) v55_render_badge_card($b); ?></div>
      <?php endif; ?>
      <?php if ($board['progress']): ?>
        <h3 class="v55-badge-group"><?= e(tr('Nejblíž máš k těmto')) ?></h3>
        <div class="v55-badge-grid"><?php foreach (array_slice($board['progress'], 0, 6) as $b) v55_render_badge_card($b); ?></div>
      <?php endif; ?>
      <?php if ($board['locked']): ?>
        <details class="v55-badge-more"><summary><?= e(tr('Zbytek sbírky ({n})', ['n' => count($board['locked'])])) ?></summary>
          <div class="v55-badge-grid"><?php foreach ($board['locked'] as $b) v55_render_badge_card($b); ?></div>
        </details>
      <?php endif; ?>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Dnešní hodina krok za krokem
// ---------------------------------------------------------------------------

function v55_render_runner(string $classId, array $module, array $session, array $modules, array $schoolYear, string $flash): void
{
    $studentKey = adaptive_student_key($classId);
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    $sessionId = (string)$session['id'];
    $sub = sess53_submission($sessionId, $studentKey);
    $completedTest = is_array($GLOBALS['completedTestResult'] ?? null) ? $GLOBALS['completedTestResult'] : null;
    $plan = v55_lesson_plan($classId, $module, $session, $sub, $completedTest);
    $window = v55_block_window($schoolYear, $classId, (string)$session['date']);
    $balance = pts53_balance($classId, $studentKey);
    $lessonNo = max(1, (int)($session['lesson_number'] ?? 1));
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $bonus = v55_bonus_get($sessionId, $studentKey);
    $tasks = (array)($session['tasks'] ?? []);
    $checks = array_map('strval', (array)($sub['checks'] ?? []));

    render_header(tr('Dnešní hodina'), $module);
    $s55SessTs = strtotime((string)$session['date']);
    $s55SessDate = $s55SessTs !== false ? edu_weekday($s55SessTs) . ' ' . edu_date($s55SessTs, 'date') : (string)$session['date'];
    ?>
    <div class="t52 s53 v55-runner" data-v55-runner data-session="<?= e($sessionId) ?>" data-student="<?= e(substr(hash('sha256', $studentKey), 0, 16)) ?>">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>

      <header class="v55-run-head">
        <div>
          <span class="t52-kicker"><?= e((string)$module['name']) ?> · <?= e($s55SessDate) ?> · <?= e($window['range']) ?></span>
          <h1><?= e((string)$session['title']) ?></h1>
          <p><?= e((string)$session['goal']) ?></p>
        </div>
        <div class="v55-run-meta">
          <span class="s53-points"><b><?= $balance ?></b> <?= e(pts53_points_label($balance)) ?></span>
          <?php if ($window['running']): ?>
            <span class="v55-clock" data-v55-clock data-v55-clock-label="<?= e(tr('do konce hodiny')) ?>" data-end="<?= (int)$window['end_ts'] ?>"><b>—</b><small><?= e(tr('do konce hodiny')) ?></small></span>
          <?php elseif ($window['before']): ?>
            <span class="v55-clock"><b><?= e($window['start']) ?></b><small><?= e(tr('hodina začíná')) ?></small></span>
          <?php else: ?>
            <span class="v55-clock"><b><?= e($window['end']) ?></b><small><?= e(tr('hodina skončila')) ?></small></span>
          <?php endif; ?>
        </div>
      </header>

      <ol class="v55-stepbar" aria-label="<?= e(tr('Postup hodinou')) ?>">
        <?php foreach ($plan['steps'] as $i => $s): $state = !empty($s['done']) ? 'done' : ($i === $plan['current'] ? 'current' : 'todo'); ?>
          <li class="<?= $state ?>"><b><?= $state === 'done' ? '✓' : ($i + 1) ?></b><span><?= e((string)$s['title']) ?></span></li>
        <?php endforeach; ?>
      </ol>

      <?php if (trim((string)($session['instructions'] ?? '')) !== ''): ?>
        <div class="t52-card s53-instructions"><span class="t52-kicker"><?= e(tr('Pokyny učitele')) ?></span><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$session['instructions'])) ?></p></div>
      <?php endif; ?>

      <?php foreach ($plan['steps'] as $i => $s):
          $isCurrent = $i === $plan['current'];
          $isDone = !empty($s['done']);
          if (!$isCurrent && !$isDone) continue; // další kroky se odemykají postupně
      ?>
      <section class="v55-step<?= $isCurrent ? ' is-current' : '' ?><?= $isDone ? ' is-done' : '' ?>" id="krok-<?= e((string)$s['id']) ?>">
        <header class="v55-step-head">
          <b><?= $isDone ? '✓' : ($i + 1) ?></b>
          <div><strong><?= e((string)$s['title']) ?></strong><small><?= e((string)$s['lead']) ?></small></div>
        </header>
        <?php if ($isCurrent): ?>
          <div class="v55-step-body">
            <?php if ((string)$s['id'] === 'work'): ?>
              <form class="v55-work" method="post" data-v55-autosave="work">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_submit"><input type="hidden" name="session" value="<?= e($sessionId) ?>">
                <div class="t52-checklist">
                  <?php foreach ($tasks as $ti => $task): $checked = in_array((string)$ti, $checks, true); ?>
                    <label><input type="checkbox" name="checks[]" value="<?= $ti ?>"<?= $checked ? ' checked' : '' ?>><span><strong><?= e((string)$task['title']) ?></strong><?php if (trim((string)($task['detail'] ?? '')) !== ''): ?><small><?= e((string)$task['detail']) ?></small><?php endif; ?></span><?php if (trim((string)($task['time'] ?? '')) !== ''): ?><em><?= e((string)$task['time']) ?></em><?php endif; ?></label>
                  <?php endforeach; ?>
                </div>
                <div class="v55-step-foot"><button class="btn primary" type="submit" name="status" value="draft"><?= e(tr('Uložit postup')) ?></button><span class="v55-autosave-note" data-v55-autosave-note><?= e(tr('Postup se ti průběžně ukládá i do prohlížeče.')) ?></span></div>
              </form>
            <?php elseif ((string)$s['id'] === 'submit'): ?>
              <form class="v55-work" method="post" data-v55-autosave="submit">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_submit"><input type="hidden" name="session" value="<?= e($sessionId) ?>">
                <?php foreach ($checks as $c): ?><input type="hidden" name="checks[]" value="<?= e($c) ?>"><?php endforeach; ?>
                <label class="u51-field"><span><?= e(tr('Co jsi zjistil/a nebo vytvořil/a?')) ?></span><textarea name="note" rows="4" placeholder="<?= e(tr('3–5 vět: co jsi udělal/a, co ti fungovalo a kde ses zasekl/a.')) ?>"><?= e((string)($sub['note'] ?? '')) ?></textarea></label>
                <label class="u51-field"><span><?= e(tr('Odkaz na práci (Canva, Figma, dokument…)')) ?></span><input name="link" maxlength="500" placeholder="https://…" value="<?= e((string)($sub['link'] ?? '')) ?>"></label>
                <div class="v55-step-foot"><button class="btn secondary" type="submit" name="status" value="draft"><?= e(tr('Uložit rozpracované')) ?></button><button class="btn primary" type="submit" name="status" value="submitted" data-v55-unlock><?= e(tr('Odevzdat učiteli')) ?></button></div>
              </form>
            <?php else: ?>
              <?php $a = $s['action'] ?? null; ?>
              <div class="v55-step-foot">
                <?php if (is_array($a) && $a['kind'] === 'post'): ?>
                  <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= e((string)$a['action']) ?>"><button class="btn primary" type="submit" data-v55-unlock><?= e((string)$a['label']) ?></button></form>
                <?php elseif (is_array($a) && $a['kind'] === 'link'): ?>
                  <a class="btn primary" href="<?= e((string)$a['href']) ?>" data-v55-allow><?= e((string)$a['label']) ?></a>
                <?php endif; ?>
                <?php if (!empty($s['confirm'])): ?>
                  <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= e((string)$s['confirm']) ?>"><input type="hidden" name="session" value="<?= e($sessionId) ?>"><button class="btn secondary" type="submit"><?= e(tr('Mám hotovo, pokračovat')) ?></button></form>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
      <?php endforeach; ?>

      <?php if ($plan['complete']): ?>
        <?php v55_render_bonus($classId, $module, $session, $window, $bonus, $sessionId); ?>
      <?php else: ?>
        <aside class="v55-next-hint"><strong><?= e(tr('Až budeš mít všechny kroky hotové')) ?></strong><span><?= e(tr('dostaneš rozšiřující zadání přesně na čas, který ti do konce hodiny zbude.')) ?></span></aside>
      <?php endif; ?>

      <section class="t52-card v55-tools">
        <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Kde pracuješ')) ?></span><strong><?= e(tr('Doporučené programy')) ?></strong></header>
        <div class="t52-tool-mini">
          <?php foreach (array_slice(array_keys($tools), 0, 3) as $tk): $tool = $tools[$tk]; ?>
            <a href="?view=tools#<?= e($tk) ?>" data-v55-allow><strong><?= e((string)$tool['name']) ?></strong><small><?= e((string)$tool['use']) ?></small></a>
          <?php endforeach; ?>
        </div>
      </section>

      <?php if (!empty($sub['teacher_comment'])): ?>
        <div class="s53-feedback"><span class="t52-kicker"><?= !empty($sub['grade']) ? e(tr('Hodnocení učitele · známka {grade} · +{points} bodů', ['grade' => (int)$sub['grade'], 'points' => (int)($sub['points'] ?? 0)])) : e(tr('Hodnocení učitele')) ?></span><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$sub['teacher_comment'])) ?></p></div>
      <?php endif; ?>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

/** Bonus: zadání přizpůsobené zbývajícímu času. Váhu hodnocení žák nikdy nevidí. */
function v55_render_bonus(string $classId, array $module, array $session, array $window, array $bonus, string $sessionId): void
{
    $lessonNo = max(1, (int)($session['lesson_number'] ?? 1));
    $lesson = ['topics' => [], 'title' => (string)$session['title']];
    $assignment = v55_bonus_assignment($classId, $module, $lesson, $window);
    $running = $bonus !== [] && (string)($bonus['status'] ?? '') === 'open';
    $submitted = $bonus !== [] && (string)($bonus['status'] ?? '') === 'submitted';
    $active = $running ? $bonus : $assignment;
    ?>
    <section class="v55-bonus<?= $submitted ? ' is-done' : '' ?>" id="bonus">
      <header class="v55-section-head">
        <div><span class="t52-kicker"><?= e(tr('Hotovo · bonus navíc')) ?></span><h2><?= e((string)$active['label']) ?></h2>
          <p><?= $submitted ? e(tr('Odevzdáno. Učitel ti dá známku a body.')) : ((string)$assignment['variant'] === 'none' ? e(tr('Hlavní část hodiny máš hotovou.')) : e((string)($assignment['note'] ?? ''))) ?></p></div>
        <?php if (!$submitted && (int)$active['minutes'] > 0): ?>
          <div class="v55-bonus-time"><strong><?= (int)$active['minutes'] ?></strong><small><?= e(tr('minut na práci')) ?></small></div>
        <?php endif; ?>
      </header>

      <?php if ($submitted): ?>
        <div class="v55-bonus-result">
          <?php if (!empty($bonus['grade'])): ?>
            <p><strong><?= e(tr('Výsledek: známka {grade}', ['grade' => (int)$bonus['grade']])) ?></strong> · <?= e(trn(['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'], (int)($bonus['points'] ?? 0))) ?></p>
            <?php if (!empty($bonus['teacher_comment'])): ?><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$bonus['teacher_comment'])) ?></p><?php endif; ?>
          <?php else: ?>
            <p><?= e(tr('Odevzdáno {time}. Výsledek uvidíš, jakmile ho učitel projde.', ['time' => date('H:i', strtotime((string)($bonus['submitted_at'] ?? 'now')))])) ?></p>
          <?php endif; ?>
        </div>
      <?php elseif ((string)$assignment['variant'] === 'none' && !$running): ?>
        <p class="v55-bonus-empty"><?= e((string)$assignment['note']) ?></p>
      <?php elseif (!$running): ?>
        <ul class="v55-bonus-preview">
          <?php foreach ((array)$assignment['tasks'] as $t): ?><li><strong><?= e((string)$t['title']) ?></strong><span><?= e((string)$t['detail']) ?></span></li><?php endforeach; ?>
        </ul>
        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v55_bonus_start"><input type="hidden" name="session" value="<?= e($sessionId) ?>">
          <button class="btn primary" type="submit"><?= e(tr('Spustit {label}', ['label' => mb_strtolower((string)$assignment['label'], 'UTF-8')])) ?></button></form>
      <?php else: ?>
        <form class="v55-work" method="post" data-v55-autosave="bonus">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v55_bonus_submit"><input type="hidden" name="session" value="<?= e($sessionId) ?>">
          <?php if (!empty($bonus['deadline_ts'])): ?>
            <p class="v55-bonus-deadline" data-v55-clock data-v55-clock-label="<?= e(tr('na dokončení bonusu')) ?>" data-end="<?= (int)$bonus['deadline_ts'] ?>"><b>—</b> <?= e(tr('na dokončení')) ?></p>
          <?php endif; ?>
          <?php foreach ((array)$bonus['tasks'] as $bi => $t): ?>
            <label class="u51-field"><span><?= ($bi + 1) ?>. <?= e((string)$t['title']) ?></span><small><?= e((string)$t['detail']) ?></small>
              <textarea name="answers[]" rows="3" placeholder="<?= e(tr('Tvoje odpověď nebo odkaz na výstup…')) ?>"><?= e((string)(($bonus['answers'][$bi] ?? ''))) ?></textarea></label>
          <?php endforeach; ?>
          <div class="v55-step-foot"><button class="btn primary" type="submit" data-v55-unlock><?= e(tr('Odevzdat bonus')) ?></button></div>
        </form>
      <?php endif; ?>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// CTA na konci stránky
// ---------------------------------------------------------------------------

function v55_render_page_cta(string $continueUrl, ?string $label = null): void
{
    $label = $label ?? tr('Pokračovat');
    ?>
    <div class="v55-page-cta" data-v55-page-cta>
      <a class="btn primary" href="<?= e($continueUrl) ?>"><?= e($label) ?></a>
      <small><?= e(tr('Další krok je vždycky jen jeden. Tenhle.')) ?></small>
    </div>
    <?php
}

/** CTA v patičce každé studentské stránky – vždy až na konci obsahu. */
function v55_footer_cta(): void
{
    if (($GLOBALS['module'] ?? null) === null) return;
    $view = (string)($GLOBALS['view'] ?? '');
    if (in_array($view, ['test', 'kb_quiz', 'hodina', 'intake', 'change_password', 'join', 'activate'], true)) return;
    $classId = current_class_id($GLOBALS['modules'] ?? []);
    if (is_string($classId) && $classId !== '' && function_exists('v56_next_step')) {
        $step = v56_next_step($classId, (array)$GLOBALS['modules'][$classId], (array)($GLOBALS['nextLessons'] ?? []), (array)($GLOBALS['extendedLessons'] ?? []), (array)($GLOBALS['schoolYear'] ?? []));
        v55_render_page_cta((string)$step['href'], (string)$step['label']);
        return;
    }
    $url = (string)($GLOBALS['v55_continue_url'] ?? '?view=dashboard');
    $label = tr('Pokračovat');
    $classId = current_class_id($GLOBALS['modules'] ?? []);
    if (is_string($classId) && $classId !== '') {
        $session = sess53_for_class_date($classId, date('Y-m-d'));
        if (is_array($session) && !empty($session['open'])) {
            $url = (string)($session['kind'] ?? '') === 'intake' ? '?view=intake' : '?view=hodina';
            $label = tr('Pokračovat v dnešní hodině');
        }
    }
    v55_render_page_cta($url, $label);
}
