<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v66 · část 4 auditu (načítá ji tools/lib/v66_audit_part2.php): sumativní test za běhu (pořadí otázek, žádné odměny), statické kontroly
 * (token-sken, invarianty, velikosti, guardy), politiky a CSRF, rozsah učitelských účtů (režim účtů – běží jako poslední, mění režim úložiště účtů).
 */

// --- 10) Sumativní test za běhu ---------------------------------------------------------------------------
$rt = runtime_content_load_classes([$CLASS]);
$bundle = static fn(int $n): array => v56_lesson_bundle($CLASS, $GLOBALS['modules'][$CLASS], $n, $rt['nextLessons'], $rt['extendedLessons']);
$qs = $bundle(2)['questions'];
$ids = static fn(array $list): array => array_map(static fn(array $q): string => (string)$q['id'], $list);
a66_set_kind($CLASS, 'v56:l2', 'summative', $ACTOR, $NOW);
$orderA = $ids(v56_test_questions($CLASS, $good, 2, $qs));
$orderA2 = $ids(v56_test_questions($CLASS, $good, 2, $qs));
$orderB = $ids(v56_test_questions($CLASS, $player, 2, $qs));
$sortedA = $orderA; sort($sortedA); $sortedIds = $ids($qs); sort($sortedIds);
$check('sumativní test lekce: pořadí otázek je pro žáka stabilní, mezi žáky různé a vždy obsahuje všechny otázky; formativní test (lekce 3) zůstává v původním pořadí', $orderA === $orderA2 && $orderA !== $orderB && $sortedA === $sortedIds
    && $ids(v56_test_questions($CLASS, $good, 3, $bundle(3)['questions'])) === $ids($bundle(3)['questions']));
$_SESSION = ['student_label' => V62FX_LABELS['good'], 'next_class_id' => $CLASS];
$answersFor = static function (array $questions): array { $a = []; foreach ($questions as $i => $q) $a[$i] = (string)$q['correct']; return $a; };
$sumQuestions = v56_test_questions($CLASS, $good, 2, $qs);
$resultSum = v56_submit_test($CLASS, $good, 2, $sumQuestions, $answersFor($sumQuestions));
$resultForm = v56_submit_test($CLASS, $good, 3, $bundle(3)['questions'], $answersFor($bundle(3)['questions']));
$events = (array)(learning_profile($CLASS)['events'] ?? []);
$check('sumativní test nedává žádné odměny: úspěšný test lekce 2 (sumativní) nezapíše událost odměny, kontrolní formativní test lekce 3 ano', $resultSum['passed'] && $resultForm['passed'] && !isset($events['v56:l2:test']) && isset($events['v56:l3:test']));
$check('sumativní test: výsledek se ukládá a vyhodnocuje podle pořadí žáka (100 % při správných odpovědích v jeho pořadí)', (int)$resultSum['percent'] === 100 && (int)$resultSum['score'] === (int)$resultSum['max']);
$_SESSION = [];
a66_set_kind($CLASS, 'v56:l2', 'formative', $ACTOR, $NOW);

