<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – katalog obecných generátorů/kontrol „core.*“ (CNT-01).
 *
 * Rozšiřuje ukázkovou sadu z linux_v57_levels.php (file, code_file, decoys, file_contains,
 * service_running – ty zůstávají tam, jsou součástí zdokumentovaného jádra v58, viz
 * docs/LAB_V58_API.md §3) o generátory a kontroly potřebné k převodu všech 46 vestavěných
 * úrovní na deklarativní tvar a o pár dalších z požadovaných kategorií (auth/syslog, proměnné
 * prostředí), které vestavěné balíčky nepoužívají, ale editor učitele (TCH-01) je nabízí.
 *
 * Pojmenování: `core_*` = obecný, znovupoužitelný kus katalogu (libovolná úroveň, libovolný
 * balíček). `v57_<id>` = úzce svázaná se scénářem jedné konkrétní vestavěné úrovně (viz
 * komentář u každého); jméno smí obsahovat jen [a-z0-9_] (registr generátorů nepovoluje tečku
 * ani pomlčku), proto '57.<id>' ze zadání zapisujeme jako 'v57_<id_s_podtrzitky>'.
 *
 * Bezpečnostní invariant: žádný generátor/kontrola nic nespouští ani nenavazuje síť – jen čte
 * a zapisuje do Lab57World (VFS, users/groups, services/procs, net, journal) a $w->facts.
 * Determinismus: veškerá náhoda jen přes $rng předaný jádrem (lab58_run_generators).
 */

// TCH-01: lab_v58_editor.php neodpovídá vzoru linux_v58_(cmd|levels)_*.php, takže by ho
// lab58_load_extensions() samo nenačetlo. require_once ho ale zajistí i na straně žáka (tenhle
// soubor jádro načítá vždy) – bez toho by balíčky „Úlohy od učitele“ nebyly žákům vidět.
// Views (jen učitelská strana) editor loaduje zvlášť přes teacher58_modules()['editor']['files'].
// lab_v58_content.php (CNT-03) je stejně bez vlastního auto-loaderu (nejde o cmd/levels soubor) –
// tady se zpřístupní cnt58_check_text() všem, kdo tuto vrstvu už načítají (lab, teacher.php ho
// vždy require_once-uje bez ohledu na záložku, takže i týmové hry ho po dispatchi mají k dispozici).
if (is_file(__DIR__ . '/lab_v58_content.php')) require_once __DIR__ . '/lab_v58_content.php';
if (is_file(__DIR__ . '/lab_v58_editor.php')) require_once __DIR__ . '/lab_v58_editor.php';

// ---------------------------------------------------------------------------
// Soubory a stromy
// ---------------------------------------------------------------------------

// core_file_set: víc fixních souborů najednou. params: files = [{path, content, mode, owner, group, days, extra, fact}, …]
lab58_register_generator('core_file_set', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    foreach ((array)($p['files'] ?? []) as $f) {
        if (!is_array($f)) continue;
        $abs = lab58_gen_path($w, (string)($f['path'] ?? ''), $r);
        lab58_gen_write($w, $abs, lab58_fill($w, (string)($f['content'] ?? ''), $r), $f);
        if (isset($f['fact'])) $w->facts[(string)$f['fact']] = $abs;
    }
});

// core_series: N podobně pojmenovaných souborů v jedné složce ({N} = 1..count). params: dir, count
// (int nebo [min,max]), name ('soubor-{N}.txt'), content (šablona), binary_len (int/[min,max],
// připojí náhodný binární šum), mode, owner, group, days, fact (uloží seznam cest, oddělený \n)
lab58_register_generator('core_series', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $dir = lab58_gen_path($w, (string)($p['dir'] ?? '~'), $r);
    $count = max(1, lab58_gen_range($r, $p['count'] ?? 1, 1));
    $paths = [];
    for ($n = 1; $n <= $count; $n++) {
        $name = lab58_fill($w, (string)($p['name'] ?? 'soubor-{N}.txt'), $r, ['N' => (string)$n]);
        $abs = Lab57Vfs::normalize($name, $dir);
        $content = lab58_fill($w, (string)($p['content'] ?? ''), $r, ['N' => (string)$n]);
        if (isset($p['binary_len'])) $content .= lab57_binary_noise($r, max(0, lab58_gen_range($r, $p['binary_len'], 0)));
        lab58_gen_write($w, $abs, $content, $p);
        $paths[] = $abs;
    }
    if (isset($p['fact'])) $w->facts[(string)$p['fact']] = implode("\n", $paths);
});

// core_mkdir: jedna (i vnořená) prázdná složka. params: path, mode (výchozí 0775), owner, group
lab58_register_generator('core_mkdir', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/slozka'), $r);
    $owner = (string)($p['owner'] ?? (str_starts_with($abs, $w->home('student') . '/') ? 'student' : 'root'));
    if (!isset($w->users[$owner])) $owner = 'root';
    $w->mkdirp($abs, lab58_gen_mode($p['mode'] ?? 0775, 0775), $owner, isset($p['group']) ? (string)$p['group'] : null);
});

// core_haystack_text: N souborů, jen jeden je čitelný text s kódem, ostatní binární šum.
// params: dir, count, pad (počet nul ve jméně), name_tpl ('-soubor{N}'), noise_len, content, fact (výchozí good_path)
lab58_register_generator('core_haystack_text', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $dir = lab58_gen_path($w, (string)($p['dir'] ?? '~/haystack'), $r);
    $count = max(2, (int)($p['count'] ?? 10));
    $good = $r->int(0, $count - 1);
    $pad = max(0, (int)($p['pad'] ?? 0));
    $goodAbs = '';
    for ($i = 0; $i < $count; $i++) {
        $numStr = $pad > 0 ? str_pad((string)$i, $pad, '0', STR_PAD_LEFT) : (string)$i;
        $abs = Lab57Vfs::normalize(lab58_fill($w, (string)($p['name_tpl'] ?? 'soubor{N}'), $r, ['N' => $numStr]), $dir);
        if ($i === $good) {
            lab58_gen_write($w, $abs, lab58_fill($w, (string)($p['content'] ?? "Kód: {CODE}\n"), $r), $p);
            $goodAbs = $abs;
        } else {
            lab58_gen_write($w, $abs, lab57_binary_noise($r, max(1, (int)($p['noise_len'] ?? 33))), $p);
        }
    }
    $w->facts[(string)($p['fact'] ?? 'good_path')] = $goodAbs;
});

