<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v76 · druhá část tools/v76_learning_audit.php (sdílí $check, $read, $throws, $ROOT, $tmp): cesty, grafické úlohy, důkazy
 * (úloha, pozorování), portfolio export, retence a registr cockpitu. Vše v dočasném úložišti auditu.
 */

// ---------------------------------------------------------------- 3) cesty 1.A ≥ 4, 3.A ≥ 5
$p1 = p63_paths_for_class('class_1a');
$p3 = p63_paths_for_class('class_3a');
$calcBad = [];
foreach (['web_css', 'gfx_formats'] as $pid) foreach ((array)p63_step((array)p63_path($pid), 'predict')['cases'] as $c) {
    $k = (array)($c['calc'] ?? []);
    $opt = (string)$c['options'][$c['correct']];
    $val = match ((string)($k['kind'] ?? '')) {
        'box' => (string)($k['sizing'] === 'border-box' ? $k['width'] : $k['width'] + 2 * $k['padding'] + 2 * $k['border']),
        'flex' => (string)(($k['container'] - ($k['items'] - 1) * $k['gap']) / $k['items']),
        'grid' => (string)(($k['container'] - (count($k['fr']) - 1) * $k['gap']) * $k['fr'][$k['pick']] / array_sum($k['fr'])),
        'raw' => str_replace('.', ',', number_format($k['w'] * $k['h'] * $k['bytes'] / 1000000, 1, '.', '')),
        'print' => implode(' × ', array_map(static fn(int $cm): string => (string)(int)round($cm / 2.54 * $k['dpi']), $k['cm'])),
        default => null,
    };
    if ($val === null && !empty($k)) $calcBad[] = $pid . ':' . $c['id'];
    if ($val !== null && ((string)$val !== (string)$k['result'] || !str_starts_with($opt, (string)$val))) $calcBad[] = $pid . ':' . $c['id'] . '=' . $val;
    if (empty($c['static']) || trim((string)$c['reveal']) === '' || trim((string)$c['explain']) === '') $calcBad[] = $pid . ':' . $c['id'] . ' text';
}
$check('cesty: 1.A má ' . count($p1) . ' (≥ 4, z toho nové web_css a gfx_formats), 3.A ' . count($p3) . ' (≥ 5: net_ports, lnx_services, net_security); kompetence nových cest pokrývají další položky katalogu',
    count($p1) >= 4 && count($p3) >= 5 && isset($p1['web_css'], $p1['gfx_formats'], $p3['net_ports'], $p3['lnx_services'], $p3['net_security'])
    && count(array_unique(array_column($p1, 'competency'))) === count($p1) && count(array_unique(array_column($p3, 'competency'))) === count($p3));
$check('cesty 1.A: výpočty statických předpovědí (box model, flexbox, mřížka, velikost obrázku, pixely pro tisk) přepočítané nezávisle souhlasí se správnou volbou' . ($calcBad ? ' [' . implode(', ', $calcBad) . ']' : ''), $calcBad === []);
$check('cesty 3.A: předpovědi nových cest mají předpočítaný výstup simulátoru (paths_v63_pre_data.php) a správná volba s ním souhlasí', array_reduce(['net_ports', 'lnx_services', 'net_security'], static function (bool $ok, string $pid): bool {
    foreach ((array)p63_step((array)p63_path($pid), 'predict')['cases'] as $c) {
        $out = (string)p63_pre_output($pid, 'predict', (string)$c['id']);
        if ($out === '' || !str_contains($out, (string)$c['expect']) || !str_contains((string)$c['options'][$c['correct']], (string)$c['expect'])) return false;
    }
    return $ok;
}, true));

// ---------------------------------------------------------------- 4) grafické úlohy 1.A / 2.A
$gfx = ['gfx_color', 'web_units', 'css_spec', 'img_dpi'];
$sA = 'stu_0123456789abcdef';
$sB = 'stu_fedcba9876543210';
$det = array_reduce($gfx, static fn(bool $ok, string $t): bool => $ok && tb75_variant($t, $sA, 1) === tb75_variant($t, $sA, 1) && tb75_variant($t, $sA, 1) !== tb75_variant($t, $sB, 1)
    && tb75_variant($t, $sA, 1) !== tb75_variant($t, $sA, 2), true);
