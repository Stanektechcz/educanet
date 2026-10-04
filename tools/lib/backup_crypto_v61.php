<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

/**
 * EDUCANET v61 · šifrované zálohy (sodium secretstream XChaCha20-Poly1305).
 *
 * Archiv `.edubak` = hlavička "EDUBAK61\n" + hlavička secretstreamu (24 B) + rámce [u32 délka][šifrovaný blok].
 * Otevřený obsah (po rozšifrování) je jednoduchý kontejner: opakovaně [u16 délka cesty][cesta][u64 velikost][data],
 * konec = délka cesty 0. Cesty jsou relativní k adresáři zálohy (manifest.json, storage/…, uploads/…).
 * Každý blok nese jako přidaná data značku formátu; poslední blok má značku FINAL, takže zkrácený archiv se pozná.
 *
 * Klíč (32 B) je tajemství `backup_key` (base64) mimo web a mimo zálohu. Žádná funkce klíč nevypisuje ani nelogují.
 */

const BKC61_MAGIC = "EDUBAK61\n";
const BKC61_AD = 'EDUBAK61';
const BKC61_CHUNK = 65536;
const BKC61_MAX_CIPHER_CHUNK = 65536 + 64;

function bkc61_available(): bool
{
    return extension_loaded('sodium') && function_exists('sodium_crypto_secretstream_xchacha20poly1305_init_push');
}

function bkc61_require_sodium(): void
{
    if (!bkc61_available()) {
        throw new RuntimeException('Šifrování záloh vyžaduje PHP rozšíření sodium (php -m | grep sodium).');
    }
}

/** Dekóduje base64 klíč; chybný tvar = výjimka bez obsahu klíče. */
function bkc61_decode_key(string $b64): string
{
    bkc61_require_sodium();
    $raw = base64_decode(trim($b64), true);
    if ($raw === false || strlen($raw) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES) {
        throw new RuntimeException('Tajemství backup_key musí být base64 řetězec s přesně 32 bajty.');
    }
    return $raw;
}

/** Klíč ze secrets (`backup_key`); chybí = výjimka (žádný nešifrovaný fallback). */
function bkc61_key_from_secrets(): string
{
    $b64 = function_exists('educanet_secret') ? educanet_secret('backup_key') : '';
    if ($b64 === '') {
        throw new RuntimeException('Chybí tajemství backup_key (base64 32 B) v souboru secrets – šifrovaná záloha nelze vytvořit ani rozšifrovat.');
    }
    return bkc61_decode_key($b64);
}

function bkc61_has_key(): bool
{
    return function_exists('educanet_secret') && educanet_secret('backup_key') !== '';
}

/** Zapisovatel šifrovaného streamu. */
final class Bkc61Writer
{
    /** @var resource */
    private $fh;
    private mixed $state;
    private string $buf = '';

    public function __construct(string $path, string $key)
    {
        bkc61_require_sodium();
        $fh = fopen($path, 'xb');
        if ($fh === false) throw new RuntimeException('Nelze vytvořit archiv ' . basename($path) . '.');
        $this->fh = $fh;
        [$this->state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        fwrite($this->fh, BKC61_MAGIC . $header);
    }

    public function write(string $data): void
    {
        $this->buf .= $data;
        while (strlen($this->buf) >= BKC61_CHUNK) {
            $this->emit(substr($this->buf, 0, BKC61_CHUNK), SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE);
            $this->buf = substr($this->buf, BKC61_CHUNK);
        }
    }

    public function finish(): void
    {
        $this->emit($this->buf, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL);
        $this->buf = '';
        fflush($this->fh);
        fclose($this->fh);
    }

    public function abort(): void
    {
        if (is_resource($this->fh)) fclose($this->fh);
    }

    private function emit(string $plain, int $tag): void
    {
        $cipher = sodium_crypto_secretstream_xchacha20poly1305_push($this->state, $plain, BKC61_AD, $tag);
        if (fwrite($this->fh, pack('N', strlen($cipher)) . $cipher) === false) throw new RuntimeException('Chyba zápisu archivu.');
    }
}

/** Čtenář šifrovaného streamu; po konci dat vyžaduje značku FINAL. */
final class Bkc61Reader
{
    /** @var resource */
    private $fh;
    private mixed $state;
    private string $buf = '';
    private bool $final = false;

