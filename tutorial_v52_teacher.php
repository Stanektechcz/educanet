<?php

declare(strict_types=1);

/** v52 · Tutorial Mode v učitelském Režimu hodiny: projekční ukázky a interaktivní úkoly lekce. */

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/** Kroky lekce s přiřazenou scénou a tématem (pro projekci i pro přehled učitele). */
function tut52_teacher_lesson_steps(string $classId, array $module, array $lesson): array
{
    $family = tut52_family($classId, $module);
    $topics = array_values(array_filter(array_map('strval', (array)($lesson['knowledge'] ?? []))));
    $steps = array_values(array_filter((array)($lesson['steps'] ?? []), 'is_array'));
    $out = [];
    foreach ($steps as $i => $step) {
        $topic = (string)(($step['knowledge'][0] ?? null) ?: ($topics ? $topics[$i % count($topics)] : ''));
        $scene = tut52_scene($topic, $family);
        $out[] = [
            'index' => $i + 1,
            'title' => trim((string)preg_replace('/^\d+\s*·\s*/u', '', (string)($step['title'] ?? ('Krok ' . ($i + 1))))),
            'time' => (string)($step['time'] ?? ''),
            'kind' => (string)($step['kind'] ?? 'manual'),
            'topic' => $topic,
            'scene' => $scene,
            'tasks' => array_values(array_filter(array_map('strval', (array)($step['tasks'] ?? [])))),
            'question' => (string)($step['question'] ?? ''),
            'options' => (array)($step['options'] ?? []),
            'correct' => $step['correct'] ?? null,
            'explanation' => (string)($step['explanation'] ?? ''),
        ];
    }
    if (!$out && $topics) {
        foreach ($topics as $i => $topic) {
            $out[] = ['index' => $i + 1, 'title' => tut52_topic_title($GLOBALS['modules'], $classId, $topic), 'time' => '', 'kind' => 'knowledge', 'topic' => $topic,
                'scene' => tut52_scene($topic, $family), 'tasks' => [], 'question' => '', 'options' => [], 'correct' => null, 'explanation' => ''];
        }
    }
    return $out;
}

/** Projekční panel: ukázky lekce, interaktivní úkoly s řešením a programy se zkratkami. */
function tut52_render_teacher_lesson(string $classId, array $module, int $number, array $lesson): void
{
    $steps = tut52_teacher_lesson_steps($classId, $module, $lesson);
    if (!$steps) return;
    $family = tut52_family($classId, $module);
    $tools = tut52_tools($family);
    $lessonTools = tut52_lesson_tools($family, [
        'title' => (string)($lesson['title'] ?? ''), 'goal' => (string)($lesson['goal'] ?? ''),
        'topics' => (array)($lesson['knowledge'] ?? []), 'steps' => (array)($lesson['steps'] ?? []),
    ]);
    $modules = $GLOBALS['modules'];
    ?>
    <section class="teacher-panel t52 t52-teach">
      <div class="teacher-panel-head">
        <div><span>Tutorial Mode v52 · stejné ukázky vidí student</span><h2>Animované ukázky a interaktivní úkoly</h2></div>
        <small><?= count($steps) ?> ukázek k lekci</small>
      </div>

      <div class="t52-deck" data-t52-deck>
        <div class="t52-deck-tabs" role="tablist" aria-label="Ukázky lekce">
          <?php foreach ($steps as $i => $s): ?>
            <button type="button" role="tab" data-t52-deck-go="<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"><b><?= (int)$s['index'] ?></b><span><?= e($s['title']) ?></span></button>
          <?php endforeach; ?>
          <button type="button" class="t52-deck-full" data-t52-deck-full>Celá obrazovka</button>
        </div>

        <?php foreach ($steps as $i => $s): $ex = tut52_exercise($s['scene']); ?>
        <div class="t52-deck-item" data-t52-deck-item <?= $i === 0 ? '' : 'hidden' ?>>
          <div class="t52-split">
            <?php tut52_render_scene($s['scene'], $s['topic'] !== '' ? tut52_topic_title($modules, $classId, $s['topic']) : ''); ?>
            <div class="t52-teach-side">
              <?php tut52_render_exercise($s['scene'], 'teach:' . $classId . ':L' . $number . ':' . $s['scene'], [], true); ?>
              <details class="t52-card t52-solution">
                <summary>Řešení a co s tím ve třídě</summary>
                <p class="t52-muted"><?= e(tut52_exercise_solution($ex)) ?></p>
                <?php if ($s['tasks']): ?><ul class="t52-plain"><?php foreach ($s['tasks'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul><?php endif; ?>
                <?php if ($s['question'] !== '' && $s['correct'] !== null): ?>
                  <p><strong>Kontrolní otázka:</strong> <?= e($s['question']) ?></p>
                  <p><strong>Správně:</strong> <?= e((string)(($s['options'][$s['correct']] ?? ''))) ?></p>
                  <?php if ($s['explanation'] !== ''): ?><p class="t52-muted"><?= e($s['explanation']) ?></p><?php endif; ?>
                <?php endif; ?>
              </details>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="t52-teach-tools">
        <?php foreach ($lessonTools as $tk): $tool = $tools[$tk] ?? null; if (!$tool) continue; ?>
          <article>
            <strong><?= e($tool['name']) ?></strong><small><?= e($tool['use']) ?></small>
            <?php $pairs = array_slice($tool['shortcuts'] ?: array_map(static fn($c) => [$c[0], $c[1]], (array)($tool['commands'] ?? [])), 0, 4); ?>
            <span class="t52-kbd-row"><?php foreach ($pairs as [$k, $d]): ?><span><kbd><?= e($k) ?></kbd> <?= e($d) ?></span><?php endforeach; ?></span>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
    tut52_assets();
}
