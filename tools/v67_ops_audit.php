<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · audit provozu: health, klíč záloh, noční přepočet, rollback migrací, plán přechodu roku, reporty a vyřazování souborů.
 *   1) rozšířený health (stáří a šifrování zálohy, backup_key, migrace, noční přepočet, APCu, strict_mode, display_errors),
 *   2) tools/backup_key_init.php: náhled nic nezapíše, --apply zapíše 0600 a vypíše jen 8 znaků otisku (NIKDY klíč), existující klíč nepřepíše,
 *   3) tools/v67_nightly_recompute.php: odmítne bez EDUCANET_NIGHTLY_ALLOW, bez zálohy, se starou zálohou i při zámku; s čerstvou zálohou proběhne a je idempotentní,
 *   4) tools/migrate.php --rollback: neprovedená migrace = chyba, bez down() přesný příkaz obnovy ze zálohy před migrací, plán s down(),
 *   5) plán přechodu roku v62–v67 (jen čtení), effect report (nic nezapíše, potlačí skupiny < 5, bez jmen), UT souhrn,
 *   6) vyřazování: seznam schválených je prázdný, bez souhlasu se nic nesmaže, postup kopie → SHA-256 → smazání, zakázané cesty,
 *   7) cron (nightly nejdřív záloha) a provozní dokumentace, preflight kontroluje duplicitní rozšíření.
 * Všechno běží v dočasném úložišti a dočasných adresářích; ostrou storage/ nikdy nečte ani nezapisuje.
 *
 *   php tools/v67_ops_audit.php
 * Konec: V67_OPS_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v67-ops')), '/');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/v61_perf_fixture.php';
foreach (['intake_v51.php', 'teacher_operations_v46.php', 'identity_v58.php', 'identity_v58_rollover.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php',
    'paths_v63.php', 'projects_v60.php', 'projects_v65.php', 'challenges_v64.php', 'growth_v67.php', 'ops_v67.php', 'rollover_v67.php'] as $lib) { require_once $ROOT . '/' . $lib; }
require_once __DIR__ . '/lib/v62_competency_fixtures.php';
require_once __DIR__ . '/backup_storage.php';
require_once __DIR__ . '/migrate.php';
require_once __DIR__ . '/v67_retire.php';
require_once __DIR__ . '/v67_ut_summary.php';

$state = audit_counter();
$check = audit_checker($state);
$php = is_file('C:/php/php.exe') ? 'C:/php/php.exe' : PHP_BINARY;

/** Spustí PHP skript v podprocesu s prostředím; vrací [exit, stdout, stderr]. */
function v67o_run(string $php, array $args, array $env, string $cwd, array $phpArgs = []): array
{
    $full = [];
    foreach (array_merge($_SERVER, $_ENV, getenv()) as $k => $v) if (is_scalar($v)) $full[(string)$k] = (string)$v;
    foreach (['EDUCANET_NIGHTLY_ALLOW', 'EDUCANET_BACKUP_DIR', 'EDUCANET_STORAGE_READONLY'] as $drop) unset($full[$drop]);
    $p = proc_open(array_merge([$php], $phpArgs, $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd, array_merge($full, $env), ['bypass_shell' => true]);
    if (!is_resource($p)) return [-1, '', ''];
    $out = (string)stream_get_contents($pipes[1]);
    $err = (string)stream_get_contents($pipes[2]);
    foreach ($pipes as $pp) fclose($pp);
    return [proc_close($p), $out, $err];
}

function v67o_snapshot(string $dir, array $skip = []): array
{
    $out = [];
    if (!is_dir($dir)) return $out;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (str_ends_with($f->getFilename(), '.lock') || in_array($f->getFilename(), $skip, true)) continue;
        $out[substr(str_replace(chr(92), '/', $f->getPathname()), strlen($dir))] = md5_file($f->getPathname());
    }
    ksort($out);
    return $out;
}

