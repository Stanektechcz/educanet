<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček „Git v terminálu“ (LAB-06): tým spravuje repozitář školního webu.
 * Historie se staví deterministicky ze semínka (tým, časy, texty) od pevného data 2026-03-02 – id commitů tak
 * nezávisí na čase požadavku (stav žáka se ukládá jako rozdíl proti výchozímu světu). Kód úrovně je běžný obsah
 * projektu (poznámky k vydání, poznámka ve větvi, odložená stránka). Obsahuje i položku příručky pro příkaz git
 * (příkazové soubory linux_v58_cmd_git*.php jsou na hranici velikosti).
 */

const LAB58_GITLV_EPOCH = 1772409600; // 2026-03-02 00:00 UTC

function lab58_gitlv_ascii(string $s): string
{
    return strtr(mb_strtolower($s), ['á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z']);
}

/** Čtyřčlenný tým z vymyšlených jmen (2 + 2), deterministicky ze semínka. */
function lab58_gitlv_team(Lab57Rng $rng): array
{
    $women = ['Jana Nováková', 'Eliška Dvořáková', 'Tereza Černá', 'Klára Veselá', 'Lucie Marková', 'Anna Pokorná', 'Kateřina Benešová', 'Veronika Horáková'];
    $men = ['Petr Svoboda', 'Tomáš Král', 'Matěj Procházka', 'Jakub Kučera', 'Ondřej Novotný', 'Adam Růžička', 'Filip Zeman', 'Vojtěch Fiala'];
    $names = $rng->shuffle(array_merge(array_slice($rng->shuffle($women), 0, 2), array_slice($rng->shuffle($men), 0, 2)));
    return array_map(static fn(string $n): array => ['name' => $n, 'email' => str_replace(' ', '.', lab58_gitlv_ascii($n)) . '@skola.test'], $names);
}

function lab58_gitlv_page(string $title, string $body, string $css = 'css/styl.css'): string
{
    return "<!doctype html>\n<html lang=\"cs\">\n<head>\n  <meta charset=\"utf-8\">\n  <title>" . $title . "</title>\n  <link rel=\"stylesheet\" href=\"" . $css . "\">\n</head>\n<body>\n" . $body . "</body>\n</html>\n";
}

function lab58_gitlv_index(string $h1, array $links, string $intro): string
{
    $nav = '';
    foreach ($links as $href => $label) $nav .= '    <a href="' . $href . '">' . $label . "</a>\n";
    return lab58_gitlv_page('Škola Educanet', '  <h1>' . $h1 . "</h1>\n  <nav>\n" . $nav . "  </nav>\n  <p>" . $intro . "</p>\n");
}

function lab58_gitlv_css(string $bg, string $fg, string $h1, string $extra = ''): string
{
    return "body {\n  font-family: sans-serif;\n  margin: 2rem;\n  background: " . $bg . ";\n  color: " . $fg . ";\n}\n\nh1 {\n  color: " . $h1 . ";\n}\n\nnav a {\n  margin-right: 1rem;\n}\n" . $extra;
}

/** Rozvrh: $v = 1 (první verze) nebo 2 (upravená – ta se v úrovni obnovuje). */
function lab58_gitlv_rozvrh(array $days, int $v): string
{
    $rows = '';
    foreach ($days as $i => $d) $rows .= '    <tr><td>' . $d[0] . '</td><td>' . ($v === 2 && $i === 1 ? $d[2] : $d[1]) . "</td></tr>\n";
    return lab58_gitlv_page('Rozvrh hodin', "  <h1>Rozvrh hodin</h1>\n  <table>\n" . $rows . "  </table>\n" . ($v === 2 ? "  <p>Aktualizováno po poradě třídních učitelů.</p>\n" : ''));
}

/**
 * Generátor git_web_repo: /srv/git/web.git (holý repozitář, historie + větve + značky) a volitelně klon ~/web.
 * params: clone (bool), code: '' | tag | stash | design (kam patří kód úrovně).
 */
