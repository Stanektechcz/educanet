<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v56 · jedna stránka lekce (teorie → test → projekt → odevzdání), materiály, výsledky, profil. */

// ---------------------------------------------------------------------------
// Společné prvky
// ---------------------------------------------------------------------------

function v56_render_rail(array $state, int $lessonNo, bool $linkable = true): void
{
    ?>
    <ol class="v56-rail" aria-label="<?= e(tr('Postup lekcí')) ?>">
      <?php $i = 0; foreach ($state['phases'] as $id => $phase): $i++; $open = $phase['state'] !== 'locked'; ?>
        <li class="v56-rail-item <?= e((string)$phase['state']) ?>">
          <?php if ($linkable && $open): ?><a href="<?= e(v56_lesson_url($lessonNo, (string)$id)) ?>"><?php else: ?><span><?php endif; ?>
            <b><?= $phase['state'] === 'done' ? '✓' : $i ?></b>
            <span class="v56-rail-text"><strong><?= e((string)$phase['label']) ?></strong>
              <small><?= $phase['state'] === 'done' ? e(tr('hotovo')) : ($id === 'test' ? ($phase['progress'] > 0 ? e(tr('{n} %', ['n' => $phase['progress']])) : e(tr('neověřeno'))) : e(tr('{done} / {total}', ['done' => $phase['progress'], 'total' => $phase['total']]))) ?></small></span>
          <?php if ($linkable && $open): ?></a><?php else: ?></span><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php
}

// ---------------------------------------------------------------------------
// Stránka lekce
// ---------------------------------------------------------------------------

/**
 * Jedna a tatáž stránka pro „Dnešní hodinu“ i pro lekci z materiálů.
 * $session != null → hodina: zámek, odevzdání učiteli, bonus.
 */
