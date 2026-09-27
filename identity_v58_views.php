<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v58 · F6 – učitelská záložka „Identita a nový rok“ (?tab=identita). Jen náhled: registr, kolize jmen a plán
 * přechodu roku. Zápisy (sestavení s --apply, rozlišení jmenovců, přechod roku) běží jen z CLI.
 * POST: identity58_plan (CSRF) → přesměrování na ?tab=identita&plan=<rok> (plán se počítá jen v paměti).
 */

require_once __DIR__ . '/identity_v58_rollover.php';

const IDENTITY58_TYPE_LABELS = [
    'directory_namesake' => 'Jmenovci v seznamu třídy',
    'shared_account' => 'Více účtů u jednoho žáka',
    'alias_conflict' => 'Klíč patří jinému žákovi',
    'same_name' => 'Stejné jméno ve více třídách',
];

function identity58_teacher_handle_post(string $action, array $modules): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals((string)($_SESSION['csrf'] ?? ''), $token)) throw new RuntimeException('Formulář vypršel. Obnov stránku a zkus to znovu.');
    if ($action !== 'identity58_plan') throw new RuntimeException('Neznámá akce.');
    $year = (int)($_POST['year'] ?? 0);
    if ($year < 2000 || $year > 2100) $year = identity58_default_year();
    header('Location: teacher.php?' . http_build_query(['tab' => 'identita', 'plan' => $year]));
    exit;
}

function identity58_class_name(string $classId): string
{
    return preg_match('/^class_(\d+)([a-z])$/', $classId, $m) ? $m[1] . '.' . strtoupper($m[2]) : $classId;
}

function identity58_format_time(string $iso): string
{
    $ts = $iso !== '' ? strtotime($iso) : false;
    return $ts ? date('j. n. Y H:i', $ts) : 'zatím ne';
}

function identity58_render_teacher_tab(string $csrf): void
{
    $reg = identity58_registry();
    $byStatus = ['active' => 0, 'archived' => 0, 'left' => 0];
    $byClass = [];
    foreach ($reg['students'] as $stu) {
        if (!is_array($stu)) continue;
        $status = (string)($stu['status'] ?? 'active');
        $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        if ($status === 'active') $byClass[(string)$stu['class_id']] = ($byClass[(string)$stu['class_id']] ?? 0) + 1;
    }
    ksort($byClass);
    $map = student_account_map();
    $withId = count(array_filter($map, static fn($r): bool => is_array($r) && !empty($r['student_id'])));
    $dups = identity58_duplicates($reg);
    $planYear = isset($_GET['plan']) && preg_match('/^\d{4}$/', (string)$_GET['plan']) ? (int)$_GET['plan'] : null;
    $defaultYear = identity58_default_year($reg);
    ?>
<section class="teacher-page-head"><div><div class="eyebrow">Podpora · identita žáků</div><h1>Identita a nový školní rok</h1>
<p>Jen náhled. Každý žák má stálé <code>student_id</code>; staré klíče (třída + jméno, účty, Linux Lab) na něj jen ukazují, historická data se nepřepisují. Zápisy se spouští z příkazové řádky: <code>tools/v58_identity.php</code> a <code>tools/v58_rollover.php</code>.</p></div></section>
<section class="idt58-grid">
  <article class="teacher-panel idt58-card" aria-labelledby="idt58-reg">
    <div class="teacher-panel-head"><div><span>Registr</span><h2 id="idt58-reg"><?= count($reg['students']) ?> žáků</h2></div></div>
    <ul class="idt58-stats">
      <li><span>Aktivní</span><b><?= (int)$byStatus['active'] ?></b></li>
      <li><span>Archivovaní (absolventi)</span><b><?= (int)$byStatus['archived'] ?></b></li>
      <li><span>Odešli ze školy</span><b><?= (int)$byStatus['left'] ?></b></li>
      <li><span>Účty se <code>student_id</code></span><b><?= (int)$withId ?> / <?= count($map) ?></b></li>
      <li><span>Aliasů</span><b><?= count($reg['alias_index']) ?></b></li>
      <li><span>Sestaveno</span><b><?= e(identity58_format_time((string)($reg['built_at'] ?? ''))) ?></b></li>
    </ul>
    <?php if ($byClass): ?>
    <ul class="idt58-chips" aria-label="Aktivní žáci podle tříd"><?php foreach ($byClass as $cid => $n): ?><li><?= e(identity58_class_name((string)$cid)) ?> <b><?= (int)$n ?></b></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ($reg['class_codes']): ?>
    <p class="idt58-note">Kódy tříd pro školní rok <?= (int)$reg['class_codes_year'] ?>/<?= (int)$reg['class_codes_year'] + 1 ?>:
      <?php foreach ($reg['class_codes'] as $cid => $code): ?><span class="idt58-code"><?= e(identity58_class_name((string)$cid)) ?> <code><?= e((string)$code) ?></code></span> <?php endforeach; ?></p>
    <?php endif; ?>
  </article>
  <article class="teacher-panel idt58-card" aria-labelledby="idt58-dups">
    <div class="teacher-panel-head"><div><span>Kolize jmen</span><h2 id="idt58-dups"><?= e($dups ? count($dups) . ' ke kontrole' : 'Bez kolizí') ?></h2></div></div>
    <?php if (!$dups): ?>
      <p class="idt58-note">Žádní jmenovci ani sdílené klíče. Registr se kontroluje při každé změně seznamu žáků nebo účtů.</p>
    <?php else: ?>
      <div class="idt58-table-wrap" tabindex="0" role="region" aria-label="Seznam kolizí jmen">
      <table class="idt58-table">
        <caption class="idt58-sr">Podezřelé kolize jmen a sdílené klíče</caption>
        <thead><tr><th scope="col">Typ</th><th scope="col">Třída</th><th scope="col">Žák</th><th scope="col">Co to znamená</th><th scope="col">student_id</th></tr></thead>
        <tbody>
        <?php foreach ($dups as $d): ?>
          <tr class="idt58-<?= e((string)$d['severity']) ?>">
            <td><?= e(IDENTITY58_TYPE_LABELS[$d['type']] ?? (string)$d['type']) ?></td>
            <td><?= e(implode(', ', array_map('identity58_class_name', explode(', ', (string)$d['class_id'])))) ?></td>
            <td><?= e((string)$d['label']) ?></td>
            <td><?= e((string)$d['detail']) ?></td>
            <td><code><?= e(implode(', ', (array)$d['stu_ids'])) ?></code></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <p class="idt58-note">Řešení: jmenovce rozliš (např. „Jan Novák (B)“) příkazem <code>php tools/v58_identity.php --split=&lt;student_id&gt; --label="…" --account=&lt;klíč účtu&gt; --apply</code>. Nový žák dostane nové ID; dosud sloučená data zůstávají u původního ID a automaticky rozdělit nejdou.</p>
    <?php endif; ?>
  </article>
</section>
<section class="teacher-panel idt58-card" aria-labelledby="idt58-plan">
  <div class="teacher-panel-head"><div><span>Přechod školního roku</span><h2 id="idt58-plan">Plán přechodu</h2></div></div>
  <form method="post" action="teacher.php?tab=identita" class="idt58-form">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="identity58_plan">
    <label for="idt58-year">Nový školní rok začíná v roce</label>
    <input id="idt58-year" name="year" type="number" inputmode="numeric" min="2000" max="2100" value="<?= (int)($planYear ?? $defaultYear) ?>" required>
    <button type="submit" class="button primary">Zobrazit plán</button>
  </form>
  <?php if ($planYear !== null) identity58_render_plan($planYear, $reg); ?>
</section>
<?php
}

