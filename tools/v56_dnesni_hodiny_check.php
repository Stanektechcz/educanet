<?php
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
$root = dirname(__DIR__);
chdir($root);
require $root . '/bootstrap.php';
require $root . '/runtime_content.php';
require_once $root . '/tutorial_v52.php';
require_once $root . '/tutorial_v52_views.php';
require_once $root . '/intake_v51.php';
require_once $root . '/accounts_v53.php';
require_once $root . '/points_v53.php';
require_once $root . '/session_v53.php';
require_once $root . '/student_v55.php';
require_once $root . '/learning_v56.php';
$runtime = runtime_content_load_classes(array_keys($modules));
$nextLessons = $runtime['nextLessons'];
$extendedLessons = $runtime['extendedLessons'];
$GLOBALS['nextLessons'] = $nextLessons;
$GLOBALS['extendedLessons'] = $extendedLessons;
$schoolYear = require $root . '/school_year.php';
$today = date('Y-m-d');

$ok = 0; $bad = 0;
$say = static function (bool $good, string $text) use (&$ok, &$bad): void {
    if ($good) $ok++; else $bad++;
    echo ($good ? '  OK   ' : '  CHYBA') . '  ' . $text . PHP_EOL;
};

echo "DNEŠNÍ DEN: " . tut52_cz_date($today) . "\n";

$order = [];
foreach ($modules as $cid => $m) {
    $s = adaptive_class_schedule($schoolYear, (string)$cid);
    $order[(string)$cid] = (string)($s['start'] ?? '');
}
asort($order);

