<?php

declare(strict_types=1);

/**
 * EDUCANET v61 · behaviorální audit části E (učitel a správa).
 *   php tools/v61_teacher_audit.php
 * Dočasné úložiště (edu_audit_temp_storage) – nikdy nečte/nezapisuje ostrou storage/. Fiktivní žáci „Audit …“.
 * Pokrývá: pravidla zaostávání (7 dní / nízké mastery / zdroje aktivity, odměna učitele se nepočítá jako aktivita),
 * rozsah (učitel nevidí cizí třídy ani v CSV, per položka u hromadného potvrzení), asistent nezapisuje, hromadné
 * potvrzení po položkách a idempotentní (odměna jednou), CSV (BOM, ochrana proti injection), CLI přeřazení dat
 * (--dry-run výchozí a bez zápisu, --apply bez zálohy odmítnut), odkaz .ics v dosažitelném kalendáři, vyřazený
 * calendar_classic.php (neexistuje a nikde se nečte).
 * Konec: V61_TEACHER_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v61-teacher')), '/');
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'intake_v51.php', 'accounts_v53.php', 'linux_v57_lab.php', 'teacher_v58.php', 'teacher_accounts_v59_admin.php',
    'points_v53.php', 'points_v60.php', 'marketplace_v60.php', 'projects_v60.php', 'feedback_v60.php', 'teacher_class_dashboard.php',
    'teacher_overview_v61.php', 'teacher_overview_v61_views.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));

$GLOBALS['modules'] = ['class_1a' => ['course_type' => 'graphics'], 'class_2a' => ['course_type' => 'graphics'], 'class_3a' => ['course_type' => 'networks'], 'class_4a' => ['course_type' => 'networks']];
teacher59_all_modules($GLOBALS['modules']);
$A = 'class_3a';
$B = 'class_4a';
$now = time();
$ago = static fn(int $days): string => date(DATE_ATOM, $now - $days * 86400 - 600);

// --- 0) Pravidla zaostávání (čistá funkce) ---------------------------------------------------------------
$day = 86400;
$check('pravidlo: aktivita před 8 dny = zaostává, před 6 dny ne, přesně 7 dní ano',
    ov61_lag_reasons($now - 8 * $day, null, false, $now) !== [] && ov61_lag_reasons($now - 6 * $day, null, false, $now) === [] && ov61_lag_reasons($now - 7 * $day, null, false, $now) !== []);
$check('pravidlo: žádná aktivita nikdy = zaostává (důvod uvádí „Zatím žádná aktivita“)', ov61_lag_reasons(0, null, false, $now) === ['Zatím žádná aktivita']);
$check('pravidlo: mastery 39,9 % s daty zaostává, 40 % ne, nízké mastery BEZ dat (nic nezačal) ne',
    ov61_lag_reasons($now, 39.9, true, $now) !== [] && ov61_lag_reasons($now, 40.0, true, $now) === [] && ov61_lag_reasons($now, 5.0, false, $now) === []);
$both = ov61_lag_reasons($now - 9 * $day, 10.0, true, $now);
$check('pravidlo: oba důvody se uvedou zvlášť', count($both) === 2 && str_contains($both[0], '9') && str_contains($both[1], 'mastery'));

// --- Fixture: roster, aktivita, mastery, hlášení, přihlášky, nákupy, účty --------------------------------
$labels3 = ['Audit Lenochod', 'Audit Aktivni', 'Audit Nikdy', 'Audit Slaby', 'Audit Dobry', 'Audit Odmena', 'Audit Labak', 'Audit Beze Dat', '=1+1 Audit', '@Zavinac Audit', '+Plus Audit', '-Minus Audit'];
$labels4 = ['Audit Ctvrta'];
@mkdir($tmp . '/intake', 0700, true);
$rosterRows = [];
foreach ([$A => $labels3, $B => $labels4] as $cid => $list) {
    foreach ($list as $i => $label) {
        [$first, $last] = explode(' ', $label, 2);
        $rosterRows[] = ['id' => 'fx61t_' . $cid . '_' . $i, 'class_id' => $cid, 'submitted_at' => date(DATE_ATOM), 'student' => ['first_name' => $first, 'last_name' => $last, 'preferred_name' => '', 'seat_id' => '', 'seat_label' => ''],
            'answers' => [], 'assessment' => [], 'source' => 'fixture', 'imported_at' => date(DATE_ATOM)];
    }
}
file_put_contents($tmp . '/intake/responses.json.php', "<?php http_response_code(403); exit; ?>\n" . json_encode($rosterRows, JSON_UNESCAPED_UNICODE));
$key = static fn(string $cid, string $label): string => project_student_key($cid, $label);
$seedProfile = static function (string $cid, string $label, string $updatedAt, array $events = []): void {
    $k = fb60_learning_key($cid, project_student_key($cid, $label));
    storage_update(learning_profiles_path(), static function (array $all) use ($k, $updatedAt, $events): array {
        $all[$k] = ['xp' => 10, 'events' => $events, 'kb' => [], 'studio' => [], 'journey' => [], 'badges' => [], 'achievements' => [], 'updated_at' => $updatedAt, 'version' => 1];
        return $all;
    });
};
$seedProfile($A, 'Audit Lenochod', $ago(10));
$seedProfile($A, 'Audit Aktivni', $ago(0), ['fx:today' => ['xp' => 5, 'at' => $ago(0)]]);
$seedProfile($A, 'Audit Slaby', $ago(0));
$seedProfile($A, 'Audit Dobry', $ago(1));
$seedProfile($A, 'Audit Odmena', $ago(12), ['fx:old' => ['xp' => 5, 'at' => $ago(12)]]);
$seedProfile($A, 'Audit Labak', $ago(20));
$seedProfile($A, 'Audit Beze Dat', $ago(1));
foreach (['=1+1 Audit', '@Zavinac Audit', '+Plus Audit', '-Minus Audit'] as $evil) $seedProfile($A, $evil, $ago(1));
$seedProfile($B, 'Audit Ctvrta', $ago(30));
storage_update(STORAGE_DIR . '/lab_v57_events.json.php', static function (array $rows) use ($A, $key, $ago): array {
    $rows[] = ['id' => 'fx61t_ev1', 'class_id' => $A, 'student_key' => $key($A, 'Audit Labak'), 'kind' => 'solve', 'at' => $ago(1)];
    return $rows;
});
$branches = skill_relevant_branches($A);
$seedMastery = static function (string $label, float $percent, int $started) use ($A, $branches): void {
    $skillKey = skill_student_key_for_label($A, $label);
    $rows = [];
    foreach ($branches as $i => $branch) {
        $rows[$A . '|' . $skillKey . '|' . $branch] = ['class_id' => $A, 'student_key' => $skillKey, 'branch' => $branch, 'mastery_percent' => $percent, 'mastery_level' => 'developing',
            'skills_total' => 10, 'skills_started' => $i === 0 ? $started : 0, 'updated_at' => date(DATE_ATOM)];
    }
    skill_store_merge('branches', $rows);
};
$seedMastery('Audit Slaby', 25.0, 3);
$seedMastery('Audit Dobry', 80.0, 5);
$seedMastery('Audit Beze Dat', 0.0, 0);
unset($GLOBALS['educanet_runtime_indexes']);

$good = static fn(string $title, string $type = 'bug') => ['type' => $type, 'title' => $title, 'description' => 'Tlačítko na stránce nereaguje, když na něj kliknu.', 'page' => 'lab'];
$rep3a1 = (string)fb60_submit($A, $key($A, 'Audit Odmena'), $good('Audit hlášení jedna tři A'))['id'];
$rep3a2 = (string)fb60_submit($A, $key($A, 'Audit Aktivni'), $good('Audit návrh dvě tři A', 'improvement'))['id'];
$rep3a3 = (string)fb60_submit($A, $key($A, 'Audit Dobry'), $good('Audit hlášení tři tři A'))['id'];
$rep4a = (string)fb60_submit($B, $key($B, 'Audit Ctvrta'), $good('Audit hlášení čtvrtá cizí'))['id'];
$project3a = (string)proj60_save('', ['title' => 'Audit projekt tři A', 'reward_type' => 'other', 'min_level' => 1, 'capacity' => 5, 'classes' => [$A], 'status' => 'open'], [$A], 'audit');
$app3a = (string)(proj60_apply($A, $key($A, 'Audit Dobry'), $project3a, 'Chci to zkusit.', 9)['application_id'] ?? '');
$shopItem = (string)mkt60_save_item('', ['type' => 'other', 'title' => 'Audit položka obchodu', 'price' => 5, 'classes' => [$A], 'active' => true], [$A], 'audit');
$buyer = $key($A, 'Audit Aktivni');
pts53_save_wallet($A, $buyer, static function (array $w) use ($shopItem, $ago): array {
    $w['earned'] = 100;
    $w['purchases']['mkt:recent'] = ['cost' => 5, 'reason' => 'Obchod: Audit položka obchodu', 'item_id' => $shopItem, 'qty' => 1, 'at' => $ago(2)];
    $w['purchases']['mkt:refunded'] = ['cost' => 5, 'reason' => 'Obchod: vrácený', 'item_id' => $shopItem, 'qty' => 1, 'at' => $ago(2)];
    $w['purchases']['mkt:old'] = ['cost' => 5, 'reason' => 'Obchod: starý', 'item_id' => $shopItem, 'qty' => 1, 'at' => $ago(40)];
    $w['awards']['refund:mkt:refunded'] = ['points' => 5, 'reason' => 'Vráceno', 'at' => $ago(1)];
    return $w;
});
$acc = v59sf_create_accounts();
$rep2a = (string)fb60_submit('class_2a', $key('class_2a', 'Audit Druha'), $good('Audit hlášení druhá A'))['id'];
$asId = static function (?string $id): void {
    unset($_SESSION['teacher59']);
    if ($id !== null) {
        $account = teacher59_account($id);
        $_SESSION['teacher59'] = ['id' => $id, 'sv' => (int)($account['session_version'] ?? 0), 'seen_at' => time(), 'login_at' => time()];
    }
    teacher59_reset_cache();
};
$rows = static function (string $cid) : array {
    $out = [];
    foreach (ov61_students($cid) as $r) $out[$r['label']] = $r;
    return $out;
};

// --- 1) Zaostávající žáci podle skutečných zdrojů --------------------------------------------------------
$asId($acc['id']['admin']);
$s3 = $rows($A);
$lag = static fn(string $label): bool => (bool)($s3[$label]['lagging'] ?? false);
$check('zdroje: 10 dní starý profil = zaostává (Bez aktivity 10 dní)', $lag('Audit Lenochod') && in_array('Bez aktivity 10 dní', $s3['Audit Lenochod']['reasons'], true));
$check('zdroje: žák bez jakékoli aktivity zaostává', $lag('Audit Nikdy') && $s3['Audit Nikdy']['last'] === 0);
$check('zdroje: aktivní žák bez mastery dat a žák s mastery 80 % nezaostávají', !$lag('Audit Aktivni') && !$lag('Audit Dobry') && !$lag('Audit Beze Dat'));
$check('zdroje: aktivní žák s mastery 25 % (začal 3 dovednosti) zaostává kvůli mastery', $lag('Audit Slaby') && str_contains(implode(' ', $s3['Audit Slaby']['reasons']), 'Nízké mastery (25 %)') && $s3['Audit Slaby']['days'] === 0);
$check('zdroje: událost Linux Labu před 1 dnem vyruší starý profil (nezaostává)', !$lag('Audit Labak') && (int)$s3['Audit Labak']['days'] <= 1);
$check('řazení: zaostávající jsou v seznamu před ostatními', (static function () use ($A): bool {
    $seenOk = false;
    foreach (ov61_students($A) as $r) { if (!$r['lagging']) $seenOk = true; elseif ($seenOk) return false; }
    return true;
})());

// --- 2) Čekající věci -------------------------------------------------------------------------------------
$pendingIds = array_column(ov61_pending_reports($A), 'id');
$check('čeká: nová hlášení třídy 3.A (3 ks) a ne hlášení 4.A', in_array($rep3a1, $pendingIds, true) && in_array($rep3a2, $pendingIds, true) && in_array($rep3a3, $pendingIds, true) && !in_array($rep4a, $pendingIds, true));
$check('čeká: přihláška na projekt (interested) je vidět, projekt je pojmenovaný', in_array($app3a, array_column(ov61_pending_applications($A), 'id'), true) && ov61_pending_applications($A)[0]['project']['title'] === 'Audit projekt tři A');
$buys = ov61_recent_purchases($A);
$check('čeká: nedávný nevrácený nákup ano; vrácený a starší než 14 dní ne', count(array_filter($buys, static fn(array $b): bool => $b['title'] === 'Audit položka obchodu')) === 1 && count($buys) === 1);

// --- 3) Rozsah: učitel nevidí cizí třídy ----------------------------------------------------------------
$asId($acc['id']['b']);
$check('rozsah: B (3.A + 4.A) má obě třídy a vidí hlášení 3.A i 4.A zvlášť', ov61_allowed_classes() === ['class_3a', 'class_4a'] && in_array($rep4a, array_column(ov61_pending_reports($B), 'id'), true));
$asId($acc['id']['a']);
$check('rozsah: A (1.A + 2.A) nemá 3.A ani 4.A', ov61_allowed_classes() === ['class_1a', 'class_2a'] && !ov61_can_class($A) && ov61_pick_class($A) === 'class_1a');
$overviewForeign = ov61_overview($A);
$check('rozsah: přehled cizí třídy je prázdný (žáci, hlášení, přihlášky, nákupy)', $overviewForeign['students'] === [] && $overviewForeign['reports'] === [] && $overviewForeign['applications'] === [] && $overviewForeign['purchases'] === []);
$check('rozsah: CSV cizí třídy má jen záhlaví a žádné jméno', count(ov61_csv_rows($A)) === 1 && !str_contains(ov61_csv_body($A), 'Audit'));
$asId($acc['id']['s']);
$check('rozsah: asistent 3.A nevidí 4.A', ov61_can_class($A) && !ov61_can_class($B) && ov61_students($B) === []);
$asId(null);
$check('rozsah: bez přihlášeného učitele (režim účtů) není žádná třída', ov61_allowed_classes() === [] && ov61_pick_class($A) === '');

// --- 4) Hromadné potvrzení ----------------------------------------------------------------------------------
$asId($acc['id']['a']);
$ownA = $rep2a;
$balanceDrew = pts53_balance('class_2a', $key('class_2a', 'Audit Druha'));
$mixed = ov61_bulk_confirm([$ownA, $rep3a1, 'fb60_000000000000', 'neplatne-id', ['pole']], 'class_2a', 'audit-a', false);
$byId = array_column($mixed, null, 'id');
$check('hromadné: vlastní hlášení potvrzeno, cizí třída 3.A odmítnuta POLOŽKA PO POLOŽCE, neexistující nenalezeno, neplatná id zahozena',
    count($mixed) === 3 && ($byId[$ownA]['outcome'] ?? '') === 'confirmed' && ($byId[$rep3a1]['outcome'] ?? '') === 'forbidden' && ($byId['fb60_000000000000']['outcome'] ?? '') === 'missing');
$check('hromadné: cizí hlášení zůstalo beze změny (new, bez odměny)', (fb60_item($rep3a1)['status'] ?? '') === 'new' && (int)(fb60_item($rep3a1)['reward_points'] ?? -1) === 0);
$defaults = fb60_reward_defaults()['bug'];
$check('hromadné: vlastní potvrzené hlášení dostalo výchozí odměnu podle typu právě jednou', (fb60_item($ownA)['status'] ?? '') === 'confirmed' && pts53_balance('class_2a', $key('class_2a', 'Audit Druha')) === $balanceDrew + (int)$defaults['points']);
$again = ov61_bulk_confirm([$ownA], 'class_2a', 'audit-a', false);
$check('hromadné: druhé potvrzení téhož je idempotentní (už potvrzeno, žádné body navíc)', $again[0]['outcome'] === 'already' && pts53_balance('class_2a', $key('class_2a', 'Audit Druha')) === $balanceDrew + (int)$defaults['points']);
$wrongClass = ov61_bulk_confirm([$rep2a], 'class_1a', 'audit-a', false);
$check('hromadné: hlášení v rozsahu, ale z jiné třídy než vybrané, se odmítne bez změny', $wrongClass[0]['outcome'] === 'wrong_class');
$asId($acc['id']['b']);
$lastBefore = $s3['Audit Odmena']['last'];
$balanceOdm = pts53_balance($A, $key($A, 'Audit Odmena'));
$batch = ov61_bulk_confirm([$rep3a1, $rep3a2, $rep4a, $rep3a2], $A, 'audit-b', false);
$byId = array_column($batch, null, 'id');
$check('hromadné: B potvrdí 3.A hlášení (duplicitní id jen jednou), 4.A hlášení z formuláře 3.A odmítne jako jinou třídu', count($batch) === 3 && ($byId[$rep3a1]['outcome'] ?? '') === 'confirmed' && ($byId[$rep3a2]['outcome'] ?? '') === 'confirmed' && ($byId[$rep4a]['outcome'] ?? '') === 'wrong_class' && (fb60_item($rep4a)['status'] ?? '') === 'new');
$check('hromadné: odměna žáka se připsala jednou a odpovídá výchozí hodnotě typu', pts53_balance($A, $key($A, 'Audit Odmena')) === $balanceOdm + (int)fb60_reward_defaults()['bug']['points']);
$ideaXp = (int)(storage_read(learning_profiles_path())[fb60_learning_key($A, $key($A, 'Audit Aktivni'))]['xp'] ?? 0);
ov61_bulk_confirm([$rep3a1, $rep3a2], $A, 'audit-b', false);
$check('hromadné: třetí průchod nepřidá XP ani body (idempotence přes fb60_decide)', (int)(storage_read(learning_profiles_path())[fb60_learning_key($A, $key($A, 'Audit Aktivni'))]['xp'] ?? 0) === $ideaXp && pts53_balance($A, $key($A, 'Audit Odmena')) === $balanceOdm + (int)fb60_reward_defaults()['bug']['points']);
unset($GLOBALS['educanet_runtime_indexes']);
$afterRows = $rows($A);
$check('aktivita: odměna učitele (XP událost fb60:*) se nepočítá jako aktivita žáka – Odměna stále zaostává', $afterRows['Audit Odmena']['lagging'] && $afterRows['Audit Odmena']['days'] >= 11 && abs($afterRows['Audit Odmena']['last'] - $lastBefore) <= 5);
$check('hromadné: potvrzená hlášení už nejsou ve frontě čekajících', array_values(array_intersect(array_column(ov61_pending_reports($A), 'id'), [$rep3a1, $rep3a2])) === [] && in_array($rep3a3, array_column(ov61_pending_reports($A), 'id'), true));
$tooMany = [];
for ($i = 0; $i < OV61_BULK_MAX + 7; $i++) $tooMany[] = 'fb60_' . str_pad(dechex($i), 12, '0', STR_PAD_LEFT);
$check('hromadné: nejvýše ' . OV61_BULK_MAX . ' položek na jeden požadavek', count(ov61_bulk_confirm($tooMany, $A, 'audit-b', false)) === OV61_BULK_MAX);
$check('hromadné: souhrn hlášky počítá výsledky', str_contains(ov61_bulk_summary($batch), '2× potvrzeno') && str_contains(ov61_bulk_summary($batch), '1× jiná třída') && ov61_bulk_summary([]) === 'Nebylo vybráno žádné hlášení.');

// --- 5) Politiky (deny-by-default) a oprávnění ---------------------------------------------------------------
$asId($acc['id']['a']);
$check('politika: ov61_bulk_confirm vyžaduje třídu v rozsahu (A: 3.A i bez třídy zamítnuto, 2.A povoleno)',
    teacher59_guard_post_check('ov61_bulk_confirm', ['class_id' => $A]) !== null && teacher59_guard_post_check('ov61_bulk_confirm', []) !== null && teacher59_guard_post_check('ov61_bulk_confirm', ['class_id' => 'class_2a']) === null);
$check('politika: neznámá akce s prefixem ov61_ je zamítnuta, oprávnění ov61_ = content.manage', !empty(teacher59_action_policy('ov61_hack')['deny']) && teacher_action_permission('ov61_bulk_confirm') === 'content.manage' && teacher_action_permission('ov61_hack') === 'content.manage');
$check('politika: GET export má třídu povinnou a modul prehled registruje POST prefix i export', (teacher59_get_policies()['prehled|export']['class'] ?? '') === 'required' && isset(teacher58_modules()['prehled']['post']['ov61_']) && isset(teacher58_modules()['prehled']['get']['export']) && teacher59_guard_get_check('prehled', ['export' => '1', 'class' => $A]) === 'class_out_of_scope' && teacher59_guard_get_check('prehled', ['export' => '1', 'class' => 'class_2a']) === null);
$asId($acc['id']['s']);
$check('asistent: nemá content.manage, hromadné potvrzení ani vidět nesmí', !teacher_permission('content.manage') && teacher_permission('view'));
$htmlAssistant = audit_capture(static function () use ($A): void { $_GET['class'] = $A; ov61_render_teacher_tab($A, 'csrf-x'); unset($_GET['class']); });
$check('asistent: přehled vidí, ale bez zaškrtávátek a bez formuláře potvrzení', str_contains($htmlAssistant, 'Audit Lenochod') && !str_contains($htmlAssistant, 'name="ids[]"') && !str_contains($htmlAssistant, 'value="ov61_bulk_confirm"') && str_contains($htmlAssistant, 'Asistent hlášení potvrzovat nemůže'));
$asId($acc['id']['b']);
$htmlTeacher = audit_capture(static function () use ($A): void { $_GET['class'] = $A; ov61_render_teacher_tab($A, 'csrf-x'); unset($_GET['class']); });
$check('učitel: přehled má CSRF, akci, skrytou třídu, zaškrtávátka a odkaz na CSV; žák je escapovaný', str_contains($htmlTeacher, 'name="csrf" value="csrf-x"') && str_contains($htmlTeacher, 'value="ov61_bulk_confirm"') && str_contains($htmlTeacher, 'name="class_id" value="class_3a"')
    && str_contains($htmlTeacher, 'name="ids[]"') && str_contains($htmlTeacher, 'export=1&amp;class=class_3a') && str_contains($htmlTeacher, '<th scope="row">Audit Lenochod</th>') && str_contains($htmlTeacher, 'aria-label="Třída přehledu"'));
$check('učitel: přehled neobsahuje žáky ani hlášení 4.A (ani v odkazech třídy mimo rozsah B není cizí)', !str_contains($htmlTeacher, 'Audit Ctvrta') && !str_contains($htmlTeacher, 'čtvrtá cizí'));

// --- 6) CSV ---------------------------------------------------------------------------------------------------
$asId($acc['id']['admin']);
$csv = ov61_csv_body($A);
$check('CSV: začíná UTF-8 BOM a používá oddělovač „;“ s českým záhlavím', str_starts_with($csv, "\xEF\xBB\xBF") && str_contains($csv, "Třída;Žák;"));
$fp = fopen('php://temp', 'r+');
fwrite($fp, substr($csv, 3));
rewind($fp);
$parsed = [];
while (($row = fgetcsv($fp, 0, ';', '"', '')) !== false) $parsed[] = $row;
fclose($fp);
$unsafe = 0;
foreach ($parsed as $row) foreach ($row as $cell) if ($cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true)) $unsafe++;
$names = array_column($parsed, 1);
$check('CSV injection: žádná buňka nezačíná =, +, -, @; jména se vzorcem mají prefix apostrofu', $unsafe === 0 && in_array("'=1+1 Audit", $names, true) && in_array("'@Zavinac Audit", $names, true) && in_array("'+Plus Audit", $names, true) && in_array("'-Minus Audit", $names, true));
$check('CSV: jeden řádek na žáka (+ záhlaví), sloupec Zaostává a Důvod odpovídá přehledu', count($parsed) === count(ov61_students($A)) + 1 && (static function () use ($parsed): bool {
    foreach ($parsed as $r) if ($r[1] === 'Audit Lenochod') return $r[5] === 'ano' && str_contains($r[6], 'Bez aktivity 10 dní');
    return false;
})());
$check('CSV: počty čekajících hlášení/přihlášek/nákupů se promítnou k žákovi', (static function () use ($parsed): bool {
    foreach ($parsed as $r) {
        if ($r[1] === 'Audit Dobry') return $r[7] === '1' && $r[8] === '1';
    }
    return false;
})() && (static function () use ($parsed): bool { foreach ($parsed as $r) if ($r[1] === 'Audit Aktivni') return $r[9] === '1'; return false; })());
$check('CSV: neobsahuje jiné třídy (4.A) ani cizí jména', !str_contains($csv, 'Audit Ctvrta') && count(array_unique(array_column(array_slice($parsed, 1), 0))) === 1);
$check('CSV buňka: řídicí znaky (nový řádek, tabulátor) se neutralizují', ov61_csv_cell("a\nb") === 'a b' && ov61_csv_cell("\t=x") === "'\t=x" && ov61_csv_cell('ok') === 'ok' && ov61_csv_cell('-5') === "'-5");

// --- 7) HTTP: rozsah a asistent skutečným serverem ------------------------------------------------------------
audit_prewarm_accounts($GLOBALS['modules']);
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1']);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
$jars = [];
$csrfOf = [];
$req = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h, $jarSwap, &$jars): array {
    $jarSwap($jars[$who] ?? []);
    try { return $h->request($method, $path, $fields, $headers + ['follow_redirects' => false]); } finally { $jars[$who] = $jarSwap(null); }
};
try {
    foreach (['a', 'b', 's', 'admin'] as $who) {
        $login = audit_login_teacher_account(v59sf_harness_as($h, $jarSwap, $jars, $who), $acc['login'][$who], V59SF_PASSWORDS[$who]);
        $jars[$who] = $jarSwap(null);
        $csrfOf[$who] = (string)$login['csrf'];
    }
    $check('HTTP: přihlášení učitelů (A, B, asistent, admin) proběhlo', count(array_filter($csrfOf)) === 4);
    $exportA = $req('a', 'GET', '/teacher.php?tab=prehled&export=1&class=class_3a');
    $check('HTTP CSV: A (1.A+2.A) na 3.A → 403 bez dat', $exportA['status'] === 403 && !str_contains($exportA['body'], 'Audit') && !str_contains($exportA['body'], "\xEF\xBB\xBF"));
    $exportB = $req('b', 'GET', '/teacher.php?tab=prehled&export=1&class=class_3a');
    $check('HTTP CSV: B na 3.A → 200 text/csv s přílohou, BOM, jen 3.A', $exportB['status'] === 200 && str_starts_with((string)($exportB['headers']['Content-Type'] ?? ''), 'text/csv') && str_contains((string)($exportB['headers']['Content-Disposition'] ?? ''), 'attachment; filename="prehled-3a-')
        && str_starts_with($exportB['body'], "\xEF\xBB\xBF") && str_contains($exportB['body'], 'Audit Lenochod') && !str_contains($exportB['body'], 'Audit Ctvrta') && str_contains($exportB['body'], "'=1+1 Audit"));
    $exportNoClass = $req('b', 'GET', '/teacher.php?tab=prehled&export=1');
    $exportAll = $req('b', 'GET', '/teacher.php?tab=prehled&export=1&class=all');
    $check('HTTP CSV: bez třídy i třída „all“ se zamítnou (403)', $exportNoClass['status'] === 403 && $exportAll['status'] === 403);
    $exportAdmin = $req('admin', 'GET', '/teacher.php?tab=prehled&export=1&class=class_4a');
    $check('HTTP CSV: admin dostane i 4.A', $exportAdmin['status'] === 200 && str_contains($exportAdmin['body'], 'Audit Ctvrta'));
    $exportS = $req('s', 'GET', '/teacher.php?tab=prehled&export=1&class=class_3a');
    $exportS4 = $req('s', 'GET', '/teacher.php?tab=prehled&export=1&class=class_4a');
    $check('HTTP CSV: asistent (čtení) 3.A ano, 4.A ne', $exportS['status'] === 200 && $exportS4['status'] === 403);
    $pageA = $req('a', 'GET', '/teacher.php?tab=prehled&class=class_3a');
    $check('HTTP stránka: A na ?class=class_3a dostane jen svou třídu (žádná jména 3.A), 200 bez PHP chyb', $pageA['status'] === 403 || (!str_contains($pageA['body'], 'Audit Lenochod') && !preg_match('/Fatal error|Uncaught|<b>Warning<\/b>/', $pageA['body'])));
    $pageB = $req('b', 'GET', '/teacher.php?tab=prehled&class=class_3a');
    $check('HTTP stránka: B vidí zaostávající, hromadný formulář s CSRF a odkaz na export', $pageB['status'] === 200 && str_contains($pageB['body'], 'Audit Lenochod') && str_contains($pageB['body'], 'value="ov61_bulk_confirm"') && str_contains($pageB['body'], 'export=1&amp;class=class_3a')
        && !preg_match('/Fatal error|Uncaught|<b>Warning<\/b>/', $pageB['body']));
    $pageS = $req('s', 'GET', '/teacher.php?tab=prehled&class=class_3a');
    $check('HTTP stránka: asistent bez formuláře potvrzení', $pageS['status'] === 200 && !str_contains($pageS['body'], 'value="ov61_bulk_confirm"') && str_contains($pageS['body'], 'Audit Lenochod'));
    $before = fb60_item($rep3a3);
    $postS = $req('s', 'POST', '/teacher.php', ['action' => 'ov61_bulk_confirm', 'csrf' => $csrfOf['s'], 'class_id' => $A, 'ids' => [$rep3a3]]);
    $check('HTTP POST: asistent nepotvrdí hromadně (zamítnuto rolí, přesměrování s hláškou) a hlášení se nezměnilo', in_array($postS['status'], [302, 303], true) && fb60_item($rep3a3) === $before);
    $postNoCsrf = $req('b', 'POST', '/teacher.php', ['action' => 'ov61_bulk_confirm', 'class_id' => $A, 'ids' => [$rep3a3]]);
    $check('HTTP POST: bez CSRF 419 a hlášení se nezměnilo', $postNoCsrf['status'] === 419 && fb60_item($rep3a3) === $before);
    $postForeign = $req('a', 'POST', '/teacher.php', ['action' => 'ov61_bulk_confirm', 'csrf' => $csrfOf['a'], 'class_id' => $A, 'ids' => [$rep3a3]]);
    $check('HTTP POST: A s třídou 3.A → 403 a hlášení se nezměnilo', $postForeign['status'] === 403 && fb60_item($rep3a3) === $before);
    $postMixed = $req('a', 'POST', '/teacher.php', ['action' => 'ov61_bulk_confirm', 'csrf' => $csrfOf['a'], 'class_id' => 'class_2a', 'ids' => [$rep3a3]]);
    $check('HTTP POST: A s vlastní třídou 2.A a cizím id → přesměrování, ale cizí hlášení beze změny (per položka)', in_array($postMixed['status'], [302, 303], true) && fb60_item($rep3a3) === $before);
    $postOk = $req('b', 'POST', '/teacher.php', ['action' => 'ov61_bulk_confirm', 'csrf' => $csrfOf['b'], 'class_id' => $A, 'ids' => [$rep3a3]]);
    $afterPost = $req('b', 'GET', '/teacher.php?tab=prehled&class=class_3a');
    $check('HTTP POST: B potvrdí hlášení, stránka ukáže výsledek po položkách (aria-live) a hlášení je confirmed', in_array($postOk['status'], [302, 303], true) && (fb60_item($rep3a3)['status'] ?? '') === 'confirmed'
        && str_contains($afterPost['body'], 'aria-live="polite"') && str_contains($afterPost['body'], 'Audit hlášení tři tři A') && str_contains($afterPost['body'], 'Potvrzeno'));
    $stu = $req('stu', 'GET', '/', ['class' => $A, 'student' => 'Audit Kalendar']);
    $calendar = $req('stu', 'GET', '/?view=calendar');
    $check('kalendář: dosažitelná stránka žáka má odkaz „Přidat svůj rozvrh (.ics)“ na calendar.ics.php své třídy', $calendar['status'] === 200 && str_contains($calendar['body'], 'href="calendar.ics.php?class=class_3a"') && str_contains($calendar['body'], 'Přidat svůj rozvrh (.ics)')
        && str_contains(html_entity_decode($calendar['body']), 'Každou středu 14:30–16:05'));
    $ics = $req('stu', 'GET', '/calendar.ics.php?class=class_3a');
    $icsForeign = $req('stu', 'GET', '/calendar.ics.php?class=class_4a');
    $check('kalendář: .ics vlastní třídy je platný VCALENDAR, cizí třída 403', $ics['status'] === 200 && str_starts_with($ics['body'], 'BEGIN:VCALENDAR') && $icsForeign['status'] === 403);
} finally {
    $h->stop();
}

// --- 8) CLI přeřazení dat učitele ------------------------------------------------------------------------------
$keyA = teacher59_owner_key_for($acc['id']['a']);
$keyB = teacher59_owner_key_for($acc['id']['b']);
$keyOther = teacher59_owner_key_for($acc['id']['s']);
$filter = static fn(string $id, string $owner, string $classId, string $name): array => ['id' => $id, 'owner_key' => $owner, 'owner_version' => 3, 'owner_label' => 'Alena Učitelová', 'scope' => 'personal', 'team_key' => '', 'name' => $name,
    'class_id' => $classId, 'status' => 'all', 'priority' => 'all', 'task_status' => 'all', 'task_due' => 'all', 'rule_mode' => 'all', 'rules' => [], 'pinned' => false, 'default_for' => 'none', 'created_at' => date(DATE_ATOM), 'updated_at' => date(DATE_ATOM)];
foreach ([$filter('rs_3a', $keyA, 'class_3a', 'Přeřazovací filtr tři'), $filter('rs_2a', $keyA, 'class_2a', 'Přeřazovací filtr dva'), $filter('rs_none', $keyA, '', 'Přeřazovací filtr bez třídy'),
    $filter('rs_other', $keyOther, 'class_3a', 'Cizí filtr asistenta'), $filter('rs_b', $keyB, 'class_3a', 'Filtr B')] as $row) v59sf_push('teacher_saved_filters.json.php', $row);
$filtersPath = $tmp . '/teacher_saved_filters.json.php';
$logPath = teacher59_log_path();
$backupDir = $tmp . '-backups';
@mkdir($backupDir, 0700, true);
$cli = static function (array $args, array $env = []) use ($root, $tmp, $backupDir): array {
    $proc = proc_open(array_merge([PHP_BINARY, $root . '/tools/v61_teacher_data_reassign.php'], $args), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root,
        array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_BACKUP_DIR' => $backupDir], $env));
    if (!is_resource($proc)) return ['code' => -1, 'out' => '', 'err' => ''];
    $out = (string)stream_get_contents($pipes[1]);
    $err = (string)stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['code' => proc_close($proc), 'out' => $out, 'err' => $err];
};
$ownerOf = static function (string $id): string {
    foreach (storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php') as $row) if (is_array($row) && ($row['id'] ?? '') === $id) return (string)$row['owner_key'];
    return '';
};
$base = ['--from-account=' . $acc['id']['a'], '--to-account=' . $acc['id']['b']];
$hashFilters = hash_file('sha256', $filtersPath);
$logBefore = is_file($logPath) ? hash_file('sha256', $logPath) : '';
$dry = $cli($base);
$check('CLI: výchozí běh je náhled (dry_run=1), spočítá 2 záznamy k přenosu a 1 mimo rozsah cíle', $dry['code'] === 0 && str_contains($dry['out'], 'REASSIGN_OK dry_run=1 rows=2 skipped=1'));
$check('CLI --dry-run: nic se nezapsalo (soubor filtrů ani log beze změny, vlastníci stejní)', hash_file('sha256', $filtersPath) === $hashFilters && (is_file($logPath) ? hash_file('sha256', $logPath) : '') === $logBefore && $ownerOf('rs_3a') === $keyA);
$explicit = $cli(array_merge($base, ['--dry-run']));
$check('CLI: explicitní --dry-run se chová stejně, --apply spolu s --dry-run se odmítne (exit 2)', $explicit['code'] === 0 && str_contains($explicit['out'], 'dry_run=1') && $cli(array_merge($base, ['--apply', '--dry-run']))['code'] === 2);
$noBackup = $cli(array_merge($base, ['--apply']));
$check('CLI --apply bez čerstvé zálohy: odmítnuto (exit 2, REASSIGN_REFUSED), data beze změny', $noBackup['code'] === 2 && str_contains($noBackup['err'], 'REASSIGN_REFUSED') && str_contains($noBackup['err'], 'záloha') && hash_file('sha256', $filtersPath) === $hashFilters && $ownerOf('rs_3a') === $keyA);
$bad = [$cli(['--to-account=' . $acc['id']['b']]), $cli(['--from-account=' . $acc['id']['a']]), $cli(['--from-account=' . $acc['id']['a'], '--to-account=' . $acc['id']['a']]),
    $cli(array_merge($base, ['--store=neexistuje'])), $cli(['--from-owner=xyz', '--to-account=' . $acc['id']['b']]), $cli(['--from-account=' . $acc['id']['a'], '--from-owner=' . $keyA, '--to-account=' . $acc['id']['b']])];
$check('CLI: chybějící/špatné argumenty (bez zdroje, bez cíle, stejný účet, neznámé úložiště, vadný klíč, dva zdroje) → exit 2 a nic se nezměnilo', count(array_filter($bad, static fn(array $r): bool => $r['code'] === 2)) === 6 && hash_file('sha256', $filtersPath) === $hashFilters);
$applied = $cli(array_merge($base, ['--apply', '--backup-now']));
$backups = glob($backupDir . '/storage-*', GLOB_ONLYDIR) ?: [];
$manifest = $backups ? json_decode((string)@file_get_contents($backups[0] . '/manifest.json'), true) : null;
$check('CLI --apply --backup-now: záloha vznikla (manifest) a přeřazení proběhlo (rows=2 skipped=1)', $applied['code'] === 0 && count($backups) === 1 && is_array($manifest) && str_contains($applied['out'], 'REASSIGN_OK dry_run=0 rows=2 skipped=1'));
$labelOf = static function (string $id): string {
    foreach (storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php') as $row) if (is_array($row) && ($row['id'] ?? '') === $id) return (string)$row['owner_label'];
    return '';
};
$check('CLI: filtr 3.A a filtr bez třídy přešly na účet B, filtr 2.A (mimo rozsah B) a cizí vlastníci zůstali', $ownerOf('rs_3a') === $keyB && $ownerOf('rs_none') === $keyB && $ownerOf('rs_2a') === $keyA && $ownerOf('rs_other') === $keyOther && $ownerOf('rs_b') === $keyB);
$check('CLI: owner_label přeřazených záznamů je jméno cílového účtu', $labelOf('rs_3a') === 'Bohdan Sítař' && $labelOf('rs_2a') === 'Alena Učitelová');
$logRows = array_values(array_filter(storage_read($logPath), static fn($r): bool => is_array($r) && ($r['event'] ?? '') === 'reassigned'));
$logJson = (string)json_encode($logRows, JSON_UNESCAPED_UNICODE);
$check('CLI: log obsahuje událost reassigned s počty a zkráceným otiskem klíče, bez jmen a názvů záznamů', count($logRows) === 1 && ($logRows[0]['meta']['skipped_out_of_scope'] ?? null) === 1 && strlen((string)($logRows[0]['meta']['old_key'] ?? '')) === 16
    && !str_contains($logJson, 'Přeřazovací') && !str_contains($logJson, 'Bohdan') && !str_contains($logJson, 'Alena') && !str_contains($logJson, $keyA));
$check('CLI: výstup neobsahuje jména ani názvy filtrů ani celé klíče', !str_contains($applied['out'] . $dry['out'], 'Přeřazovací') && !str_contains($applied['out'] . $dry['out'], 'Bohdan') && !str_contains($applied['out'] . $dry['out'], $keyA));
$second = $cli(array_merge($base, ['--apply']));
$check('CLI: opakované --apply je idempotentní (rows=0) – záloha z předchozího běhu je stále čerstvá', $second['code'] === 0 && str_contains($second['out'], 'rows=0 skipped=1'));
$byKey = $cli(['--from-owner=' . $keyOther, '--to-account=' . $acc['id']['b'], '--store=saved_filters']);
$check('CLI: --from-owner a --store=saved_filters (náhled) najde cizí záznam asistenta', $byKey['code'] === 0 && str_contains($byKey['out'], 'rows=1 skipped=0'));

// --- 9) Vyřazený calendar_classic.php --------------------------------------------------------------------------
$check('calendar_classic.php neexistuje', !is_file($root . '/app/views/calendar_classic.php'));
$readers = [];
$scan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($scan as $file) {
    $path = str_replace('\\', '/', $file->getPathname());
    if (!$file->isFile() || !preg_match('/\.(php|js|json|html|htm|css)$/', $path) || str_contains($path, '/storage/') || str_contains($path, '/V1/') || str_contains($path, '/cache/') || str_contains($path, '/lab-runtime/')
        || $path === str_replace('\\', '/', __FILE__)) continue;
    if (str_contains((string)@file_get_contents($path), 'calendar_classic')) $readers[] = substr($path, strlen(str_replace('\\', '/', $root)) + 1);
}
$check('calendar_classic se v kódu nikde neodkazuje (router, audity, assety)' . ($readers ? ' – ODKAZY: ' . implode(', ', $readers) : ''), $readers === []);
$routes = require $root . '/app/routes.php';
$routeFiles = [];
foreach ($routes as $group) {
    if (!is_array($group)) continue;
    foreach ($group as $entry) if (is_array($entry) && isset($entry['file'])) $routeFiles[] = (string)$entry['file'];
}
$missingRoute = array_filter($routeFiles, static fn(string $f): bool => !is_file($root . '/app/' . $f));
$check('router: každý soubor z tabulky rout existuje', $missingRoute === []);
$check('?view=calendar obsluhuje tutorial.php (jediná routa „calendar“)', count(array_filter($routeFiles, static fn(string $f): bool => $f === 'views/tutorial.php')) >= 1 && (static function () use ($routes): int {
    $n = 0;
    foreach ($routes as $group) if (is_array($group)) foreach ($group as $e) if (is_array($e) && in_array('calendar', (array)($e['match'] ?? []), true)) $n++;
    return $n;
})() === 1);
$check('v32/v33 audity už nečtou starý kalendář (kontrolují vykreslenou stránku s .ics)', (static function () use ($root): bool {
    $v32 = (string)file_get_contents($root . '/tools/v32_deployment_lesson_mode_audit.php');
    $v33 = (string)file_get_contents($root . '/tools/v33_schedule_performance_audit.php');
    return str_contains($v32, '?view=calendar') && str_contains($v33, '?view=calendar') && str_contains($v33, 'Harness::start') && str_contains($v32, 'Harness::start');
})(), false);

// --- 10) Zdroj: bez CDN, innerHTML, shellových funkcí; modul je pod 800 řádků ------------------------------------
$src = '';
foreach (['teacher_overview_v61.php', 'teacher_overview_v61_views.php', 'tools/v61_teacher_data_reassign.php', 'assets/teacher-overview-v61.js', 'assets/teacher-overview-v61.css'] as $f) $src .= (string)file_get_contents($root . '/' . $f);
$check('zdroj: bez CDN, innerHTML, eval a shellových funkcí', !preg_match('~https?://|innerHTML|\beval\s*\(|\b(shell_exec|exec|system|passthru|proc_open|popen)\s*\(~', $src), false);
$check('zdroj: soubory modulu pod 800 řádků', array_reduce(['teacher_overview_v61.php', 'teacher_overview_v61_views.php', 'tools/v61_teacher_data_reassign.php'], static fn(bool $ok, string $f): bool => $ok && count(file($root . '/' . $f)) < 800, true), false);
$check('zdroj: tools/v61_teacher_data_reassign.php je CLI-only s guardem a bez přímého RMW (storage_update_many)', str_contains((string)file_get_contents($root . '/tools/v61_teacher_data_reassign.php'), "PHP_SAPI !== 'cli'") && str_contains((string)file_get_contents($root . '/tools/v61_teacher_data_reassign.php'), 'storage_update_many'), false);

exit(audit_summary($state, 'V61_TEACHER'));