function v67o_tmpdir(string $tag): string
{
    $d = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-audit-v67o-' . $tag . '-' . bin2hex(random_bytes(4));
    mkdir($d, 0700, true);
    return $d;
}

$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));
v62fx_seed($tmp, time());
identity58_ensure();

// ---------------------------------------------------------------- 1) health
$bk = v67o_tmpdir('bk');
putenv('EDUCANET_BACKUP_DIR=' . $bk);
$byName = static fn(array $items): array => array_column($items, 'status', 'name');
$none = ops67_items();
$check('health: bez zálohy hlásí WARN (stáří i šifrování)', ($byName($none)['záloha_stáří'] ?? '') === 'WARN' && ($byName($none)['záloha_šifrovaná'] ?? '') === 'WARN');
file_put_contents($bk . '/storage-20260101-000000.edubak', 'x');
$enc = ops67_items();
$check('health: čerstvý šifrovaný archiv (.edubak) = záloha PASS a šifrovaná PASS', ($byName($enc)['záloha_stáří'] ?? '') === 'PASS' && ($byName($enc)['záloha_šifrovaná'] ?? '') === 'PASS');
touch($bk . '/storage-20260101-000000.edubak', time() - 5 * 86400);
$check('health: záloha stará 5 dní = stáří WARN', ($byName(ops67_items())['záloha_stáří'] ?? '') === 'WARN');
$beforeMig = $byName(ops67_items())['migrace'] ?? '';
migrate_run(migrate_load_all(dirname(__DIR__) . '/migrations'), false);
$check('health: čekající migrace jsou hlášené jako WARN, po provedení PASS', $beforeMig === 'WARN' && ($byName(ops67_items())['migrace'] ?? '') === 'PASS');
$firstNightly = $byName(ops67_items())['noční_přepočet'] ?? '';
storage_update($tmp . '/v67_nightly.json.php', static fn(array $d): array => ['v' => 1, 'last' => ['at' => date(DATE_ATOM)]]);
$secondNightly = $byName(ops67_items())['noční_přepočet'] ?? '';
storage_update($tmp . '/v67_nightly.json.php', static fn(array $d): array => ['v' => 1, 'last' => ['at' => date(DATE_ATOM, time() - 3 * 86400)]]);
$check('health: noční přepočet „zatím neproběhl“ = WARN, po zápisu stavu PASS, starý stav WARN', $firstNightly === 'WARN' && $secondNightly === 'PASS' && ($byName(ops67_items())['noční_přepočet'] ?? '') === 'WARN');
$health = ops67_health_check();
$check('health: výsledek má tvar týdenní kontroly (status, summary, counts, issues) a nevypisuje klíč ani cesty k tajemstvím', in_array($health['status'], ['PASS', 'WARN'], true) && isset($health['summary'], $health['counts'], $health['issues']) && !str_contains(json_encode($health), 'secrets'));
$panel = (string)file_get_contents($ROOT . '/ops_v61.php');
$check('health: cockpit (panel Provoz) má sloupec Provoz a týdenní health spouští kontrolu ops', str_contains($panel, '>Provoz</th>') && str_contains($panel, "'ops'") && str_contains((string)file_get_contents(__DIR__ . '/v61_weekly_health.php'), 'ops67_health_check'));
[$whExit, $whOut] = v67o_run($php, [__DIR__ . '/v61_weekly_health.php', '--skip=selftest,preflight,perf', '--no-log', '--no-record'], ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_BACKUP_DIR' => $bk], $ROOT);
$check('health: tools/v61_weekly_health.php vypíše řádek kontroly ops a konec WEEKLY_HEALTH_*', preg_match('/^(PASS|WARN|FAIL) ops\b/m', $whOut) === 1 && preg_match('/^WEEKLY_HEALTH_(OK|WARN|FAIL)\b/m', $whOut) === 1);

// ---------------------------------------------------------------- 2) backup_key_init
$sodiumArgs = extension_loaded('sodium') ? [] : (is_file(dirname($php) . '/ext/php_sodium.dll') ? ['-d', 'extension=sodium'] : null);
if ($sodiumArgs === null) {
    echo "SKIP backup_key_init: PHP rozšíření sodium není k dispozici\n";
} else {
    $sd = v67o_tmpdir('sec');
    $secrets = $sd . '/educanet.secrets.php';
    file_put_contents($secrets, "<?php\nreturn ['teacher_name' => 'Test', 'backup_key' => ''];\n");
    $before = md5_file($secrets);
    $env = ['EDUCANET_STORAGE_DIR' => $tmp];
    $tool = __DIR__ . '/backup_key_init.php';
    [$e1, $o1] = v67o_run($php, [$tool, '--secrets-file=' . $secrets], $env, $ROOT, $sodiumArgs);
    $check('backup_key_init: bez --apply jen náhled (DRYRUN), soubor beze změny', $e1 === 0 && str_contains($o1, 'BACKUP_KEY_INIT_DRYRUN') && md5_file($secrets) === $before);
    [$e2, $o2, $err2] = v67o_run($php, [$tool, '--secrets-file=' . $secrets, '--apply'], $env, $ROOT, $sodiumArgs);
    $loaded = (array)(require $secrets);
    $key = (string)($loaded['backup_key'] ?? '');
    $raw = base64_decode($key, true);
    $check('backup_key_init: --apply zapíše platný klíč (base64, 32 B) a zachová ostatní tajemství', $e2 === 0 && $raw !== false && strlen($raw) === 32 && ($loaded['teacher_name'] ?? '') === 'Test');
    $check('backup_key_init: výstup obsahuje jen 8 znaků SHA-256 otisku a nikdy klíč (base64 ani hex)', $raw !== false && preg_match('/^BACKUP_KEY_INIT_OK fingerprint=([0-9a-f]{8})$/m', $o2, $fm) === 1 && $fm[1] === substr(hash('sha256', $raw), 0, 8) && !str_contains($o2 . $err2, $key) && !str_contains($o2 . $err2, bin2hex($raw)));
    if (DIRECTORY_SEPARATOR === '/') $check('backup_key_init: soubor secrets má práva 0600', (fileperms($secrets) & 0777) === 0600);
    $after = md5_file($secrets);
    [$e3, , $err3] = v67o_run($php, [$tool, '--secrets-file=' . $secrets, '--apply'], $env, $ROOT, $sodiumArgs);
    $check('backup_key_init: existující klíč se nepřepíše (REFUSED key_exists, soubor beze změny)', $e3 === 2 && str_contains($err3, 'key_exists') && md5_file($secrets) === $after);
    [$e4, , $err4] = v67o_run($php, [$tool, '--secrets-file=' . $sd . '/neexistuje.php', '--apply'], $env, $ROOT, $sodiumArgs);
    $check('backup_key_init: chybějící soubor secrets bez --create se odmítne a nic nevznikne', $e4 === 2 && str_contains($err4, 'secrets_missing') && !is_file($sd . '/neexistuje.php'));
}

// ---------------------------------------------------------------- 3) noční přepočet
$nb = v67o_tmpdir('nb');
$nightlyEnv = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_BACKUP_DIR' => $nb];
$allow = $nightlyEnv + ['EDUCANET_NIGHTLY_ALLOW' => '1'];
$nightly = __DIR__ . '/v67_nightly_recompute.php';
[$n1, , $nerr1] = v67o_run($php, [$nightly], $nightlyEnv, $ROOT);
$check('nightly: bez EDUCANET_NIGHTLY_ALLOW odmítne (exit 2, REFUSED allow_env)', $n1 === 2 && str_contains($nerr1, 'allow_env'));
$snapBefore = v67o_snapshot($tmp);
[$n2, , $nerr2] = v67o_run($php, [$nightly], $allow, $ROOT);
$check('nightly: s povolením, ale bez zálohy odmítne (no_fresh_backup) a nic nezapíše', $n2 === 2 && str_contains($nerr2, 'no_fresh_backup') && v67o_snapshot($tmp) === $snapBefore);
$stale = $nb . '/storage-20200101-000000';
mkdir($stale);
file_put_contents($stale . '/manifest.json', json_encode(['created_at' => date(DATE_ATOM, time() - 7200), 'source' => STORAGE_DIR]));
[$n3, , $nerr3] = v67o_run($php, [$nightly], $allow, $ROOT);
$check('nightly: záloha stará 2 h se odmítne (musí být mladší než 1 h) a nic nezapíše', $n3 === 2 && str_contains($nerr3, 'no_fresh_backup') && v67o_snapshot($tmp) === $snapBefore);
backup_storage_run(STORAGE_DIR, $nb, 14, false, null, true);
$lockFp = fopen($tmp . '/.nightly.lock', 'c');
flock($lockFp, LOCK_EX);
[$n4, , $nerr4] = v67o_run($php, [$nightly], $allow, $ROOT);
flock($lockFp, LOCK_UN);
fclose($lockFp);
$check('nightly: při držení zámku storage/.nightly.lock se druhý běh odmítne (locked)', $n4 === 2 && str_contains($nerr4, 'locked'));
[$n5, $nout5] = v67o_run($php, [$nightly], $allow, $ROOT);
$snapRun1 = v67o_snapshot($tmp, ['v67_nightly.json.php']);
$check('nightly: s povolením, čerstvou zálohou a zámkem proběhne (V67_NIGHTLY_OK) a vypíše jen počty', $n5 === 0 && preg_match('/^V67_NIGHTLY_OK students=(\d+) evidence_added=(\d+) mastery=(\d+) badges=(\d+)$/m', $nout5, $nm) === 1 && (int)$nm[1] >= 3 && !str_contains($nout5, 'Audit'));
[$n6, $nout6] = v67o_run($php, [$nightly], $allow, $ROOT);
$check('nightly: druhý běh je idempotentní (0 nových důkazů, 0 odznaků, žádná změna dat)', $n6 === 0 && preg_match('/evidence_added=0 mastery=\d+ badges=0$/m', $nout6) === 1 && v67o_snapshot($tmp, ['v67_nightly.json.php']) === $snapRun1);
$stateRow = storage_read($tmp . '/v67_nightly.json.php', false)['last'] ?? [];
$check('nightly: poslední běh je zapsaný do stavu (čte ho health), bez jmen', isset($stateRow['at'], $stateRow['students']) && !str_contains(json_encode($stateRow), 'Audit'));

// ---------------------------------------------------------------- 4) rollback migrací
$mDefs = migrate_load_all(dirname(__DIR__) . '/migrations');
$noDown = ['id' => 'y', 'up' => static fn(): array => []];
$withDown = ['id' => 'x', 'up' => static fn(): array => [], 'down' => static fn(bool $d): array => []];
$check('rollback: neznámá migrace = unknown; neprovedená migrace = not_applied', migrate_rollback_plan($mDefs, 'neexistuje', $nb, $tmp)['mode'] === 'unknown' && migrate_rollback_plan(['y' => $noDown], 'y', $nb, $tmp)['mode'] === 'not_applied');
$mdb = v67o_tmpdir('mig');
$mdb2 = $mdb . '/backups';
mkdir($mdb2);
backup_storage_run(STORAGE_DIR, $mdb2, 14, false, null, true);
sleep(1);
storage_map_update(migrate_log_path(), 'z_test', static fn(?array $c): array => ['id' => 'z_test', 'applied_at' => date(DATE_ATOM), 'description' => 'test']);
$planManual = migrate_rollback_plan(['z_test' => ['id' => 'z_test'] + $noDown], 'z_test', $mdb2, STORAGE_DIR);
$check('rollback: migrace bez down() vrací přesný příkaz obnovy z nejnovější zálohy pořízené PŘED migrací', $planManual['mode'] === 'restore' && is_string($planManual['command']) && str_contains((string)$planManual['command'], 'restore_storage.php --from=' . $mdb2 . '/storage-') && str_ends_with((string)$planManual['command'], '--apply'));
$check('rollback: migrace s funkcí down() se vrací touto funkcí (mode=down)', migrate_rollback_plan(['z_test' => ['id' => 'z_test'] + $withDown], 'z_test', $mdb2, STORAGE_DIR)['mode'] === 'down');
[$re, $rout] = v67o_run($php, [__DIR__ . '/migrate.php', '--rollback=0001_streams_jsonl', '--backup-dir=' . $mdb2], ['EDUCANET_STORAGE_DIR' => $tmp], $ROOT);
$check('rollback: CLI pro provedenou migraci bez down() vypíše MIGRATE_ROLLBACK_MANUAL a nic nezmění', $re === 0 && str_contains($rout, 'MIGRATE_ROLLBACK_MANUAL'));
[$re2, , $rerr2] = v67o_run($php, [__DIR__ . '/migrate.php', '--rollback=neexistuje'], ['EDUCANET_STORAGE_DIR' => $tmp], $ROOT);
$check('rollback: CLI s neznámou migrací končí chybou (MIGRATE_ROLLBACK_FAIL, exit 1)', $re2 === 1 && str_contains($rerr2, 'MIGRATE_ROLLBACK_FAIL'));

// ---------------------------------------------------------------- 5) plán přechodu roku, reporty
$skip = ['v67_nightly.json.php', 'migrations_v58.json.php'];
$planBefore = v67o_snapshot($tmp, $skip);
$plan = rollover67_plan((int)date('Y') + 1);
$check('přechod roku: plán v62–v67 spočítá žáky, důkazy a rozpracované projekty a nic nezapisuje', $plan['students'] >= 3 && $plan['evidence_files'] >= 1 && array_key_exists('open_project_cycles', $plan) && v67o_snapshot($tmp, $skip) === $planBefore);
[$pe, $pout] = v67o_run($php, [__DIR__ . '/v67_rollover_plan.php', '--year=' . ((int)date('Y') + 1)], ['EDUCANET_STORAGE_DIR' => $tmp], $ROOT);
$check('přechod roku: tools/v67_rollover_plan.php končí V67_ROLLOVER_PLAN_OK a nevypisuje jména', $pe === 0 && str_contains($pout, 'V67_ROLLOVER_PLAN_OK') && !str_contains($pout, 'Audit'));
$effBefore = v67o_snapshot($tmp);
[$ee, $eout] = v67o_run($php, [__DIR__ . '/v67_effect_report.php'], ['EDUCANET_STORAGE_DIR' => $tmp], $ROOT);
$check('effect report: nic nezapíše, třídy s méně než 5 žáky potlačí (WARN, exit 2) a nevypisuje jména ani e-maily', $ee === 2 && str_contains($eout, 'V67_EFFECT_REPORT_WARN') && str_contains($eout, 'potlačeno') && !str_contains($eout, 'Audit') && !str_contains($eout, '@') && v67o_snapshot($tmp) === $effBefore);
$bigDir = v67o_tmpdir('eff');
v61_perf_write_roster($bigDir, v61_perf_roster(6));
[$ee2, $eout2] = v67o_run($php, [__DIR__ . '/v67_effect_report.php', '--class=class_3a'], ['EDUCANET_STORAGE_DIR' => $bigDir], $ROOT);
$check('effect report: třída s ≥ 5 žáky se vypočítá (řádek s počtem žáků a procenty), konec V67_EFFECT_REPORT_OK', $ee2 === 0 && preg_match('/^class_3a\s+(\d+)\s+\d+ %/m', $eout2, $em) === 1 && (int)$em[1] >= 5 && str_contains($eout2, 'V67_EFFECT_REPORT_OK'));
$csv = "ucastnik;role;uloha;uspech;cas_s;poznamka\n# komentář;;;;\nZ1;zak;Z-01;1;40;tajná poznámka Jan Novák\nZ2;zak;Z-01;0;90;x\nZ3;zak;Z-01;1;60;x\nU1;ucitel;U-01;1;120;x\n";
$utFile = $mdb . '/ut.csv';
file_put_contents($utFile, $csv);
[$ue, $uout] = v67o_run($php, [__DIR__ . '/v67_ut_summary.php', '--file=' . $utFile], [], $ROOT);
$check('UT souhrn: úspěšnost 67 % a medián 60 s úlohy Z-01, úloha pod 80 % označená do roadmapy, komentáře a poznámky se nevypisují', $ue === 0 && preg_match('/zak Z-01\s+3\s+67%\s+60\s+<- do roadmapy/', $uout) === 1 && !str_contains($uout, 'Novák') && str_contains($uout, 'V67_UT_SUMMARY_OK tasks=2 rows=4'));
[$ue2, $uout2] = v67o_run($php, [__DIR__ . '/v67_ut_summary.php', '--file=' . $ROOT . '/docs/ut_v67_zaznam.csv'], [], $ROOT);
$check('UT souhrn: dodaná šablona CSV je platná a prázdná (EMPTY)', $ue2 === 0 && str_contains($uout2, 'V67_UT_SUMMARY_EMPTY'));

// ---------------------------------------------------------------- 6) vyřazování
$check('vyřazení: seznam RETIRE67_APPROVED je prázdný (soubory se bez souhlasu uživatele nevyřazují)', RETIRE67_APPROVED === []);
// v68: v48_state.php byl vyřazen (tools/v68_retire.php), jako neschválený soubor slouží přehled.
[$rte, , $rterr] = v67o_run($php, [__DIR__ . '/v67_retire.php', '--file=app/views/dashboard.php', '--apply'], [], $ROOT);
$check('vyřazení: --apply na neschválený soubor skončí REFUSED not_approved a soubor existuje', $rte === 2 && str_contains($rterr, 'not_approved') && is_file($ROOT . '/app/views/dashboard.php'));
$rr = v67o_tmpdir('retire');
mkdir($rr . '/app/views', 0777, true);
file_put_contents($rr . '/app/views/stary.php', "<?php // test\n");
$shaOrig = hash_file('sha256', $rr . '/app/views/stary.php');
$dry = retire67_apply($rr, 'app/views/stary.php', ['app/views/stary.php'], false);
$check('vyřazení: dry-run nic nemění (soubor zůstává, kopie nevzniká)', $dry['ok'] && is_file($rr . '/app/views/stary.php') && !is_dir($rr . '/retired'));
$done = retire67_apply($rr, 'app/views/stary.php', ['app/views/stary.php'], true);
$check('vyřazení: kopie do retired/v67/ s ověřením SHA-256 a až pak smazání originálu', $done['ok'] && !is_file($rr . '/app/views/stary.php') && is_file($rr . '/retired/v67/app/views/stary.php') && hash_file('sha256', $rr . '/retired/v67/app/views/stary.php') === $shaOrig && $done['sha'] === $shaOrig);
$bad = [];
foreach (['../x.php', 'storage/a.json.php', 'retired/v67/a.php', 'tools/lib/a.php', '/etc/passwd', 'C:/x.php', 'a' . chr(92) . 'b.php', ''] as $p) {
    if (retire67_apply($rr, $p, [$p], true)['ok']) $bad[] = $p;
}
$check('vyřazení: zakázané cesty (.., storage/, retired/, tools/lib/, absolutní, zpětné lomítko, prázdná) se odmítnou i když jsou „schválené“' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
[$dce, $dcout] = v67o_run($php, [__DIR__ . '/v67_dead_code_report.php'], [], $ROOT);
$check('report kandidátů: jen čte, spočítá kandidáty a končí V67_DEAD_CODE_REPORT_OK candidates=N live=M', $dce === 0 && preg_match('/^V67_DEAD_CODE_REPORT_OK candidates=(\d+) live=(\d+)$/m', $dcout, $dm) === 1 && (int)$dm[2] > 50);
// v68: seznam kandidátů vede docs/V68_KANDIDATI_VYRAZENI.md (výsledek reportu v68); historický V67 dokument zůstává beze změny.
[$dc8e, $dc8out] = v67o_run($php, [__DIR__ . '/v68_dead_code_report.php'], [], $ROOT);
$check('report kandidátů: docs/V68_KANDIDATI_VYRAZENI.md existuje a uvádí výsledek reportu v68 (candidates=N)', $dc8e === 0 && preg_match('/^V68_DEAD_CODE_REPORT_OK candidates=(\d+)/m', $dc8out, $dm8) === 1 && is_file($ROOT . '/docs/V68_KANDIDATI_VYRAZENI.md') && str_contains((string)file_get_contents($ROOT . '/docs/V68_KANDIDATI_VYRAZENI.md'), 'candidates=' . $dm8[1]));

// ---------------------------------------------------------------- 7) cron, dokumentace, preflight
$cron = (string)file_get_contents($ROOT . '/docs/deploy/aapanel/educanet-cron.sh.example');
$check('cron: příkaz nightly nejdřív zálohuje a teprve potom spouští přepočet s EDUCANET_NIGHTLY_ALLOW=1', preg_match('/nightly\)\s+run tools\/backup_storage\.php[^\n]*&&\s*EDUCANET_NIGHTLY_ALLOW=1 run tools\/v67_nightly_recompute\.php/', $cron) === 1 && str_contains($cron, '|nightly'));
$cronExample = (string)file_get_contents($ROOT . '/docs/deploy/educanet.cron.example');
$check('cron.example: řádek 01:30 spouští zálohu a poté nightly přepočet', preg_match('/^30 1 \* \* \* .*backup_storage\.php.*&& EDUCANET_NIGHTLY_ALLOW=1 php tools\/v67_nightly_recompute\.php/m', $cronExample) === 1);
$prod = (string)file_get_contents($ROOT . '/docs/NASAZENI_PRODUKCE.md');
$check('dokumentace nasazení: Module already loaded (php --ini), apc.enable_cli, display_errors, session.use_strict_mode, backup_key_init a rollback', str_contains($prod, 'Module already loaded') && str_contains($prod, 'php --ini') && str_contains($prod, 'apc.enable_cli') && str_contains($prod, 'display_errors') && str_contains($prod, 'session.use_strict_mode') && str_contains($prod, 'backup_key_init.php') && str_contains($prod, '--rollback'));
[$pfe, $pfout] = v67o_run($php, [__DIR__ . '/preflight.php'], ['EDUCANET_STORAGE_DIR' => $tmp], $ROOT);
$check('preflight: kontroluje duplicitně načtená rozšíření (řádek „žádné rozšíření PHP není v ini načtené dvakrát“)', str_contains($pfout, 'není v ini načtené dvakrát'));

// ---------------------------------------------------------------- Linux Lab beze změny
$hashes = require __DIR__ . '/lib/v67_lab_hashes.php';
$changed = array_filter(array_keys($hashes), static fn(string $rel): bool => !is_file($ROOT . '/' . $rel) || hash_file('sha256', $ROOT . '/' . $rel) !== $hashes[$rel]);
$check('Linux Lab v57/v58 je beze změny proti snímku na začátku v67', $changed === []);

exit(audit_summary($state, 'V67_OPS'));