function identity58_render_plan(int $year, array $reg): void
{
    $preview = identity58_compute($reg, identity58_directory_rows($reg), student_account_map(), local_accounts(), time());
    $plan = identity58_rollover_plan($year, [], $preview['registry']);
    $c = $plan['counts'];
    $backup = identity58_backup_check(identity58_backup_dir());
    $missing = identity58_integration_missing();
    ?>
  <div class="idt58-plan" role="status" aria-live="polite">
    <h3>Plán pro školní rok <?= (int)$year ?>/<?= (int)$year + 1 ?><?= e($plan['already_applied'] ? ' – už proveden' : '') ?></h3>
    <ul class="idt58-stats">
      <?php foreach ($c['move'] as $transition => $n): [$from, $to] = explode('→', (string)$transition); ?>
        <li><span><?= e(identity58_class_name($from)) ?> → <?= e(identity58_class_name($to)) ?></span><b><?= (int)$n ?></b></li>
      <?php endforeach; ?>
      <li><span>Absolventi do archivu</span><b><?= (int)$c['archive'] ?></b></li>
      <li><span>Přenos XP a odznaků</span><b><?= (int)$c['carry_profile'] ?></b></li>
      <li><span>Přenos vyřešených úrovní labu</span><b><?= (int)$c['carry_lab'] ?></b></li>
      <li><span>Demo účty (beze změny)</span><b><?= (int)$c['demo_skipped'] ?></b></li>
      <li><span>Jmenovci v cílové třídě</span><b><?= count($plan['conflicts']) ?></b></li>
      <li><span>Záloha mladší než 1 h</span><b><?= e($backup['ok'] ? 'ano' : 'ne') ?></b></li>
      <li><span>Háčky aplikace</span><b><?= e($missing ? 'chybí' : 'v pořádku') ?></b></li>
    </ul>
    <?php if ($plan['conflicts']): ?>
      <p class="idt58-warn">Jmenovci v cílové třídě – před přechodem je přejmenuj (<code>tools/v58_identity.php --relabel</code>):</p>
      <ul class="idt58-list"><?php foreach ($plan['conflicts'] as $k): ?><li><?= e((string)$k['label']) ?> → <?= e(identity58_class_name((string)$k['to'])) ?> <code><?= e((string)$k['stu_id']) ?></code></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php foreach ($plan['blockers'] as $b): ?><p class="idt58-warn"><?= e((string)$b) ?></p><?php endforeach; ?>
    <p class="idt58-note">Kurz třídy zůstává historií ročníku, absolventi se nepřihlásí, třídy dostanou nové kódy. Provedení jen z příkazové řádky po záloze:
      <code>php tools/backup_storage.php</code> a <code>php tools/v58_rollover.php --dry-run --year=<?= (int)$year ?></code>, potom <code>--apply</code>.</p>
  </div>
<?php
}
