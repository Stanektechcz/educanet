<?php

declare(strict_types=1);

/**
 * EDUCANET v62 · behaviorální audit vrstvy „kompetence a důkazy“.
 *   php tools/v62_competency_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte ani nezapisuje ostrou storage/. Fiktivní žáci „Audit …“.
 * Kontroly: katalog, adaptér pro každý typ zdroje, deterministický výpočet, hra ≠ zvládnuto/upevněno, hranice 6/7/8 dní,
 * časový útlum, append-only + deduplikace + validace, idempotence backfillu (CLI), pilot jen 3.A (flag), retence
 * (30 dní po left/archived), token-sken invariantu labu, WARN pro nenamapované aktivity, politiky učitele (deny-by-default),
 * zdrojová data beze změny (SHA-256), zobrazení žáka a učitele.
 * Konec: V62_COMPETENCY_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v62-competency')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php',
    'mastery_v62.php', 'competency_v62_views.php', 'competency_v62_teacher_views.php', 'profile_v60.php', 'ops_v58.php', 'app/lib.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

$day = 86400;
$NOW = 1_790_000_000;
$subject = 'os_site';
$ids = array_keys(comp62_competencies($subject));
$row = static fn(string $c, string $src, float $score, int $daysAgo, string $ref = '') => ['competency' => $c, 'source' => $src, 'score' => $score, 'at' => date(DATE_ATOM, $NOW - $daysAgo * 86400),
    'k' => sha1($c . $src . $daysAgo . $ref), 'level' => 2, 'artefact_ref' => 'x:' . $ref];
$state1 = static fn(array $rows): string => (string)m62_compute($rows, $NOW, ['c1'])['c1']['state'];

// --- 1) Katalog -------------------------------------------------------------------------------------------
$comps = comp62_competencies($subject);
$tagsOk = true;
foreach ($comps as $c) foreach ($c['tags'] as $t) if (preg_match('/^(cmd|pack|bank|topic|tg):[a-z0-9\-]+$/', $t) !== 1) $tagsOk = false;
$check('katalog: 8–12 kompetencí, každá „Umím …“, úroveň 1–4, tagy ve tvaru cmd:/pack:/bank:/topic:/tg:, id platná pro důkaz',
    count($comps) >= 8 && count($comps) <= 12 && $tagsOk && array_reduce($comps, static fn(bool $ok, array $c): bool => $ok && str_starts_with($c['label'], 'Umím ') && $c['level'] >= 1 && $c['level'] <= 4 && $c['tags'] !== [] && preg_match(EV62_COMP_RE, $c['id']) === 1, true));
$check('katalog: pilot jen 3.A, předmět třídy nalezen, 1.A mimo katalog', COMP62_PILOT_CLASSES === ['class_3a'] && comp62_subject_for_class('class_3a') === $subject && comp62_subject_for_class('class_1a') === null && !comp62_enabled_for_class('class_1a'));
$msgids = comp62_label_msgids();
$en = require $root . '/lang/en/ui/competency.php';
$uk = require $root . '/lang/uk/ui/competency.php';
$labelsMatch = true;
foreach ($comps as $id => $c) if (($msgids[$id] ?? null) !== $c['label'] || ($en[$c['label']] ?? '') === '' || ($uk[$c['label']] ?? '') === '') $labelsMatch = false;
$check('katalog: každý název kompetence má žákovský msgid shodný s katalogem a překlad en i uk', $labelsMatch && count($msgids) === count($comps));
$check('katalog: shoda tagů je deterministická (stejný vstup = stejný výsledek, max 2 kompetence) a výjimka má přednost',
    comp62_match($subject, ['cmd:dig', 'topic:dns']) === ['net_dns_dhcp'] && comp62_match($subject, ['cmd:dig', 'topic:dns']) === comp62_match($subject, ['topic:dns', 'cmd:dig']) && count(comp62_match($subject, array_merge(...array_column($comps, 'tags')))) <= COMP62_MATCH_LIMIT && comp62_match($subject, ['pack:neexistuje']) === []);

// --- 2) Fixture a adaptéry --------------------------------------------------------------------------------
$fx = v62fx_seed($tmp, time());
$ctxOf = static fn(string $who): array => (array)ev62_student_context(V62FX_CLASS, v62fx_key($who));
$ctxGood = $ctxOf('good');
$check('fixture: všichni 4 fiktivní žáci mají student_id v identitě', ev62_valid_id((string)($ctxGood['id'] ?? '')) && ev62_valid_id((string)($ctxOf('player')['id'] ?? '')) && ev62_valid_id((string)($ctxOf('none')['id'] ?? '')) && ev62_valid_id((string)($ctxOf('left')['id'] ?? '')));
$sourcesSeen = [];
$adapterHits = [];
foreach (ev62_adapters() as $name => $adapter) {
    $got = (array)($adapter['collect'])($ctxGood);
    $adapterHits[$name] = count($got);
    foreach ($got as $cand) $sourcesSeen[(string)$cand['source']] = true;
}
$check('adaptéry: každý z 8 adaptérů vrátí pro fixture žáka aspoň jednoho kandidáta (' . json_encode($adapterHits) . ')', count($adapterHits) === 8 && !in_array(0, $adapterHits, true));
$check('adaptéry: pokryty všechny typy zdrojů (test, project, lab, game, arena, lesson)', array_diff(EV62_SOURCES, array_keys($sourcesSeen)) === []);
$incident = array_values(array_filter((array)(ev62_adapters()['arena_v58']['collect'])($ctxGood), static fn(array $c): bool => str_contains((string)$c['ref'], 'incident')));
$check('adaptéry: incidenty v58 mají typ zdroje lab (rozhodnutí školy), týdenní hádanka a CTF aréna', $incident !== [] && $incident[0]['source'] === 'lab'
    && array_unique(array_column(array_filter((array)(ev62_adapters()['arena_v58']['collect'])($ctxGood), static fn(array $c): bool => !str_contains((string)$c['ref'], 'incident')), 'source')) === ['arena']);
$srcFiles = [STORAGE_DIR . '/progress_v56.json.php', STORAGE_DIR . '/project_grades.json.php', STORAGE_DIR . '/arena_v60_challenges.json.php', STORAGE_DIR . '/teamgames_v58_index.json.php',
    STORAGE_DIR . '/teamgames_v58/aabbccdd11.json.php', lab57_state_path(V62FX_CLASS, v62fx_key('good')), lab57_state_path(V62FX_CLASS, v62fx_key('player'))];
foreach (glob(STORAGE_DIR . '/{practice_results,lab_results}/*.jsonl.php', GLOB_BRACE) ?: [] as $f) $srcFiles[] = $f;
$hashes = static fn(): array => array_map(static fn(string $f): string => is_file($f) ? hash_file('sha256', $f) : 'missing', $srcFiles);
$before = $hashes();
$check('fixture: zdrojové soubory existují (stavy labu, lekce, proudy, souboje, týmové hry, projekty)', !in_array('missing', $before, true) && count($srcFiles) >= 9);

// --- 3) Synchronizace, WARN pro nenamapované, idempotence ------------------------------------------------
$sync = ev62_sync_student(V62FX_CLASS, v62fx_key('good'), true, false, $NOW);
$check('sync: žák Dobrý dostane důkazy (added > 0, neplatných 0)', ($sync['added'] ?? 0) > 0 && ($sync['invalid'] ?? 1) === 0);
$sync2 = ev62_sync_student(V62FX_CLASS, v62fx_key('good'), true, false, $NOW);
$check('sync: opakovaná synchronizace je idempotentní (0 nových, vše duplicity)', ($sync2['added'] ?? 1) === 0 && ($sync2['duplicates'] ?? 0) === ($sync['added'] ?? -1));
$check('sync: TTL – druhé volání bez force do 10 minut se přeskočí (fresh), po 11 minutách proběhne', (ev62_sync_student(V62FX_CLASS, v62fx_key('good'), false, false, $NOW)['skipped'] ?? '') === 'fresh' && !isset(ev62_sync_student(V62FX_CLASS, v62fx_key('good'), false, false, time() + 660)['skipped']));
$unmapped = (int)($sync['unmapped'] ?? 0);
echo ($unmapped > 0 ? 'WARN  ' : 'INFO  ') . 'nenamapované aktivity: ' . $unmapped . ' (projekty v MVP bez tagů se do kompetencí nepřiřazují)' . PHP_EOL;
$check('WARN, ne FAIL: nenamapovaná aktivita (projekt bez tagů) nevytvoří důkaz, jen se započítá do statistiky', $unmapped >= 1 && !in_array('project', array_column(ev62_read((string)$ctxGood['id']), 'source'), true));
ev62_sync_student(V62FX_CLASS, v62fx_key('player'), true, false, $NOW);
ev62_sync_student(V62FX_CLASS, v62fx_key('left'), true, false, $NOW);
$check('zdroje: po synchronizaci a výpočtu jsou zdrojová data bajt po bajtu beze změny (SHA-256)', $hashes() === $before);

// --- 4) Výpočet zvládnutí -----------------------------------------------------------------------------------
$a = [$row('c1', 'test', 0.9, 20, 'a'), $row('c1', 'lab', 0.8, 3, 'b')];
$check('výpočet: deterministický – stejný vstup i jiné pořadí řádků dá stejný výsledek', m62_compute($a, $NOW, ['c1']) === m62_compute($a, $NOW, ['c1']) && m62_compute($a, $NOW, ['c1']) === m62_compute(array_reverse($a), $NOW, ['c1']));
$gameRows = [];
for ($i = 0; $i < 10; $i++) $gameRows[] = $row('c1', $i % 2 ? 'game' : 'arena', 1.0, $i * 9, 'g' . $i);
$check('hra: sama (i 10× aréna/hra se skóre 1,0 v odstupu týdnů) nikdy nedá zvládnuto ani upevněno', $state1($gameRows) === 'rozpracovano' && $state1([$row('c1', 'game', 1.0, 1, 'x')]) === 'rozpracovano');
$check('hra: hra + aréna se skóre 1,0 bez ne-hry se nepočítá za dva různé typy pro upevnění', $state1([$row('c1', 'game', 1.0, 30, 'x'), $row('c1', 'arena', 1.0, 1, 'y')]) === 'rozpracovano');
$check('stav: bez důkazů neověřeno; skóre 0,69 rozpracováno; 0,70 zvládnuto; Σ vah < 1 (jen lekce 0,3) rozpracováno',
    $state1([]) === 'neovereno' && $state1([$row('c1', 'test', 0.69, 1, 'x')]) === 'rozpracovano' && $state1([$row('c1', 'test', 0.70, 1, 'x')]) === 'zvladnuto' && $state1([$row('c1', 'lesson', 1.0, 1, 'x')]) === 'rozpracovano');
$check('hranice upevnění: odstup 6 dní = zvládnuto, přesně 7 dní = upevněno, 8 dní = upevněno',
    $state1([$row('c1', 'test', 0.9, 7, 'a'), $row('c1', 'lab', 0.9, 1, 'b')]) === 'zvladnuto' && $state1([$row('c1', 'test', 0.9, 8, 'a'), $row('c1', 'lab', 0.9, 1, 'b')]) === 'upevneno'
    && $state1([$row('c1', 'test', 0.9, 7, 'a'), $row('c1', 'lab', 0.9, 0, 'b')]) === 'upevneno');
$check('upevnění vyžaduje dva RŮZNÉ typy zdrojů: dva testy v odstupu 30 dní = jen zvládnuto; skóre pod 0,6 se nepočítá',
    $state1([$row('c1', 'test', 0.9, 30, 'a'), $row('c1', 'test', 0.9, 1, 'b')]) === 'zvladnuto' && $state1([$row('c1', 'test', 0.9, 1, 'a'), $row('c1', 'lab', 0.5, 20, 'b'), $row('c1', 'test', 0.95, 2, 'c')]) === 'zvladnuto');
$decay = m62_compute([$row('c1', 'test', 1.0, 120, 'old'), $row('c1', 'test', 0.0, 0, 'new')], $NOW, ['c1'])['c1'];
$check('útlum: poločas 60 dní – důkaz starý 120 dní má váhu 0,25 → skóre 0,2 (ne 0,5)', abs((float)$decay['score'] - 0.2) < 0.001);
$many = [];
for ($i = 0; $i < 12; $i++) $many[] = $row('c1', 'test', $i < 4 ? 0.0 : 1.0, 12 - $i, 'm' . $i);
$check('posledních 8: starší čtyři nulové důkazy se do výpočtu nezapočtou', (int)m62_compute($many, $NOW, ['c1'])['c1']['n'] === M62_LAST_N && (float)m62_compute($many, $NOW, ['c1'])['c1']['score'] === 1.0);
$mGood = m62_student(V62FX_CLASS, v62fx_key('good'), $NOW)['map'];
$mPlayer = m62_student(V62FX_CLASS, v62fx_key('player'), $NOW)['map'];
$mNone = m62_student(V62FX_CLASS, v62fx_key('none'), $NOW)['map'];
$states = static fn(array $m): array => array_column($m, 'state');
$check('fixture: Dobrý má zvládnuté/upevněné kompetence, Hráč (jen hry a aréna) žádné a aspoň jednu rozpracovanou, Nikdo vše neověřeno',
    array_intersect($states($mGood), ['zvladnuto', 'upevneno']) !== [] && array_intersect($states($mPlayer), ['zvladnuto', 'upevneno']) === [] && in_array('rozpracovano', $states($mPlayer), true) && array_unique($states($mNone)) === ['neovereno']);
$class = m62_class(V62FX_CLASS, $NOW);
$cacheFile = m62_cache_path('_class_' . V62FX_CLASS);
$sig1 = (string)($class['sig'] ?? '');
ev62_append((string)$ctxGood['id'], [['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'test', 'score' => 0.8, 'at' => date(DATE_ATOM, $NOW - $day), 'artefact_ref' => 'test:fxnew:ssh']], $NOW);
$check('cache třídy: je v souboru _class_<třída> bez jmen a po změně důkazů se podpis změní (přepočet)', is_file($cacheFile) && !str_contains((string)file_get_contents($cacheFile), 'Audit') && (string)m62_class(V62FX_CLASS, $NOW)['sig'] !== $sig1);

// --- 5) Append-only, validace, žádný volný text -------------------------------------------------------------
$beforeRaw = array_map(static fn(array $r): string => json_encode($r), ev62_read((string)$ctxGood['id']));
$bad = [['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'test', 'score' => 1.5, 'at' => date(DATE_ATOM), 'artefact_ref' => 'test:a'],
    ['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'hack', 'score' => 0.5, 'at' => date(DATE_ATOM), 'artefact_ref' => 'test:a'],
    ['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'test', 'score' => 0.5, 'at' => date(DATE_ATOM), 'artefact_ref' => 'test: volný text se mezerou'],
    ['competency' => 'lnx_ssh', 'level' => 9, 'source' => 'test', 'score' => 0.5, 'at' => date(DATE_ATOM), 'artefact_ref' => 'test:a'],
    ['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'test', 'score' => 0.5, 'at' => 'včera', 'artefact_ref' => 'test:a']];
$res = ev62_append((string)$ctxGood['id'], $bad);
$afterRaw = array_map(static fn(array $r): string => json_encode($r), ev62_read((string)$ctxGood['id']));
$check('append-only: neplatná pole (skóre, zdroj, volný text v ref, úroveň, čas) se odmítnou, existující řádky zůstanou beze změny', $res['invalid'] === 5 && $res['added'] === 0 && $afterRaw === $beforeRaw);
$dup = ev62_append((string)$ctxGood['id'], [['competency' => 'lnx_ssh', 'level' => 2, 'source' => 'test', 'score' => 0.8, 'at' => date(DATE_ATOM, $NOW - $day), 'artefact_ref' => 'test:fxnew:ssh']], $NOW);
$check('append-only: shodný záznam (source|ref|kompetence|čas) se nepřidá podruhé a pořadí řádků se nezmění', $dup['duplicates'] === 1 && $dup['added'] === 0 && array_slice(array_map(static fn(array $r): string => json_encode($r), ev62_read((string)$ctxGood['id'])), 0, count($beforeRaw)) === $beforeRaw);
$rawFile = (string)file_get_contents(ev62_path((string)$ctxGood['id']));
$check('úložiště důkazů: soubor má ochranný první řádek, ve tvaru nejsou jména ani e-maily a řádek nese jen povolená pole', str_starts_with($rawFile, "<?php http_response_code(403); exit; ?>\n") && !str_contains($rawFile, 'Audit') && !str_contains($rawFile, '@')
    && array_diff(array_keys(ev62_read((string)$ctxGood['id'])[0]), ['student_id', 'competency', 'level', 'source', 'score', 'at', 'artefact_ref', 'k']) === []);

// --- 6) Backfill (CLI): dry-run bez zápisu, --apply jen na kopii, idempotence ---------------------------------
$runCli = static function (array $args, array $env) use ($root): array {
    $cmd = array_merge([PHP_BINARY, $root . '/tools/v62_evidence_backfill.php'], $args);
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv(), $env));
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out];
};
$copy = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-audit-v62-copy-' . bin2hex(random_bytes(4));
mkdir($copy, 0700, true);
register_shutdown_function(static function () use ($copy): void { edu_audit_remove_dir($copy); });
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
    $rel = substr(str_replace('\\', '/', $item->getPathname()), strlen($tmp) + 1);
    if (str_starts_with($rel, 'evidence_v62') || str_starts_with($rel, 'mastery_v62') || str_ends_with($rel, '.lock')) continue;
    $item->isDir() ? @mkdir($copy . '/' . $rel, 0700, true) : copy($item->getPathname(), $copy . '/' . $rel);
}
$env = ['EDUCANET_STORAGE_DIR' => $copy];
$dry = $runCli([], $env);
$check('backfill: výchozí dry-run nic nezapíše (žádný adresář evidence_v62), vypíše statistiku a V62_BACKFILL_OK', $dry['code'] === 0 && str_contains($dry['out'], 'V62_BACKFILL_OK') && str_contains($dry['out'], 'DRYRUN') && !is_dir($copy . '/evidence_v62'));
$apply1 = $runCli(['--apply', '--json'], $env);
$apply2 = $runCli(['--apply', '--json'], $env);
$j1 = json_decode(substr($apply1['out'], 0, (int)strrpos($apply1['out'], '}') + 1), true) ?: [];
$j2 = json_decode(substr($apply2['out'], 0, (int)strrpos($apply2['out'], '}') + 1), true) ?: [];
$check('backfill: --apply na kopii přidá důkazy a druhé spuštění je idempotentní (added=0; ' . json_encode(['1' => $j1['added'] ?? null, '2' => $j2['added'] ?? null, 'nové' => $j1['new_rows'] ?? null]) . ')',
    $apply1['code'] === 0 && ($j1['added'] ?? 0) > 0 && ($j2['added'] ?? -1) === 0 && ($j2['new_rows'] ?? -1) === 0 && ($j2['duplicates'] ?? 0) === ($j2['rows'] ?? -1));
$liveRun = $runCli(['--apply'], ['EDUCANET_STORAGE_DIR' => '']);
$check('backfill: --apply bez EDUCANET_STORAGE_DIR (ostrá storage/) se odmítne s kódem 2 a nezapíše nic', $liveRun['code'] === 2 && str_contains($liveRun['out'], 'V62_BACKFILL_REFUSED'));
$check('backfill: třída mimo pilot (1.A) se odmítne', $runCli(['--class=class_1a'], $env)['code'] === 1);

// --- 7) Pilot: 1.A bez záložky a bez zápisu --------------------------------------------------------------
$sub1a = ev62_sync_student('class_1a', 'class_1a:student:neexistuje', true);
$check('pilot: synchronizace mimo 3.A je no-op (skipped=pilot) a nevytvoří soubor', ($sub1a['skipped'] ?? '') === 'pilot' && glob($tmp . '/evidence_v62/*class_1a*') === []);
$check('pilot: záložka kompetence jen v profilu pilotní třídy a jen vlastním; ne ve veřejných záložkách', in_array('kompetence', profile60_tabs_for('class_3a'), true) && !in_array('kompetence', profile60_tabs_for('class_1a'), true)
    && !in_array('kompetence', profile60_tabs_for(null), true) && !in_array('kompetence', profile60_public_tabs(), true) && !in_array('kompetence', profile60_tabs(), true));
$_GET['tab'] = 'kompetence';
$check('pilot: ?tab=kompetence mimo pilot → přehled; v pilotu vlastní profil → kompetence; cizí profil → přehled',
    profile60_current_tab(true, 'class_1a') === 'prehled' && profile60_current_tab(true, 'class_3a') === 'kompetence' && profile60_current_tab(false, 'class_3a') === 'prehled' && profile60_current_tab(true) === 'prehled');
unset($_GET['tab']);

// --- 8) Retence -----------------------------------------------------------------------------------------------
$leftId = (string)$ctxOf('left')['id'];
$setStatus = static function (string $id, string $status, ?string $at) use ($tmp): void {
    storage_update(identity58_path(), static function (array $reg) use ($id, $status, $at): array {
        $reg['students'][$id]['status'] = $status;
        $at === null ? ($reg['students'][$id]['archived_at'] = null) : ($reg['students'][$id]['archived_at'] = $at);
        return $reg;
    });
};
$check('retence: žák s důkazy a stavem active se nemaže', ev62_retention_purge(true, $NOW)['purge'] === 0 && is_file(ev62_path($leftId)));
$setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 29 * $day));
$check('retence: 29 dní po odchodu se ještě nemaže', ev62_retention_purge(true, $NOW)['purge'] === 0);
$setStatus($leftId, 'left', date(DATE_ATOM, $NOW - 31 * $day));
m62_student(V62FX_CLASS, v62fx_key('left'), $NOW);
$dryPurge = ev62_retention_purge(true, $NOW);
$check('retence: 31 dní po odchodu dry-run hlásí smazání, ale soubory zůstanou', $dryPurge['purge'] === 1 && $dryPurge['ids'] === [$leftId] && is_file(ev62_path($leftId)));
$setStatus($leftId, 'left', null);
$check('retence: bez data odchodu se nemaže nic (jen se započítá kept_unknown)', ev62_retention_purge(true, $NOW)['purge'] === 0 && ev62_retention_purge(true, $NOW)['kept_unknown'] === 1);
$setStatus($leftId, 'archived', date(DATE_ATOM, $NOW - 31 * $day));
$applyPurge = ev62_retention_purge(false, $NOW);
$check('retence: --apply smaže důkazy i cache zvládnutí žáka v archived/left; ostatní žáci zůstanou', $applyPurge['purge'] === 1 && !is_file(ev62_path($leftId)) && !is_file(m62_cache_path($leftId)) && is_file(ev62_path((string)$ctxGood['id'])));
// SEC62-01: skutečný CLI tools/v58_retention.php (samostatný proces, bez ručně načteného katalogu) nad izolovanou kopií.
$retDir = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/educanet-audit-v62-ret-' . bin2hex(random_bytes(4));
mkdir($retDir . '/evidence_v62', 0700, true);
mkdir($retDir . '/mastery_v62', 0700, true);
register_shutdown_function(static function () use ($retDir): void { edu_audit_remove_dir($retDir); });
$retIdFile = $retDir . '/' . basename(identity58_path());
copy(identity58_path(), $retIdFile);
$retId = $leftId;
$retEv = $retDir . '/evidence_v62/' . $retId . '.json.php';
$retPayload = "<?php http_response_code(403); exit; ?>\n" . json_encode(['v' => 1, 'rows' => [['student_id' => $retId, 'competency' => $ids[0], 'level' => 2, 'source' => 'lab', 'score' => 0.9, 'at' => date(DATE_ATOM, $NOW), 'artefact_ref' => 'lab:practice:sit-4', 'k' => 'x']], 'meta' => []]);
file_put_contents($retEv, $retPayload);
file_put_contents($retDir . '/mastery_v62/' . $retId . '.json.php', "<?php http_response_code(403); exit; ?>\n{}");
file_put_contents($retDir . '/mastery_v62/_class_class_3a.json.php', "<?php http_response_code(403); exit; ?>\n{}");
$retReg = file_get_contents($retIdFile);
$retJson = json_decode(substr($retReg, (int)strpos($retReg, "\n") + 1), true);
$retJson['students'][$retId]['status'] = 'archived';
$retJson['students'][$retId]['archived_at'] = date(DATE_ATOM, time() - 45 * $day);
file_put_contents($retIdFile, "<?php http_response_code(403); exit; ?>\n" . json_encode($retJson));
$runRet = static function (array $args) use ($root, $retDir): array {
    $proc = proc_open(array_merge([PHP_BINARY, $root . '/tools/v58_retention.php'], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => $retDir]));
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out];
};
$retDryRun = $runRet([]);
$check('retence CLI: dry-run (tools/v58_retention.php) doběhne bez chyby, ohlásí evidence_v62_purged=1 a nic nesmaže', $retDryRun['code'] === 0 && str_contains($retDryRun['out'], 'evidence_v62_purged=1') && !str_contains($retDryRun['out'], 'Undefined') && is_file($retEv));
$retApplyRun = $runRet(['--apply']);
$check('retence CLI: --apply smaže důkazy, cache žáka i cache třídy odešlého žáka (>30 dní) a vrátí kód 0',
    $retApplyRun['code'] === 0 && str_contains($retApplyRun['out'], 'evidence_v62_purged=1') && !is_file($retEv) && !is_file($retDir . '/mastery_v62/' . $retId . '.json.php') && !is_file($retDir . '/mastery_v62/_class_class_3a.json.php'));
$check('retence: politika v ops58 existuje (akce student_left, 30 dní) a vzor odpovídá skutečné cestě', in_array('student_left', array_column(array_filter(ops58_retention_policy(), static fn(array $p): bool => $p['pattern'] === 'evidence_v62/*.json.php'), 'action'), true)
    && ops58_matches_pattern('evidence_v62/' . $leftId . '.json.php', 'evidence_v62/*.json.php') && COMP62_RETENTION_GRACE_DAYS === 30);
$retDry = ops58_apply_retention(true);
$check('retence: ops58_apply_retention tuto akci jen přeskočí (neumí smazat podle stavu žáka, nic nearchivuje)', array_filter($retDry['actions'], static fn(array $a): bool => str_contains((string)$a['path'], 'evidence_v62')) === []);

// --- 9) Politiky učitele a zobrazení --------------------------------------------------------------------------
$check('učitel: comp62_sync vyžaduje třídu v rozsahu (class required), oprávnění analytics.view; jiné comp62_* i neznámé akce zůstávají zakázané (deny-by-default)',
    teacher59_action_policy('comp62_sync') === ['class' => 'required'] && teacher_action_permission('comp62_sync') === 'analytics.view'
    && (teacher59_action_policy('comp63_sync')['deny'] ?? false) === true && (teacher59_action_policy('comp62_hack')['deny'] ?? false) === true && (teacher59_action_policy('comp62_')['deny'] ?? false) === true && (teacher59_action_policy('neznama_akce')['deny'] ?? false) === true && teacher_action_permission('neznama_akce') === TEACHER_PERMISSION_DENY);
require_once $root . '/teacher_v58.php';
$mod = teacher58_modules()['kompetence'] ?? [];
$check('učitel: záložka kompetence je v registru modulů se všemi soubory, POST prefixem comp62_ a bez zvláštního GET parametru', $mod !== [] && teacher58_available('kompetence') && array_keys((array)$mod['post']) === ['comp62_'] && !isset($mod['get'])
    && array_filter((array)$mod['files'], static fn(string $f): bool => !is_file($root . '/' . $f)) === []);
ev62_memo_reset();
$html = audit_capture(static function (): void { comp62_render_teacher_tab(V62FX_CLASS, 'tok-csrf'); });
$check('učitel: mapa třídy má <th scope="col"> i <th scope="row">, scrollovací kontejner, formulář comp62_sync s CSRF a souhrn tfoot, bez PHP chyb',
    str_contains($html, '<th scope="col">') && str_contains($html, '<th scope="row">') && str_contains($html, 'c62-scroll') && str_contains($html, 'name="action" value="comp62_sync"') && str_contains($html, 'value="tok-csrf"')
    && str_contains($html, '<tfoot>') && audit_response_clean(['status' => 200, 'body' => $html]));
$check('učitel: cizí/nepilotní třída v požadavku se nezobrazí (zobrazí se jen pilotní z rozsahu)', !str_contains(audit_capture(static function (): void { comp62_render_teacher_tab('class_1a', 't'); }), 'class_1a'));
$_SESSION = ['next_class_id' => V62FX_CLASS];
$stu = audit_capture(static function (): void { comp62_render_student_tab(V62FX_CLASS, v62fx_key('good')); });
$check('žák: záložka zobrazí skupiny Umím / Učím se / Zatím ne, stav textem i ikonou, bez jmen jiných žáků a bez PHP chyb',
    str_contains($stu, 'Umím') && str_contains($stu, 'Učím se') && str_contains($stu, 'Zatím ne') && str_contains($stu, 'Zvládnuto') && str_contains($stu, 'aria-hidden="true">✓') && !str_contains($stu, 'Audit Hrac') && audit_response_clean(['status' => 200, 'body' => $stu]));
$check('žák: mimo pilotní třídu záložka nic nevykreslí', audit_capture(static function (): void { comp62_render_student_tab('class_1a', 'x'); }) === '');
$cssSize = (int)filesize($root . '/assets/competency-v62.css');
$check('CSS: ≤ 8 kB, žádný JS asset vrstvy, žádné CDN', $cssSize <= 8192 && !is_file($root . '/assets/competency-v62.js') && !str_contains((string)file_get_contents($root . '/assets/competency-v62.css'), 'http'));

// --- 10) Token-sken invariantu labu a zdrojů vrstvy ------------------------------------------------------------
$forbidden = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'mail', 'dns_get_record',
    'gethostbyname', 'gethostbyaddr', 'checkdnsrr', 'lab57_build_world', 'lab57_session', 'lab57_cmd_run', 'file_get_contents_url'];
$layerFiles = ['competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php', 'competency_v62_views.php', 'competency_v62_teacher_views.php'];
$hits = [];
foreach ($layerFiles as $f) {
    foreach (token_get_all((string)file_get_contents($root . '/' . $f)) as $tok) {
        if (!is_array($tok)) { if ($tok === '`') $hits[] = $f . ':backtick'; continue; }
        $name = strtolower($tok[1]);
        if ($tok[0] === T_EXIT || $tok[0] === T_EVAL) { if ($tok[0] === T_EVAL) $hits[] = $f . ':eval'; continue; }
        if (in_array($tok[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true) && (in_array($name, $forbidden, true) || str_starts_with($name, 'curl_') || str_starts_with($name, 'socket_'))) $hits[] = $f . ':' . $name;
    }
    if (preg_match('/(file_get_contents|fopen)\s*\(\s*[\'"]https?:/i', (string)file_get_contents($root . '/' . $f)) === 1) $hits[] = $f . ':url';
}
$check('invariant labu: adaptéry a vrstva nevolají exec/síť/eval/DNS/mail ani lab57_build_world/session (token-sken ' . count($layerFiles) . ' souborů)', $hits === [], false);
$linesOk = true;
foreach (array_merge($layerFiles, ['tools/v62_competency_audit.php', 'tools/v62_evidence_backfill.php', 'tools/lib/v62_competency_fixtures.php']) as $f) if (count(file($root . '/' . $f)) > 800) $linesOk = false;
$check('soubory vrstvy mají ≤ 800 řádků a ochranné guardy proti přímému volání', $linesOk && array_reduce($layerFiles, static fn(bool $ok, string $f): bool => $ok && str_contains((string)file_get_contents($root . '/' . $f), "http_response_code(403)"), true), false);

exit(audit_summary($state, 'V62_COMPETENCY'));
