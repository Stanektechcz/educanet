<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – grafika (LAB-07): exiftool, rename (Perl), popis obrázků pro file,
 * dlouhý výpis identify -verbose a generátory/kontroly obrázků pro úrovně.
 * Model obrázku je v linux_v58_cmd_media.php. Nic se nespouští, žádná síť; Perl ani skutečný
 * exiftool se nevolají – výrazy rename (s///, y///) se převádějí na PCRE s limitem navracení.
 * GPS souřadnice v generátorech jsou fiktivní (veřejná přírodní místa), jména fotografů vymyšlená.
 */

// ---------------------------------------------------------------------------
// Popis pro příkaz file (filtr file_type, kontrakt docs/V58_PLAN.md §3.2)
// ---------------------------------------------------------------------------

function lab58_img_file_desc(array $m): string
{
    $w = $m['w'];
    $h = $m['h'];
    $exif = $m['exif'];
    switch ($m['fmt']) {
        case 'PNG':
            $ct = ['gray' => $m['depth'] . '-bit grayscale', 'rgb' => $m['depth'] . '-bit/color RGB', 'rgba' => $m['depth'] . '-bit/color RGBA', 'palette' => $m['depth'] . '-bit colormap', 'graya' => $m['depth'] . '-bit gray+alpha'][$m['ctype']];
            return "PNG image data, $w x $h, $ct, non-interlaced";
        case 'JPEG':
            $out = 'JPEG image data, JFIF standard 1.01, resolution (DPI), density ' . $m['dpi'] . 'x' . $m['dpi'] . ', segment length 16';
            if ($exif !== []) {
                $parts = ['TIFF image data, big-endian, direntries=' . count($exif)];
                if (isset($exif['Make'])) $parts[] = 'manufacturer=' . $exif['Make'];
                if (isset($exif['Model'])) $parts[] = 'model=' . $exif['Model'];
                if (isset($exif['Orientation'])) $parts[] = 'orientation=' . ([1 => 'upper-left', 3 => 'lower-right', 6 => 'upper-right', 8 => 'lower-left'][(int)$exif['Orientation']] ?? 'upper-left');
                if (isset($exif['Software'])) $parts[] = 'software=' . $exif['Software'];
                if (isset($exif['ModifyDate'])) $parts[] = 'datetime=' . $exif['ModifyDate'];
                if (lab58_exif_has_gps($exif)) $parts[] = 'GPS-Data';
                $out .= ', Exif Standard: [' . implode(', ', $parts) . ']';
            }
            if ((string)($m['text']['Comment'] ?? '') !== '') $out .= ', comment: "' . $m['text']['Comment'] . '"';
            return $out . ", baseline, precision 8, {$w}x{$h}, components " . ($m['ctype'] === 'gray' ? 1 : 3);
        case 'GIF':
            return "GIF image data, version 89a, $w x $h";
        case 'WEBP':
            return "RIFF (little-endian) data, Web/P image, VP8 encoding, {$w}x{$h}, Scaling: [none]x[none], YUV color, decoders should clamp";
        case 'BMP':
            $bpp = lab58_img_has_alpha($m) ? 32 : 24;
            $ppm = (int)round($m['dpi'] / 0.0254);
            return "PC bitmap, Windows 3.x format, $w x $h x $bpp, image size " . (intdiv($w * $bpp + 31, 32) * 4 * $h) . ", resolution $ppm x $ppm px/m, cbSize " . (54 + intdiv($w * $bpp + 31, 32) * 4 * $h) . ', bits offset 54';
        case 'ICO':
            return "MS Windows icon resource - 1 icon, {$w}x{$h}, 32 bits/pixel";
        case 'TIFF':
            return "TIFF image data, big-endian, direntries=9, height=$h, bps=8, compression=none, PhotometricInterpretation=" . (in_array($m['ctype'], ['gray', 'graya'], true) ? 'BlackIsZero' : 'RGB') . ", width=$w";
        default:
            return 'SVG Scalable Vector Graphics image';
    }
}

// ---------------------------------------------------------------------------
// Registrace: filtr pro příkaz file (obrázky podle magických bajtů)
// ---------------------------------------------------------------------------

lab58_add_filter('file_type', static function (mixed $value, array $args): mixed {
    if (is_string($value) && $value !== '') return $value;
    $model = lab58_img_parse((string)($args['content'] ?? ''));
    return $model !== null ? lab58_img_file_desc($model) : $value;
});

// ---------------------------------------------------------------------------
// identify -verbose (dlouhý výpis)
// ---------------------------------------------------------------------------

/** Výběr z identify -verbose (pořadí a názvy polí jako ImageMagick 6). */
function lab58_im_verbose(Lab57Proc $p, string $name, array $m, int $size, int $mt, string $content, bool $im7): void
{
    $w = $m['w'];
    $h = $m['h'];
    $channels = in_array($m['ctype'], ['gray', 'graya'], true) ? ['gray'] : ['red', 'green', 'blue'];
    if (lab58_img_has_alpha($m)) $channels[] = 'alpha';
    $out = "Image:\n  Filename: $name\n" . ($im7 ? "  Permissions: rw-r--r--\n" : '') . '  Format: ' . lab58_im_format_name($m['fmt']) . "\n  Mime type: " . lab58_im_mime($m['fmt']) . "\n";
    $out .= '  Class: ' . ($m['ctype'] === 'palette' ? 'PseudoClass' : 'DirectClass') . "\n  Geometry: {$w}x{$h}+0+0\n";
    $out .= '  Resolution: ' . $m['dpi'] . 'x' . $m['dpi'] . "\n  Print size: " . sprintf('%gx%g', round($w / $m['dpi'], 4), round($h / $m['dpi'], 4)) . "\n  Units: PixelsPerInch\n";
    $out .= '  Colorspace: ' . lab58_im_colorspace($m) . "\n  Type: " . lab58_im_type($m) . "\n  Endianness: Undefined\n  Depth: " . ($m['fmt'] === 'SVG' ? 16 : $m['depth']) . "-bit\n  Channel depth:\n";
    foreach ($channels as $ch) $out .= '    ' . $ch . ': ' . ($m['fmt'] === 'SVG' ? 16 : $m['depth']) . "-bit\n";
    if ($m['ctype'] === 'palette') $out .= '  Colors: ' . ($m['colors'] ?: 256) . "\n";
    $out .= "  Rendering intent: Perceptual\n  Gamma: 0.454545\n  Background color: white\n  Compression: " . (['JPEG' => 'JPEG', 'PNG' => 'Zip', 'GIF' => 'LZW', 'WEBP' => 'WebP', 'ICO' => 'Zip', 'SVG' => 'Undefined'][$m['fmt']] ?? 'None') . "\n";
    if ($m['q'] > 0 && in_array($m['fmt'], ['JPEG', 'WEBP'], true)) $out .= '  Quality: ' . $m['q'] . "\n";
    $out .= '  Orientation: ' . lab58_im_orientation($m) . "\n  Properties:\n";
    $props = ['date:create' => date('c', $mt), 'date:modify' => date('c', $mt)] + lab58_im_exif_props($m['exif']);
    foreach ($m['text'] as $k => $v) $props[strtolower((string)$k) === 'comment' ? 'comment' : (string)$k] = (string)$v;
    if ($m['fmt'] === 'JPEG') $props += ['jpeg:colorspace' => $m['ctype'] === 'gray' ? '1' : '2', 'jpeg:sampling-factor' => $m['ctype'] === 'gray' ? '1x1' : '2x2,1x1,1x1'];
    $props['signature'] = hash('sha256', $content);
    ksort($props, SORT_STRING);
    foreach ($props as $k => $v) $out .= '    ' . $k . ': ' . $v . "\n";
    if ($m['exif'] !== []) $out .= "  Profiles:\n    Profile-exif: " . strlen(lab58_exif_encode($m['exif'])) . " bytes\n";
    $out .= "  Artifacts:\n    filename: $name\n    verbose: true\n  Tainted: False\n  Filesize: " . lab58_im_size($size) . "\n";
    $px = $w * $h;
    $out .= '  Number pixels: ' . ($px >= 1000 ? lab58_im_size($px) : $px . 'B') . "\n";
    $out = (string)preg_replace('/Number pixels: ([\d.]+[KMG]?)B/', 'Number pixels: $1', $out);
    $out .= "  User time: 0.010u\n  Elapsed time: 0:01.004\n  Version: " . ($im7 ? LAB58_IM7_VERSION : LAB58_IM_VERSION) . "\n";
    $p->out($out);
}

// ---------------------------------------------------------------------------
// exiftool 12.57 – čtení a zápis metadat
// ---------------------------------------------------------------------------

const LAB58_EXIFTOOL_VERSION = '12.57';
const LAB58_EXIF_WRITABLE = ['JPEG', 'PNG', 'WEBP'];
const LAB58_EXIF_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'tif', 'tiff', 'svg'];

