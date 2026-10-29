<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v74 · audit knihovny materiálů s verzemi (modul `materialy74`, akce mat74_*) a migrace 0004.
 *   1) soubory v74 (strict_types, guard, ≤ 800 řádků, PHP 8.1), registr a mapa, tmavý režim, exact politiky (třída povinná), deny jiných
 *      mat74_*, oprávnění content.manage (asistent ne), výjimky ze snímků politik v68/v70/v71 zapsané,
 *   2) odkazy: jen https (http:, javascript:, data:, přihlašovací údaje, mezery, délka odmítnuté),
 *   3) verze: přidání = v1, úprava = nová verze, historie jen append (dřívější verze beze změny), konflikt verze, prázdná změna, ověření,
 *      připojení k lekci, vyřazení, obnova = nová verze, strop a retence historie,
 *   4) Dnešní hodina: povinné → ověřené, nikdy vyřazené ani „neodpovídá tématu“; rozdíl verzí,
 *   5) migrace 0004: dry-run nic nezmění, import jen https, idempotentní, rollback jen neupravených importů,
 *   6) HTTP: CSRF 419, javascript: odmítnuto, záložka (detail, historie, rozdíl, obnova), Dnešní hodina; účty: cizí třída 403, asistent jen čte.
 *   php tools/v74_materials_audit.php     Dočasné úložiště i cache modelu. Konec: V74_MATERIALS_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v74-materials')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V74M_KEY = 'v74m-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V74M_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php',
    'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php', 'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (array_unique(array_merge((array)$mods['hodina']['files'], (array)$mods['materialy74']['files'])) as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/migrate.php';
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn, string $needle = ''): bool { try { $fn(); return false; } catch (Throwable $e) { return $needle === '' || str_contains($e->getMessage(), $needle); } };
$tree = static function (string $dir): string {
    $rows = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->isFile() && !str_ends_with($f->getFilename(), '.lock')) $rows[] = str_replace('\\', '/', substr($f->getPathname(), strlen($dir))) . '|' . hash_file('sha256', $f->getPathname());
    }
    sort($rows);
    return hash('sha256', implode("\n", $rows));
};
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && str_starts_with(mat74_path(), $tmp . '/'));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['src/ops/materials_v74.php', 'src/ops/materials_v74_views.php', 'src/teacher/insights_v74.php', 'src/teacher/insights_v74_views.php', 'src/teacher/bulk_approval_v74.php', 'src/teacher/bulk_approval_v74_views.php',
    'src/teacher/weekly_v74.php', 'src/teacher/weekly_v74_views.php', 'src/ui/palette_v74.php', 'migrations/0004_materials_v74.php', 'tools/lib/v74_cockpit_http.php'];
