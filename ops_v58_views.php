<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Provoz – šablony pro učitelskou záložku „Provoz" (?tab=provoz).
 * Jen náhled: skutečná údržba (retention --apply, zálohy, obnova) se spouští z CLI
 * (viz tools/backup_storage.php, tools/restore_storage.php, tools/v58_retention.php).
 */

function ops58_format_bytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $value = (float)$bytes;
    $i = 0;
    while ($value >= 1024 && $i < count($units) - 1) {
        $value /= 1024;
        $i++;
    }
    return number_format($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
}

function ops58_render_health_tab(string $csrf): void
{
    $health = ops58_health();
    $lastBackup = $health['last_backup'];
    $preview = ops58_apply_retention(true);
    ?>
<section class="teacher-page-head"><div><div class="eyebrow">Provoz</div><h1>Zdraví úložiště a retence</h1><p>Jen náhled – zálohy, obnova i skutečná retenční úprava se spouští z příkazové řádky (viz <code>tools/backup_storage.php</code>, <code>tools/restore_storage.php</code>, <code>tools/v58_retention.php</code>).</p></div></section>
<section class="ops58-grid">
  <article class="teacher-panel ops58-card">
    <div class="teacher-panel-head"><div><span>Úložiště</span><h2><?= ops58_format_bytes((int)$health['total_size_bytes']) ?></h2></div></div>
    <ul class="ops58-stat-list">
      <li><span>Souborů celkem</span><b><?= (int)$health['file_count'] ?></b></li>
      <li><span>Události Linux Labu</span><b><?= (int)$health['lab_event_count'] ?></b></li>
      <li><span>Soubory závodů Arény</span><b><?= (int)$health['race_file_count'] ?></b></li>
      <li><span>Volné místo na disku</span><b><?= $health['free_disk_bytes'] !== null ? e(ops58_format_bytes((int)$health['free_disk_bytes'])) : 'neznámé' ?></b></li>
      <li><span>PHP verze</span><b><?= e((string)$health['php_version']) ?></b></li>
      <li><span>Školní rok</span><b><?= e((string)$health['school_year']) ?></b></li>
    </ul>
  </article>
  <article class="teacher-panel ops58-card">
    <div class="teacher-panel-head"><div><span>Poslední záloha</span><h2><?= $lastBackup ? 'Nalezena' : 'Chybí' ?></h2></div></div>
    <?php if ($lastBackup): ?>
      <ul class="ops58-stat-list">
        <li><span>Vytvořena</span><b><?= e((string)($lastBackup['created_at'] ?: 'neznámo')) ?></b></li>
        <li><span>Souborů v záloze</span><b><?= $lastBackup['file_count'] !== null ? (int)$lastBackup['file_count'] : '?' ?></b></li>
        <li><span>Manifest</span><b><?= !empty($lastBackup['manifest_ok']) ? 'OK' : 'poškozen/chybí' ?></b></li>
        <li><span>Umístění</span><code><?= e((string)$lastBackup['path']) ?></code></li>
      </ul>
    <?php else: ?>
      <p class="teacher-empty">V zálohovacím adresáři zatím není žádná záloha. Doporučení: spusť <code>php tools/backup_storage.php</code> pravidelně (např. denně přes plánovač úloh) a ulož výstup mimo web root.</p>
    <?php endif; ?>
  </article>
  <article class="teacher-panel ops58-card">
    <div class="teacher-panel-head"><div><span>Rozšíření PHP</span><h2><?= array_sum(array_map('intval', $health['extensions'])) ?>/<?= count($health['extensions']) ?></h2></div></div>
    <ul class="ops58-ext-list">
      <?php foreach ($health['extensions'] as $name => $ok): ?>
        <li class="<?= $ok ? 'ok' : 'missing' ?>"><span><?= e($name) ?></span><b><?= $ok ? 'dostupné' : 'chybí' ?></b></li>
      <?php endforeach; ?>
    </ul>
  </article>
</section>

<section class="teacher-panel ops58-top-files">
  <div class="teacher-panel-head"><div><span>Top 15 souborů podle velikosti</span><h2>Kde se hromadí data</h2></div></div>
  <table class="ops58-table"><thead><tr><th scope="col">Soubor</th><th scope="col">Velikost</th></tr></thead><tbody>
    <?php foreach ($health['top_files'] as $f): ?>
      <tr><td><code><?= e((string)$f['path']) ?></code></td><td><?= e(ops58_format_bytes((int)$f['size'])) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$health['top_files']): ?><tr><td colspan="2" class="teacher-empty">Storage je prázdná.</td></tr><?php endif; ?>
  </tbody></table>
</section>

<section class="teacher-panel ops58-retention">
  <div class="teacher-panel-head"><div><span>Retenční politika</span><h2>Náhled (dry-run)</h2><p>Nic se odsud nemaže ani nearchivuje – jen ukazuje, co by <code>php tools/v58_retention.php --apply</code> udělal teď.</p></div></div>
  <table class="ops58-table"><thead><tr><th scope="col">Vzor</th><th scope="col">Popis</th><th scope="col">Akce</th></tr></thead><tbody>
    <?php foreach (ops58_retention_policy() as $policy): ?>
      <tr><td><code><?= e((string)$policy['pattern']) ?></code></td><td><?= e((string)$policy['label']) ?></td><td><?= e((string)$policy['action']) ?><?= $policy['max_age_days'] !== null ? ' (' . (int)$policy['max_age_days'] . ' dní)' : '' ?></td></tr>
    <?php endforeach; ?>
  </tbody></table>
  <?php if ($preview['actions']): ?>
    <h3>Co by se teď provedlo</h3>
    <ul class="ops58-preview-list">
      <?php foreach ($preview['actions'] as $a): ?>
        <li><b><?= e((string)$a['action']) ?></b> <code><?= e((string)$a['path']) ?></code> — <?= e((string)$a['detail']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="teacher-empty">Aktuálně není co archivovat ani pročišťovat.</p>
  <?php endif; ?>
</section>
    <?php
    unset($csrf); // zatím bez formulářové akce na této záložce (jen CLI provádí zápisy)
}
