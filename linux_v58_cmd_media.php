<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – grafika (LAB-07): model obrázku ve VFS.
 *
 * Obrázek = soubor ve VFS se skutečnou hlavičkou formátu (PNG, JPEG, GIF, WebP, BMP, ICO, TIFF;
 * SVG jako text) a metadaty (rozměry, barvy, DPI, EXIF včetně fiktivních GPS). Pixely se
 * nesimulují: „výpočet“ (zmenšení, převod) mění jen hlavičku, metadata a simulovanou velikost
 * uzlu (klíč 's'). Stejná data = stejné bajty (deterministické, žádný čas ani náhoda mimo semínko).
 * Příkazy: linux_v58_cmd_media_im.php (ImageMagick), linux_v58_cmd_media_tools.php (exiftool, rename).
 *
 * BEZPEČNOSTNÍ INVARIANT: nic se nespouští, žádná síť. Parser čte jen řetězec z VFS, má omezené
 * délky i počty bloků a každou hodnotu ořízne na rozumný rozsah (žák může soubor podvrhnout).
 */

const LAB58_IMG_MAX_DIM = 65535;
const LAB58_IMG_MAX_BLOCKS = 64;
const LAB58_IMG_FORMATS = ['JPEG', 'PNG', 'GIF', 'WEBP', 'BMP', 'ICO', 'TIFF', 'SVG'];

// ---------------------------------------------------------------------------
// Binární pomocníci (bezpečné čtení mimo rozsah vrací 0)
// ---------------------------------------------------------------------------

function lab58_img_u8(string $s, int $o): int
{
    return $o >= 0 && $o < strlen($s) ? ord($s[$o]) : 0;
}

function lab58_img_u16be(string $s, int $o): int
{
    return (lab58_img_u8($s, $o) << 8) | lab58_img_u8($s, $o + 1);
}

function lab58_img_u16le(string $s, int $o): int
{
    return lab58_img_u8($s, $o) | (lab58_img_u8($s, $o + 1) << 8);
}

function lab58_img_u24le(string $s, int $o): int
{
    return lab58_img_u16le($s, $o) | (lab58_img_u8($s, $o + 2) << 16);
}

function lab58_img_u32be(string $s, int $o): int
{
    return (lab58_img_u16be($s, $o) << 16) | lab58_img_u16be($s, $o + 2);
}

function lab58_img_u32le(string $s, int $o): int
{
    return lab58_img_u16le($s, $o) | (lab58_img_u16le($s, $o + 2) << 16);
}

function lab58_img_u24le_pack(int $v): string
{
    return chr($v & 0xFF) . chr(($v >> 8) & 0xFF) . chr(($v >> 16) & 0xFF);
}

/** Čitelný text z metadat: platné UTF-8, bez řídicích znaků, nejvýš $max znaků. */
function lab58_img_clean(string $text, int $max = 200): string
{
    if (!mb_check_encoding($text, 'UTF-8')) $text = (string)mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
    $text = (string)preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text);
    return mb_substr(trim($text), 0, $max);
}

/** Deterministická „komprimovaná data“ (bez bajtu 0xFF, aby nerozbila značky JPEG). */
function lab58_img_filler(string $seed, int $len): string
{
    $r = new Lab57Rng('img|' . $seed);
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $b = $r->next() & 0xFF;
        $out .= chr($b === 0xFF ? 0xFE : $b);
    }
    return $out;
}

// ---------------------------------------------------------------------------
// EXIF: skutečná čísla značek, zjednodušené kódování (záznamy za hlavičkou TIFF „MM\0*“)
// ---------------------------------------------------------------------------

/** @return array<string,array{0:int,1:string,2:int,3:string,4:string}> jméno => [id, typ a|s|r, počet, skupina, popis] */
function lab58_exif_table(): array
{
    return [
        'ImageDescription' => [0x010E, 'a', 1, 'IFD0', 'Image Description'],
        'Make' => [0x010F, 'a', 1, 'IFD0', 'Make'],
        'Model' => [0x0110, 'a', 1, 'IFD0', 'Camera Model Name'],
        'Orientation' => [0x0112, 's', 1, 'IFD0', 'Orientation'],
        'XResolution' => [0x011A, 'r', 1, 'IFD0', 'X Resolution'],
        'YResolution' => [0x011B, 'r', 1, 'IFD0', 'Y Resolution'],
        'ResolutionUnit' => [0x0128, 's', 1, 'IFD0', 'Resolution Unit'],
        'Software' => [0x0131, 'a', 1, 'IFD0', 'Software'],
        'ModifyDate' => [0x0132, 'a', 1, 'IFD0', 'Modify Date'],
        'Artist' => [0x013B, 'a', 1, 'IFD0', 'Artist'],
        'Copyright' => [0x8298, 'a', 1, 'IFD0', 'Copyright'],
        'ExposureTime' => [0x829A, 'r', 1, 'ExifIFD', 'Exposure Time'],
        'FNumber' => [0x829D, 'r', 1, 'ExifIFD', 'F Number'],
        'ISO' => [0x8827, 's', 1, 'ExifIFD', 'ISO'],
        'DateTimeOriginal' => [0x9003, 'a', 1, 'ExifIFD', 'Date/Time Original'],
        'CreateDate' => [0x9004, 'a', 1, 'ExifIFD', 'Create Date'],
        'FocalLength' => [0x920A, 'r', 1, 'ExifIFD', 'Focal Length'],
        'UserComment' => [0x9286, 'a', 1, 'ExifIFD', 'User Comment'],
        'LensModel' => [0xA434, 'a', 1, 'ExifIFD', 'Lens Model'],
        'GPSLatitudeRef' => [0x0001, 'a', 1, 'GPS', 'GPS Latitude Ref'],
        'GPSLatitude' => [0x0002, 'r', 3, 'GPS', 'GPS Latitude'],
        'GPSLongitudeRef' => [0x0003, 'a', 1, 'GPS', 'GPS Longitude Ref'],
        'GPSLongitude' => [0x0004, 'r', 3, 'GPS', 'GPS Longitude'],
        'GPSAltitudeRef' => [0x0005, 's', 1, 'GPS', 'GPS Altitude Ref'],
        'GPSAltitude' => [0x0006, 'r', 1, 'GPS', 'GPS Altitude'],
    ];
}