// --- 11) Statické kontroly ---------------------------------------------------------------------------------
/** Zakázané tokeny v kódu hodnocení: odměny, body, měna, ekonomika, aréna. */
$forbiddenScan = static function (string $source): array {
    $hits = [];
    if (preg_match_all('/(?<![A-Za-z0-9])(xp|points|coins|kc|elo)(?![A-Za-z0-9])/i', $source, $m)) $hits = array_merge($hits, $m[1]);
    if (preg_match_all('/economy_|learning_award|points_v53|eco64_|arena_/i', $source, $m)) $hits = array_merge($hits, $m[0]);
    return $hits;
};
$check('token-sken: detekuje zakázané tokeny (xp, points, coins, kc, elo, economy_, learning_award, points_v53, eco64_, arena_) a nehlásí běžný kód', $forbiddenScan('$x = learning_award_once(1); $max_points = 3;') !== [] && $forbiddenScan('$a = eco64_x(); // XP') !== [] && $forbiddenScan('$expected = explode(",", $s); $kcal = 1;') === [] && $forbiddenScan('arena_race()') !== [] && $forbiddenScan('$score = 1; $weights = []; $chain = [];') === []);
$scanFiles = array_merge(glob($root . '/grading_v66*.php') ?: [], glob($root . '/assessment_v66*.php') ?: [], [$root . '/question_meta_v66.php', $root . '/integrity_v66.php', $root . '/morning_v66.php']);
$hitsByFile = [];
foreach ($scanFiles as $file) {
    $hits = $forbiddenScan((string)file_get_contents($file));
    if ($hits !== []) $hitsByFile[basename($file)] = array_unique($hits);
}
$check('token-sken: grading_v66*, assessment_v66*, question_meta_v66, integrity_v66 a morning_v66 (' . count($scanFiles) . ' souborů) neobsahují žádný z tokenů xp, points, coins, kc, elo, economy_, learning_award, points_v53, eco64_, arena_' . ($hitsByFile ? ' – ' . json_encode($hitsByFile) : ''), $hitsByFile === [] && count($scanFiles) >= 9, false);
$v66Php = array_merge($scanFiles, [$root . '/tools/v66_item_analysis.php', $root . '/tools/v66_morning_build.php', $root . '/app/views/hodnoceni.php']);
$bannedFns = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'assert', 'create_function', 'fsockopen', 'stream_socket_client', 'mail', 'file_get_contents_url', 'gethostbyname', 'dns_get_record'];
$badCalls = [];
foreach ($v66Php as $file) {
    $tokens = token_get_all((string)file_get_contents($file));
    foreach ($tokens as $i => $t) {
        if (is_string($t) && $t === '`') $badCalls[] = basename($file) . ':backtick';
        if (!is_array($t) || ($t[0] !== T_STRING && $t[0] !== T_EVAL)) continue;
        $name = strtolower($t[1]);
        $next = $tokens[$i + 1] ?? '';
        $isCall = $next === '(' || (is_array($next) && $next[0] === T_WHITESPACE && ($tokens[$i + 2] ?? '') === '(');
        if ($isCall && (in_array($name, $bannedFns, true) || str_starts_with($name, 'curl_') || str_starts_with($name, 'socket_'))) $badCalls[] = basename($file) . ':' . $name;
    }
}
$check('invariant: kód v66 nevolá exec/system/proc_open/popen/eval/curl_/socket_/mail/DNS funkce a nemá zpětné apostrofy (nic se nespouští ani nepřipojuje)', $badCalls === [], false);
$linuxLab = array_merge(glob($root . '/linux_v57_*.php') ?: [], [$root . '/lab_v57_api.php']);
$mentions = array_filter($linuxLab, static fn(string $f): bool => str_contains((string)file_get_contents($f), 'v66') || str_contains((string)file_get_contents($f), 'a66_'));
$check('Linux Lab se nezměnil: žádný soubor simulátoru neodkazuje na vrstvu v66', $mentions === [], false);
$tooLong = array_filter(array_merge($v66Php, [$root . '/tools/v66_assessment_audit.php', $root . '/tools/lib/v66_audit_part2.php', $root . '/tools/lib/v66_audit_part3.php', $root . '/tools/lib/v66_audit_part4.php']), static fn(string $f): bool => count(file($f)) > 800);
$check('soubory v66 mají do 800 řádků', $tooLong === [], false);
$unguarded = array_filter($scanFiles, static fn(string $f): bool => !str_contains((string)file_get_contents($f), 'declare(strict_types=1);') || !str_contains((string)file_get_contents($f), "http_response_code(403); exit; }"));
$check('každá knihovna v66 má declare(strict_types=1) a guard proti přímému volání, CLI nástroje mají guard PHP_SAPI', $unguarded === [] && str_contains((string)file_get_contents($root . '/tools/v66_item_analysis.php'), "PHP_SAPI !== 'cli'") && str_contains((string)file_get_contents($root . '/tools/v66_morning_build.php'), "PHP_SAPI !== 'cli'"), false);
$assetText = (string)file_get_contents($root . '/assets/assessment-v66.css') . (string)file_get_contents($root . '/assets/assessment-v66.js') . (string)file_get_contents($root . '/assets/assessment-v66-print.css');
$check('assety v66: žádné externí zdroje (http/https/CDN), celkem do 60 kB, JS bez innerHTML, CSS má prefers-reduced-motion a viditelný fokus', !preg_match('#https?://|//cdn#i', $assetText) && strlen($assetText) < 60000 && !str_contains((string)file_get_contents($root . '/assets/assessment-v66.js'), 'innerHTML')
    && str_contains((string)file_get_contents($root . '/assets/assessment-v66.css'), 'prefers-reduced-motion') && str_contains((string)file_get_contents($root . '/assets/assessment-v66.css'), 'focus-visible'), false);
