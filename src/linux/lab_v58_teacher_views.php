<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – učitelská záložka `?tab=labdata` (podsekce Chyby/Dohled/Přehrávání/Export).
 * Čte jen přes `lab_v58_teacher.php` a `lab_v58_learning.php`. Vlastní CSS/JS: assets/lab-teacher-v58.*.
 */

const LAB58T_SECTIONS = ['chyby' => 'Chyby', 'dohled' => 'Dohled', 'prehravani' => 'Přehrávání', 'export' => 'Export'];
const LAB58T_PERIODS = [7 => '7 dní', 30 => '30 dní', 90 => '90 dní'];

function lab58t_section_url(string $classId, string $section): string
{
    return '?tab=labdata&amp;podsekce=' . e($section) . '&amp;class=' . e(rawurlencode($classId));
}

/** Hlavní vstup záložky: nadpis, navigace mezi podsekcemi, obsah aktivní podsekce. */
function lab58t_render_teacher_tab(string $classId, string $csrf): void
{
    $section = (string)($_GET['podsekce'] ?? 'chyby');
    if (!isset(LAB58T_SECTIONS[$section])) $section = 'chyby';
    ?>
    <section class="teacher-page-head">
      <div><div class="eyebrow">Linux Lab</div><h1>Analytika Labu</h1>
        <p>Kde třída dělá chyby, kdo potřebuje pomoc, přehrání relace žáka a export do tabulky.</p></div>
    </section>
    <nav class="lab58t-tabs" aria-label="Sekce analytiky Labu">
      <?php foreach (LAB58T_SECTIONS as $key => $label): ?>
        <a class="<?= $section === $key ? 'is-active' : '' ?>" href="<?= lab58t_section_url($classId, $key) ?>"<?= $section === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php
    if ($section === 'dohled') { lab58t_render_supervision_section($classId); return; }
    if ($section === 'prehravani') { lab58t_render_replay_section($classId); return; }
    if ($section === 'export') { lab58t_render_export_section($classId, $csrf); return; }
    lab58t_render_heatmap_section($classId);
}

// ---------------------------------------------------------------------------
// Chyby (EDU-02)
// ---------------------------------------------------------------------------

function lab58t_render_heatmap_section(string $classId): void
{
    $days = (int)($_GET['obdobi'] ?? 30);
    if (!isset(LAB58T_PERIODS[$days])) $days = 30;
    $data = function_exists('lab58_heatmap') ? lab58_heatmap($classId, time() - $days * 86400) : ['cells' => [], 'top' => [], 'students_active' => 0, 'total_events' => 0];
    ?>
    <section class="lab58t-panel">
      <div class="lab58t-panel-head">
        <h2>Co třídě dělá potíže</h2>
        <form method="get" class="lab58t-period">
          <input type="hidden" name="tab" value="labdata"><input type="hidden" name="podsekce" value="chyby"><input type="hidden" name="class" value="<?= e($classId) ?>">
          <label>Období<select name="obdobi" onchange="this.form.submit()">
            <?php foreach (LAB58T_PERIODS as $val => $label): ?><option value="<?= $val ?>" <?= $val === $days ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
          </select></label>
          <noscript><button class="btn secondary" type="submit">Zobrazit</button></noscript>
        </form>
      </div>
      <p class="lab58t-meta"><?= (int)$data['students_active'] ?> aktivních žáků · <?= (int)$data['total_events'] ?> příkazů za období</p>
      <?php if (!$data['cells']): ?>
        <p class="lab58t-empty">Zatím není dost dat – kvůli soukromí se zobrazí jen buňka, kde stejnou chybu udělali aspoň 3 různí žáci.</p>
      <?php else: ?>
        <?php lab58t_render_heatmap_top($data['top']); ?>
        <?php lab58t_render_heatmap_table($data['cells']); ?>
      <?php endif; ?>
    </section>
    <?php
}

