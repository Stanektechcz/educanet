<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v68 · tools/v68_retire.php – bezpečné vyřazení jednoho souboru: kopie do retired/v68/ → ověření SHA-256 → smazání originálu.
 *
 *   php tools/v68_retire.php --file=<relativní cesta> [--apply] [--root=<kořen projektu>]
 *
 * Výchozí je --dry-run (nic se nemění, jen se vypíše, co by se stalo). --apply zpracuje VŽDY JEDEN soubor a jen takový, který je
 * v seznamu RETIRE68_APPROVED níže. Seznam schválil uživatel (v68: zadání vyřazení zastaralých stránek); soubor mimo seznam skončí odmítnutím. Žádná shellová smyčka, žádné hromadné mazání (viz AUDIT_V59.md).
 * Odmítne cestu mimo projekt, ze storage/, z retired/, z tools/lib a soubor, který neexistuje. Přesměrování starých URL se řeší až
 * s vyřazením (samostatný krok), tento nástroj žádné nevytváří.
 * Konec: V68_RETIRE_DRYRUN | V68_RETIRE_OK sha256=<8 znaků> | V68_RETIRE_REFUSED reason=… (exit 2) | V68_RETIRE_FAIL (exit 1).
 */

/** Schválené soubory (relativní cesty). v68: jen pohled v48_state (route → 302 na přehled). @var list<string> */
const RETIRE68_APPROVED = ["app/views/v48_state.php"];

function retire68_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

function retire68_refuse(string $reason): int
{
    fwrite(STDERR, "V68_RETIRE_REFUSED reason=$reason\n");
    return 2;
}

/** Zkontroluje cestu; vrací null nebo důvod odmítnutí. */
function retire68_path_problem(string $rel): ?string
{
    if ($rel === '' || str_contains($rel, '..') || str_starts_with($rel, '/') || preg_match('/^[A-Za-z]:/', $rel) === 1 || str_contains($rel, chr(92))) return 'bad_path';
    foreach (['storage/', 'retired/', 'tools/lib/', 'uploads/', 'V1/', 'lab-runtime/'] as $forbidden) if (str_starts_with($rel, $forbidden)) return 'forbidden_dir';
    return null;
}

/**
 * Vyřadí jeden soubor. $approved = seznam schválených cest (v testu se dosazuje, v ostrém běhu RETIRE68_APPROVED).
 * @return array{ok:bool,reason:?string,sha:?string}
 */
function retire68_apply(string $root, string $rel, array $approved, bool $apply): array
{
    if (($problem = retire68_path_problem($rel)) !== null) return ['ok' => false, 'reason' => $problem, 'sha' => null];
    if (!in_array($rel, $approved, true)) return ['ok' => false, 'reason' => 'not_approved', 'sha' => null];
    $src = rtrim($root, '/') . '/' . $rel;
    if (!is_file($src)) return ['ok' => false, 'reason' => 'missing', 'sha' => null];
    $sha = (string)hash_file('sha256', $src);
    if (!$apply) return ['ok' => true, 'reason' => 'dryrun', 'sha' => $sha];
    $dst = rtrim($root, '/') . '/retired/v68/' . $rel;
    if (!is_dir(dirname($dst)) && !mkdir(dirname($dst), 0775, true) && !is_dir(dirname($dst))) return ['ok' => false, 'reason' => 'mkdir_failed', 'sha' => null];
    if (!copy($src, $dst)) return ['ok' => false, 'reason' => 'copy_failed', 'sha' => null];
    if (!hash_equals($sha, (string)hash_file('sha256', $dst))) {
        @unlink($dst);
        return ['ok' => false, 'reason' => 'hash_mismatch', 'sha' => null];
    }
    if (!unlink($src)) return ['ok' => false, 'reason' => 'unlink_failed', 'sha' => $sha];
    return ['ok' => true, 'reason' => null, 'sha' => $sha];
}

function retire68_main(array $argv): int
{
    $root = str_replace(chr(92), '/', retire68_arg($argv, 'root') ?? dirname(__DIR__));
    $rel = retire68_arg($argv, 'file');
    if ($rel === null) return retire68_refuse('missing_file');
    $apply = in_array('--apply', $argv, true);
    $res = retire68_apply($root, $rel, RETIRE68_APPROVED, $apply);
    if (!$res['ok']) {
        if ($res['reason'] === 'unlink_failed') { fwrite(STDERR, "V68_RETIRE_FAIL kopie je v retired/v68/, originál se nepodařilo smazat\n"); return 1; }
        return retire68_refuse((string)$res['reason']);
    }
    if (!$apply) { echo 'V68_RETIRE_DRYRUN sha256=' . substr((string)$res['sha'], 0, 8) . " (přidej --apply; soubor musí být v RETIRE68_APPROVED)\n"; return 0; }
    echo 'V68_RETIRE_OK sha256=' . substr((string)$res['sha'], 0, 8) . "\n";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(retire68_main($argv));