/** Velikost jako exiftool (jednotky po 1000). */
function lab58_exiftool_size(int $b): string
{
    if ($b < 2000) return $b . ' bytes';
    if ($b < 10000) return sprintf('%.1f kB', $b / 1000);
    if ($b < 2000000) return sprintf('%.0f kB', $b / 1000);
    return $b < 10000000 ? sprintf('%.1f MB', $b / 1000000) : sprintf('%.0f MB', $b / 1000000);
}

function lab58_exiftool_num(float $x): string
{
    $s = rtrim(rtrim(sprintf('%.10f', $x), '0'), '.');
    return $s === '-0' ? '0' : $s;
}

/** Hodnota značky jako exiftool (čitelně), nebo s -n číselně. */
function lab58_exiftool_value(string $name, mixed $v, bool $num): string
{
    if (is_array($v)) {
        if ($name === 'GPSLatitude' || $name === 'GPSLongitude') {
            $dec = lab58_exif_rational($v[0]) + lab58_exif_rational($v[1]) / 60 + lab58_exif_rational($v[2]) / 3600;
            return $num ? lab58_exiftool_num($dec) : lab58_exif_dms_text($v);
        }
        $x = lab58_exif_rational($v[0]);
        if ($num) return lab58_exiftool_num($x);
        return match ($name) {
            'ExposureTime' => $x > 0 && $x < 0.25 ? '1/' . (int)round(1 / $x) : lab58_exiftool_num($x),
            'FNumber' => sprintf('%.1f', $x),
            'FocalLength' => sprintf('%.1f mm', $x),
            'GPSAltitude' => lab58_exiftool_num(round($x, 1)) . ' m',
            default => lab58_exiftool_num($x),
        };
    }
    if ($num) return (string)$v;
    return match ($name) {
        'Orientation' => [1 => 'Horizontal (normal)', 2 => 'Mirror horizontal', 3 => 'Rotate 180', 4 => 'Mirror vertical', 5 => 'Mirror horizontal and rotate 270 CW', 6 => 'Rotate 90 CW', 7 => 'Mirror horizontal and rotate 90 CW', 8 => 'Rotate 270 CW'][(int)$v] ?? 'Unknown (' . $v . ')',
        'ResolutionUnit' => [1 => 'None', 2 => 'inches', 3 => 'cm'][(int)$v] ?? 'Unknown (' . $v . ')',
        'GPSLatitudeRef' => ['N' => 'North', 'S' => 'South'][(string)$v] ?? (string)$v,
        'GPSLongitudeRef' => ['E' => 'East', 'W' => 'West'][(string)$v] ?? (string)$v,
        'GPSAltitudeRef' => (int)$v === 1 ? 'Below Sea Level' : 'Above Sea Level',
        default => (string)$v,
    };
}

/**
 * Značky souboru v pořadí jako exiftool.
 * @return list<array{0:string,1:string,2:string,3:string,4:string}> [skupina, podskupina, značka, popis, hodnota]
 */
