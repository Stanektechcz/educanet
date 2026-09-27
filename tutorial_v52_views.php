<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** v52 · obrazovky Tutorial Mode: kurz, tutoriál lekce, kalendář, témata, programy a zkratky. */

function tut52_assets(): void
{
    // GSAP 3 (vendored) + v52 scény a úkoly. Načítá se na konci stránky.
    echo '<script src="assets/vendor/gsap-3.15.0/gsap.min.js" defer></script>';
    echo '<script src="assets/vendor/gsap-3.15.0/MotionPathPlugin.min.js" defer></script>';
    echo '<script src="assets/vendor/gsap-3.15.0/Flip.min.js" defer></script>';
    echo '<script src="assets/vendor/gsap-3.15.0/Draggable.min.js" defer></script>';
    echo '<script src="assets/vendor/gsap-3.15.0/TextPlugin.min.js" defer></script>';
    echo '<script src="assets/tutorial-v52.js?v=54.1" defer></script>';
}

function tut52_subnav(string $active): void
{
    $items = ['course' => tr('Kurz'), 'calendar' => tr('Kalendář'), 'topics' => tr('Témata'), 'tools' => tr('Programy a zkratky')];
    echo '<nav class="t52-subnav" aria-label="' . e(tr('Studium')) . '">';
    foreach ($items as $view => $label) {
        echo '<a href="?view=' . e($view) . '"' . ($active === $view ? ' class="active" aria-current="page"' : '') . '>' . e($label) . '</a>';
    }
    echo '</nav>';
}