// core_haystack_size: mřížka složek × souborů, jeden má přesně target_size bajtů a není
// spustitelný; některé návnady mají schválně stejnou velikost, ale jsou spustitelné (aby
// nešlo hádat jen podle -size). params: dir, dirs (počet), dir_tpl, names (šablony souborů
// v každé složce), target_size, decoy_min, decoy_max, share_chance (1 z N návnad sdílí
// velikost), content (šablona dobrého souboru), fact (výchozí good_path)
lab58_register_generator('core_haystack_size', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $base = lab58_gen_path($w, (string)($p['dir'] ?? '~/haystack'), $r);
    $dirs = max(1, (int)($p['dirs'] ?? 20));
    $names = array_values((array)($p['names'] ?? ['soubor']));
    $count = count($names);
    $targetDir = $r->int(0, $dirs - 1);
    $targetFile = $r->int(0, $count - 1);
    $target = (int)($p['target_size'] ?? 1000);
    $decoyMin = (int)($p['decoy_min'] ?? 200);
    $decoyMax = (int)($p['decoy_max'] ?? 9000);
    $shareChance = max(1, (int)($p['share_chance'] ?? 4));
    $dirTpl = (string)($p['dir_tpl'] ?? 'haystack{N}');
    $goodAbs = '';
    for ($d = 0; $d < $dirs; $d++) {
        $dirName = lab58_fill($w, $dirTpl, $r, ['N' => str_pad((string)$d, 2, '0', STR_PAD_LEFT)]);
        $dirAbs = Lab57Vfs::normalize($dirName, $base);
        foreach ($names as $f => $nameTpl) {
            $abs = Lab57Vfs::normalize(lab58_fill($w, (string)$nameTpl, $r), $dirAbs);
            if ($d === $targetDir && $f === $targetFile) {
                $body = lab58_fill($w, (string)($p['content'] ?? "Kód: {CODE}\n"), $r);
                lab58_gen_write($w, $abs, $body . str_repeat(' ', max(0, $target - strlen($body))), $p);
                $goodAbs = $abs;
                continue;
            }
            $size = $r->int(0, $shareChance - 1) === 0 ? $target : $r->int($decoyMin, $decoyMax);
            $mode = $size === $target ? 0750 : ($r->int(0, 1) ? 0640 : 0750);
            $decoyP = $p;
            $decoyP['mode'] = $mode;
            lab58_gen_write($w, $abs, substr(str_repeat(implode(' ', lab57_filler($r, 3)) . "\n", 200), 0, $size), $decoyP);
        }
    }
    $w->facts[(string)($p['fact'] ?? 'good_path')] = $goodAbs;
});

// ---------------------------------------------------------------------------
// Práva a vlastnictví (na už existující cestě – ať vytvořené generátorem, nebo součást
// výchozího světa jako /var/www/html/index.html)
// ---------------------------------------------------------------------------

// core_chmod: nastaví (nebo náhodně vybere z modes) práva existující cesty. params: path, mode, modes (list – náhodný výběr má přednost)
lab58_register_generator('core_chmod', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $abs = lab58_gen_path($w, (string)($p['path'] ?? ''), $r);
    $node = $w->fs->get($abs);
    if ($node === null) return;
    $modes = (array)($p['modes'] ?? []);
    $node['m'] = $modes !== [] ? lab58_gen_mode($r->pick($modes)) : lab58_gen_mode($p['mode'] ?? $node['m']);
    $w->fs->set($abs, $node);
});

// core_chown: nastaví vlastníka/skupinu existující cesty. params: path, owner, group
lab58_register_generator('core_chown', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $abs = lab58_gen_path($w, (string)($p['path'] ?? ''), $r);
    $node = $w->fs->get($abs);
    if ($node === null) return;
    if (isset($p['owner'])) $node['u'] = (string)$p['owner'];
    if (isset($p['group'])) $node['g'] = (string)$p['group'];
    $w->fs->set($abs, $node);
});

lab58_register_check('core_dir_exists', static fn(Lab57World $w, array $p): bool => $w->fs->isDir(lab58_gen_path($w, (string)($p['path'] ?? ''))));
lab58_register_check('core_file_exists', static fn(Lab57World $w, array $p): bool => $w->fs->isFile(lab58_gen_path($w, (string)($p['path'] ?? ''))));
lab58_register_check('core_not_exists', static fn(Lab57World $w, array $p): bool => !$w->fs->exists(lab58_gen_path($w, (string)($p['path'] ?? ''))));
lab58_register_check('core_executable', static fn(Lab57World $w, array $p): bool => ((int)($w->fs->get(lab58_gen_path($w, (string)($p['path'] ?? '')))['m'] ?? 0) & 0100) !== 0);
lab58_register_check('core_mode_no_other_write', static fn(Lab57World $w, array $p): bool => ((int)($w->fs->get(lab58_gen_path($w, (string)($p['path'] ?? '')))['m'] ?? 0) & 0002) === 0);

// core_files_exist: všechny cesty ze seznamu 'paths' musí být soubory
lab58_register_check('core_files_exist', static function (Lab57World $w, array $p): bool {
    foreach ((array)($p['paths'] ?? []) as $path) if (!$w->fs->isFile(lab58_gen_path($w, (string)$path))) return false;
    return true;
});

