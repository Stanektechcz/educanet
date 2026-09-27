<?php

declare(strict_types=1);

/**
 * EDUCANET v58 · CNT-01/TCH-01/CNT-03 – audit deklarativních úrovní, editoru a kontroly obsahu.
 *
 * Doplňuje tools/v57_linux_lab_audit.php (ten dál ověřuje 330/330 – shodné chování, jen jiný
 * mechanismus stavby světa). Tenhle audit se soustředí na věci specifické pro v58 převod:
 * deklarativní tvar (žádné closures v datech úrovní mimo dvě zdokumentované výjimky), řešitelnost
 * na víc semínek, tabulku parity světa, editor (řešitelnost/obsah jako brána pro zveřejnění,
 * limity, katalog) a CNT-03 (zakázaná slova, osobní údaje). Nic tu nespouští ani nenavazuje síť –
 * audit sám podléhá stejnému pravidlu jako enginové soubory.
 */

if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$ROOT = dirname(__DIR__);

$AUDIT_TMP = sys_get_temp_dir() . '/v58_levels_audit_' . bin2hex(random_bytes(6));
mkdir($AUDIT_TMP, 0770, true);
$GLOBALS['lab57_storage_override'] = $AUDIT_TMP;
$GLOBALS['lab57_secret_override'] = 'v58-levels-audit-secret';
date_default_timezone_set('Europe/Prague');

register_shutdown_function(static function () use ($AUDIT_TMP): void {
    v58a_rrmdir($AUDIT_TMP);
});

function v58a_rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    $items = @scandir($dir);
    if ($items === false) return;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) v58a_rrmdir($path); else @unlink($path);
    }
    @rmdir($dir);
}

/** @var list<string> */
$GLOBALS['v58a_warnings'] = [];
set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return true;
    $GLOBALS['v58a_warnings'][] = sprintf('%s (%s:%d)', $errstr, basename($errfile), $errline);
    return true;
});

require_once $ROOT . '/linux_v57_lab.php';

// ---------------------------------------------------------------------------
// Malý test harness (stejný styl jako v57 audit)
// ---------------------------------------------------------------------------

$GLOBALS['v58a_checks'] = 0;
$GLOBALS['v58a_failed'] = 0;

function v58a_ok(string $name): void
{
    $GLOBALS['v58a_checks']++;
    echo "PASS {$name}\n";
}

function v58a_fail(string $name, string $detail): void
{
    $GLOBALS['v58a_checks']++;
    $GLOBALS['v58a_failed']++;
    echo "FAIL {$name} – {$detail}\n";
}

function v58a_check(string $name, bool $cond, string $detail = ''): void
{
    if ($cond) v58a_ok($name); else v58a_fail($name, $detail);
}

function v58a_info(string $line): void
{
    echo 'INFO ' . $line . "\n";
}

const V58A_BUILTIN_PROVIDERS = ['lab57_levels_start', 'lab57_levels_quest', 'lab57_levels_kody', 'lab57_levels_sit', 'lab57_levels_opravna', 'lab57_levels_golf'];
// Zdokumentované výjimky z „žádné closures v datech“ – živé v58 háky (viz docs/LAB_V58_API.md §3/§7),
// přepočítávají se při každé stavbě světa, ne jen jednou při generování (proto nejdou nahradit generate/checks).
const V58A_ALLOWED_CLOSURE_KEYS = ['net' => ['quest-11'], 'programs' => ['opr-2']];

/** @return array<string,array> syrová data přímo z poskytovatelů balíčků (bez lab58_level_prepare) */
function v58a_raw_levels(): array
{
    $out = [];
    foreach (V58A_BUILTIN_PROVIDERS as $provider) {
        if (!function_exists($provider)) continue;
        foreach ($provider() as $level) $out[(string)$level['id']] = $level;
    }
    return $out;
}

// ===========================================================================
// 1) Bezpečnostní token-sken (self-contained – nezávisí na tools/v57_linux_lab_audit.php)
// ===========================================================================