    public function __construct(string $path, string $key)
    {
        bkc61_require_sodium();
        $fh = fopen($path, 'rb');
        if ($fh === false) throw new RuntimeException('Nelze otevřít archiv ' . basename($path) . '.');
        $this->fh = $fh;
        $magic = (string)fread($this->fh, strlen(BKC61_MAGIC));
        $header = (string)fread($this->fh, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES);
        if ($magic !== BKC61_MAGIC || strlen($header) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES) {
            throw new RuntimeException('Soubor není šifrovaný archiv EDUCANET (.edubak).');
        }
        try {
            $this->state = sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
        } catch (Throwable $e) {
            throw new RuntimeException('Hlavička archivu je neplatná nebo klíč nesedí.');
        }
    }

    public function read(int $n): string
    {
        while (strlen($this->buf) < $n && !$this->final) $this->pull();
        if (strlen($this->buf) < $n) throw new RuntimeException('Archiv je zkrácený nebo poškozený.');
        $out = substr($this->buf, 0, $n);
        $this->buf = substr($this->buf, $n);
        return $out;
    }

    /** Po posledním záznamu: nesmí zbýt data a stream musí být řádně ukončen. */
    public function assertEnd(): void
    {
        while (!$this->final) $this->pull();
        if ($this->buf !== '' || !feof($this->fh) && fread($this->fh, 1) !== '') throw new RuntimeException('Archiv obsahuje nadbytečná data.');
    }

    public function close(): void
    {
        if (is_resource($this->fh)) fclose($this->fh);
    }

    private function pull(): void
    {
        $len = fread($this->fh, 4);
        if ($len === false || strlen($len) !== 4) throw new RuntimeException('Archiv je zkrácený (chybí závěrečný blok).');
        $size = (int)unpack('N', $len)[1];
        if ($size < SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES || $size > BKC61_MAX_CIPHER_CHUNK) throw new RuntimeException('Archiv je poškozený (délka bloku).');
        $cipher = (string)fread($this->fh, $size);
        if (strlen($cipher) !== $size) throw new RuntimeException('Archiv je zkrácený.');
        $res = sodium_crypto_secretstream_xchacha20poly1305_pull($this->state, $cipher, BKC61_AD);
        if ($res === false) throw new RuntimeException('Dešifrování selhalo (špatný klíč nebo poškozený archiv).');
        $this->buf .= $res[0];
        if ($res[1] === SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL) $this->final = true;
    }
}

/** Relativní cesty všech souborů v adresáři zálohy (manifest.json první), seřazené. @return list<string> */
function bkc61_list_files(string $dir): array
{
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile() && !$f->isLink()) {
            $out[] = ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen($dir))), '/');
        }
    }
    sort($out);
    usort($out, static fn(string $a, string $b): int => ($b === 'manifest.json') <=> ($a === 'manifest.json'));
    return $out;
}

/** Cesta v archivu: jen manifest.json, storage/…, uploads/… ze znaků [A-Za-z0-9_./-], bez „..“. */
function bkc61_valid_entry(string $rel): bool
{
    if ($rel === 'manifest.json') return true;
    if (strlen($rel) > 400 || !preg_match('#^(storage|uploads)/[A-Za-z0-9_./-]+$#', $rel)) return false;
    foreach (explode('/', $rel) as $seg) {
        if ($seg === '' || $seg === '.' || $seg === '..') return false;
    }
    return true;
}

/**
 * Zašifruje adresář zálohy do $destFile (musí neexistovat).
 * @return array{files:int,bytes:int,sha256:string} sha256 = otisk samotného šifrovaného souboru
 */
