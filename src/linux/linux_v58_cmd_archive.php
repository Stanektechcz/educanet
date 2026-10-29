<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – archivy a komprese (LAB-03).
 *
 * tar, gzip/gunzip/zcat, bzip2/bunzip2/bzcat, zip/unzip. Archivy jsou binární data
 * ve virtuálním souborovém systému se skutečnými magickými bajty (gzip \x1f\x8b,
 * bzip2 „BZh“, zip „PK\x03\x04“, tar „ustar“), aby je poznaly file/xxd/strings/head -c.
 *
 * Vše je jen zpracování bajtů v paměti (zlib gzdeflate/gzinflate) – nic se nespouští,
 * žádná síť. Formát je deterministický, takže round-trip a audit dávají stále stejný výsledek.
 * bzip2 je věrná obdoba (rozšíření bz2 nemusí být dostupné): magické bajty a round-trip sedí,
 * tělo je komprimované stejným deflaterem jako gzip.
 */

// ---------------------------------------------------------------------------
// Komprese: deflate/inflate (zlib je standardní; když chybí, použij stored bloky)
// ---------------------------------------------------------------------------

function lab58_arc_deflate(string $data): string
{
    if (function_exists('gzdeflate')) {
        $out = @gzdeflate($data, 6);
        if ($out !== false) return $out;
    }
    return lab58_arc_deflate_stored($data);
}

function lab58_arc_inflate(string $data): ?string
{
    if (function_exists('gzinflate')) {
        $out = @gzinflate($data);
        if ($out !== false) return $out;
    }
    return lab58_arc_inflate_stored($data);
}

/** Nekomprimované („stored“) DEFLATE bloky – platný proud i bez zlib. */
function lab58_arc_deflate_stored(string $data): string
{
    $out = '';
    $len = strlen($data);
    if ($len === 0) return "\x01\x00\x00\xff\xff";
    for ($i = 0; $i < $len; $i += 65535) {
        $chunk = substr($data, $i, 65535);
        $n = strlen($chunk);
        $final = ($i + 65535 >= $len) ? "\x01" : "\x00";
        $out .= $final . pack('v', $n) . pack('v', $n ^ 0xFFFF) . $chunk;
    }
    return $out;
}

