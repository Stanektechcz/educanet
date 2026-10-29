<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček „Terminál pro grafiky“ (LAB-07) pro 1.A a 2.A.
 *
 * Příběh: školní grafické studio chystá podklady pro nový web školy. Úlohy: třídění souborů podle
 * typu (file), rozměry (identify), hromadné přejmenování (rename), velké soubory (find -size),
 * náhledy (mogrify -path -resize), WebP (-format -quality), metadata (exiftool) a smazání GPS
 * před zveřejněním (ochrana soukromí). Bez síťové teorie a bez archivů.
 *
 * Obrázky mají skutečné hlavičky formátů (linux_v58_cmd_media.php); fotky i GPS jsou smyšlené
 * (veřejně známá místa, žádné osoby). Vše deterministické ze semínka – náhoda jen přes $rng.
 * Generátory a kontroly media_* jsou obecné, může je použít i editor učitele.
 */

const LAB58_GRAF_MAX_FILES = 40;

/** Skalární parametr jako text (pole a objekty z editoru učitele → výchozí hodnota, žádné varování). */
function lab58_graf_s(mixed $v, string $default = ''): string
{
    return is_scalar($v) ? (string)$v : $default;
}

/** Jméno souboru bez lomítek, ne prázdné, ne . ani .. */
function lab58_graf_valid_name(string $name): bool
{
    return $name !== '' && $name !== '.' && $name !== '..' && !str_contains($name, '/') && strlen($name) <= 120;
}

/** Veřejně známá místa pro smyšlenou polohu fotek: [název, šířka, délka, výška m n. m.]. */
function lab58_graf_places(): array
{
    return [
        ['Sněžka', 50.7360, 15.7400, 1603], ['Ještěd', 50.7325, 14.9848, 1012], ['Pražský hrad', 50.0909, 14.4005, 250],
        ['Karlův most', 50.0865, 14.4114, 191], ['Lednice', 48.8012, 16.8047, 164], ['Pravčická brána', 50.8849, 14.2816, 420],
        ['Macocha', 49.3733, 16.7295, 470], ['Lipno', 48.6386, 14.2261, 900],
    ];
}

function lab58_graf_cameras(): array
{
    return [['Samsung', 'Galaxy A54 5G'], ['Apple', 'iPhone 13'], ['Xiaomi', 'Redmi Note 12'], ['Google', 'Pixel 7a'], ['Canon', 'Canon EOS 250D'], ['Nikon', 'NIKON Z 50']];
}

/** EXIF jako z fotoaparátu (datum z $w->now a semínka), volitelně autor, licence, popis a GPS. */
function lab58_graf_exif(Lab57World $w, Lab57Rng $r, array $p): array
{
    $exif = [];
    if (!empty($p['camera'])) {
        [$make, $model] = $r->pick(lab58_graf_cameras());
        $date = gmdate('Y:m:d H:i:s', $w->now - 86400 * max(1, (int)($p['days'] ?? 5)) - $r->int(0, 36000));
        $exif = [
            'Make' => $make, 'Model' => $model, 'Orientation' => 1, 'XResolution' => [[72, 1]], 'YResolution' => [[72, 1]], 'ResolutionUnit' => 2,
            'ModifyDate' => $date, 'ExposureTime' => [[1, (int)$r->pick([60, 125, 250, 500])]], 'FNumber' => [[(int)$r->pick([18, 20, 24, 28]), 10]],
            'ISO' => (int)$r->pick([50, 100, 200, 400]), 'DateTimeOriginal' => $date, 'CreateDate' => $date, 'FocalLength' => [[$r->int(40, 68), 10]],
        ];
    }
    foreach (['artist' => 'Artist', 'copyright' => 'Copyright'] as $key => $tag) {
        if (isset($p[$key])) $exif[$tag] = lab58_img_clean(lab58_fill($w, lab58_graf_s($p[$key]), $r));
    }
    if (!empty($p['descriptions'])) $exif['ImageDescription'] = lab58_img_clean(lab58_graf_s($r->pick(array_values((array)$p['descriptions']))));
    if (!empty($p['gps'])) {
        $place = array_values(is_array($p['gps_place'] ?? null) ? $p['gps_place'] : []);
        if (count($place) !== 4 || !is_numeric($place[1]) || !is_numeric($place[2]) || !is_numeric($place[3])) $place = $r->pick(lab58_graf_places());
        $lat = max(-89.9, min(89.9, (float)$place[1] + $r->int(-900, 900) / 1e6));
        $lon = max(-179.9, min(179.9, (float)$place[2] + $r->int(-900, 900) / 1e6));
        $exif = lab58_exif_set_gps($exif, $lat, $lon, (float)$place[3] + $r->int(0, 12));
    }
    return $exif;
}

/** Model obrázku podle přípony jména. params: w, h (číslo nebo [min,max]), ratio, even, q, cx, ctype, dpi, label (SVG) + EXIF. */
function lab58_graf_model(Lab57World $w, Lab57Rng $r, array $p, string $name): array
{
    $fmt = lab58_img_ext_format(lab58_img_ext($name)) ?? (in_array($p['fmt'] ?? '', LAB58_IMG_FORMATS, true) ? (string)$p['fmt'] : 'PNG');
    $wd = max(1, min(8000, lab58_gen_range($r, $p['w'] ?? 1600, 1600)));
    $ht = max(1, min(8000, isset($p['ratio']) ? (int)round($wd * (float)$p['ratio']) : lab58_gen_range($r, $p['h'] ?? 1200, 1200)));
    if (!empty($p['even'])) { $wd += $wd % 2; $ht += $ht % 2; }
    $opt = ['cx' => lab58_gen_range($r, $p['cx'] ?? [90, 115], 100), 'dpi' => (int)($p['dpi'] ?? 72)];
    if (in_array($fmt, ['JPEG', 'WEBP'], true)) $opt['q'] = lab58_gen_range($r, $p['q'] ?? 90, 90);
    if (isset($p['ctype'])) $opt['ctype'] = lab58_graf_s($p['ctype']);
    if ($fmt === 'SVG') $opt['svg'] = lab58_img_svg_source($wd, $ht, lab58_graf_s($p['label'] ?? 'EDUCANET', 'EDUCANET'), '#0a7e8c');
    if (in_array($fmt, ['JPEG', 'PNG', 'WEBP'], true)) $opt['exif'] = lab58_graf_exif($w, $r, $fmt === 'JPEG' ? $p : array_diff_key($p, ['camera' => 1, 'gps' => 1]));
    return lab58_img_new($fmt, $wd, $ht, $opt);
}

