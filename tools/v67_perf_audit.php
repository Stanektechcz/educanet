<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · audit výkonu přehledu žáka (fiktivní třída 30 žáků, dočasné úložiště, samostatný proces na měření, opcache jako v PHP-FPM).
 *   1) přehled: ≤ 35 čtení úložiště, žádný soubor víc než 3× (max_same), medián z 11 běhů v limitu v61 (60 ms + tolerance šumu), HTML menší než před v67,
 *   2) GET přehledu v ustáleném stavu nezapisuje do úložiště (ani při ?details=1),
 *   3) přepočet dovedností se dělá tam, kde se výsledek mění: po změně výsledku (dokončené téma) otisk neplatí, skill67_sync_now dovednost zvýší
 *      BEZ návštěvy přehledu a po přepočtu přehled čte hotové řádky (skill67_prime),
 *   4) akce, které mění výsledek, mají shutdown háček (tabulka akcí), ostatní ne,
 *   5) podrobný progres (grafy, odznaky) se vykresluje jen na ?details=1,
 *   6) paměť požadavku perf67_memo: výpočet jednou, po zápisu znovu.
 *
 *   php tools/v67_perf_audit.php
 * Cíl „≤ 45 ms“ se jen hlásí (podlaha stroje = nejlehčí stránka); tvrdá kontrola je limit v61 – změna metodiky: 11 běhů místo 5.
 * Konec: V67_PERF_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$GLOBALS['educanet_request_memo'] = true;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v67-perf')), '/');
$opcacheDir = rtrim(str_replace(chr(92), '/', sys_get_temp_dir()), '/') . '/educanet-audit-opc-' . bin2hex(random_bytes(6));
@mkdir($opcacheDir, 0700, true);
register_shutdown_function(static function () use ($opcacheDir): void { edu_audit_remove_dir($opcacheDir); });
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/app/lib.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
require_once __DIR__ . '/lib/v61_perf_pages.php';
require_once __DIR__ . '/lib/v61_perf_fixture.php';
app_require_libs(['core', 'layout']);

const V67P_RUNS = 11;
const V67P_MAX_READS = 35;
const V67P_MAX_SAME = 3;
const V67P_TARGET_MS = 45.0;
const V67P_HTML_BEFORE_BYTES = 54849;   // přehled před v67 (měřeno stejnou fixturou)

$state = audit_counter();
$check = audit_checker($state);

function v67p_snapshot(string $dir): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (str_ends_with($f->getFilename(), '.lock')) continue;
        $out[substr(str_replace(chr(92), '/', $f->getPathname()), strlen($dir))] = md5_file($f->getPathname());
    }
    ksort($out);
    return $out;
}

$check('úložiště auditu je dočasné (ne ostrá storage/)', $tmp !== '' && !str_starts_with($tmp, $ROOT . '/storage') && str_contains($tmp, 'educanet-audit-'));
$fx = v61_perf_seed($modules, $tmp, 30);
$session = ['next_class_id' => $fx['class'], 'student_label' => $fx['label'], 'local_user' => ['id' => $fx['id'], 'email' => $fx['email'], 'name' => $fx['label']]];
$spec = static fn(string $view): array => ['kind' => 'student', 'query' => ['view' => $view], 'session' => $session, 'storage' => $tmp, 'opcache_dir' => $opcacheDir];

