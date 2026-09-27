<?php

declare(strict_types=1);

if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * v52 · Tutorial Mode
 * Kurz, kalendář naplánovaných hodin, témata a programy jako jeden krokový průvodce.
 * Data lekcí se neduplikují – čtou se z kurikula (lessons_*, school_year.php, knowledgebase).
 */

const TUT52_VERSION = '52.0';

function tut52_family(string $classId, array $module): string
{
    return subject_family($module) === 'graphics' ? 'graphics' : 'networks';
}

function tut52_clean_title(string $title): string
{
    return trim((string)preg_replace('/^Lekce\s+\d+\s*·\s*/u', '', $title));
}

/** Kalendář: číslo lekce → datum naplánované hodiny (první výskyt). */
function tut52_lesson_dates(array $schoolYear, string $classId): array
{
    $out = [];
    foreach (adaptive_school_year_rows($schoolYear, $classId) as $row) {
        if (!is_array($row) || (string)($row['status'] ?? '') !== 'teaching') continue;
        $n = (int)($row['lesson_number'] ?? 0);
        if ($n > 0 && !isset($out[$n])) $out[$n] = (string)($row['date'] ?? '');
    }
    return $out;
}

/** Jednotný seznam 28 lekcí se stavem, postupem, tématy, programy a datem. */
function tut52_lessons(string $classId, array $module, array $nextLessons, array $extendedLessons, array $schoolYear, ?array $completedTest): array
{
    $family = tut52_family($classId, $module);
    $dates = tut52_lesson_dates($schoolYear, $classId);
    $lessons = [];

    $primaryDone = learning_primary_block_complete($classId);
    $lessons[1] = [
        'number' => 1, 'id' => 'primary', 'kind' => 'primary',
        'title' => tr('Start: diagnostika a první blok'),
        'goal' => (string)($module['lesson_note'] ?? ''),
        'topics' => $family === 'graphics' ? learning_graphics_core_topics() : array_slice(array_keys((array)($module['knowledgebase'] ?? [])), 0, 4),
        'schedule' => [], 'steps' => [],
        'done' => $primaryDone, 'unlocked' => true, 'done_steps' => $primaryDone ? 1 : 0, 'total_steps' => 1,
    ];

    $next = is_array($nextLessons[$classId] ?? null) ? $nextLessons[$classId] : null;
    if ($next) {
        $steps = array_values(array_filter((array)($next['steps'] ?? []), 'is_array'));
        $progress = learning_next_lesson_progress($classId, (string)($next['id'] ?? 'next'), $steps);
        $lessons[2] = [
            'number' => 2, 'id' => (string)($next['id'] ?? 'next'), 'kind' => 'next',
            'title' => tut52_clean_title((string)($next['title'] ?? tr('Lekce {n}', ['n' => 2]))), 'goal' => (string)($next['goal'] ?? ''),
            'topics' => array_values((array)($next['knowledge'] ?? [])), 'schedule' => (array)($next['schedule'] ?? []), 'steps' => $steps,
            'done' => learning_next_lesson_complete($classId, $next), 'unlocked' => $primaryDone,
            'done_steps' => count(array_filter($progress)), 'total_steps' => max(1, count($steps)), 'progress' => $progress,
        ];
    }
    foreach ((array)($extendedLessons[$classId] ?? []) as $lesson) {
        if (!is_array($lesson)) continue;
        $n = (int)($lesson['number'] ?? 0);
        if ($n < 3) continue;
        $steps = array_values(array_filter((array)($lesson['steps'] ?? []), 'is_array'));
        $progress = learning_course_lesson_progress($classId, $lesson);
        $lessons[$n] = [
            'number' => $n, 'id' => (string)($lesson['id'] ?? ''), 'kind' => 'course',
            'title' => tut52_clean_title((string)($lesson['title'] ?? tr('Lekce {n}', ['n' => $n]))), 'goal' => (string)($lesson['goal'] ?? ''),
            'topics' => array_values((array)($lesson['knowledge'] ?? [])), 'schedule' => (array)($lesson['schedule'] ?? []), 'steps' => $steps,
            'worksheet' => (array)($lesson['worksheet'] ?? []),
            'done' => learning_course_lesson_complete($classId, $lesson),
            'unlocked' => learning_course_lesson_unlocked($classId, $n, $nextLessons, $extendedLessons),
            'done_steps' => count(array_filter($progress)), 'total_steps' => max(1, count($steps)), 'progress' => $progress,
        ];
    }
    ksort($lessons);
    foreach ($lessons as $n => &$l) {
        $l['date'] = $dates[$n] ?? '';
        $l['tools'] = tut52_lesson_tools($family, $l);
        $l['status'] = $l['done'] ? 'done' : ($l['unlocked'] ? 'open' : 'locked');
    }
    unset($l);
    $currentSet = false;
    foreach ($lessons as $n => &$l) {
        if (!$currentSet && $l['status'] === 'open') { $l['status'] = 'current'; $currentSet = true; }
    }
    unset($l);
    return $lessons;
}

function tut52_current_lesson(array $lessons): ?array
{
    foreach ($lessons as $l) if ($l['status'] === 'current') return $l;
    return null;
}

// ---------------------------------------------------------------------------
// Témata → animovaná ukázka + interaktivní úkol
// ---------------------------------------------------------------------------

