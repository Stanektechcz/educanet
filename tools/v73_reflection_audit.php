<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v73 · audit reflexe hodiny (modul `reflexe73`, akce lr73_save) a karty „Minule“ v Dnešní hodině.
 *   1) soubory, registr (POST jen prefix lr73_, bez GET), mapa, tmavý režim, exact politika (třída povinná a v rozsahu), oprávnění
 *      content.manage (asistent ne), deny pro jiné lr73_*, výjimka ze snímků politik v68/v70/v71 zapsaná,
 *   2) validace: stav povinný (1 klepnutí), úsek / úkol z modelu lekce, rychlé volby jen známé, texty ≤ 300 znaků, jméno žáka se odmítne,
 *   3) úložiště: autor jen jako hash, čtení autor + administrátor (výchozí) / učitelé třídy (konfigurace), retence a strop, purge s --dry-run
 *      na kopii (dry-run nic nezmění, --apply smaže staré), pokrytí reflexemi,
 *   4) Dnešní hodina: „Minule: skončili jste u …, poznámka: …“, návrh posunu u neodučené a části hodiny (jen návrh),
 *   5) HTTP: CSRF (419), uložení, limity, neznámá akce; v režimu účtů cizí třída 403, asistent nezapisuje (403, bez formuláře), admin čte.
 *   php tools/v73_reflection_audit.php     Dočasné úložiště i cache modelu. Konec: V73_REFLECTION_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v73-reflect')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