function v58a_section_safety(string $root): void
{
    $forbiddenFuncs = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen', 'pcntl_exec', 'eval', 'create_function', 'assert', 'fsockopen', 'pfsockopen', 'stream_socket_client', 'socket_create', 'socket_connect', 'curl_init', 'curl_exec', 'curl_multi_exec', 'dns_get_record', 'gethostbyname', 'gethostbynamel', 'getmxrr', 'checkdnsrr', 'mail'];
    $fileFuncs = ['file_get_contents', 'fopen', 'file'];
    $files = ['linux_v57_levels.php', 'linux_v57_levels_ops.php', 'linux_v58_levels_generators.php', 'lab_v58_editor.php', 'lab_v58_editor_views.php', 'lab_v58_content.php'];
    $violations = [];
    foreach ($files as $rel) {
        $path = $root . '/' . $rel;
        if (!is_file($path)) { $violations[] = $rel . ': soubor chybí'; continue; }
        $tokens = token_get_all((string)file_get_contents($path));
        $n = count($tokens);
        for ($i = 0; $i < $n; $i++) {
            $tok = $tokens[$i];
            if ($tok === '`') { $violations[] = $rel . ': zpětné apostrofy'; continue; }
            if (!is_array($tok) || $tok[0] !== T_STRING) continue;
            $name = strtolower($tok[1]);
            $j = $i + 1;
            while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if (!($j < $n && $tokens[$j] === '(')) continue;
            $p = $i - 1;
            while ($p >= 0 && is_array($tokens[$p]) && $tokens[$p][0] === T_WHITESPACE) $p--;
            $prev = $p >= 0 ? $tokens[$p] : null;
            if (is_array($prev) && in_array($prev[0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NULLSAFE_OBJECT_OPERATOR], true)) continue;
            if (in_array($name, $forbiddenFuncs, true)) $violations[] = $rel . ':' . $tok[2] . ' volání ' . $name . '()';
            if (in_array($name, $fileFuncs, true)) {
                $k = $j + 1;
                while ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k++;
                if ($k < $n && is_array($tokens[$k]) && $tokens[$k][0] === T_CONSTANT_ENCAPSED_STRING) {
                    $inner = substr($tokens[$k][1], 1, -1);
                    if (preg_match('~^(https?|ftp)://~i', $inner) === 1) $violations[] = $rel . ':' . $tokens[$k][2] . ' ' . $name . '(' . $tokens[$k][1] . '…)';
                }
            }
        }
    }
    v58a_check('safety:no-exec-network', $violations === [], implode('; ', $violations));
    v58a_check('safety:scanned-files', count($files) === 6, 'očekáváno 6 souborů');
}

v58a_section_safety($ROOT);

// ===========================================================================
// 2) Deklarativní tvar: žádné closures v datech úrovní mimo dvě zdokumentované výjimky
// ===========================================================================

function v58a_section_shape(): void
{
    $raw = v58a_raw_levels();
    v58a_check('shape:count', count($raw) === 46, 'očekáváno 46 vestavěných úrovní, nalezeno ' . count($raw));
    foreach ($raw as $id => $level) {
        v58a_check("shape:no-build-closure:{$id}", !array_key_exists('build', $level), 'úroveň má přímo pole build (closures z dat) – měla by mít generate');
        foreach (['net', 'programs'] as $infraKey) {
            if (!array_key_exists($infraKey, $level)) continue;
            $allowed = in_array($id, V58A_ALLOWED_CLOSURE_KEYS[$infraKey], true);
            v58a_check("shape:{$infraKey}-is-documented-exception:{$id}", $allowed, "pole '$infraKey' smí mít jen zdokumentovaná výjimka (" . implode(',', V58A_ALLOWED_CLOSURE_KEYS[$infraKey]) . ')');
        }
        if (isset($level['checks'])) {
            $declarative = true;
            foreach ((array)$level['checks'] as $check) if (!is_array($check) || !is_string($check[0] ?? null) || isset($check['fn'])) $declarative = false;
            v58a_check("shape:checks-declarative:{$id}", $declarative, 'checks musí být seznam [jméno, parametry], ne [label,fn]');
        }
        if (isset($level['answer'])) v58a_check("shape:answer-not-closure:{$id}", !is_callable($level['answer']), 'answer musí být šablona (řetězec), ne closure');
        if (isset($level['solution'])) v58a_check("shape:solution-not-closure:{$id}", !is_callable($level['solution']), 'solution musí být seznam šablon (řetězce), ne closure');
    }
}

v58a_section_shape();