/** Hodnoty: a = string, s = int, r = list<[čitatel, jmenovatel]>. */
function lab58_exif_encode(array $exif): string
{
    $out = '';
    $count = 0;
    foreach (lab58_exif_table() as $name => [$id, $type, $num]) {
        if (!array_key_exists($name, $exif)) continue;
        $v = $exif[$name];
        if ($type === 'a') {
            $text = substr((string)$v, 0, 250);
            $out .= pack('nC', $id, 2) . pack('n', strlen($text)) . $text;
        } elseif ($type === 's') {
            $out .= pack('nC', $id, 3) . pack('n', max(0, min(65535, (int)$v)));
        } else {
            $pairs = array_slice(array_values((array)$v), 0, $num);
            if (count($pairs) !== $num) continue;
            $out .= pack('nC', $id, 5) . chr($num);
            foreach ($pairs as $pair) $out .= pack('NN', max(0, (int)($pair[0] ?? 0)), max(1, (int)($pair[1] ?? 1)));
        }
        $count++;
    }
    return "MM\x00*\x00\x00\x00\x08" . pack('n', $count) . $out;
}

function lab58_exif_decode(string $bin): array
{
    if (!str_starts_with($bin, "MM\x00*")) return [];
    $byId = [];
    foreach (lab58_exif_table() as $name => $def) $byId[$def[0]] = [$name, $def[1], $def[2]];
    $n = min(LAB58_IMG_MAX_BLOCKS, lab58_img_u16be($bin, 8));
    $pos = 10;
    $len = strlen($bin);
    $out = [];
    for ($i = 0; $i < $n && $pos + 3 <= $len; $i++) {
        $id = lab58_img_u16be($bin, $pos);
        $type = lab58_img_u8($bin, $pos + 2);
        $pos += 3;
        $def = $byId[$id] ?? null;
        if ($type === 2) {
            $l = lab58_img_u16be($bin, $pos);
            $text = substr($bin, $pos + 2, $l);
            $pos += 2 + $l;
            if ($def !== null && $def[1] === 'a') $out[$def[0]] = lab58_img_clean($text, 250);
        } elseif ($type === 3) {
            $v = lab58_img_u16be($bin, $pos);
            $pos += 2;
            if ($def !== null && $def[1] === 's') $out[$def[0]] = $v;
        } elseif ($type === 5) {
            $cnt = lab58_img_u8($bin, $pos);
            $pairs = [];
            for ($k = 0; $k < $cnt && $k < 8; $k++) $pairs[] = [lab58_img_u32be($bin, $pos + 1 + $k * 8), max(1, lab58_img_u32be($bin, $pos + 5 + $k * 8))];
            $pos += 1 + $cnt * 8;
            if ($def !== null && $def[1] === 'r' && count($pairs) === $def[2]) $out[$def[0]] = $pairs;
        } else {
            break;
        }
    }
    return $out;
}

function lab58_exif_has_gps(array $exif): bool
{
    foreach (array_keys($exif) as $name) if (str_starts_with((string)$name, 'GPS')) return true;
    return false;
}

/** Desetinné stupně → EXIF (stupně, minuty, setiny sekund). */
function lab58_exif_dms(float $deg): array
{
    $deg = abs($deg);
    $d = (int)floor($deg);
    $minF = ($deg - $d) * 60;
    $m = (int)floor($minF);
    $s = (int)round(($minF - $m) * 6000);
    if ($s >= 6000) { $s -= 6000; $m++; }
    if ($m >= 60) { $m -= 60; $d++; }
    return [[$d, 1], [$m, 1], [$s, 100]];
}

function lab58_exif_rational(array $pair): float
{
    return (float)($pair[0] ?? 0) / max(1, (int)($pair[1] ?? 1));
}

/** @return array{0:float,1:float}|null zeměpisná šířka a délka se znaménkem */
function lab58_exif_gps_decimal(array $exif): ?array
{
    if (!isset($exif['GPSLatitude'], $exif['GPSLongitude'])) return null;
    $conv = static function (array $dms): float {
        return lab58_exif_rational($dms[0]) + lab58_exif_rational($dms[1]) / 60 + lab58_exif_rational($dms[2]) / 3600;
    };
    $lat = $conv($exif['GPSLatitude']) * (($exif['GPSLatitudeRef'] ?? 'N') === 'S' ? -1 : 1);
    $lon = $conv($exif['GPSLongitude']) * (($exif['GPSLongitudeRef'] ?? 'E') === 'W' ? -1 : 1);
    return [$lat, $lon];
}

/** Zápis jako exiftool: 50 deg 5' 14.87" */
function lab58_exif_dms_text(array $dms): string
{
    return sprintf('%d deg %d\' %.2f"', (int)round(lab58_exif_rational($dms[0])), (int)round(lab58_exif_rational($dms[1])), lab58_exif_rational($dms[2]));
}

/** Nastaví GPS podle desetinných stupňů (fiktivní poloha ve cvičení). */
function lab58_exif_set_gps(array $exif, float $lat, float $lon, float $alt): array
{
    $exif['GPSLatitudeRef'] = $lat < 0 ? 'S' : 'N';
    $exif['GPSLatitude'] = lab58_exif_dms($lat);
    $exif['GPSLongitudeRef'] = $lon < 0 ? 'W' : 'E';
    $exif['GPSLongitude'] = lab58_exif_dms($lon);
    $exif['GPSAltitudeRef'] = $alt < 0 ? 1 : 0;
    $exif['GPSAltitude'] = [[(int)round(abs($alt) * 10), 10]];
    return $exif;
}