$jsText = (string)file_get_contents($root . '/teacher_v58.php');
$check('registr cockpitu: záložka hodnoceni66 existuje, její JS se načítá s defer (teacher58_foot_assets) a CSS jen na této záložce', isset(teacher58_modules()['hodnoceni66']) && teacher58_modules()['hodnoceni66']['js'] === ['assets/assessment-v66.js'] && str_contains($jsText, "defer")
    && array_filter(teacher58_modules(), static fn(array $m, string $k): bool => $k !== 'hodnoceni66' && in_array('assets/assessment-v66.css', (array)($m['css'] ?? []), true), ARRAY_FILTER_USE_BOTH) === []);
$routesSrc = (string)file_get_contents($root . '/app/routes.php');
$layoutSrc = (string)file_get_contents($root . '/app/views/_layout.php') . (string)file_get_contents($root . '/app/views/dashboard.php');
$check('CSS v66 se na dashboardu ani v hlavičce všech stránek nenačítá (rozpočet), jen v pohledu hodnoceni a záložce hodnoceni66', !str_contains($layoutSrc, 'assessment-v66') && str_contains((string)file_get_contents($root . '/grading_v66_views.php'), 'assessment-v66.css'), false);

// --- 12) Politiky, oprávnění, CSRF ------------------------------------------------------------------------
$pol = static fn(string $a): array => teacher59_action_policy($a);
$check('politiky POST: a66_recompute, a66_set_kind, g66_accept a p63_assign_student vyžadují třídu v rozsahu; g66_settings navíc jen admina', array_reduce(['a66_recompute', 'a66_set_kind', 'g66_accept', 'p63_assign_student'], static fn(bool $c, string $a): bool => $c && $pol($a) === ['class' => 'required'], true)
    && $pol('g66_settings') === ['class' => 'required', 'admin' => true]);
$check('deny-by-default: neznámé akce g66_* / a66_* / p63_* jsou zakázané', !empty($pol('g66_neznama')['deny']) && !empty($pol('a66_neznama')['deny']) && !empty($pol('p63_assign_all')['deny']) && !empty($pol('g66_settings_x')['deny']));
$check('politika GET: hodnoceni66|export vyžaduje třídu a každý GET parametr registru v58 má politiku', (teacher59_get_policies()['hodnoceni66|export'] ?? []) === ['class' => 'required']
    && array_reduce(array_keys(teacher58_modules()), static function (bool $c, string $tab): bool {
        foreach (array_keys((array)(teacher58_modules()[$tab]['get'] ?? [])) as $param) if (!isset(teacher59_get_policies()[$tab . '|' . $param])) return false;
        return $c;
    }, true));
$perm = static fn(string $a): ?string => teacher_action_permission($a);
$check('oprávnění: g66_accept = grading.manage, a66_set_kind = content.manage, a66_recompute = analytics.view, g66_settings = accounts.manage, p63_assign_student = content.manage',
    $perm('g66_accept') === 'grading.manage' && $perm('a66_set_kind') === 'content.manage' && $perm('a66_recompute') === 'analytics.view' && $perm('g66_settings') === 'accounts.manage' && $perm('p63_assign_student') === 'content.manage');
$handlerSrc = (string)file_get_contents($root . '/teacher.php');
$check('CSRF: teacher.php ověřuje token před každou POST akcí (včetně a66_/g66_/p63_) a modul v58 volá handler až po něm', str_contains($handlerSrc, 'verify_csrf') || str_contains($handlerSrc, 'hash_equals'), false);
$check('handler: nepovolená třída zastaví POST výjimkou (a66t_can_class) a neznámá akce hodnocení také', (static function () use ($CLASS): bool {
    $_POST = ['class_id' => 'class_xx'];
    try { a66t_handle_post('g66_accept'); return false; } catch (RuntimeException $e) { $first = true; }
    $_POST = ['class_id' => $CLASS];
    try { a66t_handle_post('g66_neznama'); return false; } catch (RuntimeException $e) { return $first; } finally { $_POST = []; }
})());

