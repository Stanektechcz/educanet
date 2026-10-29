<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v61 · týdenní kontrola provozu: úložiště výsledků + upozornění administrátorovi v učitelském cockpitu.
 *
 * Výsledky zapisuje CLI `tools/v61_weekly_health.php` (cron `health`) do `storage/ops_health_v61.json.php`
 * (posledních 12 běhů). Uložené jsou jen stavy PASS/WARN/FAIL, počty a krátké názvy neúspěšných kontrol
 * bez osobních údajů a bez tajných hodnot. Banner vidí jen administrátor; ostatní učitelé nic.
 */

const OPS61_HEALTH_KEEP = 12;
/** Po kolika dnech bez běhu se zobrazí upozornění, že kontrola neběží (týdenní cron + rezerva). */
const OPS61_HEALTH_STALE_DAYS = 10;

function ops61_health_path(): string
{
    return STORAGE_DIR . '/ops_health_v61.json.php';
}

/** Uložené běhy, nejnovější poslední. @return list<array<string,mixed>> */
function ops61_health_runs(): array
{
    $path = ops61_health_path();
    if (!is_file($path)) return [];
    $runs = storage_read($path, true)['runs'] ?? [];
    return is_array($runs) ? array_values(array_filter($runs, 'is_array')) : [];
}

/** Zapíše běh (read-modify-write přes storage_update) a ponechá posledních OPS61_HEALTH_KEEP. */
function ops61_health_record(array $run): void
{
    storage_update(ops61_health_path(), static function (array $data) use ($run): array {
        $runs = is_array($data['runs'] ?? null) ? array_values($data['runs']) : [];
        $runs[] = $run;
        return ['version' => 1, 'runs' => array_slice($runs, -OPS61_HEALTH_KEEP)];
    });
}

/** Celkový stav běhu: FAIL > WARN > PASS (neznámý tvar = FAIL). */
function ops61_health_overall(array $run): string
{
    $status = 'PASS';
    $checks = is_array($run['checks'] ?? null) ? $run['checks'] : [];
    if ($checks === []) return 'FAIL';
    foreach ($checks as $check) {
        $s = is_array($check) ? (string)($check['status'] ?? 'FAIL') : 'FAIL';
        if ($s === 'FAIL' || !in_array($s, ['PASS', 'WARN', 'SKIP'], true)) return 'FAIL';
        if ($s === 'WARN') $status = 'WARN';
    }
    return $status;
}

/**
 * HTML banneru pro administrátora (prázdný řetězec = nic nezobrazovat). Čistá funkce: nic nečte ani nezapisuje.
 * Zobrazí se při posledním běhu FAIL/WARN, nebo když poslední běh je starší než OPS61_HEALTH_STALE_DAYS dní.
 */
function ops61_health_banner_html(?array $last, bool $isAdmin, int $now): string
{
    if (!$isAdmin || $last === null) return '';
    $status = ops61_health_overall($last);
    $at = strtotime((string)($last['at'] ?? '')) ?: 0;
    $ageDays = $at > 0 ? intdiv(max(0, $now - $at), 86400) : 999;
    $stale = $ageDays > OPS61_HEALTH_STALE_DAYS;
    if ($status === 'PASS' && !$stale) return '';
    $text = $status === 'FAIL' ? 'Týdenní kontrola provozu hlásí chybu.' : ($status === 'WARN' ? 'Týdenní kontrola provozu má upozornění.' : '');
    if ($stale) $text .= ($text !== '' ? ' ' : '') . 'Poslední kontrola proběhla před ' . $ageDays . ' dny (má běžet týdně).';
    $class = $status === 'FAIL' ? 'teacher-flash error' : 'teacher-flash';
    return '<div class="' . $class . '" role="status" data-ops61-health="' . e($status) . '">' . e($text)
        . ' <a href="?tab=provoz#tydenni-kontrola" style="display:inline-block;min-height:44px;line-height:44px;font-weight:700">Zobrazit detail v záložce Provoz</a></div>';
}

/** Banner v cockpitu; $isAdmin = null → podle přihlášeného učitele. */
function ops61_render_health_banner(?bool $isAdmin = null): void
{
    try {
        $isAdmin ??= function_exists('teacher59_is_admin') && teacher59_is_admin();
        if (!$isAdmin) return;
        $runs = ops61_health_runs();
        echo ops61_health_banner_html($runs === [] ? null : end($runs), true, time());
    } catch (Throwable $e) {
        // Banner nesmí shodit učitelský cockpit.
    }
}

/** Panel „Týdenní kontrola“ v záložce Provoz (jen administrátor – záložka je admin). */
function ops61_render_health_panel(): void
{
    $runs = array_reverse(ops61_health_runs());
    ?>
<section class="teacher-panel" id="tydenni-kontrola" aria-labelledby="ops61-health-title">
  <div class="teacher-panel-head"><div><span>Týdenní kontrola</span><h2 id="ops61-health-title">Stav provozu</h2>
    <p>Spouští plánovač (<code>educanet-cron.sh health</code>): samotest úložiště, preflight a měření rychlosti jen čtením. Ukládají se jen stavy a počty.</p></div></div>
  <?php if ($runs === []): ?>
    <p class="teacher-empty">Zatím neproběhla žádná kontrola. Na serveru: <code>bash /www/server/educanet/educanet-cron.sh health</code>.</p>
  <?php else: ?>
  <table class="ops58-table"><caption class="sr-only">Posledních <?= count($runs) ?> týdenních kontrol</caption>
    <thead><tr><th scope="col">Kdy</th><th scope="col">Celkem</th><th scope="col">Samotest</th><th scope="col">Preflight</th><th scope="col">Rychlost</th><th scope="col">Provoz</th></tr></thead><tbody>
    <?php foreach ($runs as $run):
        $checks = is_array($run['checks'] ?? null) ? $run['checks'] : []; ?>
      <tr><td><?= e((string)($run['at'] ?? '')) ?></td><th scope="row"><?= e(ops61_health_overall($run)) ?></th>
      <?php foreach (['selftest', 'preflight', 'perf', 'ops'] as $name): $c = is_array($checks[$name] ?? null) ? $checks[$name] : []; ?>
        <td><?= e((string)($c['status'] ?? '–')) ?><?php if (!empty($c['summary'])): ?> <small><?= e((string)$c['summary']) ?></small><?php endif; ?></td>
      <?php endforeach; ?></tr>
    <?php endforeach; ?>
    </tbody></table>
    <?php $last = $runs[0]; $issues = [];
    foreach ((array)($last['checks'] ?? []) as $name => $c) foreach ((array)($c['issues'] ?? []) as $issue) $issues[] = (string)$name . ': ' . (string)$issue;
    if ($issues): ?>
    <h3>Co hlásil poslední běh</h3>
    <ul class="ops58-preview-list"><?php foreach ($issues as $issue): ?><li><code><?= e($issue) ?></code></li><?php endforeach; ?></ul>
    <?php endif; ?>
  <?php endif; ?>
</section>
    <?php
}