$check('úlohy 1.A a 2.A: sada 4 grafických úloh (barvy, jednotky CSS, specificita, DPI) s názvem a časem; deterministické, jiný žák i kolo = jiné zadání; 3.A/4.A beze změny',
    tb75_tasks_for_class('class_1a') === $gfx && tb75_tasks_for_class('class_2a') === $gfx && count(tb75_tasks_for_class('class_3a')) === 7 && $det
    && array_reduce($gfx, static fn(bool $ok, string $t): bool => $ok && isset(TB75_TASKS[$t]['title'], TB75_TASKS[$t]['minutes']), true));
$wrong = [];
for ($i = 0; $i < 200; $i++) {
    $seed = 'stu_' . substr(hash('sha256', 'v76seed' . $i), 0, 16);
    $v = tb75_variant('gfx_color', $seed, 1 + $i % 3);
    $key = array_column($v['fields'], 'answer', 'id');
    $lab = array_column($v['fields'], 'label', 'id');
    preg_match('/rgb\((\d+), (\d+), (\d+)\)/', $lab['hex'], $m1);
    preg_match('/#([0-9a-f]{6})/', $lab['rgb'], $m2);
    preg_match('/#([0-9a-f]{6})/', $lab['inv'], $m3);
    if ($key['hex'] !== '#' . strtolower(sprintf('%02X%02X%02X', $m1[1], $m1[2], $m1[3])) || $key['rgb'] !== implode(',', array_map('hexdec', str_split($m2[1], 2)))
        || $key['inv'] !== '#' . implode('', array_map(static fn(string $h): string => sprintf('%02x', 255 - hexdec($h)), str_split($m3[1], 2)))) $wrong[] = 'color:' . $i;
    $v = tb75_variant('web_units', $seed, 1);
    preg_match('/font-size: (\d+)px a kontejner je široký (\d+) px/u', $v['prompt'], $pu);
    foreach ($v['fields'] as $f) {
        preg_match('/([\d.]+)(em|rem|%)/', $f['label'], $u);
        $exp = match ($u[2]) { 'em' => (float)$u[1] * (int)$pu[1], 'rem' => (float)$u[1] * 16, default => (float)$u[1] * (int)$pu[2] / 100 };
        if (abs((float)$f['answer'] - $exp) > 1e-9) $wrong[] = 'units:' . $i;
    }
    $v = tb75_variant('css_spec', $seed, 1);
    $specs = [];
    foreach ($v['data'] as $line) {
        $sel = trim((string)preg_replace(['/^\d\) /', '/ \{.*$/'], '', $line));
        $a = preg_match_all('/#[a-z]+/', $sel);
        $b = preg_match_all('/\.[a-z]+|\[[^\]]+\]|:[a-z-]+/', $sel);
        $c = count(array_filter(preg_split('/\s+/', $sel), static fn(string $p): bool => preg_match('/^[a-z]+/', $p) === 1));
        $specs[] = [$a, $b, $c];
    }
    $keys = array_map(static fn(array $s): int => $s[0] * 100 + $s[1] * 10 + $s[2], $specs);
    $fk = array_column($v['fields'], 'answer', 'id');
    if ($fk['spec'] !== implode(',', $specs[0]) || (int)$fk['win'] !== (int)array_search(max($keys), $keys, true) + 1 || count(array_keys($keys, max($keys), true)) !== 1) $wrong[] = 'spec:' . $i;
    $v = tb75_variant('img_dpi', $seed, 1);
    preg_match('/(\d+) × (\d+) px/u', $v['prompt'], $pd);
    $fk = array_column($v['fields'], 'answer', 'id');
    $fl = array_column($v['fields'], 'label', 'id');
    preg_match('/při (\d+) DPI/u', $fl['cm'], $dd);
    preg_match('/tisk (\d+) cm/u', $fl['px'], $tc);
    if ($fk['cm'] !== number_format((int)$pd[1] / (int)$dd[1] * 2.54, 1, '.', '') || (int)$fk['px'] !== (int)round((int)$tc[1] / 2.54 * 300)
        || $fk['mp'] !== number_format((int)$pd[1] * (int)$pd[2] / 1e6, 1, '.', '') || (int)$pd[2] * 3 !== (int)$pd[1] * 2) $wrong[] = 'dpi:' . $i;
}
$check('úlohy: klíč přepočítaný nezávisle sedí (200 semínek × 4 úlohy: HEX/RGB/inverze, em/rem/%, specificita a vítěz bez remízy, cm/px/Mpx)' . ($wrong ? ' [' . implode(', ', array_slice($wrong, 0, 5)) . ']' : ''), $wrong === []);
$vc = tb75_variant('gfx_color', $sA, 1);
$hexKey = array_column($vc['fields'], 'answer', 'id');
$alt = ['hex' => strtoupper(ltrim($hexKey['hex'], '#')), 'rgb' => 'rgb(' . str_replace(',', ', ', $hexKey['rgb']) . ')', 'inv' => ' ' . strtoupper($hexKey['inv']) . ' '];
$check('úlohy: tolerance zápisu – HEX bez # a velkými písmeny, „rgb(…)“ s mezerami, desetinná čárka a „px“, „(1, 2, 1)“; krátký HEX #abc = #aabbcc; prázdné pole neprojde',
    tb75_check($vc, $alt)['ok'] && tb76_norm('web_units', 'em', '10,5 px') === tb76_norm('web_units', 'em', '10.5') && tb76_norm('css_spec', 'spec', '(1, 2, 1)') === '1,2,1'
    && tb76_norm('gfx_color', 'hex', '#ABC') === '#aabbcc' && tb76_norm('img_dpi', 'cm', '10,16') === '10.2' && !tb75_check($vc, ['hex' => '', 'rgb' => '', 'inv' => ''])['ok'] && tb76_norm('net_mask', 'prefix', '/24') === null);

