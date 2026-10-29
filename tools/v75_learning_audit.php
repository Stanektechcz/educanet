<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v75 · audit vrstvy (logika, bez HTTP): fáze E (kompetence a cesty 2.A/4.A), banka úloh s variantami, úrovně v hodině,
 * portfolio, opakování před písemkou, výkon knihovny materiálů (historie zvlášť, migrace 0005) a registr/politiky.
 * Běží v dočasném úložišti (edu_audit_temp_storage) – ostrou storage/ nečte ani nezapisuje. HTTP část: tools/v75_http_audit.php.
 * Konec: V75_LEARNING_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v75-learning')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php', 'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php', 'src/lessons/session_v53.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (['kompetence75', 'ulohy75', 'urovne75', 'portfolia75', 'opakovani75', 'hodina'] as $m) foreach ((array)$mods[$m]['files'] as $f) require_once $ROOT . '/' . $f;
require_once $ROOT . '/src/paths/paths_v63_flow.php';
require_once $ROOT . '/src/assessment/competency_v75_labels.php';
require_once __DIR__ . '/migrate.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn, string $needle = ''): bool { try { $fn(); return false; } catch (Throwable $e) { return $needle === '' || str_contains($e->getMessage(), $needle); } };
$modules = is_array($GLOBALS['modules'] ?? null) ? $GLOBALS['modules'] : [];
$check('úložiště auditu je dočasné (ne ostrá storage/)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && str_starts_with(comp75_status_path(), $tmp . '/') && str_starts_with(tb75_path(), $tmp . '/'));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['src/assessment/competencies_v75.php', 'src/assessment/competencies_v75_views.php', 'src/assessment/competency_v75_labels.php', 'src/paths/paths_v75_bank.php', 'src/paths/paths_v75_content_ops.php', 'src/paths/paths_v75_content_gfx2.php',
    'src/assessment/taskbank_v75_gen.php', 'src/assessment/taskbank_v75.php', 'src/assessment/taskbank_v75_views.php', 'src/assessment/taskbank_v75_teacher_views.php', 'src/levels/levels_v75.php', 'src/levels/levels_v75_views.php', 'src/levels/levels_v75_teacher_views.php',
    'src/levels/levels_v75_content_1a.php', 'src/levels/levels_v75_content_2a.php', 'src/levels/levels_v75_content_3a.php', 'src/levels/levels_v75_content_4a.php', 'src/student/portfolio_v75.php', 'src/student/portfolio_v75_views.php',
    'src/student/portfolio_v75_teacher_views.php', 'src/student/review_v75.php', 'src/student/review_v75_views.php', 'src/student/review_v75_teacher_views.php', 'app/actions/v75.php', 'app/views/ulohy_v75.php', 'app/views/opakovani_v75.php',
    'migrations/0005_materials_history_v75.php', 'tools/lib/v75_http.php', 'tools/lib/v75_learning_part2.php'];
$bad = [];
foreach ($libs as $f) {
    $src = $read($f);
    if ($src === '' || !str_contains($src, 'declare(strict_types=1);') || !str_contains($src, "=== basename(__FILE__)) { http_response_code(403); exit; }") || substr_count($src, "\n") > 800) $bad[] = $f;
}
foreach (['tools/v75_learning_audit.php', 'tools/v75_http_audit.php'] as $f) if (!str_contains($read($f), "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }") || substr_count($read($f), "\n") > 800) $bad[] = $f;
$check('soubory v75: strict_types, guard knihovny / CLI, každý < 800 řádků' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === []);
$scan = '';
foreach ($libs as $f) foreach (token_get_all($read($f)) as $tok) if (!is_array($tok) || !in_array($tok[0], [T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_INLINE_HTML], true)) $scan .= is_array($tok) ? $tok[1] : $tok;   // kód bez komentářů a textů
$check('invariant: vrstva v75 nic nespouští ani se nepřipojuje (žádné exec/shell/system/proc_open/popen/eval/curl/socket/fsockopen/mail ani zpětné apostrofy)',
    preg_match('/\b(exec|shell_exec|system|passthru|proc_open|popen|pcntl_exec|eval|assert|create_function|curl_[a-z_]+|fsockopen|stream_socket_client|socket_[a-z_]+|gethostbyname|dns_get_record|mail)\s*\(/', $scan) !== 1 && !str_contains($scan, '`'));