// core_children_count: kolik položek ve složce má daný suffix (''=všechny). params: dir, suffix, count|min|max
lab58_register_check('core_children_count', static function (Lab57World $w, array $p): bool {
    $dir = lab58_gen_path($w, (string)($p['dir'] ?? ''));
    $suffix = (string)($p['suffix'] ?? '');
    $n = count(array_filter($w->fs->children($dir), static fn(string $name): bool => $suffix === '' || str_ends_with($name, $suffix)));
    if (isset($p['count']) && $n !== (int)$p['count']) return false;
    if (isset($p['min']) && $n < (int)$p['min']) return false;
    if (isset($p['max']) && $n > (int)$p['max']) return false;
    return true;
});

// ---------------------------------------------------------------------------
// Logy: access/auth/syslog, chybové logy s úrovněmi
// ---------------------------------------------------------------------------

// core_access_log: webový přístupový log (lab57_gen_access_log). params: path, lines (int/[min,max]),
// start_offset (s), ips, count_needle (volitelně – spočítá řádky obsahující text a uloží jako fakt
// 'fact', výchozí jméno faktu 'log_count'; hodí se pro answer/solution šablony typu „kolik je 404“)
lab58_register_generator('core_access_log', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = lab58_gen_range($r, $p['lines'] ?? [60, 90], 75);
    $start = $w->now - (int)($p['start_offset'] ?? 7200);
    $body = lab57_gen_access_log($r, $n, $start, (array)($p['ips'] ?? []));
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/access.log'), $r);
    lab58_gen_write($w, $abs, $body, $p);
    if (isset($p['count_needle'])) {
        $needle = (string)$p['count_needle'];
        $count = count(array_filter(explode("\n", $body), static fn(string $l): bool => str_contains($l, $needle)));
        $w->facts[(string)($p['fact'] ?? 'log_count')] = (string)$count;
    }
});

// core_access_log_topips: access log s náhodným počtem IP adres (4–11) a rostoucí váhou –
// hodí se pro „nejčastější IP/uniq -c“ úlohy. params: path, ip_count [min,max], ip_prefix, pool_tag
lab58_register_generator('core_access_log_topips', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $range = (array)($p['ip_count'] ?? [4, 11]);
    $ips = [];
    for ($i = $r->int((int)$range[0], (int)$range[1]); $i > 0; $i--) $ips[] = (string)($p['ip_prefix'] ?? '10.0.0.') . $r->int(2, 250);
    $ips = array_values(array_unique($ips));
    $pool = [];
    foreach ($ips as $k => $ip) for ($n = 0; $n < 3 + ($k * 3) + $k; $n++) $pool[] = $ip;
    $r2 = new Lab57Rng($w->seed . '|' . (string)($p['pool_tag'] ?? 'pool'));
    $body = lab57_gen_access_log($r2, 0, $w->now) . implode('', array_map(static function (string $ip) use ($r2, $w): string {
        return lab57_gen_access_log(new Lab57Rng($ip . $r2->next()), 1, $w->now - 3600, [$ip]);
    }, $r->shuffle($pool)));
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/access.log'), $r);
    lab58_gen_write($w, $abs, $body, $p);
});

// core_level_log: aplikační log s úrovněmi (INFO/WARN/ERROR…) a volitelnou „návnadovou“
// poslední řádkou. params: path, rows [min,max], levels (seznam, opakuj pro váhu), messages,
// interval_s (rozestup v simulovaném čase), decoy_line (šablona, přidá se jako poslední řádek)
lab58_register_generator('core_level_log', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = lab58_gen_range($r, $p['rows'] ?? [40, 90], 60);
    $levels = (array)($p['levels'] ?? ['INFO']);
    $messages = (array)($p['messages'] ?? ['udalost']);
    $interval = (int)($p['interval_s'] ?? 37);
    $out = [];
    for ($i = $n; $i > 0; $i--) $out[] = date('H:i:s', $w->now - $i * $interval) . ' ' . (string)$r->pick($levels) . ' ' . (string)$r->pick($messages) . ' ' . $r->int(1, 999);
    if (isset($p['decoy_line'])) $out[] = date('H:i:s', $w->now) . ' ' . lab58_fill($w, (string)$p['decoy_line'], $r);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/app.log'), $r);
    lab58_gen_write($w, $abs, lab57_join($r->shuffle($out)), $p);
});

// core_auth_log: simulovaný /var/log/auth.log (přihlášení, sudo). params: path, lines [min,max], users, needle (šablona, volitelný extra řádek)
lab58_register_generator('core_auth_log', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = lab58_gen_range($r, $p['lines'] ?? [30, 60], 40);
    $users = (array)($p['users'] ?? ['student', 'root']);
    $templates = ['Accepted password for {U} from 10.0.0.{IP} port 22 ssh2', 'Failed password for {U} from 10.0.0.{IP} port 22 ssh2', 'sudo: {U} : COMMAND=/usr/bin/whoami'];
    $out = [];
    for ($i = $n; $i > 0; $i--) {
        $tpl = (string)$r->pick($templates);
        $line = str_replace(['{U}', '{IP}'], [(string)$r->pick($users), (string)$r->int(2, 250)], $tpl);
        $out[] = date('M j H:i:s', $w->now - $i * 53) . ' ' . $w->hostname . ' sshd[' . (1000 + $i) . ']: ' . $line;
    }
    if (isset($p['needle'])) $out[] = date('M j H:i:s', $w->now) . ' ' . $w->hostname . ' ' . lab58_fill($w, (string)$p['needle'], $r);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '/var/log/auth.log'), $r);
    lab58_gen_write($w, $abs, lab57_join($out), $p);
});