/** Zapíše model jako soubor (vlastník student v domovské složce, simulovaná velikost podle rozměrů a kvality). */
function lab58_graf_write(Lab57World $w, string $abs, array $m, int $days): void
{
    if ($w->fs->isDir($abs) || $abs === '/') return; // složku nikdy nepřepíše souborem
    $content = lab58_img_encode($m);
    $owner = str_starts_with($abs, $w->home('student') . '/') ? 'student' : 'root';
    $w->mkfile($abs, $content, 0644, $owner, null, $w->now - 86400 * max(0, $days), ['s' => lab58_img_calc_size(lab58_img_normalize($m), $content)]);
}

/** Obrázek ve VFS (bez kontroly práv – pro kontroly úloh); null = soubor chybí nebo není obrázek. */
function lab58_graf_read(Lab57World $w, string $abs): ?array
{
    $node = $w->fs->get($abs);
    if ($node === null || ($node['t'] ?? '') !== 'f') return null;
    return lab58_img_parse((string)($node['c'] ?? ''));
}

/** Jména souborů z parametru names (seznam) nebo z faktu fact (řádky). */
function lab58_graf_names(Lab57World $w, array $p): array
{
    $list = isset($p['names']) ? (array)$p['names'] : explode("\n", (string)($w->facts[lab58_graf_s($p['fact'] ?? '')] ?? ''));
    $names = array_map(static fn(mixed $n): string => Lab57Vfs::basename(lab58_fill($w, lab58_graf_s($n))), $list);
    return array_values(array_filter($names, 'lab58_graf_valid_name'));
}

/** @return array<string,array{0:int,1:int,2:int}> jméno → [šířka, výška, kvalita] z faktu <fact>_dims */
function lab58_graf_dims(Lab57World $w, string $fact): array
{
    $out = [];
    foreach (explode("\n", (string)($w->facts[$fact . '_dims'] ?? '')) as $line) {
        if (preg_match('/^(.+):(\d+)x(\d+):(\d+)$/', $line, $m) === 1) $out[$m[1]] = [(int)$m[2], (int)$m[3], (int)$m[4]];
    }
    return $out;
}

function lab58_graf_dir(Lab57World $w, array $p): string
{
    return lab58_gen_path($w, lab58_graf_s($p['dir'] ?? '~', '~'));
}

// ---------------------------------------------------------------------------
// Generátory
// ---------------------------------------------------------------------------

// media_image: jeden obrázek. params: path, fmt (jinak podle přípony), w, h, ratio, q, cx, ctype, camera, gps,
// artist, copyright, descriptions, label (SVG), days, fact (uloží cestu)
lab58_register_generator('media_image', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $abs = lab58_gen_path($w, lab58_graf_s($p['path'] ?? '~/obrazek.png', '~/obrazek.png'), $r);
    lab58_graf_write($w, $abs, lab58_graf_model($w, $r, $p, Lab57Vfs::basename($abs)), (int)($p['days'] ?? 2));
    if (isset($p['fact'])) $w->facts[lab58_graf_s($p['fact'])] = $abs;
});

// media_photos: série obrázků v jedné složce. params: dir, count, name ('IMG_{N}.JPG'), start, pad, gaps (mezery
// v číslování), names (zásobník jmen místo name), groups (přepisy parametrů s vlastním count a fact), secret
// {tag, text, fact} (jedna náhodná fotka), decoy {tag, text, count} + parametry media_image.
// Fakty: fact (a fact skupiny) = jména po řádcích, <fact>_dims = „jméno:ŠxV:kvalita“ po řádcích.
lab58_register_generator('media_photos', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $dir = lab58_gen_path($w, lab58_graf_s($p['dir'] ?? '~/fotky', '~/fotky'), $r);
    $pool = isset($p['names']) ? $r->shuffle(array_values((array)$p['names'])) : [];
    $num = lab58_gen_range($r, $p['start'] ?? 1, 1);
    if (!empty($p['gps'])) $p['gps_place'] = $r->pick(lab58_graf_places()); // jedna akce = jedno místo, fotky se liší jen o pár metrů
    $items = [];
    $groupFacts = [];
    foreach (is_array($p['groups'] ?? null) ? array_values($p['groups']) : [[]] as $raw) {
        $g = array_merge($p, (array)$raw);
        $count = min(lab58_gen_range($r, $g['count'] ?? 3, 3), LAB58_GRAF_MAX_FILES - count($items));
        $names = [];
        for ($i = 0; $i < $count; $i++) {
            if (isset($p['names'])) {
                $name = lab58_graf_s(array_shift($pool));
            } else {
                $name = lab58_fill($w, lab58_graf_s($g['name'] ?? 'IMG_{N}.jpg', 'IMG_{N}.jpg'), $r, ['N' => str_pad((string)$num, max(0, min(8, (int)($g['pad'] ?? 0))), '0', STR_PAD_LEFT)]);
                $num += !empty($g['gaps']) ? $r->int(1, 3) : 1;
            }
            $name = Lab57Vfs::basename($name);
            if (!lab58_graf_valid_name($name) || isset($items[$name])) continue;
            $items[$name] = lab58_graf_model($w, $r, $g, $name);
            $names[] = $name;
        }
        if (isset($raw['fact'])) $groupFacts[lab58_graf_s($raw['fact'])] = $names;
    }
    $all = array_keys($items);
    $secret = null;
    if (is_array($p['secret'] ?? null) && $all !== []) {
        $secret = $all[$r->int(0, count($all) - 1)];
        $tag = lab58_graf_s($p['secret']['tag'] ?? 'ImageDescription');
        if (isset(lab58_exif_table()[$tag])) $items[$secret]['exif'][$tag] = lab58_img_clean(lab58_fill($w, lab58_graf_s($p['secret']['text'] ?? '{CODE}', '{CODE}'), $r));
        if (isset($p['secret']['fact'])) $w->facts[lab58_graf_s($p['secret']['fact'])] = $secret;
    }
    if (is_array($p['decoy'] ?? null)) {
        $tag = lab58_graf_s($p['decoy']['tag'] ?? 'UserComment');
        $others = $r->shuffle(array_values(array_filter($all, static fn(string $n): bool => $n !== $secret)));
        foreach (array_slice($others, 0, max(0, (int)($p['decoy']['count'] ?? 1))) as $name) {
            if (isset(lab58_exif_table()[$tag])) $items[$name]['exif'][$tag] = lab58_img_clean(lab58_fill($w, lab58_graf_s($p['decoy']['text'] ?? '{DECOY}', '{DECOY}'), $r));
        }
    }
    $dims = [];
    foreach ($items as $name => $m) {
        lab58_graf_write($w, Lab57Vfs::normalize($name, $dir), $m, (int)($p['days'] ?? 3));
        $dims[$name] = $name . ':' . $m['w'] . 'x' . $m['h'] . ':' . $m['q'];
    }
    if (isset($p['fact'])) $groupFacts[lab58_graf_s($p['fact'])] = $all;
    foreach ($groupFacts as $fact => $names) {
        $w->facts[$fact] = implode("\n", $names);
        $w->facts[$fact . '_dims'] = implode("\n", array_map(static fn(string $n): string => $dims[$n], $names));
    }
});

