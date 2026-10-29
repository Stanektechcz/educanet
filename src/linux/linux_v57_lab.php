<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v57 · Linux Lab – herní engine.
 *
 * Stav žáka: storage/linux_v57/<třída>__<sha1>.json.php (instance úrovní, vyřešené úlohy, log příkazů).
 * Události (vyřešení, nápovědy, cizí kód): storage/lab_v57_events.json.php – z nich počítá žebříčky arena_v57.php.
 * Každý zápis drží zámek přes čtení i zápis (žádné ztracené změny při 30 žácích najednou).
 */

// v59 · i18n: nástroje/testy tenhle soubor někdy načítají přímo bez bootstrap.php – tr()/edu_locale() musí existovat.
if (!function_exists('tr')) {
    require_once __DIR__ . '/i18n_v58.php';
    require_once __DIR__ . '/i18n_v59.php';
}
require_once __DIR__ . '/linux_v57_core.php';
require_once __DIR__ . '/linux_v57_world.php';
require_once __DIR__ . '/linux_v57_shell.php';
require_once __DIR__ . '/linux_v57_cmd_files.php';
require_once __DIR__ . '/linux_v57_cmd_shell.php';
require_once __DIR__ . '/linux_v57_cmd_text.php';
require_once __DIR__ . '/linux_v57_cmd_sys.php';
require_once __DIR__ . '/linux_v57_cmd_net.php';
require_once __DIR__ . '/linux_v57_manual.php';
require_once __DIR__ . '/linux_v57_levels.php';
require_once __DIR__ . '/linux_v57_levels_ops.php';

const LAB57_XP = [1 => 15, 2 => 25, 3 => 40];
const LAB57_CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const LAB57_RATE_CMDS = 90;
const LAB57_RATE_SUBMITS = 12;

// ---------------------------------------------------------------------------
// Úložiště
// ---------------------------------------------------------------------------

function lab57_storage_dir(): string
{
    if (isset($GLOBALS['lab57_storage_override']) && is_string($GLOBALS['lab57_storage_override'])) return $GLOBALS['lab57_storage_override'];
    return defined('STORAGE_DIR') ? STORAGE_DIR : __DIR__ . '/storage';
}

/** Atomická úprava JSON souboru: jeden zámek LOCK_EX drží čtení, úpravu i zápis. */
function lab57_store_update(string $path, callable $mutate): array
{
    if (function_exists('storage_update')) return storage_update($path, $mutate);
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0770, true);
    $fp = fopen($path, 'c+');
    if ($fp === false) throw new RuntimeException('Nelze otevřít úložiště laboratoře.');
    try {
        if (!flock($fp, LOCK_EX)) throw new RuntimeException('Nelze uzamknout úložiště laboratoře.');
        rewind($fp);
        $raw = (string)stream_get_contents($fp);
        $raw = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
        $data = json_decode($raw, true);
        $data = is_array($data) ? $data : [];
        $data = $mutate($data);
        if (!is_array($data)) throw new RuntimeException('Neplatná data laboratoře.');
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, "<?php http_response_code(403); exit; ?>\n" . $json . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } finally {
        fclose($fp);
    }
    if (function_exists('php_json_cache_forget')) php_json_cache_forget($path);
    return $data;
}