function tut52_json(array $data): string
{
    return e((string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function tut52_status_label(string $status): string
{
    return match ($status) { 'done' => tr('Hotovo'), 'current' => tr('Teď'), 'open' => tr('Odemčeno'), default => tr('Zamčeno') };
}

function tut52_tutorial_url(int $lessonNo, int $step = 0): string
{
    return '?view=tutorial&lesson=' . $lessonNo . ($step > 0 ? '#krok-' . $step : '');
}

/** Karta s animovanou ukázkou (GSAP scéna). */
function tut52_render_scene(string $scene, string $topicTitle = ''): void
{
    $meta = tut52_scene_meta($scene);
    ?>
    <figure class="t52-scene" data-t52-scene="<?= e($scene) ?>">
      <figcaption class="t52-card-head"><span class="t52-kicker"><?= e(tr('Animovaná ukázka')) ?><?= $topicTitle !== '' ? ' · ' . edu_cs($topicTitle) : '' ?></span><strong<?= edu_content_lang_attr() ?>><?= e($meta['title']) ?></strong></figcaption>
      <div class="t52-stage" data-t52-stage role="img" aria-label="<?= e($meta['title']) ?>"<?= edu_content_lang_attr() ?>></div>
      <ol class="t52-captions" data-t52-captions<?= edu_content_lang_attr() ?>><?php foreach ($meta['caption'] as $i => $c): ?><li data-i="<?= $i ?>"><?= e($c) ?></li><?php endforeach; ?></ol>
      <div class="t52-scene-controls">
        <button type="button" class="btn primary" data-t52-play><?= e(tr('Přehrát')) ?></button>
        <button type="button" class="btn secondary" data-t52-step><?= e(tr('Po krocích')) ?></button>
        <button type="button" class="btn secondary" data-t52-replay><?= e(tr('Znovu')) ?></button>
      </div>
    </figure>
    <?php
}

/** Karta s interaktivním úkolem. */
function tut52_render_exercise(string $scene, string $exerciseId, array $scores, bool $noSave = false): void
{
    $ex = tut52_exercise($scene);
    $best = (int)($scores[$exerciseId]['points'] ?? 0);
    $balance = 0;
    if (!$noSave && function_exists('pts53_balance')) {
        $balanceClass = current_class_id($GLOBALS['modules']);
        if ($balanceClass !== null) $balance = pts53_balance($balanceClass, adaptive_student_key($balanceClass));
    }
    ?>
    <section class="t52-exercise" data-t52-exercise="<?= tut52_json($ex) ?>" data-id="<?= e($exerciseId) ?>" data-best="<?= $best ?>"<?= $noSave ? ' data-no-save' : '' ?>>
      <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Interaktivní úkol · max. 3 b.')) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)$ex['prompt']) ?></strong></header>
      <div class="t52-ex-body" data-t52-ex-body><noscript><?= e(tr('Interaktivní úkol potřebuje zapnutý JavaScript.')) ?></noscript></div>
      <?php if (!$noSave): ?>
      <div class="t52-hints" data-t52-hints data-scene="<?= e($scene) ?>">
        <div class="t52-hints-head"><span><?= e(tr('Nevíš si rady?')) ?></span><span data-t52-balance><?= tr_html('Body: {n}', ['n' => '<b>' . $balance . '</b>']) ?></span></div>
        <div class="t52-hint-buttons">
          <button type="button" data-t52-hint="1"><?= e(tr('Nápověda zdarma')) ?></button>
          <button type="button" data-t52-hint="2"><?= e(tr('Extra nápověda · 2 body')) ?></button>
          <button type="button" data-t52-hint="3"><?= e(tr('Ukázat řešení · 2 body')) ?></button>
        </div>
        <div class="t52-hint-out" data-t52-hint-out></div>
      </div>
      <?php endif; ?>
      <footer class="t52-ex-foot"><span data-t52-ex-status><?= $noSave ? e(tr('Ukázka úkolu – body se neukládají')) : ($best > 0 ? e(tr('Nejlepší výsledek: {n} / 3 b.', ['n' => $best])) : e(tr('Zatím nevyřešeno'))) ?></span><button type="button" class="btn primary" data-t52-check><?= e(tr('Zkontrolovat')) ?></button><button type="button" class="btn secondary" data-t52-reset><?= e(tr('Znovu')) ?></button></footer>
    </section>
    <?php
}

// ---------------------------------------------------------------------------
// Kurz
// ---------------------------------------------------------------------------

function tut52_render_course(string $classId, array $module, array $modules, array $lessons, array $scores, string $flash): void
{
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $current = tut52_current_lesson($lessons);
    $done = count(array_filter($lessons, static fn($l) => $l['done']));
    $total = count($lessons);
    $pct = $total ? (int)round($done / $total * 100) : 0;
    $totalPoints = array_sum(array_map(static fn($r) => (int)($r['points'] ?? 0), $scores));
    render_header(tr('Kurz'), $module);
    tut52_subnav('course');
    ?>
    <div class="t52 t52-course">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="t52-page-head">
        <div><span class="t52-kicker"<?= edu_content_lang_attr() ?>><?= e((string)$module['name']) ?> · <?= e((string)$module['subject']) ?></span><h1><?= e(tr('Kurz krok za krokem')) ?></h1><p><?= e(tr('Každá lekce je tutoriál: přehled hodiny → ukázka → interaktivní úkol → kontrola.')) ?></p></div>
        <div class="t52-ring" style="--p:<?= $pct ?>" data-t52-count="<?= $pct ?>"><strong><?= $pct ?> %</strong><span><?= e(tr('{done} / {total} lekcí · {points} b.', ['done' => $done, 'total' => $total, 'points' => $totalPoints])) ?></span></div>
      </header>

      <?php if ($current): ?>
      <section class="t52-hero-lesson" data-t52-reveal>
        <div class="t52-hero-copy">
          <span class="t52-kicker"><?= e(tr('Teď · Lekce {n}', ['n' => (int)$current['number']])) ?><?= $current['date'] !== '' ? ' · ' . e(tut52_cz_date($current['date'])) : '' ?></span>
          <h2<?= edu_content_lang_attr() ?>><?= e($current['title']) ?></h2>
          <p<?= edu_content_lang_attr() ?>><?= e($current['goal']) ?></p>
          <div class="t52-chips"<?= edu_content_lang_attr() ?>><?php foreach (array_slice($current['topics'], 0, 4) as $t): ?><span><?= e(tut52_topic_title($modules, $classId, (string)$t)) ?></span><?php endforeach; ?></div>
          <div class="t52-progress-line"><i style="width:<?= (int)round($current['done_steps'] / max(1, $current['total_steps']) * 100) ?>%"></i></div>
          <small><?= e(tr('{done} / {total} kroků', ['done' => (int)$current['done_steps'], 'total' => (int)$current['total_steps']])) ?></small>
        </div>
        <div class="t52-hero-side">
          <div class="t52-mini-scene" data-t52-scene="<?= e(tut52_scene((string)($current['topics'][0] ?? ''), $family)) ?>" data-t52-autoplay><div class="t52-stage" data-t52-stage></div></div>
          <a class="btn primary wide" href="<?= e(tut52_tutorial_url((int)$current['number'])) ?>"><?= $current['done_steps'] > 0 ? e(tr('Pokračovat v tutoriálu')) : e(tr('Začít tutoriál')) ?></a>
        </div>
      </section>
      <?php endif; ?>

      <section class="t52-path" aria-label="<?= e(tr('Lekce kurzu')) ?>">
        <?php $groups = []; foreach ($lessons as $l) { $m = $l['date'] !== '' ? (int)date('n', strtotime($l['date'])) : 0; $groups[$m][] = $l; } ?>
        <?php foreach ($groups as $month => $items): ?>
          <h3 class="t52-path-month"><?= $month ? e(tut52_month_name($month)) : e(tr('Bez termínu')) ?></h3>
          <ol class="t52-path-list">
          <?php foreach ($items as $l): $lp = tut52_lesson_points($scores, (int)$l['number']); ?>
            <li class="t52-lesson <?= e($l['status']) ?>" data-t52-reveal>
              <a href="<?= e(tut52_tutorial_url((int)$l['number'])) ?>">
                <span class="t52-lesson-no"><?= $l['done'] ? '✓' : str_pad((string)$l['number'], 2, '0', STR_PAD_LEFT) ?></span>
                <span class="t52-lesson-main">
                  <strong<?= edu_content_lang_attr() ?>><?= e($l['title']) ?></strong>
                  <small><?= $l['date'] !== '' ? e(tut52_cz_date($l['date'], false)) . ' · ' : '' ?><?= e(tr('{done}/{total} kroků', ['done' => (int)$l['done_steps'], 'total' => (int)$l['total_steps']])) ?><?= $lp['points'] ? ' · ' . e(tr('{n} b.', ['n' => $lp['points']])) : '' ?></small>
                  <span class="t52-lesson-tools"><?php foreach ($l['tools'] as $tk): ?><em><?= e((string)($tools[$tk]['name'] ?? $tk)) ?></em><?php endforeach; ?></span>
                </span>
                <span class="t52-status"><?= e(tut52_status_label($l['status'])) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
          </ol>
        <?php endforeach; ?>
      </section>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

// ---------------------------------------------------------------------------
// Tutoriál lekce
// ---------------------------------------------------------------------------

function tut52_primary_flow(string $classId, array $module, ?array $completedTest, array $simulationMap): array
{
    $graphics = tut52_family($classId, $module) === 'graphics';
    $testDone = is_array($completedTest);
    $csrf = csrf_token();
    $flow = [['id' => 'test', 'title' => tr('Startovní test'), 'text' => tr('Krátká diagnostika bez známky. Za každou správnou odpověď 1 bod.'), 'done' => $testDone, 'form' => $testDone ? '' : 'start_test', 'href' => $testDone ? '?view=result' : '', 'label' => $testDone ? tr('Zobrazit výsledek') : tr('Spustit test'), 'topic' => $graphics ? 'hierarchy' : 'dns']];
    if ($graphics) {
        $flow[] = ['id' => 'core', 'title' => tr('Základní principy designu'), 'text' => tr('Hierarchie, kompozice a kontrast v interaktivních lekcích.'), 'done' => learning_graphics_core_complete($classId, $simulationMap), 'href' => module_url('kb_lesson', ['topic' => 'hierarchy']), 'label' => tr('Otevřít lekce'), 'topic' => 'composition'];
        $flow[] = ['id' => 'studio', 'title' => tr('Studio'), 'text' => tr('Vyzkoušej hierarchii, mřížku a export na vlastním návrhu.'), 'done' => learning_studio_complete($classId), 'href' => '?view=graphics_studio', 'label' => tr('Otevřít Studio'), 'topic' => 'spacing'];
        $flow[] = ['id' => 'project', 'title' => tr('Plakát v Canvě'), 'text' => tr('Převeď principy do hotového plakátu a odevzdej ho.'), 'done' => learning_primary_block_complete($classId), 'href' => '?view=graphics_guide', 'label' => tr('Otevřít zadání'), 'topic' => 'export'];
    } else {
        $flow[] = ['id' => 'case', 'title' => tr('Případová studie'), 'text' => tr('Topologie, tikety a simulovaný terminál – nejdřív důkaz, potom změna.'), 'done' => learning_journey_step_done($classId, 'case_study'), 'href' => '?view=case_study', 'label' => tr('Otevřít studii'), 'topic' => 'troubleshooting'];
        $flow[] = ['id' => 'lab', 'title' => tr('Praktický lab'), 'text' => tr('Incidenty s checkpointy, nápovědami a body.'), 'done' => learning_primary_block_complete($classId), 'href' => '?view=practice', 'label' => tr('Spustit lab'), 'topic' => 'ports'];
    }
    return $flow;
}

function tut52_render_tutorial(string $classId, array $module, array $modules, array $lessons, int $lessonNo, array $schoolYear, array $scores, ?array $completedTest, array $simulationMap, string $flash): void
{
    $lesson = $lessons[$lessonNo] ?? null;
    if (!$lesson) redirect_to('?view=course');
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $schedule = adaptive_class_schedule($schoolYear, $classId);
    $time = trim((string)($schedule['start'] ?? '')) !== '' ? $schedule['start'] . '–' . $schedule['end'] : tr('2 × 45 minut');
    $locked = $lesson['status'] === 'locked';
    $stepAction = $lesson['kind'] === 'next' ? 'next_lesson_step' : 'course_lesson_step';
    $nextLesson = $lessons[$lessonNo + 1] ?? null;
    $topics = array_values(array_map('strval', $lesson['topics']));
    render_header(tr('Lekce {n} · {title}', ['n' => $lessonNo, 'title' => $lesson['title']]), $module, true);
    ?>
    <div class="t52 t52-tutorial" data-t52-tutorial data-lesson="<?= e($lesson['id']) ?>" data-lesson-no="<?= $lessonNo ?>" data-step-action="<?= e($stepAction) ?>" data-locked="<?= $locked ? '1' : '0' ?>">
      <header class="t52-tut-head">
        <a class="t52-back" href="?view=course"><?= e(tr('Kurz')) ?></a>
        <div class="t52-tut-title"><span class="t52-kicker"><?= e(tr('Lekce {n}', ['n' => $lessonNo])) ?><?= $lesson['date'] !== '' ? ' · ' . e(tut52_cz_date($lesson['date'])) : '' ?></span><strong<?= edu_content_lang_attr() ?>><?= e($lesson['title']) ?></strong></div>
        <span class="t52-status <?= e($lesson['status']) ?>"><?= e(tut52_status_label($lesson['status'])) ?></span>
        <div class="u51-bar t52-tut-bar"><i data-t52-bar></i></div>
        <ol class="t52-dots" data-t52-dots></ol>
      </header>
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <?php if ($locked): ?><div class="u51-notice"><?= e(tr('Náhled – lekce se odemkne po dokončení lekce {n}. Ukázky a úkoly si můžeš vyzkoušet už teď.', ['n' => $lessonNo - 1])) ?></div><?php endif; ?>

      <!-- Krok 0: přehled hodiny -->
      <section class="t52-slide" data-t52-slide data-title="<?= e(tr('Přehled hodiny')) ?>" id="krok-0">
        <div class="t52-overview">
          <div class="t52-card t52-when">
            <span class="t52-kicker"><?= e(tr('Naplánovaná hodina')) ?></span>
            <strong><?= $lesson['date'] !== '' ? e(tut52_cz_date($lesson['date'])) : e(tr('Termín upřesní učitel')) ?></strong>
            <span><?= e($time) ?><?= !empty($schedule['room']) ? ' · ' . tr_html('učebna {room}', ['room' => edu_cs((string)$schedule['room'])]) : '' ?></span>
          </div>
          <div class="t52-card t52-goal"><span class="t52-kicker"><?= e(tr('Cíl lekce')) ?></span><p<?= edu_content_lang_attr() ?>><?= e($lesson['goal']) ?></p></div>
        </div>
        <?php if ($lesson['schedule']): ?>
        <section class="t52-card">
          <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Plán 90 minut')) ?></span><strong><?= e(tr('Jak hodina poběží')) ?></strong></header>
          <ol class="t52-timeline" data-t52-timeline<?= edu_content_lang_attr() ?>>
            <?php foreach ($lesson['schedule'] as $seg): if (!is_array($seg)) continue; [$a, $b] = array_pad(array_map('intval', preg_split('/[–-]/u', (string)($seg['time'] ?? '0–15')) ?: [0, 15]), 2, 15); ?>
              <li style="--w:<?= max(6, $b - $a) ?>"><span><?= e((string)($seg['time'] ?? '')) ?> min</span><strong><?= e((string)($seg['title'] ?? '')) ?></strong><?php if (!empty($seg['text'])): ?><small><?= e((string)$seg['text']) ?></small><?php endif; ?></li>
            <?php endforeach; ?>
          </ol>
        </section>
        <?php endif; ?>
        <?php
        // Doporučené video k lekci z kurátorovaného Lesson Kitu (v42) – jedno, krátké, česky.
        $t52Video = null;
        if (function_exists('v42_find_lesson') && function_exists('v42_lesson_pack')) {
            $t52RawLesson = v42_find_lesson($classId, $lessonNo);
            if (is_array($t52RawLesson)) {
                $t52Pack = v42_lesson_pack($classId, $t52RawLesson, $module, (array)($GLOBALS['learningResources'] ?? []), '');
                $t52Video = (array)($t52Pack['videos'][0] ?? []);
            }
        }
        ?>
        <?php if (!empty($t52Video['url'])): ?>
        <section class="t52-card t52-video">
          <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Video k lekci · {length}', ['length' => (string)($t52Video['length'] ?? $t52Video['duration'] ?? tr('krátké'))])) ?></span><strong<?= edu_content_lang_attr() ?>><?= e((string)($t52Video['title'] ?? tr('Doporučené video'))) ?></strong></header>
          <?php $t52WatchFor = (string)($t52Video['watch_for'] ?? ''); ?>
          <p class="t52-muted"><?= tr_html('Při sledování hlídej: {what}', ['what' => $t52WatchFor !== '' ? edu_cs($t52WatchFor) : e(tr('hlavní princip a jeden konkrétní příklad.'))]) ?></p>
          <a class="btn primary" href="<?= e((string)$t52Video['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(tr('Přehrát video ↗')) ?></a>
        </section>
        <?php endif; ?>
        <div class="t52-overview">
          <section class="t52-card">
            <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Budeš potřebovat')) ?></span><strong><?= e(tr('Programy')) ?></strong></header>
            <div class="t52-tool-mini"<?= edu_content_lang_attr() ?>>
              <?php foreach ($lesson['tools'] as $tk): $tool = $tools[$tk] ?? null; if (!$tool) continue; ?>
                <a href="?view=tools#<?= e($tk) ?>"><strong><?= e($tool['name']) ?></strong><small><?= e($tool['use']) ?></small>
                  <?php $pairs = array_slice($tool['shortcuts'] ?: array_map(static fn($c) => [$c[0], $c[1]], (array)($tool['commands'] ?? [])), 0, 3); ?>
                  <span class="t52-kbd-row"><?php foreach ($pairs as [$k, $d]): ?><span><kbd><?= e($k) ?></kbd> <?= e($d) ?></span><?php endforeach; ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
          <section class="t52-card">
            <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Témata')) ?></span><strong><?= e(tr('Co se naučíš')) ?></strong></header>
            <ul class="t52-topic-list"<?= edu_content_lang_attr() ?>>
              <?php foreach ($topics as $t): $hasSim = isset($simulationMap[$t]); $tDone = isset($module['knowledgebase'][$t]) && learning_kb_complete($classId, $t, $hasSim); ?>
                <li><a href="<?= e(module_url('kb_lesson', ['topic' => $t])) ?>"><span class="<?= $tDone ? 'ok' : '' ?>"><?= $tDone ? '✓' : '○' ?></span><?= e(tut52_topic_title($modules, $classId, $t)) ?></a></li>
              <?php endforeach; ?>
            </ul>
          </section>
        </div>
      </section>

      <?php if ($lesson['kind'] === 'primary'): ?>
        <?php foreach (tut52_primary_flow($classId, $module, $completedTest, $simulationMap) as $i => $f): $scene = tut52_scene((string)$f['topic'], $family); $exId = 'L1:' . $f['id'] . ':' . $scene; ?>
        <section class="t52-slide" data-t52-slide data-title="<?= e($f['title']) ?>" data-done="<?= $f['done'] ? '1' : '0' ?>" id="krok-<?= $i + 1 ?>" hidden>
          <header class="t52-step-head"><span class="t52-kicker"><?= e(tr('Krok {n}', ['n' => $i + 1])) ?></span><h2><?= e($f['title']) ?></h2><p><?= e($f['text']) ?></p></header>
          <div class="t52-split"><?php tut52_render_scene($scene); tut52_render_exercise($scene, $exId, $scores); ?></div>
          <footer class="t52-step-foot">
            <?php if ($f['done']): ?><span class="t52-done-badge">✓ <?= e(tr('Hotovo')) ?></span><?php endif; ?>
            <?php if (!empty($f['form'])): ?>
              <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="<?= e($f['form']) ?>"><button class="btn primary" type="submit"><?= e($f['label']) ?></button></form>
            <?php else: ?><a class="btn <?= $f['done'] ? 'secondary' : 'primary' ?>" href="<?= e($f['href']) ?>"><?= e($f['label']) ?></a><?php endif; ?>
          </footer>
        </section>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($lesson['steps'] as $i => $step):
            $sid = (string)($step['id'] ?? '');
            $stepDone = !empty($lesson['progress'][$sid]);
            $topic = (string)(($step['knowledge'][0] ?? null) ?: ($topics ? $topics[$i % count($topics)] : ''));
            $scene = tut52_scene($topic, $family);
            $exId = 'L' . $lessonNo . ':' . $sid . ':' . $scene;
            $title = trim((string)preg_replace('/^\d+\s*·\s*/u', '', (string)($step['title'] ?? tr('Krok'))));
            $kind = (string)($step['kind'] ?? 'manual');
        ?>
        <section class="t52-slide" data-t52-slide data-title="<?= e($title) ?>" data-step="<?= e($sid) ?>" data-kind="<?= e($kind) ?>" data-done="<?= $stepDone ? '1' : '0' ?>" id="krok-<?= $i + 1 ?>" hidden>
          <header class="t52-step-head">
            <span class="t52-kicker"><?= e(tr('Krok {n} z {total} · {time} · +{xp} XP', ['n' => $i + 1, 'total' => count($lesson['steps']), 'time' => (string)($step['time'] ?? ''), 'xp' => (int)($step['xp'] ?? 20)])) ?></span>
            <h2<?= edu_content_lang_attr() ?>><?= e($title) ?></h2>
          </header>
          <div class="t52-split">
            <?php tut52_render_scene($scene, $topic !== '' ? tut52_topic_title($modules, $classId, $topic) : ''); ?>
            <?php tut52_render_exercise($scene, $exId, $scores); ?>
          </div>

          <?php if (!empty($step['knowledge'])): ?>
          <section class="t52-card t52-need">
            <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Nejdřív vysvětlení')) ?></span><strong><?= e(tr('Tento krok vyžaduje dokončenou interaktivní lekci')) ?></strong></header>
            <ul class="t52-topic-list"<?= edu_content_lang_attr() ?>>
              <?php foreach ((array)$step['knowledge'] as $t): $t = (string)$t; $ok = isset($module['knowledgebase'][$t]) && learning_kb_complete($classId, $t, isset($simulationMap[$t])); ?>
                <li><a href="<?= e(module_url('kb_lesson', ['topic' => $t])) ?>"><span class="<?= $ok ? 'ok' : '' ?>"><?= $ok ? '✓' : '○' ?></span><?= e(tut52_topic_title($modules, $classId, $t)) ?><b><?= $ok ? e(tr('hotovo')) : e(tr('otevřít')) ?></b></a></li>
              <?php endforeach; ?>
            </ul>
          </section>
          <?php endif; ?>

          <section class="t52-card t52-work" data-t52-work>
            <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('V hodině')) ?></span><strong><?= $kind === 'quiz' ? e(tr('Kontrolní otázka')) : e(tr('Splň body kroku')) ?></strong></header>
            <?php if (!empty($step['tasks'])): ?>
              <div class="t52-checklist"<?= edu_content_lang_attr() ?>>
                <?php foreach ((array)$step['tasks'] as $task): ?><label><input type="checkbox" data-t52-task<?= $stepDone ? ' checked disabled' : '' ?>><span><?= e((string)$task) ?></span></label><?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if ($kind === 'quiz' && is_array($step['options'] ?? null)): ?>
              <p class="t52-question"<?= edu_content_lang_attr() ?>><?= e((string)($step['question'] ?? '')) ?></p>
              <div class="t52-options" role="radiogroup"<?= edu_content_lang_attr() ?>>
                <?php foreach (shuffled_keys((array)$step['options']) as $d => $key): ?>
                  <button type="button" class="t52-option" data-t52-answer="<?= (int)$key ?>" <?= $stepDone ? 'disabled' : '' ?>><b><?= chr(65 + (int)$d) ?></b><span><?= e((string)$step['options'][$key]) ?></span></button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="t52-feedback" data-t52-feedback hidden></div>
            <footer class="t52-step-foot">
              <?php if ($stepDone): ?><span class="t52-done-badge">✓ <?= e(tr('Krok je hotový')) ?></span>
              <?php elseif ($locked): ?><span class="t52-muted"><?= e(tr('Dokončení se odemkne s lekcí.')) ?></span>
              <?php else: ?><button type="button" class="btn primary" data-t52-complete disabled><?= e(tr('Dokončit krok')) ?></button><?php endif; ?>
            </footer>
          </section>
        </section>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Závěr -->
      <section class="t52-slide" data-t52-slide data-title="<?= e(tr('Shrnutí')) ?>" id="krok-<?= ($lesson['kind'] === 'primary' ? count(tut52_primary_flow($classId, $module, $completedTest, $simulationMap)) : count($lesson['steps'])) + 1 ?>" hidden>
        <div class="t52-finish">
          <div class="t52-finish-mark" data-t52-finish><?= $lesson['done'] ? '✓' : (int)$lesson['done_steps'] . '/' . (int)$lesson['total_steps'] ?></div>
          <h2><?= $lesson['done'] ? e(tr('Lekce je hotová')) : e(tr('Shrnutí lekce')) ?></h2>
          <p><?= $lesson['done'] ? e(tr('Skvělé. Můžeš pokračovat další lekcí nebo si ukázky zopakovat.')) : e(tr('Vrať se ke krokům, které ještě nejsou hotové – najdeš je v tečkách nahoře.')) ?></p>
          <?php $lp = tut52_lesson_points($scores, $lessonNo); ?>
          <div class="t52-finish-stats"><div><b><?= (int)$lesson['done_steps'] ?> / <?= (int)$lesson['total_steps'] ?></b><span><?= e(tr('kroků')) ?></span></div><div><b data-t52-lesson-points><?= $lp['points'] ?></b><span><?= e(tr('bodů z úkolů')) ?></span></div></div>
          <?php if (!empty($lesson['worksheet'])): ?><section class="t52-card"><header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Pracovní list')) ?></span><strong><?= e(tr('Co odevzdat nebo si zapsat')) ?></strong></header><ul class="t52-plain"<?= edu_content_lang_attr() ?>><?php foreach ((array)$lesson['worksheet'] as $w): ?><li><?= e((string)$w) ?></li><?php endforeach; ?></ul></section><?php endif; ?>
          <div class="t52-support">
            <a href="<?= e(module_url('lesson_kit', ['lesson' => $lessonNo])) ?>">Lesson Kit</a>
            <a href="<?= e(module_url('lesson_slides', ['lesson' => $lessonNo])) ?>"><?= e(tr('Prezentace')) ?></a>
            <a href="<?= e(module_url('visual_lab', ['lesson' => $lessonNo])) ?>">Visual Lab</a>
            <?php if ($family === 'networks'): ?><a href="<?= e(module_url('hands_on', ['lesson' => $lessonNo])) ?>">Linux Lab</a><?php endif; ?>
          </div>
          <?php if ($nextLesson): ?><a class="btn primary" href="<?= e(tut52_tutorial_url($lessonNo + 1)) ?>"><?= tr_html('Další lekce: {title}', ['title' => edu_cs((string)$nextLesson['title'])]) ?></a><?php else: ?><a class="btn primary" href="?view=course"><?= e(tr('Zpět na kurz')) ?></a><?php endif; ?>
        </div>
      </section>

      <nav class="u51-wizard-nav t52-tut-nav">
        <button type="button" class="btn secondary" data-t52-prev><?= e(tr('Zpět')) ?></button>
        <span class="u51-step-title" data-t52-counter></span>
        <button type="button" class="btn primary" data-t52-next><?= e(tr('Pokračovat')) ?></button>
      </nav>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

// ---------------------------------------------------------------------------
// Kalendář naplánovaných hodin
// ---------------------------------------------------------------------------

function tut52_render_calendar(string $classId, array $module, array $modules, array $lessons, array $schoolYear, string $flash): void
{
    $schedule = adaptive_class_schedule($schoolYear, $classId);
    $time = trim((string)($schedule['start'] ?? '')) !== '' ? $schedule['start'] . '–' . $schedule['end'] : tr('2 × 45 minut');
    $periods = array_map('intval', (array)($schedule['periods'] ?? []));
    $rows = array_values(array_filter(adaptive_school_year_rows($schoolYear, $classId), 'is_array'));
    $today = date('Y-m-d');
    $studentKey = adaptive_student_key($classId);

    $byDate = [];
    $next = null;
    foreach ($rows as $r) {
        $d = (string)($r['date'] ?? '');
        if ($d === '') continue;
        $byDate[$d] = $r;
        if ($next === null && ($r['status'] ?? '') === 'teaching' && $d >= $today) $next = $r;
    }
    ksort($byDate);
    $months = [];
    foreach (array_keys($byDate) as $d) $months[date('Y-m', strtotime((string)$d))] = true;
    $months = array_keys($months);
    sort($months);
    $activeMonth = $next ? date('Y-m', strtotime((string)$next['date'])) : (string)($months[0] ?? date('Y-m'));

    render_header(tr('Kalendář'), $module);
    tut52_subnav('calendar');
    ?>
    <div class="t52 t52-calendar">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="t52-page-head">
        <div><span class="t52-kicker"><?= tr_html('Školní rok {year} · {label}', ['year' => e((string)($schoolYear['meta']['school_year'] ?? '')), 'label' => edu_cs((string)($schedule['label'] ?? $module['name']))]) ?></span><h1><?= tr_html('Kalendář třídy {name}', ['name' => edu_cs((string)$module['name'])]) ?></h1><p><?= e(tr('Každou středu {time}', ['time' => (string)$time])) ?><?= $periods ? ' · ' . e(tr('{period}. hodina', ['period' => implode('.–', $periods)])) : '' ?><?= !empty($schedule['room']) ? ' · ' . tr_html('učebna {room}', ['room' => edu_cs((string)$schedule['room'])]) : '' ?></p></div>
      </header>

      <?php if ($next): $nNo = (int)($next['lesson_number'] ?? 0); $nl = $lessons[$nNo] ?? null; $days = (int)floor((strtotime((string)$next['date']) - strtotime($today)) / 86400); ?>
      <section class="t52-hero-lesson t52-next-class" data-t52-reveal>
        <div class="t52-date-big"><b><?= e(date('j', strtotime((string)$next['date']))) ?></b><span><?= e(tut52_month_name((int)date('n', strtotime((string)$next['date'])))) ?></span></div>
        <div class="t52-hero-copy">
          <span class="t52-kicker"><?= e(tr('Nejbližší hodina · {when} · {time}', ['when' => $days === 0 ? tr('dnes') : ($days === 1 ? tr('zítra') : tr('za {n} dní', ['n' => $days])), 'time' => (string)$time])) ?></span>
          <h2<?= edu_content_lang_attr() ?>><?= e($nl ? tr('Lekce {n} · {title}', ['n' => $nNo, 'title' => $nl['title']]) : (string)($next['title'] ?? tr('Výuka'))) ?></h2>
          <?php if ($nl): ?><p<?= edu_content_lang_attr() ?>><?= e($nl['goal']) ?></p><div class="t52-chips"<?= edu_content_lang_attr() ?>><?php foreach (array_slice($nl['topics'], 0, 4) as $t): ?><span><?= e(tut52_topic_title($modules, $classId, (string)$t)) ?></span><?php endforeach; ?></div><?php else: ?><p<?= edu_content_lang_attr() ?>><?= e((string)($next['description'] ?? '')) ?></p><?php endif; ?>
        </div>
        <?php if ($nl): ?><a class="btn primary" href="<?= e(tut52_tutorial_url($nNo)) ?>"><?= e(tr('Připravit se')) ?></a><?php endif; ?>
      </section>
      <?php endif; ?>

      <div class="t52-month-tabs" role="tablist" data-t52-tabs>
        <?php foreach ($months as $ym): $m = (int)substr((string)$ym, 5, 2);
            $teach = 0;
            foreach ($byDate as $d => $r) if (str_starts_with((string)$d, (string)$ym) && ($r['status'] ?? '') === 'teaching') $teach++;
        ?>
          <button type="button" role="tab" data-t52-tab="<?= e((string)$ym) ?>" aria-selected="<?= $ym === $activeMonth ? 'true' : 'false' ?>"><?= e(tut52_month_name($m)) ?><small><?= $teach ?></small></button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($months as $ym):
          $first = strtotime((string)$ym . '-01');
          $daysInMonth = (int)date('t', $first);
          $lead = (int)date('N', $first) - 1;
      ?>
      <div class="cal54-month" data-t52-panel="<?= e((string)$ym) ?>" <?= $ym === $activeMonth ? '' : 'hidden' ?>>
        <section class="cal54" aria-label="<?= e(tr('Kalendář {month}', ['month' => tut52_month_name((int)substr((string)$ym, 5, 2))])) ?>">
          <div class="cal54-title"><?= e(tut52_month_name((int)substr((string)$ym, 5, 2))) ?> <?= e(substr((string)$ym, 0, 4)) ?></div>
          <div class="cal54-head"><?php foreach ([1, 2, 3, 4, 5, 6, 7] as $isoDow): ?><span><?= e(edu_weekday_short($isoDow)) ?></span><?php endforeach; ?></div>
          <div class="cal54-grid">
            <?php for ($i = 0; $i < $lead; $i++): ?><div class="cal54-cell empty"></div><?php endfor; ?>
            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $date = (string)$ym . '-' . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
                $row = $byDate[$date] ?? null;
                $weekend = in_array((int)date('N', strtotime($date)), [6, 7], true);
                $teaching = $row && ($row['status'] ?? '') === 'teaching';
                $state = 'plain';
                if ($weekend) $state = 'weekend';
                if ($row) $state = !$teaching ? 'off' : ($date < $today ? 'past' : ($date === $today ? 'today' : 'planned'));
                $no = $row ? (int)($row['lesson_number'] ?? 0) : 0;
                $l = $no > 0 ? ($lessons[$no] ?? null) : null;
                $title = $l ? tr('Lekce {n} · {title}', ['n' => $no, 'title' => $l['title']]) : (string)($row['title'] ?? '');
                $titleIsContent = $title !== '';
                $isToday = $date === $today;
            ?>
              <?php if ($row): ?>
                <a class="cal54-cell <?= $state ?><?= $isToday ? ' is-today' : '' ?>" href="#den-<?= e($date) ?>" title="<?= e($title !== '' ? $title : tr('Bez výuky')) ?>"<?= $titleIsContent ? edu_content_lang_attr() : '' ?>>
                  <span class="cal54-day"><?= $day ?></span>
                  <?php if ($teaching): ?>
                    <span class="cal54-time"><?= e($time) ?></span>
                    <span class="cal54-title-cell"<?= $l ? '' : ($titleIsContent ? edu_content_lang_attr() : '') ?>><?= e($l ? tr('Lekce {n}', ['n' => $no]) : ($title !== '' ? $title : tr('Výuka'))) ?></span>
                    <?php if ($l): ?><span class="cal54-sub"<?= edu_content_lang_attr() ?>><?= e((string)$l['title']) ?></span><?php endif; ?>
                  <?php else: ?>
                    <span class="cal54-off"<?= $titleIsContent ? edu_content_lang_attr() : '' ?>><?= e($title !== '' ? $title : tr('Bez výuky')) ?></span>
                  <?php endif; ?>
                </a>
              <?php else: ?>
                <div class="cal54-cell <?= $state ?><?= $isToday ? ' is-today' : '' ?>"><span class="cal54-day"><?= $day ?></span></div>
              <?php endif; ?>
            <?php endfor; ?>
          </div>
          <div class="cal54-legend"><span><i class="cal54-dot planned"></i><?= e(tr('naplánovaná hodina')) ?></span><span><i class="cal54-dot past"></i><?= e(tr('proběhlo')) ?></span><span><i class="cal54-dot today"></i><?= e(tr('dnes')) ?></span><span><i class="cal54-dot off"></i><?= e(tr('bez výuky')) ?></span></div>
        </section>

        <ol class="t52-agenda">
          <?php foreach ($byDate as $d => $r):
              if (!str_starts_with((string)$d, (string)$ym)) continue;
              $d = (string)$d;
              $teaching = ($r['status'] ?? '') === 'teaching'; $no = (int)($r['lesson_number'] ?? 0); $l = $lessons[$no] ?? null;
              $state = !$teaching ? 'off' : ($d < $today ? 'past' : ($d === $today ? 'today' : ((string)($next['date'] ?? '') === $d ? 'next' : 'planned')));
              $label = ['off' => tr('Bez výuky'), 'past' => tr('Proběhlo'), 'today' => tr('Dnes'), 'next' => tr('Příští hodina'), 'planned' => tr('Naplánováno')][$state];
              $title = $l ? tr('Lekce {n} · {title}', ['n' => $no, 'title' => $l['title']]) : (string)($r['title'] ?? ($teaching ? tr('Výuka') : tr('Volno')));
              $titleIsContent = $l !== null || (string)($r['title'] ?? '') !== '';
          ?>
          <li class="t52-day <?= $state ?>" id="den-<?= e($d) ?>" data-t52-reveal>
            <div class="t52-day-date"><b><?= e(date('j.', strtotime($d))) ?></b><span><?= e(tut52_cz_date($d, true) !== '' ? explode(' ', tut52_cz_date($d))[0] : '') ?></span></div>
            <div class="t52-day-main">
              <span class="t52-status <?= $state ?>"><?= e($label) ?></span><?php if ($teaching): ?><span class="t52-day-time"><?= e($time) ?></span><?php endif; ?>
              <strong<?= $titleIsContent ? edu_content_lang_attr() : '' ?>><?= e($title) ?></strong>
              <?php if ($l): ?>
                <div class="t52-chips small"<?= edu_content_lang_attr() ?>><?php foreach (array_slice($l['topics'], 0, 3) as $t): ?><span><?= e(tut52_topic_title($modules, $classId, (string)$t)) ?></span><?php endforeach; ?></div>
              <?php elseif (!empty($r['description']) || !empty($r['exception'])): ?><small<?= edu_content_lang_attr() ?>><?= e((string)($r['description'] ?? $r['exception'])) ?></small><?php endif; ?>
              <?php if (!empty($r['auto_shifted'])): ?><small class="t52-muted"><?= e(tr('Termín byl automaticky posunut.')) ?></small><?php endif; ?>
            </div>
            <div class="t52-day-actions">
              <?php if ($l): ?><a class="btn <?= in_array($state, ['next', 'today'], true) ? 'primary' : 'secondary' ?>" href="<?= e(tut52_tutorial_url($no)) ?>"><?= $state === 'past' ? e(tr('Zopakovat')) : e(tr('Připravit se')) ?></a><?php endif; ?>
              <?php if ($l && $state === 'past' && $studentKey !== ''): $absence = adaptive_absence_get($classId, $studentKey, $d); ?>
                <?php if ($absence): ?><a class="t52-link" href="<?= e(module_url('recovery', ['date' => $d])) ?>"><?= ((string)($absence['status'] ?? '') === 'complete') ? '✓ ' . e(tr('Doplněno')) : e(tr('Doplnit zameškané')) ?></a>
                <?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="adaptive_absence_mark"><input type="hidden" name="date" value="<?= e($d) ?>"><input type="hidden" name="lesson_number" value="<?= $no ?>"><input type="hidden" name="lesson_title" value="<?= e($title) ?>"><button class="t52-link" type="submit"><?= e(tr('Chyběl/a jsem')) ?></button></form><?php endif; ?>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>
      <?php endforeach; ?>

      <details class="t52-card t52-details"><summary><?= e(tr('Co když hodina odpadne nebo chybím?')) ?></summary><p><?= e(tr('Když odpadne středa, plán se automaticky posune a nepřeskočí se žádná lekce. Když chybíš, klikni u proběhlé hodiny na „Chyběl/a jsem“ – dostaneš krátký plán doplnění a tutoriál lekce si projdeš vlastním tempem.')) ?></p></details>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

// ---------------------------------------------------------------------------
// Témata
// ---------------------------------------------------------------------------

function tut52_render_topics(string $classId, array $module, array $modules, array $lessons, array $simulations, string $flash): void
{
    $family = tut52_family($classId, $module);
    $usedIn = [];
    foreach ($lessons as $l) foreach ($l['topics'] as $t) $usedIn[(string)$t][] = (int)$l['number'];
    $classes = allowed_subject_class_ids($classId, $modules);
    render_header(tr('Témata'), $module);
    tut52_subnav('topics');
    ?>
    <div class="t52 t52-topics">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="t52-page-head">
        <div><span class="t52-kicker"<?= edu_content_lang_attr() ?>><?= e((string)$module['subject']) ?></span><h1><?= e(tr('Témata')) ?></h1><p><?= e(tr('Každé téma má animovanou ukázku a interaktivní lekci: podívej se → vyzkoušej → postup → ověř.')) ?></p></div>
        <label class="t52-search"><span class="sr-only"><?= e(tr('Hledat téma')) ?></span><input type="search" placeholder="<?= e(tr('Hledat téma, pojem nebo lekci…')) ?>" data-t52-filter></label>
      </header>
      <?php foreach ($classes as $cid): $kb = (array)($modules[$cid]['knowledgebase'] ?? []); $simMap = (array)($simulations[$cid] ?? []); $own = $cid === $classId; ?>
      <section class="t52-topic-group">
        <h2 class="t52-path-month"><?= $own ? e(tr('Moje třída · {name}', ['name' => (string)$modules[$cid]['name']])) : e(tr('Opakování z {name}', ['name' => (string)$modules[$cid]['name']])) ?> <small><?= e(tr('{n} témat', ['n' => count($kb)])) ?></small></h2>
        <div class="t52-topic-grid">
          <?php foreach ($kb as $key => $article): if (!is_array($article)) continue; $key = (string)$key; $scene = tut52_scene($key, $family);
              $done = $own && learning_kb_complete($classId, $key, isset($simMap[$key])); $params = ['topic' => $key]; if (!$own) $params['kb_class'] = $cid; ?>
            <a class="t52-topic <?= $done ? 'done' : '' ?>" href="<?= e(module_url('kb_lesson', $params)) ?>" data-t52-item data-t52-reveal>
              <span class="t52-mini-scene" data-t52-scene="<?= e($scene) ?>" data-t52-hover><span class="t52-stage" data-t52-stage></span></span>
              <strong><?= e((string)($article['title'] ?? $key)) ?></strong>
              <small><?= e((string)($article['summary'] ?? '')) ?></small>
              <span class="t52-topic-meta"><?= $done ? '<b class="ok">✓ ' . e(tr('hotovo')) . '</b>' : '<b>' . e(tr('Otevřít lekci')) . '</b>' ?><?php if ($own && !empty($usedIn[$key])): ?><em><?= e(tr('Lekce {list}', ['list' => implode(', ', array_slice(array_unique($usedIn[$key]), 0, 3))])) ?></em><?php endif; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endforeach; ?>
      <p class="t52-empty" data-t52-empty hidden><?= e(tr('Nic nenalezeno. Zkus jiné slovo.')) ?></p>
    </div>
    <?php
    tut52_assets();
    render_footer();
}

// ---------------------------------------------------------------------------
// Programy, zkratky a pojmy
// ---------------------------------------------------------------------------

function tut52_render_tools(string $classId, array $module, array $lessons, string $flash): void
{
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $glossary = tut52_glossary($family);
    $usedIn = [];
    foreach ($lessons as $l) foreach ($l['tools'] as $tk) $usedIn[$tk][] = (int)$l['number'];
    render_header(tr('Programy a zkratky'), $module);
    tut52_subnav('tools');
    ?>
    <div class="t52 t52-tools">
      <?php if ($flash !== ''): ?><div class="u51-notice"><?= e($flash) ?></div><?php endif; ?>
      <header class="t52-page-head">
        <div><span class="t52-kicker"<?= edu_content_lang_attr() ?>><?= e((string)$module['subject']) ?></span><h1><?= e(tr('Programy a zkratky')) ?></h1><p><?= e(tr('Co v kurzu používáme, k čemu to je, nejdůležitější klávesové zkratky, příkazy a pojmy.')) ?></p></div>
        <label class="t52-search"><span class="sr-only"><?= e(tr('Hledat')) ?></span><input type="search" placeholder="<?= e(tr('Hledat program, zkratku nebo pojem…')) ?>" data-t52-filter></label>
      </header>

      <section class="t52-card t52-trainer" data-t52-trainer="<?= tut52_json(array_values(array_merge(...array_map(static fn($t) => array_map(static fn($s) => ['keys' => $s[0], 'desc' => $s[1], 'tool' => $t['name']], $t['shortcuts']), array_values($tools))))) ?>">
        <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Trénink zkratek · 5 otázek')) ?></span><strong><?= e(tr('Vyber správnou zkratku')) ?></strong></header>
        <div class="t52-trainer-body" data-t52-trainer-body><button type="button" class="btn primary" data-t52-trainer-start><?= e(tr('Začít trénink')) ?></button></div>
      </section>

      <div class="t52-tool-grid">
        <?php foreach ($tools as $key => $tool): ?>
        <article class="t52-card t52-tool" id="<?= e($key) ?>" data-t52-item data-t52-reveal>
          <header class="t52-card-head"><span class="t52-kicker"><?= e($tool['kind']) ?></span><strong><?= e($tool['name']) ?></strong></header>
          <p><?= e($tool['use']) ?></p>
          <p class="t52-muted"><?= e($tool['where']) ?><?php if (!empty($usedIn[$key])): ?> · <?= e(tr('lekce {list}', ['list' => implode(', ', array_slice($usedIn[$key], 0, 8))])) ?><?= count($usedIn[$key]) > 8 ? '…' : '' ?><?php endif; ?></p>
          <?php if ($tool['shortcuts']): ?>
            <h4><?= e(tr('Klávesové zkratky')) ?></h4>
            <dl class="t52-keys"><?php foreach ($tool['shortcuts'] as [$k, $d]): ?><div><dt><?php foreach (explode(' / ', $k) as $ki => $combo): ?><?= $ki ? ' / ' : '' ?><?php foreach (explode('+', $combo) as $pi => $part): ?><?= $pi ? '+' : '' ?><kbd><?= e($part) ?></kbd><?php endforeach; ?><?php endforeach; ?></dt><dd><?= e($d) ?></dd></div><?php endforeach; ?></dl>
          <?php endif; ?>
          <?php if (!empty($tool['commands'])): ?>
            <h4><?= $key === 'wireshark' ? e(tr('Filtry')) : e(tr('Příkazy')) ?></h4>
            <ul class="t52-commands"><?php foreach ($tool['commands'] as [$c, $d]): ?><li><code><?= e($c) ?></code><span><?= e($d) ?></span><button type="button" data-t52-copy="<?= e($c) ?>" aria-label="<?= e(tr('Kopírovat příkaz')) ?>"><?= e(tr('Kopírovat')) ?></button></li><?php endforeach; ?></ul>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>

      <section class="t52-card">
        <header class="t52-card-head"><span class="t52-kicker"><?= e(tr('Pojmy a zkratky')) ?></span><strong><?= e(tr('Slovníček')) ?></strong></header>
        <dl class="t52-glossary"><?php foreach ($glossary as [$abbr, $meaning]): ?><div data-t52-item><dt><?= e($abbr) ?></dt><dd><?= e($meaning) ?></dd></div><?php endforeach; ?></dl>
      </section>
      <p class="t52-empty" data-t52-empty hidden><?= e(tr('Nic nenalezeno. Zkus jiné slovo.')) ?></p>
    </div>
    <?php
    tut52_assets();
    render_footer();
}
