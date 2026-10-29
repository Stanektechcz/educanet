<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v74 · audit cockpitu: rychlé hledání bez jmen v HTML (palette_v74.php), „Komu dnes pomoct“ (insights_v74*.php),
 * hromadné schválení lekcí a ŠVP (bulk_approval_v74*.php, akce ba74_approve) a Týdenní souhrn (weekly_v74*.php).
 *   1) registr, mapa, tmavý režim, politika ba74_approve a GET tyden74|paleta, oprávnění, napojení v teacher.php / rámci / JS,
 *   2) paleta: bez jmen a e-mailů v HTML, žádná na projektoru, tisku a zástupu, hledací klíč bez diakritiky,
 *   3) „Komu dnes pomoct“: každý důvod má text, zdroj a krok; nejvýš 5 žáků; „bez dat“ se nevybírá; skrytá jména → „Žák N“; prázdný stav,
 *   4) hromadné schválení: výběr týden / třída / rok, T-14 první, vrácené se nezahrnou, otisk, rozsah, už schválené, strop, jeden zápis, vrácení,
 *   5) týdenní souhrn: týden, plán vs. reflexe, T-14 termíny, bez jmen, tisk; CSS jen tokeny (mimo tisk), fokus, 44 px, velikost,
 *   6) HTTP (tools/lib/v74_cockpit_http.php): HTML bez jmen v paletě, JSON jen po přihlášení a v rozsahu, skrytá jména, CSRF, potvrzení, účty.
 *   php tools/v74_cockpit_audit.php     Dočasné úložiště i cache modelu. Konec: V74_COCKPIT_AUDIT_OK checks=N failed=0.
 */

error_reporting(E_ALL);
$ROOT = str_replace(chr(92), '/', dirname(__DIR__));
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
require_once __DIR__ . '/lib/audit_storage.php';
$tmp = rtrim(str_replace(chr(92), '/', edu_audit_temp_storage('v74-cockpit')), '/');
$cacheDir = $tmp . '-lm71cache';
putenv('EDUCANET_LM71_CACHE_DIR=' . $cacheDir);
register_shutdown_function(static function () use ($cacheDir): void {
    foreach (glob($cacheDir . '/{,.}*', GLOB_BRACE) ?: [] as $f) if (is_file($f)) @unlink($f);
    @rmdir($cacheDir);
});
const V74C_KEY = 'v74c-audit-teacher-key';
putenv('EDUCANET_TEACHER_EXPORT_KEY=' . V74C_KEY);
require_once $ROOT . '/bootstrap.php';
require_once __DIR__ . '/lib/audit.php';
require_once __DIR__ . '/lib/http_harness.php';
foreach (['src/teacher/teacher_operations_v46.php', 'src/teacher/teacher_operations_plus_v46_1.php', 'src/teacher/teacher_scope_v59.php', 'src/teacher/teacher_v58.php', 'src/teacher/teacher_nav_v68.php', 'src/teacher/cockpit_v70.php',
    'src/lessons/tutorial_v52.php', 'src/lessons/learning_v56.php', 'src/ops/runtime_content.php', 'src/lessons/session_v53.php', 'src/student/accounts_v53.php', 'src/student/intake_v51.php'] as $lib) require_once $ROOT . '/' . $lib;
$mods = teacher58_modules();
foreach (array_unique(array_merge((array)$mods['hodina']['files'], (array)$mods['hromadne74']['files'], (array)$mods['tyden74']['files'])) as $f) require_once $ROOT . '/' . $f;
require_once __DIR__ . '/lib/v59_scope_fixtures.php';

$state = audit_counter();
$check = audit_checker($state);
$read = static fn(string $rel): string => (string)@file_get_contents($ROOT . '/' . $rel);
$throws = static function (callable $fn, string $needle = ''): bool { try { $fn(); return false; } catch (Throwable $e) { return $needle === '' || str_contains($e->getMessage(), $needle); } };
$check('úložiště auditu i cache modelu jsou dočasné (ne ostrá storage/ ani cache/ projektu)', STORAGE_DIR === $tmp && str_contains($tmp, 'educanet-audit-') && str_starts_with(lc72_path(), $tmp . '/'));
audit_prewarm_accounts($GLOBALS['modules']);
$roster = [];
foreach (['class_1a', 'class_2a', 'class_3a', 'class_4a'] as $c) foreach (project_students_for_class($c) as $s) if (trim((string)$s['label']) !== '') $roster[] = (string)$s['label'];
$noNames = static function (string $html) use ($roster): bool {
    foreach ($roster as $label) if (str_contains($html, e($label)) || str_contains($html, $label)) return false;
    return true;
};

