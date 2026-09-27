<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab offline – audit vrstvy OFFLINE (OPS-01).
 *
 * Ověřuje: assets/lab-manual-v58.json (platnost, shoda s aktuálním lab58_manual()),
 * statickou bezpečnost JS (žádné eval/new Function/innerHTML/document.write/XHR/vzdálený
 * fetch/importScripts z cizího originu), lab-offline.html (bez PHP, bez inline skriptů,
 * lang="cs", viewport, popisky, živý region, odkaz zpět do labu, žádné heslo/CDN),
 * chování JS jádra přes Node.js (pokud je k dispozici – jinak "neověřeno za běhu")
 * a velikosti assetů. Pracuje jen se soubory v projektu, storage/ nečte ani nemění.
 *
 * Spuštění:  C:/php/php.exe tools/v58_offline_audit.php
 * Konec:     V58_OFFLINE_AUDIT_OK checks=N failed=0   (nenulový exit kód při chybě)
 */

$ROOT = dirname(__DIR__);

$GLOBALS['v58oa'] = ['checks' => 0, 'failed' => 0];
function v58oa_check(string $name, bool $ok, string $detail = ''): void
{
    $GLOBALS['v58oa']['checks']++;
    if (!$ok) $GLOBALS['v58oa']['failed']++;
    echo ($ok ? 'PASS ' : 'FAIL ') . $name . ($ok || $detail === '' ? '' : ' – ' . $detail) . "\n";
}
function v58oa_info(string $text): void { echo 'INFO ' . $text . "\n"; }

// ===========================================================================
// 1) Manuál: platnost JSON a shoda s aktuálním lab58_manual()
// ===========================================================================
v58oa_section_manual($ROOT);

