<?php

declare(strict_types=1);
if (basename((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === basename(__FILE__)) { http_response_code(403); exit; }

/**
 * EDUCANET v62 · katalog kompetencí (páteř učení, fáze 1).
 *
 * Strom předmět → oblast → kompetence („Umím …“), úrovně 1–4 (pamatuje / použije / analyzuje / tvoří).
 * Každá kompetence má tagy, podle kterých se k ní automaticky přiřazují aktivity:
 *   cmd:<příkaz>   příkaz Linux Labu u úrovně        pack:<balíček>  balíček úrovní labu
 *   topic:<kb>     téma znalostní báze (test, lekce) bank:<id>       banka otázek (rezerva)
 *   tg:<linka>     linka týmové hry (networks/graphics)
 * Aktivita bez shody se nezapíše a audit ji hlásí jako WARN (ne FAIL). Ruční výjimky: COMP62_OVERRIDES.
 * Pilot: jen třída 3.A (rozhodnutí školy); ostatní třídy tuto vrstvu nevidí a nic se jim nepočítá.
 * Katalog je čistá data bez zápisu do storage.
 */

/** Třídy, kde vrstva běží (rozhodnutí školy: pilot jen 3.A). */
const COMP62_PILOT_CLASSES = ['class_3a'];
/** Retence důkazů: do konce studia, smazání 30 dní po stavu left/archived (rozhodnutí školy). */
const COMP62_RETENTION_GRACE_DAYS = 30;
/** Nejvýš kolik kompetencí dostane jedna aktivita (nejlepší shody podle počtu společných tagů). */
const COMP62_MATCH_LIMIT = 2;
/** Ruční výjimky: „ref“ artefaktu (např. lab:sit-4) → seznam kompetencí. Má přednost před tagy. */
const COMP62_OVERRIDES = [];

function comp62_enabled_for_class(string $classId): bool
{
    return in_array($classId, COMP62_PILOT_CLASSES, true);
}

/** @return array<int,string> úrovně kompetence */
function comp62_levels(): array
{
    return [1 => 'pamatuje', 2 => 'použije', 3 => 'analyzuje', 4 => 'tvoří'];
}

function comp62_level_label(int $level): string
{
    return comp62_levels()[$level] ?? comp62_levels()[1];
}

/**
 * Strom kompetencí. Klíč předmětu → ['label','classes','areas' => [oblast => ['label','competencies' => [id => ['label','level','tags']]]]].
 * @return array<string,array<string,mixed>>
 */
function comp62_catalog(): array
{
    return [
        'os_site' => [
            'label' => 'Operační systémy a sítě',
            'classes' => ['class_3a'],
            'areas' => [
                'site' => ['label' => 'Sítě', 'competencies' => [
                    'net_addressing' => ['label' => 'Umím určit síť, masku a adresu zařízení', 'level' => 2,
                        'tags' => ['topic:ip-addressing', 'topic:subnetting-vlsm', 'topic:ipv6-basics', 'topic:arp']],
                    'net_dns_dhcp' => ['label' => 'Umím vysvětlit DNS a DHCP a ověřit je v praxi', 'level' => 2,
                        'tags' => ['topic:dns', 'topic:dhcp', 'topic:dns-record-types', 'topic:dhcp-reservations', 'topic:dns-dhcp-operations', 'cmd:dig', 'cmd:getent']],
                    'net_diagnose' => ['label' => 'Umím krok za krokem diagnostikovat síťový problém', 'level' => 3,
                        'tags' => ['topic:troubleshooting', 'topic:icmp', 'topic:routing', 'topic:service-debug-chain', 'cmd:ping', 'cmd:traceroute', 'cmd:ip', 'pack:sit', 'tg:networks']],
                    'net_services' => ['label' => 'Umím rozlišit porty a služby a ověřit, co poslouchá', 'level' => 2,
                        'tags' => ['topic:ports', 'topic:service-matrix', 'topic:https', 'cmd:ss', 'cmd:nc', 'cmd:curl']],
                    'net_security' => ['label' => 'Umím popsat základy zabezpečení sítě a firewallu', 'level' => 2,
                        'tags' => ['topic:network-security-basics', 'topic:linux-firewall', 'topic:stateful-firewall', 'topic:nat', 'topic:vlan-basics', 'topic:packet-analysis', 'topic:monitoring-basics']],
                ]],
                'linux' => ['label' => 'Linux', 'competencies' => [
                    'lnx_navigation' => ['label' => 'Umím se pohybovat v shellu a pracovat se soubory', 'level' => 1,
                        'tags' => ['topic:linux-filesystem', 'pack:start', 'pack:quest', 'cmd:pwd', 'cmd:ls', 'cmd:cd', 'cmd:mkdir', 'cmd:cp', 'cmd:mv', 'cmd:rm', 'cmd:touch', 'cmd:find', 'cmd:less', 'cmd:head', 'cmd:file']],
                    'lnx_text' => ['label' => 'Umím filtrovat a zpracovat text v příkazové řádce', 'level' => 2,
                        'tags' => ['pack:golf', 'cmd:grep', 'cmd:sort', 'cmd:uniq', 'cmd:wc', 'cmd:cut', 'cmd:tr', 'cmd:sed', 'cmd:awk']],
                    'lnx_users' => ['label' => 'Umím spravovat uživatele a oprávnění', 'level' => 2,
                        'tags' => ['topic:users-permissions', 'pack:prava', 'cmd:chmod', 'cmd:chown', 'cmd:useradd', 'cmd:usermod', 'cmd:groupadd', 'cmd:id', 'cmd:umask', 'cmd:sudo', 'cmd:passwd', 'cmd:visudo']],
                    'lnx_services' => ['label' => 'Umím spravovat služby a číst systémové logy', 'level' => 3,
                        'tags' => ['topic:processes-systemd', 'topic:journal-logs', 'topic:web-service-linux', 'topic:linux-file-troubleshooting', 'pack:opravna', 'pack:incident', 'cmd:systemctl', 'cmd:journalctl', 'cmd:ps', 'cmd:top', 'cmd:kill', 'cmd:nginx', 'cmd:df', 'cmd:du', 'cmd:tail']],
                    'lnx_ssh' => ['label' => 'Umím se bezpečně připojit přes SSH a klíče', 'level' => 2,
                        'tags' => ['topic:ssh-sftp', 'topic:ssh-keys-ops', 'topic:ssh-key-operations', 'pack:klice', 'cmd:ssh', 'cmd:ssh-keygen', 'cmd:ssh-copy-id', 'cmd:scp']],
                    'lnx_automation' => ['label' => 'Umím automatizovat úlohy skriptem a cronem', 'level' => 3,
                        'tags' => ['topic:shell-cron', 'topic:bash-error-handling', 'pack:cron', 'cmd:crontab']],
                ]],
            ],
        ],
    ];
}

/** Předmět kompetencí pro třídu, nebo null (třída mimo katalog). */
function comp62_subject_for_class(string $classId): ?string
{
    foreach (comp62_catalog() as $subject => $row) {
        if (in_array($classId, (array)$row['classes'], true)) return (string)$subject;
    }
    return null;
}

/**
 * Plochý seznam kompetencí předmětu v pořadí katalogu: id => ['id','area','area_label','label','level','tags'].
 * @return array<string,array<string,mixed>>
 */
function comp62_competencies(string $subject): array
{
    $out = [];
    foreach ((array)(comp62_catalog()[$subject]['areas'] ?? []) as $areaId => $area) {
        foreach ((array)$area['competencies'] as $id => $c) {
            $out[(string)$id] = ['id' => (string)$id, 'area' => (string)$areaId, 'area_label' => (string)$area['label'], 'label' => (string)$c['label'], 'level' => (int)$c['level'], 'tags' => array_values((array)$c['tags'])];
        }
    }
    return $out;
}

/** Otisk katalogu – mění se s jakoukoli úpravou kompetencí nebo tagů (invaliduje cache zvládnutí). */
function comp62_catalog_version(): string
{
    static $version = null;
    return $version ??= substr(sha1((string)json_encode([comp62_catalog(), COMP62_OVERRIDES])), 0, 12);
}

/**
 * Přiřadí aktivitu ke kompetencím: ruční výjimka podle $ref, jinak kompetence s největším počtem
 * společných tagů (shodné pořadí podle katalogu, nejvýš COMP62_MATCH_LIMIT). Prázdný výsledek = nenamapováno.
 * @param list<string> $tags
 * @return list<string> id kompetencí
 */
function comp62_match(string $subject, array $tags, string $ref = ''): array
{
    $competencies = comp62_competencies($subject);
    if ($ref !== '' && isset(COMP62_OVERRIDES[$ref])) {
        return array_values(array_filter((array)COMP62_OVERRIDES[$ref], static fn($id): bool => isset($competencies[(string)$id])));
    }
    $have = array_flip(array_map('strval', $tags));
    $scored = [];
    $order = 0;
    foreach ($competencies as $id => $c) {
        $hits = 0;
        foreach ($c['tags'] as $tag) if (isset($have[$tag])) $hits++;
        if ($hits > 0) $scored[] = [$hits, $order, $id];
        $order++;
    }
    usort($scored, static fn(array $a, array $b): int => $b[0] <=> $a[0] ?: $a[1] <=> $b[1]);
    return array_values(array_map(static fn(array $r): string => (string)$r[2], array_slice($scored, 0, COMP62_MATCH_LIMIT)));
}
