<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v71 · audit jednotného modelu lekce (lm71), Dnešní hodiny v71, karet dnů bez lekce a reportů.
 *   1) soubory vrstvy (strict_types, guard, ≤ 800 řádků), registr záložky hodina (jen čtení, CSS v71), politiky beze změny,
 *   2) model: 112 lekcí + 11 dnů × 4 třídy, žákovská cesta = runtime cache, kurikulum = jeden loader, precedence overlaye,
 *      úplnost (12 polí), plán po minutách, anglické titulky, cache (podpis → obsahový hash → přestavba),
 *   3) Dnešní hodina: plán a poznámky v HTML i přes HTTP, karta dne bez lekce, „bez aktivity“ ≠ „potřebuje pomoc“,
 *   4) reporty: jen čtení (SHA úložiště před/po), bez jmen, --out mimo projekt, nejsou v run_audits,
 *   5) materials/.htaccess bez HTML komentářů, CSS v71 jen tokeny.
 *   php tools/v71_lesson_model_audit.php     Dočasné úložiště i cache modelu. Konec: V71_LESSON_MODEL_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v71-model')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V71A_KEY = 'v71-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V71A_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_operations_control_v46_2.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'ui_v67.php',
    'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php', 'session_v53.php', 'accounts_v53.php', 'intake_v51.php', 'teacher_curriculum.php', 'teacher_lesson_mode.php'] as $lib) require_once $ROOT . '/' . $lib;