// ---------------------------------------------------------------------------
// Model obrázku
// ---------------------------------------------------------------------------

/**
 * Model: fmt, w, h, ctype (rgb|rgba|gray|graya|palette), depth, dpi, q (kvalita, 0 = neurčená),
 * cx (složitost scény 1–250 → velikost souboru), colors (paleta), exif (lab58_exif_table),
 * text (Comment, Software, Title …), svg (zdroj SVG).
 */
function lab58_img_new(string $fmt, int $w, int $h, array $opt = []): array
{
    $m = [
        'fmt' => $fmt, 'w' => $w, 'h' => $h, 'ctype' => 'rgb', 'depth' => 8, 'dpi' => 72, 'q' => 0, 'cx' => 100,
        'colors' => 0, 'exif' => [], 'text' => [], 'svg' => '',
    ];
    foreach ($opt as $k => $v) if (array_key_exists($k, $m)) $m[$k] = $v;
    return lab58_img_normalize($m);
}

/** Ořízne hodnoty modelu do platných rozsahů (data mohou pocházet z podvrženého souboru). */
function lab58_img_normalize(array $m): array
{
    $m['fmt'] = in_array($m['fmt'] ?? '', LAB58_IMG_FORMATS, true) ? (string)$m['fmt'] : 'PNG';
    $m['w'] = max(1, min(LAB58_IMG_MAX_DIM, (int)($m['w'] ?? 1)));
    $m['h'] = max(1, min(LAB58_IMG_MAX_DIM, (int)($m['h'] ?? 1)));
    $m['ctype'] = in_array($m['ctype'] ?? '', ['rgb', 'rgba', 'gray', 'graya', 'palette'], true) ? (string)$m['ctype'] : 'rgb';
    $depth = (int)($m['depth'] ?? 8);
    $m['depth'] = in_array($depth, [1, 2, 4, 8, 16], true) ? $depth : 8;
    $m['dpi'] = max(1, min(9600, (int)($m['dpi'] ?? 72)));
    $m['q'] = max(0, min(100, (int)($m['q'] ?? 0)));
    $m['cx'] = max(1, min(250, (int)($m['cx'] ?? 100)));
    $m['colors'] = max(0, min(256, (int)($m['colors'] ?? 0)));
    $m['exif'] = is_array($m['exif'] ?? null) ? $m['exif'] : [];
    $m['text'] = is_array($m['text'] ?? null) ? array_slice($m['text'], 0, 8, true) : [];
    $m['svg'] = (string)($m['svg'] ?? '');
    return $m;
}

function lab58_img_has_alpha(array $m): bool
{
    return in_array($m['ctype'], ['rgba', 'graya'], true);
}

function lab58_img_payload(array $m): string
{
    return "EDU\x00" . chr($m['q']) . chr($m['cx']);
}

/** @return array{q:int,cx:int}|null */
function lab58_img_payload_read(string $data): ?array
{
    if (!str_starts_with($data, "EDU\x00") || strlen($data) < 6) return null;
    return ['q' => ord($data[4]), 'cx' => ord($data[5])];
}

function lab58_img_fingerprint(array $m): string
{
    return $m['fmt'] . '|' . $m['w'] . 'x' . $m['h'] . '|' . $m['ctype'] . '|' . $m['q'] . '|' . $m['cx'] . '|' . md5((string)json_encode([$m['exif'], $m['text']]));
}

/** Kvantizační tabulka JPEG podle kvality (vzorec IJG) – jen aby hlavička vypadala jako skutečná. */
function lab58_img_dqt(int $q): string
{
    $base = [16, 11, 10, 16, 24, 40, 51, 61, 12, 12, 14, 19, 26, 58, 60, 55, 14, 13, 16, 24, 40, 57, 69, 56, 14, 17, 22, 29, 51, 87, 80, 62,
        18, 22, 37, 56, 68, 109, 103, 77, 24, 35, 55, 64, 81, 104, 113, 92, 49, 64, 78, 87, 103, 121, 120, 101, 72, 92, 95, 98, 112, 100, 103, 99];
    $q = max(1, min(100, $q > 0 ? $q : 92));
    $scale = $q < 50 ? intdiv(5000, $q) : 200 - 2 * $q;
    $out = '';
    foreach ($base as $v) $out .= chr(max(1, min(255, intdiv($v * $scale + 50, 100))));
    return $out;
}

// ---------------------------------------------------------------------------
// Kodéry (model → bajty)
// ---------------------------------------------------------------------------

function lab58_img_encode(array $m): string
{
    $m = lab58_img_normalize($m);
    return match ($m['fmt']) {
        'JPEG' => lab58_img_enc_jpeg($m),
        'GIF' => lab58_img_enc_gif($m),
        'WEBP' => lab58_img_enc_webp($m),
        'BMP' => lab58_img_enc_bmp($m),
        'ICO' => lab58_img_enc_ico($m),
        'TIFF' => lab58_img_enc_tiff($m),
        'SVG' => $m['svg'] !== '' ? $m['svg'] : lab58_img_svg_source($m['w'], $m['h'], 'EDU', '#0a7e8c'),
        default => lab58_img_enc_png($m),
    };
}

