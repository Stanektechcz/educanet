<?php
declare(strict_types=1);

/**
 * EDUCANET · tools/v59_cleanup_audit.php (v59 · F4)
 *
 * Kontroluje stav úklidu mrtvého kódu popsaného v PLAN_F4_PROD.md (A.1–A.3), ale NEMAŽE nic sám.
 * Dva režimy:
 *  - výchozí (report-only): PASS pro to, co už dnes platí; INFO pro položky, které na úklid teprve
 *    čekají (fáze 2) – INFO nikdy neshodí exit kód, takže tento audit může bezpečně běžet v běžné
 *    sadě `run_audits.php` i před dokončením úklidu.
 *  - `--strict`: nedokončené položky (INFO) se počítají jako FAIL – pro finální ověření po úklidu.
 *
 * Konec: V59_CLEANUP_AUDIT_OK checks=N failed=0 (jinak V59_CLEANUP_AUDIT_FAILED=…).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$strict = in_array('--strict', $argv, true);
$read = static fn(string $f): string => (string)@file_get_contents($root . '/' . $f);

$ok = 0; $total = 0; $pendingCount = 0;
/** Skutečná kontrola – vždy ovlivňuje exit kód. */
$check = static function (bool $pass, string $label) use (&$ok, &$total): void {
    $total++;
    echo ($pass ? 'PASS' : 'FAIL') . "\t$label\n";
    if ($pass) $ok++;
};
/** Kontrola stavu úklidu fáze 2 – v defaultu INFO (nepočítá se do failed), v --strict FAIL. */
$pendingCheck = static function (bool $alreadyClean, string $label) use (&$ok, &$total, &$pendingCount, $strict): void {
    if ($alreadyClean) { $total++; $ok++; echo "PASS\t$label\n"; return; }
    $pendingCount++;
    if ($strict) { $total++; echo "FAIL\t$label (pending cleanup)\n"; return; }
    echo "INFO\t$label (pending cleanup, viz PLAN_F4_PROD.md)\n";
};

// ---------------------------------------------------------------------------
// A) 25 vyřazených assetů (PLAN_F4_PROD.md A.1) – žádný odkaz mimo tools/legacy/.
// ---------------------------------------------------------------------------
$retiredAssets = [
    'teacher-admin-v45-2.js', 'teacher-admin-v45-3.js', 'teacher-admin-v45-4.js',
    'teacher-admin-v45-5.js', 'teacher-admin-v45-6.js',
    'student-ux-v50-2.css', 'student-ux-v50-2.js',
    'zero-friction-v50-6.css', 'zero-friction-v50-6.js',
    'unified-page-shell-v50-7.css', 'unified-page-shell-v50-7.js',
    'student-design-system-v50-7-2.css',
    'guided-flow-v50-3.css', 'guided-flow-v50-3.js',
    'goal-navigator-v50-4.css',
    'student-design-system-v50-7-1.css',
    'student-ui-v50-7-3.css', 'student-ui-v50-7-3.js',
    'student-ui-v50-7-5.css', 'student-ui-v50-7-5.js',
    'student-ui-v50-7-6.css', 'student-ui-v50-7-6.js',
    'one-task-v50-7-2.css', 'one-task-v50-7-5.css', 'one-task-v50-7-6.css',
];
$check(count($retiredAssets) === 25, 'retired asset list matches PLAN_F4_PROD.md A.1 (25 entries)');

// Hledají se skutečné odkazy v aplikačním kódu: audity/testy (tools/, tests/) zmiňují vyřazené soubory jen jako historii
// nebo negativní kontrolu, V1/ je samostatná aplikace; komentáře (PHP/CSS/JS, např. „source:“ v konsolidovaných assetech)
// se před hledáním odstraní. HTML komentáře v šablonách se počítají – posílají se do prohlížeče.
$excludedDirs = ['/tools/', '/tests/', '/V1/', '/.git/', '/node_modules/'];
function v59c_strip_comments(string $src, string $ext): string
{
    if ($ext === 'php') {
        $out = '';
        foreach (token_get_all($src) as $t) {
            if (is_array($t) && in_array($t[0], [T_COMMENT, T_DOC_COMMENT], true)) continue;
            $out .= is_array($t) ? $t[1] : $t;
        }
        return $out;
    }
    $src = (string)preg_replace('~/\*.*?\*/~s', '', $src);
    return (string)preg_replace('~^\s*//.*$~m', '', $src);
}
$scanFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $ext = strtolower($file->getExtension());
    if (!in_array($ext, ['php', 'js', 'css'], true)) continue;
    $rel = '/' . ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');
    $skip = false;
    foreach ($excludedDirs as $ex) { if (str_contains($rel, $ex)) { $skip = true; break; } }
    if ($skip) continue;
    if (str_starts_with($rel, '/storage/') || str_starts_with($rel, '/uploads/') || str_starts_with($rel, '/cache/')) continue;
    $scanFiles[] = $file->getPathname();
}
$referencingFiles = [];
foreach ($scanFiles as $path) {
    $content = v59c_strip_comments((string)@file_get_contents($path), strtolower(pathinfo($path, PATHINFO_EXTENSION)));
    foreach ($retiredAssets as $asset) {
        if (str_contains($content, $asset)) {
            $rel = '/' . ltrim(str_replace('\\', '/', substr($path, strlen($root))), '/');
            $referencingFiles[$rel][] = $asset;
        }
    }
}
$pendingCheck(count($referencingFiles) === 0, 'no reference to any of the 25 retired assets outside tools/legacy/' . ($referencingFiles ? ' (found in: ' . implode(', ', array_slice(array_keys($referencingFiles), 0, 8)) . (count($referencingFiles) > 8 ? '…' : '') . ')' : ''));