foreach ((array)teacher58_modules()['hodina']['files'] as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/run_audits.php';

/** Snímek politik teacher59 z v68/v69/v70 – v71 nesmí přidat akci ani GET politiku. */
const V71A_POST_POLICY_SHA = '656e6ab3383dd1f14b02e3ee6799157ff5c9ac50f021808617245eea74afe55c';
const V71A_GET_POLICY_SHA = 'c27cfc36d68d8d73eaee7bed2a84f6ab478bc6f62ab6f17591982d6763699c2a';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-')
    && lm71_cache_dir() === str_replace(chr(92), '/', $cacheDir) && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['lesson_model_v71_sources.php', 'lesson_model_v71.php', 'calendar_days_v71.php', 'lesson_today_v71_views.php', 'lesson_today_v70.php', 'tools/lib/report_v71.php'];
$cli = ['tools/v71_content_report.php', 'tools/v71_data_report.php', 'tools/v71_lesson_model_audit.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if (!str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v71: strict_types, guard (knihovny SCRIPT_FILENAME, CLI PHP_SAPI), ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [], false);
$mods = teacher58_modules();
$hodina = $mods['hodina'];
$check('registr: Dnešní hodina vykresluje v71, načítá model lm71 a karty dnů, má CSS v71 a stále nemá post ani get',
    !isset($hodina['post']) && !isset($hodina['get']) && array_intersect(['lesson_model_v71.php', 'calendar_days_v71.php', 'lesson_today_v71_views.php'], $hodina['files']) === ['lesson_model_v71.php', 'calendar_days_v71.php', 'lesson_today_v71_views.php']
    && ($hodina['css'] ?? []) === ['assets/cockpit-v71.css'] && str_contains($read('teacher_v58.php'), 'lt71_render_tab($m, $c)') && in_array('hodina', UI68_TEACHER_DARK_TABS, true));
$post = teacher59_action_policies();
unset($post['teacher68_theme_set']);
ksort($post);
$get = teacher59_get_policies();
foreach (['kompetence', 'cesty', 'projekty65', 'hodnoceni66', 'labdata'] as $t) unset($get[$t . '|student']);
ksort($get);
$check('politiky teacher59: POST i GET tabulka beze změny (v71 nepřidává akci ani GET parametr)', hash('sha256', (string)json_encode($post)) === V71A_POST_POLICY_SHA && hash('sha256', (string)json_encode($get)) === V71A_GET_POLICY_SHA);
$check('deny-by-default: neznámé akce lt71_*/lm71_*/cd71_* jsou zakázané', teacher59_action_policy('lt71_x')['deny'] === true && teacher59_action_policy('lm71_x')['deny'] === true && teacher59_action_policy('cd71_x')['deny'] === true);

// ---------------------------------------------------------------- 2) model
$year = lt70_school_year();
$count = 0;
$keysOk = true;
$required = ['id', 'class_id', 'number', 'title', 'goal', 'curriculum', 'competencies', 'timeline', 'materials', 'differentiation', 'tasks', 'assessment', 'exit_ticket', 'safety', 'teacher_notes', 'substitution', 'meta', 'completeness'];
$bundleSame = true;
$curriculumSame = true;
$sourcesOk = true;
foreach (LM71_CLASSES as $c) {
    $rt = runtime_content_load_classes([$c]);
    $rows = teacher_curriculum_lessons($c);
    $curriculumSame = $curriculumSame && $rows === lm71_curriculum_rows($c) && array_column($rows, 'number') === range(1, 28) && $rows[0]['id'] === 'lesson_1_primary'
        && teacher_lesson_mode_find($c, 28) === $rows[27];
    foreach (lm71_lessons($c) as $n => $l) {
        $count++;
        $keysOk = $keysOk && array_diff($required, array_keys($l)) === [] && $l['number'] === $n && $l['class_id'] === $c && is_array($l['goal']['success_criteria']);
        $sourcesOk = $sourcesOk && $l['meta']['source'] === lm71_source_for_number($n);
        $old = v56_lesson_bundle($c, $GLOBALS['modules'][$c], $n, $rt['nextLessons'], $rt['extendedLessons']);
        $bundleSame = $bundleSame && json_encode($old) === json_encode(lm71_bundle($c, $GLOBALS['modules'][$c], $n));
    }
}
$check('model: 4 třídy × 28 lekcí = 112 normalizovaných lekcí s povinnými poli (id, cíl, plán, úplnost, meta…)', $count === 112 && $keysOk);
$check('precedence zdrojů: každá lekce pochází ze zdroje, který ji deklaruje (L1 primary … L19–28 v30), konflikty 0', $sourcesOk && array_sum(array_map(static fn(string $c): int => count(lm71_raw($c)['conflicts']), LM71_CLASSES)) === 0);
$check('žákovská cesta: lm71_bundle = v56_lesson_bundle nad runtime cache u všech 112 lekcí (učitel vidí totéž co žák)', $bundleSame);
$check('jeden loader: Plán a kurikulum i Režim hodiny čtou lekce přes lm71_curriculum_rows (28 řádků, L1 = lesson_1_primary)', $curriculumSame);
$status = lm71_cache_status($ROOT . '/cache/lesson_model');
$builder = $read('tools/build_runtime_cache.php');
preg_match_all("~'/?([a-z0-9_]+\\.php)'~", $builder, $mm);
$builderFiles = array_values(array_diff(array_unique($mm[1]), ['bootstrap.php', 'lesson_model_v71_sources.php', 'lesson_model_v71.php']));
$check('runtime cache: builder zapisuje _sources_hash ze stejného seznamu souborů, který hlídá lm71; cache všech tříd je aktuální',
    str_contains($builder, "'_sources_hash'=>\$sourcesHash") && count(array_diff($builderFiles, lm71_runtime_cache_files())) === 0 && count(array_diff(lm71_runtime_cache_files(), $builderFiles)) === 0
    && array_unique(array_values($status['runtime'])) === ['aktuální']);
$days = cd71_calendar_days($year);
$cards = [];
foreach (LM71_CLASSES as $c) foreach ($days as $row) $cards[] = lm71_day($c, $row);
$dayOk = array_reduce($cards, static fn(bool $ok, array $d): bool => $ok && in_array($d['day_kind'], CD71_DAY_KINDS, true) && array_sum(array_column($d['flow'], 'minutes')) === 90
    && $d['flow'][0]['from'] === 0 && $d['goal'] !== '' && $d['bring'] !== [] && $d['check'] !== [] && $d['focus'] !== '', true);
$check('dny bez lekce: 11 výukových dnů × 4 třídy = 44 karet (cíl, průběh 90 min, co mít, co kontrolovat, zaměření class_tail_focus), id jedinečná',
    count($days) === 11 && count($cards) === 44 && $dayOk && count(array_unique(array_column($cards, 'id'))) === 44 && count(array_unique(array_column($cards, 'day_kind'))) === count(CD71_DAY_KINDS));

// precedence overlaye (čisté funkce) a úplnost
$ov = lm71_overlay_merge(['lessons' => [], 'days' => [], 'files' => []], 'lesson_content_v72_3a.php', ['class_3a' => ['lessons' => [5 => ['title' => 'Starý', 'safety' => ['Jen simulátor.'], 'hack' => 'x'], 40 => ['title' => 'mimo']], 'days' => [29 => ['goal' => 'Cíl dne']]], 'bad id' => ['lessons' => [5 => ['title' => 'x']]]]);
$ov = lm71_overlay_merge($ov, 'lesson_content_v72_3a_b.php', ['class_3a' => ['lessons' => [5 => ['title' => 'Nový titulek lekce']]]]);
$l5 = lm71_lesson('class_3a', 5);
$full = lm71_apply_overlay($l5, $ov['lessons']['class_3a'][5] + [
    'success_criteria' => ['Umím A.', 'Umím B.'], 'timeline' => [['from' => 0, 'to' => 30, 'phase' => 'Úvod'], ['from' => 30, 'to' => 60, 'phase' => 'Práce'], ['from' => 60, 'to' => 90, 'phase' => 'Závěr']],
    'differentiation' => ['support' => 'Nápověda', 'standard' => 'Zadání', 'challenge' => 'Výzva'], 'assessment' => ['checks' => [['question' => 'Q1', 'step' => 'x', 'options' => 3, 'correct' => 1]], 'rubric' => [1, 2, 3]],
    'exit_ticket' => ['variants' => ['E1?', 'E2?', 'E3?']], 'teacher_notes' => ['Pozor na X.'], 'substitution' => ['Plán B offline.'], 'tasks' => ['Úkol 1', 'Úkol 2', 'Úkol 3'],
], []);
$full['completeness'] = lm71_completeness($full);
$check('overlay: pozdější soubor vyhrává pole po poli, neznámé pole / cizí klíč / lekce mimo 1–28 se zahodí, stav „navrh“, žákovské kroky beze změny',
    $ov['lessons']['class_3a'][5]['title'] === 'Nový titulek lekce' && $ov['lessons']['class_3a'][5]['safety'] === ['Jen simulátor.'] && !isset($ov['lessons']['class_3a'][5]['hack'])
    && !isset($ov['lessons']['class_3a'][40]) && !isset($ov['lessons']['bad id']) && $ov['days']['class_3a'][29]['goal'] === 'Cíl dne'
    && $full['title'] === 'Nový titulek lekce' && $full['meta']['status'] === 'navrh' && $full['steps'] === $l5['steps'] && $full['topics'] === $l5['topics']);
$check('úplnost: plně vyplněný model = 12/12, výchozí L5 není úplná a uvádí, co chybí', $full['completeness']['complete'] && $full['completeness']['score'] === 12
    && !$l5['completeness']['complete'] && in_array('kritéria úspěchu (2–4)', $l5['completeness']['missing'], true) && $l5['completeness']['total'] === 12);
$check('plán po minutách: souvislý 0–90 platí, mezera / dopočet z kroků / součet ≠ 90 neplatí; L1 je dopočtená z kroků',
    lm71_timeline_ok($full['timeline']) && !lm71_timeline_ok(lm71_timeline([], [], [['time' => '0–10'], ['time' => '20–50'], ['time' => '50–90']]))
    && !lm71_timeline_ok(lm71_timeline([], [], [['time' => '0–10'], ['time' => '10–50'], ['time' => '50–80']])) && !empty(lm71_lesson('class_1a', 1)['timeline'][0]['derived']));
$check('anglické titulky (heuristika reportu): „Filesystem incident“ ano, „Anatomie webu: hierarchie…“ a „SSH klíče“ ne',
    lm71_is_english_title('Lekce 19 · Filesystem incident') && !lm71_is_english_title('Anatomie webu: hierarchie od plakátu k obrazovce') && !lm71_is_english_title('SSH klíče') && !lm71_is_english_title('Mini vizuální identita'));
$l19 = lm71_lesson('class_3a', 19);
$check('šablony: generované L19–28 mají příznak šablony a šablonové úkoly; exit ticket je zatím jedna varianta shodná s kvízem', $l19['meta']['template'] && count(array_filter($l19['tasks'], static fn(array $t): bool => $t['template'])) >= 3
    && count($l19['exit_ticket']) === 1 && !$l19['completeness']['checks']['exit_ticket']);

// cache: podpis → obsahový hash → přestavba
$path = lm71_cache_path('class_2a');
$cached = lm71_cache_read($path);
$check('cache modelu: soubor v cache/lesson_model (dočasná) s podpisem a obsahovým hashem, adresář má .htaccess se zákazem', is_array($cached) && isset($cached['sig'], $cached['hash'], $cached['lessons'][28])
    && str_contains((string)@file_get_contents($cacheDir . '/.htaccess'), 'Require all denied'));
$stale = $cached;
$stale['sig'] = 'x';
$stale['built_at'] = 'v71-audit-reused';
lm71_cache_write($path, $stale);
$fresh = lm71_build_raw('class_2a');
$status2 = lm71_cache_status($cacheDir);
$loader = $cacheDir . '-load.php';
file_put_contents($loader, "<?php\nrequire " . var_export($ROOT . '/lesson_model_v71.php', true) . ";\nlm71_raw('class_2a');\n");
register_shutdown_function(static function () use ($loader): void { @unlink($loader); });
$loadRaw = static function () use ($loader): void {
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($loader) . ' 2>&1');
};
$loadRaw();
$reused = lm71_cache_read($path);
$check('cache modelu: po git checkoutu (jiný podpis, stejný obsah) se cache znovu použije a jen obnoví podpis, nepřestaví se',
    is_array($reused) && ($reused['built_at'] ?? '') === 'v71-audit-reused' && ($reused['sig'] ?? 'x') !== 'x');
$stale['hash'] = 'old';
$stale['sig'] = 'x';
lm71_cache_write($path, $stale);
$loadRaw();
$rebuilt = lm71_cache_read($path);
$check('cache modelu: změněný obsah zdrojů (jiný hash) = přestavba ze zdrojů', is_array($rebuilt) && ($rebuilt['built_at'] ?? '') !== 'v71-audit-reused' && ($rebuilt['hash'] ?? '') === $cached['hash']);
lm71_cache_write($path, $cached);
$stale2 = $cached;
$stale2['hash'] = 'old';
lm71_cache_write($path, $stale2);
$check('cache modelu: jiný podpis (časy souborů) při stejném obsahu = stále „aktuální“; jiný obsahový hash = „zastaralá“; poškozený soubor se ignoruje',
    $status2['model']['class_2a'] === 'aktuální' && lm71_cache_status($cacheDir)['model']['class_2a'] === 'zastaralá' && $fresh['lessons'] == $cached['lessons']
    && (file_put_contents($path, '<?php return [') !== false) && lm71_cache_read($path) === null);
@unlink($path);

// ---------------------------------------------------------------- 3) Dnešní hodina
$lessonDate = '';
foreach (adaptive_school_year_rows($year, 'class_3a') as $row) if ((int)($row['lesson_number'] ?? 0) === 12) $lessonDate = (string)$row['date'];
$dayDate = (string)$days[1]['date'];
$l28Rows = array_values(array_filter(adaptive_school_year_rows($year, 'class_3a'), static fn(array $r): bool => (int)($r['lesson_number'] ?? 0) === 28));
$afterLast = date('Y-m-d', (int)strtotime((string)($l28Rows[0]['date'] ?? '2027-04-21') . ' +1 day'));
$check('kalendář: v den checkpointu je karta „dnes“, den po L28 ukáže nejbližší den bez lekce, ve výukový den lekce žádná karta',
    ($r1 = cd71_next_day_row(adaptive_school_year_rows($year, 'class_3a'), $dayDate)) !== null && $r1['is_today'] && ($r2 = cd71_next_day_row(adaptive_school_year_rows($year, 'class_3a'), $afterLast)) !== null && !$r2['is_today']
    && cd71_next_day_row(adaptive_school_year_rows($year, 'class_3a'), $lessonDate) === null && lt70_slot($year, 'class_3a', $afterLast, 0)['status'] === 'none');
$now = (int)strtotime($lessonDate . ' 15:00:00');
$m = lt71_model($GLOBALS['modules'], 'class_3a', 0, $now);
$html = audit_capture(static function () use ($now): void { $_GET = ['class' => 'class_3a']; lt71_render_tab($GLOBALS['modules'], 'class_3a', $now); $_GET = []; });
$note = (string)($m['lesson']['teacher_notes'][0] ?? '');
$check('Dnešní hodina (L12 3.A): plán po minutách, poznámky pro učitele, pracovní list, kritéria, lesson kit a odznak úplnosti; bez karty dne',
    $m['lesson']['number'] === 12 && $m['day'] === null && $note !== '' && str_contains($html, e($note)) && str_contains($html, 'Plán po minutách') && str_contains($html, 'Poznámky pro učitele')
    && str_contains($html, 'Pracovní list') && str_contains($html, 'Kritéria úspěchu') && str_contains($html, 'Lesson kit lekce 12') && str_contains($html, '#lesson-kit') && preg_match('~Úplnost přípravy: \d+/12 polí~u', $html) === 1
    && str_contains($html, 'Průběh hodiny') && str_contains($html, 'class="c70-table"') && !str_contains($html, 'c71-day'));
$dayHtml = audit_capture(static function () use ($dayDate): void { $_GET = ['class' => 'class_3a']; lt71_render_tab($GLOBALS['modules'], 'class_3a', (int)strtotime($dayDate . ' 09:00:00')); $_GET = []; });
$afterHtml = audit_capture(static function () use ($afterLast): void { $_GET = ['class' => 'class_3a']; lt71_render_tab($GLOBALS['modules'], 'class_3a', (int)strtotime($afterLast . ' 09:00:00')); $_GET = []; });
$check('karta dne bez lekce: v den checkpointu „Dnes: Projektový checkpoint 1“ s průběhem a kontrolou; po L28 „Další výukový den“ místo „Školní rok skončil“',
    str_contains($dayHtml, 'c71-day') && str_contains($dayHtml, 'Dnes: ' . e((string)$days[1]['title'])) && str_contains($dayHtml, 'Co kontroluji') && str_contains($dayHtml, 'Den bez lekce')
    && str_contains($afterHtml, 'Další výukový den') && !str_contains($afterHtml, 'Školní rok skončil'));

$students = project_students_for_class('class_3a');
$firstKey = (string)array_key_first($students);
$idleModel = lt70_model($GLOBALS['modules'], 'class_3a', 0, $now);
$check('třída bez aktivity: nikdo není „potřebuje pomoc“, všichni „zatím bez aktivity“ (bez dat ≠ zaostává)', count($students) > 0 && $idleModel['kpis']['behind'] === 0
    && $idleModel['kpis']['idle'] === count($students) && str_contains($html, 'zatím bez aktivity'));
storage_update(v56_progress_path(), static function (array $d) use ($firstKey): array { $d['class_3a|' . $firstKey]['11'] = ['theory' => ['x' => true]]; return $d; });
$active = lt70_model($GLOBALS['modules'], 'class_3a', 0, $now);
$me = array_values(array_filter($active['students'], static fn(array $r): bool => $r['key'] === $firstKey))[0] ?? [];
$check('žák s nedokončenou minulou lekcí už není „bez aktivity“ a dostane signál (minulá lekce < 75 %)', ($me['idle'] ?? true) === false && ($me['behind'] ?? false) === true
    && $active['kpis']['idle'] === count($students) - 1 && $active['kpis']['behind'] >= 1);

audit_prewarm_accounts($GLOBALS['modules']);
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V71A_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
try {
    audit_login_teacher($h, V71A_KEY, 'Audit Učitel');
    $p19 = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a', 'lesson' => '19']);
    $n19 = (string)($l19['teacher_notes'][0] ?? '');
    $check('HTTP: Dnešní hodina ?lesson=19 – čistá odpověď, CSS v71, plán po minutách a poznámka pro učitele z obsahu lekce, odznak šablony',
        audit_response_clean($p19) && str_contains((string)$p19['body'], 'assets/cockpit-v71.css') && str_contains((string)$p19['body'], 'Plán po minutách')
        && $n19 !== '' && str_contains((string)$p19['body'], e($n19)) && str_contains((string)$p19['body'], 'šablonový obsah'));
    $teach = $h->request('GET', '/teacher.php', ['tab' => 'teach', 'class' => 'class_3a', 'lesson' => '12']);
    $cur = $h->request('GET', '/teacher.php', ['tab' => 'curriculum', 'class' => 'class_3a']);
    $check('HTTP: Režim hodiny má kotvu #lesson-kit a název lekce z lm71; Plán a kurikulum ukazuje 28 lekcí', audit_response_clean($teach) && str_contains((string)$teach['body'], 'id="lesson-kit"')
        && str_contains((string)$teach['body'], e(lm71_lesson('class_3a', 12)['title'])) && audit_response_clean($cur) && str_contains((string)$cur['body'], '28 dvouhodinových bloků'));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- 4) reporty
$labels = array_values(array_filter(array_map(static fn(array $s): string => (string)($s['label'] ?? ''), array_merge(...array_map('project_students_for_class', LM71_CLASSES))), static fn(string $l): bool => mb_strlen($l) >= 4));
$out = $tmp . '-reports';
$php = escapeshellarg(PHP_BINARY);
$before = rp71_audit_hash($tmp);
exec($php . ' ' . escapeshellarg($ROOT . '/tools/v71_data_report.php') . ' --storage=' . escapeshellarg($tmp) . ' --out=' . escapeshellarg($out . '/data') . ' 2>&1', $dOut, $dCode);
exec($php . ' ' . escapeshellarg($ROOT . '/tools/v71_content_report.php') . ' --storage=' . escapeshellarg($tmp) . ' --out=' . escapeshellarg($out . '/content') . ' 2>&1', $cOut, $cCode);
$after = rp71_audit_hash($tmp);
$written = implode("\n", array_merge($dOut, $cOut));
foreach (array_merge(glob($out . '/*/*') ?: []) as $f) $written .= "\n" . (string)file_get_contents($f);
$leak = array_values(array_filter($labels, static fn(string $l): bool => str_contains($written, $l)));
$check('reporty: data i obsah doběhly (exit 0, *_REPORT_OK), úložiště má stejný SHA-256 před i po, zapsaly jen JSON + TXT do --out',
    $dCode === 0 && $cCode === 0 && str_contains($written, 'V71_DATA_REPORT_OK') && str_contains($written, 'V71_CONTENT_REPORT_OK') && $before === $after && count(glob($out . '/*/*') ?: []) === 4);
$check('reporty: ve výstupu ani v souborech nejsou jména žáků (' . count($labels) . ' jmen ze seznamu)', $labels !== [] && $leak === []);
exec($php . ' ' . escapeshellarg($ROOT . '/tools/v71_data_report.php') . ' --storage=' . escapeshellarg($tmp) . ' --out=' . escapeshellarg($ROOT . '/v71-report-test') . ' 2>&1', $x1, $c1);
exec($php . ' ' . escapeshellarg($ROOT . '/tools/v71_data_report.php') . ' --out=' . escapeshellarg($out . '/x') . ' 2>&1', $x2, $c2);
$check('reporty: --out v projektu i chybějící --storage odmítnou (exit 2) a nic nevytvoří; run_audits je nezahrnuje', $c1 === 2 && !is_dir($ROOT . '/v71-report-test') && $c2 === 2 && !is_dir($out . '/x')
    && array_filter(ra_discover_audits(__DIR__, 'v1', [], []), static fn(string $f): bool => str_contains($f, 'report')) === []);
foreach (glob($out . '/*/*') ?: [] as $f) @unlink($f);
foreach (glob($out . '/*') ?: [] as $d) @rmdir($d);
@rmdir($out);

// ---------------------------------------------------------------- 5) .htaccess a CSS
$ht = $read('materials/.htaccess');
$check('materials/.htaccess: komentáře jen „#“ (žádný řádek „<!--“), zákaz pro Apache 2.2 i 2.4 s <FilesMatch>', preg_match('/^\s*<!--/m', $ht) !== 1 && preg_match('/^\s*-->/m', $ht) !== 1
    && str_contains($ht, '<FilesMatch ".">') && substr_count($ht, 'Require all denied') >= 2 && str_contains($ht, 'Deny from all'), false);
$css = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/cockpit-v71.css'));
$check('CSS v71: jen tokeny (žádné pevné barvy), :focus-visible s akcentem, cíle ≥ 44 px, mobilní zalomení, ≤ 8 kB', preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', $css) !== 1
    && str_contains($css, 'outline: var(--ui-focus, 3px) solid var(--ui-accent)') && str_contains($css, 'min-height: 44px') && str_contains($css, '@media (max-width: 480px)') && strlen($read('assets/cockpit-v71.css')) <= 8192, false);

exit(audit_summary($state, 'V71_LESSON_MODEL'));

/** SHA-256 stromu úložiště (cesta + obsah) – report nesmí nic změnit. */
function rp71_audit_hash(string $dir): string
{
    $rows = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->isFile()) $rows[] = str_replace('\\', '/', substr($f->getPathname(), strlen($dir))) . '|' . hash_file('sha256', $f->getPathname());
    }
    sort($rows);
    return hash('sha256', implode("\n", $rows));
}