function lab58_img_enc_jpeg(array $m): string
{
    $seg = static fn(int $marker, string $data): string => "\xFF" . chr($marker) . pack('n', strlen($data) + 2) . $data;
    $out = "\xFF\xD8" . $seg(0xE0, "JFIF\x00\x01\x01\x01" . pack('nn', $m['dpi'], $m['dpi']) . "\x00\x00");
    if ($m['exif'] !== []) $out .= $seg(0xE1, "Exif\x00\x00" . lab58_exif_encode($m['exif']));
    $out .= $seg(0xEB, lab58_img_payload($m));
    if ((string)($m['text']['Comment'] ?? '') !== '') $out .= $seg(0xFE, substr((string)$m['text']['Comment'], 0, 500));
    $out .= $seg(0xDB, "\x00" . lab58_img_dqt($m['q']));
    $gray = $m['ctype'] === 'gray' || $m['ctype'] === 'graya';
    $out .= $seg(0xC0, "\x08" . pack('nn', $m['h'], $m['w']) . ($gray ? "\x01\x01\x11\x00" : "\x03\x01\x22\x00\x02\x11\x01\x03\x11\x01"));
    $out .= $seg(0xDA, $gray ? "\x01\x01\x00\x00\x3F\x00" : "\x03\x01\x00\x02\x11\x03\x11\x00\x3F\x00");
    return $out . lab58_img_filler(lab58_img_fingerprint($m), 96) . "\xFF\xD9";
}

function lab58_img_png_chunk(string $type, string $data): string
{
    return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
}

function lab58_img_enc_png(array $m, bool $withPhys = true): string
{
    $ct = ['gray' => 0, 'rgb' => 2, 'palette' => 3, 'graya' => 4, 'rgba' => 6][$m['ctype']];
    $depth = $ct === 3 ? min(8, $m['depth']) : ($m['depth'] >= 16 ? 16 : 8);
    $out = "\x89PNG\r\n\x1a\n" . lab58_img_png_chunk('IHDR', pack('NN', $m['w'], $m['h']) . chr($depth) . chr($ct) . "\x00\x00\x00");
    if ($withPhys) {
        $ppm = (int)round($m['dpi'] / 0.0254);
        $out .= lab58_img_png_chunk('pHYs', pack('NN', $ppm, $ppm) . "\x01");
    }
    if ($ct === 3) $out .= lab58_img_png_chunk('PLTE', lab58_img_filler(lab58_img_fingerprint($m) . '|plte', 3 * max(2, $m['colors'] ?: 256)));
    foreach ($m['text'] as $key => $value) {
        $key = substr((string)preg_replace('/[^A-Za-z0-9 ]/', '', (string)$key), 0, 79);
        if ($key === '') continue;
        $value = (string)$value;
        $out .= preg_match('/[^\x20-\x7E]/', $value) === 1
            ? lab58_img_png_chunk('iTXt', $key . "\x00\x00\x00\x00\x00" . substr($value, 0, 500))
            : lab58_img_png_chunk('tEXt', $key . "\x00" . substr($value, 0, 500));
    }
    if ($m['exif'] !== []) $out .= lab58_img_png_chunk('eXIf', lab58_exif_encode($m['exif']));
    $out .= lab58_img_png_chunk('edUc', lab58_img_payload($m));
    $out .= lab58_img_png_chunk('IDAT', lab58_img_filler(lab58_img_fingerprint($m), 72));
    return $out . lab58_img_png_chunk('IEND', '');
}

function lab58_img_gif_bits(int $colors): int
{
    $bits = 1;
    while ((1 << $bits) < $colors && $bits < 8) $bits++;
    return $bits;
}

function lab58_img_enc_gif(array $m): string
{
    $bits = lab58_img_gif_bits($m['colors'] ?: 256);
    $out = 'GIF89a' . pack('vv', $m['w'], $m['h']) . chr(0xF0 | ($bits - 1)) . "\x00\x00";
    $out .= lab58_img_filler(lab58_img_fingerprint($m) . '|pal', 3 * (1 << $bits));
    $out .= "\x21\xFF\x0BEDUCANET1.0\x06" . lab58_img_payload($m) . "\x00";
    $comment = substr((string)($m['text']['Comment'] ?? ''), 0, 255);
    if ($comment !== '') $out .= "\x21\xFE" . chr(strlen($comment)) . $comment . "\x00";
    $data = lab58_img_filler(lab58_img_fingerprint($m), 60);
    return $out . "\x2C" . pack('vvvv', 0, 0, $m['w'], $m['h']) . "\x00\x08" . chr(strlen($data)) . $data . "\x00;";
}

function lab58_img_riff_chunk(string $fourcc, string $data): string
{
    return $fourcc . pack('V', strlen($data)) . $data . (strlen($data) % 2 === 1 ? "\x00" : '');
}

function lab58_img_enc_webp(array $m): string
{
    $alpha = lab58_img_has_alpha($m);
    $filler = lab58_img_filler(lab58_img_fingerprint($m), 64);
    $tag = (1 << 4) | ((strlen($filler) & 0x7FFFF) << 5);
    $vp8 = substr(pack('V', $tag), 0, 3) . "\x9d\x01\x2a" . pack('vv', $m['w'] & 0x3FFF, $m['h'] & 0x3FFF) . $filler;
    $chunks = '';
    if ($alpha || $m['exif'] !== []) {
        $flags = ($alpha ? 0x10 : 0) | ($m['exif'] !== [] ? 0x08 : 0);
        $chunks .= lab58_img_riff_chunk('VP8X', chr($flags) . "\x00\x00\x00" . lab58_img_u24le_pack($m['w'] - 1) . lab58_img_u24le_pack($m['h'] - 1));
        if ($alpha) $chunks .= lab58_img_riff_chunk('ALPH', "\x00" . lab58_img_filler(lab58_img_fingerprint($m) . '|a', 16));
    }
    $chunks .= lab58_img_riff_chunk('VP8 ', $vp8) . lab58_img_riff_chunk('EDUC', lab58_img_payload($m));
    if ($m['exif'] !== []) $chunks .= lab58_img_riff_chunk('EXIF', lab58_exif_encode($m['exif']));
    return 'RIFF' . pack('V', 4 + strlen($chunks)) . 'WEBP' . $chunks;
}

