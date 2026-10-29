<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – ImageMagick (identify, convert, mogrify, magick) nad modelem obrázku
 * z linux_v58_cmd_media.php. Výstupy a chybové hlášky podle ImageMagick 6.9.11 v Debianu 12
 * (convert-im6.q16: …); magick se chová jako ImageMagick 7. Nic se nespouští, žádná síť.
 * Omezení simulace: pixely se nepočítají – efekty (blur, sharpen…) mění jen „kvalitu“ a velikost
 * se odhaduje z rozměrů, formátu a kvality.
 */

const LAB58_IM_VERSION = 'ImageMagick 6.9.11-60 Q16 x86_64 2021-01-25 https://imagemagick.org';
const LAB58_IM7_VERSION = 'ImageMagick 7.1.1-43 Q16-HDRI x86_64 22550 https://imagemagick.org';
const LAB58_IM_MAX_FILES = 200;
const LAB58_IM_MAX_IMAGES = 100;

/** Jméno programu v hláškách: Debian volá binárky convert-im6.q16 atd. */
function lab58_im_prog(string $name): string
{
    return $name === 'magick' ? 'magick' : $name . '-im6.q16';
}

/** IM6 uvozuje `soubor', IM7 'soubor'. */
function lab58_im_q(string $prog, string $text): string
{
    return $prog === 'magick' ? "'" . $text . "'" : '`' . $text . "'";
}

function lab58_im_fail(Lab57Proc $p, string $prog, string $message, string $where): void
{
    $p->err($prog . ': ' . $message . ' @ ' . $where . ".\n");
}

/** Velikost jako ImageMagick (FormatMagickSize, jednotky po 1000, přesnost i+2 platných číslic). */
function lab58_im_size(int $bytes): string
{
    $units = ['', 'K', 'M', 'G', 'T'];
    $len = (float)$bytes;
    $i = 0;
    while ($len >= 1000 && $i < 4) { $len /= 1000; $i++; }
    for ($j = 2; $j < 12; $j++) {
        $text = sprintf('%.' . ($i + $j) . 'g', $len);
        if (!str_contains($text, '+')) break;
    }
    return $text . $units[$i] . 'B';
}

/** Chyba čtení podle formátu (jako skutečné dekodéry IM6). */
function lab58_im_read_error(Lab57Proc $p, string $prog, string $name, array $loaded): void
{
    $err = (string)$loaded['err'];
    if ($err !== 'improper') {
        lab58_im_fail($p, $prog, 'unable to open image ' . lab58_im_q($prog, $name) . ': ' . $err, 'error/blob.c/OpenBlob/' . ($prog === 'magick' ? '3596' : '2924'));
        lab57_error_tip($p->w, $err, $name);
        return;
    }
    $ext = strtoupper(lab58_img_ext($name));
    $c = $loaded['content'];
    $fmt = lab58_img_ext_format($ext);
    if ($fmt === 'JPEG' && $c !== '') {
        lab58_im_fail($p, $prog, sprintf('Not a JPEG file: starts with 0x%02x 0x%02x ', ord($c[0]), ord($c[1] ?? "\x00")) . lab58_im_q($prog, $name), 'error/jpeg.c/JPEGErrorHandler/332');
    } elseif ($fmt !== null && $fmt !== 'SVG') {
        lab58_im_fail($p, $prog, 'improper image header ' . lab58_im_q($prog, $name), 'error/' . strtolower($fmt === 'JPEG' ? 'jpeg' : $fmt) . '.c/Read' . $fmt . 'Image/1042');
    } else {
        lab58_im_fail($p, $prog, 'no decode delegate for this image format ' . lab58_im_q($prog, $ext), 'error/constitute.c/ReadImage/575');
    }
    $p->w->tip(tr('„{name}“ není obrázek (nebo je poškozený). Co v souboru doopravdy je, ukáže file {name}. → man identify', ['name' => $name]));
}

// ---------------------------------------------------------------------------
// Geometrie (-resize, -crop …) jako ParseMetaGeometry v ImageMagick
// ---------------------------------------------------------------------------

/** @return array{w:?float,h:?float,pct:bool,flag:string,x:int,y:int,off:bool}|null */
function lab58_im_geometry(string $g): ?array
{
    if (preg_match('/^(\d+(?:\.\d+)?)?(%)?(?:x(\d+(?:\.\d+)?)?(%)?)?([!<>^@%]?)([+-]\d+)?([+-]\d+)?$/', trim($g), $m) !== 1) return null;
    $w = ($m[1] ?? '') !== '' ? (float)$m[1] : null;
    $h = ($m[3] ?? '') !== '' ? (float)$m[3] : null;
    if ($w === null && $h === null) return null;
    $flag = (string)($m[5] ?? '');
    $pct = ($m[2] ?? '') !== '' || ($m[4] ?? '') !== '' || $flag === '%';
    return ['w' => $w, 'h' => $h, 'pct' => $pct, 'flag' => $flag === '%' ? '' : $flag, 'x' => (int)($m[6] ?? 0), 'y' => (int)($m[7] ?? 0), 'off' => ($m[6] ?? '') !== ''];
}

/** @return array{0:int,1:int} nové rozměry po -resize */
function lab58_im_resize_dims(int $w, int $h, array $g): array
{
    if ($g['pct']) {
        $sx = $g['w'] ?? $g['h'];
        $sy = $g['h'] ?? $sx;
        return [max(1, (int)floor($w * $sx / 100 + 0.5)), max(1, (int)floor($h * $sy / 100 + 0.5))];
    }
    if ($g['flag'] === '@') {
        $scale = sqrt(($g['w'] ?? 1) / max(1, $w * $h));
        return [max(1, (int)floor($w * $scale + 0.5)), max(1, (int)floor($h * $scale + 0.5))];
    }
    if ($g['flag'] === '!') return [max(1, (int)($g['w'] ?? $w)), max(1, (int)($g['h'] ?? $h))];
    if ($g['w'] === null) $scale = $g['h'] / $h;
    elseif ($g['h'] === null) $scale = $g['w'] / $w;
    else $scale = $g['flag'] === '^' ? max($g['w'] / $w, $g['h'] / $h) : min($g['w'] / $w, $g['h'] / $h);
    $nw = max(1, (int)floor($scale * $w + 0.5));
    $nh = max(1, (int)floor($scale * $h + 0.5));
    if ($g['flag'] === '>' && $w <= ($g['w'] ?? PHP_INT_MAX) && $h <= ($g['h'] ?? PHP_INT_MAX)) return [$w, $h];
    if ($g['flag'] === '<' && ($w >= ($g['w'] ?? 0) || $h >= ($g['h'] ?? 0))) return [$w, $h];
    return [$nw, $nh];
}