// ---------------------------------------------------------------------------
// B) Marker komentáře bez funkce (PLAN_F4_PROD.md A.1 "Markery").
// ---------------------------------------------------------------------------
$markerNeedles = [
    'app/views/_layout.php' => [
        'v50.7.6 compatibility markers for historical audits only',
        'v50.7.3 compatibility-only legacy asset markers',
        'v50.7.3 compatibility-only legacy runtime markers',
        'v50.2-v50.4 compatibility markers only',
        'v50.7.6 active migration lineage for v50.7.5 audits',
        'Compatibility markers for older audits / deep links',
    ],
    'app/views/dashboard.php' => [
        'Backward release-audit marker only (not rendered)',
        'v50.2/v50.4 compatibility markers only',
    ],
    'app/views/_lesson.php' => [], // doplněno níže dynamickým hledáním
    'unified_page_shell_v50_7.php' => [],
    'zero_friction_v50_6.php' => [],
    'sw.js' => [
        'compatibility marker',
        'compatibility markers retained for historical audits only',
    ],
];
$markerFilesWithHits = [];
foreach ($markerNeedles as $rel => $needles) {
    $content = $read($rel);
    $hit = false;
    foreach ($needles as $needle) { if ($needle !== '' && str_contains($content, $needle)) { $hit = true; break; } }
    // _lesson.php / unified_page_shell_v50_7.php / zero_friction_v50_6.php: obecné hledání "compatibility" markeru
    if (!$hit && in_array($rel, ['app/views/_lesson.php', 'unified_page_shell_v50_7.php', 'zero_friction_v50_6.php'], true)) {
        $hit = (bool)preg_match('~compatibility marker~i', $content);
    }
    if ($hit) $markerFilesWithHits[] = $rel;
}
$pendingCheck(count($markerFilesWithHits) === 0, 'no leftover "compatibility marker only" comments in active sources' . ($markerFilesWithHits ? ' (found in: ' . implode(', ', $markerFilesWithHits) . ')' : ''));

// ---------------------------------------------------------------------------
// C) Every <link>/<script src> and SHELL/OFFLINE_LAB entry resolves to a real file on disk.
//    (Toto je skutečná, ne odložená kontrola – musí platit vždy, nezávisle na fázi 2 úklidu.)
// ---------------------------------------------------------------------------
$assetRefFiles = ['app/views/_layout.php', 'teacher.php'];
foreach (glob($root . '/*_views.php') ?: [] as $f) $assetRefFiles[] = basename($f);
$missingRefs = [];
foreach ($assetRefFiles as $rel) {
    $content = $read($rel);
    if (preg_match_all('~(?:href|src)="(?:<\?=\s*e\(asset_url\(\')?assets/([^"\'\?]+)~', $content, $m)) {
        foreach ($m[1] as $assetPath) {
            if (!is_file($root . '/assets/' . $assetPath)) $missingRefs[] = "$rel -> assets/$assetPath";
        }
    }
}
$sw = $read('sw.js');
$shellEntries = [];
if (preg_match('~const SHELL=(\[[^;]+\]);~s', $sw, $sm)) { $decoded = json_decode(str_replace("'", '"', $sm[1]), true); if (is_array($decoded)) $shellEntries = $decoded; }
if (preg_match('~const OFFLINE_LAB=(\[[^;]+\]);~s', $sw, $om)) { $decoded = json_decode(str_replace("'", '"', $om[1]), true); if (is_array($decoded)) $shellEntries = array_merge($shellEntries, $decoded); }
foreach ($shellEntries as $entry) {
    $path = explode('?', (string)$entry, 2)[0];
    if ($path === 'manifest.webmanifest' || $path === 'lab-offline.html') { if (!is_file($root . '/' . $path)) $missingRefs[] = "sw.js SHELL/OFFLINE_LAB -> $path"; continue; }
    if (!is_file($root . '/' . $path)) $missingRefs[] = "sw.js SHELL/OFFLINE_LAB -> $path";
}
$check(count($shellEntries) > 0, 'sw.js SHELL/OFFLINE_LAB arrays were parsed successfully');
$check(count($missingRefs) === 0, 'every referenced <link>/<script src> and SHELL/OFFLINE_LAB entry exists on disk' . ($missingRefs ? ' (missing: ' . implode(', ', array_slice($missingRefs, 0, 5)) . ')' : ''));