function v58oa_section_manual(string $root): void
{
    $path = $root . '/assets/lab-manual-v58.json';
    v58oa_check('manual:file-exists', is_file($path), $path);
    if (!is_file($path)) return;

    $raw = (string)file_get_contents($path);
    $decoded = json_decode($raw, true);
    $valid = json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    v58oa_check('manual:valid-json', $valid, (string)json_last_error_msg());
    if (!$valid) return;

    foreach (['categories', 'concepts', 'commands'] as $key) {
        v58oa_check('manual:has-' . $key, isset($decoded[$key]) && is_array($decoded[$key]));
    }
    v58oa_check('manual:commands-nonempty', count((array)($decoded['commands'] ?? [])) > 0);

    // Manuál je čistě statická data z lab58_manual() (registrace v kódu) – žádná jména žáků
    // ani tříd v něm být nemohou, pokud generátor vůbec nesahá na živé úložiště. Ověř to na
    // zdroji generátoru (chování, ne hádání podle slov jako „student“, které v příkladech
    // příkazů běžně a legitimně označuje jméno cvičného uživatele v labu).
    $genSrc = (string)file_get_contents($root . '/tools/v58_export_manual.php');
    $storageCalls = [];
    foreach (['storage_read', 'storage_update', 'storage_append', 'lab57_store_read', 'load_php_json', 'append_php_json'] as $fn) {
        if (preg_match('/\b' . preg_quote($fn, '/') . '\s*\(/', $genSrc)) $storageCalls[] = $fn;
    }
    v58oa_check('manual:generator-never-reads-storage', $storageCalls === [], implode(', ', $storageCalls));

    $namesSorted = array_keys((array)($decoded['commands'] ?? []));
    $sortedCopy = $namesSorted;
    sort($sortedCopy, SORT_STRING);
    v58oa_check('manual:commands-deterministic-order', $namesSorted === $sortedCopy, 'klíče commands musí být seřazené abecedně (export to zajišťuje)');

    // Shoda s aktuálním registrem – běží ve vlastním izolovaném úložišti, storage/ se nedotkne.
    $GLOBALS['lab57_storage_override'] = sys_get_temp_dir() . '/v58_offline_audit_' . bin2hex(random_bytes(6));
    require_once $root . '/linux_v57_lab.php';
    $fresh = function_exists('lab58_manual') ? lab58_manual() : v57_manual_base();
    $freshSorted = v58oa_normalize_manual(is_array($fresh) ? $fresh : []);
    $fileSorted = v58oa_normalize_manual($decoded);
    $same = json_encode($freshSorted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        === json_encode($fileSorted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $freshCount = count($freshSorted['commands']);
    $fileCount = count($fileSorted['commands']);
    v58oa_check(
        'manual:matches-registry',
        $same,
        $same ? '' : "soubor má $fileCount příkazů, aktuální registr $freshCount – spusť znovu tools/v58_export_manual.php"
    );
    $errors = function_exists('lab58_registry_errors') ? lab58_registry_errors() : [];
    if ($errors !== []) v58oa_info('registrace rozšíření hlásí chyby (neblokuje tento audit, ale zkresluje manuál): ' . implode(' | ', $errors));
}

/** Stejná normalizace jako tools/v58_export_manual.php – nezávislá kopie kvůli oddělení producenta a testu. */
function v58oa_normalize_manual(array $manual): array
{
    $clean = static function (array $map): array {
        ksort($map, SORT_STRING);
        return $map;
    };
    $commands = $clean((array)($manual['commands'] ?? []));
    foreach ($commands as $name => $entry) {
        if (!is_array($entry)) continue;
        unset($entry['extend']);
        ksort($entry, SORT_STRING);
        $commands[$name] = $entry;
    }
    return [
        'categories' => $clean((array)($manual['categories'] ?? [])),
        'concepts' => $clean((array)($manual['concepts'] ?? [])),
        'commands' => $commands,
    ];
}

// ===========================================================================
// 2) Statická bezpečnost JS (token-sken bez komentářů/řetězců, jako u v57 invariantu)
// ===========================================================================
v58oa_section_js_safety($ROOT);

/** Nahradí obsah komentářů a řetězcových literálů mezerami (zachová délku/řádky pro čitelné hlášky). */
function v58oa_strip_js_noise(string $src): string
{
    $out = '';
    $n = strlen($src);
    $i = 0;
    $mode = 'code'; // code | line-comment | block-comment | squote | dquote | template
    while ($i < $n) {
        $c = $src[$i];
        $two = substr($src, $i, 2);
        if ($mode === 'code') {
            if ($two === '//') { $mode = 'line-comment'; $out .= '  '; $i += 2; continue; }
            if ($two === '/*') { $mode = 'block-comment'; $out .= '  '; $i += 2; continue; }
            if ($c === "'") { $mode = 'squote'; $out .= ' '; $i++; continue; }
            if ($c === '"') { $mode = 'dquote'; $out .= ' '; $i++; continue; }
            if ($c === '`') { $mode = 'template'; $out .= ' '; $i++; continue; }
            $out .= $c;
            $i++;
            continue;
        }
        if ($mode === 'line-comment') {
            if ($c === "\n") { $mode = 'code'; $out .= "\n"; $i++; continue; }
            $out .= ' ';
            $i++;
            continue;
        }
        if ($mode === 'block-comment') {
            if ($two === '*/') { $mode = 'code'; $out .= '  '; $i += 2; continue; }
            $out .= $c === "\n" ? "\n" : ' ';
            $i++;
            continue;
        }
        // squote / dquote / template: respektuj zpětné lomítko, jinak jen maskuj obsah
        $closer = $mode === 'squote' ? "'" : ($mode === 'dquote' ? '"' : '`');
        if ($c === '\\' && $i + 1 < $n) { $out .= '  '; $i += 2; continue; }
        if ($c === $closer) { $mode = 'code'; $out .= ' '; $i++; continue; }
        $out .= $c === "\n" ? "\n" : ' ';
        $i++;
    }
    return $out;
}

function v58oa_section_js_safety(string $root): void
{
    $files = ['assets/linux-core-v58.js', 'assets/linux-core-cmds-v58.js', 'assets/lab-offline-v58.js'];
    $forbidden = ['eval(', 'new Function(', '.innerHTML', 'insertAdjacentHTML(', 'document.write(', 'XMLHttpRequest', 'importScripts(', 'setTimeout(\'', 'setInterval(\''];
    foreach ($files as $rel) {
        $path = $root . '/' . $rel;
        v58oa_check('js:exists:' . $rel, is_file($path));
        if (!is_file($path)) continue;
        $src = (string)file_get_contents($path);
        $code = v58oa_strip_js_noise($src);
        $hits = [];
        foreach ($forbidden as $needle) {
            if (str_contains($code, $needle)) $hits[] = $needle;
        }
        v58oa_check('js:no-forbidden-api:' . $rel, $hits === [], implode(', ', $hits));

        // fetch(...) smí cílit jen na relativní cestu v projektu (žádný vzdálený origin).
        $remoteFetch = [];
        if (preg_match_all('/fetch\s*\(\s*[\'"]([^\'"]*)[\'"]/', $src, $m)) {
            foreach ($m[1] as $url) {
                if (preg_match('~^(https?:)?//~i', $url) || preg_match('~^[a-z][a-z0-9+.-]*://~i', $url)) $remoteFetch[] = $url;
            }
        }
        v58oa_check('js:fetch-same-origin:' . $rel, $remoteFetch === [], implode(', ', $remoteFetch));
    }
}

// ===========================================================================
// 3) lab-offline.html: bez PHP, bez inline skriptů, popisky, živý region, odkaz zpět
// ===========================================================================
v58oa_section_html($ROOT);

function v58oa_section_html(string $root): void
{
    $path = $root . '/lab-offline.html';
    v58oa_check('html:exists', is_file($path));
    if (!is_file($path)) return;
    $html = (string)file_get_contents($path);

    v58oa_check('html:no-php', !str_contains($html, '<?php'));
    v58oa_check('html:no-password-input', !preg_match('/<input[^>]+type=["\']password["\']/i', $html), 'offline stránka nesmí mít přihlašovací pole');
    v58oa_check('html:mentions-offline-disclaimer', str_contains($html, 'Offline trénink'), 'chybí jasné označení „Offline trénink…“');
    v58oa_check('html:mentions-no-progress-save', (bool)preg_match('/nezapoč|neuklád|ověřuje jen online/iu', $html), 'chybí věta, že se postup do školy neukládá');
    v58oa_check('html:links-back-to-lab', (bool)preg_match('/href=["\'][^"\']*view=lab[^"\']*["\']/i', $html), 'chybí odkaz zpět do index.php?view=lab');
    v58oa_check('html:no-external-cdn', !preg_match('/(?:src|href)=["\']((?:https?:)?\/\/)/i', $html), 'žádné externí CDN/fonty');

    libxml_use_internal_errors(true);
    $doc = new DOMDocument();
    $loaded = $doc->loadHTML('<?xml encoding="utf-8">' . $html);
    libxml_clear_errors();
    v58oa_check('html:parses', $loaded);
    if (!$loaded) return;

    $htmlEl = $doc->getElementsByTagName('html')->item(0);
    v58oa_check('html:lang-cs', $htmlEl instanceof DOMElement && strtolower($htmlEl->getAttribute('lang')) === 'cs');

    $hasViewport = false;
    foreach ($doc->getElementsByTagName('meta') as $meta) {
        if ($meta instanceof DOMElement && strtolower($meta->getAttribute('name')) === 'viewport') { $hasViewport = true; break; }
    }
    v58oa_check('html:viewport-meta', $hasViewport);

    $inlineScripts = [];
    foreach ($doc->getElementsByTagName('script') as $s) {
        if ($s instanceof DOMElement && !$s->hasAttribute('src') && trim($s->textContent) !== '') $inlineScripts[] = trim(substr($s->textContent, 0, 40));
    }
    v58oa_check('html:no-inline-scripts', $inlineScripts === [], implode(' | ', $inlineScripts));

    $xpath = new DOMXPath($doc);
    $unlabelled = [];
    foreach ($xpath->query('//input | //textarea') as $field) {
        if (!$field instanceof DOMElement) continue;
        if ($field->hasAttribute('aria-label') || $field->hasAttribute('aria-labelledby')) continue;
        $id = $field->getAttribute('id');
        if ($id !== '' && $xpath->query('//label[@for="' . $id . '"]')->length > 0) continue;
        $ancestorLabel = $xpath->query('ancestor::label', $field);
        if ($ancestorLabel->length > 0) continue;
        $unlabelled[] = $field->nodeName . '[' . ($id ?: '?') . ']';
    }
    v58oa_check('html:inputs-have-labels', $unlabelled === [], implode(', ', $unlabelled));

    $liveRegions = $xpath->query('//*[@aria-live]');
    v58oa_check('html:has-live-region', $liveRegions->length > 0, 'chybí aria-live pro dynamický výstup terminálu');

    $logRegion = $xpath->query('//*[@role="log"]');
    v58oa_check('html:has-log-role', $logRegion->length > 0, 'výstup terminálu by měl mít role="log"');
}

// ===========================================================================
// 4) Behaviorální test JS jádra přes Node.js (pokud je k dispozici)
// ===========================================================================
v58oa_section_node($ROOT);

function v58oa_section_node(string $root): void
{
    $nodeBin = 'node';
    $ver = v58oa_run_process([$nodeBin, '--version'], $root);
    if ($ver === null || $ver['exit'] !== 0) {
        v58oa_info('node není k dispozici – JS jádro pískoviště nebylo ověřeno za běhu (jen syntakticky: node --check by ověřil, kdyby byl node přítomen).');
        return;
    }
    v58oa_info('node ' . trim($ver['stdout']) . ' nalezen, spouštím chování JS jádra (~35 kontrol: příkazy, roury, přesměrování, chyby, perzistence).');

    $script = v58oa_node_test_script($root);
    $tmp = sys_get_temp_dir() . '/v58_offline_node_' . bin2hex(random_bytes(6)) . '.js';
    file_put_contents($tmp, $script);
    $result = v58oa_run_process([$nodeBin, $tmp], $root);
    @unlink($tmp);

    if ($result === null) { v58oa_check('node:run', false, 'proc_open selhalo'); return; }
    $lines = preg_split('/\r?\n/', trim($result['stdout'])) ?: [];
    $summary = null;
    foreach ($lines as $line) {
        if (preg_match('/^(PASS|FAIL)\s+(\S+)(?:\s+(.*))?$/', $line, $m)) {
            v58oa_check('node:' . $m[2], $m[1] === 'PASS', $m[3] ?? '');
            continue;
        }
        if (preg_match('/^NODE_CORE_OK checks=(\d+) failed=(\d+)$/', $line, $m)) $summary = [(int)$m[1], (int)$m[2]];
    }
    v58oa_check('node:summary-present', $summary !== null, $result['stdout'] . $result['stderr']);
    if ($summary !== null) v58oa_check('node:all-passed', $summary[1] === 0, $summary[1] . ' selhání v node testu');
    if ($result['exit'] !== 0 && $summary === null) v58oa_info('node stderr: ' . trim($result['stderr']));
}

/** @return array{exit:int,stdout:string,stderr:string}|null */
function v58oa_run_process(array $cmd, string $cwd): ?array
{
    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = @proc_open($cmd, $descriptors, $pipes, $cwd, null, ['bypass_shell' => true]);
    if (!is_resource($process)) return null;
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exit = proc_close($process);
    return ['exit' => $exit, 'stdout' => (string)$stdout, 'stderr' => (string)$stderr];
}

function v58oa_node_test_script(string $root): string
{
    $core = v58oa_js_path($root . '/assets/linux-core-v58.js');
    $cmds = v58oa_js_path($root . '/assets/linux-core-cmds-v58.js');
    return <<<JS
'use strict';
global.window = global;
require({$core});
require({$cmds});
var Core = global.LinuxCoreV58;
var checks = 0, failed = 0;
function check(name, cond, extra) {
  checks++;
  if (!cond) { failed++; console.log('FAIL ' + name + ' ' + (extra || '')); }
  else console.log('PASS ' + name);
}
function run(s, line) { return Core.runLine(s, line); }
function out(r) { return r.chunks.filter(function (c) { return c[0] === 1; }).map(function (c) { return c[1]; }).join(''); }
function err(r) { return r.chunks.filter(function (c) { return c[0] === 2; }).map(function (c) { return c[1]; }).join(''); }

var s = Core.createSession();
check('session:home-cwd', s.cwd === '/home/student');
check('cmd:pwd', out(run(s, 'pwd')) === '/home/student\\n');
check('cmd:echo', out(run(s, 'echo hello')) === 'hello\\n');
check('cmd:echo-n', out(run(s, 'echo -n hi')) === 'hi');
check('cmd:ls-sample-file', out(run(s, 'ls')).indexOf('vitej.txt') !== -1);
var r = run(s, 'mkdir foo && cd foo && pwd');
check('andor:mkdir-cd-pwd', out(r) === '/home/student/foo\\n', JSON.stringify(r));
run(s, 'cd ..');
r = run(s, 'cat missing.txt');
check('error:cat-missing-exit', r.exit === 1);
check('error:cat-missing-message', err(r).indexOf('No such file or directory') !== -1, err(r));
check('pipe:wc-c', out(run(s, 'echo hello | wc -c')).trim() === '6');
run(s, 'echo abc > out.txt');
check('redirect:overwrite', out(run(s, 'cat out.txt')) === 'abc\\n');
run(s, 'echo x >> out.txt');
check('redirect:append', out(run(s, 'cat out.txt')) === 'abc\\nx\\n');
check('redirect:input', out(run(s, 'wc -l < out.txt')).trim().indexOf('2') === 0);
run(s, 'ls nope 2> err.txt');
check('redirect:stderr', out(run(s, 'cat err.txt')).indexOf('No such file or directory') !== -1);
run(s, 'ls nope2 > all.log 2>&1');
check('redirect:2-and-1', out(run(s, 'cat all.log')).indexOf('No such file or directory') !== -1);
r = run(s, 'false && echo no');
check('andor:and-short-circuit', out(r) === '' && r.exit === 1);
check('andor:or-short-circuit', out(run(s, 'false || echo yes')) === 'yes\\n');
check('sequence:semicolon', out(run(s, 'echo a; echo b')) === 'a\\nb\\n');
check('quoting:mixed', out(run(s, 'echo "a b" \\'c d\\'')) === 'a b c d\\n');
check('variables:export', out(run(s, 'export X=hello && echo \$X')) === 'hello\\n');
check('variables:tilde', out(run(s, 'echo ~')) === '/home/student\\n');
run(s, 'touch a1.txt a2.txt');
check('glob:question-mark', out(run(s, 'ls a?.txt')) === 'a1.txt\\na2.txt\\n');
check('glob:star', out(run(s, 'ls *.txt')).indexOf('a1.txt') !== -1);
check('pipe:grep', out(run(s, 'printf "foo\\\\nbar\\\\n" | grep bar')) === 'bar\\n');
check('pipe:sort-uniq-c', out(run(s, 'printf "b\\\\na\\\\na\\\\n" | sort | uniq -c')).replace(/\\s+/g, ' ').trim() === '2 a 1 b');
check('cmd:cut', out(run(s, 'echo "a:b:c" | cut -d: -f2')) === 'b\\n');
check('pipe:seq-head', out(run(s, 'seq 1 5 | head -n 2')) === '1\\n2\\n');
check('pipe:seq-tail', out(run(s, 'seq 1 5 | tail -n 2')) === '4\\n5\\n');
check('pipe:wc-w', out(run(s, 'printf "a b c\\\\n" | wc -w')).trim() === '3');
check('pipe:3-stage', out(run(s, 'printf "c\\\\nb\\\\na\\\\n" | sort | tr a-z A-Z')) === 'A\\nB\\nC\\n');
check('cmd:find-name', out(run(s, 'find . -name "*.txt"')).indexOf('a1.txt') !== -1);
run(s, 'touch locked.txt && chmod 000 locked.txt');
r = run(s, 'cat locked.txt');
check('permissions:denied', err(r).indexOf('Permission denied') !== -1 && r.exit === 1);
run(s, 'chmod 644 locked.txt');
check('permissions:restored', run(s, 'cat locked.txt').exit === 0);
run(s, 'rm -r foo');
check('cmd:rm-recursive', run(s, 'ls foo').exit !== 0);
check('cmd:basename', out(run(s, 'basename /a/b/c.txt')) === 'c.txt\\n');
check('cmd:dirname', out(run(s, 'dirname /a/b/c.txt')) === '/a/b\\n');
check('cmd:whoami', out(run(s, 'whoami')).trim() === 'student');
r = run(s, 'nope123');
check('error:command-not-found', err(r).indexOf('command not found') !== -1 && r.exit === 127);
check('cmd:history', out(run(s, 'history')).split('\\n').filter(Boolean).length > 5);
check('syntax:unterminated-quote', run(s, 'echo \\'unterminated').exit === 2);

var restored = Core.restore(JSON.parse(JSON.stringify(Core.serialize(s))));
check('persistence:restore', !!restored);
check('persistence:file-survives', out(run(restored, 'cat out.txt')) === 'abc\\nx\\n');

Core.reset(s);
check('reset:sample-file-back', !!s.fs['/home/student/vitej.txt']);
check('reset:user-file-gone', !s.fs['/home/student/out.txt']);

console.log('NODE_CORE_OK checks=' + checks + ' failed=' + failed);
process.exit(failed ? 1 : 0);
JS;
}

function v58oa_js_path(string $absPath): string
{
    return json_encode(str_replace('\\', '/', $absPath), JSON_UNESCAPED_SLASHES);
}

// ===========================================================================
// 5) Velikost assetů (ochrana proti nafouknutí/poškození)
// ===========================================================================
v58oa_section_sizes($ROOT);

function v58oa_section_sizes(string $root): void
{
    $limits = [
        'assets/linux-core-v58.js' => 200000,
        'assets/linux-core-cmds-v58.js' => 260000,
        'assets/lab-offline-v58.js' => 200000,
        'assets/lab-offline-v58.css' => 120000,
        'assets/lab-manual-v58.json' => 800000,
        'lab-offline.html' => 60000,
    ];
    foreach ($limits as $rel => $max) {
        $path = $root . '/' . $rel;
        $ok = is_file($path) && filesize($path) > 0 && filesize($path) <= $max;
        v58oa_check('size:' . $rel, $ok, is_file($path) ? (filesize($path) . ' B, limit ' . $max) : 'chybí soubor');
    }
}

// ===========================================================================
$checks = $GLOBALS['v58oa']['checks'];
$failed = $GLOBALS['v58oa']['failed'];
echo "V58_OFFLINE_AUDIT_OK checks=$checks failed=$failed\n";
exit($failed > 0 ? 1 : 0);