// ---------------------------------------------------------------------------
// identify: řádek, -format, -verbose
// ---------------------------------------------------------------------------

function lab58_im_colorspace(array $m): string
{
    return in_array($m['ctype'], ['gray', 'graya'], true) ? 'Gray' : 'sRGB';
}

function lab58_im_line(string $name, array $m, int $size): string
{
    $colors = $m['ctype'] === 'palette' ? ' ' . ($m['colors'] ?: 256) . 'c' : (lab58_im_colorspace($m) === 'Gray' ? ' 256c' : '');
    $depth = $m['fmt'] === 'SVG' ? 16 : $m['depth'];
    return sprintf('%s %s %dx%d %dx%d+0+0 %d-bit %s%s %s 0.000u 0:00.000', $name, $m['fmt'], $m['w'], $m['h'], $m['w'], $m['h'], $depth, lab58_im_colorspace($m), $colors, lab58_im_size($size));
}

/** EXIF tak, jak ho vypisuje ImageMagick (jména podle standardu EXIF, racionální čísla jako a/b). */
function lab58_im_exif_props(array $exif): array
{
    $rename = ['ModifyDate' => 'DateTime', 'CreateDate' => 'DateTimeDigitized', 'ISO' => 'PhotographicSensitivity'];
    $out = [];
    foreach ($exif as $name => $value) {
        $text = is_array($value) ? implode(', ', array_map(static fn(array $r): string => $r[0] . '/' . $r[1], $value)) : (string)$value;
        $out['exif:' . ($rename[$name] ?? $name)] = $text;
    }
    ksort($out, SORT_STRING);
    return $out;
}

function lab58_im_format(string $fmt, string $name, array $m, int $size): string
{
    $fmt = str_replace(['\\n', '\\t'], ["\n", "\t"], $fmt);
    $base = Lab57Vfs::basename($name);
    $ext = lab58_img_ext($name);
    return (string)preg_replace_callback('/%\[([^\]]{1,60})\]|%([a-zA-Z%])/', static function (array $mm) use ($name, $base, $ext, $m, $size): string {
        if (($mm[2] ?? '') === '') {
            $key = strtolower($mm[1]);
            if ($key === 'exif:*') {
                $lines = [];
                foreach (lab58_im_exif_props($m['exif']) as $k => $v) $lines[] = $k . '=' . $v;
                return implode("\n", $lines) . ($lines === [] ? '' : "\n");
            }
            if (str_starts_with($key, 'exif:')) {
                foreach (lab58_im_exif_props($m['exif']) as $k => $v) if (strtolower($k) === $key) return $v;
                return '';
            }
            return match ($key) {
                'colorspace' => lab58_im_colorspace($m), 'width' => (string)$m['w'], 'height' => (string)$m['h'],
                'channels' => strtolower(lab58_im_colorspace($m)) . (lab58_img_has_alpha($m) ? 'a' : ''), 'type' => lab58_im_type($m),
                'orientation' => lab58_im_orientation($m), 'size' => lab58_im_size($size), default => '',
            };
        }
        return match ($mm[2]) {
            '%' => '%', 'f' => $base, 'i' => $name, 'd' => str_contains($name, '/') ? Lab57Vfs::dirname($name) : '', 'e' => $ext,
            't' => $ext === '' ? $base : substr($base, 0, -strlen($ext) - 1), 'm' => $m['fmt'], 'w', 'W' => (string)$m['w'], 'h', 'H' => (string)$m['h'],
            'g' => $m['w'] . 'x' . $m['h'] . '+0+0', 'b' => lab58_im_size($size), 'B' => (string)$size, 'z' => (string)($m['fmt'] === 'SVG' ? 16 : $m['depth']),
            'Q' => (string)$m['q'], 'x', 'y' => (string)$m['dpi'], 'n' => '1', 'p', 's' => '0', 'X', 'Y' => '+0',
            'r' => ($m['ctype'] === 'palette' ? 'PseudoClass ' : 'DirectClass ') . lab58_im_colorspace($m) . (lab58_img_has_alpha($m) ? ' Alpha' : ''),
            'k' => (string)($m['ctype'] === 'palette' ? ($m['colors'] ?: 256) : min($m['w'] * $m['h'], $m['cx'] * 1873 + $m['w'] % 997)),
            'c' => (string)($m['text']['Comment'] ?? ''),
            default => '',
        };
    }, $fmt);
}

function lab58_im_type(array $m): string
{
    return ['rgb' => 'TrueColor', 'rgba' => 'TrueColorAlpha', 'gray' => 'Grayscale', 'graya' => 'GrayscaleAlpha', 'palette' => 'Palette'][$m['ctype']];
}

function lab58_im_orientation(array $m): string
{
    return [1 => 'TopLeft', 2 => 'TopRight', 3 => 'BottomRight', 4 => 'BottomLeft', 5 => 'LeftTop', 6 => 'RightTop', 7 => 'RightBottom', 8 => 'LeftBottom'][(int)($m['exif']['Orientation'] ?? 0)] ?? 'Undefined';
}

function lab58_im_format_name(string $fmt): string
{
    return [
        'JPEG' => 'JPEG (Joint Photographic Experts Group JFIF format)', 'PNG' => 'PNG (Portable Network Graphics)',
        'GIF' => 'GIF (CompuServe graphics interchange format)', 'WEBP' => 'WEBP (WebP Image Format)', 'BMP' => 'BMP (Microsoft Windows bitmap image)',
        'ICO' => 'ICO (Microsoft icon)', 'TIFF' => 'TIFF (Tagged Image File Format)', 'SVG' => 'SVG (Scalable Vector Graphics)',
    ][$fmt] ?? $fmt;
}

function lab58_im_mime(string $fmt): string
{
    return ['JPEG' => 'image/jpeg', 'PNG' => 'image/png', 'GIF' => 'image/gif', 'WEBP' => 'image/webp', 'BMP' => 'image/bmp', 'ICO' => 'image/vnd.microsoft.icon', 'TIFF' => 'image/tiff', 'SVG' => 'image/svg+xml'][$fmt] ?? 'application/octet-stream';
}

function lab58_im_version(Lab57Proc $p, bool $im7): void
{
    $p->out('Version: ' . ($im7 ? LAB58_IM7_VERSION : LAB58_IM_VERSION) . "\nCopyright: (C) 1999-" . ($im7 ? '2024' : '2021') . " ImageMagick Studio LLC\nLicense: https://imagemagick.org/script/license.php\n"
        . "Features: Cipher DPC Modules OpenMP(4.5) \nDelegates (built-in): bzlib djvu fftw fontconfig freetype heic jbig jng jp2 jpeg lcms lqr ltdl lzma openexr pangocairo png raw tiff webp wmf x xml zlib\n");
}