/** Každé téma kurikula se namapuje na jednu z animovaných scén. */
function tut52_scene(string $topic, string $family): string
{
    $t = strtolower($topic);
    $rules = $family === 'graphics' ? [
        'contrast' => 'contrast', 'a11y' => 'forms', 'form' => 'forms', 'accessible' => 'contrast',
        'typograph' => 'typography', 'type' => 'typography',
        'hierarch' => 'hierarchy', 'visual-story' => 'hierarchy', 'content-first' => 'hierarchy', 'anatomy' => 'hierarchy',
        'raster' => 'rastervector', 'vector' => 'rastervector',
        'export' => 'export', 'preflight' => 'export', 'production' => 'export', 'asset' => 'export',
        'crop' => 'crop', 'image' => 'crop', 'photo' => 'crop', 'art-direction' => 'crop',
        'responsive' => 'responsive', 'web-layout' => 'responsive',
        'component' => 'components', 'design-system' => 'components', 'ui-kit' => 'components',
        'cta' => 'cta', 'microcopy' => 'cta',
        'color' => 'palette', 'palette' => 'palette', 'harmony' => 'palette', 'icon' => 'palette', 'identity' => 'palette', 'brand' => 'palette',
        'spacing' => 'grid', 'layout' => 'grid', 'grid' => 'grid', 'composition' => 'grid', 'rhythm' => 'grid',
        'critique' => 'hierarchy', 'feedback' => 'hierarchy', 'template' => 'grid',
    ] : [
        'dhcp' => 'dhcp', 'dns' => 'dns', 'ipv6' => 'ip', 'subnet' => 'ip', 'vlsm' => 'ip', 'ip-' => 'ip', 'addressing' => 'ip',
        'port' => 'ports', 'service-matrix' => 'ports',
        'ssh' => 'ssh', 'sftp' => 'ssh', 'https' => 'https', 'tls' => 'https', 'web-service' => 'https',
        'firewall' => 'firewall', 'security' => 'firewall', 'harden' => 'firewall',
        'journal' => 'logs', 'log' => 'logs', 'monitor' => 'logs',
        'systemd' => 'systemd', 'process' => 'systemd', 'service-debug' => 'systemd',
        'permission' => 'permissions', 'users' => 'permissions',
        'filesystem' => 'filesystem', 'linux-file' => 'filesystem',
        'bash' => 'bash', 'shell' => 'bash', 'cron' => 'bash',
        'cidr' => 'ip', 'vlsm' => 'ip',
        'binding' => 'ports', 'bind' => 'ports', 'listen' => 'ports',
        'diagnostic' => 'logs', 'evidence' => 'logs',
        'tcp' => 'packet', 'handshake' => 'packet',
        'packet' => 'packet', 'arp' => 'packet', 'icmp' => 'routing',
        'routing' => 'routing', 'nat' => 'routing', 'vlan' => 'routing', 'troubleshoot' => 'routing',
    ];
    foreach ($rules as $needle => $scene) if (str_contains($t, $needle)) return $scene;
    return $family === 'graphics' ? 'hierarchy' : 'packet';
}