function lab58_img_enc_bmp(array $m): string
{
    $bpp = lab58_img_has_alpha($m) ? 32 : 24;
    $row = intdiv($m['w'] * $bpp + 31, 32) * 4;
    $ppm = (int)round($m['dpi'] / 0.0254);
    $header = pack('VVVvvVVVVVV', 40, $m['w'], $m['h'], 1, $bpp, 0, $row * $m['h'], $ppm, $ppm, 0, 0);
    return 'BM' . pack('V', 54 + $row * $m['h']) . "\x00\x00\x00\x00" . pack('V', 54) . $header
        . lab58_img_filler(lab58_img_fingerprint($m), 48) . lab58_img_payload($m);
}

function lab58_img_enc_ico(array $m): string
{
    $png = lab58_img_enc_png(array_merge($m, ['ctype' => 'rgba', 'exif' => [], 'text' => []]), false);
    $entry = chr($m['w'] >= 256 ? 0 : $m['w']) . chr($m['h'] >= 256 ? 0 : $m['h']) . "\x00\x00" . pack('vvVV', 1, 32, strlen($png), 22);
    return "\x00\x00\x01\x00\x01\x00" . $entry . $png;
}

function lab58_img_enc_tiff(array $m): string
{
    $spp = match ($m['ctype']) { 'gray' => 1, 'graya' => 2, 'rgba' => 4, default => 3 };
    $entries = [[256, 4, $m['w']], [257, 4, $m['h']], [258, 3, 8], [259, 3, 1], [262, 3, $spp <= 2 ? 1 : 2], [277, 3, $spp], [282, 5, 0], [283, 5, 0], [296, 3, 2]];
    $ratOffset = 8 + 2 + 12 * count($entries) + 4;
    $ifd = pack('n', count($entries));
    foreach ($entries as [$tag, $type, $value]) {
        if ($type === 5) $value = $ratOffset + ($tag === 283 ? 8 : 0);
        $ifd .= pack('nnN', $tag, $type, 1) . ($type === 3 ? pack('nn', $value, 0) : pack('N', $value));
    }
    $ifd .= pack('N', 0);
    return "MM\x00*" . pack('N', 8) . $ifd . pack('NNNN', $m['dpi'], 1, $m['dpi'], 1) . lab58_img_filler(lab58_img_fingerprint($m), 48) . lab58_img_payload($m);
}

function lab58_img_svg_source(int $w, int $h, string $label, string $color): string
{
    $label = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $size = max(8, intdiv(min($w, $h * 3), 4));
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"$w\" height=\"$h\" viewBox=\"0 0 $w $h\">\n"
        . "  <title>$label</title>\n  <rect width=\"$w\" height=\"$h\" rx=\"8\" fill=\"$color\"/>\n"
        . "  <text x=\"50%\" y=\"50%\" dominant-baseline=\"middle\" text-anchor=\"middle\" font-family=\"sans-serif\" font-size=\"$size\" fill=\"#ffffff\">$label</text>\n</svg>\n";
}

// ---------------------------------------------------------------------------
// Dekodéry (bajty → model); neplatná data → null
// ---------------------------------------------------------------------------

function lab58_img_parse(string $c): ?array
{
    if ($c === '') return null;
    if (str_starts_with($c, "\xFF\xD8\xFF")) return lab58_img_dec_jpeg($c);
    if (str_starts_with($c, "\x89PNG\r\n\x1a\n")) return lab58_img_dec_png($c);
    if (str_starts_with($c, 'GIF87a') || str_starts_with($c, 'GIF89a')) return lab58_img_dec_gif($c);
    if (str_starts_with($c, 'RIFF') && substr($c, 8, 4) === 'WEBP') return lab58_img_dec_webp($c);
    if (str_starts_with($c, 'BM') && strlen($c) >= 54) return lab58_img_dec_bmp($c);
    if (str_starts_with($c, "\x00\x00\x01\x00") && strlen($c) >= 22) return lab58_img_dec_ico($c);
    if (str_starts_with($c, "MM\x00*")) return lab58_img_dec_tiff($c);
    return lab58_img_dec_svg($c);
}

function lab58_img_dec_jpeg(string $c): ?array
{
    $m = ['fmt' => 'JPEG', 'w' => 0, 'h' => 0, 'ctype' => 'rgb', 'dpi' => 72, 'q' => 0, 'cx' => 100, 'exif' => [], 'text' => []];
    $len = strlen($c);
    $pos = 2;
    $dqt = '';
    for ($guard = 0; $guard < LAB58_IMG_MAX_BLOCKS && $pos + 4 <= $len; $guard++) {
        if ($c[$pos] !== "\xFF") break;
        $marker = ord($c[$pos + 1]);
        $pos += 2;
        if ($marker === 0xD8 || $marker === 0x01 || ($marker >= 0xD0 && $marker <= 0xD7)) continue;
        $seg = lab58_img_u16be($c, $pos);
        if ($marker === 0xD9 || $seg < 2 || $pos + $seg > $len) break;
        $data = substr($c, $pos + 2, $seg - 2);
        $pos += $seg;
        if ($marker === 0xE0 && str_starts_with($data, "JFIF\x00")) {
            $units = lab58_img_u8($data, 7);
            $density = lab58_img_u16be($data, 8);
            if ($density > 0 && $units > 0) $m['dpi'] = $units === 2 ? (int)round($density * 2.54) : $density;
        } elseif ($marker === 0xE1 && str_starts_with($data, "Exif\x00\x00")) {
            $m['exif'] = lab58_exif_decode(substr($data, 6));
        } elseif ($marker === 0xEB && ($pl = lab58_img_payload_read($data)) !== null) {
            $m['q'] = $pl['q'];
            $m['cx'] = $pl['cx'];
        } elseif ($marker === 0xFE) {
            $m['text']['Comment'] = lab58_img_clean($data, 500);
        } elseif ($marker === 0xDB && $dqt === '') {
            $dqt = substr($data, 1, 64);
        } elseif ($marker >= 0xC0 && $marker <= 0xC3) {
            $m['h'] = lab58_img_u16be($data, 1);
            $m['w'] = lab58_img_u16be($data, 3);
            $m['ctype'] = lab58_img_u8($data, 5) === 1 ? 'gray' : 'rgb';
        } elseif ($marker === 0xDA) {
            break;
        }
    }
    if ($m['w'] < 1 || $m['h'] < 1) return null;
    if ($m['q'] === 0) {
        $m['q'] = 92;
        for ($q = 1; $q <= 100 && $dqt !== ''; $q++) if (lab58_img_dqt($q) === $dqt) { $m['q'] = $q; break; }
    }
    return lab58_img_normalize($m);
}