// ---------------------------------------------------------------- 1) registr, politiky, napojení
$map = teacher68_tab_map();
$ba = $mods['hromadne74'] ?? [];
$wk = $mods['tyden74'] ?? [];
$check('registr: `hromadne74` (POST jen ba74_, bez GET, Výuka), `tyden74` (bez POST, GET jen paleta, Dnes, bez ?class=), obě v UI68_TEACHER_DARK_TABS; Schvalování a Dnešní hodina beze změny politik',
    array_keys((array)($ba['post'] ?? [])) === ['ba74_'] && !isset($ba['get']) && ($map['hromadne74']['section'] ?? '') === 'vyuka' && !isset($wk['post'])
    && array_keys((array)($wk['get'] ?? [])) === ['paleta'] && ($map['tyden74']['section'] ?? '') === 'dnes' && ($map['tyden74']['class'] ?? true) === false
    && in_array('hromadne74', UI68_TEACHER_DARK_TABS, true) && in_array('tyden74', UI68_TEACHER_DARK_TABS, true) && array_keys((array)$mods['schvalovani']['post']) === ['lc72_']
    && !isset($mods['schvalovani']['get']) && !isset($mods['hodina']['post']) && !isset($mods['hodina']['get']) && array_slice(array_keys($map), 0, 2) === ['attention', 'hodina']);
$check('politiky: ba74_approve = třída povinná (class_ids[]), content.manage; ba74_reject zakázaná; GET tyden74|paleta = každý přihlášený (data filtruje rozsah), jiný GET tyden74 = unknown_get',
    teacher59_action_policy('ba74_approve') === ['class' => 'required'] && teacher_action_permission('ba74_approve') === 'content.manage' && teacher59_action_policy('ba74_reject')['deny'] === true
    && (teacher59_get_policies()['tyden74|paleta'] ?? null) === [] && !isset(teacher59_get_policies()['tyden74|export']));
$js = $read('assets/teacher-admin-v46.js');
$palJs = substr($js, (int)strpos($js, 'v74: paleta'));
$check('napojení: teacher.php vykreslí paletu jen po přihlášení (pal74_render_html), rámec skrývá ⌘K na projektoru/tisku/zástupu, JS načítá žáky až po otevření (fetch, bez innerHTML), verze JS zvýšená',
    str_contains($read('teacher.php'), 'if($authenticated) echo pal74_render_html($tab,$classId);') && !str_contains($read('teacher.php'), 'teacher_render_command_palette();')
    && str_contains($read('teacher.php'), 'teacher-admin-v46.js?v=74.0') && str_contains($read('src/teacher/teacher_shell_v68.php'), 'pal74_enabled($tab)') && str_contains($palJs, 'fetch(src')
    && str_contains($palJs, "credentials:'same-origin'") && !str_contains($palJs, 'innerHTML') && str_contains($palJs, 'textContent') && str_contains($js, 'data-command-backdrop'));

// ---------------------------------------------------------------- 2) paleta
$palHtml = pal74_render_html('attention', 'class_3a');
$check('paleta: na projektoru, tisku a zástupu se nevykreslí; jinde jen prázdný dialog (položky ze zdroje JSON v data-palette-src s třídou), žádné jméno ani e-mail',
    pal74_render_html('hodina_projektor', 'class_3a') === '' && pal74_render_html('hodina_tisk', 'class_3a') === '' && pal74_render_html('hodina_zastup', 'class_3a') === ''
    && str_contains($palHtml, 'data-palette-src="teacher.php?tab=tyden74&amp;paleta=1&amp;class=class_3a"') && str_contains($palHtml, 'data-palette-status') && $noNames($palHtml) && !str_contains($palHtml, '@')
    && count($roster) > 0 && !str_contains($palHtml, 'data-command-item') && pal74_static_entries('class_3a') !== [] && $noNames((string)json_encode(pal74_static_entries('class_3a'), JSON_UNESCAPED_UNICODE)));
$check('paleta: hledací klíč bez diakritiky a velkých písmen, mezery zachované („Žluťoučký Kůň“ → „zlutoucky kun“)', pal74_search_key('  Žluťoučký   Kůň ') === 'zlutoucky kun');
$data = pal74_students();
$check('paleta JSON (bez skrytých jmen): jen třídy v rozsahu, odkaz na detail žáka, bez e-mailu; počet = seznamy tříd',
    $data['ok'] && !$data['names_hidden'] && count($data['items']) === count($roster) && !str_contains((string)json_encode($data), '@')
    && array_filter($data['items'], static fn(array $i): bool => !str_starts_with($i['url'], 'teacher.php?tab=student360&class=class_')) === []);