/** Metadata scény: název, co ukazuje, program, interaktivní úkol. */
function tut52_scene_meta(string $scene): array
{
    $m = [
        // Sítě a OS
        'dns' => ['title' => 'Jak DNS najde adresu', 'caption' => ['Klient se ptá resolveru na jméno.', 'Resolver se ptá kořenového a TLD serveru.', 'Autoritativní server vrátí záznam A.', 'Klient se připojí na IP adresu.']],
        'dhcp' => ['title' => 'DHCP: Discover → Offer → Request → Ack', 'caption' => ['Discover: klient hledá server (broadcast).', 'Offer: server nabídne adresu.', 'Request: klient si adresu vyžádá.', 'Ack: server zápůjčku potvrdí.']],
        'ip' => ['title' => 'IP adresa a maska', 'caption' => ['Adresa má 32 bitů.', 'Maska /24 odděluje síť…', '…od části pro zařízení.', 'Stejná síť = komunikace bez brány.']],
        'ports' => ['title' => 'Porty: dveře služeb', 'caption' => ['Paket míří na IP adresu serveru.', 'Port určuje konkrétní službu.', 'Zavřený port spojení odmítne.', 'ss -tulpn ukáže, kdo poslouchá.']],
        'routing' => ['title' => 'Cesta paketu přes bránu', 'caption' => ['Cíl mimo síť → výchozí brána.', 'Každý router sníží TTL o 1.', 'traceroute ukáže jednotlivé skoky.', 'Odpověď se vrací zpět.']],
        'ssh' => ['title' => 'SSH klíče', 'caption' => ['Klient má privátní klíč – nikdy ho nesdílí.', 'Server zná veřejný klíč (authorized_keys).', 'Server pošle výzvu, klient ji podepíše.', 'Šifrovaný tunel je otevřený.']],
        'https' => ['title' => 'HTTPS a TLS', 'caption' => ['Prohlížeč pozdraví server.', 'Server pošle certifikát.', 'Dohodnou se na klíči.', 'Data putují šifrovaně.']],
        'firewall' => ['title' => 'Firewall a pravidla', 'caption' => ['Paket prochází pravidly shora dolů.', 'První shoda rozhodne.', 'Povolený provoz projde.', 'Ostatní se zahodí (default deny).']],
        'logs' => ['title' => 'Logy jako časová osa', 'caption' => ['Log má čas, zdroj, závažnost a zprávu.', 'Filtruj podle služby (-u).', 'Zúži časové okno (--since).', 'Chyba vyskočí ze šumu.']],
        'systemd' => ['title' => 'Život služby v systemd', 'caption' => ['inactive → start', 'activating → active (running)', 'Chyba → failed', 'Restart a kontrola status + journal.']],
        'permissions' => ['title' => 'Oprávnění rwx', 'caption' => ['Vlastník · skupina · ostatní.', 'r = 4, w = 2, x = 1.', '6 = rw-, 4 = r--, 0 = ---', 'chmod 640 soubor.conf']],
        'filesystem' => ['title' => 'Linux: strom adresářů', 'caption' => ['Vše začíná v kořeni /.', '/etc = konfigurace.', '/var/log = logy.', 'cd a ls -la tě provedou.']],
        'bash' => ['title' => 'Skript a cron', 'caption' => ['Skript běží řádek po řádku.', 'Každý příkaz vrací exit code.', 'set -e zastaví při chybě.', 'cron spouští skript podle času.']],
        'packet' => ['title' => 'Zapouzdření paketu', 'caption' => ['Data aplikace (HTTP).', '+ TCP hlavička (porty).', '+ IP hlavička (adresy).', '+ Ethernet rámec (MAC).']],
        // Grafika a web
        'hierarchy' => ['title' => 'Vizuální hierarchie', 'caption' => ['Všechno stejně velké = nic nevyniká.', 'Nadpis dostane velikost a váhu.', 'Klíčová informace je druhá.', 'CTA uzavírá pořadí čtení 1 → 2 → 3.']],
        'contrast' => ['title' => 'Kontrast textu', 'caption' => ['Světlý text na světlém pozadí je nečitelný.', 'Poměr kontrastu roste…', '…nad 4,5 : 1 pro běžný text (WCAG AA).', 'Kontrast je funkce, ne dekorace.']],
        'typography' => ['title' => 'Typografický systém', 'caption' => ['Nadpis, podnadpis, text.', 'Stupnice velikostí (1,25×).', 'Řádkování 1,4–1,6.', 'Délka řádku 45–75 znaků.']],
        'grid' => ['title' => 'Mřížka a rozestupy', 'caption' => ['Prvky bez systému.', 'Mřížka o 12 sloupcích.', 'Prvky se zarovnají.', 'Stejné rozestupy (8 px systém).']],
        'rastervector' => ['title' => 'Rastr vs. vektor', 'caption' => ['Obě grafiky vypadají stejně.', 'Přiblížení 800 %…', 'Rastr ukáže pixely.', 'Vektor zůstane ostrý.']],
        'export' => ['title' => 'Správný export', 'caption' => ['Fotka → JPG / WEBP.', 'Logo a ikony → SVG.', 'Průhlednost → PNG.', 'Tisk → PDF (CMYK, spadávka).']],
        'crop' => ['title' => 'Ořez a kompozice fotky', 'caption' => ['Původní fotka.', 'Pravidlo třetin.', 'Ořez na formát 4 : 5.', 'Ořez na banner 16 : 9.']],
        'responsive' => ['title' => 'Responzivní layout', 'caption' => ['Desktop: 3 sloupce.', 'Tablet: 2 sloupce.', 'Mobil: 1 sloupec.', 'Obsah zůstává ve stejném pořadí.']],
        'components' => ['title' => 'Komponenta a její stavy', 'caption' => ['Výchozí tlačítko.', 'Hover.', 'Focus – viditelný rámeček.', 'Disabled.']],
        'forms' => ['title' => 'Přístupný formulář', 'caption' => ['Každé pole má popisek.', 'Chyba se ukáže u pole.', 'Chyba říká, jak ji opravit.', 'Úspěch je potvrzený.']],
        'cta' => ['title' => 'CTA a microcopy', 'caption' => ['„Klikni zde“ nic neříká.', 'Sloveso + přínos.', 'Kontrast a velikost.', 'Jedno hlavní CTA na obrazovku.']],
        'palette' => ['title' => 'Paleta a identita', 'caption' => ['Primární barva.', 'Neutrální tóny.', 'Akcent jen pro důležité.', 'Konzistentní ikony a tvary.']],
    ];
    return $m[$scene] ?? $m['hierarchy'];
}