function lab58_img_dec_png(string $c): ?array
{
    $m = ['fmt' => 'PNG', 'w' => 0, 'h' => 0, 'ctype' => 'rgb', 'dpi' => 72, 'q' => 0, 'cx' => 100, 'exif' => [], 'text' => [], 'colors' => 0];
    $len = strlen($c);
    $pos = 8;
    for ($guard = 0; $guard < LAB58_IMG_MAX_BLOCKS && $pos + 8 <= $len; $guard++) {
        $clen = lab58_img_u32be($c, $pos);
        $type = substr($c, $pos + 4, 4);
        if ($clen > $len - $pos - 8) break;
        $data = substr($c, $pos + 8, $clen);
        $pos += 12 + $clen;
        if ($type === 'IHDR' && $clen >= 13) {
            $m['w'] = lab58_img_u32be($data, 0);
            $m['h'] = lab58_img_u32be($data, 4);
            $m['depth'] = lab58_img_u8($data, 8);
            $m['ctype'] = [0 => 'gray', 2 => 'rgb', 3 => 'palette', 4 => 'graya', 6 => 'rgba'][lab58_img_u8($data, 9)] ?? 'rgb';
        } elseif ($type === 'pHYs' && lab58_img_u8($data, 8) === 1) {
            $m['dpi'] = (int)round(lab58_img_u32be($data, 0) * 0.0254);
        } elseif ($type === 'PLTE') {
            $m['colors'] = intdiv($clen, 3);
        } elseif ($type === 'tEXt' || $type === 'iTXt') {
            $parts = explode("\x00", $data, $type === 'tEXt' ? 2 : 5);
            $key = lab58_img_clean((string)$parts[0], 79);
            if ($key !== '') $m['text'][$key] = lab58_img_clean((string)end($parts), 500);
        } elseif ($type === 'eXIf') {
            $m['exif'] = lab58_exif_decode($data);
        } elseif ($type === 'edUc' && ($pl = lab58_img_payload_read($data)) !== null) {
            $m['cx'] = $pl['cx'];
        } elseif ($type === 'IEND') {
            break;
        }
    }
    return $m['w'] >= 1 && $m['h'] >= 1 ? lab58_img_normalize($m) : null;
}

function lab58_img_dec_gif(string $c): ?array
{
    $m = ['fmt' => 'GIF', 'w' => lab58_img_u16le($c, 6), 'h' => lab58_img_u16le($c, 8), 'ctype' => 'palette', 'colors' => 256, 'q' => 0, 'cx' => 100, 'text' => []];
    $packed = lab58_img_u8($c, 10);
    $pos = 13;
    if (($packed & 0x80) !== 0) {
        $m['colors'] = 1 << (($packed & 7) + 1);
        $pos += 3 * $m['colors'];
    }
    $len = strlen($c);
    for ($guard = 0; $guard < LAB58_IMG_MAX_BLOCKS && $pos < $len; $guard++) {
        $b = lab58_img_u8($c, $pos);
        if ($b === 0x2C) {
            $flags = lab58_img_u8($c, $pos + 9);
            $pos += 11 + (($flags & 0x80) !== 0 ? 3 * (1 << (($flags & 7) + 1)) : 0);
        } elseif ($b === 0x21) {
            $label = lab58_img_u8($c, $pos + 1);
            $pos += 2;
        } else {
            break;
        }
        $data = '';
        for ($k = 0; $k < 400 && $pos < $len; $k++) {
            $size = lab58_img_u8($c, $pos);
            $pos++;
            if ($size === 0) break;
            $data .= substr($c, $pos, $size);
            $pos += $size;
        }
        if ($b !== 0x21) continue;
        if ($label === 0xFF && str_starts_with($data, 'EDUCANET1.0') && ($pl = lab58_img_payload_read(substr($data, 11))) !== null) $m['cx'] = $pl['cx'];
        if ($label === 0xFE) $m['text']['Comment'] = lab58_img_clean($data, 500);
    }
    return $m['w'] >= 1 && $m['h'] >= 1 ? lab58_img_normalize($m) : null;
}

function lab58_img_dec_webp(string $c): ?array
{
    $m = ['fmt' => 'WEBP', 'w' => 0, 'h' => 0, 'ctype' => 'rgb', 'q' => 0, 'cx' => 100, 'exif' => []];
    $len = strlen($c);
    $pos = 12;
    for ($guard = 0; $guard < LAB58_IMG_MAX_BLOCKS && $pos + 8 <= $len; $guard++) {
        $fourcc = substr($c, $pos, 4);
        $size = lab58_img_u32le($c, $pos + 4);
        if ($size > $len - $pos - 8) break;
        $data = substr($c, $pos + 8, $size);
        $pos += 8 + $size + ($size % 2);
        if ($fourcc === 'VP8 ' && substr($data, 3, 3) === "\x9d\x01\x2a") {
            $m['w'] = lab58_img_u16le($data, 6) & 0x3FFF;
            $m['h'] = lab58_img_u16le($data, 8) & 0x3FFF;
        } elseif ($fourcc === 'VP8X') {
            if ((lab58_img_u8($data, 0) & 0x10) !== 0) $m['ctype'] = 'rgba';
            $m['w'] = lab58_img_u24le($data, 4) + 1;
            $m['h'] = lab58_img_u24le($data, 7) + 1;
        } elseif ($fourcc === 'ALPH') {
            $m['ctype'] = 'rgba';
        } elseif ($fourcc === 'EXIF') {
            $m['exif'] = lab58_exif_decode($data);
        } elseif ($fourcc === 'EDUC' && ($pl = lab58_img_payload_read($data)) !== null) {
            $m['q'] = $pl['q'];
            $m['cx'] = $pl['cx'];
        }
    }
    if ($m['q'] === 0) $m['q'] = 75;
    return $m['w'] >= 1 && $m['h'] >= 1 ? lab58_img_normalize($m) : null;
}

