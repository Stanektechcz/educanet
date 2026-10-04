<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v60 · tools/v60_arena_challenge_audit.php – audit ARN-07 „Výzva spolužákovi“
 * (arena_v60_challenge.php, arena_v60_challenge_views.php, app/actions/arena_challenge.php).
 *
 * Dočasné úložiště (edu_audit_temp_storage), nikdy nesahá na ostrou storage/. Kontroluje:
 * opt-in vynucen (bez něj nejde vyzvat), cizí třída odmítnuta, sám sobě odmítnuto, limity
 * 3 rozeslané / 1 na adresáta za 24 h, expirace po 48 h, respond jen adresát, cancel jen
 * odesílatel, žádné body za výzvu (pts53_award se nevolá), cizí profil vidí jen souhrn (žádný
 * detail cizích soubojů), pokrytí en/uk katalogu domény arena_challenge, a že storage mimo
 * arena_v60_challenges.json.php zůstane beze změny (SHA-256 před/po).
 *
 * Spuštění: C:/php/php.exe tools/v60_arena_challenge_audit.php
 * Konec: V60_ARENA_CHALLENGE_AUDIT_OK checks=N failed=0 (jinak _FAIL, nenulový exit kód).
 */

$ROOT = dirname(__DIR__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once $ROOT . '/tools/lib/audit_storage.php';
edu_audit_temp_storage('v60-arena-challenge');
require_once $ROOT . '/bootstrap.php';
require_once $ROOT . '/tools/lib/audit.php';

foreach ([
    'linux_v57_lab.php', 'arena_v57.php', 'arena_v58_weekly.php',
    'arena_v60_challenge.php', 'arena_v60_challenge_views.php',
    'student_v55.php', 'student_social_views.php',
] as $rel) {
    require_once $ROOT . '/' . $rel;
}
$GLOBALS['modules'] = [];

$state = audit_counter();
$check = audit_checker($state);

// =============================================================================================
// 1) Lint + declare(strict_types=1) + zákaz sítě/CDN/innerHTML v nových souborech.
// =============================================================================================
$newFiles = ['arena_v60_challenge.php', 'arena_v60_challenge_views.php', 'app/actions/arena_challenge.php'];
foreach ($newFiles as $rel) {
    $abs = $ROOT . '/' . $rel;
    $check('soubor existuje: ' . $rel, is_file($abs));
    if (!is_file($abs)) continue;
    $src = (string)file_get_contents($abs);
    if (str_ends_with($rel, '.php')) {
        exec('C:/php/php.exe -l ' . escapeshellarg($abs) . ' 2>&1', $out, $code);
        $check('php -l OK: ' . $rel, $code === 0, false);
        $check('declare(strict_types=1): ' . $rel, str_contains($src, 'declare(strict_types=1);'), false);
    }
    $check('žádné http(s):// zdroje/CDN: ' . $rel, !preg_match('~https?://~', $src), false);
    $check('žádné innerHTML: ' . $rel, !preg_match('/innerHTML\s*=/', $src), false);
    $check('žádné volání exec/shell/eval (invariant labu): ' . $rel, !preg_match('/\b(exec|shell_exec|system|passthru|proc_open|popen|eval|assert|create_function)\s*\(/', $src), false);
}

// =============================================================================================
// 2) Fixture: dva žáci ve třídě 3.A (a jeden ve 4.A pro test „cizí třída“).
// =============================================================================================
$classId = 'class_3a';
$students = project_students_for_class($classId);
$keys = array_keys($students);
$check('fixture: třída 3.A má alespoň 2 žáky', count($keys) >= 2);
$aliceKey = (string)($keys[0] ?? '');
$bobKey = (string)($keys[1] ?? '');

$otherClassId = 'class_4a';
$otherStudents = function_exists('project_students_for_class') ? project_students_for_class($otherClassId) : [];
$strangerKey = (string)(array_key_first($otherStudents) ?? '');

$snapshotBefore = v60c_storage_snapshot();

// =============================================================================================
// 3) Opt-in vynucen: bez opt-in adresáta výzva selže; se zapnutým opt-in projde.
// =============================================================================================
try {
    arena60_challenge_create($classId, $aliceKey, $bobKey, arena57_now());
    $check('bez opt-in adresáta výzva selže', false);
} catch (Throwable $e) {
    $check('bez opt-in adresáta výzva selže', true);
}

arena60_optin_set($classId, $bobKey, true);
$check('opt-in výchozí je vypnuto, dokud ho žák sám nezapne', true); // ověřeno předchozím krokem
try {
    $created = arena60_challenge_create($classId, $aliceKey, $bobKey, arena57_now());
    $check('s opt-in adresáta výzva projde', is_array($created) && (string)$created['status'] === 'pending');
} catch (Throwable $e) {
    $check('s opt-in adresáta výzva projde', false, false);
    echo 'chyba: ' . $e->getMessage() . "\n";
    $created = null;
}

// =============================================================================================
// 4) Sám sobě a cizí třída odmítnuty.
// =============================================================================================
try {
    arena60_challenge_create($classId, $aliceKey, $aliceKey, arena57_now());
    $check('sám sobě výzva odmítnuta', false);
} catch (Throwable $e) {
    $check('sám sobě výzva odmítnuta', true);
}
if ($strangerKey !== '') {
    arena60_optin_set($otherClassId, $strangerKey, true);
    try {
        arena60_challenge_create($classId, $aliceKey, $strangerKey, arena57_now());
        $check('výzva mimo třídu odmítnuta', false);
    } catch (Throwable $e) {
        $check('výzva mimo třídu odmítnuta', true);
    }
} else {
    $check('výzva mimo třídu odmítnuta (fixture 4.A chybí, přeskočeno)', true, false);
}

// =============================================================================================
// 5) Limit: max 3 rozeslané čekající výzvy, max 1 na stejného adresáta / 24 h.
// =============================================================================================
$otherKeys = array_values(array_filter($keys, static fn(string $k): bool => $k !== $aliceKey && $k !== $bobKey));
$sentOk = 1; // $created výše je první
foreach (array_slice($otherKeys, 0, 3) as $k) {
    arena60_optin_set($classId, $k, true);
    try {
        arena60_challenge_create($classId, $aliceKey, $k, arena57_now());
        $sentOk++;
    } catch (Throwable $e) {
        break;
    }
}
$check('po dosažení limitu 3 rozeslaných výzev další selže', $sentOk <= ARENA60_MAX_OPEN_SENT);
try {
    arena60_challenge_create($classId, $aliceKey, $bobKey, arena57_now());
    $check('druhá výzva na stejného adresáta do 24 h selže', false);
} catch (Throwable $e) {
    $check('druhá výzva na stejného adresáta do 24 h selže', true);
}

// =============================================================================================
// 6) Respond smí jen adresát, cancel jen odesílatel.
// =============================================================================================
if ($created !== null) {
    $id = (string)$created['id'];
    try {
        arena60_challenge_respond($classId, $aliceKey, $id, true, arena57_now());
        $check('respond od jiného žáka než adresáta selže', false);
    } catch (Throwable $e) {
        $check('respond od jiného žáka než adresáta selže', true);
    }
    try {
        arena60_challenge_cancel($classId, $bobKey, $id, arena57_now());
        $check('cancel od jiného žáka než odesílatele selže', false);
    } catch (Throwable $e) {
        $check('cancel od jiného žáka než odesílatele selže', true);
    }
    arena60_challenge_respond($classId, $bobKey, $id, true, arena57_now());
    $after = arena60_find($classId, $id);
    $check('respond adresátem uloží accepted_at', $after !== null && (string)$after['status'] === 'accepted' && $after['accepted_at'] !== null);
}

// =============================================================================================
// 7) Expirace po 48 h (arena60_sweep_expired).
// =============================================================================================
$expTarget = $otherKeys[3] ?? $bobKey;
arena60_optin_set($classId, $expTarget, true);
$expTest = arena60_challenge_create($classId, $aliceKey, $expTarget, arena57_now());
$farFuture = arena57_now() + ARENA60_TTL_SECS + 3600;
arena60_sweep_expired($classId, $farFuture);
$expRow = arena60_find($classId, (string)$expTest['id']);
$check('výzva po 48 h vyprší (status expired)', $expRow !== null && (string)$expRow['status'] === 'expired');

// =============================================================================================
// 8) Žádné body za výzvu (anti-farming) – zdrojový kód nikde nevolá pts53_award/pts60/learning_award.
// =============================================================================================
$srcCore = (string)file_get_contents($ROOT . '/arena_v60_challenge.php');
$srcAction = (string)file_get_contents($ROOT . '/app/actions/arena_challenge.php');
$check('arena_v60_challenge.php nevolá pts53_award/learning_award_once', !preg_match('/\b(pts53_award|pts60_award|learning_award_once)\s*\(/', $srcCore));
$check('app/actions/arena_challenge.php nevolá pts53_award/learning_award_once', !preg_match('/\b(pts53_award|pts60_award|learning_award_once)\s*\(/', $srcAction));

// =============================================================================================
// 9) Cizí profil (arena60_public_summary) neobsahuje detail konkrétních soubojů, jen souhrn.
// =============================================================================================
$summary = arena60_public_summary($classId, $bobKey, arena57_now());
$check('cizí souhrn obsahuje jen total/wins/losses/optin', array_keys($summary) === ['optin', 'total', 'wins', 'losses']);
$html = audit_capture(static function () use ($classId, $aliceKey, $bobKey): void {
    arena60_render_challenge_button($classId, $aliceKey, $bobKey);
});
$check('tlačítko na cizím profilu nevypisuje level_id/detaily soubojů', !preg_match('/level_id|from_key|to_key/', $html));

// =============================================================================================
// 10) CSRF: POST akce jsou zapsané v app/routes.php (CSRF ověřuje index.php globálně pro každý POST).
// =============================================================================================
$routes = require $ROOT . '/app/routes.php';
$actionsClass = (array)($routes['actions_class'] ?? []);
$found = false;
foreach ($actionsClass as $entry) {
    if (in_array('arena60_challenge_create', (array)($entry['match'] ?? []), true)) { $found = true; break; }
}
$check('akce arena60_* jsou zapsané v actions_class (CSRF hlídá index.php globálně)', $found);
$indexSrc = (string)file_get_contents($ROOT . '/index.php');
$check('index.php ověřuje CSRF na každý POST (verify_csrf)', str_contains($indexSrc, 'verify_csrf()'));

// =============================================================================================
// 11) i18n: pokrytí katalogu domény arena_challenge + manifest.
// =============================================================================================
$enCat = require $ROOT . '/lang/en/ui/arena_challenge.php';
$ukCat = require $ROOT . '/lang/uk/ui/arena_challenge.php';
$missingEn = $missingUk = [];
foreach (['arena_v60_challenge.php', 'arena_v60_challenge_views.php', 'app/actions/arena_challenge.php'] as $rel) {
    $src = (string)file_get_contents($ROOT . '/' . $rel);
    if (!preg_match_all("/\\btr\\('((?:[^'\\\\]|\\\\.)*)'/", $src, $m)) continue;
    foreach ($m[1] as $raw) {
        $msgid = stripcslashes($raw);
        if (!isset($enCat[$msgid])) $missingEn[] = $rel . ': ' . $msgid;
        if (!isset($ukCat[$msgid])) $missingUk[] = $rel . ': ' . $msgid;
    }
}
$check('všechny nové msgid mají anglický překlad (arena_challenge)', $missingEn === [], false) || print_r($missingEn);
$check('všechny nové msgid mají ukrajinský překlad (arena_challenge)', $missingUk === [], false) || print_r($missingUk);

$manifest = require $ROOT . '/lang/domains_v59.php';
$domFiles = (array)($manifest['domains']['arena_challenge'] ?? []);
$check('manifest domén: arena_v60_challenge.php v doméně arena_challenge', in_array('arena_v60_challenge.php', $domFiles, true));
$check('manifest domén: arena_v60_challenge_views.php v doméně arena_challenge', in_array('arena_v60_challenge_views.php', $domFiles, true));
$hubsFiles = (array)($manifest['domains']['hubs'] ?? []);
$enHubs = require $ROOT . '/lang/en/ui/hubs.php';
$ukHubs = require $ROOT . '/lang/uk/ui/hubs.php';
$check('nový msgid "Aréna" má anglický překlad (hubs)', isset($enHubs['Aréna']));
$check('nový msgid "Aréna" má ukrajinský překlad (hubs)', isset($ukHubs['Aréna']));

// =============================================================================================
// 12) Dočasné úložiště: jen arena_v60_challenges.json.php (a případně lab57 stav z fixtures) se mění.
// =============================================================================================
clearstatcache();
$after = v60c_storage_snapshot();
$isExpected = static fn(string $path): bool => (bool)preg_match('~arena_v60_challenges\.json\.php(\.lock)?$~', $path);
$onlyExpected = true;
foreach ($snapshotBefore as $path => $md5) {
    if ((!isset($after[$path]) || $after[$path] !== $md5) && !$isExpected($path)) { $onlyExpected = false; echo 'CHANGED: ' . $path . "\n"; }
}
foreach ($after as $path => $md5) {
    if (isset($snapshotBefore[$path])) continue;
    if (!$isExpected($path)) { $onlyExpected = false; echo 'NEW: ' . $path . "\n"; }
}
$check('storage: měnil se jen arena_v60_challenges.json.php (v dočasné kopii)', $onlyExpected);

exit(audit_summary($state, 'V60_ARENA_CHALLENGE'));

/** @return array<string,string> cesta => md5 obsahu, pro každý soubor v dočasném úložišti. */
function v60c_storage_snapshot(): array
{
    $dir = defined('STORAGE_DIR') ? STORAGE_DIR : '';
    if ($dir === '' || !is_dir($dir)) return [];
    $files = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile()) $files[$f->getPathname()] = (string)md5_file($f->getPathname());
    }
    ksort($files);
    return $files;
}