/** Interaktivní úkol ke scéně (vyhodnocuje se v prohlížeči, body se ukládají na server). */
function tut52_exercise(string $scene): array
{
    $ex = [
        'dns' => ['type' => 'order', 'prompt' => 'Seřaď kroky překladu jména www.skola.cz.', 'items' => ['Klient se zeptá resolveru', 'Resolver se zeptá kořenového serveru', 'TLD server .cz odkáže na autoritativní server', 'Autoritativní server vrátí záznam A', 'Klient se připojí na IP adresu']],
        'dhcp' => ['type' => 'order', 'prompt' => 'Seřaď zprávy DHCP (DORA).', 'items' => ['DHCPDISCOVER', 'DHCPOFFER', 'DHCPREQUEST', 'DHCPACK']],
        'ip' => ['type' => 'match', 'prompt' => 'Přiřaď prefix k masce.', 'pairs' => [['/8', '255.0.0.0'], ['/16', '255.255.0.0'], ['/24', '255.255.255.0'], ['/30', '255.255.255.252']]],
        'ports' => ['type' => 'match', 'prompt' => 'Přiřaď službu k výchozímu portu.', 'pairs' => [['SSH', '22'], ['DNS', '53'], ['HTTP', '80'], ['HTTPS', '443'], ['DHCP server', '67']]],
        'routing' => ['type' => 'terminal', 'prompt' => 'Zjisti výchozí bránu a cestu k 1.1.1.1.', 'tasks' => [
            ['goal' => 'Vypiš směrovací tabulku', 'accept' => '^(ip r(oute)?( show)?|route -n|netstat -rn?)$', 'hint' => 'ip r', 'output' => "default via 192.168.50.1 dev eth0\n192.168.50.0/24 dev eth0 proto kernel src 192.168.50.88"],
            ['goal' => 'Ukaž cestu přes routery k 1.1.1.1', 'accept' => '^(traceroute|tracert|mtr)\s+1\.1\.1\.1$', 'hint' => 'traceroute 1.1.1.1', 'output' => " 1  192.168.50.1  1.2 ms\n 2  10.10.0.1  4.8 ms\n 3  1.1.1.1  11.3 ms"],
        ]],
        'ssh' => ['type' => 'terminal', 'prompt' => 'Připrav přihlášení klíčem na server 192.168.50.30.', 'tasks' => [
            ['goal' => 'Vygeneruj klíč typu ed25519', 'accept' => '^ssh-keygen\s+-t\s+ed25519', 'hint' => 'ssh-keygen -t ed25519', 'output' => "Generating public/private ed25519 key pair.\nYour public key has been saved in /home/student/.ssh/id_ed25519.pub"],
            ['goal' => 'Nahraj veřejný klíč na server (uživatel admin)', 'accept' => '^ssh-copy-id\s+(-i\s+\S+\s+)?admin@192\.168\.50\.30$', 'hint' => 'ssh-copy-id admin@192.168.50.30', 'output' => "Number of key(s) added: 1"],
            ['goal' => 'Přihlas se na server', 'accept' => '^ssh\s+admin@192\.168\.50\.30$', 'hint' => 'ssh admin@192.168.50.30', 'output' => "Welcome to Ubuntu 24.04 LTS\nadmin@srv:~$"],
        ]],
        'https' => ['type' => 'terminal', 'prompt' => 'Ověř, že web odpovídá přes HTTPS.', 'tasks' => [
            ['goal' => 'Zobraz jen hlavičky odpovědi https://intranet.skola.cz', 'accept' => '^curl\s+(-I|--head)\s+https://intranet\.skola\.cz/?$', 'hint' => 'curl -I https://intranet.skola.cz', 'output' => "HTTP/2 200\nserver: nginx\nstrict-transport-security: max-age=31536000"],
        ]],
        'firewall' => ['type' => 'terminal', 'prompt' => 'Povol SSH a zkontroluj firewall (ufw).', 'tasks' => [
            ['goal' => 'Zobraz stav firewallu', 'accept' => '^sudo\s+ufw\s+status( verbose)?$', 'hint' => 'sudo ufw status', 'output' => "Status: active\nTo      Action  From\n80/tcp  ALLOW   Anywhere"],
            ['goal' => 'Povol port 22/tcp', 'accept' => '^sudo\s+ufw\s+allow\s+(22(/tcp)?|ssh|OpenSSH)$', 'hint' => 'sudo ufw allow 22/tcp', 'output' => "Rule added"],
        ]],
        'logs' => ['type' => 'terminal', 'prompt' => 'Najdi chybu služby nginx za poslední hodinu.', 'tasks' => [
            ['goal' => 'Zobraz log jen pro službu nginx', 'accept' => '^(sudo\s+)?journalctl\s+-u\s+nginx(\.service)?$', 'hint' => 'journalctl -u nginx', 'output' => "Sep 16 14:31 srv nginx[812]: started\nSep 16 14:52 srv nginx[812]: bind() to 0.0.0.0:80 failed (98: Address already in use)"],
            ['goal' => 'Omez výpis na poslední hodinu', 'accept' => '^(sudo\s+)?journalctl\s+-u\s+nginx(\.service)?\s+--since\s+("|\')?(1 hour ago|-1h|1h ago)("|\')?$', 'hint' => 'journalctl -u nginx --since "1 hour ago"', 'output' => "Sep 16 14:52 srv nginx[812]: bind() to 0.0.0.0:80 failed (98: Address already in use)"],
        ]],
        'systemd' => ['type' => 'terminal', 'prompt' => 'Oprav nefunkční službu nginx.', 'tasks' => [
            ['goal' => 'Zjisti stav služby', 'accept' => '^(sudo\s+)?systemctl\s+status\s+nginx(\.service)?$', 'hint' => 'systemctl status nginx', 'output' => "● nginx.service - A high performance web server\n   Active: failed (Result: exit-code)"],
            ['goal' => 'Restartuj službu', 'accept' => '^sudo\s+systemctl\s+restart\s+nginx(\.service)?$', 'hint' => 'sudo systemctl restart nginx', 'output' => ""],
            ['goal' => 'Nastav spouštění po startu', 'accept' => '^sudo\s+systemctl\s+enable\s+nginx(\.service)?$', 'hint' => 'sudo systemctl enable nginx', 'output' => "Created symlink /etc/systemd/system/multi-user.target.wants/nginx.service"],
        ]],
        'permissions' => ['type' => 'bits', 'prompt' => 'Nastav oprávnění 640: vlastník čte a zapisuje, skupina čte, ostatní nic.', 'target' => '640'],
        'filesystem' => ['type' => 'match', 'prompt' => 'Kde v Linuxu co najdeš?', 'pairs' => [['/etc', 'konfigurace'], ['/var/log', 'logy'], ['/home', 'domovské složky uživatelů'], ['/usr/bin', 'programy'], ['/tmp', 'dočasné soubory']]],
        'bash' => ['type' => 'order', 'prompt' => 'Seřaď řádky bezpečného zálohovacího skriptu.', 'items' => ['#!/usr/bin/env bash', 'set -euo pipefail', 'SRC=/var/www', 'tar -czf /backup/web-$(date +%F).tar.gz "$SRC"', 'echo "Záloha hotová"']],
        'packet' => ['type' => 'order', 'prompt' => 'Seřaď vrstvy od dat aplikace po rámec na kabelu.', 'items' => ['Data (HTTP)', 'TCP segment (porty)', 'IP paket (adresy)', 'Ethernet rámec (MAC)', 'Bity na médiu']],
        'hierarchy' => ['type' => 'sizes', 'prompt' => 'Nastav velikosti tak, aby pořadí čtení bylo Nadpis → Datum → CTA a nadpis byl aspoň 2× větší než text.'],
        'contrast' => ['type' => 'contrast', 'prompt' => 'Uprav barvy textu a pozadí, aby kontrast dosáhl aspoň 4,5 : 1 (WCAG AA).'],
        'typography' => ['type' => 'match', 'prompt' => 'Přiřaď typografické pravidlo k hodnotě.', 'pairs' => [['Řádkování běžného textu', '1,5'], ['Délka řádku', '45–75 znaků'], ['Min. velikost textu na webu', '16 px'], ['Poměr typografické stupnice', '1,25']]],
        'grid' => ['type' => 'order', 'prompt' => 'Seřaď postup stavby layoutu.', 'items' => ['Obsah a jeho priority', 'Mřížka a okraje', 'Rozmístění bloků', 'Rozestupy podle systému', 'Kontrola zarovnání']],
        'rastervector' => ['type' => 'match', 'prompt' => 'Rastr nebo vektor?', 'pairs' => [['Fotografie z mobilu', 'rastr · JPG'], ['Logo do tiskárny i na web', 'vektor · PDF/AI'], ['Ikona na web', 'vektor · SVG'], ['Screenshot obrazovky', 'rastr · PNG']]],
        'export' => ['type' => 'match', 'prompt' => 'Přiřaď formát k použití.', 'pairs' => [['Fotka na web', 'JPG / WEBP'], ['Logo na web', 'SVG'], ['Obrázek s průhledností', 'PNG'], ['Plakát do tiskárny', 'PDF']]],
        'crop' => ['type' => 'match', 'prompt' => 'Přiřaď formát ořezu k použití.', 'pairs' => [['Instagram příspěvek', '4 : 5'], ['Web banner', '16 : 9'], ['Profilová fotka', '1 : 1'], ['Příběh / reels', '9 : 16']]],
        'responsive' => ['type' => 'match', 'prompt' => 'Kolik sloupců karet zvolíš?', 'pairs' => [['Desktop 1280 px', '3 sloupce'], ['Tablet 768 px', '2 sloupce'], ['Mobil 375 px', '1 sloupec']]],
        'components' => ['type' => 'match', 'prompt' => 'Přiřaď stav tlačítka k situaci.', 'pairs' => [['Myš nad tlačítkem', 'hover'], ['Ovládání klávesnicí (Tab)', 'focus'], ['Akce není dostupná', 'disabled'], ['Právě stisknuto', 'active']]],
        'forms' => ['type' => 'order', 'prompt' => 'Seřaď, co uživatel potřebuje u pole formuláře.', 'items' => ['Popisek pole (label)', 'Nápověda k formátu', 'Chybová zpráva u pole', 'Návod, jak chybu opravit', 'Potvrzení úspěchu']],
        'cta' => ['type' => 'match', 'prompt' => 'Nahraď slabé CTA konkrétním.', 'pairs' => [['Klikni zde', 'Stáhnout rozvrh'], ['Odeslat', 'Přihlásit se na workshop'], ['Více', 'Zobrazit program akce']]],
        'palette' => ['type' => 'match', 'prompt' => 'Přiřaď roli barvy.', 'pairs' => [['Primární', 'hlavní akce a značka'], ['Neutrální', 'text a pozadí'], ['Akcent', 'jen důležité detaily'], ['Chybová', 'upozornění a chyby']]],
    ];
    return $ex[$scene] ?? $ex['hierarchy'];
}