// ---------------------------------------------------------------- 5) důkazy: úloha (task) a pozorování učitele (observation)
v63fx_seed($tmp);
$key3 = v63fx_key('class_3a', 'a');
$sid3 = v63fx_sid('class_3a', 'a');
$ref3 = tb75_student_ref('class_3a', $key3);
$var = tb75_current('class_3a', $ref3, 'lnx_chmod')['variant'];
$res = tb75_submit('class_3a', $key3, 'lnx_chmod', array_column($var['fields'], 'answer', 'id'), 1791100000);
$ev = array_values(array_filter(ev62_read($sid3), static fn(array $r): bool => $r['source'] === 'task'));
$check('úloha → důkaz kompetence: vyřešené kolo 3.A zapíše důkaz (zdroj task, lnx_users, skóre 1,0, odkaz tb76:lnx_chmod:r1) bez odpovědí a jmen; nedokončené kolo nic',
    $ref3 === $sid3 && ($res['evidence'] ?? 0) === 1 && count($ev) === 1 && $ev[0]['competency'] === 'lnx_users' && abs((float)$ev[0]['score'] - 1.0) < 1e-9 && $ev[0]['artefact_ref'] === 'tb76:lnx_chmod:r1'
    && tb76_round_score(['last_ok' => false, 'tries' => 1, 'shown' => false]) === null && tb76_round_score(['last_ok' => true, 'tries' => 3]) === 0.5 && tb76_round_score(['shown' => true]) === 0.0
    && tb76_task_competency('grafika_web_ii', 'css_spec') === 'w2_design_system' && in_array('task', EV62_SOURCES, true) && M62_WEIGHTS['task'] === 0.5);
$key2 = 'class_2a:student:' . str_repeat('a', 24);
$check('úloha mimo kompetence: 2.A bez schváleného katalogu v75 důkaz nezapíše (jen výsledek úlohy)', tb76_record_evidence('class_2a', 'stu_' . str_repeat('1', 16), 'gfx_color', ['last_ok' => true, 'tries' => 1, 'round' => 1], 1791100000) === 0
    && tb76_record_evidence('class_3a', 'h' . str_repeat('a', 24), 'lnx_chmod', ['last_ok' => true, 'tries' => 1, 'round' => 1], 1791100000) === 0);
$students = ob76_students('class_3a');
$idx = (int)array_search($sid3, array_column($students, 'id'), true) + 1;
$obs = ob76_record('class_3a', $idx, 'net_dns_dhcp', 'umi', 'audit-teacher', 1791100100);
$obsRow = array_values(array_filter(ev62_read($sid3), static fn(array $r): bool => $r['source'] === 'observation'));
$check('pozorování jedním klikem: zapíše důkaz zdroje observation (skóre 1,0, cílová úroveň z katalogu, odkaz ob76:čas:hash učitele) bez textu a jména; váha 0,8',
    $obs['added'] === 1 && count($obsRow) === 1 && $obsRow[0]['competency'] === 'net_dns_dhcp' && preg_match('/^ob76:\d{14}:[0-9a-f]{8}$/', $obsRow[0]['artefact_ref']) === 1
    && abs((float)$obsRow[0]['score'] - 1.0) < 1e-9 && M62_WEIGHTS['observation'] === 0.8 && !str_contains(json_encode($obsRow), 'Audit Cesta'));