putenv('EDUCANET_REFLECTION_READERS');
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V73R_KEY = 'v73r-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V73R_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'cockpit_v70.php',
    'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php', 'session_v53.php', 'accounts_v53.php', 'intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (array_unique(array_merge((array)$mods['hodina']['files'], (array)$mods['reflexe73']['files'])) as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn, string $needle = ''): bool { try { $fn(); return false; } catch (Throwable $e) { return $needle === '' || str_contains($e->getMessage(), $needle); } };
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && str_starts_with(lr73_path(), $tmp . '/'));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['lesson_reflect_v73.php', 'lesson_reflect_v73_views.php', 'tools/lib/v73_prep_http.php', 'tools/lib/v73_reflect_http.php'];
$cli = ['tools/v73_reflection_audit.php', 'tools/v73_reflection_purge.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if (!str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory reflexe: strict_types, guard, ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$rf = $mods['reflexe73'] ?? [];
$map = teacher68_tab_map();
$check('registr: modul `reflexe73` (POST jen prefix lr73_, bez GET), v sekci Dnes a v UI68_TEACHER_DARK_TABS; Dnešní hodina načítá reflexi a dál nemá post/get',
    array_keys((array)($rf['post'] ?? [])) === ['lr73_'] && !isset($rf['get']) && in_array('reflexe73', teacher58_tabs(), true) && ($map['reflexe73']['section'] ?? '') === 'dnes'
    && in_array('reflexe73', UI68_TEACHER_DARK_TABS, true) && in_array('lesson_reflect_v73.php', (array)$mods['hodina']['files'], true) && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get'])
    && str_contains($read('lesson_today_v71_views.php'), 'lr73_today_note_html($m)'));
$check('politika: lr73_save = třída povinná a v rozsahu; jiné lr73_* zakázané; oprávnění content.manage (učitel ano, asistent ne)',
    teacher59_action_policy('lr73_save') === ['class' => 'required'] && teacher59_action_policy('lr73_delete')['deny'] === true && teacher59_action_policy('lr73_save_all')['deny'] === true
    && teacher_action_permission('lr73_save') === 'content.manage' && !teacher_permission_for_role('assistant', 'content.manage') && teacher_permission_for_role('teacher', 'content.manage'));
$snap = array_filter(['tools/v68_cockpit_audit.php', 'tools/v70_cockpit_audit.php', 'tools/v71_lesson_model_audit.php'], static fn(string $f): bool => !str_contains($read($f), "unset(\$post['lr73_save']);"));
$check('změna limitu auditů: lr73_save vyjmutá ze snímků politik v68/v70/v71 (zapsáno v BUILD_MANIFEST_V73.md; manifest není součástí vydání)', $snap === [] && (!is_file($ROOT . '/BUILD_MANIFEST_V73.md') || str_contains($read('BUILD_MANIFEST_V73.md'), 'lr73_save')));

// ---------------------------------------------------------------- 2) validace
$GLOBALS['educanet_runtime_indexes']['project_students:class_3a'] = ['class_3a:student:x1' => ['label' => 'Bořivoj Testovací'], 'class_3a:student:x2' => ['label' => 'Ema Zkoušková']];
$lesson = lm71_lesson('class_3a', 5);
$segs = count($lesson['timeline']);
$ok = lr73_validate(['state' => 'cast', 'stop_seg' => '1', 'stop_task' => '99', 'picks_worked' => ['tempo', 'evil'], 'picks_adjust' => ['cas'], 'worked' => " Ukázka\x07 dig  +short pomohla. ", 'adjust' => '', 'next' => 'Začít úkolem 3.'], 'class_3a', $lesson);
$check('validace: stav „část“, úsek z plánu (' . $segs . ' úseků), úkol mimo rozsah se zahodí, neznámá rychlá volba se zahodí, text bez řídicích znaků a zdvojených mezer',
    $ok['state'] === 'cast' && $ok['stop_seg'] === 1 && $ok['stop_task'] === -1 && str_starts_with($ok['stop_label'], (int)$lesson['timeline'][1]['from'] . '–') && $ok['picks_worked'] === ['tempo']
    && $ok['picks_adjust'] === ['cas'] && $ok['worked'] === 'Ukázka dig +short pomohla.' && $ok['next'] === 'Začít úkolem 3.');
$check('validace: bez stavu, s textem > 300 znaků nebo se jménem žáka ze seznamu třídy se neuloží (česká hláška); 300 znaků projde',
    $throws(static fn() => lr73_validate(['state' => ''], 'class_3a', $lesson), 'Vyberte') && $throws(static fn() => lr73_validate(['state' => 'cele', 'next' => str_repeat('á', 301)], 'class_3a', $lesson), '300 znaků')
    && $throws(static fn() => lr73_validate(['state' => 'cele', 'adjust' => 'Ema zkoušková rušila'], 'class_3a', $lesson), 'jméno žáka')
    && lr73_validate(['state' => 'cele', 'next' => str_repeat('á', 300)], 'class_3a', $lesson)['next'] === str_repeat('á', 300) && !lr73_roster_hit('class_3a', 'Ema a Bořivoj chyběli'));

// ---------------------------------------------------------------- 3) úložiště, čtení, retence, purge
$hA = substr(hash('sha256', 'lr73|teacherA'), 0, 16);
$hB = substr(hash('sha256', 'lr73|teacherB'), 0, 16);
$now = (int)strtotime('2026-10-12 12:00');
lr73_save('class_3a', 5, '2026-10-05', lr73_validate(['state' => 'cast', 'stop_seg' => '1', 'next' => 'Navázat úkolem 3.'], 'class_3a', $lesson), $hA, $now);
lr73_save('class_3a', 4, '2026-09-28', lr73_validate(['state' => 'cele'], 'class_3a', lm71_lesson('class_3a', 4)), $hB, $now);
lr73_save('class_3a', 5, '2026-10-05', lr73_validate(['state' => 'cast', 'stop_seg' => '2', 'next' => 'Navázat úkolem 3.'], 'class_3a', $lesson), $hA, $now);
$raw = (string)file_get_contents(lr73_path());
$check('uložení: přepis vlastní reflexe téže lekce a dne (2 záznamy), autor jen jako 16hex hash, žádné jméno učitele ani žáka', count(lr73_entries('class_3a')) === 2
    && lr73_entries('class_3a')['5-2026-10-05-' . $hA]['stop_seg'] === 2 && !str_contains($raw, 'Audit') && !str_contains($raw, 'Bořivoj') && str_starts_with($raw, '<?php http_response_code(403); exit; ?>')
    && $throws(static fn() => lr73_save('class_9z', 5, '2026-10-05', $ok, $hA)) && $throws(static fn() => lr73_save('class_3a', 5, '2026-10-05', $ok, 'Jana')));
$visA = lr73_visible('class_3a', $hA, false);
$visAdmin = lr73_visible('class_3a', $hA, true);
putenv('EDUCANET_REFLECTION_READERS=class_teachers');
$visClass = lr73_visible('class_3a', $hA, false);
putenv('EDUCANET_REFLECTION_READERS');
$check('čtení: výchozí autor + administrátor (A vidí 1 vlastní, admin 2); EDUCANET_REFLECTION_READERS=class_teachers → učitelé třídy (2)',
    LR73_READERS_DEFAULT === 'author_admin' && count($visA) === 1 && count($visAdmin) === 2 && count($visClass) === 2 && lr73_readers_mode() === 'author_admin');
[$purged, $removed] = lr73_purge(['classes' => ['class_3a' => ['old' => ['date' => '2024-01-10', 'at' => 'x'], 'new' => ['date' => '2026-10-01', 'at' => 'x']]]], $now);
$many = [];
for ($i = 0; $i < LR73_MAX_PER_CLASS + 5; $i++) $many['k' . $i] = ['date' => date('Y-m-d', $now - $i * 3600), 'at' => (string)$i];
[$capped, $over] = lr73_purge(['classes' => ['class_4a' => $many]], $now);
$check('retence: ' . LR73_RETENTION_DAYS . ' dní (starší se smaže), strop ' . LR73_MAX_PER_CLASS . ' záznamů na třídu (nejnovější zůstávají)',
    $removed === 1 && array_keys($purged['classes']['class_3a']) === ['new'] && $over === 5 && count($capped['classes']['class_4a']) === LR73_MAX_PER_CLASS && isset($capped['classes']['class_4a']['k0']));
storage_update(lr73_path(), static function (array $d): array { $d['classes']['class_2a']['stare'] = ['lesson' => 3, 'date' => '2024-09-02', 'state' => 'cele', 'author' => str_repeat('a', 16), 'at' => '2024-09-02T10:00:00+02:00']; return $d; });
$runTool = static function (string $mode) use ($ROOT, $tmp): array {
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($ROOT . '/tools/v73_reflection_purge.php') . ' ' . $mode;
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $ROOT, array_merge(getenv(), ['EDUCANET_STORAGE_DIR' => $tmp]));
    $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    return [proc_close($proc), trim((string)$out)];
};
$before = hash_file('sha256', lr73_path());
[$dryCode, $dryOut] = $runTool('--dry-run');
$afterDry = hash_file('sha256', lr73_path());
[$noArgCode] = $runTool('');
[$appCode, $appOut] = $runTool('--apply');
clearstatcache();
$check('purge na kopii: --dry-run spočítá 1 starou reflexi a nic nezmění, bez volby chyba, --apply ji smaže (výstup jen počty) [' . $dryOut . ' | ' . $appOut . ']',
    $dryCode === 0 && str_contains($dryOut, 'mode=dry-run removed=1 kept=2') && $before === $afterDry && $noArgCode === 1 && $appCode === 0 && str_contains($appOut, 'mode=apply removed=1 kept=2')
    && !isset(storage_read(lr73_path(), false)['classes']['class_2a']['stare']) && count(lr73_entries('class_3a', storage_read(lr73_path(), false))) === 2);