// core_syslog: obecný systémový log (jádro, systemd, cron…). params: path, lines [min,max], units, messages, needle
lab58_register_generator('core_syslog', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = lab58_gen_range($r, $p['lines'] ?? [30, 60], 40);
    $units = (array)($p['units'] ?? ['kernel', 'systemd', 'cron', 'NetworkManager']);
    $messages = (array)($p['messages'] ?? ['stav beze změny', 'perioda dokončena', 'zařízení připraveno']);
    $out = [];
    for ($i = $n; $i > 0; $i--) $out[] = date('M j H:i:s', $w->now - $i * 41) . ' ' . $w->hostname . ' ' . (string)$r->pick($units) . '[' . $r->int(100, 999) . ']: ' . (string)$r->pick($messages);
    if (isset($p['needle'])) $out[] = date('M j H:i:s', $w->now) . ' ' . $w->hostname . ' ' . lab58_fill($w, (string)$p['needle'], $r);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '/var/log/syslog'), $r);
    lab58_gen_write($w, $abs, lab57_join($out), $p);
});

// ---------------------------------------------------------------------------
// Slova/text: tabulky s jedním „jehličkovým“ řádkem, unikátní řádek, diff dvojice, binární šum se značkami
// ---------------------------------------------------------------------------

// core_filler_lines: N „vycpávkových“ vět, volitelně s jednou needle větou na náhodné pozici.
// params: path, count (int/[min,max]), needle (šablona), needle_pos ([min,max], výchozí [0,count]), prefix
lab58_register_generator('core_filler_lines', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = max(0, lab58_gen_range($r, $p['count'] ?? 40, 40));
    $lines = lab57_filler($r, $n);
    if (isset($p['needle'])) {
        $pos = (array)($p['needle_pos'] ?? [0, $n]);
        $at = $r->int((int)$pos[0], min((int)($pos[1] ?? $n), $n));
        array_splice($lines, $at, 0, [lab58_fill($w, (string)$p['needle'], $r)]);
    }
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/data.txt'), $r);
    lab58_gen_write($w, $abs, lab58_fill($w, (string)($p['prefix'] ?? ''), $r) . lab57_join($lines), $p);
});

// core_keyword_table: tabulka slovo+hodnota, jeden řádek má needle_word + needle_value (výchozí {CODE}).
// params: path, words, rows, sep, needle_word, needle_value, decoy_value
lab58_register_generator('core_keyword_table', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $words = array_values((array)($p['words'] ?? ['slovo']));
    $rows = (int)($p['rows'] ?? 100);
    $sep = (string)($p['sep'] ?? "\t");
    $out = [];
    for ($i = 0; $i < $rows; $i++) $out[] = (string)$r->pick($words) . $sep . lab58_fill($w, (string)($p['decoy_value'] ?? '{DECOY}'), $r);
    $pos = $r->int(0, max(0, $rows - 1));
    $needle = (string)($p['needle_word'] ?? '') . $sep . lab58_fill($w, (string)($p['needle_value'] ?? '{CODE}'), $r);
    array_splice($out, $pos, 0, [$needle]);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/data.txt'), $r);
    lab58_gen_write($w, $abs, lab57_join($out), $p);
});

// core_unique_row: skupiny opakovaných „návnadových“ řádků + jeden opravdu unikátní (výchozí {CODE}).
// params: path, groups (počet skupin), repeat [min,max] (kolikrát se skupina opakuje), decoy_value, needle_value
lab58_register_generator('core_unique_row', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $groups = (int)($p['groups'] ?? 40);
    $repeat = (array)($p['repeat'] ?? [2, 6]);
    $rows = [];
    for ($i = 0; $i < $groups; $i++) {
        $decoy = lab58_fill($w, (string)($p['decoy_value'] ?? '{DECOY}'), $r);
        for ($k = $r->int((int)$repeat[0], (int)$repeat[1]); $k > 0; $k--) $rows[] = $decoy;
    }
    $rows[] = lab58_fill($w, (string)($p['needle_value'] ?? '{CODE}'), $r);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/data.txt'), $r);
    lab58_gen_write($w, $abs, lab57_join($r->shuffle($rows)), $p);
});

// core_diff_pair: dva skoro stejné soubory lišící se jedním řádkem. params: path_a, path_b, rows, pos [min,max], decoy_value, needle_value
lab58_register_generator('core_diff_pair', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $rows = (int)($p['rows'] ?? 100);
    $base = [];
    for ($i = 0; $i < $rows; $i++) $base[] = lab58_fill($w, (string)($p['decoy_value'] ?? '{DECOY}'), $r);
    $changed = $base;
    $range = (array)($p['pos'] ?? [10, max(10, $rows - 10)]);
    $pos = $r->int((int)$range[0], (int)$range[1]);
    $changed[$pos] = lab58_fill($w, (string)($p['needle_value'] ?? '{CODE}'), $r);
    lab58_gen_write($w, lab58_gen_path($w, (string)($p['path_a'] ?? '~/a.txt'), $r), lab57_join($base), $p);
    lab58_gen_write($w, lab58_gen_path($w, (string)($p['path_b'] ?? '~/b.txt'), $r), lab57_join($changed), $p);
});

// core_binary_markers: binární soubor poskládaný ze šumu a čitelných značek (pro strings/grep).
// params: path, parts = [{noise: int|[min,max]} | {text: šablona}, …]
lab58_register_generator('core_binary_markers', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $out = '';
    foreach ((array)($p['parts'] ?? []) as $part) {
        if (!is_array($part)) continue;
        if (isset($part['noise'])) $out .= lab57_binary_noise($r, max(0, lab58_gen_range($r, $part['noise'], 0)));
        if (isset($part['text'])) $out .= "\x00" . lab58_fill($w, (string)$part['text'], $r) . "\x00";
    }
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/data.bin'), $r);
    lab58_gen_write($w, $abs, $out, $p);
});

