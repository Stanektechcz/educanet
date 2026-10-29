<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v53 · studentské obrazovky hodiny: vstup kódem, registrace 1.A a samostatná práce. */

function sess53_render_join(array $modules, string $flash): void
{
    $code = (string)($_SESSION['sess53_code'] ?? (string)($_GET['code'] ?? ''));
    $session = $code !== '' ? sess53_find_by_code($code) : null;
    if ($session && empty($session['open'])) $session = null;
    $class = $session ? ($modules[(string)$session['class_id']] ?? null) : null;
    render_header(tr('Vstup do hodiny'));
    ?>
    <section class="u51-narrow">
      <ol class="u51-progress-dots"><li class="<?= $session ? 'done' : 'current' ?>"><?= e(tr('Kód hodiny')) ?></li><li class="<?= $session ? 'current' : '' ?>"><?= e(tr('Kdo jsi')) ?></li><li><?= e(tr('Hodina')) ?></li></ol>
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <?php if (!$session || !is_array($class)): ?>
        <form class="u51-card" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_code">
          <span class="u51-kicker"><?= e(tr('Dnešní hodina')) ?></span>
          <h1><?= e(tr('Zadej kód hodiny')) ?></h1>
          <p class="u51-lead"><?= e(tr('Kód promítá učitel na tabuli. Platí jen pro dnešní hodinu tvé třídy.')) ?></p>
          <label class="u51-field"><span><?= e(tr('Kód')) ?></span><input class="u51-code" name="code" required maxlength="8" autocomplete="off" autocapitalize="characters" placeholder="ABC123" autofocus></label>
          <button class="btn primary wide" type="submit"><?= e(tr('Pokračovat')) ?></button>
          <a class="u51-link" href="?view=home"><?= e(tr('Mám účet a chci se přihlásit')) ?></a>
        </form>
      <?php elseif ((string)$session['kind'] === 'intake' && !auth_is_signed_in()):
        $classes = intake_v51_classes($modules);
        $room = $classes[(string)$session['class_id']] ?? null;
        $occupied = [];
        foreach (intake_v51_responses_for_class((string)$session['class_id']) as $r) $occupied[(string)($r['student']['seat_id'] ?? '')] = true;
        $seatMap = $room ? intake_v51_seat_map($room) : [];
      ?>
        <form class="u51-card" method="post" data-sess53-register>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_register"><input type="hidden" name="code" value="<?= e((string)$session['code']) ?>">
          <span class="u51-kicker"><?= e((string)$class['name']) ?> · <?= e((string)$class['subject']) ?></span>
          <h1><?= e(tr('Kdo jsi?')) ?></h1>
          <p class="u51-lead"><?= e(tr('Vytvoříme ti školní účet, ať se ti uloží odpovědi i postup v kurzu.')) ?></p>
          <div class="u51-grid-2">
            <label class="u51-field"><span><?= e(tr('Jméno')) ?></span><input name="first_name" required maxlength="60" autocomplete="given-name" data-sess53-first></label>
            <label class="u51-field"><span><?= e(tr('Příjmení')) ?></span><input name="last_name" required maxlength="60" autocomplete="family-name" data-sess53-last></label>
          </div>
          <label class="u51-field"><span><?= e(tr('Školní e-mail')) ?></span><input type="email" name="email" required maxlength="120" autocomplete="email" placeholder="jmeno.prijmeni@<?= e(google_workspace_domain()) ?>" data-sess53-email><small><?= e(tr('Doplní se sám podle jména. Po dotazníku si hned nastavíš vlastní heslo.')) ?></small></label>
          <div class="u51-field"><span><?= e(tr('Kde sedíš?')) ?></span><small><?= e(tr('Řada 1 je nejblíž tabuli. L = vlevo, P = vpravo.')) ?></small></div>
          <div class="u51-room">
            <div class="u51-board"><?= e(tr('Tabule')) ?></div>
            <div class="u51-desks" style="--u51-cols:<?= (int)($room['cols'] ?? 6) ?>">
              <?php for ($row = 1; $row <= (int)($room['rows'] ?? 8); $row++): for ($desk = 1; $desk <= (int)($room['cols'] ?? 6); $desk++):
                  $left = $seatMap[$row][$desk]['left'] ?? null; $right = $seatMap[$row][$desk]['right'] ?? null;
                  if (empty($left['active']) && empty($right['active'])): ?><div class="u51-desk off" aria-hidden="true"></div><?php continue; endif; ?>
                <div class="u51-desk" title="<?= e(tr('Řada {row} · Lavice {desk}', ['row' => $row, 'desk' => $desk])) ?>">
                  <?php foreach ([[$left, 'L'], [$right, 'P']] as [$seat, $short]): if (!$seat) continue; $occ = !empty($occupied[(string)$seat['id']]); ?>
                    <button type="button" class="u51-seat<?= $occ ? ' taken' : '' ?>" data-u51-seat="<?= e((string)$seat['id']) ?>" data-label="<?= e((string)$seat['label']) ?>" <?= $occ ? 'disabled' : '' ?>><?= $short ?></button>
                  <?php endforeach; ?>
                </div>
              <?php endfor; endfor; ?>
            </div>
            <div class="u51-room-foot"><span><i class="u51-dot free"></i><?= e(tr('volné')) ?></span><span><i class="u51-dot taken"></i><?= e(tr('obsazené')) ?></span><strong data-u51-seat-label><?= e(tr('Místo zatím nevybráno')) ?></strong></div>
          </div>
          <input type="hidden" name="seat_id" data-u51-seat-input required>
          <button class="btn primary wide" type="submit"><?= e(tr('Vytvořit účet a pokračovat')) ?></button>
        </form>
      <?php else: ?>
        <form class="u51-card" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_enter"><input type="hidden" name="code" value="<?= e((string)$session['code']) ?>">
          <span class="u51-kicker"><?= e((string)$class['name']) ?> · <?= e(tut52_cz_date((string)$session['date'])) ?></span>
          <h1><?= e((string)$session['title']) ?></h1>
          <p class="u51-lead"><?= e((string)$session['goal']) ?></p>
          <?php if (!auth_is_signed_in()): ?>
            <p class="u51-lead"><?= e(tr('Nejdřív se přihlas školním e-mailem, pak tě kód pustí do hodiny.')) ?></p>
            <a class="btn primary wide" href="?view=home"><?= e(tr('Přihlásit se')) ?></a>
          <?php else: ?>
            <button class="btn primary wide" type="submit"><?= e(tr('Vstoupit do hodiny')) ?></button>
          <?php endif; ?>
        </form>
      <?php endif; ?>
    </section>
    <script src="assets/ui-v51.js?v=51.0" defer></script>
    <script src="assets/session-v53.js?v=53.0" defer></script>
    <?php
    render_footer();
}