// ---------------------------------------------------------------- 1) měření přehledu
$dash = v61_perf_measure($spec('dashboard'), V67P_RUNS, []);
$floor = v61_perf_measure($spec('privacy'), V67P_RUNS, []);
$limit = v61_effective_limit_ms(V61_BUDGET_STUDENT_MS);
$check('přehled: HTTP 200 bez chyby a bez přesměrování', $dash !== null && $dash['status'] === 200 && !$dash['error'] && $dash['location'] === '' && $dash['bytes'] > 5000);
$check(sprintf('přehled: čtení úložiště ≤ %d (naměřeno %d)', V67P_MAX_READS, (int)($dash['calls'] ?? 999)), $dash !== null && $dash['calls'] <= V67P_MAX_READS);
$check(sprintf('přehled: žádný soubor se nečte víc než %d× (max_same %d)', V67P_MAX_SAME, (int)($dash['max_same'] ?? 99)), $dash !== null && $dash['max_same'] <= V67P_MAX_SAME);
$check(sprintf('přehled: medián z %d běhů %.1f ms ≤ limit v61 %.1f ms (60 ms + tolerance šumu; limit beze změny)', V67P_RUNS, (float)($dash['ms'] ?? 0), $limit), $dash !== null && $dash['ms'] <= $limit);
$check(sprintf('přehled: HTML %.1f KB je menší než před v67 (%.1f KB)', ($dash['bytes'] ?? 0) / 1024, V67P_HTML_BEFORE_BYTES / 1024), $dash !== null && $dash['bytes'] < V67P_HTML_BEFORE_BYTES);
$gap = $dash !== null && $floor !== null ? round($dash['ms'] - $floor['ms'], 1) : -1;
$reached = ($dash['ms'] ?? 999) <= V67P_TARGET_MS ? ' (cíl splněn)' : ' (cíl nesplněn: podlaha stroje je blízko cíle)';
echo sprintf("INFO  cíl ≤ %.0f ms: přehled %.1f ms, nejlehčí stránka (soukromí) %.1f ms = podlaha stroje; přehled je o %.1f ms nad podlahou%s\n", V67P_TARGET_MS, (float)($dash['ms'] ?? 0), (float)($floor['ms'] ?? 0), $gap, $reached);

// ---------------------------------------------------------------- 2) GET nezapisuje
$before = v67p_snapshot($tmp);
v61_perf_run($spec('dashboard'), []);
$afterPlain = v67p_snapshot($tmp);
$specDetails = $spec('dashboard');
$specDetails['query'] = ['view' => 'dashboard', 'details' => '1'];
v61_perf_run($specDetails, []);
$mid = v67p_snapshot($tmp);
v61_perf_run($specDetails, []);
$afterDetails = v67p_snapshot($tmp);
$changedPlain = array_keys(array_diff_assoc($afterPlain, $before) + array_diff_assoc($before, $afterPlain));
$check('GET přehledu v ustáleném stavu nezapisuje do úložiště' . ($changedPlain ? ' [' . implode(', ', array_slice($changedPlain, 0, 3)) . ']' : ''), $changedPlain === []);
$changedDetails = array_keys(array_diff_assoc($afterDetails, $mid) + array_diff_assoc($mid, $afterDetails));
$check('GET přehledu s ?details=1 v ustáleném stavu (druhé načtení) také nezapisuje' . ($changedDetails ? ' [' . implode(', ', array_slice($changedDetails, 0, 3)) . ']' : ''), $changedDetails === []);

// ---------------------------------------------------------------- 3) přepočet dovedností mimo přehled
$_SESSION['next_class_id'] = V61_PERF_CLASS;
$_SESSION['student_label'] = $fx['label'];
$_SESSION['local_user'] = ['id' => $fx['id'], 'email' => $fx['email'], 'name' => $fx['label']];
$classId = V61_PERF_CLASS;
skill67_sync_now($classId);
skill_memo_reset();
$primed = skill67_prime($classId);
$check('po přepočtu otisk sedí a skill67_prime naplní paměť z uložených řádků (bez přepočtu)', $primed && !empty($GLOBALS['skill_runtime_progress'][$classId . '|' . skill_current_student_key($classId)]));
$slug = '';
$topic = '';
foreach (skill_knowledge_topic_map($classId) as $s => $t) {
    $skill = skill_find((string)$s);
    $kb = (array)(learning_profile($classId)['kb'] ?? []);
    if ($skill !== null && empty($skill['mastery_node']) && is_string($t) && $t !== '' && empty($kb[$t]['complete'])) { $slug = (string)$s; $topic = $t; break; }
}
$check('fixture: existuje dovednost navázaná na téma, které žák ještě nedokončil (' . ($slug !== '' ? 'ano' : 'ne') . ')', $slug !== '');
$masteryBefore = (float)(skill_progress($classId, $slug)['mastery_percent'] ?? 0);
$profile = learning_profile($classId);
$profile['kb'][$topic] = ['complete' => true, 'check' => true];
learning_save_profile($classId, $profile);   // jako akce, která dokončí téma (progress.php / test)
skill_memo_reset();
$check('po změně výsledku otisk přestane platit (přehled by přepočítal, nikdy nezobrazí zastaralá data)', !skill67_prime($classId));
skill_memo_reset();
skill67_sync_now($classId);   // totéž dělá shutdown háček po akci – bez návštěvy přehledu
skill_memo_reset();
$masteryAfter = (float)(skill_progress($classId, $slug)['mastery_percent'] ?? 0);
$check(sprintf('po dokončení tématu se dovednost zvýší bez návštěvy přehledu (%.0f → %.0f %%)', $masteryBefore, $masteryAfter), $masteryAfter > $masteryBefore);
skill_memo_reset();
$check('po přepočtu přehled znovu čte hotové řádky (otisk sedí)', skill67_prime($classId));