function lab58t_render_heatmap_top(array $top): void
{
    ?>
    <h3>Co má smysl vysvětlit znovu (top 5)</h3>
    <ol class="lab58t-top5">
      <?php foreach ($top as $t): ?>
        <li><strong><?= e((string)$t['command']) ?></strong> · <?= e((string)$t['error_class']) ?> · <?= (int)$t['count'] ?>× u <?= (int)$t['students'] ?> žáků
          <p><?= e((string)$t['advice']) ?></p></li>
      <?php endforeach; ?>
    </ol>
    <?php
}

function lab58t_render_heatmap_table(array $cells): void
{
    ?>
    <table class="lab58t-table">
      <caption class="sr-only">Počet chyb podle příkazu a třídy chyby</caption>
      <thead><tr><th scope="col">Příkaz</th><th scope="col">Třída chyby</th><th scope="col">Počet</th><th scope="col">Žáků</th></tr></thead>
      <tbody>
        <?php foreach ($cells as $c): ?>
          <tr><td><?= e((string)$c['command']) ?></td><td><?= e((string)$c['error_class']) ?></td><td><?= (int)$c['count'] ?></td><td><?= (int)$c['students'] ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
}

// ---------------------------------------------------------------------------
// Dohled (TCH-03)
// ---------------------------------------------------------------------------

function lab58t_render_supervision_section(string $classId): void
{
    $data = function_exists('lab58t_supervision') ? lab58t_supervision($classId) : ['alerts' => []];
    ?>
    <?php /* GET parametr dohled_poll je zaregistrovaný v teacher_v58.php (teacher58_modules()['labdata']['get']) – volá lab58t_teacher_poll(). */ ?>
    <section class="lab58t-panel" data-lab58t-watch data-poll-url="?tab=labdata&amp;podsekce=dohled&amp;dohled_poll=1&amp;class=<?= e(rawurlencode($classId)) ?>">
      <div class="lab58t-panel-head"><h2>Kdo teď potřebuje pomoc</h2><p>Bez postupu přes 5 minut nebo hodně chyb za posledních 10 minut. Aktualizuje se každých 10 s.</p></div>
      <ul class="lab58t-alerts" data-lab58t-alerts aria-live="polite">
        <?php lab58t_render_alert_items($data['alerts']); ?>
      </ul>
    </section>
    <?php
}

function lab58t_render_alert_items(array $alerts): void
{
    if (!$alerts) {
        ?><li class="lab58t-empty" data-lab58t-empty>Nikdo teď nepotřebuje pomoc.</li><?php
        return;
    }
    foreach ($alerts as $a) {
        ?><li><strong><?= e((string)$a['student']) ?></strong><span class="lab58t-level"><?= e((string)$a['level']) ?></span><span class="lab58t-reason"><?= e((string)$a['reason']) ?></span></li><?php
    }
}

// ---------------------------------------------------------------------------
// Přehrávání (TCH-02)
// ---------------------------------------------------------------------------

function lab58t_replay_pick_level(array $levels, string $requested): string
{
    foreach ($levels as $l) if ((string)$l['id'] === $requested) return $requested;
    return (string)($levels[0]['id'] ?? '');
}

function lab58t_render_replay_section(string $classId): void
{
    $students = function_exists('project_students_for_class') ? project_students_for_class($classId) : [];
    $studentKey = (string)($_GET['student'] ?? '');
    if (!isset($students[$studentKey])) $studentKey = (string)array_key_first($students);
    $levels = $studentKey !== '' && function_exists('lab58t_student_levels') ? lab58t_student_levels($classId, $studentKey) : [];
    $levelId = lab58t_replay_pick_level($levels, (string)($_GET['uroven'] ?? ''));
    $replay = ($studentKey !== '' && $levelId !== '' && function_exists('lab58t_replay')) ? lab58t_replay($classId, $studentKey, $levelId) : ['events' => [], 'title' => ''];
    ?>
    <section class="lab58t-panel">
      <div class="lab58t-panel-head"><h2>Přehrávání relace</h2><p>Časová osa příkazů a výstupů; jen žáci vybrané třídy, posledních 30 dní.</p></div>
      <?php lab58t_render_replay_picker($classId, $students, $studentKey, $levels, $levelId); ?>
      <?php if ($levels === []): ?>
        <p class="lab58t-empty">Tenhle žák ještě nemá uložený žádný záznam.</p>
      <?php else: ?>
        <?php lab58t_render_replay_player($replay); ?>
      <?php endif; ?>
    </section>
    <?php
}