function lab58_exiftool_rows(string $disp, array $node, ?array $m, string $content, bool $num): array
{
    $rows = [];
    $add = static function (string $g0, string $g1, string $tag, string $desc, string|int|float $value) use (&$rows): void { $rows[] = [$g0, $g1, $tag, $desc, (string)$value]; };
    $date = date('Y:m:d H:i:sP', (int)($node['mt'] ?? 0));
    $slash = strrpos($disp, '/');
    $size = Lab57Vfs::size($node);
    $add('ExifTool', 'ExifTool', 'ExifToolVersion', 'ExifTool Version Number', LAB58_EXIFTOOL_VERSION);
    $add('File', 'System', 'FileName', 'File Name', $slash === false ? $disp : substr($disp, $slash + 1));
    $add('File', 'System', 'Directory', 'Directory', $slash === false ? '.' : ($slash === 0 ? '/' : substr($disp, 0, $slash)));
    $add('File', 'System', 'FileSize', 'File Size', $num ? $size : lab58_exiftool_size($size));
    foreach (['FileModifyDate' => 'File Modification Date/Time', 'FileAccessDate' => 'File Access Date/Time', 'FileInodeChangeDate' => 'File Inode Change Date/Time'] as $tag => $desc) $add('File', 'System', $tag, $desc, $date);
    $add('File', 'System', 'FilePermissions', 'File Permissions', $num ? sprintf('100%o', (int)($node['m'] ?? 0644) & 0777) : lab57_mode_string($node));
    if ($m === null) {
        $lines = substr_count($content, "\n");
        foreach ([['FileType', 'File Type', 'TXT'], ['FileTypeExtension', 'File Type Extension', 'txt'], ['MIMEType', 'MIME Type', 'text/plain'], ['MIMEEncoding', 'MIME Encoding', preg_match('/[^\x00-\x7F]/', $content) === 1 ? 'utf-8' : 'us-ascii'], ['Newlines', 'Newlines', 'Unix LF'], ['LineCount', 'Line Count', $lines], ['WordCount', 'Word Count', count(preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY) ?: [])]] as [$t, $d, $v]) $add('File', 'File', $t, $d, $v);
        return $rows;
    }
    $w = $m['w'];
    $h = $m['h'];
    $add('File', 'File', 'FileType', 'File Type', $m['fmt']);
    $add('File', 'File', 'FileTypeExtension', 'File Type Extension', ['JPEG' => 'jpg', 'TIFF' => 'tif'][$m['fmt']] ?? strtolower($m['fmt']));
    $add('File', 'File', 'MIMEType', 'MIME Type', lab58_im_mime($m['fmt']));
    $exifRows = static function () use ($add, $m, $num): void {
        if ($m['exif'] === []) return;
        $add('EXIF', 'IFD0', 'ExifByteOrder', 'Exif Byte Order', $num ? 'MM' : 'Big-endian (Motorola, MM)');
        foreach (lab58_exif_table() as $name => [, , , $group, $desc]) if (array_key_exists($name, $m['exif'])) $add('EXIF', $group, $name, $desc, lab58_exiftool_value($name, $m['exif'][$name], $num));
    };
    $gray = in_array($m['ctype'], ['gray', 'graya'], true);
    $ppm = (int)round($m['dpi'] / 0.0254);
    switch ($m['fmt']) {
        case 'JPEG':
            $add('JFIF', 'JFIF', 'JFIFVersion', 'JFIF Version', '1.01');
            if (!isset($m['exif']['XResolution'])) {
                $add('JFIF', 'JFIF', 'ResolutionUnit', 'Resolution Unit', $num ? '1' : 'inches');
                $add('JFIF', 'JFIF', 'XResolution', 'X Resolution', $m['dpi']);
                $add('JFIF', 'JFIF', 'YResolution', 'Y Resolution', $m['dpi']);
            }
            $exifRows();
            if ((string)($m['text']['Comment'] ?? '') !== '') $add('File', 'File', 'Comment', 'Comment', (string)$m['text']['Comment']);
            foreach ([['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h], ['EncodingProcess', 'Encoding Process', $num ? 0 : 'Baseline DCT, Huffman coding'], ['BitsPerSample', 'Bits Per Sample', 8], ['ColorComponents', 'Color Components', $gray ? 1 : 3]] as [$t, $d, $v]) $add('File', 'File', $t, $d, $v);
            if (!$gray) $add('File', 'File', 'YCbCrSubSampling', 'Y Cb Cr Sub Sampling', $num ? '2 2' : 'YCbCr4:2:0 (2 2)');
            break;
        case 'PNG':
            $ct = ['rgb' => [2, 'RGB'], 'rgba' => [6, 'RGB with Alpha'], 'gray' => [0, 'Grayscale'], 'graya' => [4, 'Grayscale with Alpha'], 'palette' => [3, 'Palette']][$m['ctype']];
            foreach ([['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h], ['BitDepth', 'Bit Depth', $m['depth']], ['ColorType', 'Color Type', $num ? $ct[0] : $ct[1]], ['Compression', 'Compression', $num ? 0 : 'Deflate/Inflate'], ['Filter', 'Filter', $num ? 0 : 'Adaptive'], ['Interlace', 'Interlace', $num ? 0 : 'Noninterlaced'], ['PixelsPerUnitX', 'Pixels Per Unit X', $ppm], ['PixelsPerUnitY', 'Pixels Per Unit Y', $ppm], ['PixelUnits', 'Pixel Units', $num ? 1 : 'meters']] as [$t, $d, $v]) $add('PNG', 'PNG', $t, $d, $v);
            foreach ($m['text'] as $k => $v) $add('PNG', 'PNG', str_replace(' ', '', ucwords((string)$k)), (string)$k, (string)$v);
            $exifRows();
            break;
        case 'GIF':
            foreach ([['GIFVersion', 'GIF Version', '89a'], ['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h], ['HasColorMap', 'Has Color Map', $num ? 1 : 'Yes'], ['ColorResolutionDepth', 'Color Resolution Depth', 8], ['BitsPerPixel', 'Bits Per Pixel', lab58_img_gif_bits($m['colors'] ?: 256)], ['BackgroundColor', 'Background Color', 0]] as [$t, $d, $v]) $add('GIF', 'GIF', $t, $d, $v);
            if ((string)($m['text']['Comment'] ?? '') !== '') $add('File', 'File', 'Comment', 'Comment', (string)$m['text']['Comment']);
            break;
        case 'WEBP':
            foreach ([['VP8Version', 'VP8 Version', $num ? 0 : '0 (bicubic reconstruction, normal loop)'], ['ImageWidth', 'Image Width', $w], ['HorizontalScale', 'Horizontal Scale', 0], ['ImageHeight', 'Image Height', $h], ['VerticalScale', 'Vertical Scale', 0]] as [$t, $d, $v]) $add('RIFF', 'RIFF', $t, $d, $v);
            $exifRows();
            break;
        case 'BMP':
            $bpp = lab58_img_has_alpha($m) ? 32 : 24;
            foreach ([['BMPVersion', 'BMP Version', $num ? 40 : 'Windows V3'], ['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h], ['Planes', 'Planes', 1], ['BitDepth', 'Bit Depth', $bpp], ['Compression', 'Compression', $num ? 0 : 'None'], ['ImageLength', 'Image Length', intdiv($w * $bpp + 31, 32) * 4 * $h], ['PixelsPerMeterX', 'Pixels Per Meter X', $ppm], ['PixelsPerMeterY', 'Pixels Per Meter Y', $ppm]] as [$t, $d, $v]) $add('File', 'File', $t, $d, $v);
            break;
        case 'TIFF':
            $spp = ['gray' => 1, 'graya' => 2, 'rgba' => 4][$m['ctype']] ?? 3;
            foreach ([['ExifByteOrder', 'Exif Byte Order', $num ? 'MM' : 'Big-endian (Motorola, MM)'], ['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h], ['BitsPerSample', 'Bits Per Sample', implode(' ', array_fill(0, $spp, 8))], ['Compression', 'Compression', $num ? 1 : 'Uncompressed'], ['PhotometricInterpretation', 'Photometric Interpretation', $spp <= 2 ? ($num ? 1 : 'BlackIsZero') : ($num ? 2 : 'RGB')], ['SamplesPerPixel', 'Samples Per Pixel', $spp], ['XResolution', 'X Resolution', $m['dpi']], ['YResolution', 'Y Resolution', $m['dpi']], ['ResolutionUnit', 'Resolution Unit', $num ? 2 : 'inches']] as [$t, $d, $v]) $add('EXIF', 'IFD0', $t, $d, $v);
            break;
        case 'SVG':
            foreach ([['Xmlns', 'Xmlns', 'http://www.w3.org/2000/svg'], ['ImageWidth', 'Image Width', $w], ['ImageHeight', 'Image Height', $h]] as [$t, $d, $v]) $add('SVG', 'SVG', $t, $d, $v);
            break;
    }
    if ($m['fmt'] !== 'ICO') {
        $mp = $w * $h / 1000000;
        $add('Composite', 'Composite', 'ImageSize', 'Image Size', $num ? "$w $h" : "{$w}x{$h}");
        $add('Composite', 'Composite', 'Megapixels', 'Megapixels', $num ? lab58_exiftool_num($mp) : sprintf('%.' . ($mp >= 1 ? 1 : ($mp >= 0.001 ? 3 : 6)) . 'f', $mp));
    }
    $gps = lab58_exif_gps_decimal($m['exif']);
    if ($gps !== null) {
        $exif = $m['exif'];
        if (isset($exif['GPSAltitude'])) {
            $alt = lab58_exif_rational($exif['GPSAltitude'][0]) * ((int)($exif['GPSAltitudeRef'] ?? 0) === 1 ? -1 : 1);
            $add('Composite', 'Composite', 'GPSAltitude', 'GPS Altitude', $num ? lab58_exiftool_num($alt) : lab58_exiftool_num(round(abs($alt), 1)) . ' m ' . ($alt < 0 ? 'Below' : 'Above') . ' Sea Level');
        }
        $lat = lab58_exif_dms_text($exif['GPSLatitude']) . ' ' . ($exif['GPSLatitudeRef'] ?? 'N');
        $lon = lab58_exif_dms_text($exif['GPSLongitude']) . ' ' . ($exif['GPSLongitudeRef'] ?? 'E');
        $add('Composite', 'Composite', 'GPSLatitude', 'GPS Latitude', $num ? lab58_exiftool_num($gps[0]) : $lat);
        $add('Composite', 'Composite', 'GPSLongitude', 'GPS Longitude', $num ? lab58_exiftool_num($gps[1]) : $lon);
        $add('Composite', 'Composite', 'GPSPosition', 'GPS Position', $num ? lab58_exiftool_num($gps[0]) . ' ' . lab58_exiftool_num($gps[1]) : $lat . ', ' . $lon);
    }
    return $rows;
}

/** Vypíše vybrané značky (bez -a jen poslední výskyt stejné značky – jako exiftool). */
function lab58_exiftool_print(Lab57Proc $p, array $rows, array $sel, array $o): int
{
    $picked = lab58_exiftool_pick($rows, $sel, $o['a']);
    foreach ($picked as [$g0, , $tag, $desc, $value]) {
        $line = $o['S'] ? $tag . ': ' . $value : str_pad($o['s'] ? $tag : $desc, 32) . ': ' . $value;
        $p->line(($o['G'] ? str_pad('[' . $g0 . ']', 16) : '') . $line);
    }
    return count($picked);
}

function lab58_exiftool_pick(array $rows, array $sel, bool $all): array
{
    $picked = [];
    foreach ($rows as $row) {
        if ($sel === []) { $picked[] = $row; continue; }
        foreach ($sel as [$group, $tag]) {
            if ($group !== null && !in_array($group, [strtolower($row[0]), strtolower($row[1])], true)) continue;
            if ($tag === 'all' || $tag === '*' || $tag === strtolower($row[2])) { $picked[] = $row; break; }
        }
    }
    if (!$all) {
        $last = [];
        foreach ($picked as $i => $row) $last[strtolower($row[2])] = $i;
        $picked = array_values(array_filter($picked, static fn(array $row, int $i): bool => $last[strtolower($row[2])] === $i, ARRAY_FILTER_USE_BOTH));
    }
    return $picked;
}

/**
 * Připraví zápisy: [skupina|null, značka, hodnota|null] → operace nad modelem. Varování vypíše.
 * @return list<array{0:string,1:?string,2:mixed}> [druh (del_group|del|set), skupina/značka, hodnota]
 */
function lab58_exiftool_prepare(Lab57Proc $p, array $writes, bool $num): array
{
    $table = lab58_exif_table();
    $names = [];
    foreach (array_keys($table) as $name) $names[strtolower($name)] = $name;
    $ops = [];
    foreach ($writes as [$group, $tag, $value]) {
        $tagL = strtolower($tag);
        if ($tagL === 'all' || $tagL === '*') {
            if ($value !== null) { $p->err("Warning: Can't set value of all tags\n"); continue; }
            $ops[] = ['del_group', $group ?? 'all', null];
            continue;
        }
        if ($tagL === 'comment') { $ops[] = $value === null ? ['del', 'Comment', null] : ['set', 'Comment', lab58_img_clean($value, 500)]; continue; }
        $name = $names[$tagL] ?? null;
        if ($name === null) { $p->err("Warning: Tag '$tag' is not defined\n"); continue; }
        if ($value === null) { $ops[] = ['del', $name, null]; continue; }
        [, $type, $count, $grp] = $table[$name];
        $bad = "Warning: Can't convert " . $grp . ':' . $name . " (not in PrintConv)\n";
        if (in_array($name, ['ModifyDate', 'DateTimeOriginal', 'CreateDate'], true) && preg_match('/^\d{4}:\d{2}:\d{2} \d{2}:\d{2}:\d{2}$/', $value) !== 1) {
            $p->err('Warning: Invalid date/time (use YYYY:mm:dd HH:MM:SS[.ss][+/-HH:MM|Z]) in ' . $grp . ':' . $name . " (PrintConvInv)\n");
            continue;
        }
        if ($type === 'a') {
            $v = lab58_img_clean($value, 250);
            if (str_ends_with($name, 'Ref')) $v = strtoupper(substr($v, 0, 1));
            if (($name === 'GPSLatitudeRef' && !in_array($v, ['N', 'S'], true)) || ($name === 'GPSLongitudeRef' && !in_array($v, ['E', 'W'], true))) { $p->err($bad); continue; }
            $ops[] = ['set', $name, $v];
            continue;
        }
        if ($type === 's') {
            $map = $name === 'Orientation' ? ['horizontal (normal)' => 1, 'rotate 180' => 3, 'rotate 90 cw' => 6, 'rotate 270 cw' => 8] : ($name === 'GPSAltitudeRef' ? ['above sea level' => 0, 'below sea level' => 1] : []);
            $v = ctype_digit($value) ? (int)$value : ($map[strtolower($value)] ?? null);
            if ($v === null || ($name === 'Orientation' && ($v < 1 || $v > 8))) { $p->err($bad); continue; }
            $ops[] = ['set', $name, $v];
            continue;
        }
        if (preg_match('#^\s*(-?\d+(?:\.\d+)?)(?:\s*/\s*(\d+))?\s*([NSEW])?\s*$#i', $value, $mm) !== 1) { $p->err($bad); continue; }
        $x = (float)$mm[1] / max(1, (int)($mm[2] ?? 1));
        if ($count === 3) {
            if (abs($x) > ($name === 'GPSLatitude' ? 90 : 180)) { $p->err($bad); continue; }
            $ops[] = ['set', $name, lab58_exif_dms($x)];
            $ref = strtoupper((string)($mm[3] ?? '')) ?: ($name === 'GPSLatitude' ? ($x < 0 ? 'S' : 'N') : ($x < 0 ? 'W' : 'E'));
            $ops[] = ['set', $name . 'Ref', $ref];
            continue;
        }
        $ops[] = ['set', $name, [isset($mm[2]) && $mm[2] !== '' ? [(int)$mm[1], (int)$mm[2]] : [(int)round(abs($x) * 1000), 1000]]];
    }
    return $ops;
}

function lab58_exiftool_apply(array $m, array $ops): array
{
    foreach ($ops as [$kind, $what, $value]) {
        if ($kind === 'del_group') {
            $g = strtolower((string)$what);
            if ($g === 'all') { $m['exif'] = []; $m['text'] = []; }
            elseif ($g === 'exif') $m['exif'] = [];
            elseif ($g === 'gps') foreach (array_keys($m['exif']) as $k) { if (str_starts_with((string)$k, 'GPS')) unset($m['exif'][$k]); }
            elseif (in_array($g, ['png', 'file'], true)) $m['text'] = [];
            continue;
        }
        if ($what === 'Comment') {
            if ($kind === 'del') unset($m['text']['Comment']);
            else $m['text']['Comment'] = $value;
            continue;
        }
        if ($kind === 'del') unset($m['exif'][$what]);
        else $m['exif'][$what] = $value;
    }
    return lab58_img_normalize($m);
}

/** Rozbalí argumenty na seznam souborů (složky: jen obrázky, s -r i podsložky). @return array{0:list<string>,1:int} */
function lab58_exiftool_targets(Lab57World $w, array $files, bool $recurse, ?string $ext): array
{
    $out = [];
    $dirs = 0;
    foreach ($files as $file) {
        $abs = $w->abs($file);
        if (!$w->fs->isDir($abs)) { $out[] = $file; continue; }
        $queue = [[$abs, rtrim($file, '/')]];
        while ($queue !== [] && count($out) < LAB58_IM_MAX_FILES) {
            [$dirAbs, $dirDisp] = array_shift($queue);
            $dirs++;
            foreach ($w->fs->children($dirAbs) as $name) {
                $childAbs = ($dirAbs === '/' ? '' : $dirAbs) . '/' . $name;
                $childDisp = ($dirDisp === '' ? '' : $dirDisp . '/') . $name;
                if ($w->fs->isDir($childAbs)) { if ($recurse && !str_starts_with($name, '.')) $queue[] = [$childAbs, $childDisp]; continue; }
                $e = strtolower(lab58_img_ext($name));
                if ($ext !== null ? $e === strtolower(ltrim($ext, '.')) : in_array($e, LAB58_EXIF_EXTS, true)) $out[] = $childDisp;
            }
        }
    }
    return [$out, $dirs];
}

function lab58_exiftool_count(Lab57Proc $p, int $n, string $what): void
{
    $p->line(str_pad((string)$n, 5, ' ', STR_PAD_LEFT) . ' ' . $what);
}

function lab58_exiftool_open_error(Lab57Proc $p, array $loaded, string $file): void
{
    $p->err(($loaded['err'] === 'Permission denied' ? 'Error: Error opening file - ' : 'Error: File not found - ') . $file . "\n");
    lab57_error_tip($p->w, (string)$loaded['err'], $file);
}

function lab58_exiftool_read(Lab57Proc $p, array $targets, int $dirs, array $sel, array $o): int
{
    $multi = count($targets) > 1 || $dirs > 0;
    $read = 0;
    $failed = 0;
    $json = [];
    foreach ($targets as $t) {
        $loaded = lab58_img_load($p->w, $t);
        if ($loaded['node'] === null || !in_array($loaded['err'], [null, 'improper'], true)) { lab58_exiftool_open_error($p, $loaded, $t); $failed++; continue; }
        if ($loaded['model'] === null && !lab57_is_text($loaded['content'])) { $p->err('Error: Unknown file type - ' . $t . "\n"); $failed++; continue; }
        $rows = lab58_exiftool_rows($t, $loaded['node'], $loaded['model'], $loaded['content'], $o['n']);
        $read++;
        if ($o['j']) {
            $lines = ['  "SourceFile": ' . json_encode($t, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
            foreach (lab58_exiftool_pick($rows, $sel, $o['a']) as [, , $tag, , $value]) {
                $plain = is_numeric($value) && ($value === '0' || !str_starts_with($value, '0') || str_starts_with($value, '0.'));
                $lines[] = '  "' . $tag . '": ' . ($plain ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
            $json[] = "{\n" . implode(",\n", $lines) . "\n}";
            continue;
        }
        if ($multi) $p->line('======== ' . $t);
        lab58_exiftool_print($p, $rows, $sel, $o);
    }
    if ($json !== []) $p->line('[' . implode(",\n", $json) . ']');
    if ($multi) {
        if ($dirs > 0) lab58_exiftool_count($p, $dirs, 'directories scanned');
        lab58_exiftool_count($p, $read, 'image files read');
        if ($failed > 0) lab58_exiftool_count($p, $failed, 'files could not be read');
    }
    return $failed > 0 ? 1 : 0;
}

/** Zápis metadat: jako skutečný exiftool nechá originál jako soubor_original (pokud už neexistuje). */
function lab58_exiftool_write(Lab57Proc $p, array $targets, int $dirs, array $writes, array $o): int
{
    $w = $p->w;
    $ops = lab58_exiftool_prepare($p, $writes, $o['n']);
    if ($ops === []) { $p->err("Nothing to do.\n"); return 1; }
    $updated = 0;
    $unchanged = 0;
    $errors = 0;
    foreach ($targets as $t) {
        $loaded = lab58_img_load($w, $t);
        if ($loaded['node'] === null || !in_array($loaded['err'], [null, 'improper'], true)) { lab58_exiftool_open_error($p, $loaded, $t); $errors++; continue; }
        $m = $loaded['model'];
        if ($m === null || !in_array($m['fmt'], LAB58_EXIF_WRITABLE, true)) {
            $p->err('Error: ' . ($m === null ? 'Unknown file type' : "Can't currently write " . $m['fmt'] . ' files') . ' - ' . $t . "\n");
            $errors++;
            continue;
        }
        $new = lab58_exiftool_apply($m, $ops);
        if ($new == $m) { $unchanged++; continue; }
        $abs = $w->abs($t);
        if (!$w->canModifyDir(Lab57Vfs::dirname($abs))) {
            $p->err('Error: Error creating output file - ' . $t . "_exiftool_tmp\n");
            lab57_error_tip($w, 'Permission denied', Lab57Vfs::dirname($abs));
            $errors++;
            continue;
        }
        $node = $loaded['node'];
        if (!$o['overwrite'] && !$w->fs->exists($abs . '_original')) $w->fs->set($abs . '_original', $node);
        $content = lab58_img_encode($new);
        $node['s'] = max(strlen($content), Lab57Vfs::size($node) - strlen($loaded['content']) + strlen($content));
        $node['c'] = $content;
        $node['mt'] = $w->now;
        unset($node['x']);
        $w->fs->set($abs, $node);
        $updated++;
    }
    if (!$o['q']) {
        if ($dirs > 0) lab58_exiftool_count($p, $dirs, 'directories scanned');
        if ($updated > 0 || ($unchanged === 0 && $errors === 0)) lab58_exiftool_count($p, $updated, 'image files updated');
        if ($unchanged > 0) lab58_exiftool_count($p, $unchanged, 'image files unchanged');
        if ($errors > 0) lab58_exiftool_count($p, $errors, "files weren't updated due to errors");
    }
    if ($updated > 0 && !$o['overwrite']) $w->tip(tr('exiftool nechal zálohu původního souboru s příponou _original – i v ní jsou stará metadata. Bez zálohy: -overwrite_original.'));
    return $errors > 0 ? 1 : 0;
}

function lab58_cmd_exiftool(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_slice($argv, 1));
    if ($args === []) {
        $p->out("Syntax:  exiftool [OPTIONS] FILE\n\nConsult the exiftool documentation for a full list of options.\n");
        $w->tip(tr('exiftool vypíše metadata: exiftool foto.jpg. Polohu GPS smažeš: exiftool -gps:all= foto.jpg → man exiftool'));
        return 1;
    }
    $o = ['s' => false, 'S' => false, 'n' => false, 'a' => false, 'G' => false, 'r' => false, 'q' => false, 'j' => false, 'overwrite' => false];
    $sel = [];
    $writes = [];
    $files = [];
    $ext = null;
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-ver') { $p->line(LAB58_EXIFTOOL_VERSION); return 0; }
        if (strlen($a) < 2 || $a[0] !== '-') { $files[] = $a; continue; }
        $body = substr($a, 1);
        if (isset($o[$body]) && strlen($body) === 1) { $o[$body] = true; continue; }
        if ($body === 's2' || $body === 'json') { $o[$body === 'json' ? 'j' : 'S'] = true; continue; }
        if (in_array($body, ['P', 'm', 'F', 'fast', 'fast2', 'u', 'U', 'E', 'e'], true)) continue;
        if ($body === 'overwrite_original' || $body === 'overwrite_original_in_place') { $o['overwrite'] = true; continue; }
        if ($body === 'ext' || $body === 'extension') { $ext = (string)($args[++$i] ?? ''); continue; }
        if (preg_match('/^(?:([A-Za-z0-9_]{1,30}):)?([A-Za-z0-9_]{1,40})=(.*)$/s', $body, $mm) === 1) { $writes[] = [$mm[1] !== '' ? strtolower($mm[1]) : null, $mm[2], $mm[3] === '' ? null : $mm[3]]; continue; }
        if (preg_match('/^(?:([A-Za-z0-9_]{1,30}):)?([A-Za-z0-9_]{1,40}|\*)$/', $body, $mm) === 1) { $sel[] = [$mm[1] !== '' ? strtolower($mm[1]) : null, strtolower($mm[2])]; continue; }
        $p->err('Error: Invalid TAG name: "' . $body . "\"\n");
        $w->tip(tr('Značka se píše např. -Make, -GPSLatitude nebo -gps:all; zápis -Artist="Jméno". → man exiftool'));
        return 1;
    }
    if ($files === []) {
        $p->err("No file specified\n");
        $w->tip(tr('Na konec přidej soubor nebo složku, např. exiftool -gps:all fotky/ → man exiftool'));
        return 1;
    }
    [$targets, $dirs] = lab58_exiftool_targets($w, $files, $o['r'], $ext);
    return $writes !== [] ? lab58_exiftool_write($p, $targets, $dirs, $writes, $o) : lab58_exiftool_read($p, $targets, $dirs, $sel, $o);
}

// ---------------------------------------------------------------------------
// rename (Perl; balík rename = File::Rename 2.01): s///, y///, tr///, $_ = lc/uc
// ---------------------------------------------------------------------------

const LAB58_RENAME_USAGE = "Usage: rename [-h|-m|-V] [-v] [-0] [-n] [-f] [-d] [-u [enc]] [-e|-E perlexpr]*|perlexpr [files]\n";

/** @return list<array>|string příkazy, nebo chybová hláška ve stylu Perlu */
function lab58_rename_compile(string $code): array|string
{
    $stmts = [];
    $n = strlen($code);
    $i = 0;
    while ($i < $n) {
        while ($i < $n && (ctype_space($code[$i]) || $code[$i] === ';')) $i++;
        if ($i >= $n) break;
        $rest = substr($code, $i);
        if (preg_match('/^\$_\s*=\s*(lc|uc)(?:\s*\(\s*\$_\s*\)|\s+\$_)?\s*(?=;|$)/', $rest, $mm) === 1) { $stmts[] = [$mm[1]]; $i += strlen($mm[0]); continue; }
        if (preg_match('/^(s|y|tr)([^\w\s;])/', $rest, $mm) !== 1) return 'syntax error at (user-supplied code), near "' . substr($rest, 0, 20) . '"';
        $op = $mm[1] === 'tr' ? 'y' : $mm[1];
        $d = $mm[2];
        if (in_array($d, ['{', '(', '[', '<'], true)) return 'Závorkové oddělovače s{…}{…} simulace nepodporuje – použij s/…/…/';
        $i += strlen($mm[1]) + 1;
        $parts = [];
        for ($k = 0; $k < 2; $k++) {
            $buf = '';
            while ($i < $n && $code[$i] !== $d) {
                if ($code[$i] === '\\' && $i + 1 < $n && $code[$i + 1] === $d) { $buf .= $d; $i += 2; continue; }
                if ($code[$i] === '\\' && $i + 1 < $n) { $buf .= $code[$i] . $code[$i + 1]; $i += 2; continue; }
                $buf .= $code[$i++];
            }
            if ($i >= $n) return ($op === 's' ? 'Substitution ' : 'Transliteration ') . ($k === 0 ? 'pattern' : 'replacement') . ' not terminated';
            $parts[] = $buf;
            $i++;
        }
        preg_match('/^[a-z]*/', substr($code, $i), $fm);
        $i += strlen($fm[0]);
        $stmts[] = [$op, $parts[0], $parts[1], $fm[0]];
    }
    return $stmts === [] ? 'syntax error at (user-supplied code), near ""' : $stmts;
}

/** Náhrada jako v Perlu: $1, ${1}, $&, \1, \U…\E, \L…\E, \u, \l. */
function lab58_rename_subst(string $repl, array $m): string
{
    $out = '';
    $mode = '';
    $once = '';
    $emit = static function (string $s) use (&$out, &$mode, &$once): void {
        if ($s === '') return;
        if ($once !== '') {
            $first = mb_substr($s, 0, 1);
            $s = ($once === 'u' ? mb_strtoupper($first) : mb_strtolower($first)) . mb_substr($s, 1);
            $once = '';
        }
        $out .= $mode === 'U' ? mb_strtoupper($s) : ($mode === 'L' ? mb_strtolower($s) : $s);
    };
    $lit = '';
    $n = strlen($repl);
    for ($i = 0; $i < $n; $i++) {
        $c = $repl[$i];
        $special = ($c === '\\' && $i + 1 < $n) || ($c === '$' && preg_match('/^\$(&|\{\d+\}|\d+)/', substr($repl, $i)) === 1);
        if (!$special) { $lit .= $c; continue; }
        $emit($lit);
        $lit = '';
        if ($c === '$') {
            preg_match('/^\$(&|\{(\d+)\}|(\d+))/', substr($repl, $i), $mm);
            $emit($mm[1] === '&' ? (string)$m[0] : (string)($m[(int)(($mm[2] ?? '') !== '' ? $mm[2] : $mm[3])] ?? ''));
            $i += strlen($mm[0]) - 1;
            continue;
        }
        $d = $repl[++$i];
        if ($d === 'U' || $d === 'L') $mode = $d;
        elseif ($d === 'E') $mode = '';
        elseif ($d === 'u' || $d === 'l') $once = $d;
        elseif (ctype_digit($d)) $emit((string)($m[(int)$d] ?? ''));
        else $lit .= $d === 'n' ? "\n" : ($d === 't' ? "\t" : $d);
    }
    $emit($lit);
    return $out;
}

/** @return list<string> znaky sady tr/// (rozsahy a-z) */
function lab58_rename_tr_set(string $s): array
{
    $c = mb_str_split($s);
    $out = [];
    for ($i = 0, $n = count($c); $i < $n; $i++) {
        if ($c[$i] === '\\' && $i + 1 < $n) { $i++; $out[] = $c[$i] === 'n' ? "\n" : $c[$i]; continue; }
        if ($i + 2 < $n && $c[$i + 1] === '-' && mb_ord($c[$i + 2]) >= mb_ord($c[$i]) && mb_ord($c[$i + 2]) - mb_ord($c[$i]) < 512) {
            for ($k = mb_ord($c[$i]); $k <= mb_ord($c[$i + 2]); $k++) $out[] = (string)mb_chr($k);
            $i += 2;
            continue;
        }
        $out[] = $c[$i];
    }
    return $out;
}

/** @return string|false nové jméno, false = neplatný regulární výraz */
function lab58_rename_apply(array $stmts, string $name): string|false
{
    foreach ($stmts as $st) {
        if ($st[0] === 'lc' || $st[0] === 'uc') { $name = $st[0] === 'lc' ? mb_strtolower($name) : mb_strtoupper($name); continue; }
        if ($st[0] === 'y') {
            $from = lab58_rename_tr_set($st[1]);
            $to = lab58_rename_tr_set($st[2]);
            $del = str_contains($st[3], 'd');
            if ($to === [] && !$del) $to = $from;
            $map = [];
            foreach ($from as $k => $ch) $map[$ch] ??= $to[$k] ?? ($del ? '' : (string)end($to));
            $name = strtr($name, $map);
            continue;
        }
        $re = '~' . str_replace('~', '\~', $st[1]) . '~' . preg_replace('/[^imsx]/', '', $st[3]) . (mb_check_encoding($st[1] . $name, 'UTF-8') ? 'u' : '');
        $repl = $st[2];
        $res = @preg_replace_callback($re, static fn(array $m): string => lab58_rename_subst($repl, $m), $name, str_contains($st[3], 'g') ? -1 : 1);
        if (!is_string($res)) return false;
        $name = $res;
    }
    return $name;
}

function lab58_cmd_rename(Lab57Proc $p, array $argv): int
{
    $w = $p->w;
    $args = array_values(array_slice($argv, 1));
    $o = ['n' => false, 'v' => false, 'f' => false, 'd' => false];
    $codes = [];
    $long = ['--nono' => 'n', '--no-act' => 'n', '--dry-run' => 'n', '--just-print' => 'n', '--verbose' => 'v', '--force' => 'f', '--filename' => 'd', '--nopath' => 'd', '--nofullpath' => 'd'];
    while ($args !== [] && strlen((string)$args[0]) > 1 && $args[0][0] === '-') {
        $a = (string)array_shift($args);
        if ($a === '--') break;
        if (isset($long[$a])) { $o[$long[$a]] = true; continue; }
        if ($a === '-V' || $a === '--version') { $p->line('/usr/bin/rename using File::Rename version 2.01, File::Rename::Options version 2.01'); return 0; }
        if ($a === '-e' || $a === '-E') {
            if ($args === []) { $p->err("Option e requires an argument\n" . LAB58_RENAME_USAGE); return 2; }
            $codes[] = (string)array_shift($args);
            continue;
        }
        if (preg_match('/^-[nvfd]+$/', $a) === 1) { foreach (str_split(substr($a, 1)) as $ch) $o[$ch] = true; continue; }
        $p->err('Unknown option: ' . ltrim($a, '-') . "\n" . LAB58_RENAME_USAGE);
        $w->tip(tr('rename zná hlavně -n (jen ukázat, co by udělal), -v (vypsat) a -f (přepsat existující). → man rename'));
        return 2;
    }
    if ($codes === []) {
        if ($args === []) { $p->err(LAB58_RENAME_USAGE); $w->tip(tr("Použití: rename 's/staré/nové/' soubory – nejdřív zkus s -n. → man rename")); return 2; }
        $codes[] = (string)array_shift($args);
    }
    $stmts = lab58_rename_compile(implode(';', $codes));
    foreach (is_array($stmts) ? $stmts : [] as $st) {
        if ($st[0] !== 's' || preg_match('/[^gimsx]/', $st[3], $bad) !== 1) continue;
        $stmts = $bad[0] === 'e' ? 'Modifikátor /e (spuštění kódu v Perlu) simulace nepodporuje' : 'Unknown regexp modifier "/' . $bad[0] . '"';
        break;
    }
    if (is_string($stmts)) {
        $p->err($stmts . (str_contains($stmts, 'simulace') ? "\n" : " at (user-supplied code).\n"));
        $w->tip(tr("Výraz pro rename se píše jako v Perlu: 's/co/čím/' (g = všechny výskyty, i = bez ohledu na velikost). Dej ho do apostrofů. → man rename"));
        return 255;
    }
    $files = $args !== [] ? $args : (preg_split('/\n/', trim($p->stdin), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    $status = 0;
    foreach (array_slice($files, 0, LAB58_IM_MAX_FILES) as $old) {
        $old = (string)$old;
        $cut = $o['d'] && str_contains($old, '/') ? (int)strrpos($old, '/') + 1 : 0;
        $new = lab58_rename_apply($stmts, substr($old, $cut));
        if ($new === false) {
            $p->err("Invalid regular expression at (user-supplied code).\n");
            $w->tip(tr('Regulární výraz má chybu (třeba neuzavřenou závorku). → man rename'));
            return 255;
        }
        $new = substr($old, 0, $cut) . $new;
        if ($new === $old) continue;
        $oldAbs = $w->abs($old);
        $newAbs = $w->abs($new);
        $node = $w->canTraverse($oldAbs) ? $w->fs->get($oldAbs) : null;
        $target = $w->fs->get($newAbs);
        $error = match (true) {
            $node === null || $new === '' => 'No such file or directory',
            $target !== null && !$o['f'] => 'exists',
            !$o['n'] && !$w->fs->isDir(Lab57Vfs::dirname($newAbs)) => 'No such file or directory',
            !$o['n'] && (!lab57_can_unlink($w, $oldAbs, $node) || !$w->canModifyDir(Lab57Vfs::dirname($newAbs))) => 'Permission denied',
            !$o['n'] && ($target['t'] ?? '') === 'd' => 'Is a directory',
            ($node['t'] ?? '') === 'd' && str_starts_with($newAbs . '/', $oldAbs . '/') => 'Invalid argument',
            default => null,
        };
        if ($error !== null) {
            $p->err($error === 'exists' ? "$old not renamed: $new already exists\n" : "Can't rename $old $new: $error\n");
            $status = 1;
            continue;
        }
        if ($o['n']) { $p->line("rename($old, $new)"); continue; }
        if ($target !== null) $w->fs->delete($newAbs);
        lab57_transfer_tree($w, $oldAbs, $newAbs, true);
        if ($o['v']) $p->line("$old renamed as $new");
    }
    return $status;
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

lab58_register_command('exiftool', 'lab58_cmd_exiftool', ['package' => 'libimage-exiftool-perl']);
lab58_register_command('rename', 'lab58_cmd_rename', ['package' => 'rename']);
lab58_register_apt_package('libimage-exiftool-perl', ['version' => '12.57+dfsg-1', 'description' => 'library and program to read and write meta information in multimedia files', 'size' => '3,337 kB', 'installed_size' => '21.3 MB', 'bins' => ['exiftool'], 'preinstalled' => true]);
lab58_register_apt_package('rename', ['version' => '2.01-1', 'description' => 'Perl extension for renaming multiple files', 'size' => '17.1 kB', 'installed_size' => '47.1 kB', 'bins' => ['rename'], 'preinstalled' => true]);