function lab58_im_usage(Lab57Proc $p, string $prog, string $tool, bool $im7): void
{
    lab58_im_version($p, $im7);
    $lines = [
        'identify' => ["Usage: $prog [options ...] file [ [options ...] file ... ]", '', 'Image Settings:', '  -format "string"     output formatted image characteristics', '  -verbose             print detailed information about the image'],
        'mogrify' => ["Usage: $prog [options ...] file [ [options ...] file ...]", '', 'Image Settings:', '  -format type         image format type', '  -path path           write images to this path on disk', '  -quality value       JPEG/MIFF/PNG compression level', '', 'Image Operators:', '  -resize geometry     resize the image', '  -strip               strip image of all profiles and comments', '  -thumbnail geometry  create a thumbnail of the image'],
    ][$tool] ?? ["Usage: $prog [options ...] file [ [options ...] file ...] [options ...] file", '', 'Image Settings:', '  -density geometry    horizontal and vertical density of the image', '  -quality value       JPEG/MIFF/PNG compression level', '', 'Image Operators:', '  -crop geometry       cut out a rectangular region of the image', '  -resize geometry     resize the image', '  -rotate degrees      apply Paeth rotation to the image', '  -strip               strip image of all profiles and comments', '  -thumbnail geometry  create a thumbnail of the image'];
    $p->out(implode("\n", $lines) . "\n\nBy default, the image format of `file' is determined by its magic\nnumber.  To specify a particular image format, precede the filename\nwith an image format name and a colon (i.e. ps:image) or specify the\nimage type as the filename suffix (i.e. image.ps).  Specify 'file' as\n'-' for standard input or output.\n");
}

// ---------------------------------------------------------------------------
// Čtení vstupů (soubor, '-', png:soubor, xc:barva)
// ---------------------------------------------------------------------------

/** @return array{model:?array,node:?array,content:string,err:?string,size:int,mt:int,name:string} */
function lab58_im_load(Lab57Proc $p, string $spec): array
{
    $name = $spec;
    if (preg_match('/^([a-zA-Z0-9]{2,5}):(.+)$/', $spec, $mm) === 1 && lab58_img_ext_format($mm[1]) !== null) $name = $mm[2];
    if ($name === '-') {
        $model = lab58_img_parse($p->stdin);
        return ['model' => $model, 'node' => null, 'content' => $p->stdin, 'err' => $model === null ? 'improper' : null, 'size' => strlen($p->stdin), 'mt' => $p->w->now, 'name' => '-'];
    }
    $loaded = lab58_img_load($p->w, $name);
    $loaded['size'] = $loaded['node'] !== null ? Lab57Vfs::size($loaded['node']) : 0;
    $loaded['mt'] = (int)($loaded['node']['mt'] ?? $p->w->now);
    $loaded['name'] = $name;
    return $loaded;
}

/** @return list<array{m:array,name:string,size:int}>|null */
function lab58_im_read(Lab57Proc $p, string $prog, string $spec, array $st): ?array
{
    if (preg_match('/^(xc|canvas):(.*)$/i', $spec, $mm) === 1) {
        if (!lab58_im_color_ok($mm[2] === '' ? 'white' : $mm[2])) { lab58_im_fail($p, $prog, 'unrecognized color ' . lab58_im_q($prog, $mm[2]), 'error/color.c/GetColorCompliance/1064'); return null; }
        [$cw, $ch] = $st['size'] ?? [1, 1];
        $alpha = in_array(strtolower($mm[2]), ['none', 'transparent'], true);
        return [['m' => lab58_img_new('PNG', $cw, $ch, ['ctype' => $alpha ? 'rgba' : 'rgb', 'cx' => 1, 'dpi' => $st['density'] ?? 72]), 'name' => $spec, 'size' => 0]];
    }
    $loaded = lab58_im_load($p, $spec);
    if ($loaded['model'] === null) {
        lab58_im_read_error($p, $prog, $loaded['name'], $loaded);
        return null;
    }
    $m = $loaded['model'];
    if ($m['fmt'] === 'SVG' && ($st['density'] ?? null) !== null) {
        $m['w'] = max(1, (int)round($m['w'] * $st['density'] / 96));
        $m['h'] = max(1, (int)round($m['h'] * $st['density'] / 96));
        $m['dpi'] = (int)$st['density'];
    }
    return [['m' => $m, 'name' => $loaded['name'], 'size' => $loaded['size']]];
}

function lab58_im_color_ok(string $color): bool
{
    return preg_match('/^(#[0-9a-f]{3,4}|#[0-9a-f]{6}|#[0-9a-f]{8}|[a-z]{3,20}\d{0,3}|(rgb|rgba|hsl|hsla|gray|cmyk)\([\d.,% ]{1,40}\))$/i', $color) === 1;
}

// ---------------------------------------------------------------------------
// identify
// ---------------------------------------------------------------------------

function lab58_im_identify(Lab57Proc $p, string $prog, array $args, bool $im7): int
{
    $verbose = false;
    $format = null;
    $files = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if ($a === '-version' || $a === '--version') { lab58_im_version($p, $im7); return 0; }
        if ($a === '-verbose') { $verbose = true; continue; }
        if ($a === '-format' || in_array($a, ['-density', '-define', '-size', '-units', '-limit'], true)) {
            if (!isset($args[$i + 1])) { lab58_im_fail($p, $prog, 'argument required ' . lab58_im_q($prog, $a), 'error/identify.c/IdentifyImageCommand/446'); return 1; }
            $i++;
            if ($a === '-format') $format = (string)$args[$i];
            continue;
        }
        if (in_array($a, ['-ping', '+ping', '-quiet', '-regard-warnings', '-precision'], true)) continue;
        if (strlen($a) > 1 && ($a[0] === '-' || $a[0] === '+')) {
            lab58_im_fail($p, $prog, 'unrecognized option ' . lab58_im_q($prog, $a), 'error/identify.c/IdentifyImageCommand/1054');
            $p->w->tip(tr('identify zná hlavně -verbose a -format "%w x %h". → man identify'));
            return 1;
        }
        $files[] = $a;
    }
    if ($files === []) { lab58_im_usage($p, $prog, 'identify', $im7); return 1; }
    $status = 0;
    foreach (array_slice($files, 0, LAB58_IM_MAX_FILES) as $file) {
        $loaded = lab58_im_load($p, $file);
        if ($loaded['model'] === null) { lab58_im_read_error($p, $prog, $loaded['name'], $loaded); $status = 1; continue; }
        if ($format !== null) $p->out(lab58_im_format($format, $file, $loaded['model'], $loaded['size']));
        elseif ($verbose) lab58_im_verbose($p, $file, $loaded['model'], $loaded['size'], $loaded['mt'], $loaded['content'], $im7);
        else $p->line(lab58_im_line($file, $loaded['model'], $loaded['size']));
    }
    return $status;
}