// core_wrapped_message: text zabalený do řetězce kódování/newline kroků (base64/hex/rot13).
// params: path, content (šablona), encode = seznam kroků: 'newline'|'base64'|'hex'|'rot13'
lab58_register_generator('core_wrapped_message', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $text = lab58_fill($w, (string)($p['content'] ?? '{CODE}'), $r);
    foreach ((array)($p['encode'] ?? []) as $step) {
        $text = match ($step) {
            'newline' => $text . "\n",
            'base64' => base64_encode($text),
            'hex' => bin2hex($text),
            'rot13' => lab57_rot13($text),
            default => $text,
        };
    }
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/zprava.txt'), $r);
    lab58_gen_write($w, $abs, $text, $p);
    if (isset($p['fact'])) $w->facts[(string)$p['fact']] = $abs;
});

// core_number_base: číslo zapsané v soustavě (2/8/16), fakta <fact>, <fact>_digits, <fact>_path.
// params: path, min, max, base (2|8|16), label (text před číslicemi), fact (výchozí number)
lab58_register_generator('core_number_base', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $n = $r->int((int)($p['min'] ?? 0), (int)($p['max'] ?? 999));
    $base = (int)($p['base'] ?? 2);
    $digits = match ($base) { 16 => dechex($n), 8 => decoct($n), default => decbin($n) };
    $label = lab58_fill($w, (string)($p['label'] ?? 'Číslo: '), $r);
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/cislo.txt'), $r);
    lab58_gen_write($w, $abs, $label . $digits . "\n", $p);
    $fact = (string)($p['fact'] ?? 'number');
    $w->facts[$fact] = (string)$n;
    $w->facts[$fact . '_digits'] = $digits;
    $w->facts[$fact . '_path'] = $abs;
});

// core_checksum_pack: N položek, jedna odpovídá sha256 v hash_path. params: count, path_tpl
// ('~/polozka-{N}.txt'), hash_path, content_tpl (musí obsahovat {VALUE} – nahradí se {CODE}
// jen u pravé položky, jinde {DECOY}), fact (uloží <fact>_path)
lab58_register_generator('core_checksum_pack', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $count = max(2, (int)($p['count'] ?? 5));
    $good = $r->int(1, $count);
    $tpl = (string)($p['content_tpl'] ?? "Položka č. {N}\nKód: {VALUE}\n");
    $goodAbs = '';
    for ($i = 1; $i <= $count; $i++) {
        $line = str_replace('{VALUE}', $i === $good ? '{CODE}' : '{DECOY}', $tpl);
        $content = lab58_fill($w, $line, $r, ['N' => (string)$i]);
        $abs = lab58_gen_path($w, lab58_fill($w, (string)($p['path_tpl'] ?? '~/polozka-{N}.txt'), $r, ['N' => (string)$i]), $r);
        lab58_gen_write($w, $abs, $content, $p);
        if ($i === $good) {
            $goodAbs = $abs;
            $hashAbs = lab58_gen_path($w, (string)($p['hash_path'] ?? '~/ocekavany.sha256'), $r);
            lab58_gen_write($w, $hashAbs, hash('sha256', $content) . "\n", $p);
        }
    }
    $w->facts[(string)($p['fact'] ?? 'good') . '_path'] = $goodAbs;
});

// ---------------------------------------------------------------------------
// CSV / tabulky
// ---------------------------------------------------------------------------

// core_name_score_csv: CSV jméno,body s náhodným pořadím jmen i bodů (bez opakování). params: path, header, count, score_range
lab58_register_generator('core_name_score_csv', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $count = max(2, (int)($p['count'] ?? 12));
    $names = $r->shuffle(lab57_first_names());
    $range = (array)($p['score_range'] ?? [10, 99]);
    $points = $r->shuffle(range((int)$range[0], (int)$range[1]));
    $rows = [(string)($p['header'] ?? 'jmeno,body')];
    for ($i = 0; $i < $count; $i++) $rows[] = $names[$i % count($names)] . ',' . $points[$i % count($points)];
    $abs = lab58_gen_path($w, (string)($p['path'] ?? '~/body.csv'), $r);
    lab58_gen_write($w, $abs, lab57_join($rows), $p);
});

// core_conf_tree: strom konfiguračních souborů *.conf v pár podsložkách + volitelný README.txt navíc.
// params: root, subdirs (list, '' = kořen stromu), count_range [min,max], names (pool jmen), extra_readme
lab58_register_generator('core_conf_tree', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $root = lab58_gen_path($w, (string)($p['root'] ?? '/etc/app'), $r);
    $range = (array)($p['count_range'] ?? [1, 3]);
    $names = array_values((array)($p['names'] ?? ['app']));
    foreach ((array)($p['subdirs'] ?? ['']) as $dir) {
        $base = rtrim($root . '/' . (string)$dir, '/');
        for ($i = $r->int((int)$range[0], (int)$range[1]); $i > 0; $i--) {
            $name = (string)$r->pick($names) . $r->int(1, 99) . '.conf';
            lab58_gen_write($w, $base . '/' . $name, "key=value\n", $p);
        }
        if (!empty($p['extra_readme'])) lab58_gen_write($w, $base . '/README.txt', "doc\n", $p);
    }
});

// ---------------------------------------------------------------------------
// Služby a procesy
// ---------------------------------------------------------------------------

// core_service: definice jedné služby (systemctl). params: name, desc, active, enabled, failed,
// result, since_offset (s), ports, procs ([user,cmd]…), validate (klíč do $w->programs['validate:*'])
lab58_register_generator('core_service', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $name = (string)($p['name'] ?? '');
    if ($name === '') return;
    $w->services[$name] = [
        'desc' => (string)($p['desc'] ?? ''), 'active' => (bool)($p['active'] ?? true), 'enabled' => (bool)($p['enabled'] ?? true),
        'failed' => (bool)($p['failed'] ?? false), 'result' => $p['result'] ?? null, 'since' => $w->now - (int)($p['since_offset'] ?? 60),
        'ports' => array_values((array)($p['ports'] ?? [])), 'procs' => array_values((array)($p['procs'] ?? [])),
    ];
    if (isset($p['validate'])) $w->services[$name]['validate'] = (string)$p['validate'];
});