$check('pozorování: neznámá kompetence, výsledek mimo umí/učí se/zatím ne, žák mimo seznam a třída mimo kompetence (2.A) se odmítnou; stejný zápis ve stejné vteřině se nezdvojí',
    $throws(static fn() => ob76_record('class_3a', $idx, 'hack', 'umi', 't'), 'kompetence') && $throws(static fn() => ob76_record('class_3a', $idx, 'net_dns_dhcp', 'skvele', 't'))
    && $throws(static fn() => ob76_record('class_3a', 999, 'net_dns_dhcp', 'umi', 't'), 'seznamu') && $throws(static fn() => ob76_record('class_2a', 1, 'w2_design_system', 'umi', 't'))
    && ob76_record('class_3a', $idx, 'net_dns_dhcp', 'umi', 'audit-teacher', 1791100100)['added'] === 0);

// ---------------------------------------------------------------- 6) portfolio export a retence
$pin = pf75_pin('class_3a', $key3, ['kind' => 'link', 'title' => 'Plakát <b>v76</b> & logo', 'url' => 'https://example.org/plakat', 'reflection' => 'Hierarchie.'], 1791100200);
$export = port65v_export_html([], 'Žák 1', pf75_export_section_html('class_3a', $key3));
$check('portfolio: lokální HTML export v65 obsahuje připnuté práce (název, druh, odkaz jen jako text, reflexe) – bez <a>, skriptů a formulářů, značky odstraněné, znaky escapované',
    ($pin['ok'] ?? false) && str_contains($export, 'Připnuté práce') && str_contains($export, 'Plakát v76 &amp; logo') && !str_contains($export, '<b>v76') && str_contains($export, 'https://example.org/plakat')
    && str_contains($export, 'Hierarchie.') && preg_match('/<a\s|<script|<form/i', $export) !== 1 && pf75_export_section_html('class_3a', '') === ''
    && str_contains($read('portfolio_v65_views.php'), 'pf75_export_section_html($classId, $studentKey)'));
$lv = lv75_student_hash($key3);
storage_update(STORAGE_DIR . '/levels_v75_picks.json.php', static fn(array $d): array => ['v' => 1, 'classes' => ['class_3a' => ['1' => [$lv => ['l' => 'vyzva', 's' => 'standard', 'at' => 1], 'other' => ['l' => 'zaklad']]]]]);
$otherKey = v63fx_key('class_3a', 'b');
pf75_pin('class_3a', $otherKey, ['kind' => 'link', 'title' => 'Jiný žák', 'url' => 'https://example.org/b'], 1791100200);
$setStatus = static function (string $id, string $status, ?string $at): void {
    storage_update(identity58_path(), static function (array $reg) use ($id, $status, $at): array { $reg['students'][$id]['status'] = $status; $reg['students'][$id]['archived_at'] = $at; return $reg; });
};
$now = 1791100000 + 40 * 86400;
$setStatus($sid3, 'left', date(DATE_ATOM, $now - 29 * 86400));
$early = r76_retention_purge(true, $now);
$setStatus($sid3, 'left', date(DATE_ATOM, $now - 31 * 86400));
$pfFile = pf75_path($ref3);
$snap = static fn(): string => hash('sha256', (string)@file_get_contents(tb75_path()) . (string)@file_get_contents(STORAGE_DIR . '/levels_v75_picks.json.php') . (is_file($pfFile) ? 'f' : '-'));
$before = $snap();
$dry = r76_retention_purge(true, $now);
$check('retence v75: 29 dní po odchodu nic; po 31 dnech dry-run spočítá portfolio, výsledky úloh i volbu úrovně, ale nic nezmění', $early['students'] === 0
    && $dry['students'] === 1 && $dry['portfolio'] === 1 && $dry['tasks'] === 1 && $dry['levels'] === 1 && $snap() === $before && is_file($pfFile));