function v56_render_lesson(string $classId, array $module, array $bundle, ?array $session, array $schoolYear, string $flash, string $requestedPhase = ''): void
{
    $studentKey = adaptive_student_key($classId);
    $state = v56_lesson_state($classId, $studentKey, $bundle);
    $lessonNo = (int)$bundle['number'];
    $isToday = is_array($session);
    $phase = $requestedPhase !== '' && isset($state['phases'][$requestedPhase]) && $state['phases'][$requestedPhase]['state'] !== 'locked'
        ? $requestedPhase
        : (string)($state['current'] ?? 'submit');
    $balance = pts53_balance($classId, $studentKey);
    $window = $isToday ? v55_block_window($schoolYear, $classId, (string)$session['date']) : [];
    $p = $state['progress'];

    render_header($isToday ? tr('Dnešní hodina') : tr('Lekce {n}', ['n' => $lessonNo]), $module);
    ?>
    <div class="t52 v56" data-v56-lesson data-lesson="<?= $lessonNo ?>"<?= $isToday ? ' data-v55-runner data-session="' . e((string)$session['id']) . '" data-student="' . e(substr(hash('sha256', $studentKey), 0, 16)) . '"' : '' ?>>
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>

      <header class="v56-head">
        <div>
          <span class="t52-kicker"><?= $isToday ? tr_html('Dnešní hodina · {module} · {range}', ['module' => edu_cs((string)$module['name']), 'range' => e((string)$window['range'])]) : tr_html('Lekce {n} · {module}', ['n' => (int)$lessonNo, 'module' => edu_cs((string)$module['name'])]) ?></span>
          <h1><?= e(tr('Lekce {n}', ['n' => $lessonNo])) ?> · <span<?= edu_content_lang_attr() ?>><?= e((string)$bundle['title']) ?></span></h1>
          <?php if (trim((string)$bundle['goal']) !== ''): ?><p<?= edu_content_lang_attr() ?>><?= e((string)$bundle['goal']) ?></p><?php endif; ?>
        </div>
        <div class="v56-head-meta">
          <span class="s53-points"><b><?= $balance ?></b> <?= e(pts53_points_label($balance)) ?></span>
          <?php if ($isToday && !empty($window['running'])): ?>
            <span class="v55-clock" data-v55-clock data-v55-clock-label="<?= e(tr('do konce hodiny')) ?>" data-end="<?= (int)$window['end_ts'] ?>"><b>—</b><small><?= e(tr('do konce hodiny')) ?></small></span>
          <?php elseif ($isToday): ?>
            <span class="v55-clock"><b><?= e((string)($window['before'] ? $window['start'] : $window['end'])) ?></b><small><?= $window['before'] ? e(tr('hodina začíná')) : e(tr('hodina skončila')) ?></small></span>
          <?php endif; ?>
        </div>
      </header>

      <?php
      // Kdo ještě nemá seznamovací dotazník, doplní ho na začátku hodiny – je to 5 minut a učitel podle něj přizpůsobuje výuku.
      $needsIntake = $isToday && function_exists('intake_v51_student_has_response') && !intake_v51_student_has_response($classId);
      $intakeClass = $needsIntake ? (intake_v51_classes((array)($GLOBALS['modules'] ?? []))[$classId] ?? null) : null;
      if ($needsIntake && is_array($intakeClass) && !empty($intakeClass['open'])): ?>
        <aside class="v56-intake-todo">
          <div><strong><?= e(tr('Ještě nemáš seznamovací dotazník')) ?></strong>
            <span><?= e(tr('Zabere 5 minut a učitel podle něj přizpůsobí výuku. Vyplň ho, než se pustíš do lekce.')) ?></span></div>
          <a class="btn primary" href="?view=intake" data-v55-allow><?= e(tr('Vyplnit dotazník')) ?></a>
        </aside>
      <?php endif; ?>

      <?php v56_render_rail($state, $lessonNo, !$isToday); ?>

      <?php if ($isToday && trim((string)($session['instructions'] ?? '')) !== ''): ?>
        <div class="v56-note"><strong><?= e(tr('Pokyny učitele')) ?></strong><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$session['instructions'])) ?></p></div>
      <?php endif; ?>

      <?php
      $meta = v56_phase_meta();
      $current = $state['phases'][$phase];
      ?>
      <section class="v56-phase v56-phase-<?= e($phase) ?>">
        <header class="v56-phase-head">
          <b><?= e((string)$meta[$phase]['mark']) ?></b>
          <div><strong><?= e((string)$current['label']) ?></strong><small><?= e((string)$current['lead']) ?></small></div>
        </header>

        <?php if ($phase === 'theory') v56_render_theory($classId, $bundle, $state, $lessonNo); ?>
        <?php if ($phase === 'test') v56_render_test($classId, $bundle, $state, $lessonNo); ?>
        <?php if ($phase === 'project') v56_render_project($classId, $bundle, $state, $lessonNo); ?>
        <?php if ($phase === 'submit') v56_render_submit($classId, $bundle, $state, $lessonNo, $session); ?>
      </section>

      <?php if (function_exists('lx72_render_student_block')) lx72_render_student_block($classId, $lessonNo); /* v72: cíl, exit ticket a volitelná domácí příprava – jen schválená lekce */ ?>

      <?php if ($state['complete'] && $isToday): ?>
        <?php v55_render_bonus($classId, $module, $session, $window, v55_bonus_get((string)$session['id'], $studentKey), (string)$session['id']); ?>
      <?php elseif ($state['complete']): ?>
        <div class="v56-done"><strong><?= e(tr('Lekce {n} je hotová.', ['n' => $lessonNo])) ?></strong><span><?= e(tr('Můžeš se k ní kdykoli vrátit v materiálech.')) ?></span>
          <?php if ($lessonNo < 28): ?><a class="btn primary" href="<?= e(v56_lesson_url($lessonNo + 1)) ?>"><?= e(tr('Pokračovat na lekci {n}', ['n' => $lessonNo + 1])) ?></a><?php endif; ?></div>
      <?php endif; ?>

    </div>
    <?php
    tut52_assets();
    render_footer();
}