$m = teacher58_modules();
$check('registr: 5 nových záložek (kompetence75, ulohy75, urovne75, portfolia75, opakovani75) s CSS v75; POST prefixy kp75_/lv75_/pf75_/op75_, GET tisk jen portfolio a opakování',
    isset($m['kompetence75'], $m['ulohy75'], $m['urovne75'], $m['portfolia75'], $m['opakovani75']) && in_array('assets/cockpit-v75.css', (array)$m['ulohy75']['css'], true)
    && array_keys((array)$m['kompetence75']['post']) === ['kp75_'] && array_keys((array)$m['urovne75']['post']) === ['lv75_'] && array_keys((array)$m['portfolia75']['post']) === ['pf75_']
    && array_keys((array)$m['opakovani75']['post']) === ['op75_'] && !isset($m['ulohy75']['post']) && array_keys((array)$m['portfolia75']['get']) === ['tisk'] && array_keys((array)$m['opakovani75']['get']) === ['tisk']
    && !isset($m['hodina']['post']) && !isset($m['hodina']['get']));
$pol = teacher59_action_policies();
$exact = ['kp75_approve', 'kp75_return', 'lv75_approve', 'lv75_return', 'pf75_comment', 'op75_publish', 'op75_close'];
$check('politiky: 7 exact POST politik v75 = třída povinná; neznámé kp75_x / lv75_x / pf75_x / op75_x zakázané; GET portfolia75|tisk a opakovani75|tisk = třída povinná',
    array_reduce($exact, static fn(bool $ok, string $a): bool => $ok && ($pol[$a]['class'] ?? '') === 'required', true) && !isset($pol['kp75_x'], $pol['lv75_x'], $pol['pf75_x'], $pol['op75_x'])
    && (teacher59_get_policies()['portfolia75|tisk']['class'] ?? '') === 'required' && (teacher59_get_policies()['opakovani75|tisk']['class'] ?? '') === 'required');
$check('oprávnění: kp75_/lv75_/op75_ = content.manage, pf75_ = projects.manage (asistent nezapisuje)', teacher_action_permission('kp75_approve') === 'content.manage' && teacher_action_permission('lv75_return') === 'content.manage'
    && teacher_action_permission('op75_publish') === 'content.manage' && teacher_action_permission('pf75_comment') === 'projects.manage');
$map = teacher68_tab_map();
$check('navigace: všech 5 záložek v mapě cockpitu i v UI68_TEACHER_DARK_TABS', array_reduce(['kompetence75', 'ulohy75', 'urovne75', 'portfolia75', 'opakovani75'], static fn(bool $ok, string $t): bool => $ok && isset($map[$t]) && in_array($t, UI68_TEACHER_DARK_TABS, true), true));
$snap = true;
foreach (['tools/v68_cockpit_audit.php', 'tools/v70_cockpit_audit.php', 'tools/v71_lesson_model_audit.php'] as $f) {
    $s = $read($f);
    foreach (array_merge($exact, ['portfolia75|tisk', 'opakovani75|tisk']) as $a) if (!str_contains($s, "'" . $a . "'")) $snap = false;
}
$check('snímky politik v68/v70/v71: nové akce a GET politiky v75 vyjmuté (změna limitu zapsaná v BUILD_MANIFEST_V75.md)', $snap && str_contains($read('BUILD_MANIFEST_V75.md'), 'Změny limitů'));