function lab58_img_tail_payload(array $m, string $c): array
{
    $pl = lab58_img_payload_read(substr($c, -6));
    if ($pl !== null) {
        $m['q'] = $pl['q'];
        $m['cx'] = $pl['cx'];
    }
    return $m;
}

function lab58_img_dec_bmp(string $c): ?array
{
    $h = lab58_img_u32le($c, 22);
    if ($h >= 0x80000000) $h = 0x100000000 - $h;
    $bpp = lab58_img_u16le($c, 28);
    $m = ['fmt' => 'BMP', 'w' => lab58_img_u32le($c, 18), 'h' => $h, 'ctype' => $bpp === 32 ? 'rgba' : ($bpp <= 8 ? 'palette' : 'rgb'), 'q' => 0, 'cx' => 100,
        'dpi' => max(1, (int)round(lab58_img_u32le($c, 38) * 0.0254))];
    return $m['w'] >= 1 && $m['h'] >= 1 ? lab58_img_normalize(lab58_img_tail_payload($m, $c)) : null;
}

function lab58_img_dec_ico(string $c): ?array
{
    if (lab58_img_u16le($c, 4) < 1) return null;
    $m = ['fmt' => 'ICO', 'w' => lab58_img_u8($c, 6) ?: 256, 'h' => lab58_img_u8($c, 7) ?: 256, 'ctype' => 'rgba', 'q' => 0, 'cx' => 100];
    $png = lab58_img_dec_png(substr($c, lab58_img_u32le($c, 18), 4096));
    if ($png !== null) $m['cx'] = $png['cx'];
    return lab58_img_normalize($m);
}

function lab58_img_dec_tiff(string $c): ?array
{
    $m = ['fmt' => 'TIFF', 'w' => 0, 'h' => 0, 'ctype' => 'rgb', 'q' => 0, 'cx' => 100];
    $ifd = lab58_img_u32be($c, 4);
    $n = min(LAB58_IMG_MAX_BLOCKS, lab58_img_u16be($c, $ifd));
    $spp = 3;
    for ($i = 0; $i < $n; $i++) {
        $e = $ifd + 2 + $i * 12;
        $tag = lab58_img_u16be($c, $e);
        $value = lab58_img_u16be($c, $e + 2) === 3 ? lab58_img_u16be($c, $e + 8) : lab58_img_u32be($c, $e + 8);
        if ($tag === 256) $m['w'] = $value;
        elseif ($tag === 257) $m['h'] = $value;
        elseif ($tag === 277) $spp = $value;
        elseif ($tag === 282) $m['dpi'] = intdiv(lab58_img_u32be($c, $value), max(1, lab58_img_u32be($c, $value + 4)));
    }
    $m['ctype'] = [1 => 'gray', 2 => 'graya', 4 => 'rgba'][$spp] ?? 'rgb';
    return $m['w'] >= 1 && $m['h'] >= 1 ? lab58_img_normalize(lab58_img_tail_payload($m, $c)) : null;
}

