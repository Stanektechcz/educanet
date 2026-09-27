<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úrovní „Archivy" (LAB-03).
 *
 * Rozpoznání formátu přes file, výpis obsahu bez rozbalení, rozbalení tar/gzip/zip/bzip2,
 * vnořené archivy (více vrstev jako Bandit 12) a zabalení výsledku. Archivy jsou binární
 * data ve VFS s pravými magickými bajty; vše deterministické ze semínka.
 * Vhodné i pro 1.A/2.A – archivy potká každý; balíček je proto dostupný všem třídám.
 */

/** Zabalí obsah do vrstvy daného formátu (deterministicky). */
function lab58_arch_wrap(string $data, string $fmt, string $innerName = 'data'): string
{
    return match ($fmt) {
        'gzip' => lab58_arc_gzip_pack($data),
        'bzip2' => lab58_arc_bzip2_pack($data),
        'tar' => lab58_arc_tar_pack([['name' => $innerName, 'mode' => 0644, 'size' => strlen($data), 'mtime' => 1700000000, 'type' => '0', 'uname' => 'student', 'gname' => 'student', 'content' => $data]]),
        'zip' => lab58_arc_zip_pack([['name' => $innerName, 'mtime' => 1700000000, 'content' => $data, 'dir' => false]]),
        default => $data,
    };
}

/** Přípona pro danou vrstvu (pro pojmenování vnořených souborů). */
function lab58_arch_ext(string $fmt): string
{
    return match ($fmt) { 'gzip' => '.gz', 'bzip2' => '.bz2', 'tar' => '.tar', 'zip' => '.zip', default => '.bin' };
}

