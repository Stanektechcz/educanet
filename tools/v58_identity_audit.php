<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · audit F6 – registr identity (student_id), kolize jmen a přechod školního roku.
 * Běží v izolovaném dočasném úložišti s vymyšlenými žáky (EDUCANET_STORAGE_DIR, EDUCANET_IDENTITY58_DIRECTORY);
 * ostrou storage/ ani skutečný seznam žáků nečte pro kontroly a nic v nich nemění.
 *   php tools/v58_identity_audit.php
 * Konec: V58_IDENTITY_AUDIT_OK checks=N failed=0 (jinak V58_IDENTITY_AUDIT_FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
require_once $root . '/tools/lib/audit_storage.php';
$tmp = edu_audit_temp_storage('v58-identity');
$backupDir = str_replace('\\', '/', sys_get_temp_dir()) . '/educanet-audit-v58idbk-' . bin2hex(random_bytes(5));
mkdir($backupDir, 0700, true);
register_shutdown_function(static function () use ($backupDir): void { edu_audit_remove_dir($backupDir); });

// Vymyšlení žáci (žádná skutečná jména). Dva „Jan Zkušební“ ve 2.A = jmenovci se sdíleným klíčem.
$directory = [
    ['class_id' => 'class_1a', 'first_name' => 'Tereza', 'last_name' => 'Pokusná'],
    ['class_id' => 'class_2a', 'first_name' => 'Eva', 'last_name' => 'Testovská'],
    ['class_id' => 'class_2a', 'first_name' => 'Jan', 'last_name' => 'Zkušební'],
    ['class_id' => 'class_2a', 'first_name' => 'Jan', 'last_name' => 'Zkušební'],
    ['class_id' => 'class_3a', 'first_name' => 'Petr', 'last_name' => 'Vzorový'],
    ['class_id' => 'class_4a', 'first_name' => 'Marek', 'last_name' => 'Ukázkový'],
];
$dirFile = $tmp . '/_identity_directory.json';
file_put_contents($dirFile, json_encode($directory, JSON_UNESCAPED_UNICODE));
putenv('EDUCANET_IDENTITY58_DIRECTORY=' . $dirFile);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require $root . '/bootstrap.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/identity_v58_views.php';
$_SESSION = [];

$checks = 0; $failed = 0;
$check = static function (string $name, bool $ok) use (&$checks, &$failed): void {
    $checks++;
    if (!$ok) $failed++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . "\n";
};
$fresh = static function (): void { clearstatcache(); $GLOBALS['educanet_json_request_cache'] = []; };
$hashTree = static function (string $dir): string {
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) { if ($f->isFile()) $out[str_replace('\\', '/', substr($f->getPathname(), strlen($dir)))] = hash_file('sha256', $f->getPathname()); }
    ksort($out);
    return hash('sha256', json_encode($out));
};
$run = static function (string $script, array $args) use ($root, $tmp, $dirFile): array {
    $cmd = array_merge([PHP_BINARY, $root . '/tools/' . $script], $args);
    $env = getenv();
    $env['EDUCANET_STORAGE_DIR'] = $tmp;
    $env['EDUCANET_IDENTITY58_DIRECTORY'] = $dirFile;
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, $env);
    if (!is_resource($proc)) return [255, ''];
    $out = (string)stream_get_contents($pipes[1]) . (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return [proc_close($proc), $out];
};
$names = ['Tereza', 'Pokusná', 'Testovská', 'Zkušební', 'Vzorový', 'Ukázkový'];
$noNames = static function (string $text) use ($names): bool { foreach ($names as $n) { if (str_contains($text, $n)) return false; } return true; };