// ---------------------------------------------------------------------------
// D) "Detail" pravidlo (F4-5): aktivní CSS nesmí schovávat obsah za .v507-detail-only.
// ---------------------------------------------------------------------------
$activeCss = $read('assets/student-ui-v50-7-7.css');
$pendingCheck(!preg_match('~\.v507-detail-only\{display:none~', $activeCss), 'active CSS has no ".v507-detail-only{display:none" rule (v51 "no hiding behind Detail" rule)');
$pendingCheck(!preg_match('~\.social-about\{display:none~', $activeCss) && !preg_match('~:not\(\.v507-details-expanded\)\s*\.social-about\{display:none~', $activeCss), 'own profile ".social-about" is not hidden behind Detail');
// V59-A11Y-02: regrese – the "Detail" JS toggle was removed in v59, so ANY ".v507-details-expanded"
// hide/revert rule in the active student CSS now hides real content permanently. Real check (not
// pending cleanup): must always pass, independent of --strict.
$check(!preg_match('~v507-details-expanded~', $activeCss), 'active CSS has no ".v507-details-expanded" hide/revert rule (dead since the Detail toggle was removed – v59 A11Y-02)');
$check(!preg_match('~\.v507-detail-toggle~', $activeCss), 'active CSS has no ".v507-detail-toggle" styling (button removed – v59 A11Y-03)');

// ---------------------------------------------------------------------------
// E) render_student_guided_flow() je mrtvá funkce.
// ---------------------------------------------------------------------------
$layout = $read('app/views/_layout.php');
$layoutNoComments = (string)preg_replace('~/\*.*?\*/~s', '', $layout);
$pendingCheck(!str_contains($layoutNoComments, 'render_student_guided_flow'), 'render_student_guided_flow() is not defined/called in active layout code');

// ---------------------------------------------------------------------------
// F) Žádná síťová volání v aplikačním PHP kromě Google přihlášení (bootstrap.php) a explicitně
//    povoleného externího tutora (adaptive_learning.php, gated přes educanet_secret('tutor_endpoint'),
//    rozhodnutí školy dle PLAN_F4_PROD.md B.1) – ne "mrtvý"/nebezpečný kód, ale zdokumentovaná výjimka.
// ---------------------------------------------------------------------------
$allowedNetworkFiles = ['bootstrap.php', 'adaptive_learning.php'];
$networkPattern = '~curl_init\s*\(|stream_context_create\s*\(\s*\[\s*[\'"]http|file_get_contents\s*\(\s*[\'"]https?://~';
$appScanFiles = [];
foreach (glob($root . '/*.php') ?: [] as $f) $appScanFiles[] = $f;
$appDirIt = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app', FilesystemIterator::SKIP_DOTS));
foreach ($appDirIt as $f) if ($f->isFile() && strtolower($f->getExtension()) === 'php') $appScanFiles[] = $f->getPathname();
$unexpectedNetwork = [];
foreach ($appScanFiles as $path) {
    $rel = basename($path);
    if (in_array($rel, $allowedNetworkFiles, true)) continue;
    $content = (string)@file_get_contents($path);
    if (preg_match($networkPattern, $content)) $unexpectedNetwork[] = str_replace($root . '/', '', $path);
}
$check(count($unexpectedNetwork) === 0, 'no unexpected outbound network calls in application PHP outside the documented allowlist (bootstrap.php Google login, adaptive_learning.php tutor_* endpoint)' . ($unexpectedNetwork ? ' (found in: ' . implode(', ', $unexpectedNetwork) . ')' : ''));

// ---------------------------------------------------------------------------
// G) Broker v50_oci_exec / v50_runtime_config síťové volání – integrátor už vypnul.
// ---------------------------------------------------------------------------
$handsOn = $read('hands_on_learning_v50.php');
$check(!str_contains($handsOn, 'v50_oci_exec'), 'v50_oci_exec() broker call is gone (hands_on_learning_v50.php)');
$handsOnViews = $read('hands_on_learning_views_v50.php');
$check(!preg_match('~v50_runtime_mode\(\)\s*!==\s*[\'"]simulator[\'"]~', $handsOnViews), "hands-on runtime mode check does not branch away from 'simulator'");

echo "V59_CLEANUP_AUDIT $ok/$total (pending=$pendingCount" . ($strict ? ', strict' : ', report-only') . ")\n";
$failed = $total - $ok;
if ($failed) { echo "V59_CLEANUP_AUDIT_FAILED=$failed\n"; exit(1); }
echo "V59_CLEANUP_AUDIT_OK checks=$total failed=0\n";