// ---------------------------------------------------------------- 3) Komu dnes pomoct
$row = ['prev' => 50, 'overdue' => 2, 'signals' => ['Bez aktivity 9 dní']];
$ctx = ['exits' => [5 => ['ok' => 0], 6 => ['ok' => 0]], 'ticket_lessons' => [5, 6], 'trend' => [7 => 100, 8 => 50, 9 => 25], 'prev_lesson' => 9, 'absent_date' => '2026-10-05', 'followups' => ['Domluvit opravu projektu']];
$r = in74_reasons($row, $ctx);
$codes = array_column($r, 'code');
$complete = array_filter($r, static fn(array $x): bool => $x['text'] === '' || $x['source'] === '' || $x['action'] === '') === [];
$check('důvody: 2× chybný exit ticket, klesající výsledky (místo „nedokončená“), chybění, bez aktivity, úkoly po termínu, follow-up; seřazené podle váhy; každý má text, zdroj a navržený krok',
    $codes === ['exit_wrong', 'declining', 'absent', 'inactive', 'overdue', 'open_note'] && $complete && str_contains($r[0]['text'], 'lekce 5 a 6') && str_contains($r[1]['text'], '100 % → 50 % → 25 %')
    && $r[4]['text'] === '2 úkoly po termínu');
$r2 = in74_reasons(['prev' => 60, 'overdue' => 0, 'signals' => ['Nízké mastery (30 %)']], ['exits' => [5 => ['ok' => 1]], 'ticket_lessons' => [5, 6], 'trend' => [], 'prev_lesson' => 6]);
$check('důvody: chybí exit ticket poslední schválené lekce, nedokončená minulá lekce (pod 75 %), nízké zvládnutí; bez signálů prázdný seznam',
    array_column($r2, 'code') === ['unfinished', 'low_mastery', 'exit_missing'] && $r2[2]['text'] === 'Chybí exit ticket z lekce 6'
    && in74_reasons(['prev' => 90, 'overdue' => 0, 'signals' => []], ['exits' => [], 'ticket_lessons' => [], 'trend' => [10, 20, 30]]) === []);
$GLOBALS['educanet_runtime_indexes']['project_students:class_3a'] = [];
$m = lt70_model($GLOBALS['modules'], 'class_3a', 0, (int)strtotime('2026-10-12 08:00'));
$fake = [];
for ($i = 1; $i <= 8; $i++) $fake[] = ['key' => 'class_3a:student:' . str_pad(dechex($i), 24, 'a', STR_PAD_LEFT), 'hash' => str_pad(dechex($i), 24, 'a', STR_PAD_LEFT), 'label' => 'Fiktivní Žák ' . $i,
    'prev' => $i === 1 ? null : 10 * $i, 'overdue' => $i % 3, 'idle' => $i === 1, 'reasons' => $i === 2 ? ['Bez aktivity 8 dní'] : [], 'tasks' => 0, 'phases' => 0, 'percent' => 0];
$m['students'] = $fake;
$c = in74_candidates($m, (int)strtotime('2026-10-12 08:00'));
$scores = array_column($c['rows'], 'score');
$sorted = $scores;
rsort($sorted);
$check('výběr: nejvýš ' . IN74_MAX . ' žáků, „bez dat“ se nevybírá (1 bez aktivity), každý s aspoň jedním důvodem, seřazení podle naléhavosti, číslo = pořadí v tabulce žáků',
    count($c['rows']) === IN74_MAX && $c['idle'] === 1 && $c['with_reasons'] === 7 && $scores === $sorted && !in_array('Fiktivní Žák 1', array_column($c['rows'], 'label'), true)
    && array_filter($c['rows'], static fn(array $x): bool => $x['reasons'] === [] || $fake[$x['index'] - 1]['label'] !== $x['label']) === []);