function lab58_arc_inflate_stored(string $data): ?string
{
    $out = '';
    $pos = 0;
    $n = strlen($data);
    while ($pos < $n) {
        $flag = ord($data[$pos]);
        $pos++;
        if (($flag & 0x06) !== 0) return null; // jen stored bloky
        if ($pos + 4 > $n) return null;
        $len = unpack('v', substr($data, $pos, 2))[1];
        $pos += 4;
        $out .= substr($data, $pos, $len);
        $pos += $len;
        if (($flag & 0x01) === 1) break;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// gzip kontejner (RFC 1952) – deterministický (mtime = 0)
// ---------------------------------------------------------------------------

function lab58_arc_gzip_pack(string $data, string $name = ''): string
{
    $flg = $name !== '' ? "\x08" : "\x00";
    $header = "\x1f\x8b\x08" . $flg . "\x00\x00\x00\x00" . "\x00\x03";
    if ($name !== '') $header .= basename($name) . "\x00";
    return $header . lab58_arc_deflate($data) . pack('V', crc32($data)) . pack('V', strlen($data) % 4294967296);
}

/** @return array{data:string,name:?string}|null */
function lab58_arc_gzip_unpack(string $raw): ?array
{
    if (strlen($raw) < 18 || substr($raw, 0, 3) !== "\x1f\x8b\x08") return null;
    $flg = ord($raw[3]);
    $pos = 10;
    $name = null;
    if ($flg & 0x04) { // FEXTRA
        if ($pos + 2 > strlen($raw)) return null;
        $xlen = unpack('v', substr($raw, $pos, 2))[1];
        $pos += 2 + $xlen;
    }
    if ($flg & 0x08) { // FNAME
        $end = strpos($raw, "\x00", $pos);
        if ($end === false) return null;
        $name = substr($raw, $pos, $end - $pos);
        $pos = $end + 1;
    }
    if ($flg & 0x10) { // FCOMMENT
        $end = strpos($raw, "\x00", $pos);
        if ($end === false) return null;
        $pos = $end + 1;
    }
    if ($flg & 0x02) $pos += 2; // FHCRC
    $body = substr($raw, $pos, strlen($raw) - $pos - 8);
    $data = lab58_arc_inflate($body);
    return $data === null ? null : ['data' => $data, 'name' => $name];
}

// ---------------------------------------------------------------------------
// bzip2 (obdoba – magické bajty + deflate tělo, deterministické)
// ---------------------------------------------------------------------------

function lab58_arc_bzip2_pack(string $data): string
{
    return "BZh9" . "\x31\x41\x59\x26\x53\x59" . pack('N', crc32($data)) . lab58_arc_deflate($data);
}

function lab58_arc_bzip2_unpack(string $raw): ?string
{
    if (strlen($raw) < 14 || substr($raw, 0, 3) !== 'BZh') return null;
    return lab58_arc_inflate(substr($raw, 14));
}

// ---------------------------------------------------------------------------
// tar (POSIX ustar) – skutečné 512bajtové bloky
// ---------------------------------------------------------------------------

function lab58_arc_tar_header(string $name, int $mode, int $size, int $mtime, string $type, string $uname, string $gname): string
{
    $h = str_pad(substr($name, 0, 100), 100, "\x00");
    $h .= sprintf('%06o ', $mode & 07777) . "\x00";      // mode 8
    $h .= sprintf('%06o ', 1000) . "\x00";                // uid 8
    $h .= sprintf('%06o ', 1000) . "\x00";                // gid 8
    $h .= sprintf('%011o', $size) . ' ';                  // size 12
    $h .= sprintf('%011o', $mtime) . ' ';                 // mtime 12
    $h .= '        ';                                      // chksum placeholder (8 spaces)
    $h .= $type;                                           // typeflag 1
    $h .= str_repeat("\x00", 100);                        // linkname 100
    $h .= "ustar\x0000";                                  // magic+version 8
    $h .= str_pad(substr($uname, 0, 31), 32, "\x00");    // uname 32
    $h .= str_pad(substr($gname, 0, 31), 32, "\x00");    // gname 32
    $h .= str_repeat("\x00", 8) . str_repeat("\x00", 8); // dev major/minor 16
    $h .= str_repeat("\x00", 155);                        // prefix 155
    $h .= str_repeat("\x00", 12);                         // padding 12
    $sum = 0;
    for ($i = 0, $n = strlen($h); $i < $n; $i++) $sum += ord($h[$i]);
    $chk = sprintf('%06o', $sum) . "\x00 ";
    return substr_replace($h, $chk, 148, 8);
}

/** @param list<array{name:string,mode:int,size:int,mtime:int,type:string,uname:string,gname:string,content:string}> $members */
function lab58_arc_tar_pack(array $members): string
{
    $out = '';
    foreach ($members as $m) {
        $size = $m['type'] === '5' ? 0 : strlen((string)$m['content']); // velikost vždy podle obsahu
        $out .= lab58_arc_tar_header($m['name'], $m['mode'], $size, $m['mtime'], $m['type'], (string)($m['uname'] ?? 'student'), (string)($m['gname'] ?? 'student'));
        if ($m['type'] !== '5') {
            $out .= $m['content'];
            $pad = (512 - strlen($m['content']) % 512) % 512;
            if ($pad > 0) $out .= str_repeat("\x00", $pad);
        }
    }
    return $out . str_repeat("\x00", 1024);
}

/** @return list<array{name:string,mode:int,size:int,mtime:int,type:string,content:string}> */
function lab58_arc_tar_unpack(string $raw): array
{
    $out = [];
    $pos = 0;
    $n = strlen($raw);
    while ($pos + 512 <= $n) {
        $header = substr($raw, $pos, 512);
        if (trim($header, "\x00") === '') break;
        if (substr($header, 257, 5) !== 'ustar') { $pos += 512; continue; }
        $name = rtrim(substr($header, 0, 100), "\x00");
        $mode = (int)octdec(trim(substr($header, 100, 8)));
        $size = (int)octdec(trim(substr($header, 124, 12)));
        $mtime = (int)octdec(trim(substr($header, 136, 12)));
        $type = substr($header, 156, 1);
        $pos += 512;
        $content = $type === '5' ? '' : substr($raw, $pos, $size);
        if ($type !== '5') $pos += (int)(ceil($size / 512) * 512);
        $out[] = ['name' => $name, 'mode' => $mode ?: ($type === '5' ? 0755 : 0644), 'size' => $size, 'mtime' => $mtime, 'type' => $type === '' ? '0' : $type, 'content' => $content];
    }
    return $out;
}

// ---------------------------------------------------------------------------
// zip (PK) – lokální hlavičky + central directory + EOCD, metoda deflate
// ---------------------------------------------------------------------------

/** @param list<array{name:string,mtime:int,content:string,dir:bool}> $members */
function lab58_arc_zip_pack(array $members): string
{
    $local = '';
    $central = '';
    $offset = 0;
    foreach ($members as $m) {
        $name = $m['dir'] ? rtrim($m['name'], '/') . '/' : $m['name'];
        $data = (string)$m['content'];
        $crc = crc32($data);
        $comp = $m['dir'] || $data === '' ? '' : lab58_arc_deflate($data);
        $method = ($m['dir'] || $data === '') ? 0 : 8;
        $lh = "PK\x03\x04" . pack('v', 20) . pack('v', 0) . pack('v', $method) . pack('v', 0) . pack('v', 0x21)
            . pack('V', $crc) . pack('V', strlen($comp)) . pack('V', strlen($data)) . pack('v', strlen($name)) . pack('v', 0) . $name . $comp;
        $ext = ($m['dir'] ? 0x10 : 0);
        $cd = "PK\x01\x02" . pack('v', 0x031E) . pack('v', 20) . pack('v', 0) . pack('v', $method) . pack('v', 0) . pack('v', 0x21)
            . pack('V', $crc) . pack('V', strlen($comp)) . pack('V', strlen($data)) . pack('v', strlen($name)) . pack('v', 0) . pack('v', 0)
            . pack('v', 0) . pack('v', 0) . pack('V', $ext) . pack('V', $offset) . $name;
        $local .= $lh;
        $central .= $cd;
        $offset += strlen($lh);
    }
    $eocd = "PK\x05\x06" . pack('v', 0) . pack('v', 0) . pack('v', count($members)) . pack('v', count($members))
        . pack('V', strlen($central)) . pack('V', strlen($local)) . pack('v', 0);
    return $local . $central . $eocd;
}

/** @return list<array{name:string,size:int,content:string,dir:bool}> */
function lab58_arc_zip_unpack(string $raw): array
{
    $out = [];
    $pos = 0;
    while (($pos = strpos($raw, "PK\x03\x04", $pos)) !== false) {
        $method = unpack('v', substr($raw, $pos + 8, 2))[1];
        $compSize = unpack('V', substr($raw, $pos + 18, 4))[1];
        $uncompSize = unpack('V', substr($raw, $pos + 22, 4))[1];
        $nameLen = unpack('v', substr($raw, $pos + 26, 2))[1];
        $extraLen = unpack('v', substr($raw, $pos + 28, 2))[1];
        $name = substr($raw, $pos + 30, $nameLen);
        $dataStart = $pos + 30 + $nameLen + $extraLen;
        $comp = substr($raw, $dataStart, $compSize);
        $data = $method === 8 ? (lab58_arc_inflate($comp) ?? '') : $comp;
        $dir = str_ends_with($name, '/');
        $out[] = ['name' => $name, 'size' => $dir ? 0 : ($uncompSize ?: strlen($data)), 'content' => $dir ? '' : $data, 'dir' => $dir];
        $pos = $dataStart + $compSize;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Detekce formátu (pro tar auto-rozbalení a filtr file_type)
// ---------------------------------------------------------------------------

function lab58_arc_detect(string $c): ?string
{
    if (str_starts_with($c, "\x1f\x8b")) return 'gzip';
    if (str_starts_with($c, 'BZh')) return 'bzip2';
    if (str_starts_with($c, "PK\x03\x04") || str_starts_with($c, "PK\x05\x06")) return 'zip';
    if (strlen($c) >= 512 && substr($c, 257, 5) === 'ustar') return 'tar';
    return null;
}

// ---------------------------------------------------------------------------
// Pomocníci: čtení a zápis do VFS, výběr souborů pro archiv
// ---------------------------------------------------------------------------

function lab58_arc_read(Lab57Proc $p, string $path, ?string &$err = null): ?string
{
    return $p->w->readFile($path, $err);
}

function lab58_arc_write(Lab57Proc $p, string $path, string $data, ?string &$err = null): bool
{
    return $p->w->writeFile($path, $data, false, $err);
}

/** @return list<array{name:string,mode:int,size:int,mtime:int,type:string,uname:string,gname:string,content:string}> */
function lab58_arc_collect(Lab57World $w, array $paths, string $base, ?string &$err = null): ?array
{
    $members = [];
    foreach ($paths as $path) {
        $abs = $w->abs($path);
        $node = $w->fs->get($abs);
        if ($node === null || !$w->canTraverse($abs)) { $err = $path . ': Cannot stat: No such file or directory'; return null; }
        foreach ($w->fs->tree($abs) as $entry) {
            $n = $w->fs->get($entry);
            if ($n === null) continue;
            $rel = $base !== '' && str_starts_with($entry, $base . '/') ? substr($entry, strlen($base) + 1) : ltrim($path, './');
            if ($entry !== $abs) {
                $prefix = ($node['t'] ?? '') === 'd' ? rtrim($path, '/') : $path;
                $rel = $prefix . substr($entry, strlen($abs));
            } else {
                $rel = $path;
            }
            $rel = ltrim((string)preg_replace('#^/+#', '', $rel), './');
            if ($rel === '') $rel = Lab57Vfs::basename($abs);
            $isDir = ($n['t'] ?? '') === 'd';
            $members[] = [
                'name' => $rel . ($isDir ? '/' : ''), 'mode' => (int)($n['m'] ?? 0644), 'size' => Lab57Vfs::size($n),
                'mtime' => (int)($n['mt'] ?? $w->now), 'type' => $isDir ? '5' : '0',
                'uname' => (string)($n['u'] ?? 'student'), 'gname' => (string)($n['g'] ?? 'student'), 'content' => (string)($n['c'] ?? ''),
            ];
        }
    }
    return $members;
}

// ---------------------------------------------------------------------------
// tar
// ---------------------------------------------------------------------------

function lab58_cmd_tar(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = lab57_split_flags(array_slice($argv, 1), ['-f', '-C']);
    $mode = '';
    $verbose = false;
    $comp = '';
    $file = null;
    $chdir = null;
    $operands = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-f' || $a === '--file') { $file = (string)($args[++$i] ?? ''); continue; }
        if ($a === '-C' || $a === '--directory') { $chdir = (string)($args[++$i] ?? ''); continue; }
        if (str_starts_with($a, '-') && $a !== '-') {
            foreach (str_split(ltrim($a, '-')) as $ch) {
                if (in_array($ch, ['c', 'x', 't'], true)) $mode = $ch;
                elseif ($ch === 'v') $verbose = true;
                elseif ($ch === 'z') $comp = 'gzip';
                elseif ($ch === 'j') $comp = 'bzip2';
                elseif ($ch === 'f') { $file = (string)($args[++$i] ?? ''); }
                else { $p->err("tar: invalid option -- '$ch'\nTry 'tar --help' for more information.\n"); $w->tip(tr('tar používá kombinaci voleb: -c vytvoř, -x rozbal, -t vypiš, -z gzip, -j bzip2, -v ukaž, -f soubor.')); return 2; }
            }
            continue;
        }
        $operands[] = $a;
    }
    if ($mode === '') { $p->err("tar: You must specify one of the '-Acdtrux', '--delete' or '--test-label' options\nTry 'tar --help' or 'tar --usage' for more information.\n"); return 2; }
    if ($file === null || $file === '') { $p->err("tar: Refusing to read archive contents from terminal (missing -f option?)\ntar: Error is not recoverable: exiting now\n"); $w->tip(tr('Zadej jméno archivu volbou -f, např. tar -czf zaloha.tar.gz slozka.')); return 2; }

    if ($mode === 'c') {
        $members = lab58_arc_collect($w, $operands, $chdir !== null ? $w->abs($chdir) : '', $err);
        if ($members === null) { $p->err("tar: $err\ntar: Error is not recoverable: exiting now\n"); return 2; }
        if ($verbose) foreach ($members as $m) $p->line($m['name']);
        $raw = lab58_arc_tar_pack($members);
        if ($comp === 'gzip') $raw = lab58_arc_gzip_pack($raw);
        elseif ($comp === 'bzip2') $raw = lab58_arc_bzip2_pack($raw);
        $err = null;
        if (!lab58_arc_write($p, $file, $raw, $err)) { $p->err("tar: $file: Cannot open: $err\ntar: Error is not recoverable: exiting now\n"); return 2; }
        return 0;
    }

    // extract / list – auto-detekce komprese podle magických bajtů
    $raw = lab58_arc_read($p, $file, $err);
    if ($raw === null) { $p->err("tar: $file: Cannot open: $err\ntar: Error is not recoverable: exiting now\n"); lab57_error_tip($w, (string)$err, $file); return 2; }
    $fmt = lab58_arc_detect($raw);
    if ($fmt === 'gzip') { $u = lab58_arc_gzip_unpack($raw); $raw = $u['data'] ?? ''; }
    elseif ($fmt === 'bzip2') { $raw = (string)lab58_arc_bzip2_unpack($raw); }
    if (lab58_arc_detect($raw) !== 'tar') {
        $p->err("tar: This does not look like a tar archive\ntar: Exiting with failure status due to previous errors\n");
        $w->tip(tr('Soubor nevypadá jako tar. Zjisti formát příkazem file {file} a použij odpovídající nástroj.', ['file' => $file]));
        return 2;
    }
    $members = lab58_arc_tar_unpack($raw);
    $destBase = $chdir !== null ? $w->abs($chdir) : $w->cwd;
    $status = 0;
    foreach ($members as $m) {
        $name = $m['name'];
        if ($mode === 't') { $p->line($name); continue; }
        if ($verbose) $p->line($name);
        $target = Lab57Vfs::normalize($name, $destBase);
        if ($m['type'] === '5') { $w->mkdirp($target, $m['mode'] & 07777, $w->effectiveUser()); continue; }
        $w->mkdirp(Lab57Vfs::dirname($target), 0755, $w->effectiveUser());
        $err = null;
        if (!$w->writeFile($target, $m['content'], false, $err)) { $p->err("tar: $name: Cannot write: $err\n"); $status = 2; continue; }
        $node = $w->fs->get($target);
        if ($node !== null) { $node['m'] = $m['mode'] & 07777; $node['mt'] = $m['mtime'] ?: $w->now; $w->fs->set($target, $node); }
    }
    return $status;
}

// ---------------------------------------------------------------------------
// gzip / gunzip / zcat
// ---------------------------------------------------------------------------

function lab58_cmd_gzip(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $name = (string)$argv[0];
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'dkclrvf123456789', ['decompress' => false, 'keep' => false, 'stdout' => false, 'list' => false, 'force' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $decompress = $name === 'gunzip' || $name === 'zcat' || isset($o['d']) || isset($o['--decompress']);
    $toStdout = $name === 'zcat' || isset($o['c']) || isset($o['--stdout']);
    $keep = isset($o['k']) || isset($o['--keep']) || $toStdout;
    $list = isset($o['l']) || isset($o['--list']);
    if ($files === []) { $p->err($name . ": compressed data not " . ($decompress ? 'read from' : 'written to') . " a terminal. Use -f to force " . ($decompress ? 'de' : '') . "compression.\nFor help, type: $name --help\n"); $w->tip(tr('{name} potřebuje jméno souboru, např. {example}.', ['name' => $name, 'example' => $name . ' data.txt'])); return 1; }
    $status = 0;
    foreach ($files as $file) {
        $err = null;
        $raw = $w->readFile($file, $err);
        if ($raw === null) { $p->err("$name: $file: $err\n"); lab57_error_tip($w, (string)$err, $file); $status = 1; continue; }
        if ($list) {
            $u = lab58_arc_gzip_unpack($raw);
            if ($u === null) { $p->err("$name: $file: not in gzip format\n"); $status = 1; continue; }
            $unc = strlen($u['data']);
            $cmp = strlen($raw);
            $ratio = $unc > 0 ? number_format(100 * (1 - $cmp / $unc), 1) : '0.0';
            $p->line('         compressed        uncompressed  ratio uncompressed_name');
            $p->line(sprintf('%19d %19d %5s%% %s', $cmp, $unc, $ratio, (string)preg_replace('/\.gz$/', '', $file)));
            continue;
        }
        if ($decompress) {
            $u = lab58_arc_gzip_unpack($raw);
            if ($u === null) { $p->err("$name: $file: not in gzip format\n"); $w->tip(tr('Soubor není ve formátu gzip. Ověř ho příkazem file {file}.', ['file' => $file])); $status = 1; continue; }
            $out = (string)($u['data'] ?? '');
            $target = str_ends_with($file, '.gz') ? substr($file, 0, -3) : ($u['name'] !== null ? Lab57Vfs::normalize($u['name'], Lab57Vfs::dirname($w->abs($file))) : $file . '.out');
            if ($toStdout) { $p->out($out); continue; }
            if (!$w->writeFile($target, $out, false, $err)) { $p->err("$name: $target: $err\n"); $status = 1; continue; }
            if (!$keep) $w->fs->delete($w->abs($file));
            continue;
        }
        if (str_ends_with($file, '.gz')) { $p->err("$name: $file already has .gz suffix -- unchanged\n"); $status = 1; continue; }
        $packed = lab58_arc_gzip_pack($raw, basename($file));
        if ($toStdout) { $p->out($packed); continue; }
        if (!$w->writeFile($file . '.gz', $packed, false, $err)) { $p->err("$name: $file.gz: $err\n"); $status = 1; continue; }
        if (!$keep) $w->fs->delete($w->abs($file));
    }
    return $status;
}

// ---------------------------------------------------------------------------
// bzip2 / bunzip2 / bzcat
// ---------------------------------------------------------------------------

function lab58_cmd_bzip2(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $name = (string)$argv[0];
    [$o, $files, $error] = lab57_getopt(array_slice($argv, 1), 'dkcfvz123456789', ['decompress' => false, 'keep' => false, 'stdout' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    $decompress = $name === 'bunzip2' || $name === 'bzcat' || isset($o['d']) || isset($o['--decompress']);
    $toStdout = $name === 'bzcat' || isset($o['c']) || isset($o['--stdout']);
    $keep = isset($o['k']) || isset($o['--keep']) || $toStdout;
    if ($files === []) { $p->err($name . ": I won't " . ($decompress ? 'read' : 'write') . " compressed data " . ($decompress ? 'from' : 'to') . " a terminal.\n"); $w->tip(tr('{name} potřebuje jméno souboru, např. {example}.', ['name' => $name, 'example' => $name . ' data.txt' . ($decompress ? '.bz2' : '')])); return 1; }
    $status = 0;
    foreach ($files as $file) {
        $err = null;
        $raw = $w->readFile($file, $err);
        if ($raw === null) { $p->err("$name: Can't open input file $file: $err.\n"); lab57_error_tip($w, (string)$err, $file); $status = 1; continue; }
        if ($decompress) {
            $out = lab58_arc_bzip2_unpack($raw);
            if ($out === null) { $p->err("$name: $file is not a bzip2 file.\n"); $w->tip(tr('Soubor není ve formátu bzip2. Ověř ho příkazem file {file}.', ['file' => $file])); $status = 1; continue; }
            $target = str_ends_with($file, '.bz2') ? substr($file, 0, -4) : $file . '.out';
            if ($toStdout) { $p->out($out); continue; }
            if (!$w->writeFile($target, $out, false, $err)) { $p->err("$name: $target: $err\n"); $status = 1; continue; }
            if (!$keep) $w->fs->delete($w->abs($file));
            continue;
        }
        if (str_ends_with($file, '.bz2')) { $p->err("$name: Input file $file already has .bz2 suffix.\n"); $status = 1; continue; }
        $packed = lab58_arc_bzip2_pack($raw);
        if ($toStdout) { $p->out($packed); continue; }
        if (!$w->writeFile($file . '.bz2', $packed, false, $err)) { $p->err("$name: $file.bz2: $err\n"); $status = 1; continue; }
        if (!$keep) $w->fs->delete($w->abs($file));
    }
    return $status;
}

// ---------------------------------------------------------------------------
// zip / unzip
// ---------------------------------------------------------------------------

function lab58_cmd_zip(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'rq', ['recurse-paths' => false]);
    if ($error !== null) return lab57_opt_error($p, $error);
    if (count($ops) < 2) { $p->err("zip error: Nothing to do! (try: zip -r archiv.zip slozka)\n"); $w->tip(tr('Použití: zip archiv.zip soubor… nebo zip -r archiv.zip slozka')); return 12; }
    $archive = (string)array_shift($ops);
    if (!str_ends_with($archive, '.zip')) $archive .= '.zip';
    $members = [];
    $seen = [];
    foreach ($ops as $path) {
        $abs = $w->abs($path);
        $node = $w->fs->get($abs);
        if ($node === null || !$w->canTraverse($abs)) { $p->err("\tzip warning: name not matched: $path\n"); continue; }
        foreach ($w->fs->tree($abs) as $entry) {
            $n = $w->fs->get($entry);
            if ($n === null) continue;
            $rel = ltrim($path, './') . substr($entry, strlen($abs));
            $rel = ltrim((string)preg_replace('#^/+#', '', $rel), './');
            if ($rel === '' || isset($seen[$rel])) continue;
            $seen[$rel] = true;
            $isDir = ($n['t'] ?? '') === 'd';
            $members[] = ['name' => $rel, 'mtime' => (int)($n['mt'] ?? $w->now), 'content' => (string)($n['c'] ?? ''), 'dir' => $isDir];
            if (!isset($o['q'])) $p->line(($isDir ? '  adding: ' : '  adding: ') . $rel . ($isDir ? '/ (stored 0%)' : ' (deflated ' . lab58_arc_ratio((string)($n['c'] ?? '')) . '%)'));
        }
    }
    if ($members === []) { $p->err("zip error: Nothing to do! ($archive)\n"); return 12; }
    $err = null;
    if (!$w->writeFile($archive, lab58_arc_zip_pack($members), false, $err)) { $p->err("zip I/O error: $err\nzip error: Could not create output file ($archive)\n"); return 14; }
    return 0;
}

function lab58_arc_ratio(string $data): int
{
    if ($data === '') return 0;
    $c = strlen(lab58_arc_deflate($data));
    return (int)max(0, round(100 * (1 - $c / strlen($data))));
}

function lab58_cmd_unzip(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    [$o, $ops, $error] = lab57_getopt(array_slice($argv, 1), 'lod:q', []);
    if ($error !== null) return lab57_opt_error($p, $error);
    $archive = (string)($ops[0] ?? '');
    if ($archive === '') { $p->err("UnZip 6.00 of 20 April 2009, by Debian. Original by Info-ZIP.\n\nUsage: unzip [-Z] [-opts[modifiers]] file[.zip] [list] [-x xlist] [-d exdir]\n"); return 10; }
    $err = null;
    $raw = $w->readFile($archive, $err);
    if ($raw === null && !$w->fs->exists($w->abs($archive)) && !str_ends_with($archive, '.zip')) { $archive .= '.zip'; $raw = $w->readFile($archive, $err); }
    if ($raw === null) { $p->err("unzip:  cannot find or open $archive, $archive.zip or $archive.ZIP.\n"); lab57_error_tip($w, (string)$err, $archive); return 9; }
    if (lab58_arc_detect($raw) !== 'zip') { $p->err("Archive:  $archive\n  End-of-central-directory signature not found.\n"); $w->tip(tr('Soubor není ve formátu ZIP. Ověř ho příkazem file {archive}.', ['archive' => $archive])); return 9; }
    $members = lab58_arc_zip_unpack($raw);
    $list = isset($o['l']);
    $overwrite = isset($o['o']);
    $destDir = isset($o['d']) ? $w->abs((string)$o['d']) : $w->cwd;
    $p->line('Archive:  ' . $archive);
    if ($list) {
        $p->line('  Length      Date    Time    Name');
        $p->line('---------  ---------- -----   ----');
        $total = 0;
        $count = 0;
        foreach ($members as $m) {
            if ($m['dir']) continue;
            $total += $m['size'];
            $count++;
            $p->line(sprintf('%9d  2024-01-01 00:00   %s', $m['size'], $m['name']));
        }
        $p->line('---------                     -------');
        $p->line(sprintf('%9d                     %d file%s', $total, $count, $count === 1 ? '' : 's'));
        return 0;
    }
    if (isset($o['d']) && !$w->fs->isDir($destDir)) $w->mkdirp($destDir, 0755, $w->effectiveUser());
    $status = 0;
    foreach ($members as $m) {
        $target = Lab57Vfs::normalize($m['name'], $destDir);
        if ($m['dir']) { $w->mkdirp($target, 0755, $w->effectiveUser()); $p->line('   creating: ' . rtrim($m['name'], '/') . '/'); continue; }
        if ($w->fs->exists($target) && !$overwrite) {
            $p->out('replace ' . $m['name'] . '? [y]es, [n]o, [A]ll, [N]one, [r]ename: ');
            $p->line('NULL');
            $p->line('(EOF or read error, treating as "[N]one" ...)');
            $w->tip(tr('Terminál je neinteraktivní – přepis povol volbou -o: unzip -o {archive}.', ['archive' => $archive]));
            return 0;
        }
        $w->mkdirp(Lab57Vfs::dirname($target), 0755, $w->effectiveUser());
        $err = null;
        if (!$w->writeFile($target, $m['content'], false, $err)) { $p->err("unzip: cannot create $m[name]: $err\n"); $status = 1; continue; }
        $p->line('  inflating: ' . $m['name']);
    }
    return $status;
}

// ---------------------------------------------------------------------------
// Registrace: příkazy, filtr file_type, příručka
// ---------------------------------------------------------------------------

lab58_register_command('tar', 'lab58_cmd_tar');
lab58_register_command('gzip', 'lab58_cmd_gzip');
lab58_register_command('gunzip', 'lab58_cmd_gzip', ['bin' => '/usr/bin/gunzip']);
lab58_register_command('zcat', 'lab58_cmd_gzip', ['bin' => '/usr/bin/zcat']);
lab58_register_command('bzip2', 'lab58_cmd_bzip2');
lab58_register_command('bunzip2', 'lab58_cmd_bzip2', ['bin' => '/bin/bunzip2']);
lab58_register_command('bzcat', 'lab58_cmd_bzip2', ['bin' => '/bin/bzcat']);
lab58_register_command('zip', 'lab58_cmd_zip', ['package' => 'zip']);
lab58_register_command('unzip', 'lab58_cmd_unzip', ['package' => 'unzip']);

lab58_register_apt_package('zip', ['version' => '3.0-13', 'description' => 'Archiver for .zip files', 'bins' => ['zip'], 'preinstalled' => true]);
lab58_register_apt_package('unzip', ['version' => '6.0-28', 'description' => 'De-archiver for .zip files', 'bins' => ['unzip'], 'preinstalled' => true]);

/** Filtr file_type: rozpozná archivy podle magických bajtů (kontrakt docs/V58_PLAN.md §3.2). */
lab58_add_filter('file_type', static function (mixed $value, array $args): mixed {
    if (is_string($value) && $value !== '') return $value;
    $c = (string)($args['content'] ?? '');
    if ($c === '') return $value;
    $fmt = lab58_arc_detect($c);
    if ($fmt === 'tar') return 'POSIX tar archive';
    if ($fmt === 'zip') return 'Zip archive data, at least v2.0 to extract, compression method=deflate';
    if ($fmt === 'bzip2') return 'bzip2 compressed data, block size = 900k';
    if ($fmt === 'gzip') {
        $orig = strlen($c) >= 8 ? unpack('V', substr($c, -4))[1] : 0;
        $named = (ord($c[3] ?? "\x00") & 0x08) !== 0;
        return 'gzip compressed data' . ($named ? ', was "' . lab58_arc_gzip_name($c) . '"' : '') . ', from Unix, original size modulo 2^32 ' . $orig;
    }
    return $value;
});

function lab58_arc_gzip_name(string $raw): string
{
    $u = lab58_arc_gzip_unpack($raw);
    return (string)($u['name'] ?? '');
}

lab58_register_manual(['categories' => ['archivy' => ['label' => 'Archivy a komprese', 'icon' => '🗜', 'lead' => 'Balení a rozbalování souborů: tar, gzip, zip a spol.']], 'commands' => [
    'tar' => ['extend' => true, 'tldr' => [['tar -tzf archiv.tar.gz', 'vypíše obsah bez rozbalení'], ['tar -xzf archiv.tar.gz', 'rozbalí archiv gzip'], ['tar -czf zaloha.tar.gz slozka', 'zabalí složku a zkomprimuje']], 'see_also' => ['gzip', 'zip', 'file']],
    'gzip' => ['extend' => true, 'tldr' => [['gzip -k data.txt', 'zkomprimuje a nechá originál'], ['gunzip data.txt.gz', 'rozbalí zpět'], ['zcat data.txt.gz', 'vypíše obsah bez rozbalení']], 'see_also' => ['gunzip', 'zcat', 'tar']],
    'gunzip' => ['cat' => 'archivy', 'summary' => 'Rozbalí soubor zabalený gzipem (.gz).', 'synopsis' => 'gunzip soubor.gz', 'about' => 'Opak příkazu gzip: ze souboru s příponou .gz obnoví původní soubor a .gz verzi odstraní. Stejného výsledku dosáhneš i příkazem gzip -d.', 'options' => [['-k', 'ponechá i zabalený soubor'], ['-c', 'vypíše obsah na výstup místo zápisu do souboru'], ['-l', 'jen ukáže velikosti a poměr komprese']], 'examples' => [['gunzip zaloha.tar.gz', 'rozbalí na zaloha.tar'], ['gunzip -k data.gz', 'rozbalí a ponechá i data.gz']], 'tldr' => [['gunzip soubor.gz', 'rozbalí .gz zpět na původní soubor']], 'see_also' => ['gzip', 'zcat', 'tar'], 'related' => ['gzip'], 'level' => 2, 'in_lab' => true],
    'zcat' => ['cat' => 'archivy', 'summary' => 'Vypíše obsah gzip souboru, aniž by ho rozbalil na disk.', 'synopsis' => 'zcat soubor.gz', 'about' => 'Funguje jako cat, ale nejdřív za běhu rozbalí gzip. Hodí se pro rychlé nahlédnutí do komprimovaných logů, aniž bys je musel(a) rozbalovat.', 'options' => [], 'examples' => [['zcat syslog.1.gz', 'vypíše rozbalený log'], ['zcat data.gz | grep chyba', 'hledá v rozbaleném obsahu']], 'tldr' => [['zcat soubor.gz', 'ukáže obsah bez rozbalení']], 'see_also' => ['gunzip', 'gzip', 'grep'], 'related' => ['gzip', 'cat'], 'level' => 2, 'in_lab' => true],
    'bzip2' => ['cat' => 'archivy', 'summary' => 'Zkomprimuje soubor formátem bzip2 (.bz2).', 'synopsis' => 'bzip2 [-dk] soubor', 'about' => 'Alternativa ke gzipu s obvykle lepším poměrem komprese. Vytvoří soubor s příponou .bz2 a původní odstraní; volbou -d ho zase rozbalí. V laboratoři jde o věrnou obdobu formátu (magické bajty a round-trip odpovídají).', 'options' => [['-d', 'rozbalí (dekomprimuje)'], ['-k', 'ponechá původní soubor'], ['-c', 'vypíše na výstup']], 'examples' => [['bzip2 zaznam.txt', 'vytvoří zaznam.txt.bz2'], ['bzip2 -d zaznam.txt.bz2', 'rozbalí zpět']], 'tldr' => [['bzip2 -k data.txt', 'zabalí a nechá originál'], ['bunzip2 data.txt.bz2', 'rozbalí .bz2']], 'see_also' => ['bunzip2', 'gzip', 'tar'], 'related' => ['gzip', 'tar'], 'level' => 2, 'in_lab' => true],
    'bunzip2' => ['cat' => 'archivy', 'summary' => 'Rozbalí soubor zabalený formátem bzip2 (.bz2).', 'synopsis' => 'bunzip2 soubor.bz2', 'about' => 'Opak příkazu bzip2: obnoví původní soubor ze souboru .bz2. Stejné jako bzip2 -d.', 'options' => [['-k', 'ponechá i .bz2 soubor'], ['-c', 'vypíše obsah na výstup']], 'examples' => [['bunzip2 zaznam.txt.bz2', 'rozbalí na zaznam.txt']], 'tldr' => [['bunzip2 soubor.bz2', 'rozbalí .bz2 zpět']], 'see_also' => ['bzip2', 'bzcat'], 'related' => ['bzip2'], 'level' => 2, 'in_lab' => true],
    'bzcat' => ['cat' => 'archivy', 'summary' => 'Vypíše obsah bzip2 souboru bez rozbalení na disk.', 'synopsis' => 'bzcat soubor.bz2', 'about' => 'Jako cat, ale za běhu rozbalí bzip2. Vhodné pro nahlédnutí do komprimovaného souboru.', 'options' => [], 'examples' => [['bzcat zaznam.txt.bz2', 'vypíše rozbalený obsah']], 'tldr' => [['bzcat soubor.bz2', 'ukáže obsah .bz2']], 'see_also' => ['bunzip2', 'zcat'], 'related' => ['bzip2', 'cat'], 'level' => 2, 'in_lab' => true],
    'zip' => ['cat' => 'archivy', 'summary' => 'Zabalí soubory do archivu ZIP.', 'synopsis' => 'zip [-r] archiv.zip soubory…', 'about' => 'Vytvoří archiv ZIP – formát běžný i ve Windows. Na rozdíl od tar+gzip archivuje i komprimuje jedním příkazem. Složky přidáš rekurzivně volbou -r.', 'options' => [['-r', 'přidá složky včetně jejich obsahu'], ['-q', 'tichý režim bez výpisu']], 'examples' => [['zip zaloha.zip index.html styl.css', 'zabalí dva soubory'], ['zip -r web.zip web', 'zabalí celou složku web']], 'tldr' => [['zip -r archiv.zip slozka', 'zabalí složku do ZIP'], ['zip archiv.zip a.txt b.txt', 'zabalí soubory']], 'see_also' => ['unzip', 'tar'], 'related' => ['unzip', 'tar'], 'level' => 2, 'in_lab' => true],
    'unzip' => ['cat' => 'archivy', 'summary' => 'Rozbalí archiv ZIP, nebo jen vypíše jeho obsah.', 'synopsis' => 'unzip [-l] [-o] [-d slozka] archiv.zip', 'about' => 'Rozbalí obsah ZIP archivu do aktuální nebo zvolené složky. Volbou -l si obsah jen vypíšeš, aniž bys cokoli rozbalil(a).', 'options' => [['-l', 'jen vypíše obsah bez rozbalení'], ['-o', 'přepíše existující soubory bez ptaní'], ['-d slozka', 'rozbalí do zvolené složky']], 'examples' => [['unzip -l data.zip', 'vypíše obsah archivu'], ['unzip data.zip', 'rozbalí do aktuální složky'], ['unzip -o -d cil data.zip', 'rozbalí do složky cil a přepíše']], 'tldr' => [['unzip -l archiv.zip', 'vypíše obsah bez rozbalení'], ['unzip archiv.zip', 'rozbalí archiv']], 'see_also' => ['zip', 'tar', 'file'], 'related' => ['zip', 'tar'], 'level' => 2, 'in_lab' => true],
]]);