// media_variants: několik verzí obrázku, právě jedna má cílové rozměry. params: dir, names, count, target [w,h],
// decoys (seznam [w,h]), q, cx, days, fact (jméno správného souboru)
lab58_register_generator('media_variants', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $dir = lab58_gen_path($w, lab58_graf_s($p['dir'] ?? '~/obrazky', '~/obrazky'), $r);
    $names = $r->shuffle(array_values(array_filter(array_map(static fn(mixed $v): string => Lab57Vfs::basename(lab58_graf_s($v)), (array)($p['names'] ?? [])), 'lab58_graf_valid_name')));
    $decoys = $r->shuffle(array_values(array_filter((array)($p['decoys'] ?? []), 'is_array')));
    if (count($names) < 2 || $decoys === []) return;
    $target = array_values((array)($p['target'] ?? [1920, 600]));
    $count = max(2, min(count($names), count($decoys) + 1, LAB58_GRAF_MAX_FILES, lab58_gen_range($r, $p['count'] ?? 5, 5)));
    $pick = $r->int(0, $count - 1);
    for ($i = 0; $i < $count; $i++) {
        $d = $i === $pick ? $target : array_values($decoys[$i < $pick ? $i : $i - 1]);
        $name = Lab57Vfs::basename($names[$i]);
        $spec = ['w' => (int)($d[0] ?? 1), 'h' => (int)($d[1] ?? 1)] + array_diff_key($p, ['ratio' => 1, 'even' => 1]);
        lab58_graf_write($w, Lab57Vfs::normalize($name, $dir), lab58_graf_model($w, $r, $spec, $name), (int)($p['days'] ?? 2));
        if ($i === $pick && isset($p['fact'])) $w->facts[lab58_graf_s($p['fact'])] = $name;
    }
});

// media_text_files: textové soubory. params: dir, files (jméno → obsah, šablony), mode, days, fact (jména po řádcích)
lab58_register_generator('media_text_files', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $dir = lab58_gen_path($w, lab58_graf_s($p['dir'] ?? '~', '~'), $r);
    $names = [];
    foreach (array_slice((array)($p['files'] ?? []), 0, LAB58_GRAF_MAX_FILES, true) as $name => $content) {
        $name = Lab57Vfs::basename(lab58_fill($w, (string)$name, $r));
        if (!lab58_graf_valid_name($name)) continue;
        lab58_gen_write($w, Lab57Vfs::normalize($name, $dir), lab58_fill($w, lab58_graf_s($content), $r), $p);
        $names[] = $name;
    }
    if (isset($p['fact'])) $w->facts[lab58_graf_s($p['fact'])] = implode("\n", $names);
});

// media_pick: náhodná hodnota ze seznamu do faktu (např. název akce pro README). params: fact, values
lab58_register_generator('media_pick', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $values = array_values(array_map('lab58_graf_s', (array)($p['values'] ?? [])));
    if ($values !== [] && isset($p['fact'])) $w->facts[lab58_graf_s($p['fact'])] = (string)$r->pick($values);
});

// ---------------------------------------------------------------------------
// Kontroly (prázdný seznam jmen = nesplněno, ať úloha nejde „vyřešit“ bez vygenerovaných dat)
// ---------------------------------------------------------------------------

// media_files_in: všechna jména (names | fact) jsou soubory ve složce dir. images: musí jít o obrázky; exact: nic jiného tam není
lab58_register_check('media_files_in', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $names = lab58_graf_names($w, $p);
    if ($names === []) return false;
    foreach ($names as $n) {
        $abs = Lab57Vfs::normalize($n, $dir);
        if (!$w->fs->isFile($abs) || (!empty($p['images']) && lab58_graf_read($w, $abs) === null)) return false;
    }
    if (!empty($p['exact'])) foreach ($w->fs->children($dir) as $child) if (!in_array($child, $names, true)) return false;
    return true;
});

