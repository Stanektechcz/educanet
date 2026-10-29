<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Editor úrovní (TCH-01) – učitelské UI.
 *
 * Volá teacher_v58.php (teacher58_modules()['editor']['render']) jako
 * lab58e_render_teacher_tab(string $classId, string $csrf): void.
 */

if (is_file(__DIR__ . '/lab_v58_editor.php')) require_once __DIR__ . '/lab_v58_editor.php';

function lab58e_h(string $s): string
{
    return function_exists('e') ? e($s) : htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function lab58e_class_label(string $classId): string
{
    return function_exists('teacher_class_label') ? teacher_class_label($classId) : $classId;
}

function lab58e_status_label(string $status): string
{
    return $status === 'published' ? 'Zveřejněno' : 'Koncept';
}

function lab58e_type_label(string $type): string
{
    return ['code' => 'Kód', 'answer' => 'Odpověď', 'check' => 'Oprava', 'golf' => 'Golf'][$type] ?? $type;
}

/** Hlavní vstupní bod (viz teacher_v58.php). */
/** v59 · AUTHZ58-07: třídy editoru v rozsahu učitele (legacy = všechny čtyři). */
function lab58e_scope_class_ids(): array
{
    if (!function_exists('teacher59_can_class')) return LAB58E_CLASS_IDS;
    return array_values(array_filter(LAB58E_CLASS_IDS, static fn(string $c): bool => teacher59_can_class($c)));
}

function lab58e_render_teacher_tab(string $classId, string $csrf): void
{
    $scopeIds = lab58e_scope_class_ids();
    if (!in_array($classId, $scopeIds, true)) $classId = (string)($scopeIds[0] ?? LAB58E_CLASS_IDS[0]);
    $editId = (string)($_GET['level'] ?? '');
    $editRow = $editId !== '' ? lab58e_get($editId) : null;
    if ($editRow !== null && !lab58e_classes_in_scope((array)($editRow['classes'] ?? []))) $editRow = null; // v59: cizí úloha se neotevře
    // v59: seznam jen s úlohami, jejichž všechny třídy jsou v rozsahu (smíšené úlohy spravuje admin).
    $rows = array_values(array_filter(lab58e_for_class($classId), static fn(array $r): bool => lab58e_classes_in_scope((array)($r['classes'] ?? []))));
    usort($rows, static fn(array $a, array $b): int => strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? '')));
    ?>
    <link rel="stylesheet" href="<?= lab58e_h('assets/lab-editor-v58.css?v=' . (is_file(__DIR__ . '/assets/lab-editor-v58.css') ? filemtime(__DIR__ . '/assets/lab-editor-v58.css') : '58')) ?>">
    <div class="lab58e">
      <header class="t52-page-head lab58e-head">
        <div><span class="t52-kicker">Linux Lab</span><h1>Editor úrovní</h1>
          <p>Vlastní úlohy do Linux Labu. Před zveřejněním server sám vyzkouší referenční řešení na 5 různých zadáních – žák tak nikdy nedostane úlohu, kterou nejde vyřešit.</p>
        </div>
      </header>
      <nav class="lab58e-classes" aria-label="Třída">
        <?php foreach ($scopeIds as $cid): ?>
          <a class="<?= $cid === $classId ? 'active' : '' ?>" href="<?= lab58e_h('teacher.php?tab=editor&class=' . rawurlencode($cid)) ?>"<?= $cid === $classId ? ' aria-current="page"' : '' ?>><?= lab58e_h(lab58e_class_label($cid)) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="lab58e-grid">
        <section class="teacher-panel lab58e-list">
          <div class="teacher-panel-head"><div><span>Vlastní úlohy</span><h2><?= lab58e_h(lab58e_class_label($classId)) ?></h2></div>
            <a class="btn primary small" href="<?= lab58e_h('teacher.php?tab=editor&class=' . rawurlencode($classId)) ?>">+ Nová úloha</a>
          </div>
          <?php if ($rows === []): ?>
            <div class="teacher-empty">Zatím žádná vlastní úloha. Založ první napravo.</div>
          <?php endif; ?>
          <?php foreach ($rows as $row): lab58e_render_list_item($row, $classId, $csrf, $editId); endforeach; ?>
        </section>
        <section class="teacher-panel lab58e-form-panel">
          <?php lab58e_render_form($editRow, $classId, $csrf); ?>
          <?php if ($editRow !== null) lab58e_render_check_panel($editRow, $classId, $csrf); ?>
        </section>
      </div>
    </div>
    <script src="<?= lab58e_h('assets/lab-editor-v58.js?v=' . (is_file(__DIR__ . '/assets/lab-editor-v58.js') ? filemtime(__DIR__ . '/assets/lab-editor-v58.js') : '58')) ?>" defer></script>
    <?php
}