/** 1 · Teorie – témata jedno po druhém. */
function v56_render_theory(string $classId, array $bundle, array $state, int $lessonNo): void
{
    $topics = (array)$bundle['topics'];
    $read = (array)$state['progress']['theory'];
    $nextKey = (string)$state['next_topic'];
    $open = isset($_GET['tema']) && isset($topics[(string)$_GET['tema']]) ? (string)$_GET['tema'] : $nextKey;
    if ($open === '' || !isset($topics[$open])) $open = (string)array_key_first($topics);
    $topic = $topics[$open];
    $idx = array_search($open, array_keys($topics), true);
    $scene = tut52_scene($open, (string)$bundle['family']);
    ?>
    <div class="v56-topic-nav"<?= edu_content_lang_attr() ?>>
      <?php foreach ($topics as $key => $t): $done = !empty($read[$key]); ?>
        <a class="<?= $key === $open ? 'is-open' : '' ?><?= $done ? ' is-done' : '' ?>" href="<?= e(v56_lesson_url($lessonNo, 'theory')) ?>&amp;tema=<?= e((string)$key) ?>"><?= $done ? '✓ ' : '' ?><?= e((string)$t['title']) ?></a>
      <?php endforeach; ?>
    </div>

    <article class="v56-topic">
      <header><span class="v56-topic-step"><?= e(tr('Téma {n} / {total}', ['n' => (int)$idx + 1, 'total' => count($topics)])) ?></span><h2<?= edu_content_lang_attr() ?>><?= e((string)$topic['title']) ?></h2>
        <?php if (trim((string)$topic['summary']) !== ''): ?><p class="v56-lead"<?= edu_content_lang_attr() ?>><?= e((string)$topic['summary']) ?></p><?php endif; ?></header>

      <div class="v56-scene"<?= edu_content_lang_attr() ?>><?php tut52_render_scene($scene, (string)$topic['title']); ?></div>

      <?php if ((array)$topic['body']): ?>
        <ul class="v56-body"<?= edu_content_lang_attr() ?>><?php foreach ((array)$topic['body'] as $line): ?><li><?= e((string)$line) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if (trim((string)$topic['example']) !== ''): ?>
        <div class="v56-example"<?= edu_content_lang_attr() ?>><strong><?= e(tr('Z praxe')) ?></strong><p><?= e((string)$topic['example']) ?></p></div>
      <?php endif; ?>

      <div class="v56-phase-foot">
        <?php if (empty($read[$open])): ?>
          <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v56_theory_done">
            <input type="hidden" name="lesson" value="<?= $lessonNo ?>"><input type="hidden" name="topic" value="<?= e($open) ?>">
            <button class="btn primary" type="submit"><?= e(tr('Rozumím, další téma')) ?></button></form>
        <?php else: ?>
          <span class="v56-ok">✓ <?= e(tr('Téma máš projité')) ?></span>
          <?php if ($nextKey !== ''): ?><a class="btn primary" href="<?= e(v56_lesson_url($lessonNo, 'theory')) ?>&amp;tema=<?= e($nextKey) ?>"><?= e(tr('Další téma')) ?></a>
          <?php else: ?><a class="btn primary" href="<?= e(v56_lesson_url($lessonNo, 'test')) ?>"><?= e(tr('Přejít na test')) ?></a><?php endif; ?>
        <?php endif; ?>
      </div>
    </article>
    <?php
}

/** 2 · Test – kontrolní otázky k tématům lekce. */
function v56_render_test(string $classId, array $bundle, array $state, int $lessonNo): void
{
    $questions = v56_test_questions($classId, adaptive_student_key($classId), $lessonNo, (array)$bundle['questions']); // v66: sumativní test v pořadí žáka
    $result = (array)$state['phases']['test']['result'];
    $showResult = !empty($result['at']) && empty($_GET['znovu']);
    ?>
    <?php if ($showResult): $detail = (array)($result['detail'] ?? []); ?>
      <div class="v56-result <?= !empty($result['passed']) ? 'ok' : 'warn' ?>">
        <strong><?= e(tr('{score} / {max} správně · {percent} %', ['score' => (int)$result['score'], 'max' => (int)$result['max'], 'percent' => (int)$result['percent']])) ?></strong>
        <p><?= !empty($result['passed']) ? e(tr('Prošel/prošla jsi. Teorii máš ověřenou, jde se na projekt.')) : e(tr('Na postup potřebuješ {percent} %. Projdi si znovu témata, kde ses spletl/a, a zkus to ještě jednou.', ['percent' => V56_TEST_PASS_PERCENT])) ?></p>
      </div>
      <ol class="v56-review">
        <?php $detailById = []; foreach ($detail as $dd) { if (is_array($dd)) $detailById[(string)($dd['id'] ?? '')] = $dd; } // v66: výsledek se páruje podle id otázky (pořadí sumativního testu se liší podle žáka) ?>
        <?php foreach ($questions as $i => $q): $d = $detailById[(string)($q['id'] ?? $i)] ?? ($detail[$i] ?? []); $ok = !empty($d['ok']); ?>
          <li class="<?= $ok ? 'ok' : 'bad' ?>"<?= edu_content_lang_attr() ?>>
            <strong><?= e((string)$q['question']) ?></strong>
            <span><?= $ok ? '✓ ' . e(tr('správně')) : '✗ ' . e(tr('správná odpověď: {answer}', ['answer' => (string)(($q['options'] ?? [])[$q['correct']] ?? '')])) ?></span>
            <?php if (!$ok && trim((string)($q['explanation'] ?? '')) !== ''): ?><small><?= e((string)$q['explanation']) ?></small><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
      <div class="v56-phase-foot">
        <?php if (!empty($result['passed'])): ?>
          <a class="btn primary" href="<?= e(v56_lesson_url($lessonNo, 'project')) ?>"><?= e(tr('Pokračovat na projekt')) ?></a>
        <?php else: ?>
          <a class="btn primary" href="<?= e(v56_lesson_url($lessonNo, 'test')) ?>&amp;znovu=1"><?= e(tr('Zkusit test znovu')) ?></a>
        <?php endif; ?>
        <a class="t52-link" href="<?= e(v56_lesson_url($lessonNo, 'theory')) ?>"><?= e(tr('Zopakovat teorii')) ?></a>
      </div>
    <?php else: ?>
      <form class="v56-test" method="post" data-v55-autosave="test">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v56_test_submit"><input type="hidden" name="lesson" value="<?= $lessonNo ?>">
        <?php foreach ($questions as $i => $q): ?>
          <fieldset class="v56-q"<?= edu_content_lang_attr() ?>>
            <legend><span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e((string)$q['question']) ?></legend>
            <?php foreach ((array)($q['options'] ?? []) as $key => $text): ?>
              <label class="v56-opt"><input type="radio" name="answers[<?= $i ?>]" value="<?= e((string)$key) ?>" required><span><?= e((string)$text) ?></span></label>
            <?php endforeach; ?>
          </fieldset>
        <?php endforeach; ?>
        <div class="v56-phase-foot"><button class="btn primary" type="submit" data-v55-unlock><?= e(tr('Vyhodnotit test')) ?></button><span class="v55-autosave-note" data-v55-autosave-note><?= e(tr('Rozpracované odpovědi se ti ukládají.')) ?></span></div>
      </form>
    <?php endif; ?>
    <?php
}