foreach ($order as $cid => $start) {
    $module = $modules[$cid];
    $sched = adaptive_class_schedule($schoolYear, $cid);
    $win = v55_block_window($schoolYear, $cid, $today);
    echo "\n" . str_repeat('=', 74) . "\n";
    printf("%s · %s\n%s · %s. hodina · učebna %s\n", $module['name'], (string)$module['subject'],
        $win['range'], implode('.–', $win['periods']), (string)$sched['room']);
    echo str_repeat('-', 74) . "\n";

    // kalendář
    $calNo = 0; $calStatus = '';
    foreach (adaptive_school_year_rows($schoolYear, $cid) as $r) {
        if ((string)($r['date'] ?? '') !== $today) continue;
        $calNo = (int)($r['lesson_number'] ?? 0);
        $calStatus = (string)($r['status'] ?? '');
    }
    $say($calStatus === 'teaching', "kalendář: dnes je vyučovací den" . ($calNo ? " (lekce {$calNo})" : ''));

    // hodina
    $sess = sess53_for_class_date($cid, $today);
    $say(is_array($sess) && !empty($sess['open']), 'hodina je otevřená' . (is_array($sess) ? ' · kód ' . (string)$sess['code'] : ''));
    if (!is_array($sess)) continue;
    $say((int)$sess['lesson_number'] === $calNo, "číslo lekce v hodině odpovídá kalendáři ({$sess['lesson_number']} = {$calNo})");

    if ((string)$sess['kind'] === 'intake') {
        $classes = intake_v51_classes($modules);
        $room = $classes[$cid] ?? null;
        $seats = 0;
        foreach (intake_v51_seat_map($room ?? []) as $rowSeats) {
            foreach ($rowSeats as $desk) foreach (['left', 'right'] as $sd) if (!empty($desk[$sd]['active'])) $seats++;
        }
        $say(is_array($room), 'zasedací pořádek pro výběr místa');
        $say($seats >= 20, "volných míst v učebně: {$seats}");
        $intakeHtml = (string)@file_get_contents($root . "/intake_v51_views.php"); $say(substr_count($intakeHtml, "intake_v51_step_open(") >= 5, "dotazník má " . substr_count($intakeHtml, "intake_v51_step_open(") . " sekcí");
        echo "  →    Žák: přihlásí se → zadá kód {$sess['code']} → jméno, e-mail, místo → dotazník\n";
        continue;
    }

    $b = v56_lesson_bundle($cid, $module, (int)$sess['lesson_number'], $nextLessons, $extendedLessons);
    printf("  Lekce %d · %s\n", (int)$b['number'], (string)$b['title']);

    // teorie
    $topicsOk = true;
    foreach ((array)$b['topics'] as $k => $t) {
        if (trim((string)$t['summary']) === '' || !(array)$t['body']) $topicsOk = false;
    }
    $say(count((array)$b['topics']) >= 3 && $topicsOk, 'teorie: ' . count((array)$b['topics']) . ' témat s výkladem i příklady');
    $scenes = [];
    foreach (array_keys((array)$b['topics']) as $k) $scenes[] = tut52_scene((string)$k, (string)$b['family']);
    $sceneList = (array)(json_decode((string)@file_get_contents($root . '/assets/tutorial-v52.js'), true) ?? []);
    $say(count(array_filter($scenes)) === count($scenes), 'každé téma má animovanou ukázku (' . implode(', ', array_unique($scenes)) . ')');

    // test
    $qOk = true;
    foreach ((array)$b['questions'] as $q) {
        if (count((array)($q['options'] ?? [])) < 3 || !isset(($q['options'] ?? [])[$q['correct'] ?? ''])) $qOk = false;
        if (trim((string)($q['explanation'] ?? '')) === '') $qOk = false;
    }
    $say(count((array)$b['questions']) >= 5 && $qOk, 'test: ' . count((array)$b['questions']) . ' otázek se správnou odpovědí i vysvětlením');

    // projekt
    $mins = 0; $stepsOk = true;
    foreach ((array)$b['steps'] as $st) {
        if (preg_match('/(\d+)/', (string)$st['time'], $m)) $mins += (int)$m[1]; else $stepsOk = false;
        if (!(array)$st['tasks']) $stepsOk = false;
    }
    $say(count((array)$b['steps']) >= 3 && $stepsOk, 'projekt: ' . count((array)$b['steps']) . ' kroků s dílčími úkoly');
    $say($mins >= 35 && $mins <= $win['total_minutes'], "projekt trvá {$mins} min (blok má {$win['total_minutes']} min)");
    $say(count((array)$sess['tasks']) === count((array)$b['steps']), 'učitel vidí stejné kroky jako žák');
    $say(count((array)$b['tools']) >= 2, 'programy: ' . implode(', ', array_map(static fn(array $t): string => (string)$t['name'], (array)$b['tools'])));

    // bonus
    $bon = v55_bonus_variant($win['total_minutes'] - $mins);
    $say((string)$bon['id'] !== 'none', "bonus po dokončení: {$bon['label']} ({$bon['minutes']} min)");
}

echo "\n" . str_repeat('=', 74) . "\n";
$codes = [];
foreach (sess53_all() as $r) if (is_array($r) && (string)$r['date'] === $today) $codes[(string)$r['code']] = (string)$r['class_id'];
$say(count($codes) === count($modules), 'kódy hodin jsou unikátní: ' . implode(', ', array_map(static fn($c, $k): string => $modules[$c]['name'] . '=' . $k, $codes, array_keys($codes))));
$accounts = local_accounts();
$withClass = array_filter($accounts, static fn($a) => is_array($a) && !empty($a['class_id']));
$say(count($withClass) >= count(student_directory()), 'účty žáků: ' . count($withClass));
$say(teacher_export_configured(), 'učitelský přístup je nastavený');
$work = load_php_json(sess53_path('lesson_session_work'));
$n = 0; foreach ((array)$work as $s) $n += count((array)$s);
$p = load_php_json(v56_progress_path());
$m = 0; foreach ((array)$p as $s) $m += count((array)$s);
$say($n === 0 && $m === 0, "žádná zbytková testovací data (odevzdání {$n}, postupů {$m})");

echo "\nSOUHRN: {$ok} v pořádku, {$bad} k řešení\n";
exit($bad === 0 ? 0 : 1);