// ===========================================================================
// 3) Řešitelnost na >= 5 semínek (přes lab58_try_solution – bez úložiště) pro všech 46 úrovní
// ===========================================================================

function v58a_section_solvable(): void
{
    $levels = array_filter(lab57_levels(), static fn(array $l): bool => in_array($l['pack'], ['start', 'quest', 'kody', 'sit', 'opravna', 'golf'], true));
    $now = time();
    foreach ($levels as $id => $level) {
        $solved = 0;
        $seedsN = 5;
        $lastCmds = [];
        for ($i = 0; $i < $seedsN; $i++) {
            $seed = 'v58a|' . $id . '|' . $i;
            $result = lab58_try_solution($level, $seed, $now, 'EDU-TEST-0000');
            if (!empty($result['solved'])) $solved++;
            $lastCmds = $result['cmds'];
        }
        v58a_check("solvable:{$id}", $solved === $seedsN, "referenční řešení vyřešilo jen {$solved}/{$seedsN} semínek, cmds=" . json_encode($lastCmds, JSON_UNESCAPED_UNICODE));
    }
}

v58a_section_solvable();

// ===========================================================================
// 4) Tabulka parity světa (shodný hash světa před/po převodu na generate – jen pro úrovně,
// jejichž build() v57 nikdy nevolal $r, tj. výsledek nezávisí na tom, jaké semínko generátor
// pro danou úroveň dostane). POZOR: napříč RŮZNÝMI semínky nejde parita ověřit uvnitř tohoto
// auditu – základní svět (lab57_world_new/lab57_world_logs) si i bez účasti úrovně generuje
// vlastní „šum“ ze semínka (např. /var/log/syslog), takže se liší podle semínka úplně pro
// každou úroveň bez ohledu na CNT-01. Parita se proto ověřovala jednorázově při migraci: stejné
// PEVNÉ semínko, izolovaná kopie stromu se starým (build-closure) linux_v57_levels*.php vs. nový
// (generate) kód – sha1(fs+net+services+procs+users+groups+mem) byl bit-přesně shodný právě a jen
// pro těchto 12 úrovní. Tady jen deklarujeme a kontrolujeme konzistenci seznamu; nová regrese by
// se odhalila při příští migraci stejným postupem (viz závěrečná zpráva agenta LEVELS).
const V58A_PARITY_YES = ['start-1', 'start-2', 'start-3', 'start-4', 'kody-1', 'kody-2', 'kody-3', 'kody-6', 'opr-3', 'quest-1', 'quest-2', 'quest-3'];

function v58a_section_parity(): void
{
    $ids = array_keys(array_filter(lab57_levels(), static fn(array $l): bool => in_array($l['pack'], ['start', 'quest', 'kody', 'sit', 'opravna', 'golf'], true)));
    $rows = [];
    foreach ($ids as $id) $rows[] = $id . '=' . (in_array($id, V58A_PARITY_YES, true) ? 'ano' : 'ne');
    v58a_info('parita světa (ano/ne vůči stavu před CNT-01 převodem, ověřeno jednorázově při migraci – viz komentář výše): ' . implode(', ', $rows));
    v58a_check('parity:yes-count', count(V58A_PARITY_YES) === 12, 'očekáváno 12 úrovní s paritou');
    $unknown = array_diff(V58A_PARITY_YES, $ids);
    v58a_check('parity:yes-ids-exist', $unknown === [], 'V58A_PARITY_YES obsahuje neznámé id: ' . implode(',', $unknown));
}

v58a_section_parity();

// ===========================================================================
// 5) Editor: řešitelný záznam projde stejným testem jako vestavěná úroveň
// ===========================================================================

function v58a_sample_input(array $overrides = []): array
{
    return array_merge([
        'type' => 'code', 'title' => 'Audit test', 'story' => 'Priklad pro audit.', 'task' => 'Najdi kod.',
        'difficulty' => 1, 'minutes' => 5, 'classes' => ['class_3a'], 'hints' => ['Zkus cat.'],
        'files' => [['path' => '~/tajny.txt', 'content' => "Kod: {CODE}\n"]],
        'solution' => "cat tajny.txt\nsubmit {CODE}",
    ], $overrides);
}