/** 3 · Projekt – praktické kroky. */
function v56_render_project(string $classId, array $bundle, array $state, int $lessonNo): void
{
    $steps = (array)$bundle['steps'];
    $done = (array)$state['progress']['project'];
    $gate = $state['lab_gate'] ?? null;
    $openIndex = null;
    foreach (array_keys($steps) as $i) if ($openIndex === null && empty($done[(string)$i])) $openIndex = (int)$i;
    ?>
    <ol class="v56-steps">
      <?php foreach ($steps as $i => $step): $isDone = !empty($done[(string)$i]); $isOpen = (int)$i === $openIndex;
        $isGated = is_array($gate) && $gate['step'] === (int)$i; $isLocked = $isGated && $gate['locked']; ?>
        <li class="v56-step <?= $isDone ? 'is-done' : ($isOpen ? 'is-open' : 'is-next') ?>"<?= edu_content_lang_attr() ?>>
          <div class="v56-step-top">
            <b><?= $isDone ? '✓' : (int)$i + 1 ?></b>
            <div><strong><?= e((string)$step['title']) ?></strong><?php if (trim((string)$step['time']) !== ''): ?><em><?= e((string)$step['time']) ?></em><?php endif; ?></div>
          </div>
          <?php if ($isOpen || $isDone): ?>
            <?php if ((array)$step['tasks']): ?>
              <ul class="v56-tasks"><?php foreach ((array)$step['tasks'] as $t): ?><li><?= e((string)$t) ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
            <?php if ($isGated) v56_render_lab_gate($gate); ?>
            <?php if (!$isLocked): ?>
              <form method="post" class="v56-step-form"<?= edu_locale() === 'cs' ? '' : ' lang="' . e(edu_html_lang()) . '"' ?>>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v56_project_step">
                <input type="hidden" name="lesson" value="<?= $lessonNo ?>"><input type="hidden" name="step" value="<?= (int)$i ?>">
                <input type="hidden" name="done" value="<?= $isDone ? '0' : '1' ?>">
                <button class="btn <?= $isDone ? 'secondary' : 'primary' ?>" type="submit"><?= $isDone ? e(tr('Vrátit se ke kroku')) : e(tr('Krok mám hotový')) ?></button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php if ($openIndex === null): ?>
      <div class="v56-phase-foot"><a class="btn primary" href="<?= e(v56_lesson_url($lessonNo, 'submit')) ?>"><?= e(tr('Přejít na odevzdání')) ?></a></div>
    <?php endif; ?>
    <?php
}

/** EDU-01: zámek kroku na nevyřešenou úroveň Labu, nebo důkaz vyřešení (úroveň + čas, bez kódu). */
function v56_render_lab_gate(array $gate): void
{
    if (!empty($gate['locked'])) {
        ?>
        <div class="v56-lab-gate" role="note">
          <strong><?= e(tr('Tenhle krok potřebuje vyřešenou úroveň Linux Labu')) ?></strong>
          <p><?= e(tr('„{title}“ – vyřeš ji v Labu a pak se sem vrať krok označit jako hotový.', ['title' => (string)$gate['title']])) ?></p>
          <a class="btn primary" href="?view=lab&amp;uroven=<?= e((string)$gate['level']) ?>"><?= e(tr('Otevřít úroveň v Labu')) ?></a>
        </div>
        <?php
        return;
    }
    $proof = (array)($gate['proof'] ?? []);
    $at = (string)($proof['at'] ?? '');
    ?>
    <div class="v56-lab-proof">
      <strong><?= e(tr('Důkaz z Labu:')) ?></strong> <?= $at !== '' ? e(tr('úroveň „{title}“ vyřešena {at}', ['title' => (string)$gate['title'], 'at' => date('j. n. Y H:i', (int)strtotime($at))])) : e(tr('úroveň „{title}“ vyřešena', ['title' => (string)$gate['title']])) ?>
    </div>
    <?php
}