function lab58_gitlv_generate(Lab57World $w, Lab57Rng $rng, array $p): void
{
    [$team, $code, $decoy] = [lab58_gitlv_team($rng), (string)($p['code'] ?? ''), lab57_decoy_code($rng, $w->code())];
    [$x, $h1, $intro] = [$rng->int(0, 3), $rng->shuffle(['Vítejte na webu naší školy', 'Škola Educanet – učíme se prakticky', 'Educanet: škola, která tě baví', 'Vítejte ve škole Educanet', 'Web školy Educanet']), $rng->shuffle(['Najdete tu rozvrh, kontakty a novinky ze školy.', 'Aktuality, rozvrh hodin a kontakty na jednom místě.', 'Vše důležité o naší škole na jednom místě.'])];
    $y = ($x + $rng->int(1, 3)) % 4;
    $names = $rng->shuffle([['Kaštan', 'kastan'], ['Lípa', 'lipa'], ['Javor', 'javor'], ['Jeřáb', 'jerab'], ['Buk', 'buk'], ['Habr', 'habr'], ['Jasan', 'jasan'], ['Modřín', 'modrin']]);
    [$typo, $design, $note] = [(string)$rng->pick(['kontakty.html', 'kontak.html', 'Kontakt.html', 'kontakt.htm']), (string)$rng->pick(['novy-design', 'redesign', 'design-v2', 'tmavy-design']), (string)$rng->pick(['POZNAMKA.md', 'poznamka-k-designu.txt', 'DESIGN.md'])];
    $days = $rng->shuffle([['Pondělí', '8:00–13:30', '8:00–14:20'], ['Úterý', '8:00–14:20', '8:55–15:10'], ['Středa', '8:00–12:35', '8:00–13:30'], ['Čtvrtek', '8:55–14:20', '8:00–14:20']]);
    $r = ['gd' => '/srv/git/web.git', 'root' => null];
    $w->root = true;
    lab58_git_write($w, '/srv/git/web.git/HEAD', "ref: refs/heads/main\n");
    lab58_git_cfg_write($w, '/srv/git/web.git/config', [['core.repositoryformatversion', '0'], ['core.filemode', 'true'], ['core.bare', 'true']]);
    lab58_git_mkdir($w, '/srv/git/web.git/refs/tags');
    $s = ['files' => [], 'head' => null];
    $commit = static function (int $who, int $day, string $msg, array $changes) use ($w, $r, $rng, $team, &$s): string {
        foreach ($changes as $path => $content) { if ($content === null) unset($s['files'][$path]); else $s['files'][$path] = lab58_git_blob_put($w, $r, $content); }
        $person = $team[$who] + ['time' => LAB58_GITLV_EPOCH + $day * 86400 + $rng->int(7, 15) * 3600 + $rng->int(0, 3599)];
        return $s['head'] = lab58_git_commit_put($w, $r, $s['files'], $s['head'] === null ? [] : [$s['head']], $person, $person, $msg);
    };
    $tag = static function (string $name, int $who, int $day, string $msg) use ($w, $r, $team, &$s): void {
        lab58_git_ref_set($w, $r, 'refs/tags/' . $name, lab58_git_put($w, $r, ['type' => 'tag', 'object' => $s['head'], 'tag' => $name, 'tagger' => $team[$who] + ['time' => LAB58_GITLV_EPOCH + $day * 86400 + 16 * 3600], 'message' => $msg]));
    };
    $nav = ['index.html' => 'Úvod', 'kontakt.html' => 'Kontakt', 'rozvrh.html' => 'Rozvrh'];
    $kontakt = static fn(string $extra): string => lab58_gitlv_page('Kontakt', "  <h1>Kontakt</h1>\n  <p>Škola Educanet, Školní 12, Praha</p>\n  <p>E-mail: info@skola.test</p>\n" . $extra);
    $readme = static fn(string $typo2): string => "# Školní web Educanet\n\nStatický web školy. Každou změnu ulož malým commitem s " . $typo2 . " zprávou.\n\nTým: " . implode(', ', array_column($team, 'name')) . "\n";
    $notes = static fn(string $ver, array $cn, string $items, string $line): string => 'Vydání ' . $ver . ' „' . $cn[0] . "“\n\n" . $items . "\n" . $line . "\n";
    $commit(0, 0, 'První verze školního webu', ['index.html' => lab58_gitlv_index($h1[0], array_slice($nav, 0, 1), $intro[0]), 'css/styl.css' => lab58_gitlv_css('#ffffff', '#1f2937', '#1d4ed8'), 'README.md' => $readme('výstižnou')]);
    $commit(1, 1, 'Stránka s kontakty', ['kontakt.html' => $kontakt(''), 'index.html' => lab58_gitlv_index($h1[0], array_slice($nav, 0, 2), $intro[0])]);
    $commit(2, 3, 'Rozvrh hodin', ['rozvrh.html' => lab58_gitlv_rozvrh($days, 1), 'index.html' => lab58_gitlv_index($h1[0], $nav, $intro[0])]);
    $commit($x, 5, 'Nový nadpis úvodní stránky', ['index.html' => lab58_gitlv_index($h1[1], $nav, $intro[0])]);
    $commit(3, 7, 'Úprava rozvrhu', ['rozvrh.html' => lab58_gitlv_rozvrh($days, 2)]);
    $notes09 = 'docs/poznamky-' . $names[0][1] . '.txt';
    $commit(1, 9, 'Poznámky k vydání 0.9', [$notes09 => $notes('0.9', $names[0], "- stránka Kontakt\n- rozvrh hodin", 'Kontrolní kód vydání: ' . $decoy)]);
    $tag('v0.9', 1, 9, 'Vydání 0.9 „' . $names[0][0] . "“ – zkušební verze webu.\n\nPoznámky k vydání: " . $notes09);
    $commit($y, 12, 'Drobné úpravy úvodní stránky', ['index.html' => lab58_gitlv_index($h1[2], $nav, $intro[1])]);
    $broken = $commit($rng->int(0, 3), 14, (string)$rng->pick(['Sjednocení menu', 'Úprava navigace', 'Menu na všech stránkách stejně']), ['index.html' => lab58_gitlv_index($h1[2], ['index.html' => 'Úvod', $typo => 'Kontakt', 'rozvrh.html' => 'Rozvrh hodin'], $intro[1])]);
    $notes10 = 'docs/poznamky-' . $names[1][1] . '.txt';
    $commit(0, 16, 'Poznámky k vydání 1.0', [$notes10 => $notes('1.0', $names[1], "- nový nadpis úvodní stránky\n- upravený rozvrh\n- jednotné menu", 'Kontrolní kód vydání: ' . ($code === 'tag' ? $w->code() : 'doplní vedení'))]);
    $tag('v1.0', 0, 16, 'Vydání 1.0 „' . $names[1][0] . "“ – první ostrá verze webu.\n\nPoznámky k vydání: " . $notes10);
    $fork = $s;
    $commit(3, 17, 'Nový design – tmavé barvy', ['css/styl.css' => lab58_gitlv_css('#111827', '#f9fafb', '#fbbf24', "\na {\n  color: #93c5fd;\n}\n")]);
    $commit(3, 18, 'Poznámka k novému designu', [$note => "# Nový design webu\n\nTmavé pozadí, výraznější odkazy a žlutý nadpis. Zpětnou vazbu pište do pátku.\nKód pro tým: " . ($code === 'design' ? $w->code() : 'pošlu později') . "\n"]);
    lab58_git_ref_set($w, $r, 'refs/heads/' . $design, (string)$s['head']);
    $s = $fork;
    $commit(2, 17, 'Patička s adresou školy', ['kontakt.html' => $kontakt("  <footer>Škola Educanet · Školní 12</footer>\n")]);
    lab58_git_ref_set($w, $r, 'refs/heads/oprava-paticky', (string)$s['head']);
    $s = $fork;
    $cleanup = $commit(1, 19, 'Úklid starých souborů', ['rozvrh.html' => null, $notes09 => null, $notes10 => null, 'index.html' => lab58_gitlv_index($h1[2], ['index.html' => 'Úvod', $typo => 'Kontakt'], $intro[1])]);
    $commit(2, 21, 'Oprava překlepů v README', ['README.md' => $readme('výstižnou a krátkou')]);
    if ($rng->int(0, 1) === 1) $commit(3, 22, 'Aktualizace kontaktů', ['kontakt.html' => $kontakt("  <p>Telefon: 555 010 020</p>\n")]);
    lab58_git_ref_set($w, $r, 'refs/heads/main', (string)$s['head']);
    $w->root = false;
    $w->facts += ['h1_author' => $team[$y]['name'], 'broken' => $broken, 'del' => $cleanup, 'del_short' => substr($cleanup, 0, 7), 'rozvrh' => lab58_gitlv_rozvrh($days, 2),
        'notes10' => $notes10, 'design_branch' => $design, 'design_note' => $note, 'typo' => $typo, 'stash_ref' => 'stash@{0}'];
    if (empty($p['clone'])) return;
    $web = lab58_git_clone_into($w, $r, '/srv/git/web.git', $w->home('student') . '/web');
    lab58_git_write($w, $w->home('student') . '/.gitconfig', "[user]\n\tname = Student\n\temail = student@skola.test\n");
    if ($code === 'design') lab58_git_wt_write($w, $web, 'css/styl.css', lab58_gitlv_css('#ffffff', '#1f2937', '#b91c1c', "\n/* rozdělané: červený nadpis? */\n"));
    if ($code === 'stash') $w->facts['stash_ref'] = lab58_gitlv_stashes($w, $web, $rng, $decoy);
}

