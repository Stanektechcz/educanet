<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v72.1 · audit rozhodnutí školy z 8. 10. 2026 (ŠVP, domácí příprava, jména žáků, AI prompty, exit ticket).
 *   1) soubory vrstvy (strict_types, guard, ≤ 800 řádků, PHP 8.1), registr modulu `nastaveni_vyuky`, mapa, tmavý režim, politiky a oprávnění,
 *   2) ŠVP: zdroj (dokument školy, URL, datum ověření), krátké formulace, 112 lekcí s vazbou z katalogu, stav „návrh“, oddělení od otisku
 *      obsahu v72, schválení / vrácení vazby (otisk, důvod, log, neznámá akce),
 *   3) jména žáků: výchozí zobrazená, osobní volba učitele, „Žák N“,
 *   4) exit ticket: výchozí „jen zpětná vazba“, režim u každé odpovědi, do návrhu v66 (sumativní testy) jen odpovědi v režimu „podklad ke známce“,
 *      váhy 40/40/20 beze změny; žák vidí režim před odpovědí; domácí příprava „dobrovolné“,
 *   5) AI prompty: 112 lekcí × 6 promptů (v77 za běhu, dřív lesson kity), upozornění na soukromí, bez duplicit, překlepů „..“, URL a identity školy,
 *      žákovský blok bez JS s tr() (en/uk), náhled pro učitele,
 *   6) HTTP: záložka Nastavení výuky, CSRF (419), vlastní volba jmen, režim exit ticketu; v režimu účtů cizí třída 403, asistent jen čte.
 *   php tools/v72_1_decisions_audit.php     Dočasné úložiště i cache modelu. Konec: V72_1_DECISIONS_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v721-decisions')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V721_KEY = 'v721-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V721_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['teacher_operations_v46.php', 'teacher_scope_v59.php', 'teacher_v58.php', 'teacher_nav_v68.php', 'cockpit_v70.php',
    'tutorial_v52.php', 'learning_v56.php', 'runtime_content.php', 'session_v53.php', 'accounts_v53.php', 'intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (array_unique(array_merge((array)$mods['hodina']['files'], (array)$mods['schvalovani']['files'], (array)$mods['nastaveni_vyuky']['files'])) as $f) require_once $ROOT . '/' . $f;
require_once $ROOT . '/lesson_exit_v72_views.php';
require_once $ROOT . '/grading_v66_views.php';
require_once $ROOT . '/ai_prompts_v77.php';   // v77: generátor promptů běží za běhu (lesson kity vyřazené – změna limitu auditu, BUILD_MANIFEST_V77.md)
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn): bool { try { $fn(); return false; } catch (Throwable $e) { return true; } };
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && !str_starts_with(lm71_cache_dir(), $ROOT));

// ---------------------------------------------------------------- 1) soubory, registr, politiky
$libs = ['svp_v72.php', 'svp_v72_map.php', 'svp_v72_views.php', 'teacher_prefs_v72_1.php', 'teacher_prefs_v72_1_views.php', 'ai_prompts_v72_1.php', 'ai_prompts_v77.php',
    'lang/en/ui/lesson_v72_1.php', 'lang/uk/ui/lesson_v72_1.php'];