// --- Účty (vymyšlené): dva účty jmenovce Jan Zkušební, jeden účet Evy, Marka a Petra; jeden Google účet Evy.
$accounts = [];
$mapRows = [];
foreach ([['eva.testovska', 'Eva Testovská', 'class_2a'], ['jan.zkusebni', 'Jan Zkušební', 'class_2a'], ['jan.zkusebni2', 'Jan Zkušební', 'class_2a'],
          ['petr.vzorovy', 'Petr Vzorový', 'class_3a'], ['marek.ukazkovy', 'Marek Ukázkový', 'class_4a'], ['demo.2a', 'Demo Žák', 'class_2a']] as [$local, $label, $cid]) {
    $email = $local . '@educanet.cz';
    $id = substr(hash('sha256', 'audit|' . $local), 0, 32);
    $accounts[$email] = ['id' => $id, 'email' => $email, 'name' => $label, 'class_id' => $cid, 'student_label' => $label,
        'password_hash' => password_hash('audit-' . $local, PASSWORD_BCRYPT, ['cost' => 4]), 'must_change_password' => false, 'demo_account' => str_starts_with($local, 'demo.')];
    $mapRows['local:' . $id] = ['provider' => 'local', 'email' => $email, 'class_id' => $cid, 'student_label' => $label, 'linked_at' => date(DATE_ATOM)];
}
$mapRows['100200300400'] = ['provider' => 'google', 'email' => 'eva.testovska@educanet.cz', 'class_id' => 'class_2a', 'student_label' => 'Eva Testovská', 'linked_at' => date(DATE_ATOM)];
storage_update(local_accounts_path(), static fn(array $r): array => $accounts);
storage_update(STORAGE_DIR . '/student_accounts.json.php', static fn(array $r): array => $mapRows);
$idOf = static fn(string $local): string => substr(hash('sha256', 'audit|' . $local), 0, 32);

// --- 1. Sestavení registru
$dry = identity58_build(true);
$fresh();
$check('build --dry-run nic nezapíše', !is_file(identity58_path()) && !empty($dry['dry_run']));
$b1 = identity58_build(false);
$fresh();
$reg = identity58_registry();
$check('registr vznikl (5 žáků z adresáře + demo)', count($reg['students']) === 6 && (int)$b1['created'] === 6);
$map = student_account_map();
$check('každý účet v mapě účtů má student_id', count(array_filter($map, static fn($r): bool => !empty($r['student_id']) && isset($reg['students'][$r['student_id']]))) === count($map));
$check('každý lokální účet žáka má student_id', count(array_filter(local_accounts(), static fn($r): bool => !empty($r['student_id']))) === count($accounts));
$hashBefore = $hashTree($tmp);
$b2 = identity58_build(false);
$fresh();
$check('druhé sestavení nic nezmění (idempotence)', empty($b2['changed']) && (int)$b2['created'] === 0 && (int)$b2['map_patches'] === 0 && (int)$b2['local_patches'] === 0);
$reg2 = identity58_registry();
$check('registr po druhém sestavení shodný', identity58_same($reg, $reg2));
$check('identity58_ensure bez změny zdrojů nic nedělá', identity58_ensure() === false);

// --- 2. Aliasy
$eva = identity58_id_for_student('class_2a', 'Eva Testovská');
$check('student_id má tvar stu_<16 hex>', is_string($eva) && preg_match('/^stu_[0-9a-f]{16}$/', $eva) === 1);
$evaAliases = [project_student_key('class_2a', 'Eva Testovská'), 'class_2a:s:' . identity58_h24('class_2a', 'Eva Testovská'),
    'local:' . $idOf('eva.testovska'), 'email:eva.testovska@educanet.cz', 'google:100200300400', 'dir:class_2a|evatestovska',
    'lab:class_2a__' . sha1(project_student_key('class_2a', 'Eva Testovská')), 'class_2a:n:' . substr(hash('sha256', strtolower('Eva Testovská')), 0, 20)];
$check('všechny aliasy (projekt, profil, účet, e-mail, Google, adresář, lab) → stejné ID', count(array_unique(array_map('identity58_id_for_alias', $evaAliases))) === 1 && identity58_id_for_alias($evaAliases[0]) === $eva);
$check('Google sub bez prefixu se najde', identity58_id_for_alias('100200300400') === $eva);
$check('identity58_aliases vrací seznam aliasů', count(array_intersect($evaAliases, identity58_aliases((string)$eva))) === count($evaAliases));
$check('soubor labu = sha1(project_student_key)', basename(lab57_state_path_like('class_2a', project_student_key('class_2a', 'Eva Testovská'))) === substr($evaAliases[6], 4) . '.json.php');
$_SESSION = ['local_user' => local_account_public($accounts['eva.testovska@educanet.cz']), 'next_class_id' => 'class_2a', 'student_label' => 'Eva Testovská'];
$check('identity58_current_student_id ze session (účet)', identity58_current_student_id() === $eva);
$_SESSION = ['next_class_id' => 'class_2a', 'student_label' => 'Eva Testovská'];
$check('identity58_current_student_id ze session (třída + jméno)', identity58_current_student_id() === $eva);
$_SESSION = [];
$check('neznámý alias → null', identity58_id_for_alias('class_2a:student:000000000000000000000000') === null);

