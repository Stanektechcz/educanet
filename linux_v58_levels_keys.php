<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úrovní „Klíče a přístupy" (LAB-02).
 *
 * Cvičná správa serveru: vytvoření SSH klíče, oprava práv, přihlášení klíčem na simulovaný
 * server, přenos souboru přes scp, alias v ~/.ssh/config a změněný otisk hostitele.
 * Vzdálený server i klíče jsou fiktivní deterministická data (žádná kryptografie, žádná síť).
 * Určeno pro 3.A/4.A (OS a sítě) – navazuje na správu serverů.
 */

const LAB58_KEYS_HOST = 'intranet.skola.test';

/** Připraví lokální klíč a vzdáleného hosta pro úroveň „Klíče a přístupy". */
function lab58_keys_setup(Lab57World $w, array $o): void
{
    $code = $w->code();
    $seed = $w->seed . '|keys';
    $remoteUser = (string)($o['remote_user'] ?? 'student');
    $kp = lab58_ssh_keypair($seed . '|userkey', 'ed25519', 0, 'student@lab-pc');
    if (!empty($o['provide_key'])) {
        $bad = !empty($o['bad_perms']);
        $w->mkfile('/home/student/.ssh/id_ed25519', $kp['priv'], $bad ? 0644 : 0600, 'student', 'student', $w->now - 86400 * 2);
        $w->mkfile('/home/student/.ssh/id_ed25519.pub', $kp['pub'], 0644, 'student', 'student', $w->now - 86400 * 2);
        $dir = $w->fs->get('/home/student/.ssh');
        if ($dir !== null) { $dir['m'] = $bad ? 0755 : 0700; $w->fs->set('/home/student/.ssh', $dir); }
    }
    $authorized = (!empty($o['provide_key']) && ($o['authorize'] ?? true)) ? [$kp['fp']] : [];
    $codePath = (string)($o['code_path'] ?? ('/home/' . $remoteUser . '/kod.txt'));
    $files = [$codePath => $code . "\n"];
    foreach ((array)($o['files'] ?? []) as $path => $content) $files[$path] = $content;
    $w->ext['ssh']['hosts'][LAB58_KEYS_HOST] = [
        'ip' => '10.0.0.10', 'hostname' => 'intranet', 'host_seed' => $seed . '|hostkey',
        'users' => [$remoteUser => ['home' => '/home/' . $remoteUser, 'authorized' => $authorized, 'password' => !empty($o['password'])]],
        'files' => $files,
    ];
    $w->facts['code_path'] = $codePath;
    if (!empty($o['changed_key'])) {
        $old = lab58_ssh_keypair($seed . '|oldhostkey', 'ed25519', 0, '');
        $w->mkfile('/home/student/.ssh/known_hosts', LAB58_KEYS_HOST . ' ' . $old['algo'] . ' ' . $old['field'] . "\n", 0644, 'student', 'student', $w->now - 86400 * 5);
    }
}