/** Dvě odložené práce v ~/web (přes skutečný git stash push): stránka o kroužcích s kódem a zkouška barev s návnadou. */
function lab58_gitlv_stashes(Lab57World $w, array $web, Lab57Rng $rng, string $decoy): string
{
    [$p, $now, $first] = [new Lab57Proc($w), $w->now, $rng->int(0, 1)];
    $git = static fn(string ...$args): int => lab58_cmd_git($p, array_merge(['git', '-C', (string)$web['root']], $args));
    foreach ($first === 1 ? ['krouzky', 'css'] : ['css', 'krouzky'] as $i => $what) {
        $w->now = LAB58_GITLV_EPOCH + (23 + $i) * 86400 + 14 * 3600;
        if ($what === 'krouzky') {
            lab58_git_wt_write($w, $web, 'krouzky.html', lab58_gitlv_page('Kroužky', "  <h1>Školní kroužky</h1>\n  <ul>\n    <li>Robotika – úterý 14:30</li>\n    <li>Fotografický kroužek – středa 15:00</li>\n    <!-- TODO: doplnit další kroužky -->\n  </ul>\n  <!-- kontrolní kód stránky: " . $w->code() . " -->\n"));
            $git('add', 'krouzky.html');
            $git('stash', 'push', '-m', 'rozdělaná stránka o kroužcích');
        } else {
            lab58_git_wt_write($w, $web, 'css/styl.css', lab58_gitlv_css('#fefce8', '#1f2937', '#15803d', "\n/* zkouška barev – kód z testu: " . $decoy . " */\n"));
            $git('stash', 'push');
        }
    }
    $w->now = $now;
    return $first === 1 ? 'stash@{1}' : 'stash@{0}';
}