$card = in74_card_html($m, (int)strtotime('2026-10-12 08:00'));
storage_update(tp721_path(), static function (array $d): array { $d['teachers'][tp721_teacher_hash()]['names_hidden'] = true; return $d; });
tp721_data(true);
$cardHidden = in74_card_html($m, (int)strtotime('2026-10-12 08:00'));
storage_update(tp721_path(), static function (array $d): array { $d['teachers'][tp721_teacher_hash()]['names_hidden'] = false; return $d; });
tp721_data(true);
$m['students'] = array_map(static fn(array $s): array => ['idle' => true] + $s, array_slice($fake, 0, 3));
foreach ($m['students'] as &$sIdle) $sIdle['idle'] = true;
unset($sIdle);
$cardIdle = in74_card_html($m);
$m['students'] = [];
$cardEmpty = in74_card_html($m);
$check('karta: „Komu dnes pomoct“, „Jak vybíráme?“, „Proč?“ u každého žáka, navržený krok s odkazem; skrytá jména → „Žák N“ bez jmen; prázdné stavy (bez dat, bez žáků) vysvětlené',
    str_contains($card, 'Komu dnes pomoct') && str_contains($card, 'Jak vybíráme?') && substr_count($card, '<summary>Proč?') === IN74_MAX && str_contains($card, 'Navržený krok:')
    && str_contains($card, 'Fiktivní Žák') && !str_contains($cardHidden, 'Fiktivní') && preg_match('~<strong>Žák \d+</strong>~u', $cardHidden) === 1 && str_contains($cardHidden, 'Jména jsou skrytá')
    && str_contains($cardIdle, 'Dnes nikoho nevybíráme') && str_contains($cardIdle, 'bez aktivity') && str_contains($cardEmpty, 'nemá žáky') && !str_contains($card, '<script'));
unset($GLOBALS['educanet_runtime_indexes']['project_students:class_3a']);

// ---------------------------------------------------------------- 4) hromadné schválení
$today = date('Y-m-d');
$all = ba74_candidates(['class_3a'], 'vse', ba74_monday($today), true, true, $today);
$expected = count(lc72_overlay_lessons('class_3a')) + count(array_filter(range(1, 28), static fn(int $n): bool => svp72_lesson_ids('class_3a', $n) !== []));
$firstNonT14 = null;
foreach ($all['items'] as $k => $it) if (!$it['t14'] && $firstNonT14 === null) $firstNonT14 = $k;
$orderOk = $firstNonT14 === null || array_filter(array_slice($all['items'], (int)$firstNonT14), static fn(array $i): bool => $i['t14']) === [];
$check('výběr „třída“: všechny návrhy lekcí i vazby ŠVP 3.A (' . $expected . '), T-14 vždy první, každá položka s otiskem, stavem a popisem změny',
    count($all['items']) === $expected && $expected > 20 && $orderOk && array_filter($all['items'], static fn(array $i): bool => preg_match('/^[0-9a-f]{16}$/', $i['hash']) !== 1 || $i['detail'] === '') === []);
$d5 = lc72_lesson_dates('class_3a')[5] ?? '';
$week = ba74_candidates(['class_3a'], 'tyden', ba74_monday($d5), true, true, $today);
$check('výběr „týden“: jen lekce s výukou v daném týdnu (lekce 5 3.A: obsah + ŠVP)', $d5 !== '' && array_unique(array_column($week['items'], 'date')) === [$d5]
    && in_array(['class_3a', 5, 'lesson'], array_map(static fn(array $i): array => [$i['class'], $i['lesson'], $i['kind']], $week['items']), true));
$byKey = [];
foreach ($all['items'] as $it) $byKey[$it['lesson'] . '|' . $it['kind']] = $it;
$v = static fn(string $k): string => 'class_3a|' . $byKey[$k]['lesson'] . '|' . $byKey[$k]['kind'] . '|' . $byKey[$k]['hash'];
$res = ba74_apply([$v('5|lesson'), $v('5|svp'), 'class_3a|6|lesson|0000000000000000', 'class_4a|5|lesson|' . str_repeat('a', 16), 'nesmysl'], ['class_3a'], 'Audit Učitel');
$okRows = array_values(array_filter($res, static fn(array $x): bool => $x['ok']));
$log = array_values(array_filter((array)(storage_read(lc72_path(), false)['log'] ?? []), static fn(array $l): bool => !empty($l['bulk'])));
$check('potvrzení: 2 schválené (obsah + ŠVP L5), změněný otisk, třída mimo rozsah a neplatná položka odmítnuté s českým důvodem; jeden zápis, log s příznakem bulk',
    count($res) === 5 && count($okRows) === 2 && lc72_status('class_3a', 5)['status'] === 'schvaleno' && svp72_status('class_3a', 5)['status'] === 'schvaleno'
    && str_contains($res[2]['message'], 'změnil') && str_contains($res[3]['message'], 'rozsahu') && str_contains($res[4]['message'], 'Neplatná') && count($log) === 2 && $log[0]['by'] === 'Audit Učitel');