function lab58_levels_archives(): array
{
    return [
        [
            'id' => 'archivy-1', 'type' => 'code', 'title' => 'Co je to za soubor?', 'difficulty' => 1, 'points' => 90, 'minutes' => 5,
            'story' => 'V domovské složce máš soubor zasilka bez přípony. Vypadá jako nesmysl, ale je to komprimovaný archiv – jen nevíš jaký.',
            'task' => 'Zjisti formát příkazem file, rozbal ho správným nástrojem a odevzdej kód z rozbaleného souboru.',
            'commands' => ['file', 'gunzip', 'cat', 'mv', 'submit'],
            'hints' => ['file zasilka ti řekne, čím je soubor zabalený.', 'gzip poznáš podle „gzip compressed data". Rozbalíš ho: gunzip potřebuje příponu .gz, takže soubor nejdřív přejmenuj: mv zasilka zasilka.gz.', 'gunzip zasilka.gz a pak cat zasilka.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $w->mkfile('/home/student/zasilka', lab58_arc_gzip_pack('Kód: ' . $w->code() . "\n"), 0644, 'student', 'student', $w->now - 3600);
            },
            'solution' => static fn(Lab57World $w): array => ['file zasilka', 'mv zasilka zasilka.gz', 'gunzip zasilka.gz', 'cat zasilka', 'submit ' . $w->code()],
            'learn' => 'Přípona v Linuxu nic negarantuje – skutečný typ pozná file podle „magických bajtů" na začátku souboru. gzip = \x1f\x8b, zip = PK, tar = ustar.',
        ],
        [
            'id' => 'archivy-2', 'type' => 'code', 'title' => 'Nahlédni dovnitř', 'difficulty' => 1, 'points' => 110, 'minutes' => 5,
            'story' => 'Dostal(a) jsi archiv projekt.tar.gz. Nechceš ho rozbalovat naslepo – nejdřív se podívej, co je uvnitř, a pak vytáhni jen ten správný soubor.',
            'task' => 'Vypiš obsah archivu bez rozbalení, pak ho rozbal a přečti kód z kod.txt.',
            'commands' => ['tar', 'cat', 'submit'],
            'hints' => ['Obsah tar.gz vypíšeš bez rozbalení: tar -tzf projekt.tar.gz.', 'Rozbalíš ho: tar -xzf projekt.tar.gz.', 'Kód je v souboru kod.txt uvnitř archivu.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $members = [
                    ['name' => 'projekt/', 'mode' => 0755, 'size' => 0, 'mtime' => $w->now - 7200, 'type' => '5', 'uname' => 'student', 'gname' => 'student', 'content' => ''],
                    ['name' => 'projekt/README.txt', 'mode' => 0644, 'size' => 0, 'mtime' => $w->now - 7200, 'type' => '0', 'uname' => 'student', 'gname' => 'student', 'content' => "Projekt webu.\n"],
                    ['name' => 'projekt/kod.txt', 'mode' => 0644, 'size' => 0, 'mtime' => $w->now - 7200, 'type' => '0', 'uname' => 'student', 'gname' => 'student', 'content' => $w->code() . "\n"],
                ];
                $w->mkfile('/home/student/projekt.tar.gz', lab58_arc_gzip_pack(lab58_arc_tar_pack($members)), 0644, 'student', 'student', $w->now - 3600);
            },
            'solution' => static fn(Lab57World $w): array => ['tar -tzf projekt.tar.gz', 'tar -xzf projekt.tar.gz', 'cat projekt/kod.txt', 'submit ' . $w->code()],
            'learn' => 'tar -t vypíše obsah, -x rozbalí, -z znamená gzip a -f určuje soubor. Před rozbalením se vždy vyplatí obsah zkontrolovat (tar -tf).',
        ],
        [
            'id' => 'archivy-3', 'type' => 'code', 'title' => 'ZIP z jiného světa', 'difficulty' => 2, 'points' => 130, 'minutes' => 6,
            'story' => 'Spolužák ti poslal data.zip – archiv, jaký znáš z Windows. Potřebuješ z něj jeden soubor.',
            'task' => 'Vypiš obsah ZIP archivu, rozbal ho a odevzdej kód z kod.txt.',
            'commands' => ['unzip', 'file', 'cat', 'submit'],
            'hints' => ['Obsah vypíšeš bez rozbalení: unzip -l data.zip.', 'Rozbalíš: unzip data.zip.', 'Pak cat kod.txt a submit.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $members = [
                    ['name' => 'poznamky.txt', 'mtime' => $w->now - 7200, 'content' => "Nic zajímavého.\n", 'dir' => false],
                    ['name' => 'kod.txt', 'mtime' => $w->now - 7200, 'content' => $w->code() . "\n", 'dir' => false],
                ];
                $w->mkfile('/home/student/data.zip', lab58_arc_zip_pack($members), 0644, 'student', 'student', $w->now - 3600);
            },
            'solution' => static fn(Lab57World $w): array => ['unzip -l data.zip', 'unzip data.zip', 'cat kod.txt', 'submit ' . $w->code()],
            'learn' => 'ZIP je nejběžnější archiv ve Windows. unzip -l vypíše obsah, unzip rozbalí. Na rozdíl od tar+gzip archivuje i komprimuje jedním krokem.',
        ],
        [
            'id' => 'archivy-4', 'type' => 'code', 'title' => 'Cibule z archivů', 'difficulty' => 3, 'points' => 200, 'minutes' => 12,
            'story' => 'Klasická hádanka: soubor balik je zabalený několikrát za sebou v různých formátech (gzip, bzip2, tar, zip). Musíš ho rozbalovat vrstvu po vrstvě – a pokaždé nejdřív zjistit, čím je zabalený.',
            'task' => 'Rozbaluj vrstvy, dokud se neobjeví text s kódem. Po každé vrstvě použij file, ať víš, co dál. Odevzdej kód.',
            'commands' => ['file', 'gunzip', 'bunzip2', 'tar', 'unzip', 'xxd', 'mv', 'cat', 'submit'],
            'hints' => ['Po každém rozbalení spusť file na nový soubor – řekne ti další formát.', 'gzip → přejmenuj na .gz a gunzip; bzip2 → .bz2 a bunzip2; tar → tar -xf; zip → unzip.', 'Až file ohlásí „ASCII text", jsi na konci: cat soubor.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $data = 'Prokousal(a) ses všemi vrstvami! Kód: ' . $w->code() . "\n";
                $layers = $r->shuffle(['gzip', 'bzip2', 'tar', 'zip', 'gzip']);
                $name = 'jadro';
                foreach ($layers as $fmt) {
                    $data = lab58_arch_wrap($data, $fmt, $name);
                    $name = 'vrstva' . lab58_arch_ext($fmt);
                }
                $w->mkfile('/home/student/balik', $data, 0644, 'student', 'student', $w->now - 3600);
                $w->mem['arch4_layers'] = $layers;
            },
            'solution' => static function (Lab57World $w): array {
                $layers = array_values((array)($w->mem['arch4_layers'] ?? []));
                $cmds = [];
                $file = 'balik';
                $step = 0;
                // Rozbaluje se od vnější vrstvy; tar/zip vrstva i obsahuje soubor pojmenovaný podle vrstvy i-1 (nejvnitřnější „jadro").
                for ($i = count($layers) - 1; $i >= 0; $i--) {
                    $fmt = (string)$layers[$i];
                    $step++;
                    $cmds[] = 'file ' . $file;
                    $out = 'out' . $step;
                    $member = $i === 0 ? 'jadro' : 'vrstva' . lab58_arch_ext((string)$layers[$i - 1]);
                    if ($fmt === 'gzip') { $cmds[] = 'mv ' . $file . ' ' . $out . '.gz'; $cmds[] = 'gunzip ' . $out . '.gz'; $file = $out; }
                    elseif ($fmt === 'bzip2') { $cmds[] = 'mv ' . $file . ' ' . $out . '.bz2'; $cmds[] = 'bunzip2 ' . $out . '.bz2'; $file = $out; }
                    elseif ($fmt === 'tar') { $cmds[] = 'mkdir -p ' . $out; $cmds[] = 'tar -xf ' . $file . ' -C ' . $out; $file = $out . '/' . $member; }
                    elseif ($fmt === 'zip') { $cmds[] = 'mkdir -p ' . $out; $cmds[] = 'unzip -o ' . $file . ' -d ' . $out; $file = $out . '/' . $member; }
                }
                $cmds[] = 'cat ' . $file;
                $cmds[] = 'submit ' . $w->code();
                return $cmds;
            },
            'learn' => 'Vnořené archivy rozbaluješ odzadu: pokaždé zjistíš formát (file) a použiješ odpovídající nástroj. Přípona nic neznamená – rozhoduje obsah.',
        ],
        [
            'id' => 'archivy-5', 'type' => 'check', 'title' => 'Zabal zálohu', 'difficulty' => 2, 'points' => 140, 'minutes' => 7,
            'story' => 'Web v ~/web je hotový a je čas ho zazálohovat. Správce chce jeden komprimovaný archiv, který se dá snadno přenést.',
            'task' => 'Vytvoř komprimovaný archiv ~/zaloha.tar.gz obsahující složku web.',
            'commands' => ['tar', 'ls', 'file'],
            'hints' => ['Komprimovaný tar vytvoříš volbami -c (create), -z (gzip) a -f (soubor).', 'tar -czf ~/zaloha.tar.gz web', 'Výsledek ověříš: file ~/zaloha.tar.gz a tar -tzf ~/zaloha.tar.gz.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $w->mkfile('/home/student/web/index.html', "<!doctype html>\n<h1>Hotovo</h1>\n", 0644, 'student', 'student', $w->now - 7200);
                $w->mkfile('/home/student/web/styl.css', "body{font-family:sans-serif}\n", 0644, 'student', 'student', $w->now - 7200);
            },
            'checks' => [
                ['label' => '~/zaloha.tar.gz existuje a je to gzip', 'fn' => static function (Lab57World $w): bool {
                    $c = (string)($w->fs->get('/home/student/zaloha.tar.gz')['c'] ?? '');
                    return str_starts_with($c, "\x1f\x8b");
                }],
                ['label' => 'Archiv obsahuje složku web s index.html', 'fn' => static function (Lab57World $w): bool {
                    $c = (string)($w->fs->get('/home/student/zaloha.tar.gz')['c'] ?? '');
                    $u = lab58_arc_gzip_unpack($c);
                    if ($u === null) return false;
                    foreach (lab58_arc_tar_unpack($u['data']) as $m) if (str_contains($m['name'], 'web/index.html')) return true;
                    return false;
                }],
            ],
            'solution' => static fn(Lab57World $w): array => ['cd ~', 'tar -czf zaloha.tar.gz web', 'file zaloha.tar.gz'],
            'learn' => 'Zálohu složky uděláš jedním příkazem: tar -czf archiv.tar.gz slozka. Písmena znamenají create, gzip, file.',
        ],
        [
            'id' => 'archivy-6', 'type' => 'code', 'title' => 'Hex, který je archivem', 'difficulty' => 3, 'points' => 200, 'minutes' => 10,
            'story' => 'V souboru sklad.hex je archiv zapsaný jako hexadecimální výpis (jako z xxd). Musíš z hexu složit binární archiv a ten pak rozbalit.',
            'task' => 'Převeď hex zpět na binární soubor, zjisti jeho formát a rozbal ho. Odevzdej kód.',
            'commands' => ['xxd', 'file', 'gunzip', 'tar', 'cat', 'submit'],
            'hints' => ['xxd -r -p sklad.hex převede „holý" hex zpět na bajty. Výsledek přesměruj do souboru: xxd -r -p sklad.hex > sklad.bin.', 'file sklad.bin ti řekne formát (gzip).', 'Přejmenuj na .gz, gunzip a cat.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $archive = lab58_arc_gzip_pack('Skvělé! Kód: ' . $w->code() . "\n");
                $w->mkfile('/home/student/sklad.hex', chunk_split(bin2hex($archive), 60, "\n"), 0644, 'student', 'student', $w->now - 3600);
            },
            'solution' => static fn(Lab57World $w): array => ['xxd -r -p sklad.hex > sklad.bin', 'file sklad.bin', 'mv sklad.bin sklad.gz', 'gunzip sklad.gz', 'cat sklad', 'submit ' . $w->code()],
            'learn' => 'xxd umí oba směry: bez voleb ukáže hex, s -r ho složí zpět na bajty (-p pro „holý" hex bez adres). Tak se dají přenášet binární data jako text.',
        ],
    ];
}

lab58_register_pack(
    [
        'id' => 'archivy', 'title' => 'Archivy', 'description' => 'Rozpoznávání formátů, balení a rozbalování tar, gzip, zip a bzip2.',
        'order' => 64, 'classes' => null, 'unlock' => 'sequential',
        'badge' => ['id' => 'archivar', 'label' => 'Archivář', 'icon' => '🗜'], 'icon' => '🗜', 'tone' => 'amber', 'inspired' => 've stylu OverTheWire Bandit 12',
    ],
    'lab58_levels_archives'
);
