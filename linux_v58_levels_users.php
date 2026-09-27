<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úrovní „Uživatelé a práva" (LAB-05).
 *
 * Účet a skupina pro projekt, sdílený adresář se skupinovými právy a setgid, umask, oprava
 * vlastníka, čtení sudo pravidel a zamčení účtu. Vše mění jen $w->users / $w->groups a soubory
 * ve VFS – hashe v /etc/shadow jsou fiktivní. Určeno pro 3.A/4.A (OS a sítě).
 */

/** Přidá skupinu do světa (deterministicky) a přepíše /etc/passwd, /etc/group. */
function lab58_users_add_group(Lab57World $w, string $name, int $gid): void
{
    if (!isset($w->groups[$name])) $w->groups[$name] = $gid;
}

function lab58_users_add_user(Lab57World $w, string $name, int $uid, array $groups, string $gecos = ''): void
{
    $w->groups[$name] ??= $uid;
    $w->users[$name] = ['uid' => $uid, 'gid' => (int)$w->groups[$name], 'home' => '/home/' . $name, 'shell' => '/bin/bash', 'groups' => array_values(array_unique(array_merge([$name], $groups))), 'gecos' => $gecos, 'pw' => true];
    $w->mkdirp('/home/' . $name, 0755, $name, $name);
}

