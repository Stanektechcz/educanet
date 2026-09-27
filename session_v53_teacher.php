<?php

declare(strict_types=1);

/** v53 · učitelská záložka Hodina: invite kódy, průběh a hodnocení samostatné práce. */

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v59 · AUTHZ58-07: hodina (i nalezená podle id) musí patřit třídě v rozsahu učitele; legacy = vždy true. */
function sess53_teacher_session_in_scope(array $session): bool
{
    return !function_exists('teacher59_can_class') || teacher59_can_class((string)($session['class_id'] ?? ''));
}

function sess53_teacher_handle_post(string $action, array $modules): void
{
    if (!str_starts_with($action, 'sess53_t_')) return;
    teacher_require_permission('students.manage');
    $classId = (string)($_POST['class_id'] ?? '');
    $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['date'] ?? '')) ? (string)$_POST['date'] : date('Y-m-d');

    if ($action === 'sess53_t_open') {
        if (!isset($modules[$classId]) || (function_exists('teacher59_can_class') && !teacher59_can_class($classId))) throw new RuntimeException('Neznámá třída.');
        $schoolYear = require __DIR__ . '/school_year.php';
        $rt = runtime_content_load_classes([$classId]);
        $lessons = tut52_lessons($classId, $modules[$classId], $rt['nextLessons'], $rt['extendedLessons'], $schoolYear, null);
        $lessonNo = 0;
        foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
            if (is_array($row) && (string)($row['date'] ?? '') === $date && (string)($row['status'] ?? '') === 'teaching') { $lessonNo = (int)($row['lesson_number'] ?? 0); break; }
        }
        if ($lessonNo === 0) $lessonNo = (int)((tut52_current_lesson($lessons)['number'] ?? 1));
        $lesson = $lessons[$lessonNo] ?? ['title' => 'Hodina', 'goal' => '', 'steps' => []];
        $kind = (string)($_POST['kind'] ?? 'work') === 'intake' ? 'intake' : 'work';
        $raw = null;
        foreach ((array)($rt['extendedLessons'][$classId] ?? []) as $cand) if (is_array($cand) && (int)($cand['number'] ?? 0) === $lessonNo) { $raw = $cand; break; }
        if ($raw === null && $lessonNo === 2) $raw = $rt['nextLessons'][$classId] ?? null;
        $session = sess53_open($classId, $date, $modules, is_array($raw) ? $raw : [], $kind, [
            'lesson_number' => $lessonNo,
            'title' => $kind === 'intake' ? 'Seznamovací dotazník' : 'Lekce ' . $lessonNo . ' · ' . (string)$lesson['title'],
            'goal' => $kind === 'intake' ? 'Poznáme se a nastavíme výuku podle vás.' : (string)$lesson['goal'],
            'instructions' => (string)($_POST['instructions'] ?? ''),
        ]);
        teacher_flash('Hodina je otevřená. Kód pro žáky: ' . (string)$session['code']);
        teacher_redirect(['tab' => 'session', 'class' => $classId]);
    }
    if ($action === 'sess53_t_toggle') {
        $session = sess53_find((string)($_POST['session'] ?? ''));
        if (!$session || !sess53_teacher_session_in_scope($session)) throw new RuntimeException('Hodina nebyla nalezena.');
        $updated = sess53_update((string)$session['id'], static function (array $row): array { $row['open'] = empty($row['open']); return $row; });
        teacher_flash(!empty($updated['open']) ? 'Hodina je znovu otevřená.' : 'Hodina je uzavřená, kód už nefunguje.');
        teacher_redirect(['tab' => 'session', 'class' => (string)$session['class_id']]);
    }
    if ($action === 'sess53_t_instructions') {
        $session = sess53_find((string)($_POST['session'] ?? ''));
        if (!$session || !sess53_teacher_session_in_scope($session)) throw new RuntimeException('Hodina nebyla nalezena.');
        $text = (string)($_POST['instructions'] ?? '');
        sess53_update((string)$session['id'], static function (array $row) use ($text): array { $row['instructions'] = intake_v51_text($text, 2000); return $row; });
        teacher_flash('Pokyny pro žáky jsou uložené.');
        teacher_redirect(['tab' => 'session', 'class' => (string)$session['class_id']]);
    }
    if ($action === 'sess53_t_grade') {
        $session = sess53_find((string)($_POST['session'] ?? ''));
        if (!$session || !sess53_teacher_session_in_scope($session)) throw new RuntimeException('Hodina nebyla nalezena.');
        $grade = (string)($_POST['grade'] ?? '') !== '' ? max(1, min(5, (int)$_POST['grade'])) : null;
        sess53_teacher_feedback((string)$session['id'], (string)($_POST['student_key'] ?? ''), (string)($_POST['comment'] ?? ''), $grade);
        teacher_flash($grade !== null ? 'Hodnocení uloženo · ' . pts53_points_label(pts53_points_for_grade($grade)) . ' pro žáka.' : 'Komentář uložen.');
        teacher_redirect(['tab' => 'session', 'class' => (string)$session['class_id']]);
    }
    if ($action === 'v55_t_bonus_grade') {
        $session = sess53_find((string)($_POST['session'] ?? ''));
        if (!$session || !sess53_teacher_session_in_scope($session)) throw new RuntimeException('Hodina nebyla nalezena.');
        if ((string)($_POST['grade'] ?? '') === '') {
            teacher_flash('Vyber známku bonusu.');
        } else {
            $row = v55_bonus_grade((string)$session['id'], (string)($_POST['student_key'] ?? ''), (int)$_POST['grade'], (string)($_POST['comment'] ?? ''), (string)$session['class_id']);
            teacher_flash('Bonus ohodnocen · ' . v55_weight_label((float)($row['weight'] ?? 1)) . ' → ' . pts53_points_label((int)($row['points'] ?? 0)) . '.');
        }
        teacher_redirect(['tab' => 'session', 'class' => (string)$session['class_id']]);
    }
}