// ---------------------------------------------------------------- 4) Dnešní hodina: „Minule“ a návrh posunu
$last = lr73_latest_before('class_3a', '2026-10-12', 6, $hA, false);
$noteCast = lr73_today_note_html(['class' => 'class_3a', 'today' => '2026-10-12', 'lesson' => ['number' => 6], 'slot' => ['override' => false]]);
lr73_save('class_3a', 6, '2026-10-12', lr73_validate(['state' => 'neoduceno', 'next' => 'Hodina odpadla – exkurze.'], 'class_3a', lm71_lesson('class_3a', 6)), $hA, $now);
$noteSkip = lr73_today_note_html(['class' => 'class_3a', 'today' => '2026-10-19', 'lesson' => ['number' => 7], 'slot' => ['override' => false]]);
$check('Dnešní hodina: „Minule (lekce 5 …, odučena část): skončili jste u …, poznámka: …“ + návrh dokončit zbytek; u neodučené návrh posunu na lekci 6 (jen návrh, odkaz na náhled)',
    is_array($last) && (int)$last['lesson'] === 5 && str_contains($noteCast, 'Minule (lekce 5') && str_contains($noteCast, 'odučena část') && str_contains($noteCast, 'skončili jste u ' . e((string)$last['stop_label']))
    && str_contains($noteCast, 'poznámka: Navázat úkolem 3.') && str_contains($noteCast, 'dokončete zbytek lekce 5') && str_contains($noteSkip, 'Návrh posunu: lekce 6 se neodučila')
    && str_contains($noteSkip, 'kalendář se nemění') && str_contains($noteSkip, 'lesson=6'));
$noteOther = lr73_today_note_html(['class' => 'class_4a', 'today' => '2026-10-12', 'lesson' => ['number' => 6], 'slot' => ['override' => false]]);
$check('Dnešní hodina bez reflexe: jen odkazy přípravy (Projektor, Tisk, Zástup, Reflexe), žádné „Minule“', !str_contains($noteOther, 'Minule') && substr_count($noteOther, '<a ') === 4);
$cov = lr73_coverage('class_3a', date('Y-m-d'), [['date' => '1999-01-01', 'state' => 'cele']]);
$check('metrika pokrytí: počet odučených hodin z kalendáře, podíl s reflexí (cíl ≥ 70 %)', $cov['reflected'] === 0 && $cov['taught'] >= 0 && $cov['pct'] === 0);

require __DIR__ . '/lib/v73_reflect_http.php';
exit(audit_summary($state, 'V73_REFLECTION'));