/** 4 · Odevzdání. */
function v56_render_submit(string $classId, array $bundle, array $state, int $lessonNo, ?array $session): void
{
    $sub = (array)$state['progress']['submit'];
    $studentKey = adaptive_student_key($classId);
    $teacher = is_array($session) ? sess53_submission((string)$session['id'], $studentKey) : [];
    $gate = $state['lab_gate'] ?? null;
    if (is_array($gate)) v56_render_lab_gate($gate);
    ?>
    <form class="v56-submit" method="post" data-v55-autosave="submit">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="v56_submit"><input type="hidden" name="lesson" value="<?= $lessonNo ?>">
      <?php if (is_array($session)): ?><input type="hidden" name="session" value="<?= e((string)$session['id']) ?>"><?php endif; ?>
      <label class="u51-field"><span><?= e(tr('Co jsi vytvořil/a a jak ses rozhodoval/a?')) ?></span>
        <textarea name="note" rows="5" placeholder="<?= e(tr('3–5 vět: co jsi udělal/a, proč právě takhle a kde ses zasekl/a.')) ?>"><?= e((string)($sub['note'] ?? '')) ?></textarea></label>
      <label class="u51-field"><span><?= e(tr('Odkaz na výstup (Canva, Figma, dokument, screenshot…)')) ?></span>
        <input name="link" maxlength="500" placeholder="https://…" value="<?= e((string)($sub['link'] ?? '')) ?>"></label>
      <div class="v56-phase-foot">
        <button class="btn secondary" type="submit" name="final" value="0"><?= e(tr('Uložit rozpracované')) ?></button>
        <button class="btn primary" type="submit" name="final" value="1" data-v55-unlock><?= is_array($session) ? e(tr('Odevzdat učiteli')) : e(tr('Odevzdat')) ?></button>
      </div>
    </form>
    <?php if (!empty($teacher['teacher_comment'])): ?>
      <div class="v56-feedback"><strong><?= !empty($teacher['grade']) ? e(tr('Hodnocení učitele · známka {grade} · +{points} bodů', ['grade' => (int)$teacher['grade'], 'points' => (int)($teacher['points'] ?? 0)])) : e(tr('Hodnocení učitele')) ?></strong><p<?= edu_content_lang_attr() ?>><?= nl2br(e((string)$teacher['teacher_comment'])) ?></p></div>
    <?php endif; ?>
    <?php
}

// ---------------------------------------------------------------------------
// Materiály
// ---------------------------------------------------------------------------