function lab58t_render_replay_picker(string $classId, array $students, string $studentKey, array $levels, string $levelId): void
{
    ?>
    <form method="get" class="lab58t-replay-pick">
      <input type="hidden" name="tab" value="labdata"><input type="hidden" name="podsekce" value="prehravani"><input type="hidden" name="class" value="<?= e($classId) ?>">
      <label>Žák<select name="student" onchange="this.form.submit()">
        <?php foreach ($students as $key => $info): ?><option value="<?= e((string)$key) ?>" <?= (string)$key === $studentKey ? 'selected' : '' ?>><?= e((string)($info['label'] ?? $key)) ?></option><?php endforeach; ?>
      </select></label>
      <label>Úroveň<select name="uroven" onchange="this.form.submit()">
        <?php foreach ($levels as $l): ?><option value="<?= e((string)$l['id']) ?>" <?= (string)$l['id'] === $levelId ? 'selected' : '' ?>><?= e((string)$l['title']) ?></option><?php endforeach; ?>
      </select></label>
      <noscript><button class="btn secondary" type="submit">Zobrazit</button></noscript>
    </form>
    <?php
}

function lab58t_render_replay_player(array $replay): void
{
    $count = count((array)$replay['events']);
    ?>
    <div class="lab58t-player" data-lab58t-player tabindex="0" aria-label="Přehrávač relace. Mezerník přehraje nebo pozastaví, šipky krokují.">
      <h3><?= e((string)$replay['title']) ?></h3>
      <?php /* v58 A11Y-A9: obrazovka bez aria-live (celý přepis by se při každém kroku četl znovu) –
               čtečkám oznamuje jen poslední řádku samostatný skrytý live region. */ ?>
      <div class="lab58t-player-screen" data-lab58t-screen></div>
      <div class="sr-only" data-lab58t-live aria-live="polite"></div>
      <div class="lab58t-player-bar">
        <button type="button" class="btn secondary" data-lab58t-play>Přehrát</button>
        <input type="range" min="0" max="<?= max(0, $count - 1) ?>" value="0" data-lab58t-seek aria-label="Poloha v relaci">
        <label class="sr-only" for="lab58t-speed">Rychlost přehrávání</label>
        <select id="lab58t-speed" data-lab58t-speed>
          <option value="2000">0.5×</option><option value="1000" selected>1×</option><option value="400">2×</option><option value="150">4×</option>
        </select>
        <span data-lab58t-pos>0 / <?= $count ?></span>
      </div>
    </div>
    <script type="application/json" data-lab58t-data><?= json_encode($replay['events'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <?php
}

// ---------------------------------------------------------------------------
// Export (TCH-04)
// ---------------------------------------------------------------------------

function lab58t_render_export_section(string $classId, string $csrf): void
{
    ?>
    <section class="lab58t-panel">
      <div class="lab58t-panel-head"><h2>Export výsledků</h2>
        <p>CSV (UTF-8, středník, bez e-mailů): žák, vyřešené úrovně, body, nápovědy, poslední aktivita, odznaky, série opakování.</p></div>
      <form method="post" class="lab58t-export-form">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="lab58t_export"><input type="hidden" name="class_id" value="<?= e($classId) ?>">
        <label>Typ hodnocení pro tento export
          <select name="mode"><option value="formativni">Formativní</option><option value="sumativni">Sumativní</option></select>
        </label>
        <button class="btn primary" type="submit">Stáhnout CSV</button>
      </form>
    </section>
    <?php
}