function lab58_cmd_identify(Lab57Proc $p, array $argv): int
{
    return lab58_im_identify($p, lab58_im_prog('identify'), array_slice($argv, 1), false);
}

// ---------------------------------------------------------------------------
// Volby convert/mogrify: jméno => počet argumentů
// ---------------------------------------------------------------------------

function lab58_im_options(): array
{
    return [
        'resize' => 1, 'thumbnail' => 1, 'scale' => 1, 'sample' => 1, 'adaptive-resize' => 1, 'liquid-rescale' => 1, 'crop' => 1, 'extent' => 1,
        'border' => 1, 'shave' => 1, 'chop' => 1, 'rotate' => 1, 'auto-orient' => 0, 'flip' => 0, 'flop' => 0, 'transpose' => 0, 'transverse' => 0,
        'trim' => 0, 'repage' => 0, 'append' => 0, 'flatten' => 0, 'layers' => 1, 'quality' => 1, 'strip' => 0, 'density' => 1, 'units' => 1,
        'colorspace' => 1, 'type' => 1, 'monochrome' => 0, 'grayscale' => 1, 'colors' => 1, 'depth' => 1, 'alpha' => 1, 'background' => 1,
        'comment' => 1, 'set' => 2, 'profile' => 1, 'define' => 1, 'interlace' => 1, 'sampling-factor' => 1, 'blur' => 1, 'gaussian-blur' => 1,
        'sharpen' => 1, 'unsharp' => 1, 'modulate' => 1, 'brightness-contrast' => 1, 'negate' => 0, 'normalize' => 0, 'auto-level' => 0,
        'equalize' => 0, 'sepia-tone' => 1, 'despeckle' => 0, 'enhance' => 0, 'contrast' => 0, 'level' => 1, 'gamma' => 1, 'fill' => 1,
        'stroke' => 1, 'pointsize' => 1, 'font' => 1, 'gravity' => 1, 'annotate' => 2, 'draw' => 1, 'bordercolor' => 1, 'dither' => 1,
        'delay' => 1, 'loop' => 1, 'quiet' => 0, 'verbose' => 0, 'size' => 1, 'format' => 1, 'path' => 1, 'ping' => 0, 'filter' => 1,
        'channel' => 1, 'compress' => 1, 'limit' => 2, 'monitor' => 0,
    ];
}

function lab58_im_is_option(string $a): bool
{
    return strlen($a) > 1 && ($a[0] === '-' || $a[0] === '+') && preg_match('/^[+-]\d/', $a) !== 1;
}

/** @return array{0:string,1:bool,2:list<string>,3:string}|null [jméno, plus, argumenty, původní text] */
function lab58_im_take_option(Lab57Proc $p, string $prog, array $args, int &$i, string $tool): ?array
{
    $raw = (string)$args[$i];
    $plus = $raw[0] === '+';
    $name = substr($raw, 1);
    $table = lab58_im_options();
    if (!isset($table[$name]) || ($name === 'path' && $tool !== 'mogrify')) {
        lab58_im_fail($p, $prog, 'unrecognized option ' . lab58_im_q($prog, $raw), 'error/' . $tool . '.c/' . ucfirst($tool) . 'ImageCommand/' . ($tool === 'mogrify' ? '6343' : '3244'));
        $p->w->tip(tr('Tuhle volbu simulace ImageMagicku nezná (nebo má překlep). Časté volby: -resize, -quality, -strip, -crop, -rotate, -thumbnail. → man {tool}', ['tool' => $tool]));
        return null;
    }
    $arity = $plus ? (['profile' => 1, 'set' => 1, 'define' => 1, 'limit' => 2][$name] ?? 0) : $table[$name];
    $params = [];
    for ($k = 0; $k < $arity; $k++) {
        if (!isset($args[$i + 1])) {
            lab58_im_fail($p, $prog, 'argument required ' . lab58_im_q($prog, $raw), 'error/' . $tool . '.c/' . ucfirst($tool) . 'ImageCommand/2549');
            $p->w->tip(tr('Volba {raw} potřebuje hodnotu, např. {example}. → man {tool}', ['raw' => $raw, 'tool' => $tool, 'example' => $name === 'resize' ? '-resize 50%' : ($name === 'quality' ? '-quality 80' : $raw . ' …')]));
            return null;
        }
        $params[] = (string)$args[++$i];
    }
    return [$name, $plus, $params, $raw];
}

// ---------------------------------------------------------------------------
// Operátory
// ---------------------------------------------------------------------------

const LAB58_IM_COLORSPACES = ['gray', 'lineargray', 'srgb', 'rgb', 'scrgb', 'cmyk', 'cmy', 'hsl', 'hsb', 'hsv', 'hwb', 'lab', 'lch', 'luv', 'xyz', 'ycbcr', 'yuv', 'rec601ycbcr', 'rec709ycbcr', 'transparent'];