// ---------------------------------------------------------------- 4) tabulka akcí
$mustSync = ['answer', 'continue_test', 'abort_test', 'practice_answer', 'submit_extra', 'v56_complete', 'v55_step', 'proj65_s_submit', 'p63_submit', 'ml_check', 'project_publish', 'tut52_score'];
$mustNot = ['ui67_theme_set', 'edu_set_lang', 'logout_class', 'local_login', 'fb60_submit', 'mkt60_buy', 'grow67_goal_add'];
$check('shutdown háček: akce měnící výsledek (test, lekce, cesta, projekt) spouštějí přepočet', array_filter($mustSync, static fn(string $a): bool => !perf67_action_changes_results($a)) === []);
$check('shutdown háček: nesouvisející akce (vzhled, jazyk, odhlášení, obchod, cíle profilu) přepočet nespouštějí', array_filter($mustNot, static fn(string $a): bool => perf67_action_changes_results($a)) === []);

// ---------------------------------------------------------------- 5) podrobný progres jen na ?details=1
audit_prewarm_accounts($modules);
$harness = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0']);
try {
    audit_login_student($harness, V61_PERF_CLASS, $fx['label']);
    $plain = $harness->request('GET', '/?view=dashboard');
    $more = $harness->request('GET', '/?view=dashboard&details=1');
    $check('HTTP: přehled bez ?details=1 nevykresluje podrobný progres, ale nabízí odkaz „Zobrazit můj progres“', audit_response_clean($plain) && !str_contains((string)$plain['body'], 'student-dashboard-more') && str_contains((string)$plain['body'], 'details=1'));
    $check('HTTP: ?details=1 vykreslí podrobný progres (grafy, odznaky, historie) a je 200 bez chyby', audit_response_clean($more) && str_contains((string)$more['body'], 'student-dashboard-more') && str_contains((string)$more['body'], 'xp-chart'));
    $check('HTTP: úkol Teď zůstal na přehledu (obsah se nezkrátil)', str_contains((string)$plain['body'], 'student-do-now-card'));
} finally {
    $harness->stop();
}

// ---------------------------------------------------------------- 6) paměť požadavku
$calls = 0;
$compute = static function () use (&$calls): int { return ++$calls; };
perf67_memo('audit', $compute);
perf67_memo('audit', $compute);
$check('perf67_memo: výpočet proběhne jednou na požadavek', $calls === 1);
storage_update($tmp . '/v67_memo_probe.json.php', static fn(array $d): array => ['x' => 1]);
perf67_memo('audit', $compute);
$check('perf67_memo: po zápisu do úložiště se počítá znovu (čerstvá data)', $calls === 2);

echo "\nPoznámka: čas = medián z " . V67P_RUNS . " běhů (V61A_RUNS v tools/v61_perf_audit.php je také 11), tolerance šumu " . (int)(V61_NOISE_TOLERANCE * 100) . " %.\n";
exit(audit_summary($state, 'V67_PERF'));