// media_files_absent: žádné z jmen (names | fact) ve složce dir neexistuje
lab58_register_check('media_files_absent', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $names = lab58_graf_names($w, $p);
    if ($names === []) return false;
    foreach ($names as $n) if ($w->fs->exists(Lab57Vfs::normalize($n, $dir))) return false;
    return true;
});

// media_no_files: ve složce dir nejsou soubory (s příponou suffix); chybějící složka = splněno
lab58_register_check('media_no_files', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    if (!$w->fs->isDir($dir)) return !$w->fs->exists($dir);
    $suffix = lab58_graf_s($p['suffix'] ?? '');
    foreach ($w->fs->children($dir) as $name) {
        if ($suffix !== '' && !str_ends_with($name, $suffix)) continue;
        if ($w->fs->isFile(Lab57Vfs::normalize($name, $dir))) return false;
    }
    return true;
});

// media_images_in: ve složce dir je aspoň tolik obrázků (podle obsahu, ne přípony), kolik jmen mají fakty facts + plus + min
lab58_register_check('media_images_in', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $need = max(0, (int)($p['min'] ?? 0)) + max(0, (int)($p['plus'] ?? 0));
    foreach ((array)($p['facts'] ?? []) as $fact) $need += count(lab58_graf_names($w, ['fact' => lab58_graf_s($fact)]));
    if ($need < 1) return false;
    $found = 0;
    foreach ($w->fs->children($dir) as $name) if (lab58_graf_read($w, Lab57Vfs::normalize($name, $dir)) !== null) $found++;
    return $found >= $need;
});

// media_renamed: každé jméno z faktu má novou podobu podle šablony to ({N} = číslo ze starého jména, {f:…}); staré zmizelo
lab58_register_check('media_renamed', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $names = lab58_graf_names($w, $p);
    if ($names === []) return false;
    foreach ($names as $old) {
        if (preg_match('/(\d+)/', $old, $m) !== 1) return false;
        $new = Lab57Vfs::basename(lab58_fill($w, lab58_graf_s($p['to'] ?? '{N}', '{N}'), null, ['N' => $m[1]]));
        if ($new === $old || $w->fs->exists(Lab57Vfs::normalize($old, $dir)) || lab58_graf_read($w, Lab57Vfs::normalize($new, $dir)) === null) return false;
    }
    return true;
});

// media_thumbs: v podsložce sub jsou zmenšeniny všech obrázků z faktu (rozměry × percent / 100, tolerance 1 px)
lab58_register_check('media_thumbs', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $sub = Lab57Vfs::normalize(lab58_graf_s($p['sub'] ?? 'nahledy', 'nahledy'), $dir);
    $dims = lab58_graf_dims($w, lab58_graf_s($p['fact'] ?? ''));
    $pct = max(1, min(100, (int)($p['percent'] ?? 50)));
    if ($dims === []) return false;
    foreach ($dims as $name => [$ow, $oh]) {
        $t = lab58_graf_read($w, Lab57Vfs::normalize($name, $sub));
        if ($t === null || abs($t['w'] - $ow * $pct / 100) > 1 || abs($t['h'] - $oh * $pct / 100) > 1) return false;
    }
    return true;
});

// media_unchanged: obrázky z faktu mají pořád původní formát, rozměry i kvalitu
lab58_register_check('media_unchanged', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $dims = lab58_graf_dims($w, lab58_graf_s($p['fact'] ?? ''));
    if ($dims === []) return false;
    foreach ($dims as $name => [$ow, $oh, $oq]) {
        $m = lab58_graf_read($w, Lab57Vfs::normalize($name, $dir));
        $fmt = lab58_img_ext_format(lab58_img_ext($name));
        if ($m === null || $m['w'] !== $ow || $m['h'] !== $oh || $m['q'] !== $oq || ($fmt !== null && $m['fmt'] !== $fmt)) return false;
    }
    return true;
});

// media_webp_versions: ke každému obrázku z faktu existuje jméno.webp (WebP, stejné rozměry, kvalita 1..max_q)
lab58_register_check('media_webp_versions', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $dims = lab58_graf_dims($w, lab58_graf_s($p['fact'] ?? ''));
    $maxQ = max(1, min(100, (int)($p['max_q'] ?? 80)));
    if ($dims === []) return false;
    foreach ($dims as $name => [$ow, $oh]) {
        $dot = strrpos($name, '.');
        $m = lab58_graf_read($w, Lab57Vfs::normalize(($dot === false ? $name : substr($name, 0, $dot)) . '.webp', $dir));
        if ($m === null || $m['fmt'] !== 'WEBP' || $m['w'] !== $ow || $m['h'] !== $oh || $m['q'] < 1 || $m['q'] > $maxQ) return false;
    }
    return true;
});

// media_no_gps: všechny obrázky z faktu existují a nemají žádnou značku GPS*
lab58_register_check('media_no_gps', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $names = lab58_graf_names($w, $p);
    if ($names === []) return false;
    foreach ($names as $n) {
        $m = lab58_graf_read($w, Lab57Vfs::normalize($n, $dir));
        if ($m === null || lab58_exif_has_gps($m['exif'])) return false;
    }
    return true;
});

// media_has_tags: všechny obrázky z faktu mají neprázdné značky tags (např. Artist, Copyright)
lab58_register_check('media_has_tags', static function (Lab57World $w, array $p): bool {
    $dir = lab58_graf_dir($w, $p);
    $names = lab58_graf_names($w, $p);
    if ($names === []) return false;
    foreach ($names as $n) {
        $m = lab58_graf_read($w, Lab57Vfs::normalize($n, $dir));
        if ($m === null) return false;
        foreach ((array)($p['tags'] ?? ['Artist']) as $tag) if (trim((string)($m['exif'][lab58_graf_s($tag)] ?? '')) === '') return false;
    }
    return true;
});

// ---------------------------------------------------------------------------
// Úrovně
// ---------------------------------------------------------------------------

