<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v72 · audit schvalování obsahu lekcí, exit ticketu a žákovského bloku.
 *   1) soubory vrstvy, registr modulu `schvalovani` (nový modul, ne `hodina`), mapa a tmavý režim, politiky a oprávnění,
 *   2) schvalování: otisk obsahu, stavy navrh → schváleno → změněno / vráceno, validace (otisk, důvod ≤ 300 znaků), append-only log,
 *      neschválený obsah žák nevidí,
 *   3) exit ticket: jen schválená lekce, jedna odpověď, úložiště bez jmen, retence, souhrn pro učitele, důkaz v62 jen v pilotu, formativní,
 *   4) žákovský blok bez JS (formulář s CSRF, žádné prozrazení odpovědi),
 *   5) HTTP bez účtů: záložka, náhled, lc72_* vyžaduje CSRF, Dnešní hodina se souhrnem, žák a budoucí lekce,
 *   6) HTTP v režimu účtů: cizí třída 403 (GET i POST), asistent jen čte, učitel třídy schválí.
 *   php tools/v72_approval_audit.php     Dočasné úložiště i cache modelu. Konec: V72_APPROVAL_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v72-approval')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V72A_KEY = 'v72-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V72A_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_operations_control_v46_2.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'ui_v67.php',
    'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php', 'session_v53.php', 'accounts_v53.php', 'intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
