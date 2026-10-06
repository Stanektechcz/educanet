<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v69 · tools/v69_retire.php – bezpečné vyřazení jednoho souboru: kopie → ověření SHA-256 → smazání originálu.
 *
 *   php tools/v69_retire.php --file=<relativní cesta> [--apply] [--root=<kořen projektu>]
 *
 * Dva druhy cílů (podle seznamu, ve kterém je cesta uvedena):
 *   RETIRE69_APPROVED – kód a assety; kopie do retired/v69/<cesta>.
 *   RETIRE69_LEGACY   – audity nad vyřazenými funkcemi (jen tools/*.php); přesun do tools/legacy/<název>.
 * Výchozí je --dry-run. --apply zpracuje VŽDY JEDEN soubor ze seznamu; jiný soubor skončí odmítnutím. Žádná shellová smyčka
 * (viz AUDIT_V59.md). Odmítne cestu mimo projekt, ze storage/, retired/, tools/lib, uploads/, V1/ a neexistující soubor.
 * Konec: V69_RETIRE_DRYRUN | V69_RETIRE_OK sha256=<8 znaků> | V69_RETIRE_REFUSED reason=… (exit 2) | V69_RETIRE_FAIL (exit 1).
 */

/** Schválené soubory (v69: skryté učitelské záložky a režim One Task). @var list<string> */
const RETIRE69_APPROVED = [
    'teacher_skill_views.php',
    'app/views/one_task.php',
    'app/actions/one_task.php',
    'one_task_v50_5.php',
    'assets/one-task-v50-5.css',
    'assets/one-task-v50-5.js',
    'assets/one-task-v50-7-7.css',
    'assets/student-dark-v68.css',   // nahrazeno po pohledech: assets/dark/student-dark-<pohled>-v69.css (tools/v69_dark_overlay.php)
];

/** Audity přesouvané do tools/legacy/ (testují vyřazené funkce). @var list<string> */
const RETIRE69_LEGACY = [
    'tools/v50_5_one_task_audit.php',
];

function retire69_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

function retire69_refuse(string $reason): int
{
    fwrite(STDERR, "V69_RETIRE_REFUSED reason=$reason\n");
    return 2;
}

/** Zkontroluje cestu; vrací null nebo důvod odmítnutí. */
function retire69_path_problem(string $rel): ?string
{
    if ($rel === '' || str_contains($rel, '..') || str_starts_with($rel, '/') || preg_match('/^[A-Za-z]:/', $rel) === 1 || str_contains($rel, chr(92))) return 'bad_path';
    foreach (['storage/', 'retired/', 'tools/lib/', 'tools/legacy/', 'uploads/', 'V1/', 'lab-runtime/'] as $forbidden) if (str_starts_with($rel, $forbidden)) return 'forbidden_dir';
    return null;
}

/** Cíl kopie podle seznamu: retired/v69/<cesta> nebo tools/legacy/<název>. */
function retire69_destination(string $rel, array $legacy): string
{
    return in_array($rel, $legacy, true) ? 'tools/legacy/' . basename($rel) : 'retired/v69/' . $rel;
}

/**
 * Vyřadí jeden soubor. $approved / $legacy = seznamy schválených cest (v testu se dosazují, v ostrém běhu konstanty).
 * @return array{ok:bool,reason:?string,sha:?string}
 */
function retire69_apply(string $root, string $rel, array $approved, array $legacy, bool $apply): array
{
    if (($problem = retire69_path_problem($rel)) !== null) return ['ok' => false, 'reason' => $problem, 'sha' => null];
    if (!in_array($rel, $approved, true) && !in_array($rel, $legacy, true)) return ['ok' => false, 'reason' => 'not_approved', 'sha' => null];
    $src = rtrim($root, '/') . '/' . $rel;
    if (!is_file($src)) return ['ok' => false, 'reason' => 'missing', 'sha' => null];
    $sha = (string)hash_file('sha256', $src);
    if (!$apply) return ['ok' => true, 'reason' => 'dryrun', 'sha' => $sha];
    $dst = rtrim($root, '/') . '/' . retire69_destination($rel, $legacy);
    if (is_file($dst)) return ['ok' => false, 'reason' => 'destination_exists', 'sha' => null];
    if (!is_dir(dirname($dst)) && !mkdir(dirname($dst), 0775, true) && !is_dir(dirname($dst))) return ['ok' => false, 'reason' => 'mkdir_failed', 'sha' => null];
    if (!copy($src, $dst)) return ['ok' => false, 'reason' => 'copy_failed', 'sha' => null];
    if (!hash_equals($sha, (string)hash_file('sha256', $dst))) {
        @unlink($dst);
        return ['ok' => false, 'reason' => 'hash_mismatch', 'sha' => null];
    }
    if (!unlink($src)) return ['ok' => false, 'reason' => 'unlink_failed', 'sha' => $sha];
    return ['ok' => true, 'reason' => null, 'sha' => $sha];
}

function retire69_main(array $argv): int
{
    $root = str_replace(chr(92), '/', retire69_arg($argv, 'root') ?? dirname(__DIR__));
    $rel = retire69_arg($argv, 'file');
    if ($rel === null) return retire69_refuse('missing_file');
    $apply = in_array('--apply', $argv, true);
    $res = retire69_apply($root, $rel, RETIRE69_APPROVED, RETIRE69_LEGACY, $apply);
    if (!$res['ok']) {
        if ($res['reason'] === 'unlink_failed') { fwrite(STDERR, "V69_RETIRE_FAIL kopie je uložena, originál se nepodařilo smazat\n"); return 1; }
        return retire69_refuse((string)$res['reason']);
    }
    if (!$apply) { echo 'V69_RETIRE_DRYRUN sha256=' . substr((string)$res['sha'], 0, 8) . " (přidej --apply; soubor musí být v seznamu)\n"; return 0; }
    echo 'V69_RETIRE_OK sha256=' . substr((string)$res['sha'], 0, 8) . "\n";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(retire69_main($argv));
