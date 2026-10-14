<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v80.2 · HTTP a prohlížečová část tools/v80_2_a11y_audit.php (používá $check, $env, $pages, $A, $tmp, V802_KEY).
 */

// ---------------------------------------------------------------- 2) HTTP: každá stránka 200, lang, title, 1 main, 1 h1
$h = Harness::start($env);
$jar = Closure::bind(function (?array $set): array { $old = $this->cookies; if ($set !== null) $this->cookies = $set; return $old; }, $h, Harness::class);
$http = [];
try {
    $jar([]);
    audit_login_teacher($h, V802_KEY, 'Audit Přístupnost');
    $jarT = $jar(null);
    $jar([]);
    audit_login_student($h, 'class_3a', (string)$A['label']);
    $jarS = $jar(null);
    foreach ($pages as $p) {
        $jar($p['who'] === 't' ? $jarT : ($p['who'] === 's' ? $jarS : []));
        $r = $h->request('GET', $p['url']);
        $body = (string)$r['body'];
        $http[$p['id']] = [
            'clean' => audit_response_clean($r) && !str_contains($body, 'teacher_key'),
            'lang' => preg_match('~<html[^>]*\blang="(cs|en|uk)"~', $body) === 1,
            'title' => preg_match('~<title>[^<]{3,}</title>~u', $body) === 1,
            'main' => preg_match_all('~<main\b~', $body),
            'h1' => preg_match_all('~<h1\b~', $body),
        ];
    }
} finally {
    $h->stop();
}
$badHttp = array_filter($http, static fn(array $x): bool => !$x['clean'] || !$x['lang'] || !$x['title']);
$check('HTTP: ' . count($http) . ' stránek → 200 bez chyby PHP a bez přesměrování na přihlášení, <html lang> (cs/en/uk) a <title>' . ($badHttp ? ' [' . implode(', ', array_map(static fn($k, $x): string => $k . json_encode($x), array_keys($badHttp), array_slice($badHttp, 0, 3))) . ']' : ''), count($http) === count($pages) && $badHttp === []);
$badMain = array_filter($http, static fn(array $x): bool => $x['main'] !== 1 || $x['h1'] < 1);
$check('HTTP: každá stránka má v HTML právě jeden <main> a aspoň jeden <h1>' . ($badMain ? ' [' . implode(', ', array_map(static fn($k, $x): string => $k . ' main=' . $x['main'] . ' h1=' . $x['h1'], array_keys($badMain), $badMain)) . ']' : ''), $badMain === []);

// ---------------------------------------------------------------- 3) prohlížeč
$node = getenv('EDUCANET_NODE') ?: 'node';
$nodePath = getenv('NODE_PATH') ?: (string)(getenv('USERPROFILE') ?: getenv('HOME')) . '/.claude/runtime/node/node_modules';
$mode = (string)getenv('EDUCANET_V802_BROWSER');
if ($mode === 'skip') { echo 'SKIP  prohlížečová část (EDUCANET_V802_BROWSER=skip)' . PHP_EOL; return; }
$pagesFile = $tmp . '-pages.json';
// ladění: EDUCANET_V802_ONLY=id,id změří jen vybrané stránky (kontrola počtu měření pak záměrně selže)
$only = array_filter(explode(',', (string)getenv('EDUCANET_V802_ONLY')));
if ($only !== []) $pages = array_values(array_filter($pages, static fn(array $p): bool => in_array($p['id'], $only, true)));
file_put_contents($pagesFile, (string)json_encode(array_map(static fn(array $p): array => ['id' => $p['id'], 'who' => $p['who'], 'url' => $p['url'], 'shots' => $p['shots']], $pages), JSON_UNESCAPED_UNICODE));
register_shutdown_function(static function () use ($pagesFile): void { @unlink($pagesFile); });
$bh = Harness::start($env);
try {
    $shots = (string)getenv('EDUCANET_V802_SHOTS');
    $cmd = [$node, __DIR__ . '/v80_2_a11y_browser.cjs', $bh->baseUrl(), V802_KEY, (string)$A['label'], $pagesFile];
    if ($shots !== '') $cmd[] = $shots;
    $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, array_merge(getenv(), ['NODE_PATH' => $nodePath]), ['bypass_shell' => true]);
    $out = is_resource($proc) ? (string)stream_get_contents($pipes[1]) : '';
    $errOut = is_resource($proc) ? (string)stream_get_contents($pipes[2]) : '';
    if (is_resource($proc)) { fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); }
} finally {
    $bh->stop();
}
$raw = (string)getenv('EDUCANET_V802_RAW');
if ($raw !== '') file_put_contents($raw, $out);
$d = json_decode(trim($out), true);
if (!is_array($d) || isset($d['skip'])) {
    $why = is_array($d) ? (string)$d['skip'] : 'node nespuštěn ' . substr(trim($errOut), 0, 200);
    if ($mode === 'require') $check('prohlížečová část je vyžadovaná, ale nejde spustit: ' . $why, false);
    else echo 'SKIP  prohlížečová část: ' . $why . ' (EDUCANET_V802_BROWSER=require ji vynutí)' . PHP_EOL;
    return;
}
require __DIR__ . '/v80_2_a11y_eval.php';