/** Dnešní hodina: zadání, postup a odevzdání. */
function sess53_render_work(string $classId, array $module, array $session, array $modules, string $flash): void
{
    $studentKey = adaptive_student_key($classId);
    $label = trim((string)($_SESSION['student_label'] ?? ''));
    $sub = sess53_submission((string)$session['id'], $studentKey);
    $tasks = (array)$session['tasks'];
    $done = count(array_filter((array)$sub['checks']));
    $balance = pts53_balance($classId, $studentKey);
    $lessonNo = (int)$session['lesson_number'];
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $lessonTools = array_slice(array_keys($tools), 0, 3);
    render_header(tr('Dnešní hodina'), $module);
    ?>
    <div class="t52 s53">
      <?php if ($flash !== ''): ?><div class="u51-notice ok"><?= e($flash) ?></div><?php endif; ?>
      <header class="s53-head">
        <div>
          <span class="t52-kicker"><?= e((string)$module['name']) ?> · <?= e(tut52_cz_date((string)$session['date'])) ?> · <?= e(tr('samostatná práce')) ?></span>
          <h1><?= e((string)$session['title']) ?></h1>
          <p><?= e((string)$session['goal']) ?></p>
        </div>
        <div class="s53-badges">
          <span class="s53-points" title="<?= e(tr('Body sbíráš za úkoly a známky. Utrácíš je za extra nápovědy.')) ?>"><b><?= e(trn(['one' => '{n} bod', 'few' => '{n} body', 'other' => '{n} bodů'], $balance)) ?></b></span>
          <span class="t52-status <?= ($sub['status'] ?? '') === 'submitted' ? 'done' : 'current' ?>"><?= ($sub['status'] ?? '') === 'submitted' ? e(tr('Odevzdáno')) : e(tr('Pracuješ')) ?></span>
        </div>
      </header>

      <?php if (trim((string)$session['instructions']) !== ''): ?>
        <div class="t52-card s53-instructions"><span class="t52-kicker"><?= e(tr('Pokyny učitele')) ?></span><p><?= nl2br(e((string)$session['instructions'])) ?></p></div>
      <?php endif; ?>

      <div class="s53-grid">
        <section class="t52-card">
          <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Krok za krokem')) ?></span><strong><?= e(tr('Co dnes uděláš')) ?></strong></header>
          <ol class="s53-steps">
            <?php if (!is_array($GLOBALS['completedTestResult'] ?? null)): ?>
            <li><strong><?= e(tr('Krátká vstupní diagnostika')) ?></strong><span><?= e(tr('Bez známky · ukáže, co už umíš. Zabere 10 minut.')) ?></span><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="start_test"><button class="btn primary" type="submit"><?= e(tr('Spustit test')) ?></button></form></li>
            <?php endif; ?>
            <li><strong><?= e(tr('Projdi tutoriál lekce')) ?></strong><span><?= e(tr('Ukázky, vysvětlení a interaktivní úkoly za body.')) ?></span><a class="btn primary" href="<?= e(tut52_tutorial_url($lessonNo)) ?>"><?= e(tr('Otevřít tutoriál lekce {n}', ['n' => $lessonNo])) ?></a></li>
            <li><strong><?= e(tr('Splň body samostatné práce')) ?></strong><span><?= e(tr('Odškrtávej si je níž, jak postupuješ.')) ?></span></li>
            <li><strong><?= e(tr('Odevzdej výsledek')) ?></strong><span><?= e(tr('Krátký popis + odkaz nebo soubor. Učitel ti dá známku a body.')) ?></span></li>
          </ol>
        </section>
        <section class="t52-card">
          <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Kde pracuješ')) ?></span><strong><?= e(tr('Doporučené programy')) ?></strong></header>
          <div class="t52-tool-mini">
            <?php foreach ($lessonTools as $tk): $tool = $tools[$tk]; ?>
              <a href="?view=tools#<?= e($tk) ?>"><strong><?= e($tool['name']) ?></strong><small><?= e($tool['use']) ?></small></a>
            <?php endforeach; ?>
          </div>
        </section>
      </div>

      <form class="t52-card s53-work" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="sess53_submit"><input type="hidden" name="session" value="<?= e((string)$session['id']) ?>">
        <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Samostatná práce · {done} / {total} hotovo', ['done' => $done, 'total' => count($tasks)])) ?></span><strong><?= e(tr('Body k splnění')) ?></strong></header>
        <div class="t52-checklist">
          <?php foreach ($tasks as $i => $task): $checked = in_array((string)$i, (array)$sub['checks'], true); ?>
            <label><input type="checkbox" name="checks[]" value="<?= $i ?>"<?= $checked ? ' checked' : '' ?>><span><strong><?= e((string)$task['title']) ?></strong><?php if (trim((string)$task['detail']) !== ''): ?><small><?= e((string)$task['detail']) ?></small><?php endif; ?></span><?php if (trim((string)$task['time']) !== ''): ?><em><?= e((string)$task['time']) ?></em><?php endif; ?></label>
          <?php endforeach; ?>
        </div>
        <label class="u51-field"><span><?= e(tr('Co jsi zjistil/a nebo vytvořil/a?')) ?></span><textarea name="note" rows="4" placeholder="<?= e(tr('3–5 vět: co jsi udělal/a, co ti fungovalo a kde ses zasekl/a.')) ?>"><?= e((string)$sub['note']) ?></textarea></label>
        <label class="u51-field"><span><?= e(tr('Odkaz na práci (Canva, Figma, dokument…)')) ?></span><input name="link" maxlength="500" placeholder="https://…" value="<?= e((string)$sub['link']) ?>"></label>
        <?php if (!empty($sub['teacher_comment'])): ?>
          <div class="s53-feedback"><span class="t52-kicker"><?= e(tr('Hodnocení učitele')) ?><?= !empty($sub['grade']) ? e(tr(' · známka {grade} · +{points} bodů', ['grade' => (int)$sub['grade'], 'points' => (int)($sub['points'] ?? 0)])) : '' ?></span><p><?= nl2br(e((string)$sub['teacher_comment'])) ?></p></div>
        <?php endif; ?>
        <div class="t52-step-foot">
          <button class="btn secondary" type="submit" name="status" value="draft"><?= e(tr('Uložit rozpracované')) ?></button>
          <button class="btn primary" type="submit" name="status" value="submitted"><?= e(tr('Odevzdat učiteli')) ?></button>
        </div>
      </form>
    </div>
    <?php
    tut52_assets();
    render_footer();
}