/** @return array|string upravený model, nebo český tip k neplatné hodnotě */
function lab58_im_op(array $m, string $name, bool $plus, array $params): array|string
{
    $arg = (string)($params[0] ?? '');
    $alpha = lab58_img_has_alpha($m);
    $gray = in_array($m['ctype'], ['gray', 'graya'], true);
    if (in_array($name, ['resize', 'scale', 'sample', 'adaptive-resize', 'liquid-rescale', 'thumbnail', 'extent', 'border', 'shave', 'chop', 'rotate', 'auto-orient', 'transpose', 'transverse'], true)) $m['svg'] = '';
    switch ($name) {
        case 'resize': case 'scale': case 'sample': case 'adaptive-resize': case 'liquid-rescale': case 'thumbnail': case 'extent': case 'chop':
            $g = lab58_im_geometry($arg);
            if ($g === null) return tr('Geometrie se píše např. 50%, 800x600, 800x nebo x600.');
            if ($name === 'extent') { [$m['w'], $m['h']] = $g['pct'] ? lab58_im_resize_dims($m['w'], $m['h'], $g) : [(int)($g['w'] ?? $m['w']), (int)($g['h'] ?? $m['h'])]; break; }
            if ($name === 'chop') { $m['w'] -= (int)($g['w'] ?? 0); $m['h'] -= (int)($g['h'] ?? 0); break; }
            [$m['w'], $m['h']] = lab58_im_resize_dims($m['w'], $m['h'], $name === 'liquid-rescale' ? ['flag' => '!'] + $g : $g);
            if ($name === 'thumbnail') { $m['exif'] = []; $m['text'] = []; }
            break;
        case 'border': case 'shave':
            if (preg_match('/^(\d{1,5})(?:x(\d{1,5}))?$/', $arg, $mm) !== 1) return tr('Okraj se píše v pixelech, např. {name} 10 nebo 10x5.', ['name' => $name]);
            $sign = $name === 'border' ? 2 : -2;
            $m['w'] += $sign * (int)$mm[1];
            $m['h'] += $sign * (int)($mm[2] ?? $mm[1]);
            break;
        case 'rotate':
            if (preg_match('/^(-?\d{1,6}(?:\.\d+)?)([<>]?)$/', $arg, $mm) !== 1) return tr('Úhel se píše ve stupních, např. -rotate 90.');
            if (($mm[2] === '>' && $m['w'] <= $m['h']) || ($mm[2] === '<' && $m['w'] >= $m['h'])) break;
            $deg = fmod((float)$mm[1], 360.0);
            if ($deg < 0) $deg += 360.0;
            if (abs($deg - 90) < 1e-9 || abs($deg - 270) < 1e-9) {
                [$m['w'], $m['h']] = [$m['h'], $m['w']];
            } elseif (abs($deg) > 1e-9 && abs($deg - 180) > 1e-9) {
                $c = abs(cos(deg2rad($deg)));
                $s = abs(sin(deg2rad($deg)));
                [$m['w'], $m['h']] = [(int)ceil($m['w'] * $c + $m['h'] * $s - 1e-6), (int)ceil($m['w'] * $s + $m['h'] * $c - 1e-6)];
            }
            break;
        case 'auto-orient':
            $o = (int)($m['exif']['Orientation'] ?? 1);
            if ($o >= 5 && $o <= 8) [$m['w'], $m['h']] = [$m['h'], $m['w']];
            if (isset($m['exif']['Orientation'])) $m['exif']['Orientation'] = 1;
            break;
        case 'transpose': case 'transverse':
            [$m['w'], $m['h']] = [$m['h'], $m['w']];
            break;
        case 'strip':
            $m['exif'] = [];
            $m['text'] = [];
            break;
        case 'profile':
            if ($plus && in_array(strtolower($arg), ['*', 'exif', '!icc,*'], true)) $m['exif'] = [];
            break;
        case 'colorspace':
            $cs = strtolower($arg);
            if (!in_array($cs, LAB58_IM_COLORSPACES, true)) return tr('Barevný prostor je např. Gray nebo sRGB.');
            if ($cs === 'gray' || $cs === 'lineargray') $m['ctype'] = $alpha ? 'graya' : 'gray';
            elseif (in_array($cs, ['srgb', 'rgb', 'scrgb'], true) && $gray) $m['ctype'] = $alpha ? 'rgba' : 'rgb';
            break;
        case 'type':
            $map = ['grayscale' => 'gray', 'grayscalealpha' => 'graya', 'truecolor' => 'rgb', 'truecoloralpha' => 'rgba', 'palette' => 'palette', 'palettealpha' => 'palette', 'bilevel' => 'gray', 'optimize' => '', 'colorseparation' => ''];
            if (!isset($map[strtolower($arg)])) return tr('Typ obrázku je např. Grayscale, TrueColor nebo Palette.');
            if ($map[strtolower($arg)] !== '') $m['ctype'] = $map[strtolower($arg)];
            break;
        case 'monochrome': case 'grayscale':
            $m['ctype'] = $alpha ? 'graya' : 'gray';
            break;
        case 'colors':
            if (preg_match('/^\d{1,5}$/', $arg) !== 1 || (int)$arg < 1) return tr('Počet barev je celé číslo, např. -colors 16.');
            $m['colors'] = max(2, min(256, (int)$arg));
            if ($m['fmt'] !== 'JPEG') $m['ctype'] = 'palette';
            break;
        case 'depth':
            if (!in_array($arg, ['1', '2', '4', '8', '16'], true)) return tr('Bitová hloubka je obvykle 8 nebo 16.');
            $m['depth'] = (int)$arg;
            break;
        case 'alpha':
            $a = strtolower($arg);
            if (in_array($a, ['off', 'remove', 'deactivate', 'disassociate', 'flatten', 'background'], true)) $m['ctype'] = ['rgba' => 'rgb', 'graya' => 'gray'][$m['ctype']] ?? $m['ctype'];
            elseif (in_array($a, ['on', 'set', 'activate', 'opaque', 'transparent', 'associate', 'copy', 'shape', 'extract'], true)) $m['ctype'] = $gray ? 'graya' : 'rgba';
            else return tr('Hodnota pro -alpha je např. off, remove nebo on.');
            break;
        case 'background': case 'fill': case 'stroke': case 'bordercolor':
            if (!lab58_im_color_ok($arg)) return tr('Barva se píše např. white, #ff8800 nebo "rgb(255,136,0)".');
            break;
        case 'comment':
            $m['text']['Comment'] = lab58_img_clean($arg, 500);
            break;
        case 'set':
            if (strtolower($arg) === 'comment') $m['text']['Comment'] = lab58_img_clean((string)($params[1] ?? ''), 500);
            break;
        case 'blur': case 'gaussian-blur':
            $m['cx'] = max(1, (int)round($m['cx'] * 0.8));
            break;
        case 'sharpen': case 'unsharp':
            $m['cx'] = min(250, (int)round($m['cx'] * 1.1));
            break;
    }
    return lab58_img_normalize($m);
}