function lab58_levels_grafika(): array
{
    $photos = ['skola-zepredu.jpg', 'tabule.jpg', 'chodba.jpg', 'jidelna.jpg', 'knihovna.jpg', 'telocvicna.jpg'];
    $texts = [
        'texty-na-web.txt' => "Vítejte na webu naší školy!\nNajdete tu rozvrhy, akce a fotky z výuky.\n",
        'kontakty.txt' => "Sekretariát: 555 123 456\nE-mail: info@skola.test\n",
        'o-skole.md' => "# O škole\n\nMáme 24 tříd, dvě počítačové učebny a vlastní grafické studio.\n",
    ];
    return [
        [
            'id' => 'grafika-1', 'type' => 'check', 'title' => 'Úklid podkladů', 'difficulty' => 1, 'points' => 100, 'minutes' => 6,
            'story' => 'Nastupuješ do školního grafického studia. Pro nový web školy dorazila hromada podkladů – fotky, ikony, logo i texty, všechno v jedné složce ~/studio/podklady. Jeden soubor navíc nemá příponu.',
            'task' => 'Vytvoř složky ~/studio/obrazky a ~/studio/texty. Všechny obrázky (i ten bez přípony – typ zjistíš příkazem file) přesuň do obrazky, textové soubory (.txt, .md) do texty. Složka podklady má zůstat prázdná. Hotovo ověříš příkazem check.',
            'commands' => ['ls', 'file', 'mkdir', 'mv', 'check'],
            'hints' => [
                'Nejdřív se rozhlédni: file ~/studio/podklady/* ti u každého souboru řekne, co je zač.',
                'Složky vytvoříš najednou: mkdir ~/studio/obrazky ~/studio/texty. Hvězdička vybere víc souborů: mv ~/studio/podklady/*.jpg ~/studio/obrazky/',
                'Nezapomeň na png, svg a na soubor, který file označil jako „PNG image data“, i když nemá příponu. Texty: mv ~/studio/podklady/*.txt ~/studio/podklady/*.md ~/studio/texty/',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/podklady', 'fact' => 'graf_imgs', 'names' => $photos, 'count' => [2, 4], 'w' => [1600, 2400], 'ratio' => 0.6667, 'q' => [82, 90], 'camera' => true]],
                ['media_image', ['path' => '~/studio/podklady/ikona-mail.png', 'w' => 64, 'h' => 64, 'ctype' => 'rgba', 'cx' => 40]],
                ['media_image', ['path' => '~/studio/podklady/ikona-telefon.png', 'w' => 64, 'h' => 64, 'ctype' => 'rgba', 'cx' => 40]],
                ['media_image', ['path' => '~/studio/podklady/logo-skoly.svg', 'w' => 400, 'h' => 120, 'label' => 'Naše škola']],
                ['media_image', ['path' => '~/studio/podklady/logo_final', 'fmt' => 'PNG', 'w' => 512, 'h' => 512, 'ctype' => 'rgba', 'cx' => 60]],
                ['media_text_files', ['dir' => '~/studio/podklady', 'fact' => 'graf_texts', 'files' => $texts]],
            ],
            'checks' => [
                ['media_images_in', ['dir' => '~/studio/obrazky', 'facts' => ['graf_imgs'], 'plus' => 4, 'label' => 'Všechny obrázky (i ten bez přípony) jsou v ~/studio/obrazky']],
                ['media_files_in', ['dir' => '~/studio/texty', 'fact' => 'graf_texts', 'exact' => true, 'label' => 'V ~/studio/texty jsou právě textové soubory']],
                ['media_no_files', ['dir' => '~/studio/podklady', 'label' => 'Složka ~/studio/podklady je prázdná']],
            ],
            'solution' => ['cd ~/studio', 'file podklady/*', 'mkdir obrazky texty', 'mv podklady/*.jpg podklady/*.png podklady/*.svg podklady/logo_final obrazky/', 'mv podklady/*.txt podklady/*.md texty/', 'check'],
            'learn' => 'Přípona je jen část jména – o skutečném typu rozhoduje obsah. Příkaz file čte „magické bajty“ na začátku souboru (PNG začíná bajty 89 50 4E 47, JPEG FF D8). Hvězdička (*.jpg) vybere všechny soubory s danou koncovkou najednou.',
        ],
        [
            'id' => 'grafika-2', 'type' => 'answer', 'title' => 'Rozměry banneru', 'difficulty' => 1, 'points' => 100, 'minutes' => 5,
            'story' => 'Webmaster potřebuje do záhlaví webu banner přesně 1920 × 600 pixelů. Ve složce ~/studio/web/bannery je několik verzí a podle jména nepoznáš, která má správné rozměry.',
            'task' => 'Zjisti rozměry obrázků příkazem identify a odevzdej jméno souboru, který má přesně 1920x600 px: answer jméno-souboru',
            'commands' => ['ls', 'identify', 'answer'],
            'hints' => [
                'identify obrázek vypíše formát a rozměry (šířka x výška), třeba PNG 1920x600.',
                'Hvězdička prověří všechny soubory najednou: identify ~/studio/web/bannery/*',
                'Pozor na podobné rozměry (1920x640, 2400x750). Odevzdej jen jméno souboru, např. answer banner-leto.jpg',
            ],
            'answer_format' => 'jméno souboru, např. banner-leto.jpg',
            'generate' => [
                ['media_variants', ['dir' => '~/studio/web/bannery', 'fact' => 'graf_banner', 'count' => [5, 6], 'target' => [1920, 600], 'q' => [80, 90],
                    'names' => ['banner-jaro.png', 'banner-leto.jpg', 'banner-podzim.png', 'banner-zima.jpg', 'banner-den-otevrenych-dveri.png', 'banner-maturita.jpg', 'banner-sportovni-den.png', 'banner-ples.jpg'],
                    'decoys' => [[1920, 640], [1900, 600], [2400, 750], [1280, 400], [600, 1920], [1920, 1080], [960, 300]]]],
            ],
            'answer' => '{f:graf_banner}',
            'solution' => ['identify ~/studio/web/bannery/*', 'answer {f:graf_banner}'],
            'learn' => 'identify (balík ImageMagick) čte rozměry přímo z hlavičky souboru, obrázek nemusíš otevírat. Rozměry se píšou šířka x výška. Stejný poměr stran (2400x750 je taky 16 : 5) ještě neznamená stejnou velikost.',
        ],
        [
            'id' => 'grafika-3', 'type' => 'check', 'title' => 'Fotky z akce', 'difficulty' => 2, 'points' => 130, 'minutes' => 7,
            'story' => 'Z fotoaparátu dorazily fotky z akce pojmenované IMG_4821.JPG, IMG_4822.JPG… Na web ale patří jména, podle kterých se dají najít. Jak se mají jmenovat, stojí v souboru README.txt ve stejné složce.',
            'task' => 'Přečti ~/studio/akce/README.txt a hromadně přejmenuj všechny fotky IMG_ČÍSLO.JPG podle vzoru (číslo zůstane, přípona malými písmeny .jpg). README.txt nech, jak je.',
            'commands' => ['cat', 'ls', 'rename', 'check'],
            'hints' => [
                'Vzor jména je v README.txt: cat ~/studio/akce/README.txt',
                'rename používá náhradu jako Perl: rename \'s/staré/nové/\' soubory. Volba -n jen ukáže, co by se stalo, a nic nezmění.',
                'Číslo si zapamatuj závorkou a vlož jako $1: rename \'s/^IMG_(\d+)\.JPG$/NAZEV-$1.jpg/\' *.JPG (NAZEV nahraď podle README).',
            ],
            'generate' => [
                ['media_pick', ['fact' => 'graf_prefix', 'values' => ['sportovni-den', 'vanocni-koncert', 'den-otevrenych-dveri', 'lyzarsky-kurz', 'exkurze-planetarium', 'zahradni-slavnost', 'majales', 'akademie']]],
                ['media_photos', ['dir' => '~/studio/akce', 'fact' => 'graf_akce', 'name' => 'IMG_{N}.JPG', 'start' => [1200, 8800], 'gaps' => true, 'count' => [6, 9], 'w' => 4032, 'h' => 3024, 'q' => [88, 94], 'camera' => true]],
                ['file', ['path' => '~/studio/akce/README.txt', 'content' => "Fotky z akce: {f:graf_prefix}\nJména pro web: {f:graf_prefix}-ČÍSLO.jpg\nPříklad: IMG_1234.JPG -> {f:graf_prefix}-1234.jpg\nČíslo z fotoaparátu zachovej, ať jdou fotky dál po sobě.\n"]],
            ],
            'checks' => [
                ['media_renamed', ['dir' => '~/studio/akce', 'fact' => 'graf_akce', 'to' => '{f:graf_prefix}-{N}.jpg', 'label' => 'Všechny fotky mají jméno podle README.txt']],
                ['media_files_in', ['dir' => '~/studio/akce', 'names' => ['README.txt'], 'label' => 'README.txt zůstal na místě']],
            ],
            'solution' => ['cat ~/studio/akce/README.txt', 'cd ~/studio/akce', 'rename -n \'s/^IMG_(\d+)\.JPG$/{f:graf_prefix}-$1.jpg/\' *.JPG', 'rename \'s/^IMG_(\d+)\.JPG$/{f:graf_prefix}-$1.jpg/\' *.JPG', 'ls'],
            'learn' => 'rename přejmenuje stovky souborů jedním příkazem. Výraz s/co/čím/ je náhrada jako v Perlu: (\d+) si zapamatuje číslo a $1 ho vloží zpět. Vždycky nejdřív zkus rename -n – ukáže plán a nic nezmění.',
        ],
        [
            'id' => 'grafika-4', 'type' => 'check', 'title' => 'Moc těžké obrázky', 'difficulty' => 2, 'points' => 130, 'minutes' => 7,
            'story' => 'Web školy se načítá pomalu. Na vině jsou fotky přímo z fotoaparátu, které mají několik megabajtů. Grafik je zmenší, ale nejdřív je musí mít pohromadě.',
            'task' => 'Najdi ve složce ~/studio/web/img všechny soubory větší než 1 MB (přesněji 1 MiB) a přesuň je do nové složky ~/studio/k-uprave. Menší obrázky nech na místě.',
            'commands' => ['ls', 'du', 'find', 'mkdir', 'mv', 'check'],
            'hints' => [
                'Velikosti uvidíš přes ls -lh ~/studio/web/img (M = megabajty, K = kilobajty).',
                'find umí hledat podle velikosti: find ~/studio/web/img -type f -size +1M vypíše soubory větší než 1 MiB.',
                'Nalezené soubory rovnou přesuneš: find ~/studio/web/img -type f -size +1M -exec mv {} ~/studio/k-uprave/ \; (složku nejdřív vytvoř přes mkdir).',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/web/img', 'fact' => 'graf_img', 'camera' => true,
                    'names' => ['uvod.jpg', 'budova.jpg', 'jidelna.jpg', 'telocvicna.jpg', 'knihovna.jpg', 'laborator.jpg', 'hriste.jpg', 'atrium.jpg', 'ucebna-it.jpg'],
                    'groups' => [
                        ['count' => [2, 3], 'w' => 4032, 'h' => 3024, 'q' => [90, 95], 'cx' => [95, 120], 'fact' => 'graf_big'],
                        ['count' => [3, 5], 'w' => [1000, 1600], 'ratio' => 0.6667, 'q' => [75, 85], 'fact' => 'graf_small'],
                    ]]],
            ],
            'checks' => [
                ['media_files_in', ['dir' => '~/studio/k-uprave', 'fact' => 'graf_big', 'exact' => true, 'label' => 'V ~/studio/k-uprave jsou právě velké obrázky']],
                ['media_files_absent', ['dir' => '~/studio/web/img', 'fact' => 'graf_big', 'label' => 'Ve ~/studio/web/img už žádný velký obrázek není']],
                ['media_files_in', ['dir' => '~/studio/web/img', 'fact' => 'graf_small', 'label' => 'Menší obrázky zůstaly ve ~/studio/web/img']],
            ],
            'solution' => ['ls -lh ~/studio/web/img', 'mkdir -p ~/studio/k-uprave', 'find ~/studio/web/img -type f -size +1M -exec mv {} ~/studio/k-uprave/ \;'],
            'learn' => 'find -size +1M najde soubory větší než 1 MiB (k = kibibajty, M = mebibajty, G = gibibajty). S -exec … {} \; spustí příkaz pro každý nalezený soubor – {} se nahradí jeho cestou.',
        ],
        [
            'id' => 'grafika-5', 'type' => 'check', 'title' => 'Náhledy do galerie', 'difficulty' => 2, 'points' => 140, 'minutes' => 8,
            'story' => 'Do fotogalerie na webu patří malé náhledy – velká fotka se otevře až po kliknutí. Zmenšovat každou fotku ručně v editoru by trvalo věčnost, ImageMagick to zvládne jedním příkazem.',
            'task' => 'Ve složce ~/studio/galerie vytvoř podsložku nahledy a ulož do ní kopie všech fotek zmenšené na 50 % (stejná jména souborů). Originály musí zůstat v plné velikosti.',
            'commands' => ['cd', 'mkdir', 'identify', 'convert', 'mogrify', 'check'],
            'hints' => [
                'Jednu fotku zmenšíš takto: convert foto-01.jpg -resize 50% nahledy/foto-01.jpg',
                'mogrify zpracuje všechny fotky najednou, ale bez -path přepíše originály! S -path ukládá výsledky do jiné složky.',
                'cd ~/studio/galerie, pak mkdir nahledy a mogrify -path nahledy -resize 50% *.jpg',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/galerie', 'fact' => 'graf_gal', 'name' => 'foto-{N}.jpg', 'start' => 1, 'pad' => 2, 'count' => [4, 6], 'w' => [1600, 2400], 'ratio' => 0.75, 'even' => true, 'q' => [85, 92], 'camera' => true]],
            ],
            'checks' => [
                ['media_thumbs', ['dir' => '~/studio/galerie', 'sub' => 'nahledy', 'fact' => 'graf_gal', 'percent' => 50, 'label' => 'V ~/studio/galerie/nahledy jsou všechny fotky zmenšené na 50 %']],
                ['media_unchanged', ['dir' => '~/studio/galerie', 'fact' => 'graf_gal', 'label' => 'Originály mají pořád plnou velikost']],
            ],
            'solution' => ['cd ~/studio/galerie', 'mkdir nahledy', 'mogrify -path nahledy -resize 50% *.jpg', 'identify nahledy/*'],
            'learn' => 'mogrify upravuje soubory hromadně a bez -path je přepíše na místě – originál je pak pryč. -path složka uloží výsledky jinam. -resize 50% zachová poměr stran, stejně jako -resize 800x600 (to je „vejdi se do“).',
        ],
        [
            'id' => 'grafika-6', 'type' => 'check', 'title' => 'Web chce WebP', 'difficulty' => 2, 'points' => 140, 'minutes' => 8,
            'story' => 'Test rychlosti webu radí: „Používejte moderní formáty obrázků.“ WebP bývá při stejné kvalitě výrazně menší než JPEG i PNG.',
            'task' => 'Ke každému obrázku ve složce ~/studio/web/obrazky vytvoř vedle něj verzi WebP se stejným jménem (tym.jpg → tym.webp) a s kvalitou nejvýš 80. Originály nemaž ani neměň.',
            'commands' => ['cd', 'ls', 'identify', 'convert', 'mogrify', 'check'],
            'hints' => [
                'Jeden soubor: convert tym.jpg -quality 80 tym.webp – formát výstupu se pozná podle přípony.',
                'Hromadně: mogrify -format webp *.jpg *.png vytvoří ke každému souboru .webp a originál nechá být.',
                'Bez -quality převezme WebP kvalitu z JPEG (třeba 92). Přidej ji: mogrify -format webp -quality 80 *.jpg *.png',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp', 'w' => [1000, 1600], 'ratio' => 0.625, 'q' => [88, 95],
                    'names' => ['tym.jpg', 'historie.jpg', 'absolventi.jpg', 'kruzky.jpg'], 'count' => [2, 3]]],
                ['media_photos', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp_png', 'w' => [800, 1200], 'ratio' => 0.625, 'cx' => [60, 90],
                    'names' => ['kontakt.png', 'mapa-arealu.png', 'rozvrh.png'], 'count' => [1, 2]]],
            ],
            'checks' => [
                ['media_webp_versions', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp', 'max_q' => 80, 'label' => 'Ke každé fotce JPEG existuje .webp s kvalitou nejvýš 80']],
                ['media_webp_versions', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp_png', 'max_q' => 80, 'label' => 'Ke každému PNG existuje .webp s kvalitou nejvýš 80']],
                ['media_unchanged', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp', 'label' => 'Fotky JPEG zůstaly beze změny']],
                ['media_unchanged', ['dir' => '~/studio/web/obrazky', 'fact' => 'graf_webp_png', 'label' => 'Obrázky PNG zůstaly beze změny']],
            ],
            'solution' => ['cd ~/studio/web/obrazky', 'mogrify -format webp -quality 80 *.jpg *.png', 'ls -l'],
            'learn' => 'Formát výstupu určuje přípona (convert a.png a.webp) nebo u mogrify volba -format. -quality 0–100 řídí kompresi ztrátových formátů: menší číslo = menší soubor, ale víc artefaktů. Pro web obvykle stačí 75–80.',
        ],
        [
            'id' => 'grafika-7', 'type' => 'code', 'title' => 'Vzkaz v metadatech', 'difficulty' => 2, 'points' => 150, 'minutes' => 8,
            'story' => 'Kolega odchází ze studia a nechal ti vzkaz netradičně – v metadatech jedné fotky ve složce ~/studio/archiv. Fotka totiž nese kromě obrazu i text: popis, autora, datum, fotoaparát…',
            'task' => 'Najdi fotku, která má v popisu (Image Description) kód, a odevzdej ho příkazem submit. Pozor: kódy v poznámce (User Comment) jsou staré a neplatí.',
            'commands' => ['cd', 'ls', 'exiftool', 'identify', 'grep', 'submit'],
            'hints' => [
                'exiftool fotka.jpg vypíše všechna metadata jedné fotky.',
                'Jen vybranou značku u všech fotek najednou: exiftool -ImageDescription ~/studio/archiv/*.jpg',
                'Hledáš řádek „Image Description : Vzkaz pro nástupce: EDU-…“. Kód odevzdáš: submit EDU-XXXX-XXXX',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/archiv', 'fact' => 'graf_archiv', 'name' => 'IMG_{N}.jpg', 'start' => [2000, 7000], 'gaps' => true, 'count' => [5, 7],
                    'w' => [2000, 3000], 'ratio' => 0.75, 'q' => [85, 92], 'camera' => true, 'artist' => 'Školní fotokroužek',
                    'descriptions' => ['Chodba ve 2. patře', 'Školní zahrada na jaře', 'Pohled z tělocvičny', 'Učebna chemie', 'Vstupní hala', 'Knihovna – čtenářský koutek', 'Jídelna po obědě'],
                    'secret' => ['tag' => 'ImageDescription', 'text' => 'Vzkaz pro nástupce: {CODE}', 'fact' => 'graf_secret'],
                    'decoy' => ['tag' => 'UserComment', 'text' => 'Starý kód (už neplatí): {DECOY}', 'count' => 2]]],
            ],
            'solution' => ['cd ~/studio/archiv', 'exiftool -ImageDescription *.jpg', 'submit {CODE}'],
            'learn' => 'Metadata (EXIF) cestují s fotkou: fotoaparát, datum, autor, popis, někdy i poloha. exiftool je umí číst i měnit. Kdo fotku stáhne, uvidí je taky – proto se vyplatí vědět, co v nich je.',
        ],
        [
            'id' => 'grafika-8', 'type' => 'check', 'title' => 'Fotky bez polohy', 'difficulty' => 3, 'points' => 170, 'minutes' => 10,
            'story' => 'Fotky ze školního výletu jdou na web. Jenže mobil do nich uložil přesnou GPS polohu – kdo fotku stáhne, zjistí, kde přesně vznikla. U výletu to tolik nevadí, u fotky z domova nebo z tréninku už ano. Zvykni si polohu před zveřejněním mazat vždycky.',
            'task' => 'Odstraň ze všech fotek v ~/studio/zverejnit údaje GPS. Autor (Artist) a licence (Copyright) musí zůstat a záložní kopie *_original ve složce nenechávej.',
            'commands' => ['cd', 'exiftool', 'ls', 'rm', 'check'],
            'hints' => [
                'Nejdřív zjisti, co fotky prozrazují: exiftool -gps:all *.jpg',
                'Značky smažeš přiřazením prázdné hodnoty: exiftool -gps:all= *.jpg smaže jen polohu. Pozor, -all= by smazalo i autora a licenci.',
                'exiftool si nechává zálohy *_original. Buď přidej -overwrite_original, nebo je potom smaž: rm *_original',
            ],
            'generate' => [
                ['media_photos', ['dir' => '~/studio/zverejnit', 'fact' => 'graf_pub', 'name' => 'IMG_{N}.jpg', 'start' => [1000, 9000], 'gaps' => true, 'count' => [3, 5],
                    'w' => 4000, 'h' => 3000, 'q' => [88, 93], 'camera' => true, 'gps' => true, 'artist' => 'Školní fotokroužek', 'copyright' => 'CC BY 4.0, Školní fotokroužek']],
            ],
            'checks' => [
                ['media_no_gps', ['dir' => '~/studio/zverejnit', 'fact' => 'graf_pub', 'label' => 'Žádná fotka neobsahuje polohu GPS']],
                ['media_has_tags', ['dir' => '~/studio/zverejnit', 'fact' => 'graf_pub', 'tags' => ['Artist', 'Copyright'], 'label' => 'Autor a licence zůstaly zachované']],
                ['media_no_files', ['dir' => '~/studio/zverejnit', 'suffix' => '_original', 'label' => 'Ve složce nejsou záložní kopie *_original']],
            ],
            'solution' => ['cd ~/studio/zverejnit', 'exiftool -gps:all *.jpg', 'exiftool -gps:all= -overwrite_original *.jpg', 'exiftool -Artist -Copyright -gps:all *.jpg'],
            'learn' => 'Poloha v EXIF (GPS Latitude/Longitude) prozradí, kde fotka vznikla – třeba kde bydlíš. exiftool -gps:all= smaže jen polohu, -all= úplně všechno. Některé sociální sítě polohu mažou samy, ale e-mail, cloud nebo školní web ne – nespoléhej na to.',
        ],
    ];
}

lab58_register_pack(
    [
        'id' => 'grafika', 'title' => 'Terminál pro grafiky',
        'description' => 'Grafické studio chystá podklady pro školní web: třídění souborů, hromadné přejmenování, náhledy, WebP a fotky bez polohy.',
        'order' => 60, 'classes' => ['class_1a', 'class_2a'], 'unlock' => 'sequential',
        'badge' => ['id' => 'graficke-studio', 'label' => 'Grafik v terminálu', 'icon' => '🖼'], 'icon' => '🖼', 'tone' => 'rose',
        'inspired' => 'praxe grafického studia (ImageMagick, exiftool)',
    ],
    'lab58_levels_grafika'
);