// --- 3. Kolize jmen
$dups = identity58_duplicates();
$types = array_column($dups, 'type');
$check('jmenovci ve stejné třídě nahlášeni', in_array('directory_namesake', $types, true));
$check('dva účty jednoho klíče nahlášeny', in_array('shared_account', $types, true));
$jan = identity58_id_for_student('class_2a', 'Jan Zkušební');
$split = identity58_split((string)$jan, 'Jan Zkušební (B)', 'local:' . $idOf('jan.zkusebni2'), false);
$fresh();
$janB = (string)($split['new_id'] ?? '');
$check('split: nový žák s novým ID', !empty($split['ok']) && $janB !== '' && $janB !== $jan);
$check('split: účet jmenovce vede na nové ID', identity58_id_for_alias('local:' . $idOf('jan.zkusebni2')) === $janB && (student_account_map()['local:' . $idOf('jan.zkusebni2')]['student_label'] ?? '') === 'Jan Zkušební (B)');
$check('split: původní účet a klíče zůstávají původnímu ID', identity58_id_for_alias('local:' . $idOf('jan.zkusebni')) === $jan && identity58_id_for_student('class_2a', 'Jan Zkušební') === $jan);
$labels = array_map(static fn(array $r): string => trim($r['first_name'] . ' ' . $r['last_name']), identity58_directory_rows());
$check('split: seznam třídy má jednoho Jana a jednoho Jana (B)', count(array_keys($labels, 'Jan Zkušební', true)) === 1 && in_array('Jan Zkušební (B)', $labels, true));
$types = array_column(identity58_duplicates(), 'type');
$check('split: kolize jmenovců vyřešena', !in_array('directory_namesake', $types, true) && !in_array('shared_account', $types, true));
$check('split: stejné jméno odmítnuto', empty(identity58_split((string)$jan, 'Jan Zkušební', '', true)['ok']));

// --- 4. Data pro přechod roku (XP, odznaky, kurz, lab)
$evaOld = 'class_2a:s:' . identity58_h24('class_2a', 'Eva Testovská');
storage_update(STORAGE_DIR . '/learning_profiles.json.php', static fn(array $a): array => [
    $evaOld => ['xp' => 140, 'events' => ['v57:solve:ls-1' => ['xp' => 20, 'at' => '2026-09-10T10:00:00+02:00'], 'test_complete' => ['xp' => 30, 'at' => '2026-09-11T10:00:00+02:00']],
        'kb' => ['dns' => ['read' => true]], 'studio' => [], 'journey' => ['intro' => true], 'badges' => ['level_10' => ['earned_at' => '2026-09-12']], 'achievements' => ['first' => true], 'updated_at' => '2026-09-12'],
]);
$labOld = STORAGE_DIR . '/linux_v57/class_2a__' . sha1(project_student_key('class_2a', 'Eva Testovská')) . '.json.php';
storage_update($labOld, static fn(array $d): array => ['v' => 1, 'solved' => ['practice' => ['ls-1' => ['points' => 20]], 'race:abc' => ['ls-2' => ['points' => 5]]],
    'inst' => ['practice:ls-3' => ['seed' => 1]], 'log' => [['cmd' => 'ls']], 'stats' => ['cmds' => 12]]);
$fresh();
$oldProfileHash = hash('sha256', (string)json_encode(load_php_json(STORAGE_DIR . '/learning_profiles.json.php')[$evaOld]));
$oldLabHash = hash_file('sha256', $labOld);