// ---------------------------------------------------------------- 2) fáze E: katalogy, ŠVP, pokrytí, schválení
$draft = comp75_draft_catalog();
$labelsOk = true;
$enC = require $ROOT . '/lang/en/ui/competency.php';
$ukC = require $ROOT . '/lang/uk/ui/competency.php';
foreach (['grafika_web_ii', 'provoz_site'] as $subject) foreach (comp75_flat($subject) as $id => $c) {
    if (!str_starts_with($c['label'], 'Umím ') || preg_match(EV62_COMP_RE, $id) !== 1 || $c['level'] < 1 || $c['level'] > 4 || $c['tags'] === [] || (comp75_label_msgids()[$id] ?? '') !== $c['label']
        || ($enC[$c['label']] ?? '') === '' || ($ukC[$c['label']] ?? '') === '' || array_filter($c['tags'], static fn(string $t): bool => preg_match('/^(topic|bank|tg|cmd|pack):[a-z0-9\-]+$/', $t) !== 1) !== []) $labelsOk = false;
}
$c2 = comp75_flat('grafika_web_ii');
$c4 = comp75_flat('provoz_site');
$check('katalog 2.A (grafika_web_ii, ' . count($c2) . ') a 4.A (provoz_site, ' . count($c4) . '): 8–12 kompetencí „Umím …“, platná id, úroveň 1–4, tagy, žákovský msgid = katalog a překlad en i uk',
    count($c2) >= 8 && count($c2) <= 12 && count($c4) >= 8 && count($c4) <= 12 && $labelsOk && array_intersect_key($c2, $c4) === [] && array_intersect_key($c2 + $c4, comp62_competencies('os_site') + comp62_competencies('grafika_web')) === []);
$check('katalog 4.A pokrývá zadání fáze E: směrování, proxy a TLS, pozorovatelnost a SLO, změna a rollback, záloha, IaC, výkon, incident', array_diff(['ops_routing', 'ops_proxy_tls', 'ops_observability', 'ops_change', 'ops_backup', 'ops_iac', 'ops_performance', 'ops_incident'], array_keys($c4)) === []);
$outcomes = svp72_outcomes();
$svpOk = true;
foreach ($c2 + $c4 as $id => $_) { $ids = comp75_svp_map()[$id] ?? []; if ($ids === []) $svpOk = false; foreach ($ids as $o) if (!isset($outcomes[$o])) $svpOk = false; }
$check('vazba na ŠVP v72: každá kompetence 2.A/4.A má aspoň jeden výsledek a všechny id existují ve svp72_outcomes()', $svpOk && array_diff(array_keys(comp75_svp_map()), array_keys($c2 + $c4)) === []);
foreach (['class_2a', 'class_4a'] as $c) {
    $cov = comp75_coverage($c, comp75_lesson_topics($c, (array)($modules[$c] ?? [])));
    $pct = $cov['total'] > 0 ? $cov['mapped'] / $cov['total'] * 100 : 0;
    $check('pokrytí ' . $c . ': témata lekcí L1–L28 s kompetencí ' . $cov['mapped'] . '/' . $cov['total'] . ' (nenamapovaná ≤ 5 %), každá lekce má aspoň jednu kompetenci',
        $cov['total'] >= 20 && $pct >= 95 && count(array_filter($cov['lessons'], static fn(array $ids): bool => $ids === [])) === 0);
}
$check('návrh: bez schválení 2.A/4.A mimo pilot (žádný předmět, žádné cesty, žádné důkazy) a stav „návrh“', !comp62_enabled_for_class('class_2a') && comp62_subject_for_class('class_4a') === null
    && p63_paths_for_class('class_2a') === [] && !p63_writes_evidence('class_4a') && comp75_status('class_2a')['status'] === 'navrh' && comp75_draft_paths('class_4a') !== []);
$check('schválení: vrácení bez poznámky výjimka, neznámá akce a cizí třída výjimka, nic se nezapíše', $throws(static fn() => comp75_decide('kp75_return', 'class_2a', '', 'T'), 'poznámkou')
    && $throws(static fn() => comp75_decide('kp75_x', 'class_2a', '', 'T')) && $throws(static fn() => comp75_decide('kp75_approve', 'class_3a', '', 'T')) && !is_file(comp75_status_path()));