function sess53_render_teacher_tab(array $modules, string $classId): void
{
    $date = date('Y-m-d');
    $csrf = e(csrf_token());
    $sessionsToday = [];
    foreach ($modules as $cid => $module) $sessionsToday[(string)$cid] = sess53_for_class_date((string)$cid, $date);
    $session = $sessionsToday[$classId] ?? null;
    $schoolYear = require __DIR__ . '/school_year.php';
    // Třídy v pořadí, jak je učitel dnes učí.
    $byTime = $modules;
    uksort($byTime, static function ($a, $b) use ($schoolYear): int {
        return strcmp((string)(adaptive_class_schedule($schoolYear, (string)$a)['start'] ?? ''), (string)(adaptive_class_schedule($schoolYear, (string)$b)['start'] ?? ''));
    });
    ?>
    <div class="t52 t52-teach">
      <header class="t52-page-head">
        <div><span class="t52-kicker">Dnes · <?= e(tut52_cz_date($date)) ?></span><h1>Hodina a kód pro žáky</h1><p>Otevři hodinu, promítni kód a sleduj, jak žáci pracují. Známka se automaticky přepočítá na body (1 = 3 body, 2 = 2 body, 3 = 1 bod).</p></div>
      </header>

      <div class="s53-teacher-grid">
        <?php foreach ($byTime as $cid => $module): $cid = (string)$cid; $row = $sessionsToday[$cid]; $sch = adaptive_class_schedule($schoolYear, $cid); ?>
          <article class="t52-card s53-class-card <?= $cid === $classId ? 'active' : '' ?>">
            <header class="t52-card-head"><span class="t52-kicker"><?= e((string)$sch['start']) ?>–<?= e((string)$sch['end']) ?> · <?= e((string)($sch['room'] ?? '')) ?></span><strong><?= e((string)$module['name']) ?> · <?= e((string)$module['subject']) ?></strong></header>
            <?php if ($row): $subs = sess53_submissions((string)$row['id']); $doneCount = count(array_filter($subs, static fn($s) => ($s['status'] ?? '') === 'submitted')); ?>
              <div class="s53-code" title="Kód pro žáky"><?= e((string)$row['code']) ?></div>
              <p class="t52-muted"><?= e((string)$row['title']) ?></p>
              <p class="t52-muted small"><?= (string)$row['kind'] === 'intake' ? 'Seznamovací dotazník' : 'Samostatná práce' ?> · <?= count($subs) ?> pracuje · <?= $doneCount ?> odevzdáno · <?= !empty($row['open']) ? 'otevřeno' : 'uzavřeno' ?></p>
              <div class="t52-step-foot">
                <a class="btn secondary" href="<?= e('teacher.php?tab=session&class=' . $cid) ?>">Detail</a>
                <form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="sess53_t_toggle"><input type="hidden" name="session" value="<?= e((string)$row['id']) ?>"><button class="btn secondary" type="submit"><?= !empty($row['open']) ? 'Uzavřít' : 'Otevřít' ?></button></form>
              </div>
            <?php else: ?>
              <p class="t52-muted">Hodina zatím není otevřená.</p>
              <form method="post" class="s53-open-form">
                <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="sess53_t_open"><input type="hidden" name="class_id" value="<?= e($cid) ?>"><input type="hidden" name="date" value="<?= e($date) ?>">
                <label class="u51-field"><span>Typ hodiny</span><select name="kind"><option value="work">Samostatná práce k dnešní lekci</option><option value="intake"<?= $cid === 'class_1a' ? ' selected' : '' ?>>Seznamovací dotazník</option></select></label>
                <button class="btn primary" type="submit">Otevřít hodinu a vygenerovat kód</button>
              </form>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($session): $subs = sess53_submissions((string)$session['id']); $students = project_students_for_class($classId); ?>
        <section class="t52-card">
          <div class="t52-t-head">
            <div><span class="t52-kicker">Kód na tabuli</span><h2><?= e((string)$modules[$classId]['name']) ?> · <?= e((string)$session['title']) ?></h2></div>
            <div class="t52-t-actions"><button type="button" class="btn secondary" data-s53-project>Promítnout kód</button></div>
          </div>
          <div class="s53-projection" data-s53-projection hidden>
            <span>Kód hodiny</span><strong><?= e((string)$session['code']) ?></strong>
            <small><?= e((string)$modules[$classId]['name']) ?> · <?= e(tut52_cz_date($date)) ?><?= (string)$session['kind'] === 'intake' ? ' · seznamovací dotazník' : '' ?></small>
            <p>Žák: přihlásí se školním e-mailem → <b>Zadej kód hodiny</b><?= (string)$session['kind'] === 'intake' ? ' (1.A rovnou vyplní jméno, e-mail a místo)' : '' ?>.</p>
            <button type="button" class="btn secondary" data-s53-projection-close>Zavřít</button>
          </div>
          <form method="post" class="s53-instructions-form">
            <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="sess53_t_instructions"><input type="hidden" name="session" value="<?= e((string)$session['id']) ?>">
            <label class="u51-field"><span>Pokyny pro žáky (volitelné)</span><textarea name="instructions" rows="2" placeholder="Např. pracujte ve dvojicích, odevzdejte odkaz na Canvu."><?= e((string)$session['instructions']) ?></textarea></label>
            <button class="btn secondary" type="submit">Uložit pokyny</button>
          </form>
        </section>

        <?php if ((string)$session['kind'] === 'work'):
            $lessonNo = max(1, (int)($session['lesson_number'] ?? 1));
            $bundle = function_exists('v56_lesson_bundle')
                ? v56_lesson_bundle($classId, $modules[$classId], $lessonNo, (array)($GLOBALS['nextLessons'] ?? []), (array)($GLOBALS['extendedLessons'] ?? []))
                : ['topics' => [], 'steps' => [], 'questions' => []];
            $topicTotal = max(1, count((array)$bundle['topics']));
            $stepTotal = max(1, count((array)$bundle['steps']));
            $phaseCount = ['theory' => 0, 'test' => 0, 'project' => 0, 'submit' => 0];
            foreach ($students as $sKey => $_s) {
                $st = function_exists('v56_lesson_state') ? v56_lesson_state($classId, (string)$sKey, $bundle) : null;
                if (!is_array($st)) continue;
                $phaseCount[(string)($st['current'] ?? 'submit')] = ($phaseCount[(string)($st['current'] ?? 'submit')] ?? 0) + ($st['complete'] ? 0 : 1);
            }
        ?>
        <section class="t52-card">
          <header class="t52-card-head"><span class="t52-kicker">Průběh třídy · lekce <?= $lessonNo ?></span><strong><?= count($subs) ?> z <?= count($students) ?> žáků pracuje</strong></header>
          <p class="t52-muted small">Teď na teorii: <b><?= (int)$phaseCount['theory'] ?></b> · na testu: <b><?= (int)$phaseCount['test'] ?></b> · na projektu: <b><?= (int)$phaseCount['project'] ?></b> · před odevzdáním: <b><?= (int)$phaseCount['submit'] ?></b></p>
          <div class="u51-table-wrap"><table class="u51-table">
            <thead><tr><th>Žák</th><th>Kde je</th><th>Odevzdání</th><th>Známka → body</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($students as $key => $student): $sub = $subs[$key] ?? null;
                $st = function_exists('v56_lesson_state') ? v56_lesson_state($classId, (string)$key, $bundle) : null;
                $ph = is_array($st) ? $st['phases'] : [];
                $cur = is_array($st) ? (string)($st['current'] ?? '') : '';
            ?>
              <tr>
                <td><strong><?= e((string)$student['label']) ?></strong><br><small><?= e((string)($student['email'] ?? '')) ?></small></td>
                <td>
                  <?php if (!is_array($st)): ?>—
                  <?php elseif ($st['complete']): ?><span class="u51-pill ok">hotovo</span>
                  <?php else: ?>
                    <span class="u51-pill <?= $cur === 'theory' ? 'warn' : '' ?>"><?= e((string)$ph[$cur]['label']) ?></span>
                    <small class="s53-phase-detail">T <?= (int)$ph['theory']['progress'] ?>/<?= $topicTotal ?> · test <?= (int)$ph['test']['progress'] ?> % · P <?= (int)$ph['project']['progress'] ?>/<?= $stepTotal ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!$sub): ?><span class="u51-pill">nezačal</span>
                  <?php else: ?>
                    <span class="u51-pill <?= ($sub['status'] ?? '') === 'submitted' ? 'ok' : 'warn' ?>"><?= ($sub['status'] ?? '') === 'submitted' ? 'odevzdáno' : 'pracuje' ?></span>
                    <?php if (trim((string)($sub['note'] ?? '')) !== ''): ?><details><summary>text</summary><p><?= nl2br(e((string)$sub['note'])) ?></p></details><?php endif; ?>
                    <?php if (safe_url((string)($sub['link'] ?? '')) !== ''): ?><br><a class="t52-link" href="<?= e(safe_url((string)$sub['link'])) ?>" target="_blank" rel="noopener noreferrer">odkaz ↗</a><?php endif; ?>
                  <?php endif; ?>
                </td>
                <td><?= !empty($sub['grade']) ? (int)$sub['grade'] . ' → +' . (int)($sub['points'] ?? 0) . ' b.' : '—' ?></td>
                <td>
                  <?php if ($sub): ?>
                  <form method="post" class="s53-grade-form">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="sess53_t_grade"><input type="hidden" name="session" value="<?= e((string)$session['id']) ?>"><input type="hidden" name="student_key" value="<?= e((string)$key) ?>">
                    <select name="grade" aria-label="Známka"><option value="">bez známky</option><?php for ($g = 1; $g <= 5; $g++): ?><option value="<?= $g ?>"<?= (int)($sub['grade'] ?? 0) === $g ? ' selected' : '' ?>><?= $g ?><?= $g <= 3 ? ' (+' . pts53_points_for_grade($g) . ' b.)' : '' ?></option><?php endfor; ?></select>
                    <input name="comment" maxlength="400" placeholder="Krátká zpětná vazba" value="<?= e((string)($sub['teacher_comment'] ?? '')) ?>">
                    <button class="btn secondary" type="submit">Uložit</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
        </section>
        <section class="t52-card">
          <header class="t52-card-head"><span class="t52-kicker">Rozšiřující zadání</span><strong>Bonus podle zbývajícího času</strong></header>
          <p class="t52-muted">Systém dá žákovi delší nebo kratší zadání podle toho, kolik času mu do konce bloku zbývalo, a podle toho sám určí váhu hodnocení. Žák váhu nevidí – vidí jen svůj výsledek.</p>
          <?php $bonusRows = (array)(v55_bonus_all()[(string)$session['id']] ?? []); ?>
          <?php if (!$bonusRows): ?>
            <p class="t52-muted">Zatím nikdo bonus nespustil.</p>
          <?php else: ?>
          <div class="u51-table-wrap"><table class="u51-table">
            <thead><tr><th>Žák</th><th>Varianta</th><th>Váha</th><th>Odpovědi</th><th>Známka → body</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($bonusRows as $key => $bonus): if (!is_array($bonus)) continue; $student = $students[$key] ?? null; ?>
              <tr>
                <td><strong><?= e((string)($student['label'] ?? $key)) ?></strong></td>
                <td><?= e((string)($bonus['label'] ?? '')) ?><br><small><?= (int)($bonus['minutes'] ?? 0) ?> min<?= !empty($bonus['late']) ? ' · po termínu' : '' ?></small></td>
                <td><span class="u51-pill"><?= e(v55_weight_label((float)($bonus['weight'] ?? 1))) ?></span></td>
                <td>
                  <?php if ((string)($bonus['status'] ?? '') !== 'submitted'): ?><span class="u51-pill warn">pracuje</span>
                  <?php else: ?>
                    <details><summary><?= count((array)($bonus['answers'] ?? [])) ?> odpovědí</summary>
                      <?php foreach ((array)($bonus['tasks'] ?? []) as $bi => $t): ?>
                        <p><b><?= e((string)$t['title']) ?></b><br><?= nl2br(e((string)(($bonus['answers'][$bi] ?? '—')))) ?></p>
                      <?php endforeach; ?>
                    </details>
                  <?php endif; ?>
                </td>
                <td><?= !empty($bonus['grade']) ? (int)$bonus['grade'] . ' → +' . (int)($bonus['points'] ?? 0) . ' b.' : '—' ?></td>
                <td>
                  <form method="post" class="s53-grade-form">
                    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="action" value="v55_t_bonus_grade"><input type="hidden" name="session" value="<?= e((string)$session['id']) ?>"><input type="hidden" name="student_key" value="<?= e((string)$key) ?>">
                    <select name="grade" aria-label="Známka bonusu"><option value="">bez známky</option><?php for ($g = 1; $g <= 5; $g++): ?><option value="<?= $g ?>"<?= (int)($bonus['grade'] ?? 0) === $g ? ' selected' : '' ?>><?= $g ?><?= $g <= 3 ? ' (+' . v55_bonus_points($g, (float)($bonus['weight'] ?? 1)) . ' b.)' : '' ?></option><?php endfor; ?></select>
                    <input name="comment" maxlength="400" placeholder="Krátká zpětná vazba" value="<?= e((string)($bonus['teacher_comment'] ?? '')) ?>">
                    <button class="btn secondary" type="submit">Uložit</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table></div>
          <?php endif; ?>
        </section>
        <?php else: ?>
          <section class="t52-card"><header class="t52-card-head"><span class="t52-kicker">Seznamovací dotazník</span><strong>Odpovědi najdeš v záložce Dotazník</strong></header><a class="btn primary" href="<?= e('teacher.php?tab=intake&class=' . $classId) ?>">Otevřít odpovědi třídy</a></section>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <script src="assets/session-v53-teacher.js?v=53.0" defer></script>
    <?php
}