function lab58_levels_users(): array
{
    return [
        [
            'id' => 'prava-1', 'type' => 'check', 'title' => 'Účet pro nováčka', 'difficulty' => 1, 'points' => 100, 'minutes' => 6,
            'story' => 'Do týmu nastupuje nový kolega. Potřebuje vlastní účet a musí být ve skupině vyvoj, aby se dostal ke společným souborům.',
            'task' => 'Vytvoř skupinu vyvoj a uživatele koder s domovskou složkou, který do skupiny vyvoj patří.',
            'commands' => ['groupadd', 'useradd', 'id', 'getent', 'sudo'],
            'hints' => ['Skupinu založíš: sudo groupadd vyvoj.', 'Uživatele s domovem a zařazením do skupiny: sudo useradd -m -G vyvoj koder.', 'Ověř: id koder nebo getent group vyvoj.'],
            'build' => static fn(Lab57World $w, Lab57Rng $r) => null,
            'checks' => [
                ['label' => 'Skupina vyvoj existuje', 'fn' => static fn(Lab57World $w): bool => isset($w->groups['vyvoj'])],
                ['label' => 'Uživatel koder existuje a má domovskou složku', 'fn' => static fn(Lab57World $w): bool => isset($w->users['koder']) && $w->fs->isDir($w->home('koder'))],
                ['label' => 'koder je ve skupině vyvoj', 'fn' => static fn(Lab57World $w): bool => isset($w->users['koder']) && in_array('vyvoj', $w->userGroups('koder'), true)],
            ],
            'solution' => static fn(Lab57World $w): array => ['sudo groupadd vyvoj', 'sudo useradd -m -G vyvoj koder', 'id koder'],
            'learn' => 'Účty zakládá useradd (-m vytvoří domov, -G přidá do skupin), skupiny groupadd. Skupiny sdílejí přístup k souborům mezi více lidmi.',
        ],
        [
            'id' => 'prava-2', 'type' => 'check', 'title' => 'Sdílená složka se setgid', 'difficulty' => 2, 'points' => 150, 'minutes' => 8,
            'story' => 'Tým vyvoj potřebuje společnou složku /srv/tym, kam mohou všichni zapisovat a kde nové soubory automaticky patří skupině.',
            'task' => 'Vytvoř /srv/tym, nastav skupinu vyvoj, práva pro zápis skupiny a setgid bit (2775).',
            'commands' => ['mkdir', 'chown', 'chmod', 'ls', 'sudo'],
            'hints' => ['Založ složku: sudo mkdir -p /srv/tym.', 'Skupinu nastavíš přes chown: sudo chown root:vyvoj /srv/tym.', 'Práva se setgid: sudo chmod 2775 /srv/tym (dvojka na začátku = setgid).'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_users_add_group($w, 'vyvoj', 1500);
                $w->users['student']['groups'] = array_values(array_unique(array_merge($w->users['student']['groups'], ['vyvoj'])));
                lab57_world_write_accounts($w);
            },
            'checks' => [
                ['label' => '/srv/tym je složka skupiny vyvoj', 'fn' => static fn(Lab57World $w): bool => $w->fs->isDir('/srv/tym') && (string)($w->fs->get('/srv/tym')['g'] ?? '') === 'vyvoj'],
                ['label' => 'Složka má setgid bit', 'fn' => static fn(Lab57World $w): bool => ((int)($w->fs->get('/srv/tym')['m'] ?? 0) & 02000) !== 0],
                ['label' => 'Skupina smí do složky zapisovat', 'fn' => static fn(Lab57World $w): bool => ((int)($w->fs->get('/srv/tym')['m'] ?? 0) & 020) !== 0],
            ],
            'solution' => static fn(Lab57World $w): array => ['sudo mkdir -p /srv/tym', 'sudo chown root:vyvoj /srv/tym', 'sudo chmod 2775 /srv/tym', 'ls -ld /srv/tym'],
            'learn' => 'setgid na složce (chmod 2775) zařídí, že nové soubory v ní automaticky patří skupině složky – ideální pro týmovou práci.',
        ],
        [
            'id' => 'prava-3', 'type' => 'check', 'title' => 'umask pro tým', 'difficulty' => 2, 'points' => 150, 'minutes' => 7,
            'story' => 'Ve sdílené složce /srv/tym vytvoříš soubor, ale kolegové ho nemůžou upravit. Nová maska práv (umask) rozhoduje, jaká práva nové soubory dostanou.',
            'task' => 'Nastav umask tak, aby nové soubory směla upravovat i skupina, a vytvoř /srv/tym/plan.txt (musí být zapisovatelný pro skupinu).',
            'commands' => ['umask', 'touch', 'ls'],
            'hints' => ['Aktuální masku ukáže samotné umask. Pro skupinový zápis nastav umask 002.', 'Pak vytvoř soubor: touch /srv/tym/plan.txt.', 'Díky setgid na složce soubor rovnou patří skupině vyvoj; ověř ls -l /srv/tym.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_users_add_group($w, 'vyvoj', 1500);
                $w->users['student']['groups'] = array_values(array_unique(array_merge($w->users['student']['groups'], ['vyvoj'])));
                lab57_world_write_accounts($w);
                $w->fs->set('/srv/tym', ['t' => 'd', 'm' => 02775, 'u' => 'root', 'g' => 'vyvoj', 'mt' => $w->now - 3600]);
            },
            'checks' => [
                ['label' => '/srv/tym/plan.txt existuje', 'fn' => static fn(Lab57World $w): bool => $w->fs->isFile('/srv/tym/plan.txt')],
                ['label' => 'Soubor smí upravovat skupina', 'fn' => static fn(Lab57World $w): bool => ((int)($w->fs->get('/srv/tym/plan.txt')['m'] ?? 0) & 020) !== 0],
                ['label' => 'Soubor patří skupině vyvoj (díky setgid)', 'fn' => static fn(Lab57World $w): bool => (string)($w->fs->get('/srv/tym/plan.txt')['g'] ?? '') === 'vyvoj'],
            ],
            'solution' => static fn(Lab57World $w): array => ['umask 002', 'touch /srv/tym/plan.txt', 'ls -l /srv/tym'],
            'learn' => 'umask určuje, která práva se novým souborům odeberou. umask 002 nechá skupině právo zápisu (soubory 664), umask 022 ho odebere (644).',
        ],
        [
            'id' => 'prava-4', 'type' => 'check', 'title' => 'Špatný vlastník', 'difficulty' => 2, 'points' => 140, 'minutes' => 7,
            'story' => 'Do sdílené složky se dostal soubor data.csv, který patří jen rootovi. Tým s ním nemůže pracovat.',
            'task' => 'Změň skupinu souboru /srv/tym/data.csv na vyvoj, aby s ním mohl pracovat celý tým.',
            'commands' => ['ls', 'chown', 'sudo'],
            'hints' => ['ls -l /srv/tym ukáže, že data.csv patří root:root.', 'Skupinu změníš přes chown s dvojtečkou: sudo chown root:vyvoj /srv/tym/data.csv.', 'Ověř ls -l /srv/tym.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_users_add_group($w, 'vyvoj', 1500);
                $w->users['student']['groups'] = array_values(array_unique(array_merge($w->users['student']['groups'], ['vyvoj'])));
                lab57_world_write_accounts($w);
                $w->fs->set('/srv/tym', ['t' => 'd', 'm' => 02775, 'u' => 'root', 'g' => 'vyvoj', 'mt' => $w->now - 3600]);
                $w->mkfile('/srv/tym/data.csv', "id,jmeno\n1,Ada\n2,Bob\n", 0644, 'root', 'root', $w->now - 1800);
            },
            'checks' => [
                ['label' => 'data.csv patří skupině vyvoj', 'fn' => static fn(Lab57World $w): bool => (string)($w->fs->get('/srv/tym/data.csv')['g'] ?? '') === 'vyvoj'],
            ],
            'solution' => static fn(Lab57World $w): array => ['ls -l /srv/tym', 'sudo chown root:vyvoj /srv/tym/data.csv', 'ls -l /srv/tym'],
            'learn' => 'chown mění vlastníka i skupinu: chown uzivatel:skupina soubor. Samotnou skupinu změníš zápisem :skupina.',
        ],
        [
            'id' => 'prava-5', 'type' => 'answer', 'title' => 'Co smím jako správce?', 'difficulty' => 3, 'points' => 170, 'minutes' => 8,
            'story' => 'Účet nasazovac má povoleno spouštět jen jeden konkrétní příkaz jako root – bez hesla. Zjisti který.',
            'task' => 'Z pravidel sudo zjisti, kterou službu smíš restartovat (sudo -l). Odpověz jménem služby (např. nginx).',
            'commands' => ['sudo', 'cat', 'visudo', 'answer'],
            'hints' => ['Svá oprávnění vypíšeš: sudo -l. Hledej řádek NOPASSWD.', 'Pravidlo je v /etc/sudoers.d/. Přečíst ho můžeš i přes sudo cat /etc/sudoers.d/nasazeni.', 'Odpověz jen jméno služby z příkazu systemctl restart <služba>.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                $svc = (string)$r->pick(['nginx', 'cron', 'ssh']);
                $w->mem['prava5'] = $svc;
                $w->mkfile('/etc/sudoers', "Defaults\tenv_reset\nDefaults\tmail_badpass\nroot\tALL=(ALL:ALL) ALL\n%sudo\tALL=(ALL:ALL) ALL\n#includedir /etc/sudoers.d\n", 0440, 'root', 'root', $w->now - 86400);
                $w->mkdirp('/etc/sudoers.d', 0750, 'root', 'root');
                $w->mkfile('/etc/sudoers.d/nasazeni', "student ALL=(root) NOPASSWD: /usr/bin/systemctl restart " . $svc . "\n", 0440, 'root', 'root', $w->now - 86400);
            },
            'answer' => static fn(Lab57World $w): string => (string)($w->mem['prava5'] ?? 'nginx'),
            'answer_format' => 'jméno služby (např. nginx)',
            'solution' => static fn(Lab57World $w): array => ['sudo -l', 'sudo visudo -c', 'answer ' . (string)($w->mem['prava5'] ?? 'nginx')],
            'learn' => 'sudo -l ukáže, co smíš spouštět jako správce. Pravidla jsou v /etc/sudoers a /etc/sudoers.d/. NOPASSWD znamená bez zadání hesla; visudo -c ověří, že soubory nemají chybu.',
        ],
        [
            'id' => 'prava-6', 'type' => 'check', 'title' => 'Zamkni odešlý účet', 'difficulty' => 2, 'points' => 140, 'minutes' => 6,
            'story' => 'Kolega byvaly odešel z firmy. Jeho účet se nesmí smazat (kvůli souborům), ale nikdo se na něj už nesmí přihlásit.',
            'task' => 'Zamkni účet byvaly, aby se přes něj nešlo přihlásit. Účet ani jeho data nemaž.',
            'commands' => ['usermod', 'passwd', 'getent', 'sudo'],
            'hints' => ['Účet zamkneš (ne smažeš): sudo usermod -L byvaly.', 'Zamčené heslo poznáš v /etc/shadow podle vykřičníku na začátku (sudo cat /etc/shadow).', 'Účet musí dál existovat – getent passwd byvaly ho stále najde.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_users_add_user($w, 'byvaly', 1600, [], 'Byvaly Kolega');
                lab57_world_write_accounts($w);
            },
            'checks' => [
                ['label' => 'Účet byvaly stále existuje', 'fn' => static fn(Lab57World $w): bool => isset($w->users['byvaly'])],
                ['label' => 'Účet byvaly je zamčený', 'fn' => static fn(Lab57World $w): bool => ($w->users['byvaly']['pw'] ?? null) === 'locked'],
            ],
            'solution' => static fn(Lab57World $w): array => ['sudo usermod -L byvaly', 'getent passwd byvaly'],
            'learn' => 'Zamčení účtu (usermod -L) zablokuje přihlášení heslem, ale účet a jeho data zůstanou. Smazání (userdel) je nevratné – u odchodů se proto obvykle jen zamyká.',
        ],
    ];
}

lab58_register_pack(
    [
        'id' => 'prava', 'title' => 'Uživatelé a práva', 'description' => 'Účty, skupiny, sdílené složky se setgid, umask, vlastníci a pravidla sudo.',
        'order' => 68, 'classes' => ['class_3a', 'class_4a'], 'unlock' => 'sequential',
        'badge' => ['id' => 'spravce-uctu', 'label' => 'Správce účtů', 'icon' => '👥'], 'icon' => '👥', 'tone' => 'green', 'inspired' => 'správa uživatelů a přístupů',
    ],
    'lab58_levels_users'
);