// core_service_enabled: služba je nastavená na automatický start (systemctl enable).
lab58_register_check('core_service_enabled', static fn(Lab57World $w, array $p): bool => !empty($w->services[(string)($p['service'] ?? '')]['enabled'] ?? false));

// core_process: jeden běžící proces (ps/top/kill). params: pid (int/[min,max]), user, cmd
// (řetězec nebo seznam – náhodný výběr), cpu, mem, vsz, rss, tty, stat, start_offset (s),
// service, ignore_term (bool nebo 'random'), fact (výchozí pid)
lab58_register_generator('core_process', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $pid = lab58_gen_range($r, $p['pid'] ?? [2000, 2999], 2500);
    $cmdOpt = $p['cmd'] ?? 'proces';
    $cmd = is_array($cmdOpt) ? (string)$r->pick($cmdOpt) : (string)$cmdOpt;
    $ignore = $p['ignore_term'] ?? false;
    $w->procs[$pid] = [
        'user' => (string)($p['user'] ?? 'student'), 'cmd' => $cmd, 'cpu' => (float)($p['cpu'] ?? 50.0), 'mem' => (float)($p['mem'] ?? 1.0),
        'vsz' => (int)($p['vsz'] ?? 20000), 'rss' => (int)($p['rss'] ?? 8000), 'tty' => (string)($p['tty'] ?? '?'), 'stat' => (string)($p['stat'] ?? 'S'),
        'start' => $w->now - (int)($p['start_offset'] ?? 600), 'service' => $p['service'] ?? null,
        'ignore_term' => $ignore === 'random' ? $r->int(0, 1) === 1 : (bool)$ignore,
    ];
    $w->facts[(string)($p['fact'] ?? 'pid')] = (string)$pid;
});

lab58_register_check('core_process_gone', static function (Lab57World $w, array $p): bool {
    $pid = (int)lab58_fill($w, (string)($p['pid'] ?? '0'));
    return $pid > 0 && !isset($w->procs[$pid]);
});

lab58_register_check('core_disk_below', static function (Lab57World $w, array $p): bool {
    if (!function_exists('lab57_disk_usage')) return false;
    $d = lab57_disk_usage($w);
    return $d['size'] > 0 && $d['used'] / $d['size'] < (float)($p['ratio'] ?? 0.9);
});

lab58_register_check('core_nginx_ok', static fn(Lab57World $w, array $p): bool => function_exists('lab57_nginx_check') && lab57_nginx_check($w) === []);

// ---------------------------------------------------------------------------
// Proměnné prostředí
// ---------------------------------------------------------------------------

// core_env_var: nastaví proměnnou prostředí žáka (viditelná v env/echo $PROMENNA). params: name, value
lab58_register_generator('core_env_var', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $name = (string)($p['name'] ?? '');
    if ($name === '' || preg_match('/^[A-Z_][A-Z0-9_]*$/', $name) !== 1) return;
    $w->env[$name] = lab58_fill($w, (string)($p['value'] ?? ''), $r);
});

// ---------------------------------------------------------------------------
// Síť: přečíslování LAN, poruchy a diagnostika (balíček „sit“)
// ---------------------------------------------------------------------------

// core_net_lan: přečísluje 10.0.0.0/24 na 10.X.0.0/24 a nastaví eth0 na náhodného hosta.
// Fakta: eth0_ip (adresa počítače), gateway_ip (adresa routeru, host .1). params: x_min/x_max, host_min/host_max
lab58_register_generator('core_net_lan', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    lab57_net_renumber($w, $r->int((int)($p['x_min'] ?? 1), (int)($p['x_max'] ?? 250)));
    $host = $r->int((int)($p['host_min'] ?? 20), (int)($p['host_max'] ?? 240));
    $ip = lab57_lan($w, $host);
    $w->net['ifaces']['eth0']['ip'] = $ip;
    foreach ($w->net['routes'] as $i => $route) if (isset($route['src'])) $w->net['routes'][$i]['src'] = $ip;
    $w->net['nodes']['pc']['ip'] = $ip;
    $w->facts['eth0_ip'] = $ip;
    $w->facts['gateway_ip'] = lab57_lan($w, 1);
});

// core_net_extra_hops: přidá 0..N dalších routerů mezi školu a páteř. params: min, max, fact (počet skoků traceroute vč. cíle)
lab58_register_generator('core_net_extra_hops', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $extra = $r->int((int)($p['min'] ?? 0), (int)($p['max'] ?? 3));
    lab57_net_extra_hops($w, $extra);
    if (isset($p['fact'])) $w->facts[(string)$p['fact']] = (string)(count((array)$w->net['wan_path']) + 1);
});

// core_net_iface_down: vypne rozhraní (ip link set … down). params: iface (výchozí eth0)
lab58_register_generator('core_net_iface_down', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $w->net['ifaces'][(string)($p['iface'] ?? 'eth0')]['up'] = false;
});

// core_net_remove_default_route: smaže výchozí trasu ze směrovací tabulky.
lab58_register_generator('core_net_remove_default_route', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $w->net['routes'] = array_values(array_filter((array)($w->net['routes'] ?? []), static fn(array $route): bool => ($route['dst'] ?? null) !== 'default'));
});

// core_net_bad_dns: nastaví /etc/resolv.conf na špatný (LAN) DNS server. params: bad_host, age (s)
lab58_register_generator('core_net_bad_dns', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $bad = lab57_lan($w, (int)($p['bad_host'] ?? 99));
    $w->mkfile('/etc/resolv.conf', "# Vygeneroval DHCP klient\nnameserver " . $bad . "\nsearch skola.test\n", 0644, 'root', 'root', $w->now - (int)($p['age'] ?? 600));
});