// --- 5. Přechod roku: --dry-run
$before = $hashTree($tmp);
[$code, $out] = $run('v58_rollover.php', ['--dry-run', '--year=2027', '--no-names', '--backup-dir=' . $backupDir]);
$fresh();
$check('rollover --dry-run skončí OK', $code === 0 && str_contains($out, 'ROLLOVER_DRY_RUN_OK'));
$check('rollover --dry-run vypíše plán (přesuny, archivace, kódy)', str_contains($out, 'přesun class_2a→class_3a: 3') && str_contains($out, 'archivace absolventů: 1') && str_contains($out, 'nové kódy tříd: 4'));
$check('rollover --dry-run bez jmen ve výpisu', $noNames($out));
$check('rollover --dry-run nic nezmění', $hashTree($tmp) === $before);
[$code, $out] = $run('v58_rollover.php', ['--dry-run', '--apply']);
$check('rollover odmítne --dry-run a --apply současně', $code === 1);

// --- 6. --apply bez zálohy odmítne
[$code, $out] = $run('v58_rollover.php', ['--apply', '--year=2027', '--no-names', '--backup-dir=' . $backupDir, '--allow-missing-integration']);
$fresh();
$check('rollover --apply bez zálohy odmítne (exit 2)', $code === 2 && str_contains($out, 'ROLLOVER_REFUSED'));
$check('odmítnutý --apply nic nezmění', $hashTree($tmp) === $before);
$old = identity58_backup_check($backupDir, IDENTITY58_BACKUP_MAX_AGE, time() + 7200);
$check('záloha starší než 1 h se nepočítá', !$old['ok']);
[$code, $out] = $run('v58_rollover.php', ['--apply', '--year=2027', '--no-names', '--backup-dir=' . $backupDir]);
$check('rollover --apply bez háčků aplikace odmítne', $code === 2);

// --- 7. --apply se zálohou
[$bcode, $bout] = $run('backup_storage.php', ['--src=' . $tmp, '--dest=' . $backupDir, '--quiet']);
$check('záloha vytvořena (tools/backup_storage.php)', $bcode === 0 && identity58_backup_check($backupDir)['ok']);
$preCodes = array_map(static fn(array $m): string => strtoupper((string)($m['code'] ?? '')), $modules);
[$code, $out] = $run('v58_rollover.php', ['--apply', '--year=2027', '--no-names', '--backup-dir=' . $backupDir, '--allow-missing-integration']);
$fresh();
$check('rollover --apply provede přechod', $code === 0 && str_contains($out, 'ROLLOVER_APPLY_OK year=2027'));
$check('rollover --apply bez jmen ve výpisu', $noNames($out));
$reg = identity58_registry();
$marek = identity58_id_for_alias('local:' . $idOf('marek.ukazkovy'));
$check('4.A archivována', ($reg['students'][$marek]['status'] ?? '') === 'archived');
$marekAcc = local_accounts()['marek.ukazkovy@educanet.cz'];
$check('absolvent: přihlášení zablokováno (identity58_login_gate)', !empty($marekAcc['login_blocked']) && identity58_login_gate($marekAcc) === IDENTITY58_MSG_BLOCKED);
$check('absolvent: mapa účtů neobnoví třídu', !identity58_assignment_allowed(student_account_map()['local:' . $idOf('marek.ukazkovy')]));
$check('aktivní žák smí obnovit třídu', identity58_assignment_allowed(student_account_map()['local:' . $idOf('eva.testovska')]) && identity58_login_gate(local_accounts()['eva.testovska@educanet.cz']) === null);
$check('2.A → 3.A (registr, mapa, účet)', ($reg['students'][$eva]['class_id'] ?? '') === 'class_3a' && student_account_map()['100200300400']['class_id'] === 'class_3a' && local_accounts()['eva.testovska@educanet.cz']['class_id'] === 'class_3a');
$petr = identity58_id_for_alias('local:' . $idOf('petr.vzorovy'));
$tereza = identity58_id_for_alias('dir:class_2a|terezapokusna');
$check('3.A → 4.A a 1.A → 2.A', ($reg['students'][$petr]['class_id'] ?? '') === 'class_4a' && is_string($tereza) && ($reg['students'][$tereza]['class_id'] ?? '') === 'class_2a');
$demo = identity58_id_for_alias('local:' . $idOf('demo.2a'));
$check('demo účet beze změny', ($reg['students'][$demo]['class_id'] ?? '') === 'class_2a');
$evaNew = 'class_3a:s:' . identity58_h24('class_3a', 'Eva Testovská');
$check('nové klíče i staré aliasy → stejné ID', identity58_id_for_alias($evaNew) === $eva && identity58_id_for_alias(project_student_key('class_3a', 'Eva Testovská')) === $eva && identity58_id_for_alias($evaOld) === $eva);
$profiles = load_php_json(STORAGE_DIR . '/learning_profiles.json.php');
$np = $profiles[$evaNew] ?? [];
$check('XP a odznaky přeneseny do nového klíče', (int)($np['xp'] ?? 0) === 140 && isset($np['badges']['level_10']) && isset($np['achievements']['first']));
$check('kurz archivován (kb/journey a události testu se nepřenáší, lab ano)', empty($np['kb']) && empty($np['journey']) && !isset($np['events']['test_complete']) && isset($np['events']['v57:solve:ls-1']));
$check('historický profil 2.A nezměněn', hash('sha256', (string)json_encode($profiles[$evaOld] ?? [])) === $oldProfileHash);
$labNew = STORAGE_DIR . '/linux_v57/class_3a__' . sha1(project_student_key('class_3a', 'Eva Testovská')) . '.json.php';
$ln = load_php_json($labNew);
$check('lab: vyřešené úrovně přeneseny, instance a závody ne', isset($ln['solved']['practice']['ls-1']) && !isset($ln['solved']['race:abc']) && empty($ln['inst']) && (int)($ln['stats']['cmds'] ?? 0) === 12);
$check('historický soubor labu nezměněn', hash_file('sha256', $labOld) === $oldLabHash);
$codes = identity58_class_codes();
$check('nové kódy tříd (4, jiné než staré)', count($codes) === 4 && !array_intersect(array_values($codes), array_values($preCodes)) && identity58_class_code('class_2a', 'X') === $codes['class_2a']);
$rows = identity58_directory_rows();
$byLabel = [];
foreach ($rows as $r) $byLabel[trim($r['first_name'] . ' ' . $r['last_name'])] = (string)$r['class_id'];
$check('seznam tříd: přesun podle registru, absolvent zmizel', ($byLabel['Eva Testovská'] ?? '') === 'class_3a' && ($byLabel['Tereza Pokusná'] ?? '') === 'class_2a' && !isset($byLabel['Marek Ukázkový']));
$entry = (array)($reg['rollovers']['2027'] ?? []);
$check('záznam přechodu bez jmen', ($entry['status'] ?? '') === 'done' && $noNames((string)json_encode($entry, JSON_UNESCAPED_UNICODE)));