function v56_render_materials(string $classId, array $module, array $index, string $section, array $simulations, string $flash): void
{
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $kb = (array)($module['knowledgebase'] ?? []);
    $sections = ['lekce' => tr('Lekce'), 'temata' => tr('Témata'), 'programy' => tr('Programy a zkratky')];
    if (!isset($sections[$section])) $section = 'lekce';
    render_header(tr('Materiály'), $module);
    ?>
    <div class="t52 v56">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="v56-head">
        <div><span class="t52-kicker"<?= edu_content_lang_attr() ?>><?= e((string)$module['name']) ?> · <?= e((string)$module['subject']) ?></span>
          <h1><?= e(tr('Materiály')) ?></h1><p><?= e(tr('Všechno, co k předmětu patří, na jednom místě. Lekce se otvírají postupně, jak jimi procházíš.')) ?></p></div>
      </header>

      <?php v56_render_lab_optional_banner($classId); ?>

      <nav class="v56-tabs" aria-label="<?= e(tr('Sekce materiálů')) ?>">
        <?php foreach ($sections as $key => $label): ?>
          <a class="<?= $key === $section ? 'is-active' : '' ?>" href="?view=materialy&amp;sekce=<?= e($key) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <?php if ($section === 'lekce'): ?>
        <div class="v56-lesson-grid">
          <?php foreach ($index as $l): ?>
            <article class="v56-lesson-card<?= $l['current'] ? ' is-current' : '' ?><?= $l['complete'] ? ' is-done' : '' ?><?= $l['available'] ? '' : ' is-locked' ?>">
              <div class="v56-lesson-no"><?= str_pad((string)$l['number'], 2, '0', STR_PAD_LEFT) ?></div>
              <div class="v56-lesson-body"<?= edu_content_lang_attr() ?>>
                <strong><?= e((string)$l['title']) ?></strong>
                <?php if (trim((string)$l['goal']) !== ''): ?><small><?= e(mb_substr((string)$l['goal'], 0, 120, 'UTF-8')) ?></small><?php endif; ?>
                <div class="v56-chips"><?php foreach (array_slice((array)$l['topics'], 0, 3) as $t): ?><span><?= e((string)$t) ?></span><?php endforeach; ?></div>
                <div class="v56-lesson-foot">
                  <?php if ($l['date'] !== ''): ?><em><?= e(date('j. n. Y', strtotime((string)$l['date']))) ?></em><?php endif; ?>
                  <?php if ($l['available']): ?>
                    <span class="v56-bar" title="<?= (int)$l['percent'] ?> %"><i style="width: <?= (int)$l['percent'] ?>%"></i></span>
                    <a class="btn <?= $l['current'] ? 'primary' : 'secondary' ?>" href="<?= e(v56_lesson_url((int)$l['number'])) ?>"<?= edu_locale() === 'cs' ? '' : ' lang="' . e(edu_html_lang()) . '"' ?>><?= $l['complete'] ? e(tr('Zopakovat')) : ($l['percent'] > 0 ? e(tr('Pokračovat')) : e(tr('Otevřít'))) ?></a>
                  <?php else: ?>
                    <span class="v56-locked"<?= edu_locale() === 'cs' ? '' : ' lang="' . e(edu_html_lang()) . '"' ?>><?= $l['date'] !== '' ? e(tr('otevře se {date}', ['date' => date('j. n.', strtotime((string)$l['date']))])) : e(tr('otevře se později')) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      <?php elseif ($section === 'temata'): ?>
        <input class="v56-filter" type="search" placeholder="<?= e(tr('Hledat téma…')) ?>" data-v56-filter aria-label="<?= e(tr('Hledat téma')) ?>">
        <div class="v56-topic-grid"<?= edu_content_lang_attr() ?>>
          <?php foreach ($kb as $key => $article): ?>
            <article class="v56-topic-card" data-v56-item="<?= e(mb_strtolower((string)($article['title'] ?? $key), 'UTF-8')) ?>">
              <strong><?= e((string)($article['title'] ?? $key)) ?></strong>
              <small><?= e(mb_substr((string)($article['summary'] ?? ''), 0, 160, 'UTF-8')) ?></small>
              <details><summary<?= edu_locale() === 'cs' ? '' : ' lang="' . e(edu_html_lang()) . '"' ?>><?= e(tr('Vysvětlení')) ?></summary>
                <ul><?php foreach ((array)($article['body'] ?? []) as $line): ?><li><?= e((string)$line) ?></li><?php endforeach; ?></ul>
                <?php if (trim((string)($article['example'] ?? '')) !== ''): ?><p class="v56-example-inline"><strong<?= edu_locale() === 'cs' ? '' : ' lang="' . e(edu_html_lang()) . '"' ?>><?= e(tr('Z praxe:')) ?></strong> <?= e((string)$article['example']) ?></p><?php endif; ?>
              </details>
            </article>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <input class="v56-filter" type="search" placeholder="<?= e(tr('Hledat program nebo zkratku…')) ?>" data-v56-filter aria-label="<?= e(tr('Hledat program')) ?>">
        <div class="v56-tool-grid"<?= edu_content_lang_attr() ?>>
          <?php foreach ($tools as $key => $tool): ?>
            <article class="v56-tool-card" id="<?= e((string)$key) ?>" data-v56-item="<?= e(mb_strtolower((string)$tool['name'] . ' ' . (string)$tool['use'], 'UTF-8')) ?>">
              <strong><?= e((string)$tool['name']) ?></strong>
              <small><?= e((string)$tool['use']) ?></small>
              <?php if (!empty($tool['shortcuts'])): ?>
                <dl class="v56-shortcuts"><?php foreach ((array)$tool['shortcuts'] as $sc): $combo = is_array($sc) ? (string)($sc[0] ?? '') : ''; $what = is_array($sc) ? (string)($sc[1] ?? '') : (string)$sc; if ($combo === '') continue; ?><div><dt><?= e($combo) ?></dt><dd><?= e($what) ?></dd></div><?php endforeach; ?></dl>
              <?php endif; ?>
              <?php if (!empty($tool['where'])): ?><span class="v56-tool-where"><?= e((string)$tool['where']) ?></span><?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

/** EDU-01: volitelný ukazatel na balíček „Terminál pro grafiky“ (LAB-07) pro 1.A/2.A, pokud existuje. */
function v56_render_lab_optional_banner(string $classId): void
{
    if (!function_exists('v56_lab_optional_pack')) return;
    $pack = v56_lab_optional_pack($classId);
    if ($pack === null) return;
    ?>
    <aside class="v56-lab-optional">
      <div><strong><?= tr_html('Volitelně: {title}', ['title' => edu_cs((string)$pack['title'])]) ?></strong>
        <span><?= edu_cs((string)$pack['description']) ?> <?= e(tr('Terminál je jen doplněk – s hodnocením lekce to nesouvisí.')) ?></span></div>
      <a class="btn secondary" href="?view=lab"><?= e(tr('Zkusit v Labu')) ?></a>
    </aside>
    <?php
}

// ---------------------------------------------------------------------------
// Výsledky
// ---------------------------------------------------------------------------

function v56_render_results(string $classId, array $module, array $index, string $flash): void
{
    $studentKey = adaptive_student_key($classId);
    $balance = pts53_balance($classId, $studentKey);
    $lvl = v55_level_state($classId);
    $graded = [];
    foreach (sess53_all() as $row) {
        if (!is_array($row) || (string)$row['class_id'] !== $classId) continue;
        $sub = sess53_submission((string)$row['id'], $studentKey);
        if (empty($sub['grade']) && empty($sub['teacher_comment'])) continue;
        $graded[] = ['date' => (string)$row['date'], 'title' => (string)$row['title'], 'grade' => (int)($sub['grade'] ?? 0),
            'points' => (int)($sub['points'] ?? 0), 'comment' => (string)($sub['teacher_comment'] ?? '')];
    }
    usort($graded, static fn(array $a, array $b): int => strcmp($b['date'], $a['date']));
    $done = array_values(array_filter($index, static fn(array $l): bool => (bool)$l['complete']));
    $started = array_values(array_filter($index, static fn(array $l): bool => $l['percent'] > 0 && !$l['complete']));
    render_header(tr('Výsledky'), $module);
    ?>
    <div class="t52 v56">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="v56-head"><div><span class="t52-kicker"<?= edu_content_lang_attr() ?>><?= e((string)$module['name']) ?></span><h1><?= e(tr('Výsledky')) ?></h1><p><?= e(tr('Co máš hotové, jak tě hodnotil učitel a kolik ti to dalo bodů.')) ?></p></div></header>

      <div class="v56-stats">
        <div class="v56-stat"><strong><?= count($done) ?></strong><small><?= e(tr('dokončených lekcí')) ?></small></div>
        <div class="v56-stat"><strong><?= count($started) ?></strong><small><?= e(tr('rozpracovaných')) ?></small></div>
        <div class="v56-stat"><strong><?= $balance ?></strong><small><?= e(tr('bodů')) ?></small></div>
        <div class="v56-stat"><strong><?= (int)$lvl['level'] ?></strong><small><?= e(tr('level · {xp} XP', ['xp' => (int)$lvl['xp']])) ?></small></div>
      </div>

      <section class="v56-block">
        <h2><?= e(tr('Hodnocení od učitele')) ?></h2>
        <?php if (!$graded): ?>
          <p class="v56-empty"><?= e(tr('Zatím nemáš žádné hodnocení. Objeví se tady hned, jak učitel projde tvoje odevzdání.')) ?></p>
        <?php else: ?>
          <ul class="v56-grades">
            <?php foreach ($graded as $g): ?>
              <li><span class="v56-grade<?= $g['grade'] > 0 ? ' g' . $g['grade'] : '' ?>"><?= $g['grade'] > 0 ? $g['grade'] : '–' ?></span>
                <div><strong><?= e($g['title']) ?></strong><small><?= e(tut52_cz_date($g['date'], false)) ?><?= $g['points'] > 0 ? ' · ' . e(tr('+{points} bodů', ['points' => $g['points']])) : '' ?></small>
                <?php if ($g['comment'] !== ''): ?><p<?= edu_content_lang_attr() ?>><?= nl2br(e($g['comment'])) ?></p><?php endif; ?></div></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

      <section class="v56-block">
        <h2><?= e(tr('Postup v lekcích')) ?></h2>
        <ul class="v56-lesson-progress">
          <?php foreach ($index as $l): if (!$l['available']) continue; ?>
            <li><span class="v56-lp-no"><?= str_pad((string)$l['number'], 2, '0', STR_PAD_LEFT) ?></span>
              <span class="v56-lp-title"<?= edu_content_lang_attr() ?>><?= e((string)$l['title']) ?></span>
              <span class="v56-bar"><i style="width: <?= (int)$l['percent'] ?>%"></i></span>
              <span class="v56-lp-percent"><?= (int)$l['percent'] ?> %</span></li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

// ---------------------------------------------------------------------------
// Profil: XP graf, levely, body, achievementy
// ---------------------------------------------------------------------------

function v56_render_profile_stats(string $classId, array $module, array $simulationMap = []): void
{
    $studentKey = adaptive_student_key($classId);
    $profile = learning_profile($classId);
    $lvl = v55_level_state($classId);
    $balance = pts53_balance($classId, $studentKey);
    $timeline = learning_xp_timeline($profile, 14);
    $achievements = learning_achievement_progress($classId, $module, $simulationMap);
    $defs = learning_achievement_definitions();
    $earned = array_filter($achievements, static fn(array $a): bool => !empty($a['earned']));
    $maxDay = 0;
    foreach ($timeline as $d) $maxDay = max($maxDay, (int)$d['earned']);
    $totalStart = (int)($timeline[0]['total'] ?? 0);
    $totalEnd = (int)($timeline[count($timeline) - 1]['total'] ?? 0);
    ?>
    <section class="dashboard-panel v56-profile-stats" id="statistiky">
      <header class="v55-section-head">
        <div><span class="t52-kicker"><?= e(tr('Přehled')) ?></span><h2><?= e(tr('Level, XP a body')) ?></h2><p><?= e(tr('Jak ti roste zkušenost a co ti přinesla.')) ?></p></div>
      </header>

      <div class="v56-stats">
        <div class="v56-stat"><strong><?= (int)$lvl['level'] ?></strong><small><?= e(tr('level')) ?></small></div>
        <div class="v56-stat"><strong><?= (int)$lvl['xp'] ?></strong><small><?= e(tr('XP celkem')) ?></small></div>
        <div class="v56-stat"><strong><?= $balance ?></strong><small><?= e(tr('bodů k utracení')) ?></small></div>
        <div class="v56-stat"><strong><?= count($earned) ?></strong><small><?= e(tr('z {total} achievementů', ['total' => count($achievements)])) ?></small></div>
      </div>

      <div class="v55-xpbar" style="--v55-lvl-percent: <?= (int)$lvl['percent'] ?>">
        <span><?= e(tr('Level {n}', ['n' => (int)$lvl['level']])) ?></span><i><b></b></i><span><?= e(tr('{current} / {next} XP', ['current' => (int)$lvl['current'], 'next' => (int)$lvl['next']])) ?></span>
      </div>

      <h3 class="v55-badge-group"><?= $totalEnd > $totalStart ? e(tr('XP za posledních 14 dní · +{n} XP', ['n' => $totalEnd - $totalStart])) : e(tr('XP za posledních 14 dní')) ?></h3>
      <div class="v56-chart<?= $maxDay === 0 ? ' is-empty' : '' ?>" role="img" aria-label="<?= e(tr('Graf získaných XP za posledních 14 dní')) ?>">
        <?php foreach ($timeline as $d): $h = $maxDay > 0 ? max(4, (int)round((int)$d['earned'] / $maxDay * 104)) : 4; ?>
          <span class="v56-chart-col" title="<?= e(tr('{date} · {xp} XP (celkem {total})', ['date' => date('j. n.', strtotime((string)$d['date'])), 'xp' => (int)$d['earned'], 'total' => (int)$d['total']])) ?>">
            <i style="height: <?= $h ?>px"></i><small><?= e(date('j.', strtotime((string)$d['date']))) ?></small></span>
        <?php endforeach; ?>
      </div>
      <?php if ($maxDay === 0): ?><p class="v56-chart-empty"><?= e(tr('Zatím nemáš žádné XP. Získáš je hned, jak projdeš první téma dnešní hodiny.')) ?></p><?php endif; ?>

      <h3 class="v55-badge-group"><?= e(tr('Achievementy')) ?></h3>
      <div class="v56-achievements">
        <?php
        uasort($achievements, static fn(array $a, array $b): int => ($b['earned'] <=> $a['earned']) ?: ($b['percent'] <=> $a['percent']));
        foreach (array_slice($achievements, 0, 12, true) as $id => $a): $def = $defs[$id] ?? ['title' => $id, 'mark' => '•', 'text' => ''];
        ?>
          <article class="v56-ach<?= !empty($a['earned']) ? ' earned' : '' ?>">
            <b><?= e((string)$def['mark']) ?></b>
            <div><strong<?= edu_content_lang_attr() ?>><?= e((string)$def['title']) ?></strong><small<?= edu_content_lang_attr() ?>><?= e((string)$def['text']) ?></small>
              <span class="v56-bar"><i style="width: <?= (int)$a['percent'] ?>%"></i></span>
              <em><?= (int)$a['current'] ?> / <?= (int)$a['target'] ?></em></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
}