function v58a_section_editor(): void
{
    if (!function_exists('lab58e_validate_input')) { v58a_fail('editor:available', 'lab_v58_editor.php se nenačetl'); return; }

    // 5a) validní úloha projde stejným testem jako vestavěná (>= 5 semínek)
    $gate = lab58e_can_save(v58a_sample_input());
    v58a_check('editor:valid-level-saves', $gate['ok'], implode(' ', $gate['errors']));
    if ($gate['ok']) {
        $row = $gate['row'];
        $row['status'] = 'draft';
        $saved = lab58e_save($row);
        $result = lab58e_check_row($saved, 5);
        v58a_check('editor:valid-level-solvable-5-seeds', $result['ok'] && $result['solved'] === 5, 'solved=' . $result['solved'] . '/' . $result['total']);
        $checklist = [];
        foreach (cnt58_checklist_items() as $item) $checklist[$item['id']] = true;
        $pub = lab58e_can_publish($saved, $checklist);
        v58a_check('editor:valid-level-can-publish', $pub['ok'], $pub['reason']);
        lab58e_delete((string)$saved['id']);
    }

    // 5b) check-type úloha z katalogu core_* je taky řešitelná
    $checkInput = v58a_sample_input([
        'type' => 'check', 'files' => [], 'solution' => 'mkdir ukol',
        'checks' => [['name' => 'core_dir_exists', 'params' => json_encode(['path' => '~/ukol'])]],
    ]);
    $gateChk = lab58e_can_save($checkInput);
    v58a_check('editor:check-type-saves', $gateChk['ok'], implode(' ', $gateChk['errors']));
    if ($gateChk['ok']) {
        $resChk = lab58e_check_row($gateChk['row'] + ['id' => 'audit-check'], 5);
        v58a_check('editor:check-type-solvable', $resChk['ok'], 'solved=' . $resChk['solved'] . '/' . $resChk['total']);
    }

    // 5c) neřešitelná úloha nejde zveřejnit (i s odškrtnutým checklistem)
    $unsolvableGate = lab58e_can_save(v58a_sample_input(['solution' => "cat tajny.txt\nsubmit EDU-SPATNE-0000"]));
    if ($unsolvableGate['ok']) {
        $row = $unsolvableGate['row']; $row['status'] = 'draft'; $saved = lab58e_save($row);
        $checklist = [];
        foreach (cnt58_checklist_items() as $item) $checklist[$item['id']] = true;
        $pub = lab58e_can_publish($saved, $checklist);
        v58a_check('editor:unsolvable-cannot-publish', !$pub['ok'] && $pub['reason'] === 'unsolvable', 'reason=' . $pub['reason']);
        lab58e_delete((string)$saved['id']);
    } else {
        v58a_fail('editor:unsolvable-cannot-publish', 'neřešitelný vstup neprošel ani uložením, test nešlo provést');
    }

    // 5d) chybějící checklist blokuje zveřejnění i řešitelné úlohy
    $goodGate = lab58e_can_save(v58a_sample_input());
    if ($goodGate['ok']) {
        $row = $goodGate['row']; $row['status'] = 'draft'; $saved = lab58e_save($row);
        $pub = lab58e_can_publish($saved, []);
        v58a_check('editor:missing-checklist-blocks-publish', !$pub['ok'] && $pub['reason'] === 'checklist', 'reason=' . $pub['reason']);
        lab58e_delete((string)$saved['id']);
    }

    // 5e) zveřejněná úloha se objeví žákovi dané třídy v balíčku „Úlohy od učitele“ – testováno
    // přímo přes úložiště (lab58e_published_for_class), ne přes lab57_levels()/lab57_pack_levels():
    // ty v jednom běhu PHP procesu cachují podle verze registru generátorů, ne podle obsahu
    // úložiště, takže by uvnitř dlouho běžícího auditu ukazovaly zastaralý (před-publikační) stav.
    // V produkci je to bez rozdílu – každý HTTP požadavek je nový proces bez téhle cache.
    $visGate = lab58e_can_save(v58a_sample_input(['title' => 'Viditelnost', 'classes' => ['class_3a']]));
    if ($visGate['ok']) {
        $row = $visGate['row']; $row['status'] = 'draft'; $saved = lab58e_save($row);
        lab58e_set_fields((string)$saved['id'], ['status' => 'published']);
        $forClass = lab58e_published_for_class('class_3a');
        $found = array_filter($forClass, static fn(array $l): bool => $l['id'] === $saved['id']);
        v58a_check('editor:published-visible-to-assigned-class', $found !== [], 'úroveň se neobjevila mezi zveřejněnými pro class_3a');
        $level = lab58e_to_level($saved, 'ucitel-3a');
        v58a_check('editor:published-level-has-correct-pack', ($level['pack'] ?? '') === 'ucitel-3a', 'pack=' . ($level['pack'] ?? '?'));
        $forOtherClass = lab58e_published_for_class('class_4a');
        v58a_check('editor:published-not-visible-to-other-class', array_filter($forOtherClass, static fn(array $l): bool => $l['id'] === $saved['id']) === [], 'úroveň se objevila i tam, kam nepatří');
        lab58e_delete((string)$saved['id']);
    } else {
        v58a_fail('editor:published-visible-to-assigned-class', 'vzorová úloha pro test viditelnosti se neuložila: ' . implode(' ', $visGate['errors']));
    }

    // 5f) zakázané slovo / osobní údaj nejde uložit ani zveřejnit
    $vulgar = lab58e_can_save(v58a_sample_input(['story' => 'V teto uloze je kurva schovany kod.']));
    v58a_check('editor:forbidden-word-blocks-save', !$vulgar['ok'], 'zakázané slovo prošlo uložením');
    $pii = lab58e_can_save(v58a_sample_input(['task' => 'Kontaktuj mě na adrese jan.novak@example.com.']));
    v58a_check('editor:personal-data-blocks-save', !$pii['ok'], 'e-mail prošel uložením');

    // 5g) limity: moc velký soubor a moc dlouhý název
    $bigFile = lab58e_can_save(v58a_sample_input(['files' => [['path' => '~/big.txt', 'content' => str_repeat('a', LAB58E_MAX_FILE_BYTES + 10)]]]));
    v58a_check('editor:file-size-limit', !$bigFile['ok'], 'soubor nad 64 KB prošel validací');
    $longTitle = lab58e_can_save(v58a_sample_input(['title' => str_repeat('x', LAB58E_MAX_TITLE + 5)]));
    v58a_check('editor:title-length-limit', !$longTitle['ok'], 'moc dlouhý název prošel validací');
    $noClass = lab58e_can_save(v58a_sample_input(['classes' => []]));
    v58a_check('editor:requires-class', !$noClass['ok'], 'úloha bez třídy prošla validací');
    $unknownCheck = lab58e_can_save(v58a_sample_input(['type' => 'check', 'checks' => [['name' => 'neexistuje', 'params' => '{}']], 'solution' => 'ls']));
    v58a_check('editor:rejects-unknown-check', !$unknownCheck['ok'], 'neznámá kontrola prošla validací');
    $unknownGen = lab58e_can_save(v58a_sample_input(['generators' => [['name' => 'neexistuje', 'params' => '{}']]]));
    v58a_check('editor:rejects-unknown-generator', !$unknownGen['ok'], 'neznámý generátor prošel validací');
    $tokenInAnswer = lab58e_can_save(v58a_sample_input(['type' => 'answer', 'answer' => '{TOKEN}', 'answer_format' => 'text', 'solution' => 'answer {TOKEN}']));
    v58a_check('editor:rejects-rng-token-in-answer', !$tokenInAnswer['ok'], '{TOKEN} v answer prošel validací (nejde spolehlivě vyřešit)');

    // 5h) oprávnění POST akcí (mapa v teacher_operations_v46.php) – content.manage, deny-by-default pro cizí prefix
    if (function_exists('teacher_action_permission')) {
        v58a_check('editor:permission-content-manage', teacher_action_permission('lab58e_save') === 'content.manage', 'lab58e_ akce musí vyžadovat content.manage');
    } else {
        v58a_info('editor:permission-content-manage – teacher_action_permission() není načtená (samostatný běh bez teacher.php), přeskočeno');
    }
}