$again = ba74_apply([$v('5|lesson')], ['class_3a'], 'Audit');
lc72_decide('lc72_return', 'class_3a', 5, $byKey['5|lesson']['hash'], 'Zpět po hromadném schválení.', 'Audit');
$after = ba74_candidates(['class_3a'], 'vse', ba74_monday($today), true, false, $today);
$retApply = ba74_apply([$v('5|lesson')], ['class_3a'], 'Audit');
$check('už schválené = beze změny; vrácení existující akcí lc72_return funguje; vrácená lekce se do výběru nezahrne a hromadně se neschválí; strop ' . BA74_MAX . ' položek',
    $again[0]['ok'] && str_contains($again[0]['message'], 'Už bylo') && count(array_filter((array)(storage_read(lc72_path(), false)['log'] ?? []), static fn(array $l): bool => !empty($l['bulk']))) === 2
    && lc72_status('class_3a', 5)['status'] === 'vraceno' && $after['returned'] === 1 && !in_array(5, array_column($after['items'], 'lesson'), true) && !$retApply[0]['ok']
    && $throws(static fn() => ba74_apply(array_fill(0, BA74_MAX + 1, 'x'), ['class_3a'], 'x'), 'nejvýš') && $throws(static fn() => ba74_apply([], ['class_3a'], 'x'), 'aspoň'));

// ---------------------------------------------------------------- 5) týdenní souhrn, CSS
$monday = wk74_week_start($d5, $today);
$plan = wk74_planned('class_3a', $monday);
$pend = wk74_pending('class_3a', date('Y-m-d', (int)strtotime($d5 . ' -3 days')), $monday);
$p5 = array_values(array_filter($pend, static fn(array $p): bool => $p['lesson'] === 5 && $p['kind'] === 'lesson'));
$check('týden: pondělí z libovolného data, neplatné ?tyden= = tento týden; plán týdne obsahuje lekci 5 3.A; T-14: vrácená L5 čeká, termín = výuka − 14 dní (po termínu)',
    $monday === date('Y-m-d', (int)strtotime('monday this week', (int)strtotime($d5))) && wk74_week_start('<x>', '2026-10-14') === '2026-10-12'
    && in_array(5, array_column($plan, 'lesson'), true) && count($p5) === 1 && $p5[0]['deadline'] === date('Y-m-d', (int)strtotime($d5 . ' -14 days')) && $p5[0]['late']);
$_GET = ['tyden' => $monday];
$wkHtml = audit_capture(static function (): void { wk74_render_tab($GLOBALS['modules'], 'class_3a'); });
$_GET = [];
$check('týdenní souhrn: odučeno vs. plán, čeká na schválení (T-14), potřebují pomoc, termíny, tisk a navigace týdnů; bez jmen žáků',
    str_contains($wkHtml, 'Týdenní souhrn') && str_contains($wkHtml, 'Odučeno / plán') && str_contains($wkHtml, 'Čeká na schválení') && str_contains($wkHtml, 'Potřebují pomoc')
    && str_contains($wkHtml, 'data-lp73-print hidden') && str_contains($wkHtml, 'Předchozí týden') && $noNames($wkHtml) && !str_contains($wkHtml, '<script'));
$css = (string)preg_replace('~/\*.*?\*/~s', '', $read('assets/cockpit-v74.css'));
$screen = (string)preg_replace('~@media print\s*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}~s', '', $css);
$jsDelta = strlen($read('assets/teacher-admin-v46.js')) - 9126;   // velikost teacher-admin-v46.js před v74
$check('CSS v74: mimo @media print jen tokeny (žádné pevné barvy), viditelný fokus, cíle 44 px, 390 px; ≤ 8 kB; nové CSS+JS fáze ≤ 20 kB',
    preg_match('~#[0-9a-fA-F]{3,8}\b|rgba?\(|hsla?\(~', $screen) !== 1 && str_contains($css, 'outline: 3px solid var(--ui-accent)') && substr_count($css, 'min-height: 44px') >= 3
    && str_contains($css, '@media (max-width: 480px)') && str_contains($css, '@media print') && strlen($read('assets/cockpit-v74.css')) <= 8192 && strlen($read('assets/cockpit-v74.css')) + max(0, $jsDelta) <= 20480, false);

require __DIR__ . '/lib/v74_cockpit_http.php';
exit(audit_summary($state, 'V74_COCKPIT'));