foreach (array_unique(array_merge((array)teacher58_modules()['hodina']['files'], (array)teacher58_modules()['schvalovani']['files'])) as $f) require_once $ROOT . '/' . $f;
require_once $ROOT . '/lesson_exit_v72_views.php';
require_once __DIR__ . '/lib/v62_competency_fixtures.php';
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['lesson_approval_v72.php', 'lesson_approval_v72_views.php', 'lesson_exit_v72.php', 'lesson_exit_v72_views.php', 'lesson_glossary_v72.php', 'app/actions/lesson_exit_v72.php'];
$cli = ['tools/v72_lesson_content_audit.php', 'tools/v72_approval_audit.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if (!str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v72: strict_types, guard (knihovny SCRIPT_FILENAME, CLI PHP_SAPI), ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [], false);
$mods = teacher58_modules();
$sch = $mods['schvalovani'] ?? [];
$check('registr: nový modul `schvalovani` (POST jen prefix lc72_, bez zvláštního GET, CSS v71+v72); `hodina` dál bez post/get, jen čte stav a souhrn v72',
    array_keys((array)($sch['post'] ?? [])) === ['lc72_'] && !isset($sch['get']) && ($sch['css'] ?? []) === ['assets/cockpit-v71.css', 'assets/cockpit-v72.css'] && in_array('schvalovani', teacher58_tabs(), true)
    && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get']) && in_array('lesson_exit_v72_views.php', $mods['hodina']['files'], true));
$map = teacher68_tab_map();
$check('mapa cockpitu: Schvalování lekcí v sekci Výuka, v UI68_TEACHER_DARK_TABS, „Související“ vede na Dnešní hodinu', ($map['schvalovani']['section'] ?? '') === 'vyuka' && in_array('schvalovani', UI68_TEACHER_DARK_TABS, true)
    && array_column(c70_related_rules()['schvalovani'] ?? [], 0) === ['hodina', 'curriculum'] && in_array('schvalovani', teacher68_visible_tabs('assistant', false, teacher58_tabs()), true));
$check('politiky: lc72_approve / lc72_return = třída povinná a v rozsahu; jiné lc72_* zakázané; oprávnění content.manage (učitel ano, asistent ne)',
    teacher59_action_policy('lc72_approve') === ['class' => 'required'] && teacher59_action_policy('lc72_return') === ['class' => 'required'] && teacher59_action_policy('lc72_delete')['deny'] === true
    && teacher_action_permission('lc72_approve') === 'content.manage' && teacher_action_permission('lc72_return') === 'content.manage'
    && teacher_permission_for_role('teacher', 'content.manage') && !teacher_permission_for_role('assistant', 'content.manage'));
$css = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/cockpit-v72.css') . $read('assets/lesson-v72.css'));
$check('CSS v72 (cockpit + žák): jen tokeny, viditelný fokus, cíle ≥ 44 px, mobilní zalomení, celkem ≤ 8 kB', preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', $css) !== 1
    && substr_count($css, 'outline: var(--ui-focus, 3px) solid var(--ui-accent)') >= 2 && substr_count($css, 'min-height: 44px') >= 3 && substr_count($css, '@media (max-width: 480px)') === 2
    && strlen($read('assets/cockpit-v72.css') . $read('assets/lesson-v72.css')) <= 8192, false);

// ---------------------------------------------------------------- 2) schvalování (čisté funkce + dočasné úložiště)
$ov5 = lc72_overlay('class_3a', 5);
$h5 = lc72_overlay_hash($ov5);
$altered = $ov5;
$altered['exit_ticket']['variants'][0]['question'] .= ' (upraveno)';
$check('otisk obsahu: 16 hex, stabilní, nezávisí na stavu a souboru, změní se při změně textu', preg_match('/^[0-9a-f]{16}$/', $h5) === 1 && $h5 === lc72_overlay_hash(['status' => 'schvaleno', '_file' => 'x'] + $ov5)
    && $h5 !== lc72_overlay_hash($altered));
$lightSame = true;
foreach (LM71_CLASSES as $c) foreach (range(1, LM71_LESSONS) as $n) $lightSame = $lightSame && lc72_overlay_light($c, $n) === lc72_overlay($c, $n);
$check('rychlá cesta žáka: overlay jen ze souborů třídy je shodný s modelem lm71 u všech 4 × 28 lekcí (stejný otisk)', $lightSame);
$check('výchozí stav: L5 3.A je návrh (žák nevidí, exit ticket nedostupný), L2 je původní obsah bez návrhu', lc72_status('class_3a', 5)['status'] === 'navrh' && lc72_student_overlay('class_3a', 5) === null
    && lx72_ticket('class_3a', 5) === null && lc72_status('class_3a', 2)['status'] === 'puvodni' && lc72_overlay_lessons('class_3a') === range(5, 16));
$throws = static function (callable $fn): bool { try { $fn(); } catch (RuntimeException $e) { return true; } return false; };
$check('validace: cizí otisk, vrácení bez důvodu, důvod > 300 znaků, lekce bez návrhu i neznámá akce se odmítnou a nic nezapíšou',
    $throws(static fn() => lc72_decide('lc72_approve', 'class_3a', 5, str_repeat('0', 16), '', 'Audit')) && $throws(static fn() => lc72_decide('lc72_return', 'class_3a', 5, $h5, '   ', 'Audit'))
    && $throws(static fn() => lc72_decide('lc72_return', 'class_3a', 5, $h5, str_repeat('a', 301), 'Audit')) && $throws(static fn() => lc72_decide('lc72_approve', 'class_3a', 2, $h5, '', 'Audit'))
    && $throws(static fn() => lc72_decide('lc72_delete', 'class_3a', 5, $h5, '', 'Audit')) && !is_file(lc72_path()));
lc72_decide('lc72_approve', 'class_3a', 5, $h5, '', 'Audit Učitel');
$st = lc72_status('class_3a', 5);
$check('schválení: stav „schváleno“, žák vidí overlay a exit ticket se 3 variantami; ostatní lekce dál návrh', $st['status'] === 'schvaleno' && $st['by'] === 'Audit Učitel' && is_array(lc72_student_overlay('class_3a', 5))
    && count((array)lx72_ticket('class_3a', 5)['variants']) === 3 && lc72_status('class_3a', 6)['status'] === 'navrh' && lc72_status('class_1a', 5)['status'] === 'navrh');
storage_update(lc72_path(), static function (array $d): array { $d['lessons']['class_3a']['9']['status'] = 'schvaleno'; $d['lessons']['class_3a']['9']['hash'] = 'ffffffffffffffff'; return $d; });
$check('změna po schválení: jiný otisk → „změněno po schválení“, žák znovu vidí původní lekci', lc72_status('class_3a', 9)['status'] === 'zmeneno' && lc72_student_overlay('class_3a', 9) === null);
$h6 = lc72_overlay_hash(lc72_overlay('class_3a', 6));
lc72_decide('lc72_return', 'class_3a', 6, $h6, "Zkraťte  úvod.\n", 'Audit Učitel');
$log = (array)(storage_read(lc72_path())['log'] ?? []);
$check('vrácení: stav „vráceno“ s důvodem (normalizované mezery), žák nevidí; log je append-only (2 záznamy, bez dat žáků)', lc72_status('class_3a', 6)['status'] === 'vraceno'
    && lc72_status('class_3a', 6)['note'] === 'Zkraťte úvod.' && lc72_student_overlay('class_3a', 6) === null && count($log) === 2 && array_keys($log[0]) === ['class', 'lesson', 'status', 'hash', 'at', 'by']);
$ovw = lc72_overview('class_3a', '2026-10-08');
$check('přehled třídy: 12 lekcí, datum výuky z kalendáře, T-14 jen u neschválených lekcí v příštích 14 dnech', count($ovw) === 12 && $ovw[0]['date'] !== ''
    && array_reduce($ovw, static fn(bool $ok, array $r): bool => $ok && ($r['t14'] === ($r['days'] !== null && $r['days'] >= 0 && $r['days'] <= LC72_T14_DAYS && $r['status']['status'] !== 'schvaleno')), true));

// ---------------------------------------------------------------- 3) exit ticket
$fx = v62fx_seed($tmp, time());
$key = static fn(string $who): string => v62fx_key($who);
$asStudent = static function (string $who): void { $_SESSION['next_class_id'] = V62FX_CLASS; $_SESSION['student_label'] = V62FX_LABELS[$who]; };
$t5 = lx72_ticket('class_3a', 5);
$vGood = $t5['variants'][lx72_variant_index($key('good'), 5, 3)];
$wrong = ($vGood['correct'] + 1) % count($vGood['options']);
$asStudent('good');
$r1 = lx72_submit('class_3a', $key('good'), 5, $wrong);
$r2 = lx72_submit('class_3a', $key('good'), 5, $vGood['correct']);
$r3 = lx72_submit('class_3a', $key('player'), 5, 9);
$r4 = lx72_submit('class_3a', $key('player'), 6, 0);
$raw = (string)file_get_contents(lx72_path('class_3a'));
$check('odpověď: uloží se jednou (druhá = duplicita), neplatná volba a neschválená lekce se odmítnou; v souboru jen hash žáka, žádné jméno',
    $r1['ok'] && !$r1['correct'] && $r2['error'] === 'duplicate' && $r3['error'] === 'choice' && $r4['error'] === 'none' && !str_contains($raw, 'Audit')
    && preg_match_all('/"[0-9a-f]{20}":/', $raw) === 1 && lx72_answer_of('class_3a', $key('good'), 5)['c'] === $wrong);
$ev = array_values(array_filter(ev62_read((string)identity58_current_student_id()), static fn(array $r): bool => $r['source'] === 'lesson' && str_starts_with($r['artefact_ref'], 'lx72:l05:')));
$check('pilot 3.A: odpověď zapsala důkaz v62 (zdroj lesson, kompetence z katalogu, skóre 0 za chybu, bez textu odpovědi)', $r1['evidence'] === 1 && count($ev) === 1
    && $ev[0]['competency'] === (string)$t5['competence'] && (float)$ev[0]['score'] === 0.0);
$asStudent('player');
$vPlayer = $t5['variants'][lx72_variant_index($key('player'), 5, 3)];
$r5 = lx72_submit('class_3a', $key('player'), 5, $vPlayer['correct']);
$sum = lx72_summary('class_3a', 5, array_map($key, ['good', 'player', 'none', 'left']));
$check('souhrn pro učitele: odpovědělo 2 ze 4, 50 % správně, nejčastější chybná odpověď = text zvolené možnosti, kompetence z katalogu v62',
    $r5['ok'] && $r5['correct'] && $sum['responded'] === 2 && $sum['students'] === 4 && $sum['percent'] === 50 && ($sum['wrong_top']['text'] ?? '') === $vGood['options'][$wrong]
    && $sum['competence'] === (string)comp62_competencies('os_site')[(string)$t5['competence']]['label'] && count($sum['variants']) === 3);
$h2a = lc72_overlay_hash(lc72_overlay('class_2a', 5));
lc72_decide('lc72_approve', 'class_2a', 5, $h2a, '', 'Audit Učitel');
$r6 = lx72_submit('class_2a', project_student_key('class_2a', 'Audit Druhak'), 5, 0);
$old = ['5' => ['aaaa' => ['v' => 0, 'c' => 0, 'ok' => 1, 'at' => date(DATE_ATOM, time() - 400 * 86400)], 'bbbb' => ['v' => 0, 'c' => 0, 'ok' => 1, 'at' => date(DATE_ATOM)]]];
$variantsUsed = count(array_unique(array_map(static fn(int $i): int => lx72_variant_index('k' . $i, 5, 3), range(1, 30))));
$check('mimo pilot (2.A bez katalogu) se důkaz nezapisuje; retence maže odpovědi starší než ' . LX72_RETENTION_DAYS . ' dní; varianta je deterministická a třída dostane všechny 3',
    $r6['ok'] && $r6['evidence'] === 0 && array_keys(lx72_purge($old, time())['5']) === ['bbbb'] && $variantsUsed === 3 && lx72_variant_index('k1', 5, 3) === lx72_variant_index('k1', 5, 3));
$check('formativní: exit ticket nevstupuje do návrhů hodnocení (G66_MASTERY_SOURCES bez „lesson“), domácí příprava jen volitelná v limitu',
    LC72_EXIT_FORMATIVE_ONLY === true && str_contains($read('grading_v66.php'), "const G66_MASTERY_SOURCES = ['test', 'project', 'lab'];") && lx72_homework('class_3a', 5) !== []
    && array_reduce(lx72_homework('class_3a', 5), static fn(bool $ok, array $h): bool => $ok && $h['minutes'] <= LC72_HOMEWORK_MAX_MIN, true) && lx72_homework('class_3a', 6) === []);

// ---------------------------------------------------------------- 4) žákovský blok (bez JS)
$asStudent('none');
$blk6 = audit_capture(static fn() => lx72_render_student_block('class_3a', 6));
$blk5 = audit_capture(static fn() => lx72_render_student_block('class_3a', 5));
$vNone = $t5['variants'][lx72_variant_index($key('none'), 5, 3)];
$asStudent('good');
$blkGood = audit_capture(static fn() => lx72_render_student_block('class_3a', 5));
$check('žák: neschválená lekce = žádný blok; schválená = cíl, kritéria, exit ticket jako formulář (POST, CSRF, lx72_answer, radio required) bez JS a bez prozrazení odpovědi; domácí příprava „volitelné“',
    $blk6 === '' && str_contains($blk5, 'Cíl dnešní hodiny') && str_contains($blk5, 'name="action" value="lx72_answer"') && str_contains($blk5, 'name="csrf"')
    && substr_count($blk5, 'type="radio"') === count($vNone['options']) && str_contains($blk5, 'required') && !str_contains($blk5, '<script') && !str_contains($blk5, e($vNone['explanation']))
    && str_contains($blk5, 'Domácí příprava (volitelné)') && str_contains($blk5, e($vNone['question'])));
$check('žák po odpovědi: zpětná vazba (tvoje odpověď, správná odpověď, vysvětlení) místo formuláře', str_contains($blkGood, 'Tentokrát ne.') && str_contains($blkGood, e($vGood['options'][$vGood['correct']]))
    && !str_contains($blkGood, 'value="lx72_answer"'));

// ---------------------------------------------------------------- 5) HTTP bez účtů
audit_prewarm_accounts($GLOBALS['modules']);
$year = require $ROOT . '/school_year.php';
$current = v56_current_lesson_number('class_3a', $year);
$h = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V72A_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_DEV_BYPASS' => '1', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
try {
    $login = audit_login_teacher($h, V72A_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a', 'lesson' => '7']);
    $body = (string)$page['body'];
    $h7 = lc72_overlay_hash(lc72_overlay('class_3a', 7));
    $check('HTTP: záložka Schvalování (3.A, L7) – čistá odpověď, CSS v72, 12 lekcí, náhled s plánem, rubrikou, variantami exit ticketu a formuláři s otiskem',
        audit_response_clean($page) && str_contains($body, 'assets/cockpit-v72.css') && substr_count($body, '?tab=schvalovani&amp;class=class_3a&amp;lesson=') >= 12 && str_contains($body, 'Rubrika (1–4)')
        && str_contains($body, 'value="lc72_approve"') && str_contains($body, 'value="' . $h7 . '"') && str_contains($body, 'Plán hodiny (90 min)') && str_contains($body, 'Glosář'));
    $csrf = (string)$h->csrfToken($body);
    $noCsrf = $h->request('POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '7', 'hash' => $h7], ['follow_redirects' => false]);
    $badHash = $h->request('POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '7', 'hash' => str_repeat('a', 16), 'csrf' => $csrf, 'return_tab' => 'schvalovani'], ['follow_redirects' => false]);
    $unknown = $h->request('POST', '/teacher.php', ['action' => 'lc72_delete', 'class_id' => 'class_3a', 'lesson' => '7', 'hash' => $h7, 'csrf' => $csrf], ['follow_redirects' => false]);
    $check('HTTP: lc72_approve bez CSRF → 419, s cizím otiskem i neznámá lc72_* akce nic neschválí', (int)$noCsrf['status'] === 419 && in_array((int)$badHash['status'], [302, 303], true)
        && lc72_status('class_3a', 7)['status'] === 'navrh');
    $ok = $h->request('POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '7', 'hash' => $h7, 'csrf' => $csrf, 'return_tab' => 'schvalovani'], ['follow_redirects' => false]);
    $after = (string)$h->request('GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a', 'lesson' => '7'])['body'];
    $check('HTTP: schválení s CSRF a otiskem → přesměrování zpět na záložku, stav „schváleno“, hláška o schválení', in_array((int)$ok['status'], [302, 303], true) && str_contains((string)($ok['headers']['Location'] ?? ''), 'tab=schvalovani')
        && lc72_status('class_3a', 7)['status'] === 'schvaleno' && str_contains($after, 'je schválená') && !str_contains($after, 'value="lc72_approve"'));
    $today = $h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a', 'lesson' => '5']);
    $todayBody = (string)$today['body'];
    $check('HTTP: Dnešní hodina L5 – stav „schváleno“, souhrn exit ticketu (odpovědělo, % správně, kompetence), formativní, karta souhrnu bez jmen žáků; L8 hlásí „čeká na schválení“',
        audit_response_clean($today) && str_contains($todayBody, 'Exit ticket lekce 5') && str_contains($todayBody, 'Odpovědělo:') && str_contains($todayBody, 'formativní – nevstupuje do hodnocení')
        && preg_match('~aria-labelledby="lx72-sum">(.*?)</section>~s', $todayBody, $sumHtml) === 1 && !str_contains($sumHtml[1], 'Audit ') && str_contains((string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a', 'lesson' => '8'])['body'], 'čeká na schválení'));
    $student = audit_login_student($h, 'class_3a', 'Audit Nikdo');
    $sCsrf = (string)$student['csrf'];
    $before = (string)@file_get_contents(lx72_path('class_3a'));
    $noCsrfS = $h->request('POST', '/', ['action' => 'lx72_answer', 'lesson' => '5', 'choice' => '0'], ['follow_redirects' => false]);
    if ($current >= 5) {
        $lessonPage = (string)$h->request('GET', '/', ['view' => 'lekce', 'n' => '5'])['body'];
        $post = $h->request('POST', '/', ['action' => 'lx72_answer', 'lesson' => '5', 'choice' => (string)$vNone['correct'], 'csrf' => $sCsrf], ['follow_redirects' => false]);
        $check('HTTP žák (L5 odemčená): stránka lekce má exit ticket, odpověď s CSRF se uloží (bez CSRF 419)', (int)$noCsrfS['status'] === 419 && str_contains($lessonPage, 'value="lx72_answer"')
            && in_array((int)$post['status'], [302, 303], true) && is_array(lx72_answer_of('class_3a', $key('none'), 5)));
    } else {
        $post = $h->request('POST', '/', ['action' => 'lx72_answer', 'lesson' => '5', 'choice' => '0', 'csrf' => $sCsrf], ['follow_redirects' => false]);
        $check('HTTP žák (L5 ještě zamčená podle kalendáře, aktuální lekce ' . $current . '): bez CSRF 419, odpověď na budoucí lekci se neuloží (přesměrování na materiály)',
            (int)$noCsrfS['status'] === 419 && in_array((int)$post['status'], [302, 303], true) && str_contains((string)($post['headers']['Location'] ?? ''), 'view=materialy') && (string)@file_get_contents(lx72_path('class_3a')) === $before);
    }
    $cur = $h->request('GET', '/', ['view' => 'lekce', 'n' => (string)$current]);
    $check('HTTP žák: aktuální lekce ' . $current . ' se vykreslí čistě; lekce bez schváleného návrhu nemá blok v72', audit_response_clean($cur)
        && (lc72_student_overlay('class_3a', $current) !== null || !str_contains((string)$cur['body'], 'class="lx72"')));
} finally {
    $h->stop();
}

// ---------------------------------------------------------------- 6) HTTP v režimu účtů
$acc = v59sf_create_accounts();
$h2 = Harness::start(['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V72A_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir]);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) {
            $jarSwap([]);
            audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]);
            $jars[$who] = $jarSwap(null);
        }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $h8 = lc72_overlay_hash(lc72_overlay('class_3a', 8));
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'schvalovani']);
    $aCsrf = (string)$h2->csrfToken((string)$aPage['body']);
    $aForeign = $as('a', 'GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $aPost = $as('a', 'POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '8', 'hash' => $h8, 'csrf' => $aCsrf], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A vidí jen své třídy; cizí třída 3.A → 403 (GET záložky i POST lc72_approve), nic se neschválí',
        audit_response_clean($aPage) && preg_match('/[?&;]class=class_[34]a\b|value="class_[34]a"/', (string)$aPage['body']) !== 1 && (int)$aForeign['status'] === 403 && (int)$aPost['status'] === 403
        && lc72_status('class_3a', 8)['status'] === 'navrh');
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a', 'lesson' => '8']);
    $sCsrf2 = (string)$h2->csrfToken((string)$sPage['body']);
    $sPost = $as('s', 'POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '8', 'hash' => $h8, 'csrf' => $sCsrf2, 'return_tab' => 'schvalovani'], ['follow_redirects' => false]);
    $check('účty: asistent 3.A vidí náhled jen pro čtení (bez formulářů), jeho lc72_approve se zamítne a nic nezapíše', audit_response_clean($sPage) && str_contains((string)$sPage['body'], 'Máte jen čtení')
        && !str_contains((string)$sPage['body'], 'value="lc72_approve"') && in_array((int)$sPost['status'], [302, 303], true) && lc72_status('class_3a', 8)['status'] === 'navrh');
    $bPage = $as('b', 'GET', '/teacher.php', ['tab' => 'schvalovani', 'class' => 'class_3a', 'lesson' => '8']);
    $bPost = $as('b', 'POST', '/teacher.php', ['action' => 'lc72_approve', 'class_id' => 'class_3a', 'lesson' => '8', 'hash' => $h8, 'csrf' => (string)$h2->csrfToken((string)$bPage['body']), 'return_tab' => 'schvalovani'], ['follow_redirects' => false]);
    $check('účty: učitel 3.A schválí L8 (záznam nese jméno učitele z účtu)', in_array((int)$bPost['status'], [302, 303], true) && lc72_status('class_3a', 8)['status'] === 'schvaleno' && lc72_status('class_3a', 8)['by'] === 'Bohdan Sítař');
} finally {
    $h2->stop();
}

exit(audit_summary($state, 'V72_APPROVAL'));