/** Textové řešení úkolu pro učitele (projekce a příprava). */
function tut52_exercise_solution(array $ex): string
{
    return match ((string)$ex['type']) {
        'order' => 'Správné pořadí: ' . implode(' → ', array_map('strval', (array)$ex['items'])) . '.',
        'match' => 'Dvojice: ' . implode(' · ', array_map(static fn(array $p): string => $p[0] . ' = ' . $p[1], (array)$ex['pairs'])) . '.',
        'terminal' => 'Příkazy: ' . implode(' · ', array_map(static fn(array $t): string => $t['hint'], (array)$ex['tasks'])) . '.',
        'bits' => 'Cílová hodnota: chmod ' . (string)$ex['target'] . ' (vlastník rw-, skupina r--, ostatní nic).',
        'sizes' => 'Nadpis je největší, datum druhé, CTA text nejmenší; nadpis alespoň 2× větší než text CTA.',
        'contrast' => 'Kontrast textu a pozadí musí být alespoň 4,5 : 1 (WCAG AA pro běžný text).',
        default => 'Úkol se vyhodnocuje automaticky.',
    };
}

// ---------------------------------------------------------------------------
// Programy, klávesové zkratky, příkazy a pojmy
// ---------------------------------------------------------------------------

function tut52_tools(string $family): array
{
    if ($family === 'graphics') {
        return [
            'canva' => ['name' => 'Canva', 'kind' => 'Online grafický editor', 'use' => 'Rychlé plakáty, sociální příspěvky a prezentace ze šablon.', 'where' => 'canva.com · přihlášení školním účtem', 'match' => '/canva|plakát|poster|šablon|post/iu',
                'shortcuts' => [['T', 'Textové pole'], ['R', 'Obdélník'], ['C', 'Kruh'], ['L', 'Čára'], ['Ctrl+D', 'Duplikovat prvek'], ['Ctrl+G', 'Seskupit'], ['Ctrl+Z', 'Zpět']]],
            'figma' => ['name' => 'Figma', 'kind' => 'Návrh UI a webu', 'use' => 'Wireframy, komponenty, responzivní layouty a prototypy.', 'where' => 'figma.com · desktopová aplikace nebo prohlížeč', 'match' => '/figma|\bui\b|\bweb|komponent|wireframe|landing|responz|prototyp|layout/iu',
                'shortcuts' => [['V', 'Přesun'], ['F', 'Frame'], ['R', 'Obdélník'], ['T', 'Text'], ['Shift+A', 'Auto layout'], ['Ctrl+Alt+K', 'Vytvořit komponentu'], ['Ctrl+G', 'Seskupit'], ['Shift+1', 'Přiblížit na vše'], ['Ctrl+/', 'Rychlé akce']]],
            'photoshop' => ['name' => 'Adobe Photoshop', 'kind' => 'Úprava rastrové grafiky', 'use' => 'Retuš, ořez, fotomontáž a export obrázků pro web.', 'where' => 'Počítače v učebně · alternativa GIMP / Photopea', 'match' => '/foto|obraz(?!ovk)|\bimage|\bcrop|ořez|retuš|art direction|fotomontáž/iu',
                'shortcuts' => [['V', 'Přesun'], ['M', 'Výběr'], ['C', 'Oříznutí'], ['B', 'Štětec'], ['T', 'Text'], ['Ctrl+T', 'Volná transformace'], ['Ctrl+J', 'Duplikovat vrstvu'], ['Ctrl+Shift+Alt+W', 'Exportovat jako'], ['Ctrl+0', 'Přizpůsobit obrazovce']]],
            'illustrator' => ['name' => 'Adobe Illustrator', 'kind' => 'Vektorová grafika', 'use' => 'Loga, ikony a ilustrace, které jsou ostré v každé velikosti.', 'where' => 'Počítače v učebně · alternativa Inkscape', 'match' => '/logo|ikon|icon|vektor|vector|identit|brand/iu',
                'shortcuts' => [['V', 'Výběr'], ['A', 'Přímý výběr'], ['P', 'Pero'], ['T', 'Text'], ['M', 'Obdélník'], ['Shift+M', 'Tvarovač'], ['Ctrl+G', 'Seskupit'], ['Ctrl+Shift+O', 'Převést text na křivky']]],
            'vscode' => ['name' => 'Visual Studio Code', 'kind' => 'Editor kódu', 'use' => 'HTML a CSS pro webové stránky a komponenty.', 'where' => 'code.visualstudio.com · zdarma', 'match' => '/html|css|web|kód|landing|responz|komponent|formulář/iu',
                'shortcuts' => [['Ctrl+P', 'Rychle otevřít soubor'], ['Ctrl+Shift+P', 'Paleta příkazů'], ['Shift+Alt+F', 'Naformátovat kód'], ['Ctrl+/', 'Zakomentovat řádek'], ['Alt+↑ / Alt+↓', 'Posunout řádek'], ['Ctrl+D', 'Vybrat další výskyt'], ['Ctrl+`', 'Terminál']]],
            'devtools' => ['name' => 'Nástroje prohlížeče (DevTools)', 'kind' => 'Kontrola webu', 'use' => 'Zkontrolovat layout, kontrast, responzivitu a přístupnost.', 'where' => 'Chrome, Edge nebo Firefox · klávesa F12', 'match' => '/web|responz|přístup|a11y|kontrast|formulář|landing/iu',
                'shortcuts' => [['F12', 'Otevřít DevTools'], ['Ctrl+Shift+C', 'Prozkoumat prvek'], ['Ctrl+Shift+M', 'Režim zařízení (mobil)'], ['Ctrl+Shift+P', 'Příkazy DevTools']]],
        ];
    }
    return [
        'terminal' => ['name' => 'Terminál a Bash', 'kind' => 'Příkazová řádka Linuxu', 'use' => 'Většina správy serveru: soubory, služby, logy, síť.', 'where' => 'Linux VM v učebně · Windows: WSL nebo SSH', 'match' => '/linux|bash|shell|\bcli\b|terminál|filesystem|soubor|cron|journalctl|systemctl/iu',
            'shortcuts' => [['Tab', 'Doplnit příkaz nebo cestu'], ['↑', 'Předchozí příkaz'], ['Ctrl+R', 'Hledat v historii'], ['Ctrl+C', 'Přerušit běžící příkaz'], ['Ctrl+L', 'Vyčistit obrazovku'], ['Ctrl+A / Ctrl+E', 'Začátek / konec řádku'], ['Ctrl+D', 'Odhlásit / konec vstupu']],
            'commands' => [['pwd', 'kde jsem'], ['ls -la', 'výpis včetně skrytých a oprávnění'], ['cd /etc', 'přejít do složky'], ['cat soubor', 'zobrazit soubor'], ['less soubor', 'listovat souborem'], ['grep -i chyba soubor', 'hledat text'], ['sudo příkaz', 'spustit jako správce']]],
        'network' => ['name' => 'Síťové nástroje', 'kind' => 'Diagnostika sítě', 'use' => 'Adresa, brána, DNS, dostupnost a porty – vždy nejdřív důkaz.', 'where' => 'Linux (iproute2) · Windows (cmd/PowerShell)', 'match' => '/\b(ip|ipv4|ipv6|dns|dhcp|ports?|rout\w*|ping|traceroute|vlan|nat|packet|subnet\w*|vlsm)\b|síť/iu',
            'shortcuts' => [],
            'commands' => [['ip a', 'adresy rozhraní'], ['ip r', 'směrovací tabulka a brána'], ['ping -c 4 1.1.1.1', 'dostupnost hostitele'], ['traceroute 1.1.1.1', 'cesta přes routery'], ['dig skola.cz', 'DNS dotaz'], ['ss -tulpn', 'naslouchající porty'], ['ipconfig /all', 'Windows: konfigurace'], ['Test-NetConnection srv -Port 22', 'PowerShell: test portu']]],
        'systemd' => ['name' => 'systemd a journalctl', 'kind' => 'Služby a logy', 'use' => 'Spouštění, restart a diagnostika služeb podle logů.', 'where' => 'Linux server', 'match' => '/systemd|služb|service|proces|journal|\blogy?\b|\blogs?\b|monitor|nginx|apache/iu',
            'shortcuts' => [],
            'commands' => [['systemctl status nginx', 'stav služby'], ['sudo systemctl restart nginx', 'restart'], ['sudo systemctl enable nginx', 'spouštět po startu'], ['journalctl -u nginx', 'log služby'], ['journalctl -u nginx --since "1 hour ago"', 'log za hodinu'], ['journalctl -f', 'sledovat log živě']]],
        'ssh' => ['name' => 'OpenSSH', 'kind' => 'Vzdálený přístup', 'use' => 'Bezpečné přihlášení a přenos souborů (SSH, SFTP, SCP).', 'where' => 'Linux/macOS/Windows 10+ vestavěně · alternativa PuTTY, WinSCP', 'match' => '/ssh|sftp|scp|vzdálen|klíč|key/iu',
            'shortcuts' => [['~.', 'Ukončit zamrzlé SSH spojení']],
            'commands' => [['ssh admin@192.168.50.30', 'přihlášení'], ['ssh-keygen -t ed25519', 'nový klíč'], ['ssh-copy-id admin@server', 'nahrát veřejný klíč'], ['sftp admin@server', 'přenos souborů'], ['scp soubor admin@server:/tmp/', 'kopírovat soubor']]],
        'firewall' => ['name' => 'Firewall (ufw / nftables)', 'kind' => 'Bezpečnost', 'use' => 'Povolit jen potřebné porty a ověřit, co je otevřené.', 'where' => 'Ubuntu/Debian: ufw', 'match' => '/firewall|harden|bezpeč|security|stateful/iu',
            'shortcuts' => [],
            'commands' => [['sudo ufw status verbose', 'stav a pravidla'], ['sudo ufw allow 22/tcp', 'povolit SSH'], ['sudo ufw deny 23/tcp', 'zakázat telnet'], ['sudo ufw enable', 'zapnout firewall'], ['sudo nft list ruleset', 'pravidla nftables']]],
        'editors' => ['name' => 'nano a vim', 'kind' => 'Úprava souborů v terminálu', 'use' => 'Konfigurační soubory a skripty přímo na serveru.', 'where' => 'Linux server', 'match' => '/bash|skript|cron|konfig|config|shell/iu',
            'shortcuts' => [['Ctrl+O', 'nano: uložit'], ['Ctrl+X', 'nano: konec'], ['Ctrl+W', 'nano: hledat'], ['Ctrl+K', 'nano: vyjmout řádek'], ['i', 'vim: psaní'], ['Esc', 'vim: zpět do příkazů'], [':wq', 'vim: uložit a konec'], [':q!', 'vim: konec bez uložení']],
            'commands' => [['nano /etc/hosts', 'upravit soubor'], ['crontab -e', 'upravit plán úloh'], ['chmod +x zaloha.sh', 'spustitelný skript'], ['chmod 640 app.conf', 'oprávnění rw-r-----']]],
        'wireshark' => ['name' => 'Wireshark', 'kind' => 'Analýza paketů', 'use' => 'Vidět skutečný provoz: DNS dotazy, TCP handshake, chyby.', 'where' => 'wireshark.org · zdarma', 'match' => '/wireshark|packet|paket|\barp\b|\bicmp\b|\btcp\b/iu',
            'shortcuts' => [['Ctrl+E', 'Spustit / zastavit zachytávání'], ['Ctrl+F', 'Najít paket']],
            'commands' => [['dns', 'filtr: jen DNS'], ['tcp.port == 22', 'filtr: SSH'], ['ip.addr == 192.168.50.30', 'filtr: jedna adresa'], ['tcp.flags.syn == 1', 'filtr: začátky spojení']]],
    ];
}

