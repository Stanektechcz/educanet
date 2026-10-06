<?php

declare(strict_types=1);

/**
 * EDUCANET v66 · HTTP audit (vestavěný server nad dočasným úložištěm, fiktivní žáci „Audit …“, lokální testovací klíč učitele se nikdy nevypisuje).
 *   php tools/v66_assessment_http_audit.php
 * Žák: pohled ?view=hodnoceni (vypnuto / řetězec důkazů bez známky / po převzetí se známkou, bez jmen spolužáků, CSS jen tam), jazyk EN, startovní test
 *   (stávající klíče výsledku beze změny, nové duration_s a student_id, XP beze změny), sumativní test lekce (jiné pořadí otázek, žádné XP) a formativní kontrola.
 * Učitel (legacy klíč): záložka hodnoceni66 (sekce, < 40 KB, CSS/JS jen tam), exporty CSV (BOM, středník, attachment, 403 pro cizí třídu), POST bez CSRF = 419,
 *   a66_set_kind, a66_recompute, g66_settings, g66_accept, p63_assign_student z ranního přehledu (stav žáka, log jen s hashi).
 * Konec: V66_ASSESSMENT_HTTP_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SESSION = [];
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v66-http')), '/');
$teacherKey = 'audit-v66-' . bin2hex(random_bytes(8));
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . $teacherKey);
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'identity_v58.php', 'linux_v57_lab.php', 'competencies_v62.php', 'evidence_v62.php', 'evidence_v62_adapters.php', 'mastery_v62.php', 'projects_v65.php',
    'paths_v63_class.php', 'paths_v63_flow.php', 'runtime_content.php', 'tutorial_v52.php', 'session_v53.php', 'learning_v56.php', 'assessment_v66_build.php', 'ops_v58.php', 'app/lib.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v62_competency_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));
$NOW = time();
$CLASS = V62FX_CLASS;
$ACTOR = str_repeat('b', 16);
v62fx_seed($tmp, $NOW);
$good = v62fx_key('good');
$player = v62fx_key('player');
$none = v62fx_key('none');
$goodId = (string)ev62_student_context($CLASS, $good)['id'];
$noneId = (string)ev62_student_context($CLASS, $none)['id'];
$fresh = static function (): void { unset($GLOBALS['educanet_runtime_indexes']); };
$questions = array_values((array)$GLOBALS['modules'][$CLASS]['questions']);
$rt = runtime_content_load_classes([$CLASS]);
$bundle = static fn(int $n): array => v56_lesson_bundle($CLASS, $GLOBALS['modules'][$CLASS], $n, $rt['nextLessons'], $rt['extendedLessons']);

$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_TEACHER_EXPORT_KEY' => $teacherKey, 'EDUCANET_TEACHER_ROLE' => 'admin']);
try {
    $jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
    $jars = [];
    $req = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h, $jarSwap, &$jars): array {
        $jarSwap($jars[$who] ?? []);
        try { return $h->request($method, $path, $fields, $headers); } finally { $jars[$who] = $jarSwap(null); }
    };
    $enter = static fn(string $who, string $label) => $req($who, 'GET', '/?class=' . V62FX_CLASS . '&student=' . rawurlencode($label));
    $sGet = static fn(string $who, string $path): array => $req($who, 'GET', $path);
    $sPost = static function (string $who, string $action, array $fields, string $page = '/?view=dashboard') use ($req, $h): array {
        $token = (string)$h->csrfToken($req($who, 'GET', $page)['body']);
        return $req($who, 'POST', '/', ['csrf' => $token, 'action' => $action] + $fields, ['follow_redirects' => false]);
    };
    /** Celkové XP žáka z hlavičky stránky (LVL n x / 250 XP): (n - 1) × 250 + x; anonymní profil se neukládá do úložiště, vidět je jen v hlavičce. */
    $xpOf = static function (string $who) use ($sGet): int {
        return preg_match('/LVL(\d+)\s*(\d+)\s*\/\s*250\s*XP/u', strip_tags($sGet($who, '/?view=dashboard')['body']), $m) === 1 ? ((int)$m[1] - 1) * 250 + (int)$m[2] : -1;
    };

    // 1) Žák: Moje hodnocení --------------------------------------------------------------------------------
    $enter('good', V62FX_LABELS['good']);
    $enter('player', V62FX_LABELS['player']);
    $off = $sGet('good', '/?view=hodnoceni');
    $check('HTTP žák: ?view=hodnoceni při vypnutém hodnocení vrátí 200 s informací a bez řetězce důkazů a známky', $off['status'] === 200 && str_contains($off['body'], '<h1>Moje hodnocení</h1>') && str_contains($off['body'], 'zatím nepočítá') && !str_contains($off['body'], 'a66-chain') && !str_contains($off['body'], 'a66-grade'));
    $dash = $sGet('good', '/?view=dashboard');
    $prof = $sGet('good', '/?view=profile');
    $check('HTTP žák: CSS v66 se na přehledu (dashboard) ani v profilu nenačítá, jen na ?view=hodnoceni', !str_contains($dash['body'], 'assessment-v66') && !str_contains($prof['body'], 'assessment-v66') && str_contains($off['body'], 'assessment-v66.css'));
    $dir = 'x';
    g66_settings_save($CLASS, ['enabled' => true, 'weights' => G66_DEFAULT_WEIGHTS, 'thresholds' => G66_DEFAULT_THRESHOLDS], $ACTOR, true, $NOW);
    a66_set_kind($CLASS, 'v56:l1', 'summative', $ACTOR, $NOW);
    foreach ([$good, $player, $none] as $k) ev62_sync_student($CLASS, $k, true, false, $NOW);
    $fresh();
    $on = $sGet('good', '/?view=hodnoceni');
    $check('HTTP žák: po zapnutí třídy vidí řetězec důkazů, rozpis zdrojů a text „Učitel zatím žádnou známku nepřevzal“, ale žádnou známku', $on['status'] === 200 && str_contains($on['body'], 'a66-chain') && str_contains($on['body'], 'Sumativní testy') && str_contains($on['body'], 'zatím žádnou známku nepřevzal') && !str_contains($on['body'], 'class="a66-grade"'));
    $proposal = g66_proposal($CLASS, $good, $NOW);
    $acc = g66_accept($CLASS, $good, (string)$proposal['proposal_hash'], $ACTOR, $NOW);
    $fresh();
    $after = $sGet('good', '/?view=hodnoceni');
    $other = $sGet('player', '/?view=hodnoceni');
    $check('HTTP žák: po převzetí učitelem vidí známku, spolužák (Hráč) ji nevidí a žádný z nich nevidí jména spolužáků ani štítky integrity', $acc['ok'] && str_contains($after['body'], 'class="a66-grade">' . $proposal['grade'] . '<') && !str_contains($other['body'], 'class="a66-grade"')
        && !str_contains($after['body'] . $other['body'], 'Audit Hrac') && !str_contains($after['body'] . $other['body'], 'Audit Dobry') && !str_contains($after['body'] . $other['body'], 'k ověření'));
    $token = (string)$h->csrfToken($req('good', 'GET', '/?view=hodnoceni')['body']);
    $req('good', 'POST', '/', ['csrf' => $token, 'action' => 'edu_set_lang', 'lang' => 'en', 'return' => '?view=hodnoceni'], ['follow_redirects' => false]);
    $en = $sGet('good', '/?view=hodnoceni');
    $req('good', 'POST', '/', ['csrf' => $token, 'action' => 'edu_set_lang', 'lang' => 'cs', 'return' => '?view=hodnoceni'], ['follow_redirects' => false]);
    $check('HTTP žák: anglické rozhraní překládá Moje hodnocení (My assessment, Grade, Evidence)', str_contains($en['body'], '<h1>My assessment</h1>') && str_contains($en['body'], 'Evidence') && !str_contains($en['body'], '<h1>Moje hodnocení</h1>'));

    // 2) Startovní test: klíče výsledku a XP -----------------------------------------------------------------
    $enter('none', V62FX_LABELS['none']);
    $sPost('none', 'start_test', [], '/?view=dashboard');
    foreach ($questions as $q) {
        $sPost('none', 'answer', ['answer' => (string)$q['correct']], '/?view=test');
        $sPost('none', 'continue_test', [], '/?view=test');
    }
    $fresh();
    $rows = storage_stream_rows('practice_results', static fn(array $r): bool => (string)($r['student_label'] ?? '') === V62FX_LABELS['none']);
    $row = $rows[0] ?? [];
    $oldKeys = ['id', 'class_id', 'student_label', 'student_email', 'auth_key', 'google_sub', 'started_at', 'finished_at', 'score', 'max_score', 'answers'];
    $check('HTTP startovní test: výsledek má všechny dosavadní klíče beze změny (' . count($oldKeys) . ') a skóre ' . count($questions) . ' / ' . count($questions), count($rows) === 1 && array_diff($oldKeys, array_keys($row)) === [] && (int)$row['score'] === count($questions) && (int)$row['max_score'] === count($questions) && count($row['answers']) === count($questions)
        && array_keys($row['answers'][0]) === ['question_id', 'selected', 'correct']);
    $check('HTTP startovní test: přibyla jen pole duration_s (celé číslo ≥ 0) a student_id (stabilní id žáka) a výsledek se hned započte do analýzy', array_values(array_diff(array_keys($row), $oldKeys)) === ['duration_s', 'student_id'] && is_int($row['duration_s']) && $row['duration_s'] >= 0 && $row['student_id'] === $noneId
        && a66_attempts($CLASS) !== [] && count(array_filter(a66_attempts($CLASS), static fn(array $a): bool => $a['sid'] === $noneId && $a['test'] === 'start')) === 1);
    $check('HTTP startovní test: XP beze změny – 25 za dokončení a 10 za každou ze ' . count($questions) . ' správných odpovědí = ' . (25 + 10 * count($questions)) . ' XP v hlavičce žáka', $xpOf('none') === 25 + 10 * count($questions));

    // 3) Sumativní test lekce za běhu -----------------------------------------------------------------------
    $doTheory = static function (string $who, int $n) use ($sPost, $bundle): void {
        foreach (array_keys((array)$bundle($n)['topics']) as $topic) $sPost($who, 'v56_theory_done', ['lesson' => (string)$n, 'topic' => (string)$topic], '/?view=lekce&n=' . $n . '&faze=theory');
    };
    $parse = static function (string $html): array {
        preg_match_all('/<fieldset class="v56-q"[^>]*>(.*?)<\/fieldset>/s', $html, $fs);
        $out = [];
        foreach ($fs[1] as $f) {
            preg_match('/<legend><span>\d+<\/span>(.*?)<\/legend>/s', $f, $lg);
            preg_match_all('/<input type="radio" name="answers\[(\d+)\]" value="([^"]+)" required><span>(.*?)<\/span>/s', $f, $op, PREG_SET_ORDER);
            $out[] = ['text' => html_entity_decode(trim($lg[1] ?? '')), 'index' => $op[0][1] ?? '', 'options' => array_map(static fn(array $o): array => ['value' => $o[2], 'text' => html_entity_decode($o[3])], $op)];
        }
        return $out;
    };
    $answerFromPage = static function (array $parsed, array $lessonQuestions): array {
        $byText = [];
        foreach ($lessonQuestions as $q) $byText[(string)$q['question']] = $q;
        $fields = [];
        foreach ($parsed as $p) {
            $q = $byText[$p['text']] ?? null;
            if ($q === null) continue;
            $correctText = (string)$q['options'][$q['correct']];
            foreach ($p['options'] as $o) if ($o['text'] === $correctText) $fields['answers[' . $p['index'] . ']'] = $o['value'];
        }
        return $fields;
    };
    $doTheory('none', 1);
    $doTheory('player', 1);
    $doTheory('none', 3);
    $pageG = $sGet('none', '/?view=lekce&n=1&faze=test');
    $pageP = $sGet('player', '/?view=lekce&n=1&faze=test');
    $parsedG = $parse($pageG['body']);
    $parsedP = $parse($pageP['body']);
    $lq1 = $bundle(1)['questions'];
    $check('HTTP sumativní test: test lekce 1 označený učitelem se vykreslí (200) s všemi otázkami a každý žák má jiné pořadí otázek, stejný žák při obnovení stejné', $pageG['status'] === 200 && count($parsedG) === count($lq1) && count($parsedP) === count($lq1)
        && array_column($parsedG, 'text') !== array_column($parsedP, 'text') && array_column($parsedG, 'text') === array_column($parse($sGet('none', '/?view=lekce&n=1&faze=test')['body']), 'text'));
    $fields = $answerFromPage($parsedG, $bundle(1)['questions']);
    $xpBeforeSummative = $xpOf('none');
    $subm = $sPost('none', 'v56_test_submit', ['lesson' => '1'] + $fields, '/?view=lekce&n=1&faze=test');
    $fresh();
    $stored = storage_read(STORAGE_DIR . '/progress_v56.json.php')[$CLASS . '|' . $none]['1']['test'] ?? [];
    $xpAfterSummative = $xpOf('none');
    $check('HTTP sumativní test: všechny odpovědi v pořadí žáka vyhodnoceny na 100 % a sumativní test nedal žádné XP (XP v hlavičce před a po odevzdání stejné)', (int)($stored['percent'] ?? -1) === 100 && count($fields) === count($lq1) && $xpAfterSummative === $xpBeforeSummative && $xpBeforeSummative > 0);
    $pageAfter = $sGet('none', '/?view=lekce&n=1&faze=test');
    $check('HTTP sumativní test: po odevzdání ukáže výsledek 100 % a stránka s výsledkem se vykreslí bez chyby', $pageAfter['status'] === 200 && str_contains($pageAfter['body'], '100 %') && audit_response_clean($pageAfter));
    $pageF = $sGet('none', '/?view=lekce&n=3&faze=test');
    $parsedF = $parse($pageF['body']);
    $lq3 = $bundle(3)['questions'];
    $check('HTTP formativní test (lekce 3): pořadí otázek je původní a odevzdání dá XP za test (kontrolní větev)', array_column($parsedF, 'text') === array_map(static fn(array $q): string => (string)$q['question'], $lq3));
    $fieldsF = $answerFromPage($parsedF, $lq3);
    $xpBeforeFormative = $xpOf('none');
    $sPost('none', 'v56_test_submit', ['lesson' => '3'] + $fieldsF, '/?view=lekce&n=3&faze=test');
    $fresh();
    $check('HTTP formativní test: úspěšný test lekce 3 stále dává XP za test (V56_XP[test])', $xpOf('none') - $xpBeforeFormative === V56_XP['test']);

    // 4) Učitel: záložka, exporty, akce -----------------------------------------------------------------------
    $login = audit_login_teacher($h, $teacherKey);
    $tcsrf = (string)$login['csrf'];
    $check('HTTP učitel: přihlášení lokálním testovacím klíčem uspělo a CSRF token je k dispozici', $tcsrf !== '' && $login['response']['status'] === 200);
    $tGet = static fn(string $path): array => $h->request('GET', $path, [], ['follow_redirects' => false]);
    $tPost = static fn(string $action, array $fields, ?string $csrf = null): array => $h->request('POST', '/teacher.php', ['action' => $action, 'csrf' => $csrf ?? $tcsrf] + $fields, ['follow_redirects' => false]);
    a66_recompute_class($CLASS, $NOW);
    m66_build($CLASS, $NOW);
    /** HTML samotné záložky (od panelu v66 po poslední </section>), bez obalu cockpitu a navigace. */
    $fragment = static function (string $html): string {
        $from = strpos($html, '<section class="teacher-panel a66-wrap">');
        $to = strrpos($html, '</section>');
        return $from === false || $to === false ? '' : substr($html, $from, $to - $from + 10);
    };
    $tab = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a');
    $check('HTTP učitel: záložka hodnoceni66 (200, HTML záložky ' . strlen($fragment($tab['body'])) . ' B < 40 KB, celá stránka ' . strlen($tab['body']) . ' B) obsahuje všechny sekce a načítá CSS i JS (defer) jen na této záložce', $tab['status'] === 200 && $fragment($tab['body']) !== '' && strlen($fragment($tab['body'])) < 40960 && str_contains($tab['body'], 'Ráno: co udělat dnes') && str_contains($tab['body'], 'Položková analýza')
        && str_contains($tab['body'], 'Druhy testů') && str_contains($tab['body'], 'Návrh hodnocení') && preg_match('~assets/(cx-)?assessment-v66\.css~', $tab['body']) === 1 /* v68: cockpit načítá odvozenou kopii cx-assessment-v66.css */ && preg_match('/<script src="assets\/assessment-v66\.js[^"]*" defer>/', $tab['body']) === 1 && audit_response_clean($tab));
    $other = $tGet('/teacher.php?tab=prehled&class=class_3a');
    $check('HTTP učitel: jiná záložka (Přehled třídy) nenačítá CSS ani JS v66', $other['status'] === 200 && !str_contains($other['body'], 'assessment-v66'));
    $itemsCsv = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a&export=items');
    $hdr = array_change_key_case($itemsCsv['headers'], CASE_LOWER);
    $check('HTTP učitel: export položek je CSV attachment s UTF-8 BOM a středníkem, nosniff a no-store', $itemsCsv['status'] === 200 && str_starts_with($itemsCsv['body'], "\xEF\xBB\xBF") && str_contains($itemsCsv['body'], ';') && str_contains((string)($hdr['content-type'] ?? ''), 'text/csv')
        && str_contains((string)($hdr['content-disposition'] ?? ''), 'attachment') && ($hdr['x-content-type-options'] ?? '') === 'nosniff' && str_contains((string)($hdr['cache-control'] ?? ''), 'no-store'));
    $propCsv = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a&export=proposals');
    $check('HTTP učitel: export návrhů je CSV s BOM a obsahuje převzatou známku žáka', $propCsv['status'] === 200 && str_starts_with($propCsv['body'], "\xEF\xBB\xBF") && str_contains($propCsv['body'], 'Převzatá známka') && str_contains($propCsv['body'], '"Audit Dobry";'));
    $badClass = $tGet('/teacher.php?tab=hodnoceni66&class=class_xx&export=items');
    $badKind = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a&export=hesla');
    $noClass = $tGet('/teacher.php?tab=hodnoceni66&export=items');
    $check('HTTP učitel: export pro neznámou třídu, neznámý druh exportu i bez třídy skončí 403 a bez dat', $badClass['status'] === 403 && $badKind['status'] === 403 && $noClass['status'] === 403 && !str_contains($badClass['body'] . $badKind['body'], 'Audit'));
    $noCsrf = $tPost('a66_set_kind', ['class_id' => $CLASS, 'test' => 'v56:l5', 'kind' => 'summative'], 'podvrzeny');
    $emptyCsrf = $h->request('POST', '/teacher.php', ['action' => 'a66_set_kind', 'class_id' => $CLASS, 'test' => 'v56:l5', 'kind' => 'summative'], ['follow_redirects' => false]);
    $fresh();
    $check('HTTP učitel: POST se špatným i chybějícím CSRF tokenem se odmítne (419) a druh testu se nezmění', $noCsrf['status'] === 419 && $emptyCsrf['status'] === 419 && !a66_is_summative($CLASS, 'v56:l5'));
    $kind = $tPost('a66_set_kind', ['class_id' => $CLASS, 'test' => 'v56:l5', 'kind' => 'summative']);
    $fresh();
    $startKind = $tPost('a66_set_kind', ['class_id' => $CLASS, 'test' => 'start', 'kind' => 'summative']);
    $fresh();
    $check('HTTP učitel: a66_set_kind uloží sumativní test lekce 5 (303), startovní test sumativní nepůjde', $kind['status'] === 303 && a66_is_summative($CLASS, 'v56:l5') && !a66_is_summative($CLASS, 'start'));
    $rec = $tPost('a66_recompute', ['class_id' => $CLASS]);
    $fresh();
    $check('HTTP učitel: a66_recompute přepočítá cache (303), položková analýza i návrhy se zapíšou', $rec['status'] === 303 && is_file(a66_items_path($CLASS)) && g66_class_cache($CLASS) !== []);
    $settingsBad = $tPost('g66_settings', ['class_id' => $CLASS, 'enabled' => '1', 'w_summative_test' => '50', 'w_mastery' => '40', 'w_project_v65' => '20', 't1' => '90', 't2' => '75', 't3' => '50', 't4' => '30']);
    $fresh();
    $settingsOk = $tPost('g66_settings', ['class_id' => 'class_1a', 'enabled' => '1', 'w_summative_test' => '30', 'w_mastery' => '50', 'w_project_v65' => '20', 't1' => '95', 't2' => '80', 't3' => '55', 't4' => '35']);
    $fresh();
    $check('HTTP učitel (admin v režimu klíče): g66_settings s neplatnou vahou (součet 110) se nepřijme, platné nastavení 1.A se uloží a ostatní třídy zůstanou', $settingsBad['status'] === 303 && g66_settings($CLASS)['weights'] === G66_DEFAULT_WEIGHTS && $settingsOk['status'] === 303 && g66_enabled('class_1a')
        && g66_settings('class_1a')['weights']['mastery'] === 50 && g66_settings('class_1a')['thresholds'] === [95, 80, 55, 35] && !g66_enabled('class_2a'));
    $stale = $tPost('g66_accept', ['class_id' => $CLASS, 'student_hash' => g66_key_hash($none), 'proposal_hash' => str_repeat('0', 16)]);
    $fresh();
    $check('HTTP učitel: převzetí se zastaralým hashem se nepřijme (žák Nikdo nemá převzatou známku)', $stale['status'] === 303 && g66_accepted($CLASS, $noneId) === null);
    $paths = p63_paths_for_class($CLASS);
    $pathId = (string)array_key_first($paths);
    p63_memo_reset();
    $before = p63_state($noneId)['assigned'];
    $assign = $tPost('p63_assign_student', ['class_id' => $CLASS, 'path' => $pathId, 'student_hash' => g66_key_hash($none), 'from' => 'morning66']);
    $fresh();
    $location = (string)($assign['headers']['location'] ?? ($assign['headers']['Location'] ?? ''));
    $check('HTTP učitel: p63_assign_student z ranního přehledu přiřadí cestu jen vybranému žákovi (303 zpět na hodnoceni66) a zapíše log jen s hashi', $assign['status'] === 303 && $before === [] && isset(p63_state($noneId)['assigned'][$pathId]) && !isset(p63_state($goodId)['assigned'][$pathId])
        && str_contains($location, 'hodnoceni66') && count(m66_log_rows()) === 1 && m66_log_rows()[0]['student_hash'] === g66_key_hash($none) && !str_contains((string)file_get_contents(m66_log_path()), 'Audit'));
    $crossPath = $tPost('p63_assign_student', ['class_id' => $CLASS, 'path' => (string)array_key_first(p63_paths_for_class('class_1a')), 'student_hash' => g66_key_hash($good)]);
    $unknownStudent = $tPost('p63_assign_student', ['class_id' => $CLASS, 'path' => $pathId, 'student_hash' => str_repeat('0', 24)]);
    $fresh();
    $check('HTTP učitel: cesta jiné třídy ani neznámý žák se nepřiřadí (stav žáka Dobrý beze změny)', !isset(p63_state($goodId)['assigned'][array_key_first(p63_paths_for_class('class_1a'))]) && !isset(p63_state($goodId)['assigned'][$pathId]) && $crossPath['status'] !== 500 && $unknownStudent['status'] !== 500);
    $zak = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a&zak=' . g66_key_hash($good));
    $check('HTTP učitel: detail žáka (?zak=) ukáže řetězec důkazů a stav (200)', $zak['status'] === 200 && str_contains($zak['body'], 'Řetězec důkazů') && audit_response_clean($zak));
    $stats = $tGet('/teacher.php?tab=hodnoceni66&class=class_3a');
    $check('HTTP učitel: záložka po zásazích dál < 40 KB (' . strlen($fragment($stats['body'])) . ' B) a bez PHP varování', strlen($fragment($stats['body'])) < 40960 && audit_response_clean($stats));
} finally {
    $h->stop();
}
exit(audit_summary($state, 'V66_ASSESSMENT_HTTP'));
