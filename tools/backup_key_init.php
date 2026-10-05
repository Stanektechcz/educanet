<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v67 · tools/backup_key_init.php – vygeneruje klíč šifrovaných záloh (`backup_key`) do souboru secrets.
 *
 *   php tools/backup_key_init.php [--secrets-file=<cesta>] [--create] [--apply]
 *
 * Bez --apply jen vypíše, co by se stalo (nic nezapíše). S --apply: 32 náhodných bajtů (sodium), uloží se base64
 * do souboru secrets pod `backup_key`, práva souboru 0600, zápis atomicky (dočasný soubor + rename).
 * Existující neprázdný klíč se NIKDY nepřepíše (konec REFUSED). Výstup obsahuje jen 8 znaků SHA-256 otisku klíče
 * (k porovnání s uloženou kopií v trezoru) – klíč samotný se nevypíše, nezaloguje ani neposílá nikam.
 * Cesta k secrets: --secrets-file, jinak stejná jako v bootstrap.php (EDUCANET_SECRETS_FILE / výchozí umístění).
 * Konec: BACKUP_KEY_INIT_OK fingerprint=xxxxxxxx | BACKUP_KEY_INIT_DRYRUN | BACKUP_KEY_INIT_REFUSED reason=… | BACKUP_KEY_INIT_FAIL.
 * Klíč si po vytvoření uschovej mimo server (docs/ZALOHY_V61.md §1); bez něj nejdou šifrované zálohy obnovit.
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/lib/backup_crypto_v61.php';

function bki67_arg(array $argv, string $name): ?string
{
    foreach ($argv as $a) if (str_starts_with((string)$a, '--' . $name . '=')) return substr((string)$a, strlen($name) + 3);
    return null;
}

function bki67_refuse(string $reason): int
{
    fwrite(STDERR, "BACKUP_KEY_INIT_REFUSED reason=$reason\n");
    return 2;
}

/** Zapíše pole secrets atomicky s právy 0600. */
function bki67_write(string $path, array $secrets): bool
{
    $tmp = $path . '.tmp-' . bin2hex(random_bytes(4));
    $code = "<?php\n// Tajemství EDUCANET – nikdy nedávej do gitu ani do veřejného kořene webu.\nreturn " . var_export($secrets, true) . ";\n";
    if (file_put_contents($tmp, $code, LOCK_EX) === false) return false;
    @chmod($tmp, 0600);
    if (!@rename($tmp, $path)) { @unlink($tmp); return false; }
    @chmod($path, 0600);
    return true;
}

function bki67_main(array $argv): int
{
    if (!bkc61_available()) return bki67_refuse('no_sodium');
    $path = bki67_arg($argv, 'secrets-file') ?? educanet_recommended_secret_path();
    $secrets = [];
    if (is_file($path)) {
        $loaded = require $path;
        if (!is_array($loaded)) return bki67_refuse('secrets_not_array');
        $secrets = $loaded;
    } elseif (!in_array('--create', $argv, true)) {
        return bki67_refuse('secrets_missing_use_create');
    } elseif (!is_dir(dirname($path))) {
        return bki67_refuse('secrets_dir_missing');
    }
    if (trim((string)($secrets['backup_key'] ?? '')) !== '') return bki67_refuse('key_exists');
    if (!in_array('--apply', $argv, true)) {
        echo "BACKUP_KEY_INIT_DRYRUN would_write=1 (přidej --apply)\n";
        return 0;
    }
    $raw = random_bytes(SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES);
    $secrets['backup_key'] = base64_encode($raw);
    if (!bki67_write($path, $secrets)) {
        fwrite(STDERR, "BACKUP_KEY_INIT_FAIL zápis souboru secrets se nepodařil\n");
        return 1;
    }
    $fingerprint = substr(hash('sha256', $raw), 0, 8);
    sodium_memzero($raw);
    echo "BACKUP_KEY_INIT_OK fingerprint=$fingerprint\n";
    return 0;
}

if (basename((string)($argv[0] ?? '')) === basename(__FILE__)) exit(bki67_main($argv));