// --- 8. Druhé spuštění nic nezmění
$after = $hashTree($tmp);
[$bcode] = $run('backup_storage.php', ['--src=' . $tmp, '--dest=' . $backupDir, '--quiet']);
[$code, $out] = $run('v58_rollover.php', ['--apply', '--year=2027', '--no-names', '--backup-dir=' . $backupDir, '--allow-missing-integration']);
$fresh();
$check('druhé --apply: už provedeno, nic se nezmění', $code === 0 && str_contains($out, 'ROLLOVER_ALREADY_APPLIED') && $hashTree($tmp) === $after);
[$code, $out] = $run('v58_rollover.php', ['--dry-run', '--year=2026', '--no-names']);
$check('starší rok je blokován', str_contains($out, 'BLOKUJE'));

// --- 9. Jmenovci přes roky: nový žák se jménem absolventa/přesunutého žáka
$GLOBALS['identity58_directory_override'] = array_merge($directory, [['class_id' => 'class_2a', 'first_name' => 'Eva', 'last_name' => 'Testovská']]);
identity58_build(false);
$fresh();
$newEva = null;
foreach (identity58_registry()['students'] as $sid => $s) { if ($sid !== $eva && ($s['label'] ?? '') === 'Eva Testovská') $newEva = $sid; }
$check('nový jmenovec ve 2.A = nový žák (nové ID)', is_string($newEva));
$check('sdílený klíč s loňskou žákyní nahlášen', in_array('alias_conflict', array_column(identity58_duplicates(), 'type'), true));
unset($GLOBALS['identity58_directory_override']);