function lab58e_hidden(string $csrf, string $action, string $classId, string $id = ''): string
{
    return '<input type="hidden" name="csrf" value="' . lab58e_h($csrf) . '"><input type="hidden" name="action" value="' . lab58e_h($action) . '">'
        . '<input type="hidden" name="class_id" value="' . lab58e_h($classId) . '">' . ($id !== '' ? '<input type="hidden" name="id" value="' . lab58e_h($id) . '">' : '');
}

function lab58e_render_list_item(array $row, string $classId, string $csrf, string $editId): void
{
    $id = (string)$row['id'];
    $status = (string)($row['status'] ?? 'draft');
    $lastCheck = is_array($row['last_check'] ?? null) ? $row['last_check'] : null;
    ?>
    <article class="lab58e-item <?= $id === $editId ? 'active' : '' ?> status-<?= lab58e_h($status) ?>">
      <div class="lab58e-item-main">
        <a href="<?= lab58e_h('teacher.php?tab=editor&class=' . rawurlencode($classId) . '&level=' . rawurlencode($id)) ?>"><strong><?= lab58e_h((string)$row['title']) ?></strong></a>
        <span class="lab58e-badge <?= lab58e_h($status) ?>"><?= lab58e_h(lab58e_status_label($status)) ?></span>
        <small><?= lab58e_h(lab58e_type_label((string)$row['type'])) ?> · obtížnost <?= (int)$row['difficulty'] ?> · <?= (int)($row['minutes'] ?? 0) ?> min</small>
        <?php if ($lastCheck !== null): ?>
          <small class="lab58e-check-mini <?= !empty($lastCheck['ok']) && !empty($lastCheck['content_ok']) ? 'ok' : 'warn' ?>">test: <?= (int)$lastCheck['solved'] ?>/<?= (int)$lastCheck['total'] ?> řešitelné<?= empty($lastCheck['content_ok']) ? ', obsah k ověření' : '' ?></small>
        <?php endif; ?>
        <small class="lab58e-classes-of">třídy: <?php foreach ((array)$row['classes'] as $cid) echo lab58e_h(lab58e_class_label((string)$cid)) . ' '; ?></small>
      </div>
      <div class="lab58e-item-actions">
        <?php if ($status === 'published'): ?>
          <form method="post"><?= lab58e_hidden($csrf, 'lab58e_unpublish', $classId, $id) ?><button class="link-button" type="submit">Stáhnout z výuky</button></form>
        <?php endif; ?>
        <form method="post" onsubmit="return confirm('Opravdu smazat tuto úlohu?');"><?= lab58e_hidden($csrf, 'lab58e_delete', $classId, $id) ?><button class="link-button danger" type="submit">Smazat</button></form>
      </div>
    </article>
    <?php
}

/** @return array<string,string> */
function lab58e_field_defaults(): array
{
    return ['id' => '', 'type' => 'code', 'title' => '', 'story' => '', 'task' => '', 'learn' => '', 'difficulty' => '1', 'minutes' => '5', 'answer' => '', 'answer_format' => '', 'golf_reference' => '', 'solution' => ''];
}

