<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(400);
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store');
    }
    echo "EDUCANET installation doctor se z bezpečnostních důvodů spouští pouze přes SSH/CLI.\n\n";
    echo "V adresáři aplikace spusť:\n";
    echo "php tools/check_install.php\n";
    exit;
}

$root = dirname(__DIR__);
$checks = [];
$add = static function(string $label, bool $ok, string $detail = '') use (&$checks): void {
    $checks[] = [$label, $ok, $detail];
};

$add('PHP >= 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION);
foreach (['json','session','fileinfo','mbstring'] as $ext) {
    $add('PHP extension '.$ext, extension_loaded($ext));
}
$storage = $root . '/storage';
$uploads = $root . '/uploads';
$add('storage existuje', is_dir($storage), $storage);
$add('storage zapisovatelný', is_dir($storage) && is_writable($storage));
$add('uploads existuje', is_dir($uploads), $uploads);
$add('uploads zapisovatelný', is_dir($uploads) && is_writable($uploads));
$runtimeCache = $root . '/cache/runtime';
$runtimeOk = is_dir($runtimeCache);
foreach (['class_1a','class_2a','class_3a','class_4a'] as $cid) $runtimeOk = $runtimeOk && is_file($runtimeCache . '/' . $cid . '.php');
$add('Runtime cache připravená', $runtimeOk, $runtimeCache);

$candidates = [];
$env = getenv('EDUCANET_SECRETS_FILE');
if (is_string($env) && trim($env) !== '') $candidates[] = trim($env);

// ISPConfig: detect /var/www/clients/clientX/webY/web[/sub/...]
$appRoot = realpath($root) ?: $root;
$cursor = $appRoot;
for ($i = 0; $i < 10; $i++) {
    if (basename($cursor) === 'web' && preg_match('/^web\\d+$/', basename(dirname($cursor)))) {
        $candidates[] = dirname($cursor) . '/private/educanet.secrets.php';
        break;
    }
    $parent = dirname($cursor);
    if ($parent === $cursor) break;
    $cursor = $parent;
}

// aaPanel fallback.
$candidates[] = '/www/server/educanet/educanet.secrets.php';
// Portable legacy fallback.
$candidates[] = dirname($root) . '/educanet.secrets.php';

$secretPath = '';
foreach (array_unique($candidates) as $candidate) {
    if (is_file($candidate)) { $secretPath = $candidate; break; }
}
$teacherEnv = getenv('EDUCANET_TEACHER_EXPORT_KEY');
$teacherConfigured = (is_string($teacherEnv) && trim($teacherEnv) !== '') || $secretPath !== '';
$add('Teacher secret nakonfigurovaný', $teacherConfigured, $secretPath !== '' ? $secretPath : ((is_string($teacherEnv)&&trim($teacherEnv)!=='') ? 'environment variable' : 'nenalezen'));

$failed = 0;
foreach ($checks as [$label,$ok,$detail]) {
    echo ($ok ? '[OK]   ' : '[FAIL] ') . $label;
    if ($detail !== '') echo ' · ' . $detail;
    echo PHP_EOL;
    if (!$ok) $failed++;
}

echo PHP_EOL . ($failed === 0 ? 'Instalace vypadá připraveně pro lokální/vývojové použití.' : "Nalezeno problémů: $failed") . PHP_EOL;
echo 'Před nasazením do produkce spusť navíc: php tools/preflight.php (podrobnější kontrola: secrets mimo docroot,'
    . ' vypnuté vývojové proměnné, migrace, zálohy, učitelské účty, .htaccess…).' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