$apply = r76_retention_purge(false, $now);
$picks = storage_read(STORAGE_DIR . '/levels_v75_picks.json.php', false);
$check('retence v75: apply smaže soubor portfolia, záznam úloh a volbu úrovně odešlého žáka; data jiného žáka zůstanou; nástroj v58_retention ji volá',
    $apply['portfolio'] === 1 && !is_file($pfFile) && !isset(tb75_data()['classes']['class_3a'][$ref3]) && !isset($picks['classes']['class_3a']['1'][$lv]) && isset($picks['classes']['class_3a']['1']['other'])
    && pf75_pins('class_3a', $otherKey) !== [] && str_contains($read('tools/v58_retention.php'), 'r76_retention_purge(!$apply)'));

// ---------------------------------------------------------------- 7) registr cockpitu, politiky a rozpočet
$mods = teacher58_modules();
$filesOk = static fn(array $m): bool => array_filter(array_merge((array)$m['files'], (array)($m['css'] ?? [])), static fn(string $f): bool => !is_file(dirname(__DIR__, 2) . '/' . $f)) === [];
$check('registr: moduly plan76 (GET tisk) a svp76 (GET tisk, csv) bez POST akcí, soubory existují; kompetence mají POST ob76_ (pozorování) a CSS v76',
    isset($mods['plan76'], $mods['svp76']) && $filesOk($mods['plan76']) && $filesOk($mods['svp76']) && array_keys((array)$mods['plan76']['get']) === ['tisk'] && array_keys((array)$mods['svp76']['get']) === ['tisk', 'csv']
    && !isset($mods['plan76']['post']) && !isset($mods['svp76']['post']) && array_keys((array)$mods['kompetence']['post']) === ['comp62_', 'ob76_'] && $filesOk($mods['kompetence']));
$map = teacher68_tab_map();
$check('navigace: obě záložky v sekci Výuka a v UI68_TEACHER_DARK_TABS (tmavý režim), „Související“ v cockpit_v70', isset($map['plan76'], $map['svp76']) && in_array('plan76', UI68_TEACHER_DARK_TABS, true)
    && in_array('svp76', UI68_TEACHER_DARK_TABS, true) && str_contains($read('cockpit_v70.php'), "'plan76' => [") && str_contains($read('cockpit_v70.php'), "'svp76' => ["));
$get = teacher59_get_policies();
$check('politiky: ob76_add = třída povinná, neznámé ob76_x zakázané, oprávnění grading.manage (asistent ho nemá); GET plan76|tisk, svp76|tisk, svp76|csv = třída povinná',
    teacher59_action_policy('ob76_add') === ['class' => 'required'] && (teacher59_action_policy('ob76_delete')['deny'] ?? false) === true && teacher_action_permission('ob76_add') === 'grading.manage'
    && !teacher_permission_for_role('assistant', 'grading.manage') && teacher_permission_for_role('teacher', 'grading.manage')
    && ($get['plan76|tisk']['class'] ?? '') === 'required' && ($get['svp76|tisk']['class'] ?? '') === 'required' && ($get['svp76|csv']['class'] ?? '') === 'required');   // 403 cizí třídy v režimu účtů ověřuje tools/v76_http_audit.php
$css = (int)@filesize($ROOT . '/assets/cockpit-v76.css') + (int)@filesize($ROOT . '/assets/print-v76.css');
$cssV76 = $read('assets/cockpit-v76.css') . $read('assets/print-v76.css');
$check('rozpočet: CSS v76 ' . round($css / 1024, 1) . ' kB (limit fáze 20 kB), žádný nový JS; cockpit CSS jen tokeny (pevné barvy jen v @media print)', $css > 0 && $css <= 20480
    && preg_match('/#[0-9a-f]{3,6}\b/i', (string)preg_replace('/@media print\s*\{[^}]*(\{[^}]*\}[^}]*)*\}/', '', $read('assets/cockpit-v76.css'))) !== 1 && !is_file($ROOT . '/assets/plan-v76.js'));
$check('soukromí: plán, ŠVP a pozorování nevypisují jména do CSV/tisku; stránky k tisku a CSV mají no-store; CSV nosniff', str_contains($read('plan_v76_views.php'), "header('Cache-Control: no-store')")
    && str_contains($read('svp_export_v76_views.php'), "header('X-Content-Type-Options: nosniff')") && !str_contains($read('svp_export_v76.php'), 'project_students_for_class') && !str_contains($read('plan_v76.php'), 'project_students_for_class'));