$cli = ['tools/v74_materials_audit.php', 'tools/v74_cockpit_audit.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if ($s === '' || !str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v74: strict_types, guard (knihovny SCRIPT_FILENAME, CLI PHP_SAPI), ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$m74 = $mods['materialy74'] ?? [];
$map = teacher68_tab_map();
$check('registr: modul `materialy74` (POST jen prefix mat74_, bez GET), v sekci Výuka, v UI68_TEACHER_DARK_TABS; Dnešní hodina načítá knihovnu, dál nemá post/get a CSS beze změny',
    array_keys((array)($m74['post'] ?? [])) === ['mat74_'] && !isset($m74['get']) && in_array('materialy74', teacher58_tabs(), true) && ($map['materialy74']['section'] ?? '') === 'vyuka'
    && in_array('materialy74', UI68_TEACHER_DARK_TABS, true) && in_array('src/ops/materials_v74_views.php', (array)$mods['hodina']['files'], true) && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get'])
    && ($mods['hodina']['css'] ?? []) === ['assets/cockpit-v71.css']);
$policyOk = true;
foreach (MAT74_ACTIONS as $a) $policyOk = $policyOk && teacher59_action_policy($a) === ['class' => 'required'] && teacher_action_permission($a) === 'content.manage';
$check('politiky: 6 akcí mat74_* = třída povinná a v rozsahu, content.manage (učitel ano, asistent ne); mat74_delete / mat74_import zakázané',
    count(MAT74_ACTIONS) === 6 && $policyOk && teacher59_action_policy('mat74_delete')['deny'] === true && teacher59_action_policy('mat74_import')['deny'] === true
    && !teacher_permission_for_role('assistant', 'content.manage') && teacher_permission_for_role('teacher', 'content.manage'));
$snap = array_filter(['tools/v68_cockpit_audit.php', 'tools/v70_cockpit_audit.php', 'tools/v71_lesson_model_audit.php'],
    static fn(string $f): bool => !str_contains($read($f), "\$post['mat74_add'], \$post['mat74_update']") || !str_contains($read($f), "unset(\$get['tyden74|paleta']);"));
$check('změna limitu auditů: mat74_*, ba74_approve a GET tyden74|paleta vyjmuté ze snímků politik v68/v70/v71 (BUILD_MANIFEST_V74.md; manifest není součástí vydání)',
    $snap === [] && (!is_file($ROOT . '/BUILD_MANIFEST_V74.md') || (str_contains($read('BUILD_MANIFEST_V74.md'), 'mat74_') && str_contains($read('BUILD_MANIFEST_V74.md'), 'tyden74|paleta'))));

// ---------------------------------------------------------------- 2) odkazy
$okUrl = mat74_clean_url('https://www.example.org/kurz?id=5#cast');
$rejected = 0;
foreach (['http://example.org/x', 'javascript:alert(1)', 'data:text/html,<b>x</b>', 'https://user:pw@example.org/', 'https://exa mple.org/', 'https://localhost/', '//example.org/x', 'https://example.org/' . str_repeat('a', 1500), ''] as $u) {
    $rejected += $throws(static fn() => mat74_clean_url($u)) ? 1 : 0;
}
$check('odkazy: https projde beze změny; http:, javascript:, data:, přihlašovací údaje, mezera, hostitel bez domény, relativní, > 1500 znaků a prázdný se odmítnou (9/9)', $okUrl === 'https://www.example.org/kurz?id=5#cast' && $rejected === 9);

// ---------------------------------------------------------------- 3) verze a historie
$t0 = (int)strtotime('2026-10-12 09:00');
$in = ['type' => 'video', 'title' => '  DNS v praxi  ', 'url' => 'https://example.org/dns', 'lang' => 'CZ', 'audience' => 'zak', 'topics' => 'dns, dhcp, DNS, <x>', 'competencies' => 'sit-dns', 'note' => "Ukázka\x07 záznamů"];
$a = mat74_add('class_3a', $in, 'Audit Učitel', $t0);
$id = (string)$a['id'];
$check('přidání: verze 1, id m74_ + 12 hex, text bez řídicích znaků, témata normalizovaná (bez duplicit a neplatných), kdo a kdy zapsané',
    $a['version'] === 1 && preg_match('/^m74_[a-f0-9]{12}$/', $id) === 1 && $a['title'] === 'DNS v praxi' && $a['topics'] === ['dns', 'dhcp'] && $a['note'] === 'Ukázka záznamů'
    && $a['by'] === 'Audit Učitel' && $a['change'] === 'vytvoření' && count(mat74_history($id)) === 1 && $throws(static fn() => mat74_add('class_9z', $in, 'x')) && $throws(static fn() => mat74_add('class_3a', ['type' => 'x'] + $in, 'x'), 'typ'));
$h1 = mat74_history($id)[0];
$in2 = array_merge($in, ['title' => 'DNS v praxi II']);
$u2 = mat74_update('class_3a', $id, $in2, 1, 'Audit Učitel', $t0 + 60);
$check('úprava: nová verze 2; zastaralá verze formuláře (1) → konflikt; beze změny → žádná nová verze; cizí třída → odmítnuto',
    $u2['version'] === 2 && $u2['title'] === 'DNS v praxi II' && $throws(static fn() => mat74_update('class_3a', $id, $in2, 1, 'x'), 'mezitím')
    && $throws(static fn() => mat74_update('class_3a', $id, $in2, 2, 'x'), 'Nic se nezměnilo') && $throws(static fn() => mat74_update('class_4a', $id, $in2, 2, 'x'), 'jiné třídě'));
$v3 = mat74_verify('class_3a', $id, 'ok', 'Audit Učitel', $t0 + 120);
$v4 = mat74_attach('class_3a', $id, 5, 'hlavni', true, false, 'Audit Učitel', $t0 + 180);
$v5 = mat74_attach('class_3a', $id, 6, 'doplnek', false, false, 'Audit Učitel', $t0 + 240);
$v6 = mat74_attach('class_3a', $id, 6, '', false, true, 'Audit Učitel', $t0 + 300);
$check('ověření (v3, datum a kdo), připojení k L5 povinně (v4), k L6 (v5), odpojení L6 (v6); znovu odpojit nejde, lekce 29 ani neznámá role nejdou',
    $v3['version'] === 3 && $v3['checked_at'] === '2026-10-12' && $v3['checked_by'] === 'Audit Učitel' && $v4['lessons'] === ['5' => ['role' => 'hlavni', 'required' => true]]
    && count($v5['lessons']) === 2 && $v6['version'] === 6 && array_keys($v6['lessons']) === [5] && $throws(static fn() => mat74_attach('class_3a', $id, 6, '', false, true, 'x'), 'není')
    && $throws(static fn() => mat74_attach('class_3a', $id, 29, 'hlavni', false, false, 'x')) && $throws(static fn() => mat74_attach('class_3a', $id, 5, 'boss', false, false, 'x')));
$v7 = mat74_retire('class_3a', $id, 'Video bylo staženo.', 'Audit Učitel', $t0 + 360);
$check('vyřazení: verze 7 se stavem vyřazený a důvodem; bez důvodu, podruhé ani ověření vyřazeného nejde',
    $v7['state'] === 'vyrazeny' && $v7['retired_reason'] === 'Video bylo staženo.' && $throws(static fn() => mat74_retire('class_3a', $id, '', 'x'), 'proč')
    && $throws(static fn() => mat74_retire('class_3a', $id, 'znovu', 'x'), 'už je') && $throws(static fn() => mat74_verify('class_3a', $id, 'ok', 'x'), 'Vyřazený'));
$v8 = mat74_restore('class_3a', $id, 6, 'Audit Učitel', $t0 + 420);
$hist = mat74_history($id);
$check('obnova verze 6 = nová verze 8 se stejným obsahem jako v6 (aktivní, L5 povinně); historie 1–8 jen přibývá, verze 1 je bajtově stejná jako při vzniku',
    $v8['version'] === 8 && $v8['change'] === 'obnovení verze 6' && mat74_fields($v8) === mat74_fields($v6) && array_column($hist, 'version') === range(1, 8) && $hist[0] === $h1
    && $hist[5]['title'] === $v6['title'] && $hist[6]['state'] === 'vyrazeny');
$check('rozdíl verzí: v1 → v2 jen „Název“ (česky, před → po); v7 → v8 stav vyřazený → aktivní',
    mat74_diff($hist[0], $hist[1]) === [['title', 'Název', 'DNS v praxi', 'DNS v praxi II']] && in_array(['state', 'Stav', 'vyřazený', 'aktivní'], array_map(static fn(array $r): array => $r, mat74_diff($hist[6], $hist[7])), true));
$b = mat74_add('class_3a', ['type' => 'article', 'title' => 'Strop historie', 'url' => 'https://example.org/strop'], 'Audit', $t0);
$snaps = [1 => mat74_history((string)$b['id'])[0]];
for ($i = 2; $i <= 35; $i++) { mat74_update('class_3a', (string)$b['id'], ['type' => 'article', 'title' => 'Strop historie ' . $i, 'url' => 'https://example.org/strop'], $i - 1, 'Audit', $t0 + $i); $hh = mat74_history((string)$b['id']); $snaps[$i] = end($hh); }
$hb = mat74_history((string)$b['id']);
$prefixOk = true;
foreach ($hb as $row) $prefixOk = $prefixOk && $row === $snaps[(int)$row['version']];
$check('strop historie: po 35 verzích zůstává ' . MAT74_HISTORY_MAX . ' nejnovějších (6–35), každá beze změny; obnova odříznuté verze 1 se odmítne',
    count($hb) === MAT74_HISTORY_MAX && (int)$hb[0]['version'] === 6 && (int)end($hb)['version'] === 35 && $prefixOk && $throws(static fn() => mat74_restore('class_3a', (string)$b['id'], 1, 'x'), 'už v historii'));
mat74_update('class_3a', (string)$b['id'], ['type' => 'article', 'title' => 'Po retenci', 'url' => 'https://example.org/strop'], 35, 'Audit', $t0 + (MAT74_HISTORY_DAYS + 70) * 86400);
$hr = mat74_history((string)$b['id']);
$check('retence: verze starší než ' . MAT74_HISTORY_DAYS . ' dní se odřežou, zůstanou nejnovější ' . MAT74_HISTORY_KEEP_MIN, count($hr) === MAT74_HISTORY_KEEP_MIN && end($hr)['title'] === 'Po retenci');

// ---------------------------------------------------------------- 4) Dnešní hodina: pořadí a skrytí
$add = static fn(string $title, array $extra = []): string => (string)mat74_add('class_3a', array_merge(['type' => 'article', 'title' => $title, 'url' => 'https://example.org/' . md5($title), 'topics' => 'dns'], $extra), 'Audit', $t0)['id'];
$vB = $add('B ověřený k tématu');
mat74_verify('class_3a', $vB, 'ok', 'Audit', $t0);
$add('C neověřený k tématu');
$vD = $add('D vyřazený k lekci');
mat74_attach('class_3a', $vD, 5, 'hlavni', true, false, 'Audit', $t0);
mat74_retire('class_3a', $vD, 'zastaralé', 'Audit', $t0);
$vE = $add('E neodpovídá tématu');
mat74_verify('class_3a', $vE, 'mismatch', 'Audit', $t0);
mat74_add('class_4a', ['type' => 'article', 'title' => 'F jiná třída', 'url' => 'https://example.org/f', 'topics' => 'dns'], 'Audit', $t0);
$list = array_column(mat74_for_lesson('class_3a', 5, ['dns'], $t0 + 3600), 'title');
$check('Dnešní hodina: povinný (L5) → ověřený → neověřený; vyřazený, „neodpovídá tématu“ ani materiál jiné třídy se neukáží',
    array_slice($list, 0, 3) === ['DNS v praxi II', 'B ověřený k tématu', 'C neověřený k tématu'] && !in_array('D vyřazený k lekci', $list, true) && !in_array('E neodpovídá tématu', $list, true) && !in_array('F jiná třída', $list, true));
$st = mat74_stats('class_3a', $t0 + 3600);
$check('ukazatele knihovny: aktivní, ověřené za 180 dní (%), neověřené, neodpovídá, vyřazené, povinné', $st['retired'] === 1 && $st['mismatch'] === 1 && $st['verified'] === 2 && $st['required'] === 1 && $st['pct'] === (int)round(2 / $st['active'] * 100));
$raw = (string)file_get_contents(mat74_path());
$check('úložiště: chráněný první řádek, bez dat žáků (jen učitel, třída, odkaz, metadata)', str_starts_with($raw, '<?php http_response_code(403); exit; ?>') && !str_contains($raw, ':student:'));

// ---------------------------------------------------------------- 5) migrace 0004
$migs = migrate_load_all($ROOT . '/migrations');
storage_update(adaptive_store_path('lesson_resources'), static fn(array $d): array => [
    'class_3a|L07' => ['id' => 'class_3a|L07', 'class_id' => 'class_3a', 'lesson_number' => 7, 'type' => 'video', 'title' => 'Video učitele k L7', 'url' => 'https://example.org/video-l7', 'note' => 'Krátké'],
    'class_2a|L04' => ['id' => 'class_2a|L04', 'class_id' => 'class_2a', 'lesson_number' => 4, 'type' => 'video', 'title' => 'Staré http', 'url' => 'http://example.org/old'],
]);
$before = $tree($tmp);
$dry = migrate_run($migs, true, '0004_materials_v74');
$check('migrace 0004: --dry-run nic nezmění (hash stromu) a vypíše počty (videa v42, zdroje learning_resources, přeskočené http)',
    isset($migs['0004_materials_v74']) && $tree($tmp) === $before && $dry['pending'] === ['0004_materials_v74'] && str_contains(implode("\n", $dry['reports']['0004_materials_v74']['details']), 'videa učitelů v42: 1 k importu')
    && str_contains(implode("\n", $dry['reports']['0004_materials_v74']['details']), 'přeskočeno (není https / neplatné): 1'));
$countBefore = count(mat74_items(null));
$applied = migrate_run($migs, false, '0004_materials_v74');
$items = mat74_items(null);
$imported = array_filter($items, static fn(array $i): bool => str_starts_with((string)$i['origin'], 'import_'));
$v42 = array_values(array_filter($imported, static fn(array $i): bool => $i['origin'] === 'import_v42'));
$check('migrace 0004 --apply: video v42 k L7 3.A (role hlavní), zdroje learning_resources po třídách s tématy, jen https; učitelské materiály beze změny',
    $applied['applied'] === ['0004_materials_v74'] && count($v42) === 1 && $v42[0]['lessons'] === ['7' => ['role' => 'hlavni', 'required' => false]] && $v42[0]['class_id'] === 'class_3a'
    && count($imported) > 10 && !array_filter($imported, static fn(array $i): bool => !str_starts_with((string)$i['url'], 'https://')) && count($items) === $countBefore + count($imported)
    && mat74_item($id)['version'] === 8);
$afterApply = $tree($tmp);
$again = migrate_run($migs, false, '0004_materials_v74');
$direct = ($migs['0004_materials_v74']['up'])(false);
$check('migrace 0004 je idempotentní: znovu nic čekajícího, přímé up() nic nepřidá a strom se nezmění', $again['pending'] === [] && (int)$direct['changed'] === 0 && $tree($tmp) === $afterApply);
$firstImported = (string)array_key_first($imported);
mat74_update((string)$imported[$firstImported]['class_id'], $firstImported, ['type' => (string)$imported[$firstImported]['type'], 'title' => 'Upraveno učitelem', 'url' => (string)$imported[$firstImported]['url']], 1, 'Audit', $t0);
$beforeDown = $tree($tmp);
$downDry = ($migs['0004_materials_v74']['down'])(true);
$sameAfterDry = $tree($tmp) === $beforeDown;
$down = ($migs['0004_materials_v74']['down'])(false);
$left = mat74_items(null);
$check('rollback 0004: náhled nic nezmění; --apply odebere jen neupravené importy (' . (count($imported) - 1) . '), upravený import i učitelské materiály zůstanou',
    $sameAfterDry && (int)$downDry['removed'] === count($imported) - 1 && (int)$down['removed'] === count($imported) - 1 && isset($left[$firstImported], $left[$id])
    && count($left) === $countBefore + 1 && !isset(mat74_data()['history'][(string)array_keys($imported)[1]]));

// ---------------------------------------------------------------- 6) HTTP
audit_prewarm_accounts($GLOBALS['modules']);
$env = ['EDUCANET_STORAGE_DIR' => $tmp, 'EDUCANET_TEACHER_EXPORT_KEY' => V74M_KEY, 'EDUCANET_LOCAL_AUTH_REQUIRE_EMAIL_VERIFY' => '0', 'EDUCANET_LM71_CACHE_DIR' => $cacheDir];
$byTitle = static function (string $title): ?array { foreach (mat74_items(null) as $i) if ((string)$i['title'] === $title) return $i; return null; };
$h = Harness::start($env);
try {
    audit_login_teacher($h, V74M_KEY, 'Audit Učitel');
    $page = $h->request('GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a']);
    $body = (string)$page['body'];
    $check('HTTP: záložka Knihovna materiálů – 200, CSS v74, ukazatele (ověřeno za 180 dní), seznam, formulář přidání mat74_add s https a CSRF',
        audit_response_clean($page) && str_contains($body, '<h1>Knihovna materiálů</h1>') && str_contains($body, 'assets/cockpit-v74.css') && str_contains($body, 'Ověřeno za 180 dní')
        && str_contains($body, 'value="mat74_add"') && str_contains($body, 'Jen https://') && str_contains($body, 'Upraveno učitelem'));
    $csrf = (string)$h->csrfToken($body);
    $base = ['action' => 'mat74_add', 'class_id' => 'class_3a', 'return_tab' => 'materialy74', 'type' => 'video', 'lang' => 'CZ', 'audience' => 'zak', 'topics' => 'dns'];
    $noCsrf = $h->request('POST', '/teacher.php', $base + ['title' => 'Bez tokenu', 'url' => 'https://example.org/x'], ['follow_redirects' => false]);
    $js = $h->request('POST', '/teacher.php', $base + ['title' => 'Zlý odkaz', 'url' => 'javascript:alert(1)', 'csrf' => $csrf], ['follow_redirects' => false]);
    $flash = (string)$h->request('GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a'])['body'];
    $ok = $h->request('POST', '/teacher.php', $base + ['title' => 'HTTP materiál', 'url' => 'https://example.org/http-mat', 'csrf' => $csrf], ['follow_redirects' => false]);
    $new = $byTitle('HTTP materiál');
    $check('HTTP: bez CSRF 419; javascript: se neuloží (česká hláška o https); platný odkaz → 303 na detail (?mat=…)', (int)$noCsrf['status'] === 419 && $byTitle('Bez tokenu') === null
        && $byTitle('Zlý odkaz') === null && str_contains($flash, 'jen odkazy https://') && in_array((int)$ok['status'], [302, 303], true) && is_array($new)
        && str_contains((string)($ok['headers']['Location'] ?? ''), 'mat=' . $new['id']));
    $nid = (string)($new['id'] ?? '');
    $h->request('POST', '/teacher.php', ['action' => 'mat74_update', 'class_id' => 'class_3a', 'material_id' => $nid, 'version' => '1', 'type' => 'video', 'title' => 'HTTP materiál – nová verze', 'url' => 'https://example.org/http-mat2', 'csrf' => $csrf, 'return_tab' => 'materialy74'], ['follow_redirects' => false]);
    $h->request('POST', '/teacher.php', ['action' => 'mat74_attach', 'class_id' => 'class_3a', 'material_id' => $nid, 'lesson' => '5', 'role' => 'hlavni', 'required' => '1', 'csrf' => $csrf, 'return_tab' => 'materialy74'], ['follow_redirects' => false]);
    $detail = (string)$h->request('GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a', 'mat' => $nid, 'verze' => '1'])['body'];
    $check('HTTP: detail – historie verzí (3), náhled verze 1, rozdíl proti verzi … a proti aktuální, formulář obnovy mat74_restore, ověření, připojení, úprava s verzí, vyřazení',
        str_contains($detail, 'Historie verzí (3)') && str_contains($detail, 'Náhled verze 1') && str_contains($detail, 'Rozdíl proti aktuální verzi 3') && str_contains($detail, 'value="mat74_restore"')
        && str_contains($detail, 'value="mat74_verify"') && str_contains($detail, 'value="mat74_attach"') && str_contains($detail, 'name="version" value="3"') && str_contains($detail, 'value="mat74_retire"')
        && str_contains($detail, 'rel="noopener noreferrer"') && !str_contains($detail, '<iframe'));
    $h->request('POST', '/teacher.php', ['action' => 'mat74_restore', 'class_id' => 'class_3a', 'material_id' => $nid, 'restore_version' => '1', 'csrf' => $csrf, 'return_tab' => 'materialy74'], ['follow_redirects' => false]);
    $restored = mat74_item($nid);
    $today = (string)$h->request('GET', '/teacher.php', ['tab' => 'hodina', 'class' => 'class_3a', 'lesson' => '5'])['body'];
    $check('HTTP: obnova verze 1 = verze 4 s původním názvem; Dnešní hodina (L5) ukazuje knihovnu (povinný první, odkaz do knihovny), ne vyřazený ani „neodpovídá tématu“',
        is_array($restored) && (int)$restored['version'] === 4 && $restored['title'] === 'HTTP materiál' && str_contains($today, 'Materiály k lekci') && str_contains($today, 'DNS v praxi II')
        && str_contains($today, 'povinný') && str_contains($today, 'Knihovna materiálů lekce 5') && !str_contains($today, 'D vyřazený k lekci') && !str_contains($today, 'E neodpovídá tématu'));
    $other = $h->request('POST', '/teacher.php', ['action' => 'mat74_update', 'class_id' => 'class_4a', 'material_id' => $nid, 'version' => '4', 'type' => 'video', 'title' => 'Přes jinou třídu', 'url' => 'https://example.org/z', 'csrf' => $csrf], ['follow_redirects' => false]);
    $check('HTTP: materiál nejde upravit přes jinou třídu (handler ověří třídu materiálu)', in_array((int)$other['status'], [302, 303], true) && mat74_item($nid)['title'] === 'HTTP materiál');
} finally {
    $h->stop();
}