$cli = ['tools/v72_1_decisions_audit.php'];
$bad = [];
foreach (array_merge($libs, $cli) as $f) {
    $s = $read($f);
    $guard = in_array($f, $cli, true) ? "if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }" : "basename(__FILE__)) { http_response_code(403); exit; }";
    if (!str_contains($s, 'declare(strict_types=1);') || !str_contains($s, $guard) || substr_count($s, "\n") > 800 || preg_match('/\bjson_validate\s*\(|readonly\s+class|const\s+(?:int|string|array|bool)\s+[A-Z]/', $s) === 1) $bad[] = $f;
}
$check('soubory v72.1: strict_types, guard (knihovny SCRIPT_FILENAME, CLI PHP_SAPI), ≤ 800 řádků, PHP 8.1' . ($bad ? ' [' . implode(', ', $bad) . ']' : ''), $bad === [], false);
$nv = $mods['nastaveni_vyuky'] ?? [];
$map = teacher68_tab_map();
$check('registr: nový modul `nastaveni_vyuky` (POST jen prefix tp721_, bez GET), v sekci Výuka a v UI68_TEACHER_DARK_TABS; `schvalovani` má soubory ŠVP; `hodina` dál bez post/get',
    array_keys((array)($nv['post'] ?? [])) === ['tp721_'] && !isset($nv['get']) && in_array('nastaveni_vyuky', teacher58_tabs(), true) && ($map['nastaveni_vyuky']['section'] ?? '') === 'vyuka'
    && in_array('nastaveni_vyuky', UI68_TEACHER_DARK_TABS, true) && array_intersect(['svp_v72.php', 'svp_v72_views.php', 'ai_prompts_v72_1.php'], (array)$mods['schvalovani']['files']) === ['svp_v72.php', 'svp_v72_views.php', 'ai_prompts_v72_1.php']
    && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get']) && array_column(c70_related_rules()['nastaveni_vyuky'] ?? [], 0) === ['hodina', 'schvalovani', 'hodnoceni66']);
$check('politiky: lc72_svp_approve / lc72_svp_return / tp721_class_exit = třída povinná a v rozsahu; tp721_self_names bez třídy; jiné tp721_* / lc72_svp_* zakázané',
    teacher59_action_policy('lc72_svp_approve') === ['class' => 'required'] && teacher59_action_policy('lc72_svp_return') === ['class' => 'required']
    && teacher59_action_policy('tp721_class_exit') === ['class' => 'required'] && teacher59_action_policy('tp721_self_names') === ['class' => 'optional']
    && teacher59_action_policy('tp721_class_delete')['deny'] === true && teacher59_action_policy('lc72_svp_delete')['deny'] === true && teacher59_action_policy('tp721_self_other')['deny'] === true);
$check('oprávnění: jména = view (každá role včetně asistenta), režim exit ticketu a vazba ŠVP = content.manage (učitel ano, asistent ne)',
    teacher_action_permission('tp721_self_names') === 'view' && teacher_action_permission('tp721_class_exit') === 'content.manage' && teacher_action_permission('lc72_svp_approve') === 'content.manage'
    && teacher_permission_for_role('assistant', 'view') && !teacher_permission_for_role('assistant', 'content.manage') && teacher_permission_for_role('teacher', 'content.manage'));

// ---------------------------------------------------------------- 2) ŠVP
$outcomes = svp72_outcomes();
$longest = max(array_map(static fn(array $o): int => mb_strlen($o['text']), $outcomes));
$check('zdroj ŠVP: dokument školy z brno.educanet.cz (https), název, obor 18-20-M/01, platnost a datum ověření 2026-10-11', str_starts_with(SVP72_SOURCE['url'], 'https://brno.educanet.cz/')
    && str_contains(SVP72_SOURCE['title'], 'Inteligentní digitální systémy 2026') && SVP72_SOURCE['verified_at'] === '2026-10-11' && str_contains(SVP72_SOURCE['field'], '18-20-M/01') && safe_url(SVP72_SOURCE['url']) === SVP72_SOURCE['url']);
$check('výsledky ŠVP: ' . count($outcomes) . ' krátkých formulací (nejdelší ' . $longest . ' znaků ≤ 140), id ids26-<předmět><ročník>-nn, předmět z katalogu, ročník 1–4, strana dokumentu',
    count($outcomes) >= 40 && $longest <= 140 && array_reduce(array_keys($outcomes), static fn(bool $ok, string $id): bool => $ok && preg_match('/^ids26-[a-z]{2,3}[1-4]-\d{2}$/', $id) === 1
        && isset(SVP72_SUBJECTS[$outcomes[$id]['subject']]) && str_contains($id, strtolower($outcomes[$id]['subject']) . $outcomes[$id]['year']) && $outcomes[$id]['page'] > 0, true));
$svpBad = [];
$used = [];
foreach (LM71_CLASSES as $c) for ($n = 1; $n <= 28; $n++) {
    $ids = svp72_lesson_ids($c, $n);
    if (count($ids) < 1 || count($ids) > 4 || count($ids) !== count((array)(svp72_map()[$c][$n] ?? []))) $svpBad[] = $c . ' L' . $n;
    foreach ($ids as $id) $used[$id] = true;
    $model = lm71_lesson($c, $n);
    $cur = $model['curriculum']['svp'] ?? null;
    if (!is_array($cur) || $cur['status'] !== 'navrh' || preg_match('/^[0-9a-f]{16}$/', (string)$cur['hash']) !== 1 || $cur['url'] !== SVP72_SOURCE['url']) $svpBad[] = $c . ' L' . $n . ' model';
}
$check('vazba: 112 lekcí má 1–4 platné výsledky, model lm71 nese curriculum.svp (zdroj, otisk, stav „návrh“), použito všech ' . count($outcomes) . ' výsledků' . ($svpBad ? ' [' . implode(', ', array_slice($svpBad, 0, 5)) . ']' : ''),
    $svpBad === [] && count($used) === count($outcomes));
$ov5 = lc72_overlay('class_3a', 5);
$check('oddělení od obsahu: overlay v72 nenese ŠVP, otisk obsahu L5 se vazbou nemění (schválení v72 zůstávají platná)', !isset($ov5['curriculum']) && lc72_overlay_hash($ov5) === lc72_overlay_hash(lc72_overlay_light('class_3a', 5)));
$st1 = svp72_status('class_3a', 1);
$check('ŠVP rozhodnutí: neznámá akce, cizí otisk, prázdný výběr a vrácení bez důvodu se odmítnou; nic se nezapíše',
    $throws(static fn() => svp72_decide('lc72_svp_delete', 'class_3a', [1 => $st1['hash']], '', 'A')) && $throws(static fn() => svp72_decide('lc72_svp_approve', 'class_3a', [1 => str_repeat('0', 16)], '', 'A'))
    && $throws(static fn() => svp72_decide('lc72_svp_approve', 'class_3a', [], '', 'A')) && $throws(static fn() => svp72_decide('lc72_svp_return', 'class_3a', [1 => $st1['hash']], '  ', 'A'))
    && $throws(static fn() => svp72_decide('lc72_svp_approve', 'class_9z', [1 => $st1['hash']], '', 'A')) && !is_file(lc72_path()));
$cnt = svp72_decide('lc72_svp_approve', 'class_3a', [1 => $st1['hash'], 2 => svp72_status('class_3a', 2)['hash']], '', 'Audit Učitel');
svp72_decide('lc72_svp_return', 'class_3a', [3 => svp72_status('class_3a', 3)['hash']], "Doplňte  SBE.\n", 'Audit Učitel');
$store = storage_read(lc72_path(), false);
$check('ŠVP schválení: L1+L2 schváleno, L3 vráceno s důvodem, L4 dál návrh; záznam pod klíčem „svp“ (obsahová schválení „lessons“ nedotčena), log bez dat žáků',
    $cnt === 2 && svp72_status('class_3a', 1)['status'] === 'schvaleno' && svp72_status('class_3a', 2)['by'] === 'Audit Učitel' && svp72_status('class_3a', 3)['status'] === 'vraceno'
    && svp72_status('class_3a', 3)['note'] === 'Doplňte SBE.' && svp72_status('class_3a', 4)['status'] === 'navrh' && !isset($store['lessons']) && count((array)$store['log']) === 3
    && ($store['log'][0]['kind'] ?? '') === 'svp' && lc72_status('class_3a', 5)['status'] === 'navrh');
$check('změna vazby po schválení: jiný otisk → „změněno“', svp72_status('class_3a', 1, ['class_3a' => ['1' => ['status' => 'schvaleno', 'hash' => 'ffffffffffffffff']]])['status'] === 'zmeneno');

// ---------------------------------------------------------------- 3) jména žáků
$tA = substr(hash('sha256', 'a'), 0, 16);
$tB = substr(hash('sha256', 'b'), 0, 16);
$defaultShown = !tp721_names_hidden($tA) && tp721_student_label('Jana Nováková', 3, false) === 'Jana Nováková';
tp721_set_names_hidden(true, $tA);
$check('jména: výchozí zobrazená; volba jednoho učitele platí jen pro něj („Žák 3“), neplatný učitel se odmítne; v úložišti jen hash učitele',
    $defaultShown && tp721_names_hidden($tA) && !tp721_names_hidden($tB) && tp721_student_label('Jana Nováková', 3, true) === 'Žák 3'
    && $throws(static fn() => tp721_set_names_hidden(true, 'Jana')) && !str_contains((string)file_get_contents(tp721_path()), 'Jana'));

// ---------------------------------------------------------------- 4) exit ticket a domácí příprava
$check('exit ticket: výchozí režim „jen zpětná vazba“ ve všech třídách, neznámý režim / třída se odmítne, váhy v66 40/40/20 beze změny',
    array_reduce(LM71_CLASSES, static fn(bool $ok, string $c): bool => $ok && tp721_exit_mode($c) === 'feedback', true) && $throws(static fn() => tp721_set_exit_mode('class_3a', 'exam', 'A'))
    && $throws(static fn() => tp721_set_exit_mode('class_9z', 'grade', 'A')) && G66_DEFAULT_WEIGHTS === ['summative_test' => 40, 'mastery' => 40, 'project_v65' => 20]);
lc72_decide('lc72_approve', 'class_3a', 5, lc72_overlay_hash($ov5), '', 'Audit Učitel');
lc72_decide('lc72_approve', 'class_3a', 6, lc72_overlay_hash(lc72_overlay('class_3a', 6)), '', 'Audit Učitel');
$_SESSION['next_class_id'] = 'class_3a';
$_SESSION['student_label'] = 'Audit Třeťák';
$sk = project_student_key('class_3a', 'Audit Třeťák');
$t5 = lx72_ticket('class_3a', 5);
$v5 = $t5['variants'][lx72_variant_index($sk, 5, count($t5['variants']))];
$blockF = audit_capture(static fn() => lx72_render_student_block('class_3a', 5));
$rF = lx72_submit('class_3a', $sk, 5, $v5['correct']);
$partF = tp721_exit_graded_part('class_3a', $sk);
tp721_set_exit_mode('class_3a', 'grade', 'Audit Učitel');
$t6 = lx72_ticket('class_3a', 6);
$v6 = $t6['variants'][lx72_variant_index($sk, 6, count($t6['variants']))];
$blockG = audit_capture(static fn() => lx72_render_student_block('class_3a', 6));
$rG = lx72_submit('class_3a', $sk, 6, ($v6['correct'] + 1) % count($v6['options']));
$partG = tp721_exit_graded_part('class_3a', $sk);
$ans = lx72_answers('class_3a');
$check('režim u odpovědi: L5 v režimu zpětné vazby (m=f), L6 po přepnutí na „podklad ke známce“ (m=g)', $rF['ok'] && $rF['mode'] === 'f' && $rG['ok'] && $rG['mode'] === 'g'
    && ($ans['5'][lx72_student_hash($sk)]['m'] ?? '') === 'f' && ($ans['6'][lx72_student_hash($sk)]['m'] ?? '') === 'g' && $partF === null && tp721_exit_mode('class_1a') === 'feedback');
$summ = g66_part_summative('class_3a', $sk, null);
$check('návrh v66: do „sumativních testů“ jde jen odpověď v režimu podklad ke známce (1 odpověď, 0 %), jako položka lx72:exit s důkazem L6; L5 (zpětná vazba) se nepočítá',
    is_array($partG) && $partG['n'] === 1 && $partG['pct'] === 0.0 && ($partG['chain']['ref'] ?? '') === 'lx72:exit' && ($partG['chain']['evidence'][0]['ref'] ?? '') === 'lx72:l06'
    && $summ['n'] === 1 && in_array('lx72:exit', array_column($summ['chain'], 'ref'), true) && g66v_test_label('lx72:exit') !== 'Ověření cesty');
tp721_set_exit_mode('class_3a', 'feedback', 'Audit Učitel');
$check('přepnutí zpět na zpětnou vazbu: exit tickety se do návrhu nepočítají (ani dřívější v režimu známky)', tp721_exit_graded_part('class_3a', $sk) === null && g66_part_summative('class_3a', $sk, null)['n'] === 0);
$check('žák vidí režim před odpovědí: „Režim: jen zpětná vazba“ / „Režim: podklad ke známce“ nad formulářem; domácí příprava „dobrovolné“ bez úvodního „Volitelné:“',
    str_contains($blockF, 'Režim: jen zpětná vazba') && str_contains($blockG, 'Režim: podklad ke známce') && strpos($blockG, 'Režim: podklad ke známce') < strpos($blockG, 'value="lx72_answer"')
    && str_contains($blockF, 'Domácí příprava (dobrovolné)') && !str_contains($blockF, 'Volitelné:') && !str_contains($blockF . $blockG, 'volitelné')
    && array_reduce(lx72_homework('class_3a', 5), static fn(bool $ok, array $h): bool => $ok && !str_starts_with($h['text'], 'Volitelné'), true));
$sumT = lx72_summary('class_3a', 6, [$sk]);
$check('učitel: souhrn exit ticketu nese režim třídy; prefs soubor bez dat žáků', ($sumT['mode'] ?? '') === 'feedback' && !str_contains((string)file_get_contents(tp721_path()), 'Třeťák'));

// ---------------------------------------------------------------- 5) AI prompty
$aipBad = [];
$seen = [];
foreach (LM71_CLASSES as $c) for ($n = 1; $n <= 28; $n++) {
    $prompts = aip77_original_prompts($c, $n);   // v77: původní lekce (bez schválení) – dřív sekce 6 lesson kitu
    $id = $c . ' L' . $n;
    if (count($prompts) !== 6) $aipBad[] = $id . ' počet';
    if (aip721_prompts($c, $n) !== $prompts) $aipBad[] = $id . ' zdroj';
    foreach ($prompts as $p) {
        if (preg_match('/(?<!\.)\.\.(?!\.)|https?:|www\.|EDUCANET|chatgpt|<script/iu', $p['text'] . $p['title']) === 1) $aipBad[] = $id . ' text';
        if (isset($seen[sha1($p['text'])])) $aipBad[] = $id . ' duplicita';
        $seen[sha1($p['text'])] = true;
    }
}
$privacy = implode(' ', aip721_privacy_lines());
$check('AI prompty: 112 lekcí × 6 promptů (v77 za běhu místo lesson kitů), upozornění na soukromí v bloku, bez duplicit, „..“, URL a identity školy' . ($aipBad ? ' [' . implode(', ', array_slice($aipBad, 0, 5)) . ']' : ''),
    $aipBad === [] && count($seen) === 672 && LC72_LINK_AI_PROMPTS === true && str_contains($privacy, 'Nevkládej osobní údaje') && str_contains($privacy, 'přihlašovací údaje školy')
    && str_contains($privacy, 'Výstup AI vždy ověř') && str_contains($privacy, 'napiš to: jaký nástroj'));
$aiBlock = audit_capture(static fn() => aip721_render_student_block('class_4a', 9));
$check('žák: blok „Procvič si s AI (dobrovolné)“ v <details>, 5 bodů upozornění (tr), 6 promptů česky, bez JS, odkazů a formulářů; v lekci se volá pod exit ticketem',
    str_contains($aiBlock, '<details class="lx72-card lx72-ai" id="ai-prompty">') && str_contains($aiBlock, 'Procvič si s AI (dobrovolné)') && substr_count($aiBlock, '<pre class="lx72-prompt"') === 6
    && substr_count($aiBlock, '<li>') === 5 && !preg_match('/<script|<a\s|<form|on[a-z]+=/i', $aiBlock) && str_contains($read('learning_v56_views.php'), 'aip721_render_student_block($classId, $lessonNo)')
    && in_array('ai_prompts_v72_1.php', (array)(require $ROOT . '/app/routes.php')['libs']['lesson_path_views'], true));
$en = require $ROOT . '/lang/en/ui/lesson_v72_1.php';
$uk = require $ROOT . '/lang/uk/ui/lesson_v72_1.php';
$msgids = array_merge(['Procvič si s AI (dobrovolné)', 'Než začneš:', 'Prompt označ a zkopíruj do AI nástroje. Prompty jsou česky – AI ti může odpovídat i v jiném jazyce, když ji o to požádáš.'], aip721_privacy_lines());
$check('i18n: doména lesson_v72_1 v manifestu, všech ' . count($msgids) . ' textů bloku AI má překlad en i uk; nové texty exit ticketu v doméně lesson_v72',
    ((require $ROOT . '/lang/domains_v59.php')['domains']['lesson_v72_1'] ?? []) === ['ai_prompts_v72_1.php'] && array_diff($msgids, array_keys($en)) === [] && array_diff($msgids, array_keys($uk)) === []
    && isset((require $ROOT . '/lang/uk/ui/lesson_v72.php')['Režim: podklad ke známce'], (require $ROOT . '/lang/en/ui/lesson_v72.php')['Domácí příprava (dobrovolné)']));
$prev = aip721_teacher_preview_html('class_3a', 5);
$check('učitel: náhled AI promptů ve Schvalování lekcí (6 promptů, česky)', substr_count($prev, '<li><strong>') === 6 && str_contains($read('lesson_approval_v72_views.php'), 'aip721_teacher_preview_html($class, $n)'));

require __DIR__ . '/lib/v72_1_decisions_http.php';
exit(audit_summary($state, 'V72_1_DECISIONS'));