function lab58_levels_keys(): array
{
    return [
        [
            'id' => 'klice-1', 'type' => 'check', 'title' => 'Vyrob si klíč', 'difficulty' => 1, 'points' => 90, 'minutes' => 5,
            'story' => 'Správce serverů se nepřihlašuje heslem, ale párem SSH klíčů: soukromý si necháš a chráníš, veřejný nahraješ na server. Začni tím, že si klíč vyrobíš.',
            'task' => 'Vygeneruj pár klíčů typu ed25519 do ~/.ssh/id_ed25519 (bez přístupové fráze).',
            'commands' => ['ssh-keygen', 'ls', 'cat'],
            'hints' => ['Klíče vyrábí ssh-keygen. Typ zadáš volbou -t, cíl volbou -f a prázdnou frázi -N \'\'.', 'ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N \'\'', 'Vzniknou dva soubory: id_ed25519 (soukromý, práva 600) a id_ed25519.pub (veřejný).'],
            'checks' => [
                ['label' => 'Soukromý klíč ~/.ssh/id_ed25519 existuje', 'fn' => static fn(Lab57World $w): bool => $w->fs->isFile('/home/student/.ssh/id_ed25519')],
                ['label' => 'Soukromý klíč má práva 600 (čte jen vlastník)', 'fn' => static fn(Lab57World $w): bool => ((int)($w->fs->get('/home/student/.ssh/id_ed25519')['m'] ?? 0) & 0077) === 0 && $w->fs->isFile('/home/student/.ssh/id_ed25519')],
                ['label' => 'Veřejný klíč ~/.ssh/id_ed25519.pub existuje', 'fn' => static fn(Lab57World $w): bool => $w->fs->isFile('/home/student/.ssh/id_ed25519.pub')],
            ],
            'solution' => static fn(Lab57World $w): array => ["ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ''", 'ls -l ~/.ssh'],
            'learn' => 'SSH klíč je pár: soukromý (id_ed25519) nikdy neopouští tvůj počítač a má práva 600, veřejný (.pub) dáváš na servery.',
        ],
        [
            'id' => 'klice-2', 'type' => 'code', 'title' => 'Příliš otevřený klíč', 'difficulty' => 2, 'points' => 130, 'minutes' => 7,
            'story' => 'Na serveru intranet.skola.test už tvůj veřejný klíč je. Soukromý klíč máš i na svém počítači, jenže SSH se přihlásit odmítá.',
            'task' => 'Oprav práva klíče a přečti kód ze serveru: ssh student@intranet.skola.test \'cat kod.txt\', pak submit EDU-XXXX-XXXX.',
            'commands' => ['ssh', 'ls', 'chmod', 'submit'],
            'hints' => ['Zkus se přihlásit: ssh student@intranet.skola.test \'ls\'. SSH si postěžuje na práva klíče.', 'Soukromý klíč nesmí číst nikdo jiný: chmod 700 ~/.ssh a chmod 600 ~/.ssh/id_ed25519.', 'Pak: ssh student@intranet.skola.test \'cat kod.txt\' a výsledek odevzdej příkazem submit.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => lab58_keys_setup($w, ['provide_key' => true, 'bad_perms' => true]),
            'solution' => static fn(Lab57World $w): array => ['chmod 700 ~/.ssh', 'chmod 600 ~/.ssh/id_ed25519', "ssh student@intranet.skola.test 'cat kod.txt'", 'submit ' . $w->code()],
            'learn' => 'SSH odmítne soukromý klíč, který může číst někdo jiný („UNPROTECTED PRIVATE KEY FILE"). Klíč musí mít práva 600 a složka ~/.ssh práva 700.',
        ],
        [
            'id' => 'klice-3', 'type' => 'code', 'title' => 'Nahraj si klíč na server', 'difficulty' => 2, 'points' => 150, 'minutes' => 8,
            'story' => 'Na server intranet.skola.test se zatím hlásíš heslem. Chceš se přihlašovat klíčem – jenže server tvůj klíč ještě nezná.',
            'task' => 'Vyrob si klíč, nahraj ho na server a přihlas se jím pro kód: ssh student@intranet.skola.test \'cat kod.txt\'.',
            'commands' => ['ssh-keygen', 'ssh-copy-id', 'ssh', 'submit'],
            'hints' => ['Nejdřív klíč: ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N \'\'.', 'Veřejný klíč nahraješ na server příkazem ssh-copy-id student@intranet.skola.test.', 'Pak už se přihlásíš klíčem: ssh student@intranet.skola.test \'cat kod.txt\'.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => lab58_keys_setup($w, ['provide_key' => false, 'password' => true]),
            'solution' => static fn(Lab57World $w): array => ["ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519 -N ''", 'ssh-copy-id student@intranet.skola.test', "ssh student@intranet.skola.test 'cat kod.txt'", 'submit ' . $w->code()],
            'learn' => 'ssh-copy-id přidá tvůj veřejný klíč do ~/.ssh/authorized_keys na serveru. Poté se přihlásíš klíčem bez hesla.',
        ],
        [
            'id' => 'klice-4', 'type' => 'code', 'title' => 'Stáhni soubor přes scp', 'difficulty' => 2, 'points' => 150, 'minutes' => 7,
            'story' => 'Na serveru je v podsložce tajne/ soubor s kódem. Nechceš se přihlašovat a hledat ho ručně – stačí ho zkopírovat k sobě.',
            'task' => 'Zkopíruj tajne/kod.txt ze serveru k sobě příkazem scp, přečti ho a odevzdej kód.',
            'commands' => ['scp', 'cat', 'ls', 'submit'],
            'hints' => ['scp kopíruje jako cp, ale mezi počítači: scp user@host:cesta cíl.', 'scp student@intranet.skola.test:tajne/kod.txt .', 'Pak cat kod.txt a submit.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => lab58_keys_setup($w, ['provide_key' => true, 'code_path' => '/home/student/tajne/kod.txt']),
            'solution' => static fn(Lab57World $w): array => ['scp student@intranet.skola.test:tajne/kod.txt .', 'cat kod.txt', 'submit ' . $w->code()],
            'learn' => 'scp přenáší soubory přes SSH. Cesta se serverem má tvar user@host:cesta; ~ i relativní cesty vedou z domovské složky uživatele na serveru.',
        ],
        [
            'id' => 'klice-5', 'type' => 'code', 'title' => 'Zkratka v ~/.ssh/config', 'difficulty' => 3, 'points' => 180, 'minutes' => 9,
            'story' => 'Server má jiné přihlašovací jméno (admin) a dlouhou adresu. Aby ses nemusel(a) pokaždé psát celé ssh admin@intranet.skola.test, nastavíš si zkratku.',
            'task' => 'Vytvoř v ~/.ssh/config záznam Host server (HostName, User, IdentityFile), přihlas se přes ssh server \'cat kod.txt\' a odevzdej kód.',
            'commands' => ['nano', 'ssh', 'cat', 'submit'],
            'hints' => ['Do ~/.ssh/config patří blok: Host server / HostName intranet.skola.test / User admin / IdentityFile ~/.ssh/id_ed25519.', 'Soubor vytvoříš i příkazem printf ... > ~/.ssh/config.', 'Pak: ssh server \'cat kod.txt\'.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => lab58_keys_setup($w, ['provide_key' => true, 'remote_user' => 'admin', 'code_path' => '/home/admin/kod.txt']),
            'solution' => static fn(Lab57World $w): array => ['mkdir -p ~/.ssh', "printf 'Host server\\n    HostName intranet.skola.test\\n    User admin\\n    IdentityFile ~/.ssh/id_ed25519\\n' > ~/.ssh/config", "ssh server 'cat kod.txt'", 'submit ' . $w->code()],
            'learn' => 'V ~/.ssh/config si pojmenuješ servery: Host je zkratka, HostName skutečná adresa, User přihlašovací jméno, IdentityFile klíč. Pak stačí ssh server.',
        ],
        [
            'id' => 'klice-6', 'type' => 'code', 'title' => 'Otisk serveru se změnil', 'difficulty' => 3, 'points' => 200, 'minutes' => 10,
            'story' => 'Server intranet.skola.test byl přeinstalován a dostal nový hostitelský klíč. SSH tě proto varuje, že se identita serveru změnila, a odmítá se připojit.',
            'task' => 'Rozumíš, proč server přeinstalovali. Zapomeň starý otisk, znovu se připoj a odevzdej kód.',
            'commands' => ['ssh', 'ssh-keygen', 'cat', 'submit'],
            'hints' => ['Varování „REMOTE HOST IDENTIFICATION HAS CHANGED" znamená, že otisk v known_hosts nesedí s tím, co server posílá.', 'Když víš, že změna je v pořádku (server byl přeinstalován), starý záznam smaž: ssh-keygen -R intranet.skola.test.', 'Pak se připoj znovu a přijmi nový otisk: ssh student@intranet.skola.test \'cat kod.txt\'.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => lab58_keys_setup($w, ['provide_key' => true, 'changed_key' => true]),
            'solution' => static fn(Lab57World $w): array => ['ssh-keygen -R intranet.skola.test', "ssh student@intranet.skola.test 'cat kod.txt'", 'submit ' . $w->code()],
            'learn' => 'known_hosts si pamatuje otisk každého serveru. Když se změní, SSH varuje (mohl by to být podvodník). Pokud je změna očekávaná, starý otisk smažeš přes ssh-keygen -R host.',
        ],
    ];
}

lab58_register_pack(
    [
        'id' => 'klice', 'title' => 'Klíče a přístupy', 'description' => 'SSH klíče, práva, přihlášení na server, scp a otisky hostitelů.',
        'order' => 62, 'classes' => ['class_3a', 'class_4a'], 'unlock' => 'sequential',
        'badge' => ['id' => 'klicnik', 'label' => 'Klíčník', 'icon' => '🔑'], 'icon' => '🔑', 'tone' => 'blue', 'inspired' => 've stylu OverTheWire Bandit',
    ],
    'lab58_levels_keys'
);