/** Ořez: s posunem jeden výřez, bez posunu dlaždice (jako ImageMagick). @return list<array>|string */
function lab58_im_crop(array $imgs, array $g): array|string
{
    $out = [];
    foreach ($imgs as $img) {
        $m = $img['m'];
        $m['svg'] = '';
        $cw = max(1, $g['pct'] ? (int)floor($m['w'] * ($g['w'] ?? $g['h']) / 100 + 0.5) : (int)($g['w'] ?? $m['w']));
        $ch = max(1, $g['pct'] ? (int)floor($m['h'] * ($g['h'] ?? $g['w']) / 100 + 0.5) : (int)($g['h'] ?? $m['h']));
        if ($g['off'] || ($cw >= $m['w'] && $ch >= $m['h'])) {
            $x0 = max(0, $g['x']);
            $y0 = max(0, $g['y']);
            $x1 = min($m['w'], $g['x'] + $cw);
            $y1 = min($m['h'], $g['y'] + $ch);
            $img['outside'] = $x1 <= $x0 || $y1 <= $y0;
            [$m['w'], $m['h']] = $img['outside'] ? [1, 1] : [$x1 - $x0, $y1 - $y0];
            $img['m'] = $m;
            $out[] = $img;
            continue;
        }
        if ((int)ceil($m['w'] / $cw) * (int)ceil($m['h'] / $ch) + count($out) > LAB58_IM_MAX_IMAGES) return 'tiles';
        for ($y = 0; $y < $m['h']; $y += $ch) {
            for ($x = 0; $x < $m['w']; $x += $cw) {
                $t = $m;
                $t['w'] = min($cw, $m['w'] - $x);
                $t['h'] = min($ch, $m['h'] - $y);
                $out[] = ['m' => $t] + $img;
            }
        }
    }
    return $out;
}

/** -append (pod sebe), +append (vedle sebe), -flatten (jedna vrstva). */
function lab58_im_merge(array $imgs, string $mode): array
{
    if ($imgs === []) return [];
    $first = $imgs[0];
    $m = $first['m'];
    $ws = array_map(static fn(array $i): int => $i['m']['w'], $imgs);
    $hs = array_map(static fn(array $i): int => $i['m']['h'], $imgs);
    if ($mode === 'h') [$m['w'], $m['h']] = [array_sum($ws), max($hs)];
    elseif ($mode === 'v') [$m['w'], $m['h']] = [max($ws), array_sum($hs)];
    else $m['ctype'] = ['rgba' => 'rgb', 'graya' => 'gray'][$m['ctype']] ?? $m['ctype'];
    $m['svg'] = '';
    $first['m'] = lab58_img_normalize($m);
    return [$first];
}

/** Jedna volba na celý seznam obrázků (nastavení se uloží do $st). null = chyba (už vypsaná). */
function lab58_im_apply(Lab57Proc $p, string $prog, array $imgs, array $opt, array &$st, string $tool): ?array
{
    [$name, $plus, $params, $raw] = $opt;
    $arg = (string)($params[0] ?? '');
    $where = 'error/' . $tool . '.c/' . ucfirst($tool) . 'ImageCommand/';
    $invalid = static function (string $tip) use ($p, $prog, $raw, $arg, $tool, $where): ?array {
        lab58_im_fail($p, $prog, 'invalid argument for option ' . lab58_im_q($prog, $raw) . ': ' . $arg, $where . '2586');
        $p->w->tip(tr('{tip} → man {tool}', ['tip' => $tip, 'tool' => $tool]));
        return null;
    };
    switch ($name) {
        case 'quality':
            if (preg_match('/^\d{1,3}(\.\d+)?%?$/', $arg) !== 1) return $invalid(tr('Kvalita je číslo 1–100, např. -quality 80.'));
            $st['quality'] = max(1, min(100, (int)round((float)$arg)));
            return $imgs;
        case 'density':
            if (preg_match('/^(\d{1,4}(?:\.\d+)?)(?:x\d{1,4}(?:\.\d+)?)?$/', $arg, $mm) !== 1) return $invalid(tr('Hustota se píše v DPI, např. -density 300.'));
            $st['density'] = max(1, min(9600, (int)round((float)$mm[1])));
            foreach ($imgs as $k => $img) $imgs[$k]['m']['dpi'] = $st['density'];
            return $imgs;
        case 'size':
            if (preg_match('/^(\d{1,5})x(\d{1,5})$/', $arg, $mm) !== 1) return $invalid(tr('Velikost plátna se píše ŠÍŘKAxVÝŠKA, např. -size 800x600.'));
            $st['size'] = [max(1, min(LAB58_IMG_MAX_DIM, (int)$mm[1])), max(1, min(LAB58_IMG_MAX_DIM, (int)$mm[2]))];
            return $imgs;
        case 'format': case 'path':
            $st[$name] = $arg;
            return $imgs;
        case 'verbose':
            $st['verbose'] = !$plus;
            return $imgs;
        case 'units':
            if (!in_array(strtolower($arg), ['pixelsperinch', 'pixelspercentimeter', 'undefined'], true)) return $invalid(tr('Jednotky jsou PixelsPerInch nebo PixelsPerCentimeter.'));
            return $imgs;
        case 'trim':
            $p->w->tip(tr('-trim ořezává okraje podle barvy pixelů – simulace pixely nemá, rozměry proto zůstanou stejné.'));
            return $imgs;
    }
    if ($imgs === []) {
        $st['pending'][] = $opt;
        return $imgs;
    }
    if ($name === 'crop') {
        $g = lab58_im_geometry($arg);
        if ($g === null) return $invalid(tr('Výřez se píše ŠÍŘKAxVÝŠKA+X+Y, např. -crop 800x600+100+50.'));
        $out = lab58_im_crop($imgs, $g);
        if ($out === 'tiles') {
            lab58_im_fail($p, $prog, 'cache resources exhausted ' . lab58_im_q($prog, (string)$imgs[0]['name']), 'error/cache.c/OpenPixelCache/4095');
            $p->w->tip(tr('Bez +X+Y dělí -crop obrázek na dlaždice – tady by jich bylo moc. Nezapomněl(a) jsi posun, např. -crop 800x600+0+0?'));
            return null;
        }
        foreach ($out as $img) if (!empty($img['outside'])) lab58_im_fail($p, $prog, 'geometry does not contain image ' . lab58_im_q($prog, (string)$img['name']), 'warning/transform.c/CropImage/666');
        return $out;
    }
    if ($name === 'append' || $name === 'flatten') return lab58_im_merge($imgs, $name === 'flatten' ? 'flat' : ($plus ? 'h' : 'v'));
    if ($name === 'layers') return in_array(strtolower($arg), ['flatten', 'merge', 'mosaic'], true) ? lab58_im_merge($imgs, 'flat') : $imgs;
    $out = [];
    foreach ($imgs as $img) {
        $m = lab58_im_op($img['m'], $name, $plus, $params);
        if (is_string($m)) return $invalid($m);
        $img['m'] = $m;
        $out[] = $img;
    }
    return $out;
}

// ---------------------------------------------------------------------------
// Zápis výstupu
// ---------------------------------------------------------------------------