function lab58e_render_form(?array $row, string $classId, string $csrf): void
{
    $d = lab58e_field_defaults();
    if ($row !== null) {
        $d = array_merge($d, array_intersect_key($row, $d));
        $d['difficulty'] = (string)($row['difficulty'] ?? 1);
        $d['minutes'] = (string)($row['minutes'] ?? 5);
        $d['solution'] = implode("\n", (array)($row['solution'] ?? []));
    }
    $classes = (array)($row['classes'] ?? [$classId]);
    $hints = array_pad((array)($row['hints'] ?? []), 3, '');
    $files = array_pad((array)($row['files'] ?? []), 6, ['path' => '', 'content' => '']);
    $generators = array_pad(array_map(static fn(array $g): array => ['name' => (string)($g[0] ?? ''), 'params' => $g[1] ?? []], (array)($row['generators'] ?? [])), 3, ['name' => '', 'params' => []]);
    $checks = array_pad(array_map(static fn(array $c): array => ['name' => (string)($c[0] ?? ''), 'label' => (string)($c[1]['label'] ?? ''), 'params' => $c[1] ?? []], (array)($row['checks'] ?? [])), 3, ['name' => '', 'label' => '', 'params' => []]);
    ?>
    <div class="teacher-panel-head"><div><span><?= $row !== null ? 'Upravit úlohu' : 'Nová úloha' ?></span><h2><?= $row !== null ? lab58e_h((string)$row['title']) : 'Vyplň zadání' ?></h2></div>
      <?php if ($row !== null): ?><a class="text-link" href="<?= lab58e_h('teacher.php?tab=editor&class=' . rawurlencode($classId)) ?>">Nová úloha místo úprav</a><?php endif; ?>
    </div>
    <form method="post" class="lab58e-form" data-lab58e-form>
      <?= lab58e_hidden($csrf, 'lab58e_save', $classId, $d['id']) ?>
      <div class="lab58e-row">
        <label>Název<input name="title" required maxlength="<?= LAB58E_MAX_TITLE ?>" value="<?= lab58e_h($d['title']) ?>"></label>
        <label>Typ cíle<select name="type"><?php foreach (['code' => 'Kód (submit)', 'answer' => 'Odpověď (answer)', 'check' => 'Oprava (checks)', 'golf' => 'Golf (jeden řádek)'] as $val => $lab): ?><option value="<?= $val ?>" <?= $d['type'] === $val ? 'selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select></label>
        <label>Obtížnost<select name="difficulty"><?php foreach ([1 => '1 (100 b)', 2 => '2 (150 b)', 3 => '3 (200 b)'] as $val => $lab): ?><option value="<?= $val ?>" <?= (int)$d['difficulty'] === $val ? 'selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select></label>
        <label>Minuty<input type="number" name="minutes" min="1" max="30" value="<?= lab58e_h($d['minutes']) ?>"></label>
      </div>
      <fieldset class="lab58e-classes-pick"><legend>Třídy, kterým se úloha zobrazí</legend>
        <?php foreach (lab58e_scope_class_ids() as $cid): ?><label class="lab58e-check"><input type="checkbox" name="classes[]" value="<?= lab58e_h($cid) ?>" <?= in_array($cid, $classes, true) ? 'checked' : '' ?>><?= lab58e_h(lab58e_class_label($cid)) ?></label><?php endforeach; ?>
      </fieldset>
      <label>Příběh (kontext úlohy)<textarea name="story" required rows="3" maxlength="<?= LAB58E_MAX_STORY ?>"><?= lab58e_h($d['story']) ?></textarea></label>
      <label>Úkol (co má žák udělat a jak odevzdat)<textarea name="task" required rows="2" maxlength="<?= LAB58E_MAX_TASK ?>"><?= lab58e_h($d['task']) ?></textarea></label>
      <fieldset><legend>Nápovědy (1.–3. stupeň, aspoň jedna)</legend>
        <?php foreach ($hints as $i => $hint): ?><input name="hints[]" placeholder="Nápověda <?= $i + 1 ?>" aria-label="Nápověda <?= $i + 1 ?>" maxlength="<?= LAB58E_MAX_HINT ?>" value="<?= lab58e_h((string)$hint) ?>"><?php endforeach; ?>
      </fieldset>
      <label>Co si žák odnese (zobrazí se po vyřešení)<textarea name="learn" rows="2" maxlength="<?= LAB58E_MAX_LEARN ?>"><?= lab58e_h($d['learn']) ?></textarea></label>

      <fieldset class="lab58e-files"><legend>Soubory ve světě úlohy (šablony: {CODE} {TOKEN} {DECOY} {NUM} {WORD} {NAME})</legend>
        <div data-lab58e-file-rows>
        <?php foreach ($files as $i => $file): ?>
          <div class="lab58e-file-row"><input name="files[<?= $i ?>][path]" placeholder="~/soubor.txt" aria-label="Cesta k souboru <?= $i + 1 ?>" value="<?= lab58e_h((string)$file['path']) ?>"><textarea name="files[<?= $i ?>][content]" rows="2" placeholder="Obsah, např. Kód: {CODE}" aria-label="Obsah souboru <?= $i + 1 ?>"><?= lab58e_h((string)$file['content']) ?></textarea></div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="btn secondary small" data-lab58e-add="file">+ Další soubor</button>
        <p class="lab58e-hint">Max <?= LAB58E_MAX_FILES ?> souborů, 64 KB na soubor. {TOKEN}/{DECOY}/{NUM}/{WORD}/{NAME} fungují jen tady (v odpovědi/řešení ne).</p>
      </fieldset>

      <fieldset class="lab58e-generators"><legend>Generátory z katalogu (nepovinné, pokročilé)</legend>
        <div data-lab58e-gen-rows>
        <?php foreach ($generators as $i => $gen): ?>
          <div class="lab58e-gen-row">
            <select name="generators[<?= $i ?>][name]" aria-label="Generátor <?= $i + 1 ?> – název"><option value="">— nepoužito —</option><?php foreach (lab58e_generator_catalog() as $name => $label): ?><option value="<?= lab58e_h($name) ?>" <?= $gen['name'] === $name ? 'selected' : '' ?>><?= lab58e_h($name . ' – ' . $label) ?></option><?php endforeach; ?></select>
            <input name="generators[<?= $i ?>][params]" placeholder='{"path":"~/log.txt"}' aria-label="Generátor <?= $i + 1 ?> – parametry (JSON)" value='<?= lab58e_h($gen['params'] !== [] ? json_encode($gen['params'], JSON_UNESCAPED_UNICODE) : '') ?>'>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="btn secondary small" data-lab58e-add="gen">+ Další generátor</button>
      </fieldset>

      <?php if ($d['type'] === 'check'): ?>
      <fieldset class="lab58e-checks"><legend>Kontroly (splněno = úloha vyřešená)</legend>
        <div data-lab58e-check-rows>
        <?php foreach ($checks as $i => $chk): ?>
          <div class="lab58e-check-row">
            <select name="checks[<?= $i ?>][name]" aria-label="Kontrola <?= $i + 1 ?> – typ"><option value="">— nepoužito —</option><?php foreach (lab58e_check_catalog() as $name => $label): ?><option value="<?= lab58e_h($name) ?>" <?= $chk['name'] === $name ? 'selected' : '' ?>><?= lab58e_h($name . ' – ' . $label) ?></option><?php endforeach; ?></select>
            <input name="checks[<?= $i ?>][label]" placeholder="Popisek pro žáka" aria-label="Kontrola <?= $i + 1 ?> – popisek pro žáka" value="<?= lab58e_h((string)$chk['label']) ?>">
            <input name="checks[<?= $i ?>][params]" placeholder='{"path":"~/ukol"}' aria-label="Kontrola <?= $i + 1 ?> – parametry (JSON)" value='<?= lab58e_h(array_diff_key($chk['params'], ['label' => 1]) !== [] ? json_encode(array_diff_key($chk['params'], ['label' => 1]), JSON_UNESCAPED_UNICODE) : '') ?>'>
          </div>
        <?php endforeach; ?>
        </div>
        <button type="button" class="btn secondary small" data-lab58e-add="check">+ Další kontrola</button>
      </fieldset>
      <?php endif; ?>

      <?php if ($d['type'] === 'answer'): ?>
      <div class="lab58e-row">
        <label>Očekávaná odpověď<input name="answer" maxlength="<?= LAB58E_MAX_ANSWER ?>" value="<?= lab58e_h($d['answer']) ?>"></label>
        <label>Formát odpovědi (nápověda pro žáka)<input name="answer_format" maxlength="120" value="<?= lab58e_h($d['answer_format']) ?>"></label>
      </div>
      <?php endif; ?>

      <?php if ($d['type'] === 'golf'): ?>
      <label>Referenční příkaz (jeho výstup musí žákovo řešení přesně zopakovat)<input name="golf_reference" value="<?= lab58e_h($d['golf_reference']) ?>"></label>
      <?php endif; ?>

      <label>Referenční řešení (jeden příkaz na řádek – server ho použije k testu řešitelnosti)<textarea name="solution" required rows="4" placeholder="cat soubor.txt&#10;submit {CODE}"><?= lab58e_h($d['solution']) ?></textarea></label>

      <div class="lab58e-form-actions"><button class="btn primary" type="submit">Uložit jako koncept</button></div>
    </form>
    <?php
}