/** Čtení pod sdíleným zámkem jádra úložiště (DAT58-04); bez jádra (izolované audity) prosté čtení. */
function lab57_store_read(string $path): array
{
    if (function_exists('storage_read_request')) return storage_read_request($path, false); // v61: paměť požadavku (N+1)
    if (function_exists('storage_read')) return storage_read($path, false);
    if (!is_file($path)) return [];
    $raw = (string)file_get_contents($path);
    $raw = preg_replace('/^<\?php.*?\?>\s*/s', '', $raw) ?? $raw;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function lab57_state_path(string $classId, string $studentKey): string
{
    return lab57_storage_dir() . '/linux_v57/' . preg_replace('/[^a-z0-9_]/i', '', $classId) . '__' . sha1($studentKey) . '.json.php';
}

/** Závody mají vlastní malý soubor událostí – žebříček (30 žáků × dotaz po 5 s) nečte celou historii Labu. v58: cesta podle registru kontextů. */
function lab57_events_path(string $context = 'practice'): string
{
    if (function_exists('lab58_context_events_path')) return lab58_context_events_path($context);
    if (str_starts_with($context, 'race:')) {
        return lab57_storage_dir() . '/linux_v57/race_' . preg_replace('/[^a-z0-9]/', '', substr($context, 5)) . '.events.json.php';
    }
    return lab57_storage_dir() . '/lab_v57_events.json.php';
}

function lab57_events(string $context = 'practice'): array
{
    $rows = lab57_store_read(lab57_events_path($context));
    return array_values(array_filter($rows, 'is_array'));
}

// ---------------------------------------------------------------------------
// Kódy a semínka
// ---------------------------------------------------------------------------

function lab57_secret(): string
{
    static $secret = null;
    if (is_string($secret)) return $secret;
    if (isset($GLOBALS['lab57_secret_override']) && is_string($GLOBALS['lab57_secret_override'])) return $secret = $GLOBALS['lab57_secret_override'];
    $data = lab57_store_update(lab57_storage_dir() . '/lab_v57_secret.json.php', static function (array $d): array {
        if (empty($d['secret'])) $d['secret'] = bin2hex(random_bytes(32));
        return $d;
    });
    return $secret = (string)$data['secret'];
}

/** Kód úrovně pro konkrétního žáka a kontext (procvičování / závod): EDU-XXXX-XXXX. */
function lab57_code(string $classId, string $studentKey, string $ctx, string $levelId): string
{
    $hash = hash_hmac('sha256', $classId . '|' . $studentKey . '|' . $ctx . '|' . $levelId, lab57_secret(), true);
    $out = '';
    for ($i = 0; $i < 8; $i++) $out .= LAB57_CODE_ALPHABET[ord($hash[$i]) % 32];
    return 'EDU-' . substr($out, 0, 4) . '-' . substr($out, 4, 4);
}

function lab57_seed(string $classId, string $studentKey, string $ctx, array $level): string
{
    return hash('sha256', 'v57|' . $classId . '|' . $studentKey . '|' . $ctx . '|' . $level['id'] . '|' . (int)$level['v']);
}

function lab57_norm_code(string $raw): string
{
    return strtoupper((string)preg_replace('/\s+/', '', trim($raw)));
}

// ---------------------------------------------------------------------------
// Svět úrovně
// ---------------------------------------------------------------------------

/** $session (v58): context, prefix, role, player, shared – jen pro čtení v příkazech; stavba světa na roli nezávisí. */
function lab57_build_world(array $level, string $seed, array $codes, int $now, array $session = []): Lab57World
{
    $opts = (array)($level['world'] ?? []);
    $w = lab57_world_new($seed, $now, $opts);
    $w->level = $level;
    $w->codes = $codes;
    $w->session = $session;
    if (!empty($opts['sandbox'])) lab57_world_sandbox_home($w, new Lab57Rng($seed . '|home'));
    if (function_exists('lab58_extend_world')) lab58_extend_world($w, $level);
    if (is_callable($level['build'] ?? null)) ($level['build'])($w, new Lab57Rng($seed . '|build'));
    $w->fs->seal();
    $w->baseAccounts = function_exists('lab58_accounts_hash') ? lab58_accounts_hash($w) : '';
    if (is_callable($level['programs'] ?? null)) ($level['programs'])($w);
    if (is_callable($level['net'] ?? null)) ($level['net'])($w);
    return $w;
}

const LAB57_STATE_FIELDS = ['cwd', 'oldCwd', 'env', 'aliases', 'history', 'lastExit', 'services', 'procs', 'net', 'packages', 'journal', 'mem', 'pidCounter', 'hostname'];

function lab57_apply_state(Lab57World $w, array $row): void
{
    if ((int)($row['schema'] ?? 0) !== LAB57_SCHEMA || (int)($row['lv'] ?? 0) !== (int)($w->level['v'] ?? 1)) return;
    $w->fs->applyOverlay(lab57_overlay_decode((array)($row['overlay'] ?? [])));
    foreach (LAB57_STATE_FIELDS as $field) {
        if (!array_key_exists($field, $row)) continue;
        $value = $row[$field];
        if (is_array($w->{$field})) {
            if (is_array($value)) $w->{$field} = $value;
        } elseif (is_int($w->{$field})) {
            $w->{$field} = (int)$value;
        } else {
            $w->{$field} = (string)$value;
        }
    }
    $w->procs = array_combine(array_map('intval', array_keys($w->procs)), array_values($w->procs)) ?: [];
    // v58: rozšiřující stav, změnění uživatelé/skupiny a simulovaný čas.
    if (is_array($row['ext'] ?? null)) $w->ext = array_replace($w->ext, $row['ext']);
    if (is_array($row['users'] ?? null) && is_array($row['groups'] ?? null)) { $w->users = $row['users']; $w->groups = $row['groups']; }
    if (function_exists('lab58_now')) $w->now = lab58_now($w);
    if (is_callable($w->level['net'] ?? null)) ($w->level['net'])($w);
}

function lab57_export_state(Lab57World $w, array $row): array
{
    $row['schema'] = LAB57_SCHEMA;
    $row['lv'] = (int)($w->level['v'] ?? 1);
    $row['overlay'] = lab57_overlay_encode($w->fs->overlay());
    foreach (LAB57_STATE_FIELDS as $field) $row[$field] = $w->{$field};
    $row['history'] = array_slice((array)$w->history, -200);
    $row['journal'] = array_slice((array)$w->journal, -150);
    if ($w->ext !== []) $row['ext'] = $w->ext; else unset($row['ext']);
    if (function_exists('lab58_accounts_hash') && lab58_accounts_hash($w) !== $w->baseAccounts) { $row['users'] = $w->users; $row['groups'] = $w->groups; }
    else unset($row['users'], $row['groups']);
    return $row;
}

/** Normalizace výstupu pro porovnání v golfu (mezery na koncích a vícenásobné mezery se ignorují). */
function lab57_norm_output(string $text): string
{
    $lines = array_map(static fn(string $l): string => (string)preg_replace('/\s+/', ' ', trim($l)), explode("\n", str_replace("\r", '', $text)));
    while ($lines !== [] && end($lines) === '') array_pop($lines);
    return implode("\n", $lines);
}

function lab57_stdout_of(array $run): string
{
    $out = '';
    foreach ($run['chunks'] as [$fd, $text]) if ($fd === 1) $out .= (string)preg_replace('/\e\[[0-9;]*m/', '', $text);
    return $out;
}

/** @return array{ok:bool,passed:int,tests:int,len:int,detail:string} */
function lab57_golf_eval(array $level, string $cmd, string $baseSeed, int $now): array
{
    $cmd = trim($cmd);
    $tests = (int)($level['golf']['tests'] ?? 3);
    $reference = (string)($level['golf']['reference'] ?? '');
    $first = strtok($cmd, " \t");
    if ($cmd === '' || in_array($first, ['submit', 'answer', 'check', 'hint', 'mise', 'reset'], true)) return ['ok' => false, 'passed' => 0, 'tests' => $tests, 'len' => 0, 'detail' => 'Nejdřív spusť svůj příkaz, potom napiš submit.'];
    $passed = 0;
    $detail = '';
    for ($t = 1; $t <= $tests; $t++) {
        $seed = $baseSeed . '|golf|' . $t;
        $expected = lab57_norm_output(lab57_stdout_of(lab57_run_line(lab57_build_world($level, $seed, [], $now), $reference)));
        $got = lab57_norm_output(lab57_stdout_of(lab57_run_line(lab57_build_world($level, $seed, [], $now), $cmd)));
        if ($expected === $got) { $passed++; continue; }
        if ($detail === '') $detail = 'Test ' . $t . ': čekaný výstup má ' . count(explode("\n", $expected)) . ' řádků, tvůj ' . ($got === '' ? '0' : count(explode("\n", $got))) . '.';
    }
    return ['ok' => $passed === $tests, 'passed' => $passed, 'tests' => $tests, 'len' => mb_strlen($cmd), 'detail' => $detail];
}

/** @return list<array{label:string,ok:bool}> */
function lab57_eval_checks(array $level, Lab57World $w): array
{
    $out = [];
    foreach ((array)($level['checks'] ?? []) as $check) {
        $ok = false;
        try { $ok = (bool)($check['fn'])($w); } catch (Throwable) { $ok = false; }
        $out[] = ['label' => (string)$check['label'], 'ok' => $ok];
    }
    return $out;
}

function lab57_display_name(string $label): string
{
    $parts = preg_split('/\s+/u', trim($label)) ?: [];
    if ($parts === []) return 'Žák';
    $first = (string)array_shift($parts);
    $last = $parts !== [] ? mb_substr((string)end($parts), 0, 1) . '.' : '';
    return trim($first . ' ' . $last);
}

// ---------------------------------------------------------------------------
// Přístup k úrovním
// ---------------------------------------------------------------------------

/** Vyřešené úrovně žáka v kontextu (practice / race:ID). */
function lab57_solved(string $classId, string $studentKey, string $ctx = 'practice'): array
{
    $data = lab57_store_read(lab57_state_path($classId, $studentKey));
    return (array)($data['solved'][$ctx] ?? []);
}

/** Odemčená je první úroveň balíčku a každá, jejíž předchůdce je vyřešený (postup krok za krokem). */
function lab57_level_unlocked(array $level, array $solvedPractice): bool
{
    if (($level['pack'] ?? '') === 'free') return true;
    if (function_exists('lab58_pack') && (lab58_pack((string)$level['pack'])['unlock'] ?? 'sequential') === 'free') return true;
    $levels = lab57_pack_levels((string)$level['pack']);
    foreach ($levels as $i => $candidate) {
        if ($candidate['id'] !== $level['id']) continue;
        return $i === 0 || isset($solvedPractice[$levels[$i - 1]['id']]);
    }
    return false;
}

/** @return array{done:int,total:int,next:?string} */
function lab57_pack_progress(string $pack, array $solvedPractice): array
{
    $levels = lab57_pack_levels($pack);
    $done = 0;
    $next = null;
    foreach ($levels as $level) {
        if (isset($solvedPractice[$level['id']])) { $done++; continue; }
        $next ??= $level['id'];
    }
    return ['done' => $done, 'total' => count($levels), 'next' => $next];
}

/**
 * Ověří, že žák smí úroveň v daném kontextu hrát. Vrací chybovou zprávu nebo null.
 * Závodní kontext kontroluje arena_v57 (třída, úrovně závodu, čas); další kontexty registr v58.
 */
function lab57_access_error(array $level, array $ctx): ?string
{
    $context = (string)$ctx['context'];
    if ($level['id'] === 'sandbox') return null;
    if ($context === 'practice') {
        if (function_exists('lab58_pack_allows_class') && !lab58_pack_allows_class((string)$level['pack'], (string)$ctx['class'])) return tr('Tenhle balíček úloh není pro tvou třídu.');
        if (!lab57_level_unlocked($level, lab57_solved((string)$ctx['class'], (string)$ctx['student']))) return tr('Tahle úroveň se odemkne po vyřešení předchozí.');
        return null;
    }
    if (function_exists('lab58_context_access')) return lab58_context_access($level, $ctx);
    if (str_starts_with($context, 'race:')) {
        if (!function_exists('arena57_race_access')) return tr('Závody nejsou dostupné.');
        return arena57_race_access(substr($context, 5), (string)$ctx['class'], (string)$level['id'], (int)$ctx['now']);
    }
    return tr('Neznámý režim laboratoře.');
}

function lab57_resolve_level(string $id): ?array
{
    return $id === 'sandbox' ? lab57_sandbox_level() : lab57_level($id);
}

// ---------------------------------------------------------------------------
// Laboratorní příkazy (mise, hint, submit, answer, check, reset)
// ---------------------------------------------------------------------------

function lab57_cmds_lab(): array
{
    return ['mise' => 'lab57_cmd_mise', 'hint' => 'lab57_cmd_hint', 'submit' => 'lab57_cmd_submit', 'answer' => 'lab57_cmd_answer', 'check' => 'lab57_cmd_check', 'reset' => 'lab57_cmd_reset'];
}

/** Aktivní kontext laboratoře během jednoho požadavku (nastavuje lab57_session_run). */
function &lab57_active(): array
{
    if (!isset($GLOBALS['lab57_active']) || !is_array($GLOBALS['lab57_active'])) $GLOBALS['lab57_active'] = [];
    return $GLOBALS['lab57_active'];
}

function lab57_cmd_mise(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    $level = $a['level'] ?? $p->w->level;
    if (($level['id'] ?? '') === 'sandbox' || $level === []) { $p->line('Volný terminál – žádná mise. Zkus help nebo man ls.'); return 0; }
    $p->line('MISE: ' . $level['title']);
    $p->out(lab57_wrap((string)$level['story'], 2));
    $p->line('');
    $p->out(lab57_wrap('Úkol: ' . (string)$level['task'], 2));
    if ($level['type'] === 'check') {
        foreach (lab57_eval_checks($level, $p->w) as $c) $p->line('  ' . ($c['ok'] ? '[✔] ' : '[ ] ') . $c['label']);
    }
    if ($level['type'] === 'code') $p->line('  Odevzdání: submit EDU-XXXX-XXXX');
    if ($level['type'] === 'answer') $p->line('  Odevzdání: answer <' . ($level['answer_format'] ?? 'odpověď') . '>');
    if ($level['type'] === 'golf') $p->line('  Odevzdání: spusť svůj příkaz a pak napiš submit');
    return 0;
}

function lab57_cmd_hint(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    $level = $a['level'] ?? [];
    $hints = (array)($level['hints'] ?? []);
    if ($hints === []) { $p->line('Tady nápověda není – zkus help nebo man <příkaz>.'); return 0; }
    if (!empty($a['race']) && empty($a['race']['settings']['hints'])) { $p->line('V tomhle závodě jsou nápovědy vypnuté.'); return 1; }
    $used = (int)($a['row']['hints'] ?? 0);
    if ($used >= count($hints)) {
        $p->line('Všechny nápovědy už máš:');
        foreach ($hints as $i => $hint) $p->line('  ' . ($i + 1) . '. ' . $hint);
        return 0;
    }
    $a['row']['hints'] = $used + 1;
    $a['result']['hint_used'] = $used + 1;
    $p->line('Nápověda ' . ($used + 1) . '/' . count($hints) . ': ' . $hints[$used]);
    if (!empty($a['race'])) $p->line('(V závodě každá nápověda snižuje body za úlohu o ' . (int)round(100 * (float)($a['race']['settings']['hint_penalty'] ?? 0.2)) . ' %.)');
    return 0;
}

function lab57_rate_ok(array &$row, string $kind, int $limit, int $now): bool
{
    $list = array_values(array_filter((array)($row['rl'][$kind] ?? []), static fn($t): bool => (int)$t > $now - 60));
    if (count($list) >= $limit) { $row['rl'][$kind] = $list; return false; }
    $list[] = $now;
    $row['rl'][$kind] = $list;
    return true;
}

function lab57_cmd_submit(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    $level = $a['level'] ?? [];
    // Bez aktivní laboratorní relace (např. přímé volání mimo lab57_session) není co odevzdat –
    // $a['row'] by jinak nebylo pole a lab57_rate_ok() níže by shodilo TypeError.
    if ($level === []) { $p->line('Tady není žádná mise k odevzdání. Zkus mise pro rozkoukání.'); return 1; }
    $type = (string)($level['type'] ?? '');
    if (!empty($a['row']['solved_at'])) { $p->line('Tuhle úlohu už máš vyřešenou. ✔'); return 0; }
    if (!lab57_rate_ok($a['row'], 'submit', LAB57_RATE_SUBMITS, (int)$a['now'])) { $p->line('Moc pokusů za minutu – chvilku počkej a zkus to znovu.'); return 1; }
    if ($type === 'golf') {
        $last = '';
        foreach (array_reverse((array)$p->w->history) as $entry) {
            $first = strtok((string)$entry, " \t");
            if (!in_array($first, ['submit', 'hint', 'mise', 'check', 'reset', 'clear', 'history', 'man', 'help'], true)) { $last = (string)$entry; break; }
        }
        $res = lab57_golf_eval($level, $last, (string)$a['seed'], (int)$a['now']);
        if ($last !== '') $p->line('Testuji příkaz: ' . $last);
        $p->line('Testy: ' . $res['passed'] . '/' . $res['tests'] . ' prošlo.');
        if (!$res['ok']) { $p->line($res['detail'] !== '' ? $res['detail'] : 'Výstup zatím nesedí. Porovnej ho se zadáním.'); return 1; }
        $a['result']['golf_len'] = $res['len'];
        $a['result']['golf_cmd'] = $last;
        $a['result']['solve'] = true;
        $p->line('Délka řešení: ' . $res['len'] . ' znaků.');
        return 0;
    }
    if ($type !== 'code') { $p->line($type === 'answer' ? 'Tuhle úlohu odevzdáš příkazem answer <odpověď>.' : 'Tahle úloha se kontroluje sama – napiš check.'); return 1; }
    $code = lab57_norm_code(implode('', array_slice($argv, 1)));
    if ($code === '') { $p->line('Použití: submit EDU-XXXX-XXXX'); return 2; }
    $expected = (string)$a['codes']['CODE'];
    if (hash_equals($expected, $code)) { $a['result']['solve'] = true; return 0; }
    if (is_callable($a['foreign'] ?? null) && ($a['foreign'])($code)) {
        $a['result']['foreign'] = $code;
        $p->line('Tenhle kód patří někomu jinému – každý má v Labu svůj vlastní. Najdi ten svůj. 🙂');
        return 1;
    }
    $a['result']['wrong'] = true;
    $p->line('Kód nesouhlasí. Zkontroluj, že jsi ho zkopíroval(a) celý (EDU-XXXX-XXXX).');
    return 1;
}

function lab57_cmd_answer(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    $level = $a['level'] ?? [];
    if (($level['type'] ?? '') !== 'answer') { $p->line('Tahle úloha se neodevzdává příkazem answer.'); return 1; }
    if (!empty($a['row']['solved_at'])) { $p->line('Tuhle úlohu už máš vyřešenou. ✔'); return 0; }
    if (!lab57_rate_ok($a['row'], 'submit', LAB57_RATE_SUBMITS, (int)$a['now'])) { $p->line('Moc pokusů za minutu – chvilku počkej.'); return 1; }
    $given = mb_strtolower(trim(implode(' ', array_slice($argv, 1))));
    if ($given === '') { $p->line('Použití: answer <' . ($level['answer_format'] ?? 'odpověď') . '>'); return 2; }
    $expected = mb_strtolower(trim((string)($level['answer'])($p->w)));
    if (rtrim($given, '/') === rtrim($expected, '/') && $given !== '') { $a['result']['solve'] = true; return 0; }
    $a['result']['wrong'] = true;
    $p->line('To není ono. Formát odpovědi: ' . ($level['answer_format'] ?? 'text') . '.');
    return 1;
}

function lab57_cmd_check(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    $level = $a['level'] ?? [];
    if (($level['type'] ?? '') !== 'check') { $p->line('Kontrola se týká jen opravných úloh. Tady použij ' . (($level['type'] ?? '') === 'answer' ? 'answer' : 'submit') . '.'); return 1; }
    $checks = lab57_eval_checks($level, $p->w);
    foreach ($checks as $c) $p->line(($c['ok'] ? '[✔] ' : '[✘] ') . $c['label']);
    return array_filter($checks, static fn(array $c): bool => !$c['ok']) === [] ? 0 : 1;
}

function lab57_cmd_reset(Lab57Proc $p, array $argv): int
{
    $a = &lab57_active();
    if (!empty($a['no_reset'])) { $p->line('V tomhle režimu se úroveň resetovat nedá – sdílený svět patří celému týmu.'); return 1; }
    $a['result']['reset'] = true;
    $p->line('Úroveň se vrátila do původního stavu.');
    return 0;
}

// ---------------------------------------------------------------------------
// Relace: spuštění příkazu, doplňování, uložení z editoru, stav
// ---------------------------------------------------------------------------

/** Výstup pro prohlížeč: platné UTF-8, řídicí znaky kromě ESC nahrazené tečkou. */
function lab57_sanitize_output(string $text): string
{
    $text = mb_scrub($text, 'UTF-8');
    return (string)preg_replace('/[\x00-\x08\x0B-\x1A\x1C-\x1F\x7F]/', '·', $text);
}

function lab57_level_points(array $level, int $hints, ?array $race): int
{
    $base = (int)($level['points'] ?? 100);
    $penalty = $race !== null ? (float)($race['settings']['hint_penalty'] ?? 0.2) : 0.1;
    return (int)round($base * max(0.4, 1 - $penalty * $hints));
}

/**
 * Hlavní vstup: spustí jednu operaci v relaci žáka.
 * $ctx: class, student, label, context (practice|race:ID|<prefix>:<id>), level, now, [classmates => list<studentKey>]
 * $op: run|complete|save|state|reset
 * v58: kontext z registru (lab58_ctx_prepare) určuje klíč stavu (sdílený svět týmu), roli, semínko a body;
 * události (command, open, hint, submit_ok, submit_fail, complete, reset) se vysílají až po uvolnění zámku.
 */
function lab57_session(array $ctx, string $op, array $input = []): array
{
    $ctx = lab58_ctx_prepare($ctx);
    $level = lab57_resolve_level((string)$ctx['level']);
    if ($level === null) return ['ok' => false, 'error' => tr('Neznámá úroveň.')];
    $error = lab57_access_error($level, $ctx);
    if ($error !== null) return ['ok' => false, 'error' => $error];
    $level = lab58_level_for_role($level, $ctx['role']);
    $race = lab58_context_info($ctx);
    $ctx['info'] = $race;
    $stateKey = (string)$ctx['state_key'];
    $canReset = (bool)((lab58_context_spec((string)$ctx['prefix']) ?? [])['reset'] ?? true);
    $key = (string)$ctx['context'] . ':' . $level['id'];
    $seed = lab58_context_seed($ctx, $level);
    $codes = ['CODE' => lab57_code((string)$ctx['class'], $stateKey, (string)$ctx['context'], (string)$level['id'])];
    $session = ['context' => (string)$ctx['context'], 'prefix' => (string)$ctx['prefix'], 'role' => $ctx['role'], 'player' => (string)$ctx['player'], 'shared' => (bool)$ctx['shared']];
    $response = ['ok' => true];
    $solveEvent = null;
    $extraEvents = [];
    $emit = [];
    lab57_store_update(lab57_state_path((string)$ctx['class'], $stateKey), static function (array $data) use ($ctx, $op, $input, $level, $race, $key, $seed, $codes, $session, $canReset, &$response, &$solveEvent, &$extraEvents, &$emit): array {
        $data['v'] = 1;
        $row = (array)($data['inst'][$key] ?? []);
        if ((int)($row['schema'] ?? 0) !== LAB57_SCHEMA || (int)($row['lv'] ?? 0) !== (int)$level['v']) {
            $row = ['started' => $ctx['now'], 'hints' => 0, 'cmds' => 0, 'tx' => []];
        }
        $w = lab57_build_world($level, $seed, $codes, $ctx['now'], $session);
        $fresh = $ctx['shared'] ? lab58_player_state($w) : [];
        lab57_apply_state($w, $row);
        if ($ctx['shared']) lab58_player_apply($w, $row, (string)$ctx['player'], $fresh);
        $wasSolved = !empty($row['solved_at']);
        if ($op === 'run') {
            if (!lab57_rate_ok($row, $ctx['shared'] ? 'cmd:' . $ctx['player'] : 'cmd', LAB57_RATE_CMDS, $ctx['now'])) {
                $response = ['ok' => false, 'error' => 'Příliš mnoho příkazů za minutu. Chvilku počkej.'];
                $data['inst'][$key] = $row;
                return $data;
            }
            $line = (string)($input['line'] ?? '');
            $active = &lab57_active();
            $active = ['level' => $level, 'row' => $row, 'result' => [], 'codes' => $codes, 'seed' => $seed, 'now' => $ctx['now'], 'race' => $race, 'no_reset' => !$canReset, 'foreign' => static function (string $code) use ($ctx, $level): bool {
                $mates = $ctx['classmates'] ?? [];
                foreach ((array)(is_callable($mates) ? $mates() : $mates) as $other) {
                    if ((string)$other === (string)$ctx['student']) continue;
                    if (hash_equals(lab57_code((string)$ctx['class'], (string)$other, (string)$ctx['context'], (string)$level['id']), $code)) return true;
                }
                return false;
            }];
            $run = lab57_run_line($w, $line);
            $row = $active['row'];
            $result = $active['result'];
            $active = [];
            $row['cmds'] = (int)($row['cmds'] ?? 0) + 1;
            $out = [];
            $stderr = '';
            foreach ($run['chunks'] as [$fd, $text]) {
                $out[] = [$fd, lab57_sanitize_output($text)];
                if ($fd === 2) $stderr .= $text;
            }
            $errorClass = lab58_classify_error($stderr, (int)$run['exit'], $line);
            $emit[] = ['command', ['line' => mb_substr($line, 0, 400), 'exit' => (int)$run['exit'], 'error_class' => $errorClass, 'out' => mb_substr(implode('', array_column($out, 1)), 0, 300), 'hint' => (int)($result['hint_used'] ?? 0), 'seq' => (int)$row['cmds']]];
            if (!empty($result['reset'])) {
                unset($data['inst'][$key]);
                $emit[] = ['reset', ['via' => 'command']];
                $response += ['reset' => true, 'out' => [[1, "Úroveň se vrátila do původního stavu.\n"]], 'prompt' => lab57_prompt(lab57_build_world($level, $seed, $codes, $ctx['now'], $session))];
                return $data;
            }
            $checks = $level['type'] === 'check' ? lab57_eval_checks($level, $w) : [];
            if (!$wasSolved && $level['type'] === 'check' && $checks !== [] && array_filter($checks, static fn(array $c): bool => !$c['ok']) === []) $result['solve'] = true;
            $response += ['out' => $out, 'echo' => $run['echo'], 'exit' => $run['exit'], 'tips' => $w->tips, 'clear' => !empty($w->effects['clear']), 'net' => $w->effects['net'] ?? [], 'manual' => $w->effects['manual'] ?? null, 'checks' => $checks];
            if (isset($w->effects['editor'])) {
                $token = bin2hex(random_bytes(12));
                $editor = $w->effects['editor'];
                $row['edit'] = ['token' => $token, 'path' => $editor['path'], 'root' => (bool)$editor['root'], 'exp' => $ctx['now'] + 3600];
                $response['editor'] = ['path' => $editor['path'], 'name' => $editor['name'], 'content' => lab57_sanitize_output((string)$editor['content']), 'token' => $token, 'writable' => (bool)$editor['writable'], 'root' => (bool)$editor['root']];
            }
            if (!empty($result['hint_used'])) { $extraEvents[] = ['kind' => 'hint', 'n' => (int)$result['hint_used']]; $emit[] = ['hint', ['n' => (int)$result['hint_used']]]; }
            if (!empty($result['foreign'])) $extraEvents[] = ['kind' => 'foreign_code'];
            if ($errorClass === 'wrong_answer') $emit[] = ['submit_fail', ['line' => mb_substr($line, 0, 200), 'foreign' => !empty($result['foreign'])]];
            if (!$wasSolved && !empty($result['solve']) && $level['id'] !== 'sandbox') {
                $row['solved_at'] = date(DATE_ATOM, $ctx['now']);
                $info = ['at' => $row['solved_at'], 'points' => lab58_context_points($level, (int)($row['hints'] ?? 0), $ctx), 'hints' => (int)($row['hints'] ?? 0), 'cmds' => (int)$row['cmds'], 'secs' => max(1, $ctx['now'] - (int)($row['started'] ?? $ctx['now']))];
                if (isset($result['golf_len'])) { $info['golf_len'] = (int)$result['golf_len']; $info['golf_cmd'] = mb_substr((string)$result['golf_cmd'], 0, 300); }
                $data['solved'][(string)$ctx['context']][$level['id']] = $info;
                $solveEvent = $info;
                $emit[] = ['submit_ok', ['line' => mb_substr($line, 0, 200)]];
                $response['solved'] = true;
                $response['learn'] = (string)($level['learn'] ?? '');
            }
            $tx = (array)($row['tx'] ?? []);
            $tx[] = ['p' => lab57_prompt($w), 'c' => mb_substr($line, 0, 400), 'o' => mb_substr(implode('', array_map(static fn(array $c): string => $c[1], $out)), 0, 3000)];
            $row['tx'] = array_slice($tx, -40);
            $data['log'] = array_slice(array_merge((array)($data['log'] ?? []), [['t' => date(DATE_ATOM, $ctx['now']), 'ctx' => (string)$ctx['context'], 'lvl' => $level['id'], 'cmd' => mb_substr($line, 0, 200), 'exit' => $run['exit']]]), -300);
            $data['stats']['cmds'] = (int)($data['stats']['cmds'] ?? 0) + 1;
        } elseif ($op === 'save') {
            $edit = (array)($row['edit'] ?? []);
            if (!hash_equals((string)($edit['token'] ?? ''), (string)($input['token'] ?? '')) || (int)($edit['exp'] ?? 0) < $ctx['now'] || (string)($edit['path'] ?? '') !== (string)($input['path'] ?? '')) {
                $response = ['ok' => false, 'error' => 'Editor vypršel. Otevři soubor znovu příkazem nano.'];
                return $data;
            }
            $content = str_replace("\r", '', (string)($input['content'] ?? ''));
            $w->root = (bool)$edit['root'];
            $err = null;
            $ok = $w->writeFile((string)$edit['path'], $content, false, $err);
            $w->root = false;
            $response['message'] = $ok ? '[ Zapsáno ' . count(lab57_lines($content)) . ' řádků ]' : '[ Chyba zápisu ' . $edit['path'] . ': ' . $err . ' ]';
            $response['saved'] = $ok;
            if (!$ok && $err === 'Permission denied') $response['message'] .= ' – soubor patří správci, otevři ho přes sudo nano';
            $checks = $level['type'] === 'check' ? lab57_eval_checks($level, $w) : [];
            $response['checks'] = $checks;
            if ($ok && !$wasSolved && $level['type'] === 'check' && $checks !== [] && array_filter($checks, static fn(array $c): bool => !$c['ok']) === []) {
                $row['solved_at'] = date(DATE_ATOM, $ctx['now']);
                $info = ['at' => $row['solved_at'], 'points' => lab58_context_points($level, (int)($row['hints'] ?? 0), $ctx), 'hints' => (int)($row['hints'] ?? 0), 'cmds' => (int)($row['cmds'] ?? 0), 'secs' => max(1, $ctx['now'] - (int)($row['started'] ?? $ctx['now']))];
                $data['solved'][(string)$ctx['context']][$level['id']] = $info;
                $solveEvent = $info;
                $emit[] = ['submit_ok', ['line' => 'nano ' . mb_substr((string)$edit['path'], 0, 190)]];
                $response['solved'] = true;
                $response['learn'] = (string)($level['learn'] ?? '');
            }
        } elseif ($op === 'complete') {
            $response['complete'] = lab57_complete($w, (string)($input['line'] ?? ''));
            return $data;
        } elseif ($op === 'reset') {
            if (!$canReset) { $response = ['ok' => false, 'error' => 'V tomhle režimu se úroveň resetovat nedá.']; return $data; }
            unset($data['inst'][$key]);
            $emit[] = ['reset', ['via' => 'op']];
            $response['prompt'] = lab57_prompt(lab57_build_world($level, $seed, $codes, $ctx['now'], $session));
            return $data;
        } else {
            $response += (array)lab58_filter('state_payload', lab57_state_payload($level, $w, $row, $race, $ctx), ['level' => $level, 'ctx' => $ctx, 'row' => $row, 'world' => $w]);
            $emit[] = ['open', ['solved' => $wasSolved]];
        }
        $response['prompt'] = lab57_prompt($w);
        $response['solved'] = $response['solved'] ?? false;
        $response['already'] = $wasSolved;
        $response['hints_used'] = (int)($row['hints'] ?? 0);
        $data['inst'][$key] = lab57_export_state($w, $ctx['shared'] ? lab58_player_export($w, $row, (string)$ctx['player']) : $row);
        return $data;
    });
    $base = ['ctx' => (string)$ctx['context'], 'prefix' => (string)$ctx['prefix'], 'class' => (string)$ctx['class'], 'student' => (string)$ctx['student'], 'state_key' => $stateKey,
        'level' => (string)$level['id'], 'pack' => (string)$level['pack'], 'role' => $ctx['role'], 'ts' => (int)$ctx['now']];
    foreach ($emit as [$name, $payload]) lab58_emit($name, $payload + $base);
    $label = lab57_display_name((string)($ctx['label'] ?? ''));
    foreach ($extraEvents as $event) lab57_event_add($ctx, $level, $event + ['label' => $label]);
    if ($solveEvent !== null) {
        $event = lab57_event_add($ctx, $level, ['kind' => 'solve', 'label' => $label] + $solveEvent);
        $response['points'] = (int)($event['points'] ?? 0);
        $response['first_blood'] = !empty($event['first']);
        if ((string)$ctx['context'] === 'practice' && function_exists('learning_award_once') && empty($ctx['cli'])) {
            $xp = LAB57_XP[(int)$level['difficulty']] ?? 15;
            if (lab58_with_session(static fn(): bool => learning_award_once((string)$ctx['class'], 'v57:solve:' . $level['id'], $xp))) $response['xp'] = $xp;
        }
        lab58_ctx_call($ctx, 'on_complete', [$ctx, $level, $event], null);
        lab58_emit('complete', ['points' => (int)($event['points'] ?? 0), 'hints' => (int)$solveEvent['hints'], 'secs' => (int)$solveEvent['secs'], 'cmds' => (int)$solveEvent['cmds'], 'first' => !empty($event['first']), 'golf_len' => $solveEvent['golf_len'] ?? null] + $base);
    }
    if ($op === 'run' && !empty($response['ok'])) $response = (array)lab58_filter('run_response', $response, ['level' => $level, 'ctx' => $ctx]);
    return $response;
}

/** Zapíše událost; v kontextu s first_blood (závod) určí „první krev“ (první vyřešení úlohy ve třídě). */
function lab57_event_add(array $ctx, array $level, array $event): array
{
    $stored = [];
    $parsed = lab58_context_parse((string)$ctx['context']);
    $firstBlood = !empty((lab58_context_spec((string)($parsed['prefix'] ?? '')) ?? [])['first_blood']);
    lab57_store_update(lab57_events_path((string)$ctx['context']), static function (array $rows) use ($ctx, $level, $event, $firstBlood, &$stored): array {
        $event += ['id' => bin2hex(random_bytes(6)), 'class_id' => (string)$ctx['class'], 'student_key' => (string)$ctx['student'], 'ctx' => (string)$ctx['context'], 'level' => (string)$level['id'], 'pack' => (string)$level['pack'], 'at' => date(DATE_ATOM, (int)$ctx['now'])];
        if (($event['kind'] ?? '') === 'solve' && $firstBlood) {
            $first = true;
            foreach ($rows as $row) {
                if (is_array($row) && ($row['kind'] ?? '') === 'solve' && ($row['ctx'] ?? '') === $event['ctx'] && ($row['level'] ?? '') === $event['level']) { $first = false; break; }
            }
            if ($first) {
                $event['first'] = true;
                $event['points'] = (int)round((int)$event['points'] * 1.25);
            }
        }
        $rows[] = $event;
        $stored = $event;
        return array_values(array_filter($rows, 'is_array'));
    });
    return $stored;
}

/** JSON pro terminál; v58: + context (prefix, label, role, shared) a role úrovně. Prochází filtrem state_payload (v lab57_session). */
function lab57_state_payload(array $level, Lab57World $w, array $row, ?array $race, array $ctx = []): array
{
    $hintsUsed = (int)($row['hints'] ?? 0);
    $nodes = [];
    foreach ((array)($w->net['nodes'] ?? []) as $id => $node) {
        if (!empty($node['hidden']) && !in_array($id, (array)($w->net['wan_path'] ?? []), true)) continue;
        $nodes[] = ['id' => (string)$id, 'label' => (string)($node['label'] ?? $id), 'ip' => (string)($node['ip'] ?? ''), 'kind' => (string)($node['kind'] ?? 'server'), 'x' => (int)($node['x'] ?? 0), 'y' => (int)($node['y'] ?? 0)];
    }
    $links = [];
    $path = array_merge(['pc', 'switch'], (array)($w->net['wan_path'] ?? []));
    for ($i = 0; $i < count($path) - 1; $i++) $links[] = [$path[$i], $path[$i + 1]];
    foreach ((array)($w->net['links'] ?? []) as [$a, $b]) {
        if (in_array($a, ['router', 'isp'], true) && in_array($b, ['isp', 'ix'], true)) continue;
        $links[] = [$a, $b];
    }
    return [
        'level' => [
            'id' => $level['id'], 'pack' => $level['pack'], 'title' => $level['title'], 'type' => $level['type'], 'story' => $level['story'], 'task' => $level['task'],
            'difficulty' => (int)$level['difficulty'], 'points' => (int)$level['points'], 'commands' => array_values((array)$level['commands']),
            'hints_total' => count((array)$level['hints']), 'hints' => array_slice((array)$level['hints'], 0, $hintsUsed), 'answer_format' => (string)($level['answer_format'] ?? ''),
            'topology' => !empty($level['topology']), 'learn' => !empty($row['solved_at']) ? (string)$level['learn'] : '', 'role' => $level['role'] ?? null,
        ],
        'checks' => $level['type'] === 'check' ? lab57_eval_checks($level, $w) : [],
        'solved' => !empty($row['solved_at']),
        'tx' => (array)($row['tx'] ?? []),
        'history' => array_slice((array)$w->history, -100),
        'topology' => ['nodes' => $nodes, 'links' => array_values(array_unique($links, SORT_REGULAR))],
        'race' => $race !== null ? ['id' => (string)($race['id'] ?? ''), 'title' => (string)($race['title'] ?? ''), 'ends_at' => $race['ends_at'] ?? null, 'hints' => !empty($race['settings']['hints'])] : null,
        'motd' => $level['id'] === 'sandbox' ? (string)($w->fs->get('/etc/motd')['c'] ?? '') : '',
        'context' => ['id' => (string)($ctx['context'] ?? 'practice'), 'prefix' => (string)($ctx['prefix'] ?? 'practice'), 'label' => (string)((lab58_context_spec((string)($ctx['prefix'] ?? '')) ?? [])['label'] ?? ''), 'role' => $ctx['role'] ?? null, 'shared' => !empty($ctx['shared'])],
    ];
}

/** Doplňování tabulátorem: jména příkazů na začátku, jinak cesty. */
function lab57_complete(Lab57World $w, string $line): array
{
    $parts = preg_split('/\s+/', $line) ?: [''];
    $word = (string)end($parts);
    $first = count($parts) <= 1;
    $candidates = [];
    if ($first || (count($parts) === 2 && in_array($parts[0], ['sudo', 'man', 'which', 'type'], true))) {
        foreach (array_merge(array_keys(lab57_command_registry()), array_keys($w->aliases)) as $name) {
            if ($word !== '' && str_starts_with((string)$name, $word)) $candidates[] = (string)$name;
        }
        if (count($parts) === 2 && $parts[0] === 'man') $candidates = array_values(array_filter(array_keys((array)(v57_manual()['commands'] ?? [])), static fn($n): bool => str_starts_with((string)$n, $word)));
    }
    if ($candidates === [] && !($first)) {
        $expanded = str_starts_with($word, '~') ? $w->home() . substr($word, 1) : $word;
        $slash = strrpos($expanded, '/');
        $dirPart = $slash === false ? '' : substr($expanded, 0, $slash + 1);
        $prefix = $slash === false ? $expanded : substr($expanded, $slash + 1);
        $dirAbs = $w->abs($dirPart === '' ? '.' : $dirPart);
        $dirNode = $w->fs->get($dirAbs);
        if ($dirNode !== null && ($dirNode['t'] ?? '') === 'd' && $w->can($dirNode, 'r')) {
            foreach ($w->fs->children($dirAbs) as $name) {
                if (!str_starts_with($name, $prefix)) continue;
                if ($name[0] === '.' && !str_starts_with($prefix, '.')) continue;
                $shownDir = str_starts_with($word, '~') ? '~' . substr($dirPart, strlen($w->home())) : $dirPart;
                $candidates[] = $shownDir . $name . ($w->fs->isDir(($dirAbs === '/' ? '' : $dirAbs) . '/' . $name) ? '/' : '');
            }
        }
    }
    sort($candidates);
    $common = $candidates[0] ?? '';
    foreach ($candidates as $c) {
        while ($common !== '' && !str_starts_with($c, $common)) $common = substr($common, 0, -1);
    }
    return ['word' => $word, 'candidates' => array_slice($candidates, 0, 60), 'common' => $common];
}

/** Souhrn laboratoře žáka pro stránky Labu a pro učitele. */
function lab57_student_summary(string $classId, string $studentKey): array
{
    $data = lab57_store_read(lab57_state_path($classId, $studentKey));
    $practice = (array)($data['solved']['practice'] ?? []);
    $points = 0;
    foreach ($practice as $info) $points += (int)($info['points'] ?? 0);
    return ['solved' => $practice, 'count' => count($practice), 'points' => $points, 'cmds' => (int)($data['stats']['cmds'] ?? 0), 'log' => array_slice((array)($data['log'] ?? []), -60)];
}

// v58: rozšiřitelné jádro (registry, kontexty, události, log) + načtení rozšíření linux_v58_cmd_*/linux_v58_levels_*.
require_once __DIR__ . '/linux_v58_ext.php';