/** Odpověď z posledního řádku historie: toleruje chybějící diakritiku (jména) a delší prefix hashe (7–40 znaků). */
function lab58_gitlv_answer(Lab57World $w, string $expected, bool $hash): string
{
    $line = (string)($w->history[count($w->history) - 1] ?? '');
    $given = preg_match('/(?:^|[;&|]\s*)answer\s+(.+?)\s*$/u', $line, $m) === 1 ? implode(' ', preg_split('/\s+/u', trim(str_replace(['"', "'"], '', $m[1]))) ?: []) : '';
    if ($hash) return strlen($given) >= 7 && strlen($given) <= 40 && ctype_xdigit($given) && str_starts_with($expected, strtolower($given)) ? $given : substr($expected, 0, 7);
    return $given !== '' && lab58_gitlv_ascii($given) === lab58_gitlv_ascii($expected) ? $given : $expected;
}

function lab58_gitlv_repo(Lab57World $w, array $p): ?array
{
    return lab58_git_find($w, lab58_gen_path($w, (string)($p['repo'] ?? '~/web')));
}

lab58_register_generator('git_web_repo', 'lab58_gitlv_generate');
// git_identity: git zná user.name a user.email (globálně, nebo v repozitáři). params: repo, label
lab58_register_check('git_identity', static function (Lab57World $w, array $p): bool {
    return lab58_git_ident($w, lab58_gitlv_repo($w, $p)) !== null && str_contains((string)lab58_git_cfg_get($w, lab58_gitlv_repo($w, $p), 'user.email'), '@');
});
// git_cloned: v repo je klon z url (origin) s rozbaleným pracovním stromem. params: repo, url, label
lab58_register_check('git_cloned', static function (Lab57World $w, array $p): bool {
    $r = lab58_gitlv_repo($w, $p);
    return $r !== null && $r['root'] === lab58_gen_path($w, (string)($p['repo'] ?? '~/web')) && lab58_git_head($w, $r)['id'] !== null
        && lab58_git_cfg_get($w, $r, 'remote.origin.url') === (string)($p['url'] ?? '/srv/git/web.git') && $w->fs->isFile($r['root'] . '/index.html');
});
// git_head_has: poslední commit (HEAD) obsahuje soubor s daným obsahem (šablona, např. {f:rozvrh}). params: repo, file, content, label
lab58_register_check('git_head_has', static function (Lab57World $w, array $p): bool {
    $r = lab58_gitlv_repo($w, $p);
    return $r !== null && (lab58_git_tree($w, $r, lab58_git_head($w, $r)['id'])[(string)($p['file'] ?? '')] ?? null) === lab58_git_blob_id(lab58_fill($w, (string)($p['content'] ?? '')));
});