// --- 13) Rozsah učitelských účtů (režim účtů; poslední sekce) ----------------------------------------------
$mk = static function (string $login, string $role, array $assign): array {
    $id = (string)teacher59_account_create(['login' => $login, 'display_name' => 'Audit ' . $login, 'role' => $role, 'assignments' => $assign], 'audit')['account']['id'];
    return teacher59_account_update($id, static fn(array $a): array => ['must_change_password' => false] + $a); // bez vynucené změny hesla (relace ve stavu ok)
};
$aAdmin = $mk('audit_admin', 'admin', []);
$aTeacher = $mk('audit_ucitel1a', 'teacher', [['class_id' => 'class_1a', 'subject_id' => '*']]);
$aAssist = $mk('audit_asistent3a', 'assistant', [['class_id' => 'class_3a', 'subject_id' => '*']]);
$as = static function (array $account, callable $fn) { teacher59_session_begin($account); try { return $fn(); } finally { teacher59_session_clear(); teacher59_reset_cache(); } };
$post = static fn(string $action, string $class): ?string => teacher59_guard_post_check($action, ['class_id' => $class]);
$check('režim účtů: je zapnutý a administrátor smí g66_settings, g66_accept i p63_assign_student v libovolné třídě', teacher59_mode() === 'accounts' && $as($aAdmin, static fn(): bool => $post('g66_settings', 'class_3a') === null && $post('g66_accept', 'class_1a') === null && $post('p63_assign_student', 'class_3a') === null && teacher59_is_admin()));
$check('rozsah: učitel 1.A nesmí akce v66 v 3.A (class_out_of_scope), v 1.A ano; bez třídy je zamítnut (missing_class)', $as($aTeacher, static fn(): bool => $post('g66_accept', 'class_3a') === 'class_out_of_scope' && $post('a66_set_kind', 'class_3a') === 'class_out_of_scope'
    && $post('a66_recompute', 'class_3a') === 'class_out_of_scope' && $post('p63_assign_student', 'class_3a') === 'class_out_of_scope' && $post('g66_accept', 'class_1a') === null && $post('a66_set_kind', 'class_1a') === null
    && teacher59_guard_post_check('g66_accept', []) === 'missing_class'));
$check('g66_settings smí jen administrátor: učitel i asistent dostanou admin_only i ve vlastní třídě', $as($aTeacher, static fn(): bool => $post('g66_settings', 'class_1a') === 'admin_only') && $as($aAssist, static fn(): bool => $post('g66_settings', 'class_3a') === 'admin_only'));
$check('asistent nemůže převzít návrh, měnit druh testu ani přiřazovat cesty (oprávnění), ale může spustit přepočet analýzy ve své třídě', $as($aAssist, static fn(): bool => !teacher_permission((string)teacher_action_permission('g66_accept')) && !teacher_permission((string)teacher_action_permission('a66_set_kind'))
    && !teacher_permission((string)teacher_action_permission('p63_assign_student')) && teacher_permission((string)teacher_action_permission('a66_recompute')) && $post('a66_recompute', 'class_3a') === null));
$check('učitel má oprávnění převzít návrh, měnit druh testu a přiřazovat cesty, ale ne měnit nastavení návrhu (accounts.manage)', $as($aTeacher, static fn(): bool => teacher_permission((string)teacher_action_permission('g66_accept')) && teacher_permission((string)teacher_action_permission('a66_set_kind'))
    && teacher_permission((string)teacher_action_permission('p63_assign_student')) && !teacher_permission((string)teacher_action_permission('g66_settings'))));
$check('GET export: učitel 1.A dostane 403 důvod class_out_of_scope pro 3.A i bez třídy, pro vlastní 1.A prochází; admin prochází', $as($aTeacher, static fn(): bool => teacher59_guard_get_check('hodnoceni66', ['export' => 'items', 'class' => 'class_3a']) === 'class_out_of_scope'
    && teacher59_guard_get_check('hodnoceni66', ['export' => 'proposals']) === 'class_out_of_scope' && teacher59_guard_get_check('hodnoceni66', ['export' => 'items', 'class' => 'class_1a']) === null)
    && $as($aAdmin, static fn(): bool => teacher59_guard_get_check('hodnoceni66', ['export' => 'items', 'class' => 'class_3a']) === null));
$check('cockpit v režimu účtů: učitel 1.A vidí jen třídu 1.A (žádná jiná třída v nabídce) a handler mu cizí třídu odmítne', $as($aTeacher, static fn(): bool => a66t_classes() === ['class_1a'] && !a66t_can_class('class_3a') && (static function (): bool {
    $_POST = ['class_id' => 'class_3a'];
    try { a66t_handle_post('a66_recompute'); return false; } catch (RuntimeException $e) { return true; } finally { $_POST = []; }
})()));
$check('záložka hodnoceni66 v režimu účtů: ve vlastní třídě nezobrazí žáky jiné třídy ani jejich jména (žádný „Audit Dobry“)', $as($aTeacher, static function (): bool {
    $_GET = [];
    $html = audit_capture(static function (): void { a66t_render_tab('class_3a', 'tok'); });
    return str_contains($html, 'a66-wrap') && !str_contains($html, 'Audit Dobry') && !str_contains($html, 'class_3a');
}));