function tut52_glossary(string $family): array
{
    if ($family === 'graphics') {
        return [
            ['CTA', 'Call to action – výzva k akci (tlačítko, odkaz)'], ['UI', 'User interface – uživatelské rozhraní'], ['UX', 'User experience – zkušenost uživatele'],
            ['RGB', 'Barevný model obrazovek (red, green, blue)'], ['CMYK', 'Barevný model tisku (cyan, magenta, yellow, key/black)'], ['HEX', 'Zápis barvy pro web, např. #3056D3'],
            ['DPI / PPI', 'Body / pixely na palec – hustota pro tisk / obrazovku'], ['SVG', 'Vektorový formát pro web'], ['PNG', 'Rastrový formát s průhledností'], ['JPG / WEBP', 'Rastrové formáty pro fotky'],
            ['PDF', 'Formát pro tisk a sdílení dokumentů'], ['WCAG', 'Pravidla přístupnosti webu (kontrast 4,5 : 1 pro text)'], ['px / rem', 'Jednotky velikosti na webu'],
            ['Grid', 'Mřížka pro zarovnání prvků'], ['Wireframe', 'Hrubé schéma rozložení bez grafiky'], ['Spadávka', 'Přesah 3 mm pro tisk'], ['Breakpoint', 'Šířka, kde se mění responzivní layout'],
        ];
    }
    return [
        ['IP', 'Internet Protocol – adresa zařízení v síti'], ['DNS', 'Domain Name System – překlad jmen na IP'], ['DHCP', 'Automatické přidělení adresy, masky, brány a DNS'],
        ['TCP / UDP', 'Transportní protokoly: spolehlivý / rychlý bez potvrzení'], ['SSH', 'Secure Shell – šifrovaný vzdálený přístup (port 22)'], ['SFTP', 'Přenos souborů přes SSH'],
        ['HTTP / HTTPS', 'Web: nešifrovaně (80) / šifrovaně přes TLS (443)'], ['TLS', 'Šifrování spojení a certifikáty'], ['ICMP', 'Diagnostické zprávy (ping, traceroute)'],
        ['NAT', 'Překlad privátních adres na veřejnou'], ['VLAN', 'Logické rozdělení jedné fyzické sítě'], ['ARP', 'Zjištění MAC adresy k IP adrese'], ['MAC', 'Fyzická adresa síťové karty'],
        ['CIDR /24', 'Zápis masky – počet bitů sítě'], ['TTL', 'Počet skoků, než paket zanikne'], ['VM', 'Virtuální počítač'], ['CLI', 'Příkazová řádka'],
        ['systemd', 'Správce služeb v Linuxu'], ['cron', 'Plánovač opakovaných úloh'], ['UFW', 'Uncomplicated Firewall – jednoduchý firewall'], ['root', 'Správce systému s plnými právy'],
    ];
}