function lab58_gitlv_levels(): array
{
    $repo = static fn(string $code = ''): array => [['git_web_repo', ['clone' => true, 'code' => $code]]];
    $cmds = ['git', 'cd', 'ls', 'cat'];
    return [
        ['id' => 'git-1', 'type' => 'check', 'title' => 'Vítej v týmu', 'difficulty' => 1, 'points' => 80, 'minutes' => 6, 'commands' => $cmds,
            'story' => 'Školní web spravuje malý tým. Repozitář s celou historií webu leží na školním serveru ve složce /srv/git/web.git. Než začneš pracovat, musíš se gitu představit a stáhnout si vlastní kopii.',
            'task' => 'Nastav gitu své jméno a e-mail (user.name, user.email) a naklonuj repozitář /srv/git/web.git do složky ~/web.',
            'hints' => ['Jméno nastavíš: git config --global user.name "Tvé Jméno", e-mail stejně přes user.email.', 'Vlastní kopii vytvoří git clone /srv/git/web.git – složka web vznikne v aktuální složce (ve ~).', 'Kontrola: git config --list a ls ~/web'],
            'generate' => [['git_web_repo', ['clone' => false]]],
            'checks' => [['git_identity', ['label' => 'git zná tvoje jméno a e-mail']], ['git_cloned', ['url' => '/srv/git/web.git', 'label' => 'repozitář je naklonovaný v ~/web']]],
            'solution' => ['git config --global user.name "Jana Testová"', 'git config --global user.email jana.testova@skola.test', 'git clone /srv/git/web.git', 'cd web', 'git log --oneline'],
            'learn' => 'git clone stáhne celou historii projektu i s větvemi a značkami. Jméno a e-mail z git config se zapisují do každého tvého commitu.'],
        ['id' => 'git-2', 'type' => 'answer', 'title' => 'Kdo změnil nadpis?', 'difficulty' => 1, 'points' => 100, 'minutes' => 6, 'commands' => $cmds, 'answer_format' => 'celé jméno autora, např. Jana Nováková',
            'story' => 'Vedení školy se ptá, kdo a kdy naposledy přepsal hlavní nadpis úvodní stránky (řádek s <h1> v souboru index.html). Repozitář máš naklonovaný v ~/web.',
            'task' => 'Zjisti, kdo jako poslední změnil řádek s nadpisem <h1> v index.html, a odpověz celým jménem: answer Jméno Příjmení',
            'hints' => ['O nadpisu mluví víc commitů – zpráva commitu může klamat. Dívej se na samotný řádek.', 'git blame index.html ukáže u každého řádku autora a datum poslední změny.', 'Jen řádek s nadpisem: git blame index.html | grep h1'],
            'generate' => $repo(), 'answer' => static fn(Lab57World $w): string => lab58_gitlv_answer($w, (string)($w->facts['h1_author'] ?? ''), false),
            'solution' => ['cd ~/web', 'git log --oneline', 'git blame index.html', 'answer {f:h1_author}'],
            'learn' => 'git blame ukáže u každého řádku commit, autora a datum poslední změny. Zpráva commitu může být nepřesná – rozhoduje obsah změny.'],
        ['id' => 'git-3', 'type' => 'answer', 'title' => 'Rozbitý odkaz', 'difficulty' => 2, 'points' => 140, 'minutes' => 8, 'commands' => $cmds, 'answer_format' => 'hash commitu (stačí prvních 7 znaků)',
            'story' => 'Návštěvníci hlásí, že na úvodní stránce nefunguje odkaz Kontakt. Stránka kontakt.html přitom v repozitáři je – chyba je v odkazu v index.html. Někdo ho při úpravách pokazil.',
            'task' => 'Najdi commit, který odkaz na kontakt.html rozbil, a odpověz jeho hashem: answer 1a2b3c4',
            'hints' => ['Jak vypadá odkaz teď? grep -n href index.html', 'git log -p index.html ukáže u každého commitu změněné řádky (- staré, + nové).', "Rychleji: git log --oneline -S 'href=\"kontakt.html\"' najde commity, které ten text přidaly nebo odebraly."],
            'generate' => $repo(), 'answer' => static fn(Lab57World $w): string => lab58_gitlv_answer($w, (string)($w->facts['broken'] ?? ''), true),
            'solution' => ['cd ~/web', 'grep -n href index.html', "git log --oneline -S 'href=\"kontakt.html\"'", 'answer {f:broken}'],
            'learn' => 'git log -p ukáže historii souboru i se změnami, git log -S text najde commity, které text přidaly nebo odebraly. Hash commitu stačí zkrácený na 7 znaků.'],
        ['id' => 'git-4', 'type' => 'check', 'title' => 'Zmizelý rozvrh', 'difficulty' => 2, 'points' => 150, 'minutes' => 10, 'commands' => $cmds,
            'story' => 'Při úklidu repozitáře někdo omylem smazal stránku rozvrh.html. Smazání už je v historii, takže soubor chybí i v posledním commitu – obyčejné git restore rozvrh.html nepomůže.',
            'task' => 'Obnov rozvrh.html v podobě, jakou měl těsně před smazáním, a obnovu ulož do historie commitem.',
            'hints' => ['Kdy soubor zmizel? git log --oneline -- rozvrh.html (u smazaného souboru nezapomeň na --).', 'Soubor z commitu těsně před smazáním vrátíš: git checkout <hash>~1 -- rozvrh.html', 'Nakonec ulož: git commit -m "Obnoven rozvrh"'],
            'generate' => $repo(),
            'checks' => [['file_contains', ['path' => '~/web/rozvrh.html', 'equals' => '{f:rozvrh}', 'label' => 'rozvrh.html je zpět v ~/web v poslední verzi']], ['git_head_has', ['file' => 'rozvrh.html', 'content' => '{f:rozvrh}', 'label' => 'obnova je uložená commitem']]],
            'solution' => ['cd ~/web', 'git log --oneline -- rozvrh.html', 'git checkout {f:del_short}~1 -- rozvrh.html', 'git commit -m "Obnoven rozvrh"'],
            'learn' => 'Git nic nezapomíná: i smazaný soubor najdeš v historii. Zápis <commit>~1 znamená „commit těsně před“.'],
        ['id' => 'git-5', 'type' => 'code', 'title' => 'Vydání 1.0', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'commands' => $cmds,
            'story' => 'Pro výroční zprávu chce vedení vědět, co přineslo vydání webu 1.0. Tým každé vydání označí značkou (tagem) a do jejího popisu napíše, kde leží poznámky k vydání.',
            'task' => 'Najdi značku v1.0, přečti její popis a v poznámkách k vydání najdi kód. Soubor s poznámkami v aktuální verzi už není.',
            'hints' => ['Značky i s prvním řádkem popisu: git tag -n', 'Celý popis značky a commit, na který ukazuje: git show v1.0', 'Soubor z revize přečteš bez přepínání: git show v1.0:cesta/k/souboru'],
            'generate' => $repo('tag'), 'solution' => ['cd ~/web', 'git tag -n', 'git show v1.0 --stat', 'git show v1.0:{f:notes10}', 'submit {CODE}'],
            'learn' => 'Značka (tag) je pojmenovaný commit – typicky vydání. Anotovaná značka má i autora, datum a popis. git show revize:soubor ukáže soubor z libovolné verze.'],
        ['id' => 'git-6', 'type' => 'code', 'title' => 'Odložená práce', 'difficulty' => 2, 'points' => 150, 'minutes' => 8, 'commands' => $cmds,
            'story' => 'Minulý týden jsi začal(a) psát stránku o školních kroužcích, ale musel(a) jsi rychle řešit něco jiného, a tak jsi rozdělanou práci odložil(a) příkazem git stash. Pak jsi odložil(a) ještě jednu drobnost.',
            'task' => 'Vrať se k rozdělané stránce o kroužcích (krouzky.html) a najdi v ní kód.',
            'hints' => ['Odložené změny vypíše git stash list – nejnovější je stash@{0}.', 'Co je ve kterém stashi: git stash show -p stash@{1}', 'Správný stash vrátíš git stash pop stash@{N}, pak cat krouzky.html'],
            'generate' => $repo('stash'), 'solution' => ['cd ~/web', 'git stash list', 'git stash pop {f:stash_ref}', 'cat krouzky.html', 'submit {CODE}'],
            'learn' => 'git stash odloží rozdělanou práci a vrátí čistý pracovní strom. git stash pop ji vrátí zpět – i když mezitím děláš něco jiného.'],
        ['id' => 'git-7', 'type' => 'code', 'title' => 'Nový design', 'difficulty' => 3, 'points' => 200, 'minutes' => 10, 'commands' => $cmds,
            'story' => 'Někdo z týmu připravil nový vzhled webu ve vlastní větvi na serveru a nechal v ní poznámku pro ostatní. Ty máš ve své kopii rozdělanou úpravu stylu, kterou nechceš ztratit.',
            'task' => 'Přepni se do větve s novým designem a přečti poznámku k designu – je v ní kód. Rozdělanou změnu v css/styl.css si odlož.',
            'hints' => ['Větve ze serveru vypíše git branch -a (remotes/origin/…).', 'Přepnutí zastaví rozdělaná změna – odlož ji: git stash (vrátíš ji git stash pop).', 'Pak git switch <jméno-větve> a ls – poznámka je nový soubor.'],
            'generate' => $repo('design'), 'solution' => ['cd ~/web', 'git branch -a', 'git stash', 'git switch {f:design_branch}', 'cat {f:design_note}', 'submit {CODE}'],
            'learn' => 'Větev je samostatná linie vývoje. Git nepřepne větev, kdyby tím přepsal neuložené změny – nejdřív je ulož commitem, nebo odlož do stashe.'],
    ];
}