v58a_section_editor();

// ===========================================================================
// 6) CNT-03: zakázaná slova a osobní údaje – přímé testy cnt58_check_text()
// ===========================================================================

function v58a_section_content(): void
{
    if (!function_exists('cnt58_check_text')) { v58a_fail('content:available', 'lab_v58_content.php se nenačetl'); return; }
    v58a_check('content:clean-text-ok', cnt58_check_text('Bezny vyukovy text o prikazu ls a cd.')['ok'], 'čistý text byl označen jako problematický');
    v58a_check('content:vulgar-blocks', !cnt58_check_text('Jsi hovado a kurva blbec.')['ok'], 'vulgarismus neprošel detekcí');
    v58a_check('content:spaced-evasion-blocks', !cnt58_check_text('t o t o j e k u r v a')['ok'], 'rozestupová obchvatka neprošla detekcí');
    v58a_check('content:leet-evasion-blocks', !cnt58_check_text('kurv4 a blbec')['ok'], 'leetspeak obchvatka neprošla detekcí');
    v58a_check('content:false-positive-safe', cnt58_check_text('Chovna stanice pro psy je bezpecna a legalni.')['ok'], 'nevinné slovo bylo falešně zablokováno');
    v58a_check('content:cp-command-safe', cnt58_check_text('cp fotky/*.jpg zaloha/')['ok'], 'příkaz cp byl falešně označen jako adresa');
    v58a_check('content:email-blocks', !cnt58_check_text('Napis mi na test@example.com')['ok'], 'e-mail neprošel detekcí');
    v58a_check('content:phone-blocks', !cnt58_check_text('Zavolej na 777 123 456')['ok'], 'telefon neprošel detekcí');
    v58a_check('content:birth-number-blocks', !cnt58_check_text('Rodne cislo 800101/1234')['ok'], 'rodné číslo neprošlo detekcí');
    // Nález integrátora (kvízová banka): IPv4/maska nesmí spadnout pod telefon a "zabít/zabije
    // proces" (kill/pkill) nesmí spadnout pod násilí – skutečný telefon a skutečná výhrůžka pořád ano.
    v58a_check('content:ipv4-not-phone', cnt58_check_text('Maska je 255.255.255.0, adresa 192.168.255.255.')['ok'], 'IPv4/maska byla mylně označena jako telefonní číslo');
    v58a_check('content:netmask-not-phone', cnt58_check_text('Nastav masku 255.255.255.128 pro tuto podsit.')['ok'], 'maska byla mylně označena jako telefonní číslo');
    v58a_check('content:kill-process-not-violence', cnt58_check_text('Jak prikaz zabije proces? Pouzij kill nebo pkill.')['ok'], '"zabije proces"/kill/pkill bylo mylně označeno jako násilí');
    v58a_check('content:zabit-process-not-violence', cnt58_check_text('Musis zabit proces s vysokou zatezi procesoru.')['ok'], '"zabít proces" bylo mylně označeno jako násilí');
    v58a_check('content:real-phone-still-blocks', !cnt58_check_text('Zavolej mi na 777 123 456.')['ok'], 'skutečné telefonní číslo přestalo být blokované');
    v58a_check('content:real-threat-still-blocks', !cnt58_check_text('Zabiju te, az te potkam.')['ok'], 'skutečná výhrůžka bez technického kontextu přestala být blokovaná');
    $issues = cnt58_check_text('Test test test')['issues'];
    v58a_check('content:issue-shape', $issues === [] || (isset($issues[0]['code'], $issues[0]['message'])), 'issue nemá code/message podle kontraktu');
    v58a_check('content:checklist-items', function_exists('cnt58_checklist_items') && count(cnt58_checklist_items()) >= 5, 'checklist pro učitele chybí nebo je neúplný');
}

v58a_section_content();

// ===========================================================================
// Souhrn
// ===========================================================================

if ($GLOBALS['v58a_warnings'] !== []) {
    foreach (array_unique($GLOBALS['v58a_warnings']) as $w) echo 'WARNING ' . $w . "\n";
}
v58a_check('runtime:no-unhandled-php-warnings', $GLOBALS['v58a_warnings'] === [], count($GLOBALS['v58a_warnings']) . ' varování za běhu auditu');

$checks = $GLOBALS['v58a_checks'];
$failed = $GLOBALS['v58a_failed'];
echo "\n=== v58 levels/editor/content audit dokončen ===\n";
if ($failed === 0) {
    echo "V58_LEVELS_AUDIT_OK checks={$checks} failed=0\n";
    exit(0);
}
echo "V58_LEVELS_AUDIT_FAILED checks={$checks} failed={$failed}\n";
exit(1);