// core_net_hosts_mismatch: /etc/hosts ukazuje na starý (wrong) host místo správného (right).
// Fakta: hosts_right_ip, hosts_wrong_ip, hosts_wrong_ip_esc (tečky escapované pro sed).
// params: name (jméno v hosts, výchozí intranet), right_host, wrong_host, age_days
lab58_register_generator('core_net_hosts_mismatch', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $name = (string)($p['name'] ?? 'intranet');
    $right = lab57_lan($w, (int)($p['right_host'] ?? 10));
    $wrong = lab57_lan($w, (int)($p['wrong_host'] ?? 11));
    $hosts = (string)($w->fs->get('/etc/hosts')['c'] ?? '');
    $w->mkfile('/etc/hosts', str_replace($right . "\t" . $name, $wrong . "\t" . $name, $hosts), 0644, 'root', 'root', $w->now - 86400 * (int)($p['age_days'] ?? 40));
    $w->facts['hosts_right_ip'] = $right;
    $w->facts['hosts_wrong_ip'] = $wrong;
    $w->facts['hosts_wrong_ip_esc'] = str_replace('.', '\\.', $wrong);
});

// core_net_public_ip: fiktivní „veřejná IP“ služba ifconfig.me. Fakt: <fact> (výchozí public_ip). params: prefix, min, max, fact
lab58_register_generator('core_net_public_ip', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $public = (string)($p['prefix'] ?? '198.51.100.') . $r->int((int)($p['min'] ?? 20), (int)($p['max'] ?? 240));
    $w->facts[(string)($p['fact'] ?? 'public_ip')] = $public;
    $w->net['nodes']['myip'] = ['ip' => '203.0.113.99', 'name' => 'ifconfig.me', 'kind' => 'server', 'label' => 'ifconfig.me', 'zone' => 'inet', 'ttl' => 64, 'lat' => 21.0, 'ports' => [80 => 'http', 443 => 'https'], 'hidden' => true, 'x' => 600, 'y' => 20];
    $w->net['dns']['ifconfig.me'] = ['A' => ['203.0.113.99']];
    $w->net['http']['203.0.113.99:80'] = ['/' => [200, 'text/plain', $public . "\n"]];
    $w->net['http']['203.0.113.99:443'] = ['/' => [200, 'text/plain', $public . "\n"]];
});

// core_net_wan_fault: cesta k www.example.com se přeruší za náhodným routerem (traceroute → hvězdičky).
// Fakt: <fact> (výchozí break_node) = IP posledního routeru, který ještě odpovídá.
lab58_register_generator('core_net_wan_fault', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $path = (array)($w->net['wan_path'] ?? []);
    $candidates = array_slice($path, (int)($p['skip_start'] ?? 1), (int)($p['skip_end'] ?? -1));
    if ($candidates === []) $candidates = $path !== [] ? $path : ['isp'];
    $node = (string)$r->pick($candidates);
    $w->net['faults']['wan_break_after'] = $node;
    $w->facts[(string)($p['fact'] ?? 'break_node')] = (string)($w->net['nodes'][$node]['ip'] ?? '');
});

lab58_register_check('core_net_iface_up', static fn(Lab57World $w, array $p): bool => !empty($w->net['ifaces'][(string)($p['iface'] ?? 'eth0')]['up']));
lab58_register_check('core_net_has_default_route', static fn(Lab57World $w, array $p): bool => array_filter((array)($w->net['routes'] ?? []), static fn(array $route): bool => ($route['dst'] ?? null) === 'default') !== []);

lab58_register_check('core_net_dns_has', static function (Lab57World $w, array $p): bool {
    return function_exists('lab57_net_nameservers') && in_array(lab58_fill($w, (string)($p['ip'] ?? '')), lab57_net_nameservers($w), true);
});

lab58_register_check('core_net_resolves', static function (Lab57World $w, array $p): bool {
    $ip = function_exists('lab57_net_resolve') ? (lab57_net_resolve($w, lab58_fill($w, (string)($p['name'] ?? '')))['ip'] ?? null) : null;
    if ($ip === null) return false;
    return !isset($p['to']) || $ip === lab58_fill($w, (string)$p['to']);
});

lab58_register_check('core_http_status', static function (Lab57World $w, array $p): bool {
    return function_exists('lab57_http_status') && lab57_http_status($w, lab58_fill($w, (string)($p['host'] ?? 'localhost')), (int)($p['port'] ?? 80), (string)($p['path'] ?? '/')) === (int)($p['equals'] ?? 200);
});

lab58_register_check('core_net_reachable', static function (Lab57World $w, array $p): bool {
    $host = lab58_fill($w, (string)($p['host'] ?? ''));
    if ($host === '') return false;
    if (filter_var($host, FILTER_VALIDATE_IP) !== false) return function_exists('lab57_net_path') && lab57_net_path($w, $host)['ok'];
    return function_exists('lab57_can_reach') && lab57_can_reach($w, $host);
});

// ---------------------------------------------------------------------------
// Pojmenované generátory v57_<id> – jen pro úrovně, kde je obecný katalog zbytečně složitý
// (vlastní uživatelé/skupiny, síťová služba s live handlerem, žurnál konkrétní služby).
// Zdůvodnění je vždy u volajícího 'generate' v linux_v57_levels*.php.
// ---------------------------------------------------------------------------