lab58_register_pack(['id' => 'git', 'title' => 'Git v terminálu', 'description' => 'Tým spravuje repozitář školního webu: historie, autoři změn, větve, značky vydání a odložená práce – všechno z příkazové řádky.',
    'order' => 72, 'classes' => null, 'unlock' => 'sequential', 'badge' => ['id' => 'strazce-historie', 'label' => 'Strážce historie', 'icon' => '🌿'], 'icon' => '⎇', 'tone' => 'teal', 'inspired' => 'Pro Git', 'source' => 'v58'], 'lab58_gitlv_levels');

lab58_register_manual(['categories' => ['verze' => ['label' => 'Správa verzí', 'icon' => '⎇', 'lead' => 'Historie projektu v gitu: commity, větve, značky a odložená práce.']], 'commands' => ['git' => [
    'cat' => 'verze', 'summary' => 'Správa verzí: historie projektu, větve, značky a odložená práce.', 'synopsis' => 'git <podpříkaz> [volby] [argumenty]',
    'about' => 'Git si pamatuje každou uloženou verzi projektu (commit) – kdo ji udělal, kdy a proč. Pracuje se na třech místech: pracovní složka (soubory, které upravuješ), index (změny připravené přes git add) a historie (git commit). Větve jsou samostatné linie vývoje, značky (tagy) označují důležité verze a stash odloží rozdělanou práci. V laboratoři jde o zjednodušený výukový model: objekty jsou JSON soubory ve složce .git/objects a síť (push, pull) není – klonuje se z místní složky.',
    'options' => [['init [složka]', 'založí nový repozitář'], ['clone <cesta> [složka]', 'zkopíruje repozitář i s historií'], ['status [-s]', 'co je změněné a co připravené'], ['add <soubor>', 'připraví změnu do indexu'], ['commit -m "…" [-a] [--amend]', 'uloží připravené změny'],
        ['log [--oneline] [-p] [-n N] [--all] [--author=…] [-S text]', 'historie commitů'], ['show <revize>[:soubor]', 'commit, značka nebo soubor ve verzi'], ['diff [--staged] [A..B] [-- soubor]', 'rozdíly'], ['blame <soubor>', 'kdo naposledy změnil který řádek'],
        ['switch <větev> / -c <nová>', 'přepne (založí) větev'], ['checkout <revize> -- <soubor>', 'vrátí soubor z revize'], ['branch [-a] [-v] [-d]', 'seznam, založení, smazání větví'], ['tag [-a -m "…"] [-n]', 'značky'], ['restore [--staged] <soubor>', 'vrátí soubor / zruší přípravu'],
        ['reset [--soft|--hard] <revize>', 'posune větev na jinou revizi'], ['stash [list|show -p|pop]', 'odloží a vrátí rozdělanou práci'], ['config [--global] [--list]', 'nastavení, např. user.name'], ['rev-parse [--short] <revize>', 'převede revizi na hash']],
    'examples' => [['git init projekt', 'založí repozitář ve složce projekt'], ['git clone /srv/git/web.git', 'naklonuje repozitář školního webu'], ['git log --oneline --all', 'stručná historie všech větví'], ['git show HEAD~1:index.html', 'index.html o jeden commit zpátky'], ['git diff --staged', 'co půjde do příštího commitu']],
    'tldr' => [['git status', 'co je nového a co je připravené'], ['git add . && git commit -m "Popis změny"', 'připraví a uloží všechny změny'], ['git log --oneline', 'stručná historie'], ['git blame index.html', 'kdo naposledy změnil který řádek']],
    'see_also' => ['diff', 'ls', 'cat'], 'related' => ['diff'], 'level' => 2, 'in_lab' => true,
    'tip' => 'Revize: HEAD (poslední commit), HEAD~2 (o dva zpět), jméno větve, značka nebo zkrácený hash. Smazaný soubor zadej za --, např. git log -- rozvrh.html.',
    'warn' => 'git reset --hard, git restore a git checkout -- soubor zahodí neuložené změny v souborech. Nejdřív se podívej na git status.',
]]]);