comp75_decide('kp75_approve', 'class_2a', '', 'Audit Učitel');
comp75_decide('kp75_approve', 'class_4a', '', 'Audit Učitel');
$paths2 = p63_paths_for_class('class_2a');
$paths4 = p63_paths_for_class('class_4a');
$check('po schválení: 2.A i 4.A v pilotu (předmět, ≥ 3 cesty, důkazy se zapisují), 1.A a 3.A beze změny, otisk uložen', comp62_enabled_for_class('class_2a') && comp62_subject_for_class('class_2a') === 'grafika_web_ii'
    && comp62_subject_for_class('class_4a') === 'provoz_site' && count($paths2) >= 3 && count($paths4) >= 3 && p63_writes_evidence('class_4a') && comp75_status('class_4a')['status'] === 'schvaleno'
    && !comp75_status('class_4a')['changed'] && array_slice(array_keys(p63_paths_for_class('class_3a')), 0, 2) === ['lnx_chmod', 'net_dns'] /* v76: 3.A má 5 cest */ && comp62_subject_for_class('class_1a') === 'grafika_web');
$pp = [];
foreach ($paths2 + $paths4 as $pid => $p) {
    $comps = comp62_competencies((string)comp62_subject_for_class((string)$p['class']));
    $ids = p63_step_ids($p);
    if ($ids !== ['explain', 'predict', 'order', 'recall', 'verify', 'reflect'] || !isset($comps[$p['competency']])) $pp[] = $pid . ':kroky';
    foreach ((array)$p['steps'] as $s) if (!isset($comps[$s['competency']]) || !in_array($s['type'], P63_STEP_TYPES, true)) $pp[] = $pid . ':' . $s['id'];
    $variants = array_values((array)p63_step($p, 'verify')['variants']);
    $flat = array_merge(...$variants);
    if (count($variants) < 3 || count($flat) !== count(array_unique($flat)) || min(array_map('count', $variants)) < 4) $pp[] = $pid . ':varianty';
    foreach (array_merge($flat, (array)p63_step($p, 'recall')['pool'], (array)$p['spaced']['pool']) as $q) {
        $qq = p63_question((string)$q);
        if ($qq === null || !in_array($p['competency'], P63_BANK_COMPETENCY[$qq['category']] ?? [], true)) $pp[] = $pid . ':' . $q;
    }
    if (count((array)p63_step($p, 'recall')['pool']) < 8 || count((array)$p['spaced']['pool']) < 8) $pp[] = $pid . ':pool';
    $order = (array)p63_step($p, 'order');
    if (count((array)$order['lines']) < 4 || count(array_unique((array)$order['lines'])) !== count((array)$order['lines'])) $pp[] = $pid . ':parsons';
    foreach ((array)p63_step($p, 'predict')['cases'] as $case) if (count((array)$case['options']) < 2 || !isset($case['options'][$case['correct']]) || trim((string)$case['explain']) === '' || empty($case['static'])) $pp[] = $pid . ':' . $case['id'];
}
$check('cesty 2.A/4.A: plná struktura (vysvětlení → předpověď → Parsons → vybavování → ověření → reflexe + opakování), ≥ 3 disjunktní varianty po ≥ 4 otázkách z banky správné kategorie, pooly ≥ 8' . ($pp ? ' [' . implode(', ', array_slice($pp, 0, 5)) . ']' : ''), $pp === []);
$calcBad = [];
foreach ($paths2 + $paths4 as $pid => $p) foreach ((array)p63_step($p, 'predict')['cases'] as $case) {
    $answer = null;
    $calc = (array)($case['calc'] ?? []);
    $answer = match ((string)($calc['kind'] ?? '')) {
        'hosts' => (string)(2 ** (32 - (int)$calc['prefix']) - 2),
        'network' => tb75_subnet((string)$calc['ip'], (int)$calc['prefix'])['network'],
        'broadcast' => tb75_subnet((string)$calc['ip'], (int)$calc['prefix'])['broadcast'],
        'contains' => (tb75_ip2long((string)$calc['ip']) & tb75_mask((int)$calc['prefix'])) === (tb75_ip2long((string)$calc['net']) & tb75_mask((int)$calc['prefix'])) ? 'Ano' : 'Ne',
        'minutes_between' => (int)((strtotime('2026-01-01 ' . $calc['to']) - strtotime('2026-01-01 ' . $calc['from'])) / 60) === (int)$calc['answer'] ? 'ok' : 'bad',
        'transfer' => (int)round((float)$calc['size'] / (float)$calc['rate']) === (int)$calc['answer'] ? 'ok' : 'bad',
        'sum' => array_sum((array)$calc['parts']) === (int)$calc['answer'] ? 'ok' : 'bad',
        'autofit' => (int)floor(((int)$calc['width'] + (int)$calc['gap']) / ((int)$calc['min'] + (int)$calc['gap'])) === (int)$calc['answer'] ? 'ok' : 'bad',
        'boxwidth' => ((string)$calc['sizing'] === 'content-box' ? (int)$calc['width'] + 2 * (int)$calc['padding'] + 2 * (int)$calc['border'] : (int)$calc['width']) === (int)$calc['answer'] ? 'ok' : 'bad',
        'scale' => (int)round((float)$calc['base'] * ((float)$calc['ratio'] ** (int)$calc['step'])) === (int)$calc['answer'] ? 'ok' : 'bad',
        'lineheight' => (int)round((float)$calc['rem'] * (float)$calc['root'] * (float)$calc['lh']) === (int)$calc['answer'] ? 'ok' : 'bad',
        'clamp' => (int)round(max((float)$calc['min'], min((float)$calc['max'], (float)$calc['vw'] / 100 * (float)$calc['viewport']))) === (int)$calc['answer'] ? 'ok' : 'bad',
        default => null,
    };
    if (isset($case['fg'])) {
        $ratio = audit_contrast_ratio((string)$case['fg'], (string)$case['bg']);
        if ($ratio === null || abs(round($ratio, 2) - (float)str_replace(',', '.', (string)$case['ratio'])) > 0.011 || ($ratio >= 4.5) !== (bool)$case['passes'] || (int)$case['correct'] !== ($case['passes'] ? 0 : 1)) $calcBad[] = $pid . ':' . $case['id'];
    }
    if ($answer === null) continue;
    if (in_array($answer, ['ok', 'bad'], true) ? $answer === 'bad' : (string)$case['options'][(int)$case['correct']] !== $answer) $calcBad[] = $pid . ':' . $case['id'];
}
$check('obsah: výpočty v předpovědích (adresy, RPO/RTO, objem, sloupce mřížky, šířka boxu, typografie, clamp) a kontrasty WCAG sedí' . ($calcBad ? ' [' . implode(', ', $calcBad) . ']' : ''), $calcBad === []);
$bankOk = true;
foreach (require $ROOT . '/src/paths/paths_v75_bank.php' as $q) if ($q['type'] === 'single' && !in_array($q['answer'], $q['options'], true)) $bankOk = false;
$bank = array_column(require $ROOT . '/src/paths/paths_v75_bank.php', 'answer', 'id');
$check('banka 4.A (záloha): ≥ 20 otázek, single má odpověď mezi možnostmi, číselné odpovědi přepočítané (RPO 4 h, 96 záloh/den, 230 GB, 180 min, 23 bodů, 6 záloh, 5 GB, 4 h)', $bankOk && count($bank) >= 20
    && $bank['ops.backup.003'] === 4 && $bank['ops.backup.004'] === 24 * 60 / 15 && $bank['ops.backup.008'] === 200 + 6 * 5 && $bank['ops.backup.009'] === 360 / 2 && $bank['ops.backup.013'] === 7 + 4 + 12
    && $bank['ops.backup.016'] === intdiv(2000, 300) && $bank['ops.backup.021'] === 3 + 2 && $bank['ops.backup.019'] === 10 - 6);
$h0 = comp75_hash('class_4a');
$check('řízená změna verze: otisk katalogu je stabilní a schválený otisk = aktuální; změna = „změněno od schválení“ (simulace jiným otiskem)', $h0 === comp75_hash('class_4a') && strlen($h0) === 12
    && comp75_status('class_4a', ['classes' => ['class_4a' => ['status' => 'schvaleno', 'hash' => '000000000000']]])['changed'] === true);

require __DIR__ . '/lib/v75_learning_part2.php';

exit(audit_summary($state, 'V75_LEARNING'));