/** Programy použité v lekci podle obsahu (název, cíl, kroky, témata). */
function tut52_lesson_tools(string $family, array $lesson): array
{
    $text = implode(' ', array_merge([(string)$lesson['title'], (string)$lesson['goal']], array_map('strval', (array)$lesson['topics']),
        array_map(static fn($s) => (string)($s['title'] ?? '') . ' ' . implode(' ', array_map('strval', (array)($s['tasks'] ?? []))), (array)$lesson['steps'])));
    $out = [];
    foreach (tut52_tools($family) as $key => $tool) if (preg_match($tool['match'], $text)) $out[] = $key;
    if (!$out) $out[] = $family === 'graphics' ? 'canva' : 'terminal';
    return array_slice($out, 0, 4);
}

// ---------------------------------------------------------------------------
// Body z interaktivních úkolů
// ---------------------------------------------------------------------------

function tut52_scores_path(): string
{
    return STORAGE_DIR . '/tutorial_v52_scores.json.php';
}

function tut52_student_scores(string $classId, string $studentKey): array
{
    $all = load_php_json(tut52_scores_path());
    return is_array($all[$classId . '|' . $studentKey] ?? null) ? $all[$classId . '|' . $studentKey] : [];
}

function tut52_handle_score_post(string $classId): never
{
    header('Content-Type: application/json; charset=utf-8');
    $studentKey = adaptive_student_key($classId);
    $exercise = preg_replace('/[^a-z0-9:_-]/i', '', (string)($_POST['exercise'] ?? '')) ?? '';
    $points = max(0, min(3, (int)($_POST['points'] ?? 0)));
    if ($studentKey === '' || $exercise === '' || strlen($exercise) > 120) { http_response_code(422); echo json_encode(['ok' => false]); exit; }
    $key = $classId . '|' . $studentKey;
    $best = 0;
    $row = (array)storage_map_update(tut52_scores_path(), $key, static function (?array $row) use ($exercise, $points, &$best): array {
        $row = $row ?? [];
        $best = max((int)($row[$exercise]['points'] ?? 0), $points);
        $row[$exercise] = ['points' => $best, 'at' => date(DATE_ATOM)];
        return $row;
    });
    // v53: body z úkolů jdou i do peněženky, ze které se platí extra nápovědy.
    if (function_exists('pts53_award')) pts53_award($classId, $studentKey, 'ex:' . $exercise, $best, 'Interaktivní úkol');
    $total = array_sum(array_map(static fn($r) => (int)($r['points'] ?? 0), $row));
    echo json_encode(['ok' => true, 'best' => $best, 'total' => $total], JSON_UNESCAPED_UNICODE);
    exit;
}

