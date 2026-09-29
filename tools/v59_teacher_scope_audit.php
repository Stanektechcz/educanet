<?php

declare(strict_types=1);

/**
 * EDUCANET v59 · behaviorální HTTP audit rozsahu učitelských účtů (AUTHZ58-07, PLAN_AUTH §6).
 *   php tools/v59_teacher_scope_audit.php [--verbose]
 * Dočasné úložiště + vestavěný server (tools/lib/http_harness.php). Fixture: A = 1.A + 2.A (grafika),
 * B = 3.A + 4.A (sítě), Admin, Asistent (3.A), E (bez tříd), F (čerstvé OTP). Každá entita (závod, zápas,
 * hra, CTF 3.A i smíšená 2.A+3.A, incident, hodina, dotazník + artefakt, aktivace, intervence, follow-up,
 * watchlist, undo, filtr, skill evidence/přiřazení, ML live/scénář/tip, skupina, peer feedback, demo účet,
 * úloha editoru, otázky banky) nese unikátní značku MK1A…MK4A.
 * Kontroly: (1) sdílený klíč v legacy/accounts, přihlášení, CSRF, fixace session, vynucená změna, deaktivace;
 * (2) každý zvláštní GET – A na 3.A → 403 bez značky (JSON {ok:false}), B/Admin ne-403; (3) každá POST politika
 * s povinnou třídou a každá entitní politika – A s cizí třídou / cizí entitou / bez třídy → 403 a úložiště beze
 * změny, B/Admin ne-403; (4) agregace: žádná záložka A neobsahuje značky 3.A/4.A ani nabídku cizí třídy;
 * (5) předměty (banka, editor, smíšené CTF); (6) admin-only záložky/akce; (7) statika – pokrytí politik.
 * Díry mimo vlastní soubory builderu jsou označené „[DÍRA → soubor]“.
 * Konec: V59_TEACHER_SCOPE_AUDIT_OK checks=N failed=0 (jinak _FAIL a exit 1). Ostrou storage/ nikdy nečte.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
$root = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$verbose = in_array('--verbose', $argv ?? [], true);
require_once __DIR__ . '/lib/audit_storage.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
$tmp = rtrim(str_replace('\\', '/', edu_audit_temp_storage('v59-teacher-scope')), '/');
$sharedKey = 'audit-shared-key-' . bin2hex(random_bytes(6));
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . $sharedKey);
require $root . '/bootstrap.php';
foreach (['teacher_operations_v46.php', 'teacher_operations_plus_v46_1.php', 'teacher_operations_control_v46_2.php', 'intake_v51.php', 'session_v53.php',
    'accounts_v53.php', 'runtime_content.php', 'linux_v57_lab.php', 'arena_v57.php', 'teacher_v58.php', 'robots_v58.php', 'teamgames_v58_teacher_views.php',
    'arena_v58_ctf.php', 'arena_v58_incident.php', 'lab_v58_editor.php', 'teacher_demo_accounts.php', 'teacher_accounts_v59_admin.php',
    'teamgames_v58_projector_views.php', 'intake_v51_teacher.php',
    'points_v53.php', 'points_v60.php', 'marketplace_v60.php', 'marketplace_v60_teacher_views.php',
    'projects_v60.php', 'projects_v60_teacher_views.php', 'feedback_v60.php', 'feedback_v60_teacher_views.php'] as $file) {
    require_once $root . '/' . $file;
}
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $path): string => (string)@file_get_contents($path);
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && !str_starts_with(STORAGE_DIR, str_replace('\\', '/', $root) . '/storage'));
$check('bez souboru účtů je režim legacy', teacher59_mode() === 'legacy');

// --- Fixture (v procesu auditu, před startem serveru) ------------------------------------------
$fx = v59sf_build_fixtures($GLOBALS['modules']);
if ($verbose) foreach ($fx['ids'] as $type => $ids) echo 'INFO  fixture ' . $type . ': ' . json_encode($ids) . PHP_EOL;
$check('fixture: entita 3.A pro každý typ politiky' . ($fx['missing'] ? ' – CHYBÍ: ' . implode(', ', $fx['missing']) : ''), $fx['missing'] === []);
foreach ($fx['errors'] as $err) echo 'INFO  fixture: ' . $err . PHP_EOL;
audit_prewarm_accounts($GLOBALS['modules']);

$h = Harness::start([
    'EDUCANET_STORAGE_DIR' => $tmp,
    'EDUCANET_TEACHER_EXPORT_KEY' => $sharedKey,
    'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0',
]);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
$jars = [];
$csrf = [];
$req = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h, $jarSwap, &$jars): array {
    $jarSwap($jars[$who] ?? []);
    try {
        return $h->request($method, $path, $fields, $headers);
    } finally {
        $jars[$who] = $jarSwap(null);
    }
};
$get = static fn(string $who, array $query, array $headers = []): array => $req($who, 'GET', '/teacher.php?' . http_build_query($query), [], $headers + ['follow_redirects' => false]);
$post = static function (string $who, string $action, array $fields = []) use ($req, &$csrf): array {
    return $req($who, 'POST', '/teacher.php', ['action' => $action, 'csrf' => (string)($csrf[$who] ?? '')] + $fields, ['follow_redirects' => false]);
};
$status = static fn(array $r): int => (int)($r['status'] ?? 0);
$body = static fn(array $r): string => (string)($r['body'] ?? '');
$marker = static fn(string $html): bool => preg_match('/MK[34]A/', $html) === 1;
$phpError = static fn(string $html): bool => preg_match('/Fatal error|Uncaught (Error|Exception)|<b>Warning<\/b>|<b>Deprecated<\/b>|<b>Notice<\/b>/', $html) === 1;
$jsonDenied = static function (array $r) use ($status, $body): bool { $j = json_decode($body($r), true); return $status($r) === 403 && is_array($j) && ($j['ok'] ?? null) === false; };

try {
    // --- 1. Legacy: sdílený klíč funguje, rozsah = vše ------------------------------------------
    $legacy = $req('legacy', 'GET', '/teacher.php');
    $legacyLogin = $req('legacy', 'POST', '/teacher.php', ['action' => 'teacher_login', 'teacher_key' => $sharedKey, 'teacher_name' => 'Legacy Učitel', 'csrf' => (string)$h->csrfToken($body($legacy))]);
    $legacy3a = $get('legacy', ['tab' => 'class_overview', 'class' => 'class_3a']);
    $check('legacy: sdílený klíč přihlásí, třída 3.A dostupná (guard no-op)', $status($legacyLogin) === 200 && $status($legacy3a) === 200 && !$phpError($body($legacy3a)) && str_contains($body($legacy3a), 'teacher_logout'));

    // --- 2. Účty (režim accounts) ---------------------------------------------------------------
    $acc = v59sf_create_accounts();
    $fx = v59sf_owner_rows($fx, $acc);
    $check('režim accounts: 6 účtů (admin, A, B, asistent, E, F s OTP)', teacher59_mode() === 'accounts' && count(teacher59_accounts()) === 6);
    $afterSwitch = $req('legacy', 'GET', '/teacher.php?tab=class_overview&class=class_3a', [], ['follow_redirects' => false]);
    $check('staré legacy session po zapnutí účtů neplatí (přihlašovací karta, žádná data)', $status($afterSwitch) === 200 && str_contains($body($afterSwitch), 'name="login"') && !$marker($body($afterSwitch)) && !str_contains($body($afterSwitch), 'teacher_logout'));
    $keyPage = $req('legacy', 'GET', '/teacher.php');
    $keyTry = $req('legacy', 'POST', '/teacher.php', ['action' => 'teacher_login', 'teacher_key' => $sharedKey, 'teacher_name' => 'X', 'csrf' => (string)$h->csrfToken($body($keyPage))]);
    $check('sdílený klíč v režimu účtů odmítnut (hláška, bez přihlášení)', str_contains($body($keyTry), 'Sdílený učitelský klíč už neplatí') && !str_contains($body($keyTry), 'teacher_logout'));
    $req('csrf', 'GET', '/teacher.php');
    $noCsrf = $req('csrf', 'POST', '/teacher.php', ['action' => 'teacher_login', 'login' => 'bohdan.sitar', 'password' => 'Spatne-heslo-7788'], ['follow_redirects' => false]);
    $check('přihlášení bez CSRF (session s tokenem) → 419', $status($noCsrf) === 419);
    $fresh = $req('fresh', 'POST', '/teacher.php', ['action' => 'teacher_login', 'login' => 'bohdan.sitar', 'password' => 'Spatne-heslo-7788'], ['follow_redirects' => false]);
    $check('přihlášení bez CSRF v úplně nové session → 419 (status ' . $status($fresh) . ')'
        . ($status($fresh) !== 419 ? ' [DÍRA → bootstrap.php:verify_csrf – prázdný token a prázdná session projdou hash_equals, login CSRF]' : ''), $status($fresh) === 419);
    $unknown = audit_login_teacher_account(v59sf_harness_as($h, $jarSwap, $jars, 'anon_u'), 'nikdo.neexistuje', 'Cokoli-heslo-7788');
    $jars['anon_u'] = $jarSwap(null);
    $wrong = audit_login_teacher_account(v59sf_harness_as($h, $jarSwap, $jars, 'anon_w'), 'bohdan.sitar', 'Spatne-heslo-7788');
    $jars['anon_w'] = $jarSwap(null);
    $check('neznámý účet i špatné heslo: stejná obecná hláška, bez přihlášení', str_contains($body($unknown['response']), TEACHER59_MSG_LOGIN) && str_contains($body($wrong['response']), TEACHER59_MSG_LOGIN)
        && !str_contains($body($wrong['response']), 'teacher_logout'));
    foreach (['admin', 'a', 'b', 's', 'e'] as $who) {
        $jars[$who] = ['PHPSESSID' => 'fixace' . $who . bin2hex(random_bytes(8))];
        $fixed = (string)$jars[$who]['PHPSESSID'];
        $login = audit_login_teacher_account(v59sf_harness_as($h, $jarSwap, $jars, $who), $acc['login'][$who], $acc['pw'][$who]);
        $jars[$who] = $jarSwap(null);
        $page = $get($who, ['tab' => 'ucet']);
        $csrf[$who] = (string)$h->csrfToken($body($page));
        $sid = (string)(array_values(array_filter($jars[$who], static fn($v, $k): bool => stripos((string)$k, 'sess') !== false || $k === session_name(), ARRAY_FILTER_USE_BOTH))[0] ?? '');
        $check('přihlášení ' . $who . ': OK, nové ID session (fixace), CSRF token', $status($page) === 200 && str_contains($body($page), 'teacher_logout') && $csrf[$who] !== '' && $sid !== '' && $sid !== $fixed);
    }
    $forcedLogin = audit_login_teacher_account(v59sf_harness_as($h, $jarSwap, $jars, 'f'), $acc['login']['f'], $acc['otp']['f']);
    $jars['f'] = $jarSwap(null);
    $forcedPage = $get('f', ['tab' => 'class_overview', 'class' => 'class_2a']);
    $forcedPoll = $get('f', ['tab' => 'labdata', 'dohled_poll' => '1', 'class' => 'class_2a']);
    $check('OTP: vynucená změna hesla (jen stránka změny, polling 403 JSON)', str_contains($body($forcedPage), 'teacher59_self_password') && !$marker($body($forcedPage)) && !str_contains($body($forcedPage), 'MK2A')
        && $jsonDenied($forcedPoll));

    $snapshot = static fn(): array => v59sf_snapshot($tmp);
    // Zahřátí: první požadavky každé role mohou jednorázově zapsat (provisioning, identita) – poté už nic.
    foreach (['a', 'b', 'admin', 's', 'e'] as $who) { $get($who, ['tab' => 'overview']); }

    // --- 3. Zvláštní GET (§1): A na 3.A → 403 bez značky, B/Admin ne-403 --------------------------
    $id = $fx['ids'];
    $specialGets = [
        'teach|v48_state' => ['tab' => 'teach', 'v48_state' => '1', 'class' => 'class_3a', 'lesson' => '1'],
        'pristupy|print' => ['tab' => 'pristupy', 'print' => '1', 'class' => 'class_3a'],
        'arena|arena_poll' => ['tab' => 'arena', 'arena_poll' => '1', 'race' => $id['race']['3a']],
        'arena|zaznam' => ['tab' => 'arena', 'zaznam' => '1', 'race' => $id['race']['3a']],
        'arena|projector' => ['tab' => 'arena', 'projector' => '1', 'race' => $id['race']['3a']],
        'roboti|robots_poll' => ['tab' => 'roboti', 'robots_poll' => '1', 'match' => $id['match']['3a']],
        'roboti|projector' => ['tab' => 'roboti', 'projector' => '1', 'match' => $id['match']['3a']],
        'hry|tg_poll' => ['tab' => 'hry', 'tg_poll' => '1', 'game' => $id['tg58_game']['3a'], 'v' => ''],
        'hry|projektor' => ['tab' => 'hry', 'projektor' => $id['tg58_game']['3a']],
        'labdata|export' => ['tab' => 'labdata', 'export' => '1', 'class' => 'class_3a'],
        'labdata|dohled_poll' => ['tab' => 'labdata', 'dohled_poll' => '1', 'class' => 'class_3a'],
        'intake|artifact' => ['tab' => 'intake', 'artifact' => $id['intake_response']['3a']],
        'intake|export' => ['tab' => 'intake', 'export' => 'csv', 'class' => 'class_3a'],
        'ctf|event' => ['tab' => 'ctf', 'event' => $id['ctf_event']['3a']],
        'incidenty|session' => ['tab' => 'incidenty', 'session' => $id['inc_session']['3a']],
        'editor|level' => ['tab' => 'editor', 'level' => $id['lab58e_level']['3a']],
        'groups|edit_group' => ['tab' => 'groups', 'edit_group' => $id['group']['3a']],
        'interventions|intervention' => ['tab' => 'interventions', 'intervention' => $id['intervention']['3a']],
        'student360|student' => ['tab' => 'student360', 'student' => $fx['student_3a'], 'class' => 'class_3a'],
        'class_overview|class' => ['tab' => 'class_overview', 'class' => 'class_3a'],
        'session|class' => ['tab' => 'session', 'class' => 'class_4a'],
    ];
    $jsonParams = ['arena_poll', 'robots_poll', 'tg_poll', 'dohled_poll', 'v48_state'];
    foreach ($specialGets as $label => $query) {
        $before = $snapshot();
        $ra = $get('a', $query);
        $isJson = array_intersect(array_keys($query), $jsonParams) !== [];
        $okA = $status($ra) === 403 && !$marker($body($ra)) && (!$isJson || $jsonDenied($ra));
        $check('GET ' . $label . ': A (1.A/2.A) na 3.A/4.A → 403' . ($isJson ? ' JSON {ok:false}' : '') . ', bez značky, úložiště beze změny', $okA && v59sf_same($before, $snapshot()));
        $rb = $get('b', $query);
        $rd = $get('admin', $query);
        $check('GET ' . $label . ': B i Admin ne-403, bez PHP chyby (status ' . $status($rb) . '/' . $status($rd) . ')', $status($rb) !== 403 && $status($rd) !== 403 && $status($rb) < 500 && !$phpError($body($rb)));
    }
    $hadankaA = $get('a', ['tab' => 'arena', 'hadanka_poll' => '1']);
    $hadankaAdmin = $get('admin', ['tab' => 'arena', 'hadanka_poll' => '1']);
    $hadankaJson = json_decode($body($hadankaA), true);
    $check('SEC59-10: GET hádanka_poll: A 200 JSON jen se svými třídami (by_class/top bez 3.A/4.A), Admin 200', $status($hadankaA) === 200 && ($hadankaJson['ok'] ?? null) === true
        && !array_intersect(array_keys((array)($hadankaJson['by_class'] ?? [])), ['class_3a', 'class_4a']) && !array_intersect(array_column((array)($hadankaJson['top'] ?? []), 'class_id'), ['class_3a', 'class_4a'])
        && $status($hadankaAdmin) === 200);
    // SEC59-02: projektor vykresluje podle ?projektor=, guard dřív ověřil jen první parametr (?game=) – smíšené parametry → 403.
    $before = $snapshot();
    $mixedA = $get('a', ['tab' => 'hry', 'game' => $id['tg58_game']['2a'], 'projektor' => $id['tg58_game']['3a']]);
    $mixedA2 = $get('a', ['tab' => 'hry', 'projektor' => $id['tg58_game']['3a'], 'game' => $id['tg58_game']['2a'], 'tg_poll' => '1']);
    $ownProj = $get('a', ['tab' => 'hry', 'projektor' => $id['tg58_game']['2a']]);
    $check('SEC59-02: projektor ?game=<2.A>&projektor=<3.A> jako A → 403 bez značky (i s tg_poll), vlastní projektor 200', $status($mixedA) === 403 && !$marker($body($mixedA))
        && $status($mixedA2) === 403 && !$marker($body($mixedA2)) && $status($ownProj) === 200 && str_contains($body($ownProj), 'MK2A') && v59sf_same($before, $snapshot()));
    teacher59_session_begin((array)teacher59_account($acc['id']['a']));
    $projHtml = audit_capture(static function () use ($id): void { tg58_render_projector((string)$id['tg58_game']['3a']); });
    $projOwn = audit_capture(static function () use ($id): void { tg58_render_projector((string)$id['tg58_game']['2a']); });
    teacher59_session_clear();
    $check('SEC59-02: tg58_render_projector() sám kontroluje rozsah (hra 3.A pro A = „nenalezena“, bez značky; 2.A ano)', str_contains($projHtml, 'nebyla nalezena') && !$marker($projHtml) && str_contains($projOwn, 'MK2A'));
    // SEC59-18: učitelské stránky se neukládají do cache (hlavička před jakýmkoli výstupem, i přihlašovací karta).
    $noStore = static fn(array $r): bool => (bool)preg_grep('/^Cache-Control:.*no-store/i', (array)($r['raw_headers'] ?? [])) && (bool)preg_grep('/^Pragma:\s*no-cache/i', (array)($r['raw_headers'] ?? []));
    $check('SEC59-18: Cache-Control: no-store + Pragma: no-cache na přehledu, záložce třídy i přihlašovací kartě', $noStore($get('a', ['tab' => 'overview'])) && $noStore($get('a', ['tab' => 'class_overview', 'class' => 'class_2a']))
        && $noStore($req('anon_cache', 'GET', '/teacher.php')));
    $cardA = $get('a', ['tab' => 'ucitele', 'karticka' => 'x']);
    $check('GET ucitele&karticka: učitel nedostane kartičku ani správu účtů (403 nebo skrytá záložka)', in_array($status($cardA), [200, 403], true) && !str_contains($body($cardA), 'teacher59_admin_'));
    $foreignStudent = $get('a', ['tab' => 'student360', 'student' => $fx['student_3a']]);
    $check('GET student360 se žákem 3.A bez ?class → 403 (třída z klíče žáka)', $status($foreignStudent) === 403);
    $own = $get('a', ['tab' => 'arena', 'arena_poll' => '1', 'race' => $id['race']['2a']]);
    $check('GET kontrola: A na vlastní závod 2.A → 200 JSON ok', $status($own) === 200 && (json_decode($body($own), true)['ok'] ?? null) === true);

    // --- 4. export_extra_csv.php ------------------------------------------------------------------
    $csvA = $req('a', 'GET', '/export_extra_csv.php?class=class_3a', [], ['follow_redirects' => false]);
    $csvAll = $req('a', 'GET', '/export_extra_csv.php?class=all', [], ['follow_redirects' => false]);
    $csvB = $req('b', 'GET', '/export_extra_csv.php?class=all', [], ['follow_redirects' => false]);
    $check('export_extra_csv: A explicitně 3.A → 403, „all“ bez řádků 3.A/4.A; B vidí svoje', $status($csvA) === 403 && $status($csvAll) === 200 && !$marker($body($csvAll)) && str_contains($body($csvB), 'MK3A'));

    // --- 5. POST politiky: povinná třída (A: cizí i chybějící → 403, úložiště beze změny) ------------
    $policies = teacher59_action_policies();
    $required = array_keys(array_filter($policies, static fn(array $p): bool => ($p['class'] ?? '') === 'required' && !isset($p['entity']) && empty($p['admin'])));
    $leaks = [];
    foreach ($required as $action) {
        foreach ([['class_id' => 'class_3a', 'class_ids' => ['class_4a'], 'classes' => ['class_4a']], []] as $variant) {
            $before = $snapshot();
            $r = $post('a', $action, $variant + v59sf_post_defaults($action, 'class_3a'));
            if ($status($r) !== 403 || !v59sf_same($before, $snapshot())) $leaks[] = $action . ($variant === [] ? '(bez třídy)' : '(' . $variant['class_id'] . ')') . ':' . $status($r) . v59sf_diff_note($before, $snapshot());
        }
    }
    $check('POST ' . count($required) . ' akcí s povinnou třídou: A s 3.A/4.A i bez třídy → 403, úložiště beze změny' . ($leaks ? ' – ' . implode('; ', $leaks) : ''), $leaks === [] && count($required) >= 35);

    // --- 6. POST entitní politiky: A s entitou 3.A → 403, beze změny ------------------------------
    $entityActions = array_filter($policies, static fn(array $p): bool => isset($p['entity']) && empty($p['admin']));
    $leaks = [];
    $untested = [];
    foreach ($entityActions as $action => $policy) {
        [$param, $type] = $policy['entity'];
        $foreign = $id[$type]['3a'] ?? null;
        if ($foreign === null) { $untested[] = $action; continue; }
        $fields = [$param => $foreign] + v59sf_post_defaults($action, 'class_2a');
        if (($policy['class'] ?? '') === 'required') $fields['class_id'] = 'class_2a';
        $before = $snapshot();
        $r = $post('a', $action, $fields);
        if ($status($r) !== 403 || !v59sf_same($before, $snapshot())) $leaks[] = $action . ':' . $status($r) . v59sf_diff_note($before, $snapshot());
        if (!empty($policy['entity_required'])) {
            $before = $snapshot();
            $r = $post('a', $action, ['class_id' => 'class_2a'] + v59sf_post_defaults($action, 'class_2a'));
            if ($status($r) !== 403 || !v59sf_same($before, $snapshot())) $leaks[] = $action . '(bez entity):' . $status($r);
        }
    }
    $check('POST ' . count($entityActions) . ' entitních akcí: A s entitou 3.A (i s class_id=2.A) nebo bez entity → 403, beze změny' . ($leaks ? ' – ' . implode('; ', $leaks) : ''), $leaks === []);
    $check('POST entitní akce: pro každou existuje fixture 3.A' . ($untested ? ' – BEZ FIXTURE: ' . implode(', ', $untested) : ''), $untested === []);
    $mixed = $post('a', 'arena58_ctf_start', ['event' => $id['ctf_event']['mix']]);
    $check('POST CTF smíšená 2.A+3.A: A (jen 2.A) → 403 (akce vyžaduje všechny třídy)', $status($mixed) === 403);
    $editorMix = $post('a', 'lab58e_save', ['class_id' => 'class_2a', 'classes' => ['class_2a', 'class_3a'], 'title' => 'x']);
    $editorOwn3a = $post('a', 'lab58e_save', ['class_id' => 'class_2a', 'classes' => ['class_2a'], 'id' => $id['lab58e_level']['3a'], 'title' => 'x']);
    $check('POST editor: classes ⊄ rozsah → 403, přepsání úlohy 3.A → 403', $status($editorMix) === 403 && $status($editorOwn3a) === 403);

    // --- 7. Předměty (banka otázek) ----------------------------------------------------------------
    $bankNet = $post('a', 'tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'networks', 'prompt' => 'x', 'answer' => 'y']);
    $bankBoth = $post('a', 'tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'both', 'prompt' => 'x', 'answer' => 'y']);
    $bankDelNet = $post('a', 'tg58_bank_delete', ['class_id' => 'class_2a', 'bank_id' => $id['tg58_bank']['networks']]);
    $check('banka: A (grafika) nepřidá sítě ani „obě“, nesmaže otázku sítí → 403', $status($bankNet) === 403 && $status($bankBoth) === 403 && $status($bankDelNet) === 403);
    $before = $snapshot();
    $bankGra = $post('a', 'tg58_bank_add', ['class_id' => 'class_2a', 'line' => 'graphics', 'prompt' => 'Grafika MK2A otázka', 'answer' => 'ano', 'type' => 'text', 'category' => 'HTML', 'difficulty' => '1']);
    $check('banka: A přidá otázku grafiky (ne-403, zápis proběhl)', $status($bankGra) !== 403 && !v59sf_same($before, $snapshot()));
    $bankPage = $get('a', ['tab' => 'hry', 'class' => 'class_2a']);
    $check('banka: A nevidí otázky sítí/obou a nenabízí jejich linii', !str_contains($body($bankPage), 'MKNET') && !str_contains($body($bankPage), 'MKBOTH') && !str_contains($body($bankPage), 'value="networks">Sítě</option><option'));

    // --- 8. Admin-only záložky a akce, asistent, učitel bez tříd -----------------------------------
    foreach (['quality' => 'Data Quality Center', 'provoz' => 'ops58-card', 'identita' => 'identity58_', 'ucitele' => 'teacher59_admin_create'] as $tab => $needle) {
        // Záložka v58 s 'admin' je pro neadmina skrytá (teacher.php pak ukáže přehled); záložka teacher.php vrací 403.
        $hidden = static fn(array $r): bool => $status($r) === 403 || ($status($r) === 200 && !str_contains($body($r), $needle));
        $ra = $get('a', ['tab' => $tab]);
        $rs = $get('s', ['tab' => $tab]);
        $rd = $get('admin', ['tab' => $tab]);
        $check('záložka ' . $tab . ': učitel A i asistent 403/skrytá (bez obsahu), admin ji vidí', $hidden($ra) && $hidden($rs) && $status($rd) === 200 && str_contains($body($rd), $needle));
    }
    foreach (['teacher_sla_policy_save', 'intake_t_sync', 'arena58_weekly_override', 'arena58_weekly_reroll', 'arena58_weekly_reveal', 'identity58_plan', 'teacher59_admin_create', 'teacher59_admin_disable'] as $action) {
        $before = $snapshot();
        $check('POST ' . $action . ': učitel A → 403, beze změny', $status($post('a', $action, ['class_id' => 'class_2a'])) === 403 && v59sf_same($before, $snapshot()));
    }
    $before = $snapshot();
    $check('POST teacher_team_member_role v režimu účtů: nic se nezmění', $status($post('admin', 'teacher_team_member_role', ['member_owner_key' => 'x', 'member_role' => 'admin'])) !== 500 && v59sf_same($before, $snapshot()));
    $before = $snapshot();
    $check('POST neznámá akce: 403, beze změny', $status($post('a', 'neznama_akce_v59', ['class_id' => 'class_2a'])) === 403 && v59sf_same($before, $snapshot()));
    $before = $snapshot();
    $sGrade = $post('s', 'save_grade', ['class_id' => 'class_3a', 'target_id' => $fx['student_3a'], 'project_id' => 'x', 'grade' => '1']);
    $check('asistent (3.A): save_grade nic nezapíše (oprávnění v46), status ' . $status($sGrade), in_array($status($sGrade), [302, 303, 403], true) && v59sf_same($before, $snapshot()));
    $check('asistent: 3.A ano, 4.A → 403', $status($get('s', ['tab' => 'class_overview', 'class' => 'class_3a'])) === 200 && $status($get('s', ['tab' => 'class_overview', 'class' => 'class_4a'])) === 403);
    $eOverview = $get('e', ['tab' => 'overview']);
    $check('učitel bez tříd: stránka „Nemáte přiřazené třídy“, žádná data', str_contains($body($eOverview), 'přiřazen') && !$marker($body($eOverview)) && !str_contains($body($eOverview), 'MK1A') && !str_contains($body($eOverview), 'MK2A'));
    $check('učitel bez tříd: POST s třídou → 403', $status($post('e', 'teacher_bulk_mark', ['class_id' => 'class_2a'])) === 403);

    // --- 8b. SEC59-06: upozornění a cron automatizací respektují rozsah účtu ------------------------
    $keyA = teacher59_owner_key_for($acc['id']['a']);
    $isoNow = date(DATE_ATOM);
    foreach ([['nt_mk3a', 'class_3a', '', 'MK3A upozornění'], ['nt_mk4a_old', null, 'teacher.php?tab=class_results&class=class_4a', 'MK4A staré upozornění'],
        ['nt_mk2a', 'class_2a', '', 'MK2A upozornění']] as [$nid, $nclass, $nurl, $ntitle]) {
        v59sf_push('teacher_notifications.json.php', array_filter(['id' => $nid, 'owner_key' => $keyA, 'class_id' => $nclass, 'title' => $ntitle, 'text' => $ntitle, 'url' => $nurl, 'kind' => 'filter', 'created_at' => $isoNow, 'read_at' => null], static fn($v): bool => $v !== null));
    }
    $autoPage = $get('a', ['tab' => 'automations']);
    $check('SEC59-06: A vidí jen upozornění svých tříd (2.A ano; 3.A podle class_id ani 4.A podle starého odkazu ne)', $status($autoPage) === 200 && str_contains($body($autoPage), 'MK2A upozornění')
        && !str_contains($body($autoPage), 'MK3A upozornění') && !str_contains($body($autoPage), 'MK4A staré upozornění'));
    foreach ([['au_mk3a', 'sf_mk3a_a'], ['au_mk2a', 'sf_mk2a']] as [$aid3, $fid]) {
        v59sf_push('teacher_automations.json.php', ['id' => $aid3, 'owner_key' => $keyA, 'owner_label' => 'Alena Učitelová', 'team_key' => teacher_team_key(), 'filter_id' => $fid, 'filter_name' => 'Audit',
            'trigger' => 'any_change', 'enabled' => true, 'last_student_keys' => ['class_x:student:nikdo'], 'last_count' => 99, 'last_run_at' => null, 'created_at' => $isoNow, 'updated_at' => $isoNow]);
    }
    $tick = proc_open([PHP_BINARY, $root . '/tools/v46_automation_tick.php'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $tickPipes, null, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => $tmp]));
    $tickOut = is_resource($tick) ? (string)stream_get_contents($tickPipes[1]) . (string)stream_get_contents($tickPipes[2]) : '';
    if (is_resource($tick)) { fclose($tickPipes[1]); fclose($tickPipes[2]); proc_close($tick); }
    $tickRows = array_values(array_filter(storage_read(STORAGE_DIR . '/teacher_notifications.json.php', false), static fn($r): bool => is_array($r) && ($r['kind'] ?? '') === 'filter' && ($r['owner_key'] ?? '') === $keyA && !str_starts_with((string)($r['id'] ?? ''), 'nt_mk')));
    $tickClasses = array_map(static fn(array $r): string => (string)($r['class_id'] ?? '?'), $tickRows);
    $check('SEC59-06: cron (bez relace) nepošle A upozornění z filtru 3.A mimo přiřazení; 2.A ano a nese class_id (' . implode(',', $tickClasses) . ')',
        str_contains($tickOut, 'Notifications:') && in_array('class_2a', $tickClasses, true) && !in_array('class_3a', $tickClasses, true) && !in_array('?', $tickClasses, true));
    // SEC59-08: CSV export dotazníku neutralizuje vzorce (=, +, -, @) ve všech textových buňkách.
    intake_v51_update('responses', static function (array $rows) use ($isoNow): array {
        $rows[] = ['id' => 'resp_csv_mk2a', 'class_id' => 'class_2a', 'source' => 'audit', 'submitted_at' => $isoNow, 'answers' => ['about' => '@SUM(A1:A9)'],
            'student' => ['first_name' => '=HYPERLINK("http://example.invalid")', 'last_name' => '+Vzorec', 'preferred_name' => '-2+3', 'seat_label' => 'R1-L2'], 'assessment' => ['type' => 'none']];
        return $rows;
    });
    $csvIntake = $get('a', ['tab' => 'intake', 'export' => 'csv', 'class' => 'class_2a']);
    $check('SEC59-08: CSV dotazníku – buňky začínající =, +, -, @ mají prefix apostrofu', $status($csvIntake) === 200 && str_contains($body($csvIntake), "'=HYPERLINK") && str_contains($body($csvIntake), "'+Vzorec")
        && str_contains($body($csvIntake), "'-2+3") && !preg_match('/(^|;)"?=HYPERLINK/m', $body($csvIntake))
        && intake_v51_teacher_csv_row(['=1+2', '+x', '-y', '@z', 'ok', 5, "\tTab"]) === ["'=1+2", "'+x", "'-y", "'@z", 'ok', '5', "'\tTab"]);

    // --- 9. Agregace a navigace: A nikde nevidí 3.A/4.A ------------------------------------------
    $tabs = array_values(array_diff(array_merge(['session', 'arena', 'pristupy', 'intake', 'attention', 'control', 'reports', 'communications', 'overview', 'class_overview',
        'class_results', 'analytics', 'interventions', 'automations', 'filters', 'demo_accounts', 'ops_audit', 'calendar', 'curriculum', 'teach', 'growth', 'grade',
        'groups', 'workspace', 'skills', 'mastery', 'authoring', 'history', 'ucet', 'team_admin'], teacher58_tabs()), TEACHER59_ADMIN_TABS));
    $label3a = $fx['label_3a_only'];
    foreach ($tabs as $tab) {
        $r = $get('a', ['tab' => $tab]);
        $html = $body($r);
        $leak = [];
        if ($marker($html)) { preg_match_all('/[^<>]{0,40}MK[34]A[^<>]{0,20}/u', $html, $m); $leak[] = 'značka: ' . implode(' | ', array_slice(array_unique($m[0]), 0, 3)); }
        foreach (['class_3a', 'class_4a'] as $foreign) {
            if (str_contains($html, 'value="' . $foreign . '"')) $leak[] = 'nabídka ' . $foreign;
            // calendar.ics.php = rozvrh bez osobních dat pro libovolnou třídu (rozhodnutí plánu) – odkaz na něj není únik.
            if (preg_match('/(?<!calendar\.ics\.php)[?&;]class=' . $foreign . '\b/', $html)) $leak[] = 'odkaz ' . $foreign;
        }
        if ($label3a !== '' && str_contains($html, $label3a)) $leak[] = 'jméno žáka 3.A';
        if ($phpError($html)) $leak[] = 'PHP chyba';
        $check('agregace A · záložka ' . $tab . ': 200, bez dat/nabídky 3.A/4.A' . v59sf_owner_note($tab, $leak), $status($r) === 200 && $leak === []);
    }
    $report = $post('a', 'teacher_ops_report_export', ['report_format' => 'json']);
    $check('agregace A · export reportu (JSON): jen 1.A/2.A', $status($report) === 200 && !str_contains($body($report), 'class_3a') && !str_contains($body($report), 'class_4a') && str_contains($body($report), 'class_2a'));
    $reportCsv = $post('a', 'teacher_ops_report_export', ['report_format' => 'csv']);
    $check('agregace A · export reportu (CSV): bez 3.A/4.A', $status($reportCsv) === 200 && !str_contains($body($reportCsv), '3.A') && !str_contains($body($reportCsv), '4.A'));
    foreach (['arena' => 'MK3A závod', 'editor' => 'MK3A úloha', 'filters' => 'MK3A filtr', 'demo_accounts' => 'MK3A Demo', 'ctf' => 'MK3A CTF', 'incidenty' => 'MK3A incident'] as $tab => $needle) {
        $rb = $get('b', ['tab' => $tab, 'class' => 'class_3a']);
        $check('kontrola značek: B na ' . $tab . ' 3.A vidí „' . $needle . '“', $status($rb) === 200 && str_contains($body($rb), $needle));
    }
    // Kontrola, že agregace vůbec vykreslují seznamy (jinak by absence značek 3.A nic neznamenala): A vidí vlastní 2.A.
    foreach (['ops_audit' => 'MK2A audit', 'filters' => 'MK2A filtr', 'attention' => 'MK2A follow-up', 'editor' => 'MK2A úloha', 'demo_accounts' => 'MK2A Demo', 'arena' => 'MK2A závod'] as $tab => $needle) {
        $ra = $get('a', ['tab' => $tab, 'class' => 'class_2a']);
        $check('kontrola značek: A na ' . $tab . ' 2.A vidí vlastní „' . $needle . '“', $status($ra) === 200 && str_contains($body($ra), $needle));
    }

    // --- 9b. Převzetí starých dat: filtr v2 (token prohlížeče + jméno) je po převzetí vlastní --------
    $actorToken = (string)($jars['a']['educanet_teacher_actor'] ?? '');
    v59sf_push('teacher_saved_filters.json.php', ['id' => 'sf_legacy_a', 'owner_key' => teacher59_legacy_owner_key($actorToken, 'Alena Učitelová'), 'owner_version' => 2, 'owner_label' => 'Alena Učitelová',
        'scope' => 'personal', 'team_key' => '', 'name' => 'MK2A starý filtr v2', 'class_id' => 'class_2a', 'status' => 'all', 'priority' => 'all', 'task_status' => 'all', 'task_due' => 'all',
        'rule_mode' => 'all', 'rules' => [], 'pinned' => false, 'default_for' => 'none', 'created_at' => date(DATE_ATOM), 'updated_at' => date(DATE_ATOM)]);
    $beforeReclaim = $get('a', ['tab' => 'filters', 'class' => 'class_2a']);
    $reclaim = $post('a', 'teacher59_self_reclaim', ['legacy_name' => 'Alena Učitelová']);
    $afterReclaim = $get('a', ['tab' => 'filters', 'class' => 'class_2a']);
    $rowAfter = array_values(array_filter(storage_read(STORAGE_DIR . '/teacher_saved_filters.json.php', false), static fn($r): bool => is_array($r) && ($r['id'] ?? '') === 'sf_legacy_a'))[0] ?? [];
    $check('převzetí starých dat: filtr v2 není před převzetím vidět, po „Převzít moje stará data“ je vlastní (v3)', $actorToken !== '' && !str_contains($body($beforeReclaim), 'MK2A starý filtr v2')
        && in_array($status($reclaim), [302, 303], true) && str_contains($body($afterReclaim), 'MK2A starý filtr v2') && (int)($rowAfter['owner_version'] ?? 0) === 3
        && (string)($rowAfter['owner_key'] ?? '') === teacher59_owner_key_for($acc['id']['a']));

    // --- 10. B a Admin: vlastní/cizí akce projdou guardem ---------------------------------------
    $bOk = [];
    foreach (v59sf_allowed_posts($id) as [$who, $action, $fields]) {
        $r = $post($who, $action, $fields);
        if (!in_array($status($r), [200, 302, 303], true)) $bOk[] = $who . ':' . $action . ':' . $status($r);
    }
    $check('POST B/Admin: akce nad 3.A (třída i entity) nejsou zamítnuté guardem' . ($bOk ? ' – ' . implode('; ', $bOk) : ''), $bOk === []);

    // --- 11. Deaktivace a odhlášení ---------------------------------------------------------------
    teacher59_account_set_status($acc['id']['b'], 'disabled', $acc['id']['admin']);
    $afterDisable = $get('b', ['tab' => 'class_overview', 'class' => 'class_3a']);
    $check('deaktivace B: otevřená relace okamžitě bez přístupu', $status($afterDisable) !== 403 ? !str_contains($body($afterDisable), 'teacher_logout') && !$marker($body($afterDisable)) : true);
    $logout = $post('a', 'teacher_logout');
    $afterLogout = $get('a', ['tab' => 'overview']);
    $check('odhlášení A: další požadavek bez přístupu', in_array($status($logout), [200, 302, 303], true) && str_contains($body($afterLogout), 'name="login"'));
    $log = $read(teacher59_log_path()) . $read(teacher59_noise_path());
    $check('log: zamítnutí zapsaná (scope_denied v hlučném logu, ne v bezpečnostním), bez hesel a OTP', str_contains($read(teacher59_noise_path()), '"scope_denied"') && !str_contains($read(teacher59_log_path()), '"scope_denied"')
        && !array_filter(array_merge(array_values($acc['pw']), array_values($acc['otp'])), static fn(string $s): bool => $s !== '' && str_contains($log, $s)));
} finally {
    $h->stop();
}

// --- 12. Statika: pokrytí politik -------------------------------------------------------------------
$static = v59sf_static_coverage($root);
$check('statika: každá POST akce z teacher.php a handlerů má ne-deny politiku (' . $static['count'] . ')' . ($static['denied'] ? ' – CHYBÍ: ' . implode(', ', $static['denied']) : ''), $static['denied'] === [] && $static['count'] >= 115, false);
$check('statika: každý GET parametr registru v58 má politiku' . ($static['get_missing'] ? ' – CHYBÍ: ' . implode(', ', $static['get_missing']) : ''), $static['get_missing'] === [], false);
$check('statika: změněné moduly hlídají rozsah přes function_exists (no-op v legacy/CLI)', $static['guards_missing'] === [], false);
if ($static['guards_missing']) echo 'INFO  bez guardu: ' . implode(', ', $static['guards_missing']) . PHP_EOL;
$teacherSrc = $read($root . '/teacher.php');
$catchBlock = (string)strstr((string)strstr($teacherSrc, "    } catch (Throwable \$e) {\n        // v59 · SEC59-16"), 'teacher_redirect(', true);
$check('SEC59-16: teacher.php/intake/registrace ukazují text jen u RuntimeException, jinak obecná hláška + error_log', str_contains($catchBlock, '$e instanceof RuntimeException ? $e->getMessage() : \'Akci se nepodařilo dokončit.\'') && str_contains($catchBlock, 'error_log(')
    && str_contains($read($root . '/intake_v51.php'), '$e instanceof RuntimeException ? $e->getMessage() : \'\'') && str_contains($read($root . '/app/actions/session_join.php'), '$joinError instanceof RuntimeException ? $joinError->getMessage() : \'\'')
    && str_contains($read($root . '/lang/en/ui/auth.php'), "'Akci se nepodařilo dokončit.' =>") && str_contains($read($root . '/lang/uk/ui/auth.php'), "'Akci se nepodařilo dokončit.' =>"), false);
$check('SEC59-14: cookie educanet_teacher_actor má Secure podle educanet_is_https() (i za proxy)', str_contains($read($root . '/teacher_tasks.php'), "'secure'=>educanet_is_https()"), false);
$check('SEC59-18: teacher.php posílá no-store hned po bootstrapu (před jakýmkoli výstupem)', (int)strpos($teacherSrc, "header('Cache-Control: no-store, max-age=0')") > 0
    && (int)strpos($teacherSrc, "header('Cache-Control: no-store, max-age=0')") < (int)strpos($teacherSrc, '<!doctype html>'), false);

exit(audit_summary($state, 'V59_TEACHER_SCOPE'));