$acc = v59sf_create_accounts();
$h2 = Harness::start($env);
$jarSwap = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h2, Harness::class);
$jars = [];
try {
    $as = static function (string $who, string $method, string $path, array $fields = [], array $headers = []) use ($h2, $jarSwap, &$jars, $acc): array {
        if (!isset($jars[$who])) { $jarSwap([]); audit_login_teacher_account($h2, $acc['login'][$who], $acc['pw'][$who]); $jars[$who] = $jarSwap(null); }
        $jarSwap($jars[$who]);
        $r = $h2->request($method, $path, $fields, $headers);
        $jars[$who] = $jarSwap(null);
        return $r;
    };
    $aPage = $as('a', 'GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_1a']);
    $aGet = $as('a', 'GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a'], ['follow_redirects' => false]);
    $aPost = $as('a', 'POST', '/teacher.php', ['action' => 'mat74_add', 'class_id' => 'class_3a', 'type' => 'video', 'title' => 'Cizí třída', 'url' => 'https://example.org/c', 'csrf' => (string)$h2->csrfToken((string)$aPage['body'])], ['follow_redirects' => false]);
    $check('účty: učitel 1.A/2.A – knihovna 3.A GET i POST → 403, nic se neuloží; vlastní třída 200 bez odkazu na 3.A/4.A', audit_response_clean($aPage) && (int)$aGet['status'] === 403 && (int)$aPost['status'] === 403
        && $byTitle('Cizí třída') === null && preg_match('/[?&;]class=class_[34]a\b/', (string)$aPage['body']) !== 1);
    $sPage = $as('s', 'GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a']);
    $sPost = $as('s', 'POST', '/teacher.php', ['action' => 'mat74_add', 'class_id' => 'class_3a', 'type' => 'video', 'title' => 'Od asistentky', 'url' => 'https://example.org/s', 'csrf' => (string)$h2->csrfToken((string)$sPage['body'])], ['follow_redirects' => false]);
    $check('účty: asistent 3.A jen čte – bez formulářů („Máte jen čtení“), POST mat74_add zamítnut oprávněním, nic se neuloží [' . (int)$sPost['status'] . ']', audit_response_clean($sPage)
        && str_contains((string)$sPage['body'], 'Máte jen čtení') && !str_contains((string)$sPage['body'], 'value="mat74_add"') && in_array((int)$sPost['status'], [302, 303, 403], true) && $byTitle('Od asistentky') === null);
    $bPage = $as('b', 'GET', '/teacher.php', ['tab' => 'materialy74', 'class' => 'class_3a']);
    $bPost = $as('b', 'POST', '/teacher.php', ['action' => 'mat74_add', 'class_id' => 'class_3a', 'type' => 'video', 'title' => 'Od učitele 3.A', 'url' => 'https://example.org/b', 'csrf' => (string)$h2->csrfToken((string)$bPage['body']), 'return_tab' => 'materialy74'], ['follow_redirects' => false]);
    $bItem = $byTitle('Od učitele 3.A');
    $check('účty: učitel 3.A materiál přidá (verze 1, autor = zobrazované jméno účtu)', in_array((int)$bPost['status'], [302, 303], true) && is_array($bItem) && (int)$bItem['version'] === 1 && (string)$bItem['by'] !== '');
} finally {
    $h2->stop();
}

exit(audit_summary($state, 'V74_MATERIALS'));
