<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$root = dirname(__DIR__);
$errors = [];
$needles = [
    'lab-runtime/Containerfile' => ['--'],
    'lab-runtime/seed.sh' => ['set -euo pipefail'],
    'lab-runtime/broker.py' => ['127.0.0.1','Bearer','COMMAND_PATTERNS','--network','none','--cap-drop','ALL','no-new-privileges','--memory','--cpus','--pids-limit','subprocess.run'],
    'lab-runtime/README.md' => ['rootless','network none','bearer'],
];
foreach ($needles as $rel => $markers) {
    $path = $root . '/' . $rel;
    if (!is_file($path)) { $errors[] = 'missing ' . $rel; continue; }
    $raw = (string)file_get_contents($path);
    foreach ($markers as $marker) if (!str_contains(strtolower($raw), strtolower($marker))) $errors[] = $rel . ' missing marker ' . $marker;
}
$broker = (string)@file_get_contents($root . '/lab-runtime/broker.py');
if (str_contains($broker, 'shell=True')) $errors[] = 'broker.py must not contain shell=True';
if (preg_match('/--volume|--mount|\s-v\s/', $broker)) $errors[] = 'broker.py must not mount host/student paths';

$url = trim((string)(getenv('EDUCANET_LAB_BROKER_URL') ?: ''));
$token = trim((string)(getenv('EDUCANET_LAB_BROKER_TOKEN') ?: ''));
$live = false;
if ($url !== '' || $token !== '') {
    if ($url === '' || $token === '') $errors[] = 'broker URL and token must be configured together';
    if (strlen($token) < 32) $errors[] = 'broker token must be at least 32 characters';
    $parts = parse_url($url);
    $host = strtolower((string)($parts['host'] ?? ''));
    if (!in_array($host, ['127.0.0.1','::1','localhost'], true)) $errors[] = 'broker URL must be loopback-only';
    if (!$errors) {
        $ctx = stream_context_create(['http'=>[
            'method'=>'GET','timeout'=>2,'ignore_errors'=>true,
            'header'=>"Authorization: Bearer {$token}\r\nAccept: application/json\r\n",
        ]]);
        $raw = @file_get_contents(rtrim($url,'/') . '/health', false, $ctx);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($data) || empty($data['ok'])) $errors[] = 'configured broker health check failed';
        else {
            $live = true;
            if (($data['network'] ?? null) !== 'none') $errors[] = 'broker health reports external networking';
            if (empty($data['rootless'])) $errors[] = 'broker health reports non-rootless runtime';
            if (!in_array((string)($data['bind'] ?? ''), ['127.0.0.1','::1','localhost'], true)) $errors[] = 'broker health reports non-loopback bind';
        }
    }
}

if ($errors) {
    fwrite(STDERR, "V50_LAB_RUNTIME_CHECK_FAIL\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}
echo 'V50_LAB_RUNTIME_CHECK_OK mode=' . ($live ? 'oci-live' : 'simulator-safe-fallback') . PHP_EOL;