// --- 10. Plán: jmenovec v cílové třídě blokuje; relabel kolizi vyřeší
$synthetic = identity58_compute(identity58_empty(), [['class_id' => 'class_2a', 'first_name' => 'Olga', 'last_name' => 'Pokusná'], ['class_id' => 'class_3a', 'first_name' => 'Olga', 'last_name' => 'Pokusná']], [], [], time());
$plan = identity58_rollover_plan(2030, [], $synthetic['registry'], []);
$check('plán: jmenovec v cílové třídě = kolize a blokace', count($plan['conflicts']) === 1 && $plan['blockers'] !== []);
$rel = identity58_relabel((string)$tereza, 'Tereza Pokusná (B)', false);
$fresh();
$check('relabel: nové jméno → stejné ID, účty a seznam třídy', !empty($rel['ok']) && identity58_id_for_student('class_2a', 'Tereza Pokusná (B)') === $tereza
    && in_array('Tereza Pokusná (B)', array_map(static fn(array $r): string => trim($r['first_name'] . ' ' . $r['last_name']), identity58_directory_rows()), true));

// --- 11. Učitelská záložka (jen náhled)
$_SESSION = ['csrf' => 'audit-token'];
$_GET = ['tab' => 'identita', 'plan' => '2028'];
ob_start();
identity58_render_teacher_tab('audit-token');
$html = (string)ob_get_clean();
$check('záložka: registr, kolize a plán', str_contains($html, 'Identita a nový školní rok') && str_contains($html, 'Plán pro školní rok 2028/2029') && str_contains($html, 'name="csrf" value="audit-token"'));
$check('záložka: bez tlačítka --apply a bez skriptů', !str_contains($html, '<script') && !str_contains($html, 'name="action" value="identity58_apply'));
$_GET = [];

// --- 12. Statické kontroly souborů F6
$files = ['identity_v58.php', 'identity_v58_rollover.php', 'identity_v58_views.php', 'tools/v58_identity.php', 'tools/v58_rollover.php', 'tools/v58_identity_audit.php'];
$forbidden = '/\b(exec|shell_exec|system|passthru|popen|pcntl_exec|eval|assert|create_function|fsockopen|stream_socket_client|curl_init|mail)\s*\(/';
$bad = [];
foreach ($files as $f) {
    $src = (string)file_get_contents($root . '/' . $f);
    if (preg_match($forbidden, $src) === 1) $bad[] = $f;
    if (count(file($root . '/' . $f)) >= 800) $bad[] = $f . ' (>= 800 řádků)';
    if (!str_contains($src, 'declare(strict_types=1);')) $bad[] = $f . ' (strict_types)';
}
$check('soubory F6: strict_types, < 800 řádků, bez spouštění příkazů a sítě', $bad === []);
$libs = ['identity_v58.php', 'identity_v58_rollover.php', 'identity_v58_views.php'];
$check('knihovny mají guard proti přímému spuštění', count(array_filter($libs, static fn(string $f): bool => str_contains((string)file_get_contents($root . '/' . $f), "basename((string)(\$_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)"))) === 3);
$check('CLI nástroje mají CLI guard', str_contains((string)file_get_contents($root . '/tools/v58_identity.php'), "PHP_SAPI !== 'cli'") && str_contains((string)file_get_contents($root . '/tools/v58_rollover.php'), "PHP_SAPI !== 'cli'"));
$views = (string)file_get_contents($root . '/identity_v58_views.php');
preg_match_all('/<\?=\s*([^;?]+)/', $views, $m);
$raw = array_filter($m[1], static fn(string $x): bool => !preg_match('/^(e\(|\(int\)|count\()/', trim($x)));
$check('šablona vypisuje jen přes e() nebo čísla', $raw === []);

echo ($failed === 0 ? 'V58_IDENTITY_AUDIT_OK' : 'V58_IDENTITY_AUDIT_FAIL') . " checks=$checks failed=$failed\n";
exit($failed === 0 ? 0 : 1);

/** Stejný vzorec jako lab57_state_path() (linux_v57_lab.php) – audit nenačítá celý simulátor. */
function lab57_state_path_like(string $classId, string $studentKey): string
{
    return STORAGE_DIR . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', $classId) . '__' . sha1($studentKey) . '.json.php';
}
