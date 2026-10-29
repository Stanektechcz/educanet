<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v58 · Linux Lab – balíček úrovní „Plánovač cron" (LAB-04).
 *
 * Údržba serveru ve stylu OverTheWire Bandit 21–24: najdi, co cron spouští, přečti cizí skript,
 * zjisti, kam zapisuje, naplánuj vlastní úlohu, oprav chybný řádek crontabu, posuň čas (timewarp)
 * a ověř výsledek. Vše je simulace – nic se nespouští, čas jen z lab58_now().
 * Určeno pro 3.A/4.A (OS a sítě).
 */

/** Připraví standardní složky run-parts, aby výchozí /etc/crontab při timewarpu nechyboval. */
function lab58_cron_prepare(Lab57World $w): void
{
    foreach (['/etc/cron.hourly', '/etc/cron.daily', '/etc/cron.weekly', '/etc/cron.monthly'] as $dir) {
        if (!$w->fs->isDir($dir)) $w->mkdirp($dir, 0755, 'root', 'root');
    }
}

function lab58_levels_cron(): array
{
    return [
        [
            'id' => 'cron-1', 'type' => 'code', 'title' => 'Co spouští cron?', 'difficulty' => 1, 'points' => 110, 'minutes' => 6,
            'story' => 'Na serveru běží nějaká automatická úloha a ty máš zjistit, co dělá. Naplánované úlohy systému bývají v /etc/cron.d/.',
            'task' => 'Najdi v /etc/cron.d, jaký skript cron spouští, přečti ho a zjisti, kam zapisuje. Z toho souboru odevzdej kód.',
            'commands' => ['ls', 'cat', 'submit'],
            'hints' => ['Podívej se do složky: ls /etc/cron.d a pak cat /etc/cron.d/sbirka.', 'V řádku je cesta ke skriptu. Přečti ho: cat /usr/local/bin/sbirka.sh a najdi, do jakého souboru zapisuje.', 'Ten výstupní soubor pak vypiš (cat) – je v něm kód.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $w->mkfile('/etc/cron.d/sbirka', "# Sběr stavu každých 5 minut\n*/5 * * * * root /usr/local/bin/sbirka.sh\n", 0644, 'root', 'root', $w->now - 86400);
                $w->mkfile('/usr/local/bin/sbirka.sh', "#!/bin/bash\n# Zapíše aktuální stav serveru\ncat /root/stav.txt > /tmp/sbirka.out\n", 0755, 'root', 'root', $w->now - 86400);
                // Zdroj úlohy (čte ho jen root); výstup už jednou proběhl – timewarp ho jen obnoví.
                $w->mkfile('/root/stav.txt', $w->code() . "\n", 0600, 'root', 'root', $w->now - 86400);
                $w->mkfile('/tmp/sbirka.out', $w->code() . "\n", 0644, 'root', 'root', $w->now - 120);
            },
            'solution' => static fn(Lab57World $w): array => ['ls /etc/cron.d', 'cat /etc/cron.d/sbirka', 'cat /usr/local/bin/sbirka.sh', 'cat /tmp/sbirka.out', 'submit ' . $w->code()],
            'learn' => 'Systémové naplánované úlohy jsou v /etc/cron.d/ a /etc/crontab. Každý řádek má čas, uživatele a příkaz. Když nevíš, co úloha dělá, přečti si její skript.',
        ],
        [
            'id' => 'cron-2', 'type' => 'code', 'title' => 'Posuň čas a nech to proběhnout', 'difficulty' => 2, 'points' => 150, 'minutes' => 8,
            'story' => 'Cron má každých 10 minut zkopírovat tajný soubor někam, kam se dostaneš. Nechce se ti čekat – v laboratoři umíš čas posunout.',
            'task' => 'Zjisti, co úloha dělá, posuň čas příkazem timewarp, aby proběhla, a přečti kód z místa, kam zapsala.',
            'commands' => ['cat', 'timewarp', 'submit'],
            'hints' => ['cat /etc/cron.d/kopirka a cat /usr/local/bin/kopirka.sh – uvidíš, že čte /root/heslo.txt a píše do /tmp.', 'Úloha běží každých 10 minut. Posuň čas: timewarp +10m.', 'Pak přečti výstupní soubor v /tmp.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $w->mkfile('/root/heslo.txt', $w->code() . "\n", 0600, 'root', 'root', $w->now - 86400);
                $w->mkfile('/usr/local/bin/kopirka.sh', "#!/bin/bash\ncat /root/heslo.txt >> /tmp/kopirka.log\n", 0755, 'root', 'root', $w->now - 86400);
                $w->mkfile('/etc/cron.d/kopirka', "*/10 * * * * root /usr/local/bin/kopirka.sh\n", 0644, 'root', 'root', $w->now - 86400);
            },
            'solution' => static fn(Lab57World $w): array => ['cat /etc/cron.d/kopirka', 'cat /usr/local/bin/kopirka.sh', 'timewarp +10m', 'cat /tmp/kopirka.log', 'submit ' . $w->code()],
            'learn' => 'Skript spouštěný cronem běží pod svým uživatelem (tady root), takže se dostane i k souborům, které ty přečíst nemůžeš. timewarp v laboratoři přeskočí čas a úlohu spustí.',
        ],
        [
            'id' => 'cron-3', 'type' => 'check', 'title' => 'Naplánuj si vlastní úlohu', 'difficulty' => 2, 'points' => 150, 'minutes' => 8,
            'story' => 'Máš připravený skript ~/uloha.sh, který po spuštění zapíše „hotovo". Tvým úkolem je zařídit, aby ho cron spustil.',
            'task' => 'Naplánuj svůj crontab tak, aby spustil ~/uloha.sh, a nech ho proběhnout (timewarp). Musí vzniknout ~/hotovo.txt s textem hotovo.',
            'commands' => ['crontab', 'timewarp', 'cat'],
            'hints' => ['Plán zapíšeš do souboru a nastavíš: printf \'* * * * * /home/student/uloha.sh\\n\' > plan.txt; crontab plan.txt.', 'Ověř: crontab -l. Pak posuň čas: timewarp +2m.', 'Skript zapíše ~/hotovo.txt – zkontroluj cat ~/hotovo.txt.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $w->mkfile('/home/student/uloha.sh', "#!/bin/bash\necho hotovo > /home/student/hotovo.txt\n", 0755, 'student', 'student', $w->now - 3600);
            },
            'checks' => [
                ['label' => 'Máš naplánovanou úlohu, která spouští ~/uloha.sh', 'fn' => static function (Lab57World $w): bool {
                    $c = (string)($w->fs->get('/var/spool/cron/crontabs/student')['c'] ?? '');
                    return str_contains($c, 'uloha.sh');
                }],
                ['file_contains', ['path' => '/home/student/hotovo.txt', 'equals' => 'hotovo', 'label' => '~/hotovo.txt obsahuje „hotovo" (úloha proběhla)']],
            ],
            'solution' => static fn(Lab57World $w): array => ["printf '* * * * * /home/student/uloha.sh\\n' > plan.txt", 'crontab plan.txt', 'crontab -l', 'timewarp +2m', 'cat /home/student/hotovo.txt'],
            'learn' => 'Vlastní úlohy plánuješ přes crontab. Řádek má 5 časových polí (minuta hodina den měsíc den_v_týdnu) a příkaz. „* * * * *" znamená každou minutu.',
        ],
        [
            'id' => 'cron-4', 'type' => 'code', 'title' => 'Rozbitý řádek', 'difficulty' => 3, 'points' => 180, 'minutes' => 9,
            'story' => 'Úloha, která má hlídat server, nikdy neproběhne. Někdo zřejmě zapsal řádek crontabu špatně.',
            'task' => 'Najdi chybu v /etc/cron.d/hlidac, oprav ji (jako správce), spusť úlohu (timewarp) a odevzdej kód, který zapíše.',
            'commands' => ['cat', 'sudo', 'tee', 'timewarp', 'submit'],
            'hints' => ['cat /etc/cron.d/hlidac – spočítej časová pole. Správně jich má být pět (minuta hodina den měsíc den_v_týdnu), pak uživatel a příkaz.', 'Oprav řádek jako správce: echo \'*/5 * * * * root /usr/local/bin/hlidac.sh\' | sudo tee /etc/cron.d/hlidac.', 'Pak timewarp +5m a přečti /tmp/hlidac.log.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $w->mkfile('/root/klic.txt', $w->code() . "\n", 0600, 'root', 'root', $w->now - 86400);
                $w->mkfile('/usr/local/bin/hlidac.sh', "#!/bin/bash\ncat /root/klic.txt >> /tmp/hlidac.log\n", 0755, 'root', 'root', $w->now - 86400);
                // Chyba: chybí jedno časové pole (jen 4 místo 5).
                $w->mkfile('/etc/cron.d/hlidac', "# hlidac serveru\n*/5 * * * root /usr/local/bin/hlidac.sh\n", 0644, 'root', 'root', $w->now - 86400);
            },
            'solution' => static fn(Lab57World $w): array => ['cat /etc/cron.d/hlidac', "echo '*/5 * * * * root /usr/local/bin/hlidac.sh' | sudo tee /etc/cron.d/hlidac", 'timewarp +5m', 'cat /tmp/hlidac.log', 'submit ' . $w->code()],
            'learn' => 'Systémový crontab (/etc/cron.d, /etc/crontab) má navíc pole s uživatelem: minuta hodina den měsíc den_v_týdnu UŽIVATEL příkaz. Chybějící pole = úloha se nikdy nespustí.',
        ],
        [
            'id' => 'cron-5', 'type' => 'answer', 'title' => 'V kolik běží záloha?', 'difficulty' => 2, 'points' => 130, 'minutes' => 6,
            'story' => 'Vedení chce vědět, kdy přesně se v noci spouští záloha, aby na ni nenaplánovali údržbu.',
            'task' => 'Zjisti z /etc/crontab, v kolik hodin se spouští zaloha.sh, a odpověz ve formátu HH:MM.',
            'commands' => ['cat', 'grep', 'answer'],
            'hints' => ['Vypiš plán: cat /etc/crontab a najdi řádek s zaloha.sh.', 'První dvě pole jsou minuta a hodina. Pořadí je: minuta hodina …', 'Odpověz čas ve formátu HH:MM, např. answer 02:30.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $h = $r->int(1, 5);
                $m = (int)$r->pick([0, 15, 30, 45]);
                $w->mem['cron5'] = sprintf('%02d:%02d', $h, $m);
                $node = $w->fs->get('/etc/crontab');
                $base = $node !== null ? (string)($node['c'] ?? '') : "SHELL=/bin/sh\n";
                $w->mkfile('/etc/crontab', $base . sprintf("%d %d\t* * *\troot\t/usr/local/bin/zaloha.sh\n", $m, $h), 0644, 'root', 'root', $w->now - 86400);
            },
            'answer' => static fn(Lab57World $w): string => (string)($w->mem['cron5'] ?? '00:00'),
            'answer_format' => 'čas HH:MM (např. 02:30)',
            'solution' => static fn(Lab57World $w): array => ['cat /etc/crontab', 'answer ' . (string)($w->mem['cron5'] ?? '00:00')],
            'learn' => 'V crontabu je pořadí polí minuta hodina den měsíc den_v_týdnu. „30 2 * * *" tedy znamená každý den ve 02:30.',
        ],
        [
            'id' => 'cron-6', 'type' => 'code', 'title' => 'Detektiv v logu', 'difficulty' => 3, 'points' => 200, 'minutes' => 10,
            'story' => 'Cron posílá výstup svých úloh poštou uživateli. Jedna úloha do zprávy zapisuje kód – najdi ho.',
            'task' => 'Nech úlohy proběhnout (timewarp), najdi v logu, že cron něco spustil, a přečti kód z pošty správce.',
            'commands' => ['cat', 'timewarp', 'grep', 'sudo', 'submit'],
            'hints' => ['Podívej se, co je naplánováno: cat /etc/cron.d/hlaseni. Pak posuň čas: timewarp +7m.', 'Že úloha proběhla, ověříš: sudo grep CRON /var/log/syslog.', 'Cron pošle výstup poštou správci: sudo cat /var/mail/root – v ní je kód.'],
            'build' => static function (Lab57World $w, Lab57Rng $r): void {
                lab58_cron_prepare($w);
                $w->mkfile('/root/kod.txt', $w->code() . "\n", 0600, 'root', 'root', $w->now - 86400);
                $w->mkfile('/usr/local/bin/hlaseni.sh', "#!/bin/bash\necho \"Denni hlaseni serveru, kod: \$(cat /root/kod.txt)\"\n", 0755, 'root', 'root', $w->now - 86400);
                $w->mkfile('/etc/cron.d/hlaseni', "*/7 * * * * root /usr/local/bin/hlaseni.sh\n", 0644, 'root', 'root', $w->now - 86400);
            },
            'solution' => static fn(Lab57World $w): array => ['cat /etc/cron.d/hlaseni', 'cat /usr/local/bin/hlaseni.sh', 'timewarp +7m', 'sudo grep CRON /var/log/syslog', 'sudo cat /var/mail/root', 'submit ' . $w->code()],
            'learn' => 'Když úloha cronu něco vypíše, cron to pošle e-mailem jejímu uživateli (soubor /var/mail/<uživatel>). Proto se výstup úloh často přesměrovává do logu – jinak plní schránku.',
        ],
    ];
}

lab58_register_pack(
    [
        'id' => 'cron', 'title' => 'Plánovač cron', 'description' => 'Automatické úlohy: čtení cizích skriptů, plánování, oprava chyb a posun času.',
        'order' => 66, 'classes' => ['class_3a', 'class_4a'], 'unlock' => 'sequential',
        'badge' => ['id' => 'planovac', 'label' => 'Plánovač', 'icon' => '⏰'], 'icon' => '⏰', 'tone' => 'violet', 'inspired' => 've stylu OverTheWire Bandit 21–24',
    ],
    'lab58_levels_cron'
);