function bkc61_encrypt_dir(string $srcDir, string $destFile, string $key): array
{
    $w = new Bkc61Writer($destFile, $key);
    $files = 0;
    $bytes = 0;
    try {
        foreach (bkc61_list_files($srcDir) as $rel) {
            if (!bkc61_valid_entry($rel)) throw new RuntimeException('Soubor s nepovoleným názvem v záloze: ' . $rel);
            $path = $srcDir . '/' . $rel;
            $size = (int)filesize($path);
            $w->write(pack('n', strlen($rel)) . $rel . pack('J', $size));
            $in = fopen($path, 'rb');
            if ($in === false) throw new RuntimeException('Nelze číst ' . $rel);
            try {
                while (!feof($in)) {
                    $chunk = fread($in, BKC61_CHUNK);
                    if ($chunk === false) throw new RuntimeException('Chyba čtení ' . $rel);
                    $w->write($chunk);
                }
            } finally {
                fclose($in);
            }
            $files++;
            $bytes += $size;
        }
        $w->write(pack('n', 0));
        $w->finish();
    } catch (Throwable $e) {
        $w->abort();
        @unlink($destFile);
        throw $e;
    }
    $sha = hash_file('sha256', $destFile);
    if ($sha === false) throw new RuntimeException('Nelze spočítat otisk archivu.');
    return ['files' => $files, 'bytes' => $bytes, 'sha256' => $sha];
}

/**
 * Rozšifruje archiv do prázdného adresáře $outDir (musí existovat). Špatný klíč / poškození / zkrácení = výjimka.
 * @return int počet rozbalených souborů
 */
function bkc61_decrypt_to_dir(string $archive, string $outDir, string $key): int
{
    $r = new Bkc61Reader($archive, $key);
    $count = 0;
    try {
        while (true) {
            $len = (int)unpack('n', $r->read(2))[1];
            if ($len === 0) break;
            $rel = $r->read($len);
            if (!bkc61_valid_entry($rel)) throw new RuntimeException('Archiv obsahuje nepovolenou cestu.');
            $size = (int)unpack('J', $r->read(8))[1];
            $target = $outDir . '/' . $rel;
            $dir = dirname($target);
            if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('Nelze vytvořit adresář pro ' . $rel);
            $out = fopen($target, 'xb');
            if ($out === false) throw new RuntimeException('Duplicitní nebo nezapisovatelný soubor v archivu.');
            try {
                for ($left = $size; $left > 0; $left -= $take) {
                    $take = min($left, BKC61_CHUNK);
                    fwrite($out, $r->read($take));
                }
            } finally {
                fclose($out);
            }
            $count++;
        }
        $r->assertEnd();
    } finally {
        $r->close();
    }
    return $count;
}

/** Ověří rozbalenou zálohu proti manifestu (existence + SHA-256 všech souborů). @return int počet ověřených souborů */
function bkc61_verify_manifest(string $dir): int
{
    $raw = @file_get_contents($dir . '/manifest.json');
    $m = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($m) || !is_array($m['files'] ?? null)) throw new RuntimeException('Manifest zálohy chybí nebo je neplatný.');
    $n = 0;
    foreach ($m['files'] as $entry) {
        $rel = is_array($entry) ? (string)($entry['path'] ?? '') : '';
        $abs = (str_starts_with($rel, 'uploads/') ? '' : 'storage/') . $rel;
        if (!bkc61_valid_entry($abs)) throw new RuntimeException('Neplatná cesta v manifestu.');
        $hash = @hash_file('sha256', $dir . '/' . $abs);
        if ($hash === false || !hash_equals((string)($entry['sha256'] ?? ''), $hash)) throw new RuntimeException('Otisk SHA-256 nesouhlasí: ' . $rel);
        $n++;
    }
    return $n;
}

/** Archivy `storage-*.edubak` v cíli, nejstarší první. @return list<string> */
function bkc61_list_archives(string $destRoot): array
{
    $list = glob(rtrim($destRoot, '/\\') . '/storage-*.edubak') ?: [];
    sort($list);
    return $list;
}

/** Ponechá posledních $keep šifrovaných archivů (i s .sha256); jiné soubory nemaže. @return int smazaných */
function bkc61_rotate(string $destRoot, int $keep): int
{
    if ($keep <= 0) return 0;
    $list = bkc61_list_archives($destRoot);
    $excess = count($list) - $keep;
    for ($i = 0; $i < $excess; $i++) {
        @unlink($list[$i]);
        @unlink($list[$i] . '.sha256');
    }
    return max(0, $excess);
}