function tut52_lesson_points(array $scores, int $lessonNo): array
{
    $sum = 0; $count = 0;
    foreach ($scores as $id => $row) {
        if (str_starts_with((string)$id, 'L' . $lessonNo . ':')) { $sum += (int)($row['points'] ?? 0); $count++; }
    }
    return ['points' => $sum, 'exercises' => $count];
}

// ---------------------------------------------------------------------------
// Pomocné formátování
// ---------------------------------------------------------------------------

/** cs: stejný formát jako dřív (beze změny); en/uk: edu_date/edu_weekday podle jazyka. */
function tut52_cz_date(string $date, bool $withWeekday = true): string
{
    $ts = strtotime($date);
    if (!$ts) return '';
    if (edu_locale() !== 'cs') {
        return $withWeekday ? (edu_weekday($ts) . ' ' . edu_date($ts, 'date')) : edu_date($ts, 'date');
    }
    $days = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];
    return ($withWeekday ? $days[(int)date('w', $ts)] . ' ' : '') . date('j. n. Y', $ts);
}

/** cs: stejný formát jako dřív – nominativ, velké písmeno (beze změny); en/uk: edu_month (jiný jazyk, jiný pád). */
function tut52_month_name(int $month): string
{
    if (edu_locale() !== 'cs') return edu_month($month);
    return ['', 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'][$month] ?? '';
}

function tut52_topic_title(array $modules, string $classId, string $topic): string
{
    return (string)($modules[$classId]['knowledgebase'][$topic]['title'] ?? $topic);
}