function lab58_im_numbered(string $path, int $idx, int $count): string
{
    if (preg_match('/%(0?\d{0,2})d/', $path) === 1) return (string)preg_replace_callback('/%(0?\d{0,2})d/', static fn(array $mm): string => sprintf('%' . $mm[1] . 'd', $idx), $path, 1);
    if ($count < 2) return $path;
    $ext = lab58_img_ext($path);
    return $ext === '' ? $path . '-' . $idx : substr($path, 0, -strlen($ext) - 1) . '-' . $idx . '.' . $ext;
}

/** Zapíše seznam obrázků do $output (přípona/předpona určí formát). */
function lab58_im_write(Lab57Proc $p, string $prog, array $imgs, string $output, array $st, string $tool): bool
{
    $path = $output;
    $key = null;
    if (preg_match('/^([a-zA-Z0-9]{2,5}):(.*)$/', $output, $mm) === 1) { $key = strtolower($mm[1]); $path = $mm[2]; }
    if ($key === 'info') {
        foreach ($imgs as $img) $st['format'] !== null ? $p->out(lab58_im_format((string)$st['format'], $img['name'], $img['m'], $img['size'])) : $p->line(lab58_im_line($img['name'], $img['m'], $img['size']));
        return true;
    }
    $key ??= strtolower(lab58_img_ext($path));
    $where = 'error/constitute.c/';
    if (in_array($key, ['pdf', 'ps', 'eps', 'epdf', 'epi', 'xps'], true)) {
        lab58_im_fail($p, $prog, 'attempt to perform an operation not allowed by the security policy ' . lab58_im_q($prog, strtoupper($key)), $where . 'IsCoderAuthorized/421');
        $p->w->tip(tr('Debian ImageMagicku zápis PDF/PS z bezpečnostních důvodů zakazuje (/etc/ImageMagick-6/policy.xml). Na tisk ulož PNG nebo JPEG.'));
        return false;
    }
    $fmt = $key === '' ? $imgs[0]['m']['fmt'] : lab58_img_ext_format($key);
    if ($fmt === null || ($fmt === 'SVG' && ($imgs[0]['m']['svg'] === '' || count($imgs) > 1))) {
        lab58_im_fail($p, $prog, 'no encode delegate for this image format ' . lab58_im_q($prog, strtoupper($key)), $where . 'WriteImage/1297');
        $p->w->tip($fmt === 'SVG' ? tr('Z rastru (pixelů) se vektor jen tak neudělá – vektorizaci dělá např. Inkscape (Trace Bitmap).') : tr('Tenhle formát simulace neumí zapsat. Zkus .jpg, .png, .webp, .gif, .ico, .bmp nebo .tif.'));
        return false;
    }
    $count = count($imgs);
    foreach ($imgs as $idx => $img) {
        $orig = $img['m'];
        $target = lab58_im_numbered($path, $idx, $count);
        $m = lab58_img_as_format($orig, $fmt);
        $inherit = in_array($orig['fmt'], ['JPEG', 'WEBP'], true) && $orig['q'] > 0 ? $orig['q'] : null;
        if ($fmt === 'JPEG') $m['q'] = $st['quality'] ?? $inherit ?? 92;
        if ($fmt === 'WEBP') $m['q'] = $st['quality'] ?? $inherit ?? 75;
        if ($fmt === 'SVG') $m = $orig;
        if (($fmt === 'ICO' && ($m['w'] > 256 || $m['h'] > 256)) || ($fmt === 'WEBP' && ($m['w'] > 16383 || $m['h'] > 16383))) {
            lab58_im_fail($p, $prog, 'width or height exceeds limit ' . lab58_im_q($prog, $target), 'error/' . ($fmt === 'ICO' ? 'icon.c/WriteICONImage/1117' : 'webp.c/WriteWEBPImage/1049'));
            if ($fmt === 'ICO') $p->w->tip(tr('Ikona smí mít nejvýš 256×256 px. Nejdřív ji zmenši: -resize 32x32'));
            return false;
        }
        if ($path === '-') { $p->out(lab58_img_encode($m)); continue; }
        $err = null;
        if (!lab58_img_store($p->w, $target, $m, $err)) {
            lab58_im_fail($p, $prog, 'unable to open image ' . lab58_im_q($prog, $target) . ': ' . $err, 'error/blob.c/OpenBlob/' . ($prog === 'magick' ? '3596' : '2924'));
            if ($tool === 'mogrify' && $err === 'No such file or directory' && ($st['path'] ?? null) !== null) $p->w->tip(tr('Složku pro -path musíš nejdřív vytvořit: mkdir -p {path}', ['path' => $st['path']]));
            else lab57_error_tip($p->w, (string)$err, $target);
            return false;
        }
        if ($st['verbose']) $p->line($img['name'] . '=>' . $target . ' ' . $m['fmt'] . ' ' . $orig['w'] . 'x' . $orig['h'] . '=>' . $m['w'] . 'x' . $m['h'] . ' ' . $m['w'] . 'x' . $m['h'] . '+0+0 ' . $m['depth'] . '-bit ' . lab58_im_colorspace($m) . ' ' . lab58_im_size(Lab57Vfs::size((array)$p->w->fs->get($p->w->abs($target)))) . ' 0.010u 0:00.004');
    }
    return true;
}

// ---------------------------------------------------------------------------
// convert, mogrify, magick
// ---------------------------------------------------------------------------

function lab58_im_state(): array
{
    return ['quality' => null, 'density' => null, 'size' => null, 'format' => null, 'path' => null, 'verbose' => false, 'pending' => []];
}

/** Načte vstup a použije na něj odložené operátory (convert -resize 50% vstup výstup). */
function lab58_im_read_into(Lab57Proc $p, string $prog, array $imgs, string $spec, array &$st, string $tool): ?array
{
    $read = lab58_im_read($p, $prog, $spec, $st);
    if ($read === null) return null;
    $pending = $st['pending'];
    $st['pending'] = [];
    foreach ($pending as $opt) {
        $read = lab58_im_apply($p, $prog, $read, $opt, $st, $tool);
        if ($read === null) return null;
    }
    return array_merge($imgs, $read);
}