function lab58e_render_check_panel(array $row, string $classId, string $csrf): void
{
    $id = (string)$row['id'];
    $lastCheck = is_array($row['last_check'] ?? null) ? $row['last_check'] : null;
    $checklist = is_array($row['checklist'] ?? null) ? $row['checklist'] : [];
    ?>
    <section class="lab58e-check-panel">
      <div class="teacher-panel-head compact"><div><span>Kontrola řešitelnosti a obsahu</span><h3>Před zveřejněním</h3></div>
        <form method="post"><?= lab58e_hidden($csrf, 'lab58e_check', $classId, $id) ?><button class="btn secondary small" type="submit">Spustit test (5 zadání)</button></form>
      </div>
      <?php if ($lastCheck === null): ?>
        <div class="teacher-empty">Ještě neotestováno. Ulož úlohu a spusť test.</div>
      <?php else: ?>
        <p class="lab58e-check-summary <?= !empty($lastCheck['ok']) ? 'ok' : 'bad' ?>">Řešitelnost: <strong><?= (int)$lastCheck['solved'] ?>/<?= (int)$lastCheck['total'] ?></strong> zadání vyřešeno referenčním postupem.</p>
        <?php if (!empty($lastCheck['detail'])): ?><ul class="lab58e-detail"><?php foreach ((array)$lastCheck['detail'] as $line): ?><li><?= lab58e_h((string)$line) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <p class="lab58e-check-summary <?= !empty($lastCheck['content_ok']) ? 'ok' : 'bad' ?>">Kontrola obsahu: <strong><?= !empty($lastCheck['content_ok']) ? 'v pořádku' : 'našla problém' ?></strong></p>
        <?php if (!empty($lastCheck['issues'])): ?><ul class="lab58e-detail"><?php foreach ((array)$lastCheck['issues'] as $issue): ?><li class="<?= ($issue['severity'] ?? 'warn') === 'block' ? 'block' : 'warn' ?>"><?= lab58e_h((string)($issue['field'] ?? '')) ?>: <?= lab58e_h((string)($issue['message'] ?? '')) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?php endif; ?>
      <form method="post" class="lab58e-publish-form">
        <?= lab58e_hidden($csrf, 'lab58e_publish', $classId, $id) ?>
        <fieldset><legend>Checklist před zveřejněním (potvrď každou položku)</legend>
          <?php foreach (function_exists('cnt58_checklist_items') ? cnt58_checklist_items() : [] as $item): ?>
            <label class="lab58e-check"><input type="checkbox" name="checklist[<?= lab58e_h($item['id']) ?>]" value="1" <?= !empty($checklist[$item['id']]) ? 'checked' : '' ?>><?= lab58e_h($item['label']) ?></label>
          <?php endforeach; ?>
        </fieldset>
        <button class="btn primary" type="submit">Zveřejnit pro vybrané třídy</button>
      </form>
    </section>
    <?php
}