// v57_quest_6 (Někde na serveru): vlastní uživatel/skupiny + rozházené decoy soubory různých
// vlastníků/velikostí + jedna nedostupná složka jen pro atmosféru. params: spots (kandidátní
// složky), size (bajtů, výchozí 33)
lab58_register_generator('v57_quest_6', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $w->users['archivar'] = ['uid' => 1007, 'gid' => 1007, 'home' => '/home/archivar', 'shell' => '/bin/bash', 'groups' => ['archivar'], 'gecos' => 'Archivar,,,'];
    $w->groups['archivar'] = 1007;
    $w->groups['tym3'] = 1013;
    $w->groups['tym4'] = 1014;
    if (function_exists('lab57_world_write_accounts')) lab57_world_write_accounts($w);
    $size = (int)($p['size'] ?? 33);
    $spot = (string)$r->pick((array)($p['spots'] ?? ['/var/lib/zaznamy/2025', '/usr/share/doc/stare', '/srv/archiv/sklep', '/opt/data/kopie']));
    $content = str_pad('Kód: ' . $w->code() . "\n", $size, '.', STR_PAD_LEFT);
    $w->mkfile($spot . '/zaznam.dat', $content, 0644, 'archivar', 'tym3', $w->now - 86400 * 9);
    $w->mkfile($spot . '/zaznam.bak', str_pad('stara kopie', $size, '.'), 0640, 'archivar', 'tym4', $w->now - 86400 * 20);
    $w->mkfile('/var/lib/zaznamy/klam.dat', str_pad('tady ne', 40, '.'), 0640, 'archivar', 'tym3', $w->now - 86400 * 2);
    $w->fs->set('/var/lib/zaznamy/tajne', ['t' => 'd', 'm' => 0700, 'u' => 'root', 'g' => 'root', 'mt' => $w->now - 86400]);
    $w->mkdirp('/home/archivar', 0700, 'archivar', 'archivar');
    $w->facts['zaznam_path'] = $spot . '/zaznam.dat';
});

// v57_quest_11 (Služba na tajném portu): vybere náhodný port a založí naslouchající proces;
// samotný živý handler zůstává v poli 'net' úrovně (viz komentář tam). params: min, max
lab58_register_generator('v57_quest_11', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $port = $r->int((int)($p['min'] ?? 30000), (int)($p['max'] ?? 30010));
    $w->mem['q11'] = $port;
    $w->facts['service_port'] = (string)$port;
    $w->mem['listen'] = [$port => ['proc' => 'kodovac', 'user' => 'student', 'pid' => 2750, 'addr' => '127.0.0.1']];
    $w->procs[2750] = ['user' => 'student', 'cmd' => '/usr/local/bin/kodovac --port ' . $port, 'cpu' => 0.1, 'mem' => 0.2, 'vsz' => 9100, 'rss' => 3100, 'tty' => '?', 'stat' => 'S', 'start' => $w->now - 900, 'service' => null];
});

// v57_opr_1 (Web server stojí): rozbije náhodně jednu ze tří direktiv v konfiguraci nginx a
// zapíše odpovídající stav služby + žurnál. Fakt: broken_directive (bez středníku, pro sed).
lab58_register_generator('v57_opr_1', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    if (!function_exists('lab57_nginx_site')) return;
    $directive = (string)$r->pick((array)($p['directives'] ?? ['root /var/www/html;', 'index index.html;', 'server_name _;']));
    $site = str_replace('    ' . $directive, '    ' . rtrim($directive, ';'), lab57_nginx_site());
    $w->mkfile('/etc/nginx/sites-enabled/default', $site, 0644, 'root', 'root', $w->now - 1800);
    $w->facts['broken_directive'] = rtrim($directive, ';');
    $w->services['nginx']['active'] = false;
    $w->services['nginx']['failed'] = true;
    $w->services['nginx']['result'] = 'exit-code';
    $w->services['nginx']['since'] = $w->now - 1700;
    foreach ($w->procs as $pid => $proc) if (($proc['service'] ?? '') === 'nginx') unset($w->procs[$pid]);
    foreach (lab57_nginx_check($w) as $line) $w->journalAdd('nginx', $line, 'err');
    $w->journalAdd('systemd', 'nginx.service: Control process exited, code=exited, status=1/FAILURE', 'err');
    $w->journalAdd('systemd', 'Failed to start nginx.service - A high performance web server and a reverse proxy server.', 'err');
});

// v57_opr_2 (Plný disk): náhodně pojmenovaný přerostlý log (simulovaná velikost přes 'extra'),
// stav služby zapisovac a žurnál. Fakt: big_log_path. 'programs' (validate:disk) zůstává u úrovně.
lab58_register_generator('v57_opr_2', static function (Lab57World $w, Lab57Rng $r, array $p): void {
    $name = (string)$r->pick((array)($p['names'] ?? ['debug.log', 'trace.log', 'zapisovac-verbose.log']));
    $path = '/var/log/zapisovac/' . $name;
    $w->facts['big_log_path'] = $path;
    $w->mkfile($path, "[DEBUG] start\n[DEBUG] tick 1\n[DEBUG] tick 2\n", 0640, 'root', 'adm', $w->now - 60, ['s' => 14680064000]);
    $w->mkfile('/var/log/zapisovac/zapisovac.log', "INFO start\nERROR write failed: No space left on device\n", 0640, 'root', 'adm', $w->now - 120);
    $w->mkfile('/etc/zapisovac.conf', "log_level=debug\ndata=/srv/data\n", 0644, 'root', 'root', $w->now - 86400);
    $w->services['zapisovac'] = ['desc' => 'Zapisovac - ukladani dochazky', 'active' => false, 'enabled' => true, 'failed' => true, 'result' => 'exit-code', 'since' => $w->now - 120, 'ports' => [], 'validate' => 'disk', 'procs' => [['root', '/usr/local/bin/zapisovac --config /etc/zapisovac.conf']]];
    $w->journalAdd('zapisovac', 'write failed: No space left on device', 'err');
    $w->journalAdd('systemd', 'zapisovac.service: Main process exited, code=exited, status=1/FAILURE', 'err');
});