function lab58_im_convert(Lab57Proc $p, string $prog, array $args, bool $im7): int
{
    $tool = 'convert';
    if ($args === []) { lab58_im_usage($p, $prog, $tool, $im7); return 1; }
    if (in_array($args[0], ['-version', '--version'], true)) { lab58_im_version($p, $im7); return 0; }
    if (in_array($args[0], ['-help', '-h'], true)) { lab58_im_usage($p, $prog, $tool, $im7); return 0; }
    $output = (string)array_pop($args);
    if (lab58_im_is_option($output) && $output !== '-') {
        lab58_im_fail($p, $prog, 'missing an image filename ' . lab58_im_q($prog, $output), 'error/convert.c/ConvertImageCommand/3272');
        $p->w->tip(tr('Na konci příkazu musí být jméno výstupního souboru: convert vstup.jpg -resize 50% vystup.jpg → man convert'));
        return 1;
    }
    $st = lab58_im_state();
    $imgs = [];
    $status = 0;
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        $a = (string)$args[$i];
        if (lab58_im_is_option($a)) {
            $opt = lab58_im_take_option($p, $prog, $args, $i, $tool);
            $imgs = $opt === null ? null : lab58_im_apply($p, $prog, $imgs, $opt, $st, $tool);
            if ($imgs === null) return 1;
            continue;
        }
        $next = lab58_im_read_into($p, $prog, $imgs, $a, $st, $tool);
        if ($next === null) { $status = 1; continue; }
        $imgs = $next;
        if (count($imgs) > LAB58_IM_MAX_IMAGES) { lab58_im_fail($p, $prog, 'cache resources exhausted ' . lab58_im_q($prog, $a), 'error/cache.c/OpenPixelCache/4095'); return 1; }
    }
    if ($imgs === []) {
        lab58_im_fail($p, $prog, 'no images defined ' . lab58_im_q($prog, $output), 'error/convert.c/ConvertImageCommand/3229');
        if ($status === 0) $p->w->tip(tr('convert potřebuje vstupní i výstupní soubor: convert vstup.png vystup.jpg → man convert'));
        return 1;
    }
    return lab58_im_write($p, $prog, $imgs, $output, $st, $tool) ? $status : 1;
}

function lab58_cmd_convert(Lab57Proc $p, array $argv): int
{
    return lab58_im_convert($p, lab58_im_prog('convert'), array_slice($argv, 1), false);
}

/** mogrify: stejné volby na každý soubor zvlášť; přepisuje originál, nebo píše do -path / s příponou -format. */
function lab58_im_mogrify(Lab57Proc $p, string $prog, array $args, bool $im7): int
{
    $tool = 'mogrify';
    if ($args === []) { lab58_im_usage($p, $prog, $tool, $im7); return 1; }
    if (in_array($args[0], ['-version', '--version'], true)) { lab58_im_version($p, $im7); return 0; }
    $opts = [];
    $files = [];
    for ($i = 0, $n = count($args); $i < $n; $i++) {
        if (!lab58_im_is_option((string)$args[$i])) { $files[] = (string)$args[$i]; continue; }
        $opt = lab58_im_take_option($p, $prog, $args, $i, $tool);
        if ($opt === null) return 1;
        $opts[] = $opt;
    }
    if ($files === []) {
        $p->w->tip(tr('mogrify potřebuje jména souborů, např. mogrify -resize 50% *.jpg → man mogrify'));
        return 0;
    }
    $status = 0;
    foreach (array_slice($files, 0, LAB58_IM_MAX_FILES) as $file) {
        $st = lab58_im_state();
        $imgs = [];
        foreach ($opts as $opt) if (in_array($opt[0], ['density', 'size'], true)) $imgs = (array)lab58_im_apply($p, $prog, $imgs, $opt, $st, $tool);
        $imgs = lab58_im_read_into($p, $prog, [], $file, $st, $tool);
        if ($imgs === null) { $status = 1; continue; }
        foreach ($opts as $opt) {
            $imgs = lab58_im_apply($p, $prog, $imgs, $opt, $st, $tool);
            if ($imgs === null) return 1;
        }
        $name = Lab57Vfs::basename($file);
        $dir = str_contains($file, '/') ? substr($file, 0, (int)strrpos($file, '/') + 1) : '';
        if ($st['format'] !== null) {
            $ext = lab58_img_ext($name);
            $name = ($ext === '' ? $name : substr($name, 0, -strlen($ext) - 1)) . '.' . strtolower((string)$st['format']);
        }
        $target = ($st['path'] !== null ? rtrim((string)$st['path'], '/') . '/' : $dir) . $name;
        if (!lab58_im_write($p, $prog, $imgs, $target, $st, $tool)) $status = 1;
    }
    if (count($files) > LAB58_IM_MAX_FILES) $p->w->tip(tr('Simulace zpracuje najednou nejvýš {max} souborů.', ['max' => LAB58_IM_MAX_FILES]));
    return $status;
}

function lab58_cmd_mogrify(Lab57Proc $p, array $argv): int
{
    return lab58_im_mogrify($p, lab58_im_prog('mogrify'), array_slice($argv, 1), false);
}

/** magick (ImageMagick 7): magick vstup [volby] výstup, magick identify|mogrify|convert … */
function lab58_cmd_magick(Lab57Proc $p, array $argv): int
{
    $args = array_values(array_slice($argv, 1));
    $sub = (string)($args[0] ?? '');
    if (in_array($sub, ['identify', 'mogrify', 'convert'], true)) {
        $rest = array_slice($args, 1);
        return match ($sub) {
            'identify' => lab58_im_identify($p, 'magick', $rest, true),
            'mogrify' => lab58_im_mogrify($p, 'magick', $rest, true),
            default => lab58_im_convert($p, 'magick', $rest, true),
        };
    }
    if (in_array($sub, ['montage', 'compare', 'composite', 'animate', 'display', 'import', 'stream', 'conjure'], true)) {
        $p->err("magick: unable to open image '$sub': No such file or directory @ error/blob.c/OpenBlob/3596.\n");
        $p->w->tip(tr('Příkaz magick {sub} simulace nemá. Umí: magick vstup [volby] výstup, magick identify, magick mogrify. → man magick', ['sub' => $sub]));
        return 1;
    }
    return lab58_im_convert($p, 'magick', $args, true);
}

// ---------------------------------------------------------------------------
// Registrace
// ---------------------------------------------------------------------------

lab58_register_command('identify', 'lab58_cmd_identify', ['package' => 'imagemagick']);
lab58_register_command('convert', 'lab58_cmd_convert', ['package' => 'imagemagick']);
lab58_register_command('mogrify', 'lab58_cmd_mogrify', ['package' => 'imagemagick']);
lab58_register_command('magick', 'lab58_cmd_magick', ['package' => 'imagemagick']);
lab58_register_apt_package('imagemagick', [
    'version' => '8:6.9.11.60+dfsg-1.6+deb12u1', 'description' => 'image manipulation programs -- binaries',
    'size' => '189 kB', 'installed_size' => '322 kB', 'bins' => ['identify', 'convert', 'mogrify', 'magick'], 'preinstalled' => true,
]);