function lab58_img_dec_svg(string $c): ?array
{
    // SVG = text, jehož první značka (po deklaraci XML, komentářích a DOCTYPE) je <svg> – HTML s vloženým SVG se nepočítá.
    $t = substr(ltrim($c, "\xEF\xBB\xBF \t\r\n"), 0, 8192);
    $t = (string)preg_replace('/^(?:<\?xml[^>]*\?>\s*|<!--.*?-->\s*|<!DOCTYPE\s+svg[^>]*>\s*)*/is', '', $t, 1);
    if (preg_match('/^<svg\b([^>]*)>/i', $t, $tag) !== 1 || !lab57_is_text($c)) return null;
    $unit = static function (string $attr) use ($tag): ?float {
        if (preg_match('/\b' . $attr . '\s*=\s*["\']\s*([\d.]+)\s*(px|pt|mm|cm|in|%)?/i', $tag[1], $m) !== 1 || ($m[2] ?? '') === '%') return null;
        return (float)$m[1] * ['' => 1, 'px' => 1, 'pt' => 4 / 3, 'mm' => 96 / 25.4, 'cm' => 96 / 2.54, 'in' => 96][strtolower($m[2] ?? '')];
    };
    $w = $unit('width');
    $h = $unit('height');
    if (($w === null || $h === null) && preg_match('/\bviewBox\s*=\s*["\']\s*[-\d.]+[\s,]+[-\d.]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $tag[1], $vb) === 1) {
        $w ??= (float)$vb[1];
        $h ??= (float)$vb[2];
    }
    return lab58_img_normalize(['fmt' => 'SVG', 'w' => (int)round($w ?? 100), 'h' => (int)round($h ?? 100), 'ctype' => 'rgba', 'depth' => 16, 'dpi' => 96, 'cx' => 5, 'svg' => $c]);
}

// ---------------------------------------------------------------------------
// Simulovaná velikost souboru, převod formátu, práce s VFS
// ---------------------------------------------------------------------------

/** Velikost souboru v bajtech podle rozměrů, formátu, kvality a „složitosti“ scény (deterministická). */
function lab58_img_calc_size(array $m, string $content): int
{
    $len = strlen($content);
    if ($m['fmt'] === 'SVG') return $len;
    $px = (float)$m['w'] * (float)$m['h'];
    $cx = $m['cx'] / 100;
    $q = ($m['q'] > 0 ? $m['q'] : ($m['fmt'] === 'WEBP' ? 75 : 92)) / 100;
    $jpeg = 0.02 + 0.28 * $q ** 3;
    $channels = ['gray' => 1, 'graya' => 2, 'rgb' => 3, 'rgba' => 4, 'palette' => 1][$m['ctype']];
    $bytes = match ($m['fmt']) {
        'JPEG' => $px * $jpeg * ($channels === 1 ? 0.7 : 1.0) * $cx,
        'WEBP' => $px * $jpeg * 0.7 * (lab58_img_has_alpha($m) ? 1.1 : 1.0) * $cx,
        'PNG', 'ICO' => $px * $channels * 0.55 * $cx,
        'GIF' => $px * 0.35 * $cx,
        'BMP' => (float)(intdiv($m['w'] * (lab58_img_has_alpha($m) ? 32 : 24) + 31, 32) * 4 * $m['h']),
        'TIFF' => $px * $channels,
        default => 0.0,
    };
    return $len + (int)round($bytes);
}

/** Přizpůsobí model cílovému formátu (co formát neumí – alfa, EXIF, paleta – se zahodí nebo převede). */
function lab58_img_as_format(array $m, string $fmt): array
{
    $alpha = lab58_img_has_alpha($m);
    $gray = in_array($m['ctype'], ['gray', 'graya'], true);
    $m['fmt'] = $fmt;
    if ($fmt === 'JPEG') $m['ctype'] = $gray ? 'gray' : 'rgb';
    if ($fmt === 'GIF') { $m['ctype'] = 'palette'; $m['colors'] = $m['colors'] ?: 256; }
    if ($fmt === 'WEBP' || $fmt === 'BMP') $m['ctype'] = $alpha ? 'rgba' : 'rgb';
    if ($fmt === 'ICO') $m['ctype'] = 'rgba';
    if ($fmt === 'TIFF' && $m['ctype'] === 'palette') $m['ctype'] = 'rgb';
    if (in_array($fmt, ['GIF', 'BMP', 'ICO', 'TIFF'], true)) $m['exif'] = [];
    if (in_array($fmt, ['BMP', 'ICO', 'TIFF'], true)) $m['text'] = [];
    if ($fmt !== 'JPEG' && $fmt !== 'WEBP') $m['q'] = 0;
    if ($m['depth'] > 8 && $fmt !== 'PNG' && $fmt !== 'TIFF') $m['depth'] = 8;
    $m['svg'] = '';
    return lab58_img_normalize($m);
}

/** Formát podle přípony (nebo předpony „png:“). null = neznámá přípona. */
function lab58_img_ext_format(string $ext): ?string
{
    return [
        'jpg' => 'JPEG', 'jpeg' => 'JPEG', 'jpe' => 'JPEG', 'jfif' => 'JPEG', 'png' => 'PNG', 'gif' => 'GIF', 'webp' => 'WEBP',
        'bmp' => 'BMP', 'ico' => 'ICO', 'tif' => 'TIFF', 'tiff' => 'TIFF', 'svg' => 'SVG',
    ][strtolower($ext)] ?? null;
}

function lab58_img_ext(string $path): string
{
    $base = Lab57Vfs::basename($path);
    $dot = strrpos($base, '.');
    return $dot === false || $dot === 0 ? '' : substr($base, $dot + 1);
}

/**
 * Načte obrázek ze VFS (s kontrolou práv).
 * @return array{model:?array,node:?array,content:string,err:?string} err: text chyby jako Linux, nebo 'improper' (není obrázek)
 */
function lab58_img_load(Lab57World $w, string $path): array
{
    $abs = $w->abs($path);
    $node = $w->canTraverse($abs) ? $w->fs->get($abs) : null;
    $err = null;
    $content = $node !== null ? $w->readFile($path, $err) : null;
    if ($node === null) $err = $w->canTraverse($abs) ? 'No such file or directory' : 'Permission denied';
    if ($content === null) return ['model' => null, 'node' => $node, 'content' => '', 'err' => $err ?? 'No such file or directory'];
    $model = lab58_img_parse($content);
    return ['model' => $model, 'node' => $node, 'content' => $content, 'err' => $model === null ? 'improper' : null];
}

/** Zapíše obrázek (práva jako writeFile; simulovaná velikost do klíče 's'). */
function lab58_img_store(Lab57World $w, string $path, array $m, ?string &$err = null, ?int $size = null): bool
{
    $content = lab58_img_encode($m);
    return lab58_img_store_raw($w, $path, $content, $size ?? lab58_img_calc_size(lab58_img_normalize($m), $content), $err);
}

function lab58_img_store_raw(Lab57World $w, string $path, string $content, int $size, ?string &$err = null): bool
{
    $abs = $w->abs($path);
    if (!$w->canTraverse($abs)) { $err = 'Permission denied'; return false; }
    $node = $w->fs->get($abs);
    $size = max(strlen($content), $size);
    if ($node !== null) {
        if (($node['t'] ?? '') === 'd') { $err = 'Is a directory'; return false; }
        if (!$w->can($node, 'w')) { $err = 'Permission denied'; return false; }
        $node['c'] = $content;
        $node['s'] = $size;
        $node['mt'] = $w->now;
        unset($node['x']);
        $w->fs->set($abs, $node);
        return true;
    }
    $parent = $w->fs->get(Lab57Vfs::dirname($abs));
    if ($parent === null) { $err = 'No such file or directory'; return false; }
    if (($parent['t'] ?? '') !== 'd') { $err = 'Not a directory'; return false; }
    if (!$w->can($parent, 'w') || !$w->can($parent, 'x')) { $err = 'Permission denied'; return false; }
    $owner = $w->effectiveUser();
    $w->fs->set($abs, ['t' => 'f', 'm' => $owner === 'root' ? 0644 : 0664, 'u' => $owner, 'g' => $w->primaryGroup($owner), 'mt' => $w->now, 'c' => $content, 's' => $size]);
    return true;
}

